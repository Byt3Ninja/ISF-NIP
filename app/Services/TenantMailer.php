<?php

namespace App\Services;

use App\Organization;
use App\OrganizationSettings;
use App\Settings;
use App\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Single source of truth for "which SMTP account sends this email".
 *
 * Resolution order:
 *   1. The involved organization's own SMTP / Resend configuration.
 *   2. The platform-wide configuration in the `settings` table.
 *   3. Whatever is in config/mail.php (env).
 *
 * If an organization's own transport fails at send time, the message is
 * retried once on the platform transport and the organization's settings
 * row is flagged so admins can see the configuration is broken.
 */
class TenantMailer
{
    /**
     * Snapshot of the mail config as it was before an organization override
     * was applied, so it can be restored and never leak into the next job
     * handled by the same queue worker.
     */
    protected static ?array $baseline = null;

    /**
     * The config applied for the send currently in flight, and the
     * organization it belongs to. Lets code further down the send (notably
     * ConfiguresSmtp) see what has already been resolved instead of
     * redundantly falling back to the platform configuration.
     */
    protected static ?array $current = null;

    protected static ?Organization $currentOrganization = null;

    /**
     * The organization the in-flight send is being routed for, if any.
     */
    public static function currentOrganization(): ?Organization
    {
        return static::$currentOrganization;
    }

    /**
     * The mail config applied for the in-flight send, if any.
     */
    public static function currentConfig(): ?array
    {
        return static::$current;
    }

    /**
     * Resolve the organization that an email belongs to.
     *
     * Accepts anything a caller happens to have: an Organization, a model
     * exposing ->organization, a notifiable, or a bare recipient email
     * address (used by the Notification::route('mail', $email) call sites,
     * whose AnonymousNotifiable carries no organization at all).
     */
    public static function resolveOrganization($source): ?Organization
    {
        if (!$source) {
            return null;
        }

        if ($source instanceof Organization) {
            return $source;
        }

        // Any model that exposes an organization (User, Cycle, Invitation, ...).
        if (is_object($source)) {
            $org = $source->organization ?? null;
            if ($org instanceof Organization) {
                return $org;
            }

            // AnonymousNotifiable: recover the address it is routed to.
            $routes = $source->routes ?? null;
            if (is_array($routes) && isset($routes['mail'])) {
                $source = $routes['mail'];
            }
        }

        // A recipient email address (or an array/collection of them).
        if (is_array($source)) {
            $source = array_key_first($source) !== 0
                ? array_key_first($source)
                : ($source[0] ?? null);
        }

        if (is_string($source) && filter_var($source, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $source)->first();

            return $user?->organization;
        }

        return null;
    }

    /**
     * Build a usable mail config array for an organization, or null when the
     * organization has no complete configuration of its own.
     */
    public static function configForOrganization(?Organization $organization): ?array
    {
        $settings = $organization?->organizationSettings;

        if (!$settings) {
            return null;
        }

        if (static::resendConnected($settings)) {
            return [
                'transport'    => 'resend',
                'api_key'      => $settings->resend_api_key,
                'from_address' => $settings->resend_from_address,
                'from_name'    => $settings->resend_from_name ?: ($organization->name ?? config('mail.from.name')),
                'source'       => 'organization',
                'organization_id' => $organization->id,
            ];
        }

        // An SMTP config is only usable with a host to connect to. Also the
        // fallback for a Resend setup that never passed its test email.
        if (empty($settings->smtp_host)) {
            return null;
        }

        return [
            'transport'    => EmailSettingsStore::transportFor($settings->smtp_preset),
            'host'         => $settings->smtp_host,
            'port'         => (int) ($settings->smtp_port ?: 587),
            'encryption'   => $settings->smtp_encryption,
            'username'     => $settings->smtp_username,
            'password'     => $settings->smtp_password,
            'timeout'      => 30,
            'auth_mode'    => null,
            'from_address' => static::safeFromAddress($settings),
            'from_name'    => $settings->smtp_from_name ?: ($organization->name ?? config('mail.from.name')),
            'source'       => 'organization',
            'organization_id' => $organization->id,
        ];
    }

