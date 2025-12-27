<!DOCTYPE html>
<html lang="{{ $l }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" data-textdirection="{{ $isRtl ? 'rtl' : 'ltr' }}"
    class="light-style layout-navbar-fixed layout-compact" data-theme="theme-default" data-assets-path="/vuexy/assets/"
    data-template="vertical-menu-template" data-style="light">

<head>
    {{--  @include('admin.includes.head')  --}}

    <!-- SEO Meta Tags -->
    <title>{{ $isRtl ? $organization->name_ar : $organization->name_en }} - {{ config('app.name') }}</title>
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

    <!-- Flag Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/tabler-icons.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/flag-icons.css" />
    <!-- End Icons -->

    <!-- Core CSS -->
    <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="/vuexy/assets/css/demo.css" />
    <!-- End Core CSS -->

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css" />

    <!-- For Searchable Select -->
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
    <!-- For Date & Time Picker -->
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />

    {{-- <link rel="stylesheet" href="/app-assets/plugins/sweatalert/sweetalert2.min.css"> --}}
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

    @php
        // Theme color configurations
        $themeConfig = [
            'gradient' => [
                'heroGradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'heroTextColor' => '#ffffff',
                'primaryColor' => '#667eea',
                'secondaryColor' => '#764ba2',
                'sectionBg' => '#f8f9fa',
                'cardBg' => '#ffffff',
            ],
            'minimal' => [
                'heroGradient' => 'linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%)',
                'heroTextColor' => '#333333',
                'primaryColor' => '#333333',
                'secondaryColor' => '#666666',
                'sectionBg' => '#ffffff',
                'cardBg' => '#f8f9fa',
            ],
            'vuexy' => [
                'heroGradient' => 'linear-gradient(135deg, #7367f0 0%, #9e95f5 100%)',
                'heroTextColor' => '#ffffff',
                'primaryColor' => '#7367f0',
                'secondaryColor' => '#9e95f5',
                'sectionBg' => '#f8f9fa',
                'cardBg' => '#ffffff',
            ],
        ];
        $currentTheme = $themeConfig[$theme] ?? $themeConfig['gradient'];

        // Get section backgrounds with defaults (alternating #f8f7fa and #ffffff)
        // Using getSectionBackground() helper from app/Http/Helpers/Helpers.php
        $heroBg = getSectionBackground($landingPage, 'hero', '#667eea', '#764ba2');
        $howItWorksBg = getSectionBackground($landingPage, 'how_it_works', '#f8f7fa', '#ffffff');
        $achievementsBg = getSectionBackground($landingPage, 'achievements', '#ffffff', '#f8f7fa');
        $mediaBg = getSectionBackground($landingPage, 'media', '#f8f7fa', '#ffffff');
        $partnersBg = getSectionBackground($landingPage, 'partners', '#ffffff', '#f8f7fa');

        // Determine if hero has dark background for text color
        $heroTextColor = '#ffffff';
        if ($landingPage->hero_bg_type === 'solid' && $landingPage->hero_bg_color) {
            // Simple lightness check for solid color
            $hex = ltrim($landingPage->hero_bg_color, '#');
            if (strlen($hex) === 6) {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
                $heroTextColor = $luminance > 0.5 ? '#333333' : '#ffffff';
            }
        }
    @endphp

    <style>
        :root {
            --theme-primary: {{ $currentTheme['primaryColor'] }};
            --theme-secondary: {{ $currentTheme['secondaryColor'] }};
            --theme-hero-gradient: {{ $currentTheme['heroGradient'] }};
            --theme-hero-text: {{ $heroTextColor }};
            --theme-section-bg: {{ $currentTheme['sectionBg'] }};
            --theme-card-bg: {{ $currentTheme['cardBg'] }};
            --hero-bg: {{ $heroBg }};
            --how-it-works-bg: {{ $howItWorksBg }};
            --achievements-bg: {{ $achievementsBg }};
            --media-bg: {{ $mediaBg }};
            --partners-bg: {{ $partnersBg }};
        }

        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* Hero Section */
        .hero-section {
            background: var(--hero-bg);
            color: var(--theme-hero-text);
            padding: 60px 0 80px;
            min-height: auto;
        }

        @if($theme === 'minimal' || ($landingPage->hero_bg_type === 'solid' && $heroTextColor === '#333333'))
        .hero-section {
            border-bottom: 1px solid #e9ecef;
        }
        @endif

        .hero-header {
            margin-bottom: 2rem;
        }

        .hero-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .hero-org-name {
            font-size: 0.8rem;
            line-height: 1.37 !important;
            font-weight: 700;
            color: var(--theme-hero-text);
            margin: 0;
        }

        .hero-org-name span{
            border: 1px solid lightgray;
            padding: 0.05rem 0.95rem;
            border-radius: 25px;
        }

        .hero-location {
            font-size: 0.9rem;
            color: var(--theme-hero-text);
            opacity: 0.8;
        }

        .hero-location i {
            font-size: 0.85rem;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-description {
            font-size: 1.15rem;
            opacity: 0.85;
            line-height: 1.8;
            max-width: 800px;
        }

        .hero-industries {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 2rem;
        }

        .industry-tag {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.15);
            color: var(--theme-hero-text);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            transition: all 0.2s ease;
        }

        @if($theme === 'minimal' || ($landingPage->hero_bg_type === 'solid' && $heroTextColor === '#333333'))
        .industry-tag {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #e0e0e0;
        }

        .industry-tag:hover {
            background: #eaeaea;
        }
        @else
        .industry-tag:hover {
            background: rgba(255, 255, 255, 0.25);
        }
        @endif

        .industry-tag i {
            font-size: 1rem;
        }

        /* Section Styles */
        .landing-section {
            padding: 80px 0;
        }

        .section-how-it-works {
            background: var(--how-it-works-bg);
        }

        .section-achievements {
            background: var(--achievements-bg);
        }

        .section-media {
            background: var(--media-bg);
        }

        .section-partners {
            background: var(--partners-bg);
        }

        .section-alt {
            background: var(--theme-section-bg);
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .section-bill-subtitle {
            margin-bottom: 0.5rem;
        }

        .section-bill-subtitle span {
            font-size: 0.8rem;
            color: #666;
            border: 1px solid lightgray;
            background-color: lightgray;
            padding: 0.25rem 0.75rem;
            border-radius: 25px;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
        }

        /* Process Steps */
        .step-card {
            background: var(--theme-card-bg);
            border-radius: 16px;
            padding: 30px;
            height: 100%;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--theme-primary);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .step-icon {
            font-size: 2rem;
            color: var(--theme-primary);
            margin-bottom: 15px;
        }

        /* Metrics */
        .metric-card {
            background: var(--theme-card-bg);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            height: 100%;
        }

        .metric-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-secondary) 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 15px;
        }

        .metric-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--theme-primary);
            margin-bottom: 5px;
        }

        .metric-label {
            font-size: 1rem;
            color: #666;
        }

        /* Articles */
        .article-card {
            background: var(--theme-card-bg);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            height: 100%;
            transition: all 0.3s ease;
        }

        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .article-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #f8f9fa;
        }

        .article-body {
            padding: 20px;
        }

        .article-publication {
            font-size: 0.85rem;
            color: var(--theme-primary);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .article-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .article-summary {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.6;
        }

        .article-date {
            font-size: 0.8rem;
            color: #999;
            margin-top: 10px;
        }

        /* Partners */
        .partners-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            align-items: center;
        }

        .partner-logo {
            height: 60px;
            max-width: 150px;
            object-fit: contain;
            filter: grayscale(100%);
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .partner-logo:hover {
            filter: grayscale(0%);
            opacity: 1;
        }

        /* Footer */
        .landing-footer {
            background: #1a1a2e;
            color: #ffffff;
            padding: 60px 0 30px;
        }

        .footer-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 10px;
            background: #ffffff;
            padding: 5px;
        }

        .footer-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .footer-description {
            font-size: 0.9rem;
            opacity: 0.8;
            max-width: 300px;
        }

        .footer-social a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .footer-social a:hover {
            background: var(--theme-primary);
            transform: translateY(-3px);
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #ffffff;
        }

        .footer-copyright {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
            margin-top: 40px;
            font-size: 0.9rem;
            opacity: 0.7;
        }

        /* Preview Banner */
        .preview-banner {
            background: #ff9800;
            color: #ffffff;
            padding: 10px;
            text-align: center;
            font-weight: 600;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
        }

        @if($isPreview)
        body {
            padding-top: 45px;
        }
        @endif

        /* Navbar */
        .landing-navbar {
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            font-weight: 700;
            color: #333 !important;
        }

        .nav-link {
            color: #666 !important;
            font-weight: 500;
        }

        .nav-link:hover {
            color: var(--theme-primary) !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-org-name {
                font-size: 1.2rem;
            }

            .hero-logo {
                width: 55px;
                height: 55px;
            }

            .hero-location {
                font-size: 0.8rem;
            }

            .hero-section {
                padding: 40px 0 60px;
            }

            .hero-description {
                font-size: 1rem;
            }

            .hero-industries {
                gap: 8px;
            }

            .industry-tag {
                padding: 8px 14px;
                font-size: 0.8rem;
            }

            .landing-section {
                padding: 50px 0;
            }

            .metric-number {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    @if($isPreview)
    <!-- Preview Banner -->
    <div class="preview-banner">
        <i class="ti ti-eye me-2"></i>
        {{ $isRtl ? 'وضع المعاينة - هذه الصفحة غير مرئية للعامة' : 'Preview Mode - This page is not publicly visible' }}
    </div>
    @endif

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg landing-navbar sticky-top py-3">
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
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 {{ $isRtl ? 'text-end' : '' }}">
                    <!-- Logo and Organization Name Row -->
                    <div class="hero-header d-flex align-items-center mb-4 {{ $isRtl ? 'flex-row-reverse' : '' }}">
                        @if($organization->img_url)
                            <img src="{{ $organization->imageUrl }}" alt="{{ $isRtl ? $organization->name_ar : $organization->name_en }}"
                                 class="hero-logo {{ $isRtl ? 'ms-3' : 'me-3' }}">
                        @endif
                        <div>
                            <h1 class="hero-title mb-1">{{ $isRtl ? $organization->name_ar : $organization->name_en }}</h1>
                            @if(($organization->city && ($organization->city->name_en || $organization->city->name_ar)) || ($organization->country && ($organization->country->name_en || $organization->country->name_ar)))
                                <div class="hero-location">
                                    <i class="ti ti-map-pin {{ $isRtl ? 'ms-1' : 'me-1' }}"></i>
                                    @if($organization->city)
                                        {{ $isRtl ? $organization->city->name_ar : $organization->city->name_en }}@if($organization->country), @endif
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
                                    <i class="ti {{ getIndustryIcon($industry->name_en) }} {{ $isRtl ? 'ms-2' : 'me-2' }}"></i>{{ $isRtl ? $industry->name_ar : $industry->name_en }}
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

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-3">
                        @if($organization->img_url)
                            <img src="{{ $organization->imageUrl }}" alt="{{ $isRtl ? $organization->name_ar : $organization->name_en }}"
                                 class="footer-logo me-3">
                        @endif
                        <div>
                            <h5 class="footer-title text-white mb-0">{{ $isRtl ? $organization->name_ar : $organization->name_en }}</h5>
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
    </footer>

    <!-- Scripts -->
    <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="/vuexy/assets/vendor/libs/popper/popper.js"></script>
    <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
    <!-- Jquery Js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Helpers -->
    <script src="/vuexy/assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="/vuexy/assets/vendor/js/template-customizer.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="/vuexy/assets/js/config.js"></script>
</body>
</html>
