<div class="col-12">
  <div class="row g-6">
    <a href="{{ route('trainings.sessions_report') }}" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-primary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="ti ti-book ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $total_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Training sessions') }}</p>
        </div>
      </div>
    </a>

    <a href="{{ route('trainings.sessions_report') }}" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-secondary h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="ti ti-clock ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $sessions_period }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Training sessions Duration') }}</p>
        </div>
      </div>
    </a>

    <a href="{{ route('trainings.sessions_report', ['type' => 'sessions']) }}" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-warning h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="ti ti-video ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $zoom_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Zoom Training Sessions Count') }}</p>
        </div>
      </div>
    </a>

    <a href="{{ route('trainings.sessions_report', ['type' => 'offline']) }}" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-danger h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-danger">
                <i class="ti ti-building ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $offline_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Offline Training Sessions Count') }}</p>
        </div>
      </div>
    </a>

    <a href="/dashboard/training/sessions" class="col-lg-4 col-sm-6">
      <div class="card card-border-shadow-success h-100">
        <div class="card-body" style="display: grid; place-items: center;">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-success">
                <i class="ti ti-world ti-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ $online_sessions_count }}</h4>
          </div>
          <p class="mb-1">{{ __('admin.Online Training Sessions Count') }}</p>
        </div>
      </div>
    </a>
  </div>
</div>