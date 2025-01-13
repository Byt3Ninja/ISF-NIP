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
            $table->boolean('allow_non_company')->default(false);
            $table->longText('program_guideline')->nullable();
            $table->string('program_guideline_file')->nullable();
            $table->longText('financial_guideline')->nullable();
            $table->string('financial_guideline_file')->nullable();
            $table->longText('legal_guideline')->nullable();
            $table->string('legal_guideline_file')->nullable();
            $table->string('delegation_file')->nullable();
            $table->decimal('budget_allocation', 15, 2)->nullable();
            $table->tinyText('currency_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cycles', function (Blueprint $table) {
            $table->dropColumn([
                'allow_non_company',
                'program_guideline',
                'program_guideline_file',
                'financial_guideline',
                'financial_guideline_file',
                'legal_guideline',
                'legal_guideline_file',
                'delegation_file',
                'budget_allocation',
                'currency_type',
            ]);
        });
    }
};
