<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Permission;
use App\Role;
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
		$_permissions = Permission::pluck('name', 'id');
		$role->load('permissions');
		return view('admin.roles.edit', compact('_permissions', 'role'));
	}

	public function update(UpdateRoleRequest $request, Role $role)
	{
		$role->update($request->all());
		$role->permissions()->sync($request->input('permissions', []));
		app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
		return redirect()->route('roles.index');
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
