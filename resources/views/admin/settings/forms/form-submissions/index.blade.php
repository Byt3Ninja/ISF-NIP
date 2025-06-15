@extends('layouts.admin')

@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Forms Submissions Table -->
    <div class="card">
      <div class="card-datatable table-responsive pt-0">
        <table class="datatables-basic table">
          <thead class="table-light">
            <tr>
              <th class="text-nowrap"></th>
              <th class="text-nowrap">Submission Id</th>
              <th class="text-nowrap">Submitted By</th>
              <th class="text-nowrap">Startup Name</th>
              <th class="text-nowrap">Organization Name</th>
              <th class="text-nowrap">Program Name</th>
              <th class="text-nowrap">Created At</th>
              <th class="text-nowrap">Step Name</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
    <!--/ Forms Submissions Table -->
  </div>
  <!--/ Content -->
</div>
<!-- / Content wrapper -->
@stop

@section('pagescripts')
<script>
  const lang = document.documentElement.lang;

// Translations for multilingual support
const translations = {
  en: {
    submissionsList: 'Submissions List',
    actions: 'Actions',
    search: 'Search',
    view: 'View',
    copy: 'Copy',
    edit: 'Edit',
    delete: 'Delete',
    formId: 'Form ID',
    formName: 'Form Name',
    assignedOrganization: 'Assigned Organization',
    submissions: 'Submissions',
    lastModified: 'Last Modified',
    createdDate: 'Created Date',
    status: 'Status',
    active: 'Active',
    inactive: 'Inactive',
    detailsOf: 'Details of',
    show: 'Show',
    entries: 'entries',
    showing: 'Showing',
    to: 'to',
    of: 'of',
    filtered: 'filtered',
    noMatchingRecords: 'No matching records found',
    noDataAvailable: 'No data available in table',
    deleteRecord: 'Delete Record',
    deleteConfirmation: 'Are you sure you want to delete this record?',
    ok: 'OK',
    cancel: 'Cancel',
    deleted: 'Deleted!',
    deleteConfirmation: 'Record has been deleted successfully.',
    submissionId: 'Submission Id',
    submittedBy: 'Submitted By',
    startupName: 'Startup Name',
    organizationName: 'Organization Name',
    programName: 'Program Name',
    createdAt: 'Created At',
    stepName: 'Step Name',
    allOrganizations: 'All Organizations',
    allPrograms: 'All Programs',
    allSteps: 'All Steps',
  },
  ar: {
    submissionsList: 'قائمة الإرسالات',
    actions: 'الإجراءات',
    search: 'بحث',
    view: 'عرض',
    copy: 'نسخ',
    edit: 'تعديل',
    delete: 'حذف',
    formId: 'معرف النموذج',
    formName: 'اسم النموذج',
    assignedOrganization: 'المنظمة المعينة',
    submissions: 'الإرسالات',
    lastModified: 'آخر تعديل',
    createdDate: 'تاريخ الإنشاء',
    status: 'الحالة',
    active: 'نشط',
    inactive: 'غير نشط',
    detailsOf: 'تفاصيل',
    show: 'عرض',
    entries: 'المدخلات',
    showing: 'عرض',
    to: 'إلى',
    of: 'من',
    filtered: 'مصفاة',
    noMatchingRecords: 'لا توجد سجلات مطابقة',
    noDataAvailable: 'لا توجد بيانات متاحة في الجدول',
    deleteRecord: 'حذف السجل',
    deleteConfirmation: 'هل أنت متأكد أنك تريد حذف هذا السجل؟',
    ok: 'حسنا',
    cancel: 'إلغاء',
    deleted: 'تم الحذف!',
    deleteConfirmation: 'تم حذف السجل بنجاح.',
    submissionId: 'معرف الإرسال',
    submittedBy: 'المقدم من',
    startupName: 'اسم الشركة الناشئة',
    organizationName: 'اسم المنظمة',
    programName: 'اسم البرنامج',
    createdAt: 'تاريخ الإنشاء',
    stepName: 'اسم المرحلة',
    allOrganizations: 'جميع المنظمات',
    allPrograms: 'جميع البرامج',
    allSteps: 'جميع الخطوات',
  }
};

const t = translations[lang] || translations.en;

