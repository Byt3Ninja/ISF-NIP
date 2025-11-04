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
  <h5>{{ __('cycles.Program Visibility') }}</h5>
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
</div>

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
      console.log('Initial is_public value:', isPublic); // Debug log
      
      if (isPublic == '1') {
        $('#public_slug_div').show();
        console.log('Showing public slug div'); // Debug log
      } else {
        $('#public_slug_div').hide();
        console.log('Hiding public slug div'); // Debug log
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
  </script>
@endpush