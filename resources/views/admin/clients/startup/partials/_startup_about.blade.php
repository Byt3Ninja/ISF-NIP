<div class="mb-6">
  <div class="row gy-6">

    {{-- About Problem --}}
    <div class="col-12">
      <div class="card shadow-sm pe-12">
        <div class="card-body pe-12">

          <h5 class="card-title mb-4">{{ __('startup.about_challenges') }}</h5>

          <h6 class="text-muted mb-1">{{ __('startup.about_problem') }}</h6>
          <p class="mb-4">{{ $company->about_problem }}</p>

          @if ($company->about_market_opportunity)
            <h6 class="text-muted mb-1">{{ __('admin.about_market_opportunity') }}</h6>
            <p class="mb-4">{{ $company->about_market_opportunity }}</p>
          @endif

          @if ($company->about_competitors)
            <h6 class="text-muted mb-1">{{ __('admin.about_competitors') }}</h6>
            <p class="mb-4">{{ $company->about_competitors }}</p>
          @endif

          @if ($company->about_monetize)
            <h6 class="text-muted mb-1">{{ __('startup.about_monetization') }}</h6>
            <p class="mb-4">{{ $company->about_monetize }}</p>
          @endif

          @if ($company->about_challenges)
            <h6 class="text-muted mb-1">{{ __('admin.about_challenges') }}</h6>
            <p class="mb-4">{{ $company->about_challenges }}</p>
          @endif

        </div>
      </div>
    </div>

    <!-- Sustainable Development Icons -->
    @php
     $sustainable_array = json_decode($company->Sustainable_development ?? '[]');
    @endphp

    @if (!empty($sustainable_array) && count($sustainable_array) > 0)
    <div class="col-12">
      <div class="card shadow-sm pe-md-12">
        <div class="card-body">
          <h5 class="card-title mb-4">
            {{ $l == 'en' ? 'Sustainability Goals' : 'اهداف الاستدامة' }}
          </h5>
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
    <!-- / Sustainable Development Icons -->

    {{-- Business Model --}}
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">

          <h5 class="card-title mb-4">{{ __('admin.business_model') }}</h5>

          {{-- Business Model --}}
          <div class="pb-4 border-bottom">
            @if ($company->business_model || $company->other_business_model)
              <h6 class="text-muted mb-1">{{ __('admin.business_model') }}</h6>
              <div class="d-flex justify-content-between align-items-start">
                @if ($company->business_model)
                  <p>{{ __('admin.' . $company->business_model) }}</p>
                @endif

                @if ($company->business_model == 'Other')
                  <p>{{ $company->other_business_model }}</p>
                @endif
              </div>
            @endif
          </div>

          {{-- Description + Business Plan --}}
          <div class="pt-4">
            @if ($company->business_model_description)
              <h6 class="text-muted mb-1">{{ __('admin.business_model_description') }}</h6>
              <p>{{ $company->business_model_description }}</p>
            @endif

            @if ($company->business_plan)
              <h6 class="text-muted mb-1">{{ __('startup.business_plan_attachment') }}</h6>
              <a class="btn btn-sm btn-label-secondary" href="{{ URL::to( $company->business_plan ) }}" target="_blank" download>
                <i class="ti ti-file text-warning me-2"></i>
                <span class="me-3">{{ basename($company->business_plan) }}</span>
                <i class="ti ti-download"></i>
              </a>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Financial Plan --}}
    @if ($company->financial_plan || $company->financial_plan_description || $company->financial_plan)
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-body">

            <h5 class="card-title mb-4">{{ __('admin.financial_plan') }}</h5>

            @if ($company->financial_plan_description)
              <h6 class="text-muted mb-1">{{ __('admin.financial_plan_description') }}</h6>
              <p>{{ $company->financial_plan_description }}</p>
            @endif

            @if ($company->financial_plan)
              <h6 class="text-muted mb-1">{{ __('startup.financial_plan_attachment') }}</h6>
              <a class="btn btn-sm btn-label-secondary" href="{{ URL::to( $company->financial_plan ) }}" target="_blank" download>
                <i class="ti ti-file text-warning me-2"></i>
                <span class="me-3">{{ basename($company->financial_plan) }}</span>
                <i class="ti ti-download"></i>
              </a>
            @endif
          </div>
        </div>
      </div>
    @endif
  </div>
</div>
