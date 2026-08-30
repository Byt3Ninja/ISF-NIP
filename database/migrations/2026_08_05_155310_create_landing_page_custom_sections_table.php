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
        Schema::create('landing_page_custom_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained()->onDelete('cascade');
            $table->string('title', 255)->nullable();
            $table->string('title_ar', 255)->nullable();
            $table->string('title_tag', 255)->nullable();
            $table->string('title_tag_ar', 255)->nullable();
            $table->longText('content')->nullable();
            $table->longText('content_ar')->nullable();
            $table->boolean('visibility')->default(true);
            $table->string('button_text', 255)->nullable();
            $table->string('button_link', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_page_custom_sections');
    }
};
