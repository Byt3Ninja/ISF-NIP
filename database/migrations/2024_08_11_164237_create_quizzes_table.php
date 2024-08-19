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
		Schema::create('quizzes', function (Blueprint $table) {
			$table->id();
			$table->string('title', 255)->nullable()->default(null);
			$table->unsignedBigInteger('organization_id')->nullable();
			$table->unsignedBigInteger('training_id')->nullable();
			$table->string('description', 255)->nullable()->default(null);
			$table->dateTime('start')->nullable()->default(null);
			$table->dateTime('end')->nullable()->default(null);
			$table->double('time_limit')->nullable()->default(null);
			$table->integer('attempts')->nullable()->default(null);
			$table->string('retry_after', 25)->nullable()->default(null);
			$table->double('passing_criteria')->nullable()->default(null);
			$table->boolean('shuffle')->nullable()->default(null);
			$table->enum('status', ['draft', 'published'])->nullable()->default('draft');
			$table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade')->onUpdate('cascade');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('quizzes');
	}
};
