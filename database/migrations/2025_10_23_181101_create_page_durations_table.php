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
        Schema::create('page_durations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('cycle_id')->nullable();
            $table->unsignedBigInteger('step_id')->nullable();
            $table->unsignedBigInteger('training_sessions_id')->nullable();
            $table->string('page_url');
            $table->integer('duration_minutes');
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('cycle_id')->references('id')->on('cycles')->onDelete('cascade');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('cascade');
            $table->foreign('training_sessions_id')->references('id')->on('training_sessions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_durations');
    }
};
