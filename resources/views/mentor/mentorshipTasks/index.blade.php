@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/jkanban/jkanban.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/tagify/tagify.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
@stop

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-kanban.css" />
  <style>
    #card-header {
      display: flex;
      gap: 10px;
      width: 100%;
    }

    .text-xs {
      font-size: 12px;
    }

    @media screen and (min-width: 768px) {
      #card-header {
        width: 50%;
      }
    }

    .kanban-container,
    .kanban-wrapper {
      display: flex !important;
      width: 100% !important;
      flex-grow: 1 !important;
      padding-inline-start: 0.7rem;
    }

    .kanban-item,
    .kanban-board {
      padding: 0 !important;
      width: 300px !important;
    }

    .kanban-board-header {
      padding: 0 !important;
      background-color: var(--bs-light) !important;
      margin-bottom: 1rem !important;
      width: 100% !important;
      border-radius: 0.25rem !important;
    }

    .kanban-title-board {
      font-size: 1rem !important;
      font-weight: 600 !important;
      padding: 0.3rem 1rem !important;
      letter-spacing: 0.5px !important;
    }

    p,
    span {
      font-size: 12px;
    }
  </style>
@stop

@section('content')
  <!-- Content wrapper -->
  <div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-end w-100 border-bottom pb-3">
            <div id="card-header">
              <div class="ms-md-8 d-flex flex-grow-1 position-relative">
                <input id="search-task" type="text" class="form-control px-8" placeholder="{{ __('admin.Search') }}" />
                <i class="ti ti-search position-absolute top-50 translate-middle-y mx-2"></i>
              </div>
              <button id="addTaskButton" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <span class="text-nowrap me-2">{{ __('admin.Add_Task') }}</span>
                <i class="ti ti-plus ti-16px"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="app-kanban">
          <div class="kanban-wrapper"></div>
        </div>
      </div>
    </div>
    <!-- / Content -->

    <!-- View Task Modal -->
    <div class="modal fade" id="viewTaskModal" tabindex="-1" aria-labelledby="viewTaskModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content rounded overflow-hidden bg-label-light p-md-4">
          <div class="mb-4 p-4 p-md-0">
            <div class="d-flex justify-content-between align-items-center w-100">
              <div class="d-flex align-items-center gap-3">
                <div class="cursor-pointer">
                  <i class="ti ti-edit text-primary ti-20px"></i>
                </div>
                <div class="cursor-pointer" id="warningDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="ti ti-trash text-danger ti-20px"></i>
                </div>
                <div class="dropdown-menu border" aria-labelledby="warningDropdown" style="background-color: #ebebed; width: 300px;">
                  <div class="d-flex justify-content-between align-items-center p-2">
                    <div class="d-flex align-items-center gap-2">
                      <i class="ti ti-alert-square-rounded ti-20px"></i>
                      <h6 class="fw-medium mb-0">Warning</h6>
                    </div>
                    <div id="closeViewTask" class="cursor-pointer" data-bs-toggle="dropdown" aria-label="Close">
                      <i class="ti ti-x ti-20px text-primary"></i>
                    </div>
                  </div>
                  <div class="bg-white rounded border border-secondary mb-2">
                    <p class="text-muted p-2" style="font-size: 13px;">Are you sure you want to delete this task?</p>
                  </div>
                  <button class="btn btn-danger" style="width: 100%;">
                    <i class="ti ti-trash ti-20px me-2"></i>
                    <span>Delete</span>
                  </button>
                </div>
              </div>
              <h6 class="modal-title" id="viewTaskModalLabel">View Task</h6>
              <div id="closeViewTask" class="cursor-pointer border rounded" data-bs-dismiss="modal" aria-label="Close">
                <i class="ti ti-x ti-20px text-primary"></i>
              </div>
            </div>
          </div>
          <div class="bg-white rounded border border-light p-2 p-md-4">
            <h4 class="fw-medium">Task Name</h4>
            <div class="bg-label-secondary p-2 rounded mb-3">
              <h6 class="fw-semibold mb-1">Description</h6>
              <p class="text-secondary">Lorem ipsum dolor sit, amet consectetur adipisicing elit. Ab quod dolores debitis
                autem officiis odio. Alias eaque itaque, quidem nostrum dolorum illo rerum ea ad? Facere dignissimos id
                recusandae deserunt.</p>
            </div>
            <div
              class="mb-3 w-100 d-flex flex-wrap flex-md-nowrap justify-content-between gap-2 align-items-center pb-2 border-bottom">
              <div class="d-flex justify-content-center align-items-center gap-2">
                <div class="d-flex justify-content-center align-items-center gap-1">
                  <i class="ti ti-flag text-primary ti-24px"></i>
                  <span class="text-dark">Priority:</span>
                </div>
                <div class="badge bg-label-warning d-flex align-items-center justify-content-center">
                  <div style="width: 9px; height: 9px;" class="bg-warning rounded-circle me-1"></div>
                  <span class="fw-semibold">Medium</span>
                </div>
              </div>
              <div class="d-flex justify-content-center align-items-center gap-2">
                <div class="p-2 border rounded d-flex flex-column">
                  <span class="text-dark fw-bold">Created</span>
                  <span class="text-muted"><i class="ti ti-clock me-1 ti-16px"></i>24 Feb 04:20 PM</span>
                </div>
                <div class="p-2 border rounded d-flex flex-column">
                  <span class="text-dark fw-bold">Updated</span>
                  <span class="text-muted"><i class="ti ti-clock me-1 ti-16px"></i>24 Feb 04:20 PM</span>
                </div>
              </div>
            </div>
            <div class="bg-label-secondary p-2 rounded mb-3 border">
              <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-white">
                <div class="d-flex align-items-center justify-content-center gap-2">
                  <h6 class="fw-semibold mb-0"><i class="ti ti-paperclip text-primary ti-24px me-2"></i>Attachments</h6>
                  <h6 class="mb-0"><span class="badge bg-white text-dark" id="attachmentCount">0</span></h6>
                </div>
                <input type="file" id="fileInput" style="display: none;" multiple onchange="handleFileUpload(event)" />
                <button type="button" class="btn btn-sm btn-outline-info" style="width: 24px;"
                  onclick="document.getElementById('fileInput').click();">
                  <i class="ti ti-plus ti-16px"></i>
                </button>
              </div>
              <div class="d-flex flex-wrap gap-2" id="fileList"></div>
            </div>
            <div class="d-flex gap-10 mb-3 pb-3">
              <div class="d-flex flex-column gap-4">
                <h6 class="mb-0 fw-medium text-nowrap"><i class="ti ti-circle-dot text-primary ti-20px me-2"></i>Comments</h6>
                <h6 class="mb-0 fw-medium text-nowrap"><i class="ti ti-users text-primary ti-20px me-2"></i>Assigness</h6>
                <h6 class="mb-0 fw-medium text-nowrap"><i class="ti ti-calendar text-primary ti-20px me-2"></i>Due date</h6>
                <h6 class="mb-0 fw-medium text-nowrap"><i class="ti ti-tag text-primary ti-20px me-2"></i>Tags</h6>
              </div>
              <div class="d-flex flex-column gap-3 overflow-x-auto">
                <div class="badge bg-label-secondary d-flex align-items-center" style="width:fit-content;">
                  <div style="width:10px;height:10px;background:#fd7e14;" class="rounded-circle me-2"></div>
                  <span>In Progress</span>
                </div>
                <div class="d-flex align-items-center">
                  <img src="https://www.w3schools.com/howto/img_avatar.png" class="rounded-circle" alt="User 1" width="24" height="24" style="{{ $l == 'en' ? 'margin-right: -5px;' : 'margin-left: -5px;' }}">
                  <img src="https://www.w3schools.com/howto/img_avatar.png" class="rounded-circle" alt="User 2" width="24" height="24" style="{{ $l == 'en' ? 'margin-right: -5px;' : 'margin-left: -5px;' }}">
                  <img src="https://www.w3schools.com/howto/img_avatar.png" class="rounded-circle" alt="User 3" width="24" height="24" style="{{ $l == 'en' ? 'margin-right: -5px;' : 'margin-left: -5px;' }}">
                </div>
                <div class="d-flex align-items-center gap-3">
                  <div class="badge bg-label-secondary radius-10 date">
                    <i class="ti ti-calendar-time ti-18px text-info"></i>
                    <span class="fw-semibold text-xs text-dark mx-2">2024-03-01</span>
                  </div>
                  <div class="badge bg-label-secondary radius-10 date">
                    <i class="ti ti-calendar-time ti-18px text-info"></i>
                    <span class="fw-semibold text-xs text-dark mx-2">2024-03-01</span>
                  </div>
                </div>
                <div class="d-flex align-items-center justif-content-center gap-3">
                  <h6 class="mb-0"><span class="badge bg-label-info">Marketing</span></h6>
                  <h6 class="mb-0"><span class="badge bg-label-success">Grahics</span></h6>
                </div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
              <div id="commentsTab" class="pb-2 border-bottom border-primary">
                <button id="commentsButton" style="border-radius: 15px" class="btn btn-outline-primary">
                  Comments
                </button>
              </div>
              <div id="activityTab" class="pb-2 ">
                <button id="activityButton" style="border-radius: 15px" class="btn">
                  Activity
                </button>
              </div>
            </div>
            <div id="commentsContainer">
              <div id="commentList" class="">
                <!-- Rendered comments will appear here -->
              </div>
              <div class="bg-label-secondary p-2 rounded mb-3 border">
                <div class="d-flex align-items-center gap-2">
                  <input type="text" id="commentInput" class="form-control bg-white" placeholder="Add a comment" />
                  <button type="button" class="btn btn-info" onclick="addComment()">Publish</button>
                </div>
              </div>
            </div>
            <div id="activityContainer" class="d-none">
              <div id="activityList" class="bg-label-secondary p-2 rounded mb-3 border">
                <div class="border rounded p-2 mb-2 bg-white">
                  <div class="d-flex justify-content-between">
                    <div class="d-flex align-items-center gap-2 mb-2">
                      <img src="https://www.w3schools.com/howto/img_avatar.png" class="rounded-circle" alt="User" width="34" height="34">
                      <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 text-dark fw-bold">User Name</h6>
                        <span class="text-secondary text-xs">2 hours ago</span>
                      </div>
                    </div>
                  </div>
                  <p class="text-muted mb-0" style="font-size: 14px;">Activity text goes here.</p>
                </div>
                <div class="border rounded p-2 mb-2 bg-white">
                  <div class="d-flex justify-content-between">
                    <div class="d-flex align-items-center gap-2 mb-2">
                      <img src="https://www.w3schools.com/howto/img_avatar.png" class="rounded-circle" alt="User" width="34" height="34">
                      <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 text-dark fw-bold">User Name</h6>
                        <span class="text-secondary text-xs">3 hours ago</span>
                      </div>
                    </div>
                  </div>
                  <p class="text-muted mb-0" style="font-size: 14px;">Another activity text goes here.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- / View Task Modal -->

    <div class="content-backdrop fade"></div>
    <div style="display: none;" class="offcanvas-backdrop fade show"></div>
  </div>
  <!-- Content wrapper -->
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/jkanban/jkanban.js"></script>
@stop

