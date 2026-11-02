<div class="col-12 mt-6">
  <div class="row justify-content-center mb-8">
    @foreach($kpiData as $key => $item)
      <div class="col-sm-6 col-md-4 col-lg-2 mb-4">
        @php
          $cardClasses = 'card card-border-shadow-' . $item['color'] . ' h-100 cursor-pointer kpi-filter-card';
          $cardClasses .= ' kpi-' . $item['label'];
        @endphp
        <div class="{{ $cardClasses }}" data-filter="{{ $item['label'] }}">
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
        </div>
      </div>
    @endforeach
  </div>
</div>

<div class="col-12 mt-6">
  <div class="card border">
    <div class="card-header d-md-flex justify-content-between border-bottom pb-3 mb-6">
      <h5 class="card-title mb-3 mb-md-0">{{ __('admin.programs_overview') }}</h5>
      <div class="d-flex align-items-center gap-3">
        <div class="btn-group">
          <button type="button" class="btn btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
          </button>
          <ul class="dropdown-menu">
            @foreach(['csv', 'excel'] as $option)
              <li>
                <a class="dropdown-item text-uppercase" href="javascript:void(0)" onclick="exportCyclesKpi('{{$option}}')">
                  {{$option}}
                </a>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover" id="tblCycleKPIs">
          <thead class="table-light">
            <tr>
              <th>{{ __('admin.Program') }}</th>
              <th>{{ __('admin.Organization') }}</th>
              <th>{{ __('admin.Start_Date') }}</th>
              <th>{{ __('admin.End_Date') }}</th>
              <th>{{ __('admin.Status') }}</th>
              <th>{{ __('admin.Subscribed_Startups') }}</th>
              <th>{{ __('admin.Enrolled_Mentors') }}</th>
              <th>{{ __('admin.Actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($cycles as $_cycle)
              @php($kpi = $cycles_kpis[$_cycle->id] ?? null)
              @if($kpi)
                <tr data-cycle-id="{{ $_cycle->id }}">
                  <td>{{ $_cycle->title }}</td>
                  <td>{{ $_cycle->organization->name ?? '-' }}</td>
                  <td>{{ $_cycle->start_format ?? '' }}</td>
                  <td>{{ $_cycle->end_format ?? '' }}</td>
                  <td>
                    {{-- GV-9566: start/end are inclusive days in the program's platform zone --}}
                    @php($cycleToday = \App\Services\TimezoneService::platformToday(\App\Services\TimezoneService::platform($_cycle->organization_id)))
                    @if( $_cycle->start?->format('Y-m-d') <= $cycleToday && $cycleToday <= $_cycle->end?->format('Y-m-d') )
                      <span class="badge bg-label-primary w-100  ms-2">
                        {{ __('admin.On going') }}
                      </span>
                    @elseif( $_cycle->start?->format('Y-m-d') > $cycleToday )
                      <span class="badge bg-label-warning w-100  ms-2">
                        {{ __('admin.Not started') }}
                      </span>
                    @elseif( $_cycle->end?->format('Y-m-d') < $cycleToday )
                      <span class="badge bg-label-success w-100  ms-2">
                        {{ __('admin.completed') }}
                      </span>
                    @else
                    ----
                    @endif
                  </td>
                  <td>{{ $kpi['startups'] }}</td>
                  <td>{{ $kpi['mentors_count'] }}</td>
                  <td>
                    <a 
                      href="/dashboard/programlist/report/{{ $_cycle->id }}" 
                      class="btn btn-sm btn-primary text-nowrap"
                    >
                      <i class="ti ti-file-text ti-16px me-1"></i>{{ __('admin.View_Report') }}
                    </a>
                  </td>
                </tr>
              @endif
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    let cyclesKpiTable = $('#tblCycleKPIs').DataTable({
      responsive: true,
      pageLength: 10,
      order: [[0, 'asc']],
      language: {
        @if (app()->getLocale() == 'ar')
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
        { extend: 'csv', text: '<i class="ti ti-file-text me-1"></i>CSV', className: 'd-none', title: `Programs_KPI_${new Date().toISOString().slice(0, 10)}` },
        { extend: 'excel', text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel', className: 'd-none', title: `Programs_KPI_${new Date().toISOString().slice(0, 10)}` }
      ]
    });

    // Build and inject filters
    addFiltersRow();

    // KPI Card Click Handler
    let currentKpiFilter = null;

    $('.kpi-filter-card').on('click', function() {
      const filterType = $(this).data('filter');
      
      // Remove active state from all KPI cards
      $('.kpi-filter-card').removeClass('border-primary').css('opacity', '1');
      
      if (currentKpiFilter === filterType) {
        // If clicking the same filter, reset
        currentKpiFilter = null;
        applyKpiFilter(null);
      } else {
        // Apply new filter
        currentKpiFilter = filterType;
        $(this).addClass('border-primary').css('opacity', '1');
        $('.kpi-filter-card').not(this).css('opacity', '0.6');
        applyKpiFilter(filterType);
      }
    });

    function applyKpiFilter(filterType) {
      // Get ongoing and completed program IDs from controller
      const ongoingIds = @json($ongoingCycles->pluck('id')->toArray() ?? []);
      const completedIds = @json($completed_programs->pluck('id')->toArray() ?? []);
      const notStartedIds = @json($not_started_programs->pluck('id')->toArray() ?? []);

      // Remove previous KPI filter
      $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
        return fn.toString().indexOf('KPI_FILTER') === -1;
      });

      if (filterType === 'ongoing_programs') {
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
          if (settings.nTable.id !== 'tblCycleKPIs') return true;
          const row = cyclesKpiTable.row(dataIndex).node();
          const cycleId = $(row).data('cycle-id');
          return ongoingIds.includes(cycleId); // KPI_FILTER
        });
      } else if (filterType === 'completed_programs') {
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
          if (settings.nTable.id !== 'tblCycleKPIs') return true;
          const row = cyclesKpiTable.row(dataIndex).node();
          const cycleId = $(row).data('cycle-id');
          return completedIds.includes(cycleId); // KPI_FILTER
        });
      } else if ( filterType === 'not_started_programs' ) {
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
          if (settings.nTable.id !== 'tblCycleKPIs') return true;
          const row = cyclesKpiTable.row(dataIndex).node();
          const cycleId = $(row).data('cycle-id');
          return notStartedIds.includes(cycleId); // KPI_FILTER
        });
      }
      // If filterType is 'total_programs' or null, show all (no custom filter)

      cyclesKpiTable.draw();
    }

    // Build and inject filters in a dedicated row above the table
    function addFiltersRow() {
      const START_DATE_COL_IDX = 2; // Start Date column index
      const END_DATE_COL_IDX = 3;   // End Date column index
      
      // Create filters container above the DataTable wrapper
      const $tableWrapper = $('#tblCycleKPIs_wrapper');
      if (!$tableWrapper.length) return;

      // Create main filters row
      const $filtersRow = $('<div/>', {
        id: 'cycleFiltersRow',
        class: 'row mb-3 align-items-end'
      });

      // Organization Filter Column
      const $orgCol = $('<div/>', {
        class: 'col-md-3'
      });
     
      const $orgSelect = $('<select/>', {
        id: 'orgFilterSelect',
        class: 'form-select form-select-sm',
        'aria-label': @json(__('admin.organization_filter'))
      });
      $orgCol.append($orgSelect);

      // Start Date Filter Column
      const $startCol = $('<div/>', {
        class: 'col-md-3'
      });
      const $startInput = $('<input/>', {
        type: 'text',
        id: 'startDateFilter',
        class: 'form-control form-control-sm datepicker',
        placeholder: "{{ __('admin.Start_Date') }}"
      });
      $startCol.append($startInput);

      // End Date Filter Column
      const $endCol = $('<div/>', {
        class: 'col-md-3'
      });
     
      const $endInput = $('<input/>', {
        type: 'text',
        id: 'endDateFilter',
        class: 'form-control form-control-sm datepicker',
        placeholder: "{{ __('admin.End_Date') }}"
      });
      $endCol.append($endInput);

      // Assemble the row
      $filtersRow.append($orgCol, $startCol, $endCol);
      
      // Insert before the table wrapper
      $tableWrapper.before($filtersRow);

      // Initialize Organization Filter
      initializeOrganizationFilter();
      
      // Initialize Date Pickers
      initializeDatePickers();

      // Initialize Organization Filter
      function initializeOrganizationFilter() {
        const ORG_COL_IDX = 1; // Organization column index
        const orgSet = new Set();
        
        // Collect unique organization names from rendered cells
        cyclesKpiTable.column(ORG_COL_IDX).nodes().each(function(cell) {
          const name = $(cell).text().trim();
          if (name && name !== '-') orgSet.add(name);
        });

        const orgs = Array.from(orgSet).sort((a, b) => a.localeCompare(b));
        const $orgSelect = $('#orgFilterSelect');
        
        if (orgs.length === 0) return;

        // Populate organization dropdown
        const allText = "{{ __('admin.All_Organizations') }}";
        $orgSelect.append($('<option/>', { value: '' }).text(allText));
        orgs.forEach(o => $orgSelect.append($('<option/>', { value: o }).text(o)));

        // Hook up the filtering logic
        $orgSelect.on('change', function() {
          const val = this.value;
          const escaped = $.fn.dataTable.util.escapeRegex(val);
          cyclesKpiTable
            .column(ORG_COL_IDX)
            .search(val ? '^' + escaped + '$' : '', true, false)
            .draw();
        });
      }

      // Initialize Date Pickers
      function initializeDatePickers() {
        const START_DATE_COL_IDX = 2;
        const END_DATE_COL_IDX = 3;

        // Initialize datepicker
        $('.datepicker').flatpickr({
          monthSelectorType: 'static',
          allowInput: true,
          onChange: function(selectedDates) {
            filterByDateRange();
          },
          onReady: function (selectedDates, dateStr, instance) {
            let btn = document.createElement("button");
            btn.type = "button";
            btn.innerHTML = "Clear";
            btn.classList.add("btn", "btn-sm", "btn-outline-secondary", "mt-1");
            btn.addEventListener("click", function () {
              instance.clear();
              instance.close();
              filterByDateRange();
            });
            instance.calendarContainer.appendChild(btn);
          }
        });

        // Date range filtering function
        function filterByDateRange() {
          const startDate = $('#startDateFilter').val();
          const endDate = $('#endDateFilter').val();

          // Remove previous custom filter but keep KPI filter
          $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
            return fn.toString().indexOf('tblCycleKPIs') === -1 || fn.toString().indexOf('KPI_FILTER') !== -1;
          });
          
          if (startDate || endDate) {
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
              if (settings.nTable.id !== 'tblCycleKPIs') return true;
              
              const rowStartDate = data[START_DATE_COL_IDX] ? data[START_DATE_COL_IDX].replace(/<[^>]*>/g, '').trim() : '';
              const rowEndDate = data[END_DATE_COL_IDX] ? data[END_DATE_COL_IDX].replace(/<[^>]*>/g, '').trim() : '';
              
              // Convert dates to comparable format (YYYY-MM-DD)
              const parseDate = (dateStr) => {
                if (!dateStr || dateStr === '-') return null;
                const date = new Date(dateStr);
                return isNaN(date.getTime()) ? null : date.toISOString().split('T')[0];
              };
              
              const parsedRowStartDate = parseDate(rowStartDate);
              const parsedRowEndDate = parseDate(rowEndDate);
              
              // Apply start date filter
              if (startDate && parsedRowStartDate) {
                if (parsedRowStartDate < startDate) return false;
              }
              
              // Apply end date filter
              if (endDate && parsedRowEndDate) {
                if (parsedRowEndDate > endDate) return false;
              }
              
              return true;
            });
          }
          
          cyclesKpiTable.draw();
        }

        // Clear all filters functionality
        $('#clearAllFilters').on('click', function() {
          // Clear date pickers
          $('#startDateFilter, #endDateFilter').each(function() {
            if (this._flatpickr) {
              this._flatpickr.clear();
            }
          });
          
          // Clear organization filter
          $('#orgFilterSelect').val('').trigger('change');
          
          // Remove custom date filters but keep KPI filter
          $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
            return fn.toString().indexOf('tblCycleKPIs') === -1 || fn.toString().indexOf('KPI_FILTER') !== -1;
          });
          
          cyclesKpiTable.draw();
        });
      }
    }

    // Export helper
    window.exportCyclesKpi = function(format) {
      if (!cyclesKpiTable) return;
      try {
        cyclesKpiTable.button(`.buttons-${format}`).trigger();
      } catch (e) {
        console.error('Export error:', e);
      }
    }
  });
</script>
