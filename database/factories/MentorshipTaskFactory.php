<?php

namespace Database\Factories;

use App\MentorshipTask;
use App\MentorshipRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class MentorshipTaskFactory extends Factory
{
    protected $model = MentorshipTask::class;

    public function definition(): array
    {
        return [
            'mentorship_request_id' => MentorshipRequest::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed']),
            'parent_id' => null,
        ];
    }
}

