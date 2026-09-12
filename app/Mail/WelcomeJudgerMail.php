<?php

namespace App\Mail;

use App\Traits\RoutesTenantMail;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeJudgerMail extends Mailable
{
    use Queueable, SerializesModels, ConfiguresSmtp, RoutesTenantMail;

    public $user;
    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }


    public function build()
    {
        $org = $this->user->organization ?? null;
        $orgData = $this->buildEmailOrganizationData($org);

        return $this->subject('مرحبًا بك كـ Judger!')
                    ->view('emails.welcome_judger')
                    ->with($orgData);
    }
    
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome Judger Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
