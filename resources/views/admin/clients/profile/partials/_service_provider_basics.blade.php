<!-- Step 1 Basics -->
@php
    $org = $client->service_provider;
    $locale = app()->getLocale();
    $l = $locale;
@endphp
<div class="row" id="step-1-basics">

  <!-- Company Logo -->
  <div class="col-12 mb-4 fv-logo-wrapper">
    <div class="d-flex align-items-start align-items-sm-center gap-6">
      <div class="position-relative w-px-100 h-px-100">

        <!-- Image preview -->
        <img
          id="logo-preview-image"
          class="w-100 h-100 rounded border shadow-sm {{ $org && $org->img_url ? '' : 'd-none' }}"
          alt="logo"
          src="{{ $org && $org->img_url ? '/191014/' . $org->img_url : '' }}"
          onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none')"
        />

        <!-- Placeholder -->
        <div
          id="logo-preview-placeholder"
          class="bg-light-subtle w-100 h-100 d-flex justify-content-center align-items-center rounded border-dashed border-light {{ $org && $org->img_url ? 'd-none' : '' }}"
        >
          <div class="d-flex flex-column gap-1 justify-content-center align-items-center">
            <i class="ti ti-cloud-upload text-muted fs-4"></i>
            <small class="text-muted">{{ __('admin.upload_logo') }}</small>
          </div>
        </div>
      </div>

      <div class="button-wrapper">
        <div class="d-flex align-items-start align-items-center">
          <label for="upload-company-logo" class="btn btn-primary text-nowrap me-3 mb-3" tabindex="0">
            <span class="d-none d-sm-block">{{ __('admin.upload_logo') }}</span>
            <i class="ti ti-cloud-upload d-block d-sm-none"></i>
            <input
              hidden
              type="file"
              name="company_logo"
              class="logo-file-input"
              id="upload-company-logo"
              accept="image/png, image/jpg, image/jpeg, image/webp, image/svg+xml"
            />
          </label>
          <div class="reset-company-logo" style="{{ $org && $org->img_url ? '' : 'display: none;' }}">
            <button type="button" class="btn btn-label-secondary reset-company-logo-btn mb-4">
              <i class="ti ti-refresh-dot d-block d-sm-none"></i>
              <span class="d-none d-sm-block">{{ __('admin.Reset') }}</span>
            </button>
          </div>
        </div>
        <small>
          PNG, JPG, JPEG, SVG, WEBP | {{ __('admin.Max size') }}: 5MB
        </small>
      </div>
    </div>
  </div>

  <!-- Company Name -->
  <div class="col-md-6 mb-4">
    <label class="form-label">{{ __('auth.companyName') }} *</label>
    <input type="text" class="form-control" name="company_name" value="{{ old('company_name', $org->company_name ?? '') }}" maxlength="50" placeholder="{{ __('auth.enterCompanyName') }}" />
  </div>

  <!-- Contact Name -->
  <div class="col-md-6 mb-4">
    <label class="form-label">{{ __('auth.contactName') }} *</label>
    <input type="text" class="form-control" name="contact_name" value="{{ old('contact_name', $org->contact_name ?? '') }}" maxlength="50" placeholder="{{ __('auth.enterContactName') }}" />
  </div>

  <!-- Email -->
  <div class="col-md-6 mb-4">
    <label class="form-label">{{ __('admin.Email') }} *</label>
    <input type="email" class="form-control" name="email" value="{{ old('email', $org->email ?? '') }}" maxlength="50" placeholder="{{ __('admin.Enter email address') }}"/>
  </div>

  <!-- Phone with Country Code (using intl-tel-input) -->
  <div class="col-md-6 mb-4 d-flex flex-column">
    <label class="form-label" for="phone">{{ __('admin.Phone') }} *</label>
    <input
      type="tel"
      class="form-control intl-tel-input w-100"
      id="phone"
      name="phone"
      value="{{ old('phone', $org->phone ?? '') }}"
      placeholder="{{ __('admin.Phone') }}"
    />
    <input type="hidden" name="country_code" id="country_code" value="{{ old('country_code', $org->country_code ?? '') }}">
  </div>

  <!-- WhatsApp with Country Code (using intl-tel-input) -->
  <div class="col-md-6 mb-4 d-flex flex-column">
    <label class="form-label" for="whatsapp">{{ __('admin.Whatsapp') }}</label>
    <input
      type="tel"
      class="form-control intl-tel-input w-100"
      id="whatsapp"
      name="whatsapp"
      value="{{ old('whatsapp', $org->whatsapp ?? '') }}"
      placeholder="{{ __('admin.Whatsapp') }}"
    />
    <input type="hidden" name="country_code_w_a" id="country_code_w_a" value="{{ old('country_code_w_a', $org->country_code_w_a ?? '') }}">
  </div>

  <!-- Preferred Language -->
  <div class="col-md-6 mb-4">
    <label class="form-label">{{ __('admin.preferred_language') }}</label>
    <select class="form-select select2" name="language" data-placeholder="{{ __('admin.Select Language') }}">
      <option value=""></option>
      <option value="en" {{ old('language', $org->preferred_language ?? '') == 'en' ? 'selected' : '' }}>{{ __('admin.English') }}</option>
      <option value="ar" {{ old('language', $org->preferred_language ?? '') == 'ar' ? 'selected' : '' }}>{{ __('admin.Arabic') }}</option>
    </select>
  </div>

  <!-- Country -->
  <div class="col-md-6 mb-4">
    <label class="form-label" for="country_id">{{ __('admin.Country') }} *</label>
    <select 
      class="form-select select2" 
      name="country" 
      id="country_id" 
      data-placeholder="{{ __('admin.Select Country') }}"
      data-allow-clear="true"
    >
      <option value=""></option>
      @foreach($countries as $country)
        <option
          value="{{ $country->id }}"
          {{ old('country_id', $org->country_id ?? '') == $country->id ? 'selected' : '' }}
        >
          {{ $l == 'ar' ? $country->name_ar : $country->name_en }}
        </option>
      @endforeach
    </select>
  </div>

  <!-- State -->
  <div class="col-md-6 mb-4">
    <label class="form-label" for="state_id">{{ __('admin.State') }} *</label>
    <select 
      class="form-select select2" 
      name="state" 
      id="state_id" 
      data-placeholder="{{ __('admin.Select State') }}"
      data-allow-clear="true"
    >
      <option value=""></option>
      @foreach($states ?? [] as $state)
        <option
          value="{{ $state->id }}"
          {{ old('state_id', $org->state_id ?? '') == $state->id ? 'selected' : '' }}
        >
          {{ $l == 'ar' ? $state->name_ar : $state->name_en }}
        </option>
      @endforeach
    </select>
  </div>

   <!-- City -->
  <div class="col-md-6 mb-4">
    <div class="form-group">
      <label class="form-label" for="city_id">{{ __('admin.District') }}</label>
      <select
        class="form-select select2"
        id="city_id"
        name="city"
        data-placeholder="{{ __('admin.Select District') }}"
        data-allow-clear="true"
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
    </div>
  </div>

  <!-- Address -->
  <div class="col-md-6 mb-4">
    <label class="form-label">{{ __('admin.Address') }}</label>
    <input type="text" class="form-control" name="address" value="{{ old('address', $org->address ?? '') }}" maxlength="50" placeholder="{{ __('admin.Enter full address') }}"/>
  </div>

  <!-- Zip -->
  <div class="col-md-6 mb-4">
    <label class="form-label">{{ __('admin.Zip_Code') }}</label>
    <input type="text" class="form-control" name="zip" value="{{ old('zip', $org->zip_code ?? '') }}" maxlength="9" placeholder="{{ __('admin.Enter zip code') }}" />
  </div>

  <!-- About -->
  <div class="col-12 mb-4">
    <label class="form-label">{{ __('admin.about_or_description') }} *</label>
    <textarea class="form-control" name="about" maxlength="500" rows="4" placeholder="{{ __('admin.about_placeholder') }}">{{ old('about', $org->description ?? '') }}</textarea>
  </div>

