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
            // How It Works Section
            $table->boolean('how_is_enabled')->default(true);
            $table->string('how_title', 100)->nullable();
            $table->string('how_title_ar', 100)->nullable();
            $table->string('how_title_tag', 150)->nullable();
            $table->string('how_title_tag_ar', 150)->nullable();
            $table->string('how_description', 500)->nullable();
            $table->string('how_description_ar', 500)->nullable();
            $table->json('how_steps')->nullable(); // [{title, title_ar, description, description_ar, icon}] max 3
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn([
                'how_is_enabled',
                'how_title',
                'how_title_ar',
                'how_title_tag',
                'how_title_tag_ar',
                'how_description',
                'how_description_ar',
                'how_steps',
            ]);
        });
    }
};
