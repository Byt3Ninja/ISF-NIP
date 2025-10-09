<div id="mentor-survey-report">
<div class="col-12">
  <div class="d-flex flex-wrap justify-content-center gap-6 mb-8">
    @php
      // Calculate accurate counts based on mentorship requests
      $totalSessions = $mentorRequests->count();
      $answeredCount = 0;
      $notAnsweredCount = 0;
      $noSurveyCount = 0;
      
      foreach($mentorRequests as $session) {
        $key = $session->mentor_id . '-' . $session->id;
        $survey = $available_surveys_map[$key] ?? null;
        if($survey && $survey->responses && count($survey->responses) > 0) {
          $answeredCount++;
        } elseif($survey) {
          $notAnsweredCount++;
        } else {
          $noSurveyCount++;
        }
      }
      
      $kpiData = [
        ['value' => $totalSessions, 'label' => 'total', 'icon' => 'chart-dots-3', 'color' => 'primary', 'filter' => 'total'],
        ['value' => $answeredCount, 'label' => 'answered', 'icon' => 'circle-check', 'color' => 'success', 'filter' => 'answered'],
        ['value' => $notAnsweredCount, 'label' => 'notAnswered', 'icon' => 'circle-x', 'color' => 'danger', 'filter' => 'not-answered'],
      ];
    @endphp
    
    @foreach ($kpiData as $key => $item)
      <div>
        <button 
          type="button"
          class="card card-border-shadow-{{ $item['color'] }} h-100 text-dark mentor-survey-kpi-filter-card btn p-0 border-0"
          title="{{ __('cycles.Filter by') . ' ' . __('admin.' . $item['label']) }}"
          data-filter-type="{{ $item['filter'] }}"
          onclick="filterByMentorSurveyKPI(this)"
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

