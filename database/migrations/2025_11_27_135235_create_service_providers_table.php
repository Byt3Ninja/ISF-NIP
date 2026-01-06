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
		if( !Schema::hasTable('service_providers') )
		{
			Schema::create('service_providers', function (Blueprint $table) {
				$table->id();
				$table->string('company_name', 255)->nullable()->default(null);
				$table->string('contact_name', 255)->nullable()->default(null);
				$table->string('email', 255)->nullable()->default(null)->unique();
				$table->string('phone', 50)->nullable()->default(null);
				$table->boolean('status')->nullable()->default(false);
				$table->unsignedBigInteger('user_id')->nullable()->default(null);
				$table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

				$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('service_providers');
	}
};
