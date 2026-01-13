@extends('layouts.admin')

@section('title', __('admin.Reports'))
@section('subTitle', __('admin.View and analyze reports'))

@section('content')
<div class="">
  <div class="">
    <div class="row py-md-3 h-100">
      <div class="col-md-12 h-100">
        {{-- Alerts --}}
        @if (session()->has('msg'))
          <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2"
            role="alert">
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

        <!-- Navigation Pills -->
        @php
          $pills = [
            'mentorship' => ['label' => __('admin.Mentorship'), 'icon' => 'ti ti-school'],
            'training' => ['label' => __('admin.Training sessions'), 'icon' => 'ti ti-chalkboard'],
            'screening' => ['label' => __('admin.Screening'), 'icon' => 'ti ti-scale'],
            'finalScreening' => ['label' => __('admin.Final Screening'), 'icon' => 'ti ti-gavel'],
            'cycles' => ['label' => __('admin.Cycles'), 'icon' => 'ti ti-components'],
            'companies' => ['label' => __('admin.Companies_hub'), 'icon' => 'ti ti-building-skyscraper'],
            'mentorSurveys' => ['label' => __('admin.Surveys_Mentor'), 'icon' => 'ti ti-clipboard-check'],
            'startupSurveys' => ['label' => __('cycles.Startup_Surveys'), 'icon' => 'ti ti-clipboard-list'],
          ];
        @endphp

        <div class="overflow-auto">
          <ul class="nav nav-pills mb-6 flex-nowrap" id="reportTabs" role="tablist">
            @foreach($pills as $key => $data)
              <li class="nav-item" role="presentation">
                <button class="nav-link px-4 py-2 d-flex align-items-center gap-2 @if ($loop->first) active @endif"
                  id="{{ $key }}-tab" data-bs-toggle="pill" data-bs-target="#{{ $key }}" type="button" role="tab"
                  aria-controls="{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                  <i class="{{ $data['icon'] }} ti-18px"></i>
                  <span style="font-size: 14px;">{{ $data['label'] }}</span>
                </button>
              </li>
            @endforeach
          </ul>
        </div>


        <!-- Program Selection -->
        <div class="card card-body mb-6" id="programSelectionCard">
          <div class="form-group d-flex flex-column gap-1">
            <label for="programSelect" class="form-label fs-6 fw-semibold">{{ __('admin.Select Program') }}:</label>
            <select class="form-select select2" id="programSelect" style="width: 100%;"
              data-placeholder="{{ __('admin.Select Program') }}">
              <option value="" selected>{{ __('admin.Select Program') }}</option>
              @foreach ($cycles as $cycle)
                <option value="{{ $cycle->id }}" data-title="{{ $cycle->title }}">{{ $cycle->title }}</option>
              @endforeach
            </select>
            <small class="form-text text-muted">{{ __('admin.Select program to filter reports') }}</small>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content p-0 w-100 h-100" id="reportTabsContent">
          @foreach($pills as $key => $label)
            <div class="w-100 h-100 tab-pane fade @if($loop->first) show active @endif" id="{{ $key }}" role="tabpanel"
              aria-labelledby="{{ $key }}-tab">
              <div class="card">
                <div class="card-body">
                  @if ($key === 'companies')
                    <label class="form-label fs-5 mb-2">{{ __('admin.SelectCompany') }}</label>
                    <h6 class="text-muted">{{ __('admin.SelectOrSearchCompany') }}</h6>
                    <select class="form-control select2" id="companiesSelectOptions"
                      data-placeholder="{{ __('cycles.Select Startup') }}">
                      <option value="">{{ __('cycles.Select Startup') }}</option>
                      @foreach ($companies as $company)
                        <option value="{{ $company->id }}" data-program-id="{{ $company->getAttribute('cycle') }}">
                          {{ $company->name }}
                        </option>
                      @endforeach
                    </select>
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
      beforeSend: function () {
        $('#report-' + type).html(`
          <div class="d-flex justify-content-center align-items-center py-4 loading-message">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        `);
      },
      success: function (data) {
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

  $(document).ready(function () {
    // Get current selected program
    function getSelectedProgram() {
      return $('#programSelect').val() || '';
    }

    // Store original company options
    var $companiesSelect = $('#companiesSelectOptions');
    var originalCompanyOptions = $companiesSelect.children().clone();

    // Clear content of inactive tabs when switching
    $('button[data-bs-toggle="pill"]').on('hide.bs.tab', function (e) {
      var previousType = $(e.target).data('bs-target').replace('#', '');
      // Clean up the content of the tab being hidden to prevent conflicts
      cleanupTabContent(previousType);
    });

    // Load report when tab is shown
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
      var type = $(e.target).data('bs-target').replace('#', ''); // e.g. "mentorship"

      // Hide/show program selection card based on active tab
      if (type === 'cycles' || type === 'companies') {
        $('#programSelectionCard').hide();
      } else {
        $('#programSelectionCard').show();
      }

      // Clear all other tab contents first to ensure only active tab has content
      $('.report-content').each(function () {
        if ($(this).attr('id') !== 'report-' + type) {
          $(this).empty();
        }
      });

      if (type !== 'companies') {
        getAjaxReport(type, 0, getSelectedProgram());
      } else {
        // Trigger change to filter companies if a program is already selected
        $('#programSelect').trigger('change');
      }
    });

    // Load immediately first active tab
    let firstTab = $('#reportTabs .nav-link.active').data('bs-target').replace('#', '');
    if (firstTab !== 'cycles' && firstTab !== 'companies') {
      getAjaxReport(firstTab, 0, getSelectedProgram());
    }

    // Reload all reports when program selection changes
    $('#programSelect').change(function () {
      var selectedProgram = getSelectedProgram();

      // Filter companies dropdown if on companies tab
      if ($('#companies').hasClass('active')) {
        $companiesSelect.empty(); // Clear current options

        originalCompanyOptions.each(function () {
          var optionProgramId = $(this).data('program-id');
          // If no program selected, or option has no program (e.g. placeholder), or matches
          if (selectedProgram === "" || !optionProgramId || optionProgramId == selectedProgram) {
            $companiesSelect.append($(this).clone());
          }
        });

        // Re-initialize select2 or trigger change to update UI
        // Assuming select2 is initialized on .select2 class
        // If select2 is already initialized, triggering change might not be enough if options changed dynamically
        // But usually appending to select and triggering change works for basic select2 usage
        // If not, we might need to destroy and re-init, but let's try trigger first.
        // Actually, for select2, simply modifying the DOM options doesn't always update it.
        // We should notify select2.
        $companiesSelect.trigger('change');
      }

      // Reload all active tabs with new program filter
      $('button[data-bs-toggle="pill"]').each(function () {
        var type = $(this).data('bs-target').replace('#', '');
        if (type !== 'companies' && $('#report-' + type).children().length > 0) {
          getAjaxReport(type, 0, selectedProgram);
        }
      });
    });

    $('#companiesSelectOptions').change(function () {
      let id = $(this).val();
      if (id) getAjaxReport('companies', id, getSelectedProgram());
    });
  });
</script>
@stop