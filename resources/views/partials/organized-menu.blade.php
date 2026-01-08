@php
$navigationItems = [
  'dashboard' => [
    'title' => 'dashboard_and_analytics',
    'children' => [
      ['title' => 'dashboard', 'route' => 'core.dashboard_view'],
      ['title' => 'reports', 'route' => 'reports.list'],
      ['title' => 'activities', 'route' => 'activities.index'],
      ['title' => 'projects', 'route' => 'projects.index'],
    ],
  ],

  'programs' => [
    'title' => 'program_management',
    'children' => [
      ['title' => 'programs', 'route' => 'programlist.index'],
      ['title' => 'forms', 'route' => 'forms.index'],
      ['title' => 'screening', 'route' => 'screening.index'],
      ['title' => 'assessment', 'route' => 'assessments.index'],
      ['title' => 'training', 'route' => 'trainings.sessions_list'],
      ['title' => 'mentorship', 'route' => 'mentorships.requests_view'],
      ['title' => 'mentorship_sessions', 'route' => 'mentorships.sessions_list'],
      ['title' => 'tasks', 'route' => 'tasks.index'],
      ['title' => 'questions', 'route' => 'question-categories.index'],
      ['title' => 'quizzes', 'route' => 'quizzes.index'],
      ['title' => 'surveys', 'route' => 'surveys.index'],
    ],
  ],

  'people' => [
    'title' => 'people_and_organizations',
    'children' => [
      ['title' => 'organization_types', 'route' => 'organization-types.index'],
      ['title' => 'organizations', 'route' => 'organizations.index'],
      ['title' => 'users', 'route' => 'users.index'],
      ['title' => 'startups', 'route' => 'users.view_companies'],
      ['title' => 'judges', 'route' => 'users.judger'],
      ['title' => 'mentors', 'route' => 'mentors.list'],
      ['title' => 'service_providers', 'route' => 'services_providers.index'],
    ],
  ],

  'services' => [
    'title' => 'services_and_resources',
    'children' => [
      ['title' => 'services', 'route' => 'services.index'],
      ['title' => 'file_manager', 'route' => 'my_files.list'],
      ['title' => 'rooms', 'route' => 'rooms.index'],
      ['title' => 'timesheets', 'route' => 'TimeSheet.index'],
    ],
  ],

  'admin' => [
    'title' => 'system_and_settings',
    'children' => [
      ['title' => 'settings', 'route' => 'settings.index'],
      ['title' => 'logs_viewer', 'route' => 'audit-logs.index'],
      ['title' => 'reminder_logs', 'route' => 'organization.ReminderLogs'],
      ['title' => 'documentations', 'route' => 'documentation.index'],
    ],
  ],
];

$navigationIcons = [
  'dashboard' => 'smart-home',
  'programs'  => 'components',
  'people'    => 'users',
  'services'  => 'briefcase',
  'admin'     => 'settings',
];

// Get current route name
$currentRoute = request()->route() ? request()->route()->getName() : null;

// Determine active group and item
$activeGroup = null;
$activeItem = null;

if ($currentRoute) {
  foreach ($navigationItems as $groupKey => $group) {
    foreach ($group['children'] as $item) {
      // Skip setting mentorship active if URL contains '/mentorship/assign/'
      if ($item['route'] === 'mentorships.requests_view' && strpos(request()->getRequestUri(), '/mentorship/assign/') !== false) {
        continue;
      }

      // Check if current route starts with the base route (for sub-routes like create, edit, etc.)
      $baseRoute = explode('.', $item['route'])[0]; // Get the base part before the first dot
      $currentBaseRoute = explode('.', $currentRoute)[0];

      if ($currentBaseRoute === $baseRoute) {
        $activeGroup = $groupKey;
        $activeItem = $item['route'];
        break 2;
      }
    }
  }
}

// Special case: Make screening active if URL contains 'final' or 'screening'
if (strpos(request()->getRequestUri(), 'final') !== false || strpos(request()->getRequestUri(), 'screening') !== false) {
  $activeGroup = 'programs';
  $activeItem = 'screening.index';
}

// Special case: Make mentorship sessions active if URL contains 'mentorship/sessions'
if (strpos(request()->getRequestUri(), 'mentorship/sessions') !== false) {
  $activeGroup = 'programs';
  $activeItem = 'mentorships.sessions_list';
}

// Special case: Make startups active if URL contains '/companies'
if (strpos(request()->getRequestUri(), '/companies') !== false) {
  $activeGroup = 'people';
  $activeItem = 'users.view_companies';
}

// Special case: Make judges active if URL contains '/judger'
if (strpos(request()->getRequestUri(), '/judger') !== false) {
  $activeGroup = 'people';
  $activeItem = 'users.judger';
}

