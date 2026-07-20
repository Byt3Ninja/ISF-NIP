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
  'screening_score'        => $screeningScore,
  'final_screening_score'  => $finalScreeningScore,
  'training_hours'         => $trainingHours,
  'mentorship_hours'       => $mentorshipHours,
  'attendance_rate'        => $attendanceRate,
  'milestones_completed'   => ['current' => $milestonesCompleted, 'total' => $milestonesCollection->count()],
  'quizzes_completed'      => ['current' => $quizzesPassed,       'total' => $quizzesCollection->count()],
  'avg_quiz_score'         => $avgQuizScore,
  'in_kind_services'       => $inKindServices,
  'surveys_completed'      => ['current' => $surveysCompleted,    'total' => $surveysCollection->count()],
  'tasks_completed'        => ['current' => $tasksCompleted,      'total' => $tasksCollection->count()],
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

// $screeningData is passed from the controller (built from alumni snapshot)

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
  'png' => 'ti-image',
  'pdf' => 'ti-file-pdf',
  'video' => 'ti-video',
  'docx' => 'ti-file-word',
  'xlsx' => 'ti-file-spreadsheet',
  'pptx' => 'ti-file-present',
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
        <a id="export-csv" class="btn btn-outline-secondary btn-sm" style="cursor:pointer;">
          <i class="ti ti-file-type-csv me-1"></i>{{ __('startup.export_csv') }}
        </a>
      </div>
    </div>

    @php
      $startupInfo = [
        ['icon' => 'calendar-event', 'color' => 'info', 'label' => __('startup.complition_date'), 'value' => $startup['established_at']],
        ['icon' => 'briefcase', 'color' => 'warning', 'label' => __('startup.business_type'), 'value' => $startup['business_type']],
        ['icon' => 'device-gamepad-3', 'color' => 'success', 'label' => __('startup.industry'), 'value' => $startup['industry']],
        ['icon' => 'packages', 'color' => 'danger', 'label' => __('startup.sector'), 'value' => $startup['sector']],
      //  ['icon' => 'users', 'color' => 'primary', 'label' => __('startup.team_size'), 'value' => $startup['team_size']],
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
                    <span class="text-secondary small">{{ $startup['industry']  ? $startup['industry'] : '---' }}</span>
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
                @if (!empty($startup['links'][$social['key']]))
                  <a href="{{ $startup['links'][$social['key']] }}"
                    target="_blank"
                    rel="noopener noreferrer"
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
       // 'screening_score' => [
       //     'format' => 'number',
       //     'label'  => __('startup.screening_score'),
       //     'icon'   => 'search',
       //     'color'  => 'primary',
       // ],
      //'final_screening_score' => [
      //      'format' => 'number',
      //      'label'  => __('startup.final_screening_score'),
      //      'icon'   => 'award',
      //      'color'  => 'success',
      //  ],
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
        //[
        //  'id' => 'screening',
        //  'icon' => 'search',
        //  'label' => __('startup.screening_tab'),
        //],
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
                    <i class="ti ti-calendar me-1"></i>{{ __('startup.completion_date') }}
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
              <div class="col-12">
                <div class="w-75">
                  @if(empty($startup['sdgs']))
                    <span class="badge p-3 bg-light-subtle text-dark border">{{ __('startup.no_sdg') }}</span>
                  @else
                    <div class="d-flex flex-wrap align-items-center gap-4">
                      @foreach($startup['sdgs'] as $sdg)
                        <span class="badge p-3 bg-light-subtle text-dark border">{{ __('admin.' . $sdg) }}</span>
                      @endforeach
                    </div>
                  @endif
                </div>
              </div>
              
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
            {{-- <span class="badge bg-label-info">
              <i class="ti ti-users me-1"></i>{{ __('startup.team_size_badge', ['count' => count($teamData['employees']) +
              count($teamData['founders']) + 1]) }}
            </span> --}}
          </div>
      
          <div class="card-body">
            <div class="row g-6">
              {{-- Leader --}}
              {{--  
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
              --}}
      
              {{-- Founders --}}
              <div class="col-12 col-lg-12">
                <div class="card card-body border h-100 shadow-sm">
                  <h6 class="mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-users text-info"></i>{{ __('startup.founders') }}
                  </h6>
                  @if(empty($teamData['founders'][0]['name']))
                    <div class="col-12 text-center text-muted">{{ __('startup.no_founders') }}</div>
                  @else
                  <div class="table-responsive">
                      <table class="table table-sm mb-0">
                        <thead class="table-light">
                          <tr>
                            <th class="text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.name') }}</th>
                            <th class="text-nowrap"><i class="ti ti-mail me-1"></i>{{ __('startup.email') }}</th>
                            <th class="text-end"><i class="ti ti-link me-1"></i>{{ __('startup.linkedin') }}</th>
                          </tr>
                        </thead>
                        <tbody>
                            @foreach ($teamData['founders'] as $founder)
                            <tr class="border-top">
                              <td class="fw-medium">{{ $founder['name'] }}</td>
                              <td>{{ $founder['email'] }}</td>
                              <td class="text-end">
                                @if(!empty($founder['linkedin']))
                                  <a href="{{ $founder['linkedin'] }}" target="_blank" rel="noopener noreferrer" class="fw-medium text-primary">
                                    {{ __('startup.linkedin_view_profile') }}
                                  </a>
                                @else
                                  <span class="text-muted">—</span>
                                @endif
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
                @php
                  $validEmployees = collect($teamData['employees'])->filter(fn($e) => !empty($e['name']));
                  $maleCount      = $validEmployees->filter(fn($e) => strtolower($e['gender'] ?? '') === 'male')->count();
                  $femaleCount    = $validEmployees->filter(fn($e) => strtolower($e['gender'] ?? '') === 'female')->count();
                @endphp
                <div class="d-flex gap-2">
                  <span class="badge bg-label-secondary"><i class="ti ti-users me-1"></i>{{ __('startup.total') }}: {{ $validEmployees->count() }}</span>
                  <span class="badge bg-label-info"><i class="ti ti-gender-male me-1"></i>{{ __('startup.male') }}: {{ $maleCount }}</span>
                  <span class="badge bg-label-warning"><i class="ti ti-gender-female me-1"></i>{{ __('startup.female') }}: {{ $femaleCount }}</span>
                </div>
              </div>
              @if(count($teamData['employees']) > 0)
                 
                @if(empty($teamData['employees'][0]['name']))
                  <div class="col-12 text-center text-muted">{{ __('startup.no_employees') }}</div>
                @else
                  <div class="table-responsive">
                    <table class="table table-sm mb-0">
                      <thead class="table-light">
                        <tr>
                          <th class="text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.full_name') }}</th>
                          <th class="text-nowrap"><i class="ti ti-briefcase me-1"></i>{{ __('startup.job_title') }}</th>
                          <th class="text-nowrap"><i class="ti ti-gender-male me-1"></i>{{ __('startup.gender') }}</th>
                          <th class="text-nowrap"><i class="ti ti-clock me-1"></i>{{ __('startup.employment_type') }}</th>
                          <th class="text-nowrap"><i class="ti ti-calendar me-1"></i>{{ __('startup.start_date_label') }}</th>
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
                        <table class="table table-sm mb-0">
                          <thead class="table-light">
                            <tr>
                              <th class="text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.name') }}</th>
                              <th class="text-nowrap"><i class="ti ti-briefcase me-1"></i>{{ __('startup.role') }}</th>
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
                        <table class="table table-sm mb-0">
                          <thead class="table-light">
                            <tr>
                              <th class="text-nowrap"><i class="ti ti-building me-1"></i>{{ __('startup.name') }}</th>
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

      <!-- Screening Tab -->
      {{-- <div class="tab-pane fade show active" id="tab-screening" role="tabpanel" aria-labelledby="tab-screening-trigger"> --}}
        {{--  <div id="program-screening" class="card mb-6 shadow-sm">
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
              $phaseSubtitle = $phase === 'initial' ? __('startup.committee_evaluation') : __('startup.investment_committee_evaluation');
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
                      <i class="ti ti-tag me-1"></i>{{ $phaseData['step_name'] ?? $phaseData['step_id'] }}
                    </span>
                  </div>
      
                  <!-- Score and Date -->
                  <div class="row g-3 mb-4">
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1">
                          <i class="ti ti-star me-1"></i>{{ __('startup.overall_score') }}
                        </small>
                        <h4 class="mb-0 text-{{ $scoreClass }} fw-bold">{{ $phaseData['overall_score'] }}</h4>
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
      
                  <!-- Criteria -->
                  <h6 class="mt-4 mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-checklist text-success"></i>{{ __('startup.criteria') }}
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0">
                      <thead class="table-light">
                        <tr>
                          <th class="text-nowrap"><i class="ti ti-ruler-measure me-1"></i>{{__('startup.criterion') }}</th>
                          <th class="text-nowrap"><i class="ti ti-weight me-1"></i>{{ __('startup.weight') }}</th>
                          <th class="text-end"><i class="ti ti-star me-1"></i>{{ __('startup.score') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($phaseData['criteria'] as $c)
                        <tr class="border-top">
                          <td class="fw-medium">{{ $c['name'] }}</td>
                          <td>
                            <span class="badge bg-label-{{ $criteriaBadgeClass }}">{{ $c['weight'] }}</span>
                          </td>
                          <td class="text-end"><span class="badge bg-label-success">{{ $c['score'] }}</span></td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
      
                  <!-- Judges -->
                  <h6 class="mt-4 mb-3 fw-bold d-flex align-items-center gap-2">
                    <i class="ti ti-user-check text-warning"></i>{{ __('startup.judges_evaluations') }}
                  </h6>
                  @if(count($phaseData['judges']) > 0)
                  <div class="table-responsive">
                    <table class="table table-sm mb-0">
                      <thead class="table-light">
                        <tr>
                          <th class="text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.judge') }}</th>
                          <th class="text-nowrap"><i class="ti ti-star me-1"></i>{{ __('startup.score') }}</th>
                          <th class="text-nowrap"><i class="ti ti-message me-1"></i>{{ __('startup.comments') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($phaseData['judges'] as $j)
                        <tr class="border-top">
                          <td class="fw-medium">
                            @if(!empty($j['link']))
                              <a href="{{ $j['link'] }}" target="_blank" class="text-primary fw-medium">{{ $j['name'] }}</a>
                            @else
                              {{ $j['name'] }}
                            @endif
                          </td>
                          <td><span class="badge bg-label-{{ $badgeClass }}">{{ $j['score'] }}</span></td>
                          <td class="small">{{ $j['comments'] ?: '—' }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                  @else
                    <p class="text-muted small">{{ __('startup.no_judges') }}</p>
                  @endif
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>  --}}
        {{-- <div id="custom-program-screening" class="card mb-6 shadow-sm">
          <div class="card-body">
            <div class="row g-4">
              <div class="col-12">
                <div class="pb-6 mb-6">
                  <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                      <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-capitalize">
                        <i class="ti ti-circle-check text-info"></i>{{ __('startup.initial_screening') }}
                      </h5>
                      <small class="text-muted">{{ __('startup.committee_evaluation') }}</small>
                    </div>
                    <span class="badge bg-label-info">
                      <i class="ti ti-tag me-1"></i>{{ $screeningData['initial']['step_name'] ?? $screeningData['initial']['step_id'] }}
                    </span>
                  </div>

                  <div class="row g-3 mb-4">
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1">
                          <i class="ti ti-star me-1"></i>{{ __('startup.overall_score') }}
                        </small>
                        <h4 class="mb-0 text-primary fw-bold">{{ $screeningResultsData['total'] * 100 }}%</h4>
                      </div>
                    </div>
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1"><i class="ti ti-calendar me-1"></i>{{ __('startup.date') }}</small>
                        <p class="mb-0 fw-bold text-dark">{{ $screeningData['initial']['date'] }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="row g-3 mb-4">
                    <div class="col-12">
                      <div id="screeningEvaluationBody">
                        <!-- Content will be loaded dynamically -->
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div id="custom-program-final-screening" class="card mb-6 shadow-sm">
          <div class="card-body">
            <div class="row g-4">
              <div class="col-12">
                <div class="pb-6 mb-6">
                  <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                    <div>
                      <h5 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-capitalize">
                        <i class="ti ti-award text-success"></i>{{ __('startup.final_screening') }}
                      </h5>
                      <small class="text-muted">{{ __('startup.investment_committee_evaluation') }}</small>
                    </div>
                    <span class="badge bg-label-info">
                      <i class="ti ti-tag me-1"></i>{{ $screeningData['final']['step_name'] ?? $screeningData['final']['step_id'] }}
                    </span>
                  </div>

                  <div class="row g-3 mb-4">
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1">
                          <i class="ti ti-star me-1"></i>{{ __('startup.overall_score') }}
                        </small>
                        <h4 class="mb-0 text-success fw-bold">{{ $finalScreeningResultsData['total'] * 100 }}%</h4>
                      </div>
                    </div>
                    <div class="col">
                      <div class="p-3 rounded bg-light-subtle border h-100">
                        <small class="text-muted d-block fw-semibold mb-1"><i class="ti ti-calendar me-1"></i>{{ __('startup.date') }}</small>
                        <p class="mb-0 fw-bold text-dark">{{ $screeningData['final']['date'] }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="row g-3 mb-4">
                    <div class="col-12">
                      <div id="finalScreeningEvaluationBody">
                        <!-- Content will be loaded dynamically -->
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div> --}}
      {{-- </div> --}}

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
              <table class="table table-sm mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="text-center text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.mentor_name') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-calendar me-1"></i>{{ __('startup.date') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-clock me-1"></i>{{ __('startup.duration_hours') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-flag me-1"></i>{{ __('startup.step_name') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.founder_attended') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-user-check me-1"></i>{{ __('startup.attendance') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-star me-1"></i>{{ __('startup.rating') }}</th>
                    <th class="text-center text-nowrap"><i class="ti ti-message me-1"></i>{{ __('startup.comments') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($mentorshipData) > 0 && !empty($mentorshipData[0]['mentor']))
                    @foreach ($mentorshipData as $key => $session)
                    <tr class="border-top">
                      <td class="fw-medium">{{ $session['mentor'] }}</td>
                      <td>{{ $session['date'] }}</td>
                      <td class="text-center"><span class="badge bg-label-secondary">{{ $session['duration'] }}</span></td>
                      <td>{{ $session['step_name'] }}</td>
                      <td class="fw-medium">{{ $session['founder_attended'] }}</td>
                      <td class="text-center"><span class="badge bg-label-{{ $session['attended_badge'] }}">{{ $session['attended'] }}</span></td>
                      <td class="text-center"><span class="badge bg-label-secondary">{{ $session['rating'] }}</span></td>
                      <td class="small">{{ $session['comments'] }}</td>
                    </tr>
                    @endforeach
                  @else
                    <tr>
                      <td colspan="9" class="text-center text-muted py-3">{{ __('admin.no_mentorship_sessions') }}</td>
                    </tr>
                  @endif
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
            
            @if (count($milestones) > 0)
              <div class="table-responsive">
                <table class="table table-sm mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="text-nowrap"><i class="ti ti-flag me-1"></i>{{ __('startup.milestone_title_label') }}</th>
                      <th class="text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.description') }}</th>
                      <th class="text-nowrap"><i class="ti ti-calendar-due me-1"></i>{{ __('startup.due_date') }}</th>
                      <th class="text-nowrap"><i class="ti ti-progress me-1"></i>{{ __('startup.completion') }}</th>
                      <th class="text-nowrap"><i class="ti ti-paperclip me-1"></i>{{ __('startup.deliverables') }}</th>
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
            @else
              <div class="col-12 text-center text-muted">{{ __('admin.no_milestones') }}</div>
            @endif
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
            @if(count($tasks) > 0)
              @if(empty($tasks[0]['title']))
                <div class="col-12 text-center text-muted">{{ __('startup.no_tasks') }}</div>
              @else
                <div class="table-responsive">
                  <table class="table table-sm mb-0">
                    <thead class="table-light">
                      <tr>
                        <th class="text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.task_title') }}</th>
                        <th class="text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.task_id') }}</th>
                        <th class="text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.description') }}</th>
                        <th class="text-nowrap"><i class="ti ti-calendar-due me-1"></i>{{ __('startup.due_date_label') }}</th>
                        <th class="text-nowrap"><i class="ti ti-calendar-check me-1"></i>{{ __('startup.completion_date_label') }}</th>
                        <th class="text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.mentor') }}</th>
                        <th class="text-nowrap"><i class="ti ti-user-circle me-1"></i>{{ __('startup.created_by') }}</th>
                        <th class="text-nowrap"><i class="ti ti-badge me-1"></i>{{ __('startup.status') }}</th>
                        <th class="text-nowrap text-center"><i class="ti ti-history me-1"></i>{{ __('admin.feedback_history') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($tasks as $key => $task)
                      <tr class="border-top">
                        <td class="fw-medium">{{ $task['title'] }}</td>
                        <td><span class="badge bg-label-{{isset($badgeColors[$key]) ? $badgeColors[$key] : 'secondary' }}">{{ $task['task_id'] }}</span></td>
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
                        <td class="text-center">
                          <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle" data-bs-toggle="modal" data-bs-target="#feedbackHistoryModal-{{ $task['task_id'] }}">
                            <i class="ti ti-history"></i>
                          </button>

                          <!-- Feedback History Modal -->
                          <div class="modal fade text-start" id="feedbackHistoryModal-{{ $task['task_id'] }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <h5 class="modal-title d-flex align-items-center gap-2">
                                    <i class="ti ti-history text-primary"></i>
                                    {{ __('admin.feedback_history') }} - {{ $task['title'] }}
                                  </h5>
                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-start">
                                  @if(!empty($task['feedbacks']) && count($task['feedbacks']) > 0)
                                    <div class="table-responsive">
                                      <table class="table table-striped table-hover table-sm">
                                        <thead>
                                          <tr>
                                            <th style="width: 25%">{{ __('startup.date') }}</th>
                                            <th style="width: 20%">{{ __('startup.status') }}</th>
                                            <th>{{ __('startup.notes') }}</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                          @foreach($task['feedbacks'] as $fb)
                                            <tr>
                                              <td>{{ \Carbon\Carbon::parse($fb['created_at'])->format('Y-m-d H:i') }}</td>
                                              <td>
                                                @if($fb['decision'] === 'pass')
                                                  <span class="badge bg-label-success">{{ __('startup.passed') }}</span>
                                                @else
                                                  <span class="badge bg-label-warning">{{ __('admin.needs_revision') }}</span>
                                                @endif
                                              </td>
                                              <td class="text-wrap">{{ $fb['note'] }}</td>
                                            </tr>
                                          @endforeach
                                        </tbody>
                                      </table>
                                    </div>
                                  @else
                                    <div class="text-center py-4 text-muted">
                                      <i class="ti ti-message-2-off ti-lg d-block mb-2"></i>
                                      {{ __('startup.no_feedback_provided_yet') }}
                                    </div>
                                  @endif
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('startup.close') }}</button>
                                </div>
                              </div>
                            </div>
                          </div>
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
            @if(count($quizzes) > 0)
              @if(empty($quizzes[0]['title']))
                <div class="col-12 text-center text-muted">{{ __('startup.no_quizzes') }}</div>
              @else
                <div class="table-responsive">
                  <table class="table table-sm mb-0">
                    <thead class="table-light">
                      <tr>
                        <th class="text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.quiz_title') }}</th>
                        <th class="text-nowrap"><i class="ti ti-calendar me-1"></i>{{ __('startup.date_completed') }}</th>
                        <th class="text-nowrap"><i class="ti ti-user me-1"></i>{{ __('startup.founder') }}</th>
                        <th class="text-nowrap"><i class="ti ti-percent me-1"></i>{{ __('startup.score') }}</th>
                        <th class="text-nowrap"><i class="ti ti-badge me-1"></i>{{ __('startup.status') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($quizzes as $q)
                      <tr class="border-top">
                        <td class="fw-medium">
                          @if($q['result_url'])
                            <a href="{{ $q['result_url'] }}" target="_blank" class="text-primary">{{ $q['title'] }}</a>
                          @else
                            {{ $q['title'] }}
                          @endif
                        </td>
                        <td>{{ $q['date_completed'] ?? 'N/A' }}</td>
                        <td>{{ $q['founder'] ?? 'N/A' }}</td>
                        <td class="text-center">
                          @if($q['score'] !== null)
                            <span class="badge bg-label-info">{{ $q['score'] }}</span>
                          @else
                            <span class="text-muted small">N/A</span>
                          @endif
                        </td>
                        <td class="text-center">
                          <span class="badge bg-label-{{ $q['status_badge'] }}">{{ $q['status_label'] }}</span>
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
            @if(count($surveys) > 0)
              @if(empty($surveys[0]['survey_id']))
                <div class="col-12 text-center text-muted">{{ __('startup.no_surveys') }}</div>
              @else
                <div class="table-responsive">
                  <table class="table table-sm mb-0">
                    <thead class="table-light">
                      <tr>
                        <th class="text-center text-nowrap"><i class="ti ti-hash me-1"></i>{{ __('startup.survey_id') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.survey_title') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-flag me-1"></i>{{ __('startup.program_step') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-calendar me-1"></i>{{ __('startup.date_completed') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-circle-check me-1"></i>{{ __('startup.status') }}</th>
                        <th class="text-center text-nowrap"><i class="ti ti-file me-1"></i>{{ __('startup.answers') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($surveys as $key => $s)
                      <tr class="border-top {{ $s['is_answered'] ? '' : 'table-warning bg-opacity-25' }}">
                        <td><span class="badge bg-label-{{ isset($badgeColors[$key]) ? $badgeColors[$key] : 'secondary' }}">{{ $s['survey_id'] }}</span></td>
                        <td class="fw-medium">{{ $s['title'] }}</td>
                        <td>{{ $s['program_step'] }}</td>
                        <td>{{ $s['date_completed'] ?? '—' }}</td>
                        <td class="text-center">
                          @if($s['is_answered'])
                            <span class="badge bg-label-success"><i class="ti ti-check me-1"></i>{{ __('startup.answered') }}</span>
                          @else
                            <span class="badge bg-label-warning"><i class="ti ti-clock me-1"></i>{{ __('startup.not_answered') }}</span>
                          @endif
                        </td>
                        <td class="text-center">
                          @if($s['is_answered'] && count($s['answers']) > 0)
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#{{ $s['modal_id'] }}">
                              <i class="ti ti-eye me-1"></i>{{ __('admin.View') }}
                            </button>
                          @else
                            <span class="text-muted small">—</span>
                          @endif
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>

                  {{-- Survey Answer Modals --}}
                  @foreach ($surveys as $s)
                    @if($s['is_answered'] && count($s['answers']) > 0)
                    <div class="modal fade" id="{{ $s['modal_id'] }}" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title fw-bold">
                              <i class="ti ti-mood-smile me-2 text-warning"></i>{{ $s['title'] }}
                              <small class="text-muted fw-normal ms-2">{{ $s['survey_id'] }}</small>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <p class="text-muted small mb-3"><i class="ti ti-calendar me-1"></i>{{ __('startup.date_completed') }}: {{ $s['date_completed'] }}</p>
                            <div class="list-group list-group-flush">
                              @foreach ($s['answers'] as $i => $qa)
                              <div class="list-group-item px-0 py-3">
                                <div class="d-flex gap-2 align-items-start">
                                  <span class="badge bg-label-primary mt-1">{{ $i + 1 }}</span>
                                  <div>
                                    <p class="mb-1 fw-semibold text-dark">{{ $qa['question'] }}</p>
                                    <p class="mb-0 text-muted">{{ $qa['answer'] ?? '—' }}</p>
                                  </div>
                                </div>
                              </div>
                              @endforeach
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('startup.close') }}</button>
                          </div>
                        </div>
                      </div>
                    </div>
                    @endif
                  @endforeach
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
            @if(count($services) > 0)
              @if(empty($services[0]['name']))
                <div class="col-12 text-center text-muted">{{ __('startup.no_services') }}</div>
              @else
                <div class="table-responsive">
                  <table class="table table-sm mb-0">
                    <thead class="table-light">
                      <tr>
                        <th class="text-nowrap"><i class="ti ti-gift me-1"></i>{{ __('startup.service_name') }}</th>
                        <th class="text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.description') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($services as $s)
                      <tr class="border-top">
                        <td class="fw-medium">{{ $s['name'] }}</td>
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
            @if(count($forms) > 0)
              @if(empty($forms[0]['title']))
                <div class="col-12 text-center text-muted">{{ __('startup.no_forms') }}</div>
              @else
                <div class="table-responsive">
                  <table class="table table-sm mb-0">
                    <thead class="table-light">
                      <tr>
                        <th class="text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.form_title') }}</th>
                        <th class="text-nowrap"><i class="ti ti-flag me-1"></i>{{ __('startup.custom_form_step') }}</th>
                        <th class="text-nowrap"><i class="ti ti-calendar me-1"></i>{{ __('startup.submission_date') }}</th>
                        <th class="text-nowrap"><i class="ti ti-file me-1"></i>{{ __('startup.form_inputs') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($forms as $f)
                      <tr class="border-top">
                        <td class="fw-medium">{{ $f['title'] }}</td>
                        <td>{{ $f['step_name'] }}</td>
                        <td>{{ $f['submission_date'] ?? '—' }}</td>
                        <td class="text-center">
                          <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#{{ $f['modal_id'] }}">
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
<div class="modal fade" id="{{ $f['modal_id'] }}" tabindex="-1" aria-hidden="true">
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
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th class="text-nowrap"><i class="ti ti-field me-1"></i>{{ __('startup.field_name') }}</th>
                <th class="text-nowrap"><i class="ti ti-database me-1"></i>{{ __('startup.field_type') }}</th>
                <th class="text-nowrap"><i class="ti ti-file-text me-1"></i>{{ __('startup.submitted_value') }}</th>
                <th class="text-nowrap"><i class="ti ti-paperclip me-1"></i>{{ __('startup.attachment') }}</th>
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
    var screeningResultsData = @json( $screeningResultsData );
    var finalScreeningResultsData = @json( $finalScreeningResultsData );
    
    document.addEventListener("DOMContentLoaded", function () {
      // Initialize DataTable
      DataTableInit(".table");
      buildEvaluationModal(screeningResultsData, 'screeningEvaluationBody');
      buildEvaluationModal(finalScreeningResultsData, 'finalScreeningEvaluationBody');
    });

    function buildEvaluationModal(data, modalId) {
      const totalScore = (data.total * 100).toFixed(1);

      let modalContent = `
        <style>
          .judge-card-ribbon { position: relative; overflow: hidden; }
          .approval-ribbon {
            position: absolute; top: 2px; right: -8px;
            width: 180px; padding: 6px 0;
            transform: translate(50px, 18px) rotate(45deg);
            font-size: 10px; font-weight: 600;
            text-align: center; white-space: nowrap;
            text-transform: uppercase; letter-spacing: 0.5px;
            z-index: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.15);
          }
          .approval-ribbon.ribbon-approved { background: #28a745; color: #fff; }
          .approval-ribbon.ribbon-disapproved { background: #dc3545; color: #fff; }
          .approval-ribbon.ribbon-pending { background: #ffc107; color: #212529; }
        </style>
        <div class="px-4 py-3">
          <!-- Overall Score Header -->
          <div class="row mb-5">
            <div class="col-12">
              <div class="card bg-gradient-primary text-white border-0 shadow-sm">
                <div class="card-body text-center py-4">
                  <div class="d-flex align-items-center justify-content-center mb-4">
                    <div class="circular-progress-large position-relative d-inline-flex align-items-center justify-content-center" style="width: 90px; height: 90px;">
                      <div class="progress-circle position-absolute w-100 h-100" style="
                        background: conic-gradient(
                          ${totalScore >= 75 ? '#28a745' : (totalScore >= 50 ? '#17a2b8' : (totalScore >= 25 ? '#ffc107' : '#dc3545'))}
                          ${totalScore * 3.6}deg,
                          rgba(255,255,255,0.3) ${totalScore * 3.6}deg
                        );
                        border-radius: 50%;
                      "></div>
                      <span class="fw-bold text-white position-relative fs-3">${totalScore}%</span>
                    </div>
                  </div>
                  <h4 class="mb-2">{{ __('cycles.Overall Evaluation Score') }}</h4>
                  <p class="mb-0 opacity-90">{{ __('cycles.Based on all judge evaluations') }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Judges Summary -->
          <div class="row mb-5">
            <div class="col-12">
              <h5 class="fw-bold mb-4 d-flex align-items-center">
                <i class="ti ti-users text-primary me-2"></i>
                {{ __('cycles.Judges Summary') }}
              </h5>
              <div class="row g-4">`;

      // Add judges summary cards
      data.judges.forEach(judge => {
        const judgeScore = (judge.evaluation.value * 100).toFixed(1);
        const statusClass = judge.evaluation.status === 'Published' ? 'success' :
                           judge.evaluation.status === 'Draft' ? 'warning' : 'danger';
        const statusIcon = judge.evaluation.status === 'Published' ? 'circle-check' :
                          judge.evaluation.status === 'Draft' ? 'clock' : 'circle-x';
        const approvalLabel = judge.evaluation.approval === 'approved' ? '{{ __("cycles.Approved") }}' :
                          judge.evaluation.approval === 'disapproved' ? '{{ __("cycles.Disapproved") }}' : '{{ __("cycles.Pending") }}';
        const ribbonClass = judge.evaluation.approval === 'approved' ? 'ribbon-approved' :
                          judge.evaluation.approval === 'disapproved' ? 'ribbon-disapproved' : 'ribbon-pending';

        modalContent += `
                <div class="col-md-4">
                  <div class="card border shadow-sm h-100 judge-card-ribbon">
                    <span class="approval-ribbon ${ribbonClass}">${approvalLabel}</span>
                    <div class="card-body text-center py-4">
                      <img src="${judge.image_url}" alt="${judge.name}" class="rounded-circle mb-3" width="60" height="60">
                      <h6 class="fw-semibold mb-2">${judge.name}</h6>
                      <div class="badge bg-${statusClass} bg-opacity-10 text-${statusClass} mb-3 px-3 py-2">
                        <i class="ti ti-${statusIcon} me-1"></i>
                        ${judge.evaluation.status}
                      </div>
                      <div class="h3 mb-0 ${judge.evaluation.status === 'Not Evaluated' ? 'text-muted' : 'text-primary'}">${judge.evaluation.status === 'Not Evaluated' ? '-' : judgeScore + '%'}</div>
                    </div>
                  </div>
                </div>`;
      });

      modalContent += `
              </div>
            </div>
          </div>

          <!-- Detailed Criteria Breakdown -->
          <div class="row mb-5">
            <div class="col-12">
              <h5 class="fw-bold mb-4 d-flex align-items-center">
                <i class="ti ti-list-details text-primary me-2"></i>
                {{ __('cycles.Detailed Criteria Breakdown') }}
              </h5>`;

      // Add criteria details
      data.criteria.forEach(criterion => {
        const criterionScore = (criterion.value * 100).toFixed(1);
        modalContent += `
              <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light py-3">
                  <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">${criterion.name}</h6>
                    <span class="fw-bold text-primary fs-5">${criterionScore} %</span>
                  </div>
                </div>
                <div class="card-body p-4">`;

        // Add sub-criteria
        criterion.details.forEach(subCriterion => {
          const subScore = (subCriterion.value * 100).toFixed(1);
          modalContent += `
                  <div class="border rounded-3 p-4 mb-4 bg-light bg-opacity-50">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                      <h6 class="mb-0 fw-semibold text-dark">${subCriterion.name}</h6>
                      <span class="fw-bold text-primary fs-6">${subScore} %</span>
                    </div>

                    <!-- Judge Details for this sub-criterion -->
                    <div class="row g-3">`;

          subCriterion.details.forEach(judgeDetail => {
            const judgeScore = (judgeDetail.value * 100).toFixed(1);
            const statusClass = judgeDetail.status === 'Published' ? 'success' :
                               judgeDetail.status === 'Draft' ? 'warning' : 'danger';

            modalContent += `
                      <div class="col-md-6">
                        <div class="card border-0 bg-white shadow-sm">
                          <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                              <div class="d-flex align-items-center">
                                <img src="${data.judges.find(j => j.id === judgeDetail.judger.id)?.image_url || '/icons/account.svg'}"
                                     alt="${judgeDetail.judger.name}" class="rounded-circle me-3" width="32" height="32">
                                <div>
                                  <div class="fw-medium text-dark">${judgeDetail.judger.name}</div>
                                  <span class="badge bg-${statusClass} bg-opacity-10 text-${statusClass} small mt-1">${judgeDetail.status}</span>
                                </div>
                              </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                              <span class="text-muted">Score: ${judgeDetail.given_score}/${judgeDetail.max}</span>
                              <span class="fw-bold text-primary">${judgeScore}%</span>
                            </div>
                            ${judgeDetail.comment ? `<div class="border-top pt-3"><small class="text-muted fst-italic">"${judgeDetail.comment}"</small></div>` : ''}
                          </div>
                        </div>
                      </div>`;
          });

          modalContent += `
                    </div>
                  </div>`;
        });

        modalContent += `
                </div>
              </div>`;
      });

      modalContent += `
            </div>
          </div>

          <!-- Individual Judge Evaluations -->
          <div class="row">
            <div class="col-12">
              <h5 class="fw-bold mb-4 d-flex align-items-center">
                <i class="ti ti-user-check text-primary me-2"></i>
                {{ __('cycles.Individual Judge Evaluations') }}
              </h5>
              <div class="accordion" id="judgeAccordion">`;

      // Add individual judge evaluations
      data.judges.forEach((judge, index) => {
        const judgeScore = (judge.evaluation.value * 100).toFixed(1);
        const statusClass = judge.evaluation.status === 'Published' ? 'success' :
                           judge.evaluation.status === 'Draft' ? 'warning' : 'danger';
        const approvalBadgeClass = judge.evaluation.approval === 'approved' ? 'success' :
                           judge.evaluation.approval === 'disapproved' ? 'danger' : 'warning';
        const approvalBadgeIcon = judge.evaluation.approval === 'approved' ? 'circle-check' :
                           judge.evaluation.approval === 'disapproved' ? 'times-circle' : 'clock';
        const approvalLabel = judge.evaluation.approval === 'approved' ? '{{ __("cycles.Approved") }}' :
                           judge.evaluation.approval === 'disapproved' ? '{{ __("cycles.Disapproved") }}' : '{{ __("cycles.Pending") }}';

        modalContent += `
                <div class="accordion-item border shadow-sm mb-3">
                  <h2 class="accordion-header" id="heading${judge.id}">
                    <button class="accordion-button ${index === 0 ? '' : 'collapsed'} py-4" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse${judge.id}" aria-expanded="${index === 0 ? 'true' : 'false'}" aria-controls="collapse${judge.id}">
                      <div class="d-flex align-items-center w-100">
                        <img src="${judge.image_url}" alt="${judge.name}" class="rounded-circle me-4" width="40" height="40">
                        <div class="flex-grow-1">
                          <div class="fw-bold text-dark">${judge.name}</div>
                          <div class="small text-muted mt-1">${judge.email}</div>
                        </div>
                        <div class="me-2">
                          <span class="badge bg-${approvalBadgeClass} bg-opacity-10 text-${approvalBadgeClass} px-3 py-2"><i class="far fa-fw fa-sm fa-${approvalBadgeIcon} me-1"></i>${approvalLabel}</span>
                        </div>
                        <div class="me-4">
                          <span class="badge bg-${statusClass} bg-opacity-10 text-${statusClass} px-3 py-2">${judge.evaluation.status}</span>
                        </div>
                        <div class="text-end me-4">
                          <div class="fw-bold fs-5 ${judge.evaluation.status === 'Not Evaluated' ? 'text-muted' : 'text-primary'}">${judge.evaluation.status === 'Not Evaluated' ? '-' : judgeScore + '%'}</div>
                        </div>
                      </div>
                    </button>
                  </h2>
                  <div id="collapse${judge.id}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}"
                       aria-labelledby="heading${judge.id}" data-bs-parent="#judgeAccordion">
                    <div class="accordion-body p-4">`;

        if (judge.evaluation.status === 'Not Evaluated') {
          modalContent += `
                      <div class="text-center text-muted py-5">
                        <i class="ti ti-clock fs-1 mb-4 opacity-50"></i>
                        <h6 class="text-muted">{{ __('cycles.This judge has not evaluated this company yet.') }}</h6>
                      </div>`;
        } else {
          // Add judge's detailed evaluation
          judge.evaluation.details.forEach(judgeCtiterion => {
            if (judgeCtiterion.details.length > 0) {
              modalContent += `
                      <div class="mb-5">
                        <h6 class="fw-bold mb-4 text-dark">${judgeCtiterion.name}</h6>
                        <div class="row g-4">`;

              judgeCtiterion.details.forEach(detail => {
                const detailScore = (detail.score / detail.max * 100).toFixed(1);
                modalContent += `
                          <div class="col-md-6">
                            <div class="card border shadow-sm">
                              <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                  <h6 class="mb-0 text-dark">${detail.name}</h6>
                                  <span class="badge bg-label-primary text-primary px-3 py-2">${detail.score}/${detail.max}</span>
                                </div>
                                <div class="mb-3">
                                  <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: ${detailScore}%" aria-valuenow="${detailScore}" aria-valuemin="0" aria-valuemax="100"></div>
                                  </div>
                                </div>
                                <div class="border-top pt-3">
                                  ${detail.comment ? `<small class="text-muted fst-italic">"${detail.comment}"</small>` : '<small class="text-muted">No comment provided</small>'}
                                </div>
                              </div>
                            </div>
                          </div>`;
              });

              modalContent += `
                        </div>
                      </div>`;
            }
          });

          if (judge.evaluation.comment) {
            modalContent += `
                      <div class="mt-5 pt-4 border-top">
                        <h6 class="fw-bold mb-3">{{ __('cycles.Overall Comment') }}</h6>
                        <div class="bg-light rounded-3 p-4">
                          <p class="mb-0 text-dark fst-italic">"${judge.evaluation.comment}"</p>
                        </div>
                      </div>`;
          }

          // Form submission comments
          if (judge.form_comments && judge.form_comments.length > 0) {
            modalContent += `
                      <div class="mt-5 pt-4 border-top">
                        <h6 class="fw-bold mb-3">
                          <i class="ti ti-message-dots text-primary me-2"></i>
                          {{ __('cycles.Submission Comments') }}
                        </h6>
                        <div class="list-group list-group-flush">`;
            judge.form_comments.forEach(fc => {
              modalContent += `
                          <div class="list-group-item border-start border-primary border-3 mb-2 rounded bg-light">
                            <div class="fw-semibold text-dark mb-1">${fc.question}</div>
                            <div class="text-muted fst-italic">"${fc.comment}"</div>
                          </div>`;
            });
            modalContent += `
                        </div>
                      </div>`;
          }
        }

        modalContent += `
                    </div>
                  </div>
                </div>`;
      });

      modalContent += `
              </div>
            </div>
          </div>
        </div>`;

      $('#' + modalId).html(modalContent);
    }

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

    // Export CSV
    document.getElementById('export-csv').addEventListener('click', function () {
      const startup      = @json($startup);
      const teamData     = @json($teamData);
      const ownershipData= @json($ownershipData);
      const legalInfo    = @json($legal_information);
      const trainingData = @json($training);
      const mentorshipData = @json($mentorshipData);
      const milestonesData = @json($milestones);
      const tasksData    = @json($tasks);
      const quizzesData  = @json($quizzes);
      const surveysData  = @json($surveys);
      const servicesData = @json($services);
      const formsData    = @json($forms);
      const screeningData= @json($screeningData);

      const csvSections = [];

      function esc(val) {
        if (val === null || val === undefined || val === false) return '';
        const str = String(val);
        if (str.includes(',') || str.includes('"') || str.includes('\n')) {
          return '"' + str.replace(/"/g, '""') + '"';
        }
        return str;
      }

      function addSection(title, headers, rows) {
        csvSections.push(esc(title));
        csvSections.push(headers.map(esc).join(','));
        rows.forEach(row => csvSections.push(row.map(esc).join(',')));
        csvSections.push('');
      }

      // ── 1. Startup Info ─────────────────────────────────────────────────────
      csvSections.push('{{ __("startup.startup_info_tab") }}');
      csvSections.push('{{ __("startup.startup_name") }},' + esc(startup.name));
      csvSections.push('{{ __("startup.location") }},' + esc(startup.location));
      csvSections.push('{{ __("startup.completion_date") }},' + esc(startup.established_at));
      csvSections.push('{{ __("startup.business_type") }},' + esc(startup.business_type));
      csvSections.push('{{ __("startup.industry") }},' + esc(startup.industry));
      csvSections.push('{{ __("startup.sector") }},' + esc(startup.sector));
      csvSections.push('{{ __("startup.technology") }},' + esc(startup.technologies));
      csvSections.push('{{ __("startup.development_stage") }},' + esc(startup.development_stage));
      csvSections.push('{{ __("startup.funding_stage") }},' + esc(startup.funding_stage));
      csvSections.push('{{ __("startup.team_size") }},' + esc(startup.team_size));
      csvSections.push('{{ __("startup.program_name") }},' + esc(startup.program ? startup.program.name : ''));
      csvSections.push('{{ __("startup.cohort") }},' + esc(startup.program ? startup.program.cohort : ''));
      csvSections.push('{{ __("startup.start_date_label") }},' + esc(startup.program ? startup.program.start : ''));
      csvSections.push('{{ __("startup.completion_date") }} (End),' + esc(startup.program ? startup.program.end : ''));
      csvSections.push('Website,' + esc(startup.links ? startup.links.website : ''));
      csvSections.push('LinkedIn,' + esc(startup.links ? startup.links.linkedin : ''));
      csvSections.push('');

      // ── 2. Legal ─────────────────────────────────────────────────────────────
      if (Array.isArray(legalInfo) && legalInfo.length && legalInfo[0].Registered) {
        const l = legalInfo[0];
        csvSections.push('{{ __("startup.legal_status") }}');
        csvSections.push('{{ __("startup.registered") }},' + esc(l.Registered));
        csvSections.push('{{ __("startup.commercial_register") }},' + esc(l.Commercial_Register));
        csvSections.push('{{ __("startup.tax_card") }},' + esc(l.Tax_Card));
        csvSections.push('');
      }

      // ── 3. Team — Leader ─────────────────────────────────────────────────────
      if (teamData.leader && teamData.leader.name) {
        csvSections.push('{{ __("startup.startup_leader") }}');
        csvSections.push('{{ __("startup.full_name") }},' + esc(teamData.leader.name));
        csvSections.push('{{ __("startup.email") }},' + esc(teamData.leader.email));
        csvSections.push('{{ __("startup.phone") }},' + esc(teamData.leader.phone));
        csvSections.push('{{ __("startup.country") }},' + esc(teamData.leader.country));
        csvSections.push('');
      }

      // ── 4. Founders ──────────────────────────────────────────────────────────
      const founders = (teamData.founders || []).filter(f => f.name);
      if (founders.length) {
        addSection(
          '{{ __("startup.founders") }}',
          ['{{ __("startup.full_name") }}', '{{ __("startup.email") }}', 'LinkedIn'],
          founders.map(f => [f.name, f.email, f.linkedin])
        );
      }

      // ── 5. Employees ─────────────────────────────────────────────────────────
      const employees = (teamData.employees || []).filter(e => e.name);
      if (employees.length) {
        addSection(
          '{{ __("startup.team_tab") }}',
          ['{{ __("startup.full_name") }}', '{{ __("startup.job_title") }}', '{{ __("startup.gender") }}', '{{ __("startup.employment_type") }}', '{{ __("startup.start_date_label") }}'],
          employees.map(e => [e.name, e.job, e.gender, e.employment, e.start_date])
        );
      }

      // ── 6. Shareholders ──────────────────────────────────────────────────────
      const shareholders = (ownershipData.shareholders || []).filter(s => s.name);
      if (shareholders.length) {
        addSection(
          '{{ __("startup.shareholders") }}',
          ['{{ __("startup.full_name") }}', '{{ __("startup.email") }}', '{{ __("startup.founder_role") }}', '%'],
          shareholders.map(s => [s.name, s.Email, s.role, s.ownership])
        );
      }

      // ── 7. Investors ─────────────────────────────────────────────────────────
      const investors = (ownershipData.investors || []).filter(i => i.name);
      if (investors.length) {
        addSection(
          '{{ __("startup.investors") }}',
          ['{{ __("startup.full_name") }}', '{{ __("startup.type") }}'],
          investors.map(i => [i.name, i.type])
        );
      }

      // ── 8. Screening ─────────────────────────────────────────────────────────
      ['initial', 'final'].forEach(phase => {
        const ph = screeningData[phase];
        if (!ph || !ph.step_name) return;
        const label = phase === 'initial' ? '{{ __("startup.initial_screening") }}' : '{{ __("startup.final_screening") }}';
        addSection(
          label + ' — ' + ph.step_name + ' ({{ __("startup.score") }}: ' + ph.overall_score + ')',
          ['{{ __("startup.criterion") }}', '{{ __("startup.weight") }}', '{{ __("startup.score") }}'],
          (ph.criteria || []).map(c => [c.name, c.weight, c.score])
        );
        addSection(
          label + ' — {{ __("startup.judges_evaluations") }}',
          ['{{ __("startup.judge") }}', '{{ __("startup.score") }}', '{{ __("startup.comments") }}'],
          (ph.judges || []).map(j => [j.name, j.score, j.comments])
        );
      });

      // ── 9. Training ──────────────────────────────────────────────────────────
      const trainingSessions = (trainingData || []).filter(t => t.title);
      if (trainingSessions.length) {
        addSection(
          '{{ __("startup.training_tab") }}',
          ['{{ __("startup.title") }}', '{{ __("startup.session_title") }}', '{{ __("startup.date") }}', '{{ __("startup.duration_hours") }}', '{{ __("startup.step") }}', '{{ __("startup.attendance") }}'],
          trainingSessions.map(t => [t.title, t.trainer_course_name, t.date, t.duration, t.step, t.attendance])
        );
      }

      // ── 10. Mentorship ───────────────────────────────────────────────────────
      const mentorSessions = (mentorshipData || []).filter(m => m.mentor);
      if (mentorSessions.length) {
        addSection(
          '{{ __("startup.mentorship_tab") }}',
          ['{{ __("startup.mentor_name") }}', '{{ __("startup.date") }}', '{{ __("startup.duration_hours") }}', '{{ __("startup.founder_attended") }}', '{{ __("startup.attendance") }}', '{{ __("startup.rating") }}', '{{ __("startup.comments") }}'],
          mentorSessions.map(m => [m.mentor, m.date, m.duration, m.founder_attended, m.attended, m.rating, m.comments])
        );
      }

      // ── 11. Milestones ───────────────────────────────────────────────────────
      const milestoneRows = (milestonesData || []).filter(m => m.title);
      if (milestoneRows.length) {
        addSection(
          '{{ __("startup.milestones_tab") }}',
          ['{{ __("startup.milestone_title_label") }}', '{{ __("startup.description") }}', '{{ __("startup.due_date") }}', '{{ __("startup.completion") }}'],
          milestoneRows.map(m => [m.title, m.description, m.due_date, m.completion])
        );
      }

      // ── 12. Tasks ────────────────────────────────────────────────────────────
      const taskRows = (tasksData || []).filter(t => t.title);
      if (taskRows.length) {
        addSection(
          '{{ __("startup.tasks_tab") }}',
          ['{{ __("startup.task_title") }}', '{{ __("startup.description") }}', '{{ __("startup.mentor") }}', '{{ __("startup.due_date") }}', '{{ __("startup.completion_date") }}', '{{ __("startup.status") }}'],
          taskRows.map(t => [t.title, t.description, t.mentor, t.due_date, t.completion_date, t.status])
        );
      }

      // ── 13. Quizzes ──────────────────────────────────────────────────────────
      if (Array.isArray(quizzesData) && quizzesData.length) {
        addSection(
          '{{ __("startup.quizzes_tab") }}',
          ['{{ __("startup.quiz_title") }}', '{{ __("startup.date_completed") }}', '{{ __("startup.founder") }}', '{{ __("startup.score") }}', '{{ __("startup.status") }}'],
          quizzesData.map(q => [q.title, q.date_completed, q.founder, q.score, q.status_label])
        );
      }

      // ── 14. Surveys ──────────────────────────────────────────────────────────
      if (Array.isArray(surveysData) && surveysData.length) {
        addSection(
          '{{ __("startup.surveys_tab") }}',
          ['{{ __("startup.survey_title") }}', '{{ __("startup.program_step") }}', '{{ __("startup.date_completed") }}', '{{ __("startup.status") }}'],
          surveysData.map(s => [s.title, s.program_step, s.date_completed, s.is_answered ? '{{ __("startup.answered") }}' : '{{ __("startup.not_answered") }}'])
        );
      }

      // ── 15. Services ─────────────────────────────────────────────────────────
      const serviceRows = (servicesData || []).filter(s => s.name);
      if (serviceRows.length) {
        addSection(
          '{{ __("startup.services_tab") }}',
          ['{{ __("startup.service_name") }}', '{{ __("startup.description") }}'],
          serviceRows.map(s => [s.name, s.description])
        );
      }

      // ── 16. Forms ─────────────────────────────────────────────────────────────
      if (Array.isArray(formsData) && formsData.length) {
        const formRows = [];
        formsData.forEach(f => {
          (f.fields || []).forEach(field => {
            formRows.push([f.title, f.step_name, f.submission_date, field.name, field.type, field.value, field.attachment]);
          });
        });
        if (formRows.length) {
          addSection(
            '{{ __("startup.forms_tab") }}',
            ['{{ __("startup.form_title") }}', '{{ __("startup.step") }}', '{{ __("startup.submission_date") }}', '{{ __("startup.field_name") }}', '{{ __("startup.field_type") }}', '{{ __("startup.financial.value") }}', '{{ __("startup.attachment") }}'],
            formRows
          );
        }
      }

      const csvContent = csvSections.join('\n');
      const blob = new Blob(['﻿' + csvContent], { type: 'text/csv;charset=utf-8;' });
      const url  = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.setAttribute('href', url);
      link.setAttribute('download', esc(startup.name || 'alumni') + '-alumni.csv');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    });
  </script>
@stop
