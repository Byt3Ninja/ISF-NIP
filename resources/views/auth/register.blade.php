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
  <meta name="description" content="VI Login">
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
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/node-waves/node-waves.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/typeahead-js/typeahead.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/swiper/swiper.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
  
  <!-- Page CSS -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-auth.css" />

  <style>
    div[data-validator] {
      width: 100% !important;
      text-align: {{ $l == 'ar' ? 'end' : 'start' }} !important;
    }

    #switcher-container {
      position: absolute;
      top: 1rem;
      width: 100px;
    }

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
  $accountRoles = ['Mentor', 'Founder'];
  $termsUrl = setting('terms_url','#') ;
  $privacyUrl = setting('privacy_url','#');
  @endphp

  @if (App\Settings::findOrFail(1)->set_order)
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
      <div class="authentication-inner py-6">
        <!-- Register Card -->
        <div class="card">
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

            
            <form id="registerForm" class="mb-6 px-md-8" method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <!-- Switch Role -->
        
                @foreach($accountRoles as $key => $role)
                <div class="col-6 mb-4">
                  <label for="{{$role}}Role" style="display: grid; place-items: center;" class="form-check custom-option custom-option-icon cursor-pointer">
                    <div class="d-flex align-items-center">
                      <input name="role" class="form-check-input role-input" type="radio" value="{{$key == 0 ? 4 : '3'}}" id="{{$role}}Role" checked={{ $key==0 ? 'true' : '' }} required />
                      <div class="form-check-label custom-option-content d-flex align-items-center">
                        <i class="ti ti-{{ $key==0 ? 'user' : 'businessplan' }} me-2"></i>
                        <span class="custom-option-title">{{ __('admin.'. $role) }}</span>
                      </div>
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
                
                <div class="col-12 mb-6">
                  <label for="file1" class="d-flex justify-content-center align-items-center position-relative rounded"
                    style="height: 140px; cursor: pointer; width: 200px; margin: 0 auto;">
                    <img 
                      src="{{'/images/user-placeholder.jpeg' }}"
                      onerror="this.src='/images/user-placeholder.jpeg'"
                      alt="Organization Logo" 
                      class="rounded img-fluid border shadow-sm"
                      style="height: 140px; width: 200px; object-fit: cover;" 
                    />
                    <input type="file" class="form-control d-none" id="file1" name="img_url">
                    <i class="ti ti-camera-plus position-absolute fs-3 text-dark end-0 bottom-0 m-4 z-1"></i>
                  </label>
                  @error('img_url')
                    <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                  @enderror
                </div>


                <div class="col-12 mb-3">
                  <label for="first_name_en" class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ __('auth.firstName') }}</label>
                  <div class="input-group input-group-merge">
                    <input name="first_name" type="text" class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}" id="first_name_en" placeholder="{{ __('auth.First English Name') }}" required autofocus />
                    @error('first_name')
                    <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                  @enderror
                  </div>
                </div>

                <div class="col-12 mb-3">
                  <label for="last_name_en" class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ __("auth.secondName") }}</label>
                  <div class="input-group input-group-merge">
                    <input name="last_name" type="text" class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}" id="last_name_en" placeholder="{{ __('auth.Last English Name') }}" required />
                  </div>
                  @error('last_name')
                    <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-12 mb-3">
                  <label for="email" class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ __('auth.E-Mail') }}</label>
                  <div class="input-group input-group-merge">
                    <input name="email" type="email" class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}" placeholder="{{ __('auth.E-Mail') }}" required />
                  </div>
                  @error('email')
                    <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-12 mb-4 form-password-toggle">
                  <label class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}" for="password">{{ __('auth.Password') }}</label>
                  <div class="input-group input-group-merge">
                    <input type="password" class="form-control" id="password" placeholder="{{ __('auth.Password') }}" name="password" required aria-describedby="password" />
                    <span class="input-group-text cursor-pointer border border-light"><i class="ti ti-eye-off"></i></span>
                  </div>
                  @error('password')
                    <div class="invalid-feedback text-{{ $l == 'ar' ? 'end' : 'start' }}">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-12 mb-4 form-password-toggle">
                  <label class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}" for="password_confirmation">{{ __('auth.Confirm') }}</label>
                  <div class="input-group input-group-merge">
                    <input type="password" class="form-control" id="password_confirmation" placeholder="{{ __('auth.Password') }}" name="password_confirmation" required aria-describedby="new-password" />
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
                    <label class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}" for="expertise">{{ __('admin.Industry Expertise') }}</label>
                    <div class="form-group">
                      <select name="expertise[]" class="select2 form-select text-{{ $l == 'ar' ? 'end' : 'start' }}" multiple="multiple" id="expertise" data-placeholder="{{__('admin.Select Expertise')}}" required>
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
      </div>
      <!--/ Register Card -->
    </div>
  </div>
  <!-- / Content -->
  @endif

  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->

  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
  <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
  {{-- <script src="/vuexy/assets/vendor/libs/popper/popper.js"></script> --}}
  <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>
  <script src="/vuexy/assets/vendor/libs/node-waves/node-waves.js"></script>
  <script src="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="/vuexy/assets/vendor/libs/hammer/hammer.js"></script>
  <script src="/vuexy/assets/vendor/libs/i18n/i18n.js"></script>
  <script src="/vuexy/assets/vendor/libs/typeahead-js/typeahead.js"></script>
  <script src="/vuexy/assets/vendor/js/menu.js"></script>
  <script src="/app-assets/js/1.js"></script>
  <!-- endbuild -->

  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/bootstrap-select/bootstrap-select.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
  {{-- <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script> --}}

  <!-- Main JS -->
  <script src="/vuexy/assets/js/main.js"></script>

  <!-- Page JS -->
  <script src="/vuexy/assets/js/pages-auth.js"></script>
  <script src="/vuexy/assets/js/forms-selects.js"></script>
  {{-- <script src="/vuexy/assets/js/pages-account-settings-account.js"></script> --}}

  <script>
    const lang = '{{ $l }}';

    // Initialize the phone input
    const phoneInput = document.getElementById("phone");
    const phoneError = document.getElementById('phone-error');
    const countryCodeInput = document.querySelector("#phone_code");

    const iti = intlTelInput(phoneInput, {
      initialCountry: 'eg',
      preferredCountries: ["eg", "ae", "sa", "ma", "dz", "tn"],
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
      formatOnDisplay: false,
      separateDialCode: true,
      autoPlaceholder: "aggressive",
      
      customPlaceholder: (placeholder) => "e.g. " + placeholder,
    });

        // Set initial country code
    countryCodeInput.value = iti.getSelectedCountryData().dialCode;

    // Update country code when country changes
    phoneInput.addEventListener('countrychange', function() {
      const countryData = iti.getSelectedCountryData();
      countryCodeInput.value = countryData.dialCode;
    });

    // Optional: Handle validation
    phoneInput.addEventListener("input", (e) => {
      console.log(e.target.value);
      if (!iti.isValidNumber()) {
        phoneError.style.display = 'block';
        phoneError.innerText = lang == 'en' ? 'Invalid phone number' : 'رقم الهاتف غير صحيح';
      } else {
        phoneError.style.display = 'none';
        // console.error("Invalid number");
      }
    });
  </script>
