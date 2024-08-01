@extends('layouts.admin')
@section('title', __('admin.Profile') )
@section('subTitle', __('admin.Edit your profile information'))

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-profile.css" />
@stop

@section('content')
  <!-- Content wrapper -->
  <div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="row">
          <div class="col-12">
            <div class="card mb-6">
              <div class="user-profile-header-banner">
                <img src="/vuexy/assets/img/pages/profile-banner.png" alt="Banner image" class="rounded-top" />
              </div>
              <div class="user-profile-header d-flex flex-column flex-lg-row text-sm-start text-center mb-5">
                <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                  <img
                    src="/vuexy/assets/img/avatars/1.png"
                    alt="user image"
                    class="d-block h-auto ms-0 ms-sm-6 rounded user-profile-img" />
                </div>
                <div class="flex-grow-1 mt-3 mt-lg-5">
                  <div
                    class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                    <div class="user-profile-info">
                      <h4 class="mb-2 mt-lg-6">John Doe</h4>
                      <ul
                        class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                        <li class="list-inline-item d-flex gap-2 align-items-center">
                          <i class="ti ti-palette ti-lg"></i><span class="fw-medium">UX Designer</span>
                        </li>
                        <li class="list-inline-item d-flex gap-2 align-items-center">
                          <i class="ti ti-map-pin ti-lg"></i><span class="fw-medium">Vatican City</span>
                        </li>
                        <li class="list-inline-item d-flex gap-2 align-items-center">
                          <i class="ti ti-calendar ti-lg"></i><span class="fw-medium"> Joined April 2021</span>
                        </li>
                      </ul>
                    </div>
                    <a href="javascript:void(0)" class="btn btn-primary mb-1">
                      <i class="ti ti-user-check ti-xs me-2"></i>Connected
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--/ Header -->

        <!-- Navbar pills -->
        <div class="row">
          <div class="col-md-12">
            <div class="nav-align-top">
              <ul class="nav nav-pills flex-column flex-sm-row mb-6 gap-2 gap-lg-0">
                <li class="nav-item">
                  <a class="nav-link active" href="javascript:void(0);"
                    ><i class="ti-sm ti ti-user-check me-1_5"></i> Profile</a
                  >
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="pages-profile-teams.html"
                    ><i class="ti-sm ti ti-users me-1_5"></i> Teams</a
                  >
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="pages-profile-projects.html"
                    ><i class="ti-sm ti ti-layout-grid me-1_5"></i> Projects</a
                  >
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="pages-profile-connections.html"
                    ><i class="ti-sm ti ti-link me-1_5"></i> Connections</a
                  >
                </li>
              </ul>
            </div>
          </div>
        </div>
        <!--/ Navbar pills -->

        <!-- User Profile Content -->
        <div class="row">
          <div class="col-xl-4 col-lg-5 col-md-5">
            <!-- About User -->
            <div class="card mb-6">
              <div class="card-body">
                <small class="card-text text-uppercase text-muted small">About</small>
                <ul class="list-unstyled my-3 py-1">
                  <li class="d-flex align-items-center mb-4">
                    <i class="ti ti-user ti-lg"></i><span class="fw-medium mx-2">Full Name:</span>
                    <span>John Doe</span>
                  </li>
                  <li class="d-flex align-items-center mb-4">
                    <i class="ti ti-check ti-lg"></i><span class="fw-medium mx-2">Status:</span>
                    <span>Active</span>
                  </li>
                  <li class="d-flex align-items-center mb-4">
                    <i class="ti ti-crown ti-lg"></i><span class="fw-medium mx-2">Role:</span>
                    <span>Developer</span>
                  </li>
                  <li class="d-flex align-items-center mb-4">
                    <i class="ti ti-flag ti-lg"></i><span class="fw-medium mx-2">Country:</span> <span>USA</span>
                  </li>
                  <li class="d-flex align-items-center mb-2">
                    <i class="ti ti-language ti-lg"></i><span class="fw-medium mx-2">Languages:</span>
                    <span>English</span>
                  </li>
                </ul>
                <small class="card-text text-uppercase text-muted small">Contacts</small>
                <ul class="list-unstyled my-3 py-1">
                  <li class="d-flex align-items-center mb-4">
                    <i class="ti ti-phone-call ti-lg"></i><span class="fw-medium mx-2">Contact:</span>
                    <span>(123) 456-7890</span>
                  </li>
                  <li class="d-flex align-items-center mb-4">
                    <i class="ti ti-messages ti-lg"></i><span class="fw-medium mx-2">Skype:</span>
                    <span>john.doe</span>
                  </li>
                  <li class="d-flex align-items-center mb-4">
                    <i class="ti ti-mail ti-lg"></i><span class="fw-medium mx-2">Email:</span>
                    <span>john.doe@example.com</span>
                  </li>
                </ul>
                <small class="card-text text-uppercase text-muted small">Teams</small>
                <ul class="list-unstyled mb-0 mt-3 pt-1">
                  <li class="d-flex flex-wrap mb-4">
                    <span class="fw-medium me-2">Backend Developer</span><span>(126 Members)</span>
                  </li>
                  <li class="d-flex flex-wrap">
                    <span class="fw-medium me-2">React Developer</span><span>(98 Members)</span>
                  </li>
                </ul>
              </div>
            </div>
            <!--/ About User -->
          </div>
        </div>
    </div>
  </div>
@stop

@section('pagescripts')
  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->

  <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
  <script src="/vuexy/assets/vendor/libs/popper/popper.js"></script>
  <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
  <script src="/vuexy/assets/vendor/libs/node-waves/node-waves.js"></script>
  <script src="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="/vuexy/assets/vendor/libs/hammer/hammer.js"></script>
  <script src="/vuexy/assets/vendor/libs/i18n/i18n.js"></script>
  <script src="/vuexy/assets/vendor/libs/typeahead-js/typeahead.js"></script>
  <script src="/vuexy/assets/vendor/js/menu.js"></script>

  <!-- endbuild -->

  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>

  <!-- Main JS -->
  <script src="/vuexy/assets/js/main.js"></script>

  <!-- Page JS -->
  <script src="/vuexy/assets/js/pages-profile.js"></script>
@stop