<?php

namespace Tests\Feature\Pipeline;

use App\Company;
use App\CompanyEnrollment;
use App\Cycle;
use App\ScreeningEvaluationResults;
use App\Step;
use Tests\TestCase;

/**
 * P1-02, screening and judging lists: who is "at a step" is read from the enrollment
 * history, so an idea active in two sub-programs is found at its step in each. The
 * cycle / step pointer can only name one of them.
 */
class ScreeningListsTest extends TestCase
{
    private Cycle $first;
    private Cycle $second;
    private Step $firstScreening;
    private Step $secondScreening;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->first = Cycle::factory()->create();
        $this->second = Cycle::factory()->create();
        $this->firstScreening = Step::factory()->create(['cycle_id' => $this->first->id, 'stype' => 2, 'arr' => 2]);
        $this->secondScreening = Step::factory()->create(['cycle_id' => $this->second->id, 'stype' => 2, 'arr' => 2]);

        // Active in both; the pointer names the second.
        $this->company = Company::factory()->create(['cycle' => $this->first->id, 'step' => $this->firstScreening->id]);
        Company::withoutEvents(fn () => $this->company->update(['cycle' => $this->second->id, 'step' => $this->secondScreening->id]));
        $enrollment = new CompanyEnrollment(['step_id' => $this->secondScreening->id, 'status' => CompanyEnrollment::STATUS_ACTIVE, 'joined_at' => now()]);
        $enrollment->company_id = $this->company->id;
        $enrollment->cycle_id = $this->second->id;
        $enrollment->save();
        $this->company->refresh();
    }

    public function test_in_memory_checks_match_the_scopes(): void
    {
        $this->assertTrue($this->company->isEnrolledIn($this->first->id));
        $this->assertTrue($this->company->isEnrolledIn($this->second->id));
        $this->assertTrue($this->company->isOnStep($this->firstScreening->id));
        $this->assertTrue($this->company->isOnStep($this->secondScreening->id));
        $this->assertTrue(Company::enrolledInAny([$this->first->id])->whereKey($this->company->id)->exists());
        $this->assertTrue(Company::enrolledOnAnyStep([$this->firstScreening->id])->whereKey($this->company->id)->exists());

        CompanyEnrollment::closeActive($this->company->id, $this->first->id, CompanyEnrollment::STATUS_REJECTED);
        $this->company->refresh();
        $this->assertFalse($this->company->isEnrolledIn($this->first->id));
        $this->assertFalse($this->company->isOnStep($this->firstScreening->id));
        $this->assertFalse(Company::enrolledOnAnyStep([$this->firstScreening->id])->whereKey($this->company->id)->exists());
    }

    public function test_grouping_by_step_lists_the_company_under_each_of_its_steps(): void
    {
        $other = Company::factory()->create(['cycle' => $this->first->id, 'step' => $this->firstScreening->id]);

        $grouped = Company::groupedByEnrolledStep([$this->firstScreening->id, $this->secondScreening->id]);

        $this->assertEqualsCanonicalizing([$this->company->id, $other->id], $grouped[$this->firstScreening->id]->pluck('id')->all());
        $this->assertSame([$this->company->id], $grouped[$this->secondScreening->id]->pluck('id')->all());
    }

    public function test_screening_results_find_the_company_at_its_step_in_each_program(): void
    {
        foreach ([$this->firstScreening, $this->secondScreening] as $step) {
            $metadata = ScreeningEvaluationResults::metadata($step->id);
            $row = collect($metadata['_companies'])->firstWhere('id', $this->company->id);

            $this->assertNotNull($row, "listed at step {$step->id}");
            $this->assertSame($step->id, (int) $row->company_step_id);
            $this->assertSame($step->cycle_id, (int) $row->company_cycle_id);
        }
    }

    public function test_staff_side_queries_find_the_company_in_both_programs(): void
    {
        // Program report counts, the mentors page and the startups list filter.
        $this->assertSame(1, Company::enrolledIn($this->first->id)->count());
        $this->assertSame(1, Company::enrolledInOnAStep($this->first->id)->count());
        $this->assertSame(1, Company::enrolledOnStep($this->firstScreening->id)->count());
        $this->assertSame(1, Company::enrolledOnStep($this->secondScreening->id)->count());

        foreach ([$this->first, $this->second] as $cycle) {
            $ids = Company::query()->enrolledIn($cycle->id)->pluck('id');
            $this->assertTrue($ids->contains($this->company->id));
        }

        // A company with no step in the cycle is not "on a step" there.
        $unplaced = Company::factory()->create(['cycle' => $this->first->id]);
        $this->assertFalse(Company::enrolledInOnAStep($this->first->id)->whereKey($unplaced->id)->exists());
        $this->assertTrue(Company::has('activeEnrollments')->whereKey($unplaced->id)->exists());
        $this->assertFalse(Company::doesntHave('activeEnrollments')->whereKey($unplaced->id)->exists());
    }
}
