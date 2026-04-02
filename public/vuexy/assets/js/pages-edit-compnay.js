/* Edit Company Validations */
'use strict';

let editInfoFV = null;
let editDescFV = null;
let mvpFV = null;

const $select2 = $('.select2');

/* ─── Translation Strings ──────────────────────────────────────────────────── */
const translations = {
  en: {
    required_field: 'This field is required.',
    save: 'Save',
    saving: 'Saving...',
    name_en: {
      notEmpty: 'This field is required.',
      stringOnly: 'Please enter a valid company name'
    },
    name_ar: {
      notEmpty: 'This field is required.',
      stringOnly: 'Please enter a valid company name'
    },
    address_en: {
      notEmpty: 'This field is required.',
      address: 'Please enter a valid address using letters, numbers and dashes only.'
    },
    address_ar: {
      notEmpty: 'This field is required.',
      address: 'Please enter a valid address using letters, numbers and dashes only.'
    },
    est_date: {
      establishmentDate: 'Establishment date cannot be a future date'
    },
    product_or_service: {
      notEmpty: 'This field is required.'
    },
    years_of_operations: {
      notEmpty: 'This field is required.'
    },
    sector: {
      notEmpty: 'This field is required.'
    },
    'business_categories[]': {
      notEmpty: 'Please select at least one business category'
    },
    country_id: {
      notEmpty: 'This field is required.'
    },
    state_id: {
      notEmpty: 'This field is required.'
    },
    other_category: {
      notEmpty: "Please specify your business type in the 'Other' field.",
      stringOnly: 'Business type accepts Arabic and English letters only'
    },
    Commercial_Register: {
      format: 'Invalid file format. Only PDF, PNG, or JPEG is allowed.',
      size: 'File size exceeds 10 MB. Please upload a smaller file.',
      required: 'Please upload the Commercial Register.'
    },
    Tax_Card: {
      format: 'Invalid file format. Only PDF, PNG, or JPEG is allowed.',
      size: 'File size exceeds 10 MB. Please upload a smaller file.',
      required: 'Please upload the Tax Card.'
    },
    Country_Register: {
      notEmpty: 'This field is required.'
    },
    company_registration_type: {
      notEmpty: 'This field is required.'
    },
    previous_incubation: {
      notEmpty: 'This field is required.'
    },
    previous_incubator: {
      notEmpty: 'This field is required.',
      maxLength: 'Previous Incubator must be at most 150 characters.'
    },
    /* MVP tab */
    business_model: {
      notEmpty: 'Business model is required'
    },
    other_business_model: {
      notEmpty: 'Other business model is required'
    },
    business_model_description: {
      notEmpty: 'Description is required',
      min: 'Minimum 10 characters required'
    },
    mvp_prototype_link: {
      notEmpty: 'MVP prototype link is required',
      invalid_url: 'Please enter a valid URL'
    },
    business_plan: {
      notEmpty: 'Business plan attachment is required',
      file_invalid: 'Invalid file. Only PDF/DOC/DOCX up to 5MB allowed',
      file_required: 'Business plan is required'
    },
    /* Toast */
    saveSuccess: 'Startup profile updated successfully.',
    processing: 'Processing...'
  },
  ar: {
    save: 'حفظ',
    required_field: "هذا الحقل مطلوب.",
    saving: 'جاري الحفظ...',
    name_en: {
      notEmpty: 'هذا الحقل مطلوب.',
      stringOnly: 'الرجاء إدخال اسم شركة صالح'
    },
    name_ar: {
      notEmpty: 'هذا الحقل مطلوب.',
      stringOnly: 'الرجاء إدخال اسم شركة صالح'
    },
    address_en: {
      notEmpty: 'هذا الحقل مطلوب.',
      address: 'الرجاء إدخال عنوان صالح باستخدام الحروف والأرقام والشرطات فقط.'
    },
    address_ar: {
      notEmpty: 'هذا الحقل مطلوب.',
      address: 'الرجاء إدخال عنوان صالح باستخدام الحروف والأرقام والشرطات فقط.'
    },
    est_date: {
      establishmentDate: 'لا يمكن أن يكون تاريخ التأسيس تاريخًا مستقبليًا'
    },
    product_or_service: {
      notEmpty: 'هذا الحقل مطلوب.'
    },
    years_of_operations: {
      notEmpty: 'هذا الحقل مطلوب.'
    },
    sector: {
      notEmpty: 'هذا الحقل مطلوب.'
    },
    'business_categories[]': {
      notEmpty: 'الرجاء اختيار فئة عمل واحدة على الأقل'
    },
    country_id: {
      notEmpty: 'هذا الحقل مطلوب.'
    },
    state_id: {
      notEmpty: 'هذا الحقل مطلوب.'
    },
    other_category: {
      notEmpty: 'الرجاء تحديد نوع عملك في الحقل "آخر".',
      stringOnly: 'نوع العمل يقبل الحروف العربية والإنجليزية فقط'
    },
    Commercial_Register: {
      format: 'تنسيق الملف غير صالح. يُسمح فقط بملفات PDF أو PNG أو JPEG.',
      size: 'يتجاوز حجم الملف 10 ميجابايت. يرجى تحميل ملف أصغر.',
      required: 'يرجى رفع السجل التجاري'
    },
    Tax_Card: {
      format: 'تنسيق الملف غير صالح. يُسمح فقط بملفات PDF أو PNG أو JPEG.',
      size: 'يتجاوز حجم الملف 10 ميجابايت. يرجى تحميل ملف أصغر.',
      required: 'يرجى رفع البطاقة الضريبية'
    },
    Country_Register: {
      notEmpty: 'هذا الحقل مطلوب.'
    },
    company_registration_type: {
      notEmpty: 'هذا الحقل مطلوب.'
    },
    previous_incubation: {
      notEmpty: 'هذا الحقل مطلوب.'
    },
    previous_incubator: {
      notEmpty: 'هذا الحقل مطلوب.',
      maxLength: 'يجب ألا يتجاوز حقل الحاضنة السابقة 150 حرفًا.'
    },
    /* MVP tab */
    business_model: {
      notEmpty: 'نموذج العمل مطلوب'
    },
    other_business_model: {
      notEmpty: 'يرجى إدخال نموذج العمل الآخر'
    },
    business_model_description: {
      notEmpty: 'الوصف مطلوب',
      min: 'يجب ألا يقل عن 10 أحرف'
    },
    mvp_prototype_link: {
      notEmpty: 'رابط النموذج الأولي مطلوب',
      invalid_url: 'يرجى إدخال رابط صحيح'
    },
    business_plan: {
      notEmpty: 'يرجى رفع ملف خطة العمل',
      file_invalid: 'ملف غير صالح. فقط PDF أو DOC أو DOCX بحجم أقصى 5MB',
      file_required: 'ملف خطة العمل مطلوب'
    },
    /* Toast */
    saveSuccess: 'تم تحديث ملف الشركة الناشئة بنجاح.',
    processing: 'جاري المعالجة...'
  }
};

