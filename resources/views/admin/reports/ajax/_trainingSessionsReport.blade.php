
<div class="col-12">
  @php
    $cards = [
      [ 'value' => $total_sessions_count ?? 0, 'label' => __('admin.Training sessions'), 'icon' => 'school', 'color' => 'primary', 'filter' => 'reset' ],
      [ 'value' => $sessions_period . ' ', 'label' => __('admin.Training_Sessions_Duration'), 'icon' => 'clock-hour-3', 'color' => 'secondary', 'filter' => 'reset' ],
      [ 'value' => $offline_sessions_count ?? 0, 'label' => __('admin.Offline Training Sessions Count'), 'icon' => 'building-community', 'color' => 'danger', 'filter' => 'Offline_Session','type' => 'offline' ],
      [ 'value' => $online_sessions_count ?? 0, 'label' => __('admin.Zoom Training Sessions Count'), 'icon' => 'screen-share', 'color' => 'info', 'filter' => 'Online_Session', 'type' => 'sessions' ],
      [ 'value' => $online_training_count ?? 0, 'label' => __('admin.Online Training Sessions Count'), 'icon' => 'certificate', 'color' => 'success', 'filter' => 'Online_Training', 'type' => 'online' ]
    ];
  @endphp

  <!-- KPI Cards -->
  <div class="row g-6 d-flex justify-content-center mb-8">
    @foreach($cards as $key => $card)
        @php
        $url = isset($card['type'])
        ? route('trainings.sessions_report', ['type' => $card['type']])
        : route('trainings.sessions_report');
      @endphp
      <div class="col-lg-2 col-sm-6">
        <div 
          class="card card-border-shadow-{{ $card['color'] }} h-100 cursor-pointer filter-card" 
          data-filter="{{ $card['filter'] ?? '' }}"
        >
          <div class="card-body py-2" style="display: grid; place-items: center;">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-{{ $card['color'] }}">
                  <i class="ti ti-{{ $card['icon'] }} ti-20px"></i>
                </span>
              </div>
              <h5 class="mb-0">{{ $card['value'] }}</h5>
            </div>
            <small class="text-center">{{ $card['label'] }}</small>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>

<!-- Sessions Table -->
<div class="card border">
  <!-- Heading & Actions -->
  <div class="card-header d-md-flex justify-content-between border-bottom pb-3 mb-6">
    <h5 class="card-title mb-3 mb-md-0">{{ __('admin.Training sessions') }}</h5>
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
          <select class="form-select form-select-sm" id="type-filter" placeholder="dddddd">
            <option value="" selected>{{ __('cycles.Select_Training_Type') }}</option>
            <option value="">{{ __('cycles.All') }}</option>
            @foreach(['Online_Training', 'Online_Session', 'Offline_Session'] as $type)
              <option value="{{ $type }}">{{ __('cycles.'.$type) }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="from-group mb-2 mb-md-0">
          <select class="form-select form-select-sm" id="cycle-filter">
            <option value="" selected>{{ __('cycles.All_Cycles') }}</option>
            @foreach($cycles as $cycle)
              <option value="{{ $cycle->id }}">{{ $cycle->title }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="from-group mb-2 mb-md-0">
          <select class="form-select form-select-sm" id="step-filter">
            <option value="" selected>{{ __('admin.All_Steps') }}</option>
            <option value="">{{ __('cycles.All') }}</option>
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
          @foreach ($sessions as $session)
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
                {{-- @else
                  <div class="text-nowrap">{{ __('cycles.trainer name') }}:</div>
                  <div class="text-nowrap">{{ $session->course->user->{'first_name_' . $l} }} {{ $session->course->user->{'last_name_' . $l} }}</div> --}}
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
      responsive: true,
      pageLength: 10,
      columnDefs: [{
        orderable: false,
        targets: [6]
      }],
      order: [[0, 'asc']],
      language: {
        @if ($l == 'ar')
          sEmptyTable: "ليست هناك بيانات متاحة في الجدول",
          sLoadingRecords: "جارٍ التحميل...",
          sProcessing: "جارٍ التحميل...",
          sLengthMenu: "أظهر _MENU_ مدخلات",
          sZeroRecords: "لم يعثر على أية سجلات",
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
      if (settings.nTable.id !== 'tblTraining') return true;
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