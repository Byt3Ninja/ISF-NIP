@extends('layouts.admin')

@section('content')
  <!-- BEGIN: Content-->
  <div class="">
    <div class="">
      @php
        $tabs = [
          ['title' => __('cycles.All requests'), 'url' => 'dashboard/mentorship/sessions?show=all', 'show' => 'all', 'icon' => 'bulb'],
          ['title' => __('cycles.New requests only'), 'url' => 'dashboard/mentorship/sessions', 'show' => '', 'icon' => 'brand-speedtest'],
          ['title' => __('cycles.approved requests'), 'url' => 'dashboard/mentorship/sessions?show=approved', 'show' => 'approved', 'icon' => 'circle-check'],
          ['title' => __('cycles.disapproved requests'), 'url' => 'dashboard/mentorship/sessions?show=disapproved', 'show' => 'disapproved', 'icon' => 'playstation-x'],
        ]
      @endphp
      <x-navigation-pills :tabs="$tabs" />

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
              <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom">
                <h5 class="card-title mb-6 mb-md-0 text-capitalize">
                  {{ __('cycles.Mentorship Sessions') }}
                  @if (request()->show == 'all') 
                    ( {{ __('cycles.All requests') }} )
                  @elseif (request()->show == 'approved')
                    ( {{ __('cycles.approved requests') }} )
                  @elseif (request()->show == 'disapproved')
                    ( {{ __('cycles.disapproved requests') }} )
                  @else
                    ( {{ __('cycles.New requests only') }} )
                  @endif
                </h5>
                <div class="d-flex align-items-center gap-2">
                  <!-- Filter Modal Trigger -->
                  <button type="button" class="btn btn-label-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="ti ti-filter me-2"></i>
                    {{ __('cycles.Filter') }}
                  </button>
                  @if(request()->hasAny(['start_date', 'end_date', 'done', 'cycle_id', 'rate', 'session_type']))
                  <button onclick="resetFilters()" class="btn btn-outline-secondary">
                    <i class="ti ti-refresh me-2"></i>
                    {{ __('cycles.Reset') }}
                  </button>
                  @endif
                  <div class="btn-group">
                    <button type="button" class="btn btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="ti ti-file-export me-2"></i>{{ __('cycles.Export') }}
                    </button>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="javascript:void(0);" onclick="exportMentorship('csv')"><i class="ti ti-file-text me-2"></i>CSV</a></li>
                      <li><a class="dropdown-item" href="javascript:void(0);" onclick="exportMentorship('excel')"><i class="ti ti-file-spreadsheet me-2"></i>Excel</a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="card-datatable table-responsive">
                <table class="datatable-basic table" id="mentorship-sessions-table">
                  <thead class="table-light">
                    <tr>
                      <th>{{ __('cycles.Company') }}</th>
                      <th>{{ __('cycles.User/Title') }}</th>
                      <th>{{ __('cycles.Mentor') }}</th>
                      <th>{{ __('cycles.Cycle') }}</th>
                      <th>{{ __('cycles.Rate/Comment') }}</th>
                      <th>{{ __('mentor.session_type') }}</th>
                      <th>{{ __('cycles.Approved') }}</th>
                      <th>{{ __('cycles.Done') }}</th>
                      <th>{{ __('cycles.Date/Time') }}</th>
                      <th>{{ __('cycles.Comment') }}</th>
                      <th class="text-nowrap">{{ __('cycles.Created At') }}</th>
                      <th>{{ __('cycles.Actions') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($sessions as $session)
                      <tr>
                        <td style="width: 130px;">
                          <a  href="/dashboard/companyProfile/{{ $session->user->company->id ?? '' }}" class="d-flex align-items-center" style="width: 130px;">
                            <img 
                              onerror="this.src='/images/logo-placeholder.png'"
                              src="{{ $session->user->company->logo_url ?? '' }}" alt="logo" class="rounded-circle me-2 border bg-label-light flex-shrink-0" height="36" width="36">
                            <small class="text-dark">{{ $session->user->company->{'name_' . $l} ?? '' }}</small>
                          </a>
                        </td>
                        <td style="width: 130px;">
                          <div class="d-flex align-items-center">
                            <a href="/dashboard/users/{{ $session->user_id }}/edit">
                              @if( !in_array(env('APP_ENV', 'local'), ['local']) )
                              <img 
                                onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                                src="{{ $session->user->image_url }}" alt="avatar" class="rounded-circle me-2 flex-shrink-0" height="36" width="36">
                            @endif
                              </a>
                            <div class="overflow-hidden">
                              <small class="text-dark">{{ $session->user->{'first_name_' . $l} }} {{ $session->user->{'last_name_' . $l} }}</small>
                              <small class="d-block text-muted">{{ $session->user->{'title_' . $l} }}</small>
                            </div>
                          </div>
                        </td>
                        <td style="width: 130px;">
                          <a href="/dashboard/show/UserProfile/{{ $session->mentor->id }}" class="d-flex align-items-center" style="min-width: 130px;">
                            @if( !in_array(env('APP_ENV', 'local'), ['local']) )
                            <img 
                              onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                              src="{{ $session->mentor->image_url ?? '' }}" alt="avatar" class="rounded-circle me-2 flex-shrink-0" height="36" width="36">
                              @endif
                            <small class="text-dark">{{ $session->mentor->name ?? '' }}</small>
                          </a>
                        </td>
                        <td style="width: 120px;">
                          <small>{{ optional($cycles->firstWhere('id', $session->cycle_id))->title }}</small>
                        </td>
                        <td style="min-width: 150px; max-width: 150px;">
                          <div class="d-flex align-items-center gap-1 mb-1">
                            <strong>{{ number_format($session->rating ?? 0, 1) }}</strong>
                            <i class="ti ti-star-filled ti-16px text-warning"></i>
                          </div>

                          @if($session->rate_comment)
                            <p class="small text-muted d-block">{!! $session->rate_comment !!}</p>
                          @else
                            <small class="text-muted block">{{ __('admin.No comment provided') }}</small>
                          @endif
                        </td>
                        <td style="width: 80px;">
                          @php
                            $badgeColor = 'secondary';
                            $badgeIcon = 'screen-share';
                            if ($session->session_type == 'Online') {
                              $badgeColor = 'success';
                              $badgeIcon = 'video';
                            } elseif ($session->session_type == 'Offline'){
                              $badgeColor = 'warning';
                              $badgeIcon = 'map';
                            }else{
                              $badgeColor = 'success';
                              $badgeIcon = 'screen-share';
                            }
                          @endphp
                          
                          <small class="badge bg-label-{{ $badgeColor }}">
                            <i class="ti ti-{{ $badgeIcon }} ti-16px me-1"></i>
                            {{ $session->session_type ?? __('admin.Online') }}
                          </small>
                        </td>
                        <td style="width: 80px;">
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
                        <td style="min-width: 80px;">
                          <span class="badge bg-label-{{ $session->done ? 'success' : 'danger' }}">
                            {{ $session->done ? __('Yes') : __('No') }}
                          </span>
                        </td>
                        <td class="text-nowrap" style="min-width: 150px;">
                          @php $shownAt = \App\Services\TimezoneService::forViewer($session->zoom_date, $_user); @endphp
                          <small>{{ $shownAt ? $shownAt->format('d-m-Y') : '' }}</small>
                          <small class="d-block">{{ $shownAt ? $shownAt->format('H:i:s') : '' }} ({{ $session->period ?? '' }}M)</small>
                        </td>
                        <td class="text-nowrap" style="min-width: 150px;">
                          @if($session->comment)
                            <p class="small text-muted mb-0">{!! $session->comment !!}</p>
                          @else
                            <small class="text-muted">{{ __('admin.No comment provided') }}</small>
                          @endif
                        </td>
                        <td class="text-nowrap" style="width: 100px;">
                          <small>{{ $session->created_at ? $session->created_at->format('d-m-Y') : '' }}</small>
                          <small class="d-block">{{ $session->created_at ? $session->created_at->format('H:i:s') : '' }}</small>
                        </td>
                        <td style="min-width: 280px;">
                          <div class="d-flex flex-wrap gap-1">
                            <button class="btn-icon rounded-circle btn btn-sm btn-label-info" onclick="edit({{ $session->id }})" title="Edit">
                              <i class="ti ti-edit"></i>
                            </button>
                            @if( $session->datetime >= now() )
                            <button class="btn-icon rounded-circle btn btn-sm btn-label-danger" onclick="deleteme({{ $session->id }})" title="Delete">
                              <i class="ti ti-trash"></i>
                            </button>
                            @endif
                            @if (!$session->approved)
                              <button class="btn-icon rounded-circle btn btn-sm btn-label-success" onclick="approve({{ $session->id }}, 'Yes')" title="Approve">
                                <i class="ti ti-check"></i>
                              </button>
                              <button class="btn-icon rounded-circle btn btn-sm btn-label-danger" onclick="approve({{ $session->id }}, 'No')" title="Disapprove">
                                <i class="ti ti-x"></i>
                              </button>
                            @endif
                            @if ($session->join_url)
                              <a href="{{ $session->start_url }}" target="_blank" class="btn-icon rounded-circle btn btn-sm btn-label-secondary" title="Start Meeting">
                                <i class="ti ti-device-computer-camera"></i>
                              </a>
                              <a href="{{ $session->join_url }}" target="_blank" class="btn-icon rounded-circle btn btn-sm btn-label-primary" title="Join only">
                                <i class="ti ti-user"></i>
                              </a>
                              @if( !empty( $session->zoom_passcode ) )
                              <button data-passcode="{{ $session->zoom_passcode }}" title="Show Passcode" type="button"
                                class="btn-icon rounded-circle btn btn-sm btn-label-warning show-passcode">
                                <i class="ti ti-key"></i>
                              </button>
                              @endif
                            @endif
                            @if ($session->fileurl)
                              <a href="/dashboard/uploads/InviteMentor/{{ $session->fileurl }}" target="_blank" class="btn-icon rounded-circle btn btn-sm btn-light" title="Download attached">
                                <i class="ti ti-download"></i>
                              </a>
                            @endif
                            <button title="View Report" type="button" onclick="openReportsModal({{ $session->id }})" class="btn btn-icon btn-label-warning rounded-circle waves-effect">
                              <i class="ti ti-file-text"></i>
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
        </section>
      </div>
    </div>
  </div>
  <!-- END: Content-->

  <!-- Filter Modal -->
  <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <form id="filterForm" method="GET" action="{{ url()->current() }}" data-ignore-dirty>
          <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center" id="filterModalLabel">
              <i class="ti ti-filter me-2"></i> {{ __('cycles.Filter_Sessions') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Cancel') }}"></button>
          </div>

          <div class="modal-body">
            <div class="row g-4">
              <!-- Request Status -->
              <div class="col-12">
                <label for="showSelect" class="form-label">{{ __('cycles.Request_Status') }}</label>
                <select class="form-select text-capitalize" name="show" data-placeholder="{{ __('cycles.Select_Status') }}" id="showSelect">
                  <option value="" {{ !request()->show ? 'selected' : '' }}>{{ __('cycles.New requests only') }}</option>
                  <option value="all" {{ request()->show == 'all' ? 'selected' : '' }}>{{ __('cycles.All requests') }}</option>
                  <option value="approved" {{ request()->show == 'approved' ? 'selected' : '' }}>{{ __('cycles.approved requests') }}</option>
                  <option value="disapproved" {{ request()->show == 'disapproved' ? 'selected' : '' }}>{{ __('cycles.disapproved requests') }}</option>
                </select>
              </div>
            
              <!-- Done Filter -->
              <div class="col-12" id="completionFilter" style="display: {{ request()->show == 'all' || request()->show == 'approved' ? 'block' : 'none' }}">
                <label for="doneValueSelect" class="form-label">{{ __('cycles.Completion_Status') }}</label>
                <select class="form-select select2" name="done" data-placeholder="{{ __('cycles.Select_Completion_Status') }}" id="doneValueSelect">
                  <option value="all" {{ request()->done == 'all' ? 'selected' : '' }}>{{ __('cycles.All') }}</option>
                  <option value="yes" {{ request()->done == 'yes' ? 'selected' : '' }}>{{ __('admin.Yes') }}</option>
                  <option value="no" {{ request()->done == 'no' ? 'selected' : '' }}>{{ __('admin.No') }}</option>
                </select>
              </div>
              
              <!-- Cycle -->
              <div class="col-12">
                <label for="cycle_id" class="form-label">{{ __('cycles.Cycle') }}</label>
                <select class="form-select select2" name="cycle_id">
                  <option value="" disabled>{{ __('cycles.Select_Program') }}</option>
                  <option value="all" {{ !request()->cycle_id ? 'selected' : '' }}>{{ __('admin.All_Programs') }}</option>
                  @foreach ($cycles as $cycle)
                    <option value="{{ $cycle->id }}" {{ request()->cycle_id == $cycle->id ? 'selected' : '' }}>
                      {{ $cycle->title }}
                    </option>
                  @endforeach
                </select>
              </div>

              <!-- Session Type -->
              <div class="col-12">
                <label for="session_type" class="form-label">{{ __('mentor.session_type') }}</label>
                <select class="form-select select2" name="session_type">
                  <option value="all" {{ request()->session_type == 'all' ? 'selected' : '' }}>{{ __('admin.All') }}</option>
                  <option value="Online" {{ request()->session_type == 'Online' ? 'selected' : '' }}>{{ __('mentor.online') }}</option>
                  <option value="Offline" {{ request()->session_type == 'Offline' ? 'selected' : '' }}>{{ __('mentor.offline') }}</option>                
                </select>
              </div>

              <!-- Rating -->
              <div class="col-12" id="rateFilter" style="display: {{ request()->show == 'all' || request()->show == 'approved' ? 'block' : 'none' }}">
                <label for="rate" class="form-label">{{ __('cycles.Rating') }}</label>
                <select class="form-select select2" name="rate">
                  <option value="all" {{ request()->rate == 'all' ? 'selected' : '' }}>{{ __('admin.All') }}</option>
                  <option value="1" {{ request()->rate == 1 ? 'selected' : '' }} > 1 </option>
                  <option value="2" {{ request()->rate == 2 ? 'selected' : '' }} > 2 </option>
                  <option value="3" {{ request()->rate == 3 ? 'selected' : '' }} > 3 </option>
                  <option value="4" {{ request()->rate == 4 ? 'selected' : '' }} > 4 </option>
                  <option value="5" {{ request()->rate == 5 ? 'selected' : '' }} > 5 </option>
                </select>
              </div>

              <!-- Start Date -->
              <div class="col-12 col-md-6">
                <label for="start_date" class="form-label">{{ __('cycles.Start_Date') }}</label>
                <input type="text" id="filter_start_date" name="start_date" class="form-control datepicker-filter"
                  value="{{ request('start_date') }}" placeholder="{{ __('cycles.Start_Date') }}">
                <div class="invalid-feedback" id="start_date_error"></div>
              </div>

              <!-- End Date -->
              <div class="col-12 col-md-6">
                <label for="end_date" class="form-label">{{ __('cycles.End_Date') }}</label>
                <input type="text" id="filter_end_date" name="end_date" class="form-control datepicker-filter"
                  value="{{ request('end_date') }}" placeholder="{{ __('cycles.End_Date') }}">
                <div class="invalid-feedback" id="end_date_error"></div>
              </div>

            </div>
          </div>

          <div class="modal-footer mt-6">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('cycles.Apply_Filters') }}</button>
          </div>
        </form>
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
  <div class="modal fade text-left" id="ModifyRequest" tabindex="-1" role="dialog" aria-labelledby="sendrequest"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <form id="ModifyRequestForm" method="post" action="/dashboard/mentorship/modify" enctype="multipart/form-data">
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
            <button type="submit" class="btn btn-primary ml-1" id="submitModifyRequest">
              {{-- <i class="ti ti-check"></i> --}}
              <span class="">{{ __('cycles.Submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@stop

@section('pagescripts')
  <script>
    var is_submitting = false;
    $(document).on('click', '.show-passcode', function(e) {
      e.preventDefault();
      let passcode = $(this).data('passcode');
      releaseAlert('copy', {
        title: '{{ __('dashboard.meeting_passcode') }}',
        message: `{!! __('dashboard.passcode_is', ['code' => '<strong>${passcode}</strong>']) !!}`,
        copyText: passcode
      });
    });

    $(document).on('click', '#submitModifyRequest', function(e) {
      e.preventDefault();
      if (is_submitting) { return; }
      else
      {
        is_submitting = true;
        $(this).prop('disabled', true).html('<i class="fas fa-fw fa-sm fa-spinner fa-spin"></i> {{ __("cycles.Submitting") }}...');
        $('#ModifyRequestForm').submit();
      }
    });

    // Helper function to copy text to clipboard
    function copyToClipboard(elementId) {
      const element = document.getElementById(elementId);
      const text = element.textContent;
      
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
          showToast({ message: '{{ __("admin.copied_to_clipboard") }}', type: 'success' });
        }).catch(() => {
          showToast({ message: '{{ __("admin.copy_failed") }}', type: 'danger' });
        });
      } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
          document.execCommand('copy');
          showToast({ message: '{{ __("admin.copied_to_clipboard") }}', type: 'success' });
        } catch (err) {
          showToast({ message: '{{ __("admin.copy_failed") }}', type: 'danger' });
        }
        document.body.removeChild(textArea);
      }
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
        url: "/dashboard/mentorship/session-reports-data",
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
      btn.html('<i class="ti ti-loader-2 ti-spin me-1"></i>{{ __("admin.refreshing") }}...');
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
      btn.html('<i class="ti ti-loader-2 ti-spin me-1"></i>{{ __("admin.refreshing") }}...');
      btn.prop('disabled', true);
      
      fetchSessionData(currentSessionId, false).always(function() {
        btn.html(originalHtml);
        btn.prop('disabled', false);
      });
    }

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
          timeZoneName: 'short'
        };
        
        return date.toLocaleString('{{ app()->getLocale() }}', options);
      } catch (error) {
        console.error('Date formatting error:', error);
        return '{{ __("admin.invalid_date") }}';
      }
    }

    // Event Listeners
    $(document).ready(function() {
      $('#refreshReportsBtn').on('click', refreshReports);
      $('#refreshRecordingsBtn').on('click', refreshRecordings);
      $('#retryLoadBtn').on('click', () => fetchSessionData(currentSessionId));
    });
    $(document).ready(function() {
    let mentorshipTable = $('#mentorship-sessions-table').DataTable({
      "responsive": true,
      "searching": true,
      "order": [],
      "columnDefs": [
        {
          "orderable": false,
          "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8]
        }
      ],
        "language": {
          emptyTable: "{{ $l === 'ar' ? 'لا توجد جلسات ارشاد متاحة' : 'No Mentorship Sessions Found' }}",
         @if ($l === 'ar')
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
      @endif
       },
      buttons: [
        { extend: 'csv', text: '<i class="ti ti-file-text me-1"></i>CSV', className: 'd-none', title: `Mentorship_Sessions_Export_${new Date().toISOString().slice(0, 10)}`, exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 9] } },
        { extend: 'excel', text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel', className: 'd-none', title: `Mentorship_Sessions_Export_${new Date().toISOString().slice(0, 10)}`, exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 9] } }
      ]
      });

    // Global export mentorship function
    window.exportMentorship = function (format) {
      if (!mentorshipTable) {
        alert('{{ __("admin.no_data_to_export") }}');
        return;
      }
      try {
        mentorshipTable.button(`.buttons-${format}`).trigger();
      } catch (error) {
        console.error('Export error:', error);
        alert('{{ __("admin.export_failed") }}');
      }
    }
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

    // Initialize flatpickr for filter modal date pickers with validation
    let startDatePicker = $('#filter_start_date').flatpickr({
      enableTime: false,
      dateFormat: "Y-m-d",
      onChange: function(selectedDates, dateStr, instance) {
        // Clear any previous error
        $('#filter_start_date').removeClass('is-invalid');
        $('#start_date_error').text('');
        
        // Update end date minDate
        if (selectedDates.length > 0 && endDatePicker) {
          endDatePicker.set('minDate', selectedDates[0]);
        }
      }
    });

    let endDatePicker = $('#filter_end_date').flatpickr({
      enableTime: false,
      dateFormat: "Y-m-d",
      onChange: function(selectedDates, dateStr, instance) {
        // Clear any previous error
        $('#filter_end_date').removeClass('is-invalid');
        $('#end_date_error').text('');
        
        // Validate against start date
        const startDate = $('#filter_start_date').val();
        if (startDate && dateStr && startDate > dateStr) {
          $('#filter_end_date').addClass('is-invalid');
          $('#end_date_error').text('{{ $l == "en" ? "End date must be on or after start date" : "تاريخ النهاية يجب أن يكون في أو بعد تاريخ البداية" }}');
        }
      }
    });

    // Form submission validation
    $('#filterForm').on('submit', function(e) {
      const startDate = $('#filter_start_date').val();
      const endDate = $('#filter_end_date').val();
      
      // Check if there are already validation errors visible
      if ($('#filter_end_date').hasClass('is-invalid') || $('#filter_start_date').hasClass('is-invalid')) {
        e.preventDefault();
        return false;
      }
      
      // Validate if both dates are selected
      if (startDate && endDate) {
        if (endDate < startDate) {
          e.preventDefault();
          $('#filter_end_date').addClass('is-invalid');
          $('#end_date_error').text('{{ $l == "en" ? "End date must be on or after start date" : "تاريخ النهاية يجب أن يكون في أو بعد تاريخ البداية" }}');
          return false;
        }
      }
      
      return true;
    });

    // Reset validation when modal is closed
    $('#filterModal').on('hidden.bs.modal', function() {
      $('#filter_start_date').removeClass('is-invalid');
      $('#filter_end_date').removeClass('is-invalid');
      $('#start_date_error').text('');
      $('#end_date_error').text('');
    });

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

    // Note: searchBot element doesn't exist in the DOM, so this event listener is commented out
    // If you need this functionality, add a button with id="searchBot" to the HTML
    // document.getElementById('searchBot')?.addEventListener('click', redirectWithDates);

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
      const isApproving = stat === 'Yes';
      const successMessage = isApproving
        ? "{{ __('admin.mentorship_session_approved_successfully') }}"
        : "{{ __('admin.mentorship_session_rejected_successfully') }}";
      const failureMessage = isApproving
        ? "{{ __('admin.mentorship_session_approve_failed') }}"
        : "{{ __('admin.mentorship_session_reject_failed') }}";

      releaseAlert('warning', {
        title: isApproving ? "{{ __('admin.Approve') }}" : "{{ __('admin.Disapprove') }}",
        message: (isApproving ? "{{ __('admin.Accepting_request') }}" : "{{ __('admin.Rejecting_request') }}").trim(),
        confirmText: "{{ __('admin.Confirm') }}",
        cancelText: "{{ __('admin.Cancel') }}",
        confirmColor: isApproving ? 'success' : 'danger',
        onConfirm: function () {
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
            success: function (response) {
              toastr.success(successMessage);
              setTimeout(function () { location.reload(); }, 1500);
            },
            error: function (xhr) {
              toastr.error(xhr.responseJSON?.message || failureMessage);
            }
          });
        }
      });
    }

    function resetFilters() {
      const url = new URL(window.location);
      url.searchParams.delete('start_date');
      url.searchParams.delete('end_date');
      url.searchParams.delete('done');
      url.searchParams.delete('cycle_id');
      url.searchParams.delete('rate');
      url.searchParams.delete('session_type');
      window.location.href = url.toString();
    }

    // Handle show filter change
    $('#showSelect').on('change', function() {
      // Show rating and completion filters if 'show' is 'approved' or 'all'
      if (this.value === 'approved' || this.value === 'all') {
        $('#rateFilter').show();
        $('#completionFilter').show();
      } else {
        $('#rateFilter').hide();
        $('#completionFilter').hide();
      }
    });
  </script>
@stop
