<div class="col-12">
  <div class="row g-6">
    <!-- Card Border Shadow -->
    <a href="/dashboard/users#search=Mentor" class="col-lg-3 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-school ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $mentors_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Mentors') }}</p>
        </div>
      </div>
    </a>
    <a href="/dashboard/users#search=Mentor" class="col-lg-3 col-sm-6">
      <div class="card card-border-shadow-secondary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="ti ti-search ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $cycle_mentors_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Mentors In Cycle') }}</p>
        </div>
      </div>
    </a>
    <a href="/dashboard/mentorship/sessions?show=all" class="col-lg-3 col-sm-6">
      <div class="card card-border-shadow-warning h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti ti-calendar-event ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $mentorships_period }} </h4>
          </div>
          <p class="mb-1">{{ __('admin.Mentorship requests') }}</p>
        </div>
      </div>
    </a>
    <a href="/dashboard/mentorship/sessions?show=all" class="col-lg-3 col-sm-6">
      <div class="card card-border-shadow-secondary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="ti ti-clock ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $total_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Mentorship requests Duration') }}</p>
        </div>
      </div>
    </a>
    <a href="/dashboard/mentorship/sessions?show=approved" class="col-lg-3 col-sm-6">
      <div class="card card-border-shadow-success h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti ti-check ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $approved_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('cycles.approved requests') }}</p>
        </div>
      </div>
    </a>
    <a href="/dashboard/mentorship/sessions?show=disapproved" class="col-lg-3 col-sm-6">
      <div class="card card-border-shadow-danger h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="ti ti-x ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $disapproved_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('cycles.disapproved requests') }}</p>
        </div>
      </div>
    </a>
    <a href="/dashboard/mentorship/sessions" class="col-lg-3 col-sm-6">
      <div class="card card-border-shadow-warning h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti ti-calendar-event ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $new_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('cycles.New requests only') }}</p>
        </div>
      </div>
    </a>
    <a href="/dashboard/mentorship/sessions" class="col-lg-3 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-archive ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $done_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Mentorship done Request') }}</p>
        </div>
      </div>
    </a>
    <a href="/dashboard/mentorship/sessions" class="col-lg-3 col-sm-6">
      <div class="card card-border-shadow-dark h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-dark">
                <i class="ti ti-calendar-event ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $pending_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Mentorship pending Request') }}</p>
        </div>
      </div>
    </a>
  </div>
</div>

<div class="col-12 mt-10">
  <div class="row">
    <div class="col-md-12">
      <div>
        <h4 class="card-title mb-4">
          <i class="ti ti-chart-donut ti-28px"></i>
          <span class="mx-2">{{ __('admin.Mentors In Cycle Statistic') }}:</span>
        </h4>
        <div class="card-body">
          <div class="card">
            <div class="table-responsive">
              <table class="table" id="userstable">
                <thead class="bg-label-secondary">
                  <tr>
                    <th class="text-center">{{ __('cycles.Mentor') }}</th>
                    <th class="text-center">{{ __('cycles.Rate') }}</th>
                    <th class="text-center">{{ __('admin.Mentorship requests number') }}</th>
                    <th class="text-center">{{ __('admin.Mentorship requests Duration') }}</th>
                    <th class="text-center">{{ __('admin.Mentorship done Request') }}</th>
                    <th class="text-center">{{ __('admin.Mentorship done requests Duration') }}</th>
                    <th class="text-center">{{ __('admin.Company Attendance Rate') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($cycle_mentors as $mentor)
                    <tr>
                      <td class="text-center">
                        <div class="d-flex align-items-center">
                          <div class="media-left mr-50">
                            <img 
                              src="{{ $mentor->image_url }}" alt="avatar" class="rounded-circle"
                              height="40" width="40" 
                            />
                            {{ $mentor->name }}
                          </div>
                        </div>
                      </td>
                      <td class="text-center">
                        <small>{!! str_repeat('<i class="bx bxs-star" style="color: #fc960f; ti-28px"></i>', $mentor->mrating()) !!}</small>
                      </td>
                      <td class="text-center">
                        {{ $mentor->mentorSessions()->where('approved', 'Yes')->count() }}
                      </td>
                      <td class="text-center">
                        {{ $mentor->mentorSessions()->where('approved', 'Yes')->sum('period') / 60 }}
                      </td>
                      <td class="text-center">
                        {{ $mentor->mentorSessions()->whereDate('zoom_date', '<', now()->format('Y-m-d'))->where('approved', 'Yes')->count() }}
                      </td>
                      <td class="text-center">
                        {{ $mentor->mentorSessions()->whereDate('zoom_date', '<', now()->format('Y-m-d'))->where('approved', 'Yes')->sum('period') / 60 }}
                      </td>
                      <td class="text-center">
                        @if ($mentor->mentorSessions()->whereNotNull('start_url')->whereDate('zoom_date', '<=', now()->format('Y-m-d'))->count())
                          {{ (int) (($mentor->mentorSessions()->whereNotNull('start_url')->whereDate('zoom_date', '<=', now()->format('Y-m-d'))->sum('done') / $mentor->mentorSessions()->whereNotNull('start_url')->whereDate('zoom_date', '<=', now()->format('Y-m-d'))->count()) * 100) }}
                          %
                        @else
                          0%
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
