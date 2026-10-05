{{--
Alert modals (success / danger / info / warning / copy) + releaseAlert()
Requires: Bootstrap 5.3 (global `bootstrap`) and Tabler Icons (ti ti-*)
No custom CSS: Bootstrap core classes only.

Usage:
releaseAlert('success', { message: 'Saved successfully' });            // closes itself after 2s
releaseAlert('danger', { title: 'Error', message: 'Something went wrong' });
releaseAlert('warning', { title: 'Delete?', message: 'Are you sure?', confirmColor: 'danger', onConfirm: () => { ... } });
releaseAlert('info', { title: 'Meeting', message: 'The passcode is: <strong>394687</strong>' });
releaseAlert('copy', { title: 'Share link', copyText: 'https://example.com/invite/abc123' });
releaseAlert('warning', { confirmText: 'Archive' }).then(ok => { if (ok) { ... } });
releaseAlert('danger', { hideCancel: false, confirmText: 'Delete', onConfirm: () => { ... } });
<button data-alert="danger" data-alert-title="Delete?" data-alert-form="delete-form">Delete</button>
<button data-alert="copy" data-alert-title="Passcode" data-alert-copy-text="394687">Show</button>

NOTE: `message` is rendered as HTML. Escape any user-provided data before passing it in.
`copyText` is set via .value, so it does not need escaping.
--}}

@php
  $alertModals = [
    'success' => [
      'icon' => 'ti-check',
      'title' => __('admin.success'),
      'message' => '',
      'hideCancel' => true,
      'autoClose' => 2000,
    ],
    'danger' => [
      'icon' => 'ti-x',
      'title' => 'Danger Title',
      'message' => "This action is permanent and can't be undone. Please confirm you want to continue.",
      'hideCancel' => true,
    ],
    'info' => [
      'icon' => 'ti-info-circle',
      'title' => 'Info Title',
      'message' => "Here's some helpful information about this action before you continue.",
      'hideCancel' => true,
    ],
    'warning' => [
      'icon' => 'ti-exclamation-circle',
      'title' => 'Warning Title',
      'message' => 'Are you sure you want to proceed? This action may affect your current settings.',
    ],
    'copy' => [
      'icon' => 'ti-copy',
      'title' => 'Copy Title',
      'message' => 'Copy the text below to your clipboard.',
      'confirm' => __('admin.Done'),
      'hideCancel' => true,
    ],
  ];
  $colorMap = ['info' => 'primary', 'copy' => 'primary'];
@endphp

