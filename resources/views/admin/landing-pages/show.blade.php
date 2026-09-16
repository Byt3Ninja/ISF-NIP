<!DOCTYPE html>
<html lang="{{ $l }}" dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}" data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-theme="theme-default"
  data-assets-path="/vuexy/assets/" data-template="vertical-menu-template" data-style="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>{{ $page->title }}</title>
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

    html {
      scroll-behavior: smooth;
    }

    /* ---------- Nav ---------- */
    .gl-nav {
      background: rgba(3, 0, 20, 0.35);
      backdrop-filter: blur(14px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.12);
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1030;
    }

    .gl-nav .navbar-brand {
      cursor: pointer;
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
      padding-top: 76px;
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
    section[id],
    header[id] {
      scroll-margin-top: 76px;
    }

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

      .hero-image-wrapper {
        aspect-ratio: 4/3 !important;
        max-width: 400px;
        margin: 0 auto;
      }
    }

    @media (max-width:575.98px) {
      .hero-image-wrapper {
        aspect-ratio: 4/3 !important;
        max-width: 320px;
      }
    }

    /* ---- Section striping ---- */
    .section-py {
      padding: 5rem 0;
    }

    @media (max-width:991.98px) {
      .section-py { padding: 3.5rem 0; }
    }

    @media (max-width:575.98px) {
      .section-py { padding: 2.5rem 0; }
    }

    /* ---- Overflow protection ---- */
    body {
      overflow-wrap: anywhere;
      word-break: break-word;
    }

    .gl-nav .navbar-brand span.fw-bold {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 200px;
    }

    .gl-hero h1 {
      overflow-wrap: anywhere;
    }

    .section-head p.text-secondary {
      overflow-wrap: anywhere;
    }

    .gl-footer p {
      overflow-wrap: anywhere;
    }
  </style>

  <script src="/vuexy/assets/vendor/js/helpers.js"></script>
  <script src="/vuexy/assets/js/config.js"></script>
</head>

