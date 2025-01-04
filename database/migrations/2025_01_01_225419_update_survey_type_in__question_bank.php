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
        Schema::table('questions_bank', function (Blueprint $table) {
            $table->enum('survey_type', ['stars', 'radio', 'answers'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions_bank', function (Blueprint $table) {
            $table->enum('survey_type', ['stars', 'radio'])->nullable()->change();
        });
    }
};
