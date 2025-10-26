<div class="col-md-12">
  <a href="/dashboard/companyProfile/{{ $company->id }}">
    <!-- users view card details start -->
    <div class="card">
      <div class="card-header border-bottom">
        <h4 class="d-flex align-items-center mb-1">
          <img src="{{ $company->cycledata->organization->image_url }}" alt="avatar" class="rounded-circle" height="40"
            width="40" />
          <span class="mx-2">{{ $company->name }}</span>
        </h4>
        <div style="cursor: pointer;" class="chip mr-1">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <img class="img-fluid" src="{{ $company->user->image_url }}" alt="Mentor photo" height="20"
                width="20" />
            </div>
            <span class="mx-2">
              {{ $company->user->name }}
            </span>
          </div>
        </div>

        <span class=" d-flex align-items-center mt-1">{{ $company->cycledata->title }} (
          {{ __('dashboard.start from') . ': ' . $company->cycledata->start_format . ' ' . __('dashboard.to') . ': ' . $company->cycledata->end_format }}
          ) - {{ $company->stepdata->title }}</span>
        <div class="d-flex align-items-center mt-2">
          <div class="media-left mr-50">
            <img src="{{ $company->cycledata->organization->image_url }}" alt="avatar" class="rounded-circle"
              height="40" width="40" />
          </div>
          <div class="mx-2">
            {{ $company->cycledata->organization->name }}
          </div>
        </div>
      </div>
    </div>
  </a>
  <!-- users view card details ends -->
</div>

<div class="col-12 mt-6">
  <div class="row g-6">
    <a href="#mentorshipSessionsSection" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-telescope ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $mentorRequests_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Mentorship Sessions') }}</p>
        </div>
      </div>
    </a>

    <a href="#mentorshipSessionsSection" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="ti ti-calendar-event ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $mentorships_period }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Mentorship Sessions Duration') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/mentorship/sessions?show=all" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="ti ti-user-screen ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ (int) $mentorship_attendance_rate }} %</h4>
          </div>
          <p class="mb-1">{{ __('admin.Company Attendance Rate') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/training/sessions" class="col-lg-6 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti ti-book ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $training_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Training sessions') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/training/sessions" class="col-lg-6 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti ti-clock ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $training_sessions_period }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Training sessions Duration') }}</p>
        </div>
      </div>
    </a>
  </div>
</div>

<div class="col-12 mt-10">
  <div class="row">
    <div class="col-md-12">
      <div>
        <h4 class="card-title align-items-center mb-3">
          <i class='ti ti-card ti-28px'></i>
          <span class="mx-2">{{ __('admin.Mentorship Sessions') }}:</span>
        </h4>
        <div id="mentorshipSessionsSection" class="card-body" style="height: 600px; overflow-y: auto;">
          @if($mentorship_sessions->count())
            <div class="card">
              <div class="table-responsive">
                <table class="table mb-0" id="mentorshipSessionsTable">
                  <thead class="bg-label-secondary">
                    <tr>
                      <th>{{ __('cycles.Mentor') }}</th>
                      <th>{{ __('cycles.Rate/Comment') }}</th>
                      <th>{{ __('cycles.Date/Time') }}</th>
                      <th>{{ __('cycles.Approved') }}</th>
                      <th>{{ __('cycles.Attendance') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($mentorship_sessions as $session)
                      <tr>
                        <td>
                          <div style="cursor: pointer;" class="chip mr-1">
                            <div class="d-flex align-items-center">
                              <div class="avatar">
                                <img class="img-fluid" src="{{ $session->mentor->image_url }}" alt="pic"
                                  height="20" width="20">
                              </div>
                              <span class="mx-2">
                                {{ $session->mentor->name }}
                              </span>
                            </div>
                          </div>
                        </td>
                        <td>
                          <small>{!! $session->rate_comment
                              ? str_repeat('<i class="bx bxs-star" style="color: #fc960f;"></i>', $session->rating) .
                                  '<br>' .
                                  $session->rate_comment
                              : $session->comment !!}</small>
                        </td>
                        <td>
                          {{ $session->zoom_date }}
                          <br>({{ $session->period }}M)
                        </td>
                        <td>
                          <div class="d-flex align-items-center gap-1">
                            {!! __($session->approved) ?? '<span class="warning">' . __('Waiting') . '</span>' !!}
                          </div>
                        </td>
                        <td>
                          {{ $session->done ? __('Yes') : __('No') }}
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @else
            <div class="text-center py-5">
              <i class="ti ti-calendar-x text-secondary" style="font-size: 3rem;"></i>
              <p class="mt-3 mb-0">{{ __('admin.No_Mentorship_Sessions_Startup') }}</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<div class="col-md-12 mt-6">
  <div>
    <h4 class="card-title align-items-center mb-3">
      <i class='ti ti-card ti-28px'></i>
      <span class="mx-2">{{ __('admin.Training sessions') }}:</span>
    </h4>
    <div class="card-body">
      <div class="card ">
        <div class="table-responsive">
          <table class="table mb-0" id="userstable">
            <thead class="bg-label-secondary">
              <tr>
                <th>{{ __('cycles.Type') }}</th>
                <th>{{ __('cycles.Title') }}</th>
                <th>{{ __('cycles.Start') }}</th>
                <th>{{ __('cycles.End') }}</th>
                <th>{{ __('cycles.details') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($training_sessions as $session)
                <tr>
                  <td>{{ __('cycles.' . $session->type) }}</td>
                  <td>{{ $session->title }}</td>
                  <td>{{ $session->datetime }}</td>
                  <td>{{ $session->enddatetime ?? '-' }}</td>
                  <td>
                    @if ($session->type == 'sessions')
                      {{ __('cycles.Duration') }}: {{ $session->duration }} {{ __('cycles.Minutes') }}
                    @elseif ($session->type == 'offline')
                      <b>{{ __('cycles.trainer name') }}:</b>
                      {{ $session->trainer_name }}<br><b>{{ __('cycles.training location') }}:</b>
                      {{ $session->location }} <br>
                    @else
                      <a href="/dashboard/training/all/{{ $session->location }}">{{ optional($session->course)->title }}
                      </a>
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
