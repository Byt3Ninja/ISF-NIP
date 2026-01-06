@extends('layouts.admin')

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />

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

    <!-- Navigation Tabs -->
    <nav class="nav-align-top mb-6">
      @php
        $tabs = [
          ['title' => __('admin.training_sessions'), 'url' => 'dashboard/training/sessions', 'icon' => 'book'],
          ['title' => __('admin.training_courses'), 'url' => 'dashboard/training/all', 'icon' => 'certificate'],
          ['title' => __('admin.training_results'), 'url' => 'dashboard/training/results', 'icon' => 'report-analytics'],
        ]
      @endphp
      <ul class="nav nav-pills">
        @foreach($tabs as $tab)
          <li class="nav-item">
            <a href="/{{ $tab['url'] }}" class="nav-link {{ Request::is($tab['url'] . '*') ? 'active' : '' }}">
              <i class="ti ti-{{ $tab['icon'] }} me-2"></i>
              {{ $tab['title'] }}
            </a>
          </li>
        @endforeach
      </ul>
    </nav>
    <!--/ Navigation Tabs -->

    <div class="card">
      <!-- Heading & Actions -->
      <div class="card-header d-md-flex justify-content-between border-bottom pb-3 mb-6">
        <h5 class="card-title mb-0">{{ __('admin.Training sessions') }}</h5>
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
      </div>
      <!-- Table Data -->
      <div class="card-datatable table-responsive">
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
                        <button type="button" data-passcode="{{ $session->zoom_passcode }}" class="btn btn-icon btn-label-warning rounded-circle show-passcode">
                          <i class="ti ti-key"></i>
                        </button>
                      @endif
                      <button onclick="openReportsModal({{ $session->id }})" class="btn btn-icon btn-label-warning rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('admin.session_reports') }}">
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

