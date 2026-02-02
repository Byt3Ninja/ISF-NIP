<div class="modal fade" id="requestServiceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Header -->
      <div class="modal-header">
        <h5 class="modal-title">{{ __('admin.request_service') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Body -->
      <div class="modal-body">

        <!-- Service Info -->
        <div class="mb-4">
          <h5 id="modalServiceName" class="fw-semibold mb-1">{{ __('admin.Service_Name') }}</h5>
          <p id="modalServiceDescription" class="text-muted mb-2">
            Service description goes here...
          </p>

          <div class="d-flex flex-wrap gap-3 align-items-center small">
            <span>
              <strong>{{ __('admin.Price') }}:</strong>
              <span id="modalServicePrice">$200</span>
            </span>

            <div class="d-flex align-items-center gap-2">
              <i class="ti ti-star-filled text-warning"></i>
              <div class="d-flex align-items-center gap-1">
                <span id="modalRating" class="fw-semibold">4.8</span>
                <span class="text-muted small ms-1">(<span id="modalReviewsCount">120</span> {{ __('admin.reviews') }})</span>
              </div>
            </div>
          </div>
        </div>

        <h6 class="mb-2 text-capitalize">{{ __('admin.provided_by') }}</h6>
        <!-- Provider Card -->
        <div class="card border shadow-none mb-4">
          <div class="card-body py-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
              @if (isset($user->image_url) && !empty($user->image_url))
                <img 
                  src="{{ $user->image_url }}" 
                  alt="avatar"
                  class="rounded-circle w-px-50 h-px-50 object-fit-cover"
                  onerror="this.onerror=null; this.nextElementSibling.classList.remove('d-none'); this.nextElementSibling.classList.add('d-flex'); this.classList.add('d-none');"
                />
              @endif

              <div class="bg-light rounded-circle d-{{ (isset($user->image_url) && !empty($user->image_url)) ? 'none' : 'flex' }} align-items-center justify-content-center w-px-50 h-px-50">
                <i class="ti ti-user fs-5"></i>
              </div>

              <div>
                <div class="fw-semibold" id="modalProviderName">
                  Contact Name
                </div>

                <div class="text-muted small" id="modalProviderCompany">
                  Company Name
                </div>
              </div>
            </div>

            <a href="#" id="modalViewProfile"
              class="btn btn-sm btn-outline-primary">
              {{ __('admin.View Profile') }}
            </a>
          </div>
        </div>

        <!-- Request Service Form -->
        <form id="requestServiceForm">

          <!-- Date and Time -->
          <div class="form-group mb-3">
            <label class="form-label">
              {{ __('admin.request_date_and_time') }} <span class="text-danger">*</span>
            </label>
            <input class='form-control' type="text" name="datetime" id="dateTimePicker" placeholder="{{ __('admin.select_date_and_time') }}" value="" required />
            <div class="invalid-feedback" id="dateError">
              {{ __('admin.please_select_date_time') }}
            </div>
          </div>

          <!-- Notes -->
          <div class="mb-3">
            <label class="form-label">{{ __('admin.Notes') }} ({{ __('admin.Optional') }})</label>
            <textarea class="form-control"
                      id="notes"
                      rows="4"
                      maxlength="2000"
                      placeholder="{{ __('admin.Add your notes') }}"></textarea>
            <div class="form-text">
              <span id="notesCounter">0</span>/2000 {{ __('admin.characters') }}
            </div>
          </div>
        </form>
      </div>
      <!-- Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          {{ __('admin.Cancel') }}
        </button>

        <button type="button" id="submitServiceRequestBtn" class="btn btn-primary">
          {{ __('admin.submit_request') }}
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(function () {
  // Cache selectors for better performance
  const $dateInput = $('#dateTimePicker');
  const $submitBtn = $('#submitServiceRequestBtn');
  const $notes = $('#notes');
  const $notesCounter = $('#notesCounter');
  const $dateError = $('#dateError');
  const $modalEl = $('#bookServiceModal');

  // Initialize Flatpickr
  $dateInput.flatpickr({
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    minDate: "{{ date('Y-m-d H:i', strtotime('+1 hour')) }}",
    maxDate: "{{ date('Y-m-d H:i', strtotime('+1 year')) }}",
  });

  // Toggle button state and validation on change
  $dateInput.on('change', function () {
    const hasValue = !!$(this).val();
    $submitBtn.prop('disabled', !hasValue);
    if (hasValue) $(this).removeClass('is-invalid');
  });

  // Notes character counter
  $notes.on('input', function () {
    $notesCounter.text($(this).val().length);
  });

  // Submit booking
  $submitBtn.on('click', function () {
    if (!$dateInput.val()) {
      $dateInput.addClass('is-invalid');
      $dateError.show();
      return;
    }

    const success = true; // Simulate success/failure

    if (!success) {
      // show error alert
      const errMsg = "{{ __('admin.unexpected_error') }}";
      releaseFailureAlert({
        title: "{{ __('admin.Error') }}!",
        message: errMsg || "{{ __('admin.unexpected_error') }}",
      });
      return;
    }

    // Hide modal on successful submission
    $('#requestServiceModal').modal('hide');

    // Show success alert
    releaseSuccessAlert({
      title: "{{ __('admin.Success') }}!",
      message: "{{ __('admin.request sent') }}",
    });
  });
});
</script>
@endpush