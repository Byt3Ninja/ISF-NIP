<div class="row">
  <h5>{{ __('cycles.Program_Settings') }}</h5>
  <!-- External Participation -->
  <div class="col-12 mb-4">
    <div class="form-check form-switch">
      <label class="form-check-label" for="external-participation">
        {{ __('cycles.External_Participation_Label') }}
      </label>
      <input class="form-check-input" type="checkbox" id="allow_non_company" name="allow_non_company" />
    </div>
    <small class="text-muted">
      {{ __('cycles.External_Participation_Helper_Text') }}
    </small>
  </div>

  <!-- Private Link -->
  <div class="col-12 mb-4">
    <div class="form-check form-switch">
      <label class="form-check-label" for="private-link">
        {{ __('cycles.Private Link') }}
      </label>
      <input class="form-check-input" type="checkbox" id="private" name="private" />
    </div>
    <small class="text-muted">
      {{ __('cycles.Private_Link_Helper_Text') }}
    </small>
  </div>

  <hr class="my-6" />

  <!-- Mentorship Settings -->
  <h5>{{ __('cycles.Mentorship_Settings') }}</h5>
  <div class="col-12">
    <div class="form-group">
      <h5>
        {{ __('cycles.Auto_Approve_Mentorship_Requests') }}
      </h5>
      <div class="form-check form-switch">
        <input 
          class="form-check-input" 
          type="checkbox" 
          id="auto-approve-mentorship-requests" 
          name="auto_approve_mentorship_requests"
        >
        <label class="form-check-label" for="auto-approve-mentorship-requests">
          {{ __('cycles.Enable_Auto_Approval') }}
        </label>
      </div>
      <small class="text-muted">
        {{ __('cycles.Auto_Approve_Helper_Text') }}
      </small>

      <input type="hidden" name="type" id="type">
      <input type="hidden" name="cycleid" id="cycleid" value="{{ isset( $cycle ) ? $cycle->id : 0 }}">
      <input type="hidden" name="organization_id" value="@if(isset($cycle)) {{ optional($cycle)->organization_id }} @else 0 @endif">
    </div>
  </div>

  <hr class="my-6" />

  <!-- Mentorship Reminder Settings -->
  <h5>{{ __('cycles.Mentorship_Reminder_Settings') }}</h5>
  <div class="col-12">
    <div class="row">
      <div class="col-md-4">
        <div class="form-check form-switch">
          <label class="form-check-label" for="reminder_enabled">
            {{ __('cycles.Enable_Mentorship_Reminders') }}
          </label>
          <input class="form-check-input" type="checkbox" id="reminder_enabled" @if(isset($cycle) && $cycle->reminder_enabled == 1) checked @endif name="reminder_enabled" />
        </div>
        @php
          $cycle = $ccycle;

          $reminderoptional = DB::table('mentorship_schedules')
            ->where('cycle_id', $cycle->id)
            ->where('is_active', 1)
            ->orderBy('value', 'desc') // عشان يرجع 24 الأول، بعده 6، بعده 1
            ->get()
            ->map(function ($reminder) {
              return $reminder->value . '_' . $reminder->unit . '_before';
            })
            ->toArray();
          $reminderOptions = ['24_hours_before', '6_hours_before', '1_hour_before'];
        @endphp
        <div id="hours_reminder" style="display: none;">
          <div class="form-group d-flex align-items-center gap-6 mt-3">

            {{-- Container where hidden inputs will be injected --}}
            <div id="reminder_times_container"></div>

            @foreach($reminderOptions as $option)
              <div class="form-check">
                <input 
                  class="form-check-input reminder-checkbox" 
                  type="checkbox" 
                  id="{{ $option }}" 
                  value="{{ $option }}"
                  {{ in_array($option, $reminderoptional ?? []) ? 'checked' : '' }}
                />
                <label class="form-check-label text-nowrap" for="{{ $option }}">
                  {{ __('cycles.' . $option) }}
                </label>
              </div>
            @endforeach
          </div>
          <small class="text-muted">
            {{ __('cycles.Mentorship_Reminders_Help_Text') }}
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Hours Reminder -->
  {{-- <div class="col-md-6" id="hours_reminder" style="display: none;">
    <div class="form-group public-data-form-fields">
      <label class="form-label fs-6 fw-semibold" for="hours_reminder_input">{{ __('admin.Hours_Reminder') }}: </label>
      <input type="number" min="1" id="hours_reminder_input" name="hours_reminder" class="form-control"
        value="{{ $ccycle?->hours_Rminder }}" placeholder="{{ __('admin.Hours_Reminder') }}">
      <div class="invalid-feedback"></div>
    </div>
  </div> --}}


  <hr class="my-6" />

  <!-- Program Visibility -->
  <h5 id="program-settings-visibility">{{ __('cycles.Program Visibility') }}</h5>
  <div class="col-12">
    <div class="form-group mb-4">
      <label class="form-label fs-6 fw-semibold" for="is_public">{{ __('cycles.visibility') }}: </label>
      <select class="form-select" id="is_public" name="is_public">
        <option value="0" {{ ($ccycle?->is_public ?? 0) == 0 ? 'selected' : '' }}>
          {{ __('cycles.Program Visible To Private') }}
        </option>
        <option value="1" {{ ($ccycle?->is_public ?? 0) == 1 ? 'selected' : '' }}>
          {{ __('cycles.Program Visible To Public') }}
        </option>
      </select>
    </div>
  </div>

  <!-- Public URL -->
  <div class="col-12">
    <div class="form-group mb-4" id="public_slug_div">
      <label class="form-label fs-6 fw-semibold" for="public_slug">{{ __('cycles.Public URL') }}: </label>
      <div class="input-group">
        <input type="text" id="public_slug" name="public_slug" class="form-control" value="{{ $ccycle?->public_slug }}" placeholder="{{ __('cycles.Public URL placeholder') }}">
        <button id="public_slug_validate" title="Validate URL" class="input-group-text btn btn-outline-secondary" id="public_slug-addon1">
          <i class="fas fa-fw fa-sm fa-refresh"></i>
        </button>
        <div id="public_slug_invalid_feedback" class="invalid-feedback"></div>
        <div id="public_slug_valid_feedback" class="valid-feedback"></div>
      </div>
    </div>
  </div>

  <hr class="my-6" />

  <!-- Offline Session Location -->
  <h5>{{ $l == 'ar' ? 'موقع الجلسة الحضورية' : 'Offline Session Location' }}</h5>
  <div class="col-12 mb-3">
    <div class="form-group">
      <label class="form-label fs-6 fw-semibold" for="offline_location_address">
        {{ $l == 'ar' ? 'العنوان' : 'Address' }}
      </label>
      <input
        type="text"
        id="offline_location_address"
        name="offline_location_address"
        class="form-control"
        maxlength="250"
        value="{{ $ccycle?->offline_location_address }}"
        placeholder="{{ $l == 'ar' ? 'أدخل العنوان' : 'Enter address' }}"
      >
      <div class="invalid-feedback"></div>
    </div>
  </div>
  <div class="col-12 mb-2">
    <div class="form-group">
      <label class="form-label fs-6 fw-semibold" for="offline_location_map">
        {{ $l == 'ar' ? 'رابط خرائط جوجل' : 'Google Map' }}
      </label>
      <input
        type="url"
        id="offline_location_map"
        name="offline_location_map"
        class="form-control"
        maxlength="250"
        value="{{ $ccycle?->offline_location_map }}"
        placeholder="https://maps.google.com/..."
      >
      <div class="invalid-feedback"></div>
    </div>
  </div>
  <div class="col-12">
    <small class="text-muted">
      {{ $l == 'ar' ? 'في حال تركها فارغة، سيتم أخذ تفاصيل موقع الجلسة الحضورية من إعدادات المنظمة.' : 'If left empty, offline session location details will be taken from the organization configuration.' }}
    </small>
  </div>
