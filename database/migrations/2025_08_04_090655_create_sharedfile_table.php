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
        Schema::create('shared_files', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('team_formation_id');
            $table->unsignedBigInteger('team_member_id');
            $table->string('file'); 
            $table->text('description')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cycle_id');
            $table->unsignedBigInteger('step_id');
            $table->timestamps();
            $table->foreign('team_formation_id')
                  ->references('id')->on('team_formations')
                  ->cascadeOnDelete();
            $table->foreign('team_member_id')
                  ->references('id')->on('team_members')
                  ->cascadeOnDelete();
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
            $table->foreign('cycle_id')
                  ->references('id')->on('cycles')
                  ->cascadeOnDelete();
            $table->foreign('step_id')
                  ->references('id')->on('steps')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_files');
    }
};
