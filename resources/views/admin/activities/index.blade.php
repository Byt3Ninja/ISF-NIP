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
<div class="">
  <div class="">

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
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between bg-white">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti ti-alert-triangle text-danger"></i>
                        <h5 class="mb-0">@lang('admin.Suspicious_Logins')</h5>
                    </div>
                    <div class="d-flex gap-2">
                        <input type="search" class="form-control form-control-sm" id="searchSuspiciousUsers" placeholder="@lang('admin.Search')" style="max-width: 200px;" />
                        <div class="btn-group">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti ti-download me-1"></i> @lang('admin.Export')
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:void(0);" onclick="exportSuspiciousLogins('csv')"><i class="ti ti-file-type-csv me-2"></i>CSV</a></li>
                                {{-- <li><a class="dropdown-item" href="javascript:void(0);" onclick="exportSuspiciousLogins('excel')"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li> --}}
                            </ul>
                        </div>
                    </div>
                </div>

                @if ($suspiciousLogins->isNotEmpty())
                <div class="list-group list-group-flush" id="suspiciousUsersList">
                    @foreach ($suspiciousLogins as $login)
                    <div class="list-group-item suspicious-user-card" data-user="{{ strtolower($login->user->first_name_en . ' ' . $login->user->last_name_en . ' ' . $login->user->email) }}">
                        <div class="p-3 d-flex flex-wrap align-items-center justify-content-between">
                            <a
                              href="/dashboard/show/UserProfile/{{ $login->user->id }}"
                              class="d-flex align-items-center gap-3"
                            >
                                <div class="avatar avatar-lg">
                                    @if($login->user->img_url ?? false)
                                        <img src="{{ asset("191014/{$login->user->img_url}") }}" alt="avatar" class="rounded-circle border shadow-xs" onerror="this.onerror=null; this.src='/icons/account.svg';">
                                    @else
                                        <div class="avatar avatar-lg avatar-initial rounded-circle bg-label-primary">
                                            {{ strtoupper(substr($login->user->first_name_en ?? 'U', 0, 1)) }}{{ strtoupper(substr($login->user->last_name_en ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $login->user->first_name_en }} {{ $login->user->last_name_en }}</div>
                                    <div class="text-muted small">{{ $login->user->email }}</div>
                                </div>
                            </a>

                            <div class="d-flex flex-wrap gap-4">
                                <div class="text-muted small"><strong class="text-dark">{{ $login->login_count }}</strong> @lang('admin.Logins')</div>
                                <div class="text-muted small"><strong class="text-dark">{{ $login->location_count }}</strong> @lang('admin.Locations')</div>
                                <div class="text-muted small">@lang('admin.Last') <strong class="text-dark">{{ \Carbon\Carbon::parse($login->last_login)->format('d M Y, H:i') }}</strong></div>
                                <span class="badge 
                                    @if($login->risk_level == 'high') bg-label-danger 
                                    @elseif($login->risk_level == 'medium') bg-label-warning 
                                    @else bg-label-success 
                                    @endif
                                    align-self-center fw-semibold">
                                    @if($login->risk_level == 'high') @lang('admin.High_Risk')
                                    @elseif($login->risk_level == 'medium') @lang('admin.Medium_Risk')
                                    @else @lang('admin.Low_Risk')
                                    @endif
                                </span>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-icon btn-outline-danger btn-sm" onclick="forceLogout({{ $login->user_id }})" title="@lang('admin.Force_Logout')" data-bs-toggle="tooltip" data-bs-placement="top">
                                    <i class="ti ti-power"></i>
                                </button>
                                <button class="btn btn-icon btn-outline-warning btn-sm" onclick="resetPassword({{ $login->user_id }})" title="@lang('admin.Reset_Password')" data-bs-toggle="tooltip" data-bs-placement="top">
                                    <i class="ti ti-shield-lock"></i>
                                </button>
                                <button class="btn btn-icon btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#u{{ $login->user_id }}Details" title="@lang('admin.View_Details')" data-bs-placement="top">
                                    <i class="ti ti-list"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Country summary -->
                        <div class="px-3 pb-3">
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($login->location_counts as $location => $count)
                                    @if($location && $location !== 'Unknown' && $location !== '')
                                        <small class="badge bg-label-light text-dark py-1">
                                            <i class="ti ti-xs ti-map-pin me-1"></i>{{ $location }} ({{ $count }})
                                        </small>
                                    @endif
                                @endforeach
                                @if(isset($login->location_counts['Unknown']) || isset($login->location_counts['']) || in_array('Unknown', $login->unique_locations))
                                    <small class="badge bg-label-warning text-dark py-1">
                                        <i class="ti ti-xs ti-help me-1"></i>@lang('admin.Unknown') 
                                        ({{ $login->location_counts['Unknown'] ?? $login->location_counts[''] ?? 1 }})
                                    </small>
                                @endif
                           </div>
                        </div>

                        <!-- Details Table -->
                        <div class="collapse" id="u{{ $login->user_id }}Details">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>@lang('admin.Date_Time')</th>
                                            <th>@lang('admin.location')</th>
                                            <th>@lang('admin.IP_Address')</th>
                                            <th>@lang('admin.browser')</th>
                                            <th>@lang('admin.device')</th>
                                            <th>@lang('admin.Actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($login->recent_activities as $activity)
                                        <tr>
                                            <td class="text-nowrap">{{ $activity->activity_time->format('d M Y, H:i') }}</td>
                                            <td>
                                                @if($activity->location && $activity->location !== 'Unknown')
                                                    <i class="ti ti-map-pin me-1 text-primary"></i>{{ $activity->location }}
                                                @else
                                                    <i class="ti ti-help text-warning me-1"></i>@lang('admin.Unknown')
                                                @endif
                                            </td>
                                            <td class="text-nowrap">{{ $activity->ip_address ?: '—' }}</td>
                                            <td class="text-nowrap">{{ $activity->browser }}</td>
                                            <td class="text-nowrap">{{ $activity->device }}</td>
                                            <td>
                                                @if($activity->ip_address)
                                                <button class="btn btn-icon btn-outline-danger btn-sm" onclick="blockIp('{{ $activity->ip_address }}')" title="@lang('admin.Block_IP')" data-bs-toggle="tooltip" data-bs-placement="top">
                                                    <i class="ti ti-ban"></i>
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($login->has_more_activities ?? false)
                            <div class="p-3 border-top text-center">
                                <button class="btn btn-light btn-sm" onclick="loadMoreActivities({{ $login->user_id }})">
                                    <i class="ti ti-refresh me-1"></i>@lang('admin.Load_more')
                                </button>
                                <small class="text-muted d-block mt-1">
                                    @lang('admin.showing') {{ $login->recent_activities->count() }} @lang('admin.of') {{ $login->total_activities_count ?? 0 }} @lang('admin.Activities')
                                </small>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-xl mx-auto mb-3">
                        <div class="avatar avatar-xl avatar-initial rounded-circle bg-label-success">
                            <i class="ti ti-shield-check" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <h6 class="mb-1">@lang('admin.No_suspicious_logins')</h6>
                    <p class="text-muted mb-0">@lang('admin.All_user_activities_appear_normal')</p>
                </div>
                @endif
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
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

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

  // Suspicious logins search functionality
  function filterSuspiciousUsers(query) {
    const val = query.trim().toLowerCase();
    document.querySelectorAll('#suspiciousUsersList .suspicious-user-card').forEach(card => {
      const hit = card.getAttribute('data-user').toLowerCase().includes(val);
      card.style.display = hit ? '' : 'none';
    });
  }

  // Bind search input
  $('#searchSuspiciousUsers').on('input', function() {
    filterSuspiciousUsers(this.value);
  });

  // Security action functions
  window.forceLogout = function(userId) {
    Swal.fire({
      title: '@lang("admin.Force_Logout_Confirmation")',
      text: '@lang("admin.This_will_immediately_log_out_the_user")',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '@lang("admin.Yes_Force_Logout")',
      cancelButtonText: '@lang("admin.Cancel")',
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        cancelButton: 'btn btn-label-secondary',
      },
      buttonsStyling: false,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('{{ route("activities.force-logout") }}', {
          user_id: userId,
          _token: '{{ csrf_token() }}'
        }).done(function(response) {
          if (response.success) {
            Swal.fire({
              title: 'Success!',
              text: response.message,
              icon: 'success',
              customClass: {
                confirmButton: 'btn btn-success',
              },
              buttonsStyling: false,
            });
          }
        }).fail(function() {
          Swal.fire({
            title: 'Error!',
            text: 'Failed to force logout user',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-danger',
            },
            buttonsStyling: false,
          });
        });
      }
    });
  };

  window.resetPassword = function(userId) {
    Swal.fire({
      title: '@lang("admin.Reset_Password_Confirmation")',
      text: '@lang("admin.This_will_generate_a_new_password")',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '@lang("admin.Yes_Reset_Password")',
      cancelButtonText: '@lang("admin.Cancel")',
      customClass: {
        confirmButton: 'btn btn-warning me-2',
        cancelButton: 'btn btn-label-secondary',
      },
      buttonsStyling: false,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('{{ route("activities.reset-password") }}', {
          user_id: userId,
          _token: '{{ csrf_token() }}'
        }).done(function(response) {
          console.log('response', response)
          if (response.success) {
            Swal.fire({
              title: 'Password Reset!',
              html: `<p>${response.message}</p><p class="mt-3"><strong>@lang('admin.New_Password')</strong>:</p><div class="bg-light p-2 rounded"><code class="text-primary">${response.new_password}</code></div><p class="text-muted small mt-2">@lang('admin.Reset_Password_Caution')</p>`,
              icon: 'success',
              customClass: {
                confirmButton: 'btn btn-success',
              },
              buttonsStyling: false,
            });
          }
        }).fail(function() {
          Swal.fire({
            title: 'Error!',
            text: 'Failed to reset password',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-danger',
            },
            buttonsStyling: false,
          });
        });
      }
    });
  };

  window.blockIp = function(ipAddress) {
    Swal.fire({
      title: '@lang("admin.Block_IP_Confirmation")',
      text: `@lang("admin.This_will_block_IP_address"): ${ipAddress}`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '@lang("admin.Yes_Block_IP")',
      cancelButtonText: '@lang("admin.Cancel")',
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        cancelButton: 'btn btn-label-secondary',
      },
      buttonsStyling: false,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('{{ route("activities.block-ip") }}', {
          ip_address: ipAddress,
          _token: '{{ csrf_token() }}'
        }).done(function(response) {
          if (response.success) {
            Swal.fire({
              title: 'Blocked!',
              text: response.message,
              icon: 'success',
              customClass: {
                confirmButton: 'btn btn-success',
              },
              buttonsStyling: false,
            });
          }
        }).fail(function() {
          Swal.fire({
            title: 'Error!',
            text: 'Failed to block IP address',
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-danger',
            },
            buttonsStyling: false,
          });
        });
      }
    });
  };

  // Load more activities function
  window.loadMoreActivities = function(userId) {
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="ti ti-loader-2 me-1 spinner-border spinner-border-sm"></i>@lang("admin.Loading")...';
    button.disabled = true;
    
    $.get('{{ route("activities.load-more") }}', {
      user_id: userId,
      offset: document.querySelectorAll(`#u${userId}Details tbody tr`).length
    }).done(function(response) {
      if (response.success && response.activities.length > 0) {
        const tbody = document.querySelector(`#u${userId}Details tbody`);
        response.activities.forEach(activity => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="text-nowrap">${activity.formatted_date}</td>
            <td>
              ${activity.location && activity.location !== 'Unknown' 
                ? `<i class="ti ti-map-pin me-1 text-primary"></i>${activity.location}`
                : `<i class="ti ti-help text-warning me-1"></i>@lang('admin.Unknown')`
              }
            </td>
            <td class="text-nowrap">${activity.ip_address || '—'}</td>
            <td class="text-nowrap">${activity.browser}</td>
            <td class="text-nowrap">${activity.device}</td>
            <td>
              ${activity.ip_address 
                ? `<button class="btn btn-icon btn-outline-danger btn-sm" onclick="blockIp('${activity.ip_address}')" title="@lang('admin.Block_IP')" data-bs-toggle="tooltip" data-bs-placement="top">
                     <i class="ti ti-ban"></i>
                   </button>`
                : ''
              }
            </td>
          `;
          tbody.appendChild(row);
        });
        
        // Update showing count
        const countElement = button.parentElement.querySelector('small');
        if (countElement) {
          const currentCount = document.querySelectorAll(`#u${userId}Details tbody tr`).length;
          countElement.innerHTML = `@lang('admin.showing') ${currentCount} @lang('admin.of') ${response.total_count} @lang('admin.Activities')`;
        }
        
        // Hide button if no more data
        if (!response.has_more) {
          button.parentElement.style.display = 'none';
        }
        
        // Re-initialize tooltips for new elements
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl);
        });
      } else {
        button.parentElement.style.display = 'none';
      }
    }).fail(function() {
      Swal.fire({
        title: 'Error!',
        text: 'Failed to load more activities',
        icon: 'error',
        customClass: {
          confirmButton: 'btn btn-danger',
        },
        buttonsStyling: false,
      });
    }).always(function() {
      // Restore button state
      button.innerHTML = originalContent;
      button.disabled = false;
    });
  };

  // Export function for suspicious logins
  window.exportSuspiciousLogins = function(format) {
    const suspiciousData = @json($suspiciousLogins->values()->toArray());
    
    if (!suspiciousData || suspiciousData.length === 0) {
      Swal.fire({
        title: '@lang("admin.No_data_to_export")',
        text: '@lang("admin.No_suspicious_logins_found")',
        icon: 'info',
        customClass: {
          confirmButton: 'btn btn-primary',
        },
        buttonsStyling: false,
      });
      return;
    }

    // Prepare data for export
    const exportData = [];
    
    // Add header row
    const headers = [
      '@lang("admin.User_Name")',
      '@lang("admin.Email")', 
      '@lang("admin.Login_Count")',
      '@lang("admin.Location_Count")',
      '@lang("admin.Risk_Level")',
      '@lang("admin.Last_Login")',
      '@lang("admin.Locations")',
      '@lang("admin.IP_Addresses")',
      '@lang("admin.Browsers")',
      '@lang("admin.Devices")'
    ];
    exportData.push(headers);

    // Add data rows
    suspiciousData.forEach(login => {
      const row = [
        login.user ? `${login.user.first_name_en || ''} ${login.user.last_name_en || ''}`.trim() : 'Unknown',
        login.user ? login.user.email || '' : '',
        login.login_count || 0,
        login.location_count || 0,
        login.risk_level || 'low',
        login.last_login ? new Date(login.last_login).toLocaleString() : '',
        login.locations || '',
        login.ip_addresses || '',
        login.browsers || '',
        login.devices || ''
      ];
      exportData.push(row);
    });

    if (format === 'csv') {
      exportToCSV(exportData, 'suspicious_logins');
    } else if (format === 'excel') {
      exportToExcel(exportData, 'suspicious_logins');
    }
  };

  // CSV Export Function
  function exportToCSV(data, filename) {
    const csvContent = data.map(row => 
      row.map(field => {
        // Escape quotes and wrap in quotes if contains comma, quote, or newline
        const fieldStr = String(field || '');
        if (fieldStr.includes(',') || fieldStr.includes('"') || fieldStr.includes('\n')) {
          return '"' + fieldStr.replace(/"/g, '""') + '"';
        }
        return fieldStr;
      }).join(',')
    ).join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
      const url = URL.createObjectURL(blob);
      link.setAttribute('href', url);
      link.setAttribute('download', `${filename}_${new Date().toISOString().split('T')[0]}.csv`);
      link.style.visibility = 'hidden';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }

  // Excel Export Function (using HTML table method)
  function exportToExcel(data, filename) {
    let excelContent = '<table>';
    
    data.forEach((row, index) => {
      excelContent += '<tr>';
      row.forEach(cell => {
        if (index === 0) {
          // Header row
          excelContent += `<th style="background-color: #f8f9fa; font-weight: bold; border: 1px solid #dee2e6; padding: 8px;">${cell || ''}</th>`;
        } else {
          // Data row
          excelContent += `<td style="border: 1px solid #dee2e6; padding: 8px;">${cell || ''}</td>`;
        }
      });
      excelContent += '</tr>';
    });
    
    excelContent += '</table>';

    const blob = new Blob([excelContent], { type: 'application/vnd.ms-excel' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
      const url = URL.createObjectURL(blob);
      link.setAttribute('href', url);
      link.setAttribute('download', `${filename}_${new Date().toISOString().split('T')[0]}.xls`);
      link.style.visibility = 'hidden';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }
</script>
@stop