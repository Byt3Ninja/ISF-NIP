<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Permission;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Requests\StorePermissionRequest;
use Illuminate\Support\Facades\DB;
use stdClass;

class PermissionsController extends Controller
{
    public function index()
    {
        // $_permissions = Permission::all();
        $permission_q = "SELECT
                p.id,
                p.name,
                r.id AS role_id,
                r.name AS role_name
            FROM
                permissions p
            LEFT JOIN
                role_has_permissions rp ON p.id = rp.permission_id
            LEFT JOIN
                roles r ON rp.role_id = r.id
        ";
        $_permissions = collect( DB::select( $permission_q ) )->groupBy('id')->map(function( $groupedPermissions ) {
            $p = $groupedPermissions->first();
            $permission = new stdClass;
            $permission->id = $p->id;
            $permission->name = $p->name;
            $permission->roles = $groupedPermissions->filter(function($roles) {
                return !is_null($roles->role_id);
            })->map(function($roles) {
                $role = new stdClass;
                $role->id = $roles->role_id;
                $role->name = $roles->role_name;
                return $role;
            })->values();
            return $permission;
        })->values();
        return view('admin.permissions.index', compact('_permissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(StorePermissionRequest $request)
    {
        $permission = Permission::create($request->all());
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        return redirect()->route('permissions.index');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $permission->update($request->all());
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        return redirect()->route('permissions.index');
    }

    public function show(Permission $permission)
    {
        return view('admin.permissions.show', compact('permission'));
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        return back();
    }
}
