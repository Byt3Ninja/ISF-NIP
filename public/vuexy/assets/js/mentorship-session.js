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
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
  $.ajax({
    type: 'POST',
    url: '/dashboard/mentors/modify',
    data: { id: id, rtype: 5 },
    success: function (response) {
      responseData = normalizeResponseDays(response); // Normalize days
      console.log('response', responseData);

      $('#addOrganizationID').val(organization_id);
      $('#addCycleID').val(cycle_id);

      onSuccess(responseData);
    },
    error: function (error) {
      console.log(error);
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
          return acc.concat(item.days);
        }, []);

        console.log('enabledDays', enabledDays);

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
            .html('<b>' + item.dfrom + '</b> (' + time + ').')
            .appendTo('#schedule');
          break;
        case 'Weekly':
          $('<li>')
            .html('<b>' + (en ? 'Weekly' : 'أسبوعي') + '</b> (' + item.days + ') - (' + time + ').')
            .appendTo('#schedule');
          break;
        case 'WorkingDays':
          $('<li>')
            .html('<b>' + (en ? 'All working days' : 'جميع أيام العمل') + '</b> (' + time + ').')
            .appendTo('#schedule');
          break;
        case 'Period':
          $('<li>')
            .html(
              '<b>' + (en ? 'From ' : 'من ') + item.dfrom + (en ? ' To ' : ' إلى ') + item.dto + '</b> (' + time + ').'
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

  if (!selectedDateValue) {
    e.preventDefault();
    $('#flatpickr-datetimeLimit').addClass('is-invalid').trigger('focus');
    $('.date-error')
      .text(en ? 'Please select a date.' : 'يرجى اختيار تاريخ.')
      .show();
  }
});

$('#flatpickr-datetimeLimit').on('change', e => {
  console.log('responseData', responseData);
  console.log('changed', e.target.value);
  $('#flatpickr-datetimeLimit').removeClass('is-invalid');
  $('.date-error').hide();

  // Get the day name of the selected date
  const selectedDate = e.target.value;
  const dayName = moment(selectedDate, 'YYYY-MM-DD').format('ddd');
  console.log('Day Name:', dayName);

  const filteredDateObj = responseData.find(item => item.days.includes(dayName));
  console.log('filteredDateObj', filteredDateObj);

  if (filteredDateObj && filteredDateObj.tfrom && filteredDateObj.tto) {

    const timeFromSelect = $('#timeFrom');
    const timeToSelect = $('#timeTo');
  
    // Clear previous options
    timeFromSelect.empty();
    timeToSelect.empty();
  
    // Parse tfrom and tto into moment objects
    const startTime = moment(filteredDateObj.tfrom, 'HH:mm:ss');
    const endTime = moment(filteredDateObj.tto, 'HH:mm:ss');
  
    // Loop through time range with 30-minute intervals
    let currentTime = startTime.clone();
    let isSecondOptionSet = false;
    while (currentTime.isBefore(endTime)) {
      const timeFormatted = currentTime.format('HH:mm:ss');
      const displayTime = currentTime.format('hh:mm A');
      const option = $('<option>').val(timeFormatted).text(displayTime);

      // Append options to both timeFrom and timeTo
      timeFromSelect.append(option.clone());
      const timeToOption = option.clone();
      timeToSelect.append(timeToOption);

      // Automatically select the second option in timeToSelect
      if (!isSecondOptionSet && timeToSelect.children().length === 2) {
      timeToOption.prop('selected', true);
      isSecondOptionSet = true;
      }

      // Increment by 30 minutes
      currentTime.add(30, 'minutes');
    }
    // Add the endTime as the last option for timeTo
    const endTimeFormatted = endTime.format('HH:mm:ss');
    const endTimeDisplay = endTime.format('hh:mm A');
    const endOption = $('<option>').val(endTimeFormatted).text(endTimeDisplay);
    timeToSelect.append(endOption);
  }
});

$('#timeTo').on('change', (e) => {
  const timeFromValue = $('#timeFrom').val();
  const timeToValue = e.target.value;

  const duration = moment.duration(moment(timeToValue, 'HH:mm:ss').diff(moment(timeFromValue, 'HH:mm:ss')));
  const hours = duration.asMinutes(); // Convert duration to minutes

  const periodSelect = $('#period');
  const matchingOption = periodSelect.find(`option[value="${hours}"]`);

  if (matchingOption.length > 0) {
    periodSelect.val(hours).trigger('change');
  } else {
    periodSelect.val(30).trigger('change'); // Default to 30 minutes
  }

  console.log('timeFromValue', timeFromValue);
  console.log('timeToValue', timeToValue);
  console.log('duration', duration);
  console.log('hours', hours);
});