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
		if( Schema::hasTable('screening_evaluation_results') )
		{
			Schema::table('screening_evaluation_results', function (Blueprint $table) {
				if( !Schema::hasColumn('screening_evaluation_results', 'evaluation_id') )
				{
					$table->unsignedBigInteger('evaluation_id')->nullable()->default(null)->after('id');
					$table->foreign('evaluation_id')->references('id')->on('screening_evaluation')->onUpdate('cascade')->onDelete('cascade');
				}
				if( !Schema::hasColumn('screening_evaluation_results', 'status') )
				{
					$table->boolean('status')->default(0)->comment('0 = Draft, 1 = Published')->after('comment');
				}
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('screening_evaluation_results') )
		{
			Schema::table('screening_evaluation_results', function (Blueprint $table) {
				if( Schema::hasColumn('screening_evaluation_results', 'evaluation_id') )
				{
					$table->dropForeign('screening_evaluation_results_evaluation_id_foreign');
					$table->dropColumn(['evaluation_id']);
				}
				if( Schema::hasColumn('screening_evaluation_results', 'status') )
				{
					$table->dropColumn(['status']);
				}
			});
		}
	}
};
