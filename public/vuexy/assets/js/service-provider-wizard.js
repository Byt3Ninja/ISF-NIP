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
      hero_image: "Unsupported. Upload PNG, JPG, JPEG, WEBP or SVG | Max size: 10MB",
      phone: "Phone number should contain numbers only",
      invalidPhone: "Invalid phone number",
      invalid_url: "The URL is not valid",
      email: "E-mail should be a valid email address",
      media_required: "Media is required",
      invalid_media: "Invalid file or size too large",
      title_required: "Title is required",
      title_max: "Title must be less than 50 chars",
      desc_required: "Description is required",
      desc_max: "Description must be less than 100 chars",
      at_least_one_item: "Please add at least one complete work item"
    },
    ar: {
      required: "هذا الحقل مطلوب",
      logo: "غير مدعوم. قم بتحميل PNG, JPG, JPEG, WEBP أو SVG | حجم الشعار لا يجب أن يتجاوز 5 MB",
      logoSize: "حجم الشعار لا يجب أن يتجاوز 5 ميغابايت",
      hero_image: "غير مدعوم. قم بتحميل PNG, JPG, JPEG, WEBP أو SVG | حجم الصورة لا يجب أن يتجاوز 10 MB",
      phone: "رقم الهاتف يجب أن يحتوي على أرقام فقط",
      invalidPhone: "رقم الهاتف غير صالح",
      invalid_url: "الرابط غير صالح",
      email: "البريد الإلكتروني يجب أن يكون عنوان بريد إلكتروني صالح",
      media_required: "الملف مطلوب",
      invalid_media: "ملف غير صالح أو حجم كبير جداً",
      title_required: "العنوان مطلوب",
      title_max: "العنوان يجب أن يكون أقل من 50 حرف",
      desc_required: "الوصف مطلوب",
      desc_max: "الوصف يجب أن يكون أقل من 100 حرف",
      at_least_one_item: "يرجى إضافة عمل واحد على الأقل"
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
    const wizardValidationFormStep4 = wizardValidationForm.querySelector('#step-4-portfolio');
    // const wizardValidationFormStep5 = wizardValidationForm.querySelector('#step-5-clients');

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
        }),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
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
    });

    // Intro & booking
    const FormValidation2 = FormValidation.formValidation(wizardValidationFormStep2, {
      fields: {
        hero_image: {
          validators: {
            notEmpty: {
              message: translations[lang].required,
            },
            file: {
              extension: 'png,jpg,jpeg,svg,webp',
              maxSize: 10 * 1024 * 1024, // 10MB
              message: translations[lang].hero_image
            }
          }
        },
        hero_description: {
          validators: {
            notEmpty: {
              message: translations[lang].required,
            }
          }
        },
        appointment_link: {
          validators: {
            uri: {
              message: translations[lang].invalid_url
            },
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger({
          event: {
            file: 'change'
          }
        }),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          // eleInvalidClass: '',
          eleValidClass: '',
          rowSelector: '.mb-4'
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

    // Key stats
    const FormValidation3 = FormValidation.formValidation(wizardValidationFormStep3, {
      fields: {
        value1: {
          validators: {
            callback: {
              message: translations[lang].required,
              callback: function (input) {
                const label = document.getElementById('label1').value.trim();
                // If label has value, then value is required
                // If label is empty, value is optional
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
              message: translations[lang].required,
              callback: function (input) {
                const value = document.getElementById('value1').value.trim();
                // If value has content, then label is required
                // If value is empty, label is optional
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
              message: translations[lang].required,
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
              message: translations[lang].required,
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
              message: translations[lang].required,
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
              message: translations[lang].required,
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
              message: translations[lang].required,
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
              message: translations[lang].required,
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
        // Jump to the next step when all fields in the current step are valid
        validationStepper.next();
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

    // Portfolio / Work Items - Step 4
    let FormValidation4 = null;
    function buildStep4Validation() {
      // 1. SAFE DESTROY: Remove previous instance
      if (FormValidation4) {
        try {
          FormValidation4.destroy();
        } catch (e) {
          console.warn("Cleanup suppressed");
        }
        FormValidation4 = null;
      }

      const fields = {};
      const $cards = $('.work-item-card');

      // 2. PREPARE FIELDS
      $cards.each(function (index) {
        const $card = $(this);
        const names = {
          media: `portfolio_media_${index}`,
          title: `portfolio_title_${index}`,
          desc: `portfolio_desc_${index}`
        };

        // Apply names to DOM so FormValidation can find them
        $card.find('.media-input').attr('name', names.media);
        $card.find('.title-input').attr('name', names.title);
        $card.find('.desc-input').attr('name', names.desc);

        const isFirst = (index === 0);

        // Media Validation
        fields[names.media] = {
          validators: {
            callback: {
              message: translations[lang].media_required || "Media is required",
              callback: function (input) {
                const hasFile = input.element.files && input.element.files.length > 0;
                // First item: mandatory. Other items: mandatory only if title/desc filled.
                // if (isFirst) return hasFile;

                const $item = $(input.element).closest('.work-item-card');
                const hasText = $item.find('.title-input').val().trim() || $item.find('.desc-input').val().trim();
                return hasText ? hasFile : true;
              }
            },
            file: {
              extension: 'png,jpg,jpeg,webp,mp4,mov,avi,mkv,wmv,flv,m4v',
              maxSize: 10485760,
              message: translations[lang].invalid_media || "Invalid file"
            }
          }
        };

        // Title Validation
        fields[names.title] = {
          validators: {
            stringLength: { max: 50, message: translations[lang].title_max },
            callback: {
              message: translations[lang].title_required,
              callback: function (input) {
                const val = input.value.trim();
                if (isFirst) return val !== '';

                const $item = $(input.element).closest('.work-item-card');
                const hasOther = $item.find('.desc-input').val().trim() || ($item.find('.media-input')[0].files.length > 0);
                return (hasOther && !val) ? false : true;
              }
            }
          }
        };

        // Description Validation
        fields[names.desc] = {
          validators: {
            stringLength: { max: 100, message: translations[lang].desc_max },
            callback: {
              message: translations[lang].desc_required,
              callback: function (input) {
                const val = input.value.trim();
                // if (isFirst) return val !== '';

                const $item = $(input.element).closest('.work-item-card');
                const hasOther = $item.find('.title-input').val().trim() || ($item.find('.media-input')[0].files.length > 0);
                return (hasOther && !val) ? false : true;
              }
            }
          }
        };
      });

      // 3. GLOBAL ERROR CONTAINER (For "At least one item" message)
      // Ensure this row is VISIBLE so the error message is seen
      if ($('#_portfolio_at_least_one').length === 0) {
        $('<div id="portfolio-error-container" class="mb-3"></div>')
          .append($('<input type="hidden" id="_portfolio_at_least_one" name="_portfolio_at_least_one">'))
          .appendTo('#itemsContainer'); // Append to the end of your list
      }

      fields['_portfolio_at_least_one'] = {
        validators: {
          callback: {
            message: translations[lang].at_least_one_item,
            callback: function () {
              let isValid = false;
              $('.work-item-card').each(function () {
                if ($(this).find('.title-input').val().trim() && $(this).find('.media-input')[0].files.length > 0) {
                  isValid = true; return false;
                }
              });
              return isValid;
            }
          }
        }
      };

      // 4. INITIALIZE
      FormValidation4 = FormValidation.formValidation(wizardValidationFormStep4, {
        fields: fields,
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: function (field) {
              if (field === '_portfolio_at_least_one') return '#portfolio-error-container';
              return '.mb-4, .col-md-4, .col-md-8';
            }
          }),
          autoFocus: new FormValidation.plugins.AutoFocus()
        }
      }).on('core.form.valid', function () {
        validationStepper.next();
      });
    }

    // Initialize step 4 validation on page load
    // Wait for the portfolio items to be ready
    setTimeout(function () {
      buildStep4Validation();
    }, 100);

    // Expose function to rebuild validation when items are added/removed
    window.rebuildPortfolioValidation = buildStep4Validation;

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

        case 3: // Portfolio Step
          if (FormValidation4) {
            FormValidation4.validate();
            // The UI will now show errors for the first item because our callback
            // now returns 'false' if the first item is empty.
          } else {
            validationStepper.next();
          }
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
