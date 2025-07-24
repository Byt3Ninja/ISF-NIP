<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StartupJoiningNotification extends Notification
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
        return (new MailMessage)
            ->subject(__('admin.Startup_Joined_New_Program_subject'))
            ->markdown('emails.startup_join', ['data' => $this->data]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => __('admin.Startup_Joined_New_Program_subject'),
            'user_id' => $notifiable->id,
        ];
    }

     public function toDatabase(object $notifiable): array
    {
       return [
            'message' => ' Congratulations! You have successfully joined the ' . ($this->data['cycle'] ?? 'N/A') . 
                ' We are excited to have you on board and look forward to supporting your journey.
                Thanks from ' . env('APP_NAME'),
        ];
    }
}
