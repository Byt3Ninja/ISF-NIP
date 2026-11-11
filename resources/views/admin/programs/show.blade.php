@extends('layouts.admin')

{{-- ============================================================
     Vendor CSS
============================================================ --}}
@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css">
@endsection

{{-- ============================================================
     Page content
============================================================ --}}
@section('content')
<div>

    {{-- Flash messages --}}
    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ __('admin.' . session('message')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('message2'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ __('admin.' . session('message2')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ── Program header card ────────────────────────────────── --}}
    <div class="card card-border-shadow-primary mb-4">
        <div class="card-body">

            {{-- Identity row --}}
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center
                        gap-3 pb-4 mb-4 border-bottom">
                <div class="avatar avatar-xl flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-secondary"
                          style="width:72px;height:72px;">
                        <img src="{{ $cycle->logo }}" alt="{{ $cycle->title }}"
                             class="rounded w-100 h-100 object-fit-contain">
                    </span>
                </div>

                <div class="flex-grow-1 min-width-0">
                    <h4 class="fw-bold mb-1 text-truncate">{{ $cycle->title }}</h4>
                    <span class="text-muted small">{{ $cycle->organization['name_' . $l] }}</span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @if ($cycle->cohort)
                        <span class="badge bg-label-primary fs-6">
                            {{ $l === 'en' ? 'Cohort' : 'الدفعة' }}: {{ $cycle->cohort }}
                        </span>
                    @endif

                    @if (!empty($cycle->private))
                        <button type="button"
                                class="badge bg-label-success border-0 fw-semibold"
                                onclick="copyToClipboard('{{ url('dashboard/cycles/view/' . $cycle->private) }}')">
                            <i class="ti ti-link me-1"></i>{{ __('cycles.Private Link') }}
                        </button>
                    @endif

                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary border-0 shadow-none p-1"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical ti-20px"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <button type="button" class="dropdown-item" id="exportBtn">
                                    <i class="ti ti-file-export me-2 text-warning"></i>
                                    {{ __('cycles.Export_Data') }}
                                </button>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                   href="/dashboard/programlist/edit/{{ $cycle->id }}">
                                    <i class="ti ti-edit me-2 text-info"></i>
                                    {{ __('cycles.Edit_Program') }}
                                </a>
                            </li>
                            @if ($_user->ableTo('delete_user', $permissions))
                                <li>
                                    <button type="button" class="dropdown-item text-danger"
                                            id="deleteProgramBtn">
                                        <i class="ti ti-trash me-2"></i>
                                        {{ __('cycles.Delete') }}
                                    </button>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Summary stat cards --}}
            <div class="row g-3 mb-4">
                @foreach ($summaryCards as [$label, $icon, $count, $color])
                    <div class="col-6 col-md-4 col-xl-3">
                        <div class="card card-border-shadow-{{ $color }} h-100 mb-0">
                            <div class="card-body d-flex align-items-center gap-3 py-3">
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-{{ $color }}">
                                        <i class="ti ti-{{ $icon }} ti-22px"></i>
                                    </span>
                                </div>
                                <div class="overflow-hidden">
                                    <p class="mb-0 text-muted small text-truncate">{{ $label }}</p>
                                    <h6 class="mb-0 fw-bold">{{ $count }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Mentors + Description + Duration --}}
            <div class="row g-4">
                <div class="col-md-9">
                    {{-- Mentors --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="ti ti-user-circle text-primary ti-20px"></i>
                            <h6 class="mb-0">{{ __('admin.Mentors') }}</h6>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse ($mentors as $mentor)
                                <a class="btn btn-sm btn-outline-primary rounded-pill"
                                   href="{{ route('mentors.profile_view', $mentor->id) }}"
                                   target="_blank">
                                    @if (!empty($mentor->image_url))
                                        <img src="{{ $mentor->image_url }}"
                                             onerror="this.src='/icons/account.svg'"
                                             width="20" height="20"
                                             class="rounded-circle me-1" alt="">
                                    @else
                                        <i class="ti ti-user me-1"></i>
                                    @endif
                                    {{ $mentor->name }}
                                </a>
                            @empty
                                <span class="text-muted small">{{ __('cycles.no_mentors_assigned') }}</span>
                            @endforelse
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="d-flex align-items-start gap-2">
                        <i class="ti ti-message text-primary ti-20px mt-1 flex-shrink-0"></i>
                        <p class="text-muted mb-0">{{ $cycle->description ?: __('cycles.NA') }}</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <h6 class="fw-semibold text-center mb-2">{{ __('admin.Duration') }}</h6>
                    <div class="d-flex flex-column gap-2 align-items-center mb-3">
                        <span class="badge bg-label-secondary d-flex align-items-center gap-1">
                            <i class="ti ti-calendar text-info ti-16px"></i>
                            <span class="fw-semibold">{{ FormatDate($cycle->start, 'Y-m-d') }}</span>
                        </span>
                        <span class="badge bg-label-secondary d-flex align-items-center gap-1">
                            <i class="ti ti-calendar text-danger ti-16px"></i>
                            <span class="fw-semibold">{{ FormatDate($cycle->end, 'Y-m-d') }}</span>
                        </span>
                    </div>

                    {{-- Steps progress --}}
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold">{{ $presSteps }}%</span>
                        <span class="text-muted">{{ $totalStepsFinish }}/{{ $totalSteps }}</span>
                    </div>
                    <div class="progress rounded-pill" style="height:8px;">
                        <div class="progress-bar bg-primary" role="progressbar"
                             style="width:{{ $presSteps }}%"
                             aria-valuenow="{{ $presSteps }}"
                             aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>{{-- /program header card --}}

    {{-- Cycle org ID for JS use --}}
    <input type="hidden" id="ccycleOrg" value="{{ $cycle->organization_id }}">

    {{-- ── Steps ───────────────────────────────────────────────── --}}
    @foreach ($steps as $step)

        {{-- ════════════════════════════════════════════════════════
             [stype 1] Custom Form step
        ════════════════════════════════════════════════════════ --}}
        @if ($step->stype == 1)
            @php $companiesInStep = $stepsExtra[$step->id]['companiesInStep']; @endphp

            <div class="card mb-4">
                <div class="card-body">

                    {{-- Header row --}}
                    <div class="d-flex flex-column flex-md-row align-items-md-center
                                justify-content-between gap-3 mb-4">
                        <span class="badge bg-label-primary fs-6">{{ __('cycles.Custom Form') }}</span>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted small">{{ __('cycles.steps') }} {{ $step->arr }}</span>
                            {{-- GV-10452 (ISF): the phase's submissions and their export, reachable by every role that may export them --}}
                            @if ($stepsExtra[$step->id]['hasSubmissions'] && $_user->ableTo('form_submission_details.index', $permissions))
                                <a href="{{ route('form_submission_details.index', ['id' => $step->id]) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="ti ti-send me-1"></i>{{ __('forms.submissions_tab') }}
                                </a>
                            @endif

                            {{-- Step kebab --}}
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary border-0 shadow-none p-1"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical ti-20px"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button type="button" class="dropdown-item"
                                                onclick="ModifyStep(1, {{ $step->id }})">
                                            <i class="ti ti-edit me-2 text-info"></i>{{ __('cycles.Edit') }}
                                        </button>
                                    </li>
                                    @if ($_user->ableTo('delete_user', $permissions))
                                        <li>
                                            <button type="button" class="dropdown-item text-danger"
                                                    onclick="deleteStep({{ $step->id }})">
                                                <i class="ti ti-trash me-2"></i>{{ __('cycles.Delete') }}
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-start">
                        <div class="col-md-9">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <h5 class="text-primary mb-0">{{ $step->title }}</h5>
                                <span class="badge bg-label-primary ms-1">
                                    <i class="ti ti-building-skyscraper me-1"></i>
                                    {{ $companiesInStep }} {{ __('admin.Startups') }}
                                </span>
                            </div>
                            @if ($step->description)
                                <p class="text-muted small mb-0">{{ $step->description }}</p>
                            @endif
                        </div>
                        <div class="col-md-3 mt-3 mt-md-0 d-flex justify-content-md-end">
                            {{-- Step dates --}}
                            <div class="d-flex flex-column gap-2">
                                <h6 class="fw-semibold mb-1 text-center">{{ __('admin.Duration') }}</h6>
                                <span class="badge bg-label-info d-flex align-items-center gap-1">
                                    <i class="ti ti-calendar-clock ti-16px text-info"></i>
                                    <span class="fw-semibold text-dark">{{ FormatDate($step->from, 'Y-m-d') }}</span>
                                </span>
                                <span class="badge bg-label-info d-flex align-items-center gap-1">
                                    <i class="ti ti-calendar-clock ti-16px text-info"></i>
                                    <span class="fw-semibold text-dark">{{ FormatDate($step->to, 'Y-m-d') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif

        {{-- ════════════════════════════════════════════════════════
             [stype 2] Screening step
        ════════════════════════════════════════════════════════ --}}
        @if ($step->stype == 2)
            @php
                $scopedJudges      = $stepsExtra[$step->id]['scopedJudges'];
                $screenCompanies   = $stepsExtra[$step->id]['screenCompanies'];
                $totalFiltration   = $stepsExtra[$step->id]['totalFiltration'];
                $filtrationPending = $stepsExtra[$step->id]['filtrationPending'];
                $filtrationDone    = $stepsExtra[$step->id]['filtrationDone'];
            @endphp

            <div class="card mb-4">
                <div class="card-body">

                    {{-- Header row --}}
                    <div class="d-flex flex-column flex-md-row align-items-md-center
                                justify-content-between gap-3 mb-4">
                        <span class="badge bg-label-primary fs-6">{{ __('cycles.Screening') }}</span>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted small">{{ __('cycles.steps') }} {{ $step->arr }}</span>
                            <a href="/dashboard/screening/results/{{ $step->id }}"
                               class="btn btn-sm btn-primary">
                                <i class="ti ti-file-analytics me-1"></i>{{ __('admin.Results') }}
                            </a>
                            <a href="/dashboard/{{ $step->id }}/screening"
                               class="btn btn-sm btn-primary">
                                <i class="ti ti-settings me-1"></i>{{ __('admin.Manage') }}
                            </a>

                            {{-- Step kebab --}}
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary border-0 shadow-none p-1"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical ti-20px"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button type="button" class="dropdown-item"
                                                onclick="ModifyStep(1, {{ $step->id }})">
                                            <i class="ti ti-edit me-2 text-info"></i>{{ __('cycles.Edit') }}
                                        </button>
                                    </li>
                                    @if ($_user->ableTo('delete_user', $permissions))
                                        <li>
                                            <button type="button" class="dropdown-item text-danger"
                                                    onclick="deleteStep({{ $step->id }})">
                                                <i class="ti ti-trash me-2"></i>{{ __('cycles.Delete') }}
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <h5 class="text-primary mb-4">{{ $step->title }}</h5>

                    {{-- Stats strip --}}
                    <div class="row g-3 border rounded-3 p-3 mb-4">
                        @foreach ([
                            ['warning', 'gavel',               __('admin.Judgers'),           $scopedJudges->count()],
                            ['primary', 'building-skyscraper', __('admin.Startups'),           $screenCompanies->count()],
                            ['warning', 'transform',           __('admin.Filtration'),         $totalFiltration],
                            ['info',    'rotate-clockwise-2',  __('admin.Filtration_pending'), $filtrationPending],
                            ['success', 'checkbox',            __('admin.Filtration_done'),    $filtrationDone],
                        ] as [$sc, $si, $sl, $sv])
                            <div class="col-sm-6 col-lg-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-{{ $sc }}">
                                            <i class="ti ti-{{ $si }} ti-18px"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mb-0 text-muted small text-nowrap">{{ $sl }}</p>
                                        <span class="fw-semibold">{{ $sv }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Judges list --}}
                    @if ($scopedJudges->count())
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="ti ti-gavel text-primary ti-18px"></i>
                            <h6 class="mb-0">{{ __('admin.Judgers') }}</h6>
                            <form action="/dashboard/programlist/Email" method="POST"
                                  class="ms-auto">
                                @csrf
                                <input type="hidden" name="step" value="{{ $step->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    <i class="ti ti-circle-check me-1"></i>{{ __('cycles.Notify them') }}
                                </button>
                            </form>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            @foreach ($scopedJudges as $judge)
                                <a class="btn btn-sm btn-outline-warning rounded-pill"
                                   href="{{ route('mentors.profile_view', $judge->id) }}"
                                   target="_blank">
                                    @if (!empty($judge->image_url))
                                        <img src="{{ $judge->image_url }}"
                                             onerror="this.src='/icons/account.svg'"
                                             width="20" height="20"
                                             class="rounded-circle me-1" alt="">
                                    @else
                                        <i class="ti ti-user me-1"></i>
                                    @endif
                                    <span class="fw-semibold">{{ $judge->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-9">
                            @if ($step->description)
                                <p class="text-muted small mb-0">{{ $step->description }}</p>
                            @endif
                        </div>
                        <div class="col-md-3 mt-3 mt-md-0 d-flex justify-content-md-end">
                            <div class="d-flex flex-column gap-2">
                                <h6 class="fw-semibold mb-1 text-center">{{ __('admin.Duration') }}</h6>
                                <span class="badge bg-label-info d-flex align-items-center gap-1">
                                    <i class="ti ti-calendar-clock ti-16px text-info"></i>
                                    <span class="fw-semibold text-dark">{{ FormatDate($step->from, 'Y-m-d') }}</span>
                                </span>
                                <span class="badge bg-label-info d-flex align-items-center gap-1">
                                    <i class="ti ti-calendar-clock ti-16px text-info"></i>
                                    <span class="fw-semibold text-dark">{{ FormatDate($step->to, 'Y-m-d') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif

        {{-- ════════════════════════════════════════════════════════
             [stype 3] Mentorship step
        ════════════════════════════════════════════════════════ --}}
        @if ($step->stype == 3)
            @php
                $mentorCount      = $stepsExtra[$step->id]['mentorCount'];
                $sessionMinutes   = $stepsExtra[$step->id]['sessionMinutes'];
                $doneSessions     = $stepsExtra[$step->id]['doneSessions'];
                $allRequests      = $stepsExtra[$step->id]['allRequests'];
                $newRequests      = $stepsExtra[$step->id]['newRequests'];
                $approvedRequests = $stepsExtra[$step->id]['approvedRequests'];
                $rejectedRequests = $stepsExtra[$step->id]['rejectedRequests'];
            @endphp

            <div class="card mb-4">
                <div class="card-body">

                    {{-- Header row --}}
                    <div class="d-flex flex-column flex-md-row align-items-md-center
                                justify-content-between gap-3 mb-4">
                        <span class="badge bg-label-primary fs-6">{{ __('cycles.Mentorship') }}</span>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted small">{{ __('cycles.steps') }} {{ $step->arr }}</span>
                            <a href="/dashboard/mentorship/assign/{{ $step->cycle_id }}"
                               class="btn btn-sm btn-primary">
                                <i class="ti ti-user me-1"></i>{{ __('admin.Mentors') }}
                            </a>
                            <a href="/dashboard/mentorship/view/{{ $step->cycle_id }}"
                               class="btn btn-sm btn-primary">
                                <i class="ti ti-settings me-1"></i>{{ __('admin.Manage') }}
                            </a>
                            <a href="/dashboard/mentorship/sessions?show=all&cycle_id={{ $step->cycle_id }}"
                               class="btn btn-sm btn-primary">
                                <i class="ti ti-cast me-1"></i>{{ __('admin.Sessions') }}
                            </a>

                            {{-- Step kebab --}}
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary border-0 shadow-none p-1"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical ti-20px"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button type="button" class="dropdown-item"
                                                onclick="ModifyStep(1, {{ $step->id }})">
                                            <i class="ti ti-edit me-2 text-info"></i>{{ __('cycles.Edit') }}
                                        </button>
                                    </li>
                                    @if ($_user->ableTo('delete_user', $permissions))
                                        <li>
                                            <button type="button" class="dropdown-item text-danger"
                                                    onclick="deleteStep({{ $step->id }})">
                                                <i class="ti ti-trash me-2"></i>{{ __('cycles.Delete') }}
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <h5 class="text-primary mb-4">{{ $step->title }}</h5>

                    {{-- Stats strip --}}
                    <div class="row g-3 border rounded-3 p-3 mb-4">
                        @foreach ([
                            ['secondary', 'user',          __('admin.Mentors'),                                 $mentorCount],
                            ['success',   'clock',         __('admin.Mentorship_sessions_duration_(hours)'),    $sessionMinutes],
                            ['info',      'database',      __('admin.Mentorship_done_requests_count'),          $doneSessions],
                            ['info',      'message',       __('cycles.All requests'),                           $allRequests],
                            ['warning',   'new-section',   __('cycles.New requests only'),                      $newRequests],
                            ['success',   'checkbox',      __('cycles.approved requests'),                      $approvedRequests],
                            ['danger',    'trash-x',       __('cycles.disapproved requests'),                   $rejectedRequests],
                        ] as [$sc, $si, $sl, $sv])
                            <div class="col-sm-6 col-lg-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-{{ $sc }}">
                                            <i class="ti ti-{{ $si }} ti-18px"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mb-0 text-muted small text-nowrap">{{ $sl }}</p>
                                        <span class="fw-semibold">{{ $sv }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-md-9">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="ti ti-bulb text-primary ti-18px"></i>
                                <h6 class="mb-0">{{ __('admin.Mentors') }}</h6>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach ($mentors as $mentor)
                                    <a class="btn btn-sm btn-outline-info rounded-pill"
                                       href="{{ route('mentors.profile_view', $mentor->id) }}"
                                       target="_blank">
                                        @if (!empty($mentor->image_url))
                                            <img src="{{ $mentor->image_url }}"
                                                 onerror="this.src='/icons/account.svg'"
                                                 width="24" height="24"
                                                 class="rounded-circle me-1" alt="">
                                        @else
                                            <i class="ti ti-user me-1"></i>
                                        @endif
                                        <span class="fw-semibold">{{ $mentor->name }}</span>
                                        @if ($mentor->title)
                                            <small class="text-muted ms-1">{{ $mentor->title }}</small>
                                        @endif
                                    </a>
                                @endforeach
                            </div>

                            @if ($step->description)
                                <p class="text-muted small mb-0">{{ $step->description }}</p>
                            @endif
                        </div>
                        <div class="col-md-3 mt-3 mt-md-0 d-flex justify-content-md-end">
                            <div class="d-flex flex-column gap-2">
                                <h6 class="fw-semibold mb-1 text-center">{{ __('admin.Duration') }}</h6>
                                <span class="badge bg-label-info d-flex align-items-center gap-1">
                                    <i class="ti ti-calendar-clock ti-16px text-info"></i>
                                    <span class="fw-semibold text-dark">{{ FormatDate($step->from, 'Y-m-d') }}</span>
                                </span>
                                <span class="badge bg-label-info d-flex align-items-center gap-1">
                                    <i class="ti ti-calendar-clock ti-16px text-info"></i>
                                    <span class="fw-semibold text-dark">{{ FormatDate($step->to, 'Y-m-d') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif

        {{-- ════════════════════════════════════════════════════════
             [stype 4] Training step
        ════════════════════════════════════════════════════════ --}}
        @if ($step->stype == 4)
            @php
                $trainingCount    = $stepsExtra[$step->id]['trainingCount'];
                $trainingMinutes  = $stepsExtra[$step->id]['trainingMinutes'];
                $sessionTypeCount = $stepsExtra[$step->id]['sessionTypeCount'];
                $onlineCount      = $stepsExtra[$step->id]['onlineCount'];
                $offlineCount     = $stepsExtra[$step->id]['offlineCount'];
            @endphp

            <div class="card mb-4">
                <div class="card-body">

                    {{-- Header row --}}
                    <div class="d-flex flex-column flex-md-row align-items-md-center
                                justify-content-between gap-3 mb-4">
                        <span class="badge bg-label-primary fs-6">{{ __('cycles.Training') }}</span>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted small">{{ __('cycles.steps') }} {{ $step->arr }}</span>
                            <a href="/dashboard/training/sessions/{{ $step->id }}"
                               class="btn btn-sm btn-primary">
                                <i class="ti ti-cast me-1"></i>{{ __('admin.Sessions') }}
                            </a>
                            <a href="/dashboard/training/results"
                               class="btn btn-sm btn-primary">
                                <i class="ti ti-file-analytics me-1"></i>{{ __('admin.Results') }}
                            </a>

                            {{-- Step kebab --}}
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary border-0 shadow-none p-1"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical ti-20px"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button type="button" class="dropdown-item"
                                                onclick="ModifyStep(1, {{ $step->id }})">
                                            <i class="ti ti-edit me-2 text-info"></i>{{ __('cycles.Edit') }}
                                        </button>
                                    </li>
                                    @if ($_user->ableTo('delete_user', $permissions))
                                        <li>
                                            <button type="button" class="dropdown-item text-danger"
                                                    onclick="deleteStep({{ $step->id }})">
                                                <i class="ti ti-trash me-2"></i>{{ __('cycles.Delete') }}
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <h5 class="text-primary mb-4">{{ $step->title }}</h5>

                    {{-- Stats strip --}}
                    <div class="row g-3 border rounded-3 p-3 mb-4">
                        @foreach ([
                            ['secondary', 'user-screen',    __('admin.Total_sessions'),                      $trainingCount],
                            ['success',   'clock',          __('admin.Training sessions duration (hours)'),  $trainingMinutes],
                            ['primary',   'device-desktop', __('cycles.sessions'),                           $sessionTypeCount],
                            ['warning',   'world',          __('cycles.online'),                             $onlineCount],
                            ['info',      'home-2',         __('cycles.offline'),                            $offlineCount],
                        ] as [$sc, $si, $sl, $sv])
                            <div class="col-sm-6 col-lg-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-{{ $sc }}">
                                            <i class="ti ti-{{ $si }} ti-18px"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mb-0 text-muted small text-nowrap">{{ $sl }}</p>
                                        <span class="fw-semibold">{{ $sv }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-md-9">
                            @if ($step->description)
                                <p class="text-muted small mb-0">{{ $step->description }}</p>
                            @endif
                        </div>
                        <div class="col-md-3 mt-3 mt-md-0 d-flex justify-content-md-end">
                            <div class="d-flex flex-column gap-2">
                                <h6 class="fw-semibold mb-1 text-center">{{ __('admin.Duration') }}</h6>
                                <span class="badge bg-label-info d-flex align-items-center gap-1">
                                    <i class="ti ti-calendar-clock ti-16px text-info"></i>
                                    <span class="fw-semibold text-dark">{{ FormatDate($step->from, 'Y-m-d') }}</span>
                                </span>
                                <span class="badge bg-label-info d-flex align-items-center gap-1">
                                    <i class="ti ti-calendar-clock ti-16px text-info"></i>
                                    <span class="fw-semibold text-dark">{{ FormatDate($step->to, 'Y-m-d') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif

        {{-- ════════════════════════════════════════════════════════
             [stype 5] Final evaluation step
        ════════════════════════════════════════════════════════ --}}
        @if ($step->stype == 5)
            @php
                $finalCompanyCount = $stepsExtra[$step->id]['finalCompanyCount'];
                $finalAnswers      = $stepsExtra[$step->id]['finalAnswers'];
                $finalPending      = $stepsExtra[$step->id]['finalPending'];
                $judgesDetails     = $stepsExtra[$step->id]['judgesDetails'];
            @endphp

            <div class="card mb-4">
                <div class="card-body">

                    {{-- Header row --}}
                    <div class="d-flex flex-column flex-md-row align-items-md-center
                                justify-content-between gap-3 mb-4">
                        <span class="badge bg-label-primary fs-6">{{ __('cycles.Final evaluation') }}</span>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted small">{{ __('cycles.steps') }} {{ $step->arr }}</span>
                            <a href="/dashboard/{{ $step->id }}/final/"
                               class="btn btn-sm btn-primary">
                                <i class="ti ti-settings me-1"></i>{{ __('admin.Manage') }}
                            </a>

                            {{-- Step kebab --}}
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary border-0 shadow-none p-1"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical ti-20px"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button type="button" class="dropdown-item"
                                                onclick="ModifyStep(1, {{ $step->id }})">
                                            <i class="ti ti-edit me-2 text-info"></i>{{ __('cycles.Edit') }}
                                        </button>
                                    </li>
                                    @if ($_user->ableTo('delete_user', $permissions))
                                        <li>
                                            <button type="button" class="dropdown-item text-danger"
                                                    onclick="deleteStep({{ $step->id }})">
                                                <i class="ti ti-trash me-2"></i>{{ __('cycles.Delete') }}
                                            </button>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <h5 class="text-primary mb-4">{{ $step->title }}</h5>

                    {{-- Stats strip --}}
                    <div class="row g-3 border rounded-3 p-3 mb-4">
                        @foreach ([
                            ['warning', 'gavel',               __('admin.Judgers'),                       $judgesDetails->count()],
                            ['primary', 'building-skyscraper', __('admin.Startups'),                      $finalCompanyCount],
                            ['danger',  'presentation',        __('admin.Evaluation'),                    $finalAnswers],
                            ['info',    'rotate-clockwise-2',  __('admin.Traditional_Evaluation_pending'), $finalPending],
                            ['success', 'checkbox',            __('admin.Evaluation_done'),               $finalAnswers],
                        ] as [$sc, $si, $sl, $sv])
                            <div class="col-sm-6 col-lg-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm flex-shrink-0">
                                        <span class="avatar-initial rounded bg-label-{{ $sc }}">
                                            <i class="ti ti-{{ $si }} ti-18px"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="mb-0 text-muted small text-nowrap">{{ $sl }}</p>
                                        <span class="fw-semibold">{{ $sv }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Judges list --}}
                    @if ($judgesDetails->count())
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="ti ti-gavel text-primary ti-18px"></i>
                            <h6 class="mb-0">{{ __('admin.Judgers') }}</h6>
                            <form action="/dashboard/programlist/Email" method="POST"
                                  class="ms-auto">
                                @csrf
                                <input type="hidden" name="step" value="{{ $step->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    <i class="ti ti-circle-check me-1"></i>{{ __('cycles.Notify them') }}
                                </button>
                            </form>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            @foreach ($judgesDetails as $judge)
                                <a class="btn btn-sm btn-outline-warning rounded-pill"
                                   href="{{ route('mentors.profile_view', $judge->id) }}"
                                   target="_blank">
                                    @if (!empty($judge->image_url))
                                        <img src="{{ $judge->image_url }}"
                                             onerror="this.src='/icons/account.svg'"
                                             width="20" height="20"
                                             class="rounded-circle me-1" alt="">
                                    @else
                                        <i class="ti ti-user me-1"></i>
                                    @endif
                                    <span class="fw-semibold">{{ $judge->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-9">
                            @if ($step->description)
                                <p class="text-muted small mb-0">{{ $step->description }}</p>
                            @endif
                        </div>
                        <div class="col-md-3 mt-3 mt-md-0 d-flex justify-content-md-end">
                            <div class="d-flex flex-column gap-2">
                                <h6 class="fw-semibold mb-1 text-center">{{ __('admin.Duration') }}</h6>
                                <span class="badge bg-label-info d-flex align-items-center gap-1">
                                    <i class="ti ti-calendar-clock ti-16px text-info"></i>
                                    <span class="fw-semibold text-dark">{{ FormatDate($step->from, 'Y-m-d') }}</span>
                                </span>
                                <span class="badge bg-label-info d-flex align-items-center gap-1">
                                    <i class="ti ti-calendar-clock ti-16px text-info"></i>
                                    <span class="fw-semibold text-dark">{{ FormatDate($step->to, 'Y-m-d') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif

    @endforeach {{-- /steps --}}

</div>{{-- /container-fluid --}}

{{-- ── Edit-step modal ────────────────────────────────────────── --}}
<div class="modal fade" id="customStepModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="edit-step-form"
                  action="{{ url('dashboard/cycles/modify') }}"
                  method="POST" autocomplete="off">
                @csrf
                <input type="hidden" name="sid"  id="sid">
                <input type="hidden" name="type" value="{{ $updateStepAction }}">

                <div class="modal-header">
                    <h5 class="modal-title">{{ __('cycles.Modify current step') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    {{-- Form selector (Custom + Final eval) --}}
                    <div id="mmenu1" class="mb-3" style="display:none;">
                        <label class="form-label" for="mFormSelect">
                            {{ __('cycles.Choose form') }}
                        </label>
                        <select class="form-select" id="mFormSelect" name="mFormSelect"></select>
                    </div>

                    {{-- Visibility (Custom form only) --}}
                    <div class="public-data-form-fields mb-3" style="display:none;">
                        <label class="form-label" for="is_public">
                            {{ __('cycles.Step Visibility') }}
                        </label>
                        <select class="form-select" id="is_public" name="is_public">
                            <option value="0">{{ __('cycles.Step Visible To Private') }}</option>
                            <option value="1">{{ __('cycles.Step Visible To Public') }}</option>
                        </select>
                    </div>
                    <div class="public-data-form-fields mb-3" id="public_slug_div" style="display:none;">
                        <label class="form-label" for="public_slug">
                            {{ __('cycles.Public URL') }}
                        </label>
                        <input type="text" id="public_slug" name="public_slug"
                               class="form-control"
                               placeholder="{{ __('cycles.Public URL placeholder') }}">
                    </div>

                    {{-- Screening type selector --}}
                    {{--  <div id="mmenu2" class="mb-3" style="display:none;">
                        <label class="form-label" for="mScreeningSelect">
                            {{ __('cycles.Choose screening type') }}
                        </label>
                        <select class="form-select" id="mScreeningSelect" name="mScreeningSelect">
                            <option disabled selected>{{ __('cycles.Select type') }}</option>
                            <option value="onetoall">{{ __('cycles.Assign One to all users') }}</option>
                            <option value="grouptoall">{{ __('cycles.Assign Group to all users') }}</option>
                            <option value="grouptogroup">{{ __('cycles.Assign Group to group of users') }}</option>
                        </select>
                    </div>  --}}

                    {{-- Title --}}
                    <div class="mb-3">
                        <label class="form-label" for="mtitle">{{ __('cycles.Form title') }}</label>
                        <input id="mtitle" name="title" type="text" class="form-control"
                               placeholder="{{ __('cycles.Title') }}" required>
                    </div>

                    {{-- Date range --}}
                    <div class="mb-3">
                        <label class="form-label">
                            {{ __('admin.Start_Date') }} – {{ __('admin.End_Date') }}
                        </label>
                        <input type="hidden" name="fromDate" id="mfromDate">
                        <input type="hidden" name="toDate"   id="mtoDate">
                        <input type="text"   id="step-range-picker" class="form-control">
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label" for="mdescription">
                            {{ __('cycles.Description') }}
                        </label>
                        <textarea id="mdescription" name="description"
                                  class="form-control" rows="3"
                                  placeholder="{{ __('cycles.Description') }}"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">
                        {{ __('cycles.Close') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-circle-check me-1"></i>{{ __('cycles.Submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- ============================================================
     Scripts
============================================================ --}}
@section('vendorscripts')
<script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
@endsection

@section('pagescripts')
<script>
(() => {
    'use strict';

    // ── CSRF header for all $.ajax calls ──────────────────────
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ── Copy to clipboard ─────────────────────────────────────
    window.copyToClipboard = function (text) {
        (navigator.clipboard?.writeText(text) ?? Promise.reject())
            .catch(() => {
                const el = Object.assign(document.createElement('input'), { value: text });
                document.body.appendChild(el);
                el.select();
                document.execCommand('copy');
                document.body.removeChild(el);
            })
            .then(() => alert("{{ __('admin.Copied') }}"));
    };

    // ── Delete step ───────────────────────────────────────────
    window.deleteStep = function (id) {
        if (!confirm('{{ __('cycles.sure delete step') }}?')) return;

        $.post('/dashboard/cycles/modify', { stepid: id, type: '{{ $deleteStepAction }}' })
            .done(() => location.reload())
            .fail(() => alert('Error occurred. Please try again.'));
    };

    // ── Edit step modal ───────────────────────────────────────
    window.ModifyStep = function (set, id) {
        if (set !== 1) return;

        $.post('/dashboard/cycles/modify', { type: '{{ $fetchStepAction }}', sid: id, cycleid: 0 })
            .done(function (response) {
                const step  = response.stype;
                const sData = response.data;

                // Reset conditional panels
                $('#mmenu1, #mmenu2, .public-data-form-fields, #public_slug_div').hide();

                if (step === 1) {
                    $('.public-data-form-fields').show();
                    $('#is_public').val(response.is_public);
                    $('#public_slug').val(response.public_slug);
                    if (response.is_public == 1) $('#public_slug_div').show();
                }

                const orgId           = $('#ccycleOrg').val();
                const needsFormSelect = step === 1 || step === 5;
                const needsScreening  = step === 2;

                if (needsFormSelect) {
                    $.get(`{{ url('dashboard/builder/organization') }}/${orgId}`)
                        .done(function (res) {
                            const opts = res.data.map(f =>
                                `<option value="${f.id}" ${f.id == sData ? 'selected' : ''}>${f.form_name}</option>`
                            ).join('');
                            $('#mFormSelect').html(opts);
                            $('#mmenu1').show();
                        });
                }

                if (needsScreening) {
                    $('#mScreeningSelect').val(sData);
                    $('#mmenu2').show();
                }

                // Populate fields
                $('#sid').val(response.id);
                $('#mtitle').val(response.title);
                $('#mdescription').val(response.description);

                $('#step-range-picker').daterangepicker({
                    opens    : isRtl ? 'left' : 'right',
                    drops    : 'up',
                    startDate: response.from ? moment(response.from) : moment(),
                    endDate  : response.to   ? moment(response.to)   : moment(),
                }).on('apply.daterangepicker', function (ev, picker) {
                    $('#mfromDate').val(picker.startDate.format('YYYY-MM-DD'));
                    $('#mtoDate').val(picker.endDate.format('YYYY-MM-DD'));
                });

                $('#customStepModal').modal('show');
            })
            .fail(() => alert('Error occurred. Please try again.'));
    };

    // ── Public-slug toggle ────────────────────────────────────
    $(document).on('change', '#is_public', function () {
        $('#public_slug_div').toggle($(this).val() === '1');
    });

})();
</script>
@endsection
