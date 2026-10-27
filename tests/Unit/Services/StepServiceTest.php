<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\StepService;
use App\Repositories\StepRepository;
use App\Step;
use App\Cycle;
use App\BuilderForm;
use App\BuilderField;
use App\Formfield;
use Mockery;

class StepServiceTest extends TestCase
{

    protected StepService $stepService;
    protected $stepRepoMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->stepRepoMock = Mockery::mock(StepRepository::class);
        $this->stepService = new StepService($this->stepRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_create_custom_form_step()
    {
        $cycle = Cycle::factory()->create();
        $form = BuilderForm::factory()->create();
        $fields = BuilderField::factory()->count(3)->create(['builder_form_id' => $form->id]);

        $stepData = [
            'programId' => $cycle->id,
            'title' => 'Custom Form Step',
            'description' => 'Description',
            'stepTypeId' => 1,
            'formId' => $form->id,
            'stepArrange' => 1,
            'startDate' => now()->format('Y-m-d'),
            'endDate' => now()->addDays(30)->format('Y-m-d'),
        ];

        $createdStep = Step::factory()->make($stepData);
        $createdStep->id = 1;

        $this->stepRepoMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($createdStep);

        $result = $this->stepService->createStep($stepData);

        $this->assertInstanceOf(Step::class, $result);
        $this->assertDatabaseHas('form_fields', [
            'step_id' => $result->id,
        ]);
    }

    /** @test */
    public function it_can_create_screening_step()
    {
        $cycle = Cycle::factory()->create();

        $stepData = [
            'programId' => $cycle->id,
            'title' => 'Screening Step',
            'description' => 'Description',
            'stepTypeId' => 2,
            'screeningType' => 'onetoall',
            'stepArrange' => 1,
            'startDate' => now()->format('Y-m-d'),
            'endDate' => now()->addDays(30)->format('Y-m-d'),
        ];

        $createdStep = Step::factory()->make($stepData);

        $this->stepRepoMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($createdStep);

        $result = $this->stepService->createStep($stepData);

        $this->assertInstanceOf(Step::class, $result);
        $this->assertEquals('onetoall', $result->data);
    }

    /** @test */
    public function it_can_create_training_step()
    {
        $cycle = Cycle::factory()->create();

        $stepData = [
            'programId' => $cycle->id,
            'title' => 'Training Step',
            'description' => 'Description',
            'stepTypeId' => 4,
            'RecordedSelect' => 'recorded',
            'stepArrange' => 1,
            'startDate' => now()->format('Y-m-d'),
            'endDate' => now()->addDays(30)->format('Y-m-d'),
        ];

        $createdStep = Step::factory()->make($stepData);

        $this->stepRepoMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($createdStep);

        $result = $this->stepService->createStep($stepData);

        $this->assertInstanceOf(Step::class, $result);
    }

    /** @test */
    public function it_validates_hackathon_step_requires_hackathon_cycle()
    {
        $cycle = Cycle::factory()->create(['type' => 'General_Program']);

        $stepData = [
            'programId' => $cycle->id,
            'title' => 'Hackathon Step',
            'description' => 'Description',
            'stepTypeId' => 6,
            'stepArrange' => 1,
            'startDate' => now()->format('Y-m-d'),
            'endDate' => now()->addDays(30)->format('Y-m-d'),
        ];

        $this->expectException(\Illuminate\Http\Exceptions\HttpResponseException::class);

        $this->stepService->createStep($stepData);
    }

    /** @test */
    public function it_allows_hackathon_step_for_hackathon_cycle()
    {
        $cycle = Cycle::factory()->create(['type' => 'Hackathon']);

        $stepData = [
            'programId' => $cycle->id,
            'title' => 'Hackathon Step',
            'description' => 'Description',
            'stepTypeId' => 6,
            'stepArrange' => 1,
            'startDate' => now()->format('Y-m-d'),
            'endDate' => now()->addDays(30)->format('Y-m-d'),
        ];

        $createdStep = Step::factory()->make($stepData);

        $this->stepRepoMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($createdStep);

        $result = $this->stepService->createStep($stepData);

        $this->assertInstanceOf(Step::class, $result);
    }

    /** @test */
    public function it_validates_final_evaluation_requires_form()
    {
        $cycle = Cycle::factory()->create();

        $stepData = [
            'programId' => $cycle->id,
            'title' => 'Final Evaluation',
            'description' => 'Description',
            'stepTypeId' => 5,
            'stepArrange' => 1,
            'startDate' => now()->format('Y-m-d'),
            'endDate' => now()->addDays(30)->format('Y-m-d'),
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->stepService->createStep($stepData);
    }

    /** @test */
    public function it_can_update_step()
    {
        $step = Step::factory()->create();
        $updateData = [
            'programId' => $step->cycle_id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'stepTypeId' => $step->stype,
            'stepArrange' => 2,
            'startDate' => now()->format('Y-m-d'),
            'endDate' => now()->addDays(30)->format('Y-m-d'),
        ];

        $this->stepRepoMock
            ->shouldReceive('find')
            ->once()
            ->with($step->id)
            ->andReturn($step);

        $this->stepRepoMock
            ->shouldReceive('update')
            ->once()
            ->with($step, Mockery::type('array'))
            ->andReturn($step);

        $result = $this->stepService->updateStep($updateData, $step->id);

        $this->assertInstanceOf(Step::class, $result);
    }

    /** @test */
    public function it_throws_exception_when_updating_nonexistent_step()
    {
        $this->stepRepoMock
            ->shouldReceive('find')
            ->once()
            ->with(99999)
            ->andReturn(null);

        $this->expectException(\Illuminate\Http\Exceptions\HttpResponseException::class);

        $this->stepService->updateStep([], 99999);
    }

    /** @test */
    public function it_can_delete_step()
    {
        $step = Step::factory()->create();

        $this->stepRepoMock
            ->shouldReceive('find')
            ->once()
            ->with($step->id)
            ->andReturn($step);

        $this->stepRepoMock
            ->shouldReceive('delete')
            ->once()
            ->with($step)
            ->andReturn(true);

        $result = $this->stepService->deleteStep($step->id);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_throws_exception_when_deleting_nonexistent_step()
    {
        $this->stepRepoMock
            ->shouldReceive('find')
            ->once()
            ->with(99999)
            ->andReturn(null);

        $this->expectException(\Illuminate\Http\Exceptions\HttpResponseException::class);

        $this->stepService->deleteStep(99999);
    }

    /** @test */
    public function it_can_reorder_steps()
    {
        $cycle = Cycle::factory()->create();
        $step1 = Step::factory()->create(['cycle_id' => $cycle->id, 'arr' => 1]);
        $step2 = Step::factory()->create(['cycle_id' => $cycle->id, 'arr' => 2]);
        $step3 = Step::factory()->create(['cycle_id' => $cycle->id, 'arr' => 3]);

        $stepsData = [
            ['id' => $step1->id, 'stepArrange' => 3],
            ['id' => $step2->id, 'stepArrange' => 1],
            ['id' => $step3->id, 'stepArrange' => 2],
        ];

        $this->stepRepoMock
            ->shouldReceive('findInProgram')
            ->times(3)
            ->andReturnUsing(function ($programId, $stepId) use ($step1, $step2, $step3) {
                return collect([$step1, $step2, $step3])->firstWhere('id', $stepId);
            });

        $result = $this->stepService->reorderSteps($cycle->id, $stepsData);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    /** @test */
    public function it_can_change_step_order_before_target()
    {
        $cycle = Cycle::factory()->create();
        $step1 = Step::factory()->create(['cycle_id' => $cycle->id, 'arr' => 1]);
        $step2 = Step::factory()->create(['cycle_id' => $cycle->id, 'arr' => 2]);
        $step3 = Step::factory()->create(['cycle_id' => $cycle->id, 'arr' => 3]);

        $this->stepRepoMock
            ->shouldReceive('findInProgram')
            ->twice()
            ->andReturnUsing(function ($programId, $stepId) use ($step1, $step2, $step3) {
                return collect([$step1, $step2, $step3])->firstWhere('id', $stepId);
            });

        $this->stepRepoMock
            ->shouldReceive('getByProgram')
            ->once()
            ->with($cycle->id)
            ->andReturn(collect([$step1, $step2, $step3]));

        $result = $this->stepService->changeStepOrder($cycle->id, $step3->id, [
            'targetStepId' => $step1->id,
            'direction' => 'before',
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('affectedSteps', $result);
    }

    /** @test */
    public function it_can_get_step_types()
    {
        $result = $this->stepService->getStepTypes();

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(6, $result);
        $this->assertEquals(1, $result->first()['id']);
        $this->assertArrayHasKey('name', $result->first());
        $this->assertArrayHasKey('requiresForm', $result->first());
    }
}

