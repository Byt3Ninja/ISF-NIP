'use strict';

const currentLang = document.documentElement.lang;
const en = currentLang === 'en';

let allSlots = [];
let blockedIntervals = [];
let offlineAllowed = false;
let startups = [];
let flatpickrInstance = null;

function request(id, mentor, organization_id = null, cycle_id = null) {
  $('#mentorid').val(id);
  $('#rname').html(mentor);

  const url = new URL(window.location);
  url.searchParams.set('id', cycle_id);
  history.pushState({}, '', url);

  let _token = $('meta[name="csrf-token"]').attr('content');
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': _token } });
  
  // Fetch predefined slots for the mentor
  $.ajax({
    type: 'GET',
    url: `/dashboard/availability/slots/${id}?cycle_id=${cycle_id}&organization_id=${organization_id}`,
    success: function (response) {
      if (response.success) {
        allSlots = response.data;
        blockedIntervals = response.blocked_intervals || [];
        offlineAllowed = response.offline_allowed;
        startups = response.startups || [];

        $('#addOrganizationID').val(organization_id);
        $('#addCycleID').val(cycle_id);

        onSuccess(response);
      } else {
        showError(en ? 'Error loading slots.' : 'حدث خطأ أثناء تحميل المواعيد.');
      }
    },
    error: function (error) {
      showError(en ? 'Error Occurred, Try again!' : 'حدث خطأ، حاول مرة أخرى!');
    }
  });
}

function onSuccess(response) {
  $('#SendRequest').modal('show');
  
  // Hide legacy schedule container
  $('#mentor-schedule-container').hide();
  
  // Reset validation and container states
  $('#flatpickr-datetimeLimit').val('');
  $('.date-error').hide();
  $('.slots-error').hide();
  $('#slots-container').removeClass('border-danger').html(`
    <p class="text-muted mb-0 text-center py-4">
      ${en ? 'Please select a date to view available slots' : 'الرجاء اختيار تاريخ لعرض المواعيد المتاحة'}
    </p>
  `);

  // Handle Startup selection dropdown for Admins/Tenant Admins
  const startupContainer = $('#startup-select-container');
  const startupSelect = $('#startup_user_id');
  startupSelect.empty().append(`<option value="">${en ? 'Select a startup' : 'اختر شركة ناشئة'}</option>`);

  if (startups && startups.length > 0) {
    startupContainer.removeClass('d-none');
    startups.forEach(startup => {
      startupSelect.append(`<option value="${startup.id}">${startup.name}</option>`);
    });
    $('#addUserID').prop('disabled', true);
    startupSelect.prop('disabled', false).prop('required', true);
  } else {
    startupContainer.addClass('d-none');
    $('#addUserID').prop('disabled', false);
    startupSelect.prop('disabled', true).prop('required', false);
  }

  // Initialize inline Flatpickr calendar
  if (flatpickrInstance) {
    flatpickrInstance.destroy();
  }

  flatpickrInstance = flatpickr('#inline-calendar', {
    inline: true,
    dateFormat: 'Y-m-d',
    minDate: 'today',
    enable: [
      function (date) {
        const dateStr = moment(date).format('YYYY-MM-DD');
        return getAvailableSlotsForDate(dateStr).length > 0;
      }
    ],
    onChange: function(selectedDates, dateStr) {
      $('#flatpickr-datetimeLimit').val(dateStr);
      $('.date-error').hide();
      renderSlotsForDate(dateStr);
    }
  });
}

function getAvailableSlotsForDate(dateStr) {
  const dayName = moment(dateStr, 'YYYY-MM-DD').format('dddd');
  const dayShort = moment(dateStr, 'YYYY-MM-DD').format('ddd');
  
  // Filter slots matching this date
  let matchingSlots = allSlots.filter(slot => {
    if (slot.weekly) {
      const match = (slot.weekday === dayName || slot.weekday === dayShort);
      if (match && slot.starts_from) {
        return moment(dateStr).isSameOrAfter(moment(slot.starts_from, 'YYYY-MM-DD'));
      }
      return match;
    } else {
      return slot.date === dateStr;
    }
  });

  // Filter out blocked/booked slots
  return matchingSlots.filter(slot => {
    const slotStart = moment(dateStr + ' ' + slot.start_time, 'YYYY-MM-DD HH:mm').unix();
    const slotEnd = moment(dateStr + ' ' + slot.end_time, 'YYYY-MM-DD HH:mm').unix();
    
    const isBlocked = blockedIntervals.some(blocked => {
      return slotStart < blocked.end && slotEnd > blocked.start;
    });
    
    return !isBlocked;
  });
}

