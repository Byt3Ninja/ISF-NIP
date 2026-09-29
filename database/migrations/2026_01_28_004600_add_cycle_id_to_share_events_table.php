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
        Schema::table('share_events', function (Blueprint $table) {
            // Make team_formation_id nullable for program-level shares
            $table->foreignId('team_formation_id')->nullable()->change();

            // Add cycle_id for program-level share tracking
            $table->foreignId('cycle_id')->nullable()->after('team_formation_id')->constrained()->onDelete('cascade');

            // Add index for cycle_id queries
            $table->index('cycle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('share_events', function (Blueprint $table) {
            $table->dropForeign(['cycle_id']);
            $table->dropIndex(['cycle_id']);
            $table->dropColumn('cycle_id');

            // Revert team_formation_id to non-nullable (only if no null values exist)
            $table->foreignId('team_formation_id')->nullable(false)->change();
        });
    }
};
