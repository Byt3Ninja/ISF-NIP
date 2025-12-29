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
        // Update existing 'In Review' values to 'Review'
        DB::table('program_tasks')
            ->where('status', 'In Review')
            ->update(['status' => 'Review']);

        DB::table('program_task_assignees')
            ->where('status', 'In Review')
            ->update(['status' => 'Review']);

        // Modify the enum column
        DB::statement("ALTER TABLE program_tasks MODIFY COLUMN status ENUM('Pending', 'In Progress', 'Review', 'Completed') DEFAULT 'Pending'");
        DB::statement("ALTER TABLE program_task_assignees MODIFY COLUMN status ENUM('Pending', 'In Progress', 'Review', 'Completed') DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'Review' values back to 'In Review'
        DB::table('program_tasks')
            ->where('status', 'Review')
            ->update(['status' => 'In Review']);

        DB::table('program_task_assignees')
            ->where('status', 'Review')
            ->update(['status' => 'In Review']);

        // Revert the enum column
        DB::statement("ALTER TABLE program_tasks MODIFY COLUMN status ENUM('Pending', 'In Progress', 'In Review', 'Completed') DEFAULT 'Pending'");
        DB::statement("ALTER TABLE program_task_assignees MODIFY COLUMN status ENUM('Pending', 'In Progress', 'In Review', 'Completed') DEFAULT 'Pending'");
    }
};
