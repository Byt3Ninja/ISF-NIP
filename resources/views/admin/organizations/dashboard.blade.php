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
    @php

        //Counts
        $messages = App\Messenger::where('receiver',auth()->id())->whereNull('read');
        $messages = $messages->count();

        $mentrequests = App\MentorshipRequest::where('organization_id',$organization_id)->whereNull('approved');
        $mentrequests = $mentrequests->count();


        $startups= \App\Company::whereHas('cycledata',function ($q) use ($organization_id){
            $q->where('organization_id',$organization_id);
        })->count();

        $cycles = App\Cycle::where('organization_id',$organization_id);
        $cycles_count = $cycles->count();

        $forms = App\BuilderForm::where('organization_id',$organization_id);
        $forms_count = $forms->count();

        $sessions = App\TrainingSession::whereHas('step',function ($q)use ($organization_id){
            $q->where('stype',4)->whereHas('cycle',function ($query) use ($organization_id){
                $query->where('organization_id',$organization_id);
            });
        })->count();

//
//        $mentors = App\User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6);
//        if( !is_null( $organization_id ) ) { $mentors = $mentors->where('users.organization_id', $organization_id); }
//        $mentors = $mentors->count();
//
//        $judgers = App\User::join('model_has_roles', 'id', 'model_id')->where('role_id', 7);
//        if( !is_null( $organization_id ) ) { $judgers = $judgers->where('users.organization_id', $organization_id); }
//        $judgers = $judgers->count();



    @endphp
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
                    <div class="row" >
                        <div class="col-sm-3 col-12 dashboard-users-success">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/admin/companies">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-primary mx-auto mb-50">
                                                <i class="bx bx-briefcase-alt font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Subscribe Startups')}}</div>
                                            <h3 class="mb-0">{{$startups}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 col-12 dashboard-users-danger">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/admin/mentorship/sessions">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-warning mx-auto mb-50">
                                                <i class="bx bxs-timer font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Training sessions')}}</div>
                                            <h3 class="mb-0">{{$sessions}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 col-12 dashboard-users-danger">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/admin/mentorship/sessions">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-danger mx-auto mb-50">
                                                <i class="bx bxs-help-circle font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Mentorship requests')}}</div>
                                            <h3 class="mb-0">{{$mentrequests}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 col-12 dashboard-users-success">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/admin/inbox">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-success mx-auto mb-50">
                                                <i class="bx bxs-conversation font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.New messages')}}</div>
                                            <h3 class="mb-0">{{$messages}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-12 dashboard-users-success">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/admin/cycles">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-info mx-auto mb-50">
                                                <i class="bx bx-bookmark-plus font-medium-5"></i>
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
                                    <a href="/admin/inbox">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light mx-auto mb-50">
                                                <i class="bx bxs-building font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('admin.Forms')}}</div>
                                            <h3 class="mb-0">{{$forms_count}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

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
@stop
@section('pagescripts')
{{--    <script>--}}

{{--        $(window).on("load", function () {--}}
{{--            var $primary = '#5A8DEE',--}}
{{--                $success = '#39DA8A',--}}
{{--                $danger = '#FF5B5C',--}}
{{--                $warning = '#FDAC41',--}}
{{--                $info = '#00CFDD',--}}
{{--                $label_color = '#475F7B',--}}
{{--                grid_line_color = '#dae1e7';--}}
{{--            var themeColors = [$primary, $warning, $danger, $success, $info, $label_color,$primary, $warning, $danger, $success, $info, $label_color,--}}
{{--                $primary, $warning, $danger, $success, $info, $label_color,$primary, $warning, $danger, $success, $info, $label_color];--}}

{{--            // Bar Chart--}}
{{--            // --------------------------------------------}}
{{--            //Get the context of the Chart canvas element we want to select--}}
{{--            var barChartctx = $("#bar-chart");--}}
{{--            // Chart Options--}}
{{--            var barchartOptions = {--}}
{{--                // Elements options apply to all of the options unless overridden in a dataset--}}
{{--                // In this case, we are setting the border of each bar to be 2px wide--}}
{{--                elements: {--}}
{{--                    rectangle: {--}}
{{--                        borderWidth: 2,--}}
{{--                        borderSkipped: 'left'--}}
{{--                    }--}}
{{--                },--}}
{{--                responsive: true,--}}
{{--                maintainAspectRatio: false,--}}
{{--                responsiveAnimationDuration: 500,--}}
{{--                legend: { display: false },--}}
{{--                scales: {--}}
{{--                    xAxes: [{--}}
{{--                        display: true,--}}
{{--                        gridLines: {--}}
{{--                            color: grid_line_color,--}}
{{--                        },--}}
{{--                        scaleLabel: {--}}
{{--                            display: true,--}}
{{--                        }--}}
{{--                    }],--}}
{{--                    yAxes: [{--}}
{{--                        display: true,--}}
{{--                        gridLines: {--}}
{{--                            color: grid_line_color,--}}
{{--                        },--}}
{{--                        scaleLabel: {--}}
{{--                            display: true,--}}
{{--                        },--}}
{{--                        ticks: {--}}
{{--                            stepSize: 10--}}
{{--                        },--}}
{{--                    }],--}}
{{--                },--}}
{{--                title: {--}}
{{--                    display: true,--}}
{{--                    text: '{{__('admin.totalregisteredusers')}}'--}}
{{--                },--}}
{{--            };--}}
{{--            // Chart Data--}}
{{--            var barchartData = {--}}
{{--                labels: {!!$usersgov->pluck('state_'.$l)->toJson()!!},--}}
{{--                datasets: [{--}}
{{--                    label: "Total",--}}
{{--                    data: {!!$usersgov->pluck('total')->toJson()!!},--}}
{{--                    backgroundColor: themeColors,--}}
{{--                    borderColor: "transparent"--}}
{{--                }]--}}
{{--            };--}}
{{--            var barChartconfig = {--}}
{{--                type: 'bar',--}}
{{--                // Chart Options--}}
{{--                options: barchartOptions,--}}
{{--                data: barchartData--}}
{{--            };--}}
{{--            // Create the chart--}}
{{--            var barChart = new Chart(barChartctx, barChartconfig);--}}
{{--            // Pie Chart--}}
{{--            // ----------------------------------}}
{{--            //Get the context of the Chart canvas element we want to select--}}
{{--            var pieChartctx = $("#simple-pie-chart");--}}
{{--            // Chart Options--}}
{{--            var piechartOptions = {--}}
{{--                responsive: true,--}}
{{--                maintainAspectRatio: false,--}}
{{--                responsiveAnimationDuration: 500,--}}
{{--                title: {--}}
{{--                    display: true,--}}
{{--                    text: '{{__('admin.totalregisteredusers')}}'--}}
{{--                }--}}
{{--            };--}}
{{--            // Chart Data--}}
{{--            var piechartData = {--}}
{{--                labels: ['{{__('admin.Female')}}','{{__('admin.Male')}}'],--}}
{{--                datasets: [{--}}
{{--                    label: "{{__('admin.totalregistered')}}",--}}
{{--                    data: {!!$usersgender->pluck('total')->toJson()!!},--}}
{{--                    backgroundColor: themeColors,--}}
{{--                }]--}}
{{--            };--}}
{{--            var pieChartconfig = {--}}
{{--                type: 'pie',--}}
{{--                // Chart Options--}}
{{--                options: piechartOptions,--}}
{{--                data: piechartData--}}
{{--            };--}}
{{--            // Create the chart--}}
{{--            var pieSimpleChart = new Chart(pieChartctx, pieChartconfig);--}}

{{--        });--}}
{{--    </script>--}}


@stop
