/**
 * Program Create / Edit - Stepper wizard with validation
 * Shared by the create and edit blades (/vuexy/assets/js/app-edit-program.js).
 *
 * Modes
 *  - Create: header tabs are locked until steps 1-3 are submitted (step 3 creates the program).
 *  - Edit:   the program id comes from data-program-id on #wizard-program; every tab is unlocked.
 *
 * Saving
 *  - "Next" validates the current step and saves it (edit / after creation).
 *  - "Publish" (header) and "Submit" (last step) validate EVERY step, then save every step in order.
 */

"use strict";

// Bootstrap 5 removed the jQuery data API, but Summernote still calls $.fn.tooltip.
(function () {
  if (!window.bootstrap || !window.jQuery || $.fn.tooltip) return;
  $.fn.tooltip = function (option) {
    return this.each(function () {
      const instance = bootstrap.Tooltip.getInstance(this);
      if (typeof option === "string") {
        if (!instance) return;
        if (option === "destroy") instance.dispose();
        else if (typeof instance[option] === "function") instance[option]();
      } else if (!instance) {
        new bootstrap.Tooltip(this, option || {});
      }
    });
  };
})();

(function () {
  const wizardEl = document.querySelector("#wizard-program");
  if (!wizardEl) return;

  // ------------------------------------------------------------
  // Helpers
  // ------------------------------------------------------------
  const BASE_URL = (wizardEl.dataset.baseUrl || "/dashboard/programs").replace(
    /\/$/,
    "",
  );
  const LIST_URL = wizardEl.dataset.listUrl || "/dashboard/programlist";

  const csrfToken = () =>
    document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute("content") || "";

  const T = (key, fallback) =>
    (window.PROGRAM_FORM_LANG && window.PROGRAM_FORM_LANG[key]) ||
    fallback ||
    key;

  const escapeHtml = (s) =>
    String(s).replace(
      /[&<>"']/g,
      (c) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        })[c],
    );

  // Keeps FormValidation messages aligned with the column that wraps the field
  const fvRowSelector = (field, ele) => {
    const row =
      ele.type === "radio"
        ? ele.closest(".col-md-6")
        : ele.closest('div[class*="col-"]');
    if (!row) return ".col-12";
    const cls = Array.from(row.classList).filter((c) => c.startsWith("col"));
    return cls.length ? "." + cls.join(".") : ".col-12";
  };

  const fvPlugins = () => ({
    trigger: new FormValidation.plugins.Trigger(),
    bootstrap5: new FormValidation.plugins.Bootstrap5({
      eleValidClass: "",
      rowSelector: fvRowSelector,
    }),
    autoFocus: new FormValidation.plugins.AutoFocus(),
  });

  // Validators are promise based: `await isValid(fv)`. No submit handler is attached, so
  // validating a step never triggers a save by itself.
  const createValidator = (formEl, fields, init) =>
    FormValidation.formValidation(formEl, {
      fields,
      plugins: fvPlugins(),
      ...(init ? { init } : {}),
    });

  const isValid = async (fv) => (await fv.validate()) === "Valid";

  const initSelect2 = ($el, placeholder, onChange) => {
    if (!$el.length) return;
    $el.wrap('<div class="position-relative"></div>');
    $el
      .select2({ placeholder, dropdownParent: $el.parent() })
      .on("change", onChange);
  };

  const len = (min, max, msg) => ({
    stringLength: { min, max, trim: true, message: T(msg, msg) },
  });

  window.Helpers.initCustomOptionCheck();

  // ------------------------------------------------------------
  // Dropzone (Program Logo) - files are sent with the step request, never auto-uploaded
  // ------------------------------------------------------------
  let logoDropzone = null;
  Dropzone.autoDiscover = false;
  const dropzoneEl = document.querySelector("#dropzone-program-logo");
  if (dropzoneEl && !dropzoneEl.dropzone) {
    logoDropzone = new Dropzone(dropzoneEl, {
      url: "#", // required by Dropzone, never used (autoProcessQueue is off)
      autoProcessQueue: false,
      parallelUploads: 1,
      maxFilesize: 2,
      maxFiles: 1,
      acceptedFiles: ".jpg,.jpeg,.png,.gif,.webp",
      addRemoveLinks: true,
      previewTemplate: `<div class="dz-preview dz-file-preview">
<div class="dz-details">
  <div class="dz-thumbnail">
    <img data-dz-thumbnail>
    <span class="dz-nopreview">${T("No preview", "No preview")}</span>
    <div class="dz-success-mark"></div>
    <div class="dz-error-mark"></div>
    <div class="dz-error-message"><span data-dz-errormessage></span></div>
  </div>
  <div class="dz-filename" data-dz-name></div>
  <div class="dz-size" data-dz-size></div>
</div>
</div>`,
      init() {
        const dz = this;
        // Replace the current file instead of silently keeping a rejected second one
        dz.on("maxfilesexceeded", (file) => {
          dz.removeAllFiles();
          dz.addFile(file);
        });
      },
    });
  }

  const setCurrentLogo = (url) => {
    const wrap = document.getElementById("currentLogoWrap");
    const img = document.getElementById("currentLogoImg");
    if (!wrap || !img || !url) return;
    img.src = url;
    wrap.classList.remove("d-none");
  };

  // ------------------------------------------------------------
  // Summernote + Repeater (Custom pages)
  // ------------------------------------------------------------
  const summernoteConfig = {
    height: 250,
    // placeholder: T("Page description", "Page description"),
    toolbar: [
      ["style", ["bold", "italic", "underline", "clear"]],
      ["font", ["strikethrough", "superscript", "subscript"]],
      ["fontname", ["fontname"]],
      ["fontsize", ["fontsize"]],
      ["color", ["color"]],
      ["para", ["ul", "ol", "paragraph"]],
      ["height", ["height"]],
      ["table", ["table"]],
      ["insert", ["link", "picture", "video", "hr"]],
      ["view", ["fullscreen", "codeview"]],
      ["help", ["help"]],
    ],
  };

  const initSummernote = (el) => {
    if (!$(el).data("summernote")) $(el).summernote(summernoteConfig);
  };

  // Blank a page row (used for freshly cloned rows and when removing the last row)
  const resetPageRow = ($row) => {
    $row.find("input[type=text]").val("");
    $row.find("select").val("public");
    $row.find("input[type=checkbox]").prop("checked", true);
    $row.find(".summernote").each(function () {
      if ($(this).data("summernote")) $(this).summernote("code", "");
      else $(this).val("");
    });
  };

  // Keep the status switch label in sync with the toggle state
  const syncPageStatusLabels = () => {
    $pageList.find(".page-status").each(function () {
      const $label = $(this).closest(".form-check").find(".page-status-label");
      $label.text(
        $(this).is(":checked")
          ? T("Active", "Active")
          : T("Inactive", "Inactive"),
      );
    });
  };

  // Renumber page rows after a drag so the posted names match the visual order
  const reindexPages = () => {
    $pageList.find("[data-repeater-item]").each(function (index) {
      $(this)
        .find("[name]")
        .each(function () {
          const $input = $(this);
          const matches = ($input.attr("name") || "").match(/\[[^\]]+\]/g);
          const base = matches
            ? matches[matches.length - 1].replace(/\[|\]/g, "")
            : $input.attr("name");
          $input.attr(
            "name",
            "programPages[" +
              index +
              "][" +
              base +
              "]" +
              ($input.is(":checkbox") ? "[]" : ""),
          );
        });
    });
  };

  const $pagesRepeater = $("#programPagesRepeater");
  const $pageList = $pagesRepeater.find("[data-repeater-list]").first();
  if ($pagesRepeater.length) {
    // Repeater first so its clone template is captured before Summernote decorates the DOM
    $pagesRepeater.repeater({
      show: function () {
        const $item = $(this);
        // The template is a clone of the FIRST row, so it may carry that row's data
        $item.find(".note-editor").remove();
        $item.find(".summernote").removeData("summernote").show();
        resetPageRow($item);
        $item.find(".summernote").each(function () {
          initSummernote(this);
        });
        $item.slideDown();
      },
      hide: function (removeElement) {
        const $item = $(this);
        const $list = $item.closest("[data-repeater-list]");
        // Always keep one row: clear it instead of removing it
        if ($list.find("[data-repeater-item]").length <= 1) {
          resetPageRow($item);
          return;
        }
        $item.find(".summernote").each(function () {
          if ($(this).data("summernote")) $(this).summernote("destroy");
        });
        $item.slideUp(removeElement);
      },
    });

    $pagesRepeater.find(".summernote").each(function () {
      initSummernote(this);
    });

    // Status switch label + drag & drop reordering (SortableJS handles the handle-only drag)
    $pageList.on("change", ".page-status", function () {
      const $label = $(this).closest(".form-check").find(".page-status-label");
      $label.text(
        $(this).is(":checked")
          ? T("Active", "Active")
          : T("Inactive", "Inactive"),
      );
    });
    syncPageStatusLabels();

    if (typeof Sortable !== "undefined") {
      Sortable.create($pageList[0], {
        handle: ".pages-drag-handle",
        animation: 150,
        direction: "vertical",
        onEnd: reindexPages,
      });
    }
  }

  // ------------------------------------------------------------
  // Wizard definition
  // ------------------------------------------------------------
  const STEPS = [
    { key: "general", selector: "#step-general", path: "general" },
    { key: "schedule", selector: "#step-schedule", path: "schedule" },
    { key: "mentorship", selector: "#step-mentorship", path: "mentorship" },
    { key: "landing", selector: "#step-landing", path: "landing" },
    { key: "pages", selector: "#step-pages", path: "publish" },
  ];

  const form = wizardEl.querySelector("#wizard-program-form");
  const stepEls = STEPS.map((s) => form.querySelector(s.selector));
  const [step1, step2, step3, step4, step5] = stepEls;
  const stepper = new Stepper(wizardEl, { linear: false });

  // bs-stepper: stepper.to() is 1-based, stepper.next()/previous() are relative
  const goToStep = (index) => stepper.to(index + 1);
  const activeIndex = () =>
    Math.max(
      0,
      stepEls.findIndex((el) => el.classList.contains("active")),
    );

  let programId = String(wizardEl.dataset.programId || "").trim();
  const isEditMode = Boolean(programId);

  const stepUrl = (key) =>
    BASE_URL +
    (programId ? "/" + programId : "") +
    "/" +
    STEPS.find((s) => s.key === key).path;

  // ---- Header tab locking (create mode) --------------------------------------
  const stepHeaders = STEPS.map((s) =>
    wizardEl.querySelector(`.step[data-target="${s.selector}"]`),
  );
  const lockedSteps = new Set();
  const setAllStepsLocked = (locked) =>
    stepHeaders.forEach(
      (el) => el && (locked ? lockedSteps.add(el) : lockedSteps.delete(el)),
    );

  // Capture phase: runs before bs-stepper's own listener on the step
  wizardEl.addEventListener(
    "click",
    (event) => {
      const step = event.target.closest?.(".step");
      if (step && lockedSteps.has(step)) {
        event.preventDefault();
        event.stopPropagation();
      }
    },
    true,
  );
  setAllStepsLocked(!isEditMode);

  // ---- URL tab param (?tab=<slug>) -------------------------------------------
  const TAB_PARAM = "tab";
  const stepSlugs = stepHeaders.map((el) => el?.dataset.url || "");

  const setUrlTab = (slug) => {
    if (!slug) return;
    const url = new URL(window.location.href);
    if (url.searchParams.get(TAB_PARAM) === slug) return;
    url.searchParams.set(TAB_PARAM, slug);
    window.history.replaceState(window.history.state, "", url);
  };

  // Jump to the step named in the URL on load (only if it's unlocked)
  const initialTab = new URLSearchParams(window.location.search).get(TAB_PARAM);
  const initialIndex = initialTab ? stepSlugs.indexOf(initialTab) : -1;
  if (initialIndex > 0 && !lockedSteps.has(stepHeaders[initialIndex])) {
    goToStep(initialIndex);
  } else {
    setUrlTab(stepSlugs[activeIndex()]);
  }

  // Keep the URL in sync whenever the active step changes (tab click, Next/Prev, goToStep)
  wizardEl.addEventListener("shown.bs-stepper", () =>
    setUrlTab(stepSlugs[activeIndex()]),
  );

  // ------------------------------------------------------------
  // Requests
  // ------------------------------------------------------------
  const publishBtn = document.querySelector("#btnPublishProgram");
  let busy = false;
  let publishReady = false;

  // Required fields that must be present before Publish becomes available in
  // create mode. Steps 1 & 2 are both persisted by the single create request
  // (see saveStep), so both must be fulfilled for the button to be useful.
  const DATE_RANGE_RE = /^\d{4}-\d{2}-\d{2} to \d{4}-\d{2}-\d{2}$/;
  const step1Required = [
    "programOrganization",
    "programType",
    "programTitle",
    "programTitleAr",
    "programDescription",
    "programDescriptionAr",
  ];
  const fieldFilled = (name) => {
    const el = form.querySelector(`[name="${name}"]`);
    return Boolean(el && String(el.value || "").trim());
  };

  const setPublishDisabled = () => {
    if (publishBtn) publishBtn.disabled = busy || !publishReady;
  };

  const checkPublishReady = () => {
    // Edit mode (or a program already saved) has a Publish target.
    if (isEditMode || programId) {
      publishReady = true;
    } else {
      const dateOk = DATE_RANGE_RE.test(
        (form.querySelector('[name="programDateRange"]')?.value || "").trim(),
      );
      const typeOk = Boolean(
        form.querySelector('input[name="programStepType"]:checked'),
      );
      publishReady = step1Required.every(fieldFilled) && dateOk && typeOk;
    }
    setPublishDisabled();
  };

  const setBusy = (state) => {
    busy = state;
    form.querySelectorAll(".btn-next").forEach((b) => (b.disabled = state));
    setPublishDisabled();
  };
  setBusy(false);
  checkPublishReady();

  // Re-evaluate Publish availability as the step 1 & 2 fields are edited
  [step1, step2].forEach((el) => {
    el.addEventListener("input", checkPublishReady);
    el.addEventListener("change", checkPublishReady);
  });

  // Text + files of one or more steps. Summernote HTML is pushed back into its textarea.
  // The logo is only attached when the Schedule step is part of the request.
  const buildStepFormData = (els) => {
    const data = new FormData();
    [].concat(els).forEach((el) => {
      $(el)
        .find(".summernote")
        .each(function () {
          if ($(this).data("summernote"))
            $(this).val($(this).summernote("code"));
        });

      $(el)
        .find("input, select, textarea")
        .each(function () {
          if (!this.name || this.disabled || this.type === "file") return;
          if (
            (this.type === "checkbox" || this.type === "radio") &&
            !this.checked
          )
            return;
          const value = $(this).val();
          data.append(this.name, value == null ? "" : value);
        });

      if (logoDropzone && el.querySelector("#dropzone-program-logo")) {
        const file = logoDropzone.getAcceptedFiles()[0];
        if (file) data.append("programLogo", file, file.name);
      }
    });
    return data;
  };

  const applyResponse = (res) => {
    if (!res) return;
    if (res.logo_url) {
      setCurrentLogo(res.logo_url);
      logoDropzone?.removeAllFiles(true); // saved, don't re-upload on the next save
    }
    // The server may de-duplicate the slug (my-program -> my-program-1)
    if (typeof res.public_slug === "string")
      $("#programPublicUrl").val(res.public_slug);
  };

  const postStep = (endpoint, els) =>
    new Promise((resolve, reject) => {
      $.ajax({
        url: endpoint,
        method: "POST",
        headers: { "X-CSRF-TOKEN": csrfToken(), Accept: "application/json" },
        data: buildStepFormData(els),
        processData: false,
        contentType: false,
        dataType: "json",
      })
        .done((res) => {
          applyResponse(res);
          resolve(res);
        })
        .fail(reject);
    });

  // ---- Error reporting --------------------------------------------------------
  const stepIndexOfField = (key) => {
    const base = String(key).split(".")[0];
    const el = form.querySelector(
      `[name="${base}"], [name="${base}[]"], [name^="${base}["]`,
    );
    const stepEl = el && el.closest(".content");
    return stepEl ? stepEls.indexOf(stepEl) : -1;
  };

  const showError = (err) => {
    let message = "";
    let messages = [];
    let errors = null;

    if (err instanceof Error) {
      console.error(err);
    } else if (typeof err === "string") {
      message = err;
    } else if (err) {
      const json = err.responseJSON || {};
      errors = json.errors || null;
      messages = errors ? [...new Set(Object.values(errors).flat())] : [];
      message = json.message || "";
      if (err.status === 419)
        message = T(
          "Your session has expired. Please refresh the page and try again.",
        );
      else if (err.status === 413)
        message = T("The uploaded file is too large.");
      else if (err.status === 0)
        message = T(
          "Network error. Please check your connection and try again.",
        );
    }

    // Take the user to the step that holds the first invalid field
    if (errors) {
      const idx = stepIndexOfField(Object.keys(errors)[0]);
      if (idx >= 0 && idx !== activeIndex()) goToStep(idx);
    }

    return releaseAlert("danger", {
      title: T("Error", "Error"),
      message: messages.length
        ? `<ul class="text-start mb-0">${messages.map((m) => `<li>${escapeHtml(m)}</li>`).join("")}</ul>`
        : message ||
          T(
            "Something went wrong while saving this step. Please try again.",
            "Something went wrong while saving this step. Please try again.",
          ),
      confirmColor: "primary",
    });
  };

  // One request at a time; buttons are disabled while it runs
  const run = async (task) => {
    if (busy) return;
    setBusy(true);
    try {
      await task();
    } catch (err) {
      await showError(err);
    } finally {
      setBusy(false);
    }
  };

  // ------------------------------------------------------------
  // Step 1: General Information
  // ------------------------------------------------------------
  const fv1 = createValidator(step1, {
    programOrganization: {
      validators: {
        notEmpty: {
          message: T(
            "Please select an organization",
            "Please select an organization",
          ),
        },
      },
    },
    programType: {
      validators: {
        notEmpty: {
          message: T(
            "Please select a program type",
            "Please select a program type",
          ),
        },
      },
    },
    programTitle: {
      validators: {
        notEmpty: {
          message: T(
            "Please enter the program title",
            "Please enter the program title",
          ),
        },
        ...len(3, 50, "The title must be between 3 and 50 characters long"),
      },
    },
    programTitleAr: {
      validators: {
        notEmpty: {
          message: T(
            "Please enter the Arabic program title",
            "Please enter the Arabic program title",
          ),
        },
        ...len(
          3,
          50,
          "The Arabic title must be between 3 and 50 characters long",
        ),
      },
    },
    programCohort: {
      validators: {
        ...len(0, 20, "The cohort must be at most 20 characters long"),
      },
    },
    programDescription: {
      validators: {
        notEmpty: {
          message: T(
            "Please enter the description",
            "Please enter the description",
          ),
        },
        ...len(0, 100, "The description must be less than 100 characters long"),
      },
    },
    programDescriptionAr: {
      validators: {
        notEmpty: {
          message: T(
            "Please enter the Arabic description",
            "Please enter the Arabic description",
          ),
        },
        ...len(
          0,
          100,
          "The Arabic description must be less than 100 characters long",
        ),
      },
    },
  });

  ["programOrganization", "programType"].forEach((field) =>
    initSelect2($("#" + field), T("Select option", "Select option"), () =>
      fv1.revalidateField(field),
    ),
  );

  // ------------------------------------------------------------
  // Step 2: Schedule & Media
  // ------------------------------------------------------------
  const fv2 = createValidator(step2, {
    programDateRange: {
      validators: {
        notEmpty: {
          message: T(
            "Please select the program duration",
            "Please select the program duration",
          ),
        },
        callback: {
          message: T(
            "Please pick both a start and an end date (click the same day twice for a one-day program)",
            "Please pick both a start and an end date (click the same day twice for a one-day program)",
          ),
          callback: (input) =>
            /^\d{4}-\d{2}-\d{2} to \d{4}-\d{2}-\d{2}$/.test(input.value.trim()),
        },
      },
    },
    programStepType: {
      validators: {
        notEmpty: {
          message: T(
            "Please choose how the program runs",
            "Please choose how the program runs",
          ),
        },
      },
    },
  });

  const dateRangeEl = document.querySelector("#programDateRange");
  if (dateRangeEl) {
    flatpickr(dateRangeEl, {
      mode: "range",
      dateFormat: "Y-m-d",
      monthSelectorType: "static",
      // A locale-independent separator; the server parses " to " (and a few others)
      locale: { rangeSeparator: " to " },
      // Editing a running program must keep its past start date
      minDate: isEditMode ? null : "today",
      onChange: () => {
        fv2.revalidateField("programDateRange");
        checkPublishReady();
      },
    });
  }

  // ------------------------------------------------------------
  // Step 3: Mentorship Settings
  // ------------------------------------------------------------
  const timeBoxes = () =>
    step3.querySelectorAll('[name="programMentorshipReminderTimes[]"]');

  const fv3 = createValidator(
    step3,
    {
      programMentorshipReminders: {
        validators: {
          callback: {
            message: T(
              "Select at least one reminder time",
              "Select at least one reminder time",
            ),
            callback: (input) =>
              !input.checked ||
              Array.from(timeBoxes()).some((cb) => cb.checked),
          },
        },
      },
      programAddress: {
        validators: {
          ...len(0, 250, "The address must be less than 250 characters long"),
        },
      },
      programGoogleMapsUrl: {
        validators: {
          regexp: {
            regexp: /^https?:\/\/\S+$/i,
            message: T(
              "Enter a valid link starting with http:// or https://",
              "Enter a valid link starting with http:// or https://",
            ),
          },
          ...len(0, 250, "The link must be less than 250 characters long"),
        },
      },
    },
    (instance) => {
      // Move the error message out of the `input-group` element
      instance.on("plugins.message.placed", (e) => {
        if (e.element.parentElement.classList.contains("input-group")) {
          e.element.parentElement.insertAdjacentElement(
            "afterend",
            e.messageElement,
          );
        }
      });
    },
  );

  const remindersToggle = document.querySelector("#programMentorshipReminders");
  const reminderTimes = document.querySelector("#mentorshipReminderTimes");
  if (remindersToggle && reminderTimes) {
    const syncReminderTimes = () => {
      reminderTimes.classList.toggle("d-none", !remindersToggle.checked);
      fv3.revalidateField("programMentorshipReminders");
    };
    remindersToggle.addEventListener("change", syncReminderTimes);
    timeBoxes().forEach((cb) =>
      cb.addEventListener("change", () =>
        fv3.revalidateField("programMentorshipReminders"),
      ),
    );
    reminderTimes.classList.toggle("d-none", !remindersToggle.checked); // initial state, no validation
  }

  // ------------------------------------------------------------
  // Step 4: Landing & Branding
  // ------------------------------------------------------------
  const fv4 = createValidator(
    step4,
    {
      programVisibility: {
        validators: {
          notEmpty: {
            message: T(
              "Please select the program visibility",
              "Please select the program visibility",
            ),
          },
        },
      },
      programPublicUrl: {
        validators: {
          callback: {
            message: T(
              "Public URL is required for a public program",
              "Public URL is required for a public program",
            ),
            callback: (input) => {
              const visibility = step4.querySelector(
                '[name="programVisibility"]',
              );
              return !(
                visibility?.value === "public" && input.value.trim() === ""
              );
            },
          },
          ...len(
            0,
            250,
            "The public URL must be less than 250 characters long",
          ),
        },
      },
      // Branding colors are stored in hidden inputs driven by the Pickr widgets.
      programColorGradientStartVal: {
        validators: {
          notEmpty: {
            message: T(
              "Please enter a valid start color.",
              "Please enter a valid start color.",
            ),
          },
        },
      },
      programColorGradientEndVal: {
        validators: {
          notEmpty: {
            message: T(
              "Please enter a valid end color.",
              "Please enter a valid end color.",
            ),
          },
        },
      },
      programColorMainVal: {
        validators: {
          notEmpty: {
            message: T(
              "Please enter a valid button color.",
              "Please enter a valid button color.",
            ),
          },
        },
      },
    },
    (instance) => {
      // Move the error message out of the `input-group` element
      instance.on("plugins.message.placed", (e) => {
        if (e.element.parentElement.classList.contains("input-group")) {
          e.element.parentElement.insertAdjacentElement(
            "afterend",
            e.messageElement,
          );
        }
      });
    },
  );

  const publicUrlWrap = document.querySelector(".program-public-url-wrap");
  const syncPublicUrl = (value) =>
    publicUrlWrap?.classList.toggle("d-none", value !== "public");

  const $visibility = $("#programVisibility");
  initSelect2(
    $visibility,
    T("Select visibility", "Select visibility"),
    function () {
      syncPublicUrl(this.value);
      fv4.revalidateField("programVisibility");
      fv4.revalidateField("programPublicUrl");
    },
  );
  if ($visibility.length) syncPublicUrl($visibility.val());

  // ------------------------------------------------------------
  // Step 5: Custom pages & review
  // ------------------------------------------------------------
  const confirmEl = form.querySelector("#programConfirmed");
  const fv5 = createValidator(step5, {
    programConfirmed: {
      validators: {
        notEmpty: {
          message: T(
            "Please confirm the program details before publishing",
            "Please confirm the program details before publishing",
          ),
        },
      },
    },
  });

  const validators = [fv1, fv2, fv3, fv4, fv5];

  // The branding Pickr widgets live in a separate IIFE below. Expose a hook so
  // they can refresh the matching color field's validation state on change/clear.
  window.programBrandingColorRevalidate = (name) => fv4.revalidateField(name);

  // ------------------------------------------------------------
  // Saving
  // ------------------------------------------------------------
  // Persist the step at `index` (what "Next" does)
  const saveStep = async (index) => {
    if (!programId) {
      if (index < 1) return; // create flow: nothing exists yet, just move on
      // Step 2 creates the program from steps 1-2 in one request
      const res = await postStep(stepUrl("general"), [step1, step2]);
      if (res && res.id) {
        programId = String(res.id);
        wizardEl.dataset.programId = programId;
        setAllStepsLocked(false);
      }
      return;
    }
    if (index === 4) return; // the last step is saved by publishAll()
    await postStep(stepUrl(STEPS[index].key), stepEls[index]);
  };

  const goNext = (index) =>
    run(async () => {
      if (index === STEPS.length - 1) return publishFlow(false);
      if (!(await isValid(validators[index]))) return;
      await saveStep(index);
      stepper.next();
    });

  // Validate every step, then save every step. Used by header "Publish" and last-step "Submit".
  const publishFlow = async (autoConfirm) => {
    if (autoConfirm && confirmEl) confirmEl.checked = true; // clicking Publish is the confirmation

    for (let i = 0; i < validators.length; i++) {
      if (!(await isValid(validators[i]))) {
        goToStep(i);
        return;
      }
    }

    // Create mode: the program doesn't exist yet — the create request persists
    // steps 1-2 and returns the id that every later step's endpoint needs.
    if (!programId) {
      const res = await postStep(stepUrl("general"), [step1, step2]);
      if (!res || !res.id) return;
      programId = String(res.id);
      wizardEl.dataset.programId = programId;
      setAllStepsLocked(false);
      checkPublishReady();
    } else {
      await postStep(stepUrl("general"), [step1, step2]);
    }
    await postStep(stepUrl("mentorship"), step3);
    await postStep(stepUrl("landing"), step4);
    await postStep(stepUrl("pages"), step5);

    await releaseAlert("success", {
      title: isEditMode
        ? T("Program updated!", "Program updated!")
        : T("Program published!", "Program published!"),
      message: isEditMode
        ? T(
            "Your program has been updated successfully.",
            "Your program has been updated successfully.",
          )
        : T(
            "Your program has been created successfully.",
            "Your program has been created successfully.",
          ),
      confirmColor: "success",
    });
    window.location.href = LIST_URL;
  };

  // ------------------------------------------------------------
  // Navigation
  // ------------------------------------------------------------
  stepEls.forEach((el, i) => {
    el.querySelector(".btn-next")?.addEventListener("click", () => goNext(i));
    el.querySelector(".btn-prev")?.addEventListener("click", () => {
      if (i > 0) stepper.previous();
    });
  });

  // Enter inside a plain text input behaves like "Next"
  form.addEventListener("keydown", (e) => {
    if (e.key !== "Enter" || !e.target.matches?.('input[type="text"]')) return;
    if (e.target.classList.contains("flatpickr-input")) return;
    e.preventDefault();
    const index = activeIndex();
    if (index < STEPS.length - 1) goNext(index); // never publish from a stray Enter
  });

  publishBtn?.addEventListener("click", () => {
    wizardEl.scrollIntoView({ behavior: "smooth", block: "start" });
    run(() => publishFlow(true));
  });
})();

