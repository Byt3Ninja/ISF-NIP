<?php

namespace App\Notifications;

use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProgramCreatedNotification extends Notification implements ShouldQueue
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

    public function __construct(
        public $cycle,
        public $admin
    ) {
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
            ->subject(__('notifications.program_created.subject'))
            ->view('emails.program-created', array_merge([
                'cycle' => $this->cycle,
                'admin' => $this->admin,
            ], $orgData));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'program_created',
            'cycle_id' => $this->cycle->id ?? null,
            'admin_id' => $this->admin->id ?? null,
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
        Log::error('ProgramCreatedNotification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'cycle_id' => $this->cycle->id ?? null,
            'admin_id' => $this->admin->id ?? null,
        ]);
    }
}
