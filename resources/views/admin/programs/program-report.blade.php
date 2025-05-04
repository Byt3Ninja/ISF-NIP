@extends('layouts.admin')

@section('style')

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
    <h4 class="fw-bold">Startup Program Dashboard</h4>

    <div class="card card-body">
      <!-- Tab Navigation -->
      <ul class="nav nav-tabs border-bottom mb-8 pb-4" id="mainDashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary-tab-pane"
            type="button" role="tab" aria-controls="summary-tab-pane" aria-selected="true">
            <i class="fas fa-file-alt me-2"></i> Program Summary
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="performance-tab" data-bs-toggle="tab" data-bs-target="#performance-tab-pane"
            type="button" role="tab" aria-controls="performance-tab-pane" aria-selected="false">
            <i class="fas fa-chart-line me-2"></i> Startup Performance
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="mentorship-tab" data-bs-toggle="tab" data-bs-target="#mentorship-tab-pane"
            type="button" role="tab" aria-controls="mentorship-tab-pane" aria-selected="false">
            <i class="fas fa-people-arrows me-2"></i> Mentorship & Advisory
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="workshops-tab" data-bs-toggle="tab" data-bs-target="#workshops-tab-pane"
            type="button" role="tab" aria-controls="workshops-tab-pane" aria-selected="false">
            <i class="fas fa-person-chalkboard me-2"></i> Workshops
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="final-eval-tab" data-bs-toggle="tab" data-bs-target="#final-eval-tab-pane"
            type="button" role="tab" aria-controls="final-eval-tab-pane" aria-selected="false">
            <i class="fas fa-trophy me-2"></i> Final Evaluation
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
                <div class="kpi-value" id="summaryTotalStartups">9</div>
                <div class="kpi-label">Total Startups</div>
              </div>
            </div>
            <div class="col-lg col-md-4 col-sm-6">
              <div class="kpi-card">
                <i class="fas fa-user-tie kpi-icon text-info"></i>
                <div class="kpi-value" id="summaryTotalMentors">5</div>
                <div class="kpi-label">Total Mentors</div>
              </div>
            </div>
            <div class="col-lg col-md-4 col-sm-6">
              <div class="kpi-card">
                <i class="fas fa-chalkboard-user kpi-icon text-success"></i>
                <div class="kpi-value" id="summaryTotalWorkshops">14</div>
                <div class="kpi-label">Total Workshops</div>
              </div>
            </div>
            <div class="col-lg col-md-4 col-sm-6">
              <div class="kpi-card">
                <i class="fas fa-star kpi-icon text-warning"></i>
                <div class="kpi-value" id="summaryAvgFinalScore">--</div>
                <div class="kpi-label">Average Final Score</div>
              </div>
            </div>
            <div class="col-lg col-md-4 col-sm-6">
              <div class="kpi-card">
                <i class="fas fa-check-circle kpi-icon text-danger"></i>
                <div class="kpi-value" id="summaryEvaluationsComplete">83%</div>
                <div class="kpi-label">Evaluation Completion</div>
              </div>
            </div>
          </div>

          <hr>

          <div class="row mt-4">
            <div class="col-md-6">
              <div class="card card-body border h-100 d-flex flex-column">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i
                    class="fas fa-tasks text-primary me-2"></i> Current Program Status</h4>
                <p class="mt-6">This summary provides a comprehensive overview of the participating startups'
                  performance and
                  aggregates key metrics from various evaluation reports.</p>
                <div>
                  <div class="py-8">
                    <h6 class="mt-3 fw-bold">Key Program Stages:</h6>
                    <ul class="list-unstyled">
                      <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Registration & Selection
                      </li>
                      <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Foundational Workshops</li>
                      <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Mentorship & Guidance</li>
                      <li class="mb-2"><i class="fas fa-spinner fa-spin text-primary me-2"></i> Final Evaluation &
                        Presentations</li>
                      <li class="mb-2"><i class="far fa-circle text-muted me-2"></i> Post-Program Phase</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card card-body border h-100">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i
                    class="fas fa-chart-pie text-primary me-2"></i> Distribution by Final Status</h4>
                <div class="chart-container" style="height: 250px; background-color: #f8f9fa;">
                  <canvas id="summaryStatusDistributionChart"></canvas>
                </div>
                <div class="w-100">
                  <h6 class="mt-4 fw-bold">Results Summary:</h6>
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item" id="summaryTopCompanies"><i class="fas fa-trophy text-muted"></i>
                      Highest Rated: --</li>
                    <li class="list-group-item" id="summaryGoodCompanies"><i class="fas fa-thumbs-up"
                        style="color:#28a745"></i> Good Performance: --</li>
                    <li class="list-group-item" id="summaryImprovementCompanies"><i class="fas fa-flag"
                        style="color:#dc3545"></i> Needs Improvement: --</li>
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
            <h5 class=""><i class="fas fa-filter"></i> Filter Options (Performance)</h5>
            <form class="row g-3 align-items-end performance-filter-form">
              <div class="col-md-3 col-sm-6"> <label for="perfFilterSector"
                  class="form-label fw-semibold">Sector</label> <select class="form-select select2"
                  id="perfFilterSector">
                  <option selected value="">All</option>
                  <option value="Fintech">Fintech</option>
                  <option value="Health">Healthcare</option>
                  <option value="Education">Education</option>
                  <option value="E-commerce">E-commerce</option>
                  <option value="Energy">Energy</option>
                  <option value="AI">Artificial Intelligence</option>
                </select> </div>
              <div class="col-md-3 col-sm-6"> <label for="perfFilterStage" class="form-label fw-semibold">Stage</label>
                <select class="form-select select2" id="perfFilterStage">
                  <option selected value="">All</option>
                  <option value="Idea">Idea</option>
                  <option value="Prototype">Prototype</option>
                  <option value="Market">Market</option>
                  <option value="Growth">Growth</option>
                  <option value="Scale">Scale</option>
                </select>
              </div>
              <div class="col-md-2 col-sm-6"> <label for="perfFilterMinRating" class="form-label fw-semibold">Min
                  Rating</label>
                <input type="number" class="form-control" id="perfFilterMinRating" min="1" max="5" step="0.1"
                  placeholder="e.g., 3.5">
              </div>
              <div class="col-md-2 col-sm-6"> <label for="perfFilterMinProgress" class="form-label fw-semibold">Min
                  Progress
                  (%)</label>
                <input type="number" class="form-control form-control" id="perfFilterMinProgress" min="0" max="100"
                  step="5" placeholder="e.g., 50">
              </div>
              <div class="col-md-2 col-sm-12 text-start mt-3 mt-md-0"> <button type="submit"
                  class="btn btn-primary w-100"><i class="fas fa-search me-2"></i> Search</button> </div>
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
                        class="fas fa-chart-pie text-primary me-2"></i> Distribution by Sector</h4>
                    <div class="chart-container">
                      <canvas id="perfSectorDistributionChart"></canvas>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <!-- Chart 2: Average Progress by Stage -->
                  <div class="card card-body border">
                    <h4 class="mb-6 fw-semibold border-bottom border-2 border-primary"><i
                        class="fas fa-chart-bar text-primary me-2"></i> Average Progress by Stage</h4>
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
                    class="fas fa-list text-primary me-2"></i> Startup Performance List</h4>
                <div class="table-responsive">
                  <!-- Copied Performance Table Structure Here -->
                  <table class="table table-striped table-hover align-middle performance-table">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">Company Name</th>
                        <th scope="col">Sector</th>
                        <th scope="col">Stage</th>
                        <th scope="col">Rating</th>
                        <th scope="col">Progress (%)</th>
                        <th scope="col">Notes</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Rows from performance report example -->
                      <tr>
                        <th scope="row">1</th>
                        <td class="company-name">Alpha Tech</td>
                        <td>Fintech</td>
                        <td>Market</td>
                        <td><span class="badge bg-label-success rating-badge">4.8/5</span></td>
                        <td>
                          <div class="progress" role="progressbar" aria-valuenow="90">
                            <div class="progress-bar bg-success" style="width: 90%">90%</div>
                          </div>
                        </td>
                        <td class="notes-col">Continuous growth...</td>
                      </tr>
                      <tr>
                        <th scope="row">2</th>
                        <td class="company-name">Beta Solutions</td>
                        <td>Healthcare</td>
                        <td>Prototype</td>
                        <td><span class="badge bg-label-primary rating-badge">4.2/5</span></td>
                        <td>
                          <div class="progress" role="progressbar" aria-valuenow="75">
                            <div class="progress-bar bg-primary" style="width: 75%">75%</div>
                          </div>
                        </td>
                        <td class="notes-col">Needs further testing...</td>
                      </tr>
                      <tr>
                        <th scope="row">3</th>
                        <td class="company-name">Gamma Education</td>
                        <td>Education</td>
                        <td>Idea</td>
                        <td><span class="badge bg-warning text-dark rating-badge">3.5/5</span></td>
                        <td>
                          <div class="progress" role="progressbar" aria-valuenow="40">
                            <div class="progress-bar bg-warning text-dark" style="width: 40%">40%</div>
                          </div>
                        </td>
                        <td class="notes-col">Needs development...</td>
                      </tr>
                      <tr>
                        <th scope="row">4</th>
                        <td class="company-name">Delta Commerce</td>
                        <td>E-commerce</td>
                        <td>Growth</td>
                        <td><span class="badge bg-info rating-badge">4.5/5</span></td>
                        <td>
                          <div class="progress" role="progressbar" aria-valuenow="85">
                            <div class="progress-bar bg-info" style="width: 85%">85%</div>
                          </div>
                        </td>
                        <td class="notes-col">Good growth...</td>
                      </tr>
                      <tr>
                        <th scope="row">5</th>
                        <td class="company-name">Epsilon Energy</td>
                        <td>Energy</td>
                        <td>Prototype</td>
                        <td><span class="badge bg-secondary rating-badge">3.1/5</span></td>
                        <td>
                          <div class="progress" role="progressbar" aria-valuenow="55">
                            <div class="progress-bar bg-secondary" style="width: 55%">55%</div>
                          </div>
                        </td>
                        <td class="notes-col">Technical challenges...</td>
                      </tr>
                      <tr>
                        <th scope="row">6</th>
                        <td class="company-name">Zeta AI</td>
                        <td>Artificial Intelligence</td>
                        <td>Idea</td>
                        <td><span class="badge bg-danger rating-badge">2.5/5</span></td>
                        <td>
                          <div class="progress" role="progressbar" aria-valuenow="20">
                            <div class="progress-bar bg-danger" style="width: 20%">20%</div>
                          </div>
                        </td>
                        <td class="notes-col">Promising potential...</td>
                      </tr>
                      <tr>
                        <th scope="row">7</th>
                        <td class="company-name">Theta Finance</td>
                        <td>Fintech</td>
                        <td>Growth</td>
                        <td><span class="badge bg-label-primary rating-badge">4.6/5</span></td>
                        <td>
                          <div class="progress" role="progressbar" aria-valuenow="88">
                            <div class="progress-bar bg-primary" style="width: 88%">88%</div>
                          </div>
                        </td>
                        <td class="notes-col">Attracting investments...</td>
                      </tr>
                      <tr>
                        <th scope="row">8</th>
                        <td class="company-name">Iota Healthcare</td>
                        <td>Healthcare</td>
                        <td>Market</td>
                        <td><span class="badge bg-label-success rating-badge">4.9/5</span></td>
                        <td>
                          <div class="progress" role="progressbar" aria-valuenow="92">
                            <div class="progress-bar bg-success" style="width: 92%">92%</div>
                          </div>
                        </td>
                        <td class="notes-col">Successful partnerships...</td>
                      </tr>
                      <tr>
                        <th scope="row">9</th>
                        <td class="company-name">Kappa Digital</td>
                        <td>Education</td>
                        <td>Prototype</td>
                        <td><span class="badge bg-label-primary rating-badge">4.0/5</span></td>
                        <td>
                          <div class="progress" role="progressbar" aria-valuenow="60">
                            <div class="progress-bar bg-primary" style="width: 60%">60%</div>
                          </div>
                        </td>
                        <td class="notes-col">Launching a beta version...</td>
                      </tr>
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
              Filter Options (Mentorship)</h5>
            <form class="row g-3 align-items-end mentorship-filter-form">
              <div class="col-md-3"><label for="mentorFilterMentor" class="form-label">Mentor</label><select
                  class="form-select select2" id="mentorFilterMentor">
                  <option selected>All</option>
                  <option value="1">Ahmed Ali</option>
                  <option value="2">Fatima Al-Zahra</option>
                  <option value="3">Khalid Mahmoud</option>
                </select></div>
              <div class="col-md-3"><label for="mentorFilterCompany" class="form-label">Company</label><select
                  class="form-select select2" id="mentorFilterCompany">
                  <option selected>All</option>
                  <option value="a">Alpha Company</option>
                  <option value="b">Beta Solutions</option>
                </select></div>
              <div class="col-md-3"><label for="mentorFilterDateStart" class="form-label">From Date</label><input
                  type="date" class="form-control" id="mentorFilterDateStart"></div>
              <div class="col-md-3 text-start"><button type="submit" class="btn btn-primary w-50"><i
                    class="fas fa-search me-2"></i> Search</button></div>
            </form>
          </div>
          <!-- Content from mentorship report -->
          <!-- KPIs Row -->
          <div class="row justify-content-center mb-4 mt-4">
            <div class="col-lg-3 col-md-6">
              <div class="kpi-card-sm"> <i class="fas fa-users-gear kpi-icon text-primary"></i>
                <div class="kpi-value-sm">+150</div>
                <div class="kpi-label-sm">Total Sessions</div>
              </div>
            </div>
            <div class="col-lg-3 col-md-6">
              <div class="kpi-card-sm"> <i class="fas fa-clock kpi-icon text-success"></i>
                <div class="kpi-value-sm">+400</div>
                <div class="kpi-label-sm">Total Hours</div>
              </div>
            </div>
            <div class="col-lg-3 col-md-6">
              <div class="kpi-card-sm"> <i class="fas fa-star kpi-icon text-warning"></i>
                <div class="kpi-value-sm">4.6<small>/5</small></div>
                <div class="kpi-label-sm">Average Rating</div>
              </div>
            </div>
          </div>
          <div class="row mt-2">
            <div class="col-lg-7">
              <!-- Table 1: Mentorship Details per Company -->
              <div class="mb-4">
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-building-user text-primary me-2"></i> Mentorship Details by Company</h4>
                <div class="table-responsive">
                  <!-- Copied Mentorship Table 1 Structure Here -->
                  <table class="table table-hover align-middle mentorship-company-table">
                    <thead>
                      <tr>
                        <th scope="col">Company Name</th>
                        <th scope="col">Mentor</th>
                        <th scope="col">Sessions</th>
                        <th scope="col">Hours</th>
                        <th scope="col">Mentor Rating</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="text-right-align">Alpha Company</td>
                        <td>Ahmed Ali</td>
                        <td>15</td>
                        <td>30</td>
                        <td><span class="badge bg-label-success rating-badge">4.9/5</span></td>
                      </tr>
                      <tr>
                        <td class="text-right-align">Beta Solutions</td>
                        <td>Fatima Al-Zahra</td>
                        <td>12</td>
                        <td>24</td>
                        <td><span class="badge bg-label-primary rating-badge">4.7/5</span></td>
                      </tr>
                      <!-- ... more rows -->
                    </tbody>
                  </table>
                </div>
              </div>
              <!-- Table 2: Mentor Performance -->
              <div>
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-user-tie text-primary me-2"></i> Mentor Performance</h4>
                <div class="table-responsive">
                  <!-- Copied Mentorship Table 2 Structure Here -->
                  <table class="table table-hover align-middle mentorship-mentor-table">
                    <thead>
                      <tr>
                        <th scope="col">Mentor</th>
                        <th scope="col">Total Sessions</th>
                        <th scope="col">Total Hours</th>
                        <th scope="col">Average Rating</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="text-right-align">Ahmed Ali</td>
                        <td>35</td>
                        <td>70</td>
                        <td><span class="badge bg-label-success rating-badge">4.8/5</span></td>
                      </tr>
                      <tr>
                        <td class="text-right-align">Fatima Al-Zahra</td>
                        <td>30</td>
                        <td>60</td>
                        <td><span class="badge bg-label-primary rating-badge">4.6/5</span></td>
                      </tr>
                      <!-- ... more rows -->
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
              Filter Options (Workshops)</h5>
            <form class="row g-3 align-items-end workshops-filter-form">
              <div class="col-md-3"><label for="wsFilterTrainer" class="form-label">Trainer</label><select
                  class="form-select select2" id="wsFilterTrainer">
                  <option selected value="">All</option>
                  <option value="1">Dr. Sarah</option>
                </select></div>
              <div class="col-md-3"><label for="wsFilterWorkshop" class="form-label">Workshop Name</label><input
                  type="text" class="form-control" id="wsFilterWorkshop" placeholder="Search..."></div>
              <div class="col-md-3"><label for="wsFilterType" class="form-label">Workshop Type</label><select
                  class="form-select select2" id="wsFilterType">
                  <option selected value="">All</option>
                  <option value="Online">Zoom</option>
                  <option value="InPerson">In-Person</option>
                </select></div>
              <div class="col-md-3 text-start"><button type="submit" class="btn btn-primary w-50"><i
                    class="fas fa-search me-2"></i> Search</button></div>
            </form>
          </div>
          <!-- Content from workshops report -->
          <!-- KPIs Row -->
          <div class="row justify-content-center mb-4 mt-4">
            <div class="col-lg-2 col-md-4 col-sm-6">
              <div class="kpi-card-sm"><i class="fas fa-chalkboard-user kpi-icon text-primary"></i>
                <div class="kpi-value-sm">14</div>
                <div class="kpi-label-sm">Total Workshops</div>
              </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
              <div class="kpi-card-sm"><i class="fas fa-clock kpi-icon text-success"></i>
                <div class="kpi-value-sm">+50</div>
                <div class="kpi-label-sm">Total Hours</div>
              </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
              <div class="kpi-card-sm"><i class="fas fa-video kpi-icon text-warning"></i>
                <div class="kpi-value-sm">8</div>
                <div class="kpi-label-sm">Zoom Workshops</div>
              </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
              <div class="kpi-card-sm"><i class="fas fa-users kpi-icon text-info"></i>
                <div class="kpi-value-sm">6</div>
                <div class="kpi-label-sm">In-Person Workshops</div>
              </div>
            </div>
          </div>
          <div class="row mt-2">
            <div class="col-lg-7">
              <!-- Workshop Details Table -->
              <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-list-check text-primary me-2"></i> Workshop Details</h4>
                  <div class="export-buttons"> <button type="button" class="btn btn-label-danger btn-sm"><i
                        class="fas fa-file-pdf me-1"></i> PDF</button> <button type="button"
                      class="btn btn-label-success btn-sm ms-2"><i class="fas fa-file-excel me-1"></i> Excel</button>
                  </div>
                </div>
                <div class="table-responsive">
                  <!-- Copied Workshop Table 1 Structure Here -->
                  <table class="table table-hover align-middle workshop-details-table">
                    <thead>
                      <tr>
                        <th scope="col">Workshop Name</th>
                        <th scope="col">Trainer</th>
                        <th scope="col">Participants</th>
                        <th scope="col">Rating</th>
                        <th scope="col">Type</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="ws-name">Business Model Canvas</td>
                        <td class="trainer-name">Dr. Sarah Ibrahim</td>
                        <td>25</td>
                        <td><span class="badge bg-label-primary rating-badge">4.7/5</span></td>
                        <td><span class="badge bg-warning text-dark">In-Person</span></td>
                      </tr>
                      <tr>
                        <td class="ws-name">Digital Marketing</td>
                        <td class="trainer-name">Mr. Omar Hassan</td>
                        <td>22</td>
                        <td><span class="badge bg-info rating-badge">4.5/5</span></td>
                        <td><span class="badge bg-secondary">Zoom</span></td>
                      </tr>
                      <!-- ... more rows -->
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
                        <th scope="col">Company Name</th>
                        <th scope="col">Completion Rate</th>
                        <th scope="col">Pass/Fail</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="text-right-align">Alpha Company</td>
                        <td><span class="badge bg-success">100%</span></td>
                        <td><i class="fas fa-check-circle text-success pass-fail-icon"></i></td>
                      </tr>
                      <tr>
                        <td class="text-right-align">Beta Solutions</td>
                        <td><span class="badge bg-label-primary">90%</span></td>
                        <td><i class="fas fa-check-circle text-success pass-fail-icon"></i></td>
                      </tr>
                      <tr>
                        <td class="text-right-align">Gamma Education</td>
                        <td><span class="badge bg-warning text-dark">75%</span></td>
                        <td><i class="fas fa-times-circle text-danger pass-fail-icon"></i></td>
                      </tr>
                      <!-- ... more rows -->
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
              <!-- Placeholder for Survey Results -->
              <div>
                <h4 class="fw-semibold border-bottom border-2 border-primary"><i class="fas fa-poll text-primary me-2"></i> Survey Results</h4>
                <p class="text-muted text-center p-3 border rounded">
                  <i class="fas fa-info-circle me-1"></i> Survey analysis results will appear here.
                </p>
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
                        type: 'doughnut', data: { labels: sectors, datasets: [{ label: 'عدد الشركات', data: sectorCounts, backgroundColor: chartColors, borderColor: chartBorderColors, borderWidth: 1 }] },
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
                document.querySelector('.performance-filter-form')?.addEventListener('submit', e => { e.preventDefault(); console.log('Performance Filter Submitted'); alert('محاكاة: تم تطبيق فلتر الأداء!'); });
            }

            // ==============================================
            // 2. Mentorship Report Logic
            // ==============================================
            function setupMentorshipReport() {
                // Data for charts (using placeholder/example data from previous script)
                const mentorNames = ['Ahmed Ali', 'Fatima Al-Zahra', 'Khalid Mahmoud', 'Sarah Hanan', 'Mohammed Abdullah'];
                const mentorSessions = [35, 30, 28, 25, 32];
                const mentorAvgRatings = [4.8, 4.6, 4.7, 4.3, 4.5];

                 // Chart 1: Mentor Session Distribution
                 const ctxMentorSessions = document.getElementById('mentorSessionsChart')?.getContext('2d');
                 if(ctxMentorSessions) {
                    new Chart(ctxMentorSessions, {
                        type: 'pie', data: { labels: mentorNames, datasets: [{ label: 'عدد الجلسات', data: mentorSessions, backgroundColor: chartColors, borderColor: chartBorderColors, borderWidth: 1 }] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { font: chartFont } }, tooltip: { bodyFont: chartFont, titleFont: chartFont } } }
                    });
                 }

                 // Chart 2: Mentor Average Rating
                 const ctxMentorRatings = document.getElementById('mentorRatingChart')?.getContext('2d');
                 if(ctxMentorRatings) {
                     new Chart(ctxMentorRatings, {
                        type: 'bar', data: { labels: mentorNames, datasets: [{ label: 'متوسط التقييم (من 5)', data: mentorAvgRatings, backgroundColor: chartColors, borderColor: chartBorderColors, borderWidth: 1 }] },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: false, min: 3.5, max: 5, ticks: { font: chartFont } }, x: { ticks: { font: chartFont } } }, plugins: { legend: { display: false }, tooltip: { bodyFont: chartFont, titleFont: chartFont } } }
                    });
                 }

                 // Filter Simulation
                 document.querySelector('.mentorship-filter-form')?.addEventListener('submit', e => { e.preventDefault(); console.log('Mentorship Filter Submitted'); alert('محاكاة: تم تطبيق فلتر الإرشاد!'); });
            }

             // ==============================================
            // 3. Workshops Report Logic
            // ==============================================
             function setupWorkshopsReport() {
                 // Chart 1: Workshop Type Distribution
                 const workshopTypeCounts = { 'Online': 8, 'InPerson': 6 }; // From KPIs
                 const ctxWsType = document.getElementById('workshopTypeChart')?.getContext('2d');
                 if(ctxWsType) {
                     new Chart(ctxWsType, {
                        type: 'pie', data: { labels: ['عبر Zoom', 'حضورية'], datasets: [{ label: 'عدد الورش', data: [workshopTypeCounts.Online, workshopTypeCounts.InPerson], backgroundColor: [chartColors[6], chartColors[2]], borderColor: [chartBorderColors[6], chartBorderColors[2]], borderWidth: 1 }] },
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

                  // Filter Simulation
                 document.querySelector('.workshops-filter-form')?.addEventListener('submit', e => { e.preventDefault(); console.log('Workshops Filter Submitted'); alert('محاكاة: تم تطبيق فلتر الورش!'); });
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

  @stop