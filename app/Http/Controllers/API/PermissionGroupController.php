<?php

namespace App\Http\Controllers\API;

use App\permissionsGroup;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionGroupResource;
use App\Http\Requests\StorePermissionGroupRequest;
use App\Http\Requests\UpdatePermissionGroupRequest;
/**
 * @OA\Schema(
 *     schema="PermissionGroup",
 *     title="PermissionGroup",
 *     description="Permission Group Resource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Program Management"),
 *     @OA\Property(property="description", type="string", example="Manage programs and related access"),
 *     @OA\Property(property="permissionsCount", type="integer", example=5)
 * )
 */
class PermissionGroupController extends Controller
{
    use ApiResponder;

   /**
 * @OA\Get(
 *     path="/api/v1/permission-groups",
 *     summary="List Permission Groups",
 *     tags={"Permission Groups"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of permission groups",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Success"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/PermissionGroup")
 *             ),
 *             @OA\Property(
 *                 property="meta",
 *                 type="object",
 *                 @OA\Property(property="total", type="integer", example=50),
 *                 @OA\Property(property="page", type="integer", example=1),
 *                 @OA\Property(property="limit", type="integer", example=10)
 *             ),
 *             @OA\Property(
 *                 property="links",
 *                 type="object",
 *                 @OA\Property(property="first", type="string", example="http://yourapi.test/api/v1/permission-groups?page=1"),
 *                 @OA\Property(property="last", type="string", example="http://yourapi.test/api/v1/permission-groups?page=5"),
 *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
 *                 @OA\Property(property="next", type="string", nullable=true, example="http://yourapi.test/api/v1/permission-groups?page=2")
 *             )
 *         )
 *     )
 * )
 */
  public function index(Request $request)
{
    $user = auth()->user();
    if (!$user->ableTo('permission-groups.index')) {
            return $this->forbidden(__('api.forbidden'));
    }
    $perPage = $request->get('per_page', 10); 
    $paginated = permissionsGroup::withCount('permissions')->paginate($perPage, ['id', 'name', 'description']);

   $paginated->setCollection(
    collect(PermissionGroupResource::collection($paginated->getCollection())->resolve())
);

    return $this->success(
        $paginated->items(),
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
 *     path="/api/v1/permission-groups",
 *     summary="Create Permission Group",
 *     tags={"Permission Groups"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "description"},
 *             @OA\Property(property="name", type="string", example="Program Management"),
 *             @OA\Property(property="description", type="string", example="Manage programs and related access")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Created",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Permission group created successfully"),
 *             @OA\Property(property="data", ref="#/components/schemas/PermissionGroup")
 *         )
 *     )
 * )
 */
    public function store(StorePermissionGroupRequest $request)
    {
        $user = auth()->user();
        if (!$user->ableToAny(['permission-groups.create', 'permission-groups.store'])) {
            return $this->forbidden(__('api.forbidden'));
        }
        // Permissions and their groups are global, platform-wide records: changing them
        // needs platform admin scope, not just the permission.
        if (!$user->hasRole('admin')) {
            return $this->forbidden(__('api.forbidden'));
        }
        $group = permissionsGroup::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        return $this->created(new PermissionGroupResource($group),__('api.permission_group_created'));
    }

   /**
 * @OA\Put(
 *     path="/api/v1/permission-groups/{id}",
 *     summary="Update Permission Group",
 *     tags={"Permission Groups"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "description"},
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="description", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Updated",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Permission group updated successfully"),
 *             @OA\Property(property="data", ref="#/components/schemas/PermissionGroup")
 *         )
 *     )
 * )
 */
    public function update(UpdatePermissionGroupRequest $request,$id)
    {
        $user = auth()->user();
        if (!$user->ableToAny(['permission-groups.edit', 'permission-groups.update'])) {
            return $this->forbidden(__('api.forbidden'));
        }
        // Permissions and their groups are global, platform-wide records: changing them
        // needs platform admin scope, not just the permission.
        if (!$user->hasRole('admin')) {
            return $this->forbidden(__('api.forbidden'));
        }
        $permission_group = permissionsGroup::find($id);
        if(!$permission_group){
            return $this->notFound(__('api.permission_group_not_found'));
        }
        $permission_group->update([
            'name' => $request->filled('name') ? $request->name : $permission_group->name,
            'description' => $request->filled('description') ? $request->description : $permission_group->description,
        ]);
        return $this->success(new PermissionGroupResource($permission_group),[],__('api.permission_group_updated'));
    }
    /**
 * @OA\Delete(
 *     path="/api/v1/permission-groups/{id}",
 *     summary="Delete Permission Group",
 *     description="Deletes a permission group by ID. Only accessible to users with 'permission-groups.destroy' permission.",
 *     tags={"Permission Groups"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the permission group to delete",
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Permission group deleted successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Permission group deleted successfully"),
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
 *         description="Permission group not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Permission group not found"),
 *             @OA\Property(property="data", type="object", example={})
 *         )
 *     )
 * )
 */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->ableTo('permission-groups.destroy')) {
            return $this->forbidden(__('api.forbidden'));
        }
        // Permissions and their groups are global, platform-wide records: changing them
        // needs platform admin scope, not just the permission.
        if (!$user->hasRole('admin')) {
            return $this->forbidden(__('api.forbidden'));
        }
        $permission_group = permissionsGroup::find($id);
        if(!$permission_group){
            return $this->notFound(__('api.permission_group_not_found'));
        }
        $permission_group->delete();
        return $this->success(null,[],__('api.permission_group_deleted'));
    }
}
