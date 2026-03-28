/**
 * Form Wizard — Service Provider
 * Refactored with real-time step unlock
 */

'use strict';

(function () {

  // ─── Translations ────────────────────────────────────────────────────────────

  const translations = {
    en: {
      required:                        'This field is required',
      logo:                            'Unsupported. Upload PNG, JPG, JPEG, WEBP or SVG | Max size: 5MB',
      logoSize:                        'Logo size should not exceed 5MB',
      hero_image:                      'Unsupported. Upload PNG, JPG, JPEG, WEBP or SVG | Max size: 10MB',
      phone:                           'Should contain numbers only',
      invalidPhone:                    'Should be a valid number',
      invalid_url:                     'The URL is not valid',
      email:                           'E-mail should be a valid email address',
      media_required:                  'Media is required',
      invalid_media:                   'Invalid file or size too large',
      title_required:                  'Title is required',
      title_max:                       'Title must be less than 50 chars',
      desc_required:                   'Description is required',
      desc_max:                        'Description must be less than 100 chars',
            save_and_next:                   'Save & Next',
      submit:                          'Submit',
      saving:                          'Saving...',
      submitting:                      'Submitting...',
      stat_value_required:             'Value is required when a label is provided',
      stat_label_required:             'Label is required when a value is provided',
      complete_work_item_details:      'Complete the work item details.',
      unsupported_file_type_portfolio: 'Unsupported file type. Upload PNG, JPG, WEBP, or MP4.',
      file_size_too_large_portfolio:   'File size must be 10MB or less.',
      change_file:                     'Change File',
    },
    ar: {
      required:                        'هذا الحقل مطلوب',
      logo:                            'غير مدعوم. قم بتحميل PNG, JPG, JPEG, WEBP أو SVG | حجم الشعار لا يجب أن يتجاوز 5 MB',
      logoSize:                        'حجم الشعار لا يجب أن يتجاوز 5 ميغابايت',
      hero_image:                      'غير مدعوم. قم بتحميل PNG, JPG, JPEG, WEBP أو SVG | حجم الصورة لا يجب أن يتجاوز 10 MB',
      phone:                           'يجب أن يحتوي على أرقام فقط',
      invalidPhone:                    'يجب أن يكون رقم صالح',
      invalid_url:                     'الرابط غير صالح',
      email:                           'البريد الإلكتروني يجب أن يكون عنوان بريد إلكتروني صالح',
      media_required:                  'الملف مطلوب',
      invalid_media:                   'ملف غير صالح أو حجم كبير جداً',
      title_required:                  'العنوان مطلوب',
      title_max:                       'العنوان يجب أن يكون أقل من 50 حرف',
      desc_required:                   'الوصف مطلوب',
      desc_max:                        'الوصف يجب أن يكون أقل من 100 حرف',
      at_least_one_item:               'يرجى إضافة عمل واحد على الأقل',
      save_and_next:                   'حفظ & التالي',
      submit:                          'إرسال',
      saving:                          'جاري الحفظ...',
      submitting:                      'جاري الإرسال...',
      stat_value_required:             'القيمة مطلوبه عند إدخال عنوان',
      stat_label_required:             'العنوان مطلوب عند إدخال قيمة',
      complete_work_item_details:      'يرجى استكمال بيانات العمل',
      unsupported_file_type_portfolio: 'نوع الملف غير مدعوم. يرجى رفع PNG أو JPG أو WEBPأو MP4',
      file_size_too_large_portfolio:   'يجب ألا يزيد حجم الملف عن 10 ميجابايت',
      change_file:                     'تغيير الملف',
    }
  };

  // ─── Globals ─────────────────────────────────────────────────────────────────

  const lang = $('html').attr('lang') || 'en';
  const t    = translations[lang];

  let iti              = null; // intl-tel-input instance for #phone (legacy ref)
  let step1InitialData = null;
  let step2InitialData = null;
  let step3InitialData = null;

  // Tracks which step-trigger buttons should be clickable (0-indexed)
  let completedSteps = new Set([0]);

  // FormValidation instances — populated during init
  let FormValidation1 = null;
  let FormValidation2 = null;
  let FormValidation3 = null;
  let FormValidation4 = null;

  // ─── Bootstrap / jQuery aliases ──────────────────────────────────────────────

  const $select2      = $('.select2');
  const $intlTelInput = $('.intl-tel-input');

  // ─── Guard: only run if the wizard exists on this page ───────────────────────

  const wizardEl = document.querySelector('#wizard-validation');
  if (!wizardEl) return;

  const wizardForm      = wizardEl.querySelector('#service-provider-form-wizard');
  const stepEls         = {
    1: wizardForm.querySelector('#step-1-basics'),
    2: wizardForm.querySelector('#step-2-booking'),
    3: wizardForm.querySelector('#step-3-stats'),
    4: wizardForm.querySelector('#step-4-portfolio'),
  };

  // ─── Stepper ─────────────────────────────────────────────────────────────────

  const validationStepper = new Stepper(wizardEl, { linear: false });

  // ─── Step-button enable/disable ──────────────────────────────────────────────

  function updateStepButtons() {
    const currentStep = validationStepper._currentIndex;

    document.querySelectorAll('.step-trigger').forEach((btn, index) => {
      const accessible = index <= currentStep || completedSteps.has(index);
      btn.disabled                    = !accessible;
      btn.style.cursor                = accessible ? 'pointer' : 'default';
      btn.parentElement.style.cursor  = accessible ? 'pointer' : 'default';
    });
  }

  // Allow clicking step-trigger buttons only for accessible steps
  document.querySelectorAll('.step-trigger').forEach((btn, index) => {
    console.log("index", index);
    btn.addEventListener('click', () => {
      if (index === 3) {
        buildStep4Validation();
      }
      const currentStep = validationStepper._currentIndex;
      if (index <= currentStep || completedSteps.has(index)) {
        validationStepper.to(index + 1);
      }
    });
  });

  // Initialise buttons after stepper is ready
  setTimeout(updateStepButtons, 100);

  // ─── Real-time completion checks ─────────────────────────────────────────────

  /**
   * Returns true when all required Step 1 fields are filled and valid enough
   * to unlock the Step 2 trigger button.
   */
  function isStep1Complete() {
    const requiredTextFields = [
      'company_name', 'contact_name', 'email', 'phone', 'country', 'state', 'about'
    ];

    for (const name of requiredTextFields) {
      const el = wizardForm.querySelector(`[name="${name}"]`);
      if (!el || el.value.trim() === '') return false;
    }

    // Basic email sanity check
    const emailEl = wizardForm.querySelector('[name="email"]');
    if (emailEl && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value.trim())) return false;

    // Logo: file selected OR existing preview visible
    const logoInput   = wizardForm.querySelector('input[name="company_logo"]');
    const logoPreview = document.getElementById('logo-preview-image');
    const hasLogo =
      (logoInput && logoInput.files && logoInput.files.length > 0) ||
      (logoPreview && logoPreview.getAttribute('src') && !logoPreview.classList.contains('d-none'));
    if (!hasLogo) return false;

    return true;
  }

  /**
   * Returns true when all required Step 2 fields are filled and a hero image
   * is present, unlocking Steps 3–5.
   */
  function isStep2Complete() {
    const requiredTextFields = [
      'hero_description', 'services_short_description', 'operating_mode', 'operating_region'
    ];

    for (const name of requiredTextFields) {
      const el = wizardForm.querySelector(`[name="${name}"]`);
      if (!el || el.value.trim() === '') return false;
    }

    // Hero image: new file selected OR existing image in DOM
    const heroInput    = wizardForm.querySelector('input[name="hero_image"]');
    const existingHero = document.querySelector('#step-2-booking img');
    const hasHero =
      (heroInput && heroInput.files && heroInput.files.length > 0) ||
      (existingHero && existingHero.getAttribute('src'));
    if (!hasHero) return false;

    return true;
  }

  /**
   * Step 3 (Key Stats) fields are all optional — treated as always passable.
   */
  function isStep3Complete() {
    return true;
  }

  /**
   * Returns true when at least one portfolio card has both a title and media.
   */
  function isStep4Complete() {
    let valid = false;
    $('.work-item-card:visible').each(function () {
      const hasTitle = $(this).find('.title-input').val().trim() !== '';
      const hasMedia =
        $(this).find('.media-input')[0].files.length > 0 ||
        $(this).find('.upload-area img, .upload-area video').length > 0;
      if (hasTitle && hasMedia) { valid = true; return false; }
    });
    return valid;
  }

  /**
   * Central function: re-evaluates every step's completion state and calls
   * updateStepButtons() so the UI reflects the current state immediately.
   */
  function syncStepUnlockState() {
    if (isStep1Complete()) {
      completedSteps.add(0);
      completedSteps.add(1); // unlocks step-2 trigger
    }

    if (isStep2Complete()) {
      completedSteps.add(1);
      completedSteps.add(2);
      completedSteps.add(3);
      completedSteps.add(4); // unlocks steps 3-5 triggers
    }

    // Step 3 is always passable once reachable
    if (completedSteps.has(2) && isStep3Complete()) {
      completedSteps.add(2);
      completedSteps.add(3);
      completedSteps.add(4);
    }

    if (isStep4Complete()) {
      completedSteps.add(3);
      completedSteps.add(4); // unlocks step-5 trigger
    }

    updateStepButtons();
  }

  /**
   * Attach live input/change listeners across all steps.
   * Uses event delegation on #itemsContainer for dynamically added portfolio cards.
   */
  function attachRealTimeListeners() {
    // ── Step 1 ──────────────────────────────────────────────────────────────
    ['company_logo', 'company_name', 'contact_name', 'email', 'phone', 'country', 'state', 'city', 'about']
      .forEach(name => {
        const el = wizardForm.querySelector(`[name="${name}"]`);
        if (el) el.addEventListener('input', syncStepUnlockState);
      });

    const logoInput = wizardForm.querySelector('input[name="company_logo"]');
    if (logoInput) logoInput.addEventListener('change', syncStepUnlockState);

    // Select2 fields fire jQuery 'change', not native 'input'
    $(wizardForm)
      .find('select[name="country"], select[name="state"]')
      .on('change', syncStepUnlockState);

    // intl-tel-input flag/country change
    $(wizardForm).find('.intl-tel-input').on('countrychange', syncStepUnlockState);

    // ── Step 2 ──────────────────────────────────────────────────────────────
    ['hero_description', 'services_short_description', 'appointment_link', 'operating_mode', 'operating_region']
      .forEach(name => {
        const el = wizardForm.querySelector(`[name="${name}"]`);
        if (el) el.addEventListener('input', syncStepUnlockState);
      });

    const heroInput = wizardForm.querySelector('input[name="hero_image"]');
    if (heroInput) heroInput.addEventListener('change', syncStepUnlockState);

    $(wizardForm)
      .find('select[name="operating_mode"], select[name="operating_region"]')
      .on('change', syncStepUnlockState);

    // ── Step 3 ──────────────────────────────────────────────────────────────
    for (let i = 1; i <= 4; i++) {
      document.getElementById(`value${i}`)?.addEventListener('input', syncStepUnlockState);
      document.getElementById(`label${i}`)?.addEventListener('input', syncStepUnlockState);
    }

    // ── Step 4 — delegated so it covers dynamically added cards ─────────────
    $('#itemsContainer').on('input change', '.title-input, .desc-input, .media-input', syncStepUnlockState);

    // Run once immediately to reflect any pre-filled / edit-mode data
    syncStepUnlockState();
  }

  // ─── Snapshot helpers (change-detection before AJAX) ─────────────────────────

  function getStep1Data() {
    const fd = new FormData(wizardForm);
    const fields = [
      'company_name', 'contact_name', 'email', 'phone',
      'whatsapp', 'country', 'state', 'city', 'about', 'zip', 'address',
    ];
    const data = {};
    fields.forEach(name => { data[name] = fd.get(name) || ''; });

    const logoInput   = wizardForm.querySelector('input[name="company_logo"]');
    const logoPreview = document.getElementById('logo-preview-image');
    data.company_logo =
      (logoInput && logoInput.files && logoInput.files.length > 0)
        ? logoInput.files[0].name
        : (logoPreview && logoPreview.getAttribute('src') ? logoPreview.getAttribute('src') : '');

    return JSON.stringify(data);
  }

  function getStep2Data() {
    const fd = new FormData(wizardForm);
    const fields = [
      'hero_description', 'services_short_description',
      'appointment_link', 'operating_mode', 'operating_region',
    ];
    const data = {};
    fields.forEach(name => { data[name] = fd.get(name) || ''; });

    const heroInput    = wizardForm.querySelector('input[name="hero_image"]');
    const existingHero = document.querySelector('#step-2-booking img');
    if (heroInput && heroInput.files && heroInput.files.length > 0) {
      const f = heroInput.files[0];
      data.hero_image = `${f.name}_${f.size}`;
    } else {
      data.hero_image = existingHero ? existingHero.getAttribute('src') : '';
    }

    return JSON.stringify(data);
  }

  function getStep3Data() {
    const fd     = new FormData(wizardForm);
    const data   = {};
    const fields = ['value1','label1','value2','label2','value3','label3','value4','label4'];
    fields.forEach(name => { data[name] = fd.get(name) || ''; });
    return JSON.stringify(data);
  }

  // ─── Load previously completed steps from existing DB data ───────────────────

  function loadCompletedSteps() {
    const step1Data    = JSON.parse(step1InitialData);
    const hasStep1Data = Object.values(step1Data).some(v => v !== '');
    if (hasStep1Data) { completedSteps.add(0); completedSteps.add(1); }

    const step2Data    = JSON.parse(step2InitialData);
    const hasStep2Data = Object.values(step2Data).some(v => v !== '');
    if (hasStep2Data) { completedSteps.add(1); completedSteps.add(2); }

    if (hasStep1Data && hasStep2Data) {
      completedSteps.add(2); completedSteps.add(3); completedSteps.add(4);
    }

    const step3Data    = JSON.parse(step3InitialData);
    const hasStep3Data = Object.values(step3Data).some(v => v !== '');
    if (hasStep3Data) { completedSteps.add(2); completedSteps.add(3); completedSteps.add(4); }

    const $portfolioItems = $('.work-item-card:visible');
    if ($portfolioItems.length > 0) {
      $portfolioItems.each(function () {
        const hasTitle = $(this).find('.title-input').val().trim() !== '';
        const hasMedia =
          $(this).find('.media-input')[0].files.length > 0 ||
          $(this).find('.upload-area img, .upload-area video').length > 0;
        if (hasTitle && hasMedia) {
          completedSteps.add(3); completedSteps.add(4);
          return false;
        }
      });
    }
  }

  // ─── Phone: intl-tel-input ───────────────────────────────────────────────────

  /**
   * Builds the phone validator callback (shared between #phone and #whatsapp).
   * @param {boolean} required  – whether an empty value should fail
   */
  function buildPhoneValidator(required) {
    return {
      message: t.invalidPhone,
      callback: function (input) {
        const value = input.value.trim();

        if (value === '') return { valid: !required };

        if (value.includes('+')) return { valid: false, message: t.phone };

        if (!/^[\d\s\-\(\)]+$/.test(value)) return { valid: false, message: t.phone };

        const itiInstance = $(input.element).data('iti');

        if (!itiInstance) {
          return { valid: /^[\d\s\-\(\)]{8,}$/.test(value), message: t.invalidPhone };
        }

        const selectedCountry = (itiInstance.getSelectedCountryData().iso2 || '').toLowerCase();

        if (selectedCountry === 'eg') {
          const clean = value.replace(/[\s\-\(\)]/g, '');
          if (clean.startsWith('0')) {
            return {
              valid: clean.length === 11 && /^0(10|11|12|15|2)\d{8}$/.test(clean),
              message: t.invalidPhone,
            };
          }
          return {
            valid: clean.length === 10 && /^(10|11|12|15|2)\d{8}$/.test(clean),
            message: t.invalidPhone,
          };
        }

        if (typeof itiInstance.isValidNumber === 'function') {
          return { valid: itiInstance.isValidNumber(), message: t.invalidPhone };
        }

        return { valid: /^[\d\s\-\(\)]{8,}$/.test(value), message: t.invalidPhone };
      }
    };
  }

  if ($intlTelInput.length) {
    $intlTelInput.each(function () {
      const $el    = $(this);
      const inputId = $el.attr('id');

      if (typeof window.intlTelInput !== 'function') return;

      const itiInstance = window.intlTelInput($el[0], {
        initialCountry:    'eg',
        preferredCountries: ['eg', 'ae', 'sa', 'ma', 'dz', 'tn'],
        utilsScript:       'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js',
        formatOnDisplay:   false,
        separateDialCode:  true,
        autoPlaceholder:   'aggressive',
        customPlaceholder: (placeholder) => `e.g. ${placeholder}`,
      });

      $el.data('iti', itiInstance);

      // Legacy global reference
      if (inputId === 'phone') iti = itiInstance;

      const updateDialCode = () => {
        const dialCode = itiInstance.getSelectedCountryData().dialCode;
        if (inputId === 'phone')     $('#country_code').val(dialCode);
        if (inputId === 'whatsapp')  $('#country_code_w_a').val(dialCode);
      };

      // Initialise hidden dial-code field if not pre-filled
      if (inputId === 'phone'    && !$('#country_code').val())     updateDialCode();
      if (inputId === 'whatsapp' && !$('#country_code_w_a').val()) updateDialCode();

      $el.on('countrychange', () => {
        updateDialCode();
        if (inputId === 'phone'    && FormValidation1) FormValidation1.revalidateField('phone');
        if (inputId === 'whatsapp' && FormValidation1) FormValidation1.revalidateField('whatsapp');
      });
    });
  }

  // ─── Select2 ─────────────────────────────────────────────────────────────────

  $select2.each(function () {
    const $el = $(this);
    $el.wrap('<div class="position-relative"></div>');
    $el.select2({ dropdownParent: $el.parent() });

    $el.on('change', function () {
      const stepIndex = validationStepper._currentIndex;
      if (stepIndex === 0 && FormValidation1) FormValidation1.revalidateField($el.attr('name'));
      if (stepIndex === 1 && FormValidation2) FormValidation2.revalidateField($el.attr('name'));
    });
  });

  // ─── FormValidation – Step 1: Profile Basics ─────────────────────────────────

  FormValidation1 = FormValidation.formValidation(stepEls[1], {
    fields: {
      company_logo: {
        validators: {
          callback: {
            message: t.required,
            callback: (input) => {
              if (input.value) return true;
              const preview = document.getElementById('logo-preview-image');
              return !!(preview && preview.getAttribute('src') && !preview.classList.contains('d-none'));
            }
          },
          file: {
            extension: 'png,jpg,jpeg,svg,webp',
            maxSize:   5 * 1024 * 1024,
            message:   t.logo,
          }
        }
      },
      company_name:  { validators: { notEmpty: { message: t.required } } },
      contact_name:  { validators: { notEmpty: { message: t.required } } },
      email: {
        validators: {
          notEmpty:     { message: t.required },
          emailAddress: { message: t.email },
        }
      },
      phone: {
        validators: {
          notEmpty: { message: t.required },
          callback: buildPhoneValidator(true),
        }
      },
      whatsapp: {
        validators: {
          callback: buildPhoneValidator(false),
        }
      },
      country: { validators: { notEmpty: { message: t.required } } },
      state:   { validators: { notEmpty: { message: t.required } } },
      city:    { validators: {} },
      about:   { validators: { notEmpty: { message: t.required } } },
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger({ event: { file: 'change' } }),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector:   '.mb-4',
      }),
      autoFocus:    new FormValidation.plugins.AutoFocus(),
      submitButton: new FormValidation.plugins.SubmitButton(),
    },
    init: (instance) => {
      instance.on('plugins.message.placed', (e) => {
        if (e.element.parentElement.classList.contains('input-group')) {
          e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
        }
      });
    }
  }).on('core.form.valid', () => {
    const currentData = getStep1Data();
    if (step1InitialData === currentData) {
      completedSteps.add(0); completedSteps.add(1);
      updateStepButtons();
      validationStepper.next();
      return;
    }
    saveStepData(0, FormValidation1);
  });

  // ─── FormValidation – Step 2: Intro & Booking ────────────────────────────────

  FormValidation2 = FormValidation.formValidation(stepEls[2], {
    fields: {
      hero_image: {
        validators: {
          callback: {
            message: t.required,
            callback: (input) => {
              if (input.value) return true;
              const preview = stepEls[2].querySelector('img');
              return !!(preview && preview.getAttribute('src'));
            }
          },
          file: {
            extension: 'png,jpg,jpeg,svg,webp',
            maxSize:   10 * 1024 * 1024,
            message:   t.hero_image,
          }
        }
      },
      hero_description:          { validators: { notEmpty: { message: t.required } } },
      services_short_description:{ validators: { notEmpty: { message: t.required } } },
      appointment_link:          { validators: { uri: { message: t.invalid_url } } },
      operating_mode:            { validators: { notEmpty: { message: t.required } } },
      operating_region:          { validators: { notEmpty: { message: t.required } } },
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger({ event: { file: 'change' } }),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector:   '.mb-4',
      }),
      autoFocus:    new FormValidation.plugins.AutoFocus(),
      submitButton: new FormValidation.plugins.SubmitButton(),
    }
  }).on('core.form.valid', () => {
    const currentData = getStep2Data();
    if (step2InitialData === currentData) {
      completedSteps.add(1); completedSteps.add(2);
      updateStepButtons();
      validationStepper.next();
      return;
    }
    saveStepData(1, FormValidation2);
  });

  // ─── FormValidation – Step 3: Key Stats ──────────────────────────────────────

  /**
   * Builds a paired value/label validator for a stat row.
   * @param {'value'|'label'} kind   – which side this field is
   * @param {number}          index  – stat row index (1–4)
   */
  function buildStatValidator(kind, index) {
    const pairedId  = kind === 'value' ? `label${index}` : `value${index}`;
    const message   = kind === 'value' ? t.stat_value_required : t.stat_label_required;
    return {
      validators: {
        callback: {
          message,
          callback: (input) => {
            const pairedVal = document.getElementById(pairedId)?.value.trim();
            return { valid: !pairedVal || input.value.trim() !== '' };
          }
        }
      }
    };
  }

  const step3Fields = {};
  for (let i = 1; i <= 4; i++) {
    step3Fields[`value${i}`] = buildStatValidator('value', i);
    step3Fields[`label${i}`] = buildStatValidator('label', i);
  }

  FormValidation3 = FormValidation.formValidation(stepEls[3], {
    fields: step3Fields,
    plugins: {
      trigger:      new FormValidation.plugins.Trigger(),
      bootstrap5:   new FormValidation.plugins.Bootstrap5({ eleValidClass: '', rowSelector: '.mb-4' }),
      autoFocus:    new FormValidation.plugins.AutoFocus(),
      submitButton: new FormValidation.plugins.SubmitButton(),
    }
  })
  .on('core.form.valid', () => {
    const currentData = getStep3Data();
    if (step3InitialData === currentData) {
      completedSteps.add(2); completedSteps.add(3);
      updateStepButtons();
      validationStepper.next();
      return;
    }
    saveStepData(2, FormValidation3);
  })
  .on('core.validator.validated', (e) => {
    // Cross-revalidate the paired field when one becomes invalid
    const pairs = {
      value1:'label1', label1:'value1',
      value2:'label2', label2:'value2',
      value3:'label3', label3:'value3',
      value4:'label4', label4:'value4',
    };
    const paired = pairs[e.field];
    if (paired && !e.result.valid) FormValidation3.revalidateField(paired);
  });

  // Cross-validation on input events for stat pairs
  for (let i = 1; i <= 4; i++) {
    document.getElementById(`value${i}`)?.addEventListener('input', () => {
      if (document.getElementById(`label${i}`).value.trim()) FormValidation3.revalidateField(`label${i}`);
    });
    document.getElementById(`label${i}`)?.addEventListener('input', () => {
      if (document.getElementById(`value${i}`).value.trim()) FormValidation3.revalidateField(`value${i}`);
    });
  }

  // ─── FormValidation – Step 4: Portfolio ──────────────────────────────────────

  function buildStep4Validation() {
    if (FormValidation4) {
      try { FormValidation4.destroy(); } catch (_) { /* suppressed */ }
      FormValidation4 = null;
    }

    const fields = {};
    const $cards  = $('.work-item-card');

    $cards.each(function (index) {
      const $card = $(this);
      const names = {
        id:    `portfolio[${index}][id]`,
        del:   `portfolio[${index}][delete]`,
        media: `portfolio[${index}][media]`,
        title: `portfolio[${index}][title]`,
        desc:  `portfolio[${index}][description]`,
      };

      // Apply names so backend receives correct array keys
      $card.find('.item-id').attr('name',     names.id);
      $card.find('.delete-flag').attr('name', names.del);
      $card.find('.media-input').attr('name', names.media);
      $card.find('.title-input').attr('name', names.title);
      $card.find('.desc-input').attr('name',  names.desc);

      // Hidden/deleted cards still need correct names but must not block submit
      if ($card.find('.delete-flag').val() === 'true' || !$card.is(':visible')) return;

      fields[names.media] = {
        validators: {
          callback: {
            message: t.complete_work_item_details,
            callback: (input) => {
              const $item    = $(input.element).closest('.work-item-card');
              const hasFile  = input.element.files && input.element.files.length > 0;
              const hasExist = $item.find('.upload-area img, .upload-area video').length > 0;
              const title    = $item.find('.title-input').val().trim();
              const desc     = $item.find('.desc-input').val().trim();
              return (title && desc) ? (hasFile || hasExist) : true;
            }
          },
          file: {
            extension: 'png,jpg,jpeg,webp,mp4',
            maxSize:   10485760,
            message:   t.unsupported_file_type_portfolio,
          }
        }
      };

      fields[names.title] = {
        validators: {
          stringLength: { max: 50, message: t.title_max },
          callback: {
            message: t.complete_work_item_details,
            callback: (input) => {
              const $item    = $(input.element).closest('.work-item-card');
              const hasFile  = $item.find('.media-input')[0].files.length > 0;
              const hasExist = $item.find('.upload-area img, .upload-area video').length > 0;
              const desc     = $item.find('.desc-input').val().trim();
              return (desc || hasFile || hasExist) ? input.value.trim() !== '' : true;
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
              const $item    = $(input.element).closest('.work-item-card');
              const hasFile  = $item.find('.media-input')[0].files.length > 0;
              const hasExist = $item.find('.upload-area img, .upload-area video').length > 0;
              const title    = $item.find('.title-input').val().trim();
              return (title || hasFile || hasExist) ? input.value.trim() !== '' : true;
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
        autoFocus:    new FormValidation.plugins.AutoFocus(),
        submitButton: new FormValidation.plugins.SubmitButton(),
      }
    }).on('core.form.valid', () => saveStepData(3, FormValidation4));
  }

  // Initialise portfolio validation and expose rebuild for external use
  // setTimeout(buildStep4Validation, 100);
  window.rebuildPortfolioValidation = buildStep4Validation;


  // ─── Stepper UI: Next/Prev button labels ─────────────────────────────────────

  wizardEl.addEventListener('shown.bs-stepper', (event) => {
    const $nextBtn  = $('.btn-next');
    const $text     = $nextBtn.find('span');
    const $icon     = $nextBtn.find('i');
    const stepIndex = event.detail.indexStep;
    const isFirst   = stepIndex === 0;
    const isLast    = stepIndex === 4;
    const newText   = isLast ? t.submit : t.save_and_next;

    $('.btn-prev').css('visibility', isFirst ? 'hidden' : 'visible');

    if ($text.text().trim() !== newText) $text.text(newText);

    $nextBtn.toggleClass('btn-success', isLast);
    $nextBtn.toggleClass('btn-primary', !isLast);
    $icon.toggle(!isLast);

    updateStepButtons();
    syncStepUnlockState(); // re-evaluate on every step change
  });

  // ─── Next button click → validate current step ───────────────────────────────

  wizardEl.querySelector('.btn-next').addEventListener('click', () => {
    switch (validationStepper._currentIndex) {
      case 0: FormValidation1.validate(); break;
      case 1: FormValidation2.validate(); break;
      case 2: FormValidation3.validate(); break;
      case 3: FormValidation4 ? FormValidation4.validate() : validationStepper.next(); break;
      case 4: submitFormData(); break;
    }
  });

  // ─── Prev button click ───────────────────────────────────────────────────────

  wizardEl.querySelector('.btn-prev').addEventListener('click', () => {
    validationStepper.previous();
    // Refresh snapshots so change-detection works correctly after going back
    step1InitialData = getStep1Data();
    step2InitialData = getStep2Data();
    step3InitialData = getStep3Data();
  });

  // ─── AJAX save per step ──────────────────────────────────────────────────────

  function saveStepData(stepIndex, formValidationInstance) {
    const route = wizardForm.getAttribute(`data-route-step-${stepIndex + 1}`);
    if (!route) { validationStepper.next(); return; }

    const formData  = new FormData(wizardForm);
    const nextBtn   = document.querySelector('.btn-next');
    const $span     = $(nextBtn).find('span');
    const $icon     = $(nextBtn).find('i');
    const origText  = $span.text();

    // Loading state
    nextBtn.disabled = true;
    $span.text(t.saving);
    $icon.hide();

    const spinner = document.createElement('span');
    spinner.className = 'spinner-border spinner-border-sm me-2';
    spinner.setAttribute('role', 'status');
    nextBtn.prepend(spinner);

    $.ajax({
      url:         route,
      type:        'POST',
      data:        formData,
      processData: false,
      contentType: false,
      headers:     { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },

      success (response) {
        spinner.remove();
        nextBtn.disabled = false;
        $span.text(origText);
        $icon.show();

        if (response.success) {
          completedSteps.add(stepIndex);
          if (stepIndex + 1 < 5) completedSteps.add(stepIndex + 1);

          // Saving step 2+ immediately unlocks 3, 4, 5
          if (stepIndex >= 1) {
            completedSteps.add(2); completedSteps.add(3); completedSteps.add(4);
          }

          updateStepButtons();
          validationStepper.next();
        } else {
          releaseFailureAlert({
            title:   lang === 'ar' ? 'خطأ!' : 'Error!',
            message: response.message || (lang === 'ar' ? 'حدث خطأ' : 'An error occurred'),
          });
        }
      },

      error (xhr) {
        spinner.remove();
        nextBtn.disabled = false;
        $span.text(origText);
        $icon.show();

        let errorMessage = lang === 'ar' ? 'حدث خطأ أثناء الحفظ' : 'An error occurred while saving';
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
          errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
        }

        releaseFailureAlert({
          title:   lang === 'ar' ? 'خطأ!' : 'Error!',
          message: errorMessage,
        });
      }
    });
  }

  // ─── Final submit (Step 5) ───────────────────────────────────────────────────

  function submitFormData() {
    releaseSuccessAlert({
      title:   lang === 'ar' ? 'تم!'                      : 'Done!',
      message: lang === 'ar' ? 'تم حفظ الملف الشخصي بنجاح' : 'Profile saved successfully',
    });

    const spId = wizardForm.querySelector('input[name="service_provider_id"]');
    if (spId) {
      setTimeout(() => { window.location.href = '/dashboard/services_providers'; }, 1500);
    }
  }

  // ─── DOMContentLoaded: snapshots + completed-step detection ──────────────────

  document.addEventListener('DOMContentLoaded', () => {
    step1InitialData = getStep1Data();
    step2InitialData = getStep2Data();
    step3InitialData = getStep3Data();

    loadCompletedSteps();
    updateStepButtons();
    attachRealTimeListeners();
  });

})();