@php
  $org = $organization ?? null;
  $locale = app()->getLocale();
@endphp

<!-- Contact Information Section -->
<div class="mb-4">
  <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
    <i class="ti ti-address-book me-2"></i>{{ __('admin.Contact Information') }}
  </h6>

  <div class="row">
    <!-- Email -->
    <div class="col-md-6 mb-3">
      <label class="form-label fw-semibold" for="contact_email">
        {{ __('admin.Email') }} <span class="text-danger">*</span>
      </label>
      <div class="input-group">
        <span class="input-group-text"><i class="ti ti-mail"></i></span>
        <input
          type="email"
          class="form-control @error('contact_email') is-invalid @enderror"
          id="contact_email"
          name="contact_email"
          value="{{ old('contact_email', $org->contact_email ?? '') }}"
          placeholder="{{ __('admin.Enter email address') }}"
          maxlength="255"
          required
        >
        <div class="invalid-feedback">{{ $errors->first('contact_email') }}</div>
      </div>
    </div>

    <!-- Phone with Country Code (using intl-tel-input) -->
    <div class="col-md-6 mb-3">
      <label class="form-label fw-semibold" for="contact_phone">
        {{ __('admin.Phone') }}
      </label>
      <input
        type="tel"
        class="form-control @error('contact_phone') is-invalid @enderror"
        id="contact_phone"
        name="contact_phone"
        value="{{ old('contact_phone', $org->contact_phone ?? '') }}"
        placeholder="{{ __('admin.Enter phone number') }}"
      >
      <input type="hidden" name="country_code" id="country_code" value="{{ old('country_code', $org->country_code ?? '') }}">
      <small class="text-danger mt-1" id="phone-error" style="display: none;"></small>
      @error('contact_phone')
        <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
      @error('country_code')
        <div class="text-danger mt-1">{{ $message }}</div>
      @enderror
    </div>

    <!-- Country -->
    <div class="col-md-4 mb-3">
      <label class="form-label fw-semibold" for="country_id">
        {{ __('admin.Country') }} <span class="text-danger">*</span>
      </label>
      <select
        class="form-select select2 @error('country_id') is-invalid @enderror"
        id="country_id"
        name="country_id"
        data-placeholder="{{ __('admin.Select Country') }}"
        required
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

    <!-- State -->
    <div class="col-md-4 mb-3">
      <label class="form-label fw-semibold" for="state_id">
        {{ __('admin.State') }} <span class="text-danger">*</span>
      </label>
      <select
        class="form-select select2 @error('state_id') is-invalid @enderror"
        id="state_id"
        name="state_id"
        data-placeholder="{{ __('admin.Select State') }}"
        required
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

    <!-- City -->
    <div class="col-md-4 mb-3">
      <label class="form-label fw-semibold" for="city_id">
        {{ __('admin.City') }} <span class="text-danger">*</span>
      </label>
      <select
        class="form-select select2 @error('city_id') is-invalid @enderror"
        id="city_id"
        name="city_id"
        data-placeholder="{{ __('admin.Select City') }}"
        required
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

    <!-- Address -->
    <div class="col-12 mb-3">
      <label class="form-label fw-semibold" for="address">
        {{ __('admin.Address') }} <span class="text-danger">*</span>
      </label>
      <textarea
        class="form-control @error('address') is-invalid @enderror"
        id="address"
        name="address"
        rows="3"
        placeholder="{{ __('admin.Enter full address') }}"
        maxlength="500"
        required
      >{{ old('address', $org->address ?? '') }}</textarea>
      <div class="d-flex justify-content-between mt-1">
        <div class="invalid-feedback">{{ $errors->first('address') }}</div>
        <small class="text-muted" id="addressCharCount">0 / 500</small>
      </div>
    </div>

    <!-- Google Maps URL -->
    <div class="col-12 mb-3">
      <label class="form-label fw-semibold" for="google_map_url">
        {{ __('admin.Google Maps URL') }} <span class="text-danger">*</span>
      </label>
      <div class="input-group">
        <span class="input-group-text"><i class="ti ti-map-pin"></i></span>
        <input
          type="url"
          class="form-control @error('google_map_url') is-invalid @enderror"
          id="google_map_url"
          name="google_map_url"
          value="{{ old('google_map_url', $org->google_map_url ?? '') }}"
          placeholder="https://maps.google.com/..."
          maxlength="500"
          required
        >
        <div class="invalid-feedback">{{ $errors->first('google_map_url') }}</div>
      </div>
      <small class="text-muted">{{ __('admin.Paste the Google Maps link for your location') }}</small>
    </div>
  </div>
</div>

