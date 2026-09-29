<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-startup screening slots (GV-3295) are DERIVED, not stored: each
     * startup's window is computed from the panel's start_time, this interval,
     * and the startup's index within `screenings.users`. Only the interval
     * itself is persisted, so changing the panel start time reflows every slot
     * automatically and existing readers of start_time/end_time keep working.
     *
     * NULL means the panel uses a manually entered end_time (interval OFF),
     * which is also the default for every pre-existing row.
     */
    public function up(): void
    {
        Schema::table('screenings', function (Blueprint $table) {
            $table->unsignedSmallInteger('interval')->nullable()->after('end_time');
        });
    }

    public function down(): void
    {
        Schema::table('screenings', function (Blueprint $table) {
            $table->dropColumn('interval');
        });
    }
};
