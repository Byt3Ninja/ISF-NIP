<?php

namespace App\Notifications;

use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;
use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;
use Throwable;

class ConfirmEmailUpdateNotification extends Notification implements ShouldQueue
{
  use Queueable, ConfiguresSmtp;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    public function __construct(private string $url)
    {
    }

   /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;
        
        // Configure SMTP if settings exist
        $this->configureSmtp($smtpSettings);
        
        // Organization channel toggles (default to true when not configured)
        $orgAcceptEmail = $smtpSettings?->accept_email ?? true;
        $orgAcceptWhatsapp = $smtpSettings?->accept_whatsapp ?? true;
        $orgAcceptPusher = $smtpSettings?->accept_pusher ?? true;

        $channels = [];

        // WhatsApp if enabled globally AND organization/user accepts it
        if (GlobalNotificationSettingsService::isWhatsAppEnabled() 
            && $orgAcceptWhatsapp 
            && $notifiable->accept_whatsapp) {
            $channels[] = WhatsAppChannel::class;
        }

        // Pusher (broadcast) if enabled globally AND organization/user accepts it
        if (GlobalNotificationSettingsService::isPusherEnabled() 
            && $orgAcceptPusher 
            && $notifiable->accept_pusher) {
            $channels[] = 'broadcast';
        }

        // Email if enabled globally AND organization/user accepts it
        if (GlobalNotificationSettingsService::isEmailEnabled() 
            && $orgAcceptEmail 
            && $notifiable->accept_email) {
            $channels[] = 'mail';
        }

        // Always store in database for audit/history if any channels are active
        if (!empty($channels)) {
            $channels[] = 'database';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;
        
        // Get from address and name
        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->buildEmailOrganizationData($org);

        return (new MailMessage)
            ->subject(__('notifications.confirm_email_update.subject'))
            ->view('admin.emails.updateEmail', array_merge([
                'url' => $this->url,
            ], $orgData));
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

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('ConfirmEmailUpdateNotification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
