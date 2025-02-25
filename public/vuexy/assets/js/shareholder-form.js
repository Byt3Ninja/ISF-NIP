
const lngg = document.documentElement.lang;

const shareholderFormTranslations = {
  en: {
    shareholder_name: {
      notEmpty: 'Founder name is required.',
      name: 'Please enter a valid name.'
    },
    shareholder_role: {
      textOnly: 'Role must be text only.'
    },
    shareholder_ownership_percentage: {
      number: 'Please enter a valid number.',
      percentage: 'Ownership percentage must be between 0 and 100.'
    }
  },
  ar: {
    shareholder_name: {
      notEmpty: 'اسم المؤسس مطلوب.',
      name: 'الرجاء إدخال اسم صالح.'
    },
    shareholder_role: {
      textOnly: 'يجب أن يكون الدور نصًا فقط.'
    },
    shareholder_ownership_percentage: {
      number: 'الرجاء إدخال رقم صالح.',
      percentage: 'يجب أن يكون نسبة الملكية بين 0 و 100.'
    }
  }
};

document.addEventListener('DOMContentLoaded', function () {
  const createValidationConfig = () => ({
    fields: {
      shareholder_name: {
        validators: {
          notEmpty: {
            message: shareholderFormTranslations[lngg].shareholder_name.notEmpty
          },
          regexp: {
            regexp: /^[\p{L}\s]+$/u,
            message: shareholderFormTranslations[lngg].shareholder_name.name
          }
        }
      },
      shareholder_role: {
        validators: {
          regexp: {
            regexp: /^[\p{L}\s]+$/u,
            message: shareholderFormTranslations[lngg].shareholder_role.textOnly
          }
        }
      },
      shareholder_ownership_percentage: {
        validators: {
          numeric: {
            message: shareholderFormTranslations[lngg].shareholder_ownership_percentage.number,
          },
          between: {
            min: 0,
            max: 100,
            message: shareholderFormTranslations[lngg].shareholder_ownership_percentage.percentage
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

  const shareholderForm = document.querySelector('#shareholder-form');
  const editShareHolderForm = document.querySelector('#edit-shareholder-form');

  console.log('editShareHolderForm', editShareHolderForm)

  if (shareholderForm) {
    const shareholderValidator = FormValidation.formValidation(shareholderForm, createValidationConfig());
  }

  if (editShareHolderForm) {
    const editFounderValidator = FormValidation.formValidation(editShareHolderForm, createValidationConfig());
  }
});
