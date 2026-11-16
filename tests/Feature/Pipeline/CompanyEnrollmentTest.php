<?php

namespace Tests\Feature\Pipeline;

use App\Almuni;
use App\Company;
use App\CompanyEnrollment;
use App\Cycle;
use App\Step;
use App\User;
use Tests\TestCase;

/**
 * P1-01: an enrollment row records each time an idea enters a sub-program.
 *
 * Until the screens read enrollments, `companies.cycle` / `companies.step` stay
 * the way the application moves an idea around, so every write to that pointer
 * has to leave the enrollment rows telling the same story.
 */
class CompanyEnrollmentTest extends TestCase
{
    /** @return array{0: Cycle, 1: \Illuminate\Support\Collection<int, Step>} */
    private function cycleWithSteps(int $steps = 2): array
    {
        $cycle = Cycle::factory()->create();

        return [$cycle, Step::factory()->count($steps)->create(['cycle_id' => $cycle->id])];
    }

    public function test_joining_a_cycle_opens_an_active_enrollment(): void
    {
        [$cycle, $steps] = $this->cycleWithSteps();
        $company = Company::factory()->create();
        $this->assertCount(0, $company->enrollments);

        $company->update(['cycle' => $cycle->id, 'step' => $steps[0]->id]);

        $enrollment = $company->enrollments()->sole();
        $this->assertSame($cycle->id, $enrollment->cycle_id);
        $this->assertSame($steps[0]->id, $enrollment->step_id);
        $this->assertSame(CompanyEnrollment::STATUS_ACTIVE, $enrollment->status);
        $this->assertNotNull($enrollment->joined_at);
        $this->assertNull($enrollment->ended_at);
    }

    public function test_a_company_created_already_in_a_cycle_gets_an_enrollment(): void
    {
        [$cycle, $steps] = $this->cycleWithSteps();

        $company = Company::factory()->create(['cycle' => $cycle->id, 'step' => $steps[0]->id]);

        $this->assertSame($steps[0]->id, $company->enrollments()->sole()->step_id);
    }

    public function test_joining_without_a_step_is_recorded_with_no_step(): void
    {
        // The CSV importer sets the cycle and leaves the step empty.
        [$cycle] = $this->cycleWithSteps();
        $company = Company::factory()->create();

        $company->update(['cycle' => $cycle->id]);

        $this->assertNull($company->enrollments()->sole()->step_id);
    }

    public function test_moving_to_another_step_updates_the_same_enrollment(): void
    {
        [$cycle, $steps] = $this->cycleWithSteps();
        $company = Company::factory()->create(['cycle' => $cycle->id, 'step' => $steps[0]->id]);

        $company->step = $steps[1]->id;
        $company->save();

        $enrollment = $company->enrollments()->sole();
        $this->assertSame($steps[1]->id, $enrollment->step_id);
        $this->assertSame(CompanyEnrollment::STATUS_ACTIVE, $enrollment->status);
    }

    public function test_an_edit_that_leaves_the_pointer_alone_changes_nothing(): void
    {
        [$cycle, $steps] = $this->cycleWithSteps();
        $company = Company::factory()->create(['cycle' => $cycle->id, 'step' => $steps[0]->id]);
        $before = $company->enrollments()->sole()->updated_at;

        $company->update(['name_en' => 'Renamed']);

        $this->assertCount(1, $company->enrollments()->get());
        $this->assertEquals($before, $company->enrollments()->sole()->updated_at);
    }

    public function test_clearing_the_pointer_closes_the_enrollment_as_withdrawn(): void
    {
        [$cycle, $steps] = $this->cycleWithSteps();
        $company = Company::factory()->create(['cycle' => $cycle->id, 'step' => $steps[0]->id]);

        $company->update(['cycle' => null, 'step' => null]);

        $enrollment = $company->enrollments()->sole();
        $this->assertSame(CompanyEnrollment::STATUS_WITHDRAWN, $enrollment->status);
        $this->assertNotNull($enrollment->ended_at);
        $this->assertSame($steps[0]->id, $enrollment->step_id, 'the last step reached is kept');
    }

    public function test_an_enrollment_closed_with_a_reason_keeps_it_when_the_pointer_clears(): void
    {
        // Alumni marking and screening rejection name the outcome first, then clear the pointer.
        [$cycle, $steps] = $this->cycleWithSteps();
        $company = Company::factory()->create(['cycle' => $cycle->id, 'step' => $steps[1]->id]);

        CompanyEnrollment::closeActive($company->id, $cycle->id, CompanyEnrollment::STATUS_COMPLETED);
        $company->update(['cycle' => null, 'step' => null]);

        $enrollment = $company->enrollments()->sole();
        $this->assertSame(CompanyEnrollment::STATUS_COMPLETED, $enrollment->status);
        $this->assertNotNull($enrollment->ended_at);
    }

