'use strict';

const laang = document.documentElement.lang;

const investorFormTranslations = {
  en: {
    investor_name: {
      notEmpty: 'Investor name is required.',
      name: 'Please enter a valid name.'
    },
  },
  ar: {
    investor_name: {
      notEmpty: 'اسم المستثمر مطلوب.',
      name: 'الرجاء إدخال اسم صالح.'
    },
  }
};

document.addEventListener('DOMContentLoaded', function () {
  const createValidationConfig = () => ({
    fields: {
      investor_name: {
        validators: {
          notEmpty: {
            message: investorFormTranslations[laang].investor_name.notEmpty
          },
          regexp: {
            regexp: /^[\p{L}\s]+$/u,
            message: investorFormTranslations[laang].investor_name.name
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
      defaultSubmit: new FormValidation.plugins.DefaultSubmit({
        onSubmit: function (e) {
          e.preventDefault();
          e.target.reset();
        }
      }),
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

  const investorForm = document.querySelector('#investor-form');
  const editInvestorForm = document.querySelector('#edit-investor-form');

  if (investorForm) {
    const investorValidator = FormValidation.formValidation(investorForm, createValidationConfig());
  }

  if (editInvestorForm) {
    const editInvestorValidator = FormValidation.formValidation(editInvestorForm, createValidationConfig());
  }
});
