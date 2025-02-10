/* Edit Company Validations */

'use strict';

const translations = {
  en: {
    name_en: {
      notEmpty: 'Please enter company name in English',
      stringOnly: 'Company name accepts english letters only'
    },
    name_ar: {
      notEmpty: 'Please enter company name in Arabic',
      stringOnly: 'Company name accepts arabic letters only'
    },
    address_en: {
      notEmpty: 'Please enter address in English',
      address: 'Please enter a valid English address using English letters'
    },
    address_ar: {
      notEmpty: 'Please enter address in Arabic',
      address: 'Please enter a valid Arabic address using Arabic letters'
    },
    est_date: {
      establishmentDate: 'Establishment date cannot be a future date'
    },
    product_or_service: {
      notEmpty: 'Please select product or service'
    },
    years_of_operations: {
      notEmpty: 'Please enter years of operations'
    },
    sector: {
      notEmpty: 'Please select sector'
    },
    'business_categories[]': {
      notEmpty: 'Please select at least one business category'
    },
    country_id: {
      notEmpty: 'Please select country'
    },
    state_id: {
      notEmpty: 'Please select state'
    },
    other_category: {
      notEmpty: "Please specify your business type in the 'Other' field.",
      stringOnly: lang === 'en' ? 'Business type accepts english letters only' : 'نوع العمل يقبل الحروف الانجليزية فقط'
    },
    Commercial_Register: {
      numeric: 'Commercial Register should contain numbers only'
    },
    Tax_Card: {
      numeric: 'Tax Card should contain numbers only'
    }
  },
  ar: {
    name_en: {
      notEmpty: 'الرجاء إدخال اسم الشركة بالإنجليزية',
      stringOnly: 'اسم الشركة يقبل الحروف الإنجليزية فقط'
    },
    name_ar: {
      notEmpty: 'الرجاء إدخال اسم الشركة بالعربية',
      stringOnly: 'اسم الشركة يقبل الحروف العربية فقط'
    },
    address_en: {
      notEmpty: 'الرجاء إدخال العنوان بالإنجليزية',
      address: 'الرجاء إدخال عنوان صالح باللغة الانجليزية باستخدام الحروف الانجليزية والأرقام والشرطات فقط.'
    },
    address_ar: {
      notEmpty: 'الرجاء إدخال العنوان بالعربية',
      address: 'الرجاء إدخال عنوان صالح باللغة العربية باستخدام الحروف العربية والأرقام والشرطات فقط.'
    },
    est_date: {
      establishmentDate: 'لا يمكن أن يكون تاريخ التأسيس تاريخًا مستقبليًا'
    },
    product_or_service: {
      notEmpty: 'الرجاء اختيار المنتج أو الخدمة'
    },
    years_of_operations: {
      notEmpty: 'الرجاء إدخال سنوات العمليات'
    },
    sector: {
      notEmpty: 'الرجاء اختر القطاع'
    },
    'business_categories[]': {
      notEmpty: 'الرجاء اختيار فئة عمل واحدة على الأقل'
    },
    country_id: {
      notEmpty: 'الرجاء اختيار الدولة'
    },
    state_id: {
      notEmpty: 'الرجاء اختيار المنطقة'
    },
    other_category: {
      notEmpty: 'الرجاء تحديد نوع عملك في الحقل "آخر".',
      stringOnly: lang === 'en' ? 'Business type accepts arabic letters only' : 'نوع العمل يقبل الحروف العربية فقط'
    },
    Commercial_Register: {
      numeric: 'يجب أن يحتوي السجل التجاري على أرقام فقط'
    },
    Tax_Card: {
      numeric: 'يجب أن يحتوي البطاقة الضريبية على أرقام فقط'
    }
  }
};

const descTranslations = {
  en: {
    company_description: {
      stringOnly: 'Company description accepts letters only'
    },
    about_problem: {
      stringOnly: 'About the problem accepts letters only'
    },
    about_market_opportunity: {
      stringOnly: 'About the market opportunity accepts letters only'
    },
    about_competitors: {
      stringOnly: 'About the competitors accepts letters only'
    },
    about_monetize: {
      stringOnly: 'About monetize accepts letters only'
    },
    about_challenges: {
      stringOnly: 'About challenges accepts letters only'
    }
  },
  ar: {
    company_description: {
      stringOnly: 'وصف الشركة يقبل الحروف فقط'
    },
    about_problem: {
      stringOnly: 'حول المشكلة يقبل الحروف فقط'
    },
    about_market_opportunity: {
      stringOnly: 'حول فرص السوق يقبل الحروف فقط'
    },
    about_competitors: {
      stringOnly: 'حول المنافسين يقبل الحروف فقط'
    },
    about_monetize: {
      stringOnly: 'حول التحويل يقبل الحروف فقط'
    },
    about_challenges: {
      stringOnly: 'حول التحديات يقبل الحروف فقط'
    }
  }
};

