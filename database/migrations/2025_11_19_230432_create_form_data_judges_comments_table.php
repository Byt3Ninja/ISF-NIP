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
		if( !Schema::hasTable('form_data_judges_comments') )
		{
			Schema::create('form_data_judges_comments', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('form_data_id')->nullable()->default(null);
				$table->foreign('form_data_id')->references('id')->on('form_data')->onDelete('cascade')->onUpdate('cascade');
				$table->unsignedBigInteger('user_id')->nullable()->default(null);
				$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
				$table->text('comment')->nullable()->default(null);
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('form_data_judges_comments');
	}
};
