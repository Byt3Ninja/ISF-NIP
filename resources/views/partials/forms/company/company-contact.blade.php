<div class="card">
  <div class="card-body">
    <div class="border-bottom border-light mb-6">
      <h5 class="card-title">{{ $tab['label'] }}</h5>
    </div>
    <form method="post" action="{{ route('update_my_company') }}"
      enctype="multipart/form-data">
      {!! csrf_field() !!}
      <input type="hidden" value="2" name="CompanyN">
      <div class="row">
        <div class="col-md-4 col-12 mb-3">
          <label for="phone"
            class="form-label">{{ __('admin.Phone') }}*</label>
          <div class="input-group">
            <div class="codeInputContainer">
              <select id="country_code" name="country_code"
                class="form-select select2" required>
              </select>
            </div>
            <div class="position-relative">
              <input type="text"
                class="form-control border-light light-shadow px-10"
                id="phone" placeholder="{{ __('admin.Phone') }}*"
                value="{{ $client->company ? $client->company->phone : old('phone') }}"
                name="phone" required autocomplete="new-phone">
              <i class="ti ti-phone fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
                style="left: 7px;"></i>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-12 mb-3">
          <label for="WhatsApp"
            class="form-label">{{ __('admin.WhatsApp') }}*</label>
          <div class="position-relative">
            <input type="text"
              class="form-control border-light light-shadow px-10"
              id="WhatsApp" placeholder="{{ __('admin.WhatsApp') }}*"
              value="{{ $client->company ? $client->company->whatsapp : old('WhatsApp') }}"
              name="WhatsApp" required autocomplete="WhatsApp">
            <i class="ti ti-brand-whatsapp fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
              style="left: 7px;"></i>
          </div>
        </div>
        <div class="col-md-6 col-12 mb-3">
          <label for="email" class="form-label">{{ __('admin.E-mail') }}
            *</label>
          <div class="position-relative">
            <input type="email"
              class="form-control border-light light-shadow px-10"
              id="email" placeholder="{{ __('admin.E-mail') }} *"
              value="{{ $client->company ? $client->company->email : old('email') }}"
              name="email" required autocomplete="new-email"
              style="direction: ltr;">
            <i class="ti ti-mail fs-5 position-absolute top-50 translate-middle-y me-3 text-primary"
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
