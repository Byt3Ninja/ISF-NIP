<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('currencies')->insert([
            [
                'currency_code' => 'USD',
                'currency_name' => 'United States dollar',
                'currency_symbol' => '$',
                'exchange_rate_to_usd' => 1.0000,
                'enabled' => true,
                'flag' => 'path/to/flags/usd.png',
            ],
            [
                'currency_code' => 'EGP',
                'currency_name' => 'Egyptian pound',
                'currency_symbol' => '£',
                'exchange_rate_to_usd' => 0.0322,
                'enabled' => true,
                'flag' => 'path/to/flags/egp.png',
            ],
            [
                'currency_code' => 'AED',
                'currency_name' => 'United Arab Emirates dirham',
                'currency_symbol' => 'د.إ',
                'exchange_rate_to_usd' => 0.2723,
                'enabled' => true,
                'flag' => 'path/to/flags/aed.png',
            ],
            [
                'currency_code' => 'SAR',
                'currency_name' => 'Saudi riyal',
                'currency_symbol' => '﷼',
                'exchange_rate_to_usd' => 0.2666,
                'enabled' => true,
                'flag' => 'path/to/flags/sar.png',
            ],
            [
                'currency_code' => 'EUR',
                'currency_name' => 'Euro',
                'currency_symbol' => '€',
                'exchange_rate_to_usd' => 1.0543,
                'enabled' => true,
                'flag' => 'path/to/flags/eur.png',
            ]
        ]);
    }
}
