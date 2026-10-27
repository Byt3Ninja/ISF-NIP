@php
  $hideBreadcrumbRoutes = ['core.dashboard_view'];
  $hideBreadcrumbPaths = ['dashboard', 'dashboard/dashboard'];
  $currentRoute = Route::currentRouteName();
  $currentPath = request()->path();
  $shouldHide = in_array($currentRoute, $hideBreadcrumbRoutes) || in_array($currentPath, $hideBreadcrumbPaths);
@endphp

@if(isset($breadcrumbs) && count($breadcrumbs) > 1 && !$shouldHide)
<nav aria-label="{{ __('breadcrumb.breadcrumb_navigation') ?? 'Breadcrumb' }}">
  
  <ol class="breadcrumb align-items-center bg-card rounded p-2 shadow-xs">
    @if(isset($backUrl) && $backUrl)
    {{-- Back Button --}}
    <li class="breadcrumb-item">
      <a
        id="back-button"
        href="{{ $backUrl }}"
        class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect waves-light mb-0"
        title="{{ __('breadcrumb.back') }}"
        aria-label="{{ __('breadcrumb.back') }}"
        data-bs-toggle="tooltip"
        data-bs-placement="{{ $l == 'ar' ? 'left' : 'right' }}">
        <i class="ti ti-arrow-{{ $l == 'ar' ? 'right' : 'left' }} ti-md"></i>
      </a>
    </li>
    @endif

    {{-- Breadcrumb Trail --}}
        @foreach($breadcrumbs as $index => $crumb)
            @if($crumb['active'] ?? false)
                <li class="breadcrumb-item active text-truncate {{ $index == 0 ? 'hide-before' : '' }}" aria-current="page" style="max-width: 200px;">
                    {{ $crumb['label'] }}
                </li>
            @else
                <li class="breadcrumb-item text-truncate {{ $index == 0 ? 'hide-before' : '' }}" style="max-width: 200px;">
                    <a href="{{ $crumb['url'] }}" class="text-decoration-none">
                        {{-- @if($index === 0)
                            <i class="ti ti-home-2 {{ $l == 'ar' ? 'ms-1' : 'me-1' }}"></i>
                        @endif --}}
                        {{ $crumb['label'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
@endif

{{-- @push('scripts')
<script>
(function () {
  'use strict';

  const CONFIG = {
    backButtonSelector: '#back-button',
    backHref: "{{ $backUrl }}",
    watchSelectors: 'input:not([type="search"]), input:not([type="file"]), textarea, select',
    ignoreSelectors: '[data-ignore-dirty], input[type="search"], input[name="search"] .dt-filter, .card-datatable, .table-responsive, .organizations-list-wrapper',
    debug: false
  };

  const backButtons = document.querySelectorAll(CONFIG.backButtonSelector);
  if (!backButtons.length) return;

  const initialState = new Map();
  let formSubmitting = false;
  let navigationAllowed = false;
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
        el.matches(CONFIG.ignoreSelectors) ||
        el.closest(CONFIG.ignoreSelectors)
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
   * TRACK FORM SUBMISSIONS
   * =============================== */
  document.addEventListener('submit', function (e) {
    formSubmitting = true;
    navigationAllowed = true;
    log('Form submitted - allowing navigation');
  });

  /* ===============================
   * BACK BUTTON HANDLER
   * =============================== */
  backButtons.forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
  
      if (!isPageDirty()) {
        window.history.back();
        return;
      }

      releaseAlert('warning', {
        title: '{{ __('settings.navigation.unsaved_changes') }}',
        message: '{{ __('settings.navigation.unsaved_changes_message') }}',
        confirmText: '{{ __('settings.navigation.leaveanyway') }}',
        cancelText: '{{ __('settings.navigation.stay') }}',
        confirmColor: 'primary'
      }).then(ok => {
        if (ok) {
          navigationAllowed = true;
          window.history.back();
        }
      });
    });
  });

  /* ===============================
   * INTERCEPT ALL NAVIGATION ATTEMPTS
   * =============================== */
  
  // Intercept all link clicks
  document.addEventListener('click', function(e) {
    const link = e.target.closest('a');
    
    // Ignore if no link, or it's the back button, or external link, or javascript void
    if (!link || 
        link.id === 'back-button' || 
        link.target === '_blank' || 
        link.href.startsWith('javascript:') ||
        link.href.startsWith('#') ||
        navigationAllowed ||
        formSubmitting) {
      return;
    }

    // Check if navigation away from current page
    const currentUrl = window.location.href.split('#')[0];
    const linkUrl = link.href.split('#')[0];
    
    if (currentUrl === linkUrl) return; // Same page navigation (hash change)

    if (isPageDirty()) {
      e.preventDefault();
      e.stopPropagation();

      releaseAlert('warning', {
        title: '{{ __('settings.navigation.unsaved_changes') }}',
        message: '{{ __('settings.navigation.unsaved_changes_message') }}',
        confirmText: '{{ __('settings.navigation.leaveanyway') }}',
        cancelText: '{{ __('settings.navigation.stay') }}',
        confirmColor: 'primary'
      }).then(ok => {
        if (ok) {
          navigationAllowed = true;
          window.location.href = link.href;
        }
      });
    }
  }, true); // Use capture phase

  /* ===============================
   * HANDLE PAGE REFRESH
   * =============================== */
  let isRefreshing = false;

  window.addEventListener('keydown', function (e) {
    if (
      e.key === 'F5' ||
      (e.ctrlKey && e.key === 'r') ||
      (e.metaKey && e.key === 'r')
    ) {
      isRefreshing = true;
      
      if (isPageDirty() && !formSubmitting && !navigationAllowed) {
        e.preventDefault();
        
        releaseAlert('warning', {
          title: '{{ __('settings.navigation.unsaved_changes') }}',
          message: '{{ __('settings.navigation.unsaved_changes_message') }}',
          confirmText: '{{ __('settings.navigation.leaveanyway') }}',
          cancelText: '{{ __('settings.navigation.stay') }}',
          confirmColor: 'primary'
        }).then(ok => {
          if (ok) {
            navigationAllowed = true;
            window.location.reload();
          } else {
            isRefreshing = false;
          }
        });
      }
    }
  });

  /* ===============================
   * FALLBACK: Browser native beforeunload
   * (Only for browser back/forward/close tab)
   * =============================== */
  window.addEventListener('beforeunload', function (e) {
    // Allow navigation if form submitted or explicitly allowed
    if (formSubmitting || navigationAllowed || isRefreshing) {
      return undefined;
    }

    // Show native browser warning only for unhandled navigation (close tab, browser back/forward)
    if (isPageDirty()) {
      const message = '{{ __('settings.navigation.unsaved_changes_message') }}';
      e.preventDefault();
      e.returnValue = message;
      return message;
    }
  });
})();
</script>
@endpush --}}