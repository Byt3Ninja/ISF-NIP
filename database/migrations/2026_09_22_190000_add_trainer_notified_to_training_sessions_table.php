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
        Schema::table('training_sessions', function (Blueprint $table) {
            // The trainer_email the "you've been invited" email was last sent
            // for. Assigning a different trainer bumps this and re-notifies;
            // re-saving the same trainer does not.
            $table->timestamp('trainer_notified_at')->nullable();
            $table->string('trainer_notified_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropColumn(['trainer_notified_at', 'trainer_notified_email']);
        });
    }
};