@foreach ($alertModals as $type => $modal)
  @php $color = $colorMap[$type] ?? $type; @endphp

  <div class="modal fade" id="alertModal-{{ $type }}" data-alert-type="{{ $type }}" data-alert-color="{{ $color }}"
    data-alert-auto-close="{{ $modal['autoClose'] ?? 0 }}" tabindex="-1" role="alertdialog"
    aria-hidden="true" aria-labelledby="alertModal-{{ $type }}-title" aria-describedby="alertModal-{{ $type }}-message">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 shadow-lg">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-4 z-3" data-bs-dismiss="modal"
          aria-label="Close"></button>

        <div
          class="modal-body d-flex flex-column justify-content-center align-items-center text-center px-4 px-sm-5 pt-5 pb-4">
          <div class="avatar avatar-xl mb-4">
            <div class="avatar-initial rounded-circle bg-label-{{ $color }}">
              <i class="ti {{ $modal['icon'] }} fs-2"></i>
            </div>
          </div>

          <h3 class="fw-bold mb-2" id="alertModal-{{ $type }}-title" data-alert-title>{{ $modal['title'] }}</h3>
          <p class="fs-5 text-body-secondary mb-{{ $type === 'copy' ? '4' : '8' }}" id="alertModal-{{ $type }}-message"
            data-alert-message>
            {{ $modal['message'] }}
          </p>

          @if ($type === 'copy')
            <div class="input-group input-group-lg mb-8">
              <input type="text" class="form-control" value="" readonly data-alert-copy-input aria-label="Text to copy">
              <button type="button" class="btn btn-primary" data-alert-copy-btn>
                <i class="ti ti-copy me-1" data-alert-copy-icon></i><span data-alert-copy-label>Copy</span>
              </button>
            </div>
          @endif

          <div class="d-flex flex-column-reverse flex-sm-row justify-content-center gap-3">
            <button type="button"
              class="btn btn-lg btn-outline-secondary px-5 fw-bold @if ($modal['hideCancel'] ?? false) d-none @endif"
              data-alert-cancel data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
            <button type="button" class="btn btn-lg btn-{{ $color }} px-5 fw-bold"
              data-alert-confirm>{{ $modal['confirm'] ?? __('admin.OK') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
@endforeach

<script>
  (function () {
    // Remember each modal's default content so every call starts from a clean state
    var defaults = {};
    document.querySelectorAll('[data-alert-type]').forEach(function (el) {
      defaults[el.dataset.alertType] = {
        title: el.querySelector('[data-alert-title]').textContent,
        message: el.querySelector('[data-alert-message]').innerHTML,
        confirmText: el.querySelector('[data-alert-confirm]').textContent,
        cancelText: el.querySelector('[data-alert-cancel]').textContent,
        hideCancel: el.querySelector('[data-alert-cancel]').classList.contains('d-none'),
        color: el.dataset.alertColor,
        autoClose: parseInt(el.dataset.alertAutoClose, 10) || 0
      };
    });

    // ---- Copy helpers ----
    var copyTimers = new WeakMap();

    function resetCopyButton(modalEl) {
      var btn = modalEl.querySelector('[data-alert-copy-btn]');
      if (!btn) return;
      clearTimeout(copyTimers.get(modalEl));
      btn.classList.remove('btn-success');
      btn.classList.add('btn-primary');
      btn.querySelector('[data-alert-copy-icon]').className = 'ti ti-copy me-1';
      btn.querySelector('[data-alert-copy-label]').textContent = 'Copy';
    }

    function copyText(text, input) {
      if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
      }
      // Fallback for non-HTTPS / older browsers
      return new Promise(function (resolve, reject) {
        input.focus();
        input.select();
        document.execCommand('copy') ? resolve() : reject();
      });
    }

    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-alert-copy-btn]');
      if (!btn) return;
      var modalEl = btn.closest('[data-alert-type]');
      var input = modalEl.querySelector('[data-alert-copy-input]');

      copyText(input.value, input).then(function () {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-success');
        btn.querySelector('[data-alert-copy-icon]').className = 'ti ti-check me-1';
        btn.querySelector('[data-alert-copy-label]').textContent = 'Copied';
        clearTimeout(copyTimers.get(modalEl));
        copyTimers.set(modalEl, setTimeout(function () { resetCopyButton(modalEl); }, 2000));
      }).catch(function () {
        // Clipboard unavailable: select the text so the user can press Ctrl/Cmd+C
        input.focus();
        input.select();
      });
    });

    /**
     * Opens one of the alert modals.
     * @param {'success'|'danger'|'info'|'warning'|'copy'} type
     * @param {Object}   [opts]
     * @param {string}   [opts.title]
     * @param {string}   [opts.message]       Rendered as HTML
     * @param {string}   [opts.copyText]      Text shown in the field (copy type only)
     * @param {string}   [opts.confirmText]
     * @param {string}   [opts.cancelText]
     * @param {string}   [opts.confirmColor]  Bootstrap color of the confirm button
     * @param {boolean}  [opts.hideCancel]    Defaults to true for 'success', 'danger', 'info' and 'copy'
     * @param {number}   [opts.autoClose]     ms before it closes itself, 0 = never.
     *                                        Defaults to 2000 for 'success'
     * @param {Function} [opts.onConfirm]
     * @param {Function} [opts.onCancel]
     * @returns {Promise<boolean>} true = confirmed, false = cancelled/dismissed
     */
    window.releaseAlert = function (type, opts) {
      type = defaults[type] ? type : 'info';
      opts = opts || {};

      var el = document.getElementById('alertModal-' + type);
      var d = defaults[type];
      var confirmBtn = el.querySelector('[data-alert-confirm]');
      var cancelBtn = el.querySelector('[data-alert-cancel]');

      // Close any other alert modal that's currently open
      document.querySelectorAll('[data-alert-type].show').forEach(function (other) {
        if (other !== el) bootstrap.Modal.getInstance(other)?.hide();
      });

      el.querySelector('[data-alert-title]').textContent = opts.title || d.title;
      el.querySelector('[data-alert-message]').innerHTML = opts.message || d.message;
      confirmBtn.textContent = opts.confirmText || d.confirmText;
      cancelBtn.textContent = opts.cancelText || d.cancelText;
      cancelBtn.classList.toggle('d-none', opts.hideCancel !== undefined ? !!opts.hideCancel : d.hideCancel);

      var color = opts.confirmColor || d.color;
      confirmBtn.classList.remove('btn-' + d.color);
      confirmBtn.classList.add('btn-' + color);

      var copyInput = el.querySelector('[data-alert-copy-input]');
      if (copyInput) {
        copyInput.value = opts.copyText || '';
        resetCopyButton(el);
      }

      var modal = bootstrap.Modal.getOrCreateInstance(el);
      var autoClose = opts.autoClose !== undefined ? opts.autoClose : d.autoClose;

      return new Promise(function (resolve) {
        var confirmed = false;

        function onConfirmClick() { confirmed = true; modal.hide(); }
        function onHidden() {
          clearTimeout(timer);
          confirmBtn.removeEventListener('click', onConfirmClick);
          el.removeEventListener('hidden.bs.modal', onHidden);
          if (confirmed) { if (typeof opts.onConfirm === 'function') opts.onConfirm(); }
          else if (typeof opts.onCancel === 'function') { opts.onCancel(); }
          resolve(confirmed);
        }

        var timer = autoClose ? setTimeout(function () { modal.hide(); }, autoClose) : null;

        confirmBtn.addEventListener('click', onConfirmClick);
        el.addEventListener('hidden.bs.modal', onHidden);
        modal.show();
      });
    };

    // Declarative triggers: <button data-alert="danger" data-alert-title="..." data-alert-message="...">
    document.addEventListener('click', function (e) {
      var trigger = e.target.closest('[data-alert]');
      if (!trigger) return;
      e.preventDefault();

      var type = trigger.dataset.alert;
      var isNotification = type === 'success' || type === 'info' || type === 'copy';

      releaseAlert(type, {
        title: trigger.dataset.alertTitle,
        message: trigger.dataset.alertMessage,
        confirmText: trigger.dataset.alertConfirm,
        cancelText: trigger.dataset.alertCancel,
        copyText: trigger.dataset.alertCopyText,
        // triggers are confirmations, so cancel stays visible unless it is a notification
        hideCancel: isNotification ? true : trigger.dataset.alertHideCancel === 'true',
        onConfirm: function () {
          var formId = trigger.dataset.alertForm;
          if (formId) document.getElementById(formId)?.submit();
          else if (trigger.tagName === 'A' && trigger.href) window.location.href = trigger.href;
        }
      });
    });
  })();
</script>
