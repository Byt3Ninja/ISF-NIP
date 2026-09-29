@php($smtp = $organization?->organizationSettings)
@php($hasCustomConfig = $smtp && ($smtp->email_provider || $smtp->smtp_host))

{{-- Info alert when no custom provider is configured --}}
@if(!$hasCustomConfig)
  <div class="alert alert-info mb-3">
    <i class="ti ti-info-circle me-1"></i>
    {{ __('settings.org_using_platform_email') }}
  </div>
@endif

{{-- Warning when the organization's own transport is currently failing --}}
@if($smtp && $smtp->smtp_last_error)
  <div class="alert alert-warning mb-3">
    <i class="ti ti-alert-triangle me-1"></i>
    {{ __('settings.org_email_failing') }}
    @if($smtp->smtp_failed_at)
      <small class="d-block mt-1">{{ $smtp->smtp_failed_at->diffForHumans() }}</small>
    @endif
    <small class="d-block text-muted mt-1">{{ $smtp->smtp_last_error }}</small>
  </div>
@endif

<div>
  {{-- Provider Selector --}}
  <div class="mb-3">
    <label class="form-label">{{ __('settings.email_provider') }}</label>
    <select id="orgEmailProviderSelect" class="form-select" onchange="toggleOrgEmailProvider(this.value)">
      <option value="smtp" {{ ($smtp->email_provider ?? 'smtp') === 'smtp' || !$smtp?->email_provider ? 'selected' : '' }}>SMTP</option>
      <option value="resend" {{ ($smtp->email_provider ?? '') === 'resend' ? 'selected' : '' }}>Resend</option>
    </select>
  </div>

  {{-- SMTP Fields --}}
  <div id="org-smtp-fields" style="{{ ($smtp->email_provider ?? '') === 'resend' ? 'display:none' : '' }}">
    <form method="POST" action="{{ route('settings.organization.smtp_update') }}" class="row g-3">
      @csrf
      @method('PUT')
      <input type="hidden" name="email_provider" value="smtp">

      <div class="col-md-6">
        <label class="form-label">SMTP Host</label>
        <input type="text" name="smtp_host" class="form-control" value="{{ old('smtp_host', $smtp->smtp_host ?? '') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">SMTP Port</label>
        <input type="number" name="smtp_port" class="form-control" value="{{ old('smtp_port', $smtp->smtp_port ?? '') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">{{ __('settings.Encryption') }}</label>
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
  </div>

  {{-- Resend Fields --}}
  <div id="org-resend-fields" style="{{ ($smtp->email_provider ?? '') !== 'resend' ? 'display:none' : '' }}">
    <form id="orgResendForm" class="row g-3">
      @csrf
      <input type="hidden" name="email_provider" value="resend">

      <div class="col-md-6">
        <label class="form-label">{{ __('settings.resend_api_key') }}</label>
        <input id="org_resend_api_key" type="password" class="form-control" name="resend_api_key"
          value="{{ $smtp->resend_api_key ?? '' }}" maxlength="64" required>
        <div id="orgResendApiKeyError" class="invalid-feedback"></div>
      </div>

      <div class="col-12">
        <div id="orgResendStatusMessage" class="mb-3" style="display:none"></div>
      </div>

      <div class="col-12">
        <button type="submit" id="orgResendSaveBtn" class="btn btn-primary">
          <span id="orgResendBtnText"><i class="ti ti-device-floppy"></i> {{ __('settings.connect_and_save') }}</span>
          <span id="orgResendBtnSpinner" class="spinner-border spinner-border-sm ms-1" role="status" style="display:none"></span>
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function toggleOrgEmailProvider(value) {
  document.getElementById('org-smtp-fields').style.display = value === 'smtp' ? '' : 'none';
  document.getElementById('org-resend-fields').style.display = value === 'resend' ? '' : 'none';
}

function validateResendApiKey(apiKey) {
  if (!apiKey) {
    return @json(__('settings.api_key_required'));
  }

  if (!apiKey.startsWith('re_')) {
    return @json(__('settings.invalid_resend_api_key_format') ?? 'Invalid API key format. Resend API keys must start with "re_".');
  }

  const keyBody = apiKey.substring(3);

  if (keyBody.length < 10) {
    return @json(__('settings.resend_api_key_too_short') ?? 'API key is too short. Please enter a valid Resend API key.');
  }

  if (!/^[a-zA-Z0-9_]+$/.test(keyBody)) {
    return @json(__('settings.resend_api_key_invalid_chars') ?? 'API key contains invalid characters. Only letters, numbers, and underscores are allowed after "re_".');
  }

  return null;
}

document.addEventListener('DOMContentLoaded', function() {
  const resendForm = document.getElementById('orgResendForm');
  if (!resendForm) return;

  // Live validation: clear error state as the user types a valid key
  const apiKeyInput = document.getElementById('org_resend_api_key');
  const errorDiv = document.getElementById('orgResendApiKeyError');

  apiKeyInput.addEventListener('input', function() {
    const validationError = validateResendApiKey(this.value.trim());
    if (!validationError) {
      this.classList.remove('is-invalid');
      errorDiv.textContent = '';
    }
  });

  resendForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const apiKey = apiKeyInput.value.trim();
    const btn = document.getElementById('orgResendSaveBtn');
    const btnSpinner = document.getElementById('orgResendBtnSpinner');
    const statusMsg = document.getElementById('orgResendStatusMessage');

    // Reset
    apiKeyInput.classList.remove('is-invalid');
    errorDiv.textContent = '';
    statusMsg.style.display = 'none';

    // Client-side validation — prevent server request if key is invalid
    const validationError = validateResendApiKey(apiKey);
    if (validationError) {
      apiKeyInput.classList.add('is-invalid');
      errorDiv.textContent = validationError;
      return;
    }

    // Disable button, show spinner
    btn.disabled = true;
    btnSpinner.style.display = '';

    const token = document.querySelector('meta[name="csrf-token"]');
    const formData = new FormData(resendForm);

    fetch("{{ route('settings.organization.smtp_update') }}", {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-HTTP-Method-Override': 'PUT'
      },
      body: formData
    })
    .then(async response => {
      const data = await response.json();
      if (!response.ok) {
        throw { status: response.status, data };
      }
      return data;
    })
    .then(data => {
      statusMsg.className = 'mb-3 alert alert-success';
      statusMsg.textContent = data.message || @json(__('settings.email_config_saved'));
      statusMsg.style.display = '';
    })
    .catch(err => {
      const msg = err.data?.message || @json(__('settings.email_config_failed'));
      if (err.status === 422 && err.data?.message === @json(__('settings.invalid_api_key'))) {
        apiKeyInput.classList.add('is-invalid');
        errorDiv.textContent = msg;
      }
      statusMsg.className = 'mb-3 alert alert-danger';
      statusMsg.textContent = msg;
      statusMsg.style.display = '';
    })
    .finally(() => {
      btn.disabled = false;
      btnSpinner.style.display = 'none';
    });
  });
});
</script>
@endpush