document.addEventListener('DOMContentLoaded', function (e) {
  // Your existing code...

  // datatable (jquery)
  $(function () {
    var dt_basic_table = $('.datatables-basic'),
      dt_basic;
  
    if (dt_basic_table.length) {
      const data = {!! json_encode($data) !!};

      dt_basic = dt_basic_table.DataTable({
        data: data,
        columns: [
          { data: 'submission_id', title: t.submissionId },
          { data: 'submitted_by', title: t.submittedBy },
          { data: 'startup_name', title: t.startupName },
          { data: 'organization_name', title: t.organizationName },
          { data: 'program_name', title: t.programName },
          { data: 'created_at', title: t.createdAt },
          { data: 'step_name', title: t.stepName },
          { data: null, title: t.actions, orderable: false, searchable: false }
        ],
        columnDefs: [
          {
            // Actions
            targets: 7,
            render: function (data, type, full, meta) {
              return (
                `<a href="/dashboard/submission-details/${data.step_id}" class="btn btn-sm btn-label-primary item-view"><i class="ti ti-eye ti-md me-1"></i>${t.view}</a>`
              );
            }
          }
        ],
        order: [[0, 'asc']],
        dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-6 pt-md-0"B>><"row"<"col-12 col-md-8"l><"col-12 col-md-4 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-4"i><"col-sm-12 col-md-4"p>>',
        displayLength: 7,
        lengthMenu: [7, 10, 25, 50, 75, 100],
        language: {
          paginate: {
            next: '<i class="ti ti-chevron-right ti-sm"></i>',
            previous: '<i class="ti ti-chevron-left ti-sm"></i>'
          },
          search: `${t.search || 'Search'}:`,
          searchPlaceholder: `${t.search || 'Search'}...`,
          lengthMenu: `${t.show} <select class="form-select form-select-sm">` +
            `<option value="7">7</option>` +
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
        buttons: [
          // The "Add New Form" button object is removed from here
        ],
        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({
              header: function (row) {
                var data = row.data();
                return `${t.detailsOf} ${data['form_name']}`;
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
          $('.card-header').after('<hr class="my-0">');

            // Add custom filters for Organization and Program
            @if(auth()->user()->hasRole('OrganizationManager'))
            ''
            @else
            const orgFilter = $(`<select class="form-select form-select-sm ms-12 me-3 w-px-150"><option value="">${t.allOrganizations}</option></select>`)
            .appendTo('.dataTables_length')
            .on('change', function () {
              const val = $.fn.dataTable.util.escapeRegex($(this).val());
              dt_basic.column(3).search(val ? '^' + val + '$' : '', true, false).draw();
            });
            // Populate organization filter dynamically
            const organizations = [...new Set(data.map(item => item.organization_name))];
            organizations.forEach(org => {
              orgFilter.append('<option value="' + org + '">' + org + '</option>');
            });
            @endif

          // Program filter
          const programFilter = $(`<select class="form-select form-select-sm w-px-150"><option value="">${t.allPrograms}</option></select>`)
            .appendTo('.dataTables_length')
            .on('change', function () {
              const val = $.fn.dataTable.util.escapeRegex($(this).val());
              dt_basic.column(4).search(val ? '^' + val + '$' : '', true, false).draw();
            });

          // Step filter
          const stepFilter = $(`<select class="form-select form-select-sm w-px-150"><option value="">${t.allSteps}</option></select>`)
            .appendTo('.dataTables_length')
            .on('change', function () {
              const val = $.fn.dataTable.util.escapeRegex($(this).val());
              dt_basic.column(6).search(val ? '^' + val + '$' : '', true, false).draw();
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
        }
      });

      $('div.head-label').html(`<h5 class="card-title mb-0">${t.submissionsList}</h5>`);

      // Delete Record with SweetAlert warning
      $('.datatables-basic tbody').on('click', '.delete-record', function () {
        const row = $(this).parents('tr');
        Swal.fire({
          title: t.deleteRecord,
          text: t.deleteConfirmation,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: t.delete,
          cancelButtonText: t.cancel,
          customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-secondary'
          },
          buttonsStyling: false
        }).then((result) => {
          if (result.isConfirmed) {
            dt_basic.row(row).remove().draw();
            Swal.fire({
              title: t.deleted,
              text: t.deleteConfirmation,
              icon: 'success',
              showCancelButton: false,
              showConfirmButton: true,
              confirmButtonText: t.ok,
              customClass: {
                confirmButton: 'btn btn-primary'
              },
              buttonsStyling: false
            });
          }
        });
      });
    }
  });
});
</script>
@stop