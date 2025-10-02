@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify.min.css">
  <link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify-bootstrap.min.css">
@stop

@section('content')
  <!-- BEGIN: Content-->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-header">
        <div class="col">
          <h3 class="fw-bold">{{ __('cycles.Mentorship Sessions') }}</h3>
        </div>
      </div>
      <div class="content-body">
        <section id="alerts">
          @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ __('admin.' . session()->get('message')) }}
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

        <section class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center border-bottom pb-3 mb-6">
                <div class="mb-4 mb-md-0 d-flex flex-wrap gap-2">
                  <button onclick="redirect('all')" class="btn btn-{{ request()->show == 'all' ? 'primary' : 'label-secondary' }}">
                    {{ __('cycles.All requests') }}
                  </button>
                  <button onclick="redirect('')" class="btn btn-{{ !request()->show ? 'primary' : 'label-secondary' }}">
                    {{ __('cycles.New requests only') }}
                  </button>
                  <button onclick="redirect('approved')" class="btn btn-{{ request()->show == 'approved' ? 'primary' : 'label-secondary' }}">
                    {{ __('cycles.approved requests') }}
                  </button>
                  <button onclick="redirect('disapproved')" class="btn btn-{{ request()->show == 'disapproved' ? 'primary' : 'label-secondary' }}">
                    {{ __('cycles.disapproved requests') }}
                  </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <!-- Filter Modal Trigger -->
                  <button type="button" class="btn btn-label-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="ti ti-filter me-2"></i>
                    {{ __('cycles.Filter') }}
                  </button>
                  <button class="btn btn-light ExportExcelMentor">
                    <i class="ti ti-file-export me-2"></i>{{ __('cycles.Export') }}
                  </button>
                </div>
              </div>

              <div class="card-body">
               <!-- Filter Modal -->
                <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-md">
                    <div class="modal-content">
                      <form method="GET" action="{{ url()->current() }}">
                        <div class="modal-header">
                          <h5 class="modal-title" id="filterModalLabel">{{ __('cycles.Filter_Sessions') }}</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Cancel') }}"></button>
                        </div>

                        <div class="modal-body">
                          <div class="row g-6">
                            <!-- Start Date -->
                            <div class="col-12 col-md-6">
                              <label for="start_date" class="form-label">{{ __('cycles.Start_Date') }}</label>
                              <input type="text" name="start_date" class="form-control datepicker" 
                                value="{{ request('start_date') }}" placeholder="Start Date">
                            </div>

                            <!-- End Date -->
                            <div class="col-12 col-md-6">
                              <label for="end_date" class="form-label">{{ __('cycles.End_Date') }}</label>
                              <input type="text" name="end_date" class="form-control datepicker" 
                                value="{{ request('end_date') }}" placeholder="End Date">
                            </div>

                            <!-- Show Filter -->
                            <div class="col-12">
                              <label for="showValueSelect" class="form-label">{{ __('cycles.Request_Status') }}</label>
                              <select class="form-select select2" name="show" data-placeholder="{{ __('cycles.Select_Request_Status') }}" id="showValueSelect">
                                <option value="">{{ __('cycles.Select_Request_Status') }}</option>
                                <option value="all" {{ request()->show == 'all' ? 'selected' : '' }}>{{ __('cycles.All requests') }}</option>
                                <option value="new" {{ !request()->show ? 'selected' : '' }}>{{ __('cycles.New requests only') }}</option>
                                <option value="approved" {{ request()->show == 'approved' ? 'selected' : '' }}>{{ __('cycles.approved requests') }}</option>
                                <option value="disapproved" {{ request()->show == 'disapproved' ? 'selected' : '' }}>{{ __('cycles.disapproved requests') }}</option>
                              </select>
                            </div>

                            <!-- Done Filter -->
                            <div class="col-12">
                              <label for="doneValueSelect" class="form-label">{{ __('cycles.Completion_Status') }}</label>
                              <select class="form-select select2" name="done" data-placeholder="{{ __('cycles.Select_Completion_Status') }}" id="doneValueSelect">
                                <option value="" {{ request()->done == '' ? 'selected' : '' }}>{{ __('cycles.Select_Completion_Status') }}</option>
                                <option value="yes" {{ request()->done == 'yes' ? 'selected' : '' }}>{{ __('cycles.Completed') }}</option>
                                <option value="no" {{ request()->done == 'no' ? 'selected' : '' }}>{{ __('cycles.Not_Completed') }}</option>
                              </select>
                            </div>

                            <!-- Cycle -->
                            <div class="col-12">
                              <label for="cycle_id" class="form-label">{{ __('cycles.Cycle') }}</label>
                              <select class="form-select select2" name="cycle_id">
                                @php
                                  $user = auth()->user();
                                  if($user->hasAnyRole(['organizationManager'])) {
                                      $cycles = \App\Cycle::where('organization_id', $user->organization_id)->get();
                                  } else {
                                      $cycles = \App\Cycle::all();
                                  }
                                @endphp
                                <option value="" disabled>{{ __('cycles.Select_Program') }}</option>
                                <option value="all" {{ !request()->cycle_id ? 'selected' : '' }}>{{ __('admin.All_Programs') }}</option>
                                @foreach ($cycles as $cycle)
                                  <option value="{{ $cycle->id }}" {{ request()->cycle_id == $cycle->id ? 'selected' : '' }}>
                                    {{ $cycle->title }}
                                  </option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="modal-footer">
                          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                          <button type="submit" class="btn btn-primary">{{ __('Apply Filters') }}</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table" id="userstable">
                    <thead class="table-light">
                      <tr>
                        <th>{{ __('cycles.Company') }}</th>
                        <th>{{ __('cycles.User/Title') }}</th>
                        <th>{{ __('cycles.Mentor') }}</th>
                        <th>{{ __('cycles.Rate/Comment') }}</th>
                        <th>{{ __('cycles.Date/Time') }}</th>
                        <th>{{ __('cycles.Approved') }}</th>
                        <th>{{ __('cycles.Done') }}</th>
                        <th>{{ __('cycles.Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($sessions as $session)
                        <tr>
                          <td>
                            <div class="d-flex align-items-center w-px-150">
                              <a href="/dashboard/companyProfile/{{ $session->user->company->id ?? '' }}">
                                <img 
                                  onerror="this.src='/images/logo-placeholder.png'"
                                  src="{{ $session->user->company->logo_url ?? '' }}" alt="avatar" class="rounded-circle me-2 border bg-label-light" height="40" width="40">
                              </a>
                              <a href="/dashboard/companyProfile/{{ $session->user->company->id ?? '' }}">
                                {{ $session->user->company->{'name_' . $l} ?? '' }}
                              </a>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <a href="/dashboard/users/{{ $session->user_id }}/edit">
                                @if( !in_array(env('APP_ENV', 'local'), ['local']) )
                                <img 
                                  onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                                  src="{{ $session->user->image_url }}" alt="avatar" class="rounded-circle me-2" height="40" width="40">
                              @endif
                                </a>
                              <div>
                                <h6 class="mb-0 text-nowrap">{{ $session->user->{'first_name_' . $l} }} {{ $session->user->{'last_name_' . $l} }}</h6>
                                <small>{{ $session->user->{'title_' . $l} }}</small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              @if( !in_array(env('APP_ENV', 'local'), ['local']) )
                              <img 
                                onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                                src="{{ $session->mentor->image_url ?? '' }}" alt="avatar" class="rounded-circle me-2" height="40" width="40">
                                @endif
                              {{ $session->mentor->name ?? '' }}
                            </div>
                          </td>
                          <td>
                            <small>{!! $session->rate_comment ? str_repeat('<i class="ti tis-star text-warning"></i>', $session->rating) . '<br>' . $session->rate_comment : $session->comment !!}</small>
                          </td>
                          <td>
                            <small>{{ $session->zoom_date }} ({{ $session->period }}M)</small>
                          </td>
                          <td>
                              @if( $session->approved )
                              @if( $session->approved == 'Yes' )
                              <span class="badge bg-label-success" >
                              {{ __('admin.Yes') }}
                              </span>
                              @else
                              <span class="badge bg-label-danger" >
                              {{ __('admin.No') }}
                              </span>
                              @endif
                              @else
                              <span class="badge bg-label-warning" >
                              {{ __('admin.Waiting') }}
                              </span>
                              @endif
                            
                          </td>
                          <td>
                            <span class="badge bg-label-{{ $session->done ? 'success' : 'danger' }}">
                              {{ $session->done ? __('Yes') : __('No') }}
                            </span>
                          </td>
                          <td>
                            <div class="btn-group">
                              <button class="me-1 btn-icon rounded-circle btn btn-sm btn-label-info" onclick="edit({{ $session->id }})" title="Edit">
                                <i class="ti ti-edit"></i>
                              </button>
                              @if( $session->datetime >= now() )
                              <button class="me-1 btn-icon rounded-circle btn btn-sm btn-label-danger" onclick="deleteme({{ $session->id }})" title="Delete">
                                <i class="ti ti-trash"></i>
                              </button>
                              @endif
                              @if (!$session->approved)
                                <button class="me-1 btn-icon rounded-circle btn btn-sm btn-label-success" onclick="approve({{ $session->id }}, 'Yes')" title="Approve">
                                  <i class="ti ti-check"></i>
                                </button>
                                <button class="me-1 btn-icon rounded-circle btn btn-sm btn-label-danger" onclick="approve({{ $session->id }}, 'No')" title="Disapprove">
                                  <i class="ti ti-x"></i>
                                </button>
                              @endif
                              @if ($session->join_url)
                                <a href="{{ $session->start_url }}" target="_blank" class="me-1 btn-icon rounded-circle btn btn-sm btn-label-secondary" title="Start Meeting">
                                  <i class="ti ti-device-computer-camera"></i>
                                </a>
                                <a href="{{ $session->join_url }}" target="_blank" class="me-1 btn-icon rounded-circle btn btn-sm btn-label-primary" title="Join only">
                                  <i class="ti ti-user"></i>
                                </a>
                                @if( !empty( $session->zoom_passcode ) )
                                <button data-passcode="{{ $session->zoom_passcode }}" title="Show Passcode" type="button"
                                  class="me-1 btn-icon rounded-circle btn btn-sm btn-label-warning show-passcode">
                                  <i class="ti ti-key"></i>
                                </button>
                                @endif
                              @endif
                              @if ($session->fileurl)
                                <a href="/dashboard/private/{{ $session->fileurl }}" target="_blank" class="me-1 btn-icon rounded-circle btn btn-sm btn-light" title="Download attached">
                                  <i class="ti ti-download"></i>
                                </a>
                              @endif
                              <button title="View Report" type="button" data-bs-toggle="modal" data-bs-target="#reportModal{{ $session->id }}" class="btn btn-icon btn-label-warning rounded-circle waves-effect">
                                 <i class="ti ti-file-text"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                  @foreach ($sessions as $session)
                    <!-- Modal for Session Reports and Recordings -->
                    <div class="modal fade" id="reportModal{{ $session->id }}" tabindex="-1" aria-labelledby="reportModalLabel{{ $session->id }}" aria-hidden="true">
                      <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                          <div class="modal-header">
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
                              <a href="{{ route('trainings.reports_session', ['id' => $session->id, 'type' => 'mentorship']) }}" class="btn btn-info">
                                {{ __('admin.update_reports') }}
                              </a>
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
                                  <td class="text-center">{{ round($report->attendance_duration / 60, 2) }} {{ __('admin.minutes') }}</td>
                                </tr>
                                @endforeach
                              </tbody>
                            </table>
                            @else
                            <!-- No Reports: Display Update Button -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                              <h5>{{ __('admin.session_reports') }}</h5>
                              <a href="{{ route('trainings.reports_session', ['id' => $session->id, 'type' => 'mentorship']) }}" class="btn btn-info">
                                {{ __('admin.update_reports') }}
                              </a>
                            </div>
                            @endif

                            <!-- Session Recordings Section -->
                            @if ($session->recordings->count() > 0)
                            <div class="d-flex justify-content-between align-items-center mt-4">
                              <h5>{{ __('admin.session_recordings') }}</h5>
                              <a href="{{ route('trainings.reports_recording', ['id' => $session->id, 'type' => 'mentorship']) }}" class="btn btn-warning">
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
                              <a href="{{ route('trainings.reports_recording',['id' => $session->id, 'type' => 'mentorship']) }}" class="btn btn-warning">
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
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
  <!-- END: Content-->
  <div class="modal fade text-left" id="ModifyRequest" tabindex="-1" role="dialog" aria-labelledby="sendrequest"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <form method="post" action="/dashboard/mentorship/modify" enctype="multipart/form-data">
          <div class="modal-header">
            <h4 class="modal-title">{{ __('cycles.Modify mentorship session') }} </h4>
          </div>
          <div class="modal-body">
            @csrf
            <input type="hidden" name="id" value="0" id="sid">
            <input type="hidden" name="mentorid" value="0" id="mentorid">
            <input type="hidden" name="userid" value="0" id="userid">
            <input type="hidden" name="approved" value="Yes" >
            <div class="form-group mt-4" id="showdates">
              <label class="mb-2">{{ __('cycles.Date/Time') }}:</label>
              <input class='date width-200 form-control' type="text" id="sdate" name="date" value=""
                required>
            </div>
            <div class="form-group mt-4">
              <label class="mb-2">{{ __('cycles.Meeting period') }}:</label>
              <select name="period" class="select form-control" id="period">
                <option value="30">30 {{ __('cycles.Minutes') }}</option>
                <option value="60">1 {{ __('cycles.Hour') }}</option>
                <option value="120">2 {{ __('cycles.Hours') }}</option>
                <option value="180">3 {{ __('cycles.Hours') }}</option>
                <option value="240">4 {{ __('cycles.Hours') }}</option>
                <option value="300">5 {{ __('cycles.Hours') }}</option>
              </select>
            </div>
            <div class="form-group mt-4">
              <label class="mb-2">{{ __('cycles.Add a session note') }}:</label>
              <textarea class="form-control" id="notes" name='notes' rows="4"
                placeholder="{{ __('cycles.Write your note here') }}"></textarea>
            </div>
            <div class="checkbox mt-4">
              <label for="checkbox1">{{ __('cycles.Approve & notify users') }}.</label>
              <input type="checkbox" class="checkbox-input" id="checkbox1" name="approved">
            </div>
          </div>
          <div class="modal-footer">
            <div class="btn btn-label-secondary ml-1" data-bs-dismiss="modal" aria-label="Close">
              {{-- <i class="ti ti-check"></i> --}}
              <span class="">{{ __('cycles.Close') }}</span>
            </div>
            <button type="submit" class="btn btn-primary ml-1">
              {{-- <i class="ti ti-check"></i> --}}
              <span class="">{{ __('cycles.Submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@stop

@section('scripts')
  <script src="/app-assets/plugins/alertify/alertify.min.js"></script>
@stop

@section('pagescripts')
  <script>
    $(document).on('click', '.show-passcode', function(e) {
      e.preventDefault();
      let passcode = $(this).data('passcode');
      alertify.defaults.transition = 'zoom';
      alertify.defaults.theme.ok = 'btn btn-sm btn-danger';
      alertify.defaults.theme.cancel = 'btn btn-sm btn-info';
      alertify.defaults.theme.input = 'form-control';

      alertify.alert('Meeting Passcode', `The passcode for this meeting is: <strong>${passcode}</strong>`, function() {});
    });
    $(document).ready(function() {
    $('#userstable').DataTable().destroy();
    $('#userstable').DataTable({
      "responsive": true,
      "searching": true,
      "lengthChange": false,
      "paging": true,
      "bInfo": false,
      "order": [],
      "columnDefs": [
        {
          "orderable": false,
          "targets": [0, 1, 2, 3, 4, 5, 6, 7]
        }
      ],
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
    // $('#sdate').flatpickr({
    //   enableTime: true,
    //   dateFormat: "Y-m-d h:i K",
    //   minDate: "today",
    //   time_24hr: false,
    //   allowInput: true,
    //   onClose: function(selectedDates, dateStr, instance) {
    //     // Ensure the date format matches what Carbon expects (Y-m-d h:i a)
    //     if (selectedDates.length > 0) {
    //       const formattedDate = instance.formatDate(selectedDates[0], "Y-m-d h:i K");
    //       instance.setDate(formattedDate, true, "Y-m-d h:i K");
    //     }
    //   }
    // });

    $('.datepicker').flatpickr({
      enableTime: false,
      dateFormat: "Y-m-d",
    });

    function edit(id) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/mentorship/session",
        data: {
          "id": id,
        },
        success: function(response) {
          $('#sid').val(id);
          $('#mentorid').val(response.mentor_id);
          $('#userid').val(response.user_id);
          // $('#sdate').val(response.zoom_date);
          $('#sdate').flatpickr({
            enableTime: true,
            dateFormat: "Y-m-d h:i K",
            // minDate: "today",
            time_24hr: false,
            allowInput: true,
            defaultDate: response.zoom_date ? response.zoom_date : '',
            onClose: function(selectedDates, dateStr, instance) {
              // Ensure the date format matches what Carbon expects (Y-m-d h:i a)
              if (selectedDates.length > 0) {
                const formattedDate = instance.formatDate(selectedDates[0], "Y-m-d h:i K");
                instance.setDate(formattedDate, true, "Y-m-d h:i K");
              }
            }
          });
          $('#period').val(response.period);
          $('#notes').val(response.notes);
          $('#ModifyRequest').modal('show');
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }
    const messages = {
      exportSuccess: "{{ __('cycles.export_willsuccess') }}",
    };

    document.getElementById('searchBot').addEventListener('click', redirectWithDates);

    function redirectWithDates() {
      // الحصول على قيم التواريخ
      const startDate = document.getElementById('start_date').value;
      const endDate = document.getElementById('end_date').value;
      const CycleId = document.getElementById('cycle_id').value;

      const showValue = document.getElementById('showValueSelect').value;

      // التحقق من وجود تواريخ والتحقق من تنسيقها لضمان التوافق
      let url = `/dashboard/mentorship/sessions?show=${showValue}`;

      // إذا كانت start_date و end_date موجودة، أضفها إلى الرابط
      if (startDate && endDate) {
        const formattedStartDate = formatDate(startDate);
        const formattedEndDate = formatDate(endDate);
        url += `&start_date=${encodeURIComponent(formattedStartDate)}&end_date=${encodeURIComponent(formattedEndDate)}`;
      }
      if (CycleId && CycleId !== 'all') {
        url += `&cycle_id=${CycleId}`;
      }

      // إعادة التوجيه إلى الرابط المناسب
      window.location.href = url;
    }

    function redirect(showValue) {


      // التحقق من وجود تواريخ والتحقق من تنسيقها لضمان التوافق
      let url = `/dashboard/mentorship/sessions?show=${showValue}`;

      // إذا كانت start_date و end_date موجودة، أضفها إلى الرابط


      // إعادة التوجيه إلى الرابط المناسب
      window.location.href = url;
    }

    // دالة لتحويل صيغة التاريخ إلى 'YYYY-MM-DD' لضمان الثبات
    function formatDate(date) {
      const dateObj = new Date(date);
      const year = dateObj.getFullYear();
      const month = String(dateObj.getMonth() + 1).padStart(2, '0');
      const day = String(dateObj.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }

    function mentor(id, name, rating) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/mentors/modify",
        data: {
          "id": id,
          "rtype": 5,
        },
        success: function(response) {
          $('#mentorstable tr').remove();
          let rate = '<i class="ti tis-star" style="color: #fc960f;"></i>';
          $('<tr>').html(" <td><b>#</b></td> <td><b>Days</b></td> <td><b>Time</b></td> <td><b>Notes</b></td>")
            .appendTo('#mentorstable');
          $('#name').html(name + "<span>" + rate.repeat(rating) + "</span>");
          if (response.length > 0) {
            $.each(response, function(i, item) {
              let days = '';
              let note = '';
              let time = "From " + item.tfrom + " to " + item.tto;
              if (item.notes) {
                note = item.notes;
              }
              switch (item.type) {
                case 'OneDay':
                  days = item.dfrom;
                  break;
                case 'Weekly':
                  days = 'Weekly (' + item.days + ')';
                  break;
                case 'WorkingDays':
                  days = 'All working days';
                  break;
                case 'Period':
                  days = 'from ' + item.dfrom + ' to ' + item.dto;
                  break;
                case 'Session':
                  days = new Date(item.dfrom);
                  time = 'From ' + days.toLocaleTimeString('pt-BR') + " (" + item.tfrom + "Minutes)";
                  days = days.toLocaleDateString('pt-BR') + " Busy";
                  break;
              }
              let num = i + 1;
              $('<tr>').html("<td>" + num + "</td><td>" + days + "</td><td>" + time + "</td><td>" + note +
                "</td>").appendTo('#mentorstable');
            });
          } else {
            $('<tr>').html("<td colspan='5'> No records saved.</td>").appendTo('#mentorstable');
          }
          $('#mentors').show();
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }

    function approve(id, stat) {
      if(stat == 'Yes'){
      alert('{{ __('admin.Accepting_request') }}')
      }else{
        alert('{{ __('admin.Rejecting_request') }}')
      }
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/mentors/modify",
        data: {
          "id": id,
          "stat": stat,
          'rtype': 6,
        },
        success: function(response) {
          location.reload();
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }

    function deleteme(id) {
      let url = '/dashboard/mentorship/delsession/' + id;
      let token = '{{ csrf_token() }}';
      Swal.fire({
          title: "{{ __('admin.SureDelete') }}",
          text: "{{ __('admin.Recover') }}",
          type: "warning",
          showCancelButton: true,
          confirmButtonClass: "btn-label-danger",
          confirmButtonText: "{{ __('admin.DYes') }}",
          cancelButtonText: "{{ __('admin.No') }}!",
        })
        .then((result) => {
          if (result.value) {
            $.post(url, {
                '_method': 'POST',
                '_token': token
              },
              function(response) {
                Swal.fire(
                  '{{ __('admin.Deleted') }}!',
                  '{{ __('admin.Session Deleted') }}.',
                  'success');
                window.location.href = '/dashboard/mentorship/sessions?show=all';
              }).fail(function(error) {
              console.log(error);
            });
          } else {
            console.log('Canceled')
          }
        });
    }
  </script>
@stop
