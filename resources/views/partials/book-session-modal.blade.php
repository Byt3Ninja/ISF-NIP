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
            {{ $l == 'en' ? 'Select Startup' : 'اختر الشركة الناشئة' }} <span class="text-danger">*</span>
          </label>
          <select class="form-select" id="startupSelect">
            <option value="">{{ $l == 'en' ? 'Select Startup' : 'اختر الشركة الناشئة' }}</option>
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
          <div class="row g-3" id="singleSessionDetails">
            <div class="col-md-6">
              <div class="d-flex gap-2">
                <i class="ti ti-calendar"></i>
                <div>
                  <div class="small">{{ __('admin.date') }}</div>
                  <div class="fw-medium" id="detailDate">-</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex gap-2">
                <i class="ti ti-video"></i>
                <div>
                  <div class="small">{{ __('mentor.session_type') }}</div>
                  <div class="fw-medium" id="detailType">-</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex gap-2">
                <i class="ti ti-clock"></i>
                <div>
                  <div class="small">{{ __('mentor.session_start_time') }}</div>
                  <div class="fw-medium" id="displayStartTime">-</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex gap-2">
                <i class="ti ti-clock"></i>
                <div>
                  <div class="small">{{ __('mentor.session_end_time') }}</div>
                  <div class="fw-medium" id="displayEndTime">-</div>
                </div>
              </div>
            </div>
            <!-- Duration & Location on the same row -->
            <div class="col-md-6">
              <div class="d-flex gap-2">
                <i class="ti ti-hourglass-empty"></i>
                <div>
                  <div class="small">{{ __('admin.duration') }}</div>
                  <div class="fw-medium" id="detailDuration">-</div>
                </div>
              </div>
            </div>
            <div class="col-md-6" id="detailLocationWrap" style="display: none;">
              <div class="d-flex gap-2">
                <i class="ti ti-map-pin"></i>
                <div>
                  <div class="small">{{ __('admin.Location') }}</div>
                  <a href="#" id="detailLocation" target="_blank"
                     class="fw-medium text-decoration-underline text-body">-</a>
                </div>
              </div>
            </div>
            <div class="col-md-6" id="detailAddressWrap" style="display: none;">
              <div class="d-flex gap-2">
                <i class="ti ti-map-pin"></i>
                <div>
                  <div class="small">{{ __('admin.Address') }}</div>
                  <div class="fw-medium" id="detailAddress">-</div>
                </div>
              </div>
            </div>
          </div>
          <div id="multiSessionDetails" style="display: none;">
            <div class="col-12">
              <ul class="list-group list-group-flush" id="selectedSlotsList">
              </ul>
            </div>
          </div>
        </div>

        <!-- File Upload -->
        <div class="mt-3">
          <label class="form-label mb-2">
            {{ $l == 'en' ? 'Attachment' : 'المرفق' }}
            <span class="text-muted fw-normal">({{ __('mentor.optional') }})</span>
          </label>
          <div class="border bg-light-subtle rounded p-4 text-center cursor-pointer"
            onclick="document.getElementById('fileUpload').click()">
            <i class="ti ti-cloud-upload mb-2"></i>
            <div class="fw-medium">{{ __('mentor.single_dropzone_description') }}</div>
            <div class="small text-muted mt-1">PDF, DOC, DOCX, PPT, XLS, PNG, JPEG up to 10MB</div>
          </div>
          <input type="file" id="fileUpload" class="d-none">
          <div id="fileError" class="text-danger small mt-2" style="display: none;"></div>
          <div id="fileName"  class="small mt-2"             style="display: none;"></div>
        </div>

        <!-- Additional Notes -->
        <div class="mt-3">
          <label for="additionalNotes" class="form-label mb-2">
            {{ $l == 'en' ? 'Additional Notes' : 'ملاحظات إضافية' }}
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

    if (isAdmin) {
      $('#singleSessionDetails').hide();
      $('#multiSessionDetails').show();
    } else {
      $('#singleSessionDetails').show();
      $('#multiSessionDetails').hide();
    }

    // ─── Utilities ────────────────────────────────────────────────────────────
    const parseTime    = (t) => { const [h, m] = t.split(':').map(Number); return h * 60 + m; };
    const calcDuration = (s, e) => parseTime(e) - parseTime(s);
    const formatTime12 = (t) => moment(t, 'HH:mm').format('hh:mm A');
    const getToday     = () => new Date().toISOString().split('T')[0];

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
        return { success: false, message: '{{ __('mentor.mentor_not_available_in_this_time') ?? 'Mentor is not available at this time.' }}' };
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
          fd.append(`selected_dates[${index}][date]`,         state.selectedDate);
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

      dates.add(getToday());

      return Array.from(dates).sort();
    };

    const getSlotsForDate = (dateStr) => {
      const dayName = getDayName(dateStr);
      return state.slots.flatMap(slot => {
        if (slot.date === dateStr)                   return [slot];
        if (slot.weekly && slot.weekday === dayName) return [{ ...slot, date: dateStr }];
        return [];
      });
    };

    const isSlotBlocked = (slot, dateStr) => {
      if (!state.blocked_intervals?.length) return false;
      const offset = '{{ date('P') }}';
      const start = moment(`${dateStr} ${slot.start_time} ${offset}`, 'YYYY-MM-DD HH:mm Z').unix();
      const end   = moment(`${dateStr} ${slot.end_time} ${offset}`, 'YYYY-MM-DD HH:mm Z').unix();
      return state.blocked_intervals.some(i => start < i.end && end > i.start);
    };

    const filterSlots = (slots, filterType) => {
      let available = slots.filter(s => !isSlotBlocked(s, s.date));
      if (state.offlineDisabled) available = available.filter(s => s.session_type === 'Online');
      if (filterType === 'online')  return available.filter(s => s.session_type === 'Online');
      if (filterType === 'offline') return available.filter(s => s.session_type === 'Offline');
      return available;
    };

    // ─── Render slots ─────────────────────────────────────────────────────────
    const renderSlots = (dateStr) => {
      const $container = $('#slotsList');
      const filter     = $('button[data-filter].active').data('filter');
      const slots      = filterSlots(getSlotsForDate(dateStr), filter);

      if (!slots.length) {
        $container.html('<div class="text-center text-muted py-5 small">{{ __('mentor.no_available_slots_for_this_date') }}</div>');
        return;
      }

      const html = slots.map((slot, i) => {
        const duration    = calcDuration(slot.start_time, slot.end_time);
        const icon        = ICONS[slot.session_type]         || ICONS.Both;
        const sessionType = SESSION_TYPES[slot.session_type] || SESSION_TYPES.Both;
        const location    = slot?.location || '';
        const showMap     = location && (slot.session_type === 'Offline' || slot.session_type === 'Both');

        const inputType   = isAdmin ? 'checkbox' : 'radio';
        const isChecked   = isAdmin
          ? state.selectedSlots.some(s => s.start_time === slot.start_time && s.end_time === slot.end_time)
          : (state.selectedSlot && state.selectedSlot.start_time === slot.start_time && state.selectedSlot.end_time === slot.end_time);
        const borderClass = isChecked ? 'border-primary' : '';

        return `
          <div class="card shadow-none mb-2 border slot-card ${borderClass}" data-slot-index="${i}">
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
                        <span>Google Maps</span>
                      </a>` : ''}
                  </div>
                </div>
                <input type="${inputType}" class="form-check-input ms-2" name="slot" value="${i}" ${isChecked ? 'checked' : ''}>
              </div>
            </div>
          </div>`;
      }).join('');

      $container.html(html);

      $container.find('.slot-card').on('click', function (e) {
        if ($(e.target).closest('a, input').length) return;
        const $input = $(this).find('input[name="slot"]');
        if (isAdmin) {
          $input.prop('checked', !$input.prop('checked')).trigger('change');
        } else {
          $input.prop('checked', true).trigger('change');
        }
      });

      $container.find('input[name="slot"]').on('change', function () {
        if (isAdmin) {
          state.selectedSlots = [];
          $container.find('.slot-card').removeClass('border-primary');
          $container.find('input[name="slot"]:checked').each(function () {
            const idx = parseInt($(this).val());
            state.selectedSlots.push(slots[idx]);
            $(this).closest('.slot-card').addClass('border-primary');
          });
          updateSelectedSlotsDisplay();
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

    // ─── selectSlot ───────────────────────────────────────────────────────────
    const selectSlot = (slot, $card) => {
      $('.slot-card').removeClass('border-primary');
      $card.addClass('border-primary');
      state.selectedSlot = slot;

      const duration = calcDuration(slot.start_time, slot.end_time);
      $('#detailDate').text(formatDate(state.selectedDate));
      $('#detailType').text(slot.session_type === 'Online' ? '{{ __('mentor.online_video_call') }}' : SESSION_TYPES[slot.session_type]);
      $('#displayStartTime').text(formatTime12(slot.start_time));
      $('#displayEndTime').text(formatTime12(slot.end_time));
      $('#detailDuration').text(`${duration} @lang('admin.min')`);

      if (slot.session_type !== 'Online') {
        $('#detailAddressWrap').show();
        $('#detailAddress').text(slot.Address ? slot.Address : '-');
      } else {
        $('#detailAddressWrap').hide();
      }

      // ── Location in details (same row as duration) ──
      const loc     = slot?.location || '';
      const locText = loc.length > 30 ? loc.slice(0, 30) + '…' : loc;
      const showLoc = loc && slot.session_type !== 'Online';
      if (showLoc) {
        $('#detailLocation')
          .attr('href', loc)
          .text(locText);

        $('#detailLocationWrap').show();
      } else {
        $('#detailLocationWrap').hide();
      }

      $('#sessionDetails').show();
      hideMessages();
      checkSubmitState();
    };

    const updateSelectedSlotsDisplay = () => {
      const $container = $('#selectedSlotsList');
      if (state.selectedSlots.length === 0) {
        $('#sessionDetails').hide();
        checkSubmitState();
        return;
      }

      const html = state.selectedSlots.map(slot => {
        const duration = calcDuration(slot.start_time, slot.end_time);
        const icon     = ICONS[slot.session_type] || ICONS.Both;
        const sessionType = SESSION_TYPES[slot.session_type] || SESSION_TYPES.Both;
        
        let offlineDetails = '';
        if (slot.session_type !== 'Online') {
          const address = slot.Address ? slot.Address : '-';
          const loc = slot.location || '';
          const mapsLink = loc ? `
            <a href="${loc}" target="_blank" class="badge bg-light-subtle text-dark rounded px-2 text-decoration-none ms-2">
              <i class="ti ti-map-pin"></i> Google Maps
            </a>` : '';
          offlineDetails = `
            <div class="small text-muted mt-1">
              <strong>@lang('admin.Address'):</strong> ${address}
              ${mapsLink}
            </div>`;
        }

        return `
          <li class="list-group-item px-0 py-2 border-bottom">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-semibold small">${formatTime12(slot.start_time)} - ${formatTime12(slot.end_time)}</div>
                <div class="small text-muted">${duration} @lang('mentor.min') | ${icon} ${sessionType}</div>
                ${offlineDetails}
              </div>
            </div>
          </li>`;
      }).join('');

      $container.html(html);
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
          state.selectedSlot = null;
          state.selectedSlots = [];
          renderSlots(dateStr);
          $('#sessionDetails').hide();
          hideMessages();
          $('#slotsDate').text(formatDate(dateStr));
          $('#submit-button').prop('disabled', true);
        },
        onDayCreate: (dObj, dStr, fp, dayElem) => {
          const dateStr     = dayElem.dateObj.toISOString().split('T')[0];
          const isAvailable = dates.includes(dateStr);
          const isFuture    = dayElem.dateObj >= new Date().setHours(0, 0, 0, 0);
          $(dayElem).addClass(isAvailable ? 'available' : (isFuture ? 'unavailable' : ''));
        },
        onReady: (selectedDates, dateStr, instance) => {
          let targetDate = today;
          if (!getSlotsForDate(today).length) {
            const nextDate = dates.find(d => getSlotsForDate(d).length > 0);
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

    const resetForm = () => {
      state.selectedSlot = null;
      state.selectedSlots = [];
      state.selectedDate = getToday();
      state.fileValid    = true;
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
        state.offlineDisabled   = !result?.offline_allowed;

        if (result?.user) $('#mentorName').text(result.user.name || 'Mentor Name');

        // Startup selection for Admin
        if (isAdmin) {
          const $select = $('#startupSelect');
          $select.html('<option value="">{{ $l == "en" ? "Select Startup" : "اختر الشركة الناشئة" }}</option>');
          
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
        showError('{{ $l == "en" ? "Please select a startup." : "يرجى اختيار شركة ناشئة." }}');
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
      .done(function (html) {
        const result = parseFlashFromHtml(html);

        if (result.success) {
          releaseSuccessAlert({
            title: "{{ __('admin.Success') }}",
            message: result.message || "{{ __('mentor.request_sent_success') }}",
          });
          setTimeout(() => {
            $('#bookingModal').modal('hide');
            window.location.reload();
          }, 1500);
        } else {
          showError(result.message);
          $btn.prop('disabled', false)
              .html('<i class="ti ti-send me-2"></i>{{ __('admin.Submit') }}');
        }
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
          msg = '{{ __('mentor.session_expired_messsage') }}';
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
    $('#bookingModal').on('hidden.bs.modal', resetForm);

  });
  </script>
@endpush
