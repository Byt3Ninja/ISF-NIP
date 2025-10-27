@extends('layouts.admin')

@section('title', __('admin.Users'))
@section('style')
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
  
@endsection
@section('index_url', route('users.index'))
@section('subTitle', __('admin.Add new user'))

@section('content')
<!-- BEGIN: Content-->
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-overlay"></div>
    <div class="content-body">
      <!-- BuilderForm start -->
      <section id="basic-input">
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title pb-4 border-bottom">{{ __('admin.Add new user') }}</h5>
              </div>
              <div class="card-content">
                <div class="card-body">
                  @if (session()->has('msg'))
                  <div class="row">
                    <div class="col-md-12">
                      <div class="alert alert-warning alert-danger mb-2" role="alert">
                        {{ __('admin.' . session()->get('msg')) }}
                        {{ Session::forget('msg') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>
                    </div>
                  </div>
                  @endif
                  <!-- Validation messages -->
                  @if (count($errors) > 0)
                  <div class="row">
                    <div class="col-md-12">
                      <div class="alert alert-danger">
                        <ul>
                          @foreach ($errors->all() as $error)
                          <li>{{ $error }} </li>
                          @endforeach
                        </ul>
                      </div>
                    </div>
                  </div>
                  @endif
                  <form method="post" action="{{ route('users.store') }}" enctype="multipart/form-data">
                    {!! csrf_field() !!}
                    <div class="row">
                      <div class="col-md-12">
                        <fieldset>
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group mb-4">
                                <label class="fs-6 fw-semibold form-label mb-1" for="first_name">{{ __('admin.First Name') }}</label>
                                <input name="first_name" type="text" class="form-control" id="first_name"
                                  value="{{ old('first_name') }}" placeholder="{{ __('admin.First Name') }}" required
                                  autocomplete="first_name" autofocus />
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group mb-4">
                                <label class="fs-6 fw-semibold form-label mb-1" for="last_name">{{ __('admin.Last Name') }}</label>
                                <input value="{{ old('last_name') }}" name="last_name" type="text"
                                  class="form-control" id="last_name" placeholder="{{ __('admin.Last Name') }}"
                                  required autocomplete="last_name" />
                              </div>
                            </div>
                          </div>
                        </fieldset>
                        <fieldset>
                          <div class="row">
                            <div class="col-md-6 col-12 mb-4">
                              <label class="fs-6 fw-semibold form-label mb-1" for="gender">{{ __('admin.Gender') }}</label>
                              <select class="form-select" id="gender" name="gender" required>
                                <option value="">{{ __('admin.Select Gender') }}</option>
                                <option value="Male" {{ old('gender')=='Male' ? 'selected' : '' }}>{{ __('Male') }}
                                </option>
                                <option value="Female" {{ old('gender')=='Female' ? 'selected' : '' }}>{{ __('Female')
                                  }}</option>
                              </select>
                            </div>
                            <div class="col-md-6 col-12 mb-4">
                              <label class="fs-6 fw-semibold form-label mb-1" for="role">{{ __('admin.Role') }}</label>
                              <select class="form-select" required id="role" name='role'>
                                <option disabled value="" selected>{{ __('admin.Select_Role') }}</option>
                                @foreach ($roles as $role)
                                <option value="{{ $role['id'] }}" {{ old('role') == $role['name'] ? 'selected' : '' }}>
                                  {{ __('admin.' . $role['name']) }}
                                </option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                        </fieldset>
                        <fieldset>
                          <div class="row">
                            <div class="col-md-6 col-12">
                              <div class="form-group mb-4">
                                <label class="fs-6 fw-semibold form-label mb-1" for="email">{{ __('admin.Email') }}</label>
                                <input value="{{ old('email') }}" name="email" type="email" class="form-control"
                                  id="email" placeholder="{{ __('admin.Email') }}" required autocomplete="email">
                              </div>
                            </div>
                <div class="col-6 mb-4 d-grid">
                  <label class="form-label fs-6 fw-bold w-100 text-{{ $l == 'ar' ? 'end' : 'start' }}" for="phone">{{ __('auth.Phone') }}</label>
                  <input id="phone" type="tel" class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}" placeholder="{{ __('auth.Phone') }}" name="phone" required />
                  <small class="text-danger mt-1" id="phone-error" style="display: none;"></small>
                  <input type="hidden" name="country_code" id="phone_code" value="20">
                 @error('phone')
                    <small class="text-danger">{{ $message }}</small>
                  @enderror

                   @error('phone_code')
                    <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
                          </div>
                        </fieldset>
                        <fieldset class="form-group mb-3">
                          <div class="row">
                            <div class="col-12 col-md-6 mb-4 form-password-toggle">
                              <label class="fs-6 fw-semibold form-label mb-1" for="password">{{ __('admin.Password') }}</label>
                              <div class="input-group input-group-merge">
                                <input type="password" class="form-control" id="password"
                                  placeholder="{{ __('admin.Password') }}" name="password" required
                                  autocomplete="new-password">
                                <span class="input-group-text cursor-pointer border border-light"><i
                                    class="ti ti-eye-off"></i></span>
                              </div>
                            </div>
                            <div class="col-12 col-md-6 mb-4 form-password-toggle">
                              <label class="fs-6 fw-semibold form-label mb-1" for="password-confirm">{{ __('admin.Confirm Password') }}</label>
                              <div class="input-group input-group-merge">
                                <input type="password" class="form-control" id="password-confirm"
                                  placeholder="{{ __('admin.Confirm Password') }}" name="password_confirmation" required
                                  autocomplete="new-password">
                                <span class="input-group-text cursor-pointer border border-light"><i
                                    class="ti ti-eye-off"></i></span>
                              </div>

                            </div>
                          </div>
                        </fieldset>
                      </div>
                    </div>
                    <div class="row mt-6">
                      <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-1 mb-1">{{ __('admin.Save') }}</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- BuilderForm end -->
    </div>
  </div>
</div>
<!-- END: Content-->
@stop

<!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->
@section('scripts')
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
  {{-- <script src="/vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js"></script> --}}

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
    phoneInput.addEventListener('countrychange', function() {
      const countryData = iti.getSelectedCountryData();
      countryCodeInput.value = countryData.dialCode;
    });

    // Optional: Handle validation
    phoneInput.addEventListener("input", (e) => {
      console.log(e.target.value);
      if (!iti.isValidNumber()) {
        phoneError.style.display = 'block';
        phoneError.innerText = lang == 'en' ? 'Invalid phone number' : 'رقم الهاتف غير صحيح';
      } else {
        phoneError.style.display = 'none';
        // console.error("Invalid number");
      }
    });
  </script>

<script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>
<script>
  CKEDITOR.replace('description_ar', {
      language: 'ar'
    });
    CKEDITOR.replace('description_en', {
      language: 'en'
    });
</script>
@endsection