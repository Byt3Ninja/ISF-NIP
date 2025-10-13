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
		if( Schema::hasTable('cycles') && !Schema::hasColumn('cycles', 'step_type') )
		{
			Schema::table('cycles', function (Blueprint $table) {
				$table->string('step_type', 25)->nullable()->default('cycle')->after('public_slug')->comment('cycle, time');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('cycles') && Schema::hasColumn('cycles', 'step_type') )
		{
			Schema::table('cycles', function (Blueprint $table) {
				$table->dropColumn('step_type');
			});
		}
	}
};
