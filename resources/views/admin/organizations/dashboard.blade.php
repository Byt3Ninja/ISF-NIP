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
            'icon' => 'ti ti-calendar-event',
            'text' => __('admin.Mentorship requests Duration'),
            'count' => $mentrequests_period,
            'color' => 'label-info',
        ],
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

      <!-- Horizontal Bar Chart -->
      <div class="col-12 mt-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <i class="ti ti-device-laptop ti-28px"></i>
              {{-- <h5 class="card-title mb-0 mx-2">{{ __('admin.Cycle Startups Statistics') }}</h5> --}}
            </div>
          </div>
          <div class="card-body">
            {{-- <div id="horizontalBarChart"></div> --}}
          </div>
        </div>
      </div>
      <!-- /Horizontal Bar Chart -->

      <div class="row g-3 align-items-center mt-6">
        <!-- Pie Chart -->
        <div class="col-md-6 col-12">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <i class="menu-icon tf-icons ti ti-user ti-28px"></i>
                {{-- <h5 class="card-title mb-0">{{ __('admin.totalusersmf') }}</h5> --}}
              </div>
            </div>
            <div class="card-body">
              {{-- <div id="pieChart"></div> --}}
            </div>
          </div>
        </div>
        <!-- /Pie Chart -->

        <!-- Vertical Bar Charts -->
        <div class="col-md-6 col-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <i class="ti ti-id ti-28px"></i>
                {{-- <h5 class="card-title mb-0 mx-2">{{ __('admin.usersdetails') }}</h5> --}}
              </div>
            </div>
            <div class="card-body">
              {{-- <canvas id="verticalBarChart" class="chartjs" data-height="345"></canvas> --}}
            </div>
          </div>
        </div>
        <!-- /Vertical Bar Charts -->
      </div>
    </div>
  </div>
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/apex-charts/apexcharts.js"></script>
  <script src="/vuexy/assets/vendor/libs/chartjs/chartjs.js"></script>
@stop

@section('pagescripts')
  <script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>
  <script src="/vuexy/assets/js/charts-apex.js"></script>
  <script src="/vuexy/assets/js/charts-chartjs.js"></script>

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
  <script>
    new Chart(document.getElementById('sessionChart'), {
      type: 'bar',
      data: {
        labels: {!! json_encode($sessions_labels) !!},
        datasets: [{
          label: '({{ __('admin.hours') }})',
          data: {!! json_encode($sessions_durations) !!},
          borderWidth: 1
        }]
      },
      options: {
        backgroundColor: '#f5ee9b',
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

    new Chart(document.getElementById('mentorshipChart'), {
      type: 'bar',
      data: {
        labels: {!! json_encode($mentorship_labels) !!},
        datasets: [{
          label: '({{ __('admin.hours') }})',
          data: {!! json_encode($mentorship_durations) !!},
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

        }
      },
    });
  </script>

@stop
