@extends('layouts.admin')

@section('style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- Custom CSS (Combined & Refined) -->
<style>
  .kpi-card,
  .kpi-card-sm {
    background-color: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 0.75rem;
    text-align: center;
    padding: 1.2rem 1rem;
    margin-bottom: 1.5rem;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
  }

  .kpi-card:hover,
  .kpi-card-sm:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  }

  .kpi-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
  }

  .kpi-value,
  .kpi-value-sm {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1a3a6e;
  }

  .kpi-label,
  .kpi-label-sm {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 600;
  }

  .kpi-card-sm {
    padding: 1rem;
  }

  .kpi-value-sm {
    font-size: 1.6rem;
  }

  .table thead th {
    background-color: #e9ecef;
    color: #495057;
    font-weight: 600;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
  }

  .table tbody td {
    vertical-align: middle;
    text-align: center;
    font-size: 0.95rem;
  }

  .table .text-right-align {
    text-align: right;
  }

  .table .company-name {
    text-align: right;
    font-weight: 500;
  }

  .table .notes-col {
    text-align: right;
    min-width: 200px;
  }

  .table .comments-col {
    text-align: right;
    font-size: 0.9rem;
    min-width: 250px;
  }

  .table .score-col {
    font-weight: 600;
    min-width: 90px;
  }

  .table .rank-col {
    font-weight: bold;
    min-width: 50px;
  }

  .table .rank-col .fa-medal {
    color: #ffc107;
  }

  .table .rank-col .fa-award {
    color: #adb5bd;
  }

  .table .rank-col .fa-bronze {
    color: #cd7f32;
  }

  .rating-badge {
    font-size: 0.9em;
    padding: 0.4em 0.7em;
    font-weight: 600;
    display: inline-block;
    min-width: 55px;
  }

  .status-badge {
    font-size: 0.85em;
    padding: 0.4em 0.7em;
  }

  .pass-fail-icon {
    font-size: 1.4rem;
  }

  .progress {
    height: 1rem;
    font-size: 0.75rem;
    background-color: #e9ecef;
    border-radius: 0.375rem;
  }

  .progress-bar {
    font-weight: bold;
    color: #fff;
  }

  .chart-container {
    position: relative;
    min-height: 280px;
    width: 100%;
    margin-top: 1rem;
  }

  canvas {
    max-width: 100%;
  }

  .export-buttons {
    margin-bottom: 1rem;
    text-align: left;
  }

  /* Align left in LTR context */
  .export-buttons .btn {
    font-size: 0.85rem;
  }

  html[dir="rtl"] .export-buttons {
    text-align: left;
  }

  /* Override for RTL */

  /* --- Report Specific Adjustments (Example) --- */
  /* Evaluator Status Card (Final Eval Report) */
  .evaluator-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1rem;
    border: 1px solid #eee;
    border-radius: 0.5rem;
    background-color: #f8f9fa;
  }

  .evaluator-img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #adb5bd;
    flex-shrink: 0;
  }

  .evaluator-info {
    flex-grow: 1;
  }

  .evaluator-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
    font-size: 1rem;
  }

  .evaluator-status {
    font-size: 0.85rem;
    color: #6c757d;
  }

  .evaluator-status .text-success,
  .evaluator-status .text-danger {
    font-weight: bold;
  }

  .evaluator-progress .progress {
    height: 6px;
    margin-top: 5px;
  }
</style>
@stop
@section('content')

