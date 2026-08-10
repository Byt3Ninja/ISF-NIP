@extends('layouts.admin')

@section('vendorstyle')
<!-- Vendors CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/node-waves/node-waves.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/typeahead-js/typeahead.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/pickr/pickr-themes.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/animate-css/animate.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
@stop

@section('style')
<style>
   input[type="file"]::file-selector-button {
    background-color: #FFFF;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    border: 1px solid lightgray;
  }
  input[type="file"]::file-selector-button:hover {
    background-color: lightgray;
  }
  .radius-15 {
    border-radius: 15px;
  }

  .light-shadow {
    box-shadow: 0px 2px 0px 0px #F2F1F3;
  }

  .btn {
    border-radius: 15px !important;
    box-shadow: 0px 2px 0px 0px #F2F1F3;
  }

  .badge {
    border-radius: 10px !important;
  }
  .text-xs {
    font-size: 12px;
  }
  .custom-pill {
    width: fit-content;
    padding: 0.25rem 0.5rem;
    margin-right: 0.5rem;
  }
</style>
@stop

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/flatpickr/flatpickr.css">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css">
@endsection

@section('content')
<div class="">
  <div class="">
    <!-- Alert -->
    @if (session()->has('message'))
      <div class="alert alert-success alert-dismissible" role="alert">
        {{ __('admin.' . session()->get('message')) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    @if (session()->has('message2'))
      <div class="alert alert-danger alert-dismissible" role="alert">
        {{ __('admin.' . session()->get('message2')) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    <!-- / Alert -->
    <div class="row">
      <input type="hidden" id="ccycleOrg" value="{{ $cycle->organization_id }}" >
      <div class="col-12 mb-4">
        <div class="card">
          <div class="card-body">
            <div class="d-flex gap-6">
              <div class="d--flex flex-shrink-0 w-px-100">
                <div class="w-px-70 h-px-100">
                  <img src="{{ $cycle->logo }}" class="w-100 h-100 border radius-15" style="object-fit: contain;" alt="Program Image" />
                </div>
              </div>
              <div class="flex-grow-1">
                <div class="d-flex flex-column flex-md-row justify-content-between mb-3 mb-md-0">
                  <h4 class="text-primary mb-3 mb-md-0">{{ $cycle->title }}</h4>                 
                  <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                    @if ($cycle->cohort)
                      <div class="d-flex align-items-center">
                        <h6 class="mb-0">{{ $l == 'en' ? 'Cohort' : 'الدفعة' }}:</h6>
                        <span class="badge fs-6 bg-label-primary ms-2">{{ $cycle->cohort }}:</span>
                      </div>
                    @endif
                    @if(!empty($cycle->private))
                      <div class="btn btn-transparent" onclick="copyToClipboard('{{ url('dashboard/cycles/view/' . $cycle->private) }}')" >
                      {{__('cycles.Private Link')}}
                        <i class="ti ti-circle-check ms-2 text-success ti-24px"></i>
                      </div>
                    @endif
                  </div>
                </div>

                <h5 class="mb-3">{{ $cycle->organization['name_' . $l] }}</h5>

                <div class="row border radius-15 py-3 mb-3">
                  @foreach ($orgCountList as $item)
                    <div class="col-md-4 mb-2">
                      <div class="d-flex flex-nowrap shrink align-items-center">
                        <div class="badge bg-label-{{ $item['color'] }} me-2 p-2">
                          <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }} ti-24px"></i>
                        </div>
                        <div class="d-flex flex-nowrap align-items-center">
                          <span class="me-2 fw-semibold text-capitalize ">{{ $item['label'] }}</span>
                          <small class="text-nowrap bg-light text-secondary py-1 px-2 rounded-pill commanums">
                            {{ $item['count'] }}
                          </small>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
                <div class="row">
                  <div class="col-md-9">
                    <div class="d-flex flex-column">
                      <div class="d-flex align-items-center mb-2">
                        <i class="ti ti-user-circle text-primary ti-24px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Mentors')}}</span>
                      </div>
                      <div class="d-flex flex-wrap gap-1">
                        @foreach($mentors as $mentor)
                        <a class="btn btn-sm btn-outline-primary rounded-pill" style="height: 30px;" target="_blank"
                          href="{{route('mentors.profile_view', $mentor->id)}}">
                          @if(!empty($mentor->image_url))
                          <img src="{{$mentor->image_url ?? '/icons/account.svg'}}"
                            onerror="if(this.src!='/icons/account.svg'){this.onerror=null;this.src='/icons/account.svg';}" width="20" height="20" class="rounded-circle" />
                          @else
                          <i class="ti ti-user"></i>
                          @endif
                          <span class="mx-1 fw-semibold">{{$mentor->name}}</span>
                        </a>
                        @endforeach
                       
                      </div>
                    </div>
                    <div class="d-flex flex-column my-3">
                      <div class="d-flex align-items-center my-2">
                        <i class="ti ti-message text-primary ti-20px me-2"></i>
                        <h6 class="mb-0">{{__('admin.Description')}}</span>
                      </div>
                      <div class="d-flex flex-wrap align-items-center ps-7">
                        <p class="text-muted">
                          @if(!empty($cycle->description))
                              {{ $cycle->description }}
                              @else
                              {{__('cycles.NA')}}
                              @endif
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="d-flex align-items-end justify-content-end w-100">
                      <div class="d-flex flex-md-column gap-1" style="width: fit-content;">
                        <h6 class="mb-1 me-2 text-center fw-semibold">{{__('admin.Duration')}}</h6>
                        <div class="badge bg-label-secondary">
                          <i class="ti ti-calendar ti-20px text-info"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($cycle->start, 'Y-m-d') }}</span>
                        </div>
                        <div class="badge bg-label-secondary">
                          <i class="ti ti-calendar ti-20px text-danger"></i>
                          <span class="fw-bold text-dark mx-2">{{ FormatDate($cycle->end, 'Y-m-d') }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @php
                    // تحقق من أن $totalSteps ليست صفرًا قبل عملية القسمة
                    $presSteps = $totalSteps > 0 ? ($totalStepsFinish / $totalSteps) * 100 : 0;
                @endphp
                <div class="row mt-4 mt-md-0">
                  <div class="col-12">
                    <div class="d-flex align-items-center">
                      <span class="fw-bold mx-1">{{ number_format($presSteps, 2) }}%</span>
                      <div style="width: 100%; height: 7px;" class="progress rounded-pill">
                        <div role="progressbar" class="progress-bar" style="background: linear-gradient(to {{$l == 'en' ? 'right' : 'left'}},
                          rgb(164, 157, 233), #857ce6, #7367f0); width: {{ number_format($presSteps, 2) }}%;" aria-valuenow="{{ number_format($presSteps, 2) }}" aria-valuemin="0"
                          aria-valuemax="100">
                        </div>
                      </div>
                      <span class="fw-bold mx-1">{{$totalSteps}}/{{$totalStepsFinish}}</span>
                      <div class="mx-2 position-relative">
                        <div class="dropdown"></div>
                        <button class="bg-transparent border-none" type="button" id="dropdownMenuButton"
                          data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="ti ti-dots-vertical ti-24px"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                          <li>
                            <button id="editButton" class="dropdown-item text-dark">
                              <i class="ti ti-file-export me-2 text-warning"></i>
                              {{ __('cycles.Export_Data') }}
                            </button>
                          </li>
                          <!-- <li>
                            <button id="editButton" class="dropdown-item text-dark">
                              <i class="ti ti-stack-2 me-2 text-danger"></i>
                              {{ __('cycles.Edit_Step') }}
                            </button>
                          </li> -->
                          <li>
                            <a 
                              id="editButton" 
                              class="dropdown-item ccycle text-dark" 
                              {{-- onclick="onEditProgramClick({{ $cycle->id }})" --}}
                              href="/dashboard/programlist/edit/{{ $cycle->id }}"
                            >
                              <i class="ti ti-edit me-2 text-info"></i>
                              {{ __('cycles.Edit_Program') }}
                            </a>
                          </li>
                          <li>
                            @if( $_user->ableTo('delete_user', $permissions) )
                            <button class="dropdown-item text-dark" id="deleteButton">
                              <i class="ti ti-trash me-2 text-danger"></i>
                              {{ __('cycles.Delete') }}
                            </button>
                            @endif
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
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
                                <span class="text-muted small">{{ __('cycles.NA') }}</span>
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
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- / Final screening step -->
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
          <input type="hidden" name="sid" id="sid">
          <input type="hidden" name="type" value="{{ \App\Enums\CycleModifyAction::UPDATE_STEP }}">

          <fieldset class="row">
            <div class="col-12 form-group mb-3">
              <label class="form-label" for="mFormSelect">{{ __('cycles.Choose form') }}: </label>
              <select class="select form-control" id="mFormSelect" name="mFormSelect" required>
                {{-- @foreach (\App\BuilderForm::all() as $form) --}}
                {{-- <option value="{{$form->id}}" >{{$form->form_name}}</option> --}}
                {{-- @endforeach --}}
              </select>
            </div>

            <div class="col-12 form-group mb-3 public-data-form-fields">
              <label class="form-label" for="is_public">{{ __('cycles.Step Visibility') }}: </label>
              <select class="select form-control" id="is_public" name="is_public">
                <option value="0">{{ __('cycles.Step Visible To Private') }}</option>
                <option value="1">{{ __('cycles.Step Visible To Public') }}</option>
              </select>
            </div>
            <div class="col-12 form-group mb-3 public-data-form-fields" id="public_slug_div">
              <label class="form-label" for="public_slug">{{ __('cycles.Public URL') }}: </label>
              <input type="text" id="public_slug" name="public_slug" class="form-control" value="" placeholder="{{ __('cycles.Public URL placeholder') }}" >
            </div>

            {{--  <div class="mb-2" id="mmenu2">
              <label class="form-label">{{ __('cycles.Choose screening type') }}: </label>
              <div>
                <select class="select2 form-control" id="mScreeningSelect" name="mScreeningSelect" required>
                  <option selected disabled> {{ __('cycles.Select type') }}</option>
                  <option value="onetoall">{{ __('cycles.Assign One to all users') }}</option>
                  <option value="grouptoall">{{ __('cycles.Assign Group to all users') }}</option>
                  <option value="grouptogroup">{{ __('cycles.Assign Group to group of users') }}</option>
                </select>
              </div>
            </div>  --}}


            <div class="col-12 form-group mb-3">
              <label class="form-label" for="step-name">{{ __('cycles.Form title') }}:</label>
              <input id='mtitle' name='title' type="text" placeholder="{{ __('cycles.Title') }}"
              class="form-control" required>
            </div>
            <div class="col-12 mb-4">
              <label for="step-range-picker" class="form-label">{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:</label>
              <input type="hidden" name="fromDate" id="mfromDate">
              <input type="hidden" name="toDate" id="mtoDate">
              <input type="text" id="step-range-picker" class="form-control" name="date_range" />
            </div>
            {{-- <div class="col-md-6 form-group mb-3">
              <label class="form-label" for="flatpickr-date">{{ __('cycles.Valid from') }}: </label>
              <div class="position-relative">
                <input id='mfromDate' name='fromDate' type="text" class="form-control flatpickr-date" required>
                <div class="position-absolute top-0 end-0 mt-2 me-3">
                  <i class="ti ti-calendar ti-24px text-primary"></i>
                </div>
              </div>
            </div>
            <div class="col-md-6 form-group mb-3">
              <label class="form-label" for="flatpickr-date">{{ __('cycles.Valid to') }}:</label>
              <div class="position-relative">
                <input id='mtoDate' name='toDate' type="text" class="form-control flatpickr-date" required>
                <div class="position-absolute top-0 end-0 mt-2 me-3">
                  <i class="ti ti-calendar ti-24px text-primary"></i>
                </div>
              </div>
            </div> --}}
            <div class="col-12 form-group mb-5">
              <label class="form-label" form="step-desc">{{ __('cycles.Description') }}: </label>
              <textarea id='mdescription' name='description' placeholder="{{ __('cycles.Description') }}" class="form-control"></textarea>
          
            </div>
          </fieldset>
        </div>
        <div class="modal-footer">
          <button type="submit" value="submit" class="btn btn-primary">
            <i class="ti ti-circle-check ti-24px me-2"></i>
            {{ __('cycles.Submit') }}
          </button>
          <button 
            type="button" 
            data-bs-toggle="modal" 
            data-bs-target="#customStepModal" 
            class="btn btn-outline-primary ml-1">
            <i class="ti ti-circle-x ti-24px me-2"></i>
            {{ __('cycles.Close') }}
          </button>
        </div>

        </form>
      </div>
    </div>
  </div>
<!-- / Custom Step Modal -->
<!-- Screening Step Modal -->
  {{-- <div 
    class="modal fade" 
    id="screeningStepModal" 
    tabindex="-1" 
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCenterTitle">Edit Step Information</h5>
          <button 
            type="button" 
            class="btn-close" 
            data-bs-toggle="modal" 
            data-bs-target="#screeningStepModal" 
            aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <form id="edit-step-form">
            <fieldset class="row">
              <div class="col-12 form-group mb-3">
                <label class="form-label" for="select-step">Select Step</label>
                <select class="form-select border light-shadow">
                  <option>Step 1</option>
                  <option>Step 2</option>
                  <option>Step 3</option>
                </select>
              </div>
              <div class="col-12 form-group mb-3">
                <label class="form-label" for="step-name">Step Name</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="step-name" 
                  placeholder="Step Name"
                />
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">Start Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control" 
                    id="flatpickr-date"
                    placeholder="Start Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">End Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control" 
                    id="flatpickr-date"
                    placeholder="End Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-12 form-group mb-5">
                <label class="form-label" form="step-desc">Description</label>
                <textarea 
                  class="form-control" 
                  id="step-desc" 
                  rows="3" 
                  placeholder="Description"
                ></textarea>
              </div>
              <div class="col-12 form-group mb-5">
                <label class="form-label" form="screening-type">Choose screening type</label>
                <div class="d-flex flex-nowrap">
                  @foreach ($screeningTypes as $type)
                    <button 
                      type="button" 
                      class="custom-pill rounded-pill border light-shadow"
                      style="width: 170px; height: 30px;"
                      onclick="this.classList.toggle('btn-primary')"
                    >
                      <div style="font-size: 12px;" class="text-nowrap">{{ $type }}</div>
                    </button>
                  @endforeach
                </div>
              </div>
              <div class="col-12 form-group mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <label for="TagifyUserList" class="form-label">Choose judges</label>
                  <div class="d-flex form-check form-switch gap-12">
                    <label for="userSwitch">{{ __('cycles.Notify them') }}</label>
                    <input class="form-check-input" type="checkbox" id="userSwitch">
                  </div>
                </div>
                <input
                  id="TagifyUserList"
                  name="TagifyUserList"
                  class="form-control"
                  value="" 
                  placeholder="Choose users"
                />
              </div>
            </fieldset>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" value="submit" class="btn btn-primary">
            <i class="ti ti-circle-check ti-24px me-2"></i>
            {{ __('cycles.Submit') }}
          </button>
          <button 
            type="button" 
            data-bs-toggle="modal" 
            data-bs-target="#screeningStepModal" 
            class="btn btn-outline-primary ml-1">
            <i class="ti ti-circle-x ti-24px me-2"></i>
            {{ __('cycles.Close') }}
          </button>
        </div>
      </div>
    </div>
  </div> --}}
<!-- / Screening Step Modal -->
<!-- Mentorship Step Modal -->
  {{-- <div 
    class="modal fade" 
    id="mentorshipStepModal" 
    tabindex="-1" 
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCenterTitle">Edit Step Information</h5>
          <button 
            type="button" 
            class="btn-close" 
            data-bs-toggle="modal" 
            data-bs-target="#mentorshipStepModal" 
            aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <form id="edit-step-form">
            <fieldset class="row">
              <div class="col-12 form-group mb-3">
                <label class="form-label" for="step-name">Mentorship Name</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="step-name" 
                  placeholder="Step Name"
                />
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">Start Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control" 
                    id="flatpickr-date"
                    placeholder="Start Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">End Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control" 
                    id="flatpickr-date"
                    placeholder="End Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-12 form-group mb-5">
                <label class="form-label" form="step-desc">Description</label>
                <textarea 
                  class="form-control" 
                  id="step-desc" 
                  rows="3" 
                  placeholder="Description"
                ></textarea>
              </div>
              <div class="col-12 form-group mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <label for="TagifyUserList" class="form-label">Choose Mentors</label>
                  <div class="d-flex form-check form-switch gap-12">
                    <label for="userSwitch">{{ __('cycles.Notify them') }}</label>
                    <input class="form-check-input" type="checkbox" id="userSwitch">
                  </div>
                </div>
                <input
                  id="TagifyUserList"
                  name="TagifyUserList"
                  class="form-control"
                  value="" 
                  placeholder="Choose users"
                />
              </div>
            </fieldset>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" value="submit" class="btn btn-primary">
            <i class="ti ti-circle-check ti-24px me-2"></i>
            {{ __('cycles.Submit') }}
          </button>
          <button 
            type="button" 
            data-bs-toggle="modal" 
            data-bs-target="#mentorshipStepModal" 
            class="btn btn-outline-primary ml-1">
            <i class="ti ti-circle-x ti-24px me-2"></i>
            {{ __('cycles.Close') }}
          </button>
        </div>
      </div>
    </div>
  </div> --}}
<!-- / Mentorship Step Modal -->
<!-- Training Step Modal -->
  {{-- <div 
    class="modal fade" 
    id="trainingStepModal" 
    tabindex="-1" 
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCenterTitle">Edit Step Information</h5>
          <button 
            type="button" 
            class="btn-close" 
            data-bs-toggle="modal" 
            data-bs-target="#trainingStepModal" 
            aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <form id="edit-step-form">
            <fieldset class="row">
              <div class="col-12 form-group mb-3">
                <label class="form-label" for="step-name">Training Name</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="step-name" 
                  placeholder="Step Name"
                />
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">Start Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control" 
                    id="flatpickr-date"
                    placeholder="Start Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">End Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control" 
                    id="flatpickr-date"
                    placeholder="End Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-12 form-group mb-5">
                <label class="form-label" form="step-desc">Description</label>
                <textarea 
                  class="form-control" 
                  id="step-desc" 
                  rows="3" 
                  placeholder="Description"
                ></textarea>
              </div>
            </fieldset>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" value="submit" class="btn btn-primary">
            <i class="ti ti-circle-check ti-24px me-2"></i>
            {{ __('cycles.Submit') }}
          </button>
          <button 
            type="button" 
            data-bs-toggle="modal" 
            data-bs-target="#trainingStepModal" 
            class="btn btn-outline-primary ml-1">
            <i class="ti ti-circle-x ti-24px me-2"></i>
            {{ __('cycles.Close') }}
          </button>
        </div>
      </div>
    </div>
  </div> --}}
<!-- / Training Step Modal -->
<!-- Final Screening Step Modal -->
  {{-- <div 
    class="modal fade" 
    id="finalScreeningModal" 
    tabindex="-1" 
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCenterTitle">Edit Step Information</h5>
          <button 
            type="button" 
            class="btn-close" 
            data-bs-toggle="modal" 
            data-bs-target="#finalScreeningModal" 
            aria-label="Close">
          </button>
        </div>
        <div class="modal-body">
          <form id="edit-step-form">
            <fieldset class="row">
              <div class="col-12 form-group mb-3">
                <label class="form-label" for="step-name">Final screening name</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="step-name" 
                  placeholder="Step Name"
                />
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">Start Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control" 
                    id="flatpickr-date"
                    placeholder="Start Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-md-6 form-group mb-3">
                <label class="form-label" for="flatpickr-date">End Date</label>
                <div class="position-relative">
                  <input 
                    type="text" 
                    class="form-control" 
                    id="flatpickr-date"
                    placeholder="End Date"
                  />
                  <div class="position-absolute top-0 end-0 mt-2 me-3">
                    <i class="ti ti-calendar ti-24px text-primary"></i>
                  </div>
                </div>
              </div>
              <div class="col-12 form-group mb-5">
                <label class="form-label" form="step-desc">Description</label>
                <textarea 
                  class="form-control" 
                  id="step-desc" 
                  rows="3" 
                  placeholder="Description"
                ></textarea>
              </div>
              <div class="col-12 form-group mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <label for="TagifyUserList" class="form-label">Choose judges</label>
                  <div class="d-flex form-check form-switch gap-12">
                    <label for="userSwitch">{{ __('cycles.Notify them') }}</label>
                    <input class="form-check-input" type="checkbox" id="userSwitch">
                  </div>
                </div>
                <input
                  id="TagifyUserList"
                  name="TagifyUserList"
                  class="form-control"
                  value="" 
                  placeholder="Choose users"
                />
              </div>
            </fieldset>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" value="submit" class="btn btn-primary">
            <i class="ti ti-circle-check ti-24px me-2"></i>
            {{ __('cycles.Submit') }}
          </button>
          <button 
            type="button" 
            data-bs-toggle="modal" 
            data-bs-target="#finalScreeningModal" 
            class="btn btn-outline-primary ml-1">
            <i class="ti ti-circle-x ti-24px me-2"></i>
            {{ __('cycles.Close') }}
          </button>
        </div>
      </div>
    </div>
  </div> --}}
<!-- / Final Screening Step Modal -->

  {{-- @include('admin.programs.partials._edit_program_modal', ['mode' => 'edit']) --}}
@stop

@section('scripts')
<!-- Vendors JS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="/vuexy/assets/vendor/libs/moment/moment.js"></script>
<script src="/vuexy/assets/vendor/libs/flatpickr/flatpickr.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/jquery-timepicker/jquery-timepicker.js"></script>
<script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
<script src="/app-assets/vendors/js/extensions/dragula.min.js"></script>
<script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
<script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
@stop

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

                    // Positioned via /js/daterangepicker-viewport.js (viewport-fixed)
                    // rather than drops:'up', which pushed it off-screen in the modal.
                    $('#step-range-picker').daterangepicker({
                        opens    : isRtl ? 'left' : 'right',
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
@stop