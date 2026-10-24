{{-- Platform Branding (GV-9674). $_branding / $_brandingSaved come from the view composer
     (PlatformBrandingService). An empty gradient pair is valid: it renders the theme gradient. --}}
<form id="platform-branding-form" class="form" action="{{ route('settings.system_branding_update') }}" method="POST" novalidate>
  {!! csrf_field() !!}
  @method('PUT')
  <input type="hidden" name="settings_tab" value="branding">

  <div class="col-md-12 col-12">
    <h6 class="mb-1">{{ __('settings.branding_title') }}</h6>
    <p class="text-secondary small mb-4">{{ __('settings.branding_description') }}</p>
  </div>

  {{-- Gradient Start --}}
  <div class="col-md-6 col-12">
    <div class="form-group mb-3">
      <label class="form-label" for="branding_gradient_start_value">
        {{ __('settings.branding_gradient_start') }}
        <span class="text-secondary small">({{ __('settings.branding_optional') }})</span>
      </label>
      <div class="d-flex align-items-center gap-2">
        <div id="branding_gradient_start_picker" class="branding-pickr"></div>
        <input type="text" class="form-control branding-color-text @error('branding_gradient_start') is-invalid @enderror"
          id="branding_gradient_start_value" name="branding_gradient_start" maxlength="7" dir="ltr"
          placeholder="{{ __('settings.branding_use_theme_gradient') }}"
          value="{{ old('branding_gradient_start', $_brandingSaved['gradient_start']) }}">
        {{--  <button type="button" class="btn btn-outline-secondary branding-gradient-clear"
          title="{{ __('settings.branding_use_theme_gradient') }}"
          aria-label="{{ __('settings.branding_use_theme_gradient') }}">
          <i class="ti ti-restore"></i>
        </button>  --}}
      </div>
      <div class="invalid-feedback d-block" data-error-for="gradient_start">@error('branding_gradient_start'){{ $message }}@enderror</div>
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
        <input type="text" class="form-control branding-color-text @error('branding_gradient_end') is-invalid @enderror"
          id="branding_gradient_end_value" name="branding_gradient_end" maxlength="7" dir="ltr"
          placeholder="{{ __('settings.branding_use_theme_gradient') }}"
          value="{{ old('branding_gradient_end', $_brandingSaved['gradient_end']) }}">
        {{--  <button type="button" class="btn btn-outline-secondary branding-gradient-clear"
          title="{{ __('settings.branding_use_theme_gradient') }}"
          aria-label="{{ __('settings.branding_use_theme_gradient') }}">
          <i class="ti ti-restore"></i>
        </button>  --}}
      </div>
      <div class="invalid-feedback d-block" data-error-for="gradient_end">@error('branding_gradient_end'){{ $message }}@enderror</div>
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
        <input type="text" class="form-control branding-color-text @error('branding_main_color') is-invalid @enderror"
          id="branding_main_color_value" name="branding_main_color" required maxlength="7" dir="ltr"
          value="{{ old('branding_main_color', $_branding['main']) }}">
      </div>
      <div class="invalid-feedback d-block" data-error-for="main_color">@error('branding_main_color'){{ $message }}@enderror</div>
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
        <input type="text" class="form-control branding-color-text @error('branding_button_text_color') is-invalid @enderror"
          id="branding_button_text_color_value" name="branding_button_text_color" required maxlength="7" dir="ltr"
          value="{{ old('branding_button_text_color', $_branding['button_text']) }}">
      </div>
      <div class="invalid-feedback d-block" data-error-for="button_text_color">@error('branding_button_text_color'){{ $message }}@enderror</div>
    </div>
  </div>

  {{-- Live Preview: same rules as admin/includes/branding-styles (.bg-gradient-primary + solid button) --}}
  <div class="col-12">
    <div class="mb-3 p-3 rounded" id="branding-preview-panel">
      <div class="d-flex align-items-center justify-content-between gap-3">
        <span class="small" id="branding-preview-label">{{ __('settings.branding_preview') }}</span>
        <span class="btn btn-sm mb-0" id="branding-preview-button">{{ __('settings.branding_preview_button') }}</span>
      </div>
    </div>
  </div>

  <div class="col-12 d-flex justify-content-end gap-2 mt-6">
    {{-- Confirmed through the shared releaseAlert() modal (partials/alert-modals), which submits the reset form. --}}
    <button type="button" class="btn btn-outline-secondary" data-alert="warning"
      data-alert-title="{{ __('settings.branding_reset') }}" data-alert-message="{{ __('settings.branding_reset_confirm') }}"
      data-alert-confirm="{{ __('settings.branding_reset') }}" data-alert-form="platform-branding-reset-form">
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
      const form = document.getElementById('platform-branding-form');
      if (!form) return;

      const DEFAULT_MAIN = @json($_branding['main']);
      const MESSAGES = {
        gradient_start_missing: @json(__('settings.branding_enter_start_color')),
        gradient_end_missing: @json(__('settings.branding_enter_end_color')),
        gradient_invalid: @json(__('settings.branding_valid_color')),
        main_color: @json(__('settings.branding_valid_main_color')),
        button_text_color: @json(__('settings.branding_valid_button_text_color')),
      };
      const KEYS = ['gradient_start', 'gradient_end', 'main_color', 'button_text_color'];
      const input = (key) => document.getElementById(`branding_${key}_value`);
      const value = (key) => input(key).value.trim();

      // Same normalization as PlatformBrandingService::normalize(): "#ABC" / "abc" -> "#aabbcc", else null.
      function normalize(raw) {
        let hex = (raw || '').trim().replace(/^#/, '');
        if (/^[0-9a-f]{3}$/i.test(hex)) hex = hex.replace(/./g, (c) => c + c);
        return /^[0-9a-f]{6}$/i.test(hex) ? '#' + hex.toLowerCase() : null;
      }

      // The theme gradient is derived from the main color (#7367f0 -> #b9b3f8 = 50% white).
      function mixWithWhite(hex, weight) {
        const channel = (i) => Math.round(parseInt(hex.substr(i, 2), 16) * weight + 255 * (1 - weight));
        return '#' + [1, 3, 5].map((i) => channel(i).toString(16).padStart(2, '0')).join('');
      }

      function colors() {
        const main = normalize(value('main_color')) || DEFAULT_MAIN;
        const start = normalize(value('gradient_start'));
        const end = normalize(value('gradient_end'));
        const custom = start && end;
        return {
          main,
          buttonText: normalize(value('button_text_color')) || '#ffffff',
          start: custom ? start : main,
          end: custom ? end : mixWithWhite(main, 0.5),
        };
      }

      function preview() {
        const c = colors();
        document.getElementById('branding-preview-panel').style.backgroundImage = `linear-gradient(45deg, ${c.start}, ${c.end})`;
        const button = document.getElementById('branding-preview-button');
        button.style.backgroundColor = c.main;
        button.style.borderColor = c.main;
        button.style.color = c.buttonText;
        document.getElementById('branding-preview-label').style.color = c.buttonText;
      }

      // ---------- Validation (mirrors UpdatePlatformBrandingRequest) ----------
      function setError(key, message) {
        input(key).classList.toggle('is-invalid', !!message);
        form.querySelector(`[data-error-for="${key}"]`).textContent = message || '';
      }

      function validate() {
        const errors = {};
        const start = value('gradient_start');
        const end = value('gradient_end');

        if (start && !normalize(start)) errors.gradient_start = MESSAGES.gradient_invalid;
        if (end && !normalize(end)) errors.gradient_end = MESSAGES.gradient_invalid;
        if (start && !end) errors.gradient_end = MESSAGES.gradient_end_missing;
        if (end && !start) errors.gradient_start = MESSAGES.gradient_start_missing;
        if (!normalize(value('main_color'))) errors.main_color = MESSAGES.main_color;
        if (!normalize(value('button_text_color'))) errors.button_text_color = MESSAGES.button_text_color;

        KEYS.forEach((key) => setError(key, errors[key]));
        return Object.keys(errors).length === 0;
      }

      // ---------- Color pickers (visual aid; the text inputs are what gets submitted) ----------
      const pickers = {};
      const PickrLib = window.Pickr || window.pickr;
      let syncing = false; // true while the code (not the user) moves a picker

      function showInPicker(key) {
        const picker = pickers[key];
        if (!picker) return;
        const c = colors();
        const fallback = { gradient_start: c.start, gradient_end: c.end, main_color: c.main, button_text_color: c.buttonText };
        syncing = true;
        picker.setColor(normalize(value(key)) || fallback[key], true);
        picker.applyColor(true); // a silent setColor() alone leaves the swatch button blank
        syncing = false;
      }

      if (PickrLib) {
        KEYS.forEach((key) => {
          const picker = PickrLib.create({
            el: document.getElementById(`branding_${key}_picker`),
            theme: 'classic',
            default: '#ffffff',
            swatches: ['#7367F0', '#28C76F', '#EA5455', '#FF9F43', '#1E9FF2', '#82868B', '#CE9FFC', '#FFFFFF'],
            components: {
              preview: true,
              opacity: false,
              hue: true,
              interaction: { hex: true, input: true, save: true },
            },
          });

          const take = (color) => {
            if (syncing || !color) return;
            input(key).value = color.toHEXA().toString().slice(0, 7).toLowerCase();
            refresh(key);
          };

          picker.on('init', () => showInPicker(key))
            .on('change', take)
            .on('save', (color) => { take(color); picker.hide(); });

          pickers[key] = picker;
        });
      } else {
        console.warn('[Branding] Pickr library not found');
      }

      // Main color drives the empty-gradient swatches, so every picker follows a change.
      // The picker being dragged (pickedKey) is left alone.
      function refresh(pickedKey) {
        KEYS.filter((key) => key !== pickedKey).forEach(showInPicker);
        if (form.querySelector('.is-invalid')) validate();
        preview();
      }

      // ---------- Typing ----------
      form.addEventListener('input', (e) => {
        if (e.target.classList.contains('branding-color-text')) refresh(null);
      });

      // ---------- "Use the theme gradient": empty both ends ----------
      form.addEventListener('click', (e) => {
        if (!e.target.closest('.branding-gradient-clear')) return;
        input('gradient_start').value = '';
        input('gradient_end').value = '';
        setError('gradient_start', null);
        setError('gradient_end', null);
        refresh(null);
      });

      form.addEventListener('submit', (e) => {
        if (!validate()) {
          e.preventDefault();
          form.querySelector('.is-invalid')?.focus();
        }
      });

      preview();
    })();
  </script>
@endpush
