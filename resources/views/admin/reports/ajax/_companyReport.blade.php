<div class="col-md-12">
    <a href="/dashboard/companyProfile/{{ $company->id }}">
    <!-- users view card details start -->
    <div class="card">
        <div class="card-header border-bottom">

            <h4 class="card-title d-flex align-items-center mb-1">
                <img src="{{$company->cycledata->organization->image_url}}" alt="avatar" class="rounded-circle"
                    height="40" width="40">
             {{$company->name }}
            </h4>
            <div style="cursor: pointer;" class="chip mr-1">
                                                <div class="chip-body">
                                                    <div class="avatar">
                                                        <img class="img-fluid" src="{{$company->user->image_url}}"
                                                             alt="Mentor photo" height="20" width="20">
                                                    </div>
                                                    <span class="chip-text">
                                {{$company->user->name}}
                                    </span>
                    </div>
            </div>
            
            <span class=" d-flex align-items-center mt-1">{{$company->cycledata->title }} ( {{ __('dashboard.start from').": " .$company->cycledata->start_format. " ".__('dashboard.to').": " .$company->cycledata->end_format  }} ) - {{$company->stepdata->title }}</span>
            <div class="media align-items-center mt-2">
                <div class="media-left mr-50">
                    <img src="{{$company->cycledata->organization->image_url}}" alt="avatar" class="rounded-circle"
                         height="40" width="40">
                </div>
                <div class="media align-items-center">
                    {{$company->cycledata->organization->name }}
                </div>
            </div>
        </div>
    </div>
    </a>
    <!-- users view card details ends -->
</div>
<div class="col-12">
    <div class="row">


        <div class="col-sm-4 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/mentorship/sessions?show=all">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-light-primary mx-auto mb-50">
                                <i class="bx bxs-book font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.Mentorship requests')}}</div>
                            <h3 class="mb-0">{{$mentorRequests_count}}</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/mentorship/sessions?show=all">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-light-secondary mx-auto mb-50">
                                <i class="bx bxs-calendar-event font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.Mentorship requests Duration')}}</div>
                            <h3 class="mb-0">{{$mentorships_period}} </h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/mentorship/sessions?show=all">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-light-danger mx-auto mb-50">
                                <i class="bx bxs-book-open font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.Company Attendance Rate')}}</div>
                            <h3 class="mb-0">{{(integer)$mentorship_attendance_rate}} % </h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/training/sessions">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-light-warning mx-auto mb-50">
                                <i class="bx bxs-book-bookmark font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.Training sessions')}}</div>
                            <h3 class="mb-0">{{$training_sessions_count}}</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/training/sessions">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-light-success mx-auto mb-50">
                                <i class="bx bxs-timer font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.Training sessions Duration')}}</div>
                            <h3 class="mb-0">{{$training_sessions_period}}</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>


    </div>
</div>
<div class="col-12">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title align-items-center">
                        <i class='bx bx-card font-medium-4 mr-1'></i> {{ __('admin.Mentorship requests') }}
                        :
                    </h4>
                </div>
                <div class="card-body">
                    <div class="card ">
                        <div class="table-responsive">
                            <table class="table mb-0" id="userstable">
                                <thead class="thead-dark">
                                <tr>
                                    <th>{{__('cycles.Mentor')}}</th>
                                    <th>{{__('cycles.Rate/Comment')}}</th>
                                    <th>{{__('cycles.Date/Time')}}</th>
                                    <th>{{__('cycles.Approved')}}</th>
                                    <th>{{__('cycles.Attendance')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($mentorship_sessions as $session)
                                    <tr>
                                        <td>
                                            <div style="cursor: pointer;" class="chip mr-1">
                                                <div class="chip-body">
                                                    <div class="avatar">
                                                        <img class="img-fluid" src="{{$session->mentor->image_url}}"
                                                             alt="Mentor photo" height="20" width="20">
                                                    </div>
                                                    <span class="chip-text">
                                {{$session->mentor->name}}
                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small>{!!  $session->rate_comment?
                                         str_repeat('<i class="bx bxs-star" style="color: #fc960f;"></i>',$session->rating).
                                    '<br>'.$session->rate_comment:$session->comment  !!}</small>
                                        </td>
                                        <td>
                                            {{$session->zoom_date}}
                                            <br>({{$session->period}}M)
                                        </td>
                                        <td>
                                            <div class="media align-items-center">
                                                {!!  __($session->approved) ??'<span class="warning">'.__('Waiting').'</span>'  !!}
                                            </div>
                                        </td>
                                        <td>
                                            {{$session->done?__('Yes'):__('No')}}
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
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title align-items-center">
                        <i class='bx bx-card font-medium-4 mr-1'></i> {{__('admin.Training sessions')}}
                        :
                    </h4>
                </div>
                <div class="card-body">
                    <div class="card ">
                        <div class="table-responsive">
                            <table class="table mb-0" id="userstable">
                                <thead class="thead-dark">
                                <tr>
                                    <th>{{__('cycles.Type')}}</th>
                                    <th>{{__('cycles.Title')}}</th>
                                    <th>{{__('cycles.Start')}}</th>
                                    <th>{{__('cycles.End')}}</th>
                                    <th>{{__('cycles.details')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($training_sessions as $session)
                                    <tr>
                                        <td>{{__('cycles.'.$session->type)}}</td>
                                        <td>{{$session->title}}</td>
                                        <td>{{$session->datetime}}</td>
                                        <td>{{$session->enddatetime??'-'}}</td>
                                        <td>
                                            @if ($session->type=='sessions')
                                                {{__('cycles.Duration')}}:  {{$session->duration}} {{__('cycles.Minutes')}}
                                            @elseif ($session->type=='offline')
                                                <b>{{__('cycles.trainer name')}}:</b> {{$session->trainer_name}}<br><b>{{__('cycles.training location')}}:</b> {{$session->location}} <br>
                                            @else
                                                <a href="/dashboard/training/all/{{$session->location}}">{{ optional($session->course)->title }} </a>
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
</div>