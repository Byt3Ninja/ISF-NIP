<?php

namespace Tests\Feature\Security\Audit;

use App\Http\Controllers\serviceProviderController;
use App\Http\Middleware\LoginSecurityMiddleware;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\RateLimiter;
use PragmaRX\Google2FA\Google2FA;
use ReflectionMethod;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * Remediation P2 #14 / #16: session cookie flags, 2FA brute-force throttling,
 * a finite 2FA session lifetime, and long generated passwords.
 */
class SessionAuthHardeningTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        // Laravel runs SecurityActors::setUpSecurityActors() automatically, which stubs the 2FA
        // middleware; this test needs the real one, so drop that stub again.
        $this->app->forgetInstance(LoginSecurityMiddleware::class);
        $this->withoutMiddleware([EnsureEmailIsVerified::class]);
    }

    public function test_session_cookie_is_same_site_lax_and_secure_is_declared_once(): void
    {
        $this->assertSame('lax', config('session.same_site'));
        $this->assertSame(1, substr_count(file_get_contents(config_path('session.php')), "'secure' =>"));
    }

    public function test_two_factor_session_has_a_finite_lifetime(): void
    {
        $this->assertGreaterThan(0, (int) config('google2fa.lifetime'));
    }

    public function test_wrong_one_time_passwords_are_throttled_on_any_route(): void
    {
        $user = $this->actor('registered');
        $user->forceFill(['google2fa_secret' => (new Google2FA())->generateSecretKey(), 'two_factor_enabled' => 1])->save();
        RateLimiter::clear('2fa-otp:' . $user->id);

        for ($i = 0; $i < 6; $i++) {
            $status = $this->actingAs($user)->post('/dashboard/2fa/2faVerify', ['one_time_password' => '000000'])->status();
            $this->assertNotSame(429, $status, "attempt {$i} throttled too early");
        }

        // The OTP is checked by middleware on every dashboard route, so the limit must follow the user, not one URL.
        $this->actingAs($user)->get('/dashboard/messages?one_time_password=000000')->assertStatus(429);
        $this->actingAs($user)->post('/dashboard/2fa/2faVerify', ['one_time_password' => '000000'])->assertStatus(429);
    }

    public function test_generated_service_provider_passwords_are_long(): void
    {
        $method = new ReflectionMethod(serviceProviderController::class, 'generateCompliantPassword');
        $method->setAccessible(true);

        $this->assertGreaterThanOrEqual(16, strlen($method->invoke(app(serviceProviderController::class))));
    }
}
