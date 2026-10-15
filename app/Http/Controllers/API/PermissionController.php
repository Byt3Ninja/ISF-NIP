<?php

namespace App\Http\Controllers\API;

use App\Role;
use App\Permission;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Resources\UserPermissionResource;

/**
 * @OA\Schema(
 *     schema="GroupedPermissions",
 *     type="object",
 *     title="GroupedPermissions",
 *     example={
 *         "roleId": 12,
 *         "roleName": {"Admin"},
 *         "permissions": {
 *             "program": {
 *                 "view_program",
 *                 "join_program"
 *             },
 *             "team": {
 *                 "view_team_info",
 *                 "edit_team_info"
 *             },
 *             "sessions": {
 *                 "view_sessions",
 *                 "book_sessions"
 *             }
 *         }
 *     },
 *     @OA\Property(property="user_id", type="integer", example=12),
 *     @OA\Property(
 *         property="roles",
 *         type="array",
 *         @OA\Items(type="string", example="Admin")
 *     ),
 *     @OA\Property(
 *         property="permissions",
 *         type="object",
 *         additionalProperties=@OA\Schema(
 *             type="array",
 *             @OA\Items(type="string", example="view_program")
 *         )
 *     )
 * )
 */
class PermissionController extends Controller
{
    use ApiResponder;

   /**
 * @OA\Get(
 *     path="/api/v1/users/me/permissions",
 *     summary="Get current user's permissions grouped by module",
 *     description="Returns the current authenticated user's roles and permissions grouped by module",
 *     tags={"Permissions"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Grouped permissions",
 *         @OA\JsonContent(ref="#/components/schemas/GroupedPermissions")
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 */
    public function myPermissions(Request $request)
    {
        $user = $request->user();
        $role = $user->roles()->with('permissions.permissionsGroup')->first();
        return $this->success(new PermissionResource($role));
    }

    /**
 * @OA\Get(
 *     path="/api/v1/{role}/permissions",
 *     summary="Get permissions based on Role (Admin only)",
 *     description="Returns the specified user's roles and permissions grouped by module. Admin only.",
 *     tags={"Permissions"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         description="Role ID to fetch permissions for",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Grouped permissions",
 *         @OA\JsonContent(ref="#/components/schemas/GroupedPermissions")
 *     ),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="User not found"),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 */

    public function userPermissions(Role $role)
    {
        if (!auth()->user()->hasRole('Admin')) {
            return $this->forbidden(__('api.forbidden'));
        }

        if(!$role) {
            return $this->notFound(__('api.role_not_found'));
        }
        $role->load(['permissions', 'permissions.permissionsGroup']);

        return $this->success(new PermissionResource($role));
    }

    /**
 * @OA\Get(
 *     path="/api/v1/permissions",
 *     summary="List Permissions",
 *     tags={"Permissions"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="List of permissions")
 * )
 */
public function index(Request $request)
{
    $user = auth()->user();
    if (!$user->ableTo('permissions.index')) {
            return $this->forbidden(__('api.forbidden'));
    }
    $perPage = $request->get('per_page', 10);

    $paginated = Permission::with(['roles:id,name','permissionsGroup'])->paginate($perPage);

    return $this->success(
        UserPermissionResource::collection($paginated->items()),
        [
            'meta' => [
                'total' => $paginated->total(),
                'page' => $paginated->currentPage(),
                'limit' => $paginated->perPage(),
            ],
            'links' => [
                'first' => $paginated->url(1),
                'last' => $paginated->url($paginated->lastPage()),
                'prev' => $paginated->previousPageUrl(),
                'next' => $paginated->nextPageUrl(),
            ],
        ]
    );
}

/**
 * @OA\Post(
 *     path="/api/v1/permissions",
 *     summary="Create Permission",
 *     tags={"Permissions"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "description", "permission_group_id"},
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="permission_group_id", type="integer"),
 *             @OA\Property(property="is_core", type="boolean")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Created")
 * )
 */
public function store(StorePermissionRequest $request)
{
    $user = auth()->user();
    if (!$user->ableToAny(['permissions.create', 'permissions.store'])) {
            return $this->forbidden(__('api.forbidden'));
    }
    // Permissions and their groups are global, platform-wide records: changing them
    // needs platform admin scope, not just the permission.
    if (!$user->hasRole('admin')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $permission = Permission::create([
        'name' => $request->name,
        'description' => $request->description,
        'is_core' => $request->is_core ?? false,
        'permission_group_id' => $request->permission_group_id,
    ]);
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    return $this->created(new UserPermissionResource($permission),__('api.permission_created'));
}

/**
 * @OA\Put(
 *     path="/api/v1/permissions/{id}",
 *     summary="Update Permission",
 *     tags={"Permissions"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="permission_group_id", type="integer"),
 *             @OA\Property(property="is_core", type="boolean")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Updated")
 * )
 */
public function update(UpdatePermissionRequest $request,$id)
{
     $user = auth()->user();
     if (!$user->ableToAny(['permissions.edit', 'permissions.update'])) {
            return $this->forbidden(__('api.forbidden'));
     }
     // Permissions and their groups are global, platform-wide records: changing them
     // needs platform admin scope, not just the permission.
     if (!$user->hasRole('admin')) {
         return $this->forbidden(__('api.forbidden'));
     }
     $permission = Permission::find($id);
     if(!$permission){
            return $this->notFound(__('api.permission_not_found'));
     }
    $permission->update([
        'name' => $request->filled('name') ? $request->name : $permission->name,
        'description' => $request->filled('description') ? $request->description : $permission->description,
        'is_core' => $request->has('is_core') ? $request->is_core : $permission->is_core,
        'permission_group_id' => $request->filled('permission_group_id') ? $request->permission_group_id : $permission->permission_group_id,
    ]);
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    $permission->refresh();
    return $this->success(new UserPermissionResource($permission),[],__('api.permission_updated'));
}

    /**
 * @OA\Delete(
 *     path="/api/v1/permissions/{id}",
 *     summary="Delete Permission",
 *     description="Deletes a permission group by ID. Only accessible to users with 'permissions.destroy' permission.",
 *     tags={"Permissions"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the permission to delete",
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Permission deleted successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Permission deleted successfully"),
 *             @OA\Property(property="data", type="null", example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Forbidden"),
 *             @OA\Property(property="data", type="object", example={})
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Permission not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Permission not found"),
 *             @OA\Property(property="data", type="object", example={})
 *         )
 *     )
 * )
 */
public function destroy($id)
{
        $user = auth()->user();
        if (!$user->ableTo('permissions.destroy')) {
            return $this->forbidden(__('api.forbidden'));
        }
        // Permissions and their groups are global, platform-wide records: changing them
        // needs platform admin scope, not just the permission.
        if (!$user->hasRole('admin')) {
            return $this->forbidden(__('api.forbidden'));
        }
        $permission = Permission::find($id);
        if(!$permission){
            return $this->notFound(__('api.permission_not_found'));
        }
        $permission->delete();
        return $this->success(null,[],__('api.permission_deleted'));
}
}
