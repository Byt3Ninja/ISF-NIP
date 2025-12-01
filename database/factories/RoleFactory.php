<?php

namespace Database\Factories;

use App\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word . '_role',
            'default_for' => null,
        ];
    }

    public function organizationManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'organizationManager',
            'default_for' => 'organizationManager',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'admin',
            'default_for' => 'admin',
        ]);
    }
}

