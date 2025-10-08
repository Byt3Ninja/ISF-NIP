<div class="col-md-12">
  <!-- users view card details start -->
  <div class="card">
    <div class="card-header border-bottom">
      <h4 class="card-title d-flex align-items-center mb-3">
        <i class='ti ti-briefcase ti-28px'></i> 
        <span class="mx-2">{{ $cycle->title }}</span>
      </h4>
      <span>
        {{ __('dashboard.start from') . ': ' . $cycle->start_format . ' ' . __('dashboard.to') . ': ' . $cycle->end_format }}
      </span>

      <div class="d-flex align-items-center mt-2">
        <div class="media-left mr-50">
          <img 
            src="{{ $cycle->organization->image_url }}"
            alt="avatar"
            class="rounded-circle"
            height="40"
            width="40"
          />
        </div>
        <span class="mx-2">
          {{ $cycle->organization->name }}
        </span>
      </div>
    </div>
  </div>
  <!-- users view card details ends -->
</div>

<div class="col-12 mt-6">
  <div class="row g-6">
  <a href="{{ route('users.view_companies', ['programFilter' => $cycle->id]) }}" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-building ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $startups }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Subscribe Startups in Cycle') }}</p>
        </div>
      </div>
    </a>

  <a href="{{ route('mentors.list', ['enrolledInProgramFilter' => 1, 'programFilter' => $cycle->id]) }}" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-warning h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti ti-school ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $mentors_count }}</h4>
          </div>
          <p class="mb-1">{{ __('cycles.Mentors_Enrolled_In_Programs') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/mentorship/sessions?show=all" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-success h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti ti-book ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $mentorRequests_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Mentorship requests') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/mentorship/sessions?show=all" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-danger h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="ti ti-clock ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $mentorships_period }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Mentorship requests Duration') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/mentorship/sessions?show=all" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-secondary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="ti ti-browser ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ (int) $mentorship_attendance_rate }} %</h4>
          </div>
          <p class="mb-1">{{ __('admin.Company Attendance Rate') }}</p>
        </div>
      </div>
    </a>

    <a href="#" class="col-lg-4 col-sm-6 scroll-to-table">
      <div class="card card-border-shadow-warning h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti ti-user-screen ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $training_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Training sessions') }}</p>
        </div>
      </div>
    </a>

    <a href="#" class="col-lg-4 col-sm-6 scroll-to-table">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-clock ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $training_sessions_period }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Training sessions Duration') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/screening/results" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-success h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti ti-rocket ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $screening_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('dashboard.First screening') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/final/results" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-telescope ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $final_screening_count }}</h4>
          </div>
          <p class="mb-1">{{ __('dashboard.Final screening') }}</p>
        </div>
      </div>
    </a>
  </div>
</div>

<div class="card border">
  <!-- Heading & Actions -->
  <div class="card-header d-md-flex justify-content-between border-bottom pb-3 mb-6">
    <h5 class="card-title">{{ __('admin.Training sessions') }}</h5>
    <div class="d-flex align-items-center gap-3">
      <div class="btn-group">
        <button type="button" class="btn btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="ti ti-file-export me-2"></i>{{ __('settings.Export') }}
        </button>
        <ul class="dropdown-menu">
          @foreach(['csv', 'excel'] as $key => $option)
            <li>
              <a class="dropdown-item text-uppercase" href="javascript:void(0)" onclick="exportTraining('{{$option}}')">
                {{$option}}
              </a>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>

  <div class="card-body">
    <!-- Table Filter -->
    <div class="row d-flex justify-content-end mb-2" id="tblTraining-filters">
      <div class="col-12 col-md-2">
        <div class="from-group mb-2 mb-md-0">
          <select class="form-select form-select-sm" id="type-filter">
            <option value="" selected>{{ __('cycles.Select_Training_Type') }}</option>
            @foreach(['Online_Training', 'Online_Session', 'Offline_Session'] as $type)
              <option value="{{ $type }}">{{ __('cycles.'.$type) }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="from-group mb-2 mb-md-0">
          <select class="form-select form-select-sm" id="cycle-filter">
            <option value="" selected>{{ __('cycles.Select_Cycle') }}</option>
            @foreach($cycles as $cycle)
              <option value="{{ $cycle->id }}">{{ $cycle->title }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="from-group mb-2 mb-md-0">
          <select class="form-select form-select-sm" id="step-filter">
            <option value="" selected>{{ __('cycles.Select_Step') }}</option>
            @foreach ($steps as $step)
              <option value="{{ $step->id }}">{{ $step->title }}
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-12 col-md-2">
        <div class="from-group mb-2 mb-md-0">
          <input 
            type="text" 
            id="start-date-filter"
            class="form-control form-control-sm datepicker"
            placeholder="{{ __('cycles.Start_Date') }}"
          />
        </div>
      </div>
      <div class="col-12 col-md-2">
        <div class="from-group mb-2 mb-md-0">
          <input 
            type="text" 
            id="end-date-filter" 
            class="form-control form-control-sm datepicker"
            placeholder="{{ __('cycles.End_Date') }}"
          />
        </div>
      </div>
    </div>

    <!-- Table Data -->
    <div class="table-responsive">
      <table class="table table-hover" id="tblTraining">
        <thead class="table-light">
          <tr>
            <th>{{ __('cycles.Title') }}</th>
            <th>{{ __('cycles.Type') }}</th>
            <th>{{ __('cycles.Program') }}</th>
            <th>{{ __('cycles.Training_Step') }}</th>
            <th>{{ __('cycles.Date_Time') }}</th>
            <th>{{ __('cycles.Duration') }}</th>
            <th>{{ __('cycles.details') }}</th>
            <th>{{ __('cycles.Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($training_sessions as $session)
            <tr>
              {{-- Title --}}
              <td><small class="text-nowrap">{{ $session->title }}</small></td>

              {{-- Type (badge) --}}
              <td>
                <small class="badge bg-label-{{ $session->type == 'offline' ? 'primary' : ($session->type == 'sessions' ? 'info' : 'success') }}">
                  {{ $session->type == 'offline' ? __('cycles.Offline_Session') : ($session->type == 'sessions' ? __('cycles.Online_Session') : __('cycles.Online_Training')) }}
                </small>
              </td>

              {{-- Program --}}
              <td><small class="text-nowrap">{{ $session->step->cycle->title }}</small></td>

              {{-- Training Step --}}
              <td><small class="text-nowrap">{{ $session->step->title }}</small></td>

              {{-- Date & Time --}}
              <td>
                <small class="text-nowrap">
                  @php
                    $startDate = FormatDate($session->datetime, 'Y-m-d');
                    $endDate   = $session->enddatetime ? FormatDate($session->enddatetime, 'Y-m-d') : null;
                    $startTime = FormatDate($session->datetime, 'H:i:s');
                    $endTime   = $session->enddatetime ? FormatDate($session->enddatetime, 'H:i:s') : null;
                  @endphp

                  {{-- Show dates --}}
                  @if($endDate && $endDate !== $startDate)
                    {{ $startDate }} - {{ $endDate }}
                  @else
                    {{ $startDate }}
                  @endif
                </small>
                <br>
                {{-- Show times --}}
                <small>
                  @if($endTime && $endTime !== $startTime)
                    {{ $startTime }} - {{ $endTime }}
                  @else
                    {{ $startTime }}
                  @endif
                </small>
              </td>
              @php
                    $hours = floor($session->duration / 60);
                    $minutes = $session->duration % 60;
               @endphp
                <td>
                    {{ sprintf('%02d:%02d', $hours, $minutes) }}
                </td>

              {{-- Details (Trainer + Address if offline) --}}
              <td>
                @if($session->type == "offline" || $session->type == "sessions")
                  <div class="text-nowrap">{{ __('cycles.trainer name') }}:</div> {{ $session->trainer_name }}<br>
                  @if(!empty($session->training_Address))
                    <small class="text-nowrap">{{ __('cycles.Training_Address') }}:</small> {{ $session->training_Address }}
                  @endif
                 @endif
              </td>

              {{-- Actions --}}
              <td>
                <div class="d-flex align-items-center gap-2">
                  {{-- Online session (Zoom) --}}
                  @if ($session->type == 'sessions')
                    @if ($session->start_url)
                      <a href="{{ $session->start_url }}" target="_blank" class="btn btn-icon btn-label-success rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('cycles.Start_Meeting') }}">
                        <i class="ti ti-brand-zoom"></i>
                      </a>
                    @endif
                    @if ($session->join_url)
                      <a href="{{ $session->join_url }}" target="_blank" class="btn btn-icon btn-label-info rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('cycles.Join_Meeting') }}">
                        <i class="ti ti-user"></i>
                      </a>
                    @endif
                    @if(!empty($session->zoom_passcode))
                      <button data-passcode="{{ $session->zoom_passcode }}" class="btn btn-icon btn-label-warning rounded-circle show-passcode">
                        <i class="ti ti-key"></i>
                      </button>
                    @endif
                    <button data-bs-toggle="modal" data-bs-target="#reportModal{{ $session->id }}" class="btn btn-icon btn-label-warning rounded-circle">
                      <i class="ti ti-file-text"></i>
                    </button>
                  @endif
                  {{-- Offline session --}}
                  @if ($session->type == 'offline')
                    @if ($session->QrCode)
                      <button data-bs-toggle="modal" data-bs-target="#qrCodeModal{{ $session->id }}" class="btn btn-icon btn-label-warning rounded-circle">
                        <i class="ti ti-qrcode"></i>
                      </button>
                    @else
                      <button onclick="createQrCode({{ $session->id }})" class="btn btn-icon btn-label-success rounded-circle">
                        <i class="ti ti-qrcode"></i>
                      </button>
                    @endif

                    @if ($session->training_location)
                      <a href="{{ $session->training_location }}" class="btn btn-icon btn-label-primary rounded-circle" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('cycles.View_Location') }}">
                        <i class="ti ti-map-pin"></i>
                      </a>
                    @endif

                    <a href="/dashboard/training/Attendance-offline/{{ $session->id }}" class="btn btn-icon btn-label-dark rounded-circle">
                      <i class="ti ti-calendar-check"></i>
                    </a>
                  @endif

                  {{-- Online training --}}
                  @if ($session->type == 'online')
                    <a href="/dashboard/training/all/{{ $session->training_Address }}">
                      <button class="btn btn-icon btn-label-dark rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('cycles.View_Course') }}">
                        <i class="ti ti-book"></i>
                      </button>
                    </a>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
      // Scroll to training table section
    $('.scroll-to-table').on('click', function (e) {
    e.preventDefault();
    $('html, body').animate({
      scrollTop: $('#trainingTableSection').offset().top - 100 
    }, 500);
     });
    // Handle filter card clicks
    $('.filter-card').on('click', function() {
      const filter = $(this).data('filter');
      
      // Reset all cards' active state
      $('.filter-card').removeClass('border-primary');
      
      if (filter === 'reset') {
        // Reset all filters
        $('#type-filter, #cycle-filter, #step-filter').val('').trigger('change');
        $('#start-date-filter, #end-date-filter').val('').trigger('change');
      } else if (filter) {
        // If this card has a filter value
        if ($('#type-filter').val() === filter) {
          // If current filter is already active, clear it
          $('#type-filter').val('').trigger('change');
          $(this).removeClass('border-primary');
        } else {
          // Apply new filter
          $('#type-filter').val(filter).trigger('change');
          $(this).addClass('border-primary');
        }
      }
    });

    // Initalize datepicker
    $('.datepicker').flatpickr({
      monthSelectorType: 'static',
      allowInput: true,
      onReady: function (selectedDates, dateStr, instance) {
        let btn = document.createElement("button");
        btn.type = "button";
        btn.innerHTML = "Clear";
        btn.classList.add("btn", "btn-sm", "btn-outline-secondary", "mt-1");
        btn.addEventListener("click", function () {
          instance.clear(); // <-- clear flatpickr value
          instance.close();
          $('#tblTraining').DataTable().draw(); // refresh filter
        });
        instance.calendarContainer.appendChild(btn);
      }
    });
    
    // Initalize date table
    let trainingTable = $('#tblTraining').DataTable({
      pageLength: 10,
      columnDefs: [{
        orderable: false,
        targets: [6]
      }],
      order: [[0, 'asc']],
      language: {
        @if ($l == 'ar')
          sEmptyTable: "لا توجد جلسات تدريبية متاحة لهذا البرنامج",
          sLoadingRecords: "جارٍ التحميل...",
          sProcessing: "جارٍ التحميل...",
          sLengthMenu: "أظهر _MENU_ مدخلات",
          sZeroRecords: "لا توجد جلسات تدريبية متاحة لهذا البرنامج" ,
          sInfo: "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
          sInfoEmpty: "يعرض 0 إلى 0 من أصل 0 سجل",
          sInfoFiltered: "(منتقاة من مجموع _MAX_ مُدخل)",
          sInfoPostFix: "",
          sSearch: "ابحث:",
          sUrl: "",
          oPaginate: {
            sFirst: "الأول",
            sPrevious: "السابق",
            sNext: "التالي",
            sLast: "الأخير"
          },
          oAria: {
            sSortAscending: ": تفعيل لترتيب العمود تصاعدياً",
            sSortDescending: ": تفعيل لترتيب العمود تنازلياً"
          }
          @else
          sEmptyTable: "No Training Sessions Available For this Program",
          sLoadingRecords: "Loading...",
          sProcessing: "Processing...",
          sLengthMenu: "Show _MENU_ entries",
          sZeroRecords: "No Training Sessions Available For this Program",
        @endif
      },
      buttons: [
        { extend: 'csv', text: '<i class="ti ti-file-text me-1"></i>CSV', className: 'd-none', title: `tblTraining_Export_${new Date().toISOString().slice(0, 10)}`, exportOptions: { columns: [0, 1, 2, 3, 4, 5] } },
        { extend: 'excel', text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel', className: 'd-none', title: `tblTraining_Export_${new Date().toISOString().slice(0, 10)}`, exportOptions: { columns: [0, 1, 2, 3, 4, 5] } }
      ]
    });

    // ============ FILTER HANDLERS ============ //

    // Type filter (column 1)
    $('#type-filter').on('change', function () {
      let val = this.value ? $('#type-filter option:selected').text().trim() : '';
      trainingTable.column(1).search(val, true, false).draw();
    });

    // Cycle filter (column 2)
    $('#cycle-filter').on('change', function () {
      let val = this.value ? $('#cycle-filter option:selected').text().trim() : '';
      trainingTable.column(2).search(val, true, false).draw();
    });

    // Step filter (column 3)
    $('#step-filter').on('change', function () {
      let val = this.value ? $('#step-filter option:selected').text().trim() : '';
      trainingTable.column(3).search(val, true, false).draw();
    });

    // Date range filter (column 4)
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
      let startDate = $('#start-date-filter').val();
      let endDate   = $('#end-date-filter').val();

      // Column 4 text (date/time)
      let dateText = data[4] || "";

      // Extract first date (format: YYYY-MM-DD …)
      let match = dateText.match(/\d{4}-\d{2}-\d{2}/);
      if (!match) return true; // no valid date in cell → keep row

      let cellDateObj = new Date(match[0]);

      // Convert filter inputs
      let startOk = startDate ? new Date(startDate) <= cellDateObj : true;
      let endOk   = endDate ? new Date(endDate) >= cellDateObj : true;

      return startOk && endOk;
    });

    // Redraw when date inputs change
    $('#start-date-filter, #end-date-filter').on('change', function () {
      trainingTable.draw();
    });


    // Global export training function
    window.exportTraining = function (format) {
      if (!trainingTable) {
        releaseFailureAlert({ message: 'No data available to export' });
        return;
      }
      try {
        trainingTable.button(`.buttons-${format}`).trigger();
      } catch (error) {
        console.error('Export error:', error);
        releaseFailureAlert({ message: 'Failed to export data. Please try again.' });
      }
    }

    window.createQrCode = function (training) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/training/QrCode-offline",
        data: {
          "id": training,
        },
        success: function(response) {
          if (response) {
            alert(response.massege);
            location.reload();
          }
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }
  });
</script>