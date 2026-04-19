@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
@stop

@php
$authUser    = auth()->user();
$_user       = $authUser;
$permissions = [];
$offcanvasTabs = [
    ['target'=>'#vt-details',     'icon'=>'ti-info-circle', 'label'=>__('admin.Details'),     'active'=>true,  'show'=>true],
    ['target'=>'#vt-submissions', 'icon'=>'ti-paperclip',   'label'=>__('admin.Submissions'), 'badge'=>['id'=>'vt-submissions-count','class'=>'bg-label-warning'], 'active'=>false, 'show'=>$authUser->hasAnyRole(['admin','sub-admin','organizationManager','mentor'])],
    ['target'=>'#vt-feedback',    'icon'=>'ti-star',        'label'=>__('admin.feedback'),    'badge'=>['id'=>'vt-feedback-count',   'class'=>'bg-label-primary'], 'active'=>false, 'show'=>true],
    ['target'=>'#vt-comments',    'icon'=>'ti-message',     'label'=>__('admin.Comments'),    'badge'=>['id'=>'vt-comment-count',    'class'=>'bg-label-danger bg-glow'], 'active'=>false, 'show'=>true],
    ['target'=>'#vt-activity',    'icon'=>'ti-activity',    'label'=>__('admin.Activities'),  'badge'=>['id'=>'vt-activity-count',   'class'=>'bg-label-info bg-glow'],   'active'=>false, 'show'=>true],
];
@endphp

@section('content')
<div>
  @include('admin.tasks.partials.tasks-count-cards')
  @include('admin.tasks.partials.tasks-table')
  @include('admin.tasks.partials.add-task-modal')
  @include('admin.tasks.partials.view-task-modal')
  @include('kanban.partials.kanban-vt-offcanvas')
  @include('kanban.partials.kanban-archived-offcanvas')
</div>
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
<script src="/js/kanban/kanban-utils.js"></script>
<script src="/js/kanban/kanban-renderers.js"></script>
@stop

@section('pagescripts')
<script>
// ─── Global Data ────────────────────────────────────────────────────────────
window.tasks    = @json($tasks);
window.startups = @json($startups);
window.authUser = {
  id:          {{ $authUser->id }},
  name:        @json($authUser->name ?? trim($authUser->first_name_en . ' ' . $authUser->last_name_en)),
  role:        @json($authUser->roles->first()->name ?? 'N/A'),
  avatar:      @json($authUser->image_url ?? '/images/placeholder.jpg'),
  profile_url: '/dashboard/show/UserProfile/{{ $authUser->id }}'
};

window.KanbanConfig = {
  profileBaseUrl: '/dashboard/users',
  labels: {
    noAttachments: '{{ __("admin.no_attachments") }}',
    noActivities:  '{{ __("admin.no_activities") }}',
    noComments:    '{{ __("admin.no_comments") }}',
    noFeedback:    '{{ __("admin.no_feedback") }}',
    noSubmissions: '{{ __("admin.no_submissions") }}',
    passTask:      '{{ __("admin.pass_task") }}',
    needsRevision: '{{ __("admin.needs_revision") }}',
    pendingReview: '{{ __("admin.pending_review") }}',
    approved:      '{{ __("admin.approved") }}',
    rejected:      '{{ __("admin.rejected") }}',
    remove:        '{{ __("admin.Remove") }}',
    download:      '{{ __("admin.Download") }}',
  },
  priorityMap: {
    Low:    { cls: 'bg-label-info',    dot: 'bg-info',    label: '{{ __("admin.priorityLow") }}'    },
    Medium: { cls: 'bg-label-warning', dot: 'bg-warning', label: '{{ __("admin.priorityMedium") }}' },
    High:   { cls: 'bg-label-danger',  dot: 'bg-danger',  label: '{{ __("admin.priorityHigh") }}'   },
  },
};

// ─── Helpers ─────────────────────────────────────────────────────────────────
const showAlert = (title, text, icon, confirmBtnText = 'Yes', showCancel = true) =>
  Swal.fire({
    title, text, icon,
    showCancelButton: showCancel,
    confirmButtonText: confirmBtnText,
    cancelButtonText: icon === 'error' ? '' : '{{ __('admin.No') }}',
    customClass: {
      confirmButton: `btn btn-${icon === 'warning' ? 'danger' : 'primary'} me-3`,
      cancelButton: showCancel ? 'btn btn-label-secondary' : ''
    },
    buttonsStyling: false
  });

