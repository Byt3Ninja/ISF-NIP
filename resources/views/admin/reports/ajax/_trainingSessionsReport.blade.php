<div class="col-12">
    <div class="row">
        <div class="col-sm-6 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/training/sessions">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-success mx-auto mb-50">
                                <i class="bx bxs-spreadsheet font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.Training sessions')}}</div>
                            <h3 class="mb-0">{{$total_sessions_count}}</h3>
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
                            <div class="badge-circle badge-circle-lg badge-circle-warning mx-auto mb-50">
                                <i class="bx bxs-select-multiple font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.Training sessions Duration')}}</div>
                            <h3 class="mb-0">{{$sessions_period}}</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/training/sessions">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-info mx-auto mb-50">
                                <i class="bx bxs-slideshow font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.Zoom Training Sessions Count')}}</div>
                            <h3 class="mb-0">{{$zoom_sessions_count}}</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/training/sessions">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-secondary mx-auto mb-50">
                                <i class="bx bxs-calendar-event font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.Offline Training Sessions Count')}}</div>
                            <h3 class="mb-0">{{$offline_sessions_count}} </h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-12 dashboard-users-danger">
            <div class="card text-center">
                <div class="card-content">
                    <a href="/dashboard/training/sessions">
                        <div class="card-body py-1">
                            <div class="badge-circle badge-circle-lg badge-circle-danger mx-auto mb-50">
                                <i class="bx bxs-save font-medium-5"></i>
                            </div>
                            <div class="text-muted line-ellipsis">{{__('admin.Online Training Sessions Count')}}</div>
                            <h3 class="mb-0">{{$online_sessions_count}}</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

