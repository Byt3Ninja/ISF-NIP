<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreUserApiRequest;
use App\Http\Requests\UpdateUserApiRequest;

class UserController extends Controller
{
    use ApiResponder;
    /**
 * @OA\Get(
 *     path="/api/users",
 *     summary="List users with filters (Admin and OrganizationManager only)",
 *     description="Returns a paginated list of users, filtered by role or search query. Excludes current authenticated user.",
 *     operationId="getUsers",
 *     tags={"Users"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="role",
 *         in="query",
 *         description="Filter users by role (e.g. Admin, User, Mentor)",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         description="Search by name, email, or phone",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Page number for pagination",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User list",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array", @OA\Items(
 *                 @OA\Property(property="firstName", type="string", example="Ahmed"),
 *                 @OA\Property(property="lastName", type="string", example="Mohamed"),
 *                 @OA\Property(property="email", type="string", example="ahmed@example.com"),
 *                 @OA\Property(property="gender", type="string", example="male"),
 *                 @OA\Property(property="phone", type="string", example="01012345678"),
 *                 @OA\Property(property="role", type="string", example="User"),
 *                 @OA\Property(property="haveCompany", type="boolean", example=true),
 *                 @OA\Property(property="imgUrl", type="string", example="https://example.com/image.jpg"),
 *                 @OA\Property(property="createdAt", type="string", format="date-time", example="2025-07-09T10:00:00")
 *             )),
 *             @OA\Property(property="meta", type="object",
 *                 @OA\Property(property="currentPage", type="integer", example=1),
 *                 @OA\Property(property="lastPage", type="integer", example=3),
 *                 @OA\Property(property="total", type="integer", example=25),
 *                 @OA\Property(property="path", type="string", example="http://api.example.com/api/users"),
 *                 @OA\Property(property="links", type="object",
 *                     @OA\Property(property="first", type="string"),
 *                     @OA\Property(property="last", type="string"),
 *                     @OA\Property(property="prev", type="string", nullable=true),
 *                     @OA\Property(property="next", type="string", nullable=true)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Access forbidden",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Access forbidden / غير مصرح بالدخول")
 *         )
 *     )
 * )
 */
    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
     public function index(Request $request)
    {
       $user = Auth::user();

     if (!$user->ableTo('users.index')) {
        return $this->forbidden(__('api.forbidden'));
     }

    $result = $this->userService->index($request->all(), $user);

    return $this->success($result['data'], [
        'meta' => $result['meta']
    ]);

    }

