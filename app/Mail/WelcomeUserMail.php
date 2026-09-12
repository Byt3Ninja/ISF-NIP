<?php

namespace App\Mail;

use App\Traits\RoutesTenantMail;
use App\User;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeUserMail extends Mailable
{
    use Queueable, SerializesModels, ConfiguresSmtp, RoutesTenantMail;

    public $user;
    public $password;
    /**
     * Create a new message instance.
     */
     public function __construct( $user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

   public function build()
    {
        $org = $this->user->organization ?? null;
        $orgData = $this->buildEmailOrganizationData($org);

        $magicLoginUrl = $this->user->generateMagicLoginUrl();

        return $this->subject('Welcome to the Platform')
                    ->view('emails.welcome-email')
                    ->with(array_merge([
                        'user'          => $this->user,
                        'magicLoginUrl' => $magicLoginUrl,
                    ], $orgData));
    }

    
}
