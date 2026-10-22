<?php

namespace App\Notifications;

use App\Cycle;
use App\Settings;
use App\Services\GlobalNotificationSettingsService;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;
use App\Broadcasting\WhatsAppChannel;
use Throwable;

class StartupJoiningNotification extends Notification implements ShouldQueue
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

    /**
     * Notification data.
     *
     * @var array
     */
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
        $this->configureSmtp($smtpSettings);

        $setting = Settings::first();
        $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
        $platformName = $setting?->Platform_Name ?: config('app.name');

        // Branded with the organization that runs the program; the platform
        // when the program has none.
        $cycle = ! empty($this->data['cycle_id']) ? Cycle::with('organization')->find($this->data['cycle_id']) : null;
        $programOrg = $cycle?->organization;
        $orgData = $this->emailBrandingData();

        $programName = $locale === 'ar'
            ? (trim((string) $cycle?->title_ar) ?: ($cycle?->title ?? ($this->data['cycle'] ?? '')))
            : ($cycle?->title ?? ($this->data['cycle'] ?? ''));
        $orgName = $programOrg
            ? (trim((string) $programOrg->getAttribute('name_' . $locale)) ?: $programOrg->getAttribute('name_en'))
            : $platformName;

        $firstName = $locale === 'ar' ? trim((string) $notifiable->first_name_ar) : '';
        if ($firstName === '') {
            $firstName = trim((string) $notifiable->first_name_en);
        }

        // Keys are named distinctly from 'platformName': a global view composer
        // (AppServiceProvider) sets that key on every view and would override it.
        return (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject(__('email.startup_join_program_email.subject', ['program_name' => $programName], $locale))
            ->view('emails.startup_join', array_merge([
                'joinLocale' => $locale,
                'joinProgramName' => $programName,
                'joinPlatformName' => $platformName,
                'joinOrgName' => $orgName,
                'joinFounderName' => $firstName,
                'joinGender' => strtolower(trim((string) $notifiable->gender)),
                'joinDashboardUrl' => url('/dashboard'),
            ], $orgData));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => __('admin.Startup_Joined_New_Program_subject'),
            'user_id' => $notifiable->id,
        ];
    }

     public function toDatabase(object $notifiable): array
    {
       return [
            'message' => ' Congratulations! You have successfully joined the ' . ($this->data['cycle'] ?? 'N/A') . 
                ' We are excited to have you on board and look forward to supporting your journey.
                Thanks from ' . env('APP_NAME'),
        ];
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.startup_join.whatsapp', [
            'cycle' => $this->data['cycle'] ?? 'N/A',
            'app' => config('app.name'),
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('admin.Startup_Joined_New_Program_subject'),
            'message' => __('notifications.startup_join.broadcast', ['cycle' => $this->data['cycle'] ?? 'N/A']),
            'time' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('StartupJoiningNotification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'data' => $this->data,
        ]);
    }
}
