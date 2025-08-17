<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\User\StoreUserRequest;
use App\User;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreUserApiRequest;
use App\Http\Requests\UpdateUserApiRequest;
use App\Http\Requests\User\UserFilterRequest;
use App\Http\Requests\User\UserIdRequest;

class UserController extends Controller
{
    use ApiResponder;

    /**
     * UserController constructor.
     * @param UserService $userService
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
     * Display a listing of the resource.
     * @param UserFilterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
     public function index(UserFilterRequest $request)
    {

       $this->authorize('index', Auth::user());

        $result = $this->userService->index($request->all());

        return $this->success($result['data'], ['meta' => $result['meta']]);

    }
    /**
     * Display the specified resource.
     * @param UserIdRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
        public function show(UserIdRequest $request, )
        {   
            $this->authorize('view', Auth::user());

            $user = $this->userService->show($request->user);

            return $this->success(new UserResource($user));
        }

        /**
         * Store a newly created resource in storage.
         * @param StoreUserRequest $request
         * @return \Illuminate\Http\JsonResponse
         */
        public function store(StoreUserRequest $request)
        {
            $this->authorize('store', Auth::user());

            $result = $this->userService->store($request->all());

            

                  if ($result['success'])
                    {
                    $this->success(new UserResource($result['data']),[], __('api.user_updated'));
                    }

                 return response()->json($result, 201);

        }

        /**
         * Update the specified resource in storage.
         * @param UpdateUserApiRequest $request
         * @param User $user
         * @return \Illuminate\Http\JsonResponse
         */
        public function update(UpdateUserApiRequest $request, User $user)
        {   
            $this->authorize('update', Auth::user());

            $result = $this->userService->update($user, $request->validated());
            

            return $this->success(new UserResource($result['user']),[], __('api.user_updated'));
        }


        /**
         * Deactivate a user.
         * @param int $id
         * @return \Illuminate\Http\JsonResponse
         */
        public function deactivate(UserIdRequest $request)
        {
            $this->authorize('deactivate', Auth::user());
            $user=$this->userService->show($request->user);
            $result = $this->userService->deactivateUser( $user,Auth::id());

            if (!$result['success']) {
                return $this->error($result['message'], $result['status'] ?? 403);
            }

            return $this->success(null, [], __('api.user_deactivated'));
        }



}
