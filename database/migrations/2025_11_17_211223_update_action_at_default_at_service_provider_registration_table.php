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
			if( !Schema::hasColumn('service_provider_registration', 'action_at') )
			{
				Schema::table('service_provider_registration', function (Blueprint $table) {
					$table->timestamp('action_at')->nullable()->default(null)->change();
				});
			}
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('service_provider_registration') )
		{
			if( Schema::hasColumn('service_provider_registration', 'action_at') )
			{
				Schema::table('service_provider_registration', function (Blueprint $table) {
					$table->timestamp('action_at')->nullable()->default(null)->change();
				});
			}
		}
	}
};
