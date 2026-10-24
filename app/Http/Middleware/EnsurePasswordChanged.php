<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePasswordChanged
{
    /**
     * Routes that an authenticated user with must_change_password=true is still
     * allowed to reach without being redirected to the profile page.
     */
    private const ALLOWED_ROUTE_NAMES = [
        'user.profile_Edit',
        'user.account_update_password',
        'user.profile_update_my_password',
        'user.notification_settings_update',
        'logout',
    ];

    private const ALLOWED_PATH_PREFIXES = [
        'lang/',
        'logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check() || ! Auth::user()->must_change_password) {
            return $next($request);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return $next($request);
        }

        $routeName = optional($request->route())->getName();
        if (in_array($routeName, self::ALLOWED_ROUTE_NAMES, true)) {
            return $next($request);
        }

        $path = ltrim($request->path(), '/');
        foreach (self::ALLOWED_PATH_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $next($request);
            }
        }

        return redirect()
            ->route('user.profile_Edit')
            ->with('warning', __('admin.Please_change_your_password_after_first_login'));
    }
}
