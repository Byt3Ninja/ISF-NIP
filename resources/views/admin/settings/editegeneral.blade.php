@extends('layouts.admin')

@section('style')
<!-- Include Dropzone -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/dropzone.min.css">
@stop

@php
  $title = $l == 'en' ? 'Settings' : 'الإعدادات';

  $tabs = collect([
    ['general', __('settings.General Settings')],
    ['analysis', __('settings.Analysis Settings')],
    ['SMTP', __('settings.SMTP Configuration')],
    ['app-identity', __('settings.Application Identity')],
    ['Notification', __('settings.Push Notification Settings')],
    ['MeetingTab', __('settings.Meeting Setting')],
    ['SocialMediaLinks', __('settings.SocialMediaLinks')],
    ['SocialMediaLogin', __('settings.SocialMediaLogin')],
    ['HomePage', __('admin.HomePageSetting')],
  ])->map(function ($tab) {
      return ['target' => $tab[0], 'label' => $tab[1]];
  })->toArray();
@endphp

@section('content')
  <!-- Content wrapper -->
  <div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
      <h4 class="mb-4">{{ $title }}</h5>
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
      <!-- / Alert -->
      <div class="col">
        <div class="nav-align-left nav-tabs-shadow mb-6 p-4 bg-white">
          <!-- Tabs -->
          <ul class="nav nav-tabs" role="tablist">
            @foreach ($tabs as $key => $tab)
              <li class="nav-item">
                <a 
                  href="#navs-left-{{ $tab['target'] }}" 
                  class="nav-link {{ $key == 0 ? 'active' : '' }} py-3" 
                  data-bs-toggle="tab"
                  role="tab"
                >
                  {{ $tab['label'] }}
                </a>
              </li>
            @endforeach
          </ul>
          <!-- / Tabs -->  
          <!-- Content On Tab Click -->
          <div class="tab-content">
            @foreach ($tabs as $index => $tab)
              <div 
                class="tab-pane fade {{ $index === 0 ? 'show active' : '' }} h-100" 
                id="navs-left-{{ $tab['target'] }}"
              >
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
                @endswitch
              </div>
            @endforeach
          </div>
          <!-- / Content On Tab Click -->
        </div>
      </div>
    </div>
    <!-- / Content -->
  </div>
  <!-- / Content wrapper -->
@stop

@section ('scripts')
  <!-- Include Dropzone -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/min/dropzone.min.js"></script>
@stop