@section('pagescripts')
  <script>
    const boards = [{
        id: 'board-1',
        title: 'Pending',
        item: [{
          id: 1,
          name: "first task pending",
          title: 'Pending Task 1',
          content: 'Task 1 content',
          due_date: '2021-09-01',
          label: 'Label 1'
        }, ]
      },
      {
        id: 'board-2',
        title: 'In Review',
        item: [{
          id: 1,
          name: 'InReview Task 1',
          content: 'Task 1 content',
          due_date: '2021-09-01',
          label: 'Label 1'
        }, ]
      },
      {
        id: 'board-3',
        title: 'Completed',
        item: [{
          id: 1,
          name: 'Completed Task 1',
          content: 'Task 1 content',
          due_date: '2021-09-01',
          label: 'Label 1'
        }, ]
      }
    ]

    // Render Kanban board
    const renderKanabanBoard = () => {
      const kanban = new jKanban({
        element: '.kanban-wrapper',
        gutter: '15px',
        widthBoard: '250px',
        boards,
        addItemButton: false,
        dragBoards: true,
        dragItems: true,
        click: (el) => {
          $('#viewTaskModal').modal('show');
        },
        dragEl: (el, source) => {
          console.log('Task is being dragged:', el);
        },
        dragendEl: el => {
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
    };

    // Render kanban item
    const renderKanbanItem = () => {
      const tasks = document.querySelectorAll('.kanban-item');
      console.log('tasks', tasks)
      tasks.forEach(item => {
        let taskContent = item.getAttribute('data-content');
        let taskName = item.getAttribute('data-name');
        // console.log('taskName', taskName);
        console.log('item', item);
        let content = `
          <div class="card shadow-sm p-3 d-flex flex-column">
            <div class="d-flex align-items-center gap-1 mb-2">
              <div class="badge bg-label-info">
                <div class="fw-bold" style="font-size: 11px;">Marketing</div>
              </div>
              <div class="badge bg-label-danger d-flex align-items-center justify-content-center">
                <div style="width: 7px; height: 7px;" class="bg-danger rounded-circle me-1"></div>
                <div class="fw-bold" style="font-size: 11px;">Medium</div>
              </div>
            </div>
            <div class="d-flex flex-column gap-2 border-bottom mb-2">
              <h6 class="mb-0 fw-bold">${taskName}</h6>
              <p class="text-muted mb-1 text-xs">${taskContent}</p>
            </div>
            <div class="d-flex justify-content-between">
              <div class="d-flex justify-content-center align-items-center">
                <img src="https://www.w3schools.com/howto/img_avatar.png" class="rounded-circle" alt="User 1" width="24" height="24" style="{{ $l == 'en' ? 'margin-right: -5px;' : 'margin-left: -5px;' }}">
                <img src="https://www.w3schools.com/howto/img_avatar.png" class="rounded-circle" alt="User 2" width="24" height="24" style="{{ $l == 'en' ? 'margin-right: -5px;' : 'margin-left: -5px;' }}">
                <img src="https://www.w3schools.com/howto/img_avatar.png" class="rounded-circle" alt="User 3" width="24" height="24" style="{{ $l == 'en' ? 'margin-right: -5px;' : 'margin-left: -5px;' }}">
              </div>
              <div class="d-flex justify-content-center align-items-center gap-2">
                <span class="text-xs"><i class='ti ti-paperclip ti-16px me-1'></i>4</span>
                <span class="text-xs"><i class="ti ti-clock ti-16px me-1"></i>24 Feb</span>
                <span class="text-xs"><i class="ti ti-message-2 ti-16px me-1"></i>12</span>
              </div>
            </div>
          </div>
        `;

        item.innerHTML = content;
      })
    };

    // Filter tasks on search
    const searchTask = () => {
      const searchInput = document.getElementById('search-task');
      console.log('searchInput', searchInput);
      searchInput.addEventListener('input', (e) => {
        const searchValue = e.target.value;
        console.log('searchValue', searchValue);
        const tasks = document.querySelectorAll('.kanban-item');
        tasks.forEach(task => {
          const title = task.getAttribute('data-name');
          console.log('title', title)
          if (title.toLowerCase().includes(searchValue.toLowerCase())) {
            task.style.display = 'block';
          } else {
            task.style.display = 'none';
          }
        });
      });
    }

    renderKanabanBoard();
    renderKanbanItem();
    searchTask();
  </script>
  <script>
    function handleFileUpload(event) {
      const files = event.target.files;
      console.log('files length', files?.length);
      const fileList = document.getElementById('fileList');
      const attachmentCount = document.getElementById('attachmentCount');
      fileList.innerHTML = '';
      Array.from(files).forEach(file => {
      const fileDiv = document.createElement('div');
      fileDiv.className = 'border rounded p-2 d-flex align-items-center bg-white';
      fileDiv.innerHTML = `
        <i class="ti ti-file text-info ti-20px me-2"></i>
        <span class="text-dark fw-bold">${file.name}</span>
        <div class="cursor-pointer" 
          onclick="this.parentElement.remove(); attachmentCount.textContent = fileList.children.length;"
        >
          <i class="ti ti-trash text-danger ti-16px ms-2"></i>
        </div>
      `;
      document.getElementById('fileInput').value = '';
      fileList.appendChild(fileDiv);
      });
      attachmentCount.textContent = fileList.children.length;
    }
  </script>
  <script>
    function addComment() {
      const commentInput = document.getElementById('commentInput');
      const commentList = document.getElementById('commentList');
      const commentText = commentInput.value.trim();

      if (commentText) {
        commentList.classList.add('bg-label-secondary', 'p-2', 'rounded', 'mb-3', 'border');
        const commentDiv = document.createElement('div');
        commentDiv.className = 'border rounded p-2 mb-2 bg-white';
        commentDiv.innerHTML = `
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center gap-2 mb-2">
              <img src="https://www.w3schools.com/howto/img_avatar.png" class="rounded-circle" alt="User" width="34" height="34">
              <div class="d-flex align-items-center gap-2">
                <h6 class="mb-0 text-dark fw-bold">User Name</h6>
                <span class="text-secodnary text-xs">2 hours ago</span>
              </div>
            </div>
            <div class="cursor-pointer" onclick="this.parentElement.parentElement.remove();commentList.classList.remove('bg-label-secondary', 'p-2', 'rounded', 'mb-3', 'border');">
              <i class="ti ti-trash text-danger ti-16px"></i>
            </div>
          </div>
          <p class="text-muted mb-0" style="font-size: 14px;">${commentText}</p>
        `;
        commentList.appendChild(commentDiv);
        commentInput.value = '';
      }
    }
  </script>
  <script>
    const commentsTab = document.getElementById('commentsTab');
    const activityTab = document.getElementById('activityTab');

    const commentsContainer = document.getElementById('commentsContainer');
    const activityContainer = document.getElementById('activityContainer');

    const commentsButton = document.getElementById('commentsButton');
    const activityButton = document.getElementById('activityButton');

    commentsButton.addEventListener('click', () => {
      activityButton.classList.remove('btn-outline-primary');
      commentsButton.classList.add('btn-outline-primary');
      commentsTab.classList.add('border-bottom', 'border-primary');
      activityTab.classList.remove('border-bottom', 'border-primary');
      commentsContainer.classList.remove('d-none');
      activityContainer.classList.add('d-none');
    });

    activityButton.addEventListener('click', () => {
      activityButton.classList.add('btn-outline-primary');
      commentsButton.classList.remove('btn-outline-primary');
      activityTab.classList.add('border-bottom', 'border-primary');
      commentsTab.classList.remove('border-bottom', 'border-primary');
      activityContainer.classList.remove('d-none');
      commentsContainer.classList.add('d-none');
    });
  </script>
@stop
