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
        Schema::create('session_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('participant_user_id')->nullable();
            $table->string('participant_email')->nullable();
            $table->string('participant_name')->nullable();
            $table->timestamp('participant_join_date')->nullable();
            $table->timestamp('participant_end_date')->nullable();
            $table->integer('attendance_duration')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('training_sessions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_reports');
    }
};
