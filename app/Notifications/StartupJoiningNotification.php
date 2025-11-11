<?php

namespace App\Notifications;

use App\Services\GlobalNotificationSettingsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Broadcasting\WhatsAppChannel;

class StartupJoiningNotification extends Notification implements ShouldQueue
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

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.startup_join.whatsapp', [
            'cycle' => $this->data['cycle'] ?? 'N/A',
            'app' => config('app.name'),
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('admin.Startup_Joined_New_Program_subject'),
            'message' => __('notifications.startup_join.broadcast', ['cycle' => $this->data['cycle'] ?? 'N/A']),
            'time' => now()->toDateTimeString(),
        ]);
    }
}
