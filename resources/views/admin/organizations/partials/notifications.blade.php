<form method="POST" action="{{ route('settings.organization.notifications_update') }}" class="row g-3">
  @csrf
  @method('PUT')
  @php($os = $organization?->organizationSettings)
  <div class="col-12">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="accept_email" name="accept_email" value="1" {{ old('accept_email', $os->accept_email ?? true) ? 'checked' : '' }}>
      <label class="form-check-label" for="accept_email">Enable Email</label>
    </div>
  </div>
  <div class="col-12">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="accept_whatsapp" name="accept_whatsapp" value="1" {{ old('accept_whatsapp', $os->accept_whatsapp ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="accept_whatsapp">Enable WhatsApp</label>
    </div>
  </div>
  <div class="col-12">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="accept_pusher" name="accept_pusher" value="1" {{ old('accept_pusher', $os->accept_pusher ?? false) ? 'checked' : '' }}>
      <label class="form-check-label" for="accept_pusher">Enable Push (Pusher)</label>
    </div>
  </div>
  <div class="col-12">
    <button type="submit" class="btn btn-primary">
      <i class="ti ti-device-floppy"></i> Save Notification Settings
    </button>
  </div>
</form>
