<?php

namespace App\Http\Controllers\API;

use App\Role;
use App\Services\UserService;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Traits\ApiResponder;
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
    $this->authorize('deactivate', Auth::user());

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
    $role = Role::find($id);
    if (!$role) {
        return $this->notFound(__('api.role_not_found'));
    }

    $role->delete();
    return $this->success(null, [], __('api.role_deleted'));
}
}
