<style>
  .flatpickr-calendar {
    box-shadow: none !important;
    border: 1px solid #e6e6e8 !important;
  }
</style>

@if (isset($mentor_id))
<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="bookingFormSubmit" method="post" action="/dashboard/mentorship/addwait" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header border-bottom py-4">
        <div>
          <h5 class="modal-title fw-semibold">
            {{ __('dashboard.Add mentorship session with') }} { <span id="mentorName"></span> }
          </h5>
          <div class="text-muted small mt-1">{{ __('mentor.select_date_and_slot') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      @csrf
      
      <input type="hidden" name="mentorid" value="{{ $mentor_id }}">
      @if (isset($user_id))
        <input type="hidden" name="user_id" value="{{ $user_id }}">
      @endif
      @if (isset($organization_id))
        <input type="hidden" name="organization_id" value="{{ $organization_id }}">
      @endif
      @if (isset($cycle_id))
        <input type="hidden" name="cycle_id" value="{{ $cycle_id }}">
      @endif
      <input type="hidden" name="date" id="selectedDateValue">
      <input type="hidden" name="timeFrom" id="selectedStartTime">
      <input type="hidden" name="timeTo" id="selectedEndTime">
      <input type="hidden" name="session_type" id="selectedSessionType">

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
              <div class="d-flex gap-2 mb-3">
                <button class="btn btn-xs btn-primary rounded active" data-filter="all">{{ __('admin.All') }}</button>
                <button class="btn btn-xs rounded border border-light" data-filter="online">
                  <i class="ti ti-video ti-14px me-1"></i>
                  {{ __('mentor.online') }}
                </button>
                <button class="btn btn-xs rounded border border-light" data-filter="offline">
                  <i class="ti ti-map-pin ti-14px me-1"></i>
                  {{ __('mentor.offline') }}
                </button>
              </div>
              <div id="slotsList" style="width: 100%; max-height: 320px; overflow-y: auto;">
                <div class="text-center text-muted py-5 small">{{ __('admin.loading_slots') }}...</div>
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
                <i class="ti ti-hourglass-empty"></i>
                <div>
                  <div class="small">{{ __('admin.duration') }}</div>
                  <div class="fw-medium" id="detailDuration">-</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex gap-2">
                <i class="ti ti-clock"></i>
                <div>
                  <div class="small">{{ __('mentor.time') }}</div>
                  <div class="fw-medium" id="detailTime">-</div>
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
            <div class="fw-medium">
              {{ __('mentor.single_dropzone_description') }}
            </div>
            <div class="small text-muted mt-1">PDF, DOC, DOCX, PPT, XLS, PNG, JPEG up to 10MB</div>
          </div>
          <input type="file" name="file_url" id="fileUpload" class="d-none" accept=".pdf,.doc,.docx,.ppt,.xls,.png,.jpeg,.jpg">
          <div id="fileError" class="invalid-feedback mt-2" style="display: none;"></div>
          <div id="fileName" class="small mt-2" style="display: none;"></div>
        </div>

        <!-- Additional Notes -->
        <div class="mt-3">
          <label for="additionalNotes" class="form-label mb-2">
            {{ $l == 'en' ? 'Additional Notes' : 'ملاحظات إضافية' }}
            <span class="text-muted fw-normal">({{ __('mentor.optional') }})</span>
          </label>
          <textarea class="form-control" name="notes" id="additionalNotes" rows="3" maxlength="1000"
            placeholder="{{ __('mentor.slot_notes_placeholder') }}..."></textarea>
        </div>
      </div>
      <div class="modal-footer border-top py-4">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-2"></i> {{ __('admin.Cancel') }}
        </button>
        <button type="submit" class="btn btn-primary">
          <i class="ti ti-send me-2"></i> {{ __('admin.Submit') }}
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>

<script>
$(document).ready(function() {
  let availableSlots = [];
  let selectedSlot = null;
  let selectedDate = null;
  let flatpickrInstance = null;
  let offlineDisabled = false;

  // Open booking modal on button click
  // $('#openBookingBtn').on('click', function() {
  //   const mentorId = $(this).data('id');
  //   fetchSlots(mentorId);
  // });

  window.fetchSlots = function(mentorId) {
    $.ajax({
      url: `/dashboard/availability/slots/${mentorId || ''}`,
      method: 'GET',
      data: { active_only: 'true' },
      dataType: 'json'
    })
    .done(function(result) {
      console.log('Fetched slots:', result?.data || []);
      availableSlots = result?.data || [];

      if (result?.user) {
        $('#mentorName').text(result.user.name || 'Mentor Name');
      }

      if (result?.data) {
        $('#bookingModal').modal('show');
        if (!flatpickrInstance) {
          initializeCalendar();
        }
      }
    })
    .fail(function() {
      alert('{{ __('mentor.could_not_load_availability_slots') }}');
    });
  }

  function getAvailableDates() {
  const dates = new Set();
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const endDate = new Date();
  endDate.setMonth(endDate.getMonth() + 3);

  availableSlots.forEach(slot => {
    if (slot.date) {
      const slotDate = new Date(slot.date);
      slotDate.setHours(0, 0, 0, 0);
      if (slotDate >= today) {
        dates.add(slot.date);
      }
    }
  });

  for (let d = new Date(today); d <= endDate; d.setDate(d.getDate() + 1)) {
    const dayName = d.toLocaleDateString('en-US', { weekday: 'long' });
    const hasWeeklySlot = availableSlots.some(slot =>
      slot.weekly && slot.weekday === dayName
    );
    if (hasWeeklySlot) {
      dates.add(d.toISOString().split('T')[0]);
    }
  }

  return Array.from(dates);
}

function getSlotsForDate(dateStr) {
  const date = new Date(dateStr);
  const dayName = date.toLocaleDateString('en-US', { weekday: 'long' });
  const slots = [];

  availableSlots.forEach(slot => {
    if (slot.date === dateStr) {
      slots.push(slot);
    } else if (slot.weekly && slot.weekday === dayName) {
      slots.push({ ...slot, date: dateStr });
    }
  });

  return slots;
}

function calculateDuration(startTime, endTime) {
  const [startHour, startMin] = startTime.split(':').map(Number);
  const [endHour, endMin] = endTime.split(':').map(Number);
  return (endHour * 60 + endMin) - (startHour * 60 + startMin);
}

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
}

  // Render available time slots for a selected date
  function renderSlots(dateStr) {
    const $container = $('#slotsList');
    const filter = $('button[data-filter].active').data('filter');
    let slots = getSlotsForDate(dateStr);

    // Filter slots based on offline disabled flag and user filter
    if (offlineDisabled) {
      slots = slots.filter(slot => slot.session_type === 'Online');
    }

    if (filter === 'online') {
      slots = slots.filter(slot => slot.session_type === 'Online');
    } else if (filter === 'offline') {
      slots = slots.filter(slot => slot.session_type === 'Offline');
    }

    if (slots.length === 0) {
      $container.html('<div class="text-center text-muted py-5 small">{{ __('mentor.no_available_slots_for_this_date') }}</div>');
      return;
    }

    // Generate HTML for slots
    const slotsHtml = slots.map((slot, index) => {
      const duration = calculateDuration(slot.start_time, slot.end_time);
      const icon = slot.session_type === 'Online'
        ? '<i class="ti ti-video ti-14px"></i>'
        : slot.session_type === 'Offline'
          ? '<i class="ti ti-map-pin ti-14px"></i>'
          : '<i class="ti ti-screen-share ti-14px"></i>';

      const sessionTypeMap = {
        Online: '{{ __('mentor.online') }}',
        Offline: '{{ __('mentor.offline') }}',
        Both: '{{ __('mentor.both') }}'
      };

      const locationText = slot.session_type === 'Offline' && slot.location ? slot.location : sessionTypeMap[slot.session_type ?? 'Both'];

      return `
        <div class="card shadow-none mb-2 border slot-card" data-slot-index="${index}">
          <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
              <div class="flex-grow-1">
                <div class="fw-semibold mb-1 d-flex align-items-center gap-2">
                  <i class="ti ti-clock ti-16px"></i>
                  ${slot.start_time} - ${slot.end_time}
                </div>
                <div class="d-flex align-items-center gap-2 small">
                  <i class="ti ti-hourglass-empty ti-16px"></i> ${duration} @lang('mentor.min')
                  <div class="badge bg-light-subtle d-flex align-items-center gap-1 text-dark rounded px-2">${icon} ${locationText}</div>
                </div>
              </div>
              <input type="radio" class="form-check-input" name="slot" value="${index}">
            </div>
          </div>
        </div>
      `;
    }).join('');

    $container.html(slotsHtml);

    // Attach change event to radio buttons
    $container.find('input[name="slot"]').each(function(index) {
      $(this).on('change', function() {
        const $cardElement = $(this).closest('.slot-card');
        selectSlot(slots[index], $cardElement);
      });
    });
  }

  // Select a time slot and update UI
  function selectSlot(slot, $cardElement) {
    $('.slot-card').removeClass('selected');
    $cardElement.addClass('selected');

    selectedSlot = slot;

    const duration = calculateDuration(slot.start_time, slot.end_time);
    $('#detailDate').text(formatDate(selectedDate));
    $('#detailTime').text(`${slot.start_time} - ${slot.end_time}`);
    $('#detailDuration').text(`${duration} minutes`);
    $('#detailType').text(slot.session_type === 'Online' ? '{{ __('mentor.online_video_call') }}' : slot.session_type);

    $('#selectedStartTime').val(slot.start_time);
    $('#selectedEndTime').val(slot.end_time);
    $('#selectedSessionType').val(slot.session_type);

    $('#sessionDetails').show();
    $('#slotError').hide();
  }

  function initializeCalendar() {
    const availableDates = getAvailableDates();
    const today = new Date().toISOString().split('T')[0];

    flatpickrInstance = flatpickr("#calendar", {
      inline: true,
      locale: '{{ $l }}',
      minDate: "today",
      defaultDate: today,
      dateFormat: "Y-m-d",
      enable: availableDates,
      onChange: function (selectedDates, dateStr) {
        selectedDate = dateStr;
        selectedSlot = null;
        renderSlots(dateStr);
        $('#sessionDetails').hide();
        $('#slotsDate').text(formatDate(dateStr));
        $('#slotError').hide();
        $('#selectedDateValue').val(dateStr || today);
      },
      onDayCreate: function (dObj, dStr, fp, dayElem) {
        const dateStr = dayElem.dateObj.toISOString().split('T')[0];
        if (availableDates.includes(dateStr)) {
          $(dayElem).addClass('available');
        } else {
          const todayDate = new Date();
          todayDate.setHours(0, 0, 0, 0);
          if (dayElem.dateObj >= todayDate) {
            $(dayElem).addClass('unavailable');
          }
        }
      }
    });

    // Set initial date and load slots
    selectedDate = today;
    $('#slotsDate').text(formatDate(today));
    renderSlots(today);
  }

  // Filter buttons
  $('button[data-filter]').on('click', function () {
    $('button[data-filter]').removeClass('active');
    $(this).addClass('active');
    if (selectedDate) {
      renderSlots(selectedDate);
    }
  });

  // File upload
  $('#fileUpload').on('change', function () {
    const file = this.files[0];
    const $fileError = $('#fileError');
    const $fileName = $('#fileName');

    if (!file) return;

    const allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'xls', 'png', 'jpeg', 'jpg'];
    const fileExtension = file.name.split('.').pop().toLowerCase();
    const maxSize = 10 * 1024 * 1024;

    if (!allowedExtensions.includes(fileExtension)) {
      $fileError.text('{{ __('mentor.unsupported_slot_attachment_format') }}').show();
      $fileName.hide();
      $(this).val('');
      return;
    }

    if (file.size > maxSize) {
      $fileError.text('{{ __('mentor.file_excced_10') }}').show();
      $fileName.hide();
      $(this).val('');
      return;
    }

    $fileError.hide();
    $fileName.html(`<i class="ti ti-paperclip me-1"></i>${file.name}`).show();
  });

  // Submit
  $('#bookingFormSubmit').on('submit', function (event) {
    event.preventDefault();
    const $slotError = $('#slotError');

    if (!selectedSlot) {
      $slotError.text('{{ __('mentor.select_available_slot') }}').show();
      return;
    }

    $slotError.hide();

    const bookingData = {
      date: selectedDate,
      startTime: selectedSlot.start_time,
      endTime: selectedSlot.end_time,
      duration: calculateDuration(selectedSlot.start_time, selectedSlot.end_time),
      sessionType: selectedSlot.session_type,
      location: selectedSlot.location || null,
      file: $('#fileUpload')[0].files[0] || null,
      notes: $('#additionalNotes').val().trim()
    };

    releaseSuccessAlert({
      title: '{{ __('mentor.success') }}',
      message: '{{ __('mentor.session_request_success') }}',
    });

    $('#bookingModal').modal('hide');
    resetForm();
  });

  function resetForm() {
    selectedSlot = null;
    $('#fileUpload').val('');
    $('#additionalNotes').val('');
    $('#fileName').hide();
    $('#fileError').hide();
    $('#sessionDetails').hide();
    $('#slotError').hide();
    $('button[data-filter]').each(function(index) {
      $(this).toggleClass('active', index === 0);
    });

    // Reset to today's date
    const today = new Date().toISOString().split('T')[0];
    selectedDate = today;

    // Destroy and reinitialize the calendar to reset to today
    if (flatpickrInstance && typeof flatpickrInstance.destroy === 'function') {
      flatpickrInstance.destroy();
      flatpickrInstance = null;
    }

    // Reinitialize the calendar with today's date
    initializeCalendar();
  }

  $('#bookingModal').on('hidden.bs.modal', resetForm);
});
</script>
@endpush

@endif
