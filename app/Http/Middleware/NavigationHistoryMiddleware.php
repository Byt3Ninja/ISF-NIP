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

        // Skip if title should be ignored
        if ($title === null) {
            return;
        }

        // Dashboard URL
        try {
            $dashboardUrl = route('core.dashboard_view');
        } catch (\Exception) {
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

        // Routes that always reset the trail to Dashboard > current page only
        $resetRoutes = ['service_provider.profile', 'service_provider.reviews'];

        // Check if navigation is from menu (has ?nav=menu parameter)
        $isFromMenu = $request->query('nav') === 'menu' || in_array($routeName, $resetRoutes);

        if (in_array($routeName, $resetRoutes)) {
            session(['_previous_url' => $dashboardUrl]);
        }

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

        // Get group parent entry if exists
        $groupParentEntry = $this->getGroupParentEntry($routeName);

        if ($isFromMenu) {
            // From menu: Reset to Dashboard / (group parent if any) / Current Page
            $trail = [[
                'url' => $dashboardUrl,
                'route' => 'core.dashboard_view',
                'title' => 'dashboard',
            ]];
            if ($groupParentEntry) {
                $trail[] = $groupParentEntry;
            }
            $trail[] = $currentEntry;
        } else {
            // From internal button: Append to trail

            // Check if URL already exists in trail (user going back)
            $existingIndex = $this->findInTrail($trail, $currentUrl);

            if ($existingIndex !== false) {
                // User going back - truncate trail
                $trail = array_slice($trail, 0, $existingIndex + 1);
                // Re-translate title in case locale changed since last visit
                $trail[$existingIndex]['title'] = $title;
            } else {
                // Check for duplicate title (prevent same page twice)
                $lastItem = end($trail);
                if ($lastItem && $lastItem['title'] === $title) {
                    // Update URL but don't add new entry
                    $trail[count($trail) - 1] = $currentEntry;
                } else {
                    // Insert group parent if not already in trail
                    if ($groupParentEntry) {
                        $groupInTrail = array_search($groupParentEntry['title'], array_column($trail, 'title'));
                        if ($groupInTrail === false) {
                            $trail[] = $groupParentEntry;
                        }
                    }

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
     * Get breadcrumb entry for the group parent of a route, if any
     */
    private function getGroupParentEntry(?string $routeName): ?array
    {
        if (!$routeName) {
            return null;
        }

        $navConfig = config('navigation');
        if (!is_callable($navConfig)) {
            return null;
        }

        $navItems = $navConfig();

        foreach ($navItems as $section) {
            $children = $section['children'] ?? [];
            foreach ($children as $child) {
                if (($child['route'] ?? null) === $routeName && isset($child['group'])) {
                    $groupTitle = $child['group'];
                    // Find the parent item with this title
                    foreach ($children as $parent) {
                        if (($parent['title'] ?? null) === $groupTitle && isset($parent['route'])) {
                            try {
                                $company = auth()->user()?->company;
                                $parentUrl = $company
                                    ? route($parent['route'], $company->id)
                                    : route($parent['route']);
                            } catch (\Exception) {
                                return null;
                            }
                            return [
                                'url'   => $parentUrl,
                                'route' => $parent['route'],
                                'title' => $groupTitle,
                            ];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get title key for route from navigation config
     * Returns null if title should be ignored
     */
    private function getTitleForRoute(?string $routeName, Request $request): ?string
{
    // List of exact titles to ignore in breadcrumb (all lowercase)
    $ignoredTitles = [
        'logo', 'login', 'logout', 'auth', 'register',
        'password', 'reset', 'verify', 'sanctum',
        'api', 'csrf', 'token', 'page', 'lfm', 'unisharp', 'ar', 'en', 'save', 'update', 'delete', 'edit', 'create', 'show',
    ];

    // List of words - if title contains any of these, ignore it (all lowercase)
    $ignoredContains = [
        'logo organization', 'logo', 'Stop', 'Lfm', 'Unisharp', 'save', 'update', 'delete', 'edit', 'create', 'show',
    ];

    $title = null;

    if (!$routeName) {
        // Fallback: use last URL segment
        $segments = $request->segments();
        $lastSegment = end($segments) ?: 'page';
        $title = $this->humanize($lastSegment);
        
        // Pass to finalizer
        return $this->finalizeTitle($title, $ignoredTitles, $ignoredContains);
    }

    // Try to find title in navigation config
    $navConfig = config('navigation');
    if (is_callable($navConfig)) {
        $navItems = $navConfig();
        $foundTitle = $this->findTitleInNav($navItems, $routeName);
        if ($foundTitle) {
            // Pass to finalizer
            return $this->finalizeTitle($foundTitle, $ignoredTitles, $ignoredContains);
        }
    }

    // Fallback: extract from route name (e.g., 'users.edit' -> 'edit_user')
    $parts = explode('.', $routeName);
    if (count($parts) >= 2) {
        $title = $parts[1] . '_' . rtrim($parts[0], 's');
        return $this->finalizeTitle($title, $ignoredTitles, $ignoredContains);
    }

    $title = $this->humanize($routeName);
    return $this->finalizeTitle($title, $ignoredTitles, $ignoredContains);
}

/**
 * دالة جديدة مسؤولة عن الاستبدال والتحقق
 */
private function finalizeTitle($title, $ignoredTitles, $ignoredContains): ?string
{
    if (!$title) {
        return null;
    }

    $cleanTitle = strtolower(trim($title));
    $l = app()->getLocale();
    //dd($cleanTitle , $l);
    $customReplacements = [
        'index_slot'      => app()->getLocale() == 'en' ? 'Slots' : 'المواعيد',
        'sessions_list_detailed_mentorship' => 'Tasks_List',
        'index_survey' => 'Surveys',
        'index_mentorshiptask' => app()->getLocale() == 'en' ? 'Task' : 'المهمة',
        'profile_edit_user' => app()->getLocale() == 'en' ? 'Edit Profile' : 'تعديل البيانات الشخصية',
        'index_organization-type' => app()->getLocale() == 'en' ? 'Organization Types' : 'أنواع المؤسسات',
        'index_country-management' => app()->getLocale() == 'en' ? 'Manage Countries' : 'الدول المتاحة',
        'view_overview_mine_quizze' => app()->getLocale() == 'en' ? 'Quiz Details' : 'تفاصيل الاختبار',
        'view_response_mine_quizze' => app()->getLocale() == 'en' ? 'Quiz Responses' : 'إجابات الاختبار',
        'results_view_screening' => app()->getLocale() == 'en' ? 'Final Screening Results' : 'نتائج الفحص النهائي',
        'view_cycle' => app()->getLocale() == 'en' ? 'Form' : 'النموذج',
        'index_form_submission_detail' => app()->getLocale() == 'en' ? 'View Form Submission' : 'تقديم النماذج',
        'list_mine_cycle' => app()->getLocale() == 'en' ? 'My Submission' : 'عرض مشاركتى',
        'render_view_form' => app()->getLocale() == 'en' ? 'Form Details' : 'تفاصيل النموذج',
        'organization_manage_setting' => app()->getLocale() == 'en' ? 'Org Manage Settings' : 'إدارة إعدادات المؤسسة',
        'questions_manage_assessment' => app()->getLocale() == 'en' ? 'Manage Assessment' : 'إدارة التقييم',
        'attendance_offline_view_training' => app()->getLocale() == 'en' ? 'Offline View Training' : 'حضور التدريب',
        'profile_view_client' => app()->getLocale() == 'en' ? 'Client Profile' : 'الملف الشخصي للعميل',
        
    ];
    if (isset($customReplacements[$cleanTitle])) {

        $title = $customReplacements[$cleanTitle];
    }
    if ($this->shouldIgnoreTitle($title, $ignoredTitles, $ignoredContains)) {
        return null;
    }

    return $title;
}

    /**
     * Check if title should be ignored
     */
    private function shouldIgnoreTitle(string $title, array $ignoredTitles, array $ignoredContains): bool
    {
        $lowerTitle = strtolower(trim($title));

        // Exact match check
        if (in_array($lowerTitle, $ignoredTitles)) {
            return true;
        }

        // Contains check (compare lowercase to lowercase)
        foreach ($ignoredContains as $word) {
            if (str_contains($lowerTitle, strtolower($word))) {
                return true;
            }
        }

        return false;
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
            'dashboard/notifications/*',
            'dashboard/files/*', // confidential file downloads are not pages
            // Static files and images
            '*/logo/*',
            'organizations/logo/*',
            'images/*',
            'storage/*',
            'uploads/*',
            'assets/*',
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

        // Skip image/file requests
        $extension = pathinfo($request->path(), PATHINFO_EXTENSION);
        $ignoredExtensions = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'css', 'js', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        if (in_array(strtolower($extension), $ignoredExtensions)) {
            return true;
        }

        return false;
    }
}

