<?php

namespace Tests\Feature\Correctness;

use App\Almuni;
use App\Employee;
use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * Correctness audit, company/profile cluster: C2 (deleteEmployee), C18 (alumni leader info),
 * C40 (leaving an impersonation whose original user is gone).
 */
class CompanyProfileCorrectnessTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
    }

    private function employee(int $companyId): Employee
    {
        return Employee::forceCreate([
            'company_id' => $companyId, 'full_name' => 'Audit Employee', 'job_title' => 'Engineer',
            'gender' => 'male', 'employment_type' => 'full-time', 'hiring_date' => now()->toDateString(),
        ]);
    }

    public function test_c2_founder_can_delete_an_employee_of_their_startup(): void
    {
        [$companyId, $founder] = $this->startup($this->program($this->organization()));
        $this->grant('registered', ['company.employees_delete']);
        $employee = $this->employee($companyId);

        $this->actingAs($founder)->deleteJson("/dashboard/employees/delete/{$companyId}", ['id' => $employee->id])
            ->assertOk()->assertJson(['success' => true]);

        $this->assertNull(Employee::find($employee->id));
    }

    public function test_c2_an_employee_of_another_startup_is_not_deleted(): void
    {
        $cycle = $this->program($this->organization());
        [$companyId, $founder] = $this->startup($cycle);
        [$otherCompanyId] = $this->startup($cycle);
        $this->grant('registered', ['company.employees_delete']);
        $foreign = $this->employee($otherCompanyId);

        $this->actingAs($founder)->deleteJson("/dashboard/employees/delete/{$companyId}", ['id' => $foreign->id]);

        $this->assertNotNull(Employee::find($foreign->id));
    }

    public function test_c18_alumni_snapshot_lists_the_startups_leaders(): void
    {
        $org = $this->organization();
        $cycle = $this->program($org, ['allow_non_company' => 1]);
        $finalStep = $this->step($cycle, ['stype' => 5]);
        [$companyId] = $this->startup($cycle, ['step' => $finalStep]);
        $member = fn (string $type, int $leader) => DB::table('team_members')->insert([
            'TeamMember' => $type, 'Name' => "{$type} {$leader}", 'Email' => uniqid($type) . '@example.invalid',
            'Company_id' => $companyId, 'isTeamLeader' => $leader, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $member('Founder', 0);
        $member('hackathon', 1);
        $member('hackathon', 0);
        $member('Shareholder', 0);
        $this->grant('organizationmanager', ['company.addCompanyAlumni']);

        $this->actingAs($this->manager($org))->postJson("/dashboard/AddCompanyalumni/{$companyId}", [], ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();

        $leaders = collect(Almuni::where('company_id', $companyId)->firstOrFail()->startup_leader_information)->pluck('Full_Name')->sort()->values()->all();
        $this->assertSame(['Founder 0', 'hackathon 1'], $leaders);
    }

    public function test_c40_leaving_an_impersonation_of_a_deleted_admin_logs_out_cleanly(): void
    {
        $impersonated = $this->actor('registered');

        $this->actingAs($impersonated)
            ->withSession(['impersonator_id' => 999999999])
            ->post('/dashboard/users/impersonate-stop')
            ->assertForbidden();

        $this->assertGuest();
        $this->assertFalse(session()->has('impersonator_id'));
    }
}
