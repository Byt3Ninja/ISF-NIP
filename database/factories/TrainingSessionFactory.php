<?php

namespace Database\Factories;

use App\TrainingSession;
use App\Step;
use App\Training;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class TrainingSessionFactory extends Factory
{
    protected $model = TrainingSession::class;

    public function definition(): array
    {
        $datetime = Carbon::now()->addDays($this->faker->numberBetween(1, 30));

        return [
            'step_id' => Step::factory(),
            'training_Address' => Training::factory(),
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'dsc' => $this->faker->paragraph,
            'datetime' => $datetime,
            'enddatetime' => $datetime->copy()->addHours(2),
            'duration' => $this->faker->numberBetween(60, 180),
            'zoom_id' => $this->faker->numerify('##########'),
            'zoom_passcode' => $this->faker->numerify('######'),
            'start_url' => $this->faker->url,
            'join_url' => $this->faker->url,
            'trainer_name' => $this->faker->name,
            'trainer_email' => $this->faker->safeEmail,
            'training_location' => $this->faker->address,
            'QrCode' => null,
            'type' => $this->faker->randomElement(['live', 'recorded']),
        ];
    }
}

