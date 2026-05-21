<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\View;
use App\Company;

class StartupProgramSummaryTest extends TestCase
{
    /**
     * Test that the _startup_programs partial renders a premium empty state when no programs are present.
     */
    public function test_renders_empty_state_when_no_programs_present()
    {
        $company = new Company();
        $company->id = 9999;
        
        $programs = collect();
        $programsSummary = [
            'totalMentorshipHours' => '00:00',
            'totalTrainingHours'   => 0,
            'avgFinalScreening'    => 0,
            'avgAttendanceRate'    => 0,
        ];

        $html = View::make('admin.clients.startup.partials._startup_programs', [
            'programs' => $programs,
            'programsSummary' => $programsSummary,
            'company' => $company,
        ])->render();

        $this->assertStringContainsString('No Programs Joined Yet', $html);
        $this->assertStringContainsString('This startup is not currently participating in any programs.', $html);
        $this->assertStringNotContainsString('Startup Hackathon 2024', $html); // Static data should not be present
    }

    /**
     * Test that the _startup_programs partial renders program details and formats null scores as '—'.
     */
    public function test_renders_program_details_and_formats_null_scores()
    {
        $company = new Company();
        $company->id = 9999;

        $program = (object)[
            'id' => 123,
            'name' => 'Dynamic Active Program',
            'img_url' => null,
            'type' => 'general',
            'organization' => (object)['name' => 'Dynamic Org'],
            'cohort' => (object)['name' => 'Cohort X'],
            'start_date' => \Carbon\Carbon::parse('2026-01-01'),
            'completion_date' => \Carbon\Carbon::parse('2026-06-01'),
            'screening_score' => null,
            'final_screening_score' => null,
            'rank' => null,
            'status' => 'ongoing',
        ];

        $programs = collect([$program]);
        $programsSummary = [
            'totalMentorshipHours' => '05:30',
            'totalTrainingHours'   => 12,
            'avgFinalScreening'    => 0,
            'avgAttendanceRate'    => 85,
        ];

        $html = View::make('admin.clients.startup.partials._startup_programs', [
            'programs' => $programs,
            'programsSummary' => $programsSummary,
            'company' => $company,
        ])->render();

        $this->assertStringNotContainsString('No Programs Joined Yet', $html);
        $this->assertStringContainsString('Dynamic Active Program', $html);
        $this->assertStringContainsString('Dynamic Org', $html);
        $this->assertStringContainsString('Cohort X', $html);
        // Scores should be null and rendered as '—'
        $this->assertStringContainsString('—', $html);
        $this->assertStringNotContainsString('0 / 100', $html);
    }
}
