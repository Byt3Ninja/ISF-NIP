<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetUserTimezone
{
    public function handle($request, Closure $next)
    {
        $defaultTimezone = 'Africa/Cairo';

        if (Auth::check()) {
            $userTimezone = Auth::user()->timezone;

            if (in_array($userTimezone, timezone_identifiers_list())) {
                date_default_timezone_set($userTimezone);
            } else {
                date_default_timezone_set($defaultTimezone);
            }
        } else {
            date_default_timezone_set($defaultTimezone);
        }

        return $next($request);
    }
}