<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold">{{ __('admin.Startup Program Dashboard') }}</h4>

    <div class="card card-body">
      <!-- Tab Navigation -->
      <ul class="nav nav-tabs border-bottom mb-8 pb-4" id="mainDashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary-tab-pane"
            type="button" role="tab" aria-controls="summary-tab-pane" aria-selected="true">
            <i class="fas fa-file-alt me-2"></i> {{ __('admin.Program Summary') }}
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="performance-tab" data-bs-toggle="tab" data-bs-target="#performance-tab-pane"
            type="button" role="tab" aria-controls="performance-tab-pane" aria-selected="false">
            <i class="fas fa-chart-line me-2"></i> {{ __('admin.Startup Performance') }}
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="mentorship-tab" data-bs-toggle="tab" data-bs-target="#mentorship-tab-pane"
            type="button" role="tab" aria-controls="mentorship-tab-pane" aria-selected="false">
            <i class="fas fa-people-arrows me-2"></i> {{ __('admin.Mentorship & Advisory') }}
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="workshops-tab" data-bs-toggle="tab" data-bs-target="#workshops-tab-pane"
            type="button" role="tab" aria-controls="workshops-tab-pane" aria-selected="false">
            <i class="fas fa-person-chalkboard me-2"></i> {{ __('admin.Workshops') }}
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="final-eval-tab" data-bs-toggle="tab" data-bs-target="#final-eval-tab-pane"
            type="button" role="tab" aria-controls="final-eval-tab-pane" aria-selected="false">
            <i class="fas fa-trophy me-2"></i> {{ __('admin.Final Evaluation') }}
          </button>
        </li>
      </ul>

      <!-- Tab Content -->
      <div class="tab-content p-0" id="mainDashboardTabsContent">

        <!-- ========================== -->
        <!-- 1. Program Summary Tab Pane -->
        <!-- ========================== -->
        <div class="tab-pane fade show active" id="summary-tab-pane" role="tabpanel" aria-labelledby="summary-tab"
          tabindex="0">
          {{-- <h3 class="section-title mt-3">Program Overview</h3> --}}
          <!-- KPIs Row -->
          <div class="row justify-content-center mb-4">
            <div class="col-lg col-md-4 col-sm-6">
              <div class="kpi-card">
                <i class="fas fa-building-flag kpi-icon text-primary"></i>
                <div class="kpi-value" id="summaryTotalStartups">{{ $totalStartups }}</div>
                <div class="kpi-label">{{ __('admin.Total Startups') }}</div>
              </div>
            </div>
            <div class="col-lg col-md-4 col-sm-6">
              <div class="kpi-card">
                <i class="fas fa-user-tie kpi-icon text-info"></i>
                <div class="kpi-value" id="summaryTotalMentors">{{ $totalMentors }}</div>
                <div class="kpi-label">{{ __('admin.Total Mentors') }}</div>
              </div>
            </div>
            <div class="col-lg col-md-4 col-sm-6">
              <div class="kpi-card">
                <i class="fas fa-chalkboard-user kpi-icon text-success"></i>
                <div class="kpi-value" id="summaryTotalWorkshops">{{ $totalWorkshops }}</div>
                <div class="kpi-label">{{ __('admin.Total Workshops') }}</div>
              </div>
            </div>
            <div class="col-lg col-md-4 col-sm-6">
              <div class="kpi-card">
                <i class="fas fa-star kpi-icon text-warning"></i>
                <div class="kpi-value" id="summaryAvgFinalScore"> {{ $averageFinalScore !== null ? number_format($averageFinalScore, 2) : '--' }}</div>
                <div class="kpi-label">{{ __('admin.Average Final Score') }}</div>
              </div>
            </div>
            <div class="col-lg col-md-4 col-sm-6">
              <div class="kpi-card">
                <i class="fas fa-check-circle kpi-icon text-danger"></i>
                <div class="kpi-value" id="summaryEvaluationsComplete">83%</div>
                <div class="kpi-label">{{ __('admin.Evaluation Completion') }}</div>
              </div>
            </div>
          </div>

          <hr>

          <div class="row mt-4">
            <div class="col-md-6">
              <div class="card card-body border h-100 d-flex flex-column">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i
                    class="fas fa-tasks text-primary me-2"></i> {{ __('admin.Program Status') }}</h4>
                <p class="mt-6">This summary provides a comprehensive overview of the participating startups
                  performance and
                  aggregates key metrics from various evaluation reports.</p>
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
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i
                    class="fas fa-chart-pie text-primary me-2"></i> {{ __('admin.Status Distribution') }}</h4>
                <div class="chart-container" style="height: 250px; background-color: #f8f9fa;">
                  <canvas id="summaryStatusDistributionChart"></canvas>
                </div>
                <div class="w-100">
                  <h6 class="mt-4 fw-bold">{{ __('admin.Results Summary') }}:</h6>
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item" id="summaryTopCompanies"><i class="fas fa-trophy text-muted"></i>
                      {{ __('admin.Highest Rated') }}: --</li>
                    <li class="list-group-item" id="summaryGoodCompanies"><i class="fas fa-thumbs-up"
                        style="color:#28a745"></i> {{ __('admin.Good Performance') }}: --</li>
                    <li class="list-group-item" id="summaryImprovementCompanies"><i class="fas fa-flag"
                        style="color:#dc3545"></i> {{ __('admin.Needs Improvement') }}: --</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================================== -->
        <!-- 2. Startup Performance Tab Pane     -->
        <!-- ==================================== -->
        <div class="tab-pane fade" id="performance-tab-pane" role="tabpanel" aria-labelledby="performance-tab"
          tabindex="0">
          <!-- Filters for this specific report -->
          <div class="bg-label-light p-4 rounded border mb-6">
            <h5 class=""><i class="fas fa-filter"></i> {{ __('admin.Filter Options (Performance)') }}  </h5>
            <form class="row g-3 align-items-end performance-filter-form" method="GET" action="{{ route('programs.report', $cycle->id) }}">
                  <div class="col-md-3 col-sm-6">
                    <label for="perfFilterSector" class="form-label fw-semibold">{{ __('admin.Sector') }}</label>
                    <select class="form-select select2" id="perfFilterSector" name="sector">
                       <option selected value="">{{ __('admin.All') }}</option>
                       @foreach ($sectors as $sector)
                          <option value="{{ $sector->name_en }}" {{ request('sector') == $sector->name_en ? 'selected' : '' }}>
                             {{ $sector->{'name_'.$l} }}
                          </option>
                       @endforeach
                   </select>
                </div>

              <div class="col-md-3 col-sm-6">
                    <label for="perfFilterStage" class="form-label fw-semibold">{{ __('admin.Stage') }}</label>
                    <select class="form-select select2" id="perfFilterStage" name="stage">
                       <option selected value="">{{ __('admin.All') }}</option>
                        @foreach ($stages as $stage)
                          <option value="{{ $stage->name_en }}" {{ request('stage') == $stage->name_en ? 'selected' : '' }}>
                            {{ $stage->{'name_' . $l} }}
                          </option>
                       @endforeach
                    </select>
               </div>

              <div class="col-md-2 col-sm-6"> <label for="perfFilterMinRating" class="form-label fw-semibold">{{ __('admin.Min Rating') }}</label>
                <input type="number" class="form-control" id="perfFilterMinRating" min="1" max="5" step="0.1"
                  placeholder="e.g., 3.5">
              </div>
              <div class="col-md-2 col-sm-6"> <label for="perfFilterMinProgress" class="form-label fw-semibold">{{ __('admin.Min Progress') }}
                  (%)</label>
                <input type="number" class="form-control form-control" id="perfFilterMinProgress" min="0" max="100"
                  step="5" placeholder="e.g., 50">
              </div>
              <div class="col-md-2 col-sm-12 text-start mt-3 mt-md-0"> <button type="submit"
                  class="btn btn-primary w-100"><i class="fas fa-search me-2"></i> {{ __('admin.Search') }}</button> </div>
            </form>
          </div>
          <!-- Content from performance report -->
          <div class="row mt-4 ">
            <div class="col-12 mb-8">
              <div class="row">
                <div class="col-md-6">
                  <!-- Chart 1: Startups by Sector -->
                  <div class="card card-body border">
                    <h4 class="mb-6 fw-semibold border-bottom border-2 border-primary"><i
                        class="fas fa-chart-pie text-primary me-2"></i> {{ __('admin.Distribution by Sector') }}</h4>
                    <div class="chart-container">
                      <canvas id="perfSectorDistributionChart"></canvas>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <!-- Chart 2: Average Progress by Stage -->
                  <div class="card card-body border">
                    <h4 class="mb-6 fw-semibold border-bottom border-2 border-primary"><i
                        class="fas fa-chart-bar text-primary me-2"></i> {{ __('admin.Average Progress by Stage') }}</h4>
                    <div class="chart-container">
                      <canvas id="perfProgressByStageChart"></canvas>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card card-body border">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i
                    class="fas fa-list text-primary me-2"></i> {{ __('admin.Startup Performance List') }}</h4>
                <div class="table-responsive">
                  <!-- Copied Performance Table Structure Here -->
                  <table class="table table-striped table-hover align-middle performance-table">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">{{ __('admin.company name') }}</th>
                        <th scope="col">{{ __('admin.Sector') }}</th>
                        <th scope="col">{{ __('admin.Stage') }}</th>
                        <th scope="col">{{ __('admin.Rating') }}</th>
                        <th scope="col">{{ __('admin.Progress') }} (%)</th>
                        <th scope="col">{{ __('admin.Notes') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                        @foreach ($companies as $index => $company)
                           <tr>
                              <th scope="row">{{ $index + 1 }}</th>
                              <td>{{ $company->{'name_'.$l} }}</td>
                              <td>{{ $company->sectoR?->{'name_'.$l} ?? '-' }}</td>
                              <td>{{ $company->stagE->{'name_'.$l} ?? '-' }}</td>
                              <td>{{ '-' }}</td>
                              <td>{{ '-' }}</td>
                              <td>{{ '-' }}</td>
                           </tr>
                        @endforeach
                    </tbody>
                  </table>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- ==================================== -->
        <!-- 3. Mentorship & Advisory Tab Pane    -->
        <!-- ==================================== -->
        <div class="tab-pane fade" id="mentorship-tab-pane" role="tabpanel" aria-labelledby="mentorship-tab"
          tabindex="0">
          <!-- Filters for this specific report -->
          <div class="bg-label-light p-4 rounded-3">
            <h5 class="fw-semibold"><i class="fas fa-filter"></i>
              {{ __('admin.Filter Options (Mentorship)') }}</h5>
            <form class="row g-3 align-items-end mentorship-filter-form" method="GET" action="{{ route('programs.report',$cycle->id) }}">
              <div class="col-md-3"><label for="mentorFilterMentor" class="form-label">{{ __('admin.Mentor') }}</label>
                   <select class="form-select select2" id="mentorFilterMentor" name="mentor">
                    <option value="">{{ __('admin.All') }}</option>
                        @foreach($mentors as $mentor)
                             <option value="{{ $mentor->id }}" {{ request('mentor') == $mentor->id ? 'selected' : '' }}>{{ $mentor->name }}</option>
                        @endforeach
                  </select>
              </div>
              <div class="col-md-3"><label for="mentorFilterCompany" class="form-label">{{ __('admin.Company') }}</label>
                    <select class="form-select select2" id="mentorFilterCompany" name="company">
                        <option value="">{{ __('admin.All') }}</option>
                            @foreach($companies as $company)
                              <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>{{ $company->{'name_'.app()->getLocale()}  }}</option>
                            @endforeach
                    </select>
              </div>
              <div class="col-md-3"><label for="mentorFilterDateStart" class="form-label">{{ __('admin.From Date') }}</label><input
                  type="date" class="form-control" id="mentorFilterDateStart" name="zoom_date"></div>
              <div class="col-md-3 text-start"><button type="submit" class="btn btn-primary w-50"><i
                    class="fas fa-search me-2"></i> {{ __('admin.Search') }}</button></div>
            </form>
          </div>
          <!-- Content from mentorship report -->
          <!-- KPIs Row -->
          <div class="row justify-content-center mb-4 mt-4">
            <div class="col-lg-3 col-md-6">
              <div class="kpi-card-sm"> <i class="fas fa-users-gear kpi-icon text-primary"></i>
                <div class="kpi-value-sm">{{ $mentorshipRequests->count() }}</div>
                <div class="kpi-label-sm">{{ __('admin.Total Sessions') }}</div>
              </div>
            </div>
            @php
            $totalMinutes = $mentorshipRequests->sum('period');
            $totalHours = $totalMinutes / 60;
            @endphp

            <div class="col-lg-3 col-md-6">
              <div class="kpi-card-sm"> <i class="fas fa-clock kpi-icon text-success"></i>
                <div class="kpi-value-sm"> {{ number_format($totalHours, 1) }} </div>
                <div class="kpi-label-sm">{{ __('admin.Total Hours') }}</div>
              </div>
            </div>
            <div class="col-lg-3 col-md-6">
              <div class="kpi-card-sm"> <i class="fas fa-star kpi-icon text-warning"></i>
                <div class="kpi-value-sm">{{ $mentorshipRequests->avg('rating') }}</div>
                <div class="kpi-label-sm">{{ __('admin.Average Rating') }}</div>
              </div>
            </div>
          </div>
          <div class="row mt-2">
            <div class="col-lg-7">
              <!-- Table 1: Mentorship Details per Company -->
              @php
                 $groupedByCompany = $mentorshipRequests->groupBy('user_id');
              @endphp

              <div class="mb-4">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-building-user text-primary me-2"></i> Mentorship Details by Company</h4>
                <div class="table-responsive">
                  <!-- Copied Mentorship Table 1 Structure Here -->
                  <table class="table table-hover align-middle mentorship-company-table">
                    <thead>
                      <tr>
                        <th scope="col">{{ __('admin.company name') }}</th>
                        <th scope="col">{{ __('admin.Mentor') }}</th>
                        <th scope="col">{{ __('admin.Sessions') }}</th>
                        <th scope="col">{{ __('admin.Hours') }}</th>
                        <th scope="col">{{ __('admin.Mentor Rating') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                        @foreach($groupedByCompany as $userId => $requests)
                         @php
                          $company = $requests->first()->user->company;
                          $mentor = $requests->first()->mentor;
                          $totalSessions = $requests->count();
                          $totalMinutes = $requests->sum('period');
                          $totalHours = $totalMinutes / 60;
                          $avgRating = $requests->whereNotNull('rating')->avg('rating');
                        @endphp
                      <tr>
                          <td class="text-right-align">{{ $company->{'name_' . app()->getLocale()} }}</td>
                          <td>{{ $mentor->first_name . ' ' . $mentor->last_name ?? 'N/A' }}</td>
                          <td>{{ $totalSessions }}</td>
                          <td>{{ number_format($totalHours, 1) }}</td>
                          <td>
                              <span class="badge bg-label-{{ $avgRating >= 4.5 ? 'success' : 'primary' }}">
                                  {{ number_format($avgRating, 1) }}/5
                             </span>
                           </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
              <!-- Table 2: Mentor Performance -->
                     @php
                        $groupedByMentor = $mentorshipRequests->groupBy('mentor_id');
                     @endphp

               <div>
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-user-tie text-primary me-2"></i> Mentor Performance</h4>
                <div class="table-responsive">
                  <!-- Copied Mentorship Table 2 Structure Here -->
                  <table class="table table-hover align-middle mentorship-mentor-table">
                    <thead>
                      <tr>
                        <th scope="col">{{ __('admin.Mentor') }}</th>
                        <th scope="col">{{ __('admin.Total Hours') }}</th>
                        <th scope="col">{{ __('admin.Total Hours') }}</th>
                        <th scope="col">{{ __('admin.Average Rating') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($groupedByMentor as $mentorId => $requests)
                       @php
                        $mentor = $requests->first()->mentor;
                        $totalSessions = $requests->count();
                        $totalMinutes = $requests->sum('period');
                        $totalHours = $totalMinutes / 60;
                        $avgRating = $requests->whereNotNull('rating')->avg('rating');
                        @endphp
                      <tr>
                          <td class="text-right-align">{{ $mentor->name ?? 'N/A' }}</td>
                          <td>{{ $totalSessions }}</td>
                          <td>{{ $totalHours }}</td>
                          <td>
                            <span class="badge bg-label-{{ $avgRating >= 4.5 ? 'success' : 'primary' }}">
                                {{ number_format($avgRating, 1) }}/5
                            </span>
                          </td>
                      </tr>
                    @endforeach
                     
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <!-- Chart 1: Session Distribution -->
              <div class="mb-4">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-chart-pie text-primary me-2"></i> Session Distribution</h4>
                <div class="chart-container">
                  <canvas id="mentorSessionsChart"></canvas>
                </div>
              </div>
              <!-- Chart 2: Mentor Average Rating -->
              <div>
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-chart-bar text-primary me-2"></i> Mentor Average Ratings</h4>
                <div class="chart-container">
                  <canvas id="mentorRatingChart"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================================== -->
        <!-- 4. Workshops Tab Pane               -->
        <!-- ==================================== -->
        <div class="tab-pane fade" id="workshops-tab-pane" role="tabpanel" aria-labelledby="workshops-tab" tabindex="0">
          <!-- Filters for this specific report -->
          <div class="bg-label-light p-4 rounded-3">
            <h5 class="fw-semibold"><i class="fas fa-filter"></i>
              {{ __('admin.Filter Options (Workshops)') }}</h5>
            <form class="row g-3 align-items-end workshops-filter-form" method="GET" action="{{ route('programs.report',$cycle->id) }}">
              <div class="col-md-3"><label for="wsFilterTrainer" class="form-label">Trainer</label><select
                  class="form-select select2" id="wsFilterTrainer" name="trainer">
                  <option selected value="">{{ __('admin.All') }}</option>
                    @foreach ($trainerNames as $trainer)
                      <option value="{{ $trainer }}" {{ request('trainer') == $trainer ? 'selected' : '' }}>{{ $trainer }}</option>
                    @endforeach
                </select></div>
              <div class="col-md-3"><label for="wsFilterWorkshop" class="form-label">{{ __('admin.Workshop Name') }}</label>
                <input type="text" class="form-control" id="wsFilterWorkshop" name="workshop_name"></div>
              <div class="col-md-3"><label for="wsFilterType" class="form-label">{{ __('admin.Workshop Type') }}</label>
                <select class="form-select select2" id="wsFilterType"name="type">
                  <option selected value="">{{ __('admin.All') }}</option>
                    <option value="online" {{ request('type') == 'online' ? 'selected' : '' }}>{{ __('admin.Zoom') }}</option>
                    <option value="offline" {{ request('type') == 'offline' ? 'selected' : '' }}>{{ __('admin.In-Person') }}</option>
                </select></div>
              <div class="col-md-3 text-start"><button type="submit" class="btn btn-primary w-50"><i
                    class="fas fa-search me-2"></i> {{ __('admin.Search') }}</button></div>
            </form>
          </div>
          <!-- Content from workshops report -->
          <!-- KPIs Row -->
          <div class="row justify-content-center mb-4 mt-4">
            <div class="col-lg-2 col-md-4 col-sm-6">
              <div class="kpi-card-sm"><i class="fas fa-chalkboard-user kpi-icon text-primary"></i>
                <div class="kpi-value-sm">{{ $totalWorkshops }}</div>
                <div class="kpi-label-sm">{{ __('admin.Total Workshops') }}</div>
              </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
              <div class="kpi-card-sm"><i class="fas fa-clock kpi-icon text-success"></i>
                <div class="kpi-value-sm">+{{ $totalHours }}</div>
                <div class="kpi-label-sm">{{ __('admin.Total Hours') }}</div>
              </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
              <div class="kpi-card-sm"><i class="fas fa-video kpi-icon text-warning"></i>
                <div class="kpi-value-sm">{{ $zoomWorkshops }}</div>
                <div class="kpi-label-sm">{{ __('admin.Zoom Workshops') }}</div>
              </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
              <div class="kpi-card-sm"><i class="fas fa-users kpi-icon text-info"></i>
                <div class="kpi-value-sm">{{ $inPersonWorkshops }}</div>
                <div class="kpi-label-sm">{{ __('admin.In-Person Workshops') }}</div>
              </div>
            </div>
          </div>
          <div class="row mt-2">
            <div class="col-lg-7">
              <!-- Workshop Details Table -->
              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-list-check text-primary me-2"></i> {{ __('admin.Workshop Details') }}</h4>
                </div>
                <div class="table-responsive">
                  <!-- Copied Workshop Table 1 Structure Here -->
                 <table id="workshopTable" class="table table-hover align-middle workshop-details-table">

                    <thead>
                      <tr>
                        <th scope="col">{{ __('admin.Workshop Name') }}</th>
                        <th scope="col">{{ __('admin.Trainer') }}</th>
                        <th scope="col">{{ __('admin.Participants') }}</th>
                        <th scope="col">{{ __('admin.rating') }}</th>
                        <th scope="col">{{ __('admin.type') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                     @foreach ($workshops as $ws)
                       <tr>
                         <td class="ws-name">{{ $ws->title }}</td>
                         <td class="trainer-name">{{ $ws->trainer_name ?? '-' }}</td>
                         <td>{{ $ws->reports->count() ?? '-' }}</td>
                         <td>
                             <span class="badge bg-label-primary rating-badge">
                                  {{ '-' }}
                              </span>
                         </td>
                          <td>
                            <span class="badge {{ $ws->type === 'online' ? 'bg-secondary' : 'bg-label-warning text-dark' }}">
                                  {{ $ws->type === 'online' ? 'Zoom' : 'In-Person' }}
                             </span>
                          </td>
                        </tr>
                 
                    @endforeach
                              
                    </tbody>
                  </table>
                </div>
              </div>
              <!-- Company Performance Table -->
              <div>
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-graduation-cap text-primary me-2"></i> Company Performance</h4>
                <div class="table-responsive">
                  <!-- Copied Workshop Table 2 Structure Here -->
                  <table class="table table-hover align-middle workshop-company-perf-table">
                    <thead>
                      <tr>
                        <th scope="col">{{ __('admin.company name') }}</th>
                        <th scope="col">{{ __('admin.completion rate') }}</th>
                        <th scope="col">{{ __('admin.Pass/Fail') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($companies as $company)
                          <tr>
                             <td>{{ $company->name }}</td>
                             <td>
                             @php
                                 $rate = $company->completion_rate ?? 0;
                                 $badgeClass = $rate >= 90 ? 'bg-success' : ($rate >= 75 ? 'bg-label-warning text-dark' : 'bg-label-secondary');
                            @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $rate }}%</span>
                            </td>
                              <td>
                                @if($company->has_passed)
                                    <i class="fas fa-check-circle text-success pass-fail-icon"></i>
                                @else
                                    <i class="fas fa-times-circle text-danger pass-fail-icon"></i>
                                @endif
                               </td>
                             </tr>
                              @empty
                                <tr>
                                  <td colspan="3">{{ __('admin.No companies found') }}</td>
                                </tr>
                               @endforelse
                     
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="col-lg-5">
              <!-- Chart 1: Workshop Type Distribution -->
              <div class="mb-4">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-chart-pie text-primary me-2"></i> Workshop Type Distribution</h4>
                <div class="chart-container">
                  <canvas id="workshopTypeChart"></canvas>
                </div>
              </div>
              <!-- Chart 2: Average Rating per Trainer -->
              <div class="mb-4">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-chart-bar text-primary me-2"></i> Average Trainer Ratings</h4>
                <div class="chart-container">
                  <canvas id="trainerRatingChart"></canvas>
                </div>
              </div>

              <div>
  <h4 class="fw-semibold border-bottom border-2 border-primary">
    <i class="fas fa-poll text-primary me-2"></i> {{ __('admin.Survey Results') }}
  </h4>

  @if ($surveyResponses->isEmpty())
    <p class="text-muted text-center p-3 border rounded">
      <i class="fas fa-info-circle me-1"></i> {{ __('No survey responses found.') }}
    </p>
  @else
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>{{ __('admin.User') }}</th>
            <th>{{ __('admin.Responses') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($surveyResponses as $response)
            <tr>
              <td>{{ $response->user->first_name_en . $response->user->last_name_en ?? '-' }}</td>
              <td>
                @php
                  $decodedResponses = json_decode($response->responses, true);
                @endphp

                @if (is_array($decodedResponses))
                  <ul>
                    @foreach ($decodedResponses as $responseItem)
                      <li>
                        <strong>{{ $responseItem['question'] }}:</strong>
                        @php
                          $selectedAnswer = collect($responseItem['answers'])->firstWhere('status', true);
                        @endphp
                        @if ($selectedAnswer)
                          {{ $selectedAnswer['text'] }}
                        @endif
                      </li>
                    @endforeach
                  </ul>
                @else
                  <span class="text-muted">{{ '-' }}</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

</div>
 </div>
  </div>

        <!-- ==================================== -->
        <!-- 5. Final Evaluation Tab Pane        -->
        <!-- ==================================== -->
        <div class="tab-pane fade" id="final-eval-tab-pane" role="tabpanel" aria-labelledby="final-eval-tab"
          tabindex="0">
          <!-- Filters for this specific report -->
          <div class="bg-label-light p-4 rounded-3">
            <h5 class="fw-semibold"><i class="fas fa-filter"></i>
              Filter Options (Final)</h5>
            <form class="row g-2 align-items-end final-eval-filter-form">
              <div class="col-md-4"><label for="feFilterStatus" class="form-label">Status</label><select
                  class="form-select select2" id="feFilterStatus">
                  <option selected value="">All</option>
                  <option value="Highest Rated">Highest</option>
                  <option value="Good Performance">Good</option>
                  <option value="Needs Improvement">Improvement</option>
                </select></div>
              <div class="col-md-4"><label for="feFilterMinScore" class="form-label">Minimum Score</label><input
                  type="number" class="form-control" id="feFilterMinScore" min="0" max="100" step="5"
                  placeholder="e.g., 70"></div>
              <div class="col-md-4 text-start"><button type="submit" class="btn btn-primary w-50"><i
                    class="fas fa-search me-2"></i> Search</button></div>
            </form>
          </div>
          <!-- Content from final evaluation report -->
          <div class="row mt-4">
            <div class="col-lg-8">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-clipboard-list text-primary me-2"></i> Final Company Evaluations</h4>
                <div class="export-buttons"> <button type="button" class="btn btn-label-danger btn-sm"><i
                      class="fas fa-file-pdf me-1"></i> PDF</button> <button type="button"
                    class="btn btn-label-success btn-sm ms-2"><i class="fas fa-file-excel me-1"></i> Excel</button>
                </div>
              </div>
              <div class="table-responsive">
                <!-- Copied Final Eval Table Structure Here -->
                <table class="table table-striped table-hover align-middle final-eval-table">
                  <thead>
                    <tr>
                      <th scope="col">Rank</th>
                      <th scope="col">Company Name</th>
                      <th scope="col">Total Score</th>
                      <th scope="col">Judge Comments (Summary)</th>
                      <th scope="col">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="rank-col">1 <i class="fas fa-medal"></i></td>
                      <td class="company-name">Alpha Tech</td>
                      <td class="score-col">92.5 / 100</td>
                      <td class="comments-col">Outstanding performance...</td>
                      <td><span class="badge bg-label-success status-badge">Highest Rated</span></td>
                    </tr>
                    <tr>
                      <td class="rank-col">2 <i class="fas fa-award"></i></td>
                      <td class="company-name">Beta Solutions</td>
                      <td class="score-col">85.0 / 100</td>
                      <td class="comments-col">Innovative solution...</td>
                      <td><span class="badge bg-label-primary status-badge">Good Performance</span></td>
                    </tr>
                    <!-- ... more rows -->
                  </tbody>
                </table>
              </div>
              <!-- Chart: Score Distribution -->
              <div class="mt-4">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-chart-bar text-primary me-2"></i> Score Distribution</h4>
                <div class="chart-container">
                  <canvas id="finalEvalScoreDistributionChart"></canvas>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <!-- Evaluator Status -->
              <div class="mb-4">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-user-check text-primary me-2"></i> Evaluator Status</h4>
                <div class="evaluator-card">
                  <div class="evaluator-img"><i class="fas fa-user"></i></div>
                  <div class="evaluator-info">
                    <div class="evaluator-name">Mohammed Abdullah</div>
                    <div class="evaluator-status"><span class="text-success">10 C</span> / <span class="text-danger">2
                        P</span></div>
                    <div class="evaluator-progress">
                      <div class="progress">
                        <div class="progress-bar bg-success" style="width: 83%;"></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="evaluator-card">
                  <div class="evaluator-img"><i class="fas fa-user"></i></div>
                  <div class="evaluator-info">
                    <div class="evaluator-name">Fatima Al-Zahra</div>
                    <div class="evaluator-status"><span class="text-success">12 C</span> / <span class="text-danger">0
                        P</span></div>
                    <div class="evaluator-progress">
                      <div class="progress">
                        <div class="progress-bar bg-success" style="width: 100%;"></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="evaluator-card">
                  <div class="evaluator-img"><i class="fas fa-user"></i></div>
                  <div class="evaluator-info">
                    <div class="evaluator-name">Khalid Mahmoud</div>
                    <div class="evaluator-status"><span class="text-success">9 C</span> / <span class="text-danger">3
                        P</span></div>
                    <div class="evaluator-progress">
                      <div class="progress">
                        <div class="progress-bar bg-success" style="width: 75%;"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Results Summary -->
              <div>
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-bullseye text-primary me-2"></i> Final Results Summary</h4>
                <h6 class="mb-2 fw-bold text-success">Highest Rated:</h6>
                <ul class="summary-list" id="feTopCompaniesList"></ul>
                <h6 class="mt-4 mb-2 fw-bold text-danger">Needs Improvement:</h6>
                <ul class="summary-list" id="feImprovementCompaniesList"></ul>
              </div>
            </div>
          </div>
        </div>

      </div> <!-- /tab-content -->
    </div>



  </div><!-- /container-fluid -->

  @stop

  @section('scripts')
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
  @stop


  @section('pagescripts')
  <!-- Combined & Organized JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {

            const chartFont = { family: 'Cairo' };
            const chartColors = ['#0d6efd', '#198754', '#ffc107', '#6f42c1', '#fd7e14', '#dc3545', '#0dcaf0'];
            const chartBorderColors = chartColors.map(color => color.replace('0.8', '1'));

             // --- Helper function to get data from a table within a specific pane ---
             function getDataFromTable(paneSelector, rowMapper) {
                const tableRows = document.querySelectorAll(`${paneSelector} .table tbody tr`);
                return Array.from(tableRows).map(rowMapper).filter(item => item !== null);
             }

            // ==============================================
            // 1. Startup Performance Report Logic
            // ==============================================
            function setupPerformanceReport() {
                const performanceData = getDataFromTable('#performance-tab-pane', row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length < 6) return null;
                    const progressText = cells[4].querySelector('.progress-bar') ? cells[4].querySelector('.progress-bar').textContent : '0%';
                    return {
                        name: cells[0].textContent.trim(), sector: cells[1].textContent.trim(), stage: cells[2].textContent.trim(),
                        rating: parseFloat(cells[3].textContent.split('/')[0]), progress: parseInt(progressText.replace('%', '')), notes: cells[5].textContent.trim()
                    };
                });

                // Chart 1: Sector Distribution
                const sectors = [...new Set(performanceData.map(s => s.sector))];
                const sectorCounts = sectors.map(sector => performanceData.filter(s => s.sector === sector).length);
                const ctxPerfSector = document.getElementById('perfSectorDistributionChart')?.getContext('2d');
                 if(ctxPerfSector) {
                    new Chart(ctxPerfSector, {
                        type: 'doughnut', data: { labels: sectors, datasets: [{ label: '{{ __('admin.company_counts') }}', data: sectorCounts, backgroundColor: chartColors, borderColor: chartBorderColors, borderWidth: 1 }] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { font: chartFont } }, tooltip: { bodyFont: chartFont, titleFont: chartFont } } }
                    });
                 }

                // Chart 2: Progress by Stage
                const stages = ['الفكرة', 'النموذج الأولي', 'السوق', 'النمو', 'التوسع'];
                const stageAvgProgress = stages.map(stage => {
                    const startupsInStage = performanceData.filter(s => s.stage === stage);
                    if (startupsInStage.length === 0) return 0;
                    return Math.round(startupsInStage.reduce((sum, s) => sum + s.progress, 0) / startupsInStage.length);
                });
                const activeStages = stages.filter((stage, index) => stageAvgProgress[index] > 0 || performanceData.some(s => s.stage === stage));
                const activeStageAvgProgress = stageAvgProgress.filter((avg, index) => avg > 0 || performanceData.some(s => s.stage === stages[index]));
                const ctxPerfProgress = document.getElementById('perfProgressByStageChart')?.getContext('2d');
                if(ctxPerfProgress) {
                     new Chart(ctxPerfProgress, {
                        type: 'bar', data: { labels: activeStages, datasets: [{ label: 'متوسط التقدم (%)', data: activeStageAvgProgress, backgroundColor: chartColors, borderColor: chartBorderColors, borderWidth: 1 }] },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100, ticks: { font: chartFont, callback: v => v + '%' } }, x: { ticks: { font: chartFont } } }, plugins: { legend: { display: false }, tooltip: { bodyFont: chartFont, titleFont: chartFont, callbacks: { label: c => c.dataset.label + ': ' + c.parsed.y + '%' } } } }
                    });
                }

                // Filter Simulation
                // document.querySelector('.performance-filter-form')?.addEventListener('submit', e => { e.preventDefault(); console.log('Performance Filter Submitted'); alert('محاكاة: تم تطبيق فلتر الأداء!'); });
            }

            // ==============================================
            // 2. Mentorship Report Logic
            // ==============================================
          function setupMentorshipReport() {
          const mentorNames = @json($mentorNames); 
          const mentorSessions = @json($mentorSessions); 
          const mentorAvgRatings = @json($mentorAvgRatings);

        const mentorSessionsArray = Object.values(mentorSessions); 

    // Chart 1: Mentor Session Distribution (Pie Chart)
    const ctxMentorSessions = document.getElementById('mentorSessionsChart')?.getContext('2d');
    if (ctxMentorSessions) {
        new Chart(ctxMentorSessions, {
            type: 'pie',
            data: {
                labels: mentorNames,
                datasets: [{
                    label: '{{ __('admin.session_count') }}',
                    data: mentorSessionsArray, 
                    backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A6', '#F5E050'],
                    borderColor: '#FFFFFF',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 14 } } },
                    tooltip: { bodyFont: { size: 14 } }
                }
            }
        });
    }

    // Chart 2: Mentor Average Rating (Bar Chart)
    const ctxMentorRatings = document.getElementById('mentorRatingChart')?.getContext('2d');
    if (ctxMentorRatings) {
        new Chart(ctxMentorRatings, {
            type: 'bar',
            data: {
                labels: mentorNames,
                datasets: [{
                    label: '{{ __('admin.average_rating') }}',
                    data: mentorAvgRatings,
                    backgroundColor: ['#FF5733', '#33FF57', '#3357FF', '#FF33A6', '#F5E050'],
                    borderColor: '#FFFFFF',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 3.5,
                        max: 5,
                        ticks: { font: { size: 14 } }
                    },
                    x: {
                        ticks: { font: { size: 14 } }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { bodyFont: { size: 14 } }
                }
            }
        });
    }
}
          // ==============================================
            // 3. Workshops Report Logic
          // ==============================================
             function setupWorkshopsReport() {
               const zoomWorkshops = @json($zoomWorkshops);
               const inPersonWorkshops = @json($inPersonWorkshops);
                 // Chart 1: Workshop Type Distribution
                const workshopTypeCounts = { 'online': zoomWorkshops, 'offline': inPersonWorkshops };
                 const ctxWsType = document.getElementById('workshopTypeChart')?.getContext('2d');
                 if(ctxWsType) {
                     new Chart(ctxWsType, {
                        type: 'pie', data: { labels: ['Zoom', 'in-Person'], datasets: [{ label: '{{ __('admin.workshop_count') }}', data: [workshopTypeCounts.online, workshopTypeCounts.offline], backgroundColor: [chartColors[6], chartColors[2]], borderColor: [chartBorderColors[6], chartBorderColors[2]], borderWidth: 1 }] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { font: chartFont } }, tooltip: { bodyFont: chartFont, titleFont: chartFont } } }
                    });
                 }

                 // Chart 2: Average Rating per Trainer (Example Data)
                  const trainerData = getDataFromTable('#workshops-tab-pane .workshop-details-table', row => {
                        const cells = row.querySelectorAll('td');
                        if (cells.length < 4) return null;
                        return { trainer: cells[1].textContent.trim(), rating: parseFloat(cells[3].textContent.split('/')[0]) };
                  });
                  const trainers = [...new Set(trainerData.map(w => w.trainer))];
                  const trainerAvgRatings = trainers.map(trainer => {
                        const workshopsByTrainer = trainerData.filter(w => w.trainer === trainer);
                        const totalRating = workshopsByTrainer.reduce((sum, w) => sum + w.rating, 0);
                        return workshopsByTrainer.length > 0 ? (totalRating / workshopsByTrainer.length).toFixed(1) : 0;
                  });
                 const ctxTrainerRating = document.getElementById('trainerRatingChart')?.getContext('2d');
                 if(ctxTrainerRating) {
                     new Chart(ctxTrainerRating, {
                        type: 'bar', data: { labels: trainers, datasets: [{ label: 'متوسط التقييم (من 5)', data: trainerAvgRatings, backgroundColor: chartColors, borderColor: chartBorderColors, borderWidth: 1 }] },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: false, min: 4.0, max: 5, ticks: { font: chartFont } }, x: { ticks: { font: chartFont } } }, plugins: { legend: { display: false }, tooltip: { bodyFont: chartFont, titleFont: chartFont } } }
                    });
                 }
             }

            // ==============================================
            // 4. Final Evaluation Report Logic
            // ==============================================
             function setupFinalEvalReport() {
                 const finalEvalData = getDataFromTable('#final-eval-tab-pane .final-eval-table', row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length < 4) return null;
                    const scoreText = cells[1].textContent.trim();
                    const scoreMatch = scoreText.match(/(\d+(\.\d+)?)\s*\/\s*100/);
                    return {
                        name: cells[0].textContent.trim(), score: scoreMatch ? parseFloat(scoreMatch[1]) : null,
                        status: cells[3].querySelector('.status-badge') ? cells[3].querySelector('.status-badge').textContent.trim() : null
                    };
                 });
                 const scores = finalEvalData.map(s => s.score).filter(s => s !== null);

                 // Chart: Score Distribution
                 const scoreBins = [ { label: '90-100', min: 90, max: 100, count: 0 }, { label: '80-89', min: 80, max: 89.9, count: 0 }, { label: '70-79', min: 70, max: 79.9, count: 0 }, { label: '60-69', min: 60, max: 69.9, count: 0 }, { label: '< 60', min: 0, max: 59.9, count: 0 } ];
                 scores.forEach(score => { for (const bin of scoreBins) { if (score >= bin.min && score <= bin.max) { bin.count++; break; } } });
                 const ctxFeScoreDist = document.getElementById('finalEvalScoreDistributionChart')?.getContext('2d');
                 if(ctxFeScoreDist) {
                     new Chart(ctxFeScoreDist, {
                        type: 'bar', data: { labels: scoreBins.map(bin => bin.label), datasets: [{ label: 'عدد الشركات', data: scoreBins.map(bin => bin.count), backgroundColor: ['#198754', '#0d6efd', '#0dcaf0', '#ffc107', '#dc3545'], borderColor: chartBorderColors, borderWidth: 1 }] },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { font: chartFont, stepSize: 1 } }, x: { ticks: { font: chartFont } } }, plugins: { legend: { display: false }, tooltip: { bodyFont: chartFont, titleFont: chartFont } } }
                    });
                 }

                // Populate Summary Lists
                const feTopCompaniesList = document.getElementById('feTopCompaniesList');
                const feImprovementCompaniesList = document.getElementById('feImprovementCompaniesList');
                feTopCompaniesList.innerHTML = ''; feImprovementCompaniesList.innerHTML = '';
                const sortedFeData = [...finalEvalData].sort((a, b) => b.score - a.score);
                sortedFeData.slice(0, 3).forEach(c => { feTopCompaniesList.innerHTML += `<li><i class="fas fa-star"></i> ${c.name} <small>(${c.score}/100)</small></li>`; });
                sortedFeData.filter(c => c.score < 70).forEach(c => { feImprovementCompaniesList.innerHTML += `<li><i class="fas fa-wrench"></i> ${c.name} <small>(${c.score}/100)</small></li>`; });
                 if (feImprovementCompaniesList.children.length === 0) feImprovementCompaniesList.innerHTML = '<li>لا توجد شركات حالياً.</li>';
                 if (feTopCompaniesList.children.length === 0) feTopCompaniesList.innerHTML = '<li>لا توجد بيانات.</li>';


                 // Filter Simulation
                 document.querySelector('.final-eval-filter-form')?.addEventListener('submit', e => { e.preventDefault(); console.log('Final Eval Filter Submitted'); alert('محاكاة: تم تطبيق فلتر التقييم النهائي!'); });
             }

             // ==============================================
             // 5. Program Summary Report Logic
             // ==============================================
              function setupSummaryReport() {
                    const finalEvalData = getDataFromTable('#final-eval-tab-pane .final-eval-table', row => { // Get data again for summary
                        const cells = row.querySelectorAll('td');
                        if (cells.length < 4) return null;
                         const scoreText = cells[1].textContent.trim();
                         const scoreMatch = scoreText.match(/(\d+(\.\d+)?)\s*\/\s*100/);
                         return {
                            score: scoreMatch ? parseFloat(scoreMatch[1]) : null,
                            status: cells[3].querySelector('.status-badge') ? cells[3].querySelector('.status-badge').textContent.trim() : null
                        };
                    });
                    const scores = finalEvalData.map(s => s.score).filter(s => s !== null);
                    const evaluatedCount = scores.length;
                    const avgScore = evaluatedCount > 0 ? (scores.reduce((a, b) => a + b, 0) / evaluatedCount).toFixed(1) : '--';

                    // Update KPI
                    document.getElementById('summaryAvgFinalScore').textContent = avgScore;

                    // Status Distribution Chart
                    const statusCounts = { 'Highest Rated': 1, 'Good Performance': 2, 'Needs Improvement': 4 };
                    finalEvalData.forEach(item => {
                        if (item.status && statusCounts.hasOwnProperty(item.status)) {
                            statusCounts[item.status]++;
                        } else if (item.status && item.status.includes('Good')) { // Handle variations like "Good Performance" vs "Good"
                             statusCounts['Good Performance']++;
                        } else if (item.status && item.status.includes('Improvement')) {
                             statusCounts['Needs Improvement']++;
                        } else if (item.status && item.status.includes('Highest')) {
                              statusCounts['Highest Rated']++;
                        }
                    });

                    const ctxSummaryStatus = document.getElementById('summaryStatusDistributionChart')?.getContext('2d');
                    if(ctxSummaryStatus) {
                        // Log to check if element exists and has context
                        // console.log('Canvas element:', document.getElementById('summaryStatusDistributionChart'));
                        // console.log('Canvas context:', ctxSummaryStatus);
                        // console.log('Status counts:', statusCounts);

                        // Check if chart container has height
                        const container = document.getElementById('summaryStatusDistributionChart').parentElement;
                        console.log('Container height:', container.offsetHeight);

                        new Chart(ctxSummaryStatus, {
                            type: 'pie',
                            data: {
                                labels: Object.keys(statusCounts),
                                datasets: [{
                                    label: 'عدد الشركات',
                                    data: Object.values(statusCounts),
                                    backgroundColor: [chartColors[1], chartColors[0], chartColors[5]], // Success, Primary, Danger
                                    borderColor: chartBorderColors,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: {
                                            font: chartFont,
                                            padding: 20,
                                            usePointStyle: true,
                                            pointStyle: 'circle'
                                        }
                                    },
                                    tooltip: {
                                        enabled: true,
                                        bodyFont: chartFont,
                                        titleFont: chartFont,
                                        backgroundColor: 'rgba(0,0,0,0.8)',
                                        padding: 12,
                                        cornerRadius: 6,
                                        displayColors: true,
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.raw || 0;
                                                const total = context.dataset.data.reduce((a,b) => a+b, 0);
                                                const percentage = ((value / total) * 100).toFixed(1);
                                                return `${label}: ${value} (${percentage}%)`;
                                            }
                                        }
                                    }
                                },
                                layout: {
                                    padding: 20
                                },
                                cutout: '60%',
                                radius: '90%',
                                animation: {
                                    animateRotate: true,
                                    animateScale: true
                                }
                            }
                        });
                    } else {
                        console.error('Could not get canvas context for summary status chart');
                    }

                 // Update summary list counts
                  document.getElementById('summaryTopCompanies').innerHTML = `<i class="fas fa-trophy"></i> Highest Rated: ${statusCounts['Highest Rated']} companies`;
                  document.getElementById('summaryGoodCompanies').innerHTML = `<i class="fas fa-thumbs-up"></i> Good Performance: ${statusCounts['Good Performance']} companies`;
                  document.getElementById('summaryImprovementCompanies').innerHTML = `<i class="fas fa-flag"></i> Needs Improvement: ${statusCounts['Needs Improvement']} companies`;
              }

            // --- Initialize all reports ---
            setupPerformanceReport();
            setupMentorshipReport();
            setupWorkshopsReport();
            setupFinalEvalReport();
            setupSummaryReport(); // Run summary last as it might depend on other data
        });
  </script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<!-- For Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<!-- For PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function () {
    $('#workshopTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf me-1"></i> {{ __("admin.PDF") }}',
                className: 'btn btn-label-danger btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel me-1"></i> {{ __("admin.Excel") }}',
                className: 'btn btn-label-success btn-sm',
                exportOptions: {
                    columns: ':visible'
                }
            }
        ],
    });
});
</script>




  @stop