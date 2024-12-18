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
		if( Schema::hasTable('mentorship_tasks') )
		{
			Schema::table('mentorship_tasks', function (Blueprint $table) {
				$table->enum('priority', ['low', 'medium', 'high'])->nullable()->default(null)->after('status');
				$table->foreignId('reported_by')->nullable()->default(null)->after('reply')->constrained('users', 'id')->onDelete('cascade')->onUpdate('cascade');
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
				$table->dropForeign('mentorship_tasks_reported_by_foreign');
				$table->dropColumn(['reported_by', 'priority']);
			});
		}
	}
};
