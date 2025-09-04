<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Broadcasting\WhatsAppChannel;

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
            return ['mail'];
        }

        $channels = ['database'];

        if (!empty($notifiable->accept_whatsapp)) {
            $channels[] = WhatsAppChannel::class;
        }
        if (!empty($notifiable->accept_pusher)) {
            $channels[] = 'broadcast';
        }
        if (!empty($notifiable->accept_email) || count($channels) === 1) { // only 'database'
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
