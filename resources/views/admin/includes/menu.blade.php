@php
  $setting = \App\Settings::where('id', 1)->first();
  if( $_user->HaveCompany && $_user->company && $_user->company->cycledata && $_user->company->cycledata->organization)
  {
    $_user->organization = $_user->company->cycledata->organization;
  }
@endphp
<!-- Menu -->

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo bg-light" style="height: 70px;">
    <a href="/dashboard" class="app-brand-link">
      @if (!is_null($_user->organization))
        @php
          $imageUrl = $_user->organization->img_url ? asset($_user->organization->img_url) : null;

          // استخراج المسار من الرابط
          $imagePath = parse_url($imageUrl, PHP_URL_PATH);

          // إزالة أي أجزاء غير ضرورية في البداية مثل `/dashboard/uploads/files/`
          $fileN = ltrim(str_replace('/dashboard/uploads/files/', '', $imagePath), '/');
        @endphp
        <img
          src="{{ $imageUrl }}"
          alt="avatar"
          class="object-fit-contain me-2 rounded-circle border p-1 bg-white"
          style="width: 50px; height: 50px;"
        />
      @else
      @if(!empty($setting->Logo))
        <img
          src="/images/logo/{{ $setting->Logo }}" alt="avatar" 
          class="object-fit-contain me-2 rounded-circle border p-1 bg-white"
          style="width: 50px; height: 50px;"
        />
        @else
          <img 
            src="/images/background/logo.png" 
            alt="avatar" 
            class="object-fit-contain me-2 rounded-circle border p-1 bg-white"
            style="width: 50px; height: 50px;"
          />
        @endif
      @endif
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
      <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
    </a>
  </div>

  {{-- <div class="menu-inner-shadow"></div> --}}

  <ul class="menu-inner py-1">
    {{-- <li class="menu-item">
      <a href="/dashboard" class="menu-link">
        {{ is_null($_user->organization_id)? $setting->Platform_Name : $_user->organization->name }}
      </a>
    </li> --}}
    <!-- Dashboards -->
    <li
      class="{{ Request::is('dashboard', 'dashboard/final/*/*', 'dashboard/cycles/view/*', 'dashboard/cycles/my/*', 'dashboard/screening/*/*') ? 'active' : '' }} menu-item">
      <a href="/dashboard" class="menu-link">
        <i class="menu-icon tf-icons ti ti-smart-home"></i>
        <div>{{ __('admin.Dashboard') }}</div>
      </a>
    </li>

    <li class="{{ Request::is('*myProfile') ? 'active' : '' }} menu-item">
      <a href="{{ route('user.profile_view') }}" class="menu-link">
        <i class="ti ti-user me-3 ti-md"></i>
        <div>{{ __('admin.Profile') }}</div>
      </a>
    </li>
    
    @if ($_user->ableTo('slots.index', $permissions))
      <li class="{{ Request::is('*availability') ? 'active' : '' }} menu-item">
        <a href="{{ route('slots.index') }}" class="menu-link">
          <i class="ti ti-calendar-cog me-3 ti-md"></i>
          <div>{{ __('mentor.availability') }}</div>
        </a>
      </li>
    @endif

    @if( $_user->HaveCompany == 1 && !$_user->company && $_user->ableToAny(['company.profile_view', 'assessments.results_analysis_view', 'swot.view'], $permissions) )
      <li class="{{ Request::is('*companyProfile') ? 'active' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <i class="ti ti-building me-3 ti-md"></i>
          <div>{{ __('admin.My company') }}</div>
        </a>
        <ul class="menu-sub">
          @if( $_user->ableTo('company.profile_view', $permissions) )
          <li class="{{ Request::is('dashboard/companyProfile') ? 'active' : '' }} menu-item">
            <a href="{{ route('company.profile_view_mine') }}" class="menu-link">
              <span class="menu-item">
                {{ $l == 'en' ? 'Startup Profile' :  'عرض شركتي' }}
              </span>
            </a>
          </li>
          @endif
          @if($setting->assessment == 1)
          {{-- @if (!app()->environment('production', 'beta')) --}}
          @if( $_user->ableTo('assessments.results_analysis_view', $permissions) )
          <li class="{{ Request::is('dashboard/analysis-assessment') ? 'active' : '' }} menu-item">
            <a href="/dashboard/analysis-assessment" class="menu-link">
              <span class="menu-item">
                {{ $l == 'en' ? 'Analysis & Assessment' : 'تحليل وتقييم' }}
              </span>
            </a>
          </li>
          @endif
          @endif
          @if( in_array(env('APP_ENV', 'local'), ['dev', 'local']) && $_user->ableTo('swot.view', $permissions) )
          <li class="{{ Request::is('dashboard/swot-analysis*') ? 'active' : '' }} menu-item">
            <a href="{{ route('swot.view') }}" class="menu-link">
              <div>{{ __('admin.swot_analysis') }}</div>
            </a>
          </li>
          @endif
        
          {{-- @endif --}}
        </ul>
      </li>
    @endif
    
    @if( $_user->HaveCompany != 0 && $_user->company && $_user->ableToAny(['company.profile_view', 'assessments.results_analysis_view', 'swot.view'], $permissions) )
    <li class="{{ Request::is('*companyProfile*') ? 'active' : '' }} menu-item">
      <a class="menu-link menu-toggle">
        <i class="ti ti-building me-3 ti-md"></i>
        <div>{{ __('admin.My company') }}</div>
      </a>
      <ul class="menu-sub">
        @if( $_user->ableTo('company.profile_view', $permissions) )
        <li class="{{ Request::is('dashboard/companyProfile') ? 'active' : '' }} menu-item">
          <a href="{{ route('company.profile_view', $_user->company->id) }}" class="menu-link">
            <span class="menu-item">
              {{ $l == 'en' ? 'Startup Profile' : ' عرض شركتي' }}
            </span>
          </a>
        </li>
        @endif
        @if($setting->assessment == 1)
        {{-- @if (!app()->environment('production', 'beta')) --}}
        @if( $_user->ableTo('assessments.results_analysis_view', $permissions) )
        <li class="{{ Request::is('dashboard/analysis-assessment') ? 'active' : '' }} menu-item">
          <a href="/dashboard/analysis-assessment" class="menu-link">
            <span class="menu-item">
              {{ $l == 'en' ? 'Analysis & Assessment' : 'تحليل وتقييم' }}
            </span>
          </a>
        </li>
        @endif
        @endif

        @if( in_array(env('APP_ENV', 'local'), ['dev', 'local']) && $_user->ableTo('swot.view', $permissions) )
        <li class="{{ Request::is('dashboard/swot-analysis*') ? 'active' : '' }} menu-item">
          <a href="{{ route('swot.view') }}" class="menu-link">
            <div>{{ __('admin.swot_analysis') }}</div>
          </a>
        </li>
        @endif
        {{-- @endif --}}
      </ul>
    </li>
    @endif

    <li class="{{ Request::is('*calendar*') ? 'active' : '' }} menu-item">
      <a href="{{ route('calendar.events_list') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-calendar"></i>
        <div>{{ __('admin.Calendar') }}</div>
      </a>
      <ul class="menu-sub"></ul>
    </li>

    <?php
    $cmessages = App\Messenger::where('receiver', auth()->id())->whereNull('read');
    //        if( !is_null( $organization_id ) ) { $cmessages = $cmessages->where('organization_id', $organization_id); }
    $unread = $cmessages->count();
    $l == 'ar' ? ($arrow = 'left') : ($arrow = 'right');
    ?>

    @if( $_user->ableTo('messages.list', $permissions) )
      <li class="{{ Request::is('dashboard/messages') ? 'active' : '' }} menu-item">
        <a href="/dashboard/messages" class="menu-link">
          <i class="menu-icon tf-icons ti ti-messages"></i>
          <div>{{ __('admin.Messages') }}</div>
        </a>
      </li>
    @endif

    @if (!$_user->hasAnyRole('Judger'))
      @if( $_user->ableTo('messages.view', $permissions) )
        <li class="{{ Request::is('dashboard/mymsg') ? 'active' : '' }} menu-item">
          <a href="/dashboard/mymsg" class="menu-link">
            <i class="menu-icon tf-icons ti ti-world"></i>
            <div>{{ __('admin.contactus') }}</div>
          </a>
        </li>
      @endif
    @endif

    {{-- @if( $_user->ableToAny(['messages.index'], $permissions) )
    <li class="{{ Request::is('dashboard/inbox', 'dashboard/outbox', 'dashboard/mymsg', 'dashboard/sendmessage', 'dashboard/messages') ? 'sidebar-group-active open' : '' }} menu-item">
      <a class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-messages"></i>
        <div>{{ __('admin.My messages') }}</div>
      </a>
      <ul class="menu-sub">
        @if( $_user->ableTo('messages.index', $permissions) )
          <li class="{{ Request::is('dashboard/messages') ? 'active' : '' }} menu-item">
            <a href="/dashboard/messages" class="menu-link">
              <span class="menu-item">{{ __('admin.Messages') }}</span>
            </a>
          </li>
        @endif
        @if( $_user->ableTo('sendmessage', $permissions) )
          <li class="{{ Request::is('dashboard/sendmessage') ? 'active' : '' }} menu-item"><a
              href="/dashboard/sendmessage" class="menu-link">
              <i class="bx bx-message"></i>
              <span class="menu-item">{{ __('admin.New Message') }}</span>
            </a>
          </li>
        @endif
        @if( $_user->ableTo('messages.folders_inbox', $permissions) )
          <li class="{{ Request::is('dashboard/inbox') ? 'active' : '' }} menu-item"><a href="/dashboard/inbox"
              class="menu-link"><i class="bx bxs-inbox"></i>
              <span class="menu-item">{{ __('admin.myinbox') }}</span>
              @if ($unread > 0)
                <span
                  class="badge badge-pill badge-round badge-dark-info float-right mr-50 ml-auto">{{ $unread }}</span>
              @endif
            </a>
          </li>
        @endif
        @if( $_user->ableTo('messages.folders_outbox', $permissions) )
          <li class="{{ Request::is('dashboard/outbox') ? 'active' : '' }} menu-item">
            <a href="/dashboard/outbox" class="menu-link"><i class="bx bx-mail-send"></i>
              <span class="menu-item">{{ __('admin.myoutbox') }}</span>
            </a>
          </li>
        @endif
        @if( $_user->ableTo('messages.view', $permissions) )
          <li class="{{ Request::is('dashboard/mymsg') ? 'active' : '' }} menu-item">
            <a href="/dashboard/mymsg" class="menu-link"><i class="bx bx-support"></i>
              <span class="menu-item">{{ __('admin.contactus') }}</span>
            </a>
          </li>
        @endif
      </ul>
    </li>
    @endif --}}
    
    @if (!$_user->hasAnyRole('Mentor|Judger'))
      @if( $_user->ableTo('users.directory_view', $permissions) )
      <li class="{{ Request::is('dashboard/directory') ? 'active' : '' }} menu-item"><a href="/dashboard/directory"
          class="menu-link">
          <i class="menu-icon tf-icons ti ti-search"></i>
          <div>{{ __('admin.Services') }}</div>
        </a>
      </li>
      @endif
    @endif
  
    @if ($_user->hasAnyRole('Mentor|Registered|Admin|organizationManager'))
      @if( $_user->ableTo('mentors.list', $permissions) )
      <li class="{{ Request::is('dashboard/show/Mentors') ? 'active' : '' }} menu-item"><a
          href="/dashboard/show/Mentors" class="menu-link">
          <i class="menu-icon tf-icons ti ti-users"></i>
          <div>{{ __('admin.Mentors') }}</div>
        </a>
      </li>
      @endif
    @endif

    @if ($_user->hasAnyRole('Admin|Registered|Mentor|organizationManager'))
    @if( $_user->ableTo('users.view_companies', $permissions) )
     <li class="{{ Request::is('dashboard/companies') ? 'active' : '' }} menu-item">
        <a class="menu-link" href="{{ route('users.view_companies') }}">
          <i class="menu-icon tf-icons ti ti-building-skyscraper"></i>
          <div>{{ __('admin.Companies') }}</div>
        </a>
      </li>
    @endif
    @endif

    @if ($_user->hasAnyRole('Mentor'))
      @if( $_user->ableTo('mentorships.sessions_list_detailed', $permissions) )
      <li 
        class="{{ 
          Request::is(
          'dashboard/mentor/mentorshipSessions*', 'dashboard/mentor/mentorshipTasks*') 
          ? 'active' : '' 
        }} menu-item"
      >
        <a href="{{ route('mentorships.sessions_list_detailed') }}" class="menu-link">
          <i class="menu-icon tf-icons ti ti-layout-kanban"></i>
          <div>{{ __('mentor.mentorshipTasks.index') }}</div>
        </a>
      </li>
      @endif
    @endif
    @if ($_user->hasAnyRole('Registered'))

    <li class="{{ Request::is('dashboard/hackathon') ? 'active' : '' }} menu-item"><a
          href="/dashboard/hackathon" class="menu-link">
          <i class="menu-icon tf-icons ti ti-activity"></i>
          <div>{{ __('admin.Hackathon') }}</div>
        </a>
      </li>
    {{-- @if (!app()->environment('production', 'beta')) --}}
      @if( $_user->ableToAny(['service_requests.list_mine', 'mentorships.list_mine'], $permissions) )
      <li class="{{ Request::is('dashboard/requests/mine', '/dashboard/mentorship/mine') ? 'sidebar-group-active open' : '' }} menu-item">
        <a href="#" class="menu-link menu-toggle">
          <i class="ti ti-file-text  ti-md me-2 me-lg-4 ti-lg"></i>
          <div>{{ __('admin.My Requests') }}</div>
        </a>
        <ul class="menu-sub">
          @if( $_user->ableTo('service_requests.list_mine', $permissions) )
          <li class="{{ Request::is('dashboard/requests/mine') ? 'active' : '' }} menu-item"><a
              href="/dashboard/requests/mine" class="menu-link"><i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Services') }}</span>
            </a>
          </li>
          @endif

          @if( $_user->ableTo('mentorships.list_mine', $permissions) )
          <li class="{{ Request::is('dashboard/mentorship/mine') ? 'active' : '' }} menu-item"><a
              href="/dashboard/mentorship/mine" class="menu-link"><i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Mentorship') }}</span>
            </a>
          </li>
          @endif
        </ul>
        
      </li>
      @endif
    {{-- @endif --}}
      @if( $_user->ableTo('trainings.list_mine', $permissions) )
      <li class="{{ Request::is('dashboard/training/my*') ? 'active' : '' }} menu-item"><a href="/dashboard/training/my"
          class="menu-link">
          <i class="menu-icon tf-icons ti ti-certificate-2"></i>
          <div>{{ __('admin.My courses') }}</div>
        </a>
      </li>
      @endif

      @if( $_user->ableTo('quizzes.dashboard_view', $permissions) )
      <li class="{{ Request::is('dashboard/quizzes/my*') ? 'active' : '' }} menu-item"> <a href="/dashboard/quizzes/my"
          class="menu-link">
          <i class="menu-icon tf-icons ti ti-question-mark"></i>
          <div>{{ __('admin.My Quizzes') }}</div>
        </a>
      </li>
      @endif

      
      @if( $_user->HaveCompany != 0 && $_user->company && $_user->ableTo('mentorships.sessions_list_detailed', $permissions) )
      <li class="{{ Request::is('dashboard/mentor/mentorshipSessions*') ? 'active' : '' }} menu-item">
        <a href="{{ route('mentorships.sessions_list_detailed') }}" class="menu-link">
          <i class="menu-icon tf-icons ti ti-layout-kanban"></i>
          <div>{{ __('mentor.mentorshipTasks.index') }}</div>
        </a>
      </li>
      @endif
    @endif

    @if( $_user->ableTo('my_files.list', $permissions) )
    <li class="{{ Request::is('dashboard/myFiles') ? 'active' : '' }} menu-item">
      <a href="/dashboard/myFiles" class="menu-link">
        <i class="menu-icon tf-icons ti ti-file"></i>
        <div>{{ __('admin.filemanager') }}</div>
      </a>
    </li>
    @endif

    @if($_user->hasRole('Mentor'))
      <li class="{{ Request::is('dashboard/program/show') ? 'active' : '' }} menu-item">
        <a href="/dashboard/program/show" class="menu-link">
          <i class="menu-icon tf-icons ti ti-device-desktop-code"></i>
          <div>{{ __('mentor.enrolled_programs') }}</div>
        </a>
      </li>
    @endif

    @if($_user->hasRole('Judger'))
      <li class="{{ Request::is('dashboard/judger/screening*') ? 'active' : '' }} menu-item">
        <a href="/dashboard/judger/screening" class="menu-link">
          <i class="menu-icon tf-icons ti ti-gavel"></i>
          <div>{{ __('admin.Screening') }}</div>
        </a>
      </li>
    @endif

    @if( $_user->ableToAny(['users.list', 'audit_logs.list', 'screening.form_view', 'reports.list'], $permissions) )
      <li class="menu-header small">
        <span>{{ __('admin.Portal Settings') }}</span>
      </li>
    @endif

    @if ($_user->hasAnyRole('Admin'))
      <li class="{{ Request::is('*organizations') ? 'active' : '' }} menu-item">
        <a href="{{ route('organizations.index') }}" class="menu-link">
          <i class="menu-icon tf-icons ti ti-building"></i>
          <div>{{ __('admin.Manage Organizations') }}</div>
        </a>
      </li>
    
    @endif

    @if( $_user->ableTo('projects.index', $permissions) )
      <li class="{{ Request::is('dashboard/projects*') ? 'active' : '' }} menu-item">
        <a class="menu-link" href="{{ route('projects.index') }}">
          <i class="menu-icon tf-icons ti ti-folder"></i>
           <div>{{ __('admin.Projects') }}</div>
        </a>
     </li>
    @endif
    
    @if( $_user->hasAnyrole('Admin|organizationManager') )
    <li class="{{ Request::is('dashboard/forms*', 'dashboard/show-builder*', 'dashboard/forms-submissions*', 'dashboard/submission-details*') ? 'active' : '' }} menu-item">
      <a href="/dashboard/forms" class="menu-link">
        <i class="menu-icon tf-icons ti ti-forms"></i>
        <div>{{ __('admin.Forms') }}</div>
      </a>
      {{-- <ul class="menu-sub">
        <li class="{{ Request::is('dashboard/forms*') ? 'active' : '' }} menu-item">
          <a href="/dashboard/forms" class="menu-link">
            <div>{{ __('admin.Forms List') }}</div>
          </a>
        </li>
        @if( $_user->ableToAny(['forms.builder_view', 'forms.render_view'], $permissions) )
          <li class="{{ Request::is('dashboard/show-builder*', 'dashboard/show-form*') ? 'active' : '' }} menu-item">
            <a href="/dashboard/show-builder" class="menu-link">
              <div>{{ __('admin.Forms builder') }}</div>
            </a>
          </li>
        @endif
        <li class="{{ Request::is('dashboard/forms-submissions*', 'dashboard/submission-details*') ? 'active' : '' }} menu-item">
          <a href="/dashboard/forms-submissions" class="menu-link">
            <div>{{ __('admin.Forms Submissions') }}</div>
          </a>
        </li>
      </ul> --}}
    </li>
    @endif

    @if( $_user->ableToAny(['cycles.list', 'programlist.index'], $permissions) )
      <li
        class="{{ Request::is('dashboard/cycles*', 'dashboard/programlist*') ? 'active' : '' }} menu-item">
        <a href="{{ route('programlist.index') }}" class="menu-link">
          <i class="menu-icon tf-icons ti ti-components"></i>
          <div>{{ __('admin.Cycles') }}</div>
        </a>
      </li>


      <!-- Start Time Sheets -->
      @if ($_user->ableToAny(['TimeSheet.index', 'time_sheets.reports.finance.view', 'time_sheets.view.mine'], $permissions))
        <li
          class="{{ Request::is('dashboard/TimeSheet*', 'dashboard/UserTimeSheet*', 'dashboard/Category*', 'dashboard/show/FinanceTimeSheet*') ? 'sidebar-group-active open' : '' }} menu-item">
          <a class="menu-link menu-toggle">
            <i class="ti ti-clock-hour-2 me-3 ti-md"></i>
            <div>{{ $l == 'en' ? 'Timesheets' : 'الجداول الزمنية' }}</div>
          </a>
          <ul class="menu-sub">
            @if ($_user->ableToAny(['TimeSheet.index'], $permissions))
              <li class="{{ Request::is('dashboard/TimeSheet*') ? 'active' : '' }} menu-item">
                <a href="{{ route('TimeSheet.index') }}" class="menu-link">
                  <span class="menu-item">{{ $l == 'en' ? 'Timesheets List' : 'قائمة الجداول الزمنية' }}</span>
                  <i class="bx bx-message"></i>
                </a>
              </li>
            @endif
            @if ($_user->ableToAny(['time_sheets.reports.finance.view'], $permissions))
              <li class="{{ Request::is('dashboard/show/FinanceTimeSheet*') ? 'active' : '' }} menu-item">
                <a href="{{ route('time_sheets.reports.finance.view') }}" class="menu-link">
                  <span class="menu-item">{{ __('admin.Financial Report') }}</span>
                  <i class="bx bx-message"></i>
                </a>
              </li>
            @endif
            @if ($_user->ableToAny(['time_sheets.view.mine'], $permissions))
              <li class="{{ Request::is('dashboard/UserTimeSheet*') ? 'active' : '' }} menu-item">
                <a href="{{ route('time_sheets.view.mine') }}" class="menu-link">
                  <span class="menu-item">{{ $l == 'en' ? 'Users TimeSheets' : 'الجدول الزمني للمستخدمين' }}</span>
                  <i class="bx bx-message"></i>
                </a>
              </li>
            @endif
          </ul>
        </li>
      @endif
      <!-- End Time Sheets -->

      
      <!-- Start Assessments -->
      <li
      class="{{ Request::is('dashboard/question-assessment*', 'dashboard/answerOption*', 'dashboard/analysis-assessment*', 'dashboard/history-assessment*', 'dashboard/category-assessment*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
        <i class="ti ti-circle-dashed-check ti-md me-3"></i>
        <div>{{ __('admin.Assessments') }}</div>
        </a>
        
      <ul class="menu-sub">
        {{-- <li class="{{ Request::is('dashboard/questions*') ? 'active' : '' }} menu-item">
          <a href="{{ route('questions.index') }}" class="menu-link">
            <i class="bx bx-message"></i>
            <div>{{ __('admin.AddQuestions') }}</div>
          </a>
        </li>
        <li class="{{ Request::is('dashboard/answerOption*') ? 'active' : '' }} menu-item">
          <a href="{{ route('answerOption.index') }}" class="menu-link">
            <i class="bx bx-message"></i>
            <div>{{ __('admin.AddAnswerOption') }}</div>
          </a>
        </li> --}}


        <li class="{{ Request::is('*assessments') ? 'active' : '' }} menu-item">
          <a href="{{ route('assessments.index') }}" class="menu-link">
            <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
            <span class="menu-item"> {{ __('admin.Manage_Assessments') }}</span>
          </a>
        </li>

        {{-- <li class="{{ Request::is('dashboard/question-assessment*') ? 'active' : '' }} menu-item">
          <a href="{{ route('assessments.take') }}" class="menu-link">
            <span class="menu-item">{{ __('admin.Question_Assessment') }}</span>
            <i class="bx bx-message"></i>
          </a>
        </li>
        <li class="{{ Request::is('dashboard/analysis-assessment*') ? 'active' : '' }} menu-item">
          <a href="{{ route('assessments.results_history_view') }}" class="menu-link">
            <span class="menu-item">{{ __('admin.Analysis_Assessment') }}</span>
            <i class="bx bx-message"></i>
          </a>
        </li>

        <li class="{{ Request::is('dashboard/history-assessment*') ? 'active' : '' }} menu-item">
          <a href="{{ route('assessments.results_history_view') }}" class="menu-link">
            <span class="menu-item">{{ __('admin.History_Assessment') }}</span>
            <i class="bx bx-message"></i>
          </a>
        </li> --}}

      </ul>
      </li>

      <!-- End Assessment -->

      <li class="{{ Request::is('dashboard/screening*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <i class="ti ti-hourglass me-3 ti-md"></i>
          <div>{{ __('admin.Screening') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="{{ Request::is('dashboard/screening', 'dashboard/*/screening') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/screening">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <div>{{ __('admin.Manage') }}</div>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/screening/results') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/screening/results">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <div> {{ __('admin.Results') }}</div>
            </a>
          </li>
           {{-- <li class="{{ Request::is('dashboard/screening/resultsReport') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/screening/resultsReport">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <div> {{ __('admin.resultsReport') }}</div>
            </a>
          </li> --}}
        </ul>
      </li>

      <!-- Mentorship Nav Item -->
      <li
        class="{{ Request::is('dashboard/mentorship/assign*', 'dashboard/mentorship/view*', 'dashboard/mentorship/sessions*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <i class="ti ti-bulb me-3 ti-md"></i>
          <div>{{ __('admin.Mentorship') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="{{ Request::is('dashboard/mentorship/assign*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/mentorship/assign">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Cycles Mentors') }}</span>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/mentorship/view*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/mentorship/view">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Manage') }}</span>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/mentorship/sessions*') ? 'sidebar-group-active open' : '' }} menu-item">
            <a class="menu-link menu-toggle">
              <i class="bx bx-slider"></i>
              <div>{{ __('admin.Sessions') }}</div>
            </a>
            <ul class="menu-sub">
              <li class="{{ request()->show == 'all' ? 'active' : '' }} menu-item">
                <a class="menu-link" href="/dashboard/mentorship/sessions?show=all">
                  <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                  <span class="menu-item">{{ __('cycles.All requests') }}</span>
                </a>
              </li>
              <li class="{{ !request()->show ? 'active' : '' }} menu-item">
                <a class="menu-link" href="/dashboard/mentorship/sessions">
                  <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                  <span class="menu-item">{{ __('cycles.New requests only') }}</span>
                </a>
              </li>
              <li class="{{ request()->show == 'approved' ? 'active' : '' }} menu-item">
                <a class="menu-link" href="/dashboard/mentorship/sessions?show=approved">
                  <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                  <span class="menu-item">{{ __('cycles.approved requests') }}</span>
                </a>
              </li>
              <li
                class="{{ request()->show == 'disapproved' ? 'active' : '' }} menu-item">
                <a class="menu-link" href="/dashboard/mentorship/sessions?show=disapproved">
                  <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                  <span class="menu-item">{{ __('cycles.disapproved requests') }}</span>
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </li>
      <!-- End Mentorship Nav Item -->

      <!-- Questions Nav Item -->
      <li
        class="{{ Request::is('*question-categories*', '*questions-bank*', '*weights*', '*assessments*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <i class="ti ti-question-mark me-3 ti-md"></i>
          <div>{{ __('admin.Questions') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="{{ Request::is('dashboard/question-categories*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/question-categories">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item"> {{ __('admin.Questions Categories') }}</span>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/questions-bank*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/questions-bank">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item"> {{ __('admin.Questions Bank') }}</span>
            </a>
          </li>
        {{-- @if (!app()->environment('production', 'beta')) --}}
          <li class="{{ Request::is('*weights') ? 'active' : '' }} menu-item">
            <a href="{{ route('weights.index') }}" class="menu-link">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item"> {{ __('admin.Weight') }}</span>
            </a>
          </li>
          
          
        {{-- @endif --}}

        </ul>
      </li>
      <!-- End Questions Nav Item -->

      <li class="{{ Request::is('*training*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          {{-- <i class="menu-livicon" data-icon="info-alt"></i> --}}
          <i class="ti ti-presentation me-3 ti-md"></i>
          <div>{{ __('admin.Training') }}</div>
        </a>

        <ul class="menu-sub">
          <li class="{{ Request::is('dashboard/training/sessions') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/training/sessions">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Training sessions') }}</span>
            </a>
          </li>
          
          <li
            class="{{ Request::is('dashboard/training/all*', 'dashboard/training/addcontent*', 'dashboard/training/editcontent*', 'dashboard/training/viewcontent*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/training/all">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Training courses') }}</span>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/training/results*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/training/results">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Results') }}</span>
            </a>
          </li>
        </ul>
      </li>
      <li class="{{ Request::is('*quizzes*', '*surveys*', '*quizResults*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <i class="ti ti-stack me-3 ti-md"></i>
          <div>{{ __('admin.quizzesAndSurveys') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="{{ Request::is('dashboard/quizzes*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/quizzes">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item"> {{ __('admin.quizzes') }}</span>
            </a>
          </li>
           <li class="{{ Request::is('dashboard/quizResults*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="{{ route('quizzes.quiz_Results_view') }}">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item"> {{ __('admin.Quiz_Results') }}</span>
            </a>
          </li>
          
          <li class="{{ Request::is('dashboard/surveys*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/surveys">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item"> {{ __('admin.surveys') }}</span>
            </a>
          </li>

          <li class="{{ Request::is('dashboard/Totalsurveys*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="{{ route('surveys.dashboard_count_view') }}">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item"> {{ __('admin.Survey_Results') }}</span>
            </a>
          </li>

         

        </ul>
      </li>
      <li class="{{ Request::is('dashboard/final/*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <i class="ti ti-trophy me-3 ti-md"></i>
          <div>{{ __('admin.Final Screening') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="{{ Request::is('dashboard/final', 'dashboard/*/final') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/final">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Manage') }}</span>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/final/results*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/final/results">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Results') }}</span>
            </a>
          </li>
        </ul>
      </li>

    @endif

      @if( !$_user->hasAnyRole('Mentor|Judger') )
        <li
          class="{{ Request::is('*services*', '*serviceCategories*', 'dashboard/requests/all', 'dashboard/services_providers*') ? 'sidebar-group-active open' : '' }} menu-item">
          <a class="menu-link menu-toggle">
            <i class="menu-icon tf-icons ti ti-arrows-move"></i>
            <div>{{ __('admin.Services') }}</div>
          </a>
          <ul class="menu-sub">
            @if( $_user->ableTo('services_providers.index', $permissions) )
            <li class="{{ Request::is('dashboard/services_providers*') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('services_providers.index') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.ServiceProviders') }}</span>
              </a>
            </li>
            @endif
            @if( $_user->ableToAny(['service_requests.list_all', 'service_requests.list_archived'], $permissions) )
            <li
              class="{{ Request::is('dashboard/requests/all', 'dashboard/requests/old') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('service_requests.list_all') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.Requests') }}</span>
              </a>
            </li>
            @endif
            @if( $_user->ableTo('services.create', $permissions) )
            <li class="{{ Request::is('dashboard/services/create') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('services.create') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.Add Service') }}</span>
              </a>
            </li>
            @endif
            @if( $_user->ableTo('services.index', $permissions) )
            <li class="{{ Request::is('dashboard/services', 'dashboard/services/*/edit') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('services.index') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.All Services') }}</span>
              </a>
            </li>
            @endif
            @if( $_user->ableTo('serviceCategories.index', $permissions) )
            <li class="{{ Request::is('dashboard/serviceCategories*') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('serviceCategories.index') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.Services Categories') }}</span>
              </a>
            </li>
            @endif
          </ul>
        </li>
      @endif
    
    @if ($_user->hasAnyRole('organizationManager|Admin'))
      <li class="{{ Request::is('*users*', 'dashboard/judger', '*roles', '*activities*', '*audit-logs*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <i class="menu-icon tf-icons ti ti-users"></i>
          <div>{{ __('admin.Users') }}</div>
        </a>
        <ul class="menu-sub">
          @if( $_user->ableToAny(['users.create'], $permissions) )
            <li class="{{ Request::is('dashboard/users/create') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('users.create') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.Add New') }}</span>
              </a>
            </li>
          @endif

          @if( $_user->ableToAny(['users.index'], $permissions) )
            <li class="{{ Request::is('dashboard/users', 'dashboard/users/*/edit') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('users.index') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.List All') }}</span>
              </a>
            </li>
          @endif

          @if( $_user->ableToAny(['users.judger'], $permissions) )
            <li class="{{ Request::is('dashboard/judger') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('users.judger') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.Judgers') }}</span>
              </a>
            </li>
          @endif
          
        
          @if( $_user->ableTo('roles.index', $permissions) )
            <li class="{{ Request::is('*roles') ? 'active' : '' }} menu-item">
              <a href="{{ route('roles.index') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-user-cog"></i>
                <div>{{ __('admin.Roles_and_permissions') }}</div>
              </a>
            </li>
          @endif
          
          @if( $_user->ableTo('audit-logs.index', $permissions) )
            <li class="{{ Request::is('*audit-logs*') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="/dashboard/audit-logs">
                <i class="menu-icon tf-icons ti ti-wood"></i>
                <div>{{ __('admin.Log viewer') }}</div>
              </a>
            </li>
          @endif
          @if( $_user->ableTo('activities.index', $permissions) )
            <li class="{{ Request::is('*activities*') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('activities.index') }}">
                <i class="menu-icon tf-icons ti ti-activity"></i>
                <div>{{ __('admin.Activities') }}</div>
              </a>
            </li>
          @endif
        </ul>
      </li>
     

      @if ($_user->hasAnyRole('organizationManager|Admin'))
        <li class="{{ Request::is('dashboard/room*', '') ? 'active' : '' }} menu-item">
          <a class="menu-link" href="{{ route('rooms.index') }}">
            <i class="menu-icon tf-icons ti ti-air-traffic-control"></i>
            <div>{{ __('admin.Rooms Management') }}</div>
          </a>
        </li>
      @endif

      @if ($_user->hasAnyRole('organizationManager'))
      @if ($_user->ableTo('settings.organization_manage',$permissions))
      <li class="{{ Request::is('dashboard/organizations/settings*') && !Request::is('dashboard/organizations/settings/reminder-logs') ? 'active' : '' }} menu-item">
        <a class="menu-link" href="{{ route('settings.organization_manage') }}">
          <i class="menu-icon tf-icons ti ti-settings"></i>
          <div>{{ __('admin.settings') }}</div>
        </a>
      </li>
      <li class="{{ Request::is('dashboard/organizations/settings/reminder-logs') ? 'active' : '' }} menu-item">
        <a class="menu-link" href="{{ route('organization.ReminderLogs') }}">
          <i class="menu-icon tf-icons ti ti-alarm"></i>
          <div>{{ __('admin.ReminderLogs') }}</div>
        </a>
      </li>
      @if ($_user->ableTo('importer.index'))
            <li class="{{ Request::is('dashboard/importer*') ? 'active' : '' }} menu-item">
                <a href="{{ route('importer.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-file-import"></i>
                    <div>{{ __('admin.Importer Tool') }}</div>
                </a>
            </li>
      @endif
      @endif
      @endif
      @endif

    @if( $_user->ableTo('documentation.index', $permissions) )
    <li class="{{ Request::is('dashboard/documentation*') ? 'active' : '' }} menu-item">
      <a class="menu-link" href="/dashboard/documentation">
        <i class="menu-icon tf-icons ti ti-file-description"></i>
        <div>{{ __('admin.Documentations') }}</div>
      </a>
    </li>
  @endif

    @if( $_user->ableTo('reports.list', $permissions) )
      <li class="{{ Request::is('dashboard/reports*') ? 'active' : '' }} menu-item">
        <a class="menu-link" href="{{ route('reports.list') }}">
          <i class="menu-icon tf-icons ti ti-id"></i>
          <div>{{ __('admin.Reports') }}</div>
        </a>
      </li>
    @endif

    @if( $_user->ableTo('license.activate_view_form', $permissions) )
    <li class="{{ Request::is('dashboard/license/activate*') ? 'active' : '' }} menu-item">
      <a class="menu-link" href="{{ route('license.activate_view_form') }}">
        <i class="menu-icon tf-icons ti ti-key"></i>
        <div>{{ __('admin.License Key') }}</div>
      </a>
    </li>
   @endif

   @if ($_user->hasAnyRole('Admin|organizationManager'))
    <li class="{{ Request::is('dashboard/admin/notification-templates*', 'dashboard/admin/notification-triggers*') ? 'sidebar-group-active open' : '' }} menu-item">
      <a class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-mail"></i>
        <div>{{ __('settings.Notification_Templates') }}</div>
      </a>
      <ul class="menu-sub">
        <li class="{{ Request::is('dashboard/admin/notification-templates/create') ? 'active' : '' }} menu-item">
          <a class="menu-link" href="{{ route('notification-templates.create') }}">
            <i class="bx bx-{{ $arrow ?? 'right' }}-arrow-alt"></i>
            <span class="menu-item">{{ __('settings.Create_Template') }}</span>
          </a>
        </li>
        <li class="{{ Request::is('dashboard/admin/notification-templates', 'dashboard/admin/notification-templates/*/edit') ? 'active' : '' }} menu-item">
          <a class="menu-link" href="{{ route('notification-templates.index') }}">
            <i class="bx bx-{{ $arrow ?? 'right' }}-arrow-alt"></i>
            <span class="menu-item">{{ __('settings.Manage_Templates') }}</span>
          </a>
        </li>

        <li class="{{ Request::is('dashboard/admin/notification-triggers', 'dashboard/admin/notification-triggers/*/edit', 'dashboard/admin/notification-triggers/*') ? 'active' : '' }} menu-item">
          <a class="menu-link" href="{{ route('notification-triggers.index') }}">
            <i class="bx bx-{{ $arrow ?? 'right' }}-arrow-alt"></i>
            <span class="menu-item">{{ __('settings.Manage_Triggers') }}</span>
          </a>
        </li>

      </ul>
    </li>
   @endif

     
  
    @if ($_user->hasAnyRole('Admin'))
      <li class="{{ Request::is('dashboard/settings*') ? 'active' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <i class="menu-icon tf-icons ti ti-settings"></i>
          <div>{{ __('admin.settings') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="{{ Request::is('*Tags') ? 'active' : '' }} menu-item">
            <a href="/dashboard/settings" class="menu-link">
              <i class="menu-livicon" data-icon="globe"></i>
              <div>{{ __('admin.settings') }}</div>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/importer*') ? 'active' : '' }} menu-item">
             <a href="{{ route('importer.index') }}" class="menu-link">
               <i class="menu-icon tf-icons ti ti-upload"></i>
                 <div>{{ __('admin.Importer Tool') }}</div>
             </a>
          </li>
          <li class="{{ Request::is('*currencies') ? 'active' : '' }} menu-item">
            <a href="{{ route('currencies.index') }}" class="menu-link">
              <i class="menu-icon fas fa-money-bill-wave"></i>
                <div>{{ __('admin.Currencies') }}</div>
            </a>
        </li>
          @if (!app()->environment('production', 'beta'))
            <li class="{{ Request::is('*Tags') ? 'active' : '' }} menu-item">
              <a href="{{ route('Tags.index') }}" class="menu-link">
                <i class="menu-livicon" data-icon="globe"></i>
                <div>{{ __('admin.Tags') }}</div>
              </a>
            </li>
            <li class="{{ Request::is('dashboard/Category*') ? 'active' : '' }} menu-item">
              <a href="{{ route('Category.index') }}" class="menu-link">
                <i class="menu-livicon" data-icon="globe"></i>
                <div>{{ __('admin.Categories') }}</div>
              </a>
            </li>
            <li class="{{ Request::is('dashboard/subcategories*') ? 'active' : '' }} menu-item">
              <a href="{{ route('subcategories.index') }}" class="menu-link">
                <i class="menu-livicon" data-icon="globe"></i>
                <div>{{ __('admin.SubCategories') }}</div>
              </a>
            </li>
            <li class="{{ Request::is('*stages') ? 'active' : '' }} menu-item">
              <a href="{{ route('stages.index') }}" class="menu-link">
                <i class="menu-livicon" data-icon="globe"></i>
                <div>{{ __('admin.Stages') }}</div>
              </a>
            </li>
            <li class="{{ Request::is('*Labels') ? 'active' : '' }} menu-item">
              <a href="{{ route('Labels.index') }}" class="menu-link">
                <i class="menu-livicon" data-icon="globe"></i>
                <div>{{ __('admin.Labels') }}</div>
              </a>
            </li>
          @endif
          <li class="{{ Request::is('*queue-jobs') ? 'active' : '' }} menu-item">
            <a href="{{ route('queue.jobs') }}" class="menu-link">
              <i class="menu-livicon" data-icon="globe"></i>
              <div>{{ __('admin.QueueJobs') }}</div>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/industry-settings') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/industry-settings">
              <i class="menu-livicon" data-icon="globe"></i>
              <div>{{ __('admin.Industry_Settings') }}</div>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/expertise-settings') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/expertise-settings">
              <i class="menu-livicon" data-icon="globe"></i>
              <div>{{ __('admin.Expertise_Settings') }}</div>
            </a>
          </li>
        </ul>
      </li>

      @endif
      
    
    @if ($setting->HomePage == true)
      @if ($_user->hasRole('Admin'))
        @if( $_user->ableToAny(['settings.system_general_view', 'settings.system_general_update'], $permissions) )
          <li id="settingMenu" class="menu-item">
            <a class="menu-link menu-toggle">
              <i class="ti ti-home-cog me-3 ti-md" data-icon="wrench"></i>
              <div>{{ __('admin.Homesettings') }}</div>
            </a>
            <ul class="menu-sub">
                @if( $_user->ableToAny(['settings.system_general_view', 'settings.system_general_update'], $permissions) )
                  <li class="{{ Request::is('dashboard/setaboutus*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/setaboutus">
                      <i class="menu-livicon" data-icon="globe"></i>
                      <span>{{ __('admin.About Settings') }}</span>
                    </a>
                  </li>
                  <li class="{{ Request::is('dashboard/setcontactus') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/setcontactus">
                      <i class="menu-livicon" data-icon="paper-plane"></i>
                      <span>{{ __('admin.Contact Settings') }}</span>
                    </a>
                  </li>
                @endif
                @if( $_user->ableTo('menus.index', $permissions) )
                  <li class="{{ Request::is('*menu*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/menus">
                      <i class="menu-livicon" data-icon="morph-menu-arrow-bottom"></i>
                      <span>{{ __('admin.Menusettings') }}</span>
                    </a>
                  </li>
                @endif
                @if( $_user->ableTo('slides.list', $permissions) )
                  <li class="{{ Request::is('*slide*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/slides">
                      <i class="menu-livicon" data-icon="settings"></i>
                      <span>{{ __('admin.Slidersettings') }}</span>
                    </a>
                  </li>
                @endif
                @if( $_user->ableToAny(['pages.list', 'pages.update_mine'], $permissions) )
                  <li class="{{ Request::is('*page*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/pages">
                      <i class="menu-livicon" data-icon="briefcase"></i>
                      <span>{{ __('admin.Pagesettings') }}</span>
                    </a>
                  </li>
                @endif
                @if( $_user->ableTo('posts.list', $permissions) )
                  <li class="{{ Request::is('*post*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/posts">
                      <i class="menu-livicon" data-icon="box-add"></i>
                      <span>{{ __('admin.Postsettings') }}</span>
                    </a>
                  </li>
                @endif
                @if( $_user->ableTo('faqs.index', $permissions) )
                  <li class="{{ Request::is('*faqs*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/faqs">
                      <i class="menu-livicon" data-icon="comments"></i>
                      <span>{{ __('admin.Faqs settings') }}</span>
                    </a>
                  </li>
                @endif
                @if( $_user->ableTo('partners.index', $permissions) )
                  <li class="{{ Request::is('*partners*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/partners">
                      <i class="menu-livicon" data-icon="share"></i>
                      <span>{{ __('admin.partners settings') }}</span>
                    </a>
                  </li>
                @endif
                @if( $_user->ableTo('press.index', $permissions) )
                  <li class="{{ Request::is('*press*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/press">
                      <i class="menu-livicon" data-icon="paper-clip"></i>
                      <span>{{ __('admin.press settings') }}</span>
                    </a>
                  </li>
                @endif
                @if( $_user->ableTo('events.list', $permissions) )
                  <li class="{{ Request::is('*event*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/allevents">
                      <i class="menu-livicon" data-icon="calendar"></i>
                      <span>{{ __('admin.manageevents') }}</span>
                    </a>
                  </li>
                @endif
                @if( $_user->ableTo('pages.update_mine', $permissions) )
                  @if ($_user->PageId)
                    <li class="{{ Request::is('editmypage') ? 'active' : '' }} menu-item">
                      <a class="menu-link" href="/dashboard/editmypage">
                        <i class="menu-livicon" data-icon="briefcase"></i>
                        <span>{{ __('admin.editmypage') }}</span>
                      </a>
                    </li>
                  @endif
                @endif

            </ul>
          </li>
        @endif
      @endif
    @endif
  </ul>
</aside>
<!-- / Menu -->

<script>
  // Scroll to the active menu item
  document.addEventListener('DOMContentLoaded', () => {
    const activeMenuItem = document.querySelector('.menu-item.active');
    
    if (activeMenuItem) {
      activeMenuItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });

  window.onload = () => {
    const settingMenu = document.getElementById('settingMenu');
    // const layoutMenu = document.getElementById('layout-menu');
    
    settingTabs = [
      'setaboutus',
      'setcontactus',
      'menus',
      'slides',
      'pages',
      'posts',
      'faqs',
      'partners',
      'press',
      'allevents',
      'editmypage'
    ];

    // Check if the current page is one of the settings tabs
    settingTabs.forEach(tab => {
      if (window.location.href.includes(tab)) {
        settingMenu.classList.add('open');
      }
    });
  };
</script>