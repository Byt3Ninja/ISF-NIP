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
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $org = $notifiable->organization;
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
