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
            $table->integer('allow_offline_sessions')->default(0);
            $table->string('default_offline_session_location')->nullable();
            $table->text('map_location')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'allow_offline_sessions',
                'default_offline_session_location',
                'map_location'
            ]);
        });
    }
};
