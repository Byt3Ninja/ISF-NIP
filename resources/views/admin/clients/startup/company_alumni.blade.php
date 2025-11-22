@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
  <div class="container-xxl grow container-p-y" id="contentToExport">
    <div class="row g-3 align-items-center mb-4">
      <div class="col-12 col-md-6">
        <h4 class="mb-1">
          Startup Alumni Report
        </h4>
        <p class="text-muted mb-0 small">
          Program based alumni insights for a single startup
        </p>
      </div>
      <div id="header-actions" class="col-12 col-md-6 d-flex justify-content-md-end gap-2 flex-wrap">
        <span class="badge bg-label-success pe-4 rounded-pill d-inline-flex align-items-center">
          <i class="ti ti-point-filled me-1"></i>
          <span>Alumni</span>
        </span>
        <button id="export-pdf-button" class="btn btn-primary btn-sm">
          <i class="ti ti-file-type-pdf me-1"></i>Export PDF
        </button>
        <a id="export-csv" class="btn btn-outline-secondary btn-sm">
          <i class="ti ti-file-type-csv me-1"></i>Export CSV
        </a>
      </div>
    </div>

    <!-- Startup Profile Card -->
    <div class="row mb-6">
      <div class="col-12">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 pb-5 border-bottom">
              <div class="grow">
                <h5 class="mb-2 fw-bold text-dark d-flex align-items-center gap-3">
                  <div class="avatar avatar-lg">
                    <div class="avatar-initial rounded-circle bg-label-primary">
                      <i class="ti ti-building-skyscraper ti-xl"></i>
                    </div>
                  </div>
                  <span>PayLinkx</span>
                </h5>
                <p class="text-muted small mb-0">Modern fintech platform for seamless payment solutions</p>
              </div>
              <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-label-primary rounded-pill">
                  <i class="ti ti-building me-1"></i>Growthlabs
                </span>
                <span class="badge bg-label-secondary rounded-pill">
                  <i class="ti ti-cloud me-1"></i>SaaS
                </span>
                <span class="badge bg-label-info rounded-pill">
                  <i class="ti ti-map-pin me-1"></i>Egypt · Cairo
                </span>
              </div>
            </div>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-4 py-5 border-bottom">
              <div class="text-center">
                <small
                  class="text-muted text-uppercase fw-semibold d-block mb-2 d-flex align-items-center justify-content-center gap-1">
                  <i class="ti ti-calendar-event text-info"></i>Est. Date
                </small>
                <h6 class="fw-bold text-dark mb-0">2019-05-01</h6>
              </div>
              <div class="text-center">
                <small
                  class="text-muted text-uppercase fw-semibold d-block mb-2 d-flex align-items-center justify-content-center gap-1">
                  <i class="ti ti-briefcase text-warning"></i>Business Type
                </small>
                <h6 class="fw-bold text-dark mb-0">B2B</h6>
              </div>
              <div class="text-center">
                <small
                  class="text-muted text-uppercase fw-semibold d-block mb-2 d-flex align-items-center justify-content-center gap-1">
                  <i class="ti ti-device-gamepad-3 text-success"></i>Industry
                </small>
                <h6 class="fw-bold text-dark mb-0">Financial Services</h6>
              </div>
              <div class="text-center">
                <small
                  class="text-muted text-uppercase fw-semibold d-block mb-2 d-flex align-items-center justify-content-center gap-1">
                  <i class="ti ti-packages text-danger"></i>Sector
                </small>
                <h6 class="fw-bold text-dark mb-0">Payments</h6>
              </div>
              <div class="text-center">
                <small
                  class="text-muted text-uppercase fw-semibold d-block mb-2 d-flex align-items-center justify-content-center gap-1">
                  <i class="ti ti-users text-primary"></i>Team Size
                </small>
                <h6 class="fw-bold text-dark mb-0">25+</h6>
              </div>
            </div>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-4 py-5">
              <div class="text-center">
                <small
                  class="text-muted text-uppercase fw-semibold d-block mb-2 d-flex align-items-center justify-content-center gap-1">
                  <i class="ti ti-components text-secondary"></i>Program Name
                </small>
                <h6 class="fw-bold text-dark mb-0">Fintech Accelerator 2025</h6>
              </div>
              <div class="text-center">
                <small
                  class="text-muted text-uppercase fw-semibold d-block mb-2 d-flex align-items-center justify-content-center gap-1">
                  <i class="ti ti-color-swatch text-danger"></i>Program Cohort
                </small>
                <h6 class="fw-bold text-dark mb-0">Cohort 3</h6>
              </div>
              <div class="text-center">
                <small
                  class="text-muted text-uppercase fw-semibold d-block mb-2 d-flex align-items-center justify-content-center gap-1">
                  <i class="ti ti-calendar-stats text-muted"></i>Program Start
                </small>
                <h6 class="fw-bold text-dark mb-0">10 Jan 2025</h6>
              </div>
              <div class="text-center">
                <small
                  class="text-muted text-uppercase fw-semibold d-block mb-2 d-flex align-items-center justify-content-center gap-1">
                  <i class="ti ti-calendar-check text-success"></i>Program End
                </small>
                <h6 class="fw-bold text-dark mb-0">15 Jun 2025</h6>
              </div>
            </div>
            <div class="pt-5 d-flex flex-wrap gap-4 border-top">
              <a href="#"
                class="d-inline-flex align-items-center fw-medium text-decoration-none link-primary hover-animate">
                <i class="ti ti-world me-2"></i>Website <i class="ti ti-external-link ms-1 small"></i>
              </a>
              <a href="#"
                class="d-inline-flex align-items-center fw-medium text-decoration-none link-primary hover-animate">
                <i class="ti ti-brand-linkedin me-2"></i>LinkedIn <i class="ti ti-external-link ms-1 small"></i>
              </a>
              <a href="#"
                class="d-inline-flex align-items-center fw-medium text-decoration-none link-primary hover-animate">
                <i class="ti ti-brand-twitter me-2"></i>Twitter <i class="ti ti-external-link ms-1 small"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Outcome Summary Section -->
    <div class="row mb-6">
      <div class="col-12">
        <div class="row gy-6">
          <!-- Screening Score -->
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card card-border-shadow-primary h-100 cursor-pointer">
              <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                  <div class="avatar avatar-md me-4">
                    <div class="avatar-initial rounded bg-label-primary">
                      <i class="ti ti-search ti-md"></i>
                    </div>
                  </div>
                  <h5 class="mb-0">4.3 / 5</h5>
                </div>
                <small class="text-center">Screening Score</small>
              </div>
            </div>
          </div>

          <!-- Final Screening Score -->
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card card-border-shadow-success h-100 cursor-pointer">
              <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                  <div class="avatar avatar-md me-4">
                    <div class="avatar-initial rounded bg-label-success">
                      <i class="ti ti-award ti-md"></i>
                    </div>
                  </div>
                  <h5 class="mb-0">4.6 / 5</h5>
                </div>
                <small class="text-center">Final Screening Score</small>
              </div>
            </div>
          </div>

          <!-- Training Hours -->
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card card-border-shadow-warning h-100 cursor-pointer">
              <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                  <div class="avatar avatar-md me-4">
                    <div class="avatar-initial rounded bg-label-warning">
                      <i class="ti ti-book ti-md"></i>
                    </div>
                  </div>
                  <h5 class="mb-0">32h 15m</h5>
                </div>
                <small class="text-center">Training Hours</small>
              </div>
            </div>
          </div>

          <!-- Mentorship Hours -->
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card card-border-shadow-info h-100 cursor-pointer">
              <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                  <div class="avatar avatar-md me-4">
                    <div class="avatar-initial rounded bg-label-info">
                      <i class="ti ti-school ti-md"></i>
                    </div>
                  </div>
                  <h5 class="mb-0">24h 30m</h5>
                </div>
                <small class="text-center">Mentorship Hours</small>
              </div>
            </div>
          </div>

          <!-- Attendance Rate -->
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card card-border-shadow-success h-100 cursor-pointer">
              <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                  <div class="avatar avatar-md me-4">
                    <div class="avatar-initial rounded bg-label-success">
                      <i class="ti ti-user-check ti-md"></i>
                    </div>
                  </div>
                  <h5 class="mb-0">94%</h5>
                </div>
                <small class="text-center">Attendance Rate</small>
              </div>
            </div>
          </div>

          <!-- Milestones Completed -->
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card card-border-shadow-primary h-100 cursor-pointer">
              <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                  <div class="avatar avatar-md me-4">
                    <div class="avatar-initial rounded bg-label-primary">
                      <i class="ti ti-flag-check ti-md"></i>
                    </div>
                  </div>
                  <h5 class="mb-0">7 / 8</h5>
                </div>
                <small class="text-center">Milestones Completed</small>
              </div>
            </div>
          </div>

          <!-- Quizzes Completed -->
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card card-border-shadow-warning h-100 cursor-pointer">
              <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                  <div class="avatar avatar-md me-4">
                    <div class="avatar-initial rounded bg-label-warning">
                      <i class="ti ti-help ti-md"></i>
                    </div>
                  </div>
                  <h5 class="mb-0">12 / 12</h5>
                </div>
                <small class="text-center">Quizzes Completed</small>
              </div>
            </div>
          </div>

          <!-- Average Quiz Score -->
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card card-border-shadow-success h-100 cursor-pointer">
              <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                  <div class="avatar avatar-md me-4">
                    <div class="avatar-initial rounded bg-label-success">
                      <i class="ti ti-chart-bar ti-md"></i>
                    </div>
                  </div>
                  <h5 class="mb-0">87%</h5>
                </div>
                <small class="text-center">Avg Quiz Score</small>
              </div>
            </div>
          </div>

          <!-- In-Kind Services -->
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card card-border-shadow-info h-100 cursor-pointer">
              <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                  <div class="avatar avatar-md me-4">
                    <div class="avatar-initial rounded bg-label-info">
                      <i class="ti ti-gift ti-md"></i>
                    </div>
                  </div>
                  <h5 class="mb-0">8</h5>
                </div>
                <small class="text-center">In-Kind Services</small>
              </div>
            </div>
          </div>

          <!-- Surveys Completed -->
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card card-border-shadow-primary h-100 cursor-pointer">
              <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                  <div class="avatar avatar-md me-4">
                    <div class="avatar-initial rounded bg-label-primary">
                      <i class="ti ti-mood-smile ti-xs"></i>
                    </div>
                  </div>
                  <h5 class="mb-0">5 / 5</h5>
                </div>
                <small class="text-center">Surveys Completed</small>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card card-border-shadow-primary h-100 cursor-pointer">
              <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-2">
                  <div class="avatar avatar-md me-4">
                    <div class="avatar-initial rounded bg-label-primary">
                      <i class="ti ti-checklist ti-xs"></i>
                    </div>
                  </div>
                  <h5 class="mb-0">24 / 26</h5>
                </div>
                <small class="text-center">Tasks Completed</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- / Outcome Summary Section -->

    <div id="navigation-pills" class="card mb-4 shadow-sm">
      <div class="card-body px-4 pt-4 pb-0">
        <div class="nav-align-top">
          <ul class="nav nav-pills flex-nowrap gap-2 pb-4" style="max-width: 100%; overflow-x: auto;" role="tablist">
            <li class="nav-item" role="presentation">
              <a type="button" class="nav-link active" id="tab-basic-trigger" href="#tab-basic" role="tab"
                aria-controls="tab-basic" aria-selected="true">
                <i class="ti ti-info-square me-1"></i>Startup info
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a type="button" class="nav-link" id="tab-team-trigger" href="#tab-team" role="tab"
                aria-controls="tab-team" aria-selected="false">
                <i class="ti ti-users me-1"></i>Team
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a type="button" class="nav-link" id="tab-ownership-trigger" href="#tab-ownership" role="tab"
                aria-controls="tab-ownership" aria-selected="false">
                <i class="ti ti-briefcase me-1"></i>Ownership &amp; investors
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a type="button" class="nav-link" id="tab-screening-trigger" href="#tab-screening" role="tab"
                aria-controls="tab-screening" aria-selected="false">
                <i class="ti ti-search me-1"></i>Screening
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a type="button" class="nav-link" id="tab-training-trigger" href="#tab-training" role="tab"
                aria-controls="tab-book" aria-selected="false">
                <i class="ti ti-school me-1"></i>Training
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a type="button" class="nav-link" id="tab-mentorship-trigger" href="#tab-mentorship" role="tab"
                aria-controls="tab-mentorship" aria-selected="false">
                <i class="ti ti-school me-1"></i>Mentorship
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a type="button" class="nav-link" id="tab-milestones-trigger" href="#tab-milestones" role="tab"
                aria-controls="tab-milestones" aria-selected="false">
                <i class="ti ti-flag-check me-1"></i>Milestones
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a type="button" class="nav-link" id="tab-tasks-trigger" href="#tab-tasks" role="tab"
                aria-controls="tab-tasks" aria-selected="false">
                <i class="ti ti-checklist me-1"></i>Tasks
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a type="button" class="nav-link" id="tab-quizzes-trigger" href="#tab-quizzes" role="tab"
                aria-controls="tab-quizzes" aria-selected="false">
                <i class="ti ti-help me-1"></i>Quizzes
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a type="button" class="nav-link" id="tab-surveys-trigger" href="#tab-surveys" role="tab"
                aria-controls="tab-surveys" aria-selected="false">
                <i class="ti ti-mood-smile me-1"></i>Surveys
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a type="button" class="nav-link" id="tab-services-trigger" href="#tab-services" role="tab"
                aria-controls="tab-services" aria-selected="false">
                <i class="ti ti-gift me-1"></i>Services
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a type="button" class="nav-link" id="tab-forms-trigger" href="#tab-forms" role="tab"
                aria-controls="tab-forms" aria-selected="false">
                <i class="ti ti-file-text me-1"></i>Forms
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div class="tab-content p-0 shadow-none" id="alumniTabContent" style="max-width: 100%; overflow-x: hidden;">
      <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-trigger">
        <div id="basic-info" class="card mb-6 shadow-sm">
          <div class="card-header d-flex justify-content-between align-it">
            <div>
              <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                <i class="ti ti-file-info text-primary"></i>Basic startup information
              </h5>
              <small class="text-muted">Static data per startup record</small>
            </div>
            <span class="badge bg-label-success rounded-pill" style="height: fit-content;">
              <i class="ti ti-check me-1"></i>Alumni
            </span>
          </div>
          <div class="card-body">
            <div class="row g-4 mb-4">
              <div class="col-12 col-md-4">
                <div class="p-3 rounded bg-light-subtle border">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-1">
                    <i class="ti ti-briefcase me-1"></i>Startup name
                  </small>
                  <p class="mb-0 fw-bold text-dark">PayLinkx</p>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-3 rounded bg-light-subtle border">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-1">
                    <i class="ti ti-calendar me-1"></i>Established date
                  </small>
                  <p class="mb-0 fw-bold text-dark">2019-05-01</p>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-3 rounded bg-light-subtle border">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-1">
                    <i class="ti ti-map-pin me-1"></i>Location
                  </small>
                  <p class="mb-0 fw-bold text-dark">Cairo, Egypt</p>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-3 rounded bg-light-subtle border">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-1">
                    <i class="ti ti-building me-1"></i>Business type
                  </small>
                  <p class="mb-0 fw-bold text-dark">B2B</p>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-3 rounded bg-light-subtle border">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-1">
                    <i class="ti ti-bulb me-1"></i>Technology
                  </small>
                  <p class="mb-0 fw-bold text-dark">AI, APIs, Cloud</p>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-3 rounded bg-light-subtle border">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-1">
                    <i class="ti ti-world me-1"></i>Website
                  </small>
                  <p class="mb-0">
                    <a href="#" class="fw-bold text-decoration-none link-primary">paylinkx.com</a>
                  </p>
                </div>
              </div>
              <div class="col-12 col-md-8">
                <div class="p-3 rounded bg-light-subtle border">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-2">
                    <i class="ti ti-share me-1"></i>Social media
                  </small>
                  <div class="d-flex flex-wrap gap-3 mb-0">
                    <a href="#" class="badge bg-label-primary text-decoration-none">
                      <i class="ti ti-brand-linkedin me-1"></i>LinkedIn
                    </a>
                    <a href="#" class="badge bg-label-secondary text-decoration-none">
                      <i class="ti ti-brand-facebook me-1"></i>Facebook
                    </a>
                    <a href="#" class="badge bg-label-info text-decoration-none">
                      <i class="ti ti-brand-twitter me-1"></i>Twitter
                    </a>
                  </div>
                </div>
              </div>
            </div>
            <hr class="my-4" />
            <div class="row g-4">
              <div class="col-12 col-md-6">
                <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                  <i class="ti ti-target text-success"></i>Sustainability goals
                </h6>
                <ul class="list-group list-group-flush rounded border">
                  <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <span class="fw-medium">SDG 8 — Decent work &amp; economic growth</span>
                    <span class="badge bg-label-primary">Primary</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <span class="fw-medium">SDG 9 — Industry innovation &amp; infrastructure</span>
                    <span class="badge bg-label-secondary">Secondary</span>
                  </li>
                </ul>
              </div>
              <div class="col-12 col-md-6">
                <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                  <i class="ti ti-shield-check text-warning"></i>Legal information
                </h6>
                <div class="border rounded p-3">
                  <dl class="row mb-0 gy-2">
                    <dt class="col-5 text-muted fw-normal small">Legal status</dt>
                    <dd class="col-7 mb-0 text-end fw-bold text-dark">Registered</dd>
                    <dt class="col-5 text-muted fw-normal small">Commercial register</dt>
                    <dd class="col-7 mb-0 text-end fw-bold text-dark">CR-123456-2020</dd>
                    <dt class="col-5 text-muted fw-normal small">Tax card</dt>
                    <dd class="col-7 mb-0 text-end fw-bold text-dark">TC-7891011-EG</dd>
                  </dl>
                </div>
              </div>
            </div>
            <hr class="my-4" />
            <div>
              <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                <i class="ti ti-presentation text-info"></i>Pitch deck and video
              </h6>
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <a href="#" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between">
                    <span class="d-flex align-items-center gap-2">
                      <i class="ti ti-slideshow"></i>Pitch deck file
                    </span>
                    <i class="ti ti-download"></i>
                  </a>
                </div>
                <div class="col-12 col-md-6">
                  <a href="#" class="btn btn-outline-info w-100 d-flex align-items-center justify-content-between">
                    <span class="d-flex align-items-center gap-2">
                      <i class="ti ti-video"></i>Pitch video
                    </span>
                    <i class="ti ti-play-circle"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="tab-pane fade show active" id="tab-team" role="tabpanel" aria-labelledby="tab-team-trigger">
        <div id="team" class="card mb-6 shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                <i class="ti ti-users-group text-primary"></i>Team
              </h5>
              <small class="text-muted">Leader, founders, employees</small>
            </div>
            <span class="badge bg-label-info">
              <i class="ti ti-users me-1"></i>Team size: 8
            </span>
          </div>
          <div class="card-body">
            <div class="row g-6">
              <div class="col-12 col-lg-4">
                <div class="card card-body border h-100 shadow-sm"
                  style="background: linear-gradient(135deg, rgba(88, 86, 214, 0.08) 0%, transparent 100%);">
                  <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-crown text-warning"></i>Startup leader
                  </h6>
                  <dl class="row mb-0 gy-3">
                    <dt class="col-5 text-secondary fw-normal small">ID</dt>
                    <dd class="col-7 text-end fw-bold text-dark">LD-001</dd>
                    <dt class="col-5 text-secondary fw-normal small">Full name</dt>
                    <dd class="col-7 text-end fw-bold text-dark">Omar Hassan</dd>
                    <dt class="col-5 text-secondary fw-normal small">Country</dt>
                    <dd class="col-7 text-end fw-bold text-dark">Egypt</dd>
                    <dt class="col-5 text-secondary fw-normal small">Email</dt>
                    <dd class="col-7 text-end"><a href="mailto:omar@example.com"
                        class="fw-medium text-primary">omar@example.com</a></dd>
                    <dt class="col-5 text-secondary fw-normal small">Phone</dt>
                    <dd class="col-7 text-end fw-bold text-dark">+20 100 000 0000</dd>
                  </dl>
                </div>
              </div>
              <div class="col-12 col-lg-8">
                <div class="card card-body border h-100 shadow-sm">
                  <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-users text-info"></i>Founders
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                      <thead class="table-light">
                        <tr>
                          <th class="fw-bold text-muted"><i class="ti ti-user me-1"></i>Name</th>
                          <th class="fw-bold text-muted"><i class="ti ti-mail me-1"></i>Email</th>
                          <th class="fw-bold text-muted text-end"><i class="ti ti-link me-1"></i>LinkedIn</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr class="border-top">
                          <td class="fw-medium">Omar Hassan</td>
                          <td>omar@example.com</td>
                          <td class="text-end"><a href="#" class="fw-medium text-primary">View profile</a></td>
                        </tr>
                        <tr>
                          <td class="fw-medium">Sara Ali</td>
                          <td>sara@example.com</td>
                          <td class="text-end"><a href="#" class="fw-medium text-primary">View profile</a></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <div class="card card-body border shadow-sm mt-6">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                  <i class="ti ti-briefcase text-success"></i>Employees
                </h5>
                <span class="badge bg-label-secondary">
                  <i class="ti ti-users me-1"></i>Count 8
                </span>
              </div>
              <div class="table-responsive">
                <table class="table table-sm mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="fw-bold text-muted"><i class="ti ti-user me-1"></i>Full name</th>
                      <th class="fw-bold text-muted"><i class="ti ti-briefcase me-1"></i>Job title</th>
                      <th class="fw-bold text-muted"><i class="ti ti-gender-male me-1"></i>Gender</th>
                      <th class="fw-bold text-muted"><i class="ti ti-clock me-1"></i>Employment type</th>
                      <th class="fw-bold text-muted"><i class="ti ti-calendar me-1"></i>Start date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="border-top">
                      <td class="fw-medium">Ahmed Mohamed</td>
                      <td>Backend engineer</td>
                      <td><span class="badge bg-label-info">Male</span></td>
                      <td><span class="badge bg-label-success">Full time</span></td>
                      <td>2022-03-01</td>
                    </tr>
                    <tr>
                      <td class="fw-medium">Mona Adel</td>
                      <td>Product manager</td>
                      <td><span class="badge bg-label-warning">Female</span></td>
                      <td><span class="badge bg-label-success">Full time</span></td>
                      <td>2021-10-15</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="tab-pane fade show active" id="tab-ownership" role="tabpanel" aria-labelledby="tab-ownership-trigger">
        <div id="ownership" class="card mb-6 shadow-sm">
          <div class="card-header">
            <div>
              <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                <i class="ti ti-chart-pie text-success"></i>Ownership and investors
              </h5>
              <small class="text-muted">Shareholders and funding information</small>
            </div>
          </div>
          <div class="card-body">
            <div class="row g-6">
              <div class="col-12 col-xl-6">
                <div class="card card-body border shadow-sm h-100">
                  <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-users text-primary"></i>Shareholders
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                      <thead class="table-light">
                        <tr>
                          <th class="fw-bold text-muted"><i class="ti ti-user me-1"></i>Name</th>
                          <th class="fw-bold text-muted"><i class="ti ti-briefcase me-1"></i>Role</th>
                          <th class="fw-bold text-muted text-end"><i class="ti ti-percent me-1"></i>Ownership</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr class="border-top">
                          <td class="fw-medium">Omar Hassan</td>
                          <td><span class="badge bg-label-primary">Founder</span></td>
                          <td class="text-end fw-bold text-primary">40%</td>
                        </tr>
                        <tr>
                          <td class="fw-medium">Sara Ali</td>
                          <td><span class="badge bg-label-info">Co-founder</span></td>
                          <td class="text-end fw-bold text-info">30%</td>
                        </tr>
                        <tr>
                          <td class="fw-medium">ABC Ventures</td>
                          <td><span class="badge bg-label-success">Investor</span></td>
                          <td class="text-end fw-bold text-success">20%</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class="col-12 col-xl-6">
                <div class="card card-body border shadow-sm h-100">
                  <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-coin text-warning"></i>Investors
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                      <thead class="table-light">
                        <tr>
                          <th class="fw-bold text-muted"><i class="ti ti-building me-1"></i>Name</th>
                          <th class="fw-bold text-muted text-end"><i class="ti ti-tag me-1"></i>Type</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr class="border-top">
                          <td class="fw-medium">ABC Ventures</td>
                          <td class="text-end"><span class="badge bg-label-warning">VC</span></td>
                        </tr>
                        <tr>
                          <td class="fw-medium">XYZ Angels Syndicate</td>
                          <td class="text-end"><span class="badge bg-label-secondary">Angel group</span></td>
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

      <div class="tab-pane fade show active" id="tab-screening" role="tabpanel" aria-labelledby="tab-screening-trigger">
        <div id="program-screening" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2">
              <i class="ti ti-search text-primary"></i>Screening
            </h5>
            <small class="text-muted">Initial and final evaluations</small>
          </div>
          <div class="card-body">
            <div class="row g-4">
              <div class="col-12">
                <div class="border-bottom pb-6 mb-6">
                  <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                      <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="ti ti-circle-check text-info"></i>Initial screening
                      </h5>
                      <small class="text-muted">Screening committee evaluation</small>
                    </div>
                    <span class="badge bg-label-info">
                      <i class="ti ti-tag me-1"></i>Step STP-001
                    </span>
                  </div>
                  <div class="row g-3 mb-4">
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1"><i class="ti ti-star me-1"></i>Overall
                          score</small>
                        <h4 class="mb-0 text-primary fw-bold">4.3 <small class="text-muted fw-normal">/ 5</small></h4>
                      </div>
                    </div>
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1"><i
                            class="ti ti-calendar me-1"></i>Date</small>
                        <p class="mb-0 fw-bold text-dark">Jan 2025</p>
                      </div>
                    </div>
                  </div>
                  <h6 class="mt-4 mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-checklist text-success"></i>Criteria
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                      <thead class="table-light">
                        <tr>
                          <th class="fw-bold text-muted"><i class="ti ti-ruler-measure me-1"></i>Criterion</th>
                          <th class="fw-bold text-muted text-center"><i class="ti ti-percent me-1"></i>Weight</th>
                          <th class="fw-bold text-muted text-end"><i class="ti ti-star me-1"></i>Score</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr class="border-top">
                          <td class="fw-medium">Problem &amp; solution</td>
                          <td class="text-center"><span class="badge bg-label-primary">30%</span></td>
                          <td class="text-end"><span class="badge bg-label-success">4.5</span></td>
                        </tr>
                        <tr>
                          <td class="fw-medium">Market size</td>
                          <td class="text-center"><span class="badge bg-label-primary">25%</span></td>
                          <td class="text-end"><span class="badge bg-label-success">4.2</span></td>
                        </tr>
                        <tr>
                          <td class="fw-medium">Team</td>
                          <td class="text-center"><span class="badge bg-label-primary">20%</span></td>
                          <td class="text-end"><span class="badge bg-label-success">4.6</span></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <h6 class="mt-4 mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-user-check text-warning"></i>Judges evaluations
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                      <thead class="table-light">
                        <tr>
                          <th class="fw-bold text-muted"><i class="ti ti-user me-1"></i>Judge</th>
                          <th class="fw-bold text-muted"><i class="ti ti-hash me-1"></i>ID</th>
                          <th class="fw-bold text-muted text-center"><i class="ti ti-star me-1"></i>Score</th>
                          <th class="fw-bold text-muted"><i class="ti ti-message me-1"></i>Comments</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr class="border-top">
                          <td class="fw-medium">Judge One</td>
                          <td>J-001</td>
                          <td class="text-center"><span class="badge bg-label-info">4.2</span></td>
                          <td class="small">Clear problem and strong traction in Egyptian market.</td>
                        </tr>
                        <tr>
                          <td class="fw-medium">Judge Two</td>
                          <td>J-002</td>
                          <td class="text-center"><span class="badge bg-label-info">4.4</span></td>
                          <td class="small">Team has relevant experience and solid execution plan.</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <div>
                  <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                      <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="ti ti-award text-success"></i>Final screening
                      </h5>
                      <small class="text-muted">Investment committee evaluation</small>
                    </div>
                    <span class="badge bg-label-success">
                      <i class="ti ti-tag me-1"></i>Step STP-010
                    </span>
                  </div>
                  <div class="row g-3 mb-4">
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1"><i class="ti ti-star me-1"></i>Overall
                          score</small>
                        <h4 class="mb-0 text-success fw-bold">4.6 <small class="text-muted fw-normal">/ 5</small></h4>
                      </div>
                    </div>
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1"><i
                            class="ti ti-calendar me-1"></i>Date</small>
                        <p class="mb-0 fw-bold text-dark">Mar 2025</p>
                      </div>
                    </div>
                  </div>
                  <h6 class="mt-4 mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-checklist text-success"></i>Criteria
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                      <thead class="table-light">
                        <tr>
                          <th class="fw-bold text-muted"><i class="ti ti-ruler-measure me-1"></i>Criterion</th>
                          <th class="fw-bold text-muted text-center"><i class="ti ti-percent me-1"></i>Weight</th>
                          <th class="fw-bold text-muted text-end"><i class="ti ti-star me-1"></i>Score</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr class="border-top">
                          <td class="fw-medium">Revenue &amp; traction</td>
                          <td class="text-center"><span class="badge bg-label-success">30%</span></td>
                          <td class="text-end"><span class="badge bg-label-success">4.7</span></td>
                        </tr>
                        <tr>
                          <td class="fw-medium">Scalability</td>
                          <td class="text-center"><span class="badge bg-label-success">25%</span></td>
                          <td class="text-end"><span class="badge bg-label-success">4.5</span></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <h6 class="mt-4 mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-user-check text-warning"></i>Judges evaluations
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                      <thead class="table-light">
                        <tr>
                          <th class="fw-bold text-muted"><i class="ti ti-user me-1"></i>Judge</th>
                          <th class="fw-bold text-muted"><i class="ti ti-hash me-1"></i>ID</th>
                          <th class="fw-bold text-muted text-center"><i class="ti ti-star me-1"></i>Score</th>
                          <th class="fw-bold text-muted"><i class="ti ti-message me-1"></i>Comments</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr class="border-top">
                          <td class="fw-medium">Judge Three</td>
                          <td>J-010</td>
                          <td class="text-center"><span class="badge bg-label-success">4.6</span></td>
                          <td class="small">Ready for next stage of funding, strong growth indicators.</td>
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

      <div class="tab-pane fade show active" id="tab-training" role="tabpanel" aria-labelledby="tab-training-trigger">
        <div id="program-training" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2">
              <i class="ti ti-school text-info"></i>Training
            </h5>
            <small class="text-muted">Sessions attended during the program</small>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th class="fw-bold text-muted"><i class="ti ti-hash me-1"></i>Session ID</th>
                    <th class="fw-bold text-muted"><i class="ti ti-file-text me-1"></i>Title</th>
                    <th class="fw-bold text-muted"><i class="ti ti-hash me-1"></i>Course ID</th>
                    <th class="fw-bold text-muted"><i class="ti ti-book me-1"></i>Course name</th>
                    <th class="fw-bold text-muted"><i class="ti ti-calendar me-1"></i>Date</th>
                    <th class="fw-bold text-muted"><i class="ti ti-steps me-1"></i>Step</th>
                    <th class="fw-bold text-muted text-center"><i class="ti ti-clock me-1"></i>Duration (h)</th>
                    <th class="fw-bold text-muted"><i class="ti ti-flag me-1"></i>Program Step</th>
                    <th class="fw-bold text-muted"><i class="ti ti-users me-1"></i>Attendance</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-top">
                    <td><span class="badge bg-label-primary">TR-101</span></td>
                    <td class="fw-medium">Lean startup fundamentals</td>
                    <td>CR-001</td>
                    <td class="fw-medium">Lean methodology</td>
                    <td>2025-02-01</td>
                    <td><span class="badge bg-label-info">Bootcamp</span></td>
                    <td class="text-center fw-bold">3</td>
                    <td>Training week 1</td>
                    <td><span class="badge bg-label-success">Founder + 2</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <div class="tab-pane fade show active" id="tab-mentorship" role="tabpanel"
        aria-labelledby="tab-mentorship-trigger">
        <div id="program-mentorship" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2">
              <i class="ti ti-message-circle text-success"></i>Mentorship
            </h5>
            <small class="text-muted">Mentor interactions and feedback</small>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th class="fw-bold text-muted"><i class="ti ti-user me-1"></i>Mentor name</th>
                    <th class="fw-bold text-muted"><i class="ti ti-hash me-1"></i>Mentor ID</th>
                    <th class="fw-bold text-muted"><i class="ti ti-flag me-1"></i>Step name</th>
                    <th class="fw-bold text-muted"><i class="ti ti-hash me-1"></i>Step ID</th>
                    <th class="fw-bold text-muted"><i class="ti ti-calendar me-1"></i>Date</th>
                    <th class="fw-bold text-muted text-center"><i class="ti ti-clock me-1"></i>Duration (h)</th>
                    <th class="fw-bold text-muted"><i class="ti ti-user-check me-1"></i>Founder attended</th>
                    <th class="fw-bold text-muted text-center"><i class="ti ti-star me-1"></i>Rating</th>
                    <th class="fw-bold text-muted"><i class="ti ti-message me-1"></i>Comments</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-top">
                    <td class="fw-medium">Mentor A</td>
                    <td><span class="badge bg-label-primary">M-001</span></td>
                    <td>Growth mentoring</td>
                    <td>STP-M-03</td>
                    <td>2025-03-10</td>
                    <td class="text-center"><span class="badge bg-label-info">1.5h</span></td>
                    <td class="fw-medium">Omar Hassan</td>
                    <td class="text-center"><span class="badge bg-label-warning">5★</span></td>
                    <td class="small">Helpful session on pricing strategy and B2B sales pipeline.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <div class="tab-pane fade show active" id="tab-milestones" role="tabpanel"
        aria-labelledby="tab-milestones-trigger">
        <div id="program-milestones" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2">
              <i class="ti ti-flag-check text-warning"></i>Milestones and deliverables
            </h5>
            <small class="text-muted">Planned progress versus completion</small>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th class="fw-bold text-muted"><i class="ti ti-flag me-1"></i>Milestone title</th>
                    <th class="fw-bold text-muted"><i class="ti ti-file-text me-1"></i>Description</th>
                    <th class="fw-bold text-muted"><i class="ti ti-calendar-due me-1"></i>Due date</th>
                    <th class="fw-bold text-muted text-center"><i class="ti ti-progress me-1"></i>Completion</th>
                    <th class="fw-bold text-muted"><i class="ti ti-paperclip me-1"></i>Deliverables</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-top">
                    <td class="fw-medium">MVP launch</td>
                    <td class="small">Launch first production version with payment gateway integration.</td>
                    <td><span class="badge bg-label-info">2025-04-15</span></td>
                    <td class="w-25">
                      <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold">80%</span>
                        <div class="progress grow" style="height: 6px">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 80%"></div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <ul class="list-unstyled mb-0 small">
                        <li><a href="#" class="fw-medium text-primary">
                            <i class="ti ti-file-pdf me-1"></i>Technical architecture PDF
                          </a></li>
                        <li><a href="#" class="fw-medium text-primary">
                            <i class="ti ti-video me-1"></i>MVP demo video
                          </a></li>
                      </ul>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <div class="tab-pane fade show active" id="tab-tasks" role="tabpanel" aria-labelledby="tab-tasks-trigger">
        <div id="program-tasks" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2">
              <i class="ti ti-checklist text-primary"></i>Program tasks
            </h5>
            <small class="text-muted">Tasks assigned to the startup</small>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th class="fw-bold text-muted"><i class="ti ti-file-text me-1"></i>Task title</th>
                    <th class="fw-bold text-muted"><i class="ti ti-hash me-1"></i>Task ID</th>
                    <th class="fw-bold text-muted"><i class="ti ti-hash me-1"></i>Session ID</th>
                    <th class="fw-bold text-muted"><i class="ti ti-file-text me-1"></i>Session title</th>
                    <th class="fw-bold text-muted"><i class="ti ti-file-text me-1"></i>Description</th>
                    <th class="fw-bold text-muted"><i class="ti ti-calendar-due me-1"></i>Due date</th>
                    <th class="fw-bold text-muted"><i class="ti ti-calendar-check me-1"></i>Completion</th>
                    <th class="fw-bold text-muted"><i class="ti ti-user me-1"></i>Mentor</th>
                    <th class="fw-bold text-muted"><i class="ti ti-user-circle me-1"></i>Created by</th>
                    <th class="fw-bold text-muted text-center"><i class="ti ti-badge me-1"></i>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-top">
                    <td class="fw-medium">Customer interviews</td>
                    <td><span class="badge bg-label-primary">TSK-001</span></td>
                    <td>TR-101</td>
                    <td>Lean startup fundamentals</td>
                    <td class="small">Conduct ten customer interviews and summarize findings.</td>
                    <td>2025-02-10</td>
                    <td>2025-02-08</td>
                    <td>Mentor A</td>
                    <td>Program manager</td>
                    <td class="text-center"><span class="badge bg-label-success">
                        <i class="ti ti-check me-1"></i>Completed
                      </span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <div class="tab-pane fade show active" id="tab-quizzes" role="tabpanel" aria-labelledby="tab-quizzes-trigger">
        <div id="program-quizzes" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2">
              <i class="ti ti-help text-info"></i>Quizzes
            </h5>
            <small class="text-muted">Knowledge checks completed by the startup</small>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th class="fw-bold text-muted"><i class="ti ti-hash me-1"></i>Quiz ID</th>
                    <th class="fw-bold text-muted"><i class="ti ti-file-text me-1"></i>Quiz title</th>
                    <th class="fw-bold text-muted"><i class="ti ti-calendar me-1"></i>Date completed</th>
                    <th class="fw-bold text-muted"><i class="ti ti-user me-1"></i>Founder</th>
                    <th class="fw-bold text-muted text-center"><i class="ti ti-percent me-1"></i>Score</th>
                    <th class="fw-bold text-muted text-center"><i class="ti ti-badge me-1"></i>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-top">
                    <td><span class="badge bg-label-primary">QZ-001</span></td>
                    <td class="fw-medium">Lean startup quiz</td>
                    <td>2025-02-05</td>
                    <td>Omar Hassan</td>
                    <td class="text-center"><span class="badge bg-label-warning">90%</span></td>
                    <td class="text-center"><span class="badge bg-label-success">
                        <i class="ti ti-check me-1"></i>Passed
                      </span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <div class="tab-pane fade show active" id="tab-surveys" role="tabpanel" aria-labelledby="tab-surveys-trigger">
        <div id="program-surveys" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2">
              <i class="ti ti-mood-smile text-warning"></i>Surveys
            </h5>
            <small class="text-muted">Feedback during the program</small>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th class="fw-bold text-muted"><i class="ti ti-hash me-1"></i>Survey ID</th>
                    <th class="fw-bold text-muted"><i class="ti ti-file-text me-1"></i>Survey title</th>
                    <th class="fw-bold text-muted"><i class="ti ti-flag me-1"></i>Program step</th>
                    <th class="fw-bold text-muted"><i class="ti ti-calendar me-1"></i>Date completed</th>
                    <th class="fw-bold text-muted text-center"><i class="ti ti-file me-1"></i>Answers</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-top">
                    <td><span class="badge bg-label-primary">SV-001</span></td>
                    <td class="fw-medium">Mid program satisfaction</td>
                    <td>Mid review step</td>
                    <td>2025-03-01</td>
                    <td class="text-center"><a href="#" class="btn btn-sm btn-link px-0 text-primary">
                        <i class="ti ti-eye me-1"></i>View
                      </a></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <div class="tab-pane fade show active" id="tab-services" role="tabpanel" aria-labelledby="tab-services-trigger">
        <div id="program-services" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2">
              <i class="ti ti-gift text-success"></i>Services provided
            </h5>
            <small class="text-muted">Perks and services delivered</small>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th class="fw-bold text-muted"><i class="ti ti-gift me-1"></i>Service name</th>
                    <th class="fw-bold text-muted"><i class="ti ti-hash me-1"></i>Service ID</th>
                    <th class="fw-bold text-muted"><i class="ti ti-file-text me-1"></i>Description</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-top">
                    <td class="fw-medium">Cloud credits</td>
                    <td><span class="badge bg-label-primary">SRV-001</span></td>
                    <td class="small">Cloud infrastructure credits for twelve months to support product development.
                    </td>
                  </tr>
                  <tr>
                    <td class="fw-medium">Legal advisory session</td>
                    <td><span class="badge bg-label-primary">SRV-002</span></td>
                    <td class="small">One-on-one session with legal partner for company setup and shareholder agreement
                      review.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <div class="tab-pane fade show active" id="tab-forms" role="tabpanel" aria-labelledby="tab-forms-trigger">
        <div id="program-forms" class="card mb-6 shadow-sm">
          <div class="card-header d-flex align-items-center justify-content-between">
            <div>
              <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                <i class="ti ti-file-text text-success"></i>Submitted forms
              </h5>
              <small class="text-muted">Custom forms filled during the program</small>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th class="fw-bold text-muted"><i class="ti ti-file-text me-1"></i>Form title</th>
                    <th class="fw-bold text-muted"><i class="ti ti-hash me-1"></i>Form ID</th>
                    <th class="fw-bold text-muted"><i class="ti ti-flag me-1"></i>Custom form step</th>
                    <th class="fw-bold text-muted"><i class="ti ti-hash me-1"></i>Step ID</th>
                    <th class="fw-bold text-muted"><i class="ti ti-calendar me-1"></i>Submission date</th>
                    <th class="fw-bold text-muted text-center"><i class="ti ti-file me-1"></i>Details</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="border-top">
                    <td class="fw-medium">Startup profiling form</td>
                    <td><span class="badge bg-label-primary">FM-001</span></td>
                    <td>Application step form</td>
                    <td>STP-FM-01</td>
                    <td>2025-01-05 14:22</td>
                    <td class="text-center">
                      <a type="button" class="btn btn-sm btn-link px-0 text-primary" data-toggle="form-details"
                        data-target="#form-details-1">
                        <i class="ti ti-eye me-1"></i>View inputs
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div id="form-details-1" class="border rounded mt-3 p-4 d-none mx-3 mb-3">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                  <i class="ti ti-list-details text-primary"></i>Form inputs for Startup profiling form
                </h6>
                <a type="button" class="btn btn-sm btn-link px-0 text-muted" data-toggle="form-details"
                  data-target="#form-details-1">
                  <i class="ti ti-x me-1"></i>Hide
                </a>
              </div>
              <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                  <thead class="table-light">
                    <tr>
                      <th class="fw-bold text-muted"><i class="ti ti-field me-1"></i>Field name</th>
                      <th class="fw-bold text-muted"><i class="ti ti-database me-1"></i>Field type</th>
                      <th class="fw-bold text-muted"><i class="ti ti-file-text me-1"></i>Submitted value</th>
                      <th class="fw-bold text-muted text-center"><i class="ti ti-paperclip me-1"></i>Attachment</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="border-top">
                      <td class="fw-medium">Company description</td>
                      <td><span class="badge bg-label-info">Text</span></td>
                      <td class="small">AI powered payment orchestration platform for SMEs.</td>
                      <td class="text-center">&mdash;</td>
                    </tr>
                    <tr>
                      <td class="fw-medium">Pitch deck file</td>
                      <td><span class="badge bg-label-warning">File</span></td>
                      <td class="small"><span class="badge bg-label-success">Uploaded</span></td>
                      <td class="text-center"><a href="#" class="fw-medium text-primary">
                          <i class="ti ti-download me-1"></i>Download
                        </a></td>
                    </tr>
                    <tr>
                      <td class="fw-medium">Industry</td>
                      <td><span class="badge bg-label-info">Selection</span></td>
                      <td class="small">Financial services</td>
                      <td class="text-center">&mdash;</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="alert alert-info d-flex align-items-center">
      <i class="ti ti-info-circle me-2"></i>
      “This is a snapshot; it reflects the startup’s details and performance at the time of program completion.”
    </div>
  </div>
