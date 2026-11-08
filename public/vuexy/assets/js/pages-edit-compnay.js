/* Edit Company Validations - Optimized & Simplified jQuery */
'use strict';

let editInfoFV = null;
let editDescFV = null;
let mvpFV = null;
let financialFV = null;

const $select2 = $('.select2');

/* ==================== Translations (Simplified) ==================== */
const translations = {
  en: {
    required_field: 'This field is required.',
    save: 'Save',
    saving: 'Saving...',
    saveSuccess: 'Startup profile updated successfully.',
    financialSaveSuccess: 'Financial information updated successfully.',

    name_en: { stringOnly: 'Please enter a valid company name' },
    name_ar: { stringOnly: 'Please enter a valid company name' },
    address_en: { address: 'Please enter a valid address using letters, numbers and dashes only.' },
    address_ar: { address: 'Please enter a valid address using letters, numbers and dashes only.' },

    est_date: { establishmentDate: 'Establishment date cannot be a future date' },

    other_category: {
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

    previous_incubator: {
      maxLength: 'Previous Incubator must be at most 150 characters.'
    },

    /* Desc Tab */
    company_description: {
      max: "Description must not exceeds 500 characters.",
    },

    /* MVP Tab */
    business_model_description: {
      min: 'Minimum 10 characters required'
    },
    mvp_prototype_link: {
      invalid_url: 'Please enter a valid URL'
    },
    business_plan: {
      file_invalid: 'Invalid file. Only PDF, DOC, DOCX, PPT, PPTX up to 20MB allowed',
      file_required: 'This field is required.'
    },

    /* Financial Tab */
    average_annual_revenues: {
      number: 'Please enter a valid number.',
      min: 'Value cannot be less than 0.'
    },
    financial_plan: {
      file_invalid: 'Invalid file format. Only PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX are allowed.',
      size: 'File size must not exceed 10 MB.'
    }
  },
  ar: {
    required_field: 'هذا الحقل مطلوب.',
    save: 'حفظ',
    saving: 'جاري الحفظ...',
    saveSuccess: 'تم تحديث ملف الشركة الناشئة بنجاح.',
    financialSaveSuccess: 'تم تحديث المعلومات المالية بنجاح.',

    name_en: { stringOnly: 'الرجاء إدخال اسم شركة صالح' },
    name_ar: { stringOnly: 'الرجاء إدخال اسم شركة صالح' },
    address_en: { address: 'الرجاء إدخال عنوان صالح باستخدام الحروف والأرقام والشرطات فقط.' },
    address_ar: { address: 'الرجاء إدخال عنوان صالح باستخدام الحروف والأرقام والشرطات فقط.' },

    est_date: { establishmentDate: 'لا يمكن أن يكون تاريخ التأسيس تاريخًا مستقبليًا' },

    other_category: {
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

    previous_incubator: {
      maxLength: 'يجب ألا يتجاوز حقل الحاضنة السابقة 150 حرفًا.'
    },

    /* Desc Tab */
    company_description: {
      max: "يجب ألا يتجاوز الوصف 500 حرف.",
    },

    /* MVP Tab */
    business_model_description: {
      min: 'يجب ألا يقل عن 10 أحرف'
    },
    mvp_prototype_link: {
      invalid_url: 'يرجى إدخال رابط صحيح'
    },
    business_plan: {
      file_invalid: 'ملف غير صالح. يُسمح فقط بملفات PDF, DOC, DOCX, PPT, PPTX بحجم أقصى 20MB',
      file_required: 'هذا الحقل مطلوب.'
    },

    /* Financial Tab */
    average_annual_revenues: {
      number: 'يرجى إدخال رقم صحيح.',
      min: 'لا يمكن أن تكون القيمة أقل من 0.'
    },
    financial_plan: {
      file_invalid: 'تنسيق الملف غير صالح. يُسمح فقط بملفات PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX.',
      size: 'يجب ألا يتجاوز حجم الملف 10 ميجابايت.'
    }
  }
};

// Server labels (lang files + localization overlay) win over the built-in table.
if (translations[lang]) Object.assign(translations[lang], window.EDIT_COMPANY_LABELS || {});

/* ─── Helpers ───────────────────────────────────────────────────────────── */

function validateFileField(input, fieldName, lang) {
  if ($('#CreatedTax').val() !== '1') return { valid: true };

  const fileInput = input.element;
  const hasNewFile = fileInput.files && fileInput.files.length > 0;
  const hasExisting = $(`input[name="${fieldName}_value"]`).val();

  if (!hasNewFile && !hasExisting) {
    return { valid: false, message: translations[lang][fieldName].required };
  }

  if (hasNewFile) {
    const file = fileInput.files[0];
    const allowed = ['pdf', 'png', 'jpeg', 'jpg'];
    const ext = file.name.split('.').pop().toLowerCase();

    if (!allowed.includes(ext)) {
      return { valid: false, message: translations[lang][fieldName].format };
    }
    if (file.size > 10 * 1024 * 1024) {
      return { valid: false, message: translations[lang][fieldName].size };
    }
  }
  return { valid: true };
}

/**
 * Clears server-rendered validation artifacts left in the DOM by a previous
 * full-page (non-AJAX) submit, so they don't linger after a successful AJAX save.
 */
function clearServerValidationState(form) {
  if (form) {
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback.d-block').forEach(el => el.remove());
  }
  document.querySelectorAll('#company-tab-content > .alert-danger').forEach(el => el.remove());
}

function submitFormWithFetch({ form, submitBtnId, btnTextId, btnSpinnerId, successMessage, onSuccess }) {
  const $btn = $(`#${submitBtnId}`);
  const $text = $(`#${btnTextId}`);
  const $spinner = $(`#${btnSpinnerId}`);
  const t = translations[lang];

  $btn.prop('disabled', true);
  $text.text(t.saving);
  $spinner.removeClass('d-none');

  fetch(form.action, {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: new FormData(form)
  })
    .then(res => res.ok ? res.json().catch(() => ({})) : Promise.reject())
    .then(() => {
      toastr.success(successMessage);
      clearServerValidationState(form);
      if (typeof onSuccess === 'function') onSuccess();
      // setTimeout(() => {
      //   if (!location.href.includes('myCompany')) {
      //     return;
      //   }
      //   location.href = location.href.replace('myCompany', 'companyProfile')
      // } , 500);
    })
    .catch(() => form.submit())
    .finally(() => {
      $btn.prop('disabled', false);
      $text.text(t.save);
      $spinner.addClass('d-none');
    });
}

function buildFVPlugins(rowSelector = '.mb-6, .mb-3, .mb-4, .form-group') {
  return {
    trigger: new FormValidation.plugins.Trigger(),
    bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '', rowSelector }),
    submitButton: new FormValidation.plugins.SubmitButton(),
    autoFocus: new FormValidation.plugins.AutoFocus()
  };
}

