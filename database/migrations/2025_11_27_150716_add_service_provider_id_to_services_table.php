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
		if( Schema::hasTable('services') && !Schema::hasColumn('services', 'service_provider_id') )
		{
			Schema::table('services', function (Blueprint $table) {
				$table->unsignedBigInteger('service_provider_id')->nullable()->default(null)->after('service_category_id');
				$table->foreign('service_provider_id')->references('id')->on('service_providers')->onDelete('cascade')->onUpdate('cascade');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('services') && Schema::hasColumn('services', 'service_provider_id') )
		{
			Schema::table('services', function (Blueprint $table) {
				$table->dropForeign(['service_provider_id']);
				$table->dropColumn(['service_provider_id']);
			});
		}
	}
};
