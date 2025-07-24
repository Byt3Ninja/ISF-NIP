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
        Schema::table('session_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('mentorship_request_id')->nullable()->after('session_id');
            $table->foreign('mentorship_request_id')
                  ->references('id')
                  ->on('mentorship_requests')
                  ->onDelete('set null');
            });

        Schema::table('session_recordings', function (Blueprint $table) {
            $table->unsignedBigInteger('mentorship_request_id')->nullable()->after('session_id');
            $table->foreign('mentorship_request_id')
                  ->references('id')
                  ->on('mentorship_requests')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_reports', function (Blueprint $table) {
            $table->dropForeign(['mentorship_request_id']);
            $table->dropColumn('mentorship_request_id');
        });

        Schema::table('session_recordings', function (Blueprint $table) {
            $table->dropForeign(['mentorship_request_id']);
            $table->dropColumn('mentorship_request_id');
        });
    }
};
