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
            $table->text('microsoft_client_id')->nullable();
            $table->text('microsoft_client_secret_value')->nullable();
            $table->text('microsoft_tenant_id')->nullable();
            $table->text('microsoft_client_secret_id')->nullable();
            $table->text('microsoft_app_email')->nullable();
            $table->text('microsoft_redirect_uri')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'microsoft_client_id',
                'microsoft_client_secret_value',
                'microsoft_tenant_id',
                'microsoft_client_secret_id',
                'microsoft_app_email',
                'microsoft_redirect_uri'
            ]);
        });
    }
};
