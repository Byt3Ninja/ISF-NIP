/**
 *  Pages Authentication
 */

'use strict';
const formAuthentication = document.querySelector('#formAuthentication');

const translations = {
  en: {
    username: {
      notEmpty: 'Please enter username',
      stringLength: 'Username must be more than 6 characters'
    },
    first_name_en: {
      notEmpty: 'Please enter first name',
      // stringLength: 'First name must be more than 6 characters'
    },
    last_name_en: {
      notEmpty: 'Please enter last name',
      // stringLength: 'Last name must be more than 6 characters'
    },
    phone: {
      notEmpty: 'Please enter your phone',
      phone: 'Please enter valid phone address'
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
      stringLength: 'Password must be more than or equal 8 characters'
    },
    password_confirmation: {
      notEmpty: 'Please confirm password',
      identical: 'The password and its confirm are not the same',
      stringLength: 'Password must be more than or equal 8 characters'
    },
    terms: {
      notEmpty: 'Please agree terms & conditions'
    }
  },
  ar: {
    username: {
      notEmpty: 'يرجى إدخال اسم المستخدم',
      stringLength: 'يجب أن يكون اسم المستخدم أكثر من 6 أحرف'
    },
    first_name_en: {
      notEmpty: 'يرجى إدخال الاسم الأول',
      // stringLength: 'يجب أن يكون الاسم الأول أكثر من 6 أحرف'
    },
    last_name_en: {
      notEmpty: 'يرجى إدخال الاسم الأخير',
      // stringLength: 'يجب أن يكون الاسم الأخير أكثر من 6 أحرف'
    },
    phone: {
      notEmpty: 'يرجى إدخال رقم الهاتف',
      phone: 'يرجى إدخال عنوان هاتف صالح'
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
      stringLength: 'يجب أن تكون كلمة المرور أكثر من او يساوي 8 أحرف'
    },
    password_confirmation: {
      notEmpty: 'يرجى تأكيد كلمة المرور',
      identical: 'كلمة المرور وتأكيدها غير متطابقين',
      stringLength: 'يجب أن تكون كلمة المرور أكثر من او يساوي 8 أحرف'
    },
    terms: {
      notEmpty: 'يرجى الموافقة على الشروط والأحكام'
    }
  }
};

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    // Form validation for Add new record
    if (formAuthentication) {
      const fv = FormValidation.formValidation(formAuthentication, {
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
          first_name_en: {
            validators: {
              notEmpty: {
                message: translations[lang].first_name_en.notEmpty
              },
              // stringLength: {
              //   min: 6,
              //   message: translations[lang].first_name_en.stringLength
              // }
            }
          },
          last_name_en: {
            validators: {
              notEmpty: {
                message: translations[lang].last_name_en.notEmpty
              },
              // stringLength: {
              //   min: 6,
              //   message: translations[lang].last_name_en.stringLength
              // }
            }
          },
          phone: {
            validators: {
              notEmpty: {
                message: translations[lang].phone.notEmpty
              },
              phone: {
                message: translations[lang].phone.phone
              }
            }
          },
          email: {
            validators: {
              notEmpty: {
                message: translations[lang].email.notEmpty
              },
              emailAddress: {
                message: translations[lang].email.emailAddress
              }
            }
          },
          'email-username': {
            validators: {
              notEmpty: {
                message: translations[lang].email_username.notEmpty
              },
              stringLength: {
                min: 6,
                message: translations[lang].email_username.stringLength
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
                message: translations[lang].password.stringLength
              }
            }
          },
          'password_confirmation': {
            validators: {
              notEmpty: {
                message: translations[lang].password_confirmation.notEmpty
              },
              identical: {
                compare: function () {
                  return formAuthentication.querySelector('[name="password"]').value;
                },
                message: translations[lang].password_confirmation.identical
              },
              stringLength: {
                min: 8,
                message: translations[lang].password_confirmation.stringLength
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
