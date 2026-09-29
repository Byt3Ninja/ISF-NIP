@php
  $org = $organization ?? null;
  $locale = app()->getLocale();
@endphp

<!-- Contact Information Section -->
<div class="mb-4 px-8">
  <h6 class="d-flex align-items-center border-bottom pb-2 mb-3">
    <i class="ti ti-address-book me-2"></i>{{ __('admin.Contact Information') }}
  </h6>

  <div class="row">
    <!-- Email -->
    <div class="col-md-6 mb-4">
      <div class="form-group">
        <label class="form-label" for="contact_email">
          {{ __('admin.Email') }}
        </label>
        <div class="input-group input-group-merge">
          <input 
            type="email"
            class="form-control @error('contact_email') is-invalid @enderror"
            id="contact_email"
            name="contact_email"
            value="{{ old('contact_email', $org->contact_email ?? '') }}"
            placeholder="{{ __('admin.Enter email address') }}"
            maxlength="255"
          />
          <span class="input-group-text text-muted"><i class="ti ti-mail"></i></span>
        </div>
        <div class="invalid-feedback">{{ $errors->first('contact_email') }}</div>
      </div>
    </div>
    
    <!-- Phone with Country Code (using intl-tel-input) -->
    <div class="col-md-6 mb-4">
      <div class="form-group">
        <label class="form-label" for="contact_phone">{{ __('admin.Phone') }}</label>
        <input
          type="tel"
          class="form-control @error('contact_phone') is-invalid @enderror"
          id="contact_phone"
          name="contact_phone"
          value="{{ old('contact_phone', $org->contact_phone ?? '') }}"
          placeholder="{{ __('admin.Enter phone number') }}"
        />
        <input type="hidden" name="country_code" id="country_code" value="{{ old('country_code', $org->country_code ?? '') }}">
        <small class="text-danger mt-1" id="phone-error" style="display: none;"></small>
        @error('contact_phone')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
        @error('country_code')
          <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
    </div>
    
    <!-- Country -->
    <div class="col-md-4 mb-4">
      <div class="form-group">
        <label class="form-label" for="country_id">
          {{ __('admin.Country') }} <span class="text-danger">*</span>
        </label>
        <select
          class="form-select select2 @error('country_id') is-invalid @enderror"
          id="country_id"
          name="country_id"
          data-placeholder="{{ __('admin.Select Country') }}"
        >
          <option value=""></option>
          @foreach($countries as $country)
            <option
              value="{{ $country->id }}"
              {{ old('country_id', $org->country_id ?? '') == $country->id ? 'selected' : '' }}
            >
              {{ $locale == 'ar' ? $country->name_ar : $country->name_en }}
            </option>
          @endforeach
        </select>
        <div class="invalid-feedback">{{ $errors->first('country_id') }}</div>
      </div>
    </div>
  
    <!-- State -->
    <div class="col-md-4 mb-4">
      <div class="form-group">
        <label class="form-label" for="state_id">
          {{ __('admin.State/Governorate/Province') }}
        </label>
        <select
          class="form-select select2 @error('state_id') is-invalid @enderror"
          id="state_id"
          name="state_id"
          data-placeholder="{{ __('admin.Select State') }}"
        >
          <option value=""></option>
          @foreach($states ?? [] as $state)
            <option
              value="{{ $state->id }}"
              {{ old('state_id', $org->state_id ?? '') == $state->id ? 'selected' : '' }}
            >
              {{ $locale == 'ar' ? $state->name_ar : $state->name_en }}
            </option>
          @endforeach
        </select>
        <div class="invalid-feedback">{{ $errors->first('state_id') }}</div>
      </div>
    </div>
  
    <!-- City -->
    <div class="col-md-4 mb-4">
      <div class="form-group">
        <label class="form-label" for="city_id">
          {{ __('admin.District') }}
        </label>
        <select
          class="form-select select2 @error('city_id') is-invalid @enderror"
          id="city_id"
          name="city_id"
          data-placeholder="{{ __('admin.Select District') }}"
        >
          <option value=""></option>
          @foreach($cities ?? [] as $city)
            <option
              value="{{ $city->id }}"
              {{ old('city_id', $org->city_id ?? '') == $city->id ? 'selected' : '' }}
            >
              {{ $locale == 'ar' ? $city->name_ar : $city->name_en }}
            </option>
          @endforeach
        </select>
        <div class="invalid-feedback">{{ $errors->first('city_id') }}</div>
      </div>
    </div>
  
    <!-- Address -->
    <div class="col-12 mb-4">
      <div class="form-group">
        <label class="form-label" for="address">
          {{ __('admin.Address') }}
        </label>
        <textarea
          class="form-control @error('address') is-invalid @enderror"
          id="address"
          name="address"
          rows="3"
          placeholder="{{ __('admin.Enter full address') }}"
          maxlength="500"
        >{{ old('address', $org->address ?? '') }}</textarea>
        <div class="invalid-feedback">{{ $errors->first('address') }}</div>
      </div>
    </div>
  
    <!-- Google Maps URL -->
    <div class="col-12 mb-4">
      <div class="form-group">
        <label class="form-label" for="google_map_url">
          {{ __('admin.Google Maps URL') }}
        </label>
        <div class="input-group input-group-merge shadow-none">
          <input
            type="url"
            class="form-control @error('google_map_url') is-invalid @enderror"
            id="google_map_url"
            name="google_map_url"
            value="{{ old('google_map_url', $org->google_map_url ?? '') }}"
            placeholder="https://maps.google.com/..."
            maxlength="500"
          />
          <span class="input-group-text text-muted"><i class="ti ti-map-pin"></i></span>
        </div>
        <div class="invalid-feedback">{{ $errors->first('google_map_url') }}</div>
        <small class="text-muted">{{ __('admin.Paste the Google Maps link for your location') }}</small>
      </div>
    </div>
  </div>
