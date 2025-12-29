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
            $table->enum('landing_theme', ['gradient', 'minimal', 'vuexy'])->default('gradient')->after('partners_list');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_landing_pages', function (Blueprint $table) {
            $table->dropColumn('landing_theme');
        });
    }
};
