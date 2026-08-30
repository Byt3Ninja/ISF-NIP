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

  <!-- Form Validation CSS -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />

  <!-- Page CSS -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-auth.css" />

  <style>
    #switcher-container {
      position: absolute;
      top: 1rem;
      width: 100px;
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
    $setting = App\Settings::first();
    $logo = $setting ? asset('images/logo/' . $setting->Logo) : asset('images/background/logo.png');
    $platformName = $setting ? $setting->Platform_Name : '';
    $formHeading = $l == 'ar' ? 'مرحبًا بعودتك إلى ' . $platformName : 'Welcome to ' . $platformName;
    $formSubHeading = $l == 'ar'
      ? 'الرجاء تسجيل الدخول إلى حسابك وابدأ المغامرة'
      : 'Please sign-in to your account and start the adventure';
  @endphp

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
      <div class="authentication-inner py-md-6 mt-10 mt-md-0">
        <!-- Login -->
        <div class="card">
          <div class="px-4 px-md-4 py-6">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-4">
              <a href="/dashboard" class="app-brand-link">
                <img src="{{ $logo }}" alt="avatar"  width="150" class="rounded">
              </a>
            </div>
            <!-- /Logo -->
            <h4 
              class="mb-2 text-center lh-base text-capitalize"
              style="direction: {{ $l == 'ar' ? 'rtl' : 'ltr' }}"
            >
              {!! str_replace('Platform', 'Platform <br/>', $formHeading) !!}
            </h4>
            <p class="mb-6 text-center">{{ $formSubHeading }}</p>

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
                  {{ $l == 'ar' ? 'بيانات الاعتماد هذه غير متطابقة مع البيانات المسجلة لدينا.' : 'These credentials do not match our records.' }}
                </div>
              @elseif ($errors->has('password'))
                <div class="text-danger mb-6 bg-label-danger p-3 rounded">
                  {{ $errors->first('password') }}
                </div>
              @endif
            </div>

            <form id="formAuthentication" class="mb-4 px-md-10" method="POST" action="{{ route('login') }}">
              @csrf
              <div class="mb-6">
                <label for="email" class="form-label fs-6 fw-semibold">{{ __('auth.E-Mail') }}</label>
                <input value="{{ old('email') }}" name="email" type="email" class="form-control" id="email"
                  placeholder="{{ __('auth.E-Mail') }}" required autocomplete="off" />
              </div>
              <div class="mb-6 form-password-toggle">
                <label class="form-label fs-6 fw-semibold" for="password">{{ __('auth.Password') }}</label>
                <div class="input-group input-group-merge shadow-none">
                  <input 
                    type="password" 
                    id="password" 
                    class="form-control" 
                    placeholder="{{ __('auth.Password') }}"
                    name="password" 
                    required 
                    aria-describedby="password" 
                    autocomplete="current-password" 
                  />
                  <span class="input-group-text cursor-pointer border border-light"><i class="ti ti-eye-off"></i></span>
                </div>
              </div>
              <div class="my-4">
                <div class="d-flex justify-content-between">
                  <div class="form-check mb-0 ms-2">
                    <input class="form-check-input" type="checkbox" id="remember-me"
                      {{ old('remember') ? 'checked' : '' }} name="remember" />
                    <label class="form-check-label" for="remember-me">{{ __('auth.stay_logged') }}</label>
                  </div>
                  <a href="{{ route('password.request') }}">
                    <p class="mb-0">{{ __('Forgot Password?') }}</p>
                  </a>
                </div>
              </div>
              <div class="mb-4">
                <button id="login-submit-btn" class="btn btn-primary d-grid w-100" type="submit">{{ __('auth.Login') }}</button>
              </div>
            </form>

            <p class="text-center">
              <span>
                {{ __('auth.New on our platform') }}
              </span>
              <a href="{{ route('register') }}">
                <span>{{ __('auth.Create_new_account') }}</span>
              </a>
            </p>
            @if ((isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled') ||(isset($setting) && ($setting->GOOGLE_LOGIN ?? 'disabled') === 'enabled'))
            <div class="divider my-6 mx-auto" style="width: 80%;">
              <div class="divider-text">{{ __('auth.or') }}</div>
            </div>
            @endif
            @php $data = request('data') ?? '2521'; @endphp
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
    </div>
  </div>

  <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
  <script src="/vuexy/assets/vendor/js/bootstrap.js"></script>

  <!-- Vendors JS -->
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <!-- Main JS -->
  <script src="/vuexy/assets/js/main.js"></script>

  <!-- Login Form Validation -->
  <script>
    const lang = '{{ $l }}';

    document.addEventListener('DOMContentLoaded', function() {
      const formAuthentication = document.getElementById('formAuthentication');

      const translations = {
        en: {
          email: {
            notEmpty: 'Please enter your email',
            emailAddress: 'Please enter a valid email address'
          },
          password: {
            notEmpty: 'Please enter your password',
            stringLength: 'Password must be 8-20 characters long',
            strong: "Password must be strong including uppercase, lowercase, numbers, and special characters"
          }
        },
        ar: {
          email: {
            notEmpty: 'يرجى إدخال بريدك الإلكتروني',
            emailAddress: 'يرجى إدخال عنوان بريد إلكتروني صالح'
          },
          password: {
            notEmpty: 'يرجى إدخال كلمة المرور الخاصة بك',
            stringLength: 'يجب ان تتكون كلمة المرور من 8-20 حرفًا',
            strong: "كلمة السر يجب أن تكون قوية حيث تتضمن حروفًا كبيرة وصغيرة وأرقامًا ورموزًا",
          }
        }
      };

      if (formAuthentication) {
        FormValidation.formValidation(formAuthentication, {
          fields: {
            email: {
              validators: {
                notEmpty: {
                  message: translations[lang].email.notEmpty
                },
                regexp: {
                  regexp: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i,
                  message: translations[lang].email.emailAddress
                }
              }
            },
            password: {
              validators: {
                callback: {
                  message: translations[lang].password.strong,
                  callback: function(input) {
                    const value = input.value;
                    if (value === '123456789' || value === '123456') {
                      return {
                        valid: true
                      };
                    }
                    if (value === '') {
                      return {
                        valid: false,
                        message: translations[lang].password.notEmpty
                      };
                    }
                    if (value.length < 8 || value.length > 20) {
                      return {
                        valid: false,
                        message: translations[lang].password.stringLength
                      };
                    }
                    if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$/.test(value)) {
                      return {
                        valid: false,
                        message: translations[lang].password.strong
                      };
                    }
                    return {
                      valid: true
                    };
                  }
                }
              }
            }
          },
          plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
              eleValidClass: '',
              rowSelector: '.mb-6'
            }),
            submitButton: new FormValidation.plugins.SubmitButton(),
            defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
            autoFocus: new FormValidation.plugins.AutoFocus()
          }
        });

        // GV-3679: disable the Login button once the form is actually being
        // submitted, so a second click cannot fire a duplicate request.
        // The native `submit` event fires only AFTER FormValidation has passed,
        // so a rejected form never leaves the button stuck.
        const loginBtn = document.getElementById('login-submit-btn');
        if (loginBtn) {
          // Capture the rendered label rather than re-deriving it, so the
          // restored button always matches the markup.
          const loginBtnHtml = loginBtn.innerHTML;

          formAuthentication.addEventListener('submit', function() {
            if (loginBtn.disabled) { return; }
            loginBtn.disabled = true;
            loginBtn.classList.add('disabled');
            loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>'
              + '{{ $l == "ar" ? "جارٍ تسجيل الدخول..." : "Signing in..." }}';
          });

          // A bfcache restore (browser Back) reuses the old DOM, which would
          // otherwise show a permanently disabled button.
          window.addEventListener('pageshow', function(event) {
            if (!event.persisted) { return; }
            loginBtn.disabled = false;
            loginBtn.classList.remove('disabled');
            loginBtn.innerHTML = loginBtnHtml;
          });
        }
      }
    });
  </script>
</body>

</html>
