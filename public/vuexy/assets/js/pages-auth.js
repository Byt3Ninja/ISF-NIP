/**
 *  Pages Auth
 */

'use strict';

const translations = {
  en: {
    first_name: {
      notEmpty: 'Please enter first name',
      stringOnly: 'This field letters letters only.',
      stringLength: "First name must not excceed 50 characters.",
    },
    last_name: {
      notEmpty: 'Please enter last name',
      stringOnly: 'This field letters letters only.',
      stringLength: "Last name must not excceed 50 characters.",
    },
    phone: {
      notEmpty: 'Please enter your phone number',
      phone: 'Phone number should contain numbers only',
      invalid: 'Invalid phone number'
    },
    email: {
      notEmpty: 'Please enter your email',
      emailAddress: 'Please enter valid email address'
    },
    password: {
      notEmpty: 'Please enter your password',
      stringLength: 'Password must be 8-20 characters long',
      strong: "Password must be strong including uppercase, lowercase, numbers, and special characters"
    },
    password_confirmation: {
      notEmpty: 'Please confirm password',
      identical: 'The password and its confirm are not the same',
      stringLength: 'Password must be 8-20 characters long',
      strong: "Password must be strong including uppercase, lowercase, numbers, and special characters"
    },
    terms: {
      notEmpty: 'Please agree terms & conditions'
    },
    gender: {
      notEmpty: 'Please select gender'
    },
    jobTitle: {
      notEmpty: 'Please enter job title',
      enOnly: 'Job title accepts letters only'
    },
    expertise: {
      notEmpty: 'Please select expertise'
    },
    bio: {
      notEmpty: 'Please enter bio'
    },
    linkedin: {
      notEmpty: 'Please enter LinkedIn URL',
      uri: 'Please enter valid linkedin URL'
    }
    ,
    company_name: {
      notEmpty: 'Please enter company name',
      stringLength: "Company name must not excceed 50 characters.",
    },
    contact_name: {
      notEmpty: 'Please enter contact name',
      stringLength: "Contact name must not excceed 50 characters.",
    }
  },
  ar: {
    first_name: {
      notEmpty: 'يرجى إدخال الاسم الأول',
      stringOnly: 'هذا الحقل يقبل الحروف فقط',
      stringLength: "الاسم الأول يجب ان يكون لا يزيد عن 50 حرفًا",
    },
    last_name: {
      notEmpty: 'يرجى إدخال الاسم الأخير',
      stringOnly: 'هذا الحقل يقبل الحروف فقط',
      stringLength: "الاسم الأخير يجب ان يكون لا يزيد عن 50 حرفًا",
    },
    phone: {
      notEmpty: 'يرجى إدخال رقم الهاتف',
      phone: 'يجب أن يتكون الهاتف من أرقام فقط',
      invalid: 'رقم هاتف غير صحيح'
    },
    email: {
      notEmpty: 'يرجى إدخال بريدك الإلكتروني',
      emailAddress: 'يرجى إدخال عنوان بريد إلكتروني صالح'
    },
    password: {
      notEmpty: 'يرجى إدخال كلمة المرور الخاصة بك',
      stringLength: 'يجب ان تتكون كلمة المرور من 8-20 حرفًا',
      strong: "كلمة السر يجب أن تكون قوية حيث تتضمن حروفًا كبيرة وصغيرة وأرقامًا ورموزًا",
    },
    password_confirmation: {
      notEmpty: 'يرجى تأكيد كلمة المرور',
      identical: 'كلمة المرور وتأكيدها غير متطابقين',
      stringLength: 'يجب ان تتكون كلمة المرور من 8-20 حرفًا',
      strong: "كلمة السر يجب أن تكون قوية حيث تتضمن حروفًا كبيرة وصغيرة وأرقامًا ورموزًا",
    },
    terms: {
      notEmpty: 'يرجى الموافقة على الشروط والأحكام'
    },
    gender: {
      notEmpty: 'يرجى تحديد النوع'
    },
    jobTitle: {
      notEmpty: 'يرجى ادخال التخصص',
      enOnly: 'الرجاء إدخال الوظيفة'
    },
    expertise: {
      notEmpty: 'يرجى تحديد الخبرة'
    },
    bio: {
      notEmpty: 'يرجى إدخال السيرة الذاتية'
    },
    linkedin: {
      notEmpty: 'يرجى إدخال رابط لينكد ان',
      uri: 'يرجى ادخال رابط  لينكد ان صحيح'
    }
    ,
    company_name: {
      notEmpty: 'يرجى إدخال اسم الشركة',
      stringLength: "اسم الشركة يجب ان يكون لا يزيد عن 50 حرفًا",
    },
    contact_name: {
      notEmpty: 'يرجى إدخال اسم جهة الاتصال',
      stringLength: "اسم جهة الاتصال يجب ان يكون لا يزيد عن 50 حرفًا",
    }
  }
};

