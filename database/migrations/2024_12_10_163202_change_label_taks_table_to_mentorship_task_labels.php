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
		Schema::rename('label_taks', 'mentorship_task_labels');
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::rename('mentorship_task_labels', 'label_taks');
	}
};
