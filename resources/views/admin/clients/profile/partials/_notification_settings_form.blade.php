<div class="card-body">
  <form action="{{ route('user.notification_settings_update') }}" method="POST">
    @csrf
    
    <div class="row">
      <div class="col-md-12">
        <div class="alert alert-info">
          <h6 class="alert-heading mb-1">{{ __('admin.Notification_Settings_Info') }}</h6>
          <p class="mb-0">{{ __('admin.Notification_Settings_Description') }}</p>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <!-- Email Notifications -->
        <div class="form-check form-switch mb-4">
          <input class="form-check-input" type="hidden" name="accept_email" value="0">
          <input class="form-check-input" type="checkbox" id="accept_email" name="accept_email" value="1"
                 {{ auth()->user()->accept_email ? 'checked' : '' }}>
          <label class="form-check-label" for="accept_email">
            <div class="d-flex align-items-center">
              <i class="ti ti-mail text-primary me-2"></i>
              <div>
                <h6 class="mb-0">{{ __('admin.Email_Notifications') }}</h6>
                <small class="text-muted">{{ __('admin.Receive_notifications_via_email') }}</small>
              </div>
            </div>
          </label>
        </div>
      </div>

      <div class="col-md-6">
        <!-- WhatsApp Notifications -->
        <div class="form-check form-switch mb-4">
          <input class="form-check-input" type="hidden" name="accept_whatsapp" value="0">
          <input class="form-check-input" type="checkbox" id="accept_whatsapp" name="accept_whatsapp" value="1"
                 {{ auth()->user()->accept_whatsapp ? 'checked' : '' }}>
          <label class="form-check-label" for="accept_whatsapp">
            <div class="d-flex align-items-center">
              <i class="ti ti-brand-whatsapp text-success me-2"></i>
              <div>
                <h6 class="mb-0">{{ __('admin.WhatsApp_Notifications') }}</h6>
                <small class="text-muted">{{ __('admin.Receive_notifications_via_WhatsApp') }}</small>
              </div>
            </div>
          </label>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <!-- Push Notifications -->
        <div class="form-check form-switch mb-4">
          <input class="form-check-input" type="hidden" name="accept_pusher" value="0">
          <input class="form-check-input" type="checkbox" id="accept_pusher" name="accept_pusher" value="1"
                 {{ auth()->user()->accept_pusher ? 'checked' : '' }}>
          <label class="form-check-label" for="accept_pusher">
            <div class="d-flex align-items-center">
              <i class="ti ti-bell text-warning me-2"></i>
              <div>
                <h6 class="mb-0">{{ __('admin.Push_Notifications') }}</h6>
                <small class="text-muted">{{ __('admin.Receive_push_notifications') }}</small>
              </div>
            </div>
          </label>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <button type="submit" class="btn btn-primary me-2">
          <i class="ti ti-device-floppy me-1"></i>
          {{ __('admin.Save_Changes') }}
        </button>
        <button type="reset" class="btn btn-outline-secondary">
          <i class="ti ti-refresh me-1"></i>
          {{ __('admin.Reset') }}
        </button>
      </div>
    </div>
  </form>
</div>
