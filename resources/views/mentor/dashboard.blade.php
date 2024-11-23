@extends('layouts.admin')

@section('title', __('admin.Dashboard'))
@section('subTitle', __('admin.My Dashboard'))

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-logistics-dashboard.css" />
@stop

@section('content')
  @php
    $countCards = [
        [
            'color' => 'success',
            'href' => '/dashboard/directory',
            'icon' => 'ti ti-briefcase',
            'text' => __('dashboard.Services'),
            'count' => $services_count,
        ],
        [
            'color' => 'primary',
            'href' => '/dashboard/inbox',
            'icon' => 'ti ti-message',
            'text' => __('dashboard.newmessages'),
            'count' => $nm_count,
        ],
        [
            'color' => 'secondary',
            'href' => '#',
            'icon' => 'ti ti-chalkboard',
            'text' => __('dashboard.Coming sessions'),
            'count' => count($mysessions),
        ],
        [
            'color' => 'warning',
            'href' => '#',
            'icon' => 'ti ti-calendar',
            'text' => __('dashboard.Cycles'),
            'count' => count($mycycles),
        ],
        [
            'color' => 'info',
            'href' => '#',
            'icon' => 'ti ti-server',
            'text' => __('admin.Company Attendance Rate'),
            'count' => $attends_rate,
        ],
        [
            'color' => 'danger',
            'href' => '#',
            'icon' => 'ti ti-star',
            'text' => __('cycles.Rate'),
            'count' => auth()->user()->mrating(),
            'isRating' => true, // Only this card has the isRating key
        ],
        [
            'color' => 'warning',
            'href' => '#',
            'icon' => 'ti ti-select',
            'text' => __('mentor.mentorshipRequests.withoutTasks'),
            'count' => $sessionWithoutTasks,
        ],
        [
            'color' => 'success',
            'href' => '#',
            'icon' => 'ti ti-check',
            'text' => __('mentor.mentorshipTasks.completedTasks'),
            'count' => $completedTasks,
        ],
        [
            'color' => 'info',
            'href' => '#',
            'icon' => 'ti ti-archive',
            'text' => __('mentor.mentorshipTasks.nonCompletedTasks'),
            'count' => $nonCompletedTasks,
        ],
    ];

    $scheduleHeadArr = [
        '#',
        __('dashboard.Days'),
        __('dashboard.Time'),
        __('dashboard.Notes'),
        __('dashboard.Actions'),
    ];

    $sessionsHeadArr = [
        '#',
        __('dashboard.Company'),
        __('dashboard.User'),
        __('dashboard.Date'),
        __('dashboard.Admin Notes'),
        __('dashboard.Meeting Link'),
    ];
  @endphp

  <!-- Content wrapper -->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-body">
        @if (session()->has('msg'))
          <div class="col-12">
            <div class="row">
              <div class="col-md-12">
                <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2" role="alert">
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                      <i class="ti ti-{{ session()->get('class') == 'success' ? 'like' : 'error' }}"></i>
                      <span>
                        {{ __('admin.' . session()->get('msg')) }}
                        {{ Session::forget('msg') }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>
      <!-- Count cards -->
      <div class="row g-6">
        @foreach ($countCards as $card)
          <a href="{{ $card['href'] }}" class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-{{ $card['color'] }} h-100">
              <div class="card-body">
                <div class="d-flex justify-content-center align-items-center mb-2">
                  <div class="avatar me-4">
                    <span class="avatar-initial rounded bg-label-primary">
                      <i class="{{ $card['icon'] }} text-{{ $card['color'] }} ti-28px"></i>
                    </span>
                  </div>
                  @if (isset($card['isRating']) && $card['isRating'])
                    <h4 class="mb-0 text-center">{{ intVal($card['count']) }}</h4>
                  @else
                    <h4 class="mb-0 text-center">{{ $card['count'] }}</h4>
                  @endif
                </div>
                <p class="mb-1 text-center">{{ $card['text'] }}</p>
              </div>
            </div>
          </a>
        @endforeach
      </div>
      <!-- / Count cards -->

      <div class="row g-3 mt-6">
        <!-- Latest Services -->
        <div class="col-sm-6 col-12">
          <div class="card">
            <div class="d-flex justify-content-between align-items-center px-6 mt-2 border-bottom" style="width: '100%">
              <div class="d-flex align-items-center py-2">
                <i class="ti ti-devices-question ti-24px"></i>
                <h5 class="card-title mb-0 mx-2">{{ __('dashboard.Latest services') }}</h5>
              </div>
            </div>
            <div class="card-body">
              @if (count($services_latest) > 0)
                @foreach ($services_latest as $serv)
                  <div class="d-flex justify-content-between align-items-center my-2">
                    <div class="widget-todo-title-area d-flex align-items-center">
                      <a href="/dashboard/directory?find={{ $serv->{'name_' . $l} }}">
                        <span class="widget-todo-title ml-50 text-secondary">
                          <i class="ti ti-link"></i>
                          {{ $serv->{'name_' . $l} }}</span>
                      </a>
                    </div>
                    <div class="widget-todo-item-action d-flex align-items-center">
                      <div class="btn btn-label-primary py-1 rounded d-flex align-items-center justify-conetnt-center"
                        style="width: 200px; height: 30px">
                        <a href="/dashboard/directory?type={{ $serv->serviceCategory->id }}">
                          {{ $serv->serviceCategory->{'name_' . $l} }}
                        </a>
                      </div>
                    </div>
                  </div>
                @endforeach
              @else
                <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                  <div class="widget-todo-title-area d-flex align-items-center">
                    <span class="widget-todo-title ml-50">
                      <i class="bx bxs-bell-ring text-danger"></i>
                      {{ __('dashboard.No services added') }}.</span>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
        <!-- / Latest Services -->

        <!-- Latest Events -->
        <div class="col-sm-6 col-12">
          <div class="card">
            <div class="d-flex justify-content-between align-items-center px-6 mt-2 border-bottom">
              <div class="d-flex align-items-center py-2">
                <i class="ti ti-calendar-event ti-24px"></i>
                <h5 class="card-title mb-0 mx-2">{{ __('dashboard.Latest Events') }}</h5>
              </div>
            </div>
            <div class="card-body">
              @if (count($event_latest) > 0)
                @foreach ($event_latest as $event)
                  <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                    <div class="widget-todo-title-area d-flex align-items-center">
                      <a href="/dashboard/calendar">
                        <span class="widget-todo-title ml-50 text-secondary">
                          <i class="ti ti-link"></i>
                          {{ $event->title }}
                        </span>
                      </a>
                    </div>
                    <div
                      class="btn btn-label-success text-success py-1 rounded d-flex align-items-center justify-conetnt-center"
                      style="width: 200px; height: 30px;">
                      {{ date('d-m-Y', strtotime($event->start)) }}
                    </div>
                  </div>
                @endforeach
              @else
                <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                  <div class="widget-todo-title-area d-flex align-items-center">
                    <span class="widget-todo-title ml-50">
                      <i class="bx bxs-bell-ring text-danger"></i>
                      {{ __('dashboard.No Events added') }} '<a
                        href="/dashboard/calendar">{{ __('dashboard.Add yours now') }}</a>'.</span>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
        <!-- / Latest Events -->
      </div>

      <!-- Schdule Meetings Tabls -->
      <div class="card mt-10">
        <h5 class="card-header d-flex justify-content-between align-items-center" style="width: 100%">
          <span style="text-transform: capitalize">
            <i class="ti ti-calendar-plus ti-24px"></i>
            {{ __('dashboard.My current schedule') }}
          </span>
          <button onclick="modify(0,0,0)" type="button" class="btn-sm btn-label-primary rounded">
            <i class="ti ti-plus ti-24px"></i>
          </button>
        </h5>
        @if (count($myschedules))
          <div class="table-responsive text-nowrap">
            <table class="table">
              <thead>
                <tr>
                  @foreach ($scheduleHeadArr as $head)
                    <th>{{ $head }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach ($myschedules as $key => $schedule)
                  <tr class="text-{{ $schedule->active ? 'primary' : 'danger' }}">
                    <td>{{ $key + 1 }}</td>
                    <td>
                      @switch($schedule->type)
                        @case('Weekly')
                          Weekly({{ str_replace(['[', ']', '"'], ' ', json_encode($schedule->days)) }})
                        @break

                        @case('WorkingDays')
                          {{ __('dashboard.All working days') }}
                        @break

                        @case('Period')
                          {{ __('dashboard.from') . $schedule->dfrom->format('d-m-Y') . __('dashboard.to') . $schedule->dto->format('d-m-Y') }}
                        @break

                        @default
                          {{ $schedule->dfrom->format('d-m-Y') }}
                      @endswitch
                    </td>
                    <td>{{ __('dashboard.from') }} {{ substr($schedule->tfrom, 0, 5) }}
                      {{ __('dashboard.to') }} {{ substr($schedule->tto, 0, 5) }}</td>
                    <td>{{ $schedule->notes ? $schedule->notes : '...' }}</td>
                    <td>
                      <div class="dropdown">
                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                          <i class="ti ti-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu">
                          <button class="dropdown-item" onclick="modify(1,{{ $schedule->id }},0)" type="button">
                            <i class="ti ti-pencil me-2"></i> Edit
                          </button>
                          <button class="dropdown-item" onclick="modify(2,{{ $schedule->id }},{{ $schedule->active }})"
                            type="button">
                            <i class="ti ti-{{ $schedule->active ? 'eye' : 'eye-off' }} me-2"></i>
                            {{ $schedule->active ? 'Deactivate' : 'Activate' }}
                          </button>
                          <button onclick="modify(3,{{ $schedule->id }},0)" type="button" class="dropdown-item"><i
                              class="ti ti-trash me-2"></i> Delete</button>
                        </div>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
      <!--/ Schdule Meetings Tabls -->

      <!-- Mentor Sessions Table -->
      <div class="card mt-10">
        <h5 class="card-header" style="text-transform: capitalize">
          <i class="ti ti-calendar-stats ti-24px"></i>
          {{ __('dashboard.My next mentorship sessions') }}
        </h5>
        @if (count($mysessions))
          <div class="table-responsive text-nowrap">
            <table class="table">
              <thead>
                <tr>
                  @foreach ($sessionsHeadArr as $head)
                    <th>{{ $head }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach ($mysessions as $key => $session)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                      <a class="text-dark" href="/dashboard/companyProfile/{{ $session->user->company->id }}">
                        <img src="{{ $session->user->company->logo_url }}" alt="avatar" class="rounded-circle"
                          height="40" width="40">
                        <span class="fw-medium"> {{ $session->user->company->name }}</span>
                      </a>
                    </td>
                    <td>
                      <a class="text-dark d-flex align-items-center gap-2" {{-- href="/dashboard/sho/{{ $session->user->id }}" --}}>
                        <img src="{{ $session->user->image_url }}" alt="avatar" class="rounded-circle"
                        height="35" width="35" style="border: 2px solid #ddd;">
                        <div class="media-body">
                          <h6 class="media-heading mb-0" style="font-size: 14px;">{{ $session->user->name }}</h6>
                          <span class="font-small-2" style="font-size:11px;">{{ $session->user->title }}</span>
                        </div>
                      </a>
                    </td>
                    <td>
                      {{ $session->zoom_date }} ({{ $session->period }}M)
                    </td>
                    <td>
                      {{ $session->admin_comment ?? '...' }}
                    </td>
                    <td>
                      @if (now()->format('Y-m-d') == date('Y-m-d', strtotime($session->zoom_date)))
                      <a href="{{ $session->start_url }}" target="_blank">
                        <button title="Start Meeting" type="button" data-value="{{ $session->id }}" type="button"
                          class="btn btn-label-primary rounded">
                          <i class="ti ti-user"></i>
                        </button>
                      </a>
                      @else
                        <i class="ti ti-alarm"></i> {{ __('admin.Meeting_will_start_at_time') }}
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
      <!--/ Mentor Sessions Table -->
    </div>

    <!-- Modal -->
    <div class="modal fade" id="SendRequest" tabindex="-1" aria-labelledby="sendrequest" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <form method="post" action="/dashboard/mentors/modify" enctype="multipart/form-data" autocomplete="off">
            <div class="modal-header">
              <h5 class="modal-title">{{ __('dashboard.Add new') }} <span id="rname"></span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              @csrf
              <div class="mb-3">
                <label for="type" class="form-label">{{ __('dashboard.Choose type') }}:</label>
                <select name="type" class="select2 form-select" id="type" onchange="SelectType(this.value)"
                  required>
                  <option value="OneDay">{{ __('dashboard.Add one day') }}</option>
                  <option value="Weekly">{{ __('dashboard.Select weekly days and times') }}</option>
                  {{--  <option value="WorkingDays">{{__('dashboard.Add working days times')}}</option>  --}}
                  <option value="Period">{{ __('dashboard.Add period') }}</option>
                </select>
              </div>
              <input type="hidden" name="rtype" value="0" id="rtype">
              <input type="hidden" name="id" value="0" id="sid">
              <div id="showdays" class="mb-3" style="display: none">
                <label for="days" class="form-label"
                  style="text-transform: capitalize">{{ $l === 'ar' ? 'اختر الايام' : 'Selecte days' }}:</label>
                <select name="days[]" class="select2 form-select" multiple id="days">
                  <option value="Sat">{{ __('dashboard.Saturday') }}</option>
                  <option value="Sun">{{ __('dashboard.Sunday') }}</option>
                  <option value="Mon">{{ __('dashboard.Monday') }}</option>
                  <option value="Tue">{{ __('dashboard.Tuesday') }}</option>
                  <option value="Wed">{{ __('dashboard.Wednesday') }}</option>
                  <option value="Thu">{{ __('dashboard.Thursday') }}</option>
                  <option value="Fri">{{ __('dashboard.Friday') }}</option>
                </select>
              </div>
              <div class="mb-3" id="showdates">
                <label for="dfrom" class="form-label">{{ __('dashboard.Date') }}:</label>
                <input class="form-control" type="date" id="dfrom" name="dfrom" value="" required>
                <div id="showtto" style="display: none">
                  <label for="dto" class="form-label">{{ __('dashboard.to') }}:</label>
                  <input class="form-control" type="date" id="dto" name="dto" value="">
                </div>
              </div>
              <div class="mb-3">
                <label for="tfrom" class="form-label">{{ __('dashboard.Time from') }}:</label>
                <input class="form-control" type="time" id="tfrom" name="tfrom" value="" required>
                <label for="tto" class="form-label">{{ __('dashboard.to') }}:</label>
                <input class="form-control" type="time" id="tto" name="tto" value="" required>
              </div>
              <div class="mb-3">
                <label for="notes" class="form-label">{{ __('dashboard.Add a Note') }}:</label>
                <textarea class="form-control" id="notes" name="notes" rows="4"
                  placeholder="{{ __('dashboard.Write your note here') }}."></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">
                
                <span class="d-none d-sm-block">{{ __('dashboard.Submit') }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- / Modal -->
  </div>
  <!-- / Content wrapper -->
@stop

@section('scripts')
  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

  <!-- Page JS -->
  <script src="/vuexy/assets/js/pages-account-settings-account.js"></script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>

  <script>
    $(document).ready(function() {
      $('#dfrom, #dto').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
      });
    });
  </script>

  <script>
    function modify(type, id, stat) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      if (type === 3) {
        //delete
        if (confirm("{{ __('dashboard.sure del schedule') }}")) {
          $.ajax({
            type: "POST",
            url: "/dashboard/mentors/modify",
            data: {
              "id": id,
              "rtype": type,
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
      } else if (type === 2) {
        //Enable-disable
        $.ajax({
          type: "POST",
          url: "/dashboard/mentors/modify",
          data: {
            "id": id,
            "rtype": type,
            "stat": stat
          },
          success: function(response) {
            location.reload();
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          }
        });
      } else if (type === 1) {
        // Edit current
        $.ajax({
          type: "POST",
          url: "/dashboard/mentors/modify",
          data: {
            "id": id,
            "rtype": type,
          },
          success: function(response) {
            switchtypes(response.type);
            $('#sid').val(response.id);
            $('#rtype').val(4);
            $('#type').val(response.type);
            $('#days').val(response.days);
            $('#dfrom').val(response.dfrom);
            $('#dto').val(response.dto);
            $('#tfrom').val(response.tfrom);
            $('#tto').val(response.tto);
            $('#notes').val(response.notes);
            $('#SendRequest').modal('show');
          },
          error: function(error) {
            console.log(error);
            alert("Error Occurred, Try again!");
          }
        });
      } else {
        //show from as new
        $('#sid, #type, #days, #dfrom, #dto, #tfrom, #tto, #notes').val();
        $('#rtype').val('0');
        switchtypes(type);
        $('#SendRequest').modal('show');
      }
    }

    function SelectType(type) {
      switchtypes(type);
    }

    function switchtypes(type) {
      switch (type) {
        case 'OneDay':
          $('#showdates').show();
          $('#showtto, #showdays').hide();
          $("#dfrom").prop('required', true);
          $("#days, #dto").prop('required', false);
          break;
        case 'Weekly':
          $('#showdates').hide();
          $('#showdays').show();
          $("#days").prop('required', true);
          $("#dfrom, #dto").prop('required', false);
          break;
        case 'WorkingDays':
          $('#showdates, #showdays').hide();
          $("#dfrom, #dto, #days").prop('required', false);
          break;
        case 'Period':
          $('#showtto,#showdates').show();
          $('#showdays').hide();
          $("#dfrom, #dto").prop('required', true);
          $("#days").prop('required', false);
          break;
      }
    }

    $('.start_meeting').click(function () {
        $.ajax({
            type: "GET", url: "/dashboard/mentor/meetingRedirect/"+$(this).data('value'),
            success: function (response) {
                // alert(response['message']);
                window.open(response['join_url'], '_blank');
{{--                window.location.href ='{{ route('mentorshipTasks.index') }}'+'?mentorship_id='+response['mentorship_id'];--}}
            },
            error: function (error) {
                console.log(error);
                alert("Error Occurred, Try again!");
            }
        });
    });
  </script>
@stop
