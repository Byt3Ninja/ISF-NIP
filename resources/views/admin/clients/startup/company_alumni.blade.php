@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
  <!-- Content -->
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-3 align-items-center mb-4">
      <div class="col-12 col-md-6">
        <h4 class="mb-1">Startup Alumni Report</h4>
        <p class="text-muted mb-0">Program based alumni insights for a single startup</p>
      </div>
      <div class="col-12 col-md-6 d-flex justify-content-md-end">
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-label-success pe-4 rounded-pill">
            <i class="ti ti-point-filled"></i>
            Alumni
          </span>
          <button class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-file-type-pdf me-1"></i>Export PDF
          </button>
          <button class="btn btn-sm btn-outline-secondary">
            <i class="ti ti-file-type-csv me-1"></i>Export CSV
          </button>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-12 col-xl-8">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
              <div>
                <h5 class="mb-1">PayLinkx</h5>
                <span class="text-muted small">Startup ID ST-2025-001</span>
              </div>
              <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-label-primary">Fintech</span>
                <span class="badge bg-label-secondary">SaaS</span>
                <span class="badge bg-label-info">Egypt &middot; Cairo</span>
              </div>
            </div>
            <div class="row row-cols-2 row-cols-md-4 g-3 pt-4">
              <div>
                <small class="text-muted text-uppercase">Established</small>
                <h6 class="fw-semibold mb-0 mt-1">2019-05-01</h6>
              </div>
              <div>
                <small class="text-muted text-uppercase">Business type</small>
                <h6 class="fw-semibold mb-0 mt-1">B2B</h6>
              </div>
              <div>
                <small class="text-muted text-uppercase">Industry</small>
                <h6 class="fw-semibold mb-0 mt-1">Financial Services</h6>
              </div>
              <div>
                <small class="text-muted text-uppercase">Sector</small>
                <h6 class="fw-semibold mb-0 mt-1">Payments</h6>
              </div>
            </div>
            <div class="pt-3 d-flex flex-wrap gap-3">
              <a href="#" class="d-inline-flex align-items-center fw-medium">
                Website <i class="ti ti-external-link ms-1"></i>
              </a>
              <a href="#" class="d-inline-flex align-items-center fw-medium">
                LinkedIn <i class="ti ti-external-link ms-1"></i>
              </a>
              <a href="#" class="d-inline-flex align-items-center fw-medium">
                Twitter <i class="ti ti-external-link ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-4">
        <div class="card h-100">
          <div class="card-header border-bottom">
            <div>
              <h6 class="mb-0">Program snapshot</h6>
              <small class="text-muted">Fintech Accelerator 2025</small>
            </div>
          </div>
          <div class="card-body pt-3">
            <dl class="row mb-0 gy-2 gx-0">
              <dt class="col-5 text-muted fw-normal">Program ID</dt>
              <dd class="col-7 text-end">PR-FT-2025-01</dd>
              <dt class="col-5 text-muted fw-normal">Organization</dt>
              <dd class="col-7 text-end">GrowthLabs</dd>
              <dt class="col-5 text-muted fw-normal">Cohort</dt>
              <dd class="col-7 text-end">Cohort 3</dd>
              <dt class="col-5 text-muted fw-normal">Start</dt>
              <dd class="col-7 text-end">10 Jan 2025</dd>
              <dt class="col-5 text-muted fw-normal">Completion</dt>
              <dd class="col-7 text-end">15 Jun 2025</dd>
            </dl>
            <div class="row g-2 pt-4">
              <div class="col-4">
                <div class="border rounded-3 text-center p-2">
                  <small class="text-muted d-block">Trainings</small>
                  <h5 class="mb-0">18</h5>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded-3 text-center p-2">
                  <small class="text-muted d-block">Mentorship</small>
                  <h5 class="mb-0">32h</h5>
                </div>
              </div>
              <div class="col-4">
                <div class="border rounded-3 text-center p-2">
                  <small class="text-muted d-block">Tasks</small>
                  <h5 class="mb-0">24 / 26</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-body py-3 px-1" style="max-width: 100%; overflow-x: hidden;">
        <div class="nav-align-left">
          <ul class="nav nav-tabs me-4" role="tablist">
            <li class="nav-item" role="presentation">
              <button type="button" class="nav-link active" id="tab-basic-trigger" data-bs-toggle="tab"
                data-bs-target="#tab-basic" role="tab" aria-controls="tab-basic" aria-selected="true">
                Startup info
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button type="button" class="nav-link" id="tab-team-trigger" data-bs-toggle="tab"
                data-bs-target="#tab-team" role="tab" aria-controls="tab-team" aria-selected="false">
                Team
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button type="button" class="nav-link" id="tab-ownership-trigger" data-bs-toggle="tab"
                data-bs-target="#tab-ownership" role="tab" aria-controls="tab-ownership" aria-selected="false">
                Ownership &amp; investors
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button type="button" class="nav-link" id="tab-screening-trigger" data-bs-toggle="tab"
                data-bs-target="#tab-screening" role="tab" aria-controls="tab-screening" aria-selected="false">
                Screening
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button type="button" class="nav-link" id="tab-training-trigger" data-bs-toggle="tab"
                data-bs-target="#tab-training" role="tab" aria-controls="tab-training" aria-selected="false">
                Training
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button type="button" class="nav-link" id="tab-mentorship-trigger" data-bs-toggle="tab"
                data-bs-target="#tab-mentorship" role="tab" aria-controls="tab-mentorship" aria-selected="false">
                Mentorship
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button type="button" class="nav-link" id="tab-milestones-trigger" data-bs-toggle="tab"
                data-bs-target="#tab-milestones" role="tab" aria-controls="tab-milestones" aria-selected="false">
                Milestones
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button type="button" class="nav-link" id="tab-tasks-trigger" data-bs-toggle="tab"
                data-bs-target="#tab-tasks" role="tab" aria-controls="tab-tasks" aria-selected="false">
                Tasks
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button type="button" class="nav-link" id="tab-quizzes-trigger" data-bs-toggle="tab"
                data-bs-target="#tab-quizzes" role="tab" aria-controls="tab-quizzes" aria-selected="false">
                Quizzes
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button type="button" class="nav-link" id="tab-surveys-trigger" data-bs-toggle="tab"
                data-bs-target="#tab-surveys" role="tab" aria-controls="tab-surveys" aria-selected="false">
                Surveys
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button type="button" class="nav-link" id="tab-services-trigger" data-bs-toggle="tab"
                data-bs-target="#tab-services" role="tab" aria-controls="tab-services" aria-selected="false">
                Services
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button type="button" class="nav-link" id="tab-forms-trigger" data-bs-toggle="tab"
                data-bs-target="#tab-forms" role="tab" aria-controls="tab-forms" aria-selected="false">
                Submitted forms
              </button>
            </li>
          </ul>
          <div class="tab-content p-0 shadow-none" id="alumniTabContent" style="max-width: 100%; overflow-x: hidden;">
      
            <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-trigger">
              <div id="basic-info" class="card shadow-none mb-4">
                <div class="card-header p-2 d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="mb-0">Basic startup information</h6>
                    <small class="text-muted">Static data per startup record</small>
                  </div>
                  <span class="badge bg-label-success">Alumni</span>
                </div>
                <div class="card-body p-2">
                  <div class="row g-4">
                    <div class="col-12 col-md-4">
                      <small class="text-muted text-uppercase">ID</small>
                      <p class="mb-0 fw-semibold">ST-2025-001</p>
                    </div>
                    <div class="col-12 col-md-4">
                      <small class="text-muted text-uppercase">Startup name</small>
                      <p class="mb-0 fw-semibold">PayLinkx</p>
                    </div>
                    <div class="col-12 col-md-4">
                      <small class="text-muted text-uppercase">Established date</small>
                      <p class="mb-0 fw-semibold">2019-05-01</p>
                    </div>
                    <div class="col-12 col-md-4">
                      <small class="text-muted text-uppercase">Location</small>
                      <p class="mb-0 fw-semibold">Cairo, Egypt</p>
                    </div>
                    <div class="col-12 col-md-4">
                      <small class="text-muted text-uppercase">Business type</small>
                      <p class="mb-0 fw-semibold">B2B</p>
                    </div>
                    <div class="col-12 col-md-4">
                      <small class="text-muted text-uppercase">Technology</small>
                      <p class="mb-0 fw-semibold">AI, APIs, Cloud</p>
                    </div>
                    <div class="col-12 col-md-4">
                      <small class="text-muted text-uppercase">Website</small>
                      <p class="mb-0">
                        <a href="#" class="fw-medium">https://paylinkx.com</a>
                      </p>
                    </div>
                    <div class="col-12 col-md-8">
                      <small class="text-muted text-uppercase">Social media</small>
                      <div class="d-flex flex-wrap gap-3 mb-0">
                        <a href="#" class="fw-medium">LinkedIn</a>
                        <a href="#" class="fw-medium">Facebook</a>
                        <a href="#" class="fw-medium">Twitter</a>
                      </div>
                    </div>
                  </div>
                  <hr class="my-4" />
                  <div class="row g-4">
                    <div class="col-12 col-md-6">
                      <h6 class="mb-3">Sustainability goals</h6>
                      <ul class="list-group list-group-flush rounded border">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          <span>SDG 8 &mdash; Decent work and economic growth</span>
                          <span class="badge bg-label-primary">Primary</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          <span>SDG 9 &mdash; Industry innovation and infrastructure</span>
                          <span class="badge bg-label-secondary">Secondary</span>
                        </li>
                      </ul>
                    </div>
                    <div class="col-12 col-md-6">
                      <h6 class="mb-3">Legal information</h6>
                      <dl class="row mb-0 gy-2">
                        <dt class="col-5 text-muted fw-normal">Legal status</dt>
                        <dd class="col-7 mb-0 text-end fw-semibold">Registered</dd>
                        <dt class="col-5 text-muted fw-normal">Commercial register</dt>
                        <dd class="col-7 mb-0 text-end fw-semibold">CR-123456-2020</dd>
                        <dt class="col-5 text-muted fw-normal">Tax card</dt>
                        <dd class="col-7 mb-0 text-end fw-semibold">TC-7891011-EG</dd>
                      </dl>
                    </div>
                  </div>
                  <hr class="my-4" />
                  <div>
                    <h6 class="mb-3">Pitch deck and video</h6>
                    <div class="row g-3">
                      <div class="col-12 col-md-6">
                        <a href="#"
                          class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-between">
                          <span>Pitch deck file</span>
                          <i class="ti ti-chevron-right"></i>
                        </a>
                      </div>
                      <div class="col-12 col-md-6">
                        <a href="#"
                          class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-between">
                          <span>Pitch video</span>
                          <i class="ti ti-play-circle"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
      
            </div>
      
            <div class="tab-pane fade" id="tab-team" role="tabpanel" aria-labelledby="tab-team-trigger">
              <div id="team" class="card shadow-none mb-4">
                <div class="card-header p-2 d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="mb-0">Team</h6>
                    <small class="text-muted">Leader, founders, employees</small>
                  </div>
                  <span class="text-muted small">Team size: 8</span>
                </div>
                <div class="card-body p-2">
                  <div class="row g-4">
                    <div class="col-12 col-lg-4">
                      <h6 class="mb-3">Startup leader</h6>
                      <dl class="row mb-0 gy-3">
                        <dt class="col-5 text-muted fw-normal">ID</dt>
                        <dd class="col-7 text-end fw-semibold">LD-001</dd>
                        <dt class="col-5 text-muted fw-normal">Full name</dt>
                        <dd class="col-7 text-end fw-semibold">Omar Hassan</dd>
                        <dt class="col-5 text-muted fw-normal">Country</dt>
                        <dd class="col-7 text-end fw-semibold">Egypt</dd>
                        <dt class="col-5 text-muted fw-normal">Email</dt>
                        <dd class="col-7 text-end"><a href="mailto:omar@example.com">omar@example.com</a></dd>
                        <dt class="col-5 text-muted fw-normal">Phone</dt>
                        <dd class="col-7 text-end fw-semibold">+20 100 000 0000</dd>
                      </dl>
                    </div>
                    <div class="col-12 col-lg-8">
                      <h6 class="mb-3">Founders</h6>
                      <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                          <thead class="table-light text-muted">
                            <tr>
                              <th>Name</th>
                              <th>Email</th>
                              <th class="text-end">LinkedIn</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>Omar Hassan</td>
                              <td>omar@example.com</td>
                              <td class="text-end"><a href="#" class="fw-medium">View profile</a></td>
                            </tr>
                            <tr>
                              <td>Sara Ali</td>
                              <td>sara@example.com</td>
                              <td class="text-end"><a href="#" class="fw-medium">View profile</a></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
      
                  <div class="pt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <h6 class="mb-0">Employees</h6>
                      <small class="text-muted">Count 8</small>
                    </div>
                    <div class="table-responsive">
                      <table class="table table-sm mb-0 text-nowrap">
                        <thead class="table-light text-muted">
                          <tr>
                            <th>Full name</th>
                            <th>Job title</th>
                            <th>Gender</th>
                            <th>Employment type</th>
                            <th>Start date</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>Ahmed Mohamed</td>
                            <td>Backend engineer</td>
                            <td>Male</td>
                            <td>Full time</td>
                            <td>2022-03-01</td>
                          </tr>
                          <tr>
                            <td>Mona Adel</td>
                            <td>Product manager</td>
                            <td>Female</td>
                            <td>Full time</td>
                            <td>2021-10-15</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
      
            </div>
      
            <div class="tab-pane fade" id="tab-ownership" role="tabpanel" aria-labelledby="tab-ownership-trigger">
              <div id="ownership" class="card shadow-none mb-4">
                <div class="card-header p-2 d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="mb-0">Ownership and investors</h6>
                    <small class="text-muted">Shareholders and funding</small>
                  </div>
                </div>
                <div class="card-body p-2">
                  <div class="row g-4">
                    <div class="col-12">
                      <h6 class="mb-3">Shareholders</h6>
                      <div class="table-responsive">
                        <table class="table table-sm mb-0 text-nowrap">
                          <thead class="table-light text-muted">
                            <tr>
                              <th>Name</th>
                              <th>Role</th>
                              <th class="text-end">Ownership %</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>Omar Hassan</td>
                              <td>Founder</td>
                              <td class="text-end">40%</td>
                            </tr>
                            <tr>
                              <td>Sara Ali</td>
                              <td>Co-founder</td>
                              <td class="text-end">30%</td>
                            </tr>
                            <tr>
                              <td>ABC Ventures</td>
                              <td>Investor</td>
                              <td class="text-end">20%</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <div class="col-12">
                      <h6 class="mb-3">Investors</h6>
                      <div class="table-responsive">
                        <table class="table table-sm mb-0 text-nowrap">
                          <thead class="table-light text-muted">
                            <tr>
                              <th>Name</th>
                              <th class="text-end">Type</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>ABC Ventures</td>
                              <td class="text-end">VC</td>
                            </tr>
                            <tr>
                              <td>XYZ Angels Syndicate</td>
                              <td class="text-end">Angel group</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
      
            </div>
      
            <div class="tab-pane fade" id="tab-screening" role="tabpanel" aria-labelledby="tab-screening-trigger">
              <div id="program-screening" class="card shadow-none mb-4">
                <div class="card-header p-2">
                  <h6 class="mb-0">Screening</h6>
                  <small class="text-muted">Initial and final evaluations</small>
                </div>
                <div class="card-body p-2">
                  <div class="row g-4">
                    <div class="col-12">
                      <div class="border rounded-3 p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                          <div>
                            <h6 class="mb-0">Initial screening</h6>
                            <small class="text-muted">Screening committee</small>
                          </div>
                          <span class="badge bg-label-info">Step STP-001</span>
                        </div>
                        <div class="row g-3">
                          <div class="col">
                            <small class="text-muted d-block">Overall score</small>
                            <h5 class="mb-0">4.3 / 5</h5>
                          </div>
                          <div class="col">
                            <small class="text-muted d-block">Date</small>
                            <p class="mb-0 fw-semibold">Jan 2025</p>
                          </div>
                        </div>
                        <h6 class="mt-4 mb-2">Criteria</h6>
                        <div class="table-responsive">
                          <table class="table table-sm mb-0">
                            <thead class="table-light text-muted">
                              <tr>
                                <th>Criterion</th>
                                <th>Weight</th>
                                <th>Score</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>Problem &amp; solution</td>
                                <td>30%</td>
                                <td>4.5</td>
                              </tr>
                              <tr>
                                <td>Market size</td>
                                <td>25%</td>
                                <td>4.2</td>
                              </tr>
                              <tr>
                                <td>Team</td>
                                <td>20%</td>
                                <td>4.6</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <h6 class="mt-4 mb-2">Judges evaluations</h6>
                        <div class="table-responsive">
                          <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light text-muted">
                              <tr>
                                <th>Judge</th>
                                <th>ID</th>
                                <th>Score</th>
                                <th>Comments</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>Judge One</td>
                                <td>J-001</td>
                                <td>4.2</td>
                                <td>Clear problem and strong traction in Egyptian market.</td>
                              </tr>
                              <tr>
                                <td>Judge Two</td>
                                <td>J-002</td>
                                <td>4.4</td>
                                <td>Team has relevant experience and solid execution plan.</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="border rounded-3 p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                          <div>
                            <h6 class="mb-0">Final screening</h6>
                            <small class="text-muted">Investment committee</small>
                          </div>
                          <span class="badge bg-label-info">Step STP-010</span>
                        </div>
                        <div class="row g-3">
                          <div class="col">
                            <small class="text-muted d-block">Overall score</small>
                            <h5 class="mb-0">4.6 / 5</h5>
                          </div>
                          <div class="col">
                            <small class="text-muted d-block">Date</small>
                            <p class="mb-0 fw-semibold">Mar 2025</p>
                          </div>
                        </div>
                        <h6 class="mt-4 mb-2">Criteria</h6>
                        <div class="table-responsive">
                          <table class="table table-sm mb-0">
                            <thead class="table-light text-muted">
                              <tr>
                                <th>Criterion</th>
                                <th>Weight</th>
                                <th>Score</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>Revenue &amp; traction</td>
                                <td>30%</td>
                                <td>4.7</td>
                              </tr>
                              <tr>
                                <td>Scalability</td>
                                <td>25%</td>
                                <td>4.5</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <h6 class="mt-4 mb-2">Judges evaluations</h6>
                        <div class="table-responsive">
                          <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light text-muted">
                              <tr>
                                <th>Judge</th>
                                <th>ID</th>
                                <th>Score</th>
                                <th>Comments</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <td>Judge Three</td>
                                <td>J-010</td>
                                <td>4.6</td>
                                <td>Ready for next stage of funding, strong growth indicators.</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
      
            </div>
      
            <div class="tab-pane fade" id="tab-training" role="tabpanel" aria-labelledby="tab-training-trigger">
              <div id="program-training" class="card shadow-none mb-4">
                <div class="card-header p-2">
                  <h6 class="mb-0">Training</h6>
                  <small class="text-muted">Sessions attended during the program</small>
                </div>
                <div class="card-body p-2">
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 text-nowrap">
                      <thead class="table-light text-muted">
                        <tr>
                          <th>Session ID</th>
                          <th>Title</th>
                          <th>Course ID</th>
                          <th>Course name</th>
                          <th>Date</th>
                          <th>Training step</th>
                          <th>Duration (h)</th>
                          <th>Program step</th>
                          <th>Step ID</th>
                          <th>Attendance</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>TR-101</td>
                          <td>Lean startup fundamentals</td>
                          <td>CR-001</td>
                          <td>Lean methodology</td>
                          <td>2025-02-01</td>
                          <td>Bootcamp</td>
                          <td>3</td>
                          <td>Training week 1</td>
                          <td>STP-TR-01</td>
                          <td>Founder + 2 team members</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
      
            </div>
      
            <div class="tab-pane fade" id="tab-mentorship" role="tabpanel" aria-labelledby="tab-mentorship-trigger">
              <div id="program-mentorship" class="card shadow-none mb-4">
                <div class="card-header p-2">
                  <h6 class="mb-0">Mentorship</h6>
                  <small class="text-muted">Mentor interactions and feedback</small>
                </div>
                <div class="card-body p-2">
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 text-nowrap align-middle">
                      <thead class="table-light text-muted">
                        <tr>
                          <th>Mentor name</th>
                          <th>Mentor ID</th>
                          <th>Step name</th>
                          <th>Step ID</th>
                          <th>Date</th>
                          <th>Duration (h)</th>
                          <th>Founder attended</th>
                          <th>Rating</th>
                          <th>Comments</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>Mentor A</td>
                          <td>M-001</td>
                          <td>Growth mentoring</td>
                          <td>STP-M-03</td>
                          <td>2025-03-10</td>
                          <td>1.5</td>
                          <td>Omar Hassan</td>
                          <td>
                            <span class="badge bg-label-success">5</span>
                          </td>
                          <td>Helpful session on pricing strategy and B2B sales pipeline.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
      
            </div>
      
            <div class="tab-pane fade" id="tab-milestones" role="tabpanel" aria-labelledby="tab-milestones-trigger">
              <div id="program-milestones" class="card shadow-none mb-4">
                <div class="card-header p-2">
                  <h6 class="mb-0">Milestones and deliverables</h6>
                  <small class="text-muted">Planned progress versus completion</small>
                </div>
                <div class="card-body p-2">
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                      <thead class="table-light text-muted">
                        <tr>
                          <th>Milestone title</th>
                          <th>Description</th>
                          <th>Due date</th>
                          <th>Completion</th>
                          <th>Linked deliverables</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>MVP launch</td>
                          <td>Launch first production version with payment gateway integration.</td>
                          <td>2025-04-15</td>
                          <td class="w-25">
                            <div class="d-flex align-items-center gap-2">
                              <span>80%</span>
                              <div class="progress flex-grow-1" style="height: 6px">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 80%"></div>
                              </div>
                            </div>
                          </td>
                          <td>
                            <ul class="list-unstyled mb-0">
                              <li><a href="#" class="fw-medium">Technical architecture PDF</a></li>
                              <li><a href="#" class="fw-medium">MVP demo video</a></li>
                            </ul>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
      
            </div>
      
            <div class="tab-pane fade" id="tab-tasks" role="tabpanel" aria-labelledby="tab-tasks-trigger">
              <div id="program-tasks" class="card shadow-none mb-4">
                <div class="card-header p-2">
                  <h6 class="mb-0">Program tasks</h6>
                  <small class="text-muted">Tasks assigned to the startup</small>
                </div>
                <div class="card-body p-2">
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 text-nowrap align-middle">
                      <thead class="table-light text-muted">
                        <tr>
                          <th>Task title</th>
                          <th>Task ID</th>
                          <th>Session ID</th>
                          <th>Session title</th>
                          <th>Description</th>
                          <th>Due date</th>
                          <th>Completion date</th>
                          <th>Assigned mentor</th>
                          <th>Created by</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>Customer interviews</td>
                          <td>TSK-001</td>
                          <td>TR-101</td>
                          <td>Lean startup fundamentals</td>
                          <td>Conduct ten customer interviews and summarize findings.</td>
                          <td>2025-02-10</td>
                          <td>2025-02-08</td>
                          <td>Mentor A</td>
                          <td>Program manager</td>
                          <td>
                            <span class="badge bg-label-success">Completed</span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
      
            </div>
      
            <div class="tab-pane fade" id="tab-quizzes" role="tabpanel" aria-labelledby="tab-quizzes-trigger">
              <div id="program-quizzes" class="card shadow-none mb-4">
                <div class="card-header p-2">
                  <h6 class="mb-0">Quizzes</h6>
                  <small class="text-muted">Knowledge checks completed by the startup</small>
                </div>
                <div class="card-body p-2">
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 text-nowrap align-middle">
                      <thead class="table-light text-muted">
                        <tr>
                          <th>Quiz ID</th>
                          <th>Quiz title</th>
                          <th>Date completed</th>
                          <th>Founder</th>
                          <th>Score</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>QZ-001</td>
                          <td>Lean startup quiz</td>
                          <td>2025-02-05</td>
                          <td>Omar Hassan</td>
                          <td>90</td>
                          <td><span class="badge bg-label-success">Passed</span></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
      
            </div>
      
            <div class="tab-pane fade" id="tab-surveys" role="tabpanel" aria-labelledby="tab-surveys-trigger">
              <div id="program-surveys" class="card shadow-none mb-4">
                <div class="card-header p-2">
                  <h6 class="mb-0">Surveys</h6>
                  <small class="text-muted">Feedback during the program</small>
                </div>
                <div class="card-body p-2">
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 text-nowrap align-middle">
                      <thead class="table-light text-muted">
                        <tr>
                          <th>Survey ID</th>
                          <th>Survey title</th>
                          <th>Program step</th>
                          <th>Date completed</th>
                          <th>Answers</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>SV-001</td>
                          <td>Mid program satisfaction</td>
                          <td>Mid review step</td>
                          <td>2025-03-01</td>
                          <td><a href="#" class="fw-medium">View survey answers</a></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
      
            </div>
      
            <div class="tab-pane fade" id="tab-services" role="tabpanel" aria-labelledby="tab-services-trigger">
              <div id="program-services" class="card shadow-none mb-4">
                <div class="card-header p-2">
                  <h6 class="mb-0">Services provided</h6>
                  <small class="text-muted">Perks and services delivered</small>
                </div>
                <div class="card-body p-2">
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                      <thead class="table-light text-muted">
                        <tr>
                          <th>Service name</th>
                          <th>Service ID</th>
                          <th>Description</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>Cloud credits</td>
                          <td>SRV-001</td>
                          <td>Cloud infrastructure credits for twelve months to support product development.</td>
                        </tr>
                        <tr>
                          <td>Legal advisory session</td>
                          <td>SRV-002</td>
                          <td>One-on-one session with legal partner for company setup and shareholder agreement review.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
      
            </div>
      
            <div class="tab-pane fade" id="tab-forms" role="tabpanel" aria-labelledby="tab-forms-trigger">
              <div id="program-forms" class="card shadow-none mb-4">
                <div class="card-header p-2 d-flex align-items-center justify-content-between">
                  <div>
                    <h6 class="mb-0">Submitted forms</h6>
                    <small class="text-muted">Custom forms filled during the program</small>
                  </div>
                </div>
                <div class="card-body p-2">
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 text-nowrap align-middle">
                      <thead class="table-light text-muted">
                        <tr>
                          <th>Form title</th>
                          <th>Form ID</th>
                          <th>Custom form step</th>
                          <th>Step ID</th>
                          <th>Submission date</th>
                          <th>Details</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>Startup profiling form</td>
                          <td>FM-001</td>
                          <td>Application step form</td>
                          <td>STP-FM-01</td>
                          <td>2025-01-05 14:22</td>
                          <td>
                            <button type="button" class="btn btn-sm btn-link px-0" data-toggle="form-details"
                              data-target="#form-details-1">
                              View inputs
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
      
                  <div id="form-details-1" class="border rounded mt-3 p-3 d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h6 class="mb-0">Form inputs for Startup profiling form</h6>
                      <button type="button" class="btn btn-sm btn-link px-0" data-toggle="form-details"
                        data-target="#form-details-1">
                        Hide
                      </button>
                    </div>
                    <div class="table-responsive">
                      <table class="table table-sm mb-0 text-nowrap align-middle">
                        <thead class="table-light text-muted">
                          <tr>
                            <th>Field name</th>
                            <th>Field type</th>
                            <th>Submitted value</th>
                            <th>Attachment</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>Company description</td>
                            <td>Text</td>
                            <td>AI powered payment orchestration platform for SMEs.</td>
                            <td>&mdash;</td>
                          </tr>
                          <tr>
                            <td>Pitch deck file</td>
                            <td>File</td>
                            <td>Uploaded</td>
                            <td><a href="#" class="fw-medium">Download attachment</a></td>
                          </tr>
                          <tr>
                            <td>Industry</td>
                            <td>Selection</td>
                            <td>Financial services</td>
                            <td>&mdash;</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- / Content -->
  </div>
</div>
@stop

@section('pagescripts')
<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Initialize DataTable
    DataTableInit(".table");
  });

  function DataTableInit(selector, options = {}) {
    // Detect RTL direction
    const isRTL = document.documentElement.dir === "rtl";

    // Default global options
    const defaultOptions = {
        responsive: true,
        processing: true,
        serverSide: false,
        pageLength: 10,
        language: {
            url: isRTL
                ? "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
                : "https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json",
        },
        // RTL table layout fix
        initComplete: function () {
            if (isRTL) {
                $(selector).addClass("datatable-rtl");
            }
        },
    };

    // Merge user options
    const finalOptions = { ...defaultOptions, ...options };

    // Initialize
    return $(selector).DataTable(finalOptions);
}
</script>
@stop