/* ─── Description-tab translations ─────────────────────────────────────────── */
const descTranslations = {
  en: {
    company_description: { stringOnly: 'Company description accepts letters and symbols only' },
    about_problem: { stringOnly: 'About the problem accepts letters only' },
    about_market_opportunity: { stringOnly: 'About the market opportunity accepts letters only' },
    about_competitors: { stringOnly: 'About the competitors accepts letters only' },
    about_monetize: { stringOnly: 'About monetize accepts letters only' },
    about_challenges: { stringOnly: 'About challenges accepts letters only' }
  },
  ar: {
    company_description: { stringOnly: 'وصف الشركة يقبل الحروف والرموز فقط' },
    about_problem: { stringOnly: 'حول المشكلة يقبل الحروف فقط' },
    about_market_opportunity: { stringOnly: 'حول فرص السوق يقبل الحروف فقط' },
    about_competitors: { stringOnly: 'حول المنافسين يقبل الحروف فقط' },
    about_monetize: { stringOnly: 'حول التحويل يقبل الحروف فقط' },
    about_challenges: { stringOnly: 'حول التحديات يقبل الحروف فقط' }
  }
};

/* ─── File validator helper ─────────────────────────────────────────────────── */
function validateFileField(input, fieldName, lang) {
  const createdTax = document.getElementById('CreatedTax');
  if (!createdTax || createdTax.value !== '1') return { valid: true };

  const fileInput = input.element;
  const hiddenInput = document.querySelector(`input[name="${fieldName}_value"]`);
  const hasNewFile = fileInput.files && fileInput.files.length > 0;
  const hasExisting = hiddenInput && hiddenInput.value;

  if (!hasNewFile && !hasExisting) {
    return { valid: false, message: translations[lang][fieldName].required };
  }

  if (hasNewFile) {
    const file = fileInput.files[0];
    const allowedExts = ['pdf', 'png', 'jpeg', 'jpg'];
    const ext = file.name.split('.').pop().toLowerCase();

    if (!allowedExts.includes(ext)) {
      return { valid: false, message: translations[lang][fieldName].format };
    }
    if (file.size > 10 * 1024 * 1024) {
      return { valid: false, message: translations[lang][fieldName].size };
    }
  }

  return { valid: true };
}

