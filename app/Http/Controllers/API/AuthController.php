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
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
      public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-login')->plainTextToken;

        $user->img_url = $user->img_url ? Storage::disk('public_uploads')->url($user->img_url) : null;

        return response()->json([
            'status'  => true,
            'message' => 'Login Successfully',
            'token'   => $token,
            'data'    => [
                'id'             => $user->id,
                'first_name'     => $user->first_name_en,
                'last_name'      => $user->last_name_en,
                'email'          => $user->email,
                'phone'          => $user->country_code . $user->phone,
                'img_url'        => $user->img_url,
            ],
        ]);
    }

    public function register(RegisterUserRequest  $request)
{
    
    $data = $request->validated();
    $settings = Settings::find(1);

    $userData = [
        'first_name_en' => $data['first_name_en'],
        'last_name_en'  => $data['last_name_en'],
        'first_name_ar' => $data['first_name_en'],
        'last_name_ar'  => $data['last_name_en'],
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
            'message' => 'Role not found'
        ], 400);
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
        'message' => 'Registeration Successfully',
        'token' => $token,
        'data' => [
            'first_name' => $user->first_name_en,
            'last_name'  => $user->last_name_en,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'role'       => $data['role'],
        ]
    ],201);
}
}
