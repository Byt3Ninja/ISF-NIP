@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/tagify/tagify.css" />
@stop

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    {{-- Tasks Count Cards --}}
    @include('admin.tasks.partials.tasks-count-cards')

    {{-- Tasks Table --}}
    @include('admin.tasks.partials.tasks-table')
  </div>
  
  {{-- Add/Edit Task Modal --}}
  @include('admin.tasks.partials.add-task-modal')

  {{-- View Task Modal --}}
  @include('admin.tasks.partials.view-task-modal')
</div>
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
<script src="/vuexy/assets/vendor/libs/tagify/tagify.js"></script>
@stop

@section('pagescripts')
<script>
// Global variables
window.tasks = @json($tasks);
window.startups = @json($startups);

// Helper: SweetAlert Wrapper
const showAlert = (title, text, icon, confirmBtnText = 'Yes', showCancel = true) => {
  return Swal.fire({
    title, text, icon, showCancelButton: showCancel, confirmButtonText: confirmBtnText,
    cancelButtonText: icon === 'error' ? '' : '{{ __('admin.No') }}', customClass: { confirmButton: `btn btn-${icon === 'warning' ? 'danger' : 'primary'} me-3`, cancelButton: showCancel ? 'btn btn-label-secondary' : '' }, buttonsStyling: false
  });
};

