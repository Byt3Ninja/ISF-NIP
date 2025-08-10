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
            $table->string('acceptInvition')->nullable()->after('user_id');
            $table->tinyInteger('isTeamLeader')->default(0)->after('acceptInvition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn('acceptInvition');
            $table->dropColumn('isTeamLeader');
        });
    }
};
