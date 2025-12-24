@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
  <style>
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

    /* intl-tel-input styling */
    .iti {
      width: 100%;
    }
    .iti__country-list {
      z-index: 9999;
      max-height: 200px;
    }
  </style>
@stop

@php
  $isEdit = isset($organization);
  $maxStep = $isEdit ? ($organization->wizard_step ?? 1) : 1;
  $isDraft = $isEdit ? $organization->is_draft : true;
  $hasManagers = $isEdit ? $organization->assignedUsers()->count() > 0 : false;
  $canPublish = $isDraft && $hasManagers;

  // Get requested step from URL, default to 1
  $requestedStep = request()->get('step', 1);
  // Active step is the requested step if it's <= maxStep, otherwise default to 1
  $activeStep = ($requestedStep <= $maxStep) ? (int)$requestedStep : 1;

  $labels = [
    'organization_basics' => $l == 'ar' ? 'المعلومات الأساسية' : 'Organization Basics',
    'contact_information' => $l == 'ar' ? 'معلومات الاتصال' : 'Contact Information',
    'organization_manager' => $l == 'ar' ? 'مدير المؤسسة' : 'Organization Manager',
    'landing_page' => $l == 'ar' ? 'الصفحة الرئيسية' : 'Landing Page',
    'custom_pages' => $l == 'ar' ? 'صفحات مخصصة' : 'Custom Pages',
  ];

  $tabs = [
    ['id' => 'organization_basics', 'label' => $labels['organization_basics'], 'step' => 1],
    ['id' => 'contact_information', 'label' => $labels['contact_information'], 'step' => 2],
    ['id' => 'organization_manager', 'label' => $labels['organization_manager'], 'step' => 3],
    ['id' => 'landing_page', 'label' => $labels['landing_page'], 'step' => 4],
    ['id' => 'custom_pages', 'label' => $labels['custom_pages'], 'step' => 5],
  ];
@endphp

