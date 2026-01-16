@php
if (!isset($crumbs)) {
    $crumbs = breadcrumbs();
}
@endphp

<style>
  .hide-before::before {
    display: none;
  }
</style>

@if(isset($crumbs))
  @if(!Route::is('core.dashboard_view') && !Route::is('core.admin_access'))

    <ol class="breadcrumb align-items-center bg-card rounded p-2 shadow-xs">

      {{-- Back Button (JS-controlled navigation) --}}
      <li class="breadcrumb-item active">
        <a
          id="back-button"
          href="#"
          class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light mb-0"
          role="button"
          aria-label="Go back"
        >
          <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }}"></i>
        </a>
      </li>

      {{-- Dashboard Link --}}
      <li class="breadcrumb-item active hide-before">
        <a href="/dashboard">
          {{ __('admin.Dashboard') }}
        </a>
      </li>

      {{-- Parent --}}
      @if (!$_user->hasAnyRole('Judger|Mentor|serviceProvider'))
        @if (isset($crumbs['parent']))
          <li class="breadcrumb-item active">
            {{ __('settings.navigation.' . $crumbs['parent']) }}
          </li>
        @endif
      @endif

      {{-- Child Group --}}
      @if (isset($crumbs['child_group']) && !Route::is($crumbs['child_group'] . '.index'))
        <li class="breadcrumb-item active">
          <a
            @if (isset($crumbs['group_route']) && !Route::is($crumbs['group_route']))
              href="{{ route($crumbs['group_route']) }}"
            @else
              href="{{ Route::has($crumbs['child_group'] . '.index')
                  ? route($crumbs['child_group'] . '.index')
                  : url()->previous() }}"
            @endif
          >
            {{ __('settings.navigation.' . $crumbs['child_group']) }}
          </a>
        </li>
      @endif

      {{-- Child --}}
      <li class="breadcrumb-item" aria-current="page">
        {{ __('settings.navigation.' . $crumbs['child']) }}
      </li>

    </ol>

  @endif
@endif

@push('scripts')
<script>
(function () {
  'use strict';

  const CONFIG = {
    backButtonSelector: '#back-button',
    backHref: "{{ session('_previous_url') ?? '/dashboard' }}",
    watchSelectors: 'input, textarea, select',
    ignoreSelectors: '[data-ignore-dirty]',
    debug: false
  };

  const backButton = document.querySelector(CONFIG.backButtonSelector);
  if (!backButton) return;

  const initialState = new Map();
  const log = (...a) => CONFIG.debug && console.log('[DIRTY]', ...a);

  /* ===============================
   * VALUE NORMALIZER (CRITICAL)
   * =============================== */
  function normalize(value) {
    if (value === null || value === undefined) return '';
    if (Array.isArray(value)) return value.sort().join('|');
    return String(value).trim();
  }

  function getValue(el) {
    if (el.type === 'checkbox') {
      return normalize(el.checked);
    }

    if (el.type === 'radio') {
      return normalize(
        document.querySelector(`input[type="radio"][name="${el.name}"]:checked`)
          ?.value
      );
    }

    if (el.type === 'file') {
      return normalize(
        Array.from(el.files).map(f => f.name)
      );
    }

    if (el.tagName === 'SELECT' && el.multiple) {
      return normalize(
        Array.from(el.selectedOptions).map(o => o.value)
      );
    }

    return normalize(el.value);
  }

  /* ===============================
   * CAPTURE INITIAL FIELD STATE
   * =============================== */
  function captureInitialState() {
    initialState.clear();

    document.querySelectorAll(CONFIG.watchSelectors).forEach(el => {
      if (
        el.disabled ||
        el.readOnly ||
        el.matches(CONFIG.ignoreSelectors)
      ) return;

      // radio groups tracked once
      if (el.type === 'radio') {
        if (initialState.has(el.name)) return;
        initialState.set(el.name, getValue(el));
        return;
      }

      initialState.set(el, getValue(el));
    });

    log('Initial state:', initialState);
  }

  window.addEventListener('load', () => {
    setTimeout(captureInitialState, 100);
  });

  /* ===============================
   * DIRTY CHECK (NOW CORRECT)
   * =============================== */
  function isPageDirty() {
    for (const [key, initialValue] of initialState.entries()) {
      let currentValue;

      if (typeof key === 'string') {
        // radio group
        currentValue = normalize(
          document.querySelector(
            `input[type="radio"][name="${key}"]:checked`
          )?.value
        );
      } else {
        if (!document.body.contains(key)) continue;
        currentValue = getValue(key);
      }

      if (currentValue !== initialValue) {
        log('Dirty field:', key, initialValue, currentValue);
        return true;
      }
    }
    return false;
  }

  /* ===============================
   * BACK BUTTON HANDLER
   * =============================== */
  backButton.addEventListener('click', function (e) {
    e.preventDefault();

    if (!isPageDirty()) {
      window.location.href = CONFIG.backHref;
      return;
    }

    Swal.fire({
      title: 'You have unsaved changes. Leave anyway?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Leave anyway',
      cancelButtonText: 'Stay',
      customClass: {
        confirmButton: 'btn btn-primary ms-3',
        cancelButton: 'btn btn-label-secondary',
      },
      buttonsStyling: false,
      reverseButtons: true,
    }).then(r => {
      if (r.isConfirmed) {
        window.location.href = CONFIG.backHref;
      }
    });
  });

  /* ===============================
   * PAGE CLOSE / REFRESH
   * =============================== */
  window.addEventListener('beforeunload', function (e) {
    if (!isPageDirty()) return;
    e.preventDefault();
    e.returnValue = '';
  });
})();
</script>
@endpush
