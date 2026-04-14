/**
 * kanban-renderers.js
 * Pure HTML-builder functions for all offcanvas tab content.
 * Depends on: kanban-utils.js (buildAvatar, fileIcon, formatFileSize)
 * Reads labels from: window.KanbanConfig.labels
 */

// ─── Attachment item ──────────────────────────────────────────────────────────

function buildAttachmentItem(att, taskId, canDelete) {
  const deleteBtn = canDelete
    ? `<button type="button" class="btn btn-icon btn-sm btn-text-danger rounded-circle delete-attachment-btn"
         data-task-id="${taskId}" data-attachment-id="${att.id}"
         title="${window.KanbanConfig.labels.remove}">
         <i class="ti ti-trash ti-xs"></i>
       </button>`
    : '';

  const createdAt = att.created_at
    ? moment(att.created_at).format('YYYY-MM-DD h:mm')
    : '';

  return `
    <div class="d-flex flex-column border rounded p-2 bg-lighter attachment-item" id="att-row-${att.id}">
      
      <div class="d-flex align-items-center justify-content-between">
        <a href="${att.url}" target="_blank"
           class="text-body fw-medium text-truncate d-flex align-items-center gap-1 flex-grow-1" style="min-width:0;">
          <i class="ti ti-paperclip ti-xs flex-shrink-0 text-primary"></i>
          <span class="text-truncate">${att.name}</span>
        </a>
        <div class="d-flex align-items-center gap-1 flex-shrink-0 ms-2">
          <a href="${att.url}" download="${att.name}"
             class="btn btn-icon btn-sm btn-text-secondary rounded-circle"
             title="${window.KanbanConfig.labels.download}">
            <i class="ti ti-download ti-xs"></i>
          </a>
          ${deleteBtn}
        </div>
      </div>

      <small class="text-muted mt-1">
        ${createdAt}
      </small>

    </div>`;
}

function renderAttachmentsList(attachments, taskId, containerSelector, canDelete = true) {
  const $c = $(containerSelector);
  if (!attachments || !attachments.length) {
    $c.html(`<span class="text-muted small">${window.KanbanConfig.labels.noAttachments}</span>`);
    return;
  }
  $c.html(attachments.map(att => buildAttachmentItem(att, taskId, canDelete)).join(''));
}

// ─── Activities ───────────────────────────────────────────────────────────────

function renderActivitiesList(activities, containerSelector) {
  const $c = $(containerSelector);
  if (!activities || !activities.length) {
    $c.html(`<p class="text-muted text-center py-4 mb-0">${window.KanbanConfig.labels.noActivities}</p>`);
    return;
  }
  $c.html(activities.map(act => `
    <div class="d-flex align-items-start gap-3 mb-3 pb-2 border-bottom">
      <div class="flex-shrink-0 mt-1">${buildAvatar(act.user, act.img_url, act.user_id)}</div>
      <div>
        <p class="mb-1"><span class="fw-semibold text-dark">${act.user}</span> ${act.action}</p>
        <span class="text-muted" style="font-size:.75rem;">${act.time}</span>
      </div>
    </div>`).join(''));
}

// ─── Comments ─────────────────────────────────────────────────────────────────

function renderCommentsList(comments, containerSelector) {
  const $c = $(containerSelector);
  if (!comments || !comments.length) {
    $c.html(`<p class="text-muted text-center py-4 mb-0">${window.KanbanConfig.labels.noComments}</p>`);
    return;
  }
  $c.html(comments.map(c => `
    <div class="d-flex align-items-start gap-3 mb-3">
      <div class="flex-shrink-0 mt-1">${buildAvatar(c.user, c.img_url, c.user_id)}</div>
      <div class="flex-grow-1 bg-lighter rounded p-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="fw-semibold text-dark">${c.user}</span>
          <span class="text-muted" style="font-size:.7rem;">${c.time}</span>
        </div>
        <div class="ql-editor p-0 small text-body" style="min-height:auto;">${c.text}</div>
      </div>
    </div>`).join(''));
}

// ─── Feedback ─────────────────────────────────────────────────────────────────

function renderFeedbackList(feedbacks, containerSelector) {
  const $c  = $(containerSelector);
  const lbl = window.KanbanConfig.labels;
  if (!feedbacks || !feedbacks.length) {
    $c.html(`<p class="text-muted text-center py-4 mb-0">${lbl.noFeedback}</p>`);
    return;
  }
  $c.html(feedbacks.map(fb => {
    const badge = fb.decision === 'pass'
      ? `<span class="badge bg-label-success ms-1"><i class="ti ti-circle-check ti-xs me-1"></i>${lbl.passTask}</span>`
      : `<span class="badge bg-label-warning ms-1"><i class="ti ti-refresh ti-xs me-1"></i>${lbl.needsRevision}</span>`;
    return `
      <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
        <div class="flex-shrink-0 mt-1">${buildAvatar(fb.user, fb.img_url, fb.user_id)}</div>
        <div class="flex-grow-1">
          <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
            <span class="fw-semibold text-dark">${fb.user || ''}${badge}</span>
            <span class="text-muted" style="font-size:.7rem;">${fb.time || ''}</span>
          </div>
          ${fb.text ? `<div class="small text-body bg-lighter rounded p-2">${fb.text}</div>` : ''}
        </div>
      </div>`;
  }).join(''));
}

// ─── Submissions ──────────────────────────────────────────────────────────────

function renderSubmissionsList(submissions, containerSelector) {
  const $c  = $(containerSelector);
  const lbl = window.KanbanConfig.labels;
  if (!submissions || !submissions.length) {
    $c.html(`<p class="text-muted text-center py-4 mb-0">${lbl.noSubmissions}</p>`);
    return;
  }

  const STATUS_BADGE = {
    pending_review: `<span class="badge bg-label-warning ms-1">${lbl.pendingReview}</span>`,
    approved:       `<span class="badge bg-label-success ms-1">${lbl.approved}</span>`,
    rejected:       `<span class="badge bg-label-danger ms-1">${lbl.rejected}</span>`,
  };

  $c.html(submissions.map(sub => {
    const statusBadge = STATUS_BADGE[sub.status] || '';
    const filesHtml = (sub.files || []).length
      ? sub.files.map(f => {
          const sz = f.size_bytes ? formatFileSize(f.size_bytes) : '';
          return `
            <a href="${f.url}" download="${f.name}"
               class="d-flex align-items-center gap-2 border rounded p-2 bg-body text-body text-decoration-none mb-1">
              <i class="ti ${fileIcon(f.name)} ti-sm flex-shrink-0"></i>
              <span class="text-truncate small flex-grow-1">${f.name}</span>
              ${sz ? `<span class="text-muted flex-shrink-0" style="font-size:.7rem;">${sz}</span>` : ''}
              <i class="ti ti-download ti-xs ms-1 text-muted flex-shrink-0"></i>
            </a>`;
        }).join('')
      : `<span class="text-muted small">${lbl.noAttachments}</span>`;

    return `
      <div class="mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center gap-2 mb-2">
          ${buildAvatar(sub.user, sub.img_url, sub.user_id)}
          <div>
            <span class="fw-semibold text-dark d-block">${sub.user || ''}${statusBadge}</span>
            <span class="text-muted" style="font-size:.7rem;">${sub.time || ''}</span>
          </div>
        </div>
        ${sub.note ? `<p class="small text-muted mb-2">${sub.note}</p>` : ''}
        <div>${filesHtml}</div>
      </div>`;
  }).join(''));
}
