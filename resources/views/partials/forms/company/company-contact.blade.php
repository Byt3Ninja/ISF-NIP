<div class="card">
  <div class="card-body">
    <div class="border-bottom border-light mb-6">
      <h5 class="card-title fw-semibold">{{ $tab['label'] }}</h5>
    </div>
    @if (Auth::user()->hasanyRole('Admin|organizationManager'))
      <form method="post"
        action="{{ route('update_my_company', $client->company->id) }}"
        enctype="multipart/form-data">
      @else
        <form id="contact-form" method="post"
          action="{{ route('update_my_company') }}"
          enctype="multipart/form-data">
    @endif
    {!! csrf_field() !!}
    <input name="CompanyN" type="hidden" value="2">
    <div class="row">
      <div class="col-md-6 col-12 mb-6">
        <div class="d-grid">
          <div class="d-grid gap-2">
            <label class="form-label fs-6 fw-bold" for="phone">{{ __('auth.Phone') }}</label>
              <input 
                class="form-control phone-input" id="phone"
                name="phone" type="tel"
                value="{{ trim($client->company ? $client->company->phone : old('phone')) }}"
                placeholder="{{ __('auth.Phone') }}" autocomplete="phone"
                oninput="this.value = this.value.trim()"
              />
            <span class="text-danger" id="phone-error"
              style="display: none;"></span>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-12 mb-6">
        <div class="d-grid">
          <div class="d-grid gap-2">
            <label class="form-label fs-6 fw-bold"
              for="WhatsApp">{{ __('admin.WhatsApp') }}</label>
                <input class="form-control phone-input" id="WhatsApp"
                  name="WhatsApp" type="tel"
                  value="{{ $client->company ? $client->company->whatsapp : old('WhatsApp') }}"
                  placeholder="{{ __('admin.WhatsApp') }}" autocomplete="WhatsApp"
                  oninput="this.value = this.value.trim()"
                   />
            <span class="text-danger" id="whatsapp-error"
              style="display: none;"></span>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-12 mb-6">
        <label class="form-label fs-6 fw-bold"
          for="email">{{ __('admin.E-mail') }}</label>
          <input class="form-control" id="email" name="email"
            type="email"
            value="{{ $client->company ? $client->company->email : old('email') }}"
            style="direction: ltr;" placeholder="{{ __('admin.E-mail') }} *"
            autocomplete="email"
            oninput="this.value = this.value.trimStart()"
          >
      </div>
    </div>
    <div class="row">
      <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary" id="submit-button" type="submit">
          <span id="button-text">{{ __('admin.Update') }}</span>
          <span class="spinner-border spinner-border-sm d-none"
            id="button-spinner" role="status" aria-hidden="true"></span>
        </button>
      </div>
    </div>
    </form>
  </div>
</div>