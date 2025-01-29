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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('whatsApp_URL_callback')->nullable();
            $table->string('whatsApp_INSTANCE_ID')->nullable();
            $table->string('whatsApp_ACCESS_TOKEN')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['whatsApp_URL_callback', 'whatsApp_INSTANCE_ID', 'whatsApp_ACCESS_TOKEN']);
        });
    }
};
