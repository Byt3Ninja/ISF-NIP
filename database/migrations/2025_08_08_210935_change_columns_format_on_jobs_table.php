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
        if( Schema::hasTable('jobs') )
        {
            Schema::table('jobs', function (Blueprint $table) {
                if( Schema::hasColumn('jobs', 'reserved_at') ) { $table->string('reserved_at', 75)->nullable()->default(null)->change(); }
                if( Schema::hasColumn('jobs', 'available_at') ) { $table->string('available_at', 75)->nullable()->default(null)->change(); }
                if( Schema::hasColumn('jobs', 'created_at') ) { $table->string('created_at', 75)->nullable()->default(DB::raw('CURRENT_TIMESTAMP'))->change(); }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if( Schema::hasColumn('jobs', 'reserved_at') ) { $table->unsignedInteger('reserved_at')->nullable()->change(); }
            if( Schema::hasColumn('jobs', 'available_at') ) { $table->unsignedInteger('available_at')->nullable()->change(); }
            if( Schema::hasColumn('jobs', 'created_at') ) { $table->unsignedInteger('created_at')->change(); }
        });
    }
};
