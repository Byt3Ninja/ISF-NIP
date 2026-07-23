<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->string('hero_image')->nullable()->after('img_url');
            $table->string('hero_description', 100)->nullable()->after('hero_image');
            $table->string('services_short_description', 200)->nullable()->after('hero_description');
            $table->string('appointment_link', 500)->nullable()->after('services_short_description');
            $table->string('operating_mode')->nullable()->after('appointment_link');
            $table->string('operating_region')->nullable()->after('operating_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn([
                'hero_image',
                'hero_description',
                'services_short_description',
                'appointment_link',
                'operating_mode',
                'operating_region'
            ]);
        });
    }
};
