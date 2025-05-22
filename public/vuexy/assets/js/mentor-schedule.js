 

 
// Translations object
const translations = {
  en: {
    Remove_Slot: 'Remove Slot',
    From: 'From',
    To: 'To',
    Min: 'Min',
    Add_Period: 'Add Period',
    Remove_Period: 'Remove Period',
    Deleted: 'Deleted!',
    ScheduleDeleted: 'The schedule has been deleted successfully.',
    OK: 'OK',
    Error: 'Error Occurred, Try again!'
  },
  ar: {
    Remove_Slot: 'إزالة الفترة',
    From: 'من',
    To: 'إلى',
    Min: 'دقيقة',
    Add_Period: 'إضافة فترة',
    Remove_Period: 'إزالة الفترة',
    Deleted: 'تم الحذف!',
    ScheduleDeleted: 'تم حذف الجدول بنجاح.',
    OK: 'موافق',
    Error: 'حدث خطأ، حاول مرة أخرى!'
  }
};

// Function to calculate the total time for a period row
function calculateTotalTime($periodRow) {
  var fromTime = $periodRow.find('select[name$="[from][]"]').val();
  var toTime = $periodRow.find('select[name$="[to][]"]').val();
  
  if (fromTime && toTime) {
    // Parse the time values correctly
    var [fromHours, fromMinutes] = fromTime.split(':').map(Number);
    var [toHours, toMinutes] = toTime.split(':').map(Number);
    
    // Convert to total minutes
    var fromTotalMinutes = (fromHours * 60) + fromMinutes;
    var toTotalMinutes = (toHours * 60) + toMinutes;
    
    // Calculate the difference
    var diff = toTotalMinutes - fromTotalMinutes;
    
    // Handle overnight schedules
    if (diff < 0) {
      diff += 24 * 60; // Add 24 hours in minutes
    }
    
    $periodRow.find('.total-time').text(diff + ' ' + __('Min'));
  } else {
    $periodRow.find('.total-time').text('0 ' + __('Min'));
  }
}

$(document).ready(function() {

    // Function to check if any input has changed
    function checkFormChanges() {
      var formChanged = false;
      $('#availability-form input, form textarea, form select').each(function() {
        if ($(this).data('initial-value') !== $(this).val()) {
          formChanged = true;
          return false; // break the loop
        }
      });
      $('#availability-form button[type="submit"]').prop('disabled', !formChanged);
    }

    // Store initial values of inputs
    $('#availability-form input, form textarea, form select').each(function() {
      $(this).data('initial-value', $(this).val());
    });

    // Toggle the periods container
    $(document).on('click', '#add-slot', function() {
      var $day = $(this).closest('.day-section').find('.periods-container');
      var dayName = $day.data('day');
      if ($day.is(':visible')) {
        checkFormChanges();
        $day.hide();
        $(this).html(__('Add_Period') + ' <i class="ti ti-plus ti-13px ms-2"></i>');
        if ($day.find('.period-row').length > 0) {
          $day.find('.period-row').remove();
        }
      } else {
        $day.show();
        $(this).html(__('Remove_Slot') + ' <i class="ti ti-x ti-13px ms-2"></i>');
        if ($day.find('.period-row').length === 0) {
          var newPeriodRow = `
            <div id="${dayName}-period-row" class="w-100 period-row d-flex align-items-center mb-2 my-2 rounded">
              <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-4 w-75">
                  <div class="row w-100">
                    <div class="col-md-6">
                      <select class=" form-select w-100" id="${dayName}-from" type="time" name="periods[${dayName}][from][]" required data-placeholder="{{ __('admin.From') }}">
                        <option value="">{{ __('admin.From') }}</option>
                        @for ($i = 0; $i < 24; $i++)
                          @for ($j = 0; $j < 60; $j += 30)
                            @php
                              $hour = $i % 12 == 0 ? 12 : $i % 12;
                              $period = $i < 12 ? 'AM' : 'PM';
                            @endphp
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}">
                              {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }} {{ $period }}
                            </option>
                          @endfor
                        @endfor
                      </select>
                    </div>
                    <div class="col-md-6">
                      <select class=" form-select w-100" id="${dayName}-to" type="time" name="periods[${dayName}][to][]" required data-placeholder="{{ __('admin.To') }}">
                        <option value="">{{ __('admin.To') }}</option>
                        @for ($i = 0; $i < 24; $i++)
                          @for ($j = 0; $j < 60; $j += 30)
                            @php
                              $hour = $i % 12 == 0 ? 12 : $i % 12;
                              $period = $i < 12 ? 'AM' : 'PM';
                            @endphp
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }}">
                              {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($j, 2, '0', STR_PAD_LEFT) }} {{ $period }}
                            </option>
                          @endfor
                        @endfor
                      </select>
                    </div>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-4 w-25">
                  <span class="total-time fw-bold text-dark text-nowrap me-2 mb-2 mb-md-0">0 {{ __('admin.Min') }}</span>
                  <div class="d-flex flex-nowrap">
                    <button 
                      type="button" 
                      class="btn btn-xs rounded-circle btn-icon me-2 add-period"
                      bs-toggle="tooltip"
                      title="{{ __('admin.Add_Period') }}"
                    >
                      <i class="ti ti-plus text-success"></i>
                    </button>
                    <div id="remove-period">
                      <button 
                        type="button" 
                        class="btn btn-xs rounded-circle btn-icon remove-period"
                        bs-toggle="tooltip"
                        title="{{ __('admin.Remove_Period') }}"
                      >
                      <i class="ti ti-trash text-danger"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>`;
          $day.append(newPeriodRow);
        }
      }
    });

    // Add a new period row
    $(document).on('click', '.add-period', function() {
      var $templateRow = $(this).closest('.periods-container').find('.period-row').first().clone();
      $templateRow.find('select').val('');
      $templateRow.find('.total-time').text('0 ' + __('Min'));
      $(this).closest('.periods-container').append($templateRow);
      calculateTotalTime($templateRow); // Ensure total time is calculated for the new period
      checkFormChanges();
    });

    // Remove the period row
    $(document).on('click', '.remove-period', function() {
      $(this).closest('.period-row').remove();
      checkFormChanges();
    });

    // Calculate total time for each period row when the time is changed
    $(document).on('change input blur', 'select, textarea', function() {
      var $periodRow = $(this).closest('.period-row');
      calculateTotalTime($periodRow);
      
      // Validate that both "from" and "to" times are required if one is changed
      var fromTime = $periodRow.find('select[name$="[from][]"]').val();
      var toTime = $periodRow.find('select[name$="[to][]"]').val();
      if (fromTime) {
        $periodRow.find('select[name$="[to][]"]').prop('required', true);
      } else {
        $periodRow.find('select[name$="[to][]"]').prop('required', false);
      }
      if (toTime) {
        $periodRow.find('select[name$="[from][]"]').prop('required', true);
      } else {
        $periodRow.find('select[name$="[from][]"]').prop('required', false);
      }
      checkFormChanges();
    });

    // Initial check to disable the submit button if no changes
    checkFormChanges();
  });

