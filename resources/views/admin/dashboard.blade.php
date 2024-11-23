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
                <div class="alert alert-{{ session()->get('class') ?? 'danger' }} alert-dismissible fade show mb-2" role="alert">
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

      <!-- Horizontal Bar Chart -->
      <div class="col-12 mt-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <i class="ti ti-device-laptop ti-28px"></i>
              <h5 class="card-title mb-0 mx-2">{{ __('admin.Cycle Startups Statistics') }}</h5>
            </div>
          </div>
          <div class="card-body">
            <div id="horizontalBarChart"></div>
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
        <div class="col-md-6 col-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <i class="ti ti-id ti-28px"></i>
                <h5 class="card-title mb-0 mx-2">{{ __('admin.usersdetails') }}</h5>
              </div>
            </div>
            <div class="card-body">
              <canvas id="verticalBarChart" class="chartjs" data-height="345"></canvas>
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
  </script>

  <script src="/vuexy/assets/js/app-logistics-dashboard.js"></script>
  <script src="/vuexy/assets/js/charts-apex.js"></script>
  <script src="/vuexy/assets/js/charts-chartjs.js"></script>
@stop
