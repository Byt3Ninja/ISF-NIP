<?php

namespace App\Mail;

use App\Traits\RoutesTenantMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ImporterFounderCredentialsMail extends Mailable
{
    use Queueable, SerializesModels, RoutesTenantMail;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function build()
    {
        $locale = $this->payload['locale'] ?? 'en';
        $subject = __('email.importer_founder_added_email.subject', ['program_name' => $this->payload['program_name'] ?? ''], $locale);

        // The shared email layout (emails.layout.master) reads org_logo_url/
        // org_name etc. as top-level view data, not nested under 'data'.
        $orgKeys = array_intersect_key($this->payload, array_flip([
            'org_name', 'org_logo_url', 'org_address', 'org_website',
            'org_linkedin', 'org_twitter', 'org_facebook', 'org_instagram',
        ]));

        return $this->subject($subject)
                    ->view('emails.importer.credentials', array_merge($orgKeys, ['data' => $this->payload]));
    }
}
