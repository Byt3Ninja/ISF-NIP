@extends('layouts.admin')

@section('style')
<style>
  /* Custom styles for program selection card */
  @media (max-width: 768px) {
    .card-body .row > div {
      margin-bottom: 1rem;
    }

    .card-body .row > div:last-child {
      margin-bottom: 0;
    }

    #programSelect {
      font-size: 16px; /* Prevents zoom on iOS */
    }
  }

  /* Select2 customization for better mobile experience */
  .select2-container--default .select2-selection--single {
    border: 1px solid #d9dee3;
    border-radius: 0.375rem;
    min-height: 38px;
    padding: 0.375rem 0.75rem;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #6f6b7d;
    line-height: 1.5;
    padding: 0;
  }

  .select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #a8aaae;
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
    right: 10px;
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #6f6b7d transparent transparent transparent;
    border-style: solid;
    border-width: 5px 4px 0 4px;
    height: 0;
    left: 50%;
    margin-left: -4px;
    margin-top: -2px;
    position: absolute;
    top: 50%;
    width: 0;
  }

  .select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent #6f6b7d transparent;
    border-width: 0 4px 5px 4px;
  }
</style>
@stop

@section('title', __('admin.Reports'))
@section('subTitle', __('admin.View and analyze reports'))

@section('content')
<div class="content-wrapper mb-12">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row py-md-3 h-100">
      <div class="col-md-12 h-100">
        {{-- Alerts --}}
        @if (session()->has('msg'))
          <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ __('admin.' . session()->get('msg')) }}
            {{ Session::forget('msg') }}
          </div>
        @endif

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
    
        <!-- Program Selection -->
        <div class="card mb-4">
          <div class="card-body py-3">
            <div class="row align-items-center">
              <div class="col-md-3 col-lg-2">
                <div class="d-flex align-items-center gap-2">
                  <i class="ti ti-components ti-lg text-primary"></i>
                  <label for="programSelect" class="form-label mb-0 fw-semibold">{{ __('admin.Select Program') }}:</label>
                </div>
              </div>
              <div class="col-md-9 col-lg-10">
                <select class="form-select select2" id="programSelect" style="width: 100%;">
                  <option value="">{{ __('admin.All Programs') }}</option>
                  @foreach ($cycles as $cycle)
                    <option value="{{ $cycle->id }}" data-title="{{ $cycle->title }}">{{ $cycle->title }}</option>
                  @endforeach
                </select>
                <small class="form-text text-muted mt-1">{{ __('admin.Select program to filter reports') }}</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation Pills -->
        @php
          $pills = [
            'mentorship'     => ['label' => __('admin.Mentorship'),      'icon' => 'ti ti-school'],
            'training'       => ['label' => __('admin.Training sessions'),'icon' => 'ti ti-chalkboard'],
            'screening'          => ['label' => __('admin.Screening'),  'icon' => 'ti ti-scale'],
            'finalScreening'          => ['label' => __('admin.Final Screening'),  'icon' => 'ti ti-gavel'],
            'cycles'         => ['label' => __('admin.Cycles'),          'icon' => 'ti ti-components'],
            'companies'      => ['label' => __('admin.Companies_hub'),       'icon' => 'ti ti-building'],
            'mentorSurveys'  => ['label' => __('admin.Surveys_Mentor'),  'icon' => 'ti ti-clipboard-check'],
            'startupSurveys' => ['label' => __('cycles.Startup_Surveys'), 'icon' => 'ti ti-clipboard-list'],
          ];
        @endphp

        <div class="overflow-auto">
          <ul class="nav nav-pills mb-4 flex-nowrap" id="reportTabs" role="tablist">
            @foreach($pills as $key => $data)
            <li class="nav-item" role="presentation">
              <button 
                class="nav-link px-4 py-2 d-flex align-items-center gap-2 @if ($loop->first) active @endif"
                id="{{ $key }}-tab"
                data-bs-toggle="pill"
                data-bs-target="#{{ $key }}"
                type="button"
                role="tab"
                aria-controls="{{ $key }}"
                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
              >
                <i class="{{ $data['icon'] }} ti-18px"></i>
                <span style="font-size: 14px;">{{ $data['label'] }}</span>
              </button>
            </li>
            @endforeach
          </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content p-0 w-100 h-100" id="reportTabsContent">
          @foreach($pills as $key => $label)
          <div 
            class="w-100 h-100 tab-pane fade @if($loop->first) show active @endif" 
            id="{{ $key }}" 
            role="tabpanel" 
            aria-labelledby="{{ $key }}-tab"
          >
            <div class="card h-100 w-100">
              <div class="card-body py-3 h-100 w-100">
                @if ($key === 'companies')
                  <h5>{{ __('admin.SelectCompany') }}</h5>
                  <p>{{ __('admin.SelectOrSearchCompany') }}</p>
                  <select class="form-control select2" id="companiesSelectOptions">
                    @foreach ($companies as $company)
                      <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                  </select>
                @else
                  {{-- <div class="text-muted">{{ __('admin.LoadingReport') }}...</div> --}}
                @endif

                <!-- Report Content Area -->
                <div class="report-content mt-3" id="report-{{ $key }}"></div>

              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script>
  // Define cleanup function first (function hoisting)
  function cleanupTabContent(type) {
    // Remove any global event handlers that might have been bound by the tab content
    const reportContainer = $('#report-' + type);
    
    // Unbind any document-level events that might be specific to this tab
    if (type === 'screening') {
      $(document).off('click', '.show-company-evaluation');
      $(document).off('click', '#export-csv, #export-excel');
    } else if (type === 'finalScreening') {
      $(document).off('click', '.show-company-evaluation');
      $(document).off('click', '#export-csv, #export-excel');
    }
    
    // Clear the container
    reportContainer.empty();
    
    // Trigger cleanup event for any custom cleanup needed
    $(document).trigger('tabContentCleanup', [type]);
  }

  function getAjaxReport(type, id = 0, programId = '') {
    let dataToSend = { id: id, program_id: programId };

    // Clean up existing content first
    cleanupTabContent(type);

    $.ajax({
      url: "{{ route('reports.view.ajax') }}/" + type,
      data: dataToSend,
      beforeSend: function() {
        $('#report-' + type).html(`
          <div class="d-flex justify-content-center align-items-center py-4 loading-message">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        `);
      },
      success: function(data) {
        if (data['value'] === 1) {
          $('#report-' + type + ' .loading-message').remove();
          $('#report-' + type).html(data['view']);

          // Initialize components if this is the training report
          if (type === 'training' && typeof initializeComponents === 'function') {
            initializeComponents();
          }

          // Trigger content loaded event for any additional initialization
          $(document).trigger('tabContentLoaded', [type]);
        }
      }
    });
  }

  $(document).ready(function() {
    // Get current selected program
    function getSelectedProgram() {
      return $('#programSelect').val() || '';
    }

    // Initialize Select2 for program selection
    $('#programSelect').select2({
      placeholder: '{{ __("admin.Select a program") }}',
      allowClear: true,
      width: '100%'
    });

    // Clear content of inactive tabs when switching
    $('button[data-bs-toggle="pill"]').on('hide.bs.tab', function (e) {
      var previousType = $(e.target).data('bs-target').replace('#','');
      // Clean up the content of the tab being hidden to prevent conflicts
      cleanupTabContent(previousType);
    });

    // Load report when tab is shown
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
      var type = $(e.target).data('bs-target').replace('#',''); // e.g. "mentorship"

      // Clear all other tab contents first to ensure only active tab has content
      $('.report-content').each(function() {
        if ($(this).attr('id') !== 'report-' + type) {
          $(this).empty();
        }
      });

      if (type !== 'companies') {
        getAjaxReport(type, 0, getSelectedProgram());
      }
    });

    // Load immediately first active tab
    let firstTab = $('#reportTabs .nav-link.active').data('bs-target').replace('#','');
    if (firstTab !== 'cycles' && firstTab !== 'companies') {
      getAjaxReport(firstTab, 0, getSelectedProgram());
    }

    // Reload all reports when program selection changes
    $('#programSelect').change(function() {
      var selectedProgram = getSelectedProgram();

      // Reload all active tabs with new program filter
      $('button[data-bs-toggle="pill"]').each(function() {
        var type = $(this).data('bs-target').replace('#','');
        if (type !== 'companies' && $('#report-' + type).children().length > 0) {
          getAjaxReport(type, 0, selectedProgram);
        }
      });
    });

    $('#companiesSelectOptions').change(function() {
      let id = $(this).val();
      if (id) getAjaxReport('companies', id, getSelectedProgram());
    });
  });
</script>
@stop
