/**
 *  Pages Authentication - Register Founders
 */

"use strict";

const translations = {
  en: {
    role: { notEmpty: "Please select an account type" },
    first_name: {
      notEmpty: "Please enter first name",
      stringLength: "First name must be between 2 and 50 characters",
    },
    last_name: {
      notEmpty: "Please enter last name",
      stringLength: "Last name must be between 2 and 50 characters",
    },
    gender: { notEmpty: "Please select your gender" },
    email: {
      notEmpty: "Please enter your email",
      emailAddress: "Please enter valid email address",
    },
    phone: {
      notEmpty: "Please enter your phone number",
      phone: "Phone number should contain numbers only",
      invalid: "Please enter a valid phone number",
    },
    password: {
      notEmpty: "Please enter your password",
      stringLength: "Password must be 8-20 characters long",
    },
    password_confirmation: {
      notEmpty: "Please confirm password",
      identical: "The password and its confirm are not the same",
    },
    terms: { notEmpty: "Please agree terms & conditions" },
    jobTitle: { notEmpty: "Please enter your job title" },
    expertise: { notEmpty: "Please select at least one expertise" },
    bio: {
      notEmpty: "Please enter your bio",
      stringLength: "Bio must be between 10 and 500 characters",
    },
    linkedin: {
      notEmpty: "Please enter your LinkedIn profile",
      uri: "Please enter a valid LinkedIn URL",
    },
    expertises_placeholder: "Select your expertises",
  },
  ar: {
    role: { notEmpty: "يرجى تحديد نوع الحساب" },
    first_name: {
      notEmpty: "يرجى إدخال الاسم الأول",
      stringLength: "يجب أن يكون الاسم الأول بين 2 و 50 حرفًا",
    },
    last_name: {
      notEmpty: "يرجى إدخال الاسم الأخير",
      stringLength: "يجب أن يكون الاسم الأخير بين 2 و 50 حرفًا",
    },
    gender: { notEmpty: "يرجى تحديد النوع" },
    email: {
      notEmpty: "يرجى إدخال بريدك الإلكتروني",
      emailAddress: "يرجى إدخال عنوان بريد إلكتروني صالح",
    },
    phone: {
      notEmpty: "يرجى إدخال رقم الهاتف",
      phone: "يجب أن يتكون الهاتف من أرقام فقط",
      invalid: "يرجى إدخال رقم هاتف صالح",
    },
    password: {
      notEmpty: "يرجى إدخال كلمة المرور",
      stringLength: "يجب أن تتكون كلمة المرور من 8-20 حرفًا",
    },
    password_confirmation: {
      notEmpty: "يرجى تأكيد كلمة المرور",
      identical: "كلمة المرور غير متطابقة",
    },
    terms: { notEmpty: "يرجى الموافقة على الشروط والأحكام" },
    jobTitle: { notEmpty: "يرجى إدخال المسمى الوظيفي" },
    expertise: { notEmpty: "يرجى تحديد الخبرة" },
    bio: {
      notEmpty: "يرجى إدخال السيرة الذاتية",
      stringLength: "يجب أن تكون السيرة الذاتية بين 10 و 500 حرفًا",
    },
    linkedin: {
      notEmpty: "يرجى إدخال رابط لينكد إن",
      uri: "يرجى إدخال رابط صحيح",
    },
    expertises_placeholder: "اختر مجالات خبرتك",
  },
};

const formAuthentication = document.querySelector("#formAuthentication");
const lang = document.documentElement.lang || "en";
const t = translations[lang] || translations.en;