    /**
 * @OA\Get(
 *     path="/api/users/{id}",
 *     summary="Get a specific user's information",
 *     description="Returns the profile data of the specified user including their role",
 *     operationId="getUserById",
 *     tags={"Users"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User data",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="firstName", type="string"),
 *                 @OA\Property(property="lastName", type="string"),
 *                 @OA\Property(property="email", type="string"),
 *                 @OA\Property(property="gender", type="string"),
 *                 @OA\Property(property="phone", type="string"),
 *                 @OA\Property(property="role", type="string"),
 *                 @OA\Property(property="imgUrl", type="string", example="https://example.com/image.jpg"),
 *                 @OA\Property(property="haveCompany", type="boolean"),
 *                 @OA\Property(property="createdAt", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="User not found")
 *         )
 *     )
 * )
 */
public function show($id)
{
    $user = User::find($id);

     if (!$user->ableTo('users.show')) {
            return $this->forbidden(__('api.forbidden'));
        }

        $user = $this->userService->show($id);
        if (!$user) {
            return $this->notFound(__('api.user_not_found'));
        }
   return $this->success(new UserResource($user));
}
/**
 * @OA\Post(
 *     path="/api/users",
 *     summary="Create a new user",
 *     description="Admins can create a new user with basic profile and role. Sends a welcome email.",
 *     tags={"Users"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"firstName", "lastName", "email", "phone", "gender", "password", "password_confirmation", "role"},
 *             @OA\Property(property="firstName", type="string", example="Ahmed"),
 *             @OA\Property(property="lastName", type="string", example="Ali"),
 *             @OA\Property(property="email", type="string", example="ahmed@example.com"),
 *             @OA\Property(property="phone", type="string", example="01012345678"),
 *             @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male"),
 *             @OA\Property(property="password", type="string", format="password", example="secret123"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123"),
 *             @OA\Property(property="role", type="integer", example=2),
 *             @OA\Property(property="imgUrl", type="string", example="https://example.com/uploads/avatar.jpg"),
 *             @OA\Property(property="createdAt", type="string", format="date-time", example="2025-07-15 09:30:00"),
 *             @OA\Property(property="status", type="string", example="active")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User created successfully."),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="firstName", type="string", example="Ahmed"),
 *                 @OA\Property(property="lastName", type="string", example="Ali"),
 *                 @OA\Property(property="email", type="string", example="ahmed@example.com"),
 *                 @OA\Property(property="phone", type="string", example="01012345678"),
 *                 @OA\Property(property="role", type="string", example="User"),
 *                 @OA\Property(property="haveCompany", type="boolean", example=false),
 *                 @OA\Property(property="imgUrl", type="string", example="https://example.com/uploads/avatar.jpg"),
 *                 @OA\Property(property="createdAt", type="string", format="date-time", example="2025-07-15 09:30:00"),
 *                 @OA\Property(property="status", type="string", example="active")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Only admins can create users"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */

public function store(StoreUserApiRequest $request)
{
    $user = Auth::user();
    if (!$user->ableToAny(['users.create', 'users.store'])) {
            return $this->forbidden(__('api.forbidden'));
    }

    $result = $this->userService->store($request->all());
     if (!$result['success']) {
        return $this->error($result['message'], $result['status'] ?? 500);
     }

   return $this->created(new UserResource($result['user']), __('api.user_created'), [], 201);
}

/**
 * @OA\Put(
 *     path="/api/users/{id}",
 *     summary="Update an existing user",
 *     description="Admins can update a user's profile information and role.",
 *     tags={"Users"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID to update",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="firstName", type="string", example="Ahmed"),
 *             @OA\Property(property="lastName", type="string", example="Ali"),
 *             @OA\Property(property="email", type="string", example="ahmed@example.com"),
 *             @OA\Property(property="phone", type="string", example="01012345678"),
 *             @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male"),
 *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123"),
 *             @OA\Property(property="role", type="integer", example=2),
 *             @OA\Property(property="haveCompany", type="boolean", example=false),
 *             @OA\Property(property="imgUrl", type="string", example="https://example.com/uploads/avatar.jpg"),
 *             @OA\Property(property="createdAt", type="string", format="date-time", example="2025-07-15 09:30:00"),
 *             @OA\Property(property="status", type="string", example="active")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User updated successfully."),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="firstName", type="string", example="Ahmed"),
 *                 @OA\Property(property="lastName", type="string", example="Ali"),
 *                 @OA\Property(property="email", type="string", example="ahmed@example.com"),
 *                 @OA\Property(property="phone", type="string", example="01012345678"),
 *                 @OA\Property(property="role", type="string", example="Manager")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Only admins can update users"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found"
 *     )
 * )
 */

public function update(UpdateUserApiRequest $request,$id)
{
  $auth = Auth::user();

    if (!$auth->ableToAny(['users.edit', 'users.update'])) {
        return $this->forbidden(__('api.forbidden'));
    }

    $user = User::find($id);
    if (!$user) {
        return $this->notFound(__('api.user_not_found'));
    }

    $result = $this->userService->update($user, $request->all());

    if (!$result['success']) {
        return $this->error($result['message'], $result['status'] ?? 500);
    }

    return $this->success(new UserResource($result['user']),[], __('api.user_updated'));
}

/**
 * @OA\Patch(
 *     path="/api/users/{id}/deactivate",
 *     summary="Deactivate a user account",
 *     description="Deactivates a user account by setting status to 'inactive'. Admins only. Cannot deactivate self or super_admin.",
 *     operationId="deactivateUser",
 *     tags={"Users"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the user to deactivate",
 *         @OA\Schema(type="integer", example=5)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User deactivated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User has been deactivated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden (e.g. deactivating self or super_admin)",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="You cannot deactivate your own account.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="User not found")
 *         )
 *     )
 * )
 */

public function deactivate($id)
{
     $user = Auth::user();
      if (!$user->ableToAny(['users.edit', 'users.update'])) {
        return $this->forbidden();
     }

    $result = $this->userService->deactivateUser($user, $id);
    if (!$result['success']) {
        return $this->error($result['message'], $result['status'] ?? 403);
    }

    return $this->success(null, [], __('api.user_deactivated'));
}



}
