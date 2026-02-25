@extends('layouts.admin')

@php
// $startup = [
//   'name' => 'PayLinkx',
//   'logo' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTc9APxkj0xClmrU3PpMZglHQkx446nQPG6lA&s',
//   'is_alumni' => true,
//   'description' => __('startup.overview_description'),
//   'organization' => 'Growthlabs',
//   'location' => 'Egypt · Cairo',
//   'established_at' => '2019-05-01',
//   'business_type' => 'B2B',
//   'development_stage' => 'Growth',
//   'funding_stage' => 'Seed',
//   'industry' => 'Financial Services',
//   'sector' => 'Payments',
//   'team_size' => '25+',
//   'technologies' => 'AI, APIs, Cloud',
//   'program' => [
//     'name' => 'Fintech Accelerator 2025',
//     'cohort' => 'Cohort 3',
//     'start' => '10 Jan 2025',
//     'end' => '15 Jun 2025',
//   ],
//   'sdgs' => ['no poverty', 'zero hunger', 'good health and well being', 'quality education'],
//   'links' => [
//     'website' => '#',
//     'linkedin' => '#',
//     'twitter' => '#',
//     'facebook' => '#',
//     'instagram' => '#',
//     'youtube' => '#',
//   ],
// ];

$startupStatValues = [
  'screening_score'        => ['current' => 4.3, 'total' => 5],
  'final_screening_score'  => ['current' => 4.6, 'total' => 5],
  'training_hours'         => ['hours' => 32, 'minutes' => 15],
  'mentorship_hours'       => ['hours' => 24, 'minutes' => 30],
  'attendance_rate'        => 94,
  'milestones_completed'   => ['current' => 7, 'total' => 8],
  'quizzes_completed'      => ['current' => 12, 'total' => 12],
  'avg_quiz_score'         => 87,
  'in_kind_services'       => 8,
  'surveys_completed'      => ['current' => 5, 'total' => 5],
  'tasks_completed'        => ['current' => 24, 'total' => 26],
];

// Team configuration and data
// $teamData = [
//   'leader' => [
//     'id' => 'LD-001',
//     'name' => 'Omar Hassan',
//     'country' => 'Egypt',
//     'email' => 'omar@example.com',
//     'phone' => '+20 100 000 0000',
//   ],
//   'founders' => [
//     ['name' => 'Omar Hassan', 'email' => 'omar@example.com', 'linkedin' => '#'],
//     ['name' => 'Sara Ali', 'email' => 'sara@example.com', 'linkedin' => '#'],
//   ],
//   'employees' => [
//     ['name' => 'Ahmed Mohamed', 'job' => 'Backend engineer', 'gender' => 'Male', 'employment' => 'Full time', 'start_date' => '2022-03-01'],
//     ['name' => 'Mona Adel', 'job' => 'Product manager', 'gender' => 'Female', 'employment' => 'Full time', 'start_date' => '2021-10-15'],
//   ],
// ];

// Ownership and investors data
// $ownershipData = [
//   'shareholders' => [
//     ['name' => 'Omar Hassan', 'role' => 'founder_role', 'ownership' => '40%', 'text_class' => 'primary'],
//     ['name' => 'Sara Ali', 'role' => 'cofounder_role', 'ownership' => '30%', 'text_class' => 'info'],
//     ['name' => 'ABC Ventures', 'role' => 'investor_role', 'ownership' => '20%', 'text_class' => 'success'],
//   ],
//   'investors' => [
//     ['name' => 'ABC Ventures', 'type' => 'vc'],
//     ['name' => 'XYZ Angels Syndicate', 'type' => 'angel_group'],
//   ],
// ];

// Screening data
$screeningData = [
  'initial' => [
    'step_id' => 'STP-001',
    'overall_score' => 4.3,
    'date' => 'Jan 2025',
    'criteria' => [
      ['name' => 'Problem & solution', 'weight' => '30%', 'score' => 4.5],
      ['name' => 'Market size', 'weight' => '25%', 'score' => 4.2],
      ['name' => 'Team', 'weight' => '20%', 'score' => 4.6],
    ],
    'judges' => [
      ['name' => 'Judge One', 'id' => 'J-001', 'score' => 4.2, 'comments' => 'Clear problem and strong traction in Egyptian market.'],
      ['name' => 'Judge Two', 'id' => 'J-002', 'score' => 4.4, 'comments' => 'Team has relevant experience and solid execution plan.'],
    ],
  ],
  'final' => [
    'step_id' => 'STP-010',
    'overall_score' => 4.6,
    'date' => 'Mar 2025',
    'criteria' => [
      ['name' => 'Revenue & traction', 'weight' => '30%', 'score' => 4.7],
      ['name' => 'Scalability', 'weight' => '25%', 'score' => 4.5],
    ],
    'judges' => [
      ['name' => 'Judge Three', 'id' => 'J-010', 'score' => 4.6, 'comments' => 'Ready for next stage of funding, strong growth indicators.'],
    ],
  ],
];

// Mentorship data
// $mentorshipData = [
//   [
//     'mentor' => 'Mentor A',
//     'id' => 'M-001',
//     'step_name' => 'Growth mentoring',
//     'step_id' => 'STP-M-03',
//     'date' => '2025-03-10',
//     'duration' => '1.5h',
//     'founder_attended' => 'Omar Hassan',
//     'rating' => '5',
//     'comments' => 'Helpful session on pricing strategy and B2B sales pipeline.',
//   ],
//   // Add more mentorship sessions here
// ];

//$milestones = [
  //[
    //'title' => 'MVP launch',
   // 'description' => 'Launch first production version with payment gateway integration.',
   // 'due_date' => '2025-04-15',
   // 'completion' => 80, // %
   // 'deliverables' => [
     //   ['name' => 'Technical architecture PDF', 'type' => 'pdf', 'link' => '#'],
       // ['name' => 'MVP demo video', 'type' => 'video', 'link' => '#'],
    //],
  //],
//  // Add more milestones here
//];

$deliverableIcons = [
  'pdf' => 'ti-file-pdf',
  'video' => 'ti-video',
];

// $tasks = [
//   [
//     'title' => 'Customer interviews',
//     'task_id' => 'TSK-001',
//     'session_id' => 'TR-101',
//     'session_title' => 'Lean startup fundamentals',
//     'description' => 'Conduct ten customer interviews and summarize findings.',
//     'due_date' => '2025-02-10',
//     'completion_date' => '2025-02-08',
//     'mentor' => 'Mentor A',
//     'created_by' => 'Program manager',
//     'status' => 'Completed',
//   ],
//   // Add more tasks here
// ];

// $quizzes = [
//   [
//     'quiz_id' => 'QZ-001',
//     'title' => 'Lean startup quiz',
//     'date_completed' => '2025-02-05',
//     'founder' => 'Omar Hassan',
//     'score' => '90%',
//     'score_badge' => 'warning',
//     'status' => __('startup.passed'),
//   ],
//   // Add more quizzes here
// ];

//$surveys = [
  //[
   // 'survey_id' => 'SV-001',
    //'title' => 'Mid program satisfaction',
    //'program_step' => 'Mid review step',
    //'date_completed' => '2025-03-01',
    //'answers_link' => '#', // هنا مش رابط هنا هيكون فى كل سؤال واجابته لوحدها 
    //'badge_class' => 'primary',
  //],
  //// Add more surveys here
//];

//$services = [
//  [
//    'name' => 'Cloud credits',
 //   'service_id' => 'SRV-001',
 //   'description' => 'Cloud infrastructure credits for twelve months to support product development.',
 //   'badge_class' => 'primary',
 // ],
 // [
 //   'name' => 'Legal advisory session',
  //  'service_id' => 'SRV-002',
 //   'description' => 'One-on-one session with legal partner for company setup and shareholder agreement review.',
  //  'badge_class' => 'primary',
 // ],
 // // Add more services here
//];

