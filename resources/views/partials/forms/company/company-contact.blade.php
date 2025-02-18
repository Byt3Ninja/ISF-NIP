<div class="card">
  <div class="card-body">
    <div class="border-bottom border-light mb-6">
      <h5 class="card-title fw-semibold">{{ $tab['label'] }}</h5>
    </div>
    @if ($_user->hasAnyRole('Admin|organizationManager'))
      <form method="post"
        action="{{ route('update_my_company', $client->company->id) }}"
        enctype="multipart/form-data">
      @else
        <form id="contact-form" method="post"
          action="{{ route('update_my_company') }}"
          enctype="multipart/form-data">
    @endif
    {!! csrf_field() !!}
    <input name="CompanyN" type="hidden" value="2">
    <div class="row">
      <div class="col-md-6 col-12 mb-6">
        <div class="d-grid">
          <div class="d-grid gap-2">
            <label class="form-label fs-6 fw-bold" for="phone">{{ __('auth.Phone') }}</label>
              <input type="hidden" name="country_code" id="country_code" value="{{ optional($client->company)->country_code ? optional($client->company)->country_code : old('country_code') }}">
              <input 
                class="form-control phone-input" id="phone"
                name="phone" type="tel"
                value="{{ trim($client->company ? $client->company->phone : old('phone')) }}"
                placeholder="{{ __('auth.Phone') }}" autocomplete="phone"
                oninput="this.value = this.value.trim()"
              />
            <span class="text-danger" id="phone-error"
              style="display: none;"></span>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-12 mb-6">
        <div class="d-grid">
          <div class="d-grid gap-2">
            <label class="form-label fs-6 fw-bold"
              for="WhatsApp">{{ __('admin.WhatsApp') }}</label>
                <input type="hidden" name="country_code_w_a" id="country_code_w_a" value="{{ $client->company ? ( $client->company->country_code_w_a ? $client->company->country_code_w_a : old('country_code_w_a') ) : old('country_code_w_a') }}">
                <input class="form-control phone-input" id="WhatsApp"
                  name="WhatsApp" type="tel"
                  value="{{ $client->company ? $client->company->whatsapp : old('WhatsApp') }}"
                  placeholder="{{ __('admin.WhatsApp') }}" autocomplete="WhatsApp"
                  oninput="this.value = this.value.trim()"
                   />
            <span class="text-danger" id="whatsapp-error"
              style="display: none;"></span>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-12 mb-6">
        <div class="form-group">
          <label class="form-label fs-6 fw-bold"
          for="email">{{ __('admin.E-mail') }}</label>         
           <div class="input-group input-group-merge">
            <input class="form-control" id="email" name="email"
              type="email"
              value="{{ $client->company ? $client->company->email : old('email') }}"
              style="direction: ltr;" placeholder="{{ __('admin.E-mail') }} *"
              autocomplete="email"
              oninput="this.value = this.value.trimStart()"
              {{-- pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i" --}}
              title="{{ $l == 'en' ? 'Please enter a valid email.' : 'يرجى إدخال بريد إلكتروني صالح.' }}"
              />
            <span class="input-group-text text-light"><i class="ti ti-mail"></i></span>
          </div>
          <div class="invalid-feedback">{{ $l == 'en' ? 'Please enter a valid email.' : 'يرجى إدخال بريد إلكتروني صالح.' }}.</div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary" id="submit-button" type="submit">
          <span id="button-text"> {{ __('admin.Update') }}
            <i class="ti ti-edit ms-2"></i></span>
          <span class="spinner-border spinner-border-sm d-none"
            id="button-spinner" role="status" aria-hidden="true"></span>
        </button>
      </div>
    </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const pickerContainer = document.querySelector('.company-picker-container');
    const fileInput = document.querySelector('#file1');

    pickerContainer.addEventListener('click', () => {
      fileInput.click();
    });

    fileInput.addEventListener('change', (event) => {
      const reader = new FileReader();
      reader.onload = function (e) {
        document.querySelector('#company-logo').src = e.target.result;
      };
      reader.readAsDataURL(event.target.files[0]);
    });
  });
  
  document.addEventListener('DOMContentLoaded', function () {
    const phoneInput = document.querySelector('#phone');
    const whatsappInput = document.querySelector('#WhatsApp');
    const phoneError = document.getElementById('phone-error');
    const whatsappError = document.getElementById('whatsapp-error');
    const countryCodeFromDB = "{{ $client->company ? $client->company->country_code : '' }}";
    const countryCodeWAFromDB = "{{ $client->company ? $client->company->country_code_w_a : '' }}";

    function getCountryShortcut(code) {
      let dialCode = `+${code.toString().trim()}`;
      const countryData = intlTelInputGlobals.getCountryData();
      const country = countryData.find(country => `+${country.dialCode}` === dialCode);

      if (country) {
        console.log("Country Code Found: " + country.iso2);
        return country.iso2;
      }
      return "eg";
    }

    // Initialize intl-tel-input
    const iti = intlTelInput(phoneInput, {
      initialCountry: getCountryShortcut(countryCodeFromDB),
      preferredCountries: ["eg", "ae", "sa", "ma", "dz", "tn"],
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
      formatOnDisplay: false,
      separateDialCode: true,
      autoPlaceholder: "aggressive",
      customPlaceholder: (placeholder) => "e.g. " + placeholder,
    });

    const whatsappIti = intlTelInput(whatsappInput, {
      initialCountry: getCountryShortcut(countryCodeWAFromDB),
      preferredCountries: ["eg", "ae", "sa", "ma", "dz", "tn"],
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
      separateDialCode: true,
      formatOnDisplay: false,
      autoPlaceholder: "aggressive",
      customPlaceholder: (placeholder) => "e.g. " + placeholder,
    });

    // Reset phone input value on select another country code
    phoneInput.addEventListener("countrychange", () => {
      phoneInput.value = '';
    });

    // Access the phone number with code
    phoneInput.addEventListener('input', function() {
      const fullPhoneNumber = iti.getNumber();
      const countryCode = iti.getSelectedCountryData().dialCode;
      const countryCodeInput = document.getElementById('country_code');
      console.log('countryCodeInput', countryCodeInput)
      countryCodeInput.value = countryCode;

      if (!iti.isValidNumber() && fullPhoneNumber.length !== 0) {
        phoneError.style.display = 'block';
        phoneError.innerText = '{{ __('auth.Invalid_Phone_Number') }}';
      } else {
        phoneError.style.display = 'none';
      }
    });

    // Access the WhatsApp number with code
    whatsappInput.addEventListener('input', function() {
      const fullPhoneNumber = whatsappIti.getNumber();
      const countryCode = whatsappIti.getSelectedCountryData().dialCode;
      const countryCodeInput = document.getElementById('country_code_w_a');
      countryCodeInput.value = countryCode;

      if (!whatsappIti.isValidNumber() && fullPhoneNumber.length !== 0) {
        whatsappError.style.display = 'block';
        whatsappError.innerText = '{{ __('auth.Invalid_WhatsApp_Number') }}';
      } else {
        whatsappError.style.display = 'none';
      }
    });

    // Reset WhatsApp input value on select another country code
    whatsappInput.addEventListener("countrychange", () => {
      whatsappInput.value = '';
    });

    const form = document.querySelector('#contact-form');
    const inputs = form.querySelectorAll('#contact-form input[type="url"]');
    const emailInput = form.querySelector('#email');

    emailInput.addEventListener('input', () => {
     const parent = emailInput.closest('.input-group');
     // Check if email match this regexp /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i
    if (emailInput.value === '' || (emailInput.value.match(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i) && !emailInput.value.endsWith('.invalidTLD'))) {
      parent.classList.remove('is-invalid');
     } else {
      parent.classList.add('is-invalid');
    }
      // if (emailInput.checkValidity()) {
      //   parent.classList.remove('is-invalid');
      // } else {
      //   parent.classList.add('is-invalid');
      // }
    });

    inputs.forEach(input => {
      input.addEventListener('input', () => {
        const parent = input.closest('.input-group');
        if (input.checkValidity()) {
          parent.classList.remove('is-invalid');
        } else {
          parent.classList.add('is-invalid');
        }
      });
    });

    form.addEventListener('submit', (e) => {
      let isValid = true;

      const parent = emailInput.closest('.input-group');
      // Check if email match this regexp /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i
      if (emailInput.value === '' || (emailInput.value.match(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i) && !emailInput.value.endsWith('.invalidTLD'))) {
        parent.classList.remove('is-invalid');
      } else {
        parent.classList.add('is-invalid');
        isValid = false;
      }

      inputs.forEach(input => {
        const parent = input.closest('.input-group');
        if (!input.checkValidity()) {
          e.preventDefault();
          parent.classList.add('is-invalid');
          isValid = false;
        }
      });

      // Disable form submission if phoneError or whatsappError is displayed
      if (phoneError.style.display === 'block' || whatsappError.style.display === 'block') {
        isValid = false;
      }

      if (!isValid) {
        e.preventDefault();
      }
    });
  });
</script>