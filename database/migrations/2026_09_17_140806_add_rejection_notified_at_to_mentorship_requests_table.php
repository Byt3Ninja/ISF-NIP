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
            // Guards the "founder notified of decline" email against being
            // sent more than once for the same decline action.
            $table->timestamp('rejection_notified_at')->nullable()->after('approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentorship_requests', function (Blueprint $table) {
            $table->dropColumn('rejection_notified_at');
        });
    }
};
