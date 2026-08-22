<!DOCTYPE html>
<html lang="{{ $l }}" dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}" data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-theme="theme-default"
  data-assets-path="/vuexy/assets/" data-template="vertical-menu-template" data-style="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Growth Labs — Find the Right Growth Program</title>
  <link rel="apple-touch-icon" href="/app-assets/images/ico/apple-icon-120.png">

  <link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/tabler-icons.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="/vuexy/assets/css/demo.css" />

  <style>
    a {
      text-decoration: none;
    }

    ::selection {
      background-color: var(--bs-primary);
      color: var(--bs-white);
    }

    :focus-visible {
      outline: 2px solid var(--bs-primary);
      outline-offset: 3px;
      border-radius: 4px;
    }

    /* ---------- Nav ---------- */
    .gl-nav {
      background: rgba(3, 0, 20, 0.35);
      backdrop-filter: blur(14px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    }

    .brand-mark {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      overflow: hidden;
      background-color: var(--bs-white);
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .brand-mark img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .live-pulse {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background-color: var(--bs-primary);
      display: inline-block;
      box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.6);
      animation: pulse 2.2s infinite;
    }

    @media (prefers-reduced-motion: reduce) {
    .live-pulse {
        animation: none;
      }
    }

    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.55);
      }

      70% {
        box-shadow: 0 0 0 8px rgba(13, 110, 253, 0);
      }

      100% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
      }
    }

    /* ---------- Hero ---------- */
    .gl-hero {
      background:
        radial-gradient(ellipse 900px 620px at 82% -12%, rgba(99, 102, 241, 0.38), transparent 62%),
        radial-gradient(ellipse 760px 520px at -8% 108%, rgba(236, 72, 153, 0.24), transparent 60%),
        radial-gradient(ellipse 640px 640px at 112% 82%, rgba(34, 211, 238, 0.16), transparent 62%),
        radial-gradient(ellipse 500px 400px at 45% 0%, rgba(13, 110, 253, 0.2), transparent 60%),
        linear-gradient(135deg, #030014 0%, #0b0530 55%, #1a1140 100%);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      padding-top: 0;
      padding-bottom: 0;
      position: relative;
      overflow: hidden;
    }

    .gl-hero::after {
      content: "";
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(248, 249, 250, 0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(248, 249, 250, 0.035) 1px, transparent 1px);
      background-size: 48px 48px;
      mask-image: linear-gradient(to bottom, black, transparent 75%);
      pointer-events: none;
    }

    .hero-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      background: rgba(13, 110, 253, 0.08);
      border: 1px solid rgba(13, 110, 253, 0.25);
      color: var(--bs-primary);
      font-size: .78rem;
      padding: .4rem .9rem;
      border-radius: 999px;
    }

    .gl-hero h1 {
      color: var(--bs-light);
      font-weight: 700;
      font-size: clamp(2.4rem, 4.6vw, 3.85rem);
      line-height: 1.08;
    }

    .gl-hero h1 em {
      font-style: normal;
      color: var(--bs-primary);
    }

    /* ---------- Section scaffolding ---------- */
    .eyebrow-mono {
      font-size: .76rem;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: var(--bs-primary);
      background: rgba(13, 110, 253, 0.08);
      border: 1px solid rgba(13, 110, 253, 0.25);
      display: inline-block;
      padding: .35rem .8rem;
      border-radius: 999px;
    }

    .section-head h2 {
      font-weight: 700;
      font-size: clamp(1.9rem, 3vw, 2.5rem);
      letter-spacing: -0.02em;
      color: var(--bs-dark);
    }

    .value-item {
      transition: background .2s ease;
    }

    .value-item:hover {
      background: rgba(79, 57, 187, 0.07);
    }

    .value-index {
      color: var(--bs-secondary);
      font-size: .85rem;
    }

    .value-item h5 {
      font-weight: 700;
      font-size: 1.15rem;
    }

    /* ---------- How it works ---------- */
    .pipeline-wrap {
      position: relative;
    }

    .pipeline-line {
      position: absolute;
      top: 33px;
      left: 0;
      right: 0;
      height: 2px;
      background: repeating-linear-gradient(to right, rgba(33, 37, 41, 0.18) 0 8px, transparent 8px 16px);
      z-index: 0;
    }

    .pipeline-step {
      position: relative;
      z-index: 1;
    }

    .pipeline-node {
      width: 66px;
      height: 66px;
      border-radius: 50%;
      background-color: var(--bs-light);
      color: var(--bs-primary);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      border: 6px solid var(--bs-white);
    }

    /* ---------- CTA ---------- */
    .gl-cta {
      background-color: #000;
      border-radius: 28px;
      padding: 3.5rem 2.5rem;
      position: relative;
      overflow: hidden;
    }

    .gl-cta::before {
      content: "";
      position: absolute;
      width: 480px;
      height: 480px;
      background: radial-gradient(circle, rgba(13, 110, 253, 0.18), transparent 70%);
      top: -160px;
      right: -120px;
    }

    .gl-cta h2 {
      color: var(--bs-light);
      font-weight: 700;
      font-size: clamp(1.8rem, 3vw, 2.4rem);
    }

    /* ---------- Footer ---------- */
    .gl-footer {
      background-color: #000;
    }

    .gl-footer h6 {
      color: var(--bs-light);
      font-size: .74rem;
      text-transform: uppercase;
      letter-spacing: .08em;
    }

    .gl-footer a.flink {
      color: var(--bs-secondary);
      font-size: .9rem;
    }

    .gl-footer .flink:hover {
      color: var(--bs-primary);
    }

    @media (max-width:991.98px) {
      .pipeline-line {
        display: none;
      }

      .stat-strip {
        gap: 1.6rem;
      }
    }
  </style>

  <script src="/vuexy/assets/vendor/js/helpers.js"></script>
  <script src="/vuexy/assets/js/config.js"></script>
