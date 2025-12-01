<?php

namespace Database\Factories;

use App\MentorshipRequest;
use App\User;
use App\Cycle;
use App\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class MentorshipRequestFactory extends Factory
{
    protected $model = MentorshipRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'mentor_id' => User::factory(),
            'cycle_id' => Cycle::factory(),
            'organization_id' => Organization::factory(),
            'zoom_date' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)),
            'period' => $this->faker->numberBetween(30, 120),
            'status' => $this->faker->randomElement(['pending', 'accepted', 'rejected', 'completed']),
        ];
    }
}

