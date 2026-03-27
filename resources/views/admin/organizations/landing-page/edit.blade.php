@extends('layouts.admin')

@section('title', __('admin.Edit') . ' ' . __('admin.Landing Page'))
@section('subTitle', $organization->{'name_' . $l})

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/toastr/toastr.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
@stop

@php
  $org = $organization;
  $isEdit = true;
  $maxStep = 5;
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
    'ti-file-text' => 'File Text',
    'ti-clipboard-check' => 'Clipboard Check',
    'ti-users' => 'Users',
    'ti-rocket' => 'Rocket',
    'ti-chart-bar' => 'Chart Bar',
    'ti-trophy' => 'Trophy',
    'ti-bulb' => 'Bulb',
    'ti-target' => 'Target',
    'ti-check' => 'Check',
    'ti-star' => 'Star',
    'ti-heart' => 'Heart',
    'ti-mail' => 'Mail',
    'ti-send' => 'Send',
    'ti-calendar' => 'Calendar',
    'ti-clock' => 'Clock',
    'ti-settings' => 'Settings',
    'ti-search' => 'Search',
    'ti-home' => 'Home',
    'ti-briefcase' => 'Briefcase',
    'ti-world' => 'World',
    'ti-message' => 'Message',
    'ti-phone' => 'Phone',
    'ti-lock' => 'Lock',
    'ti-shield-check' => 'Shield Check',
    'ti-gift' => 'Gift',
    'ti-thumb-up' => 'Thumb Up',
    'ti-lightbulb' => 'Lightbulb',
    'ti-bolt' => 'Bolt',
    'ti-flag' => 'Flag',
    'ti-award' => 'Award',
  ];

  $metricIcons = [
    'ti-users' => 'Users',
    'ti-building' => 'Building',
    'ti-currency-dollar' => 'Currency Dollar',
    'ti-rocket' => 'Rocket',
    'ti-chart-line' => 'Chart Line',
    'ti-briefcase' => 'Briefcase',
    'ti-world' => 'World',
    'ti-star' => 'Star',
    'ti-trophy' => 'Trophy',
    'ti-chart-bar' => 'Chart Bar',
    'ti-chart-pie' => 'Chart Pie',
    'ti-trending-up' => 'Trending Up',
    'ti-target' => 'Target',
    'ti-award' => 'Award',
    'ti-flag' => 'Flag',
    'ti-calendar' => 'Calendar',
    'ti-clock' => 'Clock',
    'ti-heart' => 'Heart',
    'ti-thumb-up' => 'Thumb Up',
    'ti-check' => 'Check',
    'ti-bolt' => 'Bolt',
    'ti-gift' => 'Gift',
    'ti-home' => 'Home',
    'ti-map-pin' => 'Map Pin',
    'ti-globe' => 'Globe',
    'ti-sparkles' => 'Sparkles',
    'ti-flame' => 'Flame',
    'ti-diamond' => 'Diamond',
    'ti-crown' => 'Crown',
    'ti-medal' => 'Medal',
  ];

  $benefitIcons = [
    'ti-cash' => 'Funding',
    'ti-building' => 'Workspace',
    'ti-users' => 'Mentors',
    'ti-bulb' => 'Idea',
    'ti-rocket' => 'Rocket',
    'ti-world' => 'Network',
    'ti-certificate' => 'Certificate',
    'ti-presentation' => 'Pitch',
    'ti-flask' => 'R&D',
    'ti-handshake' => 'Partners',
    'ti-key' => 'IP',
    'ti-trending-up' => 'Scale',
    'ti-heart' => 'Community',
    'ti-checkup-list' => 'MVP',
  ];

  $currentTab = request()->get('tab') ?? 'howItWorks';
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
                  >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect_to" value="landing_page" />
                    <input type="hidden" name="section_type" value="{{ $section['id'] }}" />
                    <input type="hidden" name="active_tab" value="{{ $section['id'] }}" />

                    <div class="p-4">
                      {{-- Section Header with Save/Reset --}}
                      <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">{{ $section['label'] }}</h5>
                        <div class="d-flex gap-2">
                          <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-2"></i>{{ __('admin.Save') }}
                          </button>
                        </div>
                      </div>

                      {{-- Include the actual section content --}}
                      @include('admin.organizations.wizard.partials._landing_section' . ($key + 1), [
                        'section' => $section,
                      ])
                    </div>
                  </form>
                </div>
              @endforeach

              {{-- Custom Sections Tab --}}
              <div
                class="tab-pane fade {{ $currentTab == 'customSections' ? 'show active' : '' }}"
                id="lp-customSections"
              >
                <form
                  action="{{ route('organization.wizard.update.step5', $org->id) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  id="customSectionsForm"
                >
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="redirect_to" value="landing_page" />
                  <input type="hidden" name="active_tab" value="customSections" />

                  <div class="p-4">
                    {{-- Section Header with Save/Reset --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                      <h5 class="mb-0">{{ __('admin.Custom Sections') }}</h5>
                      <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                          <i class="ti ti-device-floppy me-1"></i>{{ __('admin.Save') }}
                        </button>
                      </div>
                    </div>

                    @include('admin.organizations.wizard.partials._step5_custom_sections', [
                      'organization' => $org,
                      'customSections' => $customSections,
                    ])
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/toastr/toastr.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endsection

@section('pagescripts')
<script>
/**
 * Organizational Landing Page Management
 */
$(function () {
  'use strict';

  /* ─────────────────────────────────────────────
     CONSTANTS
  ───────────────────────────────────────────── */

  const MAX_LIMITS = {
    step: 4,
    metric: 8,
    article: 8,
    partner: 20,
    custom: 10,
  };

  const CONTAINER_MAP = {
    step: 'processStepsContainer',
    metric: 'metricsContainer',
    article: 'articlesContainer',
    partner: 'partnersContainer',
    custom: 'customSectionsContainer',
  };

  const SECTION_FORM_PREFIX_MAP = {
    howItWorks: 'how_it_works',
    achievements: 'achievements',
    media: 'media',
    partners: 'partners',
    programs: 'programs',
  };

  const FILE_VALIDATION = {
    allowedTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'],
    maxBytes: 5 * 1024 * 1024,
    acceptedExtensions: 'png,jpg,jpeg,webp,svg',
    maxSizeLabel: '5MB',
    partnerMaxBytes: 2 * 1024 * 1024,
    partnerExtensions: 'png,jpg,jpeg,svg',
    partnerSizeLabel: '2MB',
  };

  const REQUIRED_MSG = "{{ __('admin.Required_Field') }}";

  const BENEFIT_ICON_OPTIONS = `
    @foreach($benefitIcons as $iconVal => $iconLbl)
      <option value="{{ $iconVal }}">{{ $iconLbl }}</option>
    @endforeach
  `;

  const STEP_ICON_OPTIONS = `
    @foreach($stepIcons as $iv => $il)
      <option value="{{ $iv }}">{{ $il }}</option>
    @endforeach
  `;

  const METRIC_ICON_OPTIONS = `
    @foreach($metricIcons as $iv => $il)
      <option value="{{ $iv }}">{{ $il }}</option>
    @endforeach
  `;

  /* ─────────────────────────────────────────────
     VALIDATOR REGISTRY
  ───────────────────────────────────────────── */

  const validators = {};

  /* ═══════════════════════════════════════════════════════════
     1. VALIDATION ENGINE
  ─────────────────────────────────────────────────────────── */

  function getOrCreateFeedback(input) {
    let node = input.nextElementSibling;
    while (node) {
      if (node.classList.contains('invalid-feedback')) return node;
      node = node.nextElementSibling;
    }

    const fb = document.createElement('div');
    fb.className = 'invalid-feedback d-block';
    input.parentNode.insertBefore(fb, input.nextSibling);
    return fb;
  }

  function setFileError(input, message) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    getOrCreateFeedback(input).textContent = message;
  }

  function clearFileError(input) {
    input.classList.remove('is-invalid');
    getOrCreateFeedback(input).textContent = '';
  }

  function validateFileInput(input) {
    if (!input.files || !input.files[0]) {
      clearFileError(input);
      return true;
    }

    const { type, size } = input.files[0];
    const badType = !FILE_VALIDATION.allowedTypes.includes(type);
    const badSize = size > FILE_VALIDATION.maxBytes;

    if (badType || badSize) {
      setFileError(
        input,
        badType
          ? 'Only PNG, JPG, SVG files are allowed'
          : `File must be smaller than ${FILE_VALIDATION.maxSizeLabel}`
      );
      return false;
    }

    clearFileError(input);
    return true;
  }

  function showFilePreview(input) {
    if (!input.files || !input.files[0]) return;

    const col = input.closest('.col-12');
    const $preview = col
      ? $(col).find('.article-image-preview, .logo-preview')
      : $(input).siblings('.article-image-preview, .logo-preview');

    if (!$preview.length) return;

    const reader = new FileReader();
    reader.onload = (e) => {
      $preview.html(
        `<img src="${e.target.result}" class="img-thumbnail mt-2" style="max-height:60px;">`
      );
    };
    reader.readAsDataURL(input.files[0]);
  }

  function clearFilePreview(input) {
    const col = input.closest('.col-12');
    const $preview = col
      ? $(col).find('.article-image-preview, .logo-preview')
      : $(input).siblings('.article-image-preview, .logo-preview');
    $preview.empty();
  }

  function initSectionValidator(formId, fieldConfig) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener(
      'submit',
      function (e) {
        let hasFileError = false;
        const files = form.querySelectorAll('input[type="file"]');
        
        files.forEach((input) => {
          if (!validateFileInput(input)) hasFileError = true;
        });

        if (hasFileError) {
          e.preventDefault();
          e.stopImmediatePropagation();
          toastr.error('Please fix file upload errors before saving.');
        }
      },
      true
    );

    const fv = FormValidation.formValidation(form, {
      fields: fieldConfig,
      plugins: {
        trigger: new FormValidation.plugins.Trigger({ event: { file: 'change' } }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: '',
          rowSelector: '.col-md-12, .col-md-6, .col-md-4, .col-lg-4, .col-12, .mb-3, .mb-2, .card-body',
          messageBesideField: false
        }),
        autoFocus: new FormValidation.plugins.AutoFocus(),
      },
    })
    .on('core.form.invalid', () => toastr.error('Please fix the errors before saving.'))
    .on('core.form.valid', function() {
      form.submit();
    })
    .on('core.validator.validated', (data) => {
      const input = data.element;
      if (data.result.valid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        getOrCreateFeedback(input).textContent = '';
        if (data.validator === 'file') showFilePreview(input);
        return;
      }

      input.classList.add('is-invalid');
      input.classList.remove('is-valid');
      getOrCreateFeedback(input).textContent = data.result.message || 'Invalid field';
      if (data.validator === 'file') {
        input.value = '';
        clearFilePreview(input);
      }
    });

    validators[formId] = fv;
  }

  /* ═══════════════════════════════════════════════════════════
     2. REPEATER FIELD REGISTRATION
  ─────────────────────────────────────────────────────────── */

  function getRepeaterFieldNames(type, index) {
    const map = {
      step: [
        `process_steps[${index}][title]`,
        `process_steps[${index}][description]`,
        `process_steps[${index}][icon]`,
      ],
      metric: [
        `metrics[${index}][number]`,
        `metrics[${index}][label]`,
        `metrics[${index}][icon]`,
      ],
      article: [
        `articles[${index}][publication_name]`,
        `articles[${index}][date]`,
        `articles[${index}][title]`,
        `articles[${index}][link]`,
        `articles[${index}][summary]`,
        `articles[${index}][image]`,
      ],
      partner: [`partners_list[${index}][name]`, `partners_list[${index}][logo]`],
      custom: [
        `custom_sections[${index}][title]`,
        `custom_sections[${index}][content]`,
        `custom_sections[${index}][visibility]`,
      ],
    };
    return map[type] || [];
  }

  function buildLogoFieldConfig() {
    return {
      validators: {
        notEmpty: {
          message: 'Partner logo is required'
        },
        file: {
          extension: FILE_VALIDATION.partnerExtensions,
          type: ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'].join(','),
          maxSize: FILE_VALIDATION.partnerMaxBytes,
          message: `PNG, JPG, SVG only (${FILE_VALIDATION.partnerSizeLabel} max)`,
        },
      },
    };
  }

  function buildImageFieldConfig() {
    return {
      validators: {
        file: {
          extension: FILE_VALIDATION.acceptedExtensions,
          type: FILE_VALIDATION.allowedTypes.join(','),
          maxSize: FILE_VALIDATION.maxBytes,
          message: `PNG, JPG, WEBP only (${FILE_VALIDATION.maxSizeLabel} max)`,
        },
      },
    };
  }

  function buildRequiredFieldConfig() {
    return { validators: { notEmpty: { message: REQUIRED_MSG } } };
  }

  function addRepeaterValidation(formId, type, index) {
    const fv = validators[formId];
    if (!fv) return;

    getRepeaterFieldNames(type, index).forEach((name) => {
      if (name.includes('[logo]')) {
        fv.addField(name, buildLogoFieldConfig());
      } else if (name.includes('[image]')) {
        fv.addField(name, buildImageFieldConfig());
      } else {
        fv.addField(name, buildRequiredFieldConfig());
      }
    });
  }

  function removeRepeaterValidation(formId, type, index) {
    const fv = validators[formId];
    if (!fv) return;

    getRepeaterFieldNames(type, index).forEach((name) => {
      try {
        fv.removeField(name);
      } catch (_) {}
    });
  }

  /* ═══════════════════════════════════════════════════════════
     3. REPEATER — ADD
  ─────────────────────────────────────────────────────────── */

  $(document).on('click', '.add-item-btn', function () {
    const type = $(this).data('type');
    const formId = $(this).closest('form').attr('id');
    const $container = getContainerByType(type);
    const index = $container.find('.repeater-item').length;

    if (index >= (MAX_LIMITS[type] || 10)) {
      toastr.warning('Maximum limit reached');
      return;
    }

    $container.find('.empty-state').hide();

    const $item = $(getTemplateHtml(type, index));
    $container.append($item);

    initDatePicker();
    addRepeaterValidation(formId, type, index);

    if (type === 'partner') initPartnerPreview($item);
    if (type === 'custom') initQuillOnElement($item.find('.quill-editor'));
  });

  /* ═══════════════════════════════════════════════════════════
     4. REPEATER — REMOVE
  ─────────────────────────────────────────────────────────── */

  $(document).on('click', '.remove-item-btn', function () {
    const $item = $(this).closest('.repeater-item');
    const $container = $item.parent();
    const formId = $(this).closest('form').attr('id');
    const type = getTypeByContainer($container);
    const index = parseInt($item.data('index'), 10);

    removeRepeaterValidation(formId, type, index);

    $item.fadeOut(200, function () {
      $(this).remove();
      reindexContainer($container, type, formId);
      if ($container.find('.repeater-item').length === 0) {
        $container.find('.empty-state').show();
      }
    });
  });

  function reindexContainer($container, type, formId) {
    const fv = validators[formId];
    if (!fv) return;

    const prefixMap = {
      article: 'articles',
      partner: 'partners_list',
      step: 'process_steps',
      metric: 'metrics',
    };
    const prefix = prefixMap[type];

    $container.find('.repeater-item').each(function (newIdx) {
      const $item = $(this);

      $item.find('input, select, textarea').each(function () {
        const oldName = $(this).attr('name');
        if (oldName) {
          try {
            fv.removeField(oldName);
          } catch (_) {}
        }
      });

      $item.attr('data-index', newIdx);
      $item.find('.step-number').text(newIdx + 1);

      if (prefix) {
        $item.find('input, select, textarea').each(function () {
          const name = $(this).attr('name');
          if (name) {
            $(this).attr('name', name.replace(/\[\d+\]/, `[${newIdx}]`));
          }
        });
      }

      addRepeaterValidation(formId, type, newIdx);
    });
  }

  /* ═══════════════════════════════════════════════════════════
     5. REPEATER TEMPLATES
  ─────────────────────────────────────────────────────────── */

  function getTemplateHtml(type, i) {
    const templates = {
      step: buildStepTemplate,
      metric: buildMetricTemplate,
      article: buildArticleTemplate,
      partner: buildPartnerTemplate,
    };

    return templates[type] ? templates[type](i) : '';
  }

  function buildStepTemplate(i) {
    return `
      <div class="repeater-item card shadow-none mb-3 border" data-index="${i}">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
          <h6 class="mb-0">Step <span class="step-number">${i + 1}</span></h6>
          <button type="button" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill remove-item-btn">
            <i class="ti ti-trash"></i>
          </button>
        </div>
        <div class="card-body py-3">
          <div class="row">
            <div class="col-12 col-md-6 mb-2">
              <input type="text" class="form-control"
                     name="process_steps[${i}][title]"
                     placeholder="{{ __('admin.Step Title') }}"
                     maxlength="255">
            </div>
            <div class="col-12 col-md-6 mb-2">
              <select class="form-select" name="process_steps[${i}][icon]">
                <option value="">{{ __('admin.Select icon') }}</option>
                ${STEP_ICON_OPTIONS}
              </select>
            </div>
            <div class="col-12">
              <textarea class="form-control"
                        name="process_steps[${i}][description]"
                        rows="2"
                        placeholder="{{ __('admin.Step Description') }}"
                        maxlength="1000"></textarea>
            </div>
          </div>
        </div>
      </div>`;
  }

  function buildMetricTemplate(i) {
    return `
      <div class="repeater-item card shadow-none mb-3 border" data-index="${i}">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
          <h6 class="mb-0">Metric ${i + 1}</h6>
          <button type="button" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill remove-item-btn">
            <i class="ti ti-trash"></i>
          </button>
        </div>
        <div class="card-body py-3">
          <div class="row">
            <div class="col-12 mb-3">
              <input type="text" class="form-control"
                     name="metrics[${i}][number]"
                     placeholder="{{ __('admin.Metric Number') }}"
                     maxlength="20">
            </div>
            <div class="col-12 mb-3">
              <input type="text" class="form-control"
                     name="metrics[${i}][label]"
                     placeholder="{{ __('admin.Metric Label') }}"
                     maxlength="255">
            </div>
            <div class="col-12">
              <select class="form-select" name="metrics[${i}][icon]">
                <option value="">{{ __('admin.Select icon') }}</option>
                ${METRIC_ICON_OPTIONS}
              </select>
            </div>
          </div>
        </div>
      </div>`;
  }

  function buildArticleTemplate(i) {
    return `
      <div class="repeater-item card shadow-none mb-3 border" data-index="${i}">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
          <h6 class="mb-0">Article ${i + 1}</h6>
          <button type="button" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill remove-item-btn">
            <i class="ti ti-trash"></i>
          </button>
        </div>
        <div class="card-body py-3">
          <div class="row">
            <div class="col-12 col-md-6 mb-3">
              <label class="form-label">{{ __('admin.Publication Name') }}</label>
              <input type="text" class="form-control"
                     name="articles[${i}][publication_name]"
                     placeholder="{{ __('admin.Publication Name') }}"
                     maxlength="255">
            </div>
            <div class="col-12 col-md-6 mb-3">
              <label class="form-label">{{ __('admin.Publication Date') }}</label>
              <input type="text" class="form-control datepicker"
                     name="articles[${i}][date]"
                     placeholder="{{ __('admin.Publication Date') }}">
            </div>
            <div class="col-12 mb-3">
              <label class="form-label">{{ __('admin.Article Title') }}</label>
              <input type="text" class="form-control"
                     name="articles[${i}][title]"
                     placeholder="{{ __('admin.Article Title') }}"
                     maxlength="255">
            </div>
            <div class="col-12 mb-3">
              <label class="form-label">{{ __('admin.Article Link') }}</label>
              <input type="url" class="form-control"
                     name="articles[${i}][link]"
                     placeholder="{{ __('admin.Article Link') }}"
                     maxlength="500">
            </div>
            <div class="col-12 mb-3">
              <label class="form-label">{{ __('admin.Article Summary') }}</label>
              <textarea class="form-control"
                        name="articles[${i}][summary]"
                        rows="2"
                        placeholder="{{ __('admin.Article Summary') }}"
                        maxlength="1000"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">{{ __('admin.Article Image') }}</label>
              <input type="file" class="form-control article-image-input"
                     name="articles[${i}][image]"
                     accept="image/png,image/jpeg,image/jpg">
              <div class="article-image-preview rounded overflow-hidden mt-2"></div>
              <small class="text-muted d-block mt-1">
                PNG, JPG, SVG (Max: ${FILE_VALIDATION.maxSizeLabel})
              </small>
            </div>
          </div>
        </div>
      </div>`;
  }

  function buildPartnerTemplate(i) {
    return `
    <div class="repeater-item card shadow-none mb-3 border" data-index="${i}">
      <div class="card-header d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0">Partner ${i + 1}</h6>
        <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item-btn">
          <i class="ti ti-trash"></i>
        </button>
      </div>
      <div class="card-body py-3">
        <div class="row">
          <div class="col-12 mb-3">
            <label class="form-label">{{ __('admin.Partner Name') }}</label>
            <input type="text"
                  class="form-control name-input"
                  name="partners_list[${i}][name]"
                  placeholder="{{ __('admin.Partner Name') }}"
                  maxlength="255">
          </div>
          <div class="col-12 col-md-3 mb-3">
            <label class="form-label">{{ __('admin.Logo') }} {{ __('admin.Preview') }}</label>
            <div class="logo-preview h-px-75 d-flex align-items-center justify-content-center bg-light-subtle rounded text-center py-3">
              <i class="ti ti-building text-muted fs-3"></i>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('admin.Partner Logo') }}</label>
            <input type="file"
                  class="form-control logo-input"
                  name="partners_list[${i}][logo]"
                  accept="image/png,image/jpeg,image/jpg,image/svg+xml">
            <small class="text-muted d-block mt-1">
              PNG, JPG, SVG (Max: ${FILE_VALIDATION.partnerSizeLabel})
            </small>
          </div>
        </div>
      </div>
    </div>`;
  }

  /* ═══════════════════════════════════════════════════════════
     6. PARTNER — logo + name preview
  ─────────────────────────────────────────────────────────── */

  function initPartnerPreview($card) {
    $card.find('.name-input').on('input', function () {
      $card.find('.partner-name-preview').text($(this).val() || 'New Partner');
    });

    $card.find('.logo-input').on('change', function (e) {
      const file = e.target.files[0];
      if (!file || file.size > FILE_VALIDATION.maxBytes) {
        toastr.warning(`Logo too large. Max ${FILE_VALIDATION.maxSizeLabel}`);
        return;
      }

      const reader = new FileReader();
      reader.onload = (ev) => {
        $card.find('.logo-preview').html(
          `<img src="${ev.target.result}" class="img-fluid rounded" style="max-height:60px;">`
        );
      };
      reader.readAsDataURL(file);
    });
  }

  /* ═══════════════════════════════════════════════════════════
     7. PROGRAMS — sortable + visibility toggle
  ─────────────────────────────────────────────────────────── */

  $(document).on('change', '.program-visible-toggle', function () {
    $(this)
      .closest('.program-item')
      .toggleClass('border-primary', this.checked)
      .toggleClass('border-secondary opacity-75', !this.checked);
  });

  function initProgramsSortable() {
    const orgId = {{ $org->id }};
    const container = document.getElementById('programsLandingContainer');
    if (!container) return;

    new Sortable(container, {
      handle: '.drag-handle',
      animation: 150,
      onEnd() {
        const orderedIds = Array.from(container.children)
          .map((item) => parseInt(item.dataset.programId))
          .filter(Boolean);

        $.ajax({
          url: `/dashboard/organization/${orgId}/programs/reorder`,
          type: 'POST',
          data: {
            ordered_ids: orderedIds,
            _token: $('meta[name="csrf-token"]').attr('content'),
          },
          success: () => toastr.success('Order saved'),
          error: () => toastr.error('Reorder failed'),
        });
      },
    });
  }

  /* ═══════════════════════════════════════════════════════════
     8. CUSTOM SECTIONS
  ─────────────────────────────────────────────────────────── */

  window.QuillEditors = window.QuillEditors || {};

  /**
   * Add validation for a single custom section index.
   * Safe to call multiple times — removes existing fields first to avoid duplicates.
   */
  function addCustomSectionValidation(formId, index) {
    const fv = validators[formId];
    if (!fv) return;

    const fields = [
      `custom_sections[${index}][title]`,
      `custom_sections[${index}][content]`,
      `custom_sections[${index}][visibility]`,
    ];

    fields.forEach((name) => {
      // Always remove first so we never register the same field twice
      try { fv.removeField(name); } catch (_) {}
      try {
        fv.addField(name, { validators: { notEmpty: { message: REQUIRED_MSG } } });
      } catch (e) {
        console.warn(`Could not add validation for ${name}:`, e);
      }
    });
  }

  /**
   * Remove validation for a single custom section index.
   */
  function removeCustomSectionValidation(formId, index) {
    const fv = validators[formId];
    if (!fv) return;

    [
      `custom_sections[${index}][title]`,
      `custom_sections[${index}][content]`,
      `custom_sections[${index}][visibility]`,
    ].forEach((name) => {
      try { fv.removeField(name); } catch (_) {}
    });
  }

  function syncHiddenInputs($card) {
    const title = $card.find('.section-title-input').val() || '';
    const visibility = $card.find('input[name*="[visibility]"]:checked').val() || 'public';

    let content = '';
    const $quillEl = $card.find('.quill-editor');
    const quillId = $quillEl.attr('id');
    
    if ($quillEl.length && quillId && window.QuillEditors && window.QuillEditors[quillId]) {
      content = window.QuillEditors[quillId].root.innerHTML;
      content = content
        .replace(/<p><br\s*\/?><\/p>/gi, '')
        .replace(/&nbsp;/g, ' ')
        .replace(/<span[^>]*><\/span>/g, '')
        .trim();
    } else {
      const $hidden = $card.find('.hidden-content');
      content = $hidden.length ? $hidden.val() || '' : '';
    }

    const sortOrder =
      $card.data('index') !== undefined
        ? $card.data('index')
        : $card.find('.hidden-sort-order').val() || $card.index();

    $card.find('.hidden-title').val(title);
    $card.find('.hidden-content').val(content);
    $card.find('.hidden-visibility').val(visibility);
    $card.find('.hidden-sort-order').val(sortOrder);

    if ($card.hasClass('editing')) {
      const formId = 'customSectionsForm';
      const fv = validators[formId];
      if (fv) {
        const index = $card.data('index');
        fv.revalidateField(`custom_sections[${index}][title]`);
        fv.revalidateField(`custom_sections[${index}][content]`);
        fv.revalidateField(`custom_sections[${index}][visibility]`);
      }
    }
  }

  function loadSectionFromHidden($card) {
    const index = $card.data('index');
    const title = $card.find('.hidden-title').val() || '';
    const content = $card.find('.hidden-content').val() || '';
    const visibility = $card.find('.hidden-visibility').val() || 'public';

    $card.find('.section-title-input').val(title);
    $card.find('.section-title-display').text(title || '{{ __("admin.New Section") }}');

    $card.find(`input[name*="[visibility]"][value="${visibility}"]`).prop('checked', true);
    updateVisibilityBadge($card, visibility);

    const $quillEl = $card.find('.quill-editor');
    if ($quillEl.length && $quillEl.attr('id') && window.QuillEditors[$quillEl.attr('id')]) {
      if (content) {
        window.QuillEditors[$quillEl.attr('id')].root.innerHTML = content;
      }
    } else if ($card.find('.section-content-input').length) {
      $card.find('.section-content-input').val(content);
    }

    const formId = 'customSectionsForm';
    const fv = validators[formId];
    if (fv) {
      setTimeout(() => {
        fv.revalidateField(`custom_sections[${index}][title]`);
        fv.revalidateField(`custom_sections[${index}][content]`);
        fv.revalidateField(`custom_sections[${index}][visibility]`);
      }, 100);
    }
  }

  function updateVisibilityBadge($card, visibility) {
    const iconMap = { public: 'world', participants: 'users', admins_only: 'lock', private: 'lock' };
    const classMap = {
      public: 'bg-label-success',
      participants: 'bg-label-info',
      admins_only: 'bg-label-warning',
      private: 'bg-label-warning',
    };
    const labelMap = {
      public: '{{ __("admin.public") }}',
      participants: '{{ __("admin.participants") }}',
      admins_only: '{{ __("admin.admins_only") }}',
      private: '{{ __("admin.private") }}',
    };

    const icon = iconMap[visibility] || 'lock';
    const badgeClass = classMap[visibility] || 'bg-label-secondary';
    const label = labelMap[visibility] || visibility;

    $card
      .find('.section-visibility-badge, .badge:first')
      .attr('class', `badge section-visibility-badge ${badgeClass}`)
      .html(`<i class="ti ti-${icon} me-1"></i>${label}`);
  }

  function toggleSectionEdit($card, showEdit = true) {
    const $formContainer = $card.find('.section-form-container');
    const $hiddenInputs = $card.find('.section-hidden-inputs');
    const $editBtn = $card.find('.edit-section-btn');

    if (showEdit) {
      loadSectionFromHidden($card);
      $formContainer.slideDown(200);
      $hiddenInputs.hide();
      $card.addClass('editing');
      $editBtn.html('<i class="ti ti-check"></i>').removeClass('btn-outline-info').addClass('btn-info');
    } else {
      syncHiddenInputs($card);

      const title = $card.find('.section-title-input').val() || '{{ __("admin.New Section") }}';
      $card.find('.section-title-display').text(title);

      const visibility = $card.find('input[name*="[visibility]"]:checked').val() || 'public';
      updateVisibilityBadge($card, visibility);

      $formContainer.slideUp(200);
      $hiddenInputs.show();
      $card.removeClass('editing');
      $editBtn.html('<i class="ti ti-pencil"></i>').removeClass('btn-info').addClass('btn-outline-info');
    }
  }

  function addCustomSection() {
    const $container = $('#customSectionsContainer');
    const count = $container.find('.custom-section-card').length;

    if (count >= MAX_LIMITS.custom) {
      toastr.warning(`Maximum ${MAX_LIMITS.custom} sections reached`);
      return;
    }

    const tpl = document.getElementById('sectionTemplate');
    if (!tpl) return;

    $('#sectionsEmptyState').hide();

    // Use current count as the new index
    const newIndex = count;
    const $el = $(tpl.innerHTML.replace(/__INDEX__/g, String(newIndex)));
    $el.attr('data-index', newIndex);
    $el.addClass('custom-section-card');

    // Ensure all input names use the correct index
    $el.find('input, select, textarea').each(function () {
      const name = $(this).attr('name');
      if (name && name.includes('custom_sections[')) {
        $(this).attr('name', name.replace(/custom_sections\[\d+\]/, `custom_sections[${newIndex}]`));
      }
    });

    $el.find('.hidden-title').attr('name', `custom_sections[${newIndex}][title]`);
    $el.find('.hidden-content').attr('name', `custom_sections[${newIndex}][content]`);
    $el.find('.hidden-visibility').attr('name', `custom_sections[${newIndex}][visibility]`);
    $el.find('.hidden-sort-order').attr('name', `custom_sections[${newIndex}][sort_order]`);

    $container.append($el);

    const $quillEditor = $el.find('.quill-editor');
    if ($quillEditor.length && typeof Quill !== 'undefined') {
      initQuillOnElement($quillEditor);
    }

    $el.find('input[name*="[visibility]"][value="public"]').prop('checked', true);
    updateVisibilityBadge($el, 'public');

    // Register validation only for this new section — no reindex needed
    addCustomSectionValidation('customSectionsForm', newIndex);

    updateCustomSectionCount();

    setTimeout(() => {
      toggleSectionEdit($el, true);
      $el.find('.section-title-input').focus();
    }, 100);
  }

  function deleteCustomSection($card) {
    if (!confirm("{{ __('admin.Are you sure you want to delete this section?') }}")) return;

    const sectionId = $card.data('section-id');
    const index = $card.data('index');

    removeCustomSectionValidation('customSectionsForm', index);

    if (sectionId) {
      $.ajax({
        url: '{{ $isEdit ? route("organization.wizard.step5.delete", [$org->id, "__ID__"]) : "" }}'.replace('__ID__', sectionId),
        type: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: function(response) {
          if (response.success) {
            $card.slideUp(300, function() {
              $(this).remove();
              reindexCustomSections();
              updateCustomSectionCount();
              if ($('#customSectionsContainer .custom-section-card').length === 0) {
                $('#sectionsEmptyState').fadeIn(200);
              }
            });
            toastr.success(response.message);
          } else {
            toastr.error(response.message);
          }
        },
        error: function() {
          toastr.error('{{ __("admin.Error deleting section") }}');
        }
      });
    } else {
      removeSectionFromDOM($card);
    }
  }

  function removeSectionFromDOM($card) {
    $card.fadeOut(300, function () {
      $(this).remove();
      reindexCustomSections();
      updateCustomSectionCount();
      if ($('#customSectionsContainer .custom-section-card').length === 0) {
        $('#sectionsEmptyState').fadeIn(200);
      }
    });
  }

  /**
   * Reindex all custom sections after a deletion or sort.
   * This is the SINGLE authoritative place that registers validation
   * for existing (already-in-DOM) sections. It removes old field
   * registrations first, renames inputs, then re-adds validation —
   * guaranteeing each field is registered exactly once.
   */
  function reindexCustomSections() {
    const $container = $('#customSectionsContainer');
    const $sections = $container.find('.custom-section-card');

    // Phase 1: strip all existing validation for every current index
    $sections.each(function () {
      const oldIdx = $(this).data('index');
      if (oldIdx !== undefined && oldIdx !== null) {
        removeCustomSectionValidation('customSectionsForm', oldIdx);
      }
    });

    // Phase 2: rename inputs and re-register validation with fresh indices
    $sections.each(function (newIdx) {
      const $card = $(this);
      $card.attr('data-index', newIdx);
      $card.find('.hidden-sort-order').val(newIdx);

      $card.find('input, select, textarea').each(function () {
        const name = $(this).attr('name');
        if (name && name.includes('custom_sections[')) {
          $(this).attr('name', name.replace(/custom_sections\[\d+\]/, `custom_sections[${newIdx}]`));
        }
      });

      $card.find('.hidden-title').attr('name', `custom_sections[${newIdx}][title]`);
      $card.find('.hidden-content').attr('name', `custom_sections[${newIdx}][content]`);
      $card.find('.hidden-visibility').attr('name', `custom_sections[${newIdx}][visibility]`);
      $card.find('.hidden-sort-order').attr('name', `custom_sections[${newIdx}][sort_order]`);

      // addCustomSectionValidation already does remove-then-add, so it's safe here
      addCustomSectionValidation('customSectionsForm', newIdx);
      syncHiddenInputs($card);
    });

    updateCustomSectionCount();
  }

  function updateCustomSectionCount() {
    const count = $('#customSectionsContainer .custom-section-card').length;
    $('#currentCount').text(count);
    $('#addSectionBtn').prop('disabled', count >= MAX_LIMITS.custom);
  }

  function initQuillOnElement($el) {
    if (!$el.length || typeof Quill === 'undefined') return;

    const existingId = $el.attr('id');
    if (existingId && window.QuillEditors[existingId]) return;

    const id = existingId || `quill-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    if (!existingId) $el.attr('id', id);

    const quill = new Quill($el[0], {
      theme: 'snow',
      modules: {
        toolbar: [
          ['bold', 'italic', 'underline'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          ['link', 'clean'],
        ],
        clipboard: { matchVisual: false },
      },
      placeholder: $el.data('placeholder') || 'Enter section content…',
      direction: '{{ $l == "ar" ? "rtl" : "ltr" }}',
    });

    window.QuillEditors[id] = quill;

    const $card = $el.closest('.custom-section-card');
    quill.on('text-change', () => {
      if ($card.length) {
        syncHiddenInputs($card);
        if ($card.hasClass('editing')) {
          const fv = validators['customSectionsForm'];
          if (fv) {
            fv.revalidateField(`custom_sections[${$card.data('index')}][content]`);
          }
        }
      }
    });

    const $hiddenInput = $el.siblings('.hidden-content');
    const existingContent = $hiddenInput.length ? $hiddenInput.val() : '';
    if (existingContent.trim()) {
      quill.root.innerHTML = existingContent;
    }
  }

  // Custom Sections Event Handlers
  $(document).on('click', '#addSectionBtn, #addFirstSectionBtn', addCustomSection);

  $(document).on('click', '.edit-section-btn', function () {
    const $card = $(this).closest('.custom-section-card');
    toggleSectionEdit($card, !$card.hasClass('editing'));
  });

  $(document).on('click', '.delete-section-btn', function () {
    deleteCustomSection($(this).closest('.custom-section-card'));
  });

  $(document).on('input', '.section-title-input', function () {
    const $card = $(this).closest('.custom-section-card');
    $card.find('.section-title-display').text($(this).val() || '{{ __("admin.New Section") }}');
    if ($card.hasClass('editing')) {
      syncHiddenInputs($card);
      const fv = validators['customSectionsForm'];
      if (fv) fv.revalidateField(`custom_sections[${$card.data('index')}][title]`);
    }
  });

  $(document).on('change', 'input[name*="[visibility]"]', function () {
    const $card = $(this).closest('.custom-section-card');
    updateVisibilityBadge($card, $(this).val());
    if ($card.hasClass('editing')) {
      syncHiddenInputs($card);
      const fv = validators['customSectionsForm'];
      if (fv) fv.revalidateField(`custom_sections[${$card.data('index')}][visibility]`);
    }
  });

  $(document).on('click', '.preview-section-btn', function () {
    const $card = $(this).closest('.custom-section-card');
    if ($card.hasClass('editing')) syncHiddenInputs($card);

    const title = $card.find('.hidden-title').val() || 'Untitled';
    const content = $card.find('.hidden-content').val() || '';

    $('#sectionPreviewContent').html(`
      <div class="p-4">
        <h4 class="mb-3">${escapeHtml(title)}</h4>
        <div class="section-preview-content">${content}</div>
      </div>
    `);

    new bootstrap.Modal(document.getElementById('sectionPreviewModal')).show();
  });

  $('#customSectionsForm').on('submit', function () {
    $('#customSectionsContainer .custom-section-card').each(function () {
      syncHiddenInputs($(this));
    });
  });

  /* ═══════════════════════════════════════════════════════════
     9. LANDING PAGE ENABLE TOGGLE
  ─────────────────────────────────────────────────────────── */

  $('#landingEnabledToggle').on('change', function () {
    const isEnabled = this.checked;
    $('#landingStatusText').html(
      isEnabled
        ? `<span class="text-success"><i class="ti ti-check me-1"></i>{{ __('admin.Landing page is published') }}</span>`
        : `<span class="text-warning"><i class="ti ti-eye-off me-1"></i>{{ __('admin.Landing page is unpublished') }}</span>`
    );
  });

  /* ═══════════════════════════════════════════════════════════
     10. SLUG — generate + availability check
  ─────────────────────────────────────────────────────────── */

  $('#generateSlugBtn').on('click', function () {
    const orgName = '{{ addslashes($org->{"name_" . $l} ?? "") }}';
    $('#slug').val(
      orgName
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_]+/g, '-')
        .replace(/^-+|-+$/g, '')
    );
  });

  $('#checkSlugBtn').on('click', function () {
    const slug = $('#slug').val().trim();
    if (!slug) { toastr.warning('Please enter a slug first'); return; }

    const $feedback = $('#slugAvailabilityFeedback').show();
    $feedback.html('<span class="text-muted"><i class="ti ti-loader-2 ti-spin me-1"></i>Checking…</span>');

    $.get('{{ route("organization.wizard.check.slug", $org->id) }}', { slug })
      .done((res) => {
        $feedback.html(
          res.available
            ? '<span class="text-success"><i class="ti ti-circle-check me-1"></i>Available</span>'
            : '<span class="text-danger"><i class="ti ti-circle-x me-1"></i>Already taken</span>'
        );
      })
      .fail(() => {
        $feedback.html('<span class="text-danger"><i class="ti ti-alert-circle me-1"></i>Check failed</span>');
      });
  });

  /* ═══════════════════════════════════════════════════════════
     11. HELPERS
  ─────────────────────────────────────────────────────────── */

  function getContainerByType(type) {
    return $('#' + (CONTAINER_MAP[type] || ''));
  }

  function getTypeByContainer($container) {
    const id = $container.attr('id');
    return Object.keys(CONTAINER_MAP).find((k) => CONTAINER_MAP[k] === id) || null;
  }

  function initDatePicker() {
    $('.datepicker:not(.flatpickr-input)').flatpickr({ dateFormat: 'Y-m-d', allowInput: true });
  }

  function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  /* ═══════════════════════════════════════════════════════════
     12. DOCUMENT READY — BOOTSTRAP
  ─────────────────────────────────────────────────────────── */

  $(document).on('click', '.reload-tab', function () {
    const url = new URL(window.location.href);
    url.searchParams.set('tab', $(this).data('tab-id'));
    window.location.href = url.toString();
  });

  // URL form validator
  initSectionValidator('landingPageForm', {
    slug: {
      validators: {
        regexp: {
          regexp: /^[a-z0-9]+(?:-[a-z0-9]+)*$/,
          message: 'Invalid slug',
        },
      },
    },
  });

  // Section form validators
  Object.keys(SECTION_FORM_PREFIX_MAP).forEach((key) => {
    const prefix = SECTION_FORM_PREFIX_MAP[key];
    initSectionValidator(`form-${key}`, {
      [`${prefix}_title`]:       { validators: { notEmpty: { message: REQUIRED_MSG } } },
      [`${prefix}_subtitle`]:    { validators: { notEmpty: { message: REQUIRED_MSG } } },
      [`${prefix}_description`]: { validators: { notEmpty: { message: REQUIRED_MSG } } },
    });
  });

  // Custom sections form — fields are added dynamically; start with empty config
  initSectionValidator('customSectionsForm', {});

  // Non-custom repeater items: register validation once per item
  $('.repeater-item').each(function () {
    const $item = $(this);
    const formId = $item.closest('form').attr('id');
    const $container = $item.parent();
    const type = getTypeByContainer($container);
    const idx = parseInt($item.data('index'), 10);

    if (formId && type && type !== 'custom') {
      addRepeaterValidation(formId, type, idx);
    }
    if (type === 'partner') initPartnerPreview($item);
  });

  // Quill initialisation
  setTimeout(() => {
    $('.quill-editor').each(function () { initQuillOnElement($(this)); });
  }, 300);

  // Custom sections sortable
  const customSectionsEl = document.getElementById('customSectionsContainer');
  if (customSectionsEl && typeof Sortable !== 'undefined') {
    new Sortable(customSectionsEl, {
      handle: '.drag-handle',
      animation: 200,
      onEnd: function () {
        reindexCustomSections();
        const orderedIds = Array.from(customSectionsEl.children)
          .map((card) => parseInt(card.dataset.sectionId))
          .filter(Boolean);

        $.ajax({
          url: '{{ route("organization.wizard.step5.reorder", $org->id) }}',
          type: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          data: { ordered_ids: orderedIds },
          success: (r) => { if (r.success) toastr.success(r.message); },
          error: () => toastr.error('{{ __("admin.Error reordering sections") }}'),
        });
      },
    });
  }

  initProgramsSortable();
  initDatePicker();
  updateCustomSectionCount();

  /**
   * Bootstrap existing custom sections on page load.
   * reindexCustomSections() is the ONLY call that registers validation here —
   * no separate addCustomSectionValidation loop, so there is no double registration.
   */
  if ($('#customSectionsContainer .custom-section-card').length) {
    // Set initial visibility badges and sync hidden inputs first
    $('#customSectionsContainer .custom-section-card').each(function () {
      const $card = $(this);
      updateVisibilityBadge($card, $card.find('.hidden-visibility').val() || 'public');
      syncHiddenInputs($card);
    });
    // Single call registers validation for all existing sections
    reindexCustomSections();
  }
});
</script>
@endsection