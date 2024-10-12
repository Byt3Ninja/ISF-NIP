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
            $table->integer('whatsapp')->nullable()->after('phone');
            $table->text('info_company')->nullable()->after('address_ar');
            $table->string( 'Currency')->nullable()->after('raised_fund');
            $table->string( 'Pre_money_Valuation')->nullable()->after('Currency');
            $table->string('targeted_market')->nullable()->after('Pre_money_Valuation');
            $table->string('Industries')->nullable()->after('targeted_market');
            $table->string('pitch_deck_video')->nullable()->after('pitch_deck');
            $table->string('pitch_deck_summary')->nullable()->after('pitch_deck_description');
            $table->string('documents')->nullable()->after('pitch_deck_summary');
            $table->text('working_team_summary')->nullable()->after('team_num');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('whatsapp')->nullable()->after('phone');
            $table->dropColumn('info_company')->nullable()->after('address_ar');
            $table->dropColumn('Currency')->nullable()->after('raised_fund');
            $table->dropColumn('Pre_money_Valuation')->nullable()->after('Currency');
            $table->dropColumn('targeted_market')->nullable()->after('Pre_money_Valuation');
            $table->dropColumn('Industries')->nullable()->after('targeted_market');
            $table->dropColumn('pitch_deck_video')->nullable()->after('pitch_deck');
            $table->dropColumn('pitch_deck_summary')->nullable()->after('pitch_deck_description');
            $table->dropColumn('documents')->nullable()->after('pitch_deck_summary');
            $table->dropColumn('working_team_summary')->nullable()->after('team_num');
        });
    }
};
