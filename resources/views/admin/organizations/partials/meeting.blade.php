<form class="form d-flex flex-column h-100" action="{{ route('meeting-settings') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <input type="hidden" name="id" value="{{ $organization->id }}">
  
    <div class="form-content flex-grow-1">
      <div class="form-group mb-3">
        <label class="form-label mb-1">{{ __('admin.Meeting') }}</label>
        <fieldset class="form-label-group">
          <input type="text" class="form-control" name="meeting_platform" value="{{ old('meeting_platform', $organization->meeting_platform) }}" required>
        </fieldset>
      </div>
  
      <div class="ZOOM_CLIENT">
        <div class="form-group mb-3">
          <label class="form-label mb-1" for="ZOOM_CLIENT_ID">{{ __('settings.ZOOM_CLIENT_ID') }}</label>
          <input id="ZOOM_CLIENT_ID" type="text" class="form-control" value="{{ old('ZOOM_CLIENT_ID', $organization->zoom_client_id) }}" name="ZOOM_CLIENT_ID">
        </div>
        <div class="form-group mb-3">
          <label class="form-label mb-1" for="ZOOM_CLIENT_SECRET">{{ __('settings.ZOOM_CLIENT_SECRET') }}</label>
          <input type="text" class="form-control" value="{{ old('ZOOM_CLIENT_SECRET', $organization->zoom_client_secret) }}" name="ZOOM_CLIENT_SECRET" id="ZOOM_CLIENT_SECRET">
        </div>
        <div class="form-group mb-3">
          <label class="form-label mb-1" for="ZOOM_ACCOUNT_ID">{{ __('settings.ZOOM_ACCOUNT_ID') }}</label>
          <input type="text" class="form-control" value="{{ old('ZOOM_ACCOUNT_ID', $organization->zoom_account_id) }}" name="ZOOM_ACCOUNT_ID" id="ZOOM_ACCOUNT_ID">
        </div>
      </div>
  

  
    </div>
  
    <div class="form-group d-flex justify-content-end mt-auto">
      <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
    </div>
  </form>
  