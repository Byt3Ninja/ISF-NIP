@extends('layouts.admin')

@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
@stop

@section('content')
<!-- BEGIN: Content-->
<div class="">
  <div class="">
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
                        <td>
                          <a title="Click to show details" href="" onclick="ShowDetails('{{ $session->id }}'); return false;">{{ $session->title }}</a>
                        </td>
                        <td> {{ $session->datetime1 ?? '-' }} </td>
                        <td> {{ $session->enddatetime ?? '-' }} </td>
                        <td>
                          @if( $session->type == 'sessions' )
                            {{ __('cycles.Duration') }}: {{ $session->duration }} {{ __('cycles.Minutes') }} :
                            @if( $session->can_join )
                              <a title="Click to join" href="{{ route('trainings.sessions_join', $session->id ?? '0') }}" target="_blank">
                                <button title="Join only" type="button" class="btn btn-icon rounded-circle btn-label-primary">
                                  <i class="ti ti-brand-zoom"></i>
                                </button>
                              </a>
                            @else
                              {{ __('admin.Finished') }}
                            @endif
                          @elseif ($session->type == 'offline')
                            <b>{{ __('cycles.trainer name') }}:</b>
                            {{ $session->trainer_name }}<br><b>{{ __('cycles.training location') }}:</b>
                            {{ $session->location }} <br>
                          @else
                            <a title="Show course contents" href="/dashboard/training/my/{{ $session->training_Address }}">
                              {{ __('cycles.Display course contents') }}
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

