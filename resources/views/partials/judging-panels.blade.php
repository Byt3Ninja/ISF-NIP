<style>
  .companies-picker-popover {
    --bs-popover-max-width: none;
    max-width: none;
  }

  .companies-picker-popover .popover-body {
    padding: 0;
  }
</style>

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
      <input type="hidden" name="screening_type" value="{{ $screening_type }}">

      @php
        $l = app()->getLocale();
        // Off-step references: startups already saved into a panel that have since
        // moved to another step. Rendered as disabled (read-only) options so they
        // remain visible in their panel but cannot be added/removed until they
        // return to this step. Keyed by id for quick lookup.
        $offStepCompanies = ($offStepCompanies ?? collect());
        $offStepById = collect($offStepCompanies)->keyBy('id');
        $offStepIds = $offStepById->keys()->map(fn($i) => (int) $i)->all();
      @endphp

      <div class="accordion mb-4" id="panels-container">
        @foreach($judgingPanels as $index => $panel)
          <div class="accordion-item border" data-index="{{ $index }}">
            <h2 class="accordion-header d-flex align-items-center py-3">
              <button class="accordion-button flex-grow-1 {{ $index === 0 ? '' : 'collapsed' }}" type="button"
                data-bs-toggle="collapse" data-bs-target="#panel-{{ $index + 1 }}">
                <i class="ti ti-user-check me-2"></i>
                {{ __('admin.screening.panel') }} #{{ $index + 1 }}
              </button>
              <button type="button"
                class="btn btn-icon btn-sm btn-outline-danger ms-2 me-4 remove-panel {{ $index === 0 && count($judgingPanels) === 1 ? 'd-none' : '' }}"
                title="{{ __('admin.delete') }}">
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
                      <a href="javascript:void(0);"
                        class="select-all-toggle small fw-medium">{{ __('admin.select_all') }}</a>
                    </div>
                    <select name="panels[{{ $index }}][judges][]" class="form-select select2" multiple
                      data-placeholder="{{ __('cycles.Select Judges') }}">
                      @foreach($judges as $judge)
                        <option value="{{ $judge->id }}" @selected(in_array($judge->id, (array) $panel['judges']))>
                          {{ $judge->{'first_name_' . $l} }} {{ $judge->{'last_name_' . $l} }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <!-- Companies -->
                  <div class="col-md-6 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <label class="form-label fw-semibold mb-0">{{ __('admin.Startups') }}</label>
                      <a href="javascript:void(0);"
                        class="select-all-toggle small fw-medium">{{ __('admin.select_all') }}</a>
                    </div>
                    @php
                      // Off-step companies saved in THIS panel (read-only references).
                      $panelOffStepIds = collect($panel['companies'] ?? [])->map(fn($i) => (int) $i)
                        ->filter(fn($i) => in_array($i, $offStepIds, true))->unique()->values();
                      // Companies available at this step that are selected in this panel.
                      $availableCompanyIds = collect($companies)->pluck('id')->map(fn($x) => (int) $x)->all();
                      $panelSelectedIds = collect($panel['companies'] ?? [])->map(fn($i) => (int) $i)
                        ->filter(fn($i) => in_array($i, $availableCompanyIds, true))->values()->all();
                    @endphp
                    <button type="button"
                      class="btn btn-outline-primary d-flex align-items-center justify-content-between w-100 companies-picker-trigger"
                      data-panel-index="{{ $index }}" data-off-step='{{ json_encode($panelOffStepIds->values()->all()) }}'
                      data-selected='{{ json_encode($panelSelectedIds) }}'>
                      <span class="companies-summary text-truncate me-2">{{ __('admin.select_startups') }}</span>
                      <i class="ti ti-chevron-down"></i>
                    </button>
                    {{-- Disabled options are not submitted; preserve off-step assignments explicitly --}}
                    @foreach($panelOffStepIds as $offId)
                      <input type="hidden" name="panels[{{ $index }}][companies][]" value="{{ $offId }}">
                    @endforeach
                    {{-- JS keeps selected available companies in sync here for submission --}}
                    <div class="companies-hidden-container d-none" data-panel-index="{{ $index }}"></div>
                  </div>
                </div>

                <hr class="my-4">

                <!-- Session -->
                <div class="form-check form-switch mb-3">
                  <input class="form-check-input session-toggle" type="checkbox" id="session-toggle-{{ $index + 1 }}" {{ (!empty($panel['session']) && $panel['session']['checked'] == true) ? 'checked' : '' }}>
                  <label class="form-check-label fw-semibold">{{ __('admin.screening.schedule_session') }}</label>
                </div>

                <div class="session-inputs border rounded p-4"
                  style="display: {{ !empty($panel['session']) ? 'block' : 'none' }}">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">{{ __('admin.Session_date') }}</label>
                      <input type="text" class="form-control session-date" name="panels[{{ $index }}][session_date]"
                        value="{{ $panel['session']['date'] ?? '' }}" placeholder="YYYY-MM-DD">
                      <div class="invalid-feedback error-session_date"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">{{ __('admin.Start Time') }}</label>
                      <input type="text" class="form-control session-time" name="panels[{{ $index }}][start_time]"
                        value="{{ $panel['session']['start_time'] ?? '' }}" placeholder="HH:MM">
                      <div class="invalid-feedback error-start_time"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">{{ __('admin.End Time') }}</label>
                      <input type="text" class="form-control session-time" name="panels[{{ $index }}][end_time]"
                        value="{{ $panel['session']['end_time'] ?? '' }}" placeholder="HH:MM">
                      <div class="invalid-feedback error-end_time"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">{{ __('admin.Meeting Link') }}</label>
                      <div class="input-group">
                        <input type="url" class="form-control meeting-link-input"
                          value="{{ $panel['session']['meeting_link'] ?? '' }}"
                          placeholder="{{ __('admin.auto_generated') }}" readonly>
                        <button type="button" class="btn btn-outline-secondary copy-meeting-link"
                          title="{{ __('admin.copy_meeting_link') }}"
                          style="{{ empty($panel['session']['meeting_link']) ? 'display:none' : '' }}">
                          <i class="ti ti-copy"></i>
                        </button>
                      </div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">{{ __('admin.meeting_password') }}</label>
                      <div class="input-group">
                        <input type="text" class="form-control meeting-password-input"
                          value="{{ $panel['session']['meeting_password'] ?? '' }}"
                          placeholder="{{ __('admin.auto_generated') }}" readonly>
                        <button type="button" class="btn btn-outline-secondary copy-meeting-password"
                          title="{{ __('admin.copy_meeting_password') }}"
                          style="{{ empty($panel['session']['meeting_password']) ? 'display:none' : '' }}">
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

      const offStepCompanies = @json($offStepCompanies);
      const offStepMap = {};
      offStepCompanies.forEach(company => {
        offStepMap[company.id] = company;
      });

      const isValidUrl = (str) => {
        try { return new URL(str).protocol.startsWith('http'); }
        catch { return false; }
      };

      const esc = (s) => String(s ?? '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));

      const rangeLabels = {
        from: '{{ __("admin.From") }}',
        to: '{{ __("admin.To") }}',
        apply: '{{ __("admin.Apply") }}',
        title: '{{ __("admin.auto_select_range") }}',
        clear: '{{ __("admin.clear_range") }}'
      };

      const parseIdList = (raw) => raw ? raw.replace(/[\[\]"]/g, '').split(',').filter(Boolean).map(s => String(s.trim())) : [];

      const getCompaniesTrigger = (panelIndex) => $(`.companies-picker-trigger[data-panel-index="${panelIndex}"]`).first();

      // True when the given company id is already selected in a different panel.
      const isSelectedInOtherPanel = (panelIndex, companyId) => {
        return $('.companies-picker-trigger').toArray().some((el) => {
          const $other = $(el);
          if (String($other.data('panel-index')) === String(panelIndex)) return false;
          return ($other.data('selected') || new Set()).has(companyId);
        });
      };

      const initSelect2 = ($select) => {
        $select.select2({
          placeholder: $select.data('placeholder'),
          allowClear: true,
          width: '100%',
          dropdownParent: $select.parent()
        });
      };

      // ---- Companies popover picker (checkboxes + auto select range) ----

      const buildCompaniesRows = ($trig) => {
        const offStepIds = $trig.data('off-step') || [];
        const selected = $trig.data('selected') || new Set();
        const filter = $trig.data('range-filter');

        let rows = '';
        allCompanies.forEach(c => {
          const id = String(c.id);
          if (filter && !filter.has(id)) return;
          const checked = selected.has(id) ? 'checked' : '';
          rows += `<li class="list-group-item px-3 py-2"><label class="d-flex align-items-center gap-2 mb-0 w-100 cursor-pointer"><input type="checkbox" class="form-check-input company-check m-0" value="${id}" ${checked}> <span class="text-truncate">${esc(c['name_' + locale] || c.name_en)}</span></label></li>`;
        });

        offStepIds.forEach(offId => {
          const off = offStepMap[offId];
          if (!off) return;
          rows += `<li class="list-group-item px-3 py-2 bg-lighter"><label class="d-flex align-items-center gap-2 mb-0 w-100 text-muted"><input type="checkbox" class="form-check-input m-0" disabled checked> <span class="text-truncate">${esc(off['name_' + locale] || off.name_en)} — {{ __('admin.moved_off_step') }}</span></label></li>`;
        });

        if (!rows) rows = `<li class="list-group-item text-muted text-center py-4">{{ __('admin.select_startups') }}</li>`;
        return rows;
      };

      const buildCompaniesPopoverHtml = ($trig) => {
        const rangeValues = $trig.data('range-values') || {};
        const filter = $trig.data('range-filter');
        const total = allCompanies.length;
        return `
        <div class="companies-popover" data-panel-index="${$trig.data('panel-index')}">
          <div class="auto-range p-3 border-bottom bg-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold small text-uppercase">${rangeLabels.title}</span>
              <a href="javascript:void(0);" class="clear-range small fw-medium ${filter ? '' : 'd-none'}">${rangeLabels.clear}</a>
            </div>
            <div class="d-flex align-items-end flex-wrap gap-2">
              <div class="flex-grow-1" style="min-width: 70px;">
                <label class="form-label mb-1 small text-muted">${rangeLabels.from}</label>
                <input type="number" class="form-control form-control-sm range-from" min="1" max="${total}" inputmode="numeric" placeholder="${rangeLabels.from}" value="${rangeValues.from ?? ''}">
              </div>
              <div class="flex-grow-1" style="min-width: 70px;">
                <label class="form-label mb-1 small text-muted">${rangeLabels.to}</label>
                <input type="number" class="form-control form-control-sm range-to" min="1" max="${total}" inputmode="numeric" placeholder="${rangeLabels.to}" value="${rangeValues.to ?? ''}">
              </div>
              <button type="button" class="btn btn-primary btn-sm apply-range mb-0">${rangeLabels.apply}</button>
            </div>
            <p class="range-error text-danger small mt-2 mb-0 d-none"></p>
          </div>
          <ul class="list-group list-group-flush companies-list" style="max-height: 280px; overflow-y: auto;">${buildCompaniesRows($trig)}</ul>
        </div>`;
      };

      const syncCompaniesState = ($trig) => {
        const panelIndex = $trig.data('panel-index');
        const selected = $trig.data('selected') || new Set();
        const $container = $(`.companies-hidden-container[data-panel-index="${panelIndex}"]`);
        $container.empty();
        selected.forEach(id => {
          $container.append(`<input type="hidden" name="panels[${panelIndex}][companies][]" value="${id}">`);
        });

        const count = selected.size;
        const total = allCompanies.length;
        const $summary = $trig.find('.companies-summary');
        $summary.text(count > 0 ? count + ' {{ __("admin.Selected") }}' : '{{ __("admin.select_startups") }}');

        const $link = $trig.closest('.col-md-6').find('.select-all-toggle');
        $link.text(count === total && total > 0 ? '{{ __("admin.deselect_all") }}' : '{{ __("admin.select_all") }}');
      };

      const refreshCompaniesPopover = ($trig) => {
        const panelIndex = $trig.data('panel-index');
        const $pop = $(`.companies-popover[data-panel-index="${panelIndex}"]`);
        if (!$pop.length) return;
        $pop.find('.companies-list').html(buildCompaniesRows($trig));
        $pop.find('.clear-range').toggleClass('d-none', !$trig.data('range-filter'));
      };

      const toggleAllCompanies = ($trig) => {
        const filter = $trig.data('range-filter');
        let ids = allCompanies.map(c => String(c.id));
        if (filter) ids = ids.filter(id => filter.has(id));
        const selected = $trig.data('selected') || new Set();
        const allOn = ids.length > 0 && ids.every(id => selected.has(id));
        if (allOn) ids.forEach(id => selected.delete(id));
        else ids.forEach(id => selected.add(id));
        $trig.data('selected', selected);
        syncCompaniesState($trig);
        refreshCompaniesPopover($trig);
      };

      const initCompaniesState = ($trig) => {
        const selected = new Set(parseIdList($trig.attr('data-selected')));
        $trig.data('selected', selected);
        syncCompaniesState($trig);
      };

      const initCompaniesPopover = ($trig) => {
        if (bootstrap.Popover.getInstance($trig[0])) return;
        new bootstrap.Popover($trig[0], {
          html: true,
          sanitize: false,
          placement: 'bottom',
          trigger: 'click',
          container: 'body',
          customClass: 'companies-picker-popover',
          content: () => buildCompaniesPopoverHtml($trig)
        });
        // Width: at least the trigger's own width, but never smaller than 700px so the
        // list/search area has enough room. Must run on 'inserted' (after the tip is in
        // the DOM) rather than 'show', otherwise Popper computes/overrides its own width
        // before ours takes effect. We also nudge Popper to recompute position afterwards,
        // since it may have already measured the (smaller) pre-resize dimensions.
        $trig.on('inserted.bs.popover', () => {
          const instance = bootstrap.Popover.getInstance($trig[0]);
          if (!instance) return;
          const tip = instance._getTipElement();
          const width = Math.max($trig.outerWidth(), 530);
          $(tip).css({ width, maxWidth: 'calc(100vw - 2rem)' });
          if (instance._popper && typeof instance._popper.update === 'function') {
            instance._popper.update();
          }
        });
      };

      // Close the companies picker when clicking outside the trigger and popover.
      // Uses aria-describedby (which Bootstrap sets on the trigger while its popover is
      // shown) rather than private instance internals, so this reliably finds only
      // popovers that are actually open right now.
      $(document).on('click.companiesPickerOutside', (e) => {
        $('.companies-picker-trigger').each(function () {
          const tipId = this.getAttribute('aria-describedby');
          if (!tipId) return; // this trigger's popover isn't open
          const tip = document.getElementById(tipId);
          if (!tip) return;
          if ($(e.target).closest(this).length || $(e.target).closest(tip).length) return;
          const instance = bootstrap.Popover.getInstance(this);
          if (instance) instance.hide();
        });
      });

      const initPlugins = (el) => {
        $(el).find('.session-date').flatpickr({ dateFormat: 'Y-m-d', minDate: 'today' });
        $(el).find('.session-time').flatpickr({ enableTime: true, noCalendar: true, dateFormat: 'H:i' });
        $(el).find('.select2').each(function () {
          initSelect2($(this));
        });
        $(el).find('.companies-picker-trigger').each(function () {
          const $trig = $(this);
          initCompaniesState($trig);
          initCompaniesPopover($trig);
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
          $panel.find('.companies-picker-trigger, .companies-hidden-container').attr('data-panel-index', i);
          $panel.find('[name*="panels["]').each(function () {
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

      $(document).on('click', '.select-all-toggle', function () {
        const $col = $(this).closest('.col-md-6');
        const $trig = $col.find('.companies-picker-trigger');
        if ($trig.length) {
          toggleAllCompanies($trig);
          return;
        }
        const $select = $col.find('select');
        // Exclude disabled (read-only, off-step) options from select-all/deselect-all.
        const allOpts = $select.find('option:not(:disabled)').map((_, el) => $(el).val()).get();
        const curr = ($select.val() || []).filter(v => allOpts.includes(v));
        $select.val(curr.length === allOpts.length ? null : allOpts).trigger('change');
      });

      // Companies popover: keep selection state in sync when a checkbox is toggled.
      // If the startup is already assigned to another panel, ask for confirmation
      // before allowing the duplicate assignment.
      $(document).on('change', '.company-check', async function () {
        const panelIndex = $(this).closest('.companies-popover').data('panel-index');
        const $trig = getCompaniesTrigger(panelIndex);
        if (!$trig.length) return;
        const selected = $trig.data('selected') || new Set();

        if (this.checked) {
          if (isSelectedInOtherPanel(panelIndex, this.value)) {
            const { isConfirmed } = await Swal.fire({
              icon: 'warning',
              text: '{{ __("admin.company_assigned_other_panel") }}',
              showCancelButton: true,
              confirmButtonText: '{{ __("admin.Confirm") }}',
              cancelButtonText: '{{ __("admin.Cancel") }}',
              customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-label-secondary' },
              buttonsStyling: false
            });
            if (!isConfirmed) {
              this.checked = false;
              return;
            }
          }
          selected.add(this.value);
        } else {
          selected.delete(this.value);
        }
        $trig.data('selected', selected);
        syncCompaniesState($trig);
      });

      // Auto Select Range: numbers only, reject anything else while typing.
      $(document).on('input', '.range-from, .range-to', function () {
        this.value = this.value.replace(/[^\d]/g, '');
        const $wrap = $(this).closest('.auto-range');
        $wrap.find('.range-error').text('').addClass('d-none');
        $wrap.find('.range-from, .range-to').removeClass('is-invalid');
      });

      $(document).on('click', '.apply-range', function () {
        const $pop = $(this).closest('.companies-popover');
        const $trig = getCompaniesTrigger($pop.data('panel-index'));
        if (!$trig.length) return;
        const $error = $pop.find('.range-error');
        const $from = $pop.find('.range-from');
        const $to = $pop.find('.range-to');

        const showError = (msg, target) => {
          $error.text(msg).removeClass('d-none');
          $from.removeClass('is-invalid');
          $to.removeClass('is-invalid');
          (target || $from).addClass('is-invalid');
        };

        const fromStr = String($from.val() ?? '').trim();
        const toStr = String($to.val() ?? '').trim();

        if (!/^\d*$/.test(fromStr) || !/^\d*$/.test(toStr)) {
          showError('{{ __("admin.range_numbers_only") }}');
          return;
        }

        if (fromStr === '' && toStr === '') {
          showError('{{ __("admin.range_required") }}');
          return;
        }

        const selectable = allCompanies.map(c => String(c.id));
        const total = selectable.length;
        const from = fromStr === '' ? 1 : parseInt(fromStr, 10);
        const to = toStr === '' ? total : parseInt(toStr, 10);

        if (from < 1 || from > total || to < 1 || to > total) {
          showError('{{ __("admin.range_outside") }}', to > total ? $to : $from);
          return;
        }

        if (from > to) {
          showError('{{ __("admin.range_invalid") }}', $from);
          return;
        }

        const matching = selectable.slice(from - 1, to);
        $trig.data('selected', new Set(matching));
        $trig.data('range-filter', new Set(matching));
        $trig.data('range-values', { from, to });
        syncCompaniesState($trig);
        refreshCompaniesPopover($trig);

        $error.text('').addClass('d-none');
        $from.removeClass('is-invalid');
        $to.removeClass('is-invalid');
      });

      $(document).on('click', '.clear-range', function () {
        const $pop = $(this).closest('.companies-popover');
        const $trig = getCompaniesTrigger($pop.data('panel-index'));
        if (!$trig.length) return;
        $trig.removeData('range-filter').removeData('range-values');
        refreshCompaniesPopover($trig);
        $(this).addClass('d-none');
        $pop.find('.range-from, .range-to').val('');
        $pop.find('.range-error').text('').addClass('d-none');
        $pop.find('.range-from, .range-to').removeClass('is-invalid');
      });

      $(document).on('change', '.select2', function () { updateSelectAllText($(this)); });

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
        $clone.find('select').removeData('range-filter');
        // Reset the companies picker to a fresh (empty) state.
        const $cloneCompanies = $clone.find('.companies-picker-trigger');
        $cloneCompanies.attr('data-panel-index', '0')
          .attr('data-off-step', '[]')
          .attr('data-selected', '[]')
          .removeData('selected')
          .removeData('range-filter')
          .removeData('range-values')
          .removeAttr('aria-describedby');
        const $clonePop = bootstrap.Popover.getInstance($cloneCompanies[0]);
        if ($clonePop) $clonePop.dispose();
        $cloneCompanies.find('.companies-summary').text('{{ __("admin.select_startups") }}');
        $clone.find('.companies-hidden-container').attr('data-panel-index', '0').empty();
        $clone.find('.range-from, .range-to').val('');
        $clone.find('.range-error').text('').addClass('d-none');
        $clone.find('.range-from, .range-to').removeClass('is-invalid');
        $clone.find('.clear-range').addClass('d-none');
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

      $(document).on('click', '.remove-panel', async function () {
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

      $(document).on('change', '.session-toggle', function () {
        $(this).closest('.accordion-body').find('.session-inputs').toggle(this.checked);
      });

      $(document).on('submit', '#judging-panels-form', function (e) {
        e.preventDefault();
        let valid = true;

        $('.session-inputs input').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $('.accordion-item').each(function () {
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

      $(document).on('input', '.session-inputs input', function () {
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
      $(document).on('click', '.copy-meeting-link', function () {
        const text = $(this).closest('.input-group').find('.meeting-link-input').val();
        if (text) {
          navigator.clipboard.writeText(text).then(() => {
            releaseSuccessAlert({ title: '{{ __("admin.success") }}', message: '{{ __("admin.copied_to_clipboard") }}' });
          });
        }
      });

      // Copy Meeting Password
      $(document).on('click', '.copy-meeting-password', function () {
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
