<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\FormRepository;
use App\BuilderForm;
use App\Step;

class FormRepositoryTest extends TestCase
{

    protected FormRepository $formRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formRepository = new FormRepository();
    }

    /** @test */
    public function it_can_get_all_forms_with_steps()
    {
        $form1 = BuilderForm::factory()->create(['form_name' => 'Form 1']);
        $form2 = BuilderForm::factory()->create(['form_name' => 'Form 2']);
        
        Step::factory()->create(['stype' => 1, 'data' => $form1->id]);
        Step::factory()->create(['stype' => 1, 'data' => $form2->id]);

        $result = $this->formRepository->getAllFormsWithSteps();

        $this->assertIsIterable($result);
        $this->assertGreaterThan(0, $result->count());
    }

    /** @test */
    public function it_can_search_forms()
    {
        BuilderForm::factory()->create(['form_name' => 'Registration Form']);
        BuilderForm::factory()->create(['form_name' => 'Application Form']);

        $result = $this->formRepository->getAllFormsWithSteps(['search' => 'Registration']);

        $this->assertIsIterable($result);
        $this->assertGreaterThan(0, $result->count());
    }

    /** @test */
    public function it_eager_loads_steps_relationship()
    {
        $form = BuilderForm::factory()->create();
        Step::factory()->count(3)->create(['stype' => 1, 'data' => $form->id]);

        $result = $this->formRepository->getAllFormsWithSteps();

        $this->assertTrue($result->first()->relationLoaded('steps'));
    }
}

