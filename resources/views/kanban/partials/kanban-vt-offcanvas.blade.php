{{-- VIEW TASK OFFCANVAS --}}
<div class="offcanvas offcanvas-end w-px-{{ $_user->ableTo('tasks.update', $permissions) ? '700' : '600' }}" tabindex="-1" id="viewTaskOffcanvas">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title"><i class="ti ti-eye me-1 text-primary"></i> {{ __('admin.View_Task') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body pt-0 d-flex flex-column">
    
    {{-- TAB NAVIGATION GENERATED DYNAMICALLY --}}
    <ul class="nav nav-tabs pb-6 mb-4 rounded-0 mt-3" role="tablist">
      @foreach($offcanvasTabs as $tab)
        @if($tab['show'])
          <li class="nav-item">
            <button class="nav-link {{ $tab['active'] ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="{{ $tab['target'] }}" role="tab">
              <i class="ti {{ $tab['icon'] }} ti-xs me-1"></i> {{ $tab['label'] }}
              @if(isset($tab['badge']))
                <span class="badge badge-center {{ $tab['badge']['class'] }} rounded-pill ms-1" id="{{ $tab['badge']['id'] }}">0</span>
              @endif
            </button>
          </li>
        @endif
      @endforeach
    </ul>

    <div class="tab-content p-0 flex-grow-1 overflow-auto">
      {{-- DETAILS TAB --}}
      <div class="tab-pane fade show active" id="vt-details" role="tabpanel">
        <div class="mb-3">
          <span class="text-muted d-block mb-1">{{ __('admin.Title') }}</span>
          <h6 id="vt-title" class="mb-0 text-dark"></h6>
        </div>
        <div class="mb-3">
          <span class="text-muted d-block mb-1">{{ __('admin.Description') }}</span>
          <div id="vt-description" class="text-dark bg-body border rounded p-3" style="min-height: 80px;"></div>
        </div>
        <div class="row g-3 mb-4">
          <div class="col-6">
            <span class="text-muted d-block mb-1">{{ __('admin.Due_Date') }}</span>
            <p id="vt-due-date" class="mb-0 text-dark"></p>
          </div>
          <div class="col-6">
            <span class="text-muted d-block mb-1">{{ __('admin.Priority') }}</span>
            <div id="vt-priority"></div>
          </div>
          <div class="col-6">
            <span class="text-muted d-block mb-1">{{ __('admin.Status') }}</span>
            <div id="vt-status"></div>
          </div>
        </div>

        {{--
        @if ($_user->hasAnyRole('admin|subadmin|organizationManager'))
        <div class="mb-4 d-none" id="vt-assignee-wrapper">
          <span class="text-muted small d-block mb-2">{{ __('admin.Assignee') ?? 'Assignee' }}</span>
          <div class="border rounded p-3 bg-body d-flex flex-column gap-3">
            <div class="d-flex align-items-center gap-3" id="vt-assignee-startup">
              <div id="vt-assignee-startup-avatar"></div>
              <div>
                <span class="small">{{ __('admin.Startup') ?? 'Startup' }}</span>
                <div id="vt-assignee-startup-name" class="fw-semibold text-dark"></div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3 border-top pt-3" id="vt-assignee-founder">
              <div id="vt-assignee-founder-avatar"></div>
              <div>
                <span class="small">{{ __('admin.Founder') ?? 'Founder' }}</span>
                <div id="vt-assignee-founder-name" class="fw-semibold text-dark"></div>
              </div>
            </div>
          </div>
        </div>
        @endif
        --}}
        <div class="mb-4 d-none" id="vt-assignee-wrapper">
          <span class="text-muted small d-block mb-2">{{ __('admin.assignees') }}</span>
          <div class="border rounded p-3 bg-body d-flex flex-column gap-3" id="vt-assignees-list"></div>
        </div>

        <div class="mb-4">
          <span class="text-muted small d-block mb-1">{{ $_user->ableTo('tasks.update', $permissions) ? __('admin.provided_resources') : __('admin.Attachments') }}</span>
          <div id="vt-attachments-list" class="d-flex flex-column gap-2 small mb-3"></div>

          {{-- Dropzone Upload --}}
          <div class="border-top pt-3">
            <label class="form-label small mb-2">
              {{ $_user->ableTo('tasks.update', $permissions) ? __('admin.upload_attachments') : __('admin.your_submission') }}
              <span class="text-muted">({{ __('admin.max') }} 5 {{ __('admin.files') ?? 'files' }}, 10MB {{ __('admin.each') }})</span>
            </label>

            <div id="vt-dropzone"
              class="bg-light-subtle border border-dashed rounded p-4 text-center position-relative"
              style="cursor:pointer; border-color: var(--bs-border-color) !important; transition: border-color .2s, background .2s;">
              <input type="file" id="vt-dropzone-input" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor:pointer;"
                accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/zip,application/x-zip-compressed,text/plain,text/csv"
                multiple />
              <i class="ti ti-cloud-upload ti-lg text-primary mb-2 d-block"></i>
              <p class="mb-0 small">{{ __('admin.dropzone') }}</p>
              <p class="mb-0 text-muted small">{{ __('admin.max_5_files') }}</p>
            </div>

            <div id="vt-dropzone-queue" class="d-flex flex-column gap-2 mt-2"></div>

            <div class="d-flex justify-content-between align-items-center mt-2">
              <div class="invalid-feedback d-block" id="vt-upload-error"></div>
              <button type="button" class="btn btn-sm btn-primary text-nowrap ms-auto d-none" id="vt-dropzone-upload-btn">
                <i class="ti ti-upload ti-xs me-1"></i>{{ __('admin.upload_all') }}
              </button>
            </div>
            <input type="hidden" id="vt-upload-btn-task-id" value="" />
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

        @if ($_user->ableTo('tasks.add_feedback', $permissions))
        <div class="mt-3" id="vt-feedback-form-wrapper">
          <h5 class="fw-semibold small mb-3">{{ __('admin.add_feedback') }}</h5>

          <div class="mb-3">
            <label class="form-label small">{{ __('admin.feedback_decision') }} <span class="text-danger">*</span></label>
            <div class="row g-3">
              <div class="col-6">
                <div class="form-check custom-option custom-option-basic checked" id="vt-fb-pass-wrapper">
                  <label class="form-check-label custom-option-content" for="vt-fb-pass">
                    <input name="vt_feedback_decision" class="form-check-input" type="radio" value="pass" id="vt-fb-pass" checked>
                    <span class="custom-option-header"><span class="h6 mb-0">{{ __('admin.pass_task') }}</span></span>
                    <span class="custom-option-body"><small>{{ __('admin.mark_as_completed') }}</small></span>
                  </label>
                </div>
              </div>
              <div class="col-6">
                <div class="form-check custom-option custom-option-basic" id="vt-fb-revision-wrapper">
                  <label class="form-check-label custom-option-content" for="vt-fb-revision">
                    <input name="vt_feedback_decision" class="form-check-input" type="radio" value="revision" id="vt-fb-revision">
                    <span class="custom-option-header"><span class="h6 mb-0">{{ __('admin.needs_revision') }}</span></span>
                    <span class="custom-option-body"><small>{{ __('admin.request_changes') }}</small></span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small" for="vt-fb-note">{{ __('admin.feedback_note') }}</label>
            <textarea id="vt-fb-note" class="form-control" rows="3" placeholder="{{ __('admin.feedback_note_placeholder') }}"></textarea>
          </div>

          <button type="button" class="btn btn-primary" id="vt-submit-feedback-btn">
            <i class="ti ti-send ti-xs me-1"></i> {{ __('admin.submit_feedback') }}
          </button>
        </div>
        @endif
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
      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">{{ __('admin.Close') }}</button>
    </div>
  </div>
</div>