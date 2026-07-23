@php
  $max_steps = count($serviceProviderSteps);
@endphp

<div class="bs-stepper wizard-numbered" id="wizard-validation">
  <div class="bs-stepper-header">
    @foreach ($serviceProviderSteps as $key => $step)
      <div class="step {{ $key == 0 ? 'active' : '' }}" data-target="#{{ $step['id'] }}" data-tab-index="{{ $key + 1 }}">                
        <button
          type="button"
          class="step-trigger"
          style="cursor: default !important;"
          data-tab-index="{{ $key + 1 }}"
          {{ ($key + 1 > $max_steps) ? 'disabled' : '' }}
        >
          <span class="bs-stepper-circle">{{ $key + 1 }}</span>
          <span class="bs-stepper-label">
            <span class="bs-stepper-title">{{ $step['title'] }}</span>
            <span class="bs-stepper-subtitle">{{ __('admin.Add') }} {{ $step['title'] }}</span>
          </span>
        </button>
      </div>
      @if (!$loop->last)
       <div class="line">
          <i class="ti ti-chevron-right"></i>
        </div>
      @endif
    @endforeach
  </div>

  <div class="bs-stepper-content">
    <form id="service-provider-form-wizard" data-route-step-1="{{ route('services_providers.step1.store') }}" data-route-step-2="{{ route('services_providers.step2.store') }}" data-route-step-3="{{ route('services_providers.step3.store') }}" data-route-step-4="{{ route('services_providers.step4.store') }}">
      @csrf
      @if(isset($serviceProviderId))
        <input type="hidden" name="service_provider_id" value="{{ $serviceProviderId }}">
      @endif
      @foreach ($serviceProviderSteps as $key => $step)
        <div id="{{ $step['id'] }}" class="content {{ $key == 0 ? 'active' : '' }}">
          <div class="content-header mb-6">
            <h5 class="mb-0">{{ $step['title'] }}</h5>
            <p>{{ $step['subtitle'] }}</p>
          </div>
          
          @include(
            'admin.clients.profile.partials._service_provider_' . $step['id'], [
              'client' => $client ?? null,
            ]
          )
        </div>
      @endforeach

      <div class="border-top mt-6 px-0">
        <div class="d-flex justify-content-between align-items-center mt-6">
          <button type="button" class="btn btn-label-secondary btn-prev waves-effect" style="visibility: hidden;">
            <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
            <span class="align-middle d-sm-inline-block d-none">{{ __('admin.Previous') }}</span>
          </button>
          <button type="button" class="btn btn-primary btn-next waves-effect waves-light">
            <span class="align-middle d-sm-inline-block d-none me-sm-2">
              {{ __('admin.save_and_next') }}
            </span>
            <i class="ti ti-arrow-right ti-xs"></i>
          </button>
        </div>
      </div>

    </form>
  </div>
</div>