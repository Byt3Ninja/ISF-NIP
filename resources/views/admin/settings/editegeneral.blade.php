@extends('layouts.admin')

@section('style')
  <style>
     .custom-container {
      width: 100%;
    }

    @media screen and (min-width: 768px) {
      .custom-container {
        width: 75%;
      }
    }
  </style>
@stop

@php
  $title = $l == 'en' ? 'Settings' : 'الإعدادات';

  $tabs = collect([
    ['general', 'ti-settings', __('settings.General Settings')],
    ['analysis', 'ti-graph', __('settings.Analysis Settings')],
    ['SMTP', 'ti-mail', __('settings.SMTP Configuration')],
    ['app-identity', 'ti-id-badge', __('settings.Application Identity')],
    ['Notification', 'ti-bell', __('settings.Push Notification Settings')],
    ['MeetingTab', 'ti-device-computer-camera', __('settings.Meeting Setting')],
    ['SocialMediaLinks', 'ti-share', __('settings.SocialMediaLinks')],
    ['SocialMediaLogin', 'ti-brand-facebook', __('settings.SocialMediaLogin')],
    ['HomePage', 'ti-home', __('admin.HomePageSetting')],
    ['WhatsApp', 'ti-home', __('admin.WhatsApp')],
  ])->map(function ($tab) {
    return ['target' => $tab[0], 'icon' => $tab[1], 'label' => $tab[2]];
  })->toArray();
@endphp

@section('content')
  <!-- Content wrapper -->
  <div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
      <!-- Alert -->
      @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ __('admin.' . session()->get('message')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      @if (session()->has('message2'))
        <div class="alert alert-danger alert-dismissible" role="alert">
          {{ __('admin.' . session()->get('message2')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if ($errors->any())
    <div class="alert alert-danger alert-dismissible" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
      <!-- / Alert -->
      <!-- Tabs (mobile) -->
      <div style="max-width: 100%; overflow-x: auto;" class="mb-4 d-md-none">
        <ul class="nav nav-pills flex-nowrap" role="tablist">
          @foreach ($tabs as $key => $tab)
            <li class="nav-item d-flex justify-content-center align-items-center">
              <a href="#navs-left-{{ $tab['target'] }}"
                class="nav-link {{ $key == 0 ? 'active' : '' }}"
                data-bs-toggle="tab" role="tab">
                <i class="ti {{ $tab['icon'] }} me-2 fs-5"></i>
                {{ $tab['label'] }}
              </a>
            </li>
          @endforeach
        </ul>
      </div>
      <!-- / Tabs (mobile) -->
      <div class="d-flex justify-content-start w-100 mb-6">
        <nav 
          class="nav-align-left nav-tabs-shadow mb-6 p-4 bg-white position-fixed d-none d-md-block" 
          style="height: 500px; min-width: 18%; max-width: 18%;">
          <!-- Tabs -->
          <ul class="nav nav-tabs nav-fill border-none m-auto" role="tablist">
            @foreach ($tabs as $key => $tab)
              <li class="nav-item bg-label-light mb-2 rounded overflow-x-hidden d-flex align-items-center">
                <a href="#navs-left-{{ $tab['target'] }}"
                  class="nav-link {{ $key == 0 ? 'active' : '' }} py-3"
                  data-bs-toggle="tab" role="tab">
                  <i class="ti {{ $tab['icon'] }} me-2 fs-5"></i>
                  {{ $tab['label'] }}
                </a>
              </li>
            @endforeach
          </ul>
          <!-- / Tabs -->
        </nav>
        <!-- Tab Content On Tab Click -->
        <div class="tab-content p-0 overflow-y-auto custom-container mb-6" 
          style="{{ $l == 'en' ? 'margin-left: auto' : 'margin-right: auto' }}"
        >
          @foreach ($tabs as $index => $tab)
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }} mb-6"
              id="navs-left-{{ $tab['target'] }}">
              <div class="card">
                <div class="card-header">
                  <h5 class="card-title pb-3 border-bottom">{{ $tab['label'] }}</h5>
                </div>
                <div class="card-body">
                  @switch($tab['target'])
                    @case('general')
                      @include('partials.forms.general-settings-form')
                      @break
                    @case('analysis')
                      @include('partials.forms.analysis-settings-form')
                      @break
                    @case('SMTP')
                      @include('partials.forms.SMTP-configuration-form')
                      @break
                    @case('app-identity')
                      @include('partials.forms.app-identity-form')
                      @break
                    @case('Notification')
                      @include('partials.forms.notification-settings-form')
                      @break
                    @case('MeetingTab')
                      @include('partials.forms.meeting-settings-form')
                      @break
                    @case('SocialMediaLinks')
                      @include('partials.forms.social-link-form')
                      @break
                    @case('SocialMediaLogin')
                      @include('partials.forms.social-login-form')
                      @break
                    @case('HomePage')
                      @include('partials.forms.home-settings-form')
                      @break
                      @case('WhatsApp')
                      @include('partials.forms.whatsapp-settings-form')
                      @break
                  @endswitch
                </div>
              </div>
            </div>
          @endforeach
        </div>
        <!-- / Tab Content On Tab Click -->
      </div>
    </div>
    <!-- / Content -->
  </div>
  <!-- / Content wrapper -->
  <script>
    $(document).ready(function() {
      getStates($('#country_id').val());
      $('#country_id').change(function() {
        getStates($(this).val());
      });
      $('#state_id').change(function() {
        getCities($(this).val());
      });
    });

    function getStates(country_id) {
      var $state = $('#state_id');
     
      var currentState = '{{ $Title->State }}';
      var selected = '';
      $.ajax({
        url: "{{ route('get_states_by_country') }}",
        data: {
          country_id: country_id
        },
        success: function(data) {
          // console.log(data);
          $state.html('<option value="" disabled >{{ __('admin.State') }}</option>');
          $.each(data, function(id, value) {
            console.log(id, value);
            // selected = '';
            
            if (currentState == id) {
              selected = 'selected';
              // getCities(id)
            } else {
              selected = '';
            }
            
            $state.append('<option value="' + id + '" ' + selected + '>' + value + '</option>');
            $('#city_id').html('<option value="" >{{ __('admin.City') }}</option>');
          });
          $('#state').show(150);
        }
      });
    }

    
  </script>
@stop