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
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />

  <!-- Page CSS -->
  <link rel="stylesheet" href="/vuexy/assets/vendor/css/pages/page-auth.css" />

  <style>
    .authentication-wrapper.authentication-cover .authentication-inner {
      display: flex;
      align-items: stretch;
    }

    .authentication-wrapper.authentication-cover .authentication-inner .auth-cover-bg {
      height: 100vh;
      min-height: 100vh;
      position: sticky;
      top: 0;
    }

    .authentication-wrapper.authentication-cover .authentication-inner>.d-flex {
      min-width: 0;
      max-width: 100%;
    }

    /* Left form column: same max width as the founder/mentor register page */
    .authentication-wrapper.authentication-cover .authentication-inner .w-px-460 {
      max-width: 520px;
      width: 100%;
      box-sizing: border-box;
    }

    /* Centered logo above the heading, tight spacing beneath it */
    .auth-form-logo {
      display: flex;
      justify-content: center;
      margin-bottom: 0.5rem;
    }

    .auth-form-logo img {
      width: 32px;
      height: auto;
    }

    /* Center the heading + subheading under the logo */
    .auth-form-heading {
      text-align: center;
    }

    /* Cloudflare-style plain sign-in row (no boxed white card) */
    .auth-signin-row {
      text-align: center;
    }

    .auth-signin-row p {
      margin-bottom: 0.5rem;
    }

    .auth-signin-row a {
      color: #fff;
      font-weight: 600;
      text-decoration: underline;
    }

    /* Consistent tight vertical rhythm across all form groups */
    #registerForm .form-group-tight {
      margin-bottom: 1.25rem;
    }

    .iti__country-list {
      direction: ltr !important;
      text-align: left !important;
      position: absolute;
      left: 1rem;
      z-index: 9999;
      list-style: none;
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
    }
  </style>

  <!-- Helpers -->
  <script src="/vuexy/assets/vendor/js/helpers.js"></script>
  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

  <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.	-->
  <script src="/vuexy/assets/vendor/js/template-customizer.js"></script>

  <!--? Config:	Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.	-->
  <script src="/vuexy/assets/js/config.js"></script>
</head>
<!-- END: Head-->
<!-- BEGIN: Body-->

