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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('about_problem')->nullable();
            $table->string('about_market_opportunity')->nullable();
            $table->string('about_competitors')->nullable();
            $table->string('about_monetize')->nullable();
            $table->string('about_challenges')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['about_problem','about_market_opportunity','about_competitors','about_monetize','about_challenges']);
        });
    }
};
