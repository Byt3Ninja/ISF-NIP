@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/form-validation/form-validation.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
@stop

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

  #vt-dropzone.dz-dragover,
  #add-dropzone.dz-dragover,
  #et-dropzone.dz-dragover {
    border-color: var(--bs-primary) !important;
    background-color: rgba(var(--bs-primary-rgb), .05);
  }
  #vt-dropzone-queue .dz-queue-item .dz-progress-bar {
    height: 3px;
    border-radius: 2px;
    background: var(--bs-primary);
    transition: width .3s;
  }
</style>
@stop

@section('content')
  <div>
    @include('kanban.partials.kanban-toolbar')
    @include('kanban.partials.kanban-boards')
    @include('kanban.partials.kanban-vt-offcanvas')
    @include('kanban.partials.kanban-at-offcanvas')
    @include('kanban.partials.kanban-et-offcanvas')
    @include('kanban.partials.kanban-archived-offcanvas')
  </div>
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/sortablejs/sortable.js"></script>
<script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
<script src="/js/kanban/kanban-utils.js?v={{ filemtime(public_path('js/kanban/kanban-utils.js')) }}"></script>
<script src="/js/kanban/kanban-renderers.js?v={{ filemtime(public_path('js/kanban/kanban-renderers.js')) }}"></script>
<script src="/js/kanban/kanban-board.js?v={{ filemtime(public_path('js/kanban/kanban-board.js')) }}"></script>
<script src="/js/kanban/kanban-dropzone.js?v={{ filemtime(public_path('js/kanban/kanban-dropzone.js')) }}"></script>
<script src="/js/kanban/kanban-tasks.js?v={{ filemtime(public_path('js/kanban/kanban-tasks.js')) }}"></script>
@stop

