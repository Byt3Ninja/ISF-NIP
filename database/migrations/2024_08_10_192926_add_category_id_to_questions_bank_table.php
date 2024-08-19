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
				$table->unsignedBigInteger('category_id')->nullable()->default(null)->after('organization_id');
				$table->foreign('category_id')->references('id')->on('question_categories')->onDelete('cascade')->onUpdate('cascade');
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
				$table->dropForeign(['category_id']);
				$table->dropColumn('category_id');
			});
		}
	}
};
