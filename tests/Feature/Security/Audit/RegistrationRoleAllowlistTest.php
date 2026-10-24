<?php

namespace Tests\Feature\Security\Audit;

use App\User;
use Tests\Support\FakesOutboundServices;
use Tests\TestCase;

/**
 * C1 / H5d: self-registration must only ever grant the Founder or Mentor role.
 */
class RegistrationRoleAllowlistTest extends TestCase
{
    use FakesOutboundServices;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeOutboundServices();
    }

    private function payload(string $role): array
    {
        $unique = uniqid('c1', false);

        return [
            'first_name' => 'Probe',
            'last_name' => 'User',
            'email' => "{$unique}@example.test",
            'phone' => '9' . random_int(100000000, 999999999),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => $role,
            'gender' => 'Male',
            'country_code' => '20',
        ];
    }

    public static function privilegedRoles(): array
    {
        return [['admin'], ['Admin'], ['subadmin'], ['Sub-Admin'], ['organizationmanager'], ['judger'], ['serviceProvider']];
    }

    /** @dataProvider privilegedRoles */
    public function test_web_register_rejects_roles_outside_the_allowlist(string $role): void
    {
        $data = $this->payload($role);

        $this->post('/register', $data)->assertSessionHasErrors('role');

        $this->assertFalse(User::where('email', $data['email'])->exists());
        $this->assertGuest();
    }

    public function test_web_register_still_creates_a_founder(): void
    {
        $data = $this->payload('Founder');

        $this->post('/register', $data)->assertSessionHasNoErrors()->assertRedirect();

        $user = User::where('email', $data['email'])->firstOrFail();
        $this->assertSame(['registered'], $user->roles()->pluck('default_for')->all());
    }

    public function test_web_register_still_creates_a_mentor(): void
    {
        $data = $this->payload('Mentor') + ['bio_en' => 'Bio', 'title_en' => 'Coach', 'Linkedin' => ''];

        $this->post('/register', $data)->assertSessionHasNoErrors()->assertRedirect();

        $user = User::where('email', $data['email'])->firstOrFail();
        $this->assertSame(['mentor'], $user->roles()->pluck('default_for')->all());
    }

    public function test_api_register_rejects_judger(): void
    {
        $data = $this->payload('Judger');

        // The API wraps validation errors in its own envelope, so assert on the body.
        $response = $this->postJson('/api/register', [
            'firstName' => 'Probe',
            'lastName' => 'User',
            'gender' => 'Male',
            'phone' => $data['phone'],
            'email' => $data['email'],
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Judger',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('"role"', $response->getContent());

        $this->assertFalse(User::where('email', $data['email'])->exists());
    }
}
