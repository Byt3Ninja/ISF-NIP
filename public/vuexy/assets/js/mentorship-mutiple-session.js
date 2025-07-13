'use strict';

const currentLang = document.documentElement.lang;
const en = currentLang === 'en';

document.addEventListener('DOMContentLoaded', function () {
  var timeFromSelect = $('#timeFrom');
  var timeToSelect = $('#timeTo');

  // Populate timeFromSelect and timeToSelect with 30-minute intervals
  for (var i = 0; i < 48; i++) {
    var time = moment()
      .startOf('day')
      .add(i * 30, 'minutes');
    var timeFormatted = time.format('hh:mm A');
    var option = $('<option>').val(time.format('HH:mm:ss')).text(timeFormatted);
    timeFromSelect.append(option.clone());
    timeToSelect.append(option.clone());
  }
});

let responseData = [];

function normalizeResponseDays(response) {
  return response.map(item => {
    if (typeof item.days === 'string') {
      // Convert single string in dddd format to ddd format array
      const dayInDdd = moment(item.days, 'dddd').format('ddd');
      item.days = [dayInDdd];
    } else if (Array.isArray(item.days)) {
      // Convert each day in the array to ddd format
      item.days = item.days.map(day => moment(day, 'ddd').format('ddd'));
    }
    return item;
  });
}

function request(id, mentor, organization_id = null, cycle_id = null) {
  
  $('#mentorid').val(id);
  $('#rname').html(mentor);
  
    const url = new URL(window.location);
    url.searchParams.set('id', cycle_id);
    history.pushState({}, '', url); // This updates the URL without reload

  let _token = $('meta[name="csrf-token"]').attr('content');
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': _token } });
  $.ajax({
    type: 'POST',
    url: '/dashboard/mentors/modify',
    data: { id: id, rtype: 5, _token: _token },
    success: function (response) {
      responseData = normalizeResponseDays(response); // Normalize days
      // console.log('response', responseData);

      $('#addOrganizationID').val(organization_id);
      $('#addCycleID').val(cycle_id);

      onSuccess(responseData);
    },
    error: function (error) {
      // console.log(error);
      alert(en ? 'Error Occurred, Try again!' : 'حدث خطأ، حاول مرة أخرى!');
    }
  });
}

function onSuccess(response) {
  $('#SendRequest').modal('show');
  $('#SendRequest li').remove();

  // Clear date value when modal shows
  $('#flatpickr-datetimeLimit').val('').removeClass('is-invalid');
  $('.date-error').hide();
  
  // Clear time dropdowns
  $('#timeFrom').empty();
  $('#timeTo').empty();
  
  // Hide duration container and feedback messages
  $('#duration-container').removeClass('d-flex').addClass('d-none');
  $('#time-feedback').hide();
  
  // Clear selected dates container
  $('#selected-dates-container').empty().addClass('d-none');

  renderScheduleData(response);
}