<script>
const mentorWrapper = document.getElementById('mentorInputsWrapper');
const mentorFields = mentorWrapper.querySelectorAll('input, select, textarea');

document.querySelectorAll('.role-input').forEach(input => {
  input.addEventListener('change', function () {
    let selectedRole = this.value;
    let selectedRoleHash, typeValue;

    if (selectedRole === '4') {
      selectedRoleHash = "2520";
      typeValue = "mentor";
      mentorWrapper.style.display = 'block';
      mentorFields.forEach(f => f.disabled = false);
    } else {
      selectedRoleHash = "2521";
      typeValue = "3";
      mentorWrapper.style.display = 'none';
      mentorFields.forEach(f => f.disabled = true);
    }

    // تحديث الرابط بنفس الصفحة مع إضافة type و data
    const url = new URL(window.location.href);
    url.searchParams.set('type', typeValue);
    url.searchParams.set('data', selectedRoleHash);
    window.history.pushState({}, '', url);
  });
});
</script>

<script>
	const fileInput = document.getElementById('file1');
	const previewImage = document.querySelector('label[for="file1"] img');

	fileInput.addEventListener('change', (event) => {
		const file = event.target.files[0];
		if (file) {
			const reader = new FileReader();
			reader.onload = (event) => {
				previewImage.src = event.target.result;
			};
			reader.onerror = () => {
				alert('Error loading file');
			};
			reader.readAsDataURL(file);
		}
	});

	document.getElementById('company-logo').addEventListener('click', () => {
		document.getElementById('file1').click();
	});
</script>
</body>
</html>