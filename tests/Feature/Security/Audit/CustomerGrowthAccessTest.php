<?php

namespace Tests\Feature\Security\Audit;

use App\StartupCustomerGrowth;
use App\User;
use Illuminate\Support\Facades\Route;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * N2: startupGrowth routes were unnamed (no permission check) and only founders were
 * held to their own startup; every other role could write any startup's records.
 */
class CustomerGrowthAccessTest extends TestCase
{
    use SecurityActors;

    private const PERMISSIONS = ['company.customer_growth_add', 'company.customer_growth_update', 'company.customer_growth_delete', 'company.customer_growth_export'];

    private int $companyId;
    private int $orgId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();

        $this->orgId = $this->organization();
        [$this->companyId] = $this->startup($this->program($this->orgId));
        foreach (['registered', 'organizationmanager', 'subadmin', 'admin'] as $role) {
            $this->grant($role, self::PERMISSIONS);
        }
    }

    private function addPayload(int $companyId): array
    {
        return [
            'add_growth_year' => (int) date('Y'), 'add_growth_month' => 1,
            'add_growth_total_customers' => 10, 'add_growth_new_customers' => 2,
            'add_growth_segment' => 'mixed', 'company_id' => $companyId,
        ];
    }

    private function records(): int
    {
        return StartupCustomerGrowth::where('company_id', $this->companyId)->count();
    }

    public function test_mentor_cannot_add_records_to_any_startup(): void
    {
        $this->actingAs($this->actor('mentor'))->post('/dashboard/startupGrowth/add', $this->addPayload($this->companyId));

        $this->assertSame(0, $this->records());
    }

    public function test_manager_of_another_organization_cannot_add_or_export(): void
    {
        $outsider = $this->manager($this->organization());

        $this->actingAs($outsider)->post('/dashboard/startupGrowth/add', $this->addPayload($this->companyId))->assertForbidden();
        $this->actingAs($outsider)->get('/dashboard/startupGrowth/export/' . $this->companyId)->assertForbidden();

        $this->assertSame(0, $this->records());
    }

    public function test_manager_of_another_organization_cannot_delete(): void
    {
        $record = StartupCustomerGrowth::forceCreate(['company_id' => $this->companyId, 'year' => (int) date('Y'), 'month' => 2, 'total_customers' => 5, 'new_customers' => 1, 'segment' => 'mixed']);

        $this->actingAs($this->manager($this->organization()))->delete('/dashboard/startupGrowth/delete/' . $record->id)->assertForbidden();

        $this->assertNotNull($record->fresh());
    }

    public function test_founder_and_program_manager_can_still_manage_their_startup(): void
    {
        $founder = User::where('company_id', $this->companyId)->firstOrFail();

        $this->actingAs($founder)->post('/dashboard/startupGrowth/add', $this->addPayload($this->companyId))->assertRedirect();
        $this->assertSame(1, $this->records());

        $this->actingAs($this->manager($this->orgId))->get('/dashboard/startupGrowth/export/' . $this->companyId)->assertOk();
    }

    public function test_routes_are_named(): void
    {
        foreach (self::PERMISSIONS as $name) {
            $this->assertTrue(Route::has($name), "route {$name} missing");
        }
    }
}
