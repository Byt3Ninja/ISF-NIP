<?php

namespace Database\Factories;

use App\Step;
use App\Cycle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class StepFactory extends Factory
{
    protected $model = Step::class;

    public function definition(): array
    {
        $from = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $to = Carbon::parse($from)->addDays(30);

        return [
            'cycle_id' => Cycle::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'stype' => $this->faker->numberBetween(1, 6),
            'data' => null,
            'arr' => $this->faker->numberBetween(1, 10),
            'from' => $from,
            'to' => $to,
            'list' => null,
            'count_team_number' => 0,
        ];
    }

    public function customForm(): static
    {
        return $this->state(fn (array $attributes) => [
            'stype' => 1,
        ]);
    }

    public function screening(): static
    {
        return $this->state(fn (array $attributes) => [
            'stype' => 2,
            'data' => $this->faker->randomElement(['onetoall', 'grouptogroup', 'grouptoall']),
        ]);
    }

    public function mentorship(): static
    {
        return $this->state(fn (array $attributes) => [
            'stype' => 3,
        ]);
    }

    public function training(): static
    {
        return $this->state(fn (array $attributes) => [
            'stype' => 4,
        ]);
    }

    public function finalEvaluation(): static
    {
        return $this->state(fn (array $attributes) => [
            'stype' => 5,
        ]);
    }

    public function teamFormation(): static
    {
        return $this->state(fn (array $attributes) => [
            'stype' => 6,
        ]);
    }
}

