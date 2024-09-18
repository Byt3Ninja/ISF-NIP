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
        Schema::table('trainings', function (Blueprint $table) {
            $table->string('code')->nullable();      // وصف التدريب
            $table->string('CoursLevel')->nullable();       // تاريخ بدء التدريب
            $table->integer('Quizs')->nullable();   
            $table->integer('Instractor')->nullable();   
            $table->date('start_date')->nullable();   
            $table->date('end_date')->nullable();
            $table->integer('Status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn(['code', 'CoursLevel', 'Quizs', 'Instractor', 'start_date', 'end_date', 'Status']);
        });
    }
};
