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
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title align-items-center">
                        <i class='bx bx-card font-medium-4 mr-1'></i> {{__('dashboard.Final pending screening')}}
                        :
                    </h4>
                </div>
                <div class="card-body">
                    <div class="card ">
                        <div class="table-responsive">
                            <table class="table mb-0" id="userstable">
                                <thead class="thead-dark">
                                <tr>
                                    <th class="text-center">{{__('cycles.Company')}}</th>
                                    <th class="text-center">{{__('admin.Organization')}}</th>
                                    <th class="text-center">{{__('cycles.Cycle')}}</th>
                                    <th class="text-center">{{__('cycles.Step Title')}}</th>
                                    <th class="text-center">{{__('admin.Judgers')}}</th>

                                </tr>
                                </thead>
                                <tbody>
                                @foreach( $final_screening_companies as $company)
                                    <tr>
                                        <td class="text-center">
                                            <div class="media align-items-center">
                                                <div class="media-left mr-50">
                                                    <img src="{{$company->logo_url}}"
                                                         alt="avatar" class="rounded-circle" height="40"
                                                         width="40">
                                                </div>
                                                <div class="media align-items-center">
                                                    {{$company->name }}
                                                </div>

                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ $company->cycledata->organization->name }}
                                        </td>
                                        <td class="text-center">
                                            {{ $company->cycledata->title }}
                                        </td>
                                        <td class="text-center">
                                            {{ $company->stepdata->title }}
                                        </td>
                                        <td class="text-center">
                                            @foreach(\App\User::whereIn('id', $company->finals($company->stepdata->id) )->get() as $judge)
                                                <div class="chip mr-1">
                                                    <div class="chip-body">
                                                        <div class="avatar">
                                                            <img class="img-fluid" src="{{$judge->image_url}}" alt="click to view" height="20" width="20">
                                                        </div>
                                                        <span class="chip-text">{{$judge->name }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
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
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title align-items-center">
                        <i class='bx bx-card font-medium-4 mr-1'></i> {{__('dashboard.screening pending sessions')}}
                        :
                    </h4>
                </div>
                <div class="card-body">
                    <div class="card ">
                        <div class="table-responsive">
                            <table class="table mb-0" id="userstable">
                                <thead class="thead-dark">
                                <tr>
                                    <th class="text-center">{{__('cycles.Company')}}</th>
                                    <th class="text-center">{{__('admin.Organization')}}</th>
                                    <th class="text-center">{{__('cycles.Cycle')}}</th>
                                    <th class="text-center">{{__('cycles.Step Title')}}</th>
                                    <th class="text-center">{{__('admin.Judgers')}}</th>

                                </tr>
                                </thead>
                                <tbody>
                                @foreach( $screening_companies as $company)
                                    <tr>
                                        <td class="text-center">
                                            <div class="media align-items-center">
                                                <div class="media-left mr-50">
                                                    <img src="{{$company->logo_url}}"
                                                         alt="avatar" class="rounded-circle" height="40"
                                                         width="40">
                                                </div>
                                                <div class="media align-items-center">
                                                    {{$company->name }}
                                                </div>

                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {{ $company->cycledata->organization->name }}
                                        </td>
                                        <td class="text-center">
                                            {{ $company->cycledata->title }}
                                        </td>
                                        <td class="text-center">
                                            {{ $company->stepdata->title }}
                                        </td>
                                        <td class="text-center">
                                            @foreach($company->stepdata->stepFiltrationJudges()->get() as $judge)
                                                <div class="chip mr-1" data-toggle="tooltip" data-placement="bottom"
                                                     title=""
                                                     data-original-title="{{$judge->phone}} {{$judge->email}}">
                                                    <div class="chip-body">
                                                        <div class="avatar">
                                                            <img class="img-fluid"
                                                                 src="{{$judge->image_url}}"
                                                                 alt="click to view" height="20" width="20">
                                                        </div>
                                                        <span class="chip-text">{{ $judge->name }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
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
