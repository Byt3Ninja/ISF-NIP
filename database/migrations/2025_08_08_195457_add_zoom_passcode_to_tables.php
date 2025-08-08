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
        if( Schema::hasTable('training_sessions') && !Schema::hasColumn('training_sessions', 'zoom_passcode') ) {
            Schema::table('training_sessions', function (Blueprint $table) {
                $table->string('zoom_passcode', 25)->nullable()->after('zoom_id')->default(null);
            });
        }

        if( Schema::hasTable('finalscreenings') && !Schema::hasColumn('finalscreenings', 'zoom_passcode') ) {
            Schema::table('finalscreenings', function (Blueprint $table) {
                $table->string('zoom_passcode', 25)->nullable()->after('zoom_id')->default(null);
            });
        }

        if( Schema::hasTable('mentorship_requests') && !Schema::hasColumn('mentorship_requests', 'zoom_passcode') ) {
            Schema::table('mentorship_requests', function (Blueprint $table) {
                $table->string('zoom_passcode', 25)->nullable()->after('zoom_id')->default(null);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if( Schema::hasTable('training_sessions') && Schema::hasColumn('training_sessions', 'zoom_passcode') )
        {
            Schema::table('training_sessions', function (Blueprint $table) {
                $table->dropColumn(['zoom_passcode']);
            });
        }

        if( Schema::hasTable('finalscreenings') && Schema::hasColumn('finalscreenings', 'zoom_passcode') )
        {
            Schema::table('finalscreenings', function (Blueprint $table) {
                $table->dropColumn(['zoom_passcode']);
            });
        }

        if( Schema::hasTable('mentorship_requests') && Schema::hasColumn('mentorship_requests', 'zoom_passcode') )
        {
            Schema::table('mentorship_requests', function (Blueprint $table) {
                $table->dropColumn(['zoom_passcode']);
            });
        }
    }
};
