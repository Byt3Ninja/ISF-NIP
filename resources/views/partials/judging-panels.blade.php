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

    @php
      $l = app()->getLocale();
      // Off-step references: startups already saved into a panel that have since
      // moved to another step. Rendered as disabled (read-only) options so they
      // remain visible in their panel but cannot be added/removed until they
      // return to this step. Keyed by id for quick lookup.
      $offStepCompanies = ($offStepCompanies ?? collect());
      $offStepById = collect($offStepCompanies)->keyBy('id');
      $offStepIds = $offStepById->keys()->map(fn ($i) => (int) $i)->all();
    @endphp

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
                  @php
                    // Off-step companies saved in THIS panel (read-only references).
                    $panelOffStepIds = collect($panel['companies'] ?? [])->map(fn ($i) => (int) $i)
                      ->filter(fn ($i) => in_array($i, $offStepIds, true))->unique()->values();
                  @endphp
                  <select name="panels[{{ $index }}][companies][]" class="form-select select2" multiple data-placeholder="{{ __('admin.select_startups') }}">
                    @foreach($companies as $company)
                      <option value="{{ $company->id }}" @selected(in_array($company->id, (array)$panel['companies']))>
                        {{ $company->{'name_' . $l} }}
                      </option>
                    @endforeach
                    {{-- Read-only reference options for startups that moved off this step --}}
                    @foreach($panelOffStepIds as $offId)
                      @php $offCompany = $offStepById->get($offId); @endphp
                      @if($offCompany)
                        <option value="{{ $offId }}" selected disabled data-moved="1">
                          {{ $offCompany->{'name_' . $l} }} — {{ __('admin.moved_off_step') }}
                        </option>
                      @endif
                    @endforeach
                  </select>
                  {{-- Disabled options are not submitted; preserve off-step assignments explicitly --}}
                  @foreach($panelOffStepIds as $offId)
                    <input type="hidden" name="panels[{{ $index }}][companies][]" value="{{ $offId }}">
                  @endforeach
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
                    <label class="form-label">{{ __('admin.Start Time') }}</label>
                    <input type="text" class="form-control session-time" name="panels[{{ $index }}][start_time]" value="{{ $panel['session']['start_time'] ?? '' }}" placeholder="HH:MM">
                    <div class="invalid-feedback error-start_time"></div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('admin.End Time') }}</label>
                    <input type="text" class="form-control session-time" name="panels[{{ $index }}][end_time]" value="{{ $panel['session']['end_time'] ?? '' }}" placeholder="HH:MM">
                    <div class="invalid-feedback error-end_time"></div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('admin.Meeting Link') }}</label>
                    <div class="input-group">
                      <input type="url" class="form-control meeting-link-input" value="{{ $panel['session']['meeting_link'] ?? '' }}" placeholder="{{ __('admin.auto_generated') }}" readonly>
                      <button type="button" class="btn btn-outline-secondary copy-meeting-link" title="{{ __('admin.copy_meeting_link') }}" style="{{ empty($panel['session']['meeting_link']) ? 'display:none' : '' }}">
                        <i class="ti ti-copy"></i>
                      </button>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('admin.meeting_password') }}</label>
                    <div class="input-group">
                      <input type="text" class="form-control meeting-password-input" value="{{ $panel['session']['meeting_password'] ?? '' }}" placeholder="{{ __('admin.auto_generated') }}" readonly>
                      <button type="button" class="btn btn-outline-secondary copy-meeting-password" title="{{ __('admin.copy_meeting_password') }}" style="{{ empty($panel['session']['meeting_password']) ? 'display:none' : '' }}">
                        <i class="ti ti-copy"></i>
                      </button>
                    </div>
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
$(function () {

  const allJudges = @json($judges);
  const allCompanies = @json($companies);
  
  const judgesMap = {};
  allJudges.forEach(judge => {
    judgesMap[judge.id] = judge;
  });
  
  const companiesMap = {};
  allCompanies.forEach(company => {
    companiesMap[company.id] = company;
  });
  
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
    // Exclude disabled (read-only, off-step) options from select-all/deselect-all.
    const allOpts = $select.find('option:not(:disabled)').map((_, el) => $(el).val()).get();
    const curr = ($select.val() || []).filter(v => allOpts.includes(v));
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
    // A fresh panel carries no off-step references: drop cloned read-only options
    // and their preservation hidden inputs.
    $clone.find('option[data-moved="1"]').remove();
    $clone.find('input[type="hidden"][name*="[companies][]"]').remove();
    $clone.find('select, input[type="text"], input[type="url"]').val('');
    $clone.find('.select-all-toggle').text('{{ __("admin.select_all") }}');
    
    $clone.find('.accordion-button').addClass('collapsed').attr('data-bs-target', `#panel-${idx}`).html(`<i class="ti ti-user-check me-2"></i> @lang('admin.screening.panel') #${idx}`);
    $clone.find('.accordion-collapse').removeClass('show').attr('id', `panel-${idx}`);
    $clone.find('.session-toggle').prop('checked', false).attr('id', `session-toggle-${idx}`);
    $clone.find('.session-inputs').hide();
    $clone.find('.meeting-link-input, .meeting-password-input').val('');
    $clone.find('.copy-meeting-link, .copy-meeting-password').hide();
    
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
  
      return;
    }
  
    fetch($(this).attr('action'), {
      method: 'POST', body: new FormData(this),
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(d => {
      if (!d.success) {
        releaseFailureAlert({
          title: '{{ __('admin.error') }}',
          message: d.message || '{{ __('admin.unexpected_error') }}' 
        });
  
        return;
      }
  
      releaseSuccessAlert({
        title: '{{ __('admin.success') }}',
        message: d?.message || '{{ __('admin.Saved_successfully') }}',
      });
  
      if (d.data) {
        updateStepAssignees(d.data || []);

        // Update meeting link/password fields from auto-generated data
        (d.data || []).forEach((panel, idx) => {
          const $panel = $('.accordion-item').eq(idx);
          if (panel.meeting_link) {
            $panel.find('.meeting-link-input').val(panel.meeting_link);
            $panel.find('.copy-meeting-link').show();
          }
          if (panel.meeting_password) {
            $panel.find('.meeting-password-input').val(panel.meeting_password);
            $panel.find('.copy-meeting-password').show();
          }
        });
      }
    })
    .catch(e => releaseFailureAlert({ title: '{{ __('admin.error') }}', message: e?.message || '{{ __('admin.unexpected_error') }}' }));
  });
  
  const maxVisible = 10;
  const locale = '{{ $l ?? "en" }}';

  const renderBadge = (id, name, imgUrl, href, iconClass) => `
    <a href="${href}" class="badge text-dark bg-card border px-3 py-2 rounded-pill d-flex align-items-center">
      <div class="avatar avatar-xs me-2">
        ${imgUrl
          ? `<img src="${imgUrl}" class="rounded-circle object-fit-cover">`
          : `<div class="avatar-initial rounded-circle bg-label-secondary"><i class="${iconClass} ti-xs"></i></div>`
        }
      </div>
      ${name}
    </a>`;

  const renderOverflowBadge = (hiddenNames, count) => {
    const tooltip = hiddenNames.join('\n');
    return `<span class="badge bg-label-primary px-3 py-2 rounded-pill fw-semibold" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-wide" title="${tooltip}">+${count}</span>`;
  };

  const updateStepAssignees = (newAssignees) => {
    const assignedJudgesContainer = $('#assigned-judges');
    const assignedCompaniesContainer = $('#assigned-companies');

    assignedJudgesContainer.empty();
    assignedCompaniesContainer.empty();

    const allJudgeIds = [...new Set()];
    const allCompanyIds = [...new Set()];

    newAssignees.forEach(panel => {
      (panel.judges || []).forEach(j => { const id = Number(j); if (!allJudgeIds.includes(id)) allJudgeIds.push(id); });
      (panel.companies || []).forEach(c => { const id = Number(c); if (!allCompanyIds.includes(id)) allCompanyIds.push(id); });
    });

    // Judges: show up to maxVisible, then +n
    const visibleJudges = allJudgeIds.slice(0, maxVisible);
    const hiddenJudges = allJudgeIds.slice(maxVisible);

    visibleJudges.forEach(judgeId => {
      const judge = judgesMap[judgeId];
      if (!judge) return;
      const name = (judge['first_name_' + locale] || judge.first_name_en) + ' ' + (judge['last_name_' + locale] || judge.last_name_en);
      assignedJudgesContainer.append(renderBadge(judge.id, name, judge.img_url, `/dashboard/users/${judge.id}/edit`, 'ti ti-user'));
    });

    if (hiddenJudges.length > 0) {
      const names = hiddenJudges.map(id => {
        const j = judgesMap[id];
        return j ? ((j['first_name_' + locale] || j.first_name_en) + ' ' + (j['last_name_' + locale] || j.last_name_en)) : '';
      }).filter(Boolean);
      assignedJudgesContainer.append(renderOverflowBadge(names, hiddenJudges.length));
    }

    // Companies: show up to maxVisible, then +n
    const visibleCompanies = allCompanyIds.slice(0, maxVisible);
    const hiddenCompanies = allCompanyIds.slice(maxVisible);

    visibleCompanies.forEach(companyId => {
      const company = companiesMap[companyId];
      if (!company) return;
      const name = company['name_' + locale] || company.name_en;
      assignedCompaniesContainer.append(renderBadge(company.id, name, company.logo, `/dashboard/companyProfile/${company.id}`, 'ti ti-building-skyscraper'));
    });

    if (hiddenCompanies.length > 0) {
      const names = hiddenCompanies.map(id => {
        const c = companiesMap[id];
        return c ? (c['name_' + locale] || c.name_en) : '';
      }).filter(Boolean);
      assignedCompaniesContainer.append(renderOverflowBadge(names, hiddenCompanies.length));
    }

    // Reinitialize tooltips for dynamically added elements
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
  };
  
  $(document).on('input', '.session-inputs input', function() {
    $(this).removeClass('is-invalid');
    const nameAttr = $(this).attr('name');
    if (nameAttr) {
      const match = nameAttr.match(/\[([^\]]+)\]$/);
      if (match) {
        $(this).closest('.accordion-item').find(`.error-${match[1]}`).text('');
      }
    }
  });

  // Copy Meeting Link
  $(document).on('click', '.copy-meeting-link', function() {
    const text = $(this).closest('.input-group').find('.meeting-link-input').val();
    if (text) {
      navigator.clipboard.writeText(text).then(() => {
        releaseSuccessAlert({ title: '{{ __("admin.success") }}', message: '{{ __("admin.copied_to_clipboard") }}' });
      });
    }
  });

  // Copy Meeting Password
  $(document).on('click', '.copy-meeting-password', function() {
    const text = $(this).closest('.input-group').find('.meeting-password-input').val();
    if (text) {
      navigator.clipboard.writeText(text).then(() => {
        releaseSuccessAlert({ title: '{{ __("admin.success") }}', message: '{{ __("admin.copied_to_clipboard") }}' });
      });
    }
  });
});
</script>
@endpush
