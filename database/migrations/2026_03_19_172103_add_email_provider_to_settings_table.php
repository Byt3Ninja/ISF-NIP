<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('email_provider', 10)->nullable()->default('smtp')->after('encryption');
            $table->string('resend_api_key', 64)->nullable()->after('email_provider');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['email_provider', 'resend_api_key']);
        });
    }
};
