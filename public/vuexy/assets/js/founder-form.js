'use strict';

const currentLang = $('html').attr('lang');

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

$(document).ready(function () {
  const createValidationConfig = (mode = 'create') => ({
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
      autoFocus: new FormValidation.plugins.AutoFocus()
    },
    init: instance => {
      instance.on('plugins.message.placed', function (e) {
        if ($(e.element).parent().hasClass('input-group')) {
          $(e.element).parent().after(e.messageElement);
        }
      });
      instance.on('core.form.valid', function (e) {
        // console.log('e', e.formValidation.ee);
        // Prevent the form from submitting
        event.preventDefault();
        // e.formValidation.ee.preventDefault();

        createFounder(mode);

        console.log('Form is valid, but submission is prevented.');
      });
    }
  });

  const createFounder = mode => {
    const companyId = $('#CompanyN').val();
    const founderId = $('#founderId').val();

    const name = mode === 'create' ? $('#founder_name').val() : $('#edit_founder_name').val();
    const email = mode === 'create' ? $('#founder_email').val() : $('#edit_founder_email').val();
    const linkedin = mode === 'create' ? $('#founder_linkedin').val() : $('#edit_founder_linkedin').val();

    $.ajax({
      url: '/dashboard/AddCompanyTeams/' + companyId,
      method: 'post',
      data: {
        id: mode === 'edit' ? founderId : undefined,
        TeamsNo: mode == 'create' ? 'Founders' : undefined,
        founder_name: name,
        founder_email: email,
        founder_linkedin: linkedin,
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        console.log('response', response);
        const fields = editFounderForm.find('.form-control');
        fields.each(function () {
          $(this).val('');
        });

        if (response.state) {
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: response.state
          });
          if (mode === 'edit') {
            location.reload();
            return;
          }

          if (TeamsNo == 'Founders' && mode == 'create') {
            $('.ShowTableFounders').load(location.href + ' .ShowTableFounders');
          }
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errors
          });
          console.log('errors', errors);
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

  const founderForm = $('#founder-form');
  const editFounderForm = $('#edit-founder-form');

  if (founderForm.length) {
    founderForm.on('submit', function (event) {
      event.preventDefault();
    });
    const founderValidator = FormValidation.formValidation(founderForm[0], createValidationConfig());
  }

  if (editFounderForm.length) {
    editFounderForm.on('submit', function (event) {
      event.preventDefault();
    });
    const editFounderValidator = FormValidation.formValidation(editFounderForm[0], createValidationConfig('edit'));
  }
});
