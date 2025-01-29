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
        Schema::create('swot_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->text('strength')->nullable();
            $table->text('weakness')->nullable();
            $table->text('opportunity')->nullable();
            $table->text('threat')->nullable();
            $table->text('srecommendation')->nullable();
            $table->text('wrecommendation')->nullable();
            $table->text('orecommendation')->nullable();
            $table->text('trecommendation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swot_analyses');
    }
};
