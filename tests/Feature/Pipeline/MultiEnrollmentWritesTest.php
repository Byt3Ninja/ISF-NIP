<?php

namespace Tests\Feature\Pipeline;

use App\Company;
use App\CompanyEnrollment;
use App\Cycle;
use App\Step;
use Illuminate\Support\Facades\DB;
use Tests\Support\FakesOutboundServices;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * P1-03, write side: with an idea active in two sub-programs, joining, moving and
 * rejecting act on the sub-program concerned and leave the other one alone,
 * whichever of the two the founder currently has selected.
 */
class MultiEnrollmentWritesTest extends TestCase
{
    use SecurityActors;
    use FakesOutboundServices;

    private Cycle $first;
    private Cycle $second;
    /** @var \Illuminate\Support\Collection<int, Step> */
    private $firstSteps;
    /** @var \Illuminate\Support\Collection<int, Step> */
    private $secondSteps;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeOutboundServices();

        $organization = $this->organization();
        $this->first = Cycle::factory()->create(['organization_id' => $organization]);
        $this->second = Cycle::factory()->create(['organization_id' => $organization]);
        $this->firstSteps = Step::factory()->count(2)->create(['cycle_id' => $this->first->id]);
        $this->secondSteps = Step::factory()->count(2)->create(['cycle_id' => $this->second->id]);