<body>

  <!-- ===== Hero ===== -->
  <header class="gl-hero" id="hero">

    <!-- ===== Nav ===== -->
    <nav class="navbar navbar-expand-lg gl-nav py-3">
      <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#hero">
        <span class="brand-mark">
          @if($page->logo_url)
            <img src="{{ $page->logo_url }}" alt="{{ $page->title }}">
          @endif
        </span>
        <span class="fw-bold text-white fs-5">{{ $page->title }}</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
          <i class="ti ti-menu-2 text-white fs-3"></i>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navMenu">
          <ul class="navbar-nav align-items-lg-center gap-lg-1">
            @if($page->about_is_enabled)
              <li class="nav-item"><a class="nav-link text-white" href="#about">{{ __('landing.about') }}</a></li>
            @endif
            @if($page->programs_is_enabled)
              <li class="nav-item"><a class="nav-link text-white" href="#programs">{{ __('landing.programs') }}</a></li>
            @endif
            @if($page->how_is_enabled)
              <li class="nav-item"><a class="nav-link text-white" href="#how-it-works">{{ __('landing.how_it_works') }}</a></li>
            @endif
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
            @if ($page->programs_is_enabled)
              <li class="nav-item mt-2 mt-lg-0">
                <a href="#programs" class="btn btn-primary rounded-pill">{{ __('landing.apply_now') }} </a>
              </li>
            @endif
          </ul>
        </div>
      </div>
    </nav>

    <div class="container position-relative"
      style="flex:1; display:flex; flex-direction:column; justify-content:center;">
      <div class="row align-items-center gy-5 gx-lg-5">
        <div class="col-lg-6">
          @php $heroTag = $l == 'ar' ? $page->hero_title_tag_ar : $page->hero_title_tag; @endphp
          @if($heroTag)
            <span class="hero-eyebrow"><span class="live-pulse"></span> {{ $heroTag }}</span>
          @endif
          @php $heroTitle = $l == 'ar' ? $page->hero_title_ar : $page->hero_title; @endphp
          @if($heroTitle)
            <h1 class="mt-4">{{ $heroTitle }}</h1>
          @endif
          @php $heroDescription = $l == 'ar' ? $page->hero_description_ar : $page->hero_description; @endphp
          @if($heroDescription)
            <p class="mt-4" style="font-size:1.08rem; line-height:1.7; max-width:460px; color: rgba(255,255,255,.78);">
              {{ $heroDescription }}
            </p>
          @endif
          <div class="d-flex flex-wrap gap-3 mt-5">
            @if($page->programs_is_enabled)
              <a href="#programs" class="btn btn-primary rounded-pill btn-lg">{{ __('landing.browse_open_programs') }} <i
                  class="ti ti-arrow-up-right ms-1"></i></a>
            @endif
            @if($page->how_is_enabled)
              <a href="#how-it-works" class="btn btn-outline-dark text-white rounded-pill btn-lg">{{ __('landing.see_how_it_works') }}</a>
            @endif
          </div>
        </div>

        <div class="col-lg-5 ms-auto d-flex align-items-center justify-content-end">
          <div class="hero-image-wrapper" style="width:100%; max-width:400px; aspect-ratio:1/1; border-radius:1rem; overflow:hidden; box-shadow:0 8px 32px rgba(0,0,0,.35);">
            <img src="{{ $page->hero_image_url ?: '' }}" alt="hero-image" style="width:100%; height:100%; object-fit:cover; display:block;">
          </div>
        </div>
      </div>
    </div>
  </header>

  @php $landingBg = false; @endphp

  <!-- ===== About / Value props ===== -->
  @if($page->about_is_enabled)
  <section class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }}" id="about">
    @php $landingBg = !$landingBg; @endphp
    <div class="container py-4">
      <div class="row section-head mb-4">
        <div class="col-lg-7">
          @php $aboutTag = $l == 'ar' ? $page->about_title_tag_ar : $page->about_title_tag; @endphp
          @if($aboutTag)
            <p class="eyebrow-mono mb-2">{{ $aboutTag }}</p>
          @endif
          @php $aboutTitle = $l == 'ar' ? $page->about_title_ar : $page->about_title; @endphp
          @if($aboutTitle)
            <h2>{{ $aboutTitle }}</h2>
          @endif
        </div>
        @php $aboutDescription = $l == 'ar' ? $page->about_description_ar : $page->about_description; @endphp
        @if($aboutDescription)
          <div class="col-lg-4 offset-lg-1 d-flex align-items-end">
            <p class="text-secondary mb-0">{{ $aboutDescription }}</p>
          </div>
        @endif
      </div>

      @if(!empty($page->about_cards))
        <div class="border-top">
          @foreach($page->about_cards as $index => $card)
            <div class="row value-item border-bottom py-6 g-3 align-items-center">
              <div class="col-auto"><span class="text-secondary">{{ sprintf('%02d', $index + 1) }}</span></div>
              <div class="col-lg-1 col-2">
                <div class="avatar">
                  <div class="avatar-initial bg-label-primary rounded">
                    @if(!empty($card['icon']))<i class="ti {{ $card['icon'] }}"></i>@endif
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-10">
                <h5 class="mb-0">{{ $l == 'ar' ? ($card['title_ar'] ?? '') : ($card['title'] ?? '') }}</h5>
              </div>
              <div class="col-lg-6">
                <p class="text-secondary mb-0 small">{{ $l == 'ar' ? ($card['description_ar'] ?? '') : ($card['description'] ?? '') }}</p>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </section>
  @endif

  <!-- ===== Programs ===== -->
  @if($page->programs_is_enabled)
  <section class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }}" id="programs">
    @php $landingBg = !$landingBg; @endphp
    <div class="container py-4">
      <div class="row section-head mb-4">
        <div class="col-lg-7">
          @php $programsTag = $l == 'ar' ? $page->programs_title_tag_ar : $page->programs_title_tag; @endphp
          @if($programsTag)
            <p class="eyebrow-mono mb-2">{{ $programsTag }}</p>
          @endif
          @php $programsTitle = $l == 'ar' ? $page->programs_title_ar : $page->programs_title; @endphp
          @if($programsTitle)
            <h2>{{ $programsTitle }}</h2>
          @endif
          @php $programsDescription = $l == 'ar' ? $page->programs_description_ar : $page->programs_description; @endphp
          @if($programsDescription)
            <p class="text-secondary mb-0 mt-3" style="max-width:560px;">{{ $programsDescription }}</p>
          @endif
        </div>
      </div>

      <ul class="nav nav-pills mb-4" role="tablist" id="programFilters">
        <li class="nav-item"><a class="nav-link active" href="#" data-filter="all">{{ __('landing.all_programs') }}</a></li>
        @foreach(\App\Cycle::programTypes() as $typeValue => $typeLabel)
          <li class="nav-item"><a class="nav-link" href="#" data-filter="{{ $typeValue }}">{{ $typeLabel }}</a></li>
        @endforeach
      </ul>

      <div class="row g-4">
        @forelse($visiblePrograms as $program)
          @php
            $programTitle = $l == 'ar' ? ($program->title_ar ?: $program->title) : $program->title;
            $programDescription = $l == 'ar' ? ($program->description_ar ?: $program->description) : $program->description;
          @endphp
          <div class="col-md-6 col-lg-4" data-type="{{ $program->type }}">
            <div class="card h-100 border shadow-none">
              <div class="card-body d-flex flex-column p-4">

                {{-- Logo + Type badge --}}
                @if($program->logo || $program->type)
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    @if($program->logo)
                      <div class="rounded-3 border bg-body-secondary d-flex align-items-center justify-content-center p-1"
                          style="width: 56px; height: 56px; flex-shrink: 0;">
                        <img src="{{ $program->logo }}" alt="{{ $programTitle }}"
                            style="width: 40px; height: 40px; object-fit: contain;" />
                      </div>
                    @endif
                    @if($program->type)
                      <span class="badge bg-label-secondary rounded-pill">
                        {{ \App\Cycle::programTypes()[$program->type] ?? __('cycles.' . $program->type) }}
                      </span>
                    @endif
                  </div>
                @endif

                {{-- Title --}}
                @if($programTitle)
                  <h5 class="fw-semibold mb-2" style="font-size: 16px; line-height: 1.35; overflow-wrap:break-word; word-break:break-word; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">{{ $programTitle }}</h5>
                @endif

                {{-- Description --}}
                @if($programDescription)
                  <p class="line-clamp-3 mb-0">{{ $programDescription }}</p>
                @endif

                <div class="mt-auto">
                  <hr class="opacity-25 my-3">

                  {{-- Program Duration --}}
                  @if($program->start)
                    <div class="d-flex align-items-center gap-2 mb-4">
                      <div class="avatar">
                        <div class="avatar-initial rounded"><i class="ti ti-calendar text-dark"></i></div>
                      </div>
                      <div>
                        <div class="small text-secondary">{{ __('admin.Program Duration') }}</div>
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

                  {{-- Application Status --}}
                  <div class="d-flex align-items-center gap-2 mb-6">
                    <div class="avatar">
                      <div class="avatar-initial rounded"><i class="ti ti-{{ $program->statusIcon }} text-dark"></i></div>
                    </div>
                    <div>
                      <div class="small text-secondary">{{ __('admin.application_status') }}</div>
                      <div class="fw-semibold">{{ $program->statusLabel }}</div>
                    </div>
                  </div>

                  {{-- CTA: real link only while open with a public application page --}}
                  @if($program->isOpen && $program->canApply)
                    <a href="{{ route('public.program_join', ['org_slug' => $program->organization->short_code, 'program_slug' => $program->public_slug]) }}"
                      class="btn btn-primary w-100 fw-semibold" style="border-radius: 10px; padding: 10px;">
                      {{ $program->btnText }}
                      <i class="ti ti-arrow-narrow-{{ $l == 'ar' ? 'left' : 'right' }} ms-2"></i>
                    </a>
                  @elseif($program->applyUnavailable)
                    <button class="btn btn-label-info w-100 fw-semibold" disabled>
                      <i class="ti ti-clock-hour-4 {{ $l == 'ar' ? 'ms-2' : 'me-2' }}"></i>{{ $program->btnText }}
                    </button>
                  @elseif($program->isUpcoming)
                    <button class="btn btn-label-warning w-100 fw-semibold" disabled>{{ $program->btnText }}</button>
                  @else
                    <button class="btn btn-label-secondary w-100 fw-semibold" disabled>{{ $program->btnText }}</button>
                  @endif
                </div>

              </div>
            </div>
          </div>
        @empty
          <div class="col-12 text-center py-6">
            <p class="text-secondary mb-0">{{ __('landing.No_Programs') }}</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>
  @endif

  <!-- ===== How it works ===== -->
  @if($page->how_is_enabled)
    <section class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }}" id="how-it-works">
      @php $landingBg = !$landingBg; @endphp
      <div class="container py-4">
        <div class="row section-head mb-5 text-center">
          <div class="col-lg-7 mx-auto">
            @php $howTag = $l == 'ar' ? $page->how_title_tag_ar : $page->how_title_tag; @endphp
            @if($howTag)
              <p class="eyebrow-mono mb-2">{{ $howTag }}</p>
            @endif
            @php $howTitle = $l == 'ar' ? $page->how_title_ar : $page->how_title; @endphp
            @if($howTitle)
              <h2>{{ $howTitle }}</h2>
            @endif
            @php $howDescription = $l == 'ar' ? $page->how_description_ar : $page->how_description; @endphp
            @if($howDescription)
              <p class="text-secondary mb-0 mt-3">{{ $howDescription }}</p>
            @endif
          </div>
        </div>

        <div class="row pipeline-wrap">
          <div class="pipeline-line d-none d-lg-block"></div>

          @foreach($page->how_steps ?? [] as $step)
            <div class="col-lg-4 pipeline-step text-center mb-5 mb-lg-0">
              <span class="pipeline-node">
                @if(!empty($step['icon']))<i class="ti {{ $step['icon'] }}"></i>@endif
              </span>
              <h5 class="fw-bold mt-3">{{ $l == 'ar' ? ($step['title_ar'] ?? '') : ($step['title'] ?? '') }}</h5>
              <p class="text-secondary small mx-auto" style="max-width:260px;">{{ $l == 'ar' ? ($step['description_ar'] ?? '') : ($step['description'] ?? '') }}</p>
            </div>
          @endforeach
        </div>
      </div>
    </section>
  @endif

  <!-- ===== Custom sections ===== -->
  @foreach($customSections as $customSection)
    <section class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }}" id="custom-{{ $customSection->id }}">
      @php $landingBg = !$landingBg; @endphp
      <div class="container py-4">
        @php $sectionTag = $l == 'ar' ? $customSection->title_tag_ar : $customSection->title_tag; @endphp
        @if($sectionTag)
          <p class="eyebrow-mono mb-2">{{ $sectionTag }}</p>
        @endif
        @php $sectionTitle = $l == 'ar' ? $customSection->title_ar : $customSection->title; @endphp
        @if($sectionTitle)
          <h2>{{ $sectionTitle }}</h2>
        @endif
        <div class="mt-3" style="white-space:pre-wrap;">{!! ($l == 'ar' ? $customSection->content_ar : $customSection->content) ?: $customSection->content !!}</div>
      </div>
    </section>
  @endforeach

  <!-- ===== CTA ===== -->
  <section class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }}">
    @php $landingBg = !$landingBg; @endphp
    <div class="container">
      <div class="gl-cta text-center">
        <p class="eyebrow-mono text-primary mb-3 position-relative">{{ __('landing.lets_get_started') }}</p>
        <h2 class="position-relative">{{ __('landing.your_next_program') }}</h2>
        @php $ctaDescription = $l == 'ar' ? $page->description_ar : $page->description; @endphp
        @if($ctaDescription)
          <p class="text-secondary mt-3 position-relative mx-auto" style="max-width:480px;">{{ $ctaDescription }}</p>
        @endif
        <a href="#programs" class="btn btn-primary mt-3 position-relative">{{ __('landing.find_my_program') }} <i
            class="ti ti-arrow-up-right ms-1"></i></a>
      </div>
    </div>
  </section>

  <!-- ===== Footer ===== -->
  <footer class="gl-footer py-5 mt-3">
    <div class="container py-3">
      <div class="row g-4">
        <div class="col-lg-4">
          <a class="d-flex align-items-center gap-2 mb-3" href="#hero">
            <span class="brand-mark"><img src="{{ $page->logo_url ?? '/images/growthlab.jpeg' }}" alt="Growth Labs logo"></span>
            <span class="fw-bold text-white fs-5">{{ $page->title ?? 'Growth Labs' }}</span>
          </a>
          <p class="text-secondary small" style="max-width:280px;">A curated path from founder to funded — accelerators,
            incubators, and hackathons, matched to your stage.</p>
          <div class="d-flex gap-2 mt-3">
            <a href="{{ $page->x ?: '#' }}" target="_blank" rel="noopener noreferrer"
              class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="X">
              <i class="ti ti-brand-x"></i>
            </a>
            <a href="{{ $page->linkedin ?: '#' }}" target="_blank" rel="noopener noreferrer"
              class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="LinkedIn">
              <i class="ti ti-brand-linkedin"></i>
            </a>
            <a href="{{ $page->instagram ?: '#' }}" target="_blank" rel="noopener noreferrer"
              class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="Instagram">
              <i class="ti ti-brand-instagram"></i>
            </a>
            <a href="{{ $page->youtube ?: '#' }}" target="_blank" rel="noopener noreferrer"
              class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="YouTube">
              <i class="ti ti-brand-youtube"></i>
            </a>
            <a href="{{ $page->tiktok ?: '#' }}" target="_blank" rel="noopener noreferrer"
              class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="TikTok">
              <i class="ti ti-brand-tiktok"></i>
            </a>
            <a href="{{ $page->facebook ?: '#' }}" target="_blank" rel="noopener noreferrer"
              class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="Facebook">
              <i class="ti ti-brand-facebook"></i>
            </a>
          </div>
        </div>
        <div class="col-6 col-lg-2 offset-lg-2">
          <h6 class="mb-3">{{ __('landing.footer_navigate') }}</h6>
          <div class="d-flex flex-column gap-2">
            <a href="#about" class="flink">{{ __('landing.about') }}</a>
            <a href="#programs" class="flink">{{ __('landing.programs') }}</a>
            <a href="#how-it-works" class="flink">{{ __('landing.footer_process') }}</a>
          </div>
        </div>
        <div class="col-6 col-lg-2">
          <h6 class="mb-3">{{ __('landing.programs') }}</h6>
          <div class="d-flex flex-column gap-2">
            <a href="#" class="flink">{{ __('landing.footer_accelerators') }}</a>
            <a href="#" class="flink">{{ __('landing.footer_incubators') }}</a>
            <a href="#" class="flink">{{ __('landing.footer_hackathons') }}</a>
          </div>
        </div>
        <div class="col-6 col-lg-2">
          <h6 class="mb-3">{{ __('landing.footer_company') }}</h6>
          <div class="d-flex flex-column gap-2">
            <a href="#" class="flink">{{ __('landing.footer_contact') }}</a>
            <a href="#" class="flink">{{ __('landing.footer_partners') }}</a>
            <a href="#" class="flink">{{ __('landing.footer_privacy') }}</a>
          </div>
        </div>
      </div>
      <hr class="border-secondary mt-5">
      <div class="d-flex flex-wrap justify-content-between gap-2">
        <p class="text-secondary small mb-0">© 2025 Growth Labs. {{ __('landing.footer_rights_reserved') }}</p>
        <p class="text-secondary small mb-0" style="letter-spacing: 0.01em">
          {{ __('landing.footer_built_for_founders') }}
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