document.addEventListener('DOMContentLoaded', function () {
  console.log('DOM loaded with JavaScript');

  const editInfoForm = document.getElementById('edit-info-form');
  const editDescForm = document.getElementById('desc-form');
  const establishmentDate = document.querySelectorAll('#est-date');
  const businessCategories = document.querySelectorAll('input[name="business_categories[]"]');
  const otherCategoryField = document.getElementById('other-category-field');
  const stateField = document.getElementById('state_id');

  const validationConfig = {
    fields: {
      name_en: {
        validators: {
          notEmpty: {
            message: translations[lang].name_en.notEmpty
          },
          regexp: {
            regexp: /^[\p{L}\d\s]+$/u,
            message: translations[lang].name_en.stringOnly
          }
        }
      },
      name_ar: {
        validators: {
          notEmpty: {
            message: translations[lang].name_ar.notEmpty
          },
          regexp: {
            regexp: /^[\p{L}\d\s]+$/u,
            message: translations[lang].name_ar.stringOnly
          }
        }
      },
      address_en: {
        validators: {
          notEmpty: {
            message: translations[lang].address_en.notEmpty
          },
          regexp: {
            regexp: /^(?!\s)[\p{L}\p{N}\s,;:-]*$/u,
            message: translations[lang].address_en.address
          }
        }
      },
      address_ar: {
        validators: {
          notEmpty: {
            message: translations[lang].address_ar.notEmpty
          },
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
              const date = new Date(input.value);
              const today = new Date();
              return date <= today;
            }
          }
        }
      },
      product_or_service: {
        validators: {
          notEmpty: {
            message: translations[lang].product_or_service.notEmpty
          }
        }
      },
      years_of_operations: {
        validators: {
          notEmpty: {
            message: translations[lang].years_of_operations.notEmpty
          }
        }
      },
      sector: {
        validators: {
          notEmpty: {
            message: translations[lang].sector.notEmpty
          }
        }
      },
      country_id: {
        validators: {
          notEmpty: {
            message: translations[lang].country_id.notEmpty
          }
        }
      },
      state_id: {
        validators: {
          notEmpty: {
            message: translations[lang].state_id.notEmpty
          }
        }
      },
      'business_categories[]': {
        validators: {
          notEmpty: {
            message: translations[lang]['business_categories[]'].notEmpty
          }
        }
      },
      Commercial_Register: {
        validators: {
          numeric: {
            message: translations[lang].Commercial_Register.numeric
          }
        }
      },
      Tax_Card: {
        validators: {
          numeric: {
            message: translations[lang].Tax_Card.numeric
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: '.mb-6'
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
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

  const descValidationConfig = {
    fields: {
      company_description: {
        validators: {
          regexp: {
            regexp: /^[\p{L}\s.]+$/u,
            message: descTranslations[lang].company_description.stringOnly
          }
        }
      },
      about_problem: {
        validators: {
          regexp: {
            regexp: /^[\p{L}\s.]+$/u,
            message: descTranslations[lang].about_problem.stringOnly
          }
        }
      },
      about_market_opportunity: {
        validators: {
          regexp: {
            regexp: /^[\p{L}\s.]+$/u,
            message: descTranslations[lang].about_market_opportunity.stringOnly
          }
        }
      },
      about_competitors: {
        validators: {
          regexp: {
            regexp: /^[\p{L}\s.]+$/u,
            message: descTranslations[lang].about_competitors.stringOnly
          }
        }
      },
      about_monetize: {
        validators: {
          regexp: {
            regexp: /^[\p{L}\s.]+$/u,
            message: descTranslations[lang].about_monetize.stringOnly
          }
        }
      },
      about_challenges: {
        validators: {
          regexp: {
            regexp: /^[\p{L}\s.]+$/u,
            message: descTranslations[lang].about_challenges.stringOnly
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: '.mb-6'
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
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

  if (editInfoForm) {
    const formValidation = FormValidation.formValidation(editInfoForm, validationConfig);

    // Add event listener to business categories checkboxes
    businessCategories.forEach(category => {
      category.addEventListener('change', function () {
        const isOtherSelected = Array.from(businessCategories).some(cat => cat.value === 'Other' && cat.checked);
        if (isOtherSelected) {
          formValidation.addField('other_category', {
            validators: {
              notEmpty: {
                message: translations[lang].other_category.notEmpty
              },
              regexp: {
                regexp: language === 'en' ? /^[a-zA-Z\s]*$/i : /^[\u0621-\u064A\s]*$/i,
                message: translations[lang].other_category.stringOnly
              }
            }
          });
        } else {
          formValidation.removeField('other_category');
        }
      });
    });
  }

  if (editDescForm) {
    FormValidation.formValidation(editDescForm, descValidationConfig);
  }

  // Establishment Date
  if (establishmentDate) {
    establishmentDate.flatpickr({
      monthSelectorType: 'static',
      maxDate: 'today'
    });
  }
});
