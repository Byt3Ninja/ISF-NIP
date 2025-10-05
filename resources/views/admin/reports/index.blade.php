@extends('layouts.admin')

@section('title', __('admin.Profile'))
@section('subTitle', __('admin.Edit your profile information'))

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
    
        <!-- Navigation Pills -->
        @php
          $pills = [
            'mentorship'     => ['label' => __('admin.Mentorship'),      'icon' => 'ti ti-school'],
            'training'       => ['label' => __('admin.Training sessions'),'icon' => 'ti ti-chalkboard'],
            'judge'          => ['label' => __('admin.Screening result'),  'icon' => 'ti ti-scale'],
            'cycles'         => ['label' => __('admin.Cycles'),          'icon' => 'ti ti-refresh'],
            'companies'      => ['label' => __('admin.Companies'),       'icon' => 'ti ti-building'],
            'mentorSurveys'  => ['label' => __('admin.Surveys_Mentor'),  'icon' => 'ti ti-clipboard-check'],
            'startupSurveys' => ['label' => __('admin.Surveys_Startup'), 'icon' => 'ti ti-clipboard-list'],
          ];
        @endphp

        <div class="overflow-auto">
          <ul class="nav nav-pills mb-4 flex-nowrap" id="reportTabs" role="tablist">
            @foreach($pills as $key => $data)
            <li class="nav-item" role="presentation">
              <button 
                class="nav-link d-flex align-items-center gap-2 @if ($loop->first) active @endif"
                id="{{ $key }}-tab"
                data-bs-toggle="pill"
                data-bs-target="#{{ $key }}"
                type="button"
                role="tab"
                aria-controls="{{ $key }}"
                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
              >
                <i class="{{ $data['icon'] }} ti-18px"></i>
                <span>{{ $data['label'] }}</span>
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
                @if ($key === 'cycles')
                  <h5>{{ __('admin.SelectCycle') }}</h5>
                  <p>{{ __('admin.SelectOrSearchCycle') }}</p>
                  <select class="form-control select2" id="cyclesSelectOptions">
                    @foreach ($cycles as $cycle)
                      <option value="{{ $cycle->id }}">{{ $cycle->title }}</option>
                    @endforeach
                  </select>
                @elseif ($key === 'companies')
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
  $(document).ready(function() {

    // Load report when tab is shown
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
      var type = $(e.target).data('bs-target').replace('#',''); // e.g. "mentorship"
      if (type !== 'cycles' && type !== 'companies') {
        getAjaxReport(type, 0);
      }
    });

    // Load immediately first active tab
    let firstTab = $('#reportTabs .nav-link.active').data('bs-target').replace('#','');
    if (firstTab !== 'cycles' && firstTab !== 'companies') {
      getAjaxReport(firstTab, 0);
    }

    // Load when cycle/company select changes
    $('#cyclesSelectOptions').change(function() {
      let id = $(this).val();
      if (id) getAjaxReport('cycles', id);
    });

    $('#companiesSelectOptions').change(function() {
      let id = $(this).val();
      if (id) getAjaxReport('companies', id);
    });

  });

  function getAjaxReport(type, id = 0) {
    $.ajax({
      url: "{{ route('reports.view.ajax') }}/" + type,
      data: { id: id },
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
        }
      }
    });
  }
</script>
@stop
