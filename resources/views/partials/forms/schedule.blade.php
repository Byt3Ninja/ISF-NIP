<!-- Schedule Management Section -->
<div class="row">
  <div class="col-12">
    <!-- Header with Add Button and Export -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
      <div class="d-flex gap-2">
        <!-- Export Dropdown -->
        <div class="dropdown">
          <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ti ti-download me-1"></i>{{ $l == 'en' ? 'Export' : 'تصدير' }}
          </button>
          <ul class="dropdown-menu" aria-labelledby="exportDropdown">
            <li>
              <a class="dropdown-item" href="javascript:void(0)" onclick="exportSchedules('csv')">
                <i class="ti ti-file-text me-2"></i>{{ $l == 'en' ? 'Export as CSV' : 'تصدير كـ CSV' }}
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:void(0)" onclick="exportSchedules('excel')">
                <i class="ti ti-file-spreadsheet me-2"></i>{{ $l == 'en' ? 'Export as Excel' : 'تصدير كـ Excel' }}
              </a>
            </li>
          </ul>
        </div>
        
        <!-- Add Button -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleModal" onclick="openScheduleModal()">
          <i class="ti ti-plus me-1"></i>{{ $l == 'en' ? 'Add Schedule' : 'إضافة جدول' }}
        </button>
      </div>
    </div>

    <!-- Schedules Table -->
    <div class="table-responsive border px-4 pb-6 rounded">
      <table class="table table-hover" id="schedulesTable">
        <thead class="table-light">
          <tr>
            <th>{{ $l == 'en' ? 'Value' : 'القيمة' }}</th>
            <th>{{ $l == 'en' ? 'Unit' : 'الوحدة' }}</th>
            <th>{{ $l == 'en' ? 'Status' : 'الحالة' }}</th>
            <th>{{ $l == 'en' ? 'Created Date' : 'تاريخ الإنشاء' }}</th>
            <th>{{ $l == 'en' ? 'Actions' : 'الإجراءات' }}</th>
          </tr>
        </thead>
        <tbody id="schedulesTableBody">
          @php
          if(auth()->user()->hasRole('organizationManager')) {
            $schedules = \App\MentorshipSchedule::where('organization_id', auth()->user()->organization_id)->orderBy('created_at', 'desc')->get();
          } elseif(auth()->user()->hasRole('Admin')) {
            $schedules = \App\MentorshipSchedule::orderBy('created_at', 'desc')->get();
          } else {
            $schedules = [];
          }
          @endphp
          @foreach ($schedules as $schedule)
          <tr id="schedule-row-{{ $schedule->id }}">
            <td>
              <span class="fw-medium">{{ $schedule->value }}</span>
            </td>
            <td>
              <span class="badge bg-label-info">
                @switch($schedule->unit)
                  @case('hours')
                    {{ $l == 'en' ? 'Hours' : 'ساعات' }}
                    @break
                  @case('minutes')
                    {{ $l == 'en' ? 'Minutes' : 'دقائق' }}
                    @break
                  @case('days')
                    {{ $l == 'en' ? 'Days' : 'أيام' }}
                    @break
                  @case('weeks')
                    {{ $l == 'en' ? 'Weeks' : 'أسابيع' }}
                    @break
                  @default
                    {{ ucfirst($schedule->unit) }}
                @endswitch
              </span>
            </td>
            <td>
              <div class="form-check form-switch">
                <input 
                  class="form-check-input schedule-status-toggle w-px-30" 
                  data-value="{{ $schedule->value }}"
                  data-unit="{{ $schedule->unit }}" 
                  data-id="{{ $schedule->id }}" 
                  {{ $schedule->is_active ? 'checked' : '' }} 
                  type="checkbox" 
                  id="status_{{ $schedule->id }}"
                />
                <label class="form-check-label" for="status_{{ $schedule->id }}">
                  <span class="badge {{ $schedule->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ $schedule->is_active ? __('admin.Active') : __('admin.Inactive') }}
                  </span>
                </label>
              </div>
            </td>
            <td>
              <small class="text-muted">{{ $schedule->created_at->format('M d, Y') }}</small>
            </td>
            <td>
              <div class="btn-group" role="group">
                <button 
                  type="button" 
                  class="btn btn-sm btn-outline-primary schedule-edit" 
                  data-id="{{ $schedule->id }}"
                  data-value="{{ $schedule->value }}"
                  data-unit="{{ $schedule->unit }}"
                  data-active="{{ $schedule->is_active }}"
                  title="{{ __('admin.Edit') }}"
                >
                  <i class="ti ti-edit"></i>
                </button>
                <button 
                  type="button" 
                  class="btn btn-sm btn-outline-danger schedule-delete" 
                  data-id="{{ $schedule->id }}"
                  title="{{ __('admin.Delete') }}"
                >
                  <i class="ti ti-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="scheduleForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="scheduleModalLabel">{{ $l == 'en' ? 'Add Schedule' : 'إضافة جدول' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="scheduleId" name="id" value="">
          
          <div class="mb-3">
            <label for="scheduleValue" class="form-label">
              {{ $l == 'en' ? 'Value' : 'القيمة' }} <span class="text-danger">*</span>
            </label>
            <input 
              type="number" 
              class="form-control" 
              id="scheduleValue" 
              name="value" 
              placeholder="{{ $l == 'en' ? 'Enter schedule value' : 'أدخل قيمة الجدول' }}"
              min="1"
              required
            >
            <div class="invalid-feedback" id="valueError"></div>
          </div>

          <div class="mb-3">
            <label for="scheduleUnit" class="form-label">
              {{ $l == 'en' ? 'Unit' : 'الوحدة' }} <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="scheduleUnit" name="unit" required>
              <option value="">{{ $l == 'en' ? 'Select Unit' : 'اختر الوحدة' }}</option>
              <option value="hours">{{ $l == 'en' ? 'Hours' : 'ساعات' }}</option>
              <option value="minutes">{{ $l == 'en' ? 'Minutes' : 'دقائق' }}</option>
              <option value="days">{{ $l == 'en' ? 'Days' : 'أيام' }}</option>
              <option value="weeks">{{ $l == 'en' ? 'Weeks' : 'أسابيع' }}</option>
            </select>
            <div class="invalid-feedback" id="unitError"></div>
          </div>

          <div class="mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="scheduleActive" name="is_active" checked>
              <label class="form-check-label" for="scheduleActive">
                {{ __('admin.Active') }}
              </label>
            </div>
            <small class="text-muted">
              {{ $l == 'en' ? 'Enable or disable this schedule option' : 'قم بتمكين أو تعطيل خيار الجدول هذا' }}
            </small>
          </div>

          <!-- Preview Section -->
          <div class="alert alert-info d-none" id="schedulePreview">
            <strong>{{ $l == 'en' ? 'Preview' : 'معاينة' }}:</strong>
            <span id="previewText"></span>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ $l == 'en' ? 'Cancel' : 'إلغاء' }}</button>
          <button type="submit" class="btn btn-primary" id="saveScheduleBtn">
            <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
            <span class="btn-text">{{ $l == 'en' ? 'Save Schedule' : 'حفظ الجدول' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
  // Only initialize schedule management if the schedules table exists
  if ($('#schedulesTable').length === 0) {
    return;
  }
  
  // Enhanced Schedule Management with DataTable
  let currentScheduleId = null;
  let schedulesTable = null;
  
  // Initialize DataTable
  schedulesTable = $('#schedulesTable').DataTable({
    "responsive": true,
    "pageLength": 10,
    "order": [[3, "desc"]], // Sort by created date descending
    "dom": 'Bfrtip', // Add buttons to DOM
    "buttons": [
      {
        extend: 'csv',
        text: '<i class="ti ti-file-text me-1"></i>CSV',
        className: 'd-none', // Hide default buttons, we'll use custom ones
        title: 'Schedules_Export_' + new Date().toISOString().slice(0,10),
        exportOptions: {
          columns: [0, 1, 2, 3] // Export only relevant columns (exclude actions)
        }
      },
      {
        extend: 'excel',
        text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
        className: 'd-none', // Hide default buttons, we'll use custom ones
        title: 'Schedules_Export_' + new Date().toISOString().slice(0,10),
        exportOptions: {
          columns: [0, 1, 2, 3] // Export only relevant columns (exclude actions)
        }
      }
    ],
    @if ($l == 'ar')
    "language": {
      "sEmptyTable": "ليست هناك بيانات متاحة في الجدول",
      "sLoadingRecords": "جارٍ التحميل...",
      "sProcessing": "جارٍ التحميل...",
      "sLengthMenu": "أظهر _MENU_ مدخلات",
      "sZeroRecords": "لم يعثر على أية سجلات",
      "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
      "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
      "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
      "sInfoPostFix": "",
      "sSearch": "ابحث:",
      "sUrl": "",
      "oPaginate": {
        "sFirst": "الأول",
        "sPrevious": "السابق",
        "sNext": "التالي",
        "sLast": "الأخير"
      },
      "oAria": {
        "sSortAscending": ": تفعيل لترتيب العمود تصاعدياً",
        "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
      }
    }
    @endif
  });
  
  // Show Add Schedule Modal
  window.openScheduleModal = function() {
    currentScheduleId = null;
    $('#scheduleModalLabel').text('{{ $l == 'en' ? 'Add Schedule' : 'إضافة جدول' }}');
    $('#saveScheduleBtn .btn-text').text('{{ $l == 'en' ? 'Save Schedule' : 'حفظ الجدول' }}');
    $('#scheduleForm')[0].reset();
    $('#scheduleId').val('');
    $('#scheduleActive').prop('checked', true);
    clearValidationErrors();
    updatePreview();
  };

  // Edit Schedule Modal - using event delegation for DataTable compatibility
  $(document).on('click', '.schedule-edit', function() {
    const id = $(this).data('id');
    const value = $(this).data('value');
    const unit = $(this).data('unit');
    const isActive = $(this).data('active');
    
    currentScheduleId = id;
    $('#scheduleModalLabel').text('{{ $l == 'en' ? 'Edit Schedule' : 'تعديل الجدول' }}');
    $('#saveScheduleBtn .btn-text').text('{{ $l == 'en' ? 'Update Schedule' : 'تحديث الجدول' }}');
    $('#scheduleId').val(id);
    $('#scheduleValue').val(value);
    $('#scheduleUnit').val(unit);
    $('#scheduleActive').prop('checked', isActive == 1);
    clearValidationErrors();
    updatePreview();
    $('#scheduleModal').modal('show');
  });

  // Update preview when form values change
  $(document).on('input change', '#scheduleValue, #scheduleUnit', function() {
    updatePreview();
  });

  function updatePreview() {
    const value = $('#scheduleValue').val();
    const unit = $('#scheduleUnit').val();
    
    if (value && unit) {
      $('#previewText').text(`${value} ${unit}`);
      $('#schedulePreview').removeClass('d-none');
    } else {
      $('#schedulePreview').addClass('d-none');
    }
  }

  // Form submission
  $('#scheduleForm').on('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = $('#saveScheduleBtn');
    const spinner = submitBtn.find('.spinner-border');
    
    // Show loading state
    submitBtn.prop('disabled', true);
    spinner.removeClass('d-none');
    
    const formData = {
      value: $('#scheduleValue').val(),
      unit: $('#scheduleUnit').val(),
      is_active: $('#scheduleActive').is(':checked') ? 1 : 0,
      _token: "{{ csrf_token() }}"
    };
    
    if (currentScheduleId) {
      formData.id = currentScheduleId;
    }
    
    const url = currentScheduleId 
      ? "{{ route('mentorship.schedule_update') }}" 
      : "{{ route('mentorship.schedule_store') }}";
    
    $.ajax({
      url: url,
      type: 'POST',
      data: formData,
      success: function(response) {
        // Hide modal
        $('#scheduleModal').modal('hide');
        
        // Show success message with SweetAlert
        releaseAlert('success', {
          message: currentScheduleId 
            ? '{{ $l == 'en' ? 'Schedule updated successfully' : 'تم تحديث الجدول بنجاح' }}' 
            : '{{ $l == 'en' ? 'Schedule added successfully' : 'تم إضافة الجدول بنجاح' }}',
          confirmText: '{{ __('admin.OK') }}',
          confirmColor: 'primary'
        });
        
        // Update table in real-time
        if (currentScheduleId) {
          updateScheduleRowInTable(currentScheduleId, response.schedule || formData);
        } else {
          addScheduleRowToTable(response.schedule || formData);
        }
      },
      error: function(xhr) {
        let errors = {};
        if (xhr.responseJSON && xhr.responseJSON.errors) {
          errors = xhr.responseJSON.errors;
        }
        
        // Display validation errors
        displayValidationErrors(errors);
        
        // Show error with SweetAlert
        releaseAlert('danger', {
          title: '{{ $l == 'en' ? 'Error saving schedule' : 'خطأ في حفظ الجدول' }}',
          message: xhr.responseJSON?.message || '{{ $l == 'en' ? 'Please check your input and try again' : 'يرجى التحقق من المدخلات والمحاولة مرة أخرى' }}',
          confirmText: '{{ __('admin.OK') }}',
          confirmColor: 'primary'
        });
      },
      complete: function() {
        // Reset button state
        submitBtn.prop('disabled', false);
        spinner.addClass('d-none');
      }
    });
  });

  // Toggle Schedule Status - using event delegation
  $(document).on('change', '.schedule-status-toggle', function() {
    const checkbox = $(this);
    const id = checkbox.data('id');
    const isActive = checkbox.is(':checked') ? 1 : 0;
    const value = checkbox.data('value');
    const unit = checkbox.data('unit');
    
    $.ajax({
      url: "{{ route('mentorship.schedule_update') }}",
      type: 'POST',
      data: {
        id: id,
        is_active: isActive,
        value: value,
        unit: unit,
        _token: "{{ csrf_token() }}"
      },
      success: function(response) {
        // Update badge in real-time
        const label = checkbox.siblings('.form-check-label').find('.badge');
        if (isActive) {
          label.removeClass('bg-label-secondary').addClass('bg-label-success').text('{{ __("admin.Active") }}');
        } else {
          label.removeClass('bg-label-success').addClass('bg-label-secondary').text('{{ __("admin.Inactive") }}');
        }
        
        // Update data attribute
        checkbox.closest('tr').find('.schedule-edit').data('active', isActive);
      },
      error: function(xhr) {
        // Revert checkbox state
        checkbox.prop('checked', !checkbox.is(':checked'));
        
        releaseAlert('danger', {
          title: '{{ $l == 'en' ? 'Error updating schedule status' : 'خطأ في تحديث حالة الجدول' }}',
          message: xhr.responseJSON?.message || '{{ $l == 'en' ? 'Please try again' : 'يرجى المحاولة مرة أخرى' }}',
          confirmText: '{{ __('admin.OK') }}',
          confirmColor: 'primary'
        });
      }
    });
  });

  // Delete Schedule - using event delegation
  $(document).on('click', '.schedule-delete', function() {
    const id = $(this).data('id');
    const row = schedulesTable ? schedulesTable.row($(this).closest('tr')) : null;
    const value = $(this).closest('tr').find('td:first').text().trim();
    
    releaseAlert('warning', {
      title: "{{ __('admin.SureDelete') }}",
      message: `{{__('admin.SureDelete')}}: <strong>${value}</strong>`,
      confirmText: "{{ __('admin.DYes') }}",
      cancelText: "{{ __('admin.No') }}",
      confirmColor: 'danger'
    }).then((ok) => {
      if (!ok) return;
      
      $.ajax({
        url: "{{ route('mentorship.schedule_destroy') }}",
        type: 'POST',
        data: {
          id: id,
          _token: "{{ csrf_token() }}"
        },
        success: function(response) {
          // Remove row from DataTable
          if (row && schedulesTable) {
            row.remove().draw(false);
          } else {
            $(`#schedule-row-${id}`).remove();
          }
          
          // Show success message
          releaseAlert('success', {
            message: response.message || "{{ $l == 'en' ? 'Schedule deleted successfully' : 'تم حذف الجدول بنجاح' }}",
            confirmText: "{{ __('admin.OK') }}",
            confirmColor: 'primary'
          });
          
          // Check if table is empty and show empty state if needed
          if (schedulesTable && schedulesTable.rows().count() === 0) {
            addEmptyRowToTable();
          }
        },
        error: function(xhr) {
          releaseAlert('danger', {
            title: xhr.responseJSON?.message || '{{ $l == 'en' ? 'Error deleting schedule' : 'خطأ في حذف الجدول' }}',
            confirmText: '{{ __('admin.OK') }}',
            confirmColor: 'primary'
          });
        }
      });
    });
  });

  // Utility Functions
  function clearValidationErrors() {
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.invalid-feedback').text('');
  }

  function displayValidationErrors(errors) {
    clearValidationErrors();
    
    $.each(errors, function(field, messages) {
      const input = $(`#schedule${field.charAt(0).toUpperCase() + field.slice(1)}`);
      const errorDiv = $(`#${field}Error`);
      
      input.addClass('is-invalid');
      errorDiv.text(messages[0]);
    });
  }

  function updateScheduleRowInTable(id, schedule) {
    const row = $(`#schedule-row-${id}`);
    if (row.length && schedulesTable) {
      // Update the row data in DataTable
      const rowData = [
        `<span class="fw-medium">${schedule.value}</span>`,
        `<span class="badge bg-label-info">
          ${schedule.unit === 'hours' ? '{{ $l == 'en' ? 'Hours' : 'ساعات' }}' :
            schedule.unit === 'minutes' ? '{{ $l == 'en' ? 'Minutes' : 'دقائق' }}' :
            schedule.unit === 'days' ? '{{ $l == 'en' ? 'Days' : 'أيام' }}' :
            schedule.unit === 'weeks' ? '{{ $l == 'en' ? 'Weeks' : 'أسابيع' }}' :
            schedule.unit.charAt(0).toUpperCase() + schedule.unit.slice(1)}
        </span>`,
        createStatusColumn(schedule),
        `<small class="text-muted">${new Date().toLocaleDateString()}</small>`,
        createActionColumn(schedule)
      ];
      
      schedulesTable.row(row).data(rowData).draw(false);
      
      // Update data attributes on the new elements
      row.find('.schedule-edit')
        .data('id', schedule.id)
        .data('value', schedule.value)
        .data('unit', schedule.unit)
        .data('active', schedule.is_active);
      row.find('.schedule-delete').data('id', schedule.id);
      row.find('.schedule-status-toggle').data('id', schedule.id).data('value', schedule.value).data('unit', schedule.unit);
    }
  }

  function addScheduleRowToTable(schedule) {
    // Remove empty row if exists
    $('#no-schedules-row').remove();
    
    if (schedulesTable) {
      // Create new row data
      const rowData = [
        `<span class="fw-medium">${schedule.value}</span>`,
        `<span class="badge bg-label-info">${schedule.unit.charAt(0).toUpperCase() + schedule.unit.slice(1)}</span>`,
        createStatusColumn(schedule),
        `<small class="text-muted">${new Date().toLocaleDateString()}</small>`,
        createActionColumn(schedule)
      ];
      
      // Add row to DataTable
      const newRow = schedulesTable.row.add(rowData).draw(false);
      
      // Set the row ID
      $(newRow.node()).attr('id', `schedule-row-${schedule.id}`);
      
      // Update data attributes on the new elements
      $(newRow.node()).find('.schedule-edit')
        .data('id', schedule.id)
        .data('value', schedule.value)
        .data('unit', schedule.unit)
        .data('active', schedule.is_active);
      $(newRow.node()).find('.schedule-delete').data('id', schedule.id);
      $(newRow.node()).find('.schedule-status-toggle').data('id', schedule.id).data('value', schedule.value).data('unit', schedule.unit);
    }
  }

  function createStatusColumn(schedule) {
    const badgeClass = schedule.is_active ? 'bg-label-success' : 'bg-label-secondary';
    const statusText = schedule.is_active ? '{{ __("admin.Active") }}' : '{{ __("admin.Inactive") }}';
    const checked = schedule.is_active ? 'checked' : '';
    
    return `
      <div class="form-check form-switch">
        <input 
          class="form-check-input schedule-status-toggle w-px-30" 
          data-value="${schedule.value}"
          data-unit="${schedule.unit}" 
          data-id="${schedule.id}" 
          ${checked} 
          type="checkbox" 
          id="status_${schedule.id}"
        />
        <label class="form-check-label" for="status_${schedule.id}">
          <span class="badge ${badgeClass}">
            ${statusText}
          </span>
        </label>
      </div>
    `;
  }

  function createActionColumn(schedule) {
    return `
      <div class="btn-group" role="group">
        <button 
          type="button" 
          class="btn btn-sm btn-outline-primary schedule-edit" 
          data-id="${schedule.id}"
          data-value="${schedule.value}"
          data-unit="${schedule.unit}"
          data-active="${schedule.is_active}"
          title="{{ __('admin.Edit') }}"
        >
          <i class="ti ti-edit"></i>
        </button>
        <button 
          type="button" 
          class="btn btn-sm btn-outline-danger schedule-delete" 
          data-id="${schedule.id}"
          title="{{ __('admin.Delete') }}"
        >
          <i class="ti ti-trash"></i>
        </button>
      </div>
    `;
  }

  function addEmptyRowToTable() {
    if (schedulesTable && schedulesTable.rows().count() === 0) {
      const emptyRowData = [
        '', '', 
        `<div class="d-flex flex-column align-items-center py-4">
          <i class="ti ti-calendar-off mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
          <p class="text-muted mb-0">{{ $l == 'en' ? 'No schedules found' : 'لا توجد جداول' }}</p>
          <small class="text-muted">{{ $l == 'en' ? 'Click the Add Schedule button to create your first schedule' : 'انقر فوق زر إضافة جدول لإنشاء جدولك الأول' }}</small>
        </div>`,
        '', ''
      ];
      
      const emptyRow = schedulesTable.row.add(emptyRowData).draw(false);
      $(emptyRow.node()).attr('id', 'no-schedules-row').find('td:first').attr('colspan', '5').addClass('text-center');
    }
  }

  // Export Functions
  window.exportSchedules = function(format) {
    if (!schedulesTable) {
      releaseAlert('danger', {
        title: '{{ $l == 'en' ? 'Export Error' : 'خطأ في التصدير' }}',
        message: '{{ $l == 'en' ? 'No data available to export' : 'لا توجد بيانات متاحة للتصدير' }}',
        confirmText: '{{ __('admin.OK') }}',
        confirmColor: 'primary'
      });
      return;
    }

    // Show loading state on the dropdown button
    const exportBtn = document.getElementById('exportDropdown');
    const originalContent = exportBtn.innerHTML;
    
    if (exportBtn) {
      exportBtn.disabled = true;
      exportBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ $l == 'en' ? 'Exporting...' : 'جاري التصدير...' }}';
    }

    // Use DataTable's export functionality
    try {
      if (format === 'csv') {
        schedulesTable.button('.buttons-csv').trigger();
      } else if (format === 'excel') {
        schedulesTable.button('.buttons-excel').trigger();
      }

      // Show success message
      setTimeout(() => {
        releaseAlert('success', {
          message: format === 'csv' 
            ? '{{ $l == 'en' ? 'CSV file has been downloaded successfully.' : 'تم تنزيل ملف CSV بنجاح.' }}'
            : '{{ $l == 'en' ? 'Excel file has been downloaded successfully.' : 'تم تنزيل ملف Excel بنجاح.' }}',
          confirmText: '{{ __('admin.OK') }}',
          confirmColor: 'primary',
          autoClose: 3000
        });
      }, 500);

    } catch (error) {
      console.error('Export error:', error);
      releaseAlert('danger', {
        title: '{{ $l == 'en' ? 'Export Error' : 'خطأ في التصدير' }}',
        message: '{{ $l == 'en' ? 'Failed to export data. Please try again.' : 'فشل في تصدير البيانات. يرجى المحاولة مرة أخرى.' }}',
        confirmText: '{{ __('admin.OK') }}',
        confirmColor: 'primary'
      });
    }

    // Reset button state
    setTimeout(() => {
      if (exportBtn) {
        exportBtn.disabled = false;
        exportBtn.innerHTML = originalContent;
      }
    }, 1000);
  };
});
</script>
@endpush
