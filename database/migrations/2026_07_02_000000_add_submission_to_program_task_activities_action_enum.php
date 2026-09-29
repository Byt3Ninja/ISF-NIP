<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE program_task_activities MODIFY COLUMN action ENUM('create','update','delete','comment','move','feedback','submission') NULL DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE program_task_activities MODIFY COLUMN action ENUM('create','update','delete','comment','move','feedback') NULL DEFAULT NULL");
    }
};