/* ─── Main ──────────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {

  const editInfoForm = document.getElementById('edit-info-form');
  const editDescForm = document.getElementById('desc-form');
  const mvpForm = document.getElementById('mvp-form');

  const establishmentDate = document.querySelectorAll('#est-date');
  const businessCategories = document.querySelectorAll('input[name="business_categories[]"]');
  const createdTaxSelect = document.getElementById('CreatedTax');
  const prevIncubationSelect = document.getElementById('previous_incubation');
  const prevIncubatorWrapper = document.getElementById('previousIncubatorWrapper');
  const prevIncubatorInput = document.getElementById('previous_incubator');
  const descTextarea = document.getElementById('products_services_description');
  const descCounter = document.getElementById('products_services_description_count');

  /* ── Character counter for description ── */
  if (descTextarea && descCounter) {
    descTextarea.addEventListener('input', function () {
      descCounter.textContent = `${this.value.length}/1000`;
    });
  }

  /* ─────────────────────────── Info-form validation config ─────────────────── */
  const validationConfig = {
    fields: {
      name_en: {
        validators: {
          notEmpty: { message: translations[lang].name_en.notEmpty },
          regexp: {
            regexp: /^[\p{L}\d\s.,;&@:-]+$/u,
            message: translations[lang].name_en.stringOnly
          }
        }
      },
      name_ar: {
        validators: {
          notEmpty: { message: translations[lang].name_ar.notEmpty },
          regexp: {
            regexp: /^[\p{L}\d\s.,;&@:-]+$/u,
            message: translations[lang].name_ar.stringOnly
          }
        }
      },
      address_en: {
        validators: {
          notEmpty: { message: translations[lang].address_en.notEmpty },
          regexp: {
            regexp: /^(?!\s)[\p{L}\p{N}\s,;:-]*$/u,
            message: translations[lang].address_en.address
          }
        }
      },
      address_ar: {
        validators: {
          notEmpty: { message: translations[lang].address_ar.notEmpty },
          regexp: {
            regexp: /^(?!\s)[\p{L}\p{N}\s,;:-]*$/u,
            message: translations[lang].address_ar.address
          }
        }
      },
      est_date: {
        validators: {
          callback: {
            message: translations[lang].est_date.establishmentDate,
            callback: function (input) {
              if (!input.value) return true;
              const date = new Date(input.value);
              const today = new Date();
              return date <= today;
            }
          }
        }
      },
      product_or_service: {
        validators: {
          notEmpty: { message: translations[lang].product_or_service.notEmpty }
        }
      },
      years_of_operations: {
        validators: {
          notEmpty: { message: translations[lang].years_of_operations.notEmpty }
        }
      },
      country_id: {
        validators: {
          notEmpty: { message: translations[lang].country_id.notEmpty }
        }
      },
      state_id: {
        validators: {
          notEmpty: { message: translations[lang].state_id.notEmpty }
        }
      },
      'business_categories[]': {
        validators: {
          notEmpty: { message: translations[lang]['business_categories[]'].notEmpty }
        }
      },
      other_category: {
        validators: {
          notEmpty: { message: translations[lang].other_category.notEmpty },
          regexp: {
            regexp: /^[a-zA-Z\u0621-\u064A\s]*$/i,
            message: translations[lang].other_category.stringOnly
          }
        }
      },
      Commercial_Register: {
        validators: {
          callback: {
            callback: function (input) {
              return validateFileField(input, 'Commercial_Register', lang);
            }
          }
        }
      },
      Tax_Card: {
        validators: {
          callback: {
            callback: function (input) {
              return validateFileField(input, 'Tax_Card', lang);
            }
          }
        }
      },
      Country_Register: {
        validators: {
          callback: {
            callback: function (input) {
              const createdTax = document.getElementById('CreatedTax');
              if (createdTax && createdTax.value === '1') {
                if (!input.value || input.value === '') {
                  return { valid: false, message: translations[lang].Country_Register.notEmpty };
                }
              }
              return { valid: true };
            }
          }
        }
      },
      company_registration_type: {
        validators: {
          notEmpty: { message: translations[lang].company_registration_type.notEmpty }
        }
      },
      previous_incubation: {
        validators: {
          notEmpty: { message: translations[lang].previous_incubation.notEmpty }
        }
      },
      previous_incubator: {
        validators: {
          callback: {
            callback: function (input) {
              const isYes = prevIncubationSelect && prevIncubationSelect.value === '1';
              if (!isYes) return { valid: true };

              const val = (input.value || '').trim();
              if (!val) {
                return { valid: false, message: translations[lang].previous_incubator.notEmpty };
              }
              if (val.length > 150) {
                return { valid: false, message: translations[lang].previous_incubator.maxLength };
              }
              return { valid: true };
            }
          }
        }
      },
      products_services_description: {
        validators: {
          callback: {
            callback: function (input) {
              const val = (input.value || '').trim();
              if (val.length > 1000) {
                return { valid: false, message: translations[lang].products_services_description.maxLength };
              }
              return { valid: true };
            }
          }
        }
      }
    },

    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: '.mb-6, .mb-3, .mb-4, .form-group'
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    },

    init: instance => {
      instance.on('plugins.message.placed', function (e) {
        if (e.element.parentElement.classList.contains('input-group')) {
          e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
        }
      });
    }
  };

  /* ─────────────────────────── Desc-form validation config ─────────────────── */
  const descValidationConfig = {
    fields: {
      "Sustainable_development[]": {
        validators: {
          notEmpty: {
            message: translations[lang].required_field
          },
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '', rowSelector: '.mb-4' }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    },
    init: instance => {
      instance.on('plugins.message.placed', function (e) {
        if (e.element.parentElement.classList.contains('input-group')) {
          e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
        }
      });
    }
  };

  /* ─────────────────────────── MVP-form validation config ──────────────────── */
  const mvpValidationConfig = {
    fields: {
      business_model: {
        validators: {
          notEmpty: {
            message: translations[lang].business_model.notEmpty
          }
        }
      },

      other_business_model: {
        validators: {
          callback: {
            message: translations[lang].other_business_model.notEmpty,
            callback: function (input) {
              const businessModelEl = document.getElementById('business_model');
              if (businessModelEl && businessModelEl.value === 'Other') {
                return input.value.trim() !== '';
              }
              return true;
            }
          }
        }
      },

      business_model_description: {
        validators: {
          notEmpty: {
            message: translations[lang].business_model_description.notEmpty
          },
          stringLength: {
            min: 10,
            message: translations[lang].business_model_description.min
          }
        }
      },

      mvp_prototype_link: {
        validators: {
          uri: {
            message: translations[lang].mvp_prototype_link.invalid_url
          }
        }
      },

      business_plan: {
        validators: {
          file: {
            extension: 'pdf,doc,docx',
            type: 'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            maxSize: 5 * 1024 * 1024,
            message: translations[lang].business_plan.file_invalid
          },
          callback: {
            message: translations[lang].business_plan.file_required,
            callback: function (input) {
              const hasExistingFile = document.querySelector('#business_plan_card') !== null;
              if (hasExistingFile) return true;
              return input.files.length > 0;
            }
          }
        }
      }
    },

    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        rowSelector: '.mb-4',
        eleInvalidClass: '',
        eleValidClass: ''
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  };

  /* ─────────────────────────── Info-form setup ─────────────────────────────── */
  if (editInfoForm) {

    editInfoForm.addEventListener('submit', function (e) {
      // FormValidation's DefaultSubmit plugin is NOT used here; we do it manually.
    });

    editInfoFV = FormValidation.formValidation(editInfoForm, {
      ...validationConfig,
      plugins: {
        ...validationConfig.plugins,
        submitButton: new FormValidation.plugins.SubmitButton()
      }
    });

    editInfoFV.on('core.form.valid', function () {
      const submitBtn = document.getElementById('submit-button');
      const btnText = document.getElementById('button-text');
      const btnSpinner = document.getElementById('button-spinner');

      if (submitBtn) submitBtn.disabled = true;
      if (btnText) btnText.classList.add('d-none');
      if (btnSpinner) btnSpinner.classList.remove('d-none');

      const formData = new FormData(editInfoForm);

      fetch(editInfoForm.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      })
        .then(res => res.ok ? res.json().catch(() => ({})) : Promise.reject(res))
        .then(() => {
          toastr.success(translations[lang].saveSuccess);
        })
        .catch(() => {
          editInfoForm.submit();
        })
        .finally(() => {
          if (submitBtn) submitBtn.disabled = false;
          if (btnText) btnText.classList.remove('d-none');
          if (btnText) btnText.textContent = translations[lang].save || 'Save';
          if (btnSpinner) btnSpinner.classList.add('d-none');
        });
    });

    /* ── Select2 for Country_Register ── */
    const countryRegisterSelect = document.getElementById('Country_Register');
    if (countryRegisterSelect && !$(countryRegisterSelect).hasClass('select2-hidden-accessible')) {
      $(countryRegisterSelect).select2({
        placeholder: countryRegisterSelect.getAttribute('data-placeholder') || 'Select Country',
        allowClear: true
      });
    }

    if (countryRegisterSelect) {
      $(countryRegisterSelect).on('select2:select select2:unselect select2:clear change select2:close', function () {
        setTimeout(() => editInfoFV.revalidateField('Country_Register'), 50);
      });
    }

    /* ── Other category visibility ── */
    const checkOtherCategoryVisibility = () => {
      const isOtherSelected = Array.from(businessCategories).some(
        cat => (cat.value === 'Other' || cat.value === 'أخرى') && cat.checked
      );
      const otherCategoryInput = document.getElementById('otherCategoryInput');
      if (otherCategoryInput) otherCategoryInput.style.display = isOtherSelected ? 'block' : 'none';

      if (isOtherSelected) {
        editInfoFV.enableValidator('other_category', 'notEmpty');
        editInfoFV.enableValidator('other_category', 'regexp');
      } else {
        editInfoFV.disableValidator('other_category', 'notEmpty');
        editInfoFV.disableValidator('other_category', 'regexp');
      }
    };

    checkOtherCategoryVisibility();

    businessCategories.forEach(category => {
      category.addEventListener('change', function () {
        const isOtherSelected = Array.from(businessCategories).some(
          cat => (cat.value === 'Other' || cat.value === 'أخرى') && cat.checked
        );
        const otherCategoryInput = document.getElementById('otherCategoryInput');

        if (isOtherSelected) {
          if (otherCategoryInput) otherCategoryInput.style.display = 'block';
          editInfoFV.enableValidator('other_category', 'notEmpty');
          editInfoFV.enableValidator('other_category', 'regexp');
        } else {
          if (otherCategoryInput) otherCategoryInput.style.display = 'none';
          editInfoFV.disableValidator('other_category', 'notEmpty');
          editInfoFV.disableValidator('other_category', 'regexp');
          editInfoFV.resetField('other_category');
        }
      });
    });

    /* ── File validation enable/disable based on CreatedTax ── */
    const handleFileValidation = () => {
      const yes = createdTaxSelect && createdTaxSelect.value === '1';
      ['Commercial_Register', 'Tax_Card', 'Country_Register'].forEach(field => {
        if (yes) {
          editInfoFV.enableValidator(field, 'callback');
        } else {
          editInfoFV.disableValidator(field, 'callback');
          editInfoFV.resetField(field);
        }
      });
    };

    handleFileValidation();

    if (createdTaxSelect) {
      createdTaxSelect.addEventListener('change', function () {
        handleFileValidation();
        ['Commercial_Register', 'Tax_Card', 'Country_Register'].forEach(field => {
          editInfoFV.revalidateField(field);
        });
        setTimeout(() => editInfoFV.revalidateField('Country_Register'), 100);
      });
    }

    const commercialRegisterInput = document.getElementById('Commercial_Register');
    const taxCardInput = document.getElementById('Tax_Card');

    if (commercialRegisterInput) {
      commercialRegisterInput.addEventListener('change', () => editInfoFV.revalidateField('Commercial_Register'));
    }
    if (taxCardInput) {
      taxCardInput.addEventListener('change', () => editInfoFV.revalidateField('Tax_Card'));
    }

    /* ── Previous Incubation → show/hide Previous Incubator ── */
    const toggleIncubatorField = () => {
      if (!prevIncubationSelect || !prevIncubatorWrapper) return;
      const isYes = prevIncubationSelect.value === '1';
      prevIncubatorWrapper.style.display = isYes ? '' : 'none';

      if (!isYes) {
        if (prevIncubatorInput) prevIncubatorInput.value = '';
        editInfoFV.resetField('previous_incubator');
      }

      editInfoFV.revalidateField('previous_incubator');
    };

    toggleIncubatorField();

    if (prevIncubationSelect) {
      prevIncubationSelect.addEventListener('change', function () {
        toggleIncubatorField();
        editInfoFV.revalidateField('previous_incubation');
      });
    }

    if (prevIncubatorInput) {
      prevIncubatorInput.addEventListener('input', () => editInfoFV.revalidateField('previous_incubator'));
    }

    if (descTextarea) {
      descTextarea.addEventListener('input', () => editInfoFV.revalidateField('products_services_description'));
    }
  }

  /* ─────────────────────────── Desc-form setup ─────────────────────────────── */
  if (editDescForm) {

    editDescForm.addEventListener('submit', function (e) {
      // FormValidation's DefaultSubmit plugin is NOT used here; we do it manually.
    });

    editDescFV = FormValidation.formValidation(editDescForm, descValidationConfig);

    editDescFV.on('core.form.valid', function () {
      const submitBtn = document.getElementById('submit-button-desc');
      const btnText = document.getElementById('button-text-desc');
      const btnSpinner = document.getElementById('button-spinner-desc');

      if (submitBtn) submitBtn.disabled = true;
      if (btnText) btnText.textContent = translations[lang].saving || 'Saving...';
      if (btnSpinner) btnSpinner.classList.remove('d-none');

      const formData = new FormData(editDescForm);

      fetch(editDescForm.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      })
        .then(res => res.ok ? res.json().catch(() => ({})) : Promise.reject(res))
        .then(() => {
          toastr.success(translations[lang].saveSuccess || 'Description updated successfully.');
        })
        .catch(() => {
          editDescForm.submit();
        })
        .finally(() => {
          if (submitBtn) submitBtn.disabled = false;
          if (btnText) btnText.classList.remove('d-none');
          if (btnText) btnText.textContent = translations[lang].save || 'Save';
          if (btnSpinner) btnSpinner.classList.add('d-none');
        });
    });

    const sustainableSelect = document.getElementById('sustainable-mutiple-select');
    if (sustainableSelect) {
      $(sustainableSelect).on('select2:select select2:unselect', function () {
        setTimeout(() => {
          if (editDescFV) editDescFV.revalidateField('Sustainable_development[]');
        }, 100);
      });
    }
  }

  /* ─────────────────────────── MVP-form setup ──────────────────────────────── */
  if (mvpForm) {

    mvpForm.addEventListener('submit', function (e) {
      // FormValidation's DefaultSubmit plugin is NOT used here; we do it manually.
    });

    mvpFV = FormValidation.formValidation(mvpForm, mvpValidationConfig);

    mvpFV.on('core.form.valid', function () {
      const submitBtn = document.getElementById('submit-button-mvp');
      const btnText = document.getElementById('button-text-mvp');
      const btnSpinner = document.getElementById('button-spinner-mvp');

      if (submitBtn) submitBtn.disabled = true;
      if (btnText) btnText.textContent = translations[lang].saving || 'Saving...';
      if (btnSpinner) btnSpinner.classList.remove('d-none');

      const formData = new FormData(mvpForm);

      fetch(mvpForm.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      })
        .then(res => res.ok ? res.json().catch(() => ({})) : Promise.reject(res))
        .then(() => {
          toastr.success(translations[lang].saveSuccess);
        })
        .catch(() => {
          mvpForm.submit();
        })
        .finally(() => {
          if (submitBtn) submitBtn.disabled = false;
          if (btnText) btnText.classList.remove('d-none');
          if (btnText) btnText.textContent = translations[lang].save || 'Save';
          if (btnSpinner) btnSpinner.classList.add('d-none');
        });
    });

    /* ── Toggle "Other" business model field ── */
    const businessModelEl = document.getElementById('business_model');
    const otherWrapper = mvpForm.querySelector('.IfOther');

    const toggleOtherField = () => {
      if (!businessModelEl || !otherWrapper) return;
      if (businessModelEl.value === 'Other') {
        otherWrapper.style.display = 'block';
      } else {
        otherWrapper.style.display = 'none';
        mvpFV.revalidateField('other_business_model');
      }
    };

    if (businessModelEl) {
      businessModelEl.addEventListener('change', toggleOtherField);
    }

    // Init visibility on load
    toggleOtherField();
  }

  /* ─────────────────────────── Shared Select2 revalidation ─────────────────── */
  $select2.each(function () {
    const $el = $(this);
    $el.select2().on('change', function () {
      if (editInfoFV) editInfoFV.revalidateField($el.attr('name'));
      if (editDescFV) editDescFV.revalidateField($el.attr('name'));
      if (mvpFV) mvpFV.revalidateField($el.attr('name'));
    });
  });

  /* ─────────────────────────── Flatpickr for est_date ─────────────────────── */
  if (establishmentDate && establishmentDate.length) {
    establishmentDate.forEach(el => {
      el.flatpickr({
        monthSelectorType: 'static',
        maxDate: 'today'
      });
    });
  }
});