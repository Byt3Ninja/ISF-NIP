<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;
use App\Broadcasting\WhatsAppChannel;

class CustomEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $resetUrl;

    public function __construct(User $user)
    {
        $this->user = $user;
        $token = Password::getRepository()->create($this->user);
        $this->resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $this->user->email,
        ], false));
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
            ->subject(__('notifications.custom_email.subject'))
            ->view('emails.custom-mail', [
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'custom_email',
            'user_id' => $this->user->id,
        ];
    }
}
