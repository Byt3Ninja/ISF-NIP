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
		if( Schema::hasTable('screening_evaluation_fields_values') && !Schema::hasColumn('screening_evaluation_fields_values', 'company_id') )
		{
			Schema::table('screening_evaluation_fields_values', function (Blueprint $table) {
				$table->unsignedBigInteger('company_id')->nullable()->default(null)->after('user_id');
				$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('screening_evaluation_fields_values') && Schema::hasColumn('screening_evaluation_fields_values', 'company_id') )
		{
			Schema::table('screening_evaluation_fields_values', function (Blueprint $table) {
				$table->dropForeign(['company_id']);
				$table->dropColumn(['company_id']);
			});
		}
	}
};
