<div class="container mt-5">
  <form method="post" action="{{ route('update_my_company') }}" enctype="multipart/form-data">
    {!! csrf_field() !!}
    <input type="hidden" value="2" name="CompanyN">
    <div class="row">
      <div class="col-md-6 col-12 mb-3">
        <label for="phone" class="form-label">{{ __('admin.Phone') }}*</label>
        <div class="input-group">
          <span class="input-group-text"><i class="text-primary fa fa-phone"></i></span>
          <input type="text" class="form-control" id="phone" placeholder="{{ __('admin.Phone') }}*"
            value="{{ $client->company ? $client->company->phone : old('phone') }}" name="phone" required autocomplete="new-phone">
        </div>
      </div>
      <div class="col-md-6 col-12 mb-3">
        <label for="WhatsApp" class="form-label">{{ __('admin.WhatsApp') }}*</label>
        <div class="input-group">
          <span class="input-group-text"><i class="text-success fa-brands fa-whatsapp"></i></span>
          <input type="text" class="form-control" id="WhatsApp" placeholder="{{ __('admin.WhatsApp') }}*"
            value="{{ $client->company ? $client->company->WhatsApp : old('WhatsApp') }}" name="WhatsApp" required autocomplete="WhatsApp">
        </div>
      </div>
      <div class="col-md-6 col-12 mb-3">
        <label for="email" class="form-label">{{ __('admin.E-mail') }} *</label>
        <div class="input-group">
          <span class="input-group-text"><i class="text-primary fa fa-envelope"></i></span>
          <input type="email" class="form-control" id="email" placeholder="{{ __('admin.E-mail') }} *"
            value="{{ $client->company ? $client->company->email : old('email') }}" name="email" required autocomplete="new-email" style="direction: ltr;">
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" id="submit-button">
          <span id="button-text">{{ __('admin.Update') }}</span>
          <span id="button-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </form>
</div>