@php
  $msgs = 0;
  if ($_user->hasAnyRole('Admin|Manager')) {
      $msgs = App\Messages::where('read', 0)->count();
  }
  $_setting = $_settings;
  $unread_messages = App\Messenger::where('receiver', Auth::user()->id)
      ->whereNull('read')
      ->count();
  $all = $msgs + $unread_messages;
  $companyid = $_user->company?->id ?? null;

$menuItems = [
    // Main Dashboard
    [
        'label' => $l == 'en' ? 'Dashboard' : 'لوحة القيادة',
        'icon' => 'ti ti-smart-home',
        'route' => '/dashboard',
        'permission' => 'core.dashboard_view',
        'isVisible' => true, // Always visible for authenticated users
        'subItems' => []
    ],

    // Profile
    [
        'label' => $l == 'en' ? 'My Profile' : 'ملفي الشخصي',
        'icon' => 'ti ti-user me-3 ti-md',
        'route' => 'user.profile_view',
        'permission' => 'user.profile_view',
        'isVisible' => true,
        'subItems' => []
    ],

    // Settings
    [
        'label' => $l == 'en' ? 'Profile Settings' : 'اعدادات الملف الشخصي',
        'icon' => 'ti ti-user-cog me-3 ti-md',
        'route' => 'user.profile_Edit',
        'permission' => 'user.profile_Edit',
        'isVisible' => true,
        'subItems' => []
    ],

    // My Company (for users without company)
    [
        'label' => $l == 'en' ? 'Create Company' : 'إنشاء شركة',
        'icon' => 'ti-building me-3',
        'route' => 'company.profile_view_mine',
        'permission' => ['company.profile_view', 'assessments.results_analysis_view', 'swot.view'],
        'isVisible' => ( $_user->HaveCompany == 1 && !$_user->company ) || $_user->ableTo('swot.view', $permissions),
        'subItems' => [
            [
                'label' => $l == 'en' ? 'Analysis & Assessment' : 'التحليل والتقييم',
                'icon' => 'ti ti-report-analytics',
                'route' => '/dashboard/analysis-assessment',
                'permission' => 'assessments.results_analysis_view',
                'isVisible' => $_setting->assessment == 1 && $_user->ableTo('assessments.results_analysis_view', $permissions) && !$_user->hasRole("organizationManager")
            ],
            [
                'label' => $l == 'en' ? 'SWOT Analysis' : 'تحليل SWOT',
                'icon' => 'ti ti-target-arrow',
                'route' => 'swot.view',
                'permission' => 'swot.view',
                'isVisible' => $_user->ableTo('swot.view', $permissions)
            ]
        ]
    ],

    // My Company (for users with company)
    [
        'label' => $l == 'en' ? 'My Company' : 'شركتي',
        'icon' => 'ti ti-building me-3 ti-md',
        'route' => null,
        'permission' => ['company.profile_view', 'assessments.results_analysis_view', 'swot.view'],
        'isVisible' => $_user->HaveCompany != 0 && $_user->company,
        'subItems' => [
            [
                'label' => $l == 'en' ? 'My Startup Profile' : 'ملف شركتي الناشئة',
                'icon' => 'ti-building-skyscraper',
                'route' => 'company.profile_view',
                'permission' => 'company.profile_view',
                'isVisible' => $companyid
            ],
            [
                'label' => $l == 'en' ? 'Analysis & Assessment' : 'التحليل والتقييم',
                'icon' => 'ti ti-report-analytics',
                'route' => '/dashboard/analysis-assessment',
                'permission' => 'assessments.results_analysis_view',
                'isVisible' => $_setting->assessment == 1 && $_user->ableTo('assessments.results_analysis_view', $permissions) && !$_user->hasRole("organizationManager")
            ],
            [
                'label' => $l == 'en' ? 'SWOT Analysis' : 'تحليل SWOT',
                'icon' => 'ti ti-target-arrow',
                'route' => 'swot.view',
                'permission' => 'swot.view',
                'isVisible' => $_user->ableTo('swot.view', $permissions)
            ]
        ]
    ],

    // Calendar
    [
        'label' => $l == 'en' ? 'Calendar' : 'التقويم',
        'icon' => 'ti ti-calendar',
        'route' => 'calendar.events_list',
        'permission' => 'calendar.events_list',
        'isVisible' => true,
        'subItems' => []
    ],

    // Messages
    [
        'label' => $l == 'en' ? 'Messages' : 'الرسائل',
        'icon' => 'ti ti-messages',
        'route' => '/dashboard/messages',
        'permission' => 'messages.list',
        'isVisible' => true,
        'subItems' => []
    ],

    // Companies
    [
        'label' => $_user->hasRole('Mentor') ? __('breadcrumb.startup_hub') : __('breadcrumb.startups'),
        'icon' => 'ti ti-building ti-md me-2 me-lg-4 ti-lg',
        'route' => 'users.view_companies',
        'permission' => 'users.view_companies',
        'isVisible' => $_user->hasAnyRole("Registered|Mentor|organizationManager|Judger"),
        'subItems' => []
    ],

    // Mentors
    [
        'label' => $l == 'en' ? 'Mentors' : 'المرشدون',
        'icon' => 'ti ti-users ti-md me-2 me-lg-4 ti-lg',
        'route' => null,
        'permission' => $_user->ableToAny(['mentors.list', 'invitations.index'], $permissions),
        'isVisible' => $_user->ableToAny(['mentors.list', 'invitations.index'], $permissions),
        'subItems' => [
            [
            'label' => __('admin.Mentors'),
            'icon' => 'ti ti-users',
            'route' => '/dashboard/mentors',
            'permission' => $_user->ableTo('mentors.list', $permissions),
            'isVisible' => $_user->ableTo('mentors.list', $permissions)
          ],
          [
            'label' => $l == 'en' ? 'Mentors Invitations' : 'دعوات المرشدون',
            'icon' => 'ti ti-inbox',
            'route' => '/dashboard/mentors/invitations',
            'permission' => $_user->ableTo('invitations.index', $permissions),
            'isVisible' => $_user->ableTo('invitations.index', $permissions)
          ],
        ]
    ],

    // Tasks
    /* [
        'label' => $l == 'en' ? 'Tasks' : 'المهام',
        'icon' => 'ti ti-layout-kanban ti-md me-2 me-lg-4 ti-lg',
        'route' => '/dashboard/mentor/mentorshipSessions',
        'permission' => $_user->ableTo('mentorships.sessions_list_detailed', $permissions),
        'isVisible' => $_user->ableTo('mentorships.sessions_list_detailed', $permissions),
        'subItems' => [
          [
            'label' => $l == 'en' ? 'Tasks' : 'المهام',
            'icon' => 'ti ti-layout-kanban',
            'route' => '/dashboard/mentor/mentorshipSessions',
            'permission' => $_user->ableTo('mentorships.sessions_list_detailed', $permissions),
            'isVisible' => $_user->ableTo('mentorships.sessions_list_detailed', $permissions)
          ]
        ]
    ], */

    // Mentorship Tasks (for Mentors)
    [
        'label' => __('mentor.availability'),
        'icon' => 'ti ti-calendar-cog ti-md me-2 me-lg-4 ti-lg',
        'route' => 'slots.index',
        'permission' => 'slots.index',
        'isVisible' => $_user->hasRole("Mentor") || $_user->ableTo('tasks.index', $permissions),
        'subItems' => []
    ],

    // Mentorship Tasks (for Mentors)
    [
        'label' => $l == 'en' ? 'Tasks' : 'المهام',
        'icon' => 'ti ti-layout-kanban ti-md me-2 me-lg-4 ti-lg',
        'route' => 'tasks.index',
        'permission' => 'tasks.index',
        'isVisible' => $_user->hasAnyRole("Mentor|Registered") || $_user->ableTo('tasks.index', $permissions),
        'subItems' => []
    ],

    // Service Providers
    [
        'label' => $l == 'en' ? 'Services Providers' : 'مقدمي الخدمات',
        'icon' => 'ti ti-users ti-md me-2 me-lg-4 ti-lg',
        'route' => 'services_providers.index',
        'permission' => 'services_providers.index',
        'isVisible' => $_user->ableTo('services_providers.index', $permissions),
        'subItems' => []
    ],
 
    // Enrolled Programs (for Mentors)
    [
        'label' => __('mentor.enrolled_programs'),
        'icon' => 'ti ti-device-desktop-code ti-md me-2 me-lg-4 ti-lg',
        'route' => '/dashboard/program/show',
        'permission' => null,
        'isVisible' => $_user->hasRole("Mentor"),
        'subItems' => []
    ],
    // mentorship sessions (for Mentors)
    [
        'label' => __('admin.Mentorship Sessions'),
        'icon' => 'ti ti-calendar-event ti-md me-2 me-lg-4 ti-lg',
        'route' => '/dashboard/mentor/sessions',
        'permission' => $_user->ableTo('mentorships.sessions', $permissions),
        'isVisible' => $_user->hasRole("Mentor"),
        'subItems' => []
    ],
    // Surveys (for Mentors)
    [
        'label' => __('admin.Surveys'),
        'icon' => 'ti ti-notes ti-md me-2 me-lg-4 ti-lg',
        'route' => '/dashboard/mentor/surveys',
        'permission' => null,
        'isVisible' => $_user->hasRole("Mentor"),
        'subItems' => []
    ],


    // Surveys (for Mentors)
    [
        'label' => __('admin.Surveys'),
        'icon' => 'ti ti-notes ti-md me-2 me-lg-4 ti-lg',
        'route' => '/dashboard/mentor/surveys',
        'permission' => null,
        'isVisible' => $_user->hasRole("Mentor"),
        'subItems' => []
    ],
    // Hackathon
    [
        'label' => $l == 'en' ? 'Hackathon' : 'هاكاثون',
        'icon' => 'ti ti-activity ti-md me-2 me-lg-4 ti-lg',
        'route' => '/dashboard/hackathon',
        'permission' => 'hackathon.view',
        'isVisible' => true,
        'subItems' => []
    ],

    // Perks & Discounts
    [
        'label' => $l == 'en' ? 'Perks & Discounts' : 'المزايا والخصومات',
        'icon' => 'ti ti-gift ti-md me-2 me-lg-4 ti-lg',
        'route' => '/dashboard/perks',
        'permission' => $_user->hasRole("Registered"),
        'isVisible' => true,
        'subItems' => []
    ],

    // My Requests (for Registered users)
    [
        'label' => $l == 'en' ? 'My Requests' : 'طلباتي',
        'icon' => 'ti ti-file-text ti-md me-2 me-lg-4 ti-lg',
        'route' => null,
        'permission' => ['service_requests.list_mine', 'mentorships.list_mine'],
        'isVisible' => $_user->hasRole("Registered"),
        'subItems' => [
            [
                'label' => $l == 'en' ? 'Services Requests' : 'طلبات خدماتي',
                'icon' => 'ti ti-briefcase',
                'route' => '/dashboard/requests/mine',
                'permission' => $_user->ableTo('service_requests.list_mine', $permissions),
                'isVisible' => $_user->hasRole("Registered")
            ],
            [
                'label' => $l == 'en' ? 'Mentorship Requests' : 'طلبات الارشاد',
                'icon' => 'ti ti-users-group',
                'route' => '/dashboard/mentorship/mine',
                'permission' => $_user->ableTo('mentorships.list_mine', $permissions),
                'isVisible' => $_user->hasRole("Registered")
            ]
        ]
    ],

    // Services Directory
    [
        'label' => __('settings.navigation.services_directory'),
        'icon' => 'ti ti-search',
        'route' => null,
        'permission' => ['services.index', 'service_requests.list_all', 'serviceCategories.index'],
        'isVisible' => $_user->hasAnyRole("Registered|Mentor") || $_user->ableToAny(['services.index', 'service_requests.list_all', 'serviceCategories.index'], $permissions),
        'subItems' => [
          [
            'label' => $_user->hasAnyRole('Admin|OrganizationManager|SubAdmin') ? __('admin.All Services') : __('admin.Services'),
            'icon' => 'ti ti-arrows-move',
            'route' => '/dashboard/services',
            'permission' => $_user->ableTo('services.index', $permissions),
            'isVisible' => $_user->ableTo('services.index', $permissions)
          ],
          [
            'label' => __('admin.Services Requests'),
            'icon' => 'ti ti-briefcase',
            'route' => __('service_requests.list_all'),
            'permission' => $_user->ableTo('service_requests.list_all', $permissions),
            'isVisible' => $_user->ableTo('service_requests.list_all', $permissions)
          ],
          [
            'label' => __('admin.Services Categories'),
            'icon' => 'ti ti-layout-grid',
            'route' => '/dashboard/serviceCategories',
            'permission' => $_user->ableTo('serviceCategories.index', $permissions),
            'isVisible' => $_user->ableTo('serviceCategories.index', $permissions)
          ],
        ]
    ],

    // My Courses
    [
        'label' => $l == 'en' ? 'My courses' : 'دوراتي',
        'icon' => 'ti ti-certificate-2',
        'route' => '/dashboard/training/my',
        'permission' => 'trainings.list_mine',
        'isVisible' => $_user->hasRole("Registered"),
        'subItems' => []
    ],

    // My Quizzes
    [
        'label' => $l == 'en' ? 'Quizzes' : 'الاختبارات',
        'icon' => 'ti ti-question-mark',
        'route' => '/dashboard/quizzes/my',
        'permission' => 'quizzes.dashboard_view',
        'isVisible' => $_user->hasRole("Registered"),
        'subItems' => []
    ],

    // File Manager
    [
        'label' => $l == 'en' ? 'File Manager' : 'مدير الملفات',
        'icon' => 'ti ti-file',
        'route' => '/dashboard/myFiles',
        'permission' => 'my_files.list',
        'isVisible' => true,
        'subItems' => []
    ],

    // Program (for Mentors)
    [
        'label' => __('mentor.enrolled_programs'),
        'icon' => 'ti ti-device-desktop-code',
        'route' => '/dashboard/program/show',
        'permission' => $_user->ableTo('programs.view', $permissions),
        'isVisible' => $_user->hasRole("Mentor"),
        'subItems' => []
    ],

    // Screening (for Judgers)
    [
        'label' => $l == 'en' ? 'Screening' : 'الفحص و التقييم',
        'icon' => 'ti ti-gavel',
        'route' => '/dashboard/judger/screening',
        'permission' => 'screening.access',
        'isVisible' => $_user->hasRole("Judger"),
        'subItems' => []
    ],

    // Organization Types (Admin)
    [
        'label' => $l == 'en' ? 'Organization Types' : 'أنواع المؤسسات',
        'icon' => 'ti ti-category',
        'route' => 'organization-types.index',
        'permission' => null,
        'isVisible' => $_user->hasRole("Admin"),
        'subItems' => []
    ],

    // Organizations (Admin)
    [
        'label' => __('admin.Organizations'),
        'icon' => 'ti ti-layout-board',
        'route' => 'organizations.index',
        'permission' => 'organizations.index',
        'isVisible' => $_user->hasRole("Admin"),
        'subItems' => []
    ],

    // Custom Landing Pages
    [
        'label' => __('admin.Custom Landing Pages'),
        'icon' => 'ti ti-layout',
        'route' => 'landing-pages.index',
        'permission' => 'landing-pages.index',
        'isVisible' => $_user->hasAnyRole('subAdmin|Admin') && $_user->ableTo('landing-pages.index', $permissions),
        'subItems' => []
    ],

    // Projects
    [
        'label' => $l == 'en' ? 'Projects' : 'المشاريع',
        'icon' => 'ti ti-folder',
        'route' => 'projects.index',
        'permission' => 'projects.index',
        'isVisible' => $_user->ableTo('projects.index', $permissions) && app()->environment(['local', 'dev', 'test']),
        'subItems' => []
    ],

    // Forms
    [
        'label' => $l == 'en' ? 'Forms' : 'النماذج',
        'icon' => 'ti ti-forms',
        'route' => null,
        'permission' => ['forms.index', 'forms.builder_view', 'forms_submissions.index'],
        'isVisible' => $_user->hasAnyRole("Admin|organizationManager"),
        'subItems' => [
            [
                'label' => __('admin.Forms'),
                'icon' => 'ti ti-list',
                'route' => '/dashboard/forms?tab=forms',
                'permission' => 'forms.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Forms builder' : 'منشئ النماذج',
                'icon' => 'ti ti-tool',
                'route' => '/dashboard/show-builder',
                'permission' => 'forms.builder_view',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Forms Submissions' : 'إرسالات النماذج',
                'icon' => 'ti ti-send',
                'route' => '/dashboard/forms?tab=submissions',
                'permission' => 'forms_submissions.index',
                'isVisible' => true
            ]
        ]
    ],

    // Cycles
    [
        'label' => $l == 'en' ? 'Cycles' : 'الدورات',
        'icon' => 'ti ti-components',
        'route' => null,
        'permission' => ['cycles.list', 'programlist.index'],
        'isVisible' => $_user->ableToAny(["cycles.list", "programlist.index"], $permissions),
        'subItems' => [
            [
                'label' => __('admin.Cycles'),
                'icon' => 'ti ti-clipboard-list',
                'route' => '/dashboard/programlist',
                'permission' => 'programlist.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Steps' : 'الخطوات',
                'icon' => 'ti ti-stairs',
                'route' => '/dashboard/cycles',
                'permission' => 'cycles.list',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Program Startups' : 'برنامج الشركات الناشئة',
                'icon' => 'ti ti-rocket',
                'route' => '/dashboard/cycles/0/registered',
                'permission' => 'cycles.users_manage',
                'isVisible' => true
            ]
        ]
    ],

    // Time Sheets
    [
        'label' => $l == 'en' ? 'TimeSheet' : 'الجدول الزمني',
        'icon' => 'ti ti-clock-hour-2 me-3 ti-md',
        'route' => null,
        'permission' => ['TimeSheet.index', 'time_sheets.reports.finance.view', 'time_sheets.view.mine'],
        'isVisible' => $_user->ableToAny(["TimeSheet.index", "time_sheets.reports.finance.view", "time_sheets.view.mine"], $permissions) && app()->environment(['local', 'dev', 'test']),
        'subItems' => [
            [
                'label' => $l == 'en' ? 'Time sheet' : 'الجدول الزمني',
                'icon' => 'ti ti-clock-record',
                'route' => 'TimeSheet.index',
                'permission' => $_user->ableTo('TimeSheet.index', $permissions) && app()->environment(['local', 'dev', 'test']),
                'isVisible' => $_user->ableTo('TimeSheet.index', $permissions) && app()->environment(['local', 'dev', 'test'])
            ],
            [
                'label' => $l == 'en' ? 'Financial Report' : 'التقرير المالي',
                'icon' => 'ti ti-report-money',
                'route' => 'time_sheets.reports.finance.view',
                'permission' => $_user->ableTo('time_sheets.reports.finance.view', $permissions) && app()->environment(['local', 'dev', 'test']) && !$_user->hasRole("organizationManager"),
                'isVisible' => $_user->ableTo('time_sheets.reports.finance.view', $permissions) && app()->environment(['local', 'dev', 'test']) && !$_user->hasRole("organizationManager")
            ],
            [
                'label' => $l == 'en' ? 'Users TimeSheets' : 'الجدول الزمني للمستخدمين',
                'icon' => 'ti ti-user-check',
                'route' => 'time_sheets.view.mine',
                'permission' => $_user->ableTo('time_sheets.view.mine', $permissions) && app()->environment(['local', 'dev', 'test']) && !$_user->hasRole("organizationManager"),
                'isVisible' => $_user->ableTo('time_sheets.view.mine', $permissions) && app()->environment(['local', 'dev', 'test']) && !$_user->hasRole("organizationManager")
            ]
        ]
    ],

    // Assessments
    [
        'label' => $l == 'en' ? 'Assessments' : 'التقييمات',
        'icon' => 'ti ti-circle-dashed-check ti-md me-3',
        'route' => null,
        'permission' => ['assessments.index'],
        'isVisible' => $_user->ableToAny(["assessments.index"], $permissions),
        'subItems' => [
            [
                'label' => __('admin.Assessments'),
                'icon' => 'ti ti-checklist',
                'route' => 'assessments.index',
                'permission' => 'assessments.index',
                'isVisible' => true
            ]
        ]
    ],
    // Screening
    [
        'label' => __('admin.initial_screening'),
        'icon' => 'ti ti-scale me-3 ti-md',
        'route' => null,
        'permission' => ['screening.index', 'screening.results_view'],
        'isVisible' => $_user->ableToAny(["screening.index", "screening.results_view"], $permissions),
        'subItems' => [
            [
                'label' => __('admin.initial_screening'),
                'icon' => 'ti ti-scale',
                'route' => '/dashboard/screening',
                'permission' => 'screening.index',
                'isVisible' => true
            ],
            /* [
                'label' => $l == 'en' ? 'Screening Results' : 'نتائج الفحص',
                'icon' => 'ti ti-chart-dots',
                'route' => '/dashboard/screening/results',
                'permission' => 'screening.results_view',
                'isVisible' => true
            ] */
        ]
    ],

    // Mentorship
    [
        'label' => $l == 'en' ? 'Mentorship' : 'الإرشاد',
        'icon' => 'ti ti-bulb me-3 ti-md',
        'route' => null,
        'permission' => ['mentorships.requests_list', 'mentorships.requests_view', 'mentorships.sessions_list'],
        'isVisible' => $_user->ableToAny(["mentorships.requests_list", "mentorships.requests_view", "mentorships.sessions_list"], $permissions),
        'subItems' => [
            [
              'label' => $l == 'en' ? 'Programs Mentors' : 'مرشدو البرامج',
              'icon' => 'ti ti-school',
              'route' => '/dashboard/mentorship/assign',
              'permission' => 'mentorships.requests_list',
              'isVisible' => true
            ],
            [
              'label' => __('admin.mentorship_management'),
              'icon' => 'ti ti-settings',
              'route' => '/dashboard/mentorship/view',
              'permission' => 'mentorships.requests_view',
              'isVisible' => true
            ],
            [
              'label' => $l == 'en' ? 'All Mentorship requests' : 'كل طلبات الارشاد',
              'icon' => 'ti ti-list-details',
              'route' => '/dashboard/mentorship/sessions?show=all',
              'permission' => $_user->ableTo('mentorships.sessions_list', $permissions),
              'isVisible' => true
            ],
            [
              'label' => $l == 'en' ? 'New Mentorship requests only' : 'طلبات الارشاد الجديدة فقط',
              'icon' => 'ti ti-new-section',
              'route' => '/dashboard/mentorship/sessions',
              'permission' => $_user->ableTo('mentorships.sessions_list', $permissions),
              'isVisible' => true
            ],
            [
              'label' => $l == 'en' ? 'Approved mentorship requests' : 'طلبات الارشاد الموافق عليها',
              'icon' => 'ti ti-check',
              'route' => '/dashboard/mentorship/sessions?show=approved',
              'permission' => $_user->ableTo('mentorships.sessions_list', $permissions),
              'isVisible' => true
            ],
            [
              'label' => $l == 'en' ? 'Disapproved mentorship requests' : 'طلبات الارشاد المرفوضة',
              'icon' => 'ti ti-x',
              'route' => '/dashboard/mentorship/sessions?show=disapproved',
              'permission' => $_user->ableTo('mentorships.sessions_list', $permissions),
              'isVisible' => true
            ]
        ]
    ],

    // Questions
    [
        'label' => $l == 'en' ? 'Questions' : 'الأسئلة',
        'icon' => 'ti ti-question-mark me-3 ti-md',
        'route' => null,
        'permission' => ['question-categories.index', 'questions-bank.index', 'weights.index'],
        'isVisible' => $_user->ableToAny(["question-categories.index", "questions-bank.index", "weights.index"], $permissions),
        'subItems' => [
            [
                'label' => $l == 'en' ? 'Questions Categories' : 'فئات الأسئلة',
                'icon' => 'ti ti-category',
                'route' => '/dashboard/question-categories',
                'permission' => 'question-categories.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Questions Bank' : 'بنك الأسئلة',
                'icon' => 'ti ti-database',
                'route' => '/dashboard/questions-bank',
                'permission' => 'questions-bank.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Weight' : 'الوزن',
                'icon' => 'ti ti-weight',
                'route' => 'weights.index',
                'permission' => 'weights.index',
                'isVisible' => true
            ]
        ]
    ],

    // Training
    [
        'label' => $l == 'en' ? 'Training' : 'التدريب',
        'icon' => 'ti ti-presentation me-3 ti-md',
        'route' => null,
        'permission' => ['trainings.sessions_list', 'trainings.list', 'trainings.results_view'],
        'isVisible' => $_user->ableToAny(["trainings.sessions_list", "trainings.list", "trainings.results_view"], $permissions),
        'subItems' => [
            [
                'label' => $l == 'en' ? 'Training sessions' : 'جلسات التدريب',
                'icon' => 'ti ti-calendar-event',
                'route' => '/dashboard/training/sessions',
                'permission' => 'trainings.sessions_list',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Training courses' : 'الدورات التدريبية',
                'icon' => 'ti ti-book',
                'route' => '/dashboard/training/all',
                'permission' => 'trainings.list',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Training results' : 'نتائج التدريب',
                'icon' => 'ti ti-chart-bar',
                'route' => '/dashboard/training/results',
                'permission' => 'trainings.results_view',
                'isVisible' => true
            ]
        ]
    ],

    // Quizzes and Surveys
    [
        'label' => $l == 'en' ? 'Quizzes & Surveys' : 'الاختبارات والاستطلاعات',
        'icon' => 'ti ti-stack me-3 ti-md',
        'route' => null,
        'permission' => ['quizzes.index', 'surveys.index'],
        'isVisible' => $_user->ableToAny(["quizzes.index", "surveys.index"], $permissions),
        'subItems' => [
            [
                'label' => $l == 'en' ? 'Quizzes' : 'الاختبارات',
                'icon' => 'ti ti-help-circle',
                'route' => '/dashboard/quizzes',
                'permission' => 'quizzes.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Quiz Results' : 'نتائج الاختبارات',
                'icon' => 'ti ti-clipboard-data',
                'route' => 'quizzes.quiz_Results_view',
                'permission' => 'quizzes.quiz_Results_view',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Surveys' : 'الاستطلاعات',
                'icon' => 'ti ti-forms',
                'route' => '/dashboard/surveys',
                'permission' => 'surveys.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Survey Results' : 'نتائج الاستطلاعات',
                'icon' => 'ti ti-chart-pie',
                'route' => 'surveys.dashboard_count_view',
                'permission' => 'surveys.dashboard_count_view',
                'isVisible' => true
            ]
        ]
    ],

    // Final Screening
    [
        'label' => __('admin.Final Screening'),
        'icon' => 'ti ti-gavel me-3 ti-md',
        'route' => null,
        'permission' => ['screening.index', 'screening.results_view'],
        'isVisible' => $_user->ableToAny(["screening.index", "screening.results_view"], $permissions),
        'subItems' => [
            [
                'label' => __('admin.Final Screening'),
                'icon' => 'ti ti-gavel',
                'route' => '/dashboard/final',
                'permission' => 'screening.index',
                'isVisible' => true
            ],
            /* [
                'label' => $l == 'en' ? 'Final screening results' : 'نتائج الفحص النهائي',
                'icon' => 'ti ti-award',
                'route' => '/dashboard/final/results',
                'permission' => 'screening.results_view',
                'isVisible' => true
            ] */
        ]
    ],

    // Services Management
    [
        'label' => $l == 'en' ? 'Services' : 'الخدمات',
        'icon' => 'ti ti-arrows-move',
        'route' => 'services.index',
        'permission' => ['services.index', 'service_requests.list_all', 'services.create', 'services.index', 'serviceCategories.index'],
        'isVisible' => !$_user->hasAnyRole("Mentor|Judger") && $_user->ableToAny(['services.index', "service_requests.list_all", "services.create", "services.index", "serviceCategories.index"], $permissions),
        'subItems' => [
            [
                'label' => $l == 'en' ? 'Services' : 'الخدمات',
                'icon' => 'ti ti-grid',
                'route' => 'services.index',
                'permission' => 'services.index',
                'isVisible' => $_user->ableTo('services.index', $permissions)
            ],
            [
                'label' => $l == 'en' ? 'Services Requests' : 'طلبات الخدمات',
                'icon' => 'ti ti-mail-opened',
                'route' => 'service_requests.list_all',
                'permission' => 'service_requests.list_all',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Add Service' : 'إضافة خدمة',
                'icon' => 'ti ti-plus',
                'route' => 'services.create',
                'permission' => 'services.create',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Services Categories' : 'فئات الخدمات',
                'icon' => 'ti ti-folders',
                'route' => 'serviceCategories.index',
                'permission' => 'serviceCategories.index',
                'isVisible' => true
            ]
        ]
    ],

    // Users Management
    [
        'label' => $l == 'en' ? 'Users' : 'المستخدمون',
        'icon' => 'ti ti-users',
        'route' => null,
        'permission' => ['users.index', 'users.create', 'roles.index', 'audit-logs.index', 'activities.index'],
        'isVisible' => $_user->hasAnyRole("organizationManager|Admin") && $_user->ableToAny(["users.index"], $permissions),
        'subItems' => [
            [
                'label' => $l == 'en' ? 'Add New User' : 'إضافة مستخدم جديد',
                'icon' => 'ti ti-user-plus',
                'route' => 'users.create',
                'permission' => 'users.create',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Users' : 'المستخدمون',
                'icon' => 'ti ti-users-group',
                'route' => 'users.index',
                'permission' => 'users.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Judges' : 'المحكمون',
                'icon' => 'ti ti-gavel',
                'route' => 'users.judger',
                'permission' => 'users.judger',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Roles and Permissions' : 'الأدوار والأذونات',
                'icon' => 'ti ti-shield-lock',
                'route' => 'roles.index',
                'permission' => 'roles.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Log viewer' : 'عارض السجلات',
                'icon' => 'ti ti-file-text',
                'route' => '/dashboard/audit-logs',
                'permission' => 'audit-logs.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Activities' : 'الأنشطة',
                'icon' => 'ti ti-activity',
                'route' => 'activities.index',
                'permission' => 'activities.index',
                'isVisible' => true
            ]
        ]
    ],

    // Rooms Management
    [
        'label' => $l == 'en' ? 'Rooms Management' : 'إدارة الغرف',
        'icon' => 'ti ti-air-traffic-control',
        'route' => 'rooms.index',
        'permission' => 'rooms.index',
        'isVisible' => $_user->hasAnyRole("organizationManager|Admin"),
        'subItems' => []
    ],

    // Application Settings
    [
        'label' => __('settings.title'),
        'icon' => 'ti ti-settings',
        'route' => 'settings.index',
        'permission' => 'settings.index',
        'isVisible' => $_user->ableTo('settings.index', $permissions),
        'subItems' => []
    ],

    // Organization Settings
    [
        'label' => $l == 'en' ? 'Organization Settings' : 'إعدادات المنظمة',
        'icon' => 'ti ti-settings',
        'route' => 'settings.organization_manage',
        'permission' => 'settings.organization_manage',
        'isVisible' => $_user->hasRole("organizationManager"),
        'subItems' => []
    ],

    // Reminder Logs
    [
        'label' => $l == 'en' ? 'Reminder Logs' : 'سجلات التذكير',
        'icon' => 'ti ti-alarm',
        'route' => 'organization.ReminderLogs',
        'permission' => 'organization.ReminderLogs',
        'isVisible' => $_user->hasRole("organizationManager"),
        'subItems' => []
    ],

    // Documentation
    [
        'label' => $l == 'en' ? 'Documentations' : 'التوثيق',
        'icon' => 'ti ti-file-description',
        'route' => '/dashboard/documentation',
        'permission' => 'documentation.index',
        'isVisible' => $_user->ableTo("documentation.index", $permissions),
        'subItems' => []
    ],

    // Countries
    [
        'label' => $l == 'en' ? 'Countries' : 'الدول',
        'icon' => 'ti ti-world',
        'route' => 'country-management.index',
        'permission' => 'country-management.index',
        'isVisible' => $_user->ableTo("country-management.index", $permissions),
        'subItems' => []
    ],

    // Reports
    [
        'label' => $l == 'en' ? 'Reports' : 'التقارير',
        'icon' => 'ti ti-id',
        'route' => 'reports.list',
        'permission' => 'reports.list',
        'isVisible' => $_user->ableTo("reports.list", $permissions),
        'subItems' => []
    ],

    // License Key
    [
        'label' => $l == 'en' ? 'License Key' : 'مفتاح الترخيص',
        'icon' => 'ti ti-key',
        'route' => 'license.activate_view_form',
        'permission' => 'license.activate_view_form',
        'isVisible' => $_user->ableTo("license.activate_view_form", $permissions),
        'subItems' => []
    ],

    // Notification Templates
    [
        'label' => $l == 'en' ? 'Notification Templates' : 'قوالب الإشعارات',
        'icon' => 'ti ti-mail',
        'route' => null,
        'permission' => null,
        'isVisible' => $_user->hasAnyRole("Admin|organizationManager"),
        'subItems' => [
            [
                'label' => $l == 'en' ? 'Create Template' : 'إنشاء قالب',
                'icon' => 'ti ti-plus',
                'route' => 'notification-templates.create',
                'permission' => $_user->ableTo('notification-templates.create', $permissions),
                'isVisible' => $_user->ableTo('notification-templates.create', $permissions)
            ],
            [
                'label' => $l == 'en' ? 'Manage Templates' : 'إدارة القوالب',
                'icon' => 'ti ti-template',
                'route' => 'notification-templates.index',
                'permission' => $_user->ableTo('notification-templates.index', $permissions),
                'isVisible' => $_user->ableTo('notification-templates.index', $permissions)
            ],
            [
                'label' => $l == 'en' ? 'Manage Triggers' : 'إدارة المشغلات',
                'icon' => 'ti ti-bolt',
                'route' => 'notification-triggers.index',
                'permission' => $_user->ableTo('notification-triggers.index', $permissions),
                'isVisible' => $_user->ableTo('notification-triggers.index', $permissions)
            ]
        ]
    ],

    // System Settings (Admin)
    [
        'label' => $l == 'en' ? 'System Settings' : 'إعدادات النظام',
        'icon' => 'ti ti-settings',
        'route' => null,
        'permission' => ['settings.system_general_view', 'currencies.index', 'importer.index', 'queue.jobs'],
        'isVisible' => $_user->hasAnyRole("Admin|organizationManager"),
        'subItems' => [
            [
                'label' => $l == 'en' ? 'General Settings' : 'الإعدادات العامة',
                'icon' => 'ti ti-settings-cog',
                'route' => '/dashboard/settings',
                'permission' => 'settings.system_general_view',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Importer Tool' : 'أداة الاستيراد',
                'icon' => 'ti ti-file-upload',
                'route' => 'importer.index',
                'permission' => 'importer.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Currencies' : 'العملات',
                'icon' => 'ti ti-currency-dollar',
                'route' => 'currencies.index',
                'permission' => 'currencies.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Tags' : 'العلامات',
                'icon' => 'ti ti-tags',
                'route' => 'Tags.index',
                'permission' => 'Tags.index',
                'isVisible' => "!app()->environment('production', 'beta')"
            ],
            [
                'label' => $l == 'en' ? 'Categories' : 'الفئات',
                'icon' => 'ti ti-category',
                'route' => 'Category.index',
                'permission' => 'Category.index',
                'isVisible' => "!app()->environment('production', 'beta')"
            ],
            [
                'label' => $l == 'en' ? 'SubCategories' : 'الفئات الفرعية',
                'icon' => 'ti ti-category-2',
                'route' => 'subcategories.index',
                'permission' => 'subcategories.index',
                'isVisible' => "!app()->environment('production', 'beta')"
            ],
            [
                'label' => $l == 'en' ? 'Stages' : 'المراحل',
                'icon' => 'ti ti-stairs-up',
                'route' => 'stages.index',
                'permission' => 'stages.index',
                'isVisible' => "!app()->environment('production', 'beta')"
            ],
            [
                'label' => $l == 'en' ? 'Labels' : 'التسميات',
                'icon' => 'ti ti-tag',
                'route' => 'Labels.index',
                'permission' => 'Labels.index',
                'isVisible' => "!app()->environment('production', 'beta')"
            ],
            [
                'label' => $l == 'en' ? 'Queue Jobs' : 'مهام قائمة الانتظار',
                'icon' => 'ti ti-list-numbers',
                'route' => 'queue.jobs',
                'permission' => 'queue.jobs',
                'isVisible' => true
            ]
        ]
    ],

    // Industry Settings
    [
        'label' => $l == 'en' ? 'Industry Settings' : 'إعدادات الصناعة',
        'icon' => 'ti ti-augmented-reality',
        'route' => '/dashboard/industry-settings',
        'permission' => 'settings.system_general_view', // Using closest related permission
        'isVisible' => true,
        'subItems' => []
    ],

    // Home Settings (Admin only, when HomePage is enabled)
    [
        'label' => $l == 'en' ? 'Home Settings' : 'إعدادات الصفحة الرئيسية',
        'icon' => 'ti ti-home-cog me-3 ti-md',
        'route' => null,
        'permission' => ['settings.system_general_view', 'menus.list', 'slides.list', 'pages.list', 'posts.list', 'faqs.index', 'partners.index', 'press.index', 'events.list'],
        'isVisible' => $_setting->assessment == 1,
        'subItems' => [
            [
                'label' => $l == 'en' ? 'About Settings' : 'إعدادات حول',
                'icon' => 'ti ti-info-circle',
                'route' => '/dashboard/setaboutus',
                'permission' => 'settings.system_general_view',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Contact Settings' : 'إعدادات الاتصال',
                'icon' => 'ti ti-address-book',
                'route' => '/dashboard/setcontactus',
                'permission' => 'settings.system_general_update',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Menu Settings' : 'إعدادات القائمة',
                'icon' => 'ti ti-menu-2',
                'route' => '/dashboard/menus',
                'permission' => 'menus.list',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Slider Settings' : 'إعدادات شريط التمرير',
                'icon' => 'ti ti-carousel-horizontal',
                'route' => '/dashboard/slides',
                'permission' => 'slides.list',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Page Settings' : 'إعدادات الصفحة',
                'icon' => 'ti ti-page-break',
                'route' => '/dashboard/pages',
                'permission' => 'pages.list',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Post Settings' : 'إعدادات المشاركة',
                'icon' => 'ti ti-article',
                'route' => '/dashboard/posts',
                'permission' => 'posts.list',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'FAQs Settings' : 'إعدادات الأسئلة الشائعة',
                'icon' => 'ti ti-help-circle',
                'route' => '/dashboard/faqs',
                'permission' => 'faqs.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Partners Settings' : 'إعدادات الشركاء',
                'icon' => 'ti ti-settings',
                'route' => '/dashboard/partners',
                'permission' => 'partners.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Press Settings' : 'إعدادات الصحافة',
                'icon' => 'ti ti-news',
                'route' => '/dashboard/press',
                'permission' => 'press.index',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Manage Events' : 'إدارة الفعاليات',
                'icon' => 'ti ti-calendar-event',
                'route' => '/dashboard/allevents',
                'permission' => 'events.list',
                'isVisible' => true
            ],
            [
                'label' => $l == 'en' ? 'Edit My Page' : 'تعديل صفحتي',
                'icon' => 'ti ti-edit',
                'route' => '/dashboard/editmypage',
                'permission' => 'pages.update_mine',
                'isVisible' => $_user->PageId
            ]
        ]
    ],

    // // Chat System
    // [
    //     'label' => $l == 'en' ? 'Chat' : 'الدردشة',
    //     'icon' => 'ti ti-message-circle',
    //     'route' => '/dashboard/chat',
    //     'permission' => 'chat.messages',
    //     'isVisible' => true,
    //     'subItems' => []
    // ]
];

