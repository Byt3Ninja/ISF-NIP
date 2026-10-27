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
    .slot-popup.popover {
      position: absolute;
      z-index: 9999;
      pointer-events: auto;
      font-size: 12px;
      white-space: nowrap;
      max-width: none;
    }
    .slot-popup.popover .popover-arrow {
      display: none;
    }
  </style>
@stop

@section('content')

  <div class="">
    <div class="">
      <div class="min-vh-100 card mb-5">
        <div class="card-header border-bottom mb-6">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ __('mentor.weekly_availability_slots') }}</h4>

            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#availabilityModal">
              <i class="ti ti-plus me-1"></i> {{ __('mentor.add_availability_slots') }}
            </button>
          </div>
        </div>
        <div class="card-body">
          <div id="main-calendar"></div>
        </div>
      </div>
    </div>

    <!-- ADD AVAILABILITY MODAL WITH CALENDAR -->
    <div class="modal fade" id="availabilityModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <form onsubmit="return false;">  
          <div class="modal-content">

            <div class="modal-header border-bottom pb-6">
            <div class="d-flex justify-content-between align-items-center w-100">
              <h5 class="modal-title d-flex align-items-center mb-0">
                <i class="ti ti-plus me-2"></i> {{ __('mentor.add_availability_slots') }}
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
                    <div class="col-lg-5 border-end ps-5 mb-6 mb-lg-0">
                      <div id="availability-calendar"></div>
                    </div>
                    <div class="col-lg-7 ps-8">
                      <div class="pe-4">

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
                              <input type="text" id="slot-start-time" class="form-control bg-white border" placeholder="09:00 AM">
                            </div>

                            <div class="col-md-4 mb-3">
                              <label class="form-label">{{ __('mentor.end_time') }}</label>
                              <input type="text" id="slot-end-time" class="form-control bg-white border" placeholder="05:00 PM">
                            </div>

                            <div class="col-md-4 mb-3">
                              <label class="form-label">{{ __('mentor.session_type') }}</label>
                              <select id="slot-session-type" class="form-select bg-white border">
                                <option value="Online">{{ __('mentor.online') }}</option>
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

                        <div id="slots-list-container" style="max-height: 280px; overflow-y: auto;">
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
                    <div id="list-view-add-slot-form" style="display: none;">
                      <div class="border rounded py-6 px-5 mb-4">
                        <h6 class="mb-3">{{ __('mentor.add_new_slot') }}</h6>
                        <div class="row mb-3">
                          <div class="col-md-6 col-lg-4 mb-3">
                            <label class="form-label">{{ __('mentor.day') }}</label>
                            <select id="list-view-day-select" class="form-select">
                              <option value="Sunday">{{ __('mentor.sunday') }}</option>
                              <option value="Monday">{{ __('mentor.monday') }}</option>
                              <option value="Tuesday">{{ __('mentor.tuesday') }}</option>
                              <option value="Wednesday">{{ __('mentor.wednesday') }}</option>
                              <option value="Thursday">{{ __('mentor.thursday') }}</option>
                              <option value="Friday">{{ __('mentor.friday') }}</option>
                              <option value="Saturday">{{ __('mentor.saturday') }}</option>
                            </select>
                          </div>
                          <div class="col-md-6 col-lg-3 mb-3">
                            <label class="form-label">{{ __('mentor.start_time') }}</label>
                            <input type="text" id="list-view-start-time" class="form-control" placeholder="09:00 AM">
                          </div>
                          <div class="col-md-6 col-lg-3 mb-3">
                            <label class="form-label">{{ __('mentor.end_time') }}</label>
                            <input type="text" id="list-view-end-time" class="form-control" placeholder="05:00 PM">
                          </div>
                          <div class="col-md-6 col-lg-2 mb-3">
                            <label class="form-label">{{ __('mentor.session_type') }}</label>
                            <select id="list-view-session-type" class="form-select">
                              <option value="Online">{{ __('mentor.online') }}</option>
                            </select>
                          </div>
                        </div>
                        <div class="d-flex gap-2">
                          <button type="button" class="btn btn-primary" id="save-list-view-slot">
                            <i class="ti ti-plus me-2"></i>
                            {{ __('admin.Add_New') }}
                          </button>
                          <button type="button" class="btn btn-outline-secondary" id="cancel-list-view-slot">
                            <i class="ti ti-x me-2"></i>
                            {{ __('mentor.cancel') }}
                          </button>
                        </div>
                      </div>
                    </div>
                    <div id="availability-list-view">
                      <!-- List view content will be injected here -->
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <div class="modal-footer border-top pt-6">
              <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.close') }}</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script src="/vuexy/assets/vendor/libs/fullcalendar/fullcalendar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/ar.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>

