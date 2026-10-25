<!doctype html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
  data-textdirection="{{ $l == 'ar' ? 'rtl' : 'ltr' }}" data-theme="theme-default"
  data-assets-path="/vuexy/assets/" data-template="vertical-menu-template"
  data-style="light" lang="{{ $l }}" dir="{{ $l == 'ar' ? 'rtl' : 'ltr' }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>{{ $l == 'en' ? 'Reset Password Cover' : 'اعادة تعيين كلمة المرور' }}
    - {{ App\Settings::findorfail(1)->first('title')->title }} - v1</title>
  <meta name="description" content="" />
  <!-- Favicon -->
  <link href="/app-assets/images/ico/apple-icon-120.png" rel="apple-touch-icon">
  <link type="image/x-icon" href="/app-assets/images/ico/favicon.ico" rel="shortcut icon">
  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
  <!-- Icons -->
  <link href="/vuexy/assets/vendor/fonts/fontawesome.css" rel="stylesheet" />
  <link href="/vuexy/assets/vendor/fonts/tabler-icons.css" rel="stylesheet" />
  <link href="/vuexy/assets/vendor/fonts/flag-icons.css" rel="stylesheet" />
  <!-- Core CSS -->
  <link class="template-customizer-core-css" href="/vuexy/assets/vendor/css/rtl/core.css" rel="stylesheet" />
  <link class="template-customizer-theme-css" href="/vuexy/assets/vendor/css/rtl/theme-default.css" rel="stylesheet" />
  <link href="/vuexy/assets/css/demo.css" rel="stylesheet" />
  <!-- Vendors CSS -->
  <link href="/vuexy/assets/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
  <link href="/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
  <link href="/vuexy/assets/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
  <!-- Vendor -->
  <link href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" rel="stylesheet" />
  <!-- Page CSS -->
  <link href="/vuexy/assets/vendor/css/pages/page-auth.css" rel="stylesheet" />
  <!-- Helpers -->
  <script src="/vuexy/assets/vendor/js/helpers.js"></script>
  <script src="/vuexy/assets/vendor/js/template-customizer.js"></script>
  <script src="/vuexy/assets/js/config.js"></script>
  <style>
    #navbar-collapse {
      margin-top: -1rem;
    }

    #heading {
        margin-top: 2rem;
    }

    @media (min-width: 992px) {
      #navbar-collapse {
        margin-top: -6rem;
      }
      #heading {
        margin-top: 0;
      }
    }
  </style>
  <!-- BEGIN: Custom CSS-->
  @include('admin.includes.branding-styles')
  <!-- END: Custom CSS-->
</head>
@php
    $heading = $l == 'en' ? 'Reset Password' : 'اعادة تعيين كلمة المرور';
    $subHeading =
    $l == 'en'
    ? 'Your new password must be different from previously used passwords'
    : 'يجب أن تكون كلمة المرور الجديدة مختلفة عن كلمات المرور المستخدمة سابقًا';
    $pwdLabel = $l == 'en' ? 'New Password' : 'كلمة المرور الجديدة';
    $confirmPwdLabel = $l == 'en' ? 'Confirm Password' : 'تأكيد كلمة المرور';
    $backToLogin = $l == 'en' ? 'Back to login' : 'العودة لتسجيل الدخول';
    $setNewPwd = $l == 'en' ? 'Set new password' : 'تعيين كلمة المرور الجديدة';
@endphp

