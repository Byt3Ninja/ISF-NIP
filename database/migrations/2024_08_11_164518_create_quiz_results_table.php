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
		Schema::create('quiz_results', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('quiz_id')->nullable();
			$table->unsignedBigInteger('user_id')->nullable();
			$table->double('score')->nullable()->default(null);
			$table->dateTime('started_at')->nullable()->default(null);
			$table->dateTime('ended_at')->nullable()->default(null);
			$table->boolean('status')->nullable()->default(null);
			$table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade')->onUpdate('cascade');
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
		Schema::dropIfExists('quiz_results');
	}
};
