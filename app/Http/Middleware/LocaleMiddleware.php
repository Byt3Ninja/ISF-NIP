<?php

namespace App\Http\Middleware;

use Closure;

class LocaleMiddleware
{
    public function handle($request, Closure $next, ...$locales)
    {
        $locale = $request->header('Accept-Language') ?? config('app.locale');
        
        if (in_array($locale, $locales)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
