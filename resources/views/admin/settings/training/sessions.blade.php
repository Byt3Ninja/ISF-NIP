@extends('layouts.admin')

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
@stop

@php
  $tabs = [
    ['title' => __('admin.training_sessions'), 'url' => 'dashboard/training/sessions', 'icon' => 'book'],
    ['title' => __('admin.training_courses'),  'url' => 'dashboard/training/all',      'icon' => 'certificate'],
    ['title' => __('admin.training_results'),  'url' => 'dashboard/training/results',  'icon' => 'report-analytics'],
  ];
@endphp

@section('content')
<div>
  {{-- Alert --}}
  @if (session()->has('message') || session()->has('message2'))
    <x-alert
      type="{{ session()->get('class') ?? 'danger' }}"
      message="{{ session()->has('message') ? __(session('message')) : __('admin.' . session('message2')) }}"
    />
  @endif

  <x-navigation-pills :tabs="$tabs" />

  {{-- ============================================================
      Training Step Selection Card
  ============================================================ --}}
  @if( !is_null( request()->id ) )
  <div class="card mb-4">
    <div class="card-body overflow-x-hidden text-nowrap">
      <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between align-items-md-center">
        <div class="form-group d-flex flex-column flex-lg-row align-items-lg-center gap-2 mb-6 mb-lg-0 me-lg-4">
          <label class="form-label text-capitalize fs-6">{{ __('cycles.Select training step') }}:</label>
          <select id="cstep" class="form-select select2"
            onchange="window.open('/dashboard/training/sessions/'+this.options[this.selectedIndex].value,'_self');"  
          >
            @foreach ($program_steps as $step)
              <option
                value="{{ $step->id }}"
                {{ $step->id == $id ? 'selected' : '' }}
                data-cycle="{{ $step->cycle->title }}"
                data-organization="{{ optional(optional($step->cycle)->organization)->name }}"
              >
                {{ $step->title }}
                • {{ __('cycles.Cycle') }}: "{{ $step->cycle->title }}"
                • {{ __('cycles.Start') }}: {{ $step->from->format('d M Y') ?? '-' }}
                @if (optional(optional($step->cycle)->organization)->name)
                  • {{ optional(optional($step->cycle)->organization)->name }}
                @endif
              </option>
            @endforeach
            @if (!$id)
              <option> {{ __('cycles.No records saved') }} </option>
            @endif
          </select>
        </div>
        <a
          class="btn btn-outline-primary"
          href="/dashboard/cycles/{{ $current_step->cycle_id }}"
          data-bs-toggle="tooltip"
          data-bs-placement="top"
          title="{{ __('cycles.Manage Cycle') }}"
        >
          <i class="ti ti-adjustments me-2"></i> {{ __('cycles.Manage Cycle') }}</span>
        </a>
      </div>
    </div>
  </div>
  @endif

  {{-- ============================================================
       Main Card
  ============================================================ --}}
  <div class="card">
    <div class="card-header border-bottom">
      <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-md-center">
        <h5 class="card-title mb-0">{{ __('admin.Training sessions') }}</h5>
        <div class="d-flex align-items-center gap-3">
          <x-export-dropdown callback="exportTraining" />

          <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#sessionOffcanvas" data-mode="create">
            <i class="ti ti-plus me-2"></i>{{ __('admin.Add_New') }}
          </button>
        </div>
      </div>
    </div>

    {{-- Table --}}
    <div class="card-datatable">
      <table class="table" id="tblTraining">
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
            @php
              $startDate = FormatDate($session->datetime, 'Y-m-d');
              $endDate   = $session->enddatetime ? FormatDate($session->enddatetime, 'Y-m-d') : null;
              $startTime = FormatDate($session->datetime, 'H:i:s');
              $endTime   = $session->enddatetime ? FormatDate($session->enddatetime, 'H:i:s') : null;

              $typeBadge = match($session->type) {
                'offline'  => ['color' => 'primary', 'label' => __('cycles.Offline_Session')],
                'sessions' => ['color' => 'info',    'label' => __('cycles.Online_Session')],
                default    => ['color' => 'success',  'label' => __('cycles.Online_Training')],
              };
            @endphp
            <tr>
              {{-- Title --}}
              <td><small class="text-nowrap">{{ $session->title }}</small></td>

              {{-- Type --}}
              <td>
                <small class="badge bg-label-{{ $typeBadge['color'] }}">{{ $typeBadge['label'] }}</small>
              </td>

              {{-- Program --}}
              <td><small class="text-nowrap">{{ $session->step->cycle->title }}</small></td>

              {{-- Training Step --}}
              <td><small class="text-nowrap">{{ $session->step->title }}</small></td>

              {{-- Date & Time --}}
              <td>
                <small class="text-nowrap d-block">
                  @if ($endDate && $endDate !== $startDate)
                    {{ $startDate }} - {{ $endDate }}
                  @else
                    {{ $startDate }}
                  @endif
                </small>
                <small class="text-muted">
                  @if ($endTime && $endTime !== $startTime)
                    {{ $startTime }} - {{ $endTime }}
                  @else
                    {{ $startTime }}
                  @endif
                </small>
              </td>

              {{-- Details --}}
              <td>
                @if ($session->type === 'offline' || $session->type === 'sessions')
                  <small class="d-block text-nowrap fw-semibold">{{ __('cycles.trainer name') }}:</small>
                  <small>{{ $session->trainer_name }}</small>
                  @if (!empty($session->training_Address))
                    <small class="d-block text-nowrap fw-semibold mt-1">{{ __('cycles.Training_Address') }}:</small>
                    <small>{{ $session->training_Address }}</small>
                  @endif
                @endif
              </td>

              {{-- Actions --}}
              <td>
                <div class="d-flex align-items-center">
                  {{-- Online session (Zoom) --}}
                  @if ($session->type === 'sessions')
                    @if ($session->start_url)
                      <a href="{{ $session->start_url }}" target="_blank"
                         class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                         data-bs-toggle="tooltip" title="{{ __('cycles.Start_Meeting') }}">
                        <i class="ti ti-brand-zoom ti-md"></i>
                      </a>
                    @endif
                    @if ($session->join_url)
                      <a href="{{ $session->join_url }}" target="_blank"
                         class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                         data-bs-toggle="tooltip" title="{{ __('cycles.Join_Meeting') }}">
                        <i class="ti ti-user ti-md"></i>
                      </a>
                    @endif
                    @if (!empty($session->zoom_passcode))
                      <button type="button"
                              data-passcode="{{ $session->zoom_passcode }}"
                              class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill show-passcode"
                              data-bs-toggle="tooltip" title="{{ __('cycles.Passcode') }}">
                        <i class="ti ti-key ti-md"></i>
                      </button>
                    @endif
                    <button onclick="openReportsModal({{ $session->id }})"
                            class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                            data-bs-toggle="tooltip" title="{{ __('admin.session_reports') }}">
                      <i class="ti ti-file-text ti-md"></i>
                    </button>
                  @endif

                  {{-- Offline session --}}
                  @if ($session->type === 'offline')
                    @if ($session->QrCode)
                      <button data-bs-toggle="modal" data-bs-target="#qrCodeModal{{ $session->id }}"
                              class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                              title="QR Code">
                        <i class="ti ti-qrcode ti-md"></i>
                      </button>
                    @else
                      <button onclick="createQrCode({{ $session->id }})"
                              class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                              data-bs-toggle="tooltip" title="{{ __('admin.Generate_QR') }}">
                        <i class="ti ti-qrcode ti-md"></i>
                      </button>
                    @endif
                    @if ($session->training_location)
                      <a href="{{ $session->training_location }}" target="_blank"
                         class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                         data-bs-toggle="tooltip" title="{{ __('cycles.View_Location') }}">
                        <i class="ti ti-map-pin ti-md"></i>
                      </a>
                    @endif
                    <a href="/dashboard/training/Attendance-offline/{{ $session->id }}"
                       class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                       data-bs-toggle="tooltip" title="{{ __('cycles.Attendance') }}">
                      <i class="ti ti-calendar-check ti-md"></i>
                    </a>
                  @endif

                  {{-- Online training --}}
                  @if ($session->type === 'online')
                    <a href="/dashboard/training/all/{{ $session->training_Address }}"
                       class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                       data-bs-toggle="tooltip" title="{{ __('cycles.View_Course') }}">
                      <i class="ti ti-book ti-md"></i>
                    </a>
                  @endif

                  {{-- Edit --}}
                  <button
                    class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#sessionOffcanvas"
                    data-mode="edit"
                    data-id="{{ $session->id }}"
                  >
                    <i class="ti ti-pencil ti-md"></i>
                  </button>

                  {{-- Delete --}}
                  <button onclick="deleteme({{ $session->id }})"
                          class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill"
                          data-bs-toggle="tooltip" title="{{ __('cycles.Delete') }}">
                    <i class="ti ti-trash ti-md"></i>
                  </button>
                </div>
              </td>
            </tr>

          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- ============================================================
     QR Code Modals
