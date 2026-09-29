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
            // About Us Section
            $table->boolean('about_is_enabled')->default(false);
            $table->string('about_title', 100)->nullable();
            $table->string('about_title_ar', 100)->nullable();
            $table->string('about_title_tag', 150)->nullable();
            $table->string('about_title_tag_ar', 150)->nullable();
            $table->string('about_description', 500)->nullable();
            $table->string('about_description_ar', 500)->nullable();
            $table->json('about_cards')->nullable(); // [{icon, title, title_ar, description, description_ar}] max 3
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn([
                'about_is_enabled',
                'about_title',
                'about_title_ar',
                'about_title_tag',
                'about_title_tag_ar',
                'about_description',
                'about_description_ar',
                'about_cards',
            ]);
        });
    }
};
