<?php

namespace App\Notifications;

use App\Settings;
use App\Traits\ConfiguresSmtp;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ResetPassword extends Notification
{
    use ConfiguresSmtp;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * The callback that should be used to create the reset password URL.
     *
     * @var (\Closure(mixed, string): string)|null
     */
    public static $createUrlCallback;

    /**
     * Create a new notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
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
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        try {
            $org = $notifiable->organization;
            $smtpSettings = $org?->organizationSettings;

            $this->configureSmtp($smtpSettings);
            $from = $this->getFromAddress($smtpSettings);
            // Platform-branded email (platform logo/name); the org only routes SMTP.
            $orgData = $this->emailBrandingData();

            $resetUrl = $this->resetUrl($notifiable);
            $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            $setting = Settings::first();
            $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
            $platformName = $setting?->Platform_Name ?: config('app.name');

            $firstName = $locale === 'ar' ? trim((string) $notifiable->first_name_ar) : '';
            if ($firstName === '') {
                $firstName = trim((string) $notifiable->first_name_en);
            }

            return (new MailMessage)
                ->subject(__('email.password_reset_email.subject', [], $locale))
                ->view('emails.password-reset', array_merge([
                    'resetUrl' => $resetUrl,
                    'resetExpireMinutes' => $expire,
                    'resetLocale' => $locale,
                    // Named distinctly from 'platformName': a global view composer
                    // (AppServiceProvider) sets that key on every view and would
                    // silently override it.
                    'resetPlatformName' => $platformName,
                    'resetUserName' => $firstName,
                ], $orgData));

        } catch (\Exception $e) {
            // Log the error with context
            \Log::error('Failed to send password reset email', [
                'error' => $e->getMessage(),
                'user_id' => $notifiable->id ?? null,
                'email' => $notifiable->email ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw the exception to let Laravel handle it
            throw $e;
        }
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }

    /**
     * Set a callback that should be used when creating the reset password button URL.
     *
     * @param  \Closure(mixed, string): string  $callback
     * @return void
     */
    public static function createUrlUsing($callback)
    {
        static::$createUrlCallback = $callback;
    }
}