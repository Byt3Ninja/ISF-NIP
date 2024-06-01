@extends('layouts.admin')
@section('style')
    <link rel="stylesheet" type="text/css" href="/app-assets/css{{$l=='ar'?'-rtl':''}}/pages/widgets.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css{{$l=='ar'?'-rtl':''}}/pages/dashboard-ecommerce.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
@stop
@section('title', __('admin.Dashboard') )
@section('subTitle', __('admin.My Dashboard'))
@section('content')

            <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            @include('admin.includes.heading')
            <div class="content-header row">
            </div>
            <div class="content-body">
                @if(session()->has('msg'))
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-success  mb-2" role="alert">
                                <button type="button" class="close" data-dismiss="alert"
                                        aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-like"></i>
                                    <span>
                                        {{ __('admin.'.session()->get('msg')) }}
                                        {{ Session::forget('msg') }}
                                                        </span>
                                </div>
                            </div>
                        </div>
                    </div>

                @elseif(session()->has('message2'))
                        <div class="alert alert-danger alert-dismissible mb-2" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <div class="d-flex align-items-center">
                                <i class="bx bx-error"></i>
                                <span>
                                    {{ __('admin.'.session()->get('message2')) }}
                                </span>
                            </div>
                        </div>
                    @endif
                <section>
                    <div class="row">
                        <div class="col-sm-3 col-12 dashboard-users-success">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/directory">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-success mx-auto mb-50">
                                                <i class="bx bx bx-briefcase-alt font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('dashboard.Services')}}</div>
                                            <h3 class="mb-0">{{$services_count}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 col-12 dashboard-users-danger">
                            <div class="card text-center">
                                <div class="card-content">
                                    <a href="/dashboard/inbox">
                                        <div class="card-body py-1">
                                            <div class="badge-circle badge-circle-lg badge-circle-light-primary mx-auto mb-50">
                                                <i class="bx bxs-message font-medium-5"></i>
                                            </div>
                                            <div class="text-muted line-ellipsis">{{__('dashboard.newmessages')}}</div>
                                            <h3 class="mb-0">{{$messenger_count}}</h3>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 col-12 dashboard-users-success">
                            <div class="card text-center">
                                <div class="card-content">
                                    <div class="card-body py-1">
                                        <div class="badge-circle badge-circle-lg badge-circle-light-secondary mx-auto mb-50">
                                            <i class="bx bx-badge-check font-medium-5"></i>
                                        </div>
                                        <div class="text-muted line-ellipsis">{{__('dashboard.First screening')}}</div>
                                        <h3 class="mb-0">{{  count($csteps) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 col-12">
                            <div class="card text-center">
                                <div class="card-content">
                                    <div class="card-body py-1">
                                        <div class="badge-circle badge-circle-lg badge-circle-light-warning mx-auto mb-50">
                                            <i class="bx bx-bullseye font-medium-5"></i>
                                        </div>
                                        <div class="text-muted line-ellipsis">{{__('dashboard.Final screening')}}</div>
                                        <h3 class="mb-0">{{count($fcompanies)}}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title"><i
                                                class="bx bx-help-circle mr-1"></i>{{__('dashboard.Latest services')}}
                                    </h4>
                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li>
                                                <a data-action="close">
                                                    <i class="bx bx-x"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if (count($services_latest)>0)
                                        @foreach($services_latest as $serv)
                                            <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                                                <div class="widget-todo-title-area d-flex align-items-center">
                                                    <a href="/dashboard/directory?find={{$serv->name }}">
                                            <span class="widget-todo-title ml-50 text-secondary">

                                                <i class="bx bx-link text-primary"></i>
                                                {{$serv->name }}</span></a>
                                                </div>
                                                <div class="widget-todo-item-action d-flex align-items-center">
                                                    <div class="badge badge-pill badge-light-primary mr-1">
                                                        <a href="/dashboard/directory?type={{$serv->serviceCategory->id}}">
                                                            {{$serv->serviceCategory->name }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                                            <div class="widget-todo-title-area d-flex align-items-center">
                                            <span class="widget-todo-title ml-50">
                                     <i class="bx bxs-bell-ring text-danger"></i>
                                                {{__('dashboard.No services added')}}.</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title"><i
                                                class="bx bx-calendar mr-1"></i>{{__('dashboard.Latest Events')}}</h4>
                                    <div class="heading-elements">
                                        <ul class="list-inline mb-0">
                                            <li>
                                                <a data-action="close">
                                                    <i class="bx bx-x"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if (count($events_latest)>0)
                                        @foreach($events_latest as $event)
                                            <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                                                <div class="widget-todo-title-area d-flex align-items-center">
                                                    <a href="/dashboard/calendar">
                                            <span class="widget-todo-title ml-50 text-secondary">
                                                <i class="bx bx-calendar-event text-primary"></i>
                                                {{$event->title}}</span></a>
                                                </div>
                                                <div class="widget-todo-item-action d-flex align-items-center">
                                                    <div class="badge badge-pill badge-light-success mr-1">
                                                        {{date('d-m-Y', strtotime($event->start))}}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="widget-todo-title-wrapper d-flex justify-content-between align-items-center mb-75">
                                            <div class="widget-todo-title-area d-flex align-items-center">
                                            <span class="widget-todo-title ml-50">
                                     <i class="bx bxs-bell-ring text-danger"></i>
                                                {{__('dashboard.No Events added')}} '<a
                                                        href="/dashboard/calendar">{{__('dashboard.Add yours now')}}</a>'.</span>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title d-flex align-items-center">
                                        <i class='bx bx-card font-medium-4 mr-1'></i>{{__('dashboard.Your Judging List')}}
                                    </h4>
                                </div>
                                <div class="card-body">

                                    @if (count($csteps)>0)
                                        @foreach($csteps as $step)
                                            <div class="card border-light">
                                                <div class="card-header">
                                                    <h4 class="primary"><i class="bx bxs-bell"></i>{{$step->title}} -
                                                        ({{$step->cycle->title}})
                                                        - {{__('dashboard.start from')}} {{$step->from->format('d-m-Y')}} {{__('dashboard.to')}} {{$step->to->format('d-m-Y')}}
                                                    </h4>
                                                </div>
                                                <div class="card-body bg-rgba-light">
                                                    <p class="card-text">
                                                        {{$step->description}}
                                                    </p>
                                                    <small class="text-muted">{{__('dashboard.This Cycle Start from')}} {{$step->cycle->start->format('d-m-Y')}} {{__('dashboard.to')}} {{$step->cycle->end->format('d-m-Y')}}</small>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table mb-0">
                                                        <thead class="thead-dark">
                                                        <tr>
                                                            <th>{{__('dashboard.Company')}}</th>
                                                            <th>{{__('dashboard.User/Title')}}</th>
                                                            <th>{{__('dashboard.Screening completed')}}</th>
                                                            <th>{{__('dashboard.AVG degree')}}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php
                                                            $form = \App\Step::where('cycle_id',$step->cycle_id)->where('stype',1)->where('arr',$step->arr-1)->first();



                                                            $groups = App\Screening::where('user_id',Auth::id())->where('step_id',$step->id)->pluck('users');
                                                            $groups = json_decode("[".str_replace(['[',']'],' ',json_encode($groups))."]");
                                                            if ($mysteps->where('step_id',$step->id)->pluck('panel')->first()==0) {
                                                              $companies = \App\Company::where('step',$step->id)->get();
                                                            } else {
                                                              $companies = \App\Company::where('step',$step->id)->whereIN('id',$groups??[])->get();
                                                            }

                                                        @endphp

                                                        @foreach($companies as $company)
                                                            <tr>
                                                                <td>
                                                                    <div class="media align-items-center">
                                                                        <a class="media-left mr-50">
                                                                            <img src="{{$company->logo_url}}"
                                                                                 alt="avatar" class="rounded-circle"
                                                                                 height="40" width="40">
                                                                        </a>
                                                                        <a href="/dashboard/screening/{{$form->id}}/{{$company->id}}">
                                                                            <div class="media align-items-center">
                                                                                {{$company->name}}
                                                                            </div>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="media align-items-center">
                                                                        <a class="media-left mr-50">
                                                                            <img src="{{$company->user->image_url}}"
                                                                                 alt="avatar" class="rounded-circle"
                                                                                 height="40" width="40">
                                                                        </a>
                                                                        <a href="/dashboard/screening/{{$form->id}}/{{$company->id}}">
                                                                            <div class="media-body">
                                                                                <h6 class="media-heading mb-0">{{$company->user->name }} </h6>
                                                                                <span class="font-small-2">{{$company->user->title }}</span>
                                                                            </div>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                @php
                                                                    $weight = $company->companyWeight($company->id,$form->id,Auth::id());
                                                                    $judgepercentage = $company->companyJudge($company->id,$form->id,Auth::id());
                                                                @endphp
                                                                <td>
                                                                    <div class="progress progress-bar-{{$judgepercentage<50?'danger':($judgepercentage<75?'warning':'success') }} progress-sm mb-0">
                                                                        <div class="progress-bar" role="progressbar"
                                                                             aria-valuenow="52" aria-valuemin="80"
                                                                             aria-valuemax="100"
                                                                             style="width:{{$judgepercentage}}%;"></div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <span class="badge badge-light-{{$weight<25?'danger':($weight<50?'warning':'success') }}"> {{$weight}} %</span>
                                                                </td>
                                                            </tr>
                                                        @endforeach

                                                        </tbody>
                                                    </table>
                                                </div>


                                            </div>
                                        @endforeach
                                    @else
                                        {{__('dashboard.No screening started yet')}}.
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title d-flex align-items-center">
                                        <i class='bx bx-card font-medium-4 mr-1'></i>{{__('dashboard.Cycle final screening')}}
                                    </h4>
                                </div>
                                <div class="card-body">
                                    @php
                                        $today = date("Y-m-d");
                                        $csteps = App\Step::whereRaw( '"'.$today. '" BETWEEN `from` AND `to`')->where('stype',5)->get()
                                    @endphp
                                    @if (count($fcompanies)>0)
                                        @foreach($csteps as $step)

                                            <div class="card border-light">
                                                <div class="card-header">
                                                    <h4 class="primary"><i class="bx bxs-bell"></i>{{$step->title}} -
                                                        ({{$step->cycle->title}})
                                                        - {{__('dashboard.start from')}} {{$step->from->format('d-M')}} {{__('dashboard.to')}} {{$step->to->format('d-M-Y')}}
                                                    </h4>
                                                </div>
                                                <div class="card-body bg-rgba-light">
                                                    <p class="card-text">
                                                        {{$step->description}}
                                                    </p>
                                                    <small class="text-muted">{{__('dashboard.This Cycle Start from')}} {{$step->cycle->start->format('d-m')}} {{__('dashboard.to')}} {{$step->cycle->end->format('d-m-Y')}}</small>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table mb-0">
                                                        <thead class="thead-dark">
                                                        <tr>
                                                            <th>{{__('dashboard.Company')}}</th>
                                                            <th>{{__('dashboard.User/Title')}}</th>
                                                            <th>{{__('dashboard.Screening completed')}}</th>
                                                            <th>{{__('dashboard.Meeting')}}</th>
                                                            <th>{{__('dashboard.Actions')}}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>

                                                        @foreach($fcompanies as $fcompany)
                                                            <tr>
                                                                <td>
                                                                    <div class="media align-items-center">
                                                                        <a class="media-left mr-50">
                                                                            <img src="{{$fcompany->company->logo_url}}"
                                                                                 alt="avatar" class="rounded-circle"
                                                                                 height="40" width="40">
                                                                        </a>
                                                                        <a href="/dashboard/companyProfile/{{$fcompany->company_id}}">
                                                                            <div class="media align-items-center">
                                                                                {{$fcompany->company->name }}
                                                                            </div>
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="media align-items-center">
                                                                        <a class="media-left mr-50">
                                                                            <img src="{{$fcompany->company->user->image_url}}"
                                                                                 alt="avatar" class="rounded-circle"
                                                                                 height="40" width="40">
                                                                        </a>
                                                                        <a href="/dashboard/companyProfile/{{$fcompany->company_id}}">
                                                                            <div class="media-body">
                                                                                <h6 class="media-heading mb-0">{{$fcompany->company->user->name }}</h6>
                                                                                <span class="font-small-2">{{$fcompany->company->user->title }}</span>
                                                                            </div>
                                                                        </a>
                                                                    </div>
                                                                </td>

                                                                <td>
                                                                    <div class="progress progress-bar- progress-sm mb-0">
                                                                        <div class="progress-bar" role="progressbar"
                                                                             aria-valuenow="52" aria-valuemin="80"
                                                                             aria-valuemax="100"></div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    {{$fcompany->datetime??__('dashboard.Not added yet')}}
                                                                </td>
                                                                <td>
                                                                    @if($fcompany->join_url)
                                                                        <a href="{{$fcompany->join_url}}"
                                                                           target="_blank">
                                                                            <button title="Join" type="button"
                                                                                    class="btn btn-icon rounded-circle btn-light-primary">
                                                                                <i class="bx bx-user"></i></button>
                                                                        </a>
                                                                        <a href="/dashboard/final/{{$step->id}}/{{$fcompany->company_id}}"
                                                                           target="_blank">
                                                                            <button title="Open form" type="button"
                                                                                    class="btn btn-icon rounded-circle btn-light-success">
                                                                                <i class="bx bx-file"></i></button>
                                                                        </a>
                                                                    @else
                                                                        {{__('dashboard.Not started')}}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach

                                                        </tbody>
                                                    </table>
                                                </div>


                                            </div>
                                        @endforeach
                                    @else
                                        {{__('dashboard.No Final screening started yet')}}.
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->
@stop
@section('pagescripts')

    <script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.table').DataTable({
                    "responsive": true,
                    "searching": false,
                    "lengthChange": false,
                    "paging": false,
                    "bInfo": false,
                    "columnDefs": [
                        {"orderable": false, "targets": [2]},
                    ]
                }
            );
        });
    </script>
@stop