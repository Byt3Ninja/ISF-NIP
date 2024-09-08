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
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
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
  @php
    $brandName = 'GrowthLabs';
    $formHeading = $l == 'ar' ? 'تبدأ المغامرة من هنا' : 'Adventure starts here';
    $formSubHeading = $l == 'ar' ? 'اجعل إدارة تطبيقك سهلة وممتعة!' : 'Make your app management easy and fun!';
    $firstName = $l == 'ar' ? 'الاسم الأول' : 'First Name';
    $secondName = $l == 'ar' ? 'الاسم الاخير' : 'Last Name';
    $gender = $l == 'ar' ? 'النوع' : 'Gender';
    $googleSignIn = $l == 'ar' ? 'تسجيل الدخول بحساب جوجل' : 'Sign in with Google';
  @endphp
  <!-- Content -->

  <div class="authentication-wrapper authentication-cover">
     <!-- Logo -->
     <a href="/dashboard" class="app-brand auth-cover-brand">
      <img src="/images/logo/growthlab.png" alt="avatar" height="50" width="150">
      {{-- <span class="app-brand-logo demo">
        <img src="/app-assets/images/ico/favicon.ico" alt="avatar" height="25" width="25">
      </span> --}}
      {{-- <span class="app-brand-text demo text-heading fw-bold">{{ $brandName }}</span> --}}
    </a>
    <!-- /Logo -->
    <div class="authentication-inner row m-0">
      <!-- /Left Text -->
      <div class="d-none d-lg-flex col-lg-8 p-0">
        <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
          <img src="/vuexy/assets/img/illustrations/auth-register-illustration-light.png" alt="auth-register-cover"
            class="my-5 auth-illustration" data-app-light-img="illustrations/auth-register-illustration-light.png"
            data-app-dark-img="illustrations/auth-register-illustration-dark.png" 
            loading="lazy"
          />

          <img src="/vuexy/assets/img/illustrations/bg-shape-image-light.png" alt="auth-register-cover"
            class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png"
            data-app-dark-img="illustrations/bg-shape-image-dark.png" 
            loading="lazy"
          />
        </div>
      </div>
      <!-- /Left Text -->

      <!-- Register -->
      @if (App\Settings::findOrFail(1)->set_order)
        <div class="d-flex col-12 col-lg-4 align-items-center authentication-bg p-sm-12 p-6">
          <div class="w-px-400 mx-auto mt-12 pt-5">
            <h4 class="mb-1">{{ $formHeading }} 🚀</h4>
            <p class="mb-6">{{ $formSubHeading }}</p>

            <div>
              @if (count($errors) > 0)
                <div class="alert alert-danger">
                  <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif
            </div>

            <form id="formAuthentication" class="mb-6" method="POST" action="{{ route('register') }}">
              @csrf
              <div class="mb-6">
                <label for="username" class="form-label">{{$firstName }}</label>
                <input value="{{ old('first_name_en') }}" name="first_name_en" type="text"
                class="form-control" id="first_name_en"
                placeholder="{{ __('auth.First English Name') }}" required
                autocomplete="first_name_en" autofocus />
              </div>
              <div class="mb-6">
                <label for="username" class="form-label">{{ $secondName }}</label>
                <input value="{{ old('last_name_en') }}" name="last_name_en" type="text"
                class="form-control" id="last_name_en"
                placeholder="{{ __('auth.Last English Name') }}" required
                autocomplete="last_name_en" />
              </div>
              <div class="mb-6">
                <label for="email" class="form-label">{{ __('auth.E-Mail') }}</label>
                <input value="{{ old('email') }}" name="email" type="email"
                class="form-control" 
                placeholder="{{ __('auth.E-Mail') }}" required
                autocomplete="email" />
              </div>
              <div class="mb-6 form-password-toggle">
                <label class="form-label" for="password">{{ __('auth.Password') }}</label>
                <div class="input-group input-group-merge">
                  <input type="password" class="form-control" id="password"
                    placeholder="{{ __('auth.Password') }}" name="password"
                    required autocomplete="password"
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                </div>
              </div>
              <div class="mb-6 form-password-toggle">
                <label class="form-label" for="password">{{ __('auth.Confirm') }}</label>
                <div class="input-group input-group-merge">
                  <input type="password" class="form-control" id="confirm-password"
                    placeholder="{{ __('auth.Password') }}" name="password_confirmation"
                    required autocomplete="new-password"
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                </div>
              </div>

              <div class="mb-6">
                <label class="form-label" for="phone">{{ __('auth.Phone') }}</label>
                <div class="input-group">
                  <div style="width: 125px">
                    <select class="form-select" name="country_code" id="country_code" required>
                      <option value="+20">+20 (EG)</option>
                      <option value="+212">+212 (MA)</option>
                      <option value="+213">+213 (DZ)</option>
                      <option value="+216">+216 (TN)</option>
                      <option value="+218">+218 (LY)</option>
                      <option value="+973">+973 (BH)</option>
                      <option value="+965">+965 (KW)</option>
                      <option value="+968">+968 (OM)</option>
                      <option value="+974">+974 (QA)</option>
                      <option value="+966">+966 (KSA)</option>
                      <option value="+971">+971 (AE)</option>
                      <option value="+962">+962 (JO)</option>
                      <option value="+964">+964 (IQ)</option>
                      <option value="+963">+963 (SY)</option>
                      <option value="+961">+961 (LB)</option>
                      <option value="+970">+970 (PS)</option>
                      <option value="+249">+249 (SD)</option>
                      <option value="+967">+967 (YE)</option>
                    </select>
                  </div>
                  <input type="text" class="form-control mx-2 rounded" id="phone"
                      placeholder="{{ __('auth.Phone') }}" value="{{ old('phone') }}" name="phone"
                      required autocomplete="phone" />
                </div>
              </div>

              <div class="form-group mb-3">
                <label class="form-label mb-3" for="country">{{ $gender }}</label>
                <ul class="list-unstyled mb-0">
                  <li class="d-inline-block mx-2 mb-1">
                    <fieldset>
                        <div class="radio">
                            <input type="radio" name="gender" value="Male" id="rad1" checked="">
                            <label for="rad1">{{__('Male')}}</label>
                        </div>
                    </fieldset>
                  </li>
                  <li class="d-inline-block mx-2 mb-1">
                    <fieldset>
                        <div class="radio">
                            <input type="radio" name="gender" value="Female" id="rad2">
                            <label for="rad2">{{__('Female')}}</label>
                        </div>
                    </fieldset>
                  </li>
                </ul>
              </div>

              {{-- <div class="my-8">
                <div class="form-check mb-0 ms-2">
                  <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" />
                  <label class="form-check-label" for="terms-conditions">
                    I agree to
                    <a href="javascript:void(0);">privacy policy & terms</a>
                  </label>
                </div>
              </div> --}}
              <button class="btn btn-primary d-grid w-100"> {{ __('auth.Register') }}</button>
            </form>

            <p class="text-center">
              <span>{{__('Already have account')}}{{$l == 'ar' ? '؟' : '?'}}</span>
              <a href="/login">
                <span>{{__('Login')}}</span>
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
      @endif
      <!-- /Register -->
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
