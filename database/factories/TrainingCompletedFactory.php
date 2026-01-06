<?php

namespace Database\Factories;

use App\TrainingCompleted;
use App\Training;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingCompletedFactory extends Factory
{
    protected $model = TrainingCompleted::class;

    public function definition(): array
    {
        return [
            'course_id' => Training::factory(),
            'user_id' => User::factory(),
            'training_id' => $this->faker->numberBetween(1, 100),
        ];
    }
}

