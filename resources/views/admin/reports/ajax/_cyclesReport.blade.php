<style>
.cursor-pointer {
  cursor: pointer;
}
</style>
<div class="col-12 mt-6">
  <div class="row justify-content-center mb-8">
    @foreach($kpiData as $key => $item)
      <div class="col-sm-6 col-md-4 col-lg-2 mb-4">
        @php
          $isSubscribedCard = $item['label'] == 'Subscribed_Startups';
          $isMentorshipCard = $item['label'] == 'Mentorship_Requests';
          $isMentorshipDurationCard = $item['label'] == 'Mentorship_Sessions_Duration';
          $cardClasses = 'card card-border-shadow-' . $item['color'] . ' h-100';
          $isScreeningCard = $item['label'] == 'Screening';
          $isFinalScreeningCard = $item['label'] == 'Final_Screening';
          if ($isSubscribedCard || $isMentorshipCard || $isMentorshipDurationCard || $isScreeningCard || $isFinalScreeningCard) {
            $cardClasses .= ' cursor-pointer';
          }
          if ($isSubscribedCard) {
            $cardClasses .= ' subscribed-startups-card';
          }
          if ($isMentorshipCard) {
            $cardClasses .= ' mentorship-requests-card';
          }
          if ($isMentorshipDurationCard) {
            $cardClasses .= ' mentorship-duration-card';
          }
          if ($isScreeningCard) {
            $cardClasses .= ' screening-startups-card';
          }
          if ($isFinalScreeningCard) {
            $cardClasses .= ' final-screening-startups-card';
          }

          $unitSuffix = '';
          if (isset($item['unit']) && $item['unit']) {
            if ($item['unit'] !== 'hh:mm') {
              $unitSuffix = ' ' . $item['unit'];
            }
          }
        @endphp
        <div class="{{ $cardClasses }}"
          @if($isSubscribedCard)
            data-url="{{ route('users.view_companies', ['programFilter' => 'all']) }}"
          @elseif($isMentorshipCard)
            data-url="{{ route('mentorships.sessions_list', ['show' => 'all']) }}"
          @elseif($isScreeningCard)
            data-url="/dashboard/screening/results"
          @elseif($isFinalScreeningCard)
            data-url="/dashboard/final/results"
          @endif
        >
        <div class="card-body py-2" style="display: grid; place-items: center;">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-{{ $item['color'] }}">
                  <i class="ti ti-{{ $item['icon'] }} ti-20px"></i>
                </span>
              </div>
              <h5 class="mb-0">{{ $item['value'] }}{!! $unitSuffix !!}</h5>
            </div>
            <small class="text-center">{{ __('admin.' . $item['label']) }}</small>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>

@php
  $hasCycles = isset($cycles) && $cycles->count();
@endphp

