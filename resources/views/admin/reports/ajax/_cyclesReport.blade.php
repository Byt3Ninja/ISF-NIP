<div class="col-md-12">
  <!-- users view card details start -->
  <div class="card">
    <div class="card-header border-bottom">
      <h4 class="card-title d-flex align-items-center mb-3">
        <i class='ti ti-briefcase ti-28px'></i> 
        <span class="mx-2">{{ $cycle->title }}</span>
      </h4>
      <span>
        {{ __('dashboard.start from') . ': ' . $cycle->start_format . ' ' . __('dashboard.to') . ': ' . $cycle->end_format }}
      </span>

      <div class="d-flex align-items-center mt-2">
        <div class="media-left mr-50">
          <img 
            src="{{ $cycle->organization->image_url }}"
            alt="avatar"
            class="rounded-circle"
            height="40"
            width="40"
          />
        </div>
        <span class="mx-2">
          {{ $cycle->organization->name }}
        </span>
      </div>
    </div>
  </div>
  <!-- users view card details ends -->
</div>

<div class="col-12 mt-6">
  <div class="row g-6">
    <a href="dashboard/cycles/{{ $cycle->id }}/registered" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-building ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $startups }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Subscribe Startups in Cycle') }}</p>
        </div>
      </div>
    </a>

  <a href="{{ route('mentors.list', ['enrolledInProgramFilter' => 1, 'programFilter' => $cycle->id]) }}" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-warning h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti ti-school ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $mentors_count }}</h4>
          </div>
          <p class="mb-1">{{ __('cycles.Mentors_Enrolled_In_Programs') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/mentorship/sessions?show=all" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-success h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti ti-book ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $mentorRequests_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Mentorship requests') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/mentorship/sessions?show=all" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-danger h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="ti ti-clock ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $mentorships_period }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Mentorship requests Duration') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/mentorship/sessions?show=all" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-secondary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="ti ti-browser ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ (int) $mentorship_attendance_rate }} %</h4>
          </div>
          <p class="mb-1">{{ __('admin.Company Attendance Rate') }}</p>
        </div>
      </div>
    </a>

    <a href="{{ route('trainings.sessions_report', ['cycle' => $cycle->id]) }}" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-warning h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti ti-user-screen ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $training_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Training sessions') }}</p>
        </div>
      </div>
    </a>

    <a href="{{ route('trainings.sessions_report', ['cycle' => $cycle->id]) }}" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-clock ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $training_sessions_period }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Training sessions Duration') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/screening/results" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-success h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti ti-rocket ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $screening_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('dashboard.First screening') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/final/results" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-telescope ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $final_screening_count }}</h4>
          </div>
          <p class="mb-1">{{ __('dashboard.Final screening') }}</p>
        </div>
      </div>
    </a>
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
{{--                                            <div class="d-flex align-items-center gap-1">--}}
{{--                                                <div class="media-left mr-50">--}}
{{--                                                    <img src="{{$mentor->image_url}}"--}}
{{--                                                         alt="avatar" class="rounded-circle" height="40"--}}
{{--                                                         width="40">--}}
{{--                                                </div>--}}
{{--                                                <div class="d-flex align-items-center gap-1">--}}
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