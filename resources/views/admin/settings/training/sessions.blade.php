@extends('layouts.admin')

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/tagify/tagify.css" />

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
</style>
@stop

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
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
      </section>
      @if (count($steps) > 0)
      <section>
        <div class="row">
          <div class="col-xl-12 col-md-12 col-sm-12">
            <div class="card mb-4">
              <div class="card-header" style="padding-bottom: 15px;">
                <h4 class="card-title">{{ __('cycles.Training Steps') }}</h4>
              </div>
              <hr>
              @php
              $cycleId = $steps->where('id', $id)->first()->cycle_id;
              @endphp
              <div class="card-body row">
                <div class="col-12 col-lg-6">
                  <div class="form-group">
                    <label class="form-label fs-5 mb-2">{{ __('cycles.Select Step') }}</label>
                    <select id="ccycle" class="select2 form-select"
                      onchange="window.open('/dashboard/training/sessions/'+this.options[this.selectedIndex].value,'_self');">
                      @foreach ($steps as $step)
                      @php
                      if ($step->id == $id) {
                      $ccycle = $step->cycle->id;
                      }
                      @endphp
                      <option value="{{ $step->id }}" {{ $step->id == $id ? 'selected' : '' }}>
                        {{ $step->title }}
                        ({{ $step->cycle->title }}-{{ __('cycles.Start') . ' ' . $step->cycle->start->format('d-m-Y') ??
                        '-' }})
                        - {{ optional(optional($step->cycle)->organization)->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-12 col-lg-6 mt-4 mt-lg-0">
                  <div class="d-flex flex-wrap h-100 align-items-end gap-3">
                    <button data-bs-toggle="modal" data-bs-target="#addsession" type="button"
                      class="text-nowrap btn btn-label-primary">
                      <i class="ti ti-plus me-2"></i> {{ __('cycles.Add new training') }}
                    </button>
                    <a class="text-nowrap btn btn-label-success"
                      href="/dashboard/cycles/{{ $step->id == $id ? $ccycle : '' }}">
                      <i class="ti ti-pencil me-2"></i>{{ __('cycles.Cycle') }}
                    </a>
                    <a class="text-nowrap btn btn-label-dark" href="/dashboard/training/{{ $cycleId ?? 0 }}/surveys">
                      <i class="ti ti-question-mark me-2"></i>{{ __('cycles.Manage Surveys') }}
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="row">
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card">
            <div class="card-header">
            </div>
            <div class="card-body">
              <div class="card-text">
                <br>
                <!-- Filter -->
                <form method="GET" action="{{ route('trainings.sessions_list', $id) }}"
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
                        <th>{{ __('cycles.Start') }}</th>
                        <th>{{ __('cycles.End') }}</th>
                        <th>{{ __('cycles.details') }}</th>
                        <th>{{ __('cycles.Edit') }} | {{ __('cycles.Delete') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($sessions as $key => $session)
                      <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ __('cycles.' . $session->type) }}</td>
                        <td>{{ $session->title }}</td>
                        <td>{{ $session->datetime }}</td>
                        <td>{{ $session->enddatetime ?? '-' }}</td>
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
      <section class="row">
        <div class="col-xl-12 col-md-12 col-sm-12">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title danger">{{ __('cycles.Error') }}</h4>
            </div>
            <hr style="margin:-10px 0 5px 0;">
            <div class="card-body">
              {{ __('cycles.No training sessions into cycles') }}, <a href="/dashboard/cycles">{{ __('cycles.Back to
                Manage cycles') }}</a>.
            </div>
          </div>
        </div>
      </section>
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
          <a href="{{ route('trainings.reports_session', $session->id) }}" class="btn btn-info">
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
          <a href="{{ route('trainings.reports_session', $session->id) }}" class="btn btn-info">
            {{ __('admin.update_reports') }}
          </a>
        </div>
        @endif

        <!-- Session Recordings Section -->
        @if ($session->recordings->count() > 0)
        <div class="d-flex justify-content-between align-items-center mt-4">
          <h5>{{ __('admin.session_recordings') }}</h5>
          <a href="{{ route('trainings.reports_recording', $session->id) }}" class="btn btn-warning">
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







<!-- Edit Customer Modal -->
{{-- <div class="modal fade" id="addsession" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-6">
          <h4 class="mb-2">{{__('cycles.Add new training session')}} </h4>
          <p>يمكنك من هنا التعديل امر الشغل</p>
        </div>

        <form method="post" action="/dashboard/training/addsession" enctype="multipart/form-data" autocomplete="off">
          @csrf
          <input type="hidden" name="step_id" value="{{$id}}" id="id">
          <div class="form-group mb-3" id="ctype">
            <label class="form-label fs-6 mb-1">{{__('cycles.Type')}}:</label>
            <select name="type" class="select form-control" required
              onchange="tchange(this.options[this.selectedIndex].value)">
              <option value="online">{{__('cycles.online')}}</option>
              <option value="sessions">{{__('cycles.sessions')}}</option>
              <option value="offline">{{__('cycles.offline')}}</option>
            </select>
          </div>
          <div class="form-group mb-3" id="online">
            <label class="form-label fs-6 mb-1">{{__('cycles.select online')}}:</label>
            <select name="online" class="select form-control" required id="conline">
              @if (!$online)
              <option>{{__('cycles.No saved training courses')}}</option>
              @endif
              @foreach ($online as $tr)
              <option value="{{$tr->id}}">{{$tr->title}}</option>
              @endforeach
            </select>
          </div>
          <label class="form-label fs-6 mb-1">{{__('cycles.Session Title')}}</label>
          <div class="form-group mb-3">
            <input id="title" name="title" type="text" placeholder="{{__('cycles.Session Title')}}" class="form-control"
              required>
          </div>
          <label class="form-label fs-6 mb-1">{{__('cycles.Add a description')}}:</label>
          <div class="form-group mb-3">
            <textarea class="form-control" id="dsc" name='dsc' rows="2"
              placeholder="{{__('cycles.Write your description here')}}."></textarea>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fs-6 mb-1">{{__('cycles.Date/Time')}}</label>
            <input id="datetime" name="datetime" type="text" placeholder="{{__('cycles.Date/Time')}}"
              class="form-control" required>
          </div>
          <div class="form-group mb-3" style="display: none;" id="enddatetime">
            <label class="form-label fs-6 mb-1">{{__('cycles.End')}}</label>
            <input id="enddate" name="enddatetime" type="text" placeholder="{{__('cycles.Date/Time')}}"
              class="form-control">
          </div>
          <div class="form-group mb-3" style="display: none;" id="sessions">
            <label class="form-label fs-6 mb-1">{{__('cycles.Session duration')}}:</label>
            <select name="duration" class="select form-control" required>
              <option value="30">30 {{__('cycles.Minutes')}}</option>
              <option value="60" selected>1 {{__('cycles.Hour')}}</option>
              <option value="120">2 {{__('cycles.Hours')}}</option>
              <option value="180">3 {{__('cycles.Hours')}}</option>
              <option value="240">4 {{__('cycles.Hours')}}</option>
              <option value="300">5 {{__('cycles.Hours')}}</option>
            </select>
          </div>
          <div class="form-group mb-3" id="trainer" style="display: none;">
            <label class="form-label fs-6 mb-1">{{__('cycles.trainer name')}}</label>
            <input name="trainer_name" type="text" placeholder="{{__('cycles.trainer name')}}" class="form-control">
          </div>
          <div class="form-group mb-3" id="trainer_email" style="display: none;">
            <label class="form-label fs-6 mb-1">{{__('cycles.trainer email')}}</label>
            <input name="trainer_email" type="text" placeholder="{{__('cycles.trainer email')}}" class="form-control">
          </div>
          <div class="form-group mb-3" id="location" style="display: none;">
            <label class="form-label fs-6 mb-1">{{__('cycles.training location')}}</label>
            <input name="location" type="text" placeholder="{{__('cycles.training location')}}" class="form-control">
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary ml-1">

          <span class="d-none d-sm-block">{{__('cycles.Submit')}}</span>
        </button>
        </form>



      </div>
    </div>
  </div>
</div> --}}
<!--/ Edit JO Modal -->





<!-- END: Content-->
<div class="modal fade" id="addsession" tabindex="-1" role="dialog" aria-labelledby="addsession" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content rounded p-0 p-md-4" id="modal-content">
      @include('partials.modal.modal-header', ['title' => __('cycles.Add new training session' )])
      <form id="addSessionForm" method="post" action="/dashboard/training/addsession" enctype="multipart/form-data"
        autocomplete="off">
        <div class="rounded bg-white border-0 border-md-1 border-secondary p-4">
          @csrf
         
          <input type="hidden" name="step_id" value="{{ $id }}" id="id">
          <div class="form-group mb-4" id="ctype">
            <label class="form-label fs-6 mb-1">{{ __('cycles.Type') }}:</label>
            <select id="select-type" name="type" class=" form-select" required {{--
              onchange="tchange(this.options[this.selectedIndex].value)" --}}>
              <option value="online">{{ __('cycles.online') }}</option>
              <option value="sessions">{{ __('cycles.sessions') }}</option>
              <option value="offline">{{ __('cycles.offline') }}</option>
            </select>
          </div>

          <div class="row mb-4" id="date-only">
            <div class="col-12">
              <label for="date-range-picker" class="form-label fs-6 fw-semibold">{{ __('cycles.Valid_from_to')
                }}:</label>
              <input type="hidden" name="start" id="start" />
              <input type="hidden" name="end" id="end" />
              <input type="text" name="date_range" id="date-range-picker" class="form-control" />
            </div>
          </div>

          <div class="row" id="date-time" style="display: none;">
            <div class="col-12 mb-4">
              <label for="datetime" class="form-label fs-6 mb-1">{{ __('cycles.Date') }}</label>
              <input id="datetime" name="datetime" type="text" placeholder="{{ __('cycles.Date') }}"
                class="form-control flatpickr-date" />
            </div>
            <div class="col-12">
              <div class="row">
                <div class="col-md-6 col-12 mb-6">
                  <label for="flatpickr-time-from" class="form-label">{{ __('cycles.Time From') }}</label>
                  <input type="text" class="form-control" name="starttime" placeholder="HH:MM"
                    id="flatpickr-time-from" />
                </div>
                <div class="col-md-6 col-12 mb-6">
                  <label for="flatpickr-time-to" class="form-label">{{ __('cycles.Time To') }}</label>
                  <input type="text" class="form-control" name="endtime" placeholder="HH:MM" id="flatpickr-time-to" />
                </div>
              </div>
            </div>
          </div>

          <div class="form-group mb-3" id="online-dropdown">
            <label class="form-label fs-6 mb-1">{{ __('cycles.select online') }}:</label>
            <select name="online" id="select-course" class="form-select">
              @if (!$online)
              <option>{{ __('cycles.No saved training courses') }}</option>
              @endif
              @foreach ($online as $tr)
              <option value="{{ $tr->id }}">{{ $tr->title }}</option>
              @endforeach
            </select>
          </div>
          <label class="form-label fs-6 mb-1">{{ __('cycles.Session Title') }}</label>
          <div class="form-group mb-3">
            <input id="title" name="title" type="text" placeholder="{{ __('cycles.Session Title') }}"
              class="form-control">
          </div>
          <label class="form-label fs-6 mb-1">{{ __('cycles.Add a description') }}:</label>
          <div class="form-group mb-3">
            <textarea class="form-control" id="dsc" name='dsc' rows="2"
              placeholder="{{ __('cycles.Write your description here') }}."></textarea>
          </div>
          <div class="form-group mb-3" id="location" style="display: none;">
            <label class="form-label fs-6 mb-1">{{ __('cycles.training Address') }}</label>
            <input name="training_Address" id="training_Address" type="text" placeholder="{{ __('cycles.training Address') }}"
              class="form-control">
          </div>
          <div class="form-group mb-3" id="address" style="display: none;">
            <label class="form-label fs-6 mb-1">{{ __('cycles.training location') }}</label>
            <input name="training_location" id="training_location" type="text" placeholder="{{ __('cycles.training location') }}"
              class="form-control">
          </div>
          <div class="form-group mb-3" id="trainer" style="display: none;">
            <label class="form-label fs-6 mb-1">{{ __('cycles.trainer name') }}</label>
            <input name="trainer_name" id="trainer_name" type="text" placeholder="{{ __('cycles.trainer name') }}" class="form-control">
          </div>
          <div class="form-group mb-3" id="trainer_email" style="display: none;">
            <label class="form-label fs-6 mb-1">{{ __('cycles.trainer email') }}</label>
            <input name="trainer_email" id="trainer_email" type="text" placeholder="{{ __('cycles.trainer email') }}" class="form-control">
          </div>
        </div>
        <div class="d-flex align-items-center gap-3 mt-4 p-4 p-md-0">
          <button type="button" class="btn btn-outline-secondary w-px-100" data-bs-dismiss="modal">
            {{ __('cycles.Cancel') }}
            <i class="ti ti-circle-x ms-2"></i>
          </button>
          <button type="submit" class="btn btn-primary w-px-100">
            {{ __('cycles.Submit') }}
            <i class="ti ti-circle-check ms-2"></i>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@foreach ($sessions as $session)
<div class="modal fade text-left" id="editsession" tabindex="-1" role="dialog" aria-labelledby="editsession"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content rounded p-0 p-md-6" id="modal-content">
      <form id="editSessionForm" method="post" action="{{ route('trainings.sessions_edit',$session->id) }}" enctype="multipart/form-data"
        autocomplete="off">
        @include('partials.modal.modal-header', ['title' => __('cycles.Edit training session')])
        <div class="rounded bg-white border-0 border-md-1 border-secondary p-4">
          @csrf
          @method('PUT')
          <div>
            <input type="hidden" name="sid" value="{{ $id }}" id="id">
            <div class="form-group mb-4" id="ctype">
              <label class="form-label fs-6 mb-1">{{ __('cycles.Type') }}:</label>
              <select id="select-type" name="type" class="select2 form-control" required disabled>
                <option value="online" {{ old('type', $session->type) == 'online' ? 'selected' : '' }}>{{ __('cycles.online') }}</option>
                <option value="sessions" {{ old('type', $session->type) == 'sessions' ? 'selected' : '' }}>{{ __('cycles.sessions') }}</option>
                <option value="offline" {{ old('type', $session->type) == 'offline' ? 'selected' : '' }}>{{ __('cycles.offline') }}</option>
             </select>
             
            </div>
            <div class="row mb-4" id="date-only">
              <div class="col-12">
                <label for="date-range-picker" class="form-label fs-6 fw-semibold">{{ __('cycles.Valid_from_to')
                  }}:</label>
                <input type="hidden" name="start" id="start" />
                <input type="hidden" name="end" id="end" />
                <input type="text" name="date_range" id="date-range-picker" class="form-control date-range-picker" />
              </div>
            </div>
            <div class="row" id="date-time" style="display: none;">
              <div class="col-12 mb-4">
                <label for="datetime" class="form-label fs-6 mb-1">{{ __('cycles.Date') }}</label>
                <input id="datetime" name="datetime" type="text" placeholder="{{ __('cycles.Date') }}"
                  class="form-control flatpickr-date" />
              </div>
              <div class="col-12">
                <div class="row">
                  <div class="col-md-6 col-12 mb-6">
                    <label for="flatpickr-time-from" class="form-label">{{ __('cycles.Time From') }}</label>
                    <input type="text" class="form-control" name="starttime" placeholder="HH:MM"
                      id="flatpickr-time-from" />
                  </div>
                  <div class="col-md-6 col-12 mb-6">
                    <label for="flatpickr-time-to" class="form-label">{{ __('cycles.Time To') }}</label>
                    <input type="text" class="form-control" name="endtime" placeholder="HH:MM" id="flatpickr-time-to" />
                  </div>
                </div>
              </div>
            </div>
            <div class="form-group mb-3" id="online-dropdown" style="display: none;">
              <label class="form-label fs-6 mb-1">{{ __('cycles.select online') }}:</label>
              <select name="online" id="select-course" class="form-select" id="conline">
                @if (!$online)
                <option>{{ __('cycles.No saved training courses') }}</option>
                @endif
                @foreach ($online as $tr)
                <option value="{{ $tr->id }}">{{ $tr->title }}</option>
                @endforeach
              </select>
            </div>
            <label class="form-label fs-6 mb-1">{{ __('cycles.Session Title') }}</label>
            <div class="form-group mb-3">
              <input id="title" name="title" type="text" placeholder="{{ __('cycles.Session Title') }}"
                class="form-control">
            </div>
            <label class="form-label fs-6 mb-1">{{ __('cycles.Add a description') }}:</label>
            <div class="form-group mb-3">
              <textarea class="form-control" id="dsc" name='dsc' rows="2"
                placeholder="{{ __('cycles.Write your description here') }}."></textarea>
            </div>
            <div class="form-group mb-3" id="location" style="display: none;">
              <label class="form-label fs-6 mb-1">{{ __('cycles.training Address') }}</label>
              <input name="training_Address" id="training_Address" type="text" placeholder="{{ __('cycles.training Address') }}"
                class="form-control">
            </div>
            <div class="form-group mb-3" id="address" style="display: none;">
              <label class="form-label fs-6 mb-1">{{ __('cycles.training location') }}</label>
              <input name="training_location" id="training_location" type="text" placeholder="{{ __('cycles.training location') }}"
                class="form-control">
            </div>
            <div class="form-group mb-3" id="trainer" style="display: none;">
              <label class="form-label fs-6 mb-1">{{ __('cycles.trainer name') }}</label>
              <input name="trainer_name" id="trainer_name" type="text" placeholder="{{ __('cycles.trainer name') }}" class="form-control">
            </div>
            <div class="form-group mb-3" id="trainer_email" style="display: none;">
              <label class="form-label fs-6 mb-1">{{ __('cycles.trainer email') }}</label>
              <input name="trainer_email" id="trainer_email" type="text" placeholder="{{ __('cycles.trainer email') }}" class="form-control">
            </div>
          </div>
          <div class="d-flex align-items-center gap-3 mt-4 p-4 p-md-0">
            <button type="button" class="btn btn-outline-secondary w-px-100" data-bs-dismiss="modal">
              {{ __('cycles.Cancel') }}
              <i class="ti ti-circle-x ms-2"></i>
            </button>
            <button type="submit" class="btn btn-primary w-px-100">
              {{ __('cycles.Save') }}
              <i class="ti ti-circle-check ms-2"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach


<!-- المودال نفسه -->
{{-- <div class="modal fade" id="Attendance" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="qrCodeModalLabel">{{ __('admin.Attendance') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>{{ __('admin.Traning_title') }}</th>
              <th>{{ __('admin.Trainer_name') }}</th>
              <th>{{ __('admin.Program_Name') }}</th>
              <th>{{ __('admin.StartUp_name') }}</th>
              <th>{{ __('admin.Attendance') }}</th>
            </tr>
          </thead>
          <tbody class="Attendance_body">
            <tr>
              <td class="text-center Traning_title"></td>
              <td class="text-center Trainer_name"></td>
              <td class="text-center Program_Name"></td>
              <td class="text-center StartUp_name"></td>
              <td class="text-center Attendance"></td>
            </tr>
          </tbody>
        </table>

      </div>

    </div>
  </div>
</div> --}}
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
@stop

@section('pagescripts')
<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>

<script>
    // $(document).ready(function () {
    //    function toggleOnlineDropdown() {
    //       var selected = $('#select-type').val();
    //    if (selected === 'online') {
    //         $('#online-dropdown').show();
    //    } else {
    //      $('#online-dropdown').hide();
    //    }
    //  }

    //   $('.select2').select2();

    //   toggleOnlineDropdown();

    //   $('#select-type').on('change', function () {
    //     toggleOnlineDropdown();
    //   });
    // });

     $(`#addSessionForm .flatpickr-date`).on('change', function() {
      const dateValue = $(this).val();
      if (dateValue) {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
      }
    });
    $(`#addSessionForm #flatpickr-time-from`).on('change', function() {
      const timeFromValue = $(this).val();
      if (timeFromValue) {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
      }
    });
    $(`#addSessionForm #flatpickr-time-to`).on('change', function() {
      const timeToValue = $(this).val();
      if (timeToValue) {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
      }
    });
    $(`#addSessionForm #title`).on('change', function() {
      const titleValue = $(this).val();
      if (titleValue) {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
      }
    });
    $(`#editSessionForm .flatpickr-date`).on('change', function() {
      const dateValue = $(this).val();
      if (dateValue) {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
      }
    });
    $(`#editSessionForm #flatpickr-time-from`).on('change', function() {
      const timeFromValue = $(this).val();
      if (timeFromValue) {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
      }
    });
    $(`#editSessionForm #flatpickr-time-to`).on('change', function() {
      const timeToValue = $(this).val();
      if (timeToValue) {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
      }
    });
    // $(`#editSessionForm #title`).on('change', function() {
    //   const titleValue = $(this).val();
    //   if (titleValue) {
    //     $(this).removeClass('is-invalid');
    //     $(this).next('.invalid-feedback').remove();
    //   }
    // });
    
    $('#addSessionForm').on('submit', function(e) {
      const formId = '#addSessionForm';
      const typeValue = $(`${formId} #select-type`).val();
      const dateRangePicker = $(`${formId} #date-range-picker`);
      const dateRangeValue = dateRangePicker.val();
      const dateValue = $(`${formId} .flatpickr-date`).val();
      const timeFromValue = $(`${formId} #flatpickr-time-from`).val();
      const timeToValue = $(`${formId} #flatpickr-time-to`).val();
      const titleValue = $(`${formId} #title`).val();
    
      if (typeValue === 'online') {
        if (!dateRangeValue) {
          dateRangePicker.addClass('is-invalid').after('<div class="invalid-feedback">Please select a date range for online sessions.</div>');
          e.preventDefault();
          return;
        }
        if (!titleValue) {
          $(`${formId} #title`).addClass('is-invalid').after('<div class="invalid-feedback">Please enter a title.</div>');
          e.preventDefault();
          return;
        }
      }  

      if (typeValue === 'offline' || typeValue === 'sessions') {
       if (!dateValue) {
          $(`${formId} .flatpickr-date`).addClass('is-invalid').after('<div class="invalid-feedback">Please select a date.</div>');
          e.preventDefault();
          return
        }
        if (!timeFromValue) {
          $(`${formId} #flatpickr-time-from`).addClass('is-invalid').after('<div class="invalid-feedback">Please select a start time.</div>');
          e.preventDefault();
          return
        }
        if (!timeToValue) {
          $(`${formId} #flatpickr-time-to`).addClass('is-invalid').after('<div class="invalid-feedback">Please select an end time.</div>');
          e.preventDefault();
          return
        } 
        if (!titleValue) {
          $(`${formId} #title`).addClass('is-invalid').after('<div class="invalid-feedback">Please enter a title.</div>');
          e.preventDefault();
          return;
        }
      }  

    });

    $('#addSessionForm #select-type').on('change', function(e) {
      const value = e.target.value;

      if (value === 'offline') {
        $('#addSessionForm #online-dropdown').hide();
        $('#addSessionForm #date-only').hide();
        $('#addSessionForm #date-time').show();
        $('#addSessionForm #trainer').show();
        $('#addSessionForm #trainer_email').show();
        $('#addSessionForm #location').show();
        $('#addSessionForm #address').show();
      } else if (value === 'sessions') {
        $('#addSessionForm #online-dropdown').hide(); 
        $('#addSessionForm #date-only').hide();
        $('#addSessionForm #date-time').show();
        $('#addSessionForm #trainer').show();
        $('#addSessionForm #trainer_email').show();
        $('#addSessionForm #location').hide();
        $('#addSessionForm #address').hide();
      } else if (value === 'online') {
        $('#addSessionForm #online-dropdown').show();
        $('#addSessionForm #date-only').show();
        $('#addSessionForm #date-time').hide();
        $('#addSessionForm #trainer').hide();
        $('#addSessionForm #trainer_email').hide();
        $('#addSessionForm #location').hide();
        $('#addSessionForm #address').hide();
      }
    });

    $('#addSessionForm #date-range-picker').daterangepicker({
      opens: isRtl ? 'left' : 'right',
      drops: 'up',
      autoUpdateInput: false,
      minDate: moment().format('MM/DD/YYYY'),
      }).on('apply.daterangepicker', function(ev, picker) {
        $('#addSessionForm #start').val(picker.startDate.format('YYYY-MM-DD'));
        $('#addSessionForm #end').val(picker.endDate.format('YYYY-MM-DD'));
        $('#addSessionForm #date-range-picker').val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        $('#addSessionForm #date-range-picker').removeClass('is-invalid');
        $('#addSessionForm #date-range-picker').next('.invalid-feedback').remove();
      }).on('cancel.daterangepicker', function(ev, picker) {
        $('#addSessionForm #date-range-picker').val('');
        $('#addSessionForm #start').val('');
        $('#addSessionForm #end').val('');
      });

    $('#addSessionForm #flatpickr-time-from').flatpickr({
      enableTime: true,
      noCalendar: true
    });
    $('#addSessionForm #flatpickr-time-to').flatpickr({
      enableTime: true,
      noCalendar: true
    });


    /// Edit Session Form

    $('#editSessionForm').on('submit', function(e) {
      e.preventDefault(); // Prevent default form submission initially
      
      const formId = '#editSessionForm';
      const typeValue = $(`${formId} #select-type`).val();
      const dateRangePicker = $(`${formId} #date-range-picker`);
      const dateRangeValue = dateRangePicker.val();
      const dateValue = $(`${formId} .flatpickr-date`).val();
      const timeFromValue = $(`${formId} #flatpickr-time-from`).val();
      const timeToValue = $(`${formId} #flatpickr-time-to`).val();
      const titleValue = $(`${formId} #title`).val();
      // console.log('titleValue', titleValue);
      
      let isValid = true;

      if (typeValue === 'online') {
        if (!dateRangeValue) {
          dateRangePicker.addClass('is-invalid').after('<div class="invalid-feedback">Please select a date range for online sessions.</div>');
          isValid = false;
        }
        if (!titleValue) {
          $(`${formId} #title`).addClass('is-invalid').after('<div class="invalid-feedback">Please enter a title.</div>');
          isValid = false;
        }
      }  

      if (typeValue === 'offline' || typeValue === 'sessions') {
        if (!dateValue) {
          $(`${formId} .flatpickr-date`).addClass('is-invalid').after('<div class="invalid-feedback">Please select a date.</div>');
          isValid = false;
        }
        if (!timeFromValue) {
          $(`${formId} #flatpickr-time-from`).addClass('is-invalid').after('<div class="invalid-feedback">Please select a start time.</div>');
          isValid = false;
        }
        if (!timeToValue) {
          $(`${formId} #flatpickr-time-to`).addClass('is-invalid').after('<div class="invalid-feedback">Please select an end time.</div>');
          isValid = false;
        } 
        if (!titleValue) {
          $(`${formId} #title`).addClass('is-invalid').after('<div class="invalid-feedback">Please enter a title.</div>');
          isValid = false;
        }
      }
      
      if (isValid) {
        // Submit the form if all validations pass
        this.submit();
      }
    });

    $('#editSessionForm #select-type').on('change', function(e) {
      const value = e.target.value;

      if (value === 'offline') {
        $('#editSessionForm #date-only').hide();
        $('#editSessionForm #date-time').show();
        $('#editSessionForm #trainer').show();
        $('#editSessionForm #trainer_email').show();
        $('#editSessionForm #location').show();
        $('#editSessionForm #address').show();
      } else if (value === 'sessions') {
        $('#editSessionForm #date-only').hide();
        $('#editSessionForm #date-time').show();
        $('#editSessionForm #trainer').show();
        $('#editSessionForm #trainer_email').show();
        $('#editSessionForm #location').hide();
        $('#editSessionForm #address').hide();
      } else {
        $('#editSessionForm #date-only').show();
        $('#editSessionForm #date-time').hide();
      }
    });

    // $('#editSessionForm #date-range-picker').daterangepicker({
    //   opens: isRtl ? 'left' : 'right',
    //   drops: 'up',
    //   autoUpdateInput: false
    //   }).on('apply.daterangepicker', function(ev, picker) {
    //     $('#editSessionForm #start').val(picker.startDate.format('YYYY-MM-DD'));
    //     $('#editSessionForm #end').val(picker.endDate.format('YYYY-MM-DD'));
    //     $('#editSessionForm #date-range-picker').val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    //     $('#editSessionForm #date-range-picker').removeClass('is-invalid');
    //     $('#editSessionForm #date-range-picker').next('.invalid-feedback').remove();
    //   }).on('cancel.daterangepicker', function(ev, picker) {
    //     $('#editSessionForm #date-range-picker').val('');
    //     $('#editSessionForm #start').val('');
    //     $('#editSessionForm #end').val('');
    //   });

    $('#editSessionForm #flatpickr-time-from').flatpickr({
      enableTime: true,
      noCalendar: true
    });
    $('#editSessionForm #flatpickr-time-to').flatpickr({
      enableTime: true,
      noCalendar: true
    });
  
    $('#addSessionForm .flatpickr-date').flatpickr({
      monthSelectorType: 'static',
      minDate: "today",
    });

    $('#editSessionForm .flatpickr-date').flatpickr({
      monthSelectorType: 'static',
      minDate: "today",
    });

    // $('#startTime').flatpickr({
    //   enableTime: true,
    //   noCalendar: true
    // });

    // $('#endTime').flatpickr({
    //   enableTime: true,
    //   noCalendar: true
    // });

    // $('#estartTime').flatpickr({
    //   enableTime: true,
    //   noCalendar: true
    // });

    // $('#eendTime').flatpickr({
    //   enableTime: true,
    //   noCalendar: true
    // });
    // $('.flatpickr-datefilter').flatpickr({
    //   monthSelectorType: 'static',
    // });
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
          title: "{{ __('admin.SureDelete') }}",
          text: "{{ __('admin.Recover') }}",
          icon: "warning",
          showCancelButton: true,
          confirmButtonClass: "btn-primary",
          confirmButtonText: "{{ __('admin.DYes') }}",
          cancelButtonText: "{{ __('admin.No') }}",
          reverseButtons: true,
          customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false 
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
                  '{{ __('admin.Course Deleted') }}.',
                  'success');
                window.location.href = '/dashboard/training/sessions/{{ $id }}';
              }).fail(function(error) {
              console.log(error);
            });
          } else {
            console.log('Canceled')
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