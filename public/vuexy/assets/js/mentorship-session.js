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
  let _token = $('meta[name="csrf-token"]').attr('content');
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': _token } });
  $.ajax({
    type: 'POST',
    url: '/dashboard/mentors/modify',
    data: { id: id, rtype: 5, _token: _token },
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
        .html('<b>' + item.dfrom + '</b> (' + moment(item.tfrom, 'HH:mm:ss').format('hh:mm A') + ' - ' + moment(item.tto, 'HH:mm:ss').format('hh:mm A') + ').')
        .appendTo('#schedule');
          break;
        case 'Weekly':
          $('<li>')
        .html('<b>' + (en ? 'Weekly' : 'أسبوعي') + '</b> (' + item.days + ') - (' + moment(item.tfrom, 'HH:mm:ss').format('hh:mm A') + ' - ' + moment(item.tto, 'HH:mm:ss').format('hh:mm A') + ').')
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

  // Ensure that timeToValue is greater than timeFromValue
  if (moment(timeToValue, 'HH:mm:ss').isBefore(moment(timeFromValue, 'HH:mm:ss'))) {
    e.preventDefault();
    $('#time-feedback').show();
    return;
  }

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

$('#timeFrom').on('change', (e) => {
  const timeFromValue = e.target.value;
  const timeToValue = $('#timeTo').val();


  // Ensure that timeFromValue is less than timeToValue
  if (moment(timeFromValue, 'HH:mm:ss').isAfter(moment(timeToValue, 'HH:mm:ss'))) {
    $('#time-feedback').show();
    // alert(en ? 'Start time must be less than end time.' : 'يجب أن يكون وقت البدء أقل من وقت الانتهاء.');
    // $('#timeFrom').val(''); // Reset to timeToValue
    return;
  } else {
    $('#time-feedback').hide();
  }

});

$('#timeTo').on('change', (e) => {
  const timeFromValue = $('#timeFrom').val();
  const timeToValue = e.target.value;

  // Ensure that timeToValue is greater than timeFromValue
  if (moment(timeToValue, 'HH:mm:ss').isBefore(moment(timeFromValue, 'HH:mm:ss'))) {
    $('#time-feedback').show();
    return;
  } else {
    $('#time-feedback').hide();
  }

  const duration = moment.duration(moment(timeToValue, 'HH:mm:ss').diff(moment(timeFromValue, 'HH:mm:ss')));
  const minutes = duration.asMinutes(); // Convert duration to minutes

  $('#duration-container').removeClass('d-none').addClass('d-flex');
  $('#duration').html(`${minutes} ${en ? 'minutes' : 'دقائق'}`);

  const periodSelect = $('#period');
  const matchingOption = periodSelect.find(`option[value="${minutes}"]`);

  if (matchingOption.length > 0) {
    periodSelect.val(minutes).trigger('change');
  } else {
    periodSelect.val(30).trigger('change'); // Default to 30 minutes
  }
});