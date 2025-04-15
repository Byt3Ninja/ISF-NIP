<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Role;
use App\Permission;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if( Schema::hasTable('roles') )
		{
			if( !Schema::hasColumn('roles', 'default_for') )
			{
				Schema::table('roles', function (Blueprint $table) {
					$table->string('default_for', 25)->nullable()->default(null)->after('name');
				});
				$roles = Role::get();
				foreach( $roles as $role )
				{
					$name = trim( strtolower( $role->name ) );
					$role->default_for = $name;
					$role->save();
				}
				if( Permission::get()->count() > 0 ) { Permission::default_permissions(); }
			}
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('roles') )
		{
			Schema::table('roles', function (Blueprint $table) {
				$table->dropColumn(['default_for']);
			});
		}
	}
};
