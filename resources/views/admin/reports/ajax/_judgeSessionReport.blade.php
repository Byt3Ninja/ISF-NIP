<div class="col-12">
    <div class="row">
        <div class="col-sm-4 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/users#search=Judger">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-info mx-auto mb-50">
                                <i class="bx bxs-crown font-medium-5"></i>
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
                    <a href="/dashboard/screening/results">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-warning mx-auto mb-50">
                                <i class="bx bx-badge-check font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('dashboard.First screening')}}</div>
                            <h3 class="mb-0">{{$screening_sessions_count}}</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/final/results">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-secondary mx-auto mb-50">
                                <i class="bx bx-rocket font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('dashboard.Final screening')}}</div>
                            <h3 class="mb-0">{{$final_screening_count}}</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/final/results">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-light-success mx-auto mb-50">
                                <i class="bx bxs-save font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.Final pending screening')}}</div>
                            <h3 class="mb-0">{{$final_pending_screening_count}}</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/final/results">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-light-primary mx-auto mb-50">
                                <i class="bx bxs-award font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.Final done screening')}}</div>
                            <h3 class="mb-0">{{$final_done_screening_count}}</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/final/results">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-light-danger mx-auto mb-50">
                                <i class="bx bxs-star font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.screening pending sessions')}}</div>
                            <h3 class="mb-0">{{$screening_sessions_count}}</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/screening/results">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-light-warning mx-auto mb-50">
                                <i class="bx bxs-file-doc font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.screening done sessions')}}</div>
                            <h3 class="mb-0">{{$final_done_screening_count}}</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>



    </div>
</div>
{{--<div class="col-12">--}}
{{--    <div class="row">--}}
{{--        <div class="col-md-12">--}}
{{--            <div class="card">--}}
{{--                <div class="card-header border-bottom">--}}
{{--                    <h4 class="card-title align-items-center">--}}
{{--                        <i class='bx bx-card font-medium-4 mr-1'></i> {{ __("admin.Mentors In Cycle Statistic") }}--}}
{{--                        :--}}
{{--                    </h4>--}}
{{--                </div>--}}
{{--                <div class="card-body">--}}
{{--                    <div class="card ">--}}
{{--                        <div class="table-responsive">--}}
{{--                            <table class="table mb-0" id="userstable">--}}
{{--                                <thead class="thead-dark">--}}
{{--                                <tr>--}}
{{--                                    <th class="text-center">{{__('cycles.Mentor')}}</th>--}}
{{--                                    <th class="text-center">{{__('cycles.Rate')}}</th>--}}
{{--                                    <th class="text-center">{{__('admin.Mentorship requests number')}}</th>--}}
{{--                                    <th class="text-center">{{__('admin.Mentorship requests Duration')}}</th>--}}
{{--                                    <th class="text-center">{{__('admin.Mentorship done Request')}}</th>--}}
{{--                                    <th class="text-center">{{__('admin.Mentorship done requests Duration')}}</th>--}}
{{--                                    <th class="text-center">{{__('admin.Company Attendance Rate')}}</th>--}}
{{--                                </tr>--}}
{{--                                </thead>--}}
{{--                                <tbody>--}}
{{--                                @foreach($cycle_mentors as $mentor)--}}
{{--                                    <tr>--}}
{{--                                        <td class="text-center">--}}
{{--                                            <div class="media align-items-center">--}}
{{--                                                <div class="media-left mr-50">--}}
{{--                                                    <img src="{{$mentor->image_url}}"--}}
{{--                                                         alt="avatar" class="rounded-circle" height="40"--}}
{{--                                                         width="40">--}}
{{--                                                </div>--}}
{{--                                                <div class="media align-items-center">--}}
{{--                                                    {{$mentor->name }}--}}
{{--                                                </div>--}}

{{--                                            </div>--}}
{{--                                        </td>--}}
{{--                                        <td class="text-center">--}}
{{--                                            <small>{!! str_repeat('<i class="bx bxs-star" style="color: #fc960f;"></i>',$mentor->mrating()) !!}</small>--}}
{{--                                        </td>--}}
{{--                                        <td class="text-center">--}}
{{--                                            {{$mentor->mentorSessions()->where('approved','Yes')->count()}}--}}
{{--                                        </td>--}}
{{--                                        <td class="text-center">--}}
{{--                                            {{$mentor->mentorSessions()->where('approved','Yes')->sum('period')/60}}--}}
{{--                                        </td>--}}
{{--                                        <td class="text-center">--}}
{{--                                            {{$mentor->mentorSessions()->whereDate('zoom_date','<',now()->format('Y-m-d'))->where('approved','Yes')->count()}}--}}
{{--                                        </td>--}}
{{--                                        <td class="text-center">--}}
{{--                                            {{$mentor->mentorSessions()->whereDate('zoom_date','<',now()->format('Y-m-d'))->where('approved','Yes')->sum('period')/60}}--}}
{{--                                        </td>--}}
{{--                                        <td class="text-center">--}}
{{--                                            {{ (integer)($mentor->mentorSessions()->whereNotNull('start_url')->whereDate('zoom_date','<=',now()->format('Y-m-d'))->sum('done')/$mentor->mentorSessions()->whereNotNull('start_url')->whereDate('zoom_date','<=',now()->format('Y-m-d'))->count()*100) }} %--}}
{{--                                        </td>--}}
{{--                                    </tr>--}}
{{--                                @endforeach--}}

{{--                                </tbody>--}}
{{--                            </table>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}