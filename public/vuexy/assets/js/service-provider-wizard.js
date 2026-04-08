/**
 * Form Wizard — Service Provider
 * Refactored with Strict Step Locking
 */

'use strict';

(function () {

  // ─── Translations ────────────────────────────────────────────────────────────

  const translations = {
    en: {
      required: 'This field is required',
      logo: 'Unsupported. Upload PNG, JPG, JPEG, WEBP or SVG | Max size: 5MB',
      logoSize: 'Logo size should not exceed 5MB',
      hero_image: 'Unsupported. Upload PNG, JPG, JPEG, WEBP or SVG | Max size: 10MB',
      phone: 'Should contain numbers only',
      invalidPhone: 'Should be a valid number',
      invalid_url: 'The URL is not valid',
      email: 'E-mail should be a valid email address',
      media_required: 'Media is required',
      invalid_media: 'Invalid file or size too large',
      title_required: 'Title is required',
      title_max: 'Title must be less than 50 chars',
      desc_required: 'Description is required',
      desc_max: 'Description must be less than 100 chars',
      save_and_next: 'Save & Next',
      submit: 'Submit',
      saving: 'Saving...',
      submitting: 'Submitting...',
      stat_value_required: 'Value is required when a label is provided',
      stat_label_required: 'Label is required when a value is provided',
      complete_work_item_details: 'Complete the work item details.',
      unsupported_file_type_portfolio: 'Unsupported file type. Upload PNG, JPG, WEBP, or MP4.',
      file_size_too_large_portfolio: 'File size must be 10MB or less.',
      change_file: 'Change File',
    },
    ar: {
      required: 'هذا الحقل مطلوب',
      logo: 'غير مدعوم. قم بتحميل PNG, JPG, JPEG, WEBP أو SVG | حجم الشعار لا يجب أن يتجاوز 5 MB',
      logoSize: 'حجم الشعار لا يجب أن يتجاوز 5 ميغابايت',
      hero_image: 'غير مدعوم. قم بتحميل PNG, JPG, JPEG, WEBP أو SVG | حجم الصورة لا يجب أن يتجاوز 10 MB',
      phone: 'يجب أن يحتوي على أرقام فقط',
      invalidPhone: 'يجب أن يكون رقم صالح',
      invalid_url: 'الرابط غير صالح',
      email: 'البريد الإلكتروني يجب أن يكون عنوان بريد إلكتروني صالح',
      media_required: 'الملف مطلوب',
      invalid_media: 'ملف غير صالح أو حجم كبير جداً',
      title_required: 'العنوان مطلوب',
      title_max: 'العنوان يجب أن يكون أقل من 50 حرف',
      desc_required: 'الوصف مطلوب',
      desc_max: 'الوصف يجب أن يكون أقل من 100 حرف',
      at_least_one_item: 'يرجى إضافة عمل واحد على الأقل',
      save_and_next: 'حفظ & التالي',
      submit: 'إرسال',
      saving: 'جاري الحفظ...',
      submitting: 'جاري الإرسال...',
      stat_value_required: 'القيمة مطلوبه عند إدخال عنوان',
      stat_label_required: 'العنوان مطلوب عند إدخال قيمة',
      complete_work_item_details: 'يرجى استكمال بيانات العمل',
      unsupported_file_type_portfolio: 'نوع الملف غير مدعوم. يرجى رفع PNG أو JPG أو WEBPأو MP4',
      file_size_too_large_portfolio: 'يجب ألا يزيد حجم الملف عن 10 ميجابايت',
      change_file: 'تغيير الملف',
    }
  };

  // ─── Globals ─────────────────────────────────────────────────────────────────

  const lang = $('html').attr('lang') || 'en';
  const t = translations[lang];

  let iti = null;
  let step1InitialData = null;
  let step2InitialData = null;
  let step3InitialData = null;

  // Tracks which step-trigger buttons are unlocked (0-indexed)
  let completedSteps = new Set([0]);

  let FormValidation1 = null;
  let FormValidation2 = null;
  let FormValidation3 = null;
  let FormValidation4 = null;

  const $select2 = $('.select2');
  const $intlTelInput = $('.intl-tel-input');


  const wizardEl = document.querySelector('#wizard-validation');
  if (!wizardEl) return;

  const wizardForm = wizardEl.querySelector('#service-provider-form-wizard');
  const logoInput = wizardForm.querySelector('input[name="company_logo"]');
  const stepEls = {
    1: wizardForm.querySelector('#step-1-basics'),
    2: wizardForm.querySelector('#step-2-booking'),
    3: wizardForm.querySelector('#step-3-stats'),
    4: wizardForm.querySelector('#step-4-portfolio'),
  };

  // ─── Stepper Initialization ──────────────────────────────────────────────────

  const validationStepper = new Stepper(wizardEl, { linear: false });

  // ─── Step Access Logic ───────────────────────────────────────────────────────

  function updateStepButtons() {
    const currentStep = validationStepper._currentIndex;

    document.querySelectorAll('.step-trigger').forEach((btn, index) => {
      // Step index 0 is always accessible. Others depend on completedSteps Set.
      const isAccessible = index === 0 || completedSteps.has(index);

      btn.disabled = !isAccessible;
      btn.classList.toggle('disabled', !isAccessible);

      // Update styling for visual feedback
      const stepWrapper = btn.closest('.step');
      if (stepWrapper) {
        stepWrapper.style.cursor = isAccessible ? 'pointer' : 'not-allowed';
      }
    });
  }

  // Handle clicking on the top step indicators
  document.querySelectorAll('.step-trigger').forEach((btn, index) => {
    btn.addEventListener('click', (e) => {
      if (index === 3) buildStep4Validation();

      if (index > 0 && !completedSteps.has(index)) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
      validationStepper.to(index + 1);
    });
  });

  // ─── Completion Check Logic ──────────────────────────────────────────────────

  function isStep1Complete() {
    const requiredFields = ['company_name', 'contact_name', 'email', 'phone', 'country', 'state', 'about'];

    // Check text fields
    const allFieldsFilled = requiredFields.every(name => {
      const el = wizardForm.querySelector(`[name="${name}"]`);
      return el && el.value.trim() !== '';
    });

    if (!allFieldsFilled) return false;

    // Email format check
    const emailEl = wizardForm.querySelector('[name="email"]');
    if (emailEl && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value.trim())) return false;

    // Logo check (file or existing preview)
    const logoPreview = document.getElementById('logo-preview-image');
    const hasLogo = (logoInput?.files?.length > 0) || (logoPreview?.getAttribute('src') && !logoPreview.classList.contains('d-none'));

    return hasLogo;
  }

  function isStep2Complete() {
    const requiredFields = ['hero_description', 'services_short_description', 'operating_mode', 'operating_region'];

    const allFieldsFilled = requiredFields.every(name => {
      const el = wizardForm.querySelector(`[name="${name}"]`);
      return el && el.value.trim() !== '';
    });

    if (!allFieldsFilled) return false;

    const heroInput = wizardForm.querySelector('input[name="hero_image"]');
    const existingHero = document.querySelector('#step-2-booking img');
    const hasHero = (heroInput?.files?.length > 0) || (existingHero?.getAttribute('src'));

    return hasHero;
  }

  function isStep3Complete() {
    return true; // Optional fields
  }

  function isStep4Complete() {
    let valid = false;
    $('.work-item-card:visible').each(function () {
      const hasTitle = $(this).find('.title-input').val().trim() !== '';
      const hasMedia = $(this).find('.media-input')[0].files.length > 0 || $(this).find('.upload-area img, .upload-area video').length > 0;
      if (hasTitle && hasMedia) { valid = true; return false; }
    });
    return valid;
  }

  /**
   * Central Synchronization Logic:
   * Re-evaluates all steps. If Step 1 fails, Step 2+ are locked.
   */
  function syncStepUnlockState() {
    // Start fresh: Only Step 1 (index 0) is guaranteed
    completedSteps.clear();
    completedSteps.add(0);

    // If Step 1 is complete, unlock Step 2
    if (isStep1Complete()) {
      completedSteps.add(1);
    } else {
      updateStepButtons();
      return; // Stop here, Step 1 is the gatekeeper
    }

    // If Step 2 is complete, unlock Step 3, 4, 5
    if (isStep2Complete()) {
      completedSteps.add(2);
      completedSteps.add(3);
      completedSteps.add(4);
    } else {
      updateStepButtons();
      return;
    }

    // Step 3 is optional, so if we are here, we check step 4
    if (isStep4Complete()) {
      completedSteps.add(4);
    }

    updateStepButtons();
  }

  function attachRealTimeListeners() {
    // All inputs that affect step 1
    const step1Inputs = ['company_logo', 'company_name', 'contact_name', 'email', 'phone', 'country', 'state', 'about'];
    step1Inputs.forEach(name => {
      $(wizardForm).find(`[name="${name}"]`).on('input change', syncStepUnlockState);
    });

    // All inputs that affect step 2
    const step2Inputs = ['hero_image', 'hero_description', 'services_short_description', 'operating_mode', 'operating_region'];
    step2Inputs.forEach(name => {
      $(wizardForm).find(`[name="${name}"]`).on('input change', syncStepUnlockState);
    });

    // Portfolio listeners
    $('#itemsContainer').on('input change', '.title-input, .desc-input, .media-input', syncStepUnlockState);

    syncStepUnlockState();
  }

  // ─── Helpers ─────────────────────────────────────────────────────────────────

  function getStep1Data() {
    const fd = new FormData(wizardForm);
    const data = {};
    ['company_name', 'contact_name', 'email', 'phone', 'whatsapp', 'country', 'state', 'city', 'about', 'zip', 'address'].forEach(n => data[n] = fd.get(n) || '');
    const logoInput = wizardForm.querySelector('input[name="company_logo"]');
    const logoPreview = document.getElementById('logo-preview-image');
    data.company_logo = (logoInput?.files?.length > 0) ? logoInput.files[0].name : (logoPreview?.getAttribute('src') || '');
    return JSON.stringify(data);
  }

  function getStep2Data() {
    const fd = new FormData(wizardForm);
    const data = {};
    ['hero_description', 'services_short_description', 'appointment_link', 'operating_mode', 'operating_region'].forEach(n => data[n] = fd.get(n) || '');
    const heroInput = wizardForm.querySelector('input[name="hero_image"]');
    const existingHero = document.querySelector('#step-2-booking img');
    data.hero_image = (heroInput?.files?.length > 0) ? `${heroInput.files[0].name}_${heroInput.files[0].size}` : (existingHero?.getAttribute('src') || '');
    return JSON.stringify(data);
  }

  function getStep3Data() {
    const fd = new FormData(wizardForm);
    const data = {};
    data.stats_visibility = document.getElementById('stats_visibility')?.checked ? '1' : '0';
    for (let i = 1; i <= 4; i++) { data[`value${i}`] = fd.get(`value${i}`) || ''; data[`label${i}`] = fd.get(`label${i}`) || ''; }
    return JSON.stringify(data);
  }

  // ─── Phone & Select2 ─────────────────────────────────────────────────────────

  function buildPhoneValidator(required) {
    return {
      message: t.invalidPhone,
      callback: function (input) {
        const value = input.value.trim();
        if (value === '') return { valid: !required };
        if (value.includes('+')) return { valid: false, message: t.phone };
        if (!/^[\d\s\-\(\)]+$/.test(value)) return { valid: false, message: t.phone };
        const itiInstance = $(input.element).data('iti');
        if (!itiInstance) return { valid: /^[\d\s\-\(\)]{8,}$/.test(value), message: t.invalidPhone };
        const selectedCountry = (itiInstance.getSelectedCountryData().iso2 || '').toLowerCase();
        if (selectedCountry === 'eg') {
          const clean = value.replace(/[\s\-\(\)]/g, '');
          const valid = clean.startsWith('0') ? (clean.length === 11 && /^0(10|11|12|15|2)\d{8}$/.test(clean)) : (clean.length === 10 && /^(10|11|12|15|2)\d{8}$/.test(clean));
          return { valid, message: t.invalidPhone };
        }
        return { valid: itiInstance.isValidNumber(), message: t.invalidPhone };
      }
    };
  }

  if ($intlTelInput.length) {
    $intlTelInput.each(function () {
      const $el = $(this);
      const itiInstance = window.intlTelInput($el[0], {
        initialCountry: 'eg',
        preferredCountries: ['eg', 'ae', 'sa'],
        utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js',
        separateDialCode: true,
      });
      $el.data('iti', itiInstance);
      // Set initial country code so hidden field is never empty
      const initialCode = itiInstance.getSelectedCountryData().dialCode;
      if ($el.attr('id') === 'phone' && !$('#country_code').val()) $('#country_code').val(initialCode);
      if ($el.attr('id') === 'whatsapp' && !$('#country_code_w_a').val()) $('#country_code_w_a').val(initialCode);
      $el.on('countrychange', () => {
        const code = itiInstance.getSelectedCountryData().dialCode;
        if ($el.attr('id') === 'phone') $('#country_code').val(code);
        if ($el.attr('id') === 'whatsapp') $('#country_code_w_a').val(code);
        syncStepUnlockState();
      });
    });
  }

  $select2.each(function () {
    const $el = $(this);
    $el.select2().on('change', function () {
      if (FormValidation1) FormValidation1.revalidateField($el.attr('name'));
      if (FormValidation2) FormValidation2.revalidateField($el.attr('name'));
      syncStepUnlockState();
    });
  });

  // ─── Form Validation ─────────────────────────────────────────────────────────

  FormValidation1 = FormValidation.formValidation(stepEls[1], {
    fields: {
      company_logo: {
        validators: {
          callback: {
            message: t.required,
            callback: function (input) {
              const logoPreview = document.getElementById('logo-preview-image');
              const hasNewFile = logoInput?.files?.length > 0;
              const hasPreview = logoPreview?.getAttribute('src') && !logoPreview.classList.contains('d-none');

              // Scroll to parent top
              logoInput.closest('.button-wrapper').scrollIntoView({ behavior: 'smooth', block: 'center' });

              return hasNewFile || !!hasPreview;
            }
          },
          file: {
            extension: 'png,jpg,jpeg,svg,webp',
            maxSize: 5 * 1024 * 1024,
            message: t.logo
          }
        }
      },
      company_name: { validators: { notEmpty: { message: t.required } } },
      contact_name: { validators: { notEmpty: { message: t.required } } },
      email: { validators: { notEmpty: { message: t.required }, emailAddress: { message: t.email } } },
      phone: { validators: { notEmpty: { message: t.required }, callback: buildPhoneValidator(true) } },
      whatsapp: {
        validators: {
          callback: {
            message: t.phone, // or your phone error message
            callback: function (input) {
              const value = input.value.trim();

              // ✅ If empty → valid (skip validation)
              if (value.length === 0) {
                return true;
              }

              // ✅ If not empty → validate phone
              return buildPhoneValidator(true).callback(input);
            }
          }
        }
      },
      country: { validators: { notEmpty: { message: t.required } } },
      state: { validators: { notEmpty: { message: t.required } } },
      about: { validators: { notEmpty: { message: t.required } } },
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger({ event: { file: 'change' } }),
      bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '', rowSelector: '.mb-4' }),
      autoFocus: new FormValidation.plugins.AutoFocus(),
      submitButton: new FormValidation.plugins.SubmitButton(),
    }
  }).on('core.form.valid', () => {
    if (step1InitialData === getStep1Data()) { validationStepper.next(); return; }
    saveStepData(0, FormValidation1);
  });

  FormValidation2 = FormValidation.formValidation(stepEls[2], {
    fields: {
      hero_image: {
        validators: {
          callback: {
            message: t.required,
            callback: function (input) {
              const fileInput = document.getElementById('hero_image');
              const previewContainer = document.getElementById('hero-preview-container');

              const hasNewFile = fileInput.files && fileInput.files.length > 0;
              const hasPreview = previewContainer && !previewContainer.classList.contains('d-none');

              // Valid if user uploaded OR already has preview
              return hasNewFile || hasPreview;
            }
          },
          file: {
            extension: 'png,jpg,jpeg,svg,webp',
            maxSize: 10 * 1024 * 1024,
            message: t.hero_image
          }
        }
      },
      hero_description: { validators: { notEmpty: { message: t.required } } },
      services_short_description: { validators: { notEmpty: { message: t.required } } },
      appointment_link: { validators: { uri: { message: t.invalid_url } } },
      operating_mode: { validators: { notEmpty: { message: t.required } } },
      operating_region: { validators: { notEmpty: { message: t.required } } },
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger({ event: { file: 'change' } }),
      bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '', rowSelector: '.mb-4' }),
      submitButton: new FormValidation.plugins.SubmitButton(),
    }
  }).on('core.form.valid', () => {
    if (step2InitialData === getStep2Data()) { validationStepper.next(); return; }
    saveStepData(1, FormValidation2);
  });

  FormValidation3 = FormValidation.formValidation(stepEls[3], {
    fields: {
      value1: {
        validators: {
          callback: {
            message: translations[lang].stat_value_required,
            callback: function (input) {
              const label = document.getElementById('label1').value.trim();
              return {
                valid: !label || input.value.trim() !== ''
              };
            }
          }
        }
      },
      label1: {
        validators: {
          callback: {
            message: translations[lang].stat_label_required,
            callback: function (input) {
              const value = document.getElementById('value1').value.trim();
              return {
                valid: !value || input.value.trim() !== ''
              };
            }
          }
        }
      },
      value2: {
        validators: {
          callback: {
            message: translations[lang].stat_value_required,
            callback: function (input) {
              const label = document.getElementById('label2').value.trim();
              return {
                valid: !label || input.value.trim() !== ''
              };
            }
          }
        }
      },
      label2: {
        validators: {
          callback: {
            message: translations[lang].stat_label_required,
            callback: function (input) {
              const value = document.getElementById('value2').value.trim();
              return {
                valid: !value || input.value.trim() !== ''
              };
            }
          }
        }
      },
      value3: {
        validators: {
          callback: {
            message: translations[lang].stat_value_required,
            callback: function (input) {
              const label = document.getElementById('label3').value.trim();
              return {
                valid: !label || input.value.trim() !== ''
              };
            }
          }
        }
      },
      label3: {
        validators: {
          callback: {
            message: translations[lang].stat_label_required,
            callback: function (input) {
              const value = document.getElementById('value3').value.trim();
              return {
                valid: !value || input.value.trim() !== ''
              };
            }
          }
        }
      },
      value4: {
        validators: {
          callback: {
            message: translations[lang].stat_value_required,
            callback: function (input) {
              const label = document.getElementById('label4').value.trim();
              return {
                valid: !label || input.value.trim() !== ''
              };
            }
          }
        }
      },
      label4: {
        validators: {
          callback: {
            message: translations[lang].stat_label_required,
            callback: function (input) {
              const value = document.getElementById('value4').value.trim();
              return {
                valid: !value || input.value.trim() !== ''
              };
            }
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: '.mb-4'
      }),
      autoFocus: new FormValidation.plugins.AutoFocus(),
      submitButton: new FormValidation.plugins.SubmitButton()
    }
  })
    .on('core.form.valid', function () {
      // If no changes → skip AJAX
      if (step3InitialData === getStep3Data()) { validationStepper.next(); return; }
      // Otherwise save
      saveStepData(2, FormValidation3);
    })
    .on('core.validator.validated', function (e) {
      // When a field is validated, revalidate its paired field
      const fieldPairs = {
        'value1': 'label1',
        'label1': 'value1',
        'value2': 'label2',
        'label2': 'value2',
        'value3': 'label3',
        'label3': 'value3',
        'value4': 'label4',
        'label4': 'value4'
      };

      const pairedField = fieldPairs[e.field];
      if (pairedField && !e.result.valid) {
        // Only revalidate if current field became invalid
        FormValidation3.revalidateField(pairedField);
      }
    });


  // Add input event listeners to trigger cross-validation
  for (let i = 1; i <= 4; i++) {
    const valueField = document.getElementById(`value${i}`);
    const labelField = document.getElementById(`label${i}`);

    valueField.addEventListener('input', function () {
      if (labelField.value.trim()) {
        FormValidation3.revalidateField(`label${i}`);
      }
    });

    labelField.addEventListener('input', function () {
      if (valueField.value.trim()) {
        FormValidation3.revalidateField(`value${i}`);
      }
    });
  }

  // ─── Core Wizard Actions ─────────────────────────────────────────────────────

  wizardEl.querySelector('.btn-next').addEventListener('click', () => {
    const idx = validationStepper._currentIndex;
    if (idx === 0) FormValidation1.validate();
    else if (idx === 1) FormValidation2.validate();
    else if (idx === 2) {
      FormValidation3.validate();
    }
    else if (idx === 3) {
      clearPortfolioErrors();
      if (!FormValidation4) buildStep4Validation();
      FormValidation4.validate();
    }
    else if (idx === 4) submitFormData();
  });

  wizardEl.querySelector('.btn-prev').addEventListener('click', () => {
    validationStepper.previous();
  });

  function buildStep4Validation() {
    if (FormValidation4) {
      try { FormValidation4.destroy(); } catch (_) { /* suppressed */ }
      FormValidation4 = null;
    }

    const fields = {};
    const $cards = $('.work-item-card');

    $cards.each(function (index) {
      const $card = $(this);
      const names = {
        id: `portfolio[${index}][id]`,
        del: `portfolio[${index}][delete]`,
        media: `portfolio[${index}][media]`,
        title: `portfolio[${index}][title]`,
        desc: `portfolio[${index}][description]`,
      };

      $card.find('.item-id').attr('name', names.id);
      $card.find('.delete-flag').attr('name', names.del);
      $card.find('.media-input').attr('name', names.media);
      $card.find('.title-input').attr('name', names.title);
      $card.find('.desc-input').attr('name', names.desc);

      if ($card.find('.delete-flag').val() === 'true' || !$card.is(':visible')) return;

      fields[names.media] = {
        validators: {
          callback: {
            message: t.complete_work_item_details,
            callback: (input) => {
              const $item = $(input.element).closest('.work-item-card');
              const hasFile = input.element.files && input.element.files.length > 0;
              const hasExist = $item.find('.upload-area img, .upload-area video').length > 0;
              const title = $item.find('.title-input').val().trim();
              const desc = $item.find('.desc-input').val().trim();
              if (title || desc) return hasFile || hasExist;
              return true;
            }
          },
          file: {
            extension: 'png,jpg,jpeg,webp,mp4',
            maxSize: 10485760,
            message: t.unsupported_file_type_portfolio,
          }
        }
      };

      fields[names.title] = {
        validators: {
          stringLength: { max: 50, message: t.title_max },
          callback: {
            message: t.complete_work_item_details,
            callback: (input) => {
              const $item = $(input.element).closest('.work-item-card');
              const hasFile = $item.find('.media-input')[0].files.length > 0;
              const hasExist = $item.find('.upload-area img, .upload-area video').length > 0;
              const desc = $item.find('.desc-input').val().trim();
              if (desc || hasFile || hasExist) return input.value.trim() !== '';
              return true;
            }
          }
        }
      };

      fields[names.desc] = {
        validators: {
          stringLength: { max: 100, message: t.desc_max },
          callback: {
            message: t.complete_work_item_details,
            callback: (input) => {
              const $item = $(input.element).closest('.work-item-card');
              const hasFile = $item.find('.media-input')[0].files.length > 0;
              const hasExist = $item.find('.upload-area img, .upload-area video').length > 0;
              const title = $item.find('.title-input').val().trim();
              if (title || hasFile || hasExist) return input.value.trim() !== '';
              return true;
            }
          }
        }
      };
    });

    FormValidation4 = FormValidation.formValidation(stepEls[4], {
      fields,
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: '',
          rowSelector: (field) =>
            field === '_portfolio_at_least_one' ? '#portfolio-error-container' : '.mb-4, .col-md-4, .col-md-8',
        }),
        autoFocus: new FormValidation.plugins.AutoFocus(),
        submitButton: new FormValidation.plugins.SubmitButton(),
      }
    }).on('core.form.valid', () => saveStepData(3, FormValidation4));
  }

  window.rebuildPortfolioValidation = buildStep4Validation;

  function clearPortfolioErrors() {
    $('.work-item-card .server-error').remove();
    $('.work-item-card').removeClass('border-danger');
  }

  function displayPortfolioErrors(errors) {
    clearPortfolioErrors();

    const fieldLabels = {
      id: lang === 'ar' ? 'العنصر' : 'Item',
      media: lang === 'ar' ? 'الملف' : 'Media',
      title: lang === 'ar' ? 'العنوان' : 'Title',
      description: lang === 'ar' ? 'الوصف' : 'Description',
    };

    // Group errors by portfolio index: portfolio.0.title -> index 0
    const grouped = {};
    for (const [key, messages] of Object.entries(errors)) {
      const match = key.match(/^portfolio\.(\d+)\.(\w+)$/);
      if (!match) continue;
      const idx = parseInt(match[1], 10);
      const field = match[2];
      if (!grouped[idx]) grouped[idx] = [];
      grouped[idx].push({ field, messages });
    }

    const $cards = $('.work-item-card');
    let firstErrorCard = null;

    for (const [idx, fieldErrors] of Object.entries(grouped)) {
      const $card = $cards.eq(parseInt(idx, 10));
      if (!$card.length) continue;

      $card.addClass('border-danger');
      if (!firstErrorCard) firstErrorCard = $card;

      for (const { field, messages } of fieldErrors) {
        const label = fieldLabels[field] || field;
        const $target = field === 'media'
          ? $card.find('.file-error')
          : $card.find(`[name="portfolio[${idx}][${field}]"]`);

        if (field === 'media') {
          $target.html(messages.join('<br>')).removeClass('d-none');
        } else {
          const errorHtml = messages
            .map(m => `<div class="server-error text-danger small mt-1">${m}</div>`)
            .join('');
          $target.after(errorHtml);
        }
      }
    }

    // Handle non-portfolio errors (e.g. portfolio_visibility, portfolio_description)
    const nonPortfolioErrors = Object.entries(errors)
      .filter(([key]) => !key.match(/^portfolio\.\d+\./))
      .flatMap(([, msgs]) => msgs);

    if (nonPortfolioErrors.length) {
      releaseFailureAlert({
        title: lang === 'ar' ? 'خطأ!' : 'Error!',
        message: nonPortfolioErrors.join('\n'),
      });
    }

    if (firstErrorCard) {
      firstErrorCard[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  function saveStepData(stepIndex, formValidationInstance) {
    console.log('Step', stepIndex + 1, 'save triggered');
    const route = wizardForm.getAttribute(`data-route-step-${stepIndex + 1}`);
    const nextBtn = document.querySelector('.btn-next');
    const $span = $(nextBtn).find('span');
    const origText = $span.text();

    nextBtn.disabled = true;
    $span.text(t.saving);

    $.ajax({
      url: route,
      type: 'POST',
      data: new FormData(wizardForm),
      processData: false,
      contentType: false,
      headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
      success(res) {
        nextBtn.disabled = false;
        $span.text(origText);
        if (res.success) {
          syncStepUnlockState();
          validationStepper.next();
        }
      },
      error(xhr) {
        nextBtn.disabled = false;
        $span.text(origText);

        const errors = xhr.responseJSON?.errors;
        if (errors && stepIndex === 3) {
          displayPortfolioErrors(errors);
        } else {
          releaseFailureAlert({
            title: lang === 'ar' ? 'خطأ!' : 'Error!',
            message: xhr.responseJSON?.message || (lang === 'ar' ? 'حدث خطأ أثناء الحفظ' : 'An error occurred while saving'),
          });
        }
      }
    });
  }

  /* function submitFormData() {
    releaseSuccessAlert({
      title: lang === 'ar' ? 'تم!' : 'Done!',
      message: lang === 'ar' ? 'تم حفظ الملف الشخصي بنجاح' : 'Profile saved successfully',
    });
    setTimeout(() => { window.location.href = '/dashboard/services_providers'; }, 1500);
  } */

  // ─── Initialization ──────────────────────────────────────────────────────────

  logoInput?.addEventListener('change', () => {
    FormValidation1.revalidateField('company_logo');
    syncStepUnlockState();
  });

  document.addEventListener('DOMContentLoaded', () => {
    step1InitialData = getStep1Data();
    step2InitialData = getStep2Data();
    step3InitialData = getStep3Data();

    attachRealTimeListeners();
    syncStepUnlockState();
  });

})();