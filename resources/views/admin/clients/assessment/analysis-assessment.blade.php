@extends('layouts.admin')
@section('style')
  <style>
    #chart {
      width: 100%;
      height: 500px;
    }
  </style>
@stop

@php
  $pills = ['analysis-assessment', 'question-assessment', 'history'];
  $userid = auth()->user()->id;
  $assessment = App\Assessment::where('user_id', $userid)->orderBy('id', 'desc')->first();
  $assessment_user = App\Assessment::where('user_id', $userid)->whereNull('end_date')->first();
  
  $scoresTotals = $assessment ? 
    App\UserScore::where('user_id', $userid)->where('assessment_id', $assessment->id)->sum('weighted_score') : 0;
@endphp

@section('content')
  <div class="">
    <div class="">
      <!-- Alert -->
      @if (session()->has('message'))
        {{-- The controller flashes an already-translated string plus a bootstrap
             variant in 'class'; fall back to success when none was supplied. --}}
        <div class="alert alert-{{ session()->get('class', 'success') }} alert-dismissible d-flex align-items-center"
          role="alert">
          <i class="ti ti-info-circle me-2 flex-shrink-0"></i>
          <span>{{ session()->get('message') }}</span>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('admin.Close') }}"></button>
        </div>
      @endif
      @if (session()->has('message2'))
        <div class="alert alert-danger alert-dismissible" role="alert">
          {{ __('admin.' . session()->get('message2')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      <!-- / Alert -->
      <!-- Pills (Mobile) -->
      <div class="row mb-4 d-block d-md-none">
        <div class="col-12">
          <ul class="nav nav-pills">
            @foreach ($pills as $key => $pill)
              <li class="nav-item me-1 mb-3 w-100">
                @if ($pill == 'question-assessment' && !$assessment_user)
                  <span class="nav-link" style="cursor: not-allowed; opacity: 0.5;"
                        title="{{ __('admin.No_Questions_Available') }}">
                    <span>{{ __('admin.Question_assessment') }}</span>
                  </span>
                @else
                  <a href="/dashboard/{{ $key == 2 ? 'history-assessment' : $pill }}"
                     class="nav-link {{ $key == 0 ? 'active' : '' }}">
                    <span>
                      @if ($pill == 'analysis-assessment')
                        {{ __('admin.Analysis_Assessment') }}
                      @elseif($pill == 'question-assessment')
                        {{ __('admin.Question_assessment') }}
                      @else
                        {{ __('admin.History') }}
                      @endif
                    </span>
                  </a>
                @endif
              </li>
            @endforeach
          </ul>
        </div>
      </div>
      <!-- Pills (Mobile) -->
      <!-- Pills -->
      <div class="row mb-4 d-none d-md-block">
        <div class="col-12">
          <div class="card shadow-sm">
            <ul class="nav nav-pills flex-nowrap px-5 py-4">
              @foreach ($pills as $key => $pill)
                <li class="nav-item me-1">
                  @if ($pill == 'question-assessment' && !$assessment_user)
                    <span class="nav-link" style="cursor: not-allowed; opacity: 0.5;"
                          title="{{ __('admin.No_Questions_Available') }}">
                      <span class="fs-6">{{ __('admin.Question_assessment') }}</span>
                    </span>
                  @else
                    <a href="/dashboard/{{ $key == 2 ? 'history-assessment' : $pill }}"
                       class="nav-link {{ $key == 0 ? 'active' : '' }}">
                      <span class="fs-6">
                        @if ($pill == 'analysis-assessment')
                          {{ __('admin.Analysis_Assessment') }}
                        @elseif($pill == 'question-assessment')
                          {{ __('admin.Question_assessment') }}
                        @else
                          {{ __('admin.History') }}
                        @endif
                      </span>
                    </a>
                  @endif
                </li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
      <!-- / Pills -->

      <!--Analysis Assessment Card -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card shadow-sm w-100">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center flex-grow-1 justify-content-between border-bottom mb-6">
              <h5 class="card-title mb-4 mb-md-0">{{ __('admin.Analysis_Assessment') }}</h5>
              <div class="d-flex align-items-center gap-2">
                <span class="text-xs text-secondary">{{ __('admin.Created at') }}</span>
                <div class="badge bg-label-secondary">
                  <i class="ti ti-calendar ti-18px text-info"></i>
                  <span class="fw-semibold text-xs text-dark ms-1">
                    {{ $assessment == null ? 'None' : $assessment->created_at }}
                  </span>
                </div>
              </div>
            </div>
            <div class="w-100 d-flex flex-column gap-3 card-body">
              <div
                class="d-flex flex-column flex-md-row gap-2 flex-grow-1 justify-content-between align-items-md-center mb-2">
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                  <h6 class="text-sm text-muted mb-0 text-nowrap">{{ __('admin.Overall_Recommendation') }}</h6>
                  <div class="badge bg-label-secondary text-dark py-2 fs-6">
                      {{ __('admin.Good_progress_but_room_for_scaling') }}
                  </div>
                </div>
                <div class="w-100 d-flex flex-grow-1 justify-content-start justify-content-md-end align-items-center">
                  @if (!$assessment_user)
                    @php
                      // canStartAssessment() ran in the controller; 'reason' tells us
                      // which precondition is unmet so we can name it for the founder.
                      $canStart = $startCheck['can_proceed'] ?? false;
                      $blockedReason = $startCheck['reason'] ?? null;

                      // Reasons the founder can act on carry a link to the fix.
                      $blockedAction = [
                          'no_company' => ['route' => route('company.profile_view_mine'), 'label' => __('admin.GoToMyStartup')],
                          'no_stage' => ['route' => route('company.profile_view_mine'), 'label' => __('admin.GoToMyStartup')],
                      ][$blockedReason] ?? null;
                    @endphp

                    <div class="d-flex flex-column align-items-start align-items-md-end gap-2">
                      <form action="{{ route('assessments.assign') }}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary" @disabled(!$canStart)
                          @if (!$canStart) aria-describedby="assessment-blocked-hint" @endif>
                          {{ __('admin.Make_a_new_assessment') }}
                        </button>
                      </form>

                      @unless ($canStart)
                        <div id="assessment-blocked-hint"
                          class="d-flex align-items-start gap-2 text-start text-md-end justify-content-md-end"
                          style="max-width: 42ch;">
                          <i class="ti ti-alert-circle ti-16px text-warning flex-shrink-0 mt-1"></i>
                          <span class="text-xs text-muted">
                            {{ __('admin.CannotStartAssessment_' . ($blockedReason ?? 'generic')) }}
                            @if ($blockedAction)
                              <a href="{{ $blockedAction['route'] }}" class="fw-semibold">{{ $blockedAction['label'] }}</a>
                            @endif
                          </span>
                        </div>
                      @endunless
                    </div>
                  @endif

                  @if ($assessment && $assessment->end_date)
                    <a href="{{ route('assessments.results_export_pdf') }}" class="btn btn-sm btn-primary ms-3">
                      {{ __('admin.Export_assessment_pdf') }}
                    </a>
                  @endif
                </div>
              </div>
              <div class="d-flex flex-grow-1 align-items-center gap-3">
                <h6 class="text-sm text-muted mb-0">{{ __('admin.Overall_Weighted_Score') }}</h6>
                <div class="badge bg-label-info text-dark">
                  {{ $scoresTotals > 0 ? $scoresTotals : 0 }}
                </div>
              </div>

              <div class="row">
                @php
                  $colors = ['warning', 'success', 'danger', 'info', 'primary', 'secondary'];
                @endphp
                @foreach ($categories as $key => $phase)
                  @php
                    $color = $colors[$key % count($colors)];
                    $scores = $assessment ? 
                      App\UserScore::where('user_id', $userid)
                        ->where('assessment_id', $assessment->id)
                        ->where('category_id', $phase->id)
                        ->sum('weighted_score') : 0;
                  @endphp

                  <div class="col-12 col-md-3 mb-2">
                    <div class="card shadow-sm p-4">
                      <div class="badge bg-label-{{ $color }} mb-3">
                        <h6 class="mb-0 text-{{ $color }} text-uppercase">{{ $phase->{'category_name_' . $l} }}</h6>
                      </div>
                      <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ number_format($scores, 2) }}%</h4>
                        <div class="d-flex flex-column align-items-center gap-1">
                          <span class="text-xs text-secondary">{{ __('admin.Score') }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Analysis Assessment Card -->

      <!-- Assessment Radar Chart -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card shadow-sm">
            <div class="card-body">
              <div id="chart" style="display: grid; place-items: center; overflow-x: auto;"></div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Assessment Radar Chart -->

      <!-- Analysis Assessment Row -->
      <div class="row">
        @foreach ($categories as $key => $category)
          @php
            $color = $colors[$key % count($colors)];
          @endphp

          <div class="col-12 col-md-4 col-lg-3 mb-4">
            <div class="card shadow-sm p-4 h-100">
              <div class="badge bg-label-secondary mb-3">
                <h6 class="mb-0 text-dark text-uppercase">{{ $category->{'category_name_' . $l} }}</h6>
              </div>
              @foreach ($category['subcategory'] as $subcategory)
                @php
                  $scoresTotalssub = $assessment ? 
                    App\UserScore::where('user_id', $userid)
                      ->where('assessment_id', $assessment->id)
                      ->where('subcategory_id', $subcategory->id)
                      ->sum('weighted_score') : 0;
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-6">
                  <div class="badge bg-label-{{ $color }} me-2 py-2">
                    <span class="text-{{ $color }} text-capitalize mb-0">{{ $subcategory->{'name_' . $l} }}</span>
                  </div>
                  <div class="d-flex align-items-center gap-1">
                    <div class="d-flex align-items-center gap-1">
                      <span class="fs-tiny text-secondary">{{ __('admin.Score') }}</span>
                      <div class="badge bg-label-info py-1">
                        <span class="text-xs">{{ number_format($scoresTotalssub, 1) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
      <!-- / Analysis Assessment Row -->
    </div>
  </div>
@stop

@section('scripts')
  <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
@stop

@section('pagescripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const analysisUserScores = @json($weightedSubCategories) || [];
      const analysisPreviousUserScores = @json($weightedPreviousSubCategories) || [];

      // Ensure we have data or create default structure
      const hasData = analysisUserScores && analysisUserScores.length > 0;
      const hasPreviousData = analysisPreviousUserScores && analysisPreviousUserScores.length > 0;

      const chartIndicatorData = hasData ? 
        analysisUserScores.map(item => ({
          name: document.documentElement.lang == 'ar' ? item?.subcategory_name_ar : item?.subcategory_name_en,
          max: 10,
        })) : 
        [{ name: 'No Data', max: 10 }];

      const chartData = hasData ? 
        analysisUserScores.map(item => parseFloat(item?.weighted_score) || 0) : 
        [0];

      const previousChartData = hasPreviousData ? 
        analysisPreviousUserScores.map(item => parseFloat(item?.weighted_score) || 0) : 
        [0];

      var chart = echarts.init(document.getElementById('chart'));
      // Chart configuration
      var option = {
        tooltip: {
          trigger: 'item',
          backgroundColor: 'rgba(0, 0, 0, 0.7)',
          borderColor: '#ccc',
          zIndex: 9999,
          position: [10, 10], // Fixed position for the tooltip
          borderWidth: 1,
          textStyle: {
            color: '#fff',
            fontSize: 14,
          },
        },
        radar: {
      shape: 'circle',
      name: {
        textStyle: {
          color: config.colors.dark,
          fontSize: window.innerWidth < 768 ? 12 : 16,
        },
      },
      indicator: chartIndicatorData,
        },
        series: [{
        name: 'Analysis',
        type: 'radar',
        data: [{
          value: chartData || [0],
          name: '{{$l}}' == 'en' ? 'Current Status' : 'الحالة الحالية',
        },
        {
          value: hasPreviousData == false ? Array(chartData?.length || 1).fill(10) : previousChartData,
          name: '{{$l}}' == 'en' ? 'Goal' : 'الهدف',
        },
      ],
        }, ],
      };

      // Set the option to the chart
      chart.setOption(option);
    });
  </script>
@stop
