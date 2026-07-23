<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_task_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->default(null)->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('program_task_activities', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }
};
