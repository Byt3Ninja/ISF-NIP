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
        Schema::table('organization_landing_pages', function (Blueprint $table) {
            // Hero section background
            $table->string('hero_bg_type', 20)->default('solid')->after('landing_theme'); // solid, gradient
            $table->string('hero_bg_color', 20)->nullable()->after('hero_bg_type');
            $table->string('hero_bg_gradient_start', 20)->nullable()->after('hero_bg_color');
            $table->string('hero_bg_gradient_end', 20)->nullable()->after('hero_bg_gradient_start');

            // How It Works section background
            $table->string('how_it_works_bg_type', 20)->default('solid')->after('hero_bg_gradient_end');
            $table->string('how_it_works_bg_color', 20)->nullable()->after('how_it_works_bg_type');
            $table->string('how_it_works_bg_gradient_start', 20)->nullable()->after('how_it_works_bg_color');
            $table->string('how_it_works_bg_gradient_end', 20)->nullable()->after('how_it_works_bg_gradient_start');

            // Achievements section background
            $table->string('achievements_bg_type', 20)->default('solid')->after('how_it_works_bg_gradient_end');
            $table->string('achievements_bg_color', 20)->nullable()->after('achievements_bg_type');
            $table->string('achievements_bg_gradient_start', 20)->nullable()->after('achievements_bg_color');
            $table->string('achievements_bg_gradient_end', 20)->nullable()->after('achievements_bg_gradient_start');

            // Media section background
            $table->string('media_bg_type', 20)->default('solid')->after('achievements_bg_gradient_end');
            $table->string('media_bg_color', 20)->nullable()->after('media_bg_type');
            $table->string('media_bg_gradient_start', 20)->nullable()->after('media_bg_color');
            $table->string('media_bg_gradient_end', 20)->nullable()->after('media_bg_gradient_start');

            // Partners section background
            $table->string('partners_bg_type', 20)->default('solid')->after('media_bg_gradient_end');
            $table->string('partners_bg_color', 20)->nullable()->after('partners_bg_type');
            $table->string('partners_bg_gradient_start', 20)->nullable()->after('partners_bg_color');
            $table->string('partners_bg_gradient_end', 20)->nullable()->after('partners_bg_gradient_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_landing_pages', function (Blueprint $table) {
            $table->dropColumn([
                'hero_bg_type',
                'hero_bg_color',
                'hero_bg_gradient_start',
                'hero_bg_gradient_end',
                'how_it_works_bg_type',
                'how_it_works_bg_color',
                'how_it_works_bg_gradient_start',
                'how_it_works_bg_gradient_end',
                'achievements_bg_type',
                'achievements_bg_color',
                'achievements_bg_gradient_start',
                'achievements_bg_gradient_end',
                'media_bg_type',
                'media_bg_color',
                'media_bg_gradient_start',
                'media_bg_gradient_end',
                'partners_bg_type',
                'partners_bg_color',
                'partners_bg_gradient_start',
                'partners_bg_gradient_end',
            ]);
        });
    }
};
