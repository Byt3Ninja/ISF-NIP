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
		Schema::create('quiz_responses', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('result_id')->nullable();
			$table->unsignedBigInteger('quiz_id')->nullable();
			$table->unsignedBigInteger('question_id')->nullable();
			$table->unsignedBigInteger('user_id')->nullable();
			$table->string('response', 255)->nullable()->default(null);
			$table->boolean('status')->nullable()->default(null);
			$table->foreign('result_id')->references('id')->on('quiz_results')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('question_id')->references('id')->on('questions_bank')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('quiz_responses');
	}
};
