@extends('layouts.admin')

@section('title', __('admin.Activities'))

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



    {{-- Activities Table --}}
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="mb-0"><i class="ti ti-list-details me-1"></i> @lang('admin.Users_Activities')</h6>
          <div class="btn-group">
            <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="projectsExportBtn">
              <i class="bi bi-download me-1"></i> {{ $l == 'ar' ? 'تصدير' : 'Export' }}
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="javascript:void(0);" onclick="exportTable('activities-datatable', 'csv')"><i class="bi bi-filetype-csv me-2"></i>{{ $l == 'ar' ? 'تصدير CSV' : 'Export CSV' }}</a></li>
              <li><a class="dropdown-item" href="javascript:void(0);" onclick="exportTable('activities-datatable', 'excel')"><i class="bi bi-file-earmark-excel me-2"></i>{{ $l == 'ar' ? 'تصدير Excel' : 'Export Excel' }}</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="activities-datatable" class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>@lang('admin.User')</th>
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
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-3">
                      @if($activity->user->img_url ?? false)
                        <img src="{{ asset("191014/{$activity->user->img_url}") }}" alt="{{ $activity->user->first_name_en }}" class="rounded-circle border shadow-xs" onerror="this.onerror=null; this.src='/icons/account.svg';">
                      @else
                        <div class="avatar avatar-sm avatar-initial rounded-circle bg-label-primary">
                          {{ strtoupper(substr($activity->user->first_name_en ?? 'U', 0, 1)) }}{{ strtoupper(substr($activity->user->last_name_en ?? 'U', 0, 1)) }}
                        </div>
                      @endif
                    </div>
                    <div class="d-flex flex-column">
                      <h6 class="mb-0 text-sm">{{ $activity->user->first_name_en . ' ' . $activity->user->last_name_en }}</h6>
                      <small class="text-muted">{{ $activity->user->email }}</small>
                    </div>
                  </div>
                </td>
                <td class="text-nowrap">{{ $activity->browser }}</td>
                <td class="text-nowrap">{{ $activity->device }}</td>
                <td class="text-nowrap">{{ $activity->platform }}</td>
                <td class="text-nowrap">{{ $activity->ip_address }}</td>
                <td class="text-nowrap">{{ $activity->location }}</td>
                <td class="text-nowrap">{{ $activity->activity_time->format('d M Y, H:i') }}</td>
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
<script>
  // Initialize Date Picker
  flatpickr(".datepicker", {
    dateFormat: "Y-m-d",
    allowInput: true,
    onChange: function(selectedDates, dateStr, instance) {
      // Trigger table redraw when date changes
      if (instance.element.id === 'loginTimeFilter') {
        $('#activities-datatable').DataTable().draw();
      }
    }
  });

  // Define exportTable function globally before document ready
  window.exportTable = function(tableClass, format) {
    const table = $('#' + tableClass).DataTable();
    if (format === 'csv') {
      table.button('.buttons-csv').trigger();
    } else if (format === 'excel') {
      table.button('.buttons-excel').trigger();
    }
  };

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
      paging: true,
      searching: true,
      ordering: true,
      order: [[6, 'desc']], // Changed to column 6 (Login_Time) since we removed email column
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      dom: '<"row mb-2"<"col-sm-6"l><"col-sm-6"f>><"row mb-3"<"col-12"<"filter-row">>>rtip<"d-none"B>', // Add filter-row container
      buttons: [
        {
          extend: 'csv',
          className: 'buttons-csv d-none'
        },
        {
          extend: 'excel',
          className: 'buttons-excel d-none'
        }
      ]
    });

    const fieldsTranslation = {
      en: {
        'allPlatforms': 'All Platforms',
        'allBrowsers': 'All Browsers',
        'allLocations': 'All Locations',
        'selectDate': 'Select Date'
      },
      ar: {
        'allPlatforms': 'جميع المنصات',
        'allBrowsers': 'جميع المتصفحات',
        'allLocations': 'جميع المواقع',
        'selectDate': 'اختر التاريخ'
      }
    }

    // Add filter controls after table initialization
    @if ($activities->isNotEmpty())
    const filterHtml = `
      <div class="row g-3 align-items-end mb-6">
        <div class="col-md-3">
          <select id="platformFilter" class="form-select">
            <option value="">${fieldsTranslation[lang].allPlatforms}</option>
            @foreach($activities->pluck('platform')->unique()->filter() as $platform)
              <option value="{{ $platform }}">{{ $platform }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select id="browserFilter" class="form-select">
            <option value="">${fieldsTranslation[lang].allBrowsers}</option>
            @foreach($activities->pluck('browser')->unique()->filter() as $browser)
              <option value="{{ $browser }}">{{ $browser }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select id="locationFilter" class="form-select">
            <option value="">${fieldsTranslation[lang].allLocations}</option>
            @foreach($activities->pluck('location')->unique()->filter() as $location)
              <option value="{{ $location }}">{{ $location }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <input type="text" id="loginTimeFilter" class="form-control datepicker" placeholder="${fieldsTranslation[lang].selectDate}">
        </div>
      </div>
    `;
    $('.filter-row').html(filterHtml);
    
    // Re-initialize date picker for the dynamically added filter
    flatpickr("#loginTimeFilter", {
      dateFormat: "Y-m-d",
      allowInput: true,
      onChange: function(selectedDates, dateStr, instance) {
        table.draw();
      }
    });
    @endif

    // Custom filter functions
    $.fn.dataTable.ext.search.push(
      function(settings, data, dataIndex) {
        // Get filter values
        const platformFilter = $('#platformFilter').val();
        const browserFilter = $('#browserFilter').val();
        const locationFilter = $('#locationFilter').val();
        const loginTimeFilter = $('#loginTimeFilter').val();

        // Get data from the row
        const platform = data[3] || ''; // Platform column (index 3)
        const browser = data[1] || '';   // Browser column (index 1)
        const location = data[5] || '';  // Location column (index 5)
        const loginTime = data[6] || ''; // Login Time column (index 6)

        // Apply filters
        if (platformFilter && platform.toLowerCase().indexOf(platformFilter.toLowerCase()) === -1) {
          return false;
        }

        if (browserFilter && browser.toLowerCase().indexOf(browserFilter.toLowerCase()) === -1) {
          return false;
        }

        if (locationFilter && location.toLowerCase().indexOf(locationFilter.toLowerCase()) === -1) {
          return false;
        }

        if (loginTimeFilter) {
          // Parse the date from the login time (format: "01 Jan 2025, 12:30")
          const loginDate = new Date(loginTime.split(',')[0]);
          const filterDate = new Date(loginTimeFilter);
          
          // Compare dates (ignore time)
          if (loginDate.toDateString() !== filterDate.toDateString()) {
            return false;
          }
        }

        return true;
      }
    );

    // Bind change events to filters
    $('#platformFilter, #browserFilter, #locationFilter, #loginTimeFilter').on('change keyup', function() {
      table.draw();
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