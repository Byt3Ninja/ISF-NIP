<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('session_type')
                ->nullable()
                ->default(null)
                ->comment('All,Online,Offline')
                ->change();
       
        });
        DB::table('schedules')
            ->where('session_type', 'Both')
            ->update(['session_type' => 'Online']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentorship_requests', function (Blueprint $table) {
            $table->string('session_type')
                    ->default('Both')
                    ->comment('Both,Online,Offline')
                    ->change();
        });
    }
};
