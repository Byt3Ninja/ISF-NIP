<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Hash;

class UserManagementTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(), 'sanctum');
    }

    /** @test */
    public function authenticated_user_can_list_users()
    {
        User::factory()->count(10)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         '*' => [
                             'id',
                             'email',
                             'first_name',
                             'last_name',
                         ],
                     ],
                 ]);
    }

    /** @test */
    public function authenticated_user_can_view_single_user()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'id',
                         'email',
                     ],
                 ]);
    }

    /** @test */
    public function authenticated_user_can_create_user()
    {
        Role::factory()->create(['name' => 'Registered']);

        $response = $this->postJson('/api/users', [
            'first_name_en' => 'John',
            'last_name_en' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
            'role_id' => 1,
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data',
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    /** @test */
    public function authenticated_user_can_update_user()
    {
        $user = User::factory()->create();

        $response = $this->putJson("/api/users/{$user->id}", [
            'first_name_en' => 'Jane',
            'last_name_en' => 'Smith',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                 ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name_en' => 'Jane',
        ]);
    }

    /** @test */
    public function authenticated_user_can_deactivate_user()
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->patchJson("/api/users/{$user->id}/deactivate");

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                 ]);

        $this->assertEquals('inactive', $user->fresh()->status);
    }

    /** @test */
    public function user_cannot_deactivate_themselves()
    {
        $user = $this->actingAs(User::factory()->create(), 'sanctum')->getUser();

        $response = $this->patchJson("/api/users/{$user->id}/deactivate");

        $response->assertStatus(403);
    }

    /** @test */
    public function authenticated_user_can_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /** @test */
    public function authenticated_user_can_update_password()
    {
        $user = User::factory()->create();
        $oldPassword = $user->password;

        $response = $this->putJson("/api/users/{$user->id}/password", [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);

        $this->assertNotEquals($oldPassword, $user->fresh()->password);
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }
}