============================================================ --}}
@foreach ($sessions as $session)
  @if ($session->QrCode)
    <div class="modal fade" id="qrCodeModal{{ $session->id }}" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">QR Code — {{ $session->title }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            <img src="{{ asset($session->QrCode) }}" alt="QR Code" class="img-fluid">
          </div>
          <div class="modal-footer">
            <button onclick="createQrCode({{ $session->id }})" class="btn btn-label-info">
              <i class="ti ti-refresh me-2"></i>{{ __('admin.Re_generate') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  @endif
@endforeach

{{-- ============================================================
     Session Offcanvas (Add / Edit)
============================================================ --}}
<div class="offcanvas offcanvas-end w-px-600" tabindex="-1" id="sessionOffcanvas" aria-labelledby="sessionOffcanvasLabel">
  <div class="offcanvas-header border-bottom">
    <div class="d-flex justify-content-between w-100">
      <h5 class="offcanvas-title text-capitalize" id="sessionOffcanvasLabel">{{ __('cycles.Add new training session') }}</h5>
      <button type="button" class="btn-close m-0 p-0" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
  </div>

  <div class="offcanvas-body d-flex flex-column">
    <form id="sessionForm" method="POST" action="/dashboard/training/addsession" autocomplete="off" class="d-flex flex-column flex-grow-1">
      @csrf
      <input type="hidden" name="_method" id="sessionFormMethod" value="POST">
      <input type="hidden" name="step_id"  id="sessionStepId"   value="{{ $id }}">
      <input type="hidden" name="sid"      id="sessionSid"      value="">
      <input type="hidden" name="type"     id="sessionTypeHidden" value="">

      <div class="row flex-grow-1 align-content-start">

        {{-- Type --}}
        <div class="col-12 mb-4">
          <label class="form-label">{{ __('cycles.Type') }} <span class="text-danger">*</span></label>
          <select id="sessionType" class="form-select">
            <option value="online">{{ __('cycles.online') }}</option>
            <option value="sessions">{{ __('cycles.sessions') }}</option>
            <option value="offline">{{ __('cycles.offline') }}</option>
          </select>
        </div>

        {{-- Programs --}}
        <div class="col-12 mb-4">
          @php
            $selectedStep = $steps->firstWhere('id', request('id'));
            $selectedCycleId = $selectedStep?->cycle_id;
          @endphp

          <label class="form-label">{{ __('admin.Program') }} <span class="text-danger">*</span></label>
          <select name="cycle_id" id="program" class="form-select select2" {{ !is_null( request()->id ) ? 'disabled readonly' : '' }}>
            <option value="">{{ __('admin.Select Program') }}</option>
            @foreach ($cycles as $cycle)
              <option value="{{ $cycle->id }}"
                {{ $selectedCycleId == $cycle->id ? 'selected' : '' }}>
                {{ $cycle->title }}
              </option>
            @endforeach
          </select>
          <div class="invalid-feedback" id="errorProgram"></div>
        </div>
        
        {{-- Step --}}
        <div class="col-12 mb-4">
          <label class="form-label">{{ __('cycles.Step') }} <span class="text-danger">*</span></label>
          <select name="step_id" id="step" class="form-select select2" {{ !is_null( request()->id ) ? 'disabled readonly' : '' }}>
            <option value="">{{ __('cycles.Select Step') }}</option>
            @foreach ($steps as $step)
              <option 
                value="{{ $step->id }}" 
                data-cycle="{{ $step->cycle_id }}"
                {{ request('id') == $step->id ? 'selected' : '' }}>
                {{ $step->title }}
              </option>
            @endforeach
          </select>
          <div class="invalid-feedback" id="errorStep"></div>
        </div>

        {{-- Date Range (online only) --}}
        <div class="col-12 mb-4" id="fieldDateRange">
          <label class="form-label">{{ __('admin.Start_Date') }} – {{ __('admin.End_Date') }} <span class="text-danger">*</span></label>
          <input type="text" id="sessionDateRange" name="date_range" class="form-control" autocomplete="off" placeholder="{{ __('admin.select_date_range') }}" />
          <div class="invalid-feedback" id="errorDateRange"></div>
        </div>

        {{-- Date (offline / sessions) --}}
        <div class="col-12 mb-4" id="fieldDateTime" style="display:none;">
          <label class="form-label">{{ __('cycles.Date') }} <span class="text-danger">*</span></label>
          <input id="sessionDate" name="datetime" type="text" class="form-control flatpickr-date" placeholder="{{ __('cycles.Date') }}" />
          <div class="invalid-feedback" id="errorDate"></div>
        </div>

        {{-- Time From / To (offline / sessions) --}}
        <div class="col-12 mb-4" id="fieldTimes" style="display:none;">
          <div class="row">
            <div class="col-6">
              <label class="form-label">{{ __('cycles.Time From') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="starttime" id="sessionTimeFrom" placeholder="HH:MM" />
              <div class="invalid-feedback" id="errorTimeFrom"></div>
            </div>
            <div class="col-6">
              <label class="form-label">{{ __('cycles.Time To') }} <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="endtime" id="sessionTimeTo" placeholder="HH:MM" />
              <div class="invalid-feedback" id="errorTimeTo"></div>
            </div>
          </div>
        </div>

        {{-- Online course selector (online only) --}}
        <div class="col-12 mb-4" id="fieldOnlineCourse">
          <label class="form-label">{{ __('admin.online_course') }}</label>
          <select name="online" id="sessionCourse" class="form-select select2" data-placeholder="{{ __('admin.select_course') }}">
            <option value="">{{ __('admin.select_course') }}</option>
            @forelse ($online as $tr)
              <option value="{{ $tr->id }}">{{ $tr->title }}</option>
            @empty
              <option disabled>{{ __('cycles.No saved training courses') }}</option>
            @endforelse
          </select>
        </div>

        {{-- Title --}}
        <div class="col-12 mb-4">
          <label class="form-label">{{ __('cycles.Session Title') }} <span class="text-danger">*</span></label>
          <input id="sessionTitle" name="title" type="text" class="form-control" placeholder="{{ __('cycles.Session Title') }}" />
          <div class="invalid-feedback" id="errorTitle"></div>
        </div>

        {{-- Description --}}
        <div class="col-12 mb-4">
          <label class="form-label">{{ __('admin.Description') }}</label>
          <textarea class="form-control" name="dsc" id="sessionDsc" rows="4"
            placeholder="{{ __('cycles.Write your description here') }}."></textarea>
        </div>

        {{-- Trainer Name (offline / sessions) --}}
        <div class="col-12 mb-4" id="fieldTrainer" style="display:none;">
          <label class="form-label">{{ __('cycles.trainer name') }}</label>
          <input name="trainer_name" id="sessionTrainerName" type="text" class="form-control"
            placeholder="{{ __('cycles.trainer name') }}" />
        </div>

        {{-- Trainer Email (offline / sessions) --}}
        <div class="col-12 mb-4" id="fieldTrainerEmail" style="display:none;">
          <label class="form-label">{{ __('cycles.trainer email') }}</label>
          <input name="trainer_email" id="sessionTrainerEmail" type="text" class="form-control"
            placeholder="{{ __('cycles.trainer email') }}" />
        </div>

        {{-- Training Address (offline only) --}}
        <div class="col-12 mb-4" id="fieldAddress" style="display:none;">
          <label class="form-label">{{ __('cycles.training Address') }}</label>
          <input name="training_Address" id="sessionTrainingAddress" type="text" class="form-control"
            placeholder="{{ __('cycles.training Address') }}" />
        </div>

        {{-- Training Location (offline only) --}}
        <div class="col-12 mb-4" id="fieldLocation" style="display:none;">
          <label class="form-label">{{ __('cycles.training location') }}</label>
          <input name="training_location" id="sessionTrainingLocation" type="text" class="form-control"
            placeholder="{{ __('cycles.training location') }}" />
        </div>

      </div>{{-- /row --}}

      {{-- Footer Actions --}}
      <div class="row mt-auto pt-6 border-top">
        <div class="col-12">
          <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">
              <i class="ti ti-x me-2"></i>{{ __('cycles.Cancel') }}
            </button>
            <button type="submit" class="btn btn-primary" id="sessionSubmitBtn">
              <span class="btn-text">
                <i class="ti ti-circle-check me-1"></i>
                <span id="sessionSubmitText">{{ __('cycles.Submit') }}</span>
              </span>
              <span class="btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                {{ __('admin.saving') }}...
              </span>
            </button>
          </div>
        </div>
      </div>

    </form>
  </div>
</div>

{{-- ============================================================
     Passcode Modal
============================================================ --}}
<div class="modal fade" id="passcodeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center pb-6">
        <div class="avatar mx-auto mb-4">
          <span class="avatar-initial rounded-circle bg-label-warning">
            <i class="ti ti-key ti-md"></i>
          </span>
        </div>
        <h5 class="mb-2">{{ __('cycles.Meeting_Passcode') }}</h5>
        <p class="text-muted mb-3">{{ __('cycles.The passcode for this meeting is') }}:</p>
        <h4 class="font-monospace" id="passcode"></h4>
      </div>
    </div>
  </div>
</div>

{{-- ============================================================
     Session Reports & Recordings Modal
============================================================ --}}
<div class="modal fade" id="reportsModal" tabindex="-1" aria-labelledby="reportsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-label-secondary pb-4">
        <div class="d-flex align-items-center">
          <div class="bg-white me-2 w-px-40 h-px-40 d-flex align-items-center justify-content-center rounded-circle">
            <i class="ti ti-file-analytics"></i>
          </div>
          <div>
            <h5 class="modal-title mb-0" id="reportsModalLabel">{{ __('admin.session_reports_and_recordings') }}</h5>
            <small id="sessionTitleSubtext" class="text-muted"></small>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-0">
        {{-- Loading --}}
        <div id="modalLoadingState" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">{{ __('admin.Loading') }}...</span>
          </div>
          <p class="mt-3 text-muted">{{ __('admin.fetching_session_data') }}...</p>
        </div>

        {{-- Main Content --}}
        <div id="modalMainContent" style="display:none;">
          {{-- Summary Cards --}}
          @php
            $summaryCards = [
              ['label' => __('admin.total_duration'),     'id' => 'totalDuration',     'icon' => 'clock',  'color' => 'info',    'suffix' => __('admin.min')],
              ['label' => __('admin.total_participants'), 'id' => 'totalParticipants', 'icon' => 'users',  'color' => 'success', 'suffix' => ''],
              ['label' => __('admin.recordings_count'),   'id' => 'totalRecordings',   'icon' => 'video',  'color' => 'warning', 'suffix' => __('admin.min')],
            ];
          @endphp
          <div class="row g-3 p-8">
            @foreach ($summaryCards as $card)
              <div class="col-md-4">
                <div class="card card-border-shadow-{{ $card['color'] }} h-100 mb-0">
                  <div class="card-body text-center">
                    <div class="avatar mx-auto mb-3">
                      <span class="avatar-initial rounded bg-label-{{ $card['color'] }}">
                        <i class="ti ti-{{ $card['icon'] }} ti-20px"></i>
                      </span>
                    </div>
                    <h5 class="mb-0" id="{{ $card['id'] }}">–</h5>
                    @if ($card['suffix'])
                      <small class="text-muted">{{ $card['suffix'] }}</small>
                    @endif
                    <h6 class="mt-1 mb-0">{{ $card['label'] }}</h6>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          {{-- Tabs --}}
          <div class="px-8">
            <ul class="nav nav-pills nav-fill" id="reportsTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="reports-tab"
                  data-bs-toggle="tab" data-bs-target="#reports-content" type="button" role="tab">
                  <i class="ti ti-file-text me-2"></i>{{ __('admin.session_reports') }}
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="recordings-tab"
                  data-bs-toggle="tab" data-bs-target="#recordings-content" type="button" role="tab">
                  <i class="ti ti-video me-2"></i>{{ __('admin.session_recordings') }}
                </button>
              </li>
            </ul>
          </div>

          {{-- Tab Content --}}
          <div class="tab-content p-8" id="reportsTabContent">
            {{-- Reports Tab --}}
            <div class="tab-pane fade show active" id="reports-content" role="tabpanel">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">{{ __('admin.attendance_details') }}</h6>
                <button id="refreshReportsBtn" class="btn btn-outline-info btn-sm">
                  <span class="btn-text"><i class="ti ti-refresh me-1"></i>{{ __('admin.refresh_reports') }}</span>
                  <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>{{ __('admin.refreshing') }}...</span>
                </button>
              </div>
              <div class="table-responsive" id="reportsTableWrapper">
                <table class="table" id="reportsTable">
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
                  <tbody id="reportsTableBody"></tbody>
                </table>
              </div>
              <div id="reportsEmptyState" class="text-center py-5" style="display:none;">
                <i class="ti ti-file-x display-1 text-secondary"></i>
                <h6 class="mt-3 mb-1">{{ __('admin.no_reports_available') }}</h6>
                <p class="text-muted">{{ __('admin.no_reports_description') }}</p>
              </div>
            </div>

            {{-- Recordings Tab --}}
            <div class="tab-pane fade" id="recordings-content" role="tabpanel">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">{{ __('admin.recording_details') }}</h6>
                <button id="refreshRecordingsBtn" class="btn btn-outline-warning btn-sm">
                  <span class="btn-text"><i class="ti ti-refresh me-1"></i>{{ __('admin.refresh_recordings') }}</span>
                  <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>{{ __('admin.refreshing') }}...</span>
                </button>
              </div>
              <div id="recordingPasscodeContainer" class="alert alert-info mb-3" style="display:none;">
                <div class="d-flex align-items-center">
                  <i class="ti ti-key me-2"></i>
                  <strong>{{ __('admin.recording_passcode') }}:</strong>
                  <span id="recordingPasscode" class="ms-2 font-monospace"></span>
                  <button class="btn btn-sm btn-outline-info ms-auto" onclick="copyToClipboard('recordingPasscode')">
                    <i class="ti ti-copy"></i>
                  </button>
                </div>
              </div>
              <div class="table-responsive" id="recordingsTableWrapper">
                <table class="table" id="recordingsTable">
                  <thead class="table-light">
                    <tr>
                      <th class="text-center">#</th>
                      <th>{{ __('admin.recording_type') }}</th>
                      <th class="text-center">{{ __('admin.recording_time') }}</th>
                      <th class="text-center">{{ __('admin.actions') }}</th>
                    </tr>
                  </thead>
                  <tbody id="recordingsTableBody"></tbody>
                </table>
              </div>
              <div id="recordingsEmptyState" class="text-center py-5" style="display:none;">
                <i class="ti ti-video-off display-1 text-secondary"></i>
                <h6 class="mt-3 mb-1">{{ __('admin.no_recordings_available') }}</h6>
                <p class="text-muted">{{ __('admin.no_recordings_description') }}</p>
              </div>
            </div>
          </div>
        </div>

        {{-- Error State --}}
        <div id="modalErrorState" class="text-center py-5" style="display:none;">
          <i class="ti ti-alert-triangle display-1 text-danger"></i>
          <h6 class="mt-3 mb-1">{{ __('admin.error_loading_data') }}</h6>
          <p class="text-muted" id="errorMessage"></p>
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
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
@stop

@section('pagescripts')
<script>
$(function () {
  'use strict';

  /* ================================================================
   * Constants / Config
   * ============================================================== */
  const CSRF      = $('meta[name="csrf-token"]').attr('content');
  const BASE_ID   = '{{ $id }}';

  const ROUTES = {
    store:  '/dashboard/training/addsession',
    update: (id) => '{{ route('trainings.sessions_edit', '__ID__') }}'.replace('__ID__', id),
    fetch:  '/dashboard/training/session',
    delete: (id) => '/dashboard/training/delsession/' + id,
    reports:'/dashboard/training/session-reports-data',
    qr:     '/dashboard/training/QrCode-offline',
  };

  const LABELS = {
    submitText:   '{{ __('cycles.Submit') }}',
    editText:     '{{ __('cycles.Edit') }}',
    notAvailable: '{{ __('admin.not_available') }}',
    unknown:      '{{ __('admin.unknown') }}',
    notProvided:  '{{ __('admin.not_provided') }}',
    min:          '{{ __('admin.min') }}',
    video:        '{{ __('admin.video') }}',
    copiedMsg:    '{{ __('admin.copied_to_clipboard') }}',
  };

  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': CSRF } });

  // Initialise hidden type field to default
  $('#sessionTypeHidden').val('online');

  /* ================================================================
   * DataTable
   * ============================================================== */

  // Column indices
  const COL = { TITLE: 0, TYPE: 1, PROGRAM: 2, STEP: 3, DATE: 4, DETAILS: 5, ACTIONS: 6 };

  // Active filter state
  const filters = { type: '', cycle: '', step: '' };

  // Strip HTML tags helper for filter comparisons
  function stripHtml(html) {
    const d = document.createElement('div');
    d.innerHTML = html;
    return d.textContent.replace(/\s+/g, ' ').trim();
  }

  // Custom search filter
  $.fn.dataTable.ext.search.push(function (settings, data) {
    if (settings.nTable.id !== 'tblTraining') return true;
    if (filters.type  && stripHtml(data[COL.TYPE])    !== filters.type)  return false;
    if (filters.cycle && stripHtml(data[COL.PROGRAM]) !== filters.cycle) return false;
    if (filters.step  && stripHtml(data[COL.STEP])    !== filters.step)  return false;
    return true;
  });

  // Date filter state
  const dateFilters = { from: '', to: '' };

  // Date range custom search (column 4 — Date/Time)
  $.fn.dataTable.ext.search.push(function (settings, data) {
    if (settings.nTable.id !== 'tblTraining') return true;
    if (!dateFilters.from && !dateFilters.to) return true;

    // Extract first YYYY-MM-DD from the cell
    const match = (data[COL.DATE] || '').match(/\d{4}-\d{2}-\d{2}/);
    if (!match) return true;

    const cellDate = match[0];
    if (dateFilters.from && cellDate < dateFilters.from) return false;
    if (dateFilters.to   && cellDate > dateFilters.to)   return false;
    return true;
  });

  const table = $('#tblTraining').DataTable({
    ...getSharedDataTableConfig({ title: 'Trainings', descIndex: 4, actionsIndex: 6 }),
    responsive: false,
    columnDefs: [
      { targets: COL.ACTIONS, orderable: false, searchable: false, width: '1%', className: 'text-nowrap' },
      { targets: COL.TYPE,    width: '120px', className: 'text-nowrap' },
      { targets: COL.DATE,    width: '160px', className: 'text-nowrap' },
      { targets: '_all',      className: 'text-nowrap' },
    ],
    dom:
      '<"card-header border-bottom d-flex flex-wrap flex-lg-nowrap align-items-center gap-2 py-3 px-4"' +
        '<"flex-shrink-0"l>' +
        '<"d-flex flex-wrap flex-lg-nowrap align-items-center gap-2 flex-grow-1"' +
          '<"dt-filter dt-filter--type">' +
          '<"dt-filter dt-filter--cycle">' +
          '<"dt-filter dt-filter--step">' +
          '<"dt-filter dt-filter--date-from">' +
          '<"dt-filter dt-filter--date-to">' +
          '<"ms-lg-auto"f>' +
        '>' +
      '>' +
      '<"table-responsive"t>' +
      '<"card-footer d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 px-4"' +
        '<"dt-info"i>' +
        '<"dt-paging"p>' +
      '>',
    initComplete() {
      buildTypeFilter();
      buildSelectFilter(this.api(), COL.PROGRAM, 'dt-filter--cycle', "{{ __('admin.All_Programs') }}", 'cycle');
      buildSelectFilter(this.api(), COL.STEP,    'dt-filter--step',  "{{ __('admin.All_Steps') }}",    'step');
      buildDateFilter('date-from', 'dt-filter--date-from', "{{ __('cycles.Start_Date') }}", 'from');
      buildDateFilter('date-to',   'dt-filter--date-to',   "{{ __('cycles.End_Date') }}",   'to');
      // Normalise sizes injected by DataTables
      $('.dataTables_filter .form-control').removeClass('form-control-sm').css('width', '');
      $('.dataTables_length .form-select').removeClass('form-select-sm');
    },
  });

  /* ── Type filter ─────────────────────────────────────────────── */
  function buildTypeFilter() {
    const TYPE_OPTIONS = [
      { value: "{{ __('cycles.Online_Training') }}", label: "{{ __('cycles.Online_Training') }}" },
      { value: "{{ __('cycles.Online_Session') }}",  label: "{{ __('cycles.Online_Session') }}" },
      { value: "{{ __('cycles.Offline_Session') }}", label: "{{ __('cycles.Offline_Session') }}" },
    ];
    const $select = buildSelect('type-filter', "{{ __('admin.All_Types') }}", 'dt-filter--type');
    TYPE_OPTIONS.forEach(opt => $select.append(`<option value="${opt.value}">${opt.label}</option>`));
    $select.on('change', function () { filters.type = this.value; table.draw(); });
  }

  /* ── Generic column-text select filter ──────────────────────── */
  function buildSelectFilter(api, colIndex, containerClass, placeholder, filterKey) {
    const seen = new Set();
    api.column(colIndex).data().each(function (cellHtml) {
      const div  = document.createElement('div');
      div.innerHTML = cellHtml;
      const text = div.textContent.replace(/\s+/g, ' ').trim();
      if (text) seen.add(text);
    });
    const $select = buildSelect(`${filterKey}-filter`, placeholder, containerClass);
    [...seen].sort().forEach(val => $select.append(`<option value="${val}">${val}</option>`));
    $select.on('change', function () { filters[filterKey] = this.value; table.draw(); });
  }

  /* ── Date picker filters ─────────────────────────────────────── */
  function buildDateFilter(idSuffix, containerClass, placeholder, filterKey) {
    const $input = $(`<input type="text" id="filter-${idSuffix}" class="form-control" placeholder="${placeholder}" style="min-width:110px; max-width:130px;">`)
      .appendTo(`#tblTraining_wrapper .${containerClass}`);

    $input[0]._flatpickr && $input[0]._flatpickr.destroy();
    $input.flatpickr({
      monthSelectorType: 'static',
      dateFormat: 'Y-m-d',
      allowInput: true,
      onChange([date]) {
        dateFilters[filterKey] = date ? date.toISOString().slice(0, 10) : '';
        table.draw();
      },
      onReady(_, __, instance) {
        const btn = Object.assign(document.createElement('button'), { type: 'button', innerHTML: 'Clear' });
        btn.classList.add('btn', 'btn-sm', 'btn-outline-secondary', 'mt-1');
        btn.addEventListener('click', () => {
          instance.clear();
          instance.close();
          dateFilters[filterKey] = '';
          table.draw();
        });
        instance.calendarContainer.appendChild(btn);
      },
    });
  }

  function buildSelect(id, placeholder, containerClass) {
    return $(`<select id="${id}" class="form-select" style="min-width:120px; max-width:150px;"><option value="">${placeholder}</option></select>`)
      .appendTo(`#tblTraining_wrapper .${containerClass}`);
  }

  /* ── Export ──────────────────────────────────────────────────── */
  window.exportTraining = function (format) {
    try {
      table.button(`.buttons-${format}`).trigger();
    } catch (err) {
      console.error('Export error:', err);
      releaseFailureAlert({ message: 'Failed to export data. Please try again.' });
    }
  };

  /* ================================================================
   * Session Offcanvas — type-dependent field visibility
   * ============================================================== */
  const FIELD_MAP = {
    online:   { show: ['#fieldDateRange', '#fieldOnlineCourse'],                         hide: ['#fieldDateTime', '#fieldTimes', '#fieldTrainer', '#fieldTrainerEmail', '#fieldAddress', '#fieldLocation'] },
    sessions: { show: ['#fieldDateTime', '#fieldTimes', '#fieldTrainer', '#fieldTrainerEmail'], hide: ['#fieldDateRange', '#fieldOnlineCourse', '#fieldAddress', '#fieldLocation'] },
    offline:  { show: ['#fieldDateTime', '#fieldTimes', '#fieldTrainer', '#fieldTrainerEmail', '#fieldAddress', '#fieldLocation'], hide: ['#fieldDateRange', '#fieldOnlineCourse'] },
  };

  function applyFieldVisibility(type) {
    const map = FIELD_MAP[type] || FIELD_MAP.online;
    map.show.forEach(sel => $(sel).show());
    map.hide.forEach(sel => $(sel).hide());
  }

  /* ── Select2 (initialised once, dropdownParent keeps it inside offcanvas) ── */
  $('#sessionCourse').select2({
    dropdownParent: $('#sessionOffcanvas'),
    placeholder:    '{{ __('admin.select_course') }}',
    allowClear:     true,
  });

  /* ── Flatpickr instances (created once, reused) ─────────────── */
  const datePicker = $('#sessionDate').flatpickr({
    monthSelectorType: 'static',
    dateFormat: 'Y-m-d',
    allowInput: true,
  });

  const timeCfg = { enableTime: true, noCalendar: true, dateFormat: 'h:i K', time_24hr: false };
  const timeFromPicker = $('#sessionTimeFrom').flatpickr(timeCfg);
  const timeToPicker   = $('#sessionTimeTo').flatpickr(timeCfg);

  /* ── Date Range Picker (initialised once; options updated on edit) ── */
  function initDateRangePicker(startDate, endDate) {
    const opts = {
      opens:      isRtl ? 'left' : 'right',
      drops:      'down',
      parentEl:   'body',
      autoUpdateInput: !!startDate,
      minDate: moment().format('MM/DD/YYYY'),
    };
    if (startDate) opts.startDate = moment(startDate, 'YYYY-MM-DD');
    if (endDate)   opts.endDate   = moment(endDate,   'YYYY-MM-DD');

    $('#sessionDateRange').daterangepicker(opts)
      .off('apply.daterangepicker cancel.daterangepicker')
      .on('apply.daterangepicker', function (_, picker) {
                        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        $(this).removeClass('is-invalid');
        $('#errorDateRange').text('');
      })
      .on('cancel.daterangepicker', function () {
        $(this).val('');
        });
  }

  /* ── Type change handler ─────────────────────────────────────── */
  $('#sessionType').on('change', function () {
    $('#sessionTypeHidden').val(this.value);
    applyFieldVisibility(this.value);
    clearValidationErrors();
  });

  /* ================================================================
   * Offcanvas — show handler (create / edit)
   * ============================================================== */
  const $offcanvas = $('#sessionOffcanvas');

  $offcanvas.on('show.bs.offcanvas', function (e) {
    const $trigger = $(e.relatedTarget);
    const mode     = $trigger.data('mode');
    const isEdit   = mode === 'edit';

    $('#sessionOffcanvasLabel').text(
      isEdit ? '{{ __('cycles.Edit training session') }}' : '{{ __('cycles.Add new training session') }}'
    );
    $('#sessionSubmitText').text(isEdit ? LABELS.editText : LABELS.submitText);

    if (isEdit) {
      fetchAndPopulateSession($trigger.data('id'));
    } else {
      resetOffcanvas();
    }
  });

  $offcanvas.on('hidden.bs.offcanvas', function () {
    resetOffcanvas();
  });

  /* ── Fetch session data for edit ─────────────────────────────── */
  function fetchAndPopulateSession(sessionId) {
    setSubmitLoading(true);

    $.ajax({
      type: 'POST',
      url:  ROUTES.fetch,
      data: { id: sessionId },
      success(response) {
        populateOffcanvas(response);
        setSubmitLoading(false);
      },
      error(err) {
        console.error(err);
        releaseFailureAlert({ message: '{{ __('admin.error_fetching_data') }}' });
        $offcanvas.offcanvas('hide');
      }
    });
  }

  function populateOffcanvas(response) {
    const type = response.type;
    const dr   = response.daterange || '';
    const [rangeStart, rangeEnd] = dr.includes(' - ') ? dr.split(' - ') : [dr, dr];

    // Hidden fields
    $('#sessionFormMethod').val('PUT');
    $('#sessionForm').attr('action', ROUTES.update(response.id));
    $('#sessionStepId').val(response.step_id);
    $('#sessionSid').val(response.id);

    // Pre-select Program & Step dropdowns
    // Set program first — trigger('change') calls filterSteps() synchronously,
    // which rebuilds the step options for this cycle. Then set step.
    $('#program').val(response.cycle_id || '').trigger('change');
    $('#step').val(response.step_id || '').trigger('change.select2');

    // Visible fields — type is fixed on edit (disable to prevent accidental change)
    $('#sessionType').val(type).prop('disabled', true);
    $('#sessionTypeHidden').val(type);
    $('#sessionTitle').val(response.title);
    $('#sessionDsc').val(response.dsc);

    applyFieldVisibility(type);

    if (type === 'online') {
      initDateRangePicker(rangeStart, rangeEnd);
      const displayVal = rangeStart && rangeEnd
        ? moment(rangeStart, 'YYYY-MM-DD').format('MM/DD/YYYY') + ' - ' + moment(rangeEnd, 'YYYY-MM-DD').format('MM/DD/YYYY')
        : '';
      $('#sessionDateRange').val(displayVal);
      $('#sessionCourse').val(response.course_id || '').trigger('change');
    } else {
      datePicker.setDate(rangeStart || '', false);
      timeFromPicker.setDate(response.fromtime || '', false);
      timeToPicker.setDate(response.endtime || '', false);

      $('#sessionTrainerName').val(response.trainer_name || '');
      $('#sessionTrainerEmail').val(response.trainer_email || '');

      if (type === 'offline') {
        $('#sessionTrainingAddress').val(response.training_Address || '');
        $('#sessionTrainingLocation').val(response.training_location || '');
      }
    }
  }

  /* ── Reset offcanvas to create state ─────────────────────────── */
  function resetOffcanvas() {
    $('#sessionForm')[0].reset();
    $('#sessionFormMethod').val('POST');
    $('#sessionForm').attr('action', ROUTES.store);
    $('#sessionStepId').val(BASE_ID);
    $('#sessionSid').val('');
    // date range picker cleared via initDateRangePicker below
    $('#sessionCourse').val('').trigger('change');
    datePicker.clear();
    timeFromPicker.clear();
    timeToPicker.clear();
    $('#sessionType').prop('disabled', false);
    $('#sessionTypeHidden').val('online');
    applyFieldVisibility('online');
    initDateRangePicker(null, null);
    clearValidationErrors();
    setSubmitLoading(false);
  }

  /* ================================================================
   * Form Validation & Submit
   * ============================================================== */
  function clearValidationErrors() {
    $('#sessionForm .is-invalid').removeClass('is-invalid');
    $('#sessionForm .invalid-feedback').text('');
  }

  function validateForm() {
    const type       = $('#sessionType').val();
    const title      = $('#sessionTitle').val().trim();
    const dateRange  = $('#sessionDateRange').val().trim();
    const date       = $('#sessionDate').val().trim();
    const timeFrom   = $('#sessionTimeFrom').val().trim();
    const timeTo     = $('#sessionTimeTo').val().trim();
    let isValid      = true;

    clearValidationErrors();

    if (!title) {
      $('#sessionTitle').addClass('is-invalid');
      $('#errorTitle').text('{{ __('cycles.Title_Required') }}');
      isValid = false;
    }

    if (!$('#program').val()) {
      $('#program').addClass('is-invalid');
      $('#errorProgram').text('{{ __('cycles.Program_Required') }}');
      isValid = false;
    }

    if (!$('#step').val()) {
      $('#step').addClass('is-invalid');
      $('#errorStep').text('{{ __('cycles.Step_Required') }}');
      isValid = false;
    }

    if (type === 'online') {
      if (!dateRange) {
        $('#sessionDateRange').addClass('is-invalid');
        $('#errorDateRange').text('{{ __('cycles.Date_Range_Required') }}');
        isValid = false;
      }
    } else {
      if (!date) {
        $('#sessionDate').addClass('is-invalid');
        $('#errorDate').text('{{ __('cycles.Date_Required') }}');
        isValid = false;
      }
      if (!timeFrom) {
        $('#sessionTimeFrom').addClass('is-invalid');
        $('#errorTimeFrom').text('{{ __('cycles.Start_Time_Required') }}');
        isValid = false;
      }
      if (!timeTo) {
        $('#sessionTimeTo').addClass('is-invalid');
        $('#errorTimeTo').text('{{ __('cycles.End_Time_Required') }}');
        isValid = false;
      }
    }

    return isValid;
  }

  // Clear error on input
  $('#sessionForm').on('input change', '.is-invalid', function () {
    $(this).removeClass('is-invalid');
    $(this).siblings('.invalid-feedback').text('');
    setSubmitLoading(false);
  });

  $('#sessionForm').on('submit', function (e) {
    if (!validateForm()) {
      e.preventDefault();
      return;
    }
    setSubmitLoading(true);
    // Controller returns back()->with(...) — allow native form submit and page reload.
    // The flash message and updated table data are rendered server-side on the redirected page.
  });

  function setSubmitLoading(loading) {
    const $btn = $('#sessionSubmitBtn');
    $btn.prop('disabled', loading);
    $btn.find('.btn-text').toggleClass('d-none', loading);
    $btn.find('.btn-spinner').toggleClass('d-none', !loading);
    if (!loading) {
      const isEdit = $('#sessionFormMethod').val() === 'PUT';
      $('#sessionSubmitText').text(isEdit ? LABELS.editText : LABELS.submitText);
    }
  }

  /* ================================================================
   * Passcode Modal
   * ============================================================== */
  $(document).on('click', '.show-passcode', function () {
    $('#passcode').text($(this).data('passcode'));
    $('#passcodeModal').modal('show');
  });

  /* ================================================================
   * Delete Session
   * ============================================================== */
  window.deleteme = function (id) {
    Swal.fire({
      title:             '{{ __('admin.SureDelete') }}',
      text:              '{{ __('admin.Recover') }}',
      icon:              'warning',
      showCancelButton:  true,
      confirmButtonText: '{{ __('admin.DYes') }}',
      cancelButtonText:  '{{ __('admin.No') }}',
      reverseButtons:    true,
      customClass: {
        confirmButton: 'btn btn-danger me-2',
        cancelButton:  'btn btn-label-secondary',
      },
      buttonsStyling: false,
    }).then(({ isConfirmed }) => {
      if (!isConfirmed) return;

      $.post(ROUTES.delete(id), { _token: CSRF })
        .done(() => {
          Swal.fire({
            title:            '{{ __('admin.Deleted') }}!',
            text:             '{{ __('admin.Course Deleted') }}.',
            icon:             'success',
            confirmButtonText:'{{ __('admin.OK') }}',
            customClass:      { confirmButton: 'btn btn-primary' },
            buttonsStyling:   false,
            timer:            1500,
            timerProgressBar: true,
          }).then(() => window.location.reload());
        })
        .fail(() => releaseFailureAlert({ message: '{{ __('admin.unexpected_error') }}' }));
    });
  };

  /* ================================================================
   * QR Code Generation
   * ============================================================== */
  window.createQrCode = function (id) {
    $.ajax({
      type: 'POST',
      url:  ROUTES.qr,
      data: { id },
      success(response) {
        releaseSuccessAlert({ message: response.massege || '{{ __('admin.success') }}' });
      },
      error(err) {
        console.error(err);
        releaseFailureAlert({ message: '{{ __('admin.unexpected_error') }}' });
      }
    });
  };

  /* ================================================================
   * Reports Modal
   * ============================================================== */
  let currentSessionId = null;

  window.openReportsModal = function (sessionId) {
    currentSessionId = sessionId;
    showModalState('loading');
    $('#reportsModal').modal('show');
    fetchSessionReports(sessionId);
  };

  function fetchSessionReports(sessionId, showLoading = true) {
    if (showLoading) showModalState('loading');

    return $.ajax({
      type:    'POST',
      url:     ROUTES.reports,
      data:    { id: sessionId },
      timeout: 30000,
      success(response) {
        if (response.success) {
          populateReportsModal(response.data);
          showModalState('content');
        } else {
          showModalState('error', response.message || '{{ __('admin.error_fetching_data') }}');
        }
      },
      error(xhr, status) {
        const msg = xhr.responseJSON?.message
          || (status === 'timeout' ? '{{ __('admin.request_timeout') }}' : '{{ __('admin.network_error') }}');
        showModalState('error', msg);
      }
    });
  }

  function showModalState(state, errorMsg) {
    $('#modalLoadingState').toggle(state === 'loading');
    $('#modalMainContent').toggle(state === 'content');
    $('#modalErrorState').toggle(state === 'error');
    if (state === 'error' && errorMsg) $('#errorMessage').text(errorMsg);
  }

  function populateReportsModal(data) {
    $('#sessionTitleSubtext').text(data.session?.title || '');
    $('#totalDuration').text(Math.round((data.summary?.total_duration / 60) * 100) / 100 || '–');
    $('#totalParticipants').text(data.summary?.total_participants || '–');
    $('#totalRecordings').text(data.summary?.total_recordings || '–');
    renderReportsTable(data.reports || []);
    renderRecordingsTable(data.recordings || [], data.recording_passcode);
  }

  function renderReportsTable(reports) {
    const $tbody = $('#reportsTableBody').empty();
    if (!reports.length) {
      $('#reportsTableWrapper').hide();
      $('#reportsEmptyState').show();
      return;
    }
    $('#reportsTableWrapper').show();
    $('#reportsEmptyState').hide();

    reports.forEach((r, i) => {
      const duration = Math.round((r.attendance_duration / 60) * 100) / 100;
      $tbody.append(`
        <tr>
          <td class="text-center fw-semibold">${i + 1}</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <i class="ti ti-user text-primary"></i>
              <h6 class="mb-0">${escHtml(r.participant_name || LABELS.unknown)}</h6>
            </div>
          </td>
          <td><span class="badge bg-label-info">${escHtml(r.participant_email || LABELS.notProvided)}</span></td>
          <td class="text-center"><small class="text-muted">${fmtDt(r.participant_join_date)}</small></td>
          <td class="text-center"><small class="text-muted">${fmtDt(r.participant_end_date)}</small></td>
          <td class="text-center"><span class="badge bg-label-success">${duration} ${LABELS.min}</span></td>
        </tr>
      `);
    });
  }

  function renderRecordingsTable(recordings, passcode) {
    const $tbody = $('#recordingsTableBody').empty();

    if (passcode) {
      $('#recordingPasscode').text(passcode);
      $('#recordingPasscodeContainer').show();
    } else {
      $('#recordingPasscodeContainer').hide();
    }

    if (!recordings.length) {
      $('#recordingsTableWrapper').hide();
      $('#recordingsEmptyState').show();
      return;
    }
    $('#recordingsTableWrapper').show();
    $('#recordingsEmptyState').hide();

    recordings.forEach((rec, i) => {
      const recType = (rec.recording_type || LABELS.video).replace(/_/g, ' ');
      $tbody.append(`
        <tr>
          <td class="text-center fw-semibold">${i + 1}</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <i class="ti ti-video text-warning"></i>
              <h6 class="mb-0 text-capitalize">${escHtml(recType)}</h6>
            </div>
          </td>
          <td class="text-center"><small class="text-muted">${fmtDt(rec.recording_time)}</small></td>
          <td class="text-center">
            <div class="btn-group btn-group-sm">
              ${rec.recording_view_url     ? `<a href="${rec.recording_view_url}"     target="_blank" class="btn btn-outline-primary btn-sm"><i class="ti ti-eye"></i></a>`     : ''}
              ${rec.recording_download_url ? `<a href="${rec.recording_download_url}" target="_blank" class="btn btn-outline-success btn-sm"><i class="ti ti-download"></i></a>` : ''}
            </div>
          </td>
        </tr>
      `);
    });
  }

  function setRefreshLoading($btn, loading) {
    $btn.prop('disabled', loading);
    $btn.find('.btn-text').toggleClass('d-none', loading);
    $btn.find('.btn-spinner').toggleClass('d-none', !loading);
  }

  $('#refreshReportsBtn').on('click', function () {
    if (!currentSessionId) return;
    const $btn = $(this);
    setRefreshLoading($btn, true);
    fetchSessionReports(currentSessionId, false).always(() => setRefreshLoading($btn, false));
  });

  $('#refreshRecordingsBtn').on('click', function () {
    if (!currentSessionId) return;
    const $btn = $(this);
    setRefreshLoading($btn, true);
    fetchSessionReports(currentSessionId, false).always(() => setRefreshLoading($btn, false));
  });

  $('#retryLoadBtn').on('click', () => fetchSessionReports(currentSessionId));

  /* ================================================================
   * Clipboard
   * ============================================================== */
  window.copyToClipboard = function (elementId) {
    const text = document.getElementById(elementId)?.textContent || '';
    const copy = (t) => {
      if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(t);
      }
      const ta = document.createElement('textarea');
      ta.value = t;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      return Promise.resolve();
    };
    copy(text).then(() => showToast({ message: LABELS.copiedMsg, type: 'success' }));
  };

  /* ================================================================
   * Utilities
   * ============================================================== */
  function fmtDt(val) {
    if (!val) return LABELS.notAvailable;
    const m = moment(val);
    return m.isValid() ? m.format('DD MMM YYYY, HH:mm') : LABELS.notAvailable;
  }

  function escHtml(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

});
</script>

<script>
  $(document).ready(function () {

    const allSteps = $('#step option').clone(); // store all steps

    function filterSteps() {
      const selectedProgram = $('#program').val();
      const selectedStep = $('#step').val();

      // clear current options
      $('#step').empty();

      // always add default option
      $('#step').append('<option value="">{{ __("cycles.Select Step") }}</option>');

      // filter and append
      allSteps.each(function () {
        const stepCycle = $(this).data('cycle');

        if (!selectedProgram || stepCycle == selectedProgram) {
          $('#step').append($(this).clone());
        }
      });

      // restore selected step if valid
      if ($('#step option[value="' + selectedStep + '"]').length) {
        $('#step').val(selectedStep);
      } else {
        $('#step').val(null);
      }

      // refresh Select2
      $('#step').trigger('change.select2');
    }

    // when program changes
    $('#program').on('change', function () {
      filterSteps();
    });

    // initial load (edit mode)
    filterSteps();
  });
</script>
@stop