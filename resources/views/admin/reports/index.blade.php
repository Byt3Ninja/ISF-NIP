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
              <option value="" {{ empty($program_id) ? 'selected' : '' }}>{{ __('admin.Select Program') }}</option>
              @foreach ($cycles as $cycle)
                <option value="{{ $cycle->id }}" data-title="{{ $cycle->title }}" {{ $program_id == $cycle->id ? 'selected' : '' }}>{{ $cycle->title }}</option>
              @endforeach
            </select>
            <small class="form-text text-muted">{{ __('admin.Select program to filter reports') }}</small>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content p-0 w-100 h-100" id="reportTabsContent">
          {{-- Mentorship --}}
          <div class="w-100 h-100 tab-pane fade show active" id="mentorship" role="tabpanel" aria-labelledby="mentorship-tab">
            <div class="card">
              <div class="card-body">
                <div class="report-content mt-3" id="report-mentorship">
                  @include('admin.reports.ajax._mentorshipReport', $mentorshipData)
                </div>
              </div>
            </div>
          </div>

          {{-- Training Sessions --}}
          <div class="w-100 h-100 tab-pane fade" id="training" role="tabpanel" aria-labelledby="training-tab">
            <div class="card">
              <div class="card-body">
                <div class="report-content mt-3" id="report-training">
                  @include('admin.reports.ajax._trainingSessionsReport', $trainingData)
                </div>
              </div>
            </div>
          </div>

          {{-- Screening --}}
          <div class="w-100 h-100 tab-pane fade" id="screening" role="tabpanel" aria-labelledby="screening-tab">
            <div class="card">
              <div class="card-body">
                <div class="report-content mt-3" id="report-screening">
                  @include('admin.reports.ajax._screeningReport', $screeningData)
                </div>
              </div>
            </div>
          </div>

          {{-- Final Screening --}}
          <div class="w-100 h-100 tab-pane fade" id="finalScreening" role="tabpanel" aria-labelledby="finalScreening-tab">
            <div class="card">
              <div class="card-body">
                <div class="report-content mt-3" id="report-finalScreening">
                  @include('admin.reports.ajax._finalScreeningReport', $finalScreeningData)
                </div>
              </div>
            </div>
          </div>

          {{-- Cycles --}}
          <div class="w-100 h-100 tab-pane fade" id="cycles" role="tabpanel" aria-labelledby="cycles-tab">
            <div class="card">
              <div class="card-body">
                <div class="report-content mt-3" id="report-cycles">
                  @include('admin.reports.ajax._cyclesReport', $cyclesData)
                </div>
              </div>
            </div>
          </div>

          {{-- Companies (keeps AJAX - requires company selection) --}}
          <div class="w-100 h-100 tab-pane fade" id="companies" role="tabpanel" aria-labelledby="companies-tab">
            <div class="card">
              <div class="card-body">
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
                <div class="report-content mt-3" id="report-companies"></div>
              </div>
            </div>
          </div>

          {{-- Mentor Surveys --}}
          <div class="w-100 h-100 tab-pane fade" id="mentorSurveys" role="tabpanel" aria-labelledby="mentorSurveys-tab">
            <div class="card">
              <div class="card-body">
                <div class="report-content mt-3" id="report-mentorSurveys">
                  @include('admin.reports.ajax._mentorSurvey', $mentorSurveyData)
                </div>
              </div>
            </div>
          </div>

          {{-- Startup Surveys --}}
          <div class="w-100 h-100 tab-pane fade" id="startupSurveys" role="tabpanel" aria-labelledby="startupSurveys-tab">
            <div class="card">
              <div class="card-body">
                <div class="report-content mt-3" id="report-startupSurveys">
                  @include('admin.reports.ajax._startupSurveyReport', $startupSurveyData)
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script>
  $(document).ready(function () {
    // --- Program selector: reload page with ?program_id=X ---
    $('#programSelect').change(function () {
      var url = new URL(window.location.href);
      var val = $(this).val();
      if (val) {
        url.searchParams.set('program_id', val);
      } else {
        url.searchParams.delete('program_id');
      }
      // Preserve active tab via hash
      url.hash = window.location.hash || '';
      window.location.href = url.toString();
    });

    // --- Hide/show program selection card based on active tab ---
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
      var type = $(e.target).data('bs-target').replace('#', '');
      if (type === 'cycles' || type === 'companies') {
        $('#programSelectionCard').hide();
      } else {
        $('#programSelectionCard').show();
      }
    });

    // --- Preserve active tab on page reload using URL hash ---
    var hash = window.location.hash;
    if (hash) {
      var tabBtn = document.querySelector('button[data-bs-target="' + hash + '"]');
      if (tabBtn) {
        new bootstrap.Tab(tabBtn).show();
      }
    }

    // Update hash on tab change
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
      history.replaceState(null, null, $(e.target).data('bs-target'));
    });

    // --- Companies tab: keep AJAX (only tab still using it) ---
    function getCompanyReport(companyId, programId) {
      $.ajax({
        url: "{{ route('reports.view.ajax') }}/companies",
        data: { id: companyId, program_id: programId },
        beforeSend: function () {
          $('#report-companies').html(
            '<div class="d-flex justify-content-center py-4">' +
            '<div class="spinner-border text-primary" role="status">' +
            '<span class="visually-hidden">Loading...</span></div></div>'
          );
        },
        success: function (data) {
          if (data['value'] === 1) {
            $('#report-companies').html(data['view']);
          }
        }
      });
    }

    $('#companiesSelectOptions').change(function () {
      var id = $(this).val();
      if (id) getCompanyReport(id, $('#programSelect').val() || '');
    });

    // Set initial visibility based on active tab
    var firstTab = ($('#reportTabs .nav-link.active').data('bs-target') || '').replace('#', '');
    if (firstTab === 'cycles' || firstTab === 'companies') {
      $('#programSelectionCard').hide();
    }
  });
</script>
@stop