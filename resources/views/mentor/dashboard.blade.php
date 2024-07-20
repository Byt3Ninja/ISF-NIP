@extends('layouts.admin')
@section('style')
<link rel="stylesheet" type="text/css" href="/app-assets/css{{$l=='ar'?'-rtl':''}}/pages/widgets.css">
<link rel="stylesheet" type="text/css" href="/app-assets/css{{$l=='ar'?'-rtl':''}}/pages/dashboard-ecommerce.css">
<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.min.css">
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
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
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
                                        <h3 class="mb-0">{{$nm_count}}</h3>
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
                                        <i class="bx bx-chalkboard font-medium-5"></i>
                                    </div>
                                    <div class="text-muted line-ellipsis">{{__('dashboard.Coming sessions')}}</div>
                                    <h3 class="mb-0">{{ count($mysessions) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-12">
                        <div class="card text-center">
                            <div class="card-content">
                                <div class="card-body py-1">
                                    <div class="badge-circle badge-circle-lg badge-circle-light-warning mx-auto mb-50">
                                        <i class="bx bx-calendar-alt font-medium-5"></i>
                                    </div>
                                    <div class="text-muted line-ellipsis">{{__('dashboard.Cycles')}}</div>
                                    <h3 class="mb-0">{{count($mycycles)}}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-12">
                        <div class="card text-center">
                            <div class="card-content">
                                <div class="card-body py-1">
                                    <div class="badge-circle badge-circle-lg badge-circle-light-info mx-auto mb-50">
                                        <i class="bx bx-server font-medium-5"></i>
                                    </div>
                                    <div class="text-muted line-ellipsis">{{__('admin.Company Attendance Rate')}}</div>
                                    <h3 class="mb-0">{{$attends_rate}}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-12">
                        <div class="card text-center">
                            <div class="card-content">
                                <div class="card-body py-1">
                                    <div class="badge-circle badge-circle-lg badge-circle-light-danger mx-auto mb-50">
                                        <i class="bx bx-star font-medium-5"></i>
                                    </div>
                                    <div class="text-muted line-ellipsis">{{__('cycles.Rate')}}</div>
                                    <small>{!! str_repeat('<i class="bx bxs-star" style="color: #fc960f;"></i>',auth()->user()->mrating()) !!}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              <div class="row">
                    <div class="col-sm-6 col-12">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h4 class="card-title"><i class="bx bx-help-circle mr-1"></i>{{__('dashboard.Latest services')}}</h4>
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
                                                <a href="/dashboard/directory?find={{$serv->{'name_'.$l} }}">
                                            <span class="widget-todo-title ml-50 text-secondary">

                <i class="bx bx-link text-primary"></i>
                                                {{$serv->{'name_'.$l} }}</span></a>
                                            </div>
                                            <div class="widget-todo-item-action d-flex align-items-center">
                                                <div class="badge badge-pill badge-light-primary mr-1">
                                                    <a href="/dashboard/directory?type={{$serv->serviceCategory->id}}">
                                                        {{$serv->serviceCategory->{'name_'.$l} }}
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
                                <h4 class="card-title"><i class="bx bx-calendar mr-1"></i>{{__('dashboard.Latest Events')}}</h4>
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
                                @if (count($event_latest)>0)
                                    @foreach($event_latest as $event)
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
                                                {{__('dashboard.No Events added')}} '<a href="/dashboard/calendar">{{__('dashboard.Add yours now')}}</a>'.</span>
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
                                <h4 class="card-title align-items-center">
                                    <i class='bx bx-card font-medium-4 mr-1'></i>{{__('dashboard.My current schedule')}}:
                                    <button onclick="modify(0,0,0)" type="button" class="btn btn-icon rounded-circle btn-light-info float-right" >
                                        <i class="bx bx-plus"></i></button></h4>
                            </div>
                            <div class="card-body">
                                @if(count($myschedules))
                                <div class="card border-light">
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <thead class="thead-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>{{__('dashboard.Days')}}</th>
                                                <th>{{__('dashboard.Time')}}</th>
                                                <th>{{__('dashboard.Notes')}}</th>
                                                <th>{{__('dashboard.Actions')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                    @foreach($myschedules as $key=>$schedule)
                                            <tr class="text-{{$schedule->active?'primary':'danger'}}">
                                                <td>{{$key+1}}</td>
                                                <td>
                                                    @switch($schedule->type)
                                                        @case("Weekly")
                                                        Weekly({{str_replace(['[',']','"'],' ',json_encode($schedule->days))}})
                                                        @break
                                                        @case("WorkingDays")
                                                        {{__('dashboard.All working days')}}
                                                        @break
                                                        @case("Period")
                                                        {{ __('dashboard.from').$schedule->dfrom->format('d-m-Y'). __('dashboard.to') .$schedule->dto->format('d-m-Y')}}
                                                        @break
                                                        @default
                                                        {{$schedule->dfrom->format('d-m-Y')}}
                                                    @endswitch
                                                </td>
                                                <td>{{ __('dashboard.from')}} {{ substr($schedule->tfrom,0,5)}} {{ __('dashboard.to')}} {{substr($schedule->tto,0,5)}}</td>
                                                <td>{{$schedule->notes}}</td>
                                                <td>
                                                    <button onclick="modify(1,{{$schedule->id}},0)" type="button" class="btn btn-icon rounded-circle btn-light-success" >
                                                        <i class="bx bx-edit-alt"></i></button>
                                                    <button onclick="modify(2,{{$schedule->id}},{{$schedule->active}})" type="button" class="btn btn-icon rounded-circle btn-light{{$schedule->active?'-primary':''}}" >
                                                        <i class="bx bx-{{$schedule->active?'show-alt':'hide'}}"></i></button>
                                                    <button onclick="modify(3,{{$schedule->id}},0)" type="button" class="btn btn-icon rounded-circle btn-light-danger" >
                                                        <i class="bx bx-trash"></i></button>
                                                </td>
                                            </tr>
                                          @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                                @else
                                {{ __('dashboard.kindly add your schedule')}}  <a href="#" onclick="modify(0,0,0)">"{{ __('dashboard.here')}}"</a>.
                            @endif
                        </div>
                    </div>
                </div>
            </section>
          @if(count($mysessions))
          <section>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header border-bottom">
                                <h4 class="card-title align-items-center">
                                    <i class='bx bx-card font-medium-4 mr-1'></i>{{__('dashboard.My next mentorship sessions')}}:</h4>
                            </div>
                            <div class="card-body">
                                
                                <div class="card border-light">
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <thead>
                                            <tr class="table-secondary">
                                                <th>#</th>
                                                <th>{{__('dashboard.Company')}}</th>
                                                <th>{{__('dashboard.User')}}</th>
                                              	<th>{{__('dashboard.Date')}}</th>
                                                <th>{{__('dashboard.Admin Notes')}}</th>
                                                <th>{{__('dashboard.Meeting Link')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                    @foreach($mysessions as $key=>$session)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <a class="media-left mr-50" href="/dashboard/companyProfile/{{$session->user->company->id}}">
                                                        <img src="{{$session->user->company->logo_url}}" alt="avatar" class="rounded-circle" height="40" width="40">
                                                    </a>
                                                    <a href="/dashboard/companyProfile/{{$session->user->company->id}}">
                                                        <div class="media align-items-center">
                                                            {{$session->user->company->name }}
                                                        </div>
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="media align-items-center">
                                                        <a class="media-left mr-50">
                                                            <img src="{{$session->user->image_url}}" alt="avatar" class="rounded-circle" height="40" width="40">
                                                        </a>
                                                            <div class="media-body">
                                                                <h6 class="media-heading mb-0">{{$session->user->name  }}</h6>
                                                                <span class="font-small-2">{{$session->user->title }}</span>
                                                            </div>
                                                    </div>
                                                </td>
                                              <td>
                                              {{$session->zoom_date}} ({{$session->period}}M)
                                              </td>
                                                <td>
                                                   {{$session->admin_comment??'-'}}
                                                </td>
                                              <td>
                                                 @if (now()->format('Y-m-d') == date('Y-m-d',strtotime($session->zoom_date)))
                                                        <button title="Start Meeting" type="button" data-value="{{ $session->id }}" class="btn btn-icon rounded-circle btn-success start_meeting">
                                                           <i class="bx bx-user"></i></button>
                                                  @else
                                                      <button title="Start Meeting" type="button" disabled class="btn btn-icon rounded-circle btn-light-warning">
                                                          <i class="bx bx-alarm"></i></button>Meeting will start at time
                                                 @endif
                                                </td>
                                            </tr>
                                    @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                             
                        </div>
                    </div>
                </div>
            </section>
          @endif
        </div>
    </div>
</div>
<!-- END: Content-->
<!-- Send response -->
<div class="modal fade text-left" id="SendRequest" tabindex="-1" role="dialog" aria-labelledby="sendrequest" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form method="post" action="/dashboard/mentors/modify" enctype="multipart/form-data" autocomplete="off">
            <div class="modal-header">
                <h4 class="modal-title">{{__('dashboard.Add new')}} <span id="rname"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="bx bx-x"></i>
                </button>
            </div>
            <div class="modal-body">
                @csrf
                <label>{{__('dashboard.Choose type')}}:</label>
                <div class="form-group">
                    <select name="type" class="select form-control" id="type" onchange="SelectType(this.options[this.selectedIndex].value)" required>
                        <option value="OneDay">{{__('dashboard.Add one day')}}</option>
                        <option value="Weekly">{{__('dashboard.Select weekly days and times')}}</option>
                        {{--  <option value="WorkingDays">{{__('dashboard.Add working days times')}}</option>  --}}
                        <option value="Period">{{__('dashboard.Add period')}}</option>
                    </select>
                </div>
                <input type="hidden" name="rtype" value="0" id="rtype">
                <input type="hidden" name="id" value="0" id="sid">
                <div id="showdays" class="form-group" style="display: none">
                    <select name="days[]" class="select2 form-control" multiple id="days">
                        <option value="Sat">{{__('dashboard.Saturday')}}</option>
                        <option value="Sun">{{__('dashboard.Sunday')}}</option>
                        <option value="Mon">{{__('dashboard.Monday')}}</option>
                        <option value="Tue">{{__('dashboard.Tuesday')}}</option>
                        <option value="Wed">{{__('dashboard.Wednesday')}}</option>
                        <option value="Thu">{{__('dashboard.Thursday')}}</option>
                        <option value="Fri">{{__('dashboard.Friday')}}</option>
                    </select>
                </div>

                <div class="form-group" id="showdates">
                    <label>{{__('dashboard.Date')}}:</label>
                    <input class='date width-100' type="text" id="dfrom" name="dfrom" value="" required>
                    <span id="showtto" style="display: none">
                    <label>{{__('dashboard.to')}}:</label>
                    <input class='date width-100' type="text" id="dto" name="dto" value="">
                    </span>
                </div>
                <div class="form-group">
                <label>{{__('dashboard.Time from')}}:</label>
                    <input class="time width-100" type="text" id="tfrom" name="tfrom" value="" required> <label>{{__('dashboard.to')}}:</label>
                    <input class="time width-100" type="text" id="tto" name="tto" value="" required>
                </div>

                <label>{{__('dashboard.Add a Note')}}:</label>
                <div class="form-group">
                            <textarea class="form-control" id="notes" name='notes' rows="4"
                                      placeholder="{{__('dashboard.Write your note here')}}." ></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary ml-1" >
                    <i class="bx bx-check d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">{{__('dashboard.Submit')}}</span>
                </button>
            </div>
            </form>
        </div>
    </div>
</div>

@stop
@section('pagescripts')
<script src="/js/jquery.datetimepicker.full.min.js"></script>
<script>
    $('.date').datetimepicker({timepicker:false,format:'Y-m-d'});
    $('.time').datetimepicker({datepicker:false,format:'H:i'});
    function modify(type,id,stat){
        $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
        if (type===3) {
            //delete
            if (confirm("{{__('dashboard.sure del schedule')}}")) {
                $.ajax({
                    type: "POST", url: "/dashboard/mentors/modify",
                    data: {"id": id, "rtype": type,},
                    success: function (response) {
                        location.reload();
                    },
                    error: function (error) {
                        console.log(error);
                        alert("Error Occurred, Try again!");
                    }
                });
            }} else if (type===2) {
            //Enable-disable
            $.ajax({
                type: "POST", url: "/dashboard/mentors/modify",
                data: {"id": id, "rtype": type,"stat":stat},
                success: function (response) {
                    location.reload();},
                error: function (error) {
                    console.log(error);
                    alert("Error Occurred, Try again!");
                }});
        } else if (type===1) {
            // Edit current
            $.ajax({
                type: "POST", url: "/dashboard/mentors/modify",
                data: {"id": id, "rtype": type,},
                success: function (response) {
                    switchtypes(response.type);
                    $('#sid').val(response.id);
                    $('#rtype').val(4);
                    $('#type').val(response.type);
                    $('#days').val(response.days);
                    $('#dfrom').val(response.dfrom);
                    $('#dto').val(response.dto);
                    $('#tfrom').val(response.tfrom);
                    $('#tto').val(response.tto);
                    $('#notes').val(response.notes);
                    $('#SendRequest').modal('show');
                    },
                error: function (error) {
                    console.log(error);
                    alert("Error Occurred, Try again!");
                }});
        } else {
            //show from as new
            $('#sid, #type, #days, #dfrom, #dto, #tfrom, #tto, #notes').val();
            $('#rtype').val('0');
            switchtypes(type);
            $('#SendRequest').modal('show');
        }
    }
    function SelectType(type){
        switchtypes(type);
    }
    function switchtypes(type) {
        switch (type) {
            case 'OneDay':
                $('#showdates').show();
                $('#showtto, #showdays').hide();
                $("#dfrom").prop('required',true);
                $("#days, #dto").prop('required',false);
                break;
            case 'Weekly':
                $('#showdates').hide();
                $('#showdays').show();
                $("#days").prop('required',true);
                $("#dfrom, #dto").prop('required',false);
                break;
            case 'WorkingDays':
                $('#showdates, #showdays').hide();
                $("#dfrom, #dto, #days").prop('required',false);
                break;
            case 'Period':
                $('#showtto,#showdates').show();
                $('#showdays').hide();
                $("#dfrom, #dto").prop('required',true);
                $("#days").prop('required',false);
                break;
        }
    }

    $('.start_meeting').click(function () {
        $.ajax({
            type: "GET", url: "/dashboard/mentor/meetingRedirect/"+$(this).data('value'),
            success: function (response) {
                alert(response['message']);
                window.open(response['join_url'], '_blank');
                window.location.href ='{{ route('mentorshipTasks.index') }}'+'?mentorship_id='+response['mentorship_id'];
            },
            error: function (error) {
                console.log(error);
                alert("Error Occurred, Try again!");
            }
        });
    });
</script>
@stop