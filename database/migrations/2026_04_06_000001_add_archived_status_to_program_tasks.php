<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL requires re-declaring all enum values when modifying
        DB::statement("ALTER TABLE program_tasks MODIFY COLUMN status ENUM('pending','progress','review','completed','archived') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE program_task_assignees MODIFY COLUMN status ENUM('pending','progress','review','completed','archived') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Move any archived rows back to pending before shrinking the enum
        DB::statement("UPDATE program_tasks SET status = 'pending' WHERE status = 'archived'");
        DB::statement("UPDATE program_task_assignees SET status = 'pending' WHERE status = 'archived'");

        DB::statement("ALTER TABLE program_tasks MODIFY COLUMN status ENUM('pending','progress','review','completed') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE program_task_assignees MODIFY COLUMN status ENUM('pending','progress','review','completed') NOT NULL DEFAULT 'pending'");
    }
};
