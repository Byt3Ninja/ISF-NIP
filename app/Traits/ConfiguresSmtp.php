<?php

namespace App\Traits;

use App\Organization;
use App\Services\EmailBranding;
use App\Services\TenantMailer;

/**
 * Applies the correct SMTP account for the organization involved in an email,
 * falling back to the platform-wide configuration.
 *
 * All resolution logic lives in {@see TenantMailer}; this trait is the thin
 * entry point kept for the notification and mailable classes that already
 * call configureSmtp().
 */
trait ConfiguresSmtp
{
    /**
     * Configure the mail transport for the given organization settings.
     *
     * Historically callers pass an OrganizationSettings model, but anything
     * TenantMailer can resolve an organization from is accepted, including a
     * notifiable or a bare recipient email address.
     *
     * @param  mixed  $smtpSettings
     */
    protected function configureSmtp($smtpSettings): array
    {
        $config = TenantMailer::configForOrganization(
            $this->resolveSendingOrganization($smtpSettings)
        );

        // Only fall back to the platform config when no organization transport
        // was found. Applying it unconditionally would undo the organization
        // already resolved for this send by ConfigureTenantMailTransport.
        if (!$config) {
            $config = TenantMailer::currentConfig() ?? TenantMailer::globalConfig();
        }

        TenantMailer::apply($config);

        return $config ?? [];
    }

    /**
     * Work out which organization a send belongs to.
     *
     * Callers usually pass an OrganizationSettings model, but it is null
     * whenever the organization could not be determined locally (most often
     * for on-demand notifiables). In that case fall back to the organization
     * the send is already running under.
     *
     * @param  mixed  $smtpSettings
     */
    protected function resolveSendingOrganization($smtpSettings)
    {
        if ($organization = ($smtpSettings?->organization ?? null)) {
            return $organization;
        }

        if ($organization = TenantMailer::resolveOrganization($smtpSettings)) {
            return $organization;
        }

        // Set by the notification/mailable pipeline for this send.
        return TenantMailer::currentOrganization();
    }

    /**
     * Build the organization data array for email template branding.
     *
     * During a send the email layout is branded by {@see EmailBranding}
     * whatever is passed here. Emails use emailBrandingData() instead, so
     * their own text (sign-off, organization name) matches the logo.
     *
     * @param  mixed  $organization
     */
    protected function buildEmailOrganizationData($organization): array
    {
        return EmailBranding::data($organization instanceof Organization ? $organization : null);
    }

    /**
     * The organization this email is branded with: the one running the
     * program it is about, never the recipient's own (see EmailBranding).
     */
    protected function brandingOrganization(): ?Organization
    {
        return EmailBranding::forMail($this);
    }

    /**
     * The email layout variables (logo, org_name, footer links) for this
     * email, the same ones the layout itself is branded with during a send.
     */
    protected function emailBrandingData(): array
    {
        return EmailBranding::data($this->brandingOrganization());
    }

    /**
     * Get the from address and name the email should be sent with.
     *
     * @param  mixed  $smtpSettings
     * @return array{address: string|null, name: string|null}
     */
    protected function getFromAddress($smtpSettings): array
    {
        $organization = $smtpSettings?->organization
            ?? TenantMailer::resolveOrganization($smtpSettings);

        $config = TenantMailer::configForOrganization($organization)
            ?? TenantMailer::globalConfig();

        return [
            'address' => $config['from_address'] ?? config('mail.from.address'),
            'name'    => $config['from_name'] ?? config('mail.from.name'),
        ];
    }
}
