<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the new Arabic column first
        Schema::table('static_categories', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('id');
        });

        // Rename 'name' to 'name_en' using raw SQL
        DB::statement('ALTER TABLE static_categories CHANGE `name` `name_en` VARCHAR(255)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename 'name_en' back to 'name'
        DB::statement('ALTER TABLE static_categories CHANGE `name_en` `name` VARCHAR(255)');

        // Drop the Arabic column
        Schema::table('static_categories', function (Blueprint $table) {
            $table->dropColumn('name_ar');
        });
    }
};
