<div class="container mt-5">
  <form method="post" action="{{ route('update_my_company') }}"
    enctype="multipart/form-data">
    {!! csrf_field() !!}
    <input type="hidden" value="1" name="CompanyN">
    <div class="row">
      <div class="col-md-6 col-12 mb-3">
        <label for="name_en" class="form-label">{{ __('admin.English Name') }}
          *</label>
        <input type="text" class="form-control" id="name_en" name="name_en"
          placeholder="{{ __('admin.English Name') }} *"
          value="{{ $client->company ? $client->company->name_en : old('name_en') }}"
          required autocomplete="name_en" style="direction: ltr;">
      </div>
      <div class="col-md-6 col-12 mb-3">
        <label for="name_ar" class="form-label">{{ __('admin.Arabic Name') }}
          *</label>
        <input type="text" class="form-control" id="name_ar" name="name_ar"
          placeholder="{{ __('admin.Arabic Name') }} *"
          value="{{ $client->company ? $client->company->name_ar : old('name_ar') }}"
          required autocomplete="name_ar" style="direction: rtl;">
      </div>
      @php
        $stateID = $client->company ? optional($client->company)->state_id : 0;
      @endphp
      <div class="col-md-4 col-12 mb-3">
        <label for="country_id"
          class="form-label">{{ __('admin.Country') }}</label>
        <select class="form-select" id="country_id" name="country_id" required>
          <option value="">{{ __('admin.Country') }}</option>
          @foreach ($countries as $country)
            <option value="{{ $country->id }}"
              {{ $country->id == $client->country_id ? 'selected' : '' }}>
              {{ $country->{'name_' . $l} }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4 col-12 mb-3">
        <label for="state_id" class="form-label">{{ __('admin.State') }}
          *</label>
        <select class="form-select" id="state_id" name="state_id" required>
          <option value="">{{ __('admin.State') }}</option>
          @foreach ($states as $state)
            <option value="{{ $state->id }}"
              {{ $state->id == $stateID ? 'selected' : '' }}>
              {{ $state->$name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4 col-12 mb-3">
        <label for="city_id" class="form-label">{{ __('admin.City') }}
          *</label>
        <select class="form-select" id="city_id" name="city_id" required>
          <option value="">{{ __('admin.City') }}</option>
          @if (isset($client->company) && $client->company->city_id != null)
            <option value="{{ $client->company->city_id }}" selected>
              {{ $client->company->city->{$name} }}</option>
          @endif
        </select>
      </div>
      <div class="col-md-12 col-12 mb-3">
        <label for="sector" class="form-label">{{ __('admin.Sector') }}
          *</label>
        <select id="selectsector" class="form-select" name="sector" required
          onchange="ssector(this.options[this.selectedIndex].value)">
          @foreach ($sectors as $sector_group)
            <optgroup label="{{ $sector_group->name }}">
              @foreach ($sector_group->children as $sector)
                <option value="{{ $sector->name_en }}">{{ $sector->name }}
                </option>
              @endforeach
            </optgroup>
          @endforeach
        </select>
      </div>
      <div class="col-md-12 col-12 mb-3">
        <label for="address_en"
          class="form-label">{{ __('admin.English Address') }} *</label>
        <textarea class="form-control" id="address_en" name="address_en" required
          placeholder="{{ __('admin.English Address') }} *" style="direction: ltr;">{{ $client->company ? $client->company->address_en : old('address_en') }}</textarea>
      </div>
      <div class="col-md-12 col-12 mb-3">
        <label for="address_ar"
          class="form-label">{{ __('admin.Arabic Address') }} *</label>
        <textarea class="form-control" id="address_ar" name="address_ar" required
          placeholder="{{ __('admin.Arabic Address') }} *" style="direction: rtl;">{{ $client->company ? $client->company->address_ar : old('address_ar') }}</textarea>
      </div>
      <div class="col-md-12 col-12 mb-3">
        <label for="est_date"
          class="form-label">{{ __('admin.est_date') }}</label>
        <input type="text" class="form-control" id="est_date"
          name="est_date"
          value="{{ $client->company ? $client->company->est_date : old('est_date') }}">
      </div>
      <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" id="submit-button">
          <span id="button-text">{{ __('admin.Update') }}</span>
          <span id="button-spinner"
            class="spinner-border spinner-border-sm d-none" role="status"
            aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </form>
</div>