</div>

<hr class="my-6" />

<!-- Branding Colors Settings -->
<div class="col-12">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h5 class="mb-0">{{ __('cycles.Branding_Colors') }}</h5>
    <button type="button" id="reset-brand-colors" class="btn btn-sm btn-outline-secondary">
      <i class="ti ti-refresh me-1"></i>
      {{ __('cycles.Reset_To_Default') }}
    </button>
  </div>
  <div class="row">
    <div class="col-sm-4 col-lg-3 mb-3">
      <p class="mb-2">{{ __('cycles.Gradient_Start_Color') }}</p>
      <div id="start-color" class="brand-color-picker"></div>
      <input type="hidden" name="start_color" id="start_color_input"
        value="{{ $ccycle?->start_color }}">
      <div class="invalid-feedback start-color-error" style="display: none;"></div>
    </div>
    <div class="col-sm-4 col-lg-3 mb-3">
      <p class="mb-2">{{ __('cycles.Gradient_End_Color') }}</p>
      <div id="end-color" class="brand-color-picker"></div>
      <input type="hidden" name="end_color" id="end_color_input"
        value="{{ $ccycle?->end_color }}">
      <div class="invalid-feedback end-color-error" style="display: none;"></div>
    </div>
    <div class="col-sm-4 col-lg-3 mb-3">
      <p class="mb-2">{{ __('cycles.Main_Color') }}</p>
      <div id="main-color" class="brand-color-picker"></div>
      <input type="hidden" name="main_color" id="main_color_input" value="{{ $ccycle?->main_color }}">
      <div class="invalid-feedback main-color-error" style="display: none;"></div>
    </div>
  </div>

  <!-- Live preview of the hero gradient built from the start/end colors above -->
  <div class="row">
    <div class="col-12">
      <p class="mb-2">{{ __('cycles.Gradient_Preview') }}</p>
      <div id="hero-gradient-preview"></div>
    </div>
  </div>
