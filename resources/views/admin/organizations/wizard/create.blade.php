@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/select2/select2.css" />
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
  </style>
@stop

@php
  $isEdit = isset($organization);
  $currentStep = $isEdit ? ($organization->wizard_step ?? 1) : 1;
  $isDraft = $isEdit ? $organization->is_draft : true;
  $hasManagers = $isEdit ? $organization->assignedUsers()->count() > 0 : false;
  $canPublish = $isDraft && $hasManagers;

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
        <input type="hidden" name="current_step" id="current_step" value="1">
        <input type="hidden" name="action_type" id="action_type" value="save_continue">

        <!-- Tabs (mobile) -->
        <div style="max-width: 100%; overflow-x: auto;" class="mb-4 d-md-none pb-3">
          <ul class="nav nav-pills flex-nowrap" role="tablist">
            @foreach ($tabs as $key => $tab)
              <li class="nav-item" role="presentation">
                <button
                  class="nav-link {{ $key == 0 ? 'active' : '' }} {{ $tab['step'] > $currentStep ? 'tab-disabled' : '' }}"
                  id="pills-{{ $tab['id'] }}-tab-mobile"
                  data-bs-toggle="pill"
                  data-bs-target="#pills-{{ $tab['id'] }}"
                  type="button"
                  role="tab"
                  aria-controls="pills-{{ $tab['id'] }}"
                  aria-selected="{{ $key == 0 ? 'true' : 'false' }}"
                  data-tab-index="{{ $key }}"
                  data-step="{{ $tab['step'] }}"
                  {{ $tab['step'] > $currentStep ? 'disabled' : '' }}
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
                class="nav-link {{ $key == 0 ? 'active' : '' }} {{ $tab['step'] > $currentStep ? 'tab-disabled' : '' }}"
                id="pills-{{ $tab['id'] }}-tab"
                data-bs-toggle="pill"
                data-bs-target="#pills-{{ $tab['id'] }}"
                type="button"
                role="tab"
                aria-controls="pills-{{ $tab['id'] }}"
                aria-selected="{{ $key == 0 ? 'true' : 'false' }}"
                data-tab-index="{{ $key }}"
                data-step="{{ $tab['step'] }}"
                {{ $tab['step'] > $currentStep ? 'disabled' : '' }}
              >
                <span class="fw-semibold">{{ $tab['label'] }}</span>
              </button>
            </li>
          @endforeach
        </ul>
        <hr />

        <div class="tab-content shadow-none p-4" id="pills-tabContent">
          <!-- Step 1: Organization Basics -->
          <div class="tab-pane fade show active" id="pills-organization_basics" role="tabpanel" aria-labelledby="pills-organization_basics-tab" data-tab-index="0">
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

          <!-- Step 2: Contact Information (Coming Soon) -->
          <div class="tab-pane fade" id="pills-contact_information" role="tabpanel" aria-labelledby="pills-contact_information-tab" data-tab-index="1">
            <div class="text-center py-5">
              <i class="ti ti-building-skyscraper display-1 text-muted"></i>
              <h5 class="mt-3 text-muted">{{ __('admin.Coming Soon') }}</h5>
              <p class="text-muted">{{ __('admin.This step will be available in a future update') }}</p>
            </div>
          </div>

          <!-- Step 3: Organization Manager (Coming Soon) -->
          <div class="tab-pane fade" id="pills-organization_manager" role="tabpanel" aria-labelledby="pills-organization_manager-tab" data-tab-index="2">
            <div class="text-center py-5">
              <i class="ti ti-user-cog display-1 text-muted"></i>
              <h5 class="mt-3 text-muted">{{ __('admin.Coming Soon') }}</h5>
              <p class="text-muted">{{ __('admin.This step will be available in a future update') }}</p>
            </div>
          </div>

          <!-- Step 4: Landing Page (Coming Soon) -->
          <div class="tab-pane fade" id="pills-landing_page" role="tabpanel" aria-labelledby="pills-landing_page-tab" data-tab-index="3">
            <div class="text-center py-5">
              <i class="ti ti-layout display-1 text-muted"></i>
              <h5 class="mt-3 text-muted">{{ __('admin.Coming Soon') }}</h5>
              <p class="text-muted">{{ __('admin.This step will be available in a future update') }}</p>
            </div>
          </div>

          <!-- Step 5: Custom Pages (Coming Soon) -->
          <div class="tab-pane fade" id="pills-custom_pages" role="tabpanel" aria-labelledby="pills-custom_pages-tab" data-tab-index="4">
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

  <script>
    $(document).ready(function() {
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

      // Tab state management
      let currentStep = {{ $currentStep }};
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

      // Save as Draft button
      $('#saveDraftBtn').on('click', function(e) {
        e.preventDefault();

        // Copy form data to draft form
        const mainForm = $('#organizationWizardForm');
        const draftForm = $('#draftForm');

        // Clear and copy all inputs
        draftForm.find('input:not([name="_token"]):not([name="_method"])').remove();

        mainForm.find('input, select, textarea').each(function() {
          const $this = $(this);
          const name = $this.attr('name');
          const type = $this.attr('type');

          if (!name || name === '_token' || name === '_method') return;

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
    });
  </script>
@stop
