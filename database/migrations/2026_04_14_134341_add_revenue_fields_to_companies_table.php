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
            if (!Schema::hasColumn('companies', 'generate_revenue')) {
                $table->string('generate_revenue', 3)->nullable()->after('revenue_generating');
            }
            if (!Schema::hasColumn('companies', 'average_annual_revenues')) {
                $table->unsignedBigInteger('average_annual_revenues')->nullable()->after('generate_revenue');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'average_annual_revenues')) {
                $table->dropColumn('average_annual_revenues');
            }
            if (Schema::hasColumn('companies', 'generate_revenue')) {
                $table->dropColumn('generate_revenue');
            }
        });
    }
};
