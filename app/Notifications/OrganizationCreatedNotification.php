<?php

namespace App\Notifications;

use App\Organization;
use App\Services\GlobalNotificationSettingsService;
use App\Traits\ConfiguresSmtp;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Broadcasting\WhatsAppChannel;
use Throwable;

class OrganizationCreatedNotification extends Notification implements ShouldQueue
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
        public Organization $organization,
        public User $user,
        public string $password
    ) {
    }

 /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $org = $this->organization;
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
            && $this->user->accept_whatsapp) {
            $channels[] = WhatsAppChannel::class;
        }

        // Pusher (broadcast) if enabled globally AND organization/user accepts it
        if (GlobalNotificationSettingsService::isPusherEnabled() 
            && $orgAcceptPusher 
            && $this->user->accept_pusher) {
            $channels[] = 'broadcast';
        }

        // Email if enabled globally AND organization/user accepts it
        if (GlobalNotificationSettingsService::isEmailEnabled() 
            && $orgAcceptEmail 
            && $this->user->accept_email) {
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
        $org = $this->organization;
        $smtpSettings = $org?->organizationSettings;
        
        // Get from address and name
        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->buildEmailOrganizationData($org);

        return (new MailMessage)
            ->subject(__('notifications.organization_created.subject'))
            ->view('emails.organization_created', array_merge([
                'organization' => $this->organization,
                'user' => $this->user,
                'password' => $this->password,
            ], $orgData));
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

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('OrganizationCreatedNotification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'organization_id' => $this->organization->id ?? null,
            'user_id' => $this->user->id ?? null,
        ]);
    }
}
