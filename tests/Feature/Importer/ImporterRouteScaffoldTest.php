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
        $resp = $this->getJson('/dashboard/importer');
        $this->assertContains($resp->status(), [302, 401, 403, 419]);
    }

    public function test_authenticated_get_returns_wizard_view(): void
    {
        $user = User::factory()->create();
        $user->allotRole('Admin');
        // PR 2: showWizard now renders the Blade wizard rather than a JSON placeholder.
        $resp = $this->actingAs($user)->get('/dashboard/importer');
        $resp->assertStatus(200);
        $resp->assertSee('importer-v2', false);
    }

    public function test_execute_requires_validated_state(): void
    {
        $user = User::factory()->create();
        $user->allotRole('Admin');
        $create = $this->actingAs($user)->postJson('/dashboard/importer/sessions');
        $uuid = $create->json('data.session_id');

        // A freshly-created session is in 'uploaded' state — not valid for execute.
        $org = \App\Organization::create(['name_en' => 'X', 'name_ar' => 'س', 'user_id' => $user->id]);
        $resp = $this->actingAs($user)
            ->postJson("/dashboard/importer/sessions/{$uuid}/execute", [
                'organization_id' => $org->id,
            ]);
        $resp->assertStatus(409)
             ->assertJsonPath('errors.0.code', 'invalid_state_transition');
    }

    public function test_api_route_exists_with_sanctum_guard(): void
    {
        // Without a token, expect 401 from Sanctum (or similar for another guard)
        $resp = $this->getJson('/api/v1/importer/schema');
        $this->assertContains($resp->status(), [401, 501]);
    }
}
