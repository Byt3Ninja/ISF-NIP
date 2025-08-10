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
        Schema::table('team_formations', function (Blueprint $table) {
            $table->string('project_name_ar')->nullable();
            $table->string('project_name_en')->nullable();
            $table->text('project_description_ar')->nullable();
            $table->text('project_description_en')->nullable();
            $table->unsignedTinyInteger('target_track')->default(0);
            $table->unsignedTinyInteger('project_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_formations', function (Blueprint $table) {
            $table->dropColumn(['Project_Name', 'Project_Description', 'Target_Track', 'Project_Status']);
        });
    }
};
