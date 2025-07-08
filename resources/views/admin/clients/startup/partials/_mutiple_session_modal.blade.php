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
          
          <!-- Mentor Schedule Section -->
          <div class="mb-4" id="mentor-schedule-container">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="d-flex align-items-center">
                <i class="ti ti-clock-hour-4 text-primary me-2 fs-5"></i>
                <h5 class="text-dark mb-0 fw-semibold">{{__('dashboard.Mentor schedule')}}</h5>
              </div>
              <button type="button" class="btn btn-sm btn-outline-info" id="close-schedule-btn" title="{{__('dashboard.Hide schedule')}}">
                <i class="ti ti-x"></i>
              </button>
            </div>
            <div class="alert alert-info border-0 bg-light-primary">
              <ul class="mb-0 list-unstyled" id="schedule"></ul>
            </div>
          </div>
          
          <!-- Date Selection Section -->
          <div class="card border-0 mb-4">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-12">
                  <div class="form-group">
                    <label for="date" class="form-label d-flex align-items-center fw-semibold mb-3">
                      <i class="ti ti-calendar me-2 text-primary"></i>
                      {{__('dashboard.Date')}}:
                    </label>
                    <div class="input-group input-group-lg">
                      <input type="text" class="form-control border-2" id="flatpickr-datetimeLimit"
                        placeholder="{{__('dashboard.Select multiple dates')}}" name="date" required>
                      <div class="input-group-text bg-primary border-primary">
                        <i class="ti ti-calendar text-white"></i>
                      </div>
                    </div>
                    <div class="invalid-feedback date-error">{{ __('admin.Please_select_date') }}</div>
                    <div class="form-text d-flex align-items-center">
                      <i class="ti ti-info-circle me-1"></i>
                      {{__('dashboard.Click to select multiple dates for your sessions')}}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Selected Dates with Time Options -->
          <div id="selected-dates-container" class="d-none mb-4">
            <div class="card border-primary">
              <div class="card-header bg-light-primary border-primary">
                <h6 class="text-primary mb-0 fw-semibold">
                  <i class="ti ti-clock me-2"></i>
                  {{__('dashboard.Selected Dates and Times')}}
                </h6>
              </div>
              <div class="card-body">
                <!-- Dynamic content will be inserted here -->
              </div>
            </div>
          </div>

          <!-- Legacy time selectors (hidden, kept for backward compatibility) -->
          <div class="row d-none">
            <div class="col-6">
              <div class="form-group mb-4">
                <label for="from" class="form-label fw-semibold mb-2">
                  <i class="ti ti-clock-hour-9 me-1"></i>
                  {{__('dashboard.From')}}:
                </label>
                <select class="form-select form-select-lg" id="timeFrom" name="timeFrom">
                </select>
                <div class="invalid-feedback from-error">{{ __('admin.Please_select_from_time') }}</div>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group mb-4">
                <label for="to" class="form-label fw-semibold mb-2">
                  <i class="ti ti-clock-hour-3 me-1"></i>
                  {{__('dashboard.To')}}:
                </label>
                <select class="form-select form-select-lg" id="timeTo" name="timeTo">
                </select>
                <div class="invalid-feedback to-error">{{ __('admin.Please_select_to_time') }}</div>
              </div>
            </div>
          </div>

          <div class="col-12 mb-4">
            <div class="invalid-feedback" id="time-feedback">{{ __('admin.End_time_should_be_greater_than_start_time') }}</div>
          </div>

          <!-- Duration Display -->
          <div id="duration-container" class="d-none mb-4">
            <div class="alert alert-success border-0 d-flex align-items-center">
              <i class="ti ti-clock-hour-4 me-2 fs-5"></i>
              <div>
                <span class="fw-semibold me-2">{{__('dashboard.Meeting duration')}}:</span>
                <span id="duration" class="badge bg-success fs-6"></span>
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
                Supported formats: PDF, DOC, XLS, PPT, Images
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