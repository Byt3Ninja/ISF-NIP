<?php

namespace Database\Factories;

use App\Training;
use App\User;
use App\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class TrainingFactory extends Factory
{
    protected $model = Training::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $endDate = Carbon::parse($startDate)->addMonths(3);

        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['online', 'offline', 'hybrid']),
            'title' => $this->faker->sentence(4),
            'dsc' => $this->faker->paragraph,
            'code' => strtoupper($this->faker->unique()->bothify('TR##??')),
            'CoursLevel' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'Quizs' => $this->faker->numberBetween(0, 10),
            'Instractor' => $this->faker->name,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'Status' => $this->faker->randomElement(['active', 'inactive', 'completed']),
            'image' => null,
        ];
    }
}