        $this->company = Company::factory()->create(['cycle' => $this->first->id, 'step' => $this->firstSteps[0]->id]);
        $this->actor('registered', ['company_id' => $this->company->id, 'HaveCompany' => 1]);
    }

    private function enrollment(Cycle $cycle): CompanyEnrollment
    {
        return CompanyEnrollment::where('company_id', $this->company->id)->where('cycle_id', $cycle->id)->latest('id')->firstOrFail();
    }

    /** Active in both, with the founder's selection on the second. */
    private function joinSecond(): void
    {
        $this->company->enrollIn($this->second->id, $this->secondSteps[0]->id);
        $this->company->refresh();
    }

    public function test_joining_a_second_cycle_keeps_the_first_active_and_selects_the_new_one(): void
    {
        $this->joinSecond();

        $this->assertSame([$this->second->id, $this->secondSteps[0]->id], [$this->company->cycle, $this->company->step]);
        $this->assertSame(CompanyEnrollment::STATUS_ACTIVE, $this->enrollment($this->first)->status);
        $this->assertSame(CompanyEnrollment::STATUS_ACTIVE, $this->enrollment($this->second)->status);
        $this->assertSame(2, $this->company->activeEnrollments()->count());
    }

    public function test_joining_the_cycle_already_selected_or_the_first_cycle_behaves_as_before(): void
    {
        $fresh = Company::factory()->create();
        $fresh->enrollIn($this->first->id, $this->firstSteps[0]->id);
        $this->assertSame(1, $fresh->enrollments()->count());

        $fresh->enrollIn($this->first->id, $this->firstSteps[1]->id);
        $this->assertSame($this->firstSteps[1]->id, $fresh->enrollments()->sole()->step_id);
    }

    public function test_joining_again_a_cycle_it_is_active_in_reuses_that_enrollment(): void
    {
        $this->joinSecond();

        $this->company->enrollIn($this->first->id, $this->firstSteps[1]->id);

        $this->assertSame(2, $this->company->enrollments()->count());
        $this->assertSame([$this->first->id, $this->firstSteps[1]->id], [$this->company->fresh()->cycle, $this->company->fresh()->step]);
    }

    public function test_a_staff_move_in_the_cycle_not_selected_leaves_the_founders_selection_alone(): void
    {
        $this->joinSecond();

        $moved = $this->company->moveToStepIn($this->firstSteps[1]->id, ['approved' => 1, 'approved_by' => null]);

        $this->assertTrue($moved);
        $this->assertSame([$this->second->id, $this->secondSteps[0]->id], [$this->company->fresh()->cycle, $this->company->fresh()->step]);
        $this->assertSame([$this->firstSteps[1]->id, 1], [$this->enrollment($this->first)->step_id, $this->enrollment($this->first)->approved]);
        $this->assertNull($this->enrollment($this->second)->approved);
    }

    public function test_a_staff_move_in_the_selected_cycle_moves_the_pointer_too(): void
    {
        $this->joinSecond();

        $this->company->moveToStepIn($this->secondSteps[1]->id, ['approved' => 1, 'approved_by' => null]);

        $this->assertSame($this->secondSteps[1]->id, $this->company->fresh()->step);
        $this->assertSame($this->secondSteps[1]->id, $this->enrollment($this->second)->step_id);
        $this->assertSame($this->firstSteps[0]->id, $this->enrollment($this->first)->step_id);
    }

    public function test_a_move_to_a_cycle_the_company_is_not_in_is_refused_when_it_is_busy_elsewhere(): void
    {
        $this->assertFalse($this->company->moveToStepIn($this->secondSteps[0]->id));
        $this->assertSame($this->firstSteps[0]->id, $this->company->fresh()->step);
        $this->assertSame($this->firstSteps[0]->id, $this->enrollment($this->first)->step_id);
    }

    public function test_leaving_the_cycle_not_selected_keeps_the_selection(): void
    {
        $this->joinSecond();

        $this->company->leaveCycle($this->first->id, CompanyEnrollment::STATUS_REJECTED);

        $this->assertSame(CompanyEnrollment::STATUS_REJECTED, $this->enrollment($this->first)->status);
        $this->assertSame($this->second->id, $this->company->fresh()->cycle);
        $this->assertSame(CompanyEnrollment::STATUS_ACTIVE, $this->enrollment($this->second)->status);
    }

    public function test_leaving_the_selected_cycle_selects_the_one_that_is_left(): void
    {
        $this->joinSecond();

        $this->company->leaveCycle($this->second->id, CompanyEnrollment::STATUS_REJECTED);

        $this->assertSame(CompanyEnrollment::STATUS_REJECTED, $this->enrollment($this->second)->status);
        $this->assertSame([$this->first->id, $this->firstSteps[0]->id], [$this->company->fresh()->cycle, $this->company->fresh()->step]);
    }

    public function test_the_screening_screen_moves_and_rejects_in_its_own_cycle(): void
    {
        $this->joinSecond();
        $admin = $this->actor('admin');

        // Move within the first cycle while the founder works in the second.
        $this->actingAs($admin)
            ->post(route('screening.change_step'), ['company' => $this->company->id, 'step' => (string) $this->firstSteps[1]->id])
            ->assertOk();
        $this->assertSame($this->firstSteps[1]->id, $this->enrollment($this->first)->step_id);
        $this->assertSame($this->second->id, $this->company->fresh()->cycle);

        // Disapprove from the first cycle's screen: the page names its cycle.
        $this->actingAs($admin)
            ->post(route('screening.change_step'), ['company' => $this->company->id, 'step' => '0', 'cycle' => $this->first->id])
            ->assertOk();
        $this->assertSame(CompanyEnrollment::STATUS_REJECTED, $this->enrollment($this->first)->status);
        $this->assertSame(CompanyEnrollment::STATUS_ACTIVE, $this->enrollment($this->second)->status);
        $this->assertSame($this->second->id, $this->company->fresh()->cycle);
    }

    public function test_the_bulk_move_screen_acts_on_the_steps_own_cycle(): void
    {
        $this->joinSecond();
        $this->grant('admin', ['cycles.users_manage', 'cycles.companies_change_step']);

        $this->actingAs($this->actor('admin'))
            ->post(route('cycles.companies_change_step'), ['checkget' => $this->firstSteps[1]->id, 'compid' => [$this->company->id]])
            ->assertOk();

        $this->assertSame($this->firstSteps[1]->id, $this->enrollment($this->first)->step_id);
        $this->assertSame([$this->second->id, $this->secondSteps[0]->id], [$this->company->fresh()->cycle, $this->company->fresh()->step]);
    }

    public function test_the_scheduled_step_change_moves_each_enrollment_in_its_own_cycle(): void
    {
        $this->joinSecond();
        // The first cycle is time-driven: its first step ended, its second is open now.
        DB::table('cycles')->where('id', $this->first->id)->update([
            'step_type' => 'time', 'start' => now()->subDays(20)->toDateString(), 'end' => now()->addDays(20)->toDateString(),
        ]);
        DB::table('steps')->where('id', $this->firstSteps[0]->id)->update(['from' => now()->subDays(20)->toDateString(), 'to' => now()->subDays(5)->toDateString()]);
        DB::table('steps')->where('id', $this->firstSteps[1]->id)->update(['from' => now()->subDays(4)->toDateString(), 'to' => now()->addDays(20)->toDateString()]);

        $this->artisan('cycle:change-step')->assertSuccessful();

        $this->assertSame($this->firstSteps[1]->id, $this->enrollment($this->first)->step_id);
        $this->assertSame([$this->second->id, $this->secondSteps[0]->id], [$this->company->fresh()->cycle, $this->company->fresh()->step]);
        $this->assertSame($this->secondSteps[0]->id, $this->enrollment($this->second)->step_id);
    }
}
