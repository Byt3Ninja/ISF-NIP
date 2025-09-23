<div class="row">
  <h5>{{ __('admin.Program_Settings') }}</h5>
  <!-- External Participation -->
  <div class="col-12">
    <div class="form-check form-switch">
      <label class="form-check-label" for="external-participation">
        {{ __('cycles.External_Participation_Label') }}
      </label>
      <input class="form-check-input" type="checkbox" id="allow_non_company" name="allow_non_company" />
    </div>
  </div>
    
  <!-- Private Link -->
  <div class="col-12">
    <div class="form-check form-switch">
      <label class="form-check-label" for="private-link">
        {{ __('cycles.Private Link') }}
      </label>
      <input class="form-check-input" type="checkbox" id="private" name="private" />
    </div>
  </div>

  <div class="col-12">
    <div class="row">
      <!-- Reminder -->
      <div class="col-md-4">
        <div class="form-check form-switch">
          <label class="form-check-label" for="private-link">
            {{ __('admin.Reminder') }}
          </label>
          <input class="form-check-input" type="checkbox" id="reminder_enabled" name="reminder_enabled" />
        </div>
      </div>
    </div>
  </div>

  <!-- Hours Reminder -->
  <div class="col-md-6" id="hours_reminder" style="display: none;">
    <div class="form-group public-data-form-fields">
      <label class="form-label fs-6 fw-semibold" for="public_slug">{{ __('admin.Hours_Reminder') }}: </label>
      <input type="number" min="1" id="hours_reminder_input" name="hours_reminder" class="form-control"
        value="{{ $ccycle?->hours_Rminder }}" placeholder="{{ __('admin.Hours_Reminder') }}">
      <div class="invalid-feedback"></div>
    </div>
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
      <input type="hidden" name="cycleid" id="cycleid" value="0">
      <input type="hidden" name="organization_id" value="@if(isset($cycle)) {{ optional($cycle)->organization_id }} @else 0 @endif">
    </div>
  </div>

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
      <input type="text" id="public_slug" name="public_slug" class="form-control" value="{{ $ccycle?->public_slug }}"
        placeholder="{{ __('cycles.Public URL placeholder') }}">
      <div class="invalid-feedback"></div>
    </div>
  </div>
</div>

@push('scripts')
  <script>
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

    // Show/hide hours reminder field based on reminder_enabled checkbox
    $(document).on('change', '#reminder_enabled', function() {
      if ($(this).is(':checked')) {
        $('#hours_reminder').show();
      } else {
        $('#hours_reminder').hide();
        // Clear validation error when hiding
        $('#hours_reminder_input').removeClass('is-invalid');
        $('#hours_reminder_input').siblings('.invalid-feedback').text('');
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
      
      // Initialize reminder field visibility
      const reminderEnabled = $('#reminder_enabled').is(':checked');
      if (reminderEnabled) {
        $('#hours_reminder').show();
      } else {
        $('#hours_reminder').hide();
      }
    });
  </script>
@endpush