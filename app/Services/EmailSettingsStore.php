<?php

namespace App\Services;

use App\Organization;
use App\OrganizationSettings;
use App\Settings;
use Illuminate\Support\Str;

/**
 * Reads and writes the email configuration of one scope: the platform
 * (`settings` row 1) or a single organization (`organizations_settings`).
 * Both tables hold the same fields under different column names; this class
 * hides that so the controller and the Blade form treat them alike.
 *
 * Providers:
 *   - smtp   with a preset: gmail | resend | custom
 *   - resend (API) — only live once a test email has gone through
 */
class EmailSettingsStore
{
    /**
     * Connection values a preset fixes; the server enforces them on save.
     */
    public const PRESETS = [
        'gmail'  => ['host' => 'smtp.gmail.com', 'port' => 587, 'encryption' => 'tls'],
        'resend' => ['host' => 'smtp.resend.com', 'port' => 465, 'encryption' => 'ssl', 'username' => 'resend'],
        'custom' => [],
    ];

    public const VERIFY_DOMAIN_VIDEO = 'https://cdn.resend.com/posts/tutorial-video-generic-provider.mp4';

    private const SMTP_COLUMNS = [
        'platform' => [
            'host' => 'host', 'port' => 'Port', 'encryption' => 'encryption',
            'username' => 'Mail', 'password' => 'Mailpassword',
            'from_address' => 'mail_from_address', 'from_name' => 'mail_from_name',
        ],
        'organization' => [
            'host' => 'smtp_host', 'port' => 'smtp_port', 'encryption' => 'smtp_encryption',
            'username' => 'smtp_username', 'password' => 'smtp_password',
            'from_address' => 'smtp_from_address', 'from_name' => 'smtp_from_name',
        ],
    ];

    private const ROUTES = [
        'platform' => [
            'smtp_save'      => 'settings.system_mail_update',
            'smtp_test'      => 'settings.system_mail_test',
            'resend_connect' => 'settings.system_mail_resend_connect',
            'resend_domain'  => 'settings.system_mail_resend_domain',
            'resend_test'    => 'settings.system_mail_resend_test',
        ],
        'organization' => [
            'smtp_save'      => 'settings.organization.smtp_update',
            'smtp_test'      => 'settings.organization.smtp_test',
            'resend_connect' => 'settings.organization.resend_connect',
            'resend_domain'  => 'settings.organization.resend_domain',
            'resend_test'    => 'settings.organization.resend_test',
        ],
    ];

    private function __construct(
        private Settings|OrganizationSettings $settings,
        public readonly string $scope,
    ) {
    }

    public static function platform(): self
    {
        return new self(Settings::findOrFail(1), 'platform');
    }

    /**
     * Does not create a settings row until something is saved.
     */
    public static function forOrganization(Organization $organization): self
    {
        $settings = $organization->organizationSettings
            ?? new OrganizationSettings(['organization_id' => $organization->id]);

        return new self($settings, 'organization');
    }

    public function smtp(string $field): mixed
    {
        return $this->settings->{self::SMTP_COLUMNS[$this->scope][$field]};
    }

    public function preset(): string
    {
        $preset = $this->settings->smtp_preset;

        if (array_key_exists((string) $preset, self::PRESETS)) {
            return $preset;
        }

        return match ((string) $this->smtp('host')) {
            ''                => 'gmail',
            'smtp.gmail.com'  => 'gmail',
            'smtp.resend.com' => 'resend',
            default           => 'custom',
        };
    }

    public function resendApiKey(): ?string
    {
        return $this->settings->resend_api_key ?: null;
    }

    public function resendConnected(): bool
    {
        return TenantMailer::resendConnected($this->settings);
    }

    /**
     * The configuration emails go out with right now, mirroring TenantMailer:
     * a Resend setup that never passed its test email is ignored.
     *
     * @return string|null  resend | smtp_gmail | smtp_resend | smtp_custom
     */
    public function activeConfig(): ?string
    {
        if ($this->resendConnected()) {
            return 'resend';
        }

        return $this->smtp('host') ? 'smtp_' . $this->preset() : null;
    }

    /**
     * @param  array{preset: string, host: string, port: int, encryption: ?string,
     *               username: ?string, password: ?string, from_address: ?string, from_name: ?string}  $data
     */
    public function saveSmtp(array $data): void
    {
        foreach (self::SMTP_COLUMNS[$this->scope] as $field => $column) {
            $this->settings->{$column} = $data[$field] ?? null;
        }

        $this->settings->email_provider = 'smtp';
        $this->settings->smtp_preset = $data['preset'];
        // Resend keeps its key and domain for an easy switch back, but has to
        // pass a new test email before it goes live again.
        $this->settings->resend_connected_at = null;

        $this->clearFailure();
        $this->settings->save();
    }

    public function saveResend(string $apiKey, string $domain, string $fromAddress, ?string $fromName): void
    {
        $this->settings->email_provider = 'resend';
        $this->settings->resend_api_key = $apiKey;
        $this->settings->resend_domain = $domain;
        $this->settings->resend_from_address = $fromAddress;
        $this->settings->resend_from_name = $fromName;
        $this->settings->resend_connected_at = now();

        $this->clearFailure();
        $this->settings->save();
    }

    /**
     * Everything the email configuration form needs to render.
     */
    public function viewData(): array
    {
        $active = $this->activeConfig();
        $key = $this->resendApiKey();
        $from = (string) $this->settings->resend_from_address;

        return [
            'scope'     => $this->scope,
            'provider'  => $active ? Str::before($active, '_') : ($this->settings->email_provider === 'resend' ? 'resend' : 'smtp'),
            'preset'    => $this->preset(),
            'active'    => $active,
            'presets'   => self::PRESETS,
            'smtp'      => collect(self::SMTP_COLUMNS[$this->scope])->map(fn ($column) => $this->settings->{$column})->all(),
            'resend'    => [
                'key_hint'   => $key ? Str::substr($key, 0, 3) . '••••••' . Str::substr($key, -4) : null,
                'domain'     => $this->settings->resend_domain,
                'from_local' => Str::contains($from, '@') ? Str::before($from, '@') : 'no-reply',
                'from_name'  => $this->settings->resend_from_name,
                'connected'  => $this->resendConnected(),
            ],
            'failure'   => $this->scope === 'organization' && $this->settings->smtp_last_error ? [
                'error' => $this->settings->smtp_last_error,
                'at'    => $this->settings->smtp_failed_at,
            ] : null,
            'recipient' => auth()->user()?->email,
            'routes'    => array_map(fn ($name) => route($name), self::ROUTES[$this->scope]),
            'video_url' => self::VERIFY_DOMAIN_VIDEO,
        ];
    }

    private function clearFailure(): void
    {
        if ($this->scope === 'organization') {
            $this->settings->smtp_last_error = null;
            $this->settings->smtp_failed_at = null;
        }
    }
}
