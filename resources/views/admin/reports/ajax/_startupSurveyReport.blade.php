<div class="col-12">
  <div class="d-flex flex-wrap justify-content-center gap-6 mb-8">
    @php
      // Calculate accurate counts based on mentorship requests
      $totalSessions = $mentorRequests->count();
      $answeredCount = 0;
      $notAnsweredCount = 0;
      $noSurveyCount = 0;
      
      foreach($mentorRequests as $session) {
        $survey = $available_surveys->where('user_id', $session->user_id)->where('triggered_at', $session->id)->first();
        if($survey && $survey->responses && count($survey->responses) > 0) {
          $answeredCount++;
        } elseif($survey) {
          $notAnsweredCount++;
        } else {
          $noSurveyCount++;
        }
      }
      
      $kpiData = [
        ['value' => $totalSessions, 'label' => 'total_surveys', 'icon' => 'chart-dots-3', 'color' => 'primary', 'filter' => 'total'],
        ['value' => $answeredCount, 'label' => 'answered', 'icon' => 'circle-check', 'color' => 'success', 'filter' => 'answered'],
        ['value' => $notAnsweredCount, 'label' => 'notAnswered', 'icon' => 'circle-x', 'color' => 'danger', 'filter' => 'not-answered'],
      ];
    @endphp
    
    @foreach ($kpiData as $key => $item)
      <div>
        <button 
          type="button"
          class="card card-border-shadow-{{ $item['color'] }} h-100 text-dark startup-survey-kpi-filter-card btn p-0 border-0"
          title="{{ __('cycles.Filter by') . ' ' . __('admin.' . $item['label']) }}"
          data-filter-type="{{ $item['filter'] }}"
          onclick="filterByStartupSurveyKPI(this)"
        >
          <div class="card-body py-2" style="display: grid; place-items: center;">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-{{ $item['color'] }}">
                  <i class="ti ti-{{ $item['icon'] }} ti-20px"></i>
                </span>
              </div>
              <h5 class="mb-0">{{ $item['value'] }}</h5>
            </div>
            <small class="text-center">{{ __('admin.' . $item['label']) }}</small>
          </div>
        </button>
      </div>
    @endforeach
  </div>
</div>

