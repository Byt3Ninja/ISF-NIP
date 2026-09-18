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
  <meta name="description" content="Register a new account">
  <meta name="author" content="info@growthlabs.app">
  <title>
    {{ $l == 'en' ? 'Register New Account' : 'تسجيل حساب جديد'}}
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
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" />

  <!-- Page CSS -->
  <!-- Page -->
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

    /* intl-tel-input: wrapper must fill the form column and sit on its own line */
    .iti {
      display: block;
      width: 100%;
    }

    /* Prevent the form column from stretching wider than its grid share when
       mentor fields (select2 multi-select) become visible */
    .authentication-wrapper.authentication-cover .authentication-inner > .d-flex {
      min-width: 0;
    }

    #mentor-fields,
    #mentor-fields .select2,
    #mentor-fields .select2-container {
      min-width: 0;
      max-width: 100%;
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

<body>
  @php
$setting = App\Settings::first();
$logo = $setting ? asset('images/logo/' . $setting->Logo) : asset('images/background/logo.png');
$accountRoles = [
  ['label' => 'Founder', 'icon' => 'ti ti-businessplan'],
  ['label' => 'Mentor', 'icon' => 'ti ti-user'],
  // ['label' => 'Service Provider', 'icon' => 'ti ti-briefcase'],
];
$termsUrl = setting('terms_url', $setting->terms_url ?? '#');
$privacyUrl = setting('privacy_url', $setting->privacy_url ?? '#');
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
          </li>
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

    <!-- Logo -->
    <a href="/dashboard" class="app-brand auth-cover-brand">
      <img src="{{$logo}}" alt="logo" width="32">
      <span class="app-brand-text demo text-heading fw-bold">Growthlabs</span>
    </a>
    <!-- /Logo -->

    <div class="authentication-inner row m-0">
      <!-- /Register -->
      <div class="d-flex col-12 col-lg-5 align-items-center authentication-bg p-sm-12 p-6">
        <div class="w-px-460 mx-auto mt-12 pt-5">
          <h4 class="mb-1">{{ __('auth.create_account') }}</h4>
          <p class="mb-6">{{ __('auth.sign_up_subheading') }}</p>
          
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
          
          @if (App\Settings::findOrFail(1)->set_order)
            <form id="formAuthentication" class="mb-0" method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
              @csrf
              <!-- Role -->
              <div class="mb-6">
                <label class="form-label">{{ __('auth.registeringAs') }}</label>
                <div class="row gy-3">
                  <div class="col-6">
                    <label for="account-founder" class="form-check custom-option custom-option-icon cursor-pointer h-100"
                      style="display: grid; place-items: center">
                      <div class="d-flex align-items-center gap-2">
                        <input name="role" class="form-check-input" type="radio" value="Founder"
                          id="account-founder" @checked(!old('role') || old('role') === 'Founder') />
                        <div class="form-check-label custom-option-content d-flex align-items-center">
                          <i class="ti ti-user-star me-2"></i>
                          <span class="custom-option-title fw-medium text-nowrap">{{ __('admin.Founder') }}</span>
                        </div>
                      </div>
                    </label>
                  </div>
                  <div class="col-6">
                    <label for="account-mentor" class="form-check custom-option custom-option-icon cursor-pointer h-100"
                      style="display: grid; place-items: center">
                      <div class="d-flex align-items-center gap-2">
                        <input name="role" class="form-check-input" type="radio" value="Mentor"
                          id="account-mentor" @checked(old('role') === 'Mentor') />
                        <div class="form-check-label custom-option-content d-flex align-items-center">
                          <i class="ti ti-user-check me-2"></i>
                          <span class="custom-option-title fw-medium text-nowrap">{{ __('admin.Mentor') }}</span>
                        </div>
                      </div>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Service Provider -->
              <a href="/provider-registration">
                <div class="card bg-light-subtle shadow-none border mb-6">
                  <div class="card-body py-4">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center gap-2">
                        <div
                          class="avatar avatar-md bg-card rounded border d-flex align-items-center justify-content-center">
                          <i class="ti ti-briefcase text-dark"></i>
                        </div>
                        <div class="d-flex flex-column">
                          <h6 class="mb-1">{{ __('admin.serviceProvider') }}</h6>
                          <small>{{ __('admin.serviceProvider_desc') }}</small>
                        </div>
                      </div>
                      <button type="button" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill">
                        <i class="ti ti-arrow-narrow-{{ $l == 'en' ? 'right' : 'left' }} fs-3"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </a>

              <!-- Personal details -->
              <div class="row g-3 mb-6">
                <div class="col-12 col-sm-6">
                  <label for="first-name" class="form-label">{{ __('auth.firstName') }}</label>
                  <input type="text" class="form-control" id="first-name" name="first_name" placeholder="{{ __('auth.placeholder_first_name') }}" />
                  @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-12 col-sm-6">
                  <label for="last-name" class="form-label">{{ __("auth.secondName") }}</label>
                  <input type="text" class="form-control" id="last-name" name="last_name" placeholder="{{ __('auth.placeholder_last_name') }}" />
                  @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Gender -->
              <div class="mb-6">
                <label class="form-label" for="gender">{{ __('auth.gender') }}</label>
                <select class="form-select select2" id="gender" name="gender" data-placeholder="{{ __('auth.placeholder_gender') }}">
                  <option value=""></option>
                  <option value="Female">{{ __('Female') }}</option>
                  <option value="Male">{{ __('Male') }}</option>
                </select>
                @error('gender')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Email -->
              <div class="mb-6">
                <label for="email" class="form-label">{{ __('auth.E-Mail') }}</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="ti ti-mail"></i></span>
                  <input type="email" class="form-control" id="email" name="email" placeholder="{{ __('auth.placeholder_email') }}" />
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
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                </div>
                @error('password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Confirm Password -->
              <div class="mb-6 form-password-toggle">
                <label class="form-label" for="confirm-password">{{ __('auth.Confirm') }}</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="confirm-password" class="form-control" name="password_confirmation"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="confirm-password" />
                  <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                </div>
                @error('password_confirmation')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- Phone -->
              <div class="mb-6">
                <label class="form-label d-block" for="phone-number">{{ __('auth.Phone') }}</label>
                <input type="tel" class="form-control" id="phone-number" name="phone"
                  placeholder="{{ __('auth.placeholder_phone') }}" />
                  <input type="hidden" name="country_code" id="phone_code" value="20" />
                  @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  @error('country_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
              </div>

              <!-- Mentor fields -->
              <div class="mb-6 {{ old('role') === 'Mentor' ? '' : 'd-none' }}" id="mentor-fields">
                <div class="mb-6">
                  <label class="form-label" for="job-title">{{ __('admin.JobTitleEn') }}</label>
                  <input type="text" class="form-control" id="job-title" name="title_en"
                    placeholder="{{ __('auth.placeholder_job_title') }}" value="{{ old('title_en') }}" />
                  @error('title_en')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="mb-6">
                  <label class="form-label" for="expertises">{{ __('auth.area_of_expertise') }}</label>
                  <select class="form-select" id="expertises" name="expertise[]" multiple>
                    @php
                      $expertises = App\Expertise::where('parent_id', null)->get();
                    @endphp
                    @foreach ($expertises as $expertise_group)
                      <optgroup label="{{ $expertise_group->title }}">
                        @foreach ($expertise_group->children as $expertise)
                          <option value="{{ $expertise->id }}" @selected(is_array(old('expertise')) && in_array($expertise->id, old('expertise')))> {{ $expertise->title }} </option>
                        @endforeach
                      </optgroup>
                    @endforeach
                    @error('expertise')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </select>
                </div>
                <div class="mb-6">
                  <label class="form-label" for="bio">{{ __('admin.userbio') }}</label>
                  <textarea class="form-control" id="bio" name="bio_en" rows="4"
                    placeholder="{{ __('auth.placeholder_bio') }}">{{ old('bio_en') }}</textarea>
                    @error('bio_en')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-6">
                  <label class="form-label" for="linkedin">{{ __('auth.linkedin_profile') }}</label>
                  <input type="url" class="form-control" id="linkedin" name="Linkedin"
                    placeholder="https://www.linkedin.com/in/" value="{{ old('Linkedin') }}" />
                    @error('Linkedin')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
              </div>
              <!-- /Mentor fields -->

              <!-- Terms -->
              <div class="my-8">
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" name="terms" id="terms" value="1" />
                  <label class="form-check-label" for="terms">
                    @if(!empty($termsUrl) && !empty($privacyUrl))
                      {!! __('admin.signup_agreement', [
                        'terms' => '<a href="' . $termsUrl . '" target="_blank">' . __('admin.terms') . '</a>',
                        'privacy' => '<a href="' . $privacyUrl . '" target="_blank">' . __('admin.privacy') . '</a>'
                      ]) !!}
                    @elseif(!empty($termsUrl))
                      {!! __('admin.signup_agreement_terms', ['terms' => '<a href="' . $termsUrl . '" target="_blank">' . __('admin.terms') . '</a>']) !!}
                    @elseif(!empty($privacyUrl))
                      {!! __('admin.signup_agreement_privacy', ['privacy' => '<a href="' . $privacyUrl . '" target="_blank">' . __('admin.privacy') . '</a>']) !!}
                    @endif
                  </label>
                  @error('terms')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <button type="submit" class="btn btn-primary btn-lg d-grid w-100">
                {{ __('auth.Register') }}
              </button>
            </form>
          @else
            <div class="alert alert-warning text-center col-12">
              <span class="">{{ __('admin.Register_Disabled') }}</span>
            </div>
          @endif

          @if ((isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled') || (isset($setting) && ($setting->GOOGLE_LOGIN ?? 'disabled') === 'enabled'))
            <div class="divider my-6">
              <div class="divider-text">{{ __('auth.or') }}</div>
            </div>
          @endif

          @if ((isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled') || (isset($setting) && ($setting->GOOGLE_LOGIN ?? 'disabled') === 'enabled'))
            <div class="d-flex justify-content-center">
              @if(isset($setting) && ($setting->LINKEDIN_LOGIN ?? 'disabled') === 'enabled')
                <a href="{{ route('integrations.auth_linkedin_login', '2521') }}"
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
      <!-- /Register -->

      <!-- /Right Text -->
      <div class="d-none d-lg-flex col-lg-7 p-0">
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
            <!-- Logo -->
            <a href="/dashboard" class="app-brand d-inline-flex justify-content-center mb-8 text-decoration-none">
              <span class="app-brand-logo demo">
                <img src="{{$logo}}" alt="logo" width="32">
              </span>
              <span class="app-brand-text demo text-white fw-bold">Growthlabs</span>
            </a>
            <!-- /Logo -->

            <h3 class="text-white fw-bold mb-2">{{ __('auth.welcome_community') }}</h3>
            <p class="text-white opacity-75 fw-medium mb-8">
              {{ __('auth.connect_text') }}
            </p>

            <!-- Sign in -->
            <div class="bg-white rounded-3 p-6 text-center shadow-none">
              <p class="fw-medium text-heading mb-1">{{ __('Already have account') }}{{ $l == 'ar' ? '؟' : '?' }}</p>
              <p class="text-muted small mb-6">{{ __('auth.sign_in_continue') }}</p>
              <a href="{{ route('login') }}" class="btn btn-primary d-grid w-100">{{ __('auth.Sign In') }}</a>
            </div>
            <!-- /Sign in -->
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
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>

  <!-- Main JS -->
  <script src="/vuexy/assets/js/main.js"></script>

  <!-- Page JS -->
  <script src="/vuexy/assets/js/pages-auth-register-founders.js"></script>
</body>

</html>