// $forms = [
//   [
//     'title' => 'Startup profiling form',
//     'form_id' => 'FM-001',
//     'step_name' => 'Application step form',
//     'step_id' => 'STP-FM-01',
//     'submission_date' => '2025-01-05 14:22',
//     'badge_class' => 'primary',
//     'modal_id' => 1,
//     'fields' => [
//       [
//         'name' => 'Startup description',
//         'type' => 'text',
//         'value' => 'AI powered payment orchestration platform for SMEs.',
//         'attachment' => null,
//       ],
//       [
//         'name' => 'Pitch deck file',
//         'type' => 'file',
//         'value' => 'Uploaded',
//         'attachment' => '#',
//       ],
//       [
//         'name' => 'Industry',
//         'type' => 'selection',
//         'value' => 'Financial services',
//         'attachment' => null,
//       ],
//       [
//         'name' => 'Team size',
//         'type' => 'number',
//         'value' => '25',
//         'attachment' => null,
//       ],
//       [
//         'name' => 'Business model',
//         'type' => 'selection',
//         'value' => 'B2B SaaS',
//         'attachment' => null,
//       ],
//       [
//         'name' => 'Competitive landscape',
//         'type' => 'text',
//         'value' => 'Competitive landscape is moderate with a few dominant players.',
//         'attachment' => null,
//       ],
//     ],
//   ],
//   // Add more forms here
// ];

$badgeColors = ['primary', 'secondary', 'info', 'warning', 'danger', 'success'];
@endphp

