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
		if( Schema::hasTable('users') )
		{
			Schema::table('users', function (Blueprint $table) {
				$table->string('age', 15)->nullable()->default(null)->after('gender');
				$table->string('api_token', 60)->nullable()->default(null)->after('password');
				$table->string('guardian', 250)->nullable()->default(null)->after('last_name_ar');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('users') )
		{
			Schema::table('users', function (Blueprint $table) {
				$table->dropColumn(['age', 'api_token', 'guardian']);
			});
		}
	}
};
