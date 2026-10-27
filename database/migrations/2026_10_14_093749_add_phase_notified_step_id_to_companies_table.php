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
        Schema::table('companies', function (Blueprint $table) {
            // The step id the "moved to next phase" email was last sent for.
            // Moving to a different phase bumps this and notifies again;
            // re-processing the same move does not.
            $table->unsignedBigInteger('phase_notified_step_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('phase_notified_step_id');
        });
    }
};