@section('content')
<div class="content-wrapper">
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
      <h4 class="fw-bold mb-4">
        @if($isEdit)
          {{ __('admin.Edit Organization') }}
        @else
          {{ __('admin.Create Organization') }}
        @endif
      </h4>

      @if (session()->has('success'))
        <x-alert type="success" message="{{ session()->get('success') }}" />
      @endif
      @if (session()->has('error'))
        <x-alert type="danger" message="{{ session()->get('error') }}" />
      @endif
      @if ($errors->any())
        @foreach ($errors->all() as $error)
          <x-alert type="danger" message="{{ $error }}" />
        @endforeach
      @endif
    </div>

    <div class="card p-4 p-md-8">
      <form
        action="{{ $isEdit ? route('organization.wizard.update.step1', $organization->id) : route('organization.wizard.store.step1') }}"
        method="POST"
        enctype="multipart/form-data"
        id="organizationWizardForm"
      >
        @csrf
        @if($isEdit)
          @method('PUT')
        @endif
        <input type="hidden" name="current_step" id="current_step" value="{{ $activeStep }}">
        <input type="hidden" name="action_type" id="action_type" value="save_continue">

        <!-- Tabs (mobile) -->
        <div style="max-width: 100%; overflow-x: auto;" class="mb-4 d-md-none pb-3">
          <ul class="nav nav-pills flex-nowrap" role="tablist">
            @foreach ($tabs as $key => $tab)
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link {{ $tab['step'] == $activeStep ? 'active' : '' }} {{ $tab['step'] > $maxStep ? 'tab-disabled' : '' }}"
                  id="pills-{{ $tab['id'] }}-tab-mobile"
                  data-bs-toggle="pill"
                  data-bs-target="#pills-{{ $tab['id'] }}"
                  type="button"
                  role="tab"
                  aria-controls="pills-{{ $tab['id'] }}"
                  aria-selected="{{ $tab['step'] == $activeStep ? 'true' : 'false' }}"
                  data-tab-index="{{ $key }}"
                  data-step="{{ $tab['step'] }}"
                  {{ $tab['step'] > $maxStep ? 'disabled' : '' }}
                >
                  <span class="fw-semibold">{{ $tab['label'] }}</span>
                </button>
              </li>
            @endforeach
          </ul>
        </div>

        <!-- Tabs (desktop) -->
        <ul class="nav nav-pills d-none d-md-flex" id="pills-tab" role="tablist">
          @foreach ($tabs as $key => $tab)
            <li class="nav-item me-2" role="presentation">
              <button
                class="nav-link {{ $tab['step'] == $activeStep ? 'active' : '' }} {{ $tab['step'] > $maxStep ? 'tab-disabled' : '' }}"
                id="pills-{{ $tab['id'] }}-tab"
                data-bs-toggle="pill"
                data-bs-target="#pills-{{ $tab['id'] }}"
                type="button"
                role="tab"
                aria-controls="pills-{{ $tab['id'] }}"
                aria-selected="{{ $tab['step'] == $activeStep ? 'true' : 'false' }}"
                data-tab-index="{{ $key }}"
                data-step="{{ $tab['step'] }}"
                {{ $tab['step'] > $maxStep ? 'disabled' : '' }}
              >
                <span class="fw-semibold">{{ $tab['label'] }}</span>
              </button>
            </li>
          @endforeach
        </ul>
        <hr />

        <div class="tab-content shadow-none p-4" id="pills-tabContent">
          <!-- Step 1: Organization Basics -->
          <div class="tab-pane fade {{ $activeStep == 1 ? 'show active' : '' }}" id="pills-organization_basics" role="tabpanel" aria-labelledby="pills-organization_basics-tab" data-tab-index="0">
            @include('admin.organizations.wizard.partials._step1_basics', ['organization' => $organization ?? null])

            <!-- Navigation Buttons -->
            <div class="d-flex align-items-center justify-content-between gap-3 mt-4 pt-4 border-top">
              @if($isDraft)
                {{-- Draft Organization Buttons --}}
                <div>
                  <button type="button" class="btn btn-label-secondary save-draft-btn" id="saveDraftBtn">
                    <i class="ti ti-device-floppy me-2"></i>
                    {{ __('admin.Save as Draft') }}
                  </button>
                </div>

                <div class="d-flex gap-2">
                  @if($canPublish)
                    <button type="button" class="btn btn-success" id="publishBtn">
                      <i class="ti ti-world me-2"></i>
                      {{ __('admin.Publish') }}
                    </button>
                  @endif
                  <button type="submit" class="btn btn-primary save-continue-btn">
                    {{ __('admin.Save & Continue') }}
                    <i class="ti ti-arrow-{{ $l == 'en' ? 'right' : 'left' }} ms-2"></i>
                  </button>
                </div>
              @else
                {{-- Published Organization Buttons --}}
                <div></div>
                <div>
                  <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-2"></i>
                    {{ __('admin.Save Organization') }}
                  </button>
                </div>
              @endif
            </div>
          </div>

          <!-- Step 2: Contact Information -->
          <div class="tab-pane fade {{ $activeStep == 2 ? 'show active' : '' }}" id="pills-contact_information" role="tabpanel" aria-labelledby="pills-contact_information-tab" data-tab-index="1">
            @include('admin.organizations.wizard.partials._step2_contact', ['organization' => $organization ?? null])

            <!-- Navigation Buttons for Step 2 -->
            <div class="d-flex align-items-center justify-content-between gap-3 mt-4 pt-4 border-top">
              @if($isDraft)
                {{-- Draft Organization Buttons --}}
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-label-secondary prev-step-btn" data-target-step="0">
                    <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i>
                    {{ __('admin.Previous Step') }}
                  </button>
                  <button type="button" class="btn btn-label-secondary save-draft-btn" id="saveDraftBtn2">
                    <i class="ti ti-device-floppy me-2"></i>
                    {{ __('admin.Save as Draft') }}
                  </button>
                </div>

                <div class="d-flex gap-2">
                  @if($canPublish)
                    <button type="button" class="btn btn-success publish-btn">
                      <i class="ti ti-world me-2"></i>
                      {{ __('admin.Publish') }}
                    </button>
                  @endif
                  <button type="button" class="btn btn-primary save-continue-step2-btn">
                    {{ __('admin.Save & Continue') }}
                    <i class="ti ti-arrow-{{ $l == 'en' ? 'right' : 'left' }} ms-2"></i>
                  </button>
                </div>
              @else
                {{-- Published Organization Buttons --}}
                <div>
                  <button type="button" class="btn btn-label-secondary prev-step-btn" data-target-step="0">
                    <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i>
                    {{ __('admin.Previous Step') }}
                  </button>
                </div>
                <div>
                  <button type="button" class="btn btn-primary save-step2-btn">
                    <i class="ti ti-device-floppy me-2"></i>
                    {{ __('admin.Save Organization') }}
                  </button>
                </div>
              @endif
            </div>
          </div>

          <!-- Step 3: Organization Manager (Coming Soon) -->
          <div class="tab-pane fade {{ $activeStep == 3 ? 'show active' : '' }}" id="pills-organization_manager" role="tabpanel" aria-labelledby="pills-organization_manager-tab" data-tab-index="2">
            <div class="text-center py-5">
              <i class="ti ti-user-cog display-1 text-muted"></i>
              <h5 class="mt-3 text-muted">{{ __('admin.Coming Soon') }}</h5>
              <p class="text-muted">{{ __('admin.This step will be available in a future update') }}</p>
            </div>
          </div>

          <!-- Step 4: Landing Page (Coming Soon) -->
          <div class="tab-pane fade {{ $activeStep == 4 ? 'show active' : '' }}" id="pills-landing_page" role="tabpanel" aria-labelledby="pills-landing_page-tab" data-tab-index="3">
            <div class="text-center py-5">
              <i class="ti ti-layout display-1 text-muted"></i>
              <h5 class="mt-3 text-muted">{{ __('admin.Coming Soon') }}</h5>
              <p class="text-muted">{{ __('admin.This step will be available in a future update') }}</p>
            </div>
          </div>

          <!-- Step 5: Custom Pages (Coming Soon) -->
          <div class="tab-pane fade {{ $activeStep == 5 ? 'show active' : '' }}" id="pills-custom_pages" role="tabpanel" aria-labelledby="pills-custom_pages-tab" data-tab-index="4">
            <div class="text-center py-5">
              <i class="ti ti-files display-1 text-muted"></i>
              <h5 class="mt-3 text-muted">{{ __('admin.Coming Soon') }}</h5>
              <p class="text-muted">{{ __('admin.This step will be available in a future update') }}</p>
            </div>
          </div>
        </div>
      </form>

      <!-- Draft Form (separate form for draft saving) -->
      <form
        action="{{ $isEdit ? route('organization.wizard.draft.update', $organization->id) : route('organization.wizard.draft') }}"
        method="POST"
        enctype="multipart/form-data"
        id="draftForm"
        style="display: none;"
      >
        @csrf
        @if($isEdit)
          @method('PUT')
        @endif
      </form>

      @if($isEdit)
        <!-- Step 2 Form (separate form for step 2 submission) -->
        <form
          action="{{ route('organization.wizard.update.step2', $organization->id) }}"
          method="POST"
          id="step2Form"
          style="display: none;"
        >
          @csrf
          @method('PUT')
        </form>
      @endif

      @if($isEdit && $isDraft)
        <!-- Publish Form -->
        <form
          action="{{ route('organization.wizard.publish', $organization->id) }}"
          method="POST"
          id="publishForm"
          style="display: none;"
        >
          @csrf
        </form>
      @endif
    </div>
  </div>
