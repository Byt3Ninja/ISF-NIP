@extends('layouts.admin')
@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
  <style>
    .drp-calendar {
      background-color: #fff;
      margin-top: -3rem !important;
    }

    .remove-tag:hover {
      color: #ff3e1d;
    }

    @media screen and (max-width: 768px) {
      #mob {
        margin-top: 10px;
        justify-content: flex-start;
      }
    }
  </style>
@stop

@section('content')
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-body">
        <section id="multiple-column-form">
          @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible" role="alert">
              {{ __('admin.' . session()->get('message')) }}
              <button type="button" class="btn-label-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          @if (session()->has('message2'))
            <div class="alert alert-danger alert-dismissible" role="alert">
              {{ __('admin.' . session()->get('message2')) }}
              <button type="button" class="btn-label-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif
          @if ($companies->count() == 0)
            <input type="hidden" class="cyclesExcel" value="0">
          @else
            <input type="hidden" class="cyclesExcel" value="{{ request()->route('id') }}">
          @endif
        </section>
        
        <!-- Cycle Selection -->
        <section id="cycle-selection">
          <div class="card-header mb-4">
            <h4 class="mb-1">{{ __('cycles.manage_registered_startups') }}</h4>
            <p class="mb-0">{{ __('cycles.manage_startup_description1') }}</p>
            <p class="mb-0">{{ __('cycles.manage_startup_description2') }}</p>
          </div>
          <div class="card mb-6">
            <div class="card-body overflow-x-hidden text-nowrap">
              <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between align-items-lg-center">
                <div class="form-group d-flex flex-column flex-lg-row align-items-lg-center gap-2 mb-6 mb-lg-0 me-lg-4">
                  <label class="form-label fs-6">{{ __('cycles.Select_Cycle') }}:</label>
                  <select id="ccycle" class="form-select select2" style="width: 350px;"
                    onchange="window.open('/dashboard/cycles/'+this.options[this.selectedIndex].value+'/registered','_self');">
                    @foreach ($cycles as $cycle)
                      <option value="{{ $cycle->id }}" {{ $cycle->id == $id ? 'selected' : '' }}>
                        {{ $cycle->title }}
                        ({{ __('cycles.Start') }} {{ $cycle->start->format('d-m-Y') ?? '-' }})
                        -
                        {{ optional($cycle->organization)->name }} </option>
                    @endforeach
                  </select>
                </div>
                <div class="d-flex flex-wrap flex-lg-nowrap align-items-lg-center gap-3">
                  @if ($_user->ableTo('cycles.list', $permissions))
                    <a class="btn btn-label-success" href="/dashboard/cycles/{{ request('id') ?? $cycle->id }}">
                      <i class="ti ti-recycle me-2  "></i> {{ __('admin.manage_steps') }}
                    </a>
                  @endif
                  @if ($_user->ableTo('programlist.edit', $permissions))
                    <a class="btn btn-label-info" href="/dashboard/programlist/edit/{{ request('id') ?? $cycle->id }}">
                      <i class="ti ti-edit text-info me-2 "></i> {{ __('cycles.Edit_Program') }}
                    </a>
                  @endif
                  @if ($_user->ableTo('programlist.create', $permissions))
                    <a class="btn btn-label-primary" href="/dashboard/programlist/create">
                      <i class="ti ti-plus me-2 "></i> {{ __('admin.New Program') }}
                    </a>
                  @endif
                  @if ($_user->ableTo('programlist.destroy', $permissions))
                    <button class="btn btn-label-danger" onclick="DoChange(3)">
                      <i class="ti ti-trash me-2  "></i> {{ __('cycles.Delete') }}
                    </button>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </section>

        <div class="card">
          <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">
                {{ __('cycles.manage_registered_startups') }} {{ __('admin.in') }} 
                <span class="fw-bold ms-1">
                  {{ $cycles->where('id', request('id'))->pluck('title')->implode(', ') }}
                </span>
              </h5>
              <div class="d-flex align-items-center gap-4">
								<div class="dropdown">
									<button class="btn btn-label-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ti ti-file-export me-2"></i>
										<span>{{ __('admin.Export') }}</span>
									</button>
									<ul class="dropdown-menu" aria-labelledby="exportDropdown">
										<li>
                      <a class="dropdown-item" href="javascript:void(0)" id="export-csv">
											  <i class="ti ti-file-text me-2"></i>CSV
										  </a>
                    </li>
										<li>
                      <a class="dropdown-item" href="javascript:void(0)" id="export-excel">
											  <i class="ti ti-file-spreadsheet me-2"></i>Excel
										  </a>
                    </li>
									</ul>
								</div>
                <button class="btn btn-label-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                  <i class="ti ti-filter me-2"></i>{{ __('cycles.Filter') }}
                </button>
              </div>
            </div>
          </div>
            
          <!-- Selected Startups Tags Section -->
          <div class="card shadow-none border-bottom bg-light-subtle">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0 fw-bold">
                  <i class="ti ti-tags me-1"></i> {{ __('cycles.startups_selected') }}: 
                  <span id="selected-count">0</span>
                </h6>
                <button type="button" class="btn btn-sm btn-label-danger d-none" id="clearAllSelections">
                  <i class="ti ti-x me-1"></i>{{ __('cycles.clear_all') }}
                </button>
              </div>
              <div id="selectedStartupsTags" class="d-flex flex-wrap gap-2" style="width: 90%;">
                <span class="text-muted fst-italic" id="no-selection-msg">
                  {{ __('cycles.no_startups_selected') }}.
                </span>
              </div>
            </div>
          </div>

          <div class="card-datatable table-responsive datatable-basic">
            <!-- Action Buttons -->
            <div class="d-flex align-items-center gap-4 my-3 pb-3 border-bottom">
              <div class="media align-items-center col-md-3">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#changeStepModal" id="bulkChangeStepBtn" disabled>
                  <i class="ti ti-pencil me-2"></i>{{ __('cycles.change_step') }}
                </button>
              </div>
            </div>

            <table class="table" id="startupsTable">
              <thead class="table-light">
                <tr>
                  <th>
                    <input type="checkbox" class="form-check-input checkAll">
                  </th>
                  <th>{{ __('cycles.Company') }}</th>
                  <th>{{ __('cycles.User/Title') }}</th>
                  <th>{{ __('cycles.Mobile') }}</th>
                  <th>{{ __('cycles.E-Mail') }}</th>
                  <th>{{ __('cycles.Current Step') }}</th>
                  <th>{{ __('cycles.Actions') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($companies as $company)
                  <tr>
                    <td>
                      <div class="form-check d-flex align-items-center gap-1">
                        <input type="checkbox" class="form-check-input chboxcyclesReg" 
                          data-company-id="{{ $company->id }}"
                          data-user-id="{{ $company->user->id ?? '' }}"
                          data-company-name="{{ $company->{'name_' . $l} }}"
                          value="1" 
                          name="chboxcyclesReg[]">
                      </div>
                    </td>
                    <td style="width: 150px">
                      <a 
                        class="d-flex align-items-center gap-2"
                        href="/dashboard/companyProfile/{{ $company->id }}"
                      >
                        <img 
                          src="{{ $company->logo ?? '/app-assets/images/logo_placeholder.png' }}"
                          onerror="this.src='/app-assets/images/logo_placeholder.png'"
                          alt="avatar" height="40" width="40"
                          class="rounded-circle border"
                        >
                        <small class="fw-semibold text-dark">{{ $company->{'name_' . $l} }}</small>
                      </a>
                    </td>
                    <td style="width: 150px">
                      <a class="d-flex align-items-center gap-2" href="/dashboard/users/{{ $company->user->id ?? '0'  }}/edit">
                        <img 
                          src="/191014/{{ $company->user->img_url ?? '/icons/account.svg' }}"
                          onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                          alt="avatar" 
                          class="rounded-circle border" 
                          height="40" 
                          width="40"
                        >
                        <div class="d-flex flex-column">
                          <small class="fw-semibold text-dark">
                            {{ $company->user->{'first_name_' . $l} ?? '' }}
                            {{ $company->user->{'last_name_' . $l} ?? '' }}
                          </small>
                          <small class="text-muted">
                            {{ $company->user->{'title_' . $l} ?? '' }}
                          </small>
                        </div>
                      </a>
                    </td>
                    <td>
                      <small>{{ $company->user->phone ?? ''  }}</small>
                    </td>
                    <td>
                      <small>{{ $company->user->email ?? ''  }}</small>
                    </td>
                    <td>
                      <small>
                        {{ $company->currentStepTitle ?? '' }}
                      </small>
                    </td>
                    <td>
                      <div class="d-flex flex-nowrap align-items-center gap-2">
                        <!-- Change Step Button -->
                        <button type="button" class="btn btn-sm btn-light text-nowrap change-step-single"
                          data-company-id="{{ $company->id }}"
                          data-company-name="{{ $company->{'name_' . $l} }}"
                          data-user-name="{{ $company->user->{'first_name_' . $l} ?? '' }} {{ $company->user->{'last_name_' . $l} ?? '' }}"
                          data-current-step="{{ $company->step }}"
                          data-current-step-title="{{ $company->currentStepTitle }}">
                          <i class="ti ti-pencil ti-16px me-1"></i> {{ __('cycles.change_step') }}
                        </button>

                        <!-- Mark Alumni Button -->
                        <button type="button" class="btn btn-sm {{ $company->isAlumni ? 'btn-secondary' : 'btn-primary' }} text-nowrap mark-alumni-btn"
                          data-company-id="{{ $company->id }}"
                          data-company-name="{{ $company->{'name_' . $l} }}"
                          {{ $company->isAlumni ? 'disabled' : '' }}>
                          <i class="ti ti-pennant ti-16px me-1"></i>
                          <span>{{ $company->isAlumni ? __('startup.already_alumni') : __('startup.mark_alumni') }}</span>
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
    </div>
  </div>

  <!-- Change Step Modal -->
  <div class="modal fade" id="changeStepModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('cycles.change_step') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-6">
            <label class="form-label">{{ __('cycles.Select Step') }}</label>
            <select class="form-select" id="modalStepSelect">
              <option value="" selected disabled>{{ __('cycles.Select Step') }}</option>
              <option value="0">{{ __('cycles.Disapprove') }}</option>
              @foreach ($steps as $step)
                <option value="{{ $step->id }}">{{ $step->title }}</option>
              @endforeach
            </select>
          </div>
          <div id="singleCompanyInfo" class="alert alert-light" style="display: none;">
            <div class="d-flex align-items-center mb-3">
              <i class="ti ti-building-skyscraper me-2"></i>
              <div class="fw-semibold">{{ __('cycles.Company') }}:</div>
              <span id="singleCompanyName" class="text-secondary ms-2"></span>
            </div>
            <div class="d-flex align-items-center mb-3">
              <i class="ti ti-user me-2"></i>
              <div class="fw-semibold">{{ __('startup.startup_leader') }}:</div>
              <span id="singleCompanyUser" class="text-secondary ms-2"></span>
            </div>
            <div class="d-flex align-items-center">
              <i class="ti ti-route me-2"></i>
              <div class="fw-semibold">{{ __('cycles.Current Step') }}:</div>
              <span id="singleCompanyCurrentStep" class="text-secondary ms-2"></span>
            </div>
          </div>
          <div id="affectedCompaniesInfo" class="alert alert-info">
            <small><strong id="selectedCount">0</strong> {{ __('cycles.startups_will_be_affected') }}</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
            {{ __('admin.Cancel') }}
          </button>
          <button type="button" class="btn btn-primary" id="confirmStepChange">
            {{ __('admin.Confirm') }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Step Modal -->
  <div class="modal fade" id="cycleForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <form class="form" action= "{{ URL::to('dashboard/cycles/modify') }}" method="POST"
          enctype="multipart/form-data" autocomplete="off">
          {!! csrf_field() !!}
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel33">{{ __('cycles.Cycle data') }}
            </h4>
          </div>
          <div class="modal-body">
            <div class="form-group mb-4">
              <label class="form-label">{{ __('cycles.Cycle title') }}: </label>
              <input type="hidden" name="type" id="type">
              <input type="hidden" name="cycleid" id="cycleid">
              <input id='title' name='title' type="text" placeholder="{{ __('cycles.Title') }}"
                class="form-control" required>
            </div>
            <div class="col-12 mb-4">
              <label for="bs-rangepicker-basic" class="form-label">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
              <input type="text" id="bs-rangepicker-basic" class="form-control" name="date_range" />
            </div>
            {{-- <div class="form-group mb-4">
              <label class="form-label">{{ __('cycles.Valid from') }}: </label>
              <input id='start' name='start' type="text" placeholder="{{ now()->format('Y-m-d') }}"
                class="form-control date" required>
            </div>
            <div class="form-group mb-4">
              <label class="form-label">{{ __('cycles.Valid to') }}: </label>
              <input id='end' name='end' type="text"
                placeholder="{{ now()->addDays(15)->format('Y-m-d') }}" class="form-control date" required>
            </div> --}}
            <div class="form-group mb-4">
              <label class="form-label">{{ __('cycles.Description') }}: </label>
              <textarea id='description' name='description' placeholder="{{ __('cycles.Description') }}" class="form-control"></textarea>
            </div>
            <div class="form-group mb-4 custom-switch">
              <span>{{ __('settings.Private') }}</span>&nbsp;
              <label class="form-label" for="customSwitch3"></label>
              <input name="private" type="checkbox" class="custom-control-input" id="customSwitch3">
              <a id="cyclelink" target="_blank"></a>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-label-secondary" data-bs-dismiss="modal">
              <i class="ti ti-x d-block d-sm-none"></i>
              <span class="d-none d-sm-block">{{ __('cycles.Close') }}</span>
            </button>
            <button type="submit" value="submit" class="btn btn-sm btn-primary ml-1">
              <i class="ti ti-check d-block d-sm-none"></i>
              <span class="d-none d-sm-block">{{ __('cycles.Submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Filter Modal -->
  <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content bg-label-secondary p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="modal-title fw-semibold mb-0 mt-2" id="filterModalLabel">
            <i class="ti ti-filter ti-24px me-2"></i> {{ __('cycles.Filter_Startups') }}
          </h4>
          <button type="button" class="btn btn-xs btn-icon btn-outline-primary rounded" data-bs-dismiss="modal" aria-label="Close">
            <i class="ti ti-x ti-16px"></i>
          </button>
        </div>
        <form class="bg-label-secondary" method="GET" action="{{ route('cycles.users_manage', $id) }}">
          <div class="card">
            <div class="card-body">
              <div class="form-group mb-4">
                <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Select_Step') }}</label>
                <select name="step_id" class="form-select select2">
                  <option value="0">{{ __('cycles.All') }}</option>
                  @foreach ($steps as $step)
                    <option value="{{ $step->id }}" {{ $step->id == request('step_id') ? 'selected' : '' }}>
                      {{ $step->title }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group mb-4">
                <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Select_Country') }}</label>
                <select name="country_id" class="form-select select2">
                  <option value="0">{{ __('cycles.All') }}</option>
                  @foreach ($countries as $country)
                    <option value="{{ $country->id }}"
                      {{ $country->id == request('country_id') ? 'selected' : '' }}>{{ $country->{'name_' . $l} }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group mb-4">
                <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Select_Sector') }}</label>
                <select name="sector_id" class="form-select select2">
                  <option value="0">{{ __('cycles.All') }}</option>
                  @foreach ($sectors as $sector)
                    <option value="{{ $sector->id }}"
                      {{ $sector->id == request('sector_id') ? 'selected' : '' }}>{{ $sector->{'name_' . $l} }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group mb-4">
                <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Select_Stage') }}</label>
                <select name="Dstage_id" class="form-select select2">
                  <option value="0">{{ __('cycles.All') }}</option>
                  @foreach ($stages as $stage)
                    <option value="{{ $stage->id }}"
                      {{ $stage->id == request('Dstage_id') ? 'selected' : '' }}>{{ $stage->{'name_' . $l} }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group mb-4">
                <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Select_Funding_Stage') }}</label>
                <select class="form-select select2" name="funding_stage" data-placeholder="{{ __('cycles.Select_Funding_Stage') }}">
                  <option value="">{{ __('cycles.All') }}</option>
                  <option value="Pre_seed_funding_stage"
                    {{ request('funding_stage') == 'Pre_seed_funding_stage' ? 'selected' : '' }}>
                    {{ __('admin.Pre_seed_funding_stage') }}
                  </option>
                  <option value="Seed_funding_stage "
                    {{ request('funding_stage') == 'Seed_funding_stage' ? 'selected' : '' }}>
                    {{ __('admin.Seed_funding_stage') }}
                  </option>
                  <option value="Series_A_funding"
                    {{ request('funding_stage') == 'Series_A_funding' ? 'selected' : '' }}>
                    {{ __('admin.Series_A_funding') }}
                  </option>
                  <option value="Series_B_funding"
                    {{ request('funding_stage') == 'Series_B_funding' ? 'selected' : '' }}>
                    {{ __('admin.Series_B_funding') }}
                  </option>
                  <option value="Series_C_funding"
                    {{ request('funding_stage') == 'Series_C_funding' ? 'selected' : '' }}>
                    {{ __('admin.Series_C_funding') }}
                  </option>
                </select>
              </div>
              <div class="form-group mb-4">
                <label for="targeted_market" class="form-label fs-6 fw-semibold mb-1">{{ __('admin.Targeted_Market') }}</label>
                <select
                  class="form-select select2"
                  id="targeted_market"
                  name="targeted_market[]"
                  multiple
                  autocomplete="targeted_market"
                >
                  @foreach ($countries as $country)
                    <option value="{{ $country->id }}"
                      {{ $country->id == request('country_id') ? 'selected' : '' }}>
                      {{$country->emoji . ' ' . $country->{'name_'.$l} }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group mb-4">
                <label class="form-label fs-6 fw-semibold mb-1">{{ __('cycles.Business_Type') }}</label>
                <select class="form-select select2" name="BType">
                  <option value="0">{{ __('admin.All') }}</option>
                  <option value="B2C" {{ request('BType') == 'B2C' ? 'selected' : '' }}>
                    B2C</option>
                  <option value="B2B" {{ request('BType') == 'B2B' ? 'selected' : '' }}>
                    B2B</option>
                  <option value="B2B2C" {{ request('BType') == 'B2B2C' ? 'selected' : '' }}>
                    B2B2C</option>
                  <option value="C2C" {{ request('BType') == 'C2C' ? 'selected' : '' }}>
                    C2C</option>
                  <option value="B2G" {{ request('BType') == 'B2G' ? 'selected' : '' }}>
                    B2G</option>
                  <option value="Platform" {{ request('BType') == 'Platform' ? 'selected' : '' }}>
                    Platform</option>
                </select>
              </div>
            </div>
          </div>
           <!-- Footer -->
           <div class="d-flex align-items-center justify-content-end gap-3 mt-4">
            <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal" 
            onclick="window.location.href='/dashboard/cycles/{{ $id }}/registered'">
              {{ __('cycles.Reset') }}
              <i class="ti ti-circle-x ms-2"></i>
            </button>
            <button type="submit" class="btn btn-primary">
              {{ __('cycles.Apply') }}
              <i class="ti ti-circle-check ms-2"></i>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
@stop

@section('pagescripts')
  <script src="/app-assets/js/1.js"></script>
  <script>
    var bsRangePickerBasic = $('#bs-rangepicker-basic');
    if (bsRangePickerBasic.length) {
      bsRangePickerBasic.daterangepicker({
        opens: '{{ $l }}' == 'ar' ? 'left' : 'right',
      });
    }
  </script>

  <script>
    $(document).ready(function () {

      // Export functionality
      $('#export-csv').on('click', function(e) {
        e.preventDefault();
        table.button('.buttons-csv').trigger();
      });

      $('#export-excel').on('click', function(e) {
        e.preventDefault();
        table.button('.buttons-excel').trigger();
      });

      // Initialize DataTable with export buttons
      var table = $('#startupsTable').DataTable({
        "responsive": true,
        "processing": true,
        "serverSide": false,
        "pageLength": 10,
        "language": {
          "url": '{{ $l }}' == 'ar'
            ? "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
            : "https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json",
        },
        "dom": '<"row"<"col-12 col-md-9 d-flex flex-wrap flex-lg-nowrap align-items-center"l><"col-12 col-md-3 d-flex justify-content-center justify-content-lg-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6 d-flex justify-content-end"p>>',
        "buttons": [
          {
            extend: 'csv',
            text: '<i class="ti ti-file-text me-1"></i>CSV',
            className: 'btn btn-label-secondary buttons-csv d-none',
            title: `Registered_Startups_Export_${new Date().toISOString().slice(0, 10)}`,
            exportOptions: { 
              columns: [0, 1, 2, 3, 4, 5, 6]
            }
          },
          {
            extend: 'excel',
            text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
            className: 'btn btn-label-secondary buttons-excel d-none',
            title: `Registered_Startups_Export_${new Date().toISOString().slice(0, 10)}`,
            exportOptions: { 
              columns: [1, 2, 3, 4, 5]
            }
          }
        ],
      });

      // Track selected startups
      var selectedStartups = {};
      var currentModalContext = 'bulk'; // 'bulk' or 'single'
      var singleCompanyData = null;

      // Update selected tags display
      function updateSelectedTags() {
        var tagsContainer = $('#selectedStartupsTags');
        var count = Object.keys(selectedStartups).length || 0;

        $('#selected-count').text(count);

        if (count > 0) {
          $('#clearAllSelections').removeClass('d-none');

          tagsContainer.empty();
          
          $.each(selectedStartups, function(id, data) {
            var tag = $('<span class="badge bg-label-primary d-flex align-items-center gap-1">' +
              '<span>' + data.name + '</span>' +
              '<i class="ti ti-x cursor-pointer remove-tag" data-company-id="' + id + '" style="font-size: 14px;"></i>' +
              '</span>');
            tagsContainer.append(tag);
          });

          $('#bulkChangeStepBtn').prop('disabled', false);
        } else {
          $('#clearAllSelections').addClass('d-none');
          
          tagsContainer.empty();
          tagsContainer.append($('<span class="text-muted fst-italic">' + "{{ __('cycles.no_startups_selected') }}." + '</span>'));
          $('#bulkChangeStepBtn').prop('disabled', true);
        }

        $('#selectedCount').text(count);
      }

      // Check all checkbox
      $('.checkAll').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.chboxcyclesReg').prop('checked', isChecked).trigger('change');
      });

      // Individual checkbox change
      $('.chboxcyclesReg').on('change', function() {
        var companyId = $(this).data('company-id');
        var companyName = $(this).data('company-name');
        var userId = $(this).data('user-id');

        if ($(this).prop('checked')) {
          selectedStartups[companyId] = {
            name: companyName,
            userId: userId
          };
        } else {
          delete selectedStartups[companyId];
          $('.checkAll').prop('checked', false);
        }

        updateSelectedTags();
      });

      // Remove tag
      $(document).on('click', '.remove-tag', function() {
        var companyId = $(this).data('company-id');
        delete selectedStartups[companyId];
        $('.chboxcyclesReg[data-company-id="' + companyId + '"]').prop('checked', false);
        $('.checkAll').prop('checked', false);
        updateSelectedTags();
      });

      // Clear all selections
      $('#clearAllSelections').on('click', function() {
        selectedStartups = {};
        $('.chboxcyclesReg, .checkAll').prop('checked', false);
        updateSelectedTags();
      });

      // Open modal for bulk change
      $('#bulkChangeStepBtn').on('click', function() {
        currentModalContext = 'bulk';
        singleCompanyData = null;
        $('#modalStepSelect').val('');
        $('#singleCompanyInfo').hide();
        $('#affectedCompaniesInfo').show();
        updateSelectedTags();
      });

      // Open modal for single company
      $(document).on('click', '.change-step-single', function() {
        currentModalContext = 'single';
        singleCompanyData = {
          id: $(this).data('company-id'),
          name: $(this).data('company-name'),
          userName: $(this).data('user-name'),
          currentStep: $(this).data('current-step'),
          currentStepTitle: $(this).data('current-step-title')
        };

        $('#modalStepSelect').val(singleCompanyData.currentStep);
        $('#selectedCount').text('1');

        // Populate single company info
        $('#singleCompanyName').text(singleCompanyData.name);
        $('#singleCompanyUser').text(singleCompanyData.userName);
        $('#singleCompanyCurrentStep').text(singleCompanyData.currentStepTitle);

        // Show single info, hide bulk info
        $('#singleCompanyInfo').show();
        $('#affectedCompaniesInfo').hide();

        $('#changeStepModal').modal('show');
      });

      // Confirm step change
      $('#confirmStepChange').on('click', function() {
        var selectedStep = $('#modalStepSelect').val();

        if (!selectedStep) {
          alert('{{ __("cycles.Please select a step") }}');
          return;
        }

        var companiesToUpdate = [];

        if (currentModalContext === 'bulk') {
          $.each(selectedStartups, function(id, data) {
            companiesToUpdate.push(id);
          });
        } else if (currentModalContext === 'single' && singleCompanyData) {
          companiesToUpdate.push(singleCompanyData.id);
        }

        if (companiesToUpdate.length === 0) {
          alert('{{ __("cycles.No companies selected") }}');
          return;
        }

        changestep(companiesToUpdate, selectedStep);
      });

      // Mark Alumni button
      $(document).on('click', '.mark-alumni-btn', function() {
        var companyId = $(this).data('company-id');
        var companyName = $(this).data('company-name');

        var confirmationMessage = "{{ __('startup.mark_alumni_confirmation') }}";
        var confirmButtonText = "{{ __('admin.Confirm') }}";
        var cancelButtonText = "{{ __('admin.Cancel') }}";

        Swal.fire({
          title: '{{ __('startup.mark_alumni') }}',
          text: confirmationMessage,
          icon: 'warning',
          showCancelButton: false,
          customClass: {
            confirmButton: 'btn btn-primary ms-3',
            cancelButton: 'btn btn-label-secondary'
          },
          confirmButtonText: confirmButtonText,
          cancelButtonText: cancelButtonText,
          reverseButtons: true,
        }).then((result) => {
          if (result.isConfirmed) {
            // Submit via AJAX
            $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
            });

            $.ajax({
              type: "POST",
              url: "/dashboard/AddCompanyalumni/" + companyId,
              success: function(response) {
                releaseSuccessAlert({
                  title: "{{ __('admin.success') }}",
                  message: "{{ __('startup.mark_alumni_success') }}",
                });
                // Disable button
                $('.mark-alumni-btn[data-company-id="' + companyId + '"]').prop('disabled', true);
                $('.mark-alumni-btn[data-company-id="' + companyId + '"]').removeClass("btn-primary").find('span').text('{{ __('startup.already_alumni') }}');
                $('.mark-alumni-btn[data-company-id="' + companyId + '"]').addClass("btn-secondary");
              },
              error: function(xhr, status, error) {
                var errorMessage = "{{ __('startup.mark_alumni_error') }}";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                  errorMessage += " ({{ __('startup.error') }}: " + xhr.responseJSON.message + ")";
                }
                releaseFailureAlert({
                  title: "{{ __('admin.Error') }}",
                  message: errorMessage ? errorMessage : "{{ __('admin.unexpected_error') }}",
                });
              }
            });
          }
        });
      });
    });
  </script>

  <script>
    function DoChange(set) {
      var cycle = $('#ccycle').find(":selected").val();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      switch (set) {
        case 1:
          $.ajax({
            type: "POST",
            url: "/dashboard/cycles/modify",
            data: {
              "cycleid": cycle,
            },
            success: function(response) {
              $('#title').val(response.title);
              $('#description').val(response.description);
              $('#cycleid').val(cycle);
              $('#type').val(1);
              $('#start').val(response.start);
              $('#end').val(response.end);
              if (response.private) {
                $('#customSwitch3').attr('checked', 'checked');
                $('#cyclelink').html('{{ __('cycles.Join URL') }}');
                $('#cyclelink').attr('href', '/dashboard/cycles/view/' + response.id + '?id=' + response.private);
              }
              $('#cycleForm').modal('show');
            },
            error: function(error) {
              console.log(error);
              alert("Error Occurred, Try again!");
            }
          });
          break;
        case 2:
          $('#title').val('');
          $('#description').val('');
          $('#cycleid').val('');
          $('#type').val(2);
          $('#start').val('');
          $('#end').val('');
          $('#customSwitch3').removeAttr('checked');
          $('#cycleForm').modal('show');
          break;
        case 3:
          if (confirm('{{ __('cycles.sure delete cycle') }}')) {
            $.ajax({
              type: "POST",
              url: "/dashboard/cycles/modify",
              data: {
                "cycleid": cycle,
                "type": 3,
              },
              success: function(response) {
                console.log(response);
                location.href = '/dashboard/cycles';
              },
              error: function(error) {
                console.log(error);
                alert("Error Occurred, Try again!");
              }
            });
          }
          break;
        case 4:
          let step = $('#StepSelect').find(":selected").val();
          let steptxt = $('#StepSelect').find(":selected").text();
          let menu = $('#menu' + step);
          $('#menu1,#menu2,#menu5').hide();
          if (menu !== null) {
            menu.show();
          }
          $('#stype').val(step);
          $('#formtitle').text(steptxt);
          $('#stepForm').modal('show');
          break;
      }
    }

    function changestep(company, step) {
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/screening/changestep",
        data: {
          "company": company,
          "step": step,
        },
        success: function(response) {
          // alert(response.ddddd);
          console.log(response);
          location.href = '/dashboard/cycles/{{ $id }}/registered';
        },
        error: function(error) {
          console.log("Error Occurred, arrange failed");
        }
      });
    }

    const messages = {
      exportSuccess: "{{ __('cycles.export_willsuccess') }}",
      exportNotSuccess: "{{ __('cycles.export_Notsuccess') }}",
    };
  </script>

@stop
