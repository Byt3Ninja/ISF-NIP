<?php

namespace Database\Factories;

use App\Tags;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagsFactory extends Factory
{
    protected $model = Tags::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
        ];
    }
}

