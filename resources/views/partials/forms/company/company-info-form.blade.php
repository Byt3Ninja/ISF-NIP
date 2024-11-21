<div class="card">
  <div class="card-body">
    <div class="border-bottom border-light mb-6">
      <h5 class="card-title">{{ $tab['label'] }}</h5>
    </div>
    <form method="post" action="{{ route('update_my_company') }}"
      enctype="multipart/form-data">
      {!! csrf_field() !!}
      <input type="hidden" value="1" name="CompanyN">
      <div class="row">
        <div class="col-md-6 col-12 mb-3">
          <label for="name_en"
            class="form-label">{{ __('admin.English Name') }}
            *</label>
          <div class="position-relative">
            <input type="text"
              class="form-control border-light light-shadow px-10" id="name_en"
              name="name_en" placeholder="{{ __('admin.English Name') }} *"
              value="{{ $client->company ? $client->company->name_en : old('name_en') }}"
              required autocomplete="name_en" style="direction: ltr;">
            <i class="ti ti-building-skyscraper position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>
        <div class="col-md-6 col-12 mb-3">
          <label for="name_ar" class="form-label">{{ __('admin.Arabic Name') }}
            *</label>
          <div class="position-relative">
            <input type="text"
              class="form-control border-light light-shadow px-10" id="name_ar"
              name="name_ar" placeholder="{{ __('admin.Arabic Name') }} *"
              value="{{ $client->company ? $client->company->name_ar : old('name_ar') }}"
              required autocomplete="name_ar" style="direction: rtl;">
            <i class="ti ti-building-skyscraper position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>
        @php
          $stateID = $client->company
              ? optional($client->company)->state_id
              : 0;
        @endphp
        <div class="col-md-4 col-12 mb-3">
          <label for="country_id"
            class="form-label">{{ __('admin.Country') }}</label>
          <div class="position-relative">
            <select class="form-select border-light light-shadow px-10"
              id="country_id" name="country_id" required>
              <option value="">{{ __('admin.Country') }}</option>
              @foreach ($countries as $country)
                <option value="{{ $country->id }}"
                  {{ $country->id == $client->country_id ? 'selected' : '' }}>
                  {{ $country->{'name_' . $l} }}</option>
              @endforeach
            </select>
            <i class="ti ti-map-2 fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>
        <div class="col-md-4 col-12 mb-3">
          <label for="state_id" class="form-label">{{ __('admin.State') }}
            *</label>
          <div class="position-relative">
            <select class="form-select border-light light-shadow px-10"
              id="state_id" name="state_id" required>
              <option value="">{{ __('admin.State') }}</option>
              @foreach ($states as $state)
                <option value="{{ $state->id }}"
                  {{ $state->id == $stateID ? 'selected' : '' }}>
                  {{ $state->$name }}</option>
              @endforeach
            </select>
            <i class="ti ti-map-pin fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>
        <div class="col-md-4 col-12 mb-3">
          <label for="city_id" class="form-label">{{ __('admin.City') }}
            *</label>
          <div class="position-relative">
            <select class="form-select border-light light-shadow px-10"
              id="city_id" name="city_id" required>
              <option value="">{{ __('admin.City') }}</option>
              @if (isset($client->company) && $client->company->city_id != null)
                <option value="{{ $client->company->city_id }}" selected>
                  {{ $client->company->city->{$name} }}</option>
              @endif
            </select>
            <i class="ti ti-current-location fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>
        <div class="col-md-12 col-12 mb-3">
          <label for="sector" class="form-label">{{ __('admin.Sector') }}
            *</label>
          <div class="position-relative">
            <select id="selectsector"
              class="form-select border-light light-shadow px-10" name="sector"
              required
              onchange="ssector(this.options[this.selectedIndex].value)">
              @foreach ($sectors as $sector_group)
                <optgroup label="{{ $sector_group->name }}">
                  @foreach ($sector_group->children as $sector)
                    <option value="{{ $sector->id }}" {{ $sector->id == $client->company->sector ? 'Selected' : ''}}>{{ $sector->name }}
                    </option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
            <i class="ti ti-building-factory-2 fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>
        <div class="col-md-12 col-12 mb-3">
          <label for="address_en"
            class="form-label">{{ __('admin.English Address') }} *</label>
          <div class="position-relative">
            <textarea class="form-control border-light light-shadow px-10" id="address_en"
              name="address_en" required placeholder="{{ __('admin.English Address') }} *"
              style="direction: ltr;">{{ $client->company ? $client->company->address_en : old('address_en') }}</textarea>
            <i class="ti ti-map-pins fs-5 position-absolute me-3 text-primary"
              style="left: 7px; top: 10px;"></i>
          </div>
        </div>

        <div class="col-md-12 col-12 mb-3">
          <label for="address_ar"
            class="form-label">{{ __('admin.Arabic Address') }} *</label>
          <div class="position-relative">
            <textarea class="form-control border-light light-shadow px-10" id="address_ar"
              name="address_ar" required placeholder="{{ __('admin.Arabic Address') }} *"
              style="direction: rtl;">{{ $client->company ? $client->company->address_ar : old('address_ar') }}</textarea>
            <i class="ti ti-map-pins fs-5 position-absolute me-3 text-primary"
              style="left: 7px; top: 10px;"></i>
          </div>
        </div>

        <div class="col-md-12 col-12 mb-3">
          <label for="est_date" class="form-label">{{ __('admin.est_date') }}</label>
          <!-- Date Picker-->
          <div class="position-relative">
            <input 
              type="text" 
              name="est_date"
              value="{{ $client->company ? $client->company->est_date : old('est_date') }}"
              class="form-control border-light light-shadow px-10"
              placeholder="YYYY-MM-DD" 
              id="flatpickr-date" 
            />
            <i class="ti ti-calendar-time fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
          <!-- /Date Picker -->
        </div>

        <div class="col-md-12 col-12 mb-3">
          <label for="CreatedTax" class="form-label">{{ __('admin.Have_Rgister_Company') }}
            *</label>
          <div class="position-relative">
            <select id="CreatedTax"
              class="form-select border-light light-shadow px-10 CreatedTax" name="CreatedTax" required >
              
                    <option value="0" {{ $client->company && $client->company->CreatedTax == '0' ? 'selected' : '' }}>{{ __('admin.No') }}</option>
                    <option value="1" {{ $client->company && $client->company->CreatedTax == '1' ? 'selected' : '' }}>{{ __('admin.Yes') }}</option>
                  
            </select>
            <i class="ti ti-building-factory-2 fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>

      <div class="row ShowCreatedTax" style="{{ $client->company && $client->company->CreatedTax == '1' ? '' : 'display: none;' }}" > 

        <div class="col-md-4 col-12 mb-3">
          <label for="Country_Register"
            class="form-label">{{ __('admin.Country_Register') }}</label>
          <div class="position-relative">
            <select class="form-select border-light light-shadow px-10 Country_Register" 
              id="Country_Register" name="Country_Register">
              <option value="">{{ __('admin.Country') }}</option>
              @foreach ($countries as $country)
                <option value="{{ $country->id }}"
                  {{ $client->company && $country->id == $client->company->Country_Register_id ? 'selected' : '' }}>
                  {{ $country->{'name_' . $l} }}</option>
              @endforeach
            </select>
            <i class="ti ti-map-2 fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>

<div class="col-md-4 col-12 mb-3">
          <label for="Commercial_Register" class="form-label">{{ __('admin.Commercial_Register') }}
            *</label>
          <div class="position-relative">
            <input type="text"
              class="form-control border-light light-shadow px-10 Commercial_Register" id="Commercial_Register"
              name="Commercial_Register" placeholder="{{ __('admin.Commercial_Register') }} *"
              value="{{ $client->company ? $client->company->Commercial_Register : old('Commercial_Register') }}"
               autocomplete="Commercial_Register" style="direction: rtl;">
            <i class="ti ti-building-skyscraper position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>

        <div class="col-md-4 col-12 mb-3">
          <label for="Tax_Card" class="form-label">{{ __('admin.Tax_Card') }}
            *</label>
          <div class="position-relative">
            <input type="text"
              class="form-control border-light light-shadow px-10 Tax_Card" id="Tax_Card"
              name="Tax_Card" placeholder="{{ __('admin.Tax_Card') }} *"
              value="{{ $client->company ? $client->company->Tax_Card : old('Tax_Card') }}"
               autocomplete="Tax_Card" style="direction: rtl;">
            <i class="ti ti-building-skyscraper position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>
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
</div>
