<?php

namespace App\Traits;

use App\Services\EmailBranding;
use App\Services\TenantMailer;

/**
 * Routes a Mailable through the involved organization's SMTP account.
 *
 * Mailables sent with Mail::to()->send()/queue() never pass through the
 * notification pipeline, so they need their own hook: Mailable::send()
 * resolves the mailer itself, which makes overriding it the last point where
 * the transport can still be swapped.
 *
 * A mailable can expose the organization explicitly by defining a
 * mailOrganization() method; otherwise it is resolved from a public
 * $organization / $user property, or from the recipient address.
 */
trait RoutesTenantMail
{
    /**
     * @param  \Illuminate\Contracts\Mail\Factory|\Illuminate\Contracts\Mail\Mailer  $mailer
     */
    public function send($mailer)
    {
        return TenantMailer::sendWithFallback(
            $this->resolveMailOrganization(),
            function () use ($mailer) {
                // Mailer::send() stamps the sender from the mailer instance it
                // is called on, and Mail::to()->send() hands us an instance
                // that was resolved before the organization's config was
                // applied. Resolve a fresh one so the correct account is used.
                return parent::send($this->freshMailer($mailer));
            }
        );
    }

    /**
     * Rebuild the mailer against the currently applied configuration.
     *
     * @param  \Illuminate\Contracts\Mail\Factory|\Illuminate\Contracts\Mail\Mailer  $mailer
     */
    protected function freshMailer($mailer)
    {
        $factory = app(\Illuminate\Contracts\Mail\Factory::class);

        // MailConfig::set() already purged the cached mailers, so this builds
        // a mailer using the organization's transport and sender. $this->mailer
        // is ignored: Mailer::sendMailable() pins it to the mailer the send
        // started on, before the organization was applied (smtp for an
        // organization on Resend, or the reverse).
        return $factory->mailer(config('mail.default'));
    }

    /**
     * Work out which organization this mailable belongs to.
     */
    protected function resolveMailOrganization()
    {
        // The organization the email is about, as for its branding and From.
        if ($organization = EmailBranding::forMail($this)) {
            return $organization;
        }

        if (method_exists($this, 'mailOrganization')) {
            $organization = TenantMailer::resolveOrganization($this->mailOrganization());

            if ($organization) {
                return $organization;
            }
        }

        foreach (['organization', 'org', 'user', 'assignedUser', 'invitation', 'cycle'] as $property) {
            if (isset($this->{$property})) {
                $organization = TenantMailer::resolveOrganization($this->{$property});

                if ($organization) {
                    return $organization;
                }
            }
        }

        // Fall back to the recipient address, which TenantMailer maps back to
        // a user and therefore to an organization.
        return $this->to[0]['address'] ?? null;
    }
}
