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
        Schema::table('mentorship_schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('cycle_id')->nullable()->after('id');
            $table->foreign('cycle_id')->references('id')->on('cycles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentorship_schedules', function (Blueprint $table) {
            $table->dropForeign(['cycle_id']);
            $table->dropColumn('cycle_id');
        });
    }
};
