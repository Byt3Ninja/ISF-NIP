@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/fullcalendar/fullcalendar.css" />
@stop

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-calendar.css" />
  <style>
    .fc-event, .fc-event-main {
      background-color: var(--bs-primary-bg-subtle) !important;
      color: var(--bs-black) !important;
      font-size: 12px !important;
    }
    .fc-event-time {
      display: none;
    }
    .fc-today-button {
      text-transform: capitalize !important;
    }
  </style>
@stop

@section('content')

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
  
    <div class="d-flex justify-content-between align-items-center mb-6">
      <h4 class="card-title mb-0 text-dark f">{{ __('mentor.weekly_availability_slots') }}</h4>

      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#availabilityModal">
        <i class="ti ti-plus me-1"></i> {{ __('mentor.add_availability') }}
      </button>
    </div>
  
    <div class="card mb-5">
      <div class="card-body">
          <div id="main-calendar"></div>
      </div>
    </div>
  </div>
  
  <!-- ADD AVAILABILITY MODAL WITH CALENDAR -->
  <div class="modal fade" id="availabilityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <form method="POST" action="#" id="availability-form">
        @csrf
        <input type="hidden" name="availability_data" id="availability-data-input">
  
        <div class="modal-content">
  
          <div class="modal-header border-bottom pb-6">
          <div class="d-flex justify-content-between align-items-center w-100">
            <h5 class="modal-title d-flex align-items-center mb-0">
              <i class="ti ti-plus me-2"></i> {{ __('mentor.add_availability') }}
            </h5>
            <nav class="nav-align-end">
              <ul class="nav nav-tabs">
                <li class="nav-item">
                  <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#calendar-tab">
                    <i class="ti ti-calendar ti-sm me-1"></i>
                    {{ __('mentor.calendar_view') }}
                  </button>
                </li>
                <li class="nav-item">
                  <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#list-tab">
                    <i class="ti ti-list ti-sm me-1"></i>
                    {{ __('mentor.list_view') }}
                  </button>
                </li>
              </ul>
            </nav>
          </div>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
  
          <div class="modal-body p-0">
            <div class="tab-content">
              <div class="tab-pane fade show active" id="calendar-tab" role="tabpanel">
                <div class="row">
                  <div class="col-lg-5 border-end ps-5">
                    <div id="availability-calendar"></div>
                  </div>
                  <div class="col-lg-7 ps-8">
                    <div class="pe-4" style="max-height: 500px; overflow-y: auto;">
    
                      <div class="mb-4">
                        <h6 class="mb-0" id="selected-date-display">
                          {{ __('mentor.select_date_from_calendar') }}
                        </h6>
                        <small class="text-muted">{{ __('mentor.select_date_to_add_your_available_time_slots') }}</small>
                      </div>
      
                      <div class="border rounded p-4 bg-light-subtle mb-4" id="slot-form-box" style="display: none;">
                        <div class="row mb-2">
                          <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('mentor.start_time') }}</label>
                            <input type="text" id="slot-start-time" class="form-control bg-white border" placeholder="09:00">
                          </div>

                          <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('mentor.end_time') }}</label>
                            <input type="text" id="slot-end-time" class="form-control bg-white border" placeholder="17:00">
                          </div>

                          <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('mentor.session_type') }}</label>
                            <select id="slot-session-type" class="form-select bg-white border">
                              <option value="Online">{{ __('mentor.online') }}</option>
                              <option value="Offline">{{ __('mentor.offline') }}</option>
                              <option value="Both">{{ __('mentor.both') }}</option>
                            </select>
                          </div>
                        </div>
      
                        <div class="d-flex gap-2">
                          <button type="button" class="btn btn-sm btn-primary text-nowrap" id="add-single-slot">
                            <i class="ti ti-plus ti-16px me-1"></i>
                            {{ __('mentor.add_single_slot') }}
                          </button>
                          <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 text-nowrap" id="apply-to-weekday">
                            <i class="ti ti-calendar-check ti-16px"></i>
                            <span>{{ __('mentor.apply_to_all') }}</span>
                            <span id="selected-weekday"></span>
                          </button>
                        </div>
                      </div>
      
                      <div id="slots-list-container">
                        <h6 class="mb-3">{{ __('mentor.slots_for_selected_date') }}</h6>
                        <div id="slots-list">
                          <p class="text-muted">{{ __('mentor.no_slots_added') }}</p>
                        </div>
                      </div>
      
                    </div>
                  </div>
                </div>
              </div>

              <div class="tab-pane fade" id="list-tab" role="tabpanel">
                <div style="max-height: 500px; overflow-y: auto;">
                  <div id="availability-list-view">
                    <!-- List view content will be injected here -->
                  </div>
                </div>
              </div>

            </div>
          </div>
  
          <div class="modal-footer border-top pt-6">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('mentor.cancel') }}</button>
            <button type="button" class="btn btn-primary">{{ __('mentor.save_availability') }}</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="/vuexy/assets/vendor/libs/fullcalendar/fullcalendar.js"></script>