function renderSlotsForDate(dateStr) {
  const container = $('#slots-container');
  container.empty();
  $('.slots-error').hide();

  const availableSlots = getAvailableSlotsForDate(dateStr);

  if (availableSlots.length === 0) {
    container.html(`
      <p class="text-muted mb-0 text-center py-4">
        ${en ? 'No available slots for this date' : 'لا توجد مواعيد متاحة لهذا التاريخ'}
      </p>
    `);
    return;
  }

  availableSlots.forEach(slot => {
    const startTime12 = moment(slot.start_time, 'HH:mm').format('hh:mm A');
    const endTime12 = moment(slot.end_time, 'HH:mm').format('hh:mm A');
    const duration = moment(slot.end_time, 'HH:mm').diff(moment(slot.start_time, 'HH:mm'), 'minutes');
    
    const slotValue = JSON.stringify({
      date: dateStr,
      time_from: slot.start_time + ':00',
      time_to: slot.end_time + ':00',
      session_type: slot.session_type
    });

    const isOffline = slot.session_type === 'Offline';
    const addressText = slot.Address || '-';
    
    const mapsLinkHtml = (isOffline && slot.location) ? `
      <div class="mt-2">
        <a href="${slot.location}" target="_blank" class="btn btn-xs btn-label-primary">
          <i class="ti ti-map-pin me-1"></i> ${en ? 'Google Maps' : 'خرائط جوجل'}
        </a>
      </div>
    ` : '';

    const locationHtml = isOffline ? `
      <div class="mt-2 pt-2 border-top small text-muted">
        <div><strong>${en ? 'Address:' : 'العنوان:'}</strong> ${addressText}</div>
        ${mapsLinkHtml}
      </div>
    ` : '';

    const slotCard = $(`
      <div class="card border mb-2 p-2 shadow-none hover-elevation-sm">
        <div class="form-check d-flex align-items-start m-0">
          <input class="form-check-input slot-checkbox me-2 mt-1" type="checkbox" id="slot_${slot.id}_${dateStr}" value='${slotValue}'>
          <label class="form-check-label w-100 cursor-pointer" for="slot_${slot.id}_${dateStr}">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="fw-semibold text-dark">${startTime12} - ${endTime12}</span>
              <span class="badge bg-label-secondary small">${duration} ${en ? 'mins' : 'دقيقة'}</span>
            </div>
            <div>
              <span class="badge ${slot.session_type === 'Online' ? 'bg-label-success' : 'bg-label-warning'}">${slot.session_type}</span>
            </div>
            ${locationHtml}
          </label>
        </div>
      </div>
    `);

    container.append(slotCard);
  });
}

function showError(message) {
  if (typeof Swal !== 'undefined') {
    releaseAlert('danger', {
      title: en ? 'Error' : 'خطأ',
      message: message,
      confirmText: en ? 'OK' : 'موافق'
    });
  } else {
    alert(message);
  }
}

// Form validation and submission
$(document).ready(function() {
  $('#session-form').off('submit').on('submit', function(e) {
    // 1. Validate startup selection for admins
    const startupSelect = $('#startup_user_id');
    if (startupSelect.is(':visible') && !startupSelect.val()) {
      e.preventDefault();
      startupSelect.addClass('is-invalid');
      $('.startup-error').show();
      return;
    } else {
      startupSelect.removeClass('is-invalid');
      $('.startup-error').hide();
    }

    // 2. Validate slot selection
    const checkedSlots = $('.slot-checkbox:checked');
    if (checkedSlots.length === 0) {
      e.preventDefault();
      const errorMsg = en ? 'Please select an available time slot.' : 'يرجى اختيار موعد متاح';
      $('.slots-error').text(errorMsg).show();
      $('#slots-container').addClass('border-danger');
      showError(errorMsg);
      return;
    } else {
      $('.slots-error').hide();
      $('#slots-container').removeClass('border-danger');
    }

    // 3. Validate file format and size
    const fileInput = $('#inputGroupFile01')[0];
    if (fileInput && fileInput.files.length > 0) {
      const file = fileInput.files[0];
      const allowedExtensions = /(\.pdf|\.doc|\.docx|\.ppt|\.xls|\.png|\.jpeg)$/i;
      
      if (!allowedExtensions.exec(file.name)) {
        e.preventDefault();
        showError(en 
          ? 'Unsupported file format. Allowed formats: PDF, DOC, DOCX, PPT, XLS, PNG, JPEG.' 
          : 'صيغة الملف غير مدعومة. الصيغ المسموح بها:\n PDF, DOC, DOCX, PPT, XLS, PNG, JPEG'
        );
        return;
      }
      
      const maxSize = 10 * 1024 * 1024; // 10MB
      if (file.size > maxSize) {
        e.preventDefault();
        showError(en
          ? 'File size exceeds the 10MB limit.'
          : 'يتجاوز حجم الملف الحد الأقصى 10 ميجابايت'
        );
        return;
      }
    }

    // 4. Append selected_dates to form
    $('#session-form').find('input[name^="selected_dates"]').remove();
    
    checkedSlots.each((index, el) => {
      const slotData = JSON.parse($(el).val());
      $('<input>').attr({
        type: 'hidden',
        name: `selected_dates[${index}][date]`,
        value: slotData.date
      }).appendTo('#session-form');
      
      $('<input>').attr({
        type: 'hidden',
        name: `selected_dates[${index}][time_from]`,
        value: slotData.time_from
      }).appendTo('#session-form');
      
      $('<input>').attr({
        type: 'hidden',
        name: `selected_dates[${index}][time_to]`,
        value: slotData.time_to
      }).appendTo('#session-form');
      
      $('<input>').attr({
        type: 'hidden',
        name: `selected_dates[${index}][session_type]`,
        value: slotData.session_type
      }).appendTo('#session-form');
    });

    // Populate legacy parameters using the first slot for compatibility
    const firstSlotData = JSON.parse(checkedSlots.first().val());
    $('#flatpickr-datetimeLimit').val(firstSlotData.date);
    
    const appendOrSet = (name, value) => {
      if ($(`input[name="${name}"]`).length === 0) {
        $('<input>').attr({
          type: 'hidden',
          name: name,
          value: value
        }).appendTo('#session-form');
      } else {
        $(`input[name="${name}"]`).val(value);
      }
    };

    appendOrSet('timeFrom', firstSlotData.time_from);
    appendOrSet('timeTo', firstSlotData.time_to);
    appendOrSet('session_start_time', firstSlotData.time_from);
    appendOrSet('session_end_time', firstSlotData.time_to);
    appendOrSet('session_type', firstSlotData.session_type);

    const duration = moment(firstSlotData.time_to, 'HH:mm:ss').diff(moment(firstSlotData.time_from, 'HH:mm:ss'), 'minutes');
    appendOrSet('period', duration);
  });
});