<div>
  {{-- <h3 class="section-title mt-3">Program Overview</h3> --}}
  <!-- KPIs Row -->
  <div class="row justify-content-center mb-4">
    <div class="col-lg col-md-4 col-sm-6">
      <div class="card card-body text-center mb-6">
        <i class="fas fa-building-flag fs-3 mb-3 text-primary"></i>
        <div class="display-6 fw-bold text-dark" id="summaryTotalStartups">{{ $totalStartups }}</div>
        <div class="kpi-label">{{ __('admin.Total Startups') }}</div>
      </div>
    </div>
    <div class="col-lg col-md-4 col-sm-6">
      <div class="card card-body text-center mb-6">
        <i class="fas fa-user-tie fs-3 mb-3 text-info"></i>
        <div class="display-6 fw-bold text-dark" id="summaryTotalMentors">{{ $totalMentors }}</div>
        <div class="kpi-label">{{ __('admin.Total Mentors') }}</div>
      </div>
    </div>
    <div class="col-lg col-md-4 col-sm-6">
      <div class="card card-body text-center mb-6">
        <i class="fas fa-chalkboard-user fs-3 mb-3 text-success"></i>
        <div class="display-6 fw-bold text-dark" id="summaryTotalWorkshops">{{ $totalWorkshops }}</div>
        <div class="kpi-label">{{ __('admin.Total Workshops') }}</div>
      </div>
    </div>
    <div class="col-lg col-md-4 col-sm-6">
      <div class="card card-body text-center mb-6">
        <i class="fas fa-star fs-3 mb-3 text-warning"></i>
        <div class="display-6 fw-bold text-dark">{{ $averageFinalScore }}</div>
        <div class="kpi-label">{{ __('admin.Average Final Score') }}</div>
      </div>
    </div>
    <div class="col-lg col-md-4 col-sm-6">
      <div class="card card-body text-center mb-6">
        <i class="fas fa-check-circle fs-3 mb-3 text-danger"></i>
        <div class="display-6 fw-bold text-dark" id="summaryEvaluationsComplete">{{ $evaluationCompletion }}%</div>
        <div class="kpi-label">{{ __('admin.Evaluation Completion') }}</div>
      </div>
    </div>
  </div>

  <hr>

  <div class="row mt-4">
    <div class="col-md-6">
      <div class="card card-body border h-100 d-flex flex-column">
        <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-tasks text-primary me-2"></i> {{
          __('admin.Program Status') }}</h4>
        <div>
          <div class="py-8">
            <h6 class="mt-3 fw-bold">{{ __('admin.Key Program Stages') }}:</h6>
            <ul class="list-unstyled">
              @foreach($steps as $step)
              <li class="mb-2">
                @php
                $iconClass = match($loop->iteration) {
                1, 2, 3 => 'fas fa-check-circle text-success',
                4 => 'fas fa-spinner fa-spin text-primary',
                default => 'far fa-circle text-muted',
                };
                @endphp
                <i class="{{ $iconClass }} me-2"></i> {{ $step->title }}
              </li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card card-body border h-100">
        <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-chart-pie text-primary me-2"></i>
          {{ __('admin.Status Distribution') }}</h4>
        <div class="chart-container d-flex justify-content-center align-items-center" style="height: 250px; background-color: #f8f9fa;">
          <canvas id="summaryStatusDistributionChart"></canvas>
        </div>
        <div class="w-100">
          <h6 class="mt-4 fw-bold">{{ __('admin.Results Summary') }}:</h6>
          <ul class="list-group list-group-flush">
            <li class="list-group-item" id="summaryTopCompanies"><i class="fas fa-trophy text-muted"></i>
              {{ __('admin.Highest Rated') }}: {{ $statusDistribution['Highest Rated'] }}</li>
            <li class="list-group-item" id="summaryGoodCompanies"><i class="fas fa-thumbs-up" style="color:#28a745"></i>
              {{ __('admin.Good Performance') }}: {{ $statusDistribution['Good Performance'] }}</li>
            <li class="list-group-item" id="summaryImprovementCompanies">
              <i class="fas fa-flag" style="color:#dc3545"></i>
               {{ __('admin.Needs Improvement') }}: {{ $statusDistribution['Needs Improvement'] }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>