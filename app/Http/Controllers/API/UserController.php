<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\CountryResource;
use App\User;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource; 
use Illuminate\Support\Facades\Auth;
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

        $result = $this->userService->index($request->all(),true);

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

            if ($result['success']) {
              return $this->created(new UserResource($result['data']),__('api.user_created'));
            }

            return $this->serverError(__('api.user_create_failed'), 
                $result['status_code'] ?? 500, 
                $result['data'] ?? null
            );

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

            $data = $request->except(['role', 'organization_id', 'api_token']);
            $result = $this->userService->update($user, $data);
            if ($result['success']) {
                 return $this->success(new UserResource($result['user']), [], __('api.user_updated'));
            }

            return $this->serverError(__('api.user_update_failed'), $result['status']);
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

       /**
         * Get companies list based on user role.
         *
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        public function companies(Request $request)
        {
            $this->authorize('index', Auth::user()); // Or a more specific policy for companies

            $result = $this->userService->getCompanies($request->all(), Auth::user());

            if ($result['success']) {
                return $this->success($result['data']);
            }

            return $this->error($result['message'], 400);
        }

        /**
         * Update the specified user's password.
         *
         * @param UpdatePasswordRequest $request
         * @param User $user
         * @return \Illuminate\Http\JsonResponse
         */
        public function updatePassword(UpdatePasswordRequest $request, User $user)
        {
            $this->authorize('update', $user);

            $result = $this->userService->updatePassword($user, $request->validated());

            if ($result['success']) {
                return $this->success(null, [], $result['message']);
            }

            return $this->error($result['message'], $result['status']);
        }

}
