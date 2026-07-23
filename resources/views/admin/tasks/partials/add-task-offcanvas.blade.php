{{-- ═══════════════════════════════════════════
     Offcanvas — Add / Edit Task
═══════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-end w-px-500" tabindex="-1" id="addTaskModal" aria-labelledby="addTaskModalLabel">

  {{-- Header --}}
  <div class="offcanvas-header border-bottom py-3">
    <div class="d-flex align-items-center gap-2">
      <div class="avatar avatar-sm flex-shrink-0">
        <div class="avatar-initial rounded-circle bg-label-primary">
          <i class="ti ti-clipboard-text ti-sm"></i>
        </div>
      </div>
      <h5 class="offcanvas-title mb-0" id="addTaskModalLabel">{{ __('admin.add_new_task') }}</h5>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  {{-- Body --}}
  <div class="offcanvas-body d-flex flex-column p-0">
    <form class="add-new-task d-flex flex-column flex-grow-1" id="addNewTaskForm">
      <div class="flex-grow-1 p-4">

        {{-- Title --}}
        <div class="mb-4">
          <label class="form-label fw-semibold" for="task-title">
            {{ __('admin.Title') }} <span class="text-danger">*</span>
          </label>
          <input
            type="text"
            class="form-control"
            id="task-title"
            name="taskTitle"
            placeholder="{{ __('admin.task_title_placeholder') }}" />
        </div>

        {{-- Description --}}
        <div class="mb-4">
          <label class="form-label fw-semibold" for="task-description">{{ __('admin.Description') }}</label>
          <textarea
            class="form-control"
            id="task-description"
            name="taskDescription"
            rows="3"
            placeholder="{{ __('admin.task_description_placeholder') }}"></textarea>
        </div>

        {{-- Divider --}}
        <hr class="my-4">

        {{-- Status / Priority / Due Date --}}
        <div class="row g-3 mb-4">
          <div class="col-12">
            <p class="text-uppercase small fw-bold text-muted mb-3">{{ __('admin.task_settings') }}</p>
          </div>
          <div class="col-sm-4 col-12">
            <label class="form-label fw-semibold" for="task-status">
              {{ __('admin.Status') }} <span class="text-danger">*</span>
            </label>
            <select id="task-status" class="form-select select2" name="taskStatus" data-placeholder="{{ __('admin.Select Status') }}">
              <option value="">{{ __('admin.Select Status') }}</option>
              <option value="pending">{{ __('admin.boardTitlePending') }}</option>
              <option value="progress">{{ __('admin.boardTitleProgress') }}</option>
              <option value="review">{{ __('admin.boardTitleReview') }}</option>
              <option value="completed">{{ __('admin.boardTitleCompleted') }}</option>
            </select>
          </div>
          <div class="col-sm-4 col-12">
            <label class="form-label fw-semibold" for="task-priority">
              {{ __('admin.Priority') }} <span class="text-danger">*</span>
            </label>
            <select id="task-priority" class="form-select select2" name="taskPriority" data-placeholder="{{ __('admin.select_priority') }}">
              <option value="">{{ __('admin.select_priority') }}</option>
              <option value="Low">{{ __('admin.priorityLow') }}</option>
              <option value="Medium">{{ __('admin.priorityMedium') }}</option>
              <option value="High">{{ __('admin.priorityHigh') }}</option>
            </select>
          </div>
          <div class="col-sm-4 col-12">
            <label class="form-label fw-semibold" for="task-due-date">{{ __('admin.Due_Date') }}</label>
            <input
              type="text"
              class="form-control flatpickr-validation"
              id="task-due-date"
              name="taskDueDate"
              placeholder="{{ __('admin.Due_Date') }}" />
          </div>
        </div>

        {{-- Divider --}}
        <hr class="my-4">

        {{-- Program & Assignees --}}
        @if (isset($programs) && count($programs) > 0)
          <div class="mb-4">
            <p class="text-uppercase small fw-bold text-muted mb-3">{{ __('admin.assignment') }}</p>
            <div class="mb-4">
              <label class="form-label fw-semibold" for="task-program">
                {{ __('admin.program') }} <span class="text-danger">*</span>
              </label>
              <select id="task-program" class="select2 form-select" name="taskProgram" data-placeholder="{{ __('admin.Select Program') }}">
                <option value="">{{ __('admin.Select Program') }}</option>
                @foreach ($programs as $program)
                  <option value="{{ $program?->id }}">{{ $program?->title }}</option>
                @endforeach
              </select>
            </div>
            <div id="assignees-container">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label fw-semibold mb-0" for="task-assignees">
                  {{ __('admin.assignees') }} <span class="text-danger">*</span>
                </label>
                <a href="javascript:void(0);" class="select-all-toggle small fw-medium text-primary">{{ __('admin.select_all') }}</a>
              </div>
              <select
                class="select2 form-select"
                id="task-assignees"
                name="taskAssignees[]"
                multiple
                data-placeholder="{{ __('admin.select_assignees') }}">
                @foreach ($startups as $startup)
                  <option value="{{ $startup['id'] }}">{{ $startup['name'] }}</option>
                @endforeach
              </select>
            </div>
          </div>
        @endif

        {{-- Divider --}}
        <hr class="my-4">

        {{-- Attachments --}}
        <div class="mb-2">
          <p class="text-uppercase small fw-bold text-muted mb-3">{{ __('admin.attachments') }}</p>

          {{-- Existing attachments (edit mode only) --}}
          <div id="existing-attachments" class="mb-3" style="display:none;">
            <p class="small text-muted mb-2">{{ __('admin.existing_attachments') }}</p>
            <div id="existing-attachments-list" class="d-flex flex-wrap gap-2"></div>
          </div>

          <div
            class="border border-dashed rounded-3 px-4 py-3 text-center cursor-pointer bg-light-subtle"
            onclick="document.getElementById('task-upload').click()"
            style="border-style: dashed !important;">
            <i class="ti ti-cloud-upload fs-3 text-primary mb-2 d-block"></i>
            <p class="fw-semibold mb-1">{{ __('admin.click_to_upload') }}</p>
            <p class="small text-muted mb-0">{{ __('admin.all_files_allowed') }} &mdash; max 10 MB</p>
          </div>
          <input type="file" name="attachments[]" id="task-upload" class="d-none" multiple>
          <div id="fileError" class="invalid-feedback mt-2" style="display: none;"></div>
          <div id="fileName" class="small mt-2 text-muted" style="display: none;"></div>
        </div>

      </div>{{-- /flex-grow-1 --}}

      {{-- Sticky footer actions --}}
      <div class="offcanvas-footer border-top p-4 d-flex gap-3">
        <button type="button" class="btn btn-primary data-submit">
          {{ __('admin.submit') }}
        </button>
        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
          {{ __('admin.Cancel') }}
        </button>
      </div>

    </form>
  </div>

</div>
{{-- /Offcanvas Add / Edit Task --}}
