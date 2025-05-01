/**
 * DataTables Basic
 */

'use strict';

const lang = document.documentElement.lang;

// Translations for multilingual support
const translations = {
  en: {
    addNewForm: 'Add New Form',
    manageForms: 'Manage Forms',
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
  },
  ar: {
    addNewForm: 'اضافة نموذج جديد',
    manageForms: 'ادارة النماذج',
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
  }
};

const t = translations[lang] || translations.en;
let fv, offCanvasEl;

document.addEventListener('DOMContentLoaded', function (e) {
  // Your existing code...

  // datatable (jquery)
  $(function () {
    var dt_basic_table = $('.datatables-basic'),
      dt_basic;

    if (dt_basic_table.length) {
      const data = [
        {
          form_id: 1,
          form_name: "Employee Feedback",
          assigned_organization: "HR Department",
          submissions: 120,
          last_modified: "2023-01-15",
          created_date: "2022-12-01",
          status: t.active
        },
        {
          form_id: 2,
          form_name: "Customer Survey",
          assigned_organization: "Marketing Team",
          submissions: 85,
          last_modified: "2023-02-10",
          created_date: "2023-01-05",
          status: t.active
        },
        {
          form_id: 3,
          form_name: "Incident Report",
          assigned_organization: "Safety Department",
          submissions: 45,
          last_modified: "2023-03-20",
          created_date: "2023-02-15",
          status: t.inactive
        },
        {
          form_id: 4,
          form_name: "Training Feedback",
          assigned_organization: "Training Team",
          submissions: 200,
          last_modified: "2023-04-05",
          created_date: "2023-03-01",
          status: t.active
        },
        {
          form_id: 5,
          form_name: "Project Evaluation",
          assigned_organization: "Project Management",
          submissions: 60,
          last_modified: "2023-05-12",
          created_date: "2023-04-10",
          status: t.active
        }
      ];

      dt_basic = dt_basic_table.DataTable({
        data: data,
        columns: [
          { data: 'form_id', title: t.formId },
          { data: 'form_name', title: t.formName },
          { data: 'assigned_organization', title: t.assignedOrganization },
          { data: 'submissions', title: t.submissions },
          { data: 'last_modified', title: t.lastModified },
          { data: 'created_date', title: t.createdDate },
          { data: 'status', title: t.status },
          { data: null, title: t.actions, orderable: false, searchable: false }
        ],
        columnDefs: [
          {
            // Status Badge
            targets: 6,
            render: function (data, type, full, meta) {
              const statusClass = full.status === t.active ? 'bg-label-success' : 'bg-label-danger';
              return `<span class="badge ${statusClass}">${full.status}</span>`;
            }
          },
          {
            // Actions
            targets: 7,
            render: function (data, type, full, meta) {
              return (
                '<div class="d-inline-block">' +
                '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical ti-md"></i></a>' +
                '<ul class="dropdown-menu dropdown-menu-end m-0">' +
                `<li><a href="javascript:;" class="dropdown-item">${t.view}</a></li>` +
                `<li><a href="javascript:;" class="dropdown-item">${t.copy}</a></li>` +
                `<li><a href="javascript:;" class="dropdown-item">${t.edit}</a></li>` +
                '<div class="dropdown-divider"></div>' +
                `<li><a href="javascript:;" class="dropdown-item text-danger delete-record">${t.delete}</a></li>` +
                '</ul>' +
                '</div>' +
                '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon item-edit"><i class="ti ti-list-tree ti-md"></i></a>'
              );
            }
          }
        ],
        order: [[0, 'asc']],
        dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-6 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end mt-n6 mt-md-0"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
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
          {
            text: `<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">${t.addNewForm}</span>`,
            className: 'create-new btn btn-primary waves-effect waves-light d-flex'
          }
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
        }
      });

      $('div.head-label').html(`<h5 class="card-title mb-0">${t.manageForms}</h5>`);

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
            confirmButton: 'btn btn-primary me-3',
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

    setTimeout(() => {
      const newRecord = document.querySelector('.create-new'),
        offCanvasElement = document.querySelector('#add-new-record');

      // To open offCanvas, to add new record
      if (newRecord) {
        newRecord.addEventListener('click', function () {
          offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
          // Empty fields on offCanvas open
            (offCanvasElement.querySelector('.dt-form-name').value = ''),
            (offCanvasElement.querySelector('.dt-org-select').value = '');
          // Open offCanvas with form
          offCanvasEl.show();
        });
      }
    }, 200);

    // Your existing Add New record and Delete Record code...
  });
});
