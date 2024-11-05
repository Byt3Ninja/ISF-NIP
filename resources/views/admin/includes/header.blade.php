@php
  $msgs = 0;
  if (Auth::user()->hasanyRole('Admin|Manager')) {
      $msgs = App\Messages::where('read', 0)->count();
  }
  $messages = App\Messenger::where('receiver', Auth::user()->id)
      ->whereNull('read')
      ->count();
  $all = $msgs + $messages;
  isset(Auth::user()->company()->first()->id)
      ? ($companyid = Auth::user()->company()->first()->id)
      : ($companyid = null);
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
    <div class="navbar-nav align-items-center">
      <div class="nav-item navbar-search-wrapper mb-0">
        <a class="nav-item nav-link search-toggler d-flex align-items-center px-0" href="javascript:void(0);">
          <i class="ti ti-search ti-md me-2 me-lg-4 ti-lg"></i>
          <span class="d-none d-md-inline-block text-muted fw-normal">Search (Ctrl+/)</span>
        </a>
      </div>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-auto">
      <!-- Language -->
      <li class="nav-item dropdown-language dropdown">
        <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
          href="javascript:void(0);" data-bs-toggle="dropdown">
          <i class="ti ti-language rounded-circle ti-md"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li class="dropdown dropdown-language nav-item">
            @if ($l == 'ar')
              <a class="dropdown-item" href="/lang/en">
                <i class="flag-icon flag-icon-us"></i>
                <span class="selected-language">English</span>
              </a>
            @else
              <a class="dropdown-item" href="/lang/ar">
                <i class="flag-icon flag-icon-sa"></i>
                <span class="selected-language">عربي</span>
              </a>
            @endif
          </li>
          {{-- <li>
            <a class="dropdown-item" href="/lang/en" 
            data-language="en" data-text-direction="ltr"
            >
              <span>English</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item" href="/lang/ar" 
            data-language="ar" data-text-direction="rtl"
            >
              <span>Arabic</span>
            </a>
          </li> --}}
          {{-- <li>
            <a class="dropdown-item" href="javascript:void(0);" data-language="fr" data-text-direction="ltr">
              <span>French</span>
            </a>
          </li> --}}
          {{-- <li>
            <a class="dropdown-item" href="javascript:void(0);" data-language="de" data-text-direction="ltr">
              <span>German</span>
            </a>
          </li> --}}
        </ul>
      </li>
      <!--/ Language -->

      <!-- Style Switcher -->
      <li class="nav-item dropdown-style-switcher dropdown">
        <a class="nav-link btn-icon rounded-pill dropdown-toggle hide-arrow"
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

      <!-- Quick links  -->
      {{-- <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown">
        <a class="nav-link btn btn-text-secondary btn-icon rounded-pill btn-icon dropdown-toggle hide-arrow"
          href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
          <i class="ti ti-layout-grid-add ti-md"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end p-0">
          <div class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
              <h6 class="mb-0 me-auto">Shortcuts</h6>
              <a href="javascript:void(0)" class="btn btn-text-secondary rounded-pill btn-icon dropdown-shortcuts-add"
                data-bs-toggle="tooltip" data-bs-placement="top" title="Add shortcuts"><i
                  class="ti ti-plus text-heading"></i></a>
            </div>
          </div>
          <div class="dropdown-shortcuts-list scrollable-container">
            <div class="row row-bordered overflow-visible g-0">
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="ti ti-calendar ti-26px text-heading"></i>
                </span>
                <a href="app-calendar.html" class="stretched-link">Calendar</a>
                <small>Appointments</small>
              </div>
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="ti ti-file-dollar ti-26px text-heading"></i>
                </span>
                <a href="app-invoice-list.html" class="stretched-link">Invoice App</a>
                <small>Manage Accounts</small>
              </div>
            </div>
            <div class="row row-bordered overflow-visible g-0">
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="ti ti-user ti-26px text-heading"></i>
                </span>
                <a href="app-user-list.html" class="stretched-link">User App</a>
                <small>Manage Users</small>
              </div>
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="ti ti-users ti-26px text-heading"></i>
                </span>
                <a href="app-access-roles.html" class="stretched-link">Role Management</a>
                <small>Permission</small>
              </div>
            </div>
            <div class="row row-bordered overflow-visible g-0">
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="ti ti-device-desktop-analytics ti-26px text-heading"></i>
                </span>
                <a href="index.html" class="stretched-link">Dashboard</a>
                <small>User Dashboard</small>
              </div>
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="ti ti-settings ti-26px text-heading"></i>
                </span>
                <a href="pages-account-settings-account.html" class="stretched-link">Setting</a>
                <small>Account Settings</small>
              </div>
            </div>
            <div class="row row-bordered overflow-visible g-0">
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="ti ti-help ti-26px text-heading"></i>
                </span>
                <a href="/dashboard/documentation" class="stretched-link">FAQs</a>
                <small>FAQs & Articles</small>
              </div>
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="ti ti-square ti-26px text-heading"></i>
                </span>
                <a href="modal-examples.html" class="stretched-link">Modals</a>
                <small>Useful Popups</small>
              </div>
            </div>
          </div>
        </div>
      </li> --}}
      <!-- Quick links -->

      <!-- Notifications -->
      @include('partials.header.notification')
      <!--/ Notifications -->

      <!-- User -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
          <div class="avatar avatar-online">
            @if (auth()->user()->image_url)
              <img 
                class="rounded-circle" 
                src="{{ auth()->user()->image_url }}"
                onerror="this.src='/icons/account.svg'"
              />
            @else
              <img class="rounded-circle" src="/icons/account.svg" alt="avatar" />
            @endif
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item mt-0" href="/dashboard/show/myProfile">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-2">
                  <div class="avatar avatar-online">
                    @if (auth()->user()->image_url)
                      <img 
                        class="rounded-circle" 
                        src="{{ auth()->user()->image_url }}"
                        onerror="this.src='/icons/account.svg'"
                      />
                    @else
                      <img class="rounded-circle" src="/icons/account.svg" alt="avatar" />
                    @endif
                  </div>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                  <small class="text-muted">{{ Auth::user()->title }}</small>
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
          @if ($companyid)
            <li>
              <a class="dropdown-item" href="/dashboard/myCompany">
                <i class="ti ti-building me-3 ti-md"></i>
                <span class="align-middle">{{ __('admin.Company') }}</span>
              </a>
            </li>
          @endif
          <li>
            <a class="dropdown-item" href="/dashboard/myProfile">
              <i class="ti ti-settings me-3 ti-md"></i>
              <span class="align-middle">
                {{$l=='ar'?'الاعدادات':'Settings'}}
              </span>
            </a>
          </li>
          {{-- <li>
            <a class="dropdown-item" href="pages-account-settings-billing.html">
              <span class="d-flex align-items-center align-middle">
                <i class="flex-shrink-0 ti ti-file-dollar me-3 ti-md"></i><span
                  class="flex-grow-1 align-middle">Billing</span>
                <span class="flex-shrink-0 badge bg-danger d-flex align-items-center justify-content-center">4</span>
              </span>
            </a>
          </li> --}}
          {{-- <li>
            <div class="dropdown-divider my-1 mx-n2"></div>
          </li>
          <li>
            <a class="dropdown-item" href="pages-pricing.html">
              <i class="ti ti-currency-dollar me-3 ti-md"></i><span class="align-middle">Pricing</span>
            </a>
          </li> --}}
          <li>
            <a class="dropdown-item" href="/dashboard/documentation">
              <i class="ti ti-question-mark me-3 ti-md"></i><span class="align-middle">FAQ</span>
            </a>
          </li>
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
