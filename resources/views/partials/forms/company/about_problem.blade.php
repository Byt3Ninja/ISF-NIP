<div class="card">
    <div class="card-body">
      <div class="border-bottom border-light mb-6">
        <h5 class="card-title">{{ $tab['label'] }}</h5>
      </div>
      <form method="post" action="{{ route('update_my_company') }}"
        enctype="multipart/form-data">
        {!! csrf_field() !!}
        <input type="hidden" value="12" name="CompanyN">
        <div class="row">
            <div class="col-md-6 col-12 mb-6">
                <label for="about_problem"
                  class="form-label">{{ __('admin.about_problem') }}*</label>
                <div class="position-relative">
                  <input type="text"
                    class="form-control border-light light-shadow px-10"
                    id="about_problem" placeholder="{{ __('admin.about_problem') }}*"
                    value="{{ $client->company ? $client->company->about_problem : old('about_problem') }}"
                    name="about_problem" required autocomplete="about_problem">
                  <i class="ti ti-alert-circle fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
                    style="left: 7px;"></i>
                </div>
              </div>

              <div class="col-md-6 col-12 mb-6">
                <label for="about_market_opportunity"
                  class="form-label">{{ __('admin.about_market_opportunity') }}*</label>
                <div class="position-relative">
                  <input type="text"
                    class="form-control border-light light-shadow px-10"
                    id="about_market_opportunity" placeholder="{{ __('admin.about_market_opportunity') }}*"
                    value="{{ $client->company ? $client->company->about_market_opportunity : old('about_market_opportunity') }}"
                    name="about_market_opportunity" required autocomplete="about_market_opportunity">
                  <i class="ti ti-chart-bar fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
                    style="left: 7px;"></i>
                </div>
              </div>

          <div class="col-md-6 col-12 mb-6">
            <label for="about_competitors"
              class="form-label">{{ __('admin.about_competitors') }}*</label>
            <div class="position-relative">
              <input type="text"
                class="form-control border-light light-shadow px-10"
                id="about_competitors" placeholder="{{ __('admin.about_competitors') }}*"
                value="{{ $client->company ? $client->company->about_competitors : old('about_competitors') }}"
                name="about_competitors" required autocomplete="about_competitors">
              <i class="ti ti-flag fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
                style="left: 7px;"></i>
            </div>
          </div>

          <div class="col-md-6 col-12 mb-6">
            <label for="about_monetize" class="form-label">{{ __('admin.about_monetize') }}
              *</label>
            <div class="position-relative">
              <input type="text"
                class="form-control border-light light-shadow px-10"
                id="about_monetize" placeholder="{{ __('admin.about_monetize') }} *"
                value="{{ $client->company ? $client->company->about_monetize : old('about_monetize') }}"
                name="about_monetize" required autocomplete="about_monetize" >
              <i class="ti ti-cash fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
                style="left: 7px;"></i>
            </div>
          </div>

          <div class="col-md-6 col-12 mb-6">
            <label for="about_challenges" class="form-label">{{ __('admin.about_challenges') }}
              *</label>
            <div class="position-relative">
              <input type="text"
                class="form-control border-light light-shadow px-10"
                id="about_challenges" placeholder="{{ __('admin.about_challenges') }} *"
                value="{{ $client->company ? $client->company->about_challenges : old('about_challenges') }}"
                name="about_challenges" required autocomplete="about_challenges" >
              <i class="ti ti-target fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
                style="left: 7px;"></i>
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
  