<?php

namespace App\Http\Controllers\API;

use App\permissionsGroup;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionGroupRequest;
use App\Http\Requests\UpdatePermissionGroupRequest;
/**
 * @OA\Tag(name="Permission Groups")
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
     *     @OA\Response(response=200, description="List of permission groups")
     * )
     */
  public function index(Request $request)
{
    $user = auth()->user();
    if (!$user->ableTo('permission-groups.index')) {
            return $this->forbidden(__('api.forbidden'));
    }
    $perPage = $request->get('per_page', 10); 
    $paginated = permissionsGroup::withCount('permissions')
        ->paginate($perPage, ['id', 'name', 'description']);

    $groups = $paginated->getCollection()->map(function ($group) {
        return [
            'id' => $group->id,
            'title' => $group->name,
            'description' => $group->description,
            'permissionsCount' => $group->permissions_count,
        ];
    });

    $paginated->setCollection($groups);

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
     *             required={"title", "description"},
     *             @OA\Property(property="title", type="string", example="Program Management"),
     *             @OA\Property(property="description", type="string", example="Manage programs and related access")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function store(StorePermissionGroupRequest $request)
    {
        $user = auth()->user();
        if (!$user->ableToAny(['permission-groups.create', 'permission-groups.store'])) {
            return $this->forbidden(__('api.forbidden'));
        }
        $group = permissionsGroup::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        return $this->created($group,__('api.permission_group_created'));
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
     *             required={"title", "description"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated")
     * )
     */
    public function update(UpdatePermissionGroupRequest $request,$id)
    {
        $user = auth()->user();
        if (!$user->ableToAny(['permission-groups.edit', 'permission-groups.update'])) {
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
        return $this->success($permission_group,[],__('api.permission_group_updated'));
    }
}
