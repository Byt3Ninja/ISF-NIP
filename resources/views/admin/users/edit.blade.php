@extends('layouts.admin')
@section('title', __('admin.Users') )
@section('index_url', route('users.index') )
@section('subTitle', __('admin.Edit user'))

@section('vendorstyle')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
<link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify.min.css">
<link rel="stylesheet" type="text/css" href="/app-assets/plugins/alertify/alertify-bootstrap.min.css">
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

@php
  $navPills = [
    'info' => [
      'title' => $l == 'en' ? 'Info' : 'المعلومات',
      'active' => true,
      'id' => 'navs-pills-info',
      'icon' => 'ti ti-user',
    ],
    'security' => [
      'title' => $l == 'en' ? 'Security' : 'الأمان',
      'active' => false,
      'id' => 'nav-pills-security',
      'icon' => 'ti ti-lock',
    ],
  ];

  // Add organizations tab for Sub-Admin role
  $userRole = $user->roles->first();
  $isSubAdmin = $userRole && strtolower($userRole->default_for ?? '') === 'subadmin';
  if ($isSubAdmin) {
    $navPills['organizations'] = [
      'title' => __('admin.Assigned Organizations'),
      'active' => false,
      'id' => 'nav-pills-organizations',
      'icon' => 'ti ti-building',
    ];
  }
@endphp

