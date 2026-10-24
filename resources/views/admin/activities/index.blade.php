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

    const table = $('#activities-datatable').DataTable({
      ...getSharedDataTableConfig({ title: 'activities-datatable', descIndex: 6, actionsIndex: 7}),
      dom:
        '<"row"' +
        '<"col-12 col-lg-9 d-flex flex-wrap align-items-center ps-0 ps-lg-6 pb-6 pb-lg-0 mb-2"l>' +
        '<"col-12 col-lg-3 dt-action-buttons d-flex justify-content-lg-end pe-0 pe-lg-6"f>' +
        '>t<"row px-6"<"col-lg-6"i><"col-lg-6 d-flex justify-content-end"p>>',
      initComplete: function() {
        buildTableFilters(this.api());
        setTimeout(
          () => $('.form-control-sm, .form-select-sm').removeClass('form-control-sm form-select-sm'),
          300
        );
      },
    });

    /* ================================================================
     * Column filters
     * ============================================================== */
    function buildTableFilters(api) {
      const $wrapper = $(api.table().container());
      if ($('#filterPlatform').length || $('#filterBrowser').length || $('#filterLocation').length || $('#filterLoginTime').length) return;

      const lang = "{{ $l }}";
      const container = $('<div class="d-flex flex-wrap gap-3 ms-3"></div>').insertAfter('.dataTables_length');

      function addColumnFilter(colIdx, id, defaultLabel, useExactMatch) {
        const select = $(
          `<select id="${id}" class="form-select" style="min-width:150px">` +
          `<option value="">${defaultLabel}</option></select>`
        )
          .appendTo(container)
          .on('change', function () {
            const val = $(this).val();
            if (useExactMatch && val !== '') {
              api.column(colIdx)
                 .search('^' + $.fn.dataTable.util.escapeRegex(val) + '$', true, false)
                 .draw();
            } else {
              api.column(colIdx).search(val, false, false).draw();
            }
          });

        return select;
      }

      @if ($activities->isNotEmpty())
      const platformFilter = addColumnFilter(3, 'filterPlatform', lang === 'ar' ? 'جميع المنصات' : 'All Platforms', true);
      @foreach($activities->pluck('platform')->unique()->filter() as $platform)
        platformFilter.append(`<option value="{{ $platform }}">{{ $platform }}</option>`);
      @endforeach

      const browserFilter = addColumnFilter(1, 'filterBrowser', lang === 'ar' ? 'جميع المتصفحات' : 'All Browsers', true);
      @foreach($activities->pluck('browser')->unique()->filter() as $browser)
        browserFilter.append(`<option value="{{ $browser }}">{{ $browser }}</option>`);
      @endforeach

      const locationFilter = addColumnFilter(5, 'filterLocation', lang === 'ar' ? 'جميع المواقع' : 'All Locations', true);
      @foreach($activities->pluck('location')->unique()->filter() as $location)
        locationFilter.append(`<option value="{{ $location }}">{{ $location }}</option>`);
      @endforeach

      $(`<input type="text" id="filterLoginTime" class="form-control" style="min-width:150px; max-width:200px" placeholder="${lang === 'ar' ? 'اختر التاريخ' : 'Select Date'}">`)
        .appendTo(container);
      @endif

      $wrapper.find('.form-select, .dataTables_length select').each(function () {
        const $this = $(this);
        if ($this.hasClass('select2-hidden-accessible')) return;

        const isLength = $this.closest('.dataTables_length').length > 0;

        $this.wrap('<div class="position-relative' + (isLength ? ' d-inline-block' : '') + '"></div>');

        $this.select2({
          dropdownParent: $this.parent(),
          width: isLength ? '80px' : '100%',
          minimumResultsForSearch: Infinity,
        });

        if (!isLength) {
          $this.next('.select2-container').css({
            'min-width': '150px',
            'max-width': '200px'
          });
        }
      });

      flatpickr("#filterLoginTime", {
        dateFormat: "Y-m-d",
        allowInput: true,
        onReady: function (selectedDates, dateStr, instance) {
          const clearBtn = document.createElement("button");
          clearBtn.type = "button";
          clearBtn.className = "btn btn-sm btn-outline-primary w-100 mt-1";
          clearBtn.textContent = "{{ __('admin.Clear') }}";
          clearBtn.addEventListener("click", function () {
            instance.clear();
            instance.close();
          });
          instance.calendarContainer.appendChild(clearBtn);
        },
        onChange: function (selectedDates, dateStr, instance) {
          api.draw();
        }
      });

      if ($('#filterLoginTime').length) {
        $('#filterLoginTime').wrap('<div class="position-relative"></div>');
      }
    }

    $.fn.dataTable.ext.search.push(
      function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'activities-datatable') {
          return true;
        }

        const loginTimeFilter = $('#filterLoginTime').val();
        const loginTime = data[6] || '';

        if (loginTimeFilter) {
          if (!loginTime) return false;
          const datePart = loginTime.toString().split(',')[0].trim();
          const loginDate = new Date(datePart);
          const filterDate = new Date(loginTimeFilter);
          if (isNaN(loginDate) || isNaN(filterDate)) return false;
          if (loginDate.toDateString() !== filterDate.toDateString()) {
            return false;
          }
        }

        return true;
      }
    );
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
                    backgroundColor: [config.colors.primary, '#ff9f43', '#28c76f', '#00bad1', '#ff4c51'],
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
                    backgroundColor: ['#00bad1', '#28c76f', '#ff9f43', '#ff4c51', config.colors.primary],
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

    releaseAlert('warning', {
      title: "{{ __('admin.SureDelete') }}",
      message: "{{ __('admin.Recover') }}",
      confirmText: "{{ __('admin.DYes') }}",
      cancelText: "{{ __('admin.No') }}",
      confirmColor: 'danger'
    }).then((ok) => {
      if (ok) {
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
    releaseAlert('warning', {
      title: '@lang("admin.Force_Logout_Confirmation")',
      message: '@lang("admin.This_will_immediately_log_out_the_user")',
      confirmText: '@lang("admin.Yes_Force_Logout")',
      cancelText: '@lang("admin.Cancel")',
      confirmColor: 'danger'
    }).then((ok) => {
      if (ok) {
        $.post('{{ route("activities.force-logout") }}', {
          user_id: userId,
          _token: '{{ csrf_token() }}'
        }).done(function(response) {
          if (response.success) {
            releaseAlert('success', {
              title: 'Success!',
              message: response.message,
              confirmColor: 'success'
            });
          }
        }).fail(function() {
          releaseAlert('danger', {
            title: 'Error!',
            message: 'Failed to force logout user',
            confirmColor: 'danger'
          });
        });
      }
    });
  };

  window.resetPassword = function(userId) {
    releaseAlert('warning', {
      title: '@lang("admin.Reset_Password_Confirmation")',
      message: '@lang("admin.This_will_generate_a_new_password")',
      confirmText: '@lang("admin.Yes_Reset_Password")',
      cancelText: '@lang("admin.Cancel")',
      confirmColor: 'warning'
    }).then((ok) => {
      if (ok) {
        $.post('{{ route("activities.reset-password") }}', {
          user_id: userId,
          _token: '{{ csrf_token() }}'
        }).done(function(response) {
          console.log('response', response)
          if (response.success) {
            releaseAlert('success', {
              title: 'Password Reset!',
              message: `<p>${response.message}</p><p class="mt-3"><strong>@lang('admin.New_Password')</strong>:</p><div class="bg-light p-2 rounded"><code class="text-primary">${response.new_password}</code></div><p class="text-muted small mt-2">@lang('admin.Reset_Password_Caution')</p>`,
              confirmColor: 'success'
            });
          }
        }).fail(function() {
          releaseAlert('danger', {
            title: 'Error!',
            message: 'Failed to reset password',
            confirmColor: 'danger'
          });
        });
      }
    });
  };

  window.blockIp = function(ipAddress) {
    releaseAlert('warning', {
      title: '@lang("admin.Block_IP_Confirmation")',
      message: `@lang("admin.This_will_block_IP_address"): ${ipAddress}`,
      confirmText: '@lang("admin.Yes_Block_IP")',
      cancelText: '@lang("admin.Cancel")',
      confirmColor: 'danger'
    }).then((ok) => {
      if (ok) {
        $.post('{{ route("activities.block-ip") }}', {
          ip_address: ipAddress,
          _token: '{{ csrf_token() }}'
        }).done(function(response) {
          if (response.success) {
            releaseAlert('success', {
              title: 'Blocked!',
              message: response.message,
              confirmColor: 'success'
            });
          }
        }).fail(function() {
          releaseAlert('danger', {
            title: 'Error!',
            message: 'Failed to block IP address',
            confirmColor: 'danger'
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
      releaseAlert('danger', {
        title: 'Error!',
        message: 'Failed to load more activities',
        confirmColor: 'danger'
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
      releaseAlert('info', {
        title: '@lang("admin.No_data_to_export")',
        message: '@lang("admin.No_suspicious_logins_found")',
        confirmColor: 'primary'
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
