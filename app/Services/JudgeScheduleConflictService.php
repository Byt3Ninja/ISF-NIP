<?php

namespace App\Services;

use App\Screening;
use App\Step;
use App\User;
use App\Services\PanelWindowService;
use Carbon\Carbon;

/**
 * Detects judge double-booking across screening panels (GV-8300).
 *
 * A judge cannot sit on two panels whose meeting windows overlap. Panels are
 * checked on two axes:
 *
 *   1. INTRA-SUBMISSION — panel #1 vs panel #2 inside the same save. This is the
 *      case the ticket reproduces: nothing is persisted yet, so no query can see
 *      it; the panels must be compared against each other in memory.
 *   2. CROSS-STEP — against panels already saved on OTHER screening (stype 2) and
 *      final screening (stype 5) steps of the SAME cycle. The current step is
 *      excluded, otherwise re-saving an unchanged step would report every judge
 *      as conflicting with their own existing row.
 *
 * Windows are half-open [start, end): a panel ending 10:10 and one starting
 * 10:10 are back-to-back, not overlapping. Treating touching edges as a conflict
 * would make consecutive scheduling — the normal way to run a screening day —
 * impossible.
 *
 * Panels with no scheduled session (no date or no start time) are skipped: they
 * book no time and so cannot collide with anything.
 */
class JudgeScheduleConflictService
{
    /** Step types that hold judging panels: Screening and Final Screening. */
    private const PANEL_STEP_TYPES = [2, 5];

    /**
     * @param  int    $stepId  the step being saved
     * @param  array  $panels  raw `panels` input from the request
     * @return string[]  human-readable conflict messages (empty when clear)
     */
    public static function detect(int $stepId, array $panels): array
    {
        $windows = self::buildWindows($panels);

        if (empty($windows)) {
            return [];
        }

        $judgeNames = self::judgeNames($windows);

        $messages = array_merge(
            self::detectWithinSubmission($windows, $judgeNames),
            self::detectAgainstSavedSteps($stepId, $windows, $judgeNames)
        );

        // The same judge pair can collide on several startups/panels; show each
        // distinct sentence once so the dialog stays readable.
        return array_values(array_unique($messages));
    }

    /**
     * Normalise the submitted panels into comparable time windows.
     *
     * The end time mirrors ScreeningController@addJudgingPanels: with the
     * interval switch on the admin never types one (the field is rendered
     * disabled and browsers omit disabled inputs), so it is derived as
     * start + (company_count * interval). Otherwise the typed end time is used.
     *
     * @return array<int, array{panel:int, judges:int[], start:Carbon, end:Carbon, date:string}>
     */
    private static function buildWindows(array $panels): array
    {
        $windows = [];

        foreach ($panels as $index => $panel) {
            $date  = $panel['session_date'] ?? null;
            $start = $panel['start_time'] ?? null;

            // No scheduled session => books no time => cannot conflict.
            if (empty($date) || empty($start)) {
                continue;
            }

            $judges = array_values(array_unique(array_map('intval', (array) ($panel['judges'] ?? []))));
            if (empty($judges)) {
                continue;
            }

            // Shared derivation (PanelWindowService) so conflict detection,
            // history locking and change notification all measure a panel
            // identically. Returns null when the panel books no time.
            $window = PanelWindowService::fromInput($panel);
            if ($window === null) {
                continue;
            }

            $windows[] = [
                'panel'  => $index + 1,
                'judges' => $judges,
                'start'  => $window['start'],
                'end'    => $window['end'],
                'date'   => $window['start']->format('Y-m-d'),
            ];
        }

        return $windows;
    }

