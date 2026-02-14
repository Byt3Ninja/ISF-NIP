@php
  $org = $organization ?? null;
  $landingPage = $org ? $org->landingPage : null;
  $locale = app()->getLocale();
  $isRtl = $locale === 'ar';

  // Get existing data
  $processSteps = old('process_steps', $landingPage->process_steps ?? []);
  $metrics = old('metrics', $landingPage->metrics ?? []);
  $articles = old('articles', $landingPage->articles ?? []);
  $partnersList = old('partners_list', $landingPage->partners_list ?? []);
  $currentTheme = old('landing_theme', $landingPage->landing_theme ?? 'gradient');

  $sections = [
   ['id' => 'howItWorks', 'icon' => 'list', 'label' => __('admin.How It Works')],
   ['id' => 'achievements', 'icon' => 'trophy', 'label' => __('admin.Achievements & Impact')],
   ['id' => 'media', 'icon' => 'share', 'label' => __('admin.Media & Recognition')],
   ['id' => 'partners', 'icon' => 'users', 'label' => __('admin.Partners')],
   ['id' => 'programs', 'icon' => 'rocket', 'label' => __('admin.Programs')],
  ];
@endphp

<div class="row">
  <div class="col-12">
    <!-- Organization URL Section -->
    <div class="card rounded-0 shadow-none bg-light-subtle border-top border-bottom">
      <div class="card-header border-bottom-0 py-3">
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
          @if($org && $org->slug)
            <a href="{{ route('organization.wizard.preview', $org->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
              <i class="ti ti-external-link me-1"></i>{{ __('admin.Preview Landing Page') }}
            </a>
          @endif
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
  </div>
  <div class="col-12">
    <div class="card rounded-0 shadow-none">
      <div class="card-header border-bottom">
        <h6 class="card-title mb-0 d-flex align-items-center gap-2">
          <i class="ti ti-layout-dashboard"></i>{{ __('admin.Landing Page Sections') }}
        </h6>
      </div>
    </div>
    <div class="card-body">
      <div class="nav-align-left">
        <ul class="nav nav-tabs py-4" role="tablist">
          @foreach ($sections as $key => $section)
            <li class="nav-item py-1">
              <button
                type="button"
                class="nav-link {{ $loop->first ? 'active' : '' }}"
                role="tab"
                data-bs-toggle="tab"
                data-bs-target="#{{ $section['id'] }}Content"
                aria-controls="{{ $section['id'] }}Content"
                aria-selected="false">
                <i class="ti ti-{{ $section['icon'] }} me-2"></i>
                {{ $section['label'] }}
              </button>
            </li>
          @endforeach
        </ul>
        <div class="tab-content shadow-none">
          @foreach ($sections as $key => $section)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $section['id'] }}Content">
              @include('admin.organizations.wizard.partials._landing_section' . ($key + 1), ['section' => $section])
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
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
  const slugInput = $('#slug');
  const slug = slugInput.val().trim();
  const feedbackDiv = $('#slugAvailabilityFeedback');
  const orgId = '{{ $organization->id ?? "" }}';

  if (!slug) {
    feedbackDiv.html('<small class="text-danger"><i class="ti ti-alert-circle me-1"></i>{{ __("admin.Please enter a URL slug") }}</small>');
    feedbackDiv.show();
    return;
  }

  // Validate format
  const slugRegex = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
  if (!slugRegex.test(slug)) {
    feedbackDiv.html('<small class="text-danger"><i class="ti ti-alert-circle me-1"></i>{{ __("admin.URL must contain only lowercase letters, numbers, and hyphens") }}</small>');
    feedbackDiv.show();
    return;
  }

  btn.prop('disabled', true);
  btn.html('<span class="spinner-border spinner-border-sm me-1"></span>{{ __("admin.Checking") }}...');
  feedbackDiv.hide();

  $.ajax({
    url: "{{ $isEdit ? route('organization.wizard.check.slug', $organization->id ?? 0) : route('organization.wizard.check.slug') }}",
    type: 'POST',
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: {
      slug: slug
    },
    success: function(response) {
      if (response.available) {
        feedbackDiv.html('<small class="text-success"><i class="ti ti-check me-1"></i>' + response.message + '</small>');
      } else {
        feedbackDiv.html('<small class="text-danger"><i class="ti ti-alert-circle me-1"></i>' + response.message + '</small>');
      }
      feedbackDiv.show();
    },
    error: function(xhr) {
      feedbackDiv.html('<small class="text-danger"><i class="ti ti-alert-circle me-1"></i>{{ __("admin.Error checking availability") }}</small>');
      feedbackDiv.show();
    },
    complete: function() {
      btn.prop('disabled', false);
      btn.html('<i class="ti ti-check me-1"></i>{{ __("admin.Check Availability") }}');
    }
  });
});