// Special case: Make organization_types active if URL contains '/organization-types'
if (strpos(request()->getRequestUri(), '/organization-types') !== false) {
  $activeGroup = 'people';
  $activeItem = 'organization-types.index';
}

// Special case: Make questions active if URL contains '/questions-bank'
if (strpos(request()->getRequestUri(), '/questions-bank') !== false) {
  $activeGroup = 'programs';
  $activeItem = 'question-categories.index';
}

// Special case: Make questions active if URL contains '/weights'
if (strpos(request()->getRequestUri(), '/weights') !== false) {
  $activeGroup = 'programs';
  $activeItem = 'question-categories.index';
}

// Special case: Make mentors active if URL contains '/invitations'
if (strpos(request()->getRequestUri(), '/invitations') !== false) {
  $activeGroup = 'people';
  $activeItem = 'mentors.list';
}

// Special case: Make programs active if URL contains 'programlist' or 'hackathon' or 'cycles' or '/mentorship/assign/'
if (strpos(request()->getRequestUri(), 'programlist') !== false || strpos(request()->getRequestUri(), 'hackathon') !== false || strpos(request()->getRequestUri(), 'cycles') !== false || strpos(request()->getRequestUri(), '/mentorship/assign/') !== false) {
  $activeGroup = 'programs';
  $activeItem = 'programlist.index';
}

// Special case: Make settings active if route matches specified patterns
if (Route::is(
  'settings.*',
  'roles.*',
  'importer.*',
  'currencies.*',
  'Tags.*',
  'Category.*',
  'subcategories.*',
  'stages.*',
  'Labels.*',
  'queue.*',
  'notification-templates.*',
  'notification-triggers.*',
  'license.*'
)) {
  $activeGroup = 'admin';
  $activeItem = 'settings.index';
}

// If no active item found, default to first group (dashboard) being open
if (!$activeGroup) {
  $activeGroup = 'dashboard';
}
$getSetting = App\Settings::find(1);
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo bg-light-subtle border-bottom" style="height: 70px;">
    <a href="/dashboard" class="app-brand-link">
      <img 
        @if (!is_null($_user->organization) && isset($_user->organization->imageUrl))
          src="{{ $_user->organization->imageUrl }}"
        @elseif (!empty($getSetting->Logo))
          src="/images/logo/{{ $getSetting->Logo }}"
        @else
          src="/images/background/logo.png" 
        @endif
        alt="avatar" 
        class="object-fit-cover me-2 rounded-circle border p-1 bg-card"
        style="width: 50px; height: 50px;"
      />
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
      <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
    </a>
  </div>

  <ul class="menu-inner py-1">
    <!-- Search Menu -->
    <li id="search-menu" class="menu-item px-3 mb-2 pb-4 pt-2 border-bottom">
      <div class="input-group input-group-merge">
        <div class="input-group-text">
          <i class="ti ti-search text-muted"></i>
        </div>
        <input 
          id="search-menu-input" 
          type="text" 
          class="form-control" 
          placeholder="{{ __('settings.navigation.search_menu') }}" 
        />
      </div>
    </li>
    
    <li id="search-menu-icon" class="menu-item" style="display: none;">
      <a href="javascript:void(0);" class="menu-link">
        <i class="menu-icon tf-icons ti ti-search"></i>
        <div></div>
      </a>
    </li>

    @foreach ($navigationItems as $group => $item)
      <li class="menu-item mb-1 {{ $group === $activeGroup ? 'sidebar-group-active open' : '' }} {{ $loop->last ? 'pb-8' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons ti ti-{{ $navigationIcons[$group] }}"></i>
          <div>{{ __('settings.navigation.' . $item['title']) }}</div>
        </a>
        <ul class="menu-sub">
          @foreach ($item['children'] as $child)
            @if ($_user->ableTo($child['route'], $permissions))
              <li 
                class="menu-item 
                {{ $_user->hasRole('Admin') && $child['title'] === 'reminder_logs' ? 'd-none' : '' }} 
                {{ in_array(env('APP_ENV'), ['production', 'beta']) && ($child['title'] === 'timesheets' || $child['title'] === 'projects') ? 'd-none' : '' }} 
                {{ $child['route'] === $activeItem ? 'active' : '' }}"
              >
                <a 
                  class="menu-link"
                  href="{{ route($child['route']) }}{{ $child['title'] == 'mentorship_sessions' ? '?show=all' : '' }}" 
                >
                  <div>{{ __('settings.navigation.' . $child['title']) }}</div>
                </a>
              </li>
            @endif
          @endforeach
        </ul>
      </li>
    @endforeach
  </ul>
</aside>
