@php
$navigationItems = config('navigation')();

$navigationIcons = [
  'dashboard' => 'smart-home',
  'my_company' => 'building-skyscraper',
  'analysis_and_assessment' => 'chart-pie',
  'swot_analysis' => 'analyze',
  'services_directory' => 'search',
  'mentors' => 'users',
  'hackathons' => 'activity',
  'perks_and_discounts' => 'gift',
  'services_requests' => 'briefcase',
  'mentorship_requests' => 'user-check',
  'my_courses' => 'certificate-2',
  'quizzes' => 'help-octagon',
  'surveys' => 'device-imac-search',
  'tasks' => 'layout-kanban',
  'file_manager' => 'file',
  'documentation' => 'file-description',
];

  $setting = \App\Settings::where('id', 1)->first();
  // Display-only: keep this in a local, never on $_user. `users` has no
  // `organization` column, so assigning it marks the model dirty and makes
  // any later save() fail with "Unknown column 'organization'".
  $sidebarOrg = null;
  if( $_user->HaveCompany && $_user->company && $_user->company->cycledata && $_user->company->cycledata->organization)
  {
    $sidebarOrg = $_user->company->cycledata->organization;
  }

  // Sidebar avatar
  $imageSrc =
    $sidebarOrg?->img_url
      ? route('organizations.logo', $sidebarOrg?->img_url ?? '')
      : ($setting->Logo
        ? "/images/logo/$setting->Logo"
        : "/images/background/logo.png");
  
  $countSurvey = 0;
  $countSurvey = App\SurveyResponses::where('user_id', auth()->id())
    ->where('triggered_on', 'mentorship')
    ->whereHas('survey')
    ->where('user_id', $_user->id)
    ->when($_user->hasRole('Registered'), function ($query) use ($_user) {
						$query->where('cycle_id', $_user->company?->cycle);
					})
    ->where(function($q) {
        $q->whereNull('responses')
          ->orWhere('responses', '[]')
          ->orWhere('responses', '')
          ->orWhereRaw('JSON_LENGTH(responses) < (SELECT COUNT(*) FROM survey_questions WHERE survey_questions.survey_id = survey_responses.survey_id)');
    })
    ->count();

  $pendingServicesRequestsCount = 0;
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  {{-- Brand --}}
  <div class="app-brand demo bg-light-subtle border-bottom" style="height: 70px;">
    <a href="/dashboard" class="app-brand-link">
      <img src="{{ $imageSrc }}" class="object-fit-cover me-2 rounded-circle border p-1 bg-card"
           style="width: 50px; height: 50px;" alt="logo" />
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link ms-auto">
      <i class="ti menu-toggle-icon d-none d-xl-block"></i>
      <i class="ti ti-x d-xl-none ti-md"></i>
    </a>
  </div>

  <ul class="startup-menu-inner menu-inner pt-2 pb-8">

    @if ($_user->HaveCompany && $_user->company)
      @include('partials.program-switcher', ['company' => $_user->company])
    @endif

    <li class="w-100 px-3 mb-2 pt-2">
      {{-- Own form so Chrome's password manager doesn't pick this field as the "username" of a formless password field on the page --}}
      <form role="search" onsubmit="return false">
        <input
          id="search-menu-input"
          type="text"
          class="form-control"
          placeholder="{{ $l == 'en' ? 'Search in menu' : 'ابحث في القائمة' }}"
          autocomplete="off"
        />
      </form>
    </li>

    @foreach ($navigationItems as $group => $item)
      @if (isset($item['title']))
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">{{ __('settings.navigation.' . $item['title']) }}</span>
        </li>
      @endif

      @foreach ($item['children'] as $child)
        @if ($_user->ableTo($child['route'], $permissions) && isset($child['menuOnly']) && $child['menuOnly'] == true)
          <li 
            class="menu-item 
            {{ Request::is('dashboard') && $child['title'] == 'dashboard' ? 'active' : '' }}
            {{ Request::is('dashboard/myCompany/*') && $child['title'] == 'my_company' ? 'active' : '' }}
            {{ Request::is('dashboard/question-assessment') && $child['title'] == 'analysis_and_assessment' ? 'active' : '' }}
            {{ Request::is('dashboard/history-assessment') && $child['title'] == 'analysis_and_assessment' ? 'active' : '' }}
            {{ Request::route()->getName() == $child['route'] || Route::is($child['route']) ? 'active' : '' }}"
            {{ $item['title'] == 'startup_workspace' && (!isset($_user->company) || $_user->HaveCompany == 0) ? 'd-none' : '' }}
            {{ $child['title'] == 'analysis_and_assessment' && $setting->assessment != 1 ? 'd-none' : '' }}
            {{ $child['title'] == 'swot_analysis' && !in_array(env('APP_ENV','local'), ['local', 'dev']) ? 'd-none' : '' }}
          >
            <a 
              class="menu-link"
              href="{{ $child['route'] == 'company.profile_view' ? ($_user->company ? route('company.profile_view', $_user->company->id) : route('company.profile_view_mine')) : route($child['route']) }}" 
            >
              <i class="menu-icon tf-icons ti ti-{{ $navigationIcons[$child['title']] }}"></i>
              <div class="d-flex align-items-center justify-content-between w-100">
                <div>{{ __('settings.navigation.' . $child['title']) }}</div>
                @if ($countSurvey > 0 && $child['title'] == 'surveys')
                  <span class="badge badge-center rounded-pill bg-danger ms-2">{{ $countSurvey }}</span>
                @elseif ($pendingServicesRequestsCount == 0 && $child['title'] == 'services_requests')
                  {{-- <span class="badge badge-center rounded-pill bg-danger ms-2">{{ $pendingServicesRequestsCount }}</span> --}}
                @endif
              </div>
            </a>
          </li>
        @endif
      @endforeach
    @endforeach
  </ul>
</aside>
