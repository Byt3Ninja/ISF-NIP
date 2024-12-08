<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangeCycle extends Notification implements ShouldQueue
{
    use Queueable;
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        return (new MailMessage)
            ->subject('Your Startup Progress Update - Stage Transition Notification')
            ->markdown('emails.emailCycle', $this->data);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'company_name' => $this->data['company_name_en'] ?? 'Unknown Company',
            'last_step' => $this->data['last_step'] ?? 'N/A',
            'new_step' => $this->data['new_step'] ?? 'N/A',
            'organization_name' => $this->data['organization_name_en'] ?? 'Unknown Organization',
        ];
    }
}
