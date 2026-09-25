{{-- Email configuration (GV-1513 platform / GV-1514 organization). Shared by
     admin/settings/editegeneral and admin/organizations/settings; the data
     comes from App\Services\EmailSettingsStore::viewData(). --}}
@php
  $e = $emailConfig;
  $smtp = $e['smtp'];
  $saved = fn ($preset, $field, $default = '') => $e['preset'] === $preset ? ($smtp[$field] ?? $default) : $default;
  $statusBadges = [
    'resend'      => ['bg-label-success', 'email_status_resend'],
    'smtp_gmail'  => ['bg-label-success', 'email_status_smtp_gmail'],
    'smtp_resend' => ['bg-label-success', 'email_status_smtp_resend'],
    'smtp_custom' => ['bg-label-success', 'email_status_smtp_custom'],
  ];
  $badge = $statusBadges[$e['active']] ?? ($e['scope'] === 'organization'
    ? ['bg-label-info', 'email_status_platform']
    : ['bg-label-secondary', 'email_status_none']);
  $ecText = collect([
    'email_status_resend', 'email_status_smtp_gmail', 'email_status_smtp_resend', 'email_status_smtp_custom',
    'email_status_platform', 'email_status_none', 'field_required', 'invalid_email', 'invalid_port',
    'api_key_required', 'api_key_too_long', 'domain_required', 'domain_invalid', 'sender_local_invalid',
    'email_config_connect_failed', 'email_key_receipt', 'email_saved_key_receipt', 'email_domain_receipt',
    'email_step3_body', 'email_test_receipt', 'email_choose_domain', 'email_how_verify',
    'email_domain_status_verified', 'email_domain_status_pending', 'email_domain_status_not_started',
    'email_domain_status_failed', 'email_domain_status_partially_verified', 'email_domain_status_partially_failed',
    'email_domain_status_temporary_failure', 'email_no_domains', 'api_key_connected',
  ])->mapWithKeys(fn ($key) => [$key => __('settings.' . $key)]);
  $ecJs = [
    'scope'     => $e['scope'],
    'active'    => $e['active'],
    'recipient' => $e['recipient'],
    'routes'    => $e['routes'],
    'resend'    => $e['resend'],
    'csrf'      => csrf_token(),
  ];
@endphp