// Get current language from HTML lang attribute or default to English
function getCurrentLanguage() {
  const htmlLang = document.documentElement.lang || 'en';
  return htmlLang.startsWith('ar') ? 'ar' : 'en';
}

// Translation helper function
function __(key) {
  const lang = getCurrentLanguage();
  return translations[lang][key] || key;
}

// Generate time options for select dropdowns
function generateTimeOptions() {
  let options = '';
  
  for (let hour = 0; hour < 24; hour++) {
    for (let minute = 0; minute < 60; minute += 30) {
      const hour12 = hour % 12 === 0 ? 12 : hour % 12;
      const period = hour < 12 ? 'AM' : 'PM';
      
      const hourPadded = hour.toString().padStart(2, '0');
      const minutePadded = minute.toString().padStart(2, '0');
      const hour12Padded = hour12.toString().padStart(2, '0');
      
      const timeValue = `${hourPadded}:${minutePadded}`;
      const displayTime = `${hour12Padded}:${minutePadded} ${period}`;
      
      options += `<option value="${timeValue}">${displayTime}</option>`;
    }
  }
  
  return options;
}

function mentorSchedule(type, id, stat) {
  // Get CSRF token
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  
  // Create fetch request with headers
  fetch('/dashboard/mentors/modify', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      id: id || undefined,
      rtype: type
    })
  })
  .then(response => response.json())
  .then(response => {
    if (type === 1) {
      // Show modal using Bootstrap
      const availabilityModal = new bootstrap.Modal(document.getElementById('availabilty-modal'));
      availabilityModal.show();
      
      // Hide the spinner
      const spinner = document.querySelector('#addSchedule .spinner-border');
      if (spinner) spinner.classList.add('d-none');

      const daysOfWeek = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
      daysOfWeek.forEach(day => {
        // Filter the response to get the periods for the current day
        const dayPeriods = response.filter(item => item?.days === day) || [];

        if (dayPeriods.length > 0) {
          const dayRow = document.querySelector(`#availabilty-modal #${day}-period-row`);
          
          if (dayRow) {
            const periodContainer = dayRow.closest('.periods-container');
            const slotButton = periodContainer.closest('.day-section').querySelector('#add-slot');
            if (slotButton) {
              slotButton.innerHTML = `${__('Remove_Slot')} <i class="ti ti-x ti-13px ms-2"></i>`;
            }

            periodContainer.style.display = 'block'; // Show the row
            periodContainer.innerHTML = ''; // Clear existing rows

            for (let i = 0; i < dayPeriods.length; i++) {
              const fromValue = moment(dayPeriods[i].tfrom, 'HH:mm:ss').format('HH:mm');
              const toValue = moment(dayPeriods[i].tto, 'HH:mm:ss').format('HH:mm');
              
              // Generate time options
              const timeOptions = generateTimeOptions();

              // Create unique IDs for each period row
              const periodRowId = `${day}-period-row-${i}`;
              const fromSelectId = `${day}-from-${i}`;
              const toSelectId = `${day}-to-${i}`;

              const newPeriodRow = `
                <div id="${periodRowId}" class="w-100 period-row d-flex align-items-center mb-2 my-2 rounded">
                <div class="d-flex align-items-center justify-content-between w-100">
                  <div class="d-flex align-items-center gap-4 w-75">
                    <div class="row w-100">
                      <div class="col-md-6">
                        <select class="form-select w-100" id="${fromSelectId}" type="time" name="periods[${day}][from][]" data-placeholder="${__('From')}">
                          <option value="">${__('From')}</option>
                          ${timeOptions}
                        </select>
                      </div>
                      <div class="col-md-6">
                        <select class="form-select w-100" id="${toSelectId}" type="time" name="periods[${day}][to][]" data-placeholder="${__('To')}">
                          <option value="">${__('To')}</option>
                          ${timeOptions}
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex align-items-center gap-4 w-25">
                    <span class="total-time fw-bold text-dark text-nowrap me-2 mb-2 mb-md-0">0 ${__('Min')}</span>
                    <div class="d-flex flex-nowrap">
                      <button 
                        type="button" 
                        class="btn btn-xs rounded-circle btn-icon me-2 add-period"
                        data-bs-toggle="tooltip"
                        title="${__('Add_Period')}"
                      >
                        <i class="ti ti-plus text-success"></i>
                      </button>
                      <div id="remove-period">
                        <button 
                          type="button" 
                          class="btn btn-xs rounded-circle btn-icon remove-period"
                          data-bs-toggle="tooltip"
                          title="${__('Remove_Period')}"
                        >
                        <i class="ti ti-trash text-danger"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              `;
              periodContainer.innerHTML += newPeriodRow; // Append the new rows

              // Set the selected values after a short delay to ensure DOM is updated
              setTimeout(() => {
                const fromSelect = document.querySelector(`#${fromSelectId}`);
                const toSelect = document.querySelector(`#${toSelectId}`);
                if (fromSelect) fromSelect.value = fromValue;
                if (toSelect) toSelect.value = toValue;
                
                // Force a manual calculation of the duration based on the actual values
                const from = dayPeriods[i].tfrom.substring(0, 5);  // Extract HH:MM from time string
                const to = dayPeriods[i].tto.substring(0, 5);      // Extract HH:MM from time string
                
                // Calculate and update the time difference directly
                if (from && to) {
                  const [fromHours, fromMinutes] = from.split(':').map(Number);
                  const [toHours, toMinutes] = to.split(':').map(Number);
                  
                  let fromTotalMinutes = (fromHours * 60) + fromMinutes;
                  let toTotalMinutes = (toHours * 60) + toMinutes;
                  
                  // Handle overnight schedules
                  if (toTotalMinutes < fromTotalMinutes) {
                    toTotalMinutes += 24 * 60; // Add 24 hours in minutes
                  }
                  
                  const diff = toTotalMinutes - fromTotalMinutes;
                  $(`#${periodRowId} .total-time`).text(diff + ' ' + __('Min'));
                }
              }, 100);
            }
          }
        }
      });
      
      // After all periods have been created, recalculate all total times
      setTimeout(() => {
        // For each period row in the modal, calculate the total time
        $('#availabilty-modal .period-row').each(function() {
          calculateTotalTime($(this));
        });
      }, 100); // Give a little extra time for the DOM to update
      
    } else if (type === 2) {
      location.reload();
    } else if (type === 3) {
      Swal.fire({
        title: __('Deleted'),
        text: __('ScheduleDeleted'),
        icon: 'success',
        confirmButtonText: __('OK')
      }).then((result) => {
        if (result.isConfirmed) {
          location.reload();
        }
      });
    }
  })
  .catch(error => {
    console.error(error);
    // Hide the spinner
    const spinner = document.querySelector('#addSchedule .spinner-border');
    if (spinner) spinner.classList.add('d-none');
    alert(__('Error')); // Show an alert with translated error
  });
}