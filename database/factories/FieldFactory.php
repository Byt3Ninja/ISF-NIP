<?php

namespace Database\Factories;

use App\Field;
use Illuminate\Database\Eloquent\Factories\Factory;

class FieldFactory extends Factory
{
    protected $model = Field::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'type' => $this->faker->randomElement(['text', 'textarea', 'select', 'checkbox', 'radio', 'file']),
            'label_en' => $this->faker->sentence(2),
            'label_ar' => $this->faker->sentence(2),
        ];
    }
}