</div>

<!-- Social Media Section -->
<div class="mb-4 px-8">
  <h6 class="d-flex align-items-center gap-2 border-bottom pb-2 mb-3">
    <i class="ti ti-share"></i>{{ __('admin.Social Media') }}
    <small class="text-muted fw-normal">({{ __('admin.Optional') }})</small>
  </h6>

  <div class="row">
    <!-- LinkedIn -->
    <div class="col-md-6 mb-4">
      <div class="form-group">
        <label class="form-label" for="linkedin_company_url">{{ __('admin.LinkedIn') }}</label>
        <div class="input-group input-group-merge shadow-none">
          <input
            type="url"
            class="form-control @error('linkedin_company_url') is-invalid @enderror"
            id="linkedin_company_url"
            name="linkedin_company_url"
            value="{{ old('linkedin_company_url', $org->linkedin_company_url ?? '') }}"
            placeholder="https://linkedin.com/organization/..."
            maxlength="255"
            autocomplete="off"
          />
          <span class="input-group-text text-muted"><i class="ti ti-brand-linkedin"></i></span>
        </div>
        <div class="invalid-feedback">{{ $errors->first('linkedin_company_url') }}</div>
      </div>
    </div>

    <!-- Twitter/X -->
    <div class="col-md-6 mb-4">
      <div class="form-group">
        <label class="form-label" for="twitter_handle">{{ __('admin.Twitter / X') }}</label>
        <div class="input-group input-group-merge shadow-none">
          <input
            type="url"
            class="form-control @error('twitter_handle') is-invalid @enderror"
            id="twitter_handle"
            name="twitter_handle"
            value="{{ old('twitter_handle', $org->twitter_handle ?? '') }}"
            placeholder="https://x.com/..."
            maxlength="255"
            autocomplete="off"
          />
          <span class="input-group-text text-muted"><i class="ti ti-brand-x"></i></span>
        </div>
        <div class="invalid-feedback">{{ $errors->first('twitter_handle') }}</div>
      </div>
    </div>

    <!-- Facebook -->
    <div class="col-md-6 mb-4">
      <div class="form-group">
        <label class="form-label" for="facebook_page_url">{{ __('admin.Facebook') }}</label>
        <div class="input-group input-group-merge shadow-none">
          <input
            type="url"
            class="form-control @error('facebook_page_url') is-invalid @enderror"
            id="facebook_page_url"
            name="facebook_page_url"
            value="{{ old('facebook_page_url', $org->facebook_page_url ?? '') }}"
            placeholder="https://facebook.com/..."
            maxlength="255"
            autocomplete="off"
          />
          <span class="input-group-text text-muted"><i class="ti ti-brand-facebook"></i></span>
        </div>
        <div class="invalid-feedback">{{ $errors->first('facebook_page_url') }}</div>
      </div>
    </div>

    <!-- Instagram -->
    <div class="col-md-6 mb-4">
      <div class="form-group">
        <label class="form-label" for="instagram_profile_url">{{ __('admin.Instagram') }}</label>
        <div class="input-group input-group-merge shadow-none">
          <input
            type="url"
            class="form-control @error('instagram_profile_url') is-invalid @enderror"
            id="instagram_profile_url"
            name="instagram_profile_url"
            value="{{ old('instagram_profile_url', $org->instagram_profile_url ?? '') }}"
            placeholder="https://instagram.com/..."
            maxlength="255"
            autocomplete="off"
          />
          <span class="input-group-text text-muted"><i class="ti ti-brand-instagram"></i></span>
        </div>
        <div class="invalid-feedback">{{ $errors->first('instagram_profile_url') }}</div>
      </div>
    </div>

    <!-- YouTube -->
    <div class="col-md-6 mb-4">
      <div class="form-group">
        <label class="form-label" for="youtube_channel_url">{{ __('admin.YouTube') }}</label>
        <div class="input-group input-group-merge shadow-none">
          <input
            type="url"
            class="form-control @error('youtube_channel_url') is-invalid @enderror"
            id="youtube_channel_url"
            name="youtube_channel_url"
            value="{{ old('youtube_channel_url', $org->youtube_channel_url ?? '') }}"
            placeholder="https://youtube.com/..."
            maxlength="255"
            autocomplete="off"
          />
          <span class="input-group-text text-muted"><i class="ti ti-brand-youtube"></i></span>
        </div>
        <div class="invalid-feedback">{{ $errors->first('youtube_channel_url') }}</div>
      </div>
    </div>

    <!-- TikTok -->
    <div class="col-md-6 mb-4">
      <div class="form-group">
        <label class="form-label" for="tiktok_url">{{ __('admin.TikTok') }}</label>
        <div class="input-group input-group-merge shadow-none">
          <input
            type="url"
            class="form-control @error('tiktok_url') is-invalid @enderror"
            id="tiktok_url"
            name="tiktok_url"
            value="{{ old('tiktok_url', $org->tiktok_url ?? '') }}"
            placeholder="https://tiktok.com/..."
            maxlength="255"
            autocomplete="off"
          />
          <span class="input-group-text text-muted"><i class="ti ti-brand-tiktok"></i></span>
        </div>
        <div class="invalid-feedback">{{ $errors->first('tiktok_url') }}</div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  $(function () {
    window.contactPhoneIti = null;

    // Initialize intl-tel-input for phone number input
    function initPhoneInput() {
      const $phoneInput = $('#contact_phone');
      if (!$phoneInput.length || window.contactPhoneIti) return;

      const dialCodeMap = {
        '20': 'eg', '971': 'ae', '966': 'sa', '212': 'ma', '213': 'dz',
        '216': 'tn', '962': 'jo', '965': 'kw', '968': 'om', '974': 'qa',
        '973': 'bh', '961': 'lb', '963': 'sy', '964': 'iq', '967': 'ye',
        '970': 'ps', '218': 'ly', '249': 'sd', '1': 'us', '44': 'gb'
      };

      let initialCountry = 'eg';
      @if(isset($organization) && $organization->country_code)
        const savedDialCode = '{{ $organization->country_code }}'.replace('+', '');
        initialCountry = dialCodeMap[savedDialCode] || 'eg';
      @endif

      window.contactPhoneIti = intlTelInput($phoneInput[0], {
        initialCountry,
        preferredCountries: ['eg', 'ae', 'sa', 'ma', 'dz', 'tn'],
        utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js',
        separateDialCode: true,
        autoPlaceholder: 'aggressive'
      });

      $phoneInput.on('countrychange', function() {
        $('#country_code').val(window.contactPhoneIti.getSelectedCountryData().dialCode);
      });

      setTimeout(() => {
        const countryData = window.contactPhoneIti.getSelectedCountryData();
        if (countryData.dialCode && !$('#country_code').val()) {
          $('#country_code').val(countryData.dialCode);
        }
      }, 100);
    }

    // Location Cascade (Country -> State -> City)
    function initLocationCascade() {
      $('#country_id').on('change', function() {
        const countryId = $(this).val();
        const $state = $('#state_id');
        const $city = $('#city_id');

        $state.html('<option value=""></option>').select2('destroy').select2({
          placeholder: '{{ __("admin.Select State") }}',
          allowClear: true
        });
        $city.html('<option value=""></option>').select2('destroy').select2({
          placeholder: '{{ __("admin.Select City") }}',
          allowClear: true
        });

        if (!countryId) return;

        $.get('{{ route("utils.locations_list_states_by_country") }}', { country_id: countryId })
          .done(data => {
            let html = '<option value=""></option>';
            $.each(data, (id, name) => html += `<option value="${id}">${name}</option>`);
            $state.html(html).select2('destroy').select2({
              placeholder: '{{ __("admin.Select Country") }}',
              allowClear: true
            });
          })
          .fail(() => utils.showError(config.translations.errorLoadingStates));
      });

      $('#state_id').on('change', function() {
        const stateId = $(this).val();
        const $city = $('#city_id');

        $city.html('<option value=""></option>').select2('destroy').select2({
          placeholder: '{{ __("admin.Select City") }}',
          allowClear: true
        });

        if (!stateId) return;

        $.get('{{ route("cities.index") }}', { state_id: stateId })
          .done(data => {
            let html = '<option value=""></option>';
            $.each(data, (id, name) => html += `<option value="${id}">${name}</option>`);
            $city.html(html).select2('destroy').select2({
              placeholder: '{{ __("admin.Select City") }}',
              allowClear: true
            });
          })
          .fail(() => utils.showError(config.translations.errorLoadingCities));
      });
    }

    initPhoneInput();
    initLocationCascade();
  });
</script>
@endpush