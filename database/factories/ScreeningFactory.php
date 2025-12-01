<?php

namespace Database\Factories;

use App\Screening;
use App\Step;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScreeningFactory extends Factory
{
    protected $model = Screening::class;

    public function definition(): array
    {
        return [
            'step_id' => Step::factory(),
            'user_id' => User::factory(),
            'panel' => $this->faker->word,
            'users' => json_encode([$this->faker->numberBetween(1, 100)]),
        ];
    }
}

