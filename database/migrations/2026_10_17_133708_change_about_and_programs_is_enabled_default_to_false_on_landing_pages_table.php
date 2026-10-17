<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * About Us and Programs sections should be off by default on new landing pages.
     */
    public function up(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->boolean('about_is_enabled')->default(false)->change();
            $table->boolean('programs_is_enabled')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->boolean('about_is_enabled')->default(true)->change();
            $table->boolean('programs_is_enabled')->default(true)->change();
        });
    }
};
