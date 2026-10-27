@extends('layouts.admin')

@section('content')
<div class="card">
  <div class="card-body">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start mb-5">
      <div>
        <h4 class="card-title mb-1">{{ __('admin.Startups') }}</h4>
        <p class="mb-0">{{ __('admin.startups_subtitle') }}</p>
      </div>

      {{-- <button class="btn btn-primary px-4 py-2 shadow-sm">
        <i class="ti ti-plus me-2"></i>
        Add New Startup
      </button> --}}
    </div>

    {{-- Alerts --}}
    @if (isset($errorMessage) && !empty($errorMessage))
      <x-alert message="{{ $errorMessage }}" />
    @endif
    @if (session()->has('message'))
      <x-alert type="success" message="{{ __('admin.' . session()->get('message')) }}" />
    @endif
    @if (session()->has('message2'))
      <x-alert type="success" message="{{ __('admin.' . session()->get('message2')) }}" />
    @endif

    {{-- Search & Filter --}}
    <div class="d-flex gap-3 align-items-center mb-5 flex-wrap">
      <div class="input-group input-group-merge position-relative" style="max-width: 620px; width:100%;">
        <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
        <input 
          type="text"
          class="form-control"
          id="debounced-search"
          placeholder="{{ __('admin.Search') }}..." 
          value="{{ request('search') }}"
        />
      </div>

      <button type="button" class="btn btn-primary px-4 py-2" data-bs-toggle="offcanvas"
        data-bs-target="#filterOffcanvas">
        <i class="ti ti-filter me-2"></i>
        {{ __('admin.filters') }}
      </button>

      @if($hasActiveFilters)
        <a href="{{ route('users.view_companies') }}" class="btn btn-outline-primary" title="{{ __('admin.Reset') }}">
          <i class="ti ti-refresh me-2"></i>
          {{ __('admin.Reset') }}
        </a>
      @endif

      <div class="btn-group">
        <button type="button" class="btn btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown"
          aria-expanded="false">
          <i class="ti ti-file-export me-2"></i>{{ __('admin.Export') }}
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item export-action" data-format="csv" href="#">CSV</a></li>
          <li><a class="dropdown-item export-action" data-format="xlsx" href="#">Excel</a></li>
        </ul>
      </div>
    </div>

    {{-- Count --}}
    <p class="text-muted mb-4">
      {{ __('admin.showing') }} {{ $companies->count() }} {{ __('admin.of') }} {{ $companies->total() }} {{ __('admin.Startups') }}
    </p>

    {{-- Cards --}}
    <div class="row g-4" id="companiesContainer">
      @if (count($companies) == 0)
        <div class="col-12" id="noCompaniesResult">
          @if(request()->has('programFilter') && request('programFilter') != '')
            @include('includes.no-result', ['message' => __('admin.No_Subscribed_Startups')])
          @else
            @include('includes.no-result', ['message' => __('admin.No companies found')])
          @endif
        </div>
      @else
        @foreach ($companies as $company)
          <div class="col-lg-4">
            <div class="card border shadow-none h-100 rounded-4 card-hover">
              <div class="card-body p-4 d-flex flex-column">

                <div class="flex-grow-1">

                  {{-- Top Section --}}
                  <div class="d-flex justify-content-between">
                    <div class="d-flex">
                      <div class="me-3">
                        @if($company->logo)
                          <img src="{{ $company->logo }}" class="rounded-3 border object-fit-cover" width="64" height="64"
                            onerror="this.style.display='none';this.parentElement.querySelector('.placeholder-icon').classList.remove('d-none');">
                        @endif
                        <div class="avatar placeholder-icon {{ $company->logo ? 'd-none' : '' }}"
                          style="width:64px;height:64px;">
                          <div class="avatar-initial rounded-3">
                            <i class="ti ti-building-skyscraper ti-lg text-dark"></i>
                          </div>
                        </div>
                      </div>

                      <div class="min-w-0">
                        <a href="/dashboard/companyProfile/{{ $company->id }}" class="text-decoration-none">
                          <h5 class="mb-1 fw-semibold text-dark text-truncate" style="max-width:180px;">
                            {{ $company->name }}
                          </h5>
                        </a>

                        <div class="d-flex align-items-center gap-2">
                          <span
                            class="badge {{ $company->isAlumni ? 'bg-label-danger' : 'bg-label-primary' }} fw-medium px-3 py-1_5">
                            <i class="ti {{ $company->isAlumni ? 'ti-award' : 'ti-flame' }} ti-xs me-1"></i>
                            {{ $company->isAlumni ? __('startup.alumni') : 'Incubated' }}
                          </span>
                          @if($company->stage && optional($company->stagE)->{'name_' . $l})
                            <span class="badge bg-label-secondary fw-medium px-2 py-1_5">
                              {{ optional($company->stagE)->{'name_' . $l} }}
                            </span>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>

                  {{-- Social & Contact --}}
                  <div class="d-flex align-items-center gap-2 mt-3">
                    @if ($company->email)
                      <a href="mailto:{{ $company->email }}" class="text-body" title="{{ $company->email }}">
                        <i class="ti ti-mail"></i>
                      </a>
                    @endif
                    @if ($company->phone)
                      <a href="tel:{{ $company->phone }}" class="text-body" title="{{ $company->phone }}">
                        <i class="ti ti-phone"></i>
                      </a>
                    @endif
                    @if ($company->website)
                      <a href="{{ strpos($company->website, 'http') === 0 ? $company->website : 'https://' . $company->website }}"
                        target="_blank" class="text-body" title="{{ $company->website }}">
                        <i class="ti ti-world"></i>
                      </a>
                    @endif
                    @if ($company->facebook)
                      <a href="{{ strpos($company->facebook, 'http') === 0 ? $company->facebook : 'http://' . $company->facebook }}"
                        target="_blank" class="text-body">
                        <i class="ti ti-brand-facebook"></i>
                      </a>
                    @endif
                    @if ($company->linkedin)
                      <a href="{{ strpos($company->linkedin, 'http') === 0 ? $company->linkedin : 'http://' . $company->linkedin }}"
                        target="_blank" class="text-body">
                        <i class="ti ti-brand-linkedin"></i>
                      </a>
                    @endif
                    @if ($company->country_code_w_a && $company->whatsapp)
                      <a href="{{ 'https://wa.me/' . $company->country_code_w_a . $company->whatsapp }}" target="_blank"
                        class="text-body">
                        <i class="ti ti-brand-whatsapp"></i>
                      </a>
                    @endif
                  </div>

                  {{-- Description --}}
                  @if(!empty($company->info_company))
                    <p class="text-muted small mt-3 mb-0 line-clamp-2">
                      {{ \Illuminate\Support\Str::limit(strip_tags($company->info_company), 120) }}
                    </p>
                  @endif

                  {{-- Meta --}}
                  <div class="d-flex flex-wrap align-items-center gap-3 mt-3 text-muted small">
                    @if ($company->est_date)
                      <span class="d-flex align-items-center">
                        <i class="ti ti-calendar-event me-1"></i>
                        {{ $company->est_date }}
                        @if($company->yearsOp !== null)
                          <span class="ms-1">({{ $company->yearsOp }} {{ $company->yearsOp == 1 ? 'yr' : 'yrs' }})</span>
                        @endif
                      </span>
                    @else
                      <span class="d-flex align-items-center">
                        <i class="ti ti-calendar-event me-1"></i>
                        N/A
                      </span>
                    @endif

                    <span class="d-flex align-items-center">
                      <i class="ti ti-map-pin me-1"></i>
                      @if (isset($company->country) || isset($company->city))
                        {{ optional($company->city)->{'name_' . $l} }}{{ isset($company->city) && isset($company->country) ? ', ' : '' }}{{ optional($company->country)->{'name_' . $l} }}
                      @else
                        N/A
                      @endif
                    </span>
                  </div>

                  <hr class="my-3">

                  {{-- Founders --}}
                  <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                      <i class="ti ti-users text-primary me-2"></i>
                      <span class="fw-semibold text-dark small text-uppercase">{{ __('admin.Founders') }}</span>
                    </div>

                    @if (count($company->founders) > 0)
                      <div class="d-flex align-items-center gap-2 ps-3">
                        @foreach ($company->founders->take(3) as $founder)
                          <div class="d-flex align-items-center gap-1">
                            <div class="avatar avatar-xs mb-0">
                              <div
                                class="avatar-initial rounded-circle text-dark" title="{{ $founder->Name }}">
                                {{ $founder->initials ?: '?' }}
                              </div>
                            </div>
                            <span class="small text-secondary fw-medium">{{ $founder->Name }}</span>
                          </div>
                        @endforeach
                        @if (count($company->founders) > 3)
                          <span class="badge bg-label-secondary rounded-pill fw-medium">
                            +{{ count($company->founders) - 3 }}
                          </span>
                        @endif
                      </div>
                    @else
                      <div class="d-flex align-items-center ps-3">
                        <div
                          class="avatar-initial rounded-circle bg-label-secondary d-inline-flex align-items-center justify-content-center"
                          style="width:28px;height:28px;">
                          <i class="ti ti-user-off ti-xs text-muted"></i>
                        </div>
                        <span class="small text-muted ms-2">{{ $l == 'en' ? 'No founders listed' : 'لم يتم إدراج أي مؤسسين' }}</span>
                      </div>
                    @endif
                  </div>

                  <hr class="my-3">

                  {{-- Sustainability --}}
                  <div class="mb-2">
                    <div class="d-flex align-items-center mb-2">
                      <i class="ti ti-leaf text-success me-2"></i>
                      <span class="fw-semibold text-dark small text-uppercase">{{ __('admin.sustainability_goals') }}</span>
                    </div>

                    @if (!empty($company->sustainable_array) && count($company->sustainable_array) > 0)
                      <div class="d-flex align-items-center gap-1 ps-3 flex-wrap">
                        @foreach ($company->sustainable_array as $Sustainable)
                          @if ($Sustainable['icon'] && $Sustainable['icon']->icon_ar)
                            <img src="/images/sustainability/{{ $l }}/{{ $Sustainable['icon']->icon_ar }}" alt="{{ $Sustainable['title'] }}"
                              class="border rounded" width="28" height="28" style="object-fit:contain;"
                              title="{{ $Sustainable['title'] }}">
                          @else
                            <span class="badge bg-label-secondary rounded-pill small fw-normal px-2">{{ $Sustainable['title'] }}</span>
                          @endif
                        @endforeach
                        <a href="#" data-bs-toggle="modal" data-bs-target="#sustainabilityModal{{ $company->id }}"
                          class="small text-primary fw-medium ms-1 text-decoration-none">
                          {{ __('admin.view_all') }}
                        </a>
                      </div>

                      <div class="modal fade" id="sustainabilityModal{{ $company->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                          <div class="modal-content">
                            <div class="modal-body">
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              <div class="text-center mb-4">
                                <h4 class="mb-3 fw-bold">{{ __('admin.sustainability_goals') }}</h4>
                                <div class="row justify-content-center">
                                  @foreach ($company->sustainable_array as $Sustainable)
                                    <div class="col-md-3 col-sm-4 col-6 mb-3">
                                      <div class="border shadow-sm rounded p-3 d-flex justify-content-center align-items-center"
                                        style="height: 120px;">
                                        @if ($Sustainable['icon'] && $Sustainable['icon']->icon_ar)
                                          <img src="/images/sustainability/{{ $l }}/{{ $Sustainable['icon']->icon_ar ?? '1.png' }}" alt="icon"
                                            class="img-fluid" style="max-width: 100%; max-height: 100%;">
                                        @endif
                                      </div>
                                    </div>
                                  @endforeach
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    @else
                      <div class="d-flex align-items-center ps-3">
                        <div
                          class="avatar-initial rounded-circle bg-label-secondary d-inline-flex align-items-center justify-content-center"
                          style="width:28px;height:28px;">
                          <i class="ti ti-leaf-off ti-xs text-muted"></i>
                        </div>
                        <span class="small text-muted ms-2">{{ __('admin.no_sustainability_goals_listed') }}</span>
                      </div>
                    @endif
                  </div>

                  <hr class="my-3">

                  {{-- Sector --}}
                  @if ($company->sectoR)
                  <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-category text-warning"></i>
                    <span class="fw-semibold text-capitalize text-dark small">
                      {{ $company->sectoR ? $company->sectoR->{'name_' . $l} : $company->sector }}
                    </span>
                  </div>
                  @endif

                  {{-- Organization & Cycle --}}
                  <div class="d-flex flex-wrap align-items-center gap-3 mt-2 small">

                    @role('Admin')
                    <span class="d-flex align-items-center text-muted">
                      <i class="ti ti-building me-1"></i>
                      {{ optional(optional($company->cycleRelation)->organization)->{'name_' . $l} ?? 'N/A' }}
                    </span>
                    @endrole

                    <span class="d-flex align-items-center text-muted">
                      <i class="ti ti-rocket me-1"></i>
                      {{ optional($company->cycleRelation)->title ?? 'N/A' }}
                    </span>
                  </div>

                </div>

                @if (isset($company->id) && $_user->ableTo('company.profile_view', $permissions))
                  <div class="mt-auto pt-6">
                    <a href="{{ route('company.profile_view', $company->id) }}"
                      class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                      <i class="ti ti-arrow-right"></i>
                      {{ __('admin.View Profile') }}
                    </a>
                  </div>
                @endif
              </div>
            </div>
          </div>
        @endforeach

        <div class="col-12 mt-4">
          {{ $companies->links('pagination::bootstrap-5') }}
        </div>
      @endif

      <div class="col-12 d-none" id="noSearchResultsTemplate">
        @include('includes.no-result', ['message' => __('admin.No Result')])
      </div>
    </div>

  </div>
