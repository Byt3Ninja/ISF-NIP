@extends('layouts.admin')
{{-- @section('bodyclass')
    calendar-application
@stop --}}

@section('vendorstyle')
  <!-- Calendar Vendor css -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/fullcalendar/fullcalendar.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
  <!-- Calendar Vendor css -->
@stop

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-calendar.css" />
  <style>
    .fc .fc-button .fc-icon {
      font-size: 1.5rem;
      color: #000;
      vertical-align: middle;
    }
    .fc .fc-toolbar .fc-toolbar-chunk:nth-child(3) {
      margin-top: 0.5rem;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 1rem;
    }
    .fc-event, .fc-event-main {
      background-color: var(--bs-primary-bg-subtle) !important;
    }

    .fc-event-time {
      display: none;
    }

    .fc-event-title {
      color: var(--bs-black) !important;
      font-size: 12px !important;
    }
  </style>
@stop

@section('content')
  <!-- Content wrapper -->
  <div class="">
    <!-- Content -->

    @if( session()->has('success') || session()->has('error') || session()->has('msg') )
    <div class="">
      @if( session()->has('success') )
      <div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          <div class="d-flex align-items-center">
            <i class="ti ti-thumb-up me-3 ti-md"></i>
            <span> {{ session()->get('success') }} </span>
          </div>
      </div>
      @endif

      @if( session()->has('error') )
      <div role="alert" class="alert alert-danger alert-dismissible fade show mb-2">
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          <div class="d-flex align-items-center">
            <i class="ti ti-alert-triangle me-3 ti-md"></i>
            <span> {{ session()->get('error') }} </span>
          </div>
      </div>
      @endif

      @if( session()->has('msg') )
      <div role="alert" class="alert alert-success alert-dismissible fade show mb-2">
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          <div class="d-flex align-items-center">
            <i class="ti ti-thumb-up me-3 ti-md"></i>
            <span> {{ __('admin.'.session()->get('msg')) }} {{ Session::forget('msg') }} </span>
          </div>
      </div>
      @endif
    </div>
    @endif
    <div class="">
      <div class="card app-calendar-wrapper">
        <div class="row g-0">
          <!-- Calendar Sidebar -->
          <div class="col app-calendar-sidebar border-end" id="app-calendar-sidebar">
            <div class="border-bottom p-6 my-sm-0 mb-4">
              {{-- <button class="btn btn-primary btn-toggle-sidebar w-100" data-bs-toggle="offcanvas"
                data-bs-target="#addEventSidebar" aria-controls="addEventSidebar">
                <i class="ti ti-plus ti-16px me-2"></i>
                <span class="align-middle">Add Event</span>
              </button> --}}
            </div>
            <div class="px-3 pt-2">
              <!-- inline calendar (flatpicker) -->
              <div class="inline-calendar"></div>
            </div>
            <hr class="mb-6 mx-n4 mt-3" />
            {{-- <div class="px-6 pb-2"> --}}
              <!-- Filter -->
              {{-- <div>
                <h5>Event Filters</h5>
              </div> --}}

              {{-- <div class="form-check form-check-secondary mb-5 ms-2">
                <input class="form-check-input select-all" type="checkbox" id="selectAll" data-value="all" checked />
                <label class="form-check-label" for="selectAll">View All</label>
              </div> --}}

              {{-- <div class="app-calendar-events-filter text-heading">
                <div class="form-check form-check-danger mb-5 ms-2">
                  <input class="form-check-input input-filter" type="checkbox" id="select-personal" data-value="personal"
                    checked />
                  <label class="form-check-label" for="select-personal">Personal</label>
                </div>
                <div class="form-check mb-5 ms-2">
                  <input class="form-check-input input-filter" type="checkbox" id="select-business" data-value="business"
                    checked />
                  <label class="form-check-label" for="select-business">Business</label>
                </div>
                <div class="form-check form-check-warning mb-5 ms-2">
                  <input class="form-check-input input-filter" type="checkbox" id="select-family" data-value="family"
                    checked />
                  <label class="form-check-label" for="select-family">Family</label>
                </div>
                <div class="form-check form-check-success mb-5 ms-2">
                  <input class="form-check-input input-filter" type="checkbox" id="select-holiday" data-value="holiday"
                    checked />
                  <label class="form-check-label" for="select-holiday">Holiday</label>
                </div>
                <div class="form-check form-check-info ms-2">
                  <input class="form-check-input input-filter" type="checkbox" id="select-etc" data-value="etc" checked />
                  <label class="form-check-label" for="select-etc">ETC</label>
                </div>
              </div> --}}
            {{-- </div> --}}
          </div>
          <!-- /Calendar Sidebar -->

          <!-- Calendar & Modal -->
          <div class="col app-calendar-content">
            <div class="card shadow-none border-0">
              <div class="card-body pb-0">
                <!-- FullCalendar -->
                <div id="calendar"></div>
              </div>
            </div>
            <div class="app-overlay"></div>
            <!-- FullCalendar Offcanvas -->
            <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="addEventSidebar"
              aria-labelledby="addEventSidebarLabel">
              {{-- <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="addEventSidebarLabel">Add Event</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                  aria-label="Close"></button>
              </div> --}}
              <div class="offcanvas-body">
                <form class="event-form pt-0" id="eventForm" onsubmit="return false">
                  <div class="mb-5">
                    <label class="form-label" for="eventTitle">Title</label>
                    <input type="text" class="form-control" id="eventTitle" name="eventTitle"
                      placeholder="Event Title" />
                  </div>
                  <div class="mb-5">
                    <label class="form-label" for="eventLabel">Label</label>
                    <select class="select2 select-event-label form-select" id="eventLabel" name="eventLabel">
                      <option data-label="primary" value="Business" selected>Business</option>
                      <option data-label="danger" value="Personal">Personal</option>
                      <option data-label="warning" value="Family">Family</option>
                      <option data-label="success" value="Holiday">Holiday</option>
                      <option data-label="info" value="ETC">ETC</option>
                    </select>
                  </div>
                  <div class="mb-5">
                    <label class="form-label" for="eventStartDate">Start Date</label>
                    <input type="text" class="form-control" id="eventStartDate" name="eventStartDate"
                      placeholder="Start Date" />
                  </div>
                  <div class="mb-5">
                    <label class="form-label" for="eventEndDate">End Date</label>
                    <input type="text" class="form-control" id="eventEndDate" name="eventEndDate"
                      placeholder="End Date" />
                  </div>
                  <div class="mb-5">
                    <div class="form-check form-switch">
                      <input type="checkbox" class="form-check-input allDay-switch" id="allDaySwitch" />
                      <label class="form-check-label" for="allDaySwitch">All Day</label>
                    </div>
                  </div>
                  <div class="mb-5">
                    <label class="form-label" for="eventURL">Event URL</label>
                    <input type="url" class="form-control" id="eventURL" name="eventURL"
                      placeholder="https://www.google.com" />
                  </div>
                  <div class="mb-4 select2-primary">
                    <label class="form-label" for="eventGuests">Add Guests</label>
                    <select class="select2 select-event-guests form-select" id="eventGuests" name="eventGuests"
                      multiple>
                      <option data-avatar="1.png" value="Jane Foster">Jane Foster</option>
                      <option data-avatar="3.png" value="Donna Frank">Donna Frank</option>
                      <option data-avatar="5.png" value="Gabrielle Robertson">Gabrielle Robertson</option>
                      <option data-avatar="7.png" value="Lori Spears">Lori Spears</option>
                      <option data-avatar="9.png" value="Sandy Vega">Sandy Vega</option>
                      <option data-avatar="11.png" value="Cheryl May">Cheryl May</option>
                    </select>
                  </div>
                  <div class="mb-5">
                    <label class="form-label" for="eventLocation">Location</label>
                    <input type="text" class="form-control" id="eventLocation" name="eventLocation"
                      placeholder="Enter Location" />
                  </div>
                  <div class="mb-5">
                    <label class="form-label" for="eventDescription">Description</label>
                    <textarea class="form-control" name="eventDescription" id="eventDescription"></textarea>
                  </div>
                  <div class="d-flex justify-content-sm-between justify-content-start mt-6 gap-2">
                    <div class="d-flex">
                      <button type="submit" id="addEventBtn" class="btn btn-primary btn-add-event me-4">
                        Add
                      </button>
                      <button type="reset" class="btn btn-label-secondary btn-cancel me-sm-0 me-1"
                        data-bs-dismiss="offcanvas">
                        Cancel
                      </button>
                    </div>
                    <button class="btn btn-label-danger btn-delete-event d-none">Delete</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!-- /Calendar & Modal -->
        </div>
      </div>
    </div>
    <!-- / Content -->
  </div>
@stop

<!-- Vendors JS -->
@section('scripts')
  <script src="/vuexy/assets/vendor/libs/fullcalendar/fullcalendar.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
  <script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
  @include('partials.flatpickr-locale')
@stop

<!-- Page JS -->
@section('pagescripts')
  <script>
    // This siteUrl used in app-calendar.js
    
    var siteUrlFromBlade = "{{ url('/') }}";
    /* customButtons: {
      google: {
          text: 'Sync With Google Calender',
          click: function () {
              /// add
              window.open('{{ route('integrations.calendar_google_login') }}','_Self')
              //// end
          }
      },
      microsoft: {
          text: 'Sync With Microsoft Calender',
          click: function () {
              /// add
              window.open('{{ route('integrations.calendar_microsoft_login') }}','_Self')
              //// end
          }
      },

  } */



  </script>
  <script src="/vuexy/assets/js/app-calendar.js"></script>
  {{-- <script src="/vuexy/assets/js/app-calendar-events.js"></script> --}}
  {{-- <script src="/vuexy/assets/js/app-calendar-custom.js"></script> --}}
@stop
