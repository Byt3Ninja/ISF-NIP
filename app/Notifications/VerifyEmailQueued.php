<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class VerifyEmailQueued extends VerifyEmail implements ShouldQueue
{
    use Queueable;
    public $data=[];


    // Nothing else needs to go here unless you want to customize
    // the notification in any way.
    public function __construct($name)
    {
        $this->data['name'] = $name;
        $this->data['url'] = static::$createUrlCallback;
    }
//    public function build()
//    {
//        return $this->subject("Email Verification")
//            ->markdown('emails.verify');
//    }
    protected function buildMailMessage($url)
    {
        dd($url,$this->data['url']);
        return (new MailMessage)
            ->subject(Lang::get('Verify Email Address'))
            ->markdown('emails.emailVerify',$this->data);
    }
}