</div>

{{-- Filter Offcanvas --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="filterOffcanvasLabel">
      <i class="ti ti-filter me-2"></i>
      {{ __('admin.Filter') }} {{ $l == 'en' ? 'Startups' : 'الشركات' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <form id="filter-form" method="GET" action="{{ route('users.view_companies') }}" data-ignore-dirty class="d-flex flex-column h-100">
    <div class="offcanvas-body d-flex flex-column flex-grow-1 overflow-auto">
      <div>
        <div class="form-group mb-4">
          <label for="alumniStatusFilter" class="form-label">{{ __('admin.status') ?? 'Status' }}</label>
          <select name="status" id="alumniStatusFilter" class="form-select select2">
            <option value="all" {{ request('status', 'all') == 'all' ? 'selected' : '' }}>
              {{ __('admin.All') ?? 'All Startups' }}
            </option>
            <option value="incubated" {{ request('status') == 'incubated' ? 'selected' : '' }}>
              {{ __('admin.Active') ?? 'Active Startups' }}
            </option>
            <option value="enrolled" {{ request('status') == 'enrolled' ? 'selected' : '' }}>
              {{ __('admin.Enrolled') ?? 'Enrolled Startups' }}
            </option>
            <option value="alumni" {{ request('status') == 'alumni' ? 'selected' : '' }}>
              {{ __('startup.alumni') ?? 'Alumni Startups' }}
            </option>
            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
              {{ __('admin.Rejected') ?? 'Rejected Startups' }}
            </option>
          </select>
        </div>

        @if(auth()->user()->hasRole('Admin') || auth()->user()->isSubAdmin())
          <div class="form-group mb-4">
            <label for="organizationFilter" class="form-label">{{ __('admin.Organizations') }}</label>
            <select name="orgnization" id="organizationFilter" class="form-select select2">
              <option value="">{{ __('admin.All_Organizations') }}</option>
              @foreach ($orgs as $org)
                <option value="{{ $org->id }}" {{ request('orgnization') == $org->id ? 'selected' : '' }}>
                  {{ $org->{'name_' . $l} }}
                </option>
              @endforeach
            </select>
          </div>
        @endif

        <div class="form-group mb-4">
          <label for="programFilter" class="form-label">{{ __('cycles.Cycle') }}</label>
          <select name="programFilter" id="programFilter" class="form-select select2">
            <option value="">{{ __('admin.All_Programs') }}</option>
            @if(isset($cycles) && count($cycles) > 0)
              @foreach ($cycles as $cycle)
                <option value="{{ $cycle->id }}" {{ $cycle->selected ? 'selected' : '' }}>
                  {{ $cycle->title }}
                </option>
              @endforeach
            @endif
          </select>
        </div>

        <div class="form-group mb-4">
          <label for="stageFilter" class="form-label">{{ __('admin.stage') }}</label>
          <select name="stage" id="stageFilter" class="form-select select2">
            <option value="">{{ __('admin.All_Stages') }}</option>
            @foreach ($stages as $stage)
              <option value="{{ $stage->id }}" {{ request('stage') == $stage->id ? 'selected' : '' }}>
                {{ $stage->{'name_' . $l} }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-group mb-4">
          <label for="sectors" class="form-label">{{ __('admin.Sectors') }}</label>
          <select name="sectors[]" id="sectors" class="form-select select2" multiple="multiple"
            data-placeholder="{{ __('admin.Select_Sectors') }}">
            <option value=""></option>
            @foreach ($sectors as $sector_group)
              <optgroup label="{{ $sector_group->name }}">
                @foreach ($sector_group->children as $sector)
                  <option value="{{ $sector->id }}" {{ $sector->selected ? 'selected' : '' }}>
                    @if(str_contains('name_' . $l, 'ar') || str_contains('name_' . $l, 'en'))
                      {{ $sector->{'name_' . $l} }}
                    @else
                      {{ $sector->name }}
                    @endif
                  </option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    <div class="offcanvas-footer border-top p-3 d-flex gap-2 bg-white">
      <button type="button" class="btn btn-outline-secondary flex-fill" id="cancelFilterBtn" data-bs-dismiss="offcanvas">
        <i class="ti ti-x me-2"></i>
        {{ __('admin.Cancel') }}
      </button>
      <button type="submit" class="btn btn-primary flex-fill">
        <i class="ti ti-filter me-2"></i>
        {{ __('cycles.Apply_Filters') }}
      </button>
    </div>
  </form>
</div>

@section('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.export-action').forEach(function (el) {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          const format = e.target.dataset.format || 'xlsx';
          const params = new URLSearchParams(window.location.search);
          const org = document.querySelector('#organizationFilter');
          if (org && org.value) params.set('orgnization', org.value);
          const program = document.querySelector('select[name="programFilter"]');
          if (program && program.value) params.set('programFilter', program.value);
          const stage = document.querySelector('select[name="stage"]');
          if (stage && stage.value) params.set('stage', stage.value);
          const search = document.querySelector('#searchInput');
          if (search && search.value) params.set('search', search.value);
          params.set('format', format);
          const url = '{{ route('users.companies_export') }}' + '?' + params.toString();
          window.location.href = url;
        });
      });
    });
  </script>
@endsection
@stop

@section('pagescripts')
<script>
  $(document).ready(function () {
    $('#sectors').select2({
      placeholder: "{{ __('admin.Select_Sectors') }}",
      allowClear: true,
      dropdownParent: $('#filterOffcanvas'),
      closeOnSelect: false,
      width: '100%',
      templateResult: function (option) {
        if (!option.id) {
          return option.text;
        }
        return $('<span>' + option.text + '</span>');
      },
      templateSelection: function (option) {
        return option.text || option.element.textContent;
      }
    });

    $('#programFilter').select2({
      dropdownParent: $('#filterOffcanvas'),
      allowClear: false,
      minimumResultsForSearch: 5,
      templateResult: function (option) {
        if (!option.id) {
          return option.text;
        }
        return option.text;
      }
    });

    $('#stageFilter').select2({
      dropdownParent: $('#filterOffcanvas'),
      allowClear: false,
      minimumResultsForSearch: 5,
      templateResult: function (option) {
        if (!option.id) {
          return option.text;
        }
        return option.text;
      }
    });

    $('#organizationFilter').select2({
      dropdownParent: $('#filterOffcanvas'),
      allowClear: false,
      minimumResultsForSearch: 5,
      templateResult: function (option) {
        if (!option.id) {
          return option.text;
        }
        return option.text;
      }
    });

    $('#cancelFilterBtn').on('click', function (e) {
      const hasActiveFilters = checkForActiveFilters();
      if (hasActiveFilters) {
        e.preventDefault();
        window.location.href = "{{ route('users.view_companies') }}";
      }
    });

    function checkForActiveFilters() {
      const urlParams = new URLSearchParams(window.location.search);
      const filterParams = [
        'programFilter', 'sectors', 'stage', 'orgnization'
      ];
      for (let param of filterParams) {
        if (urlParams.has(param) && urlParams.get(param) !== '') {
          return true;
        }
      }
      if (urlParams.has('sectors[]')) {
        return true;
      }
      return false;
    }
  });
</script>
@stop
