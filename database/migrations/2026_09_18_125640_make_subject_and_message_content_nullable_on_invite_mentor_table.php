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
        // Custom subject/message are now optional (the mail falls back to the
        // platform's default template) -- raw SQL since doctrine/dbal isn't
        // installed for Schema::table()->change().
        DB::statement('ALTER TABLE invite_mentor MODIFY subject VARCHAR(255) NULL');
        DB::statement('ALTER TABLE invite_mentor MODIFY message_content LONGTEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE invite_mentor MODIFY subject VARCHAR(255) NOT NULL DEFAULT ''");
        DB::statement("ALTER TABLE invite_mentor MODIFY message_content LONGTEXT NOT NULL");
    }
};
