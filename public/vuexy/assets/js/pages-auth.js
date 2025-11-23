/**
 *  Pages Auth
 */

'use strict';

// const lang = document.documentElement.lang;

const translations = {
  en: {
    username: {
      notEmpty: 'Please enter username',
      stringLength: 'Username must be more than 6 characters'
    },
    first_name: {
      notEmpty: 'Please enter first name',
      stringOnly: 'This field letters letters only.'
    },
    last_name: {
      notEmpty: 'Please enter last name',
      stringOnly: 'This field letters letters only.'
    },
    phone: {
      notEmpty: 'Please enter your phone',
      phone: 'Phone should be consist of numbers only'
    },
    email: {
      notEmpty: 'Please enter your email',
      emailAddress: 'Please enter valid email address'
    },
    email_username: {
      notEmpty: 'Please enter email / username',
      stringLength: 'Username must be more than 6 characters'
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
  },
  ar: {
    username: {
      notEmpty: 'يرجى إدخال اسم المستخدم',
      stringLength: 'يجب أن يكون اسم المستخدم أكثر من 6 أحرف'
    },
    first_name: {
      notEmpty: 'يرجى إدخال الاسم الأول',
      stringOnly: 'هذا الحقل يقبل الحروف فقط'
    },
    last_name: {
      notEmpty: 'يرجى إدخال الاسم الأخير',
      stringOnly: 'هذا الحقل يقبل الحروف فقط'
    },
    phone: {
      notEmpty: 'يرجى إدخال رقم الهاتف',
      phone: 'يجب أن يتكون الهاتف من أرقام فقط',
    },
    email: {
      notEmpty: 'يرجى إدخال بريدك الإلكتروني',
      emailAddress: 'يرجى إدخال عنوان بريد إلكتروني صالح'
    },
    email_username: {
      notEmpty: 'يرجى إدخال البريد الإلكتروني / اسم المستخدم',
      stringLength: 'يجب أن يكون اسم المستخدم أكثر من 8 أحرف'
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
  }
};

function getValidationConfig(lang, isMentor) {
  const config = {
    fields: {
      username: {
        validators: {
          notEmpty: {
            message: translations[lang].username.notEmpty
          },
          stringLength: {
            min: 6,
            message: translations[lang].username.stringLength
          }
        }
      },
      first_name: {
        validators: {
          notEmpty: {
            message: translations[lang].first_name.notEmpty
          },
          // regexp: {
          //   regexp: /^[a-zA-Z\u0621-\u064A]*$/i,
          //   message: translations[lang].first_name.stringOnly
          // }
        }
      },
      last_name: {
        validators: {
          notEmpty: {
            message: translations[lang].last_name.notEmpty
          },
          // regexp: {
          //   regexp: /^[a-zA-Z\u0621-\u064A]*$/i,
          //   message: translations[lang].last_name.stringOnly
          // }
        }
      },
      phone: {
        validators: {
          notEmpty: {
            message: translations[lang].phone.notEmpty
          },
          // regexp: {
          //   regexp: /^[0-9]+$/,
          //   message: translations[lang].phone.phone
          // }
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
          },
        }
      },
      terms: {
        validators: {
          notEmpty: {
            message: translations[lang].terms.notEmpty
          }
        }
      },
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

  if (isMentor) {
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

  return config;
}

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    const registerForm = document.getElementById('registerForm');
    const mentorInputsWrapper = document.getElementById('mentorInputsWrapper');
    const mentorRoleRadio = document.getElementById('MentorRole');
    const founderRoleRadio = document.getElementById('FounderRole');

    let validationInstance;
    let validationConfig = getValidationConfig(lang, false);

    function initializeValidation(config) {
      if (validationInstance) {
        validationInstance.destroy();
      }
      validationInstance = FormValidation.formValidation(registerForm, config);
    }

    if (mentorRoleRadio) {
      mentorRoleRadio.addEventListener('change', () => {
        if (mentorRoleRadio.checked) {
          validationConfig = getValidationConfig(lang, true);
          initializeValidation(validationConfig);
          mentorInputsWrapper.style.display = 'block';
          // Remove 'expertise[]' validation once a value is selected
          const expertiseField = registerForm.querySelector('[name="expertise[]"]');
          if (expertiseField) {
            $(expertiseField).select2();
            $(expertiseField).on('change', () => {
            validationInstance.updateFieldStatus('expertise[]', 'NotValidated');
          });
          }
        }
      });
    }

    if (registerForm && !registerForm.hasAttribute('data-validation-initialized')) {
      registerForm.setAttribute('data-validation-initialized', 'true');
      initializeValidation(validationConfig);
    }

    if (founderRoleRadio) {
      founderRoleRadio.addEventListener('change', () => {
        if (founderRoleRadio.checked) {
          validationConfig = getValidationConfig(lang, false);
          mentorInputsWrapper.style.display = 'none';
          initializeValidation(validationConfig);
        }
      });
    }

    //  Two Steps Verification
    const numeralMask = document.querySelectorAll('.numeral-mask');

    // Verification masking
    if (numeralMask.length) {
      numeralMask.forEach(e => {
        new Cleave(e, {
          numeral: true
        });
      });
    }
  })();
});
