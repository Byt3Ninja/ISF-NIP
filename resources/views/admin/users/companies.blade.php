@extends('layouts.admin')

@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop

@section('content')
@if (isset($errorMessage) && !empty($errorMessage))
<div class="alert alert-danger">
  {{ $errorMessage }}
</div>
@endif
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-body">
      <!-- Alert -->
      <section id="multiple-column-form">
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
            <div class="d-flex justify-content-between align-items-center">
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
                    {{ count($companies) }}
                  </span>
                </div>
              </div>
              <div class="d-flex gap-3 align-items-center">
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
            <div
              class="card h-100 d-flex flex-column flex-fill justify-content-between border shadow-sm p-4 mb-3 rounded"
              style="min-height: 420px;" {{--
              onclick="window.location.href='/dashboard/companyProfile/{{ $company->id }}'" --}}>
              @php
              $client = App\User::where('company_id', $company->id)->first();
              @endphp
              <div class="w-100">
                <div class="w-100 d-flex justify-content-between align-items-center">
                  <a style="width: 80%" href="/dashboard/companyProfile/{{ $company->id }}">
                    <h5 class="text-dark mb-0 fw-bold line-clamp-1 text-truncate">{{ $company->{'name_' . $l} }}
                    </h5>
                  </a>
                  <div class="d-flex algin-items-center gap-3">
                    @if ($company->facebook)
                    <a class="d-flex align-items-center"
                      href="{{ strpos($company->facebook, 'http') === 0 ? $company->facebook : 'http://' . $company->facebook }}"
                      target='_blank'>
                      <i class="fa-brands fa-facebook-f fs-4 text-primary"></i>
                    </a>
                    @endif
                    @if ($company->linkedin)
                    <a class="d-flex align-items-center"
                      href="{{ strpos($company->linkedin, 'http') === 0 ? $company->linkedin : 'http://' . $company->linkedin }}"
                      target='_blank'>
                      <img src="/icons/linkedin.svg" width="22" height="22" />
                    </a>
                    @endif
                    @if ($company->country_code_w_a && $company->whatsapp)
                    <a href="{{ 'https://wa.me/' . $company->country_code_w_a . $company->whatsapp }}" target="_blank"
                      class="d-flex align-items-center">
                      <i class="fa-brands fa-whatsapp fs-4 text-success"></i>
                    </a>
                    @endif
                  </div>
                </div>
                <div class="mt-2 border shadow-sm rounded text-center p-4 w-100">
                  <img src="{{ $company->logo ? $company->logo : '/images/logo-placeholder.png' }}"
                    onerror="this.src='/images/logo-placeholder.png'" class="rounded"
                    style="height: 120px; width: 100%; object-fit: contain;" alt="logo" />
                </div>
              </div>
              <div class="w-100 d-flex align-items-center justify-content-between mt-2">
                <h6 class="fw-semibold mt-2 text-capitalize line-clamp-1" style="width: 45%;">
                  @php
                  $sector = App\Sector::where('id', $company->sector)->first();
                  @endphp
                  {{ $sector ? $sector->{'name_' . $l} : $company->sector }}
                </h6>
                @if ($company->stage && optional($company->stagE)->{'name_' . $l})
                <div class="badge bg-label-secondary text-dark d-flex align-items-center">
                  <i class="ti ti-building-estate me-1 text-primary"></i>
                  <span class="line-clamp-1">
                    {{ optional($company->stagE)->{'name_' . $l} }}
                  </span>
                </div>
                @endif
                @php
                // تحويل السلسلة إلى مصفوفة بعد إزالة الشرطات المائلة
                // $Sustainables = explode(',', $company->Sustainable_development);
                $Sustainables = json_decode($company->Sustainable_development);
                @endphp
                @if (!empty($Sustainables) && count($Sustainables) > 0)
                <a class="btn btn-label-secondary" href="#" data-bs-toggle="modal"
                  style="width: 30px; margin-inline-start: -0.5rem;" data-bs-target="#EditLabor"><i
                    class="ti ti-target-arrow ti-16px text-primary"></i>
                </a>
                <div class="modal fade" id="EditLabor" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                    <div class="modal-content">
                      <div class="modal-body">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="text-center mb-4">
                          <h4 class="mb-3 fw-bold">{{ $l == 'en' ? 'Sustainability Goals' : 'اهداف الاستدامة' }}
                          </h4>
                          <div class="row justify-content-center">
                            @foreach ($Sustainables as $Sustainable)
                            @php
                            $icon = App\SustainableIcon::where('title', $Sustainable)->first();
                            @endphp
                            <div class="col-md-3 col-sm-4 col-6 mb-3">
                              <div
                                class="border shadow-sm rounded p-3 d-flex justify-content-center align-items-center"
                                style="height: 120px;">
                                @if ($icon && $icon->icon_ar)
                                <img src="/images/sustainability/{{ $l }}/{{ $icon->icon_ar ?? '1.png' }}" alt="icon"
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
                @endif
              </div>
              <div class="w-100 d-none flex-column justify-content-between" style="height: 80px;">
                {{-- <h6 class="mt-3 fw-bold text-capitalize">{{ $company->{'name_' . $l} }}</h6> --}}
                @php
                $sector = App\Sector::where('id', $company->sector)->first();
                @endphp
                <div style="margin-top: -10px;" class="d-flex justify-content-between align-items-center">
                  @if ($company->est_date)
                  <div class="p-1 bg-label-info rounded">
                    <div class="d-flex align-items-center justif-content-center">
                      <div class="d-flex align-items-center">
                        <i class="ti ti-calendar-time fs-5"></i>
                        <small class="mx-1 fw-bold text-black text-nowrap fs-bold">
                          {{ $company->est_date }}
                        </small>
                      </div>
                      <small class="text-nowrap fw-bold">
                        {{ __('admin.Established date') }}
                      </small>
                    </div>
                  </div>
                  @else
                  <span class="text-nowrap text-sm">
                    {{ $l == 'en' ? 'Social Links' : 'روابط التواصل' }}
                  </span>
                  @endif
                  <div class="d-flex align-items-center" style="gap: 10px;">
                    @if ($company->facebook)
                    <a class="d-flex align-items-center"
                      href="{{ strpos($company->facebook, 'http') === 0 ? $company->facebook : 'http://' . $company->facebook }}"
                      target='_blank'>
                      <i class="fa-brands fa-facebook-f fs-6"></i>
                    </a>
                    @endif
                    @if ($company->linkedin)
                    <a class="d-flex align-items-center"
                      href="{{ strpos($company->linkedin, 'http') === 0 ? $company->linkedin : 'http://' . $company->linkedin }}"
                      target='_blank'>
                      <img src="/icons/linkedin.svg" width="15" height="15" />
                    </a>
                    @endif
                    <a href="#" class="d-flex align-items-center">
                      <i class="fa-brands fa-whatsapp fs-5 text-success"></i>
                    </a>
                    @if ($company->twitter)
                    <a class="d-flex align-items-center"
                      href="{{ strpos($company->twitter, 'http') === 0 ? $company->twitter : 'http://' . $company->twitter }}"
                      target='_blank'>
                      <i class="fa-brands fa-twitter fs-6 text-info"></i>
                    </a>
                    @endif
                    @if ($company->youtube)
                    <a class="d-flex align-items-center"
                      href="{{ strpos($company->youtube, 'http') === 0 ? $company->youtube : 'http://' . $company->youtube }}"
                      target='_blank'>
                      <i class="fa-brands fa-youtube fs-6 text-danger"></i>
                    </a>
                    @endif
                  </div>
                </div>
              </div>

              <!-- Founders -->
              @php
              $founders = App\Team_member::where([
              ['TeamMember', 'Founder'],
              ['Company_id', $company->id],
              ])->get();
              @endphp

              @if (!empty($founders) && count($founders) > 0)
              <div>
                <span class="text-sm">{{ __('admin.Founders') }}</span>
                <div class="row pe-4">
                  <div class="col-12" style="min-width: 100%; overflow-x: auto;">
                    <div class="d-flex align-items-center mt-1 gap-2 ">
                      @foreach ($founders as $founder)
                      <div class="pb-2" style="width: fit-content;">
                        <div class="btn btn-xs btn-outline-secondary d-flex align-items-center gap-1 rounded-pill">
                          <img src="{{ $founder->image ? $founder->image : '/icons/account.svg' }}"
                            onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}"
                            class="rounded-circle" width="24" height="24" />
                          <small class="text-nowrap">
                            {{ $founder->Name }}
                          </small>
                        </div>
                      </div>
                      @endforeach
                    </div>
                  </div>
                </div>
              </div>
              @endif

              <!-- Established Date -->
              <div class="d-flex align-items-center justify-content-between mt-2">
                <div class="d-flex align-items-center gap-1">
                  <i class="ti ti-calendar text-info"></i>
                  <small class="text-muted">
                    {{ __('admin.Established date') }}
                  </small>
                </div>
                <span class="border px-2 rounded">
                  <small class="fw-bold">
                    {{ $company->est_date ?: 'N/A' }}
                  </small>
                </span>
              </div>

              <!-- Program/Cycle Information -->
              <div class="d-flex align-items-center justify-content-between mt-2">
                <div class="d-flex align-items-center gap-1">
                  <i class="ti ti-components text-success"></i>
                  <small class="text-muted">
                    {{ __('cycles.Cycle') }}
                  </small>
                </div>
                <span class="border px-2 rounded bg-label-secondary">
                  <small class="fw-bold text-success">
                    @if ($company->cycle && optional($company->cycleRelation))
                      {{ $l == 'ar' ? optional($company->cycleRelation)->title_ar : optional($company->cycleRelation)->title }}
                    @else
                      N/A
                    @endif
                  </small>
                </span>
              </div>

              <!-- Organization Information (for Admin view) -->
              @role('Admin')
              <div class="d-flex align-items-center justify-content-between mt-2">
                <div class="d-flex align-items-center gap-1">
                  <i class="ti ti-building text-info"></i>
                  <small class="text-muted">
                    {{ __('admin.Organization') }}
                  </small>
                </div>
                <span class="border px-2 rounded bg-label-secondary">
                  <small class="fw-bold text-info">
                    @if ($company->cycleRelation && optional($company->cycleRelation->organization))
                      {{ optional($company->cycleRelation->organization)->{'name_' . $l} }}
                    @else
                      N/A
                    @endif
                  </small>
                </span>
              </div>
              @endrole

              <div class="d-flex align-items-center justify-content-between mt-3">
                <div class="d-flex align-items-center gap-1">
                  <i class="ti ti-map-pin text-danger"></i>
                  <small class="text-muted">{{ optional($company->state)->{'name_' . $l} }}</small>
                </div>
                <div class="border px-2 rounded">
                  <small class="fw-bold">
                    {{ optional($company->city)->{'name_' . $l} }},
                    {{ optional($company->country)->{'name_' . $l} }}
                  </small>
                </div>
              </div>
            </div>
          </div>
          @endforeach
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
                    {{ $l == 'ar' ? $cycle->title_ar : $cycle->title }}
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
              @php $stages = App\Stage::all(); @endphp
              @foreach ($stages as $stage)
                <option value="{{ $stage->id }}" {{ request('stage') == $stage->id ? 'selected' : '' }}>
                  {{ $stage->{'name_' . $l} ?? '' }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Sectors Filter -->
          <div class="form-group mb-4">
            <label for="sectors" class="form-label">{{ __('admin.Sectors') }}</label>
            <select name="sectors[]" id="sectors" class="form-select select2" multiple="multiple">
              @php
              $sectors = App\Sector::whereNull('parent_id')->get();
              $selectedSectors = request('sectors', []);
              @endphp
              @foreach ($sectors as $sector_group)
              <optgroup label="{{ $sector_group->name }}">
                @foreach ($sector_group->children as $sector)
                <option value="{{ $sector->id }}" {{ in_array($sector->id, $selectedSectors) ? 'selected' : '' }}>
                  {{ $sector->{'name_' . $l} }}
                </option>
                @endforeach
              </optgroup>
              @endforeach
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