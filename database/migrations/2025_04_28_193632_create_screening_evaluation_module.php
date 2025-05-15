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
		if( !Schema::hasTable('screening_evaluation') )
		{
			Schema::create('screening_evaluation', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('step_id')->nullable()->default(null);
				$table->boolean('status')->default(0)->comment('0 = Draft, 1 = Published');
				$table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

				$table->foreign('step_id')->references('id')->on('steps')->onUpdate('cascade')->onDelete('cascade');
			});
		}

		if( !Schema::hasTable('screening_evaluation_criteria') )
		{
			Schema::create('screening_evaluation_criteria', function (Blueprint $table) {
				$table->id();
				$table->string('title', 255)->nullable()->default(null);
				$table->double('weight', 5, 2)->nullable()->default(null);
				$table->string('result', 75)->nullable()->default(null);
				$table->unsignedBigInteger('evaluation_id')->nullable()->default(null);
				$table->unsignedBigInteger('parent_id')->nullable()->default(null);
				$table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
	
				$table->foreign('evaluation_id')->references('id')->on('screening_evaluation')->onUpdate('cascade')->onDelete('cascade');
				$table->foreign('parent_id')->references('id')->on('screening_evaluation_criteria')->onUpdate('cascade')->onDelete('cascade');
			});
		}

		if( !Schema::hasTable('screening_evaluation_results') )
		{
			Schema::create('screening_evaluation_results', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('user_id')->nullable()->default(null);
				$table->unsignedBigInteger('criteria_id')->nullable()->default(null);
				$table->string('score', 10)->nullable()->default(null);
				$table->text('comment')->nullable()->default(null);

				$table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

				$table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
				$table->foreign('criteria_id')->references('id')->on('screening_evaluation_criteria')->onUpdate('cascade')->onDelete('cascade');
			});
		}

		if( !Schema::hasTable('screening_evaluation_custom_fields') )
		{
			Schema::create('screening_evaluation_custom_fields', function (Blueprint $table) {
				$table->id();
				$table->string('name', 255)->nullable()->default(null);
				$table->unsignedBigInteger('evaluation_id')->nullable()->default(null);
				$table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

				$table->foreign('evaluation_id')->references('id')->on('screening_evaluation')->onUpdate('cascade')->onDelete('cascade');
			});
		}

		if( !Schema::hasTable('screening_evaluation_fields_values') )
		{
			Schema::create('screening_evaluation_fields_values', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('custom_field_id')->nullable()->default(null);
				$table->string('value', 255)->nullable()->default(null);
				$table->unsignedBigInteger('user_id')->nullable()->default(null);
				$table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
				$table->foreign('custom_field_id')->references('id')->on('screening_evaluation_custom_fields')->onUpdate('cascade')->onDelete('cascade');
				$table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('screening_evaluation');
		Schema::dropIfExists('screening_evaluation_criteria');
		Schema::dropIfExists('screening_evaluation_results');
		Schema::dropIfExists('screening_evaluation_custom_fields');
		Schema::dropIfExists('screening_evaluation_fields_values');
	}
};
