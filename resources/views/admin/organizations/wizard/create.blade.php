@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/toastr/toastr.css" />
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

    /* Theme option styling */
    .theme-option {
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .theme-option:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .theme-option.border-primary {
      box-shadow: 0 0 0 1px #696cff;
    }
    .border-primary .bg-primary .text-primary 
    {
      color: #fff !important;
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
        action="{{ $isEdit ? route('organization.wizard.update.step1', $organization->id) : route('organization.wizard.store') }}"
        method="POST"
        enctype="multipart/form-data"
        id="organizationWizardForm"
      >
        @csrf
        @if($isEdit)
          @method('PUT')
        @endif
        <input type="hidden" name="current_step" id="current_step" value="{{ $activeStep }}">
        <input type="hidden" name="redirect_to" id="redirect_to_step1" value="next">

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
              <div class="d-flex gap-2">
                @if($isDraft)
                  <button type="button" class="btn btn-label-secondary save-draft-step1-btn">
                    <i class="ti ti-file me-2"></i>
                    {{ __('admin.Save as Draft') }}
                  </button>
                @endif
                <button type="button" class="btn btn-label-secondary save-changes-step1-btn">
                  <i class="ti ti-device-floppy me-2"></i>
                  {{ __('admin.Save Changes') }}
                </button>
              </div>

              <div class="d-flex gap-2">
                @if($canPublish)
                  <button type="button" class="btn btn-success publish-btn" id="publishBtn">
                    <i class="ti ti-world me-2"></i>
                    {{ __('admin.Publish') }}
                  </button>
                @endif
                <button type="button" class="btn btn-primary save-continue-step1-btn">
                  {{ __('admin.Save & Continue') }}
                  <i class="ti ti-arrow-{{ $l == 'en' ? 'right' : 'left' }} ms-2"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Step 2: Contact Information -->
          <div class="tab-pane fade {{ $activeStep == 2 ? 'show active' : '' }}" id="pills-contact_information" role="tabpanel" aria-labelledby="pills-contact_information-tab" data-tab-index="1">
            @include('admin.organizations.wizard.partials._step2_contact', ['organization' => $organization ?? null])

            <!-- Navigation Buttons for Step 2 -->
            <div class="d-flex align-items-center justify-content-between gap-3 mt-4 pt-4 border-top">
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-label-secondary prev-step-btn" data-target-step="0">
                  <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i>
                  {{ __('admin.Previous Step') }}
                </button>
                @if($isDraft)
                  <button type="button" class="btn btn-label-secondary save-draft-step2-btn">
                    <i class="ti ti-file me-2"></i>
                    {{ __('admin.Save as Draft') }}
                  </button>
                @endif
                <button type="button" class="btn btn-label-secondary save-changes-step2-btn">
                  <i class="ti ti-device-floppy me-2"></i>
                  {{ __('admin.Save Changes') }}
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
            </div>
          </div>

          <!-- Step 3: Organization Manager -->
          <div class="tab-pane fade {{ $activeStep == 3 ? 'show active' : '' }}" id="pills-organization_manager" role="tabpanel" aria-labelledby="pills-organization_manager-tab" data-tab-index="2">
            @include('admin.organizations.wizard.partials._step3_managers', ['organization' => $organization ?? null, 'assignedManagers' => $assignedManagers ?? collect()])

            <!-- Navigation Buttons for Step 3 -->
            <div class="d-flex align-items-center justify-content-between gap-3 mt-4 pt-4 border-top">
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-label-secondary prev-step-btn" data-target-step="1">
                  <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i>
                  {{ __('admin.Previous Step') }}
                </button>
                @if($isDraft)
                  <button type="button" class="btn btn-label-secondary save-draft-step3-btn">
                    <i class="ti ti-file me-2"></i>
                    {{ __('admin.Save as Draft') }}
                  </button>
                @endif
                <button type="button" class="btn btn-label-secondary save-changes-step3-btn">
                  <i class="ti ti-device-floppy me-2"></i>
                  {{ __('admin.Save Changes') }}
                </button>
              </div>

              <div class="d-flex gap-2">
                @if($isDraft)
                  <button type="button" class="btn btn-success publish-btn" id="publishBtnStep3" style="{{ $hasManagers ? '' : 'display: none;' }}">
                    <i class="ti ti-world me-2"></i>
                    {{ __('admin.Publish') }}
                  </button>
                @endif
                <button type="button" class="btn btn-primary save-continue-step3-btn">
                  {{ __('admin.Save & Continue') }}
                  <i class="ti ti-arrow-{{ $l == 'en' ? 'right' : 'left' }} ms-2"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Step 4: Landing Page -->
          <div class="tab-pane fade {{ $activeStep == 4 ? 'show active' : '' }}" id="pills-landing_page" role="tabpanel" aria-labelledby="pills-landing_page-tab" data-tab-index="3">
            @if($isEdit)
              @include('admin.organizations.wizard.partials._step4_landing', ['organization' => $organization ?? null])

              <!-- Navigation Buttons for Step 4 -->
              <div class="d-flex align-items-center justify-content-between gap-3 mt-4 pt-4 border-top">
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-label-secondary prev-step-btn" data-target-step="2">
                    <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i>
                    {{ __('admin.Previous Step') }}
                  </button>
                  @if($isDraft)
                    <button type="button" class="btn btn-label-secondary save-draft-step4-btn">
                      <i class="ti ti-file me-2"></i>
                      {{ __('admin.Save as Draft') }}
                    </button>
                  @endif
                  <button type="button" class="btn btn-label-secondary save-changes-step4-btn">
                    <i class="ti ti-device-floppy me-2"></i>
                    {{ __('admin.Save Changes') }}
                  </button>
                </div>

                <div class="d-flex gap-2">
                  @if($isDraft)
                    <button type="button" class="btn btn-success publish-btn" id="publishBtnStep4" style="{{ $hasManagers ? '' : 'display: none;' }}">
                      <i class="ti ti-world me-2"></i>
                      {{ __('admin.Publish') }}
                    </button>
                  @endif
                  <button type="button" class="btn btn-primary save-continue-step4-btn">
                    {{ __('admin.Save & Continue') }}
                    <i class="ti ti-arrow-{{ $l == 'en' ? 'right' : 'left' }} ms-2"></i>
                  </button>
                </div>
              </div>
            @else
              <div class="text-center py-5">
                <i class="ti ti-layout display-1 text-muted"></i>
                <h5 class="mt-3 text-muted">{{ __('admin.Please complete the previous steps first') }}</h5>
              </div>
            @endif
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
          <input type="hidden" name="redirect_to" id="redirect_to_step2" value="next">
        </form>

        <!-- Step 3 Form (separate form for step 3 submission) -->
        <form
          action="{{ route('organization.wizard.update.step3', $organization->id) }}"
          method="POST"
          id="step3Form"
          style="display: none;"
        >
          @csrf
          @method('PUT')
          <input type="hidden" name="redirect_to" id="redirect_to_step3" value="next">
        </form>

        <!-- Step 4 Form (separate form for step 4 submission) -->
        <form
          action="{{ route('organization.wizard.update.step4', $organization->id) }}"
          method="POST"
          enctype="multipart/form-data"
          id="step4Form"
          style="display: none;"
        >
          @csrf
          @method('PUT')
          <input type="hidden" name="redirect_to" id="redirect_to_step4" value="next">
        </form>
      @endif

      @if($isDraft)
        <!-- Draft Form (for both new and existing organizations) -->
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
          <input type="hidden" name="current_step" id="draft_current_step" value="{{ $activeStep }}">
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
  <script src="/vuexy/assets/vendor/libs/toastr/toastr.js"></script>
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

      // Save Changes button (Step 1) - validates and stays on same page
      $('.save-changes-step1-btn').on('click', function(e) {
        e.preventDefault();

        if (!validateStep1()) {
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

        // Set redirect_to = 'same' to stay on current page
        $('#redirect_to_step1').val('same');
        $('#organizationWizardForm').submit();
      });

      // Save & Continue button (Step 1) - validates and goes to next step
      $('.save-continue-step1-btn').on('click', function(e) {
        e.preventDefault();

        if (!validateStep1()) {
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

        // Set redirect_to = 'next' to go to next step
        $('#redirect_to_step1').val('next');
        $('#organizationWizardForm').submit();
      });

      // Save as Draft button (Step 1) - NO validation, stays on same page
      $('.save-draft-step1-btn').on('click', function(e) {
        e.preventDefault();

        // Copy step 1 form data to draft form
        $('#draft_current_step').val(1);
        copyStep1DataToDraft();
        $('#draftForm').submit();
      });

      // Helper function to copy Step 1 data to draft form
      function copyStep1DataToDraft() {
        const draftForm = $('#draftForm');

        // Remove previous dynamic inputs from draft form (keep token, method, current_step)
        draftForm.find('input:not([name="_token"]):not([name="_method"]):not([name="current_step"])').remove();

        // Copy step 1 fields
        const step1Fields = ['name_en', 'name_ar', 'organization_type_id', 'status', 'bio'];
        step1Fields.forEach(function(fieldName) {
          const field = $('#' + fieldName);
          if (field.length) {
            draftForm.append('<input type="hidden" name="' + fieldName + '" value="' + (field.val() || '') + '">');
          }
        });

        // Copy industries (multi-select)
        const industries = $('#industries').val();
        if (industries && industries.length > 0) {
          industries.forEach(function(industryId) {
            draftForm.append('<input type="hidden" name="industries[]" value="' + industryId + '">');
          });
        }

        // Handle file upload - clone the file input element
        const imgInput = $('#img_url')[0];
        if (imgInput && imgInput.files && imgInput.files.length > 0) {
          const clonedInput = $(imgInput).clone()[0];
          clonedInput.files = imgInput.files;
          draftForm.append(clonedInput);
        }
      }

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

      // Helper function to submit Step 2 form
      function submitStep2Form(redirectTo) {
        // Submit step 2 form
        const step2Form = $('#step2Form');

        // Clear and copy all step 2 inputs (except token, method, redirect_to)
        step2Form.find('input:not([name="_token"]):not([name="_method"]):not([name="redirect_to"])').remove();

        // Set redirect_to value
        $('#redirect_to_step2').val(redirectTo);

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
      }

      // Save Changes for Step 2 - validates and stays on same page
      $(document).on('click', '.save-changes-step2-btn', function(e) {
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

        submitStep2Form('same');
      });

      // Save & Continue for Step 2 - validates and goes to next step
      $(document).on('click', '.save-continue-step2-btn', function(e) {
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

        submitStep2Form('next');
      });

      // Save as Draft for Step 2 - NO validation, stays on same page
      $(document).on('click', '.save-draft-step2-btn', function(e) {
        e.preventDefault();

        // Copy step 2 form data to draft form
        $('#draft_current_step').val(2);
        copyStep2DataToDraft();
        $('#draftForm').submit();
      });

      // Helper function to copy Step 2 data to draft form
      function copyStep2DataToDraft() {
        const draftForm = $('#draftForm');

        // Remove previous dynamic inputs from draft form (keep token, method, current_step)
        draftForm.find('input:not([name="_token"]):not([name="_method"]):not([name="current_step"])').remove();

        // Copy step 2 fields
        const step2Fields = [
          'contact_email', 'country_code', 'contact_phone',
          'country_id', 'state_id', 'city_id', 'address', 'google_map_url',
          'linkedin_company_url', 'twitter_handle', 'facebook_page_url',
          'instagram_profile_url', 'youtube_channel_url', 'tiktok_url'
        ];

        step2Fields.forEach(function(fieldName) {
          const field = $('#' + fieldName);
          if (field.length) {
            draftForm.append('<input type="hidden" name="' + fieldName + '" value="' + (field.val() || '') + '">');
          }
        });
      }

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

      // ==================== STEP 3: ORGANIZATION MANAGER ====================
      @if($isEdit)
      // Selected managers state
      let selectedManagers = @json($assignedManagers ?? []);

      // Initialize selected managers on load
      function initializeSelectedManagers() {
        updateSelectedManagersUI();
        updateManagerIdsInputs();
        updatePublishButtonVisibility();
      }

      // Update the selected managers UI (tags bar)
      function updateSelectedManagersUI() {
        const container = $('#selectedManagersContainer');
        const noManagersMsg = container.find('.no-managers-message');
        const clearAllBtn = $('.clear-all-managers-btn');

        // Remove existing tags (keep the no-managers message)
        container.find('.manager-tag').remove();

        if (selectedManagers.length === 0) {
          noManagersMsg.show();
          clearAllBtn.hide();
        } else {
          noManagersMsg.hide();
          clearAllBtn.show();

          selectedManagers.forEach(function(manager) {
            const tag = $('<span class="manager-tag">' +
              manager.name +
              ' <i class="ti ti-x remove-manager" data-id="' + manager.id + '"></i>' +
              '</span>');
            container.append(tag);
          });
        }
      }

      // Update hidden inputs for manager IDs
      function updateManagerIdsInputs() {
        const container = $('#managerIdsContainer');
        container.empty();

        selectedManagers.forEach(function(manager) {
          container.append('<input type="hidden" name="manager_ids[]" value="' + manager.id + '">');
        });
      }

      // Update publish button visibility
      function updatePublishButtonVisibility() {
        const publishBtn = $('#publishBtnStep3');
        if (selectedManagers.length > 0) {
          publishBtn.show();
        } else {
          publishBtn.hide();
        }
      }

      // Add manager to selection
      function addManager(manager) {
        // Check if already selected
        if (selectedManagers.find(m => m.id === manager.id)) {
          return false;
        }
        selectedManagers.push(manager);
        updateSelectedManagersUI();
        updateManagerIdsInputs();
        updatePublishButtonVisibility();
        return true;
      }

      // Remove manager from selection
      function removeManager(managerId) {
        selectedManagers = selectedManagers.filter(m => m.id !== managerId);
        updateSelectedManagersUI();
        updateManagerIdsInputs();
        updatePublishButtonVisibility();
        // Update card checkbox if visible
        $('.manager-card').each(function() {
          if ($(this).data('id') == managerId) {
            $(this).find('.manager-checkbox').prop('checked', false);
            $(this).removeClass('selected');
          }
        });
      }

      // Clear all managers
      $('.clear-all-managers-btn').on('click', function() {
        selectedManagers = [];
        updateSelectedManagersUI();
        updateManagerIdsInputs();
        updatePublishButtonVisibility();
        // Uncheck all checkboxes in cards
        $('.manager-card .manager-checkbox').prop('checked', false);
        $('.manager-card').removeClass('selected');
      });

      // Remove manager tag click
      $(document).on('click', '.remove-manager', function() {
        const managerId = $(this).data('id');
        removeManager(managerId);
      });

      // Managers list state
      let searchTimeout = null;
      let currentPage = 0;
      let pageLength = 5;
      let managersInitialized = false;

      // Load managers list
      function loadManagersList(page = 0) {
        const $list = $('#managersList');
        const $loading = $('#managersLoading');
        const $empty = $('#managersEmpty');
        const $pagination = $('#managersPagination');

        $list.hide();
        $empty.hide();
        $pagination.hide();
        $loading.show();

        $.ajax({
          url: '{{ route("organization.wizard.managers.list", $organization->id ?? 0) }}',
          type: 'GET',
          data: {
            search_term: $('#managerSearchInput').val(),
            start: page * pageLength,
            length: pageLength,
            draw: 1
          },
          success: function(response) {
            $loading.hide();

            if (response.data && response.data.length > 0) {
              renderManagerCards(response.data);
              $list.show();

              // Update pagination
              const totalPages = Math.ceil(response.recordsTotal / pageLength);
              if (totalPages > 1) {
                renderPagination(page, totalPages, response.recordsTotal);
                $pagination.show();
              }
            } else {
              $empty.show();
            }
          },
          error: function() {
            $loading.hide();
            $empty.show();
          }
        });
      }

      // Render manager cards
      function renderManagerCards(managers) {
        const $list = $('#managersList');
        $list.empty();

        managers.forEach(function(manager) {
          const isSelected = selectedManagers.find(m => m.id === manager.id);
          const initials = getInitials(manager.name);
          const avatarUrl = manager.avatar || null;

          const card = `
            <div class="manager-card ${isSelected ? 'selected' : ''}" data-id="${manager.id}" data-name="${escapeHtml(manager.name)}" data-email="${escapeHtml(manager.email)}">
              <input type="checkbox" class="manager-checkbox" ${isSelected ? 'checked' : ''}>
              <div class="manager-avatar">
                ${avatarUrl
                  ? `<img src="${avatarUrl}" onerror="this.src='/images/placeholder.jpg'" alt="${escapeHtml(manager.name)}">`
                  : `<i class="ti ti-user"></i>`
                }
              </div>
              <div class="manager-info">
                <div class="manager-name">${escapeHtml(manager.name)}</div>
                <div class="manager-email">${escapeHtml(manager.email)}</div>
                <div class="manager-meta">
                  <span class="manager-meta-item">
                    <i class="ti ti-briefcase"></i>
                    ${escapeHtml(manager.role)}
                  </span>
                  <span class="manager-meta-item">
                    <i class="ti ti-building"></i>
                    ${manager.organizations_count} {{ __('admin.Organizations') }}
                  </span>
                </div>
              </div>
            </div>
          `;
          $list.append(card);
        });
      }

      // Render pagination
      function renderPagination(currentPage, totalPages, totalRecords) {
        const $info = $('#managersInfo');
        const $list = $('#managersPaginationList');

        const start = currentPage * pageLength + 1;
        const end = Math.min((currentPage + 1) * pageLength, totalRecords);
        $info.text(lang === 'ar'
          ? `عرض ${start} - ${end} من ${totalRecords}`
          : `Showing ${start} - ${end} of ${totalRecords}`
        );

        $list.empty();

        // Previous button
        $list.append(`
          <li class="page-item ${currentPage === 0 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">&laquo;</a>
          </li>
        `);

        // Page numbers
        for (let i = 0; i < totalPages; i++) {
          if (totalPages <= 5 || i === 0 || i === totalPages - 1 || Math.abs(i - currentPage) <= 1) {
            $list.append(`
              <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i + 1}</a>
              </li>
            `);
          } else if (Math.abs(i - currentPage) === 2) {
            $list.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
          }
        }

        // Next button
        $list.append(`
          <li class="page-item ${currentPage >= totalPages - 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">&raquo;</a>
          </li>
        `);
      }

      // Get initials from name
      function getInitials(name) {
        return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
      }

      // Escape HTML
      function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
      }

      // Pagination click handler
      $(document).on('click', '#managersPaginationList .page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page !== undefined && !$(this).parent().hasClass('disabled')) {
          currentPage = page;
          loadManagersList(page);
        }
      });

      // Initialize managers list when step 3 tab is shown
      $('[data-bs-target="#pills-organization_manager"]').on('shown.bs.tab', function() {
        if (!managersInitialized) {
          loadManagersList(0);
          managersInitialized = true;
        }
      });

      // If we're already on step 3, initialize immediately
      if ({{ $activeStep }} === 3) {
        setTimeout(function() {
          loadManagersList(0);
          managersInitialized = true;
        }, 100);
      }

      // Search input handler with debounce
      $('#managerSearchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
          currentPage = 0;
          loadManagersList(0);
        }, 300);
      });

      // Card click handler
      $(document).on('click', '.manager-card', function(e) {
        if ($(e.target).is('input[type="checkbox"]')) return;

        const $card = $(this);
        const $checkbox = $card.find('.manager-checkbox');
        $checkbox.prop('checked', !$checkbox.prop('checked'));

        const managerId = $card.data('id');
        const managerName = $card.data('name');
        const managerEmail = $card.data('email');

        if ($checkbox.is(':checked')) {
          addManager({ id: managerId, name: managerName, email: managerEmail });
          $card.addClass('selected');
        } else {
          removeManager(managerId);
          $card.removeClass('selected');
        }
      });

      // Checkbox change handler
      $(document).on('change', '.manager-card .manager-checkbox', function(e) {
        e.stopPropagation();
        const $card = $(this).closest('.manager-card');
        const managerId = $card.data('id');
        const managerName = $card.data('name');
        const managerEmail = $card.data('email');

        if ($(this).is(':checked')) {
          addManager({ id: managerId, name: managerName, email: managerEmail });
          $card.addClass('selected');
        } else {
          removeManager(managerId);
          $card.removeClass('selected');
        }
      });

      // Initialize phone input for manager creation
      let managerPhoneIti = null;
      const managerPhoneInput = document.getElementById('manager_phone');
      const managerPhoneError = document.getElementById('manager-phone-error');
      const managerCountryCodeInput = document.getElementById('manager_country_code');

      if (managerPhoneInput) {
        managerPhoneIti = intlTelInput(managerPhoneInput, {
          initialCountry: 'eg',
          preferredCountries: ['eg', 'ae', 'sa', 'ma', 'dz', 'tn'],
          utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js',
          formatOnDisplay: false,
          separateDialCode: true,
          autoPlaceholder: 'aggressive',
        });

        managerPhoneInput.addEventListener('countrychange', function() {
          const countryData = managerPhoneIti.getSelectedCountryData();
          managerCountryCodeInput.value = countryData.dialCode;
        });

        setTimeout(function() {
          if (managerPhoneIti) {
            const countryData = managerPhoneIti.getSelectedCountryData();
            if (countryData.dialCode) {
              managerCountryCodeInput.value = countryData.dialCode;
            }
          }
        }, 100);

        managerPhoneInput.addEventListener('input', function() {
          if (managerPhoneInput.value.trim() && !managerPhoneIti.isValidNumber()) {
            managerPhoneError.style.display = 'block';
            managerPhoneError.innerText = lang === 'en' ? 'Invalid phone number' : 'رقم الهاتف غير صحيح';
          } else {
            managerPhoneError.style.display = 'none';
          }
        });
      }

      // Toggle password visibility
      $(document).on('click', '.toggle-password-btn', function() {
        const input = $(this).siblings('input');
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
          input.attr('type', 'text');
          icon.removeClass('ti-eye').addClass('ti-eye-off');
        } else {
          input.attr('type', 'password');
          icon.removeClass('ti-eye-off').addClass('ti-eye');
        }
      });

      // Validate create manager form
      function validateCreateManagerForm() {
        let isValid = true;
        const form = $('#createManagerForm');

        // Clear previous errors
        form.find('.form-control, .form-select').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');

        // First Name (EN)
        const firstNameEn = $('#manager_first_name_en');
        if (!firstNameEn.val().trim()) {
          firstNameEn.addClass('is-invalid');
          firstNameEn.siblings('.invalid-feedback').text('{{ __("admin.Please enter the first name") }}');
          isValid = false;
        }

        // Last Name (EN)
        const lastNameEn = $('#manager_last_name_en');
        if (!lastNameEn.val().trim()) {
          lastNameEn.addClass('is-invalid');
          lastNameEn.siblings('.invalid-feedback').text('{{ __("admin.Please enter the last name") }}');
          isValid = false;
        }

        // Gender
        const gender = $('#manager_gender');
        if (!gender.val()) {
          gender.addClass('is-invalid');
          gender.siblings('.invalid-feedback').text('{{ __("admin.Please select the gender") }}');
          isValid = false;
        }

        // Email
        const email = $('#manager_email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.val().trim()) {
          email.addClass('is-invalid');
          email.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please enter the email address") }}');
          isValid = false;
        } else if (!emailRegex.test(email.val().trim())) {
          email.addClass('is-invalid');
          email.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please enter a valid email address") }}');
          isValid = false;
        }

        // Phone
        const phone = $('#manager_phone');
        if (!phone.val().trim()) {
          phone.addClass('is-invalid');
          phone.siblings('.invalid-feedback').text('{{ __("admin.Please enter the phone number") }}');
          isValid = false;
        } else if (managerPhoneIti && !managerPhoneIti.isValidNumber()) {
          phone.addClass('is-invalid');
          phone.siblings('.invalid-feedback').text(lang === 'en' ? 'Invalid phone number' : 'رقم الهاتف غير صحيح');
          isValid = false;
        }

        // Password
        const password = $('#manager_password');
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$/;
        if (!password.val()) {
          password.addClass('is-invalid');
          password.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please enter the password") }}');
          isValid = false;
        } else if (!passwordRegex.test(password.val())) {
          password.addClass('is-invalid');
          password.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Password must include uppercase, lowercase, number and special character") }}');
          isValid = false;
        }

        // Confirm Password
        const confirmPassword = $('#manager_password_confirmation');
        if (!confirmPassword.val()) {
          confirmPassword.addClass('is-invalid');
          confirmPassword.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Please confirm the password") }}');
          isValid = false;
        } else if (confirmPassword.val() !== password.val()) {
          confirmPassword.addClass('is-invalid');
          confirmPassword.closest('.input-group').find('.invalid-feedback').text('{{ __("admin.Passwords do not match") }}');
          isValid = false;
        }

        return isValid;
      }

      // Create manager button handler
      $('#createManagerBtn').on('click', function() {
        if (!validateCreateManagerForm()) {
          return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin me-2"></i>{{ __("admin.Creating...") }}');

        $.ajax({
          url: '{{ route("organization.wizard.managers.create", $organization->id ?? 0) }}',
          type: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
            first_name_en: $('#manager_first_name_en').val(),
            last_name_en: $('#manager_last_name_en').val(),
            first_name_ar: $('#manager_first_name_ar').val(),
            last_name_ar: $('#manager_last_name_ar').val(),
            gender: $('#manager_gender').val(),
            email: $('#manager_email').val(),
            country_code: $('#manager_country_code').val(),
            phone: $('#manager_phone').val(),
            password: $('#manager_password').val(),
            password_confirmation: $('#manager_password_confirmation').val()
          },
          success: function(response) {
            if (response.success) {
              // Add the new manager to selection
              addManager(response.manager);

              // Clear form
              $('#createManagerForm input').val('');
              $('#createManagerForm select').val('');

              // Reset phone input
              if (managerPhoneIti) {
                managerPhoneIti.setCountry('eg');
              }

              // Show success message
              if (typeof toastr !== 'undefined') {
                toastr.success(response.message);
              }

              // Reload managers list to show new manager
              loadManagersList(0);

              // Switch to existing managers tab
              $('#existing-manager-tab').tab('show');
            }
          },
          error: function(xhr) {
            let errorMessage = '{{ __("admin.Error creating manager") }}';
            if (xhr.responseJSON) {
              if (xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
              }
              if (xhr.responseJSON.errors) {
                // Show validation errors
                Object.keys(xhr.responseJSON.errors).forEach(function(key) {
                  const fieldName = 'manager_' + key;
                  const field = $('#' + fieldName);
                  if (field.length) {
                    field.addClass('is-invalid');
                    const feedback = field.siblings('.invalid-feedback').length ?
                      field.siblings('.invalid-feedback') :
                      field.closest('.input-group').find('.invalid-feedback');
                    feedback.text(xhr.responseJSON.errors[key][0]);
                  }
                });
              }
            }
            if (typeof toastr !== 'undefined') {
              toastr.error(errorMessage);
            }
          },
          complete: function() {
            btn.prop('disabled', false).html('<i class="ti ti-user-plus me-2"></i>{{ __("admin.Create Manager") }}');
          }
        });
      });

      // Helper function to submit Step 3 form
      function submitStep3Form(redirectTo) {
        // Submit step 3 form
        const step3Form = $('#step3Form');

        // Clear previous inputs (except token, method, redirect_to)
        step3Form.find('input:not([name="_token"]):not([name="_method"]):not([name="redirect_to"])').remove();

        // Set redirect_to value
        $('#redirect_to_step3').val(redirectTo);

        // Add manager IDs
        selectedManagers.forEach(function(manager) {
          step3Form.append('<input type="hidden" name="manager_ids[]" value="' + manager.id + '">');
        });

        step3Form.submit();
      }

      // Save Changes for Step 3 - validates and stays on same page
      $(document).on('click', '.save-changes-step3-btn', function(e) {
        e.preventDefault();

        // Validate at least one manager selected
        if (selectedManagers.length === 0) {
          if (typeof toastr !== 'undefined') {
            toastr.error('{{ __("admin.Please select or create at least one organization manager") }}');
          }
          return;
        }

        submitStep3Form('same');
      });

      // Save & Continue for Step 3 - validates and goes to next step
      $(document).on('click', '.save-continue-step3-btn', function(e) {
        e.preventDefault();

        // Validate at least one manager selected
        if (selectedManagers.length === 0) {
          if (typeof toastr !== 'undefined') {
            toastr.error('{{ __("admin.Please select or create at least one organization manager") }}');
          }
          return;
        }

        submitStep3Form('next');
      });

      // Save as Draft for Step 3 - NO validation, stays on same page
      $(document).on('click', '.save-draft-step3-btn', function(e) {
        e.preventDefault();

        // Copy step 3 form data to draft form
        $('#draft_current_step').val(3);
        copyStep3DataToDraft();
        $('#draftForm').submit();
      });

      // Helper function to copy Step 3 data to draft form
      function copyStep3DataToDraft() {
        const draftForm = $('#draftForm');

        // Remove previous dynamic inputs from draft form (keep token, method, current_step)
        draftForm.find('input:not([name="_token"]):not([name="_method"]):not([name="current_step"])').remove();

        // Copy manager IDs
        selectedManagers.forEach(function(manager) {
          draftForm.append('<input type="hidden" name="manager_ids[]" value="' + manager.id + '">');
        });
      }

      // Initialize on load
      initializeSelectedManagers();
      @endif

      // ==========================================
      // Step 4: Landing Page Functionality
      // ==========================================

      // Landing page enabled toggle
      $(document).on('change', '#landingEnabledToggle', function() {
        const statusText = $('#landingStatusText');
        if ($(this).is(':checked')) {
          statusText.html('<span class="text-success"><i class="ti ti-check me-1"></i>{{ __("admin.Landing page is published") }}</span>');
        } else {
          statusText.html('<span class="text-warning"><i class="ti ti-eye-off me-1"></i>{{ __("admin.Landing page is unpublished") }}</span>');
        }
      });

      // Section toggles
      $(document).on('change', '.section-toggle', function() {
        const section = $(this).data('section');
        const content = $('#' + section + 'Content');
        if ($(this).is(':checked')) {
          content.slideDown(200);
        } else {
          content.slideUp(200);
        }
      });

      // Background type toggle (solid/gradient)
      $(document).on('change', '.bg-type-select', function() {
        const section = $(this).data('section');
        const type = $(this).val();

        if (type === 'gradient') {
          $('#' + section + '_solid').addClass('d-none');
          $('#' + section + '_gradient').removeClass('d-none');
        } else {
          $('#' + section + '_solid').removeClass('d-none');
          $('#' + section + '_gradient').addClass('d-none');
        }

        updateBgPreview(section);
      });

      // Color picker change - update text input and preview
      $(document).on('input', '.bg-color-picker', function() {
        const section = $(this).data('section');
        const type = $(this).data('type');
        const color = $(this).val();

        // Update corresponding text input
        $(this).closest('.input-group').find('.bg-color-text').val(color);

        updateBgPreview(section);
      });

      // Text input change - update color picker and preview
      $(document).on('input', '.bg-color-text', function() {
        const section = $(this).data('section');
        const type = $(this).data('type');
        let color = $(this).val();

        // Validate hex color format
        if (/^#[0-9A-Fa-f]{6}$/.test(color)) {
          // Update corresponding color picker
          $(this).closest('.input-group').find('.bg-color-picker').val(color);
          updateBgPreview(section);
        }
      });

      function updateBgPreview(section) {
        const bgType = $('select[name="' + section + '_bg_type"]').val();
        const preview = $('#' + section + '_preview');

        if (bgType === 'gradient') {
          const startColor = $('input[name="' + section + '_bg_gradient_start"]').val() || '#667eea';
          const endColor = $('input[name="' + section + '_bg_gradient_end"]').val() || '#764ba2';
          preview.css('background', 'linear-gradient(135deg, ' + startColor + ' 0%, ' + endColor + ' 100%)');
        } else {
          const solidColor = $('input[name="' + section + '_bg_color"]').val() || '#ffffff';
          preview.css('background', solidColor);
        }
      }

      // Theme selection handler
      $(document).on('click', '.theme-option', function() {
        const $this = $(this);
        const $radio = $this.find('input[type="radio"]');

        // Update selection
        $radio.prop('checked', true);

        // Update visual states
        $('.theme-option').removeClass('border-primary border-2').addClass('border');
        $('.theme-option .card-footer').removeClass('bg-primary bg-opacity-10');
        $('.theme-option .card-footer').html('<span class="text-muted">{{ __("admin.Click to select") }}</span>');

        $this.removeClass('border').addClass('border-primary border-2');
        $this.find('.card-footer').addClass('bg-primary bg-opacity-10');
        $this.find('.card-footer').html('<i class="ti ti-check text-primary me-1"></i><span class="text-primary fw-semibold">{{ __("admin.Selected") }}</span>');
      });

      // Check slug availability
      $(document).on('click', '#checkSlugBtn', function() {
        const btn = $(this);
        const slugInput = $('#slug');
        const slug = slugInput.val().trim();
        const feedbackDiv = $('#slugAvailabilityFeedback');
        const orgId = '{{ $organization->id ?? "" }}';

        if (!slug) {
          feedbackDiv.html('<small class="text-danger"><i class="ti ti-alert-circle me-1"></i>{{ __("admin.Please enter a URL slug") }}</small>');
          feedbackDiv.show();
          return;
        }

        // Validate format
        const slugRegex = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
        if (!slugRegex.test(slug)) {
          feedbackDiv.html('<small class="text-danger"><i class="ti ti-alert-circle me-1"></i>{{ __("admin.URL must contain only lowercase letters, numbers, and hyphens") }}</small>');
          feedbackDiv.show();
          return;
        }

        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm me-1"></span>{{ __("admin.Checking") }}...');
        feedbackDiv.hide();

        $.ajax({
          url: "{{ $isEdit ? route('organization.wizard.check.slug', $organization->id ?? 0) : route('organization.wizard.check.slug') }}",
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            slug: slug
          },
          success: function(response) {
            if (response.available) {
              feedbackDiv.html('<small class="text-success"><i class="ti ti-check me-1"></i>' + response.message + '</small>');
            } else {
              feedbackDiv.html('<small class="text-danger"><i class="ti ti-alert-circle me-1"></i>' + response.message + '</small>');
            }
            feedbackDiv.show();
          },
          error: function(xhr) {
            feedbackDiv.html('<small class="text-danger"><i class="ti ti-alert-circle me-1"></i>{{ __("admin.Error checking availability") }}</small>');
            feedbackDiv.show();
          },
          complete: function() {
            btn.prop('disabled', false);
            btn.html('<i class="ti ti-check me-1"></i>{{ __("admin.Check Availability") }}');
          }
        });
      });

      // Generate slug from organization name
      $(document).on('click', '#generateSlugBtn', function() {
        const btn = $(this);
        const orgId = '{{ $organization->id ?? "" }}';

        if (!orgId) {
          toastr.error("{{ __('admin.Please complete the previous steps first') }}");
          return;
        }

        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm me-1"></span>{{ __("admin.Processing") }}...');

        $.ajax({
          url: "{{ $isEdit ? route('organization.wizard.generate.slug', $organization->id ?? 0) : '#' }}",
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function(response) {
            if (response.status) {
              $('#slug').val(response.slug);
              toastr.success("{{ __('admin.Slug generated successfully') }}");
            } else {
              toastr.error(response.message || "{{ __('admin.Error generating slug') }}");
            }
          },
          error: function(xhr) {
            toastr.error("{{ __('admin.Error generating slug') }}");
          },
          complete: function() {
            btn.prop('disabled', false);
            btn.html('<i class="ti ti-refresh me-1"></i>{{ __("admin.Generate from name") }}');
          }
        });
      });

      // Add repeater item
      $(document).on('click', '.add-item-btn', function() {
        const type = $(this).data('type');
        const max = $(this).data('max');
        const container = getContainerByType(type);
        const currentCount = container.find('.repeater-item').length;

        if (currentCount >= max) {
          toastr.warning(getMaxMessage(type));
          return;
        }

        // Hide empty state
        container.find('.empty-state').hide();

        const newIndex = currentCount;
        const template = getTemplate(type, newIndex);

        if (type === 'partner') {
          container.append(template);
        } else {
          container.append(template);
        }

        // Update step numbers if applicable
        updateStepNumbers(container);
      });

      // Remove repeater item
      $(document).on('click', '.remove-item-btn', function() {
        const item = $(this).closest('.repeater-item');
        const container = item.parent();

        item.fadeOut(200, function() {
          $(this).remove();
          updateStepNumbers(container);

          // Show empty state if no items
          if (container.find('.repeater-item').length === 0) {
            container.find('.empty-state').show();
          }

          // Reindex remaining items
          reindexItems(container);
        });
      });

      function getContainerByType(type) {
        switch(type) {
          case 'step': return $('#processStepsContainer');
          case 'metric': return $('#metricsContainer');
          case 'article': return $('#articlesContainer');
          case 'partner': return $('#partnersContainer');
        }
      }

      function getMaxMessage(type) {
        switch(type) {
          case 'step': return "{{ __('admin.Maximum 4 steps allowed') }}";
          case 'metric': return "{{ __('admin.Maximum 8 metrics allowed') }}";
          case 'article': return "{{ __('admin.Maximum 8 articles allowed') }}";
          case 'partner': return "{{ __('admin.Maximum 20 partners allowed') }}";
        }
      }

      function getTemplate(type, index) {
        switch(type) {
          case 'step':
            return `
              <div class="repeater-item card mb-2 border" data-index="${index}">
                <div class="card-body py-3">
                  <div class="row align-items-center">
                    <div class="col-md-1 text-center">
                      <span class="badge bg-primary rounded-circle step-number" style="width: 32px; height: 32px; line-height: 24px;">${index + 1}</span>
                    </div>
                    <div class="col-md-3">
                      <input type="text" class="form-control form-control-sm" name="process_steps[${index}][title]" placeholder="{{ __('admin.Step Title') }}" maxlength="255">
                    </div>
                    <div class="col-md-4">
                      <input type="text" class="form-control form-control-sm" name="process_steps[${index}][description]" placeholder="{{ __('admin.Step Description') }}" maxlength="1000">
                    </div>
                    <div class="col-md-3">
                      <select class="form-select form-select-sm icon-select" name="process_steps[${index}][icon]">
                        <option value="">{{ __('admin.Select icon') }}</option>
                        <option value="ti-file-text">File Text</option>
                        <option value="ti-clipboard-check">Clipboard Check</option>
                        <option value="ti-users">Users</option>
                        <option value="ti-rocket">Rocket</option>
                        <option value="ti-chart-bar">Chart Bar</option>
                        <option value="ti-trophy">Trophy</option>
                        <option value="ti-bulb">Bulb</option>
                        <option value="ti-target">Target</option>
                        <option value="ti-check">Check</option>
                        <option value="ti-star">Star</option>
                        <option value="ti-heart">Heart</option>
                        <option value="ti-mail">Mail</option>
                        <option value="ti-send">Send</option>
                        <option value="ti-calendar">Calendar</option>
                        <option value="ti-clock">Clock</option>
                        <option value="ti-settings">Settings</option>
                        <option value="ti-search">Search</option>
                        <option value="ti-home">Home</option>
                        <option value="ti-briefcase">Briefcase</option>
                        <option value="ti-world">World</option>
                        <option value="ti-message">Message</option>
                        <option value="ti-phone">Phone</option>
                        <option value="ti-lock">Lock</option>
                        <option value="ti-shield-check">Shield Check</option>
                        <option value="ti-gift">Gift</option>
                        <option value="ti-thumb-up">Thumb Up</option>
                        <option value="ti-lightbulb">Lightbulb</option>
                        <option value="ti-bolt">Bolt</option>
                        <option value="ti-flag">Flag</option>
                        <option value="ti-award">Award</option>
                      </select>
                    </div>
                    <div class="col-md-1 text-end">
                      <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item-btn">
                        <i class="ti ti-trash"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>`;

          case 'metric':
            return `
              <div class="repeater-item card mb-2 border" data-index="${index}">
                <div class="card-body py-3">
                  <div class="row align-items-center">
                    <div class="col-md-3">
                      <input type="text" class="form-control form-control-sm" name="metrics[${index}][number]" placeholder="{{ __('admin.Metric Number') }}" maxlength="20">
                    </div>
                    <div class="col-md-5">
                      <input type="text" class="form-control form-control-sm" name="metrics[${index}][label]" placeholder="{{ __('admin.Metric Label') }}" maxlength="255">
                    </div>
                    <div class="col-md-3">
                      <select class="form-select form-select-sm icon-select" name="metrics[${index}][icon]">
                        <option value="">{{ __('admin.Select icon') }}</option>
                        <option value="ti-users">Users</option>
                        <option value="ti-building">Building</option>
                        <option value="ti-currency-dollar">Currency Dollar</option>
                        <option value="ti-rocket">Rocket</option>
                        <option value="ti-chart-line">Chart Line</option>
                        <option value="ti-briefcase">Briefcase</option>
                        <option value="ti-world">World</option>
                        <option value="ti-star">Star</option>
                        <option value="ti-trophy">Trophy</option>
                        <option value="ti-chart-bar">Chart Bar</option>
                        <option value="ti-chart-pie">Chart Pie</option>
                        <option value="ti-trending-up">Trending Up</option>
                        <option value="ti-target">Target</option>
                        <option value="ti-award">Award</option>
                        <option value="ti-flag">Flag</option>
                        <option value="ti-calendar">Calendar</option>
                        <option value="ti-clock">Clock</option>
                        <option value="ti-heart">Heart</option>
                        <option value="ti-thumb-up">Thumb Up</option>
                        <option value="ti-check">Check</option>
                        <option value="ti-bolt">Bolt</option>
                        <option value="ti-gift">Gift</option>
                        <option value="ti-home">Home</option>
                        <option value="ti-map-pin">Map Pin</option>
                        <option value="ti-globe">Globe</option>
                        <option value="ti-sparkles">Sparkles</option>
                        <option value="ti-flame">Flame</option>
                        <option value="ti-diamond">Diamond</option>
                        <option value="ti-crown">Crown</option>
                        <option value="ti-medal">Medal</option>
                      </select>
                    </div>
                    <div class="col-md-1 text-end">
                      <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item-btn">
                        <i class="ti ti-trash"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>`;

          case 'article':
            return `
              <div class="repeater-item card mb-3 border" data-index="${index}">
                <div class="card-body py-3">
                  <div class="row">
                    <div class="col-md-3 mb-2">
                      <input type="text" class="form-control form-control-sm" name="articles[${index}][publication_name]" placeholder="{{ __('admin.Publication Name') }}" maxlength="255">
                    </div>
                    <div class="col-md-2 mb-2">
                      <input type="date" class="form-control form-control-sm" name="articles[${index}][date]">
                    </div>
                    <div class="col-md-3 mb-2">
                      <input type="text" class="form-control form-control-sm" name="articles[${index}][title]" placeholder="{{ __('admin.Article Title') }}" maxlength="255">
                    </div>
                    <div class="col-md-3 mb-2">
                      <input type="url" class="form-control form-control-sm" name="articles[${index}][link]" placeholder="{{ __('admin.Article Link') }}" maxlength="500">
                    </div>
                    <div class="col-md-1 text-end mb-2">
                      <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item-btn">
                        <i class="ti ti-trash"></i>
                      </button>
                    </div>
                    <div class="col-md-8 mb-2">
                      <textarea class="form-control form-control-sm" name="articles[${index}][summary]" rows="2" placeholder="{{ __('admin.Article Summary') }}" maxlength="1000"></textarea>
                    </div>
                    <div class="col-md-4 mb-2">
                      <label class="form-label small">{{ __('admin.Article Image') }}</label>
                      <input type="file" class="form-control form-control-sm" name="articles[${index}][image]" accept="image/png,image/jpeg,image/jpg">
                      <small class="text-muted">PNG, JPG ({{ __('admin.Max size') }}: 5MB)</small>
                    </div>
                  </div>
                </div>
              </div>`;

          case 'partner':
            return `
              <div class="repeater-item col-md-4 col-lg-3 mb-3" data-index="${index}">
                <div class="card border h-100">
                  <div class="card-body py-3 text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger position-absolute top-0 end-0 m-2 remove-item-btn">
                      <i class="ti ti-x"></i>
                    </button>
                    <div class="mb-2 bg-light rounded d-flex align-items-center justify-content-center" style="height: 60px;">
                      <i class="ti ti-building text-muted fs-3"></i>
                    </div>
                    <input type="text" class="form-control form-control-sm mb-2" name="partners_list[${index}][name]" placeholder="{{ __('admin.Partner Name') }}" maxlength="255">
                    <input type="file" class="form-control form-control-sm" name="partners_list[${index}][logo]" accept="image/png,image/jpeg,image/jpg,image/svg+xml">
                    <small class="text-muted d-block mt-1">PNG, JPG, SVG (2MB)</small>
                  </div>
                </div>
              </div>`;
        }
      }

      function updateStepNumbers(container) {
        container.find('.step-number').each(function(index) {
          $(this).text(index + 1);
        });
      }

      function reindexItems(container) {
        container.find('.repeater-item').each(function(index) {
          $(this).attr('data-index', index);
          $(this).find('input, select, textarea').each(function() {
            const name = $(this).attr('name');
            if (name) {
              const newName = name.replace(/\[\d+\]/, '[' + index + ']');
              $(this).attr('name', newName);
            }
          });
        });
      }

      // Helper function to submit Step 4 form
      function submitStep4Form(redirectTo) {
        const step4Form = $('#step4Form');

        // Clear previous inputs except token, method, and redirect_to
        step4Form.find('input:not([name="_token"]):not([name="_method"]):not([name="redirect_to"]), select, textarea').remove();

        // Set redirect_to value
        $('#redirect_to_step4').val(redirectTo);

        // Copy all inputs from Step 4 tab, preserving select values
        $('#pills-landing_page').find('input, select, textarea').each(function() {
          const $this = $(this);
          const clone = $this.clone();

          // For select elements, manually set the selected value
          if ($this.is('select')) {
            clone.val($this.val());
          }

          step4Form.append(clone);
        });

        step4Form.submit();
      }

      // Save Changes for Step 4 - validates and stays on same page
      $(document).on('click', '.save-changes-step4-btn', function(e) {
        e.preventDefault();
        submitStep4Form('same');
      });

      // Save & Continue for Step 4 - validates and goes to next step
      $(document).on('click', '.save-continue-step4-btn', function(e) {
        e.preventDefault();
        submitStep4Form('next');
      });

      // Save as Draft for Step 4 - NO validation, stays on same page
      $(document).on('click', '.save-draft-step4-btn', function(e) {
        e.preventDefault();

        // Copy step 4 form data to draft form
        $('#draft_current_step').val(4);
        copyStep4DataToDraft();
        $('#draftForm').submit();
      });

      // Helper function to copy Step 4 data to draft form
      function copyStep4DataToDraft() {
        const draftForm = $('#draftForm');

        // Remove previous dynamic inputs from draft form (keep token, method, current_step)
        draftForm.find('input:not([name="_token"]):not([name="_method"]):not([name="current_step"]), select, textarea').remove();

        // Copy all inputs from Step 4 tab
        $('#pills-landing_page').find('input, select, textarea').each(function() {
          const $this = $(this);
          const clone = $this.clone();

          // For select elements, manually set the selected value
          if ($this.is('select')) {
            clone.val($this.val());
          }

          draftForm.append(clone);
        });
      }
    });
  </script>
@stop