<body>
  <!-- Content -->
  <div class="authentication-wrapper authentication-cover">
    <!-- Logo -->
    @php
        $setting = App\Settings::findorfail(1)->first();
    @endphp
    <a class="app-brand auth-cover-brand" href="/dashboard">
      <img src="{{ $setting ? asset('images/logo/' . $setting->Logo) : asset('images/background/logo.png') }} " alt="logo" class="w-px-50 h-px-50 rounded-circle bg-card border object-contain">
    </a>
    <!-- /Logo -->
    <div class="authentication-inner row m-0">
      <!-- /Left Text -->
      <div class="d-none d-lg-flex col-lg-8 p-0">
        <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
          <img class="my-5 auth-illustration"
            data-app-light-img="illustrations/auth-reset-password-illustration-light.png"
            data-app-dark-img="illustrations/auth-reset-password-illustration-dark.png"
            src="/vuexy/assets/img/illustrations/auth-reset-password-illustration-light.png"
            alt="auth-reset-password-cover" />
          <img class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png"
            data-app-dark-img="illustrations/bg-shape-image-dark.png"
            src="/vuexy/assets/img/illustrations/bg-shape-image-light.png" alt="auth-reset-password-cover" />
        </div>
      </div>
      <!-- /Left Text -->

      <!-- Reset Password -->
      <div class="d-flex col-12 col-lg-4 align-items-center authentication-bg">
        <div class="w-px-400 mx-auto mt-12 pt-5">
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
          <h4 class="mb-1 mt-md-0" id="heading">{{ $heading }} 🔒</h4>
          <p class="mb-6">
            <span class="fw-medium">{{ $subHeading }}</span>
          </p>
          <form class="mb-6" id="resetPasswordForm" method="POST" action="{{ route('password.update') }}">
            @csrf
            <input name="token" type="hidden" value="{{ $token }}">
            <div class="mb-6">
              <label class="form-label fs-6 fw-semibold" for="email">{{ __('auth.E-Mail') }}</label>
              <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email"
                value="{{ $email ?? old('email') }}" readonly required autocomplete="email" autofocus>
               
            </div>
            <div class="mb-2">
              @error('forget')
              <span class="invalid-feedback" role="alert">
                 <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
           
            <div class="mb-6 form-password-toggle">
              <label class="form-label fs-6 fw-semibold" for="password">{{ $pwdLabel }}</label>
              <div class="input-group input-group-merge">
                <input class="form-control" id="password" name="password" type="password" aria-describedby="password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                @error('password')
                      <span class="invalid-feedback" role="alert">
                             <strong>{{ $message }}</strong>
                      </span>
                @enderror

              </div>
            </div>
            <div class="mb-6 form-password-toggle">
              <label class="form-label fs-6 fw-semibold" for="confirm-password">
                {{ $confirmPwdLabel }}
              </label>
              <div class="input-group input-group-merge">
                <input class="form-control" id="confirm-password" name="password_confirmation" type="password"
                  aria-describedby="password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
              </div>
            </div>
            <button class="btn btn-primary d-grid w-100 mb-6">
              <span>{{ $setNewPwd }}</span>
            </button>
            <div class="text-center">
              <a href="/login">
                <i class="ti ti-chevron-left scaleX-n1-rtl me-1_5"></i>
                <span>{{ $backToLogin }}</span>
              </a>
            </div>
          </form>
        </div>
      </div>
      <!-- /Reset Password -->
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
  <!-- Page JS -->
  <script>
    const translations = {
      en: {
        email: {
          notEmpty: 'Please enter your email',
          emailAddress: 'Please enter valid email address'
        },
        email_username: {
          notEmpty: 'Please enter email / username',
          stringLength: 'Username must be more than 6 characters'
        },
        password: {
          notEmpty: 'Please enter your password',
          stringLength: 'Password must be 8-20 characters long',
          strong: 'Password must be strong including uppercase, lowercase, numbers, and special characters.'
        },
        password_confirmation: {
          notEmpty: 'Please confirm password',
          identical: 'The password and its confirm are not the same',
          stringLength: 'Password must be 8-20 characters long',
          strong: 'Password must be strong including uppercase, lowercase, numbers, and special characters.'
        },
      },
      ar: {
        email: {
          notEmpty: 'يرجى إدخال بريدك الإلكتروني',
          emailAddress: 'يرجى إدخال عنوان بريد إلكتروني صالح'
        },
        email_username: {
          notEmpty: 'يرجى إدخال البريد الإلكتروني / اسم المستخدم',
          stringLength: 'يجب أن يكون اسم المستخدم أكثر من 8 أحرف'
        },
        password: {
          notEmpty: 'يرجى إدخال كلمة المرور الخاصة بك',
          stringLength: 'يجب ان تتكون كلمة المرور من 8-20 حرفًا',
          strong: 'كلمة السر يجب أن تكون قوية حيث تتضمن حروفًا كبيرة وصغيرة وأرقامًا ورموزًا'
        },
        password_confirmation: {
          notEmpty: 'يرجى تأكيد كلمة المرور',
          identical: 'كلمة المرور وتأكيدها غير متطابقين',
          stringLength: 'يجب ان تتكون كلمة المرور من 8-20 حرفًا',
          strong: 'كلمة السر يجب أن تكون قوية حيث تتضمن حروفًا كبيرة وصغيرة وأرقامًا ورموزًا'
        },
      }
    };

    function getValidationConfig(lang) {
      const config = {
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
              notEmpty: {
                message: translations[lang].password.notEmpty
              },
              stringLength: {
                min: 8,
                max: 20,
                message: translations[lang].password.stringLength
              },
              regexp: {
                regexp:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/,
                message: translations[lang].password.strong
              }
            }
          },
          password_confirmation: {
            validators: {
              notEmpty: {
                message: translations[lang].password_confirmation.notEmpty
              },
              stringLength: {
                min: 8,
                max: 20,
                message: translations[lang].password_confirmation.stringLength
              },
              regexp: {
                regexp:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/,
                message: translations[lang].password_confirmation.strong
              },
              identical: {
                compare: function () {
                  return document.getElementById('resetPasswordForm').querySelector('[name="password"]').value;
                },
                message: translations[lang].password_confirmation.identical
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
            if (e.element.parentElement.classList.contains(
              'input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e
                .messageElement);
            }
          });
        }
      };
      return config;
    };

    document.addEventListener('DOMContentLoaded', function () {
      const lang = document.documentElement.lang;
      const config = getValidationConfig(lang);
      const form = document.getElementById('resetPasswordForm');
      const fv = FormValidation.formValidation(form, config);
    });
  </script>
</body>

</html>
