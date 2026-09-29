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
            $table->boolean('programs_is_enabled')->default(true);
            $table->string('programs_title', 50)->nullable();
            $table->string('programs_title_ar', 50)->nullable();
            $table->string('programs_title_tag', 50)->nullable();
            $table->string('programs_title_tag_ar', 50)->nullable();
            $table->string('programs_description', 100)->nullable();
            $table->string('programs_description_ar', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn([
                'programs_is_enabled',
                'programs_title',
                'programs_title_ar',
                'programs_title_tag',
                'programs_title_tag_ar',
                'programs_description',
                'programs_description_ar',
            ]);
        });
    }
};
