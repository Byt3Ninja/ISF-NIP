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
        Schema::table('service_providers', function (Blueprint $table) {
            $table->string('whatsapp')->nullable();
            $table->string('country_code_w_a')->nullable();
            $table->string('preferred_language')->nullable();
            
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('state_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();

            $table->string('address')->nullable();
            $table->string('zip_code')->nullable();
            $table->text('description')->nullable();
            $table->string('img_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp',
                'country_code_w_a',
                'preferred_language',
                'country_id',
                'state_id',
                'city_id',
                'address',
                'zip_code',
                'description',
                'img_url'
            ]);
        });
    }
};
