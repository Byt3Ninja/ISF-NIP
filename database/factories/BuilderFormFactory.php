<?php

namespace Database\Factories;

use App\BuilderForm;
use App\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuilderFormFactory extends Factory
{
    protected $model = BuilderForm::class;

    public function definition(): array
    {
        return [
            'form_name' => $this->faker->words(3, true) . ' Form',
            'organization_id' => Organization::factory(),
        ];
    }
}

