<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations_settings', function (Blueprint $table) {
            $table->string('email_provider', 10)->nullable()->after('organization_id');
            $table->string('resend_api_key', 64)->nullable()->after('email_provider');
        });
    }

    public function down(): void
    {
        Schema::table('organizations_settings', function (Blueprint $table) {
            $table->dropColumn(['email_provider', 'resend_api_key']);
        });
    }
};
