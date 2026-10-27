<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\StepRepository;
use App\Step;
use App\Cycle;

class StepRepositoryTest extends TestCase
{

    protected StepRepository $stepRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stepRepository = new StepRepository();
    }

    /** @test */
    public function it_can_create_step()
    {
        $cycle = Cycle::factory()->create();
        $stepData = [
            'cycle_id' => $cycle->id,
            'title' => 'Test Step',
            'description' => 'Description',
            'stype' => 1,
            'arr' => 1,
            'from' => now(),
            'to' => now()->addDays(30),
        ];

        $result = $this->stepRepository->create($stepData);

        $this->assertInstanceOf(Step::class, $result);
        $this->assertDatabaseHas('steps', [
            'title' => 'Test Step',
            'cycle_id' => $cycle->id,
        ]);
    }

    /** @test */
    public function it_can_update_step()
    {
        $step = Step::factory()->create();
        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ];

        $result = $this->stepRepository->update($step, $updateData);

        $this->assertInstanceOf(Step::class, $result);
        $this->assertEquals('Updated Title', $step->fresh()->title);
        $this->assertEquals('Updated Description', $step->fresh()->description);
    }

    /** @test */
    public function it_can_delete_step()
    {
        $step = Step::factory()->create();

        $result = $this->stepRepository->delete($step);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('steps', ['id' => $step->id]);
    }

    /** @test */
    public function it_can_find_step_by_id()
    {
        $step = Step::factory()->create();

        $result = $this->stepRepository->find($step->id);

        $this->assertInstanceOf(Step::class, $result);
        $this->assertEquals($step->id, $result->id);
    }

    /** @test */
    public function it_returns_null_when_step_not_found()
    {
        $result = $this->stepRepository->find(99999);

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_find_step_in_program()
    {
        $cycle = Cycle::factory()->create();
        $step = Step::factory()->create(['cycle_id' => $cycle->id]);
        Step::factory()->create(['cycle_id' => Cycle::factory()->create()->id]);

        $result = $this->stepRepository->findInProgram($cycle->id, $step->id);

        $this->assertInstanceOf(Step::class, $result);
        $this->assertEquals($step->id, $result->id);
        $this->assertEquals($cycle->id, $result->cycle_id);
    }

    /** @test */
    public function it_returns_null_when_step_not_in_program()
    {
        $cycle = Cycle::factory()->create();
        $otherCycle = Cycle::factory()->create();
        $step = Step::factory()->create(['cycle_id' => $otherCycle->id]);

        $result = $this->stepRepository->findInProgram($cycle->id, $step->id);

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_get_all_steps_by_program()
    {
        $cycle = Cycle::factory()->create();
        Step::factory()->count(5)->create(['cycle_id' => $cycle->id]);
        Step::factory()->count(3)->create(['cycle_id' => Cycle::factory()->create()->id]);

        $result = $this->stepRepository->getByProgram($cycle->id);

        $this->assertCount(5, $result);
        $result->each(function ($step) use ($cycle) {
            $this->assertEquals($cycle->id, $step->cycle_id);
        });
    }
}

