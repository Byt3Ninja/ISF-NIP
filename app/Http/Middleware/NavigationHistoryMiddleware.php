<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NavigationHistoryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentUrl = $request->url();

        // Skip tracking for ignored paths
        if (!$this->isIgnoredPath($request)) {
            // Get history stack from session
            $history = session('_navigation_history', []);
            
            // Ensure it's an array
            if (!is_array($history)) {
                $history = [];
            }

            // Check if current URL already exists in history
            $key = array_search($currentUrl, $history);
            
            if ($key !== false) {
                // URL exists in history - user is going back
                // Truncate history to this point (don't add it again)
                $history = array_slice($history, 0, $key + 1);
            } else {
                // New URL - add to history
                $history[] = $currentUrl;
                
                // Keep only last 10 URLs to prevent session bloat
                if (count($history) > 10) {
                    array_shift($history);
                }
            }
            
            session(['_navigation_history' => $history]);

            // Set previous URL for easy access (second-to-last in history)
            if (count($history) >= 2) {
                session(['_previous_url' => $history[count($history) - 2]]);
            } else {
                session(['_previous_url' => null]);
            }
        }

        return $next($request);
    }

    /**
     * Check if the path should be ignored from history tracking
     */
    private function isIgnoredPath(Request $request): bool
    {
        $ignoredPaths = [
            'api/',
            'sanctum/',
            'logout',
        ];

        foreach ($ignoredPaths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }
}

