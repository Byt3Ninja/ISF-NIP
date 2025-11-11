<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;

class ConfirmEmailUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $url)
    {
    }

    public function via(object $notifiable): array
    {
        // If we're routing to a raw email (AnonymousNotifiable), only mail makes sense
        if ($notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable) {
            return GlobalNotificationSettingsService::isEmailEnabled() ? ['mail'] : ['database'];
        }

        $channels = ['database'];

        // WhatsApp if enabled globally AND user accepts it
        if (GlobalNotificationSettingsService::isWhatsAppEnabled() && !empty($notifiable->accept_whatsapp)) {
            $channels[] = WhatsAppChannel::class;
        }
        // Pusher if enabled globally AND user accepts it
        if (GlobalNotificationSettingsService::isPusherEnabled() && !empty($notifiable->accept_pusher)) {
            $channels[] = 'broadcast';
        }
        // Email if enabled globally AND user accepts it OR as default when nothing else is active
        if ((GlobalNotificationSettingsService::isEmailEnabled() && !empty($notifiable->accept_email)) || count($channels) === 1) { // only 'database'
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.confirm_email_update.subject'))
            ->view('admin.emails.updateEmail', [
                'url' => $this->url,
            ]);
    }

    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.confirm_email_update.whatsapp', [
            'url' => $this->url,
        ]);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('notifications.confirm_email_update.subject'),
            'message' => __('notifications.confirm_email_update.broadcast'),
            'url' => $this->url,
            'time' => now()->toDateTimeString(),
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => __('notifications.confirm_email_update.database'),
            'url' => $this->url,
        ];
    }
}
