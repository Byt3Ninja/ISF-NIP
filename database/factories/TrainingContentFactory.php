<?php

namespace Database\Factories;

use App\TrainingContent;
use App\Training;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingContentFactory extends Factory
{
    protected $model = TrainingContent::class;

    public function definition(): array
    {
        return [
            'training_id' => Training::factory(),
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'dsc' => $this->faker->paragraph,
            'arr' => $this->faker->numberBetween(1, 20),
            'content' => $this->faker->paragraphs(3, true),
        ];
    }
}

