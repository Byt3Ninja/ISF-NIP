<?php

namespace App\Notifications;

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

        $this->configureSmtp($smtpSettings);
        $from = $this->getFromAddress($smtpSettings);
        $this->orgData = $this->buildEmailOrganizationData($org);

        return parent::toMail($notifiable);
    }

    protected function buildMailMessage($url)
    {
        $this->data['url'] = $url;
        $viewData = array_merge($this->data, $this->orgData ?? []);

        return (new MailMessage)
            ->subject(Lang::get('Verify Email Address'))
            ->markdown('emails.emailVerify', $viewData);
    }
}