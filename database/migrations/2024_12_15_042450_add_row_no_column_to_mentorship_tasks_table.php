<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\MentorshipTask;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if( Schema::hasTable('mentorship_tasks') )
		{
			Schema::table('mentorship_tasks', function (Blueprint $table) {
				$table->integer('row_no')->nullable()->default(0)->after('status');
			});

			$tasks = MentorshipTask::all();
			$_tasks = $tasks->groupBy('mentorship_request_id')->map(function($task) {
				$boards = $task->groupBy('status')->map(function($board) {
					$row_no = 1;
					$board = $board->map(function($task) use (&$row_no) {
						$task->row_no = $row_no++;
						$task->save();
						return $task;
					});
					return $board;
				});
				return $boards;
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('mentorship_tasks') )
		{
			Schema::table('mentorship_tasks', function (Blueprint $table) {
				$table->dropColumn(['row_no']);
			});
		}
	}
};
