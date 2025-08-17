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
            $roles=$this->userService->roleList();

                            if ($roles['success'])
                    {
                    $this->success(RoleResource::collection($roles['data']),[], __('api.user_updated'));
                    }

                 return response()->json($roles, 201);

        
    }
}