</div>
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/select2/select2.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

  <script>
    $(document).ready(function() {
      const lang = '{{ $l }}';

      // Initialize Select2
      $('.select2').select2({
        placeholder: function() {
          return $(this).data('placeholder');
        },
        allowClear: true
      });

      // Initialize industries multi-select
      $('#industries').select2({
        placeholder: '{{ __("admin.Select Industries") }}',
        allowClear: true,
        multiple: true
      });

      // Initialize intl-tel-input for phone
      const phoneInput = document.getElementById('contact_phone');
      const phoneError = document.getElementById('phone-error');
      const countryCodeInput = document.getElementById('country_code');
      let iti = null;

      if (phoneInput) {
        // Determine initial country - default to 'eg' (Egypt)
        let initialCountry = 'eg';

        @if(isset($organization) && $organization->country_code)
          // Find ISO2 code from saved dial code
          const savedDialCode = '{{ $organization->country_code }}'.replace('+', '');
          // Map common dial codes to ISO2 (for initial setting before utils loads)
          const dialCodeMap = {
            '20': 'eg',   // Egypt
            '971': 'ae',  // UAE
            '966': 'sa',  // Saudi Arabia
            '212': 'ma',  // Morocco
            '213': 'dz',  // Algeria
            '216': 'tn',  // Tunisia
            '962': 'jo',  // Jordan
            '965': 'kw',  // Kuwait
            '968': 'om',  // Oman
            '974': 'qa',  // Qatar
            '973': 'bh',  // Bahrain
            '961': 'lb',  // Lebanon
            '963': 'sy',  // Syria
            '964': 'iq',  // Iraq
            '967': 'ye',  // Yemen
            '970': 'ps',  // Palestine
            '218': 'ly',  // Libya
            '249': 'sd',  // Sudan
            '1': 'us',    // USA/Canada
            '44': 'gb',   // UK
          };
          if (dialCodeMap[savedDialCode]) {
            initialCountry = dialCodeMap[savedDialCode];
          }
        @endif

        iti = intlTelInput(phoneInput, {
          initialCountry: initialCountry,
          preferredCountries: ['eg', 'ae', 'sa', 'ma', 'dz', 'tn'],
          utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js',
          formatOnDisplay: false,
          separateDialCode: true,
          autoPlaceholder: 'aggressive',
        });

        // Update country code when country changes
        phoneInput.addEventListener('countrychange', function() {
          const countryData = iti.getSelectedCountryData();
          countryCodeInput.value = countryData.dialCode;
        });

        // Set initial country code value if not already set
        setTimeout(function() {
          if (iti) {
            const countryData = iti.getSelectedCountryData();
            if (countryData.dialCode && !countryCodeInput.value) {
              countryCodeInput.value = countryData.dialCode;
            }
          }
        }, 100);

        // Validate phone on input
        phoneInput.addEventListener('input', function() {
          if (phoneInput.value.trim() && !iti.isValidNumber()) {
            phoneError.style.display = 'block';
            phoneError.innerText = lang === 'en' ? 'Invalid phone number' : 'رقم الهاتف غير صحيح';
          } else {
            phoneError.style.display = 'none';
          }
        });
      }

      // Tab state management
      let maxStep = {{ $maxStep }};
      const isDraft = {{ $isDraft ? 'true' : 'false' }};

      // Prevent navigation to disabled tabs
      $(document).on('click', '[data-bs-toggle="pill"]', function(e) {
        if ($(this).hasClass('tab-disabled') || $(this).is(':disabled')) {
          e.preventDefault();
          e.stopPropagation();

          if (typeof toastr !== 'undefined') {
            toastr.warning('{{ __("admin.Please complete the previous steps first") }}');
          }
          return false;
        }
      });

      // Logo preview
      $('#img_url').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
          // Validate file type
          const validTypes = ['image/png', 'image/jpeg', 'image/jpg'];
          if (!validTypes.includes(file.type)) {
            if (typeof toastr !== 'undefined') {
              toastr.error('{{ __("admin.logo_invalid_format") }}');
            }
            $(this).val('');
            return;
          }

          // Validate file size (5MB)
          if (file.size > 5 * 1024 * 1024) {
            if (typeof toastr !== 'undefined') {
              toastr.error('{{ __("admin.logo_too_large") }}');
            }
            $(this).val('');
            return;
          }

          // Show preview
          const reader = new FileReader();
          reader.onload = function(e) {
            $('#logoPreview').attr('src', e.target.result);
          };
          reader.readAsDataURL(file);
        }
      });

      // Save as Draft button (Step 1)
      $('#saveDraftBtn').on('click', function(e) {
        e.preventDefault();

        // Copy form data to draft form
        const mainForm = $('#organizationWizardForm');
        const draftForm = $('#draftForm');

        // Clear and copy all inputs
        draftForm.find('input:not([name="_token"]):not([name="_method"])').remove();

        // Set current step to 1 for Step 1 save
        draftForm.append('<input type="hidden" name="current_step" value="1">');

        mainForm.find('input, select, textarea').each(function() {
          const $this = $(this);
          const name = $this.attr('name');
          const type = $this.attr('type');

          if (!name || name === '_token' || name === '_method' || name === 'current_step') return;

          if (type === 'file') {
            // Handle file input
            if ($this[0].files.length > 0) {
              const fileInput = $('<input type="file" name="' + name + '">');
              fileInput[0].files = $this[0].files;
              draftForm.append(fileInput);
            }
          } else if ($this.is('select[multiple]')) {
            // Handle multi-select
            const values = $this.val() || [];
            values.forEach(function(val) {
              draftForm.append('<input type="hidden" name="' + name + '" value="' + val + '">');
            });
          } else {
            draftForm.append('<input type="hidden" name="' + name + '" value="' + $this.val() + '">');
          }
        });

        // Submit draft form
        draftForm.submit();
      });

      // Publish button
      $('#publishBtn').on('click', function(e) {
        e.preventDefault();

        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: '{{ __("admin.Publish Organization") }}',
            text: '{{ __("admin.Are you sure you want to publish this organization? This action cannot be undone.") }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ __("admin.Yes, Publish") }}',
            cancelButtonText: '{{ __("admin.Cancel") }}',
            customClass: {
              confirmButton: 'btn btn-success me-2',
              cancelButton: 'btn btn-label-secondary',
            },
            buttonsStyling: false,
          }).then((result) => {
            if (result.isConfirmed) {
              $('#publishForm').submit();
            }
          });
        } else {
          if (confirm('{{ __("admin.Are you sure you want to publish this organization?") }}')) {
            $('#publishForm').submit();
          }
        }
      });

      // Form validation for Step 1
      function validateStep1() {
        let isValid = true;

        // Clear previous errors
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('').removeClass('d-block');

        // Validate Organization Name (English)
        const nameEn = $('#name_en');
        if (!nameEn.val().trim()) {
          nameEn.addClass('is-invalid');
          nameEn.siblings('.invalid-feedback').text('{{ __("admin.organization_name_required") }}').addClass('d-block');
          isValid = false;
        } else if (nameEn.val().trim().length > 255) {
          nameEn.addClass('is-invalid');
          nameEn.siblings('.invalid-feedback').text('{{ __("admin.Organization name must not exceed 255 characters") }}').addClass('d-block');
          isValid = false;
        }

        // Validate Organization Name (Arabic)
        const nameAr = $('#name_ar');
        if (!nameAr.val().trim()) {
          nameAr.addClass('is-invalid');
          nameAr.siblings('.invalid-feedback').text('{{ __("admin.organization_name_required") }}').addClass('d-block');
          isValid = false;
        } else if (nameAr.val().trim().length > 255) {
          nameAr.addClass('is-invalid');
          nameAr.siblings('.invalid-feedback').text('{{ __("admin.Organization name must not exceed 255 characters") }}').addClass('d-block');
          isValid = false;
        }

        // Validate Organization Type
        const orgType = $('#organization_type_id');
        if (!orgType.val()) {
          orgType.addClass('is-invalid');
          orgType.next('.select2-container').addClass('is-invalid');
          orgType.siblings('.invalid-feedback').text('{{ __("admin.organization_type_required") }}').addClass('d-block');
          isValid = false;
        }

        // Validate Status
        const status = $('#status');
        if (status.val() === '' || status.val() === null) {
          status.addClass('is-invalid');
          status.siblings('.invalid-feedback').text('{{ __("admin.status_required") }}').addClass('d-block');
          isValid = false;
        }

        // Validate Description
        const bio = $('#bio');
        if (!bio.val().trim()) {
          bio.addClass('is-invalid');
          bio.siblings('.invalid-feedback').text('{{ __("admin.organization_description_required") }}').addClass('d-block');
          isValid = false;
        } else if (bio.val().trim().length > 1000) {
          bio.addClass('is-invalid');
          bio.siblings('.invalid-feedback').text('{{ __("admin.Organization description must not exceed 1000 characters") }}').addClass('d-block');
          isValid = false;
        }

        return isValid;
      }

      // Form submission validation
      $('#organizationWizardForm').on('submit', function(e) {
        if (!validateStep1()) {
          e.preventDefault();

          // Scroll to first error
          const firstError = $('.is-invalid').first();
          if (firstError.length) {
            $('html, body').animate({
              scrollTop: firstError.offset().top - 100
            }, 500);
          }

          if (typeof toastr !== 'undefined') {
            toastr.error('{{ __("admin.Please fix the errors before continuing") }}');
          }
          return false;
        }
      });

      // Character counter for description
      $('#bio').on('input', function() {
        const maxLength = 1000;
        const currentLength = $(this).val().length;
        const remaining = maxLength - currentLength;

        $('#bioCharCount').text(currentLength + ' / ' + maxLength);

        if (remaining < 0) {
          $('#bioCharCount').addClass('text-danger');
        } else if (remaining < 100) {
          $('#bioCharCount').removeClass('text-danger').addClass('text-warning');
        } else {
          $('#bioCharCount').removeClass('text-danger text-warning');
        }
      });

      // Trigger character count on load
      $('#bio').trigger('input');

      // Character counter for address
      $('#address').on('input', function() {
        const maxLength = 500;
        const currentLength = $(this).val().length;

        $('#addressCharCount').text(currentLength + ' / ' + maxLength);

        if (currentLength > maxLength) {
          $('#addressCharCount').addClass('text-danger');
        } else if (maxLength - currentLength < 50) {
          $('#addressCharCount').removeClass('text-danger').addClass('text-warning');
        } else {
          $('#addressCharCount').removeClass('text-danger text-warning');
        }
      });
      $('#address').trigger('input');

      // Previous Step button handler - use Bootstrap Tab API
      $(document).on('click', '.prev-step-btn', function(e) {
        e.preventDefault();
        const targetStep = $(this).data('target-step');

        // Find visible tab button for the target step
        let targetTabBtn = null;

        // Try desktop version first (visible on md and up)
        const desktopTab = $('#pills-tab [data-tab-index="' + targetStep + '"]');
        if (desktopTab.length && desktopTab.is(':visible')) {
          targetTabBtn = desktopTab[0];
        } else {
          // Fall back to mobile version
          const mobileTab = $('[data-tab-index="' + targetStep + '"][id$="-mobile"]');
          if (mobileTab.length) {
            targetTabBtn = mobileTab[0];
          }
        }

        // Use Bootstrap Tab API to show the tab
        if (targetTabBtn && !$(targetTabBtn).hasClass('tab-disabled')) {
          const tab = new bootstrap.Tab(targetTabBtn);
          tab.show();
        }
      });

      // Helper to reinitialize Select2 with options
      function reinitSelect2(element, placeholder) {
        const $el = $(element);
        // Destroy existing Select2 if initialized
        if ($el.hasClass('select2-hidden-accessible')) {
          $el.select2('destroy');
        }
        // Reinitialize with placeholder
        $el.select2({
          placeholder: placeholder || $el.data('placeholder'),
          allowClear: true
        });
      }

      // Country → State cascade
      $('#country_id').on('change', function() {
        const countryId = $(this).val();
        const stateSelect = $('#state_id');
        const citySelect = $('#city_id');

        // Clear and reset state and city dropdowns
        stateSelect.html('<option value=""></option>');
        citySelect.html('<option value=""></option>');
        reinitSelect2(stateSelect, '{{ __("admin.Select State") }}');
        reinitSelect2(citySelect, '{{ __("admin.Select City") }}');

        if (!countryId) return;

        // Fetch states - API returns {id: name, id: name, ...} format
        $.ajax({
          url: '{{ route("utils.locations_list_states_by_country") }}',
          type: 'GET',
          data: { country_id: countryId },
          success: function(data) {
            // Build options HTML
            let optionsHtml = '<option value=""></option>';
            $.each(data, function(id, name) {
              optionsHtml += '<option value="' + id + '">' + name + '</option>';
            });
            stateSelect.html(optionsHtml);
            reinitSelect2(stateSelect, '{{ __("admin.Select State") }}');
          },
          error: function(xhr, status, error) {
            console.error('Error loading states:', error);
            if (typeof toastr !== 'undefined') {
              toastr.error('{{ __("admin.Error loading states") }}');
            }
          }
        });
      });

      // State → City cascade
      $('#state_id').on('change', function() {
        const stateId = $(this).val();
        const citySelect = $('#city_id');

        // Clear city dropdown
        citySelect.html('<option value=""></option>');
        reinitSelect2(citySelect, '{{ __("admin.Select City") }}');

        if (!stateId) return;

        // Fetch cities - API returns {id: name, id: name, ...} format
        $.ajax({
          url: '{{ route("cities.index") }}',
          type: 'GET',
          data: { state_id: stateId },
          success: function(data) {
            // Build options HTML
            let optionsHtml = '<option value=""></option>';
            $.each(data, function(id, name) {
              optionsHtml += '<option value="' + id + '">' + name + '</option>';
            });
            citySelect.html(optionsHtml);
            reinitSelect2(citySelect, '{{ __("admin.Select City") }}');
          },
          error: function(xhr, status, error) {
            console.error('Error loading cities:', error);
            if (typeof toastr !== 'undefined') {
              toastr.error('{{ __("admin.Error loading cities") }}');
            }
          }
        });
      });

      // Step 2 validation
      function validateStep2() {
        let isValid = true;

        // Clear previous errors
        $('#pills-contact_information .form-control, #pills-contact_information .form-select').removeClass('is-invalid');
        $('#pills-contact_information .invalid-feedback').text('').removeClass('d-block');

        // Email validation
        const email = $('#contact_email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.val().trim()) {
          email.addClass('is-invalid');
          email.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please enter the email address") }}').addClass('d-block');
          isValid = false;
        } else if (!emailRegex.test(email.val().trim())) {
          email.addClass('is-invalid');
          email.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please enter a valid email address") }}').addClass('d-block');
          isValid = false;
        }

        // Phone validation (optional, but if provided, country code is required)
        const phone = $('#contact_phone');
        const countryCode = $('#country_code');
        if (phone.val().trim() && !countryCode.val()) {
          countryCode.addClass('is-invalid');
          phone.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please select the country code") }}').addClass('d-block');
          isValid = false;
        }

        // Country validation
        const country = $('#country_id');
        if (!country.val()) {
          country.addClass('is-invalid');
          country.siblings('.invalid-feedback').text('{{ __("admin.Please select the country") }}').addClass('d-block');
          isValid = false;
        }

        // State validation
        const state = $('#state_id');
        if (!state.val()) {
          state.addClass('is-invalid');
          state.siblings('.invalid-feedback').text('{{ __("admin.Please select the state") }}').addClass('d-block');
          isValid = false;
        }

        // City validation
        const city = $('#city_id');
        if (!city.val()) {
          city.addClass('is-invalid');
          city.siblings('.invalid-feedback').text('{{ __("admin.Please select the city") }}').addClass('d-block');
          isValid = false;
        }

        // Address validation
        const address = $('#address');
        if (!address.val().trim()) {
          address.addClass('is-invalid');
          address.siblings('.invalid-feedback').text('{{ __("admin.Please enter the address") }}').addClass('d-block');
          isValid = false;
        } else if (address.val().trim().length > 500) {
          address.addClass('is-invalid');
          address.siblings('.invalid-feedback').text('{{ __("admin.Address must not exceed 500 characters") }}').addClass('d-block');
          isValid = false;
        }

        // Google Maps URL validation
        const googleMapUrl = $('#google_map_url');
        const googleMapsRegex = /^https?:\/\/(www\.)?(google\.[a-z]{2,3}(\/maps)?|maps\.google\.[a-z]{2,3}|goo\.gl|maps\.app\.goo\.gl)/i;
        if (!googleMapUrl.val().trim()) {
          googleMapUrl.addClass('is-invalid');
          googleMapUrl.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please enter the Google Maps URL") }}').addClass('d-block');
          isValid = false;
        } else if (!googleMapsRegex.test(googleMapUrl.val().trim())) {
          googleMapUrl.addClass('is-invalid');
          googleMapUrl.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please enter a valid Google Maps URL") }}').addClass('d-block');
          isValid = false;
        }

        // URL validation for social media (optional fields)
        const urlFields = [
          'linkedin_company_url',
          'twitter_handle',
          'facebook_page_url',
          'instagram_profile_url',
          'youtube_channel_url',
          'tiktok_url'
        ];
        const urlRegex = /^https?:\/\/.+/i;

        urlFields.forEach(function(fieldId) {
          const field = $('#' + fieldId);
          if (field.val().trim() && !urlRegex.test(field.val().trim())) {
            field.addClass('is-invalid');
            field.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please enter a valid URL") }}').addClass('d-block');
            isValid = false;
          }
        });

        return isValid;
      }

      // Save & Continue for Step 2
      $(document).on('click', '.save-continue-step2-btn, .save-step2-btn', function(e) {
        e.preventDefault();

        if (!validateStep2()) {
          // Scroll to first error
          const firstError = $('#pills-contact_information .is-invalid').first();
          if (firstError.length) {
            $('html, body').animate({
              scrollTop: firstError.offset().top - 100
            }, 500);
          }

          if (typeof toastr !== 'undefined') {
            toastr.error('{{ __("admin.Please fix the errors before continuing") }}');
          }
          return false;
        }

        // Submit step 2 form
        const step2Form = $('#step2Form');

        // Clear and copy all step 2 inputs
        step2Form.find('input:not([name="_token"]):not([name="_method"])').remove();

        // Get all step 2 fields
        const step2Fields = [
          'contact_email', 'country_code', 'contact_phone',
          'country_id', 'state_id', 'city_id', 'address', 'google_map_url',
          'linkedin_company_url', 'twitter_handle', 'facebook_page_url',
          'instagram_profile_url', 'youtube_channel_url', 'tiktok_url'
        ];

        step2Fields.forEach(function(fieldName) {
          const field = $('#' + fieldName);
          if (field.length) {
            step2Form.append('<input type="hidden" name="' + fieldName + '" value="' + (field.val() || '') + '">');
          }
        });

        step2Form.submit();
      });

      // Publish button handler for step 2
      $(document).on('click', '.publish-btn', function(e) {
        e.preventDefault();

        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: '{{ __("admin.Publish Organization") }}',
            text: '{{ __("admin.Are you sure you want to publish this organization? This action cannot be undone.") }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ __("admin.Yes, Publish") }}',
            cancelButtonText: '{{ __("admin.Cancel") }}',
            customClass: {
              confirmButton: 'btn btn-success me-2',
              cancelButton: 'btn btn-label-secondary',
            },
            buttonsStyling: false,
          }).then((result) => {
            if (result.isConfirmed) {
              $('#publishForm').submit();
            }
          });
        } else {
          if (confirm('{{ __("admin.Are you sure you want to publish this organization?") }}')) {
            $('#publishForm').submit();
          }
        }
      });

      // Save as Draft for step 2 (uses same draft form as step 1)
      $('#saveDraftBtn2').on('click', function(e) {
        e.preventDefault();

        // Copy form data to draft form
        const mainForm = $('#organizationWizardForm');
        const draftForm = $('#draftForm');

        // Clear and copy all inputs
        draftForm.find('input:not([name="_token"]):not([name="_method"])').remove();

        // Set current step to 2 for Step 2 save
        draftForm.append('<input type="hidden" name="current_step" value="2">');

        // Copy all fields from main form
        mainForm.find('input, select, textarea').each(function() {
          const $this = $(this);
          const name = $this.attr('name');
          const type = $this.attr('type');

          if (!name || name === '_token' || name === '_method' || name === 'current_step') return;

          if (type === 'file') {
            if ($this[0].files.length > 0) {
              const fileInput = $('<input type="file" name="' + name + '">');
              fileInput[0].files = $this[0].files;
              draftForm.append(fileInput);
            }
          } else if ($this.is('select[multiple]')) {
            const values = $this.val() || [];
            values.forEach(function(val) {
              draftForm.append('<input type="hidden" name="' + name + '" value="' + val + '">');
            });
          } else {
            draftForm.append('<input type="hidden" name="' + name + '" value="' + $this.val() + '">');
          }
        });

        // Submit draft form
        draftForm.submit();
      });
    });
  </script>
@stop
