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

    public function test_authenticated_get_returns_wizard_view(): void
    {
        $user = User::factory()->create();
        $user->allotRole('Admin');
        // PR 2: showWizard now renders the Blade wizard rather than a JSON placeholder.
        $resp = $this->actingAs($user)->get('/dashboard/importer/v2');
        $resp->assertStatus(200);
        $resp->assertSee('importer-v2', false);
    }

    public function test_remaining_placeholder_endpoints_return_501(): void
    {
        $user = User::factory()->create();
        $user->allotRole('Admin');
        // autoMap is still a scaffold stub until PR 3.
        $uuid = 'nonexistent-uuid';
        $resp = $this->actingAs($user)
            ->postJson("/dashboard/importer/v2/sessions/{$uuid}/automap");
        // Will fail with session_not_found (404) before reaching the stub,
        // so instead pick an endpoint with no uuid lookup: organizations.
        $resp = $this->actingAs($user)->getJson('/dashboard/importer/v2/organizations');
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
