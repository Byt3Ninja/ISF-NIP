const lngg = $('html').attr('lang');

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

$(document).ready(function () {
  const createValidationConfig = (mode = 'create') => ({
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
      autoFocus: new FormValidation.plugins.AutoFocus()
    },
    init: instance => {
      instance.on('plugins.message.placed', function (e) {
        if (e.element.parentElement.classList.contains('input-group')) {
          e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
        }
      });
      instance.on('core.form.valid', function () {
        createShareholder(mode);
      });
    }
  });

  const createShareholder = (mode) => {
    console.log('mode', mode)
    const companyId = $('#CompanyN').val();
    const shareholderId = $('#shareholderId').val();

    const nameInput = mode === 'create' ? $('#shareholder_name') : $('#edit_shareholder_name');
    const roleInput = mode === 'create' ? $('#shareholder_role') : $('#edit_shareholder_role');
    const percentageInput = mode === 'create' ? $('#shareholder_ownership_percentage') : $('#edit_shareholder_ownership_percentage');

    const name = mode === 'create' ? $('#shareholder_name').val() : $('#edit_shareholder_name').val();
    const role = mode === 'create' ? $('#shareholder_role').val() : $('#edit_shareholder_role').val();
    const percentage = mode === 'create' ? $('#shareholder_ownership_percentage').val() : $('#edit_shareholder_ownership_percentage').val();

    $.ajax({
      url: '/dashboard/AddCompanyTeams/' + companyId,
      method: 'post',
      data: {
        id: mode === 'edit' ? shareholderId : undefined,
        TeamsNo: mode == 'create' ? 'Shareholders' : undefined,
        shareholder_name: name,
        shareholder_role: role,
        shareholder_ownership_percentage: percentage,
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        console.log('response', response);
        nameInput.val('');
        roleInput.val('');
        percentageInput.val('');

        if (response.state) {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            confirmButtonText: 'OK'
          }).then((result) => {
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

  const shareholderForm = $('#shareholder-form');
  const editShareHolderForm = $('#edit-shareholder-form');

  if (shareholderForm.length) {
    shareholderForm.on('submit', function (e) {
      e.preventDefault(); // Prevent default form submission
    });
    const shareholderValidator = FormValidation.formValidation(shareholderForm[0], createValidationConfig('create'));
  }

  if (editShareHolderForm.length) {
    editShareHolderForm.on('submit', function (e) {
      e.preventDefault(); // Prevent default form submission
    });
    const editFounderValidator = FormValidation.formValidation(editShareHolderForm[0], createValidationConfig('edit'));
  }
});