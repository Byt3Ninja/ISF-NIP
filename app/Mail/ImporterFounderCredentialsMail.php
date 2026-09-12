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
        $subject = $locale === 'ar'
            ? 'تمت إضافتك إلى برنامج'
            : "You've been added to a program.";
        return $this->subject($subject)
                    ->view('emails.importer.credentials', ['data' => $this->payload]);
    }
}
