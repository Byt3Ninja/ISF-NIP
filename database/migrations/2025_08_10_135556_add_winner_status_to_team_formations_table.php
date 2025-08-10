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
        if( Schema::hasTable('team_formations') )
        {
            if( !Schema::hasColumn('team_formations', 'winner_status') )
            {
                Schema::table('team_formations', function (Blueprint $table) {
                    $table->string('winner_status', 75)->nullable()->default(null)->after('max_teams_members');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if( Schema::hasTable('team_formations') )
        {
            if( Schema::hasColumn('team_formations', 'winner_status') )
            {
                Schema::table('team_formations', function (Blueprint $table) {
                    $table->dropColumn(['winner_status']);
                });
            }
        }
    }
};
