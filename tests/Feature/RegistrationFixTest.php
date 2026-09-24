<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\User;

class RegistrationFixTest extends TestCase
{
    // use RefreshDatabase; // Be careful with this on existing DB

    public function test_registration_validation_errors_are_present_in_session()
    {
        $response = $this->post('/register', [
            // Missing required fields
            'first_name' => '',
            'last_name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'role' => '999', // Invalid role
        ]);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'password', 'role']);
    }

    public function test_registration_with_valid_founder_data()
    {
        // Mock data for Founder (Role ID 3)
        $data = [
            'first_name' => 'Test',
            'last_name' => 'Founder',
            'email' => 'testfounder_' . time() . '@example.com',
            'phone' => '1234567890' . time(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => '3', // Founder
            'gender' => 'Male',
            'country_code' => '20',
        ];

        $response = $this->post('/register', $data);

        // If successful, it should redirect to /dashboard or similar
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }
}
