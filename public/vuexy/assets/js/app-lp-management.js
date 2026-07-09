/**
 * app-lp-management.js
 * Organizational Landing Page Management
 */

(function ($) {
  'use strict';

  if (!window.LPConfig) {
    console.error('[app-lp-management] window.LPConfig is not defined.');
    return;
  }

  const CFG = window.LPConfig;
  const i18n = CFG.i18n || {};
  const lang = document.documentElement.lang || 'en';
  const isAr = lang === 'ar';

  /* ═══════════════════════════════════════════════════════════
     1. TRANSLATIONS
  ─────────────────────────────────────────────────────────── */
  const T = {
    completeStep: isAr ? 'يرجى إكمال جميع حقول هذه الخطوة' : 'Please complete all fields in this step.',
    completeMetric: isAr ? 'يرجى إكمال جميع حقول هذا المعيار' : 'Please complete all fields in this metric.',
    completeArticle: isAr ? 'يرجى إكمال جميع حقول هذا المقال' : 'Please complete all fields in this article.',
    completePartner: isAr ? 'يرجى إكمال جميع حقول هذا الشريك' : 'Please complete all fields in this partner.',
    articleImageBadType: isAr ? 'نوع الملف غير صالح، يرجى رفع ملفات من نوع PNG أو JPG فقط' : 'Invalid file format. Please upload PNG or JPG files only.',
    articleImageTooLarge: isAr ? 'الملف اكبر من الحد المسموح. الحد الأقصى المسموح به هو 5 ميجابايت' : 'File too large. Maximum size allowed is 5MB.',
    logoImageBadType: isAr ? 'نوع الملف غير صالح (PNG, JPG, SVG)' : 'Invalid format (PNG, JPG, SVG).',
    logoImageTooLarge: isAr ? 'الملف أكبر من الحد المسموح به. الحد الأقصى المسموح به هو 2 ميجابايت' : 'File too large. Maximum size allowed is 2 MB.',
    invalidUrl: isAr ? 'يرجى إدخال رابط صالح' : 'Please enter a valid URL.',
    maxLimitReached: isAr ? 'لقد وصلت إلى الحد الأقصى المسموح به' : 'You have reached the maximum limit.',
    required: CFG.requiredMsg || (isAr ? 'هذا الحقل مطلوب' : 'This field is required'),
    optional: isAr ? 'اختياري' : 'Optional',
    fixErrors: isAr ? 'يرجى تصحيح الأخطاء قبل الحفظ.' : 'Please fix the errors before saving.',
    slugChecking: isAr ? 'جاري التحقق' : 'Checking',
    slugAvailable: isAr ? 'متاح' : 'Available',
    slugTaken: isAr ? 'مأخوذ بالفعل' : 'Already taken',
    orderSaved: isAr ? 'تم حفظ الترتيب' : 'Order saved',
    orderFailed: isAr ? 'فشل إعادة الترتيب' : 'Reorder failed',
  };

  const MAX_LIMITS = { step: 4, metric: 8, article: 8, partner: 20 };
  const CONTAINER_MAP = { step: 'processStepsContainer', metric: 'metricsContainer', article: 'articlesContainer', partner: 'partnersContainer' };

  // Fields used for the "all or nothing" row logic
  const REPEATER_TEXT_FIELDS = {
    step: ['[title]', '[description]', '[icon]'],
    metric: ['[number]', '[label]', '[icon]'],
    article: ['[publication_name]', '[date]', '[title]', '[link]', '[summary]'],
    partner: ['[name]']
  };

  const validators = {};

  /* ═══════════════════════════════════════════════════════════
     2. HELPERS: Manual UI Feedback (For Partial-Fill Logic)
  ─────────────────────────────────────────────────────────── */

  function setManualError(input, message) {
    const $el = $(input);
    const $container = $el.closest('.fv-row, .mb-3, .col-12');
    $el.addClass('is-invalid').removeClass('is-valid');
    
    let $fb = $container.find('.invalid-feedback');
    if (!$fb.length) {
      $fb = $('<div class="invalid-feedback d-block"></div>').insertAfter(input);
    }
    $fb.text(message).show();
  }

  function clearManualError(input) {
    const $el = $(input);
    const $container = $el.closest('.fv-row, .mb-3, .col-12');
    $el.removeClass('is-invalid');
    $container.find('.invalid-feedback').hide().text('');
  }

  /* ═══════════════════════════════════════════════════════════
     3. FILE PREVIEWS
  ─────────────────────────────────────────────────────────── */

  function handleFileChange(input, type) {
    const $item = $(input).closest('.repeater-item');
    const $preview = $item.find(type === 'partner' ? '.logo-preview' : '.article-image-preview');
    
    if (!input.files || !input.files[0]) {
      if (type !== 'partner') $preview.empty();
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      const imgHtml = `<img src="${e.target.result}" class="img-thumbnail mt-2" style="max-height:60px;">`;
      if (type === 'partner') {
        $preview.html(imgHtml);
      } else {
        $preview.html(imgHtml);
      }
    };
    reader.readAsDataURL(input.files[0]);
  }

  /* ═══════════════════════════════════════════════════════════
     4. VALIDATION CORE
  ─────────────────────────────────────────────────────────── */

  function buildStandardPlugins() {
    return {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: 'is-valid',
        eleInvalidClass: 'is-invalid',
        // Target specific containers for consistent message placement
        rowSelector: '.fv-row, .mb-3, .mb-2, .col-12, .col-md-6'
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus(),
    };
  }

  function validateRepeaterRow($item, type) {
    const fragments = REPEATER_TEXT_FIELDS[type] || [];
    const fields = $item.find('input, select, textarea').filter(function() {
      const name = $(this).attr('name') || '';
      return fragments.some(f => name.includes(f));
    }).get();

    const anyFilled = fields.some(el => el.value.trim() !== '');
    if (!anyFilled) {
      fields.forEach(clearManualError);
      return true; 
    }

    let isValid = true;
    const errorMsg = T[`complete${type.charAt(0).toUpperCase() + type.slice(1)}`] || T.required;

    fields.forEach(el => {
      if (!el.value.trim()) {
        setManualError(el, errorMsg);
        isValid = false;
      } else {
        clearManualError(el);
      }
    });
    return isValid;
  }

  function attachStandardHandlers(formId, repeaterTypes, fv) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', function (e) {
      let blocked = false;

      // Check partial-fill rows
      repeaterTypes.forEach(type => {
        $(`#${CONTAINER_MAP[type]}`).find('.repeater-item').each(function() {
          if (!validateRepeaterRow($(this), type)) blocked = true;
        });
      });

      if (blocked) {
        e.preventDefault();
        e.stopImmediatePropagation();
        toastr.error(T.fixErrors);
      }
    }, true);

    fv.on('core.form.valid', () => form.submit());
    
    fv.on('core.validator.validated', (data) => {
      const isPartner = $(data.element).hasClass('logo-input');
      const isArticleImg = $(data.element).closest('.repeater-item').find('.article-image-preview').length > 0;
      if (!data.result.valid && (data.validator === 'file' || (data.validator === 'callback' && isArticleImg))) {
        data.element.value = '';
        if (!isPartner) $(data.element).closest('.repeater-item').find('.article-image-preview').empty();
      } else if (data.result.valid && (data.validator === 'file' || (data.validator === 'callback' && isArticleImg))) {
        handleFileChange(data.element, isPartner ? 'partner' : 'article');
      }
    });

    validators[formId] = fv;
  }

  /* ═══════════════════════════════════════════════════════════
     5. REPEATER FIELD CONFIGURATIONS
  ─────────────────────────────────────────────────────────── */

  function addRepeaterValidation(formId, type, index) {
    const fv = validators[formId];
    if (!fv) return;

    const fieldConfig = {};
    const prefix = { step: 'process_steps', metric: 'metrics', article: 'articles', partner: 'partners_list' }[type];

    if (type === 'step') {
      fieldConfig[`${prefix}[${index}][title]`] = { validators: { notEmpty: { message: T.required }, stringLength: { max: 255 } } };
      fieldConfig[`${prefix}[${index}][description]`] = { validators: { notEmpty: { message: T.required }, stringLength: { max: 1000 } } };
      fieldConfig[`${prefix}[${index}][icon]`] = { validators: { notEmpty: { message: T.required } } };
    }

    if (type === 'metric') {
      fieldConfig[`${prefix}[${index}][number]`] = { 
        validators: { 
          notEmpty: { message: T.required },
          regexp: { regexp: /^(\d+(?:\.\d+)?)([$+%kMB]?)$/i, message: isAr ? 'رقم غير صحيح' : 'Invalid format (e.g. 100, 1M, 75%)' }
        } 
      };
      fieldConfig[`${prefix}[${index}][label]`] = { validators: { notEmpty: { message: T.required }, stringLength: { max: 255 } } };
      fieldConfig[`${prefix}[${index}][icon]`] = { validators: { notEmpty: { message: T.required } } };
    }

    if (type === 'article') {
      fieldConfig[`${prefix}[${index}][publication_name]`] = { validators: { notEmpty: { message: T.required } } };
      fieldConfig[`${prefix}[${index}][date]`] = { validators: { notEmpty: { message: T.required } } };
      fieldConfig[`${prefix}[${index}][title]`] = { validators: { notEmpty: { message: T.required } } };
      fieldConfig[`${prefix}[${index}][link]`] = { validators: { notEmpty: { message: T.required }, uri: { message: T.invalidUrl } } };
      fieldConfig[`${prefix}[${index}][summary]`] = { validators: { notEmpty: { message: T.required }, stringLength: { max: 1000 } } };
      fieldConfig[`${prefix}[${index}][image]`] = {
        validators: {
          callback: {
            callback: (input) => {
              if (!input.value) return { valid: true };
              const file = input.element.files[0];
              if (!file) return { valid: true };
              const allowed = ['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'];
              if (!allowed.includes(file.type)) {
                return { valid: false, message: T.articleImageBadType };
              }
              if (file.size > 5 * 1024 * 1024) {
                return { valid: false, message: T.articleImageTooLarge };
              }
              return { valid: true };
            }
          }
        }
      };
    }

    if (type === 'partner') {
      fieldConfig[`${prefix}[${index}][name]`] = { validators: { stringLength: { max: 255 } } };
      fieldConfig[`${prefix}[${index}][logo]`] = {
        validators: {
          callback: {
            message: T.required,
            callback: (input) => {
              if (!input.value) {
                const hasExisting = $(input.element).closest('.repeater-item').find('.logo-preview img').length > 0;
                return hasExisting;
              }
              const file = input.element.files[0];
              if (!file) return { valid: false, message: T.required };
              const allowedTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];
              const allowedExts = ['jpg', 'jpeg', 'png', 'svg'];
              const ext = file.name.split('.').pop().toLowerCase();
              if (!allowedTypes.includes(file.type) || !allowedExts.includes(ext)) {
                return { valid: false, message: T.logoImageBadType };
              }
              if (file.size > 2 * 1024 * 1024) {
                return { valid: false, message: T.logoImageTooLarge };
              }
              return { valid: true };
            }
          }
        }
      };
    }

    Object.entries(fieldConfig).forEach(([name, config]) => fv.addField(name, config));
  }

  /* ═══════════════════════════════════════════════════════════
     6. REPEATER ACTIONS (Add, Remove, Reindex)
  ─────────────────────────────────────────────────────────── */

  $(document).on('click', '.add-item-btn', function () {
    const type = $(this).data('type');
    const formId = $(this).closest('form').attr('id');
    const $container = $('#' + CONTAINER_MAP[type]);
    const count = $container.find('.repeater-item').length;

    if (count >= (MAX_LIMITS[type] || 10)) { toastr.warning(T.maxLimitReached); return; }

    const $item = $(getTemplateHtml(type, count));
    $container.find('.empty-state').hide();
    $container.append($item);
    
    addRepeaterValidation(formId, type, count);
    if (type === 'article') initDatePicker();
    if (count + 1 >= MAX_LIMITS[type]) $(this).prop('disabled', true);
  });

  $(document).on('click', '.remove-item-btn', function () {
    const $item = $(this).closest('.repeater-item');
    const $container = $item.parent();
    const formId = $(this).closest('form').attr('id');
    const type = Object.keys(CONTAINER_MAP).find(k => CONTAINER_MAP[k] === $container.attr('id'));
    
    $item.remove();
    reindexContainer($container, type, formId);

    if ($container.find('.repeater-item').length === 0) $container.find('.empty-state').show();
    $(`[data-type="${type}"].add-item-btn`).prop('disabled', false);
  });

  function reindexContainer($container, type, formId) {
    const fv = validators[formId];
    $container.find('.repeater-item').each(function (newIdx) {
      const $item = $(this);

      // 1. Remove old names from Validator memory
      $item.find('input, select, textarea').each(function() {
        const oldName = $(this).attr('name');
        if (fv && oldName) try { fv.removeField(oldName); } catch(e) {}
      });

      // 2. Update DOM attributes
      $item.attr('data-index', newIdx).find('.item-number').text(newIdx + 1);
      $item.find('input, select, textarea').each(function() {
        const name = $(this).attr('name');
        if (name) $(this).attr('name', name.replace(/\[\d+\]/, `[${newIdx}]`));
      });

      // 3. Re-register with new names
      addRepeaterValidation(formId, type, newIdx);
    });
  }

  /* ═══════════════════════════════════════════════════════════
     7. HTML TEMPLATES
  ─────────────────────────────────────────────────────────── */

  function getTemplateHtml(type, i) {
    const commonHeader = (title) => `
      <div class="card-header d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0">${title} <span class="item-number">${i + 1}</span></h6>
        <button type="button" class="btn btn-icon btn-text-secondary remove-item-btn"><i class="ti ti-trash"></i></button>
      </div>`;

    if (type === 'step') return `
      <div class="repeater-item card shadow-none mb-3 border" data-index="${i}">
        ${commonHeader(i18n.step || 'Step')}
        <div class="card-body py-3">
          <div class="row">
            <div class="col-md-6 mb-3 fv-row">
              <input type="text" class="form-control" name="process_steps[${i}][title]" placeholder="${i18n.stepTitle}" maxlength="255">
            </div>
            <div class="col-md-6 mb-3 fv-row">
              <select class="form-select" name="process_steps[${i}][icon]">
                <option value="">${i18n.selectIcon}</option>${CFG.stepIconOptions}
              </select>
            </div>
            <div class="col-12 fv-row">
              <textarea class="form-control" name="process_steps[${i}][description]" rows="2" placeholder="${i18n.stepDescription}" maxlength="1000"></textarea>
            </div>
          </div>
        </div>
      </div>`;

    if (type === 'metric') return `
      <div class="repeater-item card shadow-none mb-3 border" data-index="${i}">
        ${commonHeader(i18n.metric || 'Metric')}
        <div class="card-body py-3">
          <div class="row">
            <div class="col-md-4 mb-3 fv-row">
              <input type="text" class="form-control" name="metrics[${i}][number]" placeholder="100+">
            </div>
            <div class="col-md-4 mb-3 fv-row">
              <input type="text" class="form-control" name="metrics[${i}][label]" placeholder="Label">
            </div>
            <div class="col-md-4 mb-3 fv-row">
              <select class="form-select" name="metrics[${i}][icon]">
                <option value="">Icon</option>${CFG.metricIconOptions}
              </select>
            </div>
          </div>
        </div>
      </div>`;

    if (type === 'article') return `
      <div class="repeater-item card shadow-none mb-3 border" data-index="${i}">
        ${commonHeader(i18n.article || 'Article')}
        <div class="card-body py-3">
          <div class="row">
            <div class="col-md-6 mb-3 fv-row"><label class="form-label">${i18n.publicationName}</label><input type="text" class="form-control" name="articles[${i}][publication_name]"></div>
            <div class="col-md-6 mb-3 fv-row"><label class="form-label">${i18n.publicationDate}</label><input type="text" class="form-control datepicker" name="articles[${i}][date]"></div>
            <div class="col-12 mb-3 fv-row"><label class="form-label">${i18n.articleTitle}</label><input type="text" class="form-control" name="articles[${i}][title]"></div>
            <div class="col-12 mb-3 fv-row"><label class="form-label">${i18n.articleLink}</label><input type="url" class="form-control" name="articles[${i}][link]"></div>
            <div class="col-12 mb-3 fv-row"><label class="form-label">${i18n.articleSummary}</label><textarea class="form-control" name="articles[${i}][summary]" rows="2"></textarea></div>
            <div class="col-12 fv-row">
              <label class="form-label">${i18n.articleImage} (${T.optional})</label>
              <input type="file" class="form-control" name="articles[${i}][image]" accept="image/png,image/jpeg">
              <div class="article-image-preview mt-2"></div>
            </div>
          </div>
        </div>
      </div>`;

    if (type === 'partner') return `
      <div class="repeater-item card shadow-none mb-3 border" data-index="${i}">
        ${commonHeader(i18n.partner || 'Partner')}
        <div class="card-body py-3">
          <div class="row">
            <div class="col-12 mb-3 fv-row">
               <label class="form-label">${i18n.partnerName} (${T.optional})</label>
               <input type="text" class="form-control" name="partners_list[${i}][name]">
            </div>
            <div class="col-md-3 mb-3 text-center">
               <div class="logo-preview border rounded d-flex align-items-center justify-content-center bg-light" style="height:80px;"><i class="ti ti-photo text-muted"></i></div>
            </div>
            <div class="col-md-9 mb-3 fv-row">
               <label class="form-label">${i18n.partnerLogo}</label>
               <input type="file" class="form-control logo-input" name="partners_list[${i}][logo]" accept="image/*">
            </div>
          </div>
        </div>
      </div>`;
  }

  /* ═══════════════════════════════════════════════════════════
     8. DOCUMENT READY & INIT
  ─────────────────────────────────────────────────────────── */

  function initSection(formId, fields, repeaterTypes = []) {
    const form = document.getElementById(formId);
    if (!form) return;
    const fv = FormValidation.formValidation(form, { fields, plugins: buildStandardPlugins() });
    attachStandardHandlers(formId, repeaterTypes, fv);
  }

  function initDatePicker() {
    if (window.flatpickr) $('.datepicker:not(.flatpickr-input)').flatpickr({ dateFormat: 'Y-m-d', allowInput: true });
  }

  $(function () {
    const titleVal = { validators: { notEmpty: { message: T.required }, stringLength: { max: 255 } } };
    const descVal = { validators: { notEmpty: { message: T.required }, stringLength: { max: 1000 } } };

    // Initialize the main forms
    initSection('form-howItWorks', { how_it_works_title: titleVal, how_it_works_subtitle: titleVal, how_it_works_description: descVal }, ['step']);
    initSection('form-achievements', { achievements_title: titleVal, achievements_subtitle: titleVal, achievements_description: descVal }, ['metric']);
    initSection('form-media', { media_title: titleVal, media_subtitle: titleVal, media_description: descVal }, ['article']);
    initSection('form-partners', { partners_title: titleVal, partners_subtitle: titleVal, partners_description: descVal }, ['partner']);
    initSection('form-programs', { programs_title: titleVal, programs_subtitle: titleVal, programs_description: descVal });

    // Bootstrap existing server-rendered items
    $('.repeater-item').each(function () {
      const $item = $(this);
      const formId = $item.closest('form').attr('id');
      const type = Object.keys(CONTAINER_MAP).find(k => CONTAINER_MAP[k] === $item.parent().attr('id'));
      if (formId && type) addRepeaterValidation(formId, type, $item.data('index'));
    });

    // Slug controls
    $('#generateSlugBtn').on('click', function() {
      const slug = (CFG.orgName || '').toLowerCase().replace(/[^\w\s-]/g, '').replace(/[\s_]+/g, '-');
      $('#slug').val(slug);
    });

    $('#checkSlugBtn').on('click', function() {
      const slug = $('#slug').val().trim();
      if (!slug) return;
      const $fb = $('#slugAvailabilityFeedback').show().html(T.slugChecking + '...');
      $.post(CFG.checkSlugUrl, { slug, _token: CFG.csrfToken }).done(res => {
        $fb.html(res.available ? `<span class="text-success">${T.slugAvailable}</span>` : `<span class="text-danger">${T.slugTaken}</span>`);
      }).fail(() => {
        $fb.html(`<span class="text-danger">${T.slugCheckError || T.slugTaken}</span>`);
      });
    });

    initDatePicker();

    // Tab navigation logic
    $('.reload-tab').on('click', function() {
      const url = new URL(window.location.href);
      url.searchParams.set('tab', $(this).data('tab-id'));
      window.location.href = url.toString();
    });

    // Landing Enable Toggle
    $('#landingEnabledToggle').on('change', function() {
       const text = this.checked ? i18n.landingPublished : i18n.landingUnpublished;
       const color = this.checked ? 'text-success' : 'text-warning';
       $('#landingStatusText').html(`<span class="${color}">${text}</span>`);
    });
  });

})(jQuery);
