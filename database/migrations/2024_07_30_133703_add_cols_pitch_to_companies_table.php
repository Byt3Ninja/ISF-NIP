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
            $table->string('business_model')->after('email')->nullable();
            $table->string('business_model_description')->after('business_model')->nullable();
            $table->string('business_plan')->after('business_model_description')->nullable();
            $table->string('business_plan_description')->after('business_plan')->nullable();
            $table->string('pitch_deck')->after('business_plan_description')->nullable();
            $table->string('pitch_deck_description')->after('pitch_deck')->nullable();
            $table->string('financial_plan')->after('pitch_deck_description')->nullable();
            $table->string('financial_plan_description')->after('financial_plan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['business_model','business_model_description',
                'business_plan','business_plan_description',
                'pitch_deck','pitch_deck_description',
                'financial_plan','financial_plan_description'
            ]);
        });
    }
};