// Generate slug from organization name
$(document).on('click', '#generateSlugBtn', function() {
  const btn = $(this);
  const orgId = '{{ $organization->id ?? "" }}';

  if (!orgId) {
    toastr.error("{{ __('admin.Please complete the previous steps first') }}");
    return;
  }

  btn.prop('disabled', true);
  btn.html('<span class="spinner-border spinner-border-sm me-1"></span>{{ __("admin.Processing") }}...');

  $.ajax({
    url: "{{ $isEdit ? route('organization.wizard.generate.slug', $organization->id ?? 0) : '#' }}",
    type: 'POST',
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    success: function(response) {
      if (response.status) {
        $('#slug').val(response.slug);
        toastr.success("{{ __('admin.Slug generated successfully') }}");
      } else {
        toastr.error(response.message || "{{ __('admin.Error generating slug') }}");
      }
    },
    error: function(xhr) {
      toastr.error("{{ __('admin.Error generating slug') }}");
    },
    complete: function() {
      btn.prop('disabled', false);
      btn.html('<i class="ti ti-refresh me-1"></i>{{ __("admin.Generate from name") }}');
    }
  });
});

// Add repeater item
$(document).on('click', '.add-item-btn', function() {
  const type = $(this).data('type');
  const max = $(this).data('max');
  const container = getContainerByType(type);
  const currentCount = container.find('.repeater-item').length;

  if (currentCount >= max) {
    toastr.warning(getMaxMessage(type));
    return;
  }

  // Hide empty state
  container.find('.empty-state').hide();

  const newIndex = currentCount;
  const template = getTemplate(type, newIndex);

  if (type === 'partner') {
    container.append(template);
  } else {
    container.append(template);
  }

  // Update step numbers if applicable
  updateStepNumbers(container);
});

