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
		Schema::create('program_task_activities', function (Blueprint $table) {
			$table->id();
			$table->foreignId('program_task_id')->nullable()->default(null)->constrained('program_tasks', 'id')->onDelete('cascade')->onUpdate('cascade');
			$table->foreignId('user_id')->nullable()->default(null)->constrained('users', 'id')->onDelete('cascade')->onUpdate('cascade');
			$table->enum('action', ['create', 'update', 'delete', 'comment', 'move', 'feedback'])->nullable()->default(null);
			$table->string('column', 255)->nullable()->default(null);
			$table->text('was')->nullable()->default(null);
			$table->text('new')->nullable()->default(null);
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('program_task_activities');
	}
};
