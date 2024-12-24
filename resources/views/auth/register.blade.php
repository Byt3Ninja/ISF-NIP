<!DOCTYPE html>
<html lang="{{ $l }}" dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}" data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-theme="theme-default"
  data-assets-path="/vuexy/assets/" data-template="vertical-menu-template" data-style="light">

<head>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta name="description" content="VI Login">
  <meta name="author" content="info@growthlabs.app">
  <title>{{ $l == 'en' ? 'Register New Account' : 'تسجيل حساب جديد'}} - {{
    App\Settings::findorfail(1)->first('title')->title }} - v1</title>
  <link rel="apple-touch-icon" href="/app-assets/images/ico/apple-icon-120.png">
  <link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">

  {{--
  <!-- Fonts -->
  @if ($l == 'ar')
  <link href="https://fonts.googleapis.com/css?family=Cairo&display=swap" rel="stylesheet">
  @else
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
    rel="stylesheet" />
  @endif
  <!-- End Fonts --> --}}

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

  <link rel="stylesheet" href="/app-assets/plugins/sweatalert/sweetalert2.min.css">
  <link rel="stylesheet" href="/app-assets/plugins/alertify/css/alertify.min.css">
  <link rel="stylesheet" href="/app-assets/plugins/alertify/css/themes/bootstrap.min.css">

  <!-- Page CSS -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-auth.css" />

  <style>
    body {
      overflow: hidden;
      min-height: 100vh;
    }

    .authentication-wrapper,
    .authentication-cover {
      padding: 0 !important;
      margin: 0 !important;
    }

    .authentication-bg {
      overflow-y: auto;
      height: 100vh;
    }

    .codeInputContainer {
      width: 135px;
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

    #google-sign-in:hover {
      background-color: var(--bs-light);
    }

    #linkedin-sign-in {
      background-color: #0077b5;
    }

    #linkedin-sign-in:hover {
      background-color: #154e6d;
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
  $formHeading = $l == 'ar' ? 'تبدأ المغامرة من هنا' : 'Adventure starts here';
  $formSubHeading = $l == 'ar' ? 'اجعل إدارة تطبيقك سهلة وممتعة!' : 'Make your app management easy and fun!';
  $firstName = $l == 'ar' ? 'الاسم الأول' : 'First Name';
  $secondName = $l == 'ar' ? 'الاسم الاخير' : 'Last Name';
  $gender = $l == 'ar' ? 'النوع' : 'Gender';
  $googleSignIn = $l == 'ar' ? 'تسجيل الدخول بحساب جوجل' : 'Sign in with Google';
  $linkdinSignIn = $l == 'ar' ? 'تسجيل الدخول بحساب لينكد ان' : 'Sign in with Linkedin';

  $accountRoles = ['Mentor', 'Founder'];
  @endphp

  <!-- Content -->
  <div class="authentication-wrapper authentication-cover p-0">
    <!-- Logo -->
    <a href="/dashboard" class="app-brand auth-cover-brand p-0">
      <img src="/images/logo/growthlab.png" alt="avatar" height="50" width="150">
    </a>
    <!-- /Logo -->
    <div class="authentication-inner row m-0 p-0">
      <!-- /Left Text -->
      <div class="d-none d-lg-flex col-lg-8 p-0">
        <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
          <img src="/vuexy/assets/img/illustrations/auth-register-illustration-light.png" alt="auth-register-cover"
            class="my-5 auth-illustration" data-app-light-img="illustrations/auth-register-illustration-light.png"
            data-app-dark-img="illustrations/auth-register-illustration-dark.png" loading="lazy" />

          <img src="/vuexy/assets/img/illustrations/bg-shape-image-light.png" alt="auth-register-cover"
            class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png"
            data-app-dark-img="illustrations/bg-shape-image-dark.png" loading="lazy" />
        </div>
      </div>
      <!-- /Left Text -->

      <!-- Register -->
      @if (App\Settings::findOrFail(1)->set_order)
      <div class="col-12 col-lg-4 authentication-bg px-0">
        <!-- Toggle theme and language -->
        <div class="navbar-nav-right p-4 d-flex" id="navbar-collapse">
          <ul class="navbar-nav flex-row ms-auto">
            <!-- Language -->
            <li class="nav-item dropdown-language dropdown">
              <a class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
                href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class="ti ti-language rounded-circle ti-md"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li class="dropdown dropdown-language nav-item">
                  <a class="dropdown-item" href="/lang/{{ $l == 'ar' ? 'en' : 'ar' }}">
                    <i class="flag-icon flag-icon-{{ $l == 'ar' ? 'us' : 'sa' }}"></i>
                    <span class="selected-language">{{ $l == 'ar' ? 'English' : 'عربي' }}</span>
                  </a>
                </li>
              </ul>
            </li>
            <!--/ Language -->

            <!-- Style Switcher -->
            <li class="nav-item dropdown-style-switcher dropdown">
              <a class="nav-link btn-icon rounded-pill dropdown-toggle hide-arrow" href="javascript:void(0);"
                data-bs-toggle="dropdown">
                <i class="ti ti-md"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                @foreach(['light' => 'sun', 'dark' => 'moon-stars', 'system' => 'device-desktop-analytics'] as $theme =>
                $icon)
                <li>
                  <a class="dropdown-item" href="javascript:void(0);" data-theme="{{ $theme }}">
                    <span class="align-middle"><i class="ti ti-{{ $icon }} ti-md me-3"></i>{{ ucfirst($theme) }}</span>
                  </a>
                </li>
                @endforeach
              </ul>
            </li>
            <!-- / Style Switcher-->
          </ul>
        </div>
        <!-- Toggle theme and language -->
        <form id="registerForm" class="col-12 mb-6" method="POST" action="{{ route('register') }}">
        @csrf
        <div class="card card-body shadow-none py-0">
          <div class="row">
            <div class="col-12">
              <h4 class="mb-1">{{ $formHeading }} 🚀</h4>
              <p class="mb-6">{{ $formSubHeading }}</p>
              @if (count($errors) > 0)
              @foreach ($errors->all() as $error)
              <x-alert type="danger" message="{{ $error }}" />
              @endforeach
              @endif
            </div>
            @foreach($accountRoles as $key => $role)
            <div class="col-6">
              <label for="{{$role}}Role" style="display: grid; place-items: center;" class="form-check custom-option custom-option-icon cursor-pointer">
                <div class="d-flex align-items-center">
                  <input name="role" class="form-check-input" type="radio" value="{{$key == 0 ? $role : 'Registered'}}"
                    id="{{$role}}Role" checked={{ $key==0 ? 'true' : '' }} required />
                  <div class="form-check-label custom-option-content d-flex align-items-center">
                    <i class="ti ti-{{ $key==0 ? 'user' : 'businessplan' }} me-2"></i>
                    <span class="custom-option-title">{{ __('admin.'. $role) }}</span>
                  </label>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        
          <div class="card card-body shadow-none">
            <div class="row">
              <div class="col-12 mb-3">
                <label for="username" class="form-label fs-6 fw-bold">{{ $firstName }}</label>
                <div class="input-group input-group-merge">
                  <input value="{{ old('first_name_en') }}" name="first_name_en" type="text" class="form-control"
                    id="first_name_en" placeholder="{{ __('auth.First English Name') }}" required
                    autocomplete="first_name_en" autofocus />
                </div>
              </div>
              <div class="col-12 mb-3">
                <label for="last_name_en" class="form-label fs-6 fw-bold">{{ $secondName }}</label>
                <div class="input-group input-group-merge">
                  <input value="{{ old('last_name_en') }}" name="last_name_en" type="text" class="form-control"
                    id="last_name_en" placeholder="{{ __('auth.Last English Name') }}" required
                    autocomplete="last_name_en" />
                </div>
              </div>
              <div class="col-12 mb-3">
                <label for="email" class="form-label fs-6 fw-bold">{{ __('auth.E-Mail') }}</label>
                <div class="input-group input-group-merge">
                  <input value="{{ old('email') }}" name="email" type="email" class="form-control"
                    placeholder="{{ __('auth.E-Mail') }}" required autocomplete="email" />
                </div>
              </div>
              <div class="col-12 mb-4 form-password-toggle">
                <label class="form-label fs-6 fw-bold" for="password">{{ __('auth.Password') }}</label>
                <div class="input-group input-group-merge">
                  <input type="password" class="form-control" id="password" placeholder="{{ __('auth.Password') }}"
                    name="password" required autocomplete="password" aria-describedby="password" />
                  <span class="input-group-text cursor-pointer border border-light"><i class="ti ti-eye-off"></i></span>
                </div>
              </div>
              <div class="col-12 mb-4 form-password-toggle">
                <label class="form-label fs-6 fw-bold" for="password_confirmation">{{ __('auth.Confirm') }}</label>
                <div class="input-group input-group-merge">
                  <input type="password" class="form-control" id="password_confirmation"
                    placeholder="{{ __('auth.Password') }}" name="password_confirmation" required
                    autocomplete="new-password" aria-describedby="new-password" />
                  <span class="input-group-text cursor-pointer border border-light"><i class="ti ti-eye-off"></i></span>
                </div>
              </div>
              <div class="col-12 mb-4">
                <label class="form-label fs-6 fw-bold" for="gender">{{ $gender }}</label>
                <select class="form-select select2" name="gender" id="gender" required>
                  <option value="Male">{{ __('Male') }}</option>
                  <option value="Female">{{ __('Female') }}</option>
                </select>
              </div>
              <div class="col-12 mb-4 d-grid">
                <label class="form-label fs-6 fw-bold" for="phone">{{ __('auth.Phone') }}</label>
                <input id="phone" type="tel" class="form-control" placeholder="{{ __('auth.Phone') }}"
                  value="{{ old('phone') }}" name="phone" autocomplete="phone" required />
                  <span class="text-danger" id="phone-error" style="display: none;"></span>
              </div>
              <div id="mentorInputsWrapper" style="display: none;">
                <div class="col-12 mb-3">
                  <label for="Title" class="form-label fs-6 fw-bold">{{ __('admin.JobTitleEn') }}</label>
                  <div class="input-group input-group-merge">
                    <input value="{{ old('Title') }}" name="title_en" type="text" class="form-control" id="Title"
                      placeholder="{{ __('admin.JobTitleEn') }}" autocomplete="title_en" />
                  </div>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label fs-6 fw-bold" for="expertise">{{ __('admin.Industry Expertise') }}</label>
                  <div class="form-group">
                    <select name="expertise[]" class="select2 form-select" multiple="multiple" id="expertise"
                      data-placeholder="{{__('admin.Select Expertise')}}" value="" required>
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
                </div>
                <div class="col-12 mb-3">
                  <label for="userbio" class="form-label fs-6 fw-bold">{{ __('admin.userbio') }}</label>
                  <div class="input-group">
                    <textarea name="userbio" type="text" class="form-control" id="userbio"
                      placeholder="{{ __('admin.userbio') }}" autocomplete="userbio">{{ old('userbio') }}</textarea>
                  </div>
                </div>
                <div class="col-12 mb-3">
                  <label for="Linkedin" class="form-label fs-6 fw-bold">{{ __('admin.Linkedin') }}</label>
                  <div class="input-group">
                    <input value="{{ old('Linkedin') }}" name="Linkedin" type="url" class="form-control" id="Linkedin"
                      placeholder="{{ __('admin.Linkedin') }}" autocomplete="Linkedin" />
                  </div>
                </div>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary w-100 mb-4"> {{ __('auth.Register') }}</button>

                <p class="text-center">
                  <span>{{ __('Already have account') }}{{ $l == 'ar' ? '؟' : '?' }}</span>
                  <a href="/login">
                    <span>{{ __('Login') }}</span>
                  </a>
                </p>
                <div class="divider my-6">
                  <div class="divider-text">{{ $l == 'ar' ? 'او' : 'OR' }}</div>
                </div>
                <a 
                  id="google-sign-in"
                  href="{{ url('/auth/redirect/google') }}" 
                  class="btn btn-transparent shadow-sm border btn-block d-flex align-items-center justify-content-center"
                >
                  <img src="/icons/google-icon.svg" alt="google" width="20" height="20" class="me-2">
                  <span class="fw-semibold">{{ $googleSignIn }}</span>
                </a>
                
                <div class="divider">
                  <div class="divider-text">{{ $l == 'ar' ? 'او' : 'OR' }}</div>
                </div>

                @if (app()->environment('local', 'dev'))
                  <a 
                    id="linkedin-sign-in"
                    href="{{ route('linkedin.login') }}" 
                    class="btn btn-block shadow-sm d-flex align-items-center justify-content-center"
                    style="color: #fff;"
                  >
                    <i class="fa-brands fa-linkedin-in me-2"></i> 
                    <span class="fw-semibold">{{ $linkdinSignIn }}</span>
                  </a>
                @endif
              </div>
            </div>
          </div>
        </form>
      </div>
      @endif
      <!-- / Register -->
    </div>
  </div>

  <!-- / Content -->

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
  <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script>

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

    const iti = intlTelInput(phoneInput, {
      initialCountry: "eg", // Default country
      preferredCountries: ["eg", "ae", "sa", "ma", "dz", "tn"], // Preferred countries
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js", // Utilities for formatting
    });

    // Optional: Handle validation
    phoneInput.addEventListener("input", (e) => {
      console.log(e.target.value);
      if (iti.isValidNumber()) {
        phoneError.style.display = 'none';
        // console.log("Valid number:", iti.getNumber());
      } else {
        phoneError.style.display = 'block';
        phoneError.innerText = lang == 'en' ? 'Invalid phone number' : 'رقم الهاتف غير صحيح';
        // console.error("Invalid number");
      }
    });
  </script>

  <!-- Get countries and phone codes -->
  {{-- <script>
    fetch('https://restcountries.com/v3.1/all')
      .then(response => response.json())
      .then(data => {
        const select = document.getElementById('country_code');
        data.forEach(country => {
          const option = document.createElement('option');
          const countryCode = `${country.idd.root}${country.idd.suffixes ? country.idd.suffixes[0] : ''}`;
          option.value = countryCode;
          option.text = `${countryCode} (${country.cca2})`;
          option.style.fontSize = '12px';
          if (country.cca2 === 'EG') {
            option.selected = true;
          }
          select.appendChild(option);
        });
      });
  </script> --}}
</body>

</html>