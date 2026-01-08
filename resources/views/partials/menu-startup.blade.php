@php
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
  <div class="app-brand demo bg-light" style="height: 70px;">
    <a href="/dashboard" class="app-brand-link">
      <img src="{{ $imageSrc }}" class="object-fit-contain me-2 rounded-circle border p-1 bg-white"
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

    {{-- Dashboard --}}
    <li class="{{ menuItem('dashboard') }} menu-item">
      <a href="/dashboard" class="menu-link">
        <i class="menu-icon tf-icons ti ti-smart-home"></i>
        <div>{{ __('admin.Dashboard') }}</div>
      </a>
    </li>

    {{-- Profile --}}
    {{-- <li class="{{ active('*myProfile') }} menu-item">
      <a href="{{ route('user.profile_view') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-user"></i>
        <div>{{ __('admin.Profile') }}</div>
      </a>
    </li> --}}


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
        <li class="{{ active('dashboard/companyProfile*', 'dashboard/myCompany/*') }} menu-item">
          <a href="{{ $isMineOnly ? route('company.profile_view_mine') : route('company.profile_view', $companyId) }}"
            class="menu-link">
            <i class="menu-icon tf-icons ti ti-building-skyscraper"></i>
            <div>{{ __('admin.My company') }}</div>
          </a>
        </li>
      @endif

      {{-- Analysis & Assessment --}}
      @if($setting->assessment == 1 && $_user->ableTo('assessments.results_analysis_view', $permissions))
        <li class="{{ active('dashboard/analysis-assessment', 'dashboard/question-assessment*', 'dashboard/history-assessment*') }} menu-item">
          <a href="/dashboard/analysis-assessment" class="menu-link">
            <i class="menu-icon tf-icons ti ti-chart-pie"></i>
            <div>{{ $l == 'en' ? 'Analysis & Assessment' : 'تحليل وتقييم' }}</div>
          </a>
        </li>
      @endif

      {{-- SWOT --}}
      @if(in_array(env('APP_ENV','local'), ['local', 'dev']) && $_user->ableTo('swot.view',$permissions))
        <li class="{{ active('dashboard/swot-analysis*') }} menu-item">
          <a href="{{ route('swot.view') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-analyze"></i>
            <div>{{ __('admin.swot_analysis') }}</div>
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
          <div>{{ __('admin.Services') }}</div>
        </a>
      </li>
    @endif

    {{-- Mentors --}}
    @if($_user->ableTo('mentors.list', $permissions))
      <li class="{{ active('dashboard/mentors') }} menu-item">
        <a href="/dashboard/mentors" class="menu-link">
          <i class="menu-icon tf-icons ti ti-users"></i>
          <div>{{ __('admin.Mentors') }}</div>
        </a>
      </li>
    @endif

    {{-- Hackathon --}}
    <li class="{{ active('dashboard/hackathon') }} menu-item">
      <a href="/dashboard/hackathon" class="menu-link">
        <i class="menu-icon tf-icons ti ti-activity"></i>
        <div>{{ __('admin.Hackathon') }}</div>
      </a>
    </li>
    
    <li class="{{ active('dashboard/perks') }} menu-item">
      <a href="/dashboard/perks" class="menu-link">
        <i class="menu-icon tf-icons ti ti-gift"></i>
        <div>{{ $l == 'en' ? 'Perks & Discounts' : 'المزايا والخصومات' }}</div>
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
          <div>{{ $l == 'en' ? 'Services Requests' : 'طلبات الخدمات' }}</div>
        </a>
      </li>
    @endif

    {{-- Mentorship Requests --}}
    @if($_user->ableTo('mentorships.list_mine', $permissions))
      <li class="{{ active('dashboard/mentorship/mine') }} menu-item">
        <a href="/dashboard/mentorship/mine" class="menu-link">
          <i class="menu-icon tf-icons ti ti-user-check"></i>
          <div>{{ $l == 'en' ? 'Mentorship Requests' : 'طلبات الإرشاد' }}</div>
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
          <div>{{ __('admin.My courses') }}</div>
        </a>
      </li>
    @endif

    {{-- Quizzes --}}
    @if($_user->ableTo('quizzes.dashboard_view', $permissions))
      <li class="{{ active('dashboard/quizzes/my*') }} menu-item">
        <a href="/dashboard/quizzes/my" class="menu-link">
          <i class="menu-icon tf-icons ti ti-help-octagon"></i>
          <div>{{ __('admin.My Quizzes') }}</div>
        </a>
      </li>
    @endif

    {{-- Mentorship Tasks --}}
    @if($_user->HaveCompany && $_user->company && $_user->ableTo('mentorships.sessions_list_detailed', $permissions))
      <li class="{{ active('dashboard/mentor/mentorshipSessions*') }} menu-item">
        <a href="{{ route('mentorships.sessions_list_detailed') }}" class="menu-link">
          <i class="menu-icon tf-icons ti ti-layout-kanban"></i>
          <div>{{ __('admin.tasks') }}</div>
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
          <div>{{ __('admin.filemanager') }}</div>
        </a>
      </li>
    @endif

    {{-- Documentation --}}
    @if($_user->ableTo('documentation.index', $permissions))
      <li class="{{ active('dashboard/documentation*') }} menu-item">
        <a href="/dashboard/documentation" class="menu-link">
          <i class="menu-icon tf-icons ti ti-file-description"></i>
          <div>{{ __('admin.Documentations') }}</div>
        </a>
      </li>
    @endif

  </ul>

</aside>