@section('content')
<div class="">
  <div class="" id="contentToExport">
    <div class="row g-3 align-items-center mb-4">
      <div class="col-12 col-md-6">
        <h4 class="mb-1">
          {{ __('startup.alumni_report_title') }}
        </h4>
        <p class="mb-0 small">
          {{ __('startup.alumni_report_subtitle') }}
        </p>
      </div>
      <div id="header-actions" class="col-12 col-md-6 d-flex justify-content-md-end gap-2 flex-wrap">
        <span class="badge bg-label-success pe-4 rounded-pill d-inline-flex align-items-center">
          <i class="ti ti-point-filled me-1"></i>
          <span>{{ __('startup.alumni') }}</span>
        </span>
        <button id="export-pdf-button" class="btn btn-primary btn-sm">
          <i class="ti ti-file-type-pdf me-1"></i>{{ __('startup.export_pdf') }}
        </button>
        <a id="export-csv" class="btn btn-outline-secondary btn-sm">
          <i class="ti ti-file-type-csv me-1"></i>{{ __('startup.export_csv') }}
        </a>
      </div>
    </div>

    @php
      $startupInfo = [
        ['icon' => 'calendar-event', 'color' => 'info', 'label' => __('startup.est_date'), 'value' => $startup['established_at']],
        ['icon' => 'briefcase', 'color' => 'warning', 'label' => __('startup.business_type'), 'value' => $startup['business_type']],
        ['icon' => 'device-gamepad-3', 'color' => 'success', 'label' => __('startup.industry'), 'value' => $startup['industry']],
        ['icon' => 'packages', 'color' => 'danger', 'label' => __('startup.sector'), 'value' => $startup['sector']],
        ['icon' => 'users', 'color' => 'primary', 'label' => __('startup.team_size'), 'value' => $startup['team_size']],
      ];

      $programInfo = [
        ['icon' => 'components', 'color' => 'secondary', 'label' => __('startup.program_Name'), 'value' => $startup['program']['name']],
        ['icon' => 'color-swatch', 'color' => 'danger', 'label' => __('startup.program_cohort'), 'value' => $startup['program']['cohort']],
        ['icon' => 'calendar-stats', 'color' => 'muted', 'label' => __('startup.program_start'), 'value' => $startup['program']['start']],
        ['icon' => 'calendar-check', 'color' => 'success', 'label' => __('startup.program_end'), 'value' => $startup['program']['end']],
      ];

      $socials = [
        ['key' => 'website', 'icon' => 'world'],
        ['key' => 'linkedin', 'icon' => 'brand-linkedin'],
        ['key' => 'twitter', 'icon' => 'brand-x'],
        ['key' => 'facebook', 'icon' => 'brand-facebook'],
        ['key' => 'instagram', 'icon' => 'brand-instagram'],
        ['key' => 'youtube', 'icon' => 'brand-youtube'],
      ];
    @endphp

    <!-- Startup Profile Card -->
    <div class="row mb-6">
      <div class="col-12">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">

            {{-- Header --}}
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 pb-5 border-bottom">
              <div class="grow">
                <div class="d-flex align-items-center gap-3 mb-4">
                  <div class="avatar avatar-lg">
                    @if ($startup['logo'])
                      <img src="{{ $startup['logo'] }}" alt="Logo" class="rounded-circle">
                    @else
                      <div class="avatar-initial rounded-circle bg-label-primary">
                        <i class="ti ti-building-skyscraper ti-xl"></i>
                      </div>
                    @endif
                  </div>
                  <div>
                    <h5 class="mb-0 fw-bold text-dark">{{ $startup['name'] }}</h5>
                    <span class="text-secondary small">{{ $startup['industry']  ? __('admin.categories.' . $startup['industry']) : '---' }}</span>
                  </div>
                </div>
                <p class="text-muted small mb-0">{{ $startup['description'] }}</p>
              </div>

              {{-- Badges --}}
              <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-label-primary rounded-pill">
                  <i class="ti ti-building me-1"></i>{{ $startup['organization'] }}
                </span>
                <span class="badge bg-label-info rounded-pill">
                  <i class="ti ti-map-pin me-1"></i>{{ $startup['location'] }}
                </span>
              </div>
            </div>

            {{-- Startup Info --}}
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-4 py-5 border-bottom">
              @foreach ($startupInfo as $item)
                <div class="text-center">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-2 d-flex align-items-center justify-content-center gap-1">
                    <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }}"></i>
                    {{ $item['label'] }}
                  </small>
                  <h6 class="fw-bold text-dark mb-0">{{ $item['value'] }}</h6>
                </div>
              @endforeach
            </div>

            {{-- Program Info --}}
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-4 py-5">
              @foreach ($programInfo as $item)
                <div class="text-center">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-2 d-flex align-items-center justify-content-center gap-1">
                    <i class="ti ti-{{ $item['icon'] }} text-{{ $item['color'] }}"></i>
                    {{ $item['label'] }}
                  </small>
                  <h6 class="fw-bold text-dark mb-0">{{ $item['value'] }}</h6>
                </div>
              @endforeach
            </div>

            {{-- Social Links --}}
            <div class="pt-5 d-flex flex-wrap gap-4 border-top">
              @foreach ($socials as $social)
                @if ($social['key'])
                  <a href="{{ $startup['links'][$social['key']] }}"
                    class="d-inline-flex align-items-center fw-medium text-decoration-none link-primary hover-animate">
                    <i class="ti ti-{{ $social['icon'] }} me-2"></i>
                    {{ __('startup.' . $social['key']) }}
                    <i class="ti ti-external-link ms-1 small"></i>
                  </a>
                @endif
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Startup Profile Card -->

    @php
      $startupStatsConfig = [
        'screening_score' => [
            'format' => 'fraction',
            'label'  => __('startup.screening_score'),
            'icon'   => 'search',
            'color'  => 'primary',
        ],
        'final_screening_score' => [
            'format' => 'fraction',
            'label'  => __('startup.final_screening_score'),
            'icon'   => 'award',
            'color'  => 'success',
        ],
        'training_hours' => [
            'format' => 'time',
            'label'  => __('startup.training_hours'),
            'icon'   => 'book',
            'color'  => 'warning',
        ],
        'mentorship_hours' => [
            'format' => 'time',
            'label'  => __('startup.mentorship_hours'),
            'icon'   => 'school',
            'color'  => 'info',
        ],
        'attendance_rate' => [
            'format' => 'percentage',
            'label'  => __('startup.attendance_rate'),
            'icon'   => 'user-check',
            'color'  => 'success',
        ],
        'milestones_completed' => [
            'format' => 'fraction',
            'label'  => __('startup.milestones_completed'),
            'icon'   => 'flag-check',
            'color'  => 'primary',
        ],
        'quizzes_completed' => [
            'format' => 'fraction',
            'label'  => __('startup.quizzes_completed'),
            'icon'   => 'help',
            'color'  => 'warning',
        ],
        'avg_quiz_score' => [
            'format' => 'percentage',
            'label'  => __('startup.avg_quiz_score'),
            'icon'   => 'chart-bar',
            'color'  => 'success',
        ],
        'in_kind_services' => [
            'format' => 'number',
            'label'  => __('startup.in_kind_services'),
            'icon'   => 'gift',
            'color'  => 'info',
        ],
        'surveys_completed' => [
            'format' => 'fraction',
            'label'  => __('startup.surveys_completed'),
            'icon'   => 'mood-smile',
            'color'  => 'primary',
        ],
        'tasks_completed' => [
            'format' => 'fraction',
            'label'  => __('startup.tasks_completed'),
            'icon'   => 'checklist',
            'color'  => 'primary',
        ],
      ];
    @endphp
    <!-- Outcome Summary Section -->
    <div class="row mb-6">
      <div class="col-12">
        <div class="row gy-6">

          @foreach ($startupStatsConfig as $key => $config)
            @php $value = $startupStatValues[$key] ?? null; @endphp

            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
              <div class="card card-border-shadow-{{ $config['color'] }} h-100 cursor-pointer">
                <div class="card-body text-center">

                  <div class="d-flex align-items-center justify-content-center mb-2">
                    <div class="avatar avatar-md me-4">
                      <div class="avatar-initial rounded bg-label-{{ $config['color'] }}">
                        <i class="ti ti-{{ $config['icon'] }} ti-md"></i>
                      </div>
                    </div>

                    <h5 class="mb-0">
                      @switch($config['format'])

                        @case('fraction')
                          {{ $value['current'] ?? 0 }} / {{ $value['total'] ?? 0 }}
                          @break

                        @case('time')
                          {{ $value['hours'] ?? 0 }}h {{ $value['minutes'] ?? 0 }}m
                          @break

                        @case('percentage')
                          {{ $value ?? 0 }}%
                          @break

                        @default
                          {{ $value ?? 0 }}

                      @endswitch
                    </h5>
                  </div>

                  <small class="text-center">{{ $config['label'] }}</small>

                </div>
              </div>
            </div>

          @endforeach

        </div>
      </div>
    </div>
    <!-- / Outcome Summary Section -->

    @php
      $startupTabs = [
        [
          'id' => 'basic',
          'icon' => 'info-square',
          'label' => __('startup.startup_info_tab'),
          'active' => true,
        ],
        [
          'id' => 'team',
          'icon' => 'users',
          'label' => __('startup.team_tab'),
        ],
        [
          'id' => 'ownership',
          'icon' => 'briefcase',
          'label' => __('startup.ownership_investors_tab'),
        ],
        [
          'id' => 'screening',
          'icon' => 'search',
          'label' => __('startup.screening_tab'),
        ],
        [
          'id' => 'training',
          'icon' => 'school',
          'label' => __('startup.training_tab'),
        ],
        [
          'id' => 'mentorship',
          'icon' => 'school',
          'label' => __('startup.mentorship_tab'),
        ],
        [
          'id' => 'milestones',
          'icon' => 'flag-check',
          'label' => __('startup.milestones_tab'),
        ],
        [
          'id' => 'tasks',
          'icon' => 'checklist',
          'label' => __('startup.tasks_tab'),
        ],
        [
          'id' => 'quizzes',
          'icon' => 'help',
          'label' => __('startup.quizzes_tab'),
        ],
        [
          'id' => 'surveys',
          'icon' => 'mood-smile',
          'label' => __('startup.surveys_tab'),
        ],
        [
          'id' => 'services',
          'icon' => 'gift',
          'label' => __('startup.services_tab'),
        ],
        [
          'id' => 'forms',
          'icon' => 'file-text',
          'label' => __('startup.forms_tab'),
        ],
      ];
    @endphp
    <!-- Navigation Pills -->
    <div id="navigation-pills" class="card mb-4 shadow-sm">
      <div class="card-body px-4 pt-4 pb-0">
        <div class="nav-align-top">
          <ul class="nav nav-pills flex-nowrap gap-2 pb-4"
              style="max-width: 100%; overflow-x: auto;"
              role="tablist">

            @foreach ($startupTabs as $tab)
              <li class="nav-item" role="presentation">
                <a
                  type="button"
                  class="nav-link {{ !empty($tab['active']) ? 'active' : '' }}"
                  id="tab-{{ $tab['id'] }}-trigger"
                  href="#tab-{{ $tab['id'] }}"
                  role="tab"
                  aria-controls="tab-{{ $tab['id'] }}"
                  aria-selected="{{ !empty($tab['active']) ? 'true' : 'false' }}"
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
    <!--/ Navigation Pills -->

    <div class="tab-content p-0 shadow-none" id="alumniTabContent" style="max-width: 100%; overflow-x: hidden;">
      <!-- Basic Info Tab -->
      <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-trigger">
        <div id="basic-info" class="card mb-6 shadow-sm">
          <div class="card-header d-flex justify-content-between align-it">
            <div>
              <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-capitalize">
                <i class="ti ti-file-info text-primary"></i>{{ __('startup.basic_info_title') }}
              </h5>
              <small class="text-muted">{{ __('startup.static_data_note') }}</small>
            </div>
            <span class="badge bg-label-success rounded-pill" style="height: fit-content;">
              <i class="ti ti-check me-1"></i>{{ $startup['is_alumni'] ? __('startup.alumni') : __('startup.non_alumni') }}
            </span>
          </div>
          <div class="card-body">
            <div class="row g-4 mb-4">
              <div class="col-12 col-md-4">
                <div class="p-3 rounded bg-light-subtle border">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-1">
                    <i class="ti ti-building-skyscraper me-1"></i>{{ __('startup.startup_name') }}
                  </small>
                  <p class="mb-0 fw-bold text-dark">{{ $startup['name'] }}</p>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-3 rounded bg-light-subtle border">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-1">
                    <i class="ti ti-calendar me-1"></i>{{ __('startup.established_date') }}
                  </small>
                  <p class="mb-0 fw-bold text-dark">{{ $startup['established_at'] }}</p>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-3 rounded bg-light-subtle border">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-1">
                    <i class="ti ti-briefcase me-1"></i>{{ __('startup.business_type') }}
                  </small>
                  <p class="mb-0 fw-bold text-dark">{{ $startup['business_type'] ?? '--' }}</p>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-3 rounded bg-light-subtle border">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-1">
                    <i class="ti ti-bulb me-1"></i>{{ __('startup.technology') }}
                  </small>
                  <p class="mb-0 fw-bold text-dark">{{ $startup['technologies'] ?? '--' }}</p>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-3 rounded bg-light-subtle border">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-1">
                    <i class="ti ti-world me-1"></i>{{ __('startup.development_stage') }}
                  </small>
                  <p class="mb-0 fw-bold text-dark">{{ $startup['development_stage'] ? __('admin.' . $startup['development_stage']) : '---' }}</p>
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="p-3 rounded bg-light-subtle border">
                  <small class="text-muted text-uppercase fw-semibold d-block mb-1">
                    <i class="ti ti-world me-1"></i>{{ __('startup.funding_stage') }}
                  </small>
                  <p class="mb-0 fw-bold text-dark">{{ $startup['funding_stage'] ?? '---' }}</p>
                </div>
              </div>
            </div>
            <hr class="my-4" />
            <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
              <i class="ti ti-target text-success"></i>{{ __('startup.sustainability_goals') }}
            </h6>
           
            <div class="row">
              @if(empty($startup['sdgs']))
                <span class="badge p-3 bg-light-subtle text-dark border">{{ __('startup.no_sdg') }}</span>
              @else
                <div class="d-flex align-items-center gap-4">
                
                  @foreach($startup['sdgs'] as $sdg)
                    <span class="badge p-3 bg-light-subtle text-dark border">{{ $sdg }}</span>
                  @endforeach
                </div>
              @endif
              {{-- @dd($legal_information) --}}
              <hr class="my-4" />
              <div class="col-12 col-md-6">
                <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                  <i class="ti ti-shield-check text-warning"></i>{{ __('startup.legal_information') }}
                </h6>
                <div class="border rounded p-3">
                  <dl class="row mb-0 gy-2">
                    <dt class="col-5 text-muted fw-normal small">{{ __('startup.legal_status') }}</dt>
                    <dd class="col-7 mb-0 text-end fw-bold text-dark">{{ $legal_information[0]['Registered'] ?? '--' }}</dd>
                    <dt class="col-5 text-muted fw-normal small">{{ __('startup.commercial_register') }}</dt>
                    <dd class="col-7 mb-0 text-end fw-bold text-dark">
                      @if(!empty($legal_information[0]['Commercial_Register']))
                        <a href="{{ asset($legal_information[0]['Commercial_Register']) ?? '#' }}" class="fw-medium text-primary"><i class="ti ti-download me-1"></i>{{ __('startup.download') }}</a>
                      @endif
                    </dd>
                    <dt class="col-5 text-muted fw-normal small">{{ __('startup.tax_card') }}</dt>
                    <dd class="col-7 mb-0 text-end fw-bold text-dark">
                      @if(!empty($legal_information[0]['Tax_Card']))
                        <a href="{{ asset($legal_information[0]['Tax_Card']) ?? '#' }}" class="fw-medium text-primary"><i class="ti ti-download me-1"></i>{{ __('startup.download') }}</a>
                      @endif
                    </dd>
                  </dl>
                </div>
              </div>
              
              <div class="col-12 col-md-6">
                <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                  <i class="ti ti-presentation text-info"></i>{{ __('startup.pitch_deck_and_video') }}
                </h6>
                <div class="d-flex flex-column justify-content-between">
                  <a href="{{ $pitch_deck[0]['pitch_deck'] != '' ? asset($pitch_deck[0]['pitch_deck']) : '#' }}" class="btn btn-lg btn-outline-primary w-100 mb-2 d-flex align-items-center justify-content-between">
                    <span class="d-flex align-items-center gap-2">
                      <i class="ti ti-slideshow"></i>{{ __('startup.pitch_deck_file') }}
                    </span>
                    <i class="ti ti-download"></i>
                  </a>
                  <a href="{{ $pitch_deck[0]['pitch_deck_video'] != '' ? asset($pitch_deck[0]['pitch_deck_video']) : '#' }}" class="btn btn-lg btn-outline-info w-100 d-flex align-items-center justify-content-between">
                    <span class="d-flex align-items-center gap-2">
                      <i class="ti ti-video"></i>{{ __('startup.pitch_video') }}
                    </span>
                    <i class="ti ti-player-play"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--/ Basic Info Tab -->

      <!-- Team Tab -->
      <div class="tab-pane fade show active" id="tab-team" role="tabpanel" aria-labelledby="tab-team-trigger">
        <div id="team" class="card mb-6 shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-capitalize">
                <i class="ti ti-users-group text-primary"></i>{{ __('startup.team_tab') }}
              </h5>
              <small class="text-muted">{{ __('startup.team_section_subtitle') }}</small>
            </div>
            <span class="badge bg-label-info">
              <i class="ti ti-users me-1"></i>{{ __('startup.team_size_badge', ['count' => count($teamData['employees']) +
              count($teamData['founders']) + 1]) }}
            </span>
          </div>
      
          <div class="card-body">
            <div class="row g-6">
              {{-- Leader --}}
              <div class="col-12 col-lg-4">
                <div class="card card-body border h-100 shadow-sm"
                  style="background: linear-gradient(135deg, rgba(88, 86, 214, 0.08) 0%, transparent 100%);">
                  <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-crown text-warning"></i>{{ __('startup.startup_leader') }}
                  </h6>
                  
                  @if(empty($teamData['leader']['name']))
                    <div class="col-12 text-center text-muted">{{ __('startup.no_leader') }}</div>
                  @else
                    <dl class="row mb-0 gy-3">
                      @foreach ($teamData['leader'] as $key => $value)
                      <dt class="col-5 text-secondary fw-normal small">{{ ucfirst($key) }}</dt>
                      <dd class="col-7 text-end fw-bold text-dark">
                        @if($key === 'email')
                        <a href="mailto:{{ $value }}" class="fw-medium text-primary">{{ $value }}</a>
                        @else
                        {{ $value }}
                        @endif
                      </dd>
                      @endforeach
                    </dl>
                  @endif
                </div>
              </div>
      
              {{-- Founders --}}
              <div class="col-12 col-lg-8">
                <div class="card card-body border h-100 shadow-sm">
                  <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-users text-info"></i>{{ __('startup.founders') }}
                  </h6>
                  @if(empty($teamData['founders'][0]['name']))
                    <div class="col-12 text-center text-muted">{{ __('startup.no_founders') }}</div>
                  @else
                  <div class="table-responsive">
                      <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                          <tr>
                            <th class="text-center text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.name') }}</th>
                            <th class="text-center text-nowrap"><i class="ti ti-mail me-1"></i>{{ __('startup.email') }}</th>
                            <th class="text-end"><i class="ti ti-link me-1"></i>{{ __('startup.linkedin') }}</th>
                          </tr>
                        </thead>
                        <tbody>
                            @foreach ($teamData['founders'] as $founder)
                            <tr class="border-top">
                              <td class="fw-medium">{{ $founder['name'] }}</td>
                              <td>{{ $founder['email'] }}</td>
                              <td class="text-end">
                                <a href="{{ $founder['linkedin'] }}" class="fw-medium text-primary">
                                  {{ __('startup.linkedin_view_profile') }}
                                </a>
                              </td>
                            </tr>
                            @endforeach
                        </tbody>
                      </table>
                    </div>
                  @endif
                </div>
              </div>
            </div>
      
            {{-- Employees --}}
            <div class="card card-body border shadow-sm mt-6">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-capitalize">
                  <i class="ti ti-briefcase text-success"></i>{{ __('startup.Employees') }}
                </h5>
                <span class="badge bg-label-secondary">
                  <i class="ti ti-users me-1"></i>{{ __('startup.count_label', ['value' => count($teamData['employees'])]) }}
                </span>
              </div>
              @if(count($teamData['employees']) > 0)
                 
                @if(empty($teamData['employees'][0]['name']))
                  <div class="col-12 text-center text-muted">{{ __('startup.no_employees') }}</div>
                @else
                  <div class="table-responsive">
                    <table class="table table-sm mb-0">
                      <thead class="table-light">
                        <tr>
                          <th class="text-center text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.full_name') }}</th>
                          <th class="text-center text-nowrap"><i class="ti ti-briefcase me-1"></i>{{ __('startup.job_title') }}
                          </th>
                          <th class="text-center text-nowrap"><i class="ti ti-gender-male me-1"></i>{{ __('startup.gender') }}
                          </th>
                          <th class="text-center text-nowrap"><i class="ti ti-clock me-1"></i>{{ __('startup.employment_type') }}
                          </th>
                          <th class="text-center text-nowrap"><i class="ti ti-calendar me-1"></i>{{ __('startup.start_date_label')
                            }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($teamData['employees'] as $employee)
                        <tr class="border-top">
                          <td class="fw-medium">{{ $employee['name'] }}</td>
                          <td>{{ $employee['job'] }}</td>
                          <td>
                            <span class="badge bg-label-{{ strtolower($employee['gender']) === 'male' ? 'info' : 'warning' }}">
                              {{ $employee['gender'] }}
                            </span>
                          </td>
                          <td>
                            <span class="badge bg-label-success">{{ $employee['employment'] }}</span>
                          </td>
                          <td>{{ $employee['start_date'] }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @endif
              @endif
            </div>
          </div>
        </div>
      </div>
      <!--/ Team Tab -->

      <!-- Ownership Tab -->
      <div class="tab-pane fade show active" id="tab-ownership" role="tabpanel" aria-labelledby="tab-ownership-trigger">
        <div id="ownership" class="card mb-6 shadow-sm">
          <div class="card-header">
            <div>
              <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-capitalize">
                <i class="ti ti-chart-pie text-success"></i>{{ __('startup.ownership_and_investors') }}
              </h5>
              <small class="text-muted">{{ __('startup.shareholders_funding_note') }}</small>
            </div>
          </div>
      
          <div class="card-body">
            <div class="row g-6">
      
              {{-- Shareholders --}}
              <div class="col-12 col-xl-6">
                <div class="card card-body border shadow-sm h-100">
                  <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-users text-primary"></i>{{ __('startup.shareholders') }}
                  </h6>
                  @if(count($ownershipData['shareholders']) > 0)
                    @if($ownershipData['shareholders'][0]['name'] == null)
                      <div class="col-12 text-center text-muted">{{ __('startup.no_shareholders') }}</div>
                    @else
                      <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                          <thead class="table-light">
                            <tr>
                              <th class="text-center text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.name') }}</th>
                              <th class="text-center text-nowrap"><i class="ti ti-briefcase me-1"></i>{{ __('startup.role') }}
                              </th>
                              <th class="text-end"><i class="ti ti-percent me-1"></i>{{ __('startup.ownership') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($ownershipData['shareholders'] as $key => $shareholder)
                            <tr class="border-top">
                              <td class="fw-medium">{{ $shareholder['name'] }}</td>
                              <td><span class="badge bg-label-{{ isset($badgeColors[$key + 1]) ? $badgeColors[$key + 1] : 'secondary' }}">{{ __('startup.' .
                                  $shareholder['role']) }}</span></td>
                              <td class="text-end fw-bold text-{{ $shareholder['text_class'] }}">{{ $shareholder['ownership'] }}
                              </td>
                            </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @endif
                  @endif
                </div>
              </div>
      
              {{-- Investors --}}
              <div class="col-12 col-xl-6">
                <div class="card card-body border shadow-sm h-100">
                  <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-coin text-warning"></i>{{ __('startup.investors') }}
                  </h6>
                  @if(count($ownershipData['investors']) > 0)
                    @if(empty($ownershipData['investors'][0]['name']))
                      <div class="col-12 text-center text-muted">{{ __('startup.no_investors') }}</div>
                    @else
                      <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                          <thead class="table-light">
                            <tr>
                              <th class="text-center text-nowrap"><i class="ti ti-building me-1"></i>{{ __('startup.name') }}</th>
                              <th class="text-end"><i class="ti ti-tag me-1"></i>{{ __('startup.type') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($ownershipData['investors'] as $key => $investor)
                            <tr class="border-top">
                              <td class="fw-medium">{{ $investor['name'] }}</td>
                              <td class="text-end"><span class="badge bg-label-{{ isset($badgeColors[$key + 1]) ? $badgeColors[$key + 1] : 'secondary'}}">{{ __('admin.' .
                                  $investor['type']) }}</span></td>
                            </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @endif
                  @endif
                </div>
              </div>
      
            </div>
          </div>
        </div>
      </div>
      <!--/ Ownership Tab -->

      <div class="tab-pane fade show active" id="tab-screening" role="tabpanel" aria-labelledby="tab-screening-trigger">
        <div id="program-screening" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-capitalize">
              <i class="ti ti-search text-primary"></i>{{ __('startup.screening_tab') }}
            </h5>
            <small class="text-muted">{{ __('startup.screening_note') }}</small>
          </div>
          <div class="card-body">
            <div class="row g-4">
              <div class="col-12">
                <div class="border-bottom pb-6 mb-6">
                  <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                      <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-capitalize">
                        <i class="ti ti-circle-check text-info"></i>{{ __('startup.initial_screening') }}
                      </h5>
                      <small class="text-muted">{{ __('startup.committee_evaluation') }}</small>
                    </div>
                      <span class="badge bg-label-info">
                      <i class="ti ti-tag me-1"></i>{{ __('startup.step_with_id', ['id' => 'STP-001']) }}
                      </span>
                  </div>
                  
                  <div class="row g-3 mb-4">
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1"><i class="ti ti-star me-1"></i>{{ __('startup.overall_score') }}</small>
                        <h4 class="mb-0 text-primary fw-bold">{{ $screeningData['initial']['overall_score'] }} <small class="text-muted fw-normal">/ 5</small></h4>
                      </div>
                    </div>
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1"><i class="ti ti-calendar me-1"></i>{{ __('startup.date') }}</small>
                        <p class="mb-0 fw-bold text-dark">{{ $screeningData['initial']['date'] }}</p>
                      </div>
                    </div>
                  </div>
                  <h6 class="mt-4 mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-checklist text-success"></i>{{ __('startup.criteria') }}
                  </h6>
                  @if(count($screeningData['initial']['criteria']) > 0)
                    @if(empty($screeningData['initial']['criteria'][0]['name']))
                      <div class="col-12 text-center text-muted">{{ __('startup.no_criteria') }}</div>
                    @else
                      <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                          <thead class="table-light">
                            <tr>
                              <th class="text-center text-nowrap"><i class="ti ti-ruler-measure me-1"></i>{{ __('startup.criterion') }}</th>
                              <th class="text-center text-nowrap"><i class="ti ti-weight me-1"></i>{{ __('startup.weight') }}</th>
                              <th class="text-end"><i class="ti ti-star me-1"></i>{{ __('startup.score') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($screeningData['initial']['criteria'] as $criterion)
                            <tr class="border-top">
                              <td class="fw-medium">{{ $criterion['name'] }}</td>
                              <td class="text-center"><span class="badge bg-label-primary">{{ $criterion['weight'] }}%</span></td>
                              <td class="text-end"><span class="badge bg-label-success">{{ $criterion['score'] }}</span></td>
                            </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @endif
                  @endif
                  <h6 class="mt-4 mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-user-check text-warning"></i>{{ __('startup.judges_evaluations') }}
                  </h6>
                  @if(count($screeningData['initial']['judges']) > 0)
                    @if(empty($screeningData['initial']['judges'][0]['name']))
                      <div class="col-12 text-center text-muted">{{ __('startup.no_judges') }}</div>
                    @else
                      <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                          <thead class="table-light">
                            <tr>
                              <th class="text-center text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.judge') }}</th>
                              <th class="text-center text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.id') }}</th>
                              <th class="text-center text-nowrap"><i class="ti ti-star me-1"></i>{{ __('startup.score') }}</th>
                              <th class="text-center text-nowrap"><i class="ti ti-message me-1"></i>{{ __('startup.comments') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($screeningData['initial']['judges'] as $judge)
                              <tr class="border-top">
                                <td class="fw-medium">{{ $judge['name'] }}</td>
                                <td>{{ $judge['id'] }}</td>
                                <td class="text-center"><span class="badge bg-label-info">{{ $judge['score'] }}</span></td>
                                <td class="small">{{ $judge['comments'] }}</td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @endif
                  @endif
                </div>
              </div>
              <div class="col-12">
                <div>
                  <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                      <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-capitalize">
                        <i class="ti ti-award text-success"></i>{{ __('startup.final_screening') }}
                      </h5>
                      <small class="text-muted">{{ __('startup.investment_committee_evaluation') }}</small>
                    </div>
                    <span class="badge bg-label-success">
                      <i class="ti ti-tag me-1"></i>{{ __('startup.step_with_id', ['id' => 'STP-010']) }}
                    </span>
                  </div>
                  <div class="row g-3 mb-4">
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1"><i class="ti ti-star me-1"></i>Overall
                          score</small>
                        <h4 class="mb-0 text-success fw-bold">{{ $screeningData['final']['overall_score'] }} <small class="text-muted fw-normal">/ 5</small></h4>
                      </div>
                    </div>
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1"><i
                            class="ti ti-calendar me-1"></i>Date</small>
                        <p class="mb-0 fw-bold text-dark">{{ $screeningData['final']['date'] }}</p>
                      </div>
                    </div>
                  </div>
                  <h6 class="mt-4 mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-checklist text-success"></i>{{ __('startup.criteria') }}
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                      <thead class="table-light">
                        <tr>
                          <th class="text-center text-nowrap"><i class="ti ti-ruler-measure me-1"></i>{{ __('startup.criterion') }}</th>
                          <th class="text-center text-nowrap"><i class="ti ti-weight me-1"></i>{{ __('startup.weight') }}</th>
                          <th class="text-end"><i class="ti ti-star me-1"></i>{{ __('startup.score') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($screeningData['final']['criteria'] as $criterion)
                          <tr class="border-top">
                            <td class="fw-medium">{{ $criterion['name'] }}</td>
                            <td class="text-center"><span class="badge bg-label-success">{{ $criterion['weight'] }}</span></td>
                            <td class="text-end"><span class="badge bg-label-success">{{ $criterion['score'] }}</span></td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  <h6 class="mt-4 mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-user-check text-warning"></i>{{ __('startup.judges_evaluations') }}
                  </h6>
                  @if(count($screeningData['final']['judges']) > 0)
                    @if(empty($screeningData['final']['judges'][0]['name']))
                      <div class="col-12 text-center text-muted">{{ __('startup.no_judges') }}</div>
                    @else
                      <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                          <thead class="table-light">
                            <tr>
                              <th class="text-center text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.judge') }}</th>
                              <th class="text-center text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.id') }}</th>
                              <th class="text-center text-nowrap"><i class="ti ti-star me-1"></i>{{ __('startup.score') }}</th>
                              <th class="text-center text-nowrap"><i class="ti ti-message me-1"></i>{{ __('startup.comments') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($screeningData['final']['judges'] as $judge)
                              <tr class="border-top">
                                <td class="fw-medium">{{ $judge['name'] }}</td>
                                <td>{{ $judge['id'] }}</td>
                                <td class="text-center"><span class="badge bg-label-success">{{ $judge['score'] }}</span></td>
                                <td class="small">{{ $judge['comments'] }}</td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @endif
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Screening Tab -->
      <div class="tab-pane fade show active" id="tab-screening" role="tabpanel" aria-labelledby="tab-screening-trigger">
        <div id="program-screening" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-capitalize">
              <i class="ti ti-search text-primary"></i>{{ __('startup.screening_tab') }}
            </h5>
            <small class="text-muted">{{ __('startup.screening_note') }}</small>
          </div>
      
          <div class="card-body">
            <div class="row g-4">
              @foreach (['initial', 'final'] as $phase)
              @php
              $phaseData = $screeningData[$phase];
              $phaseIcon = $phase === 'initial' ? 'ti-circle-check text-info' : 'ti-award text-success';
              $phaseTitle = $phase === 'initial' ? __('startup.initial_screening') : __('startup.final_screening');
              $phaseSubtitle = $phase === 'initial' ? __('startup.committee_evaluation') :
              __('startup.investment_committee_evaluation');
              $badgeClass = $phase === 'initial' ? 'info' : 'success';
              $scoreClass = $phase === 'initial' ? 'primary' : 'success';
              $criteriaBadgeClass = $phase === 'initial' ? 'primary' : 'success';
              @endphp
      
              <div class="col-12">
                <div class="border-bottom pb-6 mb-6">
                  <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                      <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-capitalize">
                        <i class="ti {{ $phaseIcon }}"></i>{{ $phaseTitle }}
                      </h5>
                      <small class="text-muted">{{ $phaseSubtitle }}</small>
                    </div>
                    <span class="badge bg-label-{{ $badgeClass }}">
                      <i class="ti ti-tag me-1"></i>{{ __('startup.step_with_id', ['id' => $phaseData['step_id']]) }}
                    </span>
                  </div>
      
                  {{-- Score and Date --}}
                  <div class="row g-3 mb-4">
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1">
                          <i class="ti ti-star me-1"></i>{{ __('startup.overall_score') }}
                        </small>
                        <h4 class="mb-0 text-{{ $scoreClass }} fw-bold">{{ $phaseData['overall_score'] }} <small
                            class="text-muted fw-normal">/ 5</small></h4>
                      </div>
                    </div>
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1"><i class="ti ti-calendar me-1"></i>{{
                          __('startup.date') }}</small>
                        <p class="mb-0 fw-bold text-dark">{{ $phaseData['date'] }}</p>
                      </div>
                    </div>
                  </div>
      
                  {{-- Criteria --}}
                  <h6 class="mt-4 mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-checklist text-success"></i>{{ __('startup.criteria') }}
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                      <thead class="table-light">
                        <tr>
                          <th class="text-center text-nowrap"><i class="ti ti-ruler-measure me-1"></i>{{
                            __('startup.criterion') }}</th>
                          <th class="text-center text-nowrap"><i class="ti ti-weight me-1"></i>{{ __('startup.weight') }}</th>
                          <th class="text-end"><i class="ti ti-star me-1"></i>{{ __('startup.score') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($phaseData['criteria'] as $c)
                        <tr class="border-top">
                          <td class="fw-medium">{{ $c['name'] }}</td>
                          <td class="text-center"><span class="badge bg-label-{{ $criteriaBadgeClass }}">{{ $c['weight']
                              }}</span></td>
                          <td class="text-end"><span class="badge bg-label-success">{{ $c['score'] }}</span></td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
      
                  {{-- Judges --}}
                  <h6 class="mt-4 mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-user-check text-warning"></i>{{ __('startup.judges_evaluations') }}
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                      <thead class="table-light">
                        <tr>
                          <th class="text-center text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.judge') }}</th>
                          <th class="text-center text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.id') }}</th>
                          <th class="text-center text-nowrap"><i class="ti ti-star me-1"></i>{{ __('startup.score') }}</th>
                          <th class="text-center text-nowrap"><i class="ti ti-message me-1"></i>{{ __('startup.comments') }}
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($phaseData['judges'] as $j)
                        <tr class="border-top">
                          <td class="fw-medium">{{ $j['name'] }}</td>
                          <td>{{ $j['id'] }}</td>
                          <td class="text-center"><span class="badge bg-label-{{ $badgeClass }}">{{ $j['score'] }}</span></td>
                          <td class="small">{{ $j['comments'] }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      <!--/ Screening Tab -->

      <!-- Mentorship Tab -->
      <div class="tab-pane fade show active" id="tab-mentorship" role="tabpanel" aria-labelledby="tab-mentorship-trigger">
        <div id="program-mentorship" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-capitalize">
              <i class="ti ti-message-circle text-success"></i>{{ __('startup.mentorship_tab') }}
            </h5>
            <small class="text-muted">{{ __('startup.mentor_interactions_note') }}</small>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th class="text-center text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.mentor_name') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.mentor_id') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-flag me-1"></i>{{ __('startup.step_name') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.step_id') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-calendar me-1"></i>{{ __('startup.date') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-clock me-1"></i>{{ __('startup.duration_hours') }}
                    </th>
                    <th class="text-center text-nowrap"><i class="ti ti-user-check me-1"></i>{{ __('startup.founder_attended')
                      }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-star me-1"></i>{{ __('startup.rating') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-message me-1"></i>{{ __('startup.comments') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($mentorshipData as $key => $session)
                  <tr class="border-top">
                    <td class="fw-medium">{{ $session['mentor'] }}</td>
                    <td><span class="badge bg-label-{{ isset($badgeColors[$key + 1]) ? $badgeColors[$key + 1] : 'secondary' }}">{{ $session['id'] }}</span></td>
                    <td>{{ $session['step_name'] }}</td>
                    <td>{{ $session['step_id'] }}</td>
                    <td>{{ $session['date'] }}</td>
                    <td class="text-center"><span class="badge bg-label-{{ isset($badgeColors[$key + 1]) ? $badgeColors[$key + 1] : 'secondary' }}">{{
                        $session['duration'] }}</span></td>
                    <td class="fw-medium">{{ $session['founder_attended'] }}</td>
                    <td class="text-center"><span class="badge bg-label-{{ isset($badgeColors[$key + 1]) ? $badgeColors[$key + 1] : 'secondary' }}">{{
                        $session['rating'] }}</span></td>
                    <td class="small">{{ $session['comments'] }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!--/ Mentorship Tab -->

      <!-- Milestones Tab -->
      <div class="tab-pane fade show active" id="tab-milestones" role="tabpanel" aria-labelledby="tab-milestones-trigger">
        <div id="program-milestones" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-capitalize">
              <i class="ti ti-flag-check text-warning"></i>{{ __('startup.milestones_and_deliverables') }}
            </h5>
            <small class="text-muted">{{ __('startup.planned_progress_note') }}</small>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th class="text-center text-nowrap"><i class="ti ti-flag me-1"></i>{{ __('startup.milestone_title_label')
                      }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.description') }}
                    </th>
                    <th class="text-center text-nowrap"><i class="ti ti-calendar-due me-1"></i>{{ __('startup.due_date') }}
                    </th>
                    <th class="text-center text-nowrap"><i class="ti ti-progress me-1"></i>{{ __('startup.completion') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-paperclip me-1"></i>{{ __('startup.deliverables') }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($milestones as $m)
                  <tr class="border-top">
                    <td class="fw-medium">{{ $m['title'] }}</td>
                    <td class="small">{{ $m['description'] }}</td>
                    <td><span class="badge bg-label-info">{{ $m['due_date'] }}</span></td>
                    <td class="w-25">
                      <div class="d-flex align-items-center gap-2">
                        <span class="fw-semibold">{{ $m['completion'] }}%</span>
                        <div class="progress grow" style="height: 6px">
                          <div class="progress-bar bg-success" role="progressbar" style="width: {{ $m['completion'] }}%">
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <ul class="list-unstyled mb-0 small">
                        @if(!empty($m['deliverables']))
                        @foreach ($m['deliverables'] as $d)
                        <li>
                          <a href="{{ $d['link'] }}" class="fw-medium text-primary">
                            <i class="ti {{ $deliverableIcons[$d['type']] }} me-1"></i>{{ $d['name'] }}
                          </a>
                        </li>
                        @endforeach
                        @else
                        <li class="text-muted">{{ __('startup.no_deliverables') }}</li>
                        @endif
                      </ul>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!--/ Milestones Tab -->

      <!-- Tasks Tab -->
      <div class="tab-pane fade show active" id="tab-tasks" role="tabpanel" aria-labelledby="tab-tasks-trigger">
        <div id="program-tasks" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-capitalize">
              <i class="ti ti-checklist text-primary"></i>{{ __('startup.program_tasks') }}
            </h5>
            <small class="text-muted">{{ __('startup.tasks_assigned_note') }}</small>
          </div>
          <div class="card-body">
            @if($tasks->count() == 0)
              @if(empty($tasks[0]['title']))
                <div class="col-12 text-center text-muted">{{ __('startup.no_tasks') }}</div>
              @else
                <div class="table-responsive">
                  <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                      <tr>
                        <th class="text-center text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.task_title') }}
                        </th>
                        <th class="text-center text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.task_id') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.session_id') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.session_title') }}
                        </th>
                        <th class="text-center text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.description') }}
                        </th>
                        <th class="text-center text-nowrap"><i class="ti ti-calendar-due me-1"></i>{{ __('startup.due_date_label')
                          }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-calendar-check me-1"></i>{{
                          __('startup.completion_date_label') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.mentor') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-user-circle me-1"></i>{{ __('startup.created_by') }}
                        </th>
                        <th class="text-center text-nowrap"><i class="ti ti-badge me-1"></i>{{ __('startup.status') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($tasks as $key => $task)
                      <tr class="border-top">
                        <td class="fw-medium">{{ $task['title'] }}</td>
                        <td><span class="badge bg-label-{{isset($badgeColors[$key]) ? $badgeColors[$key] : 'secondary' }}">{{ $task['task_id'] }}</span></td>
                        <td>{{ $task['session_id'] }}</td>
                        <td>{{ $task['session_title'] }}</td>
                        <td class="small">{{ $task['description'] }}</td>
                        <td>{{ $task['due_date'] }}</td>
                        <td>{{ $task['completion_date'] }}</td>
                        <td>{{ $task['mentor'] }}</td>
                        <td>{{ $task['created_by'] }}</td>
                        <td class="text-center">
                          <span class="badge bg-label-{{ isset($badgeColors[$key]) ? $badgeColors[$key] : 'secondary' }}">
                            {{ $task['status'] }}
                          </span>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            @endif
          </div>
        </div>
      </div>
      <!--/ Tasks Tab -->

      <!-- Quizzes Tab -->
      <div class="tab-pane fade show active" id="tab-quizzes" role="tabpanel" aria-labelledby="tab-quizzes-trigger">
        <div id="program-quizzes" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-capitalize">
              <i class="ti ti-help text-info"></i>{{ __('startup.quizzes_tab') }}
            </h5>
            <small class="text-muted">{{ __('startup.knowledge_checks_note') }}</small>
          </div>
          <div class="card-body">
            @if($quizzes->count() == 0)
              @if(empty($quizzes[0]['title']))
                <div class="col-12 text-center text-muted">{{ __('startup.no_quizzes') }}</div>
              @else
                <div class="table-responsive">
                  <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                      <tr>
                        <th class="text-center text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.quiz_id') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.quiz_title') }}
                        </th>
                        <th class="text-center text-nowrap"><i class="ti ti-calendar me-1"></i>{{ __('startup.date_completed') }}
                        </th>
                        <th class="text-center text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.founder') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-percent me-1"></i>{{ __('startup.score') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-badge me-1"></i>{{ __('startup.status') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($quizzes as $key => $q)
                      <tr class="border-top">
                        <td><span class="badge bg-label-{{ isset($badgeColors[$key + 1]) ? $badgeColors[$key + 1] : 'secondary' }}">{{ $q['quiz_id'] }}</span></td>
                        <td class="fw-medium">{{ $q['title'] }}</td>
                        <td>{{ $q['date_completed'] }}</td>
                        <td>{{ $q['founder'] }}</td>
                        <td class="text-center"><span class="badge bg-label-{{ $q['score_badge'] }}">{{ $q['score'] }}</span></td>
                        <td class="text-center">
                          <span class="badge bg-label-{{ isset($badgeColors[$key]) ? $badgeColors[$key] : 'secondary' }}">
                            {{ $q['status'] }}
                          </span>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            @endif
          </div>
        </div>
      </div>
      <!--/ Quizzes Tab -->

      <!-- Surveys Tab -->
      <div class="tab-pane fade show active" id="tab-surveys" role="tabpanel" aria-labelledby="tab-surveys-trigger">
        <div id="program-surveys" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-capitalize">
              <i class="ti ti-mood-smile text-warning"></i>{{ __('startup.surveys_tab') }}
            </h5>
            <small class="text-muted">{{ __('startup.feedback_note') }}</small>
          </div>
          <div class="card-body">
            @if($surveys->count() == 0)
              @if(empty($surveys[0]['survey_id']))
                <div class="col-12 text-center text-muted">{{ __('startup.no_surveys') }}</div>
              @else
                <div class="table-responsive">
                  <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                      <tr>
                        <th class="text-center text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.survey_id') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.survey_title') }}
                        </th>
                        <th class="text-center text-nowrap"><i class="ti ti-flag me-1"></i>{{ __('startup.program_step') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-calendar me-1"></i>{{ __('startup.date_completed') }}
                        </th>
                        <th class="text-center text-nowrap"><i class="ti ti-file me-1"></i>{{ __('startup.answers') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($surveys as $key => $s)
                      <tr class="border-top">
                        <td><span class="badge bg-label-{{ isset($badgeColors[$key]) ? $badgeColors[$key] : 'secondary' }}">{{ $s['survey_id'] }}</span></td>
                        <td class="fw-medium">{{ $s['title'] }}</td>
                        <td>{{ $s['program_step'] }}</td>
                        <td>{{ $s['date_completed'] }}</td>
                        <td class="text-center">
                          @php
                            $surveyId = str_replace('SV-', '', $s['survey_id']);    
                          @endphp
                          <a href="/dashboard/surveys/start/{{ $surveyId }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-eye me-1"></i>{{ __('admin.View') }}
                          </a>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            @endif
          </div>
        </div>
      </div>
      <!--/ Surveys Tab -->

      <!-- Services Tab -->
      <div class="tab-pane fade show active" id="tab-services" role="tabpanel" aria-labelledby="tab-services-trigger">
        <div id="program-services" class="card mb-6 shadow-sm">
          <div class="card-header">
            <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-capitalize">
              <i class="ti ti-gift text-success"></i>{{ __('startup.services_provided') }}
            </h5>
            <small class="text-muted">{{ __('startup.perks_note') }}</small>
          </div>
          <div class="card-body">
            @if($services->count() == 0)
              @if(empty($services[0]['name']))
                <div class="col-12 text-center text-muted">{{ __('startup.no_services') }}</div>
              @else
                <div class="table-responsive">
                  <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                      <tr>
                        <th class="text-center text-nowrap"><i class="ti ti-gift me-1"></i>{{ __('startup.service_name') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.service_id') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.description') }}
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($services as $key => $s)
                      <tr class="border-top">
                        <td class="fw-medium">{{ $s['name'] }}</td>
                        <td><span class="badge bg-label-{{ isset($badgeColors[$key + 1]) ? $badgeColors[$key + 1] : 'secondary' }}">{{ $s['service_id'] }}</span></td>
                        <td class="small">{{ $s['description'] }}</td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            @endif
          </div>
        </div>
      </div>
      <!--/ Services Tab -->

      <!-- Forms Tab -->
      <div class="tab-pane fade show active" id="tab-forms" role="tabpanel" aria-labelledby="tab-forms-trigger">
        <div id="program-forms" class="card mb-6 shadow-sm">
          <div class="card-header d-flex align-items-center justify-content-between">
            <div>
              <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-capitalize">
                <i class="ti ti-file-text text-success"></i>{{ __('startup.submitted_forms') }}
              </h5>
              <small class="text-muted">{{ __('startup.custom_forms_note') }}</small>
            </div>
          </div>
          <div class="card-body">
            @if($forms->count() == 0)
              @if(empty($forms[0]['title']))
                <div class="col-12 text-center text-muted">{{ __('startup.no_forms') }}</div>
              @else
                <div class="table-responsive">
                  <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                      <tr>
                        <th class="text-center text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.form_title') }}
                        </th>
                        <th class="text-center text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.form_id') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-flag me-1"></i>{{ __('startup.custom_form_step') }}
                        </th>
                        <th class="text-center text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.step_id') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-calendar me-1"></i>{{ __('startup.submission_date') }}
                        </th>
                        <th class="text-center text-nowrap"><i class="ti ti-file me-1"></i>{{ __('startup.details') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($forms as $key => $f)
                      <tr class="border-top">
                        <td class="fw-medium">{{ $f['title'] }}</td>
                        <td><span class="badge bg-label-{{ isset($badgeColors[$key]) ? $badgeColors[$key] : 'secondary' }}">{{ $f['form_id'] }}</span></td>
                        <td>{{ $f['step_name'] }}</td>
                        <td>{{ $f['step_id'] }}</td>
                        <td>{{ $f['submission_date'] }}</td>
                        <td class="text-center">
                          <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#formDetailsModal-{{ $f['modal_id'] }}">
                            <i class="ti ti-eye me-1"></i>{{ __('startup.view_inputs') }}
                          </button>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            @endif
          </div>
        </div>
      </div>
      <!--/ Forms Tab -->
    </div>

    <div class="alert alert-info d-flex align-items-center">
      <i class="ti ti-info-circle me-2"></i>
      {{ __('startup.snapshot_note') }}
    </div>
  </div>
</div>

@foreach ($forms as $f)
<!-- Form Details Modal -->
<div class="modal fade" id="formDetailsModal-{{ $f['modal_id'] }}" tabindex="-1" aria-labelledby="formDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formDetailsModalLabel">
          {{ __('startup.form_inputs_for', ['form' => $f['title']]) }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th class="text-center text-nowrap"><i class="ti ti-field me-1"></i>{{ __('startup.field_name') }}</th>
                <th class="text-center text-nowrap"><i class="ti ti-database me-1"></i>{{ __('startup.field_type') }}</th>
                <th class="text-center text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.submitted_value') }}</th>
                <th class="text-center text-nowrap"><i class="ti ti-paperclip me-1"></i>{{ __('startup.attachment') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($f['fields'] as $field)
              <tr>
                <td class="fw-medium">{{ $field['name'] }}</td>
                <td>
                  <span class="badge bg-label-{{ $field['type'] === 'text' ? 'info' : ($field['type'] === 'file' ? 'warning' : 'secondary') }}">
                    {{ __('startup.' . $field['type']) }}
                  </span>
                </td>
                <td class="small">
                  @if ($field['type'] === 'file')
                    <span class="badge bg-label-success">{{ __('startup.uploaded') }}</span>
                  @else
                    {{ $field['value'] }}
                  @endif
                </td>
                <td class="text-center">
                  @if ($field['attachment'])
                    <a href="{{ $field['attachment'] }}" class="btn btn-sm btn-link text-primary p-0" title="Download">
                      <i class="ti ti-download"></i>
                    </a>
                  @else
                    &mdash;
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
          <i class="ti ti-x me-1"></i>{{ __('admin.close') }}
        </button>
      </div>
    </div>
  </div>
</div>
@endforeach
@stop

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
@stop

@section('pagescripts')
<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Initialize DataTable
    DataTableInit(".table");
  });

  // Export PDF
  const exportPdfButton = document.getElementById("export-pdf-button");
  exportPdfButton.addEventListener("click", exportPDF);

  async function exportPDF() {
    const element = document.getElementById("contentToExport");
    const button = document.getElementById("export-pdf-button");


    button.innerHTML = "{{ __('startup.generating_pdf') }}";
    button.disabled = true; // optional

    try {
      const canvas = await html2canvas(element, {
        scale: 2,
        useCORS: true,
        ignoreElements: (el) => {
          return el.id === "navigation-pills" || el.id === "header-actions";
        },
        windowWidth: element.scrollWidth,
        windowHeight: element.scrollHeight,
      });

      const imgData = canvas.toDataURL("image/png");

      const pxToMm = px => px * 0.264583;
      const contentWidthMm = pxToMm(canvas.width);
      const contentHeightMm = pxToMm(canvas.height);

      const pdf = new jspdf.jsPDF({
        orientation: "p",
        unit: "mm",
        format: [contentWidthMm, contentHeightMm],
      });

      pdf.addImage(imgData, "PNG", 0, 0, contentWidthMm, contentHeightMm);
      pdf.save("startup-alumni-report.pdf");

    } catch (err) {
      console.error("PDF export failed:", err);
    }

    // Restore button text
    button.innerHTML = `<i class="ti ti-file-type-pdf me-1"></i>{{ __('startup.export_pdf') }}`;
    button.disabled = false; // optional
  }

  function DataTableInit(selector, options = {}) {
    // Detect RTL direction
    const isRTL = document.documentElement.dir === "rtl";

    // Default global options
    const defaultOptions = {
        responsive: true,
        processing: true,
        serverSide: false,
        pageLength: 10,
        language: {
            url: isRTL
                ? "https://cdn.datatables.net/plug-ins/1.13.6/i18n/ar.json"
                : "https://cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json",
        },
        // RTL table layout fix
        initComplete: function () {
            if (isRTL) {
                $(selector).addClass("datatable-rtl");
            }
        },
    };

    // Merge user options
    const finalOptions = { ...defaultOptions, ...options };

    // Initialize
    return $(selector).DataTable(finalOptions);
  }
</script>
@stop
