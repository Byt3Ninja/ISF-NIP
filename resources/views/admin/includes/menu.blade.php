<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo pt-4">
    <a href="/dashboard" class="app-brand-link">
      @if (!is_null(auth()->user()->organization))
        <img src="{{ auth()->user()->organization->image_url }}" alt="avatar" height="40" width="80">
      @else
        <img src="/images/logo/growthlab.png" alt="avatar" height="50" width="150">
      @endif
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
      <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    <li class="menu-item">
      <a href="/dashboard" class="menu-link">
        {{ is_null(auth()->user()->organization_id) ? config('app.name') : auth()->user()->organization->name }}
      </a>
    </li>
    <!-- Dashboards -->
    <li
      class="{{ Request::is('dashboard', 'dashboard/final/*/*', 'dashboard/companyProfile/*', 'dashboard/cycles/view/*', 'dashboard/cycles/my/*', 'dashboard/screening/*/*') ? 'active' : '' }} menu-item">
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

    @if (auth()->user()->HaveCompany == 1)
      <li class="{{ Request::is('*myCompany') ? 'active' : '' }} menu-item">
        <a href="{{ route('my_company') }}" class="menu-link">
          <i class="menu-livicon" data-icon="building"></i>
          <span class="menu-title">{{ __('admin.My company') }}</span>
        </a>
      </li>
    @endif

    <li class="{{ Request::is('*calendar*') ? 'active' : '' }} menu-item">
      <a href="{{ route('calendar') }}" class="menu-link">
        <i class="menu-livicon" data-icon="calendar"></i>
        <span class="menu-title">{{ __('admin.Calendar') }}</span>
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
      <a class="menu-link menu-toggle"><i class="menu-livicon" data-icon="comments"></i>
        <span class="menu-title">{{ __('admin.My messages') }}</span>
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
          <i class="menu-livicon" data-icon="search"></i>
          <span class="menu-title">{{ __('admin.Services') }}</span>
        </a>
      </li>
    @endif
    @role('Mentor')
      <li class="{{ Request::is('dashboard/mentor/mentorshipSessions*') ? 'active' : '' }} menu-item">
        <a href="{{ route('mentorshipSessions.index') }}" class="menu-link">
          <i class="menu-livicon" data-icon="briefcase"></i>
          <span class="menu-title">{{ __('mentor.mentorshipTasks.index') }}</span>
        </a>
      </li>
    @endrole
    @role('Registered')
      <li
        class="{{ Request::is('dashboard/requests/mine', '/dashboard/mentorship/mine') ? 'sidebar-group-active open' : '' }} menu-item">
        <a href="#" class="menu-link"><i class="menu-livicon" data-icon="two-pointers"></i>
          < class="menu-title">{{ __('admin.My Requests') }}</>
        </a>
        <ul class="menu-sub">
          <li class="{{ Request::is('dashboard/requests/mine') ? 'active' : '' }} menu-item"><a
              href="/dashboard/requests/mine"><i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Services') }}</span>
            </a>
          </li>

          <li class="{{ Request::is('dashboard/mentorship/mine') ? 'active' : '' }} menu-item"><a
              href="/dashboard/mentorship/mine"><i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-item">{{ __('admin.Mentorship') }}</span>
            </a>
          </li>
        </ul>
      </li>

      <li class="{{ Request::is('dashboard/training/my*') ? 'active' : '' }} menu-item"><a href="/dashboard/training/my">
          <i class="menu-livicon" data-icon="help"></i>
          <span class="menu-title">{{ __('admin.My courses') }}</span></a>
      </li>
      <li class="{{ Request::is('dashboard/startup/mentorshipTasks*') ? 'active' : '' }} menu-item">
        <a href="{{ route('startup.mentorshipTasks.home') }}">
          <i class="menu-livicon" data-icon="briefcase"></i>
          <span class="menu-title">{{ __('mentor.mentorshipTasks.index') }}</span>
        </a>
      </li>
    @endrole
    <li class="{{ Request::is('dashboard/myFiles') ? 'active' : '' }} menu-item">
      <a href="/dashboard/myFiles" class="menu-link">
        <i class="menu-livicon" data-icon="save"></i>
        <span class="menu-title">{{ __('admin.filemanager') }}</span>
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
          <i class="menu-livicon" data-icon="building"></i>
          <span class="menu-title">{{ __('admin.Manage Organizations') }}</span>
        </a>
      </li>
    @endif

    @can('edit_forms')
      <li class="{{ Request::is('dashboard/show-builder*', 'dashboard/show-form*') ? 'active' : '' }} menu-item">
        <a href="/dashboard/show-builder" class="menu-link">
          <i class="menu-livicon" data-icon="thumbnails-big"></i>
          <span class="menu-title">{{ __('admin.Forms builder') }}</span>
        </a>
      </li>
    @endcan

    @can('edit_cycles')
      <li class="{{ Request::is('dashboard/cycles*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <i class="menu-livicon" data-icon="retweet"></i>
          <span class="menu-title">{{ __('admin.Cycles') }}</span>
        </a>
        <ul class="menu-sub">
          <li class="{{ Request::is('/dashboard/cycles*') ? 'active' : '' }} menu-item">
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

      <li class="{{ Request::is('dashboard/screening*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <i class="menu-livicon" data-icon="hourglass"></i>
          <span class="menu-title">{{ __('admin.Screening') }}</span>
        </a>
        <ul class="menu-sub">
          <li class="{{ Request::is('dashboard/screening', 'dashboard/*/screening') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/screening">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-title">{{ __('admin.Manage') }}</span>
            </a>
          </li>
          <li class="{{ Request::is('dashboard/screening/results*') ? 'active' : '' }} menu-item">
            <a class="menu-link" href="/dashboard/screening/results">
              <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
              <span class="menu-title"> {{ __('admin.Results') }}</span>
            </a>
          </li>
        </ul>
      </li>

      <!-- Mentorship Nav Item -->
      <li
        class="{{ Request::is('dashboard/mentorship/assign*', 'dashboard/mentorship/view*', 'dashboard/mentorship/sessions*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <span class="menu-title">{{ __('admin.Mentorship') }}</span>
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
              <span class="menu-title">{{ __('admin.Sessions') }}</span>
            </a>
            <ul class="menu-sub">
              <li class="{{ Request::is('/dashboard/mentorship/sessions?show=all') ? 'active' : '' }} menu-item">
                <a class="menu-link" href="/dashboard/mentorship/sessions?show=all">
                  <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                  <span class="menu-item">{{ __('cycles.All requests') }}</span>
                </a>
              </li>
              <li class="{{ Request::is('/dashboard/mentorship/sessions') ? 'active' : '' }} menu-item">
                <a class="menu-link" href="/dashboard/mentorship/sessions">
                  <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                  <span class="menu-item">{{ __('cycles.New requests only') }}</span>
                </a>
              </li>
              <li class="{{ Request::is('/dashboard/mentorship/sessions?show=approved') ? 'active' : '' }} menu-item">
                <a class="menu-link" href="/dashboard/mentorship/sessions?show=approved">
                  <i class="bx bx-{{ $arrow }}-arrow-alt"></i>
                  <span class="menu-item">{{ __('cycles.approved requests') }}</span>
                </a>
              </li>
              <li
                class="{{ Request::is('/dashboard/mentorship/sessions?show=disapproved') ? 'active' : '' }} menu-item">
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

      <li class="{{ Request::is('*training*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle"><i class="menu-livicon" data-icon="info-alt"></i>
          <span class="menu-title">{{ __('admin.Training') }}</span>
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
        </ul>
      </li>
      <li class="{{ Request::is('dashboard/final/*') ? 'sidebar-group-active open' : '' }} menu-item">
        <a class="menu-link menu-toggle">
          <i class="menu-livicon" data-icon="trophy"></i>
          <span class="menu-title">{{ __('admin.Final Screening') }}</span>
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
            <i class="menu-livicon" data-="loader-10"></i>
            <span class="menu-title">{{ __('admin.Services') }}</span>
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
            <i class="menu-livicon" data-icon="users"></i>
            <span class="menu-title">{{ __('admin.Users') }}</span>
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
            <i class="menu-livicon" data-icon="morph-folder"></i>
            <span class="menu-title">{{ __('admin.Documentations') }}</span>
          </a>
        </li>
      @endcan
      {{-- @can('view_log')
    <li class="{{ Request::is('*audit-logs*') ? 'active' : '' }} menu-item">
      <a href="/dashboard/audit-logs">
        <i class="menu-livicon" data-icon="lab"></i>
        <span class="menu-title">{{ __('admin.Log viewer') }}</span>
      </a>
    </li>
  @endcan --}}
    @endif

    @can('view_report')
      <li class="{{ Request::is('reports*') ? 'active' : '' }} menu-item">
        <a class="menu-link" href="{{ route('reports.index') }}">
          <i class="menu-livicon" data-icon="notebook"></i>
          <span class="menu-title">{{ __('admin.Reports') }}</span></a>
      </li>
    @endcan

    <!-- Layouts -->
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-layout-sidebar"></i>
        <div data-i18n="Layouts">Layouts</div>
      </a>

      <ul class="menu-sub">
        <li class="menu-item">
          <a href="layouts-collapsed-menu.html" class="menu-link">
            <div data-i18n="Collapsed menu">Collapsed menu</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="layouts-content-navbar.html" class="menu-link">
            <div data-i18n="Content navbar">Content navbar</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="layouts-content-navbar-with-sidebar.html" class="menu-link">
            <div data-i18n="Content nav + Sidebar">Content nav + Sidebar</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="../horizontal-menu-template" class="menu-link" target="_blank">
            <div data-i18n="Horizontal">Horizontal</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="layouts-without-menu.html" class="menu-link">
            <div data-i18n="Without menu">Without menu</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="layouts-without-navbar.html" class="menu-link">
            <div data-i18n="Without navbar">Without navbar</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="layouts-fluid.html" class="menu-link">
            <div data-i18n="Fluid">Fluid</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="layouts-container.html" class="menu-link">
            <div data-i18n="Container">Container</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="layouts-blank.html" class="menu-link">
            <div data-i18n="Blank">Blank</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Front Pages -->
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-files"></i>
        <div data-i18n="Front Pages">Front Pages</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="../front-pages/landing-page.html" class="menu-link" target="_blank">
            <div data-i18n="Landing">Landing</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="../front-pages/pricing-page.html" class="menu-link" target="_blank">
            <div data-i18n="Pricing">Pricing</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="../front-pages/payment-page.html" class="menu-link" target="_blank">
            <div data-i18n="Payment">Payment</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="../front-pages/checkout-page.html" class="menu-link" target="_blank">
            <div data-i18n="Checkout">Checkout</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="../front-pages/help-center-landing.html" class="menu-link" target="_blank">
            <div data-i18n="Help Center">Help Center</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Apps & Pages -->
    <li class="menu-header small">
      <span class="menu-header-text" data-i18n="Apps & Pages">Apps &amp; Pages</span>
    </li>
    <li class="menu-item">
      <a href="app-email.html" class="menu-link">
        <i class="menu-icon tf-icons ti ti-mail"></i>
        <div data-i18n="Email">Email</div>
      </a>
    </li>
    <li class="menu-item">
      <a href="app-chat.html" class="menu-link">
        <i class="menu-icon tf-icons ti ti-messages"></i>
        <div data-i18n="Chat">Chat</div>
      </a>
    </li>
    <li class="menu-item">
      <a href="app-calendar.html" class="menu-link">
        <i class="menu-icon tf-icons ti ti-calendar"></i>
        <div data-i18n="Calendar">Calendar</div>
      </a>
    </li>
    <li class="menu-item">
      <a href="app-kanban.html" class="menu-link">
        <i class="menu-icon tf-icons ti ti-layout-kanban"></i>
        <div data-i18n="Kanban">Kanban</div>
      </a>
    </li>
    <!-- e-commerce-app menu start -->
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-shopping-cart"></i>
        <div data-i18n="eCommerce">eCommerce</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="app-ecommerce-dashboard.html" class="menu-link">
            <div data-i18n="Dashboard">Dashboard</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Products">Products</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="app-ecommerce-product-list.html" class="menu-link">
                <div data-i18n="Product List">Product List</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-ecommerce-product-add.html" class="menu-link">
                <div data-i18n="Add Product">Add Product</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-ecommerce-category-list.html" class="menu-link">
                <div data-i18n="Category List">Category List</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Order">Order</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="app-ecommerce-order-list.html" class="menu-link">
                <div data-i18n="Order List">Order List</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-ecommerce-order-details.html" class="menu-link">
                <div data-i18n="Order Details">Order Details</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Customer">Customer</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="app-ecommerce-customer-all.html" class="menu-link">
                <div data-i18n="All Customers">All Customers</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <div data-i18n="Customer Details">Customer Details</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="app-ecommerce-customer-details-overview.html" class="menu-link">
                    <div data-i18n="Overview">Overview</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="app-ecommerce-customer-details-security.html" class="menu-link">
                    <div data-i18n="Security">Security</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="app-ecommerce-customer-details-billing.html" class="menu-link">
                    <div data-i18n="Address & Billing">Address & Billing</div>
                  </a>
                </li>
                <li class="menu-item">
                  <a href="app-ecommerce-customer-details-notifications.html" class="menu-link">
                    <div data-i18n="Notifications">Notifications</div>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="app-ecommerce-manage-reviews.html" class="menu-link">
            <div data-i18n="Manage Reviews">Manage Reviews</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="app-ecommerce-referral.html" class="menu-link">
            <div data-i18n="Referrals">Referrals</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Settings">Settings</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="app-ecommerce-settings-detail.html" class="menu-link">
                <div data-i18n="Store Details">Store Details</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-ecommerce-settings-payments.html" class="menu-link">
                <div data-i18n="Payments">Payments</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-ecommerce-settings-checkout.html" class="menu-link">
                <div data-i18n="Checkout">Checkout</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-ecommerce-settings-shipping.html" class="menu-link">
                <div data-i18n="Shipping & Delivery">Shipping & Delivery</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-ecommerce-settings-locations.html" class="menu-link">
                <div data-i18n="Locations">Locations</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-ecommerce-settings-notifications.html" class="menu-link">
                <div data-i18n="Notifications">Notifications</div>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </li>
    <!-- e-commerce-app menu end -->
    <!-- Academy menu start -->
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-book"></i>
        <div data-i18n="Academy">Academy</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="app-academy-dashboard.html" class="menu-link">
            <div data-i18n="Dashboard">Dashboard</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="app-academy-course.html" class="menu-link">
            <div data-i18n="My Course">My Course</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="app-academy-course-details.html" class="menu-link">
            <div data-i18n="Course Details">Course Details</div>
          </a>
        </li>
      </ul>
    </li>
    <!-- Academy menu end -->
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-truck"></i>
        <div data-i18n="Logistics">Logistics</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="app-logistics-dashboard.html" class="menu-link">
            <div data-i18n="Dashboard">Dashboard</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="app-logistics-fleet.html" class="menu-link">
            <div data-i18n="Fleet">Fleet</div>
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-file-dollar"></i>
        <div data-i18n="Invoice">Invoice</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="app-invoice-list.html" class="menu-link">
            <div data-i18n="List">List</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="app-invoice-preview.html" class="menu-link">
            <div data-i18n="Preview">Preview</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="app-invoice-edit.html" class="menu-link">
            <div data-i18n="Edit">Edit</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="app-invoice-add.html" class="menu-link">
            <div data-i18n="Add">Add</div>
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-users"></i>
        <div data-i18n="Users">Users</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="app-user-list.html" class="menu-link">
            <div data-i18n="List">List</div>
          </a>
        </li>

        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="View">View</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="app-user-view-account.html" class="menu-link">
                <div data-i18n="Account">Account</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-user-view-security.html" class="menu-link">
                <div data-i18n="Security">Security</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-user-view-billing.html" class="menu-link">
                <div data-i18n="Billing & Plans">Billing & Plans</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-user-view-notifications.html" class="menu-link">
                <div data-i18n="Notifications">Notifications</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="app-user-view-connections.html" class="menu-link">
                <div data-i18n="Connections">Connections</div>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-settings"></i>
        <div data-i18n="Roles & Permissions">Roles & Permissions</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="app-access-roles.html" class="menu-link">
            <div data-i18n="Roles">Roles</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="app-access-permission.html" class="menu-link">
            <div data-i18n="Permission">Permission</div>
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item active open">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-file"></i>
        <div data-i18n="Pages">Pages</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item active open">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="User Profile">User Profile</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item active">
              <a href="pages-profile-user.html" class="menu-link">
                <div data-i18n="Profile">Profile</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-profile-teams.html" class="menu-link">
                <div data-i18n="Teams">Teams</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-profile-projects.html" class="menu-link">
                <div data-i18n="Projects">Projects</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-profile-connections.html" class="menu-link">
                <div data-i18n="Connections">Connections</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Account Settings">Account Settings</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="pages-account-settings-account.html" class="menu-link">
                <div data-i18n="Account">Account</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-account-settings-security.html" class="menu-link">
                <div data-i18n="Security">Security</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-account-settings-billing.html" class="menu-link">
                <div data-i18n="Billing & Plans">Billing & Plans</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-account-settings-notifications.html" class="menu-link">
                <div data-i18n="Notifications">Notifications</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-account-settings-connections.html" class="menu-link">
                <div data-i18n="Connections">Connections</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="pages-faq.html" class="menu-link">
            <div data-i18n="FAQ">FAQ</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="pages-pricing.html" class="menu-link">
            <div data-i18n="Pricing">Pricing</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Misc">Misc</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="pages-misc-error.html" class="menu-link" target="_blank">
                <div data-i18n="Error">Error</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-misc-under-maintenance.html" class="menu-link" target="_blank">
                <div data-i18n="Under Maintenance">Under Maintenance</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-misc-comingsoon.html" class="menu-link" target="_blank">
                <div data-i18n="Coming Soon">Coming Soon</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="pages-misc-not-authorized.html" class="menu-link" target="_blank">
                <div data-i18n="Not Authorized">Not Authorized</div>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-lock"></i>
        <div data-i18n="Authentications">Authentications</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Login">Login</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="auth-login-basic.html" class="menu-link" target="_blank">
                <div data-i18n="Basic">Basic</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="auth-login-cover.html" class="menu-link" target="_blank">
                <div data-i18n="Cover">Cover</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Register">Register</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="auth-register-basic.html" class="menu-link" target="_blank">
                <div data-i18n="Basic">Basic</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="auth-register-cover.html" class="menu-link" target="_blank">
                <div data-i18n="Cover">Cover</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="auth-register-multisteps.html" class="menu-link" target="_blank">
                <div data-i18n="Multi-steps">Multi-steps</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Verify Email">Verify Email</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="auth-verify-email-basic.html" class="menu-link" target="_blank">
                <div data-i18n="Basic">Basic</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="auth-verify-email-cover.html" class="menu-link" target="_blank">
                <div data-i18n="Cover">Cover</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Reset Password">Reset Password</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="auth-reset-password-basic.html" class="menu-link" target="_blank">
                <div data-i18n="Basic">Basic</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="auth-reset-password-cover.html" class="menu-link" target="_blank">
                <div data-i18n="Cover">Cover</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Forgot Password">Forgot Password</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="auth-forgot-password-basic.html" class="menu-link" target="_blank">
                <div data-i18n="Basic">Basic</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="auth-forgot-password-cover.html" class="menu-link" target="_blank">
                <div data-i18n="Cover">Cover</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Two Steps">Two Steps</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="auth-two-steps-basic.html" class="menu-link" target="_blank">
                <div data-i18n="Basic">Basic</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="auth-two-steps-cover.html" class="menu-link" target="_blank">
                <div data-i18n="Cover">Cover</div>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-forms"></i>
        <div data-i18n="Wizard Examples">Wizard Examples</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="wizard-ex-checkout.html" class="menu-link">
            <div data-i18n="Checkout">Checkout</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="wizard-ex-property-listing.html" class="menu-link">
            <div data-i18n="Property Listing">Property Listing</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="wizard-ex-create-deal.html" class="menu-link">
            <div data-i18n="Create Deal">Create Deal</div>
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item">
      <a href="modal-examples.html" class="menu-link">
        <i class="menu-icon tf-icons ti ti-square"></i>
        <div data-i18n="Modal Examples">Modal Examples</div>
      </a>
    </li>

    <!-- Components -->
    <li class="menu-header small">
      <span class="menu-header-text" data-i18n="Components">Components</span>
    </li>
    <!-- Cards -->
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-id"></i>
        <div data-i18n="Cards">Cards</div>
        <div class="badge bg-primary rounded-pill ms-auto">5</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="cards-basic.html" class="menu-link">
            <div data-i18n="Basic">Basic</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="cards-advance.html" class="menu-link">
            <div data-i18n="Advance">Advance</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="cards-statistics.html" class="menu-link">
            <div data-i18n="Statistics">Statistics</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="cards-analytics.html" class="menu-link">
            <div data-i18n="Analytics">Analytics</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="cards-actions.html" class="menu-link">
            <div data-i18n="Actions">Actions</div>
          </a>
        </li>
      </ul>
    </li>
    <!-- User interface -->
    <li class="menu-item">
      <a href="javascript:void(0)" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-color-swatch"></i>
        <div data-i18n="User interface">User interface</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="ui-accordion.html" class="menu-link">
            <div data-i18n="Accordion">Accordion</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-alerts.html" class="menu-link">
            <div data-i18n="Alerts">Alerts</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-badges.html" class="menu-link">
            <div data-i18n="Badges">Badges</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-buttons.html" class="menu-link">
            <div data-i18n="Buttons">Buttons</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-carousel.html" class="menu-link">
            <div data-i18n="Carousel">Carousel</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-collapse.html" class="menu-link">
            <div data-i18n="Collapse">Collapse</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-dropdowns.html" class="menu-link">
            <div data-i18n="Dropdowns">Dropdowns</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-footer.html" class="menu-link">
            <div data-i18n="Footer">Footer</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-list-groups.html" class="menu-link">
            <div data-i18n="List Groups">List groups</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-modals.html" class="menu-link">
            <div data-i18n="Modals">Modals</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-navbar.html" class="menu-link">
            <div data-i18n="Navbar">Navbar</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-offcanvas.html" class="menu-link">
            <div data-i18n="Offcanvas">Offcanvas</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-pagination-breadcrumbs.html" class="menu-link">
            <div data-i18n="Pagination & Breadcrumbs">Pagination &amp; Breadcrumbs</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-progress.html" class="menu-link">
            <div data-i18n="Progress">Progress</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-spinners.html" class="menu-link">
            <div data-i18n="Spinners">Spinners</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-tabs-pills.html" class="menu-link">
            <div data-i18n="Tabs & Pills">Tabs &amp; Pills</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-toasts.html" class="menu-link">
            <div data-i18n="Toasts">Toasts</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-tooltips-popovers.html" class="menu-link">
            <div data-i18n="Tooltips & Popovers">Tooltips &amp; Popovers</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="ui-typography.html" class="menu-link">
            <div data-i18n="Typography">Typography</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Extended components -->
    <li class="menu-item">
      <a href="javascript:void(0)" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-components"></i>
        <div data-i18n="Extended UI">Extended UI</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="extended-ui-avatar.html" class="menu-link">
            <div data-i18n="Avatar">Avatar</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="extended-ui-blockui.html" class="menu-link">
            <div data-i18n="BlockUI">BlockUI</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="extended-ui-drag-and-drop.html" class="menu-link">
            <div data-i18n="Drag & Drop">Drag &amp; Drop</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="extended-ui-media-player.html" class="menu-link">
            <div data-i18n="Media Player">Media Player</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="extended-ui-perfect-scrollbar.html" class="menu-link">
            <div data-i18n="Perfect Scrollbar">Perfect Scrollbar</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="extended-ui-star-ratings.html" class="menu-link">
            <div data-i18n="Star Ratings">Star Ratings</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="extended-ui-sweetalert2.html" class="menu-link">
            <div data-i18n="SweetAlert2">SweetAlert2</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="extended-ui-text-divider.html" class="menu-link">
            <div data-i18n="Text Divider">Text Divider</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <div data-i18n="Timeline">Timeline</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item">
              <a href="extended-ui-timeline-basic.html" class="menu-link">
                <div data-i18n="Basic">Basic</div>
              </a>
            </li>
            <li class="menu-item">
              <a href="extended-ui-timeline-fullscreen.html" class="menu-link">
                <div data-i18n="Fullscreen">Fullscreen</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-item">
          <a href="extended-ui-tour.html" class="menu-link">
            <div data-i18n="Tour">Tour</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="extended-ui-treeview.html" class="menu-link">
            <div data-i18n="Treeview">Treeview</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="extended-ui-misc.html" class="menu-link">
            <div data-i18n="Miscellaneous">Miscellaneous</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Icons -->
    <li class="menu-item">
      <a href="javascript:void(0)" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-brand-tabler"></i>
        <div data-i18n="Icons">Icons</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="icons-tabler.html" class="menu-link">
            <div data-i18n="Tabler">Tabler</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="icons-font-awesome.html" class="menu-link">
            <div data-i18n="Fontawesome">Fontawesome</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Forms & Tables -->
    <li class="menu-header small">
      <span class="menu-header-text" data-i18n="Forms & Tables">Forms &amp; Tables</span>
    </li>
    <!-- Forms -->
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-toggle-left"></i>
        <div data-i18n="Form Elements">Form Elements</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="forms-basic-inputs.html" class="menu-link">
            <div data-i18n="Basic Inputs">Basic Inputs</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="forms-input-groups.html" class="menu-link">
            <div data-i18n="Input groups">Input groups</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="forms-custom-options.html" class="menu-link">
            <div data-i18n="Custom Options">Custom Options</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="forms-editors.html" class="menu-link">
            <div data-i18n="Editors">Editors</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="forms-file-upload.html" class="menu-link">
            <div data-i18n="File Upload">File Upload</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="forms-pickers.html" class="menu-link">
            <div data-i18n="Pickers">Pickers</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="forms-selects.html" class="menu-link">
            <div data-i18n="Select & Tags">Select &amp; Tags</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="forms-sliders.html" class="menu-link">
            <div data-i18n="Sliders">Sliders</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="forms-switches.html" class="menu-link">
            <div data-i18n="Switches">Switches</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="forms-extras.html" class="menu-link">
            <div data-i18n="Extras">Extras</div>
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-layout-navbar"></i>
        <div data-i18n="Form Layouts">Form Layouts</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="form-layouts-vertical.html" class="menu-link">
            <div data-i18n="Vertical Form">Vertical Form</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="form-layouts-horizontal.html" class="menu-link">
            <div data-i18n="Horizontal Form">Horizontal Form</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="form-layouts-sticky.html" class="menu-link">
            <div data-i18n="Sticky Actions">Sticky Actions</div>
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-text-wrap-disabled"></i>
        <div data-i18n="Form Wizard">Form Wizard</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="form-wizard-numbered.html" class="menu-link">
            <div data-i18n="Numbered">Numbered</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="form-wizard-icons.html" class="menu-link">
            <div data-i18n="Icons">Icons</div>
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item">
      <a href="form-validation.html" class="menu-link">
        <i class="menu-icon tf-icons ti ti-checkbox"></i>
        <div data-i18n="Form Validation">Form Validation</div>
      </a>
    </li>
    <!-- Tables -->
    <li class="menu-item">
      <a href="tables-basic.html" class="menu-link">
        <i class="menu-icon tf-icons ti ti-table"></i>
        <div data-i18n="Tables">Tables</div>
      </a>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-layout-grid"></i>
        <div data-i18n="Datatables">Datatables</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="tables-datatables-basic.html" class="menu-link">
            <div data-i18n="Basic">Basic</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="tables-datatables-advanced.html" class="menu-link">
            <div data-i18n="Advanced">Advanced</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="tables-datatables-extensions.html" class="menu-link">
            <div data-i18n="Extensions">Extensions</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Charts & Maps -->
    <li class="menu-header small">
      <span class="menu-header-text" data-i18n="Charts & Maps">Charts &amp; Maps</span>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-chart-pie"></i>
        <div data-i18n="Charts">Charts</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="charts-apex.html" class="menu-link">
            <div data-i18n="Apex Charts">Apex Charts</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="charts-chartjs.html" class="menu-link">
            <div data-i18n="ChartJS">ChartJS</div>
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item">
      <a href="maps-leaflet.html" class="menu-link">
        <i class="menu-icon tf-icons ti ti-map"></i>
        <div data-i18n="Leaflet Maps">Leaflet Maps</div>
      </a>
    </li>

    <!-- Misc -->
    <li class="menu-header small">
      <span class="menu-header-text" data-i18n="Misc">Misc</span>
    </li>
    <li class="menu-item">
      <a href="https://pixinvent.ticksy.com/" target="_blank" class="menu-link">
        <i class="menu-icon tf-icons ti ti-lifebuoy"></i>
        <div data-i18n="Support">Support</div>
      </a>
    </li>
    <li class="menu-item">
      <a href="https://demos.pixinvent.com/vuexy-html-admin-template/documentation/" target="_blank"
        class="menu-link">
        <i class="menu-icon tf-icons ti ti-file-description"></i>
        <div data-i18n="Documentation">Documentation</div>
      </a>
    </li>
  </ul>
</aside>
<!-- / Menu -->
