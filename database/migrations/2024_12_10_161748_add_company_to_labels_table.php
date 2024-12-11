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
		if( Schema::hasTable('labels') )
		{
			Schema::table('labels', function (Blueprint $table) {
				$table->foreignId('company_id')->nullable()->default(null)->after('user_id')->constrained('companies', 'id')->onDelete('cascade')->onUpdate('cascade');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('labels') )
		{
			Schema::table('labels', function (Blueprint $table) {
				$table->dropForeign('labels_company_id_foreign');
				$table->dropColumn(['company_id']);
			});
		}
	}
};
