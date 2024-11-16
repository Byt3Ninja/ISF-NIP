<div class="card">
    <div class="card-body">
      <div class="border-bottom border-light mb-6">
        <h5 class="card-title">{{ $tab['label'] }}</h5>
      </div>
      <form method="post" action="{{ route('update_my_company') }}"
        enctype="multipart/form-data">
        {!! csrf_field() !!}
        <input type="hidden" value="13" name="CompanyN">
        <div class="row">
            @php
$models = ['Marketplace_model', 'On_demand_model', 'Disintermediation_model', 'Subscription_model', 'Freemium_model', 'Virtual_good_model', 'Reseller_model', 'Other'];
@endphp
            <div class="col-md-6 col-12 mb-6">
                <label for="business_model"
                  class="form-label">{{ __('admin.business_model') }}*</label>
                <div class="position-relative">
                    <select class="form-select border-light light-shadow px-10 business_model" 
                    id="business_model" name="business_model">
                    <option value="" disabled>{{ __('admin.business_model') }}</option>
                   
                    @foreach($models as $model)
                    <option value="{{ $model }}" {{ $client->company && $model == $client->company->business_model ? 'selected' : '' }}>
                        {{ __("admin.$model") }}
                    </option>
                @endforeach
                  </select>
                  
                </div>
              </div>

       
              <div class="col-md-6 col-12 mb-6 IfOther" @if($client->company->business_model != 'Other')  style="display: none;" @endif >
                <label for="other_business_model"
                  class="form-label">{{ __('admin.other_business_model') }}*</label>
                <div class="position-relative">
                  <input type="text"
                    class="form-control border-light light-shadow px-10 other_business_model"
                    id="other_business_model" placeholder="{{ __('admin.other_business_model') }}*"
                    value="{{ $client->company ? $client->company->other_business_model : old('other_business_model') }}"
                    name="other_business_model" autocomplete="other_business_model">
                  <i class="ti ti-chart-bar fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
                    style="left: 7px;"></i>
                </div>
              </div>
           

          <div class="col-md-6 col-12 mb-6">
            <label for="business_model_description"
              class="form-label">{{ __('admin.business_model_description') }}*</label>
            <div class="position-relative">
                <textarea class="form-control border-light light-shadow px-10" 
                id="business_model_description" 
                placeholder="{{ __('admin.business_model_description') }}*"
                name="business_model_description" 
                required 
                autocomplete="business_model_description">{{ $client->company ? $client->company->business_model_description : old('business_model_description') }}</textarea>
              <i class="ti ti-flag fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
                style="left: 7px;"></i>
            </div>
          </div>

          <div class="col-md-6 col-12 mb-6">
            <label for="business_plan" class="form-label">{{ __('admin.business_plan') }}
              *</label>
            <div class="position-relative">
                <div class="mt-3" style="display: grid; place-items: center;">
                    <input id="business_plan" readonly
                      class="form-control border-light light-shadow px-8 "
                      type="text" name="business_plan" value="">
                    <a data-input="business_plan" data-preview="business_plan"
                      href="#"
                      class="lfm btn btn-outline-primary text-black fw-bold rounded-pill">
                      <i
                        class="ti ti-plus me-3"></i>{{ __('admin.Add new document') }}
                    </a>
                  </div>
            </div>
          </div>


        </div>



        <div class="row">
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
  
  <!-- Get countries and phone codes -->
  <script>
    fetch('https://restcountries.com/v3.1/all')
      .then(response => response.json())
      .then(data => {
        const select = document.getElementById('country_code');
        data.forEach(country => {
          const option = document.createElement('option');
          const countryCode =
            `${country.idd.root}${country.idd.suffixes ? country.idd.suffixes[0] : ''}`;
          option.value = countryCode;
          option.text = `${countryCode} (${country.cca2})`;
          option.style.fontSize = '12px';
          if (country.cca2 === 'EG') {
            option.selected = true;
          }
          select.appendChild(option);
        });
      });


      


  </script>
  