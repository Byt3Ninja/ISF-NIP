<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Permission;
use App\permissionsGroup;
use App\Http\Requests\StorePermissionGroupRequest;
use App\Http\Requests\UpdatePermissionGroupRequest;
use Illuminate\Support\Facades\DB;
use stdClass;

class PermissionGroupsController extends Controller
{
	public function index()
	{
		$groups_q = "SELECT
				pg.id,
				pg.name,
				pg.description,
				p.id AS permission_id,
				p.name AS permission_name,
				p.description AS permission_description,
				p.is_core AS core
			FROM
				permission_groups pg
			LEFT JOIN
				permissions p ON pg.id = p.permission_group_id
		";
		$groups = collect( DB::select( $groups_q ) );
		$groups = $groups->groupBy('id')->map(function( $groupedPermissions ) {
			$g = $groupedPermissions->first();
			$group = new stdClass;
			$group->id = $g->id;
			$group->name = $g->name;
			$group->description = $g->description;
			$group->permissions = $groupedPermissions->filter(function( $permissions ) {
				return !is_null( $permissions->permission_id );
			})->map(function( $permissions ) {
				$permission = new stdClass;
				$permission->id = $permissions->permission_id;
				$permission->name = $permissions->permission_name;
				$permission->description = $permissions->permission_description;
				$permission->core = $permissions->core;
				return $permission;
			})->values();
			return $group;
		})->values();

		return view('admin.permissionGroups.index', compact('groups'));
	}

	public function create()
	{
		return view('admin.permissionGroups.create');
	}

	public function store(StorePermissionGroupRequest $request, permissionsGroup $permission_group)
	{
		$permission_group->name = $request->input('name', null);
		$permission_group->description = $request->input('description', null);
		$permission_group->save();
		return redirect()->route('permission-groups.index');
	}

	public function edit( permissionsGroup $permission_group )
	{
		return view('admin.permissionGroups.edit', compact('permission_group'));
	}

	public function update(UpdatePermissionGroupRequest $request, permissionsGroup $permission_group)
	{
		$permission_group->name = $request->input('name', null);
		$permission_group->description = $request->input('description', null);
		$permission_group->save();
		return redirect()->route('permission-groups.index');
	}

	public function show(permissionsGroup $permission_group)
	{
		return view('admin.permissionGroups.show', compact('permission_group'));
	}

	public function destroy(permissionsGroup $permission_group)
	{
		$permission_group->delete();
		return back();
	}
}
