<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * C39: signs out a service-provider applicant whose registration was rejected,
 * whatever way they signed in (password, magic link, social, an open session).
 */
class BlockRejectedProviderApplicants
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isRejectedProviderApplicant()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', __('auth.provider_registration_rejected'));
        }

        return $next($request);
    }
}
