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
            $table->string('Platform_Name')->nullable();
            $table->string('Platform_support_email')->nullable();
            $table->string('Country')->nullable();
            $table->string('State')->nullable();
            $table->string('Default_Language')->nullable();
            $table->text('timezone')->nullable();
            $table->text('Currency')->nullable();
            $table->text('Logo')->nullable();
            $table->text('header')->nullable();
            $table->text('footer')->nullable();
            $table->text('google')->nullable();
            $table->text('host')->nullable();
            $table->text('Mail')->nullable();
            $table->text('Mailpassword')->nullable();
            $table->integer('Port')->nullable();
            $table->text('BroadCast')->nullable();
            $table->text('Secret_Key')->nullable();
            $table->text('Client_ID')->nullable();
            $table->text('Cluster_name')->nullable();
            $table->text('Meeting')->nullable();
            $table->text('GOOGLE_CLIENT_ID')->nullable();
            $table->text('GOOGLE_CLIENT_SECRET')->nullable();
            $table->text('GOOGLE_REDIRECT')->nullable();
            $table->text('MICROSOFT_CLIENT_ID')->nullable();
            $table->text('MICROSOFT_CLIENT_SECRET_VALUE')->nullable();
            $table->text('MICROSOFT_TENANT_ID')->nullable();
            $table->text('MICROSOFT_CLIENT_SECRET_ID')->nullable();
            $table->text('MICROSOFT_APP_EMAIL')->nullable();
            $table->text('MICROSOFT_REDIRECT_URI')->nullable();
            $table->text('ZOOM_CLIENT_ID')->nullable();
            $table->text('ZOOM_CLIENT_SECRET')->nullable();
            $table->text('ZOOM_ACCOUNT_ID')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
};
