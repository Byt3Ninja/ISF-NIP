<form class="form" action= "{{ route('updateone') }}" method="POST" enctype="multipart/form-data">
  {!! csrf_field() !!}
  @method('PUT')
  <input type="hidden" name="id" value="{{ $Title->id }}">
  <div class="col-md-12 col-12">
    <div class="form-group mb-3">
      <label class="form-label" for="site_ar_title">{{ __('settings.ARABICWEBSITETITLE') }}</label>
      <input type="text" class="form-control" value="{{ $Title->ar_title }}" name="site_ar_title">
    </div>
  </div>
  <div class="col-md-12 col-12">
    <div class="form-group mb-3">
      <label class="form-label" for="site_title">{{ __('settings.ENGLISHWEBSITETITLE') }}</label>
      <input type="text" class="form-control" value="{{ $Title->title }}" name="site_title">
    </div>
  </div>

  <div class="col-md-12 col-12">
    <div class="custom-control custom-switch custom-control-inline mb-1 mt-6">
      <span>{{ __('settings.Enable registration') }}</span>&nbsp;
      <label class="form-label" class="custom-control-label mr-1" for="auto">
        <input name="autoact" type="checkbox" class="custom-control-input" id="auto"
          {{ $Title->set_order ? 'checked="checked"' : '' }}>
      </label>
    </div>
  </div>

  <div class="col-md-12 col-12">
    <div class="form-group mb-3">
      <label class="form-label" for="Platform_Name">{{ __('settings.Platform_Name') }}</label>
      <input id="Platform_Name" type="text" class="form-control" value="{{ $Title->Platform_Name }}"
        name="Platform_Name" required>
    </div>
  </div>
  <div class="col-md-12 col-12">
    <div class="form-group mb-3">
      <label class="form-label" for="Platform_support_email">{{ __('settings.Platform_support_email') }}</label>
      <input id="Platform_support_email" type="text" class="form-control"
        value="{{ $Title->Platform_support_email }}" name="Platform_support_email" required>
    </div>
  </div>
  <div class="col-md-12 col-12">
    <div class="form-group mb-3">
      <label class="form-label" for="country_id">{{ __('admin.Country') }}</label>

      <select class="form-control" id="country_id" name='Country'>
        <option value="" disabled>{{ __('admin.Country') }}</option>
        @foreach ($countries as $country)
          <option value="{{ $country->id }}" {{ $country->id == $Title->Country ? 'selected' : '' }}>
            {{ $country->emoji . ' ' . $country->{'name_' . $l} }}</option>
        @endforeach
      </select>

    </div>
  </div>
  <div class="col-md-12 col-12">
    <div class="form-group mb-3">
      <label class="form-label" for="state_id">{{ __('admin.State') }} *</label>
      <select class="form-control" id="state_id" name='State'>
        <option value="" disabled>{{ __('admin.State') }}</option>
        @foreach ($states as $state)
          <option value="{{ $state->id }}" {{ $state->id == $Title->State ? 'selected' : '' }}>
            {{ $state->{'name_' . $l} }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="col-md-12 col-12">
    <div class="form-group mb-3">
      <label class="form-label" for="Default_Language">{{ __('settings.Default_Language') }}
        *</label>
      <select class="form-control" id="Default_Language" name='Default_Language'>
        <option value="">{{ __('settings.Default_Language') }}</option>

        <option value="ar" {{ $Title->Default_Language == 'ar' ? 'selected' : '' }}> العربية
        </option>
        <option value="en" {{ $Title->Default_Language == 'en' ? 'selected' : '' }}> English
        </option>

      </select>
    </div>
  </div>

  {{-- <div class="col-md-12 col-12">
    <div class="form-label-group">
      <label class="form-label" for="timezone">{{ __('settings.timezone') }} *</label>
      <select class="select form-control" id="timezone" name='timezone'>
        <option value="">{{ __('settings.timezone') }}</option>
        @foreach ($timezones as $timezone)
          @php
            // حساب فرق التوقيت للمنطقة الزمنية
            $time = new DateTime('now', new DateTimeZone($timezone));
            $offset = $time->format('P'); // عرض فرق التوقيت مثل +02:00 أو -05:00
          @endphp
          <option value="{{ $timezone }}" {{ $Title->timezone == $timezone ? 'selected' : '' }}>
            {{ $timezone }} ({{ $offset }}) </option>
        @endforeach
      </select>
    </div>
  </div> --}}
  
  <div class="col-md-12 col-12">
    <div class="form-group mb-3">
      <label class="form-label" for="Currency">{{ __('settings.Currency') }} *</label>
      <select class="form-control" id="Currency" name='Currency'>
        <option value="">{{ __('settings.Currency') }}</option>
        @foreach ($currencies->where('enabled', 1) as $currency)
        <option value="{{ $currency->id }}" {{ $currency->id == $Title->Currency ? 'selected' : '' }}>
            {{ $currency->currency_name }}
        </option>
    @endforeach
      </select>
    </div>
  </div>

  <div class="col-md-12 col-12">
    <div class="form-group mb-3">
        <label class="form-label" for="terms_url">{{ __('admin.Terms and Conditions URL') }}</label>
        <input type="url" class="form-control" name="terms_url" value="{{ $Title->terms_url }}">
    </div>
</div>

<div class="col-md-12 col-12">
    <div class="form-group mb-3">
        <label class="form-label" for="privacy_url">{{ __('admin.Privacy Policy URL') }}</label>
        <input type="url" class="form-control" name="privacy_url" value="{{ $Title->privacy_url }}">
    </div>
</div>

  <br>
  <div class="col-md-12 col-12">
    <div class="form-check form-switch mb-2">
      <input class="form-check-input" name="HomePage" type="checkbox" {{ $Title->HomePage == '1' ? 'checked' : '' }} />
      <label class="form-check-label">{{ __('settings.HomeSettings') }}</label>
    </div>
  </div>


  <div class="col-md-12 col-12">
    <div class="form-check form-switch mb-2">
      <input class="form-check-input" name="MentorList" type="checkbox" {{ $Title->mentor_list == '1' ? 'checked' : '' }} />
      <label class="form-check-label">{{ __('settings.MentorList') }}</label>
    </div>
  </div>

  <div class="col-md-12 col-12">
    <div class="form-check form-switch mb-2">
      <input class="form-check-input" name="Assessment" type="checkbox" {{ $Title->assessment == '1' ? 'checked' : '' }} />
      <label class="form-check-label">{{ __('settings.Assessment') }}</label>
    </div>
  </div>

  <div class="col-12 d-flex justify-content-end mt-6">
    <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
  </div>
</form>
