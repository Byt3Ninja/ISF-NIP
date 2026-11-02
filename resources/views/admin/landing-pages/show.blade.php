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
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/custom-lp.css?v=20261019" />

  @php
    // ── Branding (Custom Sections tab) ────────────────────────────
    // Saved colors override the hero gradient and the primary buttons.
    // Fallbacks mirror the field defaults in the admin form; anything
    // missing or malformed falls back the same way.
    $lpHex = function ($value, $fallback) {
      $hex = ltrim(trim((string) $value), '#');
      if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
      }
      return preg_match('/^[0-9a-fA-F]{6}$/', $hex) ? '#' . strtolower($hex) : $fallback;
    };
    $lpRgb = function ($hex) {
      $h = ltrim($hex, '#');
      return hexdec(substr($h, 0, 2)) . ', ' . hexdec(substr($h, 2, 2)) . ', ' . hexdec(substr($h, 4, 2));
    };
    $lpShade = function ($hex, $factor) {
      $h = ltrim($hex, '#');
      $out = '#';
      for ($i = 0; $i < 6; $i += 2) {
        $v = (int) round(hexdec(substr($h, $i, 2)) * $factor);
        $out .= str_pad(dechex(max(0, min(255, $v))), 2, '0', STR_PAD_LEFT);
      }
      return $out;
    };

    $lpGradientStart  = $lpHex($page->branding_gradient_start, '#030014');
    $lpGradientMiddle = $lpHex($page->branding_gradient_middle, '#0b0530');
    $lpGradientEnd    = $lpHex($page->branding_gradient_end, '#2a1139');
    $lpButtonBg       = $lpHex($page->branding_button_bg, '#7367f0');
    $lpButtonText     = $lpHex($page->branding_button_text, '#ffffff');
    $lpButtonBgDark   = $lpShade($lpButtonBg, 0.85);
  @endphp

  {{-- Branding overrides sit after custom-lp.css so they win on equal
       specificity; the theme's `!important` hover rules are matched here. --}}
  <style>
    :root {
      --lp-branding-gradient-start: {{ $lpGradientStart }};
      --lp-branding-gradient-middle: {{ $lpGradientMiddle }};
      --lp-branding-gradient-end: {{ $lpGradientEnd }};
      --lp-branding-button-bg: {{ $lpButtonBg }};
      --lp-branding-button-text: {{ $lpButtonText }};
      --lp-branding-button-bg-dark: {{ $lpButtonBgDark }};
    }

    /* Hero backdrop: the three saved stops at 0% / 55% / 100%. */
    .hero-gradient {
      background: linear-gradient(
        170deg,
        var(--lp-branding-gradient-start) 0%,
        var(--lp-branding-gradient-middle) 55%,
        var(--lp-branding-gradient-end) 100%
      ) !important;
    }

    /* Hero CTAs (nav + hero) and every primary button follow the saved
       background / text pair. */
    .hero-nav-cta,
    .hero-primary-cta {
      background: var(--lp-branding-button-bg) !important;
      color: var(--lp-branding-button-text) !important;
      box-shadow: 0 10px 30px rgba({{ $lpRgb($lpButtonBg) }}, 0.4) !important;
    }

    .hero-nav-cta:hover,
    .hero-primary-cta:hover {
      background: var(--lp-branding-button-bg-dark) !important;
      color: var(--lp-branding-button-text) !important;
      box-shadow: 0 18px 46px rgba({{ $lpRgb($lpButtonBg) }}, 0.5) !important;
    }

    .btn-primary:not(:disabled):not(.disabled) {
      background-color: var(--lp-branding-button-bg) !important;
      border-color: var(--lp-branding-button-bg) !important;
      color: var(--lp-branding-button-text) !important;
      box-shadow: 0 0.125rem 0.375rem 0 rgba({{ $lpRgb($lpButtonBg) }}, 0.3) !important;
    }

    .btn-primary:hover,
    .btn-check:focus + .btn-primary,
    .btn-primary:focus,
    .btn-primary.focus,
    .btn-check:checked + .btn-primary,
    .btn-primary:active,
    .btn-primary.active,
    .btn-primary.show.dropdown-toggle,
    .show > .btn-primary.active.dropdown-toggle {
      background-color: var(--lp-branding-button-bg-dark) !important;
      border-color: var(--lp-branding-button-bg-dark) !important;
      color: var(--lp-branding-button-text) !important;
    }
  </style>

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
    // (or empty) Programs / How It Works section must not leak its numbers into
    // the hero.
    $lpShowProgramStats = $showPrograms && $lpProgramsCount > 0;
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
            @if($showAbout)
              @php
                $navAbout = $l == 'ar'
                  ? ($page->about_nav_title_ar ?? $page->about_title_ar ?? null)
                  : ($page->about_nav_title ?? $page->about_title ?? null);
                if (!$navAbout) {
                    $navAbout = __('landing.about');
                }
              @endphp
              <li class="nav-item"><a class="nav-link" href="#about">{{ \Illuminate\Support\Str::limit($navAbout, 15) }}</a></li>
            @endif
            @if($showPrograms)
              @php
                $navPrograms = $l == 'ar'
                  ? ($page->programs_nav_title_ar ?? $page->programs_title_ar ?? null)
                  : ($page->programs_nav_title ?? $page->programs_title ?? null);
                if (!$navPrograms) {
                    $navPrograms = __('landing.programs');
                }
              @endphp
              <li class="nav-item"><a class="nav-link" href="#programs">{{ \Illuminate\Support\Str::limit($navPrograms, 15) }}</a></li>
            @endif
            @if($showHow)
              @php
                $navHow = $l == 'ar'
                  ? ($page->how_nav_title_ar ?? ($page->how_title_ar ?? null))
                  : ($page->how_nav_title ?? ($page->how_title ?? null));
                if (!$navHow) {
                    $navHow = __('landing.how_it_works');
                }
              @endphp
              <li class="nav-item"><a class="nav-link"
                  href="#how-it-works">{{ \Illuminate\Support\Str::limit($navHow, 15) }}</a></li>
            @endif
            @if(!empty($page->custom_sections) && is_array($page->custom_sections))
              @foreach($page->custom_sections as $index => $section)
                @php
                  $sectionVisible = $section['is_enabled'] ?? false;
                  $slug = $section['slug'] ?? ('custom-section-' . ($index + 1));
                  $navTitle = $l == 'ar'
                    ? ($section['nav_title_ar'] ?? ($section['title_ar'] ?? null))
                    : ($section['nav_title'] ?? ($section['title'] ?? null));
                @endphp
                @if($sectionVisible && $navTitle)
                  <li class="nav-item"><a class="nav-link"
                      href="#{{ $slug }}">{{ \Illuminate\Support\Str::limit($navTitle, 15) }}</a></li>
                @endif
              @endforeach
            @endif
            @if(!empty($customSections))
              @foreach($customSections as $customSection)
                @php
                  $sectionVisible = $customSection->is_enabled ?? $customSection->enabled ?? true;
                  $navTitle = $l == 'ar'
                    ? ($customSection->nav_title_ar ?? ($customSection->title_ar ?? null))
                    : ($customSection->nav_title ?? ($customSection->title ?? null));
                  $slug = $customSection->slug ?? ('custom-' . $customSection->id);
                @endphp
                @if($sectionVisible && $navTitle)
                  <li class="nav-item"><a class="nav-link"
                      href="#{{ $slug }}">{{ \Illuminate\Support\Str::limit($navTitle, 15) }}</a></li>
                @endif
              @endforeach
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
            @if($showPrograms)
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
          {{-- Primary Action: the CTA text comes from the Hero tab's
               configuration (per language), and an empty CTA Link falls
               back to jumping to this page's Programs section. --}}
          @php
            $heroCtaText = $l == 'ar'
              ? ($page->hero_cta_text_ar ?: __('landing.browse_open_programs'))
              : ($page->hero_cta_text ?: __('landing.browse_open_programs'));
            $heroCtaLink = $page->hero_cta_link ?: '#programs';
          @endphp
          <div class="d-flex flex-wrap gap-3 mt-5">
            @if($page->hero_cta_link || $showPrograms)
              <a href="{{ $heroCtaLink }}" class="btn hero-primary-cta btn-lg">{{ $heroCtaText }} <i
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
          <div class="hero-visual mt-6" aria-label="{{ $heroTitle ?? $page->title }}">
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
  @if($showAbout)
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
          @php
            // Count cards that will actually render (have a title or description)
            // so a row with only 1-2 items can be centered.
            $lpVisibleAboutCards = collect($page->about_cards)->filter(function ($card) use ($l) {
                $title = $l == 'ar' ? ($card['title_ar'] ?? '') : ($card['title'] ?? '');
                $desc = $l == 'ar' ? ($card['description_ar'] ?? '') : ($card['description'] ?? '');
                return $title || $desc;
            });
          @endphp
          <div class="row g-4 {{ $lpVisibleAboutCards->count() <= 2 ? 'justify-content-center' : '' }}">
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
                        <p class="text-secondary mb-0">{{ $cardDesc }}</p>
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
  @if($showPrograms)
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
                  'label' => __('landing.past_programs'),
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
                    <p class="line-clamp-3 mb-2">{{ $programDescription }}</p>
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
          <div class="row pipeline-wrap {{ $howStepsCount <= 2 ? 'justify-content-center' : '' }}">
            @if($howStepsCount > 1)
              <div class="pipeline-line d-none d-lg-block"></div>
            @endif

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
        @php $customSectionContent = ($l == 'ar' ? $customSection->content_ar : $customSection->content) ?: $customSection->content; @endphp
        <div class="mt-3 lp-rich lp-rich-clamp" data-lp-clamp style="white-space:pre-wrap;">
          {!! preg_replace('/(<\/(?:ul|ol)>)/i', '$1<br />', $customSectionContent) !!}
        </div>
        <button type="button" class="btn btn-link lp-read-more d-none mt-2 px-0" data-lp-clamp-toggle
          data-more-label="{{ __('admin.read_more') }}" data-less-label="{{ __('admin.read_less') }}">
          <span data-lp-clamp-label>{{ __('admin.read_more') }}</span>
          <i class="ti ti-chevron-down ms-1"></i>
        </button>
      </div>
    </section>
  @endforeach

  <!-- ===== CTA ===== -->
  {{-- <section class="section-py {{ $landingBg ? 'bg-body' : 'bg-card' }}">
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
          @if($showPrograms)
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
  </section> --}}

  <!-- ===== Footer ===== -->
  @php
    // The footer Programs block needs the on-page Programs section to exist
    // (the "More Programs" link scrolls to it) and at least one program to list;
    // unlike the section itself, an empty list must not leave an empty column.
    $lpFooterShowPrograms = $showPrograms && $visiblePrograms->isNotEmpty();

    // Labels mirror the dynamic section titles used in the top nav (with the
    // translation keys only as a fallback when a section has no title set).
    $lpFooterQuickLinks = collect();
    if ($showAbout) {
      $lpFooterAboutLabel = $l == 'ar'
        ? ($page->about_nav_title_ar ?? ($page->about_title_ar ?? null))
        : ($page->about_nav_title ?? ($page->about_title ?? null));
      $lpFooterQuickLinks->push(['label' => $lpFooterAboutLabel ?: __('landing.about'), 'url' => '#about']);
    }
    if ($showPrograms) {
      $lpFooterProgramsLabel = $l == 'ar'
        ? ($page->programs_nav_title_ar ?? ($page->programs_title_ar ?? null))
        : ($page->programs_nav_title ?? ($page->programs_title ?? null));
      $lpFooterQuickLinks->push(['label' => $lpFooterProgramsLabel ?: __('landing.programs'), 'url' => '#programs']);
    }
    if ($showHow) {
      $lpFooterHowLabel = $l == 'ar'
        ? ($page->how_nav_title_ar ?? ($page->how_title_ar ?? null))
        : ($page->how_nav_title ?? ($page->how_title ?? null));
      $lpFooterQuickLinks->push(['label' => $lpFooterHowLabel ?: __('landing.how_it_works'), 'url' => '#how-it-works']);
    }
    foreach ($customSections as $lpFooterSection) {
      $lpFooterSectionLabel = $l == 'ar'
        ? ($lpFooterSection->title_ar ?: $lpFooterSection->title)
        : ($lpFooterSection->title ?: $lpFooterSection->title_ar);
      $lpFooterSectionLabel = trim(strip_tags((string) $lpFooterSectionLabel));
      if ($lpFooterSectionLabel === '') {
        $lpFooterSectionLabel = __('landing.custom_section');
      }
      $lpFooterQuickLinks->push(['label' => $lpFooterSectionLabel, 'url' => '#custom-' . $lpFooterSection->id]);
    }
    $lpFooterQuickLinks = $lpFooterQuickLinks->take(5)->values();

    // Newest first. Ten or fewer programs are all listed; anything beyond
    // that collapses to the first nine plus a "More Programs" jump link.
    $lpFooterPrograms = $visiblePrograms->sortByDesc('created_at')->values();
    $lpFooterProgramsOverflow = $lpFooterPrograms->count() > 10;
    if ($lpFooterProgramsOverflow) {
      $lpFooterPrograms = $lpFooterPrograms->take(9)->values();
    }

    $lpFooterSocials = collect([
      ['label' => 'LinkedIn', 'icon' => 'ti ti-brand-linkedin', 'url' => $page->linkedin],
      ['label' => 'Instagram', 'icon' => 'ti ti-brand-instagram', 'url' => $page->instagram],
      ['label' => 'Facebook', 'icon' => 'ti ti-brand-facebook', 'url' => $page->facebook],
      ['label' => 'X', 'icon' => 'ti ti-brand-x', 'url' => $page->x],
      ['label' => 'TikTok', 'icon' => 'ti ti-brand-tiktok', 'url' => $page->tiktok],
      ['label' => 'YouTube', 'icon' => 'ti ti-brand-youtube', 'url' => $page->youtube],
    ])->filter(fn ($social) => !empty($social['url']))->values();
  @endphp
  <footer class="gl-footer py-5">
    <div class="container pt-3">
      <div class="row g-4 lp-footer-row">
        {{-- Brand column: keeps a readable width (see .lp-footer-col--brand). --}}
        <div class="col-12 col-lg lp-footer-col lp-footer-col--brand">
          <a class="d-flex align-items-center gap-2 mb-3 gl-footer__brand" href="#hero">
            <span class="brand-mark {{ $page->logo_url ? 'brand-mark--bare' : '' }}">
              @if($page->logo_url)
                <img src="{{ $page->logo_url }}" alt="{{ $page->title }}">
              @else
                <span class="brand-mark-placeholder"></span>
              @endif
            </span>
            <span class="fw-bold text-white fs-5">{{ $page->title }}</span>
          </a>
          @php $footerDescription = $l == 'ar' ? $page->description_ar : $page->description; @endphp
          @if($footerDescription)
            <p class="text-secondary small footer-desc" title="{{ $footerDescription }}">
              {{ $footerDescription }}
            </p>
          @endif
          @if($lpFooterSocials->isNotEmpty())
            <div class="footer-socials d-flex flex-wrap align-items-center gap-3 mt-3">
              @foreach($lpFooterSocials as $lpFooterSocial)
                <a href="{{ $lpFooterSocial['url'] }}" target="_blank" rel="noopener noreferrer"
                  class="footer-social" aria-label="{{ $lpFooterSocial['label'] }}"
                  title="{{ $lpFooterSocial['label'] }}">
                  <i class="{{ $lpFooterSocial['icon'] }}" aria-hidden="true"></i>
                </a>
              @endforeach
            </div>
          @endif
        </div>

        {{-- The link columns are content-sized (col-lg-auto) and the row uses
             space-between, so the gap between the brand block, Quick Links and
             Programs is identical in both LTR and RTL. No manual offsets. --}}
        @if($lpFooterQuickLinks->isNotEmpty())
          <div class="col-12 col-md-6 col-lg-auto lp-footer-col">
            <h6 class="mb-3">{{ __('landing.footer_quick_links') }}</h6>
            <div class="gl-footer__links">
              @foreach($lpFooterQuickLinks as $lpFooterLink)
                <a href="{{ $lpFooterLink['url'] }}" class="flink">{{ $lpFooterLink['label'] }}</a>
              @endforeach
            </div>
          </div>
        @endif

        @if($lpFooterShowPrograms)
          <div class="col-12 col-md-6 col-lg-auto lp-footer-col">
            <h6 class="mb-3">{{ __('landing.programs') }}</h6>
            <div class="gl-footer__links {{ $lpFooterPrograms->count() >= 6 ? 'gl-footer__links--2col' : '' }}">
              @foreach($lpFooterPrograms as $lpFooterProgram)
                @php
                  $lpFooterProgramTitle = $l == 'ar'
                    ? ($lpFooterProgram->title_ar ?: $lpFooterProgram->title)
                    : ($lpFooterProgram->title ?: $lpFooterProgram->title_ar);
                  $lpFooterProgramUrl = ($lpFooterProgram->public_slug && $lpFooterProgram->organization && $lpFooterProgram->organization->short_code)
                    ? route('public.program_join', ['org_slug' => $lpFooterProgram->organization->short_code, 'program_slug' => $lpFooterProgram->public_slug])
                    : null;
                @endphp
                <a href="{{ $lpFooterProgramUrl ?: '#programs' }}" class="line-clamp-1 flink"
                  @if($lpFooterProgramUrl) target="_blank" rel="noopener noreferrer" @endif>{{ $lpFooterProgramTitle }}</a>
              @endforeach
              @if($lpFooterProgramsOverflow)
                <a href="#programs" class="flink flink--more">{{ __('landing.footer_more_programs') }}</a>
              @endif
            </div>
          </div>
        @endif
      </div>

      <hr class="border-secondary lp-footer-divider">
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

    /* Long custom-section copy is clamped to a readable height; the toggle is
       only surfaced when the copy actually overflows, so short sections never
       grow a pointless button. Re-measured on resize since the clamp is in
       viewport units. */
    (function () {
      var CLAMPED_CLASS = 'is-clamped';
      var EXPANDED_CLASS = 'is-expanded';

      function pairs() {
        return Array.prototype.map.call(
          document.querySelectorAll('[data-lp-clamp]'),
          function (body) {
            return { body: body, button: body.parentElement.querySelector('[data-lp-clamp-toggle]') };
          }
        );
      }

      function isOverflowing(body) {
        // +4px absorbs sub-pixel rounding so a section that fits by a hair
        // does not get a Read More button that reveals nothing.
        return body.scrollHeight - body.clientHeight > 4;
      }

      function apply(pair) {
        if (!pair.button) return;
        // Measure with the clamp already on. An unclamped box always reports
        // scrollHeight === clientHeight, so measuring first would report every
        // section as fitting and the button would never appear.
        pair.body.classList.add(CLAMPED_CLASS);
        if (isOverflowing(pair.body)) {
          pair.button.classList.remove('d-none');
        } else {
          pair.body.classList.remove(CLAMPED_CLASS);
          pair.button.classList.add('d-none');
        }
      }

      function sync(pair) {
        var label = pair.button.querySelector('[data-lp-clamp-label]');
        if (!label) return;
        var expanded = !pair.body.classList.contains(CLAMPED_CLASS);
        label.textContent = expanded
          ? pair.button.dataset.lessLabel
          : pair.button.dataset.moreLabel;
        pair.button.classList.toggle(EXPANDED_CLASS, expanded);
        pair.button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      }

      document.addEventListener('DOMContentLoaded', function () {
        pairs().forEach(function (pair) {
          if (!pair.button) return;
          apply(pair);
          if (!pair.button.classList.contains('d-none')) sync(pair);
          pair.button.addEventListener('click', function () {
            pair.body.classList.toggle(CLAMPED_CLASS);
            sync(pair);
          });
        });
      });

      var resizeTimer;
      window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
          pairs().forEach(function (pair) {
            // Leave sections the reader deliberately expanded alone.
            if (!pair.button || pair.button.classList.contains(EXPANDED_CLASS)) return;
            apply(pair);
            if (!pair.button.classList.contains('d-none')) sync(pair);
          });
        }, 200);
      });
    })();

  </script>
</body>

</html>
