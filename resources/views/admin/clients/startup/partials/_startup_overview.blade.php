@php
  /** 1. DATA PREPARATION & LOGIC **/
  $co = $company;

  // --- Identity & Category Logic ---
  $selectedCategories = is_string($co->business_categories ?? null)
    ? (json_decode($co->business_categories, true) ?? [])
    : (is_array($co->business_categories ?? null) ? $co->business_categories : []);

  $otherValue = '';
  $otherIndexEn = array_search('Other', $selectedCategories);
  $otherIndexAr = array_search('أخرى', $selectedCategories);
  if ($otherIndexEn !== false && isset($selectedCategories[$otherIndexEn + 1])) {
    $otherValue = $selectedCategories[$otherIndexEn + 1];
  } elseif ($otherIndexAr !== false && isset($selectedCategories[$otherIndexAr + 1])) {
    $otherValue = $selectedCategories[$otherIndexAr + 1];
  }

  $displayCategories = collect($selectedCategories)
    ->filter(fn($v) => filled($v))
    ->reject(fn($v) => in_array($v, ['Other', 'أخرى', $otherValue], true))
    ->values();

  $visibleCategories        = $displayCategories->take(5);
  $remainingCategoriesCount = $displayCategories->count() - $visibleCategories->count();
  $displayCat               = $displayCategories->first() ?? ($co->business_type ?? '');

  // --- Financial & Funding Logic ---
  $currenciesCollection = collect($Currencies ?? []);
  $defaultCurrencyCode  = $Setting->Currency ?? 'USD';
  $selectedCurrency     = $currenciesCollection->firstWhere('currency_code', $co->Currency ?? $defaultCurrencyCode);
  $currencyCode         = $selectedCurrency->currency_code ?? 'USD';

  $target          = (float) ($co->Funding_Target ?? 0);
  $raised          = (float) ($co->raised_fund ?? 0);
  $fundingProgress = $target > 0 ? min(100, (int) round(($raised / $target) * 100)) : 0;
  $valuation       = (float) ($co->Pre_money_Valuation ?? 0);

  // Render a money value with thousands separator + currency suffix, or "N/A"
  // when it is null / empty / non-numeric (avoids showing a bare "0 EGP").
  $fmtMoney = function ($v) use ($currencyCode) {
    return $v !== null && $v !== '' && is_numeric($v)
      ? number_format((float) $v) . ' ' . $currencyCode
      : __('admin.N/A');
  };

  // --- Development Stage Mapping ---
  $stageModel  = is_numeric($co->stage ?? null) ? App\Stage::find((int) $co->stage) : null;
  $devStageName = $stageModel
    ? ($stageModel->{'name_' . $l} ?? '')
    : ($co->stage ? __('admin.stage_' . $co->stage) : __('admin.N/A'));

  // --- Badges & Achievement Configuration ---
  $achievemenLabels = [
    'team_builder'    => __('startup.badges.added_members_count'),
    'growth_achiever' => __('startup.badges.completed_milestones_count'),
    'mentor_connect'  => __('startup.badges.make_mentorships_count'),
  ];
  $unlockHints = [
    __('startup.badges.not_graduated_yet'),
    __('startup.badges.participate_in_hackathon_to_unlock'),
    __('startup.badges.complete_profile_to_unlock'),
    __('startup.badges.create_join_team'),
    __('startup.badges.reach_growth_milestones'),
    __('startup.badges.no_mentorhip_sessions'),
  ];
  $badgeLabels = ['alumni', 'winner', 'completed', 'active', 'achiever', 'connected'];
  $badgeIcons  = ['fa-medal', 'fa-trophy', 'fa-user-check', 'fa-users', 'fa-arrow-up-right-dots', 'fa-handshake'];
  $badgeColors = ['primary', 'secondary', 'info', 'warning', 'danger', 'success'];

  // --- Sustainability & Market Logic ---
  $sustainable_array = json_decode($co->Sustainable_development ?? '[]') ?? [];

  $countryIds = collect(
      is_string($co->targeted_market ?? null)
        ? (json_decode($co->targeted_market, true) ?? [])
        : (is_array($co->targeted_market ?? null) ? $co->targeted_market : [])
    )
    ->filter(fn($id) => is_numeric($id))
    ->map(fn($id) => (int) $id)
    ->values()
    ->all();
  $marketCount = count($countryIds);

  $targetedMarketNames = $marketCount > 0
    ? App\Country::whereIn('id', $countryIds)
        ->get()
        ->map(fn($country) => $country->{'name_' . $l} ?? $country->name_en ?? $country->name_ar ?? null)
        ->filter()
        ->values()
    : collect();

  $visibleTargetMarkets        = $targetedMarketNames->take(3);
  $remainingTargetMarketsCount = $targetedMarketNames->count() - $visibleTargetMarkets->count();
  $targetedMarketLabel         = $targetedMarketNames->isNotEmpty()
    ? $visibleTargetMarkets->implode(', ')
    : __('admin.N/A');

  $uniqueTechnologies = !empty($technologies) ? $technologies->unique('name')->values() : collect();

  $technologiesUsed = collect(
    array_filter(array_map('trim', explode(',', (string) ($company->technologies_used ?? ''))))
  );

  $industryFocus = !empty($industry)
    ? ($industry->{'name_' . $l} ?? $industry->name_en ?? $industry->name ?? __('admin.N/A'))
    : __('admin.N/A');

  $whyThisStartup = $co->why_this_startup ?? $co->why_startup ?? null;

  $totalEmployees  = $co->total_employees !== null ? $co->total_employees : __('admin.N/A');
  $maleEmployees   = $co->male_employees !== null ? $co->male_employees : __('admin.N/A');
  $femaleEmployees = $co->female_employees !== null ? $co->female_employees : __('admin.N/A');

  $businessModelBadge = $co->business_model
    ? (__('admin.' . $co->business_model) ?: ($co->other_business_model ?? ''))
    : ($co->other_business_model ?? '');

  // Single source of truth for all strategy tabs (nav + panes + offcanvas).
  // Optional keys: 'badge' (inline label), 'link' (CTA button).
  $strategyTabs = [
     [
      'id'      => 'tab-why-startup',
      'title'   => __('startup.why_this_startup'),
      'heading' => __('startup.what_makes_us_different'),
      'value'   => $whyThisStartup,
      'icon'    => 'ti-sparkles',
      'color'   => 'success',
    ],
    [
      'id'      => 'tab-model',
      'title'   => __('startup.business_model'),
      'heading' => __('startup.business_model'),
      'value'   => $co->business_model_description ?: __('admin.N/A'),
      'icon'    => 'ti-layout-grid',
      'color'   => 'info',
      'badge'   => $businessModelBadge,
      'link'    => $co->mvp_prototype_link ?? null,
      'always'  => true,
    ],
    [
      'id'      => 'tab-challenges',
      'title'   => __('startup.problem'),
      'heading' => __('startup.about_problem'),
      'value'   => $co->about_problem ?: __('admin.N/A'),
      'icon'    => 'ti-alert-triangle',
      'color'   => 'danger',
      'always'  => true,
    ],
    [
      'id'      => 'tab-market-opportunity',
      'title'   => __('admin.Market'),
      'heading' => __('startup.about_market_opportunity'),
      'value'   => $co->about_market_opportunity ?: __('admin.N/A'),
      'icon'    => 'ti-chart-arcs',
      'color'   => 'primary',
      'always'  => true,
    ],
    [
      'id'      => 'tab-competitors',
      'title'   => __('startup.competitors'),
      'heading' => __('admin.about_competitors'),
      'value'   => $co->about_competitors ?: __('admin.N/A'),
      'icon'    => 'ti-sword',
      'color'   => 'warning',
      'always'  => true,
    ],
    [
      'id'      => 'tab-monetization',
      'title'   => __('startup.monetization'),
      'heading' => __('startup.about_monetization'),
      'value'   => $co->about_monetize ?: __('admin.N/A'),
      'icon'    => 'ti-coins',
      'color'   => 'success',
      'always'  => true,
    ],
    [
      'id'      => 'tab-mission',
      'title'   => __('startup.mission'),
      'heading' => __('startup.our_mission'),
      'value'   => $co->mission,
      'icon'    => 'ti-target',
      'color'   => 'primary',
    ],
    [
      'id'      => 'tab-achievements',
      'title'   => __('startup.achievements'),
      'heading' => __('startup.badges.achievements_and_progress'),
      'value'   => $co->achievements,
      'icon'    => 'ti-award',
      'color'   => 'warning',
    ],
    [
      'id'      => 'tab-startup-expertise',
      'title'   => __('startup.startup_expertise'),
      'heading' => __('startup.areas_of_expertise'),
      'value'   => $co->startup_expertise,
      'icon'    => 'ti-bulb',
      'color'   => 'info',
    ],
  ];

  // GV-2052: Hide description tabs (why_this_startup, mission, achievements,
  // startup_expertise) when their underlying column is empty / whitespace.
  // Legacy tabs are marked 'always' => true and remain visible with N/A fallback.
  $strategyTabs = array_values(array_filter($strategyTabs, function ($tab) {
    if (!empty($tab['always'])) {
      return true;
    }
    return isset($tab['value']) && trim((string) $tab['value']) !== '';
  }));

  // --- Registration & Offering Logic (GV-2063) ---
  // Display-ready array is computed in ClientCompanyController::show().
  $regType        = $registrationOfferingDetails['registration_type'] ?? null;
  $offeringType   = $registrationOfferingDetails['offering_type'] ?? null;
  $productsDesc   = $registrationOfferingDetails['products_desc'] ?? null;
  $hasRegOffering = (bool) ($registrationOfferingDetails['has_any'] ?? false);

  $generatesRevenue = ($co->generate_revenue ?? null) === 'yes';
  $revenueAmount    = is_numeric($co->average_annual_revenues ?? null) ? (float) $co->average_annual_revenues : 0;

  // --- Locale-aware helpers ---
  $isEn          = $l === 'en';
  $countryName   = optional($co->country)->{'name_' . $l} ?? '';
  $teamSizeLabel = $co->team_num ? __('startup.startup_size_' . $co->team_num) : 'N/A';
  $estDate       = $co->est_date ?: 'N/A';
  $yearsOps      = $co->years_of_operations ?? __('admin.N/A');
  $devStageLabel = $co->development_stage
    ? (__('admin.' . $co->development_stage) ?: 'N/A')
    : 'N/A';
  $companyName   = $co->name_en ?: ($co->name_ar ?? '');
