@extends('layouts.admin')

@section('title', __('admin.Activities'))
@section('vendorstyle')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
@stop
@section('styles')
<style>
  .chart-container {
    position: relative;
    height: 400px;
    width: 100%;
  }

  /* Style for the colored top border on stat cards */
  .stat-card-border-top {
    border-top: 4px solid;
  }
  /* Style for DataTables buttons */
    .dt-buttons .btn-success {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
        color: #fff !important;
    }
    .dt-buttons .btn-success:hover {
        background-color: #218838 !important;
        border-color: #1e7e34 !important;
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    {{-- Alerts --}}
    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="@lang('admin.Close')"></button>
      </div>
    @endif

    @if (session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="@lang('admin.Close')"></button>
      </div>
    @endif

    <h4 class="fw-bold mb-4">@lang('admin.Activities_Dashboard')</h4>

    {{-- Enhanced Statistics Cards --}}
    <div class="row g-3 mb-4">
      @foreach ([
        [
            'label' => 'Total_Logins_Today', 
            'value' => $totalLoginsToday, 
            'color' => '#7367f0', // Blue
            'icon' => 'ti-calendar-event'
        ],
        [
            'label' => 'Total_Logins_Week', 
            'value' => $totalLoginsWeek, 
            'color' => '#10b981', // Green
            'icon' => 'ti-calendar-time'
        ],
        [
            'label' => 'Total_Logins_Month', 
            'value' => $totalLoginsMonth, 
            'color' => '#f59e0b', // Amber
            'icon' => 'ti-calendar'
        ],
      ] as $stat)
      <div class="col-md-4">
        <div class="card shadow-sm stat-card-border-top" style="border-color: {{ $stat['color'] }};">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="fw-bold mb-0">@lang('admin.' . $stat['label'])</h5>
                <div class="display-4 fw-bold" style="color: {{ $stat['color'] }};">{{ $stat['value'] }}</div>
              </div>
              <div class="avatar avatar-lg d-flex align-items-center justify-content-center rounded" style="background-color: {{ $stat['color'] }}1a;">
                <i class="ti {{ $stat['icon'] }} fs-2" style="color: {{ $stat['color'] }};"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    {{-- Charts for Device, Browser, Country Stats & Peak Hours --}}
    <div class="row g-4 mb-4">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h6 class="mb-3 text-primary"><i class="ti ti-device-desktop me-1"></i> @lang('admin.Top_Devices')</h6>
            <div class="chart-container">
              <canvas id="topDevicesChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h6 class="mb-3 text-primary"><i class="ti ti-world me-1"></i> @lang('admin.Top_Browsers')</h6>
            <div class="chart-container">
              <canvas id="topBrowsersChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h6 class="mb-3 text-primary"><i class="ti ti-flag me-1"></i> @lang('admin.Logins_by_Country')</h6>
            <div class="chart-container">
              <canvas id="loginsByCountryChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h6 class="mb-3 text-primary"><i class="ti ti-clock me-1"></i> @lang('admin.Peak_Login_Hours')</h6>
            <div class="chart-container">
              <canvas id="peakHoursChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Suspicious Logins --}}
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card h-100">
              <div class="card-body">
                <h6 class="mb-3 text-danger"><i class="ti ti-alert-triangle me-1"></i> @lang('admin.Suspicious_Logins')</h6>
                @if ($suspiciousLogins->isNotEmpty())
                <ul class="list-group list-group-flush">
                  @foreach ($suspiciousLogins as $login)
                    <li class="list-group-item">
                      <strong>{{ $login->user->first_name_en ?? 'Unknown' }} {{ $login->user->last_name_en ?? 'Unknown' }}</strong><br>
                      <small>@lang('admin.Logins'): {{ $login->login_count }}, @lang('admin.Locations'): {{ $login->locations ?? 'N/A' }}</small>
                    </li>
                  @endforeach
                </ul>
                @else
                <p class="text-muted">@lang('admin.No_suspicious_logins')</p>
                @endif
              </div>
            </div>
        </div>
    </div>

    {{-- Filter Form --}}
    @if ($activities->isNotEmpty())
    <div class="card mb-4">
      <div class="card-body">
        <h6 class="mb-3 text-primary"><i class="ti ti-filter me-1"></i> @lang('admin.Filter_Activities')</h6>
        <form method="GET">
          <div class="row g-3 align-items-end">
            <div class="col-md-2">
              <label class="form-label">@lang('admin.user')</label>
              <input type="text" name="user" class="form-control" placeholder="@lang('admin.user')" value="{{ request('user') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label">@lang('admin.Date_From')</label>
              <input type="text" name="date_from" class="form-control datepicker" placeholder="@lang('admin.Date_From')" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label">@lang('admin.Date_To')</label>
              <input type="text" name="date_to" class="form-control datepicker" placeholder="@lang('admin.Date_To')" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label">@lang('admin.device')</label>
              <input type="text" name="device" class="form-control" placeholder="@lang('admin.device')" value="{{ request('device') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label">@lang('admin.location')</label>
              <input type="text" name="location" class="form-control" placeholder="@lang('admin.location')" value="{{ request('location') }}">
            </div>
            <div class="col-md-2 d-grid">
              <button type="submit" class="btn btn-primary">@lang('admin.Filter')</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    @endif

    {{-- Activities Table --}}
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="mb-0 text-primary"><i class="ti ti-list-details me-1"></i> @lang('admin.Users_Activities')</h6>
        </div>
        <div class="table-responsive">
          <table id="activities-datatable" class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>@lang('admin.user')</th>
                <th>@lang('admin.browser')</th>
                <th>@lang('admin.device')</th>
                <th>@lang('admin.platform')</th>
                <th>@lang('admin.IP_Address')</th>
                <th>@lang('admin.location')</th>
                <th>@lang('admin.Login_Time')</th>
                <th>@lang('admin.Actions')</th>
              </tr>
            </thead>
            <tbody>
              @foreach($activities as $activity)
              <tr>
                <td>{{ $activity->user->first_name_en . ' ' . $activity->user->last_name_en }}</td>
                <td>{{ $activity->browser }}</td>
                <td>{{ $activity->device }}</td>
                <td>{{ $activity->platform }}</td>
                <td>{{ $activity->ip_address }}</td>
                <td>{{ $activity->location }}</td>
                <td>{{ $activity->activity_time->format('d M Y, H:i') }}</td>
                <td>
                  <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" class="d-inline delete-form">
                    @csrf
                    @method('DELETE')
                    <button
                      type="submit"
                      class="btn btn-sm btn-icon btn-label-danger"
                      title="@lang('admin.Delete')"
                    >
                      <i class="ti ti-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection


@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script>
  // Initialize Date Picker
  flatpickr(".datepicker", {
    dateFormat: "Y-m-d",
    allowInput: true,
  });

  $(document).ready(function() {
    const lang = "{{ $l }}";

    const datatableLanguage = lang === 'ar' ? {
        "sEmptyTable": "@lang('admin.No_data_available_in_table')",
        "sLoadingRecords": "@lang('admin.Loading')...",
        "sProcessing": "@lang('admin.Loading')...",
        "sLengthMenu": "@lang('admin.Show') _MENU_ @lang('admin.Entries')",
        "sZeroRecords": "@lang('admin.No_records_found')",
        "sInfo": "@lang('admin.showing') _START_ @lang('admin.to') _END_ @lang('admin.of') _TOTAL_ @lang('admin.Entries')",
        "sInfoEmpty": "@lang('admin.showing') 0 @lang('admin.to') 0 @lang('admin.of') 0 @lang('admin.Entries')",
        "sInfoFiltered": "(@lang('admin.Filtered_from') _MAX_ @lang('admin.Total_Entries'))",
        "sInfoPostFix": "",
        "sSearch": "@lang('admin.Search'):",
        "sUrl": "",
        "oPaginate": {
            "sFirst": "@lang('admin.First')",
            "sPrevious": "@lang('admin.Previous')",
            "sNext": "@lang('admin.Next')",
            "sLast": "@lang('admin.Last')"
        },
        "oAria": {
            "sSortAscending": ": @lang('admin.Activate_to_sort_ascending')",
            "sSortDescending": ": @lang('admin.Activate_to_sort_descending')"
        }
    } : {};

   const table = $('#activities-datatable').DataTable({
            language: datatableLanguage,
            order: [[4, 'desc']],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            searching: true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="ti ti-file-export me-1"></i>@lang("admin.Export_to_Excel")',
                    title: 'Activities_' + new Date().toISOString().slice(0, 10),
                    className: 'btn btn-success',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6]
                    }
                }
            ]
        });
    // Chart Data
    const topDevicesData = @json($topDevices);
    const topBrowsersData = @json($topBrowsers);
    const loginsByCountryData = @json($loginsByCountry);
    const peakHoursData = @json($peakHours);

    // Top Devices Chart
    if (topDevicesData.length > 0) {
        new Chart(document.getElementById('topDevicesChart'), {
            type: 'doughnut',
            data: {
                labels: topDevicesData.map(d => d.device),
                datasets: [{
                    data: topDevicesData.map(d => d.count),
                    backgroundColor: ['#7c3aed', '#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    }

    // Top Browsers Chart
    if (topBrowsersData.length > 0) {
        new Chart(document.getElementById('topBrowsersChart'), {
            type: 'pie',
            data: {
                labels: topBrowsersData.map(b => b.browser),
                datasets: [{
                    data: topBrowsersData.map(b => b.count),
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#7c3aed'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    }

    // Logins by Country Chart
    if (loginsByCountryData.length > 0) {
        new Chart(document.getElementById('loginsByCountryChart'), {
            type: 'bar',
            data: {
                labels: loginsByCountryData.map(c => c.location),
                datasets: [{
                    label: 'Logins',
                    data: loginsByCountryData.map(c => c.count),
                    backgroundColor: '#10b981',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Peak Login Hours Chart
    if (peakHoursData.length > 0) {
        new Chart(document.getElementById('peakHoursChart'), {
            type: 'line',
            data: {
                labels: peakHoursData.map(h => `${String(h.hour).padStart(2, '0')}:00`),
                datasets: [{
                    label: 'Logins',
                    data: peakHoursData.map(h => h.count),
                    borderColor: '#3b82f6',
                    tension: 0.4,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
  });

  // Delete confirmation
  $(document).on("submit", ".delete-form", function (e) {
    e.preventDefault();

    const form = this;

    Swal.fire({
      title: "{{ __('admin.SureDelete') }}",
      text: "{{ __('admin.Recover') }}",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "{{ __('admin.DYes') }}",
      cancelButtonText: "{{ __('admin.No') }}",
      reverseButtons: true,
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        cancelButton: 'btn btn-label-secondary',
      },
      buttonsStyling: false,
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit(); // Submit natively without re-triggering the event
      }
    });
  });
</script>
@stop