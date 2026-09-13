@extends('layouts.admin')

@section('content')
<!-- Content wrapper -->
<div class="">
  <!-- Content -->
  <div class="">
    @include('judger.screening.partials.screening-header-card')
    
    <!-- Screening Table -->
    <div class="card">
      <div class="card-header border-bottom">
        <div class="d-flex flex-wrap align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ $l == 'en' ? 'Screening List' : 'قائمة الفحص' }}</h5>
          <div class="dropdown d-inline-block ms-2">
            <button class="btn btn-label-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="ti ti-file-export me-2"></i> {{ __('admin.Export') }}
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="javascript:void(0)" id="exportPdf"><i class="ti ti-file-type-pdf me-2"></i>{{ __('admin.PDF') }}</a></li>
              <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>{{ __('admin.Excel') }}</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="card-datatable table-responsive pt-0">
        <table class="datatables-screening table">
          <thead class="table-light">
            <tr>
              <th class="text-nowrap">{{ __('admin.Program_name') }}</th>
              <th class="text-nowrap">{{ __('admin.Step Name') }}</th>
              <th class="text-nowrap">{{ __('admin.Startup Name') }}</th>
              <th class="text-nowrap">{{ __('admin.Status') }}</th>
              {{-- Hidden filter-only columns; must exist so DataTables' column
                   count matches the columns[] definition below. (GV-3340) --}}
              <th class="text-nowrap">{{ __('admin.Program Type') }}</th>
              <th class="text-nowrap">{{ __('admin.Step Type') }}</th>
              <th class="text-nowrap">{{ __('admin.Actions') }}</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
    <!--/ Screening Table -->
  </div>
  <!--/ Content -->
</div>
<!-- / Content wrapper -->
@stop

