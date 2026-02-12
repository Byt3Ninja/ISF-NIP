<?php

namespace App\Notifications;

use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Broadcasting\WhatsAppChannel;

class WelcomeJudgerNotification extends Notification implements ShouldQueue
{
    use Queueable, ConfiguresSmtp;

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
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;

        $this->configureSmtp($smtpSettings);
        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->buildEmailOrganizationData($org);

        $token = Password::getRepository()->create($notifiable);

        $resetUrl = url(route('password.reset', [
        'token' => $token,
        'email' => $notifiable->email,
    ], false));
        $this->data['reset_url'] = $resetUrl;
            if ($this->data['role'] === 'Judger') {
                return (new MailMessage)
                    ->subject(__('notifications.welcome_judger.subject'))
                    ->markdown('emails.welcome_judger', array_merge(['data' => $this->data], $orgData));
            } else {
                return (new MailMessage)
                    ->subject(__('notifications.welcome.subject'))
                    ->markdown('emails.welcome', array_merge(['data' => $this->data], $orgData));
            }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->data['role'] === 'Judger') {
            return [
                'message' => __('notifications.welcome_judger.database'),
                'user_id' => $notifiable->id,
            ];
        } else {
            return [
                'message' => __('notifications.welcome.database'),
                'user_id' => $notifiable->id,
            ];
        }
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        if ($this->data['role'] === 'Judger') {
            return __('notifications.welcome_judger.whatsapp', [
                'name' => $notifiable->first_name_en . ' ' . $notifiable->last_name_en,
            ]);
        }
        return __('notifications.welcome.whatsapp', [
            'name' => $notifiable->first_name_en . ' ' . $notifiable->last_name_en,
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        if ($this->data['role'] === 'Judger') {
            return new BroadcastMessage([
                'subject' => __('notifications.welcome_judger.subject'),
                'message' => __('notifications.welcome_judger.broadcast'),
                'time' => now()->toDateTimeString(),
            ]);
        }
        return new BroadcastMessage([
            'subject' => __('notifications.welcome.subject'),
            'message' => __('notifications.welcome.broadcast'),
            'time' => now()->toDateTimeString(),
        ]);
    }
}
