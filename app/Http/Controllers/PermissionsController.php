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
                p.is_core AS core,
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
            $permission->core = $p->core;
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
        $groups = collect( DB::select("SELECT pg.id, pg.name FROM permission_groups pg") );
        return view('admin.permissions.create', compact('groups'));
    }

    public function store(StorePermissionRequest $request)
    {
        $permission = new Permission;
        $permission->name = $request->input('name', null);
        $permission->description = $request->input('description', null);
        $permission->is_core = $request->has('is_core') ? true : false;
        $permission->permission_group_id = $request->input('permission_group_id', null);
        $permission->save();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        return redirect()->route('permissions.index');
    }

    public function edit(Permission $permission)
    {
        $groups = collect( DB::select("SELECT pg.id, pg.name FROM permission_groups pg") );
        return view('admin.permissions.edit', compact('permission', 'groups'));
    }

    public function update(UpdatePermissionRequest $request, $id)
    {
        $permission = Permission::find($id);
        $permission->name = $request->input('name', null);
        $permission->description = $request->input('description', null);
        $permission->is_core = $request->has('is_core') ? true : false;
        $permission->permission_group_id = $request->input('permission_group_id', null);
        $permission->save();
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

    public function stripFromRoles(Request $request)
    {
        $user = auth()->user();
        if( $user->ableToAny(['roles.edit', 'roles.update', 'permissions.edit', 'permissions.update']) )
        {
            $permissionId = $request->input('permission', null);
            $roleId = $request->input('role', null);

            DB::table('role_has_permissions')->where('permission_id', $permissionId)->where('role_id', $roleId)->delete();
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            $getPermissionRoles = DB::table('role_has_permissions AS rhp')
                ->select([
                    'rhp.role_id',
                    'r.name',
                    'rhp.permission_id'
                ])
                ->leftJoin('roles AS r', 'rhp.role_id', '=', 'r.id')
                ->where('rhp.permission_id', $permissionId)
            ->get()->map(function($role) {
                $role->url = route('roles.edit', ['role' => $role->role_id]);
                return $role;
            }); 
            return response()->json([
                'status' => true,
                'message' => 'Done',
                'data' => $getPermissionRoles
            ]);
        } else { return abort( 401 ); }
    }
}
