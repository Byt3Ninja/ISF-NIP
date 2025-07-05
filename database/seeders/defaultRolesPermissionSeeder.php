<?php

namespace Database\Seeders;

use File;
use App\Role;
use App\Permission;
use App\RolePermissions;
use App\permissionsGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Log;

class defaultRolesPermissionSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		$toAdd = [];
		$toUpdate = [];

		$db_roles = collect( DB::select('SELECT * FROM roles') );
		$db_groups = collect( DB::select('SELECT * FROM permission_groups') );
		$db_permissions = collect( DB::select('SELECT * FROM permissions') );
		$db_role_has_permissions = collect( DB::select('SELECT * FROM role_has_permissions') );

		$json = File::get(base_path('database/dbFiles/rolesWithDefaultPermissions.json'));
		$parsedFile = json_decode($json, true);
		if( empty( $parsedFile ) )
		{
			$this->command->error('Invalid JSON file provided');
		}
		else
		{
			try {
				DB::beginTransaction();
				$rolesToCreate = [];
				$groupsToCreate = [];
				$permissionsToCreate = [];
				$roles = [];
				foreach( $parsedFile as $permissionGroup )
				{
					$name = $permissionGroup['name'];
					$check = $db_groups->where('name', $name)->first();
					if( empty( $check ) )
					{
						$groupsToCreate[] = [
							'name' => $name,
							'description' => $permissionGroup['description'],
						];
					}
				}
				if( count( $groupsToCreate ) )
				{
					permissionsGroup::insert( $groupsToCreate );
					foreach( $groupsToCreate as $group )
					{
						$name = $group['name'];
						$this->command->info("Permission Group created successfully for: {$name}");
					}
					$db_groups = collect( DB::select('SELECT * FROM permission_groups') );
				}

				foreach( $parsedFile as $permissionGroup )
				{
					$name = $permissionGroup['name'];
					$getGroup = $db_groups->where('name', $name)->first();
					$permissions = $permissionGroup['permissions'];
					foreach( $permissions as $permission )
					{
						$permission_name = $permission['name'];
						$default_for = $permission['default_for'];
						foreach( $default_for as $role ) { if( !in_array( $role, $roles ) ) { $roles[] = $role; } }
						$check = $db_permissions->where('name', $permission_name)->first();
						if( empty( $check ) )
						{
							$permissionsToCreate[] = [
								'name' => $permission_name,
								'description' => $permission['description'],
								'guard_name' => $permission['guard_name'],
								'is_core' => $permission['is_core'],
								'permission_group_id' => $getGroup->id,
							];
						}
					}
				}

				foreach( $roles as $role )
				{
					$check = $db_roles->where('default_for', $role)->first();
					if( empty( $check ) )
					{
						$rolesToCreate[] = [
							'name' => $role,
							'default_for' => $role,
							'guard_name' => 'web',
						];
					}
				}

				if( count( $rolesToCreate ) )
				{
					Role::insert( $rolesToCreate );
					foreach( $rolesToCreate as $role )
					{
						$name = $role['name'];
						$this->command->info("Role created successfully for: {$name}");
					}
					$db_roles = collect( DB::select('SELECT * FROM roles') );
				}

				if( count( $permissionsToCreate ) )
				{
					Permission::insert( $permissionsToCreate );
					foreach( $permissionsToCreate as $permission )
					{
						$name = $permission['name'];
						$this->command->info("Permission created successfully for: {$name}");
					}
					$db_permissions = collect( DB::select('SELECT * FROM permissions') );
				}
				if( count( $groupsToCreate ) || count( $permissionsToCreate ) || count( $rolesToCreate ) )
				{
					$db_role_has_permissions = collect( DB::select('SELECT * FROM role_has_permissions') );
				}

				$default_roles = $db_roles->whereIn('default_for', $roles);
				$default_roles_ids = $default_roles->pluck('id')->toArray();
				RolePermissions::whereIn('role_id', $default_roles_ids)->delete();
				$default_roles_permissions = [];
				foreach( $parsedFile as $permissionGroup )
				{
					foreach( $permissionGroup['permissions'] as $permission )
					{
						$permission_name = $permission['name'];
						$check = $db_permissions->where('name', $permission_name)->first();
						if( empty( $check ) ) { continue; }
						else
						{
							$default_for = $permission['default_for'];
							$get_roles = $db_roles->whereIn('default_for', $default_for)->values();
							foreach( $get_roles as $role )
							{
								$default_roles_permissions[] = [
									'role_id' => $role->id,
									'permission_id' => $db_permissions->where('name', $permission_name)->first()->id
								];
							}
						}
					}
				}
				if( count( $default_roles_permissions ) )
				{
					RolePermissions::insert( $default_roles_permissions );
				}

				app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
				DB::commit();
				$this->command->info('Default roles and permissions successfully deployed!');
			} catch (\Throwable $th) {
				DB::rollBack();
				$this->command->error('Something went wrong while deploying default roles and permissions!');
				$this->command->error($th->getMessage());
				// Log the error message
				Log::error('Error deploying default roles and permissions: ' . $th->getMessage());
			}
			catch (\Exception $e) {
				DB::rollBack();
				$this->command->error('Something went wrong while deploying default roles and permissions!');
				$this->command->error($e->getMessage());
				// Log the error message
				Log::error('Error deploying default roles and permissions: ' . $e->getMessage());
			}
		}
	}
}
