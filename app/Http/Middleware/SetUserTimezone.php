<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetUserTimezone
{
    public function handle($request, Closure $next)
    {
        $defaultTimezone = 'Africa/Cairo';

        $userTimezone = $defaultTimezone;
        if (Auth::check()) {
            $tz = Auth::user()->timezone;
            if (in_array($tz, timezone_identifiers_list(), true)) {
                $userTimezone = $tz;
            }
        }

        // Display-only: expose the user's timezone for views/formatters.
        // IMPORTANT: do NOT call date_default_timezone_set() here — mutating
        // PHP's global timezone makes Eloquent write created_at in the user's
        // wall-clock zone into a timezone-naive column, corrupting ordering.
        config(['app.user_timezone' => $userTimezone]);

        return $next($request);
    }
}
