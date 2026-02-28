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
          <div class="row g-3">
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
            <div class="col-12">
              <div class="d-flex gap-2">
                <i class="ti ti-hourglass-empty"></i>
                <div>
                  <div class="small">{{ __('admin.duration') }}</div>
                  <div class="fw-medium" id="detailDuration">-</div>
                </div>
              </div>
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
    selectedDate: null,
    calendar: null,
    offlineDisabled: false,
    blocked_intervals: [],
    fileValid: true,
    mentorId: null,
    cycleId: null,
    organizationId: null,
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
  // The controller uses return back()->with(['msg' => '...', 'class' => '...'])
  // After AJAX follows the redirect, we receive the destination HTML page.
  // We look for the flashed msg in the returned HTML by checking a meta tag
  // or a known element. But the simplest approach: we add X-Requested-With
  // header so Laravel detects AJAX — but back() still redirects.
  //
  // Best solution: intercept the redirect response via the X-Redirect header
  // trick — jQuery AJAX automatically follows redirects, so we receive the
  // final page HTML. We parse it for known success/error indicators.
  const parseFlashFromHtml = (html) => {
    // Parse the returned HTML page looking for flash message containers
    const $page = $($.parseHTML(html, document, true));

    // Try common flash/alert selectors used in the app
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

    // Fallback: if we got HTML back at all, the request went through.
    // Check for known success/error strings in the raw HTML.
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

    // If none matched, assume success (page reloaded without error)
    return { success: true, message: '{{ __('mentor.session_booked_successfully') }}' };
  };

  // ─── Build FormData ───────────────────────────────────────────────────────
  // Field mapping to MentorsController@addWait (non-admin branch):
  //   date               → $request->date
  //   session_start_time → $request->session_start_time  (used for datefrom & schedule query)
  //   session_end_time   → $request->session_end_time    (used for dateto   & schedule query)
  //   mentorid           → $request->mentorid
  //   organization_id    → $request->organization_id
  //   cycle_id           → $request->cycle_id
  //   session_type       → $request->session_type
  //   notes              → $request->notes
  //   file_url           → $request->file('file_url')
  const buildFormData = () => {
    const slot = state.selectedSlot;
    const fd   = new FormData();

    fd.append('_token',             '{{ csrf_token() }}');
    fd.append('mentorid',           state.mentorId);
    fd.append('organization_id',    state.organizationId);
    fd.append('cycle_id',           state.cycleId);
    fd.append('date',               state.selectedDate);
    fd.append('session_start_time', slot.start_time);   // used by controller for datefrom + schedule lookup
    fd.append('session_end_time',   slot.end_time);     // used by controller for dateto   + schedule lookup
    fd.append('timeFrom',           slot.start_time);   // kept for compatibility
    fd.append('timeTo',             slot.end_time);     // kept for compatibility
    fd.append('session_type',       slot.session_type);
    fd.append('notes',              $('#additionalNotes').val() || '');

    @if (isset(auth()->user()->company->id))
      fd.append('user_id', '{{ auth()->user()->company->id }}');
    @endif

    const file = $('#fileUpload')[0].files[0];
    if (file) fd.append('file_url', file);

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
    const [year, month, day] = dateStr.split('-').map(Number);
    const [sh, sm] = slot.start_time.split(':').map(Number);
    const [eh, em] = slot.end_time.split(':').map(Number);
    const start = new Date(year, month - 1, day, sh, sm, 0).getTime() / 1000;
    const end   = new Date(year, month - 1, day, eh, em, 0).getTime() / 1000;
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

      return `
        <div class="card shadow-none mb-2 border slot-card" data-slot-index="${i}">
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
                    <a href="https://www.google.com/maps/search/${encodeURIComponent(location)}"
                       target="_blank"
                       class="badge bg-light-subtle d-flex align-items-center gap-1 text-dark rounded px-2 text-decoration-none"
                       title="${location}">
                      <i class="ti ti-map-pin ti-16px"></i>
                      <span>${location.length > 20 ? location.slice(0, 20) + '…' : location}</span>
                    </a>` : ''}
                </div>
              </div>
              <input type="radio" class="form-check-input ms-2" name="slot" value="${i}">
            </div>
          </div>
        </div>`;
    }).join('');

    $container.html(html);

    $container.find('input[name="slot"]').on('change', function () {
      selectSlot(slots[parseInt($(this).val())], $(this).closest('.slot-card'));
    });
  };

  // ─── selectSlot ───────────────────────────────────────────────────────────
  const selectSlot = (slot, $card) => {
    $('.slot-card').removeClass('border-primary');
    $card.addClass('border-primary');
    state.selectedSlot = slot;

    const duration = calcDuration(slot.start_time, slot.end_time);
    $('#detailDate').text(formatDate(state.selectedDate));
    $('#detailType').text(slot.session_type === 'Online' ? '{{ __('mentor.online_video_call') }}' : slot.session_type);
    $('#displayStartTime').text(formatTime12(slot.start_time));
    $('#displayEndTime').text(formatTime12(slot.end_time));
    $('#detailDuration').text(`${duration} @lang('admin.min')`);

    $('#sessionDetails').show();
    hideMessages();
    $('#submit-button').prop('disabled', !state.fileValid);
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
      defaultDate: today,
      dateFormat: 'Y-m-d',
      enable: dates,
      onChange: ([date], dateStr) => {
        state.selectedDate = dateStr;
        state.selectedSlot = null;
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
      }
    });

    state.selectedDate = today;
    $('#slotsDate').text(formatDate(today));
    renderSlots(today);
  };

  const resetCalendar = () => {
    if (state.calendar?.destroy) { state.calendar.destroy(); state.calendar = null; }
    initCalendar();
  };

  const resetForm = () => {
    state.selectedSlot = null;
    state.selectedDate = getToday();
    state.fileValid    = true;
    $('#fileUpload').val('');
    $('#additionalNotes').val('');
    $('#fileName, #fileError, #sessionDetails').hide();
    hideMessages();
    $('button[data-filter]').removeClass('active btn-primary').addClass('border border-light');
    $('button[data-filter="all"]').addClass('active btn-primary').removeClass('border border-light');
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
  window.fetchSlots = (mentorId, cycleId, organizationId) => {
    if (!mentorId || !cycleId || !organizationId) {
      alert('{{ __('mentor.mentor_not_entrolled_in_cycle') }}');
      return;
    }

    state.mentorId       = mentorId;
    state.cycleId        = cycleId;
    state.organizationId = organizationId;

    $.ajax({
      url: `/dashboard/availability/slots/${mentorId}`,
      method: 'GET',
      data: { active_only: 'true' },
      dataType: 'json'
    })
    .done(result => {
      state.slots             = result?.data              || [];
      state.blocked_intervals = result?.blocked_intervals || [];
      state.offlineDisabled   = !result?.offline_allowed;

      if (result?.user) $('#mentorName').text(result.user.name || 'Mentor Name');

      $('#bookingModal').modal('show');
      resetCalendar();

      if (!state.offlineDisabled && !$('#filter-buttons button[data-filter="offline"]').length) {
        $('#filter-buttons').append(`
          <button type="button" class="btn btn-xs rounded border border-light" data-filter="offline">
            <i class="ti ti-map-pin ti-14px me-1"></i>{{ __('mentor.offline') }}
          </button>`
        );
      }
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
    $('#submit-button').prop('disabled', !state.selectedSlot || !state.fileValid);
  });

  // ─── AJAX Submit ──────────────────────────────────────────────────────────
  // The controller uses return back()->with([...]) which issues a redirect.
  // jQuery follows the redirect automatically and returns the final HTML page.
  // We do NOT use dataType:'json' — instead we receive the HTML and parse the
  // flash message out of it to determine success or failure.
  $('#submit-button').on('click', function () {
    if (!state.selectedSlot) {
      showError('{{ __('mentor.select_available_slot') }}');
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
      // No dataType:'json' — controller returns a redirect, not JSON
    })
    .done(function (html) {
      const result = parseFlashFromHtml(html);

      if (result.success) {
        // showSuccess(result.message);
        releaseSuccessAlert({
          title: "{{ __('admin.Success') }}",
          message: result.message || 'Request Sent Successfully',
        });
        setTimeout(() => {
          $('#bookingModal').modal('hide');
          window.location.reload();
        }, 1500);
      } else {
        showError(result.message);
        // releaseFailureAlert({
        //   title: "{{ __('admin.Error') }}",
        //   message: result.message || '{{ __('admin.unexpected_error') }}',
        // });
        $btn.prop('disabled', false)
            .html('<i class="ti ti-send me-2"></i>{{ __('admin.Submit') }}');
      }
    })
    .fail(function (xhr) {
      let msg = 'Something went wrong. Please try again.';

      // 422 = Laravel validation error (only the notes field is validated)
      if (xhr.status === 422) {
        try {
          const json = JSON.parse(xhr.responseText);
          if (json.errors) msg = Object.values(json.errors).flat().join('<br>');
          else if (json.message) msg = json.message;
        } catch (_) {}
      } else if (xhr.status === 419) {
        msg = 'Session expired. Please refresh the page and try again.';
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