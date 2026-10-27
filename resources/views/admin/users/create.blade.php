@extends('layouts.admin')

@section('title', __('admin.Add new user'))

@section('index_url', route('users.index'))
@section('subTitle', __('admin.Add new user'))

@section('vendorstyle')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
@endsection

@section('style')
<style>
  .iti__country-list {
    position: absolute;
    left: 1rem;
    /* right: 0; */
    z-index: 9999;
    list-style: none;
    text-align: left;
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
    background-color: #fff;
  }
</style>
@endsection

@section('content')
<div class="">
  <div class="">
    <div class="card">
      <div class="card-header mb-6 border-bottom">
        <h5 class="card-title mb-0 text-capitalize">{{ __('admin.Add new user') }}</h5>
      </div>
      <div class="card-body">
        @if (session()->has('msg'))
          <x-alert 
            type="danger"
            message="{{ __('admin.' . session()->get('msg')) }} {{ Session::forget('msg') }}"
          />
        @endif
        @if (session()->has('success'))
          <x-alert
            type="success"
            message="{{ __('admin.' . session()->get('success')) }} {{ Session::forget('success') }}"
          />
        @endif

        @if (count($errors) > 0)
          <ul class="alert alert-danger alert-dismissible ps-8">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        @endif

        <form method="post" action="{{ route('users.store') }}" enctype="multipart/form-data" id="user-form">
          {!! csrf_field() !!}
          <div class="row">
            <div class="col-md-6 col-12">
              <div class="form-group mb-4">
                <label class="form-label" for="first_name">{{ __('admin.First Name') }}</label>
                <input
                  name="first_name"
                  type="text"
                  class="form-control"
                  id="first_name"
                  placeholder="{{ __('admin.First Name') }}"
                  autocomplete="first_name"
                  autofocus
                />
              </div>
            </div>

            <div class="col-md-6 col-12">
              <div class="form-group mb-4">
                <label class="form-label" for="last_name">{{ __('admin.Last Name') }}</label>
                <input
                  name="last_name"
                  type="text"
                  class="form-control"
                  id="last_name"
                  placeholder="{{ __('admin.Last Name') }}"
                  autocomplete="last_name"
                />
              </div>
            </div>

            <div class="col-md-6 col-12">
              <div class="form-group mb-4">
                <label class="form-label" for="gender">{{ __('admin.Gender') }}</label>
                <select class="form-select" id="gender" name="gender">
                  <option value="">{{ __('admin.Select Gender') }}</option>
                  <option value="Male">{{ __('Male') }}</option>
                  <option value="Female">{{ __('Female') }}</option>
                </select>
              </div>
            </div>

            <div class="col-md-6 col-12">
              <div class="form-group mb-4">
                <label class="form-label" for="role">{{ __('admin.Role') }}</label>
                <select class="form-select" id="role" name="role">
                  <option disabled value=""
                    {{ request('role') !== 'mentor' ? 'selected' : '' }}>
                    {{ __('admin.Select_Role') }}
                  </option>

                  @foreach ($roles as $role)
                    <option value="{{ $role['default_for'] }}"
                        {{ request('role') === 'mentor' && $role['default_for'] === 'mentor' ? 'selected' : '' }}>
                        {{ __('admin.' . $role['name']) }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="col-md-6 col-12">
              <div class="form-group mb-4">
                <label class="form-label" for="email">{{ __('admin.Email') }}</label>
                <input
                  name="email"
                  type="text"
                  class="form-control"
                  id="email"
                  placeholder="{{ __('admin.Email') }}"
                  autocomplete="email"
                />
              </div>
            </div>

            <div class="col-md-6 col-12">
              <div class="form-group d-flex flex-column mb-4">
                <label class="form-label" for="phone">
                  {{ __('auth.Phone') }}
                </label>
                <input
                  id="phone"
                  type="tel"
                  class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}"
                  placeholder="{{ __('auth.Phone') }}"
                  name="phone"
                />

                <div class="invalid-feedback mt-1" id="phone-error" style="display: none;"></div>
                <input type="hidden" name="country_code" id="phone_code" value="20" />

                @error('phone')
                  <small class="invalid-feedback" data-server-error>{{ $message }}</small>
                @enderror

                @error('phone_code')
                  <small class="invalid-feedback" data-server-error>{{ $message }}</small>
                @enderror
              </div>
            </div>

            <div class="col-12 col-md-6">
              <div class="form-group form-password-toggle mb-4">
                <label class="form-label" for="password">{{ __('admin.Password') }}</label>
                <div class="input-group input-group-merge shadow-none">
                  <input
                    type="password"
                    class="form-control"
                    id="password"
                    placeholder="{{ __('admin.Password') }}"
                    name="password"
                    autocomplete="new-password"
                  />
                  <span class="input-group-text cursor-pointer border border-light">
                    <i class="ti ti-eye-off"></i>
                  </span>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <div class="form-group form-password-toggle mb-4">
                <label class="form-label" for="password-confirm">{{ __('admin.Confirm Password') }}</label>
                <div class="input-group input-group-merge shadow-none">
                  <input
                    type="password"
                    class="form-control"
                    id="password-confirm"
                    placeholder="{{ __('admin.Confirm Password') }}"
                    name="password_confirmation"
                    autocomplete="new-password"
                  />
                  <span class="input-group-text cursor-pointer border border-light">
                    <i class="ti ti-eye-off"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 d-flex justify-content-between align-items-center gap-4 mt-8">
            <a id="back-button" href="#" role="button" aria-label="Go back" class="btn btn-label-secondary">
              <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i>
              {{ __('admin.Back') }}
            </a>
            <button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
