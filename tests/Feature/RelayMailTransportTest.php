<?php

namespace Tests\Feature;

use App\Http\Middleware\LoginSecurityMiddleware;
use App\Organization;
use App\OrganizationSettings;
use App\Role;
use App\Services\MailConfig;
use App\Services\TenantMailer;
use App\Settings;
use App\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Tests\TestCase;

/**
 * The "relay" SMTP preset: an internal relay that takes mail without a login
 * or encryption. It must say HELO and never try STARTTLS or AUTH, because
 * EsmtpTransport upgrades on its own and fails on the relay's certificate.
 */
class RelayMailTransportTest extends TestCase
{
    /** @var resource[] */
    private array $relays = [];

    /** @var string[] */
    private array $relayLogs = [];

    protected function tearDown(): void
    {
        TenantMailer::restore();

        foreach ($this->relays as $relay) {
            proc_terminate($relay);
            proc_close($relay);
        }

        foreach ($this->relayLogs as $log) {
            if (is_file($log)) {
                unlink($log);
            }
        }

        parent::tearDown();
    }

    /** @test */
    public function relay_config_builds_a_plain_smtp_transport()
    {
        MailConfig::set($this->relayConfig('10.0.0.25', 25));

        $transport = Mail::mailer('smtp')->getSymfonyTransport();

        $this->assertSame(SmtpTransport::class, get_class($transport));
        $this->assertSame('10.0.0.25', $transport->getStream()->getHost());
        $this->assertSame(25, $transport->getStream()->getPort());
        $this->assertFalse($transport->getStream()->isTLS());
    }

    /** @test */
    public function restore_switches_back_from_the_relay_transport()
    {
        $before = get_class(Mail::mailer('smtp')->getSymfonyTransport());

        TenantMailer::apply($this->relayConfig('10.0.0.25', 25));
        $this->assertSame(SmtpTransport::class, get_class(Mail::mailer('smtp')->getSymfonyTransport()));

        TenantMailer::restore();
        $this->assertSame($before, get_class(Mail::mailer('smtp')->getSymfonyTransport()));
    }

    /** @test */
    public function relay_says_helo_and_never_starttls_or_auth()
    {
        $port = $this->startFakeRelay();

        TenantMailer::apply($this->relayConfig('127.0.0.1', $port));
        Mail::raw('Relay test', fn ($message) => $message->to('founder@example.test')->subject('Relay test'));
        TenantMailer::restore();

        $commands = $this->relayCommands();

        $this->assertMatchesRegularExpression('/^HELO /m', $commands);
        $this->assertStringContainsString('MAIL FROM:<startups@example.test>', $commands);
        $this->assertStringContainsString('RCPT TO:<founder@example.test>', $commands);
        $this->assertMatchesRegularExpression('/^DATA\r?$/m', $commands);
        $this->assertDoesNotMatchRegularExpression('/^(EHLO|STARTTLS|AUTH)/m', $commands);
    }

    /** @test */
    public function plain_smtp_config_still_upgrades_with_starttls()
    {
        // The original failure: encryption = null does not stop EsmtpTransport.
        $port = $this->startFakeRelay();

        TenantMailer::apply(['transport' => 'smtp'] + $this->relayConfig('127.0.0.1', $port));

        try {
            Mail::raw('Relay test', fn ($message) => $message->to('founder@example.test')->subject('Relay test'));
            $this->fail('EsmtpTransport was expected to fail on STARTTLS.');
        } catch (TransportException $e) {
            $this->assertStringContainsString('STARTTLS', $this->relayCommands());
        }
    }

    /** @test */
    public function failing_organization_without_a_platform_setup_retries_on_the_env_transport()
    {
        $orgPort = $this->startFakeRelay();
        $envPort = $this->startFakeRelay();

        // What boot leaves in place when the platform has no SMTP host: config/mail.php (.env).
        Settings::query()->update(['host' => null, 'email_provider' => 'smtp', 'resend_connected_at' => null]);
        MailConfig::set($this->relayConfig('127.0.0.1', $envPort));

        // Organization setup that fails at send time (EsmtpTransport tries STARTTLS on the fake relay).
        $organization = Organization::factory()->create();
        OrganizationSettings::create([
            'organization_id'   => $organization->id,
            'email_provider'    => 'smtp',
            'smtp_preset'       => 'custom',
            'smtp_host'         => '127.0.0.1',
            'smtp_port'         => $orgPort,
            'smtp_from_address' => 'org@example.test',
        ]);

        TenantMailer::sendWithFallback($organization->fresh(), fn () => Mail::raw(
            'Fallback test',
            fn ($message) => $message->to('founder@example.test')->subject('Fallback test')
        ));

        $this->assertStringContainsString('STARTTLS', $this->relayCommands(0));
        $this->assertStringContainsString('RCPT TO:<founder@example.test>', $this->relayCommands(1));
        $this->assertNotNull($organization->organizationSettings()->first()->smtp_failed_at);
    }

