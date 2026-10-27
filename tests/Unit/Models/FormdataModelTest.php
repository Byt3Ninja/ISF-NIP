<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Formdata;
use App\Company;
use App\Step;
use App\Formfield;
use App\User;

class FormdataModelTest extends TestCase
{

    /** @test */
    public function it_has_fillable_attributes()
    {
        $formdata = new Formdata();
        $fillable = $formdata->getFillable();

        $this->assertContains('field_id', $fillable);
        $this->assertContains('company_id', $fillable);
        $this->assertContains('user_id', $fillable);
        $this->assertContains('form_id', $fillable);
        $this->assertContains('step_id', $fillable);
        $this->assertContains('question', $fillable);
        $this->assertContains('answer', $fillable);
    }

    /** @test */
    public function it_casts_screening_to_array()
    {
        $screening = [1 => 5, 2 => 8, 3 => 7];
        $formdata = Formdata::factory()->create([
            'screening' => json_encode($screening),
        ]);

        $this->assertIsArray($formdata->screening);
        $this->assertEquals($screening, $formdata->screening);
    }

    /** @test */
    public function it_casts_comments_to_array()
    {
        $comments = ['comment1', 'comment2'];
        $formdata = Formdata::factory()->create([
            'comments' => json_encode($comments),
        ]);

        $this->assertIsArray($formdata->comments);
        $this->assertEquals($comments, $formdata->comments);
    }

    /** @test */
    public function it_can_belong_to_field()
    {
        $field = Formfield::factory()->create();
        $formdata = Formdata::factory()->create(['field_id' => $field->id]);

        $this->assertInstanceOf(Formfield::class, $formdata->field);
        $this->assertEquals($field->id, $formdata->field->id);
    }

    /** @test */
    public function it_can_belong_to_step()
    {
        $step = Step::factory()->create();
        $formdata = Formdata::factory()->create(['step_id' => $step->id]);

        $this->assertInstanceOf(Step::class, $formdata->step);
        $this->assertEquals($step->id, $formdata->step->id);
    }

    /** @test */
    public function it_has_composite_primary_key()
    {
        $formdata = new Formdata();
        $primaryKey = $formdata->getKeyName();

        $this->assertIsArray($primaryKey);
        $this->assertContains('field_id', $primaryKey);
        $this->assertContains('company_id', $primaryKey);
    }

    /** @test */
    public function it_has_incrementing_set_to_false()
    {
        $formdata = new Formdata();

        $this->assertFalse($formdata->getIncrementing());
    }

    /** @test */
    public function it_can_get_forms_data_statically()
    {
        $request = new \Illuminate\Http\Request();
        $user = User::factory()->create();
        
        $this->actingAs($user);

        // This is a complex static method, test basic structure
        $result = Formdata::formsData($request);

        $this->assertIsIterable($result);
    }
}