@section('pagescripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
  const lang = document.documentElement.lang;

// Translations for multilingual support
const translations = {
  en: {
    screeningList: 'Screening List',
    actions: 'Actions',
    search: 'Search',
    view: 'View Details',
    programName: 'Program Name',
    stepName: 'Step Name',
    startupName: 'Startup Name',
    status: 'Status',
    detailsOf: 'Details of',
    show: 'Show',
    entries: 'entries',
    showing: 'Showing',
    to: 'to',
    of: 'of',
    filtered: 'filtered',
    noMatchingRecords: 'No matching records found',
    noDataAvailable: 'No data available in table',
    allPrograms: 'All Programs',
    allSteps: 'All Steps',
    allStatus: 'All Status',
    pending: 'Pending',
    inProgress: 'In Progress',
    completed: 'Completed',
    notStarted: 'Not Started',
    export: 'Export',
    notstarted: 'Not Started',
    inprogress: 'In Progress',
    completed: 'Completed',
    programType: 'Program Type',
    stepType: 'Step Type',
    allProgramTypes: 'All Program Types',
    allStepTypes: 'All Step Types',
    // Step-type filter labels. The option VALUE stays the raw 'initial'/'final'
    // so search and the server-side export keep matching. (GV-9492)
    initial: 'Initial Screening',
    final: 'Final Screening',
    noRowsToExport: 'There are no rows to export with the current filters.',
    reset: 'Reset',
    criteriaNotConfiguredTitle: "{{ __('admin.criteria_not_configured_title') }}",
    criteriaNotConfiguredMessage: "{{ __('admin.criteria_not_configured_message') }}",
  },
  ar: {
    screeningList: 'قائمة الفحص',
    actions: 'الإجراءات',
    search: 'بحث',
    view: 'عرض التفاصيل',
    programName: 'اسم البرنامج',
    stepName: 'اسم  المرحلة',
    startupName: 'اسم الشركة الناشئة',
    status: 'الحالة',
    detailsOf: 'تفاصيل',
    show: 'عرض',
    entries: 'المدخلات',
    showing: 'عرض',
    to: 'إلى',
    of: 'من',
    filtered: 'مصفاة',
    noMatchingRecords: 'لا توجد سجلات مطابقة',
    noDataAvailable: 'لا توجد بيانات متاحة في الجدول',
    allPrograms: 'جميع البرامج',
    allSteps: 'جميع المراحل',
    allStatus: 'جميع الحالات',
    pending: 'معلق',
    inProgress: 'قيد التنفيذ',
    completed: 'مكتمل',
    notStarted: 'لم تبدأ',
    export: 'تصدير',
    notstarted: 'لم تبدأ',
    inprogress: 'قيد التنفيذ',
    completed: 'مكتمل',
    programType: 'نوع البرنامج',
    stepType: 'نوع المرحلة',
    allProgramTypes: 'جميع أنواع البرامج',
    allStepTypes: 'جميع أنواع المراحل',
    initial: 'التقييم الأولي',
    final: 'التقييم النهائي',
    noRowsToExport: 'لا توجد صفوف للتصدير بالفلاتر الحالية.',
    reset: 'إعادة تعيين',
    criteriaNotConfiguredTitle: "{{ __('admin.criteria_not_configured_title') }}",
    criteriaNotConfiguredMessage: "{{ __('admin.criteria_not_configured_message') }}",
  }
};

const t = translations[lang] || translations.en;

document.addEventListener('DOMContentLoaded', function (e) {
  // datatable (jquery)
  $(function () {
    var dt_screening_table = $('.datatables-screening'),
      dt_screening;
  
    if (dt_screening_table.length) {
      const data = [
        @foreach($companies as $company)
        {
          program_name: "{{ $company->program->name }}",
          step_name: "{{ $company->step->name }}",
          startup_name: "{{ $company->name }}",
          startup_logo: "{{ $company->logo ?? '/images/logo-placeholder.png' }}",
          program_type: "{{ $company->program->type ?? '' }}",
          step_type: "{{ $company->step->type ?? '' }}",
          status: "{{ $company->status }}",
          company_id: "{{ $company->id }}",
          icon: "{{ $company->icon ?? '' }}",
          evaluatable: {{ ($company->evaluatable ?? false) ? 'true' : 'false' }}
        },
        @endforeach
      ];

      dt_screening = dt_screening_table.DataTable({
        data: data,
        columns: [
          { data: 'program_name', title: t.programName },
          { data: 'step_name', title: t.stepName },
          { 
            data: 'startup_name', 
            title: t.startupName,
            render: function (data, type, full, meta) {
              return `<div class="d-flex align-items-center">
                        <img src="${full.startup_logo}" 
                             onerror="this.src='/images/logo-placeholder.png'"
                             class="rounded-circle me-2" 
                             width="32" height="32" alt="Company Logo">
                        <span>${data}</span>
                      </div>`;
            }
          },
          { 
            data: 'status', 
            title: t.status,
            render: function (data, type, full, meta) {
              // For filtering, return the normalized status
              if (type === 'filter' || type === 'type') {
                return data.toLowerCase().replace(/[-_\s]/g, '');
              }
              
              let badgeClass = 'bg-label-secondary';
              if (data === 'NotStarted') badgeClass = 'bg-label-secondary';
              else if (data === 'in_progress') badgeClass = 'bg-label-warning';
              else if (data === 'completed') badgeClass = 'bg-label-success';
              
              // Normalize status for translation lookup
              const normalizedStatus = data.toLowerCase().replace(/[-_\s]/g, '');
              // When the evaluation form isn't ready (not published or no
              // criteria) show a clickable warning icon instead of the gavel.
              const iconHtml = full.evaluatable
                ? `<i class="${full.icon} me-2"></i>`
                : `<i class="${full.icon} me-2 criteria-warning text-warning" role="button" title="${t.criteriaNotConfiguredTitle}"></i>`;
              return `<span class=" badge ${badgeClass}">
                        ${iconHtml} ${t[normalizedStatus] || data}
                      </span>`;
            }
          },
          { data: 'program_type', title: t.programType, visible: false },
          { data: 'step_type', title: t.stepType, visible: false },
          { 
            data: null, 
            title: t.actions, 
            orderable: false, 
            searchable: false,
            render: function (data, type, full, meta) {
              return `<a href="/dashboard/companyProfile/${data.company_id}" class="btn btn-sm btn-primary">
                        <i class="ti ti-eye me-1"></i>${t.view}
                      </a>`;
            }
          }
        ],
        order: [[0, 'asc']],
        // Row 1: page length + search.
        // Row 2: filters (own region).
        // Footer: info left, pagination hard right.
        dom:
          '<"row align-items-center px-3 pb-2"' +
            '<"col-12 col-md-6 mb-2 mb-md-0"l>' +
            '<"col-12 col-md-6 d-flex justify-content-md-end pe-8"f>' +
          '>' +
          '<"screening-filters-region px-4 pt-2 pb-4">' +
          't' +
          '<"row align-items-center px-4 py-3"' +
            '<"col-12 col-md-6 mb-2 mb-md-0"i>' +
            '<"col-12 col-md-6 d-flex justify-content-md-end"p>' +
          '>',
        displayLength: 10,
        lengthMenu: [10, 25, 50, 75, 100],
        language: {
          paginate: {
            next: '<i class="ti ti-chevron-right ti-sm"></i>',
            previous: '<i class="ti ti-chevron-left ti-sm"></i>'
          },
          search: `${t.search}:`,
          searchPlaceholder: `${t.search}...`,
          lengthMenu: `${t.show} <select class="form-select form-select-sm">` +
            `<option value="10">10</option>` +
            `<option value="25">25</option>` +
            `<option value="50">50</option>` +
            `<option value="75">75</option>` +
            `<option value="100">100</option>` +
            '</select> ' + t.entries,
          info: `${t.showing} <span class="text-primary">_START_</span> ${t.to} <span class="text-primary">_END_</span> ${t.of} <span class="text-primary">_TOTAL_</span> ${t.entries}`,
          infoEmpty: `${t.showing} 0 ${t.to} 0 ${t.of} 0 ${t.entries}`,
          infoFiltered: `(${t.filtered} ${t.of} _MAX_ ${t.entries})`,
          zeroRecords: `${t.noMatchingRecords}`,
          emptyTable: `${t.noDataAvailable}`,
        },
        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({
              header: function (row) {
                var data = row.data();
                return `${t.detailsOf} ${data['startup_name']}`;
              }
            }),
            type: 'column',
            renderer: function (api, rowIdx, columns) {
              var data = $.map(columns, function (col, i) {
                return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                  ? '<tr data-dt-row="' +
                  col.rowIndex +
                  '" data-dt-column="' +
                  col.columnIndex +
                  '">' +
                  '<td>' +
                  col.title +
                  ':' +
                  '</td> ' +
                  '<td>' +
                  col.data +
                  '</td>' +
                  '</tr>'
                  : '';
              }).join('');

              return data ? $('<table class="table"/><tbody />').append(data) : false;
            }
          }
        },
        initComplete: function (settings, json) {
          // Filter bar. The five controls live in their own labelled grid row
          // rather than being appended into .dataTables_length, which is an
          // inline-flow container and wrapped them raggedly. (GV-3340)
          const filterBar = $('<div class="screening-filters row g-3 align-items-end"></div>')
            .appendTo('.screening-filters-region');

          // Build one labelled select. `exact` anchors the regex so e.g.
          // "Screening" does not also match "Final Screening".
          const addFilter = (colIdx, label, placeholder, exact = true) => {
            const cell = $(
              '<div class="col-6 col-md-4 col-xl mt-0">' +
                '<label class="form-label text-muted small mb-1">' + label + '</label>' +
              '</div>'
            ).appendTo(filterBar);

            return $('<select class="form-select"><option value="">' + placeholder + '</option></select>')
              .appendTo(cell)
              .on('change', function () {
                const val = $.fn.dataTable.util.escapeRegex($(this).val());
                dt_screening.column(colIdx)
                  .search(val ? (exact ? '^' + val + '$' : val) : '', true, false)
                  .draw();
                syncReset();
              });
          };

          const programFilter     = addFilter(0, t.programName, t.allPrograms);
          const stepFilter        = addFilter(1, t.stepName,    t.allSteps);
          const programTypeFilter = addFilter(4, t.programType, t.allProgramTypes);
          const stepTypeFilter    = addFilter(5, t.stepType,    t.allStepTypes);
          const statusFilter      = addFilter(3, t.status,      t.allStatus);

          const allFilters = [programFilter, stepFilter, programTypeFilter, stepTypeFilter, statusFilter];

          // Reset only appears once something is actually filtered, so the
          // default state stays quiet.
          const resetCell = $('<div class="col-12 col-xl-auto"></div>').appendTo(filterBar);
          const resetBtn = $(
            '<button type="button" class="btn btn-label-secondary w-100 d-none">' +
              '<i class="ti ti-rotate-2 me-1"></i>' + t.reset +
            '</button>'
          ).appendTo(resetCell).on('click', function () {
            allFilters.forEach(f => f.val(''));
            dt_screening.columns([0, 1, 3, 4, 5]).search('').draw();
            syncReset();
          });

          const syncReset = () => {
            const active = allFilters.some(f => f.val());
            resetBtn.toggleClass('d-none', !active);
          };

          // Populate each filter from the rows actually present.
          const fill = (select, values, labelFor = (v) => v) => {
            [...new Set(values.filter(Boolean))].sort().forEach(v => {
              select.append($('<option></option>').val(v).text(labelFor(v)));
            });
          };

          fill(programFilter,     data.map(i => i.program_name));
          fill(stepFilter,        data.map(i => i.step_name));
          fill(programTypeFilter, data.map(i => i.program_type), v => v.replace(/_/g, ' '));
          fill(stepTypeFilter,    data.map(i => i.step_type),    v => t[v] || v);
          fill(
            statusFilter,
            data.map(i => i.status.toLowerCase().replace(/[-_\s]/g, '')),
            v => t[v] || v
          );

          // Expose the live filter values so the server-side export can mirror
          // exactly what the judge is currently looking at. (GV-3340)
          window.judgerScreeningFilters = () => ({
            program: programFilter.val() || '',
            program_type: programTypeFilter.val() || '',
            step: stepFilter.val() || '',
            step_type: stepTypeFilter.val() || '',
            status: statusFilter.val() || '',
            search: dt_screening.search() || '',
          });
        }
      });


      // Warning icon: inform the judge the criteria aren't configured yet.
      $('.datatables-screening tbody').on('click', '.criteria-warning', function () {
        if (typeof window.releaseWarningAlert === 'function') {
          window.releaseWarningAlert({
            title: t.criteriaNotConfiguredTitle,
            message: t.criteriaNotConfiguredMessage,
          });
        }
      });

      // Export button handlers. The export is rendered server-side because it
      // carries the full evaluation hierarchy, not just the visible columns.
      const runExport = (format) => {
        if (dt_screening.rows({ search: 'applied' }).count() === 0) {
          if (typeof window.releaseWarningAlert === 'function') {
            window.releaseWarningAlert({ title: t.export, message: t.noRowsToExport });
          }
          return;
        }
        const filters = typeof window.judgerScreeningFilters === 'function'
          ? window.judgerScreeningFilters()
          : {};
        const params = new URLSearchParams({ ...filters, format });
        window.location.href = `{{ url('dashboard/judger/screening/export') }}?${params.toString()}`;
      };

      $('#exportPdf').on('click', () => runExport('pdf'));
      $('#exportExcel').on('click', () => runExport('excel'));
    }
  });
});
</script>
@stop