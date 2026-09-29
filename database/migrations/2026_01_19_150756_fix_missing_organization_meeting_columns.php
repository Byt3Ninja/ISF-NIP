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
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'meeting_platform')) {
                $table->string('meeting_platform')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'zoom_client_id')) {
                $table->string('zoom_client_id')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'zoom_client_secret')) {
                $table->string('zoom_client_secret')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'zoom_account_id')) {
                $table->string('zoom_account_id')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'microsoft_client_id')) {
                $table->text('microsoft_client_id')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'microsoft_client_secret_value')) {
                $table->text('microsoft_client_secret_value')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'microsoft_tenant_id')) {
                $table->text('microsoft_tenant_id')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'microsoft_client_secret_id')) {
                $table->text('microsoft_client_secret_id')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'microsoft_app_email')) {
                $table->text('microsoft_app_email')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'microsoft_redirect_uri')) {
                $table->text('microsoft_redirect_uri')->nullable();
            }
            if (!Schema::hasColumn('organizations', 'time_zone')) {
                $table->string('time_zone')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'meeting_platform',
                'zoom_client_id',
                'zoom_client_secret',
                'zoom_account_id',
                'microsoft_client_id',
                'microsoft_client_secret_value',
                'microsoft_tenant_id',
                'microsoft_client_secret_id',
                'microsoft_app_email',
                'microsoft_redirect_uri',
                'time_zone'
            ]);
        });
    }
};
