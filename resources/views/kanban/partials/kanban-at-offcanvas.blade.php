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
              @foreach($priorityConfig as $k => $p)
                <option value="{{ $k }}">{{ $p['label'] }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">{{ __('admin.Status') }} <span class="text-danger">*</span></label>
            <select name="status" id="add-status" class="select2 form-select">
              <option value="">{{ __('admin.Select') }}</option>
              @foreach($boards as $k => $b)
                <option value="{{ $k }}">{{ $b['label'] }}</option>
              @endforeach
            </select>
          </div>
        </div>

        @if(isset($startups) && count($startups))
        <div class="mb-3">
          <label class="form-label">{{ __('admin.assignees') }}</label>
          <select name="assignees[]" id="add-assignees" class="select2 form-select" multiple data-placeholder="{{ __('admin.select_assignees') }}">
            @foreach($startups as $startup)
              <option value="{{ $startup['id'] }}">{{ $startup['name'] }}</option>
            @endforeach
          </select>
        </div>
        @endif

        <div class="mb-4">
          <label class="form-label d-flex justify-content-between">
            <span>{{ __('admin.Attachments') }}</span>
            <span class="text-muted small">{{ __('admin.Max_5_Files_10MB_Each') ?? 'Max 5 files, 10MB each' }}</span>
          </label>

          <div id="add-dropzone"
            class="bg-light-subtle border border-dashed rounded p-4 text-center position-relative"
            style="cursor:pointer; transition: border-color .2s, background .2s;">
            <input type="file" id="add-dropzone-input" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor:pointer;"
              accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/zip,application/x-zip-compressed,text/plain,text/csv"
              multiple />
            <i class="ti ti-cloud-upload ti-lg text-primary mb-2 d-block"></i>
            <p class="mb-0 small">{{ __('admin.dropzone') ?? 'Drag & drop files here or click to browse' }}</p>
            <p class="mb-0 text-muted small">{{ __('admin.max_5_files') ?? 'Max 5 files · 10MB each' }}</p>
          </div>

          <div id="add-dropzone-queue" class="d-flex flex-column gap-2 mt-2"></div>
          <div class="invalid-feedback d-block mt-1" id="add-file-error"></div>
        </div>

      </div>
      <div class="d-flex gap-2 mt-auto">
        <button type="submit" class="btn btn-primary" id="add-submit-btn">{{ __('admin.Create') }}</button>
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('admin.Cancel') }}</button>
      </div>
    </form>
  </div>
</div>