<script>
  // Constants
  const isOfflineAllowed = {!! json_encode($offline_allowed) !!};

  // Translation data
  const translations = {!! json_encode(__('mentor')) !!};

  // ─── 12-hour time helper ───────────────────────────────────────────────────
  function to12Hour(time) {
      if (!time) return time;
      const parts = time.split(':');
      if (parts.length < 2) return time;
      let hours = parseInt(parts[0], 10);
      const minutes = parts[1];
      if (isNaN(hours)) return time;
      const ampm = hours >= 12 ? 'PM' : 'AM';
      hours = hours % 12;
      if (hours === 0) hours = 12;
      return `${hours}:${minutes} ${ampm}`;
  }

  function parse12HourToHHmm(timeStr) {
      if (!timeStr) return timeStr;
      const parsed = moment(timeStr, 'h:mm A', true);
      if (parsed.isValid()) return parsed.format('HH:mm');
      return timeStr;
  }
  // ──────────────────────────────────────────────────────────────────────────

  // ─── Overlap → Merge logic ────────────────────────────────────────────────
  /**
   * Given a new [startHHmm, endHHmm) and the existing slots array for a
   * type+key, compute the merged result:
   *
   *  1. Collect every slot that touches or overlaps the new range
   *     (overlap condition: newStart < existingEnd && newEnd > existingStart;
   *      touching boundaries like 09:00–10:00 + 10:00–11:00 are also merged).
   *  2. Expand the new range to cover all of them (union).
   *  3. Return { mergedStart, mergedEnd, idsToDelete } so the caller can
   *     DELETE the absorbed slots on the server, then POST the merged one.
   *
   * If there are no overlapping slots, idsToDelete is [] and the range is
   * unchanged – the caller just POSTs a brand-new slot as usual.
   */
  function computeMerge(type, key, startHHmm, endHHmm) {
      const existing = (availabilityData[type] && availabilityData[type][key])
          ? availabilityData[type][key]
          : [];

      let mergedStart = moment(startHHmm, 'HH:mm');
      let mergedEnd   = moment(endHHmm,   'HH:mm');
      const idsToDelete   = [];
      const idxToDelete   = [];
      let conflictSlot    = null;

      existing.forEach((slot, idx) => {
          const slotStart = moment(slot.start, 'HH:mm');
          const slotEnd   = moment(slot.end,   'HH:mm');

          // Overlaps OR touches (adjacent slots are merged too)
          const overlaps = mergedStart.isSameOrBefore(slotEnd) && mergedEnd.isSameOrAfter(slotStart);
          if (overlaps) {
              if (slotStart.isSame(mergedStart)) conflictSlot = slot;
              if (slotStart.isBefore(mergedStart)) mergedStart = slotStart;
              if (slotEnd.isAfter(mergedEnd))       mergedEnd   = slotEnd;
              idsToDelete.push(slot.id);
              idxToDelete.push(idx);
          }
      });

      return {
          mergedStart: mergedStart.format('HH:mm'),
          mergedEnd:   mergedEnd.format('HH:mm'),
          idsToDelete,
          idxToDelete,
          conflictSlot,
      };
  }

  /**
   * Delete a list of slot IDs from the server sequentially (fire-and-forget
   * errors are silently ignored so the UI never gets stuck).
   */
  async function deleteSlotIds(ids) {
      for (const id of ids) {
          try {
              await fetch(`/dashboard/availability/slots/${id}`, {
                  method: 'DELETE',
                  headers: {
                      "Accept": "application/json",
                      "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
                  },
              });
          } catch (e) {
              console.warn('Could not delete slot id', id, e);
          }
      }
  }

  /**
   * Remove absorbed slots from the local availabilityData store by index
   * (must be called with indices in descending order to avoid index shift).
   */
  function removeLocalSlotsByIndex(type, key, idxToDelete) {
      const sorted = [...idxToDelete].sort((a, b) => b - a);
      sorted.forEach(idx => {
          availabilityData[type][key].splice(idx, 1);
      });
      if (availabilityData[type][key] && availabilityData[type][key].length === 0) {
          delete availabilityData[type][key];
      }
  }
  // ──────────────────────────────────────────────────────────────────────────

  // Global variables
  let calendarInstance;
  let currentSelectedDate = null;
  let currentWeekday = null;
  let availabilityData = {
      specific: {},
      weekly: {}
  };
  let currentEventPopover = null;

  // Weekday translations for conflict messages
  const weekdayTranslations = {
      'Monday':    '{{ __('admin.Monday') }}',
      'Tuesday':   '{{ __('admin.Tuesday') }}',
      'Wednesday': '{{ __('admin.Wednesday') }}',
      'Thursday':  '{{ __('admin.Thursday') }}',
      'Friday':    '{{ __('admin.Friday') }}',
      'Saturday':  '{{ __('admin.Saturday') }}',
      'Sunday':    '{{ __('admin.Sunday') }}',
  };

  // Helper function to create a slot object
  const createSlotObject = (slot) => ({
      id: slot.id,
      start: slot.start_time,
      end: slot.end_time,
      date: slot.date || null,
      weekday: slot.weekday || null,
      sessionType: slot.session_type,
      duration: moment(slot.end_time, 'HH:mm').diff(moment(slot.start_time, 'HH:mm'), 'minutes'),
      startsFrom: slot.starts_from || null
  });

  // UI update functions
  const updateCalendarStyles = () => {
      if (calendarInstance && typeof calendarInstance.redraw === 'function') {
          calendarInstance.redraw();
      }
  };

  const updateSelectedDateDisplay = () => {
      if (currentSelectedDate) {
          const currentWeekday = moment(currentSelectedDate).format('dddd').toLowerCase();
          const arabicDay = translations[currentWeekday];

          const monthName = moment(currentSelectedDate).format('MMMM').toLowerCase();
          const arabicMonth = translations[monthName];
          const formattedDate = `${arabicMonth} ${moment(currentSelectedDate).format('D, YYYY')}`;

          $('#selected-date-display').text(`${arabicDay}, ${formattedDate}`);
          $('#selected-weekday').text(translations[currentWeekday.toLowerCase()]);
          $('#slot-form-box').show();
      } else {
          $('#selected-date-display').text('{{ __('mentor.select_date_from_calendar') }}');
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

  // Function to get session type options HTML
  function getSessionTypeOptionsHTML(selected = 'Online') {
      let options = `<option value="Online" ${selected === 'Online' ? 'selected' : ''}>${translations.online || 'Online'}</option>`;
      if (isOfflineAllowed) {
          options += `<option value="Offline" ${selected === 'Offline' ? 'selected' : ''}>${translations.offline || 'Offline'}</option>`;
          options += `<option value="Both" ${selected === 'Both' ? 'selected' : ''}>${translations.both || 'Both'}</option>`;
      }
      return options;
  }

  // Initialize session type select
  function initSessionTypeSelect() {
      $('#slot-session-type').html(getSessionTypeOptionsHTML());
      $('#list-view-session-type').html(getSessionTypeOptionsHTML());
  }

  // Fetch slots on page load
  document.addEventListener('DOMContentLoaded', function() {
      fetch('/dashboard/availability/slots', {
          headers: {
            "Accept": "application/json"
          }
        })
          .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok'))
          .then(result => {
              processSlots(result?.data ? result.data : []);
              updateCalendarStyles();
              initializeMainCalendar();
          })
          .catch(error => {
              console.error('Error fetching slots:', error);
              alert('{{ __('mentor.could_not_load_availability_slots') }}');
          });

      startTimePicker = flatpickr("#slot-start-time", {
          enableTime: true,
          noCalendar: true,
          dateFormat: "h:i K",
          time_24hr: false,
      });
      endTimePicker = flatpickr("#slot-end-time", {
          enableTime: true,
          noCalendar: true,
          dateFormat: "h:i K",
          time_24hr: false,
      });

      initSessionTypeSelect();

      calendarInstance = flatpickr("#availability-calendar", {
          inline: true,
          locale: '{{ $l }}',
          mode: "single",
          dateFormat: "Y-m-d",
          minDate: "today",
          defaultDate: 'today',
          onReady: (selectedDates) => updateSelectedDate(selectedDates),
          onChange: (selectedDates) => updateSelectedDate(selectedDates),
          onDayCreate: (dObj, dStr, fp, dayElem) => {
              const dateStr = moment(dayElem.dateObj).format('YYYY-MM-DD');
              const weekday = moment(dayElem.dateObj).format('dddd');
              const hasSpecific = availabilityData.specific[dateStr]?.length > 0;
              const hasWeekly = availabilityData.weekly[weekday]?.some(slot => !slot.startsFrom || dateStr >= slot.startsFrom);
              if (hasSpecific || hasWeekly) {
                  dayElem.classList.add('has-slots');
              }
          },
      });
  });

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

      for (const weekday in availabilityData.weekly) {
          availabilityData.weekly[weekday].forEach(slot => {
              const event = {
                  title: `${to12Hour(slot.start)} - ${to12Hour(slot.end)} (${slot.sessionType})`,
                  daysOfWeek: [moment().day(weekday).weekday()],
                  startTime: slot.start,
                  endTime: slot.end,
                  extendedProps: {
                      sessionType: slot.sessionType,
                      weekly: true,
                      id: slot.id
                  },
                  display: 'list-item'
              };
              if (slot.startsFrom) {
                  event.startRecur = slot.startsFrom;
              }
              events.push(event);
          });
      }

      for (const date in availabilityData.specific) {
          availabilityData.specific[date].forEach(slot => {
              events.push({
                  title: `${to12Hour(slot.start)} - ${to12Hour(slot.end)} (${slot.sessionType})`,
                  start: date + 'T' + slot.start,
                  end: date + 'T' + slot.end,
                  allDay: false,
                  extendedProps: {
                      sessionType: slot.sessionType,
                      weekly: false,
                      id: slot.id
                  },
                  display: 'list-item'
              });
          });
      }

      mainCalendarInstance = new FullCalendar.Calendar(mainCalendarEl, {
          initialView: 'dayGridMonth',
          locale: '{{ $l }}',
          headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              right: 'dayGridMonth,timeGridDay,listMonth'
          },
          buttonText: {
            today: '{{ $l }}' == 'en' ? 'Today' : 'اليوم',
            month: '{{ $l }}' == 'en' ? 'Month' : 'الشهر',
            day: '{{ $l }}' == 'en' ? 'Day' : 'اليوم',
            list: '{{ $l }}' == 'en' ? 'List' : 'قائمة',
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
                  iconEl.className = 'ti ti-repeat ti-16px text-dark m{{ $l == 'en' ? 's' : 'e' }}-2';
                  arrayOfDomNodes.push(iconEl);
              }

              return { domNodes: arrayOfDomNodes };
          },
          eventClick: function(arg) {
              const event = arg.event;
              const startTime = moment(event.start);
              const endTime = event.end ? moment(event.end) : moment(event.start).add(1, 'hour');
              const duration = endTime.diff(startTime, 'minutes');
              const repeatsStatus = event.extendedProps.weekly ? '{{ __("mentor.repeats_weekly") }}' : '{{ $l == "en" ? "Does not repeat" : "لا يتكرر" }}';

              if (currentEventPopover) {
                  currentEventPopover.dispose();
              }

              const popoverContent = `
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <div><small>{{ __("mentor.duration") }}:</small> <small class="fw-semibold">${duration} {{ __("mentor.min") }}</small></div>
                      <div><small>{{ __("mentor.repeats_weekly") }}:</small> <small class="fw-semibold">${event.extendedProps.weekly ? '{{ __('admin.Yes') }} ' : '{{ __('admin.No') }}'}</small></div>
                      <div class="d-flex mt-3">
                        <button class="btn btn-label-danger btn-xs delete-event-action">
                          <i class="ti ti-trash ti-14px me-1"></i>
                          {{ __('admin.Delete') }}
                        </button>
                      </div>
                    </div>
                    <button class="btn-close close-popover-action ms-6" aria-label="Close"></button>
                  </div>
              `;

              const popover = new bootstrap.Popover(arg.el, {
                  html: true,
                  content: popoverContent,
                  sanitize: false,
                  container: 'body',
                  trigger: 'manual'
              });

              popover.show();

              const popoverTip = popover.tip;
              popoverTip.classList.add('slot-popup');
              $(popoverTip).find('.close-popover-action').on('click', () => {
                  popover.dispose();
                  currentEventPopover = null;
                  $(document).off('click.outside-popover');
              });

              $(document).on('click.outside-popover', function(e) {
                  if (currentEventPopover && !$(e.target).closest('.popover').length && !$(e.target).is(arg.el) && !$(e.target).closest(arg.el).length) {
                      currentEventPopover.dispose();
                      currentEventPopover = null;
                      $(document).off('click.outside-popover');
                  }
              });

              $(popoverTip).find('.delete-event-action').on('click', () => {
                  const id = event.extendedProps.id;

                  releaseAlert('warning', {
                      title: "{{__('mentor.delete_slot_confirm')}}",
                      message: "{{__('admin.Recover')}}",
                      confirmText: "{{ __('admin.DYes') }}",
                      cancelText: "{{ __('admin.No') }}",
                      confirmColor: 'danger'
                  }).then((ok) => {
                      if (ok) {
                          if (id) {
                              fetch(`/dashboard/availability/slots/${id}`, {
                                  method: 'DELETE',
                                  headers: {
                                      "Accept": "application/json",
                                      "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                                  }
                              })
                              .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok'))
                              .then(result => {
                                  if(result.success) {
                                      toastr.success(result?.message ? result.message : "{{ __('mentor.slot_removed_success') }}");
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
                                  } else {
                                      toastr.error(result?.message ? result.message : "{{ __('admin.unexpected_error') }}");
                                  }
                              })
                              .catch(error => {
                                  toastr.error(error?.message ?? "{{ __('admin.unexpected_error') }}");
                              });
                          }
                      }
                  });

                  popover.dispose();
                  currentEventPopover = null;
              });

              currentEventPopover = popover;
          }
      });

      mainCalendarInstance.render();
  }

  function updateMainCalendar() {
      if (mainCalendarInstance) {
          mainCalendarInstance.destroy();
      }
      initializeMainCalendar();
  }

  const originalProcessSlots = processSlots;
  processSlots = function(slots) {
      originalProcessSlots(slots);
      updateMainCalendar();
  };

  $(document).off('click.delete-slot-override').on('click.delete-slot-override', '.delete-slot', function() {
      const { type, key, index, id } = $(this).data();

      releaseAlert('warning', {
          title: "{{__('mentor.delete_slot_confirm')}}",
          message: "{{__('admin.Recover')}}",
          confirmText: "{{ __('admin.DYes') }}",
          cancelText: "{{ __('admin.No') }}",
          confirmColor: 'danger'
      }).then((ok) => {
          if (ok) {
              availabilityData[type][key].splice(index, 1);
              if (availabilityData[type][key].length === 0) {
                  delete availabilityData[type][key];
              }

              if (id) {
                  fetch(`/dashboard/availability/slots/${id}`, {
                      method: 'DELETE',
                      headers: {
                          "Accept": "application/json",
                          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                      }
                  })
                  .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok'))
                  .then(result => {
                      if(result.success) {
                          toastr.success(result?.message ? result.message : "{{ __('mentor.slot_removed_success') }}");
                      } else {
                          toastr.error(result?.message ? result.message : "{{ __('admin.unexpected_error') }}");
                      }
                  })
                  .catch(error => {
                      toastr.error(error?.message ?? "{{ __('admin.unexpected_error') }}");
                  });
              }

              updateSlotsList();
              updateListView();
              updateCalendarStyles();
              updateMainCalendar();
          }
      });
  });

  // ─── Get slot from calendar-view form ─────────────────────────────────────
  function getSlotFromForm() {
      const startRaw    = startTimePicker.input.value;
      const endRaw      = endTimePicker.input.value;
      const sessionType = $('#slot-session-type').val();

      if (!startRaw || !endRaw) {
          alert('{{ __('mentor.please_select_start_and_end_time') }}');
          return null;
      }

      const startTime = moment(startRaw, 'h:mm A', true);
      const endTime   = moment(endRaw,   'h:mm A', true);

      if (!startTime.isValid() || !endTime.isValid() || endTime.isSameOrBefore(startTime)) {
          alert('{{ __('mentor.end_time_must_be_after_start_time') }}');
          return null;
      }

      const start = startTime.format('HH:mm');
      const end   = endTime.format('HH:mm');
      return { start, end, sessionType, duration: endTime.diff(startTime, 'minutes') };
  }
  // ──────────────────────────────────────────────────────────────────────────

  // ─── Add a new slot (with automatic overlap merging) ──────────────────────
  async function addSlot(type) {
      const key = type === 'specific' ? currentSelectedDate : currentWeekday;
      if (!key) {
          alert('{{ __('mentor.please_select_date_first') }}');
          return;
      }

      const slot = getSlotFromForm();
      if (!slot) return;

      // Compute merged range (absorbs any overlapping / touching existing slots)
      const { mergedStart, mergedEnd, idsToDelete, idxToDelete, conflictSlot } = computeMerge(type, key, slot.start, slot.end);

      if (conflictSlot) {
          releaseAlert('warning', {
              hideCancel: true,
              title: '{{ __('admin.Not_Available') }}',
              message: '{{ __('mentor.overlap', ['date' => ':date','from' => ':from', 'to' => ':to']) }}'
                           .replace(':date', conflictSlot.date || weekdayTranslations[conflictSlot.weekday] || conflictSlot.weekday)
                           .replace(':from', conflictSlot.start)
                           .replace(':to',   conflictSlot.end),
          });
          return;
      }

      const wasMerged = idsToDelete.length > 0;

      try {
          // 1. Delete absorbed slots on the server
          if (wasMerged) {
              await deleteSlotIds(idsToDelete);
              removeLocalSlotsByIndex(type, key, idxToDelete);
          }

          // 2. POST the (possibly expanded) merged slot
          const response = await fetch('/dashboard/availability/slots/store', {
              headers: {
                  "Accept": "application/json",
                  "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
                  "Content-Type": 'application/json',
              },
              method: 'POST',
              body: JSON.stringify({
                  tfrom: mergedStart,
                  tto: mergedEnd,
                  session_type: slot.sessionType,
                  type: type === 'weekly' ? 'Weekly' : 'Period',
                  date: type === 'specific' ? key : null,
                  days: type === 'weekly' ? key : null,
              }),
          });

          if (!response.ok) throw await response.json();
          const result = await response.json();

          // 3. Update local store
          if (Array.isArray(result.data)) {
              result.data.forEach(s => {
                  availabilityData[type][key] = [...(availabilityData[type][key] || []), createSlotObject(s)];
              });
          } else {
              availabilityData[type][key] = [...(availabilityData[type][key] || []), createSlotObject(result.data)];
          }

          updateSlotsList();
          updateListView();
          clearSlotForm();
          updateCalendarStyles();

          const toastMsg = type === 'specific'
              ? "{{ __('mentor.slot_added_to_date', ['date' => ':date']) }}".replace(':date', moment(key).format('MMMM D, YYYY'))
              : "{{ __('mentor.slot_added_to_all', ['day' => ':day']) }}".replace(':day', key + 's');

          toastr.success(toastMsg);

      } catch (error) {
          toastr.error(error?.message ?? "{{ __('admin.unexpected_error') }}");
      }
  }
  // ──────────────────────────────────────────────────────────────────────────

  $('#add-single-slot').click(() => addSlot('specific'));
  $('#apply-to-weekday').click(() => addSlot('weekly'));

  function renderAddNewSlotBox(visible = false) {
    return `
      <div id="list-view-add-slot-form" style="display: ${visible ? 'block' : 'none'};">
        <div class="border rounded p-4 bg-light-subtle mb-4">
          <h6 class="mb-3">{{ __('mentor.add_new_slot') }}</h6>
          <div class="row mb-3">
            <div class="col-md-6 col-lg-4 mb-3">
              <label class="form-label">{{ __('mentor.day') }}</label>
              <select id="list-view-day-select" class="form-select">
                <option value="Sunday">{{ __('mentor.sunday') }}</option>
                <option value="Monday">{{ __('mentor.monday') }}</option>
                <option value="Tuesday">{{ __('mentor.tuesday') }}</option>
                <option value="Wednesday">{{ __('mentor.wednesday') }}</option>
                <option value="Thursday">{{ __('mentor.thursday') }}</option>
                <option value="Friday">{{ __('mentor.friday') }}</option>
                <option value="Saturday">{{ __('mentor.saturday') }}</option>
              </select>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
              <label class="form-label">{{ __('mentor.start_time') }}</label>
              <input type="text" id="list-view-start-time" class="form-control" placeholder="09:00 AM">
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
              <label class="form-label">{{ __('mentor.end_time') }}</label>
              <input type="text" id="list-view-end-time" class="form-control" placeholder="05:00 PM">
            </div>
            <div class="col-md-6 col-lg-2 mb-3">
              <label class="form-label">{{ __('mentor.session_type') }}</label>
              <select id="list-view-session-type" class="form-select">
                ${getSessionTypeOptionsHTML()}
              </select>
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-primary" id="save-list-view-slot">
              {{ __('admin.Save') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="cancel-list-view-slot">
              {{ __('mentor.cancel') }}
            </button>
          </div>
        </div>
      </div>
    `;
  }

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
                      <div class="fw-bold text-dark mb-1">${to12Hour(slot.start)} - ${to12Hour(slot.end)}</div>
                      <div class="small text-muted">${slot.duration} {{ __('mentor.min') }} • ${translations[slot.sessionType.toLowerCase()]}${repeatsText}</div>
                  </div>
                  <button type="button" class="btn btn-label-danger btn-sm btn-icon delete-slot" data-type="${type}" data-key="${key}" data-index="${index}" data-id="${slot.id}">
                      <i class="ti ti-trash ti-16px"></i>
                  </button>
              </div>`;
      };
      
      const renderGroup = (title, slots, type, key, emptyMsg) => {
          let filteredSlots = slots;
          if (type === 'weekly' && currentSelectedDate && slots?.length) {
              filteredSlots = slots.filter(slot => {
                  if (!slot.startsFrom) return true;
                  return currentSelectedDate >= slot.startsFrom;
              });
          }
          return `
          <div class="mb-3 mt-3"><strong>${title}</strong></div>
          ${(filteredSlots?.length)
              ? filteredSlots.map((slot, index) => {
                  const originalIndex = slots.indexOf(slot);
                  return renderSlotHTML(slot, type, key, originalIndex);
              }).join('')
              : `<p class="text-muted">${emptyMsg}</p>`
          }`;
      };

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

      const hasSlots = Object.values(availabilityData.weekly).some(slots => slots.length > 0) ||
                       Object.values(availabilityData.specific).some(slots => slots.length > 0);

      $('#list-view-add-slot-form').toggle(hasSlots);

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
                  <div class="${bgColor} border rounded p-3 mb-2 d-flex justify-content-between align-items-center" data-type="${slot.type}" data-key="${slot.key}" data-index="${originalIndex}" data-id="${slot.id}">
                      <div class="slot-details">
                          <div class="fw-bold text-dark mb-1">
                              <span class="slot-start">${to12Hour(slot.start)}</span> - <span class="slot-end">${to12Hour(slot.end)}</span>
                          </div>
                      <div class="small text-muted">
                          ${slot.duration} {{ __('mentor.min') }} • <span class="slot-session-type">${translations[slot.sessionType.toLowerCase()]}</span>${repeatsText}
                      </div>
                      </div>
                      <div class="slot-actions btn-group">
                          
                          <button type="button" class="btn btn-label-danger btn-sm btn-icon delete-slot" title="{{ __('mentor.delete') }}" data-type="${slot.type}" data-key="${slot.key}" data-index="${originalIndex}" data-id="${slot.id}">
                              <i class="ti ti-trash ti-16px"></i>
                          </button>
                      </div>
                  </div>`;
          });
          html += `</div>`;
      });

      if (html === '') {
          html = `
              <div class="text-center py-5" id="empty-list-view">
                  <p class="text-muted mb-3">{{ __('mentor.no_availability_slots_added') }}</p>
                  <button type="button" class="btn btn-primary" id="show-add-slot-form-btn">
                      <i class="ti ti-plus me-1"></i> {{ __('mentor.add_availability_slots') }}
                  </button>
              </div>
          `;
      }

      $('#availability-list-view').html(html);
  }

  $(document).on('click', '#show-add-slot-form-btn', function() {
    $('#empty-list-view').hide();
    $('#list-view-add-slot-form').show();
  });

  $(document).on('click', '#cancel-list-view-slot', function() {
      $('#list-view-add-slot-form').hide();
      $('#empty-list-view').show();
      $('#list-view-start-time').val('');
      $('#list-view-end-time').val('');
      $('#list-view-session-type').val('Online');
  });

  // ─── Save slot from list view (with automatic overlap merging) ────────────
  $(document).on('click', '#save-list-view-slot', async function() {
      const day         = $('#list-view-day-select').val();
      const startRaw    = $('#list-view-start-time').val();
      const endRaw      = $('#list-view-end-time').val();
      const sessionType = $('#list-view-session-type').val();

      if (!startRaw || !endRaw) {
          alert('{{ __('mentor.please_select_start_and_end_time') }}');
          return;
      }

      const startTime = moment(startRaw, 'h:mm A', true);
      const endTime   = moment(endRaw,   'h:mm A', true);

      if (!startTime.isValid() || !endTime.isValid() || endTime.isSameOrBefore(startTime)) {
          alert('{{ __('mentor.end_time_must_be_after_start_time') }}');
          return;
      }

      const start = startTime.format('HH:mm');
      const end   = endTime.format('HH:mm');

      // Compute merged range
      const { mergedStart, mergedEnd, idsToDelete, idxToDelete, conflictSlot } = computeMerge('weekly', day, start, end);

      if (conflictSlot) {
          releaseAlert('warning', {
              hideCancel: true,
              title: '{{ __('admin.Not_Available') }}',
              message: '{{ __('mentor.overlap', ['date' => ':date', 'from' => ':from', 'to' => ':to']) }}'
                           .replace(':date', conflictSlot.date || weekdayTranslations[conflictSlot.weekday] || conflictSlot.weekday)
                           .replace(':from', conflictSlot.start)
                           .replace(':to',   conflictSlot.end),
          });
          return;
      }

      const wasMerged = idsToDelete.length > 0;

      try {
          if (wasMerged) {
              await deleteSlotIds(idsToDelete);
              removeLocalSlotsByIndex('weekly', day, idxToDelete);
          }

          const response = await fetch('/dashboard/availability/slots/store', {
              headers: {
                  "Accept": "application/json",
                  "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
                  "Content-Type": 'application/json',
              },
              method: 'POST',
              body: JSON.stringify({
                  tfrom: mergedStart,
                  tto: mergedEnd,
                  session_type: sessionType,
                  type: 'Weekly',
                  date: null,
                  days: day,
              }),
          });

          if (!response.ok) throw new Error('Network response was not ok');
          const result = await response.json();

          if (Array.isArray(result.data)) {
              result.data.forEach(slot => {
                  availabilityData.weekly[day] = [...(availabilityData.weekly[day] || []), createSlotObject(slot)];
              });
          } else {
              availabilityData.weekly[day] = [...(availabilityData.weekly[day] || []), createSlotObject(result.data)];
          }

          updateListView();
          updateSlotsList();
          updateCalendarStyles();
          updateMainCalendar();

          // Reset form
          $('#list-view-start-time').val('');
          $('#list-view-end-time').val('');
          $('#list-view-session-type').val('Online');
          $('#list-view-day-select').val('Sunday');

          toastr.success(wasMerged
              ? `${result.message} ({{ __('mentor.slots_merged') }}: ${to12Hour(mergedStart)} – ${to12Hour(mergedEnd)})`
              : result.message,
          );

      } catch (error) {
          toastr.error(error?.message ?? "{{ __('admin.unexpected_error') }}");
      }
  });
  // ──────────────────────────────────────────────────────────────────────────

  // Handle Add (clone) action in list view
  $(document).on('click', '.add-slot-action', function() {
      const slotElement = $(this).closest('.d-flex');
      const { type, key, index } = slotElement.data();
      const originalSlot = availabilityData[type][key][index];

      const newSlotFormHtml = `
          <div class="editable-slot-form border rounded p-3 mb-2">
              <div class="row g-2 mb-2">
                  <div class="col-md-3">
                      <label class="form-label">@lang('mentor.start_time')</label>
                      <input type="text" class="form-control form-control-sm start-time-input" value="${to12Hour(originalSlot.start)}">
                  </div>
                  <div class="col-md-3">
                      <label class="form-label">@lang('mentor.end_time')</label>
                      <input type="text" class="form-control form-control-sm end-time-input" value="${to12Hour(originalSlot.end)}">
                  </div>
                  <div class="col-md-4">
                      <label class="form-label">@lang('mentor.session_type')</label>
                      <select class="form-select form-select-sm session-type-select">
                          ${getSessionTypeOptionsHTML(originalSlot.sessionType)}
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
          dateFormat: "h:i K",
          time_24hr: false,
      });
  });

  $(document).on('click', '.cancel-new-slot-action', function() {
      $(this).closest('.editable-slot-form').remove();
  });

  // ─── Save new slot from clone form (with automatic overlap merging) ────────
  $(document).on('click', '.save-new-slot-action', async function() {
      const form        = $(this).closest('.editable-slot-form');
      const startRaw    = form.find('.start-time-input').val();
      const endRaw      = form.find('.end-time-input').val();
      const sessionType = form.find('.session-type-select').val();

      const startTime = moment(startRaw, 'h:mm A', true);
      const endTime   = moment(endRaw,   'h:mm A', true);

      if (!startTime.isValid() || !endTime.isValid() || endTime.isSameOrBefore(startTime)) {
          alert('{{ __('mentor.end_time_must_be_after_start_time') }}');
          return;
      }

      const start = startTime.format('HH:mm');
      const end   = endTime.format('HH:mm');

      const { originalType, originalKey } = $(this).data();

      // Compute merged range
      const { mergedStart, mergedEnd, idsToDelete, idxToDelete } = computeMerge(originalType, originalKey, start, end);
      const wasMerged = idsToDelete.length > 0;

      try {
          if (wasMerged) {
              await deleteSlotIds(idsToDelete);
              removeLocalSlotsByIndex(originalType, originalKey, idxToDelete);
          }

          const response = await fetch('/dashboard/availability/slots/store', {
              headers: {
                  "Accept": "application/json",
                  "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
                  "Content-Type": 'application/json',
              },
              method: 'POST',
              body: JSON.stringify({
                  tfrom: mergedStart,
                  tto: mergedEnd,
                  session_type: sessionType,
                  type: originalType === 'weekly' ? 'Weekly' : 'Period',
                  date: originalType === 'specific' ? originalKey : null,
                  days: originalType === 'weekly' ? originalKey : null,
              }),
          });

          const result = await response.json();
          if (!result.success) throw new Error(result.message || '{{ __('admin.unexpected_error') }}');

          if (Array.isArray(result.data)) {
              result.data.forEach(s => {
                  availabilityData[originalType][originalKey] = [...(availabilityData[originalType][originalKey] || []), createSlotObject(s)];
              });
          } else {
              availabilityData[originalType][originalKey] = [...(availabilityData[originalType][originalKey] || []), createSlotObject(result.data)];
          }

          updateListView();
          updateSlotsList();
          updateCalendarStyles();

          toastr.success(result.message ? result.message : "{{ __('mentor.slot_added_success') }}");

          form.remove();

      } catch (error) {
          toastr.error(error?.message ?? "{{ __('admin.unexpected_error') }}");
      }
  });
  // ──────────────────────────────────────────────────────────────────────────

  // Handle Copy action in list view
  $(document).on('click', '.copy-slot-action', function (e) {
      e.stopPropagation();

      const button = this;
      const slotElement = $(button).closest('.d-flex');
      const { type, key, index } = slotElement.data();

      if (!type || !key || index === undefined) {
          console.error('Missing slot data attributes:', { type, key, index });
          return;
      }

      const sourceSlot = availabilityData?.[type]?.[key]?.[index];

      if (!sourceSlot) {
          console.error('Slot not found in availabilityData:', { type, key, index });
          return;
      }

      const slotToCopy = { ...sourceSlot };

      $('.copy-slot-action').popover('dispose');
      $(document).off('mousedown.copyPopover');

      const dayValues = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
      const currentDayIndex = dayValues.indexOf(key);

      const translatedDays = [
          '{{ __('mentor.sunday') }}',
          '{{ __('mentor.monday') }}',
          '{{ __('mentor.tuesday') }}',
          '{{ __('mentor.wednesday') }}',
          '{{ __('mentor.thursday') }}',
          '{{ __('mentor.friday') }}',
          '{{ __('mentor.saturday') }}'
      ];

      const popoverContent = `
          <div>
              ${translatedDays.map((day, i) => `
                  <div class="form-check">
                      <input 
                          class="form-check-input"
                          type="checkbox"
                          value="${dayValues[i]}"
                          id="day-copy-${dayValues[i]}"
                          ${i === currentDayIndex ? 'disabled' : ''}
                      >
                      <label class="form-check-label" for="day-copy-${dayValues[i]}">
                          ${day}
                      </label>
                  </div>
              `).join('')}
              <div class="d-flex justify-content-end mt-3">
                  <button class="btn btn-primary btn-sm apply-copy-action">
                      {{ __('mentor.apply_to_all') }}
                  </button>
              </div>
          </div>
      `;

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

      setTimeout(() => {
          $(document).on('mousedown.copyPopover', function (e) {
              if (
                  !$(button).is(e.target) &&
                  $(button).has(e.target).length === 0 &&
                  !$(e.target).closest('.popover').length
              ) {
                  popover.dispose();
                  $(document).off('mousedown.copyPopover');
              }
          });
      }, 0);

      $(popoverTip).find('.apply-copy-action').on('click', () => {

          const selectedDays = Array.from(
              $(popoverTip).find('.form-check-input:checked')
          ).map(cb => cb.value);

          if (!selectedDays.length) {
              popover.dispose();
              $(document).off('mousedown.copyPopover');
              return;
          }

          fetch(`/dashboard/availability/slots/${slotToCopy.id}/copy`, {
              method: 'POST',
              headers: {
                  "Accept": "application/json",
                  "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
                  "Content-Type": 'application/json',
              },
              body: JSON.stringify({ selectedDays }),
          })
          .then(response =>
              response.ok
                  ? response.json()
                  : Promise.reject('Network response was not ok')
          )
          .then(result => {

            result.data.forEach(slot => {
                const newSlot = createSlotObject(slot);

                if (slot.weekly) {
                    const day = slot.weekday;
                    if (!availabilityData.weekly[day]) {
                        availabilityData.weekly[day] = [];
                    }
                    availabilityData.weekly[day].push(newSlot);
                } else {
                    const date = slot.date;
                    if (!availabilityData.specific[date]) {
                        availabilityData.specific[date] = [];
                    }
                    availabilityData.specific[date].push(newSlot);
                }
            });

              updateListView();
              updateSlotsList();
              updateCalendarStyles();

              toastr.success(result?.message || "{{ __('mentor.slots_copied_success') }}");
          })
          .catch(error => {
              toastr.error(error?.message ?? "{{ __('admin.unexpected_error') }}");
          });

          popover.dispose();
          $(document).off('mousedown.copyPopover');
      });
  });

  // Reset modal
  $('#availabilityModal').on('hidden.bs.modal', function () {
      $('.copy-slot-action').popover('dispose');
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
    if ($('#list-view-start-time').length && !$('#list-view-start-time')[0]._flatpickr) {
      flatpickr("#list-view-start-time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false,
      });
    }
    if ($('#list-view-end-time').length && !$('#list-view-end-time')[0]._flatpickr) {
      flatpickr("#list-view-end-time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false,
      });
    }
  });
</script>
@stop
