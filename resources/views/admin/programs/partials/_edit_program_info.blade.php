<div class="row">

  @php
    $userOrgIds = $_user->organizations_ids;
    $showOrgDropdown = $_user->hasRole('Admin') || ($_user->isSubAdmin() && count($userOrgIds) > 1);
    $isSingleOrg = ($_user->hasRole('organizationManager') && !$_user->isSubAdmin()) || ($_user->isSubAdmin() && count($userOrgIds) == 1);
  @endphp

  @if ($isSingleOrg)
  <input type="hidden" name="organization_id2" value="{{ $userOrgIds[0] ?? $_user->organization_id }}">
  @endif

  <!-- Organizations -->
  @if ($showOrgDropdown)
  <div class="col-md-6">
    <div class="form-group mb-4">
      <label for="organization" class="form-label fs-6 fw-semibold">
        {{ __('cycles.Select_Organization') }}
      </label>
      <select
        class="form-select select2"
        id="organization"
        name="organization_id2"
        data-placeholder="{{ __('cycles.Select_Organization') }}"
        value="{{ old('organization_id2', isset($ccycle) ? $ccycle->organization_id : '') }}"
      >
        <option value="">{{ __('cycles.Select_Organization') }}</option>
        @if($_user->hasRole('Admin'))
          @foreach (\App\Organization::all() as $org)
          <option value="{{ $org->id }}">{{ $org->{'name_' . app()->getLocale()} }}</option>
          @endforeach
        @else
          @foreach ($_user->organizations as $org)
          <option value="{{ $org->id }}">{{ $org->{'name_' . app()->getLocale()} }}</option>
          @endforeach
        @endif
      </select>
      <div class="invalid-feedback"></div>
    </div>
  </div>
  @endif

  <!-- Program Title -->
  <div class="col-md-6">
    <div class="form-group mb-4">
      <label for="program-title" class="form-label fs-6 fw-semibold">
        {{ __('cycles.Cycle title') }}
      </label>
      {{-- <input type="hidden" name="cycleid" id="cycleid"> --}}
      <input 
        type="text" 
        class="form-control" 
        id="title" 
        name="title" 
        placeholder="{{ __('cycles.Cycle title') }}"
        oninput="this.value = this.value.trimStart()" 
        value="{{ isset($ccycle) ? $ccycle->title : '' }}"
        maxlength="{{ \App\Cycle::TITLE_MAX_LENGTH }}"
      />
      <div class="invalid-feedback"></div>
    </div>
  </div>

  <!-- Program Title (Arabic) -->
  <div class="col-md-6">
    <div class="form-group mb-4">
      <label for="title_ar" class="form-label fs-6 fw-semibold">
        {{ __('cycles.program_title_in_arabic') }}
      </label>
      <input 
        type="text" 
        class="form-control" 
        id="title_ar" 
        name="title_ar" 
        placeholder="{{ __('cycles.program_title_in_arabic') }}"
        oninput="this.value = this.value.trimStart()" 
        value="{{ isset($ccycle) ? $ccycle->title_ar : '' }}"
        maxlength="{{ \App\Cycle::TITLE_MAX_LENGTH }}"
      />
      <div class="invalid-feedback"></div>
    </div>
  </div>

  <!-- Program Type -->
  <div class="col-md-6">
    <div class="form-group mb-4">
      <label class="form-label fs-6 fw-semibold" for="program_type">{{ __('admin.Program_Type') }}</label>
      @php
      $selectedType = old('program_type', isset($ccycle) ? $ccycle->type : '');
      @endphp
      <select 
        class="form-select select2"
        name="program_type" 
        id="program_type" 
        data-placeholder="{{ __('cycles.select_program_type') }}"
        {{ isset($id) ? 'disabled' : '' }}
      >
        <option value="">{{ __('cycles.select_program_type') }}</option>
        @foreach(\App\Cycle::programTypes() as $value => $label)
        <option value="{{ $value }}" {{ $selectedType==$value ? 'selected' : '' }}>
          {{ $label }}
        </option>
        @endforeach
      </select>
      <div class="invalid-feedback"></div>

      @if(isset($ccycle) && $ccycle->exists)
        <input type="hidden" name="program_type" value="{{ $ccycle->type }}">
      @endif
    </div>
  </div>
  
   <div class="col-md-6">
    <div class="form-group mb-4">
      <label for="program-title" class="form-label fs-6 fw-semibold">
        {{ __('admin.Cohort') }}
      </label>
      <input 
        type="text" 
        class="form-control" 
        id="cohort" 
        name="cohort" 
        placeholder="{{ __('admin.Cohort') }}"
        oninput="this.value = this.value.trimStart()"
        value="{{ isset($ccycle) && $ccycle->cohort ? $ccycle->cohort : '' }}"
      />
      <div class="invalid-feedback"></div>
    </div>
  </div>


  <!-- Date Range Picker -->
  <div class="col-md-6">
    <div class="form-group mb-4">
      <label for="date-range-picker" class="form-label fs-6 fw-semibold">
        {{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}:
      </label>
      <input type="hidden" name="start" id="start" />
      <input type="hidden" name="end" id="end" />
      <input type="text" name="date_range" id="date-range-picker" class="form-control" placeholder="{{ __('admin.Start_Date') }} - {{ __('admin.End_Date') }}" />
      <div class="invalid-feedback"></div>
    </div>
  </div>

  <!-- Program Logo -->
  <div class="col-md-6">
    <div class="form-group mb-4">
      <label class="form-label fs-6 fw-semibold">
        {{ __('cycles.Program Logo') }}
      </label>
      <input 
        class="form-control" 
        type="file" 
        name="img_url" 
        accept="image/png, image/jpg, image/jpeg"
        value="{{ isset($ccycle) && $ccycle->img_url ? $ccycle->img_url : '' }}"
      />
      <div class="invalid-feedback"></div>
      <small class="text-muted">{{ $l == 'en' ? 'Accepted formats: PNG, JPG, JPEG. Max size: 2MB' : 'الصيغ المقبولة: PNG, JPG, JPEG. الحد الأقصى: 2 ميجا' }}</small>
    </div>
  </div>

  {{--  <!-- Program Cover -->
  <div class="col-md-6">
    <div class="form-group mb-4">
      <label class="form-label fs-6 fw-semibold">
        {{ __('cycles.program_cover') }}
      </label>
      <input 
        class="form-control" 
        type="file" 
        name="cover" 
        accept="image/png, image/jpg, image/jpeg"
      />
      <div class="invalid-feedback"></div>
      <small class="text-muted">{{ $l == 'en' ? 'Accepted formats: PNG, JPG, JPEG. Max size: 5MB' : 'الصيغ المقبولة: PNG, JPG, JPEG. الحد الأقصى: 5 ميجا' }}</small>
    </div>
  </div>  --}}

  <!-- Program Step Type -->
  <div class="col-md-6">
    <div class="form-group mb-4">
      <label class="form-label fs-6 fw-semibold" for="program_step_type">{{ __('cycles.Program_Steps_Type') }}</label>
      @php $selectedStepType = old('program_step_type', isset($ccycle) ? $ccycle->step_type : ''); @endphp
      <select 
        class="form-select select2"
        name="program_step_type" 
        id="program_step_type" 
        data-placeholder="{{ __('cycles.Select Program Steps Type') }}"
      >
        <option value="">{{ __('cycles.Select Program Steps Type') }}</option>
        <option value="cycle" {{ $selectedStepType == 'cycle' ? 'selected' : '' }}>{{ __('cycles.type_by_cycle') }}</option>
        <option value="time" {{ $selectedStepType == 'time' ? 'selected' : '' }}>{{ __('cycles.type_by_time') }}</option>
      </select>
      <div class="invalid-feedback"></div>
      <div id="program_step_type_help" class="text-muted"></div>
    </div>
  </div>

  <!-- Description -->
  <div class="col-12">
    <div class="form-group mb-4">
      <label for="description" class="form-label fs-6 fw-semibold">
        {{ __('cycles.Description') }}
      </label>
      <textarea 
        class="form-control" 
        id="description" 
        name="description" 
        placeholder="{{ __('cycles.Description') }}"
        oninput="this.value = this.value.trimStart()" 
        rows="3">{{ isset($ccycle) ? $ccycle->description : '' }}</textarea>
      <div class="invalid-feedback"></div>
    </div>
  </div>

  <!-- Description (Arabic) -->
  <div class="col-12">
    <div class="form-group mb-4">
      <label for="description_ar" class="form-label fs-6 fw-semibold">
        {{ __('cycles.program_description_in_arabic') }}
      </label>
      <textarea 
        class="form-control" 
        id="description_ar" 
        name="description_ar" 
        placeholder="{{ __('cycles.program_description_in_arabic') }}"
        oninput="this.value = this.value.trimStart()" 
        maxlength="250"
        rows="3">{{ isset($ccycle) ? $ccycle->description_ar : '' }}</textarea>
      <div class="invalid-feedback"></div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {  
  // Initialize date range picker
  $('#date-range-picker').daterangepicker({
    opens: isRtl ? 'left' : 'right',
    drops: 'up',
    minDate: moment().startOf('day'), // Prevent selecting past dates
    autoUpdateInput: false, // Prevent auto-filling with default dates
    showDropdowns: true,
    // locale: {
    //   cancelLabel: 'Clear',
    //   format: 'MM/DD/YYYY'
    // }
  }).on('apply.daterangepicker', function(ev, picker) {
    // Update the visible input field for backend compatibility
    $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    
    // Also set hidden fields for individual dates
    $('#start').val(picker.startDate.format('YYYY-MM-DD'));
    $('#end').val(picker.endDate.format('YYYY-MM-DD'));
    
    // Clear any previous validation errors
    $(this).removeClass('is-invalid');
    $(this).next('.invalid-feedback').text('');
    
    // Real-time validation for date range
    const startDate = picker.startDate;
    const endDate = picker.endDate;
    const today = moment().startOf('day');
    
    if (startDate.isBefore(today)) {
      $(this).addClass('is-invalid');
      $(this).next('.invalid-feedback').text('{{ $l == "en" ? "Start date cannot be in the past" : "تاريخ البداية لا يمكن أن يكون في الماضي" }}');
    } else if (endDate.isSame(startDate, 'day') || endDate.isBefore(startDate)) {
      $(this).addClass('is-invalid');
      $(this).next('.invalid-feedback').text('{{ $l == "en" ? "End date must be after start date" : "تاريخ النهاية يجب أن يكون بعد تاريخ البداية" }}');
    }
  }).on('cancel.daterangepicker', function(ev, picker) {
    // Clear the input when cancel is clicked
    $(this).val('');
    $('#start').val('');
    $('#end').val('');
    
    // Clear any validation errors
    $(this).removeClass('is-invalid');
    $(this).next('.invalid-feedback').text('');
  });

  // Form validation function
  function validateForm() {
    let isValid = true;
    
    // Clear all previous errors
    $('.form-control, .form-select').removeClass('is-invalid');
    $('.select2-container').removeClass('is-invalid');
    $('.invalid-feedback').text('').removeClass('d-block');

    // Validate organization (if visible)
    const orgSelect = $('#organization');
    if (orgSelect.length && !orgSelect.val()) {
      // For Select2, we need to add the is-invalid class to the Select2 container
      orgSelect.addClass('is-invalid');
      const select2Container = orgSelect.next('.select2-container');
      if (select2Container.length) {
        select2Container.addClass('is-invalid');
      }
      orgSelect.closest('.form-group').find('.invalid-feedback').text(@json(__('admin.Please select an organization'))).addClass('d-block');
      isValid = false;
    }

    // Validate program type
    const programType = $('#program_type');
    if (!programType.val() && !programType.is(':disabled')) {
      // For Select2, we need to add the is-invalid class to the Select2 container
      programType.addClass('is-invalid');
      const select2Container = programType.next('.select2-container');
      if (select2Container.length) {
        select2Container.addClass('is-invalid');
      }
      programType.closest('.form-group').find('.invalid-feedback').text(@json(__('cycles.Please select a program type'))).addClass('d-block');
      isValid = false;
    }

    // Validate title
    const title = $('#title');
    if (!title.val().trim()) {
      title.addClass('is-invalid');
      title.next('.invalid-feedback').text(@json(__('cycles.program_title_required')));
      isValid = false;
    } else if (title.val().trim().length < 3) {
      title.addClass('is-invalid');
      title.next('.invalid-feedback').text('{{ $l == "en" ? "Title must be at least 3 characters" : "العنوان يجب أن يكون 3 أحرف على الأقل" }}');
      isValid = false;
    } else if (title.val().length > {{ \App\Cycle::TITLE_MAX_LENGTH }} ) {
      title.addClass('is-invalid');
      title.next('.invalid-feedback').text('{{ $l == "en" ? "Title cannot exceed ' . \App\Cycle::TITLE_MAX_LENGTH . ' characters" : "العنوان لا يمكن أن يتجاوز ' . \App\Cycle::TITLE_MAX_LENGTH . ' حرف" }}');
      isValid = false;
    }

    // Validate title_ar
    const title_ar = $('#title_ar');
    if (!title_ar.val().trim()) {
      title_ar.addClass('is-invalid');
      title_ar.next('.invalid-feedback').text(@json(__('cycles.program_title_ar_required')));
      isValid = false;
    } else if (title_ar.val().length > {{ \App\Cycle::TITLE_MAX_LENGTH }} ) {
      title_ar.addClass('is-invalid');
      title_ar.next('.invalid-feedback').text('{{ $l == "en" ? "Title in Arabic cannot exceed ' . \App\Cycle::TITLE_MAX_LENGTH . ' characters." : "العنوان باللغة العربية لا يمكن أن يتجاوز ' . \App\Cycle::TITLE_MAX_LENGTH . ' حرف." }}');
      isValid = false;
    }

    // Validate date range
    const dateRange = $('#date-range-picker');
    if (!dateRange.val()) {
      dateRange.addClass('is-invalid');
      dateRange.next('.invalid-feedback').text('{{ $l == "en" ? "Please select start and end dates" : "يرجى اختيار تاريخ البداية والنهاية" }}');
      isValid = false;
    } else {
      const startDate = moment($('#start').val());
      const endDate = moment($('#end').val());
      const today = moment().startOf('day');
      
      // Validate that start date is not in the past
      if (startDate.isBefore(today)) {
        dateRange.addClass('is-invalid');
        dateRange.next('.invalid-feedback').text('{{ $l == "en" ? "Start date cannot be in the past" : "تاريخ البداية لا يمكن أن يكون في الماضي" }}');
        isValid = false;
      }
      // Validate that end date is after start date (not same day)
      else if (endDate.isSame(startDate, 'day') || endDate.isBefore(startDate)) {
        dateRange.addClass('is-invalid');
        dateRange.next('.invalid-feedback').text('{{ $l == "en" ? "End date must be after start date" : "تاريخ النهاية يجب أن يكون بعد تاريخ البداية" }}');
        isValid = false;
      }
    }

    // Validate description
    const description = $('#description');
    if (!description.val().trim()) {
      description.addClass('is-invalid');
      description.next('.invalid-feedback').text('{{ $l == "en" ? "Description is required" : "الوصف مطلوب" }}');
      isValid = false;
    } else if (description.val().trim().length < 10) {
      description.addClass('is-invalid');
      description.next('.invalid-feedback').text('{{ $l == "en" ? "Description must be at least 10 characters" : "الوصف يجب أن يكون 10 أحرف على الأقل" }}');
      isValid = false;
    } else if (description.val().length > 1000) {
      description.addClass('is-invalid');
      description.next('.invalid-feedback').text('{{ $l == "en" ? "Description cannot exceed 1000 characters" : "الوصف لا يمكن أن يتجاوز 1000 حرف" }}');
      isValid = false;
    }

    // Validate description_ar
    const description_ar = $('#description_ar');
    if (!description_ar.val().trim()) {
      description_ar.addClass('is-invalid');
      description_ar.next('.invalid-feedback').text(@json(__('cycles.program_description_ar_required')));
      isValid = false;
    } else if (description_ar.val().length > 250) {
      description_ar.addClass('is-invalid');
      description_ar.next('.invalid-feedback').text('{{ $l == "en" ? "Description in Arabic cannot exceed 250 characters." : "الوصف باللغة العربية لا يمكن أن يتجاوز 250 حرف." }}');
      isValid = false;
    }

    // Validate file uploads
    const logoFile = $('input[name="logo"]')[0];
    if (logoFile.files.length > 0) {
      const file = logoFile.files[0];
      if (!validateImageFile(file, 'logo', 2)) {
        isValid = false;
      }
    }

    const coverFile = $('input[name="cover"]')[0];
    if (coverFile.files.length > 0) {
      const file = coverFile.files[0];
      if (!validateImageFile(file, 'cover', 5)) {
        isValid = false;
      }
    }

    return isValid;
  }

  // File validation function
  function validateImageFile(file, fieldName, maxSizeMB) {
    const input = $(`input[name="${fieldName}"]`);
    const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg'];
    const maxSize = maxSizeMB * 1024 * 1024; // Convert MB to bytes

    // Check file type
    if (!allowedTypes.includes(file.type)) {
      input.addClass('is-invalid');
      input.next('.invalid-feedback').text('{{ $l == "en" ? "Please select a valid image file (PNG, JPG, JPEG)" : "يرجى اختيار ملف صورة صالح (PNG, JPG, JPEG)" }}');
      return false;
    }

    // Check file size
    if (file.size > maxSize) {
      input.addClass('is-invalid');
      input.next('.invalid-feedback').text(`{{ $l == "en" ? "File size cannot exceed" : "حجم الملف لا يمكن أن يتجاوز" }} ${maxSizeMB}MB`);
      return false;
    }

    // Clear any previous errors
    input.removeClass('is-invalid');
    input.next('.invalid-feedback').text('');
    return true;
  }

  // Real-time validation
  $('#title').on('input', function() {
    const value = $(this).val().trim();
    $(this).removeClass('is-invalid');
    
    if (value.length > 0 && value.length < 3) {
      $(this).addClass('is-invalid');
      $(this).next('.invalid-feedback').text('{{ $l == "en" ? "Title must be at least 3 characters" : "العنوان يجب أن يكون 3 أحرف على الأقل" }}');
    } else if (value.length > 50) {
      $(this).addClass('is-invalid');
      $(this).next('.invalid-feedback').text('{{ $l == "en" ? "Title cannot exceed 50 characters" : "العنوان لا يمكن أن يتجاوز 50 حرف" }}');
    } else {
      $(this).next('.invalid-feedback').text('');
    }
  });

  $('#title_ar').on('input', function() {
    const value = $(this).val().trim();
    $(this).removeClass('is-invalid');
    
    if (value.length > 50) {
      $(this).addClass('is-invalid');
      $(this).next('.invalid-feedback').text('{{ $l == "en" ? "Title in Arabic cannot exceed 50 characters." : "العنوان باللغة العربية لا يمكن أن يتجاوز 50 حرف." }}');
    } else {
      $(this).next('.invalid-feedback').text('');
    }
  });

  $('#description').on('input', function() {
    const value = $(this).val().trim();
    $(this).removeClass('is-invalid');
    
    if (value.length > 0 && value.length < 10) {
      $(this).addClass('is-invalid');
      $(this).next('.invalid-feedback').text('{{ $l == "en" ? "Description must be at least 10 characters" : "الوصف يجب أن يكون 10 أحرف على الأقل" }}');
    } else if (value.length > 1000) {
      $(this).addClass('is-invalid');
      $(this).next('.invalid-feedback').text('{{ $l == "en" ? "Description cannot exceed 1000 characters" : "الوصف لا يمكن أن يتجاوز 1000 حرف" }}');
    } else {
      $(this).next('.invalid-feedback').text('');
    }
  });

  $('#description_ar').on('input', function() {
    const value = $(this).val().trim();
    $(this).removeClass('is-invalid');
    
    if (value.length > 250) {
      $(this).addClass('is-invalid');
      $(this).next('.invalid-feedback').text('{{ $l == "en" ? "Description in Arabic cannot exceed 250 characters." : "الوصف باللغة العربية لا يمكن أن يتجاوز 250 حرف." }}');
    } else {
      $(this).next('.invalid-feedback').text('');
    }
  });

  // File input validation
  $('input[type="file"]').on('change', function() {
    const fieldName = $(this).attr('name');
    const maxSize = fieldName === 'logo' ? 2 : 5;
    
    if (this.files.length > 0) {
      validateImageFile(this.files[0], fieldName, maxSize);
    }
  });

  // Select2 change events to clear validation errors
  $('#organization').on('change', function() {
    if ($(this).val()) {
      $(this).removeClass('is-invalid');
      $(this).next('.select2-container').removeClass('is-invalid');
      $(this).closest('.form-group').find('.invalid-feedback').text('').removeClass('d-block');
    }
  });

  $('#program_type').on('change', function() {
    if ($(this).val()) {
      $(this).removeClass('is-invalid');
      $(this).next('.select2-container').removeClass('is-invalid');
      $(this).closest('.form-group').find('.invalid-feedback').text('').removeClass('d-block');
    }
  });

  $('#program_step_type').on('change', function() {
    if ($(this).val()) {
      $(this).removeClass('is-invalid');
      $(this).next('.select2-container').removeClass('is-invalid');
      $(this).closest('.form-group').find('.invalid-feedback').text('').removeClass('d-block');
    }
  });
});
</script>
@endpush