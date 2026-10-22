<?php

namespace App\Notifications;

use App\Settings;
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

    public function __construct(private string $url, private ?\App\User $user = null)
    {
    }

   /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $org = $this->user?->organization;
        $smtpSettings = $org?->organizationSettings;

        // Configure SMTP if settings exist
        $this->configureSmtp($smtpSettings);

        // This notification is sent to a new email via AnonymousNotifiable,
        // so only the mail channel is applicable.
        return ['mail'];
    }

    /**
     * Platform-branded email: the platform logo and name, never an
     * organization's (EmailBranding).
     */
    public function emailBranding()
    {
        return null;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $org = $this->user?->organization;
        $smtpSettings = $org?->organizationSettings;

        // Platform-branded email (platform logo/name); the org only routes SMTP.
        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->emailBrandingData();

        // Follow the platform's default language (General Settings).
        $setting = Settings::first();
        $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
        $platformName = $setting?->Platform_Name ?: config('app.name');

        $firstName = $locale === 'ar' ? trim((string) $this->user?->first_name_ar) : '';
        if ($firstName === '') {
            $firstName = trim((string) $this->user?->first_name_en);
        }

        return (new MailMessage)
            ->subject(__('email.email_change_confirm_email.subject', [], $locale))
            ->view('admin.emails.updateEmail', array_merge([
                'url' => $this->url,
                'locale' => $locale,
                // Named distinctly from 'platformName': a global view composer
                // (AppServiceProvider) sets that key on every view and would
                // silently override it.
                'emailChangePlatformName' => $platformName,
                'emailChangeFirstName' => $firstName,
                'emailChangeGender' => strtolower(trim((string) $this->user?->gender)),
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
