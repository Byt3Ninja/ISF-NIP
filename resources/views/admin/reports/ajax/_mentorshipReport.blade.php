<div class="col-12">
  <div class="row g-6">
    <a href="/dashboard/users#search=Mentor" class="col-lg-3 col-sm-6">
      <div class="card card-border-shadow-label-danger h-100">
        <div class="card-body">
          <div class="d-flex justify-content-center align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-school text-label-danger ti-28px"></i>
              </span>
            </div>
            <div class="text-muted line-ellipsis">{{ __('admin.Mentors') }}</div>
          </div>
          <p class="mb-1 text-center">{{ $mentors_count }}</p>
        </div>
      </div>
    </a>

    <div class="col-sm-3 col-12 dashboard-users-danger">
      <div class="card card-border-shadow-label-danger h-100">
        <div class="card-content">
          <a href="/dashboard/users#search=Mentor">
            <div class="card-body py-1">
              <div class="badge-circle badge-circle-lg badge-circle-label-warning mx-auto mb-50">
                <i class="ti ti-select font-medium-5 ti-28px"></i>
              </div>
              <div class="text-muted line-ellipsis">{{ __('admin.Mentors In Cycle') }}</div>
             < class="mb-1 text-center">{{ $cycle_mentors_count }}</>
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="col-sm-3 col-12 dashboard-users-danger">
      <div class="card card-border-shadow-label-danger h-100">
        <div class="card-content">
          <a href="/dashboard/mentorship/sessions?show=all">
            <div class="card-body py-1">
              <div class="badge-circle badge-circle-lg badge-circle-label-info mx-auto mb-50">
                <i class="ti ti-book font-medium-5 ti-28px"></i>
              </div>
              <div class="text-muted line-ellipsis">{{ __('admin.Mentorship requests') }}</div>
             < class="mb-1 text-center">{{ $total_sessions_count }}</>
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="col-sm-3 col-12 dashboard-users-danger">
      <div class="card card-border-shadow-label-danger h-100">
        <div class="card-content">
          <a href="/dashboard/mentorship/sessions?show=all">
            <div class="card-body py-1">
              <div class="badge-circle badge-circle-lg badge-circle-label-secondary mx-auto mb-50">
                <i class="ti ti-calendar-event font-medium-5 ti-28px"></i>
              </div>
              <div class="text-muted line-ellipsis">{{ __('admin.Mentorship requests Duration') }}</div>
             < class="mb-1 text-center">{{ $mentorships_period }} </>
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="col-sm-4 col-12 dashboard-users-danger">
      <div class="card card-border-shadow-label-danger h-100">
        <div class="card-content">
          <a href="/dashboard/mentorship/sessions?show=approved">
            <div class="card-body py-1">
              <div class="badge-circle badge-circle-lg badge-circle-success mx-auto mb-50">
                <i class="ti ti-check font-medium-5 ti-28px"></i>
              </div>
              <div class="text-muted line-ellipsis">{{ __('cycles.approved requests') }}</div>
             < class="mb-1 text-center">{{ $approved_sessions_count }}</>
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="col-sm-4 col-12 dashboard-users-danger">
      <div class="card card-border-shadow-label-danger h-100">
        <div class="card-content">
          <a href="/dashboard/mentorship/sessions?show=disapproved">
            <div class="card-body py-1">
              <div class="badge-circle badge-circle-lg badge-circle-danger mx-auto mb-50">
                <i class="ti ti-trash font-medium-5 ti-28px"></i>
              </div>
              <div class="text-muted line-ellipsis">{{ __('cycles.disapproved requests') }}</div>
             < class="mb-1 text-center">{{ $disapproved_sessions_count }}</>
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="col-sm-4 col-12 dashboard-users-danger">
      <div class="card card-border-shadow-label-danger h-100">
        <div class="card-content">
          <a href="/dashboard/mentorship/sessions">
            <div class="card-body py-1">
              <div class="badge-circle badge-circle-lg badge-circle-warning mx-auto mb-50">
                <i class="ti ti-star font-medium-5 ti-28px"></i>
              </div>
              <div class="text-muted line-ellipsis">{{ __('cycles.New requests only') }}</div>
             < class="mb-1 text-center">{{ $new_sessions_count }}</>
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-12 dashboard-users-danger">
      <div class="card card-border-shadow-label-danger h-100">
        <div class="card-content">
          <a href="/dashboard/mentorship/sessions">
            <div class="card-body py-1">
              <div class="badge-circle badge-circle-lg badge-circle-info mx-auto mb-50">
                <i class="ti ti-archive font-medium-5 ti-28px"></i>
              </div>
              <div class="text-muted line-ellipsis">{{ __('admin.Mentorship done Request') }}</div>
             < class="mb-1 text-center">{{ $done_sessions_count }}</>
            </div>
          </a>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-12 dashboard-users-danger">
      <div class="card card-border-shadow-label-danger h-100">
        <div class="card-content">
          <a href="/dashboard/mentorship/sessions">
            <div class="card-body py-1">
              <div class="badge-circle badge-circle-lg badge-circle-secondary mx-auto mb-50">
                <i class="ti ti-clock font-medium-5 ti-28px"></i>
              </div>
              <div class="text-muted line-ellipsis">{{ __('admin.Mentorship pending Request') }}</div>
             < class="mb-1 text-center">{{ $pending_sessions_count }}</>
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
            <i class='bx bx-card font-medium-4 mr-1'></i> {{ __('admin.Mentors In Cycle Statistic') }}
            :
          </h4>
        </div>
        <div class="card-body">
          <div class="card ">
            <div class="table-responsive">
              <table class="table mb-0" id="userstable">
                <thead class="thead-dark">
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
                        <div class="media align-items-center">
                          <div class="media-left mr-50">
                            <img src="{{ $mentor->image_url }}" alt="avatar" class="rounded-circle"
                              height="40" width="40">
                          </div>
                          <div class="media align-items-center">
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
