<div class="row">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center border-bottom mb-6">
      <div>
        <h5 class="mb-1">{{ __('admin.screening.add_judging_panels') }}</h5>
        <p class="text-muted">{{ __('admin.screening.add_panels_description') }}</p>
      </div>
      <button type="button" class="btn btn-outline-primary" id="addPanelBtn">
        <i class="ti ti-plus"></i>
        <span class="d-none d-md-inline ms-2">{{ __('admin.screening.add_panel') }}</span>
      </button>
    </div>

    <form method="POST" action="{{ route('screening.add_judging_panels') }}" id="judging-panels-form" novalidate>
    @csrf
    <input type="hidden" name="step" value="{{ $cstep->id }}">

    @php $l = app()->getLocale(); @endphp

    <div class="accordion mb-4" id="panels-container">
      @foreach($judgingPanels as $index => $panel)
        <div class="accordion-item border" data-index="{{ $index }}">
          <h2 class="accordion-header d-flex align-items-center py-3">
            <button class="accordion-button flex-grow-1 {{ $index === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#panel-{{ $index + 1 }}">
              <i class="ti ti-user-check me-2"></i>
              {{ __('admin.screening.panel') }} #{{ $index + 1 }}
            </button>
            <button type="button" class="btn btn-icon btn-sm btn-outline-danger ms-2 me-4 remove-panel {{ $index === 0 && count($judgingPanels) === 1 ? 'd-none' : '' }}" title="{{ __('admin.delete') }}">
              <i class="ti ti-trash"></i>
            </button>
          </h2>

          <div id="panel-{{ $index + 1 }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}">
            <div class="accordion-body mt-3">
              <div class="row">
                <!-- Judges -->
                <div class="col-md-6 mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-semibold mb-0">{{ __('admin.Judgers') }}</label>
                    <a href="javascript:void(0);" class="select-all-toggle small fw-medium">{{ __('admin.select_all') }}</a>
                  </div>
                  <select name="panels[{{ $index }}][judges][]" class="form-select select2" multiple data-placeholder="{{ __('cycles.Select Judges') }}">
                    @foreach($judges as $judge)
                      <option value="{{ $judge->id }}" @selected(in_array($judge->id, (array)$panel['judges']))>
                        {{ $judge->{'first_name_' . $l} }} {{ $judge->{'last_name_' . $l} }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <!-- Companies -->
                <div class="col-md-6 mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-semibold mb-0">{{ __('admin.Startups') }}</label>
                    <a href="javascript:void(0);" class="select-all-toggle small fw-medium">{{ __('admin.select_all') }}</a>
                  </div>
                  <select name="panels[{{ $index }}][companies][]" class="form-select select2" multiple data-placeholder="{{ __('admin.select_startups') }}">
                    @foreach($companies as $company)
                      <option value="{{ $company->id }}" @selected(in_array($company->id, (array)$panel['companies']))>
                        {{ $company->{'name_' . $l} }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <hr class="my-4">

              <!-- Session -->
              <div class="form-check form-switch mb-3">
                <input class="form-check-input session-toggle" type="checkbox" id="session-toggle-{{ $index + 1 }}" {{ !empty($panel['session']) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold">{{ __('admin.screening.schedule_session') }}</label>
              </div>

              <div class="session-inputs border rounded p-4" style="display: {{ !empty($panel['session']) ? 'block' : 'none' }}">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('admin.Session_date') }}</label>
                    <input type="text" class="form-control session-date" name="panels[{{ $index }}][session_date]" value="{{ $panel['session']['date'] ?? '' }}" placeholder="YYYY-MM-DD">
                    <div class="invalid-feedback error-session_date"></div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('admin.Meeting Link') }}</label>
                    <input type="url" class="form-control" name="panels[{{ $index }}][meeting_link]" value="{{ $panel['session']['meeting_link'] ?? '' }}" placeholder="https://meet.google.com/...">
                    <div class="invalid-feedback error-meeting_link"></div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('admin.Start Time') }}</label>
                    <input type="text" class="form-control session-time" name="panels[{{ $index }}][start_time]" value="{{ $panel['session']['start_time'] ?? '' }}" placeholder="HH:MM">
                    <div class="invalid-feedback error-start_time"></div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('admin.End Time') }}</label>
                    <input type="text" class="form-control session-time" name="panels[{{ $index }}][end_time]" value="{{ $panel['session']['end_time'] ?? '' }}" placeholder="HH:MM">
                    <div class="invalid-feedback error-end_time"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="d-flex justify-content-end mt-4">
      <button type="submit" class="btn btn-primary">{{ __('admin.submit') }}</button>
    </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
