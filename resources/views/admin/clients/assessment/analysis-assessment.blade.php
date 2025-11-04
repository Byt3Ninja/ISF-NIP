@extends('layouts.admin')
@section('style')
  <style>
    .rounded,
    .card,
    .nav-link {
      border-radius: 1rem !important;
    }

    .radius-15 {
      border-radius: 15px !important;
    }

    .radius-10 {
      border-radius: 10px !important;
    }

    .radius-5 {
      border-radius: 5px !important;
    }

    .text-sm {
      font-size: 0.85rem;
    }

    .text-xs {
      font-size: 0.65rem;
    }

    .light-shadow {
      box-shadow: 0px 2px 0px 0px #F2F1F3;
    }

    #chart {
      width: 100%;
      height: 500px;
    }
  </style>
@stop

@php
  $pills = ['analysis-assessment', 'question-assessment', 'history'];
  $mobilePills = ['analysis', 'questions', 'history'];

  $userid = auth()->user()->id;
  $assessment = App\Assessment::where('user_id', $userid)->orderBy('id', 'desc')->first();

  $assessment_user = App\Assessment::where('user_id', $userid)->whereNull('end_date')->first();

  if ($assessment != null) {
      $scoresTotal = App\UserScore::where('user_id', $userid)->where('assessment_id', $assessment->id)->get();

      $scoresTotals = $scoresTotal->sum('weighted_score');
  } else {
      $scoresTotals = 0;
  }

@endphp

