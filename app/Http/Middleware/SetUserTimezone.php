<?php

namespace App\Http\Middleware;

use App\Services\TimezoneService;
use Closure;
use Illuminate\Support\Facades\Auth;

class SetUserTimezone
{
    public function handle($request, Closure $next)
    {
        // The user's own zone, else the platform zone admins configured
        // (GV-9566) -- it used to fall back to a hard-coded 'Africa/Cairo'.
        $userTimezone = TimezoneService::viewer(Auth::user()) ?? TimezoneService::platform();

        // Display-only: expose the user's timezone for views/formatters.
        // IMPORTANT: do NOT call date_default_timezone_set() here — mutating
        // PHP's global timezone makes Eloquent write created_at in the user's
        // wall-clock zone into a timezone-naive column, corrupting ordering.
        config(['app.user_timezone' => $userTimezone]);

        return $next($request);
    }
}
