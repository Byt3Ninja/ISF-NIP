<?php

namespace App\Notifications;

use App\Company;

/**
 * Tells a founder that their screening / final screening meeting was newly
 * scheduled, rescheduled, or cancelled (GV-8892 / GV-8893, texts from GV-3295:
 * GV-8517..GV-8547, GV-8589..GV-8619, GV-8661..GV-8686, GV-8729..GV-8759).
 *
 * With interval scheduling each startup's slot is derived from its POSITION in
 * the panel, so adding, removing or reordering startups reflows the times of
 * everyone after the edit. The caller diffs the before/after slots and hands
 * this class only the startups whose meeting actually changed.
 *
 * The notifiable is the company OWNER (the user whose `users.company_id` points
 * at the company), since a Company is not itself notifiable.
 */
class StartupMeetingTimeChangedNotification extends ScreeningMeetingNotification
{
    /**
     * @var int
     */
    public $companyId;

    /**
     * Kept for jobs queued before $variant existed, which restore with only
     * this flag set.
     *
     * @var bool
     */
    public $cancelled;

    /**
     * The argument order is kept from the original signature so jobs already on
     * the queue still construct the same way; new arguments are appended.
     *
     * @param  string|bool  $variant  'scheduled', 'rescheduled' or 'cancelled' (or the legacy $cancelled flag)
     */
    /**
     * @var array<int, int>|null
     */
    public $judgeIds;

    public function __construct(
        int $stepId,
        int $companyId,
        ?string $slotStart,
        $variant = 'rescheduled',
        ?string $slotEnd = null,
        ?string $meetingLink = null,
        ?string $passcode = null,
        bool $sendEmail = true,
        ?array $judgeIds = null
    ) {
        if (is_bool($variant)) {
            $variant = $variant ? 'cancelled' : 'rescheduled';
        }

        $this->stepId = $stepId;
        $this->companyId = $companyId;
        $this->slotStart = $slotStart;
        $this->slotEnd = $slotEnd;
        $this->meetingLink = $meetingLink;
        $this->passcode = $passcode;
        $this->sendEmail = $sendEmail;
        $this->variant = in_array($variant, ['scheduled', 'rescheduled', 'cancelled'], true) ? $variant : 'rescheduled';
        $this->cancelled = $this->variant === 'cancelled';
        $this->judgeIds = $judgeIds;
    }

    /**
     * Whether this is a Final Screening (stype 5) session, as opposed to
     * Initial Screening (stype 2) -- the two stages share this class, but the
     * literal "Final Screening Session" wording and the judges list/CTA are
     * specific to final screening only.
     */
    protected function isFinalStage(): bool
    {
        return (int) ($this->step()?->stype) === 5;
    }

    /**
     * A founder's first booking on an Initial Screening (stype 2) panel, which
     * has its own literal "Screening Session" template.
     */
    protected function isInitialScheduled(): bool
    {
        return !$this->isFinalStage() && $this->variant === 'scheduled';
    }

    protected function judgeNames(): string
    {
        if (empty($this->judgeIds)) {
            return '';
        }

        return \App\User::whereIn('id', $this->judgeIds)
            ->get()
            ->map(fn ($u) => trim($u->name))
            ->filter()
            ->implode(', ');
    }

    protected function audience(): string
    {
        return 'founder';
    }

    protected function dashboardPath(): string
    {
        return '/dashboard';
    }

    protected function details(object $notifiable): array
    {
        $label = fn (string $key) => __('email.screening_meeting_email.label.' . $key, ['stage' => $this->stageName()]);

        if ($this->variant === 'cancelled') {
            return [
                $label('step')    => $this->stepName(),
                $label('startup') => $this->companyName(Company::find($this->companyId)),
                $label('date')    => $this->formatDate($notifiable),
                $label('time')    => $this->timeRange($notifiable),
            ];
        }

        if ($this->isInitialScheduled()) {
            return [
                $label('judges')       => $this->judgeNames() ?: '-',
                // Isolated left-to-right so the digits and AM/PM keep their order in an RTL email.
                $label('date_time')    => "\u{2066}" . $this->formatDate($notifiable) . ', ' . $this->timeRange($notifiable) . "\u{2069}",
                $label('session_link') => $this->meetingLink ?: '-',
                $label('passcode')     => $this->passcode,
            ];
        }

        $rows = [$label('step') => $this->stepName()];

        // Judges are only meaningful (and only populated by the caller) for
        // a Final Screening panel -- Initial Screening keeps its prior rows.
        if ($this->isFinalStage()) {
            $rows[$label('judges')] = $this->judgeNames();
        }

        return $rows + [
            $label('date')         => $this->formatDate($notifiable),
            $label('time')         => $this->timeRange($notifiable),
            $label('meeting_link') => $this->meetingLink,
            $label('passcode')     => $this->passcode,
        ];
    }

    protected function key(string $part): string
    {
        // The literal "Final Screening Session" template applies only to a
        // founder's final-screening session being scheduled/rescheduled; every
        // other combination (initial screening, judge audience, cancelled)
        // keeps the shared generic wording.
        if ($this->isFinalStage() && in_array($this->variant, ['scheduled', 'rescheduled'], true)) {
            return 'email.screening_meeting_email.founder.' . $this->variant . '_final.' . $part;
        }

        if ($this->isInitialScheduled()) {
            return 'email.screening_meeting_email.founder.scheduled_initial.' . $part;
        }

        return parent::key($part);
    }

    protected function cta(object $notifiable): ?array
    {
        if ($this->isInitialScheduled()) {
            return empty($this->meetingLink) ? null : [
                'label' => __('email.screening_meeting_email.screening_join_button'),
                'url'   => $this->meetingLink,
            ];
        }

        if (!$this->isFinalStage() || $this->variant === 'cancelled' || empty($this->meetingLink)) {
            return null;
        }

        return [
            'label' => __('email.screening_meeting_email.final_join_button'),
            'url'   => $this->meetingLink,
        ];
    }
}
