@extends('layouts.admin')
@section('title', __('admin.Profile'))
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
                <img src="{{ $client->image_url }}" alt="user image" class="d-block ms-0 ms-sm-6 rounded user-profile-img"
                  style="height: 130px" />
              </div>
              <div class="flex-grow-1 mt-3 mt-lg-5">
                <div
                  class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-5 flex-md-row flex-column gap-4">
                  <div class="user-profile-info">
                    <h4 class="mb-2 mt-lg-6">
                      {{ 
                        $l == 'ar' ? $client->first_name_ar . ' ' . $client->last_name_ar : $client->first_name_en . ' ' . $client->last_name_en
                      }}
                    </h4>
                    <ul
                      class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-4 my-2">
                      <li class="list-inline-item d-flex gap-2 align-items-center">
                        <i class="ti ti-palette ti-lg"></i><span class="fw-medium">
                          {{ $l == 'ar' ? $client->title_ar : $client->title_en }}</span>
                        </span>
                      </li>
                      <li class="list-inline-item d-flex gap-2 align-items-center">
                        <i class="ti ti-map-pin ti-lg"></i><span
                          class="fw-medium">{{ $client->city->{'name_' . $l} }}</span>
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
                <a class="nav-link active" href="javascript:void(0);"><i class="ti-sm ti ti-user-check me-1_5"></i>
                  Profile
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="pages-profile-teams.html"><i class="ti-sm ti ti-users me-1_5"></i> Teams</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="pages-profile-projects.html"><i class="ti-sm ti ti-layout-grid me-1_5"></i>
                  Projects</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="pages-profile-connections.html"><i class="ti-sm ti ti-link me-1_5"></i>
                  Connections</a>
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
              <small class="card-text text-uppercase text-muted small">
                {{ $l == 'ar' ? 'المعلومات الشخصية' : 'About' }}
              </small>
              <ul class="list-unstyled my-3 py-1">
                <li class="d-flex align-items-center mb-4">
                  <i class="ti ti-user ti-lg"></i><span class="fw-medium mx-2">{{ $l == 'ar' ? 'الاسم:' : 'Full Name:' }}</span>
                  <span>{{ 
                  $l == 'ar' 
                  ? $client->first_name_arز . ' ' . $client->last_name_ar 
                  : $client->first_name_en . ' ' . $client->last_name_en
                  }}</span>
                </li>
                <li class="d-flex align-items-center mb-4">
                  <i class="ti ti-check ti-lg"></i><span class="fw-medium mx-2">
                    {{ $l == 'ar' ? 'الحالة' : 'Status' }}:
                  </span>
                  <span>{{ $l == 'ar' ? 'نشط' : 'Active' }}</span>
                </li>
                <li class="d-flex align-items-center mb-4">
                  <i class="ti ti-crown ti-lg"></i><span class="fw-medium mx-2">
                    {{ $l == 'ar' ? 'المسمى الوظيفي' : 'Role:' }}:
                  </span>
                  <span>
                    {{ $l == 'ar' ? $client->title_ar : $client->title_en }}
                  </span>
                </li>
                <li class="d-flex align-items-center mb-4">
                  <i class="ti ti-flag ti-lg"></i><span class="fw-medium mx-2">
                    {{ $l == 'ar' ? 'الدولة:' : 'Country:' }}
                  </span>
                  <span>
                    {{ $client->country->{'name_' . $l} }}
                  </span>
                </li>
                <li class="d-flex align-items-center mb-2">
                  <i class="ti ti-language ti-lg"></i><span class="fw-medium mx-2">
                    {{ $l == 'ar'
                        ? 'اللغات:'
                        : '
                                        Languages:' }}
                  </span>
                  <span>
                    {{ $l == 'ar' ? 'العربية, الإنجليزية' : 'Arabic, English' }}
                  </span>
                </li>
              </ul>
              <small class="card-text text-uppercase text-muted small">
                {{ $l == 'ar' ? 'التواصل' : 'Contact' }}
              </small>
              <ul class="list-unstyled my-3 py-1">
                <li class="d-flex align-items-center mb-4">
                  <i class="ti ti-phone-call ti-lg"></i><span class="fw-medium mx-2">
                    {{ $l == 'ar' ? 'الهاتف:' : 'Phone:' }}
                  </span>
                  <span>{{ $client->phone }}</span>
                </li>
                {{-- @role('Mentor')
                  <li class="d-flex align-items-center mb-4">
                    <i class="ti ti-messages ti-lg"></i><span class="fw-medium mx-2">
                      {{ $l == 'ar' ? 'ليتكدان' : 'linkedin' }}:
                    </span>
                    <span>
                      {{ $client->linkedin }}
                    </span>
                  </li>
                @endrole --}}

                <li class="d-flex align-items-center mb-4">
                  <i class="ti ti-mail ti-lg"></i><span class="fw-medium mx-2">
                    {{ $l == 'ar' ? 'البريد الإلكتروني:' : 'Email:' }}
                  </span>
                  <span>{{ $client->email }}</span>
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
          <!-- Profile Overview -->
          <div class="card mb-6">
            <div class="card-body">
              <small class="card-text text-uppercase text-muted small">Overview</small>
              <ul class="list-unstyled mb-0 mt-3 pt-1">
                <li class="d-flex align-items-end mb-4">
                  <i class="ti ti-check ti-lg"></i><span class="fw-medium mx-2">Task Compiled:</span>
                  <span>13.5k</span>
                </li>
                <li class="d-flex align-items-end mb-4">
                  <i class="ti ti-layout-grid ti-lg"></i><span class="fw-medium mx-2">Projects Compiled:</span>
                  <span>146</span>
                </li>
                <li class="d-flex align-items-end">
                  <i class="ti ti-users ti-lg"></i><span class="fw-medium mx-2">Connections:</span>
                  <span>897</span>
                </li>
              </ul>
            </div>
          </div>
          <!--/ Profile Overview -->
        </div>
        <div class="col-xl-8 col-lg-7 col-md-7">
          <!-- Activity Timeline -->
          <div class="card card-action mb-6">
            <div class="card-header align-items-center">
              <h5 class="card-action-title mb-0">
                <i class="ti ti-chart-bar ti-lg text-body me-4"></i>Activity Timeline
              </h5>
            </div>
            <div class="card-body pt-3">
              <ul class="timeline mb-0">
                <li class="timeline-item timeline-item-transparent">
                  <span class="timeline-point timeline-point-primary"></span>
                  <div class="timeline-event">
                    <div class="timeline-header mb-3">
                      <h6 class="mb-0">12 Invoices have been paid</h6>
                      <small class="text-muted">12 min ago</small>
                    </div>
                    <p class="mb-2">Invoices have been paid to the company</p>
                    <div class="d-flex align-items-center mb-2">
                      <div class="badge bg-lighter rounded d-flex align-items-center">
                        <img src="/vuexy/assets//img/icons/misc/pdf.png" alt="img" width="15"
                          class="me-2" />
                        <span class="h6 mb-0 text-body">invoices.pdf</span>
                      </div>
                    </div>
                  </div>
                </li>
                <li class="timeline-item timeline-item-transparent">
                  <span class="timeline-point timeline-point-success"></span>
                  <div class="timeline-event">
                    <div class="timeline-header mb-3">
                      <h6 class="mb-0">Client Meeting</h6>
                      <small class="text-muted">45 min ago</small>
                    </div>
                    <p class="mb-2">Project meeting with john @10:15am</p>
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                      <div class="d-flex flex-wrap align-items-center mb-50">
                        <div class="avatar avatar-sm me-3">
                          <img src="/vuexy/assets/img/avatars/1.png" alt="Avatar" class="rounded-circle" />
                        </div>
                        <div>
                          <p class="mb-0 small fw-medium">Lester McCarthy (Client)</p>
                          <small>CEO of Pixinvent</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
                <li class="timeline-item timeline-item-transparent">
                  <span class="timeline-point timeline-point-info"></span>
                  <div class="timeline-event">
                    <div class="timeline-header mb-3">
                      <h6 class="mb-0">Create a new project for client</h6>
                      <small class="text-muted">2 Day Ago</small>
                    </div>
                    <p class="mb-2">6 team members in a project</p>
                    <ul class="list-group list-group-flush">
                      <li
                        class="list-group-item d-flex justify-content-between align-items-center flex-wrap border-top-0 p-0">
                        <div class="d-flex flex-wrap align-items-center">
                          <ul class="list-unstyled users-list d-flex align-items-center avatar-group m-0 me-2">
                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                              title="Vinnie Mostowy" class="avatar pull-up">
                              <img class="rounded-circle" src="/vuexy/assets/img/avatars/1.png" alt="Avatar" />
                            </li>
                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                              title="Allen Rieske" class="avatar pull-up">
                              <img class="rounded-circle" src="/vuexy/assets/img/avatars/4.png" alt="Avatar" />
                            </li>
                            <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                              title="Julee Rossignol" class="avatar pull-up">
                              <img class="rounded-circle" src="/vuexy/assets/img/avatars/2.png" alt="Avatar" />
                            </li>
                            <li class="avatar">
                              <span class="avatar-initial rounded-circle pull-up text-heading" data-bs-toggle="tooltip"
                                data-bs-placement="bottom" title="3 more">+3</span>
                            </li>
                          </ul>
                        </div>
                      </li>
                    </ul>
                  </div>
                </li>
              </ul>
            </div>
          </div>
          <!--/ Activity Timeline -->
          <div class="row">
            <!-- Connections -->
            <div class="col-lg-12 col-xl-6">
              <div class="card card-action mb-6">
                <div class="card-header align-items-center">
                  <h5 class="card-action-title mb-0">Connections</h5>
                  <div class="card-action-element">
                    <div class="dropdown">
                      <button type="button"
                        class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow p-0 text-muted"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-md text-muted"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="javascript:void(0);">Share connections</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);">Suggest edits</a></li>
                        <li>
                          <hr class="dropdown-divider" />
                        </li>
                        <li><a class="dropdown-item" href="javascript:void(0);">Report bug</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <ul class="list-unstyled mb-0">
                    <li class="mb-4">
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center">
                          <div class="avatar me-2">
                            <img src="/vuexy/assets/img/avatars/2.png" alt="Avatar" class="rounded-circle" />
                          </div>
                          <div class="me-2">
                            <h6 class="mb-0">Cecilia Payne</h6>
                            <small>45 Connections</small>
                          </div>
                        </div>
                        <div class="ms-auto">
                          <button class="btn btn-label-primary btn-icon">
                            <i class="ti ti-user-check ti-md"></i>
                          </button>
                        </div>
                      </div>
                    </li>
                    <li class="mb-4">
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center">
                          <div class="avatar me-2">
                            <img src="/vuexy/assets/img/avatars/3.png" alt="Avatar" class="rounded-circle" />
                          </div>
                          <div class="me-2">
                            <h6 class="mb-0">Curtis Fletcher</h6>
                            <small>1.32k Connections</small>
                          </div>
                        </div>
                        <div class="ms-auto">
                          <button class="btn btn-primary btn-icon"><i class="ti ti-user-x ti-md"></i></button>
                        </div>
                      </div>
                    </li>
                    <li class="mb-4">
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center">
                          <div class="avatar me-2">
                            <img src="/vuexy/assets/img/avatars/10.png" alt="Avatar" class="rounded-circle" />
                          </div>
                          <div class="me-2">
                            <h6 class="mb-0">Alice Stone</h6>
                            <small>125 Connections</small>
                          </div>
                        </div>
                        <div class="ms-auto">
                          <button class="btn btn-primary btn-icon"><i class="ti ti-user-x ti-md"></i></button>
                        </div>
                      </div>
                    </li>
                    <li class="mb-4">
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center">
                          <div class="avatar me-2">
                            <img src="/vuexy/assets/img/avatars/7.png" alt="Avatar" class="rounded-circle" />
                          </div>
                          <div class="me-2">
                            <h6 class="mb-0">Darrell Barnes</h6>
                            <small>456 Connections</small>
                          </div>
                        </div>
                        <div class="ms-auto">
                          <button class="btn btn-label-primary btn-icon">
                            <i class="ti ti-user-check ti-md"></i>
                          </button>
                        </div>
                      </div>
                    </li>

                    <li class="mb-6">
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center">
                          <div class="avatar me-2">
                            <img src="/vuexy/assets/img/avatars/12.png" alt="Avatar" class="rounded-circle" />
                          </div>
                          <div class="me-2">
                            <h6 class="mb-0">Eugenia Moore</h6>
                            <small>1.2k Connections</small>
                          </div>
                        </div>
                        <div class="ms-auto">
                          <button class="btn btn-label-primary btn-icon">
                            <i class="ti ti-user-check ti-md"></i>
                          </button>
                        </div>
                      </div>
                    </li>
                    <li class="text-center">
                      <a href="javascript:;">View all connections</a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <!--/ Connections -->
            <!-- Teams -->
            <div class="col-lg-12 col-xl-6">
              <div class="card card-action mb-6">
                <div class="card-header align-items-center">
                  <h5 class="card-action-title mb-0">Teams</h5>
                  <div class="card-action-element">
                    <div class="dropdown">
                      <button type="button" class="btn btn-icon btn-text-secondary dropdown-toggle hide-arrow p-0"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical text-muted"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="javascript:void(0);">Share teams</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);">Suggest edits</a></li>
                        <li>
                          <hr class="dropdown-divider" />
                        </li>
                        <li><a class="dropdown-item" href="javascript:void(0);">Report bug</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <ul class="list-unstyled mb-0">
                    <li class="mb-4">
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center">
                          <div class="avatar me-2">
                            <img src="/vuexy/assets/img/icons/brands/react-label.png" alt="Avatar"
                              class="rounded-circle" />
                          </div>
                          <div class="me-2">
                            <h6 class="mb-0">React Developers</h6>
                            <small>72 Members</small>
                          </div>
                        </div>
                        <div class="ms-auto">
                          <a href="javascript:;"><span class="badge bg-label-danger">Developer</span></a>
                        </div>
                      </div>
                    </li>
                    <li class="mb-4">
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center">
                          <div class="avatar me-2">
                            <img src="/vuexy/assets/img/icons/brands/support-label.png" alt="Avatar"
                              class="rounded-circle" />
                          </div>
                          <div class="me-2">
                            <h6 class="mb-0">Support Team</h6>
                            <small>122 Members</small>
                          </div>
                        </div>
                        <div class="ms-auto">
                          <a href="javascript:;"><span class="badge bg-label-primary">Support</span></a>
                        </div>
                      </div>
                    </li>
                    <li class="mb-4">
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center">
                          <div class="avatar me-2">
                            <img src="/vuexy/assets/img/icons/brands/figma-label.png" alt="Avatar"
                              class="rounded-circle" />
                          </div>
                          <div class="me-2">
                            <h6 class="mb-0">UI Designers</h6>
                            <small>7 Members</small>
                          </div>
                        </div>
                        <div class="ms-auto">
                          <a href="javascript:;"><span class="badge bg-label-info">Designer</span></a>
                        </div>
                      </div>
                    </li>
                    <li class="mb-4">
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center">
                          <div class="avatar me-2">
                            <img src="/vuexy/assets/img/icons/brands/vue-label.png" alt="Avatar"
                              class="rounded-circle" />
                          </div>
                          <div class="me-2">
                            <h6 class="mb-0">Vue.js Developers</h6>
                            <small>289 Members</small>
                          </div>
                        </div>
                        <div class="ms-auto">
                          <a href="javascript:;"><span class="badge bg-label-danger">Developer</span></a>
                        </div>
                      </div>
                    </li>
                    <li class="mb-6">
                      <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center">
                          <div class="avatar me-2">
                            <img src="/vuexy/assets/img/icons/brands/twitter-label.png" alt="Avatar"
                              class="rounded-circle" />
                          </div>
                          <div class="me-w">
                            <h6 class="mb-0">Digital Marketing</h6>
                            <small>24 Members</small>
                          </div>
                        </div>
                        <div class="ms-auto">
                          <a href="javascript:;"><span class="badge bg-label-secondary">Marketing</span></a>
                        </div>
                      </div>
                    </li>
                    <li class="text-center">
                      <a href="javascript:;">View all teams</a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <!--/ Teams -->
          </div>
          <!-- Projects table -->
          <div class="card mb-4">
            <div class="card-datatable table-responsive">
              <table class="datatables-projects table border-top">
                <thead>
                  <tr>
                    <th></th>
                    <th></th>
                    <th>Project</th>
                    <th>Leader</th>
                    <th>Team</th>
                    <th class="w-px-200">Progress</th>
                    <th>Action</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
          <!--/ Projects table -->
        </div>
      </div>
      <!--/ User Profile Content -->
    </div>
    <!-- / Content -->

    <div class="content-backdrop fade"></div>
  </div>
  <!-- Content wrapper -->
@stop

<!-- Page JS -->
@section('pagescripts')
  <script src="/vuexy/assets/js/pages-profile.js"></script>

  <script>
    $(document).ready(function() {
      getStates($('#country_id').val());
      $('#country_id').change(function() {
        getStates($(this).val());
      });
      $('#state_id').change(function() {
        getCities($(this).val());
      });
    });

    function getStates(country_id) {
      var $state = $('#state_id');
      var currentState = '{{ $client->state_id }}';
      var selected = '';
      $.ajax({
        url: "{{ route('get_states_by_country') }}",
        data: {
          country_id: country_id
        },
        success: function(data) {
          $state.html('<option value="" >{{ __('admin.State') }}</option>');
          $.each(data, function(id, value) {
            // selected = '';
            if (currentState === id) {
              selected = 'selected';
              getCities(id)
            } else {
              selected = '';
            }
            $state.append('<option value="' + id + '" ' + selected + '>' + value + '</option>');
            $('#city_id').html('<option value="" >{{ __('admin.City') }}</option>');
          });
          $('#state').show(150);
        }
      });
    }

    function getCities(state_id) {
      var $city = $('#city_id');
      var currentCity = '{{ $client->city_id }}';
      var selected = '';
      $.ajax({
        url: "{{ route('cities.index') }}",
        data: {
          state_id: state_id
        },
        success: function(data) {
          $city.html('<option value="" >{{ __('admin.City') }}</option>');
          $.each(data, function(id, value) {
            if (currentCity == id) {
              selected = 'selected';
            } else {
              selected = '';
            }
            $city.append('<option value="' + id + '" ' + selected + ' >' + value + '</option>');
          });
          $('#city').show(150);
        }
      });
    }
  </script>
  @role('Mentor')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.2/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      var useDarkMode = window.matchMedia('(prefers-color-scheme: light)').matches;

      tinymce.init({
        selector: '#basicTextarea',
        plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount textpattern noneditable help charmap quickbars emoticons',
        imagetools_cors_hosts: ['picsum.photos'],
        menubar: 'edit view insert format tools table help',
        toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview  print | insertfile image media template link anchor codesample | ltr rtl',
        toolbar_sticky: true,
        autosave_ask_before_unload: true,
        autosave_interval: '30s',
        autosave_prefix: '{path}{query}-{id}-',
        autosave_restore_when_empty: false,
        autosave_retention: '2m',
        image_advtab: true,
        importcss_append: true,
        template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
        template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
        height: 400,
        image_caption: true,
        quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
        noneditable_noneditable_class: 'mceNonEditable',
        toolbar_mode: 'sliding',
        contextmenu: 'link image imagetools table',
        skin: useDarkMode ? 'oxide-dark' : 'oxide',
        content_css: useDarkMode ? 'dark' : 'default',
        content_style: 'body { font-family:Times New Roman,Arial,sans-serif; font-size:14px }',
        image_title: true,
        automatic_uploads: true,
        media_live_embeds: true,
        relative_urls: false,
        remove_script_host: false,
        images_upload_url: "{{ route('training.upload', ['_token' => csrf_token()]) }}",
        file_picker_types: 'image',
        convert_urls: true,
        file_picker_callback: function(cb, value, meta) {
          var input = document.createElement('input');
          input.setAttribute('type', 'file');
          input.setAttribute('accept', 'image/*');
          input.onchange = function() {
            var file = this.files[0];
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function() {
              var id = 'blobid' + (new Date()).getTime();
              var blobCache = tinymce.activeEditor.editorUpload.blobCache;
              var base64 = reader.result.split(',')[1];
              var blobInfo = blobCache.create(id, file, base64);
              blobCache.add(blobInfo);
              cb(blobInfo.blobUri(), {
                title: file.name
              });
            };
          };
          input.click();
        }
      });
      @if ($l == 'ar')
        tinyMCE.settings['directionality'] = 'rtl';
      @endif
    </script>
    <script>
      $(document).ready(function() {
        @if ($client->bio)
          switch ('{{ $client->bio->entrepreneurial_experience }}') {
            case '1':
              $('#entrepreneurial_experience_brief').show();
              break;
            case '0':
              $('#entrepreneurial_experience_brief').hide();
              break;
            default:
              $('#entrepreneurial_experience_brief').hide();
              break;
          }
        @endif

        $('.entrepreneurial_experience').change(function() {
          var value = $(this).val();
          switch (value) {
            case '1':
              $('#entrepreneurial_experience_brief').show();
              break;
            case '0':
              $('#entrepreneurial_experience_brief').hide();
              break;
            default:
              $('#entrepreneurial_experience_brief').hide();
              break;
          }
        });
      });
    </script>
  @endrole
@stop
