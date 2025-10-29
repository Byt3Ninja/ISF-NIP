@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/pickr/pickr-themes.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
  <link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify.min.css">
  <link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify-bootstrap.min.css">
@stop

@php
  $types = [
    'onetoall' => 'Assign One to all users',
    'grouptoall' => 'Assign Group to all users',
    'grouptogroup' => 'Assign Group to group of users',
  ];
  $status = strtolower($evaluationForm?->status);
@endphp

@section('content')
  <!-- Content wrapper -->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-overlay"></div>
      <div class="content-body">
        <section id="multiple-column-form">
          @if (session()->has('message'))
            <x-alert type="success" message="{{ __('admin.' . session()->get('message')) }}" />
          @endif
          @if (session()->has('message2'))
            <x-alert type="danger" message="{{ __('admin.' . session()->get('message2')) }}" />
          @endif
          <!-- Validation messages -->
          @if (count($errors) > 0)
            @foreach ($errors->all() as $error)
              <x-alert type="danger" message="{{ $error }}" />
            @endforeach
          @endif
          <!-- / Alert -->

          <div class="row match-height">
            <div class="col-xl-12 col-md-12 col-sm-12">
              <div class="card mb-4">
                <div class="card-header" style="padding-bottom: 15px;">
                  <h4 class="card-title">{{ __('cycles.Portal Final Screening list') }}</h4>
                </div>
                <hr>
                <div class="card-body">
                  <div class="row">
                    <div class="col-12 col-lg-6">
                      <label class="fs-6 fw-semibold mb-1">{{ __('cycles.Select step from list to manage') }}</label>
                      <select id="cstep" class="select2 form-select"
                        onchange="window.open('/dashboard/'+this.options[this.selectedIndex].value+'/final','_self');">
                        @foreach ($steps as $step)
                          <option value="{{ $step->id }}" {{ $step->id == $id ? 'selected' : '' }}>{{ $step->title }}
                            ({{ __('cycles.Cycle') }} "{{ $step->cycle->title }}" - {{ __('cycles.Start') }}
                            {{ $step->from->format('d-m-Y') ?? '-' }})
                            - {{ optional(optional($step->cycle)->organization)->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-12 col-lg-6">
                      <div class="d-flex align-items-end h-100 flex-wrap gap-2 mt-3 mt-lg-0">
                        <button type="button" class="btn btn-label-info" onclick="DoChange(1)">
                          <i class="ti ti-edit"></i>
                          <span class="mx-1">{{ __('cycles.Edit') }}</span>
                        </button>
                        <button type="button" class="btn btn-label-secondary"
                          onclick="window.location.href='/dashboard/final/results/{{ $id }}'">
                          <i class="ti ti-user"></i> <span class="mx-1">{{ __('cycles.Results') }}</span>
                        </button>
                        <a class="btn btn-label-success" href="/dashboard/cycles/{{ $cstep->cycle_id ?? null }}">
                          <i class="ti ti-pencil"></i>
                          <span class="mx-1">{{ __('cycles.Cycle') }}</span>
                        </a>
                        <button type="button" class="btn btn-label-danger" onclick="DoChange(2)">
                          <i class="ti ti-trash"></i>
                          <span class="mx-1">{{ __('cycles.Delete') }}</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @if (!$cstep)
                <div class="card">
                  <div class="card-header border-bottom">
                    <h4 class="card-title">{{ __('cycles.Error') }}</h4>
                  </div>
                  <div class="card-body">
                    {{ __('cycles.No records Add final') }}
                    <a href="/dashboard/cycles">{{ __('cycles.Cycle') }}</a>.
                  </div>
                </div>
              @endif
            </div>
          </div>
        </section>
        <section class="row">
          @if (isset($cstep))
            <div class="col-xl-12 col-md-12 col-sm-12">
              <div class="card">
                <form id="judges-form" action="/dashboard/final/update" method="POST" enctype="multipart/form-data">
                  {!! csrf_field() !!}
                  <div class="card-header border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <h5 class="card-title mb-1">
                          {{ $l == 'en' ? 'Startups Final Screening' : $cstep->title }}
                        </h5>
                        <p class="text-muted mb-1">{{ __('cycles.Select judges from the menu') }}.</p>
                        <small class="text-muted">
                          {{ __('cycles.from') }} {{ $cstep->from->format('d-m-Y') ?? '-' }} 
                          {{ __('cycles.to') }} {{ $cstep->to->format('d-m-Y') ?? '-' }}
                        </small>
                      </div>
                      {{-- <span class="badge bg-label-primary">
                        {{ count($companies) }} {{ __('cycles.Companies') }}
                      </span> --}}
                      <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="teamsExportBtn">
                          <i class="bi bi-download me-1"></i> {{ $l == 'ar' ? 'تصدير' : 'Export' }}
                        </button>
                        <ul class="dropdown-menu">
                          <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportTable('mentorstable', 'csv')"><i class="bi bi-filetype-csv me-2"></i>{{ $l == 'ar' ? 'تصدير CSV' : 'Export CSV' }}</a></li>
                          <li><a class="dropdown-item" href="javascript:void(0)" onclick="exportTable('mentorstable', 'excel')"><i class="bi bi-file-earmark-excel me-2"></i>{{ $l == 'ar' ? 'تصدير Excel' : 'Export Excel' }}</a></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="card-text">
                      <input type="hidden" value="{{ $cstep->id }}" name="step">
                      
                      <div id="judges-assignment-section" class="d-none">
                        <div class="alert alert-info my-4">
                          <h6 class="alert-heading mb-1">{{ __('cycles.Judge Assignment') }}</h6>
                          <p class="mb-0">{{ __('cycles.Select judges to assign to the selected startups') }}.</p>
                        </div>

                        <div class="mb-4">
                          <label class="form-label fw-semibold">{{ __('cycles.Select Judges') }}</label>
                          <select id="select-judges" name="judges[]" class="select2 form-select"
                            multiple="multiple" data-placeholder="{{ __('cycles.Choose judges for assignment') }}">
                            @foreach ($judges as $judge)
                              <option value="{{ $judge->id }}">
                                {{ $judge->{'first_name_' . $l} }} {{ $judge->{'last_name_' . $l} }} ({{ $judge->email }})
                              </option>
                            @endforeach
                          </select>
                          <div id="judges-error" class="invalid-feedback" style="display: none;">
                            {{ __('cycles.Judges required') }}
                          </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                          <div>
                            <strong><span id="assignment-selected-count">0</span> {{ __('cycles.startups selected') }}</strong>
                          </div>
                          <button type="submit" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>
                            {{ __('cycles.Assign Judges') }}
                          </button>
                        </div>
                      </div>



                      <div class="table-responsive">
                        <table class="table" id="mentorstable">
                          <thead class="table-light">
                            <tr>
                              <th style="width: 40px;">
                                <div class="form-check">
                                  <input class="form-check-input" type="checkbox" id="checkAll">
                                  <label class="form-check-label" for="checkAll"></label>
                                </div>
                              </th>
                              <th>{{ __('cycles.Company') }}</th>
                              <th>{{ __('cycles.Users') }}</th>
                              <th>{{ __('cycles.Judges') }}</th>
                              <th>{{ __('cycles.Actions') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($companies as $company)
                              <tr>
                                <td>
                                  <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                      id="checkbox{{ $company->id }}"
                                      {{ in_array($company->id, []) ? 'checked' : '' }} name="users[]"
                                      value="{{ $company->id }}">
                                    <label class="form-check-label" for="checkbox{{ $company->id }}"></label>
                                  </div>
                                </td>
                                <td>
                                  <a href="/dashboard/companyProfile/{{ $company->id }}">
                                    <div class="d-flex align-items-center">
                                      <span class="media-left me-2">
                                        <img
                                          src="{{ $company->logo_url ? $company->logo_url : '/images/logo-placeholder.png' }}"
                                          onerror="this.src='/images/logo-placeholder.png'" alt="avatar"
                                          class="rounded-circle" height="40" width="40" />
                                      </span>
                                      <div class="d-flex align-items-center">
                                        {{ $company->name }}
                                      </div>
                                    </div>
                                  </a>
                                </td>
                                <td>
                                  <a href="/dashboard/users/{{ $company->resolveUser()->id }}/edit" class="d-flex align-items-center gap-2">
                                    @if( !in_array(env('APP_ENV', 'local'), ['local']) )
                                      <img
                                        src="{{ isset($company->resolveUser()->img_url) ? asset("191014/{$company->resolveUser()->img_url}") : asset('icons/account.svg') }}"
                                        onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}" alt="avatar" class="rounded-circle"
                                        height="40" width="40" />
                                    @endif
                                    <div class="media-body">
                                      <h6 class="media-heading mb-0">{{ $company->resolveUser()->name }} </h6>
                                      <span class="font-small-2">{{ $company->resolveUser()->title }}</span>
                                    </div>
                                  </a>
                                </td>
                                <td>
                                  @php
                                    $judges = \App\User::whereIn('id', $company->finals($cstep->id))->get();
                                    $judgesCount = $judges->count();
                                    $visibleJudges = $judges->take(3);
                                    $remainingCount = $judgesCount - 3;
                                  @endphp
                                  
                                  @if($judgesCount === 0)
                                    <span class="text-muted small">{{ __('cycles.No judges yet') }}</span>
                                  @else
                                    <div class="d-flex flex-wrap gap-1" data-all-judges="{{ $judges->map(function($judge) { return ['name' => $judge->name, 'avatar' => $judge->image_url]; })->toJson() }}">
                                      @foreach ($visibleJudges as $judge)
                                        <span class="btn btn-sm btn-outline-secondary rounded-pill d-flex align-items-center gap-1" style="padding: 4px 8px;">
                                          <img src="{{ $judge->image_url }}" 
                                               onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}" 
                                               alt="{{ $judge->name }}" 
                                               class="rounded-circle" 
                                               height="16" 
                                               width="16">
                                          <span class="text-truncate" style="max-width: 100px;">{{ $judge->name }}</span>
                                        </span>
                                      @endforeach
                                      
                                      @if($remainingCount > 0)
                                        <span class="btn btn-sm btn-outline-secondary rounded-pill">
                                          +{{ $remainingCount }}
                                        </span>
                                      @endif
                                    </div>
                                  @endif
                                </td>
                                <td>
                                  @php
                                    $final_session = $company->fsession($cstep->id);
                                  @endphp
                                  
                                  <div class="d-flex gap-1 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-icon rounded-circle btn-label-info" onclick="viewDetails(this, {{ $company->id }})" title="{{ __('cycles.View Details') }}">
                                      <i class="ti ti-eye"></i>
                                    </button>
                                    
                                    @if (!$final_session)
                                      <span class="btn btn-sm btn-icon rounded-circle btn-label-secondary disabled" title="{{ __('cycles.Add Judges first') }}">
                                        <i class="ti ti-plus"></i>
                                      </span>
                                    @else
                                      @if($final_session && $final_session->join_url)
                                        <button type="button" class="btn btn-sm btn-icon rounded-circle btn-label-primary" onclick="edit({{ $company->id }}, '{{ $final_session->datetime }}', '{{ $final_session->duration }}')" title="{{ __('cycles.Edit Session') }}">
                                          <i class="ti ti-edit"></i>
                                        </button>
                                        <a class="btn btn-sm btn-icon rounded-circle btn-label-success" href="{{ $final_session->start_url }}" target="_blank" title="{{ __('cycles.Start Meeting') }}">
                                          <i class="ti ti-brand-zoom"></i>
                                        </a>
                                        <a class="btn btn-sm btn-icon rounded-circle btn-label-warning" href="{{ $final_session->join_url }}" target="_blank" title="{{ __('cycles.Join Meeting') }}">
                                          <i class="ti ti-user"></i>
                                        </a>
                                        @if( !empty( $final_session->zoom_passcode ) )
                                          <button type="button" class="btn btn-sm btn-icon rounded-circle btn-label-secondary show-passcode" data-passcode="{{ $final_session->zoom_passcode }}" title="{{ __('cycles.Show Passcode') }}">
                                            <i class="ti ti-key"></i>
                                          </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-icon rounded-circle btn-label-danger" onclick="deleteme({{ $final_session->id }})" title="{{ __('cycles.Delete Session') }}">
                                          <i class="ti ti-trash"></i>
                                        </button>
                                      @else
                                        <button type="button" class="btn btn-sm btn-icon rounded-circle btn-label-primary" onclick="addzoom({{ $company->id }})" title="{{ __('cycles.Add Zoom Session') }}">
                                          <i class="ti ti-plus"></i>
                                        </button>
                                        @if ($final_session && $final_session->id)
                                          <button type="button" class="btn btn-sm btn-icon rounded-circle btn-label-danger" onclick="deleteme({{ $final_session->id }})" title="{{ __('cycles.Delete Session') }}">
                                            <i class="ti ti-trash"></i>
                                          </button>
                                        @endif
                                      @endif
                                    @endif
                                  </div>
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                      <hr>
                      <div class="d-flex justify-content-end align-items-center m-2">
                        <div class="text-muted small">
                          <span id="selected-count">0</span> {{ __('cycles.selected') }}
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          @endif
        </section>
        @if( $_user->ableTo('screening.evaluation_show', $permissions) )
          @include('admin.settings.screening-evaluation')
        @endif
      </div>
    </div>
    <div class="content-backdrop fade"></div>
  </div>
  <!-- Content wrapper -->

  <!--Screening form Modal -->
  @if (isset($cstep))
    <div class="modal fade" id="stepForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form class="form" action="{{ URL::to('dashboard/cycles/modify') }}" method="POST"
            enctype="multipart/form-data">
            {!! csrf_field() !!}
            <div class="modal-header">
              <h4 class="modal-title">{{ __('cycles.Edit_Step') }}</h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="sid" value="{{ $cstep->id }}">
              <input type="hidden" name="type" value="8">
              <br>
              <div class="col-12 mb-4">
                <label for="step-range-picker" class="form-label">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
                <input type="hidden" name="fromDate" id="mfromDate">
                <input type="hidden" name="toDate" id="mtoDate">
                <input type="text" id="step-range-picker" class="form-control" name="date_range" />
              </div>
              <div class="form-group mb-3">
                <label class="fs-6 mb-1">{{ __('cycles.Title') }}: </label>
                <input value="{{ $cstep->title }}" name='title' type="text" placeholder="Title"
                  class="form-control" required>
              </div>
              {{-- <div class="form-group mb-3">
            <label class="fs-6 mb-1">{{ __('cycles.from') }}: </label>
            <input value="{{ $cstep->from->format('Y-m-d') }}" name='fromDate' type="text" class="form-control dateTime"
              required>
          </div>
          <div class="form-group mb-3">
            <label class="fs-6 mb-1">{{ __('cycles.to') }}: </label>
            <input value="{{ $cstep->to->format('Y-m-d') }}" name='toDate' type="text" class="form-control dateTime"
              required> 
          </div> --}}
              <div class="form-group mb-0">
                <label class="fs-6 mb-1">{{ __('cycles.Description') }}: </label>
                <textarea name='description' placeholder="Description" class="form-control">{{ $cstep->description }}</textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" value="submit" class="btn btn-primary ml-1">
                <span>{{ __('cycles.Submit') }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- create meeting -->
    <div class="modal fade" id="addzoom" tabindex="-1" role="dialog" aria-labelledby="addzoom"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="create-zoom-form" method="post" action="/dashboard/final/addzoom" enctype="multipart/form-data"
            autocomplete="off">
            @csrf
            <div class="modal-header">
              <h4 class="modal-title white">{{ __('cycles.Add new zoom session') }}</h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              {{-- <p class="text-danger">{{ __('cycles.Before submit review') }}.</p> --}}
              <input type="hidden" name="step" value="{{ $cstep->id }}" id="sid">
              <input type="hidden" name="company" value="0" id="cid">
              <div class="form-group mb-3" id="showdates mb-3">
                <label class="fs-6 mb-1">{{ __('cycles.Date/Time') }}:</label>
                <input type="hidden" name="date" id="neededZoomDateTime" />
                <input class='width-200 form-control zoomDateTime' type="text" id="date"
                  placeholder="{{ __('cycles.Date/Time') }}" required>
                <div id="create-zoom-error" class="invalid-feedback" style="display: none;">
                  {{ __('cycles.date_time_required') }}
                </div>
              </div>
              <div class="form-group mb-3">
                <label class="fs-6 mb-1">{{ __('cycles.Meeting period') }}:</label>
                <select name="period" class="select form-control">
                  <option value="30">30 {{ __('cycles.Minutes') }}</option>
                  <option value="60" selected>1 {{ __('cycles.Hour') }}</option>
                  <option value="120">2 {{ __('cycles.Hours') }}</option>
                  <option value="180">3 {{ __('cycles.Hours') }}</option>
                  <option value="240">4 {{ __('cycles.Hours') }}</option>
                  <option value="300">5 {{ __('cycles.Hours') }}</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary ml-1">
                <span>{{ __('cycles.Submit') }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal fade" id="ModifyRequest" tabindex="-1" role="dialog" aria-labelledby="sendrequest"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="edit-zoom-form" method="post" action="/dashboard/final/addzoom" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
              <h4 class="modal-title">{{ __('cycles.Modify Final Screen') }} </h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              {{-- <p class="text-danger">{{ __('cycles.Before submit review') }}.</p> --}}
              <input type="hidden" name="step" value="{{ $cstep->id }}" id="sid">
              <input type="hidden" name="company" value="" id="cid" class="cid">
              <div class="form-group mb-3" id="showdates mb-3">
                <label class="fs-6 mb-1">{{ __('cycles.Date/Time') }}:</label>
                <input type="hidden" name="date" id="editNeededZoomDateTime" />
                <input class='width-200 form-control editZoomDateTime' type="text" id="sdate"
                  placeholder="{{ __('cycles.Date/Time') }}" required />
                <div id="edit-zoom-error" class="invalid-feedback" style="display: none;">
                  {{ __('cycles.date_time_required') }}
                </div>
              </div>
              <div class="form-group mb-3">
                <label class="fs-6 mb-1">{{ __('cycles.Meeting period') }}:</label>
                <select name="period" class="select form-control" id="period">
                  <option value="30">30 {{ __('cycles.Minutes') }}</option>
                  <option value="60">1 {{ __('cycles.Hour') }}</option>
                  <option value="120">2 {{ __('cycles.Hours') }}</option>
                  <option value="180">3 {{ __('cycles.Hours') }}</option>
                  <option value="240">4 {{ __('cycles.Hours') }}</option>
                  <option value="300">5 {{ __('cycles.Hours') }}</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary ml-1">
                <span>{{ __('cycles.Submit') }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- View Details Modal -->
    <div class="modal fade" id="viewDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="viewDetailsModalLabel">{{ __('cycles.Startup Details') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- Startup Information -->
            <div class="mb-3">
              <h6 class="text-primary mb-2">{{ __('cycles.Startup') }}</h6>
              <div class="d-flex align-items-center">
                <img id="detail-company-logo" src="" alt="Company Logo" class="rounded-circle me-2" height="40" width="40">
                <div>
                  <strong id="detail-company-name"></strong>
                </div>
              </div>
            </div>

            <!-- Founder Information -->
            <div class="mb-3">
              <h6 class="text-primary mb-2">{{ __('cycles.Founder') }}</h6>
              <div class="d-flex align-items-center">
                <img id="detail-user-avatar" src="" alt="User Avatar" class="rounded-circle me-2" height="35" width="35">
                <div>
                  <div class="fw-semibold" id="detail-user-name"></div>
                  <small class="text-muted" id="detail-user-title"></small>
                </div>
              </div>
            </div>

            <!-- Judges Information -->
            <div>
              <h6 class="text-primary mb-2">{{ __('cycles.Assigned Judges') }}</h6>
              <div id="detail-judges-list" style="max-height: 200px; overflow-y: auto;">
                <!-- Judges will be populated by JavaScript -->
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary btn-sm" data-bs-dismiss="modal">{{ __('cycles.Close') }}</button>
          </div>
        </div>
      </div>
    </div>
  @endif
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
  <script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
  <script src="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js"></script>
  <script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
  <script src="/app-assets/plugins/alertify/alertify.min.js"></script>
@stop

@section('pagescripts')
  <script>
    // Initialize DataTable
    $(document).ready(function() {
      var table = $('#mentorstable').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('cycles.All') }}"]],
        order: [[1, 'asc']],
        columnDefs: [
          {
            targets: [0], // Checkbox column
            orderable: false,
            searchable: false,
            width: '40px'
          },
          {
            targets: [1], // Company column
            orderable: true,
            searchable: true
          },
          {
            targets: [4], // Actions column
            orderable: false,
            searchable: false
          }
        ],
        language: {
          search: "{{ __('cycles.Search') }}:",
          lengthMenu: "{{ __('cycles.Show') }} _MENU_ {{ __('cycles.entries') }}",
          info: "{{ __('cycles.Showing') }} _START_ {{ __('cycles.to') }} _END_ {{ __('cycles.of') }} _TOTAL_ {{ __('cycles.entries') }}",
          infoEmpty: "{{ __('cycles.Showing') }} 0 {{ __('cycles.to') }} 0 {{ __('cycles.of') }} 0 {{ __('cycles.entries') }}",
          infoFiltered: "({{ __('cycles.filtered from') }} _MAX_ {{ __('cycles.total entries') }})",
          paginate: {
            first: "{{ __('cycles.First') }}",
            last: "{{ __('cycles.Last') }}",
            next: "{{ __('cycles.Next') }}",
            previous: "{{ __('cycles.Previous') }}"
          },
          emptyTable: "{{ __('cycles.No data available in table') }}",
          zeroRecords: "{{ __('cycles.No matching records found') }}"
        },
        dom: '<"d-flex justify-content-between align-items-center flex-wrap mb-3"<"d-flex align-items-center"l><"d-flex align-items-center"f>><"datatable-filters mb-3"><"table-responsive"t><"d-flex justify-content-between align-items-center flex-wrap mt-3"<"d-flex align-items-center"i><"d-flex align-items-center"p>>',
        drawCallback: function(settings) {
          // Re-initialize tooltips after table redraw
          $('[data-bs-toggle="tooltip"]').tooltip();
          
          // Re-initialize dropdown menus after table redraw
          $('[data-bs-toggle="dropdown"]').dropdown();
          
          // Update select all checkbox functionality
          updateSelectAllCheckbox();
        },
        stateSave: true, // Save table state (pagination, search, etc.)
        stateDuration: 60 * 60 * 24 // Save state for 24 hours
      });

      // Create custom filter row
      var judgesData = [
        @foreach ($judges as $judge)
          {
            value: "{{ $judge->{'first_name_' . $l} }} {{ $judge->{'last_name_' . $l} }}",
            text: "{{ $judge->{'first_name_' . $l} }} {{ $judge->{'last_name_' . $l} }}"
          },
        @endforeach
      ];

      var startupsData = [
        @php
          $startups = App\Company::where('step', $id)->get();
        @endphp
        @foreach ($startups as $startup)
          {
            value: "{{ $startup->{'name_' . $l} }}",
            text: "{{ $startup->{'name_' . $l} }}"
          },
        @endforeach
      ];

      var filterRowHtml = `
        <div class="row g-3 align-items-center">
          <div class="col-auto">
            <label class="form-label mb-0"><strong>{{ __('cycles.Filters') }}:</strong></label>
          </div>
          <div class="col-md-3">
            <select class="form-select form-select-sm" id="dt-judge-filter">
              <option value="">{{ __('cycles.All Judges') }}</option>
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select form-select-sm" id="dt-startup-filter">
              <option value="">{{ __('cycles.All Startups') }}</option>
            </select>
          </div>
          <div class="col-md-2">
            <input type="text" class="form-control form-control-sm" id="dt-user-filter" placeholder="{{ __('cycles.Filter Users') }}">
          </div>
          <div class="col-auto" id="clear-filters-container" style="display: none;">
            <button type="button" class="btn btn-sm btn-outline-secondary dt-clear-filters" id="clear-filters-btn">
              <i class="ti ti-filter-off me-1"></i>{{ __('cycles.Clear') }}
            </button>
          </div>
        </div>
      `;
      
      // Insert filter row into DataTable
      $('.datatable-filters').html(filterRowHtml);

      // Populate judges dropdown
      judgesData.forEach(function(judge) {
        $('#dt-judge-filter').append(`<option value="${judge.value}">${judge.text}</option>`);
      });

      // Populate startups dropdown
      startupsData.forEach(function(startup) {
        $('#dt-startup-filter').append(`<option value="${startup.value}">${startup.text}</option>`);
      });

      // Initial check of filter status
      checkFilterStatus();

      // Function to check if any filters are active
      function checkFilterStatus() {
        // Wait a moment for DOM to be ready
        setTimeout(function() {
          var judgeFilter = $('#dt-judge-filter').val() || '';
          var startupFilter = $('#dt-startup-filter').val() || '';
          var userFilter = $('#dt-user-filter').val() || '';
          var globalSearch = table.search() || '';
          
          var hasActiveFilters = judgeFilter.trim() !== '' || 
                                startupFilter.trim() !== '' || 
                                userFilter.trim() !== '' || 
                                globalSearch.trim() !== '';
          
          if (hasActiveFilters) {
            $('#clear-filters-container').show();
          } else {
            $('#clear-filters-container').hide();
          }
        }, 100);
      }

      // Monitor DataTable's global search
      table.on('search.dt', function() {
        checkFilterStatus();
      });

      // DataTable filter row event handlers
      $('#dt-judge-filter').on('change', function() {
        var selectedJudge = $(this).val();
        table.column(3).search(selectedJudge).draw(); // Updated column index
        checkFilterStatus();
      });

      $('#dt-startup-filter').on('change', function() {
        var selectedStartup = $(this).val();
        table.column(1).search(selectedStartup).draw(); // Updated column index
        checkFilterStatus();
      });

      $('#dt-user-filter').on('keyup change', function() {
        var userSearch = $(this).val();
        table.column(2).search(userSearch).draw(); // Updated column index
        checkFilterStatus();
      });

      // Clear filters functionality for DataTable filter row
      $(document).on('click', '.dt-clear-filters', function() {
        $('#dt-judge-filter').val('');
        $('#dt-startup-filter').val('');
        $('#dt-user-filter').val('');
        table.search('').columns().search('').draw();
        checkFilterStatus(); // Hide the button after clearing
      });

      // Function to update select all checkbox
      function updateSelectAllCheckbox() {
        var totalCheckboxes = $('input[name="users[]"]:visible').length;
        var checkedCheckboxes = $('input[name="users[]"]:visible:checked').length;
        
        // Update selected count
        $('#selected-count').text(checkedCheckboxes);
        $('#assignment-selected-count').text(checkedCheckboxes);
        
        // Show/hide judges assignment section based on selection
        if (checkedCheckboxes > 0) {
          $('#judges-assignment-section').removeClass('d-none');
        } else {
          $('#judges-assignment-section').addClass('d-none');
        }
        
        if (totalCheckboxes === 0) {
          $('#checkAll').prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
          $('#checkAll').prop('indeterminate', false).prop('checked', true);
        } else if (checkedCheckboxes > 0) {
          $('#checkAll').prop('indeterminate', true);
        } else {
          $('#checkAll').prop('indeterminate', false).prop('checked', false);
        }
      }

      // Select all checkbox functionality
      $('#checkAll').on('click', function() {
        var isChecked = $(this).prop('checked');
        $('input[name="users[]"]:visible').prop('checked', isChecked);
        updateSelectAllCheckbox();
      });

      // Individual checkbox change
      $(document).on('change', 'input[name="users[]"]', function() {
        updateSelectAllCheckbox();
      });

      // Initial update of select all checkbox
      updateSelectAllCheckbox();
      
      // Initial check of filter status
      checkFilterStatus();
    });

    $(document).on('click', '.show-passcode', function(e) {
      e.preventDefault();
      let passcode = $(this).data('passcode');
      alertify.defaults.transition = 'zoom';
      alertify.defaults.theme.ok = 'btn btn-sm btn-danger';
      alertify.defaults.theme.cancel = 'btn btn-sm btn-info';
      alertify.defaults.theme.input = 'form-control';

      alertify.alert('Meeting Passcode', `The passcode for this meeting is: <strong>${passcode}</strong>`, function() {});
    });
    // Date & TIme
    const dateTimePickr = $('.dateTime');
    if (dateTimePickr.length) {
      dateTimePickr.flatpickr({
        enableTime: true,
        minDate: "today"
      });
    };

    $('#select-judges').select2();

    $('#select-judges').on('change', function() {
      $('#select-judges').removeClass('is-invalid');
      $('#judges-error').hide();
    });

    $('#judges-form').submit(function(e) {
      const judgesValues = $('#select-judges').select2('val');
      const startupsSelected = $('input[name="users[]"]:checked').length;

      if (judgesValues.length === 0) {
        e.preventDefault();
        $('#select-judges').addClass('is-invalid');
        $('#judges-error').show();
      } else if (startupsSelected === 0) {
        e.preventDefault();
        alert('{{ __('cycles.startup_is_required') }}');
      } else {
        $('#judges-form').submit();
      }
    });

    // Initialize zoom date time picker first
    const zoomDateTime = $('.zoomDateTime');
    if (zoomDateTime.length) {
      zoomDateTime.flatpickr({
        enableTime: true,
        minDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
          $('#neededZoomDateTime').val(dateStr);
          // Clear any validation errors when date is selected
          zoomDateTime.removeClass('is-invalid');
          $('#create-zoom-error').hide();
        }
      });
    }

    $('#create-zoom-form').submit(function(e) {
      var neededZoomDateTimeValue = $('#neededZoomDateTime').val();

      if (!neededZoomDateTimeValue || neededZoomDateTimeValue.trim() === '') {
        e.preventDefault();
        zoomDateTime.addClass('is-invalid');
        zoomDateTime.focus();
        $('#create-zoom-error').show();
        return false;
      }
      
      // If validation passes, allow form to submit
      return true;
    });

    $('#edit-zoom-form').submit(function(e) {
      editNeededZoomDateTimeValue = $('#editNeededZoomDateTime').val();

      if (!editNeededZoomDateTimeValue) {
        e.preventDefault();
        editZoomDateTime.addClass('is-invalid');
        editZoomDateTime.focus();
        $('#edit-zoom-error').show();
      }
    })

    const editZoomDateTime = $('.editZoomDateTime');
    if (editZoomDateTime.length) {
      editZoomDateTime.flatpickr({
        enableTime: true,
        minDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
          $('#editNeededZoomDateTime').val(dateStr);
        }
      });
    }

    const headers = {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    };

    function edit(id) {
      $.ajaxSetup({
        headers
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/finalScreening/session",
        data: {
          "id": id,
        },
        success: function(response) {
          $('#sid').val(id);
          $('#sdate').val(response.zoom_date);
          $('#period').val(response.period);
          // $('#ModifyRequest').modal('show');
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }
  @if($cstep)
  const startDate = '{{ $cstep->from->format('Y-m-d') }}'
  const endDate = '{{ $cstep->to->format('Y-m-d') }}'
  
  const stepStartDate = startDate ? moment(startDate) : moment();
  const stepEndDate = endDate ? moment(endDate) : moment();
  @endif

    function DoChange(set) {
      switch (set) {
        case 1:
          $('#stepForm').modal('show');

          $('#step-range-picker').daterangepicker({
            opens: isRtl ? 'left' : 'right',
            // drops: 'up',
            startDate: stepStartDate,
            endDate: stepEndDate,
          }).on('apply.daterangepicker', function(ev, picker) {
            $('#mfromDate').val(picker.startDate.format('YYYY-MM-DD'));
            $('#mtoDate').val(picker.endDate.format('YYYY-MM-DD'));
          });

          break;
        case 2:
          $.ajaxSetup({
            headers
          });
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
              $.ajax({
                type: "POST",
                url: "/dashboard/cycles/modify",
                data: {
                  "stepid": {{ $cstep->id ?? 0 }},
                  "type": 6,
                },
                success: function(response) {
                  // console.log(response);
                  location.href = '/dashboard/final';
                },
                error: function(error) {
                  console.log(error);
                  alert("Error Occurred, Try again!");
                }
              });
            }
          });
          break;
      }
    }

    $('#checkAll').click(function() {
      // This is now handled by DataTable initialization above
      var isChecked = $(this).prop('checked');
      $('input[name="users[]"]:visible').prop('checked', isChecked);
    });

    function addzoom(company) {
      // Clear previous form data
      $('#create-zoom-form')[0].reset();
      $('#neededZoomDateTime').val('');
      $('.zoomDateTime').removeClass('is-invalid');
      $('#create-zoom-error').hide();
      
      // Set company ID
      $('#cid').val(company);
      
      // Show modal
      $('#addzoom').modal('show');
    }

    // Ensure date picker works when modal is shown
    $('#addzoom').on('shown.bs.modal', function() {
      // Re-initialize or refresh the date picker if needed
      const modalZoomDateTime = $('#addzoom .zoomDateTime');
      if (modalZoomDateTime.length && !modalZoomDateTime.hasClass('flatpickr-input')) {
        modalZoomDateTime.flatpickr({
          enableTime: true,
          minDate: "today",
          onChange: function(selectedDates, dateStr, instance) {
            $('#neededZoomDateTime').val(dateStr);
            modalZoomDateTime.removeClass('is-invalid');
            $('#create-zoom-error').hide();
          }
        });
      }
    });

    function edit(company, date_zoom = '', Period = '')
    {
      // alert(company);
      $('.cid').val(company);
      $('#ModifyRequest').modal('show');
      $('#sdate').val(date_zoom);
      $('#editNeededZoomDateTime').val(date_zoom);
      $('#period').val(Period);
    }

    // Re-initialize dropdown functionality after DataTable draws
    $(document).on('click', '[data-bs-toggle="dropdown"]', function(e) {
      // Bootstrap dropdown will handle this automatically
    });

    function deleteme(id) {
      let url = '/dashboard/final/delsession/' + id;
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
      })
        .then((result) => {
          if (result.value) {
            $.post(url, {
                '_method': 'POST',
                '_token': token
              },
              function(response) {
                Swal.fire({
                  icon: 'success',
                  title: "{{ __('admin.Deleted') }}!",
                  html: "{{ __('admin.DeletedSuccessfully') }}.",
                  showCancelButton: false,
                  showConfirmButton: true,
                  confirmButtonText: "{{ __('admin.OK') }}",
                  customClass: {
                    confirmButton: 'btn btn-primary',
                  },
                  buttonsStyling: false,
                });
                window.location.href = '/dashboard/final';
              }).fail(function(error) {
              console.log(error);
            });
          } else {
            console.log('Canceled')
          }
        });
    }

    function viewDetails(element, companyId) {
      // Get the table row
      var row = $(element).closest('tr');
      
      // Extract company data from the current row
      var companyLogo = row.find('td:eq(1) img').attr('src') || '/images/logo-placeholder.png';
      var companyName = row.find('td:eq(1) a').text().trim() || 'N/A';
      
      // Extract user data from the current row
      var userAvatar = row.find('td:eq(2) img').attr('src') || '/icons/account.svg';
      var userName = row.find('td:eq(2) .media-heading').text().trim() || 'N/A';
      var userTitle = row.find('td:eq(2) .font-small-2').text().trim() || 'N/A';
      
      // Extract judges data from the current row
      var judgesHtml = '';
      var judgesContainer = row.find('td:eq(3)');
      
      if (judgesContainer.find('.text-muted').length > 0) {
        judgesHtml = '<p class="text-muted">{{ __('cycles.No judges assigned yet') }}</p>';
      } else {
        // Try to get all judges data from data attribute
        var allJudgesData = judgesContainer.find('[data-all-judges]').attr('data-all-judges');
        
        if (allJudgesData) {
          try {
            var allJudges = JSON.parse(allJudgesData);
            if (allJudges && allJudges.length > 0) {
              allJudges.forEach(function(judge) {
                judgesHtml += `
                  <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                    <img src="${judge.avatar || '/icons/account.svg'}" 
                         onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                         alt="${judge.name}" 
                         class="rounded-circle me-2" 
                         height="30" 
                         width="30">
                    <div>
                      <div class="fw-semibold small">${judge.name}</div>
                      <small class="text-muted">Judge</small>
                    </div>
                  </div>
                `;
              });
            } else {
              judgesHtml = '<p class="text-muted small">{{ __('cycles.No judges assigned yet') }}</p>';
            }
          } catch (e) {
            // Fallback to original method if JSON parsing fails
            var judgeElements = judgesContainer.find('.btn-outline-secondary');
            if (judgeElements.length > 0) {
              judgeElements.each(function() {
                var judgeName = $(this).find('span').text().trim();
                var judgeAvatar = $(this).find('img').attr('src') || '/icons/account.svg';
                if (judgeName && !judgeName.startsWith('+')) {
                  judgesHtml += `
                    <div class="d-flex align-items-center mb-2 p-2 bg-label rounded">
                      <img src="${judgeAvatar}" 
                           alt="${judgeName}" 
                           class="rounded-circle me-2" 
                           height="30" 
                           width="30">
                      <div>
                        <div class="fw-semibold small">${judgeName}</div>
                        <small class="text-muted">Judge</small>
                      </div>
                    </div>
                  `;
                }
              });
            }
            
            if (judgesHtml === '') {
              judgesHtml = '<p class="text-muted">{{ __('cycles.No judges assigned yet') }}</p>';
            }
          }
        } else {
          // Fallback to original method if no data attribute found
          var judgeElements = judgesContainer.find('.btn-outline-secondary');
          if (judgeElements.length > 0) {
            judgeElements.each(function() {
              var judgeName = $(this).find('span').text().trim();
              var judgeAvatar = $(this).find('img').attr('src') || '/icons/account.svg';
              if (judgeName && !judgeName.startsWith('+')) {
                judgesHtml += `
                  <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                    <img src="${judgeAvatar}" 
                         alt="${judgeName}" 
                         class="rounded-circle me-2" 
                         height="30" 
                         width="30">
                    <div>
                      <div class="fw-semibold small">${judgeName}</div>
                      <small class="text-muted">Judge</small>
                    </div>
                  </div>
                `;
              }
            });
          }
          
          if (judgesHtml === '') {
            judgesHtml = '<p class="text-muted">{{ __('cycles.No judges assigned yet') }}</p>';
          }
        }
      }
      
      // Populate startup information
      $('#detail-company-logo').attr('src', companyLogo);
      $('#detail-company-name').text(companyName);

      // Populate founder information
      $('#detail-user-avatar').attr('src', userAvatar);
      $('#detail-user-name').text(userName);
      $('#detail-user-title').text(userTitle);

      // Populate judges information
      $('#detail-judges-list').html(judgesHtml);

      // Show the modal
      $('#viewDetailsModal').modal('show');
    }

    // Export table function
    function exportTable(tableId, format) {
      var table = $('#' + tableId).DataTable();
      
      if (format === 'csv') {
        // Export as CSV
        var csv = [];
        var headers = [];
        
        // Get headers (skip checkbox column)
        $('#' + tableId + ' thead th').each(function(index) {
          if (index > 0 && index < 4) { // Skip checkbox and actions columns
            headers.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
          }
        });
        csv.push(headers.join(','));
        
        // Get visible rows data
        table.rows({ filter: 'applied' }).every(function(rowIdx, tableLoop, rowLoop) {
          var data = this.data();
          var row = [];
          
          // Company name
          var companyName = $(data[1]).find('a').text().trim() || $(data[1]).text().trim();
          row.push('"' + companyName.replace(/"/g, '""') + '"');
          
          // User name
          var userName = $(data[2]).find('.media-heading').text().trim() || $(data[2]).text().trim();
          row.push('"' + userName.replace(/"/g, '""') + '"');
          
          // Judges count
          var judgesText = '';
          if ($(data[3]).find('.text-muted').length > 0) {
            judgesText = 'No judges yet';
          } else {
            var judgeElements = $(data[3]).find('.btn-outline-secondary');
            var judgeNames = [];
            judgeElements.each(function() {
              var judgeName = $(this).find('span').text().trim();
              if (judgeName && !judgeName.startsWith('+')) {
                judgeNames.push(judgeName);
              }
            });
            judgesText = judgeNames.join(', ') || 'No judges yet';
          }
          row.push('"' + judgesText.replace(/"/g, '""') + '"');
          
          csv.push(row.join(','));
        });
        
        // Download CSV
        var csvContent = csv.join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        var url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'final_screening_startups.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
      } else if (format === 'excel') {
        // For Excel export, we'll use the same CSV approach but with .xls extension
        // In a real implementation, you might want to use a library like SheetJS
        var csv = [];
        var headers = [];
        
        // Get headers (skip checkbox column)
        $('#' + tableId + ' thead th').each(function(index) {
          if (index > 0 && index < 4) { // Skip checkbox and actions columns
            headers.push($(this).text().trim());
          }
        });
        csv.push(headers.join('\t'));
        
        // Get visible rows data
        table.rows({ filter: 'applied' }).every(function(rowIdx, tableLoop, rowLoop) {
          var data = this.data();
          var row = [];
          
          // Company name
          var companyName = $(data[1]).find('a').text().trim() || $(data[1]).text().trim();
          row.push(companyName);
          
          // User name
          var userName = $(data[2]).find('.media-heading').text().trim() || $(data[2]).text().trim();
          row.push(userName);
          
          // Judges
          var judgesText = '';
          if ($(data[3]).find('.text-muted').length > 0) {
            judgesText = 'No judges yet';
          } else {
            var judgeElements = $(data[3]).find('.btn-outline-secondary');
            var judgeNames = [];
            judgeElements.each(function() {
              var judgeName = $(this).find('span').text().trim();
              if (judgeName && !judgeName.startsWith('+')) {
                judgeNames.push(judgeName);
              }
            });
            judgesText = judgeNames.join(', ') || 'No judges yet';
          }
          row.push(judgesText);
          
          csv.push(row.join('\t'));
        });
        
        // Download Excel
        var csvContent = csv.join('\n');
        var blob = new Blob([csvContent], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        var link = document.createElement('a');
        var url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'final_screening_startups.xls');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    }
  </script>
@stop
