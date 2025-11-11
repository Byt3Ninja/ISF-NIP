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
        $channels = [];
        // Always store in database for audit/history
        $channels[] = 'database';

        // WhatsApp if enabled globally AND user accepts it
        if (GlobalNotificationSettingsService::isWhatsAppEnabled() && !empty($notifiable->accept_whatsapp)) {
            $channels[] = WhatsAppChannel::class;
        }

        // Pusher (broadcast) if enabled globally AND user accepts it
        if (GlobalNotificationSettingsService::isPusherEnabled() && !empty($notifiable->accept_pusher)) {
            $channels[] = 'broadcast';
        }

        // Email if enabled globally AND user accepts it OR as default when nothing else is active
        if ((GlobalNotificationSettingsService::isEmailEnabled() && !empty($notifiable->accept_email)) || count($channels) === 1) { // only 'database' present
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {


        Log::info('Mail data:', ['last_step' => $this->data['last_step']]);
        return (new MailMessage)
            ->subject(__('notifications.change_cycle.subject'))
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
            'message' => __('notifications.change_cycle.database', [
                'company' => $this->data['company_name_en'] ?? 'N/A',
                'from' => $this->data['last_step'] ?? 'N/A',
                'to' => $this->data['new_step'] ?? 'N/A',
                'org' => $this->data['organization_name_en'] ?? 'N/A',
            ]),
        ];
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.change_cycle.whatsapp', [
            'company' => $this->data['company_name_en'] ?? 'N/A',
            'from' => $this->data['last_step'] ?? 'N/A',
            'to' => $this->data['new_step'] ?? 'N/A',
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('notifications.change_cycle.subject'),
            'message' => __('notifications.change_cycle.broadcast', [
                'company' => $this->data['company_name_en'] ?? 'N/A',
                'to' => $this->data['new_step'] ?? 'N/A',
            ]),
            'time' => now()->toDateTimeString(),
        ]);
    }
}
