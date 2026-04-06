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
        Schema::create('program_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('program_id');
            $table->enum('priority', ['Low', 'Medium', 'High']);
            $table->enum('status', ['pending', 'progress', 'review', 'completed', 'archived'])->default('pending');
            $table->date('due_date')->nullable();
            $table->json('tags')->nullable();
            $table->json('attachments')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('program_id')->references('id')->on('cycles')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('program_task_assignees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_task_id');
            $table->unsignedBigInteger('company_id');
            $table->enum('status', ['pending', 'progress', 'review', 'completed', 'archived'])->default('pending');
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            $table->foreign('program_task_id')->references('id')->on('program_tasks')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            
            $table->unique(['program_task_id', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_task_assignees');
        Schema::dropIfExists('program_tasks');
    }
};