</div>

<style>
  #hero-gradient-preview {
    height: 120px;
    border-radius: .5rem;
    border: 1px solid rgba(0, 0, 0, .1);
    background:
      radial-gradient(circle at 20% 30%, var(--hero-gradient-start, #6366f114) 0%, transparent 50%),
      radial-gradient(circle at 80% 70%, var(--hero-gradient-end, #0891b214) 0%, transparent 50%);
  }
</style>

@push('scripts')
  <script>
    var validating = false;
    var require_validation = true;
    // Show/hide public slug field based on is_public value
    $(document).on('change', '#is_public', function() {
      let _public = $(this).val();
      if( _public == 1 ) { 
        $('#public_slug_div').show();
      } else {
        $('#public_slug_div').hide();
        // Clear validation error when hiding
        $('#public_slug').removeClass('is-invalid');
        $('#public_slug').siblings('.invalid-feedback').text('');
      }
    });

    $(document).on('input', '#public_slug', function() {
      require_validation = true;
      if( require_validation === true )
      {
        $('#public_slug').removeClass('is-invalid');
        $('#public_slug').removeClass('is-valid');
        setTimeout(function() {
          $('#public_slug').siblings('.invalid-feedback').html('');
          $('#public_slug').siblings('.valid-feedback').html('');
        }, 60);
      }
    });

    $(document).on('click', '#public_slug_validate', function(e) {
      e.preventDefault();
      if( validating === false )
      {
        let slug = $.trim( $('#public_slug').val() );
        let title = $.trim( $('#title').val() );
        if( slug != '' )
        {
          let ids = [slug];
          let btn = $(this);
          validating = true;
          btn.prop('disabled', true);
          btn.attr('title', 'Validating...');
          btn.html('<i class="fas fa-fw fa-sm fa-refresh fa-spin"></i>');
          $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
          $.ajax({
            type: "POST",
            url: "/dashboard/cycles/validate-url",
            data: {
              "slug": slug,
              "title": title,
              "id": "{{ isset( $ccycle ) ? $ccycle->id : 0 }}",
            },
            success: function(response) {
              validating = false;
              btn.prop('disabled', false);
              btn.attr('title', 'Validate URL');
              btn.html('<i class="fas fa-fw fa-sm fa-refresh"></i>');

              if( response.status )
              {
                require_validation = false;
                $('#public_slug').removeClass('is-invalid');
                $('#public_slug').addClass('is-valid');
                $('#public_slug').val( response.slug );
                setTimeout(function() {
                  $('#public_slug').siblings('.valid-feedback').html(response.message);
                }, 120);
              }
              else
              {
                require_validation = false;
                $('#public_slug').removeClass('is-valid');
                $('#public_slug').addClass('is-invalid');
                let msg = response.message;
                msg += `<ul>`;
                  $.each(response.slugs, function(key, value) {
                  msg += `<li> <a class="use-suggested-slug text-danger" href="javascript:void(0);"> ${value} </a></li>`;
                });
                msg += `</ul>`;
                setTimeout(function() {
                  $('#public_slug').siblings('.invalid-feedback').html(msg);
                }, 120);
              }
            },
            error: function(error) {
              validating = false;
              btn.prop('disabled', false);
              btn.attr('title', 'Validate URL');
              btn.html('<i class="fas fa-fw fa-sm fa-refresh"></i>');

              require_validation = false;
              $('#public_slug').removeClass('is-valid');
              $('#public_slug').addClass('is-invalid');
              setTimeout(function() {
                $('#public_slug').siblings('.invalid-feedback').html(error.responseJSON.message);
              }, 120);
            }
          });
        }
      }
    });

    $(document).on('click', '.use-suggested-slug', function(e) {
      e.preventDefault();
      let slug = $.trim( $(this).text() );
      $('#public_slug').val(slug);
      validating = false;
      require_validation = false;
      $('#public_slug').removeClass('is-invalid');
      $('#public_slug').removeClass('is-valid');
      setTimeout(function() {
        $('#public_slug').siblings('.invalid-feedback').html('');
        $('#public_slug').siblings('.valid-feedback').html('');
      }, 60);
    });

    // Show/hide hours reminder field based on reminder_enabled checkbox
    $(document).on('change', '#reminder_enabled', function() {
      if ($(this).is(':checked')) {
        $('#hours_reminder').show();
      } else {
        $('#hours_reminder').hide();
        // // Clear validation error when hiding
        // $('#hours_reminder_input').removeClass('is-invalid');
        // $('#hours_reminder_input').siblings('.invalid-feedback').text('');
      }
    });

    // Real-time validation for hours reminder
    $(document).on('input', '#hours_reminder_input', function() {
      const value = $(this).val();
      const reminderEnabled = $('#reminder_enabled').is(':checked');

      $(this).removeClass('is-invalid');

      if (reminderEnabled && (!value || parseInt(value) < 1)) {
        $(this).addClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('{{ $l == "en" ? "Hours reminder must be at least 1 hour" : "تذكير الساعات يجب أن يكون ساعة واحدة على الأقل" }}');
      } else {
        $(this).siblings('.invalid-feedback').text('');
      }
    });

    // Real-time validation for offline location address (max 250)
    $(document).on('input', '#offline_location_address', function() {
      const value = $(this).val();
      $(this).removeClass('is-invalid');
      if (value.length > 250) {
        $(this).addClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('{{ $l == "en" ? "Address must not exceed 250 characters" : "يجب ألا يتجاوز العنوان 250 حرفاً" }}');
      } else {
        $(this).siblings('.invalid-feedback').text('');
      }
    });

    // Real-time validation for offline location map URL (max 250, must be a valid Google Maps URL)
    const googleMapsRegex = /^https:\/\/(www\.)?(google\.[a-z.]+\/maps|maps\.google\.[a-z.]+|goo\.gl\/maps|maps\.app\.goo\.gl)\/.*/i;
    $(document).on('input', '#offline_location_map', function() {
      const value = $(this).val().trim();
      $(this).removeClass('is-invalid');
      if (value.length > 250) {
        $(this).addClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('{{ $l == "en" ? "Google Map URL must not exceed 250 characters" : "يجب ألا يتجاوز رابط الخريطة 250 حرفاً" }}');
      } else if (value && !googleMapsRegex.test(value)) {
        $(this).addClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('{{ $l == "en" ? "Please enter a valid Google Maps URL" : "يرجى إدخال رابط خرائط جوجل صحيح" }}');
      } else {
        $(this).siblings('.invalid-feedback').text('');
      }
    });

    // Real-time validation for public slug
    $(document).on('input', '#public_slug', function() {
      const value = $(this).val().trim();
      const isPublic = $('#is_public').val();

      $(this).removeClass('is-invalid');

      if (isPublic == '1' && !value) {
        $(this).addClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('{{ $l == "en" ? "Public URL is required when program is set to public" : "الرابط العام مطلوب عندما يكون البرنامج عاماً" }}');
      } else if (value && value.length < 3) {
        $(this).addClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('{{ $l == "en" ? "Public URL must be at least 3 characters" : "الرابط العام يجب أن يكون 3 أحرف على الأقل" }}');
      } else {
        $(this).siblings('.invalid-feedback').text('');
      }
    });

    // Initialize visibility on page load
    $(document).ready(function() {
      // Initialize public slug visibility
      const isPublic = $('#is_public').val();
      // console.log('Initial is_public value:', isPublic); // Debug log

      if (isPublic == '1') {
        $('#public_slug_div').show();
        // console.log('Showing public slug div'); // Debug log
      } else {
        $('#public_slug_div').hide();
        // console.log('Hiding public slug div'); // Debug log
      }

      // Initialize hours reminder visibility
      function updateReminderTimes() {
        let container = $("#reminder_times_container");
        container.empty(); // clear old hidden inputs

        $(".reminder-checkbox:checked").each(function () {
          $("<input>")
            .attr("type", "hidden")
            .attr("name", "reminder_times[]")
            .val($(this).val())
            .appendTo(container);
        });
      }

      // update on change
      $(document).on("change", ".reminder-checkbox", updateReminderTimes);

      // run once on load (sync pre-checked)
      updateReminderTimes();
    });

    // Branding colors: pickers, defaults, reset button, hidden-input syncing, gradient preview
    $(document).ready(function () {
      // Start/end match the .hero-gradient CSS exactly (radial-gradient stops at 8% alpha).
      // Main is a solid brand color, no alpha involved.
      const BRAND_DEFAULTS = {
        start: '#6366f114',
        end: '#0891b214',
        main: '#6366f1'
      };

      const RESET_DEFAULTS = {
        start: '#6366f114',
        end: '#0891b214',
        main: '#6366f1'
      };

      const $resetBtn = $('#reset-brand-colors');
      const $preview = $('#hero-gradient-preview');

      function normalize(value) {
        return (value || '').toString().replace(/\s+/g, '').toLowerCase();
      }

      function checkForChanges() {
        // Keep Reset button visible as a static branding action
      }

      function updatePreview() {
        $preview.css('--hero-gradient-start', $('#start_color_input').val() || BRAND_DEFAULTS.start);
        $preview.css('--hero-gradient-end', $('#end_color_input').val() || BRAND_DEFAULTS.end);
      }

      function initColorPicker(selector, defaultColor, hiddenInputSelector) {
        const pickrInstance = pickr.create({
          el: selector,
          theme: 'classic',
          default: defaultColor.replace(/^#+/, '#'),
          swatches: [
            '#6366F114',
            '#0891B214',
            '#6366F1',
            '#7367F0',
            '#9C88FF'
          ],
          components: {
            preview: true,
            opacity: true,
            hue: true,
            interaction: {
              hex: true,
              rgba: true,
              hsla: true,
              hsva: true,
              cmyk: true,
              input: true,
              clear: true,
              save: true
            }
          }
        });

        function syncValue(color) {
          if (!color) return;
          // Use HEXA format for all inputs (with alpha preservation)
          const value = color.toHEXA().toString();
          $(hiddenInputSelector).val(value);
          
          // Clear error messages in real-time on color change
          $(hiddenInputSelector).siblings('.invalid-feedback').text('').hide();

          updatePreview();
          checkForChanges();
        }

        pickrInstance.on('change', syncValue);
        pickrInstance.on('save', function (color) {
          syncValue(color);
          pickrInstance.hide();
        });
        pickrInstance.on('clear', function () {
          $(hiddenInputSelector).val('');
          updatePreview();
          checkForChanges();
        });

        return pickrInstance;
      }

      const startPickr = initColorPicker(
        '#start-color',
        $('#start_color_input').val() || BRAND_DEFAULTS.start,
        '#start_color_input'
      );
      const endPickr = initColorPicker(
        '#end-color',
        $('#end_color_input').val() || BRAND_DEFAULTS.end,
        '#end_color_input'
      );
      const mainPickr = initColorPicker(
        '#main-color',
        $('#main_color_input').val() || BRAND_DEFAULTS.main,
        '#main_color_input'
      );

      $resetBtn.on('click', function (e) {
        e.preventDefault();

        startPickr.setColor(RESET_DEFAULTS.start);
        endPickr.setColor(RESET_DEFAULTS.end.replace(/^#+/, '#'));
        mainPickr.setColor(RESET_DEFAULTS.main);

        $('#start_color_input').val(RESET_DEFAULTS.start);
        $('#end_color_input').val(RESET_DEFAULTS.end);
        $('#main_color_input').val(RESET_DEFAULTS.main);

        // Clear validation error when reset is clicked
        $('.start-color-error, .end-color-error, .main-color-error').text('').hide();

        updatePreview();
        checkForChanges();
      });

      // Run once on load in case saved values differ from defaults (e.g. editing an existing cycle)
      updatePreview();
      checkForChanges();
    });
  </script>
@endpush