/* ─── Main ──────────────────────────────────────────────────────────────── */

(function ($) {

  /* Character Counter */
  $('#products_services_description').on('input', function () {
    $('#products_services_description_count').text(`${this.value.length}/1000`);
  });

  /* ====================== INFO FORM ====================== */
  if ($('#edit-info-form').length) {
    editInfoFV = FormValidation.formValidation(document.getElementById('edit-info-form'), {
      fields: {
        name_en: {
          validators: {
            notEmpty: { message: translations[lang].required_field },
            regexp: { regexp: /^[\p{L}\d\s.,;&@:-]+$/u, message: translations[lang].name_en.stringOnly }
          }
        },
        name_ar: {
          validators: {
            notEmpty: { message: translations[lang].required_field },
            regexp: { regexp: /^[\p{L}\d\s.,;&@:-]+$/u, message: translations[lang].name_ar.stringOnly }
          }
        },
        address_en: {
          validators: {
            notEmpty: { message: translations[lang].required_field },
            regexp: { regexp: /^(?!\s)[\p{L}\p{N}\s,;:-]*$/u, message: translations[lang].address_en.address }
          }
        },
        address_ar: {
          validators: {
            notEmpty: { message: translations[lang].required_field },
            regexp: { regexp: /^(?!\s)[\p{L}\p{N}\s,;:-]*$/u, message: translations[lang].address_ar.address }
          }
        },
        est_date: {
          validators: {
            notEmpty: { message: translations[lang].required_field },
            callback: {
              message: translations[lang].est_date.establishmentDate,
              callback: ({ value }) => !value || new Date(value) <= new Date()
            }
          }
        },
        stage: {
          validators: {
            notEmpty: { message: $('#stage').data('required-message') || translations[lang].required_field }
          }
        },
        product_or_service: { validators: { notEmpty: { message: translations[lang].required_field } } },
        years_of_operations: { validators: { notEmpty: { message: translations[lang].required_field } } },
        country_id: { validators: { notEmpty: { message: translations[lang].required_field } } },
        state_id: { validators: { notEmpty: { message: translations[lang].required_field } } },
        'business_categories[]': {
          validators: { notEmpty: { message: translations[lang].required_field } }
        },
        other_category: {
          validators: {
            notEmpty: { message: translations[lang].required_field },
            regexp: { regexp: /^[a-zA-Z\u0621-\u064A\s]*$/i, message: translations[lang].other_category.stringOnly }
          }
        },
        Commercial_Register: {
          validators: { callback: { callback: input => validateFileField(input, 'Commercial_Register', lang) } }
        },
        Tax_Card: {
          validators: { callback: { callback: input => validateFileField(input, 'Tax_Card', lang) } }
        },
        Country_Register: {
          validators: {
            callback: {
              callback: () => $('#CreatedTax').val() === '1' && !$('#Country_Register').val()
                ? { valid: false, message: translations[lang].required_field }
                : { valid: true }
            }
          }
        },
        company_registration_type: { validators: { notEmpty: { message: translations[lang].required_field } } },
        previous_incubation: { validators: { notEmpty: { message: translations[lang].required_field } } },
        previous_incubator: {
          validators: {
            callback: {
              callback: ({ value }) => {
                if ($('#previous_incubation').val() !== '1') return { valid: true };
                const val = (value || '').trim();
                if (!val) return { valid: false, message: translations[lang].required_field };
                if (val.length > 150) return { valid: false, message: translations[lang].previous_incubator.maxLength };
                return { valid: true };
              }
            }
          }
        }
      },
      plugins: buildFVPlugins()
    });

    // Message placement fix
    editInfoFV.on('plugins.message.placed', e => {
      if (e.element.parentElement.classList.contains('input-group')) {
        e.element.parentElement.after(e.messageElement);
      }
    });

    // Submit Handler
    editInfoFV.on('core.form.valid', () => {
      submitFormWithFetch({
        form: document.getElementById('edit-info-form'),
        submitBtnId: 'submit-button',
        btnTextId: 'button-text',
        btnSpinnerId: 'button-spinner',
        successMessage: translations[lang].saveSuccess,
        // First-time save (tabs still locked until the startup name exists):
        // reload so the other tabs unlock without a manual refresh.
        onSuccess: () => {
          if (window.RELOAD_AFTER_INFO_SAVE) {
            setTimeout(() => location.reload(), 700);
          }
        }
      });
    });

    /* Other Category Toggle */
    function syncOtherCategory() {
      const isOther = $('input[name="business_categories[]"]:checked').filter((_, el) =>
        el.value === 'Other' || el.value === 'أخرى'
      ).length > 0;

      $('#otherCategoryInput').toggle(isOther);

      if (isOther) {
        editInfoFV.enableValidator('other_category', 'notEmpty');
        editInfoFV.enableValidator('other_category', 'regexp');
      } else {
        editInfoFV.disableValidator('other_category', 'notEmpty');
        editInfoFV.disableValidator('other_category', 'regexp');
        editInfoFV.resetField('other_category');
      }
    }
    syncOtherCategory();
    $('input[name="business_categories[]"]').on('change', syncOtherCategory);

    /* File Validation Toggle */
    const fileFields = ['Commercial_Register', 'Tax_Card', 'Country_Register'];

    function syncFileValidation() {
      const isRegistered = $('#CreatedTax').val() === '1';
      fileFields.forEach(field => {
        if (isRegistered) {
          editInfoFV.enableValidator(field, 'callback');
        } else {
          editInfoFV.disableValidator(field, 'callback');
          editInfoFV.resetField(field);
        }
      });
    }
    syncFileValidation();
    $('#CreatedTax').on('change', () => {
      syncFileValidation();
      fileFields.forEach(f => editInfoFV.revalidateField(f));
    });

    /* Revalidate file inputs */
    $('#Commercial_Register, #Tax_Card').on('change', function () {
      editInfoFV.revalidateField(this.id);
    });

    /* Previous Incubation Toggle */
    function syncIncubator() {
      const isYes = $('#previous_incubation').val() === '1';
      $('#previousIncubatorWrapper').toggle(isYes);
      if (!isYes) {
        $('#previous_incubator').val('');
        editInfoFV.resetField('previous_incubator');
      }
      editInfoFV.revalidateField('previous_incubator');
    }
    syncIncubator();
    $('#previous_incubation').on('change', syncIncubator);
    $('#previous_incubator').on('input', () => editInfoFV.revalidateField('previous_incubator'));

    /* Country Register Select2 */
    $('#Country_Register').select2({
      placeholder: 'Select Country',
      allowClear: true
    }).on('change select2:close', () => {
      setTimeout(() => editInfoFV.revalidateField('Country_Register'), 50);
    });
  }

  /* ====================== DESCRIPTION FORM ====================== */
  if ($('#desc-form').length) {
    editDescFV = FormValidation.formValidation(document.getElementById('desc-form'), {
      fields: {
        company_description: {
          stringLength: {
            max: 500,
            message: translations[lang].company_description.max
          },
        },
      },
      plugins: buildFVPlugins('.mb-4')
    });

    editDescFV.on('core.form.valid', () => {
      submitFormWithFetch({
        form: document.getElementById('desc-form'),
        submitBtnId: 'submit-button-desc',
        btnTextId: 'button-text-desc',
        btnSpinnerId: 'button-spinner-desc',
        successMessage: translations[lang].saveSuccess
      });
    });

  }

  /* ====================== MVP FORM ====================== */
  if ($('#mvp-form').length) {
    const $businessModel = $('#business_model');

    mvpFV = FormValidation.formValidation(document.getElementById('mvp-form'), {
      fields: {
        business_model: { validators: { notEmpty: { message: translations[lang].required_field } } },
        other_business_model: {
          validators: {
            callback: {
              message: translations[lang].required_field,
              callback: () => $businessModel.val() !== 'Other' || $('#other_business_model').val().trim() !== ''
            }
          }
        },
        business_model_description: {
          validators: {
            notEmpty: { message: translations[lang].required_field },
            stringLength: { min: 10, message: translations[lang].business_model_description.min }
          }
        },
        mvp_prototype_link: {
          validators: { uri: { message: translations[lang].mvp_prototype_link.invalid_url } }
        },
        business_plan: {
          validators: {
            file: {
              extension: 'pdf,doc,docx,ppt,pptx',
              maxSize: 20 * 1024 * 1024,
              message: translations[lang].business_plan.file_invalid
            },
            callback: {
              message: translations[lang].business_plan.file_required,
              callback: ({ element, files }) => {
                // Not required when the company already has an attachment on file
                // (either the dedicated card id or the download link next to the field).
                const hasExisting = $('#business_plan_card').length ||
                  $(element).closest('.mb-4').find('a[download]').length;
                return Boolean(hasExisting) || files.length > 0;
              }
            }
          }
        }
      },
      plugins: buildFVPlugins('.mb-4')
    });

    mvpFV.on('core.form.valid', () => {
      submitFormWithFetch({
        form: document.getElementById('mvp-form'),
        submitBtnId: 'submit-button-mvp',
        btnTextId: 'button-text-mvp',
        btnSpinnerId: 'button-spinner-mvp',
        successMessage: translations[lang].saveSuccess
      });
    });

    // Drop any stale server-rendered "required" error once the user picks a file
    $('#business_plan').on('change', () => clearServerValidationState(document.getElementById('mvp-form')));

    /* Toggle Other Business Model */
    function syncOtherBusinessModel() {
      const isOther = $businessModel.val() === 'Other';
      $('.IfOther').toggle(isOther);
      if (!isOther) mvpFV.revalidateField('other_business_model');
    }
    $businessModel.on('change', syncOtherBusinessModel);
    syncOtherBusinessModel();
  }

  /* ====================== FINANCIAL FORM ====================== */
  if ($('#financial-form').length) {
    const $generateRevenue = $('#generate_revenue');

    financialFV = FormValidation.formValidation(document.getElementById('financial-form'), {
      fields: {
        generate_revenue: { validators: { notEmpty: { message: translations[lang].required_field } } },
        average_annual_revenues: {
          validators: {
            callback: {
              callback: ({ value }) => {
                if ($generateRevenue.val() !== 'yes') return { valid: true };
                const val = (value || '').trim();
                if (!val) return { valid: false, message: translations[lang].required_field };
                if (!/^\d+$/.test(val)) return { valid: false, message: translations[lang].average_annual_revenues.number };
                if (Number(val) < 0) return { valid: false, message: translations[lang].average_annual_revenues.min };
                return { valid: true };
              }
            }
          }
        },
        financial_plan: {
          validators: {
            file: {
              extension: 'pdf,doc,docx,xls,xlsx,ppt,pptx',
              maxSize: 10 * 1024 * 1024, // 10 MB
              message: function(input) {
                if (input && input.element && input.element.files && input.element.files.length > 0) {
                  const file = input.element.files[0];
                  if (file.size > 10 * 1024 * 1024) {
                    return translations[lang].financial_plan.size;
                  }
                }
                return translations[lang].financial_plan.file_invalid;
              }
            }
          }
        }
      },
      plugins: buildFVPlugins('.mb-4, .form-group')
    });

    financialFV.on('core.form.valid', () => {
      submitFormWithFetch({
        form: document.getElementById('financial-form'),
        submitBtnId: 'submit-button-financial',
        btnTextId: 'button-text-financial',
        btnSpinnerId: 'button-spinner-financial',
        successMessage: translations[lang].financialSaveSuccess
      });
    });

    /* Show/Hide Average Annual Revenues */
    function syncRevenueField(triggerValidation) {
      const isYes = $generateRevenue.val() === 'yes';
      $('#average_annual_revenues_wrapper').toggle(isYes);
      if (!isYes) {
        $('#average_annual_revenues').val('');
        financialFV.resetField('average_annual_revenues');
      } else if (triggerValidation) {
        financialFV.revalidateField('average_annual_revenues');
      }
    }
    syncRevenueField(false);
    $generateRevenue.on('change', () => {
      syncRevenueField(true);
      financialFV.revalidateField('generate_revenue');
    });

    $('#average_annual_revenues').on('input', () => financialFV.revalidateField('average_annual_revenues'));
  }

  /* ====================== SHARED ====================== */
  $select2.on('change', function () {
    const fieldName = $(this).attr('name');
    [editInfoFV, editDescFV, mvpFV, financialFV].forEach(fv => {
      if (fv) fv.revalidateField(fieldName);
    });
  });

  /* Flatpickr */
  $('#est-date').flatpickr({
    monthSelectorType: 'static',
    maxDate: 'today'
  });

})(jQuery);
