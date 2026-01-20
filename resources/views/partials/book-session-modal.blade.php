<style>
  .flatpickr-calendar {
    box-shadow: none !important;
    border: 1px solid #e6e6e8 !important;
  }
</style>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="bookingFormSubmit" method="post" action="/dashboard/mentorship/addwait" enctype="multipart/form-data" class="modal-content" data-ignore-dirty>
      <div class="modal-header border-bottom py-4">
        <div>
          <h5 class="modal-title fw-semibold">
            {{ __('dashboard.Add mentorship session with') }} <span id="mentorName"></span>
          </h5>
          <div class="text-muted small mt-1">{{ __('mentor.select_date_and_slot') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      @csrf
      
      <input type="hidden" name="mentorid" id="bookingMentorId">
      @if (isset(auth()->user()->company->id))
        <input type="hidden" name="user_id" value="{{ auth()->user()->company->id }}">
      @endif
      <input type="hidden" name="organization_id" id="bookingOrgId">
      <input type="hidden" name="cycle_id" id="bookingCycleId">
      <input type="hidden" name="date" id="selectedDateValue">
      <input type="hidden" name="timeFrom" id="selectedStartTime">
      <input type="hidden" name="timeTo" id="selectedEndTime">
      <input type="hidden" name="session_type" id="selectedSessionType">
      <input type="hidden" name="session_start_time" id="sessionStartTimeHidden">
      <input type="hidden" name="session_end_time" id="sessionEndTimeHidden">

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

        <!-- Error Message -->
        <div id="slotError" class="alert alert-danger mt-3" style="display: none;"></div>

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
            
            <!-- Session Start Time -->
            <div class="col-md-6">
              <label class="form-label small mb-1">{{ __('mentor.session_start_time') }}</label>
              <select class="form-select bg-card" id="sessionStartTime" required>
                <option value="">{{ __('admin.Select') }}...</option>
              </select>
            </div>
            
            <!-- Session End Time -->
            <div class="col-md-6">
              <label class="form-label small mb-1">{{ __('mentor.session_end_time') }}</label>
              <select class="form-select bg-card" id="sessionEndTime" required>
                <option value="">{{ __('admin.Select') }}...</option>
              </select>
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
          <input type="file" name="file_url" id="fileUpload" class="d-none">
          <div id="fileError" class="invalid-feedback mt-2" style="display: none;"></div>
          <div id="fileName" class="small mt-2" style="display: none;"></div>
        </div>

        <!-- Additional Notes -->
        <div class="mt-3">
          <label for="additionalNotes" class="form-label mb-2">
            {{ $l == 'en' ? 'Additional Notes' : 'ملاحظات إضافية' }}
            <span class="text-muted fw-normal">({{ __('mentor.optional') }})</span>
          </label>
          <textarea class="form-control" name="notes" id="additionalNotes" rows="3" maxlength="1000"
            placeholder="{{ __('mentor.slot_notes_placeholder') }}..."></textarea>
        </div>
      </div>
      <div class="modal-footer border-top py-4">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-2"></i>{{ __('admin.Cancel') }}
        </button>
        <button type="submit" class="btn btn-primary" id="submit-button">
          <i class="ti ti-send me-2"></i>{{ __('admin.Submit') }}
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>

<script>
$(document).ready(function() {
  // State management
  const state = {
    slots: [],
    selectedSlot: null,
    selectedDate: null,
    calendar: null,
    offlineDisabled: false,
    blocked_intervals: [],
    fileValid: true
  };

  const SESSION_TYPES = {
    Online: '{{ __('mentor.online') }}',
    Offline: '{{ __('mentor.offline') }}',
    Both: '{{ __('mentor.both') }}'
  };

  const ICONS = {
    Online: '<i class="ti ti-video ti-14px"></i>',
    Offline: '<i class="ti ti-map-pin ti-14px"></i>',
    Both: '<i class="ti ti-screen-share ti-14px"></i>'
  };

  // Utility functions
  const parseTime = (time) => {
    const [h, m] = time.split(':').map(Number);
    return h * 60 + m;
  };

  const calcDuration = (start, end) => parseTime(end) - parseTime(start);

  const formatTime = (minutes) => {
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
  };

  const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('en-US', { 
      weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' 
    });
  };

  const getToday = () => new Date().toISOString().split('T')[0];

  const getUTCDate = (date) => {
    return new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
  };

  const getDayName = (dateStr) => {
    const utcDate = getUTCDate(new Date(dateStr));
    return utcDate.toLocaleDateString('en-US', { weekday: 'long', timeZone: 'UTC' });
  };

  // Generate time options in 30-minute intervals
  const generateTimeOptions = (startTime, endTime) => {
    const options = [];
    const startMinutes = parseTime(startTime);
    const endMinutes = parseTime(endTime);
    
    for (let minutes = startMinutes; minutes <= endMinutes; minutes += 30) {
      options.push({
        value: formatTime(minutes),
        minutes: minutes
      });
    }
    
    return options;
  };

  // Populate start time dropdown
  const populateStartTimeOptions = (slot) => {
    const $select = $('#sessionStartTime');
    $select.empty().append('<option value="">{{ __('admin.Select') }}...</option>');
    
    const options = generateTimeOptions(slot.start_time, slot.end_time);
    
    // Remove the last option (end time) from start time options
    options.slice(0, -1).forEach(opt => {
      $select.append(`<option value="${opt.value}">${opt.value}</option>`);
    });
    
    // Set default to slot start time
    $select.val(slot.start_time);

    // Update hidden input for start time
    $('#sessionStartTimeHidden').val(slot.start_time);
  };

  // Populate end time dropdown based on selected start time
  const populateEndTimeOptions = (slot, selectedStartTime) => {
    const $select = $('#sessionEndTime');
    $select.empty().append('<option value="">{{ __('admin.Select') }}...</option>');
    
    if (!selectedStartTime) return;
    
    const startMinutes = parseTime(selectedStartTime);
    const slotEndMinutes = parseTime(slot.end_time);
    
    // Generate options starting from 30 minutes after selected start time
    for (let minutes = startMinutes + 30; minutes <= slotEndMinutes; minutes += 30) {
      const timeStr = formatTime(minutes);
      $select.append(`<option value="${timeStr}">${timeStr}</option>`);
    }
    
    // Set default to 30 minutes after start time
    const defaultEndTime = formatTime(startMinutes + 30);
    if ($select.find(`option[value="${defaultEndTime}"]`).length) {
      $select.val(defaultEndTime);
    } else {
      $select.val($select.find('option:not(:first)').first().val());
    }

    // Update hidden input for end time
    $('#sessionEndTimeHidden').val(defaultEndTime);
  };

  // Update duration display
  const updateDurationDisplay = () => {
    const startTime = $('#sessionStartTime').val();
    const endTime = $('#sessionEndTime').val();
    
    if (startTime && endTime) {
      const duration = calcDuration(startTime, endTime);
      $('#detailDuration').text(`${duration} @lang('admin.min')`);
      
      // Update hidden inputs
      $('#selectedStartTime').val(startTime);
      $('#selectedEndTime').val(endTime);
    } else {
      $('#detailDuration').text('-');
    }
  };

  // Slot filtering and retrieval
  const getAvailableDates = () => {
    const dates = new Set();
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const endDate = new Date();
    endDate.setMonth(endDate.getMonth() + 3);

    // Add specific dates
    state.slots.forEach(slot => {
      if (slot.date) {
        const slotDate = new Date(slot.date);
        slotDate.setHours(0, 0, 0, 0);
        if (slotDate >= today) dates.add(slot.date);
      }
    });

    // Add weekly recurring dates
    for (let d = new Date(today); d <= endDate; d.setDate(d.getDate() + 1)) {
      const utcDate = getUTCDate(d);
      const dayName = utcDate.toLocaleDateString('en-US', { weekday: 'long', timeZone: 'UTC' });
      const dateStr = utcDate.toISOString().split('T')[0];

      if (state.slots.some(s => s.weekly && s.weekday === dayName)) {
        dates.add(dateStr);
      }
    }

    return Array.from(dates).sort();
  };

  const getSlotsForDate = (dateStr) => {
    const dayName = getDayName(dateStr);
    return state.slots.flatMap(slot => {
      if (slot.date === dateStr) return [slot];
      if (slot.weekly && slot.weekday === dayName) return [{ ...slot, date: dateStr }];
      return [];
    });
  };

  const isSlotBlocked = (slot, dateStr) => {
    if (!state.blocked_intervals || !state.blocked_intervals.length) return false;
    
    const dateParts = dateStr.split('-');
    const year = parseInt(dateParts[0]);
    const month = parseInt(dateParts[1]) - 1;
    const day = parseInt(dateParts[2]);
    
    const startParts = slot.start_time.split(':');
    const endParts = slot.end_time.split(':');
    
    const start = new Date(year, month, day, parseInt(startParts[0]), parseInt(startParts[1]), 0).getTime() / 1000;
    const end = new Date(year, month, day, parseInt(endParts[0]), parseInt(endParts[1]), 0).getTime() / 1000;
    
    return state.blocked_intervals.some(interval => {
       return start < interval.end && end > interval.start;
    });
  };

  const filterSlots = (slots, filterType) => {
    let availableSlots = slots.filter(s => !isSlotBlocked(s, s.date));

    let filtered = state.offlineDisabled 
      ? availableSlots.filter(s => s.session_type === 'Online')
      : availableSlots;

    if (filterType === 'online') return filtered.filter(s => s.session_type === 'Online');
    if (filterType === 'offline') return filtered.filter(s => s.session_type === 'Offline');
    return filtered;
  };

  // UI rendering
  const renderSlots = (dateStr) => {
    const $container = $('#slotsList');
    const filter = $('button[data-filter].active').data('filter');
    const slots = filterSlots(getSlotsForDate(dateStr), filter);

    if (!slots.length) {
      $container.html('<div class="text-center text-muted py-5 small">{{ __('mentor.no_available_slots_for_this_date') }}</div>');
      return;
    }

    const html = slots.map((slot, i) => {
      const duration = calcDuration(slot.start_time, slot.end_time);
      const icon = ICONS[slot.session_type] || ICONS.Both;
      const sessionType = SESSION_TYPES[slot.session_type] || SESSION_TYPES.Both;
      const location = slot?.location || '';

      return `
        <div class="card shadow-none mb-2 border slot-card" data-slot-index="${i}">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div class="flex-grow-1">
                <div class="fw-semibold mb-1 d-flex align-items-center gap-2">
                  <i class="ti ti-clock ti-16px"></i>
                  ${slot.start_time} - ${slot.end_time}
                </div>
                <div class="d-flex align-items-center gap-2 small">
                  <i class="ti ti-hourglass-empty ti-16px"></i> ${duration} @lang('mentor.min')
                  <div class="badge bg-light-subtle d-flex align-items-center gap-1 text-dark rounded px-2 text-decoration-none">
                    ${icon} ${sessionType}
                  </div>

                  ${location && (sessionType === 'Offline' || sessionType === 'Both') ? ` <a href="https://www.google.com/maps/search/${encodeURIComponent(location)}" 
                    target="_blank" 
                    class="badge bg-light-subtle d-flex align-items-center gap-1 text-dark rounded px-2 text-decoration-none"
                    title="${location}">
                    <i class="ti ti-map-pin ti-16px"></i>
                    <span>
                      ${location.length > 20 ? location.slice(0, 20) + '...' : location}
                    </span>
                  </a>` : ''}
                </div>
              </div>
              <input type="radio" class="form-check-input" name="slot" value="${i}">
            </div>
          </div>
        </div>
      `;
    }).join('');

    $container.html(html);

    $container.find('input[name="slot"]').on('change', function() {
      selectSlot(slots[$(this).val()], $(this).closest('.slot-card'));
    });
  };

  const selectSlot = (slot, $card) => {
    $('.slot-card').removeClass('selected');
    $card.addClass('selected');
    state.selectedSlot = slot;

    $('#detailDate').text(formatDate(state.selectedDate));
    $('#detailType').text(slot.session_type === 'Online' ? '{{ __('mentor.online_video_call') }}' : slot.session_type);
    $('#selectedDateValue').val(state.selectedDate);
    $('#selectedSessionType').val(slot.session_type);

    // Populate time dropdowns
    populateStartTimeOptions(slot);
    populateEndTimeOptions(slot, slot.start_time);
    updateDurationDisplay();

    $('#sessionDetails').show();
    $('#slotError').hide();

    // Enable submit button when slot is selected and file is valid
    $('#submit-button').prop('disabled', !state.fileValid);
  };

  // Event handlers for time selection
  $('#sessionStartTime').on('change', function() {
    const selectedStartTime = $(this).val();
    if (state.selectedSlot && selectedStartTime) {
      populateEndTimeOptions(state.selectedSlot, selectedStartTime);
      updateDurationDisplay();

      // Update hidden input for start time
      $('#sessionStartTimeHidden').val(selectedStartTime);
    }
  });

  $('#sessionEndTime').on('change', function() {
    updateDurationDisplay();

    // Update hidden input for end time
    $('#sessionEndTimeHidden').val($(this).val());
  });

  const initCalendar = () => {
    const dates = getAvailableDates();
    const today = getToday();

    if (!dates.length) {
      $('#submit-button').prop('disabled', true);
      $('#slotsList').html('<div class="text-center text-muted py-5">{{ __('mentor.no_availability_slots_added') }}</div>');
      $('#slotsDate').text('{{ __('mentor.no_availability_slots_added') }}');
      return;
    }

    // Disable submit button initially until a slot is selected
    $('#submit-button').prop('disabled', true);

    state.calendar = flatpickr("#calendar", {
      inline: true,
      locale: '{{ $l }}',
      minDate: "today",
      defaultDate: today,
      dateFormat: "Y-m-d",
      enable: dates,
      onChange: ([date], dateStr) => {
        state.selectedDate = dateStr;
        state.selectedSlot = null;
        renderSlots(dateStr);
        $('#sessionDetails, #slotError').hide();
        $('#slotsDate').text(formatDate(dateStr));
        // Disable submit when date changes
        $('#submit-button').prop('disabled', true);
      },
      onDayCreate: (dObj, dStr, fp, dayElem) => {
        const dateStr = dayElem.dateObj.toISOString().split('T')[0];
        const isAvailable = dates.includes(dateStr);
        const isFuture = dayElem.dateObj >= new Date().setHours(0, 0, 0, 0);
        
        $(dayElem).addClass(isAvailable ? 'available' : (isFuture ? 'unavailable' : ''));
      }
    });

    state.selectedDate = today;
    $('#slotsDate').text(formatDate(today));
    renderSlots(today);
  };

  const resetCalendar = () => {
    if (state.calendar?.destroy) {
      state.calendar.destroy();
      state.calendar = null;
    }
    initCalendar();
  };

  const resetForm = () => {
    state.selectedSlot = null;
    state.selectedDate = getToday();
    state.fileValid = true;

    $('#fileUpload, #additionalNotes').val('');
    $('#sessionStartTime, #sessionEndTime').empty().append('<option value="">{{ __('admin.Select') }}...</option>');
    $('#fileName, #fileError, #sessionDetails, #slotError').hide();

    $('button[data-filter]').removeClass('active').first().addClass('active');

    resetCalendar();
  };

  // File upload validation
  const validateFile = (file) => {
    const allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'png', 'jpeg', 'jpg'];
    const ext = file.name.split('.').pop().toLowerCase();
    const maxSize = 10 * 1024 * 1024;

    if (!allowed.includes(ext)) {
      return { valid: false, error: '{{ __('mentor.unsupported_slot_attachment_format') }}' };
    }
    if (file.size > maxSize) {
      return { valid: false, error: '{{ __('mentor.file_excced_10') }}' };
    }
    return { valid: true };
  };

  // Event handlers
  window.fetchSlots = (mentorId, cycleId, organizationId) => {
    if (!mentorId || !cycleId || !organizationId) {
      alert('{{ __('mentor.mentor_not_entrolled_in_cycle') }}');
      return;
    }

    $('#bookingMentorId').val(mentorId);
    $('#bookingCycleId').val(cycleId);
    $('#bookingOrgId').val(organizationId);

    $.ajax({
      url: `/dashboard/availability/slots/${mentorId}`,
      method: 'GET',
      data: { active_only: 'true' },
      dataType: 'json'
    })
    .done(result => {
      state.slots = result?.data || [];
      state.blocked_intervals = result?.blocked_intervals || [];
      state.offlineDisabled = !result?.offline_allowed;
      if (result?.user) $('#mentorName').text(result.user.name || 'Mentor Name');

      $('#bookingModal').modal('show');
      resetCalendar();

      if (!state.offlineDisabled && !$('#filter-buttons button[data-filter="offline"]').length) {
        $('#filter-buttons').append('<button type="button" class="btn btn-xs rounded border border-light" data-filter="offline"><i class="ti ti-map-pin ti-14px me-1"></i>{{ __("mentor.offline") }}</button>');
      }
    })
    .fail(() => alert('{{ __('mentor.could_not_load_availability_slots') }}'));
  };

  $(document).on('click', 'button[data-filter]', function() {
    $('button[data-filter]').removeClass('active btn-primary').addClass('border border-light');
    $(this).addClass('active btn-primary').removeClass('border border-light');
    if (state.selectedDate) renderSlots(state.selectedDate);
  });

  $('#fileUpload').on('change', function() {
    const file = this.files[0];
    if (!file) {
      state.fileValid = true;
      $('#fileError').hide();
      $('#fileName').hide();
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
    // Update submit button based on slot and file validity
    $('#submit-button').prop('disabled', !state.selectedSlot || !state.fileValid);
  });

  $('#bookingFormSubmit').on('submit', function(e) {
    if (!state.selectedSlot) {
      e.preventDefault();
      $('#slotError').text('{{ __('mentor.select_available_slot') }}').show();
      return;
    }

    if (!state.fileValid) {
      e.preventDefault();
      $('#fileError').show();
      return;
    }

    const startTime = $('#sessionStartTime').val();
    const endTime = $('#sessionEndTime').val();

    if (!startTime || !endTime) {
      e.preventDefault();
      $('#slotError').text('{{ __('mentor.please_select_session_times') }}').show();
      return;
    }

    $('#slotError').hide();
    releaseSuccessAlert({
      title: '{{ __('mentor.success') }}',
      message: '{{ __('mentor.session_request_success') }}'
    });

    $('#bookingModal').modal('hide');
    resetForm();

    this.submit();
  });

  $('#bookingModal').on('hidden.bs.modal', resetForm);
});
</script>
@endpush
