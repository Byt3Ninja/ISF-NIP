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
        <div class="col-md-6 mb-3">
          <label for="country_id"
            class="form-label">{{ __('admin.Country') }}</label>
          <div class="position-relative">
            <select class="form-select border-light light-shadow px-10"
              id="country_id" name="country_id" required>
              <option value="">{{ __('admin.Country') }}</option>
              @foreach ($countries as $country)
                <option value="{{ $country->id }}"
                  {{ $country->id == $client->country_id ? 'selected' : '' }}>
                  {{ $country->emoji . ' ' . $country->{'name_' . $l} }}</option>
              @endforeach
            </select>
            <i class="ti ti-map-2 fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>
        <div class="col-md-6 mb-6">
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
        
        <div class="col-md-12 col-12 mb-6">
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
                    <option value="{{ $sector->id }}" {{ optional($client->company)->sector == $sector->id ? 'Selected' : ''}}>{{ $sector->name }}
                    </option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
            <i class="ti ti-building-factory-2 fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>
        <div class="mb-3">
          <label for="categories" class="form-label">{{ __('admin.Categories') }} *</label>
          <div class="row">
              @php
                  $categories = [
                      'Food & Beverage',
                      'Technology',
                      'App / Website',
                      'Housewares / Home Design',
                      'Health / Wellness / Nutrition',
                      'Fitness',
                      'Sports',
                      'Beauty',
                      'Clothing / Fashion',
                      'Toys / Games',
                      'Children',
                      'Entertainment / Experiential',
                      'Pets',
                      'Music',
                      'Holiday',
                      'Other',
                  ];
                  
                  $selectedCategories = old('business_categories', $client->company->business_categories ?? []);
              @endphp
              
              @foreach ($categories as $category)
              <div class="col-md-4 col-6">
                  <div class="form-check">
                      <input 
                          class="form-check-input" 
                          type="checkbox" 
                          name="business_categories[]" 
                          id="category_{{ $loop->index }}" 
                          value="{{ $category }}"
                          {{ in_array($category, $selectedCategories) ? 'checked' : '' }}
                          @if ($category === 'Other') onclick="toggleOtherCategoryInput(this)" @endif>
                      <label class="form-check-label" for="category_{{ $loop->index }}">
                          {{ $category }}
                      </label>
                  </div>
              </div>
          @endforeach
          </div>
       </div>

       <div class="mb-3">
    
     <div id="otherCategoryInput" class="mt-3" 
      style="display: {{ !is_array($client->company->business_categories ?? []) || in_array('Other', old('business_categories', $client->company->business_categories ?? [])) ? 'block' : 'none' }};">
    <label for="other_category" class="form-label">{{ __('admin.other? Please Specify') }}</label>
    <input 
        type="text" 
        class="form-control" 
        id="other_category" 
        name="other_category" 
        value="{{ is_array($client->company->business_categories ?? []) ? '' : old('other_category', $client->company->business_categories) }}" 
        placeholder="{{ __('admin.other? Please Specify') }}">
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

        <div class="col-6 mb-3">
          <label for="years_of_operations" class="form-label">{{ __('admin.Years of Operations') }} *</label>
          <div class="position-relative">
              <select class="form-select border-light light-shadow px-10" id="years_of_operations" name="years_of_operations" required>
                  <option value="">{{ __('admin.Select Years of Operations') }}</option>
                  <option value="0-2" {{ optional($client->company)->years_of_operations === '0-2' ? 'selected' : '' }}>0-2</option>
                  <option value="2-5" {{ optional($client->company)->years_of_operations === '2-5' ? 'selected' : '' }}>2-5</option>
                  <option value="5-10" {{ optional($client->company)->years_of_operations === '5-10' ? 'selected' : '' }}>5-10</option>
                  <option value="10+" {{ optional($client->company)->years_of_operations === '10+' ? 'selected' : '' }}>10+</option>
              </select>
              <i class="ti ti-calendar-time fs-5 position-absolute top-50 translate-middle-y me-3 text-primary" style="left: 7px;"></i>
          </div>
        </div>

        <div class="col-md-6 col-12 mb-3">
          <label for="product_or_service" class="form-label">{{ __('admin.Product or Service') }} *</label>
          <div class="position-relative">
              <select class="form-select border-light light-shadow px-10" id="product_or_service" name="product_or_service" required>
                  <option value="">{{ __('admin.Select Product or Service') }}</option>
                  <option value="Product" {{ optional($client->company)->product_or_service === 'Product' ? 'selected' : '' }}>
                      {{ __('admin.Product') }}
                  </option>
                  <option value="Service" {{ optional($client->company)->product_or_service === 'Service' ? 'selected' : '' }}>
                      {{ __('admin.Service') }}
                  </option>
                  <option value="Product + Service" {{ optional($client->company)->product_or_service === 'Product + Service' ? 'selected' : '' }}>
                      {{ __('admin.Product + Service') }}
                  </option>
              </select>
              <i class="ti ti-box fs-5 position-absolute top-50 translate-middle-y me-3 text-primary" style="left: 7px;"></i>
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
                  {{ $country->emoji . ' ' . $country->{'name_' . $l} }}</option>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.category-checkbox');
    const otherCheckbox = document.querySelector('input[value="Other"]');
    const otherCategoryInput = document.getElementById('otherCategoryInput');
    const otherCategoryTextbox = document.getElementById('other_category');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            toggleOtherCategoryInput(this);
        });
    });

    if (otherCategoryTextbox.value.trim() !== '' && !Array.isArray(JSON.parse(otherCategoryTextbox.value))) {
        otherCategoryInput.style.display = 'block';
    }
});


function toggleOtherCategoryInput(checkbox) {
    const otherInput = document.getElementById('otherCategoryInput');
    if (checkbox.value === 'Other' && checkbox.checked) {
        otherInput.style.display = 'block';
    } else if (checkbox.value === 'Other' && !checkbox.checked) {
        otherInput.style.display = 'none';
        document.getElementById('other_category').value = '';
    }
}
</script>
