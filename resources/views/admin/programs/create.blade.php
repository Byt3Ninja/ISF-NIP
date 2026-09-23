@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/pickr/pickr-themes.css" /> 
@stop

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css" />
  <!-- Summernote CSS -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
  
  <style>
    /* Tab disabled state styling */
    .tab-disabled {
      opacity: 0.5 !important;
      cursor: not-allowed !important;
      background-color: #f8f9fa !important;
      border-color: #e9ecef !important;
      color: #6c757d !important;
    }
    
    .tab-disabled:hover {
      background-color: #f8f9fa !important;
      border-color: #e9ecef !important;
      color: #6c757d !important;
    }
  </style>
@stop

@php
$ccycle = new \App\Cycle(); // Create a new empty Cycle instance for form initialization

$labels = [
  'program_info' => $l == 'ar' ? 'معلومات البرنامج' : 'Program Info',
  // 'custom_pages' => $l == 'ar' ? 'صفحات مخصصة' : 'Custom Pages',
  'Program_Settings' => $l == 'ar' ? 'إعدادات البرنامج' : 'Program Settings',
];

$tabs = [
  ['id' => 'program_info', 'label' => $labels['program_info'], 'active' => true],
  ['id' => 'program_settings', 'label' => $labels['Program_Settings'], 'active' => false],
  // ['id' => 'custom_pages', 'label' => $labels['custom_pages'], 'active' => false],
];
@endphp

@section('content')
<div class="">
  <div class="">
    <div class="row">
      <h4 class="mb-4">
        {{ __('cycles.program_setup') }}
      </h4>
      @if (session()->has('message'))
        <x-alert type="success" message="{{ __('admin.' . session()->get('message')) }} {{ Session::forget('message') }}" />      @endif
      @if (session()->has('message2'))
        <x-alert type="danger" message="{{ __('admin.' . session()->get('message2')) }} {{ Session::forget('message2') }}" />
      @endif
      @if ($errors->any())
        @foreach ($errors->all() as $error)
          <x-alert type="danger" message="{{ $error }}" />
        @endforeach
      @endif
    </div>

    <div class="card p-4 p-md-8">
      <form action="{{ route('cycles.modify') }}" method="POST" enctype="multipart/form-data" id="programForm">
        @csrf 
        <input type="hidden" name="tab_id" value="{{ \App\Enums\CycleTabAction::CREATE_CYCLE }}">
        <input type="hidden" name="typecreate" id="type" value="1">
        <!-- Tabs (mobile) -->
        <div style="max-width: 100%; overflow-x: auto;" class="mb-4 d-md-none pb-3">
          <ul class="nav nav-pills flex-nowrap" role="tablist">
            @foreach ($tabs as $key => $tab)
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link {{ $tab['active'] ? 'active' : '' }} {{ $tab['id'] != 'program_info' ? 'tab-disabled' : '' }}"
                  id="pills-{{ $tab['id'] }}-tab-edit"
                  data-bs-toggle="pill"
                  data-bs-target="#pills-{{ $tab['id'] }}-edit"
                  type="button"
                  role="tab"
                  aria-controls="pills-{{ $tab['id'] }}-edit"
                  aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
                  data-tab-index="{{ $key }}"
                >
                  <span class="fw-semibold">{{ $tab['label'] }}</span>
                </button>
              </li>
            @endforeach
          </ul>
        </div>
        <!-- / Tabs (mobile) -->

        <ul
          class="nav nav-pills d-none d-md-flex"
          id="pills-tab"
          role="tablist"
        >
          @foreach ($tabs as $tab)
            <li class="nav-item me-2" role="presentation">
              <button
                class="nav-link {{ $tab['active'] ? 'active' : '' }} {{ $tab['id'] != 'program_info' ? 'tab-disabled' : '' }}"
                id="pills-{{ $tab['id'] }}-tab"
                data-bs-toggle="pill"
                data-bs-target="#pills-{{ $tab['id'] }}-edit"
                type="button"
                role="tab"
                aria-controls="pills-{{ $tab['id'] }}-edit"
                aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
                data-tab-index="{{ $loop->index }}"
              >
                <span class="fw-semibold">{{ $tab['label'] }}</span>
              </button>
            </li>
          @endforeach
        </ul>
        <hr />
        <div class="tab-content shadow-none p-4" id="pills-tabContent">
          @foreach ($tabs as $key => $tab)
            <div
              class="tab-pane fade {{ $tab['active'] ? 'show active' : '' }}"
              id="pills-{{ $tab['id'] }}-edit"
              role="tabpanel"
              aria-labelledby="pills-{{ $tab['id'] }}-tab"
              data-tab-index="{{ $key }}"
            >
              @include('admin.programs.partials._edit_' . $tab['id'], ['mode' => 'edit', 'ccycle' => $ccycle])
              
              <!-- Tab Navigation Buttons -->
              <div class="d-flex align-items-center justify-content-between gap-3 mt-4 pt-4 border-top">
                <div>
                  @if($key > 0)
                    <button type="button" class="btn btn-label-secondary prev-tab-btn" data-current-tab="{{ $key }}">
                      <i class="ti ti-arrow-{{$l == 'en' ? 'left' : 'right'}} me-2"></i>
                      {{ __('cycles.Previous') }}
                    </button>
                  @else 
                   <a href="{{ route('programlist.index') }}" type="button" class="btn btn-label-secondary">
                      <i class="ti ti-arrow-{{$l == 'en' ? 'left' : 'right'}} me-2"></i>
                      {{ __('cycles.Back') }}
                    </a>
                  @endif
                </div>
                
                <div class="d-flex gap-2">
                  @if($key < count($tabs) - 1)
                    <button type="button" class="btn btn-primary save-next-btn" data-current-tab="{{ $key }}">
                      {{ __('cycles.Save & Next') }}
                      <i class="ti ti-arrow-{{$l == 'en' ? 'right' : 'left'}} ms-2"></i>
                    </button>
                  @else
                    <button type="button" class="btn btn-success publish-btn" data-current-tab="{{ $key }}">
                      {{ __('admin.Publish') }}
                      <i class="ti ti-circle-check ms-2"></i>
                    </button>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
        
        <!-- Hidden submit button for programmatic form submission -->
        <button type="submit" id="hidden-submit-btn" style="display: none;"></button>
      </form>
    </div>
  </div>