@endphp

<div class="row g-6">

  {{-- 1. HERO SUMMARY WIDGET --}}
  <div class="col-lg-8">
    <div class="card h-100 overflow-hidden border-0 bg-primary bg-gradient shadow-none">
      <div class="card-body d-flex flex-column h-100 p-5">
        <div class="row align-items-center">
          <div class="col-md-8 text-white">
            <div class="mb-2 d-flex align-items-center gap-3">
              <h4 class="mb-0 text-white">{{ $companyName }}</h4>
              @if (($co->CreatedTax ?? null) == '1')
                <span class="badge border border-white border-opacity-25 bg-card bg-opacity-20 text-primary">
                  <i class="ti ti-shield-check ti-xs me-1"></i>
                  {{ __('startup.registered') }}
                </span>
              @endif
            </div>

            <p class="mb-6 fs-6">
              {{ implode(' • ', array_filter([$displayCat, $countryName])) }}
            </p>

            @if ($displayCategories->isNotEmpty())
              <div class="mb-6 d-flex flex-wrap gap-2">
                @foreach ($visibleCategories as $category)
                  <span class="badge border border-white border-opacity-25 bg-card bg-opacity-20 text-primary">
                    {{ $category }}
                  </span>
                @endforeach
                @if ($remainingCategoriesCount > 0)
                  <button
                    type="button"
                    class="badge border border-white border-opacity-25 bg-card bg-opacity-20 text-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#categoriesModal"
                  >+{{ $remainingCategoriesCount }}</button>
                @endif
              </div>
            @endif

            <div class="row g-4 mt-auto">
              <div class="col-sm-6">
                <div class="mb-4 d-flex align-items-center">
                  <div class="badge me-3 rounded bg-card bg-opacity-20 p-2">
                    <i class="ti ti-users ti-md text-primary"></i>
                  </div>
                  <div>
                    <small class="d-block opacity-75">{{ __('startup.startup_size') }}</small>
                    <h6 class="mb-0 text-white">{{ $teamSizeLabel }}</h6>
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  <div class="badge me-3 rounded bg-card bg-opacity-20 p-2">
                    <i class="ti ti-rocket ti-md text-primary"></i>
                  </div>
                  <div>
                    <small class="d-block opacity-75">{{ __('admin.Dev_Stage') }}</small>
                    <h6 class="mb-0 text-white">{{ $devStageName }}</h6>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="mb-4 d-flex align-items-center">
                  <div class="badge me-3 rounded bg-card bg-opacity-20 p-2">
                    <i class="ti ti-building-community ti-md text-primary"></i>
                  </div>
                  <div>
                    <small class="d-block opacity-75">{{ $isEn ? 'Industry Focus' : 'تركيز الصناعة' }}</small>
                    <h6 class="mb-0 text-truncate text-white">{{ $industryFocus }}</h6>
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  <div class="badge me-3 rounded bg-card bg-opacity-20 p-2">
                    <i class="ti ti-calendar ti-md text-primary"></i>
                  </div>
                  <div>
                    <small class="d-block opacity-75">{{ __('admin.est_date') }}</small>
                    <h6 class="mb-0 text-white">{{ $estDate }}</h6>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4 position-relative d-none d-md-block text-center">
            <img
              src="/vuexy/assets/img/illustrations/rocket.png"
              height="180"
              class="card-website-analytics-img"
              alt="rocket"
            />
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- 2. FUNDING PROGRESS WIDGET --}}
  <div class="col-lg-4 col-md-6">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-header d-flex align-items-center justify-content-between border-bottom py-4">
        <h5 class="card-title mb-0">{{ __('admin.Funding Overview') }}</h5>
        <div class="d-flex align-items-center gap-2">
          <span class="badge rounded-pill bg-label-success">{{ $fundingProgress }}%</span>
          @if ($_user->ableTo('company.profile_view_mine', $permissions) && isset($company->id))
            <button 
              type="button" 
              class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
              onclick="localStorage.setItem('activeCompanyTab', 4);window.location.href = '/dashboard/myCompany/{{ $company->id }}'"
            >
              <i class="ti ti-edit ti-md"></i>
            </button>
          @endif
        </div>
      </div>
      <div class="card-body pt-3">
        <div class="mb-4 d-flex align-items-center">
          <div class="badge me-3 rounded bg-label-success p-2">
            <i class="ti ti-currency-dollar ti-lg"></i>
          </div>
          <div>
            <h5 class="mb-0 fw-bold">{{ $fmtMoney($co->raised_fund) }}</h5>
            <small class="text-muted mb-0">{{ __('admin.raised_fund_egp') }}</small>
          </div>
        </div>
        <div class="progress mb-4" style="height: 8px">
          <div
            class="progress-bar bg-success"
            role="progressbar"
            style="width: {{ $fundingProgress }}%"
            aria-valuenow="{{ $fundingProgress }}"
            aria-valuemin="0"
            aria-valuemax="100"
          ></div>
        </div>
        <ul class="mb-0 list-unstyled">
          <li class="mb-3 d-flex justify-content-between">
            <span class="small text-muted">{{ __('admin.Funding_Target') }}</span>
            <span class="small fw-bold">{{ $fmtMoney($co->Funding_Target) }}</span>
          </li>
          <li class="mb-3 d-flex justify-content-between">
            <span class="small text-muted">{{ __('admin.Funding_Stage') }}</span>
            <span class="small fw-bold text-end">{{ $devStageLabel }}</span>
          </li>
          <li class="mb-3 d-flex justify-content-between">
            <span class="small text-muted">{{ __('admin.Pre_money_Valuation') }}</span>
            <span class="small fw-bold">{{ $fmtMoney($co->Pre_money_Valuation) }}</span>
          </li>
          <li class="d-flex justify-content-between">
            <span class="small text-muted">{{ __('admin.average_annual_revenues') }}</span>
            <span class="small fw-bold">{{ $fmtMoney($co->average_annual_revenues) }}</span>
          </li>
        </ul>
      </div>
    </div>
  </div>

  {{-- 3. STRATEGIC CONTENT TABS --}}
  <div class="col-xl-8 col-12">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-6">
        <h5 class="card-title mb-0">{{ __('startup.about_challenges') }}</h5>
        @if ($_user->hasAnyRole('registered'))
          <div class="d-flex align-items-center gap-2">
            @if (($_user->ableTo('company.profile_view_mine', $permissions) ?? false) && !empty($company->id))
              <button
                class="btn btn-sm btn-outline-primary"
                onclick="localStorage.setItem('activeCompanyTab', 3); window.location.href = '/dashboard/myCompany/{{ $company->id }}'"
              >
                <i class="ti ti-pencil me-1"></i>
                {{ __('admin.Edit') }}
              </button>
            @endif
            <button
              class="btn btn-sm btn-primary"
              type="button"
              data-bs-toggle="offcanvas"
              data-bs-target="#offcanvasRight"
              aria-controls="offcanvasRight"
            >
              <i class="ti ti-eye me-1"></i>
              {{ __('admin.View') }}
            </button>
          </div>
        @endif
      </div>
      <div class="card-body">
        <ul class="nav widget-nav-tabs d-flex flex-wrap gap-2 border-0 pb-4 nav-tabs" role="tablist">
          @foreach ($strategyTabs as $tab)
            <li class="nav-item">
              <a href="javascript:void(0);"
                class="nav-link {{ $loop->first ? 'active' : '' }} d-flex flex-column align-items-center border"
                data-bs-toggle="tab" data-bs-target="#{{ $tab['id'] }}">
                <div class="badge rounded bg-label-{{ $tab['color'] }} p-2">
                  <i class="ti {{ $tab['icon'] }} ti-md"></i>
                </div>

                <h6 class="mt-2 mb-0 small text-center">
                  {{ $tab['title'] }}
                </h6>
              </a>
            </li>
          @endforeach
        </ul>
      
        <div class="tab-content mt-4 border-0 p-0">
          @foreach ($strategyTabs as $tab)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $tab['id'] }}">
              @if (!empty($tab['badge']))
                <div class="mb-3 d-flex align-items-center">
                  <span class="badge bg-label-{{ $tab['color'] }} px-3 py-2">
                    {{ $tab['badge'] }}
                  </span>
                </div>
              @endif

              <h6 class="fw-bold mb-0">{{ $tab['heading'] }}</h6>

              <p class="text-break {{ empty($tab['link']) ? 'mb-0' : '' }}"
                style="white-space: pre-line; word-break: break-word;">
                {{ $tab['value'] }}
              </p>

              @if (!empty($tab['link']))
                <a href="{{ $tab['link'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                  <i class="ti ti-link me-1"></i>
                  {{ __('startup.mvp_prototype_link') }}
                </a>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- 4. KEY METRICS SIDEBAR --}}
  <div class="col-xl-4">
    <div class="card h-100 mb-6 border-0 shadow-sm">
      <div class="card-header border-bottom">
        <h5 class="mb-0">{{ __('admin.Key Metrics') }}</h5>
      </div>
      <div class="card-body pt-4">
        <ul class="mb-0 list-unstyled">
          <li class="mb-4 d-flex align-items-center">
            <div class="badge me-3 rounded bg-label-warning p-2">
              <i class="ti ti-world ti-md"></i>
            </div>
            <div class="d-flex w-100 flex-wrap justify-content-between">
              <h6 class="mb-0 small">{{ __('admin.Targeted_Market') }}</h6>
              <div class="d-flex align-items-center gap-2 text-end">
                <span class="small fw-bold">{{ $targetedMarketLabel }}</span>
                @if ($remainingTargetMarketsCount > 0)
                  <button
                    type="button"
                    class="btn btn-sm btn-text-secondary p-0 small fw-bold"
                    data-bs-toggle="modal"
                    data-bs-target="#targetMarketsModal"
                  >+{{ $remainingTargetMarketsCount }}</button>
                @endif
              </div>
            </div>
          </li>
          <li class="mb-4 d-flex align-items-center">
            <div class="badge me-3 rounded bg-label-danger p-2">
              <i class="ti ti-briefcase ti-md"></i>
            </div>
            <div class="d-flex w-100 flex-wrap justify-content-between">
              <h6 class="mb-0 small">{{ __('admin.Business Type') }}</h6>
              <span class="small fw-bold">{{ $co->business_type ?? __('admin.N/A') }}</span>
            </div>
          </li>
          <li class="d-flex align-items-center">
            <div class="badge me-3 rounded bg-label-secondary p-2">
              <i class="ti ti-history ti-md"></i>
            </div>
            <div class="d-flex w-100 flex-wrap justify-content-between">
              <h6 class="mb-0 small">{{ __('admin.Years of Operations') }}</h6>
              <span class="small fw-bold">{{ $yearsOps }}</span>
            </div>
          </li>
          <li class="mt-4 d-flex align-items-center">
            <div class="badge me-3 rounded bg-label-success p-2">
              <i class="ti ti-users-group ti-md"></i>
            </div>
            <div class="d-flex w-100 flex-wrap justify-content-between">
              <h6 class="mb-0 small">{{ __('startup.total_employees') }}</h6>
              <span class="small fw-bold">{{ $totalEmployees }}</span>
            </div>
          </li>
          <li class="mt-4 d-flex align-items-center">
            <div class="badge me-3 rounded bg-label-primary p-2">
              <i class="ti ti-gender-male ti-md"></i>
            </div>
            <div class="d-flex w-100 flex-wrap justify-content-between">
              <h6 class="mb-0 small">{{ __('startup.male_employees') }}</h6>
              <span class="small fw-bold">{{ $maleEmployees }}</span>
            </div>
          </li>
          <li class="mt-4 d-flex align-items-center">
            <div class="badge me-3 rounded bg-label-info p-2">
              <i class="ti ti-gender-female ti-md"></i>
            </div>
            <div class="d-flex w-100 flex-wrap justify-content-between">
              <h6 class="mb-0 small">{{ __('startup.female_employees') }}</h6>
              <span class="small fw-bold">{{ $femaleEmployees }}</span>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>

  {{-- 5. FOCUS AREAS --}}
  @if (!empty($industry) || (!empty($sectors) && $sectors->isNotEmpty()) || $uniqueTechnologies->isNotEmpty() || $technologiesUsed->isNotEmpty())
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom">
          <h5 class="mb-0">{{ $isEn ? 'Focus Areas' : 'مجالات التركيز' }}</h5>
        </div>
        <div class="card-body p-4">
          @if (!empty($industry) || (!empty($sectors) && $sectors->isNotEmpty()))
            <div class="mb-4">
              <h6 class="text-muted text-uppercase small fw-semibold mb-2">
                {{ $isEn ? 'Industry & Sectors' : 'الصناعة والقطاعات' }}
              </h6>
              <div class="d-flex flex-wrap gap-2">
                @if (!empty($industry))
                  <span class="badge bg-label-primary rounded-pill">
                    {{ $industry->{'name_' . $l} ?? $industry->name_en ?? $industry->name ?? 'N/A' }}
                  </span>
                @endif
                @if (!empty($sectors) && $sectors->isNotEmpty())
                  @php
                    $maxSectors       = 6;
                    $visibleSectors   = $sectors->take($maxSectors);
                    $remainingSectors = $sectors->skip($maxSectors);
                  @endphp
                  @foreach ($visibleSectors as $sector)
                    <span class="badge bg-label-secondary rounded-pill">
                      {{ $sector->{'name_' . $l} ?? $sector->name_en ?? $sector->name ?? 'N/A' }}
                    </span>
                  @endforeach
                  @if ($remainingSectors->isNotEmpty())
                    <span
                      class="badge bg-label-info rounded-pill"
                      style="cursor: pointer;"
                      data-bs-toggle="modal"
                      data-bs-target="#sectorsModal"
                    >+{{ $remainingSectors->count() }}</span>
                  @endif
                @endif
              </div>
            </div>
          @endif

          @if ($uniqueTechnologies->isNotEmpty())
            <div class="mb-4">
              <h6 class="text-muted text-uppercase small fw-semibold mb-2">
                {{ $isEn ? 'Technologies' : 'التقنيات' }}
              </h6>
              <div class="d-flex flex-wrap gap-2">
                @foreach ($uniqueTechnologies as $technology)
                  <span class="badge bg-label-secondary rounded-pill">
                    {{ $technology->name ?? '' }}
                  </span>
                @endforeach
              </div>
            </div>
          @endif

          @if ($technologiesUsed->isNotEmpty())
            <div>
              <h6 class="text-muted text-uppercase small fw-semibold mb-2">{{ __('Technologies Used') }}</h6>
              <div class="d-flex flex-wrap gap-2">
                @foreach ($technologiesUsed as $tech)
                  <span class="badge bg-label-primary rounded-pill">{{ $tech }}</span>
                @endforeach
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  @endif

  {{-- 6. REGISTRATION & OFFERING DETAILS --}}
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header border-bottom">
        <div>
          <h5 class="mb-0">{{ __('startup.registration_offering_details_title') }}</h5>
          <small class="text-muted">{{ __('startup.registration_offering_details_subtitle') }}</small>
        </div>
      </div>
      <div class="card-body p-4">
        @if (!$hasRegOffering)
          <div class="text-center py-4">
            <div class="avatar avatar-md mx-auto mb-3">
              <div class="avatar-initial rounded-circle bg-label-secondary">
                <i class="ti ti-building-store ti-md text-secondary"></i>
              </div>
            </div>
            <p class="text-muted mb-0">
              {{ __('startup.no_registration_offering_details_added_yet') }}
            </p>
          </div>
        @else
          <div class="row g-4">
            @if ($regType)
              <div class="col-md-6 col-lg-4">
                <div class="d-flex align-items-start gap-3">
                  <div class="badge rounded bg-label-primary p-2 flex-shrink-0">
                    <i class="ti ti-certificate ti-md"></i>
                  </div>
                  <div class="min-w-0">
                    <small class="d-block text-muted text-uppercase fw-semibold mb-1"
                      style="font-size: 0.7rem; letter-spacing: 0.05em;">
                      {{ __('startup.registration_type') }}
                    </small>
                    <span class="fw-semibold">{{ $regType }}</span>
                  </div>
                </div>
              </div>
            @endif

            @if ($offeringType)
              <div class="col-md-6 col-lg-4">
                <div class="d-flex align-items-start gap-3">
                  <div class="badge rounded bg-label-info p-2 flex-shrink-0">
                    <i class="ti ti-package ti-md"></i>
                  </div>
                  <div class="min-w-0">
                    <small class="d-block text-muted text-uppercase fw-semibold mb-1"
                      style="font-size: 0.7rem; letter-spacing: 0.05em;">
                      {{ __('startup.offering_type') }}
                    </small>
                    <span class="fw-semibold">{{ $offeringType }}</span>
                  </div>
                </div>
              </div>
            @endif

            {{-- <div class="col-md-6 col-lg-4">
              <div class="d-flex align-items-start gap-3">
                <div class="badge rounded bg-label-{{ $generatesRevenue ? 'success' : 'secondary' }} p-2 flex-shrink-0">
                  <i class="ti ti-{{ $generatesRevenue ? 'trending-up' : 'trending-down' }} ti-md"></i>
                </div>
                <div class="min-w-0">
                  <small class="d-block text-muted text-uppercase fw-semibold mb-1"
                    style="font-size: 0.7rem; letter-spacing: 0.05em;">
                    {{ $isEn ? 'Revenue Status' : 'حالة الإيرادات' }}
                  </small>
                  @if ($generatesRevenue)
                    <span class="fw-semibold text-success">
                      {{ $isEn ? 'Generating Revenue' : 'تولّد إيرادات' }}
                    </span>
                    @if ($revenueAmount > 0)
                      <small class="d-block text-muted mt-1">
                        {{ number_format($revenueAmount) }} {{ $currencyCode }}
                      </small>
                    @endif
                  @else
                    <span class="fw-semibold text-muted">
                      {{ $isEn ? 'Pre-Revenue' : 'ما قبل الإيرادات' }}
                    </span>
                  @endif
                </div>
              </div>
            </div> --}}

            @if ($productsDesc)
              <div class="col-12">
                <hr class="my-2 opacity-25">
                <div class="d-flex flex-column">
                  <div class="d-flex align-items-center gap-3">
                    <div class="badge rounded bg-label-warning p-2 flex-shrink-0">
                      <i class="ti ti-list-details ti-md"></i>
                    </div>
                    <small class="d-block text-muted text-uppercase fw-semibold"
                      style="font-size: 0.7rem; letter-spacing: 0.05em;">
                      {{ __('startup.products_services_description') }}
                    </small>
                  </div>
                  <div class="min-w-0 flex-grow-1">
                    <p class="mb-0 text-break" style="word-break: break-word; overflow-wrap: break-word; white-space: pre-line; line-height: 1.6;">
                      {{ $productsDesc }}
                    </p>
                  </div>
                </div>
              </div>
            @endif
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- 7. INTERACTIVE BADGE RECOGNITION GRID --}}
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">{{ __('startup.badges.badges_recognition') }}</h5>
      </div>
      <div class="card-body pt-4">
        <div class="row g-6">
          @foreach ($achievements as $key => $item)
            @if ($loop->index === 1 || $loop->index === 3)
              @continue
            @endif
            @php
              $idx        = $loop->index;
              $isLocked   = (bool) ($item['locked'] ?? true);
              $itemDate   = $item['date'] ?? null;
              $itemCount  = $item['count'] ?? null;
              $itemTitle  = $item['title'] ?? null;

              $badgeName = $isLocked
                ? __('startup.badges.locked')
                : __('startup.badges.' . ($badgeLabels[$idx] ?? 'unknown'));

              $badgeDescription = $key === 'alumni'
                ? __('startup.badges.alumni_description', [
                    'program' => $itemTitle ?? __('startup.badges.incubator_program'),
                  ])
                : __('startup.badges.' . $key . '_description');

              $criteriaMet = '';
              if (!$isLocked) {
                if ($key === 'alumni') {
                  $criteriaMet = __('startup.badges.graduated_from_program', [
                    'program' => $itemTitle ?? __('startup.badges.incubator_program'),
                  ]);
                } elseif ($itemCount !== null && isset($achievemenLabels[$key])) {
                  $criteriaMet = __($achievemenLabels[$key], ['count' => $itemCount]);
                } elseif ($key === 'profile_complete') {
                  $criteriaMet = __('startup.badges.100_profile_complete');
                }
              }

              $badgeData = [
                'key'         => $key,
                'locked'      => $isLocked,
                'name'        => $badgeName,
                'description' => $badgeDescription,
                'icon'        => $badgeIcons[$idx] ?? 'fa-star',
                'color'       => $badgeColors[$idx] ?? 'secondary',
                'unlockHint'  => $isLocked ? ($unlockHints[$idx] ?? '') : '',
                'dateEarned'  => (!$isLocked && $itemDate)
                  ? \Carbon\Carbon::parse($itemDate)->format('F Y')
                  : '',
                'criteriaMet' => $criteriaMet,
              ];
            @endphp
            <div class="col-xl-2 col-md-4 col-6">
              <div
                class="card badge-card h-100 cursor-pointer opacity-{{ $isLocked ? '50' : '100' }} border shadow-none"
                @if ($isLocked)
                  data-bs-toggle="tooltip"
                  data-bs-placement="top"
                  data-bs-title="{{ $unlockHints[$idx] ?? '' }}"
                @endif
                data-badge="{{ json_encode($badgeData) }}"
                style="cursor: pointer;"
              >
                <div class="card-body position-relative text-center">
                  @if ($isLocked)
                    <div class="position-absolute top-0 mt-3 {{ app()->getLocale() === 'en' ? 'end-0 me-3' : 'start-0 ms-3' }}">
                      <i class="ti ti-lock"></i>
                    </div>
                  @endif
                  <div class="d-flex flex-column align-items-center justify-content-center">
                    <div class="avatar avatar-lg mb-3">
                      <div class="avatar-initial rounded-circle bg-label-{{ $isLocked ? 'light-subtle' : ($badgeColors[$idx] ?? 'secondary') }} text-{{ $badgeColors[$idx] ?? 'secondary' }}">
                        <i class="fa-solid {{ $badgeIcons[$idx] ?? 'fa-star' }} text-{{ $badgeColors[$idx] ?? 'secondary' }}"></i>
                      </div>
                    </div>
                    <div class="mb-2 rounded-pill bg-label-{{ $isLocked ? 'secondary' : ($badgeColors[$idx] ?? 'secondary') }} {{ $isLocked ? 'text-dark' : '' }} badge text-uppercase">
                      {{ $isLocked ? __('startup.badges.locked') : ($badgeLabels[$idx] ?? '') }}
                    </div>
                    <h6 class="mb-1 text-dark fw-semibold">
                      {{ ($itemTitle && !$isLocked) ? $itemTitle : __('startup.badges.' . $key) }}
                    </h6>
                    @if ($isLocked)
                      <small class="d-block w-100 text-truncate text-muted">
                        {{ $unlockHints[$idx] ?? '' }}
                      </small>
                    @else
                      <small class="text-muted">
                        @if ($itemDate)
                          {{ \Carbon\Carbon::parse($itemDate)->format('F Y') }}
                        @elseif ($itemCount !== null && isset($achievemenLabels[$key]))
                          {{ __($achievemenLabels[$key], ['count' => $itemCount]) }}
                        @elseif ($key === 'profile_complete')
                          {{ __('startup.badges.100_profile_complete') }}
                        @endif
                      </small>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- 8. SUSTAINABILITY GOALS WIDGET --}}
  @if (!empty($sustainable_array) && count($sustainable_array) > 0)
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom">
          <h5 class="mb-0 small fw-bold">{{ __('startup.sustainability_goals') }}</h5>
        </div>
        <div class="card-body pt-3">
          <div class="d-flex flex-wrap gap-2">
            @foreach ($sustainable_array as $Sustainable)
              @php
                $icon = App\SustainableIcon::where('title', $Sustainable)->first();
              @endphp
              <div class="rounded border p-1" style="width: 150px; height: 150px;">
                @if ($icon && !empty($icon->icon_ar))
                  <img
                    src="/images/sustainability/{{ $l }}/{{ $icon->icon_ar }}"
                    class="img-fluid"
                    data-bs-toggle="tooltip"
                    title="{{ e($Sustainable) }}"
                    alt="{{ e($Sustainable) }}"
                  >
                @endif
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- 9. DOCUMENT CENTER TABLE --}}
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header border-bottom">
        <h5 class="mb-0">{{ __('admin.Documents') }}</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <tbody>
            @foreach (['business_plan', 'financial_plan', 'Commercial_Register', 'Tax_Card'] as $field)
              @if (!empty($co->$field))
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="badge me-3 rounded bg-label-secondary p-2">
                        <i class="ti ti-file-text"></i>
                      </div>
                      <span class="fw-bold">{{ __('admin.' . $field) }}</span>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-label-info">{{ $l == 'ar' ? 'مرفق رسمي' : 'Official Attachment' }}</span>
                  </td>
                  <td class="text-end">
                    <a
                      @if( in_array($field, ['financial_plan', 'business_plan']) )
                      href="{{ app(\App\Services\FileManagerService::class)->getUrl( $co->$field,  'pitch_deck') }}"
                      @elseif( in_array($field, ['Commercial_Register', 'Tax_Card']) )
                      href="{{ app(\App\Services\FileManagerService::class)->getUrl( $co->$field,  'document') }}"
                      @else
                      href="{{ $co->$field }}"
                      @endif
                      target="_blank"
                      rel="noopener noreferrer"
                      class="btn btn-sm btn-icon rounded-pill btn-label-secondary"
                    >
                      <i class="ti ti-download"></i>
                    </a>
                  </td>
                </tr>
              @endif
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

