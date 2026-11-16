<?php

namespace Database\Factories;

use App\Company;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name_en' => $this->faker->company,
            'name_ar' => $this->faker->company,
            'email' => $this->faker->unique()->companyEmail,
            'phone' => $this->faker->phoneNumber,
            'address_en' => $this->faker->address,
            'address_ar' => $this->faker->address,
            'sector' => $this->faker->word,
            'info_company' => $this->faker->paragraph,
            'business_phase' => $this->faker->randomElement(['idea', 'startup', 'growth', 'mature']),
            'years_of_operations' => $this->faker->numberBetween(0, 20),
            'product_or_service' => $this->faker->randomElement(['Product', 'Service']), // varchar(50): a sentence can overflow it
            'annual_revenue' => $this->faker->numberBetween(0, 10000000),
            'website' => $this->faker->url,
            'est_date' => $this->faker->date,
            'idea' => $this->faker->paragraph,
            'problem' => $this->faker->paragraph,
            'solution' => $this->faker->paragraph,
            'team_num' => $this->faker->numberBetween(1, 50),
            'stage' => $this->faker->numberBetween(1, 10),
            'raised_fund' => $this->faker->numberBetween(0, 5000000),
            'equity_offered' => $this->faker->numberBetween(0, 100),
            'employees' => $this->faker->numberBetween(1, 200),
            'logo' => null,
            'cycle' => null,
            'step' => null,
            'approved' => $this->faker->boolean(70),
        ];
    }
}

