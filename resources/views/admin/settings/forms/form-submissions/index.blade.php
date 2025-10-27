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

    $(document).on('click', '.export-form-btn', function(e) {
      e.preventDefault();
      let type = $(this).data('type');
      var url = "{{ URL::to('dashboard/forms-submissions/export') }}/" + type + '?';
      var params = 'organization=' + encodeURIComponent($("#orgFilter").val().replace(/&$/, '')) || '';
      params += '&program=' + encodeURIComponent($("#programFilter").val().replace(/&$/, '')) || '';
      params += '&step=' + encodeURIComponent($("#stepFilter").val().replace(/&$/, '')) || '';
      params += '&search=' + encodeURIComponent($("#searchFilter").val().replace(/&$/, '')) || '';

      url += params;
      window.open(url, '_blank');
    });

    const t = translations[lang] || translations.en;

    // Function to format submitted by users
    function formatSubmittedBy(users, maxVisible = 3) {
      if (!users || users.trim() === '') return '';
      
      const userList = users.split(',').map(user => user.trim()).filter(user => user.length > 0);
      
      if (userList.length === 0) return '';
      
      if (userList.length <= maxVisible) {
        return userList.map((user, index) => 
          `<span class="badge ${index === 0 ? 'bg-label-primary' : 'bg-light text-dark'} me-1 mb-1">${user}</span>`
        ).join('');
      }
      
      const visibleUsers = userList.slice(0, maxVisible);
      const hiddenUsers = userList.slice(maxVisible);
      
      let html = visibleUsers.map((user, index) => 
        `<span class="badge ${index === 0 ? 'bg-label-primary' : 'bg-light text-dark'} me-1 mb-1">${user}</span>`
      ).join('');
      
      html += `<span class="badge bg-secondary me-1 mb-1">+${hiddenUsers.length}</span>`;
      html += `<button class="btn btn-link p-0 text-decoration-underline" style="font-size: 0.75rem;" onclick="showAllUsers(this, '${users.replace(/'/g, "\\'")}')">Show All</button>`;

      return `<div class="d-flex flex-wrap align-items-center" style="width: 200px; max-height: 80px; overflow-y: auto;">${html}</div>`;
    }

    // Function to show all users in a modal or expanded view
    function showAllUsers(button, allUsers) {
      const userList = allUsers.split(',').map(user => user.trim()).filter(user => user.length > 0);
      
      const modalContent = userList.map((user, index) => 
        `<span class="badge ${index === 0 ? 'bg-label-primary' : 'bg-light text-dark'} me-2 mb-2">${user}</span>`
      ).join('');
      
      Swal.fire({
        title: t.submittedBy || 'Submitted By',
        html: `<div class="d-flex flex-wrap justify-content-center">${modalContent}</div>`,
        icon: 'info',
        confirmButtonText: t.ok || 'OK',
        customClass: {
          confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false,
        width: '600px'
      });
    }

    document.addEventListener('DOMContentLoaded', function (e) {
      // Your existing code...

      // datatable (jquery)
      $(function () {
        var dt_basic_table = $('.datatables-basic'),
          dt_basic;
      
        if (dt_basic_table.length) {
          const data = {!! json_encode($data) !!};

          console.log('data', data)

          dt_basic = dt_basic_table.DataTable({
            data: data,
            columns: [
              { data: 'submission_id', title: t.submissionId, responsivePriority: 1 },
              { data: 'submitted_by', title: t.submittedBy, responsivePriority: 1 },
              { data: 'startup_name', title: t.startupName, responsivePriority: 1 },
              { data: 'organization_name', title: t.organizationName, responsivePriority: 1 },
              { data: 'program_name', title: t.programName, responsivePriority: 1 },
              { data: 'created_at', title: t.createdAt, responsivePriority: 1 },
              { data: 'step_name', title: t.stepName, responsivePriority: 1 },
              { data: null, title: t.actions, orderable: false, searchable: false, responsivePriority: 1 }
            ],
            columnDefs: [
              {
                // Submitted By column formatting
                targets: 1,
                render: function (data, type, full, meta) {
                  if (type === 'display') {
                    return formatSubmittedBy(data);
                  }
                  return data;
                }
              },
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
            dom: '<"card-header d-flex justify-content-between"<"head-label"><"dt-action-buttons text-end pt-md-0"B>><"row"<"col-12 col-md-8"l><"col-12 col-md-4 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-4"i><"col-sm-12 col-md-4"p>>',
            displayLength: 7,
            lengthMenu: [7, 10, 25, 50, 75, 100],
            language: {
              paginate: {
                next: '<i class="ti ti-chevron-right ti-sm"></i>',
                previous: '<i class="ti ti-chevron-left ti-sm"></i>'
              },
              search: `${t.search || 'Search'}:`,
              searchPlaceholder: `${t.search || 'Search'}...`,
              lengthMenu: `${t.show} <select class="form-select form-select-sm mb-3 mb-md-0">` +
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
            initComplete: function (settings, json) {
              $('.card-header').after('<hr class="my-0">');
              var tableId = this.api().table().node().id;
              $('#' + tableId + '_filter input').attr('id', 'searchFilter');
                // Add custom filters for Organization and Program
                @if(auth()->user()->hasRole('OrganizationManager'))
                ''
                @else
                const orgFilter = $(`<select id="orgFilter" class="form-select form-select-sm ms-md-12 me-md-3 w-px-150 mb-3 mb-md-0"><option value="">${t.allOrganizations}</option></select>`)
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
              const programFilter = $(`<select id="programFilter" class="form-select form-select-sm w-px-150 mb-3 mb-md-0"><option value="">${t.allPrograms}</option></select>`)
                .appendTo('.dataTables_length')
                .on('change', function () {
                  const val = $.fn.dataTable.util.escapeRegex($(this).val());
                  dt_basic.column(4).search(val ? '^' + val + '$' : '', true, false).draw();
                });

              // Step filter
              const stepFilter = $(`<select id="stepFilter" class="form-select form-select-sm w-px-150 mb-md-0"><option value="">${t.allSteps}</option></select>`)
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

          const headerContent = `
            <h5 class="card-title mb-0">${t.submissionsList}</h5>
          `;

          $('div.head-label').html(`${headerContent}`);

          const dropdownContent = `
            <div class="dropdown">
              <a class="btn btn-label-secondary dropdown-toggle" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-file-export me-2"></i> {{ $l == 'en' ? 'Export' : 'تصدير' }}</a>
              <ul class="dropdown-menu">
                <li><a data-type="Excel" class="dropdown-item export-form-btn" target="_blank" href="javascript:void(0)"><i class="fas fa-fw fa-sm fa-file-excel me-2"></i> Excel</a></li>
                <li><a data-type="CSV" class="dropdown-item export-form-btn" target="_blank" href="javascript:void(0)"><i class="fas fa-fw fa-sm fa-file-csv me-2"></i> CSV</a></li>
              </ul>
            </div>
          `;

          $('div.dt-action-buttons').html(`${dropdownContent}`);

          // Delete Record with SweetAlert warning
          $('.datatables-basic tbody').on('click', '.delete-record', function () {
            const row = $(this).parents('tr');
            Swal.fire({
              title: t.deleteRecord,
              text: t.deleteConfirmation,
              icon: 'info',
              showCancelButton: true,
              confirmButtonText: t.delete,
              cancelButtonText: t.cancel,
              customClass: {
                confirmButton: 'btn btn-danger me-3',
                cancelButton: 'btn btn-label-secondary'
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