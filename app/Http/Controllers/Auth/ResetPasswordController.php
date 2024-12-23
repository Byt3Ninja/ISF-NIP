<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function reset(Request $request)
    {
        $this->validate($request, $this->rules(), $this->validationErrorMessages());

        $email = $request->input('email');
        $token = $request->input('token');

        $resetEntry = DB::table('password_resets')->where('email', $email)->orderBy('created_at', 'desc')->first();

        if (!$resetEntry || !Hash::check($token, $resetEntry->token)) {
            return back()->withErrors(['email' => __('auth.expired_token')]);
        }

        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
    }

    protected function sendResetResponse(Request $request, $response)
    {
        return to_route('login')->with('status', trans($response));
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        return back()->withErrors(['email' => trans($response)]);
    }

 
}
