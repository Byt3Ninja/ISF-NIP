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
  'tasks' => 'layout-kanban',
  'file_manager' => 'file',
  'documentation' => 'file-description',
];

  $setting = \App\Settings::where('id', 1)->first();
  if( $_user->HaveCompany && $_user->company && $_user->company->cycledata && $_user->company->cycledata->organization)
  {
    $_user->organization = $_user->company->cycledata->organization;
  }

  // Sidebar avatar
  $imageSrc =
    $_user->organization?->img_url
      ? $_user->organization->imageUrl
      : ($setting->Logo
        ? "/images/logo/$setting->Logo"
        : "/images/background/logo.png");
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

    <li class="w-100 px-3 mb-2 pt-2">
      <input 
        id="search-menu-input" 
        type="text" 
        class="form-control" 
        placeholder="{{ $l == 'en' ? 'Search in menu' : 'ابحث في القائمة' }}" 
      />
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
            {{ Request::route()->getName() == $child['route'] || Route::is($child['route']) ? 'active' : '' }}"
            {{ $item['title'] == 'startup_workspace' && (!isset($_user->company) || $_user->HaveCompany == 0) ? 'd-none' : '' }}
            {{ $child['title'] == 'analysis_and_assessment' && $setting->assessment != 1 ? 'd-none' : '' }}
            {{ $child['title'] == 'swot_analysis' && !in_array(env('APP_ENV','local'), ['local', 'dev']) ? 'd-none' : '' }}
          >
          {{-- @dd($_user->company) --}}
            <a 
              class="menu-link"
              href="{{ $child['route'] == 'company.profile_view' && $_user->company != null ? route('company.profile_view',  $_user->company->id ?? 0) : route($child['route']) }}" 
            >
              <i class="menu-icon tf-icons ti ti-{{ $navigationIcons[$child['title']] }}"></i>
              <div>{{ __('settings.navigation.' . $child['title']) }}</div>
            </a>
          
          </li>
        @endif
      @endforeach
    @endforeach
  </ul>
</aside>
