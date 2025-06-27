<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeJudgerNotification extends Notification
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
        return ['mail','database'];
    }

    /** 
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if ($this->data['role'] === 'Judger') {
            return (new MailMessage)
                ->subject('مرحبًا بك كـ Judger!')
                ->markdown('emails.welcome_judger', ['data' => $this->data]);
        } else {
            return (new MailMessage)
                ->subject('مرحبًا بك!')
                ->markdown('emails.welcome', ['data' => $this->data]);
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'مرحبًا بك في منصتنا كـ Judger!',
            'user_id' => $notifiable->id,
        ];
    }
}
