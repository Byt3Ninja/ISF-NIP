<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;

/**
 * @OA\Schema(
 *     schema="GroupedPermissions",
 *     type="object",
 *     title="GroupedPermissions",
 *     example={
 *         "user_id": 12,
 *         "roles": {"Admin"},
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
        return $this->success(new PermissionResource($user));
    }

    /**
 * @OA\Get(
 *     path="/api/v1/users/{user_id}/permissions",
 *     summary="Get specified user's permissions (Admin only)",
 *     description="Returns the specified user's roles and permissions grouped by module. Admin only.",
 *     tags={"Permissions"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         description="User ID to fetch permissions for",
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

    public function userPermissions(User $user)
    {
        if (!auth()->user()->hasRole('Admin')) {
            return $this->forbidden(__('api.forbidden'));
        }

        if(!$user) {
            return $this->notFound(__('api.user_not_found'));
        }

        return $this->success(new PermissionResource($user));
    }
}
