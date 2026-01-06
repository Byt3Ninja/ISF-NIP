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
        Schema::create('organization_custom_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('title', 255);
            $table->longText('content');
            $table->enum('visibility', ['public', 'participants', 'admins_only'])->default('public');
            $table->enum('bg_type', ['solid', 'gradient'])->default('solid');
            $table->string('bg_color', 7)->nullable();
            $table->string('bg_gradient_start', 7)->nullable();
            $table->string('bg_gradient_end', 7)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_custom_sections');
    }
};
