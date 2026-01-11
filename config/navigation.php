<?php
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
      ['title' => 'new_program', 'route' => 'programlist.create', 'group' => 'programlist', 'menuOnly' => false],
      ['title' => 'edit_program', 'route' => 'programs.edit', 'group' => 'programlist', 'menuOnly' => false],
      ['title' => 'program_details', 'route' => 'programlist.show', 'group' => 'programlist', 'menuOnly' => false],
      ['title' => 'forms', 'route' => 'forms.index', 'menuOnly' => true],
      ['title' => 'screening', 'route' => 'screening.index', 'group' => 'screening', 'menuOnly' => true],
      ['title' => 'final_screening', 'route' => 'screening.final', 'group' => 'screening', 'menuOnly' => false],
      ['title' => 'assessments', 'route' => 'assessments.index', 'menuOnly' => true],
      ['title' => 'training', 'route' => 'trainings.sessions_list', 'menuOnly' => true],
      ['title' => 'mentorship', 'route' => 'mentorships.requests_view', 'menuOnly' => true],
      ['title' => 'mentorship_sessions', 'route' => 'mentorships.sessions_list', 'menuOnly' => true],
      ['title' => 'tasks', 'route' => 'tasks.index', 'menuOnly' => true],
      ['title' => 'questions', 'route' => 'question-categories.index', 'menuOnly' => true],
      ['title' => 'quizzes', 'route' => 'quizzes.index', 'menuOnly' => true],
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
      ['title' => 'startups', 'route' => 'users.view_companies', 'menuOnly' => true],
      ['title' => 'judges', 'route' => 'users.judger', 'menuOnly' => true],
      ['title' => 'mentors', 'route' => 'mentors.list', 'menuOnly' => true],
      ['title' => 'service_providers', 'route' => 'services_providers.index', 'menuOnly' => true],
    ],
  ],

  'services' => [
    'title' => 'services_and_resources',
    'children' => [
      ['title' => 'services', 'route' => 'services.index', 'menuOnly' => true],
      ['title' => 'file_manager', 'route' => 'my_files.list', 'menuOnly' => true],
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
    ],
  ],
];