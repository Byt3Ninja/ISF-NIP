/**
 * App Kanban
 */

'use strict';

(async function () {
  const kanbanSidebar = document.querySelector('.kanban-update-item-sidebar'),
    kanbanWrapper = document.querySelector('.kanban-wrapper'),
    commentEditor = document.querySelector('.comment-editor')

  // Init kanban Offcanvas
  const kanbanOffcanvas = new bootstrap.Offcanvas(kanbanSidebar);

  let mentorshipTasks;

  if (tasks) mentorshipTasks = tasks;

  const restructureTasks = () => {
    if (mentorshipTasks) {
      return mentorshipTasks.map(task => {
        return {
          comments: task?.comments?.length || 0,
          labels: task.label_ids,
          badgeText: 'Label',
          badge: 'primary',
          assigned: task?.attached ? [task?.attached] : [],
          ...task
        };
      });
    }
  };

  const restructuredTasks = restructureTasks();

  const getModefiedTasks = () => {
    const statusArr = ['pending', 'in-review', 'completed'];

    if (restructuredTasks) {
      return statusArr.map(status => {
        const filteredTasks = restructuredTasks.filter(task => task.status === status);
        const boardTitle = status.charAt(0).toUpperCase() + status.slice(1).replace('-', ' ');

        return {
          id: filteredTasks?.length > 0 ? `board-${status}` : '',
          title: filteredTasks?.length > 0 ? boardTitle : '',
          item: filteredTasks
        };
      });
    }
  };

  const modefiedTasks = getModefiedTasks();

  // Comment editor
  if (commentEditor) {
    new Quill(commentEditor, {
      modules: {
        toolbar: '.comment-toolbar'
      },
      placeholder: 'Write a Comment... ',
      theme: 'snow'
    });
  }

  // Render board dropdown
  function renderBoardDropdown() {
    return (
      "<div class='dropdown'>" +
      "<i class='dropdown-toggle ti ti-dots-vertical cursor-pointer' id='board-dropdown' data-bs-toggle='dropdown' aria-haspopup='true' aria-expanded='false'></i>" +
      "<div class='dropdown-menu dropdown-menu-end' aria-labelledby='board-dropdown'>" +
      "<a class='dropdown-item delete-board' href='javascript:void(0)'> <i class='ti ti-trash ti-xs' me-1></i> <span class='align-middle'>Delete</span></a>" +
      "<a class='dropdown-item' href='javascript:void(0)'><i class='ti ti-edit ti-xs' me-1></i> <span class='align-middle'>Rename</span></a>" +
      "<a class='dropdown-item' href='javascript:void(0)'><i class='ti ti-archive ti-xs' me-1></i> <span class='align-middle'>Archive</span></a>" +
      '</div>' +
      '</div>'
    );
  }

  // Render header
  function renderHeader(color, text) {
    return `
      <div class='d-flex justify-content-between flex-wrap align-items-center mb-2'>
        <div class='item-badges'>
          <div id="label-badge" class='mb-2 badge bg-label-${color}'>
            ${text}
          </div>
        </div>
      </div>
    `;
  }

  // Render footer
  function renderFooter(attachments, comments, assigned, members) {
    // console.log('assigned', assigned)
    // console.log('comments', comments)
    let img = '';

    if (assigned) {
      img = `
        <div class='avatar-group d-flex align-items-center assigned-avatar'>
          <img src="${assigned}" class="rounded-circle" alt="avatar" height="26" width="26" />
        </div>
      `;
    }

    return `
      <div class='d-flex justify-content-between align-items-center flex-wrap mt-2'>
        <div class='d-flex'>
          <span class='d-flex align-items-center me-2'>
            <i class='ti ti-paperclip me-1'></i>
            <span class='attachments'>${attachments ?? 1}</span>
          </span>
          <span class='d-flex align-items-center ms-2'>
            <i class='ti ti-message-2 me-1'></i>
            <span>${comments}</span>
          </span>
        </div>
        ${img}
      </div>
    `;
  }

  // Init kanban
  const kanban = new jKanban({
    element: '.kanban-wrapper',
    gutter: '12px',
    widthBoard: '250px',
    dragItems: true,
    boards: modefiedTasks,
    dragBoards: true,
    click: function (el) {
      let element = el;
      const taskId = element.getAttribute('data-eid');

      let title = element.getAttribute('data-eid')
          ? element.querySelector('.kanban-text').textContent
          : element.textContent,
        date = element.getAttribute('data-due-date'),
        dateObj = new Date(),
        year = dateObj.getFullYear(),
        dateToUse = date
          ? date + ', ' + year
          : dateObj.getDate() + ' ' + dateObj.toLocaleString('en', { month: 'long' }) + ', ' + year,
        label = element.getAttribute('data-badgeText'),
        avatars = element.getAttribute('data-assigned');

      // Show kanban offcanvas
      kanbanOffcanvas.show();

      // To get data on sidebar
      kanbanSidebar.querySelector('#title').value = title;
      kanbanSidebar.querySelector('#due-date').nextSibling.value = dateToUse;

      // ! Using jQuery method to get sidebar due to select2 dependency
      $('.kanban-update-item-sidebar').find(select2).val(label).trigger('change');

      // Remove & Update assigned
      kanbanSidebar.querySelector('.assigned').innerHTML = '';
      kanbanSidebar
        .querySelector('.assigned')
        .insertAdjacentHTML(
          'afterbegin',
          renderAvatar(avatars, false, 'xs', '1', el.getAttribute('data-members')) +
            "<div class='avatar avatar-xs ms-1'>" +
            "<span class='avatar-initial rounded-circle bg-label-secondary'><i class='ti ti-plus ti-xs text-heading'></i></span>" +
            '</div>'
        );
    },
  });

  // Kanban Wrapper scrollbar
  if (kanbanWrapper) {
    new PerfectScrollbar(kanbanWrapper);
  }

  const kanbanContainer = document.querySelector('.kanban-container'),
    kanbanTitleBoard = [].slice.call(document.querySelectorAll('.kanban-title-board')),
    kanbanItem = [].slice.call(document.querySelectorAll('.kanban-item'));

  // Render custom items
  if (kanbanItem) {
    kanbanItem.forEach(function (el) {
      const element = "<span class='kanban-text'>" + el.textContent + '</span>';
      let img = '';
      if (el.getAttribute('data-image') !== null) {
        img =
          "<img class='img-fluid rounded mb-2' src='" +
          assetsPath +
          'img/elements/' +
          el.getAttribute('data-image') +
          "'>";
      }
      el.textContent = '';
      if (el.getAttribute('data-badge') !== undefined && el.getAttribute('data-badgeText') !== undefined) {
        el.insertAdjacentHTML(
          'afterbegin',
          renderHeader(el.getAttribute('data-badge'), el.getAttribute('data-badgeText')) + img + element
        );
      }
      if (
        el.getAttribute('data-comments') !== undefined ||
        el.getAttribute('data-due-date') !== undefined ||
        el.getAttribute('data-assigned') !== undefined
      ) {
        el.insertAdjacentHTML(
          'beforeend',
          renderFooter(
            el.getAttribute('data-attachments'),
            el.getAttribute('data-comments'),
            el.getAttribute('data-assigned'),
            el.getAttribute('data-members')
          )
        );
      }
    });
  }

  // To initialize tooltips for rendered items
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // prevent sidebar to open onclick dropdown buttons of tasks
  const tasksItemDropdown = [].slice.call(document.querySelectorAll('.kanban-tasks-item-dropdown'));
  if (tasksItemDropdown) {
    tasksItemDropdown.forEach(function (e) {
      e.addEventListener('click', function (el) {
        el.stopPropagation();
      });
    });
  }

  // Makes kanban title editable for rendered boards
  if (kanbanTitleBoard) {
    kanbanTitleBoard.forEach(function (elem) {
      elem.addEventListener('mouseenter', function () {
        this.contentEditable = 'true';
      });

      // Appends delete icon with title
      elem.insertAdjacentHTML('afterend', renderBoardDropdown());
    });
  }

  // To delete Board for rendered boards
  const deleteBoards = [].slice.call(document.querySelectorAll('.delete-board'));
  if (deleteBoards) {
    deleteBoards.forEach(function (elem) {
      elem.addEventListener('click', function () {
        const id = this.closest('.kanban-board').getAttribute('data-id');
        kanban.removeBoard(id);
      });
    });
  }

  // Delete task for rendered boards
  const deleteTask = [].slice.call(document.querySelectorAll('.delete-task'));
  if (deleteTask) {
    deleteTask.forEach(function (e) {
      e.addEventListener('click', function () {
        const id = this.closest('.kanban-item').getAttribute('data-eid');
        kanban.removeElement(id);
      });
    });
  }

  // Cancel btn add new input
  const cancelAddNew = document.querySelector('.kanban-add-board-cancel-btn');
  if (cancelAddNew) {
    cancelAddNew.addEventListener('click', function () {
      kanbanAddNewInput.forEach(el => {
        el.classList.toggle('d-none');
      });
    });
  }

  // Clear comment editor on close
  kanbanSidebar.addEventListener('hidden.bs.offcanvas', function () {
    kanbanSidebar.querySelector('.ql-editor').firstElementChild.innerHTML = '';
    // Remove taskId from URL search params
    const searchParams = new URLSearchParams(window.location.search);
    searchParams.delete('taskId');
    const newUrl = searchParams.toString()
      ? `${window.location.pathname}?${searchParams.toString()}`
      : window.location.pathname;
    window.history.pushState({}, '', newUrl);
  });

  // Re-init tooltip when offcanvas opens(Bootstrap bug)
  if (kanbanSidebar) {
    kanbanSidebar.addEventListener('shown.bs.offcanvas', function () {
      const tooltipTriggerList = [].slice.call(kanbanSidebar.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  }
})();
