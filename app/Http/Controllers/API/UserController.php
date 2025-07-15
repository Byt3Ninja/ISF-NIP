<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Mail\WelcomeUserMail;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreUserApiRequest;
use App\Http\Requests\UpdateUserApiRequest;
use Symfony\Component\HttpFoundation\Response;

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
     public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->ableTo('users.index')) {
            return response()->json([
                'success' => false,
                'message' => __('api.forbidden')
            ], Response::HTTP_FORBIDDEN);
        }

        $query = User::query();

        if ($user->hasRole('OrganizationManager')) {
            $query->where('organization_id', $user->organization_id);
        }

       if ($request->filled('gender')) {
        $gender = strtolower($request->gender);
        if (in_array($gender, ['male', 'female'])) {
            $query->where('gender', $gender);
        }
     }

    if ($request->has('haveCompany')) {           
        $value = filter_var(
            $request->haveCompany,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        if (! is_null($value)) {
            $query->where('HaveCompany', $value);
        }
      }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name_en', 'like', "%$search%")
                  ->orWhere('last_name_en', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        $query->orderBy('created_at', 'desc');

        $users = $query->paginate(10);

        $data = UserResource::collection($users);

        return $this->success($data,['meta' => [
                'currentPage' => $users->currentPage(),
                'lastPage' => $users->lastPage(),
                'total' => $users->total(),
                'path' => $users->path(),
                'links' => [
                    'first' => $users->url(1),
                    'last' => $users->url($users->lastPage()),
                    'prev' => $users->previousPageUrl(),
                    'next' => $users->nextPageUrl(),
                ]
            ]]);
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

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => __('api.user_not_found'),
        ], 404);
    }

     if (!$user->ableTo('users.show')) {
            return response()->json([
                'success' => false,
                'message' => __('api.forbidden')
            ], Response::HTTP_FORBIDDEN);
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
    $user = auth()->user();
    if (!$user->ableToAny(['users.create', 'users.store'])) {
            return response()->json([
                'success' => false,
                'message' => __('api.forbidden')
            ], Response::HTTP_FORBIDDEN);
    }

    $data = $request->validated();
    DB::beginTransaction();
    try {
        $user = User::create([
            'first_name_en' => $data['firstName'],
            'last_name_en' => $data['lastName'],
            'first_name_ar' => $data['firstName'],
            'last_name_ar' => $data['lastName'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'gender' => $data['gender'],
            'password' => Hash::make($data['password']),
        ]);

        $role = Role::find($data['role']);

        if (!$role) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('api.invalid_role'),
            ], 422);
        }

        DB::table('model_has_roles')->insert([
            'role_id'    => $role->id,
            'model_type' => User::class,
            'model_id'   => $user->id,
        ]);

        // Send welcome email
        Mail::to($user->email)->queue(new WelcomeUserMail($user, $data['password']));

        DB::commit();
        $user->refresh();
        return $this->created(new UserResource($user),__('api.user_created'));
    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);

        return response()->json([
            'success' => false,
            'message' => __('api.failed_to_create_user')
        ], 500);
    }
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
    $user = auth()->user();
    
    if (!$user->ableToAny(['users.edit', 'users.update'])) {
            return response()->json([
                'success' => false,
                'message' => __('api.forbidden')
            ], Response::HTTP_FORBIDDEN);
    }

    $data = $request->validated();
    DB::beginTransaction();
    try {
        $user = User::findOrFail($id);

        if (isset($data['firstName'])) {
            $user->first_name_en = $data['firstName'];
            $user->first_name_ar = $data['firstName'];
        }

        if (isset($data['lastName'])) {
            $user->last_name_en = $data['lastName'];
            $user->last_name_ar = $data['lastName'];
        }

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        if (isset($data['phone'])) {
            $user->phone = $data['phone'];
        }

        if (isset($data['gender'])) {
            $user->gender = $data['gender'];
        }

        if (isset($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if (isset($data['status'])) {
            $user->status = $data['status'];
        }

        $user->save();

        // Update role if provided
        if (isset($data['role'])) {
            DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->where('model_id', $user->id)
                ->delete();

            DB::table('model_has_roles')->insert([
                'role_id'    => $data['role'],
                'model_type' => User::class,
                'model_id'   => $user->id,
            ]);
        }
        DB::commit();

        return $this->success(new UserResource($user), __('api.updated'));
       }catch (\Throwable $e) {
         DB::rollBack();
         report($e);

        return response()->json([
            'success' => false,
            'message' => __('api.failed_to_update_user')
        ], 500);
    }
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
    $user = auth()->user();

    if (!$user->ableToAny(['users.edit', 'users.update'])) {
        return response()->json([
            'success' => false,
            'message' => __('api.forbidden'),
        ], Response::HTTP_FORBIDDEN);
    }

    if ($user->id == $id) {
        return response()->json([
            'success' => false,
            'message' => __('api.cannot_deactivate_self'),
        ], Response::HTTP_FORBIDDEN);
    }

    $user = User::findOrFail($id);

    if ($user->hasRole('Admin')) {
        return response()->json([
            'success' => false,
            'message' => __('api.cannot_deactivate_super_admin'),
        ], Response::HTTP_FORBIDDEN);
    }

    $user->status = 'inactive';
    $user->save();
    return $this->success(__('api.user_deactivated'));
    // return response()->json([
    //     'success' => true,
    //     'message' => __('api.user_deactivated'),
    // ], Response::HTTP_OK);
}



}
