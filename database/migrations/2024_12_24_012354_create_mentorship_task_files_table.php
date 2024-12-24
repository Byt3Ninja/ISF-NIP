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
		Schema::create('mentorship_task_files', function (Blueprint $table) {
			$table->id();
			$table->bigInteger('mentorship_request_id')->unsigned()->nullable()->nullable()->default(null);
			$table->bigInteger('mentorship_task_id')->unsigned()->nullable()->default(null);
			$table->bigInteger('user_id')->unsigned()->nullable()->default(null);
			$table->text('file_name')->nullable()->default(null);
			$table->text('file')->nullable()->default(null);
			$table->foreign('mentorship_request_id')->references('id')->on('mentorship_requests')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('mentorship_task_id')->references('id')->on('mentorship_tasks')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('mentorship_task_files');
	}
};
