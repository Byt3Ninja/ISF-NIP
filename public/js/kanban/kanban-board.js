/**
 * kanban-board.js
 * Kanban card builder, board sync, drag-and-drop, live search.
 * Depends on: kanban-utils.js
 * Reads config from: window.KanbanConfig
 */

// ─── Card builder ─────────────────────────────────────────────────────────────

function buildTaskCard(task) {
  const cfg = window.KanbanConfig;
  const p = cfg.priorityMap[task.priority] || cfg.priorityMap["Low"];
  const r = task.creator || { name: "User", img_url: null };
  const ri = (r.name || "U").substring(0, 2).toUpperCase();
  const attCount =
    task.attachments_count ?? (task.attachments ? task.attachments.length : 0);
  const commentCount =
    task.comments_count ?? (task.comments ? task.comments.length : 0);

  const avatarHtml = r.img_url
    ? `<img src="${r.img_url}" alt="avatar" class="rounded-circle object-fit-cover" style="width:24px;height:24px;">`
    : `<span class="badge bg-label-primary rounded-circle d-flex align-items-center justify-content-center p-0" style="width:24px;height:24px;">${ri}</span>`;

  const editBtn = cfg.isAbleToEdit
    ? `<button class="btn btn-sm btn-icon btn-text-secondary rounded-circle edit-task-btn"
         type="button" data-task-id="${task.id}" title="${cfg.labels.edit}">
         <i class="ti ti-edit ti-md text-dark"></i>
       </button>`
    : "";

  const archiveBtn = cfg.isAbleToEdit
    ? ` <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle archive-task-btn"
        type="button" data-task-id="${task.id}" title="${cfg.labels.archiveTask}">
        <i class="ti ti-archive ti-md text-dark"></i>
      </button>`
    : "";

  return `
    <div class="card shadow-sm border-0 cursor-grab kanban-item"
         data-task-id="${task.id}"
         data-search-text="${(task.title || "").toLowerCase()}">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="badge ${p.cls} rounded-pill" style="font-size:.7rem;">
            <span class="d-inline-block rounded-circle me-1 ${p.dot}" style="width:6px;height:6px;"></span>${p.label}
          </span>
          <div class="d-flex align-items-center gap-1">
            <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle view-task-btn"
              type="button" data-task-id="${task.id}" title="${cfg.labels.view}">
              <i class="ti ti-eye ti-md text-dark"></i>
            </button>
            ${editBtn}
            ${archiveBtn}
          </div>
        </div>
        <h6 class="mb-1 text-truncate">${$("<div>").text(task.title).html()}</h6>
        <div class="text-muted small mb-2 line-clamp-2">${$("<div>").html(task.description).text()}</div>
        <div class="d-flex align-items-center justify-content-between small text-muted mt-3">
          <span class="d-flex align-items-center gap-2">
            ${attCount > 0 ? `<span class="d-flex align-items-center" title="${attCount} Attachments"><i class="ti ti-paperclip ti-xs me-1"></i>${attCount}</span>` : ""}
            ${commentCount > 0 ? `<span class="d-flex align-items-center" title="${commentCount} Comments"><i class="ti ti-message ti-xs me-1"></i>${commentCount}</span>` : ""}
            <span class="d-flex align-items-center"><i class="ti ti-calendar ti-xs me-1"></i> ${task.due_date || ""}</span>
          </span>
          <div class="d-flex align-items-center gap-2">
            <span>${cfg.labels.reporter}:</span>
            <div data-bs-toggle="tooltip" data-bs-placement="top" title="${$("<div>").text(r.name).html()}">${avatarHtml}</div>
          </div>
        </div>
      </div>
    </div>`;
}

// ─── Empty-board helper ───────────────────────────────────────────────────────

/**
 * Ensures exactly one empty-board placeholder exists when a board's drag zone
 * has no visible items, and none when it does.
 */
function _syncEmptyMessage($dragZone, cfg) {
  $dragZone.find("[data-empty-board]").remove();
  if ($dragZone.find(".kanban-item:not(.d-none)").length === 0) {
    $dragZone.append(
      `<p class="text-muted small text-center py-3 m-0" data-empty-board>${cfg.labels.noTasks}</p>`,
    );
  }
}

// ─── Board sync ───────────────────────────────────────────────────────────────

