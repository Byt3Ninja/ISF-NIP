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
		Schema::create('cycle_custom_pages', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('cycle_id')->nullable()->default(null);
			$table->string('title', 250)->nullable()->default(null);
			$table->text('content')->nullable()->default(null);
			$table->boolean('is_active')->default(true)->nullable();
			$table->string('visibility', 15)->nullable()->default(null);
			$table->integer('row_no')->default(0);
			$table->foreign('cycle_id')->references('id')->on('cycles')->onDelete('cascade');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('cycle_custom_pages');
	}
};
