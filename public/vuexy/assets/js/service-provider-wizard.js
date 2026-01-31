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
      phone: "Should contain numbers only",
      invalidPhone: "Should be a valid number",
      invalid_url: "The URL is not valid",
      email: "E-mail should be a valid email address",
      media_required: "Media is required",
      invalid_media: "Invalid file or size too large",
      title_required: "Title is required",
      title_max: "Title must be less than 50 chars",
      desc_required: "Description is required",
      desc_max: "Description must be less than 100 chars",
      at_least_one_item: "Please add at least one complete work item",
      save_and_next: "Save & Next",
      submit: "Submit",
      saving: "Saving...",
      submitting: "Submitting...",
      stat_value_required: "Value is required when a label is provided",
      stat_label_required: "Label is required when a value is provided",
    },
    ar: {
      required: "هذا الحقل مطلوب",
      logo: "غير مدعوم. قم بتحميل PNG, JPG, JPEG, WEBP أو SVG | حجم الشعار لا يجب أن يتجاوز 5 MB",
      logoSize: "حجم الشعار لا يجب أن يتجاوز 5 ميغابايت",
      hero_image: "غير مدعوم. قم بتحميل PNG, JPG, JPEG, WEBP أو SVG | حجم الصورة لا يجب أن يتجاوز 10 MB",
      phone: "يجب أن يحتوي على أرقام فقط",
      invalidPhone: "يجب أن يكون رقم صالح",
      invalid_url: "الرابط غير صالح",
      email: "البريد الإلكتروني يجب أن يكون عنوان بريد إلكتروني صالح",
      media_required: "الملف مطلوب",
      invalid_media: "ملف غير صالح أو حجم كبير جداً",
      title_required: "العنوان مطلوب",
      title_max: "العنوان يجب أن يكون أقل من 50 حرف",
      desc_required: "الوصف مطلوب",
      desc_max: "الوصف يجب أن يكون أقل من 100 حرف",
      at_least_one_item: "يرجى إضافة عمل واحد على الأقل",
      save_and_next: "حفظ & التالي",
      submit: "إرسال",
      saving: "جاري الحفظ...",
      submitting: "جاري الإرسال...",
      stat_value_required: "القيمة مطلوبه عند إدخال عنوان",
      stat_label_required: "العنوان مطلوب عند إدخال قيمة",
    }
  };

  let iti; // Declare iti in a scope accessible by custom validator
  let step1InitialData = null;
  let step2InitialData = null;

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

    // After page ready
    document.addEventListener('DOMContentLoaded', function () {
      console.log('page loaded - init wizard');
      step1InitialData = getStep1Data();
      step2InitialData = getStep2Data();
    });

    // Profile basics
    const FormValidation1 = FormValidation.formValidation(wizardValidationFormStep1, {
      fields: {
        company_logo: {
          validators: {
            callback: {
              message: translations[lang].required,
              callback: function (input) {
                if (input.value) {
                  return true;
                }
                // Check if an existing image is present
                const preview = document.getElementById('logo-preview-image');
                if (preview && preview.getAttribute('src') && !preview.classList.contains('d-none')) {
                  return true;
                }

                return false;
              }
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

                // **NEW: Prevent users from typing + or country codes**
                // The country code should ONLY come from the dropdown
                if (value.includes('+')) {
                  return {
                    valid: false,
                    message: translations[lang].phone // "Phone number should contain numbers only"
                  };
                }

                // Check if contains only numbers and allowed phone characters (NO PLUS)
                const numericRegex = /^[\d\s\-\(\)]+$/;
                if (!numericRegex.test(value)) {
                  return {
                    valid: false,
                    message: translations[lang].phone
                  };
                }

                // Get ITI instance from the element
                const itiInstance = $(input.element).data('iti');

                if (!itiInstance) {
                  // Fallback if iti not available - just check format
                  const phoneRegex = /^[\d\s\-\(\)]{8,}$/;
                  return {
                    valid: phoneRegex.test(value),
                    message: translations[lang].invalidPhone
                  };
                }

                // Get selected country
                const countryData = itiInstance.getSelectedCountryData();
                const selectedCountry = countryData.iso2 || '';

                // For Egypt, allow numbers starting with 0
                if (selectedCountry.toLowerCase() === 'eg') {
                  const cleanNumber = value.replace(/[\s\-\(\)]/g, '');

                  if (cleanNumber.startsWith('0')) {
                    if (cleanNumber.length === 11 && /^0(10|11|12|15|2)\d{8}$/.test(cleanNumber)) {
                      return { valid: true };
                    } else {
                      return {
                        valid: false,
                        message: translations[lang].invalidPhone
                      };
                    }
                  } else if (cleanNumber.length === 10 && /^(10|11|12|15|2)\d{8}$/.test(cleanNumber)) {
                    return { valid: true };
                  } else {
                    return {
                      valid: false,
                      message: translations[lang].invalidPhone
                    };
                  }
                }

                // Validate using intl-tel-input for other countries
                if (typeof itiInstance.isValidNumber === 'function') {
                  const isValid = itiInstance.isValidNumber();

                  if (!isValid) {
                    return {
                      valid: false,
                      message: translations[lang].invalidPhone
                    };
                  }

                  return { valid: true };
                }

                // Basic fallback
                const phoneRegex = /^[\d\s\-\(\)]{8,}$/;
                return {
                  valid: phoneRegex.test(value),
                  message: translations[lang].invalidPhone
                };
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

                // **NEW: Prevent users from typing + or country codes**
                // The country code should ONLY come from the dropdown
                if (value.includes('+')) {
                  return {
                    valid: false,
                    message: translations[lang].phone // "Phone number should contain numbers only"
                  };
                }

                // Check if contains only numbers and allowed phone characters (NO PLUS)
                const numericRegex = /^[\d\s\-\(\)]+$/;
                if (!numericRegex.test(value)) {
                  return {
                    valid: false,
                    message: translations[lang].phone
                  };
                }

                // Get ITI instance from the element
                const itiInstance = $(input.element).data('iti');

                if (!itiInstance) {
                  // Fallback if iti not available - just check format
                  const phoneRegex = /^[\d\s\-\(\)]{8,}$/;
                  return {
                    valid: phoneRegex.test(value),
                    message: translations[lang].invalidPhone
                  };
                }

                // Get selected country
                const countryData = itiInstance.getSelectedCountryData();
                const selectedCountry = countryData.iso2 || '';

                // For Egypt, allow numbers starting with 0
                if (selectedCountry.toLowerCase() === 'eg') {
                  const cleanNumber = value.replace(/[\s\-\(\)]/g, '');

                  if (cleanNumber.startsWith('0')) {
                    if (cleanNumber.length === 11 && /^0(10|11|12|15|2)\d{8}$/.test(cleanNumber)) {
                      return { valid: true };
                    } else {
                      return {
                        valid: false,
                        message: translations[lang].invalidPhone
                      };
                    }
                  } else if (cleanNumber.length === 10 && /^(10|11|12|15|2)\d{8}$/.test(cleanNumber)) {
                    return { valid: true };
                  } else {
                    return {
                      valid: false,
                      message: translations[lang].invalidPhone
                    };
                  }
                }

                // Validate using intl-tel-input for other countries
                if (typeof itiInstance.isValidNumber === 'function') {
                  const isValid = itiInstance.isValidNumber();

                  if (!isValid) {
                    return {
                      valid: false,
                      message: translations[lang].invalidPhone
                    };
                  }

                  return { valid: true };
                }

                // Basic fallback
                const phoneRegex = /^[\d\s\-\(\)]{8,}$/;
                return {
                  valid: phoneRegex.test(value),
                  message: translations[lang].invalidPhone
                };
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
      const currentData = getStep1Data();

      // If no changes → skip AJAX
      if (step1InitialData === currentData) {
        validationStepper.next();
        return;
      }

      // Otherwise save
      saveStepData(0, FormValidation1);
    });

    function getStep1Data() {
      const form = document.getElementById('service-provider-form-wizard');
      const formData = new FormData(form);

      const data = {};

      const fields = [
        'company_name',
        'contact_name',
        'email',
        'phone',
        'whatsapp',
        'country',
        'state',
        'city',
        'about',
        'zip',
        'address',
      ];

      fields.forEach(name => {
        data[name] = formData.get(name) || '';
      });

      // Handle company_logo separately
      const logoInput = form.querySelector('input[name="company_logo"]');
      if (logoInput && logoInput.files && logoInput.files.length > 0) {
        data.company_logo = logoInput.files[0].name; // compare by file name
      } else {
        // fallback to existing preview image (if editing existing record)
        const preview = document.getElementById('logo-preview-image');
        data.company_logo = preview && preview.getAttribute('src')
          ? preview.getAttribute('src')
          : '';
      }

      return JSON.stringify(data);
    }


    // Phone intl-tel-input
    if (intlTelInput.length) {
      intlTelInput.each(function () {
        var $this = $(this);
        var inputId = $this.attr('id');

        // $this.wrap('<div class="position-relative"></div>');

        if (typeof window.intlTelInput === 'function') {
          var itiInstance = window.intlTelInput($this[0], {
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

          // Store instance on the element for validation access
          $this[0].iti = itiInstance;

          // Assign to global iti if it's the phone input (legacy support for existing validation code)
          if (inputId === 'phone') {
            iti = itiInstance;
          }

          // Function to update hidden input
          var updateHiddenInput = function () {
            var countryData = itiInstance.getSelectedCountryData();
            var dialCode = countryData.dialCode;

            if (inputId === 'phone') {
              $('#country_code').val(dialCode);
            } else if (inputId === 'whatsapp') {
              $('#country_code_w_a').val(dialCode);
            }
          };

          // Update on init (if empty)
          if (inputId === 'phone' && !$('#country_code').val()) {
            updateHiddenInput();
          } else if (inputId === 'whatsapp' && !$('#country_code_w_a').val()) {
            updateHiddenInput();
          }

          // Update on change
          $this.on('countrychange', updateHiddenInput);
        }
      });
    }

    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>');
      $this.select2({
        dropdownParent: $this.parent()
      });
      $this.on('change', function () {
        const stepIndex = validationStepper._currentIndex;
        if (stepIndex === 0) FormValidation1.revalidateField($this.attr('name'));
        if (stepIndex === 1) FormValidation2.revalidateField($this.attr('name'));
      });
    });

    // Intro & booking
    const FormValidation2 = FormValidation.formValidation(wizardValidationFormStep2, {
      fields: {
        hero_image: {
          validators: {
            callback: {
              message: translations[lang].required,
              callback: function (input) {
                if (input.value) return true;
                const preview = wizardValidationFormStep2.querySelector('img');
                return (preview && preview.getAttribute('src')) ? true : false;
              }
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
        services_short_description: {
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
        },
        operating_mode: {
          validators: {
            notEmpty: {
              message: translations[lang].required,
            }
          }
        },
        operating_region: {
          validators: {
            notEmpty: {
              message: translations[lang].required,
            }
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
      const currentData = getStep2Data();
      console.log('current step 2 data', currentData);

      // If no changes → skip AJAX
      if (step2InitialData === currentData) {
        validationStepper.next();
        return;
      }

      saveStepData(1, FormValidation2);
    });

    function getStep2Data() {
      const form = document.getElementById('service-provider-form-wizard');
      const formData = new FormData(form);

      const data = {};

      // Normal fields
      [
        'hero_description',
        'services_short_description',
        'appointment_link',
        'operating_mode',
        'operating_region',
      ].forEach(name => {
        data[name] = formData.get(name) || '';
      });

      // Handle hero_image separately
      const heroInput = form.querySelector('input[name="hero_image"]');

      if (heroInput && heroInput.files && heroInput.files.length > 0) {
        const file = heroInput.files[0];

        // More reliable than just name
        data.hero_image = `${file.name}_${file.size}`;
      } else {
        // If editing and image already exists in DB
        const existingImage = document.querySelector('#step-2-booking img');
        data.hero_image = existingImage
          ? existingImage.getAttribute('src')
          : '';
      }

      return JSON.stringify(data);
    }


    // Key stats
    const FormValidation3 = FormValidation.formValidation(wizardValidationFormStep3, {
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

    validationStepper._element.addEventListener('shown.bs-stepper', function (event) {
      const $nextBtn = $('.btn-next');
      const $text = $nextBtn.find('span');
      const $icon = $nextBtn.find('i');

      const isFirstStep = event.detail.indexStep === 0;

      // Disable Previous button on first step
      $('.btn-prev').css('visibility', isFirstStep ? 'hidden' : 'visible');

      const isLastStep = event.detail.indexStep === 4;
      const newText = isLastStep
        ? translations[lang].submit
        : translations[lang].save_and_next;

      // Change text only if needed
      if ($text.text().trim() !== newText) {
        $text.text(newText);
      }

      // Toggle button color only if needed
      $nextBtn.toggleClass('btn-success', isLastStep);
      $nextBtn.toggleClass('btn-primary', !isLastStep);

      // Toggle icon visibility
      $icon.toggle(!isLastStep);
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

        case 3: // Portfolio Step
          if (FormValidation4) {
            FormValidation4.validate();
            // The UI will now show errors for the first item because our callback
            // now returns 'false' if the first item is empty.
          } else {
            validationStepper.next();
          }
          break;

        case 4: // Final Step (Step 5) - Submit the form
          submitFormData();
          break;

        default:
          break;
      }
    });

    // Add this function to handle the AJAX submission for each step
    function saveStepData(stepIndex, formValidationInstance) {
      const form = document.getElementById('service-provider-form-wizard');
      const formData = new FormData(form);
      const route = form.getAttribute(`data-route-step-${stepIndex + 1}`);

      if (!route) {
        validationStepper.next();
        return;
      }

      // Show loading state
      const nextBtn = document.querySelector('.btn-next');
      const originalText = nextBtn.querySelector('span').textContent;
      const originalIcon = nextBtn.querySelector('i');

      nextBtn.disabled = true;
      nextBtn.querySelector('span').textContent = translations[lang].saving;
      if (originalIcon) originalIcon.style.display = 'none';

      // Add spinner
      const spinner = document.createElement('span');
      spinner.className = 'spinner-border spinner-border-sm me-2';
      spinner.setAttribute('role', 'status');
      nextBtn.prepend(spinner);

      $.ajax({
        url: route,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        success: function (response) {
          spinner.remove();
          nextBtn.disabled = false;
          nextBtn.querySelector('span').textContent = originalText;
          if (originalIcon) originalIcon.style.display = '';

          if (response.success) {
            validationStepper.next();
          } else {
            releaseFailureAlert({
              title: lang === 'ar' ? 'خطأ!' : 'Error!',
              message: response.message || (lang === 'ar' ? 'حدث خطأ' : 'An error occurred'),
            });
          }
        },
        error: function (xhr) {
          spinner.remove();
          nextBtn.disabled = false;
          nextBtn.querySelector('span').textContent = originalText;
          if (originalIcon) originalIcon.style.display = '';

          let errorMessage = lang === 'ar' ? 'حدث خطأ أثناء الحفظ' : 'An error occurred while saving';
          if (xhr.status === 422 && xhr.responseJSON?.errors) {
            const errors = xhr.responseJSON.errors;
            errorMessage = Object.values(errors).flat().join('<br>');
          }

          releaseFailureAlert({
            title: lang === 'ar' ? 'خطأ!' : 'Error!',
            message: errorMessage,
          });
        }
      });
    }

    // Add this function to handle the AJAX submission
    function submitFormData() {
      const form = document.getElementById('service-provider-form-wizard');
      const formData = new FormData(form);

      // Show loading state
      const submitBtn = document.querySelector('.btn-next');
      const originalText = submitBtn.querySelector('span').textContent;
      const originalIcon = submitBtn.querySelector('i');

      submitBtn.disabled = true;
      submitBtn.querySelector('span').textContent = translations[lang].submitting;
      if (originalIcon) originalIcon.style.display = 'none';

      // Add spinner
      const spinner = document.createElement('span');
      spinner.className = 'spinner-border spinner-border-sm me-2';
      spinner.setAttribute('role', 'status');
      submitBtn.prepend(spinner);

      // Make AJAX request
      $.ajax({
        url: form.getAttribute('action') || window.location.href, // Use form action or current URL
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        success: function (response) {
          // Handle success
          console.log('Success:', response);

          // Remove spinner
          spinner.remove();

          releaseSuccessAlert({
            title: lang === 'ar' ? 'نجح!' : 'Success!',
            message: response.message || (lang === 'ar' ? 'تم إرسال النموذج بنجاح' : 'Form submitted successfully'),
          });

          // Redirect or reset form
          if (response.redirect) {
            window.location.href = response.redirect;
          } else {
            // Default redirect for profile
            window.location.href = route('user.profile_view');
          }
        },
        error: function (xhr, status, error) {
          // Handle error
          console.error('Error:', error);

          // Remove spinner
          spinner.remove();

          let errorMessage = lang === 'ar' ? 'حدث خطأ أثناء الإرسال' : 'An error occurred during submission';

          // Handle validation errors
          if (xhr.status === 422 && xhr.responseJSON?.errors) {
            const errors = xhr.responseJSON.errors;
            const errorList = Object.values(errors).flat().join('<br>');
            errorMessage = errorList;
          } else if (xhr.responseJSON?.message) {
            errorMessage = xhr.responseJSON.message;
          }

          // Show error message
          releaseFailureAlert({
            title: lang === 'ar' ? 'خطأ!' : 'Error!',
            message: errorMessage,
          });

          // Re-enable button
          submitBtn.disabled = false;
          submitBtn.querySelector('span').textContent = originalText;
          if (originalIcon) originalIcon.style.display = '';
        }
      });
    }

    const wizardValidationPrevButton = wizardValidation.querySelector('.btn-prev');
    wizardValidationPrevButton.addEventListener('click', event => {
      validationStepper.previous();
      step1InitialData = getStep1Data();
      step2InitialData = getStep2Data();
    });
  }
})();
