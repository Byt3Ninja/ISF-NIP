@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
@stop

@section('content')

{{-- @include('admin.tasks.partials.tasks-count-cards') --}}

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center border-bottom">
    <h5 class="card-title mb-0">{{ __('admin.tasks') }}</h5>
    <div class="d-flex align-items-center gap-2">
      <x-export-dropdown callback="exportCycles" />
      @if(!auth()->user()->hasRole('registered'))
        <button class="btn btn-primary ms-2" data-bs-toggle="offcanvas" data-bs-target="#addTaskModal">
          <i class="ti ti-plus"></i>
          <span class="d-none d-md-inline ms-md-2">{{ __('admin.add_new_task') }}</span>
        </button>
      @endif
    </div>
  </div>
  <div class="card-datatable table-responsive p-0">
    <table id="cyclesTable" class="table align-middle mb-0" style="width:100%">
      <thead class="table-light">
        <tr>
          <th>{{ __('admin.Program') }}</th>
          <th>{{ __('admin.Total') }}</th>
          <th>{{ __('admin.Pending') }}</th>
          <th>{{ __('admin.In_Progress') }}</th>
          <th>{{ __('admin.In_Review') }}</th>
          <th>{{ __('admin.Completed') }}</th>
          <th class="text-center">{{ __('admin.actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach($cycles as $cycle)
          @php
            $total      = ($cycle['pending'] ?? 0) + ($cycle['in_progress'] ?? 0) + ($cycle['in_review'] ?? 0) + ($cycle['completed'] ?? 0) + ($cycle['archived'] ?? 0);
          @endphp
          <tr>
            <td><strong>{{ $cycle['title'] }}</strong></td>
            <td><span class="badge bg-label-info fw-bold">{{ $total }}</span></td>
            <td><span class="badge bg-label-info fw-bold">{{ $cycle['pending'] ?? 0 }}</span></td>
            <td><span class="badge bg-label-info fw-bold">{{ $cycle['in_progress'] ?? 0 }}</span></td>
            <td><span class="badge bg-label-info fw-bold">{{ $cycle['in_review'] ?? 0 }}</span></td>
            <td><span class="badge bg-label-info fw-bold">{{ $cycle['completed'] ?? 0 }}</span></td>
            <td class="text-center">
              <a
                href="{{ route('mentorshipTasks.index', ['cycle_id' => $cycle['id']]) }}"
                class="btn btn-sm btn-primary"
              >
                <i class="ti ti-eye me-2"></i> {{ __('admin.view_tasks') }}
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@if(!auth()->user()->hasRole('registered'))
@include('admin.tasks.partials.add-task-offcanvas')
@endif
@endsection

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script>
  $(document).ready(function () {

    const table = $('#cyclesTable').DataTable({
      ...getSharedDataTableConfig({ title: 'Session_Tasks', actionsIndex: 6 }),
      dom: '<"row"<"col-12 col-md-9 d-flex flex-wrap flex-md-nowrap align-items-center"l><"col-12 col-md-3 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
      initComplete: function () {
        const api = this.api();

        const $filterContainer = $('<div class="d-flex flex-wrap gap-2 mt-3 mt-md-0 ms-md-8"></div>');

        $('.dataTables_length').after($filterContainer);
        const addDropdownFilter = (colIndex, id, placeholder, width = 240) => {
          api.columns(colIndex).every(function () {
            const column = this;

            const $select = $(
              `<select id="${id}" class="form-select" style="min-width:${width}px">
                <option value="">${placeholder}</option>
              </select>`
            )
              .appendTo($filterContainer)
              .on('change', function () {
                const val = $(this).val().trim().toLowerCase();

                if (!val) {
                  column.search('', true, false).draw();
                  return;
                }

                const escaped = $.fn.dataTable.util.escapeRegex(val);
                column.search(`^\\s*${escaped}\\s*$`, true, false).draw();
              });

            column
              .data()
              .unique()
              .sort()
              .each(function (d) {
                const text = $('<div>').html(d).text().replace(/\s+/g, ' ').trim();
                if (text) {
                  $select.append(
                    `<option value="${text.toLowerCase()}">${text}</option>`
                  );
                }
              });
          });
        };

        addDropdownFilter(0, 'cycleFilter', '{{ __("admin.All_Programs") }}', 240);

        setTimeout(() => {
          $('.dataTables_filter .form-control').removeClass('form-control-sm');
          $('.dataTables_length .form-select').removeClass('form-select-sm');
        }, 200);
      },
      language: {
        url:
          document.dir === 'rtl'
            ? 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
            : 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json',
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: '{{ __('admin.Search') }}...',
      },
    });

    // Handle Export
    window.exportCycles = function (format) {
      const btnMap = {
        csv:   '.buttons-csv',
        excel: '.buttons-excel',
      };
      const selector = btnMap[format];
      if (selector) {
        table.button(selector).trigger();
      }
    };  
  });

  // ─── Validation & Form Logic ──────────────────────────────────────────────────
  (function () {
    const form = document.getElementById('addNewTaskForm');

    const fv = FormValidation.formValidation(form, {
      fields: {
        taskTitle:       { validators: { notEmpty: { message: '{{ __('admin.please_enter_task_title') }}' }, stringLength: { max: 200, message: '{{ __('admin.task_title_max_length') }}' } } },
        taskDescription: { validators: { stringLength: { max: 2000, message: '{{ __('admin.task_description_max_length') }}' } } },
        taskProgram:     { validators: { notEmpty: { message: '{{ __('admin.please_select_program') }}' } } },
        taskPriority:    { validators: { notEmpty: { message: '{{ __('admin.please_select_priority') }}' } } },
        taskStatus:      { validators: { notEmpty: { message: '{{ __('admin.please_select_status') }}' } } },
        'taskAssignees[]': { validators: { notEmpty: { message: '{{ __('admin.please_select_assignee') }}' } } }
      },
      plugins: {
        trigger:    new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '', rowSelector: '.mb-4' }),
        autoFocus:  new FormValidation.plugins.AutoFocus(),
      },
      init: instance => {
        instance.on('plugins.message.placed', function (e) {
          if (e.element.parentElement.classList.contains('input-group')) {
            e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
          }
        });
      }
    });

    const resetForm = () => {
      fv.resetForm(true);
      $('.data-submit').prop('disabled', false);
      form.reset();
      $('#task-due-date').val('');
      ['#task-program', '#task-priority', '#task-status', '#task-assignees'].forEach(sel =>
        $(sel).val('').trigger('change')
      );
      setTimeout(() => fv.resetForm(true), 10);
      $(form).removeData('edit-row');
      $('#existing-attachments-list').empty();
      $('#existing-attachments').hide();
      $('#addTaskModalLabel').text('{{ __('admin.add_new_task') }}');
      $('.data-submit').text('{{ __('admin.Submit') }}');
    };

    // ── Submit ──────────────────────────────────────────────────────────────
    let isSubmitting = false;
    $('.data-submit').on('click', function (e) {
      e.preventDefault();
      if (isSubmitting) return;

      fv.validate().then(status => {
        if (status !== 'Valid') return;

        isSubmitting = true;
        const $btn = $('.data-submit');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>{{ __('admin.submit') }}');

        const formData = new FormData(form);

        // Append normalised field names
        formData.set('title',       formData.get('taskTitle'));
        formData.set('description', formData.get('taskDescription'));
        formData.set('program_id',  formData.get('taskProgram') ? Number(formData.get('taskProgram')) : null);
        formData.set('priority',    formData.get('taskPriority'));
        formData.set('status',      formData.get('taskStatus'));
        formData.set('due_date',    formData.get('taskDueDate'));
        ['taskTitle','taskDescription','taskProgram','taskPriority','taskStatus','taskDueDate'].forEach(k => formData.delete(k));

        // Assignees — delete the form field name and re-append as 'assignees[]'
        formData.delete('taskAssignees[]');
        ($('#task-assignees').val() || []).forEach(id => formData.append('assignees[]', id));

        const $editRow = $(form).data('edit-row');
        const isEdit   = !!$editRow;

        if (isEdit && !window.dt_task) {
          isSubmitting = false;
          $btn.prop('disabled', false).html('{{ __('admin.Submit') }}');
          return releaseAlert('danger', {
            title: '{{ __('admin.error') }}',
            message: '{{ __('admin.table_not_initialized') }}'
          });
        }

        const taskId = isEdit ? window.dt_task.row($editRow).data().id : null;
        const url    = isEdit ? `/dashboard/tasks/${taskId}` : '/dashboard/tasks';

        if (isEdit) formData.append('_method', 'PUT');

        $.ajax({
          url,
          method: 'POST',
          dataType: 'json',
          processData: false,
          contentType: false,
          data: formData,
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          success: function (response) {
            if (isEdit) {
              const rd = response.data;
              const taskObj = {
                id:            rd.id,
                title:         rd.title,
                description:   rd.description,
                creator:       rd.creator ? {
                  id:          rd.creator.id,
                  name:        rd.creator.name || ((rd.creator.first_name_en ?? '') + ' ' + (rd.creator.last_name_en ?? '')).trim() || window.authUser.name,
                  role:        rd.creator.roles?.[0]?.name ?? window.authUser.role,
                  avatar:      rd.creator.image_url ?? window.authUser.avatar,
                  profile_url: `/dashboard/users/${rd.creator.id}`
                } : window.authUser,
                program:       { id: rd.program.id, title: rd.program.title },
                assignees:     rd.assignees.map(a => ({
                  id:          a.id,
                  name:        a.name || a.name_en || a.name_ar || 'Unknown',
                  status:      a.pivot?.status?.toLowerCase() || 'pending',
                  lastUpdated: a.pivot?.last_updated ? a.pivot.last_updated.split(' ')[0] : 'N/A',
                  logo:        a.logo || null,
                  profile_url: `/dashboard/companyProfile/${a.id}`
                })),
                priority:      rd.priority,
                overallStatus: rd.status,
                dueDate:       rd.due_date ? moment(rd.due_date, 'YYYY-MM-DD').format('YYYY-MM-DD') : 'N/A',
                attachments:   rd.attachments || [],
                createdAt:     rd.created_at ? Math.floor(new Date(rd.created_at).getTime() / 1000) : Math.floor(Date.now() / 1000)
              };
              window.dt_task.row($editRow).data(taskObj).draw(false);
              toastr.success('{{ __('admin.Task_updated_successfully.') }}');
              bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('addTaskModal')).hide();
            } else {
              toastr.success('{{ __('admin.Task_added_successfully.') }}');
              bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('addTaskModal')).hide();
              setTimeout(() => window.location.reload(), 1500);
            }
          },
          error: xhr => toastr.error(xhr.responseJSON?.message || '{{ __('admin.unexpected_error') }}'),
          complete: () => {
            isSubmitting = false;
            $('.data-submit').prop('disabled', false).html('{{ __('admin.Submit') }}');
          }
        });
      });
    });

    // ── Modal events ────────────────────────────────────────────────────────
    $('#addTaskModal').on('hidden.bs.offcanvas', resetForm);
      form.addEventListener('submit', e => e.preventDefault());
      document.querySelector('#task-due-date')?.flatpickr({ enableTime: false, dateFormat: 'Y-m-d', minDate: 'today' });

      // ── Select2 validation triggers ─────────────────────────────────────────
      $('#task-program, #task-priority, #task-status').on('select2:select select2:unselect change', function () {
        fv.revalidateField(this.name);
      });
      $('#task-assignees').on('select2:select select2:unselect change', function () {
        fv.revalidateField(this.name);
      });

      // ── Filter assignees by program (add mode) / show all (edit mode) ────────
      $('#task-program').on('select2:select change', function () {
        const programId  = $(this).val();
        const $sel       = $('#task-assignees');
        const currValues = $sel.val() || [];
        const isEditMode = $('#addNewTaskForm').data('edit-row') !== undefined;

        const assignees = isEditMode
          ? (startups || [])
          : (startups || []).filter(s => s.cycle === +programId);

        $sel.empty();
        assignees.forEach(a => $sel.append(`<option value="${a.id}">${a.name ?? a.name_en}</option>`));
        $sel.val(currValues.filter(v => assignees.some(a => a.id == v))).trigger('change');
        fv.revalidateField('taskAssignees[]');
        $('#assignees-container').show();
      });

      // ── File upload ──────────────────────────────────────────────────────────
      $('#task-upload').on('change', function () {
        const files      = Array.from(this.files);
        if (!files.length) return;

        const maxSize        = 10 * 1024 * 1024;
        const allowedExts    = ['jpeg', 'jpg', 'png', 'pdf', 'docx', 'xlsx', 'pptx'];
        const errors         = [];
        const validFiles     = files.filter(file => {
          const ext = file.name.split('.').pop().toLowerCase();
          if (!allowedExts.includes(ext)) {
            errors.push(`{{ __('admin.unsupported_task_file_type') }}`);
            return false;
          }
          if (file.size > maxSize) {
            errors.push(`{{ __('admin.file_exceeds_10_limit') }}`);
            return false;
          }
          return true;
        });

        if (errors.length) {
          $('#fileError').html(errors.join('<br>')).show();
        } else {
          $('#fileError').hide();
        }

        if (validFiles.length) {
          const dt = new DataTransfer();
          validFiles.forEach(f => dt.items.add(f));
          this.files = dt.files;
          $('#fileName').html(validFiles.map(f => `<div><i class="ti ti-paperclip me-1"></i>${f.name}</div>`).join('')).show();
        } else {
          $(this).val('');
          $('#fileName').hide();
        }
      });

      // ── Select-all toggle ────────────────────────────────────────────────────
      const updateSelectAllText = ($select) => {
        const $link   = $select.closest('#assignees-container, .col-12').find('.select-all-toggle');
        const total   = $select.find('option').length;
        const selected = ($select.val() || []).length;
        $link.text(selected === total && total > 0 ? '{{ __("admin.deselect_all") }}' : '{{ __("admin.select_all") }}');
      };

      $(document).on('click', '.select-all-toggle', function () {
        const $select = $(this).closest('#assignees-container, .col-12').find('select');
        const allOpts = $select.find('option').map((_, el) => el.value).get();
        $select.val(($select.val() || []).length === allOpts.length ? null : allOpts).trigger('change');
      });

      $(document).on('change', '.select2', function () { updateSelectAllText($(this)); });

      // ── Remove existing attachment ───────────────────────────────────────────
      $(document).on('click', '.remove-existing-attachment', function () {
        const $btn     = $(this);
        const filename = $btn.data('filename');
        const taskId   = $btn.data('task-id');

        $.ajax({
          url: `/dashboard/tasks/${taskId}/attachments/${encodeURIComponent(filename)}`,
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          success: function (response) {
            $btn.closest('[data-filename]').remove();
            if (!$('#existing-attachments-list').children().length) $('#existing-attachments').hide();

            // Sync DataTable row data
            const $row = $('#addNewTaskForm').data('edit-row');
            if ($row && window.dt_task) {
              const rowData = window.dt_task.row($row).data();
              if (rowData) {
                rowData.attachments = (rowData.attachments || []).filter(a => a.name !== filename);
                window.dt_task.row($row).data(rowData).draw(false);
              }
            }
            toastr.success(response.message || '{{ __('admin.attachment_removed') }}');
          },
          error: xhr => toastr.error(xhr.responseJSON?.message || '{{ __('admin.unexpected_error') }}')
        });
      });
  })();
  </script>
@endsection
