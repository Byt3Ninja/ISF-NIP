/* Edit Company Validations */

'use strict';

const translations = {
  en: {
    name_en: {
      notEmpty: 'Please enter company name',
      stringOnly: 'Please enter a valid company name'
    },
    name_ar: {
      notEmpty: 'Please enter company name',
      stringOnly: 'Please enter a valid company name'
    },
    address_en: {
      notEmpty: 'Please enter address',
      address: 'Please enter a valid address'
    },
    address_ar: {
      notEmpty: 'Please enter address',
      address: 'Please enter a valid address using letters'
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
      notEmpty: 'Please select country of registration'
    }
  },
  ar: {
    name_en: {
      notEmpty: 'الرجاء إدخال اسم الشركة',
      stringOnly: 'الرجاء إدخال اسم شركة صالح'
    },
    name_ar: {
      notEmpty: 'الرجاء إدخال اسم الشركة',
      stringOnly: 'الرجاء إدخال اسم شركة صالح'
    },
    address_en: {
      notEmpty: 'الرجاء إدخال العنوان ',
      address: 'الرجاء إدخال عنوان صالح باستخدام الحروف والأرقام والشرطات فقط.'
    },
    address_ar: {
      notEmpty: 'الرجاء إدخال العنوان',
      address: 'الرجاء إدخال عنوان صالح باستخدام الحروف والأرقام والشرطات فقط.'
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
      notEmpty: 'الرجاء اختيار دولة التسجيل'
    }
  }
};

