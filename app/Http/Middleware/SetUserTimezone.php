<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetUserTimezone
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            // لو تريد ضبط التوقيت بناءً على المستخدم
            date_default_timezone_set(Auth::user()->timezone ?? 'Africa/Cairo'); 
            // dd(Auth::user()->timezone); // الآن يمكنك استخدامه
        } else {
            date_default_timezone_set('Africa/Cairo'); // ثابت لكل النظام
        }

        return $next($request);
    }
}