$menuItems = collect( $menuItems );
$menuItems = $menuItems->flatMap(function($item) {
  if( empty( $item['route'] ) ) { return $item['subItems']; }
  else
  {
    unset( $item['subItems'] );
    return [$item];
  }
})->where('isVisible', true)->filter(function ($item) use ($_user) {
  return $_user->ableTo($item['permission']);
})->values();

@endphp
<!-- Navbar -->
<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar">
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="ti ti-menu-2 ti-md"></i>
    </a>
  </div>

  <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <!-- Search -->
    <div class="navbar-nav align-items-center w-100 w-md-px-300">
      <div class="nav-item navbar-search-wrapper mb-0 w-100">
        <button data-bs-toggle="modal" data-bs-target="#searchModal" class="w-100 nav-item nav-link search-toggler d-flex align-items-center justify-content-center justify-content-md-start px-2 px-md-0">
          <i class="ti ti-search ti-md me-0 me-md-2 me-lg-4"></i>
          <span class="d-none d-md-inline-block text-muted fw-normal fs-6">{{ __('admin.Search') }}</span>
        </button>
      </div>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-auto">
      @if( session()->has('impersonator_id') )
        @php $impersonator = \App\User::find( session()->get('impersonator_id') ); @endphp
        @if( !empty( $impersonator ) )
          @if( $impersonator->ableTo('users.login_as') )
            <li class="nav-item me-1 me-md-2" title="{{ __('admin.Stop Impersonate') }}">
              <form id="stop-impersonation-form" action="{{ URL::to('dashboard/users/impersonate-stop') }}" method="POST">
                @csrf
              </form>
              <button id="stop-impersonation" type="button" class="btn btn-icon btn-label-danger btn-sm waves-effect">
                <i class="fa-solid fa-user-secret fs-5"></i>
              </button>
            </li>
          @endif
        @endif
      @endif
      <!-- Language -->
      <li class="nav-item dropdown-language dropdown me-1 me-md-2">
        <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow p-1 p-md-2"
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
      <li class="nav-item dropdown-style-switcher dropdown me-1 me-md-2">
        <a class="nav-link btn-icon rounded-pill dropdown-toggle hide-arrow p-1 p-md-2"
          href="javascript:void(0);" data-bs-toggle="dropdown">
          <i class="ti ti-md"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
          <li>
            <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
              <span class="align-middle"><i class="ti ti-sun ti-md me-3"></i>Light</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
              <span class="align-middle"><i class="ti ti-moon-stars ti-md me-3"></i>Dark</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
              <span class="align-middle"><i class="ti ti-device-desktop-analytics ti-md me-3"></i>System</span>
            </a>
          </li>
        </ul>
      </li>
      <!-- / Style Switcher-->

      <!-- Notifications -->
      @include('partials.header.notification')
      <!--/ Notifications -->

      <!-- User -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown ms-1">
        <a class="nav-link dropdown-toggle hide-arrow p-1" href="javascript:void(0);" data-bs-toggle="dropdown">
          <div class="avatar avatar-online avatar-sm">
            @if( $_user->hasRole('serviceProvider') )
              <img 
                class="rounded-circle" 
                src="{{ $_user->service_provider?->profile_picture ?? '/images/user-placeholder.jpeg' }}"
                onerror="this.src='/images/user-placeholder.jpeg'"
              />
            @else
              @if (auth()->user()->image_url)
                <img 
                  class="rounded-circle" 
                  src="{{ auth()->user()->image_url }}"
                  onerror="this.src='/images/user-placeholder.jpeg'"
                />
              @else
                <img class="rounded-circle" src="/images/user-placeholder.jpeg" alt="avatar" />
              @endif
            @endif
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item mt-0" href="/dashboard/show/myProfile">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-2">
                  <div class="avatar avatar-online">
                    @if( $_user->hasRole('serviceProvider') )
                      <img 
                        class="rounded-circle" 
                        src="{{ $_user->service_provider?->profile_picture ?? '/images/user-placeholder.jpeg' }}"
                        onerror="this.src='/images/user-placeholder.jpeg'"
                      />
                    @else
                      @if (auth()->user()->image_url)
                        <img 
                          class="rounded-circle" 
                          src="{{ auth()->user()->image_url }}"
                          onerror="this.src='/images/user-placeholder.jpeg'"
                        />
                      @else
                        <img class="rounded-circle" src="/images/user-placeholder.jpeg" alt="avatar" />
                      @endif
                    @endif
                  </div>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                  <small class="text-muted">
                    @php
                      $roles = Auth::user()->getRoleNames();
                      $translatedRoles = $roles->map(fn($role) => __('admin.' . strtolower($role)))->implode(', ');
                    @endphp
                    {{ $translatedRoles }}
                  </small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <div class="dropdown-divider my-1 mx-n2"></div>
          </li>
          <li>
            <a class="dropdown-item" href="/dashboard/show/myProfile">
              <i class="ti ti-user me-3 ti-md"></i>
              <span class="align-middle">{{ __('admin.Profile') }}</span>
            </a>
          </li>
          @if ($companyid && $_user->hasRole('Registered'))
            <li>
              <a class="dropdown-item" href="{{ route('company.profile_view', $companyid) }}">
                <i class="ti ti-building-skyscraper me-3 ti-md"></i>
                <span class="align-middle">{{ __('admin.CompanyProfile') }}</span>
              </a>
            </li>
          @endif
          <li>
            <a class="dropdown-item" href="/dashboard/myProfile">
              <i class="ti ti-settings me-3 ti-md"></i>
              <span class="align-middle">{{ __('admin.settings') }}</span>
            </a>
          </li>

          @if( $_user->ableTo('messages.list', $permissions) )
            <li>
              <a class="dropdown-item" href="{{ route('messages.list') }}">
                <i class="ti ti-message me-3 ti-md"></i>
                <span class="align-middle">
                  {{ __('admin.Messages') }}
                  @if ($unread_messages > 0)
                    <div class="flex-shrink-0 float-end" style="width: 24px; height: 24px;">
                      <small class="badge bg-danger rounded-circle w-100 h-100 d-flex align-items-center justify-content-center ms-2">
                        {{ $unread_messages }}
                      </small>
                    </div>
                  @endif
                </span>
              </a>
            </li>
          @endif

          @if ($_user->ableTo('calendar.events_list', $permissions))
          <li>
            <a class="dropdown-item" href="{{ route('calendar.events_list') }}">
              <i class="ti ti-calendar me-3 ti-md"></i>
              <span class="align-middle">{{ __('admin.Calendar') }}</span>
            </a>
          </li>
          @endif

           @if( $_user->ableTo('documentation.index', $permissions) )
            <li>
              <a class="dropdown-item" href="{{ route('documentation.index') }}">
                <i class="ti ti-question-mark me-3 ti-md"></i>
                <span class="align-middle">FAQ</span>
              </a>
            </li>
          @endif
          <li>
            <div class="d-grid px-2 pt-2 pb-1">
              <a class="btn btn-sm btn-danger d-flex" href="{{ route('logout') }}"
                onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <small class="align-middle">{{ __('admin.Logout') }}</small>
                <i class="ti ti-logout ms-2 ti-14px"></i>
              </a>
              <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
              </form>
            </div>
          </li>
        </ul>
      </li>
      <!--/ User -->
    </ul>
  </div>

  <!-- Search Small Screens -->
  <div class="navbar-search-wrapper search-input-wrapper d-none">
    <input type="text" class="form-control search-input container-xxl border-0" placeholder="Search..."
      aria-label="Search..." />
    <i class="ti ti-x search-toggler cursor-pointer"></i>
  </div>
