<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail;

class VerifyEmailQueued extends VerifyEmail implements ShouldQueue
{
    use Queueable;
    public $name;
    public $url;

    // Nothing else needs to go here unless you want to customize
    // the notification in any way.
    public function __construct($name)
    {
        $this->name = $name;
        $this->url = static::$createUrlCallback;
    }
    public function build()
    {
        return $this->view('emails.emailVerify');
    }
}