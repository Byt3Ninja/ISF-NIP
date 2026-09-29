<div class="card">
  <div class="card-body">
    
    @if (Auth::user()->hasanyRole('Admin|organizationManager'))
      <form method="post"
        id="contact-form" 
        action="{{ route('company.profile_update_mine', $client->company->id) }}"
        enctype="multipart/form-data">
      @else
        <form id="contact-form" method="post"
          action="{{ route('company.profile_update_mine') }}"
          enctype="multipart/form-data">
    @endif
    {!! csrf_field() !!}
    <input name="CompanyN" type="hidden" value="{{ \App\Enums\CompanyFormStep::ContactInfo->value }}">
    <div class="row">
      <div class="border-bottom mb-4">
        <h5 class="card-title">{{ __('admin.Logo_Social') }} </h5>
      </div>
      <div class="row mb-8">
        <!-- Upload Company Logo -->
        <div class="col-12 col-md-2 justify-content-center d-flex">
          <div class="d-flex flex-column" style="width: fit-content;">
            <div class="w-px-100 text-center">
              <label class="fs-6 form-label fw-semibold mb-1" class="form-label fw-medium">{{ __('admin.Logo') }}</label>
            </div>
            <div id="holder" class="company-picker-container">
              <div class="position-relative w-px-100 h-px-100 mb-3 cursor-pointer">
                
                <img 
                  id="company-logo" 
                  src="{{ $client->company ? asset($client->company->logo) : '/images/logo-placeholder.png' }}" 
                  onerror="this.src = '/images/logo-placeholder.png';"
                  alt="Company logo" 
                  class="img-fluid rounded border w-px-100 h-px-100" 
                />
                <i class="ti ti-camera-plus position-absolute bottom-0 end-0 p-2"></i>
              </div>
              <input 
                type="file" 
                class="form-control mt-2 d-none" 
                id="file1" 
                name="img_url" 
                accept="image/*" 
                value="{{ $client->company ? $client->company->logo : old('img_url') }}"
              />
            </div>

            @if ($client->company && $client->company->logo) 
              <div class="w-px-100 text-center">
                <button
                  type="button" 
                  id="reset-company-logo"
                  {{-- onclick="document.querySelector('#company-logo').src = '/images/logo-placeholder.png'; document.querySelector('#file1').value = '';" --}}
                  class="btn btn-label-secondary btn-sm"
                >
                  {{ $l == 'en' ? 'Reset' : 'إعادة تعيين' }}
                </button>
              </div>
            @endif
          </div>
        </div>
        <!-- /Upload Company Logo -->

        <!-- Social Media Links -->
        <div class="col-md-10">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="linkedin">{{ __('admin.Linkedin') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="linkedin" 
                    name="linkedin" 
                    {{-- placeholder="{{ $l == 'en' ? 'LinkedIn URL' : 'رابط لينكد إن' }}"  --}}
                    placeholder="e.g. https://www.linkedin.com/" 
                    value="{{ $client->company->linkedin ?? old('linkedin') }}" 
                    pattern="https://www.linkedin.com/.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid LinkedIn URL.' : 'يرجى إدخال رابط لينكد إن صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-brand-linkedin"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Invalid LinkedIn URL' : 'رابط لينكد إن غير صالح' }}</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="twitter">X</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="twitter" 
                    name="twitter" 
                    {{-- placeholder="{{ $l == 'en' ? 'Twitter or x URL' : 'رابط تويتر' }}"  --}}
                    placeholder="e.g. https://x.com/" 
                    value="{{ $client->company->twitter ?? old('twitter') }}" 
                    pattern="https://twitter.com/.*|https://x.com/.*|https://X.com/.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid X URL.' : 'يرجى إدخال رابط اكس صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-brand-x"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Invalid X URL' : 'رابط اكس غير صالح' }}.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="facebook">{{ __('admin.Facebook') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="facebook" 
                    name="facebook" 
                    placeholder="e.g. https://www.facebook.com/" 
                    {{-- placeholder="{{ $l == 'en' ? 'Facebook URL' : 'رابط فيسبوك' }}"  --}}
                    value="{{ $client->company->facebook ?? old('facebook') }}" 
                    pattern="https://www.facebook.com/.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid Facebook URL.' : 'يرجى إدخال رابط فيسبوك صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-brand-facebook"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Invalid Facebook URL' : 'رابط فيسبوك غير صالح' }}.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="youtube">{{ __('admin.Youtube') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="youtube" 
                    name="youtube" 
                    {{-- placeholder="{{ $l == 'en' ? 'YouTube URL' : 'رابط يوتيوب' }}"  --}}
                    placeholder="e.g. https://www.youtube.com/" 
                    value="{{ $client->company->youtube ?? old('youtube') }}" 
                    pattern="https://www.youtube.com/.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid YouTube URL.' : 'يرجى إدخال رابط يوتيوب صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-brand-youtube"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Invalid YouTube URL' : 'رابط يوتيوب غير صالح' }}.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="website">{{ __('admin.Website') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="website" 
                    name="website" 
                    {{-- placeholder="{{ $l == 'en' ? 'Website URL' : 'رابط الموقع' }}"  --}}
                    placeholder="e.g. https://website.com"
                    value="{{ $client->company->website ?? old('website') }}" 
                    pattern="https?://.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid website URL.' : 'يرجى إدخال رابط موقع صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-world"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Invalid Website URL' : 'رابط الموقع غير صالح' }}.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="google_map">{{ __('admin.Google Map full URL') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="google_map" 
                    name="google_map" 
                    placeholder="e.g. https://maps.app.goo.gl/" 
                    {{-- placeholder="{{ $l == 'en' ? 'Google Map URL' : 'رابط خريطة جوجل' }}"  --}}
                    value="{{ $client->company->google_map ?? old('google_map') }}" 
                    pattern="https://maps.app.goo.gl/.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid Google Map URL.' : 'يرجى إدخال رابط خريطة جوجل صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-map-pin"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Invalid Google Map URL' : 'رابط خريطة جوجل غير صالح' }}.</div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="instagram">{{ __('admin.Instagram') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="instagram" 
                    name="instagram" 
                    {{-- placeholder="{{ $l == 'en' ? 'Instagram URL' : 'رابط انستجرام' }}"  --}}
                    placeholder="e.g. https://www.instagram.com/" 
                    value="{{ $client->company->instagram ?? old('instagram') }}" 
                    pattern="https://www.instagram.com.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid Instagram URL.' : 'يرجى إدخال رابط انستجرام صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-brand-instagram"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Please enter a valid Instagram URL.' : 'يرجى إدخال رابط انستجرام صالح.' }}.</div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label class="fs-6 form-label fw-semibold mb-1" for="tiktok">{{ __('admin.Tiktok') }}</label>
                <div class="input-group input-group-merge">
                  <input 
                    type="url" 
                    class="form-control" 
                    id="tiktok" 
                    name="tiktok" 
                    {{-- placeholder="{{ $l == 'en' ? 'Tiktok URL' : 'رابط انستجرام' }}"  --}}
                    placeholder="e.g. https://www.tiktok.com" 
                    value="{{ $client->company->tiktok ?? old('tiktok') }}" 
                    pattern="https://www.tiktok.com.*" 
                    title="{{ $l == 'en' ? 'Please enter a valid Tiktok URL.' : 'يرجى إدخال رابط تيك توك صالح.' }}"
                  />
                  <span class="input-group-text text-light"><i class="ti ti-brand-tiktok"></i></span>
                </div>
                <div class="invalid-feedback">{{ $l == 'en' ? 'Please enter a valid Tiktok URL.' : 'يرجى إدخال رابط تيك توك صالح.' }}.</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="border-bottom border-light mb-6">
        <h5 class="card-title fw-semibold">{{ $tab['label'] }}</h5>
      </div>

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
        // console.log("Country Code Found: " + country.iso2);
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

  $('#reset-company-logo').on('click', function () {
  Swal.fire({
    title: "{{__('startup.startup_logo_delete_confirm')}}",
    text: "{{__('admin.Recover')}}",
    icon: "warning",
    reverseButtons: true,
    showCancelButton: true,
    confirmButtonText: "{{ __('admin.DYes') }}",
    cancelButtonText: "{{ __('admin.No') }}",
    customClass: {
      confirmButton: "btn btn-danger",
      cancelButton: "btn btn-label-secondary"
    }
  }).then(function (result) {
    if (result.isConfirmed) {

      $.ajax({
        url: "{{ route('company.profile_images_delete', $client->company->id ?? 0) }}",
        type: "POST",
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
        },
        success: function (data) {
         releaseSuccessAlert({
            title: "{{ __('admin.Deleted') }}",
            message: "{{ __('startup.startup_logo_deleted') }}",
          });

          // Remove image preview or reset UI
          $('#company-logo').attr('src', '/images/logo-placeholder.png');
        },

        error: function (xhr) {
          releaseFailureAlert({
            title: "{{ __('admin.Failed') }}",
            message: xhr?.responseJSON?.message ? xhr?.responseJSON?.message : "{{ __('startup.startup_logo_delete_failed') }}",
          });
        }
      });
    }
  });
});
</script>