function renderScheduleData(response) {
  var flatpickrDateTimeLimit = $('#flatpickr-datetimeLimit');
  if (response && response.length > 0) {
    // Date
    $.each(response, function (i, item) {
      if (flatpickrDateTimeLimit) {
        var enabledDays = response.reduce((acc, item) => {
          return acc.concat(item?.days?.filter(day => day !== undefined && day !== null));
        }, []);

        // console.log('enabledDays', enabledDays);

        flatpickrDateTimeLimit.flatpickr({
          dateFormat: 'Y-m-d',
          minDate: 'today',
          mode: 'multiple',
          enable: [
            function (date) {
              return enabledDays.includes(date.toLocaleString('en-US', { weekday: 'short' }));
            }
          ],
          onChange: function(selectedDates, dateStr) {
            handleMultipleDateSelection(selectedDates);
          }
        });
      }

      let note = '';
      let time = item.tfrom + (en ? ' To ' : ' الي ') + item.tto;
      if (item.notes) {
        note = item.notes;
      }
      switch (item.type) {
        case 'OneDay':
          $('<li>')
        .html('<b>' + item.dfrom + '</b> (' + moment(item.tfrom, 'HH:mm:ss').format('hh:mm A') + ' - ' + moment(item.tto, 'HH:mm:ss').format('hh:mm A') + ').')
        .appendTo('#schedule');
          break;
        case 'Weekly':
          $('<li>')
        .html('<b>' + (en ? 'Weekly' : 'أسبوعي') + '</b> (' + item.days.join(', ') + ') - (' + moment(item.tfrom, 'HH:mm:ss').format('hh:mm A') + ' - ' + moment(item.tto, 'HH:mm:ss').format('hh:mm A') + ').')
        .appendTo('#schedule');
          break;
        case 'WorkingDays':
          $('<li>')
        .html('<b>' + (en ? 'All working days' : 'جميع أيام العمل') + '</b> (' + moment(item.tfrom, 'HH:mm:ss').format('hh:mm A') + ' - ' + moment(item.tto, 'HH:mm:ss').format('hh:mm A') + ').')
        .appendTo('#schedule');
          break;
        case 'Period':
          $('<li>')
        .html(
          '<b>' + (en ? 'From ' : 'من ') + item.dfrom + (en ? ' To ' : ' إلى ') + item.dto + '</b> (' + moment(item.tfrom, 'HH:mm:ss').format('hh:mm A') + ' - ' + moment(item.tto, 'HH:mm:ss').format('hh:mm A') + ').'
        )
        .appendTo('#schedule');
          break;
        default:
      }
    });
  } else {
    $('<li>')
      .html(en ? 'No records saved.' : 'لا توجد سجلات محفوظة.')
      .appendTo('#schedule');

    // Date
    if (flatpickrDateTimeLimit) {
      flatpickrDateTimeLimit.flatpickr({
        dateFormat: 'Y-m-d',
        minDate: 'today',
        mode: 'multiple',
        onChange: function(selectedDates, dateStr) {
          handleMultipleDateSelection(selectedDates);
        }
      });
    }
  }
}

// Legacy form submission validation replaced with multiple date handling

// Global variable to store selected date-time combinations
let selectedDateTimes = [];

// Handle multiple date selection
function handleMultipleDateSelection(selectedDates) {
  $('#flatpickr-datetimeLimit').removeClass('is-invalid');
  $('.date-error').hide();
  
  // Clear old time dropdowns
  $('#timeFrom').empty();
  $('#timeTo').empty();
  $('#duration-container').removeClass('d-flex').addClass('d-none');
  
  if (selectedDates.length === 0) {
    $('#selected-dates-container').empty().addClass('d-none');
    selectedDateTimes = [];
    return;
  }
  
  // Show selected dates container
  $('#selected-dates-container').removeClass('d-none');
  renderSelectedDates(selectedDates);
}

