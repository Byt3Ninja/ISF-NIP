<?php
return function () {
  $user = auth()->user();
  $request = request();
  $company_id = isset(auth()->user()->company->id) ? auth()->user()->company->id : $request->id;
  $user_id = $request->id;

  if ($user && $user->hasRole('serviceProvider')) {
    return [
      ['title' => 'dashboard', 'url' => 'dashboard'],
      ['title' => 'my_profile', 'url' => 'dashboard/show/myProfile', 'menuOnly' => false],
      ['title' => 'edit_profile', 'url' => 'dashboard/myProfile', 'menuOnly' => false],
      ['title' => 'profile', 'url' => 'dashboard/show/UserProfile/'.$user_id, 'menuOnly' => false],
    ];
  }
  
  if ($user && $user->hasRole('Judger')) {
    return [
      ['title' => 'dashboard', 'url' => 'dashboard'],
      ['title' => 'screening', 'url' => 'dashboard/judger/screening'],
      ['title' => 'startup_profile', 'url' => 'dashboard/companyProfile/'.$company_id],
      ['title' => 'file_manager', 'url' => 'dashboard/myFiles'],
      ['title' => 'my_profile', 'url' => 'dashboard/show/myProfile', 'menuOnly' => false],
      ['title' => 'edit_profile', 'url' => 'dashboard/myProfile', 'menuOnly' => false],
      ['title' => 'profile', 'url' => 'dashboard/show/UserProfile/'.$user_id, 'menuOnly' => false],
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
      ['title' => 'surveys', 'url' => 'dashboard/surveys'],
      ['title' => 'my_profile', 'route' => 'user.profile_view', 'menuOnly' => false],
      ['title' => 'edit_profile', 'route' => 'user.profile_Edit', 'menuOnly' => false],
      ['title' => 'my_profile', 'url' => 'dashboard/show/myProfile', 'menuOnly' => false],
      ['title' => 'edit_profile', 'url' => 'dashboard/myProfile', 'menuOnly' => false],
      ['title' => 'profile', 'url' => 'dashboard/show/UserProfile/'.$user_id, 'menuOnly' => false],
    ];
  }

  if( $user && $user->hasRole('Registered') )
  {
    $show = $user->HaveCompany == 1;
    
    $menuReg = [
      'dashboard' => [
        'title' => null,
        'children' => [
          ['title' => 'dashboard', 'route' => 'core.dashboard_view', 'menuOnly' => true],
          ['title' => 'my_profile', 'route' => 'user.profile_view', 'hide_parent' => true, 'menuOnly' => false],
          ['title' => 'edit_profile', 'route' => 'user.profile_Edit', 'hide_parent' => true, 'menuOnly' => false],
          ['title' => 'profile', 'route' => 'mentors.profile_view', 'hide_parent' => true, 'menuOnly' => false],
        ]
      ],
      'startup' => [
        'title' => 'startup_workspace',
        'children' => [
          ['title' => 'my_company', 'route' => 'company.profile_view', 'menuOnly' => $show],
          ['title' => 'analysis_and_assessment', 'route' => 'assessments.results_analysis_view', 'menuOnly' => true],
          ['title' => 'questions_assessment', 'route' => 'assessments.take', 'menuOnly' => false],
          ['title' => 'history_assessment', 'route' => 'assessments.results_history_view', 'menuOnly' => false],
          ['title' => 'swot_analysis', 'route' => 'swot.view', 'menuOnly' => true],
          ['title' => 'edit_startup_profile', 'route' => 'company.profile_view_mine', 'group' => 'my_company', 'menuOnly' => false],
        ]
      ],
      'engagement' => [
        'title' => 'engagement',
        'children' => [
          ['title' => 'services_directory', 'route' => 'services.index', 'menuOnly' => true],
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
          ['title' => 'course_details', 'route'  => 'trainings.list_mine', 'group' => 'my_courses', 'menuOnly' => false],
          ['title' => 'quizzes', 'route'  => 'quizzes.dashboard_view', 'menuOnly' => true],
          ['title' => 'surveys', 'route'  => 'surveys.index', 'menuOnly' => true],
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
    return $menuReg;
  }

  $menu = [
    'dashboard' => [
      'title' => 'dashboard_and_analytics',
      'children' => [
        ['title' => 'dashboard', 'route' => 'core.dashboard_view', 'menuOnly' => true],
        
        ['title' => 'activities', 'route' => 'activities.index', 'menuOnly' => true],
        ['title' => 'projects', 'route' => 'projects.index', 'menuOnly' => true],
        ['title' => 'my_profile', 'route' => 'user.profile_view', 'hide_parent' => true, 'menuOnly' => false],
        ['title' => 'edit_profile', 'route' => 'user.profile_Edit', 'hide_parent' => true, 'menuOnly' => false],
        ['title' => 'profile', 'route' => 'mentors.profile_view', 'hide_parent' => true, 'menuOnly' => false],
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
        ['title' => 'screening_results', 'route' => 'screening.results', 'group' => 'screening', 'menuOnly' => false],
        ['title' => 'assessments', 'route' => 'assessments.index', 'menuOnly' => true],
        ['title' => 'trainings', 'route' => 'trainings.sessions_list', 'menuOnly' => true],
        ['title' => 'training_courses', 'route' => 'trainings.list', 'group' => 'trainings', 'group_route' => 'trainings.sessions_list', 'menuOnly' => false],
        ['title' => 'course_details', 'route' => 'trainings.course_view', 'group' => 'training_courses', 'group_route' => 'trainings.list', 'menuOnly' => false],
        ['title' => 'edit_course', 'route' => 'trainings.records_edit', 'group' => 'training_courses', 'group_route' => 'trainings.list', 'menuOnly' => false],
        ['title' => 'add_content', 'route'  => 'trainings.content_create', 'group' => 'course_details', 'group_route' => 'trainings.course_view', 'menuOnly' => false],
        ['title' => 'edit_content', 'route'  => 'trainings.content_edit', 'group' => 'course_details', 'group_route' => 'trainings.course_view', 'menuOnly' => false],
        ['title' => 'training_results', 'route' => 'trainings.results_view', 'group' => 'trainings', 'group_route' => 'trainings.sessions_list', 'menuOnly' => false],
        ['title' => 'mentorship', 'route' => 'mentorships.requests_view', 'menuOnly' => true],
        ['title' => 'manage_mentors', 'route' => 'mentorships.requests_list', 'group' => 'mentorship', 'group_route' => 'mentorships.requests_view', 'menuOnly' => false],
        ['title' => 'mentorship_surveys', 'route' => 'mentorships.surveys_list', 'group' => 'mentorship', 'group_route' => 'mentorships.requests_view', 'menuOnly' => false],
        ['title' => 'mentorship_sessions', 'route' => 'mentorships.sessions_list', 'menuOnly' => true],
        ['title' => 'tasks', 'route' => 'tasks.index', 'menuOnly' => true],
        ['title' => 'questions', 'route' => 'questions-bank.index', 'menuOnly' => true],
        ['title' => 'create_question', 'route' => 'questions-bank.create', 'menuOnly' => false],
        ['title' => 'edit_question', 'route' => 'questions-bank.edit', 'menuOnly' => false],
        ['title' => 'question_categories', 'route' => 'question-categories.index', 'group' => 'questions', 'group_route' => 'questions-bank.index', 'menuOnly' => false],
        ['title' => 'create_question_category', 'route' => 'question-categories.create', 'group' => 'questions', 'group_route' => 'questions-bank.index', 'menuOnly' => false],
        ['title' => 'edit_question_category', 'route' => 'question-categories.edit', 'group' => 'questions', 'group_route' => 'questions-bank.index', 'menuOnly' => false],
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
        ['title' => 'organizations', 'route' => 'organizations.index', 'menuOnly' => true],
        ['title' => 'create_organization', 'route' => 'organization.wizard.create', 'menuOnly' => false],
        ['title' => 'edit_organization', 'route' => 'organization.wizard.edit', 'menuOnly' => false],
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
        ['title' => 'view_profile', 'route' => 'service_provider.profile', 'menuOnly' => false],
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
        // ['title' => 'rooms', 'route' => 'rooms.index', 'menuOnly' => true],
        
      ],
    ],
  
    'admin' => [
      'title' => 'system_and_settings',
      'children' => [
        ['title' => 'settings', 'route' => 'settings.index', 'menuOnly' => true],
        ['title' => 'logs_viewer', 'route' => 'audit-logs.index', 'menuOnly' => true],
        ['title' => 'reminder_logs', 'route' => 'organization.ReminderLogs', 'menuOnly' => true],
        ['title' => 'documentations', 'route' => 'documentation.index', 'menuOnly' => true],
        // ['title' => 'custom_landing_pages', 'route' => 'landing-pages.index', 'menuOnly' => true],
        ['title' => 'create_documentation', 'route' => 'documentation.create', 'group' => 'documentation', 'menuOnly' => false],
        ['title' => 'edit_documentation', 'route' => 'documentation.edit', 'group' => 'documentation', 'menuOnly' => false],

        ['title' => 'roles', 'route' => 'roles.index', 'menuOnly' => false],
        ['title' => 'create_role', 'route' => 'roles.create', 'group' => 'roles', 'menuOnly' => false],
        ['title' => 'edit_role', 'route' => 'roles.edit', 'group' => 'roles', 'menuOnly' => false],
        ['title' => 'permissions', 'route' => 'permissions.index', 'menuOnly' => false],
        ['title' => 'permission_groups', 'route' => 'permission-groups.index', 'menuOnly' => false],
        ['title' => 'create_permission_group', 'route' => 'permission-groups.create', 'group' => 'permission_groups', 'menuOnly' => false],
        ['title' => 'edit_permission_group', 'route' => 'permission-groups.edit', 'group' => 'permission_groups', 'menuOnly' => false],
        ['title' => 'create_permission', 'route' => 'permissions.create', 'group' => 'permissions', 'menuOnly' => false],
        ['title' => 'edit_permission', 'route' => 'permissions.edit', 'group' => 'permissions', 'menuOnly' => false],
        ['title' => 'importer_tool', 'route' => 'importer.index', 'menuOnly' => false],
        ['title' => 'currencies', 'route' => 'currencies.index', 'menuOnly' => false],
        ['title' => 'tags', 'route' => 'Tags.index', 'menuOnly' => false],
        ['title' => 'categories', 'route' => 'Category.index', 'menuOnly' => false],
        ['title' => 'subcategories', 'route' => 'subcategories.index', 'menuOnly' => false],
        ['title' => 'stages', 'route' => 'stages.index', 'menuOnly' => false],
        ['title' => 'labels', 'route' => 'Labels.index', 'menuOnly' => false],
        ['title' => 'queue_jobs', 'route' => 'queue.jobs', 'menuOnly' => false],
        ['title' => 'industry', 'route' => 'settings.industry_view', 'menuOnly' => false],
        ['title' => 'expertise', 'route' => 'settings.expertise_view', 'menuOnly' => false],
        ['title' => 'notification_templates', 'route' => 'notification-templates.index', 'menuOnly' => false],
        ['title' => 'notification_triggers', 'route' => 'notification-triggers.index', 'menuOnly' => false],
        ['title' => 'license_key', 'route' => 'license.activate_view_form']
      ],
    ],
  ];
  if (app()->environment(['local', 'dev', 'test', 'beta'])) {
      $menu['services']['children'][] = [
        'title' => 'timesheets',
        'route' => 'TimeSheet.index',
        'menuOnly' => true,
      ];
      $menu['dashboard']['children'][] = ['title' => 'reports', 'route' => 'reports.list', 'menuOnly' => true];
    }
    if (app()->environment(['local', 'dev', 'test']))
    {
      $children = $menu['admin']['children'];
      $customLandingPage = ['title' => 'custom_landing_pages', 'route' => 'landing-pages.index', 'menuOnly' => true];
      $menu['admin']['children'] = array_merge(array_slice($children, 0, 1), [$customLandingPage], array_slice($children, 1));
    }
    if(auth()->check() && auth()->user()->hasRole('Admin')) {
      $menu['admin']['children'][] = ['title' => 'general', 'route' => 'settings.system_general_view' , 'menuOnly' => false];
    } else {
      $menu['admin']['children'][] = ['title' => 'general', 'route' => 'settings.system_general_view', 'menuOnly' => false];
    }
    
  return $menu;
};
