{{-- Read-only connection values an SMTP preset fills in (see EmailSettingsStore::PRESETS). --}}
<div class="ec-fixed">
  <div class="ec-fixed-head"><i class="ti ti-lock"></i><span>{{ $label }}</span></div>
  <dl>
    <div><dt>{{ __('settings.email_server') }}</dt><dd class="ec-mono">{{ $values['host'] }}</dd></div>
    <div><dt>{{ __('settings.email_port') }}</dt><dd class="ec-mono">{{ $values['port'] }}</dd></div>
    <div><dt>{{ __('settings.email_encryption') }}</dt><dd class="ec-mono">{{ strtoupper($values['encryption']) }}</dd></div>
    @isset($values['username'])
      <div><dt>{{ __('settings.email_username') }}</dt><dd class="ec-mono">{{ $values['username'] }}</dd></div>
    @endisset
  </dl>
</div>