// Render selected dates with their time options
function renderSelectedDates(selectedDates) {
  const container = $('#selected-dates-container');
  container.empty();
  
  // Reset selectedDateTimes array
  selectedDateTimes = [];
  
  selectedDates.forEach((date, index) => {
    const dateStr = moment(date).format('YYYY-MM-DD');
    const dayName = moment(date).format('ddd');
    const displayDate = moment(date).format('MMM DD, YYYY');
    
    // Find available time slots for this date
    const filteredDateObjs = responseData.filter(item => {
      const modifiedDays = item?.days?.filter((day) => day !== undefined && day !== null);
      return modifiedDays && modifiedDays.includes(dayName);
    });
    
    // Initialize date-time object
    selectedDateTimes.push({
      date: dateStr,
      timeFrom: '',
      timeTo: '',
      isValid: false
    });
    
    // Create date container
    const dateContainer = $(`
      <div class="border rounded p-3 mb-3" data-date-index="${index}">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="mb-0 text-primary">${displayDate} (${dayName})</h6>
          <button type="button" class="btn btn-xs btn-icon btn-label-danger remove-date-btn" data-date-index="${index}">
            <i class="ti ti-x ti-xs"></i>
          </button>
        </div>
        <div class="row">
          <div class="col-6">
            <label class="form-label fw-semibold fs-6 mb-1">${en ? 'From:' : 'من:'}</label>
            <select class="form-select time-from-select" data-date-index="${index}">
              <option value="">${en ? 'Select time' : 'اختر الوقت'}</option>
            </select>
            <div class="invalid-feedback"></div>
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold fs-6 mb-1">${en ? 'To:' : 'إلى:'}</label>
            <select class="form-select time-to-select" data-date-index="${index}">
              <option value="">${en ? 'Select time' : 'اختر الوقت'}</option>
            </select>
            <div class="invalid-feedback"></div>
          </div>
        </div>
        <div class="duration-display mt-2 d-none">
          <small class="text-muted">${en ? 'Duration:' : 'المدة:'} <span class="duration-value"></span></small>
        </div>
      </div>
    `);
    
    container.append(dateContainer);
    
    // Populate time options for this date
    populateTimeOptionsForDate(index, filteredDateObjs, date);
  });
  
  // Bind event handlers for time selects
  bindTimeSelectHandlers();
  
  // Bind remove date handlers
  bindRemoveDateHandlers();
}

// Populate time options for a specific date
function populateTimeOptionsForDate(dateIndex, filteredDateObjs, selectedDate) {
  const timeFromSelect = $(`.time-from-select[data-date-index="${dateIndex}"]`);
  const timeToSelect = $(`.time-to-select[data-date-index="${dateIndex}"]`);
  
  if (filteredDateObjs.length > 0) {
    // Merge and sort time periods
    const mergedPeriods = mergeTimePeriods(filteredDateObjs);
    
    // Check if selected date is today
    const isToday = moment(selectedDate).isSame(moment(), 'day');
    
    // Generate time slots for "From" dropdown
    let fromTimeSlots = generateFromTimeSlots(mergedPeriods);
    
    // Filter out past times if the selected date is today
    if (isToday) {
      const currentTime = moment();
      fromTimeSlots = fromTimeSlots.filter(slot => 
        slot.moment.isAfter(currentTime)
      );
    }
    
    // Populate timeFrom dropdown
    fromTimeSlots.forEach(timeSlot => {
      const option = $('<option>').val(timeSlot.value).text(timeSlot.display);
      timeFromSelect.append(option);
    });
    
  } else {
    // No availability found
    timeFromSelect.append(
      $('<option>')
        .val('')
        .text(en ? 'No available times' : 'لا توجد أوقات متاحة')
        .prop('disabled', true)
    );
    
    timeToSelect.append(
      $('<option>')
        .val('')
        .text(en ? 'No available times' : 'لا توجد أوقات متاحة')
        .prop('disabled', true)
    );
  }
}