    /**
     * Build the platform-wide mail config from the `settings` table. Boot
     * passes the row it already loaded.
     */
    public static function globalConfig(?Settings $setting = null): ?array
    {
        try {
            $setting ??= Settings::first();
        } catch (\Throwable $e) {
            return null;
        }

        if (!$setting) {
            return null;
        }

        if (static::resendConnected($setting)) {
            return [
                'transport'    => 'resend',
                'api_key'      => $setting->resend_api_key,
                'from_address' => $setting->resend_from_address,
                'from_name'    => $setting->resend_from_name ?: ($setting->Platform_Name ?? config('app.name')),
                'source'       => 'global',
            ];
        }

        if (empty($setting->host)) {
            return null;
        }

        return [
            'transport'    => EmailSettingsStore::transportFor($setting->smtp_preset),
            'host'         => $setting->host,
            'port'         => (int) ($setting->Port ?: 587),
            'encryption'   => $setting->encryption,
            'username'     => $setting->Mail,
            'password'     => $setting->Mailpassword,
            'timeout'      => 30,
            'auth_mode'    => null,
            'from_address' => $setting->mail_from_address ?: $setting->Mail,
            'from_name'    => $setting->mail_from_name ?: ($setting->Platform_Name ?? config('app.name')),
            'source'       => 'global',
        ];
    }

    /**
     * A Resend (API) setup only goes live once its test email went through
     * (GV-1513 / GV-1514); until then the SMTP settings or the platform apply.
     */
    public static function resendConnected(Settings|OrganizationSettings $settings): bool
    {
        return $settings->email_provider === 'resend'
            && !empty($settings->resend_api_key)
            && !empty($settings->resend_from_address)
            && !empty($settings->resend_connected_at);
    }

    /**
     * Pick the config an email should be sent with: the organization's own,
     * else the platform default.
     */
    public static function configFor($source): ?array
    {
        $organization = static::resolveOrganization($source);

        return static::configForOrganization($organization) ?? static::globalConfig();
    }

    /**
     * Resolve and apply the transport for an email in one step, recording the
     * organization so the rest of the send can see what was chosen.
     *
     * @param  mixed  $source  Organization, model, notifiable or email address.
     */
    public static function applyFor($source): ?array
    {
        $organization = static::resolveOrganization($source);
        $config = static::configForOrganization($organization) ?? static::globalConfig();

        static::apply($config);
        static::$currentOrganization = $organization;

        return $config;
    }

    /**
     * Apply a mail config for the duration of the current send, remembering
     * the previous state so it can be restored afterwards.
     */
    public static function apply(?array $config): ?array
    {
        if (!$config) {
            return null;
        }

        if (static::$baseline === null) {
            static::$baseline = static::snapshot();
        }

        MailConfig::set($config);

        static::$current = $config;

        if (!empty($config['organization_id'])) {
            static::$currentOrganization = Organization::find($config['organization_id']);
        }

        Log::info('Mail transport selected', [
            'source'          => $config['source'] ?? 'unknown',
            'transport'       => $config['transport'] ?? 'smtp',
            'host'            => $config['host'] ?? null,
            'from_address'    => $config['from_address'] ?? null,
            'from_name'       => $config['from_name'] ?? null,
            'organization_id' => $config['organization_id'] ?? null,
        ]);

        return $config;
    }

    /**
     * Restore the mail config captured before the last apply(). Prevents one
     * tenant's SMTP settings leaking into the next job on a shared worker.
     */
    public static function restore(): void
    {
        if (static::$baseline === null) {
            return;
        }

        foreach (static::$baseline as $key => $value) {
            Config::set($key, $value);
        }

        static::$baseline = null;
        static::$current = null;
        static::$currentOrganization = null;

        Mail::forgetMailers();
    }

