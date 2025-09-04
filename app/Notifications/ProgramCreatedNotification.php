<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProgramCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public $cycle,
        public $admin
    ) {
    }

    public function via(object $notifiable): array
    {
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
        if (!empty($notifiable->accept_email) || count($channels) === 1) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.program_created.subject'))
            ->view('emails.program-created', [
                'cycle' => $this->cycle,
                'admin' => $this->admin,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'program_created',
            'cycle_id' => $this->cycle->id ?? null,
            'admin_id' => $this->admin->id ?? null,
        ];
    }
}