<div id="ec-root" class="email-config">
  @if ($e['failure'])
    <div class="alert alert-warning ec-alert mb-6" role="alert">
      <i class="ti ti-alert-triangle ti-md"></i>
      <div>
        {{ __('settings.org_email_failing') }}
        @if ($e['failure']['at'])
          <small class="d-block mt-1">{{ $e['failure']['at']->diffForHumans() }}</small>
        @endif
        <small class="d-block text-body mt-1">{{ $e['failure']['error'] }}</small>
      </div>
    </div>
  @endif

  <div id="ec-org-note" class="alert alert-info ec-alert mb-6" role="note" @if ($e['scope'] !== 'organization' || $e['active']) hidden @endif>
    <i class="ti ti-info-circle ti-md"></i>
    <span>{{ __('settings.email_org_note') }}</span>
  </div>

  <div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <h6 class="mb-0" id="ec-provider-label">{{ __('settings.email_choose_provider') }}</h6>
      <span id="ec-status" class="badge {{ $badge[0] }}" role="status"><span class="ec-dot"></span>{{ __('settings.' . $badge[1]) }}</span>
    </div>
    <div class="ec-options" role="radiogroup" aria-labelledby="ec-provider-label">
      <label class="ec-option" for="ec-provider-smtp">
        <input type="radio" name="ec_provider" id="ec-provider-smtp" value="smtp" @checked($e['provider'] === 'smtp')>
        <span class="ec-option-icon"><i class="ti ti-server-2"></i></span>
        <span>
          <span class="ec-option-title">{{ __('settings.email_opt_smtp') }}</span>
          <small class="d-block">{{ __('settings.email_opt_smtp_desc') }}</small>
        </span>
        <span class="ec-option-radio"></span>
      </label>
      <label class="ec-option" for="ec-provider-resend">
        <input type="radio" name="ec_provider" id="ec-provider-resend" value="resend" @checked($e['provider'] === 'resend')>
        <span class="ec-option-icon"><i class="ti ti-plug-connected"></i></span>
        <span>
          <span class="ec-option-title">Resend</span>
          <small class="d-block">{{ __('settings.email_opt_resend_desc') }}</small>
        </span>
        <span class="ec-option-radio"></span>
      </label>
    </div>
  </div>

  <hr class="my-6">

  {{-- ───────────── SMTP ───────────── --}}
  <section id="ec-pane-smtp" @if ($e['provider'] !== 'smtp') hidden @endif>
    <form id="ec-smtp-form" novalidate>
      <div class="mb-6">
        <span class="form-label d-block mb-2" id="ec-preset-label">{{ __('settings.email_smtp_provider') }}</span>
        <div class="ec-seg" role="radiogroup" aria-labelledby="ec-preset-label">
          <input type="radio" class="btn-check" name="smtp_preset" id="ec-preset-gmail" value="gmail" @checked($e['preset'] === 'gmail')>
          <label for="ec-preset-gmail"><i class="ti ti-brand-gmail"></i>Gmail</label>
          <input type="radio" class="btn-check" name="smtp_preset" id="ec-preset-resend" value="resend" @checked($e['preset'] === 'resend')>
          <label for="ec-preset-resend"><i class="ti ti-send"></i>Resend</label>
          <input type="radio" class="btn-check" name="smtp_preset" id="ec-preset-custom" value="custom" @checked($e['preset'] === 'custom')>
          <label for="ec-preset-custom"><i class="ti ti-adjustments-horizontal"></i>{{ __('settings.email_preset_custom') }}</label>
        </div>
      </div>

      {{-- Gmail --}}
      <div class="ec-preset" data-preset="gmail" @if ($e['preset'] !== 'gmail') hidden @endif>
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label" for="ec-gmail-username">{{ __('settings.email_gmail_address') }} <span class="text-danger">*</span></label>
            <input id="ec-gmail-username" name="username" type="email" class="form-control" data-rule="email" maxlength="191"
              value="{{ $saved('gmail', 'username') }}" placeholder="name@gmail.com" autocomplete="off">
            <div class="form-text">{{ __('settings.email_gmail_from_note') }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="ec-gmail-password">{{ __('settings.email_app_password') }} <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge form-password-toggle">
              <input id="ec-gmail-password" name="password" type="password" class="form-control" data-rule="required" maxlength="191"
                value="{{ $saved('gmail', 'password') }}" placeholder="xxxx xxxx xxxx xxxx" autocomplete="new-password">
              <span class="input-group-text cursor-pointer" title="{{ __('settings.email_show_password') }}"><i class="ti ti-eye-off"></i></span>
            </div>
            <div class="form-text">
              <span>
                {{ __('settings.email_app_password_help') }}
                <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">{{ __('settings.email_app_password_link') }}</a>
              </span>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="ec-gmail-from-name">{{ __('settings.email_sender_name') }}</label>
            <input id="ec-gmail-from-name" name="from_name" type="text" class="form-control" maxlength="191" value="{{ $saved('gmail', 'from_name') }}">
          </div>
        </div>
        @include('partials.forms.email-configuration-fixed', ['label' => __('settings.email_fixed_gmail'), 'values' => $e['presets']['gmail']])
        <div class="alert alert-warning ec-alert mt-4 mb-0" role="note">
          <i class="ti ti-alert-triangle ti-md"></i><span>{{ __('settings.email_gmail_limit') }}</span>
        </div>
      </div>

      {{-- Resend over SMTP --}}
      <div class="ec-preset" data-preset="resend" @if ($e['preset'] !== 'resend') hidden @endif>
        <div class="row g-4">
          <div class="col-12">
            <label class="form-label" for="ec-rsmtp-password">{{ __('settings.email_api_key') }} <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge form-password-toggle">
              <input id="ec-rsmtp-password" name="password" type="password" class="form-control ec-mono-input" data-rule="required" maxlength="64"
                value="{{ $saved('resend', 'password') }}" placeholder="re_xxxxxxxxxxxxxxxx" autocomplete="off" dir="ltr">
              <span class="input-group-text cursor-pointer" title="{{ __('settings.email_show_password') }}"><i class="ti ti-eye-off"></i></span>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="ec-rsmtp-from">{{ __('settings.email_sender_email') }} <span class="text-danger">*</span></label>
            <input id="ec-rsmtp-from" name="from_address" type="email" class="form-control" data-rule="email" maxlength="191"
              value="{{ $saved('resend', 'from_address') }}" placeholder="no-reply@yourdomain.com">
            <div class="form-text">{{ __('settings.email_resend_sender_note') }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="ec-rsmtp-from-name">{{ __('settings.email_sender_name') }}</label>
            <input id="ec-rsmtp-from-name" name="from_name" type="text" class="form-control" maxlength="191" value="{{ $saved('resend', 'from_name') }}">
          </div>
        </div>
        @include('partials.forms.email-configuration-fixed', ['label' => __('settings.email_fixed_resend'), 'values' => $e['presets']['resend']])
        <p class="mt-4 mb-0">
          {{ __('settings.email_resend_guided') }}
          <button type="button" class="btn btn-link p-0 align-baseline" id="ec-go-resend">{{ __('settings.email_resend_guided_link') }}</button>
        </p>
      </div>

      {{-- Custom server --}}
      <div class="ec-preset" data-preset="custom" @if ($e['preset'] !== 'custom') hidden @endif>
        @php
          $encryption = $e['preset'] === 'custom' ? ($smtp['encryption'] ?: 'none') : 'tls';
        @endphp
        <div class="row g-4">
          <div class="col-md-6">
            <label class="form-label" for="ec-custom-host">{{ __('settings.email_smtp_host') }} <span class="text-danger">*</span></label>
            <input id="ec-custom-host" name="host" type="text" class="form-control" data-rule="required" maxlength="191" dir="ltr"
              value="{{ $saved('custom', 'host') }}" placeholder="smtp.yourprovider.com">
          </div>
          <div class="col-md-3 col-6">
            <label class="form-label" for="ec-custom-encryption">{{ __('settings.email_encryption') }}</label>
            <select id="ec-custom-encryption" name="encryption" class="form-select">
              <option value="tls" @selected($encryption === 'tls')>TLS</option>
              <option value="ssl" @selected($encryption === 'ssl')>SSL</option>
              <option value="none" @selected($encryption === 'none')>{{ __('settings.email_encryption_none') }}</option>
            </select>
          </div>
          <div class="col-md-3 col-6">
            <label class="form-label" for="ec-custom-port">{{ __('settings.email_port') }} <span class="text-danger">*</span></label>
            <input id="ec-custom-port" name="port" type="text" inputmode="numeric" class="form-control" data-rule="port" dir="ltr"
              value="{{ $saved('custom', 'port', '587') }}">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="ec-custom-username">{{ __('settings.email_username') }} <span class="text-danger">*</span></label>
            <input id="ec-custom-username" name="username" type="text" class="form-control" data-rule="required" maxlength="191"
              value="{{ $saved('custom', 'username') }}" autocomplete="off">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="ec-custom-password">{{ __('settings.email_password') }} <span class="text-danger">*</span></label>
            <div class="input-group input-group-merge form-password-toggle">
              <input id="ec-custom-password" name="password" type="password" class="form-control" data-rule="required" maxlength="191"
                value="{{ $saved('custom', 'password') }}" autocomplete="new-password">
              <span class="input-group-text cursor-pointer" title="{{ __('settings.email_show_password') }}"><i class="ti ti-eye-off"></i></span>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="ec-custom-from">{{ __('settings.email_sender_email') }} <span class="text-danger">*</span></label>
            <input id="ec-custom-from" name="from_address" type="email" class="form-control" data-rule="email" maxlength="191"
              value="{{ $saved('custom', 'from_address') }}" placeholder="no-reply@yourdomain.com">
          </div>
          <div class="col-md-6">
            <label class="form-label" for="ec-custom-from-name">{{ __('settings.email_sender_name') }}</label>
            <input id="ec-custom-from-name" name="from_name" type="text" class="form-control" maxlength="191" value="{{ $saved('custom', 'from_name') }}">
          </div>
        </div>
      </div>

      <div id="ec-smtp-result" class="mt-6" hidden></div>

      <div class="d-flex flex-wrap gap-3 mt-6">
        <button type="submit" class="btn btn-primary" id="ec-smtp-save">
          <i class="ti ti-device-floppy me-2"></i><span>{{ __('settings.email_save_settings') }}</span>
        </button>
        <button type="button" class="btn btn-outline-primary" id="ec-smtp-test">
          <i class="ti ti-mail-forward me-2"></i><span>{{ __('settings.email_send_test') }}</span>
        </button>
      </div>
    </form>
  </section>

  {{-- ───────────── Resend (API) ───────────── --}}
  <section id="ec-pane-resend" @if ($e['provider'] !== 'resend') hidden @endif>
    <div id="ec-connected" class="alert alert-success ec-alert mb-6" role="status" hidden>
      <i class="ti ti-circle-check ti-md"></i><span>{{ __('settings.email_config_connected') }}</span>
    </div>

    <ol class="ec-ladder">
      {{-- Step 1: API key --}}
      <li class="ec-step" id="ec-st1">
        <span class="ec-marker"><span class="ec-num">1</span><i class="ti ti-check"></i></span>
        <div class="ec-step-main">
          <h6 class="ec-step-title">{{ __('settings.email_step1_title') }}</h6>
          <div data-open>
            <div class="ec-step-sub">{{ __('settings.email_step1_hint') }}</div>
            <div class="ec-step-body">
              <label class="form-label" for="ec-api-key">{{ __('settings.email_api_key') }} <span class="text-danger">*</span></label>
              <div class="ec-row">
                <div>
                  <div class="input-group input-group-merge form-password-toggle">
                    <input id="ec-api-key" name="api_key" type="password" class="form-control ec-mono-input" maxlength="64"
                      placeholder="re_xxxxxxxxxxxxxxxx" autocomplete="off" dir="ltr">
                    <span class="input-group-text cursor-pointer" title="{{ __('settings.email_show_password') }}"><i class="ti ti-eye-off"></i></span>
                  </div>
                </div>
                <button type="button" class="btn btn-primary" id="ec-connect">
                  <i class="ti ti-plug-connected me-2"></i><span>{{ __('settings.email_connect') }}</span>
                </button>
              </div>
              <div id="ec-st1-alert" class="mt-3" hidden></div>
            </div>
          </div>
          <div data-receipt hidden>
            <div class="ec-receipt"><span id="ec-key-receipt"></span><button type="button" class="btn btn-link p-0" data-ec-change="1">{{ __('settings.email_change') }}</button></div>
            <div class="ec-inline-ok" id="ec-key-ok" hidden><i class="ti ti-circle-check"></i><span>{{ __('settings.api_key_connected') }}</span></div>
          </div>
        </div>
      </li>

      {{-- Step 2: domain + sender --}}
      <li class="ec-step" id="ec-st2">
        <span class="ec-marker"><span class="ec-num">2</span><i class="ti ti-check"></i></span>
        <div class="ec-step-main">
          <h6 class="ec-step-title">{{ __('settings.email_step2_title') }}</h6>
          <div class="ec-step-sub" data-locked>{{ __('settings.email_step2_locked') }}</div>
          <div data-open hidden>
            <div class="ec-step-body">
              <div id="ec-no-domains" class="alert alert-info ec-alert mb-4" hidden>
                <i class="ti ti-info-circle ti-md"></i><span>{{ __('settings.email_no_domains') }}</span>
              </div>

              <div id="ec-domain-pick">
                <label class="form-label" for="ec-domain-select">{{ __('settings.email_domain') }} <span class="text-danger">*</span></label>
                <div class="ec-row ec-row-center">
                  <div><select id="ec-domain-select" name="domain" class="form-select" dir="ltr"></select></div>
                  <span id="ec-domain-status"></span>
                </div>
                <button type="button" class="btn btn-link p-0 mt-2" id="ec-add-mode"><i class="ti ti-plus me-1"></i>{{ __('settings.email_add_domain') }}</button>
              </div>

              <div id="ec-domain-add" hidden>
                <label class="form-label" for="ec-domain-input">{{ __('settings.email_domain') }} <span class="text-danger">*</span></label>
                <div class="ec-row">
                  <div><input id="ec-domain-input" name="domain" type="text" class="form-control ec-mono-input" maxlength="255" dir="ltr"
                    placeholder="{{ __('settings.email_domain_placeholder') }}"></div>
                  <button type="button" class="btn btn-label-primary" id="ec-add-domain">
                    <i class="ti ti-world-plus me-2"></i><span>{{ __('settings.email_add_domain') }}</span>
                  </button>
                </div>
                <button type="button" class="btn btn-link p-0 mt-2" id="ec-pick-mode">{{ __('settings.email_pick_existing') }}</button>
              </div>

              <div id="ec-sender" class="row g-4 mt-0" hidden>
                <div class="col-md-6">
                  <label class="form-label" for="ec-from-local">{{ __('settings.email_send_from') }} <span class="text-danger">*</span></label>
                  <div class="input-group" dir="ltr">
                    <input id="ec-from-local" name="from_local" type="text" class="form-control" maxlength="64" value="{{ $e['resend']['from_local'] }}">
                    <span class="input-group-text ec-mono-input" id="ec-from-domain">&#64;</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="ec-from-name">{{ __('settings.email_sender_name') }}</label>
                  <input id="ec-from-name" name="from_name" type="text" class="form-control" maxlength="191" value="{{ $e['resend']['from_name'] }}">
                </div>
              </div>
              <div id="ec-st2-alert" class="mt-3" hidden></div>
            </div>
          </div>
          <div data-receipt hidden>
            <div class="ec-receipt"><span id="ec-domain-receipt"></span><button type="button" class="btn btn-link p-0" data-ec-change="2">{{ __('settings.email_change') }}</button></div>
          </div>
        </div>
      </li>

      {{-- Step 3: test email --}}
      <li class="ec-step" id="ec-st3">
        <span class="ec-marker"><span class="ec-num">3</span><i class="ti ti-check"></i></span>
        <div class="ec-step-main">
          <h6 class="ec-step-title">{{ __('settings.email_step3_title') }}</h6>
          <div class="ec-step-sub" data-locked>{{ __('settings.email_step3_locked') }}</div>
          <div data-open hidden>
            <div class="ec-step-body">
              <p class="mb-4" id="ec-test-intro"></p>
              <div id="ec-test-result" class="mb-4" hidden></div>
              <button type="button" class="btn btn-primary" id="ec-send-test">
                <i class="ti ti-send me-2"></i><span>{{ __('settings.email_send_test_connect') }}</span>
              </button>
            </div>
          </div>
          <div data-receipt hidden>
            <div class="ec-receipt"><span id="ec-test-receipt"></span><button type="button" class="btn btn-link p-0" id="ec-retest">{{ __('settings.email_send_test') }}</button></div>
          </div>
        </div>
      </li>
    </ol>

    <p class="ec-helper">
      <i class="ti ti-info-circle"></i>
      <span>
        {{ __('settings.email_helper_smtp') }}
        <button type="button" class="btn btn-link p-0 align-baseline" id="ec-use-smtp">{{ __('settings.email_helper_smtp_link') }}</button>
      </span>
    </p>
  </section>
</div>

{{-- How to verify your domain --}}
<div class="modal fade" id="ec-verify-modal" tabindex="-1" aria-labelledby="ec-verify-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ec-verify-title">{{ __('settings.email_verify_title') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('settings.email_close') }}"></button>
      </div>
      <div class="modal-body">
        <video id="ec-verify-video" class="w-100 rounded" controls preload="none" src="{{ $e['video_url'] }}"></video>
        <ol class="mt-4 mb-0 ps-4">
          <li>{{ __('settings.email_verify_s1') }}</li>
          <li class="mt-1">{{ __('settings.email_verify_s2') }}</li>
          <li class="mt-1">{{ __('settings.email_verify_s3') }}</li>
        </ol>
      </div>
      <div class="modal-footer">
        <a class="btn btn-outline-primary" href="https://resend.com/domains" target="_blank" rel="noopener">
          <i class="ti ti-external-link me-2"></i>{{ __('settings.email_open_resend') }}
        </a>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ __('settings.email_close') }}</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<style>
  .email-config .ec-alert { display: flex; gap: .5rem; }
  .email-config .ec-dot { display: inline-block; width: .45rem; height: .45rem; border-radius: 50%; background: currentColor; margin-inline-end: .4rem; vertical-align: middle; }
  .email-config .ec-mono-input, .email-config .ec-mono { font-family: var(--bs-font-monospace); font-size: .8125rem; }
  .email-config .ec-mono { direction: ltr; unicode-bidi: isolate; color: var(--bs-heading-color); font-weight: 500; }

  .email-config .ec-options { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
  .email-config .ec-option { position: relative; display: grid; grid-template-columns: 2.5rem 1fr 1.25rem; gap: .75rem; align-items: start; padding: 1rem; border: 1px solid var(--bs-border-color); border-radius: var(--bs-border-radius); cursor: pointer; margin: 0; transition: border-color .15s; }
  .email-config .ec-option:hover { border-color: rgba(var(--bs-primary-rgb), .5); }
  .email-config .ec-option input { position: absolute; opacity: 0; pointer-events: none; }
  .email-config .ec-option-icon { width: 2.5rem; height: 2.5rem; display: grid; place-items: center; border-radius: var(--bs-border-radius); background: var(--bs-gray-50); color: var(--bs-heading-color); font-size: 1.375rem; }
  .email-config .ec-option-title { display: block; font-weight: 500; color: var(--bs-heading-color); }
  .email-config .ec-option-radio { width: 1.25rem; height: 1.25rem; margin-top: .125rem; border-radius: 50%; border: 2px solid var(--bs-border-color); }
  .email-config .ec-option:has(input:checked) { border: 2px solid var(--bs-primary); padding: calc(1rem - 1px); }
  .email-config .ec-option:has(input:checked) .ec-option-icon { background: rgba(var(--bs-primary-rgb), .16); color: var(--bs-primary); }
  .email-config .ec-option:has(input:checked) .ec-option-radio { border: .375rem solid var(--bs-primary); }
  .email-config .ec-option:has(input:focus-visible) { outline: 2px solid rgba(var(--bs-primary-rgb), .5); outline-offset: 2px; }

  .email-config .ec-seg { display: inline-flex; flex-wrap: wrap; gap: .25rem; padding: .25rem; border-radius: .5rem; background: var(--bs-gray-50); }
  .email-config .ec-seg label { display: inline-flex; align-items: center; gap: .5rem; margin: 0; padding: .375rem .875rem; border-radius: var(--bs-border-radius); cursor: pointer; font-weight: 500; color: var(--bs-body-color); }
  .email-config .ec-seg label i { font-size: 1.125rem; }
  .email-config .ec-seg .btn-check:checked + label { background: var(--bs-card-bg, var(--bs-body-bg)); color: var(--bs-primary); box-shadow: var(--bs-box-shadow-sm); }
  .email-config .ec-seg .btn-check:focus-visible + label { outline: 2px solid rgba(var(--bs-primary-rgb), .5); }

  .email-config .ec-fixed { margin-top: 1.5rem; padding: .75rem 1rem; border: 1px dashed var(--bs-border-color); border-radius: var(--bs-border-radius); }
  .email-config .ec-fixed-head { display: flex; align-items: center; gap: .375rem; margin-bottom: .5rem; font-size: .8125rem; }
  .email-config .ec-fixed dl { display: grid; grid-template-columns: repeat(auto-fit, minmax(7.5rem, 1fr)); gap: .5rem 1.25rem; margin: 0; }
  .email-config .ec-fixed dt { font-weight: 400; font-size: .75rem; color: var(--bs-secondary-color); }
  .email-config .ec-fixed dd { margin: .125rem 0 0; }

  .email-config .ec-ladder { list-style: none; margin: 0; padding: 0; }
  .email-config .ec-step { position: relative; display: grid; grid-template-columns: 2.125rem minmax(0, 1fr); gap: 1rem; padding-bottom: 1.625rem; }
  .email-config .ec-step:last-child { padding-bottom: 0; }
  .email-config .ec-step::before { content: ""; position: absolute; inset-inline-start: 1rem; top: 2.5rem; bottom: .375rem; width: 2px; border-radius: 2px; background: var(--bs-border-color); }
  .email-config .ec-step:last-child::before { display: none; }
  .email-config .ec-step.is-done::before { background: rgba(var(--bs-success-rgb), .5); }
  .email-config .ec-marker { width: 2.125rem; height: 2.125rem; display: grid; place-items: center; border-radius: 50%; border: 2px solid var(--bs-border-color); color: var(--bs-secondary-color); font-weight: 600; font-size: .875rem; }
  .email-config .ec-marker i { font-size: 1.125rem; }
  .email-config .ec-step.is-active .ec-marker { background: var(--bs-primary); border-color: var(--bs-primary); color: #fff; box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), .16); }
  .email-config .ec-step.is-done .ec-marker { background: rgba(var(--bs-success-rgb), .16); border-color: transparent; color: var(--bs-success); }
  .email-config .ec-step.is-done .ec-num, .email-config .ec-step:not(.is-done) .ec-marker i { display: none; }
  .email-config .ec-step-title { margin: .3rem 0 0; }
  .email-config .ec-step.is-locked .ec-step-title { color: var(--bs-secondary-color); }
  .email-config .ec-step-sub { margin-top: .125rem; font-size: .8125rem; color: var(--bs-secondary-color); }
  .email-config .ec-step-body { margin-top: .875rem; }
  .email-config .ec-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: .75rem; align-items: start; }
  .email-config .ec-row-center { align-items: center; }
  .email-config .ec-receipt { display: flex; flex-wrap: wrap; align-items: center; gap: .375rem .75rem; margin-top: .25rem; }
  .email-config .ec-inline-ok { display: flex; align-items: center; gap: .375rem; margin-top: .5rem; font-size: .8125rem; color: var(--bs-success); }
  .email-config .ec-helper { display: flex; gap: .5rem; margin: 1.5rem 0 0; padding-top: 1.125rem; border-top: 1px solid var(--bs-border-color); }
  .email-config .ec-helper > i { font-size: 1.125rem; margin-top: .125rem; }
  .email-config .ec-settle { animation: ec-settle .35s ease-out; }
  @keyframes ec-settle { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }

  @media (max-width: 575.98px) {
    .email-config .ec-options, .email-config .ec-row { grid-template-columns: 1fr; }
    .email-config .ec-seg { display: flex; }
    .email-config .ec-seg label { flex: 1; justify-content: center; padding: .375rem .5rem; }
    .email-config .ec-step { grid-template-columns: 1.875rem minmax(0, 1fr); gap: .75rem; }
    .email-config .ec-marker { width: 1.875rem; height: 1.875rem; }
    .email-config .ec-step::before { inset-inline-start: .875rem; top: 2.25rem; }
  }
  @media (prefers-reduced-motion: reduce) {
    .email-config .ec-settle { animation: none; }
  }
</style>
<script>
  $(function () {
    const root = document.getElementById('ec-root');
    if (!root) return;

    const cfg = @json($ecJs);
    const T = @json($ecText);
    const $root = $(root);

    const STATUS = {
      resend: ['bg-label-success', 'email_status_resend'],
      smtp_gmail: ['bg-label-success', 'email_status_smtp_gmail'],
      smtp_resend: ['bg-label-success', 'email_status_smtp_resend'],
      smtp_custom: ['bg-label-success', 'email_status_smtp_custom'],
    };
    const DOMAIN_BADGE = {
      verified: 'bg-label-success', pending: 'bg-label-warning', not_started: 'bg-label-secondary',
      failed: 'bg-label-danger', partially_verified: 'bg-label-warning', partially_failed: 'bg-label-danger',
      temporary_failure: 'bg-label-warning',
    };
    const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const DOMAIN_RE = /^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))*\.[a-z]{2,63}$/i;
    const LOCAL_RE = /^[A-Za-z0-9._%+-]+$/;

    const esc = (value) => $('<div>').text(value ?? '').html();
    const t = (key, vars = {}) => Object.entries(vars).reduce((text, [name, value]) => text.replace(':' + name, value), T[key] ?? key);
    const mask = (key) => key.slice(0, 3) + '••••••' + key.slice(-4);
    const mono = (value) => '<span class="ec-mono">' + esc(value) + '</span>';

    let active = cfg.active;
    let preset = $root.find('input[name=smtp_preset]:checked').val() || 'gmail';

    // ── Shared helpers ───────────────────────────────────────────
    function renderStatus() {
      const [cls, key] = STATUS[active] || (cfg.scope === 'organization'
        ? ['bg-label-info', 'email_status_platform']
        : ['bg-label-secondary', 'email_status_none']);
      $('#ec-status').attr('class', 'badge ' + cls).html('<span class="ec-dot"></span>' + esc(t(key)));
      $('#ec-org-note').prop('hidden', cfg.scope !== 'organization' || !!active);
    }

    function fieldError($input, message) {
      $input.toggleClass('is-invalid', !!message);
      const $host = $input.closest('.input-group').length ? $input.closest('.input-group') : $input;
      let $feedback = $host.next('.ec-feedback');
      if (!$feedback.length) {
        $feedback = $('<div class="invalid-feedback ec-feedback d-block"></div>').insertAfter($host);
      }
      $feedback.text(message || '').prop('hidden', !message);
    }

    function clearErrors($scope) {
      $scope.find('.is-invalid').removeClass('is-invalid');
      $scope.find('.ec-feedback').prop('hidden', true);
    }

    // Puts the server's field errors on the matching inputs; false when none matched.
    function showFieldErrors($scope, errors) {
      let $first = null;
      Object.entries(errors || {}).forEach(([name, messages]) => {
        const $input = $scope.find('[name="' + name + '"]').filter(':visible').first();
        if ($input.length) {
          fieldError($input, messages[0]);
          $first = $first || $input;
        }
      });
      if ($first) $first.trigger('focus');
      return !!$first;
    }

    function showAlert($box, kind, icon, html, extra = '') {
      $box.html('<div class="alert alert-' + kind + ' ec-alert mb-0 ec-settle" role="alert"><i class="ti ' + icon + ' ti-md"></i><div>' + html + extra + '</div></div>')
        .prop('hidden', false);
    }

    function busy(button, on) {
      const $button = $(button);
      $button.prop('disabled', on);
      $button.find('.ec-spinner').remove();
      $button.find('i').first().toggleClass('d-none', on);
      if (on) $button.prepend('<span class="spinner-border spinner-border-sm me-2 ec-spinner" aria-hidden="true"></span>');
    }

    function request(url, data, method = 'POST') {
      return $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        headers: { Accept: 'application/json' },
        data: Object.assign({ _token: cfg.csrf, _method: method }, data),
      });
    }

    const failMessage = (xhr) => (xhr.responseJSON && xhr.responseJSON.message) || T.email_config_connect_failed;

    // ── Provider + preset ────────────────────────────────────────
    function setProvider(provider) {
      $root.find('input[name=ec_provider]').each(function () { this.checked = this.value === provider; });
      $('#ec-pane-smtp').prop('hidden', provider !== 'smtp');
      $('#ec-pane-resend').prop('hidden', provider !== 'resend');
    }

    function setPreset(value) {
      preset = value;
      $root.find('input[name=smtp_preset]').each(function () { this.checked = this.value === value; });
      $root.find('.ec-preset').each(function () { this.hidden = this.dataset.preset !== value; });
      $('#ec-smtp-result').prop('hidden', true);
    }

    $root.on('change', 'input[name=ec_provider]', function () { setProvider(this.value); });
    $root.on('change', 'input[name=smtp_preset]', function () { setPreset(this.value); });
    $('#ec-go-resend').on('click', () => setProvider('resend'));
    $('#ec-use-smtp').on('click', () => { setProvider('smtp'); setPreset('resend'); $('#ec-rsmtp-password').trigger('focus'); });

    $('#ec-custom-encryption').on('change', function () {
      const $port = $('#ec-custom-port');
      if (['', '25', '465', '587'].includes($port.val().trim())) {
        $port.val({ tls: '587', ssl: '465', none: '25' }[this.value]);
      }
    });

    // ── SMTP ─────────────────────────────────────────────────────
    const $smtpPane = () => $root.find('.ec-preset[data-preset="' + preset + '"]');

    function validateSmtp() {
      clearErrors($smtpPane());
      let $first = null;
      $smtpPane().find('[data-rule]').each(function () {
        const value = this.value.trim();
        let message = '';
        if (!value) message = T.field_required;
        else if (this.dataset.rule === 'email' && !EMAIL_RE.test(value)) message = T.invalid_email;
        else if (this.dataset.rule === 'port' && !(/^\d{1,5}$/.test(value) && +value >= 1 && +value <= 65535)) message = T.invalid_port;
        if (message) {
          fieldError($(this), message);
          $first = $first || $(this);
        }
      });
      if ($first) $first.trigger('focus');
      return !$first;
    }

    function smtpPayload() {
      const data = { smtp_preset: preset };
      $smtpPane().find(':input[name]').each(function () { data[this.name] = this.value.trim(); });
      return data;
    }

    function smtpFailed(xhr) {
      const json = xhr.responseJSON || {};
      if (showFieldErrors($smtpPane(), json.errors)) return;
      const detail = json.detail ? '<small class="d-block mt-1 text-break">' + esc(json.detail) + '</small>' : '';
      showAlert($('#ec-smtp-result'), 'danger', 'ti-alert-circle', esc(failMessage(xhr)), detail);
    }

    $('#ec-smtp-form').on('submit', function (event) {
      event.preventDefault();
      if (!validateSmtp()) return;
      const button = document.getElementById('ec-smtp-save');
      $('#ec-smtp-result').prop('hidden', true);
      busy(button, true);
      request(cfg.routes.smtp_save, smtpPayload(), 'PUT')
        .done((response) => {
          active = response.active;
          R.connected = false;
          renderStatus();
          renderResend();
          toastr.success(response.message);
        })
        .fail(smtpFailed)
        .always(() => busy(button, false));
    });

    $('#ec-smtp-test').on('click', function () {
      if (!validateSmtp()) return;
      const button = this;
      $('#ec-smtp-result').prop('hidden', true);
      busy(button, true);
      request(cfg.routes.smtp_test, smtpPayload())
        .done((response) => showAlert($('#ec-smtp-result'), 'success', 'ti-circle-check', esc(response.message)))
        .fail(smtpFailed)
        .always(() => busy(button, false));
    });

    // ── Resend connect flow ──────────────────────────────────────
    const R = {
      key: '',                                   // key typed this session; empty = use the saved key
      keyHint: cfg.resend.key_hint,
      justConnected: false,
      domains: [],
      domainsLoaded: false,
      domain: cfg.resend.domain ? { name: cfg.resend.domain, status: null } : null,
      adding: false,
      connected: cfg.resend.connected,
    };
    R.step = (cfg.resend.key_hint && cfg.resend.domain) ? 3 : 1;

    const $step = (n) => $('#ec-st' + n);
    const fromAddress = () => ($('#ec-from-local').val().trim() || 'no-reply') + '@' + (R.domain ? R.domain.name : '');

    function renderResend() {
      const openStep2 = R.step === 2 || (R.step === 3 && !R.connected && R.domainsLoaded);
      [1, 2, 3].forEach((n) => {
        const done = n < R.step || (n === 3 && R.connected);
        const open = n === 2 ? openStep2 : (n === R.step && !R.connected);
        const $s = $step(n);
        $s.toggleClass('is-done', done).toggleClass('is-active', n === R.step && !done).toggleClass('is-locked', n > R.step);
        $s.find('[data-open]').prop('hidden', !open);
        $s.find('[data-receipt]').prop('hidden', !done || open);
        $s.find('[data-locked]').prop('hidden', n <= R.step);
      });
      $('#ec-connected').prop('hidden', !R.connected);

      const hint = R.key ? mask(R.key) : R.keyHint;
      if (hint) {
        const receiptKey = (R.key || R.connected) ? 'email_key_receipt' : 'email_saved_key_receipt';
        $('#ec-key-receipt').html(esc(t(receiptKey)).replace(':key', mono(hint)));
      }
      $('#ec-key-ok').prop('hidden', !R.justConnected);

      if (R.domain) {
        $('#ec-from-domain').text('@' + R.domain.name);
        $('#ec-domain-receipt').html(esc(t('email_domain_receipt')).replace(':from', mono(fromAddress())));
      }
      $('#ec-test-intro').html(esc(t('email_step3_body')).replace(':email', '<strong>' + esc(cfg.recipient) + '</strong>'));
      $('#ec-test-receipt').html(esc(t('email_test_receipt')).replace(':email', '<strong>' + esc(cfg.recipient) + '</strong>'));
      renderDomainStep();
    }

    function renderDomainStep() {
      const hasDomains = R.domains.length > 0;
      const adding = !hasDomains || R.adding;
      $('#ec-domain-pick').prop('hidden', adding);
      $('#ec-domain-add').prop('hidden', !adding);
      $('#ec-pick-mode').prop('hidden', !hasDomains);
      $('#ec-no-domains').prop('hidden', !R.domainsLoaded || hasDomains);

      const options = ['<option value="">' + esc(T.email_choose_domain) + '</option>'].concat(R.domains.map((d) =>
        '<option value="' + esc(d.name) + '"' + (R.domain && R.domain.name === d.name ? ' selected' : '') + '>' +
        esc(d.name) + ' (' + esc(T['email_domain_status_' + d.status] || d.status) + ')</option>'));
      $('#ec-domain-select').html(options.join(''));

      const status = R.domain && R.domain.status;
      $('#ec-domain-status').html(status
        ? '<span class="badge ' + (DOMAIN_BADGE[status] || 'bg-label-secondary') + '"><span class="ec-dot"></span>' + esc(T['email_domain_status_' + status] || status) + '</span>'
        : '');
      $('#ec-sender').prop('hidden', !R.domain);
    }

    function selectDomain(domain) {
      R.domain = domain;
      if (R.step >= 2) R.step = domain ? 3 : 2;
      $('#ec-test-result').prop('hidden', true);
      renderResend();
    }

    function loadDomains(apiKey) {
      return request(cfg.routes.resend_connect, { api_key: apiKey }).done((response) => {
        R.domains = response.domains || [];
        R.domainsLoaded = true;
        if (R.domain) {
          R.domain = R.domains.find((d) => d.name === R.domain.name) || null;
        }
      });
    }

    function resendFailed(xhr, $alertBox, $fieldsScope) {
      const json = xhr.responseJSON || {};
      if ($fieldsScope && showFieldErrors($fieldsScope, json.errors)) return;
      showAlert($alertBox, 'danger', 'ti-alert-circle', esc(failMessage(xhr)));
    }

    // Step 1
    $('#ec-connect').on('click', function () {
      const $input = $('#ec-api-key');
      const apiKey = $input.val().trim();
      $('#ec-st1-alert').prop('hidden', true);
      if (!apiKey) return fieldError($input, T.api_key_required), $input.trigger('focus');
      if (apiKey.length > 64) return fieldError($input, T.api_key_too_long), $input.trigger('focus');
      fieldError($input, '');

      const button = this;
      busy(button, true);
      $input.prop('readonly', true);
      R.domain = null;
      loadDomains(apiKey)
        .done(() => {
          R.key = apiKey;
          R.justConnected = true;
          R.adding = false;
          R.connected = false;
          R.step = 2;
          renderResend();
          $step(1).find('[data-receipt]').addClass('ec-settle');
          (R.domains.length ? $('#ec-domain-select') : $('#ec-domain-input')).trigger('focus');
        })
        .fail((xhr) => resendFailed(xhr, $('#ec-st1-alert'), $step(1)))
        .always(() => { busy(button, false); $input.prop('readonly', false); });
    });
    $('#ec-api-key').on('keydown', function (event) { if (event.key === 'Enter') { event.preventDefault(); $('#ec-connect').trigger('click'); } });

    // Step 2
    $('#ec-domain-select').on('change', function () {
      fieldError($(this), '');
      selectDomain(R.domains.find((d) => d.name === this.value) || null);
    });
    $('#ec-add-mode').on('click', () => { R.adding = true; renderDomainStep(); $('#ec-domain-input').trigger('focus'); });
    $('#ec-pick-mode').on('click', () => { R.adding = false; fieldError($('#ec-domain-input'), ''); renderDomainStep(); });

    $('#ec-add-domain').on('click', function () {
      const $input = $('#ec-domain-input');
      const domain = $input.val().trim().toLowerCase();
      $('#ec-st2-alert').prop('hidden', true);
      if (!domain) return fieldError($input, T.domain_required), $input.trigger('focus');
      if (domain.length > 255 || !DOMAIN_RE.test(domain)) return fieldError($input, T.domain_invalid), $input.trigger('focus');
      fieldError($input, '');

      const button = this;
      busy(button, true);
      request(cfg.routes.resend_domain, { api_key: R.key, domain: domain })
        .done((response) => {
          R.domains.push(response.domain);
          R.adding = false;
          $input.val('');
          toastr.success(response.message);
          selectDomain(response.domain);
        })
        .fail((xhr) => resendFailed(xhr, $('#ec-st2-alert'), $('#ec-domain-add')))
        .always(() => busy(button, false));
    });
    $('#ec-domain-input').on('keydown', function (event) { if (event.key === 'Enter') { event.preventDefault(); $('#ec-add-domain').trigger('click'); } });
    $('#ec-from-local').on('input', function () {
      fieldError($(this), '');
      if (R.domain) $('#ec-domain-receipt').html(esc(t('email_domain_receipt')).replace(':from', mono(fromAddress())));
    });

    // Step 3
    function sendTest(button, onSuccess) {
      const $local = $('#ec-from-local');
      const local = $local.val().trim();
      $('#ec-test-result').prop('hidden', true);
      if (!R.domain) {
        R.step = 2;
        renderResend();
        return fieldError($('#ec-domain-select'), T.domain_required);
      }
      if (!local || !LOCAL_RE.test(local)) {
        R.domainsLoaded = true;
        renderResend();
        return fieldError($local, local ? T.sender_local_invalid : T.field_required), $local.trigger('focus');
      }

      busy(button, true);
      request(cfg.routes.resend_test, {
        api_key: R.key,
        domain: R.domain.name,
        from_local: local,
        from_name: $('#ec-from-name').val().trim(),
      })
        .done((response) => {
          active = 'resend';
          R.connected = true;
          R.step = 3;
          if (R.key) R.keyHint = mask(R.key);
          renderStatus();
          renderResend();
          $('#ec-connected').removeClass('ec-settle').width();
          $('#ec-connected').addClass('ec-settle');
          if (onSuccess) onSuccess(response);
        })
        .fail((xhr) => {
          const json = xhr.responseJSON || {};
          if (json.code === 'domain_not_verified') {
            R.connected = false;
            renderResend();
            const action = '<div class="mt-2"><button type="button" class="btn btn-sm btn-outline-warning" data-ec-verify>' +
              '<i class="ti ti-player-play me-1"></i>' + esc(T.email_how_verify) + '</button></div>';
            return showAlert($('#ec-test-result'), 'warning', 'ti-alert-triangle', esc(json.message), action);
          }
          if (R.connected) {
            return toastr.error(failMessage(xhr));
          }
          resendFailed(xhr, $('#ec-test-result'), $('#ec-sender'));
        })
        .always(() => busy(button, false));
    }

    $('#ec-send-test').on('click', function () { sendTest(this); });
    $('#ec-retest').on('click', function () { sendTest(this, (response) => toastr.success(response.message)); });

    $root.on('click', '[data-ec-change]', function () {
      const target = +this.dataset.ecChange;
      $('#ec-test-result, #ec-st1-alert, #ec-st2-alert').prop('hidden', true);
      R.connected = false;
      R.justConnected = false;

      if (target === 1) {
        R.step = 1;
        R.key = '';
        R.domain = null;
        R.domains = [];
        R.domainsLoaded = false;
        R.adding = false;
        renderResend();
        $('#ec-api-key').val('').trigger('focus');
        return;
      }

      if (R.domainsLoaded) {
        R.step = R.domain ? 3 : 2;
        renderResend();
        return;
      }

      const button = this;
      busy(button, true);
      loadDomains(R.key)
        .done(() => {
          R.step = R.domain ? 3 : 2;
          renderResend();
          $('#ec-domain-select').trigger('focus');
        })
        .fail((xhr) => {
          R.connected = cfg.resend.connected && active === 'resend';
          renderResend();
          toastr.error(failMessage(xhr));
        })
        .always(() => busy(button, false));
    });

    // Verify-domain video
    const verifyModal = document.getElementById('ec-verify-modal');
    document.body.appendChild(verifyModal);
    $root.on('click', '[data-ec-verify]', () => bootstrap.Modal.getOrCreateInstance(verifyModal).show());
    verifyModal.addEventListener('hidden.bs.modal', () => document.getElementById('ec-verify-video').pause());

    renderStatus();
    renderResend();
  });
</script>
@endpush
