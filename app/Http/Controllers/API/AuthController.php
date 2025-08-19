<?php

namespace App\Http\Controllers\API;

use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Http\Requests\RegisterUserRequest;

class AuthController extends Controller
{
    use ApiResponder;
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
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
    public function login(LoginRequest $request)
    {
        try{
            $user = $this->authService->attemptLogin($request->email, $request->password);
            $token = $this->authService->generateToken($user);
            return $this->responseWithToken($token,new AuthResource($user),__('api.login_success'), 200);
        }catch(\Exception $e){
            $data = json_decode($e->getMessage(), true);
            if($data['type'] == 'credential'){ 
                return $this->validationError($data['message']);
            }elseif($data['type'] == 'inactive'){
                return $this->forbidden($data['message']);
            }
            return $this->serverError(); 
        }

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
        try {
        $result = $this->authService->register($request->validated());
        return $this->created(new AuthResource($result['user']),__('api.registered_successfully'),['token' => $result['token']]);
       } catch (\Throwable $e) {
        report($e);
        return $this->serverError();
    }
    }
 /**
 * @OA\Post(
 *     path="/api/logout",
 *     summary="Logout the authenticated user",
 *     tags={"Authentication"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successfully logged out",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Logged out successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     ),
 *     @OA\Parameter(
 *         name="Authorization",
 *         in="header",
 *         required=true,
 *         description="Bearer token",
 *         @OA\Schema(type="string", example="Bearer eyJ0eXAiOiJKV1QiLCJh...")
 *     )
 * )
 */
     public function logout(Request $request)
    {
        return $this->authService->logout($request, $request->user());
    }
}
