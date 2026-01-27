/**
 *  Form Wizard
 */

'use strict';


(function () {
  const translations = {
    en: {
      required: "This field is required",
      logo: "Unsupported. Upload PNG, JPG, JPEG, WEBP or SVG | Max size: 5MB",
      logoSize: "Logo size should not exceed 5MB",
      phone: "Phone number should contain numbers only",
      invalidPhone: "Invalid phone number",
      email: "E-mail should be a valid email address",
    },
    ar: {
      required: "هذا الحقل مطلوب",
      logo: "غير مدعوم. قم بتحميل PNG, JPG, JPEG, WEBP أو SVG | حجم الشعار لا يجب أن يتجاوز 5 MB",
      logoSize: "حجم الشعار لا يجب أن يتجاوز 5 ميغابايت",
      phone: "رقم الهاتف يجب أن يحتوي على أرقام فقط",
      invalidPhone: "رقم الهاتف غير صالح",
      email: "البريد الإلكتروني يجب أن يكون عنوان بريد إلكتروني صالح",
    }
  };

  let iti; // Declare iti in a scope accessible by custom validator


  const lang = $('html').attr('lang') || 'en';

  const select2 = $('.select2'),
    selectPicker = $('.selectpicker'),
    intlTelInput = $('.intl-tel-input');

  // Wizard Validation
  // --------------------------------------------------------------------
  const wizardValidation = document.querySelector('#wizard-validation');
  if (typeof wizardValidation !== undefined && wizardValidation !== null) {
    // Wizard form
    const wizardValidationForm = wizardValidation.querySelector('#service-provider-form-wizard');
    // Wizard steps
    const wizardValidationFormStep1 = wizardValidationForm.querySelector('#step-1-basics');
    const wizardValidationFormStep2 = wizardValidationForm.querySelector('#step-2-booking');
    const wizardValidationFormStep3 = wizardValidationForm.querySelector('#step-3-stats');
    // const wizardValidationFormStep4 = wizardValidationForm.querySelector('#step-4-portofolio');
    // const wizardValidationFormStep5 = wizardValidationForm.querySelector('#step-5-clients');

    console.log('wizardValidationFormStep1', wizardValidationFormStep1);

    // Wizard next prev button
    const wizardValidationNext = wizardValidationForm.querySelector('.btn-next');
    const wizardValidationPrev = wizardValidationForm.querySelector('.btn-prev');

    const validationStepper = new Stepper(wizardValidation, {
      linear: true
    });

    // Profile basics
    const FormValidation1 = FormValidation.formValidation(wizardValidationFormStep1, {
      fields: {
        company_logo: {
          validators: {
            notEmpty: {
              message: translations[lang].required,
            },
            file: {
              extension: 'png,jpg,jpeg,svg,webp',
              maxSize: 5 * 1024 * 1024, // 5MB
              message: translations[lang].logo
            }
          }
        },

        company_name: {
          validators: {
            notEmpty: {
              message: translations[lang].required,
            }
          }
        },
        contact_name: {
          validators: {
            notEmpty: {
              message: translations[lang].required,
            }
          }
        },
        email: {
          validators: {
            notEmpty: {
              message: translations[lang].required,
            },
            regexp: {
              regexp: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i,
              message: translations[lang].email,
            }
          }
        },
        phone: {
          validators: {
            notEmpty: {
              message: translations[lang].required,
            },
            callback: {
              message: translations[lang].invalidPhone,
              callback: function (input) {
                const value = input.value.trim();

                // Skip validation if empty (handled by notEmpty validator)
                if (value === '') {
                  return { valid: true };
                }

                // Check if contains only numbers and allowed phone characters
                const numericRegex = /^[\d\s\+\-\(\)]+$/;
                if (!numericRegex.test(value)) {
                  return {
                    valid: false,
                    message: translations[lang].phone
                  };
                }

                // Get selected country
                let selectedCountry = '';
                if (iti && typeof iti.getSelectedCountryData === 'function') {
                  const countryData = iti.getSelectedCountryData();
                  selectedCountry = countryData.iso2 || '';
                }

                // For Egypt, allow numbers starting with 0
                if (selectedCountry.toLowerCase() === 'eg') {
                  // Egyptian phone number validation
                  // Format: 0XXXXXXXXX (11 digits) or XXXXXXXXXX (10 digits)
                  const cleanNumber = value.replace(/[\s\-\(\)]/g, '');

                  // Check if it's a valid Egyptian number
                  if (cleanNumber.startsWith('0')) {
                    // Should be 11 digits total
                    if (cleanNumber.length === 11 && /^0(10|11|12|15|2)\d{8}$/.test(cleanNumber)) {
                      return { valid: true };
                    } else {
                      return {
                        valid: false,
                        message: translations[lang].invalidPhone
                      };
                    }
                  } else if (cleanNumber.length === 10 && /^(10|11|12|15|2)\d{8}$/.test(cleanNumber)) {
                    // 10 digits without leading 0
                    return { valid: true };
                  } else {
                    return {
                      valid: false,
                      message: translations[lang].invalidPhone
                    };
                  }
                }

                // Validate using intl-tel-input for other countries
                if (iti && typeof iti.isValidNumber === 'function') {
                  const isValid = iti.isValidNumber();

                  if (!isValid) {
                    // Get the validation error
                    if (typeof iti.getValidationError === 'function') {
                      const error = iti.getValidationError();
                      let message = translations[lang].invalidPhone;

                      // Check if intlTelInputUtils is available
                      if (window.intlTelInputUtils && window.intlTelInputUtils.validationError) {
                        switch (error) {
                          case window.intlTelInputUtils.validationError.INVALID_COUNTRY_CODE:
                            message = translations[lang].invalidPhone;
                            break;
                          case window.intlTelInputUtils.validationError.TOO_SHORT:
                            message = translations[lang].invalidPhone;
                            break;
                          case window.intlTelInputUtils.validationError.TOO_LONG:
                            message = translations[lang].invalidPhone;
                            break;
                          case window.intlTelInputUtils.validationError.NOT_A_NUMBER:
                            message = translations[lang].phone;
                            break;
                          default:
                            message = translations[lang].invalidPhone;
                        }
                      }

                      return {
                        valid: false,
                        message: message
                      };
                    }

                    return {
                      valid: false,
                      message: translations[lang].invalidPhone
                    };
                  }

                  return { valid: true };
                }

                // If iti is not available, do basic validation
                const phoneRegex = /^\+?[\d\s\-\(\)]{8,}$/;
                if (!phoneRegex.test(value)) {
                  return {
                    valid: false,
                    message: translations[lang].invalidPhone
                  };
                }

                return { valid: true };
              }
            }
          }
        },
        whatsapp: {
          validators: {
            notEmpty: {
              message: translations[lang].required,
            },
            callback: {
              message: translations[lang].invalidPhone,
              callback: function (input) {
                const value = input.value.trim();

                // Skip validation if empty (handled by notEmpty validator)
                if (value === '') {
                  return { valid: true };
                }

                // Check if contains only numbers and allowed phone characters
                const numericRegex = /^[\d\s\+\-\(\)]+$/;
                if (!numericRegex.test(value)) {
                  return {
                    valid: false,
                    message: translations[lang].phone
                  };
                }

                // Get selected country
                let selectedCountry = '';
                if (iti && typeof iti.getSelectedCountryData === 'function') {
                  const countryData = iti.getSelectedCountryData();
                  selectedCountry = countryData.iso2 || '';
                }

                // For Egypt, allow numbers starting with 0
                if (selectedCountry.toLowerCase() === 'eg') {
                  // Egyptian phone number validation
                  // Format: 0XXXXXXXXX (11 digits) or XXXXXXXXXX (10 digits)
                  const cleanNumber = value.replace(/[\s\-\(\)]/g, '');

                  // Check if it's a valid Egyptian number
                  if (cleanNumber.startsWith('0')) {
                    // Should be 11 digits total
                    if (cleanNumber.length === 11 && /^0(10|11|12|15|2)\d{8}$/.test(cleanNumber)) {
                      return { valid: true };
                    } else {
                      return {
                        valid: false,
                        message: translations[lang].invalidPhone
                      };
                    }
                  } else if (cleanNumber.length === 10 && /^(10|11|12|15|2)\d{8}$/.test(cleanNumber)) {
                    // 10 digits without leading 0
                    return { valid: true };
                  } else {
                    return {
                      valid: false,
                      message: translations[lang].invalidPhone
                    };
                  }
                }

                // Validate using intl-tel-input for other countries
                if (iti && typeof iti.isValidNumber === 'function') {
                  const isValid = iti.isValidNumber();

                  if (!isValid) {
                    // Get the validation error
                    if (typeof iti.getValidationError === 'function') {
                      const error = iti.getValidationError();
                      let message = translations[lang].invalidPhone;

                      // Check if intlTelInputUtils is available
                      if (window.intlTelInputUtils && window.intlTelInputUtils.validationError) {
                        switch (error) {
                          case window.intlTelInputUtils.validationError.INVALID_COUNTRY_CODE:
                            message = translations[lang].invalidPhone;
                            break;
                          case window.intlTelInputUtils.validationError.TOO_SHORT:
                            message = translations[lang].invalidPhone;
                            break;
                          case window.intlTelInputUtils.validationError.TOO_LONG:
                            message = translations[lang].invalidPhone;
                            break;
                          case window.intlTelInputUtils.validationError.NOT_A_NUMBER:
                            message = translations[lang].phone;
                            break;
                          default:
                            message = translations[lang].invalidPhone;
                        }
                      }

                      return {
                        valid: false,
                        message: message
                      };
                    }

                    return {
                      valid: false,
                      message: translations[lang].invalidPhone
                    };
                  }

                  return { valid: true };
                }

                // If iti is not available, do basic validation
                const phoneRegex = /^\+?[\d\s\-\(\)]{8,}$/;
                if (!phoneRegex.test(value)) {
                  return {
                    valid: false,
                    message: translations[lang].invalidPhone
                  };
                }

                return { valid: true };
              }
            }
          }
        },
        country: {
          validators: {
            notEmpty: {
              message: translations[lang].required,
            }
          }
        },
        state: {
          validators: {
            notEmpty: { message: translations[lang].required, }
          }
        },
        city: {
          validators: {
            notEmpty: { message: translations[lang].required, }
          }
        },
        about: {
          validators: {
            notEmpty: { message: translations[lang].required, }
          }
        }
      },
      plugins: {
trigger: new FormValidation.plugins.Trigger({
  event: {
    file: 'change'
  }
}),        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          // eleInvalidClass: '',
          eleValidClass: '',
          rowSelector: '.mb-4'
        }),
        autoFocus: new FormValidation.plugins.AutoFocus(),
        submitButton: new FormValidation.plugins.SubmitButton()
      },
      init: instance => {
        instance.on('plugins.message.placed', function (e) {
          //* Move the error message out of the `input-group` element
          if (e.element.parentElement.classList.contains('input-group')) {
            e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
          }
        });
      }
    }).on('core.form.valid', function (e) {
      console.log('is valid');
      // Jump to the next step when all fields in the current step are valid
      validationStepper.next();
    })

    FormValidation1.on('core.field.validating', e => {
  if (e.field === 'company_logo') {
    console.log('Validating file:', e.element.files[0]?.size);
  }
});


    // $('input[name="company_logo"]').on('change', function (e) {
    //   if ($(this).val()) {
    //     $('.fv-logo-wrapper .invalid-feedback').removeClass('d-block').addClass('d-none');
    //   }
    // });

    // Personal info
    const FormValidation2 = FormValidation.formValidation(wizardValidationFormStep2, {
      fields: {
        formValidationFirstName: {
          validators: {
            notEmpty: {
              message: 'The first name is required'
            }
          }
        },
        formValidationLastName: {
          validators: {
            notEmpty: {
              message: 'The last name is required'
            }
          }
        },
        formValidationCountry: {
          validators: {
            notEmpty: {
              message: 'The Country is required'
            }
          }
        },
        formValidationLanguage: {
          validators: {
            notEmpty: {
              message: 'The Languages is required'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          // eleInvalidClass: '',
          eleValidClass: '',
          rowSelector: '.col-sm-6'
        }),
        autoFocus: new FormValidation.plugins.AutoFocus(),
        submitButton: new FormValidation.plugins.SubmitButton()
      }
    }).on('core.form.valid', function () {
      // Jump to the next step when all fields in the current step are valid
      validationStepper.next();
    });

    // Bootstrap Select (i.e Language select)
    // if (selectPicker.length) {
    //   selectPicker.each(function () {
    //     var $this = $(this);
    //     $this.selectpicker().on('change', function () {
    //       FormValidation2.revalidateField('formValidationLanguage');
    //     });
    //   });
    // }

    if (intlTelInput.length) {
      intlTelInput.each(function () {
        var $this = $(this);

        // $this.wrap('<div class="position-relative"></div>');

        if (typeof window.intlTelInput === 'function') {
          window.intlTelInput($this[0], {
            initialCountry: 'eg',
            preferredCountries: ["eg", "ae", "sa", "ma", "dz", "tn"],
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
            formatOnDisplay: false,
            separateDialCode: true,
            autoPlaceholder: "aggressive",
            customPlaceholder: function (selectedCountryPlaceholder) {
              return "e.g. " + selectedCountryPlaceholder;
            }
          });
        }
      });
    }

    // Social links
    const FormValidation3 = FormValidation.formValidation(wizardValidationFormStep3, {
      fields: {
        formValidationTwitter: {
          validators: {
            notEmpty: {
              message: 'The Twitter URL is required'
            },
            uri: {
              message: 'The URL is not proper'
            }
          }
        },
        formValidationFacebook: {
          validators: {
            notEmpty: {
              message: 'The Facebook URL is required'
            },
            uri: {
              message: 'The URL is not proper'
            }
          }
        },
        formValidationGoogle: {
          validators: {
            notEmpty: {
              message: 'The Google URL is required'
            },
            uri: {
              message: 'The URL is not proper'
            }
          }
        },
        formValidationLinkedIn: {
          validators: {
            notEmpty: {
              message: 'The LinkedIn URL is required'
            },
            uri: {
              message: 'The URL is not proper'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          // eleInvalidClass: '',
          eleValidClass: '',
          rowSelector: '.col-sm-6'
        }),
        autoFocus: new FormValidation.plugins.AutoFocus(),
        submitButton: new FormValidation.plugins.SubmitButton()
      }
    }).on('core.form.valid', function () {
      // You can submit the form
      // wizardValidationForm.submit()
      // or send the form data to server via an Ajax request
      // To make the demo simple, I just placed an alert
      alert('Submitted..!!');
    });

    const wizardValidationNextButton = wizardValidation.querySelector('.btn-next');
    wizardValidationNextButton.addEventListener('click', event => {
      // When click the Next button, we will validate the current step
      switch (validationStepper._currentIndex) {
        case 0:
          FormValidation1.validate();
          break;

        case 1:
          FormValidation2.validate();
          break;

        case 2:
          FormValidation3.validate();
          break;

        default:
          break;
      }
    });

    const wizardValidationPrevButton = wizardValidation.querySelector('.btn-prev');
    wizardValidationPrevButton.addEventListener('click', event => {
      validationStepper.previous();
    });
  }
})();
