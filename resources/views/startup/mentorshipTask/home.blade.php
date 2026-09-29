@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/jkanban/jkanban.css" />
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
  <div class="">
    <!-- Content -->

    <div class="">
      <div class="card card-body">
        <div class="app-kanban">
          <!-- Kanban Wrapper -->
          @if (!empty($mentorshipTasks) && count($mentorshipTasks) > 0)
            <div class="row mb-6 border-bottom pb-3 ms-md-8">
              <div class="col-12 col-md-6">
                <div class="d-flex flex-grow-1 position-relative">
                  <input id="search-task" type="text" class="form-control px-8"
                    placeholder="{{ __('admin.Search') }}" />
                  <i class="ti ti-search position-absolute top-50 translate-middle-y mx-2"></i>
                </div>
              </div>
            </div>
            <div class="kanban-wrapper"></div>
          @else
            <div class="d-flex flex-column justify-content-center align-items-center gap-4 mt-10">
              <img src="/icons/not-found.svg" width="337" height="234" />
              <h4 class="mb-0 text-center">
                {{ $l == 'en' ? 'You don’t have any tasks' : 'ليس لديك أي مهام' }}
              </h4>
            </div>
          @endif
          <!-- / Kanban Wrapper -->
        </div>

        <!-- Comment Modal -->
        <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="nav-align-top">
                  <ul class="nav nav-tabs mb-5 rounded-0">
                    <li class="nav-item">
                      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-update">
                        <i class="ti ti-message-2 ti-18px me-1_5"></i>
                        <span class="align-middle">Comment</span>
                      </button>
                    </li>
                    <li class="nav-item">
                      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-activity">
                        <i class="ti ti-chart-pie-2 ti-18px me-1_5"></i>
                        <span class="align-middle">Activity</span>
                      </button>
                    </li>
                  </ul>
                </div>
                <div class="tab-content p-0">
                  <!-- Add Comment -->
                  <div class="tab-pane fade show active" id="tab-update" role="tabpanel">
                    <form class="form-horizontal form-bordered" id="basicForm"
                      action= "{{ route('startup.mentorshipTasks.sendComment') }}" method="POST"
                      enctype="multipart/form-data">
                      {!! csrf_field() !!}
                      <legend>
                        <h5 class="modal-title" id="commentModalLabel">Add Comment</h5>
                      </legend>
                      <input type="hidden" name="mentorship_task_id" value="">
                      <div class="form-label-group">
                        <textarea class="form-control" id="summernote" name="comment" placeholder="Add comment" required></textarea>
                      </div>    
                      <div class="mt-8">
                        <div class="d-flex flex-wrap">
                          <button type="reset" class="btn btn-label-secondary me-4" data-bs-dismiss="modal">
                            Cancel
                          </button>
                          <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">
                            Publish
                          </button>
                        </div>
                      </div>
                    </form>
                  </div>
                  <!-- Activities -->
                  <div class="tab-pane fade text-heading" id="tab-activity" role="tabpanel">
                    <div class="media mb-4 d-flex align-items-center">
                      <div class="avatar me-3 flex-shrink-0">
                        <span class="avatar-initial bg-label-success rounded-circle">HJ</span>
                      </div>
                      <div class="media-body">
                        <p class="mb-0"><span>Jordan</span> Left the board.</p>
                        <small class="text-muted">Today 11:00 AM</small>
                      </div>
                    </div>
                    <div class="media mb-4 d-flex align-items-center">
                      <div class="avatar me-3 flex-shrink-0">
                        <img src="/vuexy/assets/img/avatars/6.png" alt="Avatar" class="rounded-circle" />
                      </div>
                      <div class="media-body">
                        <p class="mb-0">
                          <span>Dianna</span> mentioned <span class="text-primary">@bruce</span> in a comment.
                        </p>
                        <small class="text-muted">Today 10:20 AM</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- / Comment Modal -->
      </div>
    </div>
    <!-- / Content -->

    <div class="content-backdrop fade"></div>
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
        dragBoards: false,
        dragItems: false,
        click: (el) => {
          $('#commentModal').modal('show');
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
@stop
