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
            if (!Schema::hasColumn('settings', 'LINKEDIN_LOGIN')) {
                $table->enum('LINKEDIN_LOGIN', ['enabled', 'disabled'])->default('disabled')->after('LINKEDIN_REDIRECT_URI');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'LINKEDIN_LOGIN')) {
                $table->dropColumn('LINKEDIN_LOGIN');
            }
        });
    }
};