    /** @test */
    public function organization_and_platform_relay_resolve_to_the_relay_transport()
    {
        $organization = Organization::factory()->create();
        OrganizationSettings::create([
            'organization_id'   => $organization->id,
            'email_provider'    => 'smtp',
            'smtp_preset'       => 'relay',
            'smtp_host'         => '10.0.0.25',
            'smtp_port'         => 25,
            'smtp_from_address' => 'startups@example.test',
        ]);

        $config = TenantMailer::configForOrganization($organization->fresh());
        $this->assertSame('relay', $config['transport']);
        $this->assertNull($config['username']);
        $this->assertSame('startups@example.test', $config['from_address']);

        $platform = (new Settings())->forceFill([
            'email_provider'    => 'smtp',
            'smtp_preset'       => 'relay',
            'host'              => '10.0.0.25',
            'Port'              => 25,
            'mail_from_address' => 'startups@example.test',
        ]);

        $config = TenantMailer::globalConfig($platform);
        $this->assertSame('relay', $config['transport']);
        $this->assertSame('startups@example.test', $config['from_address']);

        // Every other preset keeps the regular SMTP transport.
        $this->assertSame('smtp', TenantMailer::globalConfig($platform->forceFill(['smtp_preset' => 'custom']))['transport']);
    }

    /** @test */
    public function organization_can_save_a_relay_without_credentials()
    {
        [$user, $organization] = $this->organizationManager();

        $this->actingAs($user)->putJson(route('settings.organization.smtp_update'), [
            'smtp_preset'  => 'relay',
            'host'         => '10.0.0.25',
            'port'         => '25',
            'from_address' => 'startups@example.test',
            'from_name'    => 'TIEC',
            // Posted values a relay must ignore.
            'username'     => 'leftover@example.test',
            'password'     => 'secret',
            'encryption'   => 'tls',
        ])->assertOk()->assertJson(['active' => 'smtp_relay']);

        $settings = $organization->organizationSettings()->first();
        $this->assertSame('relay', $settings->smtp_preset);
        $this->assertSame('10.0.0.25', $settings->smtp_host);
        $this->assertSame('startups@example.test', $settings->smtp_from_address);
        $this->assertNull($settings->smtp_username);
        $this->assertNull($settings->smtp_password);
        $this->assertNull($settings->smtp_encryption);
    }

    /** @test */
    public function custom_server_still_requires_credentials()
    {
        [$user] = $this->organizationManager();

        $this->actingAs($user)->putJson(route('settings.organization.smtp_update'), [
            'smtp_preset'  => 'custom',
            'host'         => '10.0.0.25',
            'port'         => '25',
            'from_address' => 'startups@example.test',
        ])->assertStatus(422)->assertJsonValidationErrors(['username', 'password']);
    }

    private function relayConfig(string $host, int $port): array
    {
        return [
            'transport'    => 'relay',
            'host'         => $host,
            'port'         => $port,
            'encryption'   => null,
            'username'     => null,
            'password'     => null,
            'timeout'      => 5,
            'from_address' => 'startups@example.test',
            'from_name'    => 'TIEC',
            'source'       => 'test',
        ];
    }

    /** Start tests/Support/fake-smtp-relay.php and return its port. */
    private function startFakeRelay(): int
    {
        $this->relayLogs[] = $log = tempnam(sys_get_temp_dir(), 'relay');

        $this->relays[] = proc_open(
            [PHP_BINARY, base_path('tests/Support/fake-smtp-relay.php'), $log],
            [1 => ['pipe', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes
        );

        // Skip PHP startup warnings printed before the port.
        $port = 0;
        while (!$port && ($line = fgets($pipes[1])) !== false) {
            $port = ctype_digit(trim($line)) ? (int) trim($line) : 0;
        }
        $this->assertGreaterThan(0, $port, 'The fake relay did not start.');

        return $port;
    }

    /** Commands the n-th started fake relay received. */
    private function relayCommands(int $relay = 0): string
    {
        return (string) file_get_contents($this->relayLogs[$relay]);
    }

    private function organizationManager(): array
    {
        $this->withoutMiddleware([LoginSecurityMiddleware::class, EnsureEmailIsVerified::class]);

        Role::factory()->create(['name' => 'organizationManager', 'default_for' => 'organizationManager']);
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $user->allotRole('organizationManager');

        $permissionId = DB::table('permissions')->where('name', 'settings.organization.smtp_update')->value('id')
            ?? DB::table('permissions')->insertGetId(['name' => 'settings.organization.smtp_update', 'guard_name' => 'web', 'is_core' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $user->roles->first()->_permissions()->syncWithoutDetaching([$permissionId]);

        return [$user->fresh(), $organization];
    }
}
