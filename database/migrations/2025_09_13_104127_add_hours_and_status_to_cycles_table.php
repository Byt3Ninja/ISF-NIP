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
             $table->integer('hours_Rminder')->default(1)->comment('hours for reminder');
            $table->integer('is_active_Rminder')->default(0)->comment('Active = 1 , Not Active = 0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cycles', function (Blueprint $table) {
            $table->dropColumn(['hours', 'is_active']);
        });
    }
};