</div>
@stop

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@stop

@section('pagescripts')
<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Initialize DataTable
    DataTableInit(".table");
  });


  // Export PDF
  const exportPdfButton = document.getElementById("export-pdf-button");
  exportPdfButton.addEventListener("click", exportPDF);

  async function exportPDF() {
    const element = document.getElementById("contentToExport");
    const button = document.getElementById("export-pdf-button");


    button.innerHTML = "Generating PDF...";
    button.disabled = true; // optional

    try {
      const canvas = await html2canvas(element, {
        scale: 2,
        useCORS: true,
        ignoreElements: (el) => {
          return el.id === "navigation-pills" || el.id === "header-actions";
        },
        windowWidth: element.scrollWidth,
        windowHeight: element.scrollHeight,
      });

      const imgData = canvas.toDataURL("image/png");

      const pxToMm = px => px * 0.264583;
      const contentWidthMm = pxToMm(canvas.width);
      const contentHeightMm = pxToMm(canvas.height);

      const pdf = new jspdf.jsPDF({
        orientation: "p",
        unit: "mm",
        format: [contentWidthMm, contentHeightMm],
      });

      pdf.addImage(imgData, "PNG", 0, 0, contentWidthMm, contentHeightMm);
      pdf.save("startup-alumni-report.pdf");

    } catch (err) {
      console.error("PDF export failed:", err);
    }

    // Restore button text
    button.innerHTML = `<i class="ti ti-file-type-pdf me-1"></i>Export PDF`;
    button.disabled = false; // optional
  }

  function DataTableInit(selector, options = {}) {
    // Detect RTL direction
    const isRTL = document.documentElement.dir === "rtl";

    // Default global options
    const defaultOptions = {
        // responsive: true,
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