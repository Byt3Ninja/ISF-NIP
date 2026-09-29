<?php

namespace App\Mail;

use App\Traits\RoutesTenantMail;
use App\Invitation;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, ConfiguresSmtp, RoutesTenantMail;

    public $invitation;

    /**
     * Create a new message instance.
     */
    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }


    public function build()
    {
        $org = $this->invitation->organization ?? null;
        $orgData = $this->buildEmailOrganizationData($org);

        return $this->subject('Invitation to Join Organization')
                    ->view('emails.invitation')
                    ->with($orgData);
    }

}
