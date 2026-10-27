<?php

namespace App\Notifications;

use App\Company;
use App\User;

/**
 * Tells a judge that their screening / final screening panel meeting was
 * rescheduled, or that the meeting with one startup on it was cancelled
 * (GV-3295: GV-8486..GV-8511, GV-8553..GV-8583, GV-8625..GV-8655,
 * GV-8692..GV-8723).
 *
 * A reschedule reports the WHOLE panel window (the GV-8553 note: the founder
 * name was dropped from that template because a judge's meeting is the panel,
 * not one startup's slot). A cancellation is per removed startup and names its
 * founder, with that startup's own slot.
 */
class JudgeScreeningMeetingChangedNotification extends ScreeningMeetingNotification
{
    /**
     * Rescheduled: every startup on the panel after the save. Cancelled: the
     * one removed startup.
     *
     * @var array<int, int>
     */
    public $companyIds;

    /**
     * @param  string  $variant  'rescheduled' or 'cancelled'
     * @param  array<int, int>  $companyIds
     */
    public function __construct(
        int $stepId,
        string $variant,
        ?string $slotStart,
        ?string $slotEnd,
        array $companyIds,
        ?string $meetingLink = null,
        ?string $passcode = null,
        bool $sendEmail = true
    ) {
        $this->stepId = $stepId;
        $this->variant = $variant === 'cancelled' ? 'cancelled' : 'rescheduled';
        $this->slotStart = $slotStart;
        $this->slotEnd = $slotEnd;
        $this->companyIds = array_values(array_map('intval', $companyIds));
        $this->meetingLink = $meetingLink;
        $this->passcode = $passcode;
        $this->sendEmail = $sendEmail;
    }

    protected function audience(): string
    {
        return 'judge';
    }

    protected function dashboardPath(): string
    {
        return '/dashboard';
    }

    protected function extraParams(): array
    {
        return ['founder' => implode(', ', $this->founderNames())];
    }

    protected function details(object $notifiable): array
    {
        $label = fn (string $key) => __('email.screening_meeting_email.label.' . $key, ['stage' => $this->stageName()]);
        $startups = implode(', ', $this->startupNames());

        if ($this->variant === 'cancelled') {
            return [
                $label('step')    => $this->stepName(),
                $label('founder') => implode(', ', $this->founderNames()),
                $label('startup') => $startups,
                $label('date')    => $this->formatDate($notifiable),
                $label('time')    => $this->timeRange($notifiable),
            ];
        }

        return [
            $label('step')         => $this->stepName(),
            $label('founders')     => implode(', ', $this->founderNames()),
            $label('startups')     => $startups,
            $label('date')         => $this->formatDate($notifiable),
            $label('time')         => $this->timeRange($notifiable),
            $label('meeting_link') => $this->meetingLink,
            $label('passcode')     => $this->passcode,
        ];
    }

    /**
     * One founder (company owner) per startup, in panel order. A startup whose
     * owner cannot be found is named by its title instead, so the founder line
     * and the cancellation subject are never blank.
     *
     * @return array<int, string>
     */
    protected function founderNames(): array
    {
        $owners = User::whereIn('company_id', $this->companyIds)->get()->keyBy('company_id');
        $titles = $this->companies();

        $names = [];
        foreach ($this->companyIds as $companyId) {
            $name = trim((string) ($owners->get($companyId)?->name ?? ''));
            $names[] = $name !== '' ? $name : ($titles[$companyId] ?? '');
        }

        return array_values(array_filter($names, 'filled'));
    }

    /**
     * @return array<int, string>
     */
    protected function startupNames(): array
    {
        $titles = $this->companies();

        return array_values(array_filter(
            array_map(fn ($id) => $titles[$id] ?? '', $this->companyIds),
            'filled'
        ));
    }

    /**
     * @return array<int, string> company id => title
     */
    protected function companies(): array
    {
        return Company::whereIn('id', $this->companyIds)->get()
            ->mapWithKeys(fn ($company) => [(int) $company->id => $this->companyName($company)])
            ->all();
    }
}
