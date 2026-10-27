@extends('layouts.admin')

@section('content')
  <div class="content-body">

    {{-- ── Flash messages ── --}}
    <section id="multiple-column-form">
      @if (session('message'))
        <x-alert type="success" message="{{ __('admin.' . session('message')) }}" />
      @endif
      @if (session('message2'))
        <x-alert type="danger" message="{{ __('admin.' . session('message2')) }}" />
      @endif

      {{-- Hidden input used by legacy Excel export JS --}}
      <input type="hidden" class="cyclesExcel" value="{{ $com->count() > 0 ? $cycleId : 0 }}">
    </section>

    {{-- ── Cycle selection & top actions ── --}}
    <section id="cycle-selection" class="mb-4">
      <div class="card">
        <div class="card-body">

          {{-- Header text --}}
          <div class="row mb-3">
            <div class="col-12">
              <h4 class="mb-1">{{ __('cycles.manage_registered_startups') }}</h4>
              <p class="mb-0 text-muted">{{ __('cycles.manage_startup_description1') }}</p>
              <p class="mb-0 text-muted">{{ __('cycles.manage_startup_description2') }}</p>
            </div>
          </div>

          {{-- Cycle dropdown + action buttons --}}
          <div class="row g-3 align-items-end">

            {{-- Dropdown --}}
            <div class="col-12 col-lg-5">
              <label class="form-label" for="ccycle">{{ __('cycles.Select_Cycle') }}</label>
              <select id="ccycle" class="form-select select2"
                onchange="window.open('/dashboard/cycles/'+this.value+'/registered','_self');">
                @foreach ($cycles as $cycle)
                  <option value="{{ $cycle->id }}" @selected($cycle->id == $id)>
                    {{ $cycle->title }}
                    ({{ $cycle->start?->format('d-m-Y') ?? '-' }} - {{ $cycle->end?->format('d-m-Y') ?? '-' }})
                    — {{ optional($cycle->organization)->name }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Action buttons --}}
            <div class="col-12 col-lg-7">
              <div class="d-flex flex-wrap gap-2 justify-content-lg-end">

                @if ($_user->ableTo('cycles.list', $permissions))
                  <a class="btn btn-label-success"
                    href="/dashboard/cycles/{{ request('id') ?? $currentCycle?->id }}">
                    <i class="ti ti-recycle me-2"></i>{{ __('admin.manage_steps') }}
                  </a>
                @endif

                @if ($_user->ableTo('programlist.edit', $permissions))
                  <a class="btn btn-label-info"
                    href="/dashboard/programlist/edit/{{ request('id') ?? $currentCycle?->id }}">
                    <i class="ti ti-edit me-2"></i>{{ __('cycles.Edit_Program') }}
                  </a>
                @endif

                @if ($_user->ableTo('programlist.create', $permissions))
                  <a class="btn btn-label-primary" href="/dashboard/programlist/create">
                    <i class="ti ti-plus me-2"></i>{{ __('admin.New Program') }}
                  </a>
                @endif

                @if ($_user->ableTo('programlist.destroy', $permissions))
                  <button id="delete-button" class="btn btn-label-danger">
                    <i class="ti ti-trash me-2"></i>{{ __('cycles.Delete') }}
                  </button>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- ── Main startups card ── --}}
    <div class="card">
      <div class="card-header border-bottom">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
          <h5 class="card-title mb-4 mb-md-0">
            {{ __('cycles.manage_registered_startups') }} {{ __('admin.in') }}
            <span class="fw-bold ms-1">{{ $cycleTitle }}</span>
          </h5>

          <div class="d-flex align-items-center gap-4">
            {{-- Export dropdown --}}
            <div class="dropdown">
              <button class="btn btn-label-secondary dropdown-toggle" type="button"
                id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
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

      {{-- Selected startups tag strip --}}
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
          <div id="selectedStartupsTags" class="d-flex flex-wrap gap-2" style="width:90%;">
            <span class="text-muted fst-italic" id="no-selection-msg">
              {{ __('cycles.no_startups_selected') }}.
            </span>
          </div>
        </div>
      </div>

      {{-- Bulk action toolbar --}}
      <div class="card-datatable table-responsive datatable-basic">
        <div class="d-flex align-items-center gap-4 my-3 pb-3 border-bottom">
          <div class="media align-items-center col-md-6 d-flex gap-2">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#changeStepModal"
              id="bulkChangeStepBtn" disabled>
              <i class="ti ti-pencil me-2"></i>{{ __('cycles.change_step') }}
            </button>
            <span id="bulkMarkAlumniWrapper" class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="top" title="">
              <button type="button" class="btn btn-primary" id="bulkMarkAlumniBtn" disabled>
                <i class="ti ti-pennant me-2"></i>{{ __('startup.mark_alumni') }}
              </button>
            </span>
          </div>
        </div>

        {{-- Startups table --}}
        <table class="table" id="startupsTable">
          <thead class="table-light">
            <tr>
              <th><input type="checkbox" class="form-check-input checkAll"></th>
              <th>{{ __('cycles.Company') }}</th>
              <th>{{ __('admin.registered') }}</th>
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
                  <input type="checkbox" class="form-check-input chboxcyclesReg"
                    data-company-id="{{ $company->id }}"
                    data-user-id="{{ $company->user->id ?? '' }}"
                    data-company-name="{{ $company->companyName }}"
                    data-is-alumni="{{ $company->isAlumni ? '1' : '0' }}"
                    data-at-alumni-step="{{ $company->atAlumniStep ? '1' : '0' }}"
                    value="1"
                    name="chboxcyclesReg[]">
                </td>

                {{-- Company --}}
                <td style="width:150px">
                  <a class="d-flex align-items-center gap-2"
                    href="/dashboard/companyProfile/{{ $company->id }}">
                    <img src="{{ $company->logo ?? '/app-assets/images/logo_placeholder.png' }}"
                      onerror="this.src='/app-assets/images/logo_placeholder.png'"
                      alt="avatar" height="40" width="40" class="rounded-circle border">
                    <small class="fw-semibold text-dark">{{ $company->companyName }}</small>
                  </a>
                </td>

                {{-- User --}}
                <td style="width:150px">
                  <a class="d-flex align-items-center gap-2"
                    href="/dashboard/users/{{ $company->user->id ?? '0' }}/edit">
                    <img src="/191014/{{ $company->user->img_url ?? '/icons/account.svg' }}"
                      onerror="if(this.src!=='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                      alt="avatar" class="rounded-circle border" height="40" width="40">
                    <div class="d-flex flex-column">
                      <small class="fw-semibold text-dark">{{ $company->userName }}</small>
                      <small class="text-muted">{{ $company->user->{'title_'.$l} ?? '' }}</small>
                    </div>
                  </a>
                </td>

                <td><small>{{ $company->user->phone ?? '' }}</small></td>
                <td><small>{{ $company->user->email ?? '' }}</small></td>
                <td><small>{{ $company->currentStepTitle }}</small></td>

                {{-- Row actions --}}
                <td>
                  <div class="d-flex flex-nowrap align-items-center gap-2">

                    @unless ($company->isAlumni)
                      <button type="button"
                        class="btn btn-sm btn-light text-nowrap change-step-single"
                        data-company-id="{{ $company->id }}"
                        data-company-name="{{ $company->companyName }}"
                        data-user-name="{{ $company->userName }}"
                        data-current-step="{{ $company->step }}"
                        data-current-step-title="{{ $company->currentStepTitle }}">
                        <i class="ti ti-pencil ti-16px me-1"></i> {{ __('cycles.change_step') }}
                      </button>
                    @endunless
                    @if($company?->cycleData?->type == 'General_Program')
                    <span @if(!$company->isAlumni && !$company->atAlumniStep) title="{{ __('startup.not_passed_final_screening_tooltip') }}" style="cursor:not-allowed;display:inline-block;" @endif>
                      <button type="button"
                        class="btn btn-sm {{ $company->isAlumni ? 'btn-secondary' : 'btn-primary' }} text-nowrap mark-alumni-btn"
                        data-company-id="{{ $company->id }}"
                        data-company-name="{{ $company->companyName }}"
                        @if(!$company->isAlumni && !$company->atAlumniStep) style="pointer-events:none;" @endif
                        {{ $company->isAlumni || !$company->atAlumniStep ? 'disabled' : '' }}>
                        <i class="ti ti-pennant ti-16px me-1"></i>
                        <span>{{ $company->isAlumni ? __('startup.already_alumni') : __('startup.mark_alumni') }}</span>
                      </button>
                    </span>
                    @endif

                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- ══════════════════════ MODALS ══════════════════════ --}}

  {{-- Change Step Modal --}}
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
            <select class="form-select select2" id="modalStepSelect">
              <option value="" selected disabled>{{ __('cycles.Select Step') }}</option>
              <option value="0">{{ __('cycles.Disapprove') }}</option>
              @foreach ($steps->where('stype', '!=', '7') as $step)
                <option value="{{ $step->id }}">{{ $step->title }}</option>
              @endforeach
            </select>
          </div>
          <div id="singleCompanyInfo" class="alert alert-light" style="display:none;">
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

  {{-- Filter Modal --}}
  <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">
            <i class="ti ti-filter me-2"></i>{{ __('cycles.Filter_Startups') }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form method="GET" action="{{ route('cycles.users_manage', $id) }}">
          <div class="modal-body">

            {{-- Step --}}
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Step') }}</label>
              <select name="step_id" class="form-select select2" data-placeholder="{{ __('cycles.Select') }}">
                <option value="">{{ __('cycles.Select') }}</option>
                @foreach ($steps as $step)
                  <option value="{{ $step->id }}" @selected($step->id == request('step_id'))>
                    {{ $step->title }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Country --}}
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Country') }}</label>
              <select name="country_id" class="form-select select2" data-placeholder="{{ __('cycles.Select') }}">
                <option value="">{{ __('cycles.Select') }}</option>
                @foreach ($countries as $country)
                  <option value="{{ $country->id }}" @selected($country->id == request('country_id'))>
                    {{ $country->{'name_'.$l} }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Sector --}}
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Sector') }}</label>
              <select name="sector_id" class="form-select select2" data-placeholder="{{ __('cycles.Select') }}">
                <option value="">{{ __('cycles.Select') }}</option>
                @foreach ($sectors as $sector)
                  <option value="{{ $sector->id }}" @selected($sector->id == request('sector_id'))>
                    {{ $sector->{'name_'.$l} }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Stage --}}
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Stages') }}</label>
              <select name="Dstage_id" class="form-select select2">
                <option value="">{{ __('cycles.Select') }}</option>
                @foreach ($stages as $stage)
                  <option value="{{ $stage->id }}" @selected($stage->id == request('Dstage_id'))>
                    {{ $stage->{'name_'.$l} }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Funding Stage --}}
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Funding_Stage') }}</label>
              <select class="form-select select2" name="funding_stage">
                <option value="">{{ __('cycles.Select') }}</option>
                @foreach ($fundingStages as $value => $label)
                  <option value="{{ $value }}" @selected(request('funding_stage') == $value)>
                    {{ $label }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Targeted Market --}}
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Targeted_Market') }}</label>
              <select class="form-select select2" name="targeted_market[]" data-placeholder="{{ __('cycles.Select') }}"  multiple>
                <option value="">{{ __('cycles.Select') }}</option>
                @foreach ($countries as $country)
                  <option value="{{ $country->id }}" @selected($country->id == request('country_id'))>
                    {{ $country->emoji . ' ' . $country->{'name_'.$l} }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Business Type --}}
            <div class="mb-3">
              <label class="form-label">{{ __('cycles.Business_Type') }}</label>
              <select class="form-select select2" name="BType">
                <option value="0">{{ __('admin.All') }}</option>
                @foreach ($bTypes as $bt)
                  <option value="{{ $bt }}" @selected(request('BType') == $bt)>{{ $bt }}</option>
                @endforeach
              </select>
            </div>

          </div>

          <div class="modal-footer">
            <a class="btn btn-label-secondary"
              href="{{ route('cycles.users_manage', $id) }}">
              {{ __('cycles.Cancel') }}
            </a>
            <button type="submit" class="btn btn-primary">
              {{ __('cycles.Apply_Filters') }}
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
@stop

