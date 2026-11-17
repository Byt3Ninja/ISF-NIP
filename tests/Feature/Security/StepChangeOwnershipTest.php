<?php

namespace Tests\Feature\Security;

use App\CompanyEnrollment;
use App\Jobs\SendChangeCycleNotificationJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\Support\FakesOutboundServices;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * Step changes from the screening and training result screens are limited to staff of
 * the program concerned and to companies enrolled in it.
 */
class StepChangeOwnershipTest extends TestCase
{
    use SecurityActors;
    use FakesOutboundServices;

    private int $organization;
    private int $cycle;
    private int $firstStep;
    private int $nextStep;
    private int $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
        Bus::fake([SendChangeCycleNotificationJob::class]);
        $this->grant('organizationmanager', ['screening.change_step', 'trainings.tl_update_all']);

        $this->organization = $this->organization();
        $this->cycle = $this->program($this->organization);
        $this->firstStep = $this->step($this->cycle);
        $this->nextStep = $this->step($this->cycle, ['arr' => 2]);
        [$this->company] = $this->startup($this->cycle, ['step' => $this->firstStep]);
    }

    private function enrollment(?int $company = null): CompanyEnrollment
    {
        return CompanyEnrollment::where('company_id', $company ?? $this->company)->latest('id')->firstOrFail();
    }

    private function assertNothingChanged(): void
    {
        $company = DB::table('companies')->where('id', $this->company)->first();
        $this->assertSame($this->cycle, (int) $company->cycle);
        $this->assertSame($this->firstStep, (int) $company->step);

        $enrollment = $this->enrollment();
        $this->assertSame(CompanyEnrollment::STATUS_ACTIVE, $enrollment->status);
        $this->assertSame($this->cycle, (int) $enrollment->cycle_id);
        $this->assertSame($this->firstStep, (int) $enrollment->step_id);

        Bus::assertNotDispatched(SendChangeCycleNotificationJob::class);
    }

    private function otherManager()
    {
        return $this->manager($this->organization());
    }

    // --- screening.change_step: move ---

    public function test_manager_of_another_organization_cannot_move_a_company(): void
    {
        $this->actingAs($this->otherManager())
            ->postJson(route('screening.change_step'), ['step' => $this->nextStep, 'company' => [$this->company]])
            ->assertForbidden();

        $this->assertNothingChanged();
    }

    public function test_manager_cannot_move_a_company_of_another_program_onto_their_own_step(): void
    {
        $otherOrganization = $this->organization();
        $ownStep = $this->step($this->program($otherOrganization));

        $this->actingAs($this->manager($otherOrganization))
            ->postJson(route('screening.change_step'), ['step' => $ownStep, 'company' => [$this->company]])
            ->assertForbidden();

        $this->assertNothingChanged();
    }

    public function test_a_selection_with_one_company_from_elsewhere_is_refused_as_a_whole(): void
    {
        [$foreign] = $this->startup($this->program($this->organization()));

        $this->actingAs($this->manager($this->organization))
            ->postJson(route('screening.change_step'), ['step' => $this->nextStep, 'company' => [$this->company, $foreign]])
            ->assertForbidden();

        $this->assertNothingChanged();
    }

    public function test_manager_of_the_organization_can_move_a_company(): void
    {
        $this->actingAs($this->manager($this->organization))
            ->postJson(route('screening.change_step'), ['step' => $this->nextStep, 'company' => [$this->company]])
            ->assertOk()
            ->assertJson(['state' => 'success']);

        $this->assertSame($this->nextStep, (int) DB::table('companies')->where('id', $this->company)->value('step'));
        $this->assertSame($this->nextStep, (int) $this->enrollment()->step_id);
    }

    public function test_admin_can_move_a_company(): void
    {
        $this->grant('admin', ['screening.change_step']);

        $this->actingAs($this->actor('admin'))
            ->postJson(route('screening.change_step'), ['step' => $this->nextStep, 'company' => [$this->company]])
            ->assertOk();

        $this->assertSame($this->nextStep, (int) $this->enrollment()->step_id);
    }

    // --- screening.change_step: disapproval ---

    public function test_manager_of_another_organization_cannot_disapprove_a_company(): void
    {
        $this->actingAs($this->otherManager())
            ->postJson(route('screening.change_step'), ['step' => 0, 'company' => [$this->company]])
            ->assertForbidden();

        $this->assertNothingChanged();
    }

    public function test_manager_cannot_disapprove_a_company_from_a_program_it_is_not_in(): void
    {
        $otherOrganization = $this->organization();
        $ownCycle = $this->program($otherOrganization);

        $this->actingAs($this->manager($otherOrganization))
            ->postJson(route('screening.change_step'), ['step' => 0, 'cycle' => $ownCycle, 'company' => [$this->company]])
            ->assertForbidden();

        $this->assertNothingChanged();
    }

    public function test_manager_of_another_organization_cannot_disapprove_by_naming_the_program(): void
    {
        $this->actingAs($this->otherManager())
            ->postJson(route('screening.change_step'), ['step' => 0, 'cycle' => $this->cycle, 'company' => [$this->company]])
            ->assertForbidden();

        $this->assertNothingChanged();
    }

    public function test_manager_of_the_organization_can_disapprove_without_naming_the_program(): void
    {
        $this->actingAs($this->manager($this->organization))
            ->postJson(route('screening.change_step'), ['step' => 0, 'company' => [$this->company]])
            ->assertOk()
            ->assertJson(['state' => 'success']);

        $this->assertSame(CompanyEnrollment::STATUS_REJECTED, $this->enrollment()->status);
        $this->assertNull(DB::table('companies')->where('id', $this->company)->value('cycle'));
    }

    public function test_manager_of_the_organization_can_disapprove_naming_the_program(): void
    {
        $this->actingAs($this->manager($this->organization))
            ->postJson(route('screening.change_step'), ['step' => 0, 'cycle' => $this->cycle, 'company' => [$this->company]])
            ->assertOk();

        $this->assertSame(CompanyEnrollment::STATUS_REJECTED, $this->enrollment()->status);
    }

    // --- trainings.tl_update_all ---

    public function test_manager_of_another_organization_cannot_move_a_company_from_training_results(): void
    {
        $this->actingAs($this->otherManager())
            ->postJson(route('trainings.tl_update_all'), ['checkget' => $this->nextStep, 'compid' => [$this->company]])
            ->assertForbidden();

        $this->assertNothingChanged();
    }

    public function test_manager_cannot_move_a_company_of_another_program_from_training_results(): void
    {
        $otherOrganization = $this->organization();
        $ownStep = $this->step($this->program($otherOrganization));

        $this->actingAs($this->manager($otherOrganization))
            ->postJson(route('trainings.tl_update_all'), ['checkget' => $ownStep, 'compid' => [$this->company]])
            ->assertForbidden();

        $this->assertNothingChanged();
    }

    public function test_manager_of_the_organization_can_move_a_company_from_training_results(): void
    {
        $this->actingAs($this->manager($this->organization))
            ->postJson(route('trainings.tl_update_all'), ['checkget' => $this->nextStep, 'compid' => [$this->company]])
            ->assertOk()
            ->assertJson(['state' => 'success']);

        $this->assertSame($this->nextStep, (int) DB::table('companies')->where('id', $this->company)->value('step'));
        $this->assertSame($this->nextStep, (int) $this->enrollment()->step_id);
    }
}
