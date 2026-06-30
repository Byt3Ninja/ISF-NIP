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
        Schema::table('startup_customer_growth', function (Blueprint $table) {
            $table->bigInteger('total_customers')->nullable()->default(0)->change();
            $table->bigInteger('new_customers')->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('startup_customer_growth', function (Blueprint $table) {
            $table->integer('total_customers')->nullable()->default(0)->change();
            $table->integer('new_customers')->nullable()->default(0)->change();
        });
    }
};
