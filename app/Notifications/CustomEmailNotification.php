<?php

namespace App\Notifications;

use App\Cycle;
use App\Traits\ConfiguresSmtp;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;
use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;

class CustomEmailNotification extends Notification implements ShouldQueue
{
    use Queueable, ConfiguresSmtp;

    protected $user;
    protected $resetUrl;

    /**
     * The program whose founders are emailed; brands the email (EmailBranding).
     */
    protected $cycle;

    public function __construct(User $user, ?Cycle $cycle = null)
    {
        $this->user = $user;
        $this->cycle = $cycle;
        $token = Password::getRepository()->create($this->user);
        $this->resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $this->user->email,
        ], false));
    }

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

    public function toMail(object $notifiable): MailMessage
    {
        $org = $this->user->organization ?? $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;

        $this->configureSmtp($smtpSettings);
        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->emailBrandingData();

        return (new MailMessage)
            ->subject(__('notifications.custom_email.subject'))
            ->view('emails.custom-mail', array_merge([
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
            ], $orgData));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'custom_email',
            'user_id' => $this->user->id,
        ];
    }
}
