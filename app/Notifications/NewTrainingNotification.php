<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;

class NewTrainingNotification extends Notification implements ShouldQueue
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
            ->subject(__('admin.new_training_message'))
            ->markdown('emails.new_training', ['data' => $this->data]);
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.new_training.whatsapp', [
            'title' => $this->data['title'] ?? '',
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('admin.new_training_message'),
            'message' => __('notifications.new_training.broadcast', ['title' => $this->data['title'] ?? '']),
            'time' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => __('admin.new_training_message', ['training' => $this->data['title']]),
            'user_id' => $notifiable->id,
        ];
    }
}
