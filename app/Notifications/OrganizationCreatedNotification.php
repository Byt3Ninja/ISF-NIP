<?php

namespace App\Notifications;

use App\Organization;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Broadcasting\WhatsAppChannel;

class OrganizationCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Organization $organization,
        public User $user,
        public string $password
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
            ->subject(__('notifications.organization_created.subject'))
            ->view('emails.organization_created', [
                'organization' => $this->organization,
                'user' => $this->user,
                'password' => $this->password,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'organization_created',
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
        ];
    }

    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.organization_created.whatsapp', [
            'organization' => $this->organization->name ?? '',
            'email' => $this->user->email ?? '',
            'password' => $this->password,
        ]);
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'title' => __('notifications.organization_created.subject'),
            'message' => __('notifications.organization_created.broadcast', [
                'organization' => $this->organization->name ?? '',
            ]),
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
        ];
    }
}
