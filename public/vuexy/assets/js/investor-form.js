'use strict';

const laang = $('html').attr('lang');

const investorFormTranslations = {
  en: {
    investor_name: {
      notEmpty: 'Investor name is required.',
      name: 'Please enter a valid name.'
    }
  },
  ar: {
    investor_name: {
      notEmpty: 'اسم المستثمر مطلوب.',
      name: 'الرجاء إدخال اسم صالح.'
    }
  }
};

$(document).ready(function () {
  const createValidationConfig = (mode = 'create') => ({
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
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: '.mb-6'
      }),
      submitButton: new FormValidation.plugins.SubmitButton({}),
      autoFocus: new FormValidation.plugins.AutoFocus()
    },
    init: instance => {
      instance.on('plugins.message.placed', function (e) {
        if ($(e.element).parent().hasClass('input-group')) {
          $(e.element).parent().after(e.messageElement);
        }
      });
      instance.on('core.form.valid', function () {
        createInvestor(mode);
      });
    }
  });

  const createInvestor = mode => {
    console.log('mode', mode);
    const companyId = $('#CompanyN').val();
    const investorId = $('#investorId').val();

    const nameInput = mode === 'create' ? $('#investor_name') : $('#edit_investor_name');
    const typeInput = mode === 'create' ? $('#investment_type') : $('#edit_investment_type');

    const name = mode === 'create' ? $('#investor_name').val() : $('#edit_investor_name').val();
    const type = mode === 'create' ? $('#investment_type').val() : $('#edit_investment_type').val();

    $.ajax({
      url: '/dashboard/AddCompanyTeams/' + companyId,
      method: 'post',
      data: {
        id: mode === 'edit' ? investorId : undefined,
        TeamsNo: mode == 'create' ? 'Investors' : undefined,
        investor_name: name,
        investment_type: type,
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        console.log('response', response);
        nameInput.val('');
        typeInput.val('');

        if (response.state) {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            confirmButtonText: 'OK'
          }).then(result => {
            if (result.isConfirmed) {
              location.reload();
            }
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: response.errors || 'An error occurred.'
          });
          console.log('errors', response.errors);
        }
      },
      error: function (error) {
        console.log('error', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'An error occurred while processing your request.'
        });
      }
    });
  };

  const investorForm = $('#investor-form');
  const editInvestorForm = $('#edit-investor-form');

  if (investorForm.length) {
    investorForm.submit(function (event) {
      event.preventDefault();
    });
    const investorValidator = FormValidation.formValidation(investorForm[0], createValidationConfig('create'));
  }

  if (editInvestorForm.length) {
    editInvestorForm.submit(function (event) {
      event.preventDefault();
    });
    const editInvestorValidator = FormValidation.formValidation(editInvestorForm[0], createValidationConfig('edit'));
  }
});
