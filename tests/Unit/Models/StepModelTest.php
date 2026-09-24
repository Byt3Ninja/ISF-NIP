<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Step;
use App\Cycle;
use App\BuilderForm;
use App\Company;
use App\Screening;
use App\Formdata;
use Carbon\Carbon;

class StepModelTest extends TestCase
{

    /** @test */
    public function it_casts_dates()
    {
        $step = Step::factory()->create([
            'from' => '2024-01-01',
            'to' => '2024-01-31',
        ]);

        $this->assertInstanceOf(Carbon::class, $step->from);
        $this->assertInstanceOf(Carbon::class, $step->to);
    }

    /** @test */
    public function it_can_belong_to_cycle()
    {
        $cycle = Cycle::factory()->create();
        $step = Step::factory()->create(['cycle_id' => $cycle->id]);

        $this->assertInstanceOf(Cycle::class, $step->cycle);
        $this->assertEquals($cycle->id, $step->cycle->id);
    }

    /** @test */
    public function it_can_belong_to_form()
    {
        $form = BuilderForm::factory()->create();
        $step = Step::factory()->create(['stype' => 1, 'data' => $form->id]);

        $this->assertInstanceOf(BuilderForm::class, $step->form);
        $this->assertEquals($form->id, $step->form->id);
    }

    /** @test */
    public function it_can_have_companies()
    {
        $step = Step::factory()->create();
        Company::factory()->count(5)->create(['step' => $step->id]);

        $this->assertCount(5, $step->companies);
    }

    /** @test */
    public function it_can_have_form_data()
    {
        $step = Step::factory()->create();
        Formdata::factory()->count(10)->create(['step_id' => $step->id]);

        $this->assertCount(10, $step->formData);
    }

    /** @test */
    public function it_can_have_screenings()
    {
        $step = Step::factory()->create();
        Screening::factory()->count(3)->create(['step_id' => $step->id]);

        $this->assertCount(3, $step->screenings);
    }

    /** @test */
    public function it_has_stype_label_attribute()
    {
        $step = Step::factory()->create(['stype' => 1]);
        $this->assertNotNull($step->stype_label);

        $step = Step::factory()->create(['stype' => 2]);
        $this->assertNotNull($step->stype_label);

        $step = Step::factory()->create(['stype' => 3]);
        $this->assertNotNull($step->stype_label);

        $step = Step::factory()->create(['stype' => 4]);
        $this->assertNotNull($step->stype_label);

        $step = Step::factory()->create(['stype' => 5]);
        $this->assertNotNull($step->stype_label);

        $step = Step::factory()->create(['stype' => 6]);
        $this->assertNotNull($step->stype_label);
    }

    /** @test */
    public function it_returns_null_for_invalid_stype()
    {
        $step = Step::factory()->create(['stype' => 99]);

        $this->assertNull($step->stype_label);
    }

    /** @test */
    public function it_can_calculate_judgers_count_for_grouptogroup()
    {
        $step = Step::factory()->create([
            'stype' => 2,
            'data' => 'grouptogroup',
            'list' => [[1, 2, 3], [4, 5]],
        ]);

        $count = $step->judgers;

        $this->assertEquals(5, $count);
    }

    /** @test */
    public function it_can_calculate_judgers_count_for_onetoall()
    {
        $step = Step::factory()->create([
            'stype' => 2,
            'data' => 'onetoall',
            'list' => [1, 2, 3],
        ]);

        $count = $step->judgers;

        $this->assertEquals(3, $count);
    }

    /** @test */
    public function it_can_calculate_judgers_count_for_grouptoall()
    {
        $step = Step::factory()->create([
            'stype' => 2,
            'data' => 'grouptoall',
            'list' => [[1, 2], [3, 4, 5]],
        ]);

        $count = $step->judgers;

        $this->assertEquals(5, $count);
    }

    /** @test */
    public function it_can_calculate_judgers_from_screenings()
    {
        $step = Step::factory()->create(['stype' => 2, 'data' => null]);
        $screening1 = Screening::factory()->create([
            'step_id' => $step->id,
            'users' => [1, 2, 3],
        ]);
        $screening2 = Screening::factory()->create([
            'step_id' => $step->id,
            'users' => [4, 5],
        ]);

        $count = $step->judgers;

        $this->assertEquals(5, $count);
    }
}

