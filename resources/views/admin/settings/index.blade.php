@extends('layouts.admin')

@section('content')
@php
$icons = [
  'general' => 'ti-adjustments',
  'roles_permissions' => 'ti-shield-lock',
  'organization_types' => 'ti ti-building' ,
  'importer_tool' => 'ti-package-import',
  'currencies' => 'ti-coins',
  'tags' => 'ti-tag',
  'categories' => 'ti-layout-grid',
  'subcategories' => 'ti-layout-board-split',
  'stages' => 'ti-packages',
  'countries' => 'ti-flag',
  'labels' => 'ti-bookmark',
  'queue_jobs' => 'ti-brand-google-big-query',
  'industry' => 'ti-building-factory',
  'expertise' => 'ti-brain',
  'notification_templates' => 'ti-mail',
  'notification_triggers' => 'ti-mail-check',
  'license_key' => 'ti-key'
];
@endphp

<div class="">
  <div class="">
    <div class="mb-5">
      <h4 class="mb-1">{{ __('settings.title') }}</h4>
      <p class="mb-0">{{ __('settings.description') }}</p>
    </div>

    <!-- Settings Grid Cards -->
    <div class="row g-4">
      @foreach($pages as $key => $page)
        @if ($_user->ableTo($page['route'], $permissions))
          <div class="col-md-6 col-lg-3">
            <a href="{{ route($page['route']) }}" class="card h-100 border-0 shadow-sm">
              <div class="card-body pb-3 px-5 pt-5">
                <div class="avatar mb-3">
                  <div class="avatar-initial rounded bg-label-primary">
                    @if (isset($icons[$page['title']]))
                      <i class="ti {{ $icons[$page['title']] }}"></i>
                    @endif
                  </div>
                </div>
                <h6 class="fw-semibold mb-1">
                  {{ isset($page['title']) ? __('settings.' . $page['title']) : '' }}
                </h6>
                <p class="text-secondary small mb-0">
                  {{ $page['title'] ? __('settings.' . 'settings_' . $page['title'] . '_label') : '' }}
                </p>
              </div>
              <div class="card-footer pb-3 ps-3">
                <button class="btn btn-sm btn-text-primary">
                  {{ __('admin.Manage') }} {{ isset($page['title']) ? __('settings.' . $page['title']) : '' }} {{ $key == 0 && $l == 'en' ? __('admin.settings') : '' }}
                  <i class="ti ti-chevron-{{ $l == 'en' ? 'right' : 'left' }} ms-2"></i>
                </button>
              </div>
            </a>
          </div>
        @endif
      @endforeach
    </div>
    <!--/ Settings Grid Cards -->
  </div>
</div>
@endsection
