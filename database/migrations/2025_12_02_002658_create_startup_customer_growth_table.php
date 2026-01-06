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
		if( !Schema::hasTable('startup_customer_growth') )
		{
			Schema::create('startup_customer_growth', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('company_id')->nullable()->default(null);
				$table->string('year', 50)->nullable()->default(null);
				$table->string('month', 50)->nullable()->default(null);
				$table->integer('total_customers')->nullable()->default(0);
				$table->integer('new_customers')->nullable()->default(0);
				$table->string('segment', 50)->nullable()->default(null);
				$table->text('notes')->nullable()->default(null);
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

				$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('startup_customer_growth') )
		{
			Schema::dropIfExists('startup_customer_growth');
		}
	}
};
