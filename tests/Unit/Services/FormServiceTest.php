<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FormService;
use App\Repositories\FormRepository;
use App\BuilderForm;
use App\Step;
use Mockery;

class FormServiceTest extends TestCase
{

    protected FormService $formService;
    protected $formRepoMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->formRepoMock = Mockery::mock(FormRepository::class);
        $this->formService = new FormService($this->formRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_available_forms()
    {
        $form1 = BuilderForm::factory()->create(['form_name' => 'Form 1']);
        $form2 = BuilderForm::factory()->create(['form_name' => 'Form 2']);
        
        $step1 = Step::factory()->create(['stype' => 1, 'data' => $form1->id]);
        $step2 = Step::factory()->create(['stype' => 2, 'data' => 'onetoall']);
        
        $form1->setRelation('steps', collect([$step1]));
        $form2->setRelation('steps', collect([$step2]));

        $this->formRepoMock
            ->shouldReceive('getAllFormsWithSteps')
            ->once()
            ->with([])
            ->andReturn(collect([$form1, $form2]));

        $result = $this->formService->getAvailableForms();

        $this->assertIsIterable($result);
        $this->assertGreaterThan(0, $result->count());
    }

    /** @test */
    public function it_can_filter_forms_by_step_type()
    {
        $form = BuilderForm::factory()->create();
        $step = Step::factory()->create(['stype' => 1]);
        $form->setRelation('steps', collect([$step]));

        $this->formRepoMock
            ->shouldReceive('getAllFormsWithSteps')
            ->once()
            ->with(['stepType' => 'Custom Form'])
            ->andReturn(collect([$form]));

        $result = $this->formService->getAvailableForms(['stepType' => 'Custom Form']);

        $this->assertIsIterable($result);
    }

    /** @test */
    public function it_filters_out_forms_without_matching_step_type()
    {
        $form = BuilderForm::factory()->create();
        $step = Step::factory()->create(['stype' => 2]); // Screening, not Custom Form
        $form->setRelation('steps', collect([$step]));

        $this->formRepoMock
            ->shouldReceive('getAllFormsWithSteps')
            ->once()
            ->with(['stepType' => 'Custom Form'])
            ->andReturn(collect([$form]));

        $result = $this->formService->getAvailableForms(['stepType' => 'Custom Form']);

        $this->assertCount(0, $result);
    }

    /** @test */
    public function it_includes_form_metadata()
    {
        $form = BuilderForm::factory()->create([
            'form_name' => 'Test Form',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $step = Step::factory()->create(['stype' => 1]);
        $form->setRelation('steps', collect([$step]));

        $this->formRepoMock
            ->shouldReceive('getAllFormsWithSteps')
            ->once()
            ->with([])
            ->andReturn(collect([$form]));

        $result = $this->formService->getAvailableForms();

        $this->assertArrayHasKey('id', $result->first());
        $this->assertArrayHasKey('name', $result->first());
        $this->assertArrayHasKey('stepTypes', $result->first());
        $this->assertArrayHasKey('createdAt', $result->first());
        $this->assertArrayHasKey('lastModified', $result->first());
    }

    /** @test */
    public function it_can_search_forms()
    {
        $form1 = BuilderForm::factory()->create(['form_name' => 'Registration Form']);
        $form2 = BuilderForm::factory()->create(['form_name' => 'Application Form']);

        $this->formRepoMock
            ->shouldReceive('getAllFormsWithSteps')
            ->once()
            ->with(['search' => 'Registration'])
            ->andReturn(collect([$form1]));

        $result = $this->formService->getAvailableForms(['search' => 'Registration']);

        $this->assertIsIterable($result);
    }
}

