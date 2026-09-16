<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * The authoritative panel meeting window: when a judging panel starts and ends.
 *
 * A panel holds ONE meeting that its startups join in sequence. With the
 * interval switch on, the admin never types an end time (the field is rendered
 * disabled, and browsers omit disabled inputs), so the end must be DERIVED as
 * start + (startup_count * interval). With it off, the typed end time is used.
 *
 * That derivation was previously repeated in ScreeningController@addJudgingPanels
 * and JudgeScheduleConflictService. It lives here now so every consumer —
 * conflict detection, history locking and change notification — measures a panel
 * the same way. Two copies that drift produce conflicts that are reported but not
 * enforced, or panels that lock a slot early.
 *
 * Nothing here is persisted; windows are always derived from
 * (session_date, start_time, interval|end_time, startup count).
 */
class PanelWindowService
{
    /**
     * Fallback length for a scheduled panel that stored neither an interval nor
     * an end time (GV-9568).
     *
     * `end_time` is rendered DISABLED whenever interval mode is on, so browsers
     * omit it from the submission; the end is then derived from the interval. If
     * the interval is also blank, both columns land NULL and the panel books no
     * time at all — so it never became History and stayed editable forever, which
     * is the "status change is delayed" in the report.
     *
     * A panel that has a date and a start time HAS taken place. Measuring it as
     * one nominal hour is the same default addJudgingPanels() already applies when
     * it books the meeting without a known end, so the lock agrees with the
     * meeting that was actually created.
     */
    public const DEFAULT_PANEL_MINUTES = 60;

    /**
     * Window for a submitted panel (raw request shape).
     *
     * @return array{start: Carbon, end: Carbon}|null  null when the panel books no time
     */
    public static function fromInput(array $panel): ?array
    {
        $date  = $panel['session_date'] ?? null;
        $start = $panel['start_time'] ?? null;

        if (empty($date) || empty($start)) {
            return null;
        }

        $interval = isset($panel['interval']) && $panel['interval'] !== ''
            ? (int) $panel['interval']
            : null;

        $companies = array_unique(array_map('intval', (array) ($panel['companies'] ?? [])));

        return self::build($date, $start, $interval, $panel['end_time'] ?? null, count($companies));
    }

    /**
     * Window for a saved `screenings` row.
     *
     * @return array{start: Carbon, end: Carbon}|null
     */
    public static function fromScreening($screening): ?array
    {
        if (empty($screening)) {
            return null;
        }

        $date = $screening->session_date instanceof \DateTimeInterface
            ? $screening->session_date->format('Y-m-d')
            : $screening->session_date;

        $companies = is_array($screening->users)
            ? $screening->users
            : (json_decode((string) $screening->users, true) ?: []);

        return self::build(
            $date,
            $screening->start_time,
            $screening->interval !== null ? (int) $screening->interval : null,
            $screening->end_time,
            count($companies)
        );
    }

    /**
     * @param  int  $companyCount  drives the derived end in interval mode
     * @return array{start: Carbon, end: Carbon}|null
     */
    public static function build($date, $startTime, ?int $interval, $endTime, int $companyCount): ?array
    {
        if (empty($date) || empty($startTime)) {
            return null;
        }

        if ($interval !== null && $interval <= 0) {
            $interval = null;
        }

        try {
            $start = Carbon::parse($date . ' ' . $startTime);

            if ($interval !== null) {
                // At least one slot: a panel with no startups still books its
                // first interval rather than collapsing to a zero-length window.
                $end = (clone $start)->addMinutes(max($companyCount, 1) * $interval);
            } elseif (!empty($endTime)) {
                $end = Carbon::parse($date . ' ' . $endTime);
            } else {
                // Neither interval nor end time stored. The panel is still
                // scheduled — it has a date and a start — so it must eventually
                // become History rather than staying editable forever (GV-9568).
                $end = (clone $start)->addMinutes(self::DEFAULT_PANEL_MINUTES);
            }
        } catch (\Exception $e) {
            return null;
        }

        return $end->greaterThan($start) ? ['start' => $start, 'end' => $end] : null;
    }

    /**
     * Has this panel's meeting finished?
     *
     * A panel becomes History once its END time passes — the last startup's slot
     * is done. Locking on the START time instead would freeze a panel the moment
     * its first slot begins, leaving an admin unable to correct a startup
     * scheduled hours later in the same session.
     *
     * Slot times are wall-clock with no stored offset, so both sides are read in
     * the viewer's timezone via MeetingWindowService::inUserZone().
     *
     * Deliberately NOT MeetingWindowService::hasExpired(): that adds a 60-minute
     * LATE_MINUTES grace so a running meeting stays joinable after it overruns.
     * That is right for a join button and wrong here — it would leave a finished
     * panel editable for an extra hour. The PM's rule is the scheduled end time
     * itself.
     *
     * An unscheduled panel books no time and is never history.
     */
    public static function isPast(?array $window, $user = null): bool
    {
        if (empty($window['end'])) {
            return false;
        }

        $end = MeetingWindowService::inUserZone($window['end'], $user);

        return $end !== null && MeetingWindowService::now($user)->greaterThan($end);
    }
}
