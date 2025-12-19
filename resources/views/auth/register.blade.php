<html 
  lang="{{ $l }}" 
  dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" 
  data-theme="theme-default"
  data-assets-path="/vuexy/assets/"
  data-template="vertical-menu-template" 
  data-style="light"
>

<head>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta name="description" content="Register a new account">
  <meta name="author" content="info@growthlabs.app">
  <title>
    {{ $l == 'en' ? 'Register New Account' : 'تسجيل حساب جديد'}}
  </title>
  <link rel="apple-touch-icon" href="/app-assets/images/ico/apple-icon-120.png">
  <link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">


  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/fontawesome.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/tabler-icons.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/flag-icons.css" />
  <!-- End Icons -->

  <!-- Core CSS -->

  <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />

  <link rel="stylesheet" href="/vuexy/assets/css/demo.css" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
  
  <!-- Page CSS -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-auth.css" />

  <style>
    .iti__country-list {
      position: absolute;
      left: 1rem;
      /* right: 0; */
      z-index: 9999;
      list-style: none;
      text-align: left;
      padding: 0;
      margin: 0 0 0 -1px;
      box-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2);
      background-color: white;
      border: 1px solid #CCC;
      white-space: nowrap;
      max-height: 200px;
      width: 350px;
      overflow-y: scroll;
      overflow-x: auto -webkit-overflow-scrolling: touch;
      background-color: #fff;
    }
  </style>

  <!-- Helpers -->
  <script src="/vuexy/assets/vendor/js/helpers.js"></script>
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
  <script src="/vuexy/assets/vendor/js/template-customizer.js"></script>

  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="/vuexy/assets/js/config.js"></script>
</head>
<!-- END: Head-->
<!-- BEGIN: Body-->

