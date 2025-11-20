@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-body">
      <!-- Alerts -->
      <section id="multiple-column-form">
        @if (isset($errorMessage) && !empty($errorMessage))
        <div class="alert alert-danger">
          {{ $errorMessage }}
        </div>
        @endif
        @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible mb-2" role="alert">
          {{ __('admin.' . session()->get('message')) }}
          <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        @endif
        @if (session()->has('message2'))
        <div class="alert alert-danger alert-dismissible mb-2" role="alert">
          {{ __('admin.' . session()->get('message2')) }}
          <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          @endif
      </section>
      <!-- / Alert -->

      <!-- Header -->
      <div class="card mb-8">
        <div class="card-body">
          <div class="row">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
              <div class="d-flex bg-label-info p-2 justify-content-center align-items-center rounded shadow">
                <div class="bg-white d-flex justify-content-center align-items-center rounded"
                  style="width: 40px; height: 40px; shadow">
                  <img src="/icons/filled-rocket.svg" width="25" height="25" alt="Companies" />
                </div>
                <div class="mx-4" style="height: 50px;">
                  <p class="text-black fw-medium fs-5" style="margin-bottom: -3px;">
                    {{ $l == 'en' ? 'Startups' : 'الشركات' }}
                  </p>
                  <span class="fs-5">
                    <!-- Total Companies Count -->
                    {{ $companies->total() }}
                  </span>
                </div>
              </div>
              <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center mt-4 mt-md-0">
                <!-- Search Bar -->
                <div class="input-group input-group-merge search-form" style="width: 300px;">
                  <span class="input-group-text"><i class="ti ti-search text-muted"></i></span>
                  <input type="text" id="searchInput" class="form-control"
                    placeholder="{{ __('admin.Search') }} {{ $l == 'en' ? 'Startups' : 'الشركات' }}" />
                </div>
                
                <!-- Reset Button (shown when filters are active) -->
                @php
                  $hasActiveFilters = request()->hasAny(['programFilter', 'sector', 'stage', 'orgnization']) || 
                                    (request()->has('sectors') && !empty(request('sectors')));
                @endphp
                @if($hasActiveFilters)
                  <a href="{{ route('users.view_companies') }}" class="btn btn-outline-secondary" title="{{ __('admin.Reset_Filters') }}">
                    <i class="ti ti-refresh me-2"></i>
                    {{ __('admin.Reset') }}
                  </a>
                @endif
                
                <!-- Filter Button -->
                <button type="button" class="btn btn-label-secondary text-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                  <i class="ti ti-filter me-2"></i>
                  <span class="mx-1">{{ __('cycles.Show_Filters') }}</span>
                </button>
                <!-- Export Dropdown -->
                <div class="btn-group">
                  <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti ti-download me-2"></i>{{ __('admin.Export') }}
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item export-action" data-format="csv" href="#">CSV</a></li>
                    <li><a class="dropdown-item export-action" data-format="xlsx" href="#">Excel</a></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Header -->

      <!-- Company List -->
      <section class="mb-10 mt-4">
        <div class="row" id="companiesContainer">
          @if (count($companies) == 0)
            <div class="col-12" id="noCompaniesResult">
              @if(request()->has('programFilter') && request('programFilter') != '')
                @include('includes.no-result', ['message' => __('admin.No_Subscribed_Startups')])
              @else
                @include('includes.no-result', ['message' => __('admin.No Companies Found')])
              @endif
            </div>
          @else
            @foreach ($companies as $company)
            <!-- Company Card -->
            <div class="col-md-4 mb-6">
              <div class="card h-100">
                <div class="card-body d-flex flex-column">
                  @php
                    $client = App\User::where('company_id', $company->id)->first();
                  @endphp
                  <div class="d-flex justify-content-between align-items-center">
                    <a href="/dashboard/companyProfile/{{ $company->id }}" class="w-75">
                      <h5 class="text-dark mb-0 fw-bold line-clamp-1 text-truncate">
                        {{ $company->{'name_' . $l} }}
                      </h5>
                    </a>
                    <div class="d-flex align-items-center gap-2">
                      @if ($company->facebook)
                        <a href="{{ strpos($company->facebook, 'http') === 0 ? $company->facebook : 'http://' . $company->facebook }}"
                          target='_blank' class="text-body">
                          <i class="ti ti-brand-facebook"></i>
                        </a>
                      @endif
                      @if ($company->linkedin)
                        <a href="{{ strpos($company->linkedin, 'http') === 0 ? $company->linkedin : 'http://' . $company->linkedin }}"
                          target='_blank' class="text-body">
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
                  </div>

                  <div class="text-center mt-2 mb-4 position-relative border rounded">
                    <img src="{{ $company->logo ? $company->logo : '/images/logo-placeholder.png' }}"
                      onerror="this.src='/images/logo-placeholder.png'" class="rounded"
                      style="height: 110px; width: 100%; object-fit: contain;" alt="logo" />

                    {{-- Alumni Badge --}}
                    @if ($company->alumni == '1' || $company->alumni == 1)
                      <div class="position-absolute bottom-0 start-0">
                        <span class="badge bg-label-danger">
                          {{ __('startup.alumni') }}
                        </span>
                      </div>
                    @endif
                  </div>

                  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h6 class="fw-semibold text-capitalize line-clamp-2 mb-0">
                      @php
                        $sector = App\Sector::where('id', $company->sector)->first();
                      @endphp
                      {{ $sector ? $sector->{'name_' . $l} : $company->sector }}
                    </h6>
                    <div class="d-flex flex-nowrap align-items-center gap-2">
                      @if ($company->stage && optional($company->stagE)->{'name_' . $l})
                        <span class="badge bg-label-secondary text-dark text-truncate">
                          <i class="ti ti-building-estate ti-xs me-1 text-primary"></i>
                          {{ optional($company->stagE)->{'name_' . $l} }}
                        </span>
                      @endif
                      @php
                        $Sustainables = json_decode($company->Sustainable_development);
                      @endphp
                      @if (!empty($Sustainables) && count($Sustainables) > 0)
                        <a class="badge bg-label-secondary" href="#" data-bs-toggle="modal"
                          data-bs-target="#sustainabilityModal{{ $company->id }}"><i
                            class="ti ti-target-arrow text-primary ti-xs"></i></a>
                        <div class="modal fade" id="sustainabilityModal{{ $company->id }}" tabindex="-1"
                          aria-hidden="true">
                          <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                            <div class="modal-content">
                              <div class="modal-body">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                  aria-label="Close"></button>
                                <div class="text-center mb-4">
                                  <h4 class="mb-3 fw-bold">
                                    {{ $l == 'en' ? 'Sustainability Goals' : 'اهداف الاستدامة' }}</h4>
                                  <div class="row justify-content-center">
                                    @foreach ($Sustainables as $Sustainable)
                                      @php
                                        $icon = App\SustainableIcon::where('title', $Sustainable)->first();
                                      @endphp
                                      <div class="col-md-3 col-sm-4 col-6 mb-3">
                                        <div class="border shadow-sm rounded p-3 d-flex justify-content-center align-items-center"
                                          style="height: 120px;">
                                          @if ($icon && $icon->icon_ar)
                                            <img
                                              src="/images/sustainability/{{ $l }}/{{ $icon->icon_ar ?? '1.png' }}"
                                              alt="icon" class="img-fluid"
                                              style="max-width: 100%; max-height: 100%;">
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
                      @endif
                    </div>
                  </div>

                <!-- Founders -->
                  @php
                  $founders = App\Team_member::where([
                      ['TeamMember', 'Founder'],
                      ['Company_id', $company->id],
                  ])->get();
                  @endphp

                  @if (count($founders) > 0)
                    <div class="d-flex align-items-center mb-3">
                      <span class="fw-semibold me-2">
                        {{ count($founders) > 1 ? __('admin.Founders') : __('admin.Founder') }}:
                      </span>

                      <div class="d-flex align-items-center">
                        <ul class="list-unstyled d-flex align-items-center mb-0">
                          @foreach ($founders->take(2) as $founder)
                            <li class="badge badge-pill bg-label-secondary me-1">
                              <span class="fw-semibold text-secondary">{{ $founder->Name }}</span>
                            </li>
                          @endforeach

                          @if (count($founders) > 2)
                            <li class="fw-semibold text-secondary">
                              +{{ count($founders) - 2 }}
                            </li>
                          @endif
                        </ul>
                      </div>
                    </div>
                  @endif


                  <div class="mt-auto">
                    <ul class="list-unstyled mb-4">
                      @role('Admin')
                        <li class="d-flex align-items-center mb-2">
                          <i class="ti ti-building text-info"></i>
                          <span class="fw-semibold mx-2">{{ __('admin.Organization') }}:</span>
                          <span
                            class="text-muted">{{ optional(optional($company->cycleRelation)->organization)->{'name_' . $l} ?? 'N/A' }}</span>
                        </li>
                      @endrole
                      <li class="d-flex align-items-center mb-2">
                        <i class="ti ti-components text-primary"></i>
                        <span class="fw-semibold mx-2">{{ __('cycles.Cycle') }}:</span>
                        <span class="text-muted">{{ optional($company->cycleRelation)->title ?? 'N/A' }}</span>
                      </li>
                      <li class="d-flex align-items-center mb-2">
                        <i class="ti ti-calendar text-dark"></i>
                        <span class="fw-semibold mx-2">{{ __('admin.Established date') }}:</span>
                        <span class="text-muted">{{ $company->est_date ?: 'N/A' }}</span>
                      </li>
                      @if (isset($company->country) || isset($company->city))
                        <li class="d-flex align-items-center">
                          <i class="ti ti-map-pin text-muted"></i>
                          <span class="fw-semibold mx-2">{{ __('admin.Location') }}:</span>
                          <span class="text-muted">{{ optional($company->city)->{'name_' . $l} }},
                            {{ optional($company->country)->{'name_' . $l} }}</span>
                        </li>
                      @endif
                    </ul>
                  </div>
                </div>
                @if (isset($company->id) && $_user->ableTo('company.profile_view', $permissions))
                  <div class="card-footer border-top-0">
                    <a href="{{ route('company.profile_view', $company->id) }}" class="btn btn-primary w-100">
                      {{ __('admin.View Profile') }}
                    </a>
                  </div>
                @endif
              </div>
            </div>
            @endforeach

            <div class="mt-8">
              {{ $companies->links('pagination::bootstrap-5') }}
            </div>
          @endif
          
          <!-- Hidden No Search Results Template -->
          <div class="col-12 d-none" id="noSearchResultsTemplate">
            @include('includes.no-result', ['message' => __('admin.No Result')])
          </div>
        </div>
      </section>
      <!-- / Company List -->
        @section('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function(){
          document.querySelectorAll('.export-action').forEach(function(el){
            el.addEventListener('click', function(e){
              e.preventDefault();
              const format = e.target.dataset.format || 'xlsx';
              // Build query params from current URL and filter modal inputs
              const params = new URLSearchParams(window.location.search);
              // If modal inputs present, include their values
              const org = document.querySelector('#organizationFilter');
              if (org && org.value) params.set('orgnization', org.value);
              const program = document.querySelector('select[name="programFilter"]');
              if (program && program.value) params.set('programFilter', program.value);
              const stage = document.querySelector('select[name="stage"]');
              if (stage && stage.value) params.set('stage', stage.value);
              // include search input if used
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
    </div>
  </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <form method="GET" action="{{ route('users.view_companies') }}">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">
            <i class="ti ti-filter me-2"></i>
            {{ __('admin.Filter') }} {{ $l == 'en' ? 'Startups' : 'الشركات' }}
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <!-- Organization Filter (Admin only) -->
          @role('Admin')
          <div class="form-group mb-4">
            <label for="organizationFilter" class="form-label">{{ __('admin.Organizations') }}</label>
            <select name="orgnization" id="organizationFilter" class="form-select select2">
              <option value="">{{ __('admin.All_Organizations') }}</option>
              @php $orgs = App\Organization::all(); @endphp
              @foreach ($orgs as $org)
                <option value="{{ $org->id }}" {{ request('orgnization') == $org->id ? 'selected' : '' }}>
                  {{ $org->{'name_' . $l} }}
                </option>
              @endforeach
            </select>
          </div>
          @endrole

          <!-- Program Filter -->
          <div class="form-group mb-4">
            <label for="programFilter" class="form-label">{{ __('cycles.Cycle') }}</label>
            <select name="programFilter" id="programFilter" class="form-select select2">
              <option value="">{{ __('admin.All_Programs') }}</option>
              @php
                $cycles = App\Cycle::all();
              @endphp
              @if(isset($cycles) && count($cycles) > 0)
                @foreach ($cycles as $cycle)
                  <option value="{{ $cycle->id }}" {{ request('programFilter') == $cycle->id ? 'selected' : '' }}>
                    {{ $cycle->title }}
                  </option>
                @endforeach
              @endif
            </select>
          </div>

          <!-- Stage Filter -->
          <div class="form-group mb-4">
            <label for="stageFilter" class="form-label">{{ __('admin.stage') }}</label>
            <select name="stage" id="stageFilter" class="form-select select2">
              <option value="">{{ __('admin.All_Stages') }}</option>
              @php
                $stages = App\Stage::all()->unique('name_' . $l);
              @endphp
              @foreach ($stages as $stage)
                <option value="{{ $stage->id }}" {{ request('stage') == $stage->id ? 'selected' : '' }}>
                  {{ $stage->{'name_' . $l} }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Sectors Filter -->
          <div class="form-group mb-4">
            <label for="sectors" class="form-label">{{ __('admin.Sectors') }}</label>
            <select name="sectors[]" id="sectors" 
                    class="form-select select2"
                    multiple="multiple"
                    data-placeholder="{{ __('admin.Select_Sectors') }}">

                <!-- REQUIRED for Select2 placeholder -->
                <option value=""></option>

                @php
                  $sectors = App\Sector::whereNull('parent_id')->get();
                  $selectedSectors = request('sectors', []);
                @endphp

                @foreach ($sectors as $sector_group)
                  <optgroup label="{{ $sector_group->name }}">
                    @foreach ($sector_group->children as $sector)
                      <option value="{{ $sector->id }}" 
                              {{ in_array($sector->id, $selectedSectors) ? 'selected' : '' }}>
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

          <!-- Alumni Status Filter -->
          <div class="form-group mb-4">
            <label for="alumniFilter" class="form-label">{{ __('startup.alumni_status') }}</label>
            <select name="alumni" id="alumniFilter" class="form-select select2" data-value="all" data-placeholder="{{ __('admin.Select Status') }}">
              <option value="all">{{ __('startup.all_startups') }}</option>
              <option value="1" {{ request('alumni') == "1" ? 'selected' : '' }}>
                {{ __('startup.alumni_only') }}
              </option>
              <option value="0" {{ request('alumni') == "0" ? 'selected' : '' }}>
                {{ __('startup.non_alumni') }}
              </option>
            </select>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" id="cancelFilterBtn">
            <i class="ti ti-x me-2"></i>
            {{ __('admin.Cancel') }}
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="ti ti-filter me-2"></i>
            {{ __('cycles.Apply_Filters') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop

@section('pagescripts')
<script>
  $(document).ready(function() {
    // JavaScript Search Functionality
    $('#searchInput').on('input', function() {
      const searchTerm = $(this).val().toLowerCase();
      const companyCards = $('#companiesContainer .col-md-4');
      let visibleCount = 0;
      
      companyCards.each(function() {
        const card = $(this);
        const companyName = card.find('h5.text-dark').text().toLowerCase();
        const companyFounders = card.find('small.text-nowrap').map(function() {
          return $(this).text().toLowerCase();
        }).get().join(' ');
        const companySector = card.find('h6.fw-semibold').text().toLowerCase();
        const companyStage = card.find('.badge.bg-label-secondary span').text().toLowerCase();
        const companyProgram = card.find('.text-success').text().toLowerCase();
        const companyOrganization = card.find('.text-info').text().toLowerCase();
        
        const searchableText = companyName + ' ' + companyFounders + ' ' + companySector + ' ' + companyStage + ' ' + companyProgram + ' ' + companyOrganization;
        
        if (searchableText.includes(searchTerm)) {
          card.show();
          visibleCount++;
        } else {
          card.hide();
        }
      });
      
      // Show/hide no results message
      if (visibleCount === 0 && searchTerm !== '') {
        if ($('#noSearchResults').length === 0) {
          // Clone the template and show it
          const noResultsTemplate = $('#noSearchResultsTemplate').clone();
          noResultsTemplate.attr('id', 'noSearchResults');
          noResultsTemplate.removeClass('d-none');
          $('#companiesContainer').append(noResultsTemplate);
        }
        $('#noCompaniesResult').hide();
      } else {
        $('#noSearchResults').remove();
        if (visibleCount > 0) {
          $('#noCompaniesResult').hide();
        }
      }
    });
    
    // Initialize Select2 for different field types
    // Initialize sectors filter (multiple select)
    $('#sectors').select2({
      placeholder: "{{ __('admin.Select Sectors') }}",
      allowClear: true,
      dropdownParent: $('#filterModal'),
      closeOnSelect: false,
      width: '100%',
      templateResult: function(option) {
        // Handle optgroup headers and regular options
        if (!option.id) {
          return option.text;
        }
        return $('<span>' + option.text + '</span>');
      },
      templateSelection: function(option) {
        return option.text || option.element.textContent;
      }
    });
    
    // Initialize program filter (single select with "All" option)
    $('#programFilter').select2({
      dropdownParent: $('#filterModal'),
      allowClear: false,
      minimumResultsForSearch: 5, // Show search box only if more than 5 options
      templateResult: function(option) {
        if (!option.id) {
          return option.text;
        }
        return option.text;
      }
    });
    
    // Initialize stage filter (single select with "All" option)
    $('#stageFilter').select2({
      dropdownParent: $('#filterModal'),
      allowClear: false,
      minimumResultsForSearch: 5, // Show search box only if more than 5 options
      templateResult: function(option) {
        if (!option.id) {
          return option.text;
        }
        return option.text;
      }
    });
    
    // Initialize organization filter (single select with "All" option)
    $('#organizationFilter').select2({
      dropdownParent: $('#filterModal'),
      allowClear: false,
      minimumResultsForSearch: 5, // Show search box only if more than 5 options
      templateResult: function(option) {
        if (!option.id) {
          return option.text;
        }
        return option.text;
      }
    });
    
    // Handle cancel button logic
    $('#cancelFilterBtn').on('click', function() {
      // Check if any filters are active
      const hasActiveFilters = checkForActiveFilters();
      
      if (hasActiveFilters) {
        // Reset page if filters are active
        window.location.href = "{{ route('users.view_companies') }}";
      } else {
        // Just close modal if no filters are active
        $('#filterModal').modal('hide');
      }
    });
    
    // Function to check if any filters are currently active
    function checkForActiveFilters() {
      const urlParams = new URLSearchParams(window.location.search);
      
      // List of filter parameters to check (excluded 'search' since it's now JS-based)
      const filterParams = [
        'programFilter', 'sectors', 'stage', 'orgnization'
      ];
      
      // Check if any filter parameter exists and has a value
      for (let param of filterParams) {
        if (urlParams.has(param) && urlParams.get(param) !== '') {
          return true;
        }
      }
      
      // Special check for sectors array
      if (urlParams.has('sectors[]')) {
        return true;
      }
      
      return false;
    }
  });
</script>
@stop