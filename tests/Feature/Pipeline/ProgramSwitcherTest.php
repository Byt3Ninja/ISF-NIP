<?php

namespace Tests\Feature\Pipeline;

use App\Company;
use App\CompanyEnrollment;
use App\Cycle;
use App\Step;
use Tests\Support\FakesOutboundServices;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * P1-02, founder side: a founder whose idea is active in several sub-programs works
 * in one at a time. Founder screens read companies.cycle / step / approved, so
 * switching points those at the chosen enrollment without ending the others.
 */
class ProgramSwitcherTest extends TestCase
{
    use SecurityActors;
    use FakesOutboundServices;

    private Cycle $first;
    private Cycle $second;
    private Step $firstStep;
    private Step $secondStep;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeOutboundServices();

        $this->first = Cycle::factory()->create(['title' => 'First Program']);
        $this->second = Cycle::factory()->create(['title' => 'Second Program']);
        $this->firstStep = Step::factory()->create(['cycle_id' => $this->first->id, 'stype' => 1]);
        $this->secondStep = Step::factory()->create(['cycle_id' => $this->second->id, 'stype' => 1]);

        // Active in both. In the first it has been approved; in the second not yet.
        $this->company = Company::factory()->create(['cycle' => $this->first->id, 'step' => $this->firstStep->id, 'approved' => 1]);
        $second = new CompanyEnrollment(['step_id' => $this->secondStep->id, 'status' => CompanyEnrollment::STATUS_ACTIVE, 'joined_at' => now()]);
        $second->company_id = $this->company->id;
        $second->cycle_id = $this->second->id;
        $second->save();
    }

    private function enrollmentIn(Cycle $cycle): CompanyEnrollment
    {
        return CompanyEnrollment::where('company_id', $this->company->id)->where('cycle_id', $cycle->id)->sole();
    }

    public function test_the_approved_flag_is_kept_per_enrollment(): void
    {
        $this->assertSame(1, (int) $this->enrollmentIn($this->first)->approved);
        $this->assertNull($this->enrollmentIn($this->second)->approved);

        $this->company->update(['approved' => null]);

        $this->assertNull($this->enrollmentIn($this->first)->approved);
    }

    public function test_switching_points_the_company_at_the_other_program_and_ends_nothing(): void
    {
        $this->company->switchToEnrollment($this->enrollmentIn($this->second));

        $company = $this->company->fresh();
        $this->assertSame([$this->second->id, $this->secondStep->id, null], [$company->cycle, $company->step, $company->approved]);
        $this->assertSame(CompanyEnrollment::STATUS_ACTIVE, $this->enrollmentIn($this->first)->status);
        $this->assertSame(1, (int) $this->enrollmentIn($this->first)->approved, 'the first program keeps its own approval');

        $company->switchToEnrollment($this->enrollmentIn($this->first));
        $this->assertSame([$this->first->id, 1], [$company->fresh()->cycle, (int) $company->fresh()->approved]);
    }

    public function test_switching_refuses_an_enrollment_that_has_ended_or_belongs_to_another_company(): void
    {
        $other = Company::factory()->create(['cycle' => $this->second->id]);
        $foreign = CompanyEnrollment::where('company_id', $other->id)->sole();
        CompanyEnrollment::closeActive($this->company->id, $this->second->id, CompanyEnrollment::STATUS_REJECTED);

        foreach ([$foreign, $this->enrollmentIn($this->second)] as $enrollment) {
            try {
                $this->company->switchToEnrollment($enrollment);
                $this->fail('expected a refusal');
            } catch (\InvalidArgumentException $e) {
                $this->assertSame($this->first->id, $this->company->fresh()->cycle);
            }
        }
    }

    public function test_leaving_the_selected_program_falls_back_to_another_active_one(): void
    {
        // Rejection in the first program: its enrollment is closed, then the pointer is cleared.
        CompanyEnrollment::closeActive($this->company->id, $this->first->id, CompanyEnrollment::STATUS_REJECTED);
        $this->company->update(['cycle' => null, 'step' => null, 'approved' => null]);

        $company = $this->company->fresh();
        $this->assertSame([$this->second->id, $this->secondStep->id], [$company->cycle, $company->step]);
        $this->assertSame(CompanyEnrollment::STATUS_ACTIVE, $this->enrollmentIn($this->second)->status);
    }

    public function test_a_founder_switches_from_the_sidebar_and_only_within_their_own_idea(): void
    {
        $founder = $this->actor('registered', ['company_id' => $this->company->id, 'HaveCompany' => 1]);
        $outsider = $this->actor('registered', ['company_id' => Company::factory()->create()->id, 'HaveCompany' => 1]);
        $target = $this->enrollmentIn($this->second);

        $this->actingAs($outsider)->post(route('company.switch_program', $target->id))->assertNotFound();
        $this->assertSame($this->first->id, $this->company->fresh()->cycle);

        $this->actingAs($founder)->post(route('company.switch_program', $target->id))->assertRedirect();
        $this->assertSame($this->second->id, $this->company->fresh()->cycle);
    }

    public function test_the_sidebar_offers_the_switcher_only_with_more_than_one_program(): void
    {
        $founder = $this->actor('registered', ['company_id' => $this->company->id, 'HaveCompany' => 1]);
        $html = view('partials.program-switcher', ['company' => $this->company->fresh()])->render();
        $this->assertStringContainsString('First Program', $html);
        $this->assertStringContainsString('Second Program', $html);
        $this->assertStringContainsString(route('company.switch_program', $this->enrollmentIn($this->second)->id), $html);

        CompanyEnrollment::closeActive($this->company->id, $this->second->id, CompanyEnrollment::STATUS_WITHDRAWN);
        $this->assertSame('', trim(view('partials.program-switcher', ['company' => $this->company->fresh()])->render()));
    }
}
