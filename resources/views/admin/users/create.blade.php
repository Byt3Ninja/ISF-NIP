@extends('layouts.admin')

@section('title', __('admin.Add new user'))

@section('index_url', route('users.index'))
@section('subTitle', __('admin.Add new user'))

@section('vendorstyle')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
@endsection

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">

    <div class="card">
      <div class="card-header mb-6 border-bottom">
        <h5 class="card-title mb-0 text-capitalize">{{ __('admin.Add new user') }}</h5>
      </div>
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
            <div class="col-md-6">
              <div class="form-group mb-4">
                <label class="form-label" for="first_name">{{ __('admin.First Name') }}</label>
                <input name="first_name" type="text" class="form-control" id="first_name"
                  placeholder="{{ __('admin.First Name') }}" required
                  autocomplete="first_name" autofocus />
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group mb-4">
                <label class="form-label" for="last_name">{{ __('admin.Last Name') }}</label>
                <input name="last_name" type="text" class="form-control"
                  id="last_name" placeholder="{{ __('admin.Last Name') }}" required autocomplete="last_name" />
              </div>
            </div>
            <div class="col-md-6 col-12 mb-4">
              <label class="form-label" for="gender">{{ __('admin.Gender') }}</label>
              <select class="form-select" id="gender" name="gender" required>
                <option value="">{{ __('admin.Select Gender') }}</option>
                <option value="Male">{{ __('Male') }}
                </option>
                <option value="Female">{{ __('Female') }}</option>
              </select>
            </div>
            <div class="col-md-6 col-12 mb-4">
              <label class="form-label" for="role">{{ __('admin.Role') }}</label>
              <select class="form-select" required id="role" name='role'>
                <option disabled value="" selected>{{ __('admin.Select_Role') }}</option>
                @foreach ($roles as $role)
                <option value="{{ $role['id'] }}">
                  {{ __('admin.' . $role['name']) }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 col-12">
              <div class="form-group mb-4">
                <label class="form-label" for="email">{{ __('admin.Email') }}</label>
                <input name="email" type="email" class="form-control" id="email"
                  placeholder="{{ __('admin.Email') }}" required autocomplete="email">
              </div>
            </div>
            <div class="col-md-6 mb-4 d-grid">
              <label class="form-label text-{{ $l == 'ar' ? 'end' : 'start' }}" for="phone">
                {{ __('auth.Phone') }}
              </label>
              <input id="phone" type="tel" class="form-control text-{{ $l == 'ar' ? 'end' : 'start' }}"
                placeholder="{{ __('auth.Phone') }}" name="phone" required />
              
              <div class="invalid-feedback mt-1" id="phone-error" style="display: none;"></div>
              <input type="hidden" name="country_code" id="phone_code" value="20">
              
              @error('phone')
              <small class="invalid-feedback">{{ $message }}</small>
              @enderror
  
              @error('phone_code')
              <small class="invalid-feedback">{{ $message }}</small>
              @enderror
            </div>
            <div class="col-12 col-md-6 mb-4 form-password-toggle">
              <label class="form-label" for="password">{{ __('admin.Password') }}</label>
              <div class="input-group input-group-merge">
                <input type="password" class="form-control" id="password" placeholder="{{ __('admin.Password') }}"
                  name="password" required autocomplete="new-password">
                <span class="input-group-text cursor-pointer border border-light">
                  <i class="ti ti-eye-off"></i>
                </span>
              </div>
            </div>
            <div class="col-12 col-md-6 mb-4 form-password-toggle">
              <label class="form-label" for="password-confirm">{{ __('admin.Confirm Password') }}</label>
              <div class="input-group input-group-merge">
                <input type="password" class="form-control" id="password-confirm"
                  placeholder="{{ __('admin.Confirm Password') }}" name="password_confirmation" required
                  autocomplete="new-password">
                <span class="input-group-text cursor-pointer border border-light">
                  <i class="ti ti-eye-off"></i>
                </span>
              </div>
            </div>
          </div>
          <div class="col-12 d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary">{{ __('admin.Save') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

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
      }
    });
</script>

@endsection