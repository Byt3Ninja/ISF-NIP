<?php

namespace Database\Factories;

use App\Formfield;
use App\Step;
use App\Field;
use App\BuilderForm;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormfieldFactory extends Factory
{
    protected $model = Formfield::class;

    public function definition(): array
    {
        return [
            'step_id' => Step::factory(),
            'field_id' => Field::factory(),
            'options' => json_encode([]),
            'builder_forms_id' => BuilderForm::factory(),
        ];
    }
}

