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
		if( !Schema::hasTable('permission_groups') )
		{
			Schema::create('permission_groups', function (Blueprint $table) {
				$table->id();
				$table->string('name', 75)->nullable()->default(null)->unique();
				$table->string('description', 255)->nullable()->default(null);
				$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
				$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
			});
		}
		if( Schema::hasTable('permissions') && !Schema::hasColumn('permissions', 'permission_group_id') )
		{
			Schema::table('permissions', function (Blueprint $table) {
				$table->unsignedBigInteger('permission_group_id')->nullable()->default(null)->after('is_core');
				$table->string('description', 255)->nullable()->default(null)->after('name');
				$table->foreign('permission_group_id')->references('id')->on('permission_groups')->onUpdate('cascade')->onDelete('set null');
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'permission_group_id') )
		{
			Schema::table('permissions', function (Blueprint $table) {
				$table->dropForeign('permissions_permission_group_id_foreign');
				$table->dropColumn(['permission_group_id', 'description']);
			});
		}

		if( Schema::hasTable('permission_groups') )
		{
			Schema::dropIfExists('permission_groups');
		}
	}
};
