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
