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
				$table->string('occupation', 75)->nullable()->default(null)->after('product_or_service');
				$table->string('annual_revenue', 25)->nullable()->default(null)->after('occupation');
				$table->string('equity_offered', 25)->nullable()->default(null)->after('raised_fund');
				$table->text('collaborators')->nullable()->default(null)->after('employees');
				$table->string('business_phase', 5)->nullable()->default(null)->after('business_categories');
				$table->text('hear_about_us')->nullable()->default(null)->after('deleted_at');
				$table->text('product_photo')->nullable()->default(null)->after('email');
				$table->string('product_video', 250)->nullable()->default(null)->after('product_photo');
				
				
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
				$table->dropColumn(['annual_revenue', 'equity_offered', 'collaborators', 'business_phase', 'hear_about_us', 'occupation', 'product_photo', 'product_video']);
			});
		}
	}
};
