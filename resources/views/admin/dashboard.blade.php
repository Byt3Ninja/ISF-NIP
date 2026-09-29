@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/apex-charts/apex-charts.css" />
@stop

@section('style')
<link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/app-logistics-dashboard.css" />
@stop

@section('content')
@php
$countCards = [
[
'color' => 'success',
'href' => '/dashboard/users#search=Manager',
'icon' => 'ti ti-user-share',
'text' => __('admin.Managers'),
'count' => $managers_count,
],
[
'color' => 'warning',
'href' => '/dashboard/organizations',
'icon' => 'ti-businessplan',
'text' => __('admin.Organizations'),
'count' => $organizations_count,
],
[
'color' => 'secondary',
'href' => '/dashboard/requests/all',
'icon' => 'ti-speakerphone',
'text' => __('admin.Services Requests'),
'count' => $servrequests_count,
],
[
'color' => 'success',
'href' => '/dashboard/inbox',
'icon' => 'ti-message',
'text' => __('admin.New messages'),
'count' => $messages_count,
],
[
'color' => 'danger',
'href' => '/dashboard/mymsg',
'icon' => 'ti-network',
'text' => __('admin.Contact us'),
'count' => $cmessages_count,
],
[
'color' => 'danger',
'href' => '/dashboard/cycles',
'icon' => 'ti-circles',
'text' => __('admin.Cycles'),
'count' => $cycles_count,
],
[
'color' => 'info',
'href' => '/dashboard/companies',
'icon' => 'ti-building',
'text' => __('admin.Startups'),
'count' => $startups_count,
],
[
'color' => 'danger',
'href' => '/dashboard/users#search=Mentor',
'icon' => 'ti-school',
'text' => __('admin.Mentors'),
'count' => $mentors_count,
],
[
'color' => 'secondary',
'href' => '/dashboard/mentorship/sessions',
'icon' => 'ti-brand-my-oppo',
'text' => __('admin.Mentorship requests'),
'count' => $mentrequests_count,
],
[
'color' => 'success',
'href' => '/dashboard/mentorship/sessions',
'icon' => 'ti-calendar-event',
'text' => __('admin.Mentorship requests Duration'),
'count' => $mentrequests_period,
],
[
'color' => 'primary',
'href' => '/dashboard/users#search=Judger',
'icon' => 'ti-certificate',
'text' => __('admin.Judgers'),
'count' => $judgers_count,
],
[
'color' => 'warning',
'href' => '/dashboard/training/sessions',
'icon' => 'ti-camera-question',
'text' => __('admin.Training sessions'),
'count' => $sessions_count,
],
[
'color' => 'info',
'href' => '/dashboard/training/sessions',
'icon' => 'ti-alarm',
'text' => __('admin.Training sessions Duration'),
'count' => $sessions_period,
],
// [
// 'color' => 'primary',
// 'href' => '#',
// 'icon' => 'ti-briefcase',
// 'text' => 'Total Company Jobs created (Total Team members)',
// 'count' => $total_jobs_created['total'],
// ],
// [
// 'color' => 'primary',
// 'href' => '#',
// 'icon' => 'ti-crown',
// 'text' => 'Total company Founders',
// 'count' => $total_founders['total'],
// ],
// [
// 'color' => 'success',
// 'href' => '#',
// 'icon' => 'ti-report-analytics',
// 'text' => 'Total company by SDG',
// 'count' => 21,
// ],
// [
// 'color' => 'info',
// 'href' => '#',
// 'icon' => 'ti-building-skyscraper',
// 'text' => 'Admin - total Number of Startup Per organisation',
// 'count' => $organization_counts,
// ],
// [
// 'color' => 'danger',
// 'href' => '#',
// 'icon' => 'ti-coin',
// 'text' => 'Total raised funds',
// 'count' => $total_raised_funds['total'],
// ],
];
@endphp