    /** Panel-vs-panel inside the submission being saved. */
    private static function detectWithinSubmission(array $windows, array $judgeNames): array
    {
        $messages = [];
        $count = count($windows);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $a = $windows[$i];
                $b = $windows[$j];

                if (!self::overlaps($a['start'], $a['end'], $b['start'], $b['end'])) {
                    continue;
                }

                foreach (array_intersect($a['judges'], $b['judges']) as $judgeId) {
                    // Name the EARLIER panel as the one already holding the slot,
                    // so the message reads as a conflict with existing work.
                    $held = $a['start']->lessThanOrEqualTo($b['start']) ? $a : $b;

                    $messages[] = __('admin.screening.judge_conflict_same_step', [
                        'judge' => $judgeNames[$judgeId] ?? ('#' . $judgeId),
                        'panel' => __('admin.screening.panel') . ' #' . $held['panel'],
                        'date'  => $held['date'],
                        'start' => $held['start']->format('H:i'),
                        'end'   => $held['end']->format('H:i'),
                    ]);
                }
            }
        }

        return $messages;
    }

    /** Submitted panels vs panels already saved on other steps of the same cycle. */
    private static function detectAgainstSavedSteps(int $stepId, array $windows, array $judgeNames): array
    {
        $step = Step::find($stepId);
        if (!$step) {
            return [];
        }

        $judgeIds = array_values(array_unique(array_merge(...array_column($windows, 'judges'))));
        $dates    = array_values(array_unique(array_column($windows, 'date')));

        // One query: only the judges and dates actually being scheduled, and only
        // panel-bearing steps of this cycle other than the one being saved.
        $saved = Screening::query()
            ->join('steps', 'steps.id', '=', 'screenings.step_id')
            ->where('steps.cycle_id', $step->cycle_id)
            ->whereIn('steps.stype', self::PANEL_STEP_TYPES)
            ->whereNull('steps.deleted_at')
            ->where('screenings.step_id', '!=', $stepId)
            ->whereIn('screenings.user_id', $judgeIds)
            ->whereIn('screenings.session_date', $dates)
            ->whereNotNull('screenings.start_time')
            ->select([
                'screenings.user_id',
                'screenings.panel',
                'screenings.session_date',
                'screenings.start_time',
                'screenings.end_time',
                'screenings.interval',
                'screenings.users',
                'steps.title as step_title',
                'steps.stype as step_stype',
            ])
            ->get();

        $messages = [];

        foreach ($saved as $row) {
            $existing = self::savedRowWindow($row);
            if ($existing === null) {
                continue;
            }

            foreach ($windows as $window) {
                if (!in_array((int) $row->user_id, $window['judges'], true)) {
                    continue;
                }
                if (!self::overlaps($window['start'], $window['end'], $existing['start'], $existing['end'])) {
                    continue;
                }

                $messages[] = __('admin.screening.judge_conflict_other_step', [
                    'judge' => $judgeNames[(int) $row->user_id] ?? ('#' . $row->user_id),
                    'panel' => __('admin.screening.panel') . ' #' . $row->panel,
                    'step'  => $row->step_title ?: self::stepTypeLabel((int) $row->step_stype),
                    'date'  => $existing['start']->format('Y-m-d'),
                    'start' => $existing['start']->format('H:i'),
                    'end'   => $existing['end']->format('H:i'),
                ]);
            }
        }

        return $messages;
    }

    /**
     * Rebuild a saved screening row's window, mirroring buildWindows() so both
     * sides of a comparison are derived the same way.
     *
     * @return array{start:Carbon, end:Carbon}|null
     */
    private static function savedRowWindow($row): ?array
    {
        if (empty($row->session_date) || empty($row->start_time)) {
            return null;
        }

        // Same shared derivation as the submitted side, so both halves of a
        // comparison are measured the same way.
        return PanelWindowService::fromScreening($row);
    }

    /**
     * Half-open overlap: [aStart, aEnd) vs [bStart, bEnd).
     * Back-to-back windows (one ends exactly as the next begins) do NOT conflict.
     */
    private static function overlaps(Carbon $aStart, Carbon $aEnd, Carbon $bStart, Carbon $bEnd): bool
    {
        return $aStart->lessThan($bEnd) && $bStart->lessThan($aEnd);
    }

    /** Resolve display names for every judge referenced by the submitted panels. */
    private static function judgeNames(array $windows): array
    {
        $ids = array_values(array_unique(array_merge(...array_column($windows, 'judges'))));
        if (empty($ids)) {
            return [];
        }

        $locale = app()->getLocale();
        $first = 'first_name_' . $locale;
        $last  = 'last_name_' . $locale;

        return User::whereIn('id', $ids)->get()->mapWithKeys(function ($user) use ($first, $last) {
            $name = trim(($user->{$first} ?? $user->first_name_en) . ' ' . ($user->{$last} ?? $user->last_name_en));
            return [(int) $user->id => $name ?: ('#' . $user->id)];
        })->all();
    }

    /** Fallback label when a step has no title. */
    private static function stepTypeLabel(int $stype): string
    {
        return $stype === 5 ? __('cycles.Final evaluation') : __('cycles.Screening');
    }
}
