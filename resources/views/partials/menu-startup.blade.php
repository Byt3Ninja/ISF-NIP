@php
  $setting = \App\Settings::where('id', 1)->first();
  if( $_user->HaveCompany && $_user->company && $_user->company->cycledata && $_user->company->cycledata->organization)
  {
    $_user->organization = $_user->company->cycledata->organization;
  }

  // Sidebar avatar
  $imageSrc =
    $_user->organization?->img_url
      ? asset($_user->organization->img_url)
      : ($setting->Logo
        ? "/images/logo/$setting->Logo"
        : "/images/background/logo.png");

  if (!function_exists('active')) {
    function active($pattern) {
      return Request::is($pattern) ? 'active' : '';
    }
  }

  if (!function_exists('menuItem')) {
    function menuItem($pattern) {
      return Request::is($pattern) ? 'active menu-item' : 'menu-item';
    }
  }
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  {{-- Brand --}}
  <div class="app-brand demo bg-light" style="height: 70px;">
    <a href="/dashboard" class="app-brand-link">
      <img src="{{ $imageSrc }}" class="object-fit-contain me-2 rounded-circle border p-1 bg-white"
           style="width: 50px; height: 50px;" alt="avatar" />
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

    {{-- Dashboard --}}
    <li class="{{ menuItem('dashboard') }} menu-item">
      <a href="/dashboard" class="menu-link">
        <i class="menu-icon tf-icons ti ti-smart-home"></i>
        <span>{{ __('admin.Dashboard') }}</span>
      </a>
    </li>

    {{-- Profile --}}
    <li class="{{ active('*myProfile') }} menu-item">
      <a href="{{ route('user.profile_view') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-user"></i>
        <span>{{ __('admin.Profile') }}</span>
      </a>
    </li>


    {{-- ================================
          STARTUP WORKSPACE (LABEL)
        ================================ --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">{{ $l == 'en' ? 'Startup workspace' : 'مجال الشركة الناشئة' }}</span>
    </li>

    @php
      $canCompany = $_user->ableToAny(
          ['company.profile_view', 'assessments.results_analysis_view', 'swot.view'],
          $permissions
      );

      $companyId = ($_user->HaveCompany == 0 || !$_user->company)
          ? null
          : $_user->company->id;

      $showCompanyMenu =
          $canCompany &&
          (
              ($_user->HaveCompany == 1 && !$companyId) ||
              ($_user->HaveCompany != 0 && $companyId)
          );

      $isMineOnly = ($_user->HaveCompany == 1 && !$companyId);
    @endphp

    @if($showCompanyMenu)

      {{-- Startup Profile --}}
      @if($_user->ableTo('company.profile_view', $permissions))
        <li class="{{ active('dashboard/companyProfile*') }} menu-item">
          <a href="{{ $isMineOnly ? route('company.profile_view_mine') : route('company.profile_view', $companyId) }}"
            class="menu-link">
            <i class="menu-icon tf-icons ti ti-building-skyscraper"></i>
            <span>{{ __('admin.My company') }}</span>
          </a>
        </li>
      @endif

      {{-- Analysis & Assessment --}}
      @if($setting->assessment == 1 && $_user->ableTo('assessments.results_analysis_view', $permissions))
        <li class="{{ active('dashboard/analysis-assessment') }} menu-item">
          <a href="/dashboard/analysis-assessment" class="menu-link">
            <i class="menu-icon tf-icons ti ti-chart-pie"></i>
            <span>{{ $l == 'en' ? 'Analysis & Assessment' : 'تحليل وتقييم' }}</span>
          </a>
        </li>
      @endif

      {{-- SWOT --}}
      @if(in_array(env('APP_ENV','local'), ['local', 'dev']) && $_user->ableTo('swot.view',$permissions))
        <li class="{{ active('dashboard/swot-analysis*') }} menu-item">
          <a href="{{ route('swot.view') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-analyze"></i>
            <span>{{ __('admin.swot_analysis') }}</span>
          </a>
        </li>
      @endif

    @endif



    {{-- ====================
          ENGAGEMENT
        ==================== --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">{{ $l == 'en' ? 'Engagement' : 'تفاعل' }}</span>
    </li>

    {{-- Services Directory --}}
    @if($_user->ableTo('users.directory_view', $permissions))
      <li class="{{ active('dashboard/directory') }} menu-item">
        <a href="/dashboard/directory" class="menu-link">
          <i class="menu-icon tf-icons ti ti-search"></i>
          <span>{{ __('admin.Services') }}</span>
        </a>
      </li>
    @endif

    {{-- Mentors --}}
    @if($_user->ableTo('mentors.list', $permissions))
      <li class="{{ active('dashboard/show/Mentors') }} menu-item">
        <a href="/dashboard/show/Mentors" class="menu-link">
          <i class="menu-icon tf-icons ti ti-users"></i>
          <span>{{ __('admin.Mentors') }}</span>
        </a>
      </li>
    @endif

    {{-- Hackathon --}}
    <li class="{{ active('dashboard/hackathon') }} menu-item">
      <a href="/dashboard/hackathon" class="menu-link">
        <i class="menu-icon tf-icons ti ti-activity"></i>
        <span>{{ __('admin.Hackathon') }}</span>
      </a>
    </li>



    {{-- ====================
          MY REQUESTS
        ==================== --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">{{ __('admin.My Requests') }}</span>
    </li>

    {{-- Service Requests --}}
    @if($_user->ableTo('service_requests.list_mine', $permissions))
      <li class="{{ active('dashboard/requests/mine') }} menu-item">
        <a href="/dashboard/requests/mine" class="menu-link">
          <i class="menu-icon tf-icons ti ti-briefcase"></i>
          <span>{{ $l == 'en' ? 'Services Requests' : 'طلبات الخدمات' }}</span>
        </a>
      </li>
    @endif

    {{-- Mentorship Requests --}}
    @if($_user->ableTo('mentorships.list_mine', $permissions))
      <li class="{{ active('dashboard/mentorship/mine') }} menu-item">
        <a href="/dashboard/mentorship/mine" class="menu-link">
          <i class="menu-icon tf-icons ti ti-user-check"></i>
          <span>{{ $l == 'en' ? 'Mentorship Requests' : 'طلبات الإرشاد' }}</span>
        </a>
      </li>
    @endif

    {{-- ====================
          LEARNING
        ==================== --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">{{ $l == 'en' ? 'Learning' : 'تعلم' }}</span>
    </li>


    {{-- Courses --}}
    @if($_user->ableTo('trainings.list_mine', $permissions))
      <li class="{{ active('dashboard/training/my*') }} menu-item">
        <a href="/dashboard/training/my" class="menu-link">
          <i class="menu-icon tf-icons ti ti-certificate-2"></i>
          <span>{{ __('admin.My courses') }}</span>
        </a>
      </li>
    @endif

    {{-- Quizzes --}}
    @if($_user->ableTo('quizzes.dashboard_view', $permissions))
      <li class="{{ active('dashboard/quizzes/my*') }} menu-item">
        <a href="/dashboard/quizzes/my" class="menu-link">
          <i class="menu-icon tf-icons ti ti-help-octagon"></i>
          <span>{{ __('admin.My Quizzes') }}</span>
        </a>
      </li>
    @endif

    {{-- Mentorship Tasks --}}
    @if($_user->HaveCompany && $_user->company && $_user->ableTo('mentorships.sessions_list_detailed', $permissions))
      <li class="{{ active('dashboard/mentor/mentorshipSessions*') }} menu-item">
        <a href="{{ route('mentorships.sessions_list_detailed') }}" class="menu-link">
          <i class="menu-icon tf-icons ti ti-layout-kanban"></i>
          <span>{{ $l == 'en' ? 'Mentorship Tasks' : 'مهام الإرشاد' }}</span>
        </a>
      </li>
    @endif



    {{-- ====================
            RESOURCES
        ==================== --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">{{ $l == 'en' ? 'Resources' : 'مصادر' }}</span>
    </li>

    {{-- File Manager --}}
    @if($_user->ableTo('my_files.list', $permissions))
      <li class="{{ active('dashboard/myFiles') }} menu-item">
        <a href="/dashboard/myFiles" class="menu-link">
          <i class="menu-icon tf-icons ti ti-file"></i>
          <span>{{ __('admin.filemanager') }}</span>
        </a>
      </li>
    @endif

    {{-- Documentation --}}
    @if($_user->ableTo('documentation.index', $permissions))
      <li class="{{ active('dashboard/documentation*') }} menu-item">
        <a href="/dashboard/documentation" class="menu-link">
          <i class="menu-icon tf-icons ti ti-file-description"></i>
          <span>{{ __('admin.Documentations') }}</span>
        </a>
      </li>
    @endif

  </ul>

</aside>
