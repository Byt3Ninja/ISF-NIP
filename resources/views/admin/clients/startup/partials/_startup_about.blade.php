<div class="row gy-4">

    <!-- About Challenges -->
    <div class="col-12">
      <div class="card border shadow-sm">
        <div class="card-body p-4">

          <span class="badge bg-label-danger d-inline-flex align-items-center gap-1 mb-3">
            <i class="ti ti-alert-triangle"></i> {{ __('startup.about_challenges') }}
          </span>
          <h5 class="card-title fw-semibold mb-4">{{ __('startup.problem_being_solved') }}</h5>

          @if ($company->about_problem)
            <h6 class="text-muted text-uppercase small fw-semibold mb-1">{{ __('startup.problem') }}</h6>
            <p class="mb-4">{{ $company->about_problem }}</p>
          @endif

          @if ($company->about_market_opportunity)
            <h6 class="text-muted text-uppercase small fw-semibold mb-1">{{ __('startup.about_market_opportunity') }}</h6>
            <p class="mb-4">{{ $company->about_market_opportunity }}</p>
          @endif

          @if ($company->about_competitors)
            <h6 class="text-muted text-uppercase small fw-semibold mb-1">{{ __('startup.competitors') }}</h6>
            <p class="mb-4">{{ $company->about_competitors }}</p>
          @endif

          @if ($company->about_monetize)
            <h6 class="text-muted text-uppercase small fw-semibold mb-1">{{ __('startup.monetization') }}</h6>
            <p class="mb-4">{{ $company->about_monetize }}</p>
          @endif

          @if ($company->about_challenges)
            <h6 class="text-muted text-uppercase small fw-semibold mb-1">{{ __('startup.challenges') }}</h6>
            <p class="mb-0">{{ $company->about_challenges }}</p>
          @endif

        </div>
      </div>
    </div>

    <!-- Mission -->
    @if ($company->mission)
    <div class="col-12">
      <div class="card border shadow-sm">
        <div class="card-body p-4">

          <span class="badge bg-label-primary d-inline-flex align-items-center gap-1 mb-3">
            <i class="ti ti-target"></i> {{ __('startup.mission') }}
          </span>
          <h5 class="card-title fw-semibold mb-3">{{ __('startup.our_mission') }}</h5>
          <p class="mb-0">{{ $company->mission }}</p>

        </div>
      </div>
    </div>
    @endif

    <!-- Why This Startup? -->
    @if ($company->why_startup)
    <div class="col-12">
      <div class="card border shadow-sm">
        <div class="card-body p-4">

          <span class="badge text-bg-success d-inline-flex align-items-center gap-1 mb-3">
            <i class="ti ti-sparkles"></i> {{ __('startup.why_this_startup') }}
          </span>
          <h5 class="card-title fw-semibold mb-3">{{ __('startup.what_makes_us_different') }}</h5>
          <p class="mb-0">{{ $company->why_startup }}</p>

        </div>
      </div>
    </div>
    @endif

    <!-- Achievements -->
    @if ($company->achievements)
    <div class="col-12">
      <div class="card border shadow-sm">
        <div class="card-body p-4">

          <span class="badge bg-label-warning d-inline-flex align-items-center gap-1 mb-3">
            <i class="ti ti-trophy"></i> {{ __('startup.achievements') }}
          </span>
          <p class="mb-0">{{ $company->achievements }}</p>

        </div>
      </div>
    </div>
    @endif

    <!-- Startup Expertise -->
    @if ($company->startup_expertise)
    <div class="col-12">
      <div class="card border shadow-sm">
        <div class="card-body p-4">

          <span class="badge bg-label-secondary d-inline-flex align-items-center gap-1 mb-3">
            <i class="ti ti-briefcase"></i> {{ __('startup.startup_expertise') }}
          </span>
          <h5 class="card-title fw-semibold mb-3">{{ __('startup.areas_of_expertise') }}</h5>
          <p class="mb-0">{{ $company->startup_expertise }}</p>

        </div>
      </div>
    </div>
    @endif

    <!-- Sustainability Goals -->
    @php
     $sustainable_array = json_decode($company->Sustainable_development ?? '[]');
    @endphp

    @if (!empty($sustainable_array) && count($sustainable_array) > 0)
    <div class="col-12">
      <div class="card shadow-sm pe-md-12">
        <div class="card-body">
          <h5 class="card-title mb-4">{{ __('startup.sustainability_goals') }}</h5>
          <div class="row g-6 pe-md-12">
            @foreach ($sustainable_array as $Sustainable)
            @php
            $icon = App\SustainableIcon::where('title', $Sustainable)->first();
            @endphp
            <div class="col-6 col-md-2">
              <div class="card p-3 shadow-sm border d-flex align-items-center justify-content-center">
                @if ($icon && $icon->icon_ar)
                  <img src="/images/sustainability/{{ $l }}/{{ $icon->icon_ar ?? '1.png' }}" alt="icon"
                    style="width: 100px; height: 100px;">
                @endif
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    @endif

    <!-- Business Model -->
    @if ($company->business_model || $company->business_model_description || $company->business_plan)
    <div class="col-12">
      <div class="card border shadow-sm">
        <div class="card-body p-4">

          <span class="badge bg-label-secondary d-inline-flex align-items-center gap-1 mb-3">
            <i class="ti ti-layout-grid"></i> {{ __('startup.business_model') }}
          </span>
          <h5 class="card-title fw-semibold mb-4">{{ __('startup.how_we_operate') }}</h5>

          @if ($company->business_model)
            <div class="pb-3 border-bottom mb-3">
              <h6 class="text-muted text-uppercase small fw-semibold mb-2">{{ __('startup.model_type') }}</h6>
              <span class="badge bg-label-primary d-inline-flex align-items-center gap-1 fs-6 fw-normal px-3 py-2">
                <i class="ti ti-arrows-left-right me-2"></i>
                {{ __('admin.' . $company->business_model) ?: $company->other_business_model }}
              </span>
            </div>
          @endif

          @if ($company->business_model_description)
            <h6 class="text-muted text-uppercase small fw-semibold mb-1">{{ __('startup.description') }}</h6>
            <p class="mb-4">{{ $company->business_model_description }}</p>
          @endif

          @if ($company->business_plan)
            <h6 class="text-muted text-uppercase small fw-semibold mb-2">{{ __('startup.business_plan') }}</h6>
            <a href="{{ app(\App\Services\FileManagerService::class)->getUrl($company->business_plan, 'pitch_deck') }}" target="_blank" download
              class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2">
              <i class="ti ti-file text-warning"></i>
              {{ basename($company->business_plan) }}
              <i class="ti ti-download"></i>
            </a>
          @endif

        </div>
      </div>
    </div>
    @endif

    <!-- Financial Plan -->
    @if ($company->financial_plan_description || $company->financial_plan)
    <div class="col-12">
      <div class="card border shadow-sm">
        <div class="card-body p-4">

          <span class="badge bg-label-success d-inline-flex align-items-center gap-1 mb-3">
            <i class="ti ti-chart-line"></i> {{ __('startup.financial_plan') }}
          </span>
          <h5 class="card-title fw-semibold mb-4">{{ __('startup.financial_projections') }}</h5>

          @if ($company->financial_plan_description)
            <h6 class="text-muted text-uppercase small fw-semibold mb-1">{{ __('startup.description') }}</h6>
            <p class="mb-4">{{ $company->financial_plan_description }}</p>
          @endif

          @if ($company->financial_plan)
            <h6 class="text-muted text-uppercase small fw-semibold mb-2">{{ __('startup.financial_plan') }}</h6>
            <a href="{{ URL::to( $company->financial_plan ) }}" target="_blank" download
              class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2">
              <i class="ti ti-file text-warning"></i>
              {{ basename($company->financial_plan) }}
              <i class="ti ti-download"></i>
            </a>
          @endif

        </div>
      </div>
    </div>
    @endif

</div>