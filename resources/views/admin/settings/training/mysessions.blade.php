@extends('layouts.admin')

@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
    <div class="content-body">
      <section id="multiple-column-form">
        @if (session()->has('message'))
        <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2"
          role="alert">
          {{ __('admin.' . session()->get('message')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
      </section>
      @if (count($sessions) > 0)
      <section class="row">
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card px-4">
            <div class="card-header border-bottom mb-4 col-md-10" style="height: 70px;">
              <h4 class="card-title">{{ __('cycles.My training courses & sessions') }}</h4>
            </div>
            <div class="card-body">
              <div class="card-text">
                <div class="table-responsive">
                  <table class="table mb-0" id="trainingtable">
                    <thead class="table-light">
                      <tr>
                        <th>#</th>
                        <th>{{ __('cycles.Type') }}</th>
                        <th>{{ __('cycles.Title') }}</th>
                        <th>{{ __('cycles.Start') }}</th>
                        <th>{{ __('cycles.End') }}</th>
                        <th>{{ __('cycles.details') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($sessions as $key => $session)
                      <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ __('cycles.' . $session->type) }}</td>
                        <td>{!! $session->dsc
                          ? '<a title="Click to show details" href="" onclick="ShowDetails(' .
                                      $session->id .
                                      ');return false;">' .
                            $session->title .
                            '</a>'
                          : $session->title !!}</td>
                        <td>{{ $session->datetime }}</td>
                        <td>{{ $session->enddatetime ?? '-' }}</td>
                        <td>
                          @if ($session->type == 'sessions')
                          {{ __('cycles.Duration') }}: {{ $session->duration }} {{ __('cycles.Minutes') }} :
                          <a title="Click to join" href="{{ $session->join_url }}" target="_blank">
                            <button title="Join only" type="button"
                              class="btn btn-icon rounded-circle btn-label-primary">
                              <i class="ti ti-brand-zoom"></i></button></a>
                          @elseif ($session->type == 'offline')
                          <b>{{ __('cycles.trainer name') }}:</b>
                          {{ $session->trainer_name }}<br><b>{{ __('cycles.training location') }}:</b>
                          {{ $session->location }} <br>
                          @else
                          <a title="Show course contents" href="/dashboard/training/my/{{ $session->training_Address }}">{{
                            __('cycles.Display course contents') }}
                          </a>
                          @endif
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      @else
      <section class="row">
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card px-4">
            <div class="card-header border-bottom mb-4">
              <h4 class="card-title">{{ __('cycles.nosubscripe') }}</h4>
            </div>

            <div class="card-body">
              {{ __('cycles.No training sessions added yet') }}, <a href="/dashboard">{{ __('cycles.Back') }}</a>.
            </div>
          </div>
        </div>
      </section>
      @endif
    </div>
  </div>
</div>
<!-- END: Content-->

<!-- Training Details Modal -->
<<div class="modal fade" id="dscModal" tabindex="-1" aria-labelledby="dscModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- Consider modal-lg if content can be wide -->
    <div class="modal-content shadow border-0">
      <!-- Modal Header -->
      <div class="modal-header"> <!-- Lighter header, no bottom border if body has cards -->
        <h5 class="modal-title fw-bold d-flex align-items-center" id="dscModalLabel">
          <i class="ti ti-info-circle ti-md me-2 text-primary"></i>
          <span id="dsctitle"></span> <!-- To be populated by JS -->
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-4"> <!-- Adjusted padding -->

        <!-- Description Card: Use d-none for JS toggling -->
        <div class="card border mb-3 shadow-sm description-card d-none">
          <div class="card-body">
            <h6 class="card-subtitle mb-3 text-primary fw-bold"> <!-- Use card-subtitle for semantics if appropriate -->
              <i class="ti ti-file-description me-2"></i>{{ __('admin.Description') }}
            </h6>
            <p class="card-text text-muted" id="dsc"></p>
          </div>
        </div>

        <div class="row g-3">
          <!-- Location Card: Use d-none -->
          <div class="col-md-12 location-card d-none">
            <div class="card h-100 border shadow-sm">
              <div class="card-body">
                <h6 class="card-subtitle mb-3 text-primary fw-bold">
                  <i class="ti ti-map-pin me-2"></i>{{ __('admin.Location') }}
                </h6>
                <!-- Using a definition list for better semantics -->
                <dl class="mb-0">
                  <dt class="visually-hidden">{{ __('admin.VenueName') }}</dt> <!-- Hidden if clear from context -->
                  <dd class="text-dark" id="location"></dd>
                  <dt class="visually-hidden">{{ __('admin.Address') }}</dt> <!-- Hidden if clear from context -->
                  <dd class="text-muted" id="address"></dd> <!-- Address often secondary, so muted -->
                </dl>
              </div>
            </div>
          </div>

          <!-- Trainer Details Card: Use d-none -->
          <div class="col-12 trainer-details-card d-none">
            <div class="card border shadow-sm">
              <div class="card-body">
                <h6 class="card-subtitle mb-3 text-primary fw-bold">
                  <i class="ti ti-user me-2"></i>{{ __('admin.Trainer Details') }}
                </h6>
                <dl class="mb-0">
                  <!-- Trainer Name -->
                  <div class="trainer-name-section"> <!-- Wrapper for JS show/hide if name itself is optional -->
                    <dt class="small text-muted mb-2">{{ __('admin.Trainer Name') }}</dt>
                    <dd class="d-flex align-items-center mb-2">
                      <span class="bg-light rounded-circle p-1 me-2 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                        <i class="ti ti-user-circle text-primary fs-5"></i> <!-- Adjust icon size if needed -->
                      </span>
                      <span id="trainerName" class="fw-medium"></span>
                    </dd>
                  </div>

                  <!-- Trainer Email: Conditionally shown by JS by populating or hiding this specific div -->
                  <div class="trainer-email-section d-none"> <!-- Wrapper for JS show/hide -->
                    <dt class="small text-muted mb-2">{{ __('admin.Trainer Email') }}</dt>
                    <dd class="d-flex align-items-center text-truncate">
                       <span class="bg-light rounded-circle p-1 me-2 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;">
                        <i class="ti ti-mail text-primary fs-5"></i>
                      </span>
                      <a href="#" id="trainerEmailLink" class="text-decoration-none">
                        <span id="trainerEmail"></span>
                      </a>
                    </dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>

          <!-- Date & Time Card: Use d-none -->
          <div class="col-12 datetime-card d-none">
            <div class="card border shadow-sm"> <!-- Added shadow-sm for consistency -->
              <div class="card-body">
                <h6 class="card-subtitle mb-3 text-primary fw-bold">
                  <i class="ti ti-calendar-event me-2"></i>{{ __('admin.Date & Time') }}
                </h6>
                <p class="card-text fw-semibold mb-0" id="datetime"></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Placeholder for when no details are available (Optional) -->
        <div class="no-details-message text-center text-muted p-5 d-none">
            <i class="ti ti-mood-empty" style="font-size: 3rem;"></i>
            <p class="mt-2">{{ __('admin.NoDetailsAvailable') }}</p>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Close')}}</button>
      </div>
    </div>
  </div>
</div>
<!-- Training Details Modal end -->
@stop

@section('pagescripts')
<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
<script>
  $(document).ready(function() {
      $('#trainingtable').DataTable({
        "responsive": true,
        "searching": true,
        "lengthChange": true,
        "paging": true,
        "bInfo": true,
        @if ($l == 'ar')
          "language": {
            "sEmptyTable": "ليست هناك بيانات متاحة في الجدول",
            "sLoadingRecords": "جارٍ التحميل...",
            "sProcessing": "جارٍ التحميل...",
            "sLengthMenu": "أظهر _MENU_ مدخلات",
            "sZeroRecords": "لم يعثر على أية سجلات",
            "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
            "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
            "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
            "sInfoPostFix": "",
            "sSearch": "ابحث:",
            "sUrl": "",
            "oPaginate": {
              "sFirst": "الأول",
              "sPrevious": "السابق",
              "sNext": "التالي",
              "sLast": "الأخير"
            },
            "oAria": {
              "sSortAscending": ": تفعيل لترتيب العمود تصاعدياً",
              "sSortDescending": ": تفعيل لترتيب العمود تنازلياً"
            }
          }
        @endif
      });
    });

   function ShowDetails(trainingId) { // Renamed 'training' to 'trainingId' for clarity
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Select modal elements once for efficiency and clarity
    const $modal = $('#dscModal');
    const $title = $('#dsctitle');

    const $descriptionCard = $('.description-card');
    const $dsc = $('#dsc');

    const $locationCard = $('.location-card'); // Updated class name
    const $location = $('#location');
    const $address = $('#address');

    const $trainerDetailsCard = $('.trainer-details-card'); // Updated class name
    const $trainerName = $('#trainerName');
    const $trainerEmailSection = $('.trainer-email-section'); // New wrapper
    const $trainerEmailLink = $('#trainerEmailLink'); // Link element
    const $trainerEmailSpan = $('#trainerEmail');    // Span inside link

    const $datetimeCard = $('.datetime-card'); // Updated class name
    const $datetime = $('#datetime');

    const $noDetailsMessage = $('.no-details-message');

    // --- Reset modal state before populating ---
    // Hide all conditional sections
    $descriptionCard.addClass('d-none');
    $locationCard.addClass('d-none');
    $trainerDetailsCard.addClass('d-none');
    $trainerEmailSection.addClass('d-none'); // Specifically hide email section
    $datetimeCard.addClass('d-none');
    $noDetailsMessage.addClass('d-none');

    // Clear previous content (important if modal is reused)
    $title.text('Training Details'); // Default title
    $dsc.empty();
    $location.empty();
    $address.empty();
    $trainerName.empty();
    $trainerEmailSpan.empty();
    $trainerEmailLink.attr('href', '#'); // Reset link
    $datetime.empty();
    // --- End Reset ---

    $.ajax({
        type: "POST",
        url: `/dashboard/trainings/getdsc/${trainingId}`, // Using template literal for cleaner URL
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        // dataType: 'json', // Explicitly expect JSON if your backend always sends it
        success: function(response) {
            // console.log('response', response);
            let detailsExist = false; // Flag to track if any content section is shown

            // 1. Modal Title
            if (response.title) {
                $title.text(response.title);
            }

            // 2. Description
            if (response.dsc) {
                $dsc.html(response.dsc); // Use .html() if description can contain HTML, otherwise .text()
                $descriptionCard.removeClass('d-none');
                detailsExist = true;
            }

            // --- Sections specific to 'offline' type or generally applicable based on your data structure ---
            // Assuming these details might not always be tied to 'offline' if the API can provide them for other types
            // If strictly 'offline', wrap the following blocks in an `if (response.type === 'offline')`

            // 3. Location
            // Show location card if either location name or address is present
            if (response.training_location || response.training_Address) {
                $location.text(response.training_location || ''); // Display even if one is missing
                $address.text(response.training_Address || '');
                $locationCard.removeClass('d-none');
                detailsExist = true;
            }

            // 4. Trainer Details
            let trainerInfoExists = false;
            if (response.trainer_name) {
                $trainerName.text(response.trainer_name);
                trainerInfoExists = true;
            }
            if (response.trainer_email) {
                $trainerEmailSpan.text(response.trainer_email);
                $trainerEmailLink.attr('href', `mailto:${response.trainer_email}`);
                $trainerEmailSection.removeClass('d-none');
                trainerInfoExists = true;
            }

            if (trainerInfoExists) {
                $trainerDetailsCard.removeClass('d-none');
                detailsExist = true;
            }

            // 5. Date & Time
            if (response.datetime) {
                // Consider formatting the date/time for better readability
                // Example: new Date(response.datetime).toLocaleString() or using a library like Moment.js/Date-fns
                $datetime.text(response.datetime); // Or formatted date
                $datetimeCard.removeClass('d-none');
                detailsExist = true;
            }

            // If response.type !== 'offline' and you *want* to ensure these fields are empty
            // The reset block at the start handles this more cleanly.
            // The original 'else' block for clearing is now largely handled by the initial reset.

            // 6. Show "No details" message if nothing was populated
            if (!detailsExist) {
                $noDetailsMessage.removeClass('d-none');
            }

            $modal.modal('show');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
            console.error("Response Text:", jqXHR.responseText);
            // Consider a more user-friendly error display, e.g., a toast notification or an error message in the modal
            alert("An error occurred while fetching details. Please try again.");
        }
    });
}
</script>
@stop