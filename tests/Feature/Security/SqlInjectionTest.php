<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\User;
use App\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SqlInjectionTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function upcoming_sessions_rejects_sql_injection_in_user_id()
    {
        Role::factory()->create(['name' => 'Registered', 'default_for' => 'registered']);
        $user = User::factory()->create();
        $user->allotRole('registered');
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/upcoming-sessions?user_id=1%20OR%201=1');

        $this->assertNotEquals(500, $response->getStatusCode());
    }

    /** @test */
    public function upcoming_sessions_rejects_sql_injection_in_days()
    {
        Role::factory()->create(['name' => 'Registered', 'default_for' => 'registered']);
        $user = User::factory()->create();
        $user->allotRole('registered');
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/upcoming-sessions?days=14;DROP%20TABLE%20users;--');

        $this->assertNotEquals(500, $response->getStatusCode());
        $this->assertTrue(\Schema::hasTable('users'));
    }
}
