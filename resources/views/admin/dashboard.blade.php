@extends('layouts.admin')

@section('vendorstyle')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/apex-charts/apex-charts.css" />
@stop

@section('style')
  <link rel="stylesheet" href="../../assets/vendor/css/pages/app-logistics-dashboard.css" />
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
    ];
  @endphp

  <!-- Content wrapper -->
  <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="content-body">
        @if (session()->has('msg'))
          <div class="col-12">
            <div class="row">
              <div class="col-md-12">
                <div class="alert alert-{{ session()->get('class') }} mb-2" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
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
          </div>
        @endif
      </div>
      <div class="row g-6">

        <!-- Count cards -->
        @foreach ($countCards as $card)
          <div class="col-lg-3 col-sm-6">
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
          </div>
        @endforeach
        <!-- / Count cards -->
      </div>
    </div>
  </div>
@stop

@section('scripts')
  {{-- <script src="/app-assets/vendors/js/charts/chart.min.js"></script>
    <script src="/app-assets/vendors/js/charts/apexcharts.min.js"></script>
    <script src="/app-assets/vendors/js/extensions/swiper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
  <script src="/vuexy/assets/vendor/libs/apex-charts/apexcharts.js"></script>
@stop
@section('pagescripts')
  <script>
    < script src = "../../assets/js/app-logistics-dashboard.js" >
      <
      />
    console.log('admin dashbaord')
  </script>
  <script>
    $(window).on("load", function() {
      var $primary = '#5A8DEE',
        $success = '#39DA8A',
        $danger = '#FF5B5C',
        $warning = '#FDAC41',
        $info = '#00CFDD',
        $label_color = '#475F7B',
        grid_line_color = '#dae1e7';
      var themeColors = [$primary, $warning, $danger, $success, $info, $label_color, $primary, $warning, $danger,
        $success, $info, $label_color,
        $primary, $warning, $danger, $success, $info, $label_color, $primary, $warning, $danger, $success, $info,
        $label_color
      ];

      // Bar Chart
      // ------------------------------------------
      //Get the context of the Chart canvas element we want to select
      var barChartctx = $("#bar-chart");
      // Chart Options
      var barchartOptions = {
        // Elements options apply to all of the options unless overridden in a dataset
        // In this case, we are setting the border of each bar to be 2px wide
        elements: {
          rectangle: {
            borderWidth: 2,
            borderSkipped: 'left'
          }
        },
        responsive: true,
        maintainAspectRatio: false,
        responsiveAnimationDuration: 500,
        legend: {
          display: false
        },
        scales: {
          xAxes: [{
            display: true,
            gridLines: {
              color: grid_line_color,
            },
            scaleLabel: {
              display: true,
            }
          }],
          yAxes: [{
            display: true,
            gridLines: {
              color: grid_line_color,
            },
            scaleLabel: {
              display: true,
            },
            ticks: {
              stepSize: 10
            },
          }],
        },
        title: {
          display: true,
          text: '{{ __('admin.totalregisteredusers') }}'
        },
      };
      // Chart Data
      var barchartData = {
        labels: {!! $usersgov->pluck('state_' . $l)->toJson() !!},
        datasets: [{
          label: "Total",
          data: {!! $usersgov->pluck('total')->toJson() !!},
          backgroundColor: themeColors,
          borderColor: "transparent"
        }]
      };
      var barChartconfig = {
        type: 'bar',
        // Chart Options
        options: barchartOptions,
        data: barchartData
      };
      // Create the chart
      var barChart = new Chart(barChartctx, barChartconfig);
      // Pie Chart
      // --------------------------------
      //Get the context of the Chart canvas element we want to select
      var pieChartctx = $("#simple-pie-chart");
      // Chart Options
      var piechartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        responsiveAnimationDuration: 500,
        title: {
          display: true,
          text: '{{ __('admin.totalregisteredusers') }}'
        }
      };
      // Chart Data
      var piechartData = {
        labels: ['{{ __('admin.Female') }}', '{{ __('admin.Male') }}'],
        datasets: [{
          label: "{{ __('admin.totalregistered') }}",
          data: {!! $usersgender->pluck('total')->toJson() !!},
          backgroundColor: themeColors,
        }]
      };
      var pieChartconfig = {
        type: 'pie',
        // Chart Options
        options: piechartOptions,
        data: piechartData
      };
      // Create the chart
      var pieSimpleChart = new Chart(pieChartctx, pieChartconfig);

    });
  </script>

  <script>
    new Chart(document.getElementById('cycleStartups'), {
      type: 'bar',
      data: {
        labels: {!! json_encode($cycles_labels) !!},
        datasets: [{
          label: '({{ __('admin.Registered') }})',
          data: {!! json_encode($cycles_startups_count) !!},
          borderWidth: 1
        }]
      },
      options: {
        backgroundColor: '#f59b9b',
        indexAxis: 'y',
        elements: {
          bar: {
            borderWidth: 2,
          }
        },
        responsive: true,
        plugins: {
          legend: {
            position: 'right',
          },

        },

      }
    });
  </script>

@stop
