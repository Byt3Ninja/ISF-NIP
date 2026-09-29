<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services_requests', function (Blueprint $table) {
            $table->timestamp('decided_at')->nullable()->after('approved');
        });

        // Backfill decided_at for already-decided requests
        DB::statement("UPDATE services_requests SET decided_at = updated_at WHERE approved IS NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services_requests', function (Blueprint $table) {
            $table->dropColumn('decided_at');
        });
    }
};
