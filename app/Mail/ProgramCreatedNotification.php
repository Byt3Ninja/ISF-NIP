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

class ProgramCreatedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, ConfiguresSmtp, RoutesTenantMail;

    public $cycle;
    public $admin; 

    /**
     * Create a new message instance.
     */
    public function __construct($cycle, $admin)
    {
        $this->cycle = $cycle;
        $this->admin = $admin;
    }
    public function build()
    {
        $org = $this->cycle->organization ?? null;
        $orgData = $this->buildEmailOrganizationData($org);

        return $this->subject('A New Program has been Created')
                    ->view('emails.program-created')
                    ->with($orgData);
    }

    

}
