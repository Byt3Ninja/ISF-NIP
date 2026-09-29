<form class="form d-flex flex-column h-100" action="{{ route('settings.organization_meeting_manage') }}" method="POST" enctype="multipart/form-data"> 
  @csrf
  @method('PUT')
  <input type="hidden" name="id" value="{{ $organization?->id }}">

  <div class="form-content flex-grow-1">
      <div class="form-group mb-3">
          <label class="form-label mb-1">{{ __('admin.Meeting') }}</label>
          <select class="form-control" id="meeting_platform" name="meeting_platform" required>
              <option value="zoom" {{ strtolower( old('meeting_platform', $organization?->meeting_platform) ) == 'zoom' ? 'selected' : '' }}>{{__('admin.Zoom')}}</option>
              <option value="teams" {{ strtolower( old('meeting_platform', $organization?->meeting_platform) ) == 'teams' ? 'selected' : '' }}>{{__('admin.Teams')}}</option>
          </select>
      </div>

      <div class="form-group mb-4">
        <label class="form-label" for="max_concurrent_sessions">
          {{ __('settings.max_concurrent_sessions') }}
        </label>
        <input
          id="max_concurrent_sessions"
          type="number"
          class="form-control"
          name="max_concurrent_sessions"
          value="{{ old('max_concurrent_sessions', $organization?->max_concurrent_sessions) }}"
          placeholder="{{ __('settings.max_concurrent_sessions_placeholder') }}"
        />
        <small class="text-muted">{{ __('settings.max_concurrent_sessions_description') }}</small>
    </div>

      <div id="zoom_credentials" class="platform-credentials" style="display: none;">
          <div class="form-group mb-3">
              <label class="form-label mb-1" for="ZOOM_CLIENT_ID">{{ __('settings.ZOOM_CLIENT_ID') }}</label>
              <input id="ZOOM_CLIENT_ID" type="text" class="form-control" value="{{ old('ZOOM_CLIENT_ID', $organization?->zoom_client_id) }}" name="ZOOM_CLIENT_ID">
          </div>
          <div class="form-group mb-3">
              <label class="form-label mb-1" for="ZOOM_CLIENT_SECRET">{{ __('settings.ZOOM_CLIENT_SECRET') }}</label>
              <input type="text" class="form-control" value="{{ old('ZOOM_CLIENT_SECRET', $organization?->zoom_client_secret) }}" name="ZOOM_CLIENT_SECRET" id="ZOOM_CLIENT_SECRET">
          </div>
          <div class="form-group mb-3">
              <label class="form-label mb-1" for="ZOOM_ACCOUNT_ID">{{ __('settings.ZOOM_ACCOUNT_ID') }}</label>
              <input type="text" class="form-control" value="{{ old('ZOOM_ACCOUNT_ID', $organization?->zoom_account_id) }}" name="ZOOM_ACCOUNT_ID" id="ZOOM_ACCOUNT_ID">
          </div>
      </div>

      <div id="teams_credentials" class="platform-credentials" style="display: none;">
          <div class="form-group mb-3">
              <label class="form-label mb-1" for="MICROSOFT_CLIENT_ID">{{ __('settings.MICROSOFT_CLIENT_ID') }}</label>
              <input id="MICROSOFT_CLIENT_ID" type="text" class="form-control" value="{{ old('MICROSOFT_CLIENT_ID', $organization?->microsoft_client_id) }}" name="MICROSOFT_CLIENT_ID">
          </div>
          <div class="form-group mb-3">
              <label class="form-label mb-1" for="MICROSOFT_CLIENT_SECRET_VALUE">{{ __('settings.MICROSOFT_CLIENT_SECRET_VALUE') }}</label>
              <input type="text" class="form-control" value="{{ old('MICROSOFT_CLIENT_SECRET_VALUE', $organization?->microsoft_client_secret_value) }}" name="MICROSOFT_CLIENT_SECRET_VALUE" id="MICROSOFT_CLIENT_SECRET_VALUE">
          </div>
          <div class="form-group mb-3">
              <label class="form-label mb-1" for="MICROSOFT_TENANT_ID">{{ __('settings.MICROSOFT_TENANT_ID') }}</label>
              <input type="text" class="form-control" value="{{ old('MICROSOFT_TENANT_ID', $organization?->microsoft_tenant_id) }}" name="MICROSOFT_TENANT_ID" id="MICROSOFT_TENANT_ID">
          </div>

          <div class="form-group mb-3">
            <label class="form-label mb-1" for="MICROSOFT_CLIENT_SECRET_ID">{{ __('settings.MICROSOFT_CLIENT_SECRET_ID') }}</label>
            <input type="text" class="form-control" value="{{ old('MICROSOFT_CLIENT_SECRET_ID', $organization?->microsoft_client_secret_id) }}" name="MICROSOFT_CLIENT_SECRET_ID" id="MICROSOFT_CLIENT_SECRET_ID">
          </div>

          <div class="form-group mb-3">
            <label class="form-label mb-1" for="MICROSOFT_APP_EMAIL">{{ __('settings.MICROSOFT_APP_EMAIL') }}</label>
            <input type="text" class="form-control" value="{{ old('MICROSOFT_APP_EMAIL', $organization?->microsoft_app_email) }}" name="MICROSOFT_APP_EMAIL" id="MICROSOFT_APP_EMAIL">
         </div>

          <div class="form-group mb-3">
              <label class="form-label mb-1" for="MICROSOFT_REDIRECT_URI">{{ __('settings.MICROSOFT_REDIRECT_URI') }}</label>
              <input type="text" class="form-control" value="{{ old('MICROSOFT_REDIRECT_URI', $organization?->microsoft_redirect_uri) }}" name="MICROSOFT_REDIRECT_URI" id="MICROSOFT_REDIRECT_URI">
          </div>
      </div>
  </div>

  <div class="form-group d-flex justify-content-end mt-auto">
      <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
  </div>
</form>

<script>
  document.addEventListener("DOMContentLoaded", function () {
      function toggleCredentials() {
          let selectedPlatform = document.getElementById("meeting_platform").value;
          document.getElementById("zoom_credentials").style.display = selectedPlatform === "zoom" ? "block" : "none";
          document.getElementById("teams_credentials").style.display = selectedPlatform === "teams" ? "block" : "none";
      }

      document.getElementById("meeting_platform").addEventListener("change", toggleCredentials);
      toggleCredentials();
  });
</script>
