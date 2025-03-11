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
		if( Schema::hasTable('services') )
		{
			Schema::table('services', function (Blueprint $table) {
				$table->foreignId('organization_id')->nullable()->default(null)->after('id')->constrained('organizations', 'id')->onDelete('cascade')->onUpdate('cascade');
			});
		}
		if( Schema::hasTable('service_categories') )
		{
			Schema::table('service_categories', function (Blueprint $table) {
				$table->foreignId('organization_id')->nullable()->default(null)->after('id')->constrained('organizations', 'id')->onDelete('cascade')->onUpdate('cascade');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('services') )
		{
			Schema::table('services', function (Blueprint $table) {
				$table->dropForeign('services_organization_id_foreign');
				$table->dropColumn(['organization_id']);
			});
		}
		if( Schema::hasTable('service_categories') )
		{
			Schema::table('service_categories', function (Blueprint $table) {
				$table->dropForeign('service_categories_organization_id_foreign');
				$table->dropColumn(['organization_id']);
			});
		}
	}
};