const exportFormat = (inner) => {
  if (!inner.length) return inner;
  const el = $.parseHTML(inner);
  let result = '';
  $.each(el, (_, item) => {
    if (item.classList?.contains('task-title')) {
      const flexDiv = item.lastChild;
      if (flexDiv) {
        const title       = flexDiv.firstChild?.textContent?.trim() || '';
        const description = flexDiv.lastChild?.textContent?.trim()  || '';
        result += title + (description ? (title ? ' - ' : '') + description : '');
      }
    } else {
      result += item.innerText || item.textContent || '';
    }
  });
  return result;
};

// ─── Config Objects ───────────────────────────────────────────────────────────
const priorityObj = {
  Low:    { title: '{{ __('admin.priorityLow') }}',    class: 'bg-label-info'    },
  Medium: { title: '{{ __('admin.priorityMedium') }}', class: 'bg-label-warning' },
  High:   { title: '{{ __('admin.priorityHigh') }}',   class: 'bg-label-danger'  }
};

const statusObj = {
  pending:   { title: '{{ __('admin.boardTitlePending') }}',   class: 'bg-label-warning'  },
  progress:  { title: '{{ __('admin.boardTitleProgress') }}',  class: 'bg-label-info'     },
  review:    { title: '{{ __('admin.boardTitleReview') }}',    class: 'bg-label-primary'  },
  completed: { title: '{{ __('admin.boardTitleCompleted') }}', class: 'bg-label-success'  },
  archived:  { title: '{{ __('admin.archived') }}',            class: 'bg-label-secondary' }
};

const roleObj = {
  admin:               '{{ __('admin.Admin') }}',
  editor:              '{{ __('admin.Editor') }}',
  manager:             '{{ __('admin.Manager') }}',
  judger:              '{{ __('admin.Judger') }}',
  mentor:              '{{ __('admin.Mentor') }}',
  startup:             '{{ __('admin.Startup') }}',
  organizationmanager: '{{ __('admin.organizationManager') }}',
  'sub-admin':         '{{ __('admin.Sub-Admin') }}'
};

// ─── Icon map for attachments (reused in view & edit) ────────────────────────
const attachmentIconMap = {
  'application/pdf': 'ti-file-type-pdf text-danger',
  'image/':          'ti-photo text-success',
  'video/':          'ti-video text-info',
  'application/zip': 'ti-file-zip text-warning',
  'application/vnd': 'ti-file-spreadsheet text-success',
};

const getAttachmentIcon = (mimeType = '') => {
  for (const [prefix, cls] of Object.entries(attachmentIconMap)) {
    if (mimeType.startsWith(prefix)) return cls;
  }
  return 'ti-file-text text-secondary';
};

