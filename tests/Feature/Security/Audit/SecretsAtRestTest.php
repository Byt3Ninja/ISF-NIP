<?php

namespace Tests\Feature\Security\Audit;

use App\Mail\OrganizationCreatedMail;
use App\Mail\serviceProviderRegistrationMail;
use App\Notifications\WelcomeUserNotification;
use App\Organization;
use App\Services\OrganizationService;
use App\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * §5 (CWE-312): plaintext passwords must not travel through queued mail or API responses,
 * and 2FA / magic-login secrets must not be mass-assignable or serialized.
 */
class SecretsAtRestTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
    }

    public function test_organization_created_mail_sends_a_sign_in_link_not_the_password(): void
    {
        $password = 'Plain-Secret-' . uniqid();

        app(OrganizationService::class)->createOrganization([
            'firstName' => 'Org', 'lastName' => 'Owner', 'gender' => 'Male',
            'email' => 'org-owner-' . uniqid() . '@example.invalid',
            'phone' => '6' . random_int(100000000, 999999999),
            'password' => $password, 'name' => 'Audit Org ' . uniqid(), 'status' => 1,
        ], null, '20');

        Mail::assertQueued(OrganizationCreatedMail::class, function (OrganizationCreatedMail $mail) use ($password) {
            $html = $mail->render();

            return !str_contains(serialize($mail), $password)
                && !str_contains($html, $password)
                && str_contains($html, 'magic-login');
        });
    }

    public function test_service_provider_registration_mail_has_no_password(): void
    {
        $user = $this->actor('serviceProvider');
        $mail = new serviceProviderRegistrationMail('Provider Contact', $user->email, $user->generateMagicLoginUrl(), 'en');

        $html = $mail->render();

        $this->assertStringNotContainsString('Temporary Password', $html);
        $this->assertStringContainsString('magic-login', $html);
    }

    public function test_welcome_notification_does_not_carry_a_password(): void
    {
        $user = $this->actor('registered');

        $this->assertStringNotContainsString('password', serialize(new WelcomeUserNotification($user)));
    }

    public function test_api_register_company_does_not_return_a_password_and_sends_a_sign_in_link(): void
    {
        // The endpoint validates email:dns, so the domain must resolve (mail itself is faked).
        $email = 'audit.api.company.' . uniqid() . '@gmail.com';

        $response = $this->postJson('/api/register-company', [
            'personal' => ['first_name' => 'Api', 'last_name' => 'Founder', 'gender' => 'Male', 'email' => $email, 'phone' => '5' . random_int(100000000, 999999999)],
            'company' => ['business_or_product_name' => 'Api Co', 'investment_amount' => 0],
        ]);

        $response->assertOk();
        $this->assertArrayNotHasKey('password', $response->json('data.personal'));
        Notification::assertSentTo(User::where('email', $email)->firstOrFail(), WelcomeUserNotification::class);
    }

    public function test_two_factor_secret_and_magic_token_are_not_mass_assignable_or_serialized(): void
    {
        $user = $this->actor('registered');
        $user->forceFill(['google2fa_secret' => 'SECRET2FA', 'magic_login_token' => 'MAGIC'])->save();

        $user->fill(['google2fa_secret' => 'ATTACKER', 'magic_login_token' => 'ATTACKER']);
        $this->assertSame('SECRET2FA', $user->google2fa_secret);
        $this->assertSame('MAGIC', $user->magic_login_token);

        $json = $user->fresh()->toJson();
        $this->assertStringNotContainsString('SECRET2FA', $json);
        $this->assertStringNotContainsString('MAGIC', $json);
    }
}
