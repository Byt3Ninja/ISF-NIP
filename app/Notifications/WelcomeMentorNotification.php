<?php

namespace App\Notifications;

use App\Settings;
use App\Broadcasting\WhatsAppChannel;
use App\Services\WhatsAppService;
use App\Traits\ConfiguresSmtp;
use App\Support\ReleasesNotificationClaim;
use Illuminate\Bus\Queueable;
use Throwable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeMentorNotification extends Notification implements ShouldQueue
{
    use Queueable, ConfiguresSmtp, ReleasesNotificationClaim;

    public $user;

    public WhatsAppService $whatsAppService;



    
    /**
     * Create a new notification instance.
     */
    public function __construct($user)
    {
        // No password: the mail only links to the login page (the mentor chose the
        // password), and a queued notification must not serialize it into the jobs table.
        $this->user = $user;
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

        // WhatsApp if enabled for user and channel available
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
    public function toMail(object $notifiable): MailMessage
    {
        $setting = Settings::first();
        $platformName = $setting ? ( $setting->Platform_Name ?? config('app.name') ) : config('app.name');
        $org = $this->user->organization ?? $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;

        $from = $this->getFromAddress($smtpSettings);
        // This is a platform-branded email (platform logo/name), not the
        // mentor's organization -- SMTP routing still uses the org, above.
        $orgData = $this->emailBrandingData();

        $this->configureSmtp($smtpSettings);

        // Follow the platform's default language (General Settings).
        $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';

        $firstName = $locale === 'ar' ? trim((string) $this->user->first_name_ar) : '';
        $lastName = $locale === 'ar' ? trim((string) $this->user->last_name_ar) : '';
        if ($firstName === '') {
            $firstName = trim((string) $this->user->first_name_en);
            $lastName = trim((string) $this->user->last_name_en);
        }

        return (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject(__('email.mentor_welcome_email.subject', ['platform_name' => $platformName], $locale))
            ->view('emails.welcome_mentor_email', array_merge([
                // Named distinctly from 'platformName': a global view composer
                // (AppServiceProvider) sets that key on every view and would
                // silently override this value.
                'mentorWelcomePlatformName' => $platformName,
                'mentorWelcomeLocale' => $locale,
                'mentorWelcomeName' => trim($firstName . ' ' . $lastName),
                'mentorWelcomeGender' => strtolower(trim((string) $this->user->gender)),
                // The platform link opens the login page.
                'mentorWelcomeUrl' => route('login'),
            ], $orgData));
    }

    /**
     * Summary of toWhatsapp
     * @param object $notifiable
     * @return array
     */
    public function toWhatsapp(object $notifiable): string
    {
        return " " . __('email.welcome_email.dear') . ' ' . ($notifiable->first_name_en ?? '') .
               ' ' . ($notifiable->last_name_en ?? '') . "\n" .
               __('email.welcome_email.m1') . "\n" .
               __('email.welcome_email.m2') . ' ' . ($notifiable->email ?? '');
    }


    /**
     * Summary of toBroadcast
     * @param object $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast(object $notifiable) 
    {
     return new BroadcastMessage([
        'subject' => __('emails.welcome_email.subject'),
        'dear'    => __('emails.welcome_email.dear') . ' ' . $notifiable->name,
        'm1'      => __('emails.welcome_email.m1'),
        'm2'      => __('emails.welcome_email.m2') . ' ' . $notifiable->email,
        'm3'      => __('emails.welcome_email.m3'),
        'best'    => __('emails.welcome_email.best_regards'),
        'time'    => now()->toDateTimeString(),
    ]);
    }

    /** Queued: when the worker gives up, free the welcome slot so it can be sent again. */
    public function failed(Throwable $exception): void
    {
        $this->releaseClaim();
        report($exception);
    }
}
