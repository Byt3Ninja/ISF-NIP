/**
 * kanban-tasks.js
 * All task CRUD actions: view, add, edit, archive, comments, feedback, attachments.
 * Depends on: kanban-utils.js, kanban-renderers.js, kanban-board.js, kanban-dropzone.js
 * Reads config from: window.KanbanConfig
 */

function initKanbanTasks(tasks, program, addDescQ, etDescQ, vtCommentQ, addFv, etFv) {
  const cfg = window.KanbanConfig;
  const lbl = cfg.labels;
  
  // ─── Inline Dropzone (Add / Edit) ──────────────────────────────────────────

  function makeInlineDz(dzId, inputId, queueId, errorId) {
    let queue = []; // { file, id }

    function renderQueue() {
      const $q = $(queueId);
      $q.empty();
      queue.forEach(item => {
        $q.append(`
          <div class="d-flex align-items-center gap-2 border rounded p-2 bg-body" id="idzq-${item.id}">
            <i class="ti ${fileIcon(item.file.name)} ti-sm flex-shrink-0"></i>
            <div class="flex-grow-1 overflow-hidden">
              <div class="text-truncate small fw-medium">${item.file.name}</div>
              <div class="text-muted" style="font-size:.7rem;">${formatFileSize(item.file.size)}</div>
            </div>
            <button type="button" class="btn btn-icon btn-sm btn-text-danger rounded-circle flex-shrink-0 idz-remove-btn" data-idz="${item.id}" data-dz-scope="${dzId}">
              <i class="ti ti-x ti-xs"></i>
            </button>
          </div>`);
      });
      $(errorId).text('');
    }

    function addFiles(files) {
      Array.from(files).forEach(file => {
        if (queue.length >= DZ_MAX_FILES) { $(errorId).text(lbl.maxFilesReached); return; }
        if (!DZ_ACCEPT_TYPES.includes(file.type)) { $(errorId).text(`"${file.name}" ${lbl.invalidFileType}`); return; }
        if (file.size > DZ_MAX_SIZE_MB * 1024 * 1024) { $(errorId).text(`"${file.name}" ${lbl.exceedsSizeLimit}`); return; }
        if (queue.find(i => i.file.name === file.name && i.file.size === file.size)) return;
        queue.push({ file, id: Date.now() + Math.random() });
      });
      renderQueue();
    }

    function reset() { queue = []; renderQueue(); }

    function getFiles() { return queue.map(i => i.file); }

    // Click to browse
    $(dzId).on('click', function (e) {
      if (!$(e.target).hasClass('idz-remove-btn') && !$(e.target).closest('.idz-remove-btn').length) {
        $(inputId).val('');
        $(inputId)[0].click();
      }
    });

    $(inputId).on('change', function () { addFiles(this.files); $(this).val(''); });

    $(dzId).on('dragover dragenter', function (e) {
      e.preventDefault(); e.stopPropagation();
      $(this).addClass('dz-dragover');
    }).on('dragleave dragend drop', function (e) {
      e.preventDefault(); e.stopPropagation();
      $(this).removeClass('dz-dragover');
      if (e.type === 'drop') addFiles(e.originalEvent.dataTransfer.files);
    });

    $(document).on('click', `.idz-remove-btn[data-dz-scope="${dzId}"]`, function (e) {
      e.stopPropagation();
      const id = $(this).data('idz');
      queue = queue.filter(i => i.id != id);
      renderQueue();
    });

    return { reset, getFiles };
  }

  const addDz = makeInlineDz('#add-dropzone', '#add-dropzone-input', '#add-dropzone-queue', '#add-file-error');
  const etDz = makeInlineDz('#et-dropzone', '#et-dropzone-input', '#et-dropzone-queue', '#et-file-error');

  // ─── View Task ─────────────────────────────────────────────────────────────

  $(document).on('click', '.view-task-btn', function () {
    const taskId = $(this).data('task-id');
    const taskData = tasks.find(t => t.id === taskId);
    if (!taskData) return;

    const p = cfg.priorityMap[taskData.priority] ?? cfg.priorityMap['Medium'];
    const sLabel = cfg.statusMap[taskData.status] ?? taskData.status;

    // Priority colour strip
    const colorMatch = (p.cls || '').match(/bg-(?:label-)?(\w+)/);
    const colorName = colorMatch ? colorMatch[1] : 'secondary';
    $('#vt-priority-bar').css('background', `rgba(var(--bs-${colorName}-rgb), 0.85)`);

    $('#vt-title').text(taskData.title);
    $('#vt-description').html(taskData.description ? taskData.description : lbl.noDescription);
    $('#vt-due-date').text(taskData.due_date || 'N/A');
    $('#vt-priority').html(`<span class="badge ${p.cls} rounded-pill"><span class="d-inline-block rounded-circle me-1 ${p.dot}" style="width:6px;height:6px;"></span>${p.label}</span>`);
    $('#vt-status').html(`<span class="badge bg-label-secondary rounded-pill">${sLabel}</span>`);

    // ── Old single-assignee (startup/founder) display ────────────���─────────
    // console.log('cfg.isAdmin', cfg.isAdmin)
    // console.log('cfg.isOrgManager', cfg.isOrgManager)
    // console.log('taskData.assignee', taskData.assignee)
    // if (taskData.assignee && (cfg.isAdmin || cfg.isSubAdmin || cfg.isOrgManager)) {
    //   const { startup, founder } = taskData.assignee;
    //   const _avatar = (obj = {}, bgCls) => {
    //     const name = obj.name || '?';
    //     const ri = name.substring(0, 2).toUpperCase();
    //     return obj.img_url
    //       ? `<img src="${obj.img_url}" alt="${name}" class="rounded-circle object-fit-cover" style="width:40px;height:40px;">`
    //       : `<span class="avatar-initial ${bgCls} rounded-circle d-flex align-items-center justify-content-center fw-semibold" style="width:40px;height:40px;font-size:13px;">${ri}</span>`;
    //   };
    //   const _wrap = (obj = {}, bgCls, nameEl) => {
    //     const { profile_url, name = '' } = obj;
    //     const av = _avatar(obj, bgCls);
    //     $(nameEl + '-avatar').html(profile_url ? `<a href="${profile_url}" target="_blank">${av}</a>` : av);
    //     $(nameEl + '-name').html(profile_url ? `<a href="${profile_url}" target="_blank" class="text-body">${name}</a>` : name);
    //   };
    //   const hasStartup = startup && startup.name;
    //   const hasFounder = founder && founder.name;
    //   if (hasStartup) { _wrap(startup, 'bg-label-info', '#vt-assignee-startup'); $('#vt-assignee-startup').removeClass('d-none'); }
    //   else { $('#vt-assignee-startup').addClass('d-none'); }
    //   if (hasFounder) { _wrap(founder, 'bg-label-primary', '#vt-assignee-founder'); $('#vt-assignee-founder').removeClass('d-none'); }
    //   else { $('#vt-assignee-founder').addClass('d-none'); }
    //   if (hasStartup || hasFounder) { $('#vt-assignee-wrapper').removeClass('d-none'); }
    //   else { $('#vt-assignee-wrapper').addClass('d-none'); }
    // } else {
    //   $('#vt-assignee-wrapper').addClass('d-none');
    // }
    // ── Assignee chips in hero area ─────────────────────────────────────────
    const assignees = taskData.assignees || [];
    if (assignees.length) {
      const $list = $('#vt-assignees-list').empty();
      assignees.forEach(a => {
        const ri = (a.name || '?').substring(0, 2).toUpperCase();
        const avatarInner = a.img_url
          ? `<img src="${a.img_url}" alt="${a.name}" class="rounded-circle object-fit-cover flex-shrink-0" style="width:18px;height:18px;">`
          : `<span class="avatar-initial bg-label-info rounded-circle d-inline-flex align-items-center justify-content-center fw-semibold flex-shrink-0" style="width:18px;height:18px;font-size:9px;">${ri}</span>`;
        const chip = a.profile_url
          ? `<a href="${a.profile_url}" target="_blank" class="d-inline-flex align-items-center gap-1 badge bg-label-secondary rounded-pill fw-normal text-decoration-none text-body" style="font-size:.75rem;padding:.3rem .6rem;">${avatarInner}<span>${a.name}</span></a>`
          : `<span class="d-inline-flex align-items-center gap-1 badge bg-label-secondary rounded-pill fw-normal text-body" style="font-size:.75rem;padding:.3rem .6rem;">${avatarInner}<span>${a.name}</span></span>`;
        $list.append(chip);
      });
      $('#vt-assignee-wrapper').removeClass('d-none');
    } else {
      $('#vt-assignee-wrapper').addClass('d-none');
    }

    // ── Reporter ────────────────────────────────────────────────────────────
    const c = taskData.creator;
    if (c) {
      const roleLabel = cfg.roleMap[String(c.role || '').toLowerCase()] || c.role || '';
      const avatarEl = c.img_url
        ? `<img src="${c.img_url}" alt="${c.name}" class="rounded-circle" style="width:28px;height:28px;object-fit:cover;" onerror="this.onerror=null; this.src='/images/placeholder.jpg'">`
        : `<span class="avatar-initial rounded-circle bg-label-secondary d-flex align-items-center justify-content-center fw-semibold" style="width:28px;height:28px;font-size:11px;">${(c.name || "?").substring(0, 2).toUpperCase()}</span>`;
      $('#vt-reporter').html(`
        <a href="${c.profile_url}" target="_blank" class="d-flex align-items-center gap-2 text-decoration-none text-body">
          ${avatarEl}
          <div class="text-start">
            <div class="fw-semibold small">${c.name}</div>
            ${roleLabel ? `<small class="text-muted text-uppercase" style="font-size:.65rem;">${roleLabel}</small>` : ''}
          </div>
          <i class="ti ti-external-link ti-xs text-muted"></i>
        </a>`);
    } else {
      $('#vt-reporter').html('<span class="small text-muted fst-italic">—</span>');
    }

    // ── Created At ──────────────────────────────────────────────────────────
    $('#vt-created-at').text(taskData.created_at ? moment(taskData.created_at).format('YYYY-MM-DD') : '—');

    // ── Program ─────────────────────────────────────────────────────────────
    const prog = taskData.program;
    if (prog) {
      $('#vt-program').html(`
        <a href="${prog.url}" target="_blank" class="small fw-semibold text-body text-decoration-none d-flex align-items-center gap-1">
          ${prog.title}<i class="ti ti-external-link ti-xs text-muted"></i>
        </a>`);
    } else {
      $('#vt-program').html('<span class="small text-muted fst-italic">—</span>');
    }

    renderAttachmentsList(taskData.attachments, taskId, '#vt-attachments-list', cfg.isAbleToDeleteAttachment);
    $('#vt-attachments-count').text(taskData.attachments.length ?? 0);
    renderActivitiesList(taskData.activities, '#vt-activities-list');
    renderCommentsList(taskData.comments, '#vt-comments-list');
    renderFeedbackList(taskData.feedbacks || [], '#vt-feedback-list');
    renderSubmissionsList(taskData.submissions || [], '#vt-submissions-list', taskData.assignees);

    $('#vt-comment-count').text(taskData.comments_count ?? (taskData.comments ? taskData.comments.length : 0));
    $('#vt-feedback-count').text(taskData.feedbacks_count ?? (taskData.feedbacks ? taskData.feedbacks.length : 0));
    $('#vt-submissions-count').text(taskData.submissions_count || (taskData.submissions ? taskData.submissions.length : 0));
    $('#vt-activity-count').text(taskData.activities_count ?? (taskData.activities ? taskData.activities.length : 0));

    // Toggle "Your Submission" tab — hidden when task is completed
    const $ysTabBtn = $('#viewTaskOffcanvas .nav-tabs .nav-link[data-bs-target="#vt-your-submission"]');
    const $ysPane   = $('#vt-your-submission');
    if (taskData.status === 'completed') {
      $ysTabBtn.addClass('d-none');
      $ysPane.addClass('d-none');
    } else {
      $ysTabBtn.removeClass('d-none');
      $ysPane.removeClass('d-none');
    }

    $('#vt-edit-trigger-btn').data('task-id', taskId);
    $('#viewTaskOffcanvas').data('current-task-id', taskId);
    $('#vt-upload-btn-task-id').val(taskId);
    $('#vt-send-comment-btn').data('task-id', taskId);

    dzReset();
    if (vtCommentQ) vtCommentQ.setContents([]);

    // Reset to first tab
    bootstrap.Tab.getOrCreateInstance(document.querySelector('#viewTaskOffcanvas .nav-tabs .nav-link')).show();
    bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('viewTaskOffcanvas')).show();
  });

  $(document).on('click', '#vt-edit-trigger-btn', function () {
    const taskId = $(this).data('task-id');
    const ocEl   = document.getElementById('viewTaskOffcanvas');
    const oc     = bootstrap.Offcanvas.getInstance(ocEl);

    if (oc) {
      $(ocEl).one('hidden.bs.offcanvas', () => openEditTask(taskId));
      oc.hide();
    } else {
      openEditTask(taskId);
    }
  });

  // ─── Add Task ──────────────────────────────────────────────────────────────

  $('#addTaskForm').on('submit', function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#add-submit-btn');

    addFv.validate().then(function (status) {
      if (status !== 'Valid') return;

      $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-2"></span>${lbl.processing}`);

      const fd = new FormData($form[0]);
      fd.append('description', addDescQ.root.innerHTML);
      fd.append('program_id', program.id);
      ($('#add-assignees').val() || []).forEach(id => fd.append('assignees[]', id));
      addDz.getFiles().forEach(f => fd.append('attachments[]', f));

      $.ajax({
        url: cfg.routes.storeTask,
        type: 'POST', data: fd, processData: false, contentType: false,
        success: function (response) {
          toastr.success(lbl.storedSuccessfully);
          const d = response.data || {};
          const newTask = {
            id: d.id || Date.now(),
            title: d.title || $form.find('[name="title"]').val(),
            description: d.description || addDescQ.root.innerHTML,
            due_date: d.due_date || $form.find('[name="due_date"]').val() || null,
            priority: d.priority || $('#add-priority').val(),
            status: d.status || $('#add-status').val(),
            creator: d.creator || null,
            attachments: d.attachments || [],
            activities: [],
            comments: [],
            assignees: (d.assignees || []).map(a => ({
              id: a.id,
              name: a.name_en || a.name_ar || 'N/A',
              img_url: a.logo || null,
              profile_url: '/dashboard/companyProfile/' + a.id,
            })),
          };
          tasks.push(newTask);
          syncTaskToBoard(newTask, null);
          $form[0].reset();
          addDescQ.setContents([]);
          addDz.reset();
          $('#add-priority, #add-status').val('').trigger('change');
          $('#add-assignees').val(null).trigger('change');
          addFv.resetForm(true);
          $('#addTaskOffcanvas').offcanvas('hide');
          $btn.prop('disabled', false).html(lbl.create);
        },
        error: function () {
          toastr.error(lbl.saveFailed);
          $btn.prop('disabled', false).text(lbl.create);
        }
      });
    });
  });

  // ─── Edit Task ─────────────────────────────────────────────────────────────

  function openEditTask(taskId) {
    const taskData = tasks.find(t => t.id == taskId);
    if (!taskData) return;

    $('#et-id').val(taskData.id);
    $('#et-title').val(taskData.title);
    $('#et-due-date').val(taskData.due_date);
    etDescQ.root.innerHTML = taskData.description;
    $('#et-priority').val(taskData.priority).trigger('change');
    $('#et-status').val(taskData.status).trigger('change');
    const currentAssigneeIds = (taskData.assignees || []).map(a => String(a.id));
    $('#et-assignees').val(currentAssigneeIds).trigger('change');
    etDz.reset();
    $('#et-file-error').text('');

    if (taskData.attachments && taskData.attachments.length) {
      $('#et-current-attachments-wrapper').removeClass('d-none');
      renderAttachmentsList(taskData.attachments, taskData.id, '#et-current-attachments-list', cfg.isAbleToDeleteAttachment);
    } else {
      $('#et-current-attachments-wrapper').addClass('d-none');
    }
    bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('editTaskOffcanvas')).show();
  }

  $(document).on('click', '.edit-task-btn', function () {
    openEditTask($(this).data('task-id'));
  });

  $('#editTaskForm').on('submit', function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#et-submit-btn');

    etFv.validate().then(function (status) {
      if (status !== 'Valid') return;

      const taskId = $('#et-id').val();
      $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-2"></span>${lbl.processing}`);

      const fd = new FormData($form[0]);
      fd.append('description', etDescQ.root.innerHTML);
      ($('#et-assignees').val() || []).forEach(id => fd.append('assignees[]', id));
      etDz.getFiles().forEach(f => fd.append('attachments[]', f));

      $.ajax({
        url: cfg.routes.updateTask.replace(':id', taskId),
        type: 'POST', data: fd, processData: false, contentType: false,
        success: function (response) {
          toastr.success(lbl.storedSuccessfully);
          const idx = tasks.findIndex(t => t.id == taskId);
          if (idx !== -1) {
            const oldStatus = tasks[idx].status;
            const updatedTask = response.task || {
              ...tasks[idx],
              title: $('#et-title').val(),
              description: etDescQ.root.innerHTML,
              due_date: $('#et-due-date').val(),
              priority: $('#et-priority').val(),
              status: $('#et-status').val(),
              attachments: response.attachments || tasks[idx].attachments,
            };
            tasks[idx] = updatedTask;
            syncTaskToBoard(updatedTask, oldStatus);
          }
          $('#editTaskOffcanvas').offcanvas('hide');
          etDz.reset();
          $('#et-assignees').val(null).trigger('change');
          $btn.prop('disabled', false).html(lbl.update);
        },
        error: function () {
          toastr.error(lbl.updateFailed);
          $btn.prop('disabled', false).text(lbl.update);
        }
      });
    });
  });

  // ─── Archive Task ──────────────────────────────────────────────────────────

  $(document).on('click', '.archive-task-btn', function () {
    const taskId = $(this).data('task-id');
    const task = tasks.find(t => t.id === taskId);
    if (!task) return;

    const isArchived = task.status === 'archived';
    const newStatus = isArchived ? 'pending' : 'archived';
    const actionLabel = isArchived ? lbl.reopenTask : lbl.archiveTask;
    const bodyText = isArchived ? lbl.reopenTaskConfirm : lbl.archiveTaskConfirm;

    releaseWarningAlert({
      title: actionLabel + '?', message: bodyText,
      confirmText: lbl.yes, cancelText: lbl.no,
      confirmColor: isArchived ? 'warning' : 'secondary',
      onConfirm: () => {
        $.ajax({
          url: cfg.routes.moveTask.replace(':id', taskId).replace(':status', newStatus),
          type: 'PATCH', contentType: 'application/json',
          data: JSON.stringify({ status: newStatus }),
          success: function () {
            const oldStatus = task.status;
            task.status = newStatus;
            if (newStatus === 'archived') {
              $(`.kanban-item[data-task-id="${taskId}"]`).fadeOut(300, function () {
                $(this).remove();
                const $board = $(`.kanban-board[data-board="${oldStatus}"] .kanban-drag`);
                if ($board.find('.kanban-item:not(.d-none)').length === 0) {
                  $board.find('[data-empty-board]').remove();
                  $board.append(`<p class="text-muted small text-center py-3 m-0" data-empty-board>${lbl.noTasks}</p>`);
                }
                $('.kanban-board').each(function () {
                  $(this).find('.board-count').text($(this).find('.kanban-item:not(.d-none)').length);
                });
              });
            } else {
              syncTaskToBoard(task, oldStatus);
            }
            toastr.success(isArchived ? lbl.taskReopened : lbl.taskArchived);
          },
          error: () => toastr.error(lbl.updateFailed)
        });
      }
    });
  });

  // ─── Delete Attachment ─────────────────────────────────────────────────────

  $(document).on('click', '.delete-attachment-btn', function () {
    const $btn = $(this);
    const taskId = $btn.data('task-id');
    const attachmentId = $btn.data('attachment-id');
    const attachmentName = $btn.data('attachment-name');

    releaseWarningAlert({
      title: lbl.deleteAttachment + '?', message: lbl.recover,
      confirmText: lbl.yes, cancelText: lbl.no, confirmColor: 'danger',
      onConfirm: () => {
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        $.ajax({
          url: cfg.routes.deleteAttachment.replace(':taskId', taskId).replace(':filename', encodeURIComponent(attachmentName || attachmentId)),
          type: 'DELETE',
          success: function () {
            $(`#att-row-${attachmentId}`).fadeOut(300, function () {
              $(this).remove();
              toastr.success(lbl.attachmentDeletedSuccess);
              const task = tasks.find(t => t.id == taskId);
              if (task && task.attachments) {
                task.attachments = task.attachments.filter(a => a.name !== attachmentName && a.id != attachmentId);
              }
              if ($('#vt-attachments-list .attachment-item').length === 0) {
                $('#vt-attachments-list').html(`<span class="text-muted small">${lbl.noAttachments}</span>`);
              }
              if ($('#et-current-attachments-list .attachment-item').length === 0) {
                $('#et-current-attachments-wrapper').addClass('d-none');
              }
            });
          },
          error: function () {
            toastr.error(lbl.deleteFailed);
            $btn.prop('disabled', false).html('<i class="ti ti-trash ti-xs"></i>');
          }
        });
      }
    });
  });

  // ─── Add Comment ───────────────────────────────────────────────────────────

  $('#vt-send-comment-btn').on('click', function () {
    if (!vtCommentQ) return;
    const taskId = $(this).data('task-id');
    const html = vtCommentQ.root.innerHTML;
    const plainText = vtCommentQ.getText().trim();
    if (!plainText) return;

    const $btn = $(this);
    $btn.prop('disabled', true).text('...');

    $.ajax({
      url: cfg.routes.addComment.replace(':id', taskId),
      type: 'POST', contentType: 'application/json',
      data: JSON.stringify({ comment: html }),
      success: function (response) {
        toastr.success(lbl.commentSuccess);
        const c = response.comment || { user: lbl.you, time: lbl.justNow, text: html, img_url: null };

        if ($('#vt-comments-list p.text-muted').length) $('#vt-comments-list').empty();

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

        $('#vt-comment-count').text((parseInt($('#vt-comment-count').text()) || 0) + 1);
        const task = tasks.find(t => t.id == taskId);
        if (task) { if (!task.comments) task.comments = []; task.comments.push(c); }
        if (vtCommentQ) vtCommentQ.setContents([]);
        $btn.prop('disabled', false).text(lbl.send);
      },
      error: function () {
        toastr.error(lbl.commentFailed);
        $btn.prop('disabled', false).text(lbl.send);
      }
    });
  });

  // ─── Submit Feedback ───────────────────────────────────────────────────────

  $('#vt-submit-feedback-btn').on('click', function () {
    const taskId = $('#vt-send-comment-btn').data('task-id');
    const decision = $('input[name="vt_feedback_decision"]:checked').val();
    const note = $('#vt-fb-note').val().trim();
    if (!decision) { toastr.warning(lbl.requiredField); return; }
    if (!note) {
      $('#vt-fb-note').addClass('is-invalid');
      $('#vt-fb-note').siblings('.invalid-feedback').remove();
      $('#vt-fb-note').after(`<div class="invalid-feedback d-block">${lbl.requiredField}</div>`);
      return;
    }
    $('#vt-fb-note').removeClass('is-invalid').siblings('.invalid-feedback').remove();

    const $btn = $(this);
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>');

    $.ajax({
      url: cfg.routes.addFeedback.replace(':id', taskId),
      type: 'POST', contentType: 'application/json',
      data: JSON.stringify({ decision, note }),
      success: function (response) {
        toastr.success(lbl.feedbackSuccess);
        const fb = response.feedback || {
          user: cfg.currentUserName, img_url: null,
          time: lbl.justNow, decision, text: note,
        };

        const task = tasks.find(t => t.id == taskId);
        if (task) {
          if (!task.feedbacks) task.feedbacks = [];
          task.feedbacks.push(fb);
          $('#vt-feedback-count').text(task.feedbacks.length);
          // Move task to the appropriate board based on decision
          const oldStatus = task.status;
          const newStatus = decision === 'pass' ? 'completed' : 'pending';
          if (oldStatus !== newStatus) {
            task.status = newStatus;
            syncTaskToBoard(task, oldStatus);
            // Update the status badge in the offcanvas details tab
            const statusMap = cfg.statusMap;
            $('#vt-status').html(`<span class="badge bg-label-${newStatus === 'completed' ? 'success' : 'secondary'} rounded-pill">${statusMap[newStatus] || newStatus}</span>`);
          }
        }

        const $list = $('#vt-feedback-list');
        if ($list.find('p.text-muted').length) $list.empty();

        const badge = decision === 'pass'
          ? `<span class="badge bg-label-success ms-2"><i class="ti ti-circle-check ti-xs me-1"></i>${lbl.passTask}</span>`
          : `<span class="badge bg-label-warning ms-2"><i class="ti ti-refresh ti-xs me-1"></i>${lbl.needsRevision}</span>`;

        $list.prepend(`
          <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
            <div class="flex-shrink-0 mt-1">${buildAvatar(fb.user, fb.img_url, fb.user_id)}</div>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                <span class="fw-semibold text-dark">${fb.user}${badge}</span>
                <span class="text-muted" style="font-size:.7rem;">${fb.time}</span>
              </div>
              ${note ? `<div class="small text-body bg-lighter rounded p-2">${note}</div>` : ''}
            </div>
          </div>`);

        $('#vt-fb-note').val('').removeClass('is-invalid').siblings('.invalid-feedback').remove();
        $('input[name="vt_feedback_decision"][value="pass"]').prop('checked', true);
        $('#vt-fb-pass-wrapper').addClass('checked');
        $('#vt-fb-revision-wrapper').removeClass('checked');
        $btn.prop('disabled', false).html(`<i class="ti ti-send ti-xs me-1"></i>${lbl.submitFeedback}`);
      },
      error: function () {
        toastr.error(lbl.feedbackFailed);
        $btn.prop('disabled', false).html(`<i class="ti ti-send ti-xs me-1"></i>${lbl.submitFeedback}`);
      }
    });
  });

  // Toggle custom radio .checked class
  $(document).on('change', 'input[name="vt_feedback_decision"]', function () {
    $('input[name="vt_feedback_decision"]').each(function () {
      $(this).closest('.custom-option').toggleClass('checked', $(this).is(':checked'));
    });
  });

  // Clear feedback note validation on input
  $(document).on('input', '#vt-fb-note', function () {
    if ($(this).val().trim()) {
      $(this).removeClass('is-invalid');
    }
  });
}
