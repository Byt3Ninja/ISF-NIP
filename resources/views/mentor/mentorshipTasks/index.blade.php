@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
@stop

@php
$cycle = [
  'id' => 84,
  'title' => 'Alumni Program',
];

// Initial mocked data (Replace with actual backend data)
$tasks = [
  [
    'id' => 1,
    'title' => 'Design System Audit',
    'description' => '<p>Review existing UI components.</p>',
    'due_date' => '2025-04-15',
    'priority' => 'High',
    'status' => 'pending',
    'creator' => ['name' => 'Alice Johnson', 'img_url' => '/vuexy/assets/img/avatars/1.png'],
    'attachments' => [
      ['id' => 101, 'name' => 'audit_v1.pdf', 'url' => '/storage/docs/audit_v1.pdf'],
      ['id' => 102, 'name' => 'screenshot.png', 'url' => '/storage/images/screenshot.png']
    ],
    'activities' => [
      ['user' => 'Alice Johnson', 'action' => 'Created this task', 'time' => 'Today 9:00 AM', 'img_url' => '/vuexy/assets/img/avatars/1.png']
    ],
    'comments' => [
      ['id' => 1, 'user' => 'Bob Smith', 'text' => '<p>We need to prioritize the typography tokens.</p>', 'time' => 'Today 10:00 AM', 'img_url' => '/vuexy/assets/img/avatars/2.png']
    ],
    'feedbacks' => [
      ['id' => 1, 'user' => 'Bob Smith', 'text' => '<p>We need to prioritize the typography tokens.</p>', 'time' => 'Today 10:00 AM', 'img_url' => '/vuexy/assets/img/avatars/2.png']
    ],
    'submissions' => [
      [
        'user'    => 'Bob Smith',
        'img_url' => '/vuexy/assets/img/avatars/2.png',
        'time'    => 'Today 11:00 AM',
        'note'    => 'First draft submission.',
        'files'   => [
          ['name' => 'audit_v1.pdf',    'url' => '/storage/docs/audit_v1.pdf'],
          ['name' => 'screenshot.png',  'url' => '/storage/images/screenshot.png'],
        ],
      ],
    ],
  ],
];

$boards = [
  'pending' => ['label' => __('admin.Pending'), 'color' => 'bg-label-secondary'],
  'progress' => ['label' => __('admin.In_Progress'), 'color' => 'bg-label-warning'],
  'review' => ['label' => __('admin.In_Review'), 'color' => 'bg-label-info'],
  'completed' => ['label' => __('admin.Completed'), 'color' => 'bg-label-success'],
];

$priorityConfig = [
  'Low' => ['label' => __('admin.Low'), 'class' => 'bg-label-secondary', 'dot' => 'bg-secondary'],
  'Medium' => ['label' => __('admin.Medium'), 'class' => 'bg-label-info', 'dot' => 'bg-info'],
  'High' => ['label' => __('admin.High'), 'class' => 'bg-label-danger', 'dot' => 'bg-danger'],
];
@endphp

@section('style')
<style>
  /* Minimal custom CSS. Relying on Bootstrap 5 utilities for layout */
  .cursor-grab { cursor: grab; }
  .cursor-grab:active { cursor: grabbing; }
  .sortable-ghost { opacity: 0.4; background-color: var(--bs-secondary-bg) !important; border: 1px dashed var(--bs-secondary); }

  .ql-editor {
    min-height: 150px !important;
    max-height: 150px !important;
  }
  
  #vt-comment-editor .ql-editor {
    min-height: 80px !important;
    max-height: 120px !important;
  }
</style>
@stop

