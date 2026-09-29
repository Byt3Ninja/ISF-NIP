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
            $table->boolean('programs_enabled')->default(false)->after('partners_bg_gradient_end');
            $table->string('programs_title', 255)->nullable()->after('programs_enabled');
            $table->string('programs_subtitle', 255)->nullable()->after('programs_title');
            $table->text('programs_description')->nullable()->after('programs_subtitle');
            $table->string('programs_order_by', 20)->default('newest')->after('programs_description');
            $table->string('programs_bg_type', 10)->nullable()->after('programs_order_by');
            $table->string('programs_bg_color', 20)->nullable()->after('programs_bg_type');
            $table->string('programs_bg_gradient_start', 20)->nullable()->after('programs_bg_color');
            $table->string('programs_bg_gradient_end', 20)->nullable()->after('programs_bg_gradient_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_landing_pages', function (Blueprint $table) {
            $table->dropColumn([
                'programs_enabled',
                'programs_title',
                'programs_subtitle',
                'programs_description',
                'programs_order_by',
                'programs_bg_type',
                'programs_bg_color',
                'programs_bg_gradient_start',
                'programs_bg_gradient_end',
            ]);
        });
    }
};