</nav>
<!-- / Navbar -->

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content p-8 p-md-5">
      <div class="modal-header px-0 px-md-8">
        <h5 class="modal-title" id="searchModalLabel">{{ __('admin.Search_Pages') }}</h5>
        <button type="button" class="btn-close border" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-0 py-3 py-md-4 px-md-8">
        <input type="text" id="menuSearch" class="form-control form-control-lg" placeholder="{{ __('admin.Search') }}...">
        <div class="w-100 d-none d-md-block" style="max-height: 450px; overflow-y: auto;">
          <div id="menuGrid" class="nav nav-pills row g-2 g-md-3 mt-3 w-100">
            @foreach($menuItems as $item)
              <div class="col-12 col-sm-6 nav-item" data-label="{{ strtolower($item['label']) }}">
                @php
                  $href = '#';
                  if (isset($item['route']) && !empty($item['route'])) {
                    if (strpos($item['route'], '/') === 0) {
                      // Direct URL path
                      $href = $item['route'];
                    } else {
                      // Named route
                      try {
                        if ($item['route'] === 'company.profile_view' && $companyid) {
                          $href = route($item['route'], ['id' => $companyid]);
                        } elseif ($item['route'] === 'pages.update_mine' && Auth::user()->PageId) {
                          $href = route($item['route'], ['id' => Auth::user()->PageId]);
                        } else {
                          $href = route($item['route']);
                        }
                      } catch (Exception $e) {
                        $href = '#';
                      }
                    }
                  }
                @endphp
                <a href="{{ $href }}" class="nav-link d-flex justify-content-start align-items-center text-start p-2 p-md-3">
                  @if (isset($item['icon']))
                    <i class="ti {{ $item['icon'] }} ti-md me-2 flex-shrink-0"></i>
                  @endif
                  <span class="text-truncate">{{ $item['label'] }}</span>
                </a>
              </div>
            @endforeach
          </div>
        </div>
        <div class="w-100 d-block d-md-none">
          <div id="menuGrid" class="nav nav-pills row g-2 g-md-3 mt-3 w-100">
            @foreach($menuItems as $item)
              <div class="col-12 col-sm-6 nav-item" data-label="{{ strtolower($item['label']) }}">
                @php
                  $href = '#';
                  if (isset($item['route']) && !empty($item['route'])) {
                    if (strpos($item['route'], '/') === 0) {
                      // Direct URL path
                      $href = $item['route'];
                    } else {
                      // Named route
                      try {
                        if ($item['route'] === 'company.profile_view' && $companyid) {
                          $href = route($item['route'], ['id' => $companyid]);
                        } elseif ($item['route'] === 'pages.update_mine' && Auth::user()->PageId) {
                          $href = route($item['route'], ['id' => Auth::user()->PageId]);
                        } else {
                          $href = route($item['route']);
                        }
                      } catch (Exception $e) {
                        $href = '#';
                      }
                    }
                  }
                @endphp
                <a href="{{ $href }}" class="nav-link d-flex justify-content-start align-items-center text-start p-2 p-md-3">
                  @if (isset($item['icon']))
                    <i class="ti {{ $item['icon'] }} ti-md me-2 flex-shrink-0"></i>
                  @endif
                  <span class="text-truncate">{{ $item['label'] }}</span>
                </a>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Search functionality
  const searchInput = document.getElementById('menuSearch');
  const menuItems = document.querySelectorAll('#menuGrid .nav-item');
  
  if (searchInput && menuItems.length > 0) {
    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase().trim();
      
      menuItems.forEach(function(item) {
        const label = item.getAttribute('data-label') || '';
        const linkText = item.querySelector('.nav-link')?.textContent?.toLowerCase() || '';
        
        if (searchTerm === '' || label.includes(searchTerm) || linkText.includes(searchTerm)) {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      });
    });
    
    // Clear search when modal is closed
    const searchModal = document.getElementById('searchModal');
    if (searchModal) {
      searchModal.addEventListener('hidden.bs.modal', function() {
        searchInput.value = '';
        menuItems.forEach(function(item) {
          item.style.display = '';
        });
      });
      
      // Focus search input when modal opens
      searchModal.addEventListener('shown.bs.modal', function() {
        searchInput.focus();
      });
    }
  }
  
  // Fix dropdown positioning in RTL
  const dropdowns = document.querySelectorAll('.dropdown-toggle');
  const isRTL = document.documentElement.dir === 'rtl' || document.body.dir === 'rtl';

  if (isRTL) {
    dropdowns.forEach(function(dropdown) {
      dropdown.addEventListener('click', function() {
        setTimeout(function() {
          const dropdownMenu = dropdown.nextElementSibling;
          if (dropdownMenu && dropdownMenu.offsetParent !== null) {
            const rect = dropdownMenu.getBoundingClientRect();
            const menuWidth = dropdownMenu.offsetWidth;
            const screenWidth = window.innerWidth;
            
            // Adjust position if dropdown goes off screen
            if (rect.left < 0) {
              // Off left side - move right
              const overflowLeft = Math.abs(rect.left);
              dropdownMenu.style.transform = 'translateX(' + overflowLeft + 'px)';
            } else if (rect.left + menuWidth > screenWidth) {
              // Off right side - move left
              const overflowRight = rect.left + menuWidth - screenWidth;
              dropdownMenu.style.transform = 'translateX(-' + overflowRight + 'px)';
            } else {
              // Reset if not off screen
              dropdownMenu.style.transform = '';
            }
          }
        }, 50);
      });
    });
  }
  $(document).on('click', '#stop-impersonation', function(e) {
    e.preventDefault();

    releaseAlert('warning', {
      title: "{{ __('admin.Confirm Action') }}",
      message: "{{ __('admin.ConfirmStopImpersonationMessage') }}",
      confirmText: "{{ __('admin.Yes') }}",
      cancelText: "{{ __('admin.cancel') }}",
      onConfirm: function() {
        $("#stop-impersonation-form").submit();
      }
    });
  });
});
</script>
@endpush
