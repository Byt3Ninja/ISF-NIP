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
        if( Schema::hasTable('cycles') )
        {
            if( !Schema::hasColumn('cycles', 'winners_visibility') )
            {
                Schema::table('cycles', function (Blueprint $table) {
                    $table->enum('winners_visibility', ['public', 'private'])->nullable()->default('private')->after('winners_announcement');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if( Schema::hasTable('cycles') )
        {
            if( Schema::hasColumn('cycles', 'winners_visibility') )
            {
                Schema::table('cycles', function (Blueprint $table) {
                    $table->dropColumn(['winners_visibility']);
                });
            }
        }
    }
};
