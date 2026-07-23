<?php

namespace Database\Factories;

use App\Settings;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingsFactory extends Factory
{
    protected $model = Settings::class;

    public function definition(): array
    {
        return [
            'type' => 'general',
            'title' => 'General Settings',
            'ar_title' => 'الإعدادات العامة',
            'VFEmail' => 0,
            'BroadCast' => 0,
        ];
    }
}
