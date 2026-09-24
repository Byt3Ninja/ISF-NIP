<?php

namespace Tests\Feature\Web;

use Tests\TestCase;
use App\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{

    /** @test */
    public function user_can_view_login_page()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->get('/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}