// ------------------------------------------------------------
// Color pickers (Branding colors)
// ------------------------------------------------------------
(function () {
  const PickrLib = window.Pickr || window.pickr;
  if (!PickrLib) return;

  // Defaults = Platform Branding (PlatformBrandingService::programDefaults), passed on the
  // reset button. Start/end carry the 8% alpha the .hero-gradient CSS uses; main is solid.
  const BRAND_DEFAULTS = Object.assign(
    { start: "#7367f014", end: "#b9b3f814", main: "#7367f0" },
    JSON.parse(document.getElementById("btnResetBrandingColors")?.dataset.defaults || "{}")
  );

  const brandingDefaults = [
    { containerId: "#programColorGradientStart", hiddenId: "#programColorGradientStartVal", field: "programColorGradientStartVal", key: "start" },
    { containerId: "#programColorGradientEnd", hiddenId: "#programColorGradientEndVal", field: "programColorGradientEndVal", key: "end" },
    { containerId: "#programColorMain", hiddenId: "#programColorMainVal", field: "programColorMainVal", key: "main" },
  ];

  const initColorPicker = ({ containerId, hiddenId, field, key }) => {
    const el = document.querySelector(containerId);
    const hidden = document.querySelector(hiddenId);
    if (!el || !hidden) return null;

    // An existing stored value (edit flow) wins over the default; keep 8-digit
    // (alpha) hex untouched because the hero gradients rely on that tint.
    const initial = (hidden.value.trim() || BRAND_DEFAULTS[key]).trim();
    hidden.value = initial;

    const picker = PickrLib.create({
      el,
      theme: "classic",
      default: initial,
      swatches: [
        "#7367F0",
        "#28C76F",
        "#EA5455",
        "#FF9F43",
        "#1E9FF2",
        "#82868B",
      ],
      components: {
        // Main components
        preview: true,
        opacity: true,
        hue: true,

        // Input / output Options
        interaction: {
          hex: true,
          rgba: true,
          hsla: true,
          hsva: true,
          cmyk: true,
          input: true,
          clear: true,
          save: true,
        },
      },
    });

    // Pickr 1.x has no onChange/onSave options: values must be read from events
    const sync = (picked) => {
      hidden.value = picked ? picked.toHEXA().toString() : "";
      window.programBrandingColorRevalidate?.(field);
    };
    picker
      .on("change", sync)
      .on("save", sync)
      .on("clear", () => {
        hidden.value = "";
        window.programBrandingColorRevalidate?.(field);
      });
    return picker;
  };

  const pickers = brandingDefaults.map(initColorPicker);

  document
    .getElementById("btnResetBrandingColors")
    ?.addEventListener("click", () => {
      brandingDefaults.forEach((cfg, i) => {
        pickers[i]?.setColor(BRAND_DEFAULTS[cfg.key], true);
        pickers[i]?.applyColor(true);
        const hidden = document.querySelector(cfg.hiddenId);
        if (hidden) hidden.value = BRAND_DEFAULTS[cfg.key];
        window.programBrandingColorRevalidate?.(cfg.field);
      });
    });
})();
