<div class="mb-6">
  <div class="row gy-6">

    {{-- About Problem --}}
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">

          <h4 class="mb-4">{{ __('startup.about_challenges') }}</h4>

          <h6 class="text-muted mb-1">{{ __('startup.about_problem') }}</h6>
          <div class="d-flex mb-4">
            <p class="mb-0 line-clamp-2">{{ $company->about_problem }}</p>
          </div>

          @if ($company->about_market_opportunity)
            <h6 class="text-muted mb-1">{{ __('admin.about_market_opportunity') }}</h6>
            <div class="d-flex mb-4">
              <p class="mb-0 line-clamp-2">{{ $company->about_market_opportunity }}</p>
            </div>
          @endif

          @if ($company->about_competitors)
            <h6 class="text-muted mb-1">{{ __('admin.about_competitors') }}</h6>
            <div class="d-flex mb-4">
              <p class="mb-0 line-clamp-2">{{ $company->about_competitors }}</p>
            </div>
          @endif

          @if ($company->about_monetize)
            <h6 class="text-muted mb-1">{{ __('startup.about_monetization') }}</h6>
            <div class="d-flex mb-4">
              <p class="mb-0 line-clamp-2">{{ $company->about_monetize }}</p>
            </div>
          @endif

          @if ($company->about_challenges)
            <h6 class="text-muted mb-1">{{ __('admin.about_challenges') }}</h6>
            <div class="d-flex">
              <p class="mb-0 line-clamp-2">{{ $company->about_challenges }}</p>
            </div>
          @endif

        </div>
      </div>
    </div>

    <!-- Sustainable Development Icons -->
    @php
     $Sustainables = json_decode($company->Sustainable_development);
    @endphp

    @if (!empty($Sustainables) && count($Sustainables) > 0)
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">
            {{ $l == 'en' ? 'Sustainability Goals' : 'اهداف الاستدامة' }}
          </h5>
          <div class="row w-75">
            @foreach ($Sustainables as $Sustainable)
            @php
            $icon = App\SustainableIcon::where('title', $Sustainable)->first();
            @endphp
            <div class="col-md-2 mb-2">
              <div class="border light-shadow radius-15" style="display: grid; place-items: center;">
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
    <!-- / Sustainable Development Icons -->

    {{-- Business Model --}}
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">

          <h4 class="mb-4">{{ __('admin.business_model') }}</h4>

          {{-- Business Model --}}
          <div class="pb-4 border-bottom">

            <h6 class="text-muted mb-1">{{ __('admin.business_model') }}</h6>
            <div class="d-flex justify-content-between align-items-start">
              @if ($company->business_model)
              <div class="d-flex align-items-center">
                <i class="ti ti-alert-circle text-primary me-2"></i>
                <span class="fw-medium">{{ __('admin.' . $company->business_model) }}</span>
              </div>
              @endif

              @if ($company->business_model == 'Other')
                <div class="d-flex align-items-center ms-3">
                  <i class="ti ti-chart-bar text-primary me-2"></i>
                  <span class="fw-medium">{{ $company->other_business_model }}</span>
                </div>
              @endif
            </div>

          </div>

          {{-- Description + Business Plan --}}
          <div class="pt-4">

            @if ($company->business_model_description)
            <h6 class="text-muted mb-1">{{ __('admin.business_model_description') }}</h6>
            <div class="d-flex align-items-start mb-4">
              <i class="ti ti-flag text-primary me-2"></i>
              <span>{{ $company->business_model_description }}</span>
            </div>
            @endif

            @php $fileNames = basename($company->business_plan); @endphp

            <h6 class="text-muted mb-1">{{ __('admin.business_plan') }}</h6>
            <div class="d-flex align-items-center">
              <div class="d-flex align-items-center p-2 border rounded-3 bg-label-secondary">
                <i class="ti ti-file  text-warning me-2"></i>
                <span class="me-3">{{ $fileNames }}</span>
                <a class="btn btn-sm btn-outline-primary" href="{{ $company->business_plan }}" target="_blank" download>
                  <i class="ti ti-download"></i>
                </a>
              </div>
            </div>

          </div>

        </div>
      </div>
    </div>

    {{-- Financial Plan --}}
    @if ($company->financial_plan || $company->financial_plan_description)
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-body">

            <h4 class="mb-4">{{ __('admin.financial_plan') }}</h4>

            <h6 class="text-muted mb-1">{{ __('admin.financial_plan_description') }}</h6>
            <div class="d-flex align-items-start mb-4">
              <i class="ti ti-flag text-primary me-2"></i>
              <span>{{ $company->financial_plan_description }}</span>
            </div>

            @php $fileNamesf = basename($company->financial_plan); @endphp

            <h6 class="text-muted mb-1">{{ __('admin.financial_plan_file') }}</h6>
            <div class="d-flex align-items-center">
              <div class="d-flex align-items-center p-2 border rounded-3 bg-light">
                <i class="ti ti-file fs-2 text-warning me-2"></i>
                <span class="me-3">{{ $fileNamesf }}</span>
                <a class="btn btn-sm btn-outline-primary" href="{{ $company->financial_plan }}" target="_blank" download>
                  <i class="ti ti-download fs-4"></i>
                </a>
              </div>
            </div>

          </div>
        </div>
      </div>
    @endif

  </div>
</div>
