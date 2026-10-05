@php
  $themeColors = [
    'gradient_start' => '#7367F0',
    'gradient_end' => '#CE9FFC',
    'main_color' => '#7367F0',
    'button_text_color' => '#FFFFFF',
  ];

  $posted = function (string $key) use ($themeColors) {
    return old('branding_' . $key, $themeColors[$key]);
  };
@endphp

<form id="platform-branding-form" class="form" action="{{ route('settings.system_branding_update') }}" method="POST">
  {!! csrf_field() !!}
  @method('PUT')

  <div class="col-md-12 col-12">
    <h6 class="mb-1">{{ __('settings.branding_title') }}</h6>
    <p class="text-secondary small mb-4">{{ __('settings.branding_description') }}</p>
  </div>

  @if ($errors->has('branding_gradient_start') || $errors->has('branding_gradient_end') || $errors->has('branding_main_color') || $errors->has('branding_button_text_color'))
    <div class="col-md-12 col-12">
      <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
          @foreach (['branding_gradient_start', 'branding_gradient_end', 'branding_main_color', 'branding_button_text_color'] as $brandingField)
            @foreach ($errors->get($brandingField) as $brandingError)
              <li>{{ $brandingError }}</li>
            @endforeach
          @endforeach
        </ul>
      </div>
    </div>
  @endif

  {{-- Gradient Start --}}
  <div class="col-md-6 col-12">
    <div class="form-group mb-3">
      <label class="form-label" for="branding_gradient_start_value">
        {{ __('settings.branding_gradient_start') }}
        <span class="text-secondary small">({{ __('settings.branding_optional') }})</span>
      </label>
      <div class="d-flex align-items-center gap-2">
        <div id="branding_gradient_start_picker" class="branding-pickr"></div>
        <input type="text" class="form-control branding-color-text" id="branding_gradient_start_value"
          name="branding_gradient_start" maxlength="20" dir="ltr" placeholder="{{ $themeColors['gradient_start'] }}"
          value="{{ $posted('gradient_start') }}">
        <button type="button" class="btn btn-outline-secondary branding-color-clear" data-key="gradient_start"
          title="{{ __('settings.branding_use_theme_gradient') }}"
          aria-label="{{ __('settings.branding_use_theme_gradient') }}">
          <i class="ti ti-restore"></i>
        </button>
      </div>
    </div>
  </div>

  {{-- Gradient End --}}
  <div class="col-md-6 col-12">
    <div class="form-group mb-3">
      <label class="form-label" for="branding_gradient_end_value">
        {{ __('settings.branding_gradient_end') }}
        <span class="text-secondary small">({{ __('settings.branding_optional') }})</span>
      </label>
      <div class="d-flex align-items-center gap-2">
        <div id="branding_gradient_end_picker" class="branding-pickr"></div>
        <input type="text" class="form-control branding-color-text" id="branding_gradient_end_value"
          name="branding_gradient_end" maxlength="20" dir="ltr" placeholder="{{ $themeColors['gradient_end'] }}"
          value="{{ $posted('gradient_end') }}">
        <button type="button" class="btn btn-outline-secondary branding-color-clear" data-key="gradient_end"
          title="{{ __('settings.branding_use_theme_gradient') }}"
          aria-label="{{ __('settings.branding_use_theme_gradient') }}">
          <i class="ti ti-restore"></i>
        </button>
      </div>
    </div>
  </div>

  {{-- Main Color --}}
  <div class="col-md-6 col-12">
    <div class="form-group mb-3">
      <label class="form-label" for="branding_main_color_value">
        {{ __('settings.branding_main_color') }} *
      </label>
      <div class="d-flex align-items-center gap-2">
        <div id="branding_main_color_picker" class="branding-pickr"></div>
        <input type="text" class="form-control branding-color-text" id="branding_main_color_value"
          name="branding_main_color" required maxlength="20" dir="ltr" placeholder="{{ $themeColors['main_color'] }}"
          value="{{ $posted('main_color') ?: $themeColors['main_color'] }}">
      </div>
    </div>
  </div>

  {{-- Button Text Color --}}
  <div class="col-md-6 col-12">
    <div class="form-group mb-3">
      <label class="form-label" for="branding_button_text_color_value">
        {{ __('settings.branding_button_text_color') }} *
      </label>
      <div class="d-flex align-items-center gap-2">
        <div id="branding_button_text_color_picker" class="branding-pickr"></div>
        <input type="text" class="form-control branding-color-text" id="branding_button_text_color_value"
          name="branding_button_text_color" required maxlength="20" dir="ltr"
          placeholder="{{ $themeColors['button_text_color'] }}"
          value="{{ $posted('button_text_color') ?: $themeColors['button_text_color'] }}">
      </div>
    </div>
  </div>

  {{-- Live Preview --}}
  <div class="col-12">
    <div class="mb-3 p-3 rounded" id="branding-preview-panel"
      style="background-image: linear-gradient(45deg, {{ $themeColors['gradient_start'] }}, {{ $themeColors['gradient_end'] }});">
      <div class="d-flex align-items-center justify-content-between gap-3">
        <span class="small" id="branding-preview-label" style="color: {{ $themeColors['button_text_color'] }};">
          {{ __('settings.branding_preview') }}
        </span>
        <span class="btn btn-sm mb-0" id="branding-preview-button"
          style="background: {{ $themeColors['main_color'] }}; color: {{ $themeColors['button_text_color'] }}; border-color: {{ $themeColors['main_color'] }};">
          {{ __('settings.branding_preview_button') }}
        </span>
      </div>
    </div>
  </div>

  <div class="col-12 d-flex justify-content-end gap-2 mt-6">
    <button type="submit" class="btn btn-outline-secondary" form="platform-branding-reset-form"
      id="platform-branding-reset">
      <i class="ti ti-refresh me-1"></i>{{ __('settings.branding_reset') }}
    </button>
    <button type="submit" class="btn btn-primary">{{ __('settings.Submit') }}</button>
  </div>
