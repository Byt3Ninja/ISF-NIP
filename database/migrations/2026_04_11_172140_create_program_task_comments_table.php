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
        Schema::create('program_task_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_task_id');
            $table->unsignedBigInteger('user_id');
            $table->mediumText('comment');
            $table->boolean('edited')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('program_task_id')->references('id')->on('program_tasks')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_task_comments');
    }
};
