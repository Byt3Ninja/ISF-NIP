<!doctype html>

<html lang="{{ $l }}" dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}"
  class="light-style layout-wide customizer-hide" data-theme="theme-default"
  data-assets-path="/vuexy/assets/" data-template="vertical-menu-template" data-style="light"
  data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}">

<head>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta name="description" content="Sign in to your account">
  <meta name="author" content="info@growthlabs.app">
  <title>
    {{ $l == 'en' ? 'Sign In' : 'تسجيل الدخول'}}
  </title>
  <link rel="apple-touch-icon" href="/app-assets/images/ico/apple-icon-120.png">
  <link rel="shortcut icon" type="image/x-icon" href="/app-assets/images/ico/favicon.ico">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/fontawesome.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/tabler-icons.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/fonts/flag-icons.css" />

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
  <!-- Shared auth-page styles (login + both register pages) -->
  <link rel="stylesheet" href="{{ asset('css/auth-pages.css') }}" />


  <!-- Helpers -->
  <script src="/vuexy/assets/vendor/js/helpers.js"></script>
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
  <script src="/vuexy/assets/vendor/js/template-customizer.js"></script>

  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="/vuexy/assets/js/config.js"></script>
</head>

<body>
  @php
    $setting = App\Settings::first();
    // brandLogo() falls back when the configured file is missing from disk, so the
    // page can never paint the <img> alt text where the logo should be.
    $logo = brandLogo();
    $platformName = brandName();
    $formHeading = $l == 'ar' ? 'مرحبًا بعودتك إلى ' . $platformName : 'Welcome to ' . $platformName;
    $formSubHeading = $l == 'ar'
      ? 'الرجاء تسجيل الدخول إلى حسابك وابدأ المغامرة'
      : 'Please sign-in to your account and start the adventure';
  @endphp
  <!-- Content -->

  <div class="authentication-wrapper authentication-cover">
    <!-- Language & Style Switchers -->
    <div class="d-flex align-items-center gap-2 position-fixed top-0 end-0 p-4 p-sm-6" style="z-index: 1200">
      <!-- Language -->
      <div class="dropdown-language dropdown">
        <a class="btn btn-primary btn-icon rounded-pill dropdown-toggle hide-arrow" href="javascript:void(0);"
          data-bs-toggle="dropdown">
          <i class="ti ti-language ti-md text-white"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
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
        </ul>
      </div>
      <!--/ Language -->

      <!-- Style Switcher -->
      <div class="dropdown-style-switcher dropdown">
        <a class="btn btn-primary btn-icon rounded-pill dropdown-toggle hide-arrow" href="javascript:void(0);"
          data-bs-toggle="dropdown">
          <i class="ti ti-md text-white"></i>
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
      </div>
      <!--/ Style Switcher -->
    </div>
    <!--/ Language & Style Switchers -->

    <div class="authentication-inner row m-0">
      <!-- /Right Text -->
      <div class="d-none d-lg-flex col-lg-5 p-0">
        <div class="auth-cover-bg d-flex align-items-center p-6">
          <div class="auth-cover-content">
            <h2 class="auth-cover-title">{{ $l == 'ar' ? 'مرحبًا بك في ' . $platformName : 'Welcome to ' . $platformName }}</h2>
            <span class="auth-cover-rule" aria-hidden="true"></span>
            <p class="auth-cover-text">{{ __('auth.connect_text') }}</p>
            <a href="{{ route('register') }}" class="auth-cover-btn">{{ __('auth.create_account') }}</a>
          </div>
        </div>
      </div>
      <!-- / Right Text -->

      <!-- /Login -->
      <div class="d-flex col-12 col-lg-7 align-items-center authentication-bg auth-form-col p-sm-12 p-6">
        <div class="auth-form-inner">
          <!-- Logo, centered above the form heading -->
          <a href="/dashboard" class="auth-brand">
            <img src="{{ $logo }}" alt="{{ $platformName }}">
          </a>

          <div class="auth-form-heading">
            <h4>{{ $formHeading }}</h4>
            <p>{{ $formSubHeading }}</p>
          </div>

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

          <form id="formAuthentication" class="mb-6" method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div class="mb-6">
              <label for="email" class="form-label">{{ __('auth.E-Mail') }}</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti ti-mail"></i></span>
                <input type="email" class="form-control" id="email" name="email"
                  value="{{ old('email') }}" placeholder="{{ __('auth.E-Mail') }}" autocomplete="off" required />
              </div>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Password -->
            <div class="mb-6 form-password-toggle">
              <label class="form-label" for="password">{{ __('auth.Password') }}</label>
              <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" name="password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="password" autocomplete="current-password" required />
                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
              </div>
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Remember & Forgot -->
            <div class="my-8">
              <div class="d-flex justify-content-between align-items-center">
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" id="remember-me"
                    {{ old('remember') ? 'checked' : '' }} name="remember" />
                  <label class="form-check-label" for="remember-me">{{ __('auth.stay_logged') }}</label>
                </div>
                <a href="{{ route('password.request') }}">
                  <p class="mb-0 fs-6">{{ __('Forgot Password?') }}</p>
                </a>
              </div>
            </div>

            <button id="login-submit-btn" type="submit" class="btn btn-primary btn-lg d-grid w-100">
              {{ __('auth.Sign In') }}
            </button>
          </form>

          <p class="text-center">
            <span>
              {{ __('auth.New on our platform') }}
            </span>
            <a href="{{ route('register') }}">
              <span>{{ __('auth.Create_new_account') }}</span>
            </a>
          </p>

          @if ((isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled') || (isset($setting) && ($setting->GOOGLE_LOGIN ?? 'disabled') === 'enabled'))
            <div class="divider my-6">
              <div class="divider-text">{{ __('auth.or') }}</div>
            </div>
          @endif

          @php $data = request('data') ?? '2521'; @endphp
          @if ((isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled') || (isset($setting) && ($setting->GOOGLE_LOGIN ?? 'disabled') === 'enabled'))
            <div class="d-flex justify-content-center">
              @if(isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled')
                <a href="{{ route('integrations.auth_linkedin_login', $data) }}"
                  class="btn btn-sm btn-icon rounded-circle btn-text-facebook me-3 shadow-sm" style="background-color: #0077b5;">
                  <i class="fa-brands fa-linkedin-in text-white"></i>
                </a>
              @endif

              @if(isset($setting) && ($setting->GOOGLE_LOGIN ?? 'disabled') === 'enabled')
                <a href="{{ url('/auth/redirect/google') }}"
                  class="btn btn-sm bg-white border btn-icon rounded-circle btn-text-google-plus shadow-sm">
                  <img src="/icons/google-icon.svg" alt="google" width="20" height="20">
                </a>
              @endif
            </div>
          @endif
        </div>
      </div>
      <!-- /Login -->
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
          },
           init: instance => {
            instance.on('plugins.message.placed', function (e) {
              if (e.element.parentElement.classList.contains('input-group')) {
                e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
              }
            });
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
