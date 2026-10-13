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

        // Enforce token expiration. This mirrors Laravel Sanctum's own Guard
        // logic, which this custom middleware would otherwise bypass:
        //  1. The global lifespan from config('sanctum.expiration') measured
        //     from the token's created_at (in minutes).
        //  2. Any explicit per-token expires_at timestamp.
        if (self::tokenHasExpired($tokenModel)) {
            // Revoke the stale token so it cannot be reused.
            $tokenModel->delete();

            return $this->unauthorized(__('api.token_expired'));
        }

        // Authenticate the user
        Auth::login($tokenModel->tokenable);

        return $next($request);
    }

    /**
     * Determine whether the given personal access token has expired, applying
     * both the global Sanctum lifespan and any per-token expiry timestamp.
     */
    public static function tokenHasExpired(PersonalAccessToken $token): bool
    {
        // Per-token expiry, when explicitly set on the token.
        if ($token->expires_at && $token->expires_at->isPast()) {
            return true;
        }

        // Global lifespan (in minutes) measured from token creation.
        $expiration = config('sanctum.expiration');

        if ($expiration && $token->created_at
            && $token->created_at->lte(now()->subMinutes($expiration))) {
            return true;
        }

        return false;
    }
}