<!-- Mentor Surveys Results Section -->
<div class="row">
  <div class="col-12">
    <div class="card border">
      <div class="card-header border-bottom mb-6">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
          <h5 class="card-title mb-3 mb-md-0"> {{ __('admin.Mentor_Sessions_survey') }}</h5>
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <!-- Export Buttons -->
            <div class="btn-group">
              <button type="button" class="btn btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ti ti-file-export me-2"></i>{{ __('settings.Export') }}
              </button>
              <ul class="dropdown-menu">
                @foreach(['csv', 'excel'] as $key => $option)
                  <li>
                    <button id="mentor-export-{{ $option }}-btn" class="dropdown-item text-uppercase">
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
        <div id="tblMentorSurveysFilters" class="row g-2 align-items-center">
          <!-- Status Filter -->
          <div class="col-lg-3 col-md-4 col-sm-6">
            <select id="mentor-survey-status-filter" class="form-select form-select-sm">
              <option value="">{{ __('cycles.All Status') }}</option>
              <option value="not-answered">{{ __('admin.Open') }}</option>
              <option value="answered">{{ __('admin.Completed') }}</option>
            </select>
          </div>
  
          <!-- Program Filter -->
          <div class="col-lg-3 col-md-4 col-sm-6">
            <select id="mentor-survey-program-filter" class="form-select form-select-sm">
              <option value="">{{ __('admin.All_Programs') }}</option>
              @foreach ($mentorRequests->pluck('cycle')->unique('id') as $cycle)
                <option value="{{ $cycle->id }}">{{ $cycle->title }}</option>
              @endforeach
            </select>
          </div>
  
          <!-- Organization Filter -->
          <div class="col-lg-3 col-md-4 col-sm-6">
            <select id="mentor-survey-organization-filter" class="form-select form-select-sm">
              <option value="">{{ __('admin.All_Organizations') }}</option>
              @foreach ($mentorRequests->pluck('orgnization')->unique('id') as $organization)
                <option value="{{ $organization->id }}">{{ $organization->{'name_' . app()->getLocale()} }}</option>
              @endforeach
            </select>
          </div>
  
          <!-- Clear Filters Button -->
          <div class="col-lg-3 col-md-4 col-sm-6">
            <button id="clear-mentor-survey-filters" class="btn btn-sm btn-outline-secondary d-none">
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
          <!-- DataTable Controls Container -->
          <div id="mentor-datatable-wrapper">
            <!-- DataTable controls will be inserted here -->
          </div>
          
          <div class="table-responsive">
            <table class="table" id="mentor-survey-table">
              <thead class="table-light">
                <tr>
                  <th class="text-center">{{ __('admin.Mentor') }}</th>
                  <th class="text-center">{{ __('admin.Startup') }}</th>
                  <th class="text-center">{{ __('admin.Prorgam_name') }}</th>
                  <th class="text-center">{{ __('admin.Orgranization') }}</th>
                  <th class="text-center">{{ __('admin.Status') }}</th>
                  <th class="text-center">{{ __('admin.Session_date') }}</th>
                  <th class="text-center">{{ __('admin.Report_status') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($mentorRequests as $session)
                  @php
                    $key = $session->mentor_id . '-' . $session->id;
                    $available_surv = $available_surveys_map[$key] ?? null;
                    $status = 'no-survey';
                    if($available_surv && $available_surv->responses && count($available_surv->responses) > 0) {
                      $status = 'answered';
                    } elseif($available_surv) {
                      $status = 'not-answered';
                    }
                  @endphp
                  <tr class="mentor-survey-row" 
                      data-mentor="{{ strtolower($session->mentor->name) }}"
                      data-startup="{{ strtolower($session->user->company->{'name_' . app()->getLocale()}) }}"
                      data-program="{{ strtolower($session->cycle->title) }}"
                      data-program-id="{{ $session->cycle->id }}"
                      data-organization="{{ strtolower($session->orgnization->{'name_' . app()->getLocale()}) }}"
                      data-organization-id="{{ $session->orgnization->id }}"
                      data-status="{{ $status }}"
                      data-session-date="{{ $session->zoom_date }}">
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="w-px-40 h-px-40 d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0 rounded-circle border bg-label-secondary">
                          @if ($session->mentor->image_url)
                            <img 
                              src="{{ $session->mentor->image_url }}" 
                              alt="avatar" 
                              class="img-fluid rounded-circle"
                              height="40"
                              width="40" 
                            />
                          @else
                            <i class="ti ti-user"></i>
                          @endif
                        </div>
                        <span>{{ $session->mentor->name }}</span>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="w-px-40 h-px-40 d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0 rounded-circle border bg-label-secondary">
                          @if ($session->user->company->logo)
                            <img 
                              src="{{ $session->user->company->logo }}" 
                              alt="avatar" 
                              class="img-fluid rounded-circle"
                              height="40"
                              width="40" 
                            />
                          @else
                            <i class="ti ti-building-skyscraper"></i>
                          @endif
                        </div>
                        <span>{{ $session->user->company->{'name_' . app()->getLocale()} }}</span>
                      </div>
                    </td>
                    <td class="text-center">{{ $session->cycle->title }}</td>
                    <td class="text-center">{{ $session->orgnization->{'name_' . app()->getLocale()} }}</td>
                    <td class="text-center">
                      @if($status === 'answered')
                        <span class="badge bg-label-success">{{ __('admin.Completed') }}</span>
                      @elseif($status === 'not-answered')
                        <span class="badge bg-label-primary">{{ __('admin.Open') }}</span>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td class="text-center">{{ $session->zoom_date }}</td>
                    <td class="text-center">
                      @if($status === 'answered')
                        <span class="badge bg-label-secondary text-success">{{ __('admin.answered') }}</span>
                      @elseif($status === 'not-answered')
                        {{-- <a href="{{ route('surveys.take', ['id' => $available_surv->id]) }}" class="btn btn-sm btn-label-dark rounded w-100">{{ __('dashboard.startSurvey') }}</a> --}}
                        <span class="badge bg-label-secondary text-danger">{{ __('admin.notAnswered') }}</span>
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

</div>
<script>
  // Global variables for filtering (Mentor Survey - namespaced to avoid conflicts)
  if (typeof mentorSurveyRows === 'undefined') {
    var mentorSurveyRows = [];
  }
  if (typeof mentorSurveyCurrentFilters === 'undefined') {
    var mentorSurveyCurrentFilters = {
      status: '',
      program: '',
      organization: ''
    };
  }
  
  var mentorSurveyDataTable;

  $(document).ready(function() {
    // Ensure custom filters are ready before initializing DataTable
    setTimeout(function() {
      // Initialize DataTable
      if ($.fn.DataTable.isDataTable('#mentor-survey-table')) {
        $('#mentor-survey-table').DataTable().destroy();
      }
    
    mentorSurveyDataTable = $('#mentor-survey-table').DataTable({
      paging: true,
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
      searching: true, // Enable DataTable's built-in search
      ordering: true,
      info: true,
      autoWidth: false,
      responsive: true,
      dom: '<"#mentor-datatable-controls.row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>B<"row"<"col-sm-12"rt>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>', // Custom DOM layout with ID
      buttons: [
        {
          extend: 'csv',
          text: '<i class="ti ti-file-text me-1"></i>CSV',
          className: 'btn btn-outline-secondary btn-sm d-none',
          filename: function() {
            const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
            return `mentor_surveys_report_${timestamp}`;
          },
          title: 'Mentor Surveys Report',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude action column
            format: {
              body: function(data, row, column, node) {
                // Clean HTML tags and get text content
                const $node = $(node);
                if ($node.find('img').length > 0) {
                  return $node.text().trim();
                }
                if ($node.find('.badge').length > 0) {
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
            return `mentor_surveys_report_${timestamp}`;
          },
          title: 'Mentor Surveys Report',
          exportOptions: {
            columns: [0, 1, 2, 3, 4, 5], // Exclude action column
            format: {
              body: function(data, row, column, node) {
                const $node = $(node);
                if ($node.find('img').length > 0) {
                  return $node.text().trim();
                }
                if ($node.find('.badge').length > 0) {
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
      order: [[5, 'desc']] // Sort by date column by default
    });

    // Initialize rows array for custom filtering
    mentorSurveyRows = $('.mentor-survey-row').toArray();
    
    // Initialize search and filter functionality
    initializeMentorSurveySearchAndFilters();
    
    // Initialize export functionality
    initializeMentorSurveyExport();
    
    // Set initial filter indicators
    updateMentorSurveyFilterIndicators();
    
    // Enable debug mode by setting: window.debugMentorSurveyFilters = true in browser console
    if (window.debugMentorSurveyFilters) {
      console.log('Mentor Survey filtration system initialized with', mentorSurveyRows.length, 'surveys');
    }
    }, 100); // Small delay to ensure DOM is ready
  });

  function initializeMentorSurveySearchAndFilters() {
    // Filter change handlers
    $('#mentor-survey-status-filter, #mentor-survey-program-filter, #mentor-survey-organization-filter').on('change', function() {
      const filterId = $(this).attr('id');
      const filterType = filterId.replace('mentor-survey-', '').replace('-filter', '');
      mentorSurveyCurrentFilters[filterType] = $(this).val() || '';
      applyMentorSurveyFilters();
    });

    // Clear filters button
    $('#clear-mentor-survey-filters').on('click', function() {
      clearAllMentorSurveyFilters();
    });
  }

  function applyMentorSurveyFilters() {
    // Ensure DataTable exists before applying filters
    if (!mentorSurveyDataTable || !$.fn.DataTable.isDataTable('#mentor-survey-table')) {
      console.warn('DataTable not initialized yet, skipping filter application');
      return;
    }
    
    // Debug logging
    if (window.debugMentorSurveyFilters) {
      console.log('Applying mentor survey filters:', mentorSurveyCurrentFilters);
    }
    
    // Update display by rebuilding table with filtered data
    updateMentorSurveyDisplay();
    
    // Show/hide clear filters button and update filter indicators
    const hasActiveFilters = mentorSurveyCurrentFilters.status || mentorSurveyCurrentFilters.program || mentorSurveyCurrentFilters.organization;
    if (hasActiveFilters) {
      $('#clear-mentor-survey-filters').removeClass('d-none');
    } else {
      $('#clear-mentor-survey-filters').addClass('d-none');
    }
    
    // Update filter indicators
    updateMentorSurveyFilterIndicators();
  }

  function updateMentorSurveyDisplay() {
    // Clear and rebuild the table with filtered data
    mentorSurveyDataTable.clear();
    
    // Get all original rows
    let filteredRows = [...mentorSurveyRows];
    
    // Debug logging
    if (window.debugMentorSurveyFilters) {
      console.log('Starting with rows:', filteredRows.length);
      console.log('Current filters:', mentorSurveyCurrentFilters);
    }
    
    // Apply status filter
    if (mentorSurveyCurrentFilters.status) {
      filteredRows = filteredRows.filter(row => {
        const $row = $(row);
        const status = String($row.data('status') || '');
        const match = status === mentorSurveyCurrentFilters.status;
        if (window.debugMentorSurveyFilters && !match) {
          console.log('Filtered out by status:', status, '!==', mentorSurveyCurrentFilters.status);
        }
        return match;
      });
      if (window.debugMentorSurveyFilters) console.log('After status filter:', filteredRows.length);
    }
    
    // Apply program filter  
    if (mentorSurveyCurrentFilters.program) {
      filteredRows = filteredRows.filter(row => {
        const $row = $(row);
        const programId = String($row.data('program-id') || '');
        const match = programId === mentorSurveyCurrentFilters.program;
        if (window.debugMentorSurveyFilters && !match) {
          console.log('Filtered out by program:', programId, '!==', mentorSurveyCurrentFilters.program);
        }
        return match;
      });
      if (window.debugMentorSurveyFilters) console.log('After program filter:', filteredRows.length);
    }
    
    // Apply organization filter
    if (mentorSurveyCurrentFilters.organization) {
      filteredRows = filteredRows.filter(row => {
        const $row = $(row);
        const organizationId = String($row.data('organization-id') || '');
        const match = organizationId === mentorSurveyCurrentFilters.organization;
        if (window.debugMentorSurveyFilters && !match) {
          console.log('Filtered out by organization:', organizationId, '!==', mentorSurveyCurrentFilters.organization);
        }
        return match;
      });
      if (window.debugMentorSurveyFilters) console.log('After organization filter:', filteredRows.length);
    }
    
    // Add filtered rows back to DataTable
    filteredRows.forEach(row => {
      const $row = $(row);
      const rowData = [];
      $row.find('td').each(function() {
        rowData.push($(this).html());
      });
      mentorSurveyDataTable.row.add(rowData);
    });
    
    // Redraw the table
    mentorSurveyDataTable.draw();
    
    if (window.debugMentorSurveyFilters) {
      console.log('Final filtered rows added to DataTable:', filteredRows.length);
    }
  }

  function updateMentorSurveyFilterIndicators() {
    // Add visual indicators for active filters
    $('#mentor-survey-status-filter, #mentor-survey-program-filter, #mentor-survey-organization-filter').each(function() {
      const $select = $(this);
      const hasValue = $select.val() && $select.val() !== '';
      
      if (hasValue) {
        $select.addClass('border-primary');
      } else {
        $select.removeClass('border-primary');
      }
    });
  }

  function clearAllMentorSurveyFilters() {
    // Ensure DataTable exists
    if (!mentorSurveyDataTable || !$.fn.DataTable.isDataTable('#mentor-survey-table')) {
      return;
    }
    
    // Reset form inputs
    $('#mentor-survey-status-filter, #mentor-survey-program-filter, #mentor-survey-organization-filter').val('');
    
    // Clear DataTable search
    mentorSurveyDataTable.search('');
    
    // Reset current filters
    mentorSurveyCurrentFilters = {
      status: '',
      program: '',
      organization: ''
    };
    
    // Rebuild table with all rows (no filters applied)
    updateMentorSurveyDisplay();
    
    // Update filter indicators
    updateMentorSurveyFilterIndicators();
  }

  // KPI Card filtering functionality for Mentor Survey
  function filterByMentorSurveyKPI(cardElement) {
    const filterType = $(cardElement).data('filter-type');
    
    // Clear all current filters first
    clearAllMentorSurveyFilters();
    
    // Apply the specific filter based on KPI card clicked
    switch(filterType) {
      case 'total':
        // Show all - filters are already cleared
        break;
      case 'answered':
        mentorSurveyCurrentFilters.status = 'answered';
        $('#mentor-survey-status-filter').val('answered');
        break;
      case 'not-answered':
        mentorSurveyCurrentFilters.status = 'not-answered';
        $('#mentor-survey-status-filter').val('not-answered');
        break;
    }
    
    // Apply the filter and update display
    applyMentorSurveyFilters();
    
    // Add visual feedback to the clicked card
    $('.mentor-survey-kpi-filter-card').removeClass('border-primary');
    $(cardElement).addClass('border-primary');
    
    // Remove the border after a few seconds
    setTimeout(() => {
      $(cardElement).removeClass('border-primary');
    }, 3000);
    
    // Scroll to results section smoothly
    $('#mentor-survey-table').get(0).scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // Debug function for troubleshooting filters
  window.debugMentorSurveyFilters = function() {
    console.log('=== Mentor Survey Filters Debug ===');
    console.log('Current Filters:', mentorSurveyCurrentFilters);
    console.log('Total Rows:', mentorSurveyRows.length);
    console.log('DataTable Rows:', mentorSurveyDataTable.rows().count());
    
    // Test each filter
    mentorSurveyRows.forEach((row, index) => {
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
  function initializeMentorSurveyExport() {
    // Export functionality using DataTable buttons
    $('#mentor-export-csv-btn').on('click', function() {
      const button = $(this);
      const originalText = button.html();
      
      // Show loading state
      button.html('<i class="ti ti-loader ti-spin me-2"></i>{{ __("cycles.Exporting...") }}');
      
      // Trigger DataTable CSV export
      try {
        mentorSurveyDataTable.button('.buttons-csv').trigger();
      } catch (error) {
        console.error('CSV export error:', error);
        // Fallback to manual CSV export
        exportMentorSurveyToCSV();
      }
      
      // Reset button state
      setTimeout(() => {
        button.html(originalText);
      }, 1500);
    });

    $('#mentor-export-excel-btn').on('click', function() {
      const button = $(this);
      const originalText = button.html();
      
      // Show loading state
      button.html('<i class="ti ti-loader ti-spin me-2"></i>{{ __("cycles.Exporting...") }}');
      
      // Trigger DataTable Excel export
      try {
        mentorSurveyDataTable.button('.buttons-excel').trigger();
      } catch (error) {
        console.error('Excel export error:', error);
        // Fallback to CSV export
        exportMentorSurveyToCSV();
      }
      
      // Reset button state
      setTimeout(() => {
        button.html(originalText);
      }, 1500);
    });
  }

  // Fallback CSV export function
  function exportMentorSurveyToCSV() {
    const table = document.getElementById('mentor-survey-table');
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
    mentorSurveyDataTable.rows({ search: 'applied' }).data().each(function (rowData) {
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
      link.setAttribute('download', `mentor_surveys_report_${timestamp}.csv`);
      link.style.visibility = 'hidden';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }

  // Test function - you can call this in console to enable debugging
  window.enableMentorSurveyDebug = function() {
    window.debugMentorSurveyFilters = true;
    console.log('Mentor Survey debug mode enabled');
    console.log('Current DataTable:', mentorSurveyDataTable);
    console.log('Current Filters:', mentorSurveyCurrentFilters);
    console.log('Available rows:', $('.mentor-survey-row').length);
    
    // Test first few rows data
    $('.mentor-survey-row').slice(0, 3).each(function(i, row) {
      const $row = $(row);
      console.log(`Row ${i} data:`, {
        status: $row.data('status'),
        programId: $row.data('program-id'),
        organizationId: $row.data('organization-id')
      });
    });
  };
</script>


  