<!-- Enhanced Modal for Session Reports and Recordings -->
<div class="modal fade" id="reportsModal" tabindex="-1" aria-labelledby="reportsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-label-secondary pb-4">
        <div class="d-flex align-items-center">
          <div class="bg-white me-2 w-px-40 h-px-40 d-flex align-items-center justify-content-center rounded-circle">
            <i class="ti ti-file-analytics"></i>
          </div>
          <div>
            <h5 class="modal-title mb-0" id="reportsModalLabel">
              {{ __('admin.session_reports_and_recordings') }}
            </h5>
            <small id="sessionTitleSubtext"></small>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body p-0">
        <!-- Loading State -->
        <div id="modalLoadingState" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ __('admin.Loading') }}...</span>
          </div>
          <p class="mt-3 text-muted">{{ __('admin.fetching_session_data') }}...</p>
        </div>

        <!-- Main Content -->
        <div id="modalMainContent" style="display: none;">
          <!-- Session Summary Cards -->
          @php
            $summaryData = [
              ['label' => __('admin.total_duration'), 'id' => 'totalDuration', 'icon' => 'clock', 'color' => 'info'],
              ['label' => __('admin.total_participants'), 'id' => 'totalParticipants', 'icon' => 'users', 'color' => 'success'],
              ['label' => __('admin.recordings_count'), 'id' => 'totalRecordings', 'icon' => 'video', 'color' => 'warning'],
            ]
          @endphp
          <div class="row g-3 p-8">
            @foreach ($summaryData as $key => $item)
              <div class="col-md-4">
                <div class="card card-border-shadow-{{ $item['color'] }} h-100">
                  <div class="card-body">
                    <div class="d-flex align-items-center justify-content-center text-center mb-2">
                      <div class="avatar me-4">
                        <span class="avatar-initial rounded bg-label-{{ $item['color'] }}">
                          <i class="ti ti-{{ $item['icon'] }} ti-20px"></i>
                        </span>
                      </div>
                      <h5 class="mb-0" id="{{ $item['id'] }}">-</h5>
                      @if ($key != 1)
                        <small class="text-muted ms-2">{{ __('admin.min') }}</small>
                      @endif
                    </div>
                    <h6 class="text-center mb-0">{{ $item['label'] }}</h6>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          <!-- Navigation Tabs -->
          <div class="px-8 py-0">
            <ul class="nav nav-pills nav-fill" id="reportsTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports-content" type="button" role="tab">
                  <i class="ti ti-file-text me-2"></i>{{ __('admin.session_reports') }}
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="recordings-tab" data-bs-toggle="tab" data-bs-target="#recordings-content" type="button" role="tab">
                  <i class="ti ti-video me-2"></i>{{ __('admin.session_recordings') }}
                </button>
              </li>
            </ul>
          </div>

          <!-- Tab Content -->
          <div class="tab-content p-8" id="reportsTabContent">
            <!-- Reports Tab -->
            <div class="tab-pane fade show active" id="reports-content" role="tabpanel">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">{{ __('admin.attendance_details') }}</h6>
                <button id="refreshReportsBtn" class="btn btn-outline-info btn-sm">
                  <i class="ti ti-refresh me-1"></i>{{ __('admin.refresh_reports') }}
                </button>
              </div>
              
              <div id="reportsTableContainer">
                <div class="table-responsive">
                  <table class="table table-hover" id="reportsTable">
                    <thead class="table-light">
                      <tr>
                        <th class="text-center">#</th>
                        <th>{{ __('admin.participant_name') }}</th>
                        <th>{{ __('admin.participant_email') }}</th>
                        <th class="text-center">{{ __('admin.join_time') }}</th>
                        <th class="text-center">{{ __('admin.leave_time') }}</th>
                        <th class="text-center">{{ __('admin.duration') }}</th>
                      </tr>
                    </thead>
                    <tbody id="reportsTableBody">
                      <!-- Dynamic content -->
                    </tbody>
                  </table>
                </div>
                <div id="reportsEmptyState" class="text-center py-5" style="display: none;">
                  <div class="mb-3">
                    <i class="ti ti-file-x display-1 text-secondary"></i>
                  </div>
                  <h6 class="mb-2">{{ __('admin.no_reports_available') }}</h6>
                  <p class="text-muted mb-3">{{ __('admin.no_reports_description') }}</p>
                </div>
              </div>
            </div>

            <!-- Recordings Tab -->
            <div class="tab-pane fade" id="recordings-content" role="tabpanel">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">{{ __('admin.recording_details') }}</h6>
                <button id="refreshRecordingsBtn" class="btn btn-outline-warning btn-sm">
                  <i class="ti ti-refresh me-1"></i>{{ __('admin.refresh_recordings') }}
                </button>
              </div>

              <div id="recordingPasscodeContainer" class="alert alert-info mb-3" style="display: none;">
                <div class="d-flex align-items-center">
                  <i class="ti ti-key me-2"></i>
                  <strong>{{ __('admin.recording_passcode') }}:</strong>
                  <span id="recordingPasscode" class="ms-2 font-monospace"></span>
                  <button class="btn btn-sm btn-outline-info ms-auto" onclick="copyToClipboard('recordingPasscode')">
                    <i class="ti ti-copy fs-6"></i>
                  </button>
                </div>
              </div>
              
              <div id="recordingsTableContainer">
                <div class="table-responsive">
                  <table class="table table-hover" id="recordingsTable">
                    <thead class="table-light">
                      <tr>
                        <th class="text-center">#</th>
                        <th>{{ __('admin.recording_type') }}</th>
                        <th class="text-center">{{ __('admin.recording_time') }}</th>
                        <th class="text-center">{{ __('admin.actions') }}</th>
                      </tr>
                    </thead>
                    <tbody id="recordingsTableBody">
                      <!-- Dynamic content -->
                    </tbody>
                  </table>
                </div>
                <div id="recordingsEmptyState" class="text-center py-5" style="display: none;">
                  <div class="mb-3">
                    <i class="ti ti-video-off display-1 text-secondary"></i>
                  </div>
                  <h6 class="mb-2">{{ __('admin.no_recordings_available') }}</h6>
                  <p class="text-muted mb-3">{{ __('admin.no_recordings_description') }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Error State -->
        <div id="modalErrorState" class="text-center py-5" style="display: none;">
          <div class="mb-3">
            <i class="ti ti-alert-triangle display-1 text-danger"></i>
          </div>
          <h6 class="mb-2">{{ __('admin.error_loading_data') }}</h6>
          <p class="text-muted mb-3" id="errorMessage"></p>
          <button id="retryLoadBtn" class="btn btn-outline-primary">
            <i class="ti ti-refresh me-2"></i>{{ __('admin.retry') }}
          </button>
        </div>
      </div>

      <div class="modal-footer bg-label-secondary pt-6">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-2"></i>{{ __('admin.Close') }}
        </button>
      </div>
    </div>
  </div>
</div>
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

<!-- Passcode Modal -->
<div class="modal fade" id="passcodeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center">
          <h4 class="mb-2">Meeting Passcode</h4>
          <p class="mb-2">The passcode for this meeting is:</p>
          <h5 class="mb-2" id="passcode"></h5>
        </div>
      </div>
    </div>
  </div>
</div>    
<!--/ Passcode Modal -->
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
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
          sEmptyTable: "     {{ __('admin.No_training_sessions_available') }}",
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
      const passcode = $(this).data('passcode');
      $('#passcode').text(passcode);
      $('#passcodeModal').modal('show');
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

    // Enhanced Reports Modal Functions
    let currentSessionId = null;

    function openReportsModal(sessionId) {
      currentSessionId = sessionId;
      
      // Reset modal state
      $('#modalLoadingState').show();
      $('#modalMainContent').hide();
      $('#modalErrorState').hide();
      
      // Show modal
      $('#reportsModal').modal('show');
      
      // Fetch session data
      fetchSessionData(sessionId);
    }

    function fetchSessionData(sessionId, showLoading = true) {
      if (showLoading) {
        $('#modalLoadingState').show();
        $('#modalMainContent').hide();
        $('#modalErrorState').hide();
      }

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      return $.ajax({
        type: "POST",
        url: "/dashboard/training/session-reports-data",
        data: { "id": sessionId },
        timeout: 30000,
        success: function(response) {
          if (response.success) {
            populateModalData(response.data);
            $('#modalLoadingState').hide();
            $('#modalMainContent').show();
          } else {
            showError(response.message || '{{ __("admin.error_fetching_data") }}');
          }
        },
        error: function(xhr, status, error) {
          let message = '{{ __("admin.network_error") }}';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
          } else if (status === 'timeout') {
            message = '{{ __("admin.request_timeout") }}';
          }
          showError(message);
        }
      });
    }

    function populateModalData(data) {
      // Update modal title
      $('#sessionTitleSubtext').text(data.session.title || '{{ __("admin.untitled_session") }}');
      
      // Update summary cards
      $('#totalDuration').text(Math.round(data.summary.total_duration / 60 * 100) / 100);
      $('#totalParticipants').text(data.summary.total_participants);
      $('#totalRecordings').text(data.summary.total_recordings);
      
      // Populate reports table
      populateReportsTable(data.reports);
      
      // Populate recordings table
      populateRecordingsTable(data.recordings, data.recording_passcode);
    }

    function populateReportsTable(reports) {
      const tbody = $('#reportsTableBody');
      tbody.empty();
      
      if (reports && reports.length > 0) {
        reports.forEach((report, index) => {
          const duration = Math.round(report.attendance_duration / 60 * 100) / 100;
          const row = `
            <tr>
              <td class="text-center fw-semibold">${index + 1}</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="me-2">
                    <i class="ti ti-user fs-6 text-primary"></i>
                  </div>
                  <div>
                    <h6 class="mb-0">${report.participant_name || '{{ __("admin.unknown") }}'}</h6>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge bg-label-info">${report.participant_email || '{{ __("admin.not_provided") }}'}</span>
              </td>
              <td class="text-center">
                <small class="text-muted">${formatDateTime(report.participant_join_date)}</small>
              </td>
              <td class="text-center">
                <small class="text-muted">${formatDateTime(report.participant_end_date)}</small>
              </td>
              <td class="text-center">
                <span class="badge bg-label-success">${duration} {{ __("admin.min") }}</span>
              </td>
            </tr>
          `;
          tbody.append(row);
        });
        
        $('#reportsTableContainer table').show();
        $('#reportsEmptyState').hide();
      } else {
        $('#reportsTableContainer table').hide();
        $('#reportsEmptyState').show();
      }
    }

    function populateRecordingsTable(recordings, passcode) {
      const tbody = $('#recordingsTableBody');
      tbody.empty();
      
      // Handle passcode
      if (passcode) {
        $('#recordingPasscode').text(passcode);
        $('#recordingPasscodeContainer').show();
      } else {
        $('#recordingPasscodeContainer').hide();
      }
      
      if (recordings && recordings.length > 0) {
        recordings.forEach((recording, index) => {
          const recordingType = recording.recording_type ? recording.recording_type.replace(/_/g, ' ') : '{{ __("admin.video") }}';
          const row = `
            <tr>
              <td class="text-center fw-semibold">${index + 1}</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="me-2">
                    <i class="ti ti-video fs-6 text-warning"></i>
                  </div>
                  <div class="text-capitalize">
                    <h6 class="mb-0">${recordingType}</h6>
                  </div>
                </div>
              </td>
              <td class="text-center">
                <small class="text-muted">${formatDateTime(recording.recording_time)}</small>
              </td>
              <td class="text-center">
                <div class="btn-group btn-group-sm">
                  ${recording.recording_view_url ? 
                    `<a href="${recording.recording_view_url}" target="_blank" class="btn btn-outline-primary btn-sm">
                      <i class="ti ti-eye fs-6"></i>
                    </a>` : ''
                  }
                  ${recording.recording_download_url ? 
                    `<a href="${recording.recording_download_url}" target="_blank" class="btn btn-outline-success btn-sm">
                      <i class="ti ti-download fs-6"></i>
                    </a>` : ''
                  }
                </div>
              </td>
            </tr>
          `;
          tbody.append(row);
        });
        
        $('#recordingsTableContainer table').show();
        $('#recordingsEmptyState').hide();
      } else {
        $('#recordingsTableContainer table').hide();
        $('#recordingsEmptyState').show();
      }
    }

    function showError(message) {
      $('#modalLoadingState').hide();
      $('#modalMainContent').hide();
      $('#errorMessage').text(message);
      $('#modalErrorState').show();
    }

    function refreshReports() {
      if (!currentSessionId) return;
      
      const btn = $('#refreshReportsBtn');
      const originalHtml = btn.html();
      btn.html('<i class="ti ti-loader-2 spinner-icon me-1"></i>{{ __("admin.refreshing") }}...');
      btn.prop('disabled', true);
      
      fetchSessionData(currentSessionId, false).always(function() {
        btn.html(originalHtml);
        btn.prop('disabled', false);
      });
    }

    function refreshRecordings() {
      if (!currentSessionId) return;
      
      const btn = $('#refreshRecordingsBtn');
      const originalHtml = btn.html();
      btn.html('<i class="ti ti-loader-2 spinner-icon me-1"></i>{{ __("admin.refreshing") }}...');
      btn.prop('disabled', true);
      
      fetchSessionData(currentSessionId, false).always(function() {
        btn.html(originalHtml);
        btn.prop('disabled', false);
      });
    }

    function copyToClipboard(elementId) {
      const element = document.getElementById(elementId);
      const text = element.textContent;
      
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
          showToast({ message: '{{ __("admin.copied_to_clipboard") }}', type: 'success' });
        });
      } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast({ message: '{{ __("admin.copied_to_clipboard") }}', type: 'success' });
      }
    }

    // Event Listeners
    $(document).ready(function() {
      $('#refreshReportsBtn').on('click', refreshReports);
      $('#refreshRecordingsBtn').on('click', refreshRecordings);
      $('#retryLoadBtn').on('click', () => fetchSessionData(currentSessionId));
    });

    function formatDateTime(dateTime) {
      if (!dateTime) return '{{ __("admin.not_available") }}';
      
      try {
        const date = new Date(dateTime);
        
        // Check if date is valid
        if (isNaN(date.getTime())) {
          return '{{ __("admin.invalid_date") }}';
        }
        
        const options = {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
          timeZoneName: 'short'  // This will show the timezone (e.g., "GMT+3", "EST")
        };
        
        return date.toLocaleString('{{ app()->getLocale() }}', options);
      } catch (error) {
        console.error('Date formatting error:', error);
        return '{{ __("admin.invalid_date") }}';
      }
    }
</script>
@stop