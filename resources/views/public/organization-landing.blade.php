<!DOCTYPE html>
<html
  lang="{{ $l }}"
  dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
  data-theme="theme-default"
  data-assets-path="/vuexy/assets/"
  data-template="vertical-menu-template"
  data-style="light"
>
<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>{{ __('admin.Landing Page') }} - {{ $isRtl ? $organization->name_ar : $organization->name_en }}</title>

  <meta name="description" content="{{ Str::limit($organization->bio, 160) }}">
  <meta name="keywords" content="{{ $organization->industries->pluck($isRtl ? 'name_ar' : 'name_en')->implode(', ') }}">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url('/o/' . $organization->slug) }}">
  <meta property="og:title" content="{{ $isRtl ? $organization->name_ar : $organization->name_en }}">
  <meta property="og:description" content="{{ Str::limit($organization->bio, 200) }}">
  @if($organization->img_url)
    <meta property="og:image" content="{{ $organization->imageUrl }}">
  @endif

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="{{ url('/o/' . $organization->slug) }}">
  <meta property="twitter:title" content="{{ $isRtl ? $organization->name_ar : $organization->name_en }}">
  <meta property="twitter:description" content="{{ Str::limit($organization->bio, 200) }}">

  <!-- Canonical URL -->
  <link rel="canonical" href="{{ url('/o/' . $organization->slug) }}">

  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
    rel="stylesheet" />

  <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/tabler-icons.css" />

  <!-- Core CSS -->

  <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />

  <style>
    :root {
      --page-gutter: clamp(1rem, 5vw, 5rem);
    }

    /* Navbar, hero, all sections, footer */
    body .container {
      padding-inline: var(--page-gutter);
    }

    /* Navigation pills wrapper (uses px-6 instead of .container) */
    .landing-pills-wrap {
      padding-inline: var(--page-gutter);
      }
    html {
      direction: {{ $l == 'ar' ? 'rtl' : 'ltr' }};
    }
    .card-hover {
      transition: box-shadow 0.25s ease, transform 0.25s ease;
    }
    .card-hover:hover {
      box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.08) !important;
      transform: translateY(-2px);
    }
    .partners-carousel .swiper-slide {
      height: auto;
    }
    .partners-carousel-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      gap: 0.75rem;
      padding: 0.5rem;
      height: 100%;
    }
    .partners-carousel-logo-frame {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 88px;
    }
    .partners-carousel-logo {
      max-width: 100%;
      max-height: 100%;
      width: auto;
      height: auto;
      object-fit: contain;
      filter: grayscale(100%);
      opacity: 0.75;
      transition: filter 0.3s ease, opacity 0.3s ease, transform 0.3s ease;
    }
    .partners-carousel-item:hover .partners-carousel-logo {
      filter: grayscale(0);
      opacity: 1;
      transform: scale(1.05);
    }
    .partners-carousel-name {
      font-size: 0.9375rem;
      font-weight: 600;
      color: #6f7782;
      text-align: center;
      transition: color 0.3s ease;
    }
    .partners-carousel-item:hover .partners-carousel-name {
      color: #696cff;
    }
    .partners-carousel .swiper-pagination {
      position: static;
      margin-top: 1.5rem;
    }
    /* Swiper adds -lock when every slide fits: hide pagination on a single page. */
    .partners-carousel .swiper-pagination.swiper-pagination-lock {
      display: none;
    }
  </style>

  <link rel="stylesheet" href="/vuexy/assets/css/demo.css" />

  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/front-page.css" />
  <!-- Vendors CSS -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/node-waves/node-waves.css" />

  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/nouislider/nouislider.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />

  <!-- Page CSS -->

  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/front-page-landing.css" />

  <!-- Helpers -->
  <script src="/vuexy/assets/vendor/js/helpers.js"></script>
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
  <script src="/vuexy/assets/vendor/js/template-customizer.js"></script>

  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="/vuexy/assets/js/front-config.js"></script>
</head>

