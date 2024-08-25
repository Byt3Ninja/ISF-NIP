<!DOCTYPE html>
<html lang="{{ $l }}">
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
  <!-- End Fonts -->

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
  $brandName = 'GrowthLabs';
  $formHeading = $l == 'ar' ? 'مرحبًا بعودتك' : 'Welcome to GrowthLabs';
  $formSubHeading = $l == 'ar' ? 'الرجاء تسجيل الدخول إلى حسابك وابدأ المغامرة' : 'Please sign-in to your account and
  start the adventure';
  $googleSignIn = $l == 'ar' ? 'تسجيل الدخول بحساب جوجل' : 'Sign in with Google';
  @endphp

  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner py-6">
        <!-- Login -->
        <div class="card">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-6">
              <a class="app-brand-link">
                <span class="app-brand-logo demo">
                  <img src="/app-assets/images/ico/favicon.ico" alt="avatar" height="25" width="25">
                </span>
                <span class="app-brand-text demo text-heading fw-bold">
                  {{ $brandName }}
                </span>
              </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-1">{{ $formHeading }}</h4>
            <p class="mb-6">{{ $formSubHeading }}</p>
            
            <!-- Auth Errors -->
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

            <form id="formAuthentication" class="mb-4" method="POST" action="{{ route('login') }}">
              @csrf
              <div class="mb-6">
                <label for="email" class="form-label">{{ __('auth.E-Mail') }}</label>
                <input 
                  value="{{ old('email') }}" 
                  name="email"
                  type="email" 
                  class="form-control"
                  id="email" 
                  placeholder="{{ __('auth.E-Mail') }}" 
                  required 
                  autocomplete="email"
                  autofocus 
                />
              </div>
              <div class="mb-6 form-password-toggle">
                <label class="form-label" for="password">{{ __('auth.Password') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="password"
                    id="password" 
                    class="form-control" 
                    placeholder="{{ __('auth.Password') }}" 
                    name="password" 
                    required
                    aria-describedby="password" 
                  />
                  <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                </div>
              </div>
              <div class="my-8">
                <div class="d-flex justify-content-between">
                  <div class="form-check mb-0 ms-2">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      id="remember-me" 
                      {{ old('remember') ? 'checked' : '' }} 
                    />
                    <label class="form-check-label" for="remember-me">{{ __('auth.Remember') }}</label>
                  </div>
                  <a href="{{ route('password.request') }}">
                    <p class="mb-0">{{ __('Forgot Password?') }}</p>
                  </a>
                </div>
              </div>
              <div class="mb-6">
                <button class="btn btn-primary d-grid w-100" type="submit">{{ __('auth.Login') }}</button>
              </div>
            </form>

            <p class="text-center">
              <span>{{ __("Don't have an account") }}{{ $l == 'ar' ? '؟' : '?' }}</span>
              <a href="/register">
                <span>{{ __('Sign up') }}</span>
              </a>
            </p>

            <div class="divider my-6">
              <div class="divider-text">{{ $l == 'ar' ? 'او' : 'or' }}</div>
            </div>

            <div class="container mt-5">
              <a href="{{ url('/auth/redirect/google') }}" class="btn btn-label-primary btn-block d-flex align-items-center justify-content-center">
                <i class="fab fa-google me-2"></i> <span>{{ $googleSignIn }}</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- / Content -->

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
