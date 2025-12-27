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
        Schema::create('organization_landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');

            // How It Works Section
            $table->boolean('how_it_works_enabled')->default(false);
            $table->string('how_it_works_title', 255)->nullable();
            $table->string('how_it_works_subtitle', 255)->nullable();
            $table->text('how_it_works_description')->nullable();
            $table->json('process_steps')->nullable(); // [{title, description, icon}] max 4

            // Achievements / Impact Metrics Section
            $table->boolean('achievements_enabled')->default(false);
            $table->string('achievements_title', 255)->nullable();
            $table->string('achievements_subtitle', 255)->nullable();
            $table->text('achievements_description')->nullable();
            $table->json('metrics')->nullable(); // [{number, label, icon}] max 8

            // Media & Recognition Section
            $table->boolean('media_enabled')->default(false);
            $table->string('media_title', 255)->nullable();
            $table->string('media_subtitle', 255)->nullable();
            $table->text('media_description')->nullable();
            $table->json('articles')->nullable(); // [{publication_name, date, title, summary, link, image}] max 8

            // Partners Section
            $table->boolean('partners_enabled')->default(false);
            $table->string('partners_title', 255)->nullable();
            $table->string('partners_subtitle', 255)->nullable();
            $table->text('partners_description')->nullable();
            $table->json('partners_list')->nullable(); // [{name, logo}] max 20

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_landing_pages');
    }
};
