<?php

namespace Tests\Feature\Pipeline;

use App\Company;
use App\CompanyEnrollment;
use App\Cycle;
use App\Step;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * P1-04: the Programs tab of the idea profile used to show only the current program
 * and the ones the idea became alumni of. Programs it was rejected from or left
 * now appear too, from the enrollment history.
 */
class ProgramHistoryTest extends TestCase
{
    private function summary(): array
    {
        return ['totalMentorshipHours' => '00:00', 'totalTrainingHours' => 0, 'avgFinalScreening' => 0, 'avgAttendanceRate' => 0];
    }

    public function test_ended_history_lists_rejected_and_withdrawn_programs_only(): void
    {
        $rejectedFrom = Cycle::factory()->create(['title' => 'Rejected Program']);
        $left = Cycle::factory()->create(['title' => 'Left Program']);
        $finished = Cycle::factory()->create(['title' => 'Finished Program']);
        $current = Cycle::factory()->create(['title' => 'Current Program']);
        $screening = Step::factory()->create(['cycle_id' => $rejectedFrom->id, 'title' => 'Screening']);
        $company = Company::factory()->create();

        $company->update(['cycle' => $rejectedFrom->id, 'step' => $screening->id]);
        CompanyEnrollment::closeActive($company->id, $rejectedFrom->id, CompanyEnrollment::STATUS_REJECTED);
        $company->update(['cycle' => $left->id, 'step' => null]);
        $company->update(['cycle' => $finished->id]); // closes "Left Program" as withdrawn
        CompanyEnrollment::closeActive($company->id, $finished->id, CompanyEnrollment::STATUS_COMPLETED);
        $company->update(['cycle' => $current->id]);

        $history = $company->endedProgramHistory();

        $this->assertEqualsCanonicalizing(['Rejected Program', 'Left Program'], $history->pluck('name')->all());
        $rejected = $history->firstWhere('name', 'Rejected Program');
        $this->assertSame('rejected', $rejected->status);
        $this->assertSame('Screening', $rejected->last_step);
        $this->assertNotNull($rejected->completion_date);
        $this->assertSame('withdrawn', $history->firstWhere('name', 'Left Program')->status);
    }

    public function test_programs_tab_shows_a_rejected_program_with_the_phase_reached(): void
    {
        $program = (object) [
            'id' => 5, 'name' => 'Rejected Program', 'img_url' => null, 'type' => 'general_program',
            'organization' => (object) ['name' => 'Some Org'], 'cohort' => (object) ['name' => null],
            'start_date' => now()->subMonth(), 'completion_date' => now(),
            'screening_score' => null, 'final_screening_score' => null, 'rank' => null,
            'status' => 'rejected', 'program_type' => 'ended', 'last_step' => 'Screening',
        ];
        $ongoing = (object) (['name' => 'Current Program', 'status' => 'ongoing', 'program_type' => 'running', 'last_step' => null] + (array) $program);

        $html = View::make('admin.clients.startup.partials._startup_programs', [
            'programs' => [$program, $ongoing], 'programsSummary' => $this->summary(), 'company' => Company::factory()->make(['id' => 1]),
        ])->render();

        $this->assertStringContainsString('Rejected Program', $html);
        $this->assertStringContainsString(__('startup.rejected'), $html);
        $this->assertStringContainsString('Screening', $html);
        $this->assertMatchesRegularExpression('/>\s*1 ' . preg_quote(__('admin.Active'), '/') . '/', $html, 'only the ongoing program counts as active');
    }
}
