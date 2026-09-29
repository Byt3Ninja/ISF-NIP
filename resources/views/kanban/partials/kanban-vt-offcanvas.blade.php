{{-- VIEW TASK OFFCANVAS --}}
<div class="offcanvas offcanvas-end w-px-{{ $_user->ableTo('tasks.update', $permissions) ? '800' : '800' }}" tabindex="-1" id="viewTaskOffcanvas">

  {{-- Priority colour strip --}}
  <div id="vt-priority-bar" style="height:4px;flex-shrink:0;transition:background .3s;"></div>

  {{-- Slim top bar --}}
  <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom flex-shrink-0">
    <div class="d-flex align-items-center gap-2">
      <div class="avatar avatar-sm flex-shrink-0">
        <span class="avatar-initial rounded bg-label-primary">
          <i class="ti ti-clipboard-check ti-xs"></i>
        </span>
      </div>
      <span class="fw-semibold" style="font-size:.7rem;letter-spacing:.07em;text-transform:uppercase;color:var(--bs-secondary-color);">
        {{ __('admin.View_Task') }}
      </span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>

  {{-- Hero: title + meta chips (always visible, no scroll) --}}
  <div class="px-4 py-3 border-bottom flex-shrink-0 bg-light-subtle">

    {{-- Title --}}
    <h5 id="vt-title" class="fw-bold mb-3 lh-sm text-heading"></h5>

    {{-- Meta chips --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
      <span class="d-inline-flex align-items-center gap-1 badge bg-label-secondary rounded-pill fw-normal px-2" style="font-size:.75rem;">
        <i class="ti ti-calendar-event ti-xs"></i>
        <span id="vt-due-date"></span>
      </span>
      <div id="vt-priority"></div>
      <div id="vt-status"></div>
    </div>

    {{-- Assignees (chip row, hidden until populated) --}}
    {{-- <div class="d-none mt-2" id="vt-assignee-wrapper">
      <div class="d-flex align-items-center gap-2 flex-wrap" id="vt-assignees-list"></div>
    </div> --}}

  </div>

  <div class="offcanvas-body p-0 d-flex flex-column overflow-hidden">

    {{-- TAB NAVIGATION --}}
    <ul class="nav nav-tabs pb-0 flex-nowrap flex-shrink-0" role="tablist"
        style="min-height: 50px;overflow-x:auto;overflow-y:hidden;scrollbar-width:none;-ms-overflow-style:none;">
      @foreach($offcanvasTabs as $tab)
        @if($tab['show'])
          <li class="nav-item h-100 flex-shrink-0 mb-0">
            <button class="nav-link h-100 {{ $tab['active'] ? 'active' : '' }} d-flex align-items-center gap-1 text-nowrap"
                    data-bs-toggle="tab" data-bs-target="{{ $tab['target'] }}" role="tab">
              <i class="ti {{ $tab['icon'] }}"></i>
              <span>{{ $tab['label'] }}</span>
              @if(isset($tab['badge']))
                <span class="badge badge-center {{ $tab['badge']['class'] }} rounded-pill ms-1" id="{{ $tab['badge']['id'] }}">0</span>
              @endif
            </button>
          </li>
        @endif
        @if ($loop->first && !$_user->ableTo('tasks.update', $permissions))
          <li class="nav-item h-100 flex-shrink-0 mb-0">
            <button class="nav-link h-100 d-flex align-items-center gap-1 text-nowrap"
                    data-bs-toggle="tab" data-bs-target="#vt-your-submission" role="tab">
              <i class="ti ti-send"></i>
              <span>{{ __('admin.your_submission') }}</span>
            </button>
          </li>
        @endif
      @endforeach
    </ul>
    
    <hr class="m-0" />
    
    {{-- TAB CONTENT --}}
    <div class="tab-content p-0 flex-grow-1 overflow-auto" style="min-height:0;">

      {{-- DETAILS TAB --}}
      <div class="tab-pane p-4 fade show active" id="vt-details" role="tabpanel">
        <div class="p-2">

          {{-- Reporter / created_at / program --}}
          <div class="card border shadow-none mb-4" id="vt-meta-card">
            <div class="card-body p-0">
              <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <span class="small text-muted d-flex align-items-center gap-2">
                  <i class="ti ti-user text-info"></i>{{ __('admin.Reporter') }}
                </span>
                <div id="vt-reporter"></div>
              </div>
              <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <span class="small text-muted d-flex align-items-center gap-2">
                  <i class="ti ti-calendar ti-xs text-info"></i>{{ __('admin.Created_At') }}
                </span>
                <span id="vt-created-at" class="small fw-semibold text-body"></span>
              </div>
              <div class="d-flex align-items-center justify-content-between px-4 py-3">
                <span class="small text-muted d-flex align-items-center gap-2">
                  <i class="ti ti-rocket text-success"></i>{{ __('admin.Program') }}
                </span>
                <div id="vt-program"></div>
              </div>
            </div>
          </div>

          {{-- Description --}}
          <div class="mb-4">
            <p class="text-muted fw-medium mb-2 d-flex align-items-center gap-1" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">
              <i class="ti ti-align-left ti-xs"></i>{{ __('admin.Description') }}
            </p>
            <div id="vt-description"
                 class="border rounded-2 p-3 bg-light-subtle text-body"
                 style="min-height:80px;font-size:.875rem;line-height:1.6;word-break:break-word;"></div>
          </div>

          {{-- Attachments --}}
          <div>
            <p class="text-muted fw-medium mb-2 d-flex align-items-center gap-2" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">
              <span class="d-flex align-items-center gap-1">
                <i class="ti ti-paperclip ti-xs"></i>
                {{ __('admin.provided_resources') }}
              </span>
              <span id="vt-attachments-count" class="badge bg-label-secondary rounded-pill fw-normal"></span>
            </p>

            <div id="vt-attachments-list" class="d-flex flex-column gap-2 mb-3"></div>

            @if ($_user->ableTo('tasks.update', $permissions))
            {{-- ── Standard upload zone (admin / manager) ── --}}
            <div class="border rounded-2 p-3 bg-lighter">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small fw-medium text-body">{{ __('admin.upload_attachments') }}</span>
                <span class="text-muted" style="font-size:.7rem;">
                  {{ __('admin.max') }} 5 {{ __('admin.files') ?? 'files' }} · 5MB {{ __('admin.each') }}
                </span>
              </div>

              <div id="vt-dropzone"
                   class="border border-dashed rounded-2 p-3 text-center position-relative"
                   style="cursor:pointer;transition:border-color .2s,background .2s;">
                <input type="file" id="vt-dropzone-input"
                  class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor:pointer;"
                  accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/zip,application/x-zip-compressed,text/plain,text/csv"
                  multiple />
                <i class="ti ti-cloud-upload ti-md text-primary mb-1 d-block"></i>
                <p class="mb-1 small fw-medium">{{ __('admin.dropzone') }}</p>
                <p class="mb-0 text-muted" style="font-size:.7rem;">{{ __('admin.max_5_files') }}</p>
              </div>

              <div id="vt-dropzone-queue" class="d-flex flex-column gap-2 mt-2"></div>

              <div class="d-flex align-items-center justify-content-between mt-2">
                <div class="invalid-feedback d-block small" id="vt-upload-error"></div>
                <button type="button" class="btn btn-sm btn-primary text-nowrap ms-auto d-none" id="vt-dropzone-upload-btn">
                  <i class="ti ti-upload ti-xs me-1"></i>{{ __('admin.upload_all') }}
                </button>
              </div>
              <input type="hidden" id="vt-upload-btn-task-id" value="" />
              <input type="hidden" id="vt-fb-company-id" value="" />
            </div>
            @else
            <input type="hidden" id="vt-upload-btn-task-id" value="" />
            @endif
          </div>

        </div>
      </div>

      {{-- SUBMISSIONS TAB --}}
      <div class="tab-pane fade p-4" id="vt-submissions" role="tabpanel">
        <div class="p-4">
          <div id="vt-submissions-list"></div>
        </div>
      </div>

      {{-- ADD FEEDBACK TAB --}}
      <div class="tab-pane fade p-4" id="vt-add-feedback" role="tabpanel">
        <div class="p-4">
          @if ($_user->ableTo('tasks.add_feedback', $permissions))
            <div id="vt-feedback-status-message" class="alert alert-warning mb-0" role="alert" style="display:none;">
              <div class="d-flex align-items-center gap-2">
                <i class="ti ti-alert-triangle ti-xs flex-shrink-0"></i>
                <span>{{ __('admin.add_feedback_message') }}</span>
              </div>
            </div>
            <div id="vt-feedback-form-wrapper" style="display:none;">
              <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                <i class="ti ti-message-report ti-sm text-primary"></i>
                {{ __('admin.add_feedback') }}
              </h6>

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
                <label class="form-label small" for="vt-fb-note">{{ __('admin.feedback_note') }} *</label>
                <textarea id="vt-fb-note" class="form-control" rows="3"
                  placeholder="{{ __('admin.feedback_note_placeholder') }}" maxlength="1000" required></textarea>
                <div class="invalid-feedback">{{ __('admin.feedback_note_required') ?? 'Feedback note is required.' }}</div>
              </div>

              {{-- Upload attachments --}}
              <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <label class="form-label small mb-0">{{ __('admin.attachments') }}</label>
                  <span class="text-muted" style="font-size:.7rem;">{{ __('admin.max') }} 5 {{ __('admin.files') ?? 'files' }} · 5MB {{ __('admin.each') }}</span>
                </div>
                <div id="vt-fb-dropzone"
                     class="border border-dashed rounded-2 p-3 text-center position-relative"
                     style="cursor:pointer;transition:border-color .2s,background .2s;">
                  <input type="file" id="vt-fb-dropzone-input"
                    class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor:pointer;"
                    accept=".pdf,.png,.jpg,.jpeg,.svg,.zip,.rar,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                    multiple />
                  <i class="ti ti-cloud-upload ti-md text-primary mb-1 d-block"></i>
                  <p class="mb-1 small fw-medium">{{ __('admin.dropzone') }}</p>
                  <p class="mb-0 text-muted" style="font-size:.7rem;">{{ __('admin.max_5_files') }}</p>
                </div>
                <div id="vt-fb-dropzone-queue" class="d-flex flex-column gap-2 mt-2"></div>
                <div class="invalid-feedback d-block small" id="vt-fb-upload-error"></div>
              </div>

              {{-- Uploaded attachments list --}}
              <div id="vt-fb-attachments-wrapper" class="mb-3" style="display:none;">
                <label class="form-label small mb-2">{{ __('admin.uploaded_attachments') }}</label>
                <div id="vt-fb-attachments-list" class="d-flex flex-column gap-2"></div>
              </div>

              <button type="button" class="btn btn-primary btn-sm" id="vt-submit-feedback-btn">
                <i class="ti ti-send ti-xs me-1"></i>{{ __('admin.submit_feedback') }}
              </button>
            </div>
          @endif
        </div>
      </div>

      {{-- FEEDBACK HISTORY TAB --}}
      <div class="tab-pane fade p-4" id="vt-feedback-history" role="tabpanel">
        <div class="p-4">
          <div id="vt-feedback-list">
            <p class="text-muted text-center py-4 mb-0">{{ __('admin.no_feedback') }}</p>
          </div>
        </div>
      </div>

      {{-- YOUR SUBMISSION TAB --}}
      @if (!$_user->ableTo('tasks.update', $permissions))
        <div class="tab-pane fade p-4" id="vt-your-submission" role="tabpanel">
          <div class="rounded-2 overflow-hidden" style="border:2px solid var(--bs-primary);">

            <div class="px-3 py-2 d-flex align-items-center justify-content-between bg-label-primary">
              <span class="small fw-semibold text-primary d-flex align-items-center gap-1">
                <i class="ti ti-send ti-xs"></i>
                {{ __('admin.your_submission') }}
              </span>
              <span class="small text-dark">
                {{ __('admin.max') }} 5 {{ __('admin.files') ?? 'files' }} · 5MB {{ __('admin.each') }}
              </span>
            </div>

            <div class="p-3">
              <div class="mb-3" id="vt-submission-note-wrapper">
                <label class="form-label small fw-medium" for="vt-submission-note">{{ __('admin.submission_note_label') }}</label>
                <textarea id="vt-submission-note" class="form-control form-control-sm" rows="3"
                  placeholder="{{ __('admin.submission_note_placeholder') }}"></textarea>
              </div>

              <div id="vt-dropzone"
                   class="border border-dashed rounded-2 p-3 text-center position-relative"
                   style="cursor:pointer;transition:border-color .2s,background .2s;">
                <input type="file" id="vt-dropzone-input"
                  class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor:pointer;"
                  accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/zip,application/x-zip-compressed,.rar,application/x-rar-compressed,application/x-compressed,application/octet-stream,application/vnd.rar,text/plain"
                  multiple />
                <i class="ti ti-cloud-upload ti-md text-primary mb-1 d-block"></i>
                <p class="mb-1 small fw-medium">{{ __('admin.dropzone') }}</p>
                <p class="mb-0 text-muted" style="font-size:.7rem;">{{ __('admin.supported_files_message') }}</p>
              </div>

              <div id="vt-dropzone-queue" class="d-flex flex-column gap-2 mt-2"></div>

              <div class="d-flex align-items-center justify-content-between mt-3">
                <div class="invalid-feedback d-block small" id="vt-upload-error"></div>
                <button type="button" class="btn btn-sm btn-primary text-nowrap px-8 ms-auto d-flex align-items-center gap-1"
                        id="vt-dropzone-upload-btn" data-always-show="true">
                  <i class="ti ti-send ti-xs"></i>
                  {{ __('admin.send_submission') }}
                </button>
              </div>
            </div>

          </div>
        </div>
      @endif

      {{-- COMMENTS TAB (hidden) --}}
      {{-- <div class="tab-pane fade p-4" id="vt-comments" role="tabpanel">
        <div class="p-4 d-flex flex-column" style="min-height:100%;">
          <div id="vt-comments-list" class="mb-4"></div>

          <div class="border rounded-2 mt-auto">
            <div class="px-3 py-2 border-bottom bg-lighter rounded-top-2">
              <span class="small fw-medium text-body">{{ __('admin.Add comment') }}</span>
            </div>
            <div id="vt-comment-editor" style="min-height:80px;"></div>
            <div class="border-top px-2 py-1 d-flex justify-content-between align-items-center">
              <div id="vt-comment-toolbar">
                <span class="ql-formats m-0">
                  <button class="ql-bold" title="Bold"></button>
                  <button class="ql-italic" title="Italic"></button>
                  <button class="ql-underline" title="Underline"></button>
                </span>
              </div>
              <button class="btn btn-sm btn-primary d-flex align-items-center gap-1" type="button" id="vt-send-comment-btn">
                <i class="ti ti-send ti-xs"></i>
                <span>{{ __('admin.send') }}</span>
              </button>
            </div>
          </div>
        </div>
      </div> --}}

      {{-- ACTIVITY TAB --}}
      <div class="tab-pane fade" id="vt-activity" role="tabpanel">
        <div class="p-4">
          <div id="vt-activities-list"></div>
        </div>
      </div>

    </div>

    {{-- FOOTER ACTIONS --}}
    <div class="d-flex align-items-center gap-2 px-4 py-3 border-top flex-shrink-0">
      {{-- @if ($_user->ableTo('tasks.update', $permissions))
      <button type="button" class="btn btn-primary btn-sm" id="vt-edit-trigger-btn">
        <i class="ti ti-edit ti-xs me-1"></i>{{ __('admin.Edit_Task') }}
      </button>
      @endif --}}
      <button type="button" class="btn btn-label-secondary btn-sm ms-auto" data-bs-dismiss="offcanvas">
        {{ __('admin.Close') }}
      </button>
    </div>

  </div>
</div>
