<?php

namespace Database\Factories;

use App\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        $currencies = [
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'rate' => 1.0],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'rate' => 0.85],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'rate' => 0.75],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'rate' => 110.0],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'ر.س', 'rate' => 3.75],
        ];

        $currency = $this->faker->randomElement($currencies);

        return [
            'currency_code' => $currency['code'],
            'currency_name' => $currency['name'],
            'currency_symbol' => $currency['symbol'],
            'exchange_rate_to_usd' => $currency['rate'],
            'enabled' => $this->faker->boolean(80),
            'flag' => null,
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => false,
        ]);
    }
}

