<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Translate non-Arabic name_ar values in countries, states, and cities.
     */
    public function up(): void
    {
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TranslateArabicNamesSeeder',
            '--force' => true,
        ]);
    }

    /**
     * No rollback — translations are a one-way data correction.
     */
    public function down(): void
    {
        // No rollback needed
    }
};
