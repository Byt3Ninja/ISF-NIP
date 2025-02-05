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
		if( Schema::hasTable('companies') )
		{
			Schema::table('companies', function (Blueprint $table) {
				$table->unsignedInteger('Country_Register_id')->nullable()->default(0)->comment('الدولة التي تم التسجيل فيها')->change();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('companies') )
		{
			Schema::table('companies', function (Blueprint $table) {
				$table->unsignedInteger('Country_Register_id')->default(0)->comment('الدولة التي تم التسجيل فيها')->change();
			});
		}
	}
};
