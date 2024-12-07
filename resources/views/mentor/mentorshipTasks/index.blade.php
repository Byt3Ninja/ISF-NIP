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
  .kanban-container, .kanban-wrapper {
    display: flex !important;
    width: 100% !important;
    flex-grow: 1 !important;
    padding-inline-start: 0.7rem;
  }

  .kanban-item, .kanban-board {
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
      {{-- <div class="px-8 row">
        <div class="col-4">
          
        </div>
      </div> --}}
    </div>
  </div>
  <!-- / Content -->

  <!-- Add Task Modal -->
  <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addTaskModalLabel">{{ __('admin.Add_Task') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form class="form-horizontal form-bordered" id="basicForm" action="{{ route('mentorshipTasks.store') }}"
            method="POST" enctype="multipart/form-data">
            {!! csrf_field() !!}
            <input type="hidden" name="mentorship_request_id" value="{{ $mentorship->id }}">
            <div class="mb-3">
              <label for="taskStatus" class="form-label">{{ __('admin.Status') }}</label>
              <select class="form-select" id="taskStatus" name="status" required>
                <option value="pending">{{ __('admin.Pending') }}</option>
                <option value="in-review">{{ __('admin.In_Review') }}</option>
                <option value="completed">{{ __('admin.Completed') }}</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="taskTitle" class="form-label">{{ __('admin.Title') }}</label>
              <input type="text" class="form-control" placeholder="{{__('mentor.mentorshipTasks.title')}}" name="title"
                value="{{ old('title') }}" id="title" required>
            </div>
            <div class="mb-3">
              <label for="taskContent" class="form-label">{{ __('admin.Description') }}</label>
              <textarea class="form-control comment-editor" id="taskContent" name="description"
                required>{{ old('description') }}</textarea>
            </div>
            <div class="mb-5">
              <label class="form-label" for="due-date">{{ __('admin.Due_Date') }}</label>
              <input type="text" placeholder="YYYY-MM-DD" id="flatpickr-date" name="due_date" class="form-control" />
            </div>
            <div class="mb-3">
              <div>
                <label class="form-label">{{ __('admin.Labels') }}</label>
                <input
                  id="addTaskLabel"
                  name="label"
                  class="form-control"
                  placeholder="{{ $l == 'en' ? 'Selecte labels' : 'اختر العلامات' }}"
                />
              </div>
            </div>
            <!-- Attached Tasks -->
            <div class="mb-8 w-100">
              <label for="editTaskAttachments" class="form-label">{{ __('admin.TaskAttachments') }}</label>
              <div class="w-100 d-flex flex-column gap-3">
                <div class="w-50">
                  <input type="text" class="form-control d-none" id="file3" name="attachments" />
                  <div class="btn btn-label-secondary lfm w-100" id="editTaskAttachments" data-input="file3"
                    data-preview="holder3">
                    <i class="ti ti-upload me-2"></i>
                    {{ __('admin.Choose') }}
                  </div>
                </div>
                <div class="ms-3" id="holder3">
                  <img
                    src="/images/background/logo.png"
                    class="rounded border shadow-sm"
                    style="max-height: 50px; max-width: 50px; object-fit: cover;"
                    height="50"
                    width="50"
                   />
                </div>
              </div>
            </div>
            <!-- / Attached Tasks -->
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-plus ti-16px me-2"></i>
              {{ __('admin.Add_Task') }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <!-- / Add Task Modal -->

  <!-- Edit Task offcanvas -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasStart" aria-labelledby="offcanvasStartLabel">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title">{{ __('admin.Edit_Task') }}</h5>
      <button type="button" class="btn-close btn-close-offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body pt-0">
      <div class="nav-align-top">
        <ul class="nav nav-tabs mb-5 rounded-0">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-update">
              <i class="ti ti-edit ti-18px me-1_5"></i>
              <span class="align-middle">{{ __('admin.Edit') }}</span>
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-activity">
              <i class="ti ti-chart-pie-2 ti-18px me-1_5"></i>
              <span class="align-middle">{{ __('admin.comments') }}</span>
            </button>
          </li>
        </ul>
      </div>
      <div class="tab-content p-0">
        <!-- Update item/tasks -->
        <div class="tab-pane fade show active" id="tab-update" role="tabpanel">
          <form class="form-horizontal form-bordered" id="editTaskForm"
            action="{{ route('mentorshipTasks.updateWith') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="mentorship_request_id" value="{{ $mentorship->id }}">
            <input type="hidden" name="MTaskId" id="MTaskId" value="">
             <!-- Attached Tasks -->
             <div class="mb-8 w-100">
              <label for="file2" class="form-label">{{ __('admin.TaskAttachments') }}</label>
              <div class="w-100 d-flex flex-column gap-3">
                <div class="w-50">
                  <input type="text" class="form-control d-none" id="file2" name="attachments" />
                  <div class="btn btn-label-secondary lfm w-100" id="editTaskAttachments" data-input="file2"
                    data-preview="holder1">
                    <i class="ti ti-upload me-2"></i>
                    {{ __('admin.Choose') }}
                  </div>
                </div>
                <div class="ms-3" id="holder1">
                  <img
                    src="/images/background/logo.png"
                    class="rounded border shadow-sm"
                    style="max-height: 50px; max-width: 50px; object-fit: cover;"
                    height="50"
                    width="50"
                   />
                </div>
              </div>
            </div>
            <!-- / Attached Tasks -->
            <div class="mb-3">
              <label for="editTaskTitle" class="form-label">{{ __('admin.Title') }}</label>
              <input type="text" class="form-control" placeholder="{{__('mentor.mentorshipTasks.title')}}" name="title"
                value="" id="editTaskTitle" required>
            </div>
            <div class="mb-3">
              <label for="editTaskContent" class="form-label">{{ __('admin.Description') }}</label>
              <textarea class="form-control" id="descriptionEdit" name="description" required></textarea>
            </div>
            <div class="mb-3">
              <label for="editTaskStatus" class="form-label">{{ __('admin.Status') }}</label>
              <select class="form-select" id="editTaskStatus" name="status" required>
                <option value="" disabled>{{ __('admin.Choose') }}</option>
                <option value="pending">{{ __('admin.Pending') }}</option>
                <option value="in-review">{{ __('admin.In_Review') }}</option>
                <option value="completed">{{ __('admin.Completed') }}</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label" for="due_date">{{ __('admin.Due_Date') }}</label>
              <input type="text" name="due_date" class="form-control due_dateEdit" placeholder="YYYY-MM-DD"
                id="flatpickr-date" />
            </div>
            
            <div class="mb-3">
              <div>
                <label class="form-label">{{ __('admin.Labels') }}</label>
                <input
                  id="editTaskLabel"
                  name="label"
                  class="form-control tagifyInput"
                  placeholder="{{ $l == 'en' ? 'Selecte labels' : 'اختر العلامات' }}"
                  
                />
              </div>
            </div>
            {{-- <div class="mb-3">
              <label for="editTaskAssigned" class="form-label">{{ __('admin.Assigned_To') }}</label>
              <select class="form-select" id="editTaskAssigned" name="assigned_to" required>
                <option value="user1">User 1</option>
                <option value="user2">User 2</option>
                <option value="user3">User 3</option>
              </select>
            </div> --}}
            <hr>
            
            <div class="mb-3">
              <label for="editTaskAttachments" class="form-label">{{ __('admin.comment') }}</label>
              <div class="form-label-group">
                <textarea class="form-control" id="summernote" name="comment"></textarea>
              </div>
            </div>



            <!-- Attached Tasks -->
            <div class="mb-8 w-100">
              <label for="file1" class="form-label">{{ __('admin.CommentAttachments') }}</label>
              <div class="w-100 d-flex flex-column gap-3">
                <div class="w-50">
                  <input type="text" class="form-control d-none" id="file1" name="attachments" />
                  <div class="btn btn-label-secondary lfm w-100" id="editTaskAttachments" data-input="file1"
                    data-preview="holder2">
                    <i class="ti ti-upload me-2"></i>
                    {{ __('admin.Choose') }}
                  </div>
                </div>
                <div class="ms-3" id="holder2">
                  <img
                    src="/images/background/logo.png"
                    class="rounded border shadow-sm"
                    style="max-height: 50px; max-width: 50px; object-fit: cover;"
                    height="50"
                    width="50"
                    />
                </div>
              </div>
            </div>
            <!-- / Attached Tasks -->

            
            <div class="d-flex flex-wrap">
              <button type="submit" class="btn btn-primary me-4 btn-close-offcanvas">
                {{ __('admin.Update') }}
              </button>
              <button type="button" class="btn btn-label-danger btn-close-offcanvas">
                {{ __('admin.Delete') }}
              </button>
            </div>
          </form>
        </div>
        <!-- Task Comments -->
        <div class="tab-pane fade text-heading" id="tab-activity" role="tabpanel">
          <div id="activity"></div>
        </div>
        <!-- / Task Comments -->
      </div>
    </div>
  </div>
  <!-- /Edit Task offcanvas -->

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
    const boards = [
      {
        id: 'board-1',
        title: 'Pending',
        item: [
          {id: 1, name: "first task pending", title: 'Pending Task 1', content: 'Task 1 content', due_date: '2021-09-01', label: 'Label 1'},
        ]
      },
      {
        id: 'board-2',
        title: 'In Review',
        item: [
          {id: 1, name: 'InReview Task 1', content: 'Task 1 content', due_date: '2021-09-01', label: 'Label 1'},
        ]
      },
      {
        id: 'board-3',
        title: 'Completed',
        item: [
          {id: 1, name: 'Completed Task 1', content: 'Task 1 content', due_date: '2021-09-01', label: 'Label 1'},
        ]
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
        // click: el => onClickTask(el),
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
                <img src="https://www.w3schools.com/howto/img_avatar.png" class="rounded-circle" alt="User 1" width="24" height="24" style="{{$l == 'en' ? 'margin-right: -5px;' : 'margin-left: -5px;'}}">
                <img src="https://www.w3schools.com/howto/img_avatar.png" class="rounded-circle" alt="User 2" width="24" height="24" style="{{$l == 'en' ? 'margin-right: -5px;' : 'margin-left: -5px;'}}">
                <img src="https://www.w3schools.com/howto/img_avatar.png" class="rounded-circle" alt="User 3" width="24" height="24" style="{{$l == 'en' ? 'margin-right: -5px;' : 'margin-left: -5px;'}}">
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