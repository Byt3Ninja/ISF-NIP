/**
 * DataTables Basic
 */

'use strict';

const lang = document.documentElement.lang;

// Translations for multilingual support
const translations = {
  en: {
    addNewForm: 'Add New Form',
    editForm: 'Edit Form',
    updateForm: 'Update Form',
    manageForms: 'Manage Forms',
    actions: 'Actions',
    search: 'Search',
    view: 'View',
    copy: 'Copy',
    edit: 'Edit',
    delete: 'Delete',
    formId: 'Form ID',
    formName: 'Form Title',
    assignedOrganization: 'Assigned Organization',
    submissions: 'Submissions',
    lastModified: 'Last Modified',
    createdDate: 'Created Date',
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
    allOrganizations: 'All Organizations',
  },
  ar: {
    addNewForm: 'اضافة نموذج جديد',
    editForm: 'تعديل النموذج',
    updateForm: 'تحديث النموذج',
    manageForms: 'ادارة النماذج',
    actions: 'الإجراءات',
    search: 'بحث',
    view: 'عرض',
    copy: 'نسخ',
    edit: 'تعديل',
    delete: 'حذف',
    formId: 'معرف النموذج',
    formName: 'عنوان النموذج',
    assignedOrganization: 'المنظمة المعينة',
    submissions: 'الإرسالات',
    lastModified: 'آخر تعديل',
    createdDate: 'تاريخ الإنشاء',
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
    allOrganizations: 'جميع المنظمات',
  }
};

const t = translations[lang] || translations.en;
let fv, offCanvasEl, editOffCanvasEl;
let currentEditingRow = null;

document.addEventListener('DOMContentLoaded', function (e) {
  // Your existing code...

  // datatable (jquery)
  $(function () {
    var dt_basic_table = $('.datatables-basic'),
      dt_basic;

    if (dt_basic_table.length) {
      
      const data = formListData ? formListData : [];

      dt_basic = dt_basic_table.DataTable({
        data: data,
        columns: [
          { data: 'form_id', title: t.formId },
          { data: 'form_name', title: t.formName },
          { data: 'assigned_organization', title: t.assignedOrganization },
          { data: 'submissions', title: t.submissions },
          { data: 'last_modified', title: t.lastModified },
          { data: 'created_date', title: t.createdDate },
          { data: null, title: t.actions, orderable: false, searchable: false }
        ],
        columnDefs: [
          {
            // Actions
            targets: 6,
            render: function (data, type, full, meta) {
              return (
                '<div class="d-inline-block">' +
                '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical ti-md"></i></a>' +
                '<ul class="dropdown-menu dropdown-menu-end m-0">' +
                `<li><a href="/dashboard/show-form/${full.form_id}" class="dropdown-item">${t.view}</a></li>` +
                // `<li><a href="javascript:;" class="dropdown-item">${t.copy}</a></li>` +
                `<li><a href="javascript:;" class="dropdown-item edit-record">${t.edit}</a></li>` +
                // '<div class="dropdown-divider"></div>' +
                // `<li><a href="javascript:;" class="dropdown-item text-danger delete-record">${t.delete}</a></li>` +
                '</ul>' +
                '</div>' +
                '<a href="/dashboard/forms-submissions" class="btn btn-sm btn-text-secondary rounded-pill btn-icon item-edit"><i class="ti ti-list-tree ti-md"></i></a>'
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
            text: `<i class="ti ti-plus me-sm-1"></i> <span class="">${t.addNewForm}</span>`,
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

          // Add custom filter for Organization only (status filter removed)
          const orgFilter = $(`<select class="form-select form-select-sm w-px-200 mt-6 mt-md-0"><option value="">${t.allOrganizations}</option></select>`)
            .appendTo('.dataTables_length')
            .on('change', function () {
              const val = $.fn.dataTable.util.escapeRegex($(this).val());
              dt_basic.column(2).search(val ? '^' + val + '$' : '', true, false).draw();
            });

          // Populate organization filter dynamically
          const organizations = [...new Set(data.map(item => item.assigned_organization))];
          organizations.forEach(org => {
            orgFilter.append('<option value="' + org + '">' + org + '</option>');
          });
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
              confirmButtonText: t.ok,
              customClass: {
                confirmButton: 'btn btn-primary'
              },
              buttonsStyling: false
            });
          }
        });
      });

      // Edit Record
      $('.datatables-basic tbody').on('click', '.edit-record', function () {
        const row = $(this).closest('tr');
        const rowData = dt_basic.row(row).data();

        console.log('Editing row data:', rowData);
        currentEditingRow = row;
        
        // Get the edit form element
        const editOffCanvasElement = document.querySelector('#edit-record');
        
        if (editOffCanvasElement) {
          // Populate form with existing data
          editOffCanvasElement.querySelector('.form_id').value = rowData.form_id;
          editOffCanvasElement.querySelector('.dt-form-name').value = rowData.form_name;
          
          // Set organization by ID if available, otherwise by name
          const orgSelect = $(editOffCanvasElement.querySelector('.dt-org-select'));
          if (rowData.organization_id) {
            orgSelect.val(rowData.organization_id).trigger('change');
          } else {
            // Fallback: find option by organization name
            const options = orgSelect.find('option');
            options.each(function() {
              if ($(this).text().trim() === rowData.assigned_organization) {
                orgSelect.val($(this).val()).trigger('change');
                return false;
              }
            });
          }
          
          // Show the edit canvas
          editOffCanvasEl = new bootstrap.Offcanvas(editOffCanvasElement);
          editOffCanvasEl.show();
        }
      });
    }

    setTimeout(() => {
      const newRecord = document.querySelector('.create-new'),
        offCanvasElement = document.querySelector('#add-new-record'),
        editCanvasElement = document.querySelector('#edit-record');

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
      
      // Handle edit form submission
      if (editCanvasElement) {
        const editForm = editCanvasElement.querySelector('form');
        if (editForm) {
          editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            
            if (currentEditingRow) {
              const formName = editCanvasElement.querySelector('.dt-form-name').value;
              // const organization = editCanvasElement.querySelector('.dt-org-select').value;
              const selectElement = editCanvasElement.querySelector('.dt-org-select');
              const selectedOption = selectElement.options[selectElement.selectedIndex];
              const organization = selectedOption.getAttribute('data-value');
              
              // Get the current data
              const rowData = dt_basic.row(currentEditingRow).data();
              
              // Update with new values
              rowData.form_name = formName;
              rowData.assigned_organization = organization;
              rowData.last_modified = new Date().toISOString().split('T')[0]; // Update modification date
              
              // Redraw the row with updated data
              dt_basic.row(currentEditingRow).data(rowData).draw();
              
              // Close the offcanvas
              editOffCanvasEl.hide();
              
              // Reset current editing row
              currentEditingRow = null;
            }
          });
        }
      }
    }, 200);

    // Your existing Add New record and Delete Record code...
  });
});
