<?php

namespace App\Http\Middleware;

use Closure;
use App\SurveyResponses;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class refreshMySurveys
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if( auth()->check() ) { SurveyResponses::assign_surveys(); }
        return $next($request);
    }
}
