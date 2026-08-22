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
        Schema::create('landing_page_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained()->onDelete('cascade');
            $table->foreignId('cycle_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['landing_page_id', 'cycle_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_page_cycles');
    }
};