// Remove repeater item
$(document).on('click', '.remove-item-btn', function() {
  const item = $(this).closest('.repeater-item');
  const container = item.parent();

  item.fadeOut(200, function() {
    $(this).remove();
    updateStepNumbers(container);

    // Show empty state if no items
    if (container.find('.repeater-item').length === 0) {
      container.find('.empty-state').show();
    }

    // Reindex remaining items
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
      return `
        <div class="repeater-item card shadow-none mb-3 border" data-index="${index}">
          <div class="card-body py-3">
            <div class="row align-items-center">
              <div class="col-md-1 text-center">
                <span class="badge bg-primary rounded-circle step-number" style="width: 32px; height: 32px; line-height: 24px;">${index + 1}</span>
              </div>
              <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="process_steps[${index}][title]" placeholder="{{ __('admin.Step Title') }}" maxlength="255">
              </div>
              <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" name="process_steps[${index}][description]" placeholder="{{ __('admin.Step Description') }}" maxlength="1000">
              </div>
              <div class="col-md-3">
                <select class="form-select form-select-sm icon-select" name="process_steps[${index}][icon]">
                  <option value="">{{ __('admin.Select icon') }}</option>
                  <option value="ti-file-text">File Text</option>
                  <option value="ti-clipboard-check">Clipboard Check</option>
                  <option value="ti-users">Users</option>
                  <option value="ti-rocket">Rocket</option>
                  <option value="ti-chart-bar">Chart Bar</option>
                  <option value="ti-trophy">Trophy</option>
                  <option value="ti-bulb">Bulb</option>
                  <option value="ti-target">Target</option>
                  <option value="ti-check">Check</option>
                  <option value="ti-star">Star</option>
                  <option value="ti-heart">Heart</option>
                  <option value="ti-mail">Mail</option>
                  <option value="ti-send">Send</option>
                  <option value="ti-calendar">Calendar</option>
                  <option value="ti-clock">Clock</option>
                  <option value="ti-settings">Settings</option>
                  <option value="ti-search">Search</option>
                  <option value="ti-home">Home</option>
                  <option value="ti-briefcase">Briefcase</option>
                  <option value="ti-world">World</option>
                  <option value="ti-message">Message</option>
                  <option value="ti-phone">Phone</option>
                  <option value="ti-lock">Lock</option>
                  <option value="ti-shield-check">Shield Check</option>
                  <option value="ti-gift">Gift</option>
                  <option value="ti-thumb-up">Thumb Up</option>
                  <option value="ti-lightbulb">Lightbulb</option>
                  <option value="ti-bolt">Bolt</option>
                  <option value="ti-flag">Flag</option>
                  <option value="ti-award">Award</option>
                </select>
              </div>
              <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item-btn">
                  <i class="ti ti-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>`;

    case 'metric':
      return `
        <div class="repeater-item card shadow-none mb-3 border" data-index="${index}">
          <div class="card-body py-3">
            <div class="row align-items-center">
              <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="metrics[${index}][number]" placeholder="{{ __('admin.Metric Number') }}" maxlength="20">
              </div>
              <div class="col-md-5">
                <input type="text" class="form-control form-control-sm" name="metrics[${index}][label]" placeholder="{{ __('admin.Metric Label') }}" maxlength="255">
              </div>
              <div class="col-md-3">
                <select class="form-select form-select-sm icon-select" name="metrics[${index}][icon]">
                  <option value="">{{ __('admin.Select icon') }}</option>
                  <option value="ti-users">Users</option>
                  <option value="ti-building">Building</option>
                  <option value="ti-currency-dollar">Currency Dollar</option>
                  <option value="ti-rocket">Rocket</option>
                  <option value="ti-chart-line">Chart Line</option>
                  <option value="ti-briefcase">Briefcase</option>
                  <option value="ti-world">World</option>
                  <option value="ti-star">Star</option>
                  <option value="ti-trophy">Trophy</option>
                  <option value="ti-chart-bar">Chart Bar</option>
                  <option value="ti-chart-pie">Chart Pie</option>
                  <option value="ti-trending-up">Trending Up</option>
                  <option value="ti-target">Target</option>
                  <option value="ti-award">Award</option>
                  <option value="ti-flag">Flag</option>
                  <option value="ti-calendar">Calendar</option>
                  <option value="ti-clock">Clock</option>
                  <option value="ti-heart">Heart</option>
                  <option value="ti-thumb-up">Thumb Up</option>
                  <option value="ti-check">Check</option>
                  <option value="ti-bolt">Bolt</option>
                  <option value="ti-gift">Gift</option>
                  <option value="ti-home">Home</option>
                  <option value="ti-map-pin">Map Pin</option>
                  <option value="ti-globe">Globe</option>
                  <option value="ti-sparkles">Sparkles</option>
                  <option value="ti-flame">Flame</option>
                  <option value="ti-diamond">Diamond</option>
                  <option value="ti-crown">Crown</option>
                  <option value="ti-medal">Medal</option>
                </select>
              </div>
              <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item-btn">
                  <i class="ti ti-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>`;

    case 'article':
      return `
        <div class="repeater-item card mb-3 border" data-index="${index}">
          <div class="card-body py-3">
            <div class="row">
              <div class="col-md-3 mb-2">
                <input type="text" class="form-control form-control-sm" name="articles[${index}][publication_name]" placeholder="{{ __('admin.Publication Name') }}" maxlength="255">
              </div>
              <div class="col-md-2 mb-2">
                <input type="date" class="form-control form-control-sm" name="articles[${index}][date]">
              </div>
              <div class="col-md-3 mb-2">
                <input type="text" class="form-control form-control-sm" name="articles[${index}][title]" placeholder="{{ __('admin.Article Title') }}" maxlength="255">
              </div>
              <div class="col-md-3 mb-2">
                <input type="url" class="form-control form-control-sm" name="articles[${index}][link]" placeholder="{{ __('admin.Article Link') }}" maxlength="500">
              </div>
              <div class="col-md-1 text-end mb-2">
                <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item-btn">
                  <i class="ti ti-trash"></i>
                </button>
              </div>
              <div class="col-md-8 mb-2">
                <textarea class="form-control form-control-sm" name="articles[${index}][summary]" rows="2" placeholder="{{ __('admin.Article Summary') }}" maxlength="1000"></textarea>
              </div>
              <div class="col-md-4 mb-2">
                <label class="form-label small">{{ __('admin.Article Image') }}</label>
                <input type="file" class="form-control form-control-sm" name="articles[${index}][image]" accept="image/png,image/jpeg,image/jpg">
                <small class="text-muted">PNG, JPG ({{ __('admin.Max size') }}: 5MB)</small>
              </div>
            </div>
          </div>
        </div>`;

    case 'partner':
      return `
        <div class="repeater-item col-md-4 col-lg-3 mb-3" data-index="${index}">
          <div class="card border h-100">
            <div class="card-body py-3 text-center">
              <button type="button" class="btn btn-sm btn-icon btn-label-danger position-absolute top-0 end-0 m-2 remove-item-btn">
                <i class="ti ti-x"></i>
              </button>
              <div class="mb-2 bg-light rounded d-flex align-items-center justify-content-center" style="height: 60px;">
                <i class="ti ti-building text-muted fs-3"></i>
              </div>
              <input type="text" class="form-control form-control-sm mb-2" name="partners_list[${index}][name]" placeholder="{{ __('admin.Partner Name') }}" maxlength="255">
              <input type="file" class="form-control form-control-sm" name="partners_list[${index}][logo]" accept="image/png,image/jpeg,image/jpg,image/svg+xml">
              <small class="text-muted d-block mt-1">PNG, JPG, SVG (2MB)</small>
            </div>
          </div>
        </div>`;
  }
}

function updateStepNumbers(container) {
  container.find('.step-number').each(function(index) {
    $(this).text(index + 1);
  });
}

function reindexItems(container) {
  container.find('.repeater-item').each(function(index) {
    $(this).attr('data-index', index);
    $(this).find('input, select, textarea').each(function() {
      const name = $(this).attr('name');
      if (name) {
        const newName = name.replace(/\[\d+\]/, '[' + index + ']');
        $(this).attr('name', newName);
      }
    });
  });
}
</script>
@endpush
