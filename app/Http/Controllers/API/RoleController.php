<?php

namespace App\Http\Controllers\API;

use Auth;
use App\Role;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    use ApiResponder;

    /**
     * UserController constructor.
     * @param \App\Services\UserService $userService
     */
    protected $userService;
    /**
     * Summary of __construct
     * @param \App\Services\UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
 * @OA\Get(
 *     path="/api/v1/roles",
 *     summary="List all roles",
 *     description="Returns a list of all roles with total users and avatars. Requires appropriate authorization.",
 *     operationId="getRoles",
 *     tags={"Roles"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of roles",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Success"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Admin"),
 *                     @OA\Property(property="totalUsers", type="integer", example=10),
 *                     @OA\Property(
 *                         property="avatars",
 *                         type="array",
 *                         @OA\Items(type="string", example="https://example.com/avatar.jpg")
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized"
 *     )
 * )
 */
    public function index()
{
    // Listing roles is governed by its own permission (it used to borrow the
    // user-deactivation policy, authorizing the caller against themself).
    if (!auth()->user()->ableTo('roles.index')) {
        return $this->forbidden(__('api.forbidden'));
    }

    $roles = $this->userService->roleList();

    if ($roles['success']) {
        return $this->success(
            RoleResource::collection($roles['data']),
        );
    }

    return response()->json($roles, 400);
}

/**
 * @OA\Delete(
 *     path="/api/v1/roles/{id}",
 *     summary="Delete a role by ID",
 *     description="Deletes the role with the given ID. Only accessible to users with 'roles.destroy' permission.",
 *     operationId="deleteRole",
 *     tags={"Roles"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Role ID to delete",
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Role deleted successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Role deleted successfully"),
 *             @OA\Property(property="data", type="null", example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Role not found"
 *     )
 * )
 */

public function destroy($id)
{
    if (!auth()->user()->ableTo('roles.destroy')) {
        return $this->forbidden(__('api.forbidden'));
    }

    // Roles are global, shared records (no tenant column on `roles`), so a
    // per-role owner cannot be expressed. Restrict deletion to platform-wide
    // admin scope: a tenant admin must not delete a role used by other tenants.
    if (!auth()->user()->hasRole('admin')) {
        return $this->forbidden(__('api.forbidden'));
    }
    $role = Role::find($id);
    if (!$role) {
        return $this->notFound(__('api.role_not_found'));
    }

    $role->delete();
    return $this->success(null, [], __('api.role_deleted'));
}

/**
 * @OA\Post(
 *     path="/api/roles",
 *     summary="Create a new role with permissions",
 *     description="Creates a new role and assigns specific permissions. Requires appropriate authorization.",
 *     operationId="createRole",
 *     tags={"Roles"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "permissions"},
 *             @OA\Property(property="name", type="string", example="Content Manager"),
 *             @OA\Property(
 *                 property="permissions",
 *                 type="object",
 *                 example={
 *                     "content": {"content.create", "content.edit", "content.view"},
 *                     "users": {"users.view"},
 *                     "dashboard": {"dashboard.view"}
 *                 }
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Role created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Role created successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="roleId", type="integer", example=123),
 *                 @OA\Property(property="roleName", type="string", example="Content Manager"),
 *                 @OA\Property(property="description", type="string", example="Role for managing content"),
 *                 @OA\Property(
 *                     property="permissions",
 *                     type="object",
 *                     example={
 *                         "content": {"content.create", "content.edit"},
 *                         "users": {"users.view"}
 *                     }
 *                 ),
 *                 @OA\Property(property="createdAt", type="string", format="date-time"),
 *                 @OA\Property(property="updatedAt", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation failed or missing fields"
 *     ),
 *     @OA\Response(
 *         response=409,
 *         description="Role with this name already exists"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Invalid permissions specified"
 *     )
 * )
 */
public function store(StoreRoleRequest $request)
{
    if (!auth()->user()->ableToAny(['roles.create','roles.store'])) {
        return $this->forbidden(__('api.forbidden'));
    }

    // New roles are global records visible to every tenant.
    if (!auth()->user()->hasRole('admin')) {
        return $this->forbidden(__('api.forbidden'));
    }

    $result = $this->userService->createRole($request->validated());

    if ($result['success']) {
        return $this->created($result['data'],$result['message']);
    }

    if (str_contains($result['message'], 'Duplicate entry')) {
        return $this->conflict(__('api.role_exists'));
    }

     return $this->serverError($result['message'] ?? __('api.unexpected_error'),[],500);
}

/**
 * @OA\Put(
 *     path="/api/v1/{roleId}/permissions",
 *     summary="Update role permissions",
 *     description="Updates the name, description, or permissions of an existing role. Requires proper authorization.",
 *     operationId="updateRolePermissions",
 *     tags={"Roles"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="roleId",
 *         in="path",
 *         required=true,
 *         description="The ID of the role to update",
 *         @OA\Schema(type="integer", example=123)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Senior Content Manager"),
 *             @OA\Property(
 *                 property="permissions",
 *                 type="object",
 *                 example={
 *                     "content": {"content.create", "content.edit", "content.view", "content.delete"},
 *                     "users": {"users.view", "users.list", "users.edit"},
 *                     "dashboard": {"dashboard.view", "dashboard.analytics"}
 *                 }
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Role updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Role permissions updated successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="roleId", type="integer", example=123),
 *                 @OA\Property(property="roleName", type="string", example="Senior Content Manager"),
 *                 @OA\Property(property="description", type="string", example="Updated role description"),
 *                 @OA\Property(
 *                     property="permissions",
 *                     type="object",
 *                     example={
 *                         "content": {"content.create", "content.edit", "content.view", "content.delete"},
 *                         "users": {"users.view", "users.list", "users.edit"},
 *                         "dashboard": {"dashboard.view", "dashboard.analytics"}
 *                     }
 *                 ),
 *                 @OA\Property(property="updatedAt", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request or invalid body"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Role not found"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Invalid permissions"
 *     )
 * )
 */

public function updatePermissions(UpdateRoleRequest $request, $roleId)
{
    if (!auth()->user()->ableToAny(['roles.update', 'roles.edit'])) {
        return $this->forbidden(__('api.forbidden'));
    }

    // Roles are global, shared records (no tenant column on `roles`), so a
    // per-role owner cannot be expressed. Restrict mutation to platform-wide
    // admin scope: this is what stops a tenant admin from rewriting the
    // permissions of a role owned by the platform or by another tenant.
    if (!auth()->user()->hasRole('admin')) {
        return $this->forbidden(__('api.forbidden'));
    }

    $result = $this->userService->updateRolePermissions($roleId, $request->validated());

    if ($result['success']) {
        return $this->success($result['data'], [], $result['message']);
    }

    if (($result['status'] ?? null) === 404) {
        return $this->notFound(__('api.role_not_found'));
    }

    return $this->serverError($result['message'], [], $result['status'] ?? 500);
}
}
