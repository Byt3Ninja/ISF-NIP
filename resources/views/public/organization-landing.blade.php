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

@php
 $sections = [
   ['id' => 'howItWorks', 'icon' => 'list', 'label' => __('admin.How It Works')],
   ['id' => 'achievements', 'icon' => 'trophy', 'label' => __('admin.Achievements & Impact')],
   ['id' => 'media', 'icon' => 'share', 'label' => __('admin.Media & Recognition')],
   ['id' => 'partners', 'icon' => 'users', 'label' => __('admin.Partners')],
  ];

  $colors = ['warning', 'success', 'danger', 'info', 'primary', 'secondary'];
@endphp

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

  <!-- Navigation Pills -->
  <div class="px-6">
    <div class="card border my-8 rounded-pill">
      <div class="card-body py-4 px-12 pb-2">
        <div class="nav-align-top">
          <ul class="nav nav-tabs flex-nowrap pb-4 border-0" style="max-width: 100%; overflow-x: auto;" role="tablist">
            @foreach ($sections as $tab)
              <li class="nav-item" role="presentation">
                <a
                  type="button"
                  class="nav-link {{ $loop->first ? 'active' : '' }}"
                  id="tab-{{ $tab['id'] }}-trigger"
                  href="#tab-{{ $tab['id'] }}"
                  role="tab"
                  aria-controls="tab-{{ $tab['id'] }}"
                  aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                >
                  <i class="ti ti-{{ $tab['icon'] }} me-1"></i>
                  {{ $tab['label'] }}
                </a>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  </div>
  <!--/ Navigation Pills -->

  <!-- How It Works Section -->
  @if($landingPage->how_it_works_enabled && !empty($landingPage->process_steps))
    <section id="tab-HowItWorks" class="section-py">
      <div class="container">
        @if($landingPage->how_it_works_subtitle)
          <div class="text-center mb-4">
            <span class="badge bg-label-primary">{{ $landingPage->how_it_works_subtitle }}</span>
          </div>
        @endif
        @if($landingPage->how_it_works_title)
          <h4 class="text-center mb-1">
            {{ $landingPage->how_it_works_title ?: ($isRtl ? 'كيف تعمل' : 'How It Works') }}
          </h4>
        @endif
        @if($landingPage->how_it_works_description)
          <p class="text-center mb-12">
            {{ $landingPage->how_it_works_description }}
          </p>
        @endif
        <div class="row g-6 justify-content-center">
          @foreach($landingPage->process_steps as $index => $step)
            <div class="col-lg-3 col-6">
              <div class="card h-100">
                <div class="card-body text-center">
                  <div class="badge rounded p-2 bg-label-{{ $colors[$index] }} mb-2">
                    @if(!empty($step['icon']))
                      <i class="ti {{ $step['icon'] }} ti-lg"></i>
                    @else
                      <span class="step-number">{{ $index + 1 }}</span>
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
  {{-- @if($landingPage->media_enabled && !empty($articlesWithUrls))
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
  @endif --}}

  <!-- Footer: Start -->
  <footer class="landing-footer bg-body footer-text">
    <div class="footer-top position-relative overflow-hidden z-1">
      <img src="/vuexy/assets/img/front-pages/backgrounds/footer-bg-light.png" alt="footer bg"
        class="footer-bg banner-bg-img z-n1" data-app-light-img="front-pages/backgrounds/footer-bg-light.png"
        data-app-dark-img="front-pages/backgrounds/footer-bg-dark.png" />
      <div class="container">
        <div class="row gx-0 gy-6 g-lg-10">
          <div class="col-lg-5">
            <a href="javascript:void(0);" class="app-brand-link mb-6">
              <img src="{{ $organization->image_url }}" alt="logo" class="app-brand-logo demo">
              <span class="app-brand-text demo footer-link fw-bold ms-2 ps-1">
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
          <div class="col-lg-2 col-md-4 col-sm-6">
            <h6 class="footer-title mb-6">{{ __('admin.quick_links') }}</h6>
            <ul class="navbar-nav list-unstyled">
              @foreach($sections as $section)
                <li class="nav-item mb-4">
                  <a href="#{{ $section['id'] }}" target="_blank" class="footer-link">{{ $section['label'] }}</a>
                </li>
              @endforeach
            </ul>
          </div>
          <div class="col-lg-2 col-md-4">
            <h6 class="footer-title mb-6">{{ __('admin.Contact us') }}</h6>
            <ul class="list-unstyled">
              @if($organization->contact_email)
                <li class="mb-2">
                  <i class="ti ti-mail me-2 text-white"></i>
                  <a class="footer-link" href="mailto:{{ $organization->contact_email }}">{{ $organization->contact_email }}</a>
                </li>
              @endif
              @if($organization->contact_phone)
                <li class="mb-2">
                  <i class="ti ti-phone me-2 text-white"></i>
                  <a class="footer-link" href="tel:{{ $organization->country_code }}{{ $organization->contact_phone }}">
                    {{ $organization->country_code }} {{ $organization->contact_phone }}
                  </a>
                </li>
              @endif
            </ul>
          </div>
          <div class="col-lg-3 col-md-4">
            <h6 class="footer-title mb-6">{{ __('admin.follow_us') }}</h6>
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
  {{-- <script src="/vuexy/assets/js/front-page-landing.js"></script> --}}
</body>

</html>