const isValidUrl = (str) => {
  try { return new URL(str).protocol.startsWith('http'); }
  catch { return false; }
};

const initPlugins = (el) => {
  $(el).find('.session-date').flatpickr({ dateFormat: 'Y-m-d', minDate: 'today' });
  $(el).find('.session-time').flatpickr({ enableTime: true, noCalendar: true, dateFormat: 'H:i' });
  $(el).find('.select2').each(function() {
    $(this).select2({ placeholder: $(this).data('placeholder'), allowClear: true, width: '100%', dropdownParent: $(this).parent() });
  });
};

const updateSelectAllText = ($select) => {
  const $link = $select.closest('.col-md-6').find('.select-all-toggle');
  const total = $select.find('option').length;
  const selected = ($select.val() || []).length;
  $link.text(selected === total && total > 0 ? '{{ __("admin.deselect_all") }}' : '{{ __("admin.select_all") }}');
};

const renumberPanels = () => {
  const $panels = $('.accordion-item');
  $panels.each((i, el) => {
    const idx = i + 1;
    const $panel = $(el);
    $panel.attr('data-index', i);
    $panel.find('.accordion-button').attr('data-bs-target', `#panel-${idx}`).html(`<i class="ti ti-user-check me-2"></i> {{ __('admin.screening.panel') }} #${idx}`);
    $panel.find('.accordion-collapse').attr('id', `panel-${idx}`);
    $panel.find('.session-toggle').attr('id', `session-toggle-${idx}`);
    $panel.find('[name*="panels["]').each(function() {
      $(this).attr('name', $(this).attr('name').replace(/panels\[\d+\]/, `panels[${i}]`));
    });
    
    // Always hide delete button for first panel
    const $btn = $panel.find('.remove-panel');
    if (i === 0) {
      $btn.addClass('d-none');
    } else {
      $btn.removeClass('d-none');
    }
  });
};

initPlugins(document);

$(document).on('click', '.select-all-toggle', function() {
  const $select = $(this).closest('.col-md-6').find('select');
  const allOpts = $select.find('option').map((_, el) => $(el).val()).get();
  const curr = $select.val() || [];
  $select.val(curr.length === allOpts.length ? null : allOpts).trigger('change');
});

$(document).on('change', '.select2', function() { updateSelectAllText($(this)); });

$('#addPanelBtn').on('click', () => {
  const $clone = $('.accordion-item').first().clone();
  
  // mark as unsaved
  $clone.attr('data-unsaved', 'true');
  
  const idx = $('.accordion-item').length + 1;

  $clone.find('.select2-container').remove();
  $clone.find('.select2').removeClass('select2-hidden-accessible');
  $clone.find('*').removeAttr('data-select2-id');
  $clone.find('select, input[type="text"], input[type="url"]').val('');
  $clone.find('.select-all-toggle').text('{{ __("admin.select_all") }}');
  
  $clone.find('.accordion-button').addClass('collapsed').attr('data-bs-target', `#panel-${idx}`).html(`<i class="ti ti-user-check me-2"></i> @lang('admin.screening.panel') #${idx}`);
  $clone.find('.accordion-collapse').removeClass('show').attr('id', `panel-${idx}`);
  $clone.find('.session-toggle').prop('checked', false).attr('id', `session-toggle-${idx}`);
  $clone.find('.session-inputs').hide();
  
  if (!$clone.find('.remove-panel').length) {
    $clone.find('.accordion-header').append('<button type="button" class="btn btn-icon btn-sm btn-outline-danger ms-2 me-4 remove-panel"><i class="ti ti-trash"></i></button>');
  }
  
  $('#panels-container').append($clone);
  renumberPanels();
  initPlugins($clone);
});

