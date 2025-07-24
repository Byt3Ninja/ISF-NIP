'use strict';

const profileTranslations = {
  en: {
    first_name_en: {
      notEmpty: 'Please enter first name',
      stringOnly: 'This field accepts letters only.',
      stringLength: 'First name should be between 2 and 50 characters'
    },
    last_name_en: {
      notEmpty: 'Please enter last name',
      stringOnly: 'This field accepts letters only.',
      stringLength: 'Last name should be between 2 and 50 characters'
    },
    phone: {
      notEmpty: 'Please enter your phone',
      phone: 'Phone should be consist of numbers only'
    },
    email: {
      notEmpty: 'Please enter your email',
      emailAddress: 'Please enter valid email address'
    },
    title_en: {
      notEmpty: 'Please enter job title',
      stringOnly: 'Job title accepts letters only'
    },
    zip_code: {
      zipCode: 'Please enter valid zip code'
    },
    address: {
      stringLength: 'Address should be between 2 and 100 characters'
    }
  },
  ar: {
    first_name_en: {
      notEmpty: 'يرجى إدخال الاسم الأول',
      stringOnly: 'هذا الحقل يقبل الحروف فقط',
      stringLength: 'يجب أن يكون الاسم الأول بين 2 و 50 حرفًا'
    },
    last_name_en: {
      notEmpty: 'يرجى إدخال الاسم الأخير',
      stringOnly: 'هذا الحقل يقبل الحروف فقط',
      stringLength: 'يجب أن يكون الاسم الأخير بين 2 و 50 حرفًا'
    },
    phone: {
      notEmpty: 'يرجى إدخال رقم الهاتف',
      phone: 'يجب أن يتكون الهاتف من أرقام فقط',
    },
    email: {
      notEmpty: 'يرجى إدخال بريدك الإلكتروني',
      emailAddress: 'يرجى إدخال عنوان بريد إلكتروني صالح'
    },
    title_en: {
      notEmpty: 'يرجى ادخال المسمى الوظيفي',
      stringOnly: 'هذا الحقل يقبل الحروف فقط'
    },
    zip_code: {
      zipCode: 'يرجى إدخال الرمز البريدي الصحيح'
    },
    address: {
      stringLength: 'يجب أن يكون العنوان بين 2 و 100 حرفًا'
    }
  },
};

document.addEventListener('DOMContentLoaded', function () {
  const config = {
    fields: {
      first_name_en: {
        validators: {
          notEmpty: {
            message: profileTranslations[lang].first_name_en.notEmpty
          },
          regexp: {
            regexp: /^[\p{L}\s]+$/u,
            message: profileTranslations[lang].first_name_en.stringOnly
          },
          stringLength: {
            min: 2,
            max: 50,
            message: profileTranslations[lang].first_name_en.stringLength
          },
        }
      },
      last_name_en: {
        validators: {
          notEmpty: {
            message: profileTranslations[lang].last_name_en.notEmpty
          },
          regexp: {
            regexp: /^[\p{L}\s]+$/u,
            message: profileTranslations[lang].last_name_en.stringOnly
          },
          stringLength: {
            min: 2,
            max: 50,
            message: profileTranslations[lang].last_name_en.stringLength
          },
        }
      },
      phone: {
        validators: {
          notEmpty: {
            message: profileTranslations[lang].phone.notEmpty
          },
          // regexp: {
          //   regexp: /^[0-9]+$/,
          //   message: profileTranslations[lang].phone.phone
          // }
        }
      },
      email: {
        validators: {
          notEmpty: {
            message: profileTranslations[lang].email.notEmpty
          },
          regexp: {
            regexp: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i,
            message: profileTranslations[lang].email.emailAddress
          }
        }
      },
      title_en: {
        validators: {
          notEmpty: {
            message: profileTranslations[lang].title_en.notEmpty
          },
          regexp: {
            regexp: /^[\p{L}\s]+$/u,
            message: profileTranslations[lang].title_en.stringOnly
          }
        }
      },
      zip_code: {
        validators: {
          numeric: {
            message: profileTranslations[lang].zip_code.zipCode
          }
        }
      },
      address: {
        validators: {
          stringLength: {
            min: 2,
            max: 100,
            message: profileTranslations[lang].address.stringLength
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

  const accountInfoForm = document.getElementById('account-info-form');

  if (accountInfoForm) {
    const accountInfoFormValidator = FormValidation.formValidation(accountInfoForm, config);
  }
});