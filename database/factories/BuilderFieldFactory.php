<?php

namespace Database\Factories;

use App\BuilderField;
use App\BuilderForm;
use App\Field;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuilderFieldFactory extends Factory
{
    protected $model = BuilderField::class;

    public function definition(): array
    {
        return [
            'builder_form_id' => BuilderForm::factory(),
            'field_id' => Field::factory(),
            'options' => json_encode([]),
        ];
    }
}

