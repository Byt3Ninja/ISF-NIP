<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        // Swallow double-submits (double click / repeated request) so one
        // submission never produces two emails.
        $lockKey = 'password-reset-request:' . sha1(strtolower(trim($request->email)));
        if (! Cache::add($lockKey, true, 10)) {
            return $this->sendResetLinkResponse($request, Password::RESET_LINK_SENT);
        }

        DB::table('password_resets')->where('email', $request->email)->delete();

        // Send the new reset link
        $response = Password::sendResetLink(
            $request->only('email')
        );

        if ($response != Password::RESET_LINK_SENT) {
            Cache::forget($lockKey);
        }

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    protected function sendResetLinkResponse(Request $request, $response)
    {
        return back()->with('status', trans($response));
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return back()->withErrors(['email' => trans($response)]);
    }
}
