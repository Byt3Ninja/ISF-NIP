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
		if( Schema::hasTable('questions_bank') )
		{
			Schema::table('questions_bank', function (Blueprint $table) {
				$table->enum('survey_type', ['stars', 'radio'])->nullable()->default(null)->after('type');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('questions_bank') )
		{
			Schema::table('questions_bank', function (Blueprint $table) {
				$table->dropColumn('survey_type');
			});
		}
	}
};
