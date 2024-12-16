/**
 *  Pages Authentication
 */

'use strict';

const lang = document.documentElement.lang;

const translations = {
  en: {
    username: {
      notEmpty: 'Please enter username',
      stringLength: 'Username must be more than 6 characters'
    },
    first_name_en: {
      notEmpty: 'Please enter first name',
      stringOnly: 'This field accepts English or Arabic letters only.'
      // stringLength: 'First name must be more than 6 characters'
    },
    last_name_en: {
      notEmpty: 'Please enter last name',
      stringOnly: 'This field accepts English or Arabic letters only.'
      // stringLength: 'Last name must be more than 6 characters'
    },
    phone: {
      notEmpty: 'Please enter your phone',
      phone: 'Please enter valid phone number'
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
      stringLength: 'Password must be more than or equal 8 characters',
      strong: 'Provide a strong password, at least 8 characters long, containing at least one lowercase, one uppercase letter, one digit and one special character'
    },
    password_confirmation: {
      notEmpty: 'Please confirm password',
      identical: 'The password and its confirm are not the same',
      stringLength: 'Password must be more than or equal 8 characters'
    },
    terms: {
      notEmpty: 'Please agree terms & conditions'
    },
    gender: {
      notEmpty: 'Please select gender'
    },
    jobTitle: {
      notEmpty: 'Please enter job title',
      enOnly: 'Please enter job title in English'
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
    first_name_en: {
      notEmpty: 'يرجى إدخال الاسم الأول',
      stringOnly: 'هذا الحقل يقبل الحروف الإنجليزية أو العربية فقط'
      // stringLength: 'يجب أن يكون الاسم الأول أكثر من 6 أحرف'
    },
    last_name_en: {
      notEmpty: 'يرجى إدخال الاسم الأخير',
      stringOnly: 'هذا الحقل يقبل الحروف الإنجليزية أو العربية فقط'
      // stringLength: 'يجب أن يكون الاسم الأخير أكثر من 6 أحرف'
    },
    phone: {
      notEmpty: 'يرجى إدخال رقم الهاتف',
      phone: 'يرجى إدخال رقم هاتف صحيح'
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
      stringLength: 'يجب أن تكون كلمة المرور أكثر من او يساوي 8 أحرف',
      strong: 'قدم كلمة مرور قوية ، تحتوي على 8 أحرف على الأقل ، تحتوي على حرف صغير واحد على الأقل ، وحرف كبير واحد على الأقل ، ورقم واحد على الأقل وحرف خاص واحد على الأقل'
    },
    password_confirmation: {
      notEmpty: 'يرجى تأكيد كلمة المرور',
      identical: 'كلمة المرور وتأكيدها غير متطابقين',
      stringLength: 'يجب أن تكون كلمة المرور أكثر من او يساوي 8 أحرف'
    },
    terms: {
      notEmpty: 'يرجى الموافقة على الشروط والأحكام'
    },
    gender: {
      notEmpty: 'يرجى تحديد النوع'
    },
    jobTitle: {
      notEmpty: 'يرجى ادخال التخصص',
      enOnly: 'الرجاء إدخال الوظيفة باللغة الإنجليزية'
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

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    const founderForm = document.querySelector('#founderForm');
    const mentorForm = document.querySelector('#mentorForm');

    const mentorRoleRadio = document.getElementById('MentorRole');
    const founderRoleRadio = document.getElementById('FounderRole');

    console.log('mentorRoleRadio', mentorRoleRadio);
    console.log('founderRoleRadio', founderRoleRadio);

    console.log('mentorForm', mentorForm);
    console.log('founderForm', founderForm);

    if (mentorRoleRadio) {
      mentorRoleRadio.addEventListener('change', () => {
        console.log('mentor changed')
        if (mentorRoleRadio.checked) {
          mentorForm.style.display = 'block';
          founderForm.style.display = 'none';
        }
      });
    }

    if (founderRoleRadio) {
      founderRoleRadio.addEventListener('change', () => {
        console.log('founder changed')
        if (founderRoleRadio.checked) {
          founderForm.style.display = 'block';
          mentorForm.style.display = 'none';
        }
      });
    }

    if (founderForm) {
      const fv = FormValidation.formValidation(founderForm, {
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
              regexp: {
                regexp: lang === 'en' ? /^[a-zA-Z\s]*$/i : /^[\u0621-\u064A\s]*$/i,
                message: translations[lang].first_name_en.stringOnly
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
              regexp: {
                regexp: lang === 'en' ? /^[a-zA-Z\s]*$/i : /^[\u0621-\u064A\s]*$/i,
                message: translations[lang].last_name_en.stringOnly
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
              regexp: {
                regexp: /^[0-9]+$/,
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
          password: {
            validators: {
              notEmpty: {
                message: translations[lang].password.notEmpty
              },
              stringLength: {
                min: 8,
                message: translations[lang].password.stringLength
              },
              regexp: {
                regexp: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/,
                message: translations[lang].password.strong
              },
            }
          },
          password_confirmation: {
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
              },
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
      });
    }
    
    if (mentorForm) {
      const fv = FormValidation.formValidation(mentorForm, {
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
              regexp: {
                regexp: /^[a-zA-Z\s]*$/i,
                message: translations[lang].first_name_en.stringOnly
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
              regexp: {
                regexp: lang === 'en' ? /^[a-zA-Z\s]*$/i : /^[\u0621-\u064A\s]*$/i,
                message: translations[lang].last_name_en.stringOnly
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
              regexp: {
                regexp: lang === 'en' ? /^[a-zA-Z\s]*$/i : /^[\u0621-\u064A\s]*$/i,
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
          password: {
            validators: {
              notEmpty: {
                message: translations[lang].password.notEmpty
              },
              stringLength: {
                min: 8,
                message: translations[lang].password.stringLength
              },
              regexp: {
                regexp: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/,
                message: translations[lang].password.strong
              },
            }
          },
          password_confirmation: {
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
              },
            }
          },
          title_en: {
            validators: {
              notEmpty: {
                message: translations[lang].jobTitle.notEmpty
              },
              regexp: {
                regexp: /^[a-zA-Z\s]*$/i,
                message: translations[lang].jobTitle.enOnly
              }
            }
          },
          expertise: {
            validators: {
              notEmpty: {
                message: translations[lang].expertise.notEmpty
              }
            }
          },
          userbio: {
            validators: {
              notEmpty: {
                message: translations[lang].bio.notEmpty
              }
            }
          },
          Linkedin: {
            validators: {
              notEmpty: {
                message: translations[lang].linkedin.notEmpty
              },
              uri: {
                message: translations[lang].linkedin.uri
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