</head>

<body>

  <!-- ===== Hero ===== -->
  <header class="gl-hero">

    <!-- ===== Nav ===== -->
    <nav class="navbar navbar-expand-lg gl-nav py-3">
      <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
        <span class="brand-mark"><img src="/images/growthlab.jpeg" alt="Growth Labs logo"></span>
        <span class="fw-bold text-white fs-5">Growth Labs</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
          <i class="ti ti-menu-2 text-white fs-3"></i>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navMenu">
          <ul class="navbar-nav align-items-lg-center gap-lg-1">
            <li class="nav-item"><a class="nav-link text-white" href="#about">About</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="#programs">Programs</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="#how-it-works">How it works</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="#success-stories">Stories</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="#impact">Impact</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="#faq">FAQ</a></li>
            <li class="nav-item dropdown-language dropdown ms-lg-3">
              <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class="ti ti-language text-white fs-5"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  @if ($l == 'ar')
                    <a class="dropdown-item" href="/lang/en" data-language="en" data-text-direction="ltr">
                      <span class="align-middle">English</span>
                    </a>
                  @else
                    <a class="dropdown-item" href="/lang/ar" data-language="ar" data-text-direction="rtl">
                      <span class="align-middle">عربي</span>
                    </a>
                  @endif
                </li>
              </ul>
            </li>
            <li class="nav-item mt-2 mt-lg-0">
              <a href="#programs" class="btn btn-primary rounded-pill">Apply now</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container position-relative"
      style="flex:1; display:flex; flex-direction:column; justify-content:center;">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <span class="hero-eyebrow"><span class="live-pulse"></span> COHORT_2025.STATUS = OPEN</span>
          <h1 class="mt-4">
            Stop scrolling job <br> boards.<br>Start <em>building</em> with the right cohort.
          </h1>
          <p class="mt-6" style="font-size:1.08rem; line-height:1.7; max-width:520px; color: rgba(255,255,255,.78);">
            Growth Labs matches early-stage founders with vetted accelerators, incubators, and hackathons — filtered by
            stage, funding need, and timeline, so you apply to programs built for where you actually are.
          </p>
          <div class="d-flex flex-wrap gap-3 mt-6">
            <a href="#programs" class="btn btn-primary rounded-pill btn-lg">Browse open programs <i
                class="ti ti-arrow-up-right ms-1"></i></a>
            <a href="#how-it-works" class="btn btn-outline-dark text-white rounded-pill btn-lg">See how it works</a>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="ratio ratio-4x3 rounded-4 overflow-hidden shadow-lg">
            <img src="/images/form-illustration.svg" alt="hero-illustration" class="w-100 h-100"
              style="object-fit: contain;">
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- ===== About / Value props ===== -->
  <section class="py-5 py-lg-5" id="about">
    <div class="container py-4">
      <div class="row section-head mb-4">
        <div class="col-lg-7">
          <p class="eyebrow-mono mb-2">// why growth labs</p>
          <h2>A curated path, not another endless directory</h2>
        </div>
        <div class="col-lg-4 offset-lg-1 d-flex align-items-end">
          <p class="text-secondary mb-0">We reject more programs than we list. Every accelerator, incubator, and
            hackathon here has been checked for real mentorship, real outcomes, and real founders behind it.</p>
        </div>
      </div>


      <div class="border-top">
        <div class="row value-item border-bottom py-6 g-3 align-items-center">
          <div class="col-auto"><span class="text-secondary">01</span></div>
          <div class="col-lg-1 col-2">
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded">
                <i class="ti ti-filter-cog"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-10">
            <h5 class="mb-0">Matched, not just listed</h5>
          </div>
          <div class="col-lg-6">
            <p class="text-secondary mb-0 small">Tell us your stage and runway once. We surface programs that actually
              fit instead of a generic feed of every accelerator on the internet.</p>
          </div>
        </div>
        <div class="row value-item border-bottom py-6 g-3 align-items-center">
          <div class="col-auto"><span class="text-secondary">02</span></div>
          <div class="col-lg-1 col-2">
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded">
                <i class="ti ti-shield-check"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-10">
            <h5 class="mb-0">Vetted before you waste a weekend</h5>
          </div>
          <div class="col-lg-6">
            <p class="text-secondary mb-0 small">Our team reviews mentor quality, past cohort outcomes, and terms before
              a program ever gets listed — so you're not the guinea pig.</p>
          </div>
        </div>
        <div class="row value-item border-bottom py-6 g-3 align-items-center">
          <div class="col-auto"><span class="text-secondary">03</span></div>
          <div class="col-lg-1 col-2">
            <div class="avatar">
              <div class="avatar-initial bg-label-primary rounded">
                <i class="ti ti-clock-bolt"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-10">
            <h5 class="mb-0">Deadlines you won't miss</h5>
          </div>
          <div class="col-lg-6">
            <p class="text-secondary mb-0 small">Live seat counts and closing dates on every listing mean you know
              exactly how much runway you have left to apply.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== Programs ===== -->
  <section class="bg-label-secondary py-5" id="programs">
    <div class="container py-4">
      <div class="row section-head mb-4">
        <div class="col-lg-7">
          <p class="eyebrow-mono mb-2">// open cohorts</p>
          <h2>Selected programs, ranked by deadline</h2>
          <p class="text-secondary mb-0 mt-3" style="max-width:560px;">Every program below is vetted and matched to your
            stage — compare live seat counts, closing dates, and terms at a glance.</p>
        </div>
        <div class="col-lg-5 d-flex align-items-end justify-content-lg-end">
          <p class="text-secondary mb-0">Showing 3 open · 2 closed for this cycle</p>
        </div>
      </div>

      <ul class="nav nav-pills mb-4" role="tablist" id="programFilters">
        <li class="nav-item"><a class="nav-link active" href="#" data-filter="all">All programs</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-filter="incubator">Incubator</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-filter="accelerator">Accelerator</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-filter="hackathon">Hackathon</a></li>
      </ul>

      <div class="row g-4">
        <!-- Card 1 -->
        <div class="col-md-6 col-lg-4" data-type="incubator">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center gap-2">
              <div class="d-flex align-items-center gap-2 min-w-0">
                <div class="avatar avatar-md flex-shrink-0">
                  <img class="rounded" src="/vuexy/assets/svg/icons/rocket.svg" alt="Launchpad Accelerator logo">
                </div>
                <div class="min-w-0">
                  <h5 class="mb-1 text-truncate">Launchpad Accelerator 2025</h5>
                  <span class="badge bg-label-primary rounded-pill text-uppercase">Incubator</span>
                </div>
              </div>
              <span class="deadline-tag text-secondary text-nowrap"><i class="ti ti-clock"></i> 6 days left</span>
            </div>
            <div class="card-body">
              <p class="desc text-secondary">A 12-week intensive helping early-stage startups validate their model and
                connect with top investors.</p>
              <div class="d-flex justify-content-between mt-3">
                <span class="small"><i class="ti ti-calendar-event"></i> Start: Sep 8</span>
                <span class="small"><i class="ti ti-calendar-x"></i> End: Nov 28, 2025</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-1 mt-3">
                <span class="small">82/100 seats filled</span>
              </div>
              <div class="progress primary mb-3">
                <div class="fill bg-primary" style="height:100%; border-radius:99px; width:80%;"></div>
              </div>
              <a href="#" class="btn btn-primary rounded-pill w-100">Apply now</a>
            </div>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="col-md-6 col-lg-4" data-type="hackathon">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center gap-2">
              <div class="d-flex align-items-center gap-2 min-w-0">
                <div class="avatar avatar-md flex-shrink-0">
                  <img class="rounded" src="/vuexy/assets/svg/icons/rocket.svg" alt="BuildFast Hackathon logo">
                </div>
                <div class="min-w-0">
                  <h5 class="mb-1 text-truncate">BuildFast Hackathon 2025</h5>
                  <span class="badge bg-label-secondary rounded-pill text-uppercase">Hackathon</span>
                </div>
              </div>
              <span class="deadline-tag text-secondary text-nowrap"><i class="ti ti-clock"></i> 2 days left</span>
            </div>
            <div class="card-body">
              <p class="desc text-secondary">A 48-hour sprint for tech founders to build, pitch, and win in front of
                industry judges.</p>
              <div class="d-flex justify-content-between mt-3">
                <span class="small"><i class="ti ti-calendar-event"></i> Start: Sep 20</span>
                <span class="small"><i class="ti ti-calendar-x"></i> End: Sep 22, 2025</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-1 mt-3">
                <span class="small">240/300 seats filled</span>
              </div>
              <div class="progress primary mb-3">
                <div class="fill bg-primary" style="height:100%; border-radius:99px; width:80%;"></div>
              </div>
              <a href="#" class="btn btn-primary rounded-pill w-100">Apply now</a>
            </div>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="col-md-6 col-lg-4" data-type="incubator">
          <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center gap-2">
              <div class="d-flex align-items-center gap-2 min-w-0">
                <div class="avatar avatar-md flex-shrink-0">
                  <img class="rounded" src="/vuexy/assets/svg/icons/rocket.svg" alt="Founders Incubator logo">
                </div>
                <div class="min-w-0">
                  <h5 class="mb-1 text-truncate">Founders Incubator Spring</h5>
                  <span class="badge bg-label-primary rounded-pill text-uppercase">Incubator</span>
                </div>
              </div>
              <span class="deadline-tag text-secondary text-nowrap"><i class="ti ti-clock"></i> 11 days left</span>
            </div>
            <div class="card-body">
              <p class="desc text-secondary">9-month incubation offering workspace, capital, and seed funding for
                pre-revenue startups.</p>
              <div class="d-flex justify-content-between mt-3">
                <span class="small"><i class="ti ti-calendar-event"></i> Start: Sep 1, 2025</span>
                <span class="small"><i class="ti ti-calendar-x"></i> End: Jun 1, 2026</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-1 mt-3">
                <span class="small">17/20 seats filled</span>
              </div>
              <div class="progress primary mb-3">
                <div class="fill bg-primary" style="height:100%; border-radius:99px; width:80%;"></div>
              </div>
              <a href="#" class="btn btn-primary rounded-pill w-100">Apply now</a>
            </div>
          </div>
        </div>

        <!-- Closed 1 -->
        <div class="col-md-6 col-lg-4" data-type="incubator">
          <div class="card h-100 opacity-50">
            <div class="card-header d-flex justify-content-between align-items-center gap-2">
              <div class="d-flex align-items-center gap-2 min-w-0">
                <div class="avatar avatar-md flex-shrink-0">
                  <img class="rounded" src="/vuexy/assets/svg/icons/rocket.svg" alt="Startup Fundamentals logo">
                </div>
                <div class="min-w-0">
                  <h5 class="mb-1 text-truncate">Startup Fundamentals 2024</h5>
                  <span class="badge bg-label-primary rounded-pill text-uppercase">Incubator</span>
                </div>
              </div>
              <span class="deadline-tag text-secondary text-nowrap">Closed</span>
            </div>
            <div class="card-body">
              <p class="desc text-secondary">An 8-week training program covering lean methodology, product-market fit,
                and pitch prep.</p>
              <div class="d-flex justify-content-between mt-3">
                <span class="small"><i class="ti ti-calendar-event"></i> Start: Aug 12, 2024</span>
                <span class="small"><i class="ti ti-calendar-x"></i> End: Oct 1, 2024</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-1 mt-3">
                <span class="small">Full — waitlist closed</span>
              </div>
              <div class="progress primary mb-3">
                <div class="fill bg-secondary" style="height:100%; border-radius:99px; width:100%;"></div>
              </div>
              <span class="btn btn-disabled rounded-pill border border-dashed w-100">Applications closed</span>
            </div>
          </div>
        </div>

        <!-- Closed 2 -->
        <div class="col-md-6 col-lg-4" data-type="accelerator">
          <div class="card h-100 opacity-50">
            <div class="card-header d-flex justify-content-between align-items-center gap-2">
              <div class="d-flex align-items-center gap-2 min-w-0">
                <div class="avatar avatar-md flex-shrink-0">
                  <img class="rounded" src="/vuexy/assets/svg/icons/rocket.svg" alt="Scale-Up Accelerator logo">
                </div>
                <div class="min-w-0">
                  <h5 class="mb-1 text-truncate">Scale-Up Accelerator 2024</h5>
                  <span class="badge bg-label-secondary rounded-pill text-uppercase">Accelerator</span>
                </div>
              </div>
              <span class="deadline-tag text-secondary text-nowrap">Closed</span>
            </div>
            <div class="card-body">
              <p class="desc text-secondary">A growth-stage accelerator focused on scaling operations, fundraising, and
                expansion.</p>
              <div class="d-flex justify-content-between mt-3">
                <span class="small"><i class="ti ti-calendar-event"></i> Start: Jan 15, 2024</span>
                <span class="small"><i class="ti ti-calendar-x"></i> End: Jun 30, 2024</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-1 mt-3">
                <span class="small">Full — waitlist closed</span>
              </div>
              <div class="progress primary mb-3">
                <div class="fill bg-secondary" style="height:100%; border-radius:99px; width:100%;"></div>
              </div>
              <span class="btn btn-disabled rounded-pill border border-dashed w-100">Applications closed</span>
            </div>
          </div>
        </div>

        <!-- Notify card -->
        <div class="col-md-6 col-lg-4">
          <div class="card waves-effect d-flex flex-column align-items-center justify-content-center text-center p-4 h-100"
            style="border-style:dashed; border-color: rgba(33, 37, 41, 0.1);">
            <i class="ti ti-bell-ringing fs-2 text-primary mb-2"></i>
            <h5 class="mb-2">More cohorts opening soon</h5>
            <p class="desc text-secondary mb-3">Get notified the moment a new program matching your stage opens
              applications.</p>
            <a href="#" class="btn btn-primary btn-lg rounded-pill px-4">Notify me</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== How it works ===== -->
  <section class="py-5" id="how-it-works">
    <div class="container py-4">
      <div class="row section-head mb-5 text-center">
        <div class="col-lg-7 mx-auto">
          <p class="eyebrow-mono mb-2">// the process</p>
          <h2>From browsing to building, in three steps</h2>
          <p class="text-secondary mb-0 mt-3">A simple pipeline — set your goals, compare your matches, and apply with
            one profile. No spreadsheets, no tabs.</p>
        </div>
      </div>

      <div class="row pipeline-wrap">
        <div class="pipeline-line d-none d-lg-block"></div>

        <div class="col-lg-4 pipeline-step text-center mb-5 mb-lg-0">
          <span class="pipeline-node"><i class="ti ti-adjustments-horizontal"></i></span>
          <h5 class="fw-bold mt-3">Set your stage &amp; goals</h5>
          <p class="text-secondary small mx-auto" style="max-width:260px;">Tell us where your startup stands — idea,
            MVP, or revenue — and what you need most: capital, mentorship, or speed.</p>
        </div>
        <div class="col-lg-4 pipeline-step text-center mb-5 mb-lg-0">
          <span class="pipeline-node"><i class="ti ti-list-search"></i></span>
          <h5 class="fw-bold mt-3">Compare matched programs</h5>
          <p class="text-secondary small mx-auto" style="max-width:260px;">Review live seat counts, deadlines, and
            mentor profiles side by side — no more digging through ten browser tabs.</p>
        </div>
        <div class="col-lg-4 pipeline-step text-center">
          <span class="pipeline-node"><i class="ti ti-send"></i></span>
          <h5 class="fw-bold mt-3">Apply with one profile</h5>
          <p class="text-secondary small mx-auto" style="max-width:260px;">Reuse a single founder profile across every
            application. Track every response in one inbox.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== CTA ===== -->
  <section class="py-5">
    <div class="container">
      <div class="gl-cta text-center">
        <p class="eyebrow-mono text-primary mb-3 position-relative">// next cohort closes in 2 days</p>
        <h2 class="position-relative">Your next program is already accepting applications.</h2>
        <p class="text-secondary mt-3 position-relative mx-auto" style="max-width:480px;">Join 4,300 founders who
          skipped the search and went straight to building.</p>
        <a href="#programs" class="btn btn-primary mt-3 position-relative">Find my program <i
            class="ti ti-arrow-up-right ms-1"></i></a>
      </div>
    </div>
  </section>

  <!-- ===== Success stories ===== -->
  <section class="py-5" id="success-stories">
    <div class="container py-4">
      <p class="eyebrow-mono mb-2">// what founders say</p>
      <div class="row section-head mb-4">
        <div class="col-lg-7">
          <h2>Founders who found their fit</h2>
        </div>
        <div class="col-lg-4 offset-lg-1 d-flex align-items-end">
          <p class="text-secondary mb-0">Track record matters. 68% of founders who applied through Growth Labs secured a
            spot in their top-choice program.</p>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="h-100 p-4 rounded-4"
            style="background:rgba(79, 57, 187, 0.05); border:1px solid rgba(79, 57, 187, 0.1);">
            <div class="d-flex align-items-center gap-2 mb-3">
              <div class="avatar">
                <div class="avatar-initial bg-label-primary rounded-circle fw-bold">SM</div>
              </div>
              <div>
                <h6 class="mb-0">Sarah M.</h6>
                <span class="text-secondary small">Fintech, Pre-seed</span>
              </div>
            </div>
            <p class="text-secondary small mb-0">"Matched into a program I didn't even know existed. Two weeks later I
              had
              my first term sheet conversation."</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="h-100 p-4 rounded-4"
            style="background:rgba(79, 57, 187, 0.05); border:1px solid rgba(79, 57, 187, 0.1);">
            <div class="d-flex align-items-center gap-2 mb-3">
              <div class="avatar">
                <div class="avatar-initial bg-label-primary rounded-circle fw-bold">OK</div>
              </div>
              <div>
                <h6 class="mb-0">Omar K.</h6>
                <span class="text-secondary small">Healthtech, MVP</span>
              </div>
            </div>
            <p class="text-secondary small mb-0">"The deadline tracker saved us. We applied in the last 24 hours and got
              in — barely."</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="h-100 p-4 rounded-4"
            style="background:rgba(79, 57, 187, 0.05); border:1px solid rgba(79, 57, 187, 0.1);">
            <div class="d-flex align-items-center gap-2 mb-3">
              <div class="avatar">
                <div class="avatar-initial bg-label-primary rounded-circle fw-bold">LN</div>
              </div>
              <div>
                <h6 class="mb-0">Lina N.</h6>
                <span class="text-secondary small">Edtech, Revenue</span>
              </div>
            </div>
            <p class="text-secondary small mb-0">"One profile, five applications. It cut our application time from days
              to
              an afternoon."</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== Impact ===== -->
  <section class="bg-label-secondary py-5" id="impact">
    <div class="container py-4">
      <p class="eyebrow-mono mb-2">// the numbers</p>
      <div class="row section-head mb-4">
        <div class="col-lg-7">
          <h2>Outcomes you can measure</h2>
        </div>
        <div class="col-lg-5 d-flex align-items-end justify-content-lg-end">
          <p class="text-secondary mb-0">Real numbers from the last two cohorts — not vanity metrics.</p>
        </div>
      </div>

      <div class="row g-4 text-center">
        <div class="col-sm-4">
          <div class="h-100 py-5 px-4 rounded-4 bg-white" style="border:1px solid rgba(33, 37, 41, 0.08);">
            <h1 class="text-primary fw-bold mb-1" style="font-size:2.8rem;">4,300+</h1>
            <p class="text-secondary small mb-0">founders matched to programs</p>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="h-100 py-5 px-4 rounded-4 bg-white" style="border:1px solid rgba(33, 37, 41, 0.08);">
            <h1 class="text-primary fw-bold mb-1" style="font-size:2.8rem;">$12M</h1>
            <p class="text-secondary small mb-0">raised by matched founders</p>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="h-100 py-5 px-4 rounded-4 bg-white" style="border:1px solid rgba(33, 37, 41, 0.08);">
            <h1 class="text-primary fw-bold mb-1" style="font-size:2.8rem;">120+</h1>
            <p class="text-secondary small mb-0">vetted programs listed</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== FAQ ===== -->
  <section class="py-5" id="faq">
    <div class="container py-4">
      <div class="row section-head mb-4 text-center">
        <div class="col-lg-7 mx-auto">
          <p class="eyebrow-mono mb-2">// faq</p>
          <h2>Questions founders ask us</h2>
          <p class="text-secondary mb-0 mt-3">Short answers to the things we hear most before someone applies.</p>
        </div>
      </div>

      <div class="row g-3 mx-lg-auto" style="max-width:860px;">
        <div class="col-12">
          <div class="p-4 rounded-4 bg-label-primary" style="border:1px solid rgba(13, 110, 253, 0.2);">
            <h6 class="fw-bold mb-2"><i class="ti ti-question-mark me-2"></i>Is there a fee to apply?</h6>
            <p class="text-secondary small mb-0">No. Growth Labs is free for founders — programs cover the platform cost
              when you join through us.</p>
          </div>
        </div>
        <div class="col-12">
          <div class="p-4 rounded-4 bg-label-primary" style="border:1px solid rgba(13, 110, 253, 0.2);">
            <h6 class="fw-bold mb-2"><i class="ti ti-question-mark me-2"></i>How do you pick which programs to list?
            </h6>
            <p class="text-secondary small mb-0">Every program is reviewed for real mentorship, transparent terms, and
              verifiable cohort outcomes before it goes live.</p>
          </div>
        </div>
        <div class="col-12">
          <div class="p-4 rounded-4 bg-label-primary" style="border:1px solid rgba(13, 110, 253, 0.2);">
            <h6 class="fw-bold mb-2"><i class="ti ti-question-mark me-2"></i>Can I apply with the same profile
              everywhere?</h6>
            <p class="text-secondary small mb-0">Yes — build one founder profile and reuse it across every application,
              then track responses in a single inbox.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== Footer ===== -->
  <footer class="gl-footer py-5 mt-3">
    <div class="container py-3">
      <div class="row g-4">
        <div class="col-lg-4">
          <a class="d-flex align-items-center gap-2 mb-3" href="#">
            <span class="brand-mark"><img src="/images/growthlab.jpeg" alt="Growth Labs logo"></span>
            <span class="fw-bold text-white fs-5">Growth Labs</span>
          </a>
          <p class="text-secondary small" style="max-width:280px;">A curated path from founder to funded — accelerators,
            incubators, and hackathons, matched to your stage.</p>
          <div class="d-flex gap-2 mt-3">
            <a href="https://x.com/your-profile" target="_blank" rel="noopener noreferrer"
              class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="X">
              <i class="ti ti-brand-x"></i>
            </a>
          
            <a href="https://www.linkedin.com/company/your-company" target="_blank" rel="noopener noreferrer"
              class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="LinkedIn">
              <i class="ti ti-brand-linkedin"></i>
            </a>
          
            <a href="https://www.instagram.com/your-profile" target="_blank" rel="noopener noreferrer"
              class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="Instagram">
              <i class="ti ti-brand-instagram"></i>
            </a>
          
            <a href="https://www.youtube.com/@your-channel" target="_blank" rel="noopener noreferrer"
              class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="YouTube">
              <i class="ti ti-brand-youtube"></i>
            </a>
          
            <a href="https://www.tiktok.com/@your-profile" target="_blank" rel="noopener noreferrer"
              class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="TikTok">
              <i class="ti ti-brand-tiktok"></i>
            </a>
          
            <a href="https://www.facebook.com/your-page" target="_blank" rel="noopener noreferrer"
              class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="Facebook">
              <i class="ti ti-brand-facebook"></i>
            </a>
          </div>
        </div>
        <div class="col-6 col-lg-2 offset-lg-2">
          <h6 class="mb-3">Navigate</h6>
          <div class="d-flex flex-column gap-2">
            <a href="#about" class="flink">About</a>
            <a href="#programs" class="flink">Programs</a>
            <a href="#how-it-works" class="flink">Process</a>
          </div>
        </div>
        <div class="col-6 col-lg-2">
          <h6 class="mb-3">Programs</h6>
          <div class="d-flex flex-column gap-2">
            <a href="#" class="flink">Accelerators</a>
            <a href="#" class="flink">Incubators</a>
            <a href="#" class="flink">Hackathons</a>
          </div>
        </div>
        <div class="col-6 col-lg-2">
          <h6 class="mb-3">Company</h6>
          <div class="d-flex flex-column gap-2">
            <a href="#" class="flink">Contact</a>
            <a href="#" class="flink">Partners</a>
            <a href="#" class="flink">Privacy</a>
          </div>
        </div>
      </div>
      <hr class="border-secondary mt-5">
      <div class="d-flex flex-wrap justify-content-between gap-2">
        <p class="text-secondary small mb-0">© 2025 Growth Labs. All rights reserved.</p>
        <p class="text-secondary small mb-0" style="letter-spacing: 0.01em">
          Built for founders, by founders.
        </p>
      </div>
    </div>
  </footer>

  <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
  <script src="/vuexy/assets/vendor/libs/popper/popper.js"></script>
  <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
  <script src="/vuexy/assets/js/main.js"></script>

  <script>
    document.querySelectorAll('#programFilters .nav-link').forEach(function (pill) {
      pill.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelectorAll('#programFilters .nav-link').forEach(function (p) {
          p.classList.remove('active');
        });
        pill.classList.add('active');
        var filter = pill.getAttribute('data-filter');
        document.querySelectorAll('#programs .row.g-4 > .col-md-6').forEach(function (col) {
          if (filter === 'all' || !col.dataset.type || col.dataset.type === filter) {
            col.style.display = '';
          } else {
            col.style.display = 'none';
          }
        });
      });
    });
  </script>
</body>

</html>
