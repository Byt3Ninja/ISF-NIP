<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MentorshipSession extends Notification
{
    use Queueable;
    public $Message;
    public $ActionText = 'Open VI portal';
    public $ActionUrl = null;

    /**
     * Create a new notification instance.
     */
   public function __construct(array $Message = [])
    {
        $this->Message = $Message;
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
   public function toMail($notifiable)
    {
        $url = is_null($this->ActionUrl) ? url($this->Message[3]) : $this->ActionUrl;
        return (new MailMessage)
            ->subject($this->Message[0])
            ->greeting($this->Message[1]) 
            ->level('info')
            ->line($this->Message[2])
            ->action($this->ActionText, $url)
            ->line('Thank you for using our system!')
            ->salutation('Kind regards,');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' =>$this->Message[4],
        ];
    }
}
