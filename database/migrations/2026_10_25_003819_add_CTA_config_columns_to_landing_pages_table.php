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
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->string('hero_cta_text', 50)->nullable()->default(null)->after('hero_description_ar');
            $table->string('hero_cta_text_ar', 50)->nullable()->default(null)->after('hero_cta_text');
            $table->string('hero_cta_link', 255)->nullable()->default(null)->after('hero_cta_text_ar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn([
                'hero_cta_text',
                'hero_cta_text_ar',
                'hero_cta_link',
            ]);
        });
    }
};
