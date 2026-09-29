<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\User;
use App\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MassAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function non_admin_cannot_change_role_via_api_update()
    {
        $registeredRole = Role::factory()->create(['name' => 'Registered', 'default_for' => 'registered']);
        $adminRole = Role::factory()->create(['name' => 'Admin', 'default_for' => 'admin']);

        $user = User::factory()->create();
        $user->allotRole('registered');
        $token = $user->createToken('test')->plainTextToken;

        // Attempt privilege escalation by injecting role in update request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/users/' . $user->id, [
            'first_name' => 'Hacker',
            'role' => $adminRole->id,
        ]);

        // Verify the user still has their original role
        $user->refresh();
        $this->assertTrue($user->hasRole('registered'));
        $this->assertFalse($user->hasRole('admin'));
    }

    /** @test */
    public function organization_id_not_mass_assignable()
    {
        $user = new User();
        $user->fill(['organization_id' => 999]);

        $this->assertNull($user->organization_id);
    }

    /** @test */
    public function api_token_not_mass_assignable()
    {
        $user = new User();
        $user->fill(['api_token' => 'malicious_token']);

        $this->assertNull($user->api_token);
    }

    /** @test */
    public function organization_id_can_still_be_set_directly()
    {
        $org = \App\Organization::factory()->create();
        $user = User::factory()->create();
        $user->organization_id = $org->id;
        $user->save();

        $this->assertEquals($org->id, $user->fresh()->organization_id);
    }
}
