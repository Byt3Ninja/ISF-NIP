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
		if( Schema::hasTable('screening_evaluation_fields_values') && !Schema::hasColumn('screening_evaluation_fields_values', 'is_approved') )
		{
			Schema::table('screening_evaluation_fields_values', function (Blueprint $table) {
				$table->boolean('is_approved')->default(null)->nullable()->after('value');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('screening_evaluation_fields_values') && Schema::hasColumn('screening_evaluation_fields_values', 'is_approved') )
		{
			Schema::table('screening_evaluation_fields_values', function (Blueprint $table) {
				$table->dropColumn('is_approved');
			});
		}
	}
};
