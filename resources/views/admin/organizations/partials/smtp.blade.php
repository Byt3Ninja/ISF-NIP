<form method="POST" action="{{ route('settings.organization.smtp_update') }}" class="row g-3">
  @csrf
  @method('PUT')
  @php($smtp = $organization->organizationSettings)
  <div class="col-md-6">
    <label class="form-label">SMTP Host</label>
    <input type="text" name="smtp_host" class="form-control" value="{{ old('smtp_host', $smtp->smtp_host ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">SMTP Port</label>
    <input type="number" name="smtp_port" class="form-control" value="{{ old('smtp_port', $smtp->smtp_port ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Encryption</label>
    <select name="smtp_encryption" class="form-select">
      @php($enc = old('smtp_encryption', $smtp->smtp_encryption ?? ''))
      <option value="" {{ $enc === null || $enc === '' ? 'selected' : '' }}>None</option>
      <option value="tls" {{ $enc === 'tls' ? 'selected' : '' }}>TLS</option>
      <option value="ssl" {{ $enc === 'ssl' ? 'selected' : '' }}>SSL</option>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Username</label>
    <input type="text" name="smtp_username" class="form-control" value="{{ old('smtp_username', $smtp->smtp_username ?? '') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">Password</label>
    <input type="password" name="smtp_password" class="form-control" value="{{ old('smtp_password', $smtp->smtp_password ?? '') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">From Address</label>
    <input type="email" name="smtp_from_address" class="form-control" value="{{ old('smtp_from_address', $smtp->smtp_from_address ?? '') }}">
  </div>
  <div class="col-md-6">
    <label class="form-label">From Name</label>
    <input type="text" name="smtp_from_name" class="form-control" value="{{ old('smtp_from_name', $smtp->smtp_from_name ?? '') }}">
  </div>
  <div class="col-12">
    <button type="submit" class="btn btn-primary">
      <i class="ti ti-device-floppy"></i> Save SMTP Settings
    </button>
  </div>
</form>
