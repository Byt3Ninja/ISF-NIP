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
  <meta name="description" content="Reset your password">
  <meta name="author" content="info@growthlabs.app">
  <title>
    {{ $l == 'en' ? 'Forgot Password' : 'استعادة كلمة المرور' }}
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
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/form-validation/form-validation.css" />

  <!-- Page CSS -->
  <!-- Page -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-auth.css" />
  <!-- Shared auth-page styles (login + register pages + forgot password) -->
  <link rel="stylesheet" href="{{ asset('css/auth-pages.css') }}" />

  <!-- Helpers -->
  <script src="/vuexy/assets/vendor/js/helpers.js"></script>
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
  <script src="/vuexy/assets/vendor/js/template-customizer.js"></script>

  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="/vuexy/assets/js/config.js"></script>
  
  <!-- BEGIN: Custom CSS-->
  @include('admin.includes.branding-styles')
  <!-- END: Custom CSS-->
</head>

<body>
  @php
    // brandLogo() falls back when the configured file is missing from disk, so the
    // page can never paint the <img> alt text where the logo should be.
    $logo = brandLogo();
    $appName = brandName();
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
      <!-- /Forgot Password -->
      <div class="d-flex col-12 col-lg-7 align-items-center authentication-bg auth-form-col p-sm-12 p-6">
        <div class="auth-form-inner">

          <!-- Logo, centered above the form heading -->
          <a href="/dashboard" class="auth-brand">
            <img src="{{ $logo }}" alt="{{ $appName }}">
          </a>

          <div class="auth-form-heading">
            <h4>{{ __('auth.Forgot') }}</h4>
            <p>{{ __('auth.reset_password_subheading') }}</p>
          </div>

          @if (session('status'))
            <div class="row">
              <div class="col-md-12">
                <div class="alert alert-success">
                  <ul class="mb-0">
                    <li>{{ session('status') }}</li>
                  </ul>
                </div>
              </div>
            </div>
          @endif

          <form id="formAuthentication" class="mb-0" method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email -->
            <div class="form-group-tight">
              <label for="email" class="form-label">{{ __('auth.E-Mail') }}</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti ti-mail"></i></span>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                  value="{{ old('email') }}" placeholder="{{ __('auth.placeholder_email') }}" autocomplete="email"
                  required autofocus />
              </div>
              @error('email')
                <div class="invalid-feedback d-block server-error">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-lg d-grid w-100">
              {{ __('auth.SendPassword') }}
            </button>
          </form>

          <p class="text-center mt-6">
            <a href="{{ route('login') }}">
              <i class="ti ti-chevron-left scaleX-n1-rtl me-1_5"></i>
              <span>{{ __('auth.Back to login') }}</span>
            </a>
          </p>
        </div>
      </div>
      <!-- /Forgot Password -->

      <!-- /Right Text -->
      <div class="d-none d-lg-flex col-lg-5 p-0">
        <div class="auth-cover-bg d-flex align-items-center p-6">
          <div class="auth-cover-content">
            <h2 class="auth-cover-title">{{ __('auth.reset_password_cover_title') }}</h2>
            <span class="auth-cover-rule" aria-hidden="true"></span>
            <p class="auth-cover-text">{{ __('auth.reset_password_cover_text') }}</p>
            <a href="{{ route('login') }}" class="auth-cover-btn">{{ __('auth.Sign In') }}</a>
          </div>
        </div>
      </div>
      <!-- /Right Text -->
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
  <script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>

  <!-- Main JS -->
  <script src="/vuexy/assets/js/main.js"></script>

  <!-- Forgot Password Form Validation -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const formAuthentication = document.getElementById('formAuthentication');
      if (!formAuthentication) { return; }

      const emailField = document.getElementById('email');

      // Re-validate as soon as the visitor starts fixing a value the server
      // rejected, so the server-side message can be dropped the moment it goes.
      if (emailField) {
        emailField.addEventListener('input', function() {
          const group = emailField.closest('.form-group-tight');
          if (!group || !group.querySelector('.is-invalid')) { return; }

          const validator = FormValidation.getInstance(formAuthentication);
          if (!validator) { return; }

          validator.refreshField('email').then(function() {
            if (group.querySelector('.is-invalid')) { return; }

            const serverError = group.querySelector('.server-error');
            if (serverError) { serverError.remove(); }
            emailField.classList.remove('is-invalid');
          });
        });
      }

      FormValidation.formValidation(formAuthentication, {
        fields: {
          email: {
            validators: {
              notEmpty: {
                message: '{{ __('auth.validate_email_required') }}'
              },
              regexp: {
                regexp: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i,
                message: '{{ __('auth.validate_email_email') }}'
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            eleInvalidClass: 'is-invalid',
            rowSelector: '.form-group-tight'
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: instance => {
          // The message is appended after the .input-group, so it lands below
          // the whole control instead of inside it.
          instance.on('plugins.message.placed', function (e) {
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        }
      });
    });
  </script>
</body>

</html>