@section('content')
  <div>
    {{-- TOOLBAR --}}
    <div class="card shadow-xs mb-6">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-md-center gap-3">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar">
              <div class="avatar-initial rounded-circle">
                <i class="ti ti-rocket ti-md text-primary"></i>
              </div>
            </div>
            <h5 class="card-title mb-0">{{ $cycle['title'] }}</h5>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="input-group input-group-merge">
              <span class="input-group-text">
                <i class="ti ti-search text-muted"></i>
              </span>
              <input type="text" id="kanbanSearch" class="form-control" placeholder="{{ __('admin.Search') }}..." />
            </div>
            @if ($_user->ableTo('tasks.store', $permissions))
            <button class="btn btn-primary text-nowrap px-8" type="button" data-bs-toggle="offcanvas" data-bs-target="#addTaskOffcanvas">
              <i class="ti ti-plus me-2"></i> {{ __('admin.Add_Task') }}
            </button>
            @endif
          </div>  
        </div>
      </div>
    </div>

    {{-- KANBAN BOARDS --}}
    <div class="d-flex align-items-start gap-4 overflow-x-auto pb-3" id="kanbanWrapper">
      @foreach ($boards as $statusKey => $board)
        @php $boardTasks = array_values(array_filter($tasks, fn($t) => $t['status'] === $statusKey)); @endphp
        <div class="bg-body border rounded p-2 flex-shrink-0 kanban-board" data-board="{{ $statusKey }}" style="width: 300px;">
          <header class="d-flex align-items-center gap-2 px-2 py-2 mb-2">
            <span class="badge {{ $board['color'] }} rounded-pill board-count">{{ count($boardTasks) }}</span>
            <h6 class="mb-0 fw-semibold">{{ $board['label'] }}</h6>
          </header>

          <main class="kanban-drag d-flex flex-column gap-2" style="min-height: 60px;">
            @forelse ($boardTasks as $task)
              @php
                $p = $priorityConfig[$task['priority']];
                $r = $task['creator'] ?? ['name' => 'User'];
                $ri = strtoupper(substr($r['name'], 0, 2));
                $attCount = isset($task['attachments']) ? count($task['attachments']) : 0;
                $commentCount = isset($task['comments']) ? count($task['comments']) : 0;
              @endphp
              <div class="card shadow-sm border-0 cursor-grab kanban-item" data-task-id="{{ $task['id'] }}" data-search-text="{{ strtolower($task['title']) }}">
                <div class="card-body p-3">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge {{ $p['class'] }} rounded-pill" style="font-size: 0.7rem;">
                      <span class="d-inline-block rounded-circle me-1 {{ $p['dot'] }}" style="width: 6px; height: 6px;"></span>{{ $p['label'] }}
                    </span>
                    <div class="d-flex align-items-center gap-1">
                      <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle view-task-btn" type="button" data-task-id="{{ $task['id'] }}" title="{{ __('admin.View') }}"><i class="ti ti-eye ti-md text-dark"></i></button>
                      @if ($_user->ableTo('tasks.update', $permissions))
                      <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle edit-task-btn" type="button" data-task-id="{{ $task['id'] }}" title="{{ __('admin.Edit') }}"><i class="ti ti-edit ti-md text-dark"></i></button>
                      @endif
                      <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle archive-task-btn" type="button" data-task-id="{{ $task['id'] }}" title="{{ __('admin.archive_task') }}"><i class="ti ti-archive ti-md text-dark"></i></button>
                    </div>
                  </div>
                  <h6 class="mb-1 text-truncate">{{ $task['title'] }}</h6>
                  <div class="text-muted small mb-2 line-clamp-2">{!! strip_tags($task['description']) !!}</div>
                  <div class="d-flex align-items-center justify-content-between small text-muted mt-3">
                    <span class="d-flex align-items-center gap-2">
                      @if($attCount > 0)
                        <span class="d-flex align-items-center" title="{{ $attCount }} Attachments">
                          <i class="ti ti-paperclip ti-xs me-1"></i>{{ $attCount }}
                        </span>
                      @endif
                      @if($commentCount > 0)
                        <span class="d-flex align-items-center" title="{{ $commentCount }} Comments">
                          <i class="ti ti-message ti-xs me-1"></i>{{ $commentCount }}
                        </span>
                      @endif
                      <span class="d-flex align-items-center">
                        <i class="ti ti-calendar ti-xs me-1"></i> {{ $task['due_date'] }}
                      </span>
                    </span>
                    <div data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $r['name'] }}">
                      @if (!empty($r['img_url']))
                        <img src="{{ $r['img_url'] }}" alt="avatar" class="rounded-circle object-fit-cover" style="width: 24px; height: 24px;" />
                      @else
                        <span class="badge bg-label-primary rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 24px; height: 24px;">{{ $ri }}</span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <p class="text-muted small text-center py-3 m-0" data-empty-board>{{ __('admin.No_Tasks') }}</p>
            @endforelse
          </main>
        </div>
      @endforeach
    </div>

    {{-- VIEW TASK OFFCANVAS --}}
    <div class="offcanvas offcanvas-end w-px-600" tabindex="-1" id="viewTaskOffcanvas">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title"><i class="ti ti-eye me-1 text-primary"></i> {{ __('admin.View_Task') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body pt-0 d-flex flex-column">
        
        <ul class="nav nav-tabs mb-3 rounded-0 mt-3" role="tablist">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#vt-details" role="tab"><i class="ti ti-info-circle ti-xs me-1"></i> {{ __('admin.Details') ?? 'Details' }}</button>
          </li>
          @role('Mentor')
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#vt-submissions" role="tab">
              <i class="ti ti-paperclip ti-xs me-1"></i> {{ __('admin.Submissions') ?? 'Submissions' }}
              <span class="badge badge-center bg-label-warning rounded-pill ms-1" id="vt-submissions-count">0</span>
            </button>
          </li>
          @endrole
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#vt-feedback" role="tab">
              <i class="ti ti-star ti-xs me-1"></i> {{ __('admin.feedback') }}
              <span class="badge badge-center bg-label-primary rounded-pill ms-1" id="vt-feedback-count">0</span>
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#vt-comments" role="tab">
              <i class="ti ti-message ti-xs me-1"></i> {{ __('admin.Comments') }} 
              <span class="badge badge-center bg-label-danger bg-glow rounded-pill ms-1" id="vt-comment-count">0</span>
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#vt-activity" role="tab">
              <i class="ti ti-activity ti-xs me-1"></i> {{ __('admin.Activities') }}
              <span class="badge badge-center bg-label-info bg-glow rounded-pill ms-1" id="vt-comment-count">0</span>
            </button>
          </li>
        </ul>

        <div class="tab-content p-0 flex-grow-1 overflow-auto">
          {{-- DETAILS TAB --}}
          <div class="tab-pane fade show active" id="vt-details" role="tabpanel">
            <div class="mb-3">
              <span class="text-muted small d-block mb-1">{{ __('admin.Title') }}</span>
              <h6 id="vt-title" class="mb-0"></h6>
            </div>
            <div class="mb-3">
              <span class="text-muted small d-block mb-1">{{ __('admin.Description') }}</span>
              <div id="vt-description" class="bg-body border rounded p-3 small" style="min-height: 80px;"></div>
            </div>
            <div class="row g-3 mb-4">
              <div class="col-6">
                <span class="text-muted small d-block mb-1">{{ __('admin.Due_Date') }}</span>
                <p id="vt-due-date" class="mb-0 small fw-medium"></p>
              </div>
              <div class="col-6">
                <span class="text-muted small d-block mb-1">{{ __('admin.Priority') }}</span>
                <div id="vt-priority"></div>
              </div>
              <div class="col-6">
                <span class="text-muted small d-block mb-1">{{ __('admin.Status') }}</span>
                <div id="vt-status"></div>
              </div>
            </div>

            <div class="mb-4">
              <span class="text-muted small d-block mb-1">{{ __('admin.Attachments') }}</span>
              <div id="vt-attachments-list" class="d-flex flex-column gap-2 small mb-3"></div>

              {{-- Add Attachment Directly from View Drawer --}}
              <div class="border-top pt-3">
                <label class="form-label small">{{ __('admin.your_submission') }} <span class="text-muted">({{ __('admin.max') }} 10MB)</span></label>
                <div class="input-group input-group-sm">
                  <input type="file" id="vt-new-attachment" class="form-control"
                    accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/zip,application/x-zip-compressed,text/plain,text/csv" />
                  <button class="btn btn-outline-primary" type="button" id="vt-upload-btn"><i class="ti ti-upload ti-xs me-1"></i> {{ __('admin.Upload') }}</button>
                </div>
                <div class="invalid-feedback d-block" id="vt-upload-error"></div>
              </div>
            </div>
          </div>

          {{-- SUBMISSIONS TAB --}}
          <div class="tab-pane fade" id="vt-submissions" role="tabpanel">
            <div id="vt-submissions-list" class="small pe-2"></div>
          </div>

           {{-- FEEDBACK TAB --}}
          <div class="tab-pane fade" id="vt-feedback" role="tabpanel">
            <div id="vt-feedback-list" class="small pe-2"></div>
          </div>
          
          {{-- COMMENTS TAB --}}
          <div class="tab-pane fade" id="vt-comments" role="tabpanel">

            <div id="vt-comments-list" class="small mb-4 pe-2"></div>
            
            <div class="border-top pt-3 mt-auto">
              <label class="form-label small fw-medium">{{ __('admin.Add comment') }}</label>
              <div id="vt-comment-editor" class="border rounded-top"></div>
              <div class="border border-top-0 rounded-bottom p-1 d-flex justify-content-between align-items-center">
                <div id="vt-comment-toolbar">
                  <span class="ql-formats m-0">
                    <button class="ql-bold"></button>
                    <button class="ql-italic"></button>
                    <button class="ql-underline"></button>
                  </span>
                </div>
                <button class="btn btn-sm btn-primary" type="button" id="vt-send-comment-btn">{{ __('admin.send') }}</button>
              </div>
            </div>
          </div>

          {{-- ACTIVITY TAB --}}
          <div class="tab-pane fade" id="vt-activity" role="tabpanel">
            <div id="vt-activities-list" class="small pe-2"></div>
          </div>
        </div>

        <div class="d-flex gap-2 pt-3 border-top mt-3">
          @if ($_user->ableTo('tasks.update', $permissions))
          <button type="button" class="btn btn-outline-primary" id="vt-edit-trigger-btn">
            <i class="ti ti-edit ti-xs me-1"></i> {{ __('admin.Edit_Task') }}
          </button>
          @endif
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('admin.Close') }}</button>
        </div>
      </div>
    </div>

    {{-- ADD TASK OFFCANVAS --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="addTaskOffcanvas" style="width: 480px;">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title"><i class="ti ti-plus me-1 text-primary"></i> {{ __('admin.add_new_task') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body">
        <form class="d-flex flex-column h-100" id="addTaskForm" novalidate enctype="multipart/form-data">
          <div class="flex-grow-1 overflow-auto">
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Title') }} <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control" placeholder="{{ __('admin.Title') }}" />
            </div>
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Description') }}</label>
              <div class="border border-bottom-0 rounded-top p-1"><div id="add-desc-toolbar"><span class="ql-formats"><button class="ql-bold"></button><button class="ql-italic"></button><button class="ql-list" value="ordered"></button><button class="ql-list" value="bullet"></button></span></div></div>
              <div id="add-desc-editor" class="border rounded-top"></div>
            </div>
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Due_Date') }}</label>
              <input type="text" id="add-due-date" name="due_date" class="form-control" placeholder="YYYY-MM-DD" />
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">{{ __('admin.Priority') }} <span class="text-danger">*</span></label>
                <select name="priority" id="add-priority" class="select2 form-select">
                  <option value="">{{ __('admin.Select') }}</option>
                  <option value="Low">{{ __('admin.Low') }}</option>
                  <option value="Medium">{{ __('admin.Medium') }}</option>
                  <option value="High">{{ __('admin.High') }}</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">{{ __('admin.Status') }} <span class="text-danger">*</span></label>
                <select name="status" id="add-status" class="select2 form-select">
                  <option value="">{{ __('admin.Select') }}</option>
                  <option value="pending">{{ __('admin.Pending') }}</option>
                  <option value="progress">{{ __('admin.In_Progress') }}</option>
                  <option value="review">{{ __('admin.In_Review') }}</option>
                  <option value="completed">{{ __('admin.Completed') }}</option>
                </select>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label d-flex justify-content-between">
                <span>{{ __('admin.Attachments') }}</span>
                <span class="text-muted small">{{ __('admin.Max_Each_5_Image_Pdf') }}</span>
              </label>
              <input type="file" name="attachments[]" id="add-attachments" class="form-control" accept="image/jpeg,image/png,image/gif,application/pdf" multiple />
              <div class="invalid-feedback" id="add-file-error"></div>
            </div>

          </div>
          <div class="d-flex gap-2 mt-auto">
            <button type="submit" class="btn btn-primary" id="add-submit-btn">{{ __('admin.Create') }}</button>
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('admin.Cancel') }}</button>
          </div>
        </form>
      </div>
    </div>

    {{-- EDIT TASK OFFCANVAS --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="editTaskOffcanvas" style="width: 480px;">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title"><i class="ti ti-edit me-1 text-warning"></i> {{ __('admin.Edit_Task') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body">
        <form id="editTaskForm" class="d-flex flex-column h-100" novalidate enctype="multipart/form-data">
          <input type="hidden" id="et-id" name="id" />
          <input type="hidden" name="_method" value="PUT" />

          <div class="flex-grow-1 overflow-auto">
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Title') }} <span class="text-danger">*</span></label>
              <input type="text" id="et-title" name="title" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Description') }}</label>
              <div class="border border-bottom-0 rounded-top p-1"><div id="et-desc-toolbar"><span class="ql-formats"><button class="ql-bold"></button><button class="ql-italic"></button><button class="ql-list" value="ordered"></button><button class="ql-list" value="bullet"></button></span></div></div>
              <div id="et-desc-editor" class="border rounded-top"></div>
            </div>
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Due_Date') }}</label>
              <input type="text" id="et-due-date" name="due_date" class="form-control" />
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">{{ __('admin.Priority') }} <span class="text-danger">*</span></label>
                <select name="priority" id="et-priority" class="select2 form-select">
                  <option value="Low">{{ __('admin.Low') }}</option>
                  <option value="Medium">{{ __('admin.Medium') }}</option>
                  <option value="High">{{ __('admin.High') }}</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">{{ __('admin.Status') }} <span class="text-danger">*</span></label>
                <select name="status" id="et-status" class="select2 form-select">
                  <option value="pending">{{ __('admin.Pending') }}</option>
                  <option value="progress">{{ __('admin.In_Progress') }}</option>
                  <option value="review">{{ __('admin.In_Review') }}</option>
                  <option value="completed">{{ __('admin.Completed') }}</option>
                </select>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">{{ __('admin.Attachments') }}</label>

              <div id="et-current-attachments-wrapper" class="mb-3 d-none">
                <span class="d-block small text-muted mb-2">{{ __('admin.current_files') }}</span>
                <div id="et-current-attachments-list" class="d-flex flex-column gap-2 small"></div>
              </div>

              <label class="form-label d-flex justify-content-between">
                <span>{{ __('admin.Upload') }}</span>
                <span class="text-muted small">{{ __('admin.max') }} 5MB</span>
              </label>
              <input type="file" name="attachments[]" id="et-attachments" class="form-control" accept="image/jpeg,image/png,image/gif,application/pdf" multiple />
              <div class="invalid-feedback" id="et-file-error"></div>
            </div>
          </div>

          <div class="d-flex gap-2 mt-auto">
            <button type="submit" class="btn btn-primary" id="et-submit-btn">{{ __('admin.Update') }}</button>
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('admin.Cancel') }}</button>
          </div>
        </form>
      </div>
    </div>

  </div>
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/sortablejs/sortable.js"></script>
<script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
@stop

