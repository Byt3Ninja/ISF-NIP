<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;

class MentorshipSession extends Notification implements ShouldQueue
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

 
        $channels = [];
        // Always store in database for audit/history
    

        // WhatsApp if enabled globally AND user accepts it
        if (GlobalNotificationSettingsService::isWhatsAppEnabled()==true && $notifiable->accept_whatsapp ==true) {
            $channels[] = WhatsAppChannel::class;
        }

        // Pusher (broadcast) if enabled globally AND user accepts it
        if (GlobalNotificationSettingsService::isPusherEnabled()==true && $notifiable->accept_pusher==true) {
            $channels[] = 'broadcast';
        }

        // Email if enabled globally AND user accepts it OR as default when nothing else is active
        if (GlobalNotificationSettingsService::isEmailEnabled() ==true && $notifiable->accept_email ==true ) { // only 'database' present
            $channels[] = 'mail';
        }

        if (!empty($channels)) {
               $channels[] = 'database';
        }


        return $channels;
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

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        // Expecting $this->Message indices: [0]=subject/title, [1]=greeting, [2]=line, [3]=url, [4]=database message
        return __('notifications.mentorship_session.whatsapp', [
            'greeting' => $this->Message[1] ?? '',
            'line' => $this->Message[2] ?? '',
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => $this->Message[0] ?? __('notifications.mentorship_session.broadcast', ['title' => '']),
            'message' => __('notifications.mentorship_session.broadcast', ['title' => $this->Message[0] ?? '']),
            'time' => now()->toDateTimeString(),
        ]);
    }
}
