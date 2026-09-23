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
        Schema::create('training_notifications_sent', function (Blueprint $table) {
            $table->id();
            // step_id + session_ref + session_type identify one created training
            // instance; a retry of the queued job for the same instance and
            // company must not re-send.
            $table->unsignedBigInteger('step_id');
            $table->string('session_ref');
            $table->string('session_type');
            $table->unsignedBigInteger('company_id');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['step_id', 'session_ref', 'session_type', 'company_id'], 'training_notif_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_notifications_sent');
    }
};
