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
		if( Schema::hasTable('service_provider_registration') )
		{
			if( !Schema::hasColumn('service_provider_registration', 'country_code') )
			{
				Schema::table('service_provider_registration', function (Blueprint $table) {
					$table->string('country_code', 25)->nullable()->default(null)->after('email');
				});
			}
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('service_provider_registration', function (Blueprint $table) {
			if( Schema::hasColumn('service_provider_registration', 'country_code') )
			{
				$table->dropColumn('country_code');
			}
		});
	}
};
