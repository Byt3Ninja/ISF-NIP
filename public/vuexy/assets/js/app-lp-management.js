/**
 * app-lp-management.js
 * Organizational Landing Page Management
 *
 * Extracted & optimized from the Blade template inline script.
 * All Blade-rendered values are injected via `window.LPConfig` (see below).
 *
 * Required global bootstrap before this file is loaded:
 * ─────────────────────────────────────────────────────
 * window.LPConfig = {
 *   orgId          : <int>,
 *   orgName        : <string>,          // e.g. "Acme Corp"
 *   checkSlugUrl   : <string>,          // route('organization.wizard.check.slug', org->id)
 *   requiredMsg    : <string>,          // __('admin.Required_Field')
 *   stepIconOptions    : <string>,      // HTML <option value="icon-class">Label</option> block for step icons (generated from Blade)
 *   metricIconOptions  : <string>,      // HTML <option> block for metric icons  
 *   benefitIconOptions : <string>,      // HTML <option> block for benefit icons (unused - available for future use)
 *   i18n: {
 *     selectIcon        : '...',
 *     stepTitle         : '...',
 *     stepDescription   : '...',
 *     metricNumber      : '...',
 *     metricLabel       : '...',
 *     publicationName   : '...',
 *     publicationDate   : '...',
 *     articleTitle      : '...',
 *     articleLink       : '...',
 *     articleSummary    : '...',
 *     articleImage      : '...',
 *     partnerName       : '...',
 *     partnerLogo       : '...',
 *     logoPreview       : '...',
 *     landingPublished  : '...',
 *     landingUnpublished: '...',
 *   },
 * };
 */

