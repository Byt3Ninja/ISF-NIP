<form class="form d-flex flex-column h-100" action="{{ route('settings.system_social_login_update') }}" method="POST"
  enctype="multipart/form-data">
  {!! csrf_field() !!}
  @method('PUT')
  <input type="hidden" name="id" value="{{ $Title->id }}">

  <div class="form-content flex-grow-1">
    <!-- Navigation Pills -->
    <ul class="nav nav-pills mb-4" id="socialLoginTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="google-oauth-tab" data-bs-toggle="pill" data-bs-target="#google-oauth" type="button" role="tab" aria-controls="google-oauth" aria-selected="true">
          <i class="fab fa-google me-2"></i>{{ __('admin.google_oauth') }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="google-calendar-tab" data-bs-toggle="pill" data-bs-target="#google-calendar" type="button" role="tab" aria-controls="google-calendar" aria-selected="false">
          <i class="fab fa-google me-2"></i>{{ __('admin.google_calendar') }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="linkedin-tab" data-bs-toggle="pill" data-bs-target="#linkedin" type="button" role="tab" aria-controls="linkedin" aria-selected="false">
          <i class="fab fa-linkedin me-2"></i>{{ __('admin.linkedin') }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="email-settings-tab" data-bs-toggle="pill" data-bs-target="#email-settings" type="button" role="tab" aria-controls="email-settings" aria-selected="false">
          <i class="fas fa-envelope me-2"></i>{{ __('admin.email_settings') }}
        </button>
      </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="socialLoginTabsContent">
      <!-- Google OAuth Tab -->
      <div class="tab-pane fade show active" id="google-oauth" role="tabpanel" aria-labelledby="google-oauth-tab">
        <div class="form-group mb-6">
          <label class="form-label mb-1" for="GOOGLE_CLIENT_ID">{{ __('settings.GOOGLE_CLIENT_ID') }}</label>
          <input id="GOOGLE_CLIENT_ID" type="text" class="form-control" value="{{ $Title->GOOGLE_CLIENT_ID }}"
            name="GOOGLE_CLIENT_ID">
        </div>
        <div class="form-group mb-6">
          <label class="form-label mb-1" for="GOOGLE_CLIENT_SECRET">{{ __('settings.GOOGLE_CLIENT_SECRET') }}</label>
          <input type="text" class="form-control" value="{{ $Title->GOOGLE_CLIENT_SECRET }}" name="GOOGLE_CLIENT_SECRET"
            id="GOOGLE_CLIENT_SECRET">
        </div>
        <div class="form-group mb-6">
          <label class="form-label mb-1" for="GOOGLE_REDIRECT">{{ __('settings.GOOGLE_REDIRECT') }}</label>
          <input type="text" class="form-control" value="{{ $Title->GOOGLE_REDIRECT }}" name="GOOGLE_REDIRECT"
            id="GOOGLE_REDIRECT">
        </div>
      </div>

      <!-- Google Calendar Tab -->
      <div class="tab-pane fade" id="google-calendar" role="tabpanel" aria-labelledby="google-calendar-tab">
        <div class="form-group mb-6">
          <label class="form-label mb-1" for="GOOGLE_CALENDAR_CLIENT_ID">{{ __('settings.GOOGLE_CALENDAR_CLIENT_ID') }}</label>
          <input id="GOOGLE_CALENDAR_CLIENT_ID" type="text" class="form-control" value="{{ $Title->GOOGLE_CALENDAR_CLIENT_ID }}"
            name="GOOGLE_CALENDAR_CLIENT_ID">
        </div>
        <div class="form-group mb-6">
          <label class="form-label mb-1" for="GOOGLE_CALENDAR_CLIENT_SECRET">{{ __('settings.GOOGLE_CALENDAR_CLIENT_SECRET') }}</label>
          <input type="text" class="form-control" value="{{ $Title->GOOGLE_CALENDAR_CLIENT_SECRET }}" name="GOOGLE_CALENDAR_CLIENT_SECRET"
            id="GOOGLE_CALENDAR_CLIENT_SECRET">
        </div>
        <div class="form-group mb-6">
          <label class="form-label mb-1" for="GOOGLE_CALENDAR_REDIRECT">{{ __('settings.GOOGLE_CALENDAR_REDIRECT') }}</label>
          <input type="text" class="form-control" value="{{ $Title->GOOGLE_CALENDAR_REDIRECT }}" name="GOOGLE_CALENDAR_REDIRECT"
            id="GOOGLE_CALENDAR_REDIRECT">
        </div>
      </div>

      <!-- LinkedIn Tab -->
      <div class="tab-pane fade" id="linkedin" role="tabpanel" aria-labelledby="linkedin-tab">
        <div class="form-group mb-6">
          <label class="form-label mb-1" for="LINKEDIN_CLIENT_ID">{{ __('admin.LINKEDIN_CLIENT_ID') }}</label>
          <input id="LINKEDIN_CLIENT_ID" type="text" class="form-control" value="{{ $Title->LINKEDIN_CLIENT_ID }}"
            name="LINKEDIN_CLIENT_ID">
        </div>
        <div class="form-group mb-6">
          <label class="form-label mb-1" for="LINKEDIN_CLIENT_SECRET">{{ __('admin.LINKEDIN_CLIENT_SECRET') }}</label>
          <input type="text" class="form-control" value="{{ $Title->LINKEDIN_CLIENT_SECRET }}" name="LINKEDIN_CLIENT_SECRET"
            id="LINKEDIN_CLIENT_SECRET">
        </div>
        <div class="form-group mb-6">
          <label class="form-label mb-1" for="LINKEDIN_REDIRECT_URI">{{ __('admin.LINKEDIN_REDIRECT_URI') }}</label>
          <input type="text" class="form-control" value="{{ $Title->LINKEDIN_REDIRECT_URI }}" name="LINKEDIN_REDIRECT_URI"
            id="LINKEDIN_REDIRECT_URI">
        </div>
      </div>

      <!-- Email Settings Tab -->
      <div class="tab-pane fade" id="email-settings" role="tabpanel" aria-labelledby="email-settings-tab">
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" name="VerficationEmail" type="checkbox" {{ $Title->VFEmail == '1' ? 'checked' : '' }} />
          <label class="form-check-label">{{ __('admin.VerficationEmail') }}</label>
        </div>
      </div>
    </div>
  </div>

  <div class="form-group d-flex justify-content-end mt-auto">
    <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
  </div>
</form>