// Bind event handlers for time selects
function bindTimeSelectHandlers() {
  // Handle timeFrom change
  $('.time-from-select').off('change').on('change', function() {
    const dateIndex = parseInt($(this).data('date-index'));
    const timeFromValue = $(this).val();
    const timeToSelect = $(`.time-to-select[data-date-index="${dateIndex}"]`);
    
    // Update selectedDateTimes
    selectedDateTimes[dateIndex].timeFrom = timeFromValue;
    selectedDateTimes[dateIndex].isValid = false;
    
    // Clear and update timeTo options
    timeToSelect.empty().append('<option value="">' + (en ? 'Select time' : 'اختر الوقت') + '</option>');
    
    if (timeFromValue) {
      updateTimeToOptionsForDate(dateIndex, timeFromValue);
    }
    
    // Hide duration display
    $(`.duration-display[data-date-index="${dateIndex}"]`).addClass('d-none');
  });
  
  // Handle timeTo change
  $('.time-to-select').off('change').on('change', function() {
    const dateIndex = parseInt($(this).data('date-index'));
    const timeToValue = $(this).val();
    const timeFromValue = selectedDateTimes[dateIndex].timeFrom;
    
    // Update selectedDateTimes
    selectedDateTimes[dateIndex].timeTo = timeToValue;
    
    if (timeFromValue && timeToValue) {
      // Validate time selection
      if (moment(timeToValue, 'HH:mm:ss').isAfter(moment(timeFromValue, 'HH:mm:ss'))) {
        const duration = moment.duration(moment(timeToValue, 'HH:mm:ss').diff(moment(timeFromValue, 'HH:mm:ss')));
        const minutes = duration.asMinutes();
        
        if (minutes >= 30) {
          selectedDateTimes[dateIndex].isValid = true;
          
          // Show duration
          const durationDisplay = $(this).closest('.border').find('.duration-display');
          durationDisplay.removeClass('d-none');
          durationDisplay.find('.duration-value').text(`${minutes} ${en ? 'minutes' : 'دقائق'}`);
          
          // Remove error styling
          $(this).removeClass('is-invalid');
        } else {
          selectedDateTimes[dateIndex].isValid = false;
          $(this).addClass('is-invalid');
          $(this).next('.invalid-feedback').text(en ? 'Minimum 30 minutes required' : 'مطلوب 30 دقيقة على الأقل');
        }
      } else {
        selectedDateTimes[dateIndex].isValid = false;
        $(this).addClass('is-invalid');
        $(this).next('.invalid-feedback').text(en ? 'End time must be after start time' : 'يجب أن يكون وقت الانتهاء بعد وقت البدء');
      }
    }
  });
}

// Update timeTo options for a specific date
function updateTimeToOptionsForDate(dateIndex, timeFromValue) {
  const timeToSelect = $(`.time-to-select[data-date-index="${dateIndex}"]`);
  const selectedDate = selectedDateTimes[dateIndex].date;
  
  const dayName = moment(selectedDate, 'YYYY-MM-DD').format('ddd');
  const filteredDateObjs = responseData.filter(item => item.days && item.days.includes(dayName));
  
  if (filteredDateObjs.length === 0) return;

  // Merge time periods and generate all slots for "To"
  const mergedPeriods = mergeTimePeriods(filteredDateObjs);
  let allToTimeSlots = generateToTimeSlots(mergedPeriods);

  // Parse the selected from time
  const selectedFromTime = moment(timeFromValue, 'HH:mm:ss');

  // Check if selected date is today
  const isToday = moment(selectedDate, 'YYYY-MM-DD').isSame(moment(), 'day');
  
  // Filter out past times if the selected date is today
  if (isToday) {
    const currentTime = moment();
    allToTimeSlots = allToTimeSlots.filter(slot => 
      slot.moment.isAfter(currentTime)
    );
  }

  // Find valid end times (at least 30 minutes after the selected from time)
  const validEndSlots = allToTimeSlots.filter(slot => 
    slot.moment.isAfter(selectedFromTime.clone().add(29, 'minutes'))
  );

  // Populate timeTo dropdown with valid options
  validEndSlots.forEach(slot => {
    const option = $('<option>').val(slot.value).text(slot.display);
    timeToSelect.append(option);
  });
}

// Remove the old single date change handler and replace with a placeholder
$('#flatpickr-datetimeLimit').off('change');