<!-- Training Session Modal -->
<div class="modal fade" id="sessionDetails" tabindex="-1" aria-labelledby="sessionDetailsLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header pb-4 px-12 bg-light">
        <h5 class="modal-title" id="sessionDetailsLabel">{{ __('admin.session_details_label') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row px-4 pb-4">
          <div class="col-md-8">
            <div class="session-main-info mb-4">
              <h4 class="mb-2 text-truncate" id="session-title"></h4>
              <p class="text-muted text-truncate" id="session-desc"></p>
              <div class="d-flex align-items-center mt-3">
                <span class="badge bg-success text-white me-2">
                  <i class="fas fa-video me-1"></i>
                  <span id="session-type"></span> 
                </span>
                <span class="badge bg-label-info me-2" id="session-duration-badge">
                  <i class="far fa-clock me-1"></i> 
                  <span id="session-duration"></span>
                </span>
                {{-- <span class="badge bg-light text-dark">
                  ID: #33
                </span> --}}
              </div>
            </div>

            <div class="session-details">
              <div id="trainer-info-wrapper"> <!-- Add this wrapper -->
                <div class="d-flex align-items-center py-3 border-bottom border-top">
                  <div class="rounded-circle me-2 w-px-40 h-px-40 d-flex justify-content-center align-items-center bg-label-light border" id="circle-info">
                    <i class="ti ti-school ti-lg text-dark"></i>
                  </div>
                  <div class="trainer-details mb-3" id="session-trainer-info">
                    <h6 class="mb-1">
                      {{ __('admin.Trainer Name') }}: <span id="session-trainer-name"></span>
                    </h6>
                    <small>
                      <i class="fas fa-envelope me-1"></i>
                      <strong>{{ __('admin.Trainer Email') }}:</strong>
                      <a href="#" class="cursor-pointer" id="session-trainer-email"></a>
                    </small>
                  </div>
                </div>
              </div>

              <div id="course-info-wrapper" style="display: none;">
                <div class="d-flex align-items-center py-3 border-bottom border-top">
                  <div class="rounded-circle me-2 w-px-40 h-px-40 d-flex justify-content-center align-items-center bg-label-light border" id="circle-info">
                    <i class="ti ti-book ti-lg text-dark"></i>
                  </div>
                  <div class="course-details mb-3" id="session-course-info">
                    <h6 class="mb-1">
                      {{ __('admin.Course Name') }}: 
                    </h6>
                    <span id="session-course-name"></span>
                  </div>
                </div>
              </div>


              <div class="row border-bottom" id="date-info">
                <div id="date-only-col" class="col-md-6" style="display: none;">
                  <div class="py-3">
                    <div class="info-label"><i class="fas fa-calendar-alt me-2"></i>{{ __('admin.Date') }}</div>
                    <div id="session-date"></div>
                  </div>
                </div>
                <div id="start-date-col" class="col-md-6" style="display: none;">
                  <div class="py-3">
                    <div class="info-label"><i class="fas fa-calendar-alt me-2"></i>{{ __('admin.StartDate') }}</div>
                    <div id="session-start-date"></div>
                  </div>
                </div>
                <div id="end-date-col" class="col-md-6" style="display: none;">
                  <div class="py-3">
                    <div class="info-label"><i class="fas fa-calendar-check me-2"></i>{{ __('admin.EndDate') }}</div>
                    <div id="session-end-date"></div>
                  </div>
                </div>
              </div>

              <div class="row border-bottom" id="time-info" style="display: none;">
                <div class="col-md-6">
                  <div class="py-3">
                    <div class="info-label"><i class="fas fa-clock me-2"></i>{{ __('admin.Start Time') }}</div>
                    <div id="session-start-time"></div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="py-3">
                    <div class="info-label"><i class="fas fa-clock me-2"></i>{{ __('admin.End Time') }}</div>
                    <div id="session-end-time"></div>
                  </div>
                </div>
              </div>

              <div class="py-3 border-bottom" id="location-info">
                <div class="info-label" id="text-label"><i class="fas fa-map-marker-alt me-2"></i>{{ __('admin.location_address') }}</div>
                <a id="session-location" href="#" target="_blank" class="text-primary text-decoration-none line-clamp-2"></a>
                <div id="session-address" class="text-muted"></div>
              </div>

              <div class="py-3 border-bottom" id="join-info">
                <div class="info-label" id="join-link-label"><i class="fas fa-link me-2"></i>{{ __('admin.join_link') }}</div>
                <div class="d-flex align-items-center">
                  <a id="join-link" href="#" class="text-primary text-decoration-none"
                    target="_blank">
                  </a>
                  <div id="copy-link" style="display: none">
                    <button class="btn btn-sm btn-outline-primary ms-2"
                      onclick="copyToClipboard('#join-link')">
                      <i class="fas fa-copy"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="py-3 border-bottom" id="zoom-info">
                <div class="info-label"><i class="fas fa-id-card me-2"></i>{{ __('admin.meeting_id') }}</div>
                <div id="zoom-id" class="text-truncate"></div>
              </div>
            </div>

            <div class="d-flex align-items-center gap-3 py-3 mt-4">
              <div id="join-button" style="display: none;">
                <button class="btn btn-primary">
                  <i class="fas fa-video me-2"></i>
                  {{ __('admin.Join') }}
                </button>
              </div>
              <button id="cancel-button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                <i class="fas fa-close me-2"></i>
                {{ __('admin.Close') }}
              </button>
            </div>
          </div>

          <div class="col-md-4">
            {{-- <div class="qr-code-container">
              <h6 class="mb-3">امسح الرمز للانضمام</h6>
              <img src="/api/placeholder/180/180" alt="QR Code" class="img-fluid mb-3"
                style="border: 1px solid #ddd; padding: 5px;">
              <div class="text-muted small">رمز الجلسة: https://qr.example.com/t/33-141-177</div>
            </div> --}}

            {{-- <div class="mt-4">
              <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                  <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>معلومات إضافية</h6>
                  <div class="small text-muted mb-2">
                    <i class="fas fa-code me-1"></i> معرف التدريب: #141
                  </div>
                  <div class="small text-muted mb-2">
                    <i class="fas fa-user me-1"></i> معرف المستخدم: #177
                  </div>
                  <div class="small text-muted mb-2">
                    <i class="fas fa-check me-1"></i> آخر تحديث: ٤ أغسطس ٢٠٢٤
                  </div>
                </div>
              </div>
            </div> --}}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Training Details Modal -->
{{-- <div class="modal fade" id="sessionDetails" tabindex="-1" aria-labelledby="sessionDetailsLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <!-- Consider modal-lg if content can be wide -->
    <div class="modal-content shadow border-0">
      <!-- Modal Header -->
      <div class="modal-header">
        <!-- Lighter header, no bottom border if body has cards -->
        <h5 class="modal-title fw-bold d-flex align-items-center" id="sessionDetailsLabel">
          <i class="ti ti-info-circle ti-md me-2 text-primary"></i>
          <span id="dsctitle"></span> <!-- To be populated by JS -->
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-4">
        <!-- Adjusted padding -->

        <!-- Description Card: Use d-none for JS toggling -->
        <div class="card border mb-3 shadow-sm description-card d-none">
          <div class="card-body">
            <h6 class="card-subtitle mb-3 text-primary fw-bold">
              <!-- Use card-subtitle for semantics if appropriate -->
              <i class="ti ti-file-description me-2"></i>{{ __('admin.Description') }}
            </h6>
            <p class="card-text text-muted" id="dsc"></p>
          </div>
        </div>

        <div class="row g-3">
          <!-- Location Card: Use d-none -->
          <div class="col-md-12 location-card">
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
          <div class="col-12 trainer-details-card">
            <div class="card border shadow-sm">
              <div class="card-body">
                <h6 class="card-subtitle mb-3 text-primary fw-bold">
                  <i class="ti ti-user me-2"></i>{{ __('admin.Trainer Details') }}
                </h6>
                <dl class="mb-0">
                  <!-- Trainer Name -->
                  <div class="trainer-name-section">
                    <!-- Wrapper for JS show/hide if name itself is optional -->
                    <dt class="small text-muted mb-2">{{ __('admin.Trainer Name') }}</dt>
                    <dd class="d-flex align-items-center mb-2">
                      <span
                        class="bg-light rounded-circle p-1 me-2 d-inline-flex align-items-center justify-content-center"
                        style="width: 24px; height: 24px;">
                        <i class="ti ti-user-circle text-primary fs-5"></i> <!-- Adjust icon size if needed -->
                      </span>
                      <span id="trainerName" class="fw-medium"></span>
                    </dd>
                  </div>

                  <!-- Trainer Email: Conditionally shown by JS by populating or hiding this specific div -->
                  <div class="trainer-email-section">
                    <!-- Wrapper for JS show/hide -->
                    <dt class="small text-muted mb-2">{{ __('admin.Trainer Email') }}</dt>
                    <dd class="d-flex align-items-center text-truncate">
                      <span
                        class="bg-light rounded-circle p-1 me-2 d-inline-flex align-items-center justify-content-center"
                        style="width: 24px; height: 24px;">
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
          <div class="col-12 datetime-card">
            <div class="card border shadow-sm">
              <!-- Added shadow-sm for consistency -->
              <div class="card-body">
                <h6 class="card-subtitle mb-3 text-primary fw-bold">
                  <i class="ti ti-calendar-event me-2"></i>{{ __('admin.Date & Time') }}
                </h6>
                <p class="card-text fw-semibold mb-0" id="datetime"></p>
              </div>
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('admin.Close')}}</button>
      </div>
    </div>
  </div>
</div> --}}
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
    const $modal = $('#sessionDetails');
    const $title = $('#session-title');

    const $dsc = $('#session-desc');
    const $sessionType = $('#session-type');

    const $location = $('#session-location');
    const $address = $('#session-address');

    const $trainerName = $('#session-trainer-name');
    const $trainerEmailLink = $('#session-trainer-email');

    const $datetime = $('#session-datetime');
    const $sessionDate = $('#session-date');
    const $sessionStartDate = $('#session-start-date');
    const $sessionEndDate = $('#session-end-date');
    const $sessionStartTime = $('#session-start-time');
    const $sessionEndTime = $('#session-end-time');
    const $sessionDuration = $('#session-duration');
    const $zoomId = $('#zoom-id');

    // Clear previous content (important if modal is reused)
    $title.text('Training Details'); // Default title
    $dsc.empty();
    $location.empty();
    $location.attr('href', '#'); // Reset location link
    $address.empty();
    $trainerName.empty();
    $trainerEmailLink.empty();
    $trainerEmailLink.attr('href', '#'); // Reset link
    $datetime.empty();
    $sessionStartTime.empty();
    $sessionEndTime.empty();
    $sessionDuration.empty();
    $zoomId.empty();
    $('#join-link').attr('href', '#'); // Reset link
    $('#join-link').text(''); // Reset text
    // --- End Reset ---

    $.ajax({
        type: "POST",
        url: `/dashboard/trainings/getdsc/${trainingId}`, // Using template literal for cleaner URL
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        // dataType: 'json', // Explicitly expect JSON if your backend always sends it
        success: function(response) {
          console.log(response);
            
            // Reset all visibility states
            $('#text-label').show();
            $('#location-info').show();
            $('#join-info').show();
            $('#zoom-info').show();
            $('#time-info').hide();
            
            // 1. Modal Title
            if (response.title) {
              $title.text(response.title);
            }

            // 2. Description
            if (response.dsc) {
              $dsc.html(response.dsc);
            }

            // 3. Session Type
            if (response.type) {
              if (response.type === 'sessions') {
                $sessionType.text('{{ __('cycles.sessions') }}');
              } else if (response.type === 'offline') {
                $sessionType.text('{{ __('cycles.offline') }}');
              } else if (response.type === 'online') {
                $sessionType.text('{{ __('cycles.online') }}');
              } else {
                $sessionType.text('{{ __('admin.course') }}');
              }
            }

            // 4. Location
            if (response.type == 'offline') {
              const locationText = response.training_location || response.location || '';
              const addressText = response.training_Address || '';
              
              // Check if locationText is already a map link
              const isMapLink = locationText.includes('google.com/maps') || locationText.includes('maps.google');
              
              // Set location text and make it clickable
              if (isMapLink) {
                // If locationText is already a map link, use it directly
                $location.text(locationText);
                $location.attr('href', locationText);
                $address.text(addressText);
           
              } else if (addressText) {
                const mapUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(addressText)}`;
                $location.text(locationText);
                $location.attr('href', mapUrl);
                $address.text(addressText);
              } else if (locationText) {
                // Fallback to location name if address is not available
                const mapUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(locationText)}`;
                $location.text(locationText);
                $location.attr('href', mapUrl);
                $('#map-link-btn').attr('href', mapUrl);
                $('#session-location-link').show();
              } else {
                $location.text('');
                $location.attr('href', '#');
              }
            } else{
              $('#text-label').hide();
              $('#location-info').hide();
            }

            // 5. Trainer Details
                if (response.type === 'sessions' || response.type === 'offline') {
                  $('#trainer-info-wrapper').show();
                  $('#session-duration-badge').show();
                  $trainerName.text(response.trainer_name);
                  $trainerEmailLink.text(response.trainer_email);
                  $trainerEmailLink.attr('href', `mailto:${response.trainer_email}`);
            } else {
             $('#trainer-info-wrapper').hide();
              $('#session-duration-badge').hide();
           }


            // 6. Date and Time
            if (response.date && (response.type === 'sessions' || response.type === 'offline')) {
              $('#date-only-col').show();
              $('#start-date-col').hide();
              $('#end-date-col').hide();
              $sessionDate.text(response.date);
              
              // Show time information for sessions and offline training
              if (response.starttime || response.endtime) {
                $('#time-info').show();
                $sessionStartTime.text(response.starttime || '-');
                $sessionEndTime.text(response.endtime || '-');
              } else {
                $('#time-info').hide();
              }
            } else {
              $('#date-only-col').hide();
              $('#start-date-col').show();
              $('#end-date-col').show();
              $('#time-info').hide();
              
              if (response.datetime1) {
                $sessionStartDate.text(response.datetime1);
              }
              if (response.enddatetime) {
                $sessionEndDate.text(response.enddatetime);
              }
            }

            let duration = response.duration || '';

            const startTime = response.starttime;
            const endTime = response.endtime;
            
            // Calculate duration from time strings
            if (startTime && endTime) {
              const start = moment(startTime, 'h:mm A');
              const end = moment(endTime, 'h:mm A');
              
              // Handle case where end time is next day
              if (end.isBefore(start)) {
                end.add(1, 'day');
              }
              
              duration = end.diff(start, 'minutes');
            }
            
            if (duration) {
              $sessionDuration.text(duration + ' ' + '{{ __('admin.minutes') }}');
            }

            // 7. Duration
            if (duration && (response.type === 'sessions' || response.type === 'offline')) {
              $sessionDuration.text(duration + ' ' + '{{ __('admin.minutes') }}');
            }

            // 8. Join Link
            if (response.type == 'sessions' && response.can_join) {
              $('#join-link').attr('href', response.join_url);
              $('#join-link').text(response.join_url);
              $('#join-button').show();
              $('#copy-link').show();
            } else {
              $('#join-button').hide();
              $('#copy-link').hide();
              $('#join-link').hide();
              $('#join-info').hide();
            }

            // 9. Zoom ID
            if (response.type == 'sessions') {
              $zoomId.text(response.zoom_id);
            } else{
              $('#zoom-info').hide();
            }

            // 10. Course
            if (response.type === 'online' && response.course_Name) {
              $('#course-info-wrapper').show();
              $('#session-course-name').text(response.course_Name);
            } else {
              $('#course-info-wrapper').hide();
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

  function copyToClipboard(elemId) {
    const text = $(elemId).attr('href');
    navigator.clipboard.writeText(text).then(function() {
        alert('تم نسخ الرابط بنجاح!');
    }, function(err) {
        console.error('فشل في نسخ النص: ', err);
    });
  }

  const joinButton = document.getElementById('join-button');

  joinButton.addEventListener('click', function() {
    const joinUrl = document.getElementById('join-link').href;
    window.open(joinUrl, '_blank');
  });
</script>
@stop