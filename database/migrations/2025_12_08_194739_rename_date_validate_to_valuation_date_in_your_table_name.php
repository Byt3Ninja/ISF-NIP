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
        Schema::table('financial_projections', function (Blueprint $table) {
            $table->renameColumn('date_validate', 'valuation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_projections', function (Blueprint $table) {
            $table->renameColumn('valuation_date', 'date_validate');
        });
    }
};
