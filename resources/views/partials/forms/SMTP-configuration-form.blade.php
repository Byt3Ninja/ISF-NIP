<div>
  {{-- Provider Selector --}}
  <div class="form-group mb-3">
    <label class="form-label mb-1">{{ __('settings.email_provider') }}</label>
    <select id="emailProviderSelect" class="form-control" onchange="toggleEmailProvider(this.value)">
      <option value="smtp" {{ ($Title->email_provider ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
      <option value="resend" {{ ($Title->email_provider ?? 'smtp') === 'resend' ? 'selected' : '' }}>Resend</option>
    </select>
  </div>

  {{-- SMTP Fields --}}
  <div id="smtp-fields" style="{{ ($Title->email_provider ?? 'smtp') === 'resend' ? 'display:none' : '' }}">
    <form class="form d-flex flex-column h-100" action="{{ route('settings.system_mail_update') }}" method="POST" enctype="multipart/form-data">
      {!! csrf_field() !!}
      @method('PUT')
      <input type="hidden" name="id" value="{{ $Title->id }}">
      <input type="hidden" name="email_provider" value="smtp">

      <div class="form-content flex-grow-1">
        <div class="form-group mb-3">
          <label class="form-label mb-1" for="host">{{ __('settings.host') }}</label>
          <input id="host" type="text" class="form-control" value="{{ $Title->host }}" name="host">
        </div>
        <div class="form-group mb-3">
          <label class="form-label mb-1" for="Mail">{{ __('settings.Mail') }}</label>
          <input id="Mail" type="text" class="form-control" value="{{ $Title->Mail }}" name="Mail">
        </div>
        <div class="form-group mb-3">
          <label class="form-label mb-1" for="Mailpassword">{{ __('settings.Mailpassword') }}</label>
          <input type="text" class="form-control" value="{{ $Title->Mailpassword }}" name="Mailpassword" id="Mailpassword">
        </div>
        <div class="form-group mb-3">
          <label class="form-label mb-1" for="Port">{{ __('settings.Port') }}</label>
          <input type="text" class="form-control" value="{{ $Title->Port }}" name="Port" id="Port">
        </div>
        <div class="form-group mb-3">
          <label class="form-label mb-1" for="Encryption">{{ __('settings.Encryption') }}</label>
          <select class="form-control" name="Encryption" id="Encryption">
            <option value="tls" {{ $Title->Encryption == 'tls' ? 'selected' : '' }}>tls</option>
            <option value="ssl" {{ $Title->Encryption == 'ssl' ? 'selected' : '' }}>ssl</option>
          </select>
        </div>
      </div>

      <div class="form-group d-flex justify-content-end mt-auto">
        <button type="submit" class="btn btn-primary mr-1 mb-1">{{ __('settings.Submit') }}</button>
      </div>
    </form>
  </div>

  {{-- Resend Fields --}}
  <div id="resend-fields" style="{{ ($Title->email_provider ?? 'smtp') !== 'resend' ? 'display:none' : '' }}">
    <form id="resendForm" class="form d-flex flex-column h-100">
      @csrf
      <input type="hidden" name="id" value="{{ $Title->id }}">
      <input type="hidden" name="email_provider" value="resend">

      <div class="form-content flex-grow-1">
        <div class="form-group mb-3">
          <label class="form-label mb-1" for="resend_api_key">{{ __('settings.resend_api_key') }}</label>
          <input id="resend_api_key" type="password" class="form-control" name="resend_api_key"
            value="{{ $Title->resend_api_key ?? '' }}" maxlength="64" required>
          <div id="resendApiKeyError" class="invalid-feedback"></div>
        </div>
      </div>

      <div id="resendStatusMessage" class="mb-3" style="display:none"></div>

      <div class="form-group d-flex justify-content-end mt-auto">
        <button type="submit" id="resendSaveBtn" class="btn btn-primary mr-1 mb-1">
          <span id="resendBtnText">{{ __('settings.connect_and_save') }}</span>
          <span id="resendBtnSpinner" class="spinner-border spinner-border-sm ms-1" role="status" style="display:none"></span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleEmailProvider(value) {
  document.getElementById('smtp-fields').style.display = value === 'smtp' ? '' : 'none';
  document.getElementById('resend-fields').style.display = value === 'resend' ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
  const resendForm = document.getElementById('resendForm');
  if (!resendForm) return;

  resendForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const apiKeyInput = document.getElementById('resend_api_key');
    const apiKey = apiKeyInput.value.trim();
    const btn = document.getElementById('resendSaveBtn');
    const btnText = document.getElementById('resendBtnText');
    const btnSpinner = document.getElementById('resendBtnSpinner');
    const statusMsg = document.getElementById('resendStatusMessage');
    const errorDiv = document.getElementById('resendApiKeyError');

    // Reset
    apiKeyInput.classList.remove('is-invalid');
    errorDiv.textContent = '';
    statusMsg.style.display = 'none';

    if (!apiKey) {
      apiKeyInput.classList.add('is-invalid');
      errorDiv.textContent = @json(__('settings.api_key_required'));
      return;
    }

    // Disable button, show spinner
    btn.disabled = true;
    btnSpinner.style.display = '';

    const token = document.querySelector('meta[name="csrf-token"]');
    const formData = new FormData(resendForm);

    fetch("{{ route('settings.system_mail_update') }}", {
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
