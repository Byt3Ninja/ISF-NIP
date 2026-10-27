<?php

namespace App\Notifications;

use App\Broadcasting\WhatsAppChannel;
use App\Settings;
use App\Services\WhatsAppService;
use App\Traits\ConfiguresSmtp;
use App\Support\ReleasesNotificationClaim;
use Illuminate\Bus\Queueable;
use Throwable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeUserNotification extends Notification implements ShouldQueue
{
    use Queueable, ConfiguresSmtp, ReleasesNotificationClaim;
 
    public $user;

    public WhatsAppService $whatsAppService;



    
    /**
     * Create a new notification instance.
     */
    public function __construct($user)
    {
        // No password: the mail signs the user in with a magic link, and a queued
        // notification must not serialize a plaintext password into the jobs table.
        $this->user = $user;

        // The user's own profile language; a new user has none yet, so they
        // get the platform default language chosen in the settings. Laravel
        // renders the whole notification (subject, view, layout) in it.
        $setting = Settings::first();
        $platformLocale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
        $this->locale = in_array($user->language, ['en', 'ar'], true) ? $user->language : $platformLocale;
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
        $org = $this->user->organization ?? $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;

        $from = $this->getFromAddress($smtpSettings);
        // Always the platform's own branding (logo/name), never the user's
        // organization -- SMTP routing still uses the org, above.
        $orgData = $this->emailBrandingData();

        $this->configureSmtp($smtpSettings);
        $magicLoginUrl = $notifiable->generateMagicLoginUrl();

        $firstName = $this->locale === 'ar' ? trim((string) $this->user->first_name_ar) : '';
        $lastName = $this->locale === 'ar' ? trim((string) $this->user->last_name_ar) : '';
        if ($firstName === '') {
            $firstName = trim((string) $this->user->first_name_en);
            $lastName = trim((string) $this->user->last_name_en);
        }

        return (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject(__('email.welcome_email.subject'))
            ->view('emails.welcome-email', array_merge([
                'user'          => $this->user,
                'magicLoginUrl' => $magicLoginUrl,
                'welcomeRecipientName' => trim($firstName . ' ' . $lastName),
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

    /**
     * Get the array representation of the notification.
     * This is used for the 'database' channel to store the notification data in the database.
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => __('notifications.welcome.database')
        ];
    }

    /** Queued: when the worker gives up, free the welcome slot so it can be sent again. */
    public function failed(Throwable $exception): void
    {
        $this->releaseClaim();
        report($exception);
    }
}