$(function () {
  // Constants & Config
  const dt_task_table = $('.datatables-tasks');
  const priorityObj = {
    Low: { title: '{{ __('admin.priorityLow') }}', class: 'bg-label-info' },
    Medium: { title: '{{ __('admin.priorityMedium') }}', class: 'bg-label-warning' },
    High: { title: '{{ __('admin.priorityHigh') }}', class: 'bg-label-danger' }
  };
  const statusObj = {
    Pending: { title: '{{ __('admin.boardTitlePending') }}', class: 'bg-label-warning' },
    'In Progress': { title: '{{ __('admin.boardTitleProgress') }}', class: 'bg-label-info' },
    'In Review': { title: '{{ __('admin.boardTitleReview') }}', class: 'bg-label-primary' },
    Completed: { title: '{{ __('admin.boardTitleCompleted') }}', class: 'bg-label-success' }
  };

  // Helper: Export Formatter
  const exportFormat = (inner) => {
    if (inner.length <= 0) return inner;
    const el = $.parseHTML(inner);
    let result = '';
    $.each(el, (index, item) => {
      if (item.classList?.contains('task-title')) {
        // Extract title and description from the task-title structure
        const flexDiv = item.lastChild;
        if (flexDiv) {
          const aTag = flexDiv.firstChild;
          const smallTag = flexDiv.lastChild;
          const title = aTag?.textContent?.trim() || '';
          const description = smallTag?.textContent?.trim() || '';
          result += title ? title : '';
          result += description ? (title ? ' - ' : '') + description : '';
        }
      } else {
        result += item.innerText || item.textContent || '';
      }
    });
    return result;
  };

  // Initialize DataTable
  if (dt_task_table.length) {
    if (window.dt_task) {
      window.dt_task.destroy();
    }
    
    window.dt_task = dt_task_table.DataTable({
      processing: true,
      responsive: true,
      data: tasks,
      columns: [{ data: null }, { data: 'title' }, { data: 'program' }, { data: 'assignees' }, { data: 'priority' }, { data: 'overallStatus' }, { data: 'dueDate' }, { data: 'id' }],
      columnDefs: [
        {
          className: 'control', orderable: false, searchable: false, responsivePriority: 2, targets: 0,
          render: () => ''
        },
        {
          targets: 1, responsivePriority: 4,
          render: (data, type, full) => `
            <div class="d-flex justify-content-start align-items-start task-title" style="max-width: 250px;">
              <div class="d-flex flex-column overflow-y-hidden" style="width: 100%; min-width: 0; max-height: 80px;">
                <a href="javascript:;" class="text-heading fw-medium line-clamp-1"><span>${full.title}</span></a>
                <small class="text-muted line-clamp-3">${full.description}</small>
              </div>
            </div>`
        },
        { targets: 2, render: (data, type, full) => `<span class="text-heading">${full.program.title}</span>` },
        {
          targets: 3,
          render: (data, type, full) => {
            const assignees = full.assignees;
            let html = assignees.slice(0, 2).map(a => {
              const cls = statusObj[a.status]?.class || 'bg-label-secondary';
              return `<span class="badge ${cls} me-1 mb-1" title="${a.name} - ${a.status}">${a.name}</span>`;
            }).join('');
            if (assignees.length > 2) html += `<span class="badge bg-label-secondary">+${assignees.length - 2}</span>`;
            return html;
          }
        },
        { targets: 4, render: (d, t, f) => `<span class="badge ${priorityObj[f.priority].class}" text-capitalized>${priorityObj[f.priority].title}</span>` },
        { targets: 5, render: (d, t, f) => `<span class="badge ${statusObj[f.overallStatus].class}" text-capitalized>${statusObj[f.overallStatus].title}</span>` },
        { targets: 6, render: (d, t, f) => `<span class="text-heading">${f.dueDate}</span>` },
        {
          targets: -1, searchable: false, orderable: false,
          render: () => `
            <div class="d-flex align-items-center">
              <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill task-view"><i class="ti ti-eye ti-md"></i></a>
              <!-- <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill task-edit"><i class="ti ti-pencil ti-md"></i></a> -->
              <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-record"><i class="ti ti-trash ti-md"></i></a>
            </div>`
        }
      ],
      order: [[1, 'desc']],
      dom: '<"row"<"col-12 col-lg-9 d-flex flex-wrap align-items-center ps-0 ps-lg-3"l><"col-12 col-lg-3 dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 pe-0 pe-lg-2"fB>>t<"row px-6"<"col-sm-12 col-lg-6"i><"col-sm-12 col-lg-6 d-flex justify-content-end"p>>',
      language: {
        url: document.dir == 'rtl'
          ? "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
          : "https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json",
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: '{{ __('admin.Search') }}...',
      },
      buttons: [{
        extend: 'collection', className: 'btn btn-label-secondary dropdown-toggle mx-4 waves-effect waves-light',
        text: '<i class="ti ti-file-export me-2 ti-xs"></i> {{ __('admin.Export') }}',
        buttons: [
          { extend: 'csv', text: '<i class="ti ti-file-text me-2"></i>Csv', className: 'dropdown-item', exportOptions: { columns: [1, 2, 3, 4, 5, 6], format: { body: exportFormat } } },
          { extend: 'excel', text: '<i class="ti ti-file-spreadsheet me-2"></i>Excel', className: 'dropdown-item', exportOptions: { columns: [1, 2, 3, 4, 5, 6], format: { body: exportFormat } } }
        ]
      }],
      initComplete: function () {
        const filterContainer = $('<div class="d-flex flex-wrap flex-md-nowrap gap-3 ms-3"></div>');
        $('.dataTables_length').after(filterContainer);

        const addDropdownFilter = (colIndex, id, placeholder) => {
          this.api().columns(colIndex).every(function () {
            const column = this;
            const select = $(`<select id="${id}" class="form-select text-capitalize" style="min-width: ${id === 'taskProgram' ? '200' : '150'}px;"><option value=""> ${placeholder} </option></select>`)
              .appendTo(filterContainer)
              .on('change', function () {
                const val = $.fn.dataTable.util.escapeRegex($(this).val());
                column.search(val ? `^${val}$` : '', true, false).draw();
              });
            
            // Program is object, others strings
            column.data().unique().sort().each(d => select.append(`<option value="${d.title || d}">${d.title || d}</option>`));
          });
        };

        addDropdownFilter(2, 'taskProgram', '{{ __('admin.Select Program') }}');
        addDropdownFilter(4, 'taskPriority', '{{ __('admin.select_priority') }}');
        addDropdownFilter(5, 'taskStatus', '{{ __('admin.Select Status') }}');

        // Styles fix
        setTimeout(() => {
          $('.dataTables_filter .form-control').removeClass('form-control-sm');
          $('.dataTables_length .form-select').removeClass('form-select-sm');
        }, 300);
      }
    });
  }

  // Delete Record
  $('.datatables-tasks tbody').on('click', '.delete-record', function () {
    const $btn = $(this);
    let $row = $btn.closest('tr');
    
    // Traverse up to find the data row if nested in responsive child row
    while ($row.length && !window.dt_task.row($row).data()) {
      $row = $row.prev();
    }
    
    if (!$row.length) {
      Swal.fire('Error!', 'Could not identify the task. Please try again.', 'error');
      return;
    }
    
    const rowData = window.dt_task.row($row).data();
    if (!rowData || !rowData.id) {
      Swal.fire('Error!', 'Could not identify the task. Please try again.', 'error');
      return;
    }
    
    const taskId = rowData.id;
    showAlert('{{ __('admin.SureDelete') }}', '{{ __('admin.Recover') }}', 'warning', '{{ __('admin.DYes') }}')
      .then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `/dashboard/tasks/${taskId}`,
            method: 'DELETE',
            dataType: 'json',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              window.dt_task.row($row).remove().draw();
              showToast({type: 'success', message: '{{ __('admin.Task_deleted_successfully') }}'});
            },
            error: function(xhr) {
              const errorMsg = xhr.responseJSON?.message;
              showToast({type: 'error', message: errorMsg ? errorMsg : '{{ __('admin.unexpected_error') }}'});
            }
          });
        }
      });
  });

  // View Task
  $('.datatables-tasks tbody').on('click', '.task-view', function () {
    const $btn = $(this);
    let $row = $btn.closest('tr');
    
    // Traverse up to find the data row if nested in responsive child row
    while ($row.length && !window.dt_task.row($row).data()) {
      $row = $row.prev();
    }
    
    if (!$row.length) {
      showToast({type: 'error', message: 'Could not load task details. Please try again later.'});
      return;
    }
    
    const data = window.dt_task.row($row).data();
    if (!data || !data.assignees) {
      showToast({type: 'error', message: 'Could not load task details. Please try again later.'});
      return;
    }
    
    const assigneesHtml = data.assignees.map(a => {
      // Find startup data to check for logo
      const startup = startups.find(s => s.id === a.id);
      const logoUrl = startup?.logo;
      let avatarHtml;

      if (logoUrl) {
        avatarHtml = `<div class="avatar avatar-sm me-2">
          <img src="${logoUrl}" alt="logo" class="rounded-circle" onerror="this.parentElement.innerHTML='<span class="avatar-initial rounded-circle bg-label-primary">${a.name.split(' ').map(n => n[0]).join('').toUpperCase()}</span>'" />
        </div>`;
      } else {
        avatarHtml = `<span class="avatar avatar-sm me-2"><span class="avatar-initial rounded-circle bg-label-primary">${a.name.split(' ').map(n => n[0]).join('').toUpperCase()}</span></span>`;
      }

      return `
        <div class="d-flex align-items-center justify-content-between mb-2 p-2 border rounded">
          <div class="d-flex align-items-center">
            ${avatarHtml}
            <div><strong>${a.name}</strong><br><small class="text-muted">{{ __('admin.last_updated') }}: ${a.lastUpdated}</small></div>
          </div>
          <div class="d-flex align-items-center gap-3 text-end">
            <div><span>Status:</span> <span class="badge ${statusObj[a.status].class}">${statusObj[a.status].title}</span></div>
            <!-- <button class="btn btn-sm btn-outline-primary trigger-company" data-company="${a.name}" data-current-status="${a.status}"><i class="ti ti-send me-1"></i>{{ __('admin.trigger_update') }}</button> -->
          </div>
        </div>`;
    }).join('');

    const tagsHtml = data.tags?.length ? `<div class="col-12 mb-3"><strong>{{ __('admin.tags') }}</strong><div class="d-flex flex-wrap gap-2 mt-2">${data.tags.map(tag => `<span class="badge bg-label-info">${tag}</span>`).join('')}</div></div>` : '';

    $('#task-details-content').html(`
      <div class="row">
        <div class="col-12 mb-4"><h4>${data.title}</h4><p class="text-muted">${data.description}</p></div>
        <div class="col-lg-6 mb-3"><strong>{{ __('admin.Program') }}:</strong> ${data.program.title}</div>
        <div class="col-lg-6 mb-3"><strong>{{ __('admin.Priority') }}:</strong> <span class="badge ${priorityObj[data.priority].class} ms-2">${priorityObj[data.priority].title}</span></div>
        <div class="col-lg-6 mb-3"><strong>{{ __('admin.overall_status') }}:</strong> <span class="badge ${statusObj[data.overallStatus].class} ms-2">${statusObj[data.overallStatus].title}</span></div>
        <div class="col-lg-6 mb-3"><strong>{{ __('admin.Due_Date') }}:</strong> ${data.dueDate}</div>
        <div class="col-12 mb-3 mt-6"><strong>{{ __('admin.startup_statuses') }}:</strong><div class="mt-2">${assigneesHtml}</div></div>
        ${tagsHtml}
      </div>
    `);
    // Store task ID in modal for trigger-company button
    $('#viewTaskModal').data('taskId', data.id);
    $('#viewTaskModal').modal('show');
  });

  // Edit Task
  $('.datatables-tasks tbody').on('click', '.task-edit', function () {
    const $btn = $(this);
    let $row = $btn.closest('tr');

    // Traverse up to find the data row if nested in responsive child row
    while ($row.length && !window.dt_task.row($row).data()) {
      $row = $row.prev();
    }

    if (!$row.length) {
      showToast({type: 'error', message: 'Could not load task. Please try again later.'});
      return;
    }

    const data = window.dt_task.row($row).data();
    if (!data || !data.id) {
      showToast({type: 'error', message: 'Could not load task. Please try again later.'});
      return;
    }

    $('#task-title').val(data.title);
    $('#task-description').val(data.description);
    $('#task-priority').val(data.priority).trigger('change');
    $('#task-status').val(data.overallStatus).trigger('change');
    $('#task-due-date').val(data.dueDate);

    // Map assignees by ID matching
    const assigneeValues = data.assignees
      .map(a => a.id)
      .filter(id => id !== null && id !== undefined);

    // Set the program first to establish edit mode, then populate assignees
    $('#task-program').val(data.program.id).trigger('change');

    // Now populate all startups (program change handler will show all in edit mode)
    const assigneeSelect = $('#task-assignees');
    assigneeSelect.empty();
    if (startups && startups.length > 0) {
      startups.forEach(startup => {
        assigneeSelect.append(`<option value="${startup.id}">${startup.name ?? startup.name_en}</option>`);
      });
    }

    // Set assignees after populating options
    if (assigneeValues.length > 0) {
      assigneeSelect.val(assigneeValues).trigger('change');
    }

    // Set tags in Tagify
    tagifyInstance.removeAllTags();
    if (data.tags && Array.isArray(data.tags)) {
      tagifyInstance.addTags(data.tags);
    }

    $('#addTaskModalLabel').text('{{ __('admin.edit_task') }}');
    $('.data-submit').text('{{ __('admin.Update') }}');
    $('#addNewTaskForm').data('edit-row', $row);
    $('#addTaskModal').modal('show');
  });

  // Trigger company update
  $(document).on('click', '.trigger-company', function () {
    const { company, currentStatus } = $(this).data();
    const $btn = $(this);
    const $modal = $('#viewTaskModal');
    
    // Get task ID from modal data
    const taskId = $modal.data('taskId');
    if (!taskId) {
      showToast({type: 'error', message: 'Could not load task. Please try again later.'});
      return;
    }
    
    showAlert('{{ __('admin.trigger_status_update') }}', `{{ __('admin.send_notification_to') }} ${company} {{ __('admin.to_update_task_status') }}?`, 'question', '{{ __('admin.send_trigger') }}')
      .then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `/dashboard/tasks/${taskId}/trigger-update`,
            method: 'POST',
            dataType: 'json',
            data: {
              company: company,
              currentStatus: currentStatus
            },
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              showToast({type: 'success', message: `{{ __('admin.notification_sent_to') }} ${company}.`});
              $btn.prop('disabled', true).html('<i class="ti ti-clock me-1"></i>{{ __('admin.triggered') }}');
            },
            error: function(xhr) {
              const errorMsg = xhr.responseJSON?.message;
              console.error('Failed to trigger update:', xhr);
              showToast({type: 'error', message: errorMsg ? errorMsg : '{{ __('admin.unexpected_error') }}'});
            }
          });
        }
      });
  });
});

