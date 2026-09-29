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
        Schema::table('mentorship_requests', function (Blueprint $table) {
            // Stores the updated_at value that was last notified to the mentor,
            // so a reschedule only re-emails when the row has actually changed
            // again since -- unlike the one-shot *_notified_at columns, this
            // event (session updated) can legitimately repeat on the same row.
            $table->timestamp('mentor_update_notified_at')->nullable()->after('approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentorship_requests', function (Blueprint $table) {
            $table->dropColumn('mentor_update_notified_at');
        });
    }
};