// Function to merge overlapping or consecutive time periods
function mergeTimePeriods(periods) {
  if (periods.length === 0) return [];

  // Convert periods to moments and sort by start time
  const sortedPeriods = periods
    .filter(period => period.tfrom && period.tto)
    .map(period => ({
      start: moment(period.tfrom, 'HH:mm:ss'),
      end: moment(period.tto, 'HH:mm:ss')
    }))
    .sort((a, b) => a.start.diff(b.start));

  const merged = [];
  let current = sortedPeriods[0];

  for (let i = 1; i < sortedPeriods.length; i++) {
    const next = sortedPeriods[i];
    
    // Check if periods overlap or are consecutive (touching)
    if (current.end.isSameOrAfter(next.start)) {
      // Merge periods by extending the end time
      current.end = moment.max(current.end, next.end);
    } else {
      // No overlap, add current period to merged and move to next
      merged.push(current);
      current = next;
    }
  }
  
  // Add the last period
  merged.push(current);
  
  return merged;
}

// Function to generate time slots for "From" dropdown (excludes end times)
function generateFromTimeSlots(mergedPeriods) {
  const allSlots = [];
  
  mergedPeriods.forEach(period => {
    let currentTime = period.start.clone();
    
    // Generate 30-minute intervals within this period, but exclude the very end
    while (currentTime.isBefore(period.end)) {
      allSlots.push({
        value: currentTime.format('HH:mm:ss'),
        display: currentTime.format('hh:mm A'),
        moment: currentTime.clone()
      });
      currentTime.add(30, 'minutes');
    }
  });
  
  // Remove duplicates and sort
  const uniqueSlots = allSlots.filter((slot, index, self) => 
    index === self.findIndex(s => s.value === slot.value)
  ).sort((a, b) => a.moment.diff(b.moment));
  
  return uniqueSlots;
}

// Function to generate time slots for "To" dropdown (includes end times)
function generateToTimeSlots(mergedPeriods) {
  const allSlots = [];
  
  mergedPeriods.forEach(period => {
    let currentTime = period.start.clone();
    
    // Generate 30-minute intervals within this period
    while (currentTime.isBefore(period.end)) {
      allSlots.push({
        value: currentTime.format('HH:mm:ss'),
        display: currentTime.format('hh:mm A'),
        moment: currentTime.clone()
      });
      currentTime.add(30, 'minutes');
    }
    
    // Always add the end time for "To" options
    allSlots.push({
      value: period.end.format('HH:mm:ss'),
      display: period.end.format('hh:mm A'),
      moment: period.end.clone()
    });
  });
  
  // Remove duplicates and sort
  const uniqueSlots = allSlots.filter((slot, index, self) => 
    index === self.findIndex(s => s.value === slot.value)
  ).sort((a, b) => a.moment.diff(b.moment));
  
  return uniqueSlots;
}

// Function to update timeTo options based on selected timeFrom
function updateTimeToOptions(timeFromValue) {
  const timeToSelect = $('#timeTo');
  const selectedDate = $('#flatpickr-datetimeLimit').val();
  
  if (!selectedDate || !timeFromValue) return;

  const dayName = moment(selectedDate, 'YYYY-MM-DD').format('ddd');
  const filteredDateObjs = responseData.filter(item => item.days && item.days.includes(dayName));
  
  if (filteredDateObjs.length === 0) return;

  // Clear current options
  timeToSelect.empty();

  // Merge time periods and generate all slots for "To" (includes end times)
  const mergedPeriods = mergeTimePeriods(filteredDateObjs);
  let allToTimeSlots = generateToTimeSlots(mergedPeriods);

  // Parse the selected from time
  const selectedFromTime = moment(timeFromValue, 'HH:mm:ss');

  // Check if selected date is today
  const isToday = moment(selectedDate, 'YYYY-MM-DD').isSame(moment(), 'day');
  
  // Filter out past times if the selected date is today
  if (isToday) {
    const currentTime = moment();
    allToTimeSlots = allToTimeSlots.filter(slot => 
      slot.moment.isAfter(currentTime)
    );
  }

  // Find valid end times (at least 30 minutes after the selected from time)
  const validEndSlots = allToTimeSlots.filter(slot => 
    slot.moment.isAfter(selectedFromTime.clone().add(29, 'minutes'))
  );

  // Populate timeTo dropdown with valid options
  validEndSlots.forEach(slot => {
    const option = $('<option>').val(slot.value).text(slot.display);
    timeToSelect.append(option);
  });

  // Auto-select the first available option if none is selected
  if (timeToSelect.val() === null && timeToSelect.children().length > 0) {
    timeToSelect.val(timeToSelect.find('option:first').val());
  }
}

