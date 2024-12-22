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
            $table->string('LINKEDIN_CLIENT_ID')->nullable()->after('GOOGLE_REDIRECT');
            $table->string('LINKEDIN_CLIENT_SECRET')->nullable()->after('LINKEDIN_CLIENT_ID');
            $table->string('LINKEDIN_REDIRECT_URI')->nullable()->after('LINKEDIN_CLIENT_SECRET');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('LINKEDIN_CLIENT_ID');
            $table->dropColumn('LINKEDIN_CLIENT_SECRET');
            $table->dropColumn('LINKEDIN_REDIRECT_URI');
        });
    }
};
