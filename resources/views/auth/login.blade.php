<!DOCTYPE html>
<html lang="{{ $l }}" dir={{ $l == 'ar' ? 'rtl' : 'ltr' }}
  data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-theme="theme-default"
  data-assets-path="/vuexy/assets/" data-template="vertical-menu-template" data-style="light">

<head>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <meta name="description" content="VI Login">
  <meta name="author" content="info@growthlabs.app">
  <title>{{ __('Login Page') }} - {{ App\Settings::findorfail(1)->first('title')->title }} - v1</title>
  <link rel="apple-touch-icon" href="/app-assets/images/ico/apple-icon-120.png">
  <link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">

  {{-- <!-- Fonts -->
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
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/node-waves/node-waves.css" />

  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/typeahead-js/typeahead.css" />
  <!-- Vendor -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />

  <!-- Page CSS -->
  <!-- Page -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-auth.css" />

  <style>
    #switcher-container {
      position: absolute;
      top: 1rem;
      width: 100px;
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
    
    @media screen and (min-width: 768px) {
      #switcher-container {
        width: 120px;
      }
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
  <!-- Content -->
  @php
    $formHeading = $l == 'ar' ? 'مرحبًا بعودتك' : 'Welcome to GrowthLabs';
    $formSubHeading =
    $l == 'ar'
    ? 'الرجاء تسجيل الدخول إلى حسابك وابدأ المغامرة'
    : 'Please sign-in to your account and
    start the adventure';
    $googleSignIn = $l == 'ar' ? 'تسجيل الدخول بحساب جوجل' : 'Sign in with Google';
    $linkdinSignIn = $l == 'ar' ? 'تسجيل الدخول بحساب لينكد ان' : 'Sign in with Linkedin';
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

  <div class="authentication-wrapper authentication-cover">
    <!-- Logo -->
    <a href="/dashboard" class="app-brand auth-cover-brand">
      <img src="/images/logo/growthlab.png" alt="avatar" height="50" width="150">
    </a>
    <!-- /Logo -->
    <div class="authentication-inner row m-0">
      <!-- /Left Text -->
      <div class="d-none d-lg-flex col-lg-8 p-0">
        <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
          <img src="/vuexy/assets/img/illustrations/auth-login-illustration-light.png" alt="auth-login-cover"
            class="my-5 auth-illustration" data-app-light-img="illustrations/auth-login-illustration-light.png"
            data-app-dark-img="illustrations/auth-login-illustration-dark.png" 
            loading="lazy"
          />

          <img src="/vuexy/assets/img/illustrations/bg-shape-image-light.png" alt="auth-login-cover" class="platform-bg"
            data-app-light-img="illustrations/bg-shape-image-light.png"
            data-app-dark-img="illustrations/bg-shape-image-dark.png" 
            loading="lazy"
          />
        </div>
      </div>
      <!-- /Left Text -->

      <!-- Login -->
      <div class="d-flex col-12 col-lg-4 align-items-center authentication-bg p-sm-12 p-6">
        <div class="w-px-400 mx-auto pt-3">
          <h3 class="mb-1">{{ $formHeading }}</h3>
          <p class="mb-6">{{ $formSubHeading }}</p>
          
          <!-- Auth Errors -->
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

            @if (session('Reset'))
              <div class="row">
                <div class="col-md-12">
                  <div class="alert alert-success">
                    <ul>
                      <li>{{ session('Reset') }} </li>
                    </ul>
                  </div>
                </div>
              </div>
            @endif
  
            @if ($errors->has('social'))
              <div class="text-danger mb-6 bg-label-danger p-3 rounded">
                {{ __('You already have an account, Login with your email, or reset your account') }}!!
              </div>
            @elseif ($errors->has('email'))
              <div class="text-danger mb-6 bg-label-danger p-3 rounded">
                {{ $errors->first('email') }}
              </div>
            @elseif ($errors->has('password'))
              <div class="text-danger mb-6 bg-label-danger p-3 rounded">
                {{ $errors->first('password') }}
              </div>
            @endif
            <!-- / Auth Errors -->
          </div>

          <form id="formAuthentication" class="mb-4" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
              <label for="email" class="form-label fs-6 fw-semibold">{{ __('auth.E-Mail') }}</label>
              <input value="{{ old('email') }}" name="email" type="email" class="form-control" id="email"
                placeholder="{{ __('auth.E-Mail') }}" required autocomplete="email" autofocus />
            </div>
            <div class="mb-4 form-password-toggle">
              <label class="form-label fs-6 fw-semibold" for="password">{{ __('auth.Password') }}</label>
              <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" placeholder="{{ __('auth.Password') }}"
                  name="password" required aria-describedby="password" autocomplete="current-password" />
                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
              </div>
            </div>
            <div class="my-4">
              <div class="d-flex justify-content-between">
                <div class="form-check mb-0 ms-2">
                  <input class="form-check-input" type="checkbox" id="remember-me"
                    {{ old('remember') ? 'checked' : '' }} name="remember" />
                  <label class="form-check-label" for="remember-me">{{ __('auth.Remember') }}</label>
                </div>
                <a href="{{ route('password.request') }}">
                  <p class="mb-0">{{ __('Forgot Password?') }}</p>
                </a>
              </div>
            </div>
            <div class="mb-4">
              <button class="btn btn-primary d-grid w-100" type="submit">{{ __('auth.Login') }}</button>
            </div>
          </form>

          <p class="text-center">
            <span>{{ __("Don't have an account") }}{{ $l == 'ar' ? '؟' : '?' }}</span>
            <a href="/register">
              <span>{{ __('Sign up') }}</span>
            </a>
          </p>

          <div class="divider">
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

          {{-- @if (app()->environment('local', 'dev')) --}}
            <a 
              id="linkedin-sign-in"
              href="{{ route('linkedin.login') }}" 
              class="btn btn-block shadow-sm d-flex align-items-center justify-content-center"
              style="color: #fff;"
            >
              <i class="fa-brands fa-linkedin-in me-2"></i> 
              <span class="fw-semibold">{{ $linkdinSignIn }}</span>
            </a>
          {{-- @endif --}}

        </div>
      </div>
      <!-- /Login -->
    </div>
  </div>
  <!-- /Content -->

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
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>

  <!-- Main JS -->
  <script src="/vuexy/assets/js/main.js"></script>

  <!-- Language -->
  <script>
    const lang = '{{ $l }}';
  </script>

  <!-- Page JS -->
  <script src="/vuexy/assets/js/pages-auth.js"></script>
</body>

</html>
