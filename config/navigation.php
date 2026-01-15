<?php
return function () {
  $user = auth()->user();

  if ($user && $user->hasRole('Judger')) {
    return [
      ['title' => 'dashboard', 'url' => 'dashboard'],
      ['title' => 'screening', 'url' => 'dashboard/judger/screening'],
      ['title' => 'file_manager', 'url' => 'dashboard/myFiles'],
    ];
  }

  if ($user && $user->hasRole('Mentor')) {
    return [
      ['title' => 'dashboard', 'url' => 'dashboard'],
      ['title' => 'availability', 'url' => 'dashboard/availability'],
      ['title' => 'startup_hub', 'url' => 'dashboard/companies'],
      ['title' => 'tasks', 'url' => 'dashboard/mentor/mentorshipSessions'],
      ['title' => 'all_tasks', 'url'  => 'dashboard/mentor/mentorshipTasks'],
      ['title' => 'programs', 'url' => 'dashboard/program/show'],
      ['title' => 'mentorship_sessions', 'url' => 'dashboard/mentor/sessions'],
      ['title' => 'surveys', 'url' => 'dashboard/mentor/surveys'],
    ];
  }

  if ($user && $user->hasRole('Registered')) {
    return [
      'dashboard' => [
        'title' => null,
        'children' => [
          ['title' => 'dashboard', 'route' => 'core.dashboard_view', 'menuOnly' => true],
        ]
      ],
      'startup' => [
        'title' => 'startup_workspace',
        'children' => [
          ['title' => 'my_company', 'route' => 'company.profile_view', 'menuOnly' => true],
          ['title' => 'analysis_and_assessment', 'route' => 'assessments.results_analysis_view', 'menuOnly' => true],
          ['title' => 'swot_analysis', 'route' => 'swot.view', 'menuOnly' => true],
          ['title' => 'edit_startup_profile', 'route' => 'company.profile_view_mine', 'group' => 'my_company', 'menuOnly' => false],
        ]
      ],
      'engagement' => [
        'title' => 'engagement',
        'children' => [
          ['title' => 'services_directory', 'route' => 'users.directory_view', 'menuOnly' => true],
          ['title' => 'mentors', 'route' => 'mentors.list', 'menuOnly' => true],
          ['title' => 'hackathons', 'route' => 'hackathon.view', 'menuOnly' => true],
          ['title' => 'perks_and_discounts', 'route' => 'company.perks_view', 'menuOnly' => true],
        ]
      ],
      'services' => [
        'title' => 'services_and_resources',
        'children' => [
          ['title' => 'services_requests', 'route' => 'service_requests.list_mine', 'menuOnly' => true],
          ['title' => 'mentorship_requests', 'route' => 'mentorships.list_mine', 'menuOnly' => true],
        ]
      ],
      'learning' => [
        'title' => 'learning',
        'children' => [
          ['title' => 'my_courses', 'route'  => 'trainings.my_sessions_mine', 'menuOnly' => true],
          ['title' => 'quizzes', 'route'  => 'quizzes.dashboard_view', 'menuOnly' => true],
          ['title' => 'tasks', 'route'  => 'mentorships.sessions_list_detailed', 'menuOnly' => true],
          ['title' => 'all_tasks', 'route'  => 'mentorshipTasks.index', 'menuOnly' => false],
        ]
      ],
      'resources' => [
        'title' => 'resources',
        'children' => [
          ['title' => 'file_manager', 'route' => 'my_files.list', 'menuOnly' => true],
          ['title' => 'documentation', 'route' => 'documentation.index', 'menuOnly' => true],
        ]
      ]
    ];
  }

  return [
    'dashboard' => [
      'title' => 'dashboard_and_analytics',
      'children' => [
        ['title' => 'dashboard', 'route' => 'core.dashboard_view', 'menuOnly' => true],
        ['title' => 'reports', 'route' => 'reports.list', 'menuOnly' => true],
        ['title' => 'activities', 'route' => 'activities.index', 'menuOnly' => true],
        ['title' => 'projects', 'route' => 'projects.index', 'menuOnly' => true],
      ],
    ],
  
    'programs' => [
      'title' => 'program_management',
      'children' => [
        ['title' => 'programs', 'route' => 'programlist.index', 'menuOnly' => true],
        ['title' => 'manage_program_steps', 'route' => 'cycles.list', 'group' => 'programlist', 'group_route' => 'programlist.index', 'menuOnly' => false],
        ['title' => 'new_program', 'route' => 'programlist.create', 'group' => 'programlist', 'menuOnly' => false],
        ['title' => 'edit_program', 'route' => 'programs.edit', 'group' => 'programlist', 'menuOnly' => false],
        ['title' => 'program_details', 'route' => 'programlist.show', 'group' => 'programlist', 'menuOnly' => false],
        ['title' => 'forms', 'route' => 'forms.index', 'menuOnly' => true],
        ['title' => 'form_builder', 'route' => 'forms.builder_view', 'group' => 'forms', 'menuOnly' => false],
        ['title' => 'screening', 'route' => 'screening.index', 'group' => 'screening', 'menuOnly' => true],
        ['title' => 'manage_screening', 'route' => 'screening.manage', 'group' => 'screening', 'menuOnly' => false],
        ['title' => 'final_screening', 'route' => 'screening.final', 'group' => 'screening', 'menuOnly' => false],
        ['title' => 'manage_final_screening', 'route' => 'screening.manage_final', 'group' => 'screening', 'menuOnly' => false],
        ['title' => 'assessments', 'route' => 'assessments.index', 'menuOnly' => true],
        ['title' => 'trainings', 'route' => 'trainings.sessions_list', 'menuOnly' => true],
        ['title' => 'training_courses', 'route' => 'trainings.list', 'group' => 'trainings', 'group_route' => 'trainings.sessions_list', 'menuOnly' => false],
        ['title' => 'training_results', 'route' => 'trainings.results_view', 'group' => 'trainings', 'group_route' => 'trainings.sessions_list', 'menuOnly' => false],
        ['title' => 'mentorship', 'route' => 'mentorships.requests_view', 'menuOnly' => true],
        ['title' => 'manage_mentors', 'route' => 'mentorships.requests_list', 'group' => 'mentorship', 'group_route' => 'mentorships.requests_view', 'menuOnly' => false],
        ['title' => 'mentorship_surveys', 'route' => 'mentorships.surveys_list', 'group' => 'mentorship', 'group_route' => 'mentorships.requests_view', 'menuOnly' => false],
        ['title' => 'mentorship_sessions', 'route' => 'mentorships.sessions_list', 'menuOnly' => true],
        ['title' => 'tasks', 'route' => 'tasks.index', 'menuOnly' => true],
        ['title' => 'questions', 'route' => 'questions-bank.index', 'menuOnly' => true],
        ['title' => 'question_categories', 'route' => 'question-categories.index', 'group' => 'questions', 'group_route' => 'questions-bank.index', 'menuOnly' => false],
        ['title' => 'weights', 'route' => 'weights.index', 'group' => 'questions', 'group_route' => 'questions-bank.index', 'menuOnly' => false],
        ['title' => 'quizzes', 'route' => 'quizzes.index', 'menuOnly' => true],
        ['title' => 'create_quiz', 'route' => 'quizzes.create', 'group' => 'quizzes', 'menuOnly' => false],
        ['title' => 'quizzes_results', 'route' => 'quizzes.quiz_Results_view', 'menuOnly' => false],
        ['title' => 'surveys', 'route' => 'surveys.index', 'group' => 'surveys', 'menuOnly' => true],
        ['title' => 'surveys_results', 'route' => 'surveys.dashboard_count_view', 'group' => 'surveys', 'menuOnly' => false],
        ['title' => 'create_survey', 'route' => 'surveys.create', 'group' => 'surveys', 'menuOnly' => false],
      ],
    ],
  
    'people' => [
      'title' => 'people_and_organizations',
      'children' => [
        ['title' => 'organization_types', 'route' => 'organization-types.index', 'menuOnly' => true],
        ['title' => 'organizations', 'route' => 'organizations.index', 'menuOnly' => true],
        ['title' => 'users', 'route' => 'users.index', 'menuOnly' => true],
        ['title' => 'create_user', 'route' => 'users.create', 'group' => 'users', 'menuOnly' => false],
        ['title' => 'edit_user', 'route' => 'users.edit', 'group' => 'users', 'menuOnly' => false],
        ['title' => 'startups', 'route' => 'users.view_companies', 'menuOnly' => true],
        ['title' => 'startup_profile', 'route' => 'company.profile_view', 'group' => 'startups', 'group_route' => 'users.view_companies', 'menuOnly' => false],
        ['title' => 'edit_startup_profile', 'route' => 'company.profile_view_mine', 'group' => 'startups', 'group_route' => 'users.view_companies', 'menuOnly' => false],
        ['title' => 'judges', 'route' => 'users.judger', 'menuOnly' => true],
        ['title' => 'mentors', 'route' => 'mentors.list', 'menuOnly' => true],
        ['title' => 'invitations', 'route' => 'invitations.index', 'menuOnly' => false],
        ['title' => 'services_providers', 'route' => 'services_providers.index', 'menuOnly' => true],
        ['title' => 'services_providers_requests', 'route' => 'services_providers.requests', 'group' => 'services_providers', 'menuOnly' => false],
      ],
    ],
  
    'services' => [
      'title' => 'services_and_resources',
      'children' => [
        ['title' => 'services', 'route' => 'services.index', 'menuOnly' => true],
        ['title' => 'services_requests', 'route' => 'service_requests.list_all', 'menuOnly' => false],
        ['title' => 'services_categories', 'route' => 'serviceCategories.index', 'menuOnly' => false],
        ['title' => 'create_service_category', 'route' => 'serviceCategories.create', 'menuOnly' => false],
        ['title' => 'edit_service_category', 'route' => 'serviceCategories.edit', 'menuOnly' => false],
        ['title' => 'create_service', 'route' => 'services.create', 'group' => 'services', 'menuOnly' => false],
        ['title' => 'edit_service', 'route' => 'services.edit', 'group' => 'services', 'menuOnly' => false],
        ['title' => 'file_manager', 'route' => 'my_files.list', 'group' => 'services', 'menuOnly' => true],
        ['title' => 'rooms', 'route' => 'rooms.index', 'menuOnly' => true],
        ['title' => 'timesheets', 'route' => 'TimeSheet.index', 'menuOnly' => true],
      ],
    ],
  
    'admin' => [
      'title' => 'system_and_settings',
      'children' => [
        ['title' => 'settings', 'route' => 'settings.index', 'menuOnly' => true],
        ['title' => 'logs_viewer', 'route' => 'audit-logs.index', 'menuOnly' => true],
        ['title' => 'reminder_logs', 'route' => 'organization.ReminderLogs', 'menuOnly' => true],
        ['title' => 'documentations', 'route' => 'documentation.index', 'menuOnly' => true],
        ['title' => 'create_documentation', 'route' => 'documentation.create', 'group' => 'documentation', 'menuOnly' => false],
        ['title' => 'edit_documentation', 'route' => 'documentation.edit', 'group' => 'documentation', 'menuOnly' => false],
      ],
    ],
  ];
};
