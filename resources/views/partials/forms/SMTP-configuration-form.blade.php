<div>
  {{-- Provider Selector --}}
  <div class="form-group mb-3">
    <label class="form-label mb-1">{{ __('settings.email_provider') }}</label>
    <select id="emailProviderSelect" class="form-control" name="email_provider">
      <option value="smtp" {{ ($Title->email_provider ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
      <option value="gmail" {{ ($Title->email_provider ?? 'gmail') === 'gmail' ? 'selected' : '' }}>Gmail SMTP</option>
      <option value="resend" {{ ($Title->email_provider ?? 'smtp') === 'resend' ? 'selected' : '' }}>Resend</option>
    </select>
  </div>

  {{-- SMTP Panel --}}
  <div
    id="smtp-fields"
    class="provider-panel"
    style="{{ ($Title->email_provider ?? 'smtp') === 'resend' ? 'display:none' : '' }}"
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

      <div class="form-content flex-grow-1">
        <div class="form-group mb-3">
          <label class="form-label mb-1" for="host">{{ __('settings.host') }}</label>
          <input
            id="host"
            type="text"
            class="form-control"
            name="host"
            value="{{ $Title->host }}"
            placeholder="e.g. smtp.mailtrap.io"
          />
        </div>

        <div class="form-group mb-3">
          <label class="form-label mb-1" for="Mail">{{ __('settings.Mail') }}</label>
          <input
            id="Mail"
            type="text"
            class="form-control"
            name="Mail"
            value="{{ $Title->Mail }}"
            placeholder="e.g. no-reply@example.com"
          />
        </div>

        <div class="form-group mb-3">
          <label class="form-label mb-1" for="Mailpassword">{{ __('settings.Mailpassword') }}</label>
          <input
            id="Mailpassword"
            type="text"
            class="form-control"
            name="Mailpassword"
            value="{{ $Title->Mailpassword }}"
            placeholder="e.g. your SMTP password"
            autocomplete="off"
          />
        </div>

        <div class="form-group mb-3">
          <label class="form-label mb-1" for="Port">{{ __('settings.Port') }}</label>
          <input
            id="Port"
            type="text"
            class="form-control"
            name="Port"
            value="{{ $Title->Port }}"
            placeholder="e.g. 587"
          />
        </div>

        <div class="form-group mb-3">
          <label class="form-label mb-1" for="Encryption">{{ __('settings.Encryption') }}</label>
          <select id="Encryption" class="form-control" name="Encryption">
            <option value="tls" {{ $Title->Encryption == 'tls' ? 'selected' : '' }}>TLS</option>
            <option value="ssl" {{ $Title->Encryption == 'ssl' ? 'selected' : '' }}>SSL</option>
          </select>
        </div>
      </div>

      <div class="form-group d-flex justify-content-end mt-auto">
        <button type="submit" class="btn btn-primary mr-1 mb-1">
          {{ __('settings.Submit') }}
        </button>
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

      <div class="form-content flex-grow-1">
        <div class="form-group mb-3">
          <label class="form-label mb-1" for="resend_api_key">
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
      </div>

      <div class="form-group d-flex justify-content-end mt-auto">
        <button type="submit" id="resendSaveBtn" class="btn btn-primary mr-1 mb-1">
          <span id="resendBtnText">{{ __('settings.connect_and_save') }}</span>
          <span
            id="resendBtnSpinner"
            class="spinner-border spinner-border-sm ms-1"
            role="status"
            style="display: none"
          ></span>
        </button>
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
              emailAddress: { message: @json(__('admin.please_enter_valid_email')) },
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
            rowSelector: '.mb-3',
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
            },
          },
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: '.form-group',
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