    public function test_closing_does_nothing_when_the_company_is_not_in_that_cycle(): void
    {
        [$cycle] = $this->cycleWithSteps();
        $company = Company::factory()->create();

        CompanyEnrollment::closeActive($company->id, $cycle->id, CompanyEnrollment::STATUS_REJECTED);
        CompanyEnrollment::closeActive($company->id, null, CompanyEnrollment::STATUS_REJECTED);

        $this->assertCount(0, $company->enrollments);
    }

    public function test_pointing_at_a_different_cycle_closes_the_first_and_opens_the_second(): void
    {
        [$first, $firstSteps] = $this->cycleWithSteps();
        [$second, $secondSteps] = $this->cycleWithSteps();
        $company = Company::factory()->create(['cycle' => $first->id, 'step' => $firstSteps[0]->id]);

        $company->update(['cycle' => $second->id, 'step' => $secondSteps[0]->id]);

        $rows = $company->enrollments()->orderBy('id')->get();
        $this->assertCount(2, $rows);
        $this->assertSame([$first->id, CompanyEnrollment::STATUS_WITHDRAWN], [$rows[0]->cycle_id, $rows[0]->status]);
        $this->assertSame([$second->id, CompanyEnrollment::STATUS_ACTIVE], [$rows[1]->cycle_id, $rows[1]->status]);
    }

    public function test_rejoining_a_cycle_adds_a_row_and_keeps_the_earlier_one(): void
    {
        [$cycle, $steps] = $this->cycleWithSteps();
        $company = Company::factory()->create(['cycle' => $cycle->id, 'step' => $steps[1]->id]);
        CompanyEnrollment::closeActive($company->id, $cycle->id, CompanyEnrollment::STATUS_REJECTED);
        $company->update(['cycle' => null, 'step' => null]);

        $company->update(['cycle' => $cycle->id, 'step' => $steps[0]->id]);

        $rows = $company->enrollments()->orderBy('id')->get();
        $this->assertSame(
            [CompanyEnrollment::STATUS_REJECTED, CompanyEnrollment::STATUS_ACTIVE],
            $rows->pluck('status')->all()
        );
        $this->assertSame($steps[0]->id, $rows[1]->step_id);
    }

    public function test_a_bulk_step_move_carries_the_enrollments_with_it(): void
    {
        // `cycle:change-step` moves companies with one query, which fires no model events.
        [$cycle, $steps] = $this->cycleWithSteps();
        [$other, $otherSteps] = $this->cycleWithSteps();
        $moved = Company::factory()->count(2)->create(['cycle' => $cycle->id, 'step' => $steps[0]->id]);
        $elsewhere = Company::factory()->create(['cycle' => $other->id, 'step' => $otherSteps[0]->id]);

        Company::whereIn('id', $moved->pluck('id'))->update(['step' => $steps[1]->id]);
        CompanyEnrollment::moveToStep($moved->pluck('id')->push($elsewhere->id)->all(), $steps[1]->id);

        foreach ($moved as $company) {
            $this->assertSame($steps[1]->id, $company->enrollments()->sole()->step_id);
        }
        $this->assertSame($otherSteps[0]->id, $elsewhere->enrollments()->sole()->step_id, 'a company in another cycle is not touched');
    }

    public function test_companies_and_cycles_list_their_enrollments(): void
    {
        [$cycle, $steps] = $this->cycleWithSteps();
        $company = Company::factory()->create(['cycle' => $cycle->id, 'step' => $steps[0]->id]);

        $this->assertTrue($cycle->enrollments()->sole()->company->is($company));
        $this->assertTrue($company->activeEnrollments()->sole()->cycle->is($cycle));
        $this->assertTrue($company->activeEnrollments()->sole()->step->is($steps[0]));
    }

    public function test_backfill_builds_history_from_pointers_and_alumni_records(): void
    {
        [$current, $currentSteps] = $this->cycleWithSteps();
        [$finished] = $this->cycleWithSteps();
        $owner = User::factory()->create();

        // Rows as they exist before this feature: written with no events, so no enrollments.
        [$enrolled, $graduate] = Company::withoutEvents(fn () => [
            Company::factory()->create(['cycle' => $current->id, 'step' => $currentSteps[1]->id]),
            Company::factory()->create(),
        ]);
        $alumni = new Almuni();
        $alumni->forceFill(['user_id' => $owner->id, 'cycle_id' => $finished->id, 'company_id' => $graduate->id, 'alumni' => 1])->save();

        CompanyEnrollment::backfill();
        CompanyEnrollment::backfill(); // safe to run twice

        $active = $enrolled->enrollments()->sole();
        $this->assertSame([$current->id, $currentSteps[1]->id, CompanyEnrollment::STATUS_ACTIVE], [$active->cycle_id, $active->step_id, $active->status]);
        $this->assertNull($active->joined_at, 'the join date of older data was never stored');

        $completed = $graduate->enrollments()->sole();
        $this->assertSame([$finished->id, CompanyEnrollment::STATUS_COMPLETED], [$completed->cycle_id, $completed->status]);
        $this->assertNotNull($completed->ended_at);
    }
}
