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

        $mentrequests = App\MentorshipRequest::where('organization_id',$organization_id)->count();

        $startups= \App\Company::whereHas('cycledata',function ($q) use ($organization_id){
            $q->where('organization_id',$organization_id);
        })->count();

        $cycles = App\Cycle::where('organization_id',$organization_id);
        $cycles_count = $cycles->count();
        $latest_cycles= $cycles->latest()->limit(10)->get();

        $forms = App\BuilderForm::where('organization_id',$organization_id);
        $forms_count = $forms->count();

        $sessions = App\TrainingSession::whereHas('step',function ($q)use ($organization_id){
            $q->where('stype',4)->whereHas('cycle',function ($query) use ($organization_id){
                $query->where('organization_id',$organization_id);
            });
        })->count();

        $mentorshipRequests = App\MentorshipRequest::where('organization_id',$organization_id)
        ->whereNull('approved')->latest()->limit(5)->get();

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
                    <div class="row">
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
                                    <a href="/admin/training/sessions">
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
                                    <a href="/admin/mentorship/sessions?show=all">
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
                                    <a href="/admin/show-builder">
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title align-items-center">
                                        <i class='bx bx-card font-medium-4 mr-1'></i> {{ __("admin.Latest 5 Cycles") }}
                                        :
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="card ">
                                        <div class="table-responsive">
                                            <table class="table" id="">
                                                <thead class="thead-dark">
                                                <tr>
                                                    <th class="text-center">{{__('cycles.Title')}}</th>
                                                    <th class="text-center">{{__('cycles.Valid from')}}</th>
                                                    <th class="text-center">{{__('cycles.Valid to')}}</th>
                                                    <th class="text-center">{{__('admin.Startups')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($latest_cycles as $cycle)
                                                    <tr>
                                                        <td class="text-center"> {{ $cycle->title }} </td>
                                                        <td class="text-center"> {{ $cycle->start->format('d-m-Y') }} </td>
                                                        <td class="text-center"> {{ $cycle->end->format('d-m-Y') }} </td>
                                                        <td class="text-center">
                                                            <button class="btn btn-light-info"
                                                                    onclick="window.location.href='/admin/cycles/{{$cycle->id}}/registered'">
                                                                <i class="bx bxs-user"></i>
                                                            </button>
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

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header border-bottom">
                                    <h4 class="card-title align-items-center">
                                        <i class='bx bx-card font-medium-4 mr-1'></i> {{ __("admin.Latest 5 Mentorship Requests") }} :
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="card ">
                                        <div class="table-responsive">
                                            <table class="table mb-0" id="userstable">
                                                <thead class="thead-dark">
                                                <tr>
                                                    <th>{{__('cycles.Company')}}</th>
                                                    <th>{{__('cycles.User/Title')}}</th>
                                                    <th>{{__('cycles.Mentor')}}</th>
                                                    <th>{{__('cycles.Date/Time')}}</th>
                                                    <th>{{__('cycles.Actions')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($mentorshipRequests as $session)
                                                    <tr>
                                                        <td>
                                                            <div class="media align-items-center">
                                                                <a class="media-left mr-50" href="/admin/companyProfile/{{$session->user->company->id}}">
                                                                    <img src="/191014/{{$session->user->company->logo??'../app-assets/images/logo_placeholder.png'}}" alt="avatar" class="rounded-circle" height="40" width="40">
                                                                </a>
                                                                <a href="/admin/companyProfile/{{$session->user->company->id}}">
                                                                    <div class="media align-items-center">
                                                                        {{$session->user->company->{'name_'.$l} }}
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="media align-items-center">
                                                                <a class="media-left mr-50" href="/admin/users/{{$session->user->company->user->id}}/edit">
                                                                    <img src="/191014/{{$session->user->img_url??'../images/placeholder.jpg'}}" alt="avatar" class="rounded-circle" height="40" width="40">
                                                                </a>
                                                                <a href="/admin/users/{{$session->user_id}}/edit">
                                                                    <div class="media-body">
                                                                        <h6 class="media-heading mb-0">{{$session->user->{'first_name_'.$l} }} {{$session->user->{'last_name_'.$l} }}</h6>
                                                                        <span class="font-small-2">{{$session->user->{'title_'.$l} }}</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div style="cursor: pointer;" class="chip mr-1" onclick="mentor({{$session->mentor_id}},'{{$session->mentor->{'first_name_'.$l} .' '. $session->mentor->{'last_name_'.$l} }}','{{$session->mentor->mrating()}}')">
                                                                <div class="chip-body">
                                                                    <div class="avatar">
                                                                        <img class="img-fluid" src="/191014/{{$session->mentor->img_url??'users/avatar.jpg'}}" alt="Mentor photo" height="20" width="20">
                                                                    </div>
                                                                    <span class="chip-text">
{{$session->mentor->{'first_name_'.$l} .' '. $session->mentor->{'last_name_'.$l} }}
                                    </span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            {{$session->zoom_date}}
                                                            <br>({{$session->period}}M)
                                                        </td>


                                                        <td>
                                                            <button title="Approve" onclick="approve({{$session->id}},'Yes')" type="button" class="btn btn-icon rounded-circle btn-light-success">
                                                                    <i class="bx bx-check-square"></i></button>
                                                            <button title="Dispprove" onclick="approve({{$session->id}}, 'No')" type="button" class="btn btn-icon rounded-circle btn-light-danger">
                                                                    <i class="bx bxs-x-square"></i></button>
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
        <script>
            function approve(id,stat){
                // prompt('Please Wait ! Sending Request ........')
                $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
                $.ajax({
                    type: "POST", url: "/admin/mentors/modify",
                    data: {"id": id,"stat": stat,'rtype':6,},
                    success: function (response) {
                        location.reload();
                    },
                    error: function (error) {
                        console.log(error);
                        alert("Error Occurred, Try again!");
                    }});
            }
        </script>

@stop
