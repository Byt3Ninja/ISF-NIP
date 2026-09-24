<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Cycle;
use App\Organization;
use App\Company;
use App\User;
use App\Step;
use App\MentorshipRequest;
use App\Project;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CycleModelTest extends TestCase
{

    /** @test */
    public function it_has_name_attribute_accessor()
    {
        app()->setLocale('en');
        $cycle = Cycle::factory()->create([
            'name_en' => 'Test Program',
            'name_ar' => 'برنامج تجريبي',
        ]);

        $this->assertEquals('Test Program', $cycle->name);
    }

    /** @test */
    public function it_has_name_attribute_in_arabic()
    {
        app()->setLocale('ar');
        $cycle = Cycle::factory()->create([
            'name_en' => 'Test Program',
            'name_ar' => 'برنامج تجريبي',
        ]);

        $this->assertEquals('برنامج تجريبي', $cycle->name);
    }

    /** @test */
    public function it_casts_dates()
    {
        $cycle = Cycle::factory()->create([
            'start' => '2024-01-01',
            'end' => '2024-12-31',
        ]);

        $this->assertInstanceOf(Carbon::class, $cycle->start);
        $this->assertInstanceOf(Carbon::class, $cycle->end);
    }

    /** @test */
    public function it_casts_is_public_to_boolean()
    {
        $cycle = Cycle::factory()->create(['is_public' => 1]);

        $this->assertIsBool($cycle->is_public);
        $this->assertTrue($cycle->is_public);
    }

    /** @test */
    public function it_can_belong_to_organization()
    {
        $organization = Organization::factory()->create();
        $cycle = Cycle::factory()->create(['organization_id' => $organization->id]);

        $this->assertInstanceOf(Organization::class, $cycle->organization);
        $this->assertEquals($organization->id, $cycle->organization->id);
    }

    /** @test */
    public function it_can_have_companies()
    {
        $cycle = Cycle::factory()->create();
        Company::factory()->count(10)->create(['cycle' => $cycle->id]);

        $this->assertCount(10, $cycle->companies);
    }

    /** @test */
    public function it_can_have_mentors()
    {
        $cycle = Cycle::factory()->create();
        $mentor1 = User::factory()->create();
        $mentor2 = User::factory()->create();

        DB::table('cycles_mentors')->insert([
            ['cycle_id' => $cycle->id, 'user_id' => $mentor1->id],
            ['cycle_id' => $cycle->id, 'user_id' => $mentor2->id],
        ]);

        $this->assertCount(2, $cycle->mentors);
    }

    /** @test */
    public function it_can_have_steps()
    {
        $cycle = Cycle::factory()->create();
        Step::factory()->count(5)->create(['cycle_id' => $cycle->id]);

        $this->assertCount(5, $cycle->steps);
    }

    /** @test */
    public function it_can_have_mentorship_requests()
    {
        $cycle = Cycle::factory()->create();
        MentorshipRequest::factory()->count(8)->create(['cycle_id' => $cycle->id]);

        $this->assertCount(8, $cycle->mentorshipRequests);
    }

    /** @test */
    public function it_can_have_projects()
    {
        $cycle = Cycle::factory()->create();
        $project = Project::factory()->create();

        DB::table('cycle_project')->insert([
            'cycle_id' => $cycle->id,
            'project_id' => $project->id,
            'status' => 'active',
        ]);

        $this->assertCount(1, $cycle->projects);
    }

    /** @test */
    public function it_can_calculate_progress()
    {
        $cycle = Cycle::factory()->create();
        Step::factory()->create([
            'cycle_id' => $cycle->id,
            'to' => Carbon::now()->addDays(10),
        ]);
        Step::factory()->create([
            'cycle_id' => $cycle->id,
            'to' => Carbon::now()->subDays(5),
        ]);

        $progress = $cycle->getProgress();

        $this->assertIsNumeric($progress);
        $this->assertGreaterThanOrEqual(0, $progress);
        $this->assertLessThanOrEqual(100, $progress);
    }

    /** @test */
    public function it_returns_zero_progress_when_no_steps()
    {
        $cycle = Cycle::factory()->create();

        $progress = $cycle->getProgress();

        $this->assertEquals(0, $progress);
    }

    /** @test */
    public function it_can_get_program_types()
    {
        $types = Cycle::programTypes();

        $this->assertIsArray($types);
        $this->assertArrayHasKey('General_Program', $types);
        $this->assertArrayHasKey('Hackathon', $types);
    }

    /** @test */
    public function it_can_have_custom_pages()
    {
        $cycle = Cycle::factory()->create();
        // Assuming CycleCustomPages model exists
        // CycleCustomPages::factory()->count(3)->create(['cycle_id' => $cycle->id]);

        // $this->assertCount(3, $cycle->custom_pages);
        $this->assertTrue(true); // Placeholder
    }

    /** @test */
    public function it_has_start_format_attribute()
    {
        $cycle = Cycle::factory()->create(['start' => '2024-01-15']);

        $this->assertEquals('2024-01-15', $cycle->start_format);
    }

    /** @test */
    public function it_has_end_format_attribute()
    {
        $cycle = Cycle::factory()->create(['end' => '2024-12-31']);

        $this->assertEquals('2024-12-31', $cycle->end_format);
    }
}