    /**
     * Send using the organization's transport, falling back to the platform
     * transport if the organization's transport fails, or to the boot (.env)
     * transport when the platform has no setup either.
     *
     * @param  mixed     $source    Organization, model, notifiable or email address.
     * @param  callable  $send      Performs the actual send.
     */
    public static function sendWithFallback($source, callable $send)
    {
        $organization = static::resolveOrganization($source);
        $orgConfig    = static::configForOrganization($organization);

        try {
            // No organization transport: send straight on the platform transport.
            if (!$orgConfig) {
                static::apply(static::globalConfig());

                return $send();
            }

            static::apply($orgConfig);

            try {
                $result = $send();
                static::clearFailureFlag($organization);

                return $result;
            } catch (\Throwable $e) {
                Log::warning('Organization mail transport failed, falling back to platform SMTP', [
                    'organization_id' => $organization?->id,
                    'host'            => $orgConfig['host'] ?? null,
                    'error'           => $e->getMessage(),
                ]);

                static::flagFailure($organization, $e->getMessage());

                $global = static::globalConfig();

                // No platform setup: retry on the transport boot left in place
                // (config/mail.php, i.e. .env), the last step of the chain.
                if (!$global) {
                    static::restore();

                    return $send();
                }

                static::apply($global);

                return $send();
            }
        } finally {
            static::restore();
        }
    }

    /**
     * Record that an organization's mail transport is failing so it can be
     * surfaced to admins in the organization settings screen.
     */
    protected static function flagFailure(?Organization $organization, string $error): void
    {
        $settings = $organization?->organizationSettings;

        if (!$settings) {
            return;
        }

        try {
            $settings->forceFill([
                'smtp_last_error' => mb_substr($error, 0, 500),
                'smtp_failed_at'  => now(),
            ])->saveQuietly();
        } catch (\Throwable $e) {
            // Flagging must never break sending.
        }
    }

    /**
     * Clear a previously recorded failure once the transport works again.
     */
    protected static function clearFailureFlag(?Organization $organization): void
    {
        $settings = $organization?->organizationSettings;

        if (!$settings || $settings->smtp_last_error === null) {
            return;
        }

        try {
            $settings->forceFill([
                'smtp_last_error'    => null,
                'smtp_failed_at'     => null,
            ])->saveQuietly();
        } catch (\Throwable $e) {
            // Ignore.
        }
    }

    /**
     * From-address that will not trip SMTP sender authentication: providers
     * commonly reject a From that is not the authenticated mailbox or one of
     * its domain aliases.
     */
    protected static function safeFromAddress(OrganizationSettings $settings): ?string
    {
        $username = $settings->smtp_username;
        $custom   = $settings->smtp_from_address;

        if (!$username) {
            return $custom;
        }

        if (!$custom) {
            return $username;
        }

        $domain = str_contains($username, '@')
            ? substr(strrchr($username, '@'), 1)
            : null;

        return ($domain && str_ends_with($custom, '@' . $domain)) ? $custom : $username;
    }

    /**
     * Capture the mail config keys that an override rewrites.
     */
    protected static function snapshot(): array
    {
        $keys = [
            'mail.driver',
            'mail.default',
            'mail.host',
            'mail.port',
            'mail.username',
            'mail.password',
            'mail.encryption',
            'mail.mailers.smtp.transport',
            'mail.mailers.smtp.host',
            'mail.mailers.smtp.port',
            'mail.mailers.smtp.username',
            'mail.mailers.smtp.password',
            'mail.mailers.smtp.encryption',
            'mail.from.address',
            'mail.from.name',
            'resend.api_key',
        ];

        $snapshot = [];

        foreach ($keys as $key) {
            $snapshot[$key] = config($key);
        }

        return $snapshot;
    }
}