<body>
  @php
  $setting = App\Settings::first();
  $logo =setting('Logo','images/background/logo.png');
  $accountRoles = ['Founder', 'Mentor'];
  $termsUrl = setting('terms_url','#') ;
  $privacyUrl = setting('privacy_url','#');
  @endphp

  <!-- Content -->
  <!-- Toggle theme and language -->
  <nav 
    class="container-xxl navbar navbar-expand-xl align-items-center"
    style="right: 1rem;"
    id="switcher-container"
  >
    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
      <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!-- Language -->
        <li class="nav-item dropdown-language dropdown" style="position: relative;">
          <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
            href="javascript:void(0);" data-bs-toggle="dropdown">
            <i class="ti ti-language rounded-circle ti-md"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" 
            style="position: absolute; left: -8rem; top: 2rem;"
          >
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
          </ul>
        </li>
        <!--/ Language -->

        <!-- Style Switcher -->
        <li class="nav-item dropdown-style-switcher dropdown" style="position: relative;">
          <a class="nav-link btn-icon rounded-pill dropdown-toggle hide-arrow"
            href="javascript:void(0);" data-bs-toggle="dropdown">
            <i class="ti ti-md"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-styles" 
            style="position: absolute; left: -8rem; width: 150px;"
          >
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
      </ul>
    </div>
  </nav>
  
  <!-- Toggle theme and language -->  
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="register-card-wrapper py-6">
        <!-- Register Card -->
        <div class="card mt-6 mt-lg-0">
          <div class="px-4 px-md-4 py-6">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-4">
              <a href="/dashboard" class="app-brand-link">
                <img src="{{$logo}}" alt="avatar" width="150">
              </a>
            </div>
            <!-- /Logo -->

            <h4 class="mb-1 text-center lh-base text-capitalize">{{ __('auth.formHeading') }} 🚀</h4>
            <p class="mb-6 text-center">{{ __('auth.formSubHeading') }}</p>

              @if (App\Settings::findOrFail(1)->set_order)
                <form id="registerForm" class="mb-6 px-md-8" method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                  @csrf
                  <div class="row">
                    <!-- Switch Role -->
       
                    @foreach($accountRoles as $key => $role)
                   
                    <div class="col-6 mb-4">
                      <label for="{{$role}}Role" style="display: grid; place-items: center;" class="form-check custom-option custom-option-icon cursor-pointer">
                        <div class="d-flex align-items-center">
                          @if($role === 'Founder')
                            <input name="role" class="form-check-input role-input" type="radio" value="registered" id="{{$role}}Role" checked required />
                            <div class="form-check-label custom-option-content d-flex align-items-center">
                              <i class="ti ti-businessplan me-2"></i>
                              <span class="custom-option-title">{{ __('admin.'. $role) }}</span>
                            </div>
                          @else
                            <input name="role" class="form-check-input role-input" type="radio" value="mentor" id="{{$role}}Role" required />
                            <div class="form-check-label custom-option-content d-flex align-items-center">
                              <i class="ti ti-user me-2"></i>
                              <span class="custom-option-title">{{ __('admin.'. $role) }}</span>
                            </div>
                          @endif
                        </div>
                      </label>
                    </div>
                    @endforeach
                    <!--/ Switch Role -->

                    <div>
                      @if (session('error'))
                        <div class="row">
                          <div class="col-md-12">
                            <div class="alert alert-danger">
                              <ul>
                                <li>{{ session('error') }} </li>
                              </ul>
                            </div>
                          </div>
                        </div>
                      @endif

                      @if ($errors->any())
                        <div class="row">
                          <div class="col-md-12">
                            <div class="alert alert-danger">
                              <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                  <li>{{ $error }}</li>
                                @endforeach
                              </ul>
                            </div>
                          </div>
                        </div>
                      @endif

                      <div class="col-12 mb-6">
                        <label class="form-label fs-6 fw-bold d-block text-{{ $l == 'ar' ? 'end' : 'start' }}">
                          {{ $l == 'en' ? 'Profile photo' : 'الصورة الشخصية' }}
                        </label>
                        <div id="uploader" class="uploader" tabindex="0">
                          <div class="d-flex gap-5 align-items-center justify-content-center">
                            <img id="avatarPreview" class="avatar-preview" src="/images/user-avatar.png" alt="avatar preview">
                            <div class="text-{{ $l == 'ar' ? 'end' : 'start' }}">
                              <strong id="uploaderTitle">
                                {{ $l == 'en' ? 'Drag & drop or click to upload' : 'اسحب وأفلت أو انقر للتحميل' }}
                              </strong>
                              <div class="small text-muted">
                                {{ $l == 'en' ? 'PNG, JPG, JPEG up to 10 MB' : 'PNG، JPG، JPEG بحد أقصى 10 MB' }}
                              </div>
                              <div class="mt-2 text-start">
                                <button type="button" id="chooseBtn" class="btn btn-sm btn-label-primary">
                                  {{ $l == 'en' ? 'Upload' : 'تحميل' }}
                                  <i class="ti ti-upload ti-xs ms-1"></i>
                                </button>
                                <button type="button" id="removeBtn" class="btn btn-sm btn-label-secondary d-none">
                                  {{ $l == 'en' ? 'Reset' : 'حذف' }}
                                  <i class="ti ti-reload ti-xs ms-1"></i>
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>

                        <input id="fileInput" class="file-input" type="file" accept="image/png, image/jpeg" name="img_url">

                        <div class="mt-3">
                          <div class="progress" style="height:6px; display:none;">
                            <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                          </div>
                          @error('img_url')
                            <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                          @enderror
                        </div>
                      </div>

                      <div class="col-12 mb-3">
                        <label for="first_name" class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ __('auth.firstName') }}</label>
                        <div class="input-group input-group-merge">
                          <input name="first_name" type="text" class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}" id="first_name" placeholder="{{ __('auth.First English Name') }}" required autofocus />
                          @error('first_name')
                          <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                        @enderror
                        </div>
                      </div>

                      <div class="col-12 mb-3">
                        <label for="last_name" class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ __("auth.secondName") }}</label>
                        <div class="input-group input-group-merge">
                          <input name="last_name" type="text" class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}" id="last_name" placeholder="{{ __('auth.Last English Name') }}" required />
                        </div>
                        @error('last_name')
                          <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-12 mb-3">
                        <label for="email" class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ __('auth.E-Mail') }}</label>
                        <div class="input-group input-group-merge">
                          <input name="email" type="email" class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}" placeholder="{{ __('auth.E-Mail') }}" required autocomplete="off" />
                        </div>
                        @error('email')
                          <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-12 mb-4 form-password-toggle">
                        <label class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}" for="password">{{ __('auth.Password') }}</label>
                        <div class="input-group input-group-merge">
                          <input type="password" class="form-control" id="password" placeholder="{{ __('auth.Password') }}" name="password" required aria-describedby="password" autocomplete="new-password" />
                          <span class="input-group-text cursor-pointer border border-light"><i class="ti ti-eye-off"></i></span>
                        </div>
                        @error('password')
                          <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-12 mb-4 form-password-toggle">
                        <label class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}" for="password_confirmation">{{ __('auth.Confirm') }}</label>
                        <div class="input-group input-group-merge">
                          <input type="password" class="form-control" id="password_confirmation" placeholder="{{ __('auth.Password') }}" name="password_confirmation" required aria-describedby="new-password" autocomplete="new-password" />
                          <span class="input-group-text cursor-pointer border border-light"><i class="ti ti-eye-off"></i></span>
                        </div>
                        @error('password_confirmation')
                          <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-12 mb-4">
                        <label class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}" for="gender">{{ __('auth.gender') }}</label>
                        <select class="form-select select2 text-{{ $l == 'ar' ? 'end' : 'start' }}" name="gender" id="gender" required>
                          <option value="Male">{{ __('Male') }}</option>
                          <option value="Female">{{ __('Female') }}</option>
                        </select>

                        @error('gender')
                          <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-12 mb-4 d-grid">
                        <label class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}" for="phone">{{ __('auth.Phone') }}</label>
                        <input id="phone" type="tel" class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}" placeholder="{{ __('auth.Phone') }}" name="phone" required />
                        <small class="text-danger mt-1" id="phone-error" style="display: none;"></small>
                        <input type="hidden" name="country_code" id="phone_code" value="20">
                        @error('phone')
                          <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                        @enderror
                        @error('country_code')
                          <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                        @enderror
                      </div>
                      <div id="mentorInputsWrapper" style="display: none;">
                        <div class="col-12 mb-3">
                          <label for="Title" class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ __('admin.JobTitleEn') }}</label>
                          <div class="input-group input-group-merge">
                            <input name="title_en" type="text" class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}" id="Title" placeholder="{{ __('admin.JobTitleEn') }}" autocomplete="title_en" oninput="this.value = this.value.trimStart()" />
                          </div>
                          @error('title_en')
                          <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                          @enderror
                        </div>

                      <div class="col-12 mb-3">
                        <label class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}" for="expertise">{{ __('admin.Expertises') }}</label>
                        <div class="form-group">
                          <select name="expertise[]" class="select2 form-select text-{{ $l == 'ar' ? 'end' : 'start' }}" multiple="multiple" id="expertise" data-placeholder="{{__('admin.Expertises')}}" required>
                            @php
                            $expertises = App\Expertise::where('parent_id', null)->get();
                            @endphp
                            @foreach ($expertises as $expertise_group)
                            <optgroup label="{{ $expertise_group->title }}">
                              @foreach ($expertise_group->children as $expertise)
                              <option value="{{ $expertise->id }}"> {{ $expertise->title }} </option>
                              @endforeach
                            </optgroup>
                            @endforeach
                          </select>
                        </div>
                        @error('expertise')
                          <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-12 mb-3">
                        <label for="bio_en" class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ __('admin.userbio') }}</label>
                        <div class="input-group">
                          <textarea name="bio_en" type="text" class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}" id="bio_en" placeholder="{{ __('admin.userbio') }}" autocomplete="userbio" oninput="this.value = this.value.trimStart()"></textarea>
                        </div>
                        @error('bio_en')
                          <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-12 mb-3">
                        <label for="Linkedin" class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ __('admin.Linkedin') }}</label>
                        <div class="input-group">
                          <input value="{{ old('Linkedin') }}" name="Linkedin" type="url" class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}" id="Linkedin" placeholder="{{ __('admin.Linkedin') }}" autocomplete="Linkedin" />
                        </div>
                        @error('Linkedin')
                          <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>

                      <div class="mb-6 mt-2">
                        @if(!empty($termsUrl) && !empty($privacyUrl))
                            <p class="ms-2">
                                {!! __('admin.signup_agreement', [
                                    'terms' => '<a href="'. $termsUrl .'" target="_blank">'. __('admin.terms') .'</a>',
                                    'privacy' => '<a href="'. $privacyUrl .'" target="_blank">'. __('admin.privacy') .'</a>'
                                ]) !!}
                            </p>
                        @elseif(!empty($termsUrl))
                            <p class="ms-2">
                                {!! __('admin.signup_agreement_terms', [
                                    'terms' => '<a href="'. $termsUrl .'" target="_blank">'. __('admin.terms') .'</a>'
                                ]) !!}
                            </p>
                        @elseif(!empty($privacyUrl))
                            <p class="ms-2">
                                {!! __('admin.signup_agreement_privacy', [
                                    'privacy' => '<a href="'. $privacyUrl .'" target="_blank">'. __('admin.privacy') .'</a>'
                                ]) !!}
                            </p>
                        @endif
                    </div>
                  
                    <div class="col-12">
                      <button type="submit" class="btn btn-primary w-100 mb-4"> {{ __('auth.Register') }}</button>
                    </div>
                  </div>
                </form>
              @else
                <div class="alert alert-warning text-center col-12">
                  <span class="">{{ __('admin.Register_Disabled') }}</span>
                </div>
              @endif

            <p class="text-center">
              <span>{{ __('Already have account') }}{{ $l == 'ar' ? '؟' : '?' }}</span>
              <a href="{{ route('login') }}">
                <span>{{ $l == 'en' ? 'Sign in instead' : 'سجل الدخول' }}</span>
              </a>
            </p>
            @if ((isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled') ||(isset($setting) && ($setting->GOOGLE_LOGIN ?? 'disabled') === 'enabled'))
            <div class="divider my-6 mx-auto" style="width: 80%;">
              <div class="divider-text">{{ __('auth.or') }}</div>
            </div>
            @endif
            @php
            $data = request('data') ?? '2521';
        @endphp
            <div class="d-flex justify-content-center">
             @if(isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled')
              <a href="{{ route('integrations.auth_linkedin_login', $data) }}" class="btn btn-sm btn-icon rounded-circle btn-text-facebook me-3 shadow-sm" style="background-color: #0077b5;">
                <i class="fa-brands fa-linkedin-in text-white"></i>
              </a>
              @endif
              @if(isset($setting) && ($setting->GOOGLE_LOGIN ?? 'disabled') === 'enabled')
              <a href="{{ url('/auth/redirect/google') }}" class="btn btn-sm bg-white border btn-icon rounded-circle btn-text-google-plus shadow-sm">
                <img src="/icons/google-icon.svg" alt="google" width="20" height="20">
              </a>
              @endif
            </div>
          </div>
        </div>
        <!--/ Register Card -->
      </div>
    </div>
  </div>
  <!-- / Content -->

  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
  <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
  <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
  <!-- endbuild -->

  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>

  <!-- Main JS -->
  <script src="/vuexy/assets/js/main.js"></script>

  <!-- Page JS -->
  <script src="/vuexy/assets/js/pages-auth.js"></script>
  <script src="/vuexy/assets/js/forms-selects.js"></script>

  <script>



  </script>
<script>
// Role-specific configuration
const ROLE_CONFIG = {
  'registered': {
    name: 'founder',
    hash: '2521',
    type: '3',
    showMentorFields: false
  },
  'mentor': {
    name: 'mentor',
    hash: '2520',
    type: 'mentor',
    showMentorFields: true
  }
};

const mentorWrapper = document.getElementById('mentorInputsWrapper');
const mentorFields = mentorWrapper.querySelectorAll('input, select, textarea');

// Update mentor fields visibility based on role
function updateMentorFieldsVisibility(selectedRole) {
  const config = ROLE_CONFIG[selectedRole];
  
  if (config.showMentorFields) {
    mentorWrapper.style.display = 'block';
    mentorFields.forEach(f => {
      f.disabled = false;
      f.removeAttribute('disabled');
    });
  } else {
    mentorWrapper.style.display = 'none';
    mentorFields.forEach(f => {
      f.disabled = false;
      f.removeAttribute('disabled');
    });
  }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  const type = urlParams.get('type');
  const data = urlParams.get('data');

  const mentorRadio = document.getElementById('MentorRole');

  if ((type === 'mentor' || data === '2520') && mentorRadio) {
    mentorRadio.checked = true;
    // We need to trigger the change event to make sure other scripts depending on it run.
    mentorRadio.dispatchEvent(new Event('change'));
  } else {
    // Manually trigger for the default case
    const checkedRole = document.querySelector('input[name="role"]:checked');
    if (checkedRole) {
      updateMentorFieldsVisibility(checkedRole.value);
    }
  }
});

// Handle role change
document.querySelectorAll('.role-input').forEach(input => {
  input.addEventListener('change', function() {
    const selectedRole = this.value;
    const config = ROLE_CONFIG[selectedRole];
    
    updateMentorFieldsVisibility(selectedRole);
    
    // Update URL with role data
    // const url = new URL(window.location.href);
    // url.searchParams.set('type', config.type);
    // url.searchParams.set('data', config.hash);
    // window.history.pushState({}, '', url);
  });
});
</script>

<script>
  // Elements
  const uploader = document.getElementById('uploader');
  const fileInput = document.getElementById('fileInput');
  const chooseBtn = document.getElementById('chooseBtn');
  const removeBtn = document.getElementById('removeBtn');
  const avatarPreview = document.getElementById('avatarPreview');
  const progressBarWrap = document.querySelector('.progress');
  const progressBar = document.getElementById('progressBar');

  // Helpers
  function resetUploader() {
    avatarPreview.src = '/images/user-avatar.png';
    removeBtn.classList.add('d-none');
    progressBar.style.width = '0%';
    progressBarWrap.style.display = 'none';
  }

  function handleFile(file) {
    // Read with progress
    const reader = new FileReader();
    progressBarWrap.style.display = 'block';
    progressBar.style.width = '0%';

    reader.onprogress = (e) => {
      if (e.lengthComputable) {
        const percent = Math.round((e.loaded / e.total) * 100);
        progressBar.style.width = percent + '%';
      }
    };

    reader.onload = () => {
      // preview + save base64 hidden field
      avatarPreview.src = reader.result;
      removeBtn.classList.remove('d-none');
      progressBar.style.width = '100%';
      setTimeout(() => { progressBarWrap.style.display = 'none'; }, 600);
    };

    reader.onerror = () => {
      progressBarWrap.style.display = 'none';
    };

    reader.readAsDataURL(file);
  }

  // Click handlers
  uploader.addEventListener('click', () => fileInput.click());
  chooseBtn.addEventListener('click', (e) => { e.stopPropagation(); fileInput.click(); });

  removeBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    fileInput.value = '';
    resetUploader();
  });

  fileInput.addEventListener('change', (e) => {
    if (e.target.files && e.target.files[0]) handleFile(e.target.files[0]);
  });

  // Drag & drop
  ['dragenter','dragover'].forEach(evt => {
    uploader.addEventListener(evt, (e) => {
      e.preventDefault(); e.stopPropagation();
      uploader.classList.add('dragover');
    });
  });
  ['dragleave','drop'].forEach(evt => {
    uploader.addEventListener(evt, (e) => {
      e.preventDefault(); e.stopPropagation();
      uploader.classList.remove('dragover');
    });
  });

  uploader.addEventListener('drop', (e) => {
    const dt = e.dataTransfer;
    if (!dt) return;
    const file = dt.files && dt.files[0];
    if (file) handleFile(file);
  });

  // Keyboard accessibility: allow Enter to open file dialog
  uploader.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); }
  });

  // init
  resetUploader();
</script>
</body>
</html>