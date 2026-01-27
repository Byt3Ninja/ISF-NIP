<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Truncate the program_tasks table
        DB::table('program_tasks')->truncate();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Truncate operation cannot be rolled back
        // This migration is one-way only
        // You would need to restore from backup if needed
    }
};
