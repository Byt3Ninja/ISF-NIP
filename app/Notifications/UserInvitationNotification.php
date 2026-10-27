<?php

namespace App\Notifications;

use App\Invitation;
use App\Settings;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable, ConfiguresSmtp;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    public function __construct(public Invitation $invitation)
    {
    }

    public function via(object $notifiable): array
    {
        // This invitation targets an external email (may not be a registered user),
        // so we send mail-only via Notification::route('mail', ...)
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // The email follows the platform's default language (General Settings),
        // not the locale of whoever sent the invitation.
        $setting = Settings::first();
        $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
        app()->setLocale($locale);

        $org = $this->invitation->organization;
        $smtpSettings = $org?->organizationSettings;

        $this->configureSmtp($smtpSettings);
        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->emailBrandingData();

        $orgName = $org->name ?? ($setting->Platform_Name ?? config('app.name'));

        // Same email as an assignment by an admin/sub-admin (OrganizationAssignedNotification);
        // only the button differs: the invitee has no account yet, so it opens the set-password page.
        return (new MailMessage)
            ->from($from['address'], $from['name'])
            ->subject(__('email.org_assignment_invite_email.subject', ['org_name' => $orgName], $locale))
            ->view('emails.organization_assigned', array_merge([
                'locale'          => $locale,
                'inviteeName'     => trim($this->invitation->first_name . ' ' . $this->invitation->last_name),
                'assignedOrgName' => $orgName,
                'acceptUrl'       => $this->invitation->setPasswordUrl(),
            ], $orgData));
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('UserInvitationNotification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'invitation_id' => $this->invitation->id ?? null,
        ]);
    }
}
