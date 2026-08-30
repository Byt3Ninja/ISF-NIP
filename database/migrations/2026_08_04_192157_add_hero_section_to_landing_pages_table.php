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
        Schema::table('landing_pages', function (Blueprint $table) {
            // Hero Section
            $table->string('hero_title', 100)->nullable();
            $table->string('hero_title_ar', 100)->nullable();
            $table->string('hero_title_tag', 150)->nullable();
            $table->string('hero_title_tag_ar', 150)->nullable();
            $table->string('hero_description', 500)->nullable();
            $table->string('hero_description_ar', 500)->nullable();
            $table->string('hero_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn([
                'hero_title',
                'hero_title_ar',
                'hero_title_tag',
                'hero_title_tag_ar',
                'hero_description',
                'hero_description_ar',
                'hero_image',
            ]);
        });
    }
};
