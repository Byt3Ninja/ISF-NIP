<?php

namespace App\Http\Controllers\API;

use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Traits\ApiResponder;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    use ApiResponder;
    /**
 * @OA\Get(
 *     path="/api/roles",
 *     summary="Get list of available roles",
 *     description="Returns a list of all roles. Only accessible by Admin users.",
 *     operationId="getRoles",
 *     tags={"Roles"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of roles returned successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="Mentor")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Only accessible by Admin",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Access denied.")
 *         )
 *     )
 * )
 */
    public function index()
    {
        $user = auth()->user();
        if (!$user->ableTo('roles.index')) {
            return $this->forbidden(__('api.forbidden'));
       }
        $roles = Role::select('id', 'name')->get();
        return $this->success(RoleResource::collection($roles));
    }
}
