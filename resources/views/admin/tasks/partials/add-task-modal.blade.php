<!-- Modal to add new task -->
<div
  class="modal fade"
  id="addTaskModal"
  tabindex="-1"
  aria-labelledby="addTaskModalLabel"
  aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addTaskModalLabel">{{ __('admin.add_new_task') }}</h5>
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form class="add-new-task pt-0" id="addNewTaskForm">
          <div class="row mb-2">
            <div class="col-12 mb-4">
              <label class="form-label" for="task-name">
                {{ __('admin.Title') }}
                <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                class="form-control"
                id="task-title"
                placeholder="{{ __('admin.task_title_placeholder') }}"
                name="taskTitle" />
            </div>
            <div class="col-12 mb-4">
              <label class="form-label" for="task-description">{{ __('admin.Description') }}</label>
              <textarea
                class="form-control"
                id="task-description"
                name="taskDescription"
                rows="3"
                placeholder="{{ __('admin.task_description_placeholder') }}"></textarea>
            </div>
            <div class="col-sm-4 col-12 mb-4">
              <label class="form-label" for="task-status">
                {{ __('admin.Status') }}
                <span class="text-danger">*</span>
              </label>
              <select id="task-status" class="form-select select2" name="taskStatus" data-placeholder="{{ __('admin.Select Status') }}">
                <option value="">{{ __('admin.Select Status') }}</option>
                <option value="pending">{{ __('admin.boardTitlePending') }}</option>
                <option value="progress">{{ __('admin.boardTitleProgress') }}</option>
                <option value="review">{{ __('admin.boardTitleReview') }}</option>
                <option value="completed">{{ __('admin.boardTitleCompleted') }}</option>
              </select>
            </div>
            <div class="col-sm-4 col-12 mb-4">
              <label class="form-label" for="task-priority">
                {{ __('admin.Priority') }}
                <span class="text-danger">*</span>
              </label>
              <select id="task-priority" class="form-select select2" name="taskPriority" data-placeholder="{{ __('admin.select_priority') }}">
                <option value="">{{ __('admin.select_priority') }}</option>
                <option value="Low">{{ __('admin.priorityLow') }}</option>
                <option value="Medium">{{ __('admin.priorityMedium') }}</option>
                <option value="High">{{ __('admin.priorityHigh') }}</option>
              </select>
            </div>
            <div class="col-sm-4 col-12 mb-4">
              <label class="form-label" for="task-due-date">{{ __('admin.Due_Date') }}</label>
              <input
                type="text"
                class="form-control flatpickr-validation"
                id="task-due-date"
                name="taskDueDate" 
                placeholder="{{ __('admin.Due_Date') }}"
              />
            </div>
            <div class="col-12 mb-4">
              <label class="form-label" for="task-program">
                {{ __('admin.program') }}
                <span class="text-danger">*</span>
              </label>
              <select id="task-program" class="select2 form-select" name="taskProgram" data-placeholder="{{ __('admin.Select Program') }}">
                <option value="">{{ __('admin.Select Program') }}</option>
                @foreach ($programs as $program)
                  <option value="{{ $program->id }}">{{ $program->title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 mb-4" id="assignees-container" style="display: none;">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label" for="task-assignees">
                  {{ __('admin.assignees') }} <span class="text-danger">*</span>
                </label>
                <a href="javascript:void(0);" class="select-all-toggle small fw-medium">{{ __('admin.select_all') }}</a>
              </div>
              <select
                class="select2 form-select"
                id="task-assignees"
                name="taskAssignees[]"
                multiple
                data-placeholder="{{ __('admin.select_assignees') }}"
              >
                @foreach ($startups as $startup)
                  <option value="{{ $startup['id'] }}">{{ $startup['name'] }}</option>
                @endforeach
              </select>
            </div>
            {{-- <div class="col-12 mb-4">
              <label class="form-label" for="task-tags">{{ __('admin.Tags') }}</label>
              <input
                type="text"
                class="form-control"
                id="task-tags"
                name="taskTags"
                placeholder="{{ __('admin.Select_Tags') }}"
                data-whitelist='@json($tags->pluck('name'))'
              />
            </div> --}}
            <div class="col-12 mb-4">
              <label class="form-label mb-2">{{ __('admin.attachments')}}</label>
              <div class="border bg-light-subtle rounded p-3 text-center cursor-pointer"
                onclick="document.getElementById('task-upload').click()">
                <i class="ti ti-cloud-upload mb-2"></i>
                <div class="fw-medium">{{ __('admin.click_to_upload') }}</div>
                <div class="small text-muted mt-1">{{ __('admin.all_files_allowed') }} up to 10MB</div>
              </div>
              <input type="file" name="attachments[]" id="task-upload" class="d-none" multiple>
              <div id="fileError" class="invalid-feedback mt-2" style="display: none;"></div>
              <div id="fileName" class="small mt-2" style="display: none;"></div>
            </div>
          </div>
          <div class="row">
            <div class="col-12 text-end">
              <button type="button" class="btn btn-primary me-3 data-submit">{{ __('admin.submit') }}</button>
              <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Modal to add new task -->