</div>
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>
  <!-- Summernote JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
  <script src="/vuexy/assets/vendor/libs/pickr/pickr.js"></script>
  
  <script>
    $(document).ready(function() {
      // Tab navigation state
      let currentTabIndex = 0;
      const totalTabs = {{ count($tabs) }};
      const tabIds = @json(array_column($tabs, 'id'));
      
      // Validation functions for each tab
      const tabValidations = {
        'program_info': function() {
          let isValid = true;
          
          // Clear all previous errors
          $('.form-control, .form-select').removeClass('is-invalid');
          $('.select2-container').removeClass('is-invalid');
          $('.invalid-feedback').text('').removeClass('d-block');

          // Validate organization (if visible and not disabled)
          const orgSelect = $('#organization');
          if (orgSelect.length && orgSelect.is(':visible') && !orgSelect.is(':disabled') && !orgSelect.val()) {
            orgSelect.addClass('is-invalid');
            const select2Container = orgSelect.next('.select2-container');
            if (select2Container.length) {
              select2Container.addClass('is-invalid');
            }
            orgSelect.closest('.form-group').find('.invalid-feedback').text('{{ $l == "en" ? "Please select an organization" : "يرجى اختيار المنظمة" }}').addClass('d-block');
            isValid = false;
          }

          // Validate program type
          const programType = $('#program_type');
          if (!programType.val() && !programType.is(':disabled')) {
            programType.addClass('is-invalid');
            const select2Container = programType.next('.select2-container');
            if (select2Container.length) {
              select2Container.addClass('is-invalid');
            }
            programType.closest('.form-group').find('.invalid-feedback').text('{{ $l == "en" ? "Please select a program type" : "يرجى اختيار نوع البرنامج" }}').addClass('d-block');
            isValid = false;
          }

          // Validate program steps type
          const programStepsType = $('#program_step_type');
          if (!programStepsType.val() && !programStepsType.is(':disabled')) {
            programStepsType.addClass('is-invalid');
            const select2Container = programStepsType.next('.select2-container');
            if (select2Container.length) {
              select2Container.addClass('is-invalid');
            }
            programStepsType.closest('.form-group').find('.invalid-feedback').text('{{ __("cycles.Select Program Steps Type") }}').addClass('d-block');
            isValid = false;
          }

          // Validate title
          const title = $('#title');
          if (!title.val().trim()) {
            title.addClass('is-invalid');
            title.next('.invalid-feedback').text('{{ $l == "en" ? "Program title is required" : "عنوان البرنامج مطلوب" }}');
            isValid = false;
          } else if (title.val().trim().length < 3) {
            title.addClass('is-invalid');
            title.next('.invalid-feedback').text('{{ $l == "en" ? "Title must be at least 3 characters" : "العنوان يجب أن يكون 3 أحرف على الأقل" }}');
            isValid = false;
          } else if (title.val().length > {{ \App\Cycle::TITLE_MAX_LENGTH }} ) {
            title.addClass('is-invalid');
            title.next('.invalid-feedback').text('{{ $l == "en" ? "Title cannot exceed 50 characters" : "العنوان لا يمكن أن يتجاوز 50 حرف" }}');
            isValid = false;
          }

          // Validate title_ar
          const title_ar = $('#title_ar');
          if (!title_ar.val().trim()) {
            title_ar.addClass('is-invalid');
            title_ar.next('.invalid-feedback').text('{{ $l == "en" ? "Program Title in Arabic is required." : "عنوان البرنامج باللغة العربية مطلوب." }}');
            isValid = false;
          } else if (title_ar.val().length > {{ \App\Cycle::TITLE_MAX_LENGTH }} ) {
            title_ar.addClass('is-invalid');
            title_ar.next('.invalid-feedback').text('{{ $l == "en" ? "Title in Arabic cannot exceed 50 characters." : "العنوان باللغة العربية لا يمكن أن يتجاوز 50 حرف." }}');
            isValid = false;
          }

          // Validate date range
          const dateRange = $('#date-range-picker');
          if (!dateRange.val()) {
            dateRange.addClass('is-invalid');
            dateRange.next('.invalid-feedback').text('{{ $l == "en" ? "Please select start and end dates" : "يرجى اختيار تاريخ البداية والنهاية" }}');
            isValid = false;
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
          }

          // Validate description_ar
          const description_ar = $('#description_ar');
          if (!description_ar.val().trim()) {
            description_ar.addClass('is-invalid');
            description_ar.next('.invalid-feedback').text('{{ $l == "en" ? "Description in Arabic is required." : "وصف البرنامج باللغة العربية مطلوب." }}');
            isValid = false;
          } else if (description_ar.val().length > 250) {
            description_ar.addClass('is-invalid');
            description_ar.next('.invalid-feedback').text('{{ $l == "en" ? "Description in Arabic cannot exceed 250 characters." : "الوصف باللغة العربية لا يمكن أن يتجاوز 250 حرف." }}');
            isValid = false;
          }

          return isValid;
        },
        
        'Program_Settings': function() {
          let isValid = true;
          
          // Clear previous errors
          $('.form-control', '#pills-Program_Settings-edit').removeClass('is-invalid');
          $('.invalid-feedback', '#pills-Program_Settings-edit').text('');
          $('.start-color-error, .end-color-error, .main-color-error').text('').hide();
          
          // Validate public URL when visibility is public
          const isPublic = $('#is_public').val();
          const publicSlug = $('#public_slug').val().trim();
          
          if (isPublic == '1') {
            if (!publicSlug) {
              $('#public_slug').addClass('is-invalid');
              $('#public_slug').siblings('.invalid-feedback').text('{{ $l == "en" ? "Public URL is required when program is set to public" : "الرابط العام مطلوب عندما يكون البرنامج عاماً" }}');
              isValid = false;
            } else if (publicSlug.length < 3) {
              $('#public_slug').addClass('is-invalid');
              $('#public_slug').siblings('.invalid-feedback').text('{{ $l == "en" ? "Public URL must be at least 3 characters" : "الرابط العام يجب أن يكون 3 أحرف على الأقل" }}');
              isValid = false;
            }
          }
          
          // Validate hours reminder when reminder is enabled
          const reminderEnabled = $('#reminder_enabled').is(':checked');
          const hoursReminder = $('#hours_reminder_input').val();
          
          if (reminderEnabled && (!hoursReminder || parseInt(hoursReminder) < 1)) {
            $('#hours_reminder_input').addClass('is-invalid');
            $('#hours_reminder_input').siblings('.invalid-feedback').text('{{ $l == "en" ? "Hours reminder must be at least 1 hour" : "تذكير الساعات يجب أن يكون ساعة واحدة على الأقل" }}');
            isValid = false;
          }

          // Validate branding colors
          const startColor = $('#start_color_input').val();
          const endColor = $('#end_color_input').val();
          const mainColor = $('#main_color_input').val();
          const colorRegex = /^#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/;

          // Start Color
          if (!startColor || !colorRegex.test(startColor.replace(/^#+/, '#'))) {
            $('.start-color-error').text('{{ $l == "en" ? "Please enter a valid start color." : "يرجى إدخال لون صالح." }}').show();
            isValid = false;
          }

          // End Color
          if (!endColor || !colorRegex.test(endColor.replace(/^#+/, '#'))) {
            $('.end-color-error').text('{{ $l == "en" ? "Please enter a valid end color." : "يرجى إدخال لون صالح." }}').show();
            isValid = false;
          }

          // Main Color
          if (!mainColor || !colorRegex.test(mainColor.replace(/^#+/, '#'))) {
            $('.main-color-error').text('{{ $l == "en" ? "Please enter a valid button color." : "يرجى إدخال لون صالح." }}').show();
            isValid = false;
          }
          
          return isValid;
        },
        
        // 'custom_pages': function() {
          // Custom pages validation - this tab is optional
          // return true;
        // }
      };

      // Function to validate current tab
      function validateCurrentTab(tabId) {
        if (tabValidations[tabId]) {
          return tabValidations[tabId]();
        }
        return true;
      }

      // Function to enable/disable tabs
      function updateTabStates() {
        tabIds.forEach((tabId, index) => {
          const tabButton = $(`#pills-${tabId}-tab, #pills-${tabId}-tab-edit`);
          if (index <= currentTabIndex) {
            tabButton.removeClass('tab-disabled').prop('disabled', false);
          } else {
            tabButton.addClass('tab-disabled').prop('disabled', true);
          }
        });
      }

      // Function to navigate to tab
      function navigateToTab(targetIndex, validateCurrent = true) {
        const currentTabId = tabIds[currentTabIndex];
        
        // Validate current tab before moving (if required)
        if (validateCurrent && !validateCurrentTab(currentTabId)) {
          // Scroll to first error
          const firstError = $('.is-invalid').first();
          if (firstError.length) {
            $('html, body').animate({
              scrollTop: firstError.offset().top - 100
            }, 500);
          }
          
          // Show error message
          if (typeof toastr !== 'undefined') {
            toastr.error('{{ $l == "en" ? "Please fix the errors in this tab before proceeding" : "يرجى إصلاح الأخطاء في هذه الصفحة قبل المتابعة" }}');
          }
          return false;
        }

        // Check if trying to skip ahead to publish tab without completing all previous tabs
        if (targetIndex === totalTabs - 1 && targetIndex > currentTabIndex + 1) {
          // Find first incomplete tab
          let firstIncompleteTab = -1;
          for (let i = 0; i < totalTabs - 1; i++) {
            if (!validateCurrentTab(tabIds[i])) {
              firstIncompleteTab = i;
              break;
            }
          }
          
          if (firstIncompleteTab >= 0) {
            // Navigate to first incomplete tab
            const targetTabId = tabIds[firstIncompleteTab];
            
            // Remove active class from all tabs and content
            $('.nav-link[data-bs-toggle="pill"]').removeClass('active').attr('aria-selected', 'false');
            $('.tab-pane').removeClass('show active');
            
            // Add active class to target tab and content
            $(`#pills-${targetTabId}-tab, #pills-${targetTabId}-tab-edit`).addClass('active').attr('aria-selected', 'true');
            $(`#pills-${targetTabId}-edit`).addClass('show active');
            
            currentTabIndex = firstIncompleteTab;
            $('#current_tab_id').val(firstIncompleteTab + 1);
            updateTabStates();
            
            if (typeof toastr !== 'undefined') {
              toastr.warning('{{ $l == "en" ? "Please complete all required fields in previous tabs before publishing" : "يرجى إكمال جميع الحقول المطلوبة في الصفحات السابقة قبل النشر" }}');
            }
            
            return false;
          }
        }

        // Navigate to target tab
        const targetTabId = tabIds[targetIndex];
        
        // Remove active class from all tabs and content
        $('.nav-link[data-bs-toggle="pill"]').removeClass('active').attr('aria-selected', 'false');
        $('.tab-pane').removeClass('show active');
        
        // Add active class to target tab and content (both desktop and mobile versions)
        $(`#pills-${targetTabId}-tab, #pills-${targetTabId}-tab-edit`).addClass('active').attr('aria-selected', 'true');
        $(`#pills-${targetTabId}-edit`).addClass('show active');
        
        currentTabIndex = targetIndex;
        $('#current_tab_id').val(targetIndex + 1);
        updateTabStates();
        
        return true;
      }

      // Previous button click handler
      $(document).on('click', '.prev-tab-btn', function() {
        const currentTab = parseInt($(this).data('current-tab'));
        if (currentTab > 0) {
          navigateToTab(currentTab - 1, false); // Don't validate when going back
        }
      });

      // Save & Next button click handler
      $(document).on('click', '.save-next-btn', function() {
        const currentTab = parseInt($(this).data('current-tab'));
        const currentTabId = tabIds[currentTab];
        
        if (validateCurrentTab(currentTabId)) {
          // Save current progress (you can implement auto-save here if needed)
          if( currentTab == 1 )
          {
            submitProgram();
          }
          else
          {
            navigateToTab(currentTab + 1, false);
          }
        }
      });

      function submitProgram() {
        const currentTab = parseInt($(this).data('current-tab'));
        
        // Validate all tabs before publishing
        let allTabsValid = true;
        let firstInvalidTab = -1;
        
        for (let i = 0; i < totalTabs; i++) {
          // Temporarily switch to each tab to validate
          const tabId = tabIds[i];
          const tempTabIndex = currentTabIndex;
          currentTabIndex = i;
          
          if (!validateCurrentTab(tabId)) {
            if (firstInvalidTab === -1) {
              firstInvalidTab = i;
            }
            allTabsValid = false;
          }
          
          currentTabIndex = tempTabIndex;
        }
        
        if (!allTabsValid && firstInvalidTab >= 0) {
          // Navigate to first invalid tab
          navigateToTab(firstInvalidTab, false);
          
          if (typeof toastr !== 'undefined') {
            toastr.error('{{ $l == "en" ? "Please complete all required fields before publishing" : "يرجى إكمال جميع الحقول المطلوبة قبل النشر" }}');
          }
          return;
        }
        
        // All validations passed, set action and submit the form
        $('#action_type').val('create'); // Use 'create' instead of 'publish' for new programs
        
        // Make sure cycleid is set to 0 for new programs
        $('#cycleid').val('0');
        
        // Submit the form
        $('#hidden-submit-btn').click();
      }
      // Publish button click handler
      $(document).on('click', '.publish-btn', function() {
        submitProgram();
      });

      // Tab click handler - prevent direct navigation to disabled tabs
      $(document).on('click', '[data-bs-toggle="pill"]', function(e) {
        const targetIndex = parseInt($(this).data('tab-index'));
        
        if ($(this).hasClass('tab-disabled')) {
          e.preventDefault();
          e.stopPropagation();
          
          if (typeof toastr !== 'undefined') {
            toastr.warning('{{ $l == "en" ? "Please complete the previous tabs first" : "يرجى إكمال الصفحات السابقة أولاً" }}');
          }
          return false;
        }
        
        // Update current tab index when manually switching tabs
        if (targetIndex !== undefined && targetIndex <= currentTabIndex + 1) {
          // Allow the natural Bootstrap tab switching to happen
          // But update our internal state
          setTimeout(function() {
            currentTabIndex = targetIndex;
            $('#current_tab_id').val(targetIndex + 1);
            updateTabStates();
          }, 50);
        }
      });

      // Form submit handler
      $('#programForm').on('submit', function(e) {
        const actionType = $('#action_type').val();
        
        // For new program creation, always use 'save' or 'create' action type
        if (actionType === 'publish' || actionType === 'create') {
          // Final validation before submit
          let allTabsValid = true;
          
          for (let i = 0; i < totalTabs; i++) {
            const tabId = tabIds[i];
            const tempTabIndex = currentTabIndex;
            currentTabIndex = i;
            
            if (!validateCurrentTab(tabId)) {
              allTabsValid = false;
              navigateToTab(i, false);
              break;
            }
            
            currentTabIndex = tempTabIndex;
          }
          
          if (!allTabsValid) {
            e.preventDefault();
            if (typeof toastr !== 'undefined') {
              toastr.error('{{ $l == "en" ? "Please fix all validation errors before publishing" : "يرجى إصلاح جميع أخطاء التحقق قبل النشر" }}');
            }
            return false;
          }
          
          // Ensure cycleid is set correctly for new programs
          $('#cycleid').val('0');
        }
      });

      $(document).on('change', '#program_step_type', function(e) { initStepTypeHelpText(); });

      function initStepTypeHelpText()
      {
        var selectedType = $('#program_step_type').val();
        var helpText = '';
        if( selectedType == 'cycle' ) { helpText = "{{ __('cycles.type_by_cycle') }} : {{ __('cycles.type_by_cycle_helper') }}"; }
        else if( selectedType == 'time' ) { helpText = "{{ __('cycles.type_by_time') }} : {{ __('cycles.type_by_time_helper') }}"; }

        $('#program_step_type_help').html(helpText);
      }

      // Initialize tab states
      updateTabStates();
      
      // Set initial tab
      currentTabIndex = 0;
      $('#current_tab_id').val(1);
      initStepTypeHelpText();
    });
  </script>
@stop

