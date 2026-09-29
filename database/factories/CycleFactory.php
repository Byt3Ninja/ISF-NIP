<?php

namespace Database\Factories;

use App\Cycle;
use App\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class CycleFactory extends Factory
{
    protected $model = Cycle::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 year', '+1 year');
        $end = Carbon::parse($start)->addMonths(6);

        return [
            'organization_id' => Organization::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'type' => $this->faker->randomElement(['General_Program', 'Hackathon']),
            'start' => $start,
            'end' => $end,
            'is_public' => $this->faker->boolean,
        ];
    }

    public function hackathon(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'Hackathon',
        ]);
    }

    public function generalProgram(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'General_Program',
        ]);
    }
}