@endsection

@section('pagescripts')
<script>
  const translations = {!! json_encode(__('auth')) !!};

  // Initialize the phone input
  const phoneInput = document.getElementById("phone");
  const countryCodeInput = document.querySelector("#phone_code");

  // Initialize the phone input
  if (phoneInput) {
    iti = intlTelInput(phoneInput, {
      initialCountry: 'eg',
      preferredCountries: ["eg", "ae", "sa", "ma", "dz", "tn"],
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
      formatOnDisplay: false,
      separateDialCode: true,
      autoPlaceholder: "aggressive",
      customPlaceholder: function(selectedCountryPlaceholder) {
        return "e.g. " + selectedCountryPlaceholder;
      }
    });

    // Set initial country code
    if (countryCodeInput) {
      const initialCountryData = iti.getSelectedCountryData();
      if (initialCountryData && initialCountryData.dialCode) {
        countryCodeInput.value = initialCountryData.dialCode;
      }
    }

    // Update country code when country changes
    phoneInput.addEventListener('countrychange', function() {
      const countryData = iti.getSelectedCountryData();
      if (countryCodeInput && countryData && countryData.dialCode) {
        countryCodeInput.value = countryData.dialCode;
      }
      
      // Revalidate phone field when country changes
      if (validationInstance) {
        validationInstance.revalidateField('phone');
      }
    });
  }

  // Initialize FormValidation
  const form = document.getElementById('user-form');
  const fv = FormValidation.formValidation(form, {
    fields: {
      first_name: {
        validators: {
          notEmpty: {
            message: translations.validate_first_name_required
          },
          stringLength: {
            min: 2,
            max: 50,
            message: translations.validate_first_name_string_length
          },
          regexp: {
            regexp: /^[\p{L}\s]+$/u,
            message: translations.validate_first_name_regexp
          }
        }
      },
      last_name: {
        validators: {
          notEmpty: {
            message: translations.validate_last_name_required
          },
          stringLength: {
            min: 2,
            max: 50,
            message: translations.validate_last_name_string_length
          },
          regexp: {
            regexp: /^[\p{L}\s]+$/u,
            message: translations.validate_last_name_regexp
          }
        }
      },
      gender: {
        validators: {
          notEmpty: {
            message: translations.validate_gender_required
          }
        }
      },
      role: {
        validators: {
          notEmpty: {
            message: translations.validate_role_required
          }
        }
      },
      email: {
        validators: {
          notEmpty: {
            message: translations.validate_email_required
          },
          emailAddress: {
            message: translations.validate_email_email
          }
        }
      },
      phone: {
        validators: {
          notEmpty: {
            message: translations.validate_phone_required
          },
          callback: {
            message: translations.validate_phone_valid,
            callback: function(input) {
              const value = input.value.trim();
              
              // Skip validation if empty (handled by notEmpty validator)
              if (value === '') {
                return { valid: true };
              }
              
              // Check if contains only numbers and allowed phone characters
              const numericRegex = /^[\d\s\+\-\(\)]+$/;
              if (!numericRegex.test(value)) {
                return {
                  valid: false,
                  message: translations.validate_phone_numbers_only
                };
              }
              
              // Get selected country
              let selectedCountry = '';
              if (iti && typeof iti.getSelectedCountryData === 'function') {
                const countryData = iti.getSelectedCountryData();
                selectedCountry = countryData.iso2 || '';
              }
              
              // For Egypt, allow numbers starting with 0
              if (selectedCountry.toLowerCase() === 'eg') {
                // Egyptian phone number validation
                // Format: 0XXXXXXXXX (11 digits) or XXXXXXXXXX (10 digits)
                const cleanNumber = value.replace(/[\s\-\(\)]/g, '');
                
                // Check if it's a valid Egyptian number
                if (cleanNumber.startsWith('0')) {
                  // Should be 11 digits total
                  if (cleanNumber.length === 11 && /^0(10|11|12|15|2)\d{8}$/.test(cleanNumber)) {
                    return { valid: true };
                  } else {
                    return {
                      valid: false,
                      message: translations.validate_phone_valid
                    };
                  }
                } else if (cleanNumber.length === 10 && /^(10|11|12|15|2)\d{8}$/.test(cleanNumber)) {
                  // 10 digits without leading 0
                  return { valid: true };
                } else {
                  return {
                    valid: false,
                    message: translations.validate_phone_valid
                  };
                }
              }
              
              // Validate using intl-tel-input for other countries
              if (iti && typeof iti.isValidNumber === 'function') {
                const isValid = iti.isValidNumber();
                
                if (!isValid) {
                  // Get the validation error
                  if (typeof iti.getValidationError === 'function') {
                    const error = iti.getValidationError();
                    let message = translations.validate_phone_valid;
                    
                    // Check if intlTelInputUtils is available
                    if (window.intlTelInputUtils && window.intlTelInputUtils.validationError) {
                      switch (error) {
                        case window.intlTelInputUtils.validationError.INVALID_COUNTRY_CODE:
                          message = translations.validate_phone_valid;
                          break;
                        case window.intlTelInputUtils.validationError.TOO_SHORT:
                          message = translations.validate_phone_valid;
                          break;
                        case window.intlTelInputUtils.validationError.TOO_LONG:
                          message = translations.validate_phone_valid;
                          break;
                        case window.intlTelInputUtils.validationError.NOT_A_NUMBER:
                          message = translations.validate_phone_valid;
                          break;
                        default:
                          message = translations.validate_phone_valid;
                      }
                    }
                    
                    return {
                      valid: false,
                      message: message
                    };
                  }
                  
                  return {
                    valid: false,
                    message: translations.validate_phone_valid
                  };
                }
                
                return { valid: true };
              }
              
              // If iti is not available, do basic validation
              const phoneRegex = /^\+?[\d\s\-\(\)]{8,}$/;
              if (!phoneRegex.test(value)) {
                return {
                  valid: false,
                  message: translations.validate_phone_valid
                };
              }
              
              return { valid: true };
            }
          }
        }
      },
      password: {
        validators: {
          notEmpty: {
            message: translations.validate_password_required
          },
          stringLength: {
            min: 8,
            max: 20,
            message: translations.validate_password_min_length
          },
          regexp: {
            regexp: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$/,
            message: translations.validate_password_regexp
          }
        }
      },
      password_confirmation: {
        validators: {
          notEmpty: {
            message: translations.validate_password_confirmation_required
          },
          identical: {
            compare: function() {
              return form.querySelector('[name="password"]').value;
            },
            message: translations.validate_password_confirmation_same
          }
        }
      }
    },
    plugins: {        
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        rowSelector: ".form-group",
        eleValidClass: '',
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
      autoFocus: new FormValidation.plugins.AutoFocus(),
      trigger: new FormValidation.plugins.Trigger(),
    }
  });
</script>

<script>
  // Server-rendered validation fallbacks are shown once, on a full page load,
  // and would otherwise sit next to a field the user has already corrected.
  // Drop them as soon as any field in the form is edited.
  //
  // Only elements carrying [data-server-error] are touched, so the messages
  // that FormValidation's bootstrap5 plugin injects (which also use
  // .invalid-feedback) are left to the library to manage on its own.
  $(document).on('input change', 'form [name]', function () {
    const $group = $(this).closest('.form-group');

    $group.find('[data-server-error]').text('').hide();
    $group.find('.is-invalid').removeClass('is-invalid');
  });
</script>
@endsection
