<!DOCTYPE html>
<html
    lang="{{ $l }}"
    dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
    data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
    class="light-style layout-navbar-fixed layout-compact"
    data-theme="theme-default"
    data-assets-path="/vuexy/assets/"
    data-template="vertical-menu-template"
    data-style="light">

<head>
    @include('admin.includes.head')

    <!-- Additional Styles -->
    <link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />

    <style>
        .hackathon-banner {
            background: linear-gradient(135deg, #7367f0 0%, #9c88ff 35%, #ffc107 100%);
            background-size: 400% 400%;
            animation: gradientShift 6s ease infinite;
            color: white;
            padding: 2.5rem 2rem;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(115, 103, 240, 0.3);
            margin-bottom: 2rem;
        }

        .hackathon-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            z-index: 1;
        }

        .hackathon-banner .banner-content {
            position: relative;
            z-index: 2;
        }

        .hackathon-banner h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.02em;
        }

        .hackathon-banner .lead {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 2rem;
            opacity: 0.95;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .hackathon-banner .lead i {
            font-size: 1.3rem;
            margin-right: 0.5rem;
            margin-left: 0.5rem;
        }

        .hackathon-status-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        @media (max-width: 768px) {
            .hackathon-banner {
                padding: 3rem 1rem;
            }

            .hackathon-banner h1 {
                font-size: 2.5rem;
            }
        }

        .nav-tabs .nav-link.active {
            background-color: #7367f0;
            color: white;
            border-color: #7367f0;
        }

        .nav-tabs .nav-link {
            color: #7367f0;
            border-radius: 10px !important;
            margin: 0 0.25rem;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            border-color: #7367f0;
            background-color: rgba(115, 103, 240, 0.1);
        }

        .timeline {
            list-style-type: none;
            padding-left: 0;
        }

        .timeline li {
            margin-bottom: 1.5rem;
            position: relative;
            padding-left: 25px;
        }

        .timeline li::before {
            content: '';
            background-color: #7367f0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            position: absolute;
            right: -15px;
            top: 5px;
            border: 2px solid white;
        }

        .timeline li .time {
            font-weight: bold;
            display: block;
            color: #6c757d;
        }

        .countdown-timer {
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>

@php
    // $currentHackathon = [
    //     'title' => 'Future of AI Hackathon',
    //     'title_ar' => 'هاكثون مستقبل الذكاء الاصطناعي',
    //     'date' => 'March 15 - 17, 2024',
    //     'date_ar' => '15 - 17 مارس 2024',
    //     'location' => 'Virtual',
    //     'location_ar' => 'افتراضي',
    //     'status' => 'Upcoming',
    //     'status_ar' => 'قادم',
    //     'description' => 'Innovate solutions using artificial intelligence to address urgent industrial challenges.',
    //     'description_ar' => 'هذا الهاكثون يهدف إلى جمع المبتكرين والمطورين لخلق حلول رائدة باستخدام تقنيات الذكاء الاصطناعي لمواجهة التحديات الحالية والمستقبلية في مختلف القطاعات.',
    //     'tags' => ['AI', 'Big Data'],
    //     'tags_ar' => ['الذكاء الاصطناعي', 'البيانات الضخمة'],
    // ];
@endphp

<body>
    <!-- Include original navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/dashboard/hackathon">
                <i class="ti ti-tournament text-primary me-2 me-2"></i> {{ $l == 'en' ? 'Hackathon Platform' : 'منصة
                الهاكثونات' }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard/hackathon">
                            <i class="ti ti-home me-2"></i>
                            {{ $l == 'en' ? 'Home' : 'الرئيسية' }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard/hackathon#past-hackathons">
                            <i class="ti ti-calendar-check me-2"></i>
                            {{ $l == 'en' ? 'Past Hackathons' : 'الهاكثونات السابقة' }}
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-user-circle me-2"></i> {{ $l == 'en' ? 'Hello, Entrepreneur' : 'مرحباً، رائد
                            أعمال' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="ti ti-user me-2"></i>
                                    {{ $l == 'en' ? 'My Profile' : 'ملفي الشخصي' }}
                                </a>
                            </li>
                            @if($companyStep?->stype != 1)
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="ti ti-users me-2"></i>
                                    {{ $l == 'en' ? 'My Team' : 'فريقي' }}
                                </a>
                            </li>
                            @endif
                            <li>
                                <a class="dropdown-item" href="/dashboard">
                                    <i class="ti ti-dashboard me-2"></i>
                                    {{ $l == 'en' ? 'Admin Dashboard' : 'لوحة تحكم الإدارة' }}
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="ti ti-logout me-2"></i>
                                    {{ $l == 'en' ? 'Logout' : 'تسجيل الخروج' }}
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="overflow-x-hidden mx-auto" style="width: 96%;">

        <div class="container mt-4">

            <div class="hackathon-banner text-center">
                <div class="banner-content">
                    <div class="hackathon-status-badge">
                        {{ $l == 'en' ? $currentHackathon['status'] : $currentHackathon['status_ar'] }}
                    </div>
                    <h1 class="display-5 fw-bold" id="hackathon-main-title">
                        {{ $l == 'en' ? $currentHackathon['title'] : $currentHackathon['title_ar'] }}
                    </h1>
                    <p class="lead" id="hackathon-dates-location">
                        <i class="ti ti-calendar-event me-2"></i>
                        {{ $l == 'en' ? $currentHackathon['date'] : $currentHackathon['date_ar'] }}
                        <span class="mx-3">|</span>
                        <i class="ti ti-map-pin me-2"></i>
                        {{ $l == 'en' ? $currentHackathon['location'] : $currentHackathon['location_ar'] }}
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                        @foreach(($l == 'en' ? $currentHackathon['tags'] : $currentHackathon['tags_ar']) as $tag)
                            <span class="badge bg-light text-dark px-3 py-2"
                                style="background: rgba(255, 255, 255, 0.2) !important; color: white !important; border: 1px solid rgba(255, 255, 255, 0.3);">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                    <div id="action-buttons-placeholder" class="mt-4">
                        <!-- Buttons will be injected by JS -->
                        @php
                            $teamcreated = App\TeamFormation::where('user_id', auth()->user()->id)->first();
                            $teamJoin = App\Team_member::where('Email', auth()->user()->email)->where('acceptInvition', 'pending')->first();
                            $teamaccept = App\Team_member::where('Email', auth()->user()->email)->where('acceptInvition', 'accepted')->first();
                           
                        @endphp
                        @if($teamcreated != null)
                           

                                <a href="/dashboard/hackathon/{{ $teamcreated->id }}/manage" class="btn btn-light btn-lg px-4 py-2 me-3"
                                    style="border-radius: 50px; font-weight: 600;">
                                    <i class="ti ti-settings me-2 me-2"></i>
                                    {{ $l == 'en' ? 'Manage My Team' : 'إدارة فريقي' }}
                                </a>
                         
                        @else
                          
                     
                        @if($companyStep?->stype == 6 OR $companyStep == null)
                         @if($teamJoin != null)

                        <form action="{{ route('hackathon.acceptInvitation', $teamJoin->id) }}" class="d-inline-block" method="post">
                            @csrf
                            @method('PATCH')
                                <button class="btn btn-success btn-lg px-4 py-2 me-3"
                                    style="border-radius: 50px; font-weight: 600;">
                                    <i class="ti ti-users me-2 me-2"></i>
                                    {{ $l == 'en' ? 'Accept Invitation' : ' قبول الدعوة ' }}
                                </button>
                        </form>
                        <form action="{{ route('hackathon.rejectInvitation', $teamJoin->id) }}" class="d-inline-block" method="post">
                            @csrf
                            @method('PATCH')
                                <button class="btn btn-danger btn-lg px-4 py-2 me-3"
                                    style="border-radius: 50px; font-weight: 600;">
                                    <i class="ti ti-users me-2 me-2"></i>
                                    {{ $l == 'en' ? 'Reject Invitation' : ' رفض الدعوة ' }}
                                </button>
                        </form>
                            @else
                           
                        @if($teamaccept == null)
                        @if($companyStep?->stype  != 6)
                        <button type="button"  class="btn btn-primary" style="flex: 1;"
                        onclick="window.location.href='/dashboard/apply/{{ $currentHackathon['step_id'] }}/hackathon'" >
                        <i class="ti ti-send me-1"></i>
                        {{__('admin.ApplyHackathon') }}
                        </button>
        @else
                        <a href="#" class="btn btn-light btn-lg px-4 py-2 me-3"
                            style="border-radius: 50px; font-weight: 600;" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                            <i class="ti ti-settings me-2 me-2"></i>
                            {{ $l == 'en' ? 'Create New Team' : 'تكوين فريق جديد' }}
                        </a>
                        @endif
                        @else
                        <a href="/dashboard/hackathon/{{ $teamaccept->team_formation_id }}/manage" class="btn btn-light btn-lg px-4 py-2 me-3"
                            style="border-radius: 50px; font-weight: 600;">
                            <i class="ti ti-users me-2 me-2"></i>
                            {{ $l == 'en' ? 'View My Team' : 'مشاهدة الفريق' }}
                        </a>
                        @endif
                        @endif
                        @else
                        <a href="#" class="btn btn-light btn-lg px-4 py-2 me-3"
                            style="border-radius: 50px; font-weight: 600;" data-bs-toggle="modal" data-bs-target="#applyModal">
                            <i class="ti ti-send me-2 me-2"></i>
                            {{ $l == 'en' ? ' Wait for Approval' : ' انتظر موافقة المسئول' }}
                        </a>
                        @endif
                        @endif
                        {{-- <button class="btn btn-light btn-lg px-4 py-2 me-3"
                            style="border-radius: 50px; font-weight: 600;">
                            <i class="ti ti-user-plus me-2 me-2"></i>
                            {{ $l == 'en' ? 'Join Hackathon' : 'انضم للهاكثون' }}
                        </button>
                        <button class="btn btn-outline-light btn-lg px-4 py-2"
                            style="border-radius: 50px; font-weight: 600; border: 2px solid rgba(255, 255, 255, 0.5);">
                            <i class="ti ti-heart me-2 me-2"></i>
                            {{ $l == 'en' ? 'Add to Wishlist' : 'أضف للمفضلة' }}
                        </button> --}}
                    </div>
                </div>
            </div>

            <div id="countdown-timer-container" class="alert text-center mb-4"
                style="display:none; background: linear-gradient(45deg, #17a2b8, #20c997); border: none; border-radius: 15px; color: white; box-shadow: 0 8px 25px rgba(23, 162, 184, 0.3);">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="ti ti-clock-hour-4 me-2" style="font-size: 1.5rem; me-2"></i>
                    <strong>
                        {{ $l == 'en' ? 'Time remaining to start hackathon:' : 'الوقت المتبقي لبدء الهاكثون:' }}
                    </strong>
                </div>
                <div class="mt-2">
                    <span id="countdown-timer" class="countdown-timer"
                        style="font-size: 1.8rem; font-weight: 800; text-shadow: 0 2px 4px rgba(0,0,0,0.2);"></span>
                </div>
            </div>

            <div id="submission-countdown-timer-container" class="alert text-center mb-4"
                style="display:none; background: linear-gradient(45deg, #fd7e14, #ffc107); border: none; border-radius: 15px; color: white; box-shadow: 0 8px 25px rgba(253, 126, 20, 0.3);">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="ti ti-upload me-2" style="font-size: 1.5rem; me-2"></i>
                    <strong>
                        {{ $l == 'en' ? 'Time remaining to submit projects:' : 'الوقت المتبقي لتقديم المشاريع:' }}
                    </strong>
                </div>
                <div class="mt-2">
                    <span id="submission-countdown-timer" class="countdown-timer"
                        style="font-size: 1.8rem; font-weight: 800; text-shadow: 0 2px 4px rgba(0,0,0,0.2);"></span>
                </div>
            </div>


            <ul class="nav nav-tabs nav-fill mb-4 pb-4" id="hackathonTab" role="tablist">
                <li class="nav-item me-1" role="presentation">
                    <button class="nav-link rounded active" id="overview-tab" data-bs-toggle="tab"
                        data-bs-target="#overview" type="button" role="tab" aria-controls="overview"
                        aria-selected="true">
                        <i class="bi bi-card-text me-2"></i> {{ $l == 'en' ? 'Overview' : 'نظرة عامة' }}
                    </button>
                </li>
                <li class="nav-item me-1" role="presentation">
                    <button class="nav-link rounded" id="themes-tab" data-bs-toggle="tab" data-bs-target="#themes"
                        type="button" role="tab" aria-controls="themes" aria-selected="false">
                        <i class="bi bi-lightbulb-fill me-2"></i> {{ $l == 'en' ? 'Themes' : 'المحاور' }}
                    </button>
                </li>
                <li class="nav-item me-1" role="presentation">
                    <button class="nav-link rounded" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule"
                        type="button" role="tab" aria-controls="schedule" aria-selected="false">
                        <i class="bi bi-calendar2-week-fill me-2"></i> {{ $l == 'en' ? 'Schedule' : 'جدول الأعمال' }}
                    </button>
                </li>
                <li class="nav-item me-1" role="presentation">
                    <button class="nav-link rounded" id="prizes-tab" data-bs-toggle="tab" data-bs-target="#prizes"
                        type="button" role="tab" aria-controls="prizes" aria-selected="false">
                        <i class="bi bi-trophy-fill me-2"></i> {{ $l == 'en' ? 'Prizes' : 'الجوائز' }}
                    </button>
                </li>
                <li class="nav-item me-1" role="presentation">
                    <button class="nav-link rounded" id="evaluation-tab" data-bs-toggle="tab"
                        data-bs-target="#evaluation" type="button" role="tab" aria-controls="evaluation"
                        aria-selected="false">
                        <i class="bi bi-clipboard-check-fill me-2"></i> {{ $l == 'en' ? 'Evaluation Criteria' : 'معايير
                        التقييم' }}
                    </button>
                </li>
                <li class="nav-item me-1" role="presentation">
                    <button class="nav-link rounded" id="mentors-tab" data-bs-toggle="tab" data-bs-target="#mentors"
                        type="button" role="tab" aria-controls="mentors" aria-selected="false">
                        <i class="bi bi-person-video3 me-2"></i> {{ $l == 'en' ? 'Mentors & Judges' : 'المرشدون
                        والمحكمون' }}
                    </button>
                </li>
                
                @if($companyStep?->stype != 1)
                <li class="nav-item me-1" role="presentation" id="teams-tab-li">
                    <button class="nav-link rounded" id="teams-tab-btn" data-bs-toggle="tab" data-bs-target="#teams"
                        type="button" role="tab" aria-controls="teams" aria-selected="false">
                        <i class="bi bi-people-fill me-2"></i> {{ $l == 'en' ? 'Team Formation' : 'تكوين الفرق' }}
                    </button>
                </li>
                @endif
                <li class="nav-item me-1" role="presentation" id="submission-tab-li">
                    <button class="nav-link rounded" id="submission-tab-btn" data-bs-toggle="tab"
                        data-bs-target="#submission" type="button" role="tab" aria-controls="submission"
                        aria-selected="false">
                        <i class="bi bi-upload me-2"></i> {{ $l == 'en' ? 'Project Submission' : 'رفع المشاريع' }}
                    </button>
                </li>
                <li class="nav-item me-1" role="presentation" id="results-tab-li">
                    <button class="nav-link rounded" id="results-tab-btn" data-bs-toggle="tab" data-bs-target="#results"
                        type="button" role="tab" aria-controls="results" aria-selected="false">
                        <i class="bi bi-award-fill me-2"></i> {{ $l == 'en' ? 'Results' : 'النتائج' }}
                    </button>
                </li>

                <li class="nav-item me-1" role="presentation" id="submissions-tab-li">
                    <button class="nav-link rounded" id="submissions-tab-btn" data-bs-toggle="tab" data-bs-target="#submissions"
                        type="button" role="tab" aria-controls="submissions" aria-selected="false">
                        <i class="bi bi-award-fill me-2"></i> {{ $l == 'en' ? 'Submissions' : 'الاجابات' }}
                    </button>
                </li>

            </ul>

            <div class="tab-content p-0 mb-12" id="hackathonTabContent">
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $l == 'en' ? 'About the Hackathon' : 'عن الهاكثون' }}
                            </h5>
                            <p id="hackathon-description">
                                {{ $l == 'en' ? $currentHackathon['description'] : $currentHackathon['description_ar'] }}
                            </p>
                            {{-- <h6 class="mt-4">
                                {{ $l == 'en' ? 'Main Objectives:' : 'الأهداف الرئيسية:' }}
                            </h6> --}}
                            {{-- <ul id="hackathon-objectives">
                                @if($l == 'en')
                                    <li>Encourage innovation in artificial intelligence.</li>
                                    <li>Provide a platform for collaboration and knowledge exchange.</li>
                                    <li>Develop practical and applicable solutions.</li>
                                    <li>Support promising projects to advance to higher stages.</li>
                                @else
                                    <li>تشجيع الابتكار في مجال الذكاء الاصطناعي.</li>
                                    <li>توفير منصة للتعاون وتبادل الخبرات.</li>
                                    <li>تطوير حلول عملية قابلة للتطبيق.</li>
                                    <li>دعم المشاريع الواعدة للانتقال إلى مراحل متقدمة.</li>
                                @endif
                            </ul> --}}
                            {{-- <h6 class="mt-4">
                                {{ $l == 'en' ? 'Target Audience:' : 'الفئة المستهدفة:' }}
                            </h6>
                            <ul id="hackathon-audience">
                                @if($l == 'en')
                                    <li>Entrepreneurs registered on the platform.</li>
                                    <li>Developers, designers, and idea holders.</li>
                                    <li>Those interested in artificial intelligence technologies.</li>
                                    <li>Participating teams from outside the incubator (according to hackathon conditions).</li>
                                @else
                                    <li>رواد الأعمال المسجلين على المنصة.</li>
                                    <li>المطورون، المصممون، وأصحاب الأفكار.</li>
                                    <li>المهتمون بتقنيات الذكاء الاصطناعي.</li>
                                    <li>الفرق المشاركة من خارج المسرعة (حسب شروط الهاكثون).</li>
                                @endif
                            </ul> --}}
                        </div>
                    </div>
                </div>

                <!-- Themes Tab -->
                <div class="tab-pane fade" id="themes" role="tabpanel" aria-labelledby="themes-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $l == 'en' ? 'Hackathon Themes' : 'محاور الهاكثون' }}
                            </h5>
                            <p>
                                {{ $l == 'en' ? 'The hackathon focuses on the following themes. Teams can choose one theme or combine multiple themes in their solutions.' : 'يركز الهاكثون على المحاور التالية. يمكن للفرق اختيار محور واحد أو دمج عدة محاور في حلولهم.' }}
                            </p>
                            <div class="row" id="hackathon-themes-list">
                                <div class="col-md-6 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="bi bi-robot me-2 text-primary me-2"></i>
                                                {{ $l == 'en' ? 'AI in Healthcare' : 'الذكاء الاصطناعي في الرعاية الصحية' }}
                                            </h6>
                                            <p class="card-text">
                                                {{ $l == 'en' ? 'Applications to improve diagnosis, patient data management, and personalized medicine.' : 'تطبيقات لتحسين التشخيص، إدارة بيانات المرضى، والطب الشخصي.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="bi bi-bank2 me-2 text-success me-2"></i>
                                                {{ $l == 'en' ? 'AI in Financial Services' : 'الذكاء الاصطناعي في الخدمات المالية' }}
                                            </h6>
                                            <p class="card-text">
                                                {{ $l == 'en' ? 'Solutions for fraud detection, risk analysis, and automated financial consulting.' : 'حلول للكشف عن الاحتيال، تحليل المخاطر، وتقديم استشارات مالية آلية.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="bi bi-buildings-fill me-2 text-info me-2"></i>
                                                {{ $l == 'en' ? 'AI for Smart Cities' : 'الذكاء الاصطناعي للمدن الذكية' }}
                                            </h6>
                                            <p class="card-text">
                                                {{ $l == 'en' ? 'Systems for traffic management, energy efficiency, and improving public services.' : 'أنظمة لإدارة المرور، كفاءة الطاقة، وتحسين الخدمات العامة.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedule Tab -->
                <div class="tab-pane fade" id="schedule" role="tabpanel" aria-labelledby="schedule-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $l == 'en' ? 'Hackathon Schedule' : 'جدول أعمال الهاكثون' }}
                            </h5>
                            <ul class="timeline" id="hackathon-schedule-list">
                                <li>
                                    <span class="time">
                                        {{ $l == 'en' ? 'Day 1: March 15, 2024' : 'اليوم الأول: 15 مارس 2024' }}
                                    </span>
                                    @if($l == 'en')
                                        <strong>09:00 - 10:00:</strong> Registration and hackathon opening<br>
                                        <strong>10:00 - 11:00:</strong> Keynote speeches and theme presentations<br>
                                        <strong>11:00 - 13:00:</strong> Workshop: Team formation and idea development<br>
                                        <strong>13:00 - 14:00:</strong> Lunch break<br>
                                        <strong>14:00 - 18:00:</strong> Start working on projects (mentoring sessions available)
                                    @else
                                        <strong>09:00 - 10:00:</strong> تسجيل وافتتاح الهاكثون<br>
                                        <strong>10:00 - 11:00:</strong> كلمات رئيسية وعرض المحاور<br>
                                        <strong>11:00 - 13:00:</strong> ورشة عمل: تكوين الفرق وتطوير الأفكار<br>
                                        <strong>13:00 - 14:00:</strong> استراحة غداء<br>
                                        <strong>14:00 - 18:00:</strong> بدء العمل على المشاريع (جلسات إرشاد متاحة)
                                    @endif
                                </li>
                                <li>
                                    <span class="time">
                                        {{ $l == 'en' ? 'Day 2: March 16, 2024' : 'اليوم الثاني: 16 مارس 2024' }}
                                    </span>
                                    @if($l == 'en')
                                        <strong>09:00 - 18:00:</strong> Continue working on projects (advanced mentoring sessions)<br>
                                        <strong>14:00 - 15:00:</strong> Workshop: How to make an effective pitch
                                    @else
                                        <strong>09:00 - 18:00:</strong> مواصلة العمل على المشاريع (جلسات إرشاد متقدمة)<br>
                                        <strong>14:00 - 15:00:</strong> ورشة عمل: كيفية تقديم عرض فعال (Pitching)
                                    @endif
                                </li>
                                <li>
                                    <span class="time">
                                        {{ $l == 'en' ? 'Day 3: March 17, 2024' : 'اليوم الثالث: 17 مارس 2024' }}
                                    </span>
                                    @if($l == 'en')
                                        <strong>09:00 - 12:00:</strong> Final touches on projects<br>
                                        <strong>12:00:</strong> Final deadline for project submission<br>
                                        <strong>14:00 - 17:00:</strong> Project presentations to judging panel<br>
                                        <strong>17:30 - 18:00:</strong> Results announcement and awards ceremony
                                    @else
                                        <strong>09:00 - 12:00:</strong> اللمسات الأخيرة على المشاريع<br>
                                        <strong>12:00:</strong> الموعد النهائي لرفع المشاريع<br>
                                        <strong>14:00 - 17:00:</strong> عروض المشاريع أمام لجنة التحكيم<br>
                                        <strong>17:30 - 18:00:</strong> إعلان النتائج وتوزيع الجوائز
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Prizes Tab -->
                <div class="tab-pane fade" id="prizes" role="tabpanel" aria-labelledby="prizes-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $l == 'en' ? 'Prizes and Rewards' : 'الجوائز والمكافآت' }}
                            </h5>
                            <p>
                                {{ $l == 'en' ? 'Winning teams will be honored with valuable prizes, in addition to advanced support opportunities from the incubator ecosystem.' : 'سيتم تكريم الفرق الفائزة بجوائز قيمة، بالإضافة إلى فرص دعم متقدمة من منظومة المسرعة.' }}
                            </p>
                            <div class="row text-center" id="hackathon-prizes-list">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-warning">
                                        <div class="card-body">
                                            <i class="bi bi-award-fill display-3 text-warning me-2"></i>
                                            <h6 class="card-title mt-2">
                                                {{ $l == 'en' ? 'First Place' : 'المركز الأول' }}
                                            </h6>
                                            <p class="card-text">
                                                {{ $l == 'en' ? 'Prize of 20,000 SAR + incubation opportunity in the accelerator.' : 'جائزة مالية قدرها 20,000 ريال سعودي + فرصة احتضان في المسرعة.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-secondary">
                                        <div class="card-body">
                                            <i class="bi bi-award display-3 text-secondary me-2"></i>
                                            <h6 class="card-title mt-2">
                                                {{ $l == 'en' ? 'Second Place' : 'المركز الثاني' }}
                                            </h6>
                                            <p class="card-text">
                                                {{ $l == 'en' ? 'Prize of 10,000 SAR + access to specialized mentoring programs.' : 'جائزة مالية قدرها 10,000 ريال سعودي + وصول لبرامج إرشاد متخصصة.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-danger">
                                        <div class="card-body">
                                            <i class="bi bi-award display-3" style="color: #cd7f32; me-2"></i>
                                            <h6 class="card-title mt-2">
                                                {{ $l == 'en' ? 'Third Place' : 'المركز الثالث' }}
                                            </h6>
                                            <p class="card-text">
                                                {{ $l == 'en' ? 'Prize of 5,000 SAR + invitations to exclusive events.' : 'جائزة مالية قدرها 5,000 ريال سعودي + دعوات لفعاليات حصرية.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3">
                                <strong>
                                    {{ $l == 'en' ? 'Additional Prizes:' : 'جوائز إضافية:' }}
                                </strong>
                                {{ $l == 'en' ? 'There may be special prizes from sponsors for specific themes or outstanding solutions.' : 'قد تكون هناك جوائز خاصة مقدمة من الرعاة لبعض المحاور أو الحلول المتميزة.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Evaluation Tab -->
                <div class="tab-pane fade" id="evaluation" role="tabpanel" aria-labelledby="evaluation-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $l == 'en' ? 'Evaluation Criteria' : 'معايير التقييم' }}
                            </h5>
                            <p>
                                {{ $l == 'en' ? 'The judging committee will evaluate projects based on the following criteria:' : 'ستقوم لجنة التحكيم بتقييم المشاريع بناءً على المعايير التالية:' }}
                            </p>
                            <ul class="list-group list-group-flush" id="hackathon-evaluation-criteria">
                                <li class="list-group-item">
                                    <strong>
                                        {{ $l == 'en' ? 'Innovation and Originality (30%):' : 'الابتكار والأصالة (30%):' }}
                                    </strong>
                                    {{ $l == 'en' ? 'How innovative the idea is and its ability to provide a unique solution.' : 'مدى حداثة الفكرة وقدرتها على تقديم حل فريد.' }}
                                </li>
                                <li class="list-group-item">
                                    <strong>
                                        {{ $l == 'en' ? 'Practical Application and Feasibility (25%):' : 'التطبيق العملي والقابلية للتنفيذ (25%):' }}
                                    </strong>
                                    {{ $l == 'en' ? 'The possibility of implementing the solution in reality and its potential impact.' : 'إمكانية تطبيق الحل على أرض الواقع وتأثيره المحتمل.' }}
                                </li>
                                <li class="list-group-item">
                                    <strong>
                                        {{ $l == 'en' ? 'Quality of Prototype/Presentation (20%):' : 'جودة النموذج الأولي/العرض (20%):' }}
                                    </strong>
                                    {{ $l == 'en' ? 'How complete and effective the prototype is and how it is presented.' : 'مدى اكتمال وفعالية النموذج الأولي وطريقة عرضه.' }}
                                </li>
                                <li class="list-group-item">
                                    <strong>
                                        {{ $l == 'en' ? 'Business Model and Sustainability (15%):' : 'نموذج العمل والاستدامة (15%):' }}
                                    </strong>
                                    {{ $l == 'en' ? 'Clarity of the proposed business model and the project\'s ability to continue and grow.' : 'وضوح نموذج العمل المقترح وقدرة المشروع على الاستمرار والنمو.' }}
                                </li>
                                <li class="list-group-item">
                                    <strong>
                                        {{ $l == 'en' ? 'Team Performance and Collaboration (10%):' : 'أداء الفريق والتعاون (10%):' }}
                                    </strong>
                                    {{ $l == 'en' ? 'How effectively the team worked and collaborated during the hackathon.' : 'مدى فعالية عمل الفريق وتعاونه خلال الهاكثون.' }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Mentors Tab -->
                <div class="tab-pane fade" id="mentors" role="tabpanel" aria-labelledby="mentors-tab">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $l == 'en' ? 'Mentors and Judging Panel' : 'المرشدون ولجنة التحكيم' }}
                            </h5>
                            <h6>{{ $l == 'en' ? 'Mentors' : 'المرشدون' }}</h6>
                            <p>
                                {{ $l == 'en' ? 'Participants will have the opportunity to receive guidance from experts in technology and entrepreneurship fields.' : 'سيتاح للمشاركين فرصة الحصول على إرشاد من خبراء في مجالات التكنولوجيا وريادة الأعمال.' }}
                            </p>
                            <div class="row" id="hackathon-mentors-list">
                                <div class="col-md-4 mb-3">
                                    <div class="card text-center">
                                        <img src="https://via.placeholder.com/150.png?text=Mentor+1"
                                            class="card-img-top rounded-circle mx-auto mt-3" alt="Mentor 1"
                                            style="width:100px; height:100px; object-fit: cover;">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                {{ $l == 'en' ? 'Dr. Ahmed Abdullah' : 'د. أحمد عبدالله' }}
                                            </h6>
                                            <p class="card-text small text-muted">
                                                {{ $l == 'en' ? 'AI Expert' : 'خبير في الذكاء الاصطناعي' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <h6 class="mt-4">{{ $l == 'en' ? 'Judging Panel' : 'لجنة التحكيم' }}</h6>
                            <p>
                                {{ $l == 'en' ? 'The judging panel consists of industry leaders and investors to evaluate projects and select winners.' : 'تتكون لجنة التحكيم من قادة الصناعة والمستثمرين لتقييم المشاريع واختيار الفائزين.' }}
                            </p>
                            <div class="row" id="hackathon-judges-list">
                                <div class="col-md-4 mb-3">
                                    <div class="card text-center">
                                        <img src="https://via.placeholder.com/150.png?text=Judge+1"
                                            class="card-img-top rounded-circle mx-auto mt-3" alt="Judge 1"
                                            style="width:100px; height:100px; object-fit: cover;">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                {{ $l == 'en' ? 'Ms. Sarah Mohammed' : 'أ. سارة محمد' }}
                                            </h6>
                                            <p class="card-text small text-muted">
                                                {{ $l == 'en' ? 'Investor and Entrepreneur' : 'مستثمرة ورائدة أعمال' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @if($companyStep?->stype  != 1)
                <!-- Teams Tab (conditionally shown) -->
                
                <div class="tab-pane fade" id="teams" role="tabpanel" aria-labelledby="teams-tab-btn">
                    <div class="card">
                        <div class="card-body">
                            @if(count($teams) == 0)
                            <h5 class="card-title">
                                {{ $l == 'en' ? 'Team Formation and Joining' : 'تكوين الفرق والانضمام إليها' }}
                            </h5>
                            <p>
                                {{ $l == 'en' ? 'You can now create a new team or join an existing team. Maximum team members is '. $count_team . '.' : 'يمكنك الآن تكوين فريق جديد أو الانضمام إلى فريق قائم. الحد الأقصى لأعضاء الفريق هو'. $count_team .'.' }}
                            </p>
                            @else
                            <h5 class="card-title">
                                {{ $l == 'en' ? 'Your Teams' : 'الفرق التي أنت فيها' }}
                            </h5>
                            <p>
                                {{ $l == 'en' ? 'You are currently part of the following team(s). You can leave a team or join another one.' : 'أنت حالياً جزء من الفريق(الفرق) التالية. يمكنك مغادرة فريق أو الانضمام إلى آخر.' }}
                            </p>
                            @endif
                            <div id="my-current-team" class="mb-4 p-3 border rounded bg-light" style="display:none;">
                                <h6>
                                    {{ $l == 'en' ? 'Your Current Team:' : 'فريقك الحالي:' }}
                                    <span id="current-team-name" class="fw-bold">{{ $l == 'en' ? 'Team Name' : 'اسم الفريق' }}</span>
                                </h6>
                                <ul id="current-team-members" class="list-unstyled">
                                    <li>
                                        <i class="bi bi-person-check-fill text-success me-2"></i>
                                        {{ $l == 'en' ? 'You (Team Leader)' : 'أنت (قائد الفريق)' }}
                                    </li>
                                    <li>
                                        <i class="bi bi-person-fill me-2"></i>
                                        {{ $l == 'en' ? 'Team Member 2' : 'عضو الفريق 2' }}
                                    </li>
                                </ul>
                                <a href="collaboration-space.html?hackathonId=1&teamId=123" class="btn btn-success btn-sm">
                                    <i class="bi bi-chat-dots-fill me-2"></i>
                                    {{ $l == 'en' ? 'Team Workspace' : 'مساحة عمل الفريق' }}
                                </a>
                                <button class="btn btn-danger btn-sm" onclick="leaveTeam()">
                                    <i class="bi bi-box-arrow-left me-2"></i>
                                    {{ $l == 'en' ? 'Leave Team' : 'مغادرة الفريق' }}
                                </button>
                            </div>
                           
                            <div id="team-actions">
                                @if(count($teams) > 0)
                                    @php
                                        $isteamleader = $teams->where('tm_isTeamLeader', 1)->first();
                                        $teamsMembers = $teams->where('tm_isTeamLeader', 0);
                                        $teamUser = $teams->where('tm_user_id', Auth::user()->id);
                                        $teams_with_user = $teams->where('tm_team_formation_id', $teamUser->first()?->tm_team_formation_id);
                                        $isleader = $teams_with_user->where('tm_isTeamLeader', 1)->first();
                                    @endphp
                                   @if($teamUser->count() > 0)
                                        @if($teams_with_user != null)
                                        <h6 class="mt-4">{{ $l == 'en' ? 'Your Current Team:' : 'فريقك الحالي:' }} <span class="fw-bold">{{$teamUser->first()->tf_name}}</span></h6>
                                    
                                        <ul class="list-unstyled">
                                            
                                            <hr>
                                            @foreach($teams_with_user as $team)
                                                <li>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <a class="media-left" href="/dashboard/users/{{ $team->tm_user_id }}/edit">
                                                            <img src="{{ $team->u_img_url }}" alt="avatar" class="rounded-circle me-3"
                                                                height="40" width="40">
                                                        </a>
                                                        <a href="/dashboard/users/{{ $team->tm_user_id }}/edit">
                                                            <div class="media-body">
                                                                <h6 class="media-heading mb-0 text-nowrap">{{ $team->{'user_name_'.$l} }}</h6>
                                                                <span class="font-small-2 text-nowrap">{{$team->tf_name }}</span>
                                                            </div>
                                                        </a>
                                                        @if($team->tm_isTeamLeader == 1)
                                                    {{ $l == 'en' ? '(Team Leader)' : '(قائد الفريق)' }}
                                                    @endif
                                                    </div>
                                                    
                                                </li>
                                            @endforeach
                                        </ul>

                                        @endif

                                        <a href="collaboration-space.html?hackathonId=1&teamId=123" class="btn btn-success btn-sm">
                                            <i class="bi bi-chat-dots-fill me-2"></i>
                                            {{ $l == 'en' ? 'Team Workspace' : 'مساحة عمل الفريق' }}
                                        </a>
                                    @endif
                                    
                                    @else
                                    @if($allteams->first()['tm_acceptInvition'] == null)
                                <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                                    <i class="bi bi-plus-circle-fill me-2"></i>
                                    {{ $l == 'en' ? 'Create New Team' : 'تكوين فريق جديد' }}
                                </button>
                                @endif
                                @endif
                                
                                <h6 class="mt-4">{{ $l == 'en' ? 'Join Existing Team:' : 'الانضمام إلى فريق قائم:' }}</h6>

                                <div class="list-group" id="available-teams-list">

                                    @foreach($allteams as $team)
                                    
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $team['tf_name'] }}</strong>
                                            ({{ $team['members_count'] }}/{{ $count_team }} {{ $l == 'en' ? 'members' : 'أعضاء' }})
                                            <br>
                                            <small class="text-muted">
                                                {{ $team['tf_description'] }}
                                            </small>
                                        </div>

                                        @if($team['members_count'] < $count_team)
                                        @if($team['tm_user_id']!=Auth::user()->id)
                                       @php
                                       $hasJoin = $team_invition->contains('tm_acceptInvition', 'join');
                                        $hasAccepted = $team_invition->contains('tm_acceptInvition', 'accepted');
                                        $allRejected = $team_invition->every(function($item) {
                                            return $item->tm_acceptInvition === 'rejected';
                                        });
                                       @endphp
                                        @if(!$hasJoin && !$hasAccepted && $allRejected)
                                        
                                        <button class="btn btn-outline-primary btn-sm" onclick="joinTeam('{{ $team['tf_id'] }}','{{ $team['tf_name'] }}')">
                                            {{ $l == 'en' ? 'Request to Join' : 'طلب انضمام' }}
                                            <i class="bi bi-person-plus-fill ms-2"></i>
                                        </button>
                                        @else
                                        {{-- <button class="btn btn-outline-primary btn-sm" disabled>
                                            {{ $l == 'en' ? 'Request to Join' : 'طلب انضمام' }}
                                            <i class="bi bi-person-plus-fill ms-2"></i>
                                        </button> --}}
                                        @endif
                                        @endif
                                        @else
                                        <button class="btn btn-outline-primary btn-sm" disabled>
                                            {{ $l == 'en' ? 'Request to Join' : 'طلب انضمام' }}
                                            <i class="bi bi-person-plus-fill ms-2"></i>
                                        </button>
                                        @endif
                                    </div>
                                    @endforeach


                                    {{-- <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $l == 'en' ? 'Team Stars' : 'فريق النجوم' }}</strong>
                                            (4/5 {{ $l == 'en' ? 'members' : 'أعضاء' }})
                                            <br>
                                            <small class="text-muted">
                                                {{ $l == 'en' ? 'Complete team looking for an additional member.' : 'فريق متكامل يبحث عن عضو إضافي.' }}
                                            </small>
                                        </div>
                                        <button class="btn btn-outline-primary btn-sm" onclick="joinTeam('{{ $l == 'en' ? 'Stars' : 'النجوم' }}')">
                                            {{ $l == 'en' ? 'Request to Join' : 'طلب انضمام' }}
                                            <i class="bi bi-person-plus-fill me-2"></i>
                                        </button>
                                    </div> --}}
                                </div>
                                

                            </div>
                        </div>
                    </div>
                </div>
           @endif
                <!-- Submission Tab (conditionally shown) -->
                <div class="tab-pane fade" id="submission" role="tabpanel" aria-labelledby="submission-tab-btn">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $l == 'en' ? 'Project Submission' : 'رفع المشاريع والحلول' }}
                            </h5>
                            <p>
                                {{ $l == 'en' ? 'Please upload your team\'s project before the hackathon deadline. Make sure to include all required files and a clear presentation.' : 'يرجى رفع مشروع فريقك قبل انتهاء وقت الهاكثون. تأكد من تضمين جميع الملفات المطلوبة وعرض تقديمي واضح.' }}
                            </p>
                            <div id="submission-status-message" class="alert alert-info">
                                {{ $l == 'en' ? 'You can upload your project now. Deadline: March 17, 2024, 12:00 PM.' : 'يمكنك رفع مشروعك الآن. الموعد النهائي: 17 مارس 2024, 12:00 ظهراً.' }}
                            </div>
                            <div id="already-submitted-message" class="alert alert-success" style="display:none;">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                {{ $l == 'en' ? 'Your project has been uploaded successfully! You can update it until the deadline.' : 'تم رفع مشروعكم بنجاح! يمكنكم تحديثه حتى الموعد النهائي.' }}
                                <p class="mt-2">
                                    <strong>{{ $l == 'en' ? 'Uploaded Project:' : 'المشروع المرفوع:' }}</strong>
                                    MyAwesomeSolution.zip (2.5MB) -
                                    {{ $l == 'en' ? 'Upload Date: March 16, 2024, 05:30 PM' : 'تاريخ الرفع: 16 مارس 2024, 05:30 مساءً' }}
                                </p>
                            </div>
                            <form id="projectSubmissionForm" class="mt-3">
                                <div class="mb-3">
                                    <label for="projectName" class="form-label">
                                        {{ $l == 'en' ? 'Project Name' : 'اسم المشروع' }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="projectName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="projectDescription" class="form-label">
                                        {{ $l == 'en' ? 'Brief Project Description' : 'وصف مختصر للمشروع' }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="projectDescription" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="projectFiles" class="form-label">
                                        {{ $l == 'en' ? 'Project Files (single compressed file: zip, rar)' : 'ملفات المشروع (ملف مضغوط واحد: zip, rar)' }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input class="form-control" type="file" id="projectFiles" accept=".zip,.rar" required>
                                    <small class="form-text text-muted">
                                        {{ $l == 'en' ? 'Maximum size: 25MB' : 'الحد الأقصى للحجم: 25MB' }}
                                    </small>
                                </div>
                                <div class="mb-3">
                                    <label for="projectLink" class="form-label">
                                        {{ $l == 'en' ? 'Presentation Link (Google Slides, YouTube, etc.) (Optional)' : 'رابط العرض التقديمي (Google Slides, YouTube, etc.) (اختياري)' }}
                                    </label>
                                    <input type="url" class="form-control" id="projectLink" placeholder="https://">
                                </div>
                                <div class="mb-3">
                                    <label for="projectRepo" class="form-label">
                                        {{ $l == 'en' ? 'Code Repository Link (GitHub, GitLab, etc.) (Optional)' : 'رابط مستودع الكود (GitHub, GitLab, etc.) (اختياري)' }}
                                    </label>
                                    <input type="url" class="form-control" id="projectRepo" placeholder="https://">
                                </div>
                                <div class="progress mb-3" style="display:none;" id="uploadProgressBarContainer">
                                    <div id="uploadProgressBar"
                                        class="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
                                        aria-valuemax="100">0%</div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-cloud-upload-fill me-2"></i>
                                    {{ $l == 'en' ? 'Upload Project' : 'رفع المشروع' }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary ms-2" onclick="saveDraft()">
                                    {{ $l == 'en' ? 'Save as Draft' : 'حفظ كمسودة' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Results Tab (conditionally shown) -->
                <div class="tab-pane fade" id="results" role="tabpanel" aria-labelledby="results-tab-btn">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $l == 'en' ? 'Results and Winning Teams' : 'النتائج والفرق الفائزة' }}
                            </h5>
                            <div id="results-announcement" class="alert alert-success text-center">
                                <i class="bi bi-trophy-fill display-4 mb-3 me-2"></i>
                                <h4>{{ $l == 'en' ? 'Congratulations to the Winning Teams!' : 'تهانينا للفرق الفائزة!' }}</h4>
                                <p>
                                    {{ $l == 'en' ? 'We announce the winners of the Future of AI Hackathon:' : 'نعلن عن الفائزين في هاكثون مستقبل الذكاء الاصطناعي:' }}
                                </p>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-4 mb-3">
                                    <div class="card text-center border-warning shadow-sm">
                                        <div class="card-header bg-warning text-dark fw-bold">
                                            {{ $l == 'en' ? 'First Place' : 'المركز الأول' }}
                                            <i class="bi bi-award-fill me-2"></i>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                {{ $l == 'en' ? 'Team Innovative Minds' : 'فريق العقول المبتكرة' }}
                                            </h5>
                                            <p class="card-text">
                                                {{ $l == 'en' ? 'Project: Smart Medical Diagnosis System' : 'مشروع: نظام تشخيص طبي ذكي' }}
                                            </p>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#solutionDetailsModal"
                                                data-team="{{ $l == 'en' ? 'Innovative Minds' : 'العقول المبتكرة' }}"
                                                data-project="{{ $l == 'en' ? 'Smart Medical Diagnosis System' : 'نظام تشخيص طبي ذكي' }}">
                                                {{ $l == 'en' ? 'Solution Details' : 'تفاصيل الحل' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card text-center border-secondary shadow-sm">
                                        <div class="card-header bg-light fw-bold">
                                            {{ $l == 'en' ? 'Second Place' : 'المركز الثاني' }}
                                            <i class="bi bi-award me-2"></i>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                {{ $l == 'en' ? 'Team Green Code' : 'فريق الكود الأخضر' }}
                                            </h5>
                                            <p class="card-text">
                                                {{ $l == 'en' ? 'Project: Energy Consumption Management Platform' : 'مشروع: منصة لإدارة استهلاك الطاقة' }}
                                            </p>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#solutionDetailsModal"
                                                data-team="{{ $l == 'en' ? 'Green Code' : 'الكود الأخضر' }}"
                                                data-project="{{ $l == 'en' ? 'Energy Consumption Management Platform' : 'منصة لإدارة استهلاك الطاقة' }}">
                                                {{ $l == 'en' ? 'Solution Details' : 'تفاصيل الحل' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card text-center shadow-sm" style="border-color: #cd7f32;">
                                        <div class="card-header fw-bold"
                                            style="background-color: #f0e6d1; color: #cd7f32;">
                                            {{ $l == 'en' ? 'Third Place' : 'المركز الثالث' }}
                                            <i class="bi bi-award me-2"></i>
                                        </div>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                {{ $l == 'en' ? 'Team Future Data' : 'فريق بيانات المستقبل' }}
                                            </h5>
                                            <p class="card-text">
                                                {{ $l == 'en' ? 'Project: AI Financial Analysis Tool' : 'مشروع: أداة تحليل مالي بالذكاء الاصطناعي' }}
                                            </p>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                data-bs-target="#solutionDetailsModal"
                                                data-team="{{ $l == 'en' ? 'Future Data' : 'بيانات المستقبل' }}"
                                                data-project="{{ $l == 'en' ? 'AI Financial Analysis Tool' : 'أداة تحليل مالي بالذكاء الاصطناعي' }}">
                                                {{ $l == 'en' ? 'Solution Details' : 'تفاصيل الحل' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h6 class="mt-3">
                                {{ $l == 'en' ? 'Other Outstanding Projects:' : 'المشاريع المتميزة الأخرى:' }}
                            </h6>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    {{ $l == 'en' ? 'Team "Tech Innovators" - Project "Smart City Traffic Flow"' : 'فريق "Tech Innovators" - مشروع "Smart City Traffic Flow"' }}
                                </li>
                                <li class="list-group-item">
                                    {{ $l == 'en' ? 'Team "AI Avengers" - Project "AI Powered Education Platform"' : 'فريق "AI Avengers" - مشروع "AI Powered Education Platform"' }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                 <!-- Results Tab (conditionally shown) -->
                <div class="tab-pane fade" id="submissions" role="tabpanel" aria-labelledby="submissions-tab-btn">
                    <div class="card">

                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $l == 'en' ? 'Former Submissions Requests' : 'طلبات تقديم الشركات' }}
                            </h5>
                           <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ $l == 'en' ? 'Submitter' : 'المستخدم' }}</th>
                                        <th scope="col">{{ $l == 'en' ? 'Email' : 'البريد الاليكترونى' }}</th>
                                        <th scope="col">{{ $l == 'en' ? 'Date of Submission' : 'تاريخ التقديم' }}</th>
                                        <th scope="col">{{ $l == 'en' ? 'View Details' : 'عرض التفاصيلs' }}</th>
                                        <th scope="col">{{ $l == 'en' ? 'Actions' : 'الإجراءات' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $submission)
                                       
                                                <tr>
                                                    <td>{{ $submission->user_name }}</td>
                                                    <td>{{ $submission->email }}</td>
                                                    <td>{{ $submission->submissionDate }}</td>
                                                    <td><a target="_blank" title="view" href="/dashboard/hackathon/viewSubmission/{{ $submission->step_id }}/{{ $submission->user_id }}">{{ $l == 'en' ? 'View' : 'عرض' }} </a></td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-1">
                                                            <select id="cstep" class="select form-control"
                                                                onchange="changestep({{ $submission->company_id }},this.options[this.selectedIndex].value)">
                                                                <option value="0">{{ __('cycles.Disapprove') }}</option>
                                                                @foreach (\App\Step::where('cycle_id', $submission->cycle_id)->orderBy('arr')->get() as $step)
                                                                <option value="{{ $step->id }}"
                                                                    {{ $step->id == $submission->company_step ? 'selected' : '' }}>
                                                                    {{ $step->title }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>
                                                </tr>
                                          
                                    @endforeach
                                </tbody>
                            </table>
                           
                    
                            
                        </div>
                       
                    </div>
                </div>

            </div>
        </div>

        @if($companyStep?->stype  == 6)
        <!-- Create Team Modal -->
        <div class="modal fade" id="createTeamModal" tabindex="-1" aria-labelledby="createTeamModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createTeamModalLabel">
                            {{ $l == 'en' ? 'Create New Team' : 'تكوين فريق جديد' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="/dashboard/hackathon/createTeam">
                            @csrf
                            <input type="hidden" name="step_id" value="{{ $currentHackathon['step_id'] }}">
                            <input type="hidden" name="cycle_id" value="{{ $id }}">
                            <div class="mb-3">
                                <label for="teamName" class="form-label">
                                    {{ $l == 'en' ? 'Team Name' : 'اسم الفريق' }}
                                </label>
                                <input type="text" class="form-control" name="team_name" value="" id="teamName" required>
                            </div>
                            <div class="mb-3">
                                <label for="teamDescription" class="form-label">
                                    {{ $l == 'en' ? 'Brief Team/Idea Description (Optional)' : 'وصف مختصر للفريق/الفكرة (اختياري)' }}
                                </label>
                                <textarea class="form-control" name="description" id="teamDescription" rows="2"></textarea>
                            </div>

                            <hr>

                            {{-- <div class="mb-3">
                                <label for="Project_Name_en" class="form-label">
                                    {{ __('admin.Project_Name') ." " . __('admin.English') }}
                                </label>
                                <input type="text" class="form-control" name="Project_Name_en" value="" id="Project_Name_en" >
                            </div>
                            <div class="mb-3">
                                <label for="Project_Name_ar" class="form-label">
                                    {{__('admin.Project_Name') ." " . __('admin.Arabic') }}
                                </label>
                                <input type="text" class="form-control" name="Project_Name_ar" value="" id="Project_Name_ar" >
                            </div> --}}

                            {{-- <div class="mb-3">
                                <label for="Project_Description_en" class="form-label">
                                    {{ __('admin.Project_Description') ." " . __('admin.English') }}
                                </label>
                                <textarea class="form-control" name="Project_Description_en" id="Project_Description_en" rows="2"></textarea>
                            </div>

                             <div class="mb-3">
                                <label for="Project_Description_ar" class="form-label">
                                    {{ __('admin.Project_Description') ." " .  __('admin.Arabic') }}
                                </label>
                                <textarea class="form-control" name="Project_Description_ar" id="Project_Description_ar" rows="2"></textarea>
                            </div> --}}

                            {{-- <div class="mb-3">
                                <label for="Target_Track" class="form-label">{{ $l == 'en' ? 'Target Track' : 'المحور المستهدف' }}</label>
                                <select class="form-select" id="Target_Track" name="Target_Track" >
                                    <option value="" >{{ $l == 'en' ? 'Choose track...' : 'اختر المحور...' }}</option>
                                    @for ($i = 5; $i <= 100; $i += 5)
                                        <option value="{{ $i }}" {{ old('TargetTrack', $selected ?? '') == $i ? 'selected' : '' }}>{{ $i  .'%'}}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="Project_Status" class="form-label">{{ $l == 'en' ? 'Target Track' : 'المحور المستهدف' }}</label>
                                <select class="form-select" id="Project_Status" name="Project_Status">
                                    <option value="" >{{ $l == 'en' ? 'Choose Status...' : 'اختر الحالة...' }}</option>
                                    @for ($s = 0; $s <= 100; $s += 25)
                                        <option value="{{ $s }}" {{ old('Project_Status', $selected ?? '') == $s ? 'selected' : '' }}>{{ $s .'%' }}</option>
                                    @endfor
                                </select>
                            </div> --}}


                       
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                                    {{ $l == 'en' ? 'Cancel' : 'إلغاء' }}
                                </button>
                                <button type="submit"  class="btn btn-primary" >
                                    {{ $l == 'en' ? 'Create Team' : 'إنشاء الفريق' }}
                                </button>
                            </div>
                     </form>
                </div>
            </div>
        </div>
        @endif
        <!-- Solution Details Modal -->
        <div class="modal fade" id="solutionDetailsModal" tabindex="-1" aria-labelledby="solutionDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="solutionDetailsModalLabel">
                            {{ $l == 'en' ? 'Solution Details' : 'تفاصيل الحل' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h4 id="modalTeamName">{{ $l == 'en' ? 'Team Name' : 'اسم الفريق' }}</h4>
                        <h6 id="modalProjectName" class="text-muted mb-3">{{ $l == 'en' ? 'Project Name' : 'اسم المشروع' }}</h6>
                        <p><strong>{{ $l == 'en' ? 'Description:' : 'الوصف:' }}</strong></p>
                        <p id="modalProjectDescription">
                            {{ $l == 'en' ? 'This is a detailed description of the solution provided by the team. It explains the problem being addressed, the technologies used, and the expected outcomes...' : 'هذا وصف تفصيلي للحل المقدم من الفريق. يشرح المشكلة التي يعالجها، والتقنيات المستخدمة، والنتائج المتوقعة...' }}
                        </p>
                        <p>
                            <strong>{{ $l == 'en' ? 'Technologies Used:' : 'التقنيات المستخدمة:' }}</strong>
                            Python, TensorFlow, React, AWS
                        </p>
                        <p>
                            <strong>{{ $l == 'en' ? 'Presentation Link:' : 'رابط العرض التقديمي:' }}</strong>
                            <a href="#" target="_blank">{{ $l == 'en' ? 'Click here' : 'اضغط هنا' }}</a>
                        </p>
                        <p>
                            <strong>{{ $l == 'en' ? 'Code Repository Link:' : 'رابط مستودع الكود:' }}</strong>
                            <a href="#" target="_blank">{{ $l == 'en' ? 'Click here' : 'اضغط هنا' }}</a>
                        </p>
                        <img src="https://via.placeholder.com/700x400.png?text=Project+Screenshot"
                            class="img-fluid rounded mt-3" alt="Screenshot">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                            {{ $l == 'en' ? 'Close' : 'إغلاق' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Apply Modal (same as in index.html, can be a shared component in a real app) -->
    <div class="modal fade" id="applyModalHackathonDetails" tabindex="-1" aria-labelledby="applyModalHackathonDetailsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="applyModalHackathonDetailsLabel">
                        {{ $l == 'en' ? 'Submit Application' : 'تقديم طلب الانضمام' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        {{ $l == 'en' ? 'You are about to submit an application to join this hackathon. Please review the hackathon terms and conditions before proceeding.' : 'أنت على وشك تقديم طلب للانضمام إلى هذا الهاكثون. يرجى مراجعة شروط وأحكام الهاكثون قبل المتابعة.' }}
                    </p>
                    <form>
                        <div class="mb-3">
                            <label for="dh-applicantName" class="form-label">
                                {{ $l == 'en' ? 'Full Name' : 'الاسم الكامل' }}
                            </label>
                            <input type="text" class="form-control" id="dh-applicantName"
                                value="{{ $l == 'en' ? 'Entrepreneur (from profile)' : 'رائد أعمال (من الملف الشخصي)' }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="dh-motivation" class="form-label">
                                {{ $l == 'en' ? 'Why do you want to participate? (Optional)' : 'لماذا ترغب في المشاركة؟ (اختياري)' }}
                            </label>
                            <textarea class="form-control" id="dh-motivation" rows="3"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="dh-termsAndConditions" required>
                            <label class="form-check-label" for="dh-termsAndConditions">
                                {{ $l == 'en' ? 'I agree to the' : 'أوافق على' }}
                                <a href="#terms" data-bs-toggle="modal" data-bs-target="#termsModalDetails">
                                    {{ $l == 'en' ? 'Terms and Conditions' : 'الشروط والأحكام' }}
                                </a>.
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        {{ $l == 'en' ? 'Close' : 'إغلاق' }}
                    </button>
                    <button type="button" class="btn btn-primary" onclick="submitApplicationDetails()">
                        {{ $l == 'en' ? 'Confirm Application' : 'تأكيد التقديم' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Terms Modal (nested for hackathon details page) -->
    <div class="modal fade" id="termsModalDetails" tabindex="-1" aria-labelledby="termsModalDetailsLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalDetailsLabel">
                        {{ $l == 'en' ? 'Hackathon Terms and Conditions' : 'شروط وأحكام الهاكثون' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ $l == 'en' ? 'This is a placeholder text for terms and conditions...' : 'هذا نص وهمي للشروط والأحكام...' }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-target="#applyModalHackathonDetails"
                        data-bs-toggle="modal" data-bs-dismiss="modal">
                        {{ $l == 'en' ? 'I Agree and Return to Application' : 'أوافق وأعود للتقديم' }}
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Include original footer -->
    <footer class="footer mt-auto py-3 bg-dark text-white">
        <div class="container text-center">
            <span>
                © 2024
                {{ $l == 'en' ? 'Incubator Hackathon Platform. All rights reserved.' : 'منصة المسرعة للهاكثونات. جميع الحقوق محفوظة.' }}
            </span>
        </div>
    </footer>
    <!-- Core JS -->
    <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
    <script src="/vuexy/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="/vuexy/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="/vuexy/assets/vendor/libs/i18n/i18n.js"></script>
    <script src="/vuexy/assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="/vuexy/assets/vendor/js/menu.js"></script>
    <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
    <script src="/vuexy/assets/js/main.js"></script>
    <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
    <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>

    <!-- Custom JS -->
    <script src="/vuexy/assets/js/forms-selects.js"></script>

    <script>
        // Mock data - in a real app, this would come from a server
        const hackathonData = {
            1: {
                title: "{{ $l == 'en' ? 'Future of AI Hackathon' : 'هاكثون مستقبل الذكاء الاصطناعي' }}",
                dates: "{{ $l == 'en' ? 'March 15 - 17, 2024' : '15 - 17 مارس 2024' }}",
                location: "{{ $l == 'en' ? 'Virtual' : 'افتراضي' }}",
                status: "upcoming", // upcoming, ongoing, submission, judging, finished
                applicationDeadline: new Date("2024-03-10T23:59:59"),
                startDate: new Date("2024-03-15T09:00:00"),
                submissionDeadline: new Date("2024-03-17T12:00:00"),
            },
            2: {
                title: "{{ $l == 'en' ? 'Green Sustainability Solutions Hackathon' : 'هاكثون حلول الاستدامة الخضراء' }}",
                dates: "{{ $l == 'en' ? 'March 01 - 03, 2024 (Ongoing)' : '01 - 03 مارس 2024 (جاري الآن)' }}",
                location: "{{ $l == 'en' ? 'Hybrid (Riyadh and Online)' : 'هجين (الرياض وعبر الإنترنت)' }}",
                status: "ongoing",
                applicationDeadline: new Date("2024-02-28T23:59:59"),
                startDate: new Date("2024-03-01T09:00:00"),
                submissionDeadline: new Date("2024-03-03T12:00:00"),
            },
            3: {
                title: "{{ $l == 'en' ? 'FinTech Hackathon 2023' : 'هاكثون التقنية المالية 2023' }}",
                dates: "{{ $l == 'en' ? 'November 10 - 12, 2023' : '10 - 12 نوفمبر 2023' }}",
                location: "{{ $l == 'en' ? 'Riyadh' : 'الرياض' }}",
                status: "finished",
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const hackathonId = urlParams.get('id') || '1'; // Default to 1 if no ID
            const isPast = urlParams.get('past') === 'true';
            const applied = urlParams.get('applied') === 'true';
            const hasTeam = urlParams.get('teamCreated') === 'true';
            const submissionOpen = urlParams.get('submissionOpen') === 'true';
            const resultsAnnounced = urlParams.get('results') === 'true';

            const currentHackathon = hackathonData[hackathonId];

            if (currentHackathon) {
                document.getElementById('hackathon-breadcrumb-title').textContent = currentHackathon.title;
                document.getElementById('hackathon-main-title').textContent = currentHackathon.title;
                document.getElementById('hackathon-dates-location').innerHTML = `<i class="bi bi-calendar-event me-2"></i> ${currentHackathon.dates} | <i class="bi bi-geo-alt-fill me-2"></i> ${currentHackathon.location}`;

                // Update Apply Modal Title
                const applyModalTitle = document.getElementById('applyModalHackathonDetailsLabel');
                if (applyModalTitle) {
                    applyModalTitle.textContent = `{{ $l == 'en' ? 'Apply to join' : 'تقديم طلب الانضمام لـ' }} ${currentHackathon.title}`;
                }

                // Action Buttons & Tab Visibility Logic
                const actionButtonsPlaceholder = document.getElementById('action-buttons-placeholder');
                const teamsTabLi = document.getElementById('teams-tab-li');
                const submissionTabLi = document.getElementById('submission-tab-li');
                const resultsTabLi = document.getElementById('results-tab-li');
                const countdownContainer = document.getElementById('countdown-timer-container');
                const submissionCountdownContainer = document.getElementById('submission-countdown-timer-container');

                if (isPast || currentHackathon.status === 'finished' || resultsAnnounced) {
                    actionButtonsPlaceholder.innerHTML = `<a href="#results" class="btn btn-info btn-lg fw-bold"><i class="bi bi-award-fill me-2"></i> {{ $l == 'en' ? 'View Results' : 'عرض النتائج' }}</a>`;
                    resultsTabLi.style.display = 'list-item';
                    var resultsTab = new bootstrap.Tab(document.getElementById('results-tab-btn'));
                    resultsTab.show();
                    if (teamsTabLi) teamsTabLi.style.display = 'none';
                    if (submissionTabLi) submissionTabLi.style.display = 'none';
                } else if (applied || hasTeam || currentHackathon.status === 'ongoing' || currentHackathon.status === 'submission' || submissionOpen) {
                    let teamButtonHTML = `<a href="my-team.html?hackathonId=${hackathonId}" class="btn btn-warning btn-lg fw-bold me-2"><i class="bi bi-people-fill me-2"></i> {{ $l == 'en' ? 'Manage My Team' : 'إدارة فريقي' }}</a>`;
                    if (hasTeam) {
                        document.getElementById('my-current-team').style.display = 'block';
                        document.getElementById('current-team-name').textContent = "{{ $l == 'en' ? 'Team Heroes' : 'فريق الأبطال' }}";
                        document.getElementById('team-actions').style.display = 'none';
                    }

                    let submissionButtonHTML = '';
                    if (currentHackathon.status === 'submission' || submissionOpen) {
                        submissionButtonHTML = `<button class="btn btn-success btn-lg fw-bold" onclick="showSubmissionTab()"><i class="bi bi-upload me-2"></i> {{ $l == 'en' ? 'Upload Project' : 'رفع المشروع' }}</button>`;
                        submissionTabLi.style.display = 'list-item';
                        startCountdown(currentHackathon.submissionDeadline, 'submission-countdown-timer', submissionCountdownContainer, "{{ $l == 'en' ? 'to submit projects' : 'لتقديم المشاريع' }}");

                        if (urlParams.get('submitted') === 'true') {
                            document.getElementById('already-submitted-message').style.display = 'block';
                            document.getElementById('projectSubmissionForm').style.display = 'none';
                        }
                    } else {
                        startCountdown(currentHackathon.submissionDeadline, 'submission-countdown-timer', submissionCountdownContainer, "{{ $l == 'en' ? 'to open project submission' : 'لفتح باب تقديم المشاريع' }}");
                    }

                    actionButtonsPlaceholder.innerHTML = teamButtonHTML + submissionButtonHTML;
                    teamsTabLi.style.display = 'list-item';
                    if (!submissionOpen && !resultsAnnounced) {
                        var teamTabToShow = new bootstrap.Tab(document.getElementById('teams-tab-btn'));
                        teamTabToShow.show();
                    }
                } else if (currentHackathon.status === 'upcoming') {
                    actionButtonsPlaceholder.innerHTML = `<button type="button" class="btn btn-primary btn-lg fw-bold" data-bs-toggle="modal" data-bs-target="#applyModalHackathonDetails"><i class="bi bi-pencil-square me-2"></i> {{ $l == 'en' ? 'Apply Now!' : 'قدم الآن!' }}</button>`;
                    startCountdown(currentHackathon.startDate, 'countdown-timer', countdownContainer, "{{ $l == 'en' ? 'to start hackathon' : 'لبدء الهاكثون' }}");
                }

                // Handle countdowns
                if (currentHackathon.status === "upcoming" && currentHackathon.startDate) {
                    startCountdown(currentHackathon.startDate, 'countdown-timer', countdownContainer, "{{ $l == 'en' ? 'to start hackathon' : 'لبدء الهاكثون' }}");
                }
                if ((currentHackathon.status === "ongoing" || currentHackathon.status === "submission") && currentHackathon.submissionDeadline) {
                    startCountdown(currentHackathon.submissionDeadline, 'submission-countdown-timer', submissionCountdownContainer, "{{ $l == 'en' ? 'to submit projects' : 'لتقديم المشاريع' }}");
                }
            } else {
                document.getElementById('hackathon-main-title').textContent = "{{ $l == 'en' ? 'Hackathon not found' : 'الهاكثون غير موجود' }}";
                document.querySelector('.tab-content').innerHTML = "<p class='text-center'>{{ $l == 'en' ? 'Sorry, hackathon details not found.' : 'عفواً، لم يتم العثور على تفاصيل هذا الهاكثون.' }}</p>";
                document.querySelector('.nav-tabs').style.display = 'none';
            }

            // Solution Details Modal
            var solutionModal = document.getElementById('solutionDetailsModal')
            if (solutionModal) {
                solutionModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget
                    var team = button.getAttribute('data-team')
                    var project = button.getAttribute('data-project')

                    var modalTitle = solutionModal.querySelector('.modal-title')
                    var modalTeamName = solutionModal.querySelector('#modalTeamName')
                    var modalProjectName = solutionModal.querySelector('#modalProjectName')

                    modalTitle.textContent = '{{ $l == "en" ? "Solution details for team: " : "تفاصيل حل فريق: " }}' + team
                    modalTeamName.textContent = team
                    modalProjectName.textContent = project
                })
            }
        });

        function submitApplicationDetails() {
            if (!document.getElementById('dh-termsAndConditions').checked) {
                alert('{{ $l == "en" ? "Please agree to the terms and conditions." : "يرجى الموافقة على الشروط والأحكام." }}');
                return;
            }
            alert('{{ $l == "en" ? "Your application has been sent successfully! You can now create your team." : "تم إرسال طلبك بنجاح! يمكنك الآن تكوين فريقك." }}');
            var applyModal = bootstrap.Modal.getInstance(document.getElementById('applyModalHackathonDetails'));
            applyModal.hide();
            const url = new URL(window.location);
            url.searchParams.set('applied', 'true');
            window.location.href = url.href;
        }

        // function createTeam() {
        //     const teamName = document.getElementById('teamName').value;
        //     if (!teamName.trim()) {
        //         alert('{{ $l == "en" ? "Please enter a team name." : "يرجى إدخال اسم للفريق." }}');
        //         return;
        //     }
        //     alert(`{{ $l == "en" ? "Team created successfully!" : "تم إنشاء فريق" }} "${teamName}" {{ $l == "en" ? "" : "بنجاح!" }}`);
        //     var createTeamModal = bootstrap.Modal.getInstance(document.getElementById('createTeamModal'));
        //     createTeamModal.hide();
        //     const url = new URL(window.location);
        //     url.searchParams.set('teamCreated', 'true');
        //     window.location.href = url.href;
        // }

        function joinTeam(id, teamName) {
        if (confirm(`{{ $l == "en" ? "Are you sure you want to request to join team" : "هل أنت متأكد أنك تريد طلب الانضمام إلى فريق" }} "${teamName}"{{ $l == "en" ? "?" : "؟" }}`)) {
            
            fetch("{{ route('hackathon.RequestJoinTeam') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    id: id
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json(); // أو response.text() حسب ما يرجع الـ controller
            })
            .then(data => {
                alert(`{{ $l == "en" ? "Join request sent to team" : "تم إرسال طلب الانضمام إلى فريق" }} "${teamName}".`);
                // إعادة توجيه أو تحديث
                window.location.href = window.location.href;
            })
            .catch(error => {
                alert("{{ $l == 'en' ? 'There was an error sending the request.' : 'حدث خطأ أثناء إرسال الطلب.' }}");
                console.error(error);
            });
        }
    }

        function leaveTeam() {
            if (confirm(`{{ $l == "en" ? "Are you sure you want to leave the current team?" : "هل أنت متأكد أنك تريد مغادرة الفريق الحالي؟" }}`)) {
                alert(`{{ $l == "en" ? "You have left the team." : "لقد غادرت الفريق." }}`);
                const url = new URL(window.location);
                url.searchParams.delete('teamCreated');
                url.searchParams.delete('joinedTeamName');
                window.location.href = url.href;
            }
        }

        function showSubmissionTab() {
            var submissionTab = new bootstrap.Tab(document.getElementById('submission-tab-btn'));
            submissionTab.show();
            document.getElementById('submission').scrollIntoView({ behavior: 'smooth' });
        }

        document.getElementById('projectSubmissionForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const projectName = document.getElementById('projectName').value;
            const projectFiles = document.getElementById('projectFiles').files;

            if (!projectName.trim() || projectFiles.length === 0) {
                alert("{{ $l == 'en' ? 'Please fill in the project name and upload the required files.' : 'يرجى تعبئة اسم المشروع ورفع الملفات المطلوبة.' }}");
                return;
            }

            // Simulate upload
            const progressBarContainer = document.getElementById('uploadProgressBarContainer');
            const progressBar = document.getElementById('uploadProgressBar');
            progressBarContainer.style.display = 'flex';
            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                progressBar.style.width = progress + '%';
                progressBar.textContent = progress + '%';
                progressBar.setAttribute('aria-valuenow', progress);
                if (progress >= 100) {
                    clearInterval(interval);
                    progressBarContainer.style.display = 'none';
                    alert('{{ $l == "en" ? "Your project has been uploaded successfully!" : "تم رفع مشروعك بنجاح!" }}');
                    document.getElementById('already-submitted-message').style.display = 'block';
                    document.getElementById('projectSubmissionForm').style.display = 'none';
                    const url = new URL(window.location);
                    url.searchParams.set('submitted', 'true');
                    window.history.pushState({}, '', url);
                }
            }, 200);
        });

        function saveDraft() {
            alert("{{ $l == 'en' ? 'Project draft saved. You can return later to complete the upload.' : 'تم حفظ مسودة المشروع. يمكنك العودة لاحقاً لإكمال الرفع.' }}");
        }

        function startCountdown(targetDate, elementId, containerElement, typeText) {
            const countdownElement = document.getElementById(elementId);
            if (!countdownElement || !containerElement) return;

            const interval = setInterval(() => {
                const now = new Date().getTime();
                const distance = new Date(targetDate).getTime() - now;

                if (distance < 0) {
                    clearInterval(interval);
                    containerElement.classList.remove('alert-info', 'alert-warning');
                    containerElement.classList.add('alert-success');
                    countdownElement.innerHTML = `{{ $l == 'en' ? 'Time for' : 'حان وقت' }} ${typeText.replace("{{ $l == 'en' ? 'to ' : 'ل' }}", "")}{{ $l == 'en' ? '!' : '!' }}`;
                    
                    if (elementId === 'countdown-timer' && new URLSearchParams(window.location.search).get('id') === '1') {
                        const url = new URL(window.location);
                        url.searchParams.set('applied', 'true');
                    }
                    if (elementId === 'submission-countdown-timer') {
                        document.getElementById('submission-tab-li').style.display = 'list-item';
                    }
                    return;
                }
                containerElement.style.display = 'block';

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                countdownElement.innerHTML = `${days} {{ $l == 'en' ? 'days' : 'يوم' }} {{ $l == 'en' ? 'and' : 'و' }} ${hours} {{ $l == 'en' ? 'hours' : 'ساعة' }} {{ $l == 'en' ? 'and' : 'و' }} ${minutes} {{ $l == 'en' ? 'minutes' : 'دقيقة' }} {{ $l == 'en' ? 'and' : 'و' }} ${seconds} {{ $l == 'en' ? 'seconds' : 'ثانية' }}`;
            }, 1000);
        }

        function changestep(company, step) {
      if (confirm("{{ __('cycles.sure change step') }}")) {
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
        $.ajax({
          type: "POST",
          url: "/dashboard/screening/changestep",
          data: {
            "company": company,
            "step": step,
          },
          success: function(response) {
            // alert(response.ddddd);
            console.log(response);
            // location.href = '/dashboard/hackathon/details/{{ $id }}';
            location.reload();
          },
          error: function(error) {
            console.log("Error Occurred, arrange failed");
          }
        });
      } else {
        location.reload();
      }
    }
    </script>
</body>

</html>