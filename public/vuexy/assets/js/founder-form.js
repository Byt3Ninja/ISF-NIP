'use strict';

const currentLang = document.documentElement.lang;

const founderFormTranslations = {
  en: {
    founder_name: {
      notEmpty: 'Founder name is required.',
      name: 'Please enter a valid name.'
    },
    founder_email: {
      email: 'Please enter a valid email.'
    },
    founder_linkedin: {
      linkedin: 'Please enter a valid LinkedIn URL.'
    }
  },
  ar: {
    founder_name: {
      notEmpty: 'اسم المؤسس مطلوب.',
      name: 'الرجاء إدخال اسم صالح.'
    },
    founder_email: {
      email: 'الرجاء إدخال بريد إلكتروني صالح.'
    },
    founder_linkedin: {
      linkedin: 'يرجى إدخال رابط لينكد إن صالح.'
    }
  }
};

document.addEventListener('DOMContentLoaded', function () {
  const createValidationConfig = () => ({
    fields: {
      founder_name: {
        validators: {
          notEmpty: {
            message: founderFormTranslations[currentLang].founder_name.notEmpty
          },
          regexp: {
            regexp: /^[\p{L}\s]+$/u,
            message: founderFormTranslations[currentLang].founder_name.name
          }
        }
      },
      founder_email: {
        validators: {
          emailAddress: {
            message: founderFormTranslations[currentLang].founder_email.email
          }
        }
      },
      founder_linkedin: {
        validators: {
          regexp: {
            regexp: /^https:\/\/www.linkedin.com\/.*/,
            message: founderFormTranslations[currentLang].founder_linkedin.linkedin
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

  const founderForm = document.querySelector('#founder-form');
  const editFounderForm = document.querySelector('#edit-founder-form');

  if (founderForm) {
    const founderValidator = FormValidation.formValidation(founderForm, createValidationConfig());
    founderForm.addEventListener('submit', function (e) {
      const fields = founderForm.querySelectorAll('.form-control');
      fields.forEach(field => {
        field.value = '';
      });
    });
  }

  if (editFounderForm) {
    const editFounderValidator = FormValidation.formValidation(editFounderForm, createValidationConfig());
    editFounderForm.addEventListener('submit', function (e) {
      const fields = editFounderForm.querySelectorAll('.form-control');
      fields.forEach(field => {
        field.value = '';
      });
    });
  }
});
