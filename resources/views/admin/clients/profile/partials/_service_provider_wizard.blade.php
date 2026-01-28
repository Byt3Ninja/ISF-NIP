{{-- {{ dd($serviceProviderSteps) }} --}}
@php
  $max_steps = count($serviceProviderSteps);
  $active_step = request()->get('step', 1);
@endphp
<div class="bs-stepper wizard-numbered" id="wizard-validation">
  <div class="bs-stepper-header">
    @foreach ($serviceProviderSteps as $key => $step)
      <div class="step {{ $active_step == $key + 1 ? 'active' : '' }} {{ $active_step > $key + 1 ? 'crossed' : '' }}" data-target="#{{ $step['id'] }}">                
        <button
          type="button"
          class="step-trigger"
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
    <form id="service-provider-form-wizard" data-route-step-1="{{ route('services_providers.step1.store') }}">
      @foreach ($serviceProviderSteps as $key => $step)
        <div id="{{ $step['id'] }}" class="content {{ $active_step == $key + 1 ? 'active' : '' }}">
          <div class="content-header mb-6">
            <h5 class="mb-0">{{ $step['title'] }}</h5>
            <small>{{ __('admin.setup') }} {{ $step['title'] }}</small>
          </div>
          
          @include(
            'admin.clients.profile.partials._service_provider_' . $step['id'], [
              'client' => $client ?? null,
            ]
          )
        </div>
      @endforeach

      <div class="d-flex justify-content-between align-items-center mt-6">
        <button class="btn btn-label-secondary btn-prev waves-effect">
          <i class="ti ti-arrow-left ti-xs me-sm-2 me-0"></i>
          <span class="align-middle d-sm-inline-block d-none">{{ __('admin.Previous') }}</span>
        </button>
        <button type="button" class="btn btn-{{ $active_step == $max_steps ? 'success' : 'primary' }} btn-next waves-effect waves-light">
          <span class="align-middle d-sm-inline-block d-none me-sm-2">
            {{ $active_step == $max_steps ? __('admin.Submit') : __('admin.Next') }}
          </span>
          @if ($active_step != $max_steps)
            <i class="ti ti-arrow-right ti-xs"></i>
          @endif
        </button>
      </div>
    </form>
  </div>
</div>