<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * GV-1513 / GV-1514: the email provider is now "smtp" or "resend" only.
 * Gmail becomes an SMTP preset (smtp_preset = gmail) next to the Resend SMTP
 * preset and a custom server. Resend (API) keeps its own sending domain and
 * sender, and the moment the test email succeeded; it only counts as connected
 * after that.
 */
return new class extends Migration
{
    private const TABLES = [
        'settings'               => 'host',
        'organizations_settings' => 'smtp_host',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table => $hostColumn) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('smtp_preset', 10)->nullable()->after('email_provider');
                $table->string('resend_domain', 255)->nullable()->after('resend_api_key');
                $table->string('resend_from_address', 191)->nullable()->after('resend_domain');
                $table->string('resend_from_name', 191)->nullable()->after('resend_from_address');
                $table->timestamp('resend_connected_at')->nullable()->after('resend_from_name');
            });

            DB::table($table)->where('email_provider', 'gmail')
                ->update(['email_provider' => 'smtp', 'smtp_preset' => 'gmail']);

            DB::table($table)->where('email_provider', 'smtp')->whereNull('smtp_preset')
                ->where($hostColumn, 'smtp.gmail.com')->update(['smtp_preset' => 'gmail']);

            DB::table($table)->where('email_provider', 'smtp')->whereNull('smtp_preset')
                ->where($hostColumn, 'smtp.resend.com')->update(['smtp_preset' => 'resend']);

            DB::table($table)->where('email_provider', 'smtp')->whereNull('smtp_preset')
                ->whereNotNull($hostColumn)->where($hostColumn, '!=', '')
                ->update(['smtp_preset' => 'custom']);
        }
    }

    public function down(): void
    {
        foreach (array_keys(self::TABLES) as $table) {
            DB::table($table)->where('email_provider', 'smtp')->where('smtp_preset', 'gmail')
                ->update(['email_provider' => 'gmail']);

            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['smtp_preset', 'resend_domain', 'resend_from_address', 'resend_from_name', 'resend_connected_at']);
            });
        }
    }
};
