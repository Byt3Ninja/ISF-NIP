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
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('time_zone')->nullable();
            $table->string('currency_id')->nullable();
            $table->string('meeting_platform')->nullable();
            $table->string('zoom_client_id')->nullable();
            $table->string('zoom_client_secret')->nullable();
            $table->string('zoom_account_id')->nullable();
            $table->string('website_url')->nullable();
            $table->string('facebook_page_url')->nullable();
            $table->string('twitter_handle')->nullable();
            $table->string('youtube_channel_url')->nullable();
            $table->string('instagram_profile_url')->nullable();
            $table->string('linkedin_company_url')->nullable();
            $table->string('tiktok_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            //
        });
    }
};