$(document).on('click', '.remove-panel', async function() {
  const $panel = $(this).closest('.accordion-item');

  // If panel is not saved yet → remove silently
  if ($panel.data('unsaved') === true || $panel.attr('data-unsaved') === 'true') {
    $panel.remove();
    renumberPanels();
    return;
  }

  const panelIdx = $panel.data('index') + 1; // 1-based index for backend

  const result = await Swal.fire({
    icon: 'warning', title: '@lang("admin.SureDelete")', showCancelButton: true,
    confirmButtonText: '@lang("admin.DYes")', cancelButtonText: '@lang("Cancel")',
    customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-label-secondary' },
    buttonsStyling: false
  });

  if (result.isConfirmed) {
    try {
      const response = await fetch('{{ route("screening.delete_judging_panel") }}', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ step: '{{ $cstep->id }}', panel: panelIdx })
      });
      const data = await response.json();
      if (data.success) {
        $panel.remove();
        renumberPanels();
        releaseSuccessAlert({ title: '{{ __('admin.success') }}', message: data.message });
      } else {
        releaseFailureAlert({ title: '{{ __('admin.error') }}', message: data.message });
      }
    } catch (e) {
      releaseFailureAlert({ title: '{{ __('admin.error') }}', message: e?.message || '{{ __('admin.unexpected_error') }}' });
    }
  }
});

$(document).on('change', '.session-toggle', function() {
  $(this).closest('.accordion-body').find('.session-inputs').toggle(this.checked);
});

$(document).on('submit', '#judging-panels-form', function(e) {
  e.preventDefault();
  let valid = true;
  
  $('.session-inputs input').removeClass('is-invalid');
  $('.invalid-feedback').text('');
  
  $('.accordion-item').each(function() {
    const $p = $(this);
    if (!$p.find('.session-toggle').is(':checked')) return;
    
    const fields = {
      session_date: { msg: '{{ __('admin.date_required') }}' },
      meeting_link: { msg: '{{ __('admin.meeting_link_required') }}', url: true },
      start_time: { msg: '{{ __('admin.start_time_required') }}' },
      end_time: { msg: '{{ __('admin.end_time_required') }}' }
    };
    
    $.each(fields, (name, cfg) => {
      const $inp = $p.find(`input[name*="${name}"]`);
      const val = $inp.val().trim();
      
      if (!val) {
        $inp.addClass('is-invalid');
        $p.find(`.error-${name}`).text(cfg.msg);
        valid = false;
      } else if (cfg.url && !isValidUrl(val)) {
        $inp.addClass('is-invalid');
        $p.find(`.error-${name}`).text('{{ __('admin.meeting_link_url_required') }}');
        valid = false;
      }
    });
    
    const start = $p.find('input[name*="start_time"]').val();
    const end = $p.find('input[name*="end_time"]').val();
    if (start && end && start >= end) {
      $p.find('input[name*="end_time"]').addClass('is-invalid');
      $p.find('.error-end_time').text('{{ __('admin.end_time_must_be_after_start_time') }}');
      valid = false;
    }
  });
  
  if (!valid) {
    const $err = $('.is-invalid').first();
    const $collapse = $err.closest('.accordion-collapse');
    if (!$collapse.hasClass('show')) $collapse.collapse('show');
    $('html, body').animate({ scrollTop: $err.offset().top - 100 }, 300);
  } else {
    fetch($(this).attr('action'), {
      method: 'POST', body: new FormData(this),
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(d => {
      if (d.success) releaseSuccessAlert({ title: '{{ __('admin.success') }}', message: d.message });
      else releaseFailureAlert({ title: '{{ __('admin.error') }}', message: d.message || '{{ __('admin.unexpected_error') }}' });
    })
    .catch(e => releaseFailureAlert({ title: '{{ __('admin.error') }}', message: e?.message || '{{ __('admin.unexpected_error') }}' }));
  }
});

$(document).on('input', '.session-inputs input', function() {
  $(this).removeClass('is-invalid');
  const field = $(this).attr('name').match(/\[([^\]]+)\]$/)[1];
  $(this).closest('.accordion-item').find(`.error-${field}`).text('');
});
</script>
@endpush