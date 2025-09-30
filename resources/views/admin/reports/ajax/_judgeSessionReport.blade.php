<div class="col-12">
  <div class="row g-6">
    <a href="{{ route('users.judger') }}" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-crown ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $judgers_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Judgers') }}</p>
        </div>
      </div>
    </a>

    <a href="{{ route('screening.results_report')}}" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-warning h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti ti-check ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $screening_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('dashboard.First screening') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/final/results" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-success h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti ti-rocket ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $final_screening_count }}</h4>
          </div>
          <p class="mb-1">{{ __('dashboard.Final screening') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/final/results" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-danger h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="ti ti-award ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $final_pending_screening_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Final pending screening') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/final/results" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-secondary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="ti ti-award ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $final_done_screening_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Final done screening') }}</p>
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
          <i class="ti ti-calendar-time ti-28px"></i>
          <span class="mx-2">{{ __('dashboard.Final pending screening') }}:</span>
        </h4>
        <div class="card-body">
          <div class="card ">
            <div class="table-responsive">
              <table class="table mb-0" id="userstable">
                <thead class="bg-label-secondary">
                  <tr>
                    <th class="text-center">{{ __('cycles.Company') }}</th>
                    <th class="text-center">{{ __('admin.Organization') }}</th>
                    <th class="text-center">{{ __('cycles.Cycle') }}</th>
                    <th class="text-center">{{ __('cycles.Step Title') }}</th>
                    <th class="text-center">{{ __('admin.Judgers') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($final_screening_companies as $company)
                    <tr>
                      <td class="text-center">
                        <div class="d-flex align-items-center">
                          <div class="media-left mr-50">
                            <img src="{{ $company->logo_url }}" alt="avatar" class="rounded-circle" height="40"
                              width="40">
                          </div>
                          <div class="d-flex align-items-center">
                            {{ $company->name }}
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
                      <td class="text-center d-flex align-items-center">
                        @foreach (\App\User::whereIn('id', $company->finals($company->stepdata->id))->get() as $judge)
                          <div class="chip mx-2">
                            <div class="d-flex align-items-center g-3">
                              <div class="avatar">
                                <img class="img-fluid" src="{{ $judge->image_url }}" alt="click to view" height="20"
                                  width="20">
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

    <div class="row g-6">
      <a href="{{ route('screening.results_report', ['status' => 'not-evaluated']) }}" class="col-lg-4 col-sm-6">
        <div class="card card-border-shadow-primary h-100">
          <div class="card-body" style="display: grid; place-items: center;">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="ti ti-user-screen ti-28px"></i>
                </span>
              </div>
              <h4 class="mb-0">{{ $screening_sessions_count }}</h4>
            </div>
            <p class="mb-1">{{ __('admin.screening pending sessions') }}</p>
          </div>
        </div>
      </a>
      <a href="{{ route('screening.results_report', ['status' => 'published']) }}" class="col-lg-4 col-sm-6">
        <div class="card card-border-shadow-primary h-100">
          <div class="card-body" style="display: grid; place-items: center;">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="ti ti-user-screen ti-28px"></i>
                </span>
              </div>
              <h4 class="mb-0">{{ $final_done_screening_count }}</h4>
            </div>
            <p class="mb-1">{{ __('admin.screening done sessions') }}</p>
          </div>
        </div>
      </a>
    </div>

    <div class="col-md-12 mt-10">
      <div>
        <div class="card-header border-bottom">
          <h4 class="card-title align-items-center mb-3">
            <i class="ti ti-calendar-time ti-28px"></i>
            <span class="mx-2">{{ __('dashboard.screening pending sessions') }}:</span>
          </h4>
        </div>
        <div class="card-body">
          <div class="card">
            <div class="table-responsive">
              <table class="table mb-0" id="userstable">
                <thead class="bg-label-secondary">
                  <tr>
                    <th class="text-center">{{ __('cycles.Company') }}</th>
                    <th class="text-center">{{ __('admin.Organization') }}</th>
                    <th class="text-center">{{ __('cycles.Cycle') }}</th>
                    <th class="text-center">{{ __('cycles.Step Title') }}</th>
                    <th class="text-center">{{ __('admin.Judgers') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($screening_companies as $company)
                    <tr>
                      <td class="text-center">
                        <div class="d-flex align-items-center">
                          <div class="media-left mr-50">
                            <img src="{{ $company->logo_url }}" alt="avatar" class="rounded-circle"
                              height="40" width="40">
                          </div>
                          <div class="d-flex align-items-center">
                            {{ $company->name }}
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
                      <td class="text-center d-flex align-items-center">
                        @foreach ($company->stepdata->stepFiltrationJudges()->get() as $judge)
                          <div class="chip mx-2" data-toggle="tooltip" data-placement="bottom" title=""
                            data-original-title="{{ $judge->phone }} {{ $judge->email }}">
                            <div class="d-flex align-items-center">
                              <div class="avatar">
                                <img class="img-fluid" src="{{ $judge->image_url }}" alt="click to view"
                                  height="20" width="20">
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
