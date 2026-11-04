@extends('layouts.admin')

@section('title', __('admin.Edit') . ' ' . __('admin.Landing Page'))
@section('subTitle', $organization->{'name_' . $l})

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/form-validation/form-validation.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
@stop

@php
  $org = $organization;
  $isEdit = true;
  $maxStep = 5;
  $maxCustomSections = 10;
  $landingPage = $org->landingPage ?? null;

  // Data for landing section partials
  $processSteps = old('process_steps', $landingPage->process_steps ?? []);
  $metrics = old('metrics', $landingPage->metrics ?? []);
  $articles = old('articles', $landingPage->articles ?? []);
  $partnersList = old('partners_list', $landingPage->partners_list ?? []);
  $currentTheme = old('landing_theme', $landingPage->landing_theme ?? 'gradient');

  $landingSections = [
    ['id' => 'howItWorks', 'icon' => 'list', 'label' => __('admin.How It Works')],
    ['id' => 'achievements', 'icon' => 'trophy', 'label' => __('admin.Achievements & Impact')],
    ['id' => 'media', 'icon' => 'share', 'label' => __('admin.Media & Recognition')],
    ['id' => 'partners', 'icon' => 'users', 'label' => __('admin.Partners')],
    ['id' => 'programs', 'icon' => 'rocket', 'label' => __('admin.Programs')],
  ];

  $stepIcons = [
    'ti-file-text' => 'File Text', 'ti-clipboard-check' => 'Clipboard Check',
    'ti-users' => 'Users', 'ti-rocket' => 'Rocket', 'ti-chart-bar' => 'Chart Bar',
    'ti-trophy' => 'Trophy', 'ti-bulb' => 'Bulb', 'ti-target' => 'Target', 'ti-check' => 'Check',
    'ti-star' => 'Star', 'ti-heart' => 'Heart', 'ti-mail' => 'Mail', 'ti-send' => 'Send',
    'ti-calendar' => 'Calendar', 'ti-clock' => 'Clock', 'ti-settings' => 'Settings', 
    'ti-search' => 'Search', 'ti-home' => 'Home', 'ti-briefcase' => 'Briefcase',
    'ti-world' => 'World', 'ti-message' => 'Message', 'ti-phone' => 'Phone', 'ti-lock' => 'Lock',
    'ti-shield-check' => 'Shield Check', 'ti-gift' => 'Gift', 'ti-thumb-up' => 'Thumb Up',
    'ti-lightbulb' => 'Lightbulb', 'ti-bolt' => 'Bolt', 'ti-flag' => 'Flag', 'ti-award' => 'Award',
  ];

  $metricIcons = [ 
    'ti-users' => 'Users', 'ti-building' => 'Building', 'ti-currency-dollar' => 'Currency Dollar',
    'ti-rocket' => 'Rocket', 'ti-chart-line' => 'Chart Line', 'ti-briefcase' => 'Briefcase',
    'ti-world' => 'World', 'ti-star' => 'Star', 'ti-trophy' => 'Trophy', 'ti-chart-bar' => 'Chart Bar',
    'ti-chart-pie' => 'Chart Pie','ti-trending-up' => 'Trending Up','ti-target' => 'Target',
    'ti-award' => 'Award', 'ti-flag' => 'Flag', 'ti-calendar' => 'Calendar','ti-crown' => 'Crown',
    'ti-clock' => 'Clock', 'ti-heart' => 'Heart', 'ti-thumb-up' => 'Thumb Up', 'ti-check' => 'Check',
    'ti-bolt' => 'Bolt', 'ti-gift' => 'Gift', 'ti-home' => 'Home', 'ti-map-pin' => 'Map Pin', 'ti-medal' => 'Medal',
    'ti-globe' => 'Globe', 'ti-sparkles' => 'Sparkles', 'ti-flame' => 'Flame', 'ti-diamond' => 'Diamond',
  ];

  $benefitIcons = [
    'ti-cash' => 'Funding', 'ti-building' => 'Workspace', 'ti-users' => 'Mentors', 
    'ti-bulb' => 'Idea', 'ti-rocket' => 'Rocket', 'ti-world' => 'Network', 
    'ti-certificate' => 'Certificate', 'ti-presentation' => 'Pitch', 'ti-flask' => 'R&D',
    'ti-handshake' => 'Partners', 'ti-key' => 'IP', 'ti-trending-up' => 'Scale', 
    'ti-heart' => 'Community', 'ti-checkup-list' => 'MVP',
  ];

  $currentTab = request()->get('tab') ?? 'howItWorks';
