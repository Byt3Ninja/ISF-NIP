<?php

namespace App\Notifications;

use App\Settings;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainerEmail extends Notification
{
    use Queueable, ConfiguresSmtp;

    public $data;

    /**
     * The organization the training belongs to, used for SMTP config and branding.
     *
     * @var \App\Organization|null
     */
    public $organization;

    /**
     * Create a new notification instance.
     */
    public function __construct($data, $organization = null)
    {
        $this->data = $data;
        $this->organization = $organization;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Only mail: this notification is sent to an on-demand notifiable
        // (Notification::route('mail', $trainer_email)), which has no database record.
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // The trainer is an on-demand notifiable (AnonymousNotifiable) and has no
        // organization of its own, so it is passed in from the dispatching job.
        $org = $this->organization;
        $smtpSettings = $org?->organizationSettings;

        $this->configureSmtp($smtpSettings);
        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->buildEmailOrganizationData($org);

        $setting = Settings::first();
        $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
        $platformName = $setting?->Platform_Name ?: config('app.name');
        $orgName = $orgData['org_name'] ?? $platformName;

        $type = strtolower(trim((string) ($this->data['type'] ?? '')));
        $isOnline = $type !== 'offline';

        // Keys are named distinctly from 'platformName': a global view composer
        // (AppServiceProvider) sets that key on every view and would override it.
        return (new MailMessage)
            ->subject(__('email.trainer_invitation_email.subject', [], $locale))
            ->view('emails.emailTrainer', array_merge([
                'trainerLocale' => $locale,
                'trainerOrgName' => $orgName,
                'trainerName' => $this->data['trainer_name'] ?? '',
                'trainerProgramName' => $this->data['program_name'] ?? '',
                'trainerTrainingTitle' => $this->data['title'] ?? '',
                'trainerSessionDate' => $this->data['session_date'] ?? '',
                'trainerStartTime' => $this->data['start_time'] ?? '',
                'trainerEndTime' => $this->data['end_time'] ?? '',
                'trainerIsOnline' => $isOnline,
                'trainerLink' => $this->data['join_url'] ?? '',
                'trainerAddress' => $this->data['Address'] ?? '',
                'trainerViewUrl' => $this->data['join_url'] ?? '',
            ], $orgData));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'New Training invitation',
        ];
    }
}
