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

$userid = $assessment_user->user_id;
                  $assessment = App\Assessment::where('user_id', $userid)->orderBy('id', 'desc')->first();
                  
                  $assessment_user = App\Assessment::where('user_id', $userid)->first();
                  
                  if($assessment != null){
                    
                    $scoresTotal = App\UserScore::where('user_id', $userid)
                    ->where('assessment_id', $assessment->id)
                    ->get();
                    
                    $scoresTotals = $scoresTotal->sum('weighted_score');
                    
                  }else{
                    $scoresTotals = 0;
                  }
                  
@endphp

@section('content')
<div class="">
  <div class="">
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
                  <span class="fw-semibold text-xs text-dark mx-2">{{ $assessment == null ? 'None' : $assessment->created_at }}</span>
                </div>
              </div>
            </div>
            <div
              class="d-flex flex-column flex-md-row gap-2 flex-grow-1 justify-content-between align-items-md-center mb-2">
              <div class="d-flex flex-column flex-md-row gap-3">
                <h6 class="text-sm text-muted mb-0 text-nowrap">{{ __('admin.Overall_Recommendation') }}</h6>
                <div class="badge bg-label-secondary text-dark radius-10 py-2 fs-6">
                  Good progress, but room for scaling
                  
                </div>
              </div>
              @if( !empty($assessment->end_date) )
              <a href="{{ route('assessments.results_export_pdf') }}?assessment_id={{ $assessment->id }}" class="btn btn-sm btn-primary ms-3">
                {{ __('admin.Export_assessment_pdf') }}
              </a>
              @endif
              
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
              @foreach($categories as $key => $phase)
              @php
                  $color = $colors[$key % count($colors)]; // تعيين لون بناءً على المفتاح
                  $userid = $assessment_user->user_id;

                  if($assessment != null){
                    
                    $scoresTotal = App\UserScore::with(['category', 'subcategory', 'stage', 'assessment'])
                    ->where('user_id', $userid)
                    ->where('assessment_id', $assessment->id)
                    ->where('category_id', $phase->id)
                    ->get();
                    $scores = $scoresTotal->sum('weighted_score');
                    
                    if(!empty($scoresTotal)){
                      // إجمالي عدد الأسئلة
                      $totalQuestions = $scoresTotal->count();
                      // عدد الأسئلة المحلولة (التي ليست null)
                      $answeredQuestions = $scoresTotal->whereNotNull('Result_assessment')->count();
                      // حساب النسبة المئوية
                      $percentage = $totalQuestions > 0 ? ($answeredQuestions / $totalQuestions) * 100 : 0;
                    }else{
                      $percentage = 0;
                      
                    }
                  }else{
                    $scores = 0;
                    $percentage = 0;
                  }

              @endphp
              
              <div class="col-12 col-md-3 mb-2">
                <div class="card shadow-sm p-4">
                  <div class="badge bg-label-{{ $color }} mb-3 radius-10">
                    <h6 class="mb-0 text-{{ $color }} text-uppercase">{{ $phase->{'category_name_'. $l} }}</h6>
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
      @foreach($categories as $key => $category)

      @php
      $color = $colors[$key % count($colors)];
      $userid = $assessment_user->user_id;
                  if($assessment != null){
                    
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


                  }else{
                    $scoresTotalssub = 0;
                    $progress = 0;
                  }

                  

      @endphp

      <div class="col-12 col-md-4 col-lg-3 mb-2">
        <div class="card shadow-sm p-4">
          <div class="badge bg-label-secondary mb-3 radius-10">
            <h6 class="mb-0 text-dark text-uppercase">{{ $category->{'category_name_'. $l} }}</h6>
          </div>
          @foreach($category['subcategory'] as $subcategory)

          @php
                  if($assessment != null){
                    
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


                  }else{
                    $scoresTotalssub = 0;
                    $progress = 0;
                  }
          @endphp
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="badge bg-label-{{ $color }} radius-10 me-2">
              <span class="text-{{ $color }} text-capitalize text-xs fw-bold">
                {{ $subcategory->{'name_'. $l} }}
              </span>
            </div>
            <div class="d-flex align-items-center gap-1">
              <div class="d-flex flex-column align-items-center gap-1">
                <span class="text-xs text-secodnary">{{ __('admin.Score') }}</span>
                <div class="badge bg-label-info radius-10">
                  <span class="text-xs">{{ $scoresTotalssub }} %</span>
                </div>
              </div>
              {{-- <div class="text-sm fw-bold">{{ $progress }}%</div> --}}
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
<script>
  window.addEventListener('DOMContentLoaded', () => {
    // Initialize the chart
    var chart = echarts.init(document.getElementById('chart'));

    // Chart configuration
    var option = {
      // title: {
      //   text: 'Business Analysis Radar Chart',
      //   left: 'center',
      // },
      tooltip: {},
      radar: {
        shape: 'circle',
        name: {
          textStyle: {
            color: config.colors.dark,
            fontSize: 16,
          },
        },
        indicator: [
          { name: 'Business Idea', max: 100 },
          { name: 'Product Portfolio', max: 100 },
          { name: 'Revenue Model', max: 100 },
          { name: 'Customer Portfolio', max: 100 },
          { name: 'Market Position', max: 100 },
          { name: 'Networking', max: 100 },
          { name: 'Marketing', max: 100 },
          { name: 'Sales & Service', max: 100 },
          { name: 'Communication & PR', max: 100 },
          { name: 'Branding', max: 100 },
          { name: 'Financials', max: 100 },
          { name: 'Funding', max: 100 },
          { name: 'Production & Deliveries', max: 100 },
          { name: 'IT Systems', max: 100 },
          { name: 'Facilities', max: 100 },
          { name: 'Ownership & Board', max: 100 },
          { name: 'Employees', max: 100 },
          { name: 'Partnerships', max: 100 },
          { name: 'Business Processes', max: 100 },
          { name: 'Legal Issues', max: 100 },
        ],
      },
      series: [
        {
          name: 'Analysis',
          type: 'radar',
          data: [
            {
              value: [
                80, 90, 75, 60, 85, 95, 70, 80, 90, 75,
                70, 65, 85, 75, 70, 85, 60, 70, 80, 90,
              ],
              name: 'Current Status',
            },
            {
              value: [
                70, 85, 60, 75, 80, 90, 85, 90, 70, 80,
                80, 70, 75, 80, 75, 80, 70, 65, 75, 85,
              ],
              name: 'Goal',
            },
          ],
        },
      ],
    };

    // Set the option to the chart
    chart.setOption(option);
  });
</script>
<script>
  window.addEventListener('DOMContentLoaded', () => {
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

    // Show loading spinner
    var chart = echarts.init(document.getElementById('chart'));
    chart.showLoading();

    // Simulate async data loading
    setTimeout(() => {
      // Chart configuration
      var option = {
        tooltip: {},
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
        series: [
          {
            name: 'Analysis',
            type: 'radar',
            data: [
              {
                value: chartData || [0],
                name: '{{$l}}' == 'en' ? 'Current Status' : 'الحالة الحالية',
              },
              {
                value: hasPreviousData == false ? Array(chartData?.length || 1).fill(10) : previousChartData,
                name: '{{$l}}' == 'en' ? 'Goal' : 'الهدف',
              },
            ],
          },
        ],
      };

      // Set the option to the chart
      chart.setOption(option);

      // Hide loading spinner
      chart.hideLoading();
    }, 2000); // Simulate a 2-second data loading delay
  });
</script>
@stop