@section('content')
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <!-- Alert -->
      @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible" role="alert">
          {{ __('admin.' . session()->get('message')) }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
            <ul class="nav nav-pills flex-nowrap px-2 py-3">
              @foreach ($pills as $key => $pill)
                <li class="nav-item me-1">
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
            <div class="w-100 d-flex flex-column gap-3 p-4">
              <div class="d-flex flex-grow-1 justify-content-between align-items-center">
                <h4 class="mb-0">{{ __('admin.Analysis_Assessment') }}</h4>
                <div class="d-flex align-items-center gap-2">
                  <span class="text-xs text-secondary">{{ __('admin.Created') }}</span>
                  <div class="badge bg-label-secondary radius-10">
                    <i class="ti ti-calendar ti-18px text-info"></i>
                    <span
                      class="fw-semibold text-xs text-dark mx-2">{{ $assessment == null ? 'None' : $assessment->created_at }}</span>
                  </div>
                </div>
              </div>
              <div
                class="d-flex flex-column flex-md-row gap-2 flex-grow-1 justify-content-between align-items-md-center mb-2">
                <div class="d-flex flex-column flex-md-row gap-3">
                  <h6 class="text-sm text-muted mb-0 text-nowrap">{{ __('admin.Overall_Recommendation') }}</h6>
                  <div class="badge bg-label-secondary text-dark radius-10 py-2 fs-6">
                      {{ __('admin.Good_progress_but_room_for_scaling') }}
                  </div>
                </div>
                <div class="w-100 d-flex flex-grow-1 justify-content-start justify-content-md-end align-items-center">

                  @if (!$assessment_user)
                    <a href="{{ route('assessments.assign') }}"
                      class="btn btn-sm btn-primary light-shadow radius-10">
                      {{ __('admin.Make_a_new_assessment') }}
                    </a>
                  @else
                    <a class="bg-light hover-none radius-10 disabled px-4 py-2" style="cursor: not-allowed !important;">
                      {{ __('admin.Make_a_new_assessment') }}
                    </a>
                  @endif

                  @if ($assessment && $assessment->end_date != null)
                    <a href="{{ route('assessments.results_export_pdf') }}" class="btn btn-sm btn-primary light-shadow radius-10 ms-2">
                      {{ __('admin.Export_assessment_pdf') }}
                    </a>
                  @else
                    <a class="bg-light hover-none radius-10 disabled px-4 py-2 ms-2"
                      style="cursor: not-allowed !important;">
                      {{ __('admin.Export_assessment_pdf') }}
                    </a>
                  @endif
                </div>
              </div>
              <div class="d-flex flex-grow-1 align-items-center gap-3">
                <h6 class="text-sm text-muted mb-0">{{ __('admin.Overall_Weighted_Score') }}</h6>
                <div class="badge bg-label-info text-dark radius-10">
                  {{ $scoresTotals > 0 ? $scoresTotals : 0 }}
                </div>
              </div>

              <div class="row">
                @php
                  $colors = ['warning', 'success', 'danger', 'info', 'primary', 'secondary']; // مصفوفة الألوان
                @endphp
                @foreach ($categories as $key => $phase)
                  @php
                    $color = $colors[$key % count($colors)]; // تعيين لون بناءً على المفتاح
                    $userid = auth()->user()->id;

                    if ($assessment != null) {
                        $scoresTotal = App\UserScore::with(['category', 'subcategory', 'stage', 'assessment'])
                            ->where('user_id', $userid)
                            ->where('assessment_id', $assessment->id)
                            ->where('category_id', $phase->id)
                            ->get();
                        $scores = $scoresTotal->sum('weighted_score');

                        if (!empty($scoresTotal)) {
                            // إجمالي عدد الأسئلة
                            $totalQuestions = $scoresTotal->count();
                            // عدد الأسئلة المحلولة (التي ليست null)
                            $answeredQuestions = $scoresTotal->whereNotNull('Result_assessment')->count();
                            // حساب النسبة المئوية
                            $percentage = $totalQuestions > 0 ? ($answeredQuestions / $totalQuestions) * 100 : 0;
                        } else {
                            $percentage = 0;
                        }
                    } else {
                        $scores = 0;
                        $percentage = 0;
                    }

                  @endphp

                  <div class="col-12 col-md-3 mb-2">
                    <div class="card shadow-sm p-4">
                      <div class="badge bg-label-{{ $color }} mb-3 radius-10">
                        <h6 class="mb-0 text-{{ $color }} text-uppercase">{{ $phase->{'category_name_' . $l} }}
                        </h6>
                      </div>
                      <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ number_format($scores, 2) }}%</h4>
                        <div class="d-flex flex-column align-items-center gap-1">
                          <span class="text-xs text-secodnary">{{ __('admin.Score') }}</span>
                          {{-- <div class="badge bg-label-info text-xs radius-10">
                        {{ $scores }}
                      </div> --}}
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
            $userid = auth()->user()->id;
            if ($assessment != null) {
                $scoresTotalsub = App\UserScore::with(['category', 'subcategory', 'stage', 'assessment'])
                    ->where('user_id', $userid)
                    ->where('assessment_id', $assessment->id)
                    ->where('category_id', $category->id)
                    ->get();
                $scoresTotalssub = $scoresTotalsub->sum('weighted_score');

                $countanswered = $scoresTotalsub->whereNotNull('Result_assessment')->count();
                $countNotanswered = $scoresTotalsub->whereNull('Result_assessment')->count();
                $totalQuestions = $countanswered + $countNotanswered;
                $progress = $totalQuestions > 0 ? ($countanswered / $totalQuestions) * 100 : 0;
            } else {
                $scoresTotalssub = 0;
                $progress = 0;
            }

          @endphp

            <div class="col-12 col-md-4 col-lg-3 mb-4">
            <div class="card shadow-sm p-4 h-100">
              <div class="badge bg-label-secondary mb-3 radius-10">
              <h6 class="mb-0 text-dark text-uppercase">{{ $category->{'category_name_' . $l} }}</h6>
              </div>
              @foreach ($category['subcategory'] as $subcategory)
              @php
                if ($assessment != null) {
                  $scoresTotalsub = App\UserScore::with(['category', 'subcategory', 'stage', 'assessment'])
                    ->where('user_id', $userid)
                    ->where('assessment_id', $assessment->id)
                    ->where('subcategory_id', $subcategory->id)
                    ->get();
                  $scoresTotalssub = $scoresTotalsub->sum('weighted_score');

                  $countanswered = $scoresTotalsub->whereNotNull('Result_assessment')->count();
                  $countNotanswered = $scoresTotalsub->whereNull('Result_assessment')->count();
                  $totalQuestions = $countanswered + $countNotanswered;
                  $progress = $totalQuestions > 0 ? ($countanswered / $totalQuestions) * 100 : 0;
                } else {
                  $scoresTotalssub = 0;
                  $progress = 0;
                }
              @endphp
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="badge bg-label-{{ $color }} radius-10 me-2">
                <span class="text-{{ $color }} text-capitalize text-xs fw-bold">
                  {{ $subcategory->{'name_' . $l} }}
                </span>
                </div>
                <div class="d-flex align-items-center gap-1">
                <div class="d-flex flex-column align-items-center gap-1">
                  <span class="text-xs text-secodnary">{{ __('admin.Score') }}</span>
                  <div class="badge bg-label-info radius-10">
                  <span class="text-xs">{{ $scoresTotalssub }} %</span>
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
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const analysisUserScores = @json($weightedSubCategories) || [];
      console.log('analysisUserScores', analysisUserScores);
      // console.log('analysisUserScores', analysisUserScores)

      const chartIndicatorData = analysisUserScores?.map(item => ({
        name: document.documentElement.lang == 'ar' ? item?.subcategory_name_ar : item?.subcategory_name_en,
        // max: Math.ceil(item?.weighted_score || 10), // Use the ceiling of the weighted_score as the max value
        max: 10,
      }));

      const chartData = analysisUserScores?.map(item => item?.weighted_score || 0);

      // Show loading spinner
      var chart = echarts.init(document.getElementById('chart'));
      chart.showLoading();

      // Simulate async data loading
      setTimeout(() => {
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
            value: chartData,
            name: '{{$l}}' == 'en' ? 'Current Status' : 'الحالة الحالية',
          },
          {
            value: Array(chartData?.length).fill(100),
            name: 'Goal',
          },
        ],
          }, ],
        };

        // Set the option to the chart
        chart.setOption(option);

        // Hide loading spinner
        chart.hideLoading();
      }, 2000); // Simulate a 2-second data loading delay
    });
  </script>
@stop
