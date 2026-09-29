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
            'href' => '/dashboard/cycles',
            'icon' => 'ti ti-bookmark',
            'text' => __('admin.Cycles'),
            'count' => $cycles_count,
            'color' => 'success',
        ],
        [
            'href' => '/dashboard/companies',
            'icon' => 'ti ti-briefcase',
            'text' => __('admin.Subscribe Startups'),
            'count' => $startups,
            'color' => 'warning',
        ],
        [
            'href' => '/dashboard/show-builder',
            'icon' => 'ti ti-building',
            'text' => __('admin.Forms'),
            'count' => $forms_count,
            'color' => 'secondary',
        ],
        [
            'href' => '/dashboard/inbox',
            'icon' => 'ti ti-message-circle',
            'text' => __('admin.New messages'),
            'count' => $messages,
            'color' => 'dark',
        ],
        [
            'href' => '/dashboard/mentorship/sessions?show=all',
            'icon' => 'ti ti-book',
            'text' => __('admin.Mentorship requests'),
            'count' => $mentrequests,
            'color' => 'danger',
        ],
        [
            'href' => '/dashboard/mentorship/sessions?show=all',
            'icon' => 'ti ti-clock-hour-5',
            'text' => __('admin.Mentorship requests Duration'),
            'count' => $mentrequests_period,
            'color' => 'info',
        ],
        [
            'href' => '/dashboard/training/sessions',
            'icon' => 'ti ti-calendar',
            'text' => __('admin.Training sessions'),
            'count' => $sessions,
            'color' => 'danger',
        ],
        [
            'href' => '/dashboard/training/sessions',
            'icon' => 'ti ti-clock-hour-5',
            'text' => __('admin.Training sessions Duration'),
            'count' => $sessions_period,
            'color' => 'muted',
        ],
    ];

    $cyclesHeadArr = [__('cycles.Title'), __('cycles.Valid from'), __('cycles.Valid to'), __('admin.Startups')];

    $requestsHeadArr = [
        __('cycles.Company'),
        __('cycles.User/Title'),
        __('cycles.Mentor'),
        __('cycles.Date/Time'),
        __('cycles.Actions'),
    ];
  @endphp

  <!-- Content wrapper -->
  <div class="">
    
    
    <div class="">
      <!-- Alert -->
      @if (session()->has('msg'))
        <div class="alert alert-danger alert-dismissible" role="alert">
          {{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      <!-- / Alert -->

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

      <!-- Mentorship Sessions Chart -->
       @if (!empty($mentorship_durations))
        <div class="col-12 mt-6">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <i class="ti ti-device-laptop ti-28px"></i>
                <h5 class="card-title mb-0 mx-2">{{ __('admin.mentorship statistic') }}</h5>
              </div>
            </div>
            <div class="card-body">
              <div id="mentorshipStatisticChart"></div>
            </div>
          </div>
        </div>
      @endif
      <!-- / Mentorship Sessions Chart -->

      <!-- Internship Sessions Chart -->
      @if(!empty($sessions_durations))
        <div class="col-12 mt-6">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <i class="ti ti-device-laptop ti-28px"></i>
                <h5 class="card-title mb-0 mx-2">{{ __('admin.Sessions statistic') }}</h5>
              </div>
            </div>
            <div class="card-body">
              <div id="internshipChart"></div>
            </div>
          </div>
        </div>
      @endif
      <!-- / Internship Sessions Chart -->

      <!-- Latest Cycles Table -->
      <div class="card mt-6">
        <h5 class="card-header" style="text-transform: capitalize">
          <i class="ti ti-circles ti-24px"></i>
          <span class="mx-2">{{ $l === 'ar' ? __('admin.Latest 5 Cycles') : 'Latest 5 cycles' }}</span>
        </h5>
        <div class="table-responsive text-nowrap">
          <table class="table">
            <thead>
              <tr>
                @foreach ($cyclesHeadArr as $header)
                  <th class="text-center">{{ $header }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach ($latest_cycles as $cycle)
                <tr>
                  <td class="text-center"> {{ $cycle->title }} </td>
                  <td class="text-center"> {{ $cycle->start_format }} </td>
                  <td class="text-center"> {{ $cycle->end_format }} </td>
                  <td class="text-center">
                    <button onclick="window.location.href='/dashboard/cycles/{{ $cycle->id }}/registered'"
                      type="button" class="btn-sm btn-label-primary rounded">
                      <i class="ti ti-user"></i>
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <!--/ Latest Cycles Table -->

      <!-- Latest Mentorship Requests Table -->
      @if (!empty($mentorshipRequests))
      <div class="card mt-6">
        <h5 class="card-header" style="text-transform: capitalize">
          <i class="ti ti-school ti-28px"></i>
          <span class="mx-2">{{ __('admin.Latest 5 Mentorship Requests') }}</span>
        </h5>
        <div class="table-responsive text-nowrap">
          <table class="table">
            <thead>
              <tr>
                @foreach ($requestsHeadArr as $header)
                  <th class="text-center">{{ $header }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach ($mentorshipRequests as $session)
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <a class="media-left" href="/dashboard/companyProfile/{{ $session->user->company->id }}">
                        <img src="{{ $session->user->company->logo_url }}" alt="avatar" 
                          class="rounded-circle me-3"
                          height="40" width="40">
                      </a>
                      <a href="/dashboard/companyProfile/{{ $session->user->company->id }}">
                        <div class="d-flex align-items-center gap-1 text-nowrap">
                          {{ $session->user->company->name }}
                        </div>
                      </a>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <a class="media-left" href="/dashboard/users/{{ $session->user->company->user->id }}/edit">
                        <img src="{{ $session->user->image_url }}" alt="avatar" 
                        class="rounded-circle me-3" height="40"
                          width="40">
                      </a>
                      <a href="/dashboard/users/{{ $session->user_id }}/edit">
                        <div class="media-body">
                          <h6 class="media-heading mb-0 text-nowrap">{{ $session->user->name }}</h6>
                          <span class="font-small-2 text-nowrap">{{ $session->user->title }}</span>
                        </div>
                      </a>
                    </div>
                  </td>
                  <td>
                    <div style="cursor: pointer;" class="chip mr-1"
                      onclick="mentor({{ $session->mentor_id }},'{{ $session->mentor->name }}','{{ $session->mentor->mrating() }}')">
                      <div class="d-flex align-items-center">
                        <div class="avatar">
                          <img class="img-fluid rounded-circle me-3" 
                          src="{{ $session->mentor->image_url }}" alt="Mentor photo"
                            height="20" width="20"
                          />
                        </div>
                        <span class="chip-text text-nowrap">
                          {{ $session->mentor->name }}
                        </span>
                      </div>
                    </div>
                  </td>
                  <td>
                    {{ $session->zoom_date }}
                    <br>({{ $session->period }}M)
                  </td>
                  <td>
                    <button title="Approve" onclick="approve({{ $session->id }},'Yes')" type="button"
                      class="btn btn-icon rounded-circle btn-label-success">
                      <i class="ti ti-check ti-24px"></i>
                    </button>
                    <button title="Dispprove" onclick="approve({{ $session->id }}, 'No')" type="button"
                      class="btn btn-icon rounded-circle btn-label-danger">
                      <i class="ti ti-x ti-24px"></i>
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <!--/ Latest Mentorship Requests Table -->
      @endif

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
              <canvas id="horizontalBarChart" style="height: 400px;"></canvas>

            </div>
          </div>
        </div>
        <!-- /Horizontal Bar Chart -->
        <!-- / Total companies by sdg chart -->
        @if (!empty($sdg_data))
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
        @endif
        <!-- / Total companies by sdg chart -->
        <!-- Pie Chart -->
        @if (!empty($usersgender))
      <div class="col-md-6 col-12 mt-6">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
             <div class="d-flex align-items-center">
              <h5 class="card-title mb-0">{{ __('admin.totalusersmf') }}</h5>
            </div>
          </div>
          <div class="card-body">
             <div id="pieChart" style="width: 100%; height: 350px;"></div>
          </div>
      </div>
      @endif
    </div>
<!-- /Pie Chart -->
        <!-- Vertical Bar Charts -->
        @if (!empty($usersgov))
          <div class="col-md-6 col-12 mt-6">
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
        @endif
        <!-- /Vertical Bar Charts -->
        <!-- Projects Pie Chart -->
        @if (!empty($project_types))
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
        @endif
        <!-- / Projects Pie Chart -->
        <!-- Vertical Bar Charts -->
        @if (!empty($development_stages))
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
        @endif
        <!-- /Vertical Bar Charts -->
        <!-- Sector Breakdown Chart -->
        @if (!empty($sector_data))
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
        @endif
        <!-- / Sector Breakdown Chart -->
        <!-- /User Distribution Chart -->
        @if (!empty($countries))
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
        @endif
        <!-- /User Distribution Chart -->
        <!-- Target Market Analysis Chart -->
        @if (!empty($target_market_data))
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
        @endif
        <!-- /Target Market Analysis Chart -->
        <!-- Project Status Over Time Chart -->
        @if (!empty($project_status))
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
        @endif
        <!-- /Project Status Over Time Chart -->
  
        <!-- Total companies by sectors -->
        {{-- <div class="col-md-6 col-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <h5 class="card-title mb-0">Total companies by sectors</h5>
              </div>
            </div>
            <div class="card-body">
              <canvas id="companyBySectorChart" class="chartjs" data-height="345"></canvas>
            </div>
          </div>
          <!-- / Total companies by sectors -->
        </div> --}}
      </div>
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

    // Project Distribution Pie Chart Data
    const projectsPieData = @json($project_types);    
    
    // Development Stage Chart Data
    const devStageData = @json($development_stages);

    // Target Market Analysis Chart Data
    const targetMarketData = @json($target_market_data);    

     // Sector Breakdown
     const sectorBreakdownData = @json($sector_data);

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

    // Mentorship chart data
    const seriesName = '({{ __('admin.hours') }})';
    const mentorshipStatisticsLabels = {!! json_encode($mentorship_labels) !!};
    const mentorshipStatisticsData = {!! json_encode($mentorship_durations) !!};

    // Internship chart data
    const internshipSeriesName = '({{ __('admin.hours') }})';
    const internshipBarChartCategories = {!! json_encode($sessions_labels) !!};
    const internshipSeriesData = {!! json_encode($sessions_durations) !!};

    // Cycle Startups Chart Data
    const cyclesStartupName = '';
    const cyclesStartupLabels = null;
    const cyclesStartupData = null;
  </script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
      const pieLabels = ['{{ __("admin.Female") }}', '{{ __("admin.Male") }}'];
      const usersGender = @json($usersgender);

      // console.log('usersGender', usersGender)

      let maleCount = 0;
      let femaleCount = 0;

      usersGender.forEach(user => {
          if (user.gender.toLowerCase() === 'male') {
              maleCount = user.total;
          } else if (user.gender.toLowerCase() === 'female') {
              femaleCount = user.total;
          }
      });

      const series = [];
      
      if (femaleCount > 0) {
          series.push(femaleCount);
      } else {
          series.push(0);
      }
      
      if (maleCount > 0) {
          series.push(maleCount);
      }

      if (document.querySelector("#pieChart")) {
          const options = {
              chart: {
                  type: "pie",
                  height: 350
              },
              labels: pieLabels,
              series: series,
              colors: ["#FF4560", "#008FFB"],
              legend: {
                  position: "bottom"
              },
              dataLabels: {
                  enabled: true,
                  formatter: function(val, opts) {
                      return val.toFixed(2) + '%'; 
                  }
              },
              responsive: [{
                  breakpoint: 480,
                  options: {
                      chart: {
                          width: 200
                      },
                      legend: {
                          position: 'bottom'
                      }
                  }
              }]
          };

          const chart = new ApexCharts(document.querySelector("#pieChart"), options);
          chart.render();
      }
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
      const usersGov = @json($usersgov); 
      const locale = "{{ app()->getLocale() }}";

      const stateLabels = usersGov.map(state => locale === 'ar' ? state.state_ar : state.state_en);
      const userCounts = usersGov.map(state => state.total);

      console.log('userCounts', userCounts)

      const generateRandomColor = () => `hsl(${Math.random() * 360}, 70%, 50%)`;
      const backgroundColors = stateLabels.map(() => generateRandomColor());

      const chartElement = document.getElementById("verticalBarChart");
      if (!chartElement) {
          console.error("Error: #verticalBarChart element not found!");
          return;
      }

      // Chart Configuration
      const ctx = chartElement.getContext("2d");

      new Chart(ctx, {
          type: "bar",
          data: {
          labels: stateLabels, 
          datasets: [{
          data: userCounts, 
          backgroundColor: backgroundColors,
          borderColor: backgroundColors.map(color => color.replace("70%", "50%")),
          borderWidth: 1,
          barThickness: 15 // Set bar width to 15px
          }]
          },
          options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
          x: {
              ticks: {
              font: {
              size: 10 
              }
              }
          },
          y: {
              beginAtZero: false,
              suggestedMin: Math.min(...userCounts) - 1,
              suggestedMax: Math.max(...userCounts) + 2,
              ticks: {
              font: {
              size: 10 
              },
              stepSize: 2,
              }
          }
          },
          plugins: {
          legend: {
              display: false
          }
          }
          }
      });
  });
</script>

<script>

document.addEventListener("DOMContentLoaded", function() {
    var ctx = document.getElementById("horizontalBarChart");

    if (ctx) {
        var cyclesLabels = @json($cycles_labels);
        var cyclesStartupsCount = @json($cycles_startups_count);

        new Chart(ctx.getContext("2d"), {
            type: 'bar',
            data: {
                labels: cyclesLabels,
                datasets: [{
                    data: cyclesStartupsCount,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    barThickness: 25
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    y: {
                        ticks: {
                            autoSkip: false,
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            }
        });
    } else {
        console.error("Canvas with ID 'horizontalBarChart' not found.");
    }
});


</script>



  
  <script src="/vuexy/assets/js/app-admin-dashboard.js"></script>
  <script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>
  <!-- <script src="/vuexy/assets/js/orgDashboard.js"></script> -->

  <script>
    function approve(id, stat) {
      // prompt('Please Wait ! Sending Request ........')
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        url: "/dashboard/mentors/modify",
        data: {
          "id": id,
          "stat": stat,
          'rtype': 6,
        },
        success: function(response) {
          alert(response);
          // location.reload();
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }
  </script>
@stop
