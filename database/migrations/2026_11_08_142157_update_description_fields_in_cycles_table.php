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
        Schema::table('cycles', function (Blueprint $table) {
            $table->string('description', 750)->nullable()->default(null)->change();
            $table->string('description_ar', 750)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cycles', function (Blueprint $table) {
            $table->string('description', 250)->nullable()->default(null)->change();
            $table->string('description_ar', 250)->nullable()->default(null)->change();
        });
    }
};