document.addEventListener("DOMContentLoaded", function (e) {
  (function () {
    window.Helpers.initCustomOptionCheck();

    const mentorFields = document.querySelector("#mentor-fields");
    const accountTypeInputs = formAuthentication
      ? formAuthentication.querySelectorAll('input[name="role"]')
      : [];
    let formValidationInstance = null;
    let intlTelInputInstance = null;

    // --- Initialize Select2 ---
    const genderSelect = document.querySelector("#gender");
    function initGenderSelect2() {
      if (
        !genderSelect ||
        !window.jQuery ||
        typeof window.jQuery.fn.select2 !== "function"
      )
        return;
      if (window.jQuery(genderSelect).hasClass("select2-hidden-accessible"))
        return;
      window
        .jQuery(genderSelect)
        .wrap('<div class="position-relative"></div>')
        .select2({
          placeholder:
            genderSelect.dataset.placeholder || "Select your gender",
          dropdownParent: window.jQuery(genderSelect).parent(),
          width: "100%",
        });
    }

    const expertisesSelect = document.querySelector("#expertises");
    function initExpertisesSelect2() {
      if (
        !expertisesSelect ||
        !window.jQuery ||
        typeof window.jQuery.fn.select2 !== "function"
      )
        return;
      if (window.jQuery(expertisesSelect).hasClass("select2-hidden-accessible"))
        return;
      window
        .jQuery(expertisesSelect)
        .wrap('<div class="position-relative"></div>')
        .select2({
          placeholder: t.expertises_placeholder,
          dropdownParent: window.jQuery(expertisesSelect).parent(),
          width: "100%",
        });
    }

    function destroyExpertisesSelect2() {
      if (
        expertisesSelect &&
        window.jQuery &&
        typeof window.jQuery.fn.select2 === "function" &&
        window.jQuery(expertisesSelect).hasClass("select2-hidden-accessible")
      ) {
        window.jQuery(expertisesSelect).select2("destroy").unwrap();
      }
    }

    // --- Validation Configuration Factory ---
    function getFormValidationConfig() {
      const isMentor =
        formAuthentication.querySelector('input[name="role"]:checked')
          ?.value === "Mentor";

      const config = {
        fields: {
          role: { validators: { notEmpty: { message: t.role.notEmpty } } },
          first_name: {
            validators: {
              notEmpty: { message: t.first_name.notEmpty },
              stringLength: {
                min: 2,
                max: 50,
                message: t.first_name.stringLength,
              },
            },
          },
          last_name: {
            validators: {
              notEmpty: { message: t.last_name.notEmpty },
              stringLength: {
                min: 2,
                max: 50,
                message: t.last_name.stringLength,
              },
            },
          },
          gender: { validators: { notEmpty: { message: t.gender.notEmpty } } },
          email: {
            validators: {
              notEmpty: { message: t.email.notEmpty },
              regexp: {
                regexp: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i,
                message: translations[lang].email.emailAddress,
              },
            },
          },
          phone: {
            validators: {
              notEmpty: { message: t.phone.notEmpty },
              callback: {
                message: t.phone.invalid,
                callback: (input) => {
                  const value = input.value.trim();

                  if (value === "") return { valid: true };

                  const numericRegex = /^[\d\s\+\-\(\)]+$/;
                  if (!numericRegex.test(value)) {
                    return { valid: false, message: t.phone.phone };
                  }

                  let selectedCountry = "";
                  if (
                    intlTelInputInstance &&
                    typeof intlTelInputInstance.getSelectedCountryData ===
                      "function"
                  ) {
                    const countryData =
                      intlTelInputInstance.getSelectedCountryData();
                    selectedCountry = countryData.iso2 || "";
                  }

                  if (selectedCountry.toLowerCase() === "eg") {
                    const cleanNumber = value.replace(/[\s\-\(\)]/g, "");
                    if (cleanNumber.startsWith("0")) {
                      if (
                        cleanNumber.length === 11 &&
                        /^0(10|11|12|15|2)\d{8}$/.test(cleanNumber)
                      ) {
                        return { valid: true };
                      }
                      return { valid: false, message: t.phone.invalid };
                    }
                    if (
                      cleanNumber.length === 10 &&
                      /^(10|11|12|15|2)\d{8}$/.test(cleanNumber)
                    ) {
                      return { valid: true };
                    }
                    return { valid: false, message: t.phone.invalid };
                  }

                  if (
                    intlTelInputInstance &&
                    typeof intlTelInputInstance.isValidNumber === "function"
                  ) {
                    if (!intlTelInputInstance.isValidNumber()) {
                      let message = t.phone.invalid;
                      if (
                        typeof intlTelInputInstance.getValidationError ===
                          "function" &&
                        window.intlTelInputUtils &&
                        window.intlTelInputUtils.validationError
                      ) {
                        const error =
                          intlTelInputInstance.getValidationError();
                        switch (error) {
                          case window.intlTelInputUtils.validationError
                            .NOT_A_NUMBER:
                            message = t.phone.phone;
                            break;
                          default:
                            message = t.phone.invalid;
                        }
                      }
                      return { valid: false, message };
                    }
                    return { valid: true };
                  }

                  const phoneRegex = /^\+?[\d\s\-\(\)]{8,}$/;
                  if (!phoneRegex.test(value)) {
                    return { valid: false, message: t.phone.invalid };
                  }
                  return { valid: true };
                },
              },
            },
          },
          password: {
            validators: {
              notEmpty: { message: t.password.notEmpty },
              stringLength: {
                min: 8,
                max: 20,
                message: t.password.stringLength,
              },
            },
          },
          password_confirmation: {
            validators: {
              notEmpty: { message: t.password_confirmation.notEmpty },
              identical: {
                compare: () =>
                  formAuthentication.querySelector('[name="password"]').value,
                message: t.password_confirmation.identical,
              },
            },
          },
          terms: { validators: { notEmpty: { message: t.terms.notEmpty } } },
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            rowSelector: ".mb-6, .my-8",
            eleValidClass: "", // removes the green "is-valid" styling
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
          autoFocus: new FormValidation.plugins.AutoFocus(),
          excluded: new FormValidation.plugins.Excluded(),
        },
        init: (instance) => {
          instance.on("plugins.message.placed", function (e) {
            if (e.element.parentElement.classList.contains("input-group")) {
              e.element.parentElement.insertAdjacentElement(
                "afterend",
                e.messageElement,
              );
            }
          });
        },
      };

      if (isMentor) {
        config.fields.title_en = {
          validators: { notEmpty: { message: t.jobTitle.notEmpty } },
        };
        config.fields["expertise[]"] = {
          validators: { notEmpty: { message: t.expertise.notEmpty } },
        };
        config.fields.bio_en = {
          validators: {
            notEmpty: { message: t.bio.notEmpty },
            stringLength: { min: 10, max: 500, message: t.bio.stringLength },
          },
        };
        config.fields.Linkedin = {
          validators: {
            notEmpty: { message: t.linkedin.notEmpty },
            regexp: {
              regexp:
                /^https?:\/\/(www\.)?linkedin\.com\/in\/[a-zA-Z0-9-_\u00C0-\u017F]+\/?$/,
              message: translations[lang].linkedin.uri,
            },
          },
        };
      }

      return config;
    }

    // --- Initialization ---
    if (formAuthentication) {
      formValidationInstance = FormValidation.formValidation(
        formAuthentication,
        getFormValidationConfig(),
      );
    }

    function toggleMentorFields() {
      const isMentor =
        formAuthentication.querySelector('input[name="role"]:checked')
          ?.value === "Mentor";
      mentorFields?.classList.toggle("d-none", !isMentor);

      if (isMentor) initExpertisesSelect2();
      else destroyExpertisesSelect2();

      // Re-create validation instance to account for dynamic fields
      if (formValidationInstance) {
        formValidationInstance.destroy();
        formValidationInstance = FormValidation.formValidation(
          formAuthentication,
          getFormValidationConfig(),
        );
      }
    }

    accountTypeInputs.forEach((input) =>
      input.addEventListener("change", toggleMentorFields),
    );

    // Clear server-side @error validation messages when a field is modified
    const serverErrorRowSelector = ".mb-6, .my-8, .col-12, .form-check";
    formAuthentication &&
      formAuthentication
        .querySelectorAll("input, select, textarea")
        .forEach((field) => {
          const clearServerErrors = () => {
            const row = field.closest(serverErrorRowSelector);
            if (!row) return;
            row
              .querySelectorAll(
                ":scope > .invalid-feedback:not(.fv-plugins-message-container)",
              )
              .forEach((el) => el.remove());
          };
          field.addEventListener("change", clearServerErrors);
          field.addEventListener("input", clearServerErrors);
        });

    // Initialize Gender Select2
    initGenderSelect2();

    // If the mentor fields are visible on page load (e.g. after a server-side
    // validation error with the Mentor role preselected), make sure the
    // expertise multi-select is initialized.
    if (mentorFields && !mentorFields.classList.contains("d-none")) {
      initExpertisesSelect2();
    }

    // Initialize Phone Input
    const phoneInput = document.querySelector("#phone-number");
    const countryCodeInput = document.querySelector("#phone_code");
    if (phoneInput && window.intlTelInput) {
      intlTelInputInstance = window.intlTelInput(phoneInput, {
        initialCountry: "eg",
        separateDialCode: true,
        utilsScript:
          "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js",
      });
      countryCodeInput.value =
        intlTelInputInstance.getSelectedCountryData().dialCode;
      phoneInput.addEventListener("countrychange", function () {
        countryCodeInput.value =
          intlTelInputInstance.getSelectedCountryData().dialCode;
        if (formValidationInstance) {
          formValidationInstance.revalidateField("phone");
        }
      });
      phoneInput.addEventListener("input", function () {
        if (formValidationInstance) {
          formValidationInstance.revalidateField("phone");
        }
      });
      phoneInput.addEventListener("blur", function () {
        if (formValidationInstance) {
          formValidationInstance.revalidateField("phone");
        }
      });
    }
  })();
});
