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
          <h5 id="modalServiceName" class="fw-semibold mb-1">—</h5>
          <p id="modalServiceDescription" class="text-muted mb-2">—</p>

          <div class="d-flex flex-wrap gap-3 align-items-center small">
            <span id="modalPriceWrapper">
              <strong>{{ __('admin.Price') }}:</strong>
              <span id="modalServicePrice">—</span>
            </span>

            <div class="d-flex align-items-center gap-2">
              <i class="ti ti-star-filled text-warning"></i>
              <div class="d-flex align-items-center gap-1">
                <span id="modalRating" class="fw-semibold">0</span>
                <span class="text-muted small ms-1">(<span id="modalReviewsCount">0</span> {{ __('admin.reviews') }})</span>
              </div>
            </div>
          </div>
        </div>

        <h6 class="mb-2 text-capitalize" id="modalProviderSection">{{ __('admin.provided_by') }}</h6>
        <!-- Provider Card -->
        <div class="card border shadow-none mb-4" id="modalProviderCard">
          <div class="card-body py-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <img
                id="modalProviderImg"
                src=""
                alt="avatar"
                class="rounded-circle w-px-50 h-px-50 object-fit-cover d-none"
                onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none'); this.nextElementSibling.classList.add('d-flex');"
              />
              <div class="bg-light rounded-circle d-flex align-items-center justify-content-center w-px-50 h-px-50" id="modalProviderPlaceholder">
                <i class="ti ti-user fs-5"></i>
              </div>

              <div>
                <div class="fw-semibold" id="modalProviderName">—</div>
                <div class="text-muted small" id="modalProviderCompany">—</div>
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
          <input type="hidden" id="modalServiceId" name="id" value="">

          <!-- Date and Time -->
          <div class="form-group mb-3">
            <label class="form-label">
              {{ __('admin.request_date_and_time') }} <span class="text-danger">*</span>
            </label>
            <input class='form-control' type="text" name="datetime" id="dateTimePicker" placeholder="{{ __('admin.select_date_and_time') }}" value="" required />
            <div class="invalid-feedback" id="dateError">
              {{ __('admin.service_request_date_required') }}
            </div>
          </div>

          <!-- Notes -->
          <div class="mb-3">
            <label class="form-label">{{ __('admin.Notes') }} ({{ __('admin.Optional') }})</label>
            <textarea class="form-control"
                      id="notes"
                      rows="4"
                      maxlength="1000"
                      placeholder="{{ __('admin.Add your notes') }}"></textarea>
            <div class="form-text">
              <span id="notesCounter">0</span>/1000 {{ __('admin.characters') }}
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
  const $modal       = $('#requestServiceModal');
  const $dateInput   = $('#dateTimePicker');
  const $submitBtn   = $('#submitServiceRequestBtn');
  const $notes       = $('#notes');
  const $notesCounter = $('#notesCounter');
  const $dateError   = $('#dateError');
  const csrfToken    = $('meta[name="csrf-token"]').attr('content');
  const locale       = '{{ $l }}';

  // Initialize Flatpickr
  const fp = $dateInput.flatpickr({
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    minDate: "{{ date('Y-m-d H:i', strtotime('+1 hour')) }}",
    maxDate: "{{ date('Y-m-d H:i', strtotime('+1 year')) }}",
  });

  // Toggle button state and validation on change
  $dateInput.on('change', function () {
    const hasValue = !!$(this).val();
    if (hasValue) $(this).removeClass('is-invalid');
  });

  // Notes character counter
  $notes.on('input', function () {
    $notesCounter.text($(this).val().length);
  });

  // Populate modal with service data when opened
  $modal.on('show.bs.modal', function (e) {
    const trigger = $(e.relatedTarget);
    const serviceId = trigger.data('service-id');
    if (!serviceId) return;

    $('#modalServiceId').val(serviceId);

    // Reset form fields
    $dateInput.val('');
    $notes.val('');
    $notesCounter.text('0');
    $dateInput.removeClass('is-invalid');
    $dateError.hide();

    // Reset display fields to loading state
    $('#modalServiceName').text('—');
    $('#modalServiceDescription').text('—');
    $('#modalServicePrice').text('—');
    $('#modalRating').text('0');
    $('#modalReviewsCount').text('0');
    $('#modalProviderName').text('—');
    $('#modalProviderCompany').text('—');
    $('#modalPriceWrapper').show();
    $('#modalProviderCard').show();
    $('#modalProviderSection').show();
    $('#modalViewProfile').attr('href', '#');

    // Fetch service details
    $.ajax({
      type: 'POST',
      url: '/dashboard/directory/' + serviceId,
      headers: { 'X-CSRF-TOKEN': csrfToken },
      success: function (res) {
        if (!res.status) return;
        const s = res.service;
        const nameKey = 'name_' + locale;
        const descKey = 'description_' + locale;

        $('#modalServiceName').text(s[nameKey] || s.name_en);
        $('#modalServiceDescription').html(s[descKey] || s.description_en || '—');
        $('#modalRating').text(s.avg_rating || '0');
        $('#modalReviewsCount').text(s.review_count || '0');

        // Price visibility
        if (s.hide_price) {
          $('#modalPriceWrapper').hide();
        } else {
          $('#modalPriceWrapper').show();
          $('#modalServicePrice').text(s.price ? (s.price + ' ' + (s.currency || '')) : "{{ __('admin.free') }}");
        }

        // Provider visibility
        if (s.hide_vendor_info) {
          $('#modalProviderCard').hide();
          $('#modalProviderSection').hide();
        } else {
          $('#modalProviderCard').show();
          $('#modalProviderSection').show();
          $('#modalProviderName').text(s.provider_contact || s.vendor_name || '—');
          $('#modalProviderCompany').text(s.provider_company || '—');

          // Provider avatar
          if (s.provider_img) {
            $('#modalProviderImg').attr('src', '/191014/' + s.provider_img).removeClass('d-none');
            $('#modalProviderPlaceholder').addClass('d-none').removeClass('d-flex');
          } else {
            $('#modalProviderImg').addClass('d-none');
            $('#modalProviderPlaceholder').removeClass('d-none').addClass('d-flex');
          }

          // View Profile link
          if (s.provider_user_id) {
            $('#modalViewProfile').attr('href', '/dashboard/service-provider/' + s.service_provider_id + '/profile').show();
          } else {
            $('#modalViewProfile').hide();
          }
        }
      },
    });
  });

  // Reset form on modal close
  $modal.on('hidden.bs.modal', function () {
    $dateInput.val('');
    $notes.val('');
    $notesCounter.text('0');
    $dateInput.removeClass('is-invalid');
    $dateError.hide();
    $('#modalServiceId').val('');
  });

  // Submit booking
  $submitBtn.on('click', function () {
    if (!$dateInput.val()) {
      $dateInput.addClass('is-invalid');
      $dateError.show();
      return;
    }

    const serviceId = $('#modalServiceId').val();
    if (!serviceId) return;

    $submitBtn.prop('disabled', true);

    $.ajax({
      type: 'POST',
      url: '/dashboard/service/request',
      headers: { 'X-CSRF-TOKEN': csrfToken },
      data: {
        id: serviceId,
        datetime: $dateInput.val(),
        comment: $notes.val() || null,
      },
      success: function (res) {
        $submitBtn.prop('disabled', false);
        $modal.modal('hide');

        if (res.state) {
          releaseSuccessAlert({
            title: "{{ __('admin.Success') }}!",
            message: res.message || "{{ __('admin.service_request_submitted_successfully') }}",
          });
        } else {
          releaseFailureAlert({
            title: "{{ __('admin.Error') }}!",
            message: res.message || "{{ __('admin.unexpected_error') }}",
          });
        }
      },
      error: function (xhr) {
        $submitBtn.prop('disabled', false);
        let errorMsg = "{{ __('admin.unexpected_error') }}";

        if (xhr.responseJSON && xhr.responseJSON.errors) {
          const errors = xhr.responseJSON.errors;
          errorMsg = Object.values(errors).flat().join('\n');
        }

        releaseFailureAlert({
          title: "{{ __('admin.Error') }}!",
          message: errorMsg,
        });
      },
    });
  });
});
</script>
@endpush
