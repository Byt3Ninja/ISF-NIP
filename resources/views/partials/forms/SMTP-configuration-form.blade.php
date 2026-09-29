<div>
  {{-- Provider Selector --}}
  <div class="mb-3">
    <label class="form-label">{{ __('settings.email_provider') }}</label>
    <select id="emailProviderSelect" class="form-select" name="email_provider">
      <option value="smtp" {{ ($Title->email_provider ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
      <option value="gmail" {{ ($Title->email_provider ?? '') === 'gmail' ? 'selected' : '' }}>Gmail SMTP</option>
      <option value="resend" {{ ($Title->email_provider ?? 'smtp') === 'resend' ? 'selected' : '' }}>Resend</option>
    </select>
  </div>

  {{-- SMTP Panel --}}
  <div
    id="smtp-fields"
    class="provider-panel"
    style="{{ in_array($Title->email_provider ?? 'smtp', ['resend', 'gmail'], true) ? 'display:none' : '' }}"
  >
    <form
      id="smtpForm"
      action="{{ route('settings.system_mail_update') }}"
      method="POST"
      enctype="multipart/form-data"
    >
      @csrf
      @method('PUT')

      <input type="hidden" name="id" value="{{ $Title->id }}" />
      <input type="hidden" name="email_provider" value="smtp" />

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="host">{{ __('settings.host') }}</label>
          <input
            id="host"
            type="text"
            class="form-control"
            name="host"
            value="{{ $Title->host }}"
            placeholder="e.g. smtp.mailtrap.io"
          />
        </div>

        <div class="col-md-3">
          <label class="form-label" for="Port">{{ __('settings.Port') }}</label>
          <input
            id="Port"
            type="text"
            class="form-control"
            name="Port"
            value="{{ $Title->Port }}"
            placeholder="e.g. 587"
          />
        </div>

        <div class="col-md-3">
          <label class="form-label" for="Encryption">{{ __('settings.Encryption') }}</label>
          <select id="Encryption" class="form-select" name="Encryption">
            <option value="tls" {{ $Title->Encryption == 'tls' ? 'selected' : '' }}>TLS</option>
            <option value="ssl" {{ $Title->Encryption == 'ssl' ? 'selected' : '' }}>SSL</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label" for="Mail">{{ __('settings.Mail') }}</label>
          <input
            id="Mail"
            type="text"
            class="form-control"
            name="Mail"
            value="{{ $Title->Mail }}"
            placeholder="e.g. no-reply@example.com"
          />
        </div>

        <div class="col-md-6">
          <label class="form-label" for="Mailpassword">{{ __('settings.Mailpassword') }}</label>
          <input
            id="Mailpassword"
            type="password"
            class="form-control"
            name="Mailpassword"
            value="{{ $Title->Mailpassword }}"
            placeholder="e.g. your SMTP password"
            autocomplete="off"
          />
        </div>

        <div class="col-md-6">
          <label class="form-label" for="mail_from_address">{{ __('settings.mailSenderAddress') }}</label>
          <input
            id="mail_from_address"
            type="text"
            class="form-control"
            name="mail_from_address"
            value="{{ $Title->mail_from_address }}"
            placeholder="e.g. no-reply@example.com"
          />
        </div>

        <div class="col-md-6">
          <label class="form-label" for="mail_from_name">{{ __('settings.mailSenderName') }}</label>
          <input
            id="mail_from_name"
            type="text"
            class="form-control"
            name="mail_from_name"
            value="{{ $Title->mail_from_name }}"
            placeholder="Platform Name by default"
          />
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy"></i> {{ __('settings.Submit') }}
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- Gmail Panel --}}
  {{-- GmailTransport hardcodes smtp.gmail.com:587, so only credentials and the
       sender identity are configurable here. --}}
  <div
    id="gmail-fields"
    class="provider-panel"
    style="{{ ($Title->email_provider ?? '') !== 'gmail' ? 'display:none' : '' }}"
  >
    <form
      id="gmailForm"
      action="{{ route('settings.system_mail_update') }}"
      method="POST"
    >
      @csrf
      @method('PUT')

      <input type="hidden" name="id" value="{{ $Title->id }}" />
      <input type="hidden" name="email_provider" value="gmail" />
      <input type="hidden" name="host" value="smtp.gmail.com" />
      <input type="hidden" name="Port" value="587" />
      <input type="hidden" name="Encryption" value="tls" />

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="gmail_Mail">{{ __('settings.Mail') }}</label>
          <input
            id="gmail_Mail"
            type="text"
            class="form-control"
            name="Mail"
            value="{{ $Title->Mail }}"
            placeholder="e.g. you@gmail.com"
          />
        </div>

        <div class="col-md-6">
          <label class="form-label" for="gmail_Mailpassword">{{ __('settings.Mailpassword') }}</label>
          <input
            id="gmail_Mailpassword"
            type="password"
            class="form-control"
            name="Mailpassword"
            value="{{ $Title->Mailpassword }}"
            placeholder="Gmail App Password"
            autocomplete="off"
          />
        </div>

        <div class="col-md-6">
          <label class="form-label" for="gmail_from_address">{{ __('settings.mailSenderAddress') }}</label>
          <input
            id="gmail_from_address"
            type="text"
            class="form-control"
            name="mail_from_address"
            value="{{ $Title->mail_from_address }}"
            placeholder="e.g. you@gmail.com"
          />
        </div>

        <div class="col-md-6">
          <label class="form-label" for="gmail_from_name">{{ __('settings.mailSenderName') }}</label>
          <input
            id="gmail_from_name"
            type="text"
            class="form-control"
            name="mail_from_name"
            value="{{ $Title->mail_from_name }}"
            placeholder="Platform Name by default"
          />
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy"></i> {{ __('settings.Submit') }}
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- Resend Panel --}}
  <div
    id="resend-fields"
    class="provider-panel"
    style="{{ ($Title->email_provider ?? 'smtp') !== 'resend' ? 'display:none' : '' }}"
  >
    <form id="resendForm">
      @csrf

      <input type="hidden" name="id" value="{{ $Title->id }}" />
      <input type="hidden" name="email_provider" value="resend" />

      {{-- API Key Status Alert --}}
      <div id="resendStatusMessage" class="alert alert-dismissible fade show mb-3" role="alert" style="display: none">
        <span id="resendStatusText"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label" for="resend_api_key">
            {{ __('settings.resend_api_key') }}
          </label>
          <input
            id="resend_api_key"
            type="password"
            class="form-control"
            name="resend_api_key"
            value="{{ $Title->resend_api_key ?? '' }}"
            maxlength="64"
            placeholder="e.g. re_xxxxxxxxxxxxxxxxxxxx"
            autocomplete="off"
          />
        </div>

        <div class="col-12">
          <button type="submit" id="resendSaveBtn" class="btn btn-primary">
            <span id="resendBtnText">
              <i class="ti ti-device-floppy"></i> {{ __('settings.connect_and_save') }}
            </span>
            <span
              id="resendBtnSpinner"
              class="spinner-border spinner-border-sm ms-1"
              role="status"
              style="display: none"
            ></span>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

