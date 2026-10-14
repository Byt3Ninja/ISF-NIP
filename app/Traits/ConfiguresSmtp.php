<?php

namespace App\Traits;

use App\Services\TenantMailer;
use App\Settings;
use Illuminate\Support\Facades\Storage;

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
     * @param  mixed  $organization
     */
    protected function buildEmailOrganizationData($organization): array
    {
        try {
            $setting = Settings::first();
        } catch (\Throwable $e) {
            $setting = null;
        }

        $defaultName = $setting ? ($setting->Platform_Name ?? config('app.name')) : config('app.name');

        return [
            'org_name'      => $organization?->name ?? $defaultName,
            // image_url falls back to a RELATIVE placeholder path when the
            // organization has no logo, which renders as a broken image in a
            // mail client. Only a real logo is passed, so the layout falls back
            // to the platform logo instead (GV-8625).
            'org_logo_url'  => filled($organization?->img_url) ? $organization->image_url : null,
            'org_logo_path' => $this->embeddableLogoPath($organization),
            'org_address'   => $organization?->address ?? null,
            'org_website'   => $organization?->website_url ?? null,
            'org_linkedin'  => $organization?->linkedin_company_url ?? null,
            'org_twitter'   => $organization?->twitter_handle ?? null,
            'org_facebook'  => $organization?->facebook_page_url ?? null,
            'org_instagram' => $organization?->instagram_profile_url ?? null,
        ];
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

    /**
     * Local file path of the organization logo, so the layout can embed it
     * (CID) when mail.embed_logos is on; null otherwise.
     */
    protected function embeddableLogoPath($organization): ?string
    {
        if (!config('mail.embed_logos') || blank($organization?->img_url)) {
            return null;
        }

        try {
            $disk = Storage::disk('organizations');

            return $disk->exists($organization->img_url) ? $disk->path($organization->img_url) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
