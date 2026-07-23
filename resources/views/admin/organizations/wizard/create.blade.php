@extends('layouts.admin')

@section('style')
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/bs-stepper/bs-stepper.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/@form-validation/form-validation.css" />
  <link rel="stylesheet" href="/vuexy/assets/vendor/libs/toastr/toastr.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
  <style>
    .iti { width: 100%; }
    .iti__country-list { z-index: 9999; max-height: 200px; }
  </style>
@stop

@php
  $isEdit = isset($organization);
  $maxStep = $isEdit ? min($organization->wizard_step ?? 1, 3) : 1;
  $isDraft = $isEdit ? $organization->is_draft : true;
  $hasManagers = $isEdit ? $organization->assignedUsers()->count() > 0 : false;
  $canPublish = $isDraft && $hasManagers;
  $requestedStep = request()->get('step', 1);
  $activeStep = min($requestedStep, $maxStep);

  $tabs = [
    ['id' => 'basics', 'title' => __('admin.Organization Basics'), 'step' => 1],
    ['id' => 'contact', 'title' => __('admin.Contact Info'), 'step' => 2],
    ['id' => 'managers', 'title' => __('admin.Organization Manager'), 'step' => 3],
  ];
@endphp

@section('content')
<div class="">
  <div class="">
    <div class="row">
      <div class="col-12">
        <div class="mb-4">
          <h4 class="mb-1">
            {{ $isEdit ? __('admin.Edit Organization') : __('admin.Create Organization') }}
          </h4>
          <p class="mb-0">{{ __('admin.setup_organization_wizard_description') }}</p>
        </div>

        <!-- Alerts -->
        @if (session()->has('success'))
          <x-alert type="success" message="{{ session()->get('success') }}" />
        @endif

        <div class="bs-stepper wizard-numbered">
          <!-- Wizard header -->
          <div class="bs-stepper-header">
            @foreach ($tabs as $key => $tab)
              <div class="step {{ $activeStep == $tab['step'] ? 'active' : '' }} {{ $activeStep > $tab['step'] ? 'crossed' : '' }}" data-target="#{{ $tab['id'] }}">                
                <button
                  type="button"
                  class="step-trigger"
                  data-tab-index="{{ $tab['step'] }}"
                  {{ ($tab['step'] > $maxStep) ? 'disabled' : '' }}
                >
                  <span class="bs-stepper-circle">{{ $tab['step'] }}</span>
                  <span class="bs-stepper-label">
                    <span class="bs-stepper-title">{{ $tab['title'] }}</span>
                    <span class="bs-stepper-subtitle">{{ __('admin.Add') }} {{ $tab['title'] }}</span>
                  </span>
                </button>
              </div>
              @if (!$loop->last)
               <div class="line">
                  <i class="ti ti-chevron-right"></i>
                </div>
              @endif
            @endforeach
          </div>

          <!-- Wizard content -->
          <div class="bs-stepper-content p-0">
            <form
              action="{{ $isEdit ? route('organization.wizard.update.step1', $organization->id) : route('organization.wizard.store') }}"
              method="POST"
              enctype="multipart/form-data"
              id="wizardForm"
            >
              @csrf
              @if($isEdit) @method('PUT') @endif
              
              <input type="hidden" name="current_step" id="current_step" value="{{ $activeStep }}">
              <input type="hidden" name="redirect_to" id="redirect_to" value="next">

              <!-- Alerts -->
              <div class="px-8">
                @if (session()->has('error'))
                  <div class="mt-6">
                    <x-alert type="danger" message="{{ session()->get('error') }}" />
                  </div>
                @endif
                @if ($errors->any())
                  <div class="mt-6">
                    @foreach ($errors->all() as $error)
                      <x-alert type="danger" message="{{ $error }}" />
                    @endforeach
                  </div>
                @endif

                <div class="mt-6" id="errorAlert" style="display: none;">
                  <div class="alert alert-danger alert-dismissible">
                    <i class="ti ti-alert-triangle me-2"></i>
                    <span id="errorAlertMessage"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
                </div>
              </div>

              @foreach ($tabs as $tab)
                <div id="{{ $tab['id'] }}" class="content {{ $activeStep == $tab['step'] ? 'active' : '' }}">
                  <div class="content-header py-4 px-8">
                    <h5 class="mb-0">{{ $tab['title'] }}</h5>
                    <small>{{ __('admin.setup') }} {{ $tab['title'] }}</small>
                  </div>
                  
                    @include(
                      'admin.organizations.wizard.partials._step' . $tab['step'] . '_' . $tab['id'], [
                        'organization' => $organization ?? null,
                        'assignedManagers' => $assignedManagers ?? collect(),
                      ]
                    )
                </div>
              @endforeach

              <div class="row border-top py-6 px-8">
                <div class="col-12 d-flex justify-content-between">
                  <button type="button" class="btn btn-label-secondary btn-prev" {{ $activeStep <= 1 ? 'disabled' : '' }}>
                    <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} ti-xs me-sm-2"></i>
                    <span class="align-middle d-sm-inline-block d-none">{{ __('admin.Previous') }}</span>
                  </button>
                  <div class="d-flex align-items-center gap-3">
                    @if($isDraft)
                      <button type="button" class="btn btn-label-secondary" data-action="draft">
                        <i class="ti ti-file me-2"></i>{{ __('admin.Save as Draft') }}
                      </button>
                    @endif
                    @if($activeStep == 3)
                      <button type="button" class="btn btn-primary" data-action="same">
                        <i class="ti ti-device-floppy me-2"></i>{{ __('admin.Save') }}
                      </button>
                      @if($canPublish)
                        <button type="button" class="btn btn-success" id="publishBtn">
                          <i class="ti ti-world me-2"></i>{{ __('admin.Publish') }}
                        </button>
                      @endif
                    @else
                      <button type="button" class="btn btn-primary btn-next">
                        <span class="align-middle d-sm-inline-block d-none me-sm-2">{{ __('admin.Save & Continue') }}</span>
                        <i class="ti ti-arrow-right ti-xs"></i>
                      </button>
                    @endif
                  </div>
                </div>
              </div>
            </form>

            <div class="px-8 pb-6">
              @if(isset($organization) && $organization->is_draft)
                <div class="alert alert-warning d-flex align-items-center mt-3" role="alert">
                  <i class="ti ti-alert-triangle me-2"></i>
                  <div>
                    {{ __('admin.This organization is currently in draft mode') }}.
                    {{ __('admin.Complete all required steps to publish it') }}.
                  </div>
                </div>
              @endif
            </div>

            @if($isEdit)
              @foreach(range(2, 3) as $step)
                <form action="{{ route('organization.wizard.update.step' . $step, $organization->id) }}" method="POST" id="step{{ $step }}Form" style="display:none;" enctype="multipart/form-data">
                  @csrf @method('PUT')
                  <input type="hidden" name="redirect_to" value="next">
                </form>
              @endforeach
            @endif

            @if($isDraft)
              <form action="{{ $isEdit ? route('organization.wizard.draft.update', $organization->id) : route('organization.wizard.draft') }}" method="POST" enctype="multipart/form-data" id="draftForm" style="display:none;">
                @csrf
                @if($isEdit) @method('PUT') @endif
                <input type="hidden" name="current_step" value="{{ $activeStep }}">
              </form>
            @endif

            @if($isEdit && $isDraft)
              <form action="{{ route('organization.wizard.publish', $organization->id) }}" method="POST" id="publishForm" style="display:none;">
                @csrf
              </form>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@stop