<!-- Surveys Results Section -->
<div class="row">
  <div class="col-12">
    <div class="card border">
      <div class="card-header border-bottom mb-6">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
          <h5 class="card-title mb-3 mb-md-0"> {{ __('cycles.Startup_Surveys') }}</h5>
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <!-- Export Buttons -->
            <div class="btn-group">
              <button type="button" class="btn btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ti ti-file-export me-2"></i>{{ __('settings.Export') }}
              </button>
              <ul class="dropdown-menu">
                @foreach(['csv', 'excel'] as $key => $option)
                  <li>
                    <button id="export-{{ $option }}-btn" class="dropdown-item text-uppercase">
                      {{$option}}
                    </button>
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card-body">
        <!-- Enhanced Filter Section -->
        <div id="tblStartupSurveysFilters" class="row g-2 align-items-center">
          <!-- Status Filter -->
          <div class="col-lg-3 col-md-4 col-sm-6">
            <select id="startup-survey-status-filter" class="form-select form-select-sm">
              <option value="">{{ __('cycles.All Status') }}</option>
              <option value="not-answered">{{ __('admin.notAnswered') }}</option>
              <option value="answered">{{ __('admin.answered') }}</option>
            </select>
          </div>
  
          <!-- Program Filter -->
          <div class="col-lg-3 col-md-4 col-sm-6">
            <select id="startup-survey-program-filter" class="form-select form-select-sm">
              <option value="">{{ __('admin.All_Programs') }}</option>
              @foreach ($mentorRequests->pluck('cycle')->unique('id') as $cycle)
                <option value="{{ $cycle->id }}">{{ $cycle->title }}</option>
              @endforeach
            </select>
          </div>
  
          <!-- Organization Filter -->
          <div class="col-lg-3 col-md-4 col-sm-6">
            <select id="startup-survey-organization-filter" class="form-select form-select-sm">
              <option value="">{{ __('admin.All_Organizations') }}</option>
              @foreach ($mentorRequests->pluck('orgnization')->unique('id') as $organization)
                <option value="{{ $organization->id }}">{{ $organization->{'name_' . app()->getLocale()} }}</option>
              @endforeach
            </select>
          </div>
  
          <!-- Clear Filters Button -->
          <div class="col-lg-3 col-md-4 col-sm-6">
            <button id="clear-startup-survey-filters" class="btn btn-sm btn-outline-secondary d-none">
              <i class="ti ti-x me-1"></i>
              {{ __('cycles.Clear') }}
            </button>
          </div>
        </div>

        @if($mentorRequests->count() == 0)
          <!-- No Surveys Available -->
          <div class="col-12">
            <div class="text-center py-5">
              <p class="text-muted">{{ __('admin.No_surveys_found') }}</p>
            </div>
          </div>
        @else
          <div class="table-responsive">
            <table class="table" id="startup-survey-table">
              <thead class="table-light">
                <tr>
                  <th class="text-center">{{ __('admin.Mentor') }}</th>
                  <th class="text-center">{{ __('admin.Startup') }}</th>
                  <th class="text-center">{{ __('admin.Prorgam_name') }}</th>
                  <th class="text-center">{{ __('admin.Orgranization') }}</th>
                  <th class="text-center">{{ __('admin.Session_date') }}</th>
                  <th class="text-center">{{ __('admin.Report_status') }}</th>
                  <th class="text-center">{{ __('admin.Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @php $c = 1; @endphp
                @foreach ($mentorRequests as $session)
                  @php
                    $available_surv = $available_surveys->where('user_id', $session->user_id)->where('triggered_at', $session->id)->first();
                    $surveyStatus = 'not-answered';
                    if(isset($available_surv) && $available_surv->responses && count($available_surv->responses) > 0) {
                      $surveyStatus = 'answered';  
                    } elseif(isset($available_surv)) {
                      $surveyStatus = 'not-answered';
                    } else {
                      $surveyStatus = 'no-survey';
                    }
                  @endphp
                  <tr class="startup-survey-row" 
                      data-mentor="{{ strtolower($session->mentor->name ?? '') }}"
                      data-startup="{{ strtolower($session->user->company->{'name_' . app()->getLocale()} ?? '') }}"
                      data-program="{{ strtolower($session->cycle->title ?? '') }}"
                      data-program-id="{{ $session->cycle->id ?? '' }}"
                      data-organization="{{ strtolower($session->orgnization->{'name_' . app()->getLocale()} ?? '') }}"
                      data-organization-id="{{ $session->orgnization->id ?? '' }}"
                      data-status="{{ $surveyStatus }}"
                      data-date="{{ $session->zoom_date ?? '' }}">
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <img 
                          src="{{ $session->mentor->image_url ?? '/icons/account.svg' }}"
                          onerror="this.onerror=null;this.src='/icons/account.svg';"
                          alt="avatar" 
                          class="rounded-circle" 
                          height="40"
                          width="40" />
                        <span>{{ $session->mentor->name }}</span>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <img 
                          src="{{ $session->user->company->logo ?? '/images/logo-placeholder.png' }}" 
                          onerror="this.onerror=null;this.src='/images/logo-placeholder.png';"
                          alt="avatar" class="rounded-circle border"
                          height="40"
                          width="40" 
                        />
                        <span>{{ $session->user->company->{'name_' . app()->getLocale()} }}</span>
                      </div>
                    </td>
                    <td class="text-center">{{ $session->cycle->title }}</td>
                    <td class="text-center">{{ $session->orgnization->{'name_' . app()->getLocale()} }}</td>
                    <td class="text-center">{{ $session->zoom_date }}</td>
                    <td class="text-center">
                      @if($surveyStatus === 'answered')
                        <span class="badge bg-label-success">{{ __('admin.answered') }}</span>
                      @elseif($surveyStatus === 'not-answered')
                        <span class="badge bg-label-danger">{{ __('admin.notAnswered') }}</span>
                      @else
                        <span class="badge bg-label-secondary">{{ __('admin.No_Survey') }}</span>
                      @endif
                    </td>
                    <td class="text-center">
                      @if($surveyStatus === 'answered')
                        <a href="/dashboard/reports/show-survey/{{ $available_surv->id }}" class="btn btn-sm btn-primary">
                          <i class="ti ti-eye me-2"></i>
                          <span class="text-nowrap">{{ __('admin.View_Report') }}</span>
                        </a>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

<script>
  // Global variables for filtering (Startup Survey - namespaced to avoid conflicts)
  if (typeof startupSurveyRows === 'undefined') {
    var startupSurveyRows = [];
  }
  if (typeof startupSurveyCurrentFilters === 'undefined') {
    var startupSurveyCurrentFilters = {
      status: '',
      program: '',
      organization: ''
    };
  }
  
  var startupSurveyDataTable;

  $(document).ready(function() {
    // Initialize DataTable
    if ($.fn.DataTable.isDataTable('#startup-survey-table')) {
      $('#startup-survey-table').DataTable().destroy();
    }
    
    startupSurveyDataTable = $('#startup-survey-table').DataTable({
      paging: true,
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
      searching: true, // Enable DataTable's built-in search
      ordering: true,
      info: true,
      autoWidth: false,
      responsive: true,
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>B<"row"<"col-sm-12"rt>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>', // Custom DOM layout
      buttons: [
        {
          extend: 'csv',
          text: '<i class="ti ti-file-text me-1"></i>CSV',
          className: 'btn btn-outline-secondary btn-sm d-none',
          filename: function() {
            const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
            return `startup_surveys_report_${timestamp}`;
          },
          title: 'Startup Surveys Report',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude action column
            format: {
              body: function(data, row, column, node) {
                // Clean HTML tags and get text content
                const $node = $(node);
                if ($node.find('img').length > 0) {
                  // For columns with images, get the text after the image
                  return $node.find('span').text() || $node.text().trim();
                }
                if ($node.find('.badge').length > 0) {
                  // For badge columns, get badge text
                  return $node.find('.badge').text().trim();
                }
                return $node.text().trim();
              }
            }
          }
        },
        {
          extend: 'excel',
          text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
          className: 'btn btn-outline-secondary btn-sm d-none',
          filename: function() {
            const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
            return `startup_surveys_report_${timestamp}`;
          },
          title: 'Startup Surveys Report',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude action column
            format: {
              body: function(data, row, column, node) {
                // Clean HTML tags and get text content
                const $node = $(node);
                if ($node.find('img').length > 0) {
                  // For columns with images, get the text after the image
                  return $node.find('span').text() || $node.text().trim();
                }
                if ($node.find('.badge').length > 0) {
                  // For badge columns, get badge text
                  return $node.find('.badge').text().trim();
                }
                return $node.text().trim();
              }
            }
          }
        }
      ],
      language: {
        paginate: {
          next: '<i class="ti ti-chevron-right"></i>',
          previous: '<i class="ti ti-chevron-left"></i>'
        },
        lengthMenu: '{{ __("cycles.Show") }} _MENU_ {{ __("cycles.entries") }}',
        info: '{{ __("cycles.Showing") }} _START_ {{ __("cycles.to") }} _END_ {{ __("cycles.of") }} _TOTAL_ {{ __("cycles.entries") }}',
        infoEmpty: '{{ __("cycles.Showing") }} 0 {{ __("cycles.to") }} 0 {{ __("cycles.of") }} 0 {{ __("cycles.entries") }}',
        infoFiltered: '({{ __("cycles.filtered from") }} _MAX_ {{ __("cycles.total entries") }})',
        emptyTable: '{{ __("admin.No_surveys_found") }}',
        zeroRecords: '{{ __("cycles.No_surveys_match_filters") }}',
        buttons: {
          csv: '{{ __("settings.Export") }} CSV',
          excel: '{{ __("settings.Export") }} Excel'
        }
      },
      columnDefs: [
        { targets: [0, 1, 2, 3, 4, 5, 6], orderable: true },
        { targets: [0, 1, 6], orderable: false } // Disable ordering for image columns and actions
      ],
      order: [[4, 'desc']] // Sort by date column by default
    });

    // Initialize rows array for custom filtering
    startupSurveyRows = $('.startup-survey-row').toArray();
    
    // Initialize search and filter functionality
    initializeStartupSurveySearchAndFilters();
    
    // Initialize export functionality
    initializeStartupSurveyExport();
    
    // Set initial filter indicators
    updateStartupSurveyFilterIndicators();
    
    // Enable debug mode by setting: window.debugStartupSurveyFilters = true in browser console
    if (window.debugStartupSurveyFilters) {
      console.log('Startup Survey filtration system initialized with', startupSurveyRows.length, 'surveys');
    }
  });

  function initializeStartupSurveySearchAndFilters() {
    // Filter change handlers
    $('#startup-survey-status-filter, #startup-survey-program-filter, #startup-survey-organization-filter').on('change', function() {
      const filterId = $(this).attr('id');
      const filterType = filterId.replace('startup-survey-', '').replace('-filter', '');
      startupSurveyCurrentFilters[filterType] = $(this).val() || '';
      applyStartupSurveyFilters();
    });

    // Clear filters button
    $('#clear-startup-survey-filters').on('click', function() {
      clearAllStartupSurveyFilters();
    });
  }

  function applyStartupSurveyFilters() {
    // Debug logging
    if (window.debugStartupSurveyFilters) {
      console.log('Applying startup survey filters:', startupSurveyCurrentFilters);
    }
    
    // Update display by rebuilding the table
    updateStartupSurveyDisplay();
    
    // Show/hide clear filters button and update filter indicators
    const hasActiveFilters = startupSurveyCurrentFilters.status || startupSurveyCurrentFilters.program || startupSurveyCurrentFilters.organization;
    if (hasActiveFilters) {
      $('#clear-startup-survey-filters').removeClass('d-none');
    } else {
      $('#clear-startup-survey-filters').addClass('d-none');
    }
    
    // Update filter indicators
    updateStartupSurveyFilterIndicators();
  }

  function updateStartupSurveyDisplay() {
    // Clear the DataTable and rebuild with filtered data
    startupSurveyDataTable.clear();
    
    // Filter the original rows based on current filters
    const filteredRows = startupSurveyRows.filter(row => {
      const $row = $(row);
      
      // Apply status filter
      if (startupSurveyCurrentFilters.status) {
        const status = String($row.data('status') || '');
        if (status !== startupSurveyCurrentFilters.status) {
          return false;
        }
      }
      
      // Apply program filter  
      if (startupSurveyCurrentFilters.program) {
        const programId = String($row.data('program-id') || '');
        if (programId !== startupSurveyCurrentFilters.program) {
          return false;
        }
      }
      
      // Apply organization filter
      if (startupSurveyCurrentFilters.organization) {
        const organizationId = String($row.data('organization-id') || '');
        if (organizationId !== startupSurveyCurrentFilters.organization) {
          return false;
        }
      }
      
      return true;
    });
    
    // Add filtered rows back to the DataTable
    filteredRows.forEach(row => {
      const $row = $(row);
      const rowData = [];
      
      // Extract data from each cell (excluding the row number)
      $row.find('td').each(function() {
        rowData.push($(this).html());
      });
      
      startupSurveyDataTable.row.add(rowData);
    });
    
    // Redraw the table
    startupSurveyDataTable.draw();
    
    // Debug logging
    if (window.debugStartupSurveyFilters) {
      console.log(`Filtered ${filteredRows.length} rows from ${startupSurveyRows.length} total rows`);
    }
  }

  function updateStartupSurveyFilterIndicators() {
    // Add visual indicators for active filters
    $('#startup-survey-status-filter, #startup-survey-program-filter, #startup-survey-organization-filter').each(function() {
      const $select = $(this);
      const hasValue = $select.val() && $select.val() !== '';
      
      if (hasValue) {
        $select.addClass('border-primary');
      } else {
        $select.removeClass('border-primary');
      }
    });
  }

  function clearAllStartupSurveyFilters() {
    // Reset form inputs
    $('#startup-survey-status-filter, #startup-survey-program-filter, #startup-survey-organization-filter').val('');
    
    // Clear DataTable search
    startupSurveyDataTable.search('').draw();
    
    // Reset current filters
    startupSurveyCurrentFilters = {
      status: '',
      program: '',
      organization: ''
    };
    
    // Rebuild table with all rows
    startupSurveyDataTable.clear();
    startupSurveyRows.forEach(row => {
      const $row = $(row);
      const rowData = [];
      
      // Extract data from each cell
      $row.find('td').each(function() {
        rowData.push($(this).html());
      });
      
      startupSurveyDataTable.row.add(rowData);
    });
    startupSurveyDataTable.draw();
    
    // Update filter indicators
    updateStartupSurveyFilterIndicators();
    
    // Hide clear filters button
    $('#clear-startup-survey-filters').addClass('d-none');
  }

  // KPI Card filtering functionality for Startup Survey
  function filterByStartupSurveyKPI(cardElement) {
    const filterType = $(cardElement).data('filter-type');
    
    // Clear all current filters first
    clearAllStartupSurveyFilters();
    
    // Apply the specific filter based on KPI card clicked
    switch(filterType) {
      case 'total':
        // Show all - filters are already cleared
        break;
      case 'answered':
        startupSurveyCurrentFilters.status = 'answered';
        $('#startup-survey-status-filter').val('answered');
        break;
      case 'not-answered':
        startupSurveyCurrentFilters.status = 'not-answered';
        $('#startup-survey-status-filter').val('not-answered');
        break;
      case 'no-survey':
        startupSurveyCurrentFilters.status = 'no-survey';
        $('#startup-survey-status-filter').val('no-survey');
        break;
    }
    
    // Apply the filter and update display
    applyStartupSurveyFilters();
    
    // Add visual feedback to the clicked card
    $('.startup-survey-kpi-filter-card').removeClass('border-primary');
    $(cardElement).addClass('border-primary');
    
    // Remove the border after a few seconds
    setTimeout(() => {
      $(cardElement).removeClass('border-primary');
    }, 3000);
    
    // Scroll to results section smoothly
    $('#startup-survey-table').get(0).scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // Debug function for troubleshooting filters
  window.debugStartupSurveyFilters = function() {
    console.log('=== Startup Survey Filters Debug ===');
    console.log('Current Filters:', startupSurveyCurrentFilters);
    console.log('Total Rows:', startupSurveyRows.length);
    console.log('DataTable Rows:', startupSurveyDataTable.rows().count());
    
    // Test each filter
    startupSurveyRows.forEach((row, index) => {
      const $row = $(row);
      console.log(`Row ${index}:`, {
        mentor: $row.data('mentor'),
        startup: $row.data('startup'),
        program: $row.data('program'),
        organization: $row.data('organization'),
        status: $row.data('status')
      });
    });
  };

  // Initialize export functionality
  function initializeStartupSurveyExport() {
    // Export functionality using DataTable buttons
    $('#export-csv-btn').on('click', function() {
      const button = $(this);
      const originalText = button.html();
      
      // Show loading state
      button.html('<i class="ti ti-loader ti-spin me-2"></i>{{ __("cycles.Exporting...") }}');
      
      // Trigger DataTable CSV export
      try {
        startupSurveyDataTable.button('.buttons-csv').trigger();
      } catch (error) {
        console.error('CSV export error:', error);
        // Fallback to manual CSV export
        exportToCSV();
      }
      
      // Reset button state
      setTimeout(() => {
        button.html(originalText);
      }, 1500);
    });

    $('#export-excel-btn').on('click', function() {
      const button = $(this);
      const originalText = button.html();
      
      // Show loading state
      button.html('<i class="ti ti-loader ti-spin me-2"></i>{{ __("cycles.Exporting...") }}');
      
      // Trigger DataTable Excel export
      try {
        startupSurveyDataTable.button('.buttons-excel').trigger();
      } catch (error) {
        console.error('Excel export error:', error);
        // Fallback to CSV export
        exportToCSV();
      }
      
      // Reset button state
      setTimeout(() => {
        button.html(originalText);
      }, 1500);
    });
  }

  // Fallback CSV export function
  function exportToCSV() {
    const table = document.getElementById('startup-survey-table');
    let csv = [];
    
    // Get headers
    const headers = [];
    table.querySelectorAll('thead th').forEach((th, index) => {
      if (index < 6) { // Exclude action column
        headers.push('"' + th.textContent.trim().replace(/"/g, '""') + '"');
      }
    });
    csv.push(headers.join(','));
    
    // Get visible rows data
    startupSurveyDataTable.rows({ search: 'applied' }).data().each(function (rowData) {
      const row = [];
      for (let i = 0; i < 6; i++) { // Exclude action column
        let cellData = '';
        if (typeof rowData[i] === 'string') {
          // Clean HTML and extract text
          const tempDiv = document.createElement('div');
          tempDiv.innerHTML = rowData[i];
          cellData = tempDiv.textContent || tempDiv.innerText || '';
        } else {
          cellData = rowData[i] || '';
        }
        row.push('"' + String(cellData).trim().replace(/"/g, '""') + '"');
      }
      csv.push(row.join(','));
    });
    
    // Download CSV
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
    
    if (link.download !== undefined) {
      const url = URL.createObjectURL(blob);
      link.setAttribute('href', url);
      link.setAttribute('download', `startup_surveys_report_${timestamp}.csv`);
      link.style.visibility = 'hidden';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }
</script>