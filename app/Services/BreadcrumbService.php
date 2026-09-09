<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class BreadcrumbService
{
    /**
     * Cached navigation config
     */
    protected ?array $navigationConfig = null;

    /**
     * Cached flattened navigation items (route => item mapping)
     */
    protected ?array $flattenedNavigation = null;

    /**
     * Get the breadcrumb trail for the current route
     * Uses session-based trail for accurate navigation tracking
     *
     * @return array<array{url: string, label: string, active: bool}>
     */
    public function getTrail(): array
    {
        // Get session-based breadcrumb trail
        $sessionTrail = session('_breadcrumb_trail', []);

        if (!empty($sessionTrail)) {
            return $this->buildFromSessionTrail($sessionTrail);
        }

        // Fallback to config-based if no session trail
        $currentRoute = Route::currentRouteName();

        if (!$currentRoute) {
            return $this->buildFromUrl();
        }

        return $this->buildFromRoute($currentRoute);
    }

    /**
     * Build breadcrumb from session trail
     *
     * @param array $sessionTrail
     * @return array
     */
    protected function buildFromSessionTrail(array $sessionTrail): array
    {
        $breadcrumbs = [];

        foreach ($sessionTrail as $index => $item) {
            $isLast = ($index === count($sessionTrail) - 1);

            $breadcrumbs[] = [
                'url' => $item['url'],
                'label' => $this->getLabel($item['title']),
                'active' => $isLast,
            ];
        }

        return $this->cleanTrail($breadcrumbs);
    }

    /**
     * Build breadcrumb trail from route name
     *
     * @param string $routeName
     * @return array
     */
    public function buildFromRoute(string $routeName): array
    {
        $trail = [];

        // Always start with Home/Dashboard
        $trail[] = [
            'url' => route('core.dashboard_view'),
            'label' => $this->getLabel('dashboard'),
            'active' => false,
        ];

        // Find the current route in navigation config
        $navItem = $this->findNavItem($routeName);

        if ($navItem) {
            // Build hierarchy from navigation config
            $hierarchy = $this->buildHierarchy($navItem, $routeName);

            foreach ($hierarchy as $item) {
                $trail[] = $item;
            }
        } else {
            // Fallback: generate from route name pattern
            $trail = array_merge($trail, $this->generateFromRouteName($routeName));
        }

        // Mark the last item as active
        if (count($trail) > 0) {
            $trail[count($trail) - 1]['active'] = true;
        }

        // Remove duplicate consecutive items and ensure dashboard is not duplicated
        return $this->cleanTrail($trail);
    }

    /**
     * Build breadcrumb from URL path segments (fallback)
     *
     * @return array
     */
    protected function buildFromUrl(): array
    {
        $trail = [];
        $path = Request::path();
        $segments = explode('/', $path);

        // Always start with Home/Dashboard
        $trail[] = [
            'url' => route('core.dashboard_view'),
            'label' => $this->getLabel('dashboard'),
            'active' => false,
        ];

        // Skip 'dashboard' segment if present
        if (isset($segments[0]) && $segments[0] === 'dashboard') {
            array_shift($segments);
        }

        $currentPath = '/dashboard';
        foreach ($segments as $index => $segment) {
            if (empty($segment) || is_numeric($segment)) {
                continue;
            }

            $currentPath .= '/' . $segment;
            $isLast = $index === count($segments) - 1;

            $trail[] = [
                'url' => url($currentPath),
                'label' => $this->humanize($segment),
                'active' => $isLast,
            ];
        }

        return $trail;
    }

    /**
     * Get the previous URL for the back button
     *
     * @return string|null
     */
    public function getPreviousUrl(): ?string
    {
        // First try session-based navigation history
        $previousUrl = session('_previous_url');

        if ($previousUrl && $previousUrl !== Request::url()) {
            return $previousUrl;
        }

        // Fallback to Laravel's previous URL
        $laravelPrevious = url()->previous();

        // Don't return current URL as previous
        if ($laravelPrevious && $laravelPrevious !== Request::url()) {
            return $laravelPrevious;
        }

        // Final fallback: dashboard
        try {
            $dashboardUrl = route('core.dashboard_view');
            if ($dashboardUrl !== Request::url()) {
                return $dashboardUrl;
            }
        } catch (\Exception $e) {
            // Route doesn't exist
        }

        return null;
    }

    /**
     * Check if the back button should be shown
     *
     * @return bool
     */
    public function hasBackUrl(): bool
    {
        return $this->getPreviousUrl() !== null;
    }

    /**
     * Get translated label for a navigation title key
     *
     * @param string $titleKey
     * @return string
     */
    public function getLabel(string $titleKey): string
    {
        // Normalize legacy session entries that stored the rendered label
        // (e.g. "My Profile") instead of a snake_case translation key,
        // so they still resolve to the right lang key regardless of locale.
        // Only ASCII labels are converted: Str::snake() strips whitespace,
        // which would collapse already-translated non-ASCII titles (e.g. Arabic).
        if (!preg_match('/[^\x00-\x7F]/', $titleKey) && preg_match('/[A-Z\s]/', $titleKey)) {
            $titleKey = Str::snake(trim($titleKey));
        }

        // Try breadcrumb translations first
        $breadcrumbKey = 'breadcrumb.' . $titleKey;
        $translated = __($breadcrumbKey);

        if ($translated !== $breadcrumbKey) {
            return $translated;
        }

        // Fallback to admin translations
        $adminKey = 'admin.' . $titleKey;
        $translated = __($adminKey);

        if ($translated !== $adminKey) {
            return $translated;
        }

        // Final fallback: humanize the key
        return $this->humanize($titleKey);
    }

    /**
     * Get navigation config (cached)
     *
     * @return array
     */
    protected function getNavigationConfig(): array
    {
        if ($this->navigationConfig === null) {
            $configFunction = config('navigation');
            $this->navigationConfig = is_callable($configFunction) ? $configFunction() : [];
        }

        return $this->navigationConfig;
    }

    /**
     * Get flattened navigation (route => item mapping)
     *
     * @return array
     */
    protected function getFlattenedNavigation(): array
    {
        if ($this->flattenedNavigation === null) {
            $this->flattenedNavigation = [];
            $config = $this->getNavigationConfig();

            $this->flattenNavigation($config);
        }

        return $this->flattenedNavigation;
    }

    /**
     * Recursively flatten navigation config
     *
     * @param array $items
     * @param string|null $parentKey
     * @param string|null $parentTitle
     */
    protected function flattenNavigation(array $items, ?string $parentKey = null, ?string $parentTitle = null): void
    {
        foreach ($items as $key => $item) {
            // Handle grouped structure (admin menu)
            if (isset($item['children'])) {
                $groupTitle = $item['title'] ?? $parentTitle;
                foreach ($item['children'] as $child) {
                    if (isset($child['route'])) {
                        $this->flattenedNavigation[$child['route']] = array_merge($child, [
                            'parent_key' => $key,
                            'parent_title' => $groupTitle,
                        ]);
                    }
                }
            }
            // Handle flat structure (mentor, judger, etc.)
            elseif (isset($item['route'])) {
                $this->flattenedNavigation[$item['route']] = array_merge($item, [
                    'parent_key' => $parentKey,
                    'parent_title' => $parentTitle,
                ]);
            }
            elseif (isset($item['url'])) {
                // URL-based items (for non-named routes)
                $urlKey = 'url:' . $item['url'];
                $this->flattenedNavigation[$urlKey] = array_merge($item, [
                    'parent_key' => $parentKey,
                    'parent_title' => $parentTitle,
                ]);
            }
        }
    }

    /**
     * Find navigation item by route name
     *
     * @param string $routeName
     * @return array|null
     */
    protected function findNavItem(string $routeName): ?array
    {
        $flattened = $this->getFlattenedNavigation();
        return $flattened[$routeName] ?? null;
    }

    /**
     * Build hierarchy from navigation item
     *
     * @param array $navItem
     * @param string $currentRoute
     * @return array
     */
    protected function buildHierarchy(array $navItem, string $currentRoute): array
    {
        $hierarchy = [];
        $flattened = $this->getFlattenedNavigation();

        // Check if this item has a group (parent)
        if (isset($navItem['group'])) {
            // Find parent item by title matching group
            $parentItem = $this->findParentByGroup($navItem['group']);

            if ($parentItem) {
                // Check if parent also has a parent
                if (isset($parentItem['group'])) {
                    $grandParent = $this->findParentByGroup($parentItem['group']);
                    if ($grandParent && isset($grandParent['route'])) {
                        $hierarchy[] = [
                            'url' => $this->getRouteUrl($grandParent['route']),
                            'label' => $this->getLabel($grandParent['title']),
                            'active' => false,
                        ];
                    }
                }

                // Add parent to hierarchy
                $parentUrl = null;
                if (isset($navItem['group_route'])) {
                    $parentUrl = $this->getRouteUrl($navItem['group_route']);
                } elseif (isset($parentItem['route'])) {
                    $parentUrl = $this->getRouteUrl($parentItem['route']);
                }

                if ($parentUrl) {
                    $hierarchy[] = [
                        'url' => $parentUrl,
                        'label' => $this->getLabel($parentItem['title']),
                        'active' => false,
                    ];
                }
            }
        }

        // Add current item
        $hierarchy[] = [
            'url' => $this->getRouteUrl($currentRoute),
            'label' => $this->getLabel($navItem['title']),
            'active' => true,
        ];

        return $hierarchy;
    }

    /**
     * Find parent item by group name (matching title)
     *
     * @param string $groupName
     * @return array|null
     */
    protected function findParentByGroup(string $groupName): ?array
    {
        $flattened = $this->getFlattenedNavigation();

        foreach ($flattened as $route => $item) {
            if (isset($item['title']) && $item['title'] === $groupName) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Generate breadcrumb from route name pattern
     *
     * @param string $routeName
     * @return array
     */
    protected function generateFromRouteName(string $routeName): array
    {
        $trail = [];
        $parts = explode('.', $routeName);

        if (count($parts) < 2) {
            return $trail;
        }

        // Get the resource name (first part)
        $resourceName = $parts[0];
        $action = $parts[1] ?? 'index';

        // Try to generate resource list URL
        try {
            $resourceUrl = route($resourceName . '.index');
            $trail[] = [
                'url' => $resourceUrl,
                'label' => $this->getLabel($resourceName),
                'active' => false,
            ];
        } catch (\Exception $e) {
            // Route doesn't exist, use humanized name
            $trail[] = [
                'url' => '#',
                'label' => $this->humanize($resourceName),
                'active' => false,
            ];
        }

        // Add current action if not index
        if ($action !== 'index') {
            $trail[] = [
                'url' => Request::url(),
                'label' => $this->humanize($action),
                'active' => true,
            ];
        }

        return $trail;
    }

    /**
     * Get URL for a route name
     *
     * @param string $routeName
     * @return string
     */
    protected function getRouteUrl(string $routeName): string
    {
        try {
            // Get current route parameters to pass to the route
            $currentParams = Route::current()?->parameters() ?? [];
            return route($routeName, $currentParams);
        } catch (\Exception $e) {
            // Route requires parameters we don't have
            try {
                return route($routeName);
            } catch (\Exception $e) {
                return '#';
            }
        }
    }

    /**
     * Clean the trail (remove duplicates and fix active states)
     *
     * @param array $trail
     * @return array
     */
    protected function cleanTrail(array $trail): array
    {
        $cleaned = [];
        $previousLabel = null;

        foreach ($trail as $item) {
            // Skip duplicate consecutive items
            if ($item['label'] === $previousLabel) {
                continue;
            }

            $cleaned[] = $item;
            $previousLabel = $item['label'];
        }

        // Ensure only the last item is active
        foreach ($cleaned as $index => &$item) {
            $item['active'] = ($index === count($cleaned) - 1);
        }

        return $cleaned;
    }

    /**
     * Convert snake_case or kebab-case to human readable
     *
     * @param string $string
     * @return string
     */
    protected function humanize(string $string): string
    {
        $string = str_replace(['_', '-'], ' ', $string);
        return Str::title($string);
    }
}
