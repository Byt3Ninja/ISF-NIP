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
    public function __construct(
        int $stepId,
        int $companyId,
        ?string $slotStart,
        $variant = 'rescheduled',
        ?string $slotEnd = null,
        ?string $meetingLink = null,
        ?string $passcode = null,
        bool $sendEmail = true
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

        return [
            $label('step')         => $this->stepName(),
            $label('date')         => $this->formatDate($notifiable),
            $label('time')         => $this->timeRange($notifiable),
            $label('meeting_link') => $this->meetingLink,
            $label('passcode')     => $this->passcode,
        ];
    }
}