<body>
  <script src="/vuexy/assets/vendor/js/dropdown-hover.js"></script>
  <script src="/vuexy/assets/vendor/js/mega-dropdown.js"></script>
  
  <!-- Preview Banner -->
  {{-- @if($isPreview)
  <div class="bg-primary bg-gradient text-white px-3 py-2 text-center">
    <i class="ti ti-eye me-2"></i>
    {{ $isRtl ? 'وضع المعاينة - هذه الصفحة غير مرئية للعامة' : 'Preview Mode - This page is not publicly visible' }}
  </div>
  @endif --}}

  <section class="min-vh-90 d-flex flex-column">
    <!-- Navbar: Start -->
    <nav class="layout-navbar shadow-none py-0">
      <div class="container">
        <div class="navbar navbar-expand-lg landing-navbar px-3 px-md-8">
          <!-- Brand logo and name -->
          <div class="navbar-brand app-brand demo d-flex py-0 py-lg-2 me-4 me-xl-8">
            <!-- Mobile menu toggle -->
            <button class="navbar-toggler border-0 px-0 me-4" type="button" data-bs-toggle="collapse"
              data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
              aria-label="Toggle navigation">
              <i class="ti ti-menu-2 ti-lg align-middle text-heading fw-medium"></i>
            </button>
            <!-- Brand -->
            <a href="javascript:void(0);" class="app-brand-link d-flex align-items-center">
              @if($organization->img_url)
                <img src="{{ route('organizations.logo', $organization->img_url ?? '') }}" alt="Logo" width="32" height="32" class="me-2 object-fit-contain rounded" />
              @endif
              <span class="app-brand-text text-dark fw-bold">{{ Str::limit($isRtl ? $organization->name_ar : $organization->name_en, 20) }}</span>
            </a>
          </div>

          <!-- Menu links -->
          <div class="collapse navbar-collapse landing-nav-menu" id="navbarSupportedContent">
            <button class="navbar-toggler border-0 text-heading position-absolute end-0 top-0 scaleX-n1-rtl" type="button"
              data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
              aria-expanded="false" aria-label="Toggle navigation">
              <i class="ti ti-x ti-lg"></i>
            </button>
            {{-- <ul class="navbar-nav me-auto">
              <li class="nav-item">
                <a class="nav-link fw-medium" aria-current="page" href="#hero">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-medium" aria-current="page" href="#hero">How it works</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-medium" href="#about">Achievements & Impact</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-medium" href="#services">Media & Recognition</a>
              </li>
              <li class="nav-item">
                <a class="nav-link fw-medium" href="#team">Our Partners</a>
              </li>
            </ul> --}}
          </div>
          <div class="landing-menu-overlay d-lg-none"></div>

          <!-- Right toolbar -->
          <ul class="navbar-nav flex-row align-items-center ms-auto">
            
            <!-- Language -->
            <li class="nav-item dropdown-language dropdown me-2 me-xl-1">
              <a class="nav-link dropdown-toggle hide-arrow"
                href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class="ti ti-language rounded-circle ti-md"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li class="dropdown dropdown-language nav-item">
                  @if ($l == 'ar')
                    <a class="dropdown-item" href="/lang/en" 
                      data-language="en" data-text-direction="ltr">
                      <i class="flag-icon flag-icon-us me-2"></i>
                      <span class="selected-language">English</span>
                    </a>
                  @else
                    <a class="dropdown-item" href="/lang/ar" 
                      data-language="ar" data-text-direction="rtl">
                      <i class="flag-icon flag-icon-sa me-2"></i>
                      <span class="selected-language">عربي</span>
                    </a>
                  @endif
                </li>
              </ul>
            </li>
            <!--/ Language -->

            <!-- Style Switcher -->
            <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-1">
              <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class="ti ti-lg"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                <li>
                  <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                    <span class="align-middle"><i class="ti ti-sun me-3"></i>Light</span>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                    <span class="align-middle"><i class="ti ti-moon-stars me-3"></i>Dark</span>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                    <span class="align-middle"><i class="ti ti-device-desktop-analytics me-3"></i>System</span>
                  </a>
                </li>
              </ul>
            </li>
            <!-- Login/Register -->
            @auth
              <li class="nav-item">
                <a class="btn btn-primary" href="{{ url('/dashboard') }}">
                <i class="ti ti-dashboard me-2"></i>
                  {{ __('admin.Dashboard') }}
                </a>
              </li>
            @else
              <li>
                <a href="/login" class="btn btn-primary">
                  <span class="tf-icons ti ti-login scaleX-n1-rtl me-md-1"></span>
                  <span class="d-none d-md-block">{{ __('admin.login_or_register') }}</span>
                </a>
              </li>
            @endauth
          </ul>
        </div>
      </div>
    </nav>
    <!-- Navbar: End -->

    <!-- ============ HERO (FLEX-FILL) ============ -->
    <div class="flex-fill d-flex section-py landing-hero position-relative">
      <div class="container">
        <div class="row">
          <div class="col-lg-8">

            <!-- Logo + Location -->
            <div class="d-flex align-items-center mb-4">
              <div class="me-6">
                @if($organization->img_url)
                  <img 
                    src="{{ route('organizations.logo', $organization->img_url ?? '') }}" 
                    alt="{{ $organization->name_en }}" 
                    width="50" 
                    height="50" 
                    class="object-fit-contain rounded"
                  />
                @endif
              </div>

              <div>
                <h5 class="mb-0 fw-semibold">{{ $isRtl ? $organization->name_ar : $organization->name_en }}</h5>
                @if($organization->city || $organization->country)
                  <small>
                    <i class="ti ti-map-pin"></i> 
                    {{ optional($organization->city)->{'name_' . $l} ?? '-' }}
                    {{ $organization->country ? ', ' : '' }}
                    {{ optional($organization->country)->{'name_' . $l} ?? '-' }}
                  </small>
                @endif
              </div>
            </div>

            <!-- Headline -->
            {{-- <h1 class="fw-bold display-4 mb-3 text-dark">
              {{ __('admin.org_landing_heading') }}
            </h1> --}}
            
            <!-- Description -->
            <h4 class="hero-sub-title mb-6 mb-md-12 text-break">
              {{ Str::limit($organization->bio, 300) }}
            </h4>

            <!-- Industries -->
            @if($organization->industries && $organization->industries->count() > 0)
              <div class="d-flex flex-wrap gap-2 mb-8">
                @foreach($organization->industries as $industry)
                <span class="badge rounded-pill bg-card text-dark px-3 py-2">
                  {{ $isRtl ? $industry->name_ar : $industry->name_en }}
                </span>
                @endforeach
              </div>
            @endif
            
            <!-- CTA + Social -->
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              @if($landingPrograms->count() > 0)
                <a href="#programs" class="btn btn-xl btn-primary mb-8 mb-md-4">
                  {{ __('admin.explore_programs') }}
                </a>
              @endif
              @if(count($socialLinks) > 0)
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
                  <span class="text-dark me-2 d-block">{{ __('admin.follow_us') }}</span>
                  <div class="d-flex align-items-center gap-2">
                    @foreach ($socialLinks as $icon => $link)
                      @if(!empty($link))
                        <a href="{{ $link }}" class="btn btn-icon btn-white waves-effect waves-light">
                          <i class="ti ti-brand-{{ $icon }}"></i>
                        </a>
                      @endif
                    @endforeach
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Navigation Pills -->
  <div class="landing-pills-wrap">
    <div class="card shadow-none border-0 my-8 rounded-pill">
      <div class="card-body">
        <div class="nav-align-top">
          <ul class="nav nav-pills justify-content-lg-center flex-nowrap pb-4 border-0" style="max-width: 100%; overflow-x: auto;" role="tablist">
            @foreach ($sections as $tab)
              @if ($tab['visible'])
                <li class="nav-item" role="presentation">
                  <a
                    type="button"
                    class="nav-link {{ $loop->first ? 'bg-label-primary border border-primary' : '' }}"
                    id="tab-{{ $tab['id'] }}-trigger"
                    href="#{{ $tab['id'] }}"
                    role="tab"
                    aria-controls="tab-{{ $tab['id'] }}"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                  >
                    {{-- <i class="ti ti-{{ $tab['icon'] }} me-1"></i> --}}
                    {{ Str::limit($tab['label'], 50) }}
                  </a>
                </li>
              @endif
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  </div>
  <!--/ Navigation Pills -->

  @php $landingBg = false; @endphp

  <!-- Programs Section -->
  @if(isset($landingPage) && $landingPage->programs_enabled)
  <section id="programs" class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }}">@php $landingBg = !$landingBg; @endphp
    <div class="container">
      @if($landingPage->programs_subtitle)
        <div class="text-center mb-4">
          <span class="badge bg-label-primary">{{ $landingPage->programs_subtitle }}</span>
        </div>
      @endif
      @if($landingPage->programs_title)
        <h4 class="text-center mb-1">
          <span class="position-relative fw-extrabold z-1">
            {{ $landingPage->programs_title }}
            <img
              src="/vuexy/assets/img/front-pages/icons/section-title-icon.png"
              alt="laptop charging"
              class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
          </span>
        </h4>
      @endif
      @if($landingPage->programs_description)
        <p class="text-center mb-12">
          {{ $landingPage->programs_description }}
        </p>
      @endif
      
      @if(isset($landingPrograms) && $landingPrograms->count() > 0)
        <div class="swiper-programs-carousel overflow-hidden">
          <div class="swiper" id="swiper-programs">
            <div class="swiper-wrapper">
              @foreach($landingPrograms as $program)
                <div class="swiper-slide h-100">
                  <div class="card h-100 border shadow-none mb-12" style="width: calc(100% - 24px); margin-inline-end: 24px;">
                    <div class="card-body d-flex flex-column p-4">

                      {{-- Logo + Type badge --}}
                      @if($program->logo || $program->type)
                        <div class="d-flex justify-content-between align-items-start mb-3">
                          @if($program->logo)
                            <div class="rounded-3 border bg-body-secondary d-flex align-items-center justify-content-center p-1"
                                style="width: 56px; height: 56px; flex-shrink: 0;">
                              <img src="{{ $program->logo }}"
                                  alt="{{ $program->title }}"
                                  style="width: 40px; height: 40px; object-fit: contain;" />
                            </div>
                          @endif
                          @if($program->type)
                            <span class="badge bg-label-secondary rounded-pill">
                              {{ __('cycles.' . $program->type) }}
                            </span>
                          @endif
                        </div>
                      @endif

                      {{-- Title --}}
                      @if($program->title)
                        <h5 class="fw-semibold mb-2" style="font-size: 16px; line-height: 1.35;">
                          {{ $program->title }}
                        </h5>
                      @endif

                      {{-- Description --}}
                      @if($program->description)
                        <p class="line-clamp-3 mb-0">
                          {{ $program->description }}
                        </p>
                      @endif

                      {{-- Push everything below to bottom --}}
                      <div class="mt-auto">
                        <hr class="opacity-25 my-3">

                        {{-- Date + Location --}}
                        @if($program->start || $program->location)
                          <div class="d-flex flex-wrap gap-3 mb-4">
                            @if($program->start)
                              <div class="d-flex align-items-center gap-2">
                                <div class="avatar">
                                  <div class="avatar-initial rounded">
                                    <i class="ti ti-calendar text-dark"></i>
                                  </div>
                                </div>
                                <div>
                                  <div class="small text-secondary">
                                    {{ __('admin.Program Duration') }}
                                  </div>
                                  <div class="fw-semibold">
                                    {{ \Carbon\Carbon::parse($program->start)->format('M d') }}
                                    @if($program->end)
                                      – {{ \Carbon\Carbon::parse($program->end)->format('M d, Y') }}
                                    @else
                                      , {{ \Carbon\Carbon::parse($program->start)->format('Y') }}
                                    @endif
                                  </div>
                                </div>
                              </div>
                            @endif

                            @if($program->location)
                              <div class="d-flex align-items-center gap-2">
                                <div class="avatar">
                                  <div class="avatar-initial rounded">
                                    <i class="ti ti-map-pin text-dark"></i>
                                  </div>
                                </div>
                                <div>
                                  <div class="small text-secondary">
                                    {{ __('admin.Location') }}
                                  </div>
                                  <div class="fw-semibold">
                                    {{ $program->location }}
                                  </div>
                                </div>
                              </div>
                            @endif
                          </div>
                        @endif

                        <div class="d-flex align-items-center gap-2 mb-6">
                          <div class="avatar">
                            <div class="avatar-initial rounded">
                              <i class="ti ti-{{ $program->statusIcon }} text-dark"></i>
                            </div>
                          </div>
                          <div>
                            <div class="small text-secondary">
                              {{ __('admin.application_status') }}
                            </div>
                            <div class="fw-semibold">
                              {{ $program->statusLabel }}
                            </div>
                          </div>
                        </div>

                        @if($program->isOpen && $program->public_slug)
                          <a href="/{{ $organization->short_code ?? $organization->slug }}/program/{{ $program->public_slug }}"
                            class="btn btn-primary w-100 fw-semibold"
                            style="border-radius: 10px; padding: 10px;">
                            {{ $program->btnText }}
                            <i class="ti ti-arrow-narrow-{{ $isRtl ? 'left' : 'right' }} ms-2"></i>
                          </a>
                        @elseif($program->isUpcoming)
                          <button class="btn btn-label-warning w-100 fw-semibold" disabled>
                            {{ $program->btnText }}
                          </button>
                        @else
                          <button class="btn btn-label-secondary w-100 fw-semibold" disabled>
                            {{ $program->btnText }}
                          </button>
                        @endif
                      </div>

                    </div>
                  </div>
                </div>
              @endforeach
            </div>
            <!-- Pagination -->
            <div class="swiper-pagination pt-4" id="programs-pagination"></div>
          </div>
        </div>
    
        <!-- Navigation Controls -->
        <div class="programs-nav-controls text-center pt-4" id="programs-nav-controls">
          <button id="programs-previous-btn" class="btn btn-label-primary programs-btn programs-previous-btn me-4 scaleX-n1-rtl" type="button">
            <i class="ti ti-chevron-left ti-md"></i>
          </button>
          <button id="programs-next-btn" class="btn btn-label-primary programs-btn programs-next-btn scaleX-n1-rtl" type="button">
            <i class="ti ti-chevron-right ti-md"></i>
          </button>
        </div>
      @else
        <div class="text-center py-8">
          <h5 class="text-muted mb-0">
            {{ __('landing.No_Programs') }}
          </h5>
        </div>
      @endif
    </div>
  </section>
  @endif

  <!-- How It Works Section -->
  @if($landingPage->how_it_works_enabled && !empty($landingPage->process_steps))
    <section id="how-it-works" class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }}">@php $landingBg = !$landingBg; @endphp
      <div class="container">
        @if($landingPage->how_it_works_subtitle)
          <div class="text-center mb-4">
            <span class="badge bg-label-primary">{{ $landingPage->how_it_works_subtitle }}</span>
          </div>
        @endif
        @if($landingPage->how_it_works_title)
          <h4 class="text-center mb-1">
            <span class="position-relative fw-extrabold z-1">
              {{ $landingPage->how_it_works_title ?: ($isRtl ? 'كيف تعمل' : 'How It Works') }}
              <img
                src="/vuexy/assets/img/front-pages/icons/section-title-icon.png"
                alt="laptop charging"
                class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
          </h4>
        @endif
        @if($landingPage->how_it_works_description)
          <p class="text-center mb-12">
            {{ $landingPage->how_it_works_description }}
          </p>
        @endif
        <div class="row g-6 justify-content-center">
          @foreach($landingPage->process_steps as $index => $step)
            <div class="col-lg-3">
              <div class="card shadow-none border h-100 position-relative">
                <div class="avatar avatar-sm" style="position: absolute; top: -1rem; left: 14px; transform: translateX(-50%);">
                  <div class="avatar-initial rounded-circle bg-label-primary">
                    {{ abs($index + 1) }}
                  </div>
                </div>
                <div class="card-body text-center">
                  <div class="badge rounded p-4 bg-label-{{ $colors[$index] }} mb-4">
                    @if(!empty($step['icon']))
                      <i class="ti {{ $step['icon'] }} ti-lg"></i>
                    @else
                      <span>{{ $index + 1 }}</span>
                    @endif
                  </div>
                  <h5 class="card-title mb-1">{{ $step['title'] }}</h5>
                  <p class="mb-0">{{ $step['description'] }}</p>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </section>
  @endif
  <!--/ How It Works Section -->

  <!-- Achievements Section -->
  @if($landingPage->achievements_enabled && !empty($landingPage->metrics))
  <section id="achievements" class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }} landing-reviews">@php $landingBg = !$landingBg; @endphp
    <div class="container">
      @if($landingPage->achievements_subtitle)
        <div class="text-center mb-4">
          <span class="badge bg-label-primary">{{ $landingPage->achievements_subtitle }}</span>
        </div>
      @endif
      @if($landingPage->achievements_title)
        <h4 class="text-center mb-1">
          <span class="position-relative fw-extrabold z-1">
            {{ $landingPage->achievements_title ?: ($isRtl ? 'الإنجازات والأثر' : 'Achievements & Impact') }}
            <img
              src="/vuexy/assets/img/front-pages/icons/section-title-icon.png"
              alt="laptop charging"
              class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
          </span>
        </h4>
      @endif
      @if($landingPage->achievements_description)
        <p class="text-center mb-12">
          {{ $landingPage->achievements_description }}
        </p>
      @endif

      <div class="row g-6 justify-content-center">
        @foreach($landingPage->metrics as $index => $metric)
          @if(!empty($metric['number']) || !empty($metric['label']))
            <div class="col-12 col-md-4 col-lg-3">
              <div class="card h-100">
                <div class="card-body">
                  <div class="card-icon mb-4">
                    <span class="badge bg-label-{{ $colors[$index + 1] }} rounded p-2">
                      @if(!empty($metric['icon']))
                        <i class="ti {{ $metric['icon'] }} ti-26px"></i>
                      @endif
                    </span>
                  </div>
                  <h4 class="card-title">{{ $metric['number'] ?? '' }}</h4>
                  <p class="card-text">{{ $metric['label'] ?? '' }}</p>
                </div>
              </div>
            </div>
          @endif
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!-- Media & Recognition Section -->
  @if($landingPage->media_enabled && !empty($articlesWithUrls))
  <section id="media" class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }}">@php $landingBg = !$landingBg; @endphp
    <div class="container">
      @if($landingPage->media_subtitle)
        <div class="text-center mb-4">
          <span class="badge bg-label-primary">{{ $landingPage->media_subtitle }}</span>
        </div>
      @endif
      <h4 class="text-center mb-1">
        <span class="position-relative fw-extrabold z-1">
          {{ $landingPage->media_title ?: ($isRtl ? 'الإعلام والتقدير' : 'Media & Recognition') }}
          <img
            src="/vuexy/assets/img/front-pages/icons/section-title-icon.png"
            alt="laptop charging"
            class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
        </span>
      </h4>
      @if($landingPage->media_description)
        <p class="text-center mb-12">{{ $landingPage->media_description }}</p>
      @endif

      <div class="row g-6">
        @foreach($articlesWithUrls as $article)
        @if(!empty($article['title']))
        <div class="col-lg-6">
          <div class="card h-100">
            <div class="card-body">
              <div class="row">
                <div class="col-12 col-md-3 mb-6 mb-md-0">
                  @if(!empty($article['image_url']))
                    <img 
                      src="{{ $article['image_url'] }}"
                      alt="article-img"
                      class="w-100 h-px-100 rounded-2 border object-fit-cover"
                    />
                  @else
                    <div class="w-100 h-px-100 rounded-2 border d-flex align-items-center justify-content-center">
                      <i class="ti ti-news ti-xl"></i>
                    </div>
                  @endif
                </div>
                <div class="col-12 col-md-9">
                  <div class="d-flex flex-column h-100">
                    <div class="flex-grow-1">
                      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                        @if(!empty($article['publication_name']))
                          <h6 class="text-dark mb-3 mb-md-0">{{ $article['publication_name'] }}</h6>
                        @endif
  
                        @if(!empty($article['date']))
                          <span class="small text-muted">
                            <i class="ti ti-calendar ti-sm me-1"></i>
                            {{ \Carbon\Carbon::parse($article['date'])->format('M d, Y') }}
                          </span>
                        @endif
                      </div>
  
                      <h5 class="card-title">{{ $article['title'] }}</h5>
                      
                      @if(!empty($article['summary']))
                        <p class="card-text mb-4">{{ $article['summary'] }}</p>
                      @endif
                    </div>
                    
                    <div class="mt-auto">
                      @if(!empty($article['link']))
                        <a href="{{ $article['link'] }}" target="_blank" class="btn btn-sm btn-text-primary">
                          {{ __('admin.read_more') }} <i class="ti ti-arrow-right ms-2"></i>
                        </a>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endif
        @endforeach
      </div>
    </div>
  </section>
  @endif

  @if($landingPage->partners_enabled && !empty($partnersWithUrls))
  <section id="partners" class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }} landing-reviews pb-0">@php $landingBg = !$landingBg; @endphp
    <!-- Partners carousel: Start -->
    <div class="container">
      <div class="text-center mb-12">
        @if($landingPage->partners_subtitle)
          <div class="mb-4">
            <span class="badge bg-label-primary">{{ $landingPage->partners_subtitle }}</span>
          </div>
        @endif
        @if($landingPage->partners_title)
          <h4 class="mb-1">
            <span class="position-relative fw-extrabold z-1">
              {{ $landingPage->partners_title ?: ($isRtl ? 'شركاؤنا' : 'Our Partners') }}
              <img
                src="/vuexy/assets/img/front-pages/icons/section-title-icon.png"
                alt="laptop charging"
                class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
            </span>
          </h4>
        @endif
        @if($landingPage->partners_description)
          <p class="text-center mb-0">{{ $landingPage->partners_description }}</p>
        @endif
      </div>

      <div class="partners-carousel overflow-hidden">
        <div class="swiper" id="swiper-partners">
          <div class="swiper-wrapper">
            @foreach($partnersWithUrls as $partner)
              <div class="swiper-slide">
                <div class="partners-carousel-item">
                  @if(!empty($partner['logo_url']))
                    <div class="partners-carousel-logo-frame">
                      <img
                        src="{{ $partner['logo_url'] }}"
                        alt="{{ $partner['name'] ?? '' }}"
                        loading="lazy"
                        class="partners-carousel-logo" />
                    </div>
                  @endif
                  @if(!empty($partner['name']))
                    <span class="partners-carousel-name">{{ $partner['name'] }}</span>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
          <div class="swiper-pagination pt-2" id="partners-pagination"></div>
        </div>
      </div>
    <!-- Partners carousel: End -->
    <hr class="m-0 mt-6 mt-md-12" />
  </section>
  @endif

  <!-- Custom Sections -->
  @if(!empty($customSections))
    @foreach($customSections as $index => $customSection)
      @if(!empty($customSection->content))
        <section id="custom-{{ $customSection->id }}" class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }}">@php $landingBg = !$landingBg; @endphp
          <div class="container d-flex flex-column align-items-center">
            <div class="mb-12">
              <span class="badge bg-label-primary">{{ $customSection->title }}</span>
            </div>
            <div class="w-50 d-flex justify-content-center">
              <p>{!! $customSection->safe_content !!}</p>
            </div>
          </div>
        </section>
      @endif
    @endforeach
  @endif

  <!-- Contact Us: Start -->
  <section id="landingContact" class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }} landing-contact">
    <div class="container">
      <div class="text-center mb-4">
        <span class="badge bg-label-primary">{{ __('admin.Contact') }}</span>
      </div>
      <h4 class="text-center mb-12">
        <span class="position-relative fw-extrabold z-1"
          >{{ __('admin.get_in_touch') }}
          <img
            src="/vuexy/assets/img/front-pages/icons/section-title-icon.png"
            alt="laptop charging"
            class="section-title-img position-absolute object-fit-contain bottom-0 z-n1" />
        </span>
      </h4>
      <div class="row g-6 align-items-stretch">
        <div class="col-lg-6 d-flex flex-column gap-4">
          @if($organization->contact_email)
            <a class="card border shadow-none card-hover transition-shadow" href="mailto:{{ $organization->contact_email }}">
              <div class="card-body d-flex align-items-center gap-4">
                <div class="avatar avatar-lg flex-shrink-0">
                  <div class="avatar-initial rounded-circle bg-label-primary">
                    <i class="ti ti-mail ti-lg text-primary"></i>
                  </div>
                </div>
                <div class="d-flex flex-column">
                  <span class="text-muted small fw-medium mb-1">{{ __('admin.Email') }}</span>
                  <h6 class="mb-0 fw-semibold">{{ $organization->contact_email }}</h6>
                </div>
              </div>
            </a>
          @endif
          @if($organization->contact_phone)
            <a class="card border shadow-none card-hover transition-shadow" href="tel:{{ $organization->country_code }}{{ $organization->contact_phone }}">
              <div class="card-body d-flex align-items-center gap-4">
                <div class="avatar avatar-lg flex-shrink-0">
                  <div class="avatar-initial rounded-circle bg-label-primary">
                    <i class="ti ti-phone ti-lg text-primary"></i>
                  </div>
                </div>
                <div class="d-flex flex-column">
                  <span class="text-muted small fw-medium mb-1">{{ __('admin.Phone') }}</span>
                  <h6 class="mb-0 fw-semibold">{{ $organization->country_code }}{{ $organization->contact_phone }}</h6>
                </div>
              </div>
            </a>
          @endif
          @if($organization->address)
            <a class="card border shadow-none card-hover transition-shadow" href="{{ $organization->google_map_url ?: '#' }}" target="{{ $organization->google_map_url ? '_blank' : '_self' }}">
              <div class="card-body d-flex align-items-center gap-4">
                <div class="avatar avatar-lg flex-shrink-0">
                  <div class="avatar-initial rounded-circle bg-label-primary">
                    <i class="ti ti-map-pin ti-lg text-primary"></i>
                  </div>
                </div>
                <div class="d-flex flex-column">
                  <span class="text-muted small fw-medium mb-1">{{ __('admin.Location') }}</span>
                  <h6 class="mb-0 fw-semibold">{{ $organization->address }}</h6>
                </div>
              </div>
            </a>
          @endif
        </div>
        <div class="col-lg-6 d-flex">
          @if($organization->google_map_url || $organization->address)
            <div class="card border shadow-none w-100 overflow-hidden">
              <iframe
                src="{{ $mapEmbedUrl }}"
                width="100%"
                height="100%"
                style="border:0; min-height: 350px; display: block;"
                allowfullscreen=""
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
              </iframe>
            </div>
          @endif
        </div>
      </div>
      {{-- <p class="text-center mb-12 pb-md-4">{{ __('admin.org_landing_contact_description') }}</p> --}}
      {{-- <div class="row g-6">
        <div class="col-lg-5">
          <div class="contact-img-box position-relative border p-2 h-100">
            <img
              src="/vuexy/assets/img/front-pages/icons/contact-border.png"
              alt="contact border"
              class="contact-border-img position-absolute d-none d-lg-block scaleX-n1-rtl" />
            <img
              src="/vuexy/assets/img/front-pages/landing-page/contact-customer-service.png"
              alt="contact customer service"
              class="contact-img w-100 scaleX-n1-rtl" />
            <div class="p-4 pb-2">
              <div class="row g-4">
                <div class="col-md-6 col-lg-12 col-xl-6">
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-primary rounded p-1_5 me-3"><i class="ti ti-mail ti-lg"></i></div>
                    <div>
                      <p class="mb-0">{{ __('admin.Email') }}</p>
                      <h6 class="mb-0">
                        <a href="mailto:{{ $organization->contact_email }}" class="text-heading">{{ $organization->contact_email }}</a>
                      </h6>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-12 col-xl-6">
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-success rounded p-1_5 me-3">
                      <i class="ti ti-phone-call ti-lg"></i>
                    </div>
                    <div>
                      <p class="mb-0">{{ __('admin.Phone') }}</p>
                      <h6 class="mb-0"><a href="tel:{{ $organization->country_code }}{{ $organization->contact_phone }}" class="text-heading">{{ $organization->country_code }} {{ $organization->contact_phone }}</a></h6>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-12">
                  <div class="d-flex align-items-center">
                    <div class="badge bg-label-warning rounded p-1_5 me-3"><i class="ti ti-map-pin ti-lg"></i></div>
                    <div>
                      <p class="mb-0">{{ __('admin.Address') }}</p>
                      <h6 class="mb-0 line-clamp-2">{{ $organization->address }}</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
            <div class="card-body">
              <h4 class="mb-2">{{ __('admin.send_a_message') }}</h4>
              <p class="mb-6">
                {{ __('admin.send_message_description1') }}<br
                  class="d-none d-lg-block" />
                {{ __('admin.send_message_description2') }}
              </p>
              @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
              @endif
              @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
              @endif
              <form method="POST" action="{{ route('organization.contact.submit', $organization->slug) }}">
                @csrf
                <div class="row g-4">
                  <div class="col-md-6">
                    <label class="form-label" for="contact-form-fullname">{{ __('admin.full_name') }}</label>
                    <input type="text" class="form-control" id="contact-form-fullname" name="fullname" placeholder="john" required />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="contact-form-email">{{ __('admin.Email') }}</label>
                    <input
                      type="email"
                      id="contact-form-email"
                      class="form-control"
                      name="email"
                      placeholder="johndoe@gmail.com"
                      required />
                  </div>
                  <div class="col-12 mb-4">
                    <label class="form-label" for="contact-form-message">{{ __('admin.message') }}</label>
                    <textarea
                      id="contact-form-message"
                      class="form-control"
                      name="message"
                      rows="9"
                      placeholder="{{ __('messages.Write your message') }}"
                      required></textarea>
                  </div>
                  <div class="col-12">
                    <button type="submit" class="btn btn-primary">{{ __('admin.send_inquiry') }}</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div> --}}
    </div>
  </section>
  <!-- Contact Us: End -->

  <!-- Footer: Start -->
  <footer class="landing-footer bg-body footer-text">
    <div class="footer-top position-relative overflow-hidden z-1">
      <img src="/vuexy/assets/img/front-pages/backgrounds/footer-bg-light.png" alt="footer bg"
        class="footer-bg banner-bg-img z-n1" />
      <div class="container">
        <div class="row gx-0 gy-6 g-lg-10">
          <div class="col-lg-5">
            <a href="javascript:void(0);" class="app-brand-link d-flex align-items-center mb-6">

              <img src="{{ route('organizations.logo', $organization->img_url ?? '') }}"  alt="Logo" width="32" height="32" class="me-2 object-fit-contain rounded">
              <span class="app-brand-text footer-text fw-bold ms-2 ps-1">
                {{ $isRtl ? $organization->name_ar : $organization->name_en }}
              </span>
            </a>
            <p class="footer-text footer-logo-description mb-6 d-block w-break-word text-break">
              {{ $organization->bio ? nl2br(Str::limit(strip_tags($organization->bio), 150)) : '' }}
            </p>
            <div class="footer-text pe-8">
              <span>
                <i class="ti ti-map-pin ti-sm me-1"></i>
                {{ __('admin.Address') }}
              </span>
              <p class="d-block w-break-word text-break mt-2">{{ $organization->address }}</p>
            </div>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6">
            <h6 class="footer-title mb-6">{{ __('admin.quick_links') }}</h6>
            <ul class="navbar-nav list-unstyled">
              @foreach($sections as $section)
                @if(!str_contains($section['id'], 'custom'))
                  <li class="nav-item mb-4">
                    <a href="#{{ $section['id'] }}" class="footer-link">{{ $section['label'] }}</a>
                  </li>
                @endif
              @endforeach
            </ul>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="d-flex flex-column">
              <div class="mb-6">
                <h6 class="footer-title mb-3">{{ __('admin.Contact us') }}</h6>
                <ul class="list-unstyled">
                  @if($organization->contact_email)
                    <li class="mb-2 text-nowrap">
                      <i class="ti ti-mail me-1 text-white"></i>
                      <a class="footer-link" href="mailto:{{ $organization->contact_email }}">{{ $organization->contact_email }}</a>
                    </li>
                  @endif
                  @if($organization->contact_phone)
                    <li class="mb-2 text-nowrap">
                      <i class="ti ti-phone me12 text-white"></i>
                      <a class="footer-link" href="tel:{{ $organization->country_code }}{{ $organization->contact_phone }}">
                        {{ $organization->country_code }} {{ $organization->contact_phone }}
                      </a>
                    </li>
                  @endif
                </ul>
              </div>  
            </div>
            <div>
              <h6 class="footer-title mb-3">{{ __('admin.follow_us') }}</h6>
              <div>
                @if(array_filter($socialLinks))
                  @foreach($socialLinks as $icon => $link)
                    <a href="{{ $link }}" class="me-3 text-white" target="_blank">
                      <i class="ti ti-brand-{{ $icon }} ti-lg"></i>
                    </a>
                  @endforeach
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-bottom py-3 py-md-5">
      <div class="container d-flex flex-wrap justify-content-between flex-md-row flex-column text-center text-md-start">
        <div class="footer-bottom-text mb-2 mb-md-0">
          &copy; {{ date('Y') }} {{ $isRtl ? $organization->name_ar : $organization->name_en }}.
          {{ $isRtl ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
        </div>
      </div>
    </div>
  </footer>
  <!-- Footer: End -->

  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->
  <script src="/vuexy/assets/vendor/libs/popper/popper.js"></script>
  <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
  <script src="/vuexy/assets/vendor/libs/node-waves/node-waves.js"></script>

  <!-- endbuild -->

  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/nouislider/nouislider.js"></script>
  <script src="/vuexy/assets/vendor/libs/swiper/swiper.js"></script>

  <!-- Main JS -->
  <script src="/vuexy/assets/js/front-main.js"></script>

  <!-- Page JS -->
  <script>
    (function () {
      'use strict';

      const nav = document.querySelector('.layout-navbar'),
        animationImg = document.querySelectorAll('.hero-dashboard-img'),
        swiperPrograms = document.getElementById('swiper-programs');
        
      // -----------------------------------
      // Programs carousel
      // -----------------------------------
      if (swiperPrograms) {
        var programSlideCount = swiperPrograms.querySelectorAll('.swiper-slide').length;
        new Swiper(swiperPrograms, {
          slidesPerView: 1,
          spaceBetween: 24,
          grabCursor: true,
          loop: programSlideCount > 3,
          navigation: {
            nextEl: '#programs-next-btn',
            prevEl: '#programs-previous-btn'
          },
          pagination: {
            el: '#programs-pagination',
            clickable: true
          },
          breakpoints: {
            1200: {
              slidesPerView: 3
            },
            992: {
              slidesPerView: 2
            }
          }
        });

        // Hide nav controls when all cards fit
        function toggleProgramsNav() {
          var controls = document.getElementById('programs-nav-controls');
          if (!controls) return;
          var w = window.innerWidth;
          var visible = programSlideCount > (w >= 1200 ? 3 : (w >= 992 ? 2 : 1));
          controls.style.display = visible ? '' : 'none';
        }
        toggleProgramsNav();
        window.addEventListener('resize', toggleProgramsNav);
      }

      // -----------------------------------
      // Partners carousel
      // -----------------------------------
      const swiperPartners = document.getElementById('swiper-partners');
      if (swiperPartners) {
        new Swiper(swiperPartners, {
          slidesPerView: 2,
          spaceBetween: 24,
          grabCursor: true,
          watchOverflow: true,
          autoHeight: false,
          pagination: {
            el: '#partners-pagination',
            clickable: true
          },
          breakpoints: {
            1200: { slidesPerView: 6 },
            992: { slidesPerView: 4 },
            576: { slidesPerView: 3 }
          }
        });
      }
    })();
  </script>
</body>

</html>
