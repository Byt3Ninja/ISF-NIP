<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations_settings', function (Blueprint $table) {
            $table->string('smtp_last_error', 500)->nullable()->after('smtp_from_name');
            $table->timestamp('smtp_failed_at')->nullable()->after('smtp_last_error');
        });
    }

    public function down(): void
    {
        Schema::table('organizations_settings', function (Blueprint $table) {
            $table->dropColumn(['smtp_last_error', 'smtp_failed_at']);
        });
    }
};
