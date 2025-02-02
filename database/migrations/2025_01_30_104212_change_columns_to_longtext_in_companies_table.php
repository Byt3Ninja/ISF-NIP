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
            $table->longText('about_problem')->nullable()->change();
            $table->longText('about_market_opportunity')->nullable()->change();
            $table->longText('about_competitors')->nullable()->change();
            $table->longText('about_monetize')->nullable()->change();
            $table->longText('about_challenges')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('about_problem')->change();
            $table->string('about_market_opportunity')->change();
            $table->string('about_competitors')->change();
            $table->string('about_monetize')->change();
            $table->string('about_challenges')->change();
        });
    }
};
