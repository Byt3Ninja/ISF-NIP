@extends('layouts.admin')

@section('style')
<style>
  /* Assessment history — layout tokens follow the admin theme (Bootstrap vars)
     rather than fixed hex, so the page tracks the rest of the dashboard. */
  .history-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .history-card {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    align-items: center;
    gap: 1.5rem;
    padding: 1.25rem 1.5rem;
    border: 1px solid var(--bs-border-color);
    border-radius: 0.875rem;
    background-color: var(--bs-body-bg);
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  }

  .history-card:hover {
    border-color: var(--bs-primary);
    box-shadow: 0 0.125rem 0.5rem rgba(var(--bs-primary-rgb), 0.12);
  }

  /* The score is why this page exists, so it leads the card. */
  .history-card__score {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
    min-width: 6.5rem;
    padding-inline-end: 1.5rem;
    /* border-inline-end: 1px solid var(--bs-border-color); */
  }

  .history-card__score-value {
    font-size: 1.75rem;
    font-weight: 600;
    line-height: 1.1;
    color: var(--bs-info);
    font-variant-numeric: tabular-nums;
  }

  .history-card__score-label {
    font-size: 0.6875rem;
    line-height: 1.3;
    color: var(--bs-secondary-color);
  }

  .history-card__recommendation-label {
    margin-bottom: 0.125rem;
    font-size: 0.6875rem;
    color: var(--bs-secondary-color);
  }

  .history-card__recommendation {
    margin-bottom: 0;
    font-size: 0.9375rem;
    color: var(--bs-body-color);
  }

  .history-card__meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    white-space: nowrap;
  }

  .history-card__status {
    padding: 0.25rem 0.625rem;
    border-radius: 1rem;
    font-size: 0.6875rem;
    font-weight: 500;
    color: var(--bs-warning);
    background-color: rgba(var(--bs-warning-rgb), 0.12);
  }

  .history-card__date {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    color: var(--bs-secondary-color);
  }

  .history-card__date i {
    color: var(--bs-secondary-color);
  }

  .history-card__date time {
    font-variant-numeric: tabular-nums;
    color: var(--bs-body-color);
  }

  /* Compare chart sits beside the list at desktop width and stacks below it
     on small screens. No negative margins. */
  .history-compare .card-title {
    margin-bottom: 0.125rem;
  }

  .history-compare__range {
    display: block;
    font-size: 0.6875rem;
    color: var(--bs-secondary-color);
    font-variant-numeric: tabular-nums;
  }

  @media screen and (max-width: 767.98px) {
    .history-card {
      grid-template-columns: minmax(0, 1fr);
      gap: 1rem;
    }

    .history-card__score {
      flex-direction: row;
      align-items: baseline;
      gap: 0.5rem;
      padding-inline-end: 0;
      padding-bottom: 0.875rem;
      border-inline-end: 0;
      border-bottom: 1px solid var(--bs-border-color);
    }

    .history-card__meta {
      flex-wrap: wrap;
      white-space: normal;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .history-card {
      transition: none;
    }
  }
</style>
@stop

@php
$pills = ['analysis-assessment', 'question-assessment', 'history'];
$assessment_user = $assessmentsopen->first();
$hasHistory = $assessmentsopen->isNotEmpty() || $assessments->isNotEmpty();

// $assessments = [
// ['title' => 'business-concept', 'progress' => 50, 'status' => 'in-progress'],
// ['title' => 'organization', 'progress' => 75, 'status' => 'in-progress'],
// ['title' => 'operations', 'progress' => 100, 'status' => 'completed'],
// ['title' => 'customer-relation', 'progress' => 20, 'status' => 'not-started'],
// ];
@endphp

@section('content')
<div class="">
  <div class="">
    <!-- Pills (Mobile) -->
    <div class="row mb-4 d-block d-md-none">
      <div class="col-12">
        <ul class="nav nav-pills">
          @foreach($pills as $key => $pill)
          <li class="nav-item me-1">
            @if ($pill == 'question-assessment' && !$assessment_user)
              <span class="nav-link" style="cursor: not-allowed; opacity: 0.5;"
                    title="{{ __('admin.No_Questions_Available') }}">
                <span>{{ __('admin.Question_assessment') }}</span>
              </span>
            @else
              <a href="/dashboard/{{ $key == 2 ? 'history-assessment' : $pill }}"
                class="nav-link {{ $key == 2 ? 'active' : '' }}">
                <span>
                  @if($pill == 'analysis-assessment')
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
          <ul class="nav nav-pills flex-nowrap px-2 py-3">
            @foreach($pills as $key => $pill)
            <li class="nav-item me-1">
              @if ($pill == 'question-assessment' && !$assessment_user)
                <span class="nav-link" style="cursor: not-allowed; opacity: 0.5;"
                      title="{{ __('admin.No_Questions_Available') }}">
                  <span class="fs-6">{{ __('admin.Question_assessment') }}</span>
                </span>
              @else
                <a href="/dashboard/{{ $key == 2 ? 'history-assessment' : $pill }}"
                  class="nav-link {{ $key == 2 ? 'active' : '' }}">
                  <span class="fs-6">
                    @if($pill == 'analysis-assessment')
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

    <!-- Assessment History -->
    @if(!$hasHistory)
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="ti ti-history ti-48px text-muted mb-3"></i>
            <h5 class="mb-1">{{ __('admin.No_History_Available') }}</h5>
            <p class="text-muted mb-0">{{ __('admin.No_History_Available_Hint') }}</p>
          </div>
        </div>
      </div>
    </div>
    @else
    <div class="row g-4">
      <div class="col-12 {{ $historySeries->isNotEmpty() ? 'col-lg-9' : '' }}">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h6 class="text-secondary mb-0">{{ __('admin.History') }}</h6>
              <a href="/dashboard/analysis-assessment" class="btn btn-sm btn-label-secondary">{{ __('admin.Back') }}</a>
            </div>
            <div class="history-list">
              @foreach($assessmentsopen as $assessmentopen)
                @include('admin.clients.assessment.partials.history-card', [
                  'assessment' => $assessmentopen,
                  'score' => $weightedScores[$assessmentopen->id] ?? 0,
                  'state' => 'open',
                ])
              @endforeach

              @foreach($assessments as $assessment)
                @include('admin.clients.assessment.partials.history-card', [
                  'assessment' => $assessment,
                  'score' => $weightedScores[$assessment->id] ?? 0,
                  'state' => 'done',
                ])
              @endforeach
            </div>
          </div>
        </div>
      </div>

      @if($historySeries->isNotEmpty())
      <div class="col-12 col-lg-3">
        <div class="card h-100 history-compare">
          <div class="card-header pb-0">
            <h6 class="mb-0 card-title">{{ __('admin.Compare') }}</h6>
            <span class="history-compare__range">
              {{ $historyRangeStart ? \Carbon\Carbon::parse($historyRangeStart)->format('Y-m-d') : '—' }}
              &ndash;
              {{ $historyRangeEnd ? \Carbon\Carbon::parse($historyRangeEnd)->format('Y-m-d') : '—' }}
            </span>
          </div>
          <div class="card-body px-0 d-flex align-items-end">
            <div id="historyChart" class="w-100" data-series="{{ json_encode($historySeries) }}"></div>
          </div>
        </div>
      </div>
      @endif
    </div>
    @endif
    <!-- / Assessment History -->
  </div>
</div>
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/apex-charts/apexcharts.js"></script>
@stop
@section('pagescripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // History Chart
    // --------------------------------------------------------------------
    const historyChartEl = document.getElementById('historyChart');

    if (historyChartEl === null) {
      return;
    }

    let historySeriesData = [];
    try {
      historySeriesData = JSON.parse(historyChartEl.dataset.series || '[]');
    } catch (e) {
      historySeriesData = [];
    }

    const historyChartConfig = {
        chart: {
          height: 123,
          type: 'area',
          toolbar: {
            show: false
          },
          sparkline: {
            enabled: true
          }
        },
        markers: {
          colors: 'transparent',
          strokeColors: 'transparent'
        },
        grid: {
          show: false
        },
        colors: [config.colors.success],
        fill: {
          type: 'gradient',
          gradient: {
            shade: isDarkStyle ? 'dark' : '',
            shadeIntensity: 0.8,
            opacityFrom: 0.6,
            opacityTo: 0.1
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          width: 2,
          curve: 'smooth'
        },
        series: [
          {
            name: @json(__('admin.Overall_Weighted_Score')),
            data: historySeriesData
          }
        ],
        xaxis: {
          show: true,
          lines: {
            show: false
          },
          labels: {
            show: false
          },
          stroke: {
            width: 0
          },
          axisBorder: {
            show: false
          }
        },
        yaxis: {
          stroke: {
            width: 0
          },
          show: false
        },
        tooltip: {
          enabled: true,
          x: { show: false }
        },
        responsive: [
          {
            breakpoint: 1387,
            options: {
              chart: {
                height: 80
              }
            }
          },
          {
            breakpoint: 1200,
            options: {
              chart: {
                height: 123
              }
            }
          }
        ]
      };

    const chart = new ApexCharts(historyChartEl, historyChartConfig);
    chart.render();
  });

  
</script>
@stop
