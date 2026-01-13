@extends('layouts.admin')
@section('vendorstyle')
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/charts/apexcharts.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/swiper.min.css">
@stop
@section('style')
    <link rel="stylesheet" type="text/css" href="/app-assets/css{{$l=='ar'?'-rtl':''}}/pages/dashboard-analytics.css">
@stop
@section('content')
    @php
        $tusers = App\User::count();
        // $notactive = App\User::join('model_has_roles','id','=','model_id')->where('role_id',5)->count();
		$notactive = App\User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->where('roles.default_for', 'registered')
			->select('users.*')
            ->count();
        $individual = App\User::whereNull('HaveCompany')->count();

    @endphp
            <!-- BEGIN: Content-->
    <div class="">
    <div class="">
      <div class="content-overlay"></div>
            <div class="content-header row">
            </div>
            <div class="content-body">

                <!-- Multiple Select2 start -->
                <section class="multiple-select2">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">{{__('admin.Download')}} {{__('admin.UsersReports')}}</h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <form class="form" id="basicForm" action="/reports/users" method="POST" enctype="multipart/form-data">
                                            {!! csrf_field() !!}
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>{{__('admin.SelType')}}</h6>
                                                    <p>{{__('admin.SelMulti')}}.</p>
                                                    <div class="form-group">
                                                        <select name="type[]" class="select2 form-control" multiple="multiple">
                                                            <option value="2">{{__('admin.Editor')}}</option>
                                                            <option value="3">{{__('admin.Coordinator')}}</option>
                                                            <option value="4">{{__('admin.Page Manager')}}</option>
                                                            <option value="5">{{__('admin.Manager')}}</option>
                                                            <option value="6">{{__('admin.Mentor')}}</option>
                                                            <option value="7">{{__('admin.Judger')}}</option>
                                                            <option value="8">{{__('admin.Registered')}}</option>
                                                            <option value="9">{{__('admin.Pages Editor')}}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="card-content">
                                                    <div class="card-body">
                                                        <p>{{__('admin.ChooseGender')}}</p>
                                                        <ul class="list-unstyled mb-0">
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="gender" value="" id="rad0" checked="">
                                                                        <label for="rad0">{{__('admin.All')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="gender" value="Male" id="rad1">
                                                                        <label for="rad1">{{__('admin.Male')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="gender" value="Female" id="rad2">
                                                                        <label for="rad2">{{__('admin.Female')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                        </ul>
                                                    </div>

                                                </div>
                                                <div class="card-content">
                                                    <div class="card-body">
                                                        <p>{{__('admin.ReportLang')}}</p>
                                                        <ul class="list-unstyled mb-0">
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="lang" value="ar" id="radio1" checked="">
                                                                        <label for="radio1">{{__('admin.Arabic')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="lang" value="en" id="radio2">
                                                                        <label for="radio2">{{__('admin.English')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                        </ul>
                                                        <button type="submit" class="btn btn-primary">{{__('admin.Download')}}</button>

                                                    </div>

                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Multiple Select2 end -->
                <!-- Multiple Select2 start -->
                <section class="multiple-select2">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">{{__('admin.Download')}} {{__('admin.Company`s Reports')}} </h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <form class="form" id="basicForm2" action="/reports/companies" method="POST" enctype="multipart/form-data">
                                            {!! csrf_field() !!}
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h6>{{__('admin.SelState')}}</h6>
                                                    <p>{{__('admin.SelMultiState')}}.</p>
                                                    <div class="form-group">
                                                        <select name="state[]" class="select2 form-control" multiple="multiple">
                                                            @foreach(App\State::get() as $type)
                                                                <option value="{{$type->id}}">{{$type->{'name_'.$l}  }} - {{ $type->country->{'name_'.$l} }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="card-content">
                                                    <div class="card-body">
                                                        <p>{{__('admin.Type')}}</p>
                                                        <ul class="list-unstyled mb-0">
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="type" value="" id="type0" checked="">
                                                                        <label for="type0">{{__('admin.All')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="type" value="new" id="type1">
                                                                        <label for="type1">{{__('admin.New only')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="type" value="cycle" id="type2">
                                                                        <label for="type2">{{__('admin.Join Cycle')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="type" value="approved" id="type3">
                                                                        <label for="type3">{{__('admin.Approved')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                        </ul>
                                                    </div>

                                                </div>
                                                <div class="card-content">
                                                    <div class="card-body">
                                                        <p>{{__('admin.ReportLang')}}</p>
                                                        <ul class="list-unstyled mb-0">
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="lang" value="ar" id="r1" checked="">
                                                                        <label for="r1">{{__('admin.Arabic')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                            <li class="d-inline-block mr-2 mb-1">
                                                                <fieldset>
                                                                    <div class="radio">
                                                                        <input type="radio" name="lang" value="en" id="r2">
                                                                        <label for="r2">{{__('admin.English')}}</label>
                                                                    </div>
                                                                </fieldset>
                                                            </li>
                                                        </ul>
                                                        <button type="submit" class="btn btn-primary">{{__('admin.Download')}}</button>

                                                    </div>

                                                </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </section>
            <!-- Multiple Select2 end -->

        </div>
    </div>
    </div>
    <!-- END: Content-->
@stop
@section('scripts')
    <script src="/app-assets/vendors/js/charts/apexcharts.min.js"></script>
@stop
@section('pagescripts')
    <script>
        var $primary = '#5A8DEE';
        var $success = '#39DA8A';
        var $danger = '#FF5B5C';
        var $warning = '#FDAC41';
        var $info = '#00CFDD';
        var $label_color = '#475f7b';
        var $primary_light = '#E2ECFF';
        var $danger_light = '#ffeed9';
        var $gray_light = '#828D99';
        var $sub_label_color = "#596778";
        var $radial_bg = "#e7edf3";
        // Bar Chart
        // ---------
        var analyticsBarChartOptions = {
            chart: {
                height: 260,
                type: 'bar',
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '20%',
                    endingShape: 'rounded'
                },
            },
            legend: {
                horizontalAlign: 'right',
                offsetY: -10,
                markers: {
                    radius: 50,
                    height: 8,
                    width: 8
                }
            },
            dataLabels: {
                enabled: false
            },
            colors: [$info],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: "vertical",
                    inverseColors: true,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [0, 70, 100]
                },
            },
            series: [{
                name: 'Total',
                data: 0
            }],
            xaxis: {
                categories: 0,
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: $gray_light
                    }
                }
            },
            yaxis: {
                min: 0,
                max: 10,
                tickAmount: 5,
                labels: {
                    style: {
                        color: $gray_light
                    }
                }
            },
            legend: {
                show: false
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return " " + val
                    }
                }
            }
        }

        var analyticsBarChart = new ApexCharts(
            document.querySelector("#analytics-bar-chart"),
            analyticsBarChartOptions
        );

        //    analyticsBarChart.render();

        // Radial-Success-chart
        // --------------------------------
        var radialSuccessoptions = {
            chart: {
                height: 40,
                width: 40,
                type: "radialBar"
            },
            grid: {
                show: false,
                padding: {
                    left: -30,
                    right: -30,
                    top: 0,
                }
            },
            series: [{{intval($notactive / $tusers * 100)}}],
            colors: [$success],
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: "30%"
                    },
                    dataLabels: {
                        showOn: "always",
                        name: {
                            show: false
                        },
                        value: {
                            show: false,
                        }
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: "horizontal",
                    gradientToColors: [$success],
                    opacityFrom: 1,
                    opacityTo: 0.8,
                    stops: [0, 70, 100]
                }
            },
            stroke: {
                lineCap: "round",
            }
        };
        var radialSuccessChart = new ApexCharts(
            document.querySelector("#radial-success-chart"),
            radialSuccessoptions
        );

        radialSuccessChart.render();

        // Radial-Warning-chart
        // --------------------------------
        var radialWarningoptions = {
            chart: {
                height: 40,
                width: 40,
                type: "radialBar"
            },
            grid: {
                show: false,
                padding: {
                    left: -30,
                    right: -30,
                    top: 0,
                }
            },
            series: [50],
            colors: [$warning],
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: "30%"
                    },
                    dataLabels: {
                        showOn: "always",
                        name: {
                            show: false
                        },
                        value: {
                            show: false,
                        }
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: "horizontal",
                    gradientToColors: [$warning],
                    opacityFrom: 1,
                    opacityTo: 0.8,
                    stops: [0, 70, 100]
                }
            },
            stroke: {
                lineCap: "round",
            }
        };
        var radialWarningChart = new ApexCharts(
            document.querySelector("#radial-warning-chart"),
            radialWarningoptions
        );

        radialWarningChart.render();
        // Radial-Success-chart
        // --------------------------------
        var radialSuccessoptions = {
            chart: {
                height: 40,
                width: 40,
                type: "radialBar"
            },
            grid: {
                show: false,
                padding: {
                    left: -30,
                    right: -30,
                    top: 0,
                }
            },
            series: [{{intval(11 / $tusers * 100)}}],
            colors: [$primary],
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: "30%"
                    },
                    dataLabels: {
                        showOn: "always",
                        name: {
                            show: false
                        },
                        value: {
                            show: false,
                        }
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: "horizontal",
                    gradientToColors: [$primary],
                    opacityFrom: 1,
                    opacityTo: 0.8,
                    stops: [0, 70, 100]
                }
            },
            stroke: {
                lineCap: "round",
            }
        };
        var radialSuccessChart = new ApexCharts(
            document.querySelector("#radial-primary-chart"),
            radialSuccessoptions
        );

        radialSuccessChart.render();
        // Radial-Danger-chart
        // --------------------------------
        var radialDangeroptions = {
            chart: {
                height: 40,
                width: 40,
                type: "radialBar",
            },
            grid: {
                show: false,
                padding: {
                    left: -30,
                    right: -30,
                    top: 0,
                }
            },
            series: [{{intval($individual / $tusers * 100)}}],
            colors: [$danger],
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: "30%"
                    },
                    dataLabels: {
                        showOn: "always",
                        name: {
                            show: false
                        },
                        value: {
                            show: false,
                        }
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: "horizontal",
                    gradientToColors: [$danger],
                    opacityFrom: 1,
                    opacityTo: 0.8,
                    stops: [0, 70, 100]
                }
            },
            stroke: {
                lineCap: "round",
            }
        };
        var radialDangerChart = new ApexCharts(
            document.querySelector("#radial-danger-chart"),
            radialDangeroptions
        );

        radialDangerChart.render();
    </script>
@stop

