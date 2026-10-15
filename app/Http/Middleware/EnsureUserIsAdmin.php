<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to authenticated administrators only.
 *
 * Used to gate sensitive surfaces such as the Swagger API documentation, which
 * must never be reachable by anonymous or non-admin users.
 */
class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $request->expectsJson()
                ? abort(401)
                : redirect()->route('login');
        }

        if (!$user->hasRole('admin')) {
            abort(403);
        }

        return $next($request);
    }
}