// Initialize Tagify for tags input
const tagifyInput = document.getElementById('task-tags');
const tagifyWhitelist = tagifyInput.dataset.whitelist ? JSON.parse('[' + tagifyInput.dataset.whitelist + ']') : [];

const tagifyInstance = new Tagify(tagifyInput, {
  whitelist: tagifyWhitelist,
  maxTags: 50,
  dropdown: {
    maxItems: 20,
    classname: 'tags-look',
    enabled: 0,
    closeOnSelect: false
  },
  enforceWhitelist: false,
  placeholder: tagifyInput.placeholder
});

// Validation & Form Logic
(function () {
  const form = document.getElementById('addNewTaskForm');
  
  // Validation Config
  const fv = FormValidation.formValidation(form, {
    fields: {
      taskTitle: { validators: { notEmpty: { message: '{{ __('admin.please_enter_task_title') }}' }, stringLength: { max: 200, message: '{{ __('admin.task_title_max_length') }}' } } },
      taskDescription: { validators: { stringLength: { max: 2000, message: '{{ __('admin.task_description_max_length') }}' } } },
      taskProgram: { validators: { notEmpty: { message: '{{ __('admin.please_select_program') }}' } } },
      taskPriority: { validators: { notEmpty: { message: '{{ __('admin.please_select_priority') }}' } } },
      taskStatus: { validators: { notEmpty: { message: '{{ __('admin.please_select_status') }}' } } },
      'taskAssignees[]': { validators: { notEmpty: { message: '{{ __('admin.please_select_assignee') }}' } } }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '', rowSelector: '.mb-4' }),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  });

  const resetForm = () => {
    // Reset form validation first
    fv.resetForm(true);

    // Reset all form fields
    form.reset();

    // Reset date picker
    $('#task-due-date').val('');

    // Reset tags
    tagifyInstance.removeAllTags();

    // Reset select2 elements (need to trigger change for proper display)
    $('#task-program').val('').trigger('change');
    $('#task-priority').val('').trigger('change');
    $('#task-status').val('').trigger('change');
    $('#task-assignees').val('').trigger('change');

    // Reset validation again after select2 triggers (to clear any validation errors)
    setTimeout(() => fv.resetForm(true), 10);

    // Remove edit mode data
    $(form).removeData('edit-row');

    // Update modal labels
    $('#addTaskModalLabel').text('{{ __('admin.add_new_task') }}');
    $('.data-submit').text('{{ __('admin.Submit') }}');
  };

  // Submit Handler
  $('.data-submit').on('click', function (e) {
    e.preventDefault();
    fv.validate().then(status => {
      if (status !== 'Valid') return;
      
      const formData = new FormData(form);

      // Get assignee IDs directly from the select element
      const assigneeIds = $('#task-assignees').val() || [];
      const selectedAssignees = assigneeIds.map(val => ({
        company: $(`#task-assignees option[value="${val}"]`).text(),
        status: 'Pending',
        lastUpdated: new Date().toISOString().split('T')[0]
      }));
      
      // Get tags from Tagify
      const tags = tagifyInstance.value.map(tag => tag.value || tag);
      
      const taskPayload = {
        title: formData.get('taskTitle'),
        description: formData.get('taskDescription'),
        program_id: formData.get('taskProgram') ? Number(formData.get('taskProgram')) : null,
        assignees: assigneeIds.map(val => +val),
        priority: formData.get('taskPriority'),
        status: formData.get('taskStatus'),
        due_date: formData.get('taskDueDate'),
        tags: tags
      };

      if (!window.dt_task) return Swal.fire('{{ __('admin.error') }}', '{{ __('admin.table_not_initialized') }}', 'error');

      const editRow = $(form).data('edit-row');
      const taskId = editRow ? window.dt_task.row(editRow).data().id : null;
      const method = editRow ? 'PUT' : 'POST';
      const url = editRow ? `/dashboard/tasks/${taskId}` : '/dashboard/tasks';

      $.ajax({
        url: url,
        method: method,
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(taskPayload),
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
          if (editRow) {
            // Update the existing row with the new data
            const taskObj = {
              id: response.data.id || taskId,
              title: response.data.title,
              description: response.data.description,
              program: { id: response.data.program_id, title: $('#task-program option:selected').text() },
              assignees: selectedAssignees.length ? selectedAssignees : [{ company: 'Current User', status: 'Pending', lastUpdated: new Date().toISOString().split('T')[0] }],
              priority: response.data.priority,
              overallStatus: response.data.status,
              dueDate: response.data.due_date,
              tags: response.data.tags || []
            };
            window.dt_task.row(editRow).data(taskObj).draw();
            showToast({type: 'success', message: '{{ __('admin.Task_updated_successfully') }}'});
          } else {
            // Add new task to the table
            const taskObj = {
              id: response.data.id,
              title: response.data.title,
              description: response.data.description,
              program: { id: response.data.program_id, title: $('#task-program option:selected').text() },
              assignees: selectedAssignees.length ? selectedAssignees : [{ company: 'Current User', status: 'Pending', lastUpdated: new Date().toISOString().split('T')[0] }],
              priority: response.data.priority,
              overallStatus: response.data.status,
              dueDate: response.data.due_date,
              tags: response.data.tags || []
            };
            window.dt_task.row.add(taskObj).draw('page');
            showToast({type: 'success', message: '{{ __('admin.Task_added_successfully') }}'});
          }
          $('#addTaskModal').modal('hide');
          resetForm();
        },
        error: function(xhr) {
          console.error('Failed to save task:', xhr);
          const errorMsg = xhr.responseJSON?.message;
          showToast({type: 'error', message: errorMsg ? errorMsg : '{{ __('admin.unexpected_error') }}'});
        }
      });
    });
  });

  // Modal Close & Init
  $('#addTaskModal').on('hidden.bs.modal', resetForm);
  form.addEventListener('submit', e => e.preventDefault());
  document.querySelector('#task-due-date')?.flatpickr({ enableTime: false, dateFormat: 'Y-m-d', minDate: 'today' });

  // Clear validation errors for select2 fields on change
  $('#task-program, #task-priority, #task-status').on('select2:select select2:unselect change', function () {
    fv.revalidateField(this.name);
  });
  
  // Filter assignees when program is changed
  $('#task-program').on('select2:select change', function () {
    const programId = $(this).val();
    const assigneeSelect = $('#task-assignees');
    const currentValues = assigneeSelect.val() || [];

    // Check if we're in edit mode (modal has edit-row data)
    const isEditMode = $('#addNewTaskForm').data('edit-row') !== undefined;

    let assignees;
    if (isEditMode) {
      // In edit mode, show all startups to allow changing assignees
      assignees = startups || [];
    } else {
      // In add mode, filter by program
      assignees = startups ? startups.filter(startup => startup.cycle === +programId) : [];
    }

    // Clear and rebuild options
    assigneeSelect.empty();
    assignees.forEach(assignee => {
      assigneeSelect.append(`<option value="${assignee.id}">${assignee.name ?? assignee.name_en}</option>`);
    });

    // Keep previously selected values if they're still available
    const validValues = currentValues.filter(val => assignees.some(a => a.id === val));
    assigneeSelect.val(validValues).trigger('change');

    // Revalidate assignees field
    fv.revalidateField('taskAssignees[]');
  });

  // Validate assignees on change
  $('#task-assignees').on('select2:select select2:unselect change', function () {
    fv.revalidateField(this.name);
  });

// File Upload (Multiple)
$('#task-upload').on('change', function () {
  const files = Array.from(this.files);
  if (!files.length) return;

  const allowedExt = ['pdf', 'doc', 'docx', 'ppt', 'xls', 'png', 'jpeg', 'jpg'];
  const maxSize = 10 * 1024 * 1024; // 10MB
  const errors = [];
  const validFiles = [];

  files.forEach(file => {
    const ext = file.name.split('.').pop().toLowerCase();

    if (!allowedExt.includes(ext)) {
      errors.push(`{{ __('admin.invalid_file_format') }} (${file.name})`);
      return;
    }

    if (file.size > maxSize) {
      errors.push(`{{ __('admin.file_exceeds_limit') }} (${file.name})`);
      return;
    }

    validFiles.push(file);
  });

  if (errors.length) {
    $('#fileError').html(errors.join('<br>')).show();
    $('#fileName').hide();
    $(this).val('');
    return;
  }

  // Success UI
  $('#fileError').hide();
  $('#fileName')
    .html(validFiles.map(f =>
      `<div><i class="ti ti-paperclip me-1"></i>${f.name}</div>`
    ).join(''))
    .show();
  });
})();
</script>
@stop
