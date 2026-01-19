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
            $table->string('meeting_platform')->nullable();
            $table->string('zoom_client_id')->nullable();
            $table->string('zoom_client_secret')->nullable();
            $table->string('zoom_account_id')->nullable();
            $table->text('microsoft_client_id')->nullable();
            $table->text('microsoft_client_secret_value')->nullable();
            $table->text('microsoft_tenant_id')->nullable();
            $table->text('microsoft_client_secret_id')->nullable();
            $table->text('microsoft_app_email')->nullable();
            $table->text('microsoft_redirect_uri')->nullable();
            $table->string('time_zone')->nullable();
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
