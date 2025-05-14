<div class="modal fade" id="SendRequest" tabindex="-1" aria-labelledby="sendrequest" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header mb-0">
        <h4 class="modal-title">{{__('dashboard.Add mentorship session with')}} <span class="text-primary"
            id="rname"></span></h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <form id="session-form" method="post" action="/dashboard/mentorship/addwait" enctype="multipart/form-data"
        autocomplete="off">
      
        @csrf
        <div class="modal-body">
          <input type="hidden" name="mentorid" value="0" id="mentorid">
          <input type="hidden" name="organization_id" id="addOrganizationID">
          <input type="hidden" name="cycle_id" id="addCycleID">
          <div class="mb-6">
            <h5 class="text-secondary">{{__('dashboard.Mentor schedule')}}</h5>
            <div class="bg-label-secondary p-2 rounded">
              <ul class="mb-0" id="schedule"></ul>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="form-group mb-4">
                <label for="date" class="form-label fs-6 fw-semibold mb-1">{{__('dashboard.Date')}}:</label>
                <div class="input-group input-group-merge">
                  <input type="text" class="form-control" id="flatpickr-datetimeLimit"
                    placeholder="{{__('dashboard.Date')}}" name="date" required>
                  <div class="input-group-text">
                    <i class="ti ti-calendar text-secondary"></i>
                  </div>
                </div>
                <div class="invalid-feedback date-error">{{ __('admin.Please_select_date') }}</div>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group mb-4">
                <label for="from" class="form-label fw-semibold fs-6 mb-1">
                  {{__('dashboard.From')}}:
                </label>
                <select class="select2 form-select" id="timeFrom" name="timeFrom">
                </select>
                <div class="invalid-feedback from-error">{{ __('admin.Please_select_from_time') }}</div>
              </div>
            </div>
            <div class="col-6">
              <div class="form-group mb-4">
                <label for="to" class="form-label fw-semibold fs-6 mb-1">
                  {{__('dashboard.To')}}:
                </label>
                <select class="select2 form-select" id="timeTo" name="timeTo">
                </select>
                <div class="invalid-feedback to-error">{{ __('admin.Please_select_to_time') }}</div>
              </div>
            </div>
          </div>

          <div class="col-12 mb-6">
            <div class="invalid-feedback" id="time-feedback">{{ __('admin.End_time_should_be_greater_than_start_time') }}</div>
          </div>

          <div id="duration-container" class="d-none align-items-center gap-4 mb-6 border-bottom pb-3">
            <span class="fs-6 mb-0 text-dark fw-semi">{{__('dashboard.Meeting duration')}}:</span>
            <span id="duration" class="fw-bold fs-6 mb-0"></span>
            {{-- <label for="period" class="form-label fs-6 fw-semibold mb-1">{{__('dashboard.Meeting duration')}}:</label>
            <select name="period" class="form-select" id="period">
              <option value="30" selected>30 {{__('dashboard.Minutes')}}</option>
              <option value="60">1 {{__('dashboard.Hour')}}</option>
              <option value="120">2 {{__('dashboard.Hours')}}</option>
              <option value="180">3 {{__('dashboard.Hours')}}</option>
              <option value="240">4 {{__('dashboard.Hours')}}</option>
              <option value="300">5 {{__('dashboard.Hours')}}</option>
            </select> --}}
          </div>
          <div class="mb-3">
            <label for="inputGroupFile01" class="form-label fs-6 fw-semibold mb-1">
              {{__('dashboard.Send file with your request')}}</label>
            <input type="file" class="form-control" id="inputGroupFile01" name="file_url"
              accept="application/msword, application/vnd.ms-excel, application/vnd.ms-powerpoint, text/plain, application/pdf, image/*">
          </div>
          <div class="mb-3">
            <label for="notes" class="form-label fs-6 fw-semibold mb-1">{{__('dashboard.Add note')}}:</label>
            <textarea class="form-control" id="notes" name="notes" rows="4"
              placeholder="{{__('dashboard.Write your note here')}}"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button id="sessionSubmitButton" type="submit" class="btn btn-primary">{{__('dashboard.Submit')}}</button>
        </div>
      </form>
    </div>
  </div>
</div>