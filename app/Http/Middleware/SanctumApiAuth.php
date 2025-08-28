<?php

namespace App\Http\Middleware;

use Closure;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class SanctumApiAuth
{
    use ApiResponder;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return $this->unauthorized(__('api.unauthenticated'));
        }

        $tokenModel = PersonalAccessToken::findToken($accessToken);

        if (!$tokenModel || !$tokenModel->tokenable) {
            return $this->unauthorized(__('api.unauthenticated'));
        }

        // Authenticate the user
        Auth::login($tokenModel->tokenable);

        return $next($request);
    }
}
