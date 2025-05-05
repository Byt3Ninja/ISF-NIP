<form class="form d-flex flex-column h-100" action="{{ route('settings.system_social_login_update') }}" method="POST"
  enctype="multipart/form-data">
  {!! csrf_field() !!}
  @method('PUT')
  <input type="hidden" name="id" value="{{ $Title->id }}">

  <div class="form-content flex-grow-1">
    <div class="form-group mb-3">
      <label class="form-label mb-1" for="GOOGLE_CLIENT_ID">{{ __('settings.GOOGLE_CLIENT_ID') }}</label>
      <input id="GOOGLE_CLIENT_ID" type="text" class="form-control" value="{{ $Title->GOOGLE_CLIENT_ID }}"
        name="GOOGLE_CLIENT_ID">
    </div>
    <div class="form-group mb-3">
      <label class="form-label mb-1" for="GOOGLE_CLIENT_SECRET">{{ __('settings.GOOGLE_CLIENT_SECRET') }}</label>
      <input type="text" class="form-control" value="{{ $Title->GOOGLE_CLIENT_SECRET }}" name="GOOGLE_CLIENT_SECRET"
        id="GOOGLE_CLIENT_SECRET">
    </div>
    <div class="form-group mb-3">
      <label class="form-label mb-1" for="GOOGLE_REDIRECT">{{ __('settings.GOOGLE_REDIRECT') }}</label>
      <input type="text" class="form-control" value="{{ $Title->GOOGLE_REDIRECT }}" name="GOOGLE_REDIRECT"
        id="GOOGLE_REDIRECT">
    </div>


    <div class="form-group mb-3">
      <label class="form-label mb-1" for="LINKEDIN_CLIENT_ID">{{ __('admin.LINKEDIN_CLIENT_ID') }}</label>
      <input id="LINKEDIN_CLIENT_ID" type="text" class="form-control" value="{{ $Title->LINKEDIN_CLIENT_ID }}"
        name="LINKEDIN_CLIENT_ID">
    </div>
    <div class="form-group mb-3">
      <label class="form-label mb-1" for="LINKEDIN_CLIENT_SECRET">{{ __('admin.LINKEDIN_CLIENT_SECRET') }}</label>
      <input type="text" class="form-control" value="{{ $Title->LINKEDIN_CLIENT_SECRET }}" name="LINKEDIN_CLIENT_SECRET"
        id="LINKEDIN_CLIENT_SECRET">
    </div>
    <div class="form-group mb-3">
      <label class="form-label mb-1" for="LINKEDIN_REDIRECT_URI">{{ __('admin.LINKEDIN_REDIRECT_URI') }}</label>
      <input type="text" class="form-control" value="{{ $Title->LINKEDIN_REDIRECT_URI }}" name="LINKEDIN_REDIRECT_URI"
        id="LINKEDIN_REDIRECT_URI">
    </div>
  </div>

<hr>
<br>
  <div class="col-md-12 col-12">
    <div class="form-check form-switch mb-2">
      <input class="form-check-input" name="VerficationEmail" type="checkbox" {{ $Title->VFEmail == '1' ? 'checked' : '' }} />
      <label class="form-check-label">{{ __('admin.VerficationEmail') }}</label>
    </div>
  </div>

  <div class="form-group d-flex justify-content-end mt-auto">
    <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
  </div>
</form>
