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
         data-task-id="${taskId}" data-attachment-id="${att.id}" data-attachment-name="${att.name}"
         title="${window.KanbanConfig.labels.remove}">
         <i class="ti ti-trash ti-xs"></i>
       </button>`
    : '';

  const createdAt = att.created_at
    ? moment(att.created_at).format('YYYY-MM-DD h:mm')
    : '';

  const statusMap = {
    feedback:   { label: 'Feedback',   color: 'warning'  },
    submission: { label: 'Submission', color: 'info'     },
    attachment: { label: 'Attachment', color: 'secondary' },
  };
  const statusInfo = statusMap[att.status] || statusMap['attachment'];
  const statusBadge = `<span class="badge bg-label-${statusInfo.color} ms-1" style="font-size:.65rem;">${statusInfo.label}</span>`;

  return `
    <div class="d-flex flex-column border rounded px-2 bg-light-subtle attachment-item" id="att-row-${att.id}">

      <div class="d-flex align-items-center justify-content-between">
        <a href="${att.url}" target="_blank"
           class="text-body fw-medium text-truncate d-flex align-items-center gap-1 flex-grow-1" style="min-width:0;">
          <i class="ti ${fileIcon(att.name)} ti-xs flex-shrink-0"></i>
          <span class="text-truncate small">${att.name}</span>
          ${statusBadge}
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

const ACTIVITY_META = {
  created:  { icon: 'ti-plus',               color: 'success'   },
  updated:  { icon: 'ti-pencil',             color: 'info'      },
  moved:    { icon: 'ti-arrows-right-left',  color: 'warning'   },
  comment:  { icon: 'ti-message-circle',     color: 'primary'   },
  feedback: { icon: 'ti-star',               color: 'warning'   },
  deleted:  { icon: 'ti-trash',              color: 'danger'    },
};

function getActivityMeta(action) {
  if (!action) return { icon: 'ti-activity', color: 'secondary' };
  const a = action.toLowerCase();
  for (const [key, meta] of Object.entries(ACTIVITY_META)) {
    if (a.startsWith(key) || a.includes(key)) return meta;
  }
  return { icon: 'ti-activity', color: 'secondary' };
}

// Builds a single activity timeline <li>. Shared by the initial render and
// the live-prepend flows (task move, submission upload) so all activity items
// stay visually identical. `isLast` only affects bottom spacing.
function buildActivityTimelineItem(act, isLast) {
  const { icon, color } = getActivityMeta(act.action);
  return `
      <li class="timeline-item timeline-item-transparent${isLast ? ' pb-1' : ''}">
        <span class="timeline-indicator timeline-indicator-${color}">
          <i class="ti ${icon}" style="font-size:.85rem;"></i>
        </span>
        <div class="timeline-event pb-${isLast ? '0' : '3'}">
          <div class="rounded-2 p-3 bg-light-subtle" style="border-left:3px solid var(--bs-${color});">

            <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
              <div class="d-flex align-items-center gap-2">
                ${buildAvatar(act.user, act.img_url, act.user_id)}
                <span class="fw-semibold small text-dark">${act.user}</span>
              </div>
              <span class="badge bg-label-${color}" style="font-size:.65rem;">
                <i class="ti ti-clock ti-xs me-1"></i>${act.time}
              </span>
            </div>

            <p class="mb-0 small text-body lh-sm">${act.action}</p>

            ${act.created_at ? `
            <div class="mt-2 pt-2 border-top">
              <span class="text-muted" style="font-size:.68rem;">
                <i class="ti ti-calendar ti-xs me-1"></i>${act.created_at}
              </span>
            </div>` : ''}

          </div>
        </div>
      </li>`;
}

