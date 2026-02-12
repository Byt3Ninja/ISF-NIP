@php
  $orgPrograms = $orgPrograms ?? ($org ? $org->cycles()->whereNull('deleted_at')->with(['focusAreas', 'benefits'])->orderBy('created_at', 'desc')->get() : collect());
  $focusAreaOptions = $focusAreaOptions ?? \App\StaticCategory::all();
  $locale = app()->getLocale();
  $isRtl = $locale === 'ar';

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
@endphp

<!-- Programs Section -->
<div class="row" id="programsContent">
  <div class="col-12 mb-6">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">{{ __('admin.Programs') }}</h5>
      <div class="form-check form-switch me-3">
        <input
          type="checkbox"
          class="form-check-input"
          id="programs_enabled"
          name="programs_enabled"
          value="1"
          {{ old('programs_enabled', $landingPage->programs_enabled ?? false) ? 'checked' : '' }}
          data-section="programs"
        />
        <label class="form-check-label" for="programs_enabled">
          {{ __('admin.Enable Section') }}
        </label>
      </div>
    </div>
  </div>

  <div class="col-12">
    <!-- Section Header Fields -->
    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <label class="form-label" for="programs_title">{{ __('admin.Section Title') }}</label>
        <input
          type="text"
          class="form-control"
          id="programs_title"
          name="programs_title"
          value="{{ old('programs_title', $landingPage->programs_title ?? '') }}"
          placeholder="{{ __('admin.Enter section title') }}"
          maxlength="255"
        >
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="programs_subtitle">{{ __('admin.Section Subtitle') }}</label>
        <input
          type="text"
          class="form-control"
          id="programs_subtitle"
          name="programs_subtitle"
          value="{{ old('programs_subtitle', $landingPage->programs_subtitle ?? '') }}"
          placeholder="{{ __('admin.Enter section subtitle') }}"
          maxlength="255"
        >
      </div>
      <div class="col-12 mb-3">
        <label class="form-label" for="programs_description">{{ __('admin.Section Description') }}</label>
        <textarea
          class="form-control"
          id="programs_description"
          name="programs_description"
          rows="2"
          placeholder="{{ __('admin.Enter section description') }}"
          maxlength="1000"
        >{{ old('programs_description', $landingPage->programs_description ?? '') }}</textarea>
      </div>
    </div>

    <!-- Order By -->
    <div class="row mb-6">
      <div class="col-md-4">
        <label class="form-label">{{ __('admin.Display Order') }}</label>
        <select class="form-select" name="programs_order_by">
          <option value="newest" {{ old('programs_order_by', $landingPage->programs_order_by ?? 'newest') == 'newest' ? 'selected' : '' }}>
            {{ __('admin.Newest First') }}
          </option>
          <option value="oldest" {{ old('programs_order_by', $landingPage->programs_order_by ?? '') == 'oldest' ? 'selected' : '' }}>
            {{ __('admin.Oldest First') }}
          </option>
          <option value="custom" {{ old('programs_order_by', $landingPage->programs_order_by ?? '') == 'custom' ? 'selected' : '' }}>
            {{ __('admin.Custom Order') }}
          </option>
        </select>
      </div>
    </div>

    <!-- Programs List -->
    <div class="mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">{{ __('admin.Program Cards') }}</h6>
        <small class="text-muted">{{ __('admin.Toggle visibility for each program') }}</small>
      </div>

      <div id="programsLandingContainer">
        @forelse($orgPrograms as $program)
          <div class="card border mb-3 program-landing-card" data-program-id="{{ $program->id }}">
            <div class="card-body py-3">
              <div class="row align-items-start">
                <!-- Toggle + Program Info -->
                <div class="col-md-4">
                  <div class="d-flex align-items-center mb-2">
                    <div class="form-check form-switch me-2">
                      <input type="checkbox" class="form-check-input program-visible-toggle"
                        data-program-id="{{ $program->id }}"
                        {{ $program->landing_visible ? 'checked' : '' }} />
                    </div>
                    <div>
                      <strong>{{ $program->title }}</strong>
                      <br>
                      <small class="text-muted">
                        <span class="badge bg-label-{{ $program->type == 'Hackathon' ? 'warning' : 'primary' }}">
                          {{ __('cycles.' . ($program->type ?? 'General_Program')) }}
                        </span>
                        @if($program->start)
                          <i class="ti ti-calendar ms-2"></i> {{ $program->start->format('M d, Y') }}
                        @endif
                      </small>
                    </div>
                  </div>
                </div>

                <!-- Short Description -->
                <div class="col-md-3">
                  <div class="form-floating">
                    <textarea class="form-control form-control-sm program-field"
                      data-field="short_description"
                      id="shortDesc{{ $program->id }}"
                      placeholder="{{ __('admin.Short Description') }}"
                      style="height: 68px"
                      maxlength="500">{{ $program->short_description ?? '' }}</textarea>
                    <label for="shortDesc{{ $program->id }}">{{ __('admin.Short Description') }}</label>
                  </div>
                </div>

                <!-- Location -->
                <div class="col-md-3">
                  <div class="form-floating">
                    <input type="text" class="form-control form-control-sm program-field"
                      data-field="location"
                      id="location{{ $program->id }}"
                      placeholder="{{ __('admin.Location') }}"
                      value="{{ $program->location ?? '' }}"
                      maxlength="255">
                    <label for="location{{ $program->id }}">{{ __('admin.Location') }}</label>
                  </div>
                </div>

                <!-- Expand for Details -->
                <div class="col-md-2 text-end">
                  <button type="button" class="btn btn-sm btn-outline-primary toggle-program-details"
                    data-bs-toggle="collapse" data-bs-target="#programDetails{{ $program->id }}">
                    <i class="ti ti-chevron-down me-1"></i>{{ __('admin.Details') }}
                  </button>
                </div>
              </div>

              <!-- Expandable Details -->
              <div class="collapse mt-3" id="programDetails{{ $program->id }}">
                <hr class="my-3">
                <div class="row">
                  <!-- Focus Areas -->
                  <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">
                      <i class="ti ti-tags me-1"></i>{{ __('admin.Focus Areas') }}
                    </label>
                    <select class="select2 form-select program-focus-areas"
                      data-program-id="{{ $program->id }}" multiple>
                      @foreach($focusAreaOptions as $category)
                        <option value="{{ $category->id }}"
                          {{ $program->focusAreas->contains('id', $category->id) ? 'selected' : '' }}>
                          {{ $isRtl ? $category->name_ar : $category->name_en }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <!-- Benefits -->
                  <div class="col-md-6 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <label class="form-label small fw-semibold mb-0">
                        <i class="ti ti-star me-1"></i>{{ __('admin.Benefits') }}
                      </label>
                      <button type="button" class="btn btn-sm btn-label-primary add-benefit-btn"
                        data-program-id="{{ $program->id }}">
                        <i class="ti ti-plus me-1"></i>{{ __('admin.Add') }}
                      </button>
                    </div>
                    <div class="benefits-container" id="benefits{{ $program->id }}">
                      @foreach($program->benefits as $benefit)
                        <div class="d-flex align-items-center gap-2 mb-2 benefit-row">
                          <span class="benefit-icon-preview d-inline-flex align-items-center justify-content-center rounded bg-label-primary"
                            style="width: 32px; height: 32px; min-width: 32px;">
                            <i class="ti {{ $benefit->icon ?: 'ti-circle-check' }} ti-sm"></i>
                          </span>
                          <select class="form-select form-select-sm benefit-icon" style="max-width: 110px;">
                            <option value="">{{ __('admin.Icon') }}</option>
                            @foreach($benefitIcons as $iconVal => $iconLbl)
                              <option value="{{ $iconVal }}"
                                {{ ($benefit->icon ?? '') == $iconVal ? 'selected' : '' }}>{{ $iconLbl }}</option>
                            @endforeach
                          </select>
                          <input type="text" class="form-control form-control-sm benefit-label-en"
                            value="{{ $benefit->label_en }}" placeholder="{{ __('admin.Benefit EN') }}">
                          <input type="text" class="form-control form-control-sm benefit-label-ar"
                            value="{{ $benefit->label_ar ?? '' }}" placeholder="{{ __('admin.Benefit AR') }}">
                          <button type="button" class="btn btn-sm btn-icon btn-text-danger remove-benefit-btn" title="{{ __('admin.Remove') }}">
                            <i class="ti ti-trash ti-sm"></i>
                          </button>
                        </div>
                      @endforeach
                    </div>
                  </div>
                </div>

                <hr class="my-3">

                <!-- Program Visibility -->
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label small fw-semibold">
                      <i class="ti ti-eye me-1"></i>{{ __('cycles.visibility') }}
                    </label>
                    <select class="form-select form-select-sm program-is-public" data-program-id="{{ $program->id }}">
                      <option value="0" {{ !$program->is_public ? 'selected' : '' }}>
                        {{ __('cycles.Program Visible To Private') }}
                      </option>
                      <option value="1" {{ $program->is_public ? 'selected' : '' }}>
                        {{ __('cycles.Program Visible To Public') }}
                      </option>
                    </select>
                  </div>
                  <div class="col-md-5 mb-3 program-public-slug-wrapper" style="{{ $program->is_public ? '' : 'display:none' }}">
                    <label class="form-label small fw-semibold">
                      <i class="ti ti-link me-1"></i>{{ __('cycles.Public URL') }}
                    </label>
                    <div class="input-group input-group-sm">
                      <input type="text" class="form-control form-control-sm program-public-slug"
                        value="{{ $program->public_slug ?? '' }}"
                        placeholder="{{ __('cycles.Public URL placeholder') }}">
                      <button type="button" class="btn btn-outline-secondary validate-slug-btn"
                        data-program-id="{{ $program->id }}" title="{{ __('admin.Validate URL') }}">
                        <i class="ti ti-refresh ti-xs"></i>
                      </button>
                    </div>
                    <div class="valid-feedback"></div>
                    <div class="invalid-feedback"></div>
                  </div>
                </div>
              </div>

              <!-- Save Button (always visible) -->
              <div class="text-end mt-3">
                <button type="button" class="btn btn-sm btn-primary save-program-landing-btn"
                  data-program-id="{{ $program->id }}"
                  data-org-id="{{ $org->id ?? $organization->id }}">
                  <span class="btn-text"><i class="ti ti-device-floppy me-1"></i>{{ __('admin.Save Program') }}</span>
                  <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm" role="status"></span></span>
                </button>
              </div>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4 rounded border-dashed">
            <i class="ti ti-rocket fs-1 mb-2 d-block opacity-50"></i>
            <p class="mb-0">{{ __('admin.No programs found') }}</p>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize Select2 for focus areas inside collapsed sections
  // We initialize on first expand to avoid Select2 rendering issues inside hidden elements
  document.querySelectorAll('.program-landing-card').forEach(function(card) {
    var collapseEl = card.querySelector('.collapse');
    if (!collapseEl) return;

    collapseEl.addEventListener('shown.bs.collapse', function() {
      var $sel = $(this).find('.program-focus-areas');
      if ($sel.length && !$sel.data('select2')) {
        $sel.wrap('<div class="position-relative"></div>').select2({
          placeholder: '{{ __("admin.Select focus areas") }}',
          dropdownParent: $sel.parent(),
          allowClear: true,
          width: '100%'
        });
      }
    }, { once: true });
  });

  // Save individual program landing data via AJAX
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.save-program-landing-btn');
    if (!btn) return;

    var programId = btn.dataset.programId;
    var orgId = btn.dataset.orgId;
    var card = btn.closest('.program-landing-card');

    // Collect benefits
    var benefits = [];
    card.querySelectorAll('.benefit-row').forEach(function(row) {
      benefits.push({
        icon: row.querySelector('.benefit-icon').value,
        label_en: row.querySelector('.benefit-label-en').value,
        label_ar: row.querySelector('.benefit-label-ar').value,
      });
    });

    // Collect focus areas from Select2
    var $focusSelect = $(card).find('.program-focus-areas');
    var focusAreaIds = $focusSelect.length ? ($focusSelect.val() || []) : [];

    var isPublicSelect = card.querySelector('.program-is-public');
    var publicSlugInput = card.querySelector('.program-public-slug');

    var data = {
      _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      landing_visible: card.querySelector('.program-visible-toggle').checked ? 1 : 0,
      short_description: card.querySelector('[data-field="short_description"]').value,
      location: card.querySelector('[data-field="location"]').value,
      is_public: isPublicSelect ? parseInt(isPublicSelect.value) : 0,
      public_slug: publicSlugInput ? publicSlugInput.value.trim() : '',
      focus_area_ids: focusAreaIds,
      benefits: benefits,
    };

    btn.disabled = true;
    var btnText = btn.querySelector('.btn-text');
    var btnSpinner = btn.querySelector('.btn-spinner');
    if (btnText) btnText.classList.add('d-none');
    if (btnSpinner) btnSpinner.classList.remove('d-none');

    function resetBtn() {
      btn.disabled = false;
      if (btnText) btnText.classList.remove('d-none');
      if (btnSpinner) btnSpinner.classList.add('d-none');
    }

    fetch('/dashboard/organization/' + orgId + '/programs/' + programId + '/landing', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': data._token,
        'Accept': 'application/json',
      },
      body: JSON.stringify(data),
    })
    .then(function(r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    })
    .then(function(response) {
      if (response.success) {
        if (typeof toastr !== 'undefined') toastr.success(response.message);
      } else {
        if (typeof toastr !== 'undefined') toastr.error(response.message || 'Error');
      }
      resetBtn();
    })
    .catch(function(err) {
      console.error('Save program error:', err);
      if (typeof toastr !== 'undefined') toastr.error('Error saving program data');
      resetBtn();
    });
  });

  // Add benefit row
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.add-benefit-btn');
    if (!btn) return;

    var programId = btn.dataset.programId;
    var container = document.getElementById('benefits' + programId);
    if (container.querySelectorAll('.benefit-row').length >= 8) {
      if (typeof toastr !== 'undefined') toastr.warning('{{ __("admin.Maximum 8 benefits allowed") }}');
      return;
    }

    var row = document.createElement('div');
    row.className = 'd-flex align-items-center gap-2 mb-2 benefit-row';
    row.innerHTML =
      '<span class="benefit-icon-preview d-inline-flex align-items-center justify-content-center rounded bg-label-secondary" style="width: 32px; height: 32px; min-width: 32px;">' +
        '<i class="ti ti-circle-check ti-sm"></i>' +
      '</span>' +
      '<select class="form-select form-select-sm benefit-icon" style="max-width: 110px;">' +
        '<option value="">{{ __("admin.Icon") }}</option>' +
        @foreach($benefitIcons as $iconVal => $iconLbl)
        '<option value="{{ $iconVal }}">{{ $iconLbl }}</option>' +
        @endforeach
      '</select>' +
      '<input type="text" class="form-control form-control-sm benefit-label-en" placeholder="{{ __("admin.Benefit EN") }}">' +
      '<input type="text" class="form-control form-control-sm benefit-label-ar" placeholder="{{ __("admin.Benefit AR") }}">' +
      '<button type="button" class="btn btn-sm btn-icon btn-text-danger remove-benefit-btn" title="{{ __("admin.Remove") }}"><i class="ti ti-trash ti-sm"></i></button>';
    container.appendChild(row);
  });

  // Remove benefit row
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.remove-benefit-btn');
    if (!btn) return;
    btn.closest('.benefit-row').remove();
  });

  // Update icon preview when benefit icon dropdown changes
  document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('benefit-icon')) return;
    var val = e.target.value;
    var row = e.target.closest('.benefit-row');
    var preview = row.querySelector('.benefit-icon-preview');
    if (preview) {
      preview.className = 'd-inline-flex align-items-center justify-content-center rounded ' + (val ? 'bg-label-primary' : 'bg-label-secondary');
      preview.style.cssText = 'width: 32px; height: 32px; min-width: 32px;';
      preview.innerHTML = '<i class="ti ' + (val || 'ti-circle-check') + ' ti-sm"></i>';
    }
  });

  // Toggle public slug field visibility based on is_public dropdown
  document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('program-is-public')) return;
    var card = e.target.closest('.program-landing-card');
    var slugWrapper = card.querySelector('.program-public-slug-wrapper');
    if (slugWrapper) {
      slugWrapper.style.display = e.target.value === '1' ? '' : 'none';
    }
  });

  // Validate public slug via AJAX
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.validate-slug-btn');
    if (!btn || btn.disabled) return;

    var card = btn.closest('.program-landing-card');
    var slugInput = card.querySelector('.program-public-slug');
    var slug = slugInput.value.trim();
    if (!slug) return;

    var programId = btn.dataset.programId;
    var wrapper = btn.closest('.program-public-slug-wrapper');
    var validFeedback = wrapper.querySelector('.valid-feedback');
    var invalidFeedback = wrapper.querySelector('.invalid-feedback');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    slugInput.classList.remove('is-valid', 'is-invalid');
    validFeedback.innerHTML = '';
    validFeedback.style.display = 'none';
    invalidFeedback.innerHTML = '';
    invalidFeedback.style.display = 'none';

    $.ajax({
      type: 'POST',
      url: '/dashboard/cycles/validate-url',
      data: {
        slug: slug,
        title: card.querySelector('strong').textContent.trim(),
        id: programId,
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      success: function(response) {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-refresh ti-xs"></i>';

        if (response.status) {
          slugInput.classList.add('is-valid');
          slugInput.value = response.slug;
          validFeedback.textContent = response.message;
          validFeedback.style.display = 'block';
          invalidFeedback.style.display = 'none';
        } else {
          slugInput.classList.add('is-invalid');
          var msg = response.message;
          if (response.slugs && response.slugs.length) {
            msg += '<ul class="mb-0 mt-1">';
            response.slugs.forEach(function(s) {
              msg += '<li><a href="javascript:void(0);" class="text-danger use-suggested-slug">' + s + '</a></li>';
            });
            msg += '</ul>';
          }
          invalidFeedback.innerHTML = msg;
          invalidFeedback.style.display = 'block';
          validFeedback.style.display = 'none';
        }
      },
      error: function(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-refresh ti-xs"></i>';
        slugInput.classList.add('is-invalid');
        invalidFeedback.textContent = err.responseJSON ? err.responseJSON.message : 'Validation error';
        invalidFeedback.style.display = 'block';
        validFeedback.style.display = 'none';
      }
    });
  });

  // Use suggested slug when clicked
  document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('use-suggested-slug')) return;
    e.preventDefault();
    var slug = e.target.textContent.trim();
    var wrapper = e.target.closest('.program-public-slug-wrapper');
    var slugInput = wrapper.querySelector('.program-public-slug');
    slugInput.value = slug;
    slugInput.classList.remove('is-invalid');
    slugInput.classList.add('is-valid');
    var ivf = wrapper.querySelector('.invalid-feedback');
    var vf = wrapper.querySelector('.valid-feedback');
    ivf.innerHTML = '';
    ivf.style.display = 'none';
    vf.textContent = '{{ __("cycles.Public URL available") }}';
    vf.style.display = 'block';
  });
});
</script>
