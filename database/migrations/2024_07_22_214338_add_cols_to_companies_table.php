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
            $table->string('development_stage')->nullable()->after('institutions');
            $table->string('revenue_generating')->nullable()->after('development_stage');
            $table->string('currently_fundraising')->nullable()->after('revenue_generating');
            $table->string('desired_funding_amount')->nullable()->after('currently_fundraising');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['development_stage','revenue_generating','currently_fundraising','desired_funding_amount']);
        });
    }
};