</form>

<form id="platform-branding-reset-form" action="{{ route('settings.system_branding_reset') }}" method="POST"
  class="d-none">
  {!! csrf_field() !!}
  @method('PUT')
</form>

@push('scripts')
  <script>
    (function () {
      const PickrLib = window.Pickr || window.pickr;
      if (!PickrLib) {
        console.warn('[Branding] Pickr library not found');
        return;
      }

      const form = document.getElementById('platform-branding-form');
      if (!form) return;

      // Theme defaults (shared with PHP)
      const DEFAULTS = {
        gradient_start: @json($themeColors['gradient_start']),
        gradient_end: @json($themeColors['gradient_end']),
        main_color: @json($themeColors['main_color']),
        button_text_color: @json($themeColors['button_text_color']),
      };

      // Fields that can be cleared (optional)
      const OPTIONAL = new Set(['gradient_start', 'gradient_end']);

      const HEX = /^#?([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/;

      function expand(hex) {
        hex = (hex || '').replace('#', '').trim();
        if (hex.length === 3) {
          hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        }
        return hex ? '#' + hex.toLowerCase() : '';
      }

      function isValidHex(value) {
        return HEX.test((value || '').trim());
      }

      // Field configuration
      const fields = [
        { key: 'gradient_start', pickerId: 'branding_gradient_start_picker', inputId: 'branding_gradient_start_value' },
        { key: 'gradient_end', pickerId: 'branding_gradient_end_picker', inputId: 'branding_gradient_end_value' },
        { key: 'main_color', pickerId: 'branding_main_color_picker', inputId: 'branding_main_color_value' },
        { key: 'button_text_color', pickerId: 'branding_button_text_color_picker', inputId: 'branding_button_text_color_value' },
      ];

      const pickers = {};
      let suppressSync = false; // prevents change event from overwriting empty value on reset

      // ---------- Preview ----------
      function preview() {
        const panel = document.getElementById('branding-preview-panel');
        const button = document.getElementById('branding-preview-button');
        const label = document.getElementById('branding-preview-label');

        const start = form.querySelector('[name="branding_gradient_start"]').value.trim();
        const end = form.querySelector('[name="branding_gradient_end"]').value.trim();
        const main = form.querySelector('[name="branding_main_color"]').value.trim();
        const text = form.querySelector('[name="branding_button_text_color"]').value.trim();

        const bothValid = isValidHex(start) && isValidHex(end);

        panel.style.backgroundImage =
          `linear-gradient(45deg, ${bothValid ? expand(start) : DEFAULTS.gradient_start}, ${bothValid ? expand(end) : DEFAULTS.gradient_end})`;

        const mainColor = isValidHex(main) ? expand(main) : DEFAULTS.main_color;
        const textColor = isValidHex(text) ? expand(text) : DEFAULTS.button_text_color;

        button.style.background = mainColor;
        button.style.color = textColor;
        button.style.borderColor = mainColor;
        if (label) label.style.color = textColor;
      }

      // ---------- Create Pickr instances ----------
      fields.forEach(({ key, pickerId, inputId }) => {
        const el = document.getElementById(pickerId);
        const hidden = document.getElementById(inputId);
        if (!el || !hidden) return;

        // Prefer current value → otherwise fall back to theme default
        const initial = isValidHex(hidden.value) ? expand(hidden.value) : DEFAULTS[key];
        hidden.value = initial; // normalize on load

        const picker = PickrLib.create({
          el,
          theme: 'classic',
          default: initial,
          swatches: [
            '#7367F0', '#28C76F', '#EA5455', '#FF9F43',
            '#1E9FF2', '#82868B', '#CE9FFC', '#FFFFFF',
          ],
          components: {
            preview: true,
            opacity: false,
            hue: true,
            interaction: {
              hex: true,
              rgba: false,
              hsla: false,
              hsva: false,
              cmyk: false,
              input: true,
              clear: OPTIONAL.has(key),
              save: true,
            },
          },
        });

        const sync = (color) => {
          if (suppressSync) return; // ignore while we are resetting

          const hex = color ? color.toHEXA().toString().slice(0, 7) : '';
          hidden.value = hex;
          preview();
        };

        picker
          .on('change', sync)
          .on('save', (color) => {
            sync(color);
            picker.hide();
          })
          .on('clear', () => {
            hidden.value = '';
            preview();
          });

        pickers[key] = picker;
      });

      // ---------- Manual typing in text fields ----------
      form.addEventListener('input', (e) => {
        const input = e.target;
        if (!input.classList.contains('branding-color-text')) return;

        const key = input.name.replace('branding_', '');
        const picker = pickers[key];
        if (!picker) return;

        const val = input.value.trim();
        if (isValidHex(val)) {
          const hex = expand(val);
          picker.setColor(hex, true);
          preview();
        } else {
          preview();
        }
      });

      // ---------- Clear / restore buttons (fixed) ----------
      form.addEventListener('click', (e) => {
        const btn = e.target.closest('.branding-color-clear');
        if (!btn) return;

        const key = btn.dataset.key;
        const picker = pickers[key];
        const input = document.getElementById(`branding_${key}_value`);

        // 1. Empty the form value (this is what gets submitted)
        if (input) input.value = '';

        // 2. Visually show the theme default without overwriting the empty value
        if (picker) {
          suppressSync = true;

          picker.setColor(DEFAULTS[key], true);

          // Some Pickr versions need this extra call
          if (typeof picker.applyColor === 'function') {
            picker.applyColor(true);
          }

          // Re-enable change events on next tick
          setTimeout(() => {
            suppressSync = false;
          }, 0);
        }

        preview();
      });

      // ---------- Reset confirmation ----------
      form.addEventListener('submit', (e) => {
        if (e.submitter?.id === 'platform-branding-reset' &&
          !window.confirm(@json(__('settings.branding_reset_confirm')))) {
          e.preventDefault();
        }
      });

      // Initial preview
      preview();
    })();
  </script>
@endpush
