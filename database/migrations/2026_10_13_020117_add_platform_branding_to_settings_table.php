<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Platform Branding (GV-9674). NULL means "use the theme default",
     * so an unconfigured platform keeps the stock theme colors.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('main_color', 7)->nullable();
            $table->string('gradient_start_color', 7)->nullable();
            $table->string('gradient_end_color', 7)->nullable();
            $table->string('button_text_color', 7)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['main_color', 'gradient_start_color', 'gradient_end_color', 'button_text_color']);
        });
    }
};