@section('scripts')
  <script src="/vuexy/assets/vendor/libs/bs-stepper/bs-stepper.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/popular.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/bootstrap5.js"></script>
  <script src="/vuexy/assets/vendor/libs/@form-validation/auto-focus.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave.js"></script>
  <script src="/vuexy/assets/vendor/libs/cleavejs/cleave-phone.js"></script>
  <script src="/vuexy/assets/vendor/libs/toastr/toastr.js"></script>

  <script>
    (function() {
      'use strict';

      const config = {
        lang: '{{ $l }}',
        maxStep: {{ $maxStep }},
        isDraft: {{ $isDraft ? 'true' : 'false' }},
        isEdit: {{ $isEdit ? 'true' : 'false' }},
        activeStep: {{ $activeStep }},
        translations: {
          completeStepsFirst: '{{ __("admin.Please complete the previous steps first") }}',
          fixErrors: '{{ __("admin.Please fix the errors before continuing") }}',
          logoInvalidFormat: '{{ __("admin.logo_invalid_format") }}',
          logoTooLarge: '{{ __("admin.logo_too_large") }}',
          errorLoadingStates: '{{ __("admin.Error loading states") }}',
          errorLoadingCities: '{{ __("admin.Error loading cities") }}',
          publishTitle: '{{ __("admin.Publish Organization") }}',
          publishText: '{{ __("admin.Are you sure you want to publish this organization? This action cannot be undone.") }}',
          yesPublish: '{{ __("admin.Yes, Publish") }}',
          cancel: '{{ __("admin.Cancel") }}',
        }
      };

      // Utility functions
      const utils = {
        scrollToError() {
          const firstError = $('.is-invalid').first();
          if (firstError.length) {
            $('html, body').animate({ scrollTop: firstError.offset().top - 100 }, 500);
          }
        },

        showError(message) {
          $('#errorAlert').show().find('#errorAlertMessage').text(message);
        },

        showWarning(message) {
          if (typeof toastr !== 'undefined') toastr.warning(message);
        },

        copyFormDataToTarget(sourceSelector, targetForm, excludeFields = []) {
          const $target = $(targetForm);
          $target.find('input:not([name="_token"]):not([name="_method"]):not([type="file"])').not(function() {
            return excludeFields.includes($(this).attr('name'));
          }).remove();

          $(sourceSelector).find('input, select, textarea').each(function() {
            const $field = $(this);
            const name = $field.attr('name');
            const type = $field.attr('type');

            if (!name || excludeFields.includes(name) || type === 'file') return;

            if (type === 'checkbox' || type === 'radio') {
              if ($field.is(':checked')) {
                $target.append(`<input type="hidden" name="${name}" value="${$field.val()}">`);
              }
            } else if ($field.is('select[multiple]')) {
              const values = $field.val() || [];
              values.forEach(v => $target.append(`<input type="hidden" name="${name}" value="${v}">`));
            } else {
              $target.append(`<input type="hidden" name="${name}" value="${$field.val() || ''}">`);
            }
          });

          // Handle file inputs separately
          $(sourceSelector).find('input[type="file"]').each(function() {
            if (this.files && this.files.length > 0) {
              const cloned = $(this).clone()[0];
              cloned.files = this.files;
              $target.append(cloned);
            }
          });
        }
      };

      // Form Validation Setup
      const validationRules = {
        step1: {
          fields: {
            img_url: {
              validators: {
                file: {
                  extension: 'png,jpg,jpeg',
                  maxSize: 5 * 1024 * 1024,
                  message: '{{ __("admin.accepted_formats") }}: PNG, JPG | {{ __("admin.max_size") }}: 5MB'
                }
              }
            },
            name_en: {
              validators: {
                notEmpty: { message: '{{ __("admin.organization_name_required") }}' },
                stringLength: { max: 255, message: '{{ __("admin.Organization name must not exceed 255 characters") }}' },
                regexp: {
                  regexp: /^[a-zA-Z\u0600-\u06FF ]+$/,
                  message: '{{ __("admin.Organization name must contain only English letters") }}'
                }
              }
            },
            name_ar: {
              validators: {
                notEmpty: { message: '{{ __("admin.organization_name_required") }}' },
                stringLength: { max: 255, message: '{{ __("admin.Organization name must not exceed 255 characters") }}' },
                regexp: {
                  regexp: /^[a-zA-Z\u0600-\u06FF ]+$/,
                  message: '{{ __("admin.Organization name must contain only Arabic letters") }}'
                }
              }
            },
            organization_type_id: {
              validators: {
                notEmpty: { message: '{{ __("admin.organization_type_required") }}' }
              }
            },
            status: {
              validators: {
                notEmpty: { message: '{{ __("admin.status_required") }}' }
              }
            },
            bio: {
              validators: {
                notEmpty: { message: '{{ __("admin.organization_description_required") }}' },
                stringLength: { max: 1000, message: '{{ __("admin.Organization description must not exceed 1000 characters") }}' }
              }
            }
          }
        },
        step2: {
          fields: {
            contact_email: {
              validators: {
                regexp: {
                  regexp: /^[a-zA-Z0-9._%+\-@]+$/,
                  message: '{{ __("admin.Please enter a valid email address") }}'
                },
                emailAddress: { message: '{{ __("admin.Please enter a valid email address") }}' }
              }
            },
            contact_phone: {
              validators: {
                callback: {
                  message: '{{ __("auth.Invalid_Phone_Number") }}',
                  callback: function(input) {
                    if (!input.value.trim()) return true;
                    return window.contactPhoneIti && window.contactPhoneIti.isValidNumber();
                  }
                }
              }
            },
            country_id: {
              validators: {
                notEmpty: { message: '{{ __("admin.Please select the country") }}' }
              }
            },
            state_id: {
              validators: {}
            },
            city_id: {
              validators: {}
            },
            address: {
              validators: {
                stringLength: { max: 500, message: '{{ __("admin.Address must not exceed 500 characters") }}' }
              }
            },
            google_map_url: {
              validators: {
                regexp: {
                  regexp: /^https?:\/\/(www\.)?(google\.[a-z]{2,3}(\/maps)?|maps\.google\.[a-z]{2,3}|goo\.gl|maps\.app\.goo\.gl)/i,
                  message: '{{ __("admin.Please enter a valid Google Maps URL") }}'
                }
              }
            },
            linkedin_company_url: {
              validators: {
                uri: { message: '{{ __("admin.Please enter a valid URL") }}' }
              }
            },
            twitter_handle: {
              validators: {
                uri: { message: '{{ __("admin.Please enter a valid URL") }}' }
              }
            },
            facebook_page_url: {
              validators: {
                uri: { message: '{{ __("admin.Please enter a valid URL") }}' }
              }
            },
            instagram_profile_url: {
              validators: {
                uri: { message: '{{ __("admin.Please enter a valid URL") }}' }
              }
            },
            youtube_channel_url: {
              validators: {
                uri: { message: '{{ __("admin.Please enter a valid URL") }}' }
              }
            },
            tiktok_url: {
              validators: {
                uri: { message: '{{ __("admin.Please enter a valid URL") }}' }
              }
            }
          }
        },
      };

      // Initialize form validations
      const validators = {};

      function initValidation(step) {
        if (step > 2) return;

        const formId = '#wizardForm';
        const rules = validationRules[`step${step}`];

        if (!rules || validators[step]) return;

        validators[step] = FormValidation.formValidation(
          document.querySelector(formId), {
            fields: rules.fields,
            plugins: {
              trigger: new FormValidation.plugins.Trigger(),
              bootstrap5: new FormValidation.plugins.Bootstrap5({
                eleValidClass: '',
                rowSelector: '.mb-4, .mb-6'
              }),
              excluded: new FormValidation.plugins.Excluded({
                excluded: function(field, element, elements) {
                  if (field === 'img_url') return false;
                  return element.getAttribute('type') === 'hidden'
                    || element.style.display === 'none'
                    || element.offsetWidth === 0 && element.offsetHeight === 0;
                }
              }),
              submitButton: new FormValidation.plugins.SubmitButton(),
              autoFocus: new FormValidation.plugins.AutoFocus()
            },
            init: instance => {
              instance.on('plugins.message.placed', function (e) {
                if (e.element.parentElement.classList.contains('input-group')) {
                  e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
                }
                if (e.field === 'img_url') {
                  const container = e.element.closest('.mb-6') || e.element.closest('.col-12');
                  if (container) {
                    container.appendChild(e.messageElement);
                  }
                }
              });
            }
          }
        );
      }

      // Initialize Select2
      function initSelect2() {
        $('.select2').each(function () {
          const $el = $(this);

          if (!$el.hasClass('select2-hidden-accessible')) {
            $el.select2({
              placeholder: $el.data('placeholder'),
              allowClear: true,
              multiple: $el.prop('multiple')
            });
          }

          $el.on('change', function () {
            const $wrapper = $el.closest(
              '.form-group, .col-12, .col-6, .col-4, .col-3'
            );

            const fieldName = $el.attr('name') || $el.data('field');

            // Remove bootstrap validation state
            $el.removeClass('is-invalid');

            // Hide validator message
            $(`[data-field="${fieldName}"]`).hide();

            // Hide validation message
            $wrapper.find('.invalid-feedback').hide();

            // Fix select2 UI invalid state
            $el.next('.select2-container')
              .find('.select2-selection')
              .removeClass('is-invalid');
          });
        });
      }


      // Tab Navigation
      function initTabNavigation() {
        $(document).on('click', '[data-bs-toggle="pill"]', function(e) {
          const $tab = $(this);
          const currentStep = parseInt($('#current_step').val()) || config.activeStep;
          const targetStep = parseInt($tab.data('tab-index'));

          // If clicking on current tab, allow it
          if (targetStep === currentStep) return;

          // If tab is already disabled by maxStep, prevent navigation
          if ($tab.is(':disabled')) {
            e.preventDefault();
            e.stopPropagation();
            utils.showWarning(config.translations.completeStepsFirst);
            return false;
          }

          // Validate current step before allowing navigation to other steps
          const validator = validators[currentStep];
          if (validator) {
            e.preventDefault();
            e.stopPropagation();

            validator.validate().then(status => {
              if (status === 'Valid') {
                // Validation passed, allow navigation
                new bootstrap.Tab(this).show();
              } else {
                // Validation failed, show error and prevent navigation
                utils.scrollToError();
                utils.showError(config.translations.fixErrors);
              }
            });
          }
        });

        $(document).on('click', '.prev-step-btn', function(e) {
          e.preventDefault();
          const targetStep = $(this).data('target-step');
          const selector = `[data-tab-index="${targetStep}"]:visible, [data-tab-index="${targetStep}"][id$="-mobile"]`;
          const tab = $(selector).first()[0];

          if (tab && !$(tab).is(':disabled')) {
            new bootstrap.Tab(tab).show();
          }
        });
      }

      // Form Submission Handler
      function handleFormSubmit(action, currentStep) {
        const validator = validators[currentStep];
        
        if (action === 'draft') {
          const selector = currentStep === 1 ? '#wizardForm' : `#${['basics', 'contact', 'managers'][currentStep - 1]}`;
          utils.copyFormDataToTarget(selector, '#draftForm', ['current_step', '_token', '_method']);
          $('#draftForm').find('[name="current_step"]').val(currentStep);
          $('#draftForm').submit();
          return;
        }

        if (action === 'publish') {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              title: config.translations.publishTitle,
              text: config.translations.publishText,
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: config.translations.yesPublish,
              cancelButtonText: config.translations.cancel,
              customClass: {
                confirmButton: 'btn btn-success me-2',
                cancelButton: 'btn btn-label-secondary'
              },
              buttonsStyling: false
            }).then(result => {
              if (result.isConfirmed) $('#publishForm').submit();
            });
          } else {
            if (confirm(config.translations.publishText)) $('#publishForm').submit();
          }
          return;
        }

        if (!validator) {
          submitForm(action, currentStep);
          return;
        }

        validator.validate().then(status => {
          if (status === 'Valid') {
            submitForm(action, currentStep);
          } else {
            utils.scrollToError();
            utils.showError(config.translations.fixErrors);
          }
        });
      }

      function submitForm(action, step) {
        if (step === 1) {
          $('#redirect_to').val(action === 'same' ? 'same' : 'next');
          $('#wizardForm').submit();
        } else if (config.isEdit) {
          const $form = $(`#step${step}Form`);
          const selector = `#${['basics', 'contact', 'managers'][step - 1]}`;

          utils.copyFormDataToTarget(selector, $form, ['redirect_to', '_token', '_method']);
          $form.find('[name="redirect_to"]').val(action === 'same' ? 'same' : 'next');
          $form.submit();
        }
      }

      // Event Handlers
      function initEventHandlers() {
        // Previous button handler
        $('.btn-prev').on('click', function(e) {
          e.preventDefault();
          const urlParams = new URLSearchParams(window.location.search);
          const currentStep = parseInt(urlParams.get('step')) || 1;

          if (currentStep > 1) {
            const newStep = currentStep - 1;
            urlParams.set('step', newStep);
            window.location.href = window.location.pathname + '?' + urlParams.toString();
          }
        });

        // Save buttons for different steps
        $(document).on('click', '[data-action]', function(e) {
          e.preventDefault();
          const action = $(this).data('action');
          const step = parseInt($('#current_step').val()) || config.activeStep;
          handleFormSubmit(action, step);
        });

        $('.btn-next').on('click', function(e) {
          e.preventDefault();
          const step = parseInt($('#current_step').val()) || config.activeStep;
          handleFormSubmit('next', step);
        });

        $('.save-changes-step1-btn, .save-changes-step2-btn, .save-changes-step3-btn').on('click', function(e) {
          e.preventDefault();
          const step = $(this).hasClass('save-changes-step1-btn') ? 1 : $(this).hasClass('save-changes-step2-btn') ? 2 : 3;
          handleFormSubmit('same', step);
        });

        $('.save-continue-step1-btn, .save-continue-step2-btn, .save-continue-step3-btn').on('click', function(e) {
          e.preventDefault();
          const step = $(this).hasClass('save-continue-step1-btn') ? 1 : $(this).hasClass('save-continue-step2-btn') ? 2 : 3;
          handleFormSubmit('next', step);
        });

        $('.save-draft-step1-btn, .save-draft-step2-btn, .save-draft-step3-btn').on('click', function(e) {
          e.preventDefault();
          const step = $(this).hasClass('save-draft-step1-btn') ? 1 : $(this).hasClass('save-draft-step2-btn') ? 2 : 3;
          handleFormSubmit('draft', step);
        });

        $('#publishBtn').on('click', function(e) {
          e.preventDefault();
          handleFormSubmit('publish', config.activeStep);
        });
      }

      function initStepperWithValidation() {
        const wizardIcons = document.querySelector('.wizard-numbered');
        if (!wizardIcons) return;

        const iconsStepper = new Stepper(wizardIcons, {
          linear: false
        });

        if (config.activeStep > 1) {
          iconsStepper.to(config.activeStep);
        }

        // COMPLETELY disable step trigger clicks from stepper
        document.querySelectorAll('.step-trigger').forEach(trigger => {
          trigger.removeAttribute('data-bs-toggle');
          
          // Remove any existing click handlers from bs-stepper
          const newTrigger = trigger.cloneNode(true);
          trigger.parentNode.replaceChild(newTrigger, trigger);
        });

        wizardIcons.addEventListener('shown.bs-stepper', function(event) {
          const newStep = (event.detail.indexStep !== undefined) ? event.detail.indexStep + 1 : config.activeStep;
          $('#current_step').val(newStep);
        });

        window.iconsStepper = iconsStepper;
      }

      function initStepperClickHandlers() {
        // Now attach fresh handlers after cloning
        $(document).on('click', '.step-trigger', function(e) {
          e.preventDefault();
          e.stopImmediatePropagation(); // Stop ALL other handlers
          
          const $trigger = $(this);
          const targetStep = parseInt($trigger.data('tab-index'));
          const currentStep = parseInt($('#current_step').val()) || config.activeStep;
          
          if ($trigger.is(':disabled') || $trigger.prop('disabled')) {
            utils.showWarning(config.translations.completeStepsFirst);
            return false;
          }
          
          if (targetStep === currentStep) {
            return false;
          }
          
          if (targetStep > currentStep) {
            const validator = validators[currentStep];
            if (validator) {
              validator.validate().then(status => {
                if (status === 'Valid') {
                  updateUrlStep(targetStep);
                } else {
                  utils.scrollToError();
                  utils.showError(config.translations.fixErrors);
                }
              });
              return false;
            }
          }
          
          updateUrlStep(targetStep);
          return false;
        });
      }

      function updateUrlStep(step) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('step', step);
        window.location.href = window.location.pathname + '?' + urlParams.toString();
      }


      // Initialize Everything
      $(document).ready(function() {
        initSelect2();
        initValidation(1);
        initValidation(2);
        initStepperClickHandlers();
        initStepperWithValidation();
        initTabNavigation();
        initEventHandlers();
      });
    })();
  </script>
@stop