@push('scripts')
  <script>
    $(function () {
      // ─────────────────────────────────────────────────────────────
      // Provider Toggle — Select
      // ─────────────────────────────────────────────────────────────
      $('#emailProviderSelect').on('change', function () {
        const provider = $(this).val();

        $('.provider-panel').hide();
        $(`#${provider}-fields`).show();
      });

      // ─────────────────────────────────────────────────────────────
      // SMTP Form — FormValidation
      // ─────────────────────────────────────────────────────────────
      FormValidation.formValidation(document.getElementById('smtpForm'), {
        fields: {
          host: {
            validators: {
              notEmpty: { message: @json(__('admin.Required_Field')) },
            },
          },
          Mail: {
            validators: {
              notEmpty: { message: @json(__('admin.Required_Field')) },
              // emailAddress: { message: @json(__('admin.please_enter_valid_email')) },
            },
          },
          Mailpassword: {
            validators: {
              notEmpty: { message: @json(__('admin.Required_Field')) },
            },
          },
          Port: {
            validators: {
              notEmpty: { message: @json(__('admin.Required_Field')) },
              digits: { message: @json(__('admin.digits_only_validation')) },
            },
          },
          Encryption: {
            validators: {
              notEmpty: { message: @json(__('admin.Required_Field')) },
            },
          },
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: '.col-md-3, .col-md-6',
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
          autoFocus: new FormValidation.plugins.AutoFocus(),
        },
        init: (instance) => {
          instance.on('plugins.message.placed', function (e) {
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        },
      });

      // ─────────────────────────────────────────────────────────────
      // Resend Form — FormValidation + AJAX Submit
      // ─────────────────────────────────────────────────────────────
      const resendFv = FormValidation.formValidation(document.getElementById('resendForm'), {
        fields: {
          resend_api_key: {
            validators: {
              notEmpty: { message: @json(__('settings.api_key_required')) },
              stringLength: {
                max: 64,
                message: @json(__('settings.api_key_too_long')),
              },
              regexp: {
                regexp: /^re_[a-zA-Z0-9_]{10,}$/,
                message: @json(__('settings.invalid_resend_api_key_format') ?? 'Invalid API key format. Resend API keys must start with "re_" followed by at least 10 alphanumeric characters.'),
              },
            },
          },
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: '.col-md-6',
          }),
          autoFocus: new FormValidation.plugins.AutoFocus(),
        },
      });

      $('#resendForm').on('submit', function (e) {
        e.preventDefault();

        resendFv.validate().then(function (status) {
          if (status !== 'Valid') return;

          const $btn = $('#resendSaveBtn');
          const $spinner = $('#resendBtnSpinner');
          const $statusMsg = $('#resendStatusMessage');
          const $statusText = $('#resendStatusText');

          $statusMsg
            .hide()
            .removeClass('alert-success alert-danger')
            .addClass('alert-dismissible fade show');
          $statusText.text('');
          $btn.prop('disabled', true);
          $spinner.show();

          $.ajax({
            url: "{{ route('settings.system_mail_update') }}",
            method: 'POST',
            data: $('#resendForm').serialize() + '&_method=PUT',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              Accept: 'application/json',
            },
            success: function (data) {
              $statusText.text(data.message || @json(__('settings.email_config_saved')));
              $statusMsg
                .addClass('alert-success')
                .removeClass('alert-danger')
                .show();
            },
            error: function (xhr) {
              const data = xhr.responseJSON || {};
              const msg = data.message || @json(__('settings.email_config_failed'));

              // Mark the API key field as invalid when the key is rejected by Resend
              if (xhr.status === 422 && data.message === @json(__('settings.invalid_api_key'))) {
                resendFv.updateFieldStatus('resend_api_key', 'Invalid', 'notEmpty');
              }

              $statusText.text(msg);
              $statusMsg
                .addClass('alert-danger')
                .removeClass('alert-success')
                .show();
            },
            complete: function () {
              $btn.prop('disabled', false);
              $spinner.hide();

              // Auto-dismiss alert after 5 seconds
              setTimeout(function () {
                $statusMsg.fadeOut(300);
              }, 5000);
            },
          });
        });
      });
    });
  </script>
@endpush
