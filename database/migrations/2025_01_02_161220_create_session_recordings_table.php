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
        Schema::create('session_recordings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('recording_type');
            $table->string('recording_id')->unique();
            $table->string('recording_view_url')->nullable();
            $table->string('recording_download_url')->nullable();
            $table->timestamp('recording_time')->nullable();
            $table->timestamps();
            $table->foreign('session_id')->references('id')->on('training_sessions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_recordings');
    }
};
