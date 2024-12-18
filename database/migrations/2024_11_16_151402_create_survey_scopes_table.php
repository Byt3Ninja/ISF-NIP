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
		Schema::create('survey_scopes', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('survey_id')->nullable();
			$table->unsignedBigInteger('cycle_id')->nullable();
			$table->unsignedBigInteger('organization_id')->nullable();
			$table->string('scope', 25)->nullable()->default(null);
			$table->boolean('trigger_by_session')->nullable()->default(true);
			$table->boolean('trigger_by_due')->nullable()->default(true);
			$table->boolean('trigger_by_quiz')->nullable()->default(true);
			$table->foreign('survey_id')->references('id')->on('surveys')->onDelete('cascade')->onUpdate('cascade');
			$table->foreign('cycle_id')->references('id')->on('cycles')->onDelete('cascade')->onUpdate('cascade');
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
		Schema::dropIfExists('survey_scopes');
	}
};