</div>

@push('scripts')
<script>
  $(function() {
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
          placeholder: '{{ __("admin.Select District") }}',
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
          .fail(function(xhr, status, error) {
            $('#state_id').html('<option value=""></option>').select2('destroy').select2({
              placeholder: '{{ __("admin.Select Country") }}',
              allowClear: true
            });
            $('#state_id').find('.select2-selection').html('<span class="select2-selection__rendered select2-selection__placeholder">{{ __("admin.Error loading states") }}</span>');
          });
      });

      $('#state_id').on('change', function() {
        const stateId = $(this).val();
        const $city = $('#city_id');

        $city.html('<option value=""></option>').select2('destroy').select2({
          placeholder: '{{ __("admin.Select District") }}',
          allowClear: true
        });

        if (!stateId) return;

        $.get('{{ route("cities.index") }}', { state_id: stateId })
          .done(data => {
            let html = '<option value=""></option>';
            $.each(data, (id, name) => html += `<option value="${id}">${name}</option>`);
            $city.html(html).select2('destroy').select2({
              placeholder: '{{ __("admin.Select District") }}',
              allowClear: true
            });
          })
          .fail(function(xhr, status, error) {
            $('#city_id').html('<option value=""></option>').select2('destroy').select2({
              placeholder: '{{ __("admin.Select District") }}',
              allowClear: true
            });
            $('#city_id').find('.select2-selection').html('<span class="select2-selection__rendered select2-selection__placeholder">{{ __("admin.Error loading cities") }}</span>');
          });
      });
    }

    $('#upload-company-logo').on('change', function (e) {
      const file = e.target.files[0];
      const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/svg+xml', 'image/webp'];

      if (!file) return;

      if (!allowedTypes.includes(file.type)) {
        // invalidFeedback.removeClass('d-none').text(messages.fileType[currentLang()]);
        $(this).val('');
        return;
      }
      
      // Validate file size (5MB)
      if (file.size > 5 * 1024 * 1024) {
        // invalidFeedback.removeClass('d-none').text(messages.fileType[currentLang()]);
        $(this).val('');
        return;
      }
      
      const reader = new FileReader();
      reader.onload = function (e) {
        $('#logo-preview-image')
          .attr('src', e.target.result)
          .removeClass('d-none');

        $('#logo-preview-placeholder').addClass('d-none');

        $('.reset-company-logo').show();
      };

      reader.readAsDataURL(file);
    });

    $('.reset-company-logo-btn').on('click', function () {
      $('#upload-company-logo').val('');

      $('#logo-preview-image')
        .attr('src', '')
        .addClass('d-none');

      $('#logo-preview-placeholder').removeClass('d-none');

      $(this).closest('.reset-company-logo').hide();
    });


    $('#logo-preview-image, #logo-preview-placeholder').on('click', function () {
      $('#upload-company-logo').trigger('click');
    });

    initLocationCascade();
  });
</script>
@endpush
