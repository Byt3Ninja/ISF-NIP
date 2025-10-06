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
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Alert -->
    @if (session()->has('message') || session()->has('message2'))
     <x-alert 
        type="{{ session()->get('class') ?? 'danger' }}"
        message="{{ session()->has('message') ? __(session('message')) : __('admin.' . session('message2')) }}" 
      />
    @endif

    <div class="card">
      <!-- Heading & Actions -->
      <div class="card-header d-md-flex justify-content-between border-bottom pb-3 mb-6">
        <h4 class="card-title">{{ __('admin.Training sessions') }}</h4>
        <div class="d-flex align-items-center gap-3">
          <div class="btn-group">
            <button type="button" class="btn btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="ti ti-file-export me-2"></i>{{ __('settings.Export') }}
            </button>
            <ul class="dropdown-menu">
              @foreach(['csv', 'excel'] as $key => $option)
                <li>
                  <a class="dropdown-item text-uppercase" href="javascript:void(0)" onclick="exportTraining('{{$option}}')">
                    {{$option}}
                  </a>
                </li>
              @endforeach
            </ul>
          </div>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addsession" type="button">
            <i class="ti ti-plus me-2"></i>{{ __('admin.Add_New') }}
          </button>
        </div>
      </div>

      <div class="card-body">
        <!-- Table Filter -->
        <div class="row d-flex justify-content-end mb-2" id="tblTraining-filters">
          <div class="col-12 col-md-2">
            <div class="from-group mb-2 mb-md-0">
              <select class="form-select form-select-sm" id="type-filter">
                <option value="" selected>{{ __('cycles.Select_Training_Type') }}</option>
                @foreach(['Online_Training', 'Online_Session', 'Offline_Session'] as $type)
                  <option value="{{ $type }}">{{ __('cycles.'.$type) }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-12 col-md-3">
            <div class="from-group mb-2 mb-md-0">
              <select class="form-select form-select-sm" id="cycle-filter">
                <option value="" selected>{{ __('cycles.Select_Cycle') }}</option>
                @foreach($cycles as $cycle)
                  <option value="{{ $cycle->id }}">{{ $cycle->title }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-12 col-md-3">
            <div class="from-group mb-2 mb-md-0">
              <select class="form-select form-select-sm" id="step-filter">
                <option value="" selected>{{ __('cycles.Select_Step') }}</option>
                @foreach ($steps as $step)
                  <option value="{{ $step->id }}">{{ $step->title }}
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-12 col-md-2">
            <div class="from-group mb-2 mb-md-0">
              <input 
                type="text" 
                id="start-date-filter"
                class="form-control form-control-sm datepicker"
                placeholder="{{ __('cycles.Start_Date') }}"
              />
            </div>
          </div>
          <div class="col-12 col-md-2">
            <div class="from-group mb-2 mb-md-0">
              <input 
                type="text" 
                id="end-date-filter" 
                class="form-control form-control-sm datepicker"
                placeholder="{{ __('cycles.End_Date') }}"
              />
            </div>
          </div>
        </div>

        <!-- Table Data -->
        <div class="table-responsive">
          <table class="table table-hover" id="tblTraining">
            <thead class="table-light">
              <tr>
                <th>{{ __('cycles.Title') }}</th>
                <th>{{ __('cycles.Type') }}</th>
                <th>{{ __('cycles.Program') }}</th>
                <th>{{ __('cycles.Training_Step') }}</th>
                <th>{{ __('cycles.Date_Time') }}</th>
                <th>{{ __('cycles.details') }}</th>
                <th>{{ __('cycles.Actions') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($sessions as $session)
                <tr>
                  {{-- Title --}}
                  <td><small class="text-nowrap">{{ $session->title }}</small></td>

                  {{-- Type (badge) --}}
                  <td>
                    <small class="badge bg-label-{{ $session->type == 'offline' ? 'primary' : ($session->type == 'sessions' ? 'info' : 'success') }}">
                      {{ $session->type == 'offline' ? __('cycles.Offline_Session') : ($session->type == 'sessions' ? __('cycles.Online_Session') : __('cycles.Online_Training')) }}
                    </small>
                  </td>

                  {{-- Program --}}
                  <td><small class="text-nowrap">{{ $session->step->cycle->title }}</small></td>

                  {{-- Training Step --}}
                  <td><small class="text-nowrap">{{ $session->step->title }}</small></td>

                  {{-- Date & Time --}}
                  <td>
                    <small class="text-nowrap">
                     @php
                        $startDate = FormatDate($session->datetime, 'Y-m-d');
                        $endDate   = $session->enddatetime ? FormatDate($session->enddatetime, 'Y-m-d') : null;
                        $startTime = FormatDate($session->datetime, 'H:i:s');
                        $endTime   = $session->enddatetime ? FormatDate($session->enddatetime, 'H:i:s') : null;
                      @endphp

                      {{-- Show dates --}}
                      @if($endDate && $endDate !== $startDate)
                        {{ $startDate }} - {{ $endDate }}
                      @else
                        {{ $startDate }}
                      @endif
                    </small>
                    <br>
                    {{-- Show times --}}
                    <small>
                      @if($endTime && $endTime !== $startTime)
                        {{ $startTime }} - {{ $endTime }}
                      @else
                        {{ $startTime }}
                      @endif
                    </small>
                  </td>

                  {{-- Details (Trainer + Address if offline) --}}
                  <td>
                    @if($session->type == "offline" || $session->type == "sessions")
                      <div class="text-nowrap">{{ __('cycles.trainer name') }}:</div> {{ $session->trainer_name }}<br>
                      @if(!empty($session->training_Address))
                        <small class="text-nowrap">{{ __('cycles.Training_Address') }}:</small> {{ $session->training_Address }}
                      @endif
                    {{-- @else
                      <div class="text-nowrap">{{ __('cycles.trainer name') }}:</div>
                      <div class="text-nowrap">{{ $session->course->user->{'first_name_' . $l} }} {{ $session->course->user->{'last_name_' . $l} }}</div> --}}
                    @endif
                  </td>

                  {{-- Actions --}}
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      {{-- Online session (Zoom) --}}
                      @if ($session->type == 'sessions')
                        @if ($session->start_url)
                          <a href="{{ $session->start_url }}" target="_blank" class="btn btn-icon btn-label-success rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('cycles.Start_Meeting') }}">
                            <i class="ti ti-brand-zoom"></i>
                          </a>
                        @endif
                        @if ($session->join_url)
                          <a href="{{ $session->join_url }}" target="_blank" class="btn btn-icon btn-label-info rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('cycles.Join_Meeting') }}">
                            <i class="ti ti-user"></i>
                          </a>
                        @endif
                        @if(!empty($session->zoom_passcode))
                          <button data-passcode="{{ $session->zoom_passcode }}" class="btn btn-icon btn-label-warning rounded-circle show-passcode">
                            <i class="ti ti-key"></i>
                          </button>
                        @endif
                        <button data-bs-toggle="modal" data-bs-target="#reportModal{{ $session->id }}" class="btn btn-icon btn-label-warning rounded-circle">
                          <i class="ti ti-file-text"></i>
                        </button>
                      @endif
                      {{-- Offline session --}}
                      @if ($session->type == 'offline')
                        @if ($session->QrCode)
                          <button data-bs-toggle="modal" data-bs-target="#qrCodeModal{{ $session->id }}" class="btn btn-icon btn-label-warning rounded-circle">
                            <i class="ti ti-qrcode"></i>
                          </button>
                        @else
                          <button onclick="createQrCode({{ $session->id }})" class="btn btn-icon btn-label-success rounded-circle">
                            <i class="ti ti-qrcode"></i>
                          </button>
                        @endif

                        @if ($session->training_location)
                          <a href="{{ $session->training_location }}" class="btn btn-icon btn-label-primary rounded-circle" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('cycles.View_Location') }}">
                            <i class="ti ti-map-pin"></i>
                          </a>
                        @endif

                        <a href="/dashboard/training/Attendance-offline/{{ $session->id }}" class="btn btn-icon btn-label-dark rounded-circle">
                          <i class="ti ti-calendar-check"></i>
                        </a>
                      @endif

                      {{-- Online training --}}
                      @if ($session->type == 'online')
                        <a href="/dashboard/training/all/{{ $session->training_Address }}">
                          <button class="btn btn-icon btn-label-dark rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('cycles.View_Course') }}">
                            <i class="ti ti-book"></i>
                          </button>
                        </a>
                      @endif

                      {{-- Common actions --}}
                      <button onclick="editme({{ $session->id }})" class="btn btn-icon btn-label-info rounded-circle">
                        <i class="ti ti-edit"></i>
                      </button>
                      <button onclick="deleteme({{ $session->id }})" class="btn btn-icon btn-label-danger rounded-circle">
                        <i class="ti ti-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                {{-- QR Modal --}}
                <div class="modal fade" id="qrCodeModal{{ $session->id }}" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-label-secondary h-100 h-md-auto">
                      <div class="modal-header">
                        <h5 class="modal-title">QR Code</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body text-center">
                        <img src="{{ asset($session->QrCode) }}" alt="QR Code" class="img-fluid">
                      </div>
                      <div onclick="createQrCode({{ $session->id }})" class="btn btn-label-info">
                        {{ __('admin.Re_generate') }}
                      </div>
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
              <label for="date-range-picker" class="form-label fs-6 fw-semibold">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
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
          <button type="submit" class="btn btn-primary">
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
            <input type="hidden" name="step_id" value="{{ $id }}" id="step_id">
            <div class="form-group mb-4" id="ctype">
              <input type="hidden" name="type" id="select-type">
              <label class="form-label fs-6 mb-1">{{ __('cycles.Type') }}:</label>
              <select id="select-type" class="select2 form-control" required disabled>
                <option value="online" {{ old('type', $session->type) == 'online' ? 'selected' : '' }}>{{ __('cycles.online') }}</option>
                <option value="sessions" {{ old('type', $session->type) == 'sessions' ? 'selected' : '' }}>{{ __('cycles.sessions') }}</option>
                <option value="offline" {{ old('type', $session->type) == 'offline' ? 'selected' : '' }}>{{ __('cycles.offline') }}</option>
             </select>
             
            </div>
            <div class="row mb-4" id="date-only">
              <div class="col-12">
                <label for="date-range-picker" class="form-label fs-6 fw-semibold">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
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
            <button type="submit" class="btn btn-primary">
              {{ __('cycles.Edit') }}
              <i class="ti ti-circle-check ms-2"></i>
            </button>
          </div>
        </div>
      </form>
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
<script>
  $(document).ready(function() {
    // Initalize datepicker
    $('.datepicker').flatpickr({
      monthSelectorType: 'static',
      allowInput: true,
      onReady: function (selectedDates, dateStr, instance) {
        let btn = document.createElement("button");
        btn.type = "button";
        btn.innerHTML = "Clear";
        btn.classList.add("btn", "btn-sm", "btn-outline-secondary", "mt-1");
        btn.addEventListener("click", function () {
          instance.clear(); // <-- clear flatpickr value
          instance.close();
          $('#tblTraining').DataTable().draw(); // refresh filter
        });
        instance.calendarContainer.appendChild(btn);
      }
    });
    
    // Initalize date table
    let trainingTable = $('#tblTraining').DataTable({
      responsive: true,
      pageLength: 10,
      columnDefs: [{
        orderable: false,
        targets: [6]
      }],
      order: [[4, 'desc']],
      language: {
        @if ($l == 'ar')
          sEmptyTable: "ليست هناك بيانات متاحة في الجدول",
          sLoadingRecords: "جارٍ التحميل...",
          sProcessing: "جارٍ التحميل...",
          sLengthMenu: "أظهر _MENU_ مدخلات",
          sZeroRecords: "لم يعثر على أية سجلات",
          sInfo: "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
          sInfoEmpty: "يعرض 0 إلى 0 من أصل 0 سجل",
          sInfoFiltered: "(منتقاة من مجموع _MAX_ مُدخل)",
          sInfoPostFix: "",
          sSearch: "ابحث:",
          sUrl: "",
          oPaginate: {
            sFirst: "الأول",
            sPrevious: "السابق",
            sNext: "التالي",
            sLast: "الأخير"
          },
          oAria: {
            sSortAscending: ": تفعيل لترتيب العمود تصاعدياً",
            sSortDescending: ": تفعيل لترتيب العمود تنازلياً"
          }
        @endif
      },
      buttons: [
        { extend: 'csv', text: '<i class="ti ti-file-text me-1"></i>CSV', className: 'd-none', title: `tblTraining_Export_${new Date().toISOString().slice(0, 10)}`, exportOptions: { columns: [0, 1, 2, 3, 4, 5] } },
        { extend: 'excel', text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel', className: 'd-none', title: `tblTraining_Export_${new Date().toISOString().slice(0, 10)}`, exportOptions: { columns: [0, 1, 2, 3, 4, 5] } }
      ]
    });

    // ============ FILTER HANDLERS ============ //

    // Type filter (column 1)
    $('#type-filter').on('change', function () {
      let val = this.value ? $('#type-filter option:selected').text().trim() : '';
      trainingTable.column(1).search(val, true, false).draw();
    });

    // Cycle filter (column 2)
    $('#cycle-filter').on('change', function () {
      let val = this.value ? $('#cycle-filter option:selected').text().trim() : '';
      trainingTable.column(2).search(val, true, false).draw();
    });

    // Step filter (column 3)
    $('#step-filter').on('change', function () {
      let val = this.value ? $('#step-filter option:selected').text().trim() : '';
      trainingTable.column(3).search(val, true, false).draw();
    });

    // Date range filter (column 4)
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
      let startDate = $('#start-date-filter').val();
      let endDate   = $('#end-date-filter').val();

      // Column 4 text (date/time)
      let dateText = data[4] || "";

      // Extract first date (format: YYYY-MM-DD …)
      let match = dateText.match(/\d{4}-\d{2}-\d{2}/);
      if (!match) return true; // no valid date in cell → keep row

      let cellDateObj = new Date(match[0]);

      // Convert filter inputs
      let startOk = startDate ? new Date(startDate) <= cellDateObj : true;
      let endOk   = endDate ? new Date(endDate) >= cellDateObj : true;

      return startOk && endOk;
    });

    // Redraw when date inputs change
    $('#start-date-filter, #end-date-filter').on('change', function () {
      trainingTable.draw();
    });


    // Global export training function
    window.exportTraining = function (format) {
      if (!trainingTable) {
        releaseFailureAlert({ message: 'No data available to export' });
        return;
      }
      try {
        trainingTable.button(`.buttons-${format}`).trigger();
      } catch (error) {
        console.error('Export error:', error);
        releaseFailureAlert({ message: 'Failed to export data. Please try again.' });
      }
    }
  });
</script>

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
    
    $('#addSessionForm').on('submit', function(e) {
      const formId = '#addSessionForm';
      const typeValue = $(`${formId} #select-type`).val();
      const dateRangePicker = $(`${formId} #date-range-picker`);
      const dateRangeValue = dateRangePicker.val();
      const dateValue = $(`${formId} .flatpickr-date`).val();
      const timeFromValue = $(`${formId} #flatpickr-time-from`).val();
      const timeToValue = $(`${formId} #flatpickr-time-to`).val();
      const titleValue = $(`${formId} #title`).val();
      const submitBtn = $(`${formId} button[type="submit"]`);
    
      if (typeValue === 'online') {
        if (!dateRangeValue) {
          dateRangePicker.addClass('is-invalid').after('<div class="invalid-feedback">{{ __('cycles.Date_Range_Required') }}</div>');
          e.preventDefault();
          return;
        }
        if (!titleValue) {
          $(`${formId} #title`).addClass('is-invalid').after('<div class="invalid-feedback">{{ __('cycles.Title_Required') }}</div>');
          e.preventDefault();
          return;
        }
      }  

      if (typeValue === 'offline' || typeValue === 'sessions') {
       if (!dateValue) {
          $(`${formId} .flatpickr-date`).addClass('is-invalid').after('<div class="invalid-feedback">{{ __('cycles.Date_Required') }}</div>');
          e.preventDefault();
          return
        }
        if (!timeFromValue) {
          $(`${formId} #flatpickr-time-from`).addClass('is-invalid').after('<div class="invalid-feedback">{{ __('cycles.Start_Time_Required') }}</div>');
          e.preventDefault();
          return
        }
        if (!timeToValue) {
          $(`${formId} #flatpickr-time-to`).addClass('is-invalid').after('<div class="invalid-feedback">{{ __('cycles.End_Time_Required') }}</div>');
          e.preventDefault();
          return
        } 
        if (!titleValue) {
          $(`${formId} #title`).addClass('is-invalid').after('<div class="invalid-feedback">{{ __('cycles.Title_Required') }}</div>');
          e.preventDefault();
          return;
        }
      }  

      // If validation passes, disable button and show spinner
      submitBtn.prop('disabled', true);
      submitBtn.find('i').removeClass('ti-circle-check').addClass('ti-loader-2 spinner-icon');
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
      const id = $(`${formId} #id`).val();
      const dateRangePicker = $(`${formId} #date-range-picker`);
      const dateRangeValue = dateRangePicker.val();
      const dateValue = $(`${formId} .flatpickr-date`).val();
      const timeFromValue = $(`${formId} #flatpickr-time-from`).val();
      const timeToValue = $(`${formId} #flatpickr-time-to`).val();
      const titleValue = $(`${formId} #title`).val();
      const submitBtn = $(`${formId} button[type="submit"]`);
      // console.log('titleValue', titleValue);
      
      let isValid = true;

      if (typeValue === 'online') {
        if (!dateRangeValue) {
          dateRangePicker.addClass('is-invalid').after('<div class="invalid-feedback">{{ __("cycles.Date_Range_Required") }}</div>');
          isValid = false;
        }
        if (!titleValue) {
          $(`${formId} #title`).addClass('is-invalid').after('<div class="invalid-feedback">{{ __("cycles.Title_Required") }}</div>');
          isValid = false;
        }
      }  

      if (typeValue === 'offline' || typeValue === 'sessions') {
        if (!dateValue) {
          $(`${formId} .flatpickr-date`).addClass('is-invalid').after('<div class="invalid-feedback">{{ __("cycles.Date_Required") }}</div>');
          isValid = false;
        }
        if (!timeFromValue) {
          $(`${formId} #flatpickr-time-from`).addClass('is-invalid').after('<div class="invalid-feedback">{{ __("cycles.Start_Time_Required") }}</div>');
          isValid = false;
        }
        if (!timeToValue) {
          $(`${formId} #flatpickr-time-to`).addClass('is-invalid').after('<div class="invalid-feedback">{{ __("cycles.End_Time_Required") }}</div>');
          isValid = false;
        } 
        if (!titleValue) {
          $(`${formId} #title`).addClass('is-invalid').after('<div class="invalid-feedback">{{ __("cycles.Title_Required") }}</div>');
          isValid = false;
        }
      }
      
      if (isValid) {
        // Disable button and show spinner before submitting
        submitBtn.prop('disabled', true);
        submitBtn.find('i').removeClass('ti-circle-check').addClass('ti-loader-2 spinner-icon');
        
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

    // Reset button state when modals are closed
    function resetSubmitButton(formId) {
      const submitBtn = $(`${formId} button[type="submit"]`);
      submitBtn.prop('disabled', false);
      submitBtn.find('i').removeClass('ti-loader-2 spinner-icon').addClass('ti-circle-check');
    }

    // Reset submit button state when modals are hidden
    $('#addsession').on('hidden.bs.modal', function() {
      resetSubmitButton('#addSessionForm');
    });

    $('#editsession').on('hidden.bs.modal', function() {
      resetSubmitButton('#editSessionForm');
    });

    // Also reset button state on validation errors
    $('#addSessionForm input, #addSessionForm select, #addSessionForm textarea').on('input change', function() {
      if ($(this).hasClass('is-invalid')) {
        resetSubmitButton('#addSessionForm');
      }
    });

    $('#editSessionForm input, #editSessionForm select, #editSessionForm textarea').on('input change', function() {
      if ($(this).hasClass('is-invalid')) {
        resetSubmitButton('#editSessionForm');
      }
    });
</script>

<script>
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
                    window.location.href = '/dashboard/training/sessions/{{ $id }}';
                });
            }).fail(function(error) {
                console.error(error);
            });
        } else {
            console.log('Canceled');
        }
    });
}

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