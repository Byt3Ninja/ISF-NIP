<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ChangeCycle extends Notification
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
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {


        Log::info('Mail data:', ['emailCompany' => $this->data['emailCompany']]);
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
            'message' => 'The Step has been Changed For ' . $this->data['company_name_en'] . ' from' . $this->data['last_step'] . ' to ' . $this->data['new_step'] . 'by ' . $this->data['organization_name_en'],
        ];
    }
}
