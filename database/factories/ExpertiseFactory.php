<?php

namespace Database\Factories;

use App\Expertise;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpertiseFactory extends Factory
{
    protected $model = Expertise::class;

    public function definition(): array
    {
        return [
            'name_en' => $this->faker->words(2, true),
            'name_ar' => $this->faker->words(2, true),
        ];
    }
}