<body>
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
      <!-- /Register -->
      <div class="d-flex col-12 col-lg-6 align-items-center authentication-bg p-sm-12 p-6">
        <div class="w-px-460 mx-auto mt-12 pt-5">

          <!-- Logo, centered above the heading -->
          <a href="/dashboard" class="auth-form-logo text-decoration-none">
            <img src="{{$logo}}" alt="avatar" width="32">
          </a>

          <div class="auth-form-heading">
            <h4 class="mb-1 lh-base text-capitalize">{{ __('auth.formHeading') }} 🚀</h4>
            <p class="mb-6">{{ __('auth.formSubHeading') }}</p>
          </div>

          @if($setting->set_order)
            <form id="registerForm" class="mb-0" method="POST" action="{{ route('provider-registration') }}"
              enctype="multipart/form-data" novalidate>
              @csrf
              <div class="row">
                <div>
                  @if(session('error'))
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
                  @if(session('success'))
                    <div class="row">
                      <div class="col-md-12">
                        <div role="alert" class="alert alert-success alert-dismissible mb-2">
                          {{ __('auth.provider_registration_recorded') }}
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                      </div>
                    </div>
                  @endif

                  <div class="col-12 form-group-tight">
                    <label for="company_name" class="form-label fs-6 mb-2 w-100">{{ __('auth.companyName') }}</label>
                    <div class="input-group input-group-merge">
                      <input name="company_name" type="text" class="form-control" id="company_name"
                        placeholder="{{ __('auth.enterCompanyName') }}" required autofocus />
                      @error('company_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-12 form-group-tight">
                    <label for="contact_name" class="form-label fs-6 mb-2 w-100">{{ __("auth.contactName") }}</label>
                    <div class="input-group input-group-merge">
                      <input name="contact_name" type="text" class="form-control" id="contact_name"
                        placeholder="{{ __('auth.enterContactName') }}" required />
                    </div>
                    @error('contact_name')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12 form-group-tight">
                    <label for="email" class="form-label fs-6 mb-2 w-100">{{ __('auth.E-Mail') }}</label>
                    <div class="input-group input-group-merge">
                      <input name="email" type="email" class="form-control" placeholder="{{ __('auth.E-Mail') }}"
                        required />
                    </div>
                    @error('email')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="col-12 form-group-tight d-grid">
                    <label class="form-label fs-6 mb-2 w-100" for="phone">{{ __('auth.Phone') }}</label>
                    <input id="phone" type="tel" class="form-control" placeholder="{{ __('auth.Phone') }}" name="phone"
                      required />
                    <small class="text-danger mt-1" id="phone-error" style="display: none;"></small>
                    <input type="hidden" name="country_code" id="phone_code" value="20">
                    @error('phone')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @error('country_code')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="form-group-tight">
                    @if(!empty($termsUrl) || !empty($privacyUrl))
                      <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="terms_agreement" id="terms_agreement" required
                          value="agreed">
                        <label class="form-check-label" for="terms_agreement">
                          @if(!empty($termsUrl) && !empty($privacyUrl))
                                            {!! __('auth.register_agreement', [
                                              'terms' => '<a href="' . $termsUrl . '" target="_blank">' . __('admin.terms') . '</a>',
                                              'privacy' => '<a href="' . $privacyUrl . '" target="_blank">' . __('admin.privacy') . '</a>'
                                            ]) !!}
                          @elseif(!empty($termsUrl))
                                            {!! __('auth.register_agreement_terms', [
                                              'terms' => '<a href="' . $termsUrl . '" target="_blank">' . __('admin.terms') . '</a>'
                                            ]) !!}
                          @elseif(!empty($privacyUrl))
                                            {!! __('auth.register_agreement_privacy', [
                                              'terms' => '<a href="' . $termsUrl . '" target="_blank">' . __('admin.terms') . '</a>'
                                            ]) !!}
                          @endif
                        </label>
                      </div>
                    @endif
                  </div>

                  <div class="col-12">
                    <button type="submit" class="btn btn-primary w-100 mb-4"> {{ __('auth.Register') }}</button>
                  </div>
                </div>
              </div>
            </form>
          @else
            <div class="alert alert-warning text-center col-12">
              <span class="">{{ __('admin.Register_Disabled') }}</span>
            </div>
          @endif

          <!-- Shown here for small/medium screens, where the right-side cover panel is hidden -->
          <p class="text-center d-lg-none">
            <span>{{ __('Already have account') }}{{ $l == 'ar' ? '؟' : '?' }}</span>
            <a href="{{ route('login') }}">
              <span>{{ $l == 'en' ? 'Sign in instead' : 'سجل الدخول' }}</span>
            </a>
          </p>

          @php
            $data = request('data') ?? '2521';
          @endphp

          @if ((isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled') || (isset($setting) && ($setting->GOOGLE_LOGIN ?? 'disabled') === 'enabled'))
            <div class="divider my-6">
              <div class="divider-text">{{ __('auth.or') }}</div>
            </div>
          @endif

          @if ((isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled') || (isset($setting) && ($setting->GOOGLE_LOGIN ?? 'disabled') === 'enabled'))
            <div class="d-flex justify-content-center">
              @if(isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled')
                <a href="{{ route('integrations.auth_linkedin_login', $data) }}"
                  class="btn btn-sm btn-icon rounded-circle btn-text-facebook me-3 shadow-sm"
                  style="background-color: #0077b5;">
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
      <!-- /Register -->

      <!-- /Right Text -->
      <div class="d-none d-lg-flex col-lg-6 p-0">
        <div class="auth-cover-bg d-flex justify-content-center align-items-center text-center p-6" style="
							background-image:
								radial-gradient(
									circle at 12% 12%,
									rgba(255, 255, 255, 0.18) 0%,
									rgba(255, 255, 255, 0.05) 32%,
									rgba(255, 255, 255, 0) 56%
								),
								radial-gradient(circle at 90% 88%, rgba(255, 255, 255, 0.14) 0%, rgba(255, 255, 255, 0) 45%),
								linear-gradient(155deg, #6d5df8 0%, #7367f0 42%, #a08eff 100%);
						">
          <div class="w-px-440">
            <h3 class="text-white fw-bold mb-2">{{ __('auth.welcome_community') }}</h3>
            <p class="text-white opacity-75 fw-medium mb-8">
              {{ __('auth.connect_text') }}
            </p>

            <!-- Sign in, integrated (no boxed card) -->
            <div class="auth-signin-row">
              <p class="text-white opacity-75 mb-1">{{ __('Already have account') }}{{ $l == 'ar' ? '؟' : '?' }}</p>
              <a href="{{ route('login') }}" class="text-white">{{ $l == 'en' ? 'Sign in instead' : 'سجل الدخول' }}</a>
            </div>

            <!-- Founder or mentor? -->
            <div class="auth-signin-row mt-6">
              <p class="text-white opacity-75 mb-1">{{ __('auth.founder_or_mentor_q') }}</p>
              <a href="{{ url('/register') }}" class="text-white">{{ __('auth.register_as_founder_mentor') }}</a>
            </div>
          </div>
        </div>
      </div>
      <!-- /Right Text -->
    </div>
  </div>
  <!-- / Content -->


  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->

  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
  <script src="/vuexy/assets/vendor/libs/jquery/jquery.js"></script>
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
    phoneInput.addEventListener('countrychange', function () {
      const countryData = iti.getSelectedCountryData();
      countryCodeInput.value = countryData.dialCode;
    });

    // Optional: Handle validation
    phoneInput.addEventListener("input", (e) => {
      if (!iti.isValidNumber()) {
        phoneError.style.display = 'block';
        phoneError.innerText = lang == 'en' ? 'Invalid phone number' : 'رقم الهاتف غير صحيح';
      } else {
        phoneError.style.display = 'none';
      }
    });

    // Validation messages
    const t = {
      phone: {
        notEmpty: lang == 'en' ? 'Phone number is required' : 'رقم الهاتف مطلوب',
        invalid: lang == 'en' ? 'Please enter a valid phone number' : 'يرجى إدخال رقم هاتف صحيح',
        phone: lang == 'en' ? 'Please enter numbers only' : 'يرجى إدخال أرقام فقط'
      }
    };

    // FormValidation: every field in the registration form is required
    const registerFormEl = document.getElementById('registerForm');

    if (registerFormEl) {
      const fvFields = {
        company_name: {
          validators: {
            notEmpty: {
              message: lang == 'en' ? 'Company name is required' : 'اسم الشركة مطلوب'
            }
          }
        },
        contact_name: {
          validators: {
            notEmpty: {
              message: lang == 'en' ? 'Contact name is required' : 'اسم المسؤول مطلوب'
            }
          }
        },
        email: {
          validators: {
            notEmpty: {
              message: lang == 'en' ? 'Email is required' : 'البريد الإلكتروني مطلوب'
            },
            regexp: {
              regexp: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i,
              message: lang == 'en' ? 'Please enter a valid email address' : 'يرجى إدخال بريد إلكتروني صحيح'
            }
          }
        },
        phone: {
          validators: {
            notEmpty: { message: t.phone.notEmpty },
            callback: {
              message: t.phone.invalid,
              callback: (input) => {
                const value = input.value.trim();

                if (value === "") return { valid: true };

                const numericRegex = /^[\d\s\+\-\(\)]+$/;
                if (!numericRegex.test(value)) {
                  return { valid: false, message: t.phone.phone };
                }

                let selectedCountry = "";
                if (
                  iti &&
                  typeof iti.getSelectedCountryData === "function"
                ) {
                  const countryData = iti.getSelectedCountryData();
                  selectedCountry = countryData.iso2 || "";
                }

                if (selectedCountry.toLowerCase() === "eg") {
                  const cleanNumber = value.replace(/[\s\-\(\)]/g, "");
                  if (cleanNumber.startsWith("0")) {
                    if (
                      cleanNumber.length === 11 &&
                      /^0(10|11|12|15|2)\d{8}$/.test(cleanNumber)
                    ) {
                      return { valid: true };
                    }
                    return { valid: false, message: t.phone.invalid };
                  }
                  if (
                    cleanNumber.length === 10 &&
                    /^(10|11|12|15|2)\d{8}$/.test(cleanNumber)
                  ) {
                    return { valid: true };
                  }
                  return { valid: false, message: t.phone.invalid };
                }

                if (iti && typeof iti.isValidNumber === "function") {
                  if (!iti.isValidNumber()) {
                    let message = t.phone.invalid;
                    if (
                      typeof iti.getValidationError === "function" &&
                      window.intlTelInputUtils &&
                      window.intlTelInputUtils.validationError
                    ) {
                      const error = iti.getValidationError();
                      switch (error) {
                        case window.intlTelInputUtils.validationError.NOT_A_NUMBER:
                          message = t.phone.phone;
                          break;
                        default:
                          message = t.phone.invalid;
                      }
                    }
                    return { valid: false, message };
                  }
                  return { valid: true };
                }

                const phoneRegex = /^\+?[\d\s\-\(\)]{8,}$/;
                if (!phoneRegex.test(value)) {
                  return { valid: false, message: t.phone.invalid };
                }
                return { valid: true };
              }
            }
          }
        }
      };

      // Only validate terms_agreement if the checkbox actually rendered
      // (it's conditional on $termsUrl / $privacyUrl being set server-side)
      if (document.getElementById('terms_agreement')) {
        fvFields.terms_agreement = {
          validators: {
            notEmpty: {
              message: lang == 'en' ? 'You must agree before submitting' : 'يجب الموافقة قبل الإرسال'
            }
          }
        };
      }

      const fv = FormValidation.formValidation(registerFormEl, {
        fields: fvFields,
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            rowSelector: '.form-group-tight, .form-check',
            eleValidClass: "", // removes the green "is-valid" styling
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: (instance) => {
          instance.on("plugins.message.placed", function (e) {
            if (e.element.parentElement.classList.contains("input-group")) {
              e.element.parentElement.insertAdjacentElement(
                "afterend",
                e.messageElement,
              );
            }
          });
        },
      });
    }
  </script>
</body>

</html>