// Bind event handlers for remove date buttons
function bindRemoveDateHandlers() {
  $('.remove-date-btn').off('click').on('click', function() {
    const dateIndex = parseInt($(this).data('date-index'));
    const dateToRemove = selectedDateTimes[dateIndex].date;
    
    // Remove from selectedDateTimes array
    selectedDateTimes.splice(dateIndex, 1);
    
    // Remove from flatpickr selection
    const flatpickrInstance = $('#flatpickr-datetimeLimit')[0]._flatpickr;
    if (flatpickrInstance) {
      const currentDates = flatpickrInstance.selectedDates.filter(date => {
        return moment(date).format('YYYY-MM-DD') !== dateToRemove;
      });
      flatpickrInstance.setDate(currentDates, false);
    }
    
    // Re-render the selected dates
    renderSelectedDates(flatpickrInstance ? flatpickrInstance.selectedDates : []);
  });
}

$('#session-form').on('submit', e => {
  // Validate multiple date-time selections
  if (selectedDateTimes.length === 0) {
    e.preventDefault();
    $('#flatpickr-datetimeLimit').addClass('is-invalid').trigger('focus');
    $('.date-error')
      .text(en ? 'Please select at least one date.' : 'يرجى اختيار تاريخ واحد على الأقل.')
      .show();
    return;
  }
  
  // Check if all selected dates have valid time selections
  const invalidDates = selectedDateTimes.filter(dt => !dt.isValid || !dt.timeFrom || !dt.timeTo);
  if (invalidDates.length > 0) {
    e.preventDefault();
    $('#time-feedback').text(en ? 'Please complete time selection for all dates.' : 'يرجى إكمال اختيار الوقت لجميع التواريخ.').show();
    return;
  }

  // Add selected date-times to form data
  selectedDateTimes.forEach((dt, index) => {
    $('<input>').attr({
      type: 'hidden',
      name: `selected_dates[${index}][date]`,
      value: dt.date
    }).appendTo('#session-form');
    
    $('<input>').attr({
      type: 'hidden',
      name: `selected_dates[${index}][time_from]`,
      value: dt.timeFrom
    }).appendTo('#session-form');
    
    $('<input>').attr({
      type: 'hidden',
      name: `selected_dates[${index}][time_to]`,
      value: dt.timeTo
    }).appendTo('#session-form');
  });

  // Also set the first date and time as the primary date/time for backward compatibility
  if (selectedDateTimes.length > 0) {
    $('input[name="date"]').val(selectedDateTimes[0].date);
    
    // Add primary timeFrom and timeTo as hidden fields if they don't exist
    if ($('input[name="timeFrom"]').length === 0) {
      $('<input>').attr({
        type: 'hidden',
        name: 'timeFrom',
        value: selectedDateTimes[0].timeFrom
      }).appendTo('#session-form');
    }
    
    if ($('input[name="timeTo"]').length === 0) {
      $('<input>').attr({
        type: 'hidden',
        name: 'timeTo',
        value: selectedDateTimes[0].timeTo
      }).appendTo('#session-form');
    }
  }

  // Hide any error messages if validation passes
  $('#time-feedback').hide();
  $('.date-error').hide();
});

// Close schedule container functionality
$(document).on('click', '#close-schedule-btn', function() {
  $('#mentor-schedule-container').slideUp(300);
});

// Show schedule container when mentor is selected (optional - for better UX)
function showScheduleContainer() {
  $('#mentor-schedule-container').slideDown(300);
}