// Language
const lang = lng;

let labelsWhiteList = labels;

(async function () {
  let mentorshipTasks;

  if (tasks) mentorshipTasks = tasks;

  const restructureTasks = () => {
    if (mentorshipTasks) {
      return mentorshipTasks.map(task => {
        // console.log('taskkkkkkkkk', task)
        // console.log('task?.comments?.length', task?.comments?.length)
        // console.log('task attached', task?.attached)
        return {
          comments: task?.comments?.length || 0,
          labels: task.label_ids,
          badgeText: "UX",
          badge: "primary",
          assigned: task?.mentorshipTask?.attached ? [task?.mentorshipTask?.attached] : [],
          ...task?.mentorshipTask,
        }
      });
    }
  };

  const restructuredTasks = restructureTasks();

  const getModefiedTasks = () => {
    const statusArr = ['pending', 'in-review', 'completed'];

    if (restructuredTasks) {
      return statusArr.map(status => ({
        id: `board-${status}`,
        title: status.charAt(0).toUpperCase() + status.slice(1).replace('-', ' '),
        item: restructuredTasks.filter(task => task.status === status),
      }));
    };
  };
  
  const modefiedTasks = getModefiedTasks();
  
  // Kanban board
  const kanban = new jKanban({
    element: '.kanban-wrapper',
    gutter: '15px',
    widthBoard: '250px',
    boards: modefiedTasks,
    addItemButton: false,
    dragBoards: true,
    dragItems: true,
    click: el => onClickTask(el),
    dragEl: (el, source) => {
      console.log('Task is being dragged:', el);
    },
    dragendEl: (el) => {
      console.log('Task drag ended:', el);
      // Perform any additional actions here
    },
    dropEl: (el, target, source, sibling) => {
      console.log('Task dropped:', el);
      console.log('New parent board:', target.parentElement.dataset.id);
      console.log('Previous parent board:', source.parentElement.dataset.id);
      // Perform any additional actions here
    }
  });
  // Function to send updated task to backend
  const updateTaskStatus = async (taskId, newStatus) => {
    try {
     
    } catch (error) {
      // console.error('Error updating task:', error);
    }
  };

  // Update task status on drop
  kanban.options.dropEl = (el, target, source, sibling) => {
    console.log('Task dropped:', el);
    console.log('New parent board:', target.parentElement.dataset.id);
    console.log('Previous parent board:', source.parentElement.dataset.id);

    const taskId = el.getAttribute('data-eid');
    const newStatus = target.parentElement.dataset.id.replace('board-', '');

    updateTaskStatus(taskId, newStatus);
  };

  const kanbanItem = [].slice.call(document.querySelectorAll('.kanban-item'));

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
        <div class='avatar-group d-flex align-items-center assigned-avatar'>
          <img src="${assigned}" class="rounded-circle" alt="avatar" height="26" width="26" />
        </div>
      </div>
    `;
  }

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
      if (el.getAttribute('data-badge') !== undefined && el.getAttribute('data-badgetext') !== undefined) {
        el.insertAdjacentHTML(
          'afterbegin',
          renderHeader(el.getAttribute('data-badge'), el.getAttribute('data-badgetext')) + img + element
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

  // console.log('kanbanItem', kanbanItem);

  let taskDetails; // Declare taskDetails variable here

  // Handle task click
  const onClickTask = el => {
    const elementId = el.getAttribute('data-eid');
    const taskId = +elementId;

    // Open offcanvas
    $('#offcanvasStart').addClass('fade show');
    $('.offcanvas-backdrop').css('display', 'block');

    // Get task details
    const task = restructuredTasks.find(task => task.id === taskId);
    taskDetails = task;

    if (task) {
      $('#editTaskTitle').val(task.title);
      $('#descriptionEdit').html(task.description);
      $('#editTaskStatus').val(task.status);
      $('#editTaskLabel').val(task.label_id);
      $('#editTaskAssigned').val(task.assigned_to);
      $('#MTaskId').val(task.id);
      $('.due_dateEdit').val(task.due_date);
    }
  };

  const closeOffcanvas = () => {
    $('#offcanvasStart').removeClass('fade show');
    $('.offcanvas-backdrop').css('display', 'none');
  };

  // Close offcanvas
  $('.offcanvas-backdrop').click(closeOffcanvas);
  $('.btn-close-offcanvas').click(closeOffcanvas);

  const addTaskLabel = document.getElementById('#addTaskLabel');
  const editTaskLabel = document.getElementById('#editTaskLabel');

  console.log('editTaskLabel', editTaskLabel)

  // const whitelist = ['A# .NET', 'A# (Axiom)', 'A-0 System', 'A+', 'A++', 'ABAP', 'ABC', 'ABC ALGOL', 'ABSET'];
  const whitelist = labelsWhiteList;

  console.log('whitelist', whitelist)

  if (addTaskLabel) {
    let TagifyCustomInlineSuggestion = new Tagify(addTaskLabel, {
      whitelist: whitelist,
      maxTags: 10,
      dropdown: {
        maxItems: 20,
        classname: 'tags-inline',
        enabled: 0,
        closeOnSelect: false
      }
    });
  };

  if (editTaskLabel) {
    let TagifyCustomInlineSuggestion = new Tagify(editTaskLabel, {
      whitelist: whitelist,
      maxTags: 10,
      dropdown: {
        maxItems: 20,
        classname: 'tags-inline',
        enabled: 0,
        closeOnSelect: false
      }
    });
  };
})();