@section('scripts')
  <script>
    window.PageConfig = {
      cycleId:         {{ $id }},
      locale:          '{{ $l }}',
      csrfToken:       '{{ csrf_token() }}',
      redirectBase:    '/dashboard/cycles/{{ $id }}/registered',
      i18n: {
        pleaseSelectStep:         '{{ __("cycles.please_select_step") }}',
        noStartupsSelected:       '{{ __("cycles.no_startups_selected") }}',
        confirmBtnText:           '{{ __("admin.Confirm") }}',
        cancelBtnText:            '{{ __("admin.Cancel") }}',
        markAlumniTitle:          '{{ __("startup.mark_alumni") }}',
        markAlumniSuccess:        '{{ __("startup.mark_alumni_success") }}',
        markAlumniError:          '{{ __("startup.mark_alumni_error") }}',
        alreadyAlumni:            '{{ __("startup.already_alumni") }}',
        sureDeleteCycle:          '{{ __("cycles.sure delete cycle") }}',
        errorOccurred:            '{{ __("admin.unexpected_error") }}',
        success:                  '{{ __("admin.success") }}',
        error:                    '{{ __("admin.Error") }}',
        warning:                  '{{ __("admin.Warning") }}',
        exportSuccess:            '{{ __("cycles.export_willsuccess") }}',
        exportNotSuccess:         '{{ __("cycles.export_Notsuccess") }}',
        markAlumniConfirmation:   '{{ __("startup.mark_alumni_confirmation") }}',
        markAlumniConfirmBulk:    '{{ __("startup.mark_alumni_confirmation_bulk") }}',
        notOnFinalScreeningTooltip: '{{ __("startup.not_on_final_screening_tooltip") }}',
        deleteProgram: '{{ __("cycles.delete_program") }}',
        programDeleted: '{{ __("cycles.program_delete_success") }}',
        yesDelete: '{{ __("admin.DYes") }}',
        noCancel: '{{ __("admin.DNo") }}',
        unexpectedError: '{{ __("admin.unexpected_error") }}',
        alreadyAlumniBtn: '{{ __("startup.already_alumni_btn") }}',
        someNotFinalScreening: '{{ __("startup.some_not_final_screening") }}'
      }
    };
  </script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/app-manage-registered.js"></script>
@stop
