<?php

namespace App\Http\Middleware;

use App\Support\Google2FAAuthenticator;
use Closure;
use Illuminate\Support\Facades\RateLimiter;

class LoginSecurityMiddleware
{
    /**
     * Wrong one-time passwords a user may submit per minute. Any route behind this
     * middleware accepts the OTP, so the limit is per user rather than per URL.
     */
    private const MAX_OTP_ATTEMPTS = 6;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $authenticator = app(Google2FAAuthenticator::class)->boot($request);
        $attemptKey = '2fa-otp:' . $request->user()?->id;
        $submittedOtp = $request->filled(config('google2fa.otp_input'));

        if ($submittedOtp && RateLimiter::tooManyAttempts($attemptKey, self::MAX_OTP_ATTEMPTS)) {
            abort(429);
        }

        if ($authenticator->isAuthenticated()) {
            if ($submittedOtp) {
                RateLimiter::clear($attemptKey);
            }

            return $next($request);
        }

        if ($submittedOtp) {
            RateLimiter::hit($attemptKey, 60);
        }

        return $authenticator->makeRequestOneTimePasswordResponse();
    }
}
