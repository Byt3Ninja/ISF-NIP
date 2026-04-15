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
        Schema::table('team_members', function (Blueprint $table) {
            $table->date('join_date')->nullable()->after('phone');
            $table->string('founder_type', 20)->nullable()->after('join_date');
            $table->string('working_commitment', 20)->nullable()->after('founder_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn(['join_date', 'founder_type', 'working_commitment']);
        });
    }
};
