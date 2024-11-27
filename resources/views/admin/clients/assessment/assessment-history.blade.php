@extends('layouts.admin')

@section('style')
<style>
  .rounded,
  .card,
  .nav-link,
  .btn {
    border-radius: 1rem !important;
  }

  .history-card {
    border: 1px solid #dfdfe3;
  }

  .history-card:hover {
    background-color: #F2F1F3;
    border: 1px solid #7367f0;
  }
  .history-card:hover .badge.bg-label-secondary {
    background-color: #fff !important;
  }

  .radius-10 {
    border-radius: 10px !important;
  }

  .text-sm {
    font-size: 0.85rem;
  }

  .text-xs {
    font-size: 0.65rem;
  }
  .history-chart {
    margin-top: 0;
  }
  @media screen and (min-width: 768px) {
    .history-chart {
      margin-top: -6rem;
    }
  }
</style>
@stop

@php
$pills = ['analysis-assessment', 'question-assessment', 'history'];
$mobilePills = ['analysis', 'questions', 'history'];

$assessments = [
['title' => 'business-concept', 'progress' => 50, 'status' => 'in-progress'],
['title' => 'organization', 'progress' => 75, 'status' => 'in-progress'],
['title' => 'operations', 'progress' => 100, 'status' => 'completed'],
['title' => 'customer-relation', 'progress' => 20, 'status' => 'not-started'],
];
@endphp

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <!-- Pills (Mobile) -->
    <div class="row mb-4 d-block d-md-none">
      <div class="col-12">
        <ul class="nav nav-pills">
          @foreach($mobilePills as $key => $pill)
          <li class="nav-item me-1">
            <a href="/dashboard/{{ $pill }}" class="nav-link {{ $key == 2 ? 'active' : '' }}">
              <span>{{ $pill }}</span>
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
            @foreach($pills as $key => $pill)
            <li class="nav-item me-1">
              <a href="/dashboard/{{ $key == 2 ? 'history-assessment' : $pill }}"
                class="nav-link {{ $key == 2 ? 'active' : '' }}">
                <span class="fs-6">{{ str_replace('-', ' ', $pill) }}</span>
              </a>
            </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
    <!-- / Pills -->

    <!-- Assessment History -->
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h6 class="text-secondary">History</h6>
            <div class="row">
              <div class="col-12 col-md-10">
                <div class="card card-body history-card mb-4">
                  <div class="row">
                    <div class="col-12 col-md-8">
                      <div class="d-flex flex-column gap-3">
                        <div class="d-flex flex-column flex-md-row gap-2">
                          <h6 class="text-sm text-muted mb-0 text-nowrap">Overall Recommendation</h6>
                          <div class="badge bg-label-secondary text-dark radius-10 py-2 fs-6">
                            Good progress, but room for scaling
                          </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                          <h6 class="text-sm text-muted mb-0">Overall Weighted Score</h6>
                          <div class="badge bg-label-info text-info radius-10 fs-6">
                            980
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-12 col-md-4">
                      <div class="d-flex align-items-center justify-content-between h-100 gap-2 mt-2">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                          <span class="text-xs text-dark">Create</span>
                          <div class="badge bg-label-secondary radius-10 date">
                            <i class="ti ti-calendar ti-18px text-danger"></i>
                            <span class="fw-semibold text-xs text-dark mx-2">2024-03-01</span>
                          </div>
                        </div>
                        <button class="btn btn-sm btn-primary">Back</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-10">
                <div class="card card-body history-card mb-4">
                  <div class="row">
                    <div class="col-12 col-md-8">
                      <div class="d-flex flex-column gap-3">
                        <div class="d-flex flex-column flex-md-row gap-2">
                          <h6 class="text-sm text-muted mb-0 text-nowrap">Overall Recommendation</h6>
                          <div class="badge bg-label-secondary text-dark radius-10 py-2 fs-6">
                            Good progress, but room for scaling
                          </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                          <h6 class="text-sm text-muted mb-0">Overall Weighted Score</h6>
                          <div class="badge bg-label-info text-info radius-10 fs-6">
                            980
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-12 col-md-4">
                      <div class="d-flex align-items-center justify-content-end h-100 gap-2 mt-2">
                        <span class="text-xs text-dark">Update</span>
                        <div class="badge bg-label-secondary radius-10 date">
                          <i class="ti ti-calendar ti-18px text-info"></i>
                          <span class="fw-semibold text-xs text-dark mx-2">2024-03-01</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-2">
                <!-- Assessment History Chart -->
                <div class="history-chart">
                  <div class="card">
                    <div class="card-header pb-0">
                      <h6 class="mb-0 card-title">Compare</h6>
                      <span class="text-muted" style="font-size: 10px;">2024-3-4 / 2024-9-6</span>
                    </div>
                    <div class="card-body px-0">
                      <div id="historyChart"></div>
                    </div>
                  </div>
                </div>
                <!-- / Assessment History Chart -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
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
    const historyChartEl = document.querySelector('#historyChart'),
      historyChartConfig = {
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
            data: [400, 200, 650, 500]
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
          enabled: false
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

    if (typeof historyChartEl !== undefined && historyChartEl !== null) {
      const chart = new ApexCharts(historyChartEl, historyChartConfig);
      chart.render();
    }
  });
</script>
@stop