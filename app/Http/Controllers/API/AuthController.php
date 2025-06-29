<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
}
