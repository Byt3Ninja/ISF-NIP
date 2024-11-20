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
		Schema::create('survey_responses', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id')->nullable();
			$table->unsignedBigInteger('survey_id')->nullable();
			$table->unsignedBigInteger('cycle_id')->nullable();
			$table->unsignedBigInteger('scope_id')->nullable();
			$table->string('triggered_on', '75')->nullable()->default(null)->comment('scope: mentorship, training, quiz, etc');
			$table->string('triggered_at', '75')->nullable()->default(null)->comment('id of the scope');
			$table->string('triggered_by', '75')->nullable()->default(null)->comment('condition: session, due, quiz , etc');
			$table->mediumText('responses')->nullable()->default(null);
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('survey_id')->references('id')->on('surveys')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('cycle_id')->references('id')->on('cycles')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('scope_id')->references('id')->on('survey_scopes')->onDelete('cascade')->onUpdate('cascade');
			$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('survey_responses');
	}
};
