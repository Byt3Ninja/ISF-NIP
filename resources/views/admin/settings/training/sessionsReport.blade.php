@extends('layouts.admin')

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/tagify/tagify.css" />
<link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify.min.css">
<link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify-bootstrap.min.css">

<style>
  .daterangepicker {
    margin-top: 10rem !important;
  }

  #modal-content {
    background-color: var(--bs-white);
  }

  @media (min-width: 768px) {
    #modal-content {
      background-color: #ebebed;
    }
  }

  @media (max-width: 767.98px) {
    .modal-dialog {
      max-width: 100%;
      margin: 0;
    }

    .modal-content {
      min-height: 100vh;
      border-radius: 0;
    }
  }

  /* Spinning icon animation */
  .spinner-icon {
    animation: spin 1s linear infinite;
  }
  
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
</style>
@stop

@section('content')
<div class="">
  <div class="">
    <div class="content-overlay"></div>
    {{--
    <h5 class="content-header-title float-left pr-1 mb-0">
      {{ __('cycles.all online training steps') }}
    </h5>
    --}}
    <div class="content-body">
      <section id="multiple-column-form">
        @if (session()->has('message'))
        <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2"
          role="alert">
          {{ __(session()->get('message')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
         @if (session()->has('message2'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              {{ __('admin.' . session()->get('message2')) }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
      </section>
      @if (count($sessions) > 0)
      <section class="row">
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card">
            <div class="card-header">
            </div>
            <div class="card-body">
                    <div class="card-header" style="padding-bottom: 15px;">
                        <h4 class="card-title">{{ __('cycles.Training Steps') }}</h4>
                    </div>
                   
              <div class="card-text">
                <br>
                <!-- Filter -->
                <form method="GET" action="{{ route('trainings.sessions_report') }}"
                  class="row g-3 align-items-center">
                  <div class="col-auto">
                    <button class="btn btn-primary d-flex justify-content-center align-items-center"
                      style="border-radius: 50%; width: 40px; height: 40px;" type="submit">
                      <i class="ti ti-filter text-white"></i>
                    </button>
                  </div>

                  <div class="col-auto">
                    <select name="type" class="form-select">
                      <option value="0">All</option>
                      @php
                      $Types = App\TrainingSession::select('type')->distinct()->get();
                      @endphp
                      @foreach ($Types as $Type)
                      <option value="{{ $Type->type }}" {{ $Type->type == request('type') ? 'selected' : '' }}>
                        {{ $Type->type }}</option>
                      @endforeach
                    </select>
                  </div>

                   <div class="col-auto">
                    <select name="cycle" class="form-select">
                      <option value="0">{{ __('admin.Choose_Program') }}</option>
                      @php
                    
                      $cycles = app\TrainingSession::with('step.cycle')
                                ->get()
                                ->pluck('step.cycle')
                                ->unique('id');
                      @endphp
                      @foreach ($cycles as $cycle)
                      <option value="{{ $cycle->id }}" {{ $cycle->id == request('cycle') ? 'selected' : '' }}>
                        {{ $cycle->title }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-auto">
                    <select name="step" class="form-select">
                      <option value="0">{{ __('admin.Choose_Step') }}</option>
                      @php
                        $cycle = request('cycle');
                        $query = \App\TrainingSession::with('step');

                        if ($cycle != 0) {
                            $query->whereHas('step', function ($q) use ($cycle) {
                                $q->where('cycle_id', $cycle);
                            });
                        }

                        $steps = $query->get()
                            ->pluck('step')
                            ->filter()
                            ->unique('id')
                            ->values(); 
                      @endphp
                      @foreach ($steps as $step)
                      <option value="{{ $step->id }}" {{ $step->id == request('step') ? 'selected' : '' }}>
                        {{ $step->title }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-auto">
                    <input type="text" name="dateFrom" value="{{ request('dateFrom') }}"
                      class="form-control flatpickr-datefilter" placeholder="From">
                  </div>

                  <div class="col-auto">
                    <input type="text" name="dateTo" value="{{ request('dateTo') }}"
                      class="form-control flatpickr-datefilter" placeholder="To">
                  </div>

                </form>

                <hr>
                <div class="table-responsive">
                  <table class="table mb-0" id="trainingtable">
                    <thead class="bg-label-light">
                      <tr>
                        <th>#</th>
                        <th>{{ __('cycles.Type') }}</th>
                        <th>{{ __('cycles.Title') }}</th>
                        <th>{{ __('cycles.Description') }}</th>
                        <th>{{ __('cycles.Start') }}</th>
                        <th>{{ __('cycles.End') }}</th>
                        <th>{{ __('cycles.Duration') }}</th>
                        <th>{{ __('cycles.Program') }}</th>
                        <th>{{ __('cycles.Training_Step') }}</th>
                        <th>{{ __('cycles.Trainer') }}</th>
                        <th>{{ __('cycles.Training_Map/Link') }}</th>
                        <th>{{ __('cycles.Edit') }} | {{ __('cycles.Delete') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($sessions as $key => $session)
                      <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ __('cycles.' . $session->type) }}</td>
                        <td>{{ $session->title }}</td>
                        <td>{{ $session->dsc }}</td>
                        <td>
                          @if($session->type == "online")
                          {{ FormatDate($session->datetime, 'Y-m-d') ?? '-' }}
                          @else
                          {{ FormatDate($session->datetime, 'Y-m-d HH:i') ?? '-' }}
                          @endif
                        </td>
                        <td>
                          @if($session->type == "online")
                          {{ FormatDate($session->enddatetime, 'Y-m-d') ?? '-' }}
                          @else
                          {{ FormatDate($session->enddatetime, 'Y-m-d HH:i') ?? '-' }}
                          @endif
                        </td>
                        @php
                          $hours = floor($session->duration / 60);
                          $minutes = $session->duration % 60;
                        @endphp
                        <td>
                           {{ sprintf('%02d:%02d', $hours, $minutes) }}
                        </td>
                        <td>{{ $session->step->cycle->title }}</td>                     
                        <td>{{ $session->step->title }}</td>
                        @if($session->type == "offline")
                        <td>{{ $session->trainer_name }}</td>
                        @elseif($session->type == "sessions")
                        <td>{{ $session->trainer_name }}</td>
                        @else
                        <td>{{ $session->course->user->{'first_name_' . $l} }} {{ $session->course->user->{'last_name_' . $l} }}</td>
                        @endif
                        {{-- @if($session->type == "offline")
                        <td>{{ $session->training_location }}</td>
                        @elseif($session->type == "sessions")
                        <td><a href="{{ $session->start_url }}" target="_blank">{{ __('cycles.Start Meeting') }}<a></td>
                        @else
                        <td>{{ $session->course->title }}</td>
                        @endif --}}

                        <td>
                          @if ($session->type == 'sessions')
                          {{ __('cycles.Duration') }}: {{ $session->duration }} {{ __('cycles.Minutes') }} :
                          <a href="{{ $session->start_url }}" target="_blank">
                            <button title="Start Meeting" type="button"
                              class="btn btn-icon btn-label-success rounded-circle waves-effect">
                              <i class="ti ti-brand-zoom"></i></button></a> |
                          <a href="{{ $session->join_url }}" target="_blank">
                            <button title="Join only" type="button"
                              class="btn btn-icon btn-label-info rounded-circle waves-effect">
                              <i class="ti ti-user"></i></button></a> |
                          @if( !empty( $session->zoom_passcode ) )
                            <button data-passcode="{{ $session->zoom_passcode }}" title="Show Passcode" type="button"
                              class="btn btn-icon btn-label-warning rounded-circle waves-effect show-passcode">
                              <i class="ti ti-key"></i></button></a> |
                          @endif
                          <button title="View Report" type="button" data-bs-toggle="modal"
                            data-bs-target="#reportModal{{ $session->id }}"
                            class="btn btn-icon btn-label-warning rounded-circle waves-effect">
                            <i class="ti ti-file-text"></i>
                          </button>
                          {{-- @endif --}}
                          @elseif ($session->type == 'offline')
                          <b>{{ __('cycles.trainer name') }}:</b>
                          {{ $session->trainer_name }}<br><b>{{ __('cycles.training location') }}:</b>
                          {{ $session->training_Address }} <br>
                          @else
                          <a href="/dashboard/training/all/{{ $session->training_Address }}">{{ __('cycles.Display course contents') }}
                          </a>
                          @endif

                        </td>
                        <td>
                          @if ($session->type == 'offline')
                          @if ($session->QrCode != null)
                          <button title="QRCode" class="btn btn-icon btn-label-warning rounded-circle waves-effect"
                            data-bs-toggle="modal" data-bs-target="#qrCodeModal{{ $session->id }}">
                            <i class="ti ti-qrcode text-warning"></i></button>
                          @else
                          <button title="CreateQRCode" onclick="createQrCode({{ $session->id }})"
                            class="btn btn-icon btn-label-success rounded-circle waves-effect">
                            <i class="ti ti-qrcode text-success"></i></button>
                          @endif
                          <a title="Attendance" href="/dashboard/training/Attendance-offline/{{ $session->id }}"
                            class="btn btn-icon btn-label-dark rounded-circle waves-effect">
                            <i class="ti ti-calendar-check text-dark"></i></a>
                          @endif

                          <button title="Edit" onclick="editme({{ $session->id }})" type="button"
                            class="btn btn-icon btn-label-info rounded-circle waves-effect">
                            <i class="ti ti-edit text-info"></i></button>&nbsp;
                          {{-- @if( $session->datetime >= now() ) --}}
                          <button title="Delete" onclick="deleteme({{ $session->id }})" type="button"
                            class="btn btn-label-danger btn-icon rounded-circle waves-effect">
                            <i class="ti ti-trash text-danger"></i></button>
                          {{-- @endif --}}
                        </td>
                      </tr>
                      <!-- المودال نفسه -->
                      <div class="modal fade" id="qrCodeModal{{ $session->id }}" tabindex="-1"
                        aria-labelledby="qrCodeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="qrCodeModalLabel">QR Code</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                              <img src="{{ asset($session->QrCode) }}" alt="QR Code" class="img-fluid">

                            </div>
                            <div title="CreateQRCode" onclick="createQrCode({{ $session->id }})"
                              class="btn btn-label-info">
                              {{ __('admin.Re_generate') }}</div>

                          </div>
                        </div>
                      </div>
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
      <div class="row">
        <div class="col-12">
          <div class="card card-body h-100 d-flex justify-content-center align-items-center">
            <div class="d-flex flex-column align-items-center">
              <img src="/icons/not-found.svg" width="250" height="250" alt="No Result">
              <h6 class="text-center text-secondary w-75">
                {{ __('admin.No_training_sessions_available') }}
              </h6>
              <div class="text-center">
                <a href="/dashboard/cycles" class="btn btn-primary">
                  <span class="mx-1">{{ __('admin.Go to Steps') }}</span>
                  <i class="ti ti-arrow-{{$l == 'en' ? 'right' : 'left'}} ms-2"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>



@foreach ($sessions as $session)
<!-- Modal for Session Reports and Recordings -->
<div class="modal fade" id="reportModal{{ $session->id }}" tabindex="-1"
  aria-labelledby="reportModalLabel{{ $session->id }}" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reportModalLabel{{ $session->id }}">
          {{ __('admin.session_reports') }}: {{ $session->title }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Total Meeting Duration -->
        <div class="mb-3">
          <strong>{{ __('admin.total_meeting_duration') }}:</strong>
          {{ round($session->reports->sum('attendance_duration') / 60, 2) }} {{ __('admin.minutes') }}
        </div>
        <!-- Total Number of Participants -->
        <div class="mb-3">
          <strong>{{ __('admin.total_number_of_participants') }}:</strong>
          {{ $session->reports->count() }}
        </div>

        <!-- Session Reports Section -->
        @if ($session->reports->count() > 0)
        <div class="d-flex justify-content-between align-items-center mt-3">
          <h5>{{ __('admin.session_reports') }}</h5>
        </div>
        <table class="table table-bordered mt-2">
          <thead>
            <tr>
              <th class="text-center">#</th>
              <th class="text-center">{{ __('admin.participant_name') }}</th>
              <th class="text-center">{{ __('admin.participant_email') }}</th>
              <th class="text-center">{{ __('admin.join_time') }}</th>
              <th class="text-center">{{ __('admin.leave_time') }}</th>
              <th class="text-center">{{ __('admin.attendance_duration') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($session->reports as $key => $report)
            <tr>
              <td class="text-center">{{ $key + 1 }}</td>
              <td class="text-center">{{ $report->participant_name }}</td>
              <td class="text-center">{{ $report->participant_email }}</td>
              <td class="text-center">{{ $report->participant_join_date }}</td>
              <td class="text-center">{{ $report->participant_end_date }}</td>
              <td class="text-center">{{ round($report->attendance_duration / 60, 2) }}
                {{ __('admin.minutes') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @else
        <!-- No Reports: Display Update Button -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <h5>{{ __('admin.session_reports') }}</h5>
          <a href="{{ route('trainings.reports_session', ['id' => $session->id, 'type' => 'training']) }}" class="btn btn-info">
            {{ __('admin.update_reports') }}
          </a>
        </div>
        @endif

        <!-- Session Recordings Section -->
        @if ($session->recordings->count() > 0)
        <div class="d-flex justify-content-between align-items-center mt-4">
          <h5>{{ __('admin.session_recordings') }}</h5>
          <a href="{{ route('trainings.reports_recording', ['id' => $session->id, 'type' => 'training']) }}" class="btn btn-warning">
            {{ __('admin.update_recordings') }}
          </a>
        </div>
        <div class="mt-2">
          <strong>{{ __('admin.recording_passcode') }}:</strong>
          <span>{{ $session->recordings->first()->passcode ?? __('admin.not_available') }}</span>
        </div>
        <table class="table table-bordered mt-2">
          <thead>
            <tr>
              <th class="text-center">#</th>
              <th class="text-center">{{ __('admin.recording_type') }}</th>
              <th class="text-center">{{ __('admin.recording_time') }}</th>
              <th class="text-center">{{ __('admin.view_recording') }}</th>
              <th class="text-center">{{ __('admin.download_recording') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($session->recordings as $key => $recording)
            <tr>
              <td class="text-center">{{ $key + 1 }}</td>
              <td class="text-center">{{ $recording->recording_type }}</td>
              <td class="text-center">{{ $recording->recording_time }}</td>
              <td class="text-center">
                <a href="{{ $recording->recording_view_url }}" target="_blank" class="btn btn-primary">
                  {{ __('admin.View') }}
                </a>
              </td>
              <td class="text-center">
                <a href="{{ $recording->recording_download_url }}" target="_blank" class="btn btn-success">
                  {{ __('admin.Download') }}
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @else
        <!-- No Recordings: Display Update Button -->
        <div class="d-flex justify-content-between align-items-center mt-4">
          <h5>{{ __('admin.session_recordings') }}</h5>
          <a href="{{ route('trainings.reports_recording', $session->id) }}" class="btn btn-warning">
            {{ __('admin.update_recordings') }}
          </a>
        </div>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.Close') }}</button>
      </div>
    </div>
  </div>
</div>
@endforeach


















@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
<script src="/app-assets/plugins/alertify/alertify.min.js"></script>
@stop

@section('pagescripts')
<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>

<script>
  

    // Reset button state when modals are closed
    function resetSubmitButton(formId) {
      const submitBtn = $(`${formId} button[type="submit"]`);
      submitBtn.prop('disabled', false);
      submitBtn.find('i').removeClass('ti-loader-2 spinner-icon').addClass('ti-circle-check');
    }

</script>

<script>
  $(document).ready(function() {
      $('#trainingtable').DataTable({
        "responsive": true,
        "searching": true,
        "lengthChange": false,
        "paging": true,
        "bInfo": false,
        "columnDefs": [{
          "orderable": false,
          "targets": [6]
        }, ],
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
</script>
<script>
  // $('#datetime,#enddate,#edatetime,#eenddate').datetimepicker({
    //   timepicker: true,
    //   format: 'Y-m-d H:i'
    // });

  function deleteme(id) {
    let url = '/dashboard/training/delsession/' + id;
    let token = '{{ csrf_token() }}';

    Swal.fire({
      title: "{{__('admin.SureDelete')}}",
      text: "{{__('admin.Recover')}}",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "{{ __('admin.DYes') }}",
      cancelButtonText: "{{ __('admin.No') }}",
      reverseButtons: true,
      customClass: {
          confirmButton: 'btn btn-danger me-2',
          cancelButton: 'btn btn-label-secondary',
      },
      buttonsStyling: false,
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(url, {
                '_method': 'POST',
                '_token': token
            }, function(response) {
                Swal.fire({
                  title: '{{ __('admin.Deleted') }}!',
                  text: '{{ __('admin.Course Deleted') }}.',
                  icon: 'success',
                  showCancelButton: false,
                  showConfirmButton: true,
                  confirmButtonText: "{{ __('admin.OK') }}",
                  customClass: {
                      confirmButton: 'btn btn-primary',
                  },
                  buttonsStyling: false,
                  timer: 1500,                    
                  timerProgressBar: true
                }).then(() => {
                    window.location.href = '/dashboard/training/sessionsReport';
                });
            }).fail(function(error) {
                console.error(error);
            });
        } else {
            console.log('Canceled');
        }
    });
}

    // function tchange(training) {
    //   if (training === 'offline') {
    //     $('#online').hide();
    //     $('#startdatetime').show();
    //     $('#enddatetime').show();
    //     // $('#starttime').show();
    //     // $('#endtime').show();
    //     $('#sessions').hide();
    //     $('#trainer').show();
    //     $('#trainer_email').show();
    //     $('#location').show();
    //     $('#address').show();
    //   } else if (training === 'sessions') {
    //     $('#online').hide();
    //     $('#enddatetime').show();
    //     $('#startdatetime').show();
    //     // $('#endtime').show();
    //     $('#sessions').show();
    //     $('#trainer').show();
    //     $('#trainer_email').show();
    //     $('#location').hide();
    //     $('#address').hide();
    //   } else {
    //     $('#online').show();
    //     $('#enddatetime').hide();
    //     $('#startdatetime').hide();
    //     $('#endtime').hide();
    //     $('#sessions').hide();
    //     $('#trainer').hide();
    //     $('#trainer_email').hide();
    //     $('#location').hide();
    //     $('#address').hide();
    //   }
    // }

    function editme(training) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/training/session",
        data: {
          "id": training,
        },
        success: function(response) {
          console.log('response', response);
          let type = response.type;
          let dateRange = response.daterange;

          const startDate = dateRange.split(' - ')[0];
          const endDate = dateRange.split(' - ')[1];

          $('#editSessionForm #select-type').val(type).trigger('change');


          $('#editSessionForm #title').show();
          $('#editSessionForm #id').val(response.id);
          $('#editSessionForm #step_id').val(response.step_id);
          $('#editSessionForm #title').val(response.title);
          $('#editSessionForm #dsc').val(response.dsc);
          $('#editSessionForm #select-type').val(response.type).trigger('change');
          
          if(response.type != 'online'){
            $('#editSessionForm #online-dropdown').hide();
            $('#editSessionForm .flatpickr-date').val(dateRange  || '');
            $('#editSessionForm #flatpickr-time-from').val(response.fromtime  || '');
            $('#editSessionForm #flatpickr-time-to').val(response.endtime  || '');
          } else{
            $('#editSessionForm #select-course').val(response?.course_id || '').trigger('change');

            $('#editSessionForm #online-dropdown').show();

            $('#editSessionForm #date-range-picker').daterangepicker({
              opens: isRtl ? 'left' : 'right',
              drops: 'up',
              autoUpdateInput: true,
              minDate: moment().format('MM/DD/YYYY'),
              startDate: startDate ? moment(startDate, 'YYYY-MM-DD') : moment(),
              endDate: endDate ? moment(endDate, 'YYYY-MM-DD') : moment().add(7, 'days')
            }).on('apply.daterangepicker', function(ev, picker) {
              $('#editSessionForm #start').val(picker.startDate.format('YYYY-MM-DD'));
              $('#editSessionForm #end').val(picker.endDate.format('YYYY-MM-DD'));
              $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
              $(this).removeClass('is-invalid');
              $(this).next('.invalid-feedback').remove();
            }).on('cancel.daterangepicker', function() {
              $(this).val('');
              $('#editSessionForm #start').val('');
              $('#editSessionForm #end').val('');
            });
            }
         
          if (type === 'offline') {
            $('#editSessionForm #date-only').hide();
            $('#editSessionForm #date-time').show();
            $('#editSessionForm #trainer').show()
            $('#editSessionForm #trainer_name').val(response.trainer_name);
            $('#editSessionForm #trainer_email').show().val(response.trainer_email);
            $('#editSessionForm #location').show();
            $('#editSessionForm #training_location').val(response.training_location);

            $('#editSessionForm #address').show()
            $('#editSessionForm #training_Address').val(response.training_Address);
          } else if (type === 'sessions') {
            $('#editSessionForm #date-only').hide();
            $('#editSessionForm #date-time').show();
            $('#editSessionForm #trainer').show()
            $('#editSessionForm #trainer_name').val(response.trainer_name); 
            $('#editSessionForm #trainer_email').show().val(response.trainer_email);
            $('#editSessionForm #location').hide();
            $('#editSessionForm #address').hide();
          } else {
            $('#editSessionForm #date-only').show();
            $('#editSessionForm #date-time').hide();
            $('#editSessionForm #trainer').hide();
            $('#editSessionForm #trainer_email').hide();
            $('#editSessionForm #location').hide();
            $('#editSessionForm #address').hide();
          }

          // $('#ctype').hide();
          // $('#etitle').val(response.title);
          // $('#edsc').val(response.dsc);
          // $('#edatetime').val(startDate);
          // $('#estartTime').val(startTime);
          // $('#sid').val(training);
          // $('#stype').val(type);

          // // // Time & Date
          // // if (editBsRangePickerTime.length) {
          // //   editBsRangePickerTime.daterangepicker({
          // //     timePicker: true,
          // //     timePickerIncrement: 30,
          // //     locale: {
          // //       format: 'MM/DD/YYYY h:mm A'
          // //     },
          // //     opens: isRtl ? 'left' : 'right'
          // //   });
          // // }

          // if (type === 'offline') {
          //   let endDate = response.enddatetime.split(' ')[0];
          //   let endTime = response.enddatetime.split(' ')[1].substring(0, 5);
          //   $('#sonline').hide();
          //   // $('#senddatetime').show();
          //   $('#estarttime').show();
          //   $('#eendtime').show();
          //   $('#ssessions').hide();
          //   $('#strainer').show();
          //   $('#strainer_email').show();
          //   $('#slocation').show();
          //   $('#saddress').show();
          //   // $('#eenddate').val(response.enddatetime);
          //   $('#edatetime').val(startDate);
          //   $('#estartTime').val(startTime);
          //   $('#eendTime').val(endTime);
          //   $('#elocation').val(response.training_location);
          //   $('#eaddress').val(response.training_Address);
          //   $('#etrainer').val(response.trainer_name);
          //   $('#etrainer_email').val(response.trainer_email);
          // } else if (type === 'sessions') {
          //   $('#sonline').hide();
          //   // $('#senddatetime').hide();
          //   $('#estarttime').show();
          //   $('#eendtime').hide();
          //   $('#ssessions').show();
          //   $('#strainer').show();
          //   $('#strainer_email').show();
          //   $('#slocation').hide();
          //   $('#saddress').hide();
          //   $('#etrainer').val(response.trainer_name);
          //   $('#etrainer_email').val(response.trainer_email);
          //   $('#eduration').val(response.duration);
          //   $('#elocation').val('');
          //   $('#eaddress').val('');
          // } else {
          //   $('#sonline').show();
          //   // $('#senddatetime').hide();
          //   $('#estarttime').show();
          //   $('#eendtime').hide();
          //   $('#ssessions').hide();
          //   $('#strainer').hide();
          //   $('#strainer_email').hide();
          //   $('#slocation').hide();
          //   $('#saddress').hide();
          //   $('#eonline').val(response.training_Address);
          //   $('#etrainer').val('');
          // }
          $('#editsession').modal('show');
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }

    function createQrCode(training) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/training/QrCode-offline",
        data: {
          "id": training,
        },
        success: function(response) {
          alert(response.massege);
          location.reload();
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }


    function formatDateTime(dateTime) {
      if (!dateTime) return 'غير متوفر'; // التحقق من عدم وجود قيمة

      let date = new Date(dateTime);
      return date.toLocaleString({
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true // 24 ساعة
      });
    }

    function Attendance(training) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/training/Attendance-offline",
        data: {
          "id": training,
        },
        success: function(response) {
          let attendanceHtml = "";
          response.attendances.forEach(function(att) {
            attendanceHtml += `<tr>
        <td class="text-center">${att?.training_session?.title ?? 'غير متوفر'}</td> 
        <td class="text-center">${att?.training_session?.trainer_name ?? 'غير متوفر'}</td> 
        <td class="text-center">${att?.cycle?.title ?? 'غير متوفر'}</td> 
        <td class="text-center">${att?.user?.first_name_en +" "+att?.user?.last_name_en +" <br> ( "+ att?.company?.name_en +" ) " ?? 'غير متوفر'}</td> 
        <td class="text-center text-success">${formatDateTime(att?.created_at) ?? 'غير متوفر'}</td>
    </tr>`;
          });

          $(".Attendance_body").html(attendanceHtml);

          $('#Attendance').modal('show');
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }
</script>
@stop