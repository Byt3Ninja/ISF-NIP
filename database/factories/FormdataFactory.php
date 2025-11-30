<?php

namespace Database\Factories;

use App\Formdata;
use App\Company;
use App\Step;
use App\Formfield;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormdataFactory extends Factory
{
    protected $model = Formdata::class;

    public function definition(): array
    {
        return [
            'field_id' => Formfield::factory(),
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'form_id' => $this->faker->numberBetween(1, 100),
            'step_id' => Step::factory(),
            'question' => $this->faker->sentence,
            'answer' => $this->faker->sentence,
            'screening' => json_encode([]),
            'comments' => json_encode([]),
        ];
    }
}