<!-- Content wrapper -->
<div class="">
  <div class="">
    <div class="content-body">
      @if (session()->has('msg'))
      <div class="row">
        <div class="col-md-12">
          <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2"
            role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="d-flex align-items-center">
              <i class="bx bx-{{ session()->get('class') == 'success' ? 'like' : 'error' }}"></i>
              <span>
                {{ __('admin.' . session()->get('msg')) }}
                {{ Session::forget('msg') }}
              </span>
            </div>
          </div>
        </div>
      </div>
      @endif
    </div>
    <div class="row g-6">
      <!-- Count cards -->
      @foreach ($countCards as $card)
      <a href="{{ $card['href'] }}" class="col-lg-3 col-sm-6">
        <div class="card card-border-shadow-{{ $card['color'] }} h-100">
          <div class="card-body">
            <div class="d-flex justify-content-center align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="ti {{ $card['icon'] }} text-{{ $card['color'] }} ti-28px"></i>
                </span>
              </div>
              <h4 class="mb-0 text-center">{{ $card['count'] }}</h4>
            </div>
            <p class="mb-1 text-center">{{ $card['text'] }}</p>
          </div>
        </div>
      </a>
      @endforeach
      <!-- / Count cards -->
    </div>

    <div class="row mt-6">
      <!-- Horizontal Bar Chart -->
      <div class="col-12 mt-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0 mx-2">{{ __('admin.Cycle Startups Statistics') }}</h5>
            </div>
          </div>
          <div class="card-body">
            <div id="horizontalBarChart"></div>
          </div>
        </div>
      </div>
      <!-- /Horizontal Bar Chart -->
      <!-- / Total companies by sdg chart -->
      <div class="col-12 mt-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0">{{__('admin.Total_company_by_SDG')}}</h5>
            </div>
          </div>
          <div class="card-body">
            <canvas id="sdgCompaniesChart" class="chartjs" data-height="345"></canvas>
          </div>
        </div>
      </div>
      <!-- Vertical Bar Charts -->
      <div class="col-12 mt-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0 mx-2">{{ __('admin.usersdetails') }}</h5>
            </div>
          </div>
          <div class="card-body">
            <canvas id="verticalBarChart" class="chartjs" data-height="345"></canvas>
          </div>
        </div>
      </div>
       <!-- Total companies by sectors -->
       <div class="col-12 mt-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0">{{__('admin.Total_companies_by_sector')}}</h5>
            </div>
          </div>
          <div class="card-body">
            <canvas id="companyBySectorChart" class="chartjs" data-height="345"></canvas>
          </div>
        </div>
      </div>
      <!-- / Total companies by sectors -->
      <!-- /Vertical Bar Charts -->
      <!-- / Total companies by sdg chart -->
      <!-- Pie Chart -->
      <div class="col-md-6 col-12 mt-6">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0">{{ __('admin.totalusersmf') }}</h5>
            </div>
          </div>
          <div class="card-body">
            <div id="pieChart"></div>
          </div>
        </div>
      </div>
      <!-- /Pie Chart -->
      
      <!-- Projects Pie Chart -->
      <div class="col-md-6 col-12 mt-6">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0">{{__('admin.company_business_model')}}</h5>
            </div>
          </div>
          <div class="card-body">
            <div id="projectsPieChart"></div>
          </div>
        </div>
      </div>
      <!-- / Projects Pie Chart -->
      <!-- Vertical Bar Charts -->
      <div class="col-md-6 col-12 mt-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0">{{__('admin.Development_Stage')}}</h5>
            </div>
          </div>
          <div class="card-body">
            <canvas id="devStageStackedBarChart" class="chartjs" data-height="345"></canvas>
          </div>
        </div>
      </div>
      <!-- /Vertical Bar Charts -->
      <!-- Sector Breakdown Chart -->
      <div class="col-md-6 mt-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0">{{__('admin.Sector_Breakdown')}} </h5>
            </div>
          </div>
          <div class="card-body">
            <div id="sectorBreakdownChart"></div>
          </div>
        </div>
      </div>
      <!-- / Sector Breakdown Chart -->
      <!-- /User Distribution Chart -->
      <div class="col-md-6 mt-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0">{{__('admin.User_distribution_by_country')}}</h5>
            </div>
          </div>
          <div class="card-body">
            <canvas id="userDistributionChart" class="chartjs" data-height="345"></canvas>
          </div>
        </div>
      </div>
      <!-- /User Distribution Chart -->
      <!-- Target Market Analysis Chart -->
      <div class="col-md-6 mt-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <h5 class="card-title mb-0">{{__('admin.Target_Market_Analysis')}}</h5>
            </div>
          </div>
          <div class="card-body">
            <canvas id="targetMarketChart" class="chartjs" data-height="345"></canvas>
          </div>
        </div>
      </div>
      <!-- /Target Market Analysis Chart -->
      <!-- Project Status Over Time Chart -->
        <div class="col-12 col-md-6 mt-6">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <h5 class="card-title mb-0">{{__('admin.Project_Status_Over_Time')}}</h5>
              </div>
            </div>
            <div class="card-body">
              <div id="projectStatusOverTimeChart"></div>
            </div>
          </div>
        </div>
      <!-- /Project Status Over Time Chart -->

      {{-- <div class="row">
       
      </div> --}}
    </div>
  </div>
  @stop

  @section('scripts')
  <script src="/vuexy/assets/vendor/libs/apex-charts/apexcharts.js"></script>
  <script src="/vuexy/assets/vendor/libs/chartjs/chartjs.js"></script>
  @stop

  @section('pagescripts')
  <script>
    // User distribution by country
    const userDistributionData = @json($countries);

    // Sector Breakdown Chart Data
    const sectorBreakdownData = @json($sector_data);

    // Project Distribution Pie Chart Data
    const projectsPieData = @json($project_types);

    // Development Stage Chart Data
    const devStageData = @json($development_stages);

    // Target Market Analysis Chart Data
    const targetMarketData = @json($target_market_data);

    // Project Status Over Time Chart Data
    const projectStatusData = @json($project_status);

    // Total jobs created
    const totalJobsCreated = @json($total_jobs_created);

    // Total Founders
    const totalFounders = @json($total_founders);

    // Companies by SDG Chart Data
    const sdgData = @json($sdg_data);

    // Companies by sector
    const companiesBySectorData = @json($sector_distribution);

    // Horizontal Bar Chart
    const seriesName = '{{ __('admin.Registered') }}';
    const barChartCategories = {!! json_encode($cycles_labels) !!};
    const seriesData = {!! json_encode($cycles_startups_count) !!};

    // Vertical Bar Chart 
    const verticalBarLabels = {!! json_encode($usersgov->pluck('state_' . $l)) !!};
    const verticalBarData = {!! json_encode($usersgov->pluck('total')) !!};

    // Pie Chart
    const pieLabels = ['{{ __('admin.Female') }}', '{{ __('admin.Male') }}'];
    const pieData = {!! json_encode($usersgender->pluck('total')) !!};

    const internshipSeriesName = '';
    const mentorshipStatisticsLabels = null;
    const mentorshipStatisticsData = null;
    const internshipBarChartCategories = null;
    const internshipSeriesData = null;
    const cyclesStartupName = '';
    const cyclesStartupLabels = null;
    const cyclesStartupData = null;
  </script>
  <script src="/vuexy/assets/js/app-admin-dashboard.js"></script>
  <script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>
  <script src="/vuexy/assets/js/charts-apex.js"></script>
  <script src="/vuexy/assets/js/charts-chartjs.js"></script>
  @stop

  {{-- @section('pagescripts')
  <script>
    // Horizontal Bar Chart
    const seriesName = '{{ __('admin.Registered') }}';
    const barChartCategories = {!! json_encode($cycles_labels) !!};
    const seriesData = {!! json_encode($cycles_startups_count) !!};

    // Vertical Bar Chart 
    const verticalBarLabels = {!! json_encode($usersgov->pluck('state_' . $l)) !!};
    const verticalBarData = {!! json_encode($usersgov->pluck('total')) !!};

    // Pie Chart
    const pieLabels = ['{{ __('admin.Female') }}', '{{ __('admin.Male') }}'];
    const pieData = {!! json_encode($usersgender->pluck('total')) !!};
    
    // // Target Market Analysis
    // $targetMarket = Company::select('targeted_market', DB::raw('count(*) as frequency'))
    //     ->groupBy('targeted_market')
    //     ->orderByDesc('frequency')
    //     ->get();
    // $data['target_market_labels'] = $targetMarket->pluck('targeted_market');
    // $data['target_market_counts'] = $targetMarket->pluck('frequency');

    const sectorBreakdownLabels = @json($sector_labels);
    const sectorBreakdownLabels = '{{ $sector_labels }}'
    const sctorBreakdownData = @json($sector_counts);

    console.log('targetMarketLabels', targetMarketLabels);
    console.log('targetMarketData', targetMarketData);


    (() => {
      // Projects Pie Chart data and labels
      const projectsPieLabels = ['users', 'companies'];
      const projectsPieData = [30, 70];
      const legendColor = '#212529';

      // Projects Pie Chart
      
      // Development Stage Funnel Chart data and labels
      const devStageFunnelLabels = ['Idea', 'Prototype', 'MVP', 'Scaling', 'Established'];
      const devStageFunnelData = [100, 80, 60, 40, 20];

      // Development Stage Funnel Chart
      const devStageStackedBarChartEl = document.querySelector('#devStageStackedBarChart');
      
      var devStageChartConfig = {
          series: [{
          name: 'Marine Sprite',
          data: [44, 55, 41, 37, 22, 43, 21]
        }, {
          name: 'Striking Calf',
          data: [53, 32, 33, 52, 13, 43, 32]
        }, {
          name: 'Tank Picture',
          data: [12, 17, 11, 9, 15, 11, 20]
        }, {
          name: 'Bucket Slope',
          data: [9, 7, 5, 8, 6, 9, 4]
        }, {
          name: 'Reborn Kid',
          data: [25, 12, 19, 32, 25, 24, 10]
        }],
          chart: {
          type: 'bar',
          height: 350,
          stacked: true,
        },
        plotOptions: {
          bar: {
            horizontal: true,
            dataLabels: {
              total: {
                enabled: true,
                offsetX: 0,
                style: {
                  fontSize: '13px',
                  fontWeight: 900
                }
              }
            }
          },
        },
        stroke: {
          width: 1,
          colors: [isDarkStyle ? '#fff' : '#000']
        },
        title: {
          text: 'Fiction Books Sales'
        },
        xaxis: {
          categories: [2008, 2009, 2010, 2011, 2012, 2013, 2014],
          labels: {
            formatter: function (val) {
              return val + "K"
            }
          }
        },
        yaxis: {
          title: {
            text: undefined
          },
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + "K"
            }
          }
        },
        fill: {
          opacity: 1
        },
        legend: {
          position: 'top',
          horizontalAlign: 'left',
          offsetX: 40
        }
        };

        if (typeof devStageStackedBarChartEl !== undefined && devStageStackedBarChartEl !== null) {
          const projectsPieChart = new ApexCharts(devStageStackedBarChartEl, devStageChartConfig);
          projectsPieChart.render();
        }

      // Target Market Analysis Chart data and labels
      const targetMarketLabels = ['Market A', 'Market B', 'Market C', 'Market D'];
      const targetMarketData = [30, 40, 20, 10];

      // Target Market Analysis Chart
      const targetMarketChartEl = document.querySelector('#targetMarketChart');
      const targetMarketChartConfig = {
        chart: {height: 380, type: 'bar'},
        labels: targetMarketLabels,
        series: [{data: targetMarketData}],
        colors: ['#1E90FF'],
        stroke: {show: true, width: 2, colors: ['#fff']},
        legend: {
          show: true,
          position: 'bottom',
          markers: { offsetX: -3 },
          itemMargin: {vertical: 3, horizontal: 10},
          labels: {colors: isDarkStyle ? '#c0c0c0' : '#515365', useSeriesColors: false}
        },
        plotOptions: {
          bar: {
        horizontal: false,
        columnWidth: '20%', // Decrease bar width
        dataLabels: {
          total: {
            enabled: true,
            offsetX: 0,
            style: {
          fontSize: '13px',
          fontWeight: 900
            }
          }
        }
          },
        },
        xaxis: {
          categories: targetMarketLabels,
        },
        yaxis: {
          title: {
        text: 'Frequency'
          },
        },
        tooltip: {
          y: {
        formatter: function (val) {
          return val + " times"
        }
          }
        },
        fill: {
          opacity: 1
        },
        legend: {
          position: 'top',
          horizontalAlign: 'left',
          offsetX: 40
        }
      };

      if (typeof targetMarketChartEl !== undefined && targetMarketChartEl !== null) {
        const targetMarketChart = new ApexCharts(targetMarketChartEl, targetMarketChartConfig);
        targetMarketChart.render();
      }

      // Sector Breakdown Chart data and labels
      const sectorBreakdownLabels = ['Software Development', 'Marketing', 'Finance', 'Healthcare'];
      const sectorBreakdownData = [40, 30, 20, 10];

      // Sector Breakdown Chart
      const sectorBreakdownChartEl = document.querySelector('#sectorBreakdownChart');
      const sectorBreakdownChartConfig = {
        chart: {height: 380, type: 'donut'},
        labels: sectorBreakdownLabels,
        series: sectorBreakdownData,
        colors: ['#FF5733', '#33FF57', '#3357FF', '#FF33A1'],
        stroke: {show: true, width: 2, colors: ['#fff']},
        legend: {
          show: true,
          position: 'bottom',
          markers: { offsetX: -3 },
          itemMargin: {vertical: 3, horizontal: 10},
          labels: {colors: isDarkStyle ? '#c0c0c0' : '#515365', useSeriesColors: false}
        },
        plotOptions: {
          pie: {
            donut: {
              size: '75%'
            }
          }
        }
      };

      if (typeof sectorBreakdownChartEl !== undefined && sectorBreakdownChartEl !== null) {
        const sectorBreakdownChart = new ApexCharts(sectorBreakdownChartEl, sectorBreakdownChartConfig);
        sectorBreakdownChart.render();
      }

      // Project Status Over Time Chart data and labels
      const projectStatusLabels = ['January', 'February', 'March', 'April', 'May', 'June', 'July'];
      const projectStatusData = [10, 20, 15, 25, 30, 35, 40];

      // Project Status Over Time curve Chart
      const projectStatusChartEl = document.querySelector('#projectStatusOverTimeChart');
      const projectStatusChartConfig = {
      chart: {
        height: 400,
        type: 'line',
        parentHeightOffset: 0,
        zoom: {
          enabled: false
        },
        toolbar: {
          show: false
        }
      },
      series: [
        {
          data: [280, 200, 220, 180, 270, 250, 70, 90, 200, 150, 160, 100, 150, 100, 50]
        }
      ],
      markers: {
        strokeWidth: 7,
        strokeOpacity: 1,
        strokeColors: [isDarkStyle ? '#fff' : '#000'],
        colors: [config.colors.warning]
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'straight'
      },
      colors: [config.colors.warning],
      grid: {
        borderColor: isDarkStyle ? '#555' : '#ddd',
        xaxis: {
          lines: {
            show: true
          }
        },
        padding: {
          top: -20
        }
      },
      tooltip: {
        custom: function ({ series, seriesIndex, dataPointIndex, w }) {
          return '<div class="px-3 py-2">' + '<span>' + series[seriesIndex][dataPointIndex] + '%</span>' + '</div>';
        }
      },
      xaxis: {
        categories: [
          '7/12',
          '8/12',
          '9/12',
          '10/12',
          '11/12',
          '12/12',
          '13/12',
          '14/12',
          '15/12',
          '16/12',
          '17/12',
          '18/12',
          '19/12',
          '20/12',
          '21/12'
        ],
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        },
        labels: {
          style: {
            colors: isDarkStyle ? '#c0c0c0' : '#515365',
            fontSize: '13px'
          }
        }
      },
      yaxis: {
        labels: {
          style: {
            colors: isDarkStyle ? '#c0c0c0' : '#515365',
            fontSize: '13px'
          }
        }
      }
    };
    
      if (typeof projectStatusChartEl !== undefined && projectStatusChartEl !== null) {
        const projectStatusChart = new ApexCharts(projectStatusChartEl, projectStatusChartConfig);
        projectStatusChart.render();
      }
    })();
  </script>

  <script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>
  <script src="/vuexy/assets/js/charts-apex.js"></script>
  <script src="/vuexy/assets/js/charts-chartjs.js"></script>
  <script src="/vuexy/assets/js/app-admin-dashboard.js"></script>
  @stop --}}