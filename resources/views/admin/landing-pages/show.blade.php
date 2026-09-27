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
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/custom-lp.css" />

  <script src="/vuexy/assets/vendor/js/helpers.js"></script>
  <script src="/vuexy/assets/js/config.js"></script>
</head>

<body>

  @php
    $lpProgramsCount = $visiblePrograms->count();
    $lpOpenCount = $visiblePrograms->filter(function ($program) {
        return $program->isOpen;
    })->count();
    $lpStepCount = count($page->how_steps ?? []);

    // Stats belong to a section only while that section is enabled: a disabled
    // Programs / How It Works section must not leak its numbers into the hero.
    $lpShowProgramStats = $page->programs_is_enabled && $lpProgramsCount > 0;
    $lpShowStepStat = $showHow && $lpStepCount > 0;
  @endphp

  <!-- ===== Hero ===== -->
  <header class="gl-hero" id="hero">
    <span class="hero-gradient" aria-hidden="true"></span>
    <span class="hero-grid" aria-hidden="true"></span>

    <!-- ===== Nav ===== -->
    <nav class="navbar navbar-expand-lg gl-nav py-3">
      <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#hero">
          <span class="brand-mark">
            @if($page->logo_url)
              <img src="{{ $page->logo_url }}" alt="{{ $page->title }}">
            @else
              <span class="brand-mark-placeholder"></span>
            @endif
          </span>
          <span class="brand-title">{{ $page->title }}</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu"
          aria-label="Toggle navigation">
          <i class="ti ti-menu-2 text-white fs-3"></i>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navMenu">
          <ul class="navbar-nav align-items-lg-center gap-lg-1">
            @if($page->about_is_enabled)
              <li class="nav-item"><a class="nav-link" href="#about">{{ __('landing.about') }}</a></li>
            @endif
            @if($page->programs_is_enabled)
              <li class="nav-item"><a class="nav-link" href="#programs">{{ __('landing.programs') }}</a></li>
            @endif
            @if($showHow)
              <li class="nav-item"><a class="nav-link"
                  href="#how-it-works">{{ __('landing.how_it_works') }}</a></li>
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
                <a href="#programs" class="btn hero-nav-cta">{{ __('landing.apply_now') }} </a>
              </li>
            @endif
          </ul>
        </div>
      </div>
    </nav>

    <div class="gl-hero__inner container position-relative">
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
            <p class="mt-4 hero-desc">
              {{ $heroDescription }}
            </p>
          @endif
          <div class="d-flex flex-wrap gap-3 mt-5">
            @if($page->programs_is_enabled)
              <a href="#programs" class="btn hero-primary-cta btn-lg">{{ __('landing.browse_open_programs') }} <i
                  class="ti ti-arrow-up-right ms-1"></i></a>
            @endif
            {{-- @if($showHow)
              <a href="#how-it-works"
                class="btn btn-outline-light rounded-pill btn-lg">{{ __('landing.see_how_it_works') }}</a>
            @endif --}}
          </div>

          @if($lpShowProgramStats || $lpShowStepStat)
            <div class="hero-stats">
              @if($lpShowProgramStats)
                <div class="hero-stat">
                  <i class="ti ti-layout-grid text-primary" aria-hidden="true"></i>
                  <div>
                    <strong>{{ $lpProgramsCount }}</strong>
                    <span>{{ __('landing.all_programs') }}</span>
                  </div>
                </div>
                <div class="hero-stat">
                  <i class="ti ti-badge text-primary" aria-hidden="true"></i>
                  <div>
                    <strong>{{ $lpOpenCount }}</strong>
                    <span>{{ __('landing.open_programs') }}</span>
                  </div>
                </div>
              @endif
              @if($lpShowStepStat)
                <div class="hero-stat">
                  <i class="ti ti-route-2 text-primary" aria-hidden="true"></i>
                  <div>
                    <strong>{{ $lpStepCount }}</strong>
                    <span>{{ __('landing.how_it_works') }}</span>
                  </div>
                </div>
              @endif
            </div>
          @endif
        </div>

        <div class="col-lg-5 ms-auto d-flex align-items-center justify-content-center justify-content-lg-end">
          <div class="hero-visual" aria-label="{{ $heroTitle ?? $page->title }}">
            <div class="hero-image-wrapper">
              @if($page->hero_image_url)
                <img src="{{ $page->hero_image_url }}" alt="hero-image" class="hero-img">
              @else
                <span class="hero-image-placeholder">image</span>
              @endif
            </div>
            <span class="hero-chip hero-chip--one"><i class="ti ti-square-rounded"></i></span>
            <span class="hero-chip hero-chip--two"><i class="ti ti-circle"></i></span>
            <span class="hero-chip hero-chip--three"><i class="ti ti-diamond"></i></span>
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
        <div class="row section-head mb-5 text-center">
          <div class="col-lg-7 mx-auto">
            @php $aboutTag = $l == 'ar' ? $page->about_title_tag_ar : $page->about_title_tag; @endphp
            @if($aboutTag)
              <p class="eyebrow-mono mb-2">{{ $aboutTag }}</p>
            @endif
            @php $aboutTitle = $l == 'ar' ? $page->about_title_ar : $page->about_title; @endphp
            @if($aboutTitle)
              <h2>{{ $aboutTitle }}</h2>
            @endif
            @php $aboutDescription = $l == 'ar' ? $page->about_description_ar : $page->about_description; @endphp
            @if($aboutDescription)
              <div class="mt-3">
                <p class="text-secondary mb-0 mx-auto" style="max-width:620px;">{{ $aboutDescription }}</p>
              </div>
            @endif
          </div>
        </div>

        @if(!empty($page->about_cards))
          <div class="row g-4">
            @foreach($page->about_cards as $index => $card)
              @php
                $cardTitle = $l == 'ar' ? ($card['title_ar'] ?? '') : ($card['title'] ?? '');
                $cardDesc = $l == 'ar' ? ($card['description_ar'] ?? '') : ($card['description'] ?? '');
              @endphp
              @if($cardTitle || $cardDesc)
                <div class="col-md-6 col-xl-4">
                  <div class="card h-100 border value-card">
                    <div class="card-body position-relative p-4">
                      {{-- <span class="value-watermark" aria-hidden="true">{{ sprintf('%02d', $index + 1) }}</span> --}}
                      <div class="avatar avatar-lg mb-3">
                        <div class="avatar-initial bg-label-primary rounded">
                          @if(!empty($card['icon']))<i class="ti {{ $card['icon'] }}"></i>@endif
                        </div>
                      </div>
                      @if($cardTitle)
                        <h5 class="mb-2">{{ $cardTitle }}</h5>
                      @endif
                      @if($cardDesc)
                        <p class="text-secondary small mb-0">{{ $cardDesc }}</p>
                      @endif
                    </div>
                  </div>
                </div>
              @endif
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
        <div class="row section-head mb-5 text-center">
          <div class="col-lg-7 mx-auto">
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
              <p class="text-secondary mb-0 mt-3 mx-auto" style="max-width:560px;">{{ $programsDescription }}</p>
            @endif
          </div>
        </div>

        @php
          $typePalette = ['primary', 'info', 'success', 'warning', 'secondary'];

          $openPrograms = $visiblePrograms->filter(function ($program) {
              return $program->isOpen;
          });
          $pastPrograms = $visiblePrograms->reject(function ($program) {
              return $program->isOpen;
          });

          $programGroups = [
              [
                  'label' => $l == 'ar' ? 'مفتوح للتقديم' : 'Open for application',
                  'icon' => 'ti-badge',
                  'items' => $openPrograms,
              ],
              [
                  'label' => $l == 'ar' ? 'البرامج السابقة' : 'Past Programs',
                  'icon' => 'ti-history',
                  'items' => $pastPrograms,
              ],
          ];

          // A single group is the whole section, so its heading is redundant noise.
          $showProgramGroupTitles = collect($programGroups)
            ->filter(fn ($group) => $group['items']->count() > 0)
            ->count() > 1;
        @endphp

        @if($lpProgramsCount === 0)
          <div class="text-center py-6">
            <p class="text-secondary mb-0">{{ __('landing.No_Programs') }}</p>
          </div>
        @endif

        @foreach($programGroups as $programGroup)
          @if($programGroup['items']->count() > 0)
            <div class="programs-group">
              @if($showProgramGroupTitles)
                <h3 class="programs-group-title">
                  <span class="programs-group-ic"><i class="ti {{ $programGroup['icon'] }}" aria-hidden="true"></i></span>
                  <span>{{ $programGroup['label'] }}</span>
                  <span class="programs-group-count">{{ $programGroup['items']->count() }}</span>
                </h3>
              @endif

              <div class="row g-4">
                @foreach($programGroup['items'] as $program)
                  @php
                    $programTitle = $l == 'ar' ? ($program->title_ar ?: $program->title) : $program->title;
                    $programDescription = $l == 'ar' ? ($program->description_ar ?: $program->description) : $program->description;
                    $typeColor = $typePalette[abs(crc32((string) $program->type)) % count($typePalette)];
                    $statusColor = $program->isOpen && $program->canApply
                        ? 'success'
                        : ($program->applyUnavailable
                            ? 'info'
                            : ($program->isUpcoming ? 'warning' : 'secondary'));
                  @endphp
                  <div class="col-md-6 col-lg-4"
                    style="--program-accent-rgb: var(--bs-{{ $typeColor }}-rgb);">
                    <div class="card h-100 border shadow-none program-card">
                      <div class="card-body d-flex flex-column p-4">

                        {{-- Logo + Type badge --}}
                        @if($program->logo || $program->type)
                          <div class="d-flex justify-content-between align-items-start mb-3">
                            @if($program->logo)
                              <div class="program-logo rounded-3 border bg-body-secondary d-flex align-items-center justify-content-center p-1"
                                style="width: 56px; height: 56px; flex-shrink: 0;">
                                <img src="{{ $program->logo }}" alt="{{ $programTitle }}"
                                  style="width: 40px; height: 40px; object-fit: contain;" />
                              </div>
                            @else
                              <div class="program-logo d-flex align-items-center justify-content-center rounded-3 border bg-lighter"
                                style="width: 56px; height: 56px; flex-shrink: 0;">
                                <i class="ti ti-rocket fs-4 text-primary" aria-hidden="true"></i>
                              </div>
                            @endif
                            @if($program->type)
                              <span class="badge bg-label-{{ $typeColor }} rounded-pill">
                                {{ \App\Cycle::programTypes()[$program->type] ?? __('cycles.' . $program->type) }}
                              </span>
                            @endif
                          </div>
                        @endif

                  {{-- Title --}}
                  @if($programTitle)
                    <h5 class="fw-semibold mb-2 program-title line-clamp-2">{{ $programTitle }}</h5>
                  @endif

                  {{-- Description --}}
                  @if($programDescription)
                    <p class="line-clamp-3 mb-0">{{ $programDescription }}</p>
                  @endif

                  <div class="mt-auto">
                    <div class="program-divider" aria-hidden="true"></div>

                    {{-- Duration + Status --}}
                    <div class="program-meta mb-5">
                      @if($program->start)
                        <div class="d-flex align-items-center gap-2 mb-4">
                          <div class="avatar">
                            <div class="avatar-initial rounded bg-lighter"><i class="ti ti-calendar text-dark"></i></div>
                          </div>
                          <div class="min-w-0">
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

                      <div class="d-flex align-items-center gap-2">
                        <div class="avatar">
                          <div class="avatar-initial rounded bg-lighter"><i
                              class="ti ti-{{ $program->statusIcon }} text-dark"></i></div>
                        </div>
                        <div class="min-w-0">
                          <div class="small text-secondary">{{ __('admin.application_status') }}</div>
                          <div class="fw-semibold d-flex align-items-center gap-2">
                            <span class="status-dot bg-{{ $statusColor }}"></span>
                            <span class="text-{{ $statusColor }}">{{ $program->statusLabel }}</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    {{-- CTA: real link only while open with a public application page --}}
                    @if($program->isOpen && $program->canApply)
                      <a href="{{ route('public.program_join', ['org_slug' => $program->organization->short_code, 'program_slug' => $program->public_slug]) }}"
                        class="btn btn-primary w-100 fw-semibold" style="border-radius: 12px; padding: 10px;">
                        {{ $program->btnText }}
                        <i class="ti ti-arrow-narrow-{{ $l == 'ar' ? 'left' : 'right' }} ms-2"></i>
                      </a>
                    @elseif($program->applyUnavailable)
                      <button class="btn btn-label-info w-100 fw-semibold" disabled style="border-radius: 12px;">
                        <i class="ti ti-clock-hour-4 {{ $l == 'ar' ? 'ms-2' : 'me-2' }}"></i>{{ $program->btnText }}
                      </button>
                    @elseif($program->isUpcoming)
                      <button class="btn btn-label-warning w-100 fw-semibold" disabled
                        style="border-radius: 12px;">{{ $program->btnText }}</button>
                    @else
                      <button class="btn btn-label-secondary w-100 fw-semibold" disabled
                        style="border-radius: 12px;">{{ $program->btnText }}</button>
                    @endif
                  </div>

                </div>
              </div>
            </div>
                  @endforeach
                </div>
              </div>
            @endif
          @endforeach
      </div>
    </section>
  @endif

  <!-- ===== How it works ===== -->
  @if($showHow)
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
              <p class="text-secondary mb-0 mt-3 mx-auto" style="max-width:600px;">{{ $howDescription }}</p>
            @endif
          </div>
        </div>

        @php $howStepsCount = count($page->how_steps ?? []); @endphp

        @if($howStepsCount > 0)
          <div class="row pipeline-wrap">
            <div class="pipeline-line d-none d-lg-block"></div>

            @foreach($page->how_steps ?? [] as $index => $step)
              <div class="col-lg-4 pipeline-step text-center mb-5 mb-lg-0">
                <span class="pipeline-node-wrap">
                  <span class="pipeline-node">
                    @if(!empty($step['icon']))<i class="ti {{ $step['icon'] }}"></i>@endif
                  </span>
                  <span class="pipeline-num" aria-hidden="true">{{ sprintf('%02d', $index + 1) }}</span>
                </span>
                <h5 class="fw-bold mt-4">{{ $l == 'ar' ? ($step['title_ar'] ?? '') : ($step['title'] ?? '') }}</h5>
                <p class="text-secondary small mx-auto" style="max-width:260px;">
                  {{ $l == 'ar' ? ($step['description_ar'] ?? '') : ($step['description'] ?? '') }}</p>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </section>
  @endif

  <!-- ===== Custom sections ===== -->
  @foreach($customSections as $customSection)
    <section class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }}" id="custom-{{ $customSection->id }}">
      @php $landingBg = !$landingBg; @endphp
      <div class="container section-head py-4 text-center">
        @php $sectionTag = $l == 'ar' ? $customSection->title_tag_ar : $customSection->title_tag; @endphp
        @if($sectionTag)
          <p class="eyebrow-mono mb-2">{{ $sectionTag }}</p>
        @endif
        @php $sectionTitle = $l == 'ar' ? $customSection->title_ar : $customSection->title; @endphp
        @if($sectionTitle)
          <h2>{{ $sectionTitle }}</h2>
        @endif
        <div class="mt-3 lp-rich" style="white-space:pre-wrap;">
          {!! ($l == 'ar' ? $customSection->content_ar : $customSection->content) ?: $customSection->content !!}</div>
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
        <div class="d-flex flex-wrap gap-3 justify-content-center mt-4 position-relative">
          @if($page->programs_is_enabled)
            <a href="#programs" class="btn btn-primary rounded-pill btn-lg">{{ __('landing.find_my_program') }} <i
                class="ti ti-arrow-up-right ms-1"></i></a>
          @endif
          @if($showHow)
            <a href="#how-it-works"
              class="btn btn-outline-light rounded-pill btn-lg">{{ __('landing.see_how_it_works') }}</a>
          @endif
        </div>
      </div>
    </div>
  </section>

  <!-- ===== Footer ===== -->
  <footer class="gl-footer py-5">
    <div class="container py-3">
      <div class="row g-4">
        <div class="col-lg-4">
          <a class="d-flex align-items-center gap-2 mb-3" href="#hero">
            <span class="brand-mark"><img src="{{ $page->logo_url ?? '/images/growthlab.jpeg' }}"
                alt="Growth Labs logo"></span>
            <span class="fw-bold text-white fs-5">{{ $page->title ?? 'Growth Labs' }}</span>
          </a>
          @php $footerDescription = $l == 'ar' ? $page->description_ar : $page->description; @endphp
          <p class="text-secondary small" style="max-width:280px;">
            {{ $footerDescription }}
          </p>
          <div class="d-flex gap-2 mt-3">
            @if ($page->x)
              <a href="{{ $page->x ?: '#' }}" target="_blank" rel="noopener noreferrer"
                class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="X">
                <i class="ti ti-brand-x"></i>
              </a>
            @endif
            @if ($page->linkedin)
              <a href="{{ $page->linkedin ?: '#' }}" target="_blank" rel="noopener noreferrer"
                class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="LinkedIn">
                <i class="ti ti-brand-linkedin"></i>
              </a>
            @endif
            @if ($page->instagram)
              <a href="{{ $page->instagram ?: '#' }}" target="_blank" rel="noopener noreferrer"
                class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="Instagram">
                <i class="ti ti-brand-instagram"></i>
              </a>
            @endif
            @if ($page->youtube)
              <a href="{{ $page->youtube ?: '#' }}" target="_blank" rel="noopener noreferrer"
                class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="YouTube">
                <i class="ti ti-brand-youtube"></i>
              </a>
            @endif
            @if ($page->tiktok)
              <a href="{{ $page->tiktok ?: '#' }}" target="_blank" rel="noopener noreferrer"
                class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="TikTok">
                <i class="ti ti-brand-tiktok"></i>
              </a>
            @endif
            @if ($page->facebook)
              <a href="{{ $page->facebook ?: '#' }}" target="_blank" rel="noopener noreferrer"
                class="btn btn-icon btn-text-primary waves-effect waves-light rounded-pill" aria-label="Facebook">
                <i class="ti ti-brand-facebook"></i>
              </a>
            @endif
          </div>
        </div>
        {{-- Without the Programs column the row is only two columns wide, so the
             offset grows to keep Quick Links aligned to the right as in the design. --}}
        <div class="col-6 col-lg-3 {{ $page->programs_is_enabled ? 'offset-lg-2' : 'offset-lg-5' }}">
          <h6 class="mb-3">{{ __('landing.footer_quick_links') }}</h6>
          <div class="d-flex flex-column gap-2">
            @php
              $footerQuickLinks = collect();
              if ($page->about_is_enabled) {
                $footerQuickLinks->push(['label' => __('landing.about'), 'url' => '#about']);
              }
              if ($page->programs_is_enabled) {
                $footerQuickLinks->push(['label' => __('landing.programs'), 'url' => '#programs']);
              }
              if ($showHow) {
                $footerQuickLinks->push(['label' => __('landing.how_it_works'), 'url' => '#how-it-works']);
              }
              foreach ($customSections as $footerSection) {
                $footerSectionLabel = $l == 'ar'
                  ? ($footerSection->title_ar ?: $footerSection->title)
                  : ($footerSection->title ?: $footerSection->title_ar);
                $footerSectionLabel = trim(strip_tags((string) $footerSectionLabel));
                if ($footerSectionLabel === '') {
                  $footerSectionLabel = __('landing.custom_section');
                }
                $footerQuickLinks->push(['label' => $footerSectionLabel, 'url' => '#custom-' . $footerSection->id]);
              }
              $footerQuickLinks = $footerQuickLinks->take(5);
            @endphp
            @foreach($footerQuickLinks as $footerLink)
              <a href="{{ $footerLink['url'] }}" class="flink">{{ $footerLink['label'] }}</a>
            @endforeach
          </div>
        </div>
        @if($page->programs_is_enabled)
        <div class="col-6 col-lg-3">
          <h6 class="mb-3">{{ __('landing.footer_program_links') }}</h6>
          <div class="d-flex flex-column gap-2">
            @php $footerPrograms = $visiblePrograms->sortByDesc('created_at')->take(5); @endphp
            @forelse($footerPrograms as $footerProgram)
              @php
                $footerProgramTitle = $l == 'ar'
                  ? ($footerProgram->title_ar ?: $footerProgram->title)
                  : ($footerProgram->title ?: $footerProgram->title_ar);
                $footerProgramUrl = ($footerProgram->public_slug && $footerProgram->organization && $footerProgram->organization->short_code)
                  ? route('public.program_join', ['org_slug' => $footerProgram->organization->short_code, 'program_slug' => $footerProgram->public_slug])
                  : '#programs';
              @endphp
              <a href="{{ $footerProgramUrl }}" class="flink" target="_blank"
                rel="noopener noreferrer">{{ $footerProgramTitle }}</a>
            @empty
              <span class="text-secondary small">{{ __('landing.No_Programs') }}</span>
            @endforelse
          </div>
        </div>
        @endif
      </div>

      <hr class="border-secondary my-5">
      <div class="text-center">
        <p class="text-secondary small mb-0">© {{ date('Y') }} {{ __('landing.footer_rights_reserved') }}</p>
      </div>
    </div>
  </footer>

  <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
  <script src="/vuexy/assets/vendor/libs/popper/popper.js"></script>
  <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
  <script src="/vuexy/assets/js/main.js"></script>

  <script>
    (function () {
      var glNav = document.querySelector('.gl-nav');
      function toggleNavState() {
        if (glNav) glNav.classList.toggle('scrolled', window.scrollY > 10);
      }
      window.addEventListener('scroll', toggleNavState, { passive: true });
      toggleNavState();

      var spyLinks = Array.prototype.slice.call(
        document.querySelectorAll('.gl-nav .nav-link[href^="#"]:not(.btn)')
      );
      var spyTargets = [];
      spyLinks.forEach(function (link) {
        var el = document.querySelector(link.getAttribute('href'));
        if (el) {
          el._spyLink = link;
          spyTargets.push(el);
        }
      });
      if ('IntersectionObserver' in window && spyTargets.length) {
        var spy = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (!entry.isIntersecting || !entry.target._spyLink) return;
            spyLinks.forEach(function (l) { l.classList.remove('active'); });
            entry.target._spyLink.classList.add('active');
          });
        }, { rootMargin: '-45% 0px -50% 0px' });
        spyTargets.forEach(function (t) { spy.observe(t); });
      }
    })();

  </script>
</body>

</html>
