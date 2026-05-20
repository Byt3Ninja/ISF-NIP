/**
 * kanban-dropzone.js
 * Dropzone upload logic for the view-task offcanvas.
 * Depends on: kanban-utils.js (fileIcon, formatFileSize, validateFiles)
 *             kanban-renderers.js (buildAttachmentItem)
 * Reads config from: window.KanbanConfig
 */

const DZ_MAX_FILES   = 5;
const DZ_MAX_SIZE_MB = 5;
const DZ_ACCEPT_TYPES = [
  'image/jpeg','image/jpg','image/png','image/gif','image/webp','image/svg+xml',
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/vnd.ms-excel',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'application/vnd.ms-powerpoint',
  'application/vnd.openxmlformats-officedocument.presentationml.presentation',
  'application/zip','application/x-zip-compressed',
  'text/plain',
];

let dzQueue = []; // { file, id }

function dzRenderQueue() {
  const $q = $('#vt-dropzone-queue');
  $q.empty();
  dzQueue.forEach(item => {
    $q.append(`
      <div class="d-flex align-items-center gap-2 border rounded p-2 bg-body dz-queue-item" id="dzq-${item.id}">
        <i class="ti ${fileIcon(item.file.name)} ti-sm flex-shrink-0"></i>
        <div class="flex-grow-1 overflow-hidden">
          <div class="text-truncate small fw-medium">${item.file.name}</div>
          <div class="text-muted" style="font-size:.7rem;">${formatFileSize(item.file.size)}</div>
          <div class="dz-progress-bar mt-1" style="width:0%;"></div>
        </div>
        <button type="button"
          class="btn btn-icon btn-sm btn-text-danger rounded-circle flex-shrink-0 dz-remove-btn"
          data-dz-id="${item.id}">
          <i class="ti ti-x ti-xs"></i>
        </button>
      </div>`);
  });
  if (!$('#vt-dropzone-upload-btn').data('always-show')) {
    $('#vt-dropzone-upload-btn').toggleClass('d-none', dzQueue.length === 0);
  }
}

function dzAddFiles(files) {
  const lbl    = window.KanbanConfig.labels;
  const $error = $('#vt-upload-error');
  $error.text('');

  Array.from(files).forEach(file => {
    if (dzQueue.length >= DZ_MAX_FILES) {
      $error.text(lbl.maxFilesReached); return;
    }
    if (!DZ_ACCEPT_TYPES.includes(file.type)) {
      $error.text(`"${file.name}" ${lbl.invalidFileType}`); return;
    }
    if (file.size > DZ_MAX_SIZE_MB * 1024 * 1024) {
      $error.text(`"${file.name}" ${lbl.exceedsSizeLimit}`); return;
    }
    if (dzQueue.find(i => i.file.name === file.name && i.file.size === file.size)) return;
    dzQueue.push({ file, id: Date.now() + Math.random() });
  });
  dzRenderQueue();
}

function dzReset() {
  dzQueue = [];
  dzRenderQueue();
  $('#vt-upload-error').text('');
}

function initDropzone(tasks) {
  const cfg = window.KanbanConfig;

  // Click to browse
  $('#vt-dropzone').on('click', function (e) {
    if ($(e.target).is('#vt-dropzone-input, .dz-remove-btn, .dz-remove-btn *')) return;
    $('#vt-dropzone-input').val('');
    $('#vt-dropzone-input')[0].click();
  });

  // File input change
  $('#vt-dropzone-input').on('change', function () {
    dzAddFiles(this.files);
    $(this).val('');
  });

  // Drag events
  $('#vt-dropzone').on('dragover dragenter', function (e) {
    e.preventDefault(); e.stopPropagation();
    $(this).addClass('dz-dragover');
  }).on('dragleave dragend drop', function (e) {
    e.preventDefault(); e.stopPropagation();
    $(this).removeClass('dz-dragover');
    if (e.type === 'drop') dzAddFiles(e.originalEvent.dataTransfer.files);
  });

  // Remove from queue
  $(document).on('click', '.dz-remove-btn', function (e) {
    e.stopPropagation();
    const id = $(this).data('dz-id');
    dzQueue = dzQueue.filter(i => i.id != id);
    $('#vt-upload-error').text('');
    dzRenderQueue();
  });

  // Upload all / Send Submission
  $('#vt-dropzone-upload-btn').on('click', function () {
    const lbl  = cfg.labels;
    const $btn = $(this);
    const originalHtml = $btn.html();
    if (!dzQueue.length) {
      if ($btn.data('always-show')) {
        $('#vt-upload-error').text(lbl.pleaseAddFiles || 'Please add at least one file.');
      }
      return;
    }
    $('#vt-upload-error').text('');
    const taskId   = $('#vt-upload-btn-task-id').val();
    const note     = $('#vt-submission-note').val() || '';
    const batchId  = Date.now().toString();

    $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1"></span>${lbl.uploading}`);

    let completed = 0, failed = 0;
    const total    = dzQueue.length;
    const snapshot = [...dzQueue];

    snapshot.forEach(item => {
      const fd = new FormData();
      fd.append('attachment', item.file);
      fd.append('note', note);
      fd.append('batch_id', batchId);
      $(`#dzq-${item.id} .dz-progress-bar`).css('width', '60%');

      $.ajax({
        url: cfg.routes.uploadAttachment.replace(':taskId', taskId),
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (response) {
          $(`#dzq-${item.id} .dz-progress-bar`).css('width', '100%');
          setTimeout(() => $(`#dzq-${item.id}`).fadeOut(200, function () { $(this).remove(); }), 400);

          const newAtt = response.attachment || { id: Date.now(), name: item.file.name, url: '#' };
          const task   = tasks.find(t => t.id == taskId);
          if (task) {
            if (!task.attachments) task.attachments = [];
            task.attachments.push(newAtt);
          }
          if ($('#vt-attachments-list').find('span.text-muted').length) $('#vt-attachments-list').empty();
          $('#vt-attachments-list').append(buildAttachmentItem(newAtt, taskId, true));

          dzQueue = dzQueue.filter(i => i.id !== item.id);
          completed++; checkDone();
        },
        error: function () {
          $(`#dzq-${item.id} .dz-progress-bar`).css({ width: '100%', background: 'var(--bs-danger)' });
          failed++; completed++; checkDone();
        }
      });
    });

    function checkDone() {
      if (completed < total) return;
      if (failed > 0) toastr.warning(`${total - failed} ${lbl.uploaded}, ${failed} ${lbl.failed}.`);
      else {
        toastr.success(lbl.attachmentUploadedSuccess);
        $('#vt-submission-note').val('');
      }
      dzRenderQueue();
      $btn.prop("disabled", false).html(originalHtml);
    }
  });
}
