<?php

namespace App\Notifications;

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

        return (new MailMessage)
            ->subject('New Training invitation')
            ->markdown('emails.emailTrainer', array_merge($this->data, $orgData));
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
