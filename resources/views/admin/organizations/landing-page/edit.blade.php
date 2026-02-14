@extends('layouts.admin')

@section('title', __('admin.Edit') . ' ' . __('admin.Landing Page'))
@section('subTitle', $organization->{'name_' . $l})

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/toastr/toastr.css" />
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
@endphp

@section('content')
<div class="">
  <div class="">
    <div class="row">
      <div class="col-12">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="mb-1">
              <i class="ti ti-layout-dashboard me-2"></i>{{ __('admin.Manage') }} {{ __('admin.Landing Page') }}
            </h4>
            <p class="mb-0 text-muted">{{ $org->{'name_' . $l} }}</p>
          </div>
          <div class="d-flex gap-2">
            @if($org->slug)
              <a href="{{ route('organization.wizard.preview', $org->id) }}" target="_blank" class="btn btn-outline-primary">
                <i class="ti ti-external-link me-1"></i>{{ __('admin.Preview Landing Page') }}
              </a>
            @endif
            <a href="{{ route('organizations.index') }}" class="btn btn-label-secondary">
              <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i>{{ __('admin.Back') }}
            </a>
          </div>
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

        <!-- Landing Page Form (URL + 4 landing sections) -->
        <form
          action="{{ route('organization.wizard.update.step4', $org->id) }}"
          method="POST"
          enctype="multipart/form-data"
          id="landingPageForm"
        >
          @csrf
          @method('PUT')
          <input type="hidden" name="redirect_to" value="landing_page">

          <!-- Organization URL Section -->
          <div class="card mb-4">
            <div class="card-header py-3">
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
                    >
                  </div>
                  <div>
                    <h5 class="card-title mb-0">
                      <i class="ti ti-link me-2"></i>{{ __('admin.Organization URL') }}
                    </h5>
                    <small class="text-muted" id="landingStatusText">
                      @if(old('landing_enabled', $landingPage->landing_enabled ?? true))
                        <span class="text-success"><i class="ti ti-check me-1"></i>{{ __('admin.Landing page is published') }}</span>
                      @else
                        <span class="text-warning"><i class="ti ti-eye-off me-1"></i>{{ __('admin.Landing page is unpublished') }}</span>
                      @endif
                    </small>
                  </div>
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
                  <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-merge bg-card">
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
                    <button type="button" class="btn btn-outline-secondary text-nowrap flex-shrink-0" id="generateSlugBtn">
                      <i class="ti ti-refresh me-2"></i>{{ __('admin.Generate from name') }}
                    </button>
                    <button type="button" class="btn btn-outline-info text-nowrap flex-shrink-0" id="checkSlugBtn">
                      <i class="ti ti-check me-2"></i>{{ __('admin.Check Availability') }}
                    </button>
                  </div>
                  <div class="invalid-feedback">{{ $errors->first('slug') }}</div>
                  <div id="slugAvailabilityFeedback" class="mt-1" style="display: none;"></div>
                  <small class="text-muted">{{ __('admin.URL must contain only lowercase letters, numbers, and hyphens') }}</small>
                </div>
              </div>
            </div>
          </div>
        </form>

        <!-- Sections Card with Side Nav -->
        <div class="card mb-4">
          <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="card-title mb-0 d-flex align-items-center gap-2">
                <i class="ti ti-layout-dashboard"></i>{{ __('admin.Landing Page Sections') }}
              </h6>
              <button type="button" class="btn btn-primary" id="saveCurrentSection">
                <i class="ti ti-device-floppy me-2"></i>{{ __('admin.Save') }}
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="nav-align-left">
              {{-- Side Navigation --}}
              <ul class="nav nav-tabs py-4" role="tablist" id="landingPageTabs">
                @foreach ($landingSections as $key => $section)
                  <li class="nav-item py-1">
                    <button
                      type="button"
                      class="nav-link {{ $loop->first ? 'active' : '' }} lp-section-tab"
                      data-section-type="landing"
                      data-bs-toggle="tab"
                      data-bs-target="#lp-{{ $section['id'] }}"
                      role="tab">
                      <i class="ti ti-{{ $section['icon'] }} me-2"></i>
                      {{ $section['label'] }}
                    </button>
                  </li>
                @endforeach
                <li class="nav-item py-1">
                  <button
                    type="button"
                    class="nav-link lp-section-tab"
                    data-section-type="custom"
                    data-bs-toggle="tab"
                    data-bs-target="#lp-customSections"
                    role="tab">
                    <i class="ti ti-layout-grid me-2"></i>
                    {{ __('admin.Custom Sections') }}
                  </button>
                </li>
              </ul>

              {{-- Tab Content --}}
              <div class="tab-content shadow-none" style="width: 100%;">
                {{-- Landing Page Section Tabs (1-4) - inside landing form --}}
                @foreach ($landingSections as $key => $section)
                  <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="lp-{{ $section['id'] }}">
                    <div class="p-4">
                      @include('admin.organizations.wizard.partials._landing_section' . ($key + 1), ['section' => $section])
                    </div>
                  </div>
                @endforeach

                {{-- Custom Sections Tab --}}
                <div class="tab-pane fade" id="lp-customSections">
                  <div class="p-4">
                    @include('admin.organizations.wizard.partials._step5_custom_sections', [
                      'organization' => $org,
                      'customSections' => $customSections,
                    ])
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Hidden form for custom sections submission --}}
        <form
          action="{{ route('organization.wizard.update.step5', $org->id) }}"
          method="POST"
          id="customSectionsForm"
          enctype="multipart/form-data"
          style="display:none;"
        >
          @csrf
          @method('PUT')
          <input type="hidden" name="redirect_to" value="landing_page">
        </form>

        <!-- Bottom Save Button -->
        <div class="d-flex justify-content-end mb-4">
          <button type="button" class="btn btn-primary" id="saveAllBottom">
            <i class="ti ti-device-floppy me-2"></i>{{ __('admin.Save') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/toastr/toastr.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/tinymce.min.js" referrerpolicy="origin"></script>
@stop

@push('scripts')
<script>
(function() {
  'use strict';

  let currentSectionType = 'landing';
  let activeTabId = 'howItWorks';

  // On page load, restore active tab from URL
  const urlParams = new URLSearchParams(window.location.search);
  const tabFromUrl = urlParams.get('tab');
  if (tabFromUrl) {
    activeTabId = tabFromUrl;
    const tabButton = $(`[data-bs-target="#lp-${tabFromUrl}"]`);
    if (tabButton.length) {
      new bootstrap.Tab(tabButton[0]).show();
      currentSectionType = tabButton.data('section-type') || 'landing';
    }
  }

  // Track which section type and tab is active
  $(document).on('shown.bs.tab', '.lp-section-tab', function() {
    currentSectionType = $(this).data('section-type');
    // Extract tab ID from data-bs-target (e.g. "#lp-howItWorks" -> "howItWorks")
    const target = $(this).data('bs-target') || '';
    activeTabId = target.replace('#lp-', '');
    // Update save button text
    if (currentSectionType === 'custom') {
      $('#saveCurrentSection').html('<i class="ti ti-device-floppy me-2"></i>{{ __("admin.Save Custom Sections") }}');
    } else {
      $('#saveCurrentSection').html('<i class="ti ti-device-floppy me-2"></i>{{ __("admin.Save") }}');
    }
  });

  // Save button logic - determines which form to submit based on active tab
  function saveLandingPage() {
    // Collect all landing section fields and submit via the landing page form
    const $form = $('#landingPageForm');

    // Also collect fields from landing section tab panes (they're outside the form)
    const formData = new FormData($form[0]);

    // Gather inputs from the landing section tab panes
    $('#lp-howItWorks, #lp-achievements, #lp-media, #lp-partners, #lp-programs').find('input, select, textarea').each(function() {
      const $el = $(this);
      const name = $el.attr('name');
      if (!name) return;

      if ($el.attr('type') === 'file') {
        if (this.files && this.files.length > 0) {
          formData.append(name, this.files[0]);
        }
      } else if ($el.attr('type') === 'checkbox') {
        if ($el.is(':checked')) {
          formData.append(name, $el.val());
        }
      } else if ($el.attr('type') === 'radio') {
        if ($el.is(':checked')) {
          formData.append(name, $el.val());
        }
      } else {
        formData.append(name, $el.val() || '');
      }
    });

    // Submit via AJAX to avoid losing page state, or just create a hidden form and submit
    const $submitForm = $('<form>', {
      action: $('#landingPageForm').attr('action'),
      method: 'POST',
      enctype: 'multipart/form-data'
    }).css('display', 'none');

    // Add CSRF and method
    $submitForm.append($('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }));
    $submitForm.append($('<input>', { type: 'hidden', name: '_method', value: 'PUT' }));
    $submitForm.append($('<input>', { type: 'hidden', name: 'redirect_to', value: 'landing_page' }));
    $submitForm.append($('<input>', { type: 'hidden', name: 'active_tab', value: activeTabId }));

    // Copy all fields from URL section form
    $('#landingPageForm').find('input, select, textarea').each(function() {
      const $el = $(this);
      const name = $el.attr('name');
      if (!name || name === '_token' || name === '_method' || name === 'redirect_to') return;

      if ($el.attr('type') === 'file') {
        if (this.files && this.files.length > 0) {
          const clone = $el.clone()[0];
          clone.files = this.files;
          $submitForm.append(clone);
        }
      } else if ($el.attr('type') === 'checkbox') {
        if ($el.is(':checked')) {
          $submitForm.append($('<input>', { type: 'hidden', name: name, value: $el.val() }));
        }
      } else {
        $submitForm.append($('<input>', { type: 'hidden', name: name, value: $el.val() || '' }));
      }
    });

    // Copy all fields from landing section tab panes
    $('#lp-howItWorks, #lp-achievements, #lp-media, #lp-partners, #lp-programs').find('input, select, textarea').each(function() {
      const $el = $(this);
      const name = $el.attr('name');
      if (!name) return;

      if ($el.attr('type') === 'file') {
        if (this.files && this.files.length > 0) {
          const clone = $el.clone()[0];
          clone.files = this.files;
          $submitForm.append(clone);
        }
      } else if ($el.attr('type') === 'checkbox') {
        if ($el.is(':checked')) {
          $submitForm.append($('<input>', { type: 'hidden', name: name, value: $el.val() }));
        }
      } else if ($el.attr('type') === 'radio') {
        if ($el.is(':checked')) {
          $submitForm.append($('<input>', { type: 'hidden', name: name, value: $el.val() }));
        }
      } else {
        $submitForm.append($('<input>', { type: 'hidden', name: name, value: $el.val() || '' }));
      }
    });

    $('body').append($submitForm);
    $submitForm.submit();
  }

  function saveCustomSections() {
    const $form = $('#customSectionsForm');

    // Remove old dynamic data
    $form.find('[name^="custom_sections"]').remove();
    $form.find('[name="active_tab"]').remove();
    $form.append($('<input>', { type: 'hidden', name: 'active_tab', value: activeTabId }));

    // Sync TinyMCE content
    $('.custom-section-card.editing').each(function() {
      const card = $(this);
      card.find('.rich-text-editor').each(function() {
        if (typeof tinymce !== 'undefined') {
          const editor = tinymce.get(this.id);
          if (editor) editor.save();
        }
      });
      // Sync to hidden inputs
      const form = card.find('.section-form-container');
      const hidden = card.find('.section-hidden-inputs');
      hidden.find('.hidden-title').val(form.find('.section-title-input').val());
      hidden.find('.hidden-content').val(form.find('.section-content-input').val());
      hidden.find('.hidden-visibility').val(form.find('.visibility-radio:checked').val());
    });

    // Collect custom sections data
    $('.custom-section-card').each(function(index) {
      const card = $(this);
      const prefix = 'custom_sections[' + index + ']';
      const sectionId = card.data('section-id') || '';

      $form.append($('<input>', { type: 'hidden', name: prefix + '[id]', value: sectionId }));
      $form.append($('<input>', { type: 'hidden', name: prefix + '[title]', value: card.find('.hidden-title').val() || card.find('.section-title-input').val() || '' }));
      $form.append($('<input>', { type: 'hidden', name: prefix + '[content]', value: card.find('.hidden-content').val() || card.find('.section-content-input').val() || '' }));
      $form.append($('<input>', { type: 'hidden', name: prefix + '[visibility]', value: card.find('.hidden-visibility').val() || card.find('.visibility-radio:checked').val() || 'public' }));
      $form.append($('<input>', { type: 'hidden', name: prefix + '[sort_order]', value: index }));
    });

    $form.submit();
  }

  // Save buttons
  $('#saveCurrentSection').on('click', function() {
    if (currentSectionType === 'custom') {
      saveCustomSections();
    } else {
      saveLandingPage();
    }
  });

  $('#saveAllBottom').on('click', function() {
    if (currentSectionType === 'custom') {
      saveCustomSections();
    } else {
      saveLandingPage();
    }
  });

  // Landing page toggle
  $(document).on('change', '#landingEnabledToggle', function() {
    const statusText = $('#landingStatusText');
    if ($(this).is(':checked')) {
      statusText.html('<span class="text-success"><i class="ti ti-check me-1"></i>{{ __("admin.Landing page is published") }}</span>');
    } else {
      statusText.html('<span class="text-warning"><i class="ti ti-eye-off me-1"></i>{{ __("admin.Landing page is unpublished") }}</span>');
    }
  });

  // Check slug availability
  $(document).on('click', '#checkSlugBtn', function() {
    const btn = $(this);
    const slug = $('#slug').val().trim();
    const feedbackDiv = $('#slugAvailabilityFeedback');

    if (!slug) {
      feedbackDiv.html('<small class="text-danger"><i class="ti ti-alert-circle me-1"></i>{{ __("admin.Please enter a URL slug") }}</small>').show();
      return;
    }

    if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) {
      feedbackDiv.html('<small class="text-danger"><i class="ti ti-alert-circle me-1"></i>{{ __("admin.URL must contain only lowercase letters, numbers, and hyphens") }}</small>').show();
      return;
    }

    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>{{ __("admin.Checking") }}...');
    feedbackDiv.hide();

    $.ajax({
      url: "{{ route('organization.wizard.check.slug', $org->id) }}",
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      data: { slug: slug },
      success: function(response) {
        feedbackDiv.html(response.available
          ? '<small class="text-success"><i class="ti ti-check me-1"></i>' + response.message + '</small>'
          : '<small class="text-danger"><i class="ti ti-alert-circle me-1"></i>' + response.message + '</small>'
        ).show();
      },
      error: function() {
        feedbackDiv.html('<small class="text-danger"><i class="ti ti-alert-circle me-1"></i>{{ __("admin.Error checking availability") }}</small>').show();
      },
      complete: function() {
        btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i>{{ __("admin.Check Availability") }}');
      }
    });
  });

  // Generate slug
  $(document).on('click', '#generateSlugBtn', function() {
    const btn = $(this);
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>{{ __("admin.Processing") }}...');

    $.ajax({
      url: "{{ route('organization.wizard.generate.slug', $org->id) }}",
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      success: function(response) {
        if (response.status) {
          $('#slug').val(response.slug);
          toastr.success("{{ __('admin.Slug generated successfully') }}");
        } else {
          toastr.error(response.message || "{{ __('admin.Error generating slug') }}");
        }
      },
      error: function() { toastr.error("{{ __('admin.Error generating slug') }}"); },
      complete: function() {
        btn.prop('disabled', false).html('<i class="ti ti-refresh me-1"></i>{{ __("admin.Generate from name") }}');
      }
    });
  });

  // Repeater: Add item
  $(document).on('click', '.add-item-btn', function() {
    const type = $(this).data('type');
    const max = $(this).data('max');
    const container = getContainerByType(type);
    const currentCount = container.find('.repeater-item').length;

    if (currentCount >= max) {
      toastr.warning(getMaxMessage(type));
      return;
    }

    container.find('.empty-state').hide();
    container.append(getTemplate(type, currentCount));
    updateStepNumbers(container);
  });

  // Repeater: Remove item
  $(document).on('click', '.remove-item-btn', function() {
    const item = $(this).closest('.repeater-item');
    const container = item.parent();

    item.fadeOut(200, function() {
      $(this).remove();
      updateStepNumbers(container);
      if (container.find('.repeater-item').length === 0) {
        container.find('.empty-state').show();
      }
      reindexItems(container);
    });
  });

  function getContainerByType(type) {
    switch(type) {
      case 'step': return $('#processStepsContainer');
      case 'metric': return $('#metricsContainer');
      case 'article': return $('#articlesContainer');
      case 'partner': return $('#partnersContainer');
    }
  }

  function getMaxMessage(type) {
    switch(type) {
      case 'step': return "{{ __('admin.Maximum 4 steps allowed') }}";
      case 'metric': return "{{ __('admin.Maximum 8 metrics allowed') }}";
      case 'article': return "{{ __('admin.Maximum 8 articles allowed') }}";
      case 'partner': return "{{ __('admin.Maximum 20 partners allowed') }}";
    }
  }

  function getTemplate(type, index) {
    switch(type) {
      case 'step':
        return `<div class="repeater-item card shadow-none mb-3 border" data-index="${index}"><div class="card-body py-3"><div class="row align-items-center"><div class="col-md-1 text-center"><span class="badge bg-primary rounded-circle step-number" style="width: 32px; height: 32px; line-height: 24px;">${index + 1}</span></div><div class="col-md-3"><input type="text" class="form-control form-control-sm" name="process_steps[${index}][title]" placeholder="{{ __('admin.Step Title') }}" maxlength="255"></div><div class="col-md-4"><input type="text" class="form-control form-control-sm" name="process_steps[${index}][description]" placeholder="{{ __('admin.Step Description') }}" maxlength="1000"></div><div class="col-md-3"><select class="form-select form-select-sm icon-select" name="process_steps[${index}][icon]"><option value="">{{ __('admin.Select icon') }}</option><option value="ti-file-text">File Text</option><option value="ti-clipboard-check">Clipboard Check</option><option value="ti-users">Users</option><option value="ti-rocket">Rocket</option><option value="ti-chart-bar">Chart Bar</option><option value="ti-trophy">Trophy</option><option value="ti-bulb">Bulb</option><option value="ti-target">Target</option><option value="ti-check">Check</option><option value="ti-star">Star</option></select></div><div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item-btn"><i class="ti ti-trash"></i></button></div></div></div></div>`;
      case 'metric':
        return `<div class="repeater-item card shadow-none mb-3 border" data-index="${index}"><div class="card-body py-3"><div class="row align-items-center"><div class="col-md-3"><input type="text" class="form-control form-control-sm" name="metrics[${index}][number]" placeholder="{{ __('admin.Metric Number') }}" maxlength="20"></div><div class="col-md-5"><input type="text" class="form-control form-control-sm" name="metrics[${index}][label]" placeholder="{{ __('admin.Metric Label') }}" maxlength="255"></div><div class="col-md-3"><select class="form-select form-select-sm icon-select" name="metrics[${index}][icon]"><option value="">{{ __('admin.Select icon') }}</option><option value="ti-users">Users</option><option value="ti-building">Building</option><option value="ti-currency-dollar">Currency Dollar</option><option value="ti-rocket">Rocket</option><option value="ti-chart-line">Chart Line</option><option value="ti-star">Star</option><option value="ti-trophy">Trophy</option><option value="ti-chart-bar">Chart Bar</option><option value="ti-target">Target</option><option value="ti-award">Award</option></select></div><div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item-btn"><i class="ti ti-trash"></i></button></div></div></div></div>`;
      case 'article':
        return `<div class="repeater-item card mb-3 border" data-index="${index}"><div class="card-body py-3"><div class="row"><div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="articles[${index}][publication_name]" placeholder="{{ __('admin.Publication Name') }}" maxlength="255"></div><div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" name="articles[${index}][date]"></div><div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="articles[${index}][title]" placeholder="{{ __('admin.Article Title') }}" maxlength="255"></div><div class="col-md-3 mb-2"><input type="url" class="form-control form-control-sm" name="articles[${index}][link]" placeholder="{{ __('admin.Article Link') }}" maxlength="500"></div><div class="col-md-1 text-end mb-2"><button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item-btn"><i class="ti ti-trash"></i></button></div><div class="col-md-8 mb-2"><textarea class="form-control form-control-sm" name="articles[${index}][summary]" rows="2" placeholder="{{ __('admin.Article Summary') }}" maxlength="1000"></textarea></div><div class="col-md-4 mb-2"><label class="form-label small">{{ __('admin.Article Image') }}</label><input type="file" class="form-control form-control-sm" name="articles[${index}][image]" accept="image/png,image/jpeg,image/jpg"><small class="text-muted">PNG, JPG ({{ __('admin.Max size') }}: 5MB)</small></div></div></div></div>`;
      case 'partner':
        return `<div class="repeater-item col-md-4 col-lg-3 mb-3" data-index="${index}"><div class="card border h-100"><div class="card-body py-3 text-center"><button type="button" class="btn btn-sm btn-icon btn-label-danger position-absolute top-0 end-0 m-2 remove-item-btn"><i class="ti ti-x"></i></button><div class="mb-2 bg-light rounded d-flex align-items-center justify-content-center" style="height: 60px;"><i class="ti ti-building text-muted fs-3"></i></div><input type="text" class="form-control form-control-sm mb-2" name="partners_list[${index}][name]" placeholder="{{ __('admin.Partner Name') }}" maxlength="255"><input type="file" class="form-control form-control-sm" name="partners_list[${index}][logo]" accept="image/png,image/jpeg,image/jpg,image/svg+xml"><small class="text-muted d-block mt-1">PNG, JPG, SVG (2MB)</small></div></div></div>`;
    }
  }

  function updateStepNumbers(container) {
    container.find('.step-number').each(function(index) { $(this).text(index + 1); });
  }

  function reindexItems(container) {
    container.find('.repeater-item').each(function(index) {
      $(this).attr('data-index', index);
      $(this).find('input, select, textarea').each(function() {
        const name = $(this).attr('name');
        if (name) $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
      });
    });
  }
})();
</script>
@endpush
