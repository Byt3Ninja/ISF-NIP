<?php

namespace App\Notifications;

use App\Settings;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class VerifyEmailQueued extends VerifyEmail implements ShouldQueue
{
    use Queueable, ConfiguresSmtp;

    public $data=[];

    protected $user = null;

    protected $organization = null;

    protected $orgData = [];


    // Nothing else needs to go here unless you want to customize
    // the notification in any way.
    public function __construct($name)
    {
        $this->data['name'] = $name;
        $this->data['url'] = static::$createUrlCallback;
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;

        $this->user = $notifiable;
        $this->organization = $org;

        $this->configureSmtp($smtpSettings);
        $from = $this->getFromAddress($smtpSettings);
        $this->orgData = $this->buildEmailOrganizationData($org);

        return parent::toMail($notifiable);
    }

    protected function buildMailMessage($url)
    {
        $setting = Settings::first();
        $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
        $platformName = $setting?->Platform_Name ?: config('app.name');

        // Enrolled founders are branded with their organization, others with the platform.
        $orgData = $this->orgData ?? [];
        $hasOrg = ! empty($this->organization);
        $senderName = $hasOrg ? ($this->organization->name ?: $platformName) : $platformName;
        if (! $hasOrg) {
            $orgData = $this->buildEmailOrganizationData(null);
        }

        $firstName = $locale === 'ar' ? trim((string) $this->user?->first_name_ar) : '';
        if ($firstName === '') {
            $firstName = trim((string) $this->user?->first_name_en);
        }

        // Keys are named distinctly from 'platformName': a global view composer
        // (AppServiceProvider) sets that key on every view and would override it.
        $viewData = array_merge([
            'verifyUrl' => $url,
            'verifyLocale' => $locale,
            'verifyPlatformName' => $platformName,
            'verifySenderName' => $senderName,
            'verifyUserName' => $firstName,
            'verifyGender' => strtolower(trim((string) $this->user?->gender)),
        ], $orgData);

        return (new MailMessage)
            ->subject(__('email.verify_email_account.subject', ['platform_name' => $platformName], $locale))
            ->view('emails.emailVerify', $viewData);
    }
}
