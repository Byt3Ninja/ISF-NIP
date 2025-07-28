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
		if( Schema::hasTable('cycles') )
		{
			Schema::table('cycles', function (Blueprint $table) {
				if( !Schema::hasColumn('cycles', 'is_public') ) { $table->boolean('is_public')->default(false)->after('organization_id'); }
				if( !Schema::hasColumn('cycles', 'public_slug') ) { $table->string('public_slug', 250)->nullable()->default(null)->after('is_public'); }
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('cycles') )
		{
			Schema::table('cycles', function (Blueprint $table) {
				if( Schema::hasColumn('cycles', 'is_public') ) { $table->dropColumn('is_public'); }
				if( Schema::hasColumn('cycles', 'public_slug') ) { $table->dropColumn('public_slug'); }
			});
		}
	}
};
