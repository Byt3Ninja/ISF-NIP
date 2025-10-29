@extends('layouts.admin')

@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
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
              <li><a class="dropdown-item" href="javascript:void(0)" id="exportExcel"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
              <li><a class="dropdown-item" href="javascript:void(0)" id="exportCsv"><i class="ti ti-file-text me-2"></i>CSV</a></li>
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
          status: "{{ $company->status }}",
          company_id: "{{ $company->id }}",
          icon: "{{ $company->icon ?? '' }}"
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
              return `<span class=" badge ${badgeClass}">
                        <i class="${full.icon} me-2"></i> ${t[normalizedStatus] || data}
                      </span>`;
            }
          },
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
        dom: '<"row"<"col-12 col-md-8"l><"col-12 col-md-4 d-flex justify-content-center justify-content-md-end"f>><"d-none"B>t<"row"<"col-sm-12 col-md-4"i><"col-sm-12 col-md-4"p>>',
        buttons: [
        {
          extend: 'excel',
          title: t.screeningList,
          exportOptions: { columns: [0, 1, 2, 3] }
        },
        {
          extend: 'csv',
          title: t.screeningList,
          exportOptions: { columns: [0, 1, 2, 3] }
        },
      ],
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
          // Add custom filters for Program, Step, and Status
          
          // Program filter
          const programFilter = $(`<select class="form-select form-select-sm ms-md-12 me-3 w-px-150 mb-3 mt-3 mt-lg-0 mb-lg-0"><option value="">${t.allPrograms}</option></select>`)
            .appendTo('.dataTables_length')
            .on('change', function () {
              const val = $.fn.dataTable.util.escapeRegex($(this).val());
              dt_screening.column(0).search(val ? '^' + val + '$' : '', true, false).draw();
            });

          // Step filter
          const stepFilter = $(`<select class="form-select form-select-sm me-3 w-px-150 mb-3 mb-lg-0"><option value="">${t.allSteps}</option></select>`)
            .appendTo('.dataTables_length')
            .on('change', function () {
              const val = $.fn.dataTable.util.escapeRegex($(this).val());
              dt_screening.column(1).search(val ? '^' + val + '$' : '', true, false).draw();
            });

          // Status filter
          const statusFilter = $(`<select class="form-select form-select-sm w-px-150"><option value="">${t.allStatus}</option></select>`)
            .appendTo('.dataTables_length')
            .on('change', function () {
              const val = $.fn.dataTable.util.escapeRegex($(this).val());
              dt_screening.column(3).search(val ? '^' + val + '$' : '', true, false).draw();
            });

          // Populate program filter dynamically
          const programs = [...new Set(data.map(item => item.program_name))];
          programs.forEach(prog => {
            programFilter.append('<option value="' + prog + '">' + prog + '</option>');
          });

          // Populate step filter dynamically
          const steps = [...new Set(data.map(item => item.step_name))];
          steps.forEach(step => {
            stepFilter.append('<option value="' + step + '">' + step + '</option>');
          });

          // Populate status filter dynamically
          const statuses = [...new Set(data.map(item => item.status))];
          statuses.forEach(status => {
            // Normalize the status value to match what's in the data and for consistent filtering
            const normalizedStatus = status.toLowerCase().replace(/[-_\s]/g, '');
            const displayStatus = t[normalizedStatus] || status;
            // Use normalized status as value to match the filtering logic
            statusFilter.append('<option value="' + normalizedStatus + '">' + displayStatus + '</option>');
          });
        }
      });


      // Export button handlers
      $('#exportExcel').on('click', () => dt_screening.button('.buttons-excel').trigger());
      $('#exportCsv').on('click', () => dt_screening.button('.buttons-csv').trigger());
    }
  });
});
</script>
@stop