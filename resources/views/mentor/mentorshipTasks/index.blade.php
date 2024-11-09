@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/jkanban/jkanban.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
@stop

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-kanban.css" />
<style>
  .kanban-container {
    display: flex;
    width: max-content !important;
    gap: 3rem;
  }
</style>
@stop

@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
  <!-- Content -->

  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-6">
      <div class="col-12">
        <button id="addTaskButton" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
          <i class="ti ti-plus ti-16px me-2"></i>
          <span class="align-middle">{{ __('admin.Add_Task') }}</span>
        </button>
      </div>
    </div>
    <div class="app-kanban">
      <!-- Kanban Wrapper -->
      <div class="kanban-wrapper bg-white px-4 py-8" style="border-radius: 1rem;">
      </div>

      <!-- Edit Task/Task & Activities -->
      {{-- <div class="offcanvas offcanvas-end kanban-update-item-sidebar">
        <div class="offcanvas-header border-bottom">
          <h5 class="offcanvas-title">{{ __('admin.Edit_Task') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
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
                  <span class="align-middle">{{ __('admin.Activity') }}</span>
                </button>
              </li>
            </ul>
          </div>
          <div class="tab-content p-0">
            <!-- Update item/tasks -->
            <div class="tab-pane fade show active" id="tab-update" role="tabpanel">
              <form>
                <div class="mb-5">
                  <label class="form-label" for="title">{{ __('admin.Title') }}</label>
                  <input type="text" id="title" class="form-control" placeholder="Enter Title" />
                </div>
                <div class="mb-5">
                  <label class="form-label" for="due-date">{{ __('admin.Due_Date') }}</label>
                  <input type="text" id="due-date" class="form-control" placeholder="Enter Due Date" />
                </div>
                <div class="mb-5">
                  <label class="form-label" for="label">{{ __('admin.Label') }} </label>
                  <select class="select2 select2-label form-select" id="label">
                    <option data-color="bg-label-success" value="UX">UX</option>
                    <option data-color="bg-label-warning" value="Images">Images</option>
                    <option data-color="bg-label-info" value="Info">Info</option>
                    <option data-color="bg-label-danger" value="Code Review">Code Review</option>
                    <option data-color="bg-label-secondary" value="App">App</option>
                    <option data-color="bg-label-primary" value="Charts & Maps">Charts & Maps</option>
                  </select>
                </div>
                <div class="mb-5">
                  <label class="form-label">{{ __('admin.Assigned') }}</label>
                  <div class="assigned d-flex flex-wrap"></div>
                </div>
                <div class="mb-5">
                  <label class="form-label" for="attachments">{{ __('admin.Attachments') }}</label>
                  <div>
                    <input type="file" class="form-control" id="attachments" />
                  </div>
                </div>
                <div class="mb-5">
                  <label class="form-label">{{ __('admin.Comment') }}</label>
                  <div class="comment-editor border-bottom-0"></div>
                  <div class="d-flex justify-content-end">
                    <div class="comment-toolbar">
                      <span class="ql-formats me-0">
                        <button class="ql-bold"></button>
                        <button class="ql-italic"></button>
                        <button class="ql-underline"></button>
                        <button class="ql-link"></button>
                        <button class="ql-image"></button>
                      </span>
                    </div>
                  </div>
                </div>
                <div>
                  <div class="d-flex flex-wrap">
                    <button type="button" class="btn btn-primary me-4" data-bs-dismiss="offcanvas">
                      {{ __('admin.Update') }}
                    </button>
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="offcanvas">
                      {{ __('admin.Delete') }}
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
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <img src="/vuexy/assets/img/avatars/2.png" alt="Avatar" class="rounded-circle" />
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Martian</span> added moved Charts & Maps task to the done board.</p>
                  <small class="text-muted">Today 10:00 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <img src="/vuexy/assets/img/avatars/1.png" alt="Avatar" class="rounded-circle" />
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Barry</span> Commented on App review task.</p>
                  <small class="text-muted">Today 8:32 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <span class="avatar-initial bg-label-dark rounded-circle">BW</span>
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Bruce</span> was assigned task of code review.</p>
                  <small class="text-muted">Today 8:30 PM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <span class="avatar-initial bg-label-danger rounded-circle">CK</span>
                </div>
                <div class="media-body">
                  <p class="mb-0">
                    <span>Clark</span> assigned task UX Research to
                    <span class="text-primary">@martian</span>
                  </p>
                  <small class="text-muted">Today 8:00 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <img src="/vuexy/assets/img/avatars/4.png" alt="Avatar" class="rounded-circle" />
                </div>
                <div class="media-body">
                  <p class="mb-0">
                    <span>Ray</span> Added moved <span>Forms & Tables</span> task from in progress to done.
                  </p>
                  <small class="text-muted">Today 7:45 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <img src="/vuexy/assets/img/avatars/1.png" alt="Avatar" class="rounded-circle" />
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Barry</span> Complete all the tasks assigned to him.</p>
                  <small class="text-muted">Today 7:17 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <span class="avatar-initial bg-label-success rounded-circle">HJ</span>
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Jordan</span> added task to update new images.</p>
                  <small class="text-muted">Today 7:00 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <img src="/vuexy/assets/img/avatars/6.png" alt="Avatar" class="rounded-circle" />
                </div>
                <div class="media-body">
                  <p class="mb-0">
                    <span>Dianna</span> moved task <span>FAQ UX</span> from in progress to done board.
                  </p>
                  <small class="text-muted">Today 7:00 AM</small>
                </div>
              </div>
              <div class="media mb-4 d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <span class="avatar-initial bg-label-danger rounded-circle">CK</span>
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Clark</span> added new board with name <span>Done</span>.</p>
                  <small class="text-muted">Yesterday 3:00 PM</small>
                </div>
              </div>
              <div class="media d-flex align-items-center">
                <div class="avatar me-3 flex-shrink-0">
                  <span class="avatar-initial bg-label-dark rounded-circle">BW</span>
                </div>
                <div class="media-body">
                  <p class="mb-0"><span>Bruce</span> added new task in progress board.</p>
                  <small class="text-muted">Yesterday 12:00 PM</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> --}}
    </div>
  </div>
  <!-- / Content -->

  <div class="content-backdrop fade"></div>
</div>
<!-- Content wrapper -->

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
            <div style="height: 150px;" class="comment-editor border-bottom-0 form-control border mb-2 overflow-auto"
              id="taskContent">
            </div>
            <div class="d-flex justify-content-end border-top">
              <div class="comment-toolbar">
                <span class="ql-formats me-0">
                  <button class="ql-bold"></button>
                  <button class="ql-italic"></button>
                  <button class="ql-underline"></button>
                  <button class="ql-link"></button>
                  <button class="ql-image"></button>
                </span>
              </div>
            </div>
          </div>
          <div class="mb-5">
            <label class="form-label" for="due-date">{{ __('admin.Due_Date') }}</label>
            <input type="text" placeholder="YYYY-MM-DD" id="flatpickr-date" name="due_date" class="form-control" />
          </div>

          <button type="submit" class="btn btn-primary">{{ __('admin.Add_Task') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- / Add Task Modal -->

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editTaskModalLabel">{{ __('admin.Edit_Task') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <form id="editTaskForm">

          <div class="mb-3">
            <label for="editTaskTitle" class="form-label">{{ __('admin.Title') }}</label>
            <input type="text" class="form-control" placeholder="{{__('mentor.mentorshipTasks.title')}}" name="title"
              value="{{ $mentorship->title }}" id="editTaskTitle" required>
          </div>
          <div class="mb-3">
            <label for="editTaskContent" class="form-label">{{ __('admin.Description') }}</label>
            <div style="height: 150px;" class="comment-editor border-bottom-0 form-control border mb-2 overflow-auto"
              id="editTaskContent">
            </div>
            <div class="d-flex justify-content-end border-top">
              <div class="comment-toolbar">
                <span class="ql-formats me-0">
                  <button class="ql-bold"></button>
                  <button class="ql-italic"></button>
                  <button class="ql-underline"></button>
                  <button class="ql-link"></button>
                  <button class="ql-image"></button>
                </span>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="editTaskStatus" class="form-label">{{ __('admin.Status') }}</label>
            <select class="form-select" id="taskStatus" name="status" required>
              <option value="pending">{{ __('admin.Pending') }}</option>
              <option value="in-review">{{ __('admin.In_Review') }}</option>
              <option value="completed">{{ __('admin.Completed') }}</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="due-date">{{ __('admin.Due_Date') }}</label>
            <input type="text" name="due_date" class="form-control " placeholder="YYYY-MM-DD" id="flatpickr-date" />
          </div>
          <div class="mb-3">
            <label for="editTaskLabel" class="form-label">{{ __('admin.Label') }}</label>
            <select class="form-select" id="editTaskLabel" name="label" required>
              <option value="UX">UX</option>
              <option value="Images">Images</option>
              <option value="Info">Info</option>
              <option value="Code Review">Code Review</option>
              <option value="App">App</option>
              <option value="Charts & Maps">Charts & Maps</option>
            </select>
          </div>

          {{-- <div class="mb-3">
            <label for="editTaskAssigned" class="form-label">{{ __('admin.Assigned_To') }}</label>
            <select class="form-select" id="editTaskAssigned" name="assigned_to" required>
              <option value="user1">User 1</option>
              <option value="user2">User 2</option>
              <option value="user3">User 3</option>
            </select>
          </div> --}}

          <div class="mb-3">
            <label for="editTaskAttachments" class="form-label">{{ __('admin.Attachments') }}</label>
            <input type="file" class="form-control" id="editTaskAttachments" name="attachments[]" multiple>
          </div>
          <button type="submit" class="btn btn-primary">{{ __('admin.Update') }}</button>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- Edit Task Modal -->
@stop

@section('scripts')
<script>
  // Remove taskId from URL
    document.addEventListener('DOMContentLoaded', () => {
      const searchParams = new URLSearchParams(window.location.search);
      if (searchParams.has('taskId')) {
        searchParams.delete('taskId');
        const newUrl = searchParams.toString() ? `${window.location.pathname}?${searchParams.toString()}` : window.location.pathname;
        window.history.pushState({}, '', newUrl);
      }
    });
</script>
<script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
<script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
<script src="/vuexy/assets/vendor/libs/jkanban/jkanban.js"></script>
<script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
<script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
@stop

@section('pagescripts')
<script src="/vuexy/assets/js/forms-pickers.js"></script>
<script>
  // Language
  const lang = '{{ $l }}';
  
  // Mentorship tasks data
  const mentorshipTasks = @json($mentorshipTasks);
  // console.log('mentorshipTasks',mentorshipTasks)
        
  // Restructure tasks
  const restructureTasks = () => {
    if (mentorshipTasks?.length > 0) {
      return [
        {
          id: "board-in-pending",
          title: "Pending",
          item: mentorshipTasks.filter(task => task.status === 'pending'),
        },
        {
          id: "board-in-review",
          title: "In Review",
          item: mentorshipTasks.filter(task => task.status === 'in-review'),
        },
        {
          id: "board-completed",
          title: "Completed",
          item: mentorshipTasks.filter(task => task.status === 'completed'),
        }
      ]
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
    click: (el) => onClickTask(el),
  });

  let taskDetails;

  const onClickTask = (el) => {
    const elementId = el.getAttribute('data-eid');
    const taskId = +elementId;

    // Open edit task modal
    $('#editTaskModal').modal('show');

    // Get task details
    const task = mentorshipTasks.find(task => task.id === taskId);
    taskDetails = task;

    if (task) {
      // $('#editTaskForm').attr('action', `/mentorshipTasks/${task.id}`);
      $('#editTaskTitle').val(task.title);
      $('.comment-editor .ql-editor').html(task.description);
      $('#editTaskStatus').val(task.status);
      $('#editTaskLabel').val(task.label);
      $('#editTaskAssigned').val(task.assigned_to);
      $('#editTaskAttachments').val(task.attachments);
      $('#due-date').val(task.due_date);
    };
  };

  const commentEditor = new Quill('.comment-editor', {
    modules: {
      toolbar: '.comment-toolbar'
    },
    theme: 'snow',
    placeholder: lang === 'ar' ? 'أدخل التعليق' : 'Enter description',
    theme: 'snow',
  });
  commentEditor.root.innerHTML = taskDetails.description;
</script>
@stop