@section('pagescripts')
<script>
(function ($) {
  'use strict';

  // ─── Server-side config injected into JS ────────────────────────────────
  window.KanbanConfig = {
    isAbleToEdit:    @json($_user->ableTo('tasks.store', $permissions)),
    isAbleToDeleteAttachment:  @json($_user->ableTo('tasks.delete_attachment', $permissions)),
    isAdmin:         @json(auth()->user()->hasRole('Admin')),
    isSubAdmin:         @json(auth()->user()->hasRole('subadmin')),
    isOrgManager:         @json(auth()->user()->hasRole('OrganizationManager')),
    currentUserName: @json(auth()->user()->name ?? 'You'),
    profileBaseUrl:  @json(url("/dashboard/show/UserProfile")),

    priorityMap: @json($jsPriorityMap),
    statusMap:   @json($jsStatusMap),

    routes: {
      storeTask:        '{{ route("tasks.store") }}',
      updateTask:       '{{ route("tasks.update", ":id") }}',
      moveTask:         '{{ route("tasks.move", ["taskId" => ":id", "status" => ":status"]) }}',
      uploadAttachment: '{{ route("tasks.upload_attachment", ":taskId") }}',
      deleteAttachment: '{{ route("tasks.delete_attachment", ["taskId" => ":taskId", "filename" => ":filename"]) }}',
      addComment:       '{{ route("tasks.add_comment", ":id") }}',
      addFeedback:      '{{ route("tasks.add_feedback", ":id") }}',
    },

    roleMap: {
      admin:               @json(__('admin.Admin')),
      'sub-admin':         @json(__('admin.Sub-Admin')),
      subadmin:            @json(__('admin.Sub-Admin')),
      mentor:              @json(__('admin.Mentor')),
      manager:             @json(__('admin.Manager')),
      organizationmanager: @json(__('admin.organizationManager')),
      editor:              @json(__('admin.Editor')),
      judger:              @json(__('admin.Judger')),
      startup:             @json(__('admin.Startup')),
    },

    labels: {
      // General
      processing:         @json(__('admin.processing')),
      storedSuccessfully: @json(__('admin.StoredSuccessfully')),
      saveFailed:         @json(__('admin.save_failed')),
      updateFailed:       @json(__('admin.update_failed')),
      deleteFailed:       @json(__('admin.delete_failed')),
      requiredField:      @json(__('admin.Required_Field')),
      noTasks:            @json(__('admin.No_Tasks')),
      you:                @json(__('admin.you')),
      justNow:            @json(__('admin.just_now')),
      // Buttons
      view:               @json(__('admin.View')),
      edit:               @json(__('admin.Edit')),
      create:             @json(__('admin.Create')),
      update:             @json(__('admin.Update')),
      send:               @json(__('admin.send')),
      remove:             @json(__('admin.Remove')),
      download:           @json(__('admin.Download')),
      yes:                @json(__('admin.archive_task')),
      no:                 @json(__('admin.DNo')),
      // Tasks
      reporter:           @json(__('admin.Reporter')),
      archiveTask:        @json(__('admin.archive_task')),
      archiveTaskConfirm: @json(__('admin.archive_task_confirm')),
      reopenTask:         @json(__('admin.reopen_task')),
      reopenTaskConfirm:  @json(__('admin.reopen_task_confirm')),
      taskArchived:       @json(__('admin.task_archived')),
      taskReopened:       @json(__('admin.task_reopened')),
      moveTaskFailed:     @json(__('admin.move_task_failed')),
      noDescription:      @json(__('admin.no_description')),
      // Attachments
      noAttachments:            @json(__('admin.no_attachments')),
      deleteAttachment:         @json(__('admin.delete_attachment')),
      recover:                  @json(__('admin.Recover')),
      attachmentDeletedSuccess: @json(__('admin.attachment_deleted_success')),
      attachmentUploadedSuccess:@json(__('admin.attachment_uploaded_success')),
      uploading:                @json(__('admin.uploading')),
      uploadAll:                @json(__('admin.upload_all')),
      uploaded:                 @json(__('admin.uploaded')),
      failed:                   @json(__('admin.Failed')),
      maxFilesReached:          @json(__('admin.max_5_files_reached')),
      invalidFileType:          @json(__('admin.Invalid_task_file_type')),
      exceedsSizeLimit:         @json(__('admin.file_exceeds_10_limit')),
      uploadFailedRetry:        @json(__('admin.upload_failed_retry')),
      // Comments
      noComments:     @json(__('admin.no_comments')),
      commentSuccess: @json(__('admin.comment_success')),
      commentFailed:  @json(__('admin.comment_failed')),
      requiredField:  @json(__('admin.Required_Field')),
      // Feedback
      noFeedback:     @json(__('admin.no_feedback')),
      feedbackSuccess:@json(__('admin.feedback_success')),
      feedbackFailed: @json(__('admin.feedback_failed')),
      submitFeedback: @json(__('admin.submit_feedback')),
      passTask:       @json(__('admin.pass_task')),
      needsRevision:  @json(__('admin.needs_revision')),
      // Submissions
      noSubmissions:             @json(__('admin.no_submissions')),
      noNotesAvailable:          @json(__('admin.no_notes_available')),
      noSubmittedFilesAvailable: @json(__('admin.no_submitted_files_available')),
      pendingReview:             @json(__('admin.pending_review')),
      approved:       @json(__('admin.Approved')),
      rejected:       @json(__('admin.Rejected')),
      // Activities
      noActivities:   @json(__('admin.no_recent_activities')),
      send_submission: @json(__('admin.send_submission')),
      submissionNote: @json(__('admin.submission_note')),
      submittedFiles: @json(__('admin.submittedFiles')),
      submission: @json(__('admin.submission')),
    },
  };

  // ─── Bootstrap ──────────────────────────────────────────────────────────
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  let tasks     = @json($tasks);
  const program = @json($cycle);

  // Plugins
  const addDescQ   = new Quill('#add-desc-editor',   { theme: 'snow', modules: { toolbar: '#add-desc-toolbar' } });
  const etDescQ    = new Quill('#et-desc-editor',    { theme: 'snow', modules: { toolbar: '#et-desc-toolbar' } });
  const vtCommentQ = document.getElementById('vt-comment-editor')
    ? new Quill('#vt-comment-editor', { theme: 'snow', modules: { toolbar: '#vt-comment-toolbar' } })
    : null;

  $('#add-due-date, #et-due-date').flatpickr({ dateFormat: 'Y-m-d' });
  $('#add-priority, #add-status').select2({ dropdownParent: $('#addTaskOffcanvas') });
  $('#add-assignees').select2({ dropdownParent: $('#addTaskOffcanvas'), placeholder: @json(__('admin.select_assignees')) });
  $('#et-priority, #et-status').select2({ dropdownParent: $('#editTaskOffcanvas') });
  $('#et-assignees').select2({ dropdownParent: $('#editTaskOffcanvas'), placeholder: @json(__('admin.select_assignees')) });

  // Form validation
  const fvConfig = {
    fields: {
      title:    { validators: { notEmpty: { message: @json(__('admin.Required_Field')) } } },
      priority: { validators: { notEmpty: { message: @json(__('admin.Required_Field')) } } },
      status:   { validators: { notEmpty: { message: @json(__('admin.Required_Field')) } } },
    },
    plugins: {
      trigger:    new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({ rowSelector: '.mb-3, .col-6' }),
    },
  };
  const addFv = FormValidation.formValidation(document.getElementById('addTaskForm'), fvConfig);
  const etFv  = FormValidation.formValidation(document.getElementById('editTaskForm'), fvConfig);

  // Bind change event listeners compactly
  ['add', 'et'].forEach(prefix => {
    [`#${prefix}-priority`, `#${prefix}-status`].forEach(sel => {
      const field = sel.split('-')[1];
      const fv = prefix === 'add' ? addFv : etFv;
      $(sel).on('change', () => fv.revalidateField(field));
    });
    
    $(`#${prefix}-attachments`).on('change', function () {
      validateFiles($(this), `#${prefix}-file-error`);
    });
  });

  // Init modules
  initKanbanDragDrop(tasks);
  initKanbanSearch();
  initDropzone(tasks);
  initKanbanTasks(tasks, program, addDescQ, etDescQ, vtCommentQ, addFv, etFv);
})(jQuery);
</script>
@stop
