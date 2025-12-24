<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: Many contact/social fields already exist in the table:
     * - contact_email, contact_phone, address (contact info)
     * - facebook_page_url, twitter_handle, youtube_channel_url,
     *   instagram_profile_url, linkedin_company_url, tiktok_url (social media)
     *
     * This migration only adds the missing columns.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Add missing contact fields
            $table->string('country_code', 10)->nullable()->after('contact_email');
            $table->unsignedBigInteger('country_id')->nullable()->after('address');
            $table->unsignedBigInteger('state_id')->nullable()->after('country_id');
            $table->unsignedBigInteger('city_id')->nullable()->after('state_id');
            $table->string('google_map_url', 500)->nullable()->after('city_id');

            // Foreign keys
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
            $table->foreign('state_id')->references('id')->on('states')->nullOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);

            // Drop columns
            $table->dropColumn([
                'country_code',
                'country_id',
                'state_id',
                'city_id',
                'google_map_url',
            ]);
        });
    }
};
