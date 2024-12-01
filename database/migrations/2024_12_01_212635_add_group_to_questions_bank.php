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
            $table->enum('group', ['quiz', 'survey', 'assessment'])->change(); // إضافة العمود بعد عمود id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions_bank', function (Blueprint $table) {
            $table->enum('group', ['quiz', 'survey'])->change();
        });
    }
};
