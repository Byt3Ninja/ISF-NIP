<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeUserMail extends Mailable
{
    use Queueable, SerializesModels;

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
        return $this->subject('Welcome to the Platform')
                    ->view('emails.welcome-email')
                    ->with([
                        'user' => $this->user,
                        'password' => $this->password
                    ]);
    }

    
}
