@extends('layouts.admin')

@section('vendorStyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
@stop

@section('content')
<!-- Content wrapper -->
<div class="" dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}">
  <!-- Content -->
  <div class="">    
    <!-- Navigation Tabs -->
    <ul class="nav nav-pills flex-nowrap overflow-x-auto mb-6">
      <li class="nav-item">
        <a class="nav-link active" href="javascript:void(0);" data-bs-toggle="pill" data-bs-target="#areas-of-expertise">
          <i class="ti ti-brain ti-sm me-1_5"></i>
          <span class="align-middle">{{ $l == 'ar' ? 'مجالات الخبرة' : 'Areas of Expertise' }}</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="javascript:void(0);" data-bs-toggle="pill" data-bs-target="#specializations">
          <i class="ti ti-certificate ti-sm me-1_5"></i>
          <span class="align-middle">{{ $l == 'ar' ? 'التخصصات' : 'Specializations' }}</span>
        </a>
      </li>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content p-0">
      <!-- Areas of Expertise Tab -->
      <div class="tab-pane fade show active" id="areas-of-expertise" role="tabpanel">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <h5 class="card-title m-0">{{ $l == 'ar' ? 'مجالات الخبرة' : 'Areas of Expertise' }}</h5>
            <div class="d-flex gap-2">
              <input type="file" id="expertise-import-file" accept=".json" style="display: none;">
              <button
                class="btn btn-label-secondary"
                type="button"
                onclick="document.getElementById('expertise-import-file').click()">
                <i class="ti ti-upload me-0 me-sm-1 ti-xs"></i>
                <span class="d-none d-sm-inline-block">{{ $l == 'ar' ? 'استيراد JSON' : 'Import JSON' }}</span>
              </button>
              <button
                class="btn btn-primary"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasAddExpertise"
                aria-controls="offcanvasAddExpertise">
                <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i>
                <span class="d-none d-sm-inline-block">{{ $l == 'ar' ? 'إضافة خبرة' : 'Add Expertise' }}</span>
              </button>
            </div>
          </div>
          <div class="card-datatable table-responsive">
            <table class="datatables-expertise-list table border-top">
              <thead>
                <tr>
                  <th></th>
                  <th>{{ $l == 'ar' ? 'المعرف' : 'ID' }}</th>
                  <th>{{ $l == 'ar' ? 'الاسم' : 'Name' }}</th>
                  <th>{{ $l == 'ar' ? 'الوصف' : 'Description' }}</th>
                  <th>{{ $l == 'ar' ? 'التخصصات' : 'Specializations' }}</th>
                  <th>{{ $l == 'ar' ? 'الحالة' : 'Status' }}</th>
                  <th>{{ $l == 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}</th>
                  <th>{{ $l == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>

      <!-- Specializations Tab -->
      <div class="tab-pane fade" id="specializations" role="tabpanel">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <h5 class="card-title m-0">{{ $l == 'ar' ? 'التخصصات' : 'Specializations' }}</h5>
            <div class="d-flex gap-2">
              <input type="file" id="specialization-import-file" accept=".json" style="display: none;">
              <button
                class="btn btn-label-secondary"
                type="button"
                onclick="document.getElementById('specialization-import-file').click()">
                <i class="ti ti-upload me-0 me-sm-1 ti-xs"></i>
                <span class="d-none d-sm-inline-block">{{ $l == 'ar' ? 'استيراد JSON' : 'Import JSON' }}</span>
              </button>
              <button
                class="btn btn-primary"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasAddSpecialization"
                aria-controls="offcanvasAddSpecialization">
                <i class="ti ti-plus me-0 me-sm-1 ti-xs"></i>
                <span class="d-none d-sm-inline-block">{{ $l == 'ar' ? 'إضافة تخصص' : 'Add Specialization' }}</span>
              </button>
            </div>
          </div>
          <div class="card-datatable table-responsive">
            <table class="datatables-specialization-list table border-top">
              <thead>
                <tr>
                  <th></th>
                  <th>{{ $l == 'ar' ? 'المعرف' : 'ID' }}</th>
                  <th>{{ $l == 'ar' ? 'الاسم' : 'Name' }}</th>
                  <th>{{ $l == 'ar' ? 'مجال الخبرة' : 'Area of Expertise' }}</th>
                  <th>{{ $l == 'ar' ? 'الوصف' : 'Description' }}</th>
                  <th>{{ $l == 'ar' ? 'الحالة' : 'Status' }}</th>
                  <th>{{ $l == 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}</th>
                  <th>{{ $l == 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- / Content -->

  <div class="content-backdrop fade"></div>
</div>
<!-- Content wrapper -->

<!-- Offcanvas to add new expertise -->
<div
  class="offcanvas {{ $l == 'ar' ? 'offcanvas-start' : 'offcanvas-end' }}"
  tabindex="-1"
  id="offcanvasAddExpertise"
  aria-labelledby="offcanvasAddExpertiseLabel">
  <div class="offcanvas-header py-6">
    <h5 id="offcanvasAddExpertiseLabel" class="offcanvas-title">{{ $l == 'ar' ? 'إضافة مجال خبرة' : 'Add Area of Expertise' }}</h5>
    <button
      type="button"
      class="btn-close text-reset"
      data-bs-dismiss="offcanvas"
      aria-label="Close"></button>
  </div>
  <div class="offcanvas-body border-top">
    <form class="pt-0" id="expertiseForm">
      <div class="mb-6">
        <label class="form-label" for="expertise-name">{{ $l == 'ar' ? 'الاسم' : 'Name' }}</label>
        <input
          type="text"
          class="form-control"
          id="expertise-name"
          placeholder="{{ $l == 'ar' ? 'أدخل اسم الخبرة' : 'Enter expertise name' }}"
          name="expertiseName"
          required />
      </div>
      <div class="mb-6">
        <label class="form-label" for="expertise-description">{{ $l == 'ar' ? 'الوصف' : 'Description' }}</label>
        <textarea
          class="form-control"
          id="expertise-description"
          rows="3"
          placeholder="{{ $l == 'ar' ? 'أدخل الوصف' : 'Enter description' }}"
          name="expertiseDescription"
          ></textarea>
      </div>
      <div class="mb-6">
        <label class="form-label">{{ $l == 'ar' ? 'الحالة' : 'Status' }}</label>
        <select id="expertise-status" class="select2 form-select" name="expertiseStatus">
          <option value="active">{{ $l == 'ar' ? 'نشط' : 'Active' }}</option>
          <option value="inactive">{{ $l == 'ar' ? 'غير نشط' : 'Inactive' }}</option>
        </select>
      </div>
      <div class="mb-6">
        <button type="submit" class="btn btn-primary me-1 me-sm-3">{{ $l == 'ar' ? 'إضافة' : 'Add' }}</button>
        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ $l == 'ar' ? 'إلغاء' : 'Cancel' }}</button>
      </div>
    </form>
  </div>
</div>

<!-- Offcanvas to add new specialization -->
<div
  class="offcanvas {{ $l == 'ar' ? 'offcanvas-start' : 'offcanvas-end' }}"
  tabindex="-1"
  id="offcanvasAddSpecialization"
  aria-labelledby="offcanvasAddSpecializationLabel">
  <div class="offcanvas-header py-6">
    <h5 id="offcanvasAddSpecializationLabel" class="offcanvas-title">{{ $l == 'ar' ? 'إضافة تخصص' : 'Add Specialization' }}</h5>
    <button
      type="button"
      class="btn-close text-reset"
      data-bs-dismiss="offcanvas"
      aria-label="Close"></button>
  </div>
  <div class="offcanvas-body border-top">
    <form class="pt-0" id="specializationForm">
      <div class="mb-6">
        <label class="form-label" for="specialization-name">{{ $l == 'ar' ? 'الاسم' : 'Name' }}</label>
        <input
          type="text"
          class="form-control"
          id="specialization-name"
          placeholder="{{ $l == 'ar' ? 'أدخل اسم التخصص' : 'Enter specialization name' }}"
          name="specializationName"
          required />
      </div>
      <div class="mb-6">
        <label class="form-label" for="specialization-expertise">{{ $l == 'ar' ? 'مجال الخبرة' : 'Area of Expertise' }}</label>
        <select id="specialization-expertise" class="select2 form-select" name="specializationExpertise" required>
          <option value="">{{ $l == 'ar' ? 'اختر مجال الخبرة' : 'Select area of expertise' }}</option>
        </select>
      </div>
      <div class="mb-6">
        <label class="form-label" for="specialization-description">{{ $l == 'ar' ? 'الوصف' : 'Description' }}</label>
        <textarea
          class="form-control"
          id="specialization-description"
          rows="3"
          placeholder="{{ $l == 'ar' ? 'أدخل الوصف' : 'Enter description' }}"
          name="specializationDescription"
          ></textarea>
      </div>
      <div class="mb-6">
        <label class="form-label">{{ $l == 'ar' ? 'الحالة' : 'Status' }}</label>
        <select id="specialization-status" class="select2 form-select" name="specializationStatus">
          <option value="active">{{ $l == 'ar' ? 'نشط' : 'Active' }}</option>
          <option value="inactive">{{ $l == 'ar' ? 'غير نشط' : 'Inactive' }}</option>
        </select>
      </div>
      <div class="mb-6">
        <button type="submit" class="btn btn-primary me-1 me-sm-3">{{ $l == 'ar' ? 'إضافة' : 'Add' }}</button>
        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ $l == 'ar' ? 'إلغاء' : 'Cancel' }}</button>
      </div>
    </form>
  </div>
</div>
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
@stop

@section('pagescripts')
<script>
  $(function () {
  let isRtl = {{ $l == 'ar' ? 'true' : 'false' }};
  
  // API Configuration
  const API_BASE = '/api';
  const API_ENDPOINTS = {
    expertise: {
      list: `${API_BASE}/expertise`,
      create: `${API_BASE}/expertise`,
      update: (id) => `${API_BASE}/expertise/${id}`,
      delete: (id) => `${API_BASE}/expertise/${id}`,
      import: `${API_BASE}/expertise/import`
    },
    specialization: {
      list: `${API_BASE}/specializations`,
      create: `${API_BASE}/specializations`,
      update: (id) => `${API_BASE}/specializations/${id}`,
      delete: (id) => `${API_BASE}/specializations/${id}`,
      import: `${API_BASE}/specializations/import`
    }
  };

  let expertiseData = [];
  let specializationData = [];
  let dt_expertise, dt_specialization;
  let editingExpertiseId = null;
  let editingSpecializationId = null;

  // CSRF Token Setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // API Helper Functions
  const api = {
    get: async (url) => {
      try {
        const response = await $.ajax({
          url: url,
          method: 'GET',
          dataType: 'json'
        });
        return { success: true, data: response.data || response };
      } catch (error) {
        return { success: false, error: error.responseJSON || error };
      }
    },
    
    post: async (url, data) => {
      try {
        const response = await $.ajax({
          url: url,
          method: 'POST',
          data: JSON.stringify(data),
          contentType: 'application/json',
          dataType: 'json'
        });
        return { success: true, data: response.data || response };
      } catch (error) {
        return { success: false, error: error.responseJSON || error };
      }
    },
    
    put: async (url, data) => {
      try {
        const response = await $.ajax({
          url: url,
          method: 'PUT',
          data: JSON.stringify(data),
          contentType: 'application/json',
          dataType: 'json'
        });
        return { success: true, data: response.data || response };
      } catch (error) {
        return { success: false, error: error.responseJSON || error };
      }
    },
    
    delete: async (url) => {
      try {
        const response = await $.ajax({
          url: url,
          method: 'DELETE',
          dataType: 'json'
        });
        return { success: true, data: response.data || response };
      } catch (error) {
        return { success: false, error: error.responseJSON || error };
      }
    }
  };

  // Load data from API
  async function loadData() {
    try {
      // Fetch expertise data
      const expertiseResult = await api.get(API_ENDPOINTS.expertise.list);
      if (expertiseResult.success) {
        expertiseData = expertiseResult.data;
      } else {
        throw new Error('Failed to load expertise data');
      }

      // Fetch specialization data
      const specializationResult = await api.get(API_ENDPOINTS.specialization.list);
      if (specializationResult.success) {
        specializationData = specializationResult.data;
      } else {
        throw new Error('Failed to load specialization data');
      }

      initializeDataTables();
      
    } catch (error) {
      console.error('Load data error:', error);
      initializeDataTables();
    }
  }

  // Initialize DataTables
  function initializeDataTables() {
    var dt_expertise_table = $('.datatables-expertise-list'),
      dt_specialization_table = $('.datatables-specialization-list');

    // Destroy existing DataTables if they exist
    if ($.fn.DataTable.isDataTable(dt_expertise_table)) {
      dt_expertise_table.DataTable().destroy();
    }
    if ($.fn.DataTable.isDataTable(dt_specialization_table)) {
      dt_specialization_table.DataTable().destroy();
    }

    // Expertise DataTable
    if (dt_expertise_table.length) {
      dt_expertise = dt_expertise_table.DataTable({
        processing: true,
        data: expertiseData,
        columns: [
          { data: null },
          { data: 'id' },
          { data: 'name' },
          { data: 'description' },
          { data: 'specializations' },
          { data: 'status' },
          { data: 'createdAt' },
          { data: null }
        ],
        columnDefs: [
          {
            className: 'control d-lg-none',
            searchable: false,
            orderable: false,
            responsivePriority: 2,
            targets: 0,
            render: function (data, type, full, meta) {
              return '';
            }
          },
          {
            targets: 1,
            responsivePriority: 4,
            render: function (data, type, full, meta) {
              return '<span class="fw-medium">' + full['id'] + '</span>';
            }
          },
          {
            targets: 2,
            responsivePriority: 4,
            render: function (data, type, full, meta) {
              var $name = full['name'];
              return '<span class="fw-medium">' + $name + '</span>';
            }
          },
          {
            targets: 3,
            render: function (data, type, full, meta) {
              var $description = full['description'] || '';
              return `
                <div class="d-flex align-items-center" style="max-width: 400px;">
                  <span class="text-truncate" style="max-width: 100%;">
                    ${$description}
                  </span>
                </div>
              `;
            }
          },
          {
            targets: 4,
            render: function (data, type, full, meta) {
              var $count = full['specializations'] || 0;
              return '<span class="badge bg-label-primary">' + $count + '</span>';
            }
          },
          {
            targets: 5,
            render: function (data, type, full, meta) {
              var $status = full['status'];
              var statusObj = {
                active: { title: 'Active', class: 'bg-label-success' },
                inactive: { title: 'Inactive', class: 'bg-label-secondary' }
              };
              return (
                '<span class="badge ' +
                statusObj[$status].class +
                '" text-capitalized>' +
                statusObj[$status].title +
                '</span>'
              );
            }
          },
          {
            targets: 6,
            render: function (data, type, full, meta) {
              return full['createdAt'] ? moment(full['createdAt']).format('YYYY-MM-DD HH:mm:ss') : '';
            }
          },
          {
            targets: -1,
            title: isRtl ? 'الإجراءات' : 'Actions',
            searchable: false,
            orderable: false,
            render: function (data, type, full, meta) {
              return (
                '<div class="d-flex align-items-center">' +
                '<a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect edit-expertise" data-id="' + full.id + '" data-bs-toggle="tooltip" title="Edit Expertise"><i class="ti ti-edit ti-md"></i></a>' +
                '<a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect delete-expertise" data-id="' + full.id + '" data-bs-toggle="tooltip" title="Delete Expertise"><i class="ti ti-trash ti-md"></i></a>' +
                '</div>'
              );
            }
          }
        ],
        order: [[6, 'desc']],
        dom:
          '<"row me-2"' +
          '<"col-md-2"<"me-3"l>>' +
          '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +
          '>t' +
          '<"row mx-2"' +
          '<"col-sm-12 col-md-6"i>' +
          '<"col-sm-12 col-md-6"p>' +
          '>'
        @if (app()->getLocale() == 'ar')
        ,language: {
          search: 'ابحث:',
          lengthMenu: 'أظهر _MENU_ مدخلات',
          info: 'إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل',
          paginate: { previous: 'السابق', next: 'التالي' },
          emptyTable: 'لا توجد بيانات'
        }
        @endif,
        buttons: [
          {
            extend: 'collection',
            className: 'btn btn-label-secondary dropdown-toggle mx-3 waves-effect waves-light',
            text: '<i class="ti ti-file-export me-1 ti-xs"></i>' + (isRtl ? 'تصدير' : 'Export'),
            buttons: [
              {
                extend: 'csv',
                text: '<i class="ti ti-file-text me-2" ></i>Csv',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4, 5, 6]
                }
              },
              {
                extend: 'excel',
                text: '<i class="ti ti-file-spreadsheet me-2"></i>Excel',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4, 5, 6]
                }
              }
            ]
          }
        ],
        responsive: true
      });
    }

    // Specialization DataTable
    if (dt_specialization_table.length) {
      dt_specialization = dt_specialization_table.DataTable({
        processing: true,
        data: specializationData,
        columns: [
          { data: null },
          { data: 'id' },
          { data: 'name' },
          { data: 'expertise_name' },
          { data: 'description' },
          { data: 'status' },
          { data: 'createdAt' },
          { data: null }
        ],
        columnDefs: [
          {
            className: 'control d-lg-none',
            searchable: false,
            orderable: false,
            responsivePriority: 2,
            targets: 0,
            render: function (data, type, full, meta) {
              return '';
            }
          },
          {
            targets: 1,
            responsivePriority: 4,
            render: function (data, type, full, meta) {
              return '<span class="fw-medium">' + full['id'] + '</span>';
            }
          },
          {
            targets: 2,
            responsivePriority: 4,
            render: function (data, type, full, meta) {
              var $name = full['name'];
              return '<span class="fw-medium">' + $name + '</span>';
            }
          },
          {
            targets: 3,
            render: function (data, type, full, meta) {
              var $expertise = full['expertise_name'] || '';
              return '<span class="badge bg-label-info">' + $expertise + '</span>';
            }
          },
          {
            targets: 4,
            render: function (data, type, full, meta) {
              var $description = full['description'] || '';
              return `
                <div class="d-flex align-items-center" style="max-width: 400px;">
                  <span class="text-truncate" style="max-width: 100%;">
                    ${$description}
                  </span>
                </div>
              `;
            }
          },
          {
            targets: 5,
            render: function (data, type, full, meta) {
              var $status = full['status'];
              var statusObj = {
                active: { title: 'Active', class: 'bg-label-success' },
                inactive: { title: 'Inactive', class: 'bg-label-secondary' }
              };
              return (
                '<span class="badge ' +
                statusObj[$status].class +
                '" text-capitalized>' +
                statusObj[$status].title +
                '</span>'
              );
            }
          },
          {
            targets: 6,
            render: function (data, type, full, meta) {
              return full['createdAt'] ? moment(full['createdAt']).format('YYYY-MM-DD HH:mm:ss') : '';
            }
          },
          {
            targets: -1,
            title: isRtl ? 'الإجراءات' : 'Actions',
            searchable: false,
            orderable: false,
            render: function (data, type, full, meta) {
              return (
                '<div class="d-flex align-items-center">' +
                '<a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect edit-specialization" data-id="' + full.id + '" data-bs-toggle="tooltip" title="Edit Specialization"><i class="ti ti-edit ti-md"></i></a>' +
                '<a href="javascript:;" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect delete-specialization" data-id="' + full.id + '" data-bs-toggle="tooltip" title="Delete Specialization"><i class="ti ti-trash ti-md"></i></a>' +
                '</div>'
              );
            }
          }
        ],
        order: [[6, 'desc']],
        dom:
          '<"row me-2"' +
          '<"col-md-2"<"me-3"l>>' +
          '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +
          '>t' +
          '<"row mx-2"' +
          '<"col-sm-12 col-md-6"i>' +
          '<"col-sm-12 col-md-6"p>' +
          '>'
        @if (app()->getLocale() == 'ar')
        ,language: {
          search: 'ابحث:',
          lengthMenu: 'أظهر _MENU_ مدخلات',
          info: 'إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل',
          paginate: { previous: 'السابق', next: 'التالي' },
          emptyTable: 'لا توجد بيانات'
        }
        @endif,
        buttons: [
          {
            extend: 'collection',
            className: 'btn btn-label-secondary dropdown-toggle mx-3 waves-effect waves-light',
            text: '<i class="ti ti-file-export me-1 ti-xs"></i>' + (isRtl ? 'تصدير' : 'Export'),
            buttons: [
              {
                extend: 'csv',
                text: '<i class="ti ti-file-text me-2" ></i>Csv',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4, 5, 6]
                }
              },
              {
                extend: 'excel',
                text: '<i class="ti ti-file-spreadsheet me-2"></i>Excel',
                className: 'dropdown-item',
                exportOptions: {
                  columns: [1, 2, 3, 4, 5, 6]
                }
              }
            ]
          }
        ],
        responsive: true
      });
    }

    initializeEventHandlers();

    setTimeout(() => {
      $('.dataTables_filter .form-control').removeClass('form-control-sm');
      $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);
  }

  // Initialize event handlers
  function initializeEventHandlers() {
    // Remove any existing event handlers first to prevent duplicates
    $(document).off('click', '.edit-expertise');
    $(document).off('click', '.edit-specialization');
    $(document).off('click', '.delete-expertise');
    $(document).off('click', '.delete-specialization');

    // Edit expertise
    $(document).on('click', '.edit-expertise', function () {
      const id = $(this).data('id');
      const expertise = expertiseData.find(e => e.id == id);
      if (expertise) {
        editingExpertiseId = id;
        $('#expertise-name').val(expertise.name);
        $('#expertise-description').val(expertise.description);
        $('#expertise-status').val(expertise.status).trigger('change');
        $('#offcanvasAddExpertiseLabel').text(isRtl ? 'تعديل مجال الخبرة' : 'Edit Area of Expertise');
        $('#expertiseForm button[type="submit"]').text(isRtl ? 'تحديث' : 'Update');
        $('#offcanvasAddExpertise').offcanvas('show');
      }
    });

    // Edit specialization
    $(document).on('click', '.edit-specialization', function () {
      const id = $(this).data('id');
      const specialization = specializationData.find(s => s.id == id);
      if (specialization) {
        editingSpecializationId = id;
        populateExpertiseDropdown();
        $('#specialization-name').val(specialization.name);
        $('#specialization-expertise').val(specialization.expertise_id).trigger('change');
        $('#specialization-description').val(specialization.description);
        $('#specialization-status').val(specialization.status).trigger('change');
        $('#offcanvasAddSpecializationLabel').text(isRtl ? 'تعديل التخصص' : 'Edit Specialization');
        $('#specializationForm button[type="submit"]').text(isRtl ? 'تحديث' : 'Update');
        $('#offcanvasAddSpecialization').offcanvas('show');
      }
    });

    // Delete expertise
    $(document).on('click', '.delete-expertise', async function () {
      const id = $(this).data('id');
      releaseAlert('warning', {
        title: isRtl ? 'هل أنت متأكد؟' : 'Are you sure?',
        message: isRtl ? 'سيؤدي هذا أيضاً إلى حذف جميع التخصصات المرتبطة!' : 'This will also delete all related specializations!',
        confirmText: isRtl ? 'نعم، احذف!' : 'Yes, delete it!',
        cancelText: isRtl ? 'إلغاء' : 'Cancel',
        confirmColor: 'danger'
      }).then(async function (ok) {
        if (ok) {
          const deleteResult = await api.delete(API_ENDPOINTS.expertise.delete(id));
          
          if (deleteResult.success) {
            // Remove from local data
            expertiseData = expertiseData.filter(e => e.id != id);
            
            // Remove related specializations from local data
            specializationData = specializationData.filter(s => s.expertise_id != id);
            
            // Update DataTable without refetching
            dt_expertise.clear();
            dt_expertise.rows.add(expertiseData);
            dt_expertise.draw();
            
            // Also update specialization table if it exists
            if (dt_specialization) {
              dt_specialization.clear();
              dt_specialization.rows.add(specializationData);
              dt_specialization.draw();
            }
            
            releaseAlert('success', {
              title: isRtl ? 'تم الحذف!' : 'Deleted!',
              message: isRtl ? 'تم حذف مجال الخبرة.' : 'Area of expertise has been deleted.',
            });
          } else {
            releaseAlert('danger', {
              title: isRtl ? 'خطأ' : 'Error',
              message: deleteResult.error?.message || (isRtl ? 'فشل حذف مجال الخبرة' : 'Failed to delete expertise'),
              confirmColor: 'primary'
            });
          }
        }
      });
    });

    // Delete specialization
    $(document).on('click', '.delete-specialization', async function () {
      const id = $(this).data('id');
      const specialization = specializationData.find(s => s.id == id);
      
      releaseAlert('warning', {
        title: isRtl ? 'هل أنت متأكد؟' : 'Are you sure?',
        message: isRtl ? 'لن تتمكن من التراجع عن هذا!' : 'You won\'t be able to revert this!',
        confirmText: isRtl ? 'نعم، احذف!' : 'Yes, delete it!',
        cancelText: isRtl ? 'إلغاء' : 'Cancel',
        confirmColor: 'danger'
      }).then(async function (ok) {
        if (ok) {
          const deleteResult = await api.delete(API_ENDPOINTS.specialization.delete(id));
          
          if (deleteResult.success) {
            // Remove from local data
            specializationData = specializationData.filter(s => s.id != id);
            
            // Update specialization count for the expertise
            if (specialization && specialization.expertise_id) {
              const expertise = expertiseData.find(e => e.id == specialization.expertise_id);
              if (expertise) {
                expertise.specializations = Math.max(0, (expertise.specializations || 0) - 1);
                
                // Update expertise table
                dt_expertise.clear();
                dt_expertise.rows.add(expertiseData);
                dt_expertise.draw();
              }
            }
            
            // Update specialization table
            dt_specialization.clear();
            dt_specialization.rows.add(specializationData);
            dt_specialization.draw();
            
            releaseAlert('success', {
              title: isRtl ? 'تم الحذف!' : 'Deleted!',
              message: isRtl ? 'تم حذف التخصص.' : 'Specialization has been deleted.',
            });
          } else {
            releaseAlert('danger', {
              title: isRtl ? 'خطأ' : 'Error',
              message: deleteResult.error?.message || (isRtl ? 'فشل حذف التخصص' : 'Failed to delete specialization'),
              confirmColor: 'primary'
            });
          }
        }
      });
    });
  }

  // Form validation for expertise
  const expertiseForm = document.getElementById('expertiseForm');
  const fv = FormValidation.formValidation(expertiseForm, {
    fields: {
      expertiseName: {
        validators: {
          notEmpty: {
            message: isRtl ? 'يرجى إدخال اسم الخبرة' : 'Please enter expertise name'
          },
          stringLength: {
            max: 50,
            message: isRtl ? 'اسم الخبرة يجب أن يكون 50 حرف أو أقل' : 'Expertise name must be 50 characters or less'
          }
        }
      },
      expertiseDescription: {
        validators: {
          stringLength: {
            max: 100,
            message: isRtl ? 'الوصف يجب أن يكون 100 حرف أو أقل' : 'Description must be 100 characters or less'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: function (field, ele) {
          return '.mb-6';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', async function () {
    var expertiseName = document.getElementById('expertise-name').value,
      expertiseDescription = document.getElementById('expertise-description').value,
      expertiseStatus = document.getElementById('expertise-status').value;

    if (expertiseName) {
      const isEditing = editingExpertiseId !== null;
      const data = {
        name: expertiseName,
        description: expertiseDescription,
        status: expertiseStatus
      };

      let result;
      if (isEditing) {
        result = await api.put(API_ENDPOINTS.expertise.update(editingExpertiseId), data);
      } else {
        result = await api.post(API_ENDPOINTS.expertise.create, data);
      }

      if (result.success) {
        // Update local data instead of refetching
        if (isEditing) {
          // Update existing expertise
          const index = expertiseData.findIndex(e => e.id == editingExpertiseId);
          if (index !== -1) {
            expertiseData[index] = {
              ...expertiseData[index],
              ...data,
              id: editingExpertiseId
            };
          }
        } else {
          // Add new expertise (use returned ID from API if available)
          const newExpertise = {
            id: result.data.id || Date.now(), // Use API ID or fallback
            ...data,
            specializations: 0
          };
          expertiseData.unshift(newExpertise); // Add to beginning
        }

        // Update DataTable without refetching
        dt_expertise.clear();
        dt_expertise.rows.add(expertiseData);
        dt_expertise.draw();

        // Hide offcanvas
        $('#offcanvasAddExpertise').offcanvas('hide');

        // Reset form
        expertiseForm.reset();
        fv.resetForm();
        editingExpertiseId = null;
        $('#offcanvasAddExpertiseLabel').text(isRtl ? 'إضافة مجال خبرة' : 'Add Area of Expertise');
        $('#expertiseForm button[type="submit"]').text(isRtl ? 'إضافة' : 'Add');

        // Show success message
        releaseAlert('success', {
          title: isRtl ? 'نجاح!' : 'Success!',
          message: isEditing ? 
            (isRtl ? 'تم تحديث مجال الخبرة بنجاح.' : 'Area of expertise updated successfully.') : 
            (isRtl ? 'تم إضافة مجال الخبرة بنجاح.' : 'Area of expertise added successfully.'),
        });
      } else {
        releaseAlert('danger', {
          title: isRtl ? 'خطأ' : 'Error',
          message: result.error?.message || (isRtl ? 'فشل حفظ مجال الخبرة' : 'Failed to save expertise'),
          confirmColor: 'primary'
        });
      }
    }
  });

  // Form validation for specialization
  const specializationForm = document.getElementById('specializationForm');
  const fv2 = FormValidation.formValidation(specializationForm, {
    fields: {
      specializationName: {
        validators: {
          notEmpty: {
            message: isRtl ? 'يرجى إدخال اسم التخصص' : 'Please enter specialization name'
          },
          stringLength: {
            max: 50,
            message: isRtl ? 'اسم التخصص يجب أن يكون 50 حرف أو أقل' : 'Specialization name must be 50 characters or less'
          }
        }
      },
      specializationExpertise: {
        validators: {
          notEmpty: {
            message: isRtl ? 'يرجى اختيار مجال الخبرة' : 'Please select area of expertise'
          }
        }
      },
      specializationDescription: {
        validators: {
          stringLength: {
            max: 100,
            message: isRtl ? 'الوصف يجب أن يكون 100 حرف أو أقل' : 'Description must be 100 characters or less'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: function (field, ele) {
          return '.mb-6';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  }).on('core.form.valid', async function () {
    var specializationName = document.getElementById('specialization-name').value,
      specializationExpertise = document.getElementById('specialization-expertise').value,
      specializationDescription = document.getElementById('specialization-description').value,
      specializationStatus = document.getElementById('specialization-status').value;

    if (specializationName && specializationExpertise) {
      const isEditing = editingSpecializationId !== null;
      const data = {
        name: specializationName,
        expertise_id: parseInt(specializationExpertise),
        description: specializationDescription,
        status: specializationStatus
      };

      let result;
      if (isEditing) {
        result = await api.put(API_ENDPOINTS.specialization.update(editingSpecializationId), data);
      } else {
        result = await api.post(API_ENDPOINTS.specialization.create, data);
      }

      if (result.success) {
        // Get expertise name for display
        const expertise = expertiseData.find(e => e.id == data.expertise_id);
        const expertiseName = expertise ? expertise.name : '';
        
        // Update local data instead of refetching
        if (isEditing) {
          // Track old expertise_id to update counts
          const oldSpecialization = specializationData.find(s => s.id == editingSpecializationId);
          const oldExpertiseId = oldSpecialization ? oldSpecialization.expertise_id : null;
          
          // Update existing specialization
          const index = specializationData.findIndex(s => s.id == editingSpecializationId);
          if (index !== -1) {
            specializationData[index] = {
              ...specializationData[index],
              ...data,
              expertise_name: expertiseName,
              id: editingSpecializationId
            };
          }
          
          // Update specialization counts if expertise changed
          if (oldExpertiseId && oldExpertiseId != data.expertise_id) {
            // Decrease old expertise count
            const oldExpertise = expertiseData.find(e => e.id == oldExpertiseId);
            if (oldExpertise) {
              oldExpertise.specializations = Math.max(0, (oldExpertise.specializations || 0) - 1);
            }
            
            // Increase new expertise count
            if (expertise) {
              expertise.specializations = (expertise.specializations || 0) + 1;
            }
            
            // Update expertise table
            dt_expertise.clear();
            dt_expertise.rows.add(expertiseData);
            dt_expertise.draw();
          }
        } else {
          // Add new specialization (use returned ID from API if available)
          const newSpecialization = {
            id: result.data.id || Date.now(), // Use API ID or fallback
            ...data,
            expertise_name: expertiseName
          };
          specializationData.unshift(newSpecialization); // Add to beginning
          
          // Increase expertise count
          if (expertise) {
            expertise.specializations = (expertise.specializations || 0) + 1;
            
            // Update expertise table
            dt_expertise.clear();
            dt_expertise.rows.add(expertiseData);
            dt_expertise.draw();
          }
        }

        // Update specialization DataTable without refetching
        dt_specialization.clear();
        dt_specialization.rows.add(specializationData);
        dt_specialization.draw();

        // Hide offcanvas
        $('#offcanvasAddSpecialization').offcanvas('hide');

        // Reset form
        specializationForm.reset();
        fv2.resetForm();
        editingSpecializationId = null;
        $('#offcanvasAddSpecializationLabel').text(isRtl ? 'إضافة تخصص' : 'Add Specialization');
        $('#specializationForm button[type="submit"]').text(isRtl ? 'إضافة' : 'Add');

        // Show success message
        releaseAlert('success', {
          title: isRtl ? 'نجاح!' : 'Success!',
          message: isEditing ? 
            (isRtl ? 'تم تحديث التخصص بنجاح.' : 'Specialization updated successfully.') : 
            (isRtl ? 'تم إضافة التخصص بنجاح.' : 'Specialization added successfully.'),
        });
      } else {
        releaseAlert('danger', {
          title: isRtl ? 'خطأ' : 'Error',
          message: result.error?.message || (isRtl ? 'فشل حفظ التخصص' : 'Failed to save specialization'),
          confirmColor: 'primary'
        });
      }
    }
  });

  // Populate expertise dropdown in specialization form
  function populateExpertiseDropdown() {
    const select = document.getElementById('specialization-expertise');
    select.innerHTML = '<option value="">' + (isRtl ? 'اختر مجال الخبرة' : 'Select area of expertise') + '</option>';
    
    expertiseData.forEach(expertise => {
      const option = document.createElement('option');
      option.value = expertise.id;
      option.textContent = expertise.name;
      select.appendChild(option);
    });
  }

  // Initialize expertise dropdown when specialization offcanvas is shown
  $('#offcanvasAddSpecialization').on('shown.bs.offcanvas', function () {
    if (!editingSpecializationId) {
      populateExpertiseDropdown();
    }
    $('#offcanvasAddSpecialization .select2').select2({
      placeholder: isRtl ? 'اختر عنصر' : 'Select option',
      dropdownParent: $('#offcanvasAddSpecialization')
    });
  });

  // Reset forms when offcanvas is hidden
  $('#offcanvasAddExpertise').on('hidden.bs.offcanvas', function () {
    editingExpertiseId = null;
    $('#offcanvasAddExpertiseLabel').text(isRtl ? 'إضافة مجال خبرة' : 'Add Area of Expertise');
    $('#expertiseForm button[type="submit"]').text(isRtl ? 'إضافة' : 'Add');
    $('#expertiseForm')[0].reset();
  });

  $('#offcanvasAddSpecialization').on('hidden.bs.offcanvas', function () {
    editingSpecializationId = null;
    $('#offcanvasAddSpecializationLabel').text(isRtl ? 'إضافة تخصص' : 'Add Specialization');
    $('#specializationForm button[type="submit"]').text(isRtl ? 'إضافة' : 'Add');
    $('#specializationForm')[0].reset();
  });

  // Initialize Select2 for expertise offcanvas
  $('#offcanvasAddExpertise').on('shown.bs.offcanvas', function () {
    $('#offcanvasAddExpertise .select2').select2({
      placeholder: isRtl ? 'اختر عنصر' : 'Select option',
      dropdownParent: $('#offcanvasAddExpertise')
    });
  });

  // JSON Import Functions
  async function handleExpertiseImport(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.name.toLowerCase().endsWith('.json')) {
      releaseAlert('danger', {
        title: isRtl ? 'خطأ' : 'Error',
        message: isRtl ? 'نوع الملف غير مدعوم. يرجى تحميل ملف بصيغة JSON فقط' : 'Unsupported file type. Please upload a JSON file only.',
        confirmColor: 'primary'
      });
      event.target.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = async function(e) {
      try {
        const jsonData = JSON.parse(e.target.result);
        
        if (!Array.isArray(jsonData) || jsonData.length === 0) {
          throw new Error('Invalid format');
        }

        const result = await api.post(API_ENDPOINTS.expertise.import, { items: jsonData });

        if (result.success) {
          // Add imported items to local data (if API returns them)
          if (result.data.items && Array.isArray(result.data.items)) {
            result.data.items.forEach(item => {
              const exists = expertiseData.find(e => e.id == item.id);
              if (!exists) {
                expertiseData.unshift(item);
              }
            });
          } else {
            // If API doesn't return items, refetch only expertise data
            const expertiseResult = await api.get(API_ENDPOINTS.expertise.list);
            if (expertiseResult.success) {
              expertiseData = expertiseResult.data;
            }
          }
          
          // Update DataTable
          dt_expertise.clear();
          dt_expertise.rows.add(expertiseData);
          dt_expertise.draw();

          const importedCount = result.data.imported || 0;
          const skippedCount = result.data.skipped || 0;

          if (importedCount > 0) {
            let message = isRtl ? `تم استيراد ${importedCount} من مجالات الخبرة بنجاح` : `${importedCount} expertise areas imported successfully.`;
            if (skippedCount > 0) {
              message += isRtl ? `. تم تخطي ${skippedCount} عنصر` : `. ${skippedCount} items skipped.`;
            }
            
            releaseAlert('success', {
              title: isRtl ? 'نجاح!' : 'Success!',
              message: message
            });
          } else if (skippedCount > 0) {
            releaseAlert('warning', {
              title: isRtl ? 'تحذير' : 'Warning',
              message: isRtl ? 'تم تخطي جميع العناصر' : 'All items were skipped.',
              confirmColor: 'primary',
              hideCancel: true
            });
          }
        } else {
          throw new Error(result.error?.message || 'Import failed');
        }

      } catch (error) {
        releaseAlert('danger', {
          title: isRtl ? 'خطأ' : 'Error',
          message: error.message || (isRtl ? 'ملف غير صالح أو فارغ' : 'Invalid or empty file'),
          confirmColor: 'primary'
        });
      }
    };
    reader.readAsText(file);
    event.target.value = '';
  }

  async function handleSpecializationImport(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.name.toLowerCase().endsWith('.json')) {
      releaseAlert('danger', {
        title: isRtl ? 'خطأ' : 'Error',
        message: isRtl ? 'نوع الملف غير مدعوم. يرجى تحميل ملف بصيغة JSON فقط' : 'Unsupported file type. Please upload a JSON file only.',
        confirmColor: 'primary'
      });
      event.target.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = async function(e) {
      try {
        const jsonData = JSON.parse(e.target.result);
        
        if (!Array.isArray(jsonData) || jsonData.length === 0) {
          throw new Error('Invalid format');
        }

        const result = await api.post(API_ENDPOINTS.specialization.import, { items: jsonData });

        if (result.success) {
          // Add imported items to local data and update counts
          if (result.data.items && Array.isArray(result.data.items)) {
            result.data.items.forEach(item => {
              const exists = specializationData.find(s => s.id == item.id);
              if (!exists) {
                specializationData.unshift(item);
                
                // Update expertise count
                const expertise = expertiseData.find(e => e.id == item.expertise_id);
                if (expertise) {
                  expertise.specializations = (expertise.specializations || 0) + 1;
                }
              }
            });
            
            // Update both tables
            dt_expertise.clear();
            dt_expertise.rows.add(expertiseData);
            dt_expertise.draw();
          } else {
            // If API doesn't return items, refetch both data sets
            const specializationResult = await api.get(API_ENDPOINTS.specialization.list);
            if (specializationResult.success) {
              specializationData = specializationResult.data;
            }
            
            const expertiseResult = await api.get(API_ENDPOINTS.expertise.list);
            if (expertiseResult.success) {
              expertiseData = expertiseResult.data;
              dt_expertise.clear();
              dt_expertise.rows.add(expertiseData);
              dt_expertise.draw();
            }
          }
          
          // Update specialization DataTable
          dt_specialization.clear();
          dt_specialization.rows.add(specializationData);
          dt_specialization.draw();

          const importedCount = result.data.imported || 0;
          const skippedCount = result.data.skipped || 0;

          if (importedCount > 0) {
            let message = isRtl ? `تم استيراد ${importedCount} من التخصصات بنجاح` : `${importedCount} specializations imported successfully.`;
            if (skippedCount > 0) {
              message += isRtl ? `. تم تخطي ${skippedCount} عنصر` : `. ${skippedCount} items skipped.`;
            }
            
            releaseAlert('success', {
              title: isRtl ? 'نجاح!' : 'Success!',
              message: message
            });
          } else if (skippedCount > 0) {
            releaseAlert('warning', {
              title: isRtl ? 'تحذير' : 'Warning',
              message: isRtl ? 'تم تخطي جميع العناصر' : 'All items were skipped.',
              confirmColor: 'primary',
              hideCancel: true
            });
          }
        } else {
          throw new Error(result.error?.message || 'Import failed');
        }

      } catch (error) {
        releaseAlert('danger', {
          title: isRtl ? 'خطأ' : 'Error',
          message: error.message || (isRtl ? 'ملف غير صالح أو فارغ' : 'Invalid or empty file'),
          confirmColor: 'primary'
        });
      }
    };
    reader.readAsText(file);
    event.target.value = '';
  }

  // Attach import event listeners
  document.getElementById('expertise-import-file').addEventListener('change', handleExpertiseImport);
  document.getElementById('specialization-import-file').addEventListener('change', handleSpecializationImport);

  // Tab switch event handler - just redraw with existing data
  $('a[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
    const target = $(e.target).attr('data-bs-target');
    
    // Just redraw the table with existing data
    if (target === '#specializations' && dt_specialization) {
      dt_specialization.draw();
    }
  });

  // Load data and initialize
  loadData();
});
</script>
@stop