function syncTaskToBoard(task, oldStatus) {
  const cfg = window.KanbanConfig;
  const $existing = $(`.kanban-item[data-task-id="${task.id}"]`);
  const $targetDrag = $(
    `.kanban-board[data-board="${task.status}"] .kanban-drag`,
  );
  const cardHtml = buildTaskCard(task);

  if ($existing.length) {
    if (oldStatus && oldStatus !== task.status) {
      const $oldDrag = $(
        `.kanban-board[data-board="${oldStatus}"] .kanban-drag`,
      );
      $existing.remove();
      _syncEmptyMessage($oldDrag, cfg);
      $targetDrag.find("[data-empty-board]").remove();
      $targetDrag.append(cardHtml);
    } else {
      $existing.replaceWith(cardHtml);
    }
  } else {
    $targetDrag.find("[data-empty-board]").remove();
    $targetDrag.append(cardHtml);
  }

  $('[data-bs-toggle="tooltip"]').tooltip();
  _updateBoardCounts();
}

function _updateBoardCounts() {
  $(".kanban-board").each(function () {
    $(this)
      .find(".board-count")
      .text($(this).find(".kanban-item:not(.d-none)").length);
  });
}

// ─── Drag & drop ──────────────────────────────────────────────────────────────

function initKanbanDragDrop(tasks) {
  const cfg = window.KanbanConfig;

  $(".kanban-drag").each(function () {
    new Sortable(this, {
      group: "kanban-boards",
      animation: 150,
      ghostClass: "sortable-ghost",
      onEnd: function (evt) {
        if (evt.from === evt.to) return;

        const taskId = $(evt.item).data("task-id");
        const newStatus = $(evt.to).closest(".kanban-board").data("board");
        const oldStatus = $(evt.from).closest(".kanban-board").data("board");
        const task = tasks.find((t) => t.id === taskId);
        if (task) task.status = newStatus;

        $(evt.to).find("[data-empty-board]").remove();
        _syncEmptyMessage($(evt.from), cfg);
        _updateBoardCounts();

        $.ajax({
          url: cfg.routes.moveTask
            .replace(":id", taskId)
            .replace(":status", newStatus),
          type: "PATCH",
          contentType: "application/json",
          data: JSON.stringify({ status: newStatus }),
          success: function (response) {
            if (response.message) {
              toastr.success(response.message);
            }
            if (response.activity) {
              if (task) {
                if (!task.activities) task.activities = [];
                task.activities.unshift(response.activity);
                task.activities_count = (task.activities_count || 0) + 1;
              }
              const $oc = $('#viewTaskOffcanvas');
              const openTaskId = $oc.data('current-task-id');
              if ($oc.hasClass('show') && openTaskId == taskId) {
                const act = response.activity;
                const { icon, color } = getActivityMeta(act.action);
                const html = `
                  <li class="timeline-item timeline-item-transparent">
                    <span class="timeline-indicator timeline-indicator-${color}">
                      <i class="ti ${icon}" style="font-size:.85rem;"></i>
                    </span>
                    <div class="timeline-event pb-3">
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
                const $list = $('#vt-activities-list');
                const $ul = $list.find('ul.timeline');
                if ($ul.length) {
                  $ul.prepend(html);
                } else {
                  $list.html(`<ul class="timeline pt-1 ps-1">${html}</ul>`);
                }
                const current = parseInt($('#vt-activity-count').text()) || 0;
                $('#vt-activity-count').text(current + 1);
              }
            }
          },
          error: function (xhr) {
            if (task) task.status = oldStatus;
            $(evt.from).find("[data-empty-board]").remove();
            $(evt.from).append(evt.item);
            _syncEmptyMessage($(evt.to), cfg);
            _updateBoardCounts();
            toastr.error(
              xhr?.responseJSON?.message || cfg.labels.moveTaskFailed,
            );
          },
        });
      },
    });
  });
}

// ─── Live search ──────────────────────────────────────────────────────────────

function initKanbanSearch() {
  $("#kanbanSearch").on("input", function () {
    const q = $(this).val().trim().toLowerCase();
    $(".kanban-item").each(function () {
      $(this).toggleClass("d-none", !$(this).data("search-text").includes(q));
    });
    _updateBoardCounts();
  });
}
