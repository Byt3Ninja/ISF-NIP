<?php

namespace Database\Factories;

use App\SessionAttendance;
use App\TrainingSession;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class SessionAttendanceFactory extends Factory
{
    protected $model = SessionAttendance::class;

    public function definition(): array
    {
        return [
            'session_id' => TrainingSession::factory(),
            'user_id' => User::factory(),
            'attended' => $this->faker->boolean(80),
            'joined_at' => $this->faker->optional()->dateTime,
        ];
    }

    public function attended(): static
    {
        return $this->state(fn (array $attributes) => [
            'attended' => true,
            'joined_at' => Carbon::now(),
        ]);
    }

    public function notAttended(): static
    {
        return $this->state(fn (array $attributes) => [
            'attended' => false,
            'joined_at' => null,
        ]);
    }
}