(function ($) {
  'use strict';

  /* ═══════════════════════════════════════════════════════════
     GUARD — abort cleanly if config is missing
  ─────────────────────────────────────────────────────────── */
  if (!window.LPConfig) {
    console.error('[app-lp-management] window.LPConfig is not defined. Aborting.');
    return;
  }

  const CFG = window.LPConfig;
  const i18n = CFG.i18n || {};

  const lang = document.documentElement.lang || 'en';

  /* ═══════════════════════════════════════════════════════════
     1. CONSTANTS
  ─────────────────────────────────────────────────────────── */

  const MAX_LIMITS = {
    step: 4,
    metric: 8,
    article: 8,
    partner: 20,
    custom: 10,
  };

  const CONTAINER_MAP = {
    step: 'processStepsContainer',
    metric: 'metricsContainer',
    article: 'articlesContainer',
    partner: 'partnersContainer',
    custom: 'customSectionsContainer',
  };

  /** Maps tab section IDs → form-field name prefixes used for static header fields */
  const SECTION_FORM_PREFIX_MAP = {
    howItWorks: 'how_it_works',
    achievements: 'achievements',
    media: 'media',
    partners: 'partners',
    programs: 'programs',
  };

  const FILE_VALIDATION = {
    allowedTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'],
    maxBytes: 5 * 1024 * 1024,   // 5 MB
    acceptedExtensions: 'png,jpg,jpeg,webp,svg',
    maxSizeLabel: '5MB',
    partnerMaxBytes: 2 * 1024 * 1024,   // 2 MB
    partnerExtensions: 'png,jpg,jpeg,svg',
    partnerSizeLabel: '2MB',
  };

  /* ═══════════════════════════════════════════════════════════
     2. VALIDATOR REGISTRY
  ─────────────────────────────────────────────────────────── */

  /** Holds one FormValidation instance per form ID */
  const validators = {};

  /* ═══════════════════════════════════════════════════════════
     3. VALIDATION ENGINE
  ─────────────────────────────────────────────────────────── */

  /**
   * Find or create the .invalid-feedback element adjacent to `input`.
   * Checks next siblings first (fast path), then the whole parent subtree.
   */
  function getOrCreateFeedback(input) {
    let node = input.nextElementSibling;
    while (node) {
      if (node.classList.contains('invalid-feedback')) return node;
      node = node.nextElementSibling;
    }

    const existing = input.parentNode.querySelector('.invalid-feedback');
    if (existing) return existing;

    const fb = document.createElement('div');
    fb.className = 'invalid-feedback d-block';
    input.parentNode.insertBefore(fb, input.nextSibling);
    return fb;
  }

  function setFileError(input, message) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    getOrCreateFeedback(input).textContent = message;
  }

  function clearFileError(input) {
    input.classList.remove('is-invalid');
    getOrCreateFeedback(input).textContent = '';
  }

  /**
   * Validate a file input against allowed types + size limits.
   * Returns true when valid (or empty).
   */
  function validateFileInput(input) {
    if (!input.files || !input.files[0]) {
      clearFileError(input);
      return true;
    }

    const { type, size } = input.files[0];
    const badType = !FILE_VALIDATION.allowedTypes.includes(type);

    // Check if this is a partner logo input
    const isPartnerLogo = $(input).hasClass('logo-input');
    const maxSize = isPartnerLogo ? FILE_VALIDATION.partnerMaxBytes : FILE_VALIDATION.maxBytes;
    const badSize = size > maxSize;

    if (badType || badSize) {
      setFileError(
        input,
        badType
          ? 'Only PNG, JPG, SVG files are allowed'
          : isPartnerLogo
            ? `File must be smaller than ${FILE_VALIDATION.partnerSizeLabel}`
            : `File must be smaller than ${FILE_VALIDATION.maxSizeLabel}`
      );
      return false;
    }

    clearFileError(input);
    return true;
  }

  /** Render a thumbnail preview inside the nearest preview container */
  function showFilePreview(input) {
    if (!input.files || !input.files[0]) return;

    const col = input.closest('.col-12');
    const $preview = col
      ? $(col).find('.article-image-preview, .logo-preview')
      : $(input).siblings('.article-image-preview, .logo-preview');

    if (!$preview.length) return;

    const reader = new FileReader();
    reader.onload = (e) => {
      $preview.html(
        `<img src="${e.target.result}" class="img-thumbnail mt-2" style="max-height:60px;">`
      );
    };
    reader.readAsDataURL(input.files[0]);
  }

  function clearFilePreview(input) {
    const col = input.closest('.col-12');
    const $preview = col
      ? $(col).find('.article-image-preview, .logo-preview')
      : $(input).siblings('.article-image-preview, .logo-preview');
    $preview.empty();
  }

  /**
 * Validate metric number input - allows numbers, decimals, and optional text suffixes
 * Examples allowed: 100, 100.5, 100+, 1M, 1.5M, 1k, 5000+
 */
  function validateMetricNumber(input) {
    const value = input.value.trim();

    // Allow empty values
    if (value === '') {
      clearMetricError(input);
      return true;
    }

    // Regex pattern: allows numbers (with decimals) optionally followed by +, k, M, B, %
    // Matches: 100, 100.5, 100+, 1.5k, 2M, 3B, 75%, 1000+
    const pattern = /^(\d+(?:\.\d+)?)([$+%kMB]?)$/i;

    if (pattern.test(value)) {
      clearMetricError(input);
      return true;
    }

    setMetricError(input, i18n.metric_number_validation || 'Please enter a valid number (e.g., 100, 100.5, 100+, 1M, 75%)');
    return false;
  }

  function setMetricError(input, message) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    getOrCreateFeedback(input).textContent = message;
  }

  function clearMetricError(input) {
    input.classList.remove('is-invalid');
    const feedback = getOrCreateFeedback(input);
    if (feedback) feedback.textContent = '';
  }

  // Add this inside your document ready function
  $(document).on('input', 'input[name*="[number]"]', function () {
    validateMetricNumber(this);
  });

  /**
   * Initialise a FormValidation instance for a form.
   *
   * @param {string} formId      DOM id of the <form>
   * @param {Object} fieldConfig FormValidation fields configuration object
   */
  function initSectionValidator(formId, fieldConfig) {
    const form = document.getElementById(formId);
    if (!form) return;

    // File-validation pre-submit gate (runs before FormValidation)
    form.addEventListener(
      'submit',
      function (e) {
        let hasFileError = false;

        let hasError = false;
        $(this).find('input[name*="[number]"]').each(function () {
          if (!validateMetricNumber(this)) {
            hasError = true;
          }
        });

        if (hasError) {
          e.preventDefault();
          toastr.error('Please fix metric number errors before saving.');
        }

        form.querySelectorAll('input[type="file"]').forEach((input) => {
          if (!validateFileInput(input)) hasFileError = true;
        });

        if (hasFileError) {
          e.preventDefault();
          e.stopImmediatePropagation();
          toastr.error('Please fix file upload errors before saving.');
        }
      },
      true
    );

    const fv = FormValidation.formValidation(form, {
      fields: fieldConfig,
      plugins: {
        trigger: new FormValidation.plugins.Trigger({ event: { file: 'change' } }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: '',
          rowSelector: '.col-md-12, .col-md-6, .col-md-4, .col-lg-4, .col-12, .mb-3, .mb-2, .card-body',
          feedbackIcons: false,
        }),
        autoFocus: new FormValidation.plugins.AutoFocus(),
      },
    })
      // .on('core.form.invalid', () => toastr.error('Please fix the errors before saving.'))
      .on('core.form.valid', () => form.submit())
      .on('core.validator.validated', (data) => {
        const input = data.element;

        if (data.result.valid) {
          input.classList.remove('is-invalid');
          input.classList.add('is-valid');
          getOrCreateFeedback(input).textContent = '';
          if (data.validator === 'file') showFilePreview(input);
          return;
        }

        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        getOrCreateFeedback(input).textContent = data.result.message || 'Invalid field';

        if (data.validator === 'file') {
          input.value = '';
          clearFilePreview(input);
        }
      });

    validators[formId] = fv;
  }

  /* ═══════════════════════════════════════════════════════════
     4. REPEATER — FIELD NAME HELPERS
  ─────────────────────────────────────────────────────────── */

  /**
   * Returns the canonical field names for a repeater type at a given index.
   * Used to register / deregister FormValidation fields.
   */
  function getRepeaterFieldNames(type, index) {
    const map = {
      step: [
        `process_steps[${index}][title]`,
        `process_steps[${index}][description]`,
        `process_steps[${index}][icon]`,
      ],
      metric: [
        `metrics[${index}][number]`,
        `metrics[${index}][label]`,
        `metrics[${index}][icon]`,
      ],
      article: [
        `articles[${index}][publication_name]`,
        `articles[${index}][date]`,
        `articles[${index}][title]`,
        `articles[${index}][link]`,
        `articles[${index}][summary]`,
        `articles[${index}][image]`,
      ],
      partner: [
        `partners_list[${index}][name]`,
        `partners_list[${index}][logo]`,
      ],
      custom: [
        `custom_sections[${index}][title]`,
        `custom_sections[${index}][content]`,
        `custom_sections[${index}][visibility]`,
      ],
    };
    return map[type] || [];
  }

  /* ─── Field config factories ──────────────────────────────── */

  function buildLogoFieldConfig(hasPreviewImage = false) {
    const config = {
      validators: {
        file: {
          extension: FILE_VALIDATION.partnerExtensions,
          type: ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'].join(','),
          maxSize: FILE_VALIDATION.partnerMaxBytes,
          message: `PNG, JPG, SVG <= ${FILE_VALIDATION.partnerSizeLabel}`,
        },
      },
    };

    if (!hasPreviewImage) {
      config.validators.notEmpty = { message: i18n.requiredMsg || 'This field is required' };
    }

    return config;
  }

  function buildImageFieldConfig() {
    return {
      validators: {
        file: {
          extension: FILE_VALIDATION.acceptedExtensions,
          type: FILE_VALIDATION.allowedTypes.join(','),
          maxSize: FILE_VALIDATION.maxBytes,
          message: `PNG, JPG, WEBP <= ${FILE_VALIDATION.maxSizeLabel}`,
        },
      },
    };
  }

  function buildRequiredFieldConfig() {
    return { validators: { notEmpty: { message: CFG.requiredMsg } } };
  }

  /* ─── Register / deregister ───────────────────────────────── */

  function addRepeaterValidation(formId, type, index) {
    const fv = validators[formId];
    if (!fv) return;

    getRepeaterFieldNames(type, index).forEach((name) => {
      if (name.includes('[logo]')) {
        const hasPreviewImage = $(`input[name="${name}"]`).closest('.repeater-item').find('.logo-preview img').length > 0;
        return fv.addField(name, buildLogoFieldConfig(hasPreviewImage));
      }
      if (name.includes('[image]')) return fv.addField(name, buildImageFieldConfig());
      fv.addField(name, buildRequiredFieldConfig());
    });
  }

  function removeRepeaterValidation(formId, type, index) {
    const fv = validators[formId];
    if (!fv) return;

    getRepeaterFieldNames(type, index).forEach((name) => {
      try { fv.removeField(name); } catch (_) { /* field may not exist */ }
    });
  }

  /* ═══════════════════════════════════════════════════════════
     5. REPEATER — ADD
  ─────────────────────────────────────────────────────────── */

  $(document).on('click', '.add-item-btn', function () {
    const type = $(this).data('type');
    const formId = $(this).closest('form').attr('id');
    const $container = getContainerByType(type);
    const index = $container.find('.repeater-item').length;

    if (index >= (MAX_LIMITS[type] || 10)) {
      toastr.warning(`${i18n.maximumLimitReached}, ${MAX_LIMITS[type]} ${i18n.items} ${i18n.allowed}`);
      return;
    }

    $container.find('.empty-state').hide();

    const $item = $(getTemplateHtml(type, index));
    $container.append($item);

    initDatePicker();
    addRepeaterValidation(formId, type, index);

    if (type === 'partner') initPartnerPreview($item);
    if (type === 'custom') initQuillOnElement($item.find('.quill-editor'));
  });

  /* ═══════════════════════════════════════════════════════════
     6. REPEATER — REMOVE
  ─────────────────────────────────────────────────────────── */

  $(document).on('click', '.remove-item-btn', function () {
    const $item = $(this).closest('.repeater-item');
    const $container = $item.parent();
    const formId = $(this).closest('form').attr('id');
    const type = getTypeByContainer($container);
    const index = parseInt($item.data('index'), 10);

    removeRepeaterValidation(formId, type, index);

    $item.fadeOut(200, function () {
      $(this).remove();
      reindexContainer($container, type, formId);
      if ($container.find('.repeater-item').length === 0) {
        $container.find('.empty-state').show();
      }
    });
  });

  /**
   * Re-number every remaining repeater item after a removal.
   * Also re-registers FormValidation fields with the corrected indices.
   */
  function reindexContainer($container, type, formId) {
    const fv = validators[formId];
    if (!fv) return;

    const prefixMap = {
      article: 'articles',
      partner: 'partners_list',
      step: 'process_steps',
      metric: 'metrics',
    };
    const prefix = prefixMap[type];

    $container.find('.repeater-item').each(function (newIdx) {
      const $item = $(this);

      // Deregister old field names
      $item.find('input, select, textarea').each(function () {
        const oldName = $(this).attr('name');
        if (oldName) { try { fv.removeField(oldName); } catch (_) { } }
      });

      // Update DOM attributes
      $item.attr('data-index', newIdx);
      $item.find('.step-number').text(newIdx + 1);

      // Rename inputs
      if (prefix) {
        $item.find('input, select, textarea').each(function () {
          const name = $(this).attr('name');
          if (name) $(this).attr('name', name.replace(/\[\d+\]/, `[${newIdx}]`));
        });
      }

      // Re-register with new index
      addRepeaterValidation(formId, type, newIdx);
    });
  }

  /* ═══════════════════════════════════════════════════════════
     7. REPEATER TEMPLATES
  ─────────────────────────────────────────────────────────── */

  function getTemplateHtml(type, i) {
    const templates = {
      step: buildStepTemplate,
      metric: buildMetricTemplate,
      article: buildArticleTemplate,
      partner: buildPartnerTemplate,
    };
    return templates[type] ? templates[type](i) : '';
  }

  function buildStepTemplate(i) {
    return `
      <div class="repeater-item card shadow-none mb-3 border" data-index="${i}">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
          <h6 class="mb-0">${i18n.step || 'Step'} <span class="step-number">${i + 1}</span></h6>
          <button type="button" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill remove-item-btn">
            <i class="ti ti-trash"></i>
          </button>
        </div>
        <div class="card-body py-3">
          <div class="row">
            <div class="col-12 col-md-6 mb-2">
              <input type="text" class="form-control"
                     name="process_steps[${i}][title]"
                     placeholder="${i18n.stepTitle || 'Step Title'}"
                     maxlength="255">
            </div>
            <div class="col-12 col-md-6 mb-2">
              <select class="form-select" name="process_steps[${i}][icon]">
                <option value="">${i18n.selectIcon || 'Select icon'}</option>
                ${CFG.stepIconOptions || ''}
              </select>
            </div>
            <div class="col-12">
              <textarea class="form-control"
                        name="process_steps[${i}][description]"
                        rows="2"
                        placeholder="${i18n.stepDescription || 'Step Description'}"
                        maxlength="1000"></textarea>
            </div>
          </div>
        </div>
      </div>`;
  }

  function buildMetricTemplate(i) {
    return `
      <div class="repeater-item card shadow-none mb-3 border" data-index="${i}">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
          <h6 class="mb-0">${i18n.metric || 'Metric'} ${i + 1}</h6>
          <button type="button" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill remove-item-btn">
            <i class="ti ti-trash"></i>
          </button>
        </div>
        <div class="card-body py-3">
          <div class="row">
            <div class="col-12 mb-3">
              <input type="text" class="form-control"
                     name="metrics[${i}][number]"
                     placeholder="${i18n.metricNumber || 'Metric Number'}"
                     maxlength="20"
                    >
            </div>
            <div class="col-12 mb-3">
              <input type="text" class="form-control"
                     name="metrics[${i}][label]"
                     placeholder="${i18n.metricLabel || 'Metric Label'}"
                     maxlength="255">
            </div>
            <div class="col-12">
              <select class="form-select" name="metrics[${i}][icon]">
                <option value="">${i18n.selectIcon || 'Select icon'}</option>
                ${CFG.metricIconOptions || ''}
              </select>
            </div>
          </div>
        </div>
      </div>`;
  }

  function buildArticleTemplate(i) {
    return `
      <div class="repeater-item card shadow-none mb-3 border" data-index="${i}">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
          <h6 class="mb-0">${i18n.article || 'Article'} ${i + 1}</h6>
          <button type="button" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill remove-item-btn">
            <i class="ti ti-trash"></i>
          </button>
        </div>
        <div class="card-body py-3">
          <div class="row">
            <div class="col-12 col-md-6 mb-3">
              <label class="form-label">${i18n.publicationName || 'Publication Name'}</label>
              <input type="text" class="form-control"
                     name="articles[${i}][publication_name]"
                     placeholder="${i18n.publicationName || 'Publication Name'}"
                     maxlength="255">
            </div>
            <div class="col-12 col-md-6 mb-3">
              <label class="form-label">${i18n.publicationDate || 'Publication Date'}</label>
              <input type="text" class="form-control datepicker"
                     name="articles[${i}][date]"
                     placeholder="${i18n.publicationDate || 'Publication Date'}">
            </div>
            <div class="col-12 mb-3">
              <label class="form-label">${i18n.articleTitle || 'Article Title'}</label>
              <input type="text" class="form-control"
                     name="articles[${i}][title]"
                     placeholder="${i18n.articleTitle || 'Article Title'}"
                     maxlength="255">
            </div>
            <div class="col-12 mb-3">
              <label class="form-label">${i18n.articleLink || 'Article Link'}</label>
              <input type="url" class="form-control"
                     name="articles[${i}][link]"
                     placeholder="${i18n.articleLink || 'Article Link'}"
                     maxlength="500">
            </div>
            <div class="col-12 mb-3">
              <label class="form-label">${i18n.articleSummary || 'Article Summary'}</label>
              <textarea class="form-control"
                        name="articles[${i}][summary]"
                        rows="2"
                        placeholder="${i18n.articleSummary || 'Article Summary'}"
                        maxlength="1000"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">${i18n.articleImage || 'Article Image'}</label>
              <input type="file" class="form-control article-image-input"
                     name="articles[${i}][image]"
                     accept="image/png,image/jpeg,image/jpg">
              <div class="article-image-preview rounded overflow-hidden mt-2"></div>
              <small class="text-muted d-block mt-1">
                PNG, JPG, SVG (Max: ${FILE_VALIDATION.maxSizeLabel})
              </small>
            </div>
          </div>
        </div>
      </div>`;
  }

  function buildPartnerTemplate(i) {
    return `
      <div class="repeater-item card shadow-none mb-3 border" data-index="${i}">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
          <h6 class="mb-0">${i18n.partner || 'Partner'} ${i + 1}</h6>
          <button type="button" class="btn btn-icon btn-text-secondary waves-effect waves-light rounded-pill remove-item-btn">
            <i class="ti ti-trash"></i>
          </button>
        </div>
        <div class="card-body py-3">
          <div class="row">
            <div class="col-12 mb-3">
              <label class="form-label">${i18n.partnerName || 'Partner Name'}</label>
              <input type="text"
                     class="form-control name-input"
                     name="partners_list[${i}][name]"
                     placeholder="${i18n.partnerName || 'Partner Name'}"
                     maxlength="255">
            </div>
            <div class="col-12 col-md-3 mb-3">
              <label class="form-label">${i18n.logoPreview || 'Logo Preview'}</label>
              <div class="logo-preview h-px-75 d-flex align-items-center justify-content-center bg-light-subtle rounded text-center py-3">
                <i class="ti ti-building text-muted fs-3"></i>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">${i18n.partnerLogo || 'Partner Logo'}</label>
              <input type="file"
                     class="form-control logo-input"
                     name="partners_list[${i}][logo]"
                     accept="image/png,image/jpeg,image/jpg,image/svg+xml">
              <small class="text-muted d-block mt-1">
                PNG, JPG, SVG (Max: ${FILE_VALIDATION.partnerSizeLabel})
              </small>
            </div>
          </div>
        </div>
      </div>`;
  }

  /* ═══════════════════════════════════════════════════════════
     8. PARTNER — logo + name live preview
  ─────────────────────────────────────────────────────────── */

  function initPartnerPreview($card) {
    $card.find('.name-input').on('input', function () {
      $card.find('.partner-name-preview').text($(this).val() || 'New Partner');
    });

    $card.find('.logo-input').on('change', function (e) {
      const file = e.target.files[0];
      if (!file) return;

      if (file.size > FILE_VALIDATION.partnerMaxBytes) {
        toastr.warning(`Logo too large. Max ${FILE_VALIDATION.partnerSizeLabel}`);
        return;
      }

      const reader = new FileReader();
      reader.onload = (ev) => {
        $card.find('.logo-preview').html(
          `<img src="${ev.target.result}" class="img-fluid rounded" style="max-height:60px;">`
        );
      };
      reader.readAsDataURL(file);
    });
  }

  /* ═══════════════════════════════════════════════════════════
     9. PROGRAMS — visibility toggle + drag-to-reorder
  ─────────────────────────────────────────────────────────── */

  $(document).on('change', '.program-visible-toggle', function () {
    $(this)
      .closest('.program-item')
      .toggleClass('border-primary', this.checked)
      .toggleClass('border-secondary opacity-75', !this.checked);
  });

  function initProgramsSortable() {
    const container = document.getElementById('programsLandingContainer');
    if (!container) return;

    new Sortable(container, {
      handle: '.drag-handle',
      animation: 150,
      onEnd() {
        const orderedIds = Array.from(container.children)
          .map((item) => parseInt(item.dataset.programId))
          .filter(Boolean);

        $.ajax({
          url: `/dashboard/organization/${CFG.orgId}/programs/reorder`,
          type: 'POST',
          data: {
            ordered_ids: orderedIds,
            _token: $('meta[name="csrf-token"]').attr('content'),
          },
          success: () => toastr.success('Order saved'),
          error: () => toastr.error('Reorder failed'),
        });
      },
    });
  }

  /* ═══════════════════════════════════════════════════════════
     10. LANDING PAGE ENABLE TOGGLE
  ─────────────────────────────────────────────────────────── */

  function initLandingToggle() {
    $('#landingEnabledToggle').on('change', function () {
      const isEnabled = this.checked;
      $('#landingStatusText').html(
        isEnabled
          ? `<span class="text-success"><i class="ti ti-check me-1"></i>${i18n.landingPublished || 'Landing page is published'}</span>`
          : `<span class="text-warning"><i class="ti ti-eye-off me-1"></i>${i18n.landingUnpublished || 'Landing page is unpublished'}</span>`
      );
    });
  }

  /* ═══════════════════════════════════════════════════════════
     11. SLUG — generate + availability check
  ─────────────────────────────────────────────────────────── */

  function initSlugControls() {
    $('#generateSlugBtn').on('click', function () {
      const slug = (CFG.orgName || '')
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_]+/g, '-')
        .replace(/^-+|-+$/g, '');
      $('#slug').val(slug);
    });

    $('#checkSlugBtn').on('click', function () {
      const slug = $('#slug').val().trim();
      if (!slug) { toastr.warning('Please enter a slug first'); return; }

      const $feedback = $('#slugAvailabilityFeedback').show();
      $feedback.html(`<span class="text-muted"><i class="ti ti-loader-2 ti-spin me-1"></i>${lang == 'en' ? 'Checking' : 'جاري التحقق'}…</span>`);

      $.get(CFG.checkSlugUrl, { slug })
        .done((res) => {
          $feedback.html(
            res.available
              ? `<span class="text-success"><i class="ti ti-circle-check me-1"></i>${lang == 'en' ? 'Available' : 'متاح'}</span>`
              : `<span class="text-danger"><i class="ti ti-circle-x me-1"></i>${lang == 'en' ? 'Already taken' : 'مأخوذ بالفعل'}</span>`
          );
        })
        .fail(() => {
          $feedback.html(`<span class="text-danger"><i class="ti ti-alert-circle me-1"></i>${lang == 'en' ? 'Check failed' : 'فشل التحقق'}</span>`);
        });
    });
  }

  /* ═══════════════════════════════════════════════════════════
     12. TAB NAVIGATION (page-reload strategy)
  ─────────────────────────────────────────────────────────── */

  function initTabNavigation() {
    $(document).on('click', '.reload-tab', function () {
      const url = new URL(window.location.href);
      url.searchParams.set('tab', $(this).data('tab-id'));
      window.location.href = url.toString();
    });
  }

  /* ═══════════════════════════════════════════════════════════
     13. HELPERS
  ─────────────────────────────────────────────────────────── */

  function getContainerByType(type) {
    return $('#' + (CONTAINER_MAP[type] || ''));
  }

  function getTypeByContainer($container) {
    const id = $container.attr('id');
    return Object.keys(CONTAINER_MAP).find((k) => CONTAINER_MAP[k] === id) || null;
  }

  function initDatePicker() {
    if (typeof flatpickr === 'undefined') return;
    $('.datepicker:not(.flatpickr-input)').flatpickr({ dateFormat: 'Y-m-d', allowInput: true });
  }

  /* ═══════════════════════════════════════════════════════════
     14. BOOTSTRAP — DOCUMENT READY
  ─────────────────────────────────────────────────────────── */

  $(function () {

    /* ── Tab navigation ─────────────────────────────────── */
    initTabNavigation();

    /* ── Landing enabled toggle ─────────────────────────── */
    initLandingToggle();

    /* ── Slug controls ──────────────────────────────────── */
    initSlugControls();

    /* ── URL / landing form validator ───────────────────── */
    // initSectionValidator('landingPageForm', {
    //   slug: {
    //     validators: {
    //       regexp: {
    //         regexp : /^[a-z0-9]+(?:-[a-z0-9]+)*$/,
    //         message: 'Invalid slug',
    //       },
    //     },
    //   },
    // });

    /* ── Section-header field validators ────────────────── */
    Object.keys(SECTION_FORM_PREFIX_MAP).forEach((key) => {
      const prefix = SECTION_FORM_PREFIX_MAP[key];
      initSectionValidator(`form-${key}`, {
        [`${prefix}_title`]: { validators: { notEmpty: { message: CFG.requiredMsg } } },
        [`${prefix}_subtitle`]: { validators: { notEmpty: { message: CFG.requiredMsg } } },
        [`${prefix}_description`]: { validators: { notEmpty: { message: CFG.requiredMsg } } },
      });
    });

    /* ── Existing (server-rendered) repeater items ──────── */
    $('.repeater-item').each(function () {
      const $item = $(this);
      const formId = $item.closest('form').attr('id');
      const $container = $item.parent();
      const type = getTypeByContainer($container);
      const idx = parseInt($item.data('index'), 10);

      if (formId && type && type !== 'custom') {
        addRepeaterValidation(formId, type, idx);
      }
      if (type === 'partner') initPartnerPreview($item);
    });

    /* ── Programs sortable ──────────────────────────────── */
    initProgramsSortable();

    /* ── Date pickers ───────────────────────────────────── */
    initDatePicker();
  });

})(jQuery);