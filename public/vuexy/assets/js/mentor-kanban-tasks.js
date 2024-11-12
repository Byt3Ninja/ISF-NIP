// Language
const lang = lng;

  // Mentorship tasks data
const mentorshipTasks = tasks;

(async function () {
  console.log('hello');

  const pendingTasks = mentorshipTasks.filter(task => task.status === 'pending');
  const inReviewTasks = mentorshipTasks.filter(task => task.status === 'in-review');
  const completedTasks = mentorshipTasks.filter(task => task.status === 'completed');
  
  // Restructure tasks
  const restructureTasks = () => {
    if (mentorshipTasks?.length > 0) {
      return [
        {
          id: 'board-in-pending',
          title: pendingTasks?.length > 0 ? 'Pending' : '',
          item: pendingTasks,
        },
        {
          id: 'board-in-review',
          title: inReviewTasks?.length > 0 ? 'In Review' : '',
          item: inReviewTasks,
        },
        {
          id: 'board-completed',
          title: completedTasks?.length > 0 ? 'Completed' : '',
          item: completedTasks,
        }
      ];
    }
  };

  const tasks = restructureTasks();

  // Kanban board
  const kanban = new jKanban({
    element: '.kanban-wrapper',
    gutter: '15px',
    widthBoard: '250px',
    boards: tasks,
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
    return (
      "<div class='d-flex justify-content-between flex-wrap align-items-center mb-2'>" +
      "<div class='item-badges'> " +
      "<div class='badge bg-label-" +
      color +
      "'> " +
      text +
      '</div>'
    );
  }

  // Render avatar
  function renderAvatar(images, pullUp, size, margin, members) {
    var $transition = pullUp ? ' pull-up' : '',
      $size = size ? 'avatar-' + size + '' : '',
      member = members == undefined ? ' ' : members.split(',');

    return images == undefined
      ? ' '
      : images
          .split(',')
          .map(function (img, index, arr) {
            var $margin = margin && index !== arr.length - 1 ? ' me-' + margin + '' : '';

            return (
              "<div class='avatar " +
              $size +
              $margin +
              "'" +
              "data-bs-toggle='tooltip' data-bs-placement='top'" +
              "title='" +
              member[index] +
              "'" +
              '>' +
              "<img src='" +
              assetsPath +
              'img/avatars/' +
              img +
              "' alt='Avatar' class='rounded-circle " +
              $transition +
              "'>" +
              '</div>'
            );
          })
          .join(' ');
  }

  // Render footer
  function renderFooter(attachments, comments, assigned, members) {
    return (
      "<div class='d-flex justify-content-between align-items-center flex-wrap mt-2'>" +
      "<div class='d-flex'> <span class='d-flex align-items-center me-2'><i class='ti ti-paperclip me-1'></i>" +
      "<span class='attachments'>" +
      attachments +
      '</span>' +
      "</span> <span class='d-flex align-items-center ms-2'><i class='ti ti-message-2 me-1'></i>" +
      '<span> ' +
      comments +
      ' </span>' +
      '</span></div>' +
      "<div class='avatar-group d-flex align-items-center assigned-avatar'>" +
      renderAvatar(assigned, true, 'xs', null, members) +
      '</div>' +
      '</div>'
    );
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
      if (el.getAttribute('data-badge') !== undefined && el.getAttribute('data-badge-text') !== undefined) {
        el.insertAdjacentHTML(
          'afterbegin',
          renderHeader(el.getAttribute('data-badge'), el.getAttribute('data-badge-text')) + img + element
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
    const task = mentorshipTasks.find(task => task.id === taskId);
    taskDetails = task;

    console.log('taskDetails', taskDetails); // Moved inside the function

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

  const editTaskLabel = document.querySelector('#editTaskLabel');

  const taskLabels = taskDetails?.label_id;

  const whitelist = ['A# .NET', 'A# (Axiom)', 'A-0 System', 'A+', 'A++', 'ABAP', 'ABC', 'ABC ALGOL', 'ABSET'];

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
})();
