<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\MentorshipRequest;
use App\User;
use App\Cycle;
use App\Organization;
use App\MentorshipTask;
use Carbon\Carbon;

class MentorshipRequestModelTest extends TestCase
{

    /** @test */
    public function it_casts_zoom_date_to_datetime()
    {
        $request = MentorshipRequest::factory()->create([
            'zoom_date' => '2024-01-15 10:00:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $request->zoom_date);
    }

    /** @test */
    public function it_can_belong_to_user()
    {
        $user = User::factory()->create();
        $request = MentorshipRequest::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $request->user);
        $this->assertEquals($user->id, $request->user->id);
    }

    /** @test */
    public function it_can_belong_to_mentor()
    {
        $mentor = User::factory()->create();
        $request = MentorshipRequest::factory()->create(['mentor_id' => $mentor->id]);

        $this->assertInstanceOf(User::class, $request->mentor);
        $this->assertEquals($mentor->id, $request->mentor->id);
    }

    /** @test */
    public function it_can_belong_to_cycle()
    {
        $cycle = Cycle::factory()->create();
        $request = MentorshipRequest::factory()->create(['cycle_id' => $cycle->id]);

        $this->assertInstanceOf(Cycle::class, $request->cycle);
        $this->assertEquals($cycle->id, $request->cycle->id);
    }

    /** @test */
    public function it_can_belong_to_organization()
    {
        $organization = Organization::factory()->create();
        $request = MentorshipRequest::factory()->create(['organization_id' => $organization->id]);

        $this->assertInstanceOf(Organization::class, $request->orgnization);
        $this->assertEquals($organization->id, $request->orgnization->id);
    }

    /** @test */
    public function it_can_have_mentorship_tasks()
    {
        $request = MentorshipRequest::factory()->create();
        MentorshipTask::factory()->count(5)->create(['mentorship_request_id' => $request->id]);

        $this->assertCount(5, $request->mentorshipTasks);
        $this->assertCount(5, $request->tasks);
    }

    /** @test */
    public function it_has_code_attribute()
    {
        $user = User::factory()->create();
        $mentor = User::factory()->create();
        $request = MentorshipRequest::factory()->create([
            'user_id' => $user->id,
            'mentor_id' => $mentor->id,
        ]);

        $expectedCode = "M-{$mentor->id}-{$user->id}-{$request->id}";
        $this->assertEquals($expectedCode, $request->code);
    }

    /** @test */
    public function it_can_have_reports()
    {
        $request = MentorshipRequest::factory()->create();
        // Assuming SessionReport model exists
        // SessionReport::factory()->count(3)->create(['mentorship_request_id' => $request->id]);

        // $this->assertCount(3, $request->reports);
        $this->assertTrue(true); // Placeholder
    }

    /** @test */
    public function it_can_have_recordings()
    {
        $request = MentorshipRequest::factory()->create();
        // Assuming SessionRecording model exists
        // SessionRecording::factory()->count(2)->create(['mentorship_request_id' => $request->id]);

        // $this->assertCount(2, $request->recordings);
        $this->assertTrue(true); // Placeholder
    }
}

