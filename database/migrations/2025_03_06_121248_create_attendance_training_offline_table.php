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
        Schema::create('attendance_training_offline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('training_session_id')->constrained('training_sessions')->onDelete('cascade');
            $table->foreignId('step_id')->constrained('steps')->onDelete('cascade'); // تأكد من وجود جدول steps
            $table->foreignId('cycle_id')->constrained('cycles')->onDelete('cascade'); // تأكد من وجود جدول cycles
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade'); // تأكد من وجود جدول Companies
            $table->boolean('attendance')->default(0); // 0: غائب - 1: حاضر
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_training_offline');
    }
};
