<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Broadcasting\WhatsAppChannel;

class MentorshipRequestNotification extends Notification implements ShouldQueue
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
        // Always store in database
        $channels[] = 'database';

        // WhatsApp if enabled
        if (!empty($notifiable->accept_whatsapp)) {
            $channels[] = WhatsAppChannel::class;
        }

        // Pusher (broadcast) if enabled
        if (!empty($notifiable->accept_pusher)) {
            $channels[] = 'broadcast';
        }

        // Email if user accepts OR default to email if nothing else (besides database) selected
        if (!empty($notifiable->accept_email) || count($channels) === 1) { // only 'database' present
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('admin.mentorship_request_subject', ['startup' => $this->data['company_name']]))
            ->markdown('emails.mentorship_request', ['data' => $this->data]);
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
}
