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
        Schema::table('users_bio', function (Blueprint $table) {
            $table->boolean('entrepreneurial_experience')->default(false);
            $table->mediumText('entrepreneurial_experience_brief')->nullable();
            $table->boolean('profile_privacy')->default(true);
            $table->text('mentorship_approach')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_bio', function (Blueprint $table) {
            $table->dropColumn(['entrepreneurial_experience','entrepreneurial_experience_brief','profile_privacy','mentorship_approach']);
        });
    }
};
