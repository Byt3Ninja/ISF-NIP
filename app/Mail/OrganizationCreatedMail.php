<?php

namespace App\Mail;

use App\Traits\RoutesTenantMail;
use App\User;
use App\Organization;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class OrganizationCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, ConfiguresSmtp, RoutesTenantMail;

    public $organization;
    public $user;
    public $password;
    /**
     * Create a new message instance.
     */
    public function __construct(Organization $organization, User $user, $password)
    {
        $this->organization = $organization;
        $this->user = $user;
        $this->password = $password;
    }

     public function build()
    {
        $orgData = $this->emailBrandingData();

        return $this->subject('New Organization Created')
                    ->view('emails.organization_created')
                    ->with(array_merge(['organization' => $this->organization, 'user' => $this->user, 'password' => $this->password], $orgData));
    // Log::info('Organization created email sent successfully', ['organization' => $this->organization, 'user' => $this->user, 'password' => $this->password]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Organization Created Mail',
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
