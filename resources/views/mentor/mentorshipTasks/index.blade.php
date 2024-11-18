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
    @if (!empty($mentorshipTasks) && count($mentorshipTasks) > 0)
      <div class="row mb-4">
        <div class="col-12">
          <button id="addTaskButton" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="ti ti-plus ti-16px me-2"></i>
            <span class="align-middle">{{ __('admin.Add_Task') }}</span>
          </button>
        </div>
      </div>
    @endif
    <div class="app-kanban">
      <!-- Kanban Wrapper -->
      @if (!empty($mentorshipTasks) && count($mentorshipTasks) > 0)
        <div class="kanban-wrapper bg-white px-4 py-8" style="border-radius: 1rem;">
        </div>
      @else
        <div class="d-flex flex-column justify-content-center align-items-center gap-4 mt-10">
          <img src="/icons/not-found.svg" width="337" height="234" />
          <h4 class="mb-0 text-center">
            {{ $l == 'en' ? 'You don’t have any tasks' : 'ليس لديك أي مهام' }}
          </h4>
          <button id="addTaskButton" class="btn btn-md btn-primary" data-bs-toggle="modal"       data-bs-target="#addTaskModal">
            <i class="ti ti-plus ti-16px me-2"></i>
            <span class="align-middle">{{ __('admin.Add_Task') }}</span>
          </button>
        </div>
      @endif
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
            <input type="hidden" name="MTaskId" id="MTaskId">
             <!-- Attached Tasks -->
             <div class="mb-8 w-100">
              <label for="file2" class="form-label">{{ __('admin.TaskAttachments') }}</label>
              <div class="w-100 d-flex flex-column gap-3">
                <div class="w-50">
                  <input type="text" class="form-control d-none" id="file2" name="attachments" />
                  <div class="btn btn-label-secondary lfm w-100" id="editTaskAttachments" data-input="file2"
                    data-preview="holder3">
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
            <!-- Attached Tasks -->
            <div class="mb-8 w-100">
              <label for="file1" class="form-label">{{ __('admin.CommentAttachments') }}</label>
              <div class="w-100 d-flex flex-column gap-3">
                <div class="w-50">
                  <input type="text" class="form-control d-none" id="file1" name="attachments" />
                  <div class="btn btn-label-secondary lfm w-100" id="editTaskAttachments" data-input="file1"
                    data-preview="holder3">
                    <i class="ti ti-upload me-2"></i>
                    {{ __('admin.Choose') }}
                  </div>
                </div>
                <div class="ms-3" id="holder">
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
              <label for="editTaskAttachments" class="form-label">{{ __('admin.comment') }}</label>
              <div class="form-label-group">
                <textarea class="form-control" id="summernote" name="comment"></textarea>
              </div>
            </div>
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
        <!-- Activities -->
        <div class="tab-pane fade text-heading" id="tab-activity" role="tabpanel">
          @foreach ($allComments as $comment )
          <div class="media mb-4 d-flex align-items-center">
            <div class="media-body">
              <p class="mb-0">
                {{ $comment->comment }}
              </p>
              <small class="text-muted">{{ $comment->created_at }}</small>
            </div>
          </div>
          @endforeach
        </div>
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
<script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
<script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
<script src="/vuexy/assets/vendor/libs/tagify/tagify.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
<script src="/vuexy/assets/vendor/libs/jkanban/jkanban.js"></script>
<script src="/vuexy/assets/vendor/libs/quill/katex.js"></script>
<script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
@stop

@section('pagescripts')
<script src="/vuexy/assets/js/forms-selects.js"></script>
<script>
  // const tasks = @json($mentorshipTasks);
  // const labels = @json($mentorshipTasks->pluck('label_names')->flatten());
  const lng = '{{ $l }}';
  const tasks = @json($mentorshipTasks);
  const labels = @json($labels);
  const whitelist = labels.map(label => label?.name);
</script>
<script src="/vuexy/assets/js/mentor-kanban-tasks.js"></script>
<script>
  // Render Tagify inputs with suggestions
  (() => {
    const addTaskTagifyInput = document.querySelector('#addTaskLabel');
    const editTaskTagifyInput = document.querySelector('#editTaskLabel');

    const tagifyOptions = {
      whitelist,
      maxTags: 10,
      dropdown: {
        maxItems: 20,
        classname: 'tags-inline',
        enabled: 0,
        closeOnSelect: false
      }
    };
    let addTaskTagifySuggestions = new Tagify(addTaskTagifyInput, tagifyOptions);
    let editTaskTagifySuggestions = new Tagify(editTaskTagifyInput, tagifyOptions);
  })();

  // Render flatpickr date inputs
  (() => {
    const flatpickrDate = document.querySelectorAll('#flatpickr-date');
    if (flatpickrDate) {
      flatpickrDate.flatpickr({
        monthSelectorType: 'static'
      });
    };
  })();
</script>
@stop