@if($hasCycles)
<div class="modal fade" id="mentorshipDurationModal" tabindex="-1" aria-labelledby="mentorshipDurationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mentorshipDurationModalLabel">
          <i class="ti ti-clock me-2"></i>
          {{ __('admin.Mentorship_Sessions_Duration') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="mentorshipDurationSelect" class="form-label">{{ __('cycles.Cycle') }}</label>
          <select id="mentorshipDurationSelect" class="form-select select2">
            @foreach($cycles as $cycleOption)
              <option value="{{ $cycleOption->id }}">{{ $cycleOption->title }}</option>
            @endforeach
          </select>
        </div>

        <div class="table-responsive" id="mentorshipDurationTableWrapper">
          <table class="table table-striped" id="mentorshipDurationTable">
            <thead class="table-light">
              <tr>
                <th style="width: 60px;">#</th>
                <th>{{ __('cycles.Company') }}</th>
                <th>{{ __('cycles.Mentor') }}</th>
                <th>{{ __('cycles.Date/Time') }}</th>
                <th>{{ __('cycles.Duration') }}</th>
              </tr>
            </thead>
            <tbody id="mentorshipDurationTableBody"></tbody>
          </table>
        </div>

        <div class="text-center py-5 d-none" id="mentorshipDurationEmpty">
          <i class="ti ti-info-circle text-secondary display-4 d-block mb-3"></i>
          <p class="mb-0">{{ __('admin.No_Mentorship_Sessions') }}</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Close') }}</button>
      </div>
    </div>
  </div>
</div>
@endif

<div class="col-12 mt-6">
  <div class="card border">
    <div class="card-header d-md-flex justify-content-between border-bottom pb-3 mb-6">
      <h5 class="card-title mb-3 mb-md-0">{{ $l == 'en' ? 'Programs Overview' : 'نظرة عامة على البرامج' }}</h5>
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
              <th><small>{{ __('admin.Program') }}</small></th>
              <th><small>{{ __('admin.Organization') }}</small></th>
              <th><small>{{ __('admin.Start_Date') }}</small></th>
              <th><small>{{ __('admin.End_Date') }}</small></th>
              <th><small>{{ __('admin.Subscribed_Startups') }}</small></th>
              <th><small>{{ __('admin.Enrolled_Mentors') }}</small></th>
              <th><small>{{ __('admin.Mentorship_Requests') }}</small></th>
              <th><small>{{ __('admin.Mentorship_Sessions_Duration') }}</small></th>
              <th><small>{{ __('admin.Startups_Attendance_Rate') }}</small></th>
              <th><small>{{ __('admin.Training_Sessions') }}</small></th>
              <th><small>{{ __('admin.Training_Sessions_Duration') }} (h)</small></th>
              <th><small>{{ __('admin.First_Screening') }}</small></th>
              <th><small>{{ __('admin.Final_Screening') }}</small></th>
              <th><small>{{ __('admin.Actions') }}</small></th>
            </tr>
          </thead>
          <tbody>
            @foreach($cycles as $_cycle)
              @php($kpi = $cycles_kpis[$_cycle->id] ?? null)
              @if($kpi)
                <tr data-cycle-id="{{ $_cycle->id }}">
                  <td><small>{{ $_cycle->title }}</small></td>
                  <td><small>{{ $_cycle->organization->name ?? '-' }}</small></td>
                  <td><small>{{ $_cycle->start_format ?? '' }}</small></td>
                  <td><small>{{ $_cycle->end_format ?? '' }}</small></td>
                  <td><small>{{ $kpi['startups'] }}</small></td>
                  <td><small>{{ $kpi['mentors_count'] }}</small></td>
                  <td><small>{{ $kpi['mentorRequests_count'] }}</small></td>
                  <td><small>{{ $kpi['mentorships_period_formatted'] }}</small></td>
                  <td><small>{{ (int) ($kpi['mentorship_attendance_rate'] ?? 0) }}%</small></td>
                  <td><small>{{ $kpi['training_sessions_count'] }}</small></td>
                  <td><small>{{ number_format($kpi['training_sessions_period'], 1) }}</small></td>
                  <td><small>{{ $kpi['screening_sessions_count'] }}</small></td>
                  <td><small>{{ $kpi['final_screening_count'] }}</small></td>
                  <td>
                    <a href="/dashboard/programlist/report/{{ $_cycle->id }}" class="btn btn-sm btn-primary">
                      <i class="ti ti-eye me-1"></i>{{ __('admin.View_Report') }}
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
<script>
  $(document).ready(function() {
    // Initialize DataTable for Per Program KPIs
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
        'aria-label': 'Organization filter'
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

          // Remove previous custom filter
          $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
            return fn.toString().indexOf('tblCycleKPIs') === -1;
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
          
          // Remove custom date filters
          $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
            return fn.toString().indexOf('tblCycleKPIs') === -1;
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

    const mentorshipSessionsData = @json($cycles_sessions ?? []);
    const mentorshipCycles = @json(
      isset($cycles)
        ? $cycles->map(fn($cycle) => ['id' => $cycle->id, 'title' => $cycle->title])->values()
        : []
    );
    let selectedCycleIdForDuration = null;

    $(document).on('click', '.subscribed-startups-card', function (event) {
      event.preventDefault();
      const targetUrl = $(this).data('url');
      if (targetUrl) {
        window.location.href = targetUrl;
      }
    });

    $(document).on('click', '.mentorship-requests-card', function (event) {
      event.preventDefault();
      const targetUrl = $(this).data('url');
      if (targetUrl) {
        window.location.href = targetUrl;
      }
    });

    $(document).on('click', '.screening-startups-card', function (event) {
      event.preventDefault();
      const targetUrl = $(this).data('url');
      if (targetUrl) {
        window.location.href = targetUrl;
      }
    });

    $(document).on('click', '.final-screening-startups-card', function (event) {
      event.preventDefault();
      const targetUrl = $(this).data('url');
      if (targetUrl) {
        window.location.href = targetUrl;
      }
    });

    $(document).on('click', '.mentorship-duration-card', function (event) {
      event.preventDefault();
      const modalElement = document.getElementById('mentorshipDurationModal');
      if (!modalElement) {
        return;
      }

      const $select = $('#mentorshipDurationSelect');
      if ($select.length && !$select.hasClass('select2-hidden-accessible')) {
        $select.select2({
          dropdownParent: $('#mentorshipDurationModal'),
          width: '100%'
        });
      }

      const availableCycleIds = mentorshipCycles.map(cycle => String(cycle.id));
      const defaultCycleId = selectedCycleIdForDuration && availableCycleIds.includes(String(selectedCycleIdForDuration))
        ? String(selectedCycleIdForDuration)
        : (availableCycleIds[0] ?? '');

      if (defaultCycleId) {
        $select.val(defaultCycleId).trigger('change');
      } else {
        renderMentorshipSessions('');
      }

      const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
      modalInstance.show();
    });

    $('#mentorshipDurationSelect').on('change', function () {
      const cycleId = $(this).val();
      renderMentorshipSessions(cycleId);
    });

    $('#tblCycleKPIs tbody').on('click', 'tr', function () {
      const cycleId = $(this).data('cycle-id');
      if (!cycleId) {
        return;
      }
      selectedCycleIdForDuration = cycleId;
      $('#tblCycleKPIs tbody tr').removeClass('selected');
      $(this).addClass('selected');
    });

    function renderMentorshipSessions(cycleId) {
      const $tableWrapper = $('#mentorshipDurationTableWrapper');
      const $tbody = $('#mentorshipDurationTableBody');
      const $emptyState = $('#mentorshipDurationEmpty');

      $tbody.empty();

      if (!cycleId || !mentorshipSessionsData || !mentorshipSessionsData[cycleId] || mentorshipSessionsData[cycleId].length === 0) {
        $tableWrapper.addClass('d-none');
        $emptyState.removeClass('d-none');
        return;
      }

      $tableWrapper.removeClass('d-none');
      $emptyState.addClass('d-none');

      mentorshipSessionsData[cycleId].forEach((session, index) => {
        const $row = $('<tr/>');
        $row.append($('<td/>').text(index + 1));
        $row.append($('<td/>').text(session.startup || '-'));
        $row.append($('<td/>').text(session.mentor || '-'));
        $row.append($('<td/>').text(session.zoom_date || '-'));
        $row.append($('<td/>').text(session.duration_formatted || '00:00'));
        $tbody.append($row);
      });
    }
  });
</script>