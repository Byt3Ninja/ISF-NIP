<?php

namespace App\Mail;

use App\Traits\RoutesTenantMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ImporterFounderEnrolledMail extends Mailable
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
        $subject = $locale === 'ar'
            ? 'لقد انضممت إلى برنامج جديد'
            : "You've joined a new program.";
        return $this->subject($subject)
                    ->view('emails.importer.enrolled', ['data' => $this->payload]);
    }
}
