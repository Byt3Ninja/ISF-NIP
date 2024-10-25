@php $setting = \App\Settings::where('id', 1)->first(); @endphp
<style>
  /* Add this to your CSS file */
.logo-img {
    max-width: 50%;
    height: auto;
    display: block;
}
</style>
<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo bg-light" style="height: 70px;">
    <a href="/dashboard" class="app-brand-link">
      @if (!is_null(auth()->user()->organization))
          <img src="{{ auth()->user()->organization->image_url }}" alt="avatar" class="logo-img">
      @else
          <img src="/images/logo/{{ $setting->Logo }}" alt="avatar" class="logo-img">
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
        {{ is_null(auth()->user()->organization_id)? $setting->Platform_Name : auth()->user()->organization->name }}
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
      <a href="{{ route('show_my_profile') }}" class="menu-link">
        <i class="ti ti-user me-3 ti-md"></i>
        <div>{{ __('admin.Profile') }}</div>
      </a>
    </li>



    @if (auth()->user()->HaveCompany == 1 && !auth()->user()->company)
      <li class="{{ Request::is('*companyProfile') ? 'active' : '' }} menu-item">
        <a href="{{ route('my_company') }}" class="menu-link">
          <i class="ti ti-building me-3 ti-md"></i>
          <div>{{ __('admin.My company') }}</div>
        </a>
      </li>
    @endif
      {{-- @if (auth()->user()->company)
      <li class="{{ Request::is('*companyProfile*') ? 'active' : '' }} menu-item">
        <a href="{{ route('company_profile', auth()->user()->company->id) }}" class="menu-link">
          <i class="ti ti-building me-3 ti-md"></i>
          <div>{{ __('admin.My company') }}</div>
        </a>
      </li>
      @endif --}}

    <li class="{{ Request::is('*calendar*') ? 'active' : '' }} menu-item">
      <a href="{{ route('calendar') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-calendar"></i>
        <div>{{ __('admin.Calendar') }}</div>
      </a>
    </li>

    <?php
    $cmessages = App\Messenger::where('receiver', auth()->id())->whereNull('read');
    //        if( !is_null( $organization_id ) ) { $cmessages = $cmessages->where('organization_id', $organization_id); }
    $unread = $cmessages->count();
    $l == 'ar' ? ($arrow = 'left') : ($arrow = 'right');
    ?>

    <li
      class="{{ Request::is('dashboard/inbox', 'dashboard/outbox', 'dashboard/mymsg', 'dashboard/sendmessage') ? 'sidebar-group-active open' : '' }} menu-item">
      <a class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-messages"></i>
        <div>{{ __('admin.My messages') }}</div>
      </a>
      <ul class="menu-sub">
        <li class="{{ Request::is('dashboard/sendmessage') ? 'active' : '' }} menu-item"><a
            href="/dashboard/sendmessage" class="menu-link">
            <i class="bx bx-message"></i>
            <span class="menu-item">{{ __('admin.New Message') }}</span>
          </a>
        </li>
        <li class="{{ Request::is('dashboard/inbox') ? 'active' : '' }} menu-item"><a href="/dashboard/inbox"
            class="menu-link"><i class="bx bxs-inbox"></i>
            <span class="menu-item">{{ __('admin.myinbox') }}</span>
            @if ($unread > 0)
              <span
                class="badge badge-pill badge-round badge-dark-info float-right mr-50 ml-auto">{{ $unread }}</span>
            @endif
          </a>
        </li>
        <li class="{{ Request::is('dashboard/outbox') ? 'active' : '' }} menu-item">
          <a href="/dashboard/outbox" class="menu-link"><i class="bx bx-mail-send"></i>
            <span class="menu-item">{{ __('admin.myoutbox') }}</span>
          </a>
        </li>
        @can('view_messages')
          <li class="{{ Request::is('dashboard/mymsg') ? 'active' : '' }} menu-item">
            <a href="/dashboard/mymsg" class="menu-link"><i class="bx bx-support"></i>
              <span class="menu-item">{{ __('admin.contactus') }}</span>
            </a>
          </li>
        @endcan
      </ul>
    </li>
    @if (!Auth::user()->hasanyRole('Mentor|Judger'))
      <li class="{{ Request::is('dashboard/directory') ? 'active' : '' }} menu-item"><a href="/dashboard/directory"
          class="menu-link">
          <i class="ti ti-search ti-md me-2 me-lg-4 ti-lg"></i>
          <div>{{ __('admin.Services') }}</div>
        </a>
      </li>
    @endif
    @if (Auth::user()->hasanyRole('Mentor|Registered|Admin'))
      <li class="{{ Request::is('dashboard/show/Mentors') ? 'active' : '' }} menu-item"><a
          href="/dashboard/show/Mentors" class="menu-link">
          <i class="ti ti-users ti-md me-2 me-lg-4 ti-lg"></i>
          <div>{{ __('admin.Mentors') }}</div>
        </a>
      </li>
    @endif
    @role('Mentor')
      <li class="{{ Request::is('dashboard/mentor/mentorshipSessions*') ? 'active' : '' }} menu-item">
        <a href="{{ route('mentorshipSessions.index') }}" class="menu-link">
          <i class="menu-livicon" data-icon="briefcase"></i>
          <div>{{ __('mentor.mentorshipTasks.index') }}</div>
        </a>
      </li>
    @endrole
    @role('Registered')
      <li
        class="{{ Request::is('dashboard/requests/mine', '/dashboard/mentorship/mine') ? 'sidebar-group-active open' : '' }} menu-item">
        <a href="#" class="menu-link menu-toggle">
          <i class="ti ti-file-text  ti-md me-2 me-lg-4 ti-lg"></i>
          <div>{{ __('admin.My Requests') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="{{ Request::is('dashboard/requests/mine') ? 'active' : '' }} menu-item"><a
              href="/dashboard/requests/mine" class="menu-link"><i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Services') }}</span>
            </a>
          </li>

          <li class="{{ Request::is('dashboard/mentorship/mine') ? 'active' : '' }} menu-item"><a
              href="/dashboard/mentorship/mine" class="menu-link"><i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Mentorship') }}</span>
            </a>
          </li>
        </ul>
      </li>

      <li class="{{ Request::is('dashboard/training/my*') ? 'active' : '' }} menu-item"><a href="/dashboard/training/my"
          class="menu-link">
          <i class="menu-icon tf-icons ti ti-forms"></i>
          <div>{{ __('admin.My courses') }}</div>
        </a>
      </li>
      <li class="{{ Request::is('dashboard/quizzes/my*') ? 'active' : '' }} menu-item"> <a href="/dashboard/quizzes/my"
          class="menu-link">
          <i class="menu-icon tf-icons ti ti-question-mark"></i>
          <div>{{ __('admin.My Quizzes') }}</div>
        </a>
      </li>
      <li class="{{ Request::is('dashboard/startup/mentorshipTasks*') ? 'active' : '' }} menu-item">
        <a href="{{ route('startup.mentorshipTasks.home') }}" class="menu-link">
          <i class="menu-icon tf-icons ti ti-checklist"></i>
          <div>{{ __('mentor.mentorshipTasks.index') }}</div>
        </a>
      </li>
    @endrole
    <li class="{{ Request::is('dashboard/myFiles') ? 'active' : '' }} menu-item">
      <a href="/dashboard/myFiles" class="menu-link">
        <i class="menu-icon tf-icons ti ti-file"></i>
        <div>{{ __('admin.filemanager') }}</div>
      </a>
    </li>

    @canany(['view_user', 'view_log', 'view_forms', 'view_report'])
      <li class="menu-header small">
        <span>{{ __('admin.Portal Settings') }}</span>
      </li>
    @endcanany

    @if (auth()->user()->hasRole('Admin'))
      <li class="{{ Request::is('*organizations') ? 'active' : '' }} menu-item">
        <a href="{{ route('organizations.index') }}" class="menu-link">
          <i class="menu-icon tf-icons ti ti-settings"></i>
          <div>{{ __('admin.Manage Organizations') }}</div>
        </a>
      </li>

      <li class="{{ Request::is('*Tags') ? 'active' : '' }} menu-item">
        <a href="{{ route('Tags.index') }}" class="menu-link">
          <i class="ti ti-flag me-3 ti-md"></i>
          <div>{{ __('admin.Tags') }}</div>
        </a>
      </li>
    @endif

    @can('edit_forms')
      <li class="{{ Request::is('dashboard/show-builder*', 'dashboard/show-form*') ? 'active' : '' }} menu-item">
        <a href="/dashboard/show-builder" class="menu-link">
          <i class="menu-icon tf-icons ti ti-layout-grid"></i>
          <div>{{ __('admin.Forms builder') }}</div>
        </a>
      </li>
    @endcan

    @can('edit_cycles')
      <li
        class="{{ Request::is('dashboard/cycles*', 'dashboard/programlist*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <i class="menu-icon tf-icons ti ti-components"></i>
          <div>{{ __('admin.Cycles') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="{{ Request::is('dashboard/programlist*') ? 'active' : '' }} menu-item">
            <a href="/dashboard/programlist" class="menu-link">
              <span class="menu-item">{{ __('admin.ProgramList') }}</span>
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/cycles*') ? 'active' : '' }} menu-item">
            <a href="/dashboard/cycles" class="menu-link">
              <span class="menu-item">{{ __('cycles.steps') }}</span>
              <i class="bx bx-message"></i>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/cycles/*/registered') ? 'active' : '' }} menu-item">
            <a href="/dashboard/cycles/0/registered" class="menu-link">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item"> {{ __('admin.Startups') }}</span>
            </a>
          </li>
        </ul>
      </li>


      <!-- Start Time Sheets -->
      <li
        class="{{ Request::is('dashboard/TimeSheet*', 'dashboard/UserTimeSheet*', 'dashboard/Category*', 'dashboard/show/FinanceTimeSheet*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <i class="ti ti-clock-hour-2 me-3 ti-md"></i>
          <div>{{ __('admin.TimeSheet') }}</div>
        </a>
        <ul class="menu-sub">
          <li class="{{ Request::is('dashboard/TimeSheet*') ? 'active' : '' }} menu-item">
            <a href="{{ route('TimeSheet.index') }}" class="menu-link">
              <span class="menu-item">{{ __('admin.TimeSheet') }}</span>
              <i class="bx bx-message"></i>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/show/FinanceTimeSheet*') ? 'active' : '' }} menu-item">
            <a href="{{ route('show_finance_time_sheet') }}" class="menu-link">
              <span class="menu-item">{{ __('admin.Financial Report') }}</span>
              <i class="bx bx-message"></i>
            </a>
          </li>

          <li class="{{ Request::is('dashboard/UserTimeSheet*') ? 'active' : '' }} menu-item">
            <a href="{{ route('UserTimeSheet') }}" class="menu-link">
              <span class="menu-item">{{ __('admin.UserTimeSheet') }}</span>
              <i class="bx bx-message"></i>
            </a>
          </li>

          <li class="{{ Request::is('dashboard/Category*') ? 'active' : '' }} menu-item">
            <a href="{{ route('Category.index') }}" class="menu-link">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item"> {{ __('admin.Category') }}</span>
            </a>
          </li>
        </ul>
      </li>

      <!-- End Time Sheets -->





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
          <li class="{{ Request::is('dashboard/screening/results*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/screening/results">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <div> {{ __('admin.Results') }}</div>
            </a>
          </li>
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
        class="{{ Request::is('*question-categories*', '*questions-bank*') ? 'sidebar-group-active open' : '' }} menu-item">
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
        </ul>
      </li>
      <!-- End Questions Nav Item -->

      <li class="{{ Request::is('*training*', '*quizzes*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          {{-- <i class="menu-livicon" data-icon="info-alt"></i> --}}
          <i class="ti ti-presentation me-3 ti-md"></i>
          <div>{{ __('admin.Training') }}</div>
        </a>

        <ul class="menu-sub">
          <li class="{{ Request::is('dashboard/training/sessions*') ? 'active' : '' }} menu-item">
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
          <li class="{{ Request::is('dashboard/quizzes*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/quizzes">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item"> {{ __('admin.quizzes') }}</span>
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

      @if (!Auth::user()->hasanyRole('organizationManager|Mentor|Judger'))
        <li
          class="{{ Request::is('*services*', '', 'dashboard/requests/all', 'dashboard/room*') ? 'sidebar-group-active open' : '' }} menu-item">
          <a class="menu-link menu-toggle">
            <i class="menu-icon tf-icons ti ti-components"></i>
            <div>{{ __('admin.Services') }}</div>
          </a>
          <ul class="menu-sub">
            <li
              class="{{ Request::is('dashboard/requests/all', 'dashboard/requests/old') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('requests') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.Requests') }}</span>
              </a>
            </li>
            <li class="{{ Request::is('dashboard/services/create') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('services.create') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.Add Service') }}</span>
              </a>
            </li>
            <li class="{{ Request::is('dashboard/services', 'dashboard/services/*/edit') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('services.index') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.All Services') }}</span>
              </a>
            </li>
            <!--
                                      <li class="{{ Request::is('dashboard/room*', '') ? 'active' : '' }} menu-item">
                                        <a href="{{ route('rooms.index') }}">
                                          <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                                          <span class="menu-item">{{ __('admin.Rooms Management') }}</span>
                                        </a>
                                      </li>
                                    -->
            <li class="{{ Request::is('dashboard/serviceCategories*') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('serviceCategories.index') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.Services Categories') }}</span>
              </a>
            </li>
          </ul>
        </li>
      @endif
    @endcan
    @if (!Auth::user()->hasanyRole('organizationManager'))
      @can('view_user')
        <li class="{{ Request::is('*users*') ? 'sidebar-group-active open' : '' }} menu-item">
          <a class="menu-link menu-toggle">
            <i class="menu-icon tf-icons ti ti-users"></i>
            <div>{{ __('admin.Users') }}</div>
          </a>
          <ul class="menu-sub">
            <li class="{{ Request::is('dashboard/users/create') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('users.create') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.Add New') }}</span>
              </a>
            </li>
            <li class="{{ Request::is('dashboard/users', 'dashboard/users/*/edit') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('users.index') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.List All') }}</span>
              </a>
            </li>
            <li class="{{ Request::is('dashboard/companies') ? 'active' : '' }} menu-item">
              <a class="menu-link" href="{{ route('users.companies') }}">
                <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                <span class="menu-item">{{ __('admin.Companies') }}</span>
              </a>
            </li>
            @can('view_permissions')
              {{-- <li class="{{ Request::is('dashboard/permissions*') ? 'active' : '' }} menu-item">
            <a href="{{ route('permissions.index') }}">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Permissions') }}</span>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/roles*') ? 'active' : '' }} menu-item">
            <a href="{{ route('roles.index') }}">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Roles') }}</span>
            </a>
          </li> --}}
            @endcan
          </ul>
        </li>
      @endcan
      @can('view_documentation')
        <li class="{{ Request::is('dashboard/documentation*') ? 'active' : '' }} menu-item">
          <a class="menu-link" href="/dashboard/documentation">
            <i class="menu-icon tf-icons ti ti-file-description"></i>
            <div>{{ __('admin.Documentations') }}</div>
          </a>
        </li>
      @endcan
      {{-- @can('view_log')
    <li class="{{ Request::is('*audit-logs*') ? 'active' : '' }} menu-item">
      <a href="/dashboard/audit-logs">
        <i class="menu-livicon" data-icon="lab"></i>
        <div>{{ __('admin.Log viewer') }}</div>
      </a>
    </li>
  @endcan --}}
    @endif

    @can('view_report')
      <li class="{{ Request::is('dashboard/reports*') ? 'active' : '' }} menu-item">
        <a class="menu-link" href="{{ route('reports.index') }}">
          <i class="menu-icon tf-icons ti ti-id"></i>
          <div>{{ __('admin.Reports') }}</div>
        </a>
      </li>
    @endcan
  
    @can('edit_settings')
      <li class="{{ Request::is('dashboard/settings*') ? 'active' : '' }} menu-item">
        <a class="menu-link" href="/dashboard/settings">
          <i class="menu-icon tf-icons ti ti-settings"></i>
          <div>{{ __('admin.settings') }}</div>
        </a>
      </li>
    @endcan

    @php
      $updateSittings = App\Settings::where('id', 1)->first();
    @endphp
    @if ($updateSittings->HomePage == true)
      @if (auth()->user()->hasRole('Admin'))
        @can('edit_settings')
          <li id="settingMenu" class="menu-item">
            <a class="menu-link menu-toggle">
              <i class="menu-livicon" data-icon="wrench"></i>
              <div>{{ __('admin.Homesettings') }}</div>
            </a>
            <ul class="menu-sub">
                @can('edit_settings')
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
                @endcan
                @can('manage_menus')
                  <li class="{{ Request::is('*menu*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/menus">
                      <i class="menu-livicon" data-icon="morph-menu-arrow-bottom"></i>
                      <span>{{ __('admin.Menusettings') }}</span>
                    </a>
                  </li>
                @endcan
                @can('view_slide')
                  <li class="{{ Request::is('*slide*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/slides">
                      <i class="menu-livicon" data-icon="settings"></i>
                      <span>{{ __('admin.Slidersettings') }}</span>
                    </a>
                  </li>
                @endcan
                @can('manage_pages')
                  <li class="{{ Request::is('*page*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/pages">
                      <i class="menu-livicon" data-icon="briefcase"></i>
                      <span>{{ __('admin.Pagesettings') }}</span>
                    </a>
                  </li>
                @endcan
                @can('manage_posts')
                  <li class="{{ Request::is('*post*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/posts">
                      <i class="menu-livicon" data-icon="box-add"></i>
                      <span>{{ __('admin.Postsettings') }}</span>
                    </a>
                  </li>
                @endcan
                @can('view_faqs')
                  <li class="{{ Request::is('*faqs*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/faqs">
                      <i class="menu-livicon" data-icon="comments"></i>
                      <span>{{ __('admin.Faqs settings') }}</span>
                    </a>
                  </li>
                @endcan
                @can('view_partner')
                  <li class="{{ Request::is('*partners*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/partners">
                      <i class="menu-livicon" data-icon="share"></i>
                      <span>{{ __('admin.partners settings') }}</span>
                    </a>
                  </li>
                @endcan
                @can('view_press')
                  <li class="{{ Request::is('*press*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/press">
                      <i class="menu-livicon" data-icon="paper-clip"></i>
                      <span>{{ __('admin.press settings') }}</span>
                    </a>
                  </li>
                @endcan
                @can('manage_events')
                  <li class="{{ Request::is('*event*') ? 'active' : '' }} menu-item">
                    <a class="menu-link" href="/dashboard/allevents">
                      <i class="menu-livicon" data-icon="calendar"></i>
                      <span>{{ __('admin.manageevents') }}</span>
                    </a>
                  </li>
                @endcan
                @can('manage_page')
                  @if (Auth::user()->PageId)
                    <li class="{{ Request::is('editmypage') ? 'active' : '' }} menu-item">
                      <a class="menu-link" href="/dashboard/editmypage">
                        <i class="menu-livicon" data-icon="briefcase"></i>
                        <span>{{ __('admin.editmypage') }}</span>
                      </a>
                    </li>
                  @endif
                @endcan
            </ul>
          </li>
        @endcan
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