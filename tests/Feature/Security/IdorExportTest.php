<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\User;
use App\Role;
use App\Cycle;
use App\Step;
use App\Organization;
use App\Http\Middleware\LoginSecurityMiddleware;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class IdorExportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            LoginSecurityMiddleware::class,
            EnsureEmailIsVerified::class,
        ]);
    }

    private function createUserWithOrg(): array
    {
        Role::factory()->create(['name' => 'organizationManager', 'default_for' => 'organizationManager']);
        $org = Organization::factory()->create();
        $user = User::factory()->create();
        $user->organization_id = $org->id;
        $user->save();
        $user->allotRole('organizationManager');

        return [$user, $org];
    }

    /** @test */
    public function non_admin_cannot_export_other_orgs_cycle_data()
    {
        [$userA, $orgA] = $this->createUserWithOrg();

        $orgB = Organization::factory()->create();
        $cycleB = Cycle::factory()->create(['organization_id' => $orgB->id]);

        $this->actingAs($userA);

        $response = $this->get('/dashboard/export-cyclesComp?cycle=' . $cycleB->id);

        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function user_can_export_own_org_cycle_data()
    {
        [$user, $org] = $this->createUserWithOrg();

        $cycle = Cycle::factory()->create(['organization_id' => $org->id]);

        $this->actingAs($user);

        $response = $this->get('/dashboard/export-cyclesComp?cycle=' . $cycle->id);

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function non_admin_cannot_export_other_orgs_screening_results()
    {
        [$userA, $orgA] = $this->createUserWithOrg();

        $orgB = Organization::factory()->create();
        $cycleB = Cycle::factory()->create(['organization_id' => $orgB->id]);
        $stepB = Step::factory()->screening()->create(['cycle_id' => $cycleB->id]);

        $this->actingAs($userA);

        $response = $this->get(route('screening.export_results', $stepB->id));

        // 403 from IDOR check or 401 from permission middleware — both block unauthorized access
        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    /** @test */
    public function non_admin_cannot_export_other_orgs_final_screening_results()
    {
        [$userA, $orgA] = $this->createUserWithOrg();

        $orgB = Organization::factory()->create();
        $cycleB = Cycle::factory()->create(['organization_id' => $orgB->id]);
        $stepB = Step::factory()->finalEvaluation()->create(['cycle_id' => $cycleB->id]);

        $this->actingAs($userA);

        $response = $this->get(route('final.export_results', $stepB->id));

        // 403 from IDOR check or 401 from permission middleware — both block unauthorized access
        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    /** @test */
    public function admin_can_export_any_org_cycle_data()
    {
        Role::factory()->create(['name' => 'Admin', 'default_for' => 'admin']);
        $admin = User::factory()->create();
        $admin->allotRole('admin');

        $org = Organization::factory()->create();
        $cycle = Cycle::factory()->create(['organization_id' => $org->id]);

        $this->actingAs($admin);

        $response = $this->get('/dashboard/export-cyclesComp?cycle=' . $cycle->id);

        $this->assertNotEquals(403, $response->getStatusCode());
    }
}
