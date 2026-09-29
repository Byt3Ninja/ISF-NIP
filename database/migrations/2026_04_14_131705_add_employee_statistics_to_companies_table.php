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
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedInteger('total_employees')->nullable()->after('employees');
            $table->unsignedInteger('male_employees')->nullable()->after('total_employees');
            $table->unsignedInteger('female_employees')->nullable()->after('male_employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['total_employees', 'male_employees', 'female_employees']);
        });
    }
};
