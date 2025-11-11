<?php

namespace App\Notifications;

use Log;
use Illuminate\Bus\Queueable;
use App\Broadcasting\WhatsAppChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Services\GlobalNotificationSettingsService;

class MentorshipRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $data;
    public $reminder;
    /**
     * Create a new notification instance.
     */
    public function __construct($data, $reminder = null)
    {
        $this->data = $data;
        $this->reminder = $reminder;
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
        if($this->reminder == 'Reminder'){
            return (new MailMessage)
            ->subject( __('emails.mentorship_request_reminder.subject', ['startup' => $this->data['company_name']]))
            ->markdown('emails.mentorship_request_reminder', ['data' => $this->data]);
           Log::info('Mail data:', ['company_name' => $this->data['company_name']]);
        }else{
            return (new MailMessage)
            ->subject(__('admin.mentorship_request_subject', ['startup' => $this->data['company_name']]))
            ->markdown('emails.mentorship_request', ['data' => $this->data]);
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
            'message' => __('admin.mentorship_request_subject', ['startup' => $this->data['company_name']]),
            'user_id' => $notifiable->id,
        ];
    }

     public function toDatabase(object $notifiable): array
    {
       return [
        'message' => ($this->data['company_name'] ?? 'N/A') . 
                     ' has sent you a mentorship request. They are eager to learn from your expertise and guidance.',
         ]; 
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.mentorship_request.whatsapp', [
            'startup' => $this->data['company_name'] ?? '',
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('admin.mentorship_request_subject', ['startup' => $this->data['company_name'] ?? '']),
            'message' => __('notifications.mentorship_request.broadcast', ['startup' => $this->data['company_name'] ?? '']),
            'time' => now()->toDateTimeString(),
        ]);
    }

    public function afterSend($notifiable)
    {
        Log::info("Notification sent to user {$notifiable->id}", [
            'type' => static::class,
        ]);
    }
}
