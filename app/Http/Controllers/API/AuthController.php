<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Settings;
use App\UsersBio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login a user and return an access token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login Successfully"),
     *             @OA\Property(property="token", type="string", example="long-jwt-token-here"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="firstName", type="string", example="Ahmed"),
     *                 @OA\Property(property="lastName", type="string", example="Mohamed"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="phone", type="string", example="+201234567890"),
     *                 @OA\Property(property="imgUrl", type="string", example="https://example.com/image.jpg"),
     *  *              @OA\Property(property="role", type="string", example="user")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('api.incorrect_credentials')],
            ]);
        }

        $l = app()->getLocale();
        $token = $user->createToken('auth-login')->plainTextToken;
        $user->img_url = $user->img_url ? Storage::disk('public_uploads')->url($user->img_url) : null;

        return response()->json(['status'  => true, 'message' => __('api.login_success'), 'token'   => $token, 'data' => [
                'id' => $user->id,
                'firstName' => $user->{'first_name_' . $l},
                'lastName' => $user->{'last_name_' . $l},
                'email' => $user->email,
                'phone' => $user->country_code . $user->phone,
                'imgUrl' => $user->img_url,
                'role'   => $user->getRoleNames()->first(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={
     *                 "first_name_en","last_name_en","gender",
     *                 "phone","email","password","role"
     *             },
     *             @OA\Property(property="firstName", type="string", example="Ahmed"),
     *             @OA\Property(property="lastName", type="string", example="Mohamed"),
     *             @OA\Property(property="gender", type="string", example="male"),
     *             @OA\Property(property="phone", type="string", example="123456789"),
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="role", type="string", example="Mentor"),
     *             @OA\Property(property="title_en", type="string", example="Senior Engineer"),
     *             @OA\Property(property="userbio", type="string", example="Experienced mentor in tech industry."),
     *             @OA\Property(property="linkedin", type="string", example="https://linkedin.com/in/example"),
     *             @OA\Property(
     *                 property="expertise",
     *                 type="array",
     *                 @OA\Items(type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registeration Successfully"),
     *             @OA\Property(property="token", type="string", example="long-jwt-token-here"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="first_name", type="string", example="Ahmed"),
     *                 @OA\Property(property="last_name", type="string", example="Mohamed"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="phone", type="string", example="123456789"),
     *                 @OA\Property(property="role", type="string", example="Mentor")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found or validation error"
     *     )
     * )
     */
    public function register(RegisterUserRequest  $request)
    {
        $data = $request->validated();
        $settings = Settings::find(1);

        $userData = [
            'first_name_en' => $data['firstName'],
            'last_name_en'  => $data['lastName'],
            'first_name_ar' => $data['firstName'],
            'last_name_ar'  => $data['lastName'],
            'gender'        => $data['gender'],
            'phone'         => $data['phone'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'title_en'      => $data['title_en'] ?? null,
            'title_ar'      => $data['title_en'] ?? null,
        ];

        if ($settings && $settings->VFEmail != 1) {
            $userData['email_verified_at'] = now();
        }

        $user = User::create($userData);
        
        $role = Role::where('name', $data['role'])->first();
        if (!$role) {
            return response()->json([
                'status' => false,
                'message' => __('api.role_not_found')
            ], 404);
        }

        DB::table('model_has_roles')->insert([
            'role_id'    => $role->id,
            'model_type' => User::class,
            'model_id'   => $user->id,
        ]);

        if ($data['role'] === 'Mentor') {
            UsersBio::create([
                'user_id'  => $user->id,
                'bio_en'   => $data['userbio'] ?? null,
                'linkedin' => $data['linkedin'] ?? null,
            ]);

            if (!empty($data['expertise'])) {
                $expertiseInsert = collect($data['expertise'])->map(function ($expertiseId) use ($user) {
                    return [
                        'expertise_id' => $expertiseId,
                        'mentor_id' => $user->id,
                    ];
                })->toArray();

                DB::table('mentor_expertise')->insert($expertiseInsert);
            }
        }

        $token = $user->createToken('auth-register')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => __('api.registered_successfully'),
            'token' => $token,
            'data' => [
                'firstName' => $user->first_name_en,
                'lastName'  => $user->last_name_en,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'role'       => $data['role'],
            ]
        ],201);
    }
}
