<div class="modal fade" id="SendRequest" tabindex="-1" aria-labelledby="sendrequest" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content shadow-lg">
      <div class="modal-header bg-light border-0">
        <h4 class="modal-title fw-bold mb-0 d-flex align-items-center pb-4">
          <i class="ti ti-calendar-plus ti-lg me-2"></i>
          {{__('dashboard.Add mentorship session with')}} <span class="text-warning fw-semibold ms-2" id="rname"></span>
        </h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="session-form" method="post" action="/dashboard/mentorship/addwait" enctype="multipart/form-data"
        autocomplete="off">

        @csrf
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
          <input type="hidden" name="mentorid" value="0" id="mentorid">
          <input type="hidden" name="organization_id" id="addOrganizationID">
          <input type="hidden" name="cycle_id" id="addCycleID">
          <input type="hidden" name="user_id" id="addUserID">

          <!-- Startup Selection (For Admin/Tenant Admin) -->
          <div class="mb-4 d-none" id="startup-select-container">
            <div class="form-group">
              <label for="startup_user_id" class="form-label fw-semibold fs-6 mb-1">
                <i class="ti ti-building text-primary me-2"></i>
                {{ $l == 'ar' ? 'اختر الشركة الناشئة' : 'Select Startup' }}:
              </label>
              <select class="form-select border-2" id="startup_user_id" name="user_id">
                <option value="">{{ $l == 'ar' ? 'اختر شركة ناشئة' : 'Select a startup' }}</option>
              </select>
              <div class="invalid-feedback startup-error" style="display:none;">
                {{ $l == 'ar' ? 'يرجى اختيار شركة ناشئة.' : 'Please select a startup.' }}
              </div>
            </div>
          </div>

          <!-- Mentor Schedule Section -->
          <div class="mb-4" id="mentor-schedule-container">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="d-flex align-items-center">
                <i class="ti ti-clock-hour-4 text-primary me-2 fs-5"></i>
                <h5 class="text-dark mb-0 fw-semibold">{{__('dashboard.Mentor schedule')}}</h5>
              </div>
            </div>
            <div class="alert alert-info border-0 bg-light-primary">
              <ul class="mb-0 list-unstyled" id="schedule"></ul>
            </div>
          </div>

          <!-- Date and Time Selection (Predefined Slots) -->
          <div class="card border-0 mb-4">
            <div class="card-body p-3">
              <!-- Title with Icon -->
              <div class="d-flex align-items-center mb-3">
                <i class="ti ti-calendar-event text-primary me-2 fs-5"></i>
                <h5 class="text-dark mb-0 fw-semibold">{{ $l == 'ar' ? 'التاريخ والوقت' : 'Date & Time' }}</h5>
              </div>
              <div class="row">
                <!-- Left Side: Inline Calendar -->
                <div class="col-md-6 mb-4">
                  <label class="form-label fs-6 fw-semibold mb-2">{{ $l == 'ar' ? 'اختر التاريخ' : 'Select Date' }}:</label>
                  <div id="inline-calendar" class="d-flex justify-content-center"></div>
                  <input type="hidden" id="flatpickr-datetimeLimit" name="date" required>
                  <div class="invalid-feedback date-error" style="display:none;">{{ __('admin.Please_select_date') }}</div>
                </div>
                
                <!-- Right Side: Slots Checklist -->
                <div class="col-md-6 mb-4">
                  <label class="form-label fs-6 fw-semibold mb-2">{{ $l == 'ar' ? 'المواعيد المتاحة' : 'Available Slots' }}:</label>
                  <div id="slots-container" class="border rounded p-3 bg-light" style="max-height: 350px; overflow-y: auto;">
                    <p class="text-muted mb-0 text-center py-4">{{ $l == 'ar' ? 'الرجاء اختيار تاريخ لعرض المواعيد المتاحة' : 'Please select a date to view available slots' }}</p>
                  </div>
                  <div class="invalid-feedback slots-error" style="display:none;"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- File Upload Section -->
          <div class="card border-0 mb-4">
            <div class="card-body p-3">
              <label for="inputGroupFile01" class="form-label d-flex align-items-center fw-semibold mb-3">
                <i class="ti ti-paperclip me-2 text-primary"></i>
                {{__('dashboard.Send file with your request')}}
              </label>
              <input type="file" class="form-control form-control-lg border-2" id="inputGroupFile01" name="file_url"
                accept="application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, text/plain, application/pdf, image/*">
              <div class="form-text mt-2">
                <i class="ti ti-info-circle me-1"></i>
                {{ $l == 'ar' ? 'الصيغ المدعومة: PDF، DOC، XLS، PPT، الصور' : 'Supported formats: PDF, DOC, XLS, PPT, Images' }}
              </div>
            </div>
          </div>

          <!-- Notes Section -->
          <div class="card border-0">
            <div class="card-body p-3">
              <label for="notes" class="form-label d-flex align-items-center fw-semibold mb-3">
                <i class="ti ti-notes me-2 text-primary"></i>
                {{__('dashboard.Add note')}}:
              </label>
              <textarea class="form-control form-control-lg border-2" id="notes" name="notes" rows="4"
                placeholder="{{__('dashboard.Write your note here')}}"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light border-0 d-flex justify-content-between align-items-center p-4">
          <button type="button" class="btn btn-outline-secondary bg-white btn-lg" data-bs-dismiss="modal">
            <i class="ti ti-x me-2"></i>{{__('admin.Cancel')}}
          </button>
          <button id="sessionSubmitButton" type="submit" class="btn btn-primary btn-lg px-4">
            <i class="ti ti-send me-2"></i>{{__('dashboard.Submit')}}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>