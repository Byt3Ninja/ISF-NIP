<?php

namespace Tests\Feature\Importer;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ImporterRouteScaffoldTest extends TestCase
{
    use DatabaseTransactions;

    public function test_unauthenticated_get_to_importer_v2_is_redirected_or_401(): void
    {
        $resp = $this->getJson('/dashboard/importer/v2');
        $this->assertContains($resp->status(), [302, 401, 403, 419]);
    }

    public function test_authenticated_get_returns_placeholder_json(): void
    {
        $user = User::factory()->create();
        $user->allotRole('Admin');
        $resp = $this->actingAs($user)->getJson('/dashboard/importer/v2');
        $resp->assertStatus(200)
             ->assertJsonPath('data.message', 'importer_v2_placeholder');
    }

    public function test_placeholder_endpoints_return_501(): void
    {
        $user = User::factory()->create();
        $user->allotRole('Admin');
        $resp = $this->actingAs($user)
            ->postJson('/dashboard/importer/v2/sessions');
        $resp->assertStatus(501)
             ->assertJsonPath('errors.0.code', 'not_implemented');
    }

    public function test_api_route_exists_with_sanctum_guard(): void
    {
        // Without a token, expect 401 from Sanctum (or similar for another guard)
        $resp = $this->getJson('/api/v1/importer/schema');
        $this->assertContains($resp->status(), [401, 501]);
    }
}