@section('pagescripts')
<script>
$(document).ready(function () {
  'use strict';

  const isAbleToEdit = '{{ $_user->ableTo('tasks.store', $permissions) }}' === '1' ;

  const PRIORITY_MAP = {
    Low:    { label: '{{ __("admin.Low") }}',    cls: 'bg-label-secondary', dot: 'bg-secondary' },
    Medium: { label: '{{ __("admin.Medium") }}', cls: 'bg-label-info',      dot: 'bg-info' },
    High:   { label: '{{ __("admin.High") }}',   cls: 'bg-label-danger',    dot: 'bg-danger' },
  };

  const STATUS_MAP = {
    pending:   '{{ __("admin.Pending") }}',
    progress:  '{{ __("admin.In_Progress") }}',
    review:    '{{ __("admin.In_Review") }}',
    completed: '{{ __("admin.Completed") }}',
  };

  let tasks = @json($tasks);
  const program = @json($cycle);

  // Fallback string definitions if translation is missing
  const noActivitiesStr = '{{ __("admin.no_recent_activities") }}';
  const noCommentsStr   = '{{ __("admin.no_comments") }}';

  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  // --- Validate Multiple Files ---
  function validateFiles(fileInput, errorSelector) {
    const files = fileInput[0].files;
    const $errorDiv = $(errorSelector);
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml', 'application/pdf', 'application/zip', 'application/x-rar-compressed', 'application/vnd.rar', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
    const maxSize = 10 * 1024 * 1024; // 10MB limit
    
    fileInput.removeClass('is-invalid');
    $errorDiv.text('');

    if (files.length === 0) return true;

    for (let i = 0; i < files.length; i++) {
      if (!allowedTypes.includes(files[i].type)) {
        fileInput.addClass('is-invalid');
        $errorDiv.text(`File "${files[i].name}" has an invalid type. Only JPG, PNG, GIF, PDF allowed.`);
        return false;
      }
      if (files[i].size > maxSize) {
        fileInput.addClass('is-invalid');
        $errorDiv.text(`File "${files[i].name}" exceeds the 5MB limit.`);
        return false;
      }
    }
    return true;
  }

  // --- Build a kanban card HTML string for a task ---
  function buildTaskCard(task) {
    const p = PRIORITY_MAP[task.priority] || PRIORITY_MAP['Low'];
    const r = task.creator || { name: 'User', img_url: null };
    const ri = (r.name || 'U').substring(0, 2).toUpperCase();
    const attCount = task.attachments ? task.attachments.length : 0;
    const commentCount = task.comments ? task.comments.length : 0;
    const avatarHtml = r.img_url
      ? `<img src="${r.img_url}" alt="avatar" class="rounded-circle object-fit-cover" style="width:24px;height:24px;" />`
      : `<span class="badge bg-label-primary rounded-circle d-flex align-items-center justify-content-center p-0" style="width:24px;height:24px;">${ri}</span>`;

    return `
      <div class="card shadow-sm border-0 cursor-grab kanban-item" data-task-id="${task.id}" data-search-text="${(task.title || '').toLowerCase()}">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="badge ${p.cls} rounded-pill" style="font-size:0.7rem;">
              <span class="d-inline-block rounded-circle me-1 ${p.dot}" style="width:6px;height:6px;"></span>${p.label}
            </span>
            <div class="d-flex align-items-center gap-1">
              <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle view-task-btn" type="button" data-task-id="${task.id}" title="{{ __('admin.View') }}"><i class="ti ti-eye ti-md text-dark"></i></button>
              ${isAbleToEdit 
                ? `<button class="btn btn-sm btn-icon btn-text-secondary rounded-circle edit-task-btn" type="button" data-task-id="${task.id}" title="{{ __('admin.Edit') }}"><i class="ti ti-edit ti-md text-dark"></i></button>` 
                : ''
              }
              <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle archive-task-btn" type="button" data-task-id="${task.id}" title="{{ __('admin.archive_task') }}"><i class="ti ti-archive ti-md text-dark"></i></button>
            </div>
          </div>
          <h6 class="mb-1 text-truncate">${$('<div>').text(task.title).html()}</h6>
          <div class="text-muted small mb-2 line-clamp-2">${$('<div>').html(task.description).text()}</div>
          <div class="d-flex align-items-center justify-content-between small text-muted mt-3">
            <span class="d-flex align-items-center gap-2">
              ${attCount > 0 ? `<span class="d-flex align-items-center" title="${attCount} Attachments"><i class="ti ti-paperclip ti-xs me-1"></i>${attCount}</span>` : ''}
              ${commentCount > 0 ? `<span class="d-flex align-items-center" title="${commentCount} Comments"><i class="ti ti-message ti-xs me-1"></i>${commentCount}</span>` : ''}
              <span class="d-flex align-items-center"><i class="ti ti-calendar ti-xs me-1"></i> ${task.due_date || ''}</span>
            </span>
            <div data-bs-toggle="tooltip" data-bs-placement="top" title="${$('<div>').text(r.name).html()}">${avatarHtml}</div>
          </div>
        </div>
      </div>`;
  }

  // --- Add or update a task card in the correct board column ---
  function syncTaskToBoard(task, oldStatus) {
    const $existingCard = $(`.kanban-item[data-task-id="${task.id}"]`);
    const $targetBoard = $(`.kanban-board[data-board="${task.status}"] .kanban-drag`);
    const newCardHtml = buildTaskCard(task);

    if ($existingCard.length) {
      if (oldStatus && oldStatus !== task.status) {
        // Moved to a different column — remove from old, append to new
        const $oldBoard = $(`.kanban-board[data-board="${oldStatus}"] .kanban-drag`);
        $existingCard.remove();
        if ($oldBoard.find('.kanban-item:not(.d-none)').length === 0) {
          $oldBoard.append('<p class="text-muted small text-center py-3 m-0" data-empty-board>{{ __("admin.No_Tasks") }}</p>');
        }
        $targetBoard.find('[data-empty-board]').remove();
        $targetBoard.append(newCardHtml);
      } else {
        // Same column — replace in place
        $existingCard.replaceWith(newCardHtml);
      }
    } else {
      // New task — append to target board
      $targetBoard.find('[data-empty-board]').remove();
      $targetBoard.append(newCardHtml);
    }

    // Reinit tooltips on new elements
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Update all board badge counts
    $('.kanban-board').each(function () {
      $(this).find('.board-count').text($(this).find('.kanban-item:not(.d-none)').length);
    });
  }

  // --- HTML Generator for Attachments ---
  function renderAttachmentsList(attachments, taskId, containerSelector) {
    const $container = $(containerSelector);
    if (!attachments || attachments.length === 0) {
      $container.html('<span class="text-muted small">@lang("admin.no_attachments")</span>');
      return;
    }

    let html = '';
    attachments.forEach(att => {
      html += `
        <div class="d-flex align-items-center justify-content-between border rounded p-2 bg-lighter attachment-item" id="att-row-${att.id}">
          <a href="${att.url}" target="_blank" class="text-primary fw-medium text-truncate d-flex align-items-center gap-1" style="max-width: 85%;">
            <i class="ti ti-paperclip ti-xs flex-shrink-0"></i> 
            <span class="text-truncate">${att.name}</span>
          </a>
          <button type="button" class="btn btn-icon btn-sm btn-text-danger rounded-circle delete-attachment-btn" data-task-id="${taskId}" data-attachment-id="${att.id}" title="{{ __('admin.Remove') }}">
            <i class="ti ti-trash ti-xs"></i>
          </button>
        </div>
      `;
    });
    $container.html(html);
  }

  // --- HTML Generator for Activities ---
  function renderActivitiesList(activities, containerSelector) {
    const $container = $(containerSelector);
    if (!activities || activities.length === 0) {
      $container.html(`<p class="text-muted text-center py-4 mb-0">${noActivitiesStr}</p>`);
      return;
    }
    
    let html = '';
    activities.forEach(act => {
      const ri = (act.user || 'U').substring(0, 2).toUpperCase();
      const avatar = act.img_url 
        ? `<img src="${act.img_url}" alt="${act.user}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">` 
        : `<span class="avatar-initial bg-label-primary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:12px;">${ri}</span>`;
      
      html += `
        <div class="d-flex align-items-start gap-3 mb-3 pb-2 border-bottom">
          <div class="flex-shrink-0 mt-1">${avatar}</div>
          <div>
            <p class="mb-1"><span class="fw-semibold text-dark">${act.user}</span> ${act.action}</p>
            <span class="text-muted" style="font-size: 0.75rem;">${act.time}</span>
          </div>
        </div>
      `;
    });
    $container.html(html);
  }

  // --- HTML Generator for Submissions ---
  function renderSubmissionsList(submissions, containerSelector) {
    const $container = $(containerSelector);
    const noSubmissionsStr = '{{ __("admin.no_submissions") ?? "No submissions yet." }}';

    if (!submissions || submissions.length === 0) {
      $container.html(`<p class="text-muted text-center py-4 mb-0">${noSubmissionsStr}</p>`);
      return;
    }

    const EXT_ICON = {
      pdf:  'ti-file-type-pdf text-danger',
      doc:  'ti-file-type-doc text-primary',
      docx: 'ti-file-type-doc text-primary',
      xls:  'ti-file-type-xls text-success',
      xlsx: 'ti-file-type-xls text-success',
      png:  'ti-photo text-info',
      jpg:  'ti-photo text-info',
      jpeg: 'ti-photo text-info',
      gif:  'ti-photo text-info',
    };

    function fileIcon(name) {
      const ext = (name || '').split('.').pop().toLowerCase();
      return EXT_ICON[ext] || 'ti-file text-secondary';
    }

    let html = '';
    submissions.forEach(sub => {
      const ri = (sub.user || 'U').substring(0, 2).toUpperCase();
      const avatar = sub.img_url
        ? `<img src="${sub.img_url}" alt="${sub.user}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">`
        : `<span class="avatar-initial bg-label-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:12px;">${ri}</span>`;

      const files = sub.files || [];
      const filesHtml = files.length
        ? files.map(f => `
            <a href="${f.url}" target="_blank" class="d-flex align-items-center gap-2 border rounded p-2 bg-body text-body text-decoration-none mb-1">
              <i class="ti ${fileIcon(f.name)} ti-sm flex-shrink-0"></i>
              <span class="text-truncate small">${f.name}</span>
              <i class="ti ti-download ti-xs ms-auto text-muted flex-shrink-0"></i>
            </a>`).join('')
        : `<span class="text-muted small">{{ __('admin.no_attachments') ?? 'No files.' }}</span>`;

      html += `
        <div class="mb-4 pb-3 border-bottom">
          <div class="d-flex align-items-center gap-2 mb-2">
            ${avatar}
            <div>
              <span class="fw-semibold text-dark d-block">${sub.user || ''}</span>
              <span class="text-muted" style="font-size:0.7rem;">${sub.time || ''}</span>
            </div>
          </div>
          ${sub.note ? `<p class="small text-muted mb-2">${sub.note}</p>` : ''}
          <div>${filesHtml}</div>
        </div>`;
    });

    $container.html(html);
  }

  // --- HTML Generator for Feedback ---
  function renderFeedbackList(feedbacks, containerSelector) {
    const $container = $(containerSelector);
    const noFeedbackStr = '{{ __("admin.no_feedback") }}';

    if (!feedbacks || feedbacks.length === 0) {
      $container.html(`<p class="text-muted text-center py-4 mb-0">${noFeedbackStr}</p>`);
      return;
    }

    const RATING_COLORS = ['', 'danger', 'danger', 'warning', 'info', 'success'];

    let html = '';
    feedbacks.forEach(fb => {
      const ri = (fb.user || 'U').substring(0, 2).toUpperCase();
      const avatar = fb.img_url
        ? `<img src="${fb.img_url}" alt="${fb.user}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">`
        : `<span class="avatar-initial bg-label-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:12px;">${ri}</span>`;

      const rating = parseInt(fb.rating) || 0;
      const stars = Array.from({ length: 5 }, (_, i) =>
        `<i class="ti ti-star${i < rating ? '-filled text-warning' : ' text-muted'}" style="font-size:0.85rem;"></i>`
      ).join('');

      html += `
        <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
          <div class="flex-shrink-0 mt-1">${avatar}</div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="fw-semibold text-dark">${fb.user || ''}</span>
              <span class="text-muted" style="font-size:0.7rem;">${fb.time || ''}</span>
            </div>
            <div class="mb-1">${stars}</div>
            ${fb.text ? `<div class="small text-body bg-lighter rounded p-2">${fb.text}</div>` : ''}
          </div>
        </div>`;
    });
    $container.html(html);
  }

  // --- HTML Generator for Comments ---
  function renderCommentsList(comments, containerSelector) {
    const $container = $(containerSelector);
    if (!comments || comments.length === 0) {
      $container.html(`<p class="text-muted text-center py-4 mb-0">${noCommentsStr}</p>`);
      return;
    }
    
    let html = '';
    comments.forEach(c => {
      const ri = (c.user || 'U').substring(0, 2).toUpperCase();
      const avatar = c.img_url 
        ? `<img src="${c.img_url}" alt="${c.user}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">` 
        : `<span class="avatar-initial bg-label-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:12px;">${ri}</span>`;
      
      html += `
        <div class="d-flex align-items-start gap-3 mb-3">
          <div class="flex-shrink-0 mt-1">${avatar}</div>
          <div class="flex-grow-1 bg-lighter rounded p-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="fw-semibold text-dark">${c.user}</span>
              <span class="text-muted" style="font-size: 0.7rem;">${c.time}</span>
            </div>
            <div class="ql-editor p-0 small text-body" style="min-height:auto;">${c.text}</div>
          </div>
        </div>
      `;
    });
    $container.html(html);
  }

  // --- Initialize Plugins ---
  const addDescQ = new Quill('#add-desc-editor', { theme: 'snow', modules: { toolbar: '#add-desc-toolbar' } });
  const etDescQ  = new Quill('#et-desc-editor',  { theme: 'snow', modules: { toolbar: '#et-desc-toolbar' } });
  const vtCommentQ = new Quill('#vt-comment-editor', { theme: 'snow', modules: { toolbar: '#vt-comment-toolbar' } });

  $('#add-due-date, #et-due-date').flatpickr({ dateFormat: 'Y-m-d' });
  $('#add-priority, #add-status').select2({ dropdownParent: $('#addTaskOffcanvas') });
  $('#et-priority, #et-status').select2({ dropdownParent: $('#editTaskOffcanvas') });

  // --- Drag & Drop ---
  $('.kanban-drag').each(function () {
    new Sortable(this, {
      group: 'kanban-boards',
      animation: 150,
      ghostClass: 'sortable-ghost',
      onEnd: function (evt) {
        if (evt.from === evt.to) return;

        const taskId = $(evt.item).data('task-id');
        const newStatus = $(evt.to).closest('.kanban-board').data('board');
        const oldStatus = $(evt.from).closest('.kanban-board').data('board');

        // Update tasks array
        const task = tasks.find(t => t.id === taskId);
        if (task) task.status = newStatus;

        $(evt.to).find('[data-empty-board]').remove();
        if ($(evt.from).find('.kanban-item:not(.d-none)').length === 0) {
          $(evt.from).append('<p class="text-muted small text-center py-3 m-0" data-empty-board>@lang("admin.No_Tasks")</p>');
        }

        $('.kanban-board').each(function () {
          $(this).find('.board-count').text($(this).find('.kanban-item:not(.d-none)').length);
        });

        $.ajax({
          url: '{{ route("tasks.move", ["taskId" => ":id", "status" => ":status"]) }}'
            .replace(':id', taskId)
            .replace(':status', newStatus),
          type: 'PATCH',
          contentType: 'application/json',
          data: JSON.stringify({ status: newStatus }),
          error: function () {
            // Revert: move card back and restore tasks array
            if (task) task.status = oldStatus;
            $(evt.from).append(evt.item);
            $(evt.to).find('[data-empty-board]').remove();
            if ($(evt.to).find('.kanban-item:not(.d-none)').length === 0) {
              $(evt.to).append('<p class="text-muted small text-center py-3 m-0" data-empty-board>@lang("admin.No_Tasks")</p>');
            }
            $('.kanban-board').each(function () {
              $(this).find('.board-count').text($(this).find('.kanban-item:not(.d-none)').length);
            });
            toastr.error('{{ __("admin.move_task_failed") }}');
          }
        });
      }
    });
  });

  // --- Form Validation Config ---
  const fvConfig = {
    fields: {
      title: { validators: { notEmpty: { message: '{{ __("admin.Required_Field") }}' } } },
      priority: { validators: { notEmpty: { message: '{{ __("admin.Required_Field") }}' } } },
      status: { validators: { notEmpty: { message: '{{ __("admin.Required_Field") }}' } } }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({ rowSelector: '.mb-3, .col-6' })
    }
  };

  const addFv = FormValidation.formValidation(document.getElementById('addTaskForm'), fvConfig);
  const etFv  = FormValidation.formValidation(document.getElementById('editTaskForm'), fvConfig);

  $('#add-priority').on('change', () => addFv.revalidateField('priority'));
  $('#add-status').on('change', () => addFv.revalidateField('status'));
  $('#et-priority').on('change', () => etFv.revalidateField('priority'));
  $('#et-status').on('change', () => etFv.revalidateField('status'));

  $('#add-attachments').on('change', function() { validateFiles($(this), '#add-file-error'); });
  $('#et-attachments').on('change', function() { validateFiles($(this), '#et-file-error'); });
  $('#vt-new-attachment').on('change', function() { validateFiles($(this), '#vt-upload-error'); });


  // --- DELETE ATTACHMENT (Shared) ---
  $(document).on('click', '.delete-attachment-btn', function () {
    const $btn = $(this);
    const taskId = $btn.data('task-id');
    const attachmentId = $btn.data('attachment-id');

    releaseWarningAlert({
      title: '{{ __("admin.delete_attachment") }}?',
      message: '{{ __("admin.Recover") }}',
      confirmText: '{{ __("admin.DYes") }}',
      cancelText: '{{ __("admin.DNo") }}',
      confirmColor: 'danger',
      onConfirm: () => {
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
  
        $.ajax({
          url: '{{ route("tasks.delete_attachment", ["taskId" => ":taskId", "filename" => ":filename"]) }}'.replace(':taskId', taskId).replace(':filename', attachmentId),
          type: 'DELETE',
          success: function() {
            $(`#att-row-${attachmentId}`).fadeOut(300, function() {
              $(this).remove();
              toastr.success('{{ __("admin.attachment_deleted_success") }}');

              // Sync tasks array
              const task = tasks.find(t => t.id == taskId);
              if (task && task.attachments) {
                task.attachments = task.attachments.filter(a => a.id != attachmentId);
              }

              if ($('#vt-attachments-list .attachment-item').length === 0) {
                $('#vt-attachments-list').html('<span class="text-muted small">@lang("admin.no_attachments")</span>');
              }
              if ($('#et-current-attachments-list .attachment-item').length === 0) {
                $('#et-current-attachments-wrapper').addClass('d-none');
              }
            });
          },
          error: function() {
            toastr.error('{{ __("admin.delete_failed") }}');
            $btn.prop('disabled', false).html('<i class="ti ti-trash ti-xs"></i>');
          }
        });
      }
    });
  });

  // --- ADD ATTACHMENT (Directly from View Drawer) ---
  $('#vt-upload-btn').on('click', function () {
    const $fileInput = $('#vt-new-attachment');
    if ($fileInput[0].files.length === 0) return;
    if (!validateFiles($fileInput, '#vt-upload-error')) return;

    const taskId = $(this).data('task-id');
    const $btn = $(this);

    let formData = new FormData();
    formData.append('attachment', $fileInput[0].files[0]);

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    $.ajax({
      url: '{{ route("tasks.upload_attachment", ":taskId") }}'.replace(':taskId', taskId),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        toastr.success('{{ __("admin.attachment_uploaded_success") }}');
        const newAtt = response.attachment || { id: Date.now(), name: $fileInput[0].files[0].name, url: '#' };

        // Sync tasks array
        const task = tasks.find(t => t.id == taskId);
        if (task) {
          if (!task.attachments) task.attachments = [];
          task.attachments.push(newAtt);
        }
        
        if ($('#vt-attachments-list').text().trim() === '@lang("admin.no_attachments")' || $('#vt-attachments-list').text().trim() === 'None') {
          $('#vt-attachments-list').empty();
        }
        
        const html = `
          <div class="d-flex align-items-center justify-content-between border rounded p-2 bg-lighter attachment-item" id="att-row-${newAtt.id}">
            <a href="${newAtt.url}" target="_blank" class="text-primary fw-medium text-truncate d-flex align-items-center gap-1" style="max-width: 85%;">
              <i class="ti ti-paperclip ti-xs flex-shrink-0"></i> <span class="text-truncate">${newAtt.name}</span>
            </a>
            <button type="button" class="btn btn-icon btn-sm btn-text-danger rounded-circle delete-attachment-btn" data-task-id="${taskId}" data-attachment-id="${newAtt.id}" title="{{ __('admin.Remove') }}">
              <i class="ti ti-trash ti-xs"></i>
            </button>
          </div>
        `;
        $('#vt-attachments-list').append(html);
        $fileInput.val('');
        $btn.prop('disabled', false).html('<i class="ti ti-upload ti-xs me-1"></i> {{ __("admin.Upload") }}');
      },
      error: function() {
        toastr.error('{{ __("admin.upload_failed") }}');
        $btn.prop('disabled', false).html('<i class="ti ti-upload ti-xs me-1"></i> {{ __("admin.Upload") }}');
      }
    });
  });

  // --- ADD TASK POST ---
  $('#addTaskForm').on('submit', function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#add-submit-btn');

    addFv.validate().then(function (status) {
      if (status !== 'Valid') return;
      if (!validateFiles($('#add-attachments'), '#add-file-error')) return;

      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>{{ __("admin.processing") }}');

      let formData = new FormData($form[0]);
      formData.append('description', addDescQ.root.innerHTML);
      formData.append('program_id', program.id);

      $.ajax({
        url: '{{ route("tasks.store") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) { 
          toastr.success('{{ __("admin.StoredSuccessfully") }}');

          // Build new task object from response or form data
          const newTask = response.task || {
            id: response.id || Date.now(),
            title: $form.find('[name="title"]').val(),
            description: addDescQ.root.innerHTML,
            due_date: $form.find('[name="due_date"]').val(),
            priority: $('#add-priority').val(),
            status: $('#add-status').val(),
            creator: response.creator || null,
            attachments: response.attachments || [],
            activities: response.activities || [],
            comments: [],
          };

          tasks.push(newTask);
          syncTaskToBoard(newTask, null);

          // Reset form
          $form[0].reset();
          addDescQ.setContents([]);
          $('#add-priority, #add-status').val('').trigger('change');
          addFv.resetForm(true);

          $('#addTaskOffcanvas').offcanvas('hide');
          $btn.prop('disabled', false).html('{{ __("admin.Create") }}');
        },
        error: function() {
          toastr.error('{{ __("admin.save_failed") }}');
          $btn.prop('disabled', false).text('{{ __("admin.Create") }}');
        }
      });
    });
  });

  // --- VIEW TASK ---
  $(document).on('click', '.view-task-btn', function () {
    const taskId = $(this).data('task-id');
    const taskData = tasks.find(task => task.id === taskId);
    if(!taskData) return;

    const p = PRIORITY_MAP[taskData.priority];
    const sLabel = STATUS_MAP[taskData.status];

    $('#vt-title').text(taskData.title);
    $('#vt-description').html(taskData.description);
    $('#vt-due-date').text(taskData.due_date || 'N/A');
    $('#vt-priority').html(`<span class="badge ${p.cls} rounded-pill"><span class="d-inline-block rounded-circle me-1 ${p.dot}" style="width: 6px; height: 6px;"></span>${p.label}</span>`);
    $('#vt-status').html(`<span class="badge bg-label-secondary rounded-pill">${sLabel}</span>`);
    
    renderAttachmentsList(taskData.attachments, taskId, '#vt-attachments-list');
    renderActivitiesList(taskData.activities, '#vt-activities-list');
    renderCommentsList(taskData.comments, '#vt-comments-list');
    renderFeedbackList(taskData.feedbacks || [], '#vt-feedback-list');
    renderSubmissionsList(taskData.submissions || [], '#vt-submissions-list');
    $('#vt-comment-count').text(taskData.comments ? taskData.comments.length : 0);
    $('#vt-feedback-count').text(taskData.feedbacks ? taskData.feedbacks.length : 0);
    $('#vt-submissions-count').text(taskData.submissions ? taskData.submissions.length : 0);

    $('#vt-edit-trigger-btn').data('task-id', taskId);
    $('#vt-upload-btn').data('task-id', taskId);
    $('#vt-send-comment-btn').data('task-id', taskId);
    $('#vt-new-attachment').val(''); 
    vtCommentQ.setContents([]); 

    // Reset tabs to show details first
    const viewTabs = new bootstrap.Tab(document.querySelector('#viewTaskOffcanvas .nav-tabs .nav-link'));
    viewTabs.show();

    bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('viewTaskOffcanvas')).show();
  });

  $('#vt-edit-trigger-btn').on('click', function() {
    const taskId = $(this).data('task-id');
    bootstrap.Offcanvas.getInstance(document.getElementById('viewTaskOffcanvas')).hide();
    setTimeout(() => { $(`.edit-task-btn[data-task-id="${taskId}"]`).trigger('click'); }, 350);
  });

  // --- ADD COMMENT (View Task) ---
  $('#vt-send-comment-btn').on('click', function() {
    const taskId = $(this).data('task-id');
    const commentHtml = vtCommentQ.root.innerHTML;
    const plainText = vtCommentQ.getText().trim();
    
    if (!plainText) return;

    const $btn = $(this);
    $btn.prop('disabled', true).text('...');

    $.ajax({
      url: '{{ route("tasks.add_comment", ":id") }}'.replace(':id', taskId),
      type: 'POST',
      data: JSON.stringify({ comment: commentHtml }),
      contentType: 'application/json',
      success: function(response) {
        toastr.success('{{ __("admin.comment_success") }}');
        
        const newComment = response.comment || { 
          user: 'You', 
          time: 'Just now', 
          text: commentHtml, 
          img_url: null 
        };

        if ($('#vt-comments-list').text().trim() === noCommentsStr) {
          $('#vt-comments-list').empty();
        }

        const ri = (newComment.user || 'U').substring(0, 2).toUpperCase();
        const avatar = newComment.img_url 
          ? `<img src="${newComment.img_url}" alt="${newComment.user}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">` 
          : `<span class="avatar-initial bg-label-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:12px;">${ri}</span>`;

        const html = `
          <div class="d-flex align-items-start gap-3 mb-3">
            <div class="flex-shrink-0 mt-1">${avatar}</div>
            <div class="flex-grow-1 bg-lighter rounded p-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-semibold text-dark">${newComment.user}</span>
                <span class="text-muted" style="font-size: 0.7rem;">${newComment.time}</span>
              </div>
              <div class="ql-editor p-0 small text-body" style="min-height:auto;">${newComment.text}</div>
            </div>
          </div>
        `;
        
        $('#vt-comments-list').append(html);

        let count = parseInt($('#vt-comment-count').text()) || 0;
        $('#vt-comment-count').text(count + 1);

        // Sync tasks array
        const task = tasks.find(t => t.id == taskId);
        if (task) {
          if (!task.comments) task.comments = [];
          task.comments.push(newComment);
        }

        vtCommentQ.setContents([]);
        $btn.prop('disabled', false).text('{{ __("admin.send") }}');
      },
      error: function() {
        toastr.error('{{ __("admin.comment_failed") }}');
        $btn.prop('disabled', false).text('{{ __("admin.send") }}');
      }
    });
  });

  // --- ARCHIVE TASK ---
  $(document).on('click', '.archive-task-btn', function () {
    const taskId = $(this).data('task-id');
    const task = tasks.find(t => t.id === taskId);
    if (!task) return;

    const isArchived  = task.status === 'archived';
    const newStatus   = isArchived ? 'pending' : 'archived';
    const actionLabel = isArchived ? '{{ __("admin.reopen_task") }}' : '{{ __("admin.archive_task") }}';
    const bodyText    = isArchived ? '{{ __("admin.reopen_task_confirm") }}' : '{{ __("admin.archive_task_confirm") }}';

    releaseWarningAlert({
      title: actionLabel + '?',
      message: bodyText,
      confirmText: '{{ __("admin.DYes") }}',
      cancelText: '{{ __("admin.DNo") }}',
      confirmColor: isArchived ? 'warning' : 'secondary',
      onConfirm: () => {
        $.ajax({
          url: '{{ route("tasks.move", ["taskId" => ":id", "status" => ":status"]) }}'
            .replace(':id', taskId)
            .replace(':status', newStatus),
          type: 'PATCH',
          contentType: 'application/json',
          data: JSON.stringify({ status: newStatus }),
          success: function () {
            const oldStatus = task.status;
            task.status = newStatus;

            if (newStatus === 'archived') {
              // Remove card from board entirely
              $(`.kanban-item[data-task-id="${taskId}"]`).fadeOut(300, function () {
                $(this).remove();
                const $board = $(`.kanban-board[data-board="${oldStatus}"] .kanban-drag`);
                if ($board.find('.kanban-item:not(.d-none)').length === 0) {
                  $board.append('<p class="text-muted small text-center py-3 m-0" data-empty-board>{{ __("admin.No_Tasks") }}</p>');
                }
                $('.kanban-board').each(function () {
                  $(this).find('.board-count').text($(this).find('.kanban-item:not(.d-none)').length);
                });
              });
            } else {
              syncTaskToBoard(task, oldStatus);
            }

            toastr.success(isArchived ? '{{ __("admin.task_reopened") }}' : '{{ __("admin.task_archived") }}');
          },
          error: function () {
            toastr.error('{{ __("admin.update_failed") }}');
          }
        });
      }
    });
  });

  // --- EDIT TASK ---
  $(document).on('click', '.edit-task-btn', function () {
    const taskId = $(this).data('task-id');
    const taskData = tasks.find(task => task.id === taskId);
    if(!taskData) return;

    $('#et-id').val(taskData.id);
    $('#et-title').val(taskData.title);
    $('#et-due-date').val(taskData.due_date);
    etDescQ.root.innerHTML = taskData.description;
    $('#et-priority').val(taskData.priority).trigger('change');
    $('#et-status').val(taskData.status).trigger('change');
    
    $('#et-attachments').val('').removeClass('is-invalid');
    $('#et-file-error').text('');

    if (taskData.attachments && taskData.attachments.length > 0) {
      $('#et-current-attachments-wrapper').removeClass('d-none');
      renderAttachmentsList(taskData.attachments, taskId, '#et-current-attachments-list');
    } else {
      $('#et-current-attachments-wrapper').addClass('d-none');
    }

    bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('editTaskOffcanvas')).show();
  });

  $('#editTaskForm').on('submit', function (e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#et-submit-btn');

    etFv.validate().then(function (status) {
      if (status !== 'Valid') return;
      if (!validateFiles($('#et-attachments'), '#et-file-error')) return;

      const taskId = $('#et-id').val();
      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>{{ __("admin.processing") }}');

      let formData = new FormData($form[0]);
      formData.append('description', etDescQ.root.innerHTML);

      $.ajax({
        url: '{{ route("tasks.update", ":id") }}'.replace(':id', taskId),
        type: 'POST', // Sent as POST because of _method=PUT hidden field
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) { 
          toastr.success('{{ __("admin.StoredSuccessfully") }}');

          const idx = tasks.findIndex(t => t.id == taskId);
          if (idx !== -1) {
            const oldStatus = tasks[idx].status;
            const updatedTask = response.task || {
              ...tasks[idx],
              title: $('#et-title').val(),
              description: etDescQ.root.innerHTML,
              due_date: $('#et-due-date').val(),
              priority: $('#et-priority').val(),
              status: $('#et-status').val(),
              attachments: response.attachments || tasks[idx].attachments,
            };
            tasks[idx] = updatedTask;
            syncTaskToBoard(updatedTask, oldStatus);
          }

          $('#editTaskOffcanvas').offcanvas('hide');
          $btn.prop('disabled', false).html('{{ __("admin.Update") }}');
        },
        error: function() {
          toastr.error('{{ __("admin.update_failed") }}');
          $btn.prop('disabled', false).text('{{ __("admin.Update") }}');
        }
      });
    });
  });

  // --- LIVE SEARCH ---
  $('#kanbanSearch').on('input', function () {
    const q = $(this).val().trim().toLowerCase();
    $('.kanban-item').each(function () {
      const show = $(this).data('search-text').includes(q);
      $(this).toggleClass('d-none', !show);
    });
    // Update badge counts after filtering
    $('.kanban-board').each(function () {
      $(this).find('.board-count').text($(this).find('.kanban-item:not(.d-none)').length);
    });
  });

});
</script>
@stop