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
		$roles = Role::with(['permissions'])->get();
		return view('admin.roles.index', compact('roles'));
	}

	public function create()
	{
		$_permissions = Permission::pluck('name', 'id');
		return view('admin.roles.create', compact('_permissions'));
	}

	public function store(StoreRoleRequest $request)
	{
		$role = Role::create($request->all());
		$role->permissions()->sync($request->input('permissions', []));
		app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
		return redirect()->route('roles.index');
	}

	public function edit(Role $role)
	{
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
		$role->update($request->all());
		$role->permissions()->sync($request->input('permissions', []));
		app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
		return redirect()->route('roles.edit', [$role->id])->with('success', 'Saved');
	}

	public function show(Role $role)
	{
		$role->load('permissions');
		return view('admin.roles.show', compact('role'));
	}

	public function destroy(Role $role)
	{
		$role->delete();
		app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
		return back();
	}

}
