@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/jkanban/jkanban.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
@stop

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-kanban.css" />
  <style>
    .kanban-board-header .dropdown {
      display: none;
    }
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
    <div class="app-kanban">
      <!-- Kanban Wrapper -->
      @if (!empty($mentorshipTasks) && count($mentorshipTasks) > 0)
        <div 
          class="kanban-wrapper bg-white px-4 py-8" 
          style="border-radius: 1rem;">
        </div>
      @else
        <div class="d-flex flex-column justify-content-center align-items-center gap-4 mt-10">
          <img src="/icons/not-found.svg" width="337" height="234" />
          <h4 class="mb-0 text-center">
            {{ $l == 'en' ? 'You don’t have any tasks' : 'ليس لديك أي مهام' }}
          </h4>
        </div>
      @endif
      <!-- / Kanban Wrapper -->

      <!-- Edit Task/Task & Activities -->
      <div class="offcanvas offcanvas-end kanban-update-item-sidebar">
        <div class="offcanvas-header border-bottom">
          <h5 class="offcanvas-title">Add Comment</h5>
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
                  <span class="align-middle">Activity</span>
                </button>
              </li>
            </ul>
          </div>
          <div class="tab-content p-0">
            <!-- Update item/tasks -->
            <div class="tab-pane fade show active" id="tab-update" role="tabpanel">
              <form class="form-horizontal form-bordered" id="basicForm" action= "{{ route('startup.mentorshipTasks.sendComment') }}" method="POST" enctype="multipart/form-data">
                {!! csrf_field() !!}
                <input type="hidden" name="mentorship_task_id" value="">
                <div class="form-label-group">
                    <textarea class="form-control" id="summernote" name="comment" required></textarea>
                </div>
                
            
                <div class="mt-8">
                  <div class="d-flex flex-wrap">
                    <button type="button" class="btn btn-primary me-4" data-bs-dismiss="offcanvas">
                      {{__('settings.Send')}}
                    </button>
                    <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">
                      {{__('admin.Delete')}}
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
      </div>
    </div>
  </div>
  <!-- / Content -->

  <div class="content-backdrop fade"></div>
</div>
<!-- Content wrapper -->
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/jkanban/jkanban.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
  <script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
@stop

@section('pagescripts')
  <script>
    // Mentorship tasks data
    const tasks = @json($mentorshipTasks);
  </script>
  <script src="/vuexy/assets/js/app-kanban.js"></script>
@stop