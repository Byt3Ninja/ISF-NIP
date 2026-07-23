@extends('layouts.admin')

@section('title', __('admin.Activities'))

@section('style')
<style>
  .dataTables_paginate, .dataTables_info { padding: 0 2rem; }
</style>
@endsection

@php
  $stats = [
    ['key' => 'Total_Logins', 'value' => $totalLogins],
    ['key' => 'Total_Logins_Today', 'value' => $totalLoginsToday],
    ['key' => 'Total_Logins_Week', 'value' => $totalLoginsWeek],
    ['key' => 'Total_Logins_Month', 'value' => $totalLoginsMonth],
  ];

  $statColors = ['primary', 'success', 'info', 'warning'];
  $statIcons = ['calendar-check', 'calendar-time', 'calendar-event', 'calendar'];

  $charts = [
    ['id' => 'topDevicesChart', 'title' => 'Top_Devices'],
    ['id' => 'topBrowsersChart', 'title' => 'Top_Browsers'],
    ['id' => 'loginsByCountryChart', 'title' => 'Logins_by_Country'],
    ['id' => 'peakHoursChart', 'title' => 'Peak_Login_Hours'],
  ];
  $chartIcons = ['device-desktop', 'world', 'flag', 'clock'];
@endphp

@section('content')
<div class="">
  <div class="">

    {{-- Alerts --}}
    @if (session('success'))
      <x-alert type="success" message="{{ session('success') }}" />
    @endif

    @if (session('error'))
      <x-alert type="danger" message="{{ session('error') }}" />
    @endif

    {{-- Header Title --}}
    <div class="d-flex align-items-center gap-2 mb-4">
      <i class="ti ti-dashboard ti-28px"></i>
      <h4 class="mb-0">@lang('admin.Activities_Dashboard')</h4>
    </div>

    {{-- Enhanced Statistics Cards --}}
    <div class="row mb-4">
      @foreach ($stats as $key => $stat)
        <div class="col-xl-3">
          <div class="card card-border-shadow-{{ $statColors[$key] }}">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <h6 class="mb-0">@lang('admin.' . $stat['key'])</h6>
                  <h4 class="mb-0">{{ $stat['value'] ?? 0 }}</h4>
                </div>
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-{{ $statColors[$key] }}">
                    <i class="ti ti-{{ $statIcons[$key] }} ti-lg"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Charts for Device, Browser, Country Stats & Peak Hours --}}
    <div class="row g-4 mb-4">
      @foreach ($charts as $key => $chart)
        <div class="col-md-6">
          <div class="card">
            <div class="card-body">
              <h6 class="card-title mb-3"><i class="ti ti-{{ $chartIcons[$key] }} me-1"></i> @lang('admin.' . $chart['title'])</h6>
              <div class="w-100 h-px-200 position-relative">
                <canvas id="{{ $chart['id'] }}"></canvas>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    @php
      $tabs = [
        ['title' => __('admin.Suspicious_Logins'), 'id' => 'Suspicious_Logins', 'icon' => 'alert-triangle'],
        ['title' => __('admin.Users_Activities'), 'id' => 'Users_Activities', 'icon' => 'list-details'],
      ];
    @endphp

    {{-- Navigation Pills --}}
    <x-navigation-pills :tabs="$tabs" />

    <div class="tab-content p-0 shadow-none" id="activitiesTabContent">
      {{-- Suspicious Logins Tab --}}
      @include('admin.activities.partials.suspicious-logins')

      {{-- Activities Tab --}}
      @include('admin.activities.partials.user-activities')
    </div>
  </div>
</div>
@endsection

@section('scripts')
{{-- Use Chart.js from CDN with version pinning for reliability --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

{{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
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
      dom: '<"row mb-2 px-8"<"col-sm-6"l><"col-sm-6"f>><"row mb-3"<"col-12"<"filter-row px-4">>>rtip<"d-none"B>', // Add filter-row container
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
                    backgroundColor: ['#7367f0', '#ff9f43', '#28c76f', '#00bad1', '#ff4c51'],
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
                    backgroundColor: ['#00bad1', '#28c76f', '#ff9f43', '#ff4c51', '#7367f0'],
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
                    backgroundColor: '#28c76f',
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
                    borderColor: '#00bad1',
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