// Prepends a newly-created activity to the open offcanvas's activity list and
// bumps the badge. No-op unless the offcanvas is showing `taskId`. Used by the
// task-move and submission-upload flows.
function prependActivityToOffcanvas(act, taskId) {
  const $oc = $('#viewTaskOffcanvas');
  if (!$oc.hasClass('show') || $oc.data('current-task-id') != taskId) return;

  const $list = $('#vt-activities-list');
  const $ul   = $list.find('ul.timeline');
  const html  = buildActivityTimelineItem(act, false);
  if ($ul.length) {
    $ul.prepend(html);
  } else {
    $list.html(`<ul class="timeline pt-1 ps-1">${html}</ul>`);
  }

  const current = parseInt($('#vt-activity-count').text()) || 0;
  $('#vt-activity-count').text(current + 1);
}

function renderActivitiesList(activities, containerSelector) {
  const $c = $(containerSelector);
  if (!activities || !activities.length) {
    $c.html(`
      <div class="text-center py-5">
        <div class="avatar avatar-md mb-3 mx-auto">
          <span class="avatar-initial rounded-circle bg-label-secondary">
            <i class="ti ti-activity ti-md"></i>
          </span>
        </div>
        <p class="text-muted mb-0 small">${window.KanbanConfig.labels.noActivities}</p>
      </div>`);
    return;
  }

  const items = activities
    .map((act, i) => buildActivityTimelineItem(act, i === activities.length - 1))
    .join('');

  $c.html(`<ul class="timeline pt-1 ps-1">${items}</ul>`);
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
      <div class="flex-grow-1 bg-light-subtle rounded p-3">
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
          ${fb.text ? `<div class="small text-body bg-light-subtle rounded p-2">${fb.text}</div>` : ''}
        </div>
      </div>`;
  }).join(''));
}

// ─── Submissions ──────────────────────────────────────────────────────────────

function renderSubmissionsList(submissions, containerSelector, assignees) {
  const $c = $(containerSelector);
  const lbl = window.KanbanConfig.labels;

  let assigneeHtml = '';
  if (assignees && assignees.length) {
    const ASSIGNEE_STATUS_BADGE = {
      pending: `<span class="badge bg-label-warning" style="font-size:.65rem;">${lbl.pending}</span>`,
      progress: `<span class="badge bg-label-info" style="font-size:.65rem;">${lbl.progress}</span>`,
      review: `<span class="badge bg-label-primary" style="font-size:.65rem;">${lbl.review}</span>`,
      completed: `<span class="badge bg-label-success" style="font-size:.65rem;">${lbl.completed}</span>`,
      archived: `<span class="badge bg-label-secondary" style="font-size:.65rem;">${lbl.archived}</span>`,
      pending_review: `<span class="badge bg-label-warning" style="font-size:.65rem;">${lbl.pendingReview}</span>`,
      approved: `<span class="badge bg-label-success" style="font-size:.65rem;">${lbl.approved}</span>`,
      rejected: `<span class="badge bg-label-danger" style="font-size:.65rem;">${lbl.rejected}</span>`,
    };

    assigneeHtml = `
      <div class="mb-4 p-3 bg-light-subtle rounded border">
        <div class="small fw-semibold text-uppercase text-muted mb-2 d-flex align-items-center gap-1">
          <i class="ti ti-user"></i> ${lbl.assignee || 'Assignee'}
        </div>
        <div class="d-flex flex-column gap-2">
          ${assignees.map(a => {
            const imgUrl = a.logo || a.img_url;
            const img = imgUrl
              ? `<img src="${imgUrl}" alt="${a.name}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">`
              : `<span class="avatar-initial bg-label-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:12px;">${(a.name || 'U').substring(0, 2).toUpperCase()}</span>`;
            return `
              <div class="d-flex align-items-center gap-2">
                <span class="d-inline-flex flex-shrink-0">${img}</span>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <a href="${a.profile_url || '#'}" target="_blank" class="fw-semibold text-dark text-decoration-none small">${a.name}</a>
                  ${ASSIGNEE_STATUS_BADGE[a.status] || `<span class="badge bg-label-secondary" style="font-size:.65rem;">${a.status}</span>`}
                </div>
              </div>`;
          }).join('')}
        </div>
      </div>`;
  }

  if (!submissions || !submissions.length) {
    $c.html(`
      ${assigneeHtml}
      <div class="text-center py-5">
        <i class="ti ti-inbox ti-lg text-muted mb-2"></i>
        <p class="text-muted mb-0">${lbl.noSubmissions}</p>
      </div>
    `);
    return;
  }

  const STATUS_BADGE = {
    pending_review: `<span class="badge bg-label-warning">${lbl.pendingReview}</span>`,
    approved: `<span class="badge bg-label-success">${lbl.approved}</span>`,
    rejected: `<span class="badge bg-label-danger">${lbl.rejected}</span>`,
  };

  $c.html(
    assigneeHtml +

    submissions
      .map((sub, index) => {
        const hasUser = !!(sub.user || sub.user_id);
        const statusBadge = hasUser ? STATUS_BADGE[sub.status] || "" : "";

        const filesHtml = (sub.files || []).length
          ? `
          <div class="mt-3">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="ti ti-paperclip text-muted"></i>
              <span class="fw-semibold small text-uppercase text-muted">
                ${lbl.submittedFiles || "Submitted Files"}
              </span>
            </div>

            <div class="d-flex flex-column gap-2">
              ${sub.files
                .map((f) => {
                  const sz = f.size_bytes ? formatFileSize(f.size_bytes) : "";

                  return `
                  <a href="${f.url}"
                     download="${f.name}"
                     class="d-flex align-items-center gap-3 p-2 rounded border bg-body text-body text-decoration-none hover-shadow-sm">

                    <div class="rounded bg-label-secondary d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:38px;height:38px;">
                      <i class="ti ${fileIcon(f.name)}"></i>
                    </div>

                    <div class="flex-grow-1 overflow-hidden">
                      <div class="small fw-medium text-truncate">${f.name}</div>
                      <small class="text-muted">${[sz, f.created_at ? new Date(f.created_at).toLocaleDateString() : ""].filter(Boolean).join(" · ")}</small>
                    </div>

                    <i class="ti ti-download text-muted flex-shrink-0"></i>
                  </a>
                `;
                })
                .join("")}
            </div>
          </div>
        `
          : `
          <div class="mt-3 small text-muted">
            <i class="ti ti-paperclip-off me-1"></i>
            ${lbl.noSubmittedFilesAvailable || "No submitted files available."}
          </div>
        `;

        const avatarHtml = hasUser
          ? buildAvatar(sub.user, sub.img_url, sub.user_id)
          : `
          <span class="d-inline-flex rounded-circle bg-label-secondary"
                style="width:40px;height:40px;">
            <span class="d-flex align-items-center justify-content-center w-100 h-100">
              <i class="ti ti-file-text"></i>
            </span>
          </span>
        `;

        return `
        <div class="card shadow-none border mb-3">
          <div class="card-body">

            <div class="d-flex align-items-start justify-content-between gap-3">
              
              <div class="d-flex align-items-center gap-2">
                ${avatarHtml}

                <div>
                  ${
                    hasUser
                      ? `
                        <div class="d-flex align-items-center flex-wrap gap-2">
                          <span class="fw-semibold text-dark">
                            ${sub.user || ""}
                          </span>
                          ${statusBadge}
                        </div>
                      `
                      : `
                        <span class="fw-semibold text-dark">
                          ${lbl.submission || "Submission"} ${index + 1}
                        </span>
                      `
                  }

                  ${
                    sub.time
                      ? `<small class="text-muted d-block">${sub.time}</small>`
                      : ""
                  }
                </div>
              </div>

            </div>

            <div class="mt-3">
              <div class="small fw-semibold text-uppercase text-muted mb-1">
                ${lbl.submissionNote || "Submission Note"}
              </div>
              ${
                sub.note
                  ? `<div class="bg-light-subtle rounded p-3 small text-body">${sub.note}</div>`
                  : `<div class="bg-light-subtle rounded p-3 small text-muted fst-italic">${lbl.noNotesAvailable || "No notes available."}</div>`
              }
            </div>

            ${filesHtml}

          </div>
        </div>
      `;
      })
      .join(""),
  );
}
