@extends('layouts.admin')
@section('vendorstyle')
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/charts/apexcharts.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/swiper.min.css">
@stop
@section('style')
    <link rel="stylesheet" type="text/css" href="/app-assets/css{{$l=='ar'?'-rtl':''}}/pages/dashboard-ecommerce.css">
@stop
@section('content')
    <!-- BEGIN: Content-->

    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                @if(session()->has('msg'))
                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-{{session()->get('class')}} mb-2" role="alert">
                                    <button type="button" class="close" data-dismiss="alert"
                                            aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-{{session()->get('class')=='success'?'like':'error'}}"></i>
                                        <span>
                                                            {{ __('admin.'.session()->get('msg')) }}
                                            {{ Session::forget('msg') }}
                                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-12">
{{--             ==================> Counting Section     <=======================    --}}
                    <div class="row">
                        <div class="col-sm-6 col-12 dashboard-users-danger">
                            <div class="card text-center">
                                <a href="/dashboard/users#search=Manager">
                                    <div class="card-content">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-success mx-auto mb-50">
                                                <i class="bx bxs-user-voice font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Managers')}}</div>
                                            <h3 class="mb-0">{{$managers_count}}</h3>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-6 col-12 dashboard-users-success">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/organizations">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-warning mx-auto mb-50">
                                                <i class="bx bxs-school font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Organizations')}}</div>
                                            <h3 class="mb-0">{{$organizations_count}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-12 dashboard-users-success">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/requests/all">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-secondary mx-auto mb-50">
                                                <i class="bx bxs-megaphone font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Services Requests')}}</div>
                                            <h3 class="mb-0">{{$servrequests_count}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-12 dashboard-users-success">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/inbox">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-success mx-auto mb-50">
                                                <i class="bx bxs-conversation font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.New messages')}}</div>
                                            <h3 class="mb-0">{{$messages_count}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-12 dashboard-users-danger">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/mymsg">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-danger  mx-auto mb-50">
                                                <i class="bx bxs-message font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Contact us')}}</div>
                                            <h3 class="mb-0">{{$cmessages_count}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-12 dashboard-users-success">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/cycles">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-danger mx-auto mb-50">
                                                <i class="bx bx-clipboard font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Cycles')}}</div>
                                            <h3 class="mb-0">{{$cycles_count}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-12 dashboard-users-success">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/companies">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-info mx-auto mb-50">
                                                <i class="bx bx-buildings font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Startups')}}</div>
                                            <h3 class="mb-0">{{$startups_count}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4 col-12 dashboard-users-danger">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/users#search=Mentor">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-danger mx-auto mb-50">
                                                <i class="bx bxs-graduation font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Mentors')}}</div>
                                            <h3 class="mb-0">{{$mentors_count}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-12 dashboard-users-danger">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/mentorship/sessions">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-secondary mx-auto mb-50">
                                                <i class="bx bxs-help-circle font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Mentorship requests')}}</div>
                                            <h3 class="mb-0">{{$mentrequests_count}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-12 dashboard-users-danger">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/mentorship/sessions">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-success mx-auto mb-50">
                                                <i class="bx bxs-calendar-event font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Mentorship requests Duration')}}</div>
                                            <h3 class="mb-0">{{$mentrequests_period}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-12 dashboard-users-success">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/users#search=Judger">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-primary mx-auto mb-50">
                                                <i class="bx bxs-filter-alt font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Judgers')}}</div>
                                            <h3 class="mb-0">{{$judgers_count}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-12 dashboard-users-success">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/training/sessions">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-warning mx-auto mb-50">
                                                <i class="bx bxs-alarm font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Training sessions')}}</div>
                                            <h3 class="mb-0">{{$sessions_count}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-12 dashboard-users-success">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/training/sessions">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-info mx-auto mb-50">
                                                <i class="bx bxs-timer font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Training sessions Duration')}}</div>
                                            <h3 class="mb-0">{{$sessions_period}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>




                    </div>

{{--             ==================>    Charts Section   <==========================  --}}
                    <section>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header border-bottom">
                                        <h4 class="card-title align-items-center">
                                            <i class='bx bx-card font-medium-4 mr-1'></i> {{ __("admin.Cycle Startups Statistics") }}
                                            :
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="card ">
                                            <div>
                                                <canvas id="cycleStartups"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">{{__('admin.usersdetails')}}</h4>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-body pl-0">
                                            <div class="height-300">
                                                <canvas id="bar-chart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">{{__('admin.totalusersmf')}} </h4>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-body pl-0">
                                            <div class="height-300">
                                                <canvas id="simple-pie-chart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Content-->
@stop

@section('scripts')
    <script src="/app-assets/vendors/js/charts/chart.min.js"></script>
    <script src="/app-assets/vendors/js/charts/apexcharts.min.js"></script>
    <script src="/app-assets/vendors/js/extensions/swiper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stop
@section('pagescripts')
    <script>

        $(window).on("load", function () {
            var $primary = '#5A8DEE',
                $success = '#39DA8A',
                $danger = '#FF5B5C',
                $warning = '#FDAC41',
                $info = '#00CFDD',
                $label_color = '#475F7B',
                grid_line_color = '#dae1e7';
            var themeColors = [$primary, $warning, $danger, $success, $info, $label_color, $primary, $warning, $danger, $success, $info, $label_color,
                $primary, $warning, $danger, $success, $info, $label_color, $primary, $warning, $danger, $success, $info, $label_color];

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
                legend: {display: false},
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
                    text: '{{__('admin.totalregisteredusers')}}'
                },
            };
            // Chart Data
            var barchartData = {
                labels: {!!$usersgov->pluck('state_'.$l)->toJson()!!},
                datasets: [{
                    label: "Total",
                    data: {!!$usersgov->pluck('total')->toJson()!!},
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
                    text: '{{__('admin.totalregisteredusers')}}'
                }
            };
            // Chart Data
            var piechartData = {
                labels: ['{{__('admin.Female')}}', '{{__('admin.Male')}}'],
                datasets: [{
                    label: "{{__('admin.totalregistered')}}",
                    data: {!!$usersgender->pluck('total')->toJson()!!},
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
                labels:{!! json_encode($cycles_labels) !!},
                datasets: [{
                    label: '({{ __('admin.Registered') }})',
                    data: {!! json_encode($cycles_startups_count) !!},
                    borderWidth: 1
                }]
            },
            options: {
                backgroundColor : '#f59b9b',
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
