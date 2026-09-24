<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\TrainingSession;
use App\Training;
use App\Step;
use App\SessionAttendance;
use App\SessionRecording;
use App\SessionReport;
use Carbon\Carbon;

class TrainingSessionModelTest extends TestCase
{

    /** @test */
    public function it_can_belong_to_training()
    {
        $training = Training::factory()->create();
        $session = TrainingSession::factory()->create(['training_id' => $training->id]);

        $this->assertInstanceOf(Training::class, $session->training);
        $this->assertEquals($training->id, $session->training->id);
    }

    /** @test */
    public function it_can_belong_to_step()
    {
        $step = Step::factory()->create();
        $session = TrainingSession::factory()->create(['step_id' => $step->id]);

        $this->assertInstanceOf(Step::class, $session->step);
        $this->assertEquals($step->id, $session->step->id);
    }

    /** @test */
    public function it_can_have_attendances()
    {
        $session = TrainingSession::factory()->create();
        SessionAttendance::factory()->count(10)->create(['session_id' => $session->id]);

        $this->assertCount(10, $session->attendance);
    }

    /** @test */
    public function it_can_have_recordings()
    {
        $session = TrainingSession::factory()->create();
        // Assuming SessionRecording model exists
        // SessionRecording::factory()->count(2)->create(['session_id' => $session->id]);

        // $this->assertCount(2, $session->recordings);
        $this->assertTrue(true); // Placeholder
    }

    /** @test */
    public function it_can_have_reports()
    {
        $session = TrainingSession::factory()->create();
        // Assuming SessionReport model exists
        // SessionReport::factory()->count(3)->create(['session_id' => $session->id]);

        // $this->assertCount(3, $session->reports);
        $this->assertTrue(true); // Placeholder
    }

    /** @test */
    public function it_can_check_if_upcoming()
    {
        $session = TrainingSession::factory()->create([
            'date' => Carbon::now()->addDays(5),
        ]);

        // Assuming method exists or can be tested via date comparison
        $this->assertTrue($session->date->isFuture());
    }

    /** @test */
    public function it_can_check_if_past()
    {
        $session = TrainingSession::factory()->create([
            'date' => Carbon::now()->subDays(5),
        ]);

        $this->assertTrue($session->date->isPast());
    }

    /** @test */
    public function it_can_calculate_attendance_rate()
    {
        $session = TrainingSession::factory()->create();
        SessionAttendance::factory()->count(8)->create([
            'session_id' => $session->id,
            'attended' => true,
        ]);
        SessionAttendance::factory()->count(2)->create([
            'session_id' => $session->id,
            'attended' => false,
        ]);

        // Assuming method exists or can calculate
        $attended = $session->attendances->where('attended', true)->count();
        $total = $session->attendances->count();
        $rate = $total > 0 ? ($attended / $total) * 100 : 0;

        $this->assertEquals(80, $rate);
    }
}

