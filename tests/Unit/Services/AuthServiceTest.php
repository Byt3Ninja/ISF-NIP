<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AuthService;
use App\User;
use App\Settings;
use App\Role;
use App\UsersBio;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AuthServiceTest extends TestCase
{

    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    /** @test */
    public function it_can_authenticate_user_with_valid_credentials()
    {
        $password = 'password123';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
            'status' => 'active',
        ]);

        $result = $this->authService->attemptLogin('test@example.com', $password);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }

    /** @test */
    public function it_throws_exception_for_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('credential');

        $this->authService->attemptLogin('test@example.com', 'wrongpassword');
    }

    /** @test */
    public function it_throws_exception_for_inactive_user()
    {
        $password = 'password123';
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make($password),
            'status' => 'inactive',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('inactive');

        $this->authService->attemptLogin('test@example.com', $password);
    }

    /** @test */
    public function it_can_generate_token_for_user()
    {
        $user = User::factory()->create();

        $token = $this->authService->generateToken($user);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertTrue($user->tokens()->exists());
    }

    /** @test */
    public function it_can_register_a_new_user()
    {
        Settings::factory()->create(['id' => 1, 'VFEmail' => 0]);
        $role = Role::factory()->create(['name' => 'Registered']);

        $userData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'gender' => 'male',
            'phone' => '1234567890',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'Registered',
        ];

        $result = $this->authService->register($userData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertArrayHasKey('token', $result);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'first_name_en' => 'John',
            'last_name_en' => 'Doe',
        ]);
    }

    /** @test */
    public function it_can_register_a_mentor_with_bio_and_expertise()
    {
        Settings::factory()->create(['id' => 1, 'VFEmail' => 0]);
        $role = Role::factory()->create(['name' => 'Mentor']);

        $userData = [
            'firstName' => 'Jane',
            'lastName' => 'Mentor',
            'gender' => 'female',
            'phone' => '1234567890',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'role' => 'Mentor',
            'userbio' => 'Experienced mentor',
            'linkedin' => 'https://linkedin.com/in/jane',
            'expertise' => [1, 2, 3],
        ];

        $result = $this->authService->register($userData);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('users_bio', [
            'user_id' => $result['user']->id,
            'bio_en' => 'Experienced mentor',
        ]);
    }

    /** @test */
    public function it_verifies_email_when_settings_require_it()
    {
        Settings::factory()->create(['id' => 1, 'VFEmail' => 1]);
        $role = Role::factory()->create(['name' => 'Registered']);

        $userData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'gender' => 'male',
            'phone' => '1234567890',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'Registered',
        ];

        $result = $this->authService->register($userData);

        $this->assertNull($result['user']->email_verified_at);
    }

    /** @test */
    public function it_rolls_back_transaction_on_registration_failure()
    {
        Settings::factory()->create(['id' => 1, 'VFEmail' => 0]);
        // Don't create role to cause failure

        $userData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'gender' => 'male',
            'phone' => '1234567890',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'NonExistentRole',
        ];

        $this->expectException(\Exception::class);

        try {
            $this->authService->register($userData);
        } catch (\Exception $e) {
            $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
            throw $e;
        }
    }

    /** @test */
    public function it_can_logout_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $request = Request::create('/api/logout', 'POST');
        $request->headers->set('Authorization', 'Bearer ' . $token->plainTextToken);

        $result = $this->authService->logout($request, $user);

        $this->assertIsObject($result);
        $this->assertFalse($user->tokens()->where('id', $token->accessToken->id)->exists());
    }

    /** @test */
    public function it_returns_unauthorized_when_no_token_provided()
    {
        $user = User::factory()->create();
        $request = Request::create('/api/logout', 'POST');

        $result = $this->authService->logout($request, $user);

        $this->assertIsObject($result);
        // Check it's an unauthorized response
    }

    /** @test */
    public function it_returns_unauthorized_when_user_is_null()
    {
        $request = Request::create('/api/logout', 'POST');
        $request->headers->set('Authorization', 'Bearer invalid-token');

        $result = $this->authService->logout($request, null);

        $this->assertIsObject($result);
    }
}