@section('content')
<div class="">
  <div class="">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center mb-6 border-bottom">
        <h5 class="card-title mb-0 text-capitalize">{{ __('admin.Edit user') }}</h5>
        <nav class="nav-align-top">
          <ul class="nav nav-tabs border-none" role="tablist">
            @foreach($navPills as $key => $navPill)
              <li class="nav-item me-2">
                <button type="button" class="nav-link {{ $navPill['active'] ? 'active' : '' }}" role="tab"
                  data-bs-toggle="tab" data-bs-target="#{{ $navPill['id'] }}" aria-controls="{{ $navPill['id'] }}"
                  aria-selected="{{ $navPill['active'] }}">
                  <span>
                    <i class="{{ $navPill['icon'] }} ti-sm me-1"></i>
                    {{ $navPill['title'] }}
                  </span>
                </button>
              </li>
            @endforeach
          </ul>
        </nav>
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

        <div class="tab-content p-0">
          <!-- Info -->
          <div class="tab-pane fade show active" id="navs-pills-info" role="tabpanel">
            @if( $_user->ableTo('users.login_as', $permissions) )
            <div class="text-end">
              <button id="start-impersonation" class="btn btn-xs btn-outline-primary" data-href="{{ route('users.login_as', ['id' => $user->id]) }}">
                <i class="fa-solid fa-user-secret me-2"></i> {{ __('admin.Impersonate this user') }}
              </button>
            </div>
            @endif
            <form method="post" action="{{route('users.update', ['user' => $user->id])}}" enctype="multipart/form-data" id="info-form">
              {!! csrf_field() !!}
              @method('patch')

              <div class="d-flex justify-content-between align-items-center mb-6">
                <div for="upload" class="d-flex align-items-center gap-4 mb-6">
                  <label
                    for="upload"
                    id="upload-label"
                    class="w-px-100 h-px-100 position-relative border-2 border-light border-dashed rounded-circle bg-label-light cursor-pointer"
                    style="background-image: url('/191014/{{ $user->img_url }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
                  >
                    <input hidden type="file" name="img_url" class="account-file-input" id="upload"
                      accept="image/*" />
                    <div class="position-absolute top-50 start-50 translate-middle z-1">
                      <i class="ti ti-camera-plus ti-28px text-light">
                      </i>
                    </div>
                  </label>
                  <span class="fs-6 fw-semibold">
                    {{ $l == 'en' ? 'Update User Logo' : 'تحديث صورة المستخدم' }}
                  </span>
                </div>

                @if ($user->company_id && $_user->ableTo('company.profile_view', $permissions))
                  <a 
                    class="btn btn-light" 
                    href="/dashboard/companyProfile/{{$user->company->id}}" 
                    target="_blank"
                  >
                    <i class="ti ti-building-skyscraper me-2"></i>
                    {{ __('startup.view_startup_profile') }}
                  </a>
                @endif
              </div>
              <div class="row">
                <div class="col-md-6 col-12">
                  <div class="form-group mb-4">
                    <label class="form-label" for="first_name">{{ __('admin.First Name') }}</label>
                    <input value="{{ $user->first_name_en}}" name="first_name" type="text" class="form-control"
                      id="first_name" placeholder="{{ __('admin.First Name') }}"
                      autocomplete="first_name" autofocus />
                  </div>
                </div>

                <div class="col-md-6 col-12">
                  <div class="form-group mb-4">
                    <label class="form-label" for="last_name">{{ __('admin.Last Name') }}</label>
                    <input value="{{ $user->last_name_en }}" name="last_name" type="text" class="form-control"
                      id="last_name" placeholder="{{ __('admin.Last Name') }}"
                      autocomplete="last_name" />
                  </div>
                </div>

                <div class="col-md-6 col-12">
                  <div class="form-group mb-4">
                    <label class="form-label" for="gender">{{ __('admin.Gender') }}</label>
                    <select class="form-select" id="gender" name="gender">
                      <option value="">{{ __('admin.Select Gender') }}</option>
                      <option value="Male" {{ $user->gender == 'Male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                      <option value="Female" {{ $user->gender == 'Female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-6 col-12">
                  <div class="form-group mb-4">
                    <label class="form-label" for="role">{{ __('admin.Role') }}</label>
                    @if(isset($user->roles->first()->default_for))
                    @php $currentRole = $user->roles->first()->default_for; @endphp
                    @else
                    @php $currentRole = ''; @endphp
                    @endif
                    <select class="form-select select2" id="role" name="role">
                      <option disabled value="" selected>{{ __('admin.Select_Role') }}</option>
                      @foreach($roles as $role)
                        <option value="{{ $role['id'] }}" {{ ($role['default_for'] == $currentRole)? "selected":"" }} >{{ __('admin.' . $role['name']) }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="col-md-6 col-12">
                  <div class="form-group mb-4">
                    <label class="form-label" for="email">{{ __('admin.Email') }}</label>
                    <input value="{{ $user->email }}" name="email" type="text" class="form-control"
                      id="email" placeholder="{{ __('admin.Email') }}" autocomplete="email" />
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group mb-4">
                    <label class="form-label" for="HaveCompany">{{ __('admin.HaveCompany')}}</label>
                    <select class="form-select" id="HaveCompany" name="HaveCompany">
                      <option value="1" {{ $user->HaveCompany ? 'selected' : '' }}>{{ __('Yes') }}</option>
                      <option value="" {{ !$user->HaveCompany ? 'selected' : '' }}>{{ __('No') }}</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group d-flex flex-column mb-4">
                    <label class="form-label" for="phone">
                      {{ __('auth.Phone') }}
                    </label>
                    <input type="tel" class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}"
                      id="phone" placeholder="{{ __('auth.Phone') }}" value="{{ $user->phone }}" name="phone" />

                    <div class="invalid-feedback mt-1" id="phone-error" style="display: none;"></div>
                    <input type="hidden" name="country_code" id="phone_code" value="{{ $user->country_code ?? '' }}" />

                    @error('phone')
                      <small class="invalid-feedback" data-server-error>{{ $message }}</small>
                    @enderror

                    @error('country_code')
                      <small class="invalid-feedback" data-server-error>{{ $message }}</small>
                    @enderror
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group mb-4">
                    <label for="title_en" class="form-label">{{ __('admin.JobTitleEn') }}</label>
                    <input type="text" class="form-control mb-3" id="title_en" style=""
                      placeholder="{{ __('admin.JobTitleEn') }}" value="{{ $user->title_en }}" name="title_en"
                      autocomplete="title_en">
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group mb-4">
                    <label for="country_id" class="form-label">{{ __('admin.Country')}}</label>
                    <select 
                      id="country_id" 
                      name='country_id'
                      class="form-select select2"
                      data-placeholder="{{ __('admin.Country')}}" 
                    >
                      <option value="0">{{ __('admin.Country')}}</option>
                      @foreach($countries as $country)
                      <option value="{{$country->id}}" {{($country->id==$user->country_id)? 'selected':''}}>
                        {{ $country->emoji . ' ' . $country->{'name_'.$l} }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group mb-4">
                    <label for="state_id" class="form-label">{{ __('admin.State')}}</label>
                    <select 
                      class="form-select select2" 
                      id="state_id"
                      name='state_id'
                      data-placeholder="{{ __('admin.State')}}"
                    >
                      <option value="0">{{ __('admin.State')}}</option>
                      @php $name = 'name_'.$l; @endphp
                      @if($user->state_id)
                      <option value="{{$user->state_id}}" selected>{{$user->state->{'name_'.$l} }}</option>
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group mb-4">
                    <label for="city_id" class="form-label">{{ __('admin.District')}}</label>
                    <select
                      class="form-select select2"
                      id="city_id"
                      name='city_id'
                      data-placeholder="{{ __('admin.Select District')}}"
                    >
                      <option value="0">{{ __('admin.District')}}</option>
                      @php $name = 'name_'.$l; @endphp
                      @if($user->city_id)
                      <option value="{{$user->city_id}}" selected>{{$user->city->{'name_'.$l} }}</option>
                      @endif
                    </select>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group mb-4">
                    <label for="houre_rate" class="form-label">{{ __('admin.HouresRate') }}</label>
                    <input value="{{ $user->houre_rate }}" name="houre_rate" type="number" class="form-control mb-3"
                      id="houre_rate" placeholder="{{ __('admin.HouresRate') }}" autocomplete="HouresRate">
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group mb-4">
                    <label class="form-label" for="email_verified_at">{{ __('admin.Verified')}}</label>
                    <select class="form-select mb-3" id="email_verified_at" name="email_verified_at">
                      <option value="{{ $user->email_verified_at ? $user->email_verified_at : now() }}" {{ $user->email_verified_at ? 'selected' : '' }}>{{ __('Yes') }}</option>
                      <option value="" {{ !$user->email_verified_at ? 'selected' : '' }}>{{ __('No') }}</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="col-12 d-flex justify-content-end align-items-center gap-4 mt-8">
                <a href="{{ route('users.index') }}" class="btn btn-label-secondary">{{ __('admin.Cancel') }}</a>
                <button type="submit" class="btn btn-primary">{{ __('admin.Update') }}</button>
              </div>
            </form>
          </div>
          <!-- /Info -->

          <!-- Security -->
          <div class="tab-pane fade" id="nav-pills-security" role="tabpanel">
            <form
              method="post"
              action="{{route('user.profile_update_password')}}"
              enctype="multipart/form-data"
              id="security-form"
            >
              {!! csrf_field() !!}
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group form-password-toggle mb-4">
                    <label class="form-label" for="password">{{ __('admin.Password') }}</label>
                    <div class="input-group input-group-merge shadow-none">
                      <input type="password" class="form-control" id="security-password"
                        placeholder="{{ __('admin.Password') }}" name="password"
                        autocomplete="new-password">
                      <input type="hidden" name='id' value='{{$user->id}}'>
                      <span class="input-group-text cursor-pointer border border-light">
                        <i class="ti ti-eye-off"></i>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group form-password-toggle mb-3">
                    <label class="form-label" for="password-confirm">{{ __('admin.Confirm') }}</label>
                    <div class="input-group input-group-merge shadow-none">
                      <input type="password" class="form-control" id="security-password-confirm"
                        placeholder="{{ __('admin.Password') }}" name="password_confirmation"
                        autocomplete="new-password">
                      <span class="input-group-text cursor-pointer border border-light">
                        <i class="ti ti-eye-off"></i>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12 d-flex justify-content-end align-items-center gap-4 mt-8">
                <button type="submit" class="btn btn-primary">{{__('admin.Update')}}</button>
              </div>
            </form>
          </div>
          <!-- /Security -->

          <!-- Assigned Organizations (Sub-Admin only) -->
          @if($isSubAdmin)
          <div class="tab-pane fade" id="nav-pills-organizations" role="tabpanel">
            <form
              method="post"
              action="{{ route('users.update_organizations', ['user' => $user->id]) }}"
              id="organizations-form"
            >
              @csrf
              @method('patch')
              <div class="row">
                <div class="col-12">
                  <div class="form-group mb-4">
                    <label class="form-label" for="organization_ids">
                      {{ __('admin.Assigned Organizations') }}
                    </label>
                    <p class="text-muted small mb-3">
                      {{ __('admin.select_subadmin_organizations_hint') }}
                    </p>
                    <select
                      class="form-select select2"
                      id="organization_ids"
                      name="organization_ids[]"
                      multiple="multiple"
                      data-placeholder="{{ __('admin.Select Organizations') }}"
                    >
                      @foreach($organizations as $org)
                        <option
                          value="{{ $org->id }}"
                          {{ in_array($org->id, $user->organizations->pluck('id')->toArray()) ? 'selected' : '' }}
                        >
                          {{ $org->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-12 d-flex justify-content-end align-items-center gap-4 mt-8">
                <a id="back-button" href="#" role="button" aria-label="Go back" class="btn btn-label-secondary">
                  <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i>
                  {{ __('admin.Back') }}
                </a>
                <button type="submit" class="btn btn-primary">{{ __('admin.Update') }}</button>
              </div>
            </form>
          </div>
          @endif
          <!-- /Assigned Organizations -->

        </div>
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
<script src="/app-assets/plugins/alertify/alertify.min.js"></script>
@endsection

@section('pagescripts')
<script>
	alertify.defaults.transition = 'zoom';
	alertify.defaults.theme.ok = 'btn btn-sm btn-danger';
	alertify.defaults.theme.cancel = 'btn btn-sm btn-info';
	alertify.defaults.theme.input = 'form-control';
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

    // Set initial country code if exists
    if (countryCodeInput && '{{ $user->country_code }}') {
      countryCodeInput.value = '{{ $user->country_code }}';
      // Only set country if the value is not a numeric dial code
      if (isNaN('{{ $user->country_code }}')) {
        iti.setCountry('{{ strtolower($user->country_code) }}');
      }
    } else if (countryCodeInput) {
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
      if (fvInfo) {
        fvInfo.revalidateField('phone');
      }
    });
  }

  // Initialize FormValidation for Info form
  const infoForm = document.getElementById('info-form');
  if (infoForm) {
    const fvInfo = FormValidation.formValidation(infoForm, {
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
        title_en: {
          validators: {
            stringLength: {
              min: 2,
              max: 100,
              message: translations.validate_job_title_string_length
            },
            regexp: {
              regexp: /^[a-zA-Z\s]+$/,
              message: translations.validate_job_title_regexp
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

                if (value === '') {
                  return { valid: true };
                }

                const numericRegex = /^[\d\s\+\-\(\)]+$/;
                if (!numericRegex.test(value)) {
                  return {
                    valid: false,
                    message: translations.validate_phone_numbers_only
                  };
                }

                let selectedCountry = '';
                if (iti && typeof iti.getSelectedCountryData === 'function') {
                  const countryData = iti.getSelectedCountryData();
                  selectedCountry = countryData.iso2 || '';
                }

                if (selectedCountry.toLowerCase() === 'eg') {
                  const cleanNumber = value.replace(/[\s\-\(\)]/g, '');

                  if (cleanNumber.startsWith('0')) {
                    if (cleanNumber.length === 11 && /^0(10|11|12|15|2)\d{8}$/.test(cleanNumber)) {
                      return { valid: true };
                    } else {
                      return {
                        valid: false,
                        message: translations.validate_phone_valid
                      };
                    }
                  } else if (cleanNumber.length === 10 && /^(10|11|12|15|2)\d{8}$/.test(cleanNumber)) {
                    return { valid: true };
                  } else {
                    return {
                      valid: false,
                      message: translations.validate_phone_valid
                    };
                  }
                }

                if (iti && typeof iti.isValidNumber === 'function') {
                  const isValid = iti.isValidNumber();

                  if (!isValid) {
                    return {
                      valid: false,
                      message: translations.validate_phone_valid
                    };
                  }

                  return { valid: true };
                }

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
        role: {
          validators: {
            notEmpty: {
              message: translations.validate_role_required
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
  }

  // Initialize FormValidation for Security form
  const securityForm = document.getElementById('security-form');
  if (securityForm) {
    const fvSecurity = FormValidation.formValidation(securityForm, {
      fields: {
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
                return securityForm.querySelector('[name="password"]').value;
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
  }
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

<script>
  const uploadInput = document.getElementById('upload');

  if (uploadInput) {
    uploadInput.addEventListener('change', (e) => {
      const file = e.target.files[0];

      if (file) {
        const reader = new FileReader();

        reader.onload = (e) => {
          const uploadLabel = document.getElementById('upload-label');
          if (uploadLabel) {
            uploadLabel.style.backgroundImage = `url(${e.target.result})`;
            uploadLabel.style.backgroundSize = 'cover';
            uploadLabel.style.backgroundPosition = 'center';
            uploadLabel.style.backgroundRepeat = 'no-repeat';
            uploadLabel.classList.remove('border-2', 'border-light', 'border-dashed');
          }
        }
        reader.readAsDataURL(file);
      }
    });
  }
</script>

<script>

  @if( $_user->ableTo('users.login_as', $permissions) )
  $(document).on('click', '#start-impersonation', function(e) {
    e.preventDefault();
    var login_url = $(this).data('href');
    alertify.confirm(
			"{{ __('admin.Confirm Action') }}",
			"{!! __('admin.ConfirmStartImpersonationMessage') !!}",
			function() {
				window.location.href = login_url;
			},
			function(){}
		).set('labels', {ok: "<i class='fa fa-check me-2'></i> {{ __('admin.Yes') }}", cancel: "{{ __('admin.cancel') }}"});
  });
  @endif

  $(document).ready(function() {
    getStates($('#country_id').val());
    $('#country_id').change(function () {
        getStates($(this).val());
    });
    $('#state_id').change(function () {
        getCities($(this).val());
    });
  });

  function getStates(country_id){
      var $state = $('#state_id');
      var currentState = '{{ $user->state_id }}';
      var selected = '';

      $.ajax({
        url: "{{ route('utils.locations_list_states_by_country') }}",
        data: {
          country_id: country_id
        },
        success: function (data) {
          $state.html('<option value="" >{{ __('admin.State')}}</option>');
          $.each(data, function (id, value) {
              // selected = '';
              if (currentState === id) {
                  selected = 'selected';
                  getCities(id)
              } else {
                  selected = '';
              }
              $state.append('<option value="' + id + '" ' + selected + '>' + value + '</option>');
              $('#city_id').html('<option value="" >{{ __('admin.District')}}</option>');
          });
          $('#state').show(150);
        }
    });
  }

  function getCities(state_id){
    var $city = $('#city_id');
    var currentCity = '{{ $user->city_id }}';
    var selected = '';
    $.ajax({
        url: "{{ route('cities.index') }}",
        data: {
            state_id: state_id
        },
        success: function(data) {
            $city.html('<option value="" >{{ __('admin.District')}}</option>');
            $.each(data, function(id, value) {
                if(currentCity == id){
                    selected = 'selected';
                }else{
                    selected = '';
                }
                $city.append('<option value="'+id+'" '+selected+' >'+value+'</option>');
            });
            $('#city').show(150);
        }
    });
  }
</script>

@endsection