// ─── DataTable ────────────────────────────────────────────────────────────────
$(function () {
  const $table = $('.datatables-tasks');
  if (!$table.length) return;

  if (window.dt_task) window.dt_task.destroy();

  // ── Custom search (raw-data filter for priority & status) ──────────────────
  // Registered once; uses IDs set during initComplete
  $.fn.dataTable.ext.search.push(function (settings, _data, dataIndex) {
    if (settings.nTable !== $table[0]) return true;

    const priorityVal = $('#taskPriority').val();
    const statusVal   = $('#taskStatus').val();
    const row         = window.dt_task?.row(dataIndex).data();
    if (!row) return true;

    if (priorityVal && row.priority      !== priorityVal) return false;
    if (statusVal   && row.overallStatus !== statusVal)   return false;
    return true;
  });

  window.dt_task = $table.DataTable({
    processing: true,
    responsive: true,
    data: tasks,
    columns: [
      { data: null },
      { data: 'title' },
      { data: 'creator' },
      { data: 'program' },
      { data: 'priority' },
      // { data: 'overallStatus' },
      { data: 'dueDate' },
      { data: 'id' },
      { data: 'createdAt' }
    ],
    columnDefs: [
      {
        targets: 0, className: 'control', orderable: false, searchable: false,
        responsivePriority: 2, render: () => ''
      },
      {
        targets: 7, visible: false, searchable: false
      },
      {
        targets: 1, responsivePriority: 4,
        render: (_, __, full) => `
          <div class="d-flex justify-content-start align-items-start task-title" style="max-width:200px;">
            <div class="d-flex flex-column overflow-y-hidden" style="width:100%;min-width:0;max-height:80px;">
              <div class="d-flex align-items-center gap-1 flex-wrap">
                <a href="javascript:;" class="text-heading fw-medium line-clamp-1"><span>${full.title}</span></a>
                ${full.overallStatus === 'archived' ? '<span class="badge bg-label-secondary"><i class="ti ti-archive ti-xs me-1"></i>Archived</span>' : ''}
              </div>
              <small class="text-muted line-clamp-3">${full.description}</small>
            </div>
          </div>`
      },
      {
        targets: 2,
        render: (_, __, full) => `
          <div>
            <span class="text-heading">${full.creator.name || (full.creator.first_name_en + ' ' + full.creator.last_name_en)}</span>
            <small class="text-muted d-block text-uppercase">${roleObj[full.creator.role] || full.creator.role}</small>
          </div>`
      },
      {
        targets: 3,
        render: (_, __, full) =>
          `<a href="/dashboard/programlist/${full.program.id}" target="_blank" class="text-heading text-decoration-none">${full.program.title}</a>`
      },
      {
        targets: 4,
        // Render badge for display; raw data used by custom search
        render: (_, __, full) => {
          const p = priorityObj[full.priority] || { class: 'bg-label-secondary', title: 'Unknown' };
          return `<span class="badge ${p.class}">${p.title}</span>`;
        }
      },
      // {
      //   targets: 5,
      //   render: (_, __, full) => {
      //     const s = statusObj[full.overallStatus] || { class: 'bg-label-secondary', title: 'Unknown' };
      //     return `<span class="badge ${s.class}">${s.title}</span>`;
      //   }
      // },
      {
        targets: 5,
        render: (_, __, full) =>
          `<span class="text-heading">${full.dueDate !== 'N/A' ? moment(full.dueDate, 'YYYY-MM-DD').format('YYYY-MM-DD') : 'N/A'}</span>`
      },
      {
        targets: 6, searchable: false, orderable: false,
        render: (_, __, full) => `
          <div class="d-flex align-items-center">
            <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill task-view"><i class="ti ti-eye ti-md"></i></a>
            <a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill task-edit"><i class="ti ti-pencil ti-md"></i></a>
            <a href="javascript:;" class="btn btn-icon ${full.overallStatus === 'archived' ? 'btn-text-warning' : 'btn-text-secondary'} waves-effect waves-light rounded-pill task-archive">
              <i class="ti ${full.overallStatus === 'archived' ? 'ti-rotate' : 'ti-archive'} ti-md"></i>
            </a>
            ${window.authUser.role !== 'Mentor' ? '<a href="javascript:;" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill delete-record"><i class="ti ti-trash ti-md"></i></a>' : ''}
          </div>`
      }
    ],
    order: [[7, 'desc']],
    dom: '<"row"<"col-12 col-lg-9 d-flex flex-wrap align-items-center ps-0 ps-lg-3"l><"col-12 col-lg-3 dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 pe-0 pe-lg-2"fB>>t<"row px-6"<"col-sm-12 col-lg-6"i><"col-sm-12 col-lg-6 d-flex justify-content-end"p>>',
    language: {
      url: document.dir === 'rtl'
        ? 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json'
        : 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json',
      sLengthMenu: '_MENU_',
      search: '',
      searchPlaceholder: '{{ __('admin.Search') }}...'
    },
    buttons: [{
      extend: 'collection',
      className: 'btn btn-label-secondary dropdown-toggle mx-4 waves-effect waves-light',
      text: '<i class="ti ti-file-export me-2 ti-xs"></i> {{ __('admin.Export') }}',
      buttons: [
        { extend: 'csv',   text: '<i class="ti ti-file-text me-2"></i>Csv',         className: 'dropdown-item', exportOptions: { columns: [1,2,3,4,5,6,7], format: { body: exportFormat } } },
        { extend: 'excel', text: '<i class="ti ti-file-spreadsheet me-2"></i>Excel', className: 'dropdown-item', exportOptions: { columns: [1,2,3,4,5,6,7], format: { body: exportFormat } } }
      ]
    }],
    initComplete: function () {
      const api            = this.api();
      const $filterWrap    = $('<div class="d-flex flex-wrap flex-md-nowrap gap-3 ms-3"></div>');
      $('.dataTables_length').after($filterWrap);

      // Program dropdown — filtered from unique column values
      api.columns(3).every(function () {
        const column = this;
        const seen   = new Set();
        const $sel   = $(`<select id="taskProgram" class="form-select text-capitalize" style="min-width:200px;">
                            <option value="">{{ __('admin.All_Programs') }}</option>
                          </select>`)
          .appendTo($filterWrap)
          .on('change', function () {
            const val = $.fn.dataTable.util.escapeRegex($(this).val());
            column.search(val ? '^' + val + '$' : '', true, false).draw();
          });

        column.data().each(d => {
          const value = d?.title || d;
          if (!value || seen.has(value)) return;
          seen.add(value);
          $sel.append(`<option value="${value}">${value}</option>`);
        });
      });

      // Priority dropdown — triggers raw-data custom search
      const $prioritySel = $(`<select id="taskPriority" class="form-select text-capitalize" style="min-width:150px;">
                                <option value="">{{ __('admin.All_Priorities') }}</option>
                              </select>`)
        .appendTo($filterWrap)
        .on('change', () => api.draw());

      Object.entries(priorityObj).forEach(([key, val]) =>
        $prioritySel.append(`<option value="${key}">${val.title}</option>`)
      );

      // Status dropdown — triggers raw-data custom search
      // const $statusSel = $(`<select id="taskStatus" class="form-select text-capitalize" style="min-width:150px;">
      //                         <option value="">{{ __('admin.All_statuses') }}</option>
      //                       </select>`)
      //   .appendTo($filterWrap)
      //   .on('change', () => api.draw());

      // Object.entries(statusObj).forEach(([key, val]) =>
      //   $statusSel.append(`<option value="${key}">${val.title}</option>`)
      // );

      // Fix select/input sizes injected by DataTables
      setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
      }, 300);
    }
  });

  // ─── Helper: resolve DataTable row from any child element ────────────────
  const resolveRow = ($el) => {
    let $row = $el.closest('tr');
    while ($row.length && !window.dt_task.row($row).data()) {
      $row = $row.prev();
    }
    return $row.length ? $row : null;
  };

  // ─── Delete Record ────────────────────────────────────────────────────────
  $table.on('click', 'tbody .delete-record', function () {
    const $row = resolveRow($(this));
    if (!$row) return Swal.fire('Error!', 'Could not identify the task.', 'error');

    const rowData = window.dt_task.row($row).data();
    if (!rowData?.id) return Swal.fire('Error!', 'Could not identify the task.', 'error');

    showAlert('{{ __('admin.SureDelete') }}', '{{ __('admin.Recover') }}', 'warning', '{{ __('admin.DYes') }}')
      .then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
          url: `/dashboard/tasks/${rowData.id}`,
          method: 'DELETE',
          dataType: 'json',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          success: () => {
            window.dt_task.row($row).remove().draw();
            toastr.success('{{ __('admin.Task_deleted_successfully.') }}');
          },
          error: xhr => toastr.error(xhr.responseJSON?.message || '{{ __('admin.unexpected_error') }}')
        });
      });
  });

  // ─── Archive Task ─────────────────────────────────────────────────────────
  $table.on('click', 'tbody .task-archive', function () {
    const $row = resolveRow($(this));
    if (!$row) return;
    const rowData = window.dt_task.row($row).data();
    if (!rowData?.id) return;

    const isArchived   = rowData.overallStatus === 'archived';
    const newStatus    = isArchived ? 'pending' : 'archived';
    const actionLabel  = isArchived ? '{{ __('admin.reopen_task') }}' : '{{ __('admin.archive_task') }}';
    const actionIcon   = isArchived ? 'ti-refresh' : 'ti-archive';
    const actionColor  = isArchived ? 'btn-warning' : 'btn-secondary';
    const bodyText     = isArchived
      ? '{{ __('admin.reopen_task_confirm') }}'
      : '{{ __('admin.archive_task_confirm') }}';

    Swal.fire({
      title: actionLabel,
      html: `<p class="mb-0">${bodyText}</p>
             <div class="mt-3">
               <span class="badge bg-label-secondary me-1">
                 <i class="ti ti-clipboard-text ti-xs me-1"></i>${rowData.title}
               </span>
             </div>`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: `<i class="ti ${actionIcon} me-1"></i>${actionLabel}`,
      cancelButtonText: '{{ __('admin.Cancel') }}',
      customClass: {
        confirmButton: `btn ${actionColor} me-3`,
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(result => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: `/dashboard/tasks/${rowData.id}`,
        method: 'POST',
        dataType: 'json',
        data: { _method: 'PUT', status: newStatus },
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (response) {
          rowData.overallStatus = newStatus;
          window.dt_task.row($row).data(rowData).draw(false);
          Swal.fire({
            title: '{{ __('admin.Done') }}',
            text: response.message || '{{ __('admin.Task_updated_successfully.') }}',
            icon: 'success',
            showCancelButton: false,
            confirmButtonText: '{{ __('admin.Ok') }}',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false,
            timer: 2500,
            timerProgressBar: true
          });
        },
        error: xhr => toastr.error(xhr.responseJSON?.message || '{{ __('admin.unexpected_error') }}')
      });
    });
  });

  // ─── View Task ────────────────────────────────────────────────────────────
  $table.on('click', 'tbody .task-view', function () {
    const $row = resolveRow($(this));
    const data = $row ? window.dt_task.row($row).data() : null;
    if (!data) return toastr.error('Could not load task details.');

    const assigneesHtml = data.assignees?.length
      ? data.assignees.map(a => {
          const initials = (a.name || '?').split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
          const aStatus  = statusObj[a.status] || { class: 'bg-label-secondary', title: 'Unknown' };
          const avatarEl = a.logo
            ? `<img src="${a.logo}" alt="${a.name}" class="rounded-circle" style="width:38px;height:38px;object-fit:cover;"
                onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'avatar-initial rounded-circle bg-label-primary d-flex align-items-center justify-content-center fw-semibold',style:'width:38px;height:38px;font-size:13px',textContent:'${initials}'}))"/>`
            : `<span class="avatar-initial rounded-circle bg-label-primary d-flex align-items-center justify-content-center fw-semibold" style="width:38px;height:38px;font-size:13px;">${initials}</span>`;
          return `
            <div class="d-flex align-items-center justify-content-between p-3 mb-2 rounded-3 border bg-light-subtle">
              <div class="d-flex align-items-center gap-3 me-4">
                <a href="${a.profile_url}" target="_blank" class="flex-shrink-0 text-decoration-none">${avatarEl}</a>
                <div>
                  <a href="${a.profile_url}" target="_blank" class="fw-semibold small text-body text-decoration-none d-flex align-items-center gap-1">
                    ${a.name}<i class="ti ti-external-link ti-xs text-muted"></i>
                  </a>
                  <small class="text-muted"><i class="ti ti-clock ti-xs me-1"></i>{{ __('admin.last_updated') }}: ${moment(a.lastUpdated).format('YYYY-MM-DD')}</small>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <span class="badge ${aStatus.class}">${aStatus.title}</span>
                @if ($_user->ableTo('tasks.show', $permissions))
                  <button type="button"
                    class="btn btn-sm btn-outline-primary task-review-btn"
                    data-task-id="${data.id}">
                    {{ __('admin.Review') }} <i class="ti ti-settings ti-xs ms-1"></i>
                  </button>
                @endif
              </div>
            </div>`;
        }).join('')
      : `<p class="text-muted small fst-italic">{{ __('admin.no_assignees') }}</p>`;

    const attachmentsHtml = data.attachments?.length
      ? `<div class="mb-2">
           <p class="text-uppercase small fw-bold text-muted mb-3 d-flex align-items-center gap-2">
             <i class="ti ti-paperclip"></i>{{ __('admin.attachments') }}
           </p>
           <div class="d-flex flex-wrap gap-2">
             ${data.attachments.map(file => {
               const iconClass = getAttachmentIcon(file.type);
               const sizeKb    = file.size ? (file.size / 1024).toFixed(1) + ' KB' : '';
               return `<a href="/storage/${file.path}" target="_blank"
                         class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 waves-effect">
                         <i class="ti ${iconClass}"></i>
                         <span>${file.name}</span>
                         ${sizeKb ? `<small class="text-muted">(${sizeKb})</small>` : ''}
                       </a>`;
             }).join('')}
           </div>
         </div>`
      : '';

    const priorityInfo = priorityObj[data.priority]      || { class: 'bg-label-secondary', title: data.priority };
    const statusInfo   = statusObj[data.overallStatus]   || { class: 'bg-label-secondary', title: data.overallStatus };

    $('#task-details-content').html(`
      <div class="mb-5">
        <h5 class="fw-bold mb-1">${data.title}</h5>
        <p class="text-muted small mb-0">${data.description || '<em>{{ __('admin.no_description') }}</em>'}</p>
      </div>

      <div class="d-flex flex-wrap gap-2 mb-5">
        <span class="badge ${priorityInfo.class} d-flex align-items-center gap-1 px-2 py-1">
          <i class="ti ti-flag ti-xs"></i>${priorityInfo.title}
        </span>
        <span class="badge ${statusInfo.class} d-flex align-items-center gap-1 px-2 py-1">
          <i class="ti ti-circle-dot ti-xs"></i>${statusInfo.title}
        </span>
        <span class="badge bg-label-secondary d-flex align-items-center gap-1 px-2 py-1">
          <i class="ti ti-calendar ti-xs"></i>${data.dueDate}
        </span>
      </div>

      <div class="card border shadow-none mb-5">
        <div class="card-body p-0">
          <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
            <span class="small text-muted d-flex align-items-center gap-2">
              <i class="ti ti-user text-info"></i>{{ __('admin.Created_By') }}
            </span>
            ${data.creator ? `
            <a href="${data.creator.profile_url}" target="_blank" class="d-flex align-items-center gap-2 text-decoration-none text-body">
              <div class="avatar avatar-xs">
                <img src="${data.creator.avatar}" alt="${data.creator.name}" class="rounded-circle" onerror="this.src='/images/placeholder.jpg'" />
              </div>
              <span class="fw-semibold small">${data.creator.name}</span>
              <i class="ti ti-external-link ti-xs text-muted"></i>
            </a>` : `<span class="small text-muted fst-italic">{{ __('admin.unknown') }}</span>`}
          </div>
          <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
            <span class="small text-muted d-flex align-items-center gap-2">
              <i class="ti ti-shield-check text-info"></i>{{ __('admin.Creator_Role') }}
            </span>
            <span class="small fw-semibold text-uppercase">${data.creator ? (roleObj[data.creator.role] || data.creator.role) : '—'}</span>
          </div>
          <div class="d-flex align-items-center justify-content-between px-4 py-3">
            <span class="small text-muted d-flex align-items-center gap-2">
              <i class="ti ti-rocket text-success"></i>{{ __('admin.Program') }}
            </span>
            <a href="/dashboard/cycles/${data.program.id}" target="_blank" class="small fw-semibold text-body text-decoration-none d-flex align-items-center gap-1">
              ${data.program.title}<i class="ti ti-external-link ti-xs text-muted"></i>
            </a>
          </div>
        </div>
      </div>

      <div class="mb-5">
        <p class="text-uppercase small fw-bold text-muted mb-3 d-flex align-items-center gap-2">
          <i class="ti ti-building-skyscraper"></i>{{ __('admin.assignee_startups_statuses') }}
        </p>
        ${assigneesHtml}
      </div>

      ${attachmentsHtml}
    `);

    $('#viewTaskModal').data('taskId', data.id).offcanvas('show');
  });

  const vtCommentQ = new Quill('#vt-comment-editor', { theme: 'snow', modules: { toolbar: '#vt-comment-toolbar' } });

  // ─── Review Task (open kanban-vt-offcanvas) ───────────────────────────────
  $(document).on('click', '.task-review-btn', function () {
    const taskId = $(this).data('task-id');
    $('#viewTaskModal').offcanvas('hide');

    $.ajax({
      url: `/dashboard/tasks/${taskId}`,
      method: 'GET',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      success: function (res) {
        if (!res.success) return toastr.error('{{ __("admin.unexpected_error") }}');
        const td   = res.data;
        const p    = window.KanbanConfig.priorityMap[td.priority] ?? window.KanbanConfig.priorityMap['Medium'];
        const sObj = statusObj[td.status] ?? { class: 'bg-label-secondary', title: td.status };

        $('#vt-title').text(td.title);
        $('#vt-description').html(td.description || '');
        $('#vt-due-date').text(td.due_date || 'N/A');
        $('#vt-priority').html(`<span class="badge ${p.cls} rounded-pill"><span class="d-inline-block rounded-circle me-1 ${p.dot}" style="width:6px;height:6px;"></span>${p.label}</span>`);
        $('#vt-status').html(`<span class="badge ${sObj.class} rounded-pill">${sObj.title}</span>`);

        // ── Creator ─────────────────────────────────────────────────────
        $('#vt-creator-section').remove();
        const c = td.creator;
        if (c) {
          const cAvatar = c.avatar
            ? `<img src="${c.avatar}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;" alt="${c.name}">`
            : `<span class="avatar-initial bg-label-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:12px;">${c.name.substring(0,2).toUpperCase()}</span>`;
          $('#vt-description').after(`
            <div id="vt-creator-section" class="mb-4 mt-3">
              <span class="text-muted small d-block mb-2">{{ __('admin.Created_By') }}</span>
              <div class="d-flex align-items-center gap-2 border rounded p-2 bg-body">
                <a href="${c.profile_url}" target="_blank">${cAvatar}</a>
                <div>
                  <a href="${c.profile_url}" target="_blank" class="fw-semibold small text-body text-decoration-none">${c.name}</a>
                  <small class="text-muted d-block text-uppercase">${roleObj[c.role] || c.role}</small>
                </div>
              </div>
            </div>`);
        }

        // ── Assignees: startup + founder ─────────────────────────────────
        const $list = $('#vt-assignees-list').empty();
        if (td.assignees?.length) {
          $list.closest('.mb-4').removeClass('d-none');
          td.assignees.forEach((a, i) => {
            const logoEl = a.logo
              ? `<img src="${a.logo}" class="rounded-circle object-fit-cover" style="width:36px;height:36px;" alt="${a.name}">`
              : `<span class="avatar-initial bg-label-info rounded-circle d-flex align-items-center justify-content-center fw-semibold" style="width:36px;height:36px;font-size:12px;">${(a.name||'?').substring(0,2).toUpperCase()}</span>`;
            const founderHtml = a.founder
              ? `<div class="d-flex align-items-center gap-2 mt-2 ps-1 border-start ms-2">
                   ${a.founder.avatar
                     ? `<img src="${a.founder.avatar}" class="rounded-circle" style="width:26px;height:26px;object-fit:cover;" alt="${a.founder.name}">`
                     : `<span class="avatar-initial bg-label-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:26px;height:26px;font-size:10px;">${a.founder.name.substring(0,2).toUpperCase()}</span>`}
                   <a href="${a.founder.profile_url}" target="_blank" class="small text-body text-decoration-none">${a.founder.name}</a>
                   <span class="badge bg-label-primary" style="font-size:.65rem;">{{ __('admin.Founder') }}</span>
                 </div>`
              : '';
            $list.append(`
              <div class="${i > 0 ? 'border-top pt-3 mt-1' : ''}">
                <div class="d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-2">
                    <a href="${a.profile_url}" target="_blank">${logoEl}</a>
                    <a href="${a.profile_url}" target="_blank" class="fw-semibold small text-body text-decoration-none">${a.name}</a>
                  </div>
                  <span class="badge ${statusObj[a.status]?.class ?? 'bg-label-secondary'}">${statusObj[a.status]?.title ?? a.status}</span>
                </div>
                ${founderHtml}
              </div>`);
          });
        } else {
          $list.closest('.mb-4').addClass('d-none');
        }

        renderAttachmentsList(td.attachments, td.id, '#vt-attachments-list', false);
        $('#vt-attachments-count').text(td.attachments.length ?? 0);
        renderActivitiesList(td.activities,            '#vt-activities-list');
        renderCommentsList(td.comments,                '#vt-comments-list');
        renderFeedbackList(td.feedbacks,               '#vt-feedback-list');
        renderSubmissionsList(td.submissions,           '#vt-submissions-list');

        $('#vt-comment-count').text(td.comments?.length ?? 0);
        $('#vt-feedback-count').text(td.feedbacks?.length ?? 0);
        $('#vt-submissions-count').text(td.submissions?.length ?? 0);
        $('#vt-activity-count').text(td.activities?.length ?? 0);

        $('#vt-upload-btn-task-id').val(td.id);
        $('#vt-send-comment-btn').data('task-id', td.id);
        $('#vt-edit-trigger-btn').data('task-id', td.id);

        bootstrap.Tab.getOrCreateInstance(
          document.querySelector('#viewTaskOffcanvas .nav-tabs .nav-link')
        ).show();
        bootstrap.Offcanvas.getOrCreateInstance(
          document.getElementById('viewTaskOffcanvas')
        ).show();
      },
      error: () => toastr.error('{{ __("admin.unexpected_error") }}')
    });
  });

  // ─── Send Comment (kanban-vt-offcanvas) ───────────────────────────────────
  $(document).on('click', '#vt-send-comment-btn', function () {
    const taskId  = $(this).data('task-id');
    const $editor = document.querySelector('#vt-comment-editor .ql-editor');
    const text    = $editor?.innerHTML?.trim();
    if (!text || text === '<p><br></p>') return;

    $.ajax({
      url: `/dashboard/tasks/${taskId}/add-comment`,
      method: 'POST',
      data: { comment: text },
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      success: function (res) {
        const c       = res.comment;
        const isBlank = !$('#vt-comments-list').children().length ||
                        $('#vt-comments-list').text().includes(window.KanbanConfig.labels.noComments);
        if (isBlank) $('#vt-comments-list').html('');
        $('#vt-comments-list').append(`
          <div class="d-flex align-items-start gap-3 mb-3">
            <div class="flex-shrink-0 mt-1">${buildAvatar(c.user, c.img_url, c.user_id)}</div>
            <div class="flex-grow-1 bg-lighter rounded p-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-semibold text-dark">${c.user}</span>
                <span class="text-muted" style="font-size:.7rem;">${c.time}</span>
              </div>
              <div class="ql-editor p-0 small text-body" style="min-height:auto;">${c.text}</div>
            </div>
          </div>`);
        $('#vt-comment-count').text(parseInt($('#vt-comment-count').text() || '0') + 1);
        if ($editor) $editor.innerHTML = '';
      },
      error: () => toastr.error('{{ __("admin.unexpected_error") }}')
    });
  });

  // ─── Submit Feedback (kanban-vt-offcanvas) ────────────────────────────────
  $(document).on('click', '#vt-submit-feedback-btn', function () {
    const taskId   = $('#vt-upload-btn-task-id').val();
    const decision = $('input[name="vt_feedback_decision"]:checked').val();
    const note     = $('#vt-fb-note').val().trim();
    $.ajax({
      url: `/dashboard/tasks/${taskId}/add-feedback`,
      method: 'POST',
      data: { decision, note },
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      success: res => toastr.success(res.message || '{{ __("admin.feedback_submitted") }}'),
      error:   ()  => toastr.error('{{ __("admin.unexpected_error") }}')
    });
  });

  // ─── Edit Task ────────────────────────────────────────────────────────────
  $table.on('click', 'tbody .task-edit', function () {
    const $row = resolveRow($(this));
    const data = $row ? window.dt_task.row($row).data() : null;
    if (!data?.id) return toastr.error('Could not load task.');

    $('#task-title').val(data.title);
    $('#task-description').val(data.description);
    $('#task-priority').val(data.priority).trigger('change');
    $('#task-status').val(data.overallStatus).trigger('change');
    $('#task-due-date').val(data.dueDate !== 'N/A' ? data.dueDate : '');
    // Set edit-row BEFORE triggering program change so isEditMode check works correctly
    $('#addNewTaskForm').data('edit-row', $row);

    // Set program (triggers program-change handler which populates assignees in edit mode)
    $('#task-program').val(data.program.id).trigger('change');

    // Populate all startups and select current assignees
    const $assigneeSelect  = $('#task-assignees');
    const assigneeIds      = data.assignees.map(a => a.id).filter(id => id != null);
    $assigneeSelect.empty();
    (startups || []).forEach(s =>
      $assigneeSelect.append(`<option value="${s.id}">${s.name ?? s.name_en}</option>`)
    );
    if (assigneeIds.length) $assigneeSelect.val(assigneeIds).trigger('change');

    // Existing attachments
    const $existingWrap = $('#existing-attachments');
    const $existingList = $('#existing-attachments-list').empty();
    if (data.attachments?.length) {
      data.attachments.forEach(file => {
        const iconClass = getAttachmentIcon(file.type);
        const sizeKb    = file.size ? (file.size / 1024).toFixed(1) + ' KB' : '';
        $existingList.append(`
          <div class="d-flex align-items-center gap-1 border rounded-2 px-2 py-1 bg-light-subtle" data-filename="${file.name}">
            <i class="ti ${iconClass} ti-sm"></i>
            <a href="/storage/${file.path}" target="_blank" class="small text-body text-decoration-none text-truncate" style="max-width:140px;">${file.name}</a>
            ${sizeKb ? `<small class="text-muted">(${sizeKb})</small>` : ''}
            <button type="button" class="btn btn-xs btn-icon text-danger ms-1 remove-existing-attachment p-0"
              data-filename="${file.name}" data-task-id="${data.id}">
              <i class="ti ti-x ti-xs"></i>
            </button>
          </div>`);
      });
      $existingWrap.show();
    } else {
      $existingWrap.hide();
    }

    $('#addTaskModalLabel').text('{{ __('admin.edit_task') }}');
    $('.data-submit').text('{{ __('admin.Update') }}');
    $('#addTaskModal').offcanvas('show');
  });
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
      autoFocus:  new FormValidation.plugins.AutoFocus()
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

      if (!window.dt_task) return Swal.fire('{{ __('admin.error') }}', '{{ __('admin.table_not_initialized') }}', 'error');

      const $editRow = $(form).data('edit-row');
      const taskId   = $editRow ? window.dt_task.row($editRow).data().id : null;
      const isEdit   = !!$editRow;
      const url      = isEdit ? `/dashboard/tasks/${taskId}` : '/dashboard/tasks';

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

          if (isEdit) {
            window.dt_task.row($editRow).data(taskObj).draw(false);
            toastr.success('{{ __('admin.Task_updated_successfully.') }}');
          } else {
            window.dt_task.row.add(taskObj);
            window.dt_task.order([[7, 'desc']]).draw();
            toastr.success('{{ __('admin.Task_added_successfully.') }}');
          }

          bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('addTaskModal')).hide();
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

    const maxSize    = 10 * 1024 * 1024;
    const errors     = [];
    const validFiles = files.filter(file => {
      if (file.size > maxSize) { errors.push(`{{ __('admin.file_exceeds_10_limit') }} (${file.name})`); return false; }
      return true;
    });

    if (errors.length) {
      $('#fileError').html(errors.join('<br>')).show();
      $('#fileName').hide();
      $(this).val('');
      return;
    }

    $('#fileError').hide();
    $('#fileName').html(validFiles.map(f => `<div><i class="ti ti-paperclip me-1"></i>${f.name}</div>`).join('')).show();
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
@stop