const lang = document.documentElement.lang || 'en';
let iti; // Declare iti in a scope accessible by custom validator

function getValidationConfig(lang, isMentor, isServiceProvider) {
  const config = {
    fields: {
      first_name: {
        validators: {
          notEmpty: {
            message: translations[lang].first_name.notEmpty
          },
          stringLength: {
            max: 50,
            message: translations[lang].first_name.stringLength
          },
        }
      },
      last_name: {
        validators: {
          notEmpty: {
            message: translations[lang].last_name.notEmpty
          },
          stringLength: {
            max: 50,
            message: translations[lang].last_name.stringLength
          },
        }
      },
      phone: {
        validators: {
          notEmpty: {
            message: translations[lang].phone.notEmpty
          },
          callback: {
            message: translations[lang].phone.invalid,
            callback: function(input) {
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
                  message: translations[lang].phone.phone
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
                      message: translations[lang].phone.invalid
                    };
                  }
                } else if (cleanNumber.length === 10 && /^(10|11|12|15|2)\d{8}$/.test(cleanNumber)) {
                  // 10 digits without leading 0
                  return { valid: true };
                } else {
                  return {
                    valid: false,
                    message: translations[lang].phone.invalid
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
                    let message = translations[lang].phone.invalid;
                    
                    // Check if intlTelInputUtils is available
                    if (window.intlTelInputUtils && window.intlTelInputUtils.validationError) {
                      switch (error) {
                        case window.intlTelInputUtils.validationError.INVALID_COUNTRY_CODE:
                          message = translations[lang].phone.invalid;
                          break;
                        case window.intlTelInputUtils.validationError.TOO_SHORT:
                          message = translations[lang].phone.invalid;
                          break;
                        case window.intlTelInputUtils.validationError.TOO_LONG:
                          message = translations[lang].phone.invalid;
                          break;
                        case window.intlTelInputUtils.validationError.NOT_A_NUMBER:
                          message = translations[lang].phone.phone;
                          break;
                        default:
                          message = translations[lang].phone.invalid;
                      }
                    }
                    
                    return {
                      valid: false,
                      message: message
                    };
                  }
                  
                  return {
                    valid: false,
                    message: translations[lang].phone.invalid
                  };
                }
                
                return { valid: true };
              }
              
              // If iti is not available, do basic validation
              const phoneRegex = /^\+?[\d\s\-\(\)]{8,}$/;
              if (!phoneRegex.test(value)) {
                return {
                  valid: false,
                  message: translations[lang].phone.invalid
                };
              }
              
              return { valid: true };
            }
          }
        }
      },
      email: {
        validators: {
          notEmpty: {
            message: translations[lang].email.notEmpty
          },
          regexp: {
            regexp: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i,
            message: translations[lang].email.emailAddress
          }
        }
      },
      password: {
        validators: {
          notEmpty: {
            message: translations[lang].password.notEmpty
          },
          stringLength: {
            min: 8,
            max: 20,
            message: translations[lang].password.stringLength
          },
          regexp: {
            regexp: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$/,
            message: translations[lang].password.strong
          }
        }
      },
      password_confirmation: {
        validators: {
          notEmpty: {
            message: translations[lang].password_confirmation.notEmpty
          },
          identical: {
            compare: function () {
              return document.getElementById('registerForm').querySelector('[name="password"]').value;
            },
            message: translations[lang].password_confirmation.identical
          },
          stringLength: {
            min: 8,
            max: 20,
            message: translations[lang].password_confirmation.stringLength
          },
          regexp: {
            regexp: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$/,
            message: translations[lang].password_confirmation.strong
          }
        }
      },
      terms: {
        validators: {
          notEmpty: {
            message: translations[lang].terms.notEmpty
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

  if (isMentor || window.location.href.includes("MentorRole")) {
    config.fields.title_en = {
      validators: {
        notEmpty: {
          message: translations[lang].jobTitle.notEmpty
        },
        regexp: {
          regexp: /^[a-zA-Z\s]*$/i,
          message: translations[lang].jobTitle.enOnly
        }
      }
    };
    config.fields['expertise[]'] = {
      validators: {
        notEmpty: {
          message: translations[lang].expertise.notEmpty
        }
      }
    };
    config.fields.bio_en = {
      validators: {
        notEmpty: {
          message: translations[lang].bio.notEmpty
        }
      }
    };
    config.fields.Linkedin = {
      validators: {
        notEmpty: {
          message: translations[lang].linkedin.notEmpty
        },
        regexp: {
          regexp: /^https?:\/\/(www\.)?linkedin\.com\/in\/[a-zA-Z0-9-_\u00C0-\u017F]+\/?$/,
          message: translations[lang].linkedin.uri
        }
      }
    };
  }

  if (isServiceProvider) {
    config.fields.first_name = {};
    config.fields.last_name = {};
    config.fields.company_name = {
      validators: {
        notEmpty: { message: translations[lang].company_name.notEmpty },
        stringLength: {
          max: 50,
          message: translations[lang].company_name.stringLength
        },
      }
    };
    config.fields.contact_name = {
      validators: {
        notEmpty: { message: translations[lang].contact_name.notEmpty },
        stringLength: {
          max: 50,
          message: translations[lang].contact_name.stringLength
        },
      }
    };
  }


  return config;
}

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    const registerForm = document.getElementById('registerForm');
    const mentorInputsWrapper = document.getElementById('mentorInputsWrapper');
    const serviceProviderInputsWrapper = document.getElementById('serviceProviderInputsWrapper');
    const mentorRoleRadio = document.getElementById('MentorRole');
    const founderRoleRadio = document.getElementById('FounderRole');
    const serviceProviderRole = registerForm.querySelector('input[name="role"][value="Service Provider"]');
    const phoneInput = document.getElementById("phone");
    const countryCodeInput = document.querySelector("#phone_code");
    
    // Initialize the phone input
    if (phoneInput) {
      iti = intlTelInput(phoneInput, {
        initialCountry: 'eg',
        preferredCountries: ["eg", "ae", "sa", "ma", "dz", "tn"],
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        formatOnDisplay: false,
        separateDialCode: true,
        autoPlaceholder: "aggressive",
        customPlaceholder: function(selectedCountryPlaceholder) {
          return "e.g. " + selectedCountryPlaceholder;
        }
      });

      // Set initial country code
      if (countryCodeInput) {
        const initialCountryData = iti.getSelectedCountryData();
        if (initialCountryData && initialCountryData.dialCode) {
          countryCodeInput.value = initialCountryData.dialCode;
        }
      }

      // Update country code when country changes
      phoneInput.addEventListener('countrychange', function() {
        const countryData = iti.getSelectedCountryData();
        if (countryCodeInput && countryData && countryData.dialCode) {
          countryCodeInput.value = countryData.dialCode;
        }
        
        // Revalidate phone field when country changes
        if (validationInstance) {
          validationInstance.revalidateField('phone');
        }
      });
    }

    let validationInstance;
    let validationConfig = getValidationConfig(lang, false);

    function initializeValidation(config) {
      if (validationInstance) {
        validationInstance.destroy();
      }
      
      validationInstance = FormValidation.formValidation(registerForm, config);

      // Add event listeners for phone input
      if (phoneInput) {
        phoneInput.addEventListener('input', function() {
          if (validationInstance) {
            validationInstance.revalidateField('phone');
          }
        });
        
        phoneInput.addEventListener('blur', function() {
          if (validationInstance) {
            validationInstance.revalidateField('phone');
          }
        });
      }
    }

    if (mentorRoleRadio) {
      mentorRoleRadio.addEventListener('change', function() {
        if (mentorRoleRadio.checked) {
          validationConfig = getValidationConfig(lang, true);
          initializeValidation(validationConfig);
          if (mentorInputsWrapper) {
            mentorInputsWrapper.style.display = 'block';
          }
          
          // Setup expertise field
          const expertiseField = registerForm.querySelector('[name="expertise[]"]');
          if (expertiseField && typeof $ !== 'undefined' && $.fn.select2) {
            $(expertiseField).select2();
            $(expertiseField).on('change', function() {
              if (validationInstance) {
                validationInstance.updateFieldStatus('expertise[]', 'NotValidated');
              }
            });
          }
        }
      });
    }

    if (registerForm && !registerForm.hasAttribute('data-validation-initialized')) {
      registerForm.setAttribute('data-validation-initialized', 'true');
      
      // Wait a bit for intl-tel-input to fully initialize
      setTimeout(function() {
        initializeValidation(validationConfig);
      }, 100);
    }

    if (founderRoleRadio) {
      founderRoleRadio.addEventListener('change', function() {
        if (founderRoleRadio.checked) {
          validationConfig = getValidationConfig(lang, false);
          if (mentorInputsWrapper) {
            mentorInputsWrapper.style.display = 'none';
          }
          initializeValidation(validationConfig);
        }
      });
    }

    if (serviceProviderRole) {
      serviceProviderRole.addEventListener("change", function() {
        if (serviceProviderRole.checked) {
           validationConfig = getValidationConfig(lang, false, true);
          // if (mentorInputsWrapper) {
          //   mentorInputsWrapper.style.display = 'none';
          // }
          initializeValidation(validationConfig);
        }
      })
    }

    // Two Steps Verification
    const numeralMask = document.querySelectorAll('.numeral-mask');

    // Verification masking
    if (numeralMask.length) {
      numeralMask.forEach(function(e) {
        new Cleave(e, {
          numeral: true
        });
      });
    }
  })();
});