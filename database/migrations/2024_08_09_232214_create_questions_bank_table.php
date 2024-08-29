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
		Schema::create('questions_bank', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('organization_id')->nullable();
			$table->string('question', 750)->nullable()->default(null);
			$table->enum('type', ['mcq', 'tfq'])->nullable()->default('mcq');
			$table->boolean('correct')->nullable()->default(true);
			$table->longText('answers')->nullable()->default(null);
			$table->text('explanation')->nullable()->default(null);
			$table->enum('difficulty', ['easy', 'medium', 'hard'])->nullable()->default(null);
			$table->text('tags')->nullable()->default(null);
			$table->text('keywords')->nullable()->default(null);
			$table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade')->onUpdate('cascade');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('questions_bank');
	}
};
