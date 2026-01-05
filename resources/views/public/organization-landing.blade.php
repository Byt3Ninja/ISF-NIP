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
  <link rel="icon" type="image/x-icon" href="/vuexy/assets/img/favicon/favicon.ico" />

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

  {{-- {{ dd($organization) }} --}}

  @if($isPreview)
  <!-- Preview Banner -->
  <div class="preview-banner">
    <i class="ti ti-eye me-2"></i>
    {{ $isRtl ? 'وضع المعاينة - هذه الصفحة غير مرئية للعامة' : 'Preview Mode - This page is not publicly visible' }}
  </div>
  @endif

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
                <img src="{{ $organization->img_url }}" alt="Logo" width="32" height="32" class="me-2 object-fit-contain" />
              @endif
              <span class="app-brand-text text-dark fw-bold">{{ Str::limit($isRtl ? $organization->name_ar : $organization->name_en, 10) }}</span>
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
    <div id="hero-animation" class="flex-fill d-flex section-py landing-hero position-relative">
      <div class="container">
        <div class="row px-4 px-lg-0">
          <div class="col-lg-8 offset-lg-1">

            <!-- Logo + Location -->
            <div class="d-flex align-items-center mb-4">
              <div class="avatar avatar-lg bg-card rounded-3 d-flex align-items-center justify-content-center me-3">
                @if($organization->img_url)
                  <img src="{{ $organization->img_url }}" alt="{{ $organization->name_en }}" class="rounded-circle">
                @else
                  <i class="ti ti-rocket ti-lg text-primary"></i>
                @endif
              </div>

              <div>
                <h5 class="mb-0 fw-semibold">{{ $isRtl ? $organization->name_ar : $organization->name_en }}</h5>
                <small class="">
                  <i class="ti ti-map-pin"></i> San Francisco, CA
                </small>
              </div>
            </div>

            <!-- Headline -->
            <h1 class="fw-bold display-4 mb-3 text-dark">
              {{ __('admin.org_landing_heading') }}
            </h1>
            
            <!-- Description -->
            <h2 class="hero-sub-title h5 mb-6 line-clamp-3">
              {{ $organization->bio }}
            </h2>

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
            <div class="d-flex align-items-center flex-wrap">
              <button class="btn btn-xl btn-primary mb-8 mb-md-4">
                {{ __('admin.explore_programs') }}
              </button>

              @php
                $socialLinks = [
                  'x' => $organization->twitter_handle,
                  'linkedin' => $organization->linkedin_company_url,
                  'facebook' => $organization->facebook_page_url,
                  'instagram' => $organization->instagram_profile_url,
                  'tiktok' => $organization->tiktok_url,
                  'youtube' => $organization->youtube_channel_url
                ]
              @endphp

              @if(array_filter($socialLinks))
              <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 ms-lg-auto">
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

  <!-- Footer: Start -->
  {{-- <footer class="landing-footer bg-body footer-text">
    <div class="footer-top position-relative overflow-hidden z-1">
      <img src="/vuexy/assets/img/front-pages/backgrounds/footer-bg-light.png" alt="footer bg"
        class="footer-bg banner-bg-img z-n1" data-app-light-img="front-pages/backgrounds/footer-bg-light.png"
        data-app-dark-img="front-pages/backgrounds/footer-bg-dark.png" />
      <div class="container">
        <div class="row gx-0 gy-6 g-lg-10">
          <div class="col-lg-5">
            <a href="landing-page.html" class="app-brand-link mb-6">
              <span class="app-brand-logo demo">
                <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                    fill="#7367F0" />
                  <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                    d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616" />
                  <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                    d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616" />
                  <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                    fill="#7367F0" />
                </svg>
              </span>
              <span class="app-brand-text demo footer-link fw-bold ms-2 ps-1">Vuexy</span>
            </a>
            <p class="footer-text footer-logo-description mb-6">
              Most developer friendly & highly customisable Admin Dashboard Template.
            </p>
            <form class="footer-form">
              <label for="footer-email" class="small">Subscribe to newsletter</label>
              <div class="d-flex mt-1">
                <input type="email" class="form-control rounded-0 rounded-start-bottom rounded-start-top"
                  id="footer-email" placeholder="Your email" />
                <button type="submit" class="btn btn-primary shadow-none rounded-0 rounded-end-bottom rounded-end-top">
                  Subscribe
                </button>
              </div>
            </form>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <h6 class="footer-title mb-6">Demos</h6>
            <ul class="list-unstyled">
              <li class="mb-4">
                <a href="../vertical-menu-template/" target="_blank" class="footer-link">Vertical Layout</a>
              </li>
              <li class="mb-4">
                <a href="../horizontal-menu-template/" target="_blank" class="footer-link">Horizontal Layout</a>
              </li>
              <li class="mb-4">
                <a href="../vertical-menu-template-bordered/" target="_blank" class="footer-link">Bordered Layout</a>
              </li>
              <li class="mb-4">
                <a href="../vertical-menu-template-semi-dark/" target="_blank" class="footer-link">Semi Dark Layout</a>
              </li>
              <li class="mb-4">
                <a href="../vertical-menu-template-dark/" target="_blank" class="footer-link">Dark Layout</a>
              </li>
            </ul>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <h6 class="footer-title mb-6">Pages</h6>
            <ul class="list-unstyled">
              <li class="mb-4">
                <a href="pricing-page.html" class="footer-link">Pricing</a>
              </li>
              <li class="mb-4">
                <a href="payment-page.html" class="footer-link">Payment<span
                    class="badge bg-primary ms-2">New</span></a>
              </li>
              <li class="mb-4">
                <a href="checkout-page.html" class="footer-link">Checkout</a>
              </li>
              <li class="mb-4">
                <a href="help-center-landing.html" class="footer-link">Help Center</a>
              </li>
              <li class="mb-4">
                <a href="../vertical-menu-template/auth-login-cover.html" target="_blank"
                  class="footer-link">Login/Register</a>
              </li>
            </ul>
          </div>
          <div class="col-lg-3 col-md-4">
            <h6 class="footer-title mb-6">Download our app</h6>
            <a href="javascript:void(0);" class="d-block mb-4"><img
                src="/vuexy/assets/img/front-pages/landing-page/apple-icon.png" alt="apple icon" /></a>
            <a href="javascript:void(0);" class="d-block"><img
                src="/vuexy/assets/img/front-pages/landing-page/google-play-icon.png" alt="google play icon" /></a>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-bottom py-3 py-md-5">
      <div class="container d-flex flex-wrap justify-content-between flex-md-row flex-column text-center text-md-start">
        <div class="mb-2 mb-md-0">
          <span class="footer-bottom-text">©
            <script>
              document.write(new Date().getFullYear());
            </script>
          </span>
          <a href="https://pixinvent.com" target="_blank" class="fw-medium text-white text-white">Pixinvent,</a>
          <span class="footer-bottom-text"> Made with ❤️ for a better web.</span>
        </div>
        <div>
          <a href="https://github.com/pixinvent" class="me-3" target="_blank">
            <img src="/vuexy/assets/img/front-pages/icons/github.svg" alt="github icon" />
          </a>
          <a href="https://www.facebook.com/pixinvents/" class="me-3" target="_blank">
            <img src="/vuexy/assets/img/front-pages/icons/facebook.svg" alt="facebook icon" />
          </a>
          <a href="https://twitter.com/pixinvents" class="me-3" target="_blank">
            <img src="/vuexy/assets/img/front-pages/icons/twitter.svg" alt="twitter icon" />
          </a>
          <a href="https://www.instagram.com/pixinvents/" target="_blank">
            <img src="/vuexy/assets/img/front-pages/icons/instagram.svg" alt="google icon" />
          </a>
        </div>
      </div>
    </div>
  </footer> --}}
  <!-- Footer: End -->

  <!-- Navbar -->
  {{-- <nav class="navbar navbar-expand-lg landing-navbar sticky-top py-3">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
        @if($organization->img_url)
        <img src="{{ $organization->imageUrl }}" alt="{{ $isRtl ? $organization->name_ar : $organization->name_en }}"
          class="rounded me-2" style="height: 40px; width: auto;">
        @endif
        <span>{{ $isRtl ? $organization->name_ar : $organization->name_en }}</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
          <!-- Language Toggle -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="ti ti-language me-1"></i>
              {{ $isRtl ? 'العربية' : 'English' }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="/lang/{{ $isRtl ? 'en' : 'ar' }}">
                  {{ $isRtl ? 'English' : 'العربية' }}
                </a>
              </li>
            </ul>
          </li>

          @auth
          <li class="nav-item">
            <a class="btn btn-primary ms-3" href="{{ url('/dashboard') }}">
              <i class="ti ti-dashboard me-1"></i>
              {{ $isRtl ? 'لوحة التحكم' : 'Dashboard' }}
            </a>
          </li>
          @else
          <li class="nav-item">
            <a class="nav-link" href="{{ url('/login') }}">
              {{ $isRtl ? 'تسجيل الدخول' : 'Login' }}
            </a>
          </li>
          <li class="nav-item">
            <a class="btn btn-primary ms-2" href="{{ url('/register') }}">
              {{ $isRtl ? 'إنشاء حساب' : 'Register' }}
            </a>
          </li>
          @endauth
        </ul>
      </div>
    </div>
  </nav> --}}

  {{--
  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <div class="row">
        <div class="col-lg-10 {{ $isRtl ? 'text-end' : '' }}">
          <!-- Logo and Organization Name Row -->
          <div class="hero-header d-flex align-items-center mb-4 {{ $isRtl ? 'flex-row-reverse' : '' }}">
            @if($organization->img_url)
            <img src="{{ $organization->imageUrl }}"
              alt="{{ $isRtl ? $organization->name_ar : $organization->name_en }}"
              class="hero-logo {{ $isRtl ? 'ms-3' : 'me-3' }}">
            @endif
            <div>
              <h1 class="hero-title mb-1">{{ $isRtl ? $organization->name_ar : $organization->name_en }}</h1>
              @if(($organization->city && ($organization->city->name_en || $organization->city->name_ar)) ||
              ($organization->country && ($organization->country->name_en || $organization->country->name_ar)))
              <div class="hero-location">
                <i class="ti ti-map-pin {{ $isRtl ? 'ms-1' : 'me-1' }}"></i>
                @if($organization->city)
                {{ $isRtl ? $organization->city->name_ar : $organization->city->name_en }}@if($organization->country),
                @endif
                @endif
                @if($organization->country)
                {{ $isRtl ? $organization->country->name_ar : $organization->country->name_en }}
                @endif
              </div>
              @endif
            </div>
          </div>

          <!-- Main Headline/Tagline -->
          <h1 class="hero-org-name mb-3">
            <span>
              @if($organization->organizationType)
              {{ $organization->organizationType->localizedName }}
              @else
              {{ $isRtl ? $organization->name_ar : $organization->name_en }}
              @endif
            </span>
          </h1>

          <!-- Description -->
          @if($organization->bio)
          <p class="hero-description">{{ $organization->bio }}</p>
          @endif

          <!-- Industry Tags with Icons -->
          @if($organization->industries && $organization->industries->count() > 0)
          <div class="hero-industries mt-4">
            @foreach($organization->industries as $industry)
            <span class="industry-tag">
              <i class="ti {{ getIndustryIcon($industry->name_en) }} {{ $isRtl ? 'ms-2' : 'me-2' }}"></i>{{ $isRtl ?
              $industry->name_ar : $industry->name_en }}
            </span>
            @endforeach
          </div>
          @endif
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works Section -->
  @if($landingPage->how_it_works_enabled && !empty($landingPage->process_steps))
  <section class="landing-section section-how-it-works">
    <div class="container">
      <div class="text-center mb-5">
        @if($landingPage->how_it_works_subtitle)
        <div class="section-bill-subtitle">
          <span> {{ $landingPage->how_it_works_subtitle }} </span>
        </div>
        @endif
        <h2 class="section-title">
          {{ $landingPage->how_it_works_title ?: ($isRtl ? 'كيف تعمل' : 'How It Works') }}
        </h2>
        @if($landingPage->how_it_works_description)
        <p class="section-subtitle">{{ $landingPage->how_it_works_description }}</p>
        @endif
      </div>

      <div class="row g-4">
        @foreach($landingPage->process_steps as $index => $step)
        @if(!empty($step['title']))
        <div class="col-md-6 col-lg-3">
          <div class="step-card">
            @if(!empty($step['icon']))
            <div class="step-icon">
              <i class="ti {{ $step['icon'] }}"></i>
            </div>
            @else
            <div class="step-number">{{ $index + 1 }}</div>
            @endif
            <h5 class="fw-bold mb-2">{{ $step['title'] }}</h5>
            @if(!empty($step['description']))
            <p class="text-muted mb-0">{{ $step['description'] }}</p>
            @endif
          </div>
        </div>
        @endif
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!-- Achievements Section -->
  @if($landingPage->achievements_enabled && !empty($landingPage->metrics))
  <section class="landing-section section-achievements">
    <div class="container">
      <div class="text-center mb-5">
        @if($landingPage->achievements_subtitle)
        <div class="section-bill-subtitle">
          <span> {{ $landingPage->achievements_subtitle }} </span>
        </div>
        @endif
        <h2 class="section-title">
          {{ $landingPage->achievements_title ?: ($isRtl ? 'الإنجازات والأثر' : 'Achievements & Impact') }}
        </h2>
        @if($landingPage->achievements_description)
        <p class="section-subtitle">{{ $landingPage->achievements_description }}</p>
        @endif
      </div>

      <div class="row g-4 justify-content-center">
        @foreach($landingPage->metrics as $metric)
        @if(!empty($metric['number']) || !empty($metric['label']))
        <div class="col-6 col-md-4 col-lg-3">
          <div class="metric-card">
            @if(!empty($metric['icon']))
            <div class="metric-icon">
              <i class="ti {{ $metric['icon'] }}"></i>
            </div>
            @endif
            <div class="metric-number">{{ $metric['number'] ?? '' }}</div>
            <div class="metric-label">{{ $metric['label'] ?? '' }}</div>
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
  <section class="landing-section section-media">
    <div class="container">
      <div class="text-center mb-5">
        @if($landingPage->media_subtitle)
        <div class="section-bill-subtitle">
          <span> {{ $landingPage->media_subtitle }} </span>
        </div>
        @endif
        <h2 class="section-title">
          {{ $landingPage->media_title ?: ($isRtl ? 'الإعلام والتقدير' : 'Media & Recognition') }}
        </h2>
        @if($landingPage->media_description)
        <p class="section-subtitle">{{ $landingPage->media_description }}</p>
        @endif
      </div>

      <div class="row g-4">
        @foreach($articlesWithUrls as $article)
        @if(!empty($article['title']))
        <div class="col-md-6 col-lg-4">
          <div class="article-card">
            @if(!empty($article['image_url']))
            <img src="{{ $article['image_url'] }}" alt="{{ $article['title'] }}" class="article-image">
            @else
            <div class="article-image d-flex align-items-center justify-content-center">
              <i class="ti ti-news text-muted" style="font-size: 3rem;"></i>
            </div>
            @endif
            <div class="article-body">
              @if(!empty($article['publication_name']))
              <div class="article-publication">{{ $article['publication_name'] }}</div>
              @endif
              <h5 class="article-title">
                @if(!empty($article['link']))
                <a href="{{ $article['link'] }}" target="_blank" class="text-decoration-none text-dark">
                  {{ $article['title'] }}
                  <i class="ti ti-external-link ms-1" style="font-size: 0.8rem;"></i>
                </a>
                @else
                {{ $article['title'] }}
                @endif
              </h5>
              @if(!empty($article['summary']))
              <p class="article-summary">{{ $article['summary'] }}</p>
              @endif
              @if(!empty($article['date']))
              <div class="article-date">
                <i class="ti ti-calendar me-1"></i>
                {{ \Carbon\Carbon::parse($article['date'])->format('M d, Y') }}
              </div>
              @endif
            </div>
          </div>
        </div>
        @endif
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!-- Partners Section -->
  @if($landingPage->partners_enabled && !empty($partnersWithUrls))
  <section class="landing-section section-partners">
    <div class="container">
      <div class="text-center mb-5">
        @if($landingPage->partners_subtitle)
        <div class="section-bill-subtitle">
          <span> {{ $landingPage->partners_subtitle }} </span>
        </div>
        @endif
        <h2 class="section-title">
          {{ $landingPage->partners_title ?: ($isRtl ? 'شركاؤنا' : 'Our Partners') }}
        </h2>
        @if($landingPage->partners_description)
        <p class="section-subtitle">{{ $landingPage->partners_description }}</p>
        @endif
      </div>

      <div class="partners-grid">
        @foreach($partnersWithUrls as $partner)
        @if(!empty($partner['logo_url']) || !empty($partner['name']))
        <div class="text-center" title="{{ $partner['name'] ?? '' }}">
          @if(!empty($partner['logo_url']))
          <img src="{{ $partner['logo_url'] }}" alt="{{ $partner['name'] ?? 'Partner' }}" class="partner-logo">
          @else
          <div class="d-inline-flex align-items-center justify-content-center bg-light rounded px-4 py-3">
            <span class="fw-semibold text-muted">{{ $partner['name'] ?? '' }}</span>
          </div>
          @endif
        </div>
        @endif
        @endforeach
      </div>
    </div>
  </section>
  @endif

  <!-- Custom Sections -->
  @if(!empty($customSections))
  @foreach($customSections as $customSection)
  <section class="landing-section section-custom" style="{{ $customSection->getBackgroundStyle() }}">
    <div class="container">
      <div class="custom-section-content">
        <h2 class="section-title text-center mb-4">{{ $customSection->title }}</h2>
        <div class="custom-section-body">
          {!! $customSection->content !!}
        </div>
      </div>
    </div>
  </section>
  @endforeach
  @endif

  <!-- Footer -->
  <footer class="landing-footer">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 mb-4 mb-lg-0">
          <div class="d-flex align-items-center mb-3">
            @if($organization->img_url)
            <img src="{{ $organization->imageUrl }}"
              alt="{{ $isRtl ? $organization->name_ar : $organization->name_en }}" class="footer-logo me-3">
            @endif
            <div>
              <h5 class="footer-title text-white mb-0">{{ $isRtl ? $organization->name_ar : $organization->name_en }}
              </h5>
            </div>
          </div>
          @if($organization->bio)
          <p class="footer-description">{{ Str::limit($organization->bio, 150) }}</p>
          @endif
        </div>

        <div class="col-lg-4 mb-4 mb-lg-0">
          <h6 class="fw-bold mb-3 text-white">{{ $isRtl ? 'تواصل معنا' : 'Contact Us' }}</h6>
          <ul class="list-unstyled footer-links">
            @if($organization->contact_email)
            <li class="mb-2">
              <i class="ti ti-mail me-2"></i>
              <a href="mailto:{{ $organization->contact_email }}">{{ $organization->contact_email }}</a>
            </li>
            @endif
            @if($organization->contact_phone)
            <li class="mb-2">
              <i class="ti ti-phone me-2"></i>
              <a href="tel:{{ $organization->country_code }}{{ $organization->contact_phone }}">
                {{ $organization->country_code }} {{ $organization->contact_phone }}
              </a>
            </li>
            @endif
            @if($organization->address)
            <li class="mb-2">
              <i class="ti ti-map-pin me-2"></i>
              <span>{{ $organization->address }}</span>
            </li>
            @endif
          </ul>
        </div>

        <div class="col-lg-4">
          <h6 class="fw-bold text-white mb-3">{{ $isRtl ? 'تابعنا' : 'Follow Us' }}</h6>
          <div class="footer-social">
            @if($organization->linkedin_company_url)
            <a href="{{ $organization->linkedin_company_url }}" target="_blank" title="LinkedIn">
              <i class="ti ti-brand-linkedin"></i>
            </a>
            @endif
            @if($organization->twitter_handle)
            <a href="{{ $organization->twitter_handle }}" target="_blank" title="Twitter">
              <i class="ti ti-brand-twitter"></i>
            </a>
            @endif
            @if($organization->facebook_page_url)
            <a href="{{ $organization->facebook_page_url }}" target="_blank" title="Facebook">
              <i class="ti ti-brand-facebook"></i>
            </a>
            @endif
            @if($organization->instagram_profile_url)
            <a href="{{ $organization->instagram_profile_url }}" target="_blank" title="Instagram">
              <i class="ti ti-brand-instagram"></i>
            </a>
            @endif
            @if($organization->youtube_channel_url)
            <a href="{{ $organization->youtube_channel_url }}" target="_blank" title="YouTube">
              <i class="ti ti-brand-youtube"></i>
            </a>
            @endif
            @if($organization->tiktok_url)
            <a href="{{ $organization->tiktok_url }}" target="_blank" title="TikTok">
              <i class="ti ti-brand-tiktok"></i>
            </a>
            @endif
          </div>
        </div>
      </div>

      <div class="text-center footer-copyright">
        &copy; {{ date('Y') }} {{ $isRtl ? $organization->name_ar : $organization->name_en }}.
        {{ $isRtl ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
      </div>
    </div>
  </footer> --}}

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
  {{-- <script src="/vuexy/assets/js/front-page-landing.js"></script> --}}
</body>

</html>