@endphp

{{-- Generate HTML option strings for icon selects --}}
@php
  function arrayToOptions($icons) {
    $options = '';
    foreach ($icons as $value => $label) {
      $options .= "<option value=\"{$value}\">{$label}</option>\n";
    }
    return $options;
  }

  $stepIconOptionsHtml = arrayToOptions($stepIcons);
  $metricIconOptionsHtml = arrayToOptions($metricIcons);
  $benefitIconOptionsHtml = arrayToOptions($benefitIcons);
@endphp

@section('content')
  <div class="row">
    <div class="col-12">
      <!-- Page Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
          {{ __('admin.Manage') }} {{ __('admin.Landing Page') }} - {{ $org->{'name_' . $l} }}
        </h4>
        @if ($org->slug)
          <a
            href="{{ route('organization.wizard.preview', $org->id) }}"
            target="_blank"
            class="btn btn-outline-primary"
          >
            <i class="ti ti-external-link me-2"></i>{{ __('admin.Preview Landing Page') }}
          </a>
        @endif
      </div>

      <!-- Alerts -->
      @if (session()->has('success'))
        <x-alert type="success" message="{{ session()->get('success') }}" />
      @endif
      @if (session()->has('error'))
        <x-alert type="danger" message="{{ session()->get('error') }}" />
      @endif
      @if ($errors->any())
        @foreach ($errors->all() as $error)
          <x-alert type="danger" message="{{ $error }}" />
        @endforeach
      @endif

      <!-- Landing Page Form (URL Section) -->
      <form
        action="{{ route('organization.wizard.update.step4', $org->id) }}"
        method="POST"
        enctype="multipart/form-data"
        id="landingPageForm"
        novalidate
      >
        @csrf
        @method('PUT')
        <input type="hidden" name="redirect_to" value="landing_page" />
        <input type="hidden" name="section_type" value="url" />

        <!-- Organization URL Section -->
        <div class="card mb-4">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <div class="form-check form-switch me-3">
                  <input
                    type="checkbox"
                    class="form-check-input landing-toggle"
                    id="landingEnabledToggle"
                    name="landing_enabled"
                    value="1"
                    {{ old('landing_enabled', $landingPage->landing_enabled ?? true) ? 'checked' : '' }}
                  />
                </div>
                <div>
                  <h5 class="card-title mb-0">
                    <i class="ti ti-link me-2"></i>{{ __('admin.Organization URL') }}
                  </h5>
                  <small class="text-muted" id="landingStatusText">
                    @if (old('landing_enabled', $landingPage->landing_enabled ?? true))
                      <span class="text-success">
                        <i class="ti ti-check me-1"></i>{{ __('admin.Landing page is published') }}
                      </span>
                    @else
                      <span class="text-warning">
                        <i class="ti ti-eye-off me-1"></i>{{ __('admin.Landing page is unpublished') }}
                      </span>
                    @endif
                  </small>
                </div>
              </div>
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                  <i class="ti ti-device-floppy me-2"></i>{{ __('admin.Save') }}
                </button>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <label class="form-label" for="slug">
                  {{ __('admin.Custom URL Slug') }}
                  <small class="text-muted">({{ __('admin.Optional') }})</small>
                </label>

                <div class="row g-2">
                  <!-- Input -->
                  <div class="col-md">
                    <div class="input-group input-group-merge shadow-none">
                      <span class="input-group-text text-muted">{{ url('/o/') }}/</span>
                      <input
                        type="text"
                        class="form-control @error('slug') is-invalid @enderror"
                        id="slug"
                        name="slug"
                        value="{{ old('slug', $org->slug ?? '') }}"
                        placeholder="organization-name"
                        pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                        maxlength="100"
                      />
                    </div>
                  </div>

                  <!-- Generate Button -->
                  <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary text-nowrap w-100" id="generateSlugBtn">
                      <i class="ti ti-refresh me-2"></i>{{ __('admin.Generate from name') }}
                    </button>
                  </div>

                  <!-- Check Button -->
                  <div class="col-auto">
                    <button type="button" class="btn btn-outline-info text-nowrap w-100" id="checkSlugBtn">
                      <i class="ti ti-check me-2"></i>{{ __('admin.Check Availability') }}
                    </button>
                  </div>
                </div>

                <div class="invalid-feedback">{{ $errors->first('slug') }}</div>
                <div id="slugAvailabilityFeedback" class="mt-1" style="display: none"></div>
                <small class="text-muted">
                  {{ __('admin.URL must contain only lowercase letters, numbers, and hyphens') }}
                </small>
              </div>
            </div>
          </div>
        </div>
      </form>

      <!-- Sections Card with Side Nav -->
      <div class="card mb-4">
        <div class="card-header border-bottom">
          <h6 class="card-title mb-0 d-flex align-items-center gap-2">
            <i class="ti ti-layout-dashboard"></i>{{ __('admin.Landing Page Sections') }}
          </h6>
        </div>
        <div class="card-body p-0">
          <div class="nav-align-left">
            {{-- Side Navigation --}}
            <ul class="nav nav-tabs py-4" role="tablist" id="landingPageTabs">
              @foreach ($landingSections as $key => $section)
                <li class="nav-item py-1">
                  <button
                    type="button"
                    class="nav-link {{ $currentTab == $section['id'] ? 'active' : '' }} lp-section-tab reload-tab"
                    data-tab-id="{{ $section['id'] }}"
                    role="tab"
                  >
                    <i class="ti ti-{{ $section['icon'] }} me-2"></i>
                    {{ $section['label'] }}
                  </button>
                </li>
              @endforeach
              <li class="nav-item py-1">
                <button
                  type="button"
                  class="nav-link {{ $currentTab == 'customSections' ? 'active' : '' }} lp-section-tab reload-tab"
                  data-tab-id="customSections"
                  role="tab"
                >
                  <i class="ti ti-layout-grid me-2"></i>
                  {{ __('admin.Custom Sections') }}
                </button>
              </li>
            </ul>

            {{-- Tab Content --}}
            <div class="tab-content py-0 shadow-none" style="width: 100%">
              {{-- Landing Page Section Tabs (1-5) --}}
              @foreach ($landingSections as $key => $section)
                <div
                  class="tab-pane fade {{ $currentTab == $section['id'] ? 'show active' : '' }}"
                  id="lp-{{ $section['id'] }}"
                >
                  <form
                    action="{{ route('organization.wizard.update.step4', $org->id) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="landing-section-form"
                    id="form-{{ $section['id'] }}"
                    data-section-id="{{ $section['id'] }}"
                    novalidate
                  >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect_to" value="landing_page" />
                    <input type="hidden" name="section_type" value="{{ $section['id'] }}" />
                    <input type="hidden" name="active_tab" value="{{ $section['id'] }}" />

                    <div class="p-4">
                      <h5 class="mb-4">{{ $section['label'] }}</h5>

                      {{-- Include the actual section content --}}
                      @include('admin.organizations.wizard.partials._landing_section' . ($key + 1), [
                        'section' => $section,
                      ])

                      {{-- Save button, bottom of each section --}}
                      <div class="d-flex justify-content-end mt-4 pt-4 border-top">
                        <button type="submit" class="btn btn-primary">
                          <i class="ti ti-device-floppy me-2"></i>{{ __('admin.Save') }}
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              @endforeach

              {{-- Custom Sections Tab --}}
              <div
                class="tab-pane mt-6 fade {{ $currentTab == 'customSections' ? 'show active' : '' }}"
                id="lp-customSections"
              >
                @include('admin.organizations.landing-page.partials.custom-sections-form', [
                  'organization' => $org,
                  'customSections' => $customSections,
                ])
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

  <script>
    /**
     * Organizational Landing Page Management
     * Required global bootstrap before this file is loaded 
     * Required for app-lp-management.js
    */
    window.LPConfig = {
      orgId          : "{{ $org->id }}",
      orgName        : "{{ $org->name }}",
      checkSlugUrl   : "{{ route('organization.wizard.check.slug', $org->id) }}",
      csrfToken      : "{{ csrf_token() }}",
      requiredMsg    : "{{ __('admin.Required_Field') }}",
      stepIconOptions    : {!! json_encode($stepIconOptionsHtml) !!},
      metricIconOptions  : {!! json_encode($metricIconOptionsHtml) !!},
      benefitIconOptions : {!! json_encode($benefitIconOptionsHtml) !!},
      i18n: {
        step              : "{{ __('admin.Step') }}", 
        selectIcon        : "{{ __('settings.Select Icon') }}",
        stepTitle         : "{{ __('admin.Step Title') }}",
        stepDescription   : "{{ __('admin.Step Description') }}",
        metric            : "{{ __('admin.Metric') }}",
        metricNumber      : "{{ __('admin.Metric Number') }}",
        metricLabel       : "{{ __('admin.Metric Label') }}",
        publicationName   : "{{ __('admin.Publication Name') }}",
        publicationDate   : "{{ __('admin.Publication Date') }}",
        article           : "{{ __('admin.Article') }}",
        articleTitle      : "{{ __('admin.Article Title') }}",
        articleLink       : "{{ __('admin.Article Link') }}",
        articleSummary    : "{{ __('admin.Article Summary') }}",
        articleImage      : "{{ __('admin.Article Image') }}",
        partner           : "{{ __('admin.Partner') }}",
        partnerName       : "{{ __('admin.Partner Name') }}",
        partnerLogo       : "{{ __('admin.Partner Logo') }}",
        logoPreview       : "{{ __('admin.Logo') }} {{ __('admin.Preview') }}",
        landingPublished  : "{{ __('admin.Landing Published') }}",
        landingUnpublished: "{{ __('admin.Landing Unpublished') }}",
        maximumLimitReached  : "{{ __('admin.maximum_limit_reached') }}",
        metric_number_validation : "{{ __('admin.metric_number_validation') }}",
        items: "{{ __('admin.items') }}",
        allowed: "{{ __('admin.allowed') }}",
      },
    };

    /**
     * Configuration for Custom Sections management
     * injected into the global scope for access in app-custom-sections.js 
    */
    window.CS_CONFIG = {
      maxSections : "{{ $maxCustomSections }}",
      orgId       : "{{ $org->id }}",
      deleteUrl   : '{{ route("organization.wizard.step5.delete",  [$org->id, "__ID__"]) }}',
      reorderUrl  : '{{ route("organization.wizard.step5.reorder", $org->id) }}',
      csrfToken   : '{{ csrf_token() }}',
      lang: {
        required      : '{{ __("admin.Required_Field") }}',
        maxReached    : '{{ __("admin.maximum_sections_reached") }}',
        warning:       '{{ __("admin.Warning") }}',
        confirmDelete : '{{ __("admin.Are you sure you want to delete this section?") }}',
        deleteError   : '{{ __("admin.Error deleting section") }}',
        reorderError  : '{{ __("admin.Error reordering sections") }}',
        newSection    : '{{ __("admin.New Section") }}',
        untitled      : '{{ __("admin.Untitled Section") }}',
        saveError     : '{{ __("admin.save_failed") }}',
        quillDir      : '{{ $l == "ar" ? "rtl" : "ltr" }}',
        addSection     : '{{ __("admin.add_section") }}',
        DYes: '{{ __("admin.DYes") }}',
        DNo: '{{ __("admin.DNo") }}',
        saving: '{{ __("admin.saving") }}',
      }
    };
  </script>
@endsection

@section('pagescripts')
  <script src="/vuexy/assets/js/app-lp-management.js"></script>
  <script src="/vuexy/assets/js/app-custom-sections.js"></script>
@endsection