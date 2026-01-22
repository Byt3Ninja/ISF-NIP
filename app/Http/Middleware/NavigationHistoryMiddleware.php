<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

            // Track breadcrumb trail with route names and titles
            $this->trackBreadcrumbTrail($request, $currentUrl);
        }

        return $next($request);
    }

    /**
     * Track breadcrumb trail in session
     * - From menu: Dashboard / Current Page only (reset trail)
     * - From internal button: Dashboard / Page1 / Page2 / Page3 (append to trail)
     */
    private function trackBreadcrumbTrail(Request $request, string $currentUrl): void
    {
        $routeName = Route::currentRouteName();
        $title = $this->getTitleForRoute($routeName, $request);

        // Dashboard URL
        try {
            $dashboardUrl = route('core.dashboard_view');
        } catch (\Exception $e) {
            $dashboardUrl = url('/dashboard');
        }

        // Check if current page is dashboard
        $dashboardRoutes = ['core.dashboard_view'];
        $dashboardPaths = ['dashboard', 'dashboard/dashboard'];
        $isDashboard = in_array($routeName, $dashboardRoutes) || in_array($request->path(), $dashboardPaths);

        if ($isDashboard) {
            // On dashboard - reset trail
            $trail = [[
                'url' => $currentUrl,
                'route' => $routeName,
                'title' => 'dashboard',
            ]];
            session(['_breadcrumb_trail' => $trail]);
            return;
        }

        // Check if navigation is from menu (has ?nav=menu parameter)
        $isFromMenu = $request->query('nav') === 'menu';

        // Get current trail
        $trail = session('_breadcrumb_trail', []);

        if (!is_array($trail) || empty($trail)) {
            $trail = [[
                'url' => $dashboardUrl,
                'route' => 'core.dashboard_view',
                'title' => 'dashboard',
            ]];
        }

        // Ensure first item is dashboard
        if (($trail[0]['title'] ?? '') !== 'dashboard') {
            array_unshift($trail, [
                'url' => $dashboardUrl,
                'route' => 'core.dashboard_view',
                'title' => 'dashboard',
            ]);
        }

        // Create current page entry
        $currentEntry = [
            'url' => $currentUrl,
            'route' => $routeName,
            'title' => $title,
        ];

        if ($isFromMenu) {
            // From menu: Reset to Dashboard / Current Page only
            $trail = [
                [
                    'url' => $dashboardUrl,
                    'route' => 'core.dashboard_view',
                    'title' => 'dashboard',
                ],
                $currentEntry,
            ];
        } else {
            // From internal button: Append to trail

            // Check if URL already exists in trail (user going back)
            $existingIndex = $this->findInTrail($trail, $currentUrl);

            if ($existingIndex !== false) {
                // User going back - truncate trail
                $trail = array_slice($trail, 0, $existingIndex + 1);
            } else {
                // Check for duplicate title (prevent same page twice)
                $lastItem = end($trail);
                if ($lastItem && $lastItem['title'] === $title) {
                    // Update URL but don't add new entry
                    $trail[count($trail) - 1] = $currentEntry;
                } else {
                    // Add to trail
                    $trail[] = $currentEntry;

                    // Keep max 10 items (always keep dashboard)
                    while (count($trail) > 10) {
                        array_splice($trail, 1, 1);
                    }
                }
            }
        }

        session(['_breadcrumb_trail' => $trail]);
    }

    /**
     * Find URL in breadcrumb trail
     */
    private function findInTrail(array $trail, string $url): int|false
    {
        foreach ($trail as $index => $item) {
            if ($item['url'] === $url) {
                return $index;
            }
        }
        return false;
    }

    /**
     * Get title key for route from navigation config
     */
    private function getTitleForRoute(?string $routeName, Request $request): string
    {
        if (!$routeName) {
            // Fallback: use last URL segment
            $segments = $request->segments();
            $lastSegment = end($segments) ?: 'page';
            return $this->humanize($lastSegment);
        }

        // Try to find title in navigation config
        $navConfig = config('navigation');
        if (is_callable($navConfig)) {
            $navItems = $navConfig();
            $title = $this->findTitleInNav($navItems, $routeName);
            if ($title) {
                return $title;
            }
        }

        // Fallback: extract from route name (e.g., 'users.edit' -> 'edit_user')
        $parts = explode('.', $routeName);
        if (count($parts) >= 2) {
            return $parts[1] . '_' . rtrim($parts[0], 's');
        }

        return $this->humanize($routeName);
    }

    /**
     * Search navigation config for route title
     */
    private function findTitleInNav(array $items, string $routeName): ?string
    {
        foreach ($items as $item) {
            // Check if this item has the route
            if (isset($item['route']) && $item['route'] === $routeName) {
                return $item['title'] ?? null;
            }

            // Check children
            if (isset($item['children'])) {
                foreach ($item['children'] as $child) {
                    if (isset($child['route']) && $child['route'] === $routeName) {
                        return $child['title'] ?? null;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Convert string to human readable
     */
    private function humanize(string $string): string
    {
        return str_replace(['_', '-', '.'], ' ', $string);
    }

    /**
     * Check if the path should be ignored from history tracking
     */
    private function isIgnoredPath(Request $request): bool
    {
        $ignoredPaths = [
            'api/*',
            'sanctum/*',
            'logout',
            '_debugbar/*',
            'livewire/*',
        ];

        foreach ($ignoredPaths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        // Skip AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            return true;
        }

        return false;
    }
}