<script>
  // Global variables
    let calendarInstance;
    let currentSelectedDate = null;
    let currentWeekday = null;
    let availabilityData = {
        specific: {},
        weekly: {}
    };

    // Helper function to create a slot object
    const createSlotObject = (slot) => ({
        start: slot.start_time,
        end: slot.end_time,
        sessionType: slot.session_type,
        duration: moment(slot.end_time, 'HH:mm').diff(moment(slot.start_time, 'HH:mm'), 'minutes')
    });

    // UI update functions
    const updateCalendarStyles = () => {
        if (calendarInstance && typeof calendarInstance.redraw === 'function') {
            calendarInstance.redraw();
        }
    };

    const updateSelectedDateDisplay = () => {
        if (currentSelectedDate) {
            $('#selected-date-display').text(moment(currentSelectedDate).format('dddd, MMMM D, YYYY'));
            $('#selected-weekday').text(currentWeekday);
            $('#slot-form-box').show();
        } else {
            $('#selected-date-display').text('Select a date from the calendar');
            $('#slot-form-box').hide();
        }
    };

    const clearSlotForm = () => {
        if (startTimePicker) startTimePicker.clear();
        if (endTimePicker) endTimePicker.clear();
        $('#slot-session-type').val('Online');
    };

    // Process slots from JSON
    function processSlots(slots) {
        availabilityData = slots.reduce((acc, slot) => {
            const newSlot = createSlotObject(slot);
            if (slot.weekly && slot.weekday) {
                const { weekday } = slot;
                acc.weekly[weekday] = [...(acc.weekly[weekday] || []), newSlot];
            } else if (!slot.weekly && slot.date) {
                const date = moment(slot.date).format('YYYY-MM-DD');
                acc.specific[date] = [...(acc.specific[date] || []), newSlot];
            }
            return acc;
        }, { specific: {}, weekly: {} });

        updateSlotsList();
        updateListView();
    }

    // Global time picker variables
    let startTimePicker;
    let endTimePicker;

    // Fetch slots on page load
    document.addEventListener('DOMContentLoaded', function() {
        fetch('/dashboard/availability/slots', {
            headers: {
              "Accept": "application/json"
            }
          })
            .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok'))
            .then(result => {
              console.log('Fetched slots:', result?.data ? result.data : []);
                processSlots(result?.data ? result.data : []);
                updateCalendarStyles();
                initializeMainCalendar(); // Initialize the main calendar here
            })
            .catch(error => {
                console.error('Error fetching slots:', error);
                alert('{{ __('mentor.could_not_load_availability_slots') }}');
            });

        // Initialize time pickers after DOM is ready
        startTimePicker = flatpickr("#slot-start-time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
        });
        endTimePicker = flatpickr("#slot-end-time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
        });

        // Initialize calendar after time pickers
        calendarInstance = flatpickr("#availability-calendar", {
        inline: true,
        mode: "single",
        dateFormat: "Y-m-d",
        minDate: "today",
        defaultDate: 'today',
        onReady: (selectedDates) => updateSelectedDate(selectedDates),
        onChange: (selectedDates) => updateSelectedDate(selectedDates),
        onDayCreate: (dObj, dStr, fp, dayElem) => {
            const dateStr = moment(dayElem.dateObj).format('YYYY-MM-DD');
            const weekday = moment(dayElem.dateObj).format('dddd');
            const hasSlots = (availabilityData.specific[dateStr]?.length > 0) || (availabilityData.weekly[weekday]?.length > 0);
            if (hasSlots) {
                dayElem.classList.add('has-slots');
            }
        },
    });
    }); // Close DOMContentLoaded

    // Update state on date selection
    function updateSelectedDate(selectedDates) {
        if (selectedDates.length > 0) {
            currentSelectedDate = moment(selectedDates[0]).format('YYYY-MM-DD');
            currentWeekday = moment(selectedDates[0]).format('dddd');
            updateSelectedDateDisplay();
            updateSlotsList();
        }
    }

    // Main Calendar Initialization
    const mainCalendarEl = document.getElementById('main-calendar');
    let mainCalendarInstance;

    function initializeMainCalendar() {
        const events = [];

        // Add weekly recurring slots to main calendar
        for (const weekday in availabilityData.weekly) {
            availabilityData.weekly[weekday].forEach(slot => {
                events.push({
                    title: `${slot.start} - ${slot.end} (${slot.sessionType})`,
                    daysOfWeek: [moment().day(weekday).weekday()], // Convert weekday name to 0-6 index
                    startTime: slot.start,
                    endTime: slot.end,
                    extendedProps: {
                        sessionType: slot.sessionType,
                        weekly: true
                    },
                    display: 'list-item'
                });
            });
        }

        // Add specific date slots to main calendar
        for (const date in availabilityData.specific) {
            availabilityData.specific[date].forEach(slot => {
                events.push({
                    title: `${slot.start} - ${slot.end} (${slot.sessionType})`,
                    start: date + 'T' + slot.start,
                    end: date + 'T' + slot.end,
                    allDay: false,
                    extendedProps: {
                        sessionType: slot.sessionType,
                        weekly: false
                    },
                    display: 'list-item'
                });
            });
        }

        mainCalendarInstance = new FullCalendar.Calendar(mainCalendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridDay,listMonth'
            },
            editable: false,
            selectable: false,
            events: events,
            eventContent: function(arg) {
                let arrayOfDomNodes = [];
                
                let titleEl = document.createElement('span');
                titleEl.innerHTML = arg.event.title;
                arrayOfDomNodes.push(titleEl);

                if (arg.event.extendedProps.weekly) {
                    let iconEl = document.createElement('i');
                    iconEl.className = 'ti ti-repeat ti-16px text-dark ms-2'; // Added ms-1 for margin-left
                    arrayOfDomNodes.push(iconEl);
                }

                return { domNodes: arrayOfDomNodes };
            }
        });

        mainCalendarInstance.render();
    }

    // Re-render main calendar when availability data changes
    function updateMainCalendar() {
        if (mainCalendarInstance) {
            mainCalendarInstance.destroy(); // Destroy existing calendar to re-render events
        }
        initializeMainCalendar();
    }

    // Update calls in processSlots and delete-slot
    const originalProcessSlots = processSlots;
    processSlots = function(slots) {
        originalProcessSlots(slots);
        updateMainCalendar();
    };

    // Override delete slot handler to also update main calendar
    $(document).off('click.delete-slot-override').on('click.delete-slot-override', '.delete-slot', function() {
        const { type, key, index } = $(this).data();
        availabilityData[type][key].splice(index, 1);
        if (availabilityData[type][key].length === 0) {
            delete availabilityData[type][key];
        }
        updateSlotsList();
        updateListView();
        updateCalendarStyles();
        updateMainCalendar();
    });
    

    // Get slot from form
    function getSlotFromForm() {
        const start = startTimePicker.input.value;
        const end = endTimePicker.input.value;
        const sessionType = $('#slot-session-type').val();

        if (!start || !end) {
            alert('{{ __('mentor.please_select_start_and_end_time') }}');
            return null;
        }

        const startTime = moment(start, 'HH:mm');
        const endTime = moment(end, 'HH:mm');

        if (!startTime.isValid() || !endTime.isValid() || endTime.isSameOrBefore(startTime)) {
            alert('{{ __('mentor.end_time_must_be_after_start_time') }}');
            return null;
        }
        
        return { start, end, sessionType, duration: endTime.diff(startTime, 'minutes') };
    }

    // Add a new slot
    function addSlot(type) {
        const key = type === 'specific' ? currentSelectedDate : currentWeekday;
        if (!key) {
            alert('{{ __('mentor.please_select_date_first') }}');
            return;
        }

        const slot = getSlotFromForm();
        if (!slot) return;

         fetch('/dashboard/availability/add-slot', {
            headers: {
              "Accept": "application/json",
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
              "Content-Type": 'application/json',
            },
            method: 'POST',
            body: JSON.stringify({
              tfrom: slot.start,
              tto: slot.end,
              session_type: slot.sessionType,
              type: type === 'weekly' ? 'Weekly' : 'Period',
              date: type === 'specific' ? key : null,
              days: type === 'weekly' ? [key] : null,
            }),
        })
          .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok'))
          .then(result => {
            availabilityData[type][key] = [...(availabilityData[type][key] || []), slot];
            
            updateSlotsList();
            updateListView();
            clearSlotForm();
            if (type === 'specific') {
              const message = "{{ __('mentor.slot_added_to_date', ['date' => ':date']) }}".replace(':date', moment(key).format('MMMM D, YYYY'));
              
              updateCalendarStyles();
              releaseSuccessAlert({
                'title': '{{ __('mentor.slot_added_success') }}',
                'message': message,
              });
            } else {
              const message = "{{ __('mentor.slot_added_to_all', ['date' => ':date']) }}".replace(':date', key + 's');
              updateCalendarStyles();
              releaseSuccessAlert({
                'title': '{{ __('mentor.slot_added_success') }}',
                'message': message,
              });
            }
          })
          .catch(error => {
            releaseFailureAlert({
              title: '{{ __('admin.Error') }}',
              message: error?.message ? error.message : "{{ __('admin.unexpected_error') }}", 
            });
          });
    }

    $('#add-single-slot').click(() => addSlot('specific'));
    $('#apply-to-weekday').click(() => addSlot('weekly'));

    // Render calendar view's slots list
    function updateSlotsList() {
        if (!currentSelectedDate) {
            $('#slots-list').html('<p class="text-muted">@lang('mentor.no_slots_added')</p>');
            return;
        }

        const renderSlotHTML = (slot, type, key, index) => {
            const isWeekly = type === 'weekly';
            const repeatsText = isWeekly ? ' • <em>{{ __('mentor.repeats_weekly') }}</em>' : '';
            const bgColor = isWeekly ? 'bg-light-subtle' : 'bg-white';
            return `
                <div class="${bgColor} border rounded p-3 mb-2 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-dark mb-1">${slot.start} - ${slot.end}</div>
                        <div class="small text-muted">${slot.duration} {{ __('mentor.min') }} • ${slot.sessionType === 'Online' ? '{{ __('mentor.online') }}' : slot.sessionType === 'Offline' ? '{{ __('mentor.offline') }}' : '{{ __('mentor.both') }}'}${repeatsText}</div>
                    </div>
                    <button type="button" class="btn btn-label-danger btn-sm btn-icon delete-slot" data-type="${type}" data-key="${key}" data-index="${index}">
                        <i class="ti ti-trash ti-16px"></i>
                    </button>
                </div>`;
        };
        
        const renderGroup = (title, slots, type, key, emptyMsg) => `
            <div class="mb-3 mt-3"><strong>${title}</strong></div>
            ${(slots?.length)
                ? slots.map((slot, index) => renderSlotHTML(slot, type, key, index)).join('')
                : `<p class="text-muted">${emptyMsg}</p>`
            }`;

        const specificSlotsHtml = renderGroup('{{ __('mentor.specific_date_slots') }}:', availabilityData.specific[currentSelectedDate], 'specific', currentSelectedDate, '{{ __('mentor.no_slots_added') }}');
        const weeklySlotsHtml = renderGroup(`{{ __('mentor.weekly_recurring_slots') }} ({{ __('mentor.every') }} ${currentWeekday === 'Sunday' ? '{{ __('mentor.sunday') }}' : currentWeekday === 'Monday' ? '{{ __('mentor.monday') }}' : currentWeekday === 'Tuesday' ? '{{ __('mentor.tuesday') }}' : currentWeekday === 'Wednesday' ? '{{ __('mentor.wednesday') }}' : currentWeekday === 'Thursday' ? '{{ __('mentor.thursday') }}' : currentWeekday === 'Friday' ? '{{ __('mentor.friday') }}' : '{{ __('mentor.saturday') }}'}):`, availabilityData.weekly[currentWeekday], 'weekly', currentWeekday, `{{ __('mentor.no_slots_added') }}`);
        
        $('#slots-list').html(specificSlotsHtml + weeklySlotsHtml);
    }

    // Render list view
    function updateListView() {
        const weekdaysOrder = [
            { key: 'Sunday', display: '{{ __('mentor.sunday') }}' },
            { key: 'Monday', display: '{{ __('mentor.monday') }}' },
            { key: 'Tuesday', display: '{{ __('mentor.tuesday') }}' },
            { key: 'Wednesday', display: '{{ __('mentor.wednesday') }}' },
            { key: 'Thursday', display: '{{ __('mentor.thursday') }}' },
            { key: 'Friday', display: '{{ __('mentor.friday') }}' },
            { key: 'Saturday', display: '{{ __('mentor.saturday') }}' }
        ];
        let html = '';

        weekdaysOrder.forEach(day => {
            const weekdayKey = day.key;
            const weekdayDisplay = day.display;

            const weeklySlots = (availabilityData.weekly[weekdayKey] || []).map(slot => ({ ...slot, type: 'weekly', key: weekdayKey }));
            
            const specificSlotsOnWeekday = Object.entries(availabilityData.specific)
                .filter(([date]) => moment(date).format('dddd') === weekdayKey)
                .flatMap(([date, slots]) => slots.map(slot => ({ ...slot, type: 'specific', key: date })));

            const allSlotsForDay = [...weeklySlots, ...specificSlotsOnWeekday];
            if (allSlotsForDay.length === 0) return;

            allSlotsForDay.sort((a, b) => a.start.localeCompare(b.start));

            html += `<div class="mb-4"><h6 class="mb-3">${weekdayDisplay}</h6>`;

            allSlotsForDay.forEach(slot => {
                const originalArray = availabilityData[slot.type][slot.key];
                const originalIndex = originalArray.findIndex(s => s.start === slot.start && s.end === slot.end && s.sessionType === slot.sessionType);

                const isWeekly = slot.type === 'weekly';
                const repeatsText = isWeekly ? ` • <em>{{ __('mentor.repeats_weekly') }}</em>` : ` • <span class="text-primary">${moment(slot.key).format('MMM D')}</span>`;
                const bgColor = isWeekly ? 'bg-light-subtle' : 'bg-white';

                html += `
                    <div class="${bgColor} border rounded p-3 mb-2 d-flex justify-content-between align-items-center" data-type="${slot.type}" data-key="${slot.key}" data-index="${originalIndex}">
                        <div class="slot-details">
                            <div class="fw-bold text-dark mb-1">
                                <span class="slot-start">${slot.start}</span> - <span class="slot-end">${slot.end}</span>
                            </div>
                        <div class="small text-muted">
                            ${slot.duration} {{ __('mentor.min') }} • <span class="slot-session-type">${slot.sessionType === 'Online' ? '{{ __('mentor.online') }}' : slot.sessionType === 'Offline' ? '{{ __('mentor.offline') }}' : '{{ __('mentor.both') }}'}</span>${repeatsText}
                        </div>
                        </div>
                        <div class="slot-actions btn-group">
                            <button type="button" class="btn btn-sm btn-icon btn-label-primary add-slot-action" title="{{ __('mentor.add') }}"><i class="ti ti-plus"></i></button>
                            <button type="button" class="btn btn-sm btn-icon btn-label-secondary copy-slot-action" title="{{ __('mentor.copy') }}"><i class="ti ti-copy"></i></button>
                            <button type="button" class="btn btn-label-danger btn-sm btn-icon delete-slot" title="{{ __('mentor.delete') }}" data-type="${slot.type}" data-key="${slot.key}" data-index="${originalIndex}">
                                <i class="ti ti-trash ti-16px"></i>
                            </button>
                        </div>
                    </div>`;
            });
            html += `</div>`;
        });

        if (html === '') {
            html = '<p class="text-muted">{{ __('mentor.no_availability_slots_added') }}</p>';
        }

        $('#availability-list-view').html(html);
    }
    
    // Handle Add (clone) action in list view
    $(document).on('click', '.add-slot-action', function() {
        const slotElement = $(this).closest('.d-flex');
        const { type, key, index } = slotElement.data();
        const originalSlot = availabilityData[type][key][index];

        const newSlotFormHtml = `
            <div class="editable-slot-form border rounded p-3 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <label class="form-label">Start</label>
                        <input type="text" class="form-control form-control-sm start-time-input" value="${originalSlot.start}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End</label>
                        <input type="text" class="form-control form-control-sm end-time-input" value="${originalSlot.end}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select class="form-select form-select-sm session-type-select">
                            <option ${originalSlot.sessionType === 'Online' ? 'selected' : ''}>Online</option>
                            <option ${originalSlot.sessionType === 'Offline' ? 'selected' : ''}>Offline</option>
                            <option ${originalSlot.sessionType === 'Both' ? 'selected' : ''}>Both</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-sm btn-primary save-new-slot-action" data-original-type="${type}" data-original-key="${key}">{{ __('mentor.save_availability') }}</button>
                    <button class="btn btn-sm btn-label-secondary cancel-new-slot-action">{{ __('mentor.cancel') }}</button>
                </div>
            </div>
        `;

        slotElement.after(newSlotFormHtml);
        
        const newForm = slotElement.next('.editable-slot-form');
        newForm.find('.start-time-input, .end-time-input').flatpickr({
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });
    });

    // Handle cancelling a new slot
    $(document).on('click', '.cancel-new-slot-action', function() {
        $(this).closest('.editable-slot-form').remove();
    });

    // Handle saving a new slot
    $(document).on('click', '.save-new-slot-action', function() {
        const form = $(this).closest('.editable-slot-form');
        const start = form.find('.start-time-input').val();
        const end = form.find('.end-time-input').val();
        const sessionType = form.find('.session-type-select').val();

        const startTime = moment(start, 'HH:mm');
        const endTime = moment(end, 'HH:mm');
        if (!startTime.isValid() || !endTime.isValid() || endTime.isSameOrBefore(startTime)) {
            alert('{{ __('mentor.end_time_must_be_after_start_time') }}');
            return;
        }

        const newSlot = { start, end, sessionType, duration: endTime.diff(startTime, 'minutes') };
        
        const { originalType, originalKey } = $(this).data();
        availabilityData[originalType][originalKey] = [...(availabilityData[originalType][originalKey] || []), newSlot];
        
        updateListView();
        updateSlotsList();
        updateCalendarStyles();
        
        form.remove();
    });

    // Handle Copy action in list view
    $(document).on('click', '.copy-slot-action', function(e) {
        e.stopPropagation();
        const button = this;
        const slotElement = $(button).closest('.d-flex');
        const { type, key, index } = slotElement.data();
        const slotToCopy = { ...availabilityData[type][key][index] };

        $('.copy-slot-action').not(button).popover('dispose');
        
        const popoverContent = `
            <div>
                ${['{{ __('mentor.sunday') }}', '{{ __('mentor.monday') }}', '{{ __('mentor.tuesday') }}', '{{ __('mentor.wednesday') }}', '{{ __('mentor.thursday') }}', '{{ __('mentor.friday') }}', '{{ __('mentor.saturday') }}'].map((day, index) => {
                    const dayValues = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    return `
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="${dayValues[index]}" id="day-copy-${dayValues[index]}">
                      <label class="form-check-label" for="day-copy-${dayValues[index]}">${day}</label>
                    </div>
                `}).join('')}
                <div class="d-flex justify-content-end mt-3">
                    <button class="btn btn-primary btn-sm apply-copy-action">{{ __('mentor.apply_to_all') }}</button>
                </div>
            </div>`;

        const popover = new bootstrap.Popover(button, {
            html: true,
            title: '{{ __('mentor.copy') }} {{ __('mentor.slots_for_selected_date') }}',
            content: popoverContent,
            sanitize: false,
            container: 'body',
            trigger: 'manual'
        });
        
        popover.show();
        
        const popoverTip = popover.tip; 
        $(popoverTip).find('.apply-copy-action').on('click', () => {
            const selectedDays = Array.from($(popoverTip).find('.form-check-input:checked')).map(cb => cb.value);

            if (selectedDays.length > 0) {
                selectedDays.forEach(day => {
                    availabilityData.weekly[day] = [...(availabilityData.weekly[day] || []), slotToCopy];
                });
                updateListView();
                updateSlotsList();
                updateCalendarStyles();
            }
            popover.dispose();
        });
        
        $('body').on('click.popover', function(e) {
            if (!$(button).is(e.target) && $(button).has(e.target).length === 0 && !$(e.target).closest('.popover').length) {
                $('.copy-slot-action').popover('dispose');
                $('body').off('click.popover');
            }
        });
    });


    // Form submission
    $('#availability-form').submit(function(e) {
        e.preventDefault();
        if (!Object.keys(availabilityData.specific).length && !Object.keys(availabilityData.weekly).length) {
            alert('{{ __('mentor.please_add_at_least_one_slot') }}');
            return;
        }
        $('#availability-data-input').val(JSON.stringify(availabilityData));
        console.log('Availability Data:', availabilityData);
        alert('{{ __('mentor.save_availability') }}! Check console for data.');
    });

    // Reset modal
    $('#availabilityModal').on('hidden.bs.modal', function () {
        $('.copy-slot-action').popover('dispose'); // Clean up popovers when modal closes
        // calendarInstance.clear();
        // currentSelectedDate = null;
        // currentWeekday = null;
        // Re-fetch data on close to discard any unstaged changes
        fetch('/dashboard/availability/slots', {
          headers: {
            "Accept": "application/json"
          }
        })
            .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok'))
            .then(result => {
                processSlots(result?.data ? result.data : []);
                updateCalendarStyles();
            });

        clearSlotForm();
        updateSelectedDateDisplay();
    });

    $('#availabilityModal').on('shown.bs.modal', function () {
        if (!currentSelectedDate) {
            const today = moment().format('YYYY-MM-DD');
            calendarInstance.setDate(today, true);
            updateSelectedDate([new Date(today)]);
        }
    });

    $('button[data-bs-toggle="tab"][data-bs-target="#list-tab"]').on('shown.bs.tab', function (e) {
      updateListView();
    });
</script>
@stop