{{-- ABOUT CHALLENGES OFFCANVAS --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
  <div class="offcanvas-header border-bottom">
    <div>
      <h5 class="offcanvas-title mb-1" id="offcanvasRightLabel">{{ __('startup.about_challenges') }}</h5>
      <small class="text-muted">
        {{ $isEn ? 'All strategy details' : 'جميع تفاصيل الاستراتيجية' }}
      </small>
    </div>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <div class="d-flex flex-column gap-4">
      @foreach ($strategyTabs as $detail)
        <div class="card border shadow-none mb-0">
          <div class="card-body">
            <div class="d-flex align-items-start gap-3">
              <div class="badge rounded bg-label-{{ $detail['color'] }} p-2 flex-shrink-0">
                <i class="ti {{ $detail['icon'] }} ti-md"></i>
              </div>
              <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                  <h6 class="mb-0">{{ $detail['title'] }}</h6>
                  @if (!empty($detail['badge']))
                    <span class="badge bg-label-{{ $detail['color'] }}">{{ $detail['badge'] }}</span>
                  @endif
                </div>
                <p class="text-muted small mb-2">{{ $detail['heading'] }}</p>
                <p class="text-break mb-0" style="white-space: pre-line; word-break: break-word;">{{ $detail['value'] }}</p>
                @if (!empty($detail['link']))
                  <a href="{{ $detail['link'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary mt-3">
                    <i class="ti ti-link me-1"></i>
                    {{ __('startup.mvp_prototype_link') }}
                  </a>
                @endif
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>

{{-- CATEGORIES MODAL --}}
@if ($remainingCategoriesCount > 0)
  <div class="modal fade" id="categoriesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header">
          <h5 class="modal-title">{{ $isEn ? 'All Categories' : 'فئات الشركة' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="d-flex flex-wrap gap-2">
            @foreach ($displayCategories as $category)
              <span class="badge bg-label-primary rounded-pill">{{ $category }}</span>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
@endif

{{-- TARGET MARKETS MODAL --}}
@if ($remainingTargetMarketsCount > 0)
  <div class="modal fade" id="targetMarketsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('admin.Targeted_Market') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="d-flex flex-wrap gap-2">
            @foreach ($targetedMarketNames as $marketName)
              <span class="badge bg-label-secondary rounded-pill">{{ $marketName }}</span>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
@endif

{{-- BADGE MODAL --}}
<div class="modal fade" id="badgeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('startup.badges.badge_details') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-5 text-center">
        <div class="avatar avatar-xl mx-auto mb-4">
          <div class="avatar-initial rounded-circle" id="modalBadgeIcon"></div>
        </div>
        <h4 id="modalBadgeName" class="mb-2"></h4>
        <p class="text-muted" id="modalBadgeDescription"></p>
        <div id="earnedBadgeContent" class="mt-4 d-none rounded bg-label-success p-3 text-start">
          <div class="row">
            <div class="col-6">
              <strong>{{ __('startup.badges.date_earned') }}:</strong>
              <div id="modalDateEarned"></div>
            </div>
            <div class="col-6">
              <strong>{{ __('startup.badges.criteria_met') }}:</strong>
              <div id="modalCriteriaMet"></div>
            </div>
          </div>
        </div>
        <div id="lockedBadgeContent" class="mt-4 d-none alert alert-warning text-start">
          <strong>{{ __('startup.badges.requirements_to_unlock') }}:</strong>
          <p id="modalRequirements" class="mb-0"></p>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
  <script>
    $(function () {
      $('.badge-card').on('click', function () {
        const data = $(this).data('badge');
        if (!data) return;

        const color = data.locked ? 'secondary' : (data.color || 'secondary');
        const icon  = data.icon || 'fa-star';

        $('#modalBadgeIcon')
          .attr('class', `avatar-initial rounded-circle bg-label-${color}`)
          .html(`<i class="fa-solid ${icon} fs-3"></i>`);

        $('#modalBadgeName').text(data.name || '');
        $('#modalBadgeDescription').text(data.description || '');

        if (data.locked) {
          $('#earnedBadgeContent').addClass('d-none');
          $('#lockedBadgeContent').removeClass('d-none');
          $('#modalRequirements').text(data.unlockHint || '');
        } else {
          $('#lockedBadgeContent').addClass('d-none');
          $('#earnedBadgeContent').removeClass('d-none');
          $('#modalDateEarned').text(data.dateEarned || 'N/A');
          $('#modalCriteriaMet').text(data.criteriaMet || 'N/A');
        }

        $('#badgeModal').modal('show');
      });
    });
  </script>
@endpush
