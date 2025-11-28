<form class="form d-flex flex-column h-100" action="{{ route('settings.system_meeting_integration_update') }}" method="POST"
  enctype="multipart/form-data">
  {!! csrf_field() !!}
  @method('PUT')
  <input type="hidden" name="id" value="{{ $Title->id }}">

  <div class="form-content flex-grow-1">
    <div class="form-group mb-3">
      <label class="form-label mb-1">{{ __('settings.Meeting') }}</label>
      <fieldset class="form-label-group">
        <select class="form-control Meeting" id="Meeting" name="Meeting" required>
          <option value="0">{{ __('settings.Choose') }}</option>
          <option value="zoom" {{ strtolower( $Title->Meeting ) == 'zoom' ? 'selected' : '' }}>{{__('admin.Zoom')}}</option>
          <option value="teams" {{ strtolower( $Title->Meeting ) == 'teams' ? 'selected' : '' }}>{{__('admin.Teams')}}</option>

        </select>
      </fieldset>
    </div>

    <div id="teams_credentials" @if ($Title->Meeting != 'Teams') style="display: none;" @endif>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="MICROSOFT_CLIENT_ID">{{ __('settings.MICROSOFT_CLIENT_ID') }}</label>
        <input id="MICROSOFT_CLIENT_ID" type="text" class="form-control" value="{{ $Title->MICROSOFT_CLIENT_ID }}"
          name="MICROSOFT_CLIENT_ID">
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1"
          for="MICROSOFT_CLIENT_SECRET_VALUE">{{ __('settings.MICROSOFT_CLIENT_SECRET_VALUE') }}</label>
        <input type="text" class="form-control" value="{{ $Title->MICROSOFT_CLIENT_SECRET_VALUE }}"
          name="MICROSOFT_CLIENT_SECRET_VALUE" id="MICROSOFT_CLIENT_SECRET_VALUE">
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="MICROSOFT_TENANT_ID">{{ __('settings.MICROSOFT_TENANT_ID') }}</label>
        <input type="text" class="form-control" value="{{ $Title->MICROSOFT_TENANT_ID }}" name="MICROSOFT_TENANT_ID"
          id="MICROSOFT_TENANT_ID">
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1"
          for="MICROSOFT_CLIENT_SECRET_ID">{{ __('settings.MICROSOFT_CLIENT_SECRET_ID') }}</label>
        <input type="text" class="form-control" value="{{ $Title->MICROSOFT_CLIENT_SECRET_ID }}"
          name="MICROSOFT_CLIENT_SECRET_ID" id="MICROSOFT_CLIENT_SECRET_ID">
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="MICROSOFT_APP_EMAIL">{{ __('settings.MICROSOFT_APP_EMAIL') }}</label>
        <input type="text" class="form-control" value="{{ $Title->MICROSOFT_APP_EMAIL }}" name="MICROSOFT_APP_EMAIL"
          id="MICROSOFT_APP_EMAIL">
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="MICROSOFT_REDIRECT_URI">{{ __('settings.MICROSOFT_REDIRECT_URI') }}</label>
        <input type="text" class="form-control" value="{{ $Title->MICROSOFT_REDIRECT_URI }}"
          name="MICROSOFT_REDIRECT_URI" id="MICROSOFT_REDIRECT_URI">
      </div>
    </div>

    <div id="zoom_credentials" @if ($Title->Meeting != 'Zoom') style="display: none;" @endif>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="ZOOM_CLIENT_ID">{{ __('settings.ZOOM_CLIENT_ID') }}</label>
        <input id="ZOOM_CLIENT_ID" type="text" class="form-control" value="{{ $Title->ZOOM_CLIENT_ID }}"
          name="ZOOM_CLIENT_ID">
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="ZOOM_CLIENT_SECRET">{{ __('settings.ZOOM_CLIENT_SECRET') }}</label>
        <input type="text" class="form-control" value="{{ $Title->ZOOM_CLIENT_SECRET }}" name="ZOOM_CLIENT_SECRET"
          id="ZOOM_CLIENT_SECRET">
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="ZOOM_ACCOUNT_ID">{{ __('settings.ZOOM_ACCOUNT_ID') }}</label>
        <input type="text" class="form-control" value="{{ $Title->ZOOM_ACCOUNT_ID }}" name="ZOOM_ACCOUNT_ID"
          id="ZOOM_ACCOUNT_ID">
      </div>
    </div>


<hr>
<h3>{{__('admin.Offline_Sessions')}}</h3>
<p>{{__('admin.Offline_Sessions_Description')}}</p>
    <div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="allow_offline_sessions">{{ __('admin.Allow_Offline_Sessions') }}</label>
        <input id="allow_offline_sessions" type="checkbox" value="1" name="allow_offline_sessions" {{ $Title->allow_offline_sessions == 1 ? 'checked' : '' }}>
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="default_offline_session_location">{{ __('admin.Default_Offline_Session_Location') }}</label>
        <input type="text" class="form-control" value="{{ $Title->default_offline_session_location }}" name="default_offline_session_location"
          id="default_offline_session_location">
      </div>
      <div class="form-group mb-3">
        <label class="form-label mb-1" for="map_location">{{ __('admin.Map_Location') }}</label>
        <input type="text" class="form-control" value="{{ $Title->map_location }}" name="map_location"
          id="map_location">
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
          let selectedPlatform = document.getElementById("Meeting").value;
          document.getElementById("zoom_credentials").style.display = selectedPlatform === "zoom" ? "block" : "none";
          document.getElementById("teams_credentials").style.display = selectedPlatform === "teams" ? "block" : "none";
      }

      document.getElementById("Meeting").addEventListener("change", toggleCredentials);
      toggleCredentials();
  });
</script>
