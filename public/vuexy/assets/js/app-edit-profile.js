'use strict';

(function ($) {
  const user = CONFIG.user || {};

  // Cache DOM elements
  const $select2 = $('.select2');
  const $dobPicker = $('.dob-picker');

  // ─── National ID rules ─────────────────────────────────────────────────────
  const nationalIdRules = {
    eg: { pattern: /^\d{14}$/ },
    sa: { pattern: /^\d{10}$/ },
    ae: { pattern: /^\d{15}$/ },
    kw: { pattern: /^\d{12}$/ },
    qa: { pattern: /^\d{11}$/ },
    bh: { pattern: /^\d{9}$/ },
    om: { pattern: /^\d{8}$/ },
    jo: { pattern: /^\d{10}$/ },
    lb: { pattern: /^\d{12}$/ },
    iq: { pattern: /^\d{12}$/ },
    sy: { pattern: /^\d{11}$/ },
    ps: { pattern: /^\d{9}$/ },
    ly: { pattern: /^\d{12}$/ },
    tn: { pattern: /^\d{8}$/ },
    dz: { pattern: /^\d{18}$/ },
    ma: { pattern: /^[A-Z]{1,2}\d{6}$/ },
    sd: { pattern: /^\d{9}$/ },
    tr: { pattern: /^\d{11}$/ },
    us: { pattern: /^\d{9}$/ },
    gb: { pattern: /^[A-Z]{2}\d{6}[A-D]$/i },
    de: { pattern: /^\d{11}$/ },
    fr: { pattern: /^\d{13}$/ },
    in: { pattern: /^\d{12}$/ },
    pk: { pattern: /^\d{13}$/ },
    bd: { pattern: /^\d{10,17}$/ },
    ng: { pattern: /^[A-Z]{2}\d{7}[A-Z]{1}$/i },
    za: { pattern: /^\d{13}$/ },
  };

  // ─── Translations ───────────────────────────────────────────────────────────
  const profileTranslations = {
    en: {
      first_name_en: { notEmpty: 'Please enter first name', stringOnly: 'This field accepts letters only.', stringLength: 'First name should be between 2 and 50 characters' },
      last_name_en: { notEmpty: 'Please enter last name', stringOnly: 'This field accepts letters only.', stringLength: 'Last name should be between 2 and 50 characters' },
      phone: { notEmpty: 'Please enter your phone', invalidPhone: 'Please enter a valid phone number' },
      email: { notEmpty: 'Please enter your email', emailAddress: 'Please enter valid email address' },
      title_en: { notEmpty: 'Please enter job title', stringOnly: 'Job title accepts letters only' },
      zip_code: { zipCode: 'Please enter valid zip code' },
      academic_stat: { notEmpty: 'Please select your education status' },
      dob: { notEmpty: 'Please enter your date of birth', invalid: 'Please enter a valid date of birth' },
      national_id: { notEmpty: 'Please enter your National ID', invalidFormat: 'Invalid National ID format for the selected country', noCountry: 'Please select country first', noRule: 'National ID validation is not available for this country' },
      country_id: { notEmpty: 'Please select your country' },
      state_id: { notEmpty: 'Please select your state' },
      address: { notEmpty: 'Please enter your address', stringLength: 'Address should be between 2 and 100 characters' },
      timezone: { notEmpty: 'Please select your timezone' },
      Please_enter_otp: 'Please enter OTP',
      Please_enter_valid_whatsapp_number: 'Please enter a valid WhatsApp number',
    },
    ar: {
      first_name_en: { notEmpty: 'يرجى إدخال الاسم الأول', stringOnly: 'هذا الحقل يقبل الحروف فقط', stringLength: 'يجب أن يكون الاسم الأول بين 2 و 50 حرفًا' },
      last_name_en: { notEmpty: 'يرجى إدخال الاسم الأخير', stringOnly: 'هذا الحقل يقبل الحروف فقط', stringLength: 'يجب أن يكون الاسم الأخير بين 2 و 50 حرفًا' },
      phone: { notEmpty: 'يرجى إدخال رقم الهاتف', invalidPhone: 'يرجى إدخال رقم هاتف صحيح' },
      email: { notEmpty: 'يرجى إدخال بريدك الإلكتروني', emailAddress: 'يرجى إدخال عنوان بريد إلكتروني صالح' },
      title_en: { notEmpty: 'يرجى ادخال المسمى الوظيفي', stringOnly: 'هذا الحقل يقبل الحروف فقط' },
      zip_code: { zipCode: 'يرجى إدخال الرمز البريدي الصحيح' },
      academic_stat: { notEmpty: 'يرجى اختيار الحالة الدراسية' },
      dob: { notEmpty: 'يرجى إدخال تاريخ الميلاد', invalid: 'يرجى إدخال تاريخ ميلاد صحيح' },
      national_id: { notEmpty: 'يرجى إدخال الرقم القومي', invalidFormat: 'صيغة رقم الهوية غير صحيحة', noCountry: 'يرجى اختيار الدولة أولاً', noRule: 'التحقق من رقم الهوية غير متاح لهذه الدولة' },
      country_id: { notEmpty: 'يرجى اختيار الدولة' },
      state_id: { notEmpty: 'يرجى اختيار المنطقة' },
      address: { notEmpty: 'يرجى إدخال العنوان', stringLength: 'يجب أن يكون العنوان بين 2 و 100 حرفًا' },
      timezone: { notEmpty: 'يرجى اختيار المنطقة الزمنية' },
      Please_enter_otp: 'يرجى إدخال OTP',
      Please_enter_valid_whatsapp_number: 'يرجى إدخال رقم واتساب صحيح',
    }
  };

  function getLang() {
    return window.lang || 'en';
  }

  function t(key, subKey) {
    const lang = getLang();
    const trans = profileTranslations[lang] || profileTranslations.en;
    return trans[key]?.[subKey] || profileTranslations.en[key]?.[subKey] || key;
  }

  function getSelectedCountryIso2() {
    const countryId = $('#country_id').val();
    if (CONFIG?.countriesIso2Map && countryId) {
      const iso = String(CONFIG.countriesIso2Map[countryId] || '').toLowerCase().trim();
      if (iso) return iso;
    }
    return 'eg';
  }

  function buildNationalIdValidator() {
    return {
      callback: function (input) {
        const value = (input.value || '').trim();
        if (value.length === 0) return input.element.required ? { valid: false, message: t('national_id', 'notEmpty') } : { valid: true };
        const iso2 = getSelectedCountryIso2();
        const rule = nationalIdRules[iso2];
        if (!rule) return { valid: false, message: t('national_id', 'noRule') };
        return rule.pattern.test(value) ? { valid: true } : { valid: false, message: t('national_id', 'invalidFormat') };
      }
    };
  }

  function buildPhoneValidator(required = true) {
    return {
      callback: function (input) {
        const value = (input.value || '').trim();
        const errMsg = t('phone', 'invalidPhone');
        if (value === '') return required ? { valid: false, message: t('phone', 'notEmpty') } : { valid: true };
        if (!/^[\d\s\-\(\)]+$/.test(value)) return { valid: false, message: errMsg };

        const iti = $(input.element).data('iti');
        if (!iti) return { valid: value.length >= 8, message: errMsg };

        const country = iti.getSelectedCountryData().iso2.toLowerCase();
        if (country === 'eg') {
          const clean = value.replace(/[\s\-\(\)]/g, '');
          const valid = clean.startsWith('0')
            ? (clean.length === 11 && /^0(10|11|12|15|2)\d{8}$/.test(clean))
            : (clean.length === 10 && /^(10|11|12|15|2)\d{8}$/.test(clean));
          return { valid, message: errMsg };
        }
        return { valid: iti.isValidNumber(), message: errMsg };
      }
    };
  }

  function revalidateField(fieldName) {
    const validator = $('#account-info-form').data('formValidation');
    if (validator) validator.revalidateField(fieldName);
  }

  function isRegistered() {
    return user?.roles?.some(r => r.name?.toLowerCase() === 'registered') || false;
  }

  // ─── Form Validation ────────────────────────────────────────────────────────
  function initFormValidation() {
    const $form = $('#account-info-form');
    if (!$form.length) return;

    // The view sets `required` from User::PROFILE_REQUIRED_FIELDS, so the server rules
    // and this validator always agree on which fields are mandatory.
    const isRequired = name => !!$form[0].elements[name]?.required;
    const notEmpty = name => (isRequired(name) ? { notEmpty: { message: t(name, 'notEmpty') } } : {});

    const fields = {
      first_name_en: { validators: { ...notEmpty('first_name_en'), regexp: { regexp: /^[\p{L}\s]+$/u, message: t('first_name_en', 'stringOnly') }, stringLength: { min: 2, max: 50, message: t('first_name_en', 'stringLength') } } },
      last_name_en: { validators: { ...notEmpty('last_name_en'), regexp: { regexp: /^[\p{L}\s]+$/u, message: t('last_name_en', 'stringOnly') }, stringLength: { min: 2, max: 50, message: t('last_name_en', 'stringLength') } } },
      email: { validators: { notEmpty: { message: t('email', 'notEmpty') }, regexp: { regexp: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i, message: t('email', 'emailAddress') } } },
      title_en: { validators: { ...notEmpty('title_en'), regexp: { regexp: /^[\p{L}\s]+$/u, message: t('title_en', 'stringOnly') } } },
      phone: { validators: { callback: buildPhoneValidator(isRequired('phone')) } },
      whatsapp: { validators: { callback: buildPhoneValidator(false) } },
      zip_code: { validators: { regexp: { regexp: /^$|^\d{4,10}$/, message: t('zip_code', 'zipCode') } } },
      address: { validators: { ...notEmpty('address'), stringLength: { min: 0, max: 100, message: t('address', 'stringLength') } } },
      country_id: { validators: { ...notEmpty('country_id') } },
      state_id: { validators: { ...notEmpty('state_id') } },
      timezone: { validators: { ...notEmpty('timezone') } }
    };

    if (isRegistered()) {
      fields.academic_stat = { validators: { ...notEmpty('academic_stat') } };
      fields.dob = { validators: { ...notEmpty('dob'), date: { format: 'YYYY-MM-DD', message: t('dob', 'invalid') } } };
      fields.national_id = { validators: { callback: buildNationalIdValidator() } };
    }

    const validator = FormValidation.formValidation($form[0], {
      fields,
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '', rowSelector: '.mb-4, .mb-6' }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });

    $form.data('formValidation', validator);
  }

  // ─── Initialize intlTelInput with default country code ───────────────────────
  let phoneIti = null;
  let whatsappIti = null;
  const whatsappError = document.getElementById('whatsapp-error');

  function initPhoneInputs() {
    const commonOptions = {
      preferredCountries: ['eg', 'sa', 'ae', 'kw', 'qa'],
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.8/js/utils.js",
      separateDialCode: true,
      autoPlaceholder: "polite",
      formatOnDisplay: true
    };

    // Phone
    const $phone = $('#phone');
    if ($phone.length) {
      const defaultIso = $('#country_code').val() ? getIsoFromDialCode($('#country_code').val()) : 'eg';
      phoneIti = window.intlTelInput($phone[0], { ...commonOptions, initialCountry: defaultIso });
      $phone.data('iti', phoneIti);
    }

    // WhatsApp
    const $whatsapp = $('#whatsapp');
    if ($whatsapp.length) {
      const defaultIso = $('#country_code_w_a').val() ? getIsoFromDialCode($('#country_code_w_a').val()) : 'eg';
      whatsappIti = window.intlTelInput($whatsapp[0], { ...commonOptions, initialCountry: defaultIso });
      $whatsapp.data('iti', whatsappIti);

      $('#sendWhatsappButton').on('click', () => sendCode('whatsapp'));
    }
  }

  // Helper: Convert dial code (e.g. "20") to ISO2 (e.g. "eg")
  function getIsoFromDialCode(dialCode) {
    if (!window.intlTelInputGlobals) return 'eg';
    const countries = window.intlTelInputGlobals.getCountryData();
    const country = countries.find(c => c.dialCode === dialCode.toString());
    return country ? country.iso2 : 'eg';
  }

  // ─── WhatsApp Send & Verify ─────────────────────────────────────────────────
  function sendCode(type) {
    if (type === 'whatsapp' && whatsappIti) {
      if (!whatsappIti.isValidNumber()) {
        whatsappError.style.display = 'block';
        whatsappError.innerText = t[lang].Please_enter_valid_whatsapp_number;
        return;
      }
      whatsappError.style.display = 'none';
    }

    const modalId = type === 'phone' ? '#verifyCodeModal' : '#verifyWhatsappCodeModal';
    $(modalId).modal('show');

    let number = $('#whatsapp').val().trim();
    const countryCode = whatsappIti ? whatsappIti.getSelectedCountryData().dialCode : '20';

    if (number.startsWith('0') && countryCode === '20') number = number.substring(1);

    $.ajax({
      url: CONFIG.routes.sendCode,
      type: 'POST',
      data: { countryCode, phone: number, type, _token: CONFIG.csrf_token },
      success: function (res) {
        if (res.success) $('#sentMessageotp').text(res.success).show();
        else if (res.error) $('#whatsapp-error').text(res.error).show();
      }
    });
  }

  function verifyWhatsappCode() {
    let code = '';
    $('.otp-input').each(function () { code += $(this).val(); });

    if (code.length !== 6) {
      $('#otp-error').text(t[lang].Please_enter_otp).show();
      $('.otp-input').addClass('is-invalid');
      return;
    }

    $('#otp-error').hide();
    $('.otp-input').removeClass('is-invalid');

    $.ajax({
      url: CONFIG.routes.verifyCode,
      type: 'POST',
      data: { code, type: 'whatsapp', _token: CONFIG.csrf_token },
      success: function (res) {
        if (res.success) {
          $('#sentMessageotp').text(res.success).show();
          setTimeout(() => location.reload(), 1500);
        } else if (res.error) {
          $('#errorotp').text(res.error).show();
        }
      }
    });
  }

  // ─── DOM Ready ──────────────────────────────────────────────────────────────
  $(function () {
    initPhoneInputs();
    initFormValidation();

    $select2.each(function () {
      const name = $(this).attr('name');
      $(this).select2().on('change', function () {
        if (name) revalidateField(name);
        if (name === 'country_id' && isRegistered()) setTimeout(() => revalidateField('national_id'), 50);
      });
    });

    $('#national_id').on('keyup blur change input', () => revalidateField('national_id'));

    $('.intl-tel-input').on('countrychange keyup blur', function () {
      revalidateField($(this).attr('name'));
    });

    if ($dobPicker.length) {
      $dobPicker.flatpickr({
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        allowInput: true,
        maxDate: new Date().fp_incr(-3650),
        disableMobile: true,
        locale: getLang() === 'ar' ? 'ar' : 'en',
        onChange: () => revalidateField('dob')
      });
    }

    $(document).on('input', '.otp-input', function () {
      if (this.value.length === this.maxLength) $(this).next('.otp-input').focus();
    });
  });

  // Public API
  window.revalidateField = revalidateField;
  window.sendCode = sendCode;
  window.verifyWhatsappCode = verifyWhatsappCode;

})(jQuery);