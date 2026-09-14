<style>
  .companies-picker-popover {
    --bs-popover-max-width: none;
    max-width: none;
  }

  .companies-picker-popover .popover-body {
    padding: 0;
  }

  /* The picker trigger is a <button>, so Bootstrap's .is-invalid (which only
     styles form controls) leaves it unmarked. Border it explicitly so the
     mandatory-startups error is visible on the field itself, not just below it. */
  .companies-picker-trigger.is-invalid {
    border-color: var(--bs-danger);
    color: var(--bs-danger);
  }

  /* ------------------------------------------------------------------
     History panels

     This screen mixes two kinds of thing: panels an admin can still act on,
     and panels that merely record what already happened. The design job is to
     make that distinction readable at a glance without the finished record
     shouting louder than the live work.

     The theme's own surfaces do the work. A card sits at --bs-card-bg (white in
     light, a lifted slate in dark) on a page of --bs-body-bg. A finished panel
     therefore RECEDES to the page color instead of being painted over: it reads
     as an inset shelf rather than a grey slab stacked on top of the card.

     Deliberately NOT --bs-secondary-bg: that token is rgba(47,43,61,.12) — a 12%
     dark wash meant for small chips. Across a whole panel it renders as heavy
     grey, which is what made this section outweigh the live panels around it.
     Every value below is a theme variable, so the light/dark stylesheet swap
     recolors it automatically; no hardcoded hex would survive that toggle.
     ------------------------------------------------------------------ */
  .accordion-item.panel-history {
    background-color: var(--bs-body-bg);
    border-color: var(--bs-border-color);
  }

  /* One quiet accent marks the state on the inline edge, so it reads correctly
     in both LTR and RTL without a second rule. */
  .accordion-item.panel-history {
    box-shadow: inset 3px 0 0 0 var(--bs-border-color);
  }

  .accordion-item.panel-history > .accordion-header .accordion-button {
    background-color: transparent;
    color: var(--bs-secondary-color);
  }

  /* The badge is a pill with its clock icon, sized to sit beside the title
     without competing with it. */
  .accordion-item.panel-history .accordion-button .badge {
    display: inline-flex;
    align-items: center;
    background-color: var(--bs-body-bg) !important;
    border: 1px solid var(--bs-border-color);
    border-radius: 50rem;
    padding: .25rem .625rem;
    margin-inline-start: .625rem !important;
    color: var(--bs-secondary-color);
    font-size: .75rem;
    font-weight: 500;
    line-height: 1.2;
  }

  .accordion-item.panel-history .accordion-button .badge i {
    font-size: .875rem;
    line-height: 1;
  }

  /* ------------------------------------------------------------------
     Panel header / body separation

     Every panel — live or history — needs a hairline between the header strip
     and the fields below it. Without one the title floats directly above the
     first label and the two read as a single block, which is what made a long
     list of panels feel undifferentiated.
     ------------------------------------------------------------------ */
  #panels-container .accordion-item > .accordion-collapse > .accordion-body {
    border-top: 1px solid var(--bs-border-color);
    /* Generous top padding so the first row of labels (Judges / Startups) clears
       the divider instead of sitting tight against it. The Bootstrap default
       leaves them cramped once a rule is introduced above. */
    padding: 1.5rem 1.25rem 1.25rem;
  }

  /* Give the panels breathing room from each other so each reads as its own
     card rather than one continuous stack. */
  #panels-container .accordion-item {
    margin-bottom: .75rem;
    border-radius: var(--bs-border-radius);
  }

  #panels-container .accordion-item:last-child {
    margin-bottom: 0;
  }

  /* The header strip is the panel's handle: keep it compact so a collapsed list
     of finished panels stays scannable. */
  #panels-container .accordion-header .accordion-button {
    padding-block: .875rem;
  }

  /* Locked fields sit on the CARD color — lifted just clear of the recessed
     panel so each value still reads as a distinct field. Muting is carried by
     the surrounding surface, not by fading the text: this is a record people
     still need to read. */
  .accordion-item.panel-history fieldset[disabled] .form-control,
  .accordion-item.panel-history fieldset[disabled] .form-select,
  .accordion-item.panel-history fieldset[disabled] .select2-selection {
    background-color: var(--bs-card-bg);
    border-color: var(--bs-border-color);
    color: var(--bs-body-color);
    opacity: 1;
    cursor: default;
  }

  /* Select2 chips: same treatment, kept legible. */
  .accordion-item.panel-history .select2-selection__choice {
    background-color: var(--bs-card-bg) !important;
    border-color: var(--bs-border-color) !important;
    color: var(--bs-body-color) !important;
    opacity: 1;
  }

  /* The startups picker stays clickable so the selection can be READ, so it must
     not wear the flat look of its locked neighbours. */
  .accordion-item.panel-history .companies-picker-trigger[data-readonly="1"] {
    background-color: var(--bs-card-bg);
    border-color: var(--bs-border-color);
    color: var(--bs-body-color);
    opacity: 1;
    cursor: pointer;
  }

  .accordion-item.panel-history .companies-picker-trigger[data-readonly="1"]:hover {
    border-color: var(--bs-primary);
  }

  /* The lock notice is a caption above the record, not a warning banner. It is
     the one place the rule is stated in full, so it stays — but tightly, since a
     boxed alert on every finished panel is what made the list feel heavy. */
  .accordion-item.panel-history .alert {
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    background-color: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: var(--bs-border-radius);
    padding: .75rem 1rem;
    margin-bottom: 1.5rem;
    /* --bs-body-color, not --bs-secondary-color: the latter is the body colour at
       75% opacity, which lands at ~3:1 on the recessed panel background — under
       the 4.5:1 AA floor. This sentence explains WHY the panel is locked, so it
       has to be comfortably readable rather than decoratively faint. */
    color: var(--bs-body-color);
    font-size: .8125rem;
  }

  /* Startups summary: names, not just a count, so the trigger says what it holds. */
  .companies-picker-trigger .companies-summary {
    font-weight: 500;
    text-align: start;
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
          @php
  // A finished panel is History: read-only and preserved as a record of
  // what took place. Collapsed by default so live panels stay in view.
  $isHistory = !empty($panel['is_history']);
  $expanded = $index === 0 && !$isHistory;
          @endphp
          <div class="accordion-item border {{ $isHistory ? 'panel-history' : '' }}" data-index="{{ $index }}"
            @if($isHistory) data-history="1" @endif>
            @if($isHistory)
              {{-- The panel number this history record is stored under. Deleting an
                   earlier panel renumbers the accordion, so position alone cannot
                   identify it; the server matches on this instead. Outside the
                   fieldset so it is still submitted. --}}
              <input type="hidden" name="panels[{{ $index }}][history_panel]"
                value="{{ $panel['panel_number'] ?? $index + 1 }}">
            @endif
            <h2 class="accordion-header d-flex align-items-center py-3">
              <button class="accordion-button flex-grow-1 {{ $expanded ? '' : 'collapsed' }}" type="button"
                data-bs-toggle="collapse" data-bs-target="#panel-{{ $index + 1 }}">
                <i class="ti ti-user-check me-2"></i>
                {{ __('admin.screening.panel') }} #{{ $index + 1 }}
                @if($isHistory)
                  <span class="badge bg-label-secondary ms-2">
                    <i class="ti ti-history me-1"></i>{{ __('admin.screening.panel_history') }}
                  </span>
                @endif
              </button>
              {{-- History panels record a screening that already happened, so they
                   cannot be deleted. --}}
              <button type="button"
                class="btn btn-icon btn-sm btn-outline-danger ms-2 me-4 remove-panel {{ ($index === 0 && count($judgingPanels) === 1) || $isHistory ? 'd-none' : '' }}"
                title="{{ __('admin.delete') }}">
                <i class="ti ti-trash"></i>
              </button>
            </h2>

            <div id="panel-{{ $index + 1 }}" class="accordion-collapse collapse {{ $expanded ? 'show' : '' }}">
              <div class="accordion-body">
                @if($isHistory)
                  <div class="alert alert-secondary d-flex align-items-center" role="alert">
                    <i class="ti ti-lock me-2"></i>
                    <span>{{ __('admin.screening.panel_history_notice') }}</span>
                  </div>
                @endif
                {{-- One fieldset gates the whole panel rather than a `disabled` on
                     each control: a single authoritative switch cannot be applied
                     to some inputs and forgotten on others. The server rebuilds
                     history panels from stored state regardless, since disabled
                     inputs are simply absent from the submission. --}}
                <fieldset @if($isHistory) disabled @endif class="border-0 p-0 m-0">
                <div class="row">
                  <!-- Judges -->
                  <div class="col-md-6 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <label class="form-label fw-semibold mb-0">{{ __('admin.Judgers') }}</label>
                      <a href="javascript:void(0);"
                        class="select-all-toggle small fw-medium {{ $isHistory ? 'd-none' : '' }}">{{ __('admin.select_all') }}</a>
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
                        class="select-all-toggle small fw-medium {{ $isHistory ? 'd-none' : '' }}">{{ __('admin.select_all') }}</a>
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
                      data-selected='{{ json_encode($panelSelectedIds) }}'
                      {{-- The startup list as SAVED, in submission order (off-step hidden
                           inputs render first, then the picker's selections). Compared
                           against the live inputs on submit to detect an add, a removal
                           or a REORDER, any of which reflows interval slots
                           (GV-8892 / GV-8893). --}}
                      data-companies-baseline='{{ json_encode($panelOffStepIds->values()->concat($panelSelectedIds)->all()) }}'>
                      <span class="companies-summary text-truncate me-2">{{ __('admin.select_startups') }}</span>
                      <i class="ti ti-chevron-down"></i>
                    </button>
                    {{-- Disabled options are not submitted; preserve off-step assignments explicitly --}}
                    @foreach($panelOffStepIds as $offId)
                      <input type="hidden" name="panels[{{ $index }}][companies][]" value="{{ $offId }}">
                    @endforeach
                    {{-- JS keeps selected available companies in sync here for submission --}}
                    <div class="companies-hidden-container d-none" data-panel-index="{{ $index }}"></div>
                    {{-- Startups are mandatory; mirrors the server-side rule inline --}}
                    <div class="text-danger small mt-1 error-companies"></div>
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

                  <label class="switch switch-square mb-3">
                    <input type="checkbox" class="switch-input interval-toggle" id="interval-toggle-{{ $index + 1 }}" {{ !empty($panel['session']['interval_checked']) ? 'checked' : '' }}>
                    <span class="switch-toggle-slider">
                      <span class="switch-on"></span>
                      <span class="switch-off"></span>
                    </span>
                    <span class="switch-label">{{ __('admin.interval') }}</span>
                  </label>
                  
                  <div class="interval-inputs mb-3"
                    style="display: {{ !empty($panel['session']['interval_checked']) ? 'block' : 'none' }}">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('admin.interval_minutes') }}</label>
                        <input type="number" class="form-control interval-duration" name="panels[{{ $index }}][interval]"
                          value="{{ $panel['session']['interval'] ?? '' }}" min="1" max="99" maxlength="2"
                          inputmode="numeric" placeholder="30">
                        <div class="invalid-feedback error-interval"></div>
                      </div>
                    </div>
                  </div>

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
                        value="{{ $panel['session']['end_time'] ?? '' }}" placeholder="HH:MM"
                        {{ !empty($panel['session']['interval_checked']) ? 'disabled' : '' }}>
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
                </fieldset>

                {{-- Notify now (per panel). One switch covers judges AND startups,
                     mail AND in-app notification: separate toggles per audience and
                     per channel would be configuration nobody sets correctly.
                     Default OFF — saving never contacts anyone unless asked. On a
                     history panel it is hidden entirely, since a finished meeting
                     must not re-notify. --}}
                @unless($isHistory)
                  <div class="form-check form-switch mt-3">
                    <input class="form-check-input notify-toggle" type="checkbox"
                      id="notify-toggle-{{ $index + 1 }}"
                      name="panels[{{ $index }}][notify]" value="1" checked>
                    <label class="form-check-label" for="notify-toggle-{{ $index + 1 }}">
                      {{ __('admin.screening.notify_now') }}
                      <span class="text-muted d-block small">{{ __('admin.screening.notify_now_hint') }}</span>
                    </label>
                  </div>
                @endunless
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-primary" id="panels-submit-btn">
          <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
          <span class="submit-label">{{ __('admin.submit') }}</span>
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
  <script>
    $(function () {

      // Declared up here, not beside the badge helpers further down: initPlugins()
      // runs during setup and calls companyDisplayName()/syncCompaniesState(),
      // which read `locale`. A `const` declared after that call is still in its
      // temporal dead zone, so reaching it throws
      // "ReferenceError: Cannot access 'locale' before initialization".
      const locale = '{{ $l ?? "en" }}';

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

      // ---- Interval scheduling helpers: auto-calculated end time ----

      const parseTime = (t) => {
        const m = /^(\d{1,2}):(\d{2})(?::\d{2})?$/.exec(String(t || '').trim());
        if (!m) return null;
        const h = parseInt(m[1], 10);
        const min = parseInt(m[2], 10);
        if (h > 23 || min > 59) return null;
        return h * 60 + min;
      };

      const minutesToTime = (mins) => {
        const total = ((Math.round(mins) % 1440) + 1440) % 1440;
        return `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`;
      };

      // Number of startups submitted for a panel. Two DISJOINT groups make up the
      // total, and they must not be conflated:
      //   1. picker selections, held in the trigger's `data-selected` set and
      //      mirrored into .companies-hidden-container by syncCompaniesState()
      //   2. off-step startups, rendered by blade as hidden inputs OUTSIDE that
      //      container (their options are disabled, so they are preserved here)
      // Counting the container's inputs on top of `data-selected` would count
      // every picker selection twice, doubling the calculated end time.
      const countPanelCompanies = ($panel) => {
        const $item = $panel.closest('.accordion-item').length ? $panel.closest('.accordion-item') : $panel;
        const $trig = $item.find('.companies-picker-trigger');
        const selected = $trig.length ? ($trig.data('selected') || new Set()).size : 0;
        const offStep = $item.find('input[type="hidden"][name*="[companies][]"]')
          .not('.companies-hidden-container input').length;
        return selected + offStep;
      };

      // The panel books ONE meeting that every startup joins in sequence, so the
      // end time must cover ALL of their slots: start + (count * interval).
      // Mirrors the server-side derivation in ScreeningController@addJudgingPanels
      // so the previewed end time equals the one that is actually saved.
      const computeEndTime = ($panel) => {
        const $end = $panel.find('input[name*="end_time"]');
        const start = parseTime($panel.find('input[name*="start_time"]').val());
        const interval = parseInt($panel.find('input[name*="interval"]').val(), 10);
        if (start === null || !interval || interval <= 0) {
          $end.val('');
          return;
        }
        const slots = Math.max(countPanelCompanies($panel), 1);
        $end.val(minutesToTime(start + (slots * interval)));
      };

      const rangeLabels = {
        from: '{{ __("admin.From") }}',
        to: '{{ __("admin.To") }}',
        apply: '{{ __("admin.Apply") }}',
        title: '{{ __("admin.auto_select_range") }}',
        clear: '{{ __("admin.clear_range") }}',
        search: '{{ __("admin.search_startups") }}',
        noMatch: '{{ __("admin.no_startups_match_search") }}'
      };

      // The single source of truth for a startup's sequential number.
      //
      // The number a user reads next to a startup and the number Auto Select Range
      // consumes MUST be the same axis, otherwise applying a range twice selects
      // different startups than the list shows. Both therefore derive from this
      // one ordered list of selectable companies. Search filters what is *displayed*
      // but never renumbers, so a startup keeps number 7 whether or not a search is
      // active. Off-step (read-only) startups are excluded entirely: they are not
      // selectable and must not consume range positions.
      const orderedCompanyIds = () => allCompanies.map(c => String(c.id));

      const companyDisplayName = (c) => c['name_' + locale] || c.name_en;

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

      // Confirm a BULK selection that would duplicate startups already sitting in
      // another panel (Select All / Select Startup Range).
      //
      // The single-checkbox path asks per startup, but a bulk action can touch
      // dozens at once, so asking once and listing them keeps it usable. Returns
      // the ids to actually select: Confirm keeps every duplicate, Cancel drops
      // ONLY the duplicates and keeps the rest of the action's selection — the
      // spec's "Cancel removes the startup from the current panel" applied per
      // startup rather than abandoning the whole action.
      const confirmBulkDuplicates = async (panelIndex, ids) => {
        const duplicates = ids.filter(id => isSelectedInOtherPanel(panelIndex, id));
        if (!duplicates.length) return ids;

        const names = duplicates
          .map(id => companiesMap[id] ? companyDisplayName(companiesMap[id]) : id)
          .map(n => `<li class="text-start">${esc(n)}</li>`)
          .join('');

        const { isConfirmed } = await Swal.fire({
          icon: 'warning',
          html: `<p class="mb-2">{{ __("admin.companies_assigned_other_panel") }}</p><ul class="mb-0">${names}</ul>`,
          showCancelButton: true,
          confirmButtonText: '{{ __("admin.Confirm") }}',
          cancelButtonText: '{{ __("admin.Cancel") }}',
          customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-label-secondary' },
          buttonsStyling: false
        });

        return isConfirmed ? ids : ids.filter(id => !duplicates.includes(id));
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
        const search = String($trig.data('search') || '').trim().toLowerCase();

        let rows = '';
        let matches = 0;

        // Number comes from the startup's position in the FULL ordered list, not
        // from its position among the search results, so numbers stay stable while
        // the user types and always agree with what Auto Select Range acts on.
        allCompanies.forEach((c, i) => {
          const id = String(c.id);
          const name = companyDisplayName(c);
          if (search && !String(name).toLowerCase().includes(search)) return;
          matches++;
          const checked = selected.has(id) ? 'checked' : '';
          // A history panel's picker opens so the selection can be READ, but its
          // checkboxes must not change anything.
          const ro = $trig.attr('data-readonly') === '1' ? 'disabled' : '';
          rows += `<li class="list-group-item px-3 py-2"><label class="d-flex align-items-center gap-2 mb-0 w-100 ${ro ? '' : 'cursor-pointer'}"><input type="checkbox" class="form-check-input company-check m-0" value="${id}" ${checked} ${ro}> <span class="text-muted small company-number" style="min-width: 1.75rem;">${i + 1}.</span> <span class="text-truncate">${esc(name)}</span></label></li>`;
        });

        if (search && matches === 0) {
          return `<li class="list-group-item text-muted text-center py-4">${esc(rangeLabels.noMatch)}</li>`;
        }

        // Off-step startups are read-only references and carry no sequential
        // number, so they never shift the numbering of selectable startups.
        // They are hidden while searching, since they cannot be acted on.
        if (!search) {
          offStepIds.forEach(offId => {
            const off = offStepMap[offId];
            if (!off) return;
            rows += `<li class="list-group-item px-3 py-2 bg-lighter"><label class="d-flex align-items-center gap-2 mb-0 w-100 text-muted"><input type="checkbox" class="form-check-input m-0" disabled checked> <span class="text-truncate">${esc(companyDisplayName(off))} — {{ __('admin.moved_off_step') }}</span></label></li>`;
          });
        }

        if (!rows) rows = `<li class="list-group-item text-muted text-center py-4">{{ __('admin.select_startups') }}</li>`;
        return rows;
      };

      const buildCompaniesPopoverHtml = ($trig) => {
        const rangeValues = $trig.data('range-values') || {};
        const hasRange = rangeValues.from != null || rangeValues.to != null;
        const total = allCompanies.length;
        const search = String($trig.data('search') || '');
        return `
        <div class="companies-popover" data-panel-index="${$trig.data('panel-index')}">
          <div class="p-3 border-bottom">
            <div class="input-group input-group-sm">
              <span class="input-group-text"><i class="ti ti-search"></i></span>
              <input type="text" class="form-control companies-search" placeholder="${esc(rangeLabels.search)}" value="${esc(search)}" autocomplete="off">
            </div>
          </div>
          <div class="auto-range p-3 border-bottom bg-card ${$trig.attr('data-readonly') === '1' ? 'd-none' : ''}">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold small text-uppercase">${rangeLabels.title}</span>
              <a href="javascript:void(0);" class="clear-range small fw-medium ${hasRange ? '' : 'd-none'}">${rangeLabels.clear}</a>
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

        // Name the startups rather than only counting them. "3 Selected" forces a
        // click to learn WHICH three, and on a history panel — where the picker is
        // read-only — the trigger is the primary way the selection is read at all.
        // Long lists fall back to a count so the button cannot grow unbounded.
        const count = selected.size;
        const $summary = $trig.find('.companies-summary');
        if (count === 0) {
          $summary.text('{{ __("admin.select_startups") }}');
        } else {
          const names = [...selected]
            .map(id => companiesMap[id] ? companyDisplayName(companiesMap[id]) : null)
            .filter(Boolean);
          const sep = locale === 'ar' ? '، ' : ', ';
          // Names are shown only when they account for EVERY selected id. If one
          // cannot be resolved (an off-step startup, or stale state), listing the
          // rest would understate the selection — "Alpha" while two are selected.
          // Fall back to the count, which is always true.
          $summary.text(
            names.length === count && count <= 3
              ? names.join(sep)
              : count + ' {{ __("admin.Selected") }}'
          );
        }
        $summary.attr('title', [...selected]
          .map(id => companiesMap[id] ? companyDisplayName(companiesMap[id]) : id)
          .join(', '));

        // The Select All / Deselect All label must describe what the link will do
        // to the rows on screen, so it reflects the currently visible subset.
        const visible = visibleCompanyIds($trig);
        const allVisibleOn = visible.length > 0 && visible.every(id => selected.has(id));
        const $link = $trig.closest('.col-md-6').find('.select-all-toggle');
        $link.text(allVisibleOn ? '{{ __("admin.deselect_all") }}' : '{{ __("admin.select_all") }}');

        // Selecting a startup clears the "required" error surfaced on submit.
        if (count > 0) {
          $trig.removeClass('is-invalid');
          $trig.closest('.col-md-6').find('.error-companies').text('');
        }

        // The auto-calculated end time depends on how many startups are booked,
        // so adding/removing one must refresh the preview.
        const $item = $trig.closest('.accordion-item');
        if ($item.find('.interval-toggle').is(':checked')) computeEndTime($item);
      };

      const refreshCompaniesPopover = ($trig) => {
        const panelIndex = $trig.data('panel-index');
        const $pop = $(`.companies-popover[data-panel-index="${panelIndex}"]`);
        if (!$pop.length) return;
        $pop.find('.companies-list').html(buildCompaniesRows($trig));
        const rangeValues = $trig.data('range-values') || {};
        $pop.find('.clear-range').toggleClass('d-none', rangeValues.from == null && rangeValues.to == null);
      };

      // Select All acts on what the user can currently SEE. With a search active
      // that means the matching startups only, so the link never silently toggles
      // startups scrolled out of view behind a filter.
      const visibleCompanyIds = ($trig) => {
        const search = String($trig.data('search') || '').trim().toLowerCase();
        return allCompanies
          .filter(c => !search || String(companyDisplayName(c)).toLowerCase().includes(search))
          .map(c => String(c.id));
      };

      const toggleAllCompanies = async ($trig) => {
        if ($trig.attr('data-readonly') === '1') return;
        const ids = visibleCompanyIds($trig);
        const selected = $trig.data('selected') || new Set();
        const allOn = ids.length > 0 && ids.every(id => selected.has(id));
        if (allOn) {
          ids.forEach(id => selected.delete(id));
        } else {
          // Only startups not already in THIS panel are newly assigned, so only
          // those need confirming.
          const adding = ids.filter(id => !selected.has(id));
          const approved = await confirmBulkDuplicates($trig.data('panel-index'), adding);
          ids.filter(id => selected.has(id) || approved.includes(id)).forEach(id => selected.add(id));
        }
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

      // True for controls inside a finished (History) panel.
      const inHistoryPanel = (el) => $(el).closest('.accordion-item').attr('data-history') === '1';

      const initPlugins = (el) => {
        // flatpickr is NOT attached to history panels. `minDate: 'today'` rejects
        // a past date, and the picker then rewrites the field — which is why a
        // finished panel rendered its END TIME inside the Session Date box. The
        // stored value is correct; the picker was corrupting the display. A
        // read-only panel needs no picker anyway.
        $(el).find('.session-date').filter((_, d) => !inHistoryPanel(d))
          .flatpickr({ dateFormat: 'Y-m-d', minDate: 'today' });
        $(el).find('.session-time').filter((_, d) => !inHistoryPanel(d))
          .flatpickr({ enableTime: true, noCalendar: true, dateFormat: 'H:i' });
        $(el).find('.select2').each(function () {
          initSelect2($(this));
          // Select2 renders its own widget, which ignores an ancestor
          // `fieldset[disabled]` and stays fully interactive — judges could still
          // be added to a locked panel. Disable the underlying <select> so Select2
          // reflects it.
          if (inHistoryPanel(this)) {
            $(this).prop('disabled', true).trigger('change.select2');
          }
        });
        $(el).find('.companies-picker-trigger').each(function () {
          const $trig = $(this);
          initCompaniesState($trig);
          // The startups picker is a <button>, so the fieldset disables it and the
          // popover cannot open — leaving "3 Selected" with no way to see WHICH
          // three. A history panel keeps its popover so the selection stays
          // viewable; the checkboxes inside are rendered read-only instead.
          initCompaniesPopover($trig);
          if (inHistoryPanel(this)) {
            $trig.prop('disabled', false).attr('data-readonly', '1');
          }
        });
        $(el).find('.accordion-item').each(function () {
          const $panel = $(this);
          if ($panel.find('.interval-toggle').is(':checked')) computeEndTime($panel);
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
          // Rebuilding the label wholesale would strip a history panel's badge,
          // so re-append it for panels that carry one.
          const historyBadge = $panel.attr('data-history') === '1'
            ? ` <span class="badge bg-label-secondary ms-2"><i class="ti ti-history me-1"></i>{{ __('admin.screening.panel_history') }}</span>`
            : '';
          $panel.find('.accordion-button').attr('data-bs-target', `#panel-${idx}`).html(`<i class="ti ti-user-check me-2"></i> {{ __('admin.screening.panel') }} #${idx}${historyBadge}`);
          $panel.find('.accordion-collapse').attr('id', `panel-${idx}`);
          $panel.find('.session-toggle').attr('id', `session-toggle-${idx}`);
          $panel.find('.interval-toggle').attr('id', `interval-toggle-${idx}`);
          // The notify switch carries a name, so it must be renumbered like any
          // other field; its label is tied to it by id.
          $panel.find('.notify-toggle')
            .attr('name', `panels[${i}][notify]`)
            .attr('id', `notify-toggle-${idx}`)
            .closest('.form-check').find('.form-check-label').attr('for', `notify-toggle-${idx}`);
          $panel.find('.companies-picker-trigger, .companies-hidden-container').attr('data-panel-index', i);
          $panel.find('[name*="panels["]').each(function () {
            $(this).attr('name', $(this).attr('name').replace(/panels\[\d+\]/, `panels[${i}]`));
          });

          // Delete is hidden for the first panel (a step keeps at least one) and
          // for ANY history panel — a finished screening is a record and must not
          // be removable. This ran on every Add Panel and previously re-showed the
          // button on locked panels, so adding a panel made the finished ones
          // deletable again.
          const $btn = $panel.find('.remove-panel');
          if (i === 0 || $panel.attr('data-history') === '1') {
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
        // A history panel's picker is view-only; never mutate its selection.
        if ($trig.attr('data-readonly') === '1') return;
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

      $(document).on('click', '.apply-range', async function () {
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

        const selectable = orderedCompanyIds();
        const total = selectable.length;
        const from = fromStr === '' ? 1 : parseInt(fromStr, 10);
        const to = toStr === '' ? total : parseInt(toStr, 10);

        if (from < 1 || from > total || to < 1 || to > total) {
          showError('{{ __("admin.range_outside") }}', to > total ? $to : $from);
          return;
        }

        // Checked before the bounds test would otherwise mask it, since the ticket
        // requires this specific message whenever start > end.
        if (from > to) {
          showError('{{ __("admin.range_invalid") }}', $from);
          return;
        }

        // From/To are the sequential numbers shown beside each startup, so the
        // slice indexes the full ordered list regardless of any active search.
        // The list is NOT filtered to the range: hiding the other startups would
        // renumber them, and a second Apply would then act on a different set
        // than the one on screen.
        const matching = selectable.slice(from - 1, to);
        // Applying a range must honour the same duplicate check as ticking a box
        // one at a time, otherwise the range is a way to bypass the warning.
        const approved = await confirmBulkDuplicates($trig.data('panel-index'), matching);
        $trig.data('selected', new Set(approved));
        $trig.removeData('range-filter');
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
        // Clearing the range now resets the whole picker: the From/To values AND the
        // companies selection they produced. A trailing hand-adjustment after Apply
        // is no longer preserved — Clear is a full reset of this control, not just
        // a way to re-open the range fields.
        $trig.removeData('range-filter').removeData('range-values');
        $trig.data('selected', new Set());
        syncCompaniesState($trig);
        refreshCompaniesPopover($trig);
        $(this).addClass('d-none');
        $pop.find('.range-from, .range-to').val('');
        $pop.find('.range-error').text('').addClass('d-none');
        $pop.find('.range-from, .range-to').removeClass('is-invalid');
      });

      // Search filters the visible startups by name. It never changes the
      // selection and never renumbers, so selections survive typing and clearing.
      $(document).on('input', '.companies-search', function () {
        const $pop = $(this).closest('.companies-popover');
        const $trig = getCompaniesTrigger($pop.data('panel-index'));
        if (!$trig.length) return;
        $trig.data('search', this.value);
        $pop.find('.companies-list').html(buildCompaniesRows($trig));
        syncCompaniesState($trig);
      });

      // Typing in the search box must not submit the surrounding form.
      $(document).on('keydown', '.companies-search', function (e) {
        if (e.key === 'Enter') e.preventDefault();
      });

      $(document).on('change', '.select2', function () { updateSelectAllText($(this)); });

      $('#addPanelBtn').on('click', () => {
        // Clone a LIVE panel as the template. Cloning a history panel would carry
        // its disabled fieldset, History badge and locked state into a new panel.
        const $template = $('.accordion-item').not('[data-history="1"]').first();
        const $clone = ($template.length ? $template : $('.accordion-item').first()).clone();
        // A new panel is never history, whatever it was cloned from. When EVERY
        // existing panel is history there is no live template, so the clone comes
        // from a locked panel and every lock has to be undone explicitly:
        // initPlugins() disables the judges <select> and marks the startups picker
        // read-only DIRECTLY on those elements, so clearing the fieldset alone
        // leaves both controls dead in the new panel.
        $clone.removeAttr('data-history').removeClass('panel-history');
        $clone.find('fieldset').prop('disabled', false).removeAttr('disabled');
        $clone.find('select').prop('disabled', false).removeAttr('disabled');
        $clone.find('input, textarea, button').prop('disabled', false);
        $clone.find('.companies-picker-trigger').removeAttr('data-readonly');
        // THE marker that makes the server treat a panel as history. Cloning a
        // history panel copies this hidden input, so without removing it every
        // NEW panel was submitted as history and got overwritten with the stored
        // panel's date/judges/startups. Everything else in this reset is cosmetic;
        // this line is the one that decides what the SERVER does.
        $clone.find('input[name*="[history_panel]"]').remove();
        $clone.find('.badge').filter((_, el) => $(el).find('.ti-history').length).remove();
        $clone.find('.alert').filter((_, el) => $(el).find('.ti-lock').length).remove();
        // Default ON, matching a freshly rendered panel: a new panel's judges are
        // by definition newly assigned, so notifying is the expected case and the
        // admin opts OUT. (The clone's source may be a history panel, whose switch
        // is forced off, so this is a set-to-default and not a copy.)
        $clone.find('.notify-toggle').prop('checked', true);
        $clone.find('.select-all-toggle').removeClass('d-none');
        // Blade renders the notify switch only for non-history panels, so a clone
        // taken from a history panel has none to copy. Rebuild it, otherwise a new
        // panel silently loses the ability to notify.
        if (!$clone.find('.notify-toggle').length) {
          $clone.find('.accordion-body').append(
            `<div class="form-check form-switch notify-row mt-3">
               <input class="form-check-input notify-toggle" type="checkbox" value="1" checked>
               <label class="form-check-label">
                 {{ __('admin.screening.notify_now') }}
                 <span class="text-muted d-block small">{{ __('admin.screening.notify_now_hint') }}</span>
               </label>
             </div>`
          );
        }

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
          .removeData('search')
          .removeClass('is-invalid')
          .removeAttr('aria-describedby');
        const $clonePop = bootstrap.Popover.getInstance($cloneCompanies[0]);
        if ($clonePop) $clonePop.dispose();
        $cloneCompanies.find('.companies-summary').text('{{ __("admin.select_startups") }}');
        $clone.find('.companies-hidden-container').attr('data-panel-index', '0').empty();
        $clone.find('.range-from, .range-to').val('');
        $clone.find('.range-error').text('').addClass('d-none');
        $clone.find('.range-from, .range-to').removeClass('is-invalid');
        $clone.find('.clear-range').addClass('d-none');
        $clone.find('.companies-search').val('');
        $clone.find('.error-companies').text('');
        $clone.find('.select-all-toggle').text('{{ __("admin.select_all") }}');

        $clone.find('.accordion-button').addClass('collapsed').attr('data-bs-target', `#panel-${idx}`).html(`<i class="ti ti-user-check me-2"></i> @lang('admin.screening.panel') #${idx}`);
        $clone.find('.accordion-collapse').removeClass('show').attr('id', `panel-${idx}`);
        $clone.find('.session-toggle').prop('checked', false).attr('id', `session-toggle-${idx}`);
        $clone.find('.interval-toggle').prop('checked', false).attr('id', `interval-toggle-${idx}`);
        $clone.find('.session-inputs').hide();
        $clone.find('.interval-inputs').hide();
        $clone.find('.interval-duration').val('');
        $clone.find('input[name*="end_time"]').prop('disabled', false).val('');
        $clone.find('.error-interval').text('');
        $clone.find('.meeting-link-input, .meeting-password-input').val('');
        $clone.find('.copy-meeting-link, .copy-meeting-password').hide();

        // The resets above clear the VALUE PROPERTY, but a clone also inherits
        // the source panel's `value` ATTRIBUTES — which panelChangeKind() reads
        // as "what the server rendered". Left in place, a new panel would carry
        // the copied panel's date/time/interval as its baseline and compare
        // against values it never had. A new panel has no saved state, so its
        // baselines are cleared to match (GV-8892 / GV-8893).
        $clone.find('.session-date, .session-time, .interval-duration').attr('value', '');
        $clone.find('.companies-picker-trigger').attr('data-companies-baseline', '[]');

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

      // ---- Interval scheduling: auto-calculated end time per startup slot ----

      $(document).on('change', '.interval-toggle', function () {
        const $body = $(this).closest('.accordion-body');
        const on = this.checked;
        $body.find('.interval-inputs').toggle(on);
        const $end = $body.find('input[name*="end_time"]');
        $end.prop('disabled', on).removeClass('is-invalid');
        $body.find('.error-end_time').text('');
        $body.find('.interval-duration').removeClass('is-invalid');
        $body.find('.error-interval').text('');
        if (on) {
          $end.val('');
          computeEndTime($body);
        }
      });

      $(document).on('input change', '.session-time, .interval-duration', function () {
        const $body = $(this).closest('.accordion-body');
        if ($body.find('.interval-toggle').is(':checked')) computeEndTime($body);
      });

      $(document).on('input', '.interval-duration', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 2);
        $(this).removeClass('is-invalid');
        $(this).closest('.accordion-body').find('.error-interval').text('');
      });

      // async: the startup-assignment confirmation below awaits the admin's answer
      // before the panels are posted (GV-8892 / GV-8893). e.preventDefault() runs
      // synchronously at the top, so the native submit is still cancelled.
      $(document).on('submit', '#judging-panels-form', async function (e) {
        e.preventDefault();
        let valid = true;

        $('.session-inputs input').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('.error-companies').text('');
        $('.companies-picker-trigger').removeClass('is-invalid');

        // Startups are mandatory per panel. Checked for EVERY panel, before the
        // session-only checks below bail out on panels with no scheduled session,
        // so an empty picker surfaces inline instead of only as a server toast.
        $('.accordion-item').each(function (i) {
          const $p = $(this);
          // History panels are rebuilt server-side from stored state; their inputs
          // are disabled and therefore not submitted, so validating them would
          // report a required-field error the admin cannot act on.
          if ($p.attr('data-history') === '1') return;
          const $trig = $p.find('.companies-picker-trigger');
          if (!$trig.length) return;
          const selectedCount = ($trig.data('selected') || new Set()).size;
          const offStepCount = $p.find('input[type="hidden"][name*="[companies][]"]').length;
          if (selectedCount + offStepCount === 0) {
            $trig.addClass('is-invalid');
            $p.find('.error-companies').text(
              '{{ __('admin.screening.panel_companies_required', ['panel' => ':panel']) }}'.replace(':panel', i + 1)
            );
            valid = false;
          }
        });

        $('.accordion-item').each(function () {
          const $p = $(this);
          if ($p.attr('data-history') === '1') return;
          if (!$p.find('.session-toggle').is(':checked')) return;

          const $start = $p.find('input[name*="start_time"]');
          const $end = $p.find('input[name*="end_time"]');
          const intervalOn = $p.find('.interval-toggle').is(':checked');

          const fields = {
            session_date: { msg: '{{ __('admin.date_required') }}' }
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

          const startVal = $start.val().trim();
          const endVal = $end.val().trim();

          if (intervalOn) {
            const $interval = $p.find('input[name*="interval"]');
            const intervalVal = $interval.val().trim();

            if (!startVal) {
              $start.addClass('is-invalid');
              $p.find('.error-start_time').text('{{ __('admin.select_start_time_first') }}');
              valid = false;
            }

            if (intervalVal === '' || !/^\d+$/.test(intervalVal) || parseInt(intervalVal, 10) <= 0) {
              $interval.addClass('is-invalid');
              $p.find('.error-interval').text('{{ __('admin.valid_interval_duration') }}');
              valid = false;
            } else {
              computeEndTime($p);
            }
          } else {
            if (!startVal) {
              $start.addClass('is-invalid');
              $p.find('.error-start_time').text('{{ __('admin.start_time_required') }}');
              valid = false;
            }
            if (!endVal) {
              $end.addClass('is-invalid');
              $p.find('.error-end_time').text('{{ __('admin.end_time_required') }}');
              valid = false;
            } else if (startVal && endVal && startVal >= endVal) {
              $end.addClass('is-invalid');
              $p.find('.error-end_time').text('{{ __('admin.end_time_must_be_after_start_time') }}');
              valid = false;
            }
          }
        });

        if (!valid) {
          const $err = $('.is-invalid').first();
          const $collapse = $err.closest('.accordion-collapse');
          if (!$collapse.hasClass('show')) $collapse.collapse('show');
          $('html, body').animate({ scrollTop: $err.offset().top - 100 }, 300);

          return;
        }

        const action = $(this).attr('action');
        const formData = new FormData(this);

        // GV-8892 / GV-8893: warn before a save that moves meeting times.
        //
        // Only interval panels are gated. With the interval off every startup in
        // a panel shares one panel-wide window, so nothing here moves a time and
        // the warning would be untrue. With it on, each slot is derived from the
        // startup's POSITION and the panel's date/start/interval, so editing any
        // of them shifts real meetings.
        //
        // Purely client-side, unlike the judge-conflict round-trip below: the
        // browser already rendered the saved values, so it can see the change
        // without asking the server.
        //
        // A roster change wins over a schedule change when both are present: its
        // wording is the more specific of the two.
        const changeKinds = $('.accordion-item').toArray()
          .map(el => panelChangeKind($(el)))
          .filter(Boolean);

        const changeKind = changeKinds.includes('startup_assignment')
          ? 'startup_assignment'
          : (changeKinds[0] || null);

        if (changeKind) {
          const copy = {
            startup_assignment: {
              title: '{{ __("admin.screening.startup_assignment_change_title") }}',
              body: '{{ __("admin.screening.startup_assignment_change_body") }}'
            },
            schedule: {
              title: '{{ __("admin.screening.schedule_change_title") }}',
              body: '{{ __("admin.screening.schedule_change_body") }}'
            }
          }[changeKind];

          const { isConfirmed } = await Swal.fire({
            icon: 'warning',
            title: copy.title,
            text: copy.body,
            showCancelButton: true,
            confirmButtonText: '{{ __("admin.Confirm") }}',
            cancelButtonText: '{{ __("admin.Cancel") }}',
            customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
          });

          // Cancel leaves the form exactly as the admin left it, so the panel can
          // be corrected before retrying.
          if (!isConfirmed) return;
        }

        submitPanels(action, formData);
      });

      // Which kind of change a panel carries, or null when nothing that moves a
      // meeting time was touched.
      //
      // Three inputs drive ScreeningSlotService — the session date, the start
      // time and the interval — plus the startup list, whose ORDER decides each
      // slot's position. Any of them reflows the schedule, so all four are
      // compared against the values the server rendered.
      //
      // end_time is deliberately NOT compared: with the interval on it is
      // derived by computeEndTime() from the start time and interval, so it
      // would only restate a change already detected here.
      //
      // The baseline for the schedule fields is the `value` ATTRIBUTE, which
      // keeps what blade rendered, while .val() reads what the admin now has.
      // flatpickr writes the property, never the attribute, so the two stay
      // independent. The startup list needs an explicit baseline attribute
      // instead, because its inputs are rebuilt from a Set on every change.
      const panelChangeKind = ($p) => {
        // A history panel's inputs are disabled and its stored values are
        // restored server-side, so it cannot have changed.
        if ($p.attr('data-history') === '1') return null;

        // An unscheduled panel has no meeting times to move.
        if (!$p.find('.session-toggle').is(':checked')) return null;

        // The dialog promises "affected startups will be notified", and with
        // "Notify now" off the server deliberately emails nobody. Warning about
        // a notification that will not be sent would be a false promise, so a
        // quiet edit stays quiet — matching the backend gate in
        // notifyAffectedStartups().
        if (!$p.find('.notify-toggle').is(':checked')) return null;

        // Per-startup slots only exist in interval mode. Without it every
        // startup shares the panel window, so a roster edit moves nobody.
        if (!$p.find('.interval-toggle').is(':checked')) return null;

        // Roster first: it is the case the ticket names, and its wording is the
        // more specific of the two.
        if (companiesChanged($p)) return 'startup_assignment';

        const scheduleFields = ['session_date', 'start_time', 'interval'];
        const scheduleMoved = scheduleFields.some((name) => {
          const $input = $p.find(`[name*="[${name}]"]`).first();
          if (!$input.length) return false;

          const rendered = ($input.attr('value') || '').trim();
          const now = ($input.val() || '').trim();

          return rendered !== now;
        });

        return scheduleMoved ? 'schedule' : null;
      };

      // True when a panel's startup list differs from the one that was rendered
      // — in membership OR in order.
      //
      // Order matters as much as membership: ScreeningSlotService derives slot N
      // from start + (N * interval), so dragging startup 3 above startup 1
      // changes both of their times while the set stays identical.
      const companiesChanged = ($p) => {
        const $trig = $p.find('.companies-picker-trigger');
        if (!$trig.length) return false;

        // parseIdList yields STRINGS, so both sides are normalised to numbers
        // before comparing — otherwise 3 !== '3' would report a change on every
        // save and the dialog would fire even when nothing moved.
        const toIds = (values) => values
          .map(v => parseInt(v, 10))
          .filter(id => !isNaN(id));

        const baseline = toIds(parseIdList($trig.attr('data-companies-baseline')));

        // Read the inputs that will actually be posted, so this compares exactly
        // what the server will store — including submission order.
        const current = toIds(
          $p.find('input[type="hidden"][name*="[companies][]"]')
            .toArray()
            .map(input => input.value)
        );

        if (baseline.length !== current.length) return true;

        return baseline.some((id, i) => id !== current[i]);
      };

      // Posts the panels, handling the judge-conflict round-trip.
      //
      // Judge double-booking (GV-8300) can only be resolved on the server: a
      // conflict may sit on another step entirely, which the browser never
      // loads. So the first POST may come back asking for confirmation; on
      // Confirm we re-post the SAME payload plus an acknowledgement flag.
      // `acknowledged` guards the retry so a second conflict response can never
      // loop the dialog.
      // Submit busy-state. The button is disabled with a spinner while the request
      // is in flight so a second click cannot fire another save — this endpoint
      // deletes and rebuilds every screening for the step, so a double submit is
      // not harmless.
      //
      // It is released around the confirmation dialogs too: those await the admin,
      // and a button left spinning behind a modal reads as a hung page.
      const setSubmitBusy = (busy) => {
        const $btn = $('#panels-submit-btn');
        $btn.prop('disabled', busy);
        $btn.find('.spinner-border').toggleClass('d-none', !busy);
      };

      const submitPanels = (action, formData, acknowledged = false) => {
        setSubmitBusy(true);
        if (acknowledged) formData.set('acknowledge_judge_conflicts', '1');

        return fetch(action, {
          method: 'POST', body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
          .then(r => r.json())
          .then(async d => {
            if (!d.success) {
              if (!acknowledged && d.requires_confirmation && Array.isArray(d.judge_conflicts) && d.judge_conflicts.length) {
                setSubmitBusy(false);
                const { isConfirmed } = await Swal.fire({
                  icon: 'warning',
                  title: '{{ __("admin.screening.judge_conflict_title") }}',
                  html: d.judge_conflicts.map(m => `<p class="mb-2 text-start">${esc(m)}</p>`).join(''),
                  showCancelButton: true,
                  confirmButtonText: '{{ __("admin.Confirm") }}',
                  cancelButtonText: '{{ __("admin.Cancel") }}',
                  customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-label-secondary' },
                  buttonsStyling: false
                });

                // Cancel leaves the form exactly as the admin left it, so the
                // judge or the schedule can be corrected before retrying.
                if (!isConfirmed) return;

                return submitPanels(action, formData, true);
              }

              setSubmitBusy(false);
              releaseFailureAlert({
                title: '{{ __('admin.error') }}',
                message: d.message || '{{ __('admin.unexpected_error') }}'
              });

              return;
            }

            setSubmitBusy(false);

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
          .catch(e => {
            setSubmitBusy(false);
            releaseFailureAlert({ title: '{{ __('admin.error') }}', message: e?.message || '{{ __('admin.unexpected_error') }}' });
          });
      };

      const maxVisible = 10;

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
