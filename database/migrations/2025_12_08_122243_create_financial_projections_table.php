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
        Schema::create('financial_projections', function (Blueprint $table) {
            $table->id();
            $table->date('date_validate');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->string('revenues')->nullable();
            $table->string('cogs')->nullable();
            $table->string('salaries')->nullable();
            $table->string('other_op_expenses')->nullable();
            $table->string('ebitda')->nullable();
            $table->string('depreciation_amortization')->nullable();
            $table->string('ebit')->nullable();
            $table->string('interest')->nullable();
            $table->string('taxes')->nullable();
            $table->string('net_profit')->nullable();

            $table->string('receivables')->nullable();
            $table->string('inventory')->nullable();
            $table->string('payables')->nullable();
            $table->string('working_capital')->nullable();
            $table->string('change_in_wc')->nullable();
            $table->string('capital_expenditures')->nullable();
            $table->string('debt_end_year')->nullable();
            $table->string('debt_change')->nullable();
            $table->string('free_cash_flow')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_projections');
    }
};