const descTranslations = {
  en: {
    company_description: {
      stringOnly: 'Company description accepts letters and symbols only'
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
      stringOnly: 'وصف الشركة يقبل الحروف والرموز فقط'
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
  // console.log('DOM loaded with JavaScript');

  const editInfoForm = document.getElementById('edit-info-form');
  const editDescForm = document.getElementById('desc-form');
  const establishmentDate = document.querySelectorAll('#est-date');
  const businessCategories = document.querySelectorAll('input[name="business_categories[]"]');
  const otherCategoryField = document.getElementById('other-category-field');
  const stateField = document.getElementById('state_id');
  const createdTaxSelect = document.getElementById('CreatedTax');

  const validationConfig = {
    fields: {
      name_en: {
        validators: {
          notEmpty: {
            message: translations[lang].name_en.notEmpty
          },
          regexp: {
            regexp: /^[\p{L}\d\s.,;&@:-]+$/u,
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
            regexp: /^[\p{L}\d\s.,;&@:-]+$/u,
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
      other_category: {
        validators: {
          notEmpty: {
            message: translations[lang].other_category.notEmpty
          },
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
              const createdTax = document.getElementById('CreatedTax');
              if (createdTax && createdTax.value === '1') {
                const fileInput = input.element;
                const existingFileInput = document.querySelector('input[name="Commercial_Register_value"]');
                
                // Check if file exists (new upload OR existing file)
                const hasNewFile = fileInput.files && fileInput.files.length > 0;
                const hasExistingFile = existingFileInput && existingFileInput.value;
                
                if (!hasNewFile && !hasExistingFile) {
                  return {
                    valid: false,
                    message: translations[lang].Commercial_Register.required
                  };
                }
                
                // If new file is selected, validate format and size
                if (hasNewFile) {
                  const file = fileInput.files[0];
                  const allowedExtensions = ['pdf', 'png', 'jpeg', 'jpg'];
                  const fileExtension = file.name.split('.').pop().toLowerCase();
                  
                  if (!allowedExtensions.includes(fileExtension)) {
                    return {
                      valid: false,
                      message: translations[lang].Commercial_Register.format
                    };
                  }
                  
                  if (file.size > 10 * 1024 * 1024) { // 10 MB
                    return {
                      valid: false,
                      message: translations[lang].Commercial_Register.size
                    };
                  }
                }
              }
              
              return { valid: true };
            }
          }
        }
      },
      Tax_Card: {
        validators: {
          callback: {
            callback: function (input) {
              const createdTax = document.getElementById('CreatedTax');
              if (createdTax && createdTax.value === '1') {
                const fileInput = input.element;
                const existingFileInput = document.querySelector('input[name="Tax_Card_value"]');
                
                // Check if file exists (new upload OR existing file)
                const hasNewFile = fileInput.files && fileInput.files.length > 0;
                const hasExistingFile = existingFileInput && existingFileInput.value;
                
                if (!hasNewFile && !hasExistingFile) {
                  return {
                    valid: false,
                    message: translations[lang].Tax_Card.required
                  };
                }
                
                // If new file is selected, validate format and size
                if (hasNewFile) {
                  const file = fileInput.files[0];
                  const allowedExtensions = ['pdf', 'png', 'jpeg', 'jpg'];
                  const fileExtension = file.name.split('.').pop().toLowerCase();
                  
                  if (!allowedExtensions.includes(fileExtension)) {
                    return {
                      valid: false,
                      message: translations[lang].Tax_Card.format
                    };
                  }
                  
                  if (file.size > 10 * 1024 * 1024) { // 10 MB
                    return {
                      valid: false,
                      message: translations[lang].Tax_Card.size
                    };
                  }
                }
              }
              
              return { valid: true };
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
                // Country_Register is required when CreatedTax is "Yes"
                const value = input.value || '';
                if (value === '') {
                  return {
                    valid: false,
                    message: translations[lang].Country_Register.notEmpty
                  };
                }
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
          // regexp: {
          //   regexp: /^[\p{L}\p{P}\p{S}\s.]+$/u,
          //   message: descTranslations[lang].company_description.stringOnly
          // }        
        }
      },
      about_problem: {
        validators: {
          // regexp: {
          //   regexp: /^[\p{L}\s.]+$/u,
          //   message: descTranslations[lang].about_problem.stringOnly
          // }
        }
      },
      about_market_opportunity: {
        validators: {
          // regexp: {
          //   regexp: /^[\p{L}\s.]+$/u,
          //   message: descTranslations[lang].about_market_opportunity.stringOnly
          // }
        }
      },
      about_competitors: {
        validators: {
          // regexp: {
          //   regexp: /^[\p{L}\s.]+$/u,
          //   message: descTranslations[lang].about_competitors.stringOnly
          // }
        }
      },
      about_monetize: {
        validators: {
          // regexp: {
          //   regexp: /^[\p{L}\s.]+$/u,
          //   message: descTranslations[lang].about_monetize.stringOnly
          // }
        }
      },
      about_challenges: {
        validators: {
          // regexp: {
          //   regexp: /^[\p{L}\s.]+$/u,
          //   message: descTranslations[lang].about_challenges.stringOnly
          // }
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

    // Initialize Select2 for Country_Register if not already initialized
    const countryRegisterSelect = document.getElementById('Country_Register');
    if (countryRegisterSelect && !$(countryRegisterSelect).hasClass('select2-hidden-accessible')) {
      $(countryRegisterSelect).select2({
        placeholder: countryRegisterSelect.getAttribute('data-placeholder') || 'Select Country',
        allowClear: true
      });
    }

    // Handle Select2 validation for Country_Register
    if (countryRegisterSelect) {
      $(countryRegisterSelect).on('select2:select select2:unselect select2:clear', function() {
        // Revalidate the field when Select2 value changes
        formValidation.revalidateField('Country_Register');
      });
      
      // Also handle regular change event as backup
      $(countryRegisterSelect).on('change', function() {
        formValidation.revalidateField('Country_Register');
      });
    }

    // Initial check for Other category visibility
    const checkOtherCategoryVisibility = () => {
      const isOtherSelected = Array.from(businessCategories).some(cat => (cat.value === 'Other' || cat.value === 'أخرى') && cat.checked);
      const otherCategoryInput = document.getElementById('otherCategoryInput');
      
      if (otherCategoryInput) {
        otherCategoryInput.style.display = isOtherSelected ? 'block' : 'none';
      }
      
      // Enable or disable validation for other_category field
      if (isOtherSelected) {
        formValidation.enableValidator('other_category', 'notEmpty');
        formValidation.enableValidator('other_category', 'regexp');
      } else {
        formValidation.disableValidator('other_category', 'notEmpty');
        formValidation.disableValidator('other_category', 'regexp');
      }
    };

    // Function to handle file validation based on CreatedTax value
    const handleFileValidation = () => {
      const createdTaxValue = createdTaxSelect ? createdTaxSelect.value : '0';
      
      if (createdTaxValue === '1') {
        // Enable file validation when CreatedTax is "Yes"
        formValidation.enableValidator('Commercial_Register', 'callback');
        formValidation.enableValidator('Tax_Card', 'callback');
        formValidation.enableValidator('Country_Register', 'callback');
      } else {
        // Disable file validation when CreatedTax is "No"
        formValidation.disableValidator('Commercial_Register', 'callback');
        formValidation.disableValidator('Tax_Card', 'callback');
        formValidation.disableValidator('Country_Register', 'callback');
        
        // Clear any existing validation errors
        formValidation.resetField('Commercial_Register');
        formValidation.resetField('Tax_Card');
        formValidation.resetField('Country_Register');
      }
    };

    // Check initial state
    checkOtherCategoryVisibility();
    handleFileValidation();

    // Add event listener to CreatedTax select
    if (createdTaxSelect) {
      createdTaxSelect.addEventListener('change', function() {
        handleFileValidation();
        
        // Revalidate the file fields when CreatedTax changes
        formValidation.revalidateField('Commercial_Register');
        formValidation.revalidateField('Tax_Card');
        formValidation.revalidateField('Country_Register');
        
        // Force revalidation for Select2 fields
        setTimeout(() => {
          formValidation.revalidateField('Country_Register');
        }, 100);
      });
    }

    // Add event listener to business categories checkboxes
    businessCategories.forEach(category => {
      category.addEventListener('change', function () {
        const isOtherSelected = Array.from(businessCategories).some(cat => (cat.value === 'Other' || cat.value === 'أخرى') && cat.checked);
        const otherCategoryInput = document.getElementById('otherCategoryInput');
        
        if (isOtherSelected) {
          // Show the other category input field
          if (otherCategoryInput) {
            otherCategoryInput.style.display = 'block';
          }
          
          // Enable validation for other_category field
          formValidation.enableValidator('other_category', 'notEmpty');
          formValidation.enableValidator('other_category', 'regexp');
        } else {
          // Hide the other category input field
          if (otherCategoryInput) {
            otherCategoryInput.style.display = 'none';
          }
          
          // Disable validation for other_category field
          formValidation.disableValidator('other_category', 'notEmpty');
          formValidation.disableValidator('other_category', 'regexp');
          
          // Clear any existing validation errors
          formValidation.resetField('other_category');
        }
      });
    });

    // Add event listeners to file inputs to revalidate when files are selected
    const commercialRegisterInput = document.getElementById('Commercial_Register');
    const taxCardInput = document.getElementById('Tax_Card');

    if (commercialRegisterInput) {
      commercialRegisterInput.addEventListener('change', function() {
        formValidation.revalidateField('Commercial_Register');
      });
    }

    if (taxCardInput) {
      taxCardInput.addEventListener('change', function() {
        formValidation.revalidateField('Tax_Card');
      });
    }

    if (countryRegisterSelect) {
      // For Select2, we need to listen to select2 events instead of regular change events
      $(countryRegisterSelect).on('select2:select select2:unselect select2:clear', function() {
        formValidation.revalidateField('Country_Register');
      });
      
      // Also handle regular change event as backup
      $(countryRegisterSelect).on('change', function() {
        formValidation.revalidateField('Country_Register');
      });
      
      // Additional trigger for when the validation needs to be updated
      $(countryRegisterSelect).on('select2:close', function() {
        setTimeout(() => {
          formValidation.revalidateField('Country_Register');
        }, 50);
      });
    }
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