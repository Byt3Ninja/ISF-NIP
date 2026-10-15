<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Permission;
use App\Role;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\StoreRoleRequest;

class RolesController extends Controller
{
	public function index()
	{
		$this->authorizeRoleAccess('roles.index');
		$roles = Role::with(['permissions'])->get();
		return view('admin.roles.index', compact('roles'));
	}

	public function create()
	{
		$this->authorizeRoleAccess('roles.create', true);
		$permissions = DB::select("SELECT
				p.id,
				p.name,
				p.description,
				pg.name AS `group`,
				pg.description AS group_description
			FROM
				permissions p
			LEFT JOIN
				permission_groups pg ON pg.id = p.permission_group_id
			WHERE
				p.deleted_at IS NULL
		");
		$_permissions = collect( $permissions )->filter(function($permission) { return !is_null( $permission->group ); })->values();
		return view('admin.roles.create', compact('_permissions'));
	}

	public function store(StoreRoleRequest $request)
	{
		$this->authorizeRoleAccess('roles.store', true);
		$role = Role::create($request->all());
		$permissionNames = $request->input('permissions_flat');
    	$permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id');
		$role->permissions()->sync($permissionIds);
		app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
		return redirect()->route('roles.index');
	}

	public function edit(Role $role)
	{
		$this->authorizeRoleAccess('roles.edit', true);
		// $_permissions = Permission::pluck('name', 'id');
		$permissions = DB::select("SELECT
				p.id,
				p.name,
				p.description,
				pg.name AS `group`,
				pg.description AS group_description
			FROM
				permissions p
			LEFT JOIN
				permission_groups pg ON pg.id = p.permission_group_id
			WHERE
				p.deleted_at IS NULL
		");
		$role->load('permissions');
		$my_permissions = $role->permissions->pluck('id')->toArray();
		$_permissions = collect( $permissions )->filter(function($permission) { return !is_null( $permission->group ); })->map(function( $permission ) use ( $my_permissions ) {
			$permission->checked = in_array( $permission->id, $my_permissions );
			return $permission;
		})->values();
		return view('admin.roles.edit', compact('_permissions', 'role'));
	}

	public function update(UpdateRoleRequest $request, Role $role)
	{
		$this->authorizeRoleAccess('roles.update', true);
		$role->update($request->all());
		$permissionNames = $request->input('permissions_flat', []);
    	$permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id')->toArray();
		$role->permissions()->sync($permissionIds);
		app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
		return redirect()->route('roles.edit', [$role->id])->with('success', 'Saved');
	}

	public function show(Role $role)
	{
		$this->authorizeRoleAccess('roles.show');
		$role->load('permissions');
		return view('admin.roles.show', compact('role'));
	}

	public function destroy(Role $role)
	{
		$this->authorizeRoleAccess('roles.destroy', true);
		$role->delete();
		app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
		return back();
	}

	/**
	 * Roles are global, platform-wide records: the `roles` table carries no
	 * organization/tenant column, and every role is shared by all tenants.
	 *
	 * A resource-level "owner" check is therefore not expressible, and scoping a
	 * shared role to one tenant would break every other tenant using it. So
	 * reads require the matching roles.* permission, and any MUTATION
	 * additionally requires platform-wide (global admin) scope — which stops a
	 * tenant admin from editing or deleting a role belonging to the platform or
	 * to another tenant.
	 *
	 * @param  string  $permission  the roles.* permission for this action
	 * @param  bool    $mutating    true for actions that create/modify/delete a role
	 */
	protected function authorizeRoleAccess(string $permission, bool $mutating = false): void
	{
		$user = auth()->user();

		if (!$user || !$user->ableTo($permission)) {
			abort(401);
		}

		if ($mutating && !$user->hasRole('admin')) {
			abort(403);
		}
	}
}
