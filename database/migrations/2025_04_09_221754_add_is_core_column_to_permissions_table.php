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
		if( Schema::hasTable('permissions') )
		{
			Schema::table('permissions', function (Blueprint $table) {
				$table->boolean('is_core')->nullable()->default(false)->comment('core permission will be allowed to any role')->after('guard_name');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('permissions') )
		{
			Schema::table('permissions', function (Blueprint $table) {
				$table->dropColumn('is_core');
			});
		}
	}
};
