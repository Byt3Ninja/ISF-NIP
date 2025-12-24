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
		if( !Schema::hasTable('organization_types') )
		{
			Schema::create('organization_types', function (Blueprint $table) {
				$table->id();
				$table->string('title_en', 35)->nullable()->default(null);
				$table->string('title_ar', 35)->nullable()->default(null);
				$table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
				$table->softDeletes();
			});
			$types = [
				[
					'en' => 'Incubator',
					'ar' => 'حاضنة'
				],
				[
					'en' => 'Accelerator',
					'ar' => 'مسرعة'
				],
				[
					'en' => 'University',
					'ar' => 'جامعة'
				]
			];

			foreach( $types as $type )
			{
				DB::table('organization_types')->insert([
					'title_en' => $type['en'],
					'title_ar' => $type['ar'],
				]);
			}
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('organization_types');
	}
};
