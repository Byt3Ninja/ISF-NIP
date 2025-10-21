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
        Schema::table('session_recordings', function (Blueprint $table) {
            $table->longText('recording_view_url')->nullable()->change();
            $table->longText('recording_download_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_recordings', function (Blueprint $table) {
            //
        });
    }
};
