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

  renderScheduleData(response);
}

function renderScheduleData(response) {
  var flatpickrDateTimeLimit = $('#flatpickr-datetimeLimit');
  if (response && response.length > 0) {
    // Date
    $.each(response, function (i, item) {
      if (flatpickrDateTimeLimit) {
        var enabledDays = response.reduce((acc, item) => {
          return acc.concat(item.days.filter(day => day !== undefined && day !== null));
        }, []);

        // console.log('enabledDays', enabledDays);

        flatpickrDateTimeLimit.flatpickr({
          dateFormat: 'Y-m-d',
          minDate: 'today',
          enable: [
            function (date) {
              return enabledDays.includes(date.toLocaleString('en-US', { weekday: 'short' }));
            }
          ]
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
        minDate: 'today'
      });
    }
  }
}

$('#session-form').on('submit', e => {
  const selectedDateValue = $('#flatpickr-datetimeLimit').val();
  const timeFromValue = $('#timeFrom').val();
  const timeToValue = $('#timeTo').val();

  // Validate date selection
  if (!selectedDateValue) {
    e.preventDefault();
    $('#flatpickr-datetimeLimit').addClass('is-invalid').trigger('focus');
    $('.date-error')
      .text(en ? 'Please select a date.' : 'يرجى اختيار تاريخ.')
      .show();
    return;
  }

  // Validate time selection
  if (!timeFromValue || !timeToValue) {
    e.preventDefault();
    $('#time-feedback').text(en ? 'Please select both start and end times.' : 'يرجى اختيار وقت البدء والانتهاء.').show();
    return;
  }

  // Ensure that timeToValue is greater than timeFromValue
  if (moment(timeToValue, 'HH:mm:ss').isSameOrBefore(moment(timeFromValue, 'HH:mm:ss'))) {
    e.preventDefault();
    $('#time-feedback').text(en ? 'End time must be after start time.' : 'يجب أن يكون وقت الانتهاء بعد وقت البدء.').show();
    return;
  }

  // Ensure minimum duration (at least 30 minutes)
  const duration = moment.duration(moment(timeToValue, 'HH:mm:ss').diff(moment(timeFromValue, 'HH:mm:ss')));
  const minutes = duration.asMinutes();
  
  if (minutes < 30) {
    e.preventDefault();
    $('#time-feedback').text(en ? 'Session must be at least 30 minutes long.' : 'يجب أن تكون الجلسة 30 دقيقة على الأقل.').show();
    return;
  }

  // Hide any error messages if validation passes
  $('#time-feedback').hide();
  $('.date-error').hide();
});

$('#flatpickr-datetimeLimit').on('change', e => {
  // console.log('responseData', responseData);
  // console.log('changed', e.target.value);
  $('#flatpickr-datetimeLimit').removeClass('is-invalid');
  $('.date-error').hide();

  // Get the day name of the selected date
  const selectedDate = e.target.value;
  const dayName = moment(selectedDate, 'YYYY-MM-DD').format('ddd');
  // console.log('Day Name:', dayName);

  // Find ALL schedule entries for the selected day (not just the first one)
  const filteredDateObjs = responseData.filter(item => {
    const modifiedDays = item?.days?.filter((day) => day !== undefined && day !== null);
    // console.log('item.days', modifiedDays);
    return modifiedDays && modifiedDays.includes(dayName);
  });
  // console.log('filteredDateObjs', filteredDateObjs);

  if (filteredDateObjs.length > 0) {
    const timeFromSelect = $('#timeFrom');
    const timeToSelect = $('#timeTo');
  
    // Clear previous options
    timeFromSelect.empty();
    timeToSelect.empty();

    // Merge and sort time periods
    const mergedPeriods = mergeTimePeriods(filteredDateObjs);
    // console.log('mergedPeriods', mergedPeriods);

    // Generate time slots for "From" dropdown (excludes end times)
    const fromTimeSlots = generateFromTimeSlots(mergedPeriods);
    // console.log('fromTimeSlots', fromTimeSlots);

    // Populate timeFrom dropdown with available slots (no end times)
    fromTimeSlots.forEach((timeSlot, index) => {
      const option = $('<option>').val(timeSlot.value).text(timeSlot.display);
      timeFromSelect.append(option);
    });

    // Auto-select first option in timeFrom
    if (fromTimeSlots.length > 0) {
      timeFromSelect.val(fromTimeSlots[0].value);
      // Update timeTo options based on first timeFrom selection
      updateTimeToOptions(fromTimeSlots[0].value);
      
      // Auto-select the first available option in timeTo
      if (timeToSelect.children().length > 0) {
        timeToSelect.val(timeToSelect.find('option:first').val()).trigger('change');
      }
    }
  }
});

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
  const allToTimeSlots = generateToTimeSlots(mergedPeriods);

  // Parse the selected from time
  const selectedFromTime = moment(timeFromValue, 'HH:mm:ss');

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

$('#timeFrom').on('change', (e) => {
  const timeFromValue = e.target.value;
  const timeToValue = $('#timeTo').val();
  const timeToSelect = $('#timeTo');

  if (!timeFromValue) return;

  // Update the timeTo options to only show times after timeFrom
  updateTimeToOptions(timeFromValue);

  // Check if current timeTo value is still valid (after timeFrom)
  if (timeToValue && moment(timeFromValue, 'HH:mm:ss').isSameOrAfter(moment(timeToValue, 'HH:mm:ss'))) {
    // Find the next available time slot (at least 30 minutes after timeFrom)
    const nextAvailableTime = moment(timeFromValue, 'HH:mm:ss').add(30, 'minutes');
    const nextOption = timeToSelect.find(`option[value="${nextAvailableTime.format('HH:mm:ss')}"]`);
    
    if (nextOption.length > 0) {
      timeToSelect.val(nextAvailableTime.format('HH:mm:ss')).trigger('change');
    } else {
      // If no exact match, select the first available option
      const firstOption = timeToSelect.find('option:first');
      if (firstOption.length > 0) {
        timeToSelect.val(firstOption.val()).trigger('change');
      }
    }
    $('#time-feedback').hide();
  } else {
    $('#time-feedback').hide();
  }
});

$('#timeTo').on('change', (e) => {
  const timeFromValue = $('#timeFrom').val();
  const timeToValue = e.target.value;

  if (!timeFromValue || !timeToValue) return;

  // Ensure that timeToValue is greater than timeFromValue
  if (moment(timeToValue, 'HH:mm:ss').isSameOrBefore(moment(timeFromValue, 'HH:mm:ss'))) {
    $('#time-feedback').show();
    // Reset to first available option
    const firstOption = $('#timeTo').find('option:first');
    if (firstOption.length > 0) {
      $('#timeTo').val(firstOption.val());
      // Trigger change again with the corrected value
      setTimeout(() => $('#timeTo').trigger('change'), 100);
    }
    return;
  } else {
    $('#time-feedback').hide();
  }

  // Calculate and display duration
  const duration = moment.duration(moment(timeToValue, 'HH:mm:ss').diff(moment(timeFromValue, 'HH:mm:ss')));
  const minutes = duration.asMinutes();

  $('#duration-container').removeClass('d-none').addClass('d-flex');
  $('#duration').html(`${minutes} ${en ? 'minutes' : 'دقائق'}`);

  // Update period select if it exists
  const periodSelect = $('#period');
  if (periodSelect.length > 0) {
    const matchingOption = periodSelect.find(`option[value="${minutes}"]`);
    if (matchingOption.length > 0) {
      periodSelect.val(minutes).trigger('change');
    } else {
      periodSelect.val(30).trigger('change'); // Default to 30 minutes
    }
  }
});