<!-- Social Media Section -->
<div class="mb-4">
  <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
    <i class="ti ti-brand-facebook me-2"></i>{{ __('admin.Social Media') }}
    <small class="text-muted fw-normal">({{ __('admin.Optional') }})</small>
  </h6>

  <div class="row">
    <!-- LinkedIn -->
    <div class="col-md-6 mb-3">
      <label class="form-label fw-semibold" for="linkedin_company_url">
        {{ __('admin.LinkedIn') }}
      </label>
      <div class="input-group">
        <span class="input-group-text"><i class="ti ti-brand-linkedin"></i></span>
        <input
          type="url"
          class="form-control @error('linkedin_company_url') is-invalid @enderror"
          id="linkedin_company_url"
          name="linkedin_company_url"
          value="{{ old('linkedin_company_url', $org->linkedin_company_url ?? '') }}"
          placeholder="https://linkedin.com/company/..."
          maxlength="255"
        >
        <div class="invalid-feedback">{{ $errors->first('linkedin_company_url') }}</div>
      </div>
    </div>

    <!-- Twitter/X -->
    <div class="col-md-6 mb-3">
      <label class="form-label fw-semibold" for="twitter_handle">
        {{ __('admin.Twitter / X') }}
      </label>
      <div class="input-group">
        <span class="input-group-text"><i class="ti ti-brand-x"></i></span>
        <input
          type="url"
          class="form-control @error('twitter_handle') is-invalid @enderror"
          id="twitter_handle"
          name="twitter_handle"
          value="{{ old('twitter_handle', $org->twitter_handle ?? '') }}"
          placeholder="https://x.com/..."
          maxlength="255"
        >
        <div class="invalid-feedback">{{ $errors->first('twitter_handle') }}</div>
      </div>
    </div>

    <!-- Facebook -->
    <div class="col-md-6 mb-3">
      <label class="form-label fw-semibold" for="facebook_page_url">
        {{ __('admin.Facebook') }}
      </label>
      <div class="input-group">
        <span class="input-group-text"><i class="ti ti-brand-facebook"></i></span>
        <input
          type="url"
          class="form-control @error('facebook_page_url') is-invalid @enderror"
          id="facebook_page_url"
          name="facebook_page_url"
          value="{{ old('facebook_page_url', $org->facebook_page_url ?? '') }}"
          placeholder="https://facebook.com/..."
          maxlength="255"
        >
        <div class="invalid-feedback">{{ $errors->first('facebook_page_url') }}</div>
      </div>
    </div>

    <!-- Instagram -->
    <div class="col-md-6 mb-3">
      <label class="form-label fw-semibold" for="instagram_profile_url">
        {{ __('admin.Instagram') }}
      </label>
      <div class="input-group">
        <span class="input-group-text"><i class="ti ti-brand-instagram"></i></span>
        <input
          type="url"
          class="form-control @error('instagram_profile_url') is-invalid @enderror"
          id="instagram_profile_url"
          name="instagram_profile_url"
          value="{{ old('instagram_profile_url', $org->instagram_profile_url ?? '') }}"
          placeholder="https://instagram.com/..."
          maxlength="255"
        >
        <div class="invalid-feedback">{{ $errors->first('instagram_profile_url') }}</div>
      </div>
    </div>

    <!-- YouTube -->
    <div class="col-md-6 mb-3">
      <label class="form-label fw-semibold" for="youtube_channel_url">
        {{ __('admin.YouTube') }}
      </label>
      <div class="input-group">
        <span class="input-group-text"><i class="ti ti-brand-youtube"></i></span>
        <input
          type="url"
          class="form-control @error('youtube_channel_url') is-invalid @enderror"
          id="youtube_channel_url"
          name="youtube_channel_url"
          value="{{ old('youtube_channel_url', $org->youtube_channel_url ?? '') }}"
          placeholder="https://youtube.com/..."
          maxlength="255"
        >
        <div class="invalid-feedback">{{ $errors->first('youtube_channel_url') }}</div>
      </div>
    </div>

    <!-- TikTok -->
    <div class="col-md-6 mb-3">
      <label class="form-label fw-semibold" for="tiktok_url">
        {{ __('admin.TikTok') }}
      </label>
      <div class="input-group">
        <span class="input-group-text"><i class="ti ti-brand-tiktok"></i></span>
        <input
          type="url"
          class="form-control @error('tiktok_url') is-invalid @enderror"
          id="tiktok_url"
          name="tiktok_url"
          value="{{ old('tiktok_url', $org->tiktok_url ?? '') }}"
          placeholder="https://tiktok.com/..."
          maxlength="255"
        >
        <div class="invalid-feedback">{{ $errors->first('tiktok_url') }}</div>
      </div>
    </div>
  </div>
</div>
