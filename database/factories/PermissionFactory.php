<?php

namespace Database\Factories;

use App\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word . '.' . $this->faker->word,
            'description' => $this->faker->sentence,
            'is_core' => $this->faker->boolean(30),
            'permission_group_id' => null,
        ];
    }
}

