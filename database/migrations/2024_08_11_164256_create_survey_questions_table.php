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
		Schema::create('survey_questions', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('survey_id')->nullable();
			$table->unsignedBigInteger('question_id')->nullable();
			$table->unsignedInteger('row_no')->nullable();
			$table->foreign('survey_id')->references('id')->on('surveys')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('question_id')->references('id')->on('questions_bank')->onDelete('cascade')->onUpdate('cascade');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('survey_questions');
	}
};
