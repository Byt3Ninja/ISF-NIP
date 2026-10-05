<style>
  .flatpickr-calendar {
    box-shadow: none !important;
    border: 1px solid #e6e6e8 !important;
  }
</style>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header border-bottom py-4">
        <div>
          <h5 class="modal-title fw-semibold">
            {{ __('dashboard.Add mentorship session with') }} <span id="mentorName"></span>
          </h5>
          <div class="text-muted small mt-1">{{ __('mentor.select_date_and_slot') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <!-- Startup Selection (Only for Admin when booking) -->
        <div id="startupSelectWrap" class="mb-4" style="display: none;">
          <label for="startupSelect" class="form-label mb-2 fw-semibold">
            {{ __('mentor.select_startup') }} <span class="text-danger">*</span>
          </label>
          <select class="form-select" id="startupSelect">
            <option value="">{{ __('mentor.select_startup') }}</option>
          </select>
        </div>

        <div class="row mb-6 g-4">
          <!-- Calendar Section -->
          <div class="col-auto mb-6 mb-lg-0">
            <div id="calendar"></div>
          </div>

          <!-- Slots Section -->
          <div class="col-auto flex-grow-1">
            <div class="card card-body border shadow-none h-100">
              <div class="mb-3">
                <h6 class="fw-semibold mb-1">{{ __('mentor.available_time_slots') }}</h6>
                <div class="text-muted small" id="slotsDate">{{ __('admin.loading') }}...</div>
              </div>
              <div class="d-flex gap-2 mb-3" id="filter-buttons">
                <button type="button" class="btn btn-xs btn-primary rounded active" data-filter="all">{{ __('admin.All') }}</button>
                <button type="button" class="btn btn-xs rounded border border-light" data-filter="online">
                  <i class="ti ti-video ti-14px me-1"></i>{{ __('mentor.online') }}
                </button>
              </div>
              <div id="slotsList" style="width: 100%; max-height: 320px; overflow-y: auto;">
                <div class="text-center text-muted py-5 small">{{ __('admin.loading') }}...</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Error / Success Messages -->
        <div id="slotError"   class="alert alert-danger  mt-3" style="display: none;"></div>
        <div id="slotSuccess" class="alert alert-success mt-3" style="display: none;"></div>

        <!-- Session Details -->
        <div id="sessionDetails" class="bg-light-subtle border rounded p-3 mt-3" style="display: none;">
          <h6 class="fw-semibold mb-3">{{ __('admin.session_details_label') }}</h6>
          <div id="sessionDetailsBody"></div>
        </div>

        <!-- File Upload -->
        <div class="mt-3">
          <label class="form-label mb-2">
            {{ __('mentor.attachment') }}
            <span class="text-muted fw-normal">({{ __('mentor.optional') }})</span>
          </label>
          <div class="border bg-light-subtle rounded p-4 text-center cursor-pointer"
            onclick="document.getElementById('fileUpload').click()">
            <i class="ti ti-cloud-upload mb-2"></i>
            <div class="fw-medium">{{ __('mentor.single_dropzone_description') }}</div>
            <div class="small text-muted mt-1">{{ __('mentor.allowed_formats_hint') }}</div>
          </div>
          <input type="file" id="fileUpload" class="d-none">
          <div id="fileError" class="text-danger small mt-2" style="display: none;"></div>
          <div id="fileName"  class="small mt-2"             style="display: none;"></div>
        </div>

        <!-- Additional Notes -->
        <div class="mt-3">
          <label for="additionalNotes" class="form-label mb-2">
            {{ __('mentor.additional_notes') }}
            <span class="text-muted fw-normal">({{ __('mentor.optional') }})</span>
          </label>
          <textarea class="form-control" id="additionalNotes" rows="3" maxlength="1000"
            placeholder="{{ __('mentor.slot_notes_placeholder') }}..."></textarea>
        </div>
      </div>

      <div class="modal-footer border-top py-4">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-2"></i>{{ __('admin.Cancel') }}
        </button>
        <button type="button" class="btn btn-primary" id="submit-button" disabled>
          <i class="ti ti-send me-2"></i>{{ __('admin.Submit') }}
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>

  <script>
  $(document).ready(function () {

    // ─── State ────────────────────────────────────────────────────────────────
    const state = {
      slots: [],
      selectedSlot: null,
      selectedSlots: [],
      selectedDate: null,
      calendar: null,
      offlineDisabled: false,
      blocked_intervals: [],
      fileValid: true,
      mentorId: null,
      cycleId: null,
      organizationId: null,
      userId: null,
      staleCheckTimer: null,
      reloadOnClose: false,
      // GV-9566: slot times are wall-clock in this zone (sent by the slots endpoint).
      timezone: @json(\App\Services\TimezoneService::platform()),
    };

    const SESSION_TYPES = {
      Online:  '{{ __('mentor.online') }}',
      Offline: '{{ __('mentor.offline') }}',
      Both:    '{{ __('mentor.both') }}'
    };

    const ICONS = {
      Online:  '<i class="ti ti-video ti-14px"></i>',
      Offline: '<i class="ti ti-map-pin ti-14px"></i>',
      Both:    '<i class="ti ti-screen-share ti-14px"></i>'
    };

    const isAdmin = @json(auth()->user()->hasAnyRole(['admin', 'organizationManager', 'subadmin']));

    // ─── Utilities ────────────────────────────────────────────────────────────
    const parseTime    = (t) => { const [h, m] = t.split(':').map(Number); return h * 60 + m; };
    const calcDuration = (s, e) => parseTime(e) - parseTime(s);
    const formatTime12 = (t) => moment(t, 'HH:mm').format('hh:mm A');
    const getToday     = () => new Date().toISOString().split('T')[0];
    const escapeHtml   = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[c]);

    const formatDate = (dateStr) =>
      new Date(dateStr).toLocaleDateString('en-US', {
        weekday: 'long', month: 'long', day: 'numeric', year: 'numeric'
      });

    const getUTCDate = (date) =>
      new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));

    const getDayName = (dateStr) =>
      getUTCDate(new Date(dateStr))
        .toLocaleDateString('en-US', { weekday: 'long', timeZone: 'UTC' });

    const showError   = (msg) => { $('#slotError').html(msg).show(); $('#slotSuccess').hide(); };
    const showSuccess = (msg) => { $('#slotSuccess').html(msg).show(); $('#slotError').hide(); };
    const hideMessages = () => $('#slotError, #slotSuccess').hide();

    // ─── Parse the redirected HTML page for session flash messages ────────────
    const parseFlashFromHtml = (html) => {
      const $page = $($.parseHTML(html, document, true));

      const successSelectors = [
        '[data-flash="success"]', '.alert-success', '[class*="success"]'
      ];
      const errorSelectors = [
        '[data-flash="danger"]', '.alert-danger', '[class*="danger"]'
      ];

      for (const sel of successSelectors) {
        const text = $page.find(sel).first().text().trim();
        if (text) return { success: true, message: text };
      }

      for (const sel of errorSelectors) {
        const text = $page.find(sel).first().text().trim();
        if (text) return { success: false, message: text };
      }

      if (html.includes('Request_successfully_added_Startup') || html.includes('successfully')) {
        return { success: true, message: '{{ __('mentor.session_booked_successfully') }}' };
      }
      if (html.includes('mentor_not_available_in_this_time')) {
        return { success: false, message: '{{ __('mentor.mentor_not_available_in_this_time') }}' };
      }
      if (html.includes('cannot_schedule_past_meeting')) {
        return { success: false, message: 'Cannot schedule a meeting in the past.' };
      }
      if (html.includes('meeting_error')) {
        return { success: false, message: 'Failed to create the online meeting. Please try again.' };
      }
      if (html.includes('Offline sessions are not allowed')) {
        return { success: false, message: 'Offline sessions are not allowed.' };
      }
      if (html.includes('max_concurrent_sessions_reached')) {
        return { success: false, message: '{{ __('api.max_concurrent_sessions_reached') }}' };
      }

      return { success: true, message: '{{ __('mentor.session_booked_successfully') }}' };
    };

    // ─── Render the per-slot failure list from addWait's JSON summary ─────────
    const formatFailedSlots = (failed) => {
      if (!Array.isArray(failed) || !failed.length) return '';
      const rows = failed.map(f => {
        const when = [
          f.date ? formatDate(f.date) : '',
          (f.time_from && f.time_to)
            ? `${formatTime12(f.time_from)} - ${formatTime12(f.time_to)}`
            : ''
        ].filter(Boolean).join(', ');
        const reason = escapeHtml(f.reason || '');
        return `<li>${when ? `<strong>${escapeHtml(when)}</strong> — ` : ''}${reason}</li>`;
      }).join('');
      return `<ul class="mb-0 mt-1 ps-3">${rows}</ul>`;
    };

    // Normalise addWait's response: structured JSON (AJAX path) or, as a
    // fallback, the scraped redirect HTML.
    const readBookingOutcome = (res) => {
      if (res && typeof res === 'object') {
        return {
          status:  res.status || 'success',
          message: res.message || '',
          failedHtml: formatFailedSlots(res.failed),
        };
      }
      const parsed = parseFlashFromHtml(res);
      return {
        status: parsed.success ? 'success' : 'error',
        message: parsed.message,
        failedHtml: '',
      };
    };

    // ─── Build FormData ───────────────────────────────────────────────────────
    const buildFormData = () => {
      const fd   = new FormData();

      fd.append('_token',             '{{ csrf_token() }}');
      fd.append('mentorid',           state.mentorId);
      fd.append('organization_id',    state.organizationId);
      fd.append('cycle_id',           state.cycleId);
      fd.append('notes',              $('#additionalNotes').val() || '');

      const file = $('#fileUpload')[0].files[0];
      if (file) fd.append('file_url', file);

      if (isAdmin) {
        const selectedUserId = $('#startupSelect').val();
        fd.append('user_id', selectedUserId);

        state.selectedSlots.forEach((slot, index) => {
          fd.append(`selected_dates[${index}][date]`,         slot.date);
          fd.append(`selected_dates[${index}][time_from]`,    slot.start_time);
          fd.append(`selected_dates[${index}][time_to]`,      slot.end_time);
          fd.append(`selected_dates[${index}][session_type]`, slot.session_type);
        });
      } else {
        const slot = state.selectedSlot;
        fd.append('date',               state.selectedDate);
        fd.append('session_start_time', slot.start_time);
        fd.append('session_end_time',   slot.end_time);
        fd.append('timeFrom',           slot.start_time);
        fd.append('timeTo',             slot.end_time);
        fd.append('session_type',       slot.session_type);

        @if (isset(auth()->user()->company->id))
          fd.append('user_id', '{{ auth()->user()->company->id }}');
        @endif
      }

      return fd;
    };

    // ─── Slot helpers ─────────────────────────────────────────────────────────
    const getAvailableDates = () => {
      const dates   = new Set();
      const today   = new Date(); today.setHours(0, 0, 0, 0);
      const endDate = new Date(); endDate.setMonth(endDate.getMonth() + 3);

      state.slots.forEach(slot => {
        if (slot.date) {
          const d = new Date(slot.date); d.setHours(0, 0, 0, 0);
          if (d >= today) dates.add(slot.date);
        }
      });

      for (let d = new Date(today); d <= endDate; d.setDate(d.getDate() + 1)) {
        const utcDate = getUTCDate(d);
        const dayName = utcDate.toLocaleDateString('en-US', { weekday: 'long', timeZone: 'UTC' });
        const dateStr = utcDate.toISOString().split('T')[0];
        if (state.slots.some(s => s.weekly && s.weekday === dayName)) dates.add(dateStr);
      }

      // Only offer dates that still have a slot left to book: drop any date
      // whose slots are all past (today, late in the day) or already taken.
      return Array.from(dates)
        .filter(dateStr => getSlotsForDate(dateStr)
          .some(s => !isSlotBlocked(s, dateStr) && !isSlotPast(s, dateStr)))
        .sort();
    };

    const getSlotsForDate = (dateStr) => {
      const dayName = getDayName(dateStr);
      return state.slots.flatMap(slot => {
        if (slot.date === dateStr)                   return [slot];
        if (slot.weekly && slot.weekday === dayName) return [{ ...slot, date: dateStr }];
        return [];
      });
    };

    // GV-9566: "YYYY-MM-DD" + "HH:mm" read as wall-clock in state.timezone -> unix seconds.
    // Uses the zone's offset ON THAT DATE (DST-safe), the same instant the server builds
    // for blocked_intervals, whatever the browser's own timezone is.
    const zoneParts = (ms, tz) => {
      const p = {};
      new Intl.DateTimeFormat('en-US', {
        timeZone: tz, hourCycle: 'h23', year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
      }).formatToParts(new Date(ms)).forEach(x => { p[x.type] = x.value; });
      return Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour % 24, +p.minute, +p.second);
    };
    const slotInstant = (dateStr, time) => {
      const [y, mo, d] = dateStr.split('-').map(Number);
      const [h, mi]    = time.split(':').map(Number);
      const asUtc = Date.UTC(y, mo - 1, d, h, mi);
      let ms = asUtc;
      try {
        ms = asUtc - (zoneParts(asUtc, state.timezone) - asUtc);
        ms = asUtc - (zoneParts(ms, state.timezone) - ms);
      } catch (e) { /* unknown zone: fall back to reading it as UTC */ }
      return Math.floor(ms / 1000);
    };

    const isSlotBlocked = (slot, dateStr) => {
      if (!state.blocked_intervals?.length) return false;
      const start = slotInstant(dateStr, slot.start_time);
      const end   = slotInstant(dateStr, slot.end_time);
      return state.blocked_intervals.some(i => start < i.end && end > i.start);
    };

    const isSlotPast = (slot, dateStr) => slotInstant(dateStr, slot.start_time) <= moment().unix();

    const filterSlots = (slots, filterType) => {
      // Past slots are dropped; already-booked slots are kept but tagged so the
      // list can render them locked/disabled instead of hiding them (prevents
      // rebooking before submit rather than erroring after).
      let available = slots.filter(s => !isSlotPast(s, s.date));
      available.forEach(s => { s._blocked = isSlotBlocked(s, s.date); });
      if (state.offlineDisabled) available = available.filter(s => s.session_type === 'Online');
      if (filterType === 'online')  return available.filter(s => s.session_type === 'Online');
      if (filterType === 'offline') return available.filter(s => s.session_type === 'Offline');
      return available;
    };

    // A slot conflicts when another *selected* slot occupies the exact same
    // time range with a different session type. Admins may book several
    // different time ranges, but only one session type per time range.
    const conflictsWithSelection = (slot) =>
      state.selectedSlots.some(s =>
        s.date         === slot.date       &&
        s.start_time   === slot.start_time &&
        s.end_time     === slot.end_time   &&
        s.session_type !== slot.session_type);

    // ─── Render slots ─────────────────────────────────────────────────────────
    const renderSlots = (dateStr) => {
      const $container = $('#slotsList');
      const filter     = $('button[data-filter].active').data('filter');
      const slots      = filterSlots(getSlotsForDate(dateStr), filter);

      if (!slots.length) {
        $container.html('<div class="text-center text-muted py-5 small">{{ __('mentor.no_available_slots_for_this_date') }}</div>');
        return;
      }

      slots.forEach((s) => { s._key = `${s.id ?? 'x'}|${s.date ?? ''}|${s.start_time}|${s.end_time}|${s.session_type}`; });

      const html = slots.map((slot, i) => {
        const duration    = calcDuration(slot.start_time, slot.end_time);
        const icon        = ICONS[slot.session_type]         || ICONS.Both;
        const sessionType = SESSION_TYPES[slot.session_type] || SESSION_TYPES.Both;
        const location    = slot?.location || '';
        const showMap     = location && (slot.session_type === 'Offline' || slot.session_type === 'Both');

        const isBlocked   = !!slot._blocked;
        const inputType   = isAdmin ? 'checkbox' : 'radio';
        const isChecked   = isAdmin
          ? (!isBlocked && state.selectedSlots.some(s => s._key === slot._key))
          : (!isBlocked && state.selectedSlot && state.selectedSlot.start_time === slot.start_time && state.selectedSlot.end_time === slot.end_time);
        const isConflict  = isAdmin && !isChecked && !isBlocked && conflictsWithSelection(slot);
        const isDisabled  = isBlocked || isConflict;
        const borderClass = isChecked ? 'border-primary' : '';
        const stateClass  = isBlocked ? 'opacity-50 slot-booked' : (isConflict ? 'opacity-50 slot-conflict' : '');

        return `
          <div class="card shadow-none mb-2 border slot-card ${borderClass} ${stateClass}" data-slot-index="${i}">
            <div class="card-body p-3">
              <div class="d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                  <div class="fw-semibold mb-1 d-flex align-items-center gap-2">
                    <i class="ti ti-clock ti-16px"></i>
                    ${formatTime12(slot.start_time)} - ${formatTime12(slot.end_time)}
                  </div>
                  <div class="d-flex align-items-center gap-2 small">
                    <i class="ti ti-hourglass-empty ti-16px"></i> ${duration} @lang('mentor.min')
                    <span class="badge bg-light-subtle d-flex align-items-center gap-1 text-dark rounded px-2">
                      ${icon} ${sessionType}
                    </span>
                    ${showMap ? `
                      <a href="${location}"
                        target="_blank"
                        class="badge bg-light-subtle d-flex align-items-center gap-1 text-dark rounded px-2 text-decoration-none"
                        title="${location}">
                        <i class="ti ti-map-pin ti-16px"></i>
                        <span>@lang('mentor.google_maps')</span>
                      </a>` : ''}
                  </div>
                  ${isBlocked ? `
                    <div class="small text-muted mt-1">
                      <i class="ti ti-lock ti-14px me-1"></i>@lang('mentor.slot_booked_unavailable_hint')
                    </div>` : ''}
                  ${isConflict ? `
                    <div class="small text-muted mt-1">
                      <i class="ti ti-info-circle ti-14px me-1"></i>@lang('mentor.slot_type_conflict_hint')
                    </div>` : ''}
                </div>
                ${isBlocked ? `
                  <span class="badge bg-light-subtle text-dark d-flex align-items-center gap-1 rounded px-2 ms-2">
                    <i class="ti ti-lock ti-14px"></i>@lang('mentor.slot_booked_unavailable')
                  </span>` : `
                  <input type="${inputType}" class="form-check-input ms-2" name="slot" value="${i}"
                         ${isChecked ? 'checked' : ''} ${isDisabled ? 'disabled' : ''}>`}
              </div>
            </div>
          </div>`;
      }).join('');

      $container.html(html);

      $container.find('.slot-card').on('click', function (e) {
        if ($(e.target).closest('a, input').length) return;
        const $input = $(this).find('input[name="slot"]');
        if ($input.prop('disabled')) return;
        if (isAdmin) {
          $input.prop('checked', !$input.prop('checked')).trigger('change');
        } else {
          $input.prop('checked', true).trigger('change');
        }
      });

      $container.find('input[name="slot"]').on('change', function () {
        if (isAdmin) {
          // Keep picks from other days (and drop any that went stale), then
          // rebuild this day's picks from its checked inputs. Selection is
          // per-request, accumulating across calendar days until submit.
          state.selectedSlots = state.selectedSlots.filter(s =>
            s.date !== dateStr && !isSlotBlocked(s, s.date) && !isSlotPast(s, s.date));
          $container.find('input[name="slot"]:checked').each(function () {
            state.selectedSlots.push(slots[parseInt($(this).val())]);
          });
          updateSelectedSlotsDisplay();
          // Re-render so conflicting slots gain/lose their disabled state.
          renderSlots(dateStr);
        } else {
          selectSlot(slots[parseInt($(this).val())], $(this).closest('.slot-card'));
        }
      });
    };

    const checkSubmitState = () => {
      const hasSelected = isAdmin ? state.selectedSlots.length > 0 : state.selectedSlot !== null;
      const startupSelected = isAdmin ? !!$('#startupSelect').val() : true;
      $('#submit-button').prop('disabled', !hasSelected || !state.fileValid || !startupSelected);
    };

    $('#startupSelect').on('change', checkSubmitState);

    // ─── Shared session-details grid ──────────────────────────────────────────
    // One template for every role, so the layout cannot diverge between the
    // startup single-select and the admin multi-select views.
    const renderSessionGrid = (slot, dateStr) => {
      const duration    = calcDuration(slot.start_time, slot.end_time);
      const sessionType = slot.session_type === 'Online'
        ? '{{ __('mentor.online_video_call') }}'
        : (SESSION_TYPES[slot.session_type] || SESSION_TYPES.Both);

      const isOffline = slot.session_type !== 'Online';
      const address   = slot.Address || '-';
      const loc       = slot.location || '';
      const locText   = loc.length > 30 ? loc.slice(0, 30) + '…' : loc;

      const addressBlock = isOffline ? `
        <div class="col-md-6">
          <div class="d-flex gap-2">
            <i class="ti ti-map-pin"></i>
            <div>
              <div class="small">@lang('admin.Address')</div>
              <div class="fw-medium detail-address">${escapeHtml(address)}</div>
            </div>
          </div>
        </div>` : '';

      const locationBlock = (isOffline && loc) ? `
        <div class="col-md-6">
          <div class="d-flex gap-2">
            <i class="ti ti-map-pin"></i>
            <div>
              <div class="small">@lang('admin.Location')</div>
              <a href="${escapeHtml(loc)}" target="_blank"
                 class="fw-medium text-decoration-underline text-body detail-location">${escapeHtml(locText)}</a>
            </div>
          </div>
        </div>` : '';

      return `
        <div class="row g-3 session-grid">
          <div class="col-md-6">
            <div class="d-flex gap-2">
              <i class="ti ti-calendar"></i>
              <div>
                <div class="small">@lang('admin.date')</div>
                <div class="fw-medium detail-date">${formatDate(dateStr)}</div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex gap-2">
              <i class="ti ti-video"></i>
              <div>
                <div class="small">@lang('mentor.session_type')</div>
                <div class="fw-medium detail-type">${sessionType}</div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex gap-2">
              <i class="ti ti-clock"></i>
              <div>
                <div class="small">@lang('mentor.session_start_time')</div>
                <div class="fw-medium detail-start">${formatTime12(slot.start_time)}</div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex gap-2">
              <i class="ti ti-clock"></i>
              <div>
                <div class="small">@lang('mentor.session_end_time')</div>
                <div class="fw-medium detail-end">${formatTime12(slot.end_time)}</div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex gap-2">
              <i class="ti ti-hourglass-empty"></i>
              <div>
                <div class="small">@lang('admin.duration')</div>
                <div class="fw-medium detail-duration">${duration} @lang('admin.min')</div>
              </div>
            </div>
          </div>
          ${addressBlock}
          ${locationBlock}
        </div>`;
    };

    // ─── selectSlot ───────────────────────────────────────────────────────────
    const selectSlot = (slot, $card) => {
      $('.slot-card').removeClass('border-primary');
      $card.addClass('border-primary');
      state.selectedSlot = slot;

      $('#sessionDetailsBody').html(renderSessionGrid(slot, state.selectedDate));
      $('#sessionDetails').show();
      hideMessages();
      checkSubmitState();
    };

    const updateSelectedSlotsDisplay = () => {
      if (state.selectedSlots.length === 0) {
        $('#sessionDetails').hide();
        checkSubmitState();
        return;
      }

      const items = [...state.selectedSlots].sort((a, b) =>
        (a.date + a.start_time).localeCompare(b.date + b.start_time));

      const header = `<div class="fw-semibold mb-3">${
        '{{ __('mentor.selected_sessions_count', ['count' => ':n']) }}'.replace(':n', items.length)
      }</div>`;

      const html = items
        .map(slot => renderSessionGrid(slot, slot.date))
        .join('<hr class="my-3">');

      $('#sessionDetailsBody').html(header + html);
      $('#sessionDetails').show();
      checkSubmitState();
    };

    // ─── Calendar ─────────────────────────────────────────────────────────────
    const initCalendar = () => {
      const dates = getAvailableDates();
      const today = getToday();
      $('#submit-button').prop('disabled', true);

      if (!dates.length) {
        $('#slotsList').html('<div class="text-center text-muted py-5">{{ __('mentor.no_availability_slots_added') }}</div>');
        $('#slotsDate').text('{{ __('mentor.no_availability_slots_added') }}');
        return;
      }

      state.calendar = flatpickr('#calendar', {
        inline: true,
        locale: '{{ $l }}',
        minDate: 'today',
        dateFormat: 'Y-m-d',
        enable: dates,
        onChange: ([date], dateStr) => {
          state.selectedDate = dateStr;
          renderSlots(dateStr);
          hideMessages();
          $('#slotsDate').text(formatDate(dateStr));
          if (isAdmin) {
            // Admin multi-select accumulates across days — keep prior picks and
            // just refresh the summary / submit state for the new day.
            updateSelectedSlotsDisplay();
          } else {
            state.selectedSlot = null;
            state.selectedSlots = [];
            $('#sessionDetails').hide();
            $('#submit-button').prop('disabled', true);
          }
        },
        onDayCreate: (dObj, dStr, fp, dayElem) => {
          const dateStr     = dayElem.dateObj.toISOString().split('T')[0];
          const isAvailable = dates.includes(dateStr);
          const isFuture    = dayElem.dateObj >= new Date().setHours(0, 0, 0, 0);
          $(dayElem).addClass(isAvailable ? 'available' : (isFuture ? 'unavailable' : ''));
        },
        onReady: (selectedDates, dateStr, instance) => {
          const hasBookable = (d) =>
            getSlotsForDate(d).some(s => !isSlotBlocked(s, d) && !isSlotPast(s, d));

          let targetDate = today;
          if (!hasBookable(today)) {
            const nextDate = dates.find(d => hasBookable(d));
            if (nextDate) targetDate = nextDate;
          }
          instance.setDate(targetDate, true);
        }
      });
    };

    const resetCalendar = () => {
      if (state.calendar?.destroy) { state.calendar.destroy(); state.calendar = null; }
      initCalendar();
    };

    // ─── Keep the slot list from going stale while the modal is open ──────────
    const startStaleCheckTimer = () => {
      stopStaleCheckTimer();
      state.staleCheckTimer = setInterval(() => {
        if (isAdmin && state.selectedSlots.length) {
          // Drop cross-day picks that another booking has since taken.
          const before = state.selectedSlots.length;
          state.selectedSlots = state.selectedSlots.filter(s =>
            !isSlotBlocked(s, s.date) && !isSlotPast(s, s.date));
          if (state.selectedSlots.length !== before) updateSelectedSlotsDisplay();
        }
        if (state.selectedDate) renderSlots(state.selectedDate);
      }, 30000);
    };

    const stopStaleCheckTimer = () => {
      if (state.staleCheckTimer) {
        clearInterval(state.staleCheckTimer);
        state.staleCheckTimer = null;
      }
    };

    const resetForm = () => {
      state.selectedSlot = null;
      state.selectedSlots = [];
      state.selectedDate = getToday();
      state.fileValid    = true;
      state.reloadOnClose = false;
      $('#fileUpload').val('');
      $('#additionalNotes').val('');
      $('#fileName, #fileError, #sessionDetails').hide();
      hideMessages();
      $('button[data-filter]').removeClass('active btn-primary').addClass('border border-light');
      $('button[data-filter="all"]').addClass('active btn-primary').removeClass('border border-light');
      if (isAdmin) {
        $('#startupSelect').val('');
        $('#startupSelectWrap').hide();
      }
      resetCalendar();
    };

    // ─── File validation ──────────────────────────────────────────────────────
    const validateFile = (file) => {
      const allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'png', 'jpeg', 'jpg'];
      const ext     = file.name.split('.').pop().toLowerCase();
      if (!allowed.includes(ext))        return { valid: false, error: '{{ __('mentor.unsupported_slot_attachment_format') }}' };
      if (file.size > 10 * 1024 * 1024)  return { valid: false, error: '{{ __('mentor.file_excced_10') }}' };
      return { valid: true };
    };

    // ─── fetchSlots (public entry point) ─────────────────────────────────────
    window.fetchSlots = (mentorId, cycleId, organizationId, userId = null) => {
      if (!mentorId || !cycleId || !organizationId) {
        alert('{{ __('mentor.mentor_not_entrolled_in_cycle') }}');
        return;
      }

      state.mentorId       = mentorId;
      state.cycleId        = cycleId;
      state.organizationId = organizationId;
      state.userId         = userId;

      $.ajax({
        url: `/dashboard/availability/slots/${mentorId}`,
        method: 'GET',
        data: { active_only: 'true', cycle_id: cycleId, organization_id: organizationId },
        dataType: 'json'
      })
      .done(result => {
        state.slots             = result?.data              || [];
        state.blocked_intervals = result?.blocked_intervals || [];
        if (result?.timezone) state.timezone = result.timezone;
        state.offlineDisabled   = !result?.offline_allowed;

        if (result?.user) $('#mentorName').text(result.user.name || 'Mentor Name');

        // Startup selection for Admin
        if (isAdmin) {
          const $select = $('#startupSelect');
          $select.html('<option value="">{{ __('mentor.select_startup') }}</option>');
          
          if (result?.startups && result.startups.length > 0) {
            result.startups.forEach(startup => {
              $select.append(`<option value="${startup.id}">${startup.name}</option>`);
            });
          }

          if (state.userId) {
            $select.val(state.userId);
            $('#startupSelectWrap').hide();
          } else {
            $('#startupSelectWrap').show();
          }
        } else {
          $('#startupSelectWrap').hide();
        }

        $('#bookingModal').modal('show');
        resetCalendar();
        startStaleCheckTimer();

        if (state.offlineDisabled && $('#filter-buttons button[data-filter="all"]').length) {
          $('#filter-buttons button[data-filter="all"]').remove();
        }

        if (!state.offlineDisabled && !$('#filter-buttons button[data-filter="offline"]').length) {
          $('#filter-buttons').append(`
            <button type="button" class="btn btn-xs rounded border border-light" data-filter="offline">
              <i class="ti ti-map-pin ti-14px me-1"></i>{{ __('mentor.offline') }}
            </button>`
          );
        }
        checkSubmitState();
      })
      .fail(xhr => {
        let msg = 'Could not load availability slots.';
        try {
          const json = JSON.parse(xhr.responseText);
          if (json.message) msg = json.message;
        } catch (_) {
          if (xhr.status !== 0) msg = `Could not load slots (${xhr.status}). Please try again.`;
        }
        alert(msg);
      });
    };

    // ─── Filter buttons ───────────────────────────────────────────────────────
    $(document).on('click', 'button[data-filter]', function () {
      $('button[data-filter]').removeClass('active btn-primary').addClass('border border-light');
      $(this).addClass('active btn-primary').removeClass('border border-light');
      if (state.selectedDate) renderSlots(state.selectedDate);
    });

    // ─── File upload ──────────────────────────────────────────────────────────
    $('#fileUpload').on('change', function () {
      const file = this.files[0];
      if (!file) {
        state.fileValid = true;
        $('#fileError, #fileName').hide();
      } else {
        const result = validateFile(file);
        state.fileValid = result.valid;
        if (!result.valid) {
          $('#fileError').text(result.error).show();
          $('#fileName').hide();
        } else {
          $('#fileError').hide();
          $('#fileName').html(`<i class="ti ti-paperclip me-1"></i>${file.name}`).show();
        }
      }
      checkSubmitState();
    });

    // ─── AJAX Submit ──────────────────────────────────────────────────────────
    $('#submit-button').on('click', function () {
      const hasSelected = isAdmin ? state.selectedSlots.length > 0 : state.selectedSlot !== null;
      if (!hasSelected) {
        showError('{{ __('mentor.select_available_slot') }}');
        return;
      }
      if (isAdmin && !$('#startupSelect').val()) {
        showError('{{ __('mentor.please_select_startup') }}');
        return;
      }
      if (!state.fileValid) {
        $('#fileError').show();
        return;
      }

      const $btn = $(this);
      $btn.prop('disabled', true)
          .html('<span class="spinner-border spinner-border-sm me-2"></span>{{ __('admin.loading') }}...');
      hideMessages();

      $.ajax({
        url: '{{ route('mentorships.waitlist_add') }}',
        method: 'POST',
        data: buildFormData(),
        processData: false,
        contentType: false,
      })
      .done(function (res) {
        const outcome = readBookingOutcome(res);
        const submitLabel = '<i class="ti ti-send me-2"></i>{{ __('admin.Submit') }}';

        if (outcome.status === 'success') {
          releaseAlert('success', {
            title: "{{ __('admin.Success') }}",
            message: outcome.message || "{{ __('mentor.request_sent_success') }}",
          });
          setTimeout(() => {
            $('#bookingModal').modal('hide');
            window.location.reload();
          }, 1500);
          return;
        }

        if (outcome.status === 'partial') {
          // Some sessions booked, some failed — never show a bare success.
          showError(`<div class="fw-semibold">${escapeHtml(outcome.message)}</div>${outcome.failedHtml}`);
          // The successful bookings are already saved; refresh once the manager
          // dismisses the modal so they show up.
          state.reloadOnClose = true;
          $btn.prop('disabled', true).html(submitLabel);
          return;
        }

        // Full failure — surface the real per-slot reason(s).
        showError(outcome.failedHtml
          ? `<div class="fw-semibold">${escapeHtml(outcome.message)}</div>${outcome.failedHtml}`
          : (outcome.message || '{{ __('admin.unexpected_error') }}'));
        $btn.prop('disabled', false).html(submitLabel);
      })
      .fail(function (xhr) {
        let msg = '{{ __('admin.unexpected_error') }}';

        if (xhr.status === 422) {
          try {
            const json = JSON.parse(xhr.responseText);
            if (json.errors)  msg = Object.values(json.errors).flat().join('<br>');
            else if (json.message) msg = json.message;
          } catch (_) {}
        } else if (xhr.status === 419) {
          msg = '{{ __('mentor.session_expired_message') }}';
        } else {
          try {
            const json = JSON.parse(xhr.responseText);
            if (json.message) msg = json.message;
          } catch (_) {}
        }

        showError(msg);
        $btn.prop('disabled', false)
            .html('<i class="ti ti-send me-2"></i>{{ __('admin.Submit') }}');
      });
    });

    // ─── Reset on modal close ─────────────────────────────────────────────────
    $('#bookingModal').on('hidden.bs.modal', function () {
      stopStaleCheckTimer();
      if (state.reloadOnClose) {
        window.location.reload();
        return;
      }
      resetForm();
    });

  });
  </script>
@endpush
