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
  <div class="content-wrapper">
    
    
    <div class="container-xxl flex-grow-1 container-p-y">
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
      <div class="col-12 mt-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <i class="ti ti-device-laptop ti-28px"></i>
              <h5 class="card-title mb-0 mx-2">{{ __('admin.mentorship statistic') }}</h5>
            </div>
          </div>
          <div class="card-body">
            <div id="horizontalBarChart"></div>
          </div>
        </div>
      </div>
      <!-- / Mentorship Sessions Chart -->

      <!-- Internship Sessions Chart -->
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
                      <a class="media-left mr-50" href="/dashboard/companyProfile/{{ $session->user->company->id }}">
                        <img src="{{ $session->user->company->logo_url }}" alt="avatar" class="rounded-circle"
                          height="40" width="40">
                      </a>
                      <a href="/dashboard/companyProfile/{{ $session->user->company->id }}">
                        <div class="d-flex align-items-center gap-1">
                          {{ $session->user->company->name }}
                        </div>
                      </a>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <a class="media-left mr-50" href="/dashboard/users/{{ $session->user->company->user->id }}/edit">
                        <img src="{{ $session->user->image_url }}" alt="avatar" class="rounded-circle" height="40"
                          width="40">
                      </a>
                      <a href="/dashboard/users/{{ $session->user_id }}/edit">
                        <div class="media-body">
                          <h6 class="media-heading mb-0">{{ $session->user->name }}</h6>
                          <span class="font-small-2">{{ $session->user->title }}</span>
                        </div>
                      </a>
                    </div>
                  </td>
                  <td>
                    <div style="cursor: pointer;" class="chip mr-1"
                      onclick="mentor({{ $session->mentor_id }},'{{ $session->mentor->name }}','{{ $session->mentor->mrating() }}')">
                      <div class="d-flex align-items-center">
                        <div class="avatar">
                          <img class="img-fluid" src="{{ $session->mentor->image_url }}" alt="Mentor photo"
                            height="20" width="20"
                          />
                        </div>
                        <span class="chip-text">
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
        <!-- Vertical Bar Charts -->
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
        <!-- /Vertical Bar Charts -->
        <!-- Projects Pie Chart -->
        <div class="col-md-6 col-12 mt-6">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <h5 class="card-title mb-0">{{__('admin.Project_Status_Over_Time')}}</h5>
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
    const barChartCategories = {!! json_encode($mentorship_labels) !!};
    const seriesData = {!! json_encode($mentorship_durations) !!};

    // Internship chart data
    const internshipSeriesName = '({{ __('admin.hours') }})';
    const internshipBarChartCategories = {!! json_encode($sessions_labels) !!};
    const internshipSeriesData = {!! json_encode($sessions_durations) !!};
  </script>
  
  <script src="/vuexy/assets/js/app-admin-dashboard.js"></script>
  <script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>
  <script src="/vuexy/assets/js/orgDashboard.js"></script>

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
          location.reload();
        },
        error: function(error) {
          console.log(error);
          alert("Error Occurred, Try again!");
        }
      });
    }
  </script>
@stop
