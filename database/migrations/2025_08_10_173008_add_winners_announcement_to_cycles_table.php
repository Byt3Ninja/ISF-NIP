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
            if( !Schema::hasColumn('cycles', 'winners_announcement') )
            {
                Schema::table('cycles', function (Blueprint $table) {
                    $table->boolean('winners_announcement')->nullable()->default(false)->after('organization_id');
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
            if( Schema::hasColumn('cycles', 'winners_announcement') )
            {
                Schema::table('cycles', function (Blueprint $table) {
                    $table->dropColumn(['winners_announcement']);
